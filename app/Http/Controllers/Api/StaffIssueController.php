<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Api\Concerns\FormatsStaffStudentPayloads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StaffIssuePreviewRequest;
use App\Http\Requests\Api\StaffIssueStoreRequest;
use App\Jobs\SendBookIssuedEmail;
use App\Models\book as Book;
use App\Models\BookRequest;
use App\Models\IssuedBook;
use App\Models\Notification;
use App\Models\Student;
use App\Services\StudentIssuePrivilegeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffIssueController extends Controller
{
    use FormatsStaffStudentPayloads;

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
                ->all();
        }

        $books = Book::query()
            ->with('category')
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
            ->orderByDesc('available_copies')
            ->orderBy('title')
            ->limit(20)
            ->get()
            ->map(fn (Book $book) => $this->bookPayload($book, in_array($book->id, $studentIssuedBookIds, true)))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Books fetched successfully.',
            'data' => $books,
        ]);
    }

    public function preview(StaffIssuePreviewRequest $request, StudentIssuePrivilegeService $privilegeService): JsonResponse
    {
        $student = Student::query()->with(['user', 'department', 'privileges'])->findOrFail($request->integer('student_id'));
        $bookIds = $request->bookIds();
        $books = Book::query()->with('category')->whereIn('id', $bookIds)->get()->keyBy('id');
        $issueCheck = $privilegeService->canIssue($student, count($bookIds));
        $warnings = [];
        $errors = [];

        foreach ($bookIds as $bookId) {
            $book = $books->get($bookId);

            if (! $book) {
                $errors[] = "Book ID {$bookId} was not found.";
                continue;
            }

            if ((int) $book->available_copies <= 0) {
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

    public function store(StaffIssueStoreRequest $request, StudentIssuePrivilegeService $privilegeService): JsonResponse
    {
        $student = Student::query()->with(['user', 'department', 'privileges'])->findOrFail($request->integer('student_id'));
        $bookIds = $request->bookIds();
        $issueCheck = $privilegeService->canIssue($student, count($bookIds));

        if (! $issueCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $issueCheck['message'],
                'errors' => [
                    'book_ids' => ['Selected books exceed allowed issue limit.'],
                ],
                'data' => [
                    'privileges' => $issueCheck['privileges'],
                ],
            ], 422);
        }

        try {
            $issuedBooks = DB::transaction(function () use ($request, $student, $bookIds, $issueCheck) {
                $issueDate = Carbon::parse($issueCheck['privileges']['issue_date']);
                $dueDate = Carbon::parse($issueCheck['privileges']['due_date']);
                $createdIssues = collect();

                foreach ($bookIds as $bookId) {
                    $book = Book::query()->lockForUpdate()->with('category')->findOrFail($bookId);

                    if ((int) $book->available_copies <= 0) {
                        abort(response()->json([
                            'success' => false,
                            'message' => 'One or more selected books are not available.',
                            'errors' => [
                                'book_ids' => ["The book '{$book->title}' is not available."],
                            ],
                        ], 409));
                    }

                    if ($this->studentHasActiveIssue($student->id, $book->id)) {
                        abort(response()->json([
                            'success' => false,
                            'message' => "The student already has '{$book->title}' issued.",
                        ], 409));
                    }

                    $issue = IssuedBook::create([
                        'book_id' => $book->id,
                        'student_id' => $student->id,
                        'issued_by' => $request->user()->id,
                        'issue_date' => $issueDate,
                        'due_date' => $dueDate,
                        'status' => 'issued',
                        'remarks' => $request->input('remarks', $request->input('notes')),
                    ]);

                    $book->decrement('available_copies');

                    BookRequest::query()
                        ->where('student_id', $student->id)
                        ->where('book_id', $book->id)
                        ->where('status', 'approved')
                        ->update([
                            'status' => 'issued',
                            'processed_by' => $request->user()->name ?? (string) $request->user()->id,
                            'processed_date' => now(),
                        ]);

                    $this->notifyIssueCreated($student, $book, $issue);

                    ActivityLogger::logBookIssued($student, $book->title, [
                        'book_id' => $book->id,
                        'issued_book_id' => $issue->id,
                        'isbn' => $book->isbn,
                        'due_date' => $dueDate->toDateString(),
                        'source' => 'mobile_staff_api',
                    ]);

                    $createdIssues->push($issue->fresh(['student.user', 'student.department', 'book.category']));
                }

                return $createdIssues;
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Books issued successfully.',
            'data' => [
                'student' => $this->studentPayload($student),
                'issued_count' => $issuedBooks->count(),
                'issue_date' => $issueCheck['privileges']['issue_date'],
                'due_date' => $issueCheck['privileges']['due_date'],
                'issued_books' => $issuedBooks->map(fn (IssuedBook $issue) => [
                    'issue_id' => $issue->id,
                    'book_id' => $issue->book_id,
                    'title' => $issue->book?->title,
                    'author' => $issue->book?->author,
                    'due_date' => optional($issue->due_date)->toDateString(),
                    'status' => $issue->status,
                ])->values(),
            ],
        ], 201);
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
        $available = (int) $book->available_copies > 0 && ! $alreadyIssuedByStudent;

        return [
            'id' => $book->id,
            'book_id' => $book->id,
            'copy_id' => null,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'accession_no' => $book->isbn,
            'category' => $book->category?->name,
            'total_copies' => (int) $book->total_copies,
            'available_copies' => (int) $book->available_copies,
            'is_available' => $available,
            'status' => $available ? 'available' : 'unavailable',
            'reason' => $alreadyIssuedByStudent
                ? 'Student already has this book issued.'
                : (((int) $book->available_copies <= 0) ? 'No copies available.' : null),
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

    private function notifyIssueCreated(Student $student, Book $book, IssuedBook $issue): void
    {
        if (! $student->user) {
            return;
        }

        Notification::notify(
            user: $student->user,
            type: 'book.issued',
            title: 'Book Issued Successfully',
            message: "You have been issued '{$book->title}' by {$book->author}.",
            data: [
                'book_id' => $book->id,
                'issued_book_id' => $issue->id,
                'issue_date' => optional($issue->issue_date)->toDateString(),
                'due_date' => optional($issue->due_date)->toDateString(),
            ],
            relatedModel: 'IssuedBook',
            relatedId: $issue->id
        );

        if ($student->user->email) {
            try {
                SendBookIssuedEmail::dispatch(
                    $student->user->email,
                    $student->user->name,
                    $book->title,
                    $book->author ?? 'Unknown',
                    optional($issue->issue_date)->format('Y-m-d'),
                    optional($issue->due_date)->format('Y-m-d')
                );
            } catch (\Throwable $exception) {
                \Log::warning('Unable to queue book issued email: ' . $exception->getMessage(), [
                    'issued_book_id' => $issue->id,
                ]);
            }
        }
    }
}
