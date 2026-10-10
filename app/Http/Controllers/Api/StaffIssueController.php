<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PhysicalCopyException;
use App\Http\Controllers\Api\Concerns\FormatsStaffStudentPayloads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StaffIssuePreviewRequest;
use App\Http\Requests\Api\StaffIssueStoreRequest;
use App\Http\Resources\Concerns\IncludesBookCover;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\PhysicalBookCopyService;
use App\Services\StudentIssuePrivilegeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffIssueController extends Controller
{
    use FormatsStaffStudentPayloads;
    use IncludesBookCover;

    public function privileges(int $student, StudentIssuePrivilegeService $privilegeService): JsonResponse
    {
        $studentModel = Student::query()
            ->with(['user', 'department', 'privileges'])
            ->findOrFail($student);

        return response()->json([
            'success' => true,
            'message' => 'Issue privileges fetched successfully.',
            'data' => [
                'student' => $this->studentPayload($studentModel),
                'privileges' => $privilegeService->getPrivileges($studentModel),
            ],
        ]);
    }

    public function issueBooks(Request $request, PhysicalBookCopyService $circulation): JsonResponse
    {
        $search = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? ''));
        $normalizedAccession = strtoupper($search);
        $hasExactAccession = $search !== '' && BookCopy::query()
            ->where('accession_number', $normalizedAccession)
            ->exists();

        $activeBookIds = $request->integer('student_id')
            ? IssuedBook::query()->where('student_id', $request->integer('student_id'))
                ->whereNull('return_date')->pluck('book_id')->map(fn ($id) => (int) $id)->all()
            : [];
        $copies = BookCopy::query()
            ->with(['book.category'])
            ->withActiveLoan()
            ->withCount(['issuedBooks as active_issues_count' => fn ($query) => $query->whereNull('return_date')])
            ->when(! $request->boolean('include_unavailable'), function ($query): void {
                $query->availableForIssue()
                    ->whereHas('book', fn ($book) => $book->whereNotIn('status', ['inactive', 'withdrawn']));
            })
            ->when($search !== '', function ($query) use ($search, $normalizedAccession, $hasExactAccession): void {
                if ($hasExactAccession) {
                    $query->where('accession_number', $normalizedAccession);

                    return;
                }

                $query->where(function ($copyQuery) use ($search, $normalizedAccession): void {
                    $copyQuery
                        ->where('accession_number', 'like', "%{$normalizedAccession}%")
                        ->orWhereHas('book', function ($bookQuery) use ($search): void {
                            $bookQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('isbn', 'like', "%{$search}%")
                                ->orWhere('author', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('accession_number')
            ->paginate($this->perPage($request));

        return response()->json([
            'success' => true,
            'message' => 'Physical book copies fetched successfully.',
            'data' => $copies->getCollection()->map(fn (BookCopy $copy) => $this->physicalCopyPayload($copy, $circulation, $activeBookIds))->values(),
            'meta' => [
                'current_page' => $copies->currentPage(),
                'last_page' => $copies->lastPage(),
                'per_page' => $copies->perPage(),
                'total' => $copies->total(),
            ],
        ]);
    }

    public function searchBooks(Request $request): JsonResponse
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? ''));
        $studentId = $request->integer('student_id') ?: $request->integer('studentId') ?: null;
        $studentIssuedBookIds = [];

        if ($studentId) {
            $studentIssuedBookIds = IssuedBook::query()
                ->where('student_id', $studentId)
                ->whereNull('return_date')
                ->pluck('book_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        $books = Book::query()
            ->with('category')
            ->withCirculationAvailability()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($bookQuery) use ($query) {
                    $bookQuery
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('author', 'like', "%{$query}%")
                        ->orWhere('publisher', 'like', "%{$query}%")
                        ->orWhere('isbn', 'like', "%{$query}%")
                        ->orWhere('shelf_no', 'like', "%{$query}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$query}%"));
                });
            })
            ->orderByCirculationAvailability()
            ->orderBy('title')
            ->paginate($this->perPage($request));

        return response()->json([
            'success' => true,
            'message' => 'Books fetched successfully.',
            'data' => $books->getCollection()
                ->map(fn (Book $book) => $this->bookPayload($book, in_array($book->id, $studentIssuedBookIds, true)))
                ->values(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
        ]);
    }

    public function preview(StaffIssuePreviewRequest $request, StudentIssuePrivilegeService $privilegeService, PhysicalBookCopyService $circulation): JsonResponse
    {
        $student = Student::query()->with(['user', 'department', 'privileges'])->findOrFail($request->integer('student_id'));
        if ($request->filled('accession_numbers')) {
            $accessions = array_values($request->input('accession_numbers'));
            $issueCheck = $privilegeService->canIssue($student, count($accessions));
            $snapshot = $this->physicalSelection($accessions, $student, $circulation);
            $errors = $snapshot['errors'];
            if (! $issueCheck['allowed']) {
                $errors['student_id'] = [$issueCheck['message']];
            }

            return response()->json([
                'success' => $errors === [],
                'message' => $errors === [] ? 'Selected copies are eligible for issue.' : 'Review the selected books before issuing.',
                'data' => [
                    'student' => $this->studentPayload($student),
                    'privileges' => $issueCheck['privileges'],
                    'selected_copies' => $snapshot['selected_copies'],
                    'invalid_copies' => $snapshot['invalid_copies'],
                    'errors' => (object) $errors,
                ],
            ], $errors === [] ? 200 : 422);
        }
        $bookIds = $request->bookIds();
        $books = Book::query()->with('category')->withCirculationAvailability()->whereIn('id', $bookIds)->get()->keyBy('id');
        $issueCheck = $privilegeService->canIssue($student, count($bookIds));
        $warnings = [];
        $errors = [];

        foreach ($bookIds as $bookId) {
            $book = $books->get($bookId);

            if (! $book) {
                $errors[] = "Book ID {$bookId} was not found.";

                continue;
            }

            if ($book->availableForBorrowingCount() <= 0) {
                $errors[] = "The book '{$book->title}' is not available.";
            }

            if ($this->studentHasActiveIssue($student->id, $book->id)) {
                $errors[] = "The student already has '{$book->title}' issued.";
            }
        }

        if (! $issueCheck['allowed']) {
            $errors[] = $issueCheck['message'];
        }

        if ($issueCheck['privileges']['has_pending_fines']) {
            $warnings[] = 'Student has pending fines, but current policy does not block issuing.';
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => count($errors) === 0 ? 'Issue preview fetched successfully.' : 'Issue preview contains validation errors.',
            'data' => [
                'student' => $this->studentPayload($student),
                'selected_books' => collect($bookIds)
                    ->map(fn (int $bookId) => $books->has($bookId) ? $this->bookPayload($books->get($bookId), $this->studentHasActiveIssue($student->id, $bookId)) : null)
                    ->filter()
                    ->values(),
                'privileges' => $issueCheck['privileges'],
                'issue_date' => $issueCheck['privileges']['issue_date'],
                'due_date' => $issueCheck['privileges']['due_date'],
                'duration_days' => $issueCheck['privileges']['duration_days'],
                'fine_rate' => $issueCheck['privileges']['fine_rate'],
                'warnings' => $warnings,
                'errors' => $errors,
            ],
        ], count($errors) === 0 ? 200 : 422);
    }

    public function store(
        StaffIssueStoreRequest $request,
        StudentIssuePrivilegeService $privilegeService,
        NotificationService $notifications,
        PhysicalBookCopyService $copies
    ): JsonResponse {
        $student = Student::query()->with(['user', 'department', 'privileges'])->findOrFail($request->integer('student_id'));

        if ($request->filled('book_copy_id')) {
            try {
                $issue = DB::transaction(function () use ($request, $privilegeService, $notifications, $copies): IssuedBook {
                    $lockedStudent = Student::query()
                        ->with(['user', 'department', 'privileges'])
                        ->lockForUpdate()
                        ->findOrFail($request->integer('student_id'));
                    $issueCheck = $privilegeService->canIssue($lockedStudent, 1);

                    if (! $issueCheck['allowed']) {
                        throw new PhysicalCopyException(
                            $issueCheck['message'] ?: 'This student is not eligible to borrow a book.',
                            422,
                            'student_not_eligible'
                        );
                    }

                    $issue = $copies->issueById(
                        $lockedStudent,
                        $request->integer('book_copy_id'),
                        $request->user(),
                        [
                            'issue_date' => $request->input('issue_date'),
                            'due_date' => $request->input('due_date'),
                            'remarks' => $request->input('remarks', $request->input('notes')),
                        ],
                        $request->filled('book_id') ? $request->integer('book_id') : null
                    );

                    BookRequest::query()
                        ->where('student_id', $lockedStudent->id)
                        ->where('book_id', $issue->book_id)
                        ->where('status', 'approved')
                        ->update([
                            'status' => 'issued',
                            'processed_by' => $request->user()->name ?? (string) $request->user()->id,
                            'processed_date' => now(),
                        ]);

                    $notifications->notifyBookIssued($issue);

                    return $issue;
                });
            } catch (PhysicalCopyException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'code' => $exception->errorCode,
                    ...$exception->details,
                ], $exception->status);
            }

            return response()->json([
                'success' => true,
                'message' => 'Book copy issued successfully.',
                'data' => [
                    'student' => $this->studentPayload($student),
                    'issue' => [
                        'issue_id' => $issue->id,
                        'book_id' => $issue->book_id,
                        'book_copy_id' => $issue->book_copy_id,
                        'accession_number' => $issue->bookCopy?->accession_number,
                        'title' => $issue->book?->title,
                        'author' => $issue->book?->author,
                        ...$this->bookCoverPayload($issue->book),
                        'issue_date' => optional($issue->issue_date)->toDateString(),
                        'due_date' => optional($issue->due_date)->toDateString(),
                        'status' => $issue->status,
                    ],
                ],
            ], 201);
        }

        $accessionNumbers = $request->accessionNumbers();

        if ($accessionNumbers !== []) {
            try {
                $issuedBooks = DB::transaction(function () use ($accessionNumbers, $request, $copies, $notifications, $privilegeService): array {
                    $lockedStudent = Student::query()
                        ->with(['user', 'department', 'privileges'])
                        ->lockForUpdate()
                        ->findOrFail($request->integer('student_id'));
                    $issueCheck = $privilegeService->canIssue($lockedStudent, count($accessionNumbers));

                    if (! $issueCheck['allowed']) {
                        throw new PhysicalCopyException(
                            $issueCheck['message'] ?: 'This student is not eligible to borrow a book.',
                            422,
                            'student_not_eligible'
                        );
                    }

                    $snapshot = $this->physicalSelection($accessionNumbers, $lockedStudent, $copies, lock: true);
                    if ($snapshot['errors'] !== []) {
                        throw new PhysicalCopyException(
                            $snapshot['invalid_copies'][0]['message'],
                            409,
                            'invalid_issue_selection',
                            ['errors' => $snapshot['errors'], 'invalid_copies' => $snapshot['invalid_copies']]
                        );
                    }

                    $created = [];
                    foreach ($accessionNumbers as $accessionNumber) {
                        $issue = $copies->issue($lockedStudent, $accessionNumber, $request->user(), [
                            'issue_date' => $request->input('issue_date'),
                            'due_date' => $request->input('due_date'),
                            'remarks' => $request->input('remarks', $request->input('notes')),
                        ]);

                        BookRequest::query()
                            ->where('student_id', $lockedStudent->id)
                            ->where('book_id', $issue->book_id)
                            ->where('status', 'approved')
                            ->update([
                                'status' => 'issued',
                                'processed_by' => $request->user()->name ?? (string) $request->user()->id,
                                'processed_date' => now(),
                            ]);

                        $notifications->notifyBookIssued($issue);
                        $created[] = $issue;
                    }

                    return $created;
                });
            } catch (PhysicalCopyException $exception) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                    'code' => $exception->errorCode,
                    ...$exception->details,
                ], $exception->status);
            }

            return response()->json([
                'success' => true,
                'message' => 'Book copies issued successfully.',
                'data' => [
                    'student' => $this->studentPayload($student),
                    'issued_count' => count($issuedBooks),
                    'issued_books' => collect($issuedBooks)->map(fn (IssuedBook $issue) => [
                        'issue_id' => $issue->id,
                        'book_id' => $issue->book_id,
                        'book_copy_id' => $issue->book_copy_id,
                        'accession_number' => $issue->bookCopy?->accession_number,
                        'title' => $issue->book?->title,
                        'author' => $issue->book?->author,
                        ...$this->bookCoverPayload($issue->book),
                        'due_date' => optional($issue->due_date)->toDateString(),
                        'status' => $issue->status,
                    ])->values(),
                ],
            ], 201);
        }
    }

    private function physicalCopyPayload(BookCopy $copy, PhysicalBookCopyService $circulation, array $activeBookIds = []): array
    {
        $error = $circulation->issueEligibility($copy, $activeBookIds);

        return [
            'book_id' => (int) $copy->book_id,
            'title' => $copy->book?->title ?? 'Book unavailable',
            'isbn' => $copy->book?->isbn,
            'author' => $copy->book?->author,
            ...$this->bookCoverPayload($copy->book),
            'book_copy_id' => $copy->id,
            'accession_number' => $copy->accession_number,
            'copy_type' => $copy->book_type,
            'book_type' => $copy->book_type,
            'status' => $copy->circulationStatus(),
            'active_issue' => $copy->activeLoanDetails(),
            'shelf_location' => $copy->shelf_location,
            'condition' => $copy->condition,
            'category' => $copy->book?->category?->name,
            'can_select' => $error === null,
            'unavailable_reason' => $error?->getMessage(),
            'eligibility_code' => $error?->errorCode,
        ];
    }

    private function physicalSelection(array $accessions, Student $student, PhysicalBookCopyService $circulation, bool $lock = false): array
    {
        $query = BookCopy::query()->with('book.category')->withActiveLoan()->whereIn('accession_number', $accessions)->orderBy('id');
        if ($lock) {
            $query->lockForUpdate();
        }
        $copies = $query->get();
        if ($lock) {
            // A consistent lock order prevents reversed batches from deadlocking each other.
            $books = Book::query()->whereIn('id', $copies->pluck('book_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $copies->each(fn (BookCopy $copy) => $copy->setRelation('book', $books->get($copy->book_id)));
        }
        $activeBookIds = IssuedBook::query()->where('student_id', $student->id)
            ->whereNull('return_date')->pluck('book_id')->map(fn ($id) => (int) $id)->all();
        $byAccession = $copies->keyBy('accession_number');
        $duplicateBookIds = $copies->groupBy('book_id')->filter(fn ($group) => $group->count() > 1)->keys()->all();
        $selectedCopies = [];
        $invalidCopies = [];
        $errors = [];
        foreach ($accessions as $index => $accession) {
            $copy = $byAccession->get($accession);
            $error = ! $copy
                ? new PhysicalCopyException('This physical copy no longer exists. Refresh and select another copy.', 409, 'accession_not_found')
                : (in_array($copy->book_id, $duplicateBookIds)
                    ? new PhysicalCopyException('Only one physical copy of each Book ID can be issued in this transaction.', 422, 'duplicate_book_id')
                    : $circulation->issueEligibility($copy, $activeBookIds));
            if ($copy) {
                $selectedCopies[] = $this->physicalCopyPayload($copy, $circulation, $activeBookIds);
            }
            if ($error) {
                $message = $accession.': '.$error->getMessage().' Refresh and review your selection.';
                $errors['accession_numbers.'.$index] = [$message];
                $invalidCopies[] = [
                    'book_id' => $copy?->book_id,
                    'book_copy_id' => $copy?->id,
                    'accession_number' => $accession,
                    'message' => $message,
                    'code' => $error->errorCode,
                ];
            }
        }

        return ['selected_copies' => $selectedCopies, 'invalid_copies' => $invalidCopies, 'errors' => $errors];
    }

    private function studentPayload(Student $student): array
    {
        return [
            'id' => $student->id,
            'user_id' => $student->user_id,
            'name' => $student->user?->name,
            'email' => $student->user?->email,
            'student_id' => $student->student_id ?: $student->roll_no,
            'roll_no' => $student->roll_no,
            'department' => $student->department?->name,
            'status' => $student->user?->status,
            ...$this->staffStudentPhotoPayload($student),
        ];
    }

    private function bookPayload(Book $book, bool $alreadyIssuedByStudent = false): array
    {
        $availableCopies = $book->availableForBorrowingCount();
        $available = $availableCopies > 0 && ! $alreadyIssuedByStudent;

        return [
            'id' => $book->id,
            'book_id' => $book->id,
            'copy_id' => null,
            'title' => $book->title,
            'author' => $book->author,
            ...$this->bookCoverPayload($book),
            'isbn' => $book->isbn,
            'accession_no' => $book->isbn,
            'category' => $book->category?->name,
            'total_copies' => (int) $book->total_copies,
            'available_copies' => $availableCopies,
            'is_available' => $available,
            'status' => $available ? 'available' : 'unavailable',
            'reason' => $alreadyIssuedByStudent
                ? 'Student already has this book issued.'
                : ($availableCopies <= 0 ? 'No copies available.' : null),
        ];
    }

    private function studentHasActiveIssue(int $studentId, int $bookId): bool
    {
        return IssuedBook::query()
            ->where('student_id', $studentId)
            ->where('book_id', $bookId)
            ->whereNull('return_date')
            ->exists();
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
