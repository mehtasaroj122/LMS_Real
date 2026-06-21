<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StaffReturnRequest;
use App\Jobs\SendBookReturnedEmail;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffReturnController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) ($request->query('query') ?? $request->query('q') ?? ''));
        $fineSetting = FineSetting::resolveActive();

        $issues = IssuedBook::query()
            ->with(['student.user', 'student.department', 'student.privileges', 'book.category'])
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

    public function returnBook(int $issue, StaffReturnRequest $request): JsonResponse
    {
        $issuedBook = IssuedBook::query()
            ->with(['student.user', 'student.privileges', 'book.category'])
            ->findOrFail($issue);

        if ($issuedBook->return_date !== null) {
            return response()->json([
                'success' => false,
                'message' => 'This book has already been returned.',
            ], 409);
        }

        $condition = $request->input('condition', 'good');
        $returnDate = $request->date('return_date') ?? now();

        $issuedBook = DB::transaction(function () use ($issuedBook, $condition, $returnDate, $request) {
            $fineData = $this->calculateReturnFine($issuedBook, $condition, Carbon::parse($returnDate));
            $remarks = $request->input('remarks', $request->input('notes'));

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
                    'processed_by' => $request->user()->name ?? (string) $request->user()->id,
                    'processed_date' => now(),
                ]);

            if (! in_array($condition, ['lost', 'damaged'], true)) {
                $issuedBook->book()->lockForUpdate()->first()?->increment('available_copies');
            }

            ActivityLogger::logBookReturned($issuedBook->student, $issuedBook->book?->title ?? 'Unknown Book', [
                'book_id' => $issuedBook->book_id,
                'issued_book_id' => $issuedBook->id,
                'isbn' => $issuedBook->book?->isbn,
                'condition' => $condition,
                'fine_amount' => $fineData['amount'],
                'source' => 'mobile_staff_api',
            ]);

            $this->notifyReturned($issuedBook, $condition, $fineData['amount']);

            return $issuedBook->fresh(['student.user', 'student.department', 'student.privileges', 'book.category', 'fine']);
        });

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
                    'remarks' => $issuedBook->fine->remarks,
                ] : null,
            ],
        ]);
    }

    private function issuePayload(IssuedBook $issue, FineSetting $fineSetting): array
    {
        $overdueDays = $this->overdueDays($issue, today());

        return [
            'issue_id' => $issue->id,
            'book_id' => $issue->book_id,
            'book_title' => $issue->book?->title,
            'author' => $issue->book?->author,
            'isbn' => $issue->book?->isbn,
            'student_id' => $issue->student_id,
            'student_name' => $issue->student?->user?->name,
            'student_roll_no' => $issue->student?->roll_no,
            'issued_date' => optional($issue->issue_date)->toDateString(),
            'issue_date' => optional($issue->issue_date)->toDateString(),
            'due_date' => optional($issue->due_date)->toDateString(),
            'return_date' => optional($issue->return_date)->toDateString(),
            'overdue_days' => $overdueDays,
            'estimated_fine' => $this->estimatedOverdueFine($issue, $fineSetting, today()),
            'fine_amount' => (float) $issue->fine_amount,
            'status' => $issue->status,
            'condition' => $issue->condition,
        ];
    }

    private function calculateReturnFine(IssuedBook $issue, string $condition, Carbon $returnDate): array
    {
        $fineSetting = FineSetting::resolveActive();
        $overdueAmount = $this->estimatedOverdueFine($issue, $fineSetting, $returnDate);
        $daysLate = $this->overdueDays($issue, $returnDate);

        return match ($condition) {
            'lost' => [
                'amount' => (float) ($fineSetting->lost_book_penalty ?? 0),
                'days_late' => $daysLate,
                'remarks' => 'Lost book penalty',
            ],
            'damaged' => [
                'amount' => (float) $overdueAmount + (float) ($fineSetting->damaged_book_penalty ?? 0),
                'days_late' => $daysLate,
                'remarks' => 'Damaged book penalty + overdue fine',
            ],
            'fair' => [
                'amount' => (float) $overdueAmount + (float) ($fineSetting->fair_condition_penalty ?? 0),
                'days_late' => $daysLate,
                'remarks' => 'Fair condition penalty + overdue fine',
            ],
            default => [
                'amount' => (float) $overdueAmount,
                'days_late' => $daysLate,
                'remarks' => 'Overdue fine',
            ],
        };
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

    private function notifyReturned(IssuedBook $issuedBook, string $condition, float $fineAmount): void
    {
        $student = $issuedBook->student;

        if (! $student?->user) {
            return;
        }

        $title = $fineAmount > 0 ? 'Book Returned with Fine' : 'Book Returned Successfully';
        $message = $fineAmount > 0
            ? "Your return of '{$issuedBook->book?->title}' has been processed. Fine amount: Rs. {$fineAmount}."
            : "Your return of '{$issuedBook->book?->title}' has been accepted.";

        Notification::notify(
            user: $student->user,
            type: $fineAmount > 0 ? 'fine.created' : 'book.returned',
            title: $title,
            message: $message,
            data: [
                'book_id' => $issuedBook->book_id,
                'issued_book_id' => $issuedBook->id,
                'condition' => $condition,
                'fine_amount' => $fineAmount,
            ],
            relatedModel: 'IssuedBook',
            relatedId: $issuedBook->id
        );

        if ($student->user->email) {
            try {
                SendBookReturnedEmail::dispatch(
                    $student->user->email,
                    $student->user->name,
                    $issuedBook->book?->title ?? 'Book',
                    $condition,
                    $fineAmount
                );
            } catch (\Throwable $exception) {
                \Log::warning('Unable to queue book returned email: ' . $exception->getMessage(), [
                    'issued_book_id' => $issuedBook->id,
                ]);
            }
        }
    }
}
