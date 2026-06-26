<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IssueReturnRequest;
use App\Http\Requests\Api\IssueStoreRequest;
use App\Http\Resources\FineResource;
use App\Http\Resources\IssueResource;
use App\Models\book as Book;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class IssueController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $issues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate($this->perPage($request));

        return IssueResource::collection($issues);
    }

    public function store(IssueStoreRequest $request, NotificationService $notifications): JsonResponse
    {
        $student = Student::query()->with(['user', 'department', 'privileges'])->findOrFail($request->integer('student_id'));
        $bookIds = $request->bookIds();

        if (! $this->isStudentAllowedToBorrow($student)) {
            return response()->json([
                'message' => 'This student is not allowed to borrow books at this time.',
            ], 403);
        }

        if (! $this->canIssueRequestedCount($student, count($bookIds))) {
            return response()->json([
                'message' => 'This issue would exceed the student borrowing limit.',
            ], 422);
        }

        $issuedBooks = DB::transaction(function () use ($request, $student, $bookIds, $notifications) {
            $issueDate = $request->date('issue_date') ?? now();
            $dueDate = $request->date('due_date') ?? (clone $issueDate)->addDays($this->getEffectiveIssueDuration($student));
            $createdIssues = collect();

            foreach ($bookIds as $bookId) {
                $book = Book::query()->lockForUpdate()->findOrFail($bookId);

                if ((int) $book->available_copies <= 0) {
                    abort(response()->json([
                        'message' => "The book '{$book->title}' is not available.",
                    ], 409));
                }

                $alreadyIssued = IssuedBook::query()
                    ->where('student_id', $student->id)
                    ->where('book_id', $book->id)
                    ->whereNull('return_date')
                    ->exists();

                if ($alreadyIssued) {
                    abort(response()->json([
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
                    'remarks' => $request->input('remarks'),
                ]);

                $book->decrement('available_copies');

                BookRequest::query()
                    ->where('student_id', $student->id)
                    ->where('book_id', $book->id)
                    ->where('status', 'approved')
                    ->update([
                        'status' => 'issued',
                        'processed_by' => $request->user()->id,
                        'processed_date' => now(),
                    ]);

                $issue->load(['student.user', 'student.department', 'book.category']);
                $notifications->notifyBookIssued($issue);

                $createdIssues->push($issue);
            }

            return $createdIssues;
        });

        return response()->json([
            'message' => 'Book issue completed successfully.',
            'issued_count' => $issuedBooks->count(),
            'data' => IssueResource::collection($issuedBooks),
        ], 201);
    }

    public function show(int $id): IssueResource
    {
        return new IssueResource(
            IssuedBook::query()
                ->with(['student.user', 'student.department', 'book.category'])
                ->findOrFail($id)
        );
    }

    public function studentIssues(int $studentId, Request $request): AnonymousResourceCollection
    {
        Student::query()->findOrFail($studentId);

        $issues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->where('student_id', $studentId)
            ->latest()
            ->paginate($this->perPage($request));

        return IssueResource::collection($issues);
    }

    public function returnBook(int $id, IssueReturnRequest $request, NotificationService $notifications): JsonResponse
    {
        $issue = IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category'])
            ->findOrFail($id);

        if ($issue->return_date !== null) {
            return response()->json([
                'message' => 'This book has already been returned.',
            ], 409);
        }

        $condition = $request->input('condition', 'good');
        $returnDate = $request->date('return_date') ?? now();

        $issue = DB::transaction(function () use ($issue, $condition, $returnDate, $request, $notifications) {
            $bookFine = $this->calculateReturnFine($issue, $condition, $returnDate);

            if ($bookFine['amount'] > 0) {
                Fine::create([
                    'issued_book_id' => $issue->id,
                    'student_id' => $issue->student_id,
                    'amount' => $bookFine['amount'],
                    'days_late' => $bookFine['days_late'],
                    'status' => 'paid',
                    'remarks' => $bookFine['remarks'],
                ]);
            }

            $issue->update([
                'return_date' => $returnDate,
                'status' => 'returned',
                'condition' => $condition,
                'fine_amount' => $bookFine['amount'],
                'remarks' => $request->input('remarks', $issue->remarks),
            ]);

            BookRequest::query()
                ->where('student_id', $issue->student_id)
                ->where('book_id', $issue->book_id)
                ->where('status', 'issued')
                ->update([
                    'status' => 'returned',
                    'processed_by' => $request->user()->id,
                    'processed_date' => now(),
                ]);

            if (! in_array($condition, ['lost', 'damaged'], true)) {
                $issue->book()->lockForUpdate()->first()?->increment('available_copies');
            }

            $freshIssue = $issue->fresh(['student.user', 'student.department', 'book.category']);
            $notifications->notifyBookReturned($freshIssue, $condition, (float) $bookFine['amount']);

            return $freshIssue;
        });

        return response()->json([
            'message' => 'Book returned successfully.',
            'data' => new IssueResource($issue),
        ], 200);
    }

    public function overdue(Request $request): AnonymousResourceCollection
    {
        $issues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'book.category'])
            ->whereNull('return_date')
            ->whereDate('due_date', '<', today())
            ->orderBy('due_date')
            ->paginate($this->perPage($request));

        return IssueResource::collection($issues);
    }

    public function fines(Request $request): AnonymousResourceCollection
    {
        $fines = Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->latest()
            ->paginate($this->perPage($request));

        return FineResource::collection($fines);
    }

    public function studentFines(int $id, Request $request): AnonymousResourceCollection
    {
        Student::query()->findOrFail($id);

        $fines = Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->where('student_id', $id)
            ->latest()
            ->paginate($this->perPage($request));

        return FineResource::collection($fines);
    }

    private function calculateReturnFine(IssuedBook $issue, string $condition, Carbon $returnDate): array
    {
        $fineSetting = FineSetting::resolveActive();
        $overdueFine = $this->calculateOverdueFine($issue, $fineSetting, $returnDate);

        return match ($condition) {
            'lost' => [
                'amount' => (float) ($fineSetting->lost_book_penalty ?? 0),
                'days_late' => 0,
                'remarks' => 'Lost book penalty',
            ],
            'damaged' => [
                'amount' => (float) ($overdueFine['amount'] ?? 0) + (float) ($fineSetting->damaged_book_penalty ?? 0),
                'days_late' => (int) ($overdueFine['days_late'] ?? 0),
                'remarks' => 'Damaged book penalty + overdue fine',
            ],
            'fair' => [
                'amount' => (float) ($overdueFine['amount'] ?? 0) + (float) ($fineSetting->fair_condition_penalty ?? 0),
                'days_late' => (int) ($overdueFine['days_late'] ?? 0),
                'remarks' => 'Fair condition penalty + overdue fine',
            ],
            default => [
                'amount' => (float) ($overdueFine['amount'] ?? 0),
                'days_late' => (int) ($overdueFine['days_late'] ?? 0),
                'remarks' => 'Overdue fine',
            ],
        };
    }

    private function getEffectiveIssueDuration(Student $student): int
    {
        return (int) ($student->privileges?->issue_duration_days ?: FineSetting::resolveActive()->issue_duration_days);
    }

    private function calculateOverdueFine(IssuedBook $issue, FineSetting $fineSetting, Carbon $returnDate): array
    {
        $dueDate = Carbon::parse($issue->due_date)->startOfDay();
        $returnedOn = $returnDate->copy()->startOfDay();

        if ($returnedOn <= $dueDate) {
            return ['amount' => 0, 'days_late' => 0];
        }

        $daysLate = (int) $dueDate->diffInDays($returnedOn);
        $gracePeriod = (int) ($fineSetting->grace_period_days ?? 0);

        if ($daysLate <= $gracePeriod) {
            return ['amount' => 0, 'days_late' => $daysLate];
        }

        $perDayFine = (float) ($issue->student?->privileges?->per_day_fine ?: $fineSetting->per_day_fine);
        $chargeableDays = $daysLate - $gracePeriod;
        $amount = min($chargeableDays * $perDayFine, (float) $fineSetting->max_fine_amount);

        return [
            'amount' => (float) $amount,
            'days_late' => $daysLate,
        ];
    }

    private function isStudentAllowedToBorrow(Student $student): bool
    {
        return (bool) ($student->privileges?->borrowing_allowed ?? true);
    }

    private function canIssueRequestedCount(Student $student, int $requestedCount): bool
    {
        $maxBooks = (int) ($student->privileges?->max_books ?: FineSetting::resolveActive()->max_books_per_student);
        $currentActiveIssues = IssuedBook::query()
            ->where('student_id', $student->id)
            ->whereNull('return_date')
            ->count();

        return ($currentActiveIssues + $requestedCount) <= $maxBooks;
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
