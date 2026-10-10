<?php

namespace App\Services;

use App\Exceptions\PhysicalCopyException;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PhysicalBookCopyService
{
    public function __construct(
        private readonly AccessionNumberGenerator $accessions,
        private readonly FineCalculator $fineCalculator,
        private readonly StudentIssuePrivilegeService $privileges
    ) {}

    public function findByAccession(string $accessionNumber, bool $withActiveIssue = false): ?BookCopy
    {
        $query = BookCopy::query()->with(['book.category']);

        if ($withActiveIssue) {
            $query->withActiveLoan();
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
            // Serialize circulation for this student even when called outside the mobile API.
            $student = Student::query()->lockForUpdate()->findOrFail($student->id);
            $copy = BookCopy::query()
                ->where('accession_number', $this->normalizeAccession($accessionNumber))
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw new PhysicalCopyException('Accession number not found.', 404, 'accession_not_found');
            }

            return $this->issueLockedCopy($student, $copy, $issuer, $values);
        });
    }

    public function issueById(
        Student $student,
        int $bookCopyId,
        ?User $issuer = null,
        array $values = [],
        ?int $expectedBookId = null
    ): IssuedBook {
        return DB::transaction(function () use ($student, $bookCopyId, $issuer, $values, $expectedBookId): IssuedBook {
            $student = Student::query()->lockForUpdate()->findOrFail($student->id);
            $copy = BookCopy::query()
                ->whereKey($bookCopyId)
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw new PhysicalCopyException('Book copy not found.', 404, 'book_copy_not_found');
            }

            if ($expectedBookId !== null && (int) $copy->book_id !== $expectedBookId) {
                throw new PhysicalCopyException(
                    'The selected book copy does not belong to this book.',
                    422,
                    'book_copy_mismatch'
                );
            }

            return $this->issueLockedCopy($student, $copy, $issuer, $values);
        });
    }

    /** Shared by copy search, previews and the final locked circulation write. */
    public function issueEligibility(BookCopy $copy, array $activeBookIds = []): ?PhysicalCopyException
    {
        $copyStatus = $copy->circulationStatus();
        if ($copyStatus !== 'available') {
            [$message, $status, $code] = match (strtolower($copyStatus)) {
                'issued' => ['This book copy is already issued.', 409, $copy->status === 'issued' ? 'copy_issued' : 'copy_unavailable'],
                'lost' => ['This book copy is marked as lost and cannot be issued.', 409, 'copy_lost'],
                'damaged' => ['This book copy is marked as damaged and cannot be issued.', 409, 'copy_damaged'],
                'maintenance', 'under_maintenance' => ['This book copy is currently under maintenance.', 409, 'copy_maintenance'],
                'withdrawn' => ['This book copy has been withdrawn from circulation.', 409, 'copy_withdrawn'],
                default => ['This book copy is unavailable.', 409, 'copy_unavailable'],
            };

            return new PhysicalCopyException($message, $status, $code);
        }

        if (! $copy->isBorrowable()) {
            return new PhysicalCopyException('This book is reference-only and cannot be borrowed.', 422, 'reference_only');
        }

        if (strtolower((string) $copy->condition) === 'damaged') {
            return new PhysicalCopyException('This book copy is marked as damaged and cannot be issued.', 409, 'copy_damaged');
        }

        if (strtolower((string) $copy->condition) === 'lost') {
            return new PhysicalCopyException('This book copy is marked as lost and cannot be issued.', 409, 'copy_lost');
        }

        $book = $copy->book;
        if (! $book) {
            return new PhysicalCopyException('This book is no longer in the catalogue.', 409, 'book_not_borrowable');
        }
        $rawBookStatus = strtolower((string) $book->getRawOriginal('status'));
        if (in_array($rawBookStatus, ['inactive', 'withdrawn'], true)) {
            return new PhysicalCopyException('This book is not available for borrowing.', 422, 'book_not_borrowable');
        }

        if (in_array((int) $copy->book_id, $activeBookIds, true)) {
            return new PhysicalCopyException('This student already has an active copy of this book.', 409, 'duplicate_student_issue');
        }

        return null;
    }

    private function issueLockedCopy(
        Student $student,
        BookCopy $copy,
        ?User $issuer,
        array $values
    ): IssuedBook {
        $book = Book::query()->lockForUpdate()->findOrFail($copy->book_id);
        $copy->setRelation('book', $book);
        $activeBookIds = IssuedBook::query()->where('student_id', $student->id)
            ->whereNull('return_date')->lockForUpdate()->pluck('book_id')->map(fn ($id) => (int) $id)->all();
        if ($error = $this->issueEligibility($copy, $activeBookIds)) {
            throw $error;
        }

        // Recheck after acquiring the student lock, including callers with an earlier preflight.
        $issueCheck = $this->privileges->canIssue($student, 1);
        if (! $issueCheck['allowed']) {
            throw new PhysicalCopyException($issueCheck['message'], 422, 'student_not_eligible');
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
    }

    public function activeIssueByAccession(string $accessionNumber): ?IssuedBook
    {
        return IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy'])
            ->whereHas('bookCopy', fn ($query) => $query->where('accession_number', $this->normalizeAccession($accessionNumber)))
            ->returnable()
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
                ->whereIn('status', IssuedBook::ACTIVE_RETURN_STATUSES)
                ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy'])
                ->lockForUpdate()
                ->latest('issue_date')
                ->first();

            if (! $issue) {
                throw new PhysicalCopyException('No active borrowing record found.', 422, 'no_active_issue');
            }

            return $this->completeLockedReturn(
                $issue,
                $copy,
                $condition,
                $returnDate ?? now(),
                $remarks,
                $user
            );
        });
    }

    public function returnIssueById(
        int $issueId,
        int $bookCopyId,
        string $condition = 'good',
        ?Carbon $returnDate = null,
        ?string $remarks = null,
        ?User $user = null,
        ?int $studentId = null,
        ?string $accessionNumber = null
    ): array {
        return DB::transaction(function () use ($issueId, $bookCopyId, $condition, $returnDate, $remarks, $user, $studentId, $accessionNumber): array {
            $copy = BookCopy::query()->lockForUpdate()->find($bookCopyId);

            if (! $copy) {
                throw new PhysicalCopyException('Book copy not found.', 404, 'book_copy_not_found');
            }

            $issue = IssuedBook::query()
                ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy'])
                ->lockForUpdate()
                ->find($issueId);

            if (! $issue) {
                throw new PhysicalCopyException('Issue record not found.', 404, 'issue_not_found');
            }

            $this->validateReturnIssue($issue, $copy, $studentId, $accessionNumber);

            return $this->completeLockedReturn(
                $issue,
                $copy,
                $condition,
                $returnDate ?? now(),
                $remarks,
                $user
            );
        });
    }

    public function calculateReturnFine(
        IssuedBook $issue,
        string $condition,
        ?Carbon $returnDate = null
    ): array {
        $issue->loadMissing(['student.privileges']);

        return $this->fineCalculator->calculateReturnFine($issue, $condition, $returnDate);
    }

    /** The final return calls this with the issue and physical copy locked. */
    public function validateReturnIssue(
        IssuedBook $issue,
        BookCopy $copy,
        ?int $studentId = null,
        ?string $accessionNumber = null
    ): void {
        if ($studentId !== null && (int) $issue->student_id !== $studentId) {
            throw new PhysicalCopyException('This issue does not belong to the selected borrower.', 422, 'borrower_mismatch');
        }

        if ($issue->return_date !== null || $issue->status === 'returned') {
            throw new PhysicalCopyException('This book has already been returned.', 409, 'issue_already_returned');
        }

        if (! in_array($issue->status, IssuedBook::ACTIVE_RETURN_STATUSES, true)
            || ! $issue->issue_date || ! $issue->due_date || ! $issue->student || ! $issue->book
            || $issue->issue_date->gt(today()) || $issue->due_date->lt($issue->issue_date)) {
            throw new PhysicalCopyException('This issue record is no longer actively issued.', 409, 'issue_not_active');
        }

        if ((int) $issue->book_copy_id !== $copy->id || (int) $issue->book_id !== (int) $copy->book_id) {
            throw new PhysicalCopyException('This physical copy does not belong to the selected issue.', 422, 'issue_copy_mismatch');
        }

        if ($accessionNumber !== null && $this->normalizeAccession($accessionNumber) !== $copy->accession_number) {
            throw new PhysicalCopyException('The accession number does not match the issued physical copy.', 422, 'accession_mismatch');
        }

        if ($copy->status !== 'issued' || $copy->condition === 'lost') {
            throw new PhysicalCopyException('This book copy is not currently issued.', 409, 'copy_not_issued');
        }

        $activeIds = IssuedBook::query()->where('book_copy_id', $copy->id)->whereNull('return_date')
            ->whereIn('status', IssuedBook::ACTIVE_RETURN_STATUSES)->pluck('id');
        if ($activeIds->count() !== 1 || (int) $activeIds->first() !== $issue->id) {
            throw new PhysicalCopyException('The active issue does not match this physical copy.', 409, 'active_issue_mismatch');
        }
    }

    private function completeLockedReturn(
        IssuedBook $issue,
        BookCopy $copy,
        string $condition,
        Carbon $returnDate,
        ?string $remarks,
        ?User $user
    ): array {
        $this->validateReturnIssue($issue, $copy);
        if ($returnDate->copy()->startOfDay()->lt($issue->issue_date)) {
            throw new PhysicalCopyException('Return date cannot be before the issue date.', 422, 'invalid_return_date');
        }
        $book = Book::query()->lockForUpdate()->findOrFail($copy->book_id);
        $fine = $this->calculateReturnFine($issue, $condition, $returnDate);

        $this->fineCalculator->applyReturnFine($issue, $fine);

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

    private function isAccessionUniqueViolation(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return in_array((string) $exception->getCode(), ['19', '1062', '23000'], true)
            && str_contains($message, 'accession_number');
    }
}
