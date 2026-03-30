<?php

namespace App\Services\StudentManagement;

use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentManagementDataService
{
    public function getDepartments()
    {
        return Department::query()->orderBy('name')->get();
    }

    public function getPaginatedStudents(array $filters = []): LengthAwarePaginator
    {
        $filters = $this->normalizeFilters($filters);

        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);
        $this->applySorting($query, $filters);

        return $query->paginate($filters['per_page'], ['*'], 'page', $filters['page']);
    }

    public function findStudentById(string|int $id, array $relations = ['user', 'department']): Student
    {
        return Student::query()
            ->with($relations)
            ->whereKey($id)
            ->whereHas('user', function (Builder $builder) {
                $builder->where('role', 'student');
            })
            ->firstOrFail();
    }

    public function getListingData(array $filters = [], array $options = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $students = $this->getPaginatedStudents($filters);

        return [
            'students' => $students->getCollection()
                ->map(fn (Student $student) => $this->serializeStudent($student, $options))
                ->values()
                ->all(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem() ?? 0,
                'to' => $students->lastItem() ?? 0,
            ],
            'stats' => $this->getStats($filters),
        ];
    }

    public function getStats(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);
        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);

        $totalStudents = (clone $query)->count();
        $activeStudents = (clone $query)
            ->whereHas('user', function (Builder $builder) {
                $builder->where('status', 'active');
            })
            ->count();
        $inactiveStudents = (clone $query)
            ->whereHas('user', function (Builder $builder) {
                $builder->where('status', 'inactive');
            })
            ->count();

        return [
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
            'updatedAt' => now()->format('M d, Y h:i A'),
        ];
    }

    public function serializeStudent(Student $student, array $options = []): array
    {
        $status = strtolower((string) ($student->user?->status ?? 'inactive'));
        $name = (string) ($student->user?->name ?? 'Unknown');
        $profilePhoto = $student->user?->profile_photo;

        return [
            'id' => $student->id,
            'name' => $name,
            'initials' => $this->initials($name),
            'rollNo' => $student->roll_no ?? 'N/A',
            'email' => $student->user?->email ?? 'N/A',
            'phone' => $student->user?->phone ?? 'N/A',
            'department' => $student->department?->name ?? 'N/A',
            'batch' => $student->batch ?? 'N/A',
            'semester' => $student->semester ?? 'N/A',
            'status' => $status,
            'statusLabel' => ucfirst($status),
            'avatar' => $this->profilePhotoUrl($profilePhoto),
            'createdAt' => optional($student->created_at)->format('M d, Y'),
            'canView' => true,
            'canToggleStatus' => (bool) ($options['can_toggle_status'] ?? false),
        ];
    }

    protected function normalizeFilters(array $filters): array
    {
        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'department' => (string) ($filters['department'] ?? 'all'),
            'status' => strtolower((string) ($filters['status'] ?? 'all')),
            'sort' => strtolower((string) ($filters['sort'] ?? 'created-desc')),
            'page' => max(1, (int) ($filters['page'] ?? 1)),
            'per_page' => max(5, min(50, (int) ($filters['per_page'] ?? 10))),
        ];
    }

    protected function baseQuery(): Builder
    {
        return Student::query()
            ->with(['user', 'department'])
            ->whereHas('user', function (Builder $builder) {
                $builder->where('role', 'student');
            });
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function (Builder $builder) use ($search) {
                $builder->whereHas('user', function (Builder $userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('roll_no', 'like', "%{$search}%");
            });
        }

        if ($filters['department'] !== '' && $filters['department'] !== 'all') {
            $query->where('department_id', (int) $filters['department']);
        }

        if ($filters['status'] !== '' && $filters['status'] !== 'all') {
            $status = $filters['status'];

            $query->whereHas('user', function (Builder $builder) use ($status) {
                $builder->where('status', $status);
            });
        }
    }

    protected function applySorting(Builder $query, array $filters): void
    {
        $sort = $filters['sort'] ?? 'created-desc';

        if ($sort === 'created-asc') {
            $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
            return;
        }

        if ($sort === 'name-asc') {
            $query->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'students.user_id')
                    ->limit(1),
                'asc'
            )->orderBy('created_at', 'desc');
            return;
        }

        if ($sort === 'name-desc') {
            $query->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'students.user_id')
                    ->limit(1),
                'desc'
            )->orderBy('created_at', 'desc');
            return;
        }

        $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    protected function initials(string $name): string
    {
        $parts = collect(preg_split('/\s+/', trim($name)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn (string $part) => strtoupper(substr($part, 0, 1)));

        return $parts->isEmpty() ? 'ST' : $parts->implode('');
    }

    protected function profilePhotoUrl(?string $profilePhoto): ?string
    {
        if (blank($profilePhoto)) {
            return null;
        }

        if (str_starts_with($profilePhoto, 'http')) {
            return $profilePhoto;
        }

        return asset(str_starts_with($profilePhoto, 'storage/') ? $profilePhoto : 'storage/' . ltrim($profilePhoto, '/'));
    }
}
