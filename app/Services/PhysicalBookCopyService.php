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
use Illuminate\Database\QueryException;

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

        $bookType = $attributes['book_type'] ?? 'borrowing';

        if (! in_array($bookType, ['borrowing', 'reference'], true)) {
            throw new PhysicalCopyException('Invalid physical book type.', 422, 'invalid_book_type');
        }

        return $this->createCopyBatch(
            $book,
            $bookType === 'borrowing' ? $quantity : 0,
            $bookType === 'reference' ? $quantity : 0,
            $attributes
        );
    }

    public function createCopyBatch(
        Book $book,
        int $borrowingQuantity,
        int $referenceQuantity,
        array $attributes = []
    ): Collection {
        $totalQuantity = $borrowingQuantity + $referenceQuantity;

        if ($totalQuantity < 1) {
            throw new PhysicalCopyException('At least one physical copy is required.', 422, 'invalid_copy_quantity');
        }

        return DB::transaction(function () use ($book, $borrowingQuantity, $referenceQuantity, $attributes): Collection {
            $lockedBook = Book::query()->lockForUpdate()->findOrFail($book->id);
            $copies = collect();

            foreach ([
                'borrowing' => $borrowingQuantity,
                'reference' => $referenceQuantity,
            ] as $bookType => $quantity) {
                for ($index = 0; $index < $quantity; $index++) {
                    $copies->push($this->createOneCopy($lockedBook, $bookType, $attributes));
                }
            }

            $this->refreshBookCounters($lockedBook);

            return $copies;
        });
    }

    private function createOneCopy(Book $book, string $bookType, array $attributes): BookCopy
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $accessionNumber = $this->accessions->next();

            try {
                return BookCopy::create([
                    'book_id' => $book->id,
                    'accession_number' => $accessionNumber,
                    'entry_date' => $attributes['entry_date'] ?? today(),
                    'book_type' => $bookType,
                    // A newly registered physical copy always starts available.
                    'status' => 'available',
                    'shelf_location' => $attributes['shelf_location'] ?? $book->shelf_no,
                    'condition' => $attributes['condition'] ?? $book->condition ?? 'good',
                    'price' => $attributes['price'] ?? null,
                    'remarks' => $attributes['remarks'] ?? null,
                ]);
            } catch (QueryException $exception) {
                if (! $this->isAccessionUniqueViolation($exception) || $attempt === 2) {
                    throw $exception;
                }
            }
        }

        throw new PhysicalCopyException('Unable to allocate a unique accession number.', 503, 'accession_generation_failed');
    }

    public function previewAccessions(int $quantity): array
    {
        return $this->accessions->preview($quantity);
    }

    public function refreshBookCounters(Book $book): Book
    {
        $total = BookCopy::query()->where('book_id', $book->id)->count();
        $available = BookCopy::query()->where('book_id', $book->id)->where('status', 'available')->count();

        $book->forceFill([
            'total_copies' => $total,
            'available_copies' => $available,
            'status' => $available > 0 ? 'available' : 'unavailable',
        ])->saveQuietly();

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
                [$message, $status, $code] = match (strtolower((string) $copy->status)) {
                    'issued' => ['This book copy is already issued.', 409, 'copy_issued'],
                    'lost' => ['This book copy is marked as lost and cannot be issued.', 409, 'copy_lost'],
                    'damaged' => ['This book copy is marked as damaged and cannot be issued.', 409, 'copy_damaged'],
                    'maintenance', 'under_maintenance' => ['This book copy is currently under maintenance.', 409, 'copy_maintenance'],
                    'withdrawn' => ['This book copy has been withdrawn from circulation.', 409, 'copy_withdrawn'],
                    default => ['This book copy is unavailable.', 409, 'copy_unavailable'],
                };

                throw new PhysicalCopyException($message, $status, $code);
            }

            if (! $copy->isBorrowable()) {
                throw new PhysicalCopyException('This book is reference-only and cannot be borrowed.', 422, 'reference_only');
            }

            if (strtolower((string) $copy->condition) === 'damaged') {
                throw new PhysicalCopyException('This book copy is marked as damaged and cannot be issued.', 409, 'copy_damaged');
            }

            $rawBookStatus = strtolower((string) $book->getRawOriginal('status'));
            if (in_array($rawBookStatus, ['inactive', 'withdrawn'], true)) {
                throw new PhysicalCopyException('This book is not available for borrowing.', 422, 'book_not_borrowable');
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

    private function isAccessionUniqueViolation(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return in_array((string) $exception->getCode(), ['19', '1062', '23000'], true)
            && str_contains($message, 'accession_number');
    }
}
