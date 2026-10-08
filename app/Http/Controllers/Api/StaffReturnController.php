<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\PhysicalCopyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StaffReturnRequest;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\PhysicalBookCopyService;
use App\Services\StudentFineSummaryService;
use App\Support\Currency;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffReturnController extends Controller
{
    public function __construct(private readonly PhysicalBookCopyService $copies) {}

    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Default return rules loaded successfully.',
            'data' => $this->returnRulesPayload(null),
        ]);
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? ''));

        $students = Student::query()
            ->with(['user', 'department', 'privileges'])
            ->withCount(['issuedBooks as active_issues_count' => fn ($builder) => $builder->returnable()])
            ->whereHas('issuedBooks', fn ($builder) => $builder->returnable())
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($studentQuery) use ($query) {
                    $studentQuery
                        ->where('student_id', 'like', "%{$query}%")
                        ->orWhere('roll_no', 'like', "%{$query}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%"))
                        ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$query}%"));
                });
            })
            ->orderByRaw('active_issues_count desc')
            ->limit(20)
            ->get()
            ->map(fn (Student $student) => $this->studentPayload($student))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Students with active issued books fetched successfully.',
            'data' => $students,
        ]);
    }

    public function studentReturnData(int $student): JsonResponse
    {
        $studentModel = Student::query()
            ->with(['user', 'department', 'privileges'])
            ->findOrFail($student);

        $fineSetting = FineSetting::resolveActive();
        $activeIssues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine'])
            ->where('student_id', $studentModel->id)
            ->returnable()
            ->orderBy('due_date')
            ->get()
            ->map(fn (IssuedBook $issue) => $this->issuePayload($issue, $fineSetting))
            ->values();

        $studentModel->setAttribute('active_issues_count', $activeIssues->count());

        return response()->json([
            'success' => true,
            'message' => 'Return data fetched successfully.',
            'data' => [
                'student' => $this->studentPayload($studentModel),
                'active_issues' => $activeIssues,
                'return_rules' => $this->returnRulesPayload($studentModel),
            ],
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        if ($request->filled('items')) {
            return $this->previewPhysicalItems($request);
        }

        $validated = $request->validate([
            'issue_ids' => ['required', 'array', 'min:1'],
            'issue_ids.*' => ['integer', 'distinct'],
            'condition' => ['required', 'in:good,fair,damaged,lost'],
        ]);

        $issues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine'])
            ->whereIn('id', $validated['issue_ids'])
            ->get();

        if ($issues->count() !== count($validated['issue_ids'])) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected issue records were not found.',
            ], 404);
        }

        if ($issues->contains(fn (IssuedBook $issue) => $issue->return_date !== null)) {
            return response()->json([
                'success' => false,
                'message' => 'One or more selected books have already been returned.',
            ], 422);
        }

        if ($issues->pluck('student_id')->unique()->count() !== 1
            || $issues->contains(fn (IssuedBook $issue) => ! in_array($issue->status, IssuedBook::ACTIVE_RETURN_STATUSES, true))) {
            return response()->json(['success' => false, 'message' => 'Select active issues belonging to one borrower.'], 422);
        }
        try {
            foreach ($issues as $issue) {
                if ($issue->book_copy_id) {
                    $this->physicalIssueForReturn($issue->id, $issue->book_copy_id);
                }
            }
        } catch (PhysicalCopyException $exception) {
            return $this->physicalCopyError($exception);
        }

        $condition = $validated['condition'];
        $returnDate = today();
        $items = $issues->map(fn (IssuedBook $issue) => $this->previewItemPayload($issue, $condition, $returnDate))->values();
        $totalFine = (float) $items->sum('book_total');
        $student = $issues->first()?->student;

        return response()->json([
            'success' => true,
            'message' => 'Fine preview fetched successfully.',
            'data' => [
                'total_fine' => $totalFine,
                'selected_count' => $items->count(),
                'condition' => $condition,
                'summary' => $this->previewSummary($items->count(), $condition, $student),
                'items' => $items,
                'rules' => $this->returnRulesPayload($student),
            ],
        ]);
    }

    public function returnBooks(Request $request, NotificationService $notifications): JsonResponse
    {
        // Borrower-scoped requests use independent transactions and report each result.
        // Keep the existing atomic contract for older clients without student_id.
        if ($request->has('student_id')) {
            return $this->returnBorrowerBooks($request, $notifications);
        }

        try {
            if ($request->filled('items')) {
                $validated = $request->validate([
                    'items' => ['required', 'array', 'min:1'],
                    'items.*.issue_id' => ['required', 'integer', 'distinct', 'exists:issued_books,id'],
                    'items.*.book_copy_id' => ['required', 'integer', 'distinct', 'exists:book_copies,id'],
                    'items.*.return_condition' => ['required', 'in:good,fair,damaged,lost'],
                    'items.*.notes' => ['nullable', 'string', 'max:1000'],
                    'items.*.accession_number' => ['nullable', 'string', 'max:100'],
                    'return_date' => ['nullable', 'date'],
                ]);

                $returnedIssues = DB::transaction(function () use ($validated, $request, $notifications) {
                    $studentId = IssuedBook::findOrFail($validated['items'][0]['issue_id'])->student_id;

                    return collect($validated['items'])->map(function (array $item) use ($validated, $request, $notifications, $studentId) {
                        $result = $this->copies->returnIssueById(
                            (int) $item['issue_id'],
                            (int) $item['book_copy_id'],
                            $item['return_condition'],
                            isset($validated['return_date']) ? Carbon::parse($validated['return_date']) : now(),
                            $item['notes'] ?? null,
                            $request->user(),
                            (int) $studentId,
                            $item['accession_number'] ?? null
                        );
                        $notifications->notifyBookReturned(
                            $result['issue'],
                            $item['return_condition'],
                            (float) $result['fine']['amount']
                        );

                        return $result['issue'];
                    })->values();
                });
            } else {
                $validated = $request->validate([
                    'issue_ids' => ['required', 'array', 'min:1'],
                    'issue_ids.*' => ['integer', 'distinct'],
                    'condition' => ['required', 'in:good,fair,damaged,lost'],
                    'notes' => ['nullable', 'string', 'max:1000'],
                ]);

                $condition = $validated['condition'];
                $notes = $validated['notes'] ?? null;
                $user = $request->user();

                $returnedIssues = DB::transaction(function () use ($validated, $condition, $notes, $user, $notifications) {
                    $issues = IssuedBook::query()
                        ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine'])
                        ->whereIn('id', $validated['issue_ids'])
                        ->lockForUpdate()
                        ->get();

                    abort_if($issues->count() !== count($validated['issue_ids']), 404, 'One or more selected issue records were not found.');
                    abort_if($issues->contains(fn (IssuedBook $issue) => $issue->return_date !== null), 422, 'One or more selected books have already been returned.');
                    abort_if($issues->pluck('student_id')->unique()->count() !== 1, 422, 'Select issues belonging to one borrower.');
                    abort_if($issues->contains(fn (IssuedBook $issue) => ! in_array($issue->status, IssuedBook::ACTIVE_RETURN_STATUSES, true)), 422, 'One or more issue records are no longer active.');

                    return $issues->map(function (IssuedBook $issuedBook) use ($condition, $notes, $user, $notifications) {
                        if ($issuedBook->book_copy_id) {
                            $result = $this->copies->returnIssueById(
                                $issuedBook->id,
                                $issuedBook->book_copy_id,
                                $condition,
                                now(),
                                $notes,
                                $user
                            );
                            $notifications->notifyBookReturned($result['issue'], $condition, (float) $result['fine']['amount']);

                            return $result['issue'];
                        }

                        return $this->processLegacyReturnIssue($issuedBook, $condition, now(), $notes, $user, $notifications);
                    })->values();
                });
            }
        } catch (PhysicalCopyException $exception) {
            return $this->physicalCopyError($exception);
        }

        $fineSetting = FineSetting::resolveActive();
        $totalFine = (float) $returnedIssues->sum(fn (IssuedBook $issue) => (float) $issue->fine_amount);

        return response()->json([
            'success' => true,
            'message' => 'Book return processed successfully.',
            'data' => [
                'returned_count' => $returnedIssues->count(),
                'total_fine' => $totalFine,
                'returned_books' => $returnedIssues->map(fn (IssuedBook $issue) => $this->issuePayload($issue, $fineSetting))->values(),
                'fines' => $returnedIssues->map(fn (IssuedBook $issue) => $issue->fine ? [
                    'id' => $issue->fine->id,
                    'amount' => (float) $issue->fine->amount,
                    'days_late' => (int) $issue->fine->days_late,
                    'status' => $issue->fine->status,
                    'remarks' => Currency::normalizeText($issue->fine->remarks),
                ] : null)->filter()->values(),
            ],
        ]);
    }

    public function accession(string $accessionNumber): JsonResponse
    {
        $copy = $this->copies->findByAccession($accessionNumber);

        if (! $copy) {
            return response()->json([
                'success' => false,
                'message' => 'Accession number not found.',
                'code' => 'accession_not_found',
            ], 404);
        }

        if ($copy->status !== 'issued') {
            return response()->json([
                'success' => false,
                'message' => 'This book copy is not currently issued.',
                'code' => 'copy_not_issued',
            ], 422);
        }

        $issue = $this->copies->activeIssueByAccession($copy->accession_number);

        if (! $issue) {
            return response()->json([
                'success' => false,
                'message' => 'This book copy is not currently issued.',
                'code' => 'no_active_issue',
            ], 422);
        }

        $issue->loadMissing(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine']);

        return response()->json([
            'success' => true,
            'message' => 'Issued physical copy fetched successfully.',
            'data' => $this->returnLookupPayload($issue),
        ]);
    }

    public function calculateFine(Request $request): JsonResponse
    {
        if (! $request->filled('return_condition') && $request->filled('condition')) {
            $request->merge(['return_condition' => $request->input('condition')]);
        }

        $validated = $request->validate([
            'issue_id' => ['required', 'integer', 'exists:issued_books,id'],
            'book_copy_id' => ['required', 'integer', 'exists:book_copies,id'],
            'return_condition' => ['required', 'in:good,fair,damaged,lost'],
            'return_date' => ['nullable', 'date'],
        ]);

        try {
            $issue = $this->physicalIssueForReturn(
                (int) $validated['issue_id'],
                (int) $validated['book_copy_id']
            );
            $fine = $this->copies->calculateReturnFine(
                $issue,
                $validated['return_condition'],
                isset($validated['return_date']) ? Carbon::parse($validated['return_date']) : today()
            );
        } catch (PhysicalCopyException $exception) {
            return $this->physicalCopyError($exception);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fine calculated successfully.',
            'data' => [
                'issue_id' => $issue->id,
                'book_copy_id' => $issue->book_copy_id,
                ...$this->fineCalculationPayload($fine),
            ],
        ]);
    }

    public function returnPhysicalBook(Request $request, NotificationService $notifications): JsonResponse
    {
        if (! $request->filled('return_condition') && $request->filled('condition')) {
            $request->merge(['return_condition' => $request->input('condition')]);
        }

        $validated = $request->validate([
            'issue_id' => ['required', 'integer', 'exists:issued_books,id'],
            'book_copy_id' => ['required', 'integer', 'exists:book_copies,id'],
            'return_condition' => ['required', 'in:good,fair,damaged,lost'],
            'return_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $result = $this->copies->returnIssueById(
                (int) $validated['issue_id'],
                (int) $validated['book_copy_id'],
                $validated['return_condition'],
                isset($validated['return_date']) ? Carbon::parse($validated['return_date']) : now(),
                $validated['remarks'] ?? $validated['notes'] ?? null,
                $request->user()
            );
            $notifications->notifyBookReturned(
                $result['issue'],
                $validated['return_condition'],
                (float) $result['fine']['amount']
            );
        } catch (PhysicalCopyException $exception) {
            return $this->physicalCopyError($exception);
        }

        return response()->json([
            'success' => true,
            'message' => 'Book returned successfully.',
            'data' => [
                'issue' => $this->issuePayload($result['issue'], FineSetting::resolveActive()),
                'fine' => $this->fineCalculationPayload($result['fine']),
            ],
        ]);
    }

    private function studentPayload(Student $student): array
    {
        $activeIssuesCount = $student->getAttribute('active_issues_count');
        $photo = $student->user?->profile_photo;

        return [
            'id' => $student->id,
            'user_id' => $student->user_id,
            'name' => $student->user?->name,
            'email' => $student->user?->email,
            'student_id' => $student->student_id,
            'roll_no' => $student->roll_no,
            'symbol_no' => $student->roll_no,
            'department' => $student->department?->name,
            'status' => $student->user?->status,
            'profile_photo' => $photo,
            'profile_photo_url' => $photo ? asset('storage/'.ltrim($photo, '/')) : null,
            'active_issues_count' => $activeIssuesCount !== null
                ? (int) $activeIssuesCount
                : (int) $student->issuedBooks()->returnable()->count(),
            'pending_fine' => app(StudentFineSummaryService::class)->pendingAmount($student),
        ];
    }

    private function returnRulesPayload(?Student $student): array
    {
        $fineSetting = FineSetting::resolveActive();
        $student?->loadMissing('privileges');
        $privileges = $student?->privileges;

        return [
            'issue_duration_days' => (int) ($privileges?->issue_duration_days ?: $fineSetting->issue_duration_days),
            'late_fine_per_day' => (float) ($privileges?->per_day_fine ?: $fineSetting->per_day_fine),
            'grace_days' => (int) ($fineSetting->grace_period_days ?? 0),
            'fine_cap_per_book' => (float) ($fineSetting->max_fine_amount ?? 0),
            'condition_fines' => [
                'good' => 0.0,
                'fair' => (float) ($fineSetting->fair_condition_penalty ?? 0),
                'damaged' => (float) ($fineSetting->damaged_book_penalty ?? 0),
                'lost' => (float) ($fineSetting->lost_book_penalty ?? 0),
            ],
            'source' => $privileges ? 'student_privileges' : 'fine_settings',
            'has_custom_rules' => $privileges !== null,
        ];
    }

    private function previewItemPayload(IssuedBook $issue, string $condition, Carbon $returnDate): array
    {
        $fineSetting = FineSetting::resolveActive();
        $fineData = $this->copies->calculateReturnFine($issue, $condition, $returnDate);
        $overdueDays = $this->overdueDays($issue, $returnDate);
        $graceDays = (int) ($fineSetting->grace_period_days ?? 0);
        $chargeableDays = max($overdueDays - $graceDays, 0);
        $overdueFine = $condition === 'lost'
            ? 0.0
            : $this->estimatedOverdueFine($issue, $fineSetting, $returnDate);
        $conditionFine = match ($condition) {
            'lost' => (float) ($fineSetting->lost_book_penalty ?? 0),
            'damaged' => (float) ($fineSetting->damaged_book_penalty ?? 0),
            'fair' => (float) ($fineSetting->fair_condition_penalty ?? 0),
            default => 0.0,
        };

        return [
            'issue_id' => $issue->id,
            'book_title' => $issue->book?->title,
            'issued_date' => optional($issue->issue_date)->toDateString(),
            'due_date' => optional($issue->due_date)->toDateString(),
            'status' => $overdueDays > 0 ? 'overdue' : 'on_time',
            'overdue_days' => $overdueDays,
            'grace_days' => $graceDays,
            'chargeable_overdue_days' => $chargeableDays,
            'late_fine' => (float) $overdueFine,
            'condition_fine' => (float) $conditionFine,
            'book_total' => (float) $fineData['amount'],
        ];
    }

    private function previewSummary(int $count, string $condition, ?Student $student): string
    {
        $rules = $this->returnRulesPayload($student);
        $conditionFine = $rules['condition_fines'][$condition] ?? 0;

        return "{$count} selected book(s) will use रु {$rules['late_fine_per_day']}/day after {$rules['grace_days']} grace day(s), capped at रु {$rules['fine_cap_per_book']} per book, plus रु {$conditionFine} for ".ucfirst($condition).' condition.';
    }

    private function processLegacyReturnIssue(
        IssuedBook $issuedBook,
        string $condition,
        Carbon $returnDate,
        ?string $remarks,
        $user,
        NotificationService $notifications
    ): IssuedBook {
        if ($issuedBook->return_date !== null || ! in_array($issuedBook->status, IssuedBook::ACTIVE_RETURN_STATUSES, true)) {
            throw new PhysicalCopyException('This issue record is no longer actively issued.', 409, 'issue_not_active');
        }
        $fineData = $this->copies->calculateReturnFine($issuedBook, $condition, $returnDate);

        if ($fineData['amount'] > 0) {
            Fine::updateOrCreate(
                ['issued_book_id' => $issuedBook->id],
                [
                    'student_id' => $issuedBook->student_id,
                    'amount' => $fineData['amount'],
                    'days_late' => $fineData['days_late'],
                    'status' => 'pending',
                    'remarks' => $fineData['remarks'],
                ]
            );
        }

        $issuedBook->update([
            'return_date' => $returnDate,
            'status' => 'returned',
            'condition' => $condition,
            'fine_amount' => $fineData['amount'],
            'remarks' => $remarks ?: $issuedBook->remarks,
        ]);

        BookRequest::query()
            ->where('student_id', $issuedBook->student_id)
            ->where('book_id', $issuedBook->book_id)
            ->where('status', 'issued')
            ->update([
                'status' => 'returned',
                'processed_by' => $user?->name ?? (string) $user?->id,
                'processed_date' => now(),
            ]);

        if (! in_array($condition, ['lost', 'damaged'], true)) {
            $issuedBook->book()->lockForUpdate()->first()?->increment('available_copies');
        }

        $notifications->notifyBookReturned($issuedBook->load(['student.user', 'book']), $condition, (float) $fineData['amount']);

        return $issuedBook->fresh(['student.user', 'student.department', 'student.privileges', 'book.category', 'fine']);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? ''));
        $fineSetting = FineSetting::resolveActive();

        $issues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine'])
            ->whereNull('return_date')
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($issueQuery) use ($query) {
                    $issueQuery
                        ->where('issued_books.id', 'like', "%{$query}%")
                        ->orWhereHas('book', function ($bookQuery) use ($query) {
                            $bookQuery
                                ->where('title', 'like', "%{$query}%")
                                ->orWhere('author', 'like', "%{$query}%")
                                ->orWhere('isbn', 'like', "%{$query}%");
                        })
                        ->orWhereHas('student', function ($studentQuery) use ($query) {
                            $studentQuery
                                ->where('student_id', 'like', "%{$query}%")
                                ->orWhere('roll_no', 'like', "%{$query}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%"));
                        });
                });
            })
            ->orderBy('due_date')
            ->limit(30)
            ->get()
            ->map(fn (IssuedBook $issue) => $this->issuePayload($issue, $fineSetting))
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Active issues fetched successfully.',
            'data' => $issues,
        ]);
    }

    public function returnBook(int $issue, StaffReturnRequest $request, NotificationService $notifications): JsonResponse
    {
        $issuedBook = IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine'])
            ->findOrFail($issue);

        if ($issuedBook->return_date !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This book has already been returned.',
            ], 409);
        }

        $condition = $request->input('condition', 'good');
        $returnDate = $request->date('return_date') ?? now();

        try {
            if ($issuedBook->book_copy_id) {
                $result = $this->copies->returnIssueById(
                    $issuedBook->id,
                    $issuedBook->book_copy_id,
                    $condition,
                    Carbon::parse($returnDate),
                    $request->input('remarks', $request->input('notes')),
                    $request->user()
                );
                $issuedBook = $result['issue'];
                $notifications->notifyBookReturned($issuedBook, $condition, (float) $result['fine']['amount']);
            } else {
                $issuedBook = DB::transaction(function () use ($issuedBook, $condition, $returnDate, $request, $notifications) {
                    return $this->processLegacyReturnIssue(
                        issuedBook: $issuedBook,
                        condition: $condition,
                        returnDate: Carbon::parse($returnDate),
                        remarks: $request->input('remarks', $request->input('notes')),
                        user: $request->user(),
                        notifications: $notifications
                    );
                });
            }
        } catch (PhysicalCopyException $exception) {
            return $this->physicalCopyError($exception);
        }

        return response()->json([
            'success' => true,
            'message' => 'Book returned successfully.',
            'data' => [
                'issue' => $this->issuePayload($issuedBook, FineSetting::resolveActive()),
                'fine' => $issuedBook->fine ? [
                    'id' => $issuedBook->fine->id,
                    'amount' => (float) $issuedBook->fine->amount,
                    'days_late' => (int) $issuedBook->fine->days_late,
                    'status' => $issuedBook->fine->status,
                    'remarks' => Currency::normalizeText($issuedBook->fine->remarks),
                ] : null,
            ],
        ]);
    }

    private function issuePayload(IssuedBook $issue, FineSetting $fineSetting): array
    {
        $overdueDays = $this->overdueDays($issue, today());
        $persistedFine = $issue->relationLoaded('fine') ? $issue->fine : null;

        return [
            'issue_id' => $issue->id,
            'book_id' => $issue->book_id,
            'book_title' => $issue->book?->title,
            'title' => $issue->book?->title,
            'author' => $issue->book?->author,
            'isbn' => $issue->book?->isbn,
            'book_copy_id' => $issue->book_copy_id,
            'accession_number' => $issue->bookCopy?->accession_number,
            'book_type' => $issue->bookCopy?->book_type,
            'copy_condition' => $issue->bookCopy?->condition,
            'shelf_location' => $issue->bookCopy?->shelf_location,
            'copy_status' => $issue->bookCopy?->status,
            'can_return' => IssuedBook::query()->returnable()->whereKey($issue->id)->exists(),
            'student_id' => $issue->student_id,
            'student_name' => $issue->student?->user?->name,
            'student_roll_no' => $issue->student?->roll_no,
            'student' => $this->issueStudentPayload($issue->student),
            'issued_date' => optional($issue->issue_date)->toDateString(),
            'issue_date' => optional($issue->issue_date)->toDateString(),
            'due_date' => optional($issue->due_date)->toDateString(),
            'return_date' => optional($issue->return_date)->toDateString(),
            'overdue_days' => $overdueDays,
            'estimated_fine' => $this->estimatedOverdueFine($issue, $fineSetting, today()),
            'fine_amount' => (float) $issue->fine_amount,
            'current_fine' => $persistedFine ? [
                'id' => $persistedFine->id,
                'amount' => (float) $persistedFine->amount,
                'days_late' => (int) $persistedFine->days_late,
                'status' => $persistedFine->status,
                'remarks' => Currency::normalizeText($persistedFine->remarks),
            ] : null,
            'status' => $issue->status,
            'condition' => $issue->condition,
        ];
    }

    private function physicalIssueForReturn(int $issueId, int $bookCopyId): IssuedBook
    {
        $issue = IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category', 'bookCopy', 'fine'])
            ->find($issueId);

        if (! $issue) {
            throw new PhysicalCopyException('Issue record not found.', 404, 'issue_not_found');
        }

        if ($issue->return_date !== null || $issue->status === 'returned') {
            throw new PhysicalCopyException('This book has already been returned.', 409, 'issue_already_returned');
        }

        if ((int) $issue->book_copy_id !== $bookCopyId) {
            throw new PhysicalCopyException(
                'This physical copy does not belong to the selected issue.',
                422,
                'issue_copy_mismatch'
            );
        }

        if (! $issue->bookCopy) {
            throw new PhysicalCopyException('Book copy not found.', 404, 'book_copy_not_found');
        }

        $this->copies->validateReturnIssue($issue, $issue->bookCopy);

        return $issue;
    }

    private function returnLookupPayload(IssuedBook $issue): array
    {
        return [
            'book' => [
                'book_id' => $issue->book_id,
                'title' => $issue->book?->title,
                'isbn' => $issue->book?->isbn,
                'author' => $issue->book?->author,
            ],
            'physical_copy' => [
                'book_copy_id' => $issue->book_copy_id,
                'accession_number' => $issue->bookCopy?->accession_number,
                'type' => $issue->bookCopy?->book_type,
                'book_type' => $issue->bookCopy?->book_type,
                'condition' => $issue->bookCopy?->condition,
                'shelf_location' => $issue->bookCopy?->shelf_location,
                'status' => $issue->bookCopy?->status,
            ],
            'borrower' => $this->studentPayload($issue->student),
            'issue' => $this->issuePayload($issue, FineSetting::resolveActive()),
        ];
    }

    private function issueStudentPayload(?Student $student): ?array
    {
        if (! $student) {
            return null;
        }

        return [
            'id' => $student->id,
            'student_id' => $student->student_id,
            'roll_no' => $student->roll_no,
            'name' => $student->user?->name,
            'email' => $student->user?->email,
            'department' => $student->department?->name,
        ];
    }

    private function fineCalculationPayload(array $fine): array
    {
        $total = (float) ($fine['total_fine'] ?? $fine['amount'] ?? 0);

        return [
            'overdue_days' => (int) ($fine['overdue_days'] ?? $fine['days_late'] ?? 0),
            'grace_days' => (int) ($fine['grace_days'] ?? 0),
            'chargeable_overdue_days' => (int) ($fine['chargeable_overdue_days'] ?? 0),
            'overdue_fine' => (float) ($fine['overdue_fine'] ?? 0),
            'condition_fine' => (float) ($fine['condition_fine'] ?? 0),
            'total_fine' => $total,
            'amount' => $total,
            'currency' => Currency::CODE,
            'display_value' => Currency::format($total),
            'condition' => $fine['condition'] ?? null,
        ];
    }

    private function validatedPhysicalItems(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.issue_id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.book_copy_id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.accession_number' => ['required', 'string', 'max:100', 'distinct'],
            'items.*.return_condition' => ['required', 'in:good,fair,damaged,lost'],
            'items.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function failedReturnItem(array $item, PhysicalCopyException $exception): array
    {
        return [
            'issue_id' => (int) $item['issue_id'],
            'book_copy_id' => (int) $item['book_copy_id'],
            'accession_number' => $item['accession_number'],
            'return_condition' => $item['return_condition'],
            'return_status' => 'failed',
            'code' => $exception->errorCode,
            'message' => $exception->getMessage(),
            'can_return' => false,
        ];
    }

    private function previewPhysicalItems(Request $request): JsonResponse
    {
        $validated = $this->validatedPhysicalItems($request);
        $items = collect();
        $failures = collect();
        foreach ($validated['items'] as $item) {
            try {
                $issue = $this->physicalIssueForReturn((int) $item['issue_id'], (int) $item['book_copy_id']);
                $this->copies->validateReturnIssue($issue, $issue->bookCopy, (int) $validated['student_id'], $item['accession_number']);
                $items->push([
                    ...$this->previewItemPayload($issue, $item['return_condition'], today()),
                    'accession_number' => $issue->bookCopy->accession_number,
                    'return_condition' => $item['return_condition'],
                ]);
            } catch (PhysicalCopyException $exception) {
                $failures->push($this->failedReturnItem($item, $exception));
            }
        }

        $student = Student::with(['user', 'department', 'privileges'])->findOrFail($validated['student_id']);

        return response()->json([
            'success' => true,
            'message' => 'Selected return items validated.',
            'data' => [
                'selected_count' => $items->count(),
                'total_fine' => (float) $items->sum('book_total'),
                'items' => $items,
                'failed_items' => $failures,
                'rules' => $this->returnRulesPayload($student),
            ],
        ]);
    }

    private function returnBorrowerBooks(Request $request, NotificationService $notifications): JsonResponse
    {
        $validated = $this->validatedPhysicalItems($request);
        $returned = collect();
        $failures = collect();
        $results = collect();
        foreach ($validated['items'] as $item) {
            try {
                $result = DB::transaction(function () use ($item, $validated, $request, $notifications) {
                    $result = $this->copies->returnIssueById(
                        (int) $item['issue_id'],
                        (int) $item['book_copy_id'],
                        $item['return_condition'],
                        now(),
                        $item['notes'] ?? null,
                        $request->user(),
                        (int) $validated['student_id'],
                        $item['accession_number']
                    );
                    $notifications->notifyBookReturned($result['issue'], $item['return_condition'], (float) $result['fine']['amount']);

                    return $result;
                }, 3);
                $returned->push($result['issue']);
                $results->push([
                    'issue_id' => $result['issue']->id,
                    'book_copy_id' => $result['issue']->book_copy_id,
                    'accession_number' => $result['issue']->bookCopy->accession_number,
                    'return_condition' => $item['return_condition'],
                    'return_status' => 'returned',
                    'fine' => [
                        'issue_id' => $result['issue']->id,
                        'book_copy_id' => $result['issue']->book_copy_id,
                        ...$this->fineCalculationPayload($result['fine']),
                    ],
                ]);
            } catch (PhysicalCopyException $exception) {
                $failure = $this->failedReturnItem($item, $exception);
                $failures->push($failure);
                $results->push($failure);
            } catch (\Throwable $exception) {
                report($exception);
                // An unexpected item failure also leaves the rest of the batch independent.
                $failure = [
                    ...$this->failedReturnItem($item, new PhysicalCopyException('Unable to return this copy. Refresh before trying again.', 500, 'return_failed')),
                    'can_return' => true,
                ];
                $failures->push($failure);
                $results->push($failure);
            }
        }

        return response()->json([
            'success' => $failures->isEmpty(),
            'message' => "Returned: {$returned->count()}. Failed: {$failures->count()}.",
            'data' => [
                'returned_count' => $returned->count(),
                'failed_count' => $failures->count(),
                'total_fine' => (float) $returned->sum('fine_amount'),
                'returned_books' => $returned->map(fn (IssuedBook $issue) => $this->issuePayload($issue, FineSetting::resolveActive()))->values(),
                'failed_items' => $failures,
                'results' => $results,
            ],
        ]);
    }

    private function physicalCopyError(PhysicalCopyException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $exception->getMessage(),
            'code' => $exception->errorCode,
        ], $exception->status);
    }

    private function estimatedOverdueFine(IssuedBook $issue, FineSetting $fineSetting, Carbon $date): float
    {
        $daysLate = $this->overdueDays($issue, $date);

        if ($daysLate <= (int) ($fineSetting->grace_period_days ?? 0)) {
            return 0.0;
        }

        $perDayFine = (float) ($issue->student?->privileges?->per_day_fine ?: $fineSetting->per_day_fine);
        $chargeableDays = $daysLate - (int) ($fineSetting->grace_period_days ?? 0);

        return (float) min($chargeableDays * $perDayFine, (float) $fineSetting->max_fine_amount);
    }

    private function overdueDays(IssuedBook $issue, Carbon $date): int
    {
        $dueDate = Carbon::parse($issue->due_date)->startOfDay();
        $date = $date->copy()->startOfDay();

        return $date->greaterThan($dueDate) ? (int) $dueDate->diffInDays($date) : 0;
    }
}
