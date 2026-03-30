<?php

namespace App\Services\BookRequestManagement;

use App\Models\Book;
use App\Models\BookRequest;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

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
            ->where('status', 'pending')
            ->when(!empty($excludeIds), function (Builder $query) use ($excludeIds) {
                $query->whereNotIn('id', $excludeIds);
            })
            ->orderBy('request_date', 'desc')
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
            })->orWhere('request_date', 'like', "%{$search}%");
        });
    }

    protected function applyStatusFilter(Builder $query, string $status): void
    {
        if ($status === '' || $status === 'all') {
            return;
        }

        $query->where('status', $status);
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

        return [
            'totalCount' => (clone $statsQuery)->count(),
            'pendingCount' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approvedCount' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejectedCount' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];
    }
}
