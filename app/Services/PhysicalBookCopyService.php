<?php

namespace App\Services;

use App\Exceptions\PhysicalCopyException;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PhysicalBookCopyService
{
    public function __construct(private readonly AccessionNumberGenerator $accessions)
    {
    }

    public function findByAccession(string $accessionNumber, bool $withActiveIssue = false): ?BookCopy
    {
        $query = BookCopy::query()->with(['book.category']);

        if ($withActiveIssue) {
            $query->with(['issuedBooks' => fn ($issued) => $issued
                ->whereNull('return_date')
                ->with(['student.user', 'student.department'])]);
        }

        return $query->where('accession_number', $this->normalizeAccession($accessionNumber))->first();
    }

    public function createCopies(Book $book, int $quantity, array $attributes = []): Collection
    {
        if ($quantity < 1) {
            throw new PhysicalCopyException('At least one physical copy is required.', 422, 'invalid_copy_quantity');
        }

        return DB::transaction(function () use ($book, $quantity, $attributes): Collection {
            $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);
            $copies = collect();

            for ($index = 0; $index < $quantity; $index++) {
                $copies->push(BookCopy::create([
                    'book_id' => $lockedBook->id,
                    'accession_number' => $this->accessions->next(),
                    'entry_date' => $attributes['entry_date'] ?? today(),
                    'book_type' => $attributes['book_type'] ?? 'borrowing',
                    'status' => $attributes['status'] ?? 'available',
                    'shelf_location' => $attributes['shelf_location'] ?? $lockedBook->shelf_no,
                    'condition' => $attributes['condition'] ?? $lockedBook->condition ?? 'good',
                    'remarks' => $attributes['remarks'] ?? null,
                ]));
            }

            $this->refreshBookCounters($lockedBook);

            return $copies;
        });
    }

    public function refreshBookCounters(Book $book): Book
    {
        $total = BookCopy::query()->where('book_id', $book->id)->count();
        $available = BookCopy::query()->where('book_id', $book->id)->where('status', 'available')->count();

        if ($total > 0) {
            $book->forceFill([
                'total_copies' => $total,
                'available_copies' => $available,
                'status' => $available > 0 ? 'available' : 'unavailable',
            ])->saveQuietly();
        }

        return $book->refresh();
    }

    public function issue(
        Student $student,
        string $accessionNumber,
        ?User $issuer = null,
        array $values = []
    ): IssuedBook {
        return DB::transaction(function () use ($student, $accessionNumber, $issuer, $values): IssuedBook {
            $copy = BookCopy::query()
                ->where('accession_number', $this->normalizeAccession($accessionNumber))
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw new PhysicalCopyException('Accession number not found.', 404, 'accession_not_found');
            }

            $book = Book::query()->lockForUpdate()->findOrFail($copy->book_id);

            if ($copy->status !== 'available') {
                throw new PhysicalCopyException('This book copy is already issued.', 409, 'copy_unavailable');
            }

            if (! $copy->isBorrowable()) {
                throw new PhysicalCopyException('This book is reference-only and cannot be borrowed.', 422, 'reference_only');
            }

            $alreadyIssued = IssuedBook::query()
                ->where('book_copy_id', $copy->id)
                ->whereNull('return_date')
                ->lockForUpdate()
                ->exists();

            if ($alreadyIssued) {
                throw new PhysicalCopyException('This book copy is already issued.', 409, 'copy_unavailable');
            }

            $sameTitleIssued = IssuedBook::query()
                ->where('student_id', $student->id)
                ->where('book_id', $book->id)
                ->whereNull('return_date')
                ->exists();

            if ($sameTitleIssued) {
                throw new PhysicalCopyException('The student already has this book issued.', 409, 'duplicate_student_issue');
            }

            $issueDate = isset($values['issue_date']) ? Carbon::parse($values['issue_date']) : now();
            $dueDate = isset($values['due_date'])
                ? Carbon::parse($values['due_date'])
                : $issueDate->copy()->addDays($this->effectiveIssueDuration($student));

            $issue = IssuedBook::create([
                'book_id' => $book->id,
                'book_copy_id' => $copy->id,
                'student_id' => $student->id,
                'issued_by' => $issuer?->id,
                'issue_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => 'issued',
                'remarks' => $values['remarks'] ?? null,
            ]);

            $copy->update(['status' => 'issued']);
            $this->refreshBookCounters($book);

            return $issue->fresh(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy']);
        });
    }

    public function activeIssueByAccession(string $accessionNumber): ?IssuedBook
    {
        return IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy'])
            ->whereHas('bookCopy', fn ($query) => $query->where('accession_number', $this->normalizeAccession($accessionNumber)))
            ->whereNull('return_date')
            ->latest('issue_date')
            ->first();
    }

    public function return(
        string $accessionNumber,
        string $condition = 'good',
        ?Carbon $returnDate = null,
        ?string $remarks = null,
        ?User $user = null
    ): array {
        return DB::transaction(function () use ($accessionNumber, $condition, $returnDate, $remarks, $user): array {
            $copy = BookCopy::query()
                ->where('accession_number', $this->normalizeAccession($accessionNumber))
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw new PhysicalCopyException('Accession number not found.', 404, 'accession_not_found');
            }

            if ($copy->status !== 'issued') {
                throw new PhysicalCopyException('This book copy is not currently issued.', 422, 'copy_not_issued');
            }

            $issue = IssuedBook::query()
                ->where('book_copy_id', $copy->id)
                ->whereNull('return_date')
                ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy'])
                ->lockForUpdate()
                ->latest('issue_date')
                ->first();

            if (! $issue) {
                throw new PhysicalCopyException('No active borrowing record found.', 422, 'no_active_issue');
            }

            $book = Book::query()->lockForUpdate()->findOrFail($copy->book_id);
            $fine = $this->calculateFine($issue, $condition, $returnDate ?? now());

            if ($fine['amount'] > 0) {
                Fine::updateOrCreate(
                    ['issued_book_id' => $issue->id],
                    [
                        'student_id' => $issue->student_id,
                        'amount' => $fine['amount'],
                        'days_late' => $fine['days_late'],
                        'status' => 'pending',
                        'remarks' => $fine['remarks'],
                    ]
                );
            }

            $issue->update([
                'return_date' => $returnDate ?? now(),
                'status' => 'returned',
                'condition' => $condition,
                'fine_amount' => $fine['amount'],
                'remarks' => $remarks ?: $issue->remarks,
            ]);

            $copy->update([
                'status' => match ($condition) {
                    'lost' => 'lost',
                    'damaged' => 'damaged',
                    default => 'available',
                },
                'condition' => $condition,
            ]);

            BookRequest::query()
                ->where('student_id', $issue->student_id)
                ->where('book_id', $issue->book_id)
                ->where('status', 'issued')
                ->update([
                    'status' => 'returned',
                    'processed_by' => $user?->name ?? (string) $user?->id,
                    'processed_date' => now(),
                ]);

            $this->refreshBookCounters($book);

            return [
                'issue' => $issue->fresh(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine']),
                'fine' => $fine,
            ];
        });
    }

    public function normalizeAccession(string $accessionNumber): string
    {
        return strtoupper(trim($accessionNumber));
    }

    private function effectiveIssueDuration(Student $student): int
    {
        $student->loadMissing('privileges');

        return (int) ($student->privileges?->issue_duration_days ?: FineSetting::resolveActive()->issue_duration_days);
    }

    private function calculateFine(IssuedBook $issue, string $condition, Carbon $returnDate): array
    {
        $settings = FineSetting::resolveActive();
        $dueDate = Carbon::parse($issue->due_date)->startOfDay();
        $returnedOn = $returnDate->copy()->startOfDay();
        $daysLate = $returnedOn->greaterThan($dueDate) ? (int) $dueDate->diffInDays($returnedOn) : 0;
        $graceDays = (int) ($settings->grace_period_days ?? 0);
        $perDay = (float) ($issue->student?->privileges?->per_day_fine ?: $settings->per_day_fine);
        $overdueAmount = $daysLate > $graceDays
            ? min(($daysLate - $graceDays) * $perDay, (float) $settings->max_fine_amount)
            : 0.0;

        $amount = match ($condition) {
            'lost' => (float) ($settings->lost_book_penalty ?? 0),
            'damaged' => $overdueAmount + (float) ($settings->damaged_book_penalty ?? 0),
            'fair' => $overdueAmount + (float) ($settings->fair_condition_penalty ?? 0),
            default => $overdueAmount,
        };

        return [
            'amount' => (float) $amount,
            'days_late' => $condition === 'lost' ? 0 : $daysLate,
            'remarks' => match ($condition) {
                'lost' => 'Lost book penalty',
                'damaged' => 'Damaged book penalty + overdue fine',
                'fair' => 'Fair condition penalty + overdue fine',
                default => 'Overdue fine',
            },
        ];
    }
}
