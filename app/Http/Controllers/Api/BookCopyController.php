<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PhysicalCopyException;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookCopyResource;
use App\Http\Resources\IssueResource;
use App\Models\BookCopy;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\PhysicalBookCopyService;
use App\Services\StudentIssuePrivilegeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookCopyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $copies = BookCopy::query()
            ->with(['book.category'])
            ->when($request->filled('book_id'), fn ($query) => $query->where('book_id', $request->integer('book_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('accession_number'), fn ($query) => $query->where('accession_number', 'like', strtoupper(trim($request->string('accession_number'))) . '%'))
            ->orderBy('accession_number')
            ->paginate($this->perPage($request));

        return BookCopyResource::collection($copies);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:3', 'max:32'],
            'mode' => ['nullable', 'in:issue,return'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        $term = strtoupper(trim($validated['query']));
        $mode = $validated['mode'] ?? 'issue';
        $issuedBookIds = $mode === 'issue' && ! empty($validated['student_id'])
            ? IssuedBook::query()->where('student_id', $validated['student_id'])
                ->whereNull('return_date')->distinct()->pluck('book_id')->map(fn ($id) => (int) $id)->all()
            : [];

        $copies = BookCopy::query()
            ->with(['book.category'])
            ->when($mode === 'return', fn ($query) => $query
                ->where('status', 'issued')
                ->whereHas(
                    'issuedBooks',
                    fn ($issueQuery) => $issueQuery->whereNull('return_date')
                ))
            ->with(['issuedBooks' => fn ($query) => $query
                ->whereNull('return_date')
                ->with(['student.user', 'student.department'])
                ->latest('issue_date')])
            ->where('accession_number', 'like', '%' . $term . '%')
            ->orderBy('accession_number')
            ->limit(15)
            ->get();

        if ($mode === 'return' && $copies->isEmpty() && preg_match('/^ACC-\d{6}$/', $term)) {
            $exactCopy = BookCopy::query()
                ->where('accession_number', $term)
                ->first();

            return response()->json([
                'success' => false,
                'message' => $exactCopy
                    ? 'This book copy is not currently issued.'
                    : 'Accession number not found.',
                'code' => $exactCopy ? 'no_active_issue' : 'accession_not_found',
                'data' => [],
            ], $exactCopy ? 422 : 404);
        }

        return response()->json([
            'success' => true,
            'data' => $copies->map(function (BookCopy $copy) use ($issuedBookIds): array {
                $issue = $copy->issuedBooks->first();
                $student = $issue?->student;

                return [
                    'copy' => array_merge((new BookCopyResource($copy))->resolve(), [
                        'already_issued_to_student' => in_array((int) $copy->book_id, $issuedBookIds, true),
                    ]),
                    'issue' => $issue ? [
                        'id' => $issue->id,
                        'issue_date' => optional($issue->issue_date)->toDateString(),
                        'due_date' => optional($issue->due_date)->toDateString(),
                        'accession_number' => $copy->accession_number,
                        'book' => [
                            'id' => $copy->book->id,
                            'title' => $copy->book->title,
                            'author' => $copy->book->author,
                        ],
                        'student' => [
                            'id' => $student?->id,
                            'name' => $student?->user?->name,
                            'roll_no' => $student?->roll_no,
                            'email' => $student?->user?->email,
                            'department' => $student?->department?->name,
                        ],
                    ] : null,
                ];
            })->values(),
        ]);
    }

    public function show(string $accessionNumber, PhysicalBookCopyService $copies): JsonResponse
    {
        $copy = $copies->findByAccession($accessionNumber, true);

        if (! $copy) {
            return response()->json([
                'success' => false,
                'message' => 'Accession number not found.',
                'code' => 'accession_not_found',
            ], 404);
        }

        $activeIssue = $copy->issuedBooks->first();

        return response()->json([
            'success' => true,
            'data' => [
                'copy' => (new BookCopyResource($copy))->resolve(),
                'active_issue' => $activeIssue ? (new IssueResource($activeIssue->load(['book.category', 'student.user', 'student.department', 'bookCopy'])))->resolve() : null,
            ],
        ]);
    }

    public function issue(
        Request $request,
        PhysicalBookCopyService $copies,
        StudentIssuePrivilegeService $privileges,
        NotificationService $notifications
    ): JsonResponse {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'accession_number' => ['required', 'string', 'max:32'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $student = Student::query()->with(['user', 'department', 'privileges'])->findOrFail($validated['student_id']);
        $issueCheck = $privileges->canIssue($student, 1);

        if (! $issueCheck['allowed']) {
            return response()->json([
                'success' => false,
                'message' => $issueCheck['message'],
                'data' => ['privileges' => $issueCheck['privileges']],
            ], 422);
        }

        try {
            $issue = $copies->issue($student, $validated['accession_number'], $request->user(), $validated);
            $notifications->notifyBookIssued($issue);

            return response()->json([
                'success' => true,
                'message' => 'Book copy issued successfully.',
                'data' => ['issue' => new IssueResource($issue)],
            ], 201);
        } catch (PhysicalCopyException $exception) {
            return $this->physicalCopyError($exception);
        }
    }

    public function activeIssue(Request $request, PhysicalBookCopyService $copies): JsonResponse
    {
        $validated = $request->validate([
            'accession_number' => ['required', 'string', 'max:32'],
        ]);

        $issue = $copies->activeIssueByAccession($validated['accession_number']);

        if (! $issue) {
            $copy = $copies->findByAccession($validated['accession_number']);

            return response()->json([
                'success' => false,
                'message' => $copy ? 'No active borrowing record found.' : 'Accession number not found.',
                'code' => $copy ? 'no_active_issue' : 'accession_not_found',
            ], $copy ? 422 : 404);
        }

        return response()->json([
            'success' => true,
            'data' => ['issue' => new IssueResource($issue)],
        ]);
    }

    public function returnCopy(
        Request $request,
        PhysicalBookCopyService $copies,
        NotificationService $notifications
    ): JsonResponse {
        $validated = $request->validate([
            'accession_number' => ['required', 'string', 'max:32'],
            'condition' => ['nullable', 'in:good,fair,damaged,lost'],
            'return_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $copies->return(
                $validated['accession_number'],
                $validated['condition'] ?? 'good',
                isset($validated['return_date']) ? Carbon::parse($validated['return_date']) : null,
                $validated['remarks'] ?? null,
                $request->user()
            );
            $notifications->notifyBookReturned($result['issue'], $validated['condition'] ?? 'good', (float) $result['fine']['amount']);

            return response()->json([
                'success' => true,
                'message' => 'Book copy returned successfully.',
                'data' => [
                    'issue' => new IssueResource($result['issue']),
                    'fine' => $result['fine'],
                ],
            ]);
        } catch (PhysicalCopyException $exception) {
            return $this->physicalCopyError($exception);
        }
    }

    private function physicalCopyError(PhysicalCopyException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
            'code' => $exception->errorCode,
        ], $exception->status);
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
