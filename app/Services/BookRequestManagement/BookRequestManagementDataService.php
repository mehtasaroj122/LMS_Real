<?php

namespace App\Services\BookRequestManagement;

use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BookRequestManagementDataService
{
    public function getReferenceData(): array
    {
        return [
            'students' => Student::query()
                ->with('user')
                ->whereHas('user', function (Builder $query) {
                    $query->where('role', 'student');
                })
                ->get()
                ->sortBy(function (Student $student) {
                    return mb_strtolower((string) ($student->user?->name ?? ''));
                })
                ->values(),
            'books' => Book::query()
                ->orderBy('title')
                ->get(),
        ];
    }

    public function getListingData(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters['sort']);

        $paginated = $query->paginate($filters['per_page'], ['*'], 'page', $filters['page']);

        return [
            'requests' => $paginated->getCollection()
                ->map(fn (BookRequest $bookRequest) => $this->serializeRequest($bookRequest))
                ->values()
                ->all(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem() ?? 0,
                'to' => $paginated->lastItem() ?? 0,
            ],
            'stats' => $this->buildStats($filters),
        ];
    }

    public function getStats(array $filters = []): array
    {
        return $this->buildStats($this->normalizeFilters($filters));
    }

    public function getNextPending(array $excludeIds = []): ?array
    {
        $bookRequest = $this->baseQuery()
            ->where('book_requests.status', 'pending')
            ->when(!empty($excludeIds), function (Builder $query) use ($excludeIds) {
                $query->whereNotIn('book_requests.id', $excludeIds);
            })
            ->orderBy('book_requests.request_date', 'desc')
            ->first();

        if (!$bookRequest) {
            return null;
        }

        return [
            'id' => $bookRequest->id,
            'book' => [
                'title' => $bookRequest->book?->title ?? 'Untitled',
                'author' => $bookRequest->book?->author ?? '',
            ],
            'student' => [
                'name' => $bookRequest->student?->user?->name ?? 'Unknown',
                'student_id' => $bookRequest->student?->roll_no ?? '',
            ],
            'request_date' => $bookRequest->request_date?->format('M d, Y') ?? 'N/A',
        ];
    }

    public function serializeRequest(BookRequest $bookRequest): array
    {
        $profilePhoto = $bookRequest->student?->user?->profile_photo;
        $studentAvatar = null;

        if (!empty($profilePhoto)) {
            $studentAvatar = str_starts_with($profilePhoto, 'http')
                ? $profilePhoto
                : asset('storage/' . ltrim($profilePhoto, '/'));
        }

        $status = strtolower((string) ($bookRequest->status ?? 'pending'));

        return [
            'id' => $bookRequest->id,
            'studentName' => $bookRequest->student?->user?->name ?? 'Unknown',
            'studentRoll' => $bookRequest->student?->roll_no ?? 'N/A',
            'studentAvatar' => $studentAvatar,
            'bookTitle' => $bookRequest->book?->title ?? 'Unknown',
            'bookAuthor' => $bookRequest->book?->author ?? '',
            'requestDate' => $bookRequest->request_date?->format('Y-m-d') ?? 'N/A',
            'requestDateRaw' => $bookRequest->request_date?->format('Y-m-d'),
            'status' => $status,
            'statusLabel' => ucfirst($status),
            'processedBy' => $bookRequest->processed_by ?: 'N/A',
            'processedDate' => $bookRequest->processed_date?->format('Y-m-d H:i:s'),
            'canApprove' => $status === 'pending',
            'canReject' => $status === 'pending',
        ];
    }

    protected function normalizeFilters(array $filters): array
    {
        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => strtolower((string) ($filters['status'] ?? 'all')),
            'sort' => strtolower((string) ($filters['sort'] ?? 'date-desc')),
            'page' => max(1, (int) ($filters['page'] ?? 1)),
            'per_page' => max(1, min(50, (int) ($filters['per_page'] ?? 10))),
        ];
    }

    protected function baseQuery(): Builder
    {
        return BookRequest::query()
            ->with(['student.user', 'book'])
            ->whereHas('student.user', function (Builder $query) {
                $query->where('role', 'student');
            });
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearchFilter($query, $filters['search']);
        $this->applyStatusFilter($query, $filters['status']);
    }

    protected function applySearchFilter(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $subQuery) use ($search) {
            $subQuery->whereHas('student.user', function (Builder $userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%");
            })->orWhereHas('student', function (Builder $studentQuery) use ($search) {
                $studentQuery->where('roll_no', 'like', "%{$search}%");
            })->orWhereHas('book', function (Builder $bookQuery) use ($search) {
                $bookQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            })->orWhere('book_requests.request_date', 'like', "%{$search}%");
        });
    }

    protected function applyStatusFilter(Builder $query, string $status): void
    {
        if ($status === '' || $status === 'all') {
            return;
        }

        $query->where('book_requests.status', $status);
    }

    protected function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'date-asc' => $query->orderBy('book_requests.request_date', 'asc'),
            'student-asc' => $query
                ->leftJoin('students', 'book_requests.student_id', '=', 'students.id')
                ->leftJoin('users', 'students.user_id', '=', 'users.id')
                ->select('book_requests.*')
                ->orderBy('users.name', 'asc'),
            'book-asc' => $query
                ->leftJoin('books', 'book_requests.book_id', '=', 'books.id')
                ->select('book_requests.*')
                ->orderBy('books.title', 'asc'),
            default => $query->orderBy('book_requests.request_date', 'desc'),
        };
    }

    protected function buildStats(array $filters): array
    {
        $statsQuery = $this->baseQuery();
        $this->applyFilters($statsQuery, $filters);

        $pendingQuery = (clone $statsQuery)->where('book_requests.status', 'pending');
        $approvedQuery = (clone $statsQuery)->where('book_requests.status', 'approved');
        $rejectedQuery = (clone $statsQuery)->where('book_requests.status', 'rejected');

        $totalCount = (clone $statsQuery)->count();
        $pendingCount = (clone $pendingQuery)->count();
        $approvedCount = (clone $approvedQuery)->count();
        $rejectedCount = (clone $rejectedQuery)->count();

        return [
            'totalCount' => $totalCount,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
            'pendingMeta' => $this->buildPendingMeta($pendingQuery, $pendingCount),
            'approvedMeta' => $this->buildApprovedMeta($approvedQuery, $approvedCount),
            'rejectedMeta' => $this->buildRejectedMeta($rejectedQuery, $rejectedCount),
        ];
    }

    protected function buildPendingMeta(Builder $query, int $count): string
    {
        if ($count === 0) {
            return 'No requests waiting in this view';
        }

        $oldestRequestDate = (clone $query)->orderBy('book_requests.request_date')->value('book_requests.request_date');
        $studentCount = (clone $query)->distinct('book_requests.student_id')->count('book_requests.student_id');

        $parts = [];

        if ($oldestRequestDate) {
            $parts[] = 'Oldest from ' . $this->formatStatDate($oldestRequestDate);
        }

        if ($studentCount > 0) {
            $parts[] = $studentCount . ' ' . Str::plural('student', $studentCount) . ' in queue';
        }

        return implode(' • ', $parts);
    }

    protected function buildApprovedMeta(Builder $query, int $count): string
    {
        if ($count === 0) {
            return 'No approved requests in this view';
        }

        $studentCount = (clone $query)->distinct('book_requests.student_id')->count('book_requests.student_id');
        $weekCount = (clone $query)->where('book_requests.processed_date', '>=', now()->startOfWeek())->count();
        $latestProcessedDate = (clone $query)->orderByDesc('book_requests.processed_date')->value('book_requests.processed_date');

        $parts = [];

        if ($studentCount > 0) {
            $parts[] = 'Approved for ' . $studentCount . ' ' . Str::plural('student', $studentCount);
        }

        if ($weekCount > 0) {
            $parts[] = $weekCount . ' this week';
        } elseif ($latestProcessedDate) {
            $parts[] = 'Latest on ' . $this->formatStatDate($latestProcessedDate);
        }

        return implode(' • ', $parts);
    }

    protected function buildRejectedMeta(Builder $query, int $count): string
    {
        if ($count === 0) {
            return 'No rejected requests in this view';
        }

        $titleCount = (clone $query)->distinct('book_requests.book_id')->count('book_requests.book_id');
        $weekCount = (clone $query)->where('book_requests.processed_date', '>=', now()->startOfWeek())->count();
        $latestProcessedDate = (clone $query)->orderByDesc('book_requests.processed_date')->value('book_requests.processed_date');

        $parts = [];

        if ($titleCount > 0) {
            $parts[] = 'Across ' . $titleCount . ' ' . Str::plural('title', $titleCount);
        }

        if ($weekCount > 0) {
            $parts[] = $weekCount . ' this week';
        } elseif ($latestProcessedDate) {
            $parts[] = 'Latest on ' . $this->formatStatDate($latestProcessedDate);
        }

        return implode(' • ', $parts);
    }

    protected function formatStatDate(mixed $value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('M j');
        }

        return Carbon::parse($value)->format('M j');
    }
}
