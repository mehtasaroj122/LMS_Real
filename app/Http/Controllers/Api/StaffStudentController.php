<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Services\StudentIssuePrivilegeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffStudentController extends Controller
{
    public function index(Request $request, StudentIssuePrivilegeService $privilegeService): JsonResponse
    {
        $students = $this->studentQuery($request->string('query')->toString())
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json([
            'success' => true,
            'message' => 'Students fetched successfully.',
            'data' => $students->getCollection()
                ->map(fn (Student $student) => $this->summary($student, $privilegeService))
                ->values(),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    public function search(Request $request, StudentIssuePrivilegeService $privilegeService): JsonResponse
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? ''));

        $students = $this->studentQuery($query)
            ->limit(20)
            ->get()
            ->map(fn (Student $student) => $this->summary($student, $privilegeService))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Students fetched successfully.',
            'data' => $students,
        ]);
    }

    public function show(int $student, StudentIssuePrivilegeService $privilegeService): JsonResponse
    {
        $studentModel = Student::query()
            ->with([
                'user',
                'department',
                'privileges',
                'issuedBooks' => fn ($query) => $query->with('book.category')->latest(),
                'bookRequests' => fn ($query) => $query->with('book.category')->latest('request_date'),
                'fines' => fn ($query) => $query->with('issuedBook.book.category')->latest(),
            ])
            ->findOrFail($student);

        $activeIssues = $studentModel->issuedBooks
            ->whereNull('return_date')
            ->values();

        $pendingRequests = $studentModel->bookRequests
            ->whereIn('status', ['pending', 'approved'])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Student fetched successfully.',
            'data' => [
                ...$this->summary($studentModel, $privilegeService),
                'phone' => $studentModel->user?->phone,
                'gender' => $studentModel->user?->gender,
                'batch' => $studentModel->batch,
                'semester' => $studentModel->semester,
                'address' => $studentModel->address ?: $studentModel->user?->address,
                'returned_books_count' => $studentModel->issuedBooks->whereNotNull('return_date')->count(),
                'pending_fines_amount' => (float) $studentModel->fines->where('status', 'pending')->sum('amount'),
                'pending_requests_count' => $pendingRequests->count(),
                'active_issued_books' => $activeIssues->map(fn (IssuedBook $issue) => $this->issueSummary($issue))->values(),
                'pending_requests' => $pendingRequests->map(fn ($bookRequest) => [
                    'id' => $bookRequest->id,
                    'book_id' => $bookRequest->book_id,
                    'book_title' => $bookRequest->book?->title,
                    'author' => $bookRequest->book?->author,
                    'status' => $bookRequest->status,
                    'request_date' => optional($bookRequest->request_date)->toDateTimeString(),
                ])->values(),
                'pending_fines' => $studentModel->fines
                    ->where('status', 'pending')
                    ->map(fn (Fine $fine) => [
                        'id' => $fine->id,
                        'issue_id' => $fine->issued_book_id,
                        'book_title' => $fine->issuedBook?->book?->title,
                        'amount' => (float) $fine->amount,
                        'days_late' => (int) $fine->days_late,
                        'status' => $fine->status,
                        'remarks' => $fine->remarks,
                    ])
                    ->values(),
            ],
        ]);
    }

    private function studentQuery(string $query)
    {
        return Student::query()
            ->with(['user', 'department', 'privileges'])
            ->withCount([
                'issuedBooks as active_issued_books_count' => fn ($builder) => $builder->whereNull('return_date'),
            ])
            ->whereHas('user', fn ($builder) => $builder->where('role', 'student'))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($studentQuery) use ($query) {
                    $studentQuery
                        ->where('students.id', 'like', "%{$query}%")
                        ->orWhere('students.student_id', 'like', "%{$query}%")
                        ->orWhere('students.roll_no', 'like', "%{$query}%")
                        ->orWhereHas('user', function ($userQuery) use ($query) {
                            $userQuery
                                ->where('name', 'like', "%{$query}%")
                                ->orWhere('email', 'like', "%{$query}%")
                                ->orWhere('phone', 'like', "%{$query}%");
                        })
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$query}%"));
                });
            })
            ->orderBy('students.roll_no');
    }

    private function summary(Student $student, StudentIssuePrivilegeService $privilegeService): array
    {
        $privileges = $privilegeService->getPrivileges($student);

        return [
            'id' => $student->id,
            'user_id' => $student->user_id,
            'name' => $student->user?->name,
            'email' => $student->user?->email,
            'student_id' => $student->student_id ?: $student->roll_no,
            'roll_no' => $student->roll_no,
            'symbol_no' => $student->roll_no,
            'department' => $student->department?->name,
            'status' => $student->user?->status,
            'current_issued' => (int) ($student->active_issued_books_count ?? $privileges['already_issued']),
            'max_books' => $privileges['max_books'],
            'can_issue' => $privileges['allowed'] && $privileges['can_issue'] > 0,
        ];
    }

    private function issueSummary(IssuedBook $issue): array
    {
        return [
            'issue_id' => $issue->id,
            'book_id' => $issue->book_id,
            'title' => $issue->book?->title,
            'author' => $issue->book?->author,
            'isbn' => $issue->book?->isbn,
            'issue_date' => optional($issue->issue_date)->toDateString(),
            'due_date' => optional($issue->due_date)->toDateString(),
            'status' => $issue->status,
        ];
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
