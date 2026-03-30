<?php

namespace App\Services\StudentManagement;

use App\Models\ActivityLog;
use App\Models\BookRequest;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\IssuedBook;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StudentProfileDataService
{
    public function loadStudent(string $id): Student
    {
        return Student::with([
            'user',
            'department',
            'issuedBooks.book.category',
            'issuedBooks.issuer',
            'issuedBooks.fine',
            'bookRequests.book',
            'fines.issuedBook.book',
            'privileges',
        ])->findOrFail($id);
    }

    public function buildStaffProfile(Student $student): array
    {
        $student->loadMissing([
            'user',
            'department',
            'issuedBooks.book.category',
            'issuedBooks.issuer',
            'issuedBooks.fine',
            'bookRequests.book',
            'fines.issuedBook.book',
            'privileges',
        ]);

        $books = $student->issuedBooks
            ->sortByDesc(function (IssuedBook $issuedBook) {
                return $issuedBook->issue_date ?? $issuedBook->created_at;
            })
            ->map(fn (IssuedBook $issuedBook) => $this->serializeIssuedBook($issuedBook))
            ->values()
            ->all();

        $fines = $student->fines
            ->sortByDesc('created_at')
            ->map(fn (Fine $fine) => $this->serializeFine($fine))
            ->values()
            ->all();

        $requests = $student->bookRequests
            ->sortByDesc(function (BookRequest $bookRequest) {
                return $bookRequest->request_date ?? $bookRequest->created_at;
            })
            ->map(fn (BookRequest $bookRequest) => $this->serializeBookRequest($bookRequest))
            ->values()
            ->all();

        $activities = $this->buildActivityTimeline($student);
        $privileges = $this->buildPrivilegeSummary($student);
        $summary = $this->buildSummary($student, $books, $activities);

        return [
            'summary' => $summary,
            'books' => $books,
            'fines' => $fines,
            'requests' => $requests,
            'activities' => $activities,
            'privileges' => $privileges,
        ];
    }

    protected function buildSummary(Student $student, array $books, array $activities): array
    {
        $issuedCollection = collect($books);
        $lastActivity = collect($activities)->first();

        return [
            'totalIssued' => $issuedCollection->count(),
            'currentlyIssued' => $issuedCollection->where('status', 'issued')->count() + $issuedCollection->where('status', 'overdue')->count(),
            'overdueCount' => $issuedCollection->where('status', 'overdue')->count(),
            'pendingFineTotal' => (float) $student->fines->where('status', 'pending')->sum('amount'),
            'lastActivity' => $lastActivity['displayTime'] ?? 'No activity yet',
        ];
    }

    protected function serializeIssuedBook(IssuedBook $issuedBook): array
    {
        $status = $this->resolveBookStatus($issuedBook);
        $issueDate = $issuedBook->issue_date;
        $dueDate = $issuedBook->due_date;
        $returnDate = $issuedBook->return_date;
        $daysOverdue = $status === 'overdue' && $dueDate
            ? now()->diffInDays($dueDate)
            : 0;
        $fine = $issuedBook->fine;
        $fineAmount = (float) ($fine?->amount ?? $issuedBook->fine_amount ?? 0);

        return [
            'id' => $issuedBook->id,
            'title' => $issuedBook->book?->title ?? 'Unknown',
            'author' => $issuedBook->book?->author ?? 'Unknown Author',
            'isbn' => $issuedBook->book?->isbn ?? 'N/A',
            'category' => $issuedBook->book?->category?->name ?? 'Uncategorized',
            'issueDate' => $issueDate?->format('M d, Y') ?? 'N/A',
            'dueDate' => $dueDate?->format('M d, Y') ?? 'N/A',
            'returnDate' => $returnDate?->format('M d, Y') ?? '-',
            'status' => $status,
            'statusLabel' => ucfirst($status),
            'daysOverdue' => $daysOverdue,
            'fineAmount' => $fineAmount,
            'fineLabel' => 'Rs. ' . number_format($fineAmount, 2),
            'fineStatus' => strtolower((string) ($fine?->status ?? 'n/a')),
            'fineStatusLabel' => $fine ? ucfirst((string) $fine->status) : 'No fine',
            'issuedBy' => $issuedBook->issuer?->name ?? 'System',
            'remarks' => $issuedBook->remarks ?? 'No remarks',
        ];
    }

    protected function serializeFine(Fine $fine): array
    {
        $status = strtolower((string) ($fine->status ?? 'pending'));
        $dueDate = $fine->issuedBook?->due_date;
        $createdAt = $fine->created_at;

        return [
            'id' => $fine->id,
            'bookName' => $fine->issuedBook?->book?->title ?? 'Unknown',
            'dueDate' => $dueDate?->format('M d, Y') ?? 'N/A',
            'daysOverdue' => (int) ($fine->days_late ?? 0),
            'amount' => (float) ($fine->amount ?? 0),
            'amountLabel' => 'Rs. ' . number_format((float) ($fine->amount ?? 0), 2),
            'status' => $status,
            'statusLabel' => ucfirst($status),
            'remarks' => $fine->remarks ?: 'No remarks',
            'createdAt' => $createdAt?->format('M d, Y h:i A') ?? 'N/A',
        ];
    }

    protected function serializeBookRequest(BookRequest $bookRequest): array
    {
        $status = strtolower((string) ($bookRequest->status ?? 'pending'));
        $requestDate = $bookRequest->request_date ?? $bookRequest->created_at;

        return [
            'id' => $bookRequest->id,
            'bookTitle' => $bookRequest->book?->title ?? 'Unknown',
            'requestDate' => $requestDate?->format('M d, Y') ?? 'N/A',
            'requestTime' => $requestDate?->format('M d, Y h:i A') ?? 'N/A',
            'status' => $status,
            'statusLabel' => ucfirst($status),
        ];
    }

    protected function buildPrivilegeSummary(Student $student): array
    {
        $privileges = $student->privileges;
        $defaults = FineSetting::resolveActive();

        return [
            'items' => [
                $this->privilegeItem('Maximum Books', $privileges?->max_books, $defaults->max_books_per_student, ' books'),
                $this->privilegeItem('Issue Duration', $privileges?->issue_duration_days, $defaults->issue_duration_days, ' days'),
                $this->privilegeItem('Fine Rate', $privileges?->per_day_fine, $defaults->per_day_fine, prefix: 'Rs. '),
                $this->privilegeItem('Grace Period', $privileges?->grace_period_days, $defaults->grace_period_days, ' days'),
                $this->privilegeItem('Maximum Fine', $privileges?->max_fine_amount, $defaults->max_fine_amount, prefix: 'Rs. '),
                [
                    'label' => 'Borrowing Permission',
                    'value' => ($privileges?->borrowing_allowed ?? true) ? 'Allowed' : 'Restricted',
                    'source' => $privileges && $privileges->borrowing_allowed !== null ? 'Custom' : 'Default',
                ],
            ],
        ];
    }

    protected function privilegeItem(string $label, $customValue, $defaultValue, string $suffix = '', string $prefix = ''): array
    {
        $hasCustomValue = $customValue !== null;
        $value = $hasCustomValue ? $customValue : $defaultValue;

        if (is_numeric($value) && ($prefix !== '' || Str::contains($suffix, 'books') === false)) {
            $formatted = $prefix . number_format((float) $value, Str::contains($prefix, 'Rs.') ? 2 : 0) . $suffix;
        } else {
            $formatted = $prefix . $value . $suffix;
        }

        return [
            'label' => $label,
            'value' => $formatted,
            'source' => $hasCustomValue ? 'Custom' : 'Default',
        ];
    }

    protected function buildActivityTimeline(Student $student, ?int $limit = null): array
    {
        $issuedBookIds = $student->issuedBooks->pluck('id')->filter();
        $fineIds = $student->fines->pluck('id')->filter();

        $query = ActivityLog::query()
            ->with('user')
            ->where(function (Builder $builder) use ($student) {
                $builder->where('model_type', Student::class)
                    ->where('model_id', $student->id);
            })
            ->orWhere(function (Builder $builder) use ($student) {
                $builder->where('affected_user_id', $student->user_id);
            });

        if ($issuedBookIds->isNotEmpty()) {
            $query->orWhere(function (Builder $builder) use ($issuedBookIds) {
                $builder->where('resource_type', 'issued_book')
                    ->whereIn('resource_id', $issuedBookIds);
            });
        }

        if ($fineIds->isNotEmpty()) {
            $query->orWhere(function (Builder $builder) use ($fineIds) {
                $builder->where('resource_type', 'fine')
                    ->whereIn('resource_id', $fineIds);
            });
        }

        $query->orderByDesc('created_at');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query
            ->get()
            ->map(fn (ActivityLog $activityLog) => $this->serializeActivity($activityLog))
            ->values()
            ->all();
    }

    protected function serializeActivity(ActivityLog $activityLog): array
    {
        $type = $this->activityType($activityLog);
        $role = strtolower((string) ($activityLog->user?->role ?? $activityLog->user_role ?? 'system'));
        $createdAt = $activityLog->created_at;

        return [
            'id' => $activityLog->id,
            'title' => $activityLog->action_name,
            'description' => $activityLog->readable_description,
            'type' => $type,
            'displayTime' => $createdAt?->diffForHumans() ?? 'Unknown time',
            'timestamp' => $createdAt?->format('M d, Y h:i A') ?? 'N/A',
            'actorName' => $activityLog->user?->name ?? $activityLog->user_name ?? 'System',
            'actorRole' => ucfirst($role),
            'roleClass' => in_array($role, ['admin', 'staff', 'student'], true) ? $role : 'system',
        ];
    }

    protected function activityType(ActivityLog $activityLog): string
    {
        $action = strtolower((string) ($activityLog->action ?? ''));
        $category = strtolower((string) ($activityLog->action_category ?? ''));

        return match (true) {
            str_contains($action, 'issue') || $category === 'book' => 'book',
            str_contains($action, 'fine') || $category === 'fine' => 'fine',
            str_contains($action, 'status') || str_contains($action, 'password') || $category === 'auth' => 'account',
            default => 'profile',
        };
    }

    protected function resolveBookStatus(IssuedBook $issuedBook): string
    {
        if ($issuedBook->return_date) {
            return 'returned';
        }

        if ($issuedBook->due_date && $issuedBook->due_date->toDateString() < now()->toDateString()) {
            return 'overdue';
        }

        return 'issued';
    }
}
