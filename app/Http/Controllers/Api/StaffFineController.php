<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StaffFinePaymentRequest;
use App\Http\Requests\Api\StaffFineWaiveRequest;
use App\Models\Fine;
use App\Models\IssuedBook;
use App\Models\Student;
use App\Services\FineCalculator;
use App\Services\FineManagement\FineManagementActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffFineController extends Controller
{
    public function summary(FineCalculator $fineCalculator): JsonResponse
    {
        $this->syncOverdueFines($fineCalculator);

        $baseQuery = Fine::query()->whereHas('student.user', fn ($query) => $query->where('role', 'student'));

        return response()->json([
            'success' => true,
            'message' => 'Fine summary fetched successfully.',
            'data' => [
                'total_fines' => $this->money((clone $baseQuery)->sum('amount')),
                'collected' => $this->money((clone $baseQuery)->where('status', 'paid')->sum('amount')),
                'pending' => $this->money((clone $baseQuery)->where('status', 'pending')->sum('amount')),
                'waived' => $this->money((clone $baseQuery)->where('status', 'waived')->sum('amount')),
                'total_records' => (int) (clone $baseQuery)->count(),
                'paid_records' => (int) (clone $baseQuery)->where('status', 'paid')->count(),
                'pending_records' => (int) (clone $baseQuery)->where('status', 'pending')->count(),
                'waived_records' => (int) (clone $baseQuery)->where('status', 'waived')->count(),
                'overdue_records' => (int) (clone $baseQuery)->where('days_late', '>', 0)->count(),
            ],
        ]);
    }

    public function students(Request $request, FineCalculator $fineCalculator): JsonResponse
    {
        $this->syncOverdueFines($fineCalculator);

        $fines = $this->studentFineQuery($request)
            ->get()
            ->groupBy('student_id')
            ->map(function (Collection $studentFines) {
                /** @var Fine $firstFine */
                $firstFine = $studentFines->first();
                $student = $firstFine->student;
                $summary = $this->studentSummaryPayload($studentFines);

                return [
                    ...$this->studentPayload($student),
                    'total_fine' => $summary['total_fine'],
                    'pending_amount' => $summary['pending_amount'],
                    'paid_amount' => $summary['paid_amount'],
                    'waived_amount' => $summary['waived_amount'],
                    'fine_records_count' => $summary['records_count'],
                    'pending_records_count' => $summary['pending_records'],
                    'paid_records_count' => $summary['paid_records'],
                    'waived_records_count' => $summary['waived_records'],
                    'overdue_records_count' => $summary['overdue_records'],
                    'status' => $this->studentFineStatus($studentFines),
                ];
            })
            ->sortBy(fn (array $student) => strtolower((string) ($student['name'] ?? '')))
            ->values();

        return response()->json([
            'success' => true,
            'message' => $fines->isEmpty()
                ? 'No fine records found.'
                : 'Student fine records fetched successfully.',
            'data' => $fines,
        ]);
    }

    public function studentDetails(int $student, FineCalculator $fineCalculator): JsonResponse
    {
        $studentModel = Student::query()
            ->with(['user', 'department'])
            ->findOrFail($student);

        $this->syncOverdueFines($fineCalculator, $studentModel);

        $fines = Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->where('student_id', $studentModel->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Student fine detail fetched successfully.',
            'data' => [
                'student' => $this->studentPayload($studentModel),
                'summary' => $this->studentSummaryPayload($fines),
                'fines' => $fines->map(fn (Fine $fine) => $this->finePayload($fine))->values(),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $fines = $this->filteredQuery($request)
            ->latest()
            ->paginate($this->perPage($request));

        return response()->json([
            'success' => true,
            'message' => 'Fines fetched successfully.',
            'data' => $fines->getCollection()->map(fn (Fine $fine) => $this->finePayload($fine))->values(),
            'meta' => [
                'current_page' => $fines->currentPage(),
                'last_page' => $fines->lastPage(),
                'per_page' => $fines->perPage(),
                'total' => $fines->total(),
            ],
        ]);
    }

    public function show(int $fine): JsonResponse
    {
        $fineModel = Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->findOrFail($fine);

        return response()->json([
            'success' => true,
            'message' => 'Fine fetched successfully.',
            'data' => $this->finePayload($fineModel),
        ]);
    }

    public function pay(StaffFinePaymentRequest $request, int $fine, FineManagementActionService $service): JsonResponse
    {
        $fineModel = Fine::query()
            ->with(['student.user', 'student.privileges', 'issuedBook.book.category'])
            ->findOrFail($fine);

        if ($this->mobileStatus($fineModel->status) === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'This fine is already paid.',
            ], 422);
        }

        if ($this->mobileStatus($fineModel->status) === 'waived') {
            return response()->json([
                'success' => false,
                'message' => 'Waived fine cannot be marked as paid.',
            ], 422);
        }

        if ($request->filled('payment_method')) {
            $fineModel->forceFill(['payment_method' => $request->input('payment_method')])->save();
        }

        try {
            $updatedFine = DB::transaction(fn () => $service->markAsPaid($fineModel, [
                'notify_student' => true,
                'log_email' => true,
            ]));
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?: 'Only pending fines can be updated.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fine marked as paid successfully.',
            'data' => $this->finePayload($updatedFine->loadMissing(['student.user', 'student.department', 'issuedBook.book.category'])),
        ]);
    }

    public function waive(StaffFineWaiveRequest $request, int $fine, FineManagementActionService $service): JsonResponse
    {
        $fineModel = Fine::query()
            ->with(['student.user', 'student.privileges', 'issuedBook.book.category'])
            ->findOrFail($fine);

        if ($this->mobileStatus($fineModel->status) === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid fine cannot be waived.',
            ], 422);
        }

        if ($this->mobileStatus($fineModel->status) === 'waived') {
            return response()->json([
                'success' => false,
                'message' => 'This fine is already waived.',
            ], 422);
        }

        try {
            $updatedFine = DB::transaction(fn () => $service->waive($fineModel, $request->string('reason')->toString(), [
                'notify_student' => true,
                'log_email' => true,
            ]));
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first() ?: 'Unable to waive this fine.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Fine waived successfully.',
            'data' => $this->finePayload($updatedFine->loadMissing(['student.user', 'student.department', 'issuedBook.book.category'])),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? $request->query('search') ?? ''));

        return Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->query('status')))
            ->when($request->filled('student'), fn ($builder) => $builder->where('student_id', $request->query('student')))
            ->when($request->filled('student_id'), fn ($builder) => $builder->where('student_id', $request->query('student_id')))
            ->when($request->filled('date'), fn ($builder) => $builder->whereDate('created_at', $request->query('date')))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($fineQuery) use ($query) {
                    $fineQuery
                        ->where('fines.id', 'like', "%{$query}%")
                        ->orWhere('remarks', 'like', "%{$query}%")
                        ->orWhereHas('student', function ($studentQuery) use ($query) {
                            $studentQuery
                                ->where('student_id', 'like', "%{$query}%")
                                ->orWhere('roll_no', 'like', "%{$query}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%"));
                        })
                        ->orWhereHas('issuedBook.book', fn ($bookQuery) => $bookQuery->where('title', 'like', "%{$query}%")->orWhere('isbn', 'like', "%{$query}%"));
                });
            });
    }

    private function studentFineQuery(Request $request)
    {
        $query = trim((string) ($request->query('search') ?? $request->query('query') ?? $request->query('q') ?? ''));

        return Fine::query()
            ->with(['student.user', 'student.department', 'issuedBook.book.category'])
            ->whereHas('student.user', fn ($userQuery) => $userQuery->where('role', 'student'))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($fineQuery) use ($query) {
                    $fineQuery
                        ->where('fines.id', 'like', "%{$query}%")
                        ->orWhere('remarks', 'like', "%{$query}%")
                        ->orWhere('waive_reason', 'like', "%{$query}%")
                        ->orWhereHas('student', function ($studentQuery) use ($query) {
                            $studentQuery
                                ->where('student_id', 'like', "%{$query}%")
                                ->orWhere('roll_no', 'like', "%{$query}%")
                                ->orWhereHas('user', function ($userQuery) use ($query) {
                                    $userQuery
                                        ->where('name', 'like', "%{$query}%")
                                        ->orWhere('email', 'like', "%{$query}%");
                                })
                                ->orWhereHas('department', fn ($departmentQuery) => $departmentQuery->where('name', 'like', "%{$query}%"));
                        })
                        ->orWhereHas('issuedBook.book', function ($bookQuery) use ($query) {
                            $bookQuery
                                ->where('title', 'like', "%{$query}%")
                                ->orWhere('author', 'like', "%{$query}%")
                                ->orWhere('isbn', 'like', "%{$query}%");
                        });
                });
            });
    }

    private function finePayload(Fine $fine): array
    {
        $issue = $fine->issuedBook;
        $book = $issue?->book;
        $student = $fine->student;
        $cover = $book?->cover_image;
        $coverUrl = $cover
            ? (str_starts_with($cover, 'http') ? $cover : asset('storage/' . ltrim($cover, '/')))
            : null;

        return [
            'id' => $fine->id,
            'fine_id' => $fine->id,
            'student_id' => $fine->student_id,
            'user_id' => $student?->user_id,
            'student_name' => $student?->user?->name,
            'student_email' => $student?->user?->email,
            'student_roll_no' => $student?->roll_no,
            'book_id' => $book?->id,
            'book_title' => $book?->title,
            'author' => $book?->author,
            'isbn' => $book?->isbn,
            'cover_image' => $cover,
            'cover_image_url' => $coverUrl,
            'amount' => (float) $fine->amount,
            'status' => $this->mobileStatus($fine->status),
            'reason' => $fine->remarks,
            'fine_type' => $fine->remarks,
            'remarks' => $fine->remarks,
            'waive_reason' => $fine->waive_reason,
            'days_late' => (int) $fine->days_late,
            'days_overdue' => (int) $fine->days_late,
            'issue_id' => $fine->issued_book_id,
            'due_date' => optional($issue?->due_date)->toDateString(),
            'return_date' => optional($issue?->return_date)->toDateString(),
            'paid_at' => optional($fine->paid_at ?? $fine->paid_on)->toDateTimeString(),
            'paid_date' => optional($fine->paid_on)->toDateString(),
            'paid_by' => $fine->paid_by,
            'waived_at' => optional($fine->waived_at)->toDateTimeString(),
            'waived_by' => $fine->waived_by,
            'payment_method' => $fine->payment_method,
            'created_at' => optional($fine->created_at)->toDateTimeString(),
            'updated_at' => optional($fine->updated_at)->toDateTimeString(),
        ];
    }

    private function syncOverdueFines(FineCalculator $fineCalculator, ?Student $student = null): void
    {
        $overdueIssuedBooks = IssuedBook::query()
            ->with(['student.privileges', 'fine'])
            ->when($student, fn ($query) => $query->where('student_id', $student->id))
            ->whereNull('return_date')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        foreach ($overdueIssuedBooks as $issuedBook) {
            $calculation = $fineCalculator->calculateFine($issuedBook);
            $amount = (float) ($calculation['amount'] ?? 0);
            $daysLate = (int) ($calculation['days_late'] ?? 0);

            if (!$calculation || $amount <= 0) {
                $issuedBook->forceFill(['fine_amount' => $amount])->save();
                continue;
            }

            $existingFine = $issuedBook->fine;

            if ($existingFine) {
                if ($this->mobileStatus($existingFine->status) === 'pending') {
                    $existingFine->forceFill([
                        'amount' => $amount,
                        'days_late' => $daysLate,
                        'remarks' => $existingFine->remarks ?: 'Overdue fine',
                    ])->save();

                    $issuedBook->forceFill([
                        'fine_amount' => $amount,
                        'status' => 'overdue',
                    ])->save();
                }

                continue;
            }

            Fine::query()->create([
                'issued_book_id' => $issuedBook->id,
                'student_id' => $issuedBook->student_id,
                'amount' => $amount,
                'days_late' => $daysLate,
                'status' => 'pending',
                'remarks' => 'Overdue fine',
            ]);

            $issuedBook->forceFill([
                'fine_amount' => $amount,
                'status' => 'overdue',
            ])->save();
        }
    }

    private function studentPayload(?Student $student): array
    {
        $photo = $student?->user?->profile_photo;
        $photoUrl = $photo
            ? (str_starts_with($photo, 'http') ? $photo : asset('storage/' . ltrim($photo, '/')))
            : null;

        return [
            'student_id' => $student?->id,
            'id' => $student?->id,
            'user_id' => $student?->user_id,
            'name' => $student?->user?->name,
            'email' => $student?->user?->email,
            'roll_no' => $student?->roll_no,
            'symbol_no' => $student?->roll_no,
            'student_code' => $student?->student_id,
            'department' => $student?->department?->name,
            'photo' => $photoUrl,
            'profile_photo' => $photo,
            'profile_photo_url' => $photoUrl,
        ];
    }

    private function studentSummaryPayload(Collection $fines): array
    {
        return [
            'total_fine' => $this->money($fines->sum('amount')),
            'pending_amount' => $this->money($fines->where('status', 'pending')->sum('amount')),
            'paid_amount' => $this->money($fines->where('status', 'paid')->sum('amount')),
            'waived_amount' => $this->money($fines->where('status', 'waived')->sum('amount')),
            'records_count' => $fines->count(),
            'pending_records' => $fines->where('status', 'pending')->count(),
            'paid_records' => $fines->where('status', 'paid')->count(),
            'waived_records' => $fines->where('status', 'waived')->count(),
            'overdue_records' => $fines->filter(fn (Fine $fine) => (int) $fine->days_late > 0)->count(),
        ];
    }

    private function studentFineStatus(Collection $fines): string
    {
        $summary = $this->studentSummaryPayload($fines);

        if ($summary['pending_amount'] > 0) {
            return 'pending';
        }

        $statuses = $fines
            ->map(fn (Fine $fine) => $this->mobileStatus($fine->status))
            ->filter()
            ->unique()
            ->values();

        return $statuses->count() === 1 ? (string) $statuses->first() : 'mixed';
    }

    private function mobileStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'unpaid' => 'pending',
            'paid' => 'paid',
            'waived' => 'waived',
            default => 'pending',
        };
    }

    private function money(mixed $amount): float
    {
        return round((float) $amount, 2);
    }

    private function perPage(Request $request): int
    {
        return min(max((int) $request->input('per_page', 20), 1), 100);
    }
}
