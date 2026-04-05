<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\staff;
use App\Models\student;
use App\Models\User;
use App\Helpers\ActivityLogger;
use App\Mail\PasswordResetEmail;
use App\Services\Auth\InvitationEmailService;
use App\Services\StudentManagement\StudentNotificationEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(
        private StudentNotificationEmailService $studentNotificationEmailService,
        private InvitationEmailService $invitationEmailService,
    )
    {
        Gate::authorize('access-admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentUserId = auth()->id();
        [
            'search' => $search,
            'status' => $status,
            'role' => $role,
            'sort' => $sort,
            'per_page' => $perPage,
        ] = $this->extractUserFilters($request);

        $users = $this->buildUserQuery($search, $status, $role, $sort, $currentUserId)
            ->with([
            'student.department',
            'staff.department'
        ])
            ->paginate($perPage)
            ->appends($request->query());

        /* ===== Stats ===== */
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();

        $roleCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        // Get departments for the form
        $departments = \App\Models\Department::all();

        return view('admin.UserManagement', compact(
            'users',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'roleCounts',
            'departments',
            'search',
            'status',
            'role',
            'sort',
            'perPage'
        ));
    }

    /**
     * Get users data for AJAX requests (with filtering, search, pagination)
     */
    public function getUsersData(Request $request)
    {
        try {
            $currentUserId = auth()->id();
            [
                'search' => $search,
                'status' => $status,
                'role' => $role,
                'sort' => $sort,
                'page' => $page,
                'per_page' => $perPage,
            ] = $this->extractUserFilters($request);

            $users = $this->buildUserQuery($search, $status, $role, $sort, $currentUserId)
                ->paginate($perPage, ['*'], 'page', $page)
                ->appends($request->query());

            // Load relationships for each user
            $users->load('student.department', 'staff.department');

            // Get stats for current filters
            $stats = $this->getUserStats($search, $status, $role);

            // Prepare HTML for table rows
            $tableRows = '';
            foreach ($users as $user) {
                $tableRows .= view('admin.partials.user-row', compact('user'))->render();
            }

            // Prepare pagination HTML
            $pagination = view('shared.admin-table-pagination', ['paginator' => $users])->render();

            return response()->json([
                'success' => true,
                'tableRows' => $tableRows,
                'pagination' => $pagination,
                'stats' => $stats,
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]);

        } catch (\Exception $e) {
            \Log::error('getUsersData error: ' . $e->getMessage() . ' ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current statistics (for live stat updates)
     * Returns GLOBAL counts plus filtered counts based on current filters
     */
    public function getStats(Request $request)
    {
        try {
            // Get filter parameters from request
            $search = $request->input('search', '');
            $status = $request->input('status', 'all');
            $role = $request->input('role', 'all');

            // Build base query for global stats
            $baseQuery = User::query();
            
            // Create filtered query with all filters applied
            $filteredQuery = User::query();
            
            // Apply search filter
            if (!empty($search)) {
                $filteredQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            // Apply status filter to filtered query only
            if ($status !== 'all') {
                $filteredQuery->where('status', $status);
            }

            // Apply role filter to filtered query
            if ($role !== 'all') {
                $filteredQuery->where('role', $role);
            }

            // Get role counts for filtered results
            $roleCounts = $filteredQuery->clone()
                ->select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray();

            // Return both global and filtered stats
            $stats = [
                'totalUsers' => $baseQuery->count(),
                'activeUsers' => (clone $baseQuery)->where('status', 'active')->count(),
                'inactiveUsers' => (clone $baseQuery)->where('status', 'inactive')->count(),
                'roleCounts' => $roleCounts,
                'filteredTotal' => $filteredQuery->count(),
                'filteredActive' => $filteredQuery->clone()->where('status', 'active')->count(),
                'filteredInactive' => $filteredQuery->clone()->where('status', 'inactive')->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            \Log::error('getStats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics with optional filters
     */
    private function getUserStats($search = '', $status = 'all', $role = 'all')
    {
        // Build queries for stats
        $baseQuery = User::query();
        
        // Create filtered query with all filters applied
        $filteredQuery = User::query();
        
        // Apply search filter
        if (!empty($search)) {
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter to filtered query only
        if ($status !== 'all') {
            $filteredQuery->where('status', $status);
        }

        // Apply role filter to filtered query
        if ($role !== 'all') {
            $filteredQuery->where('role', $role);
        }

        // Get role counts for filtered results - clone to avoid modifying the query builder
        $roleCounts = $filteredQuery->clone()
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return [
            'totalUsers' => $baseQuery->count(),
            'activeUsers' => (clone $baseQuery)->where('status', 'active')->count(),
            'inactiveUsers' => (clone $baseQuery)->where('status', 'inactive')->count(),
            'roleCounts' => $roleCounts,
            'filteredTotal' => $filteredQuery->count(),
            'filteredActive' => $filteredQuery->clone()->where('status', 'active')->count(),
            'filteredInactive' => $filteredQuery->clone()->where('status', 'inactive')->count(),
        ];
    }

    private function extractUserFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->input('search', '')),
            'status' => (string) $request->input('status', 'all'),
            'role' => (string) $request->input('role', 'all'),
            'sort' => (string) $request->input('sort', 'recently-added'),
            'page' => max(1, (int) $request->input('page', 1)),
            'per_page' => $this->normalizeAdminPerPage($request->input('per_page', 10)),
        ];
    }

    private function buildUserQuery(string $search, string $status, string $role, string $sort, int $currentUserId)
    {
        $query = User::query();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('staff', function ($staffQuery) use ($search) {
                        $staffQuery->where('staff_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where(function ($identifierQuery) use ($search) {
                            $identifierQuery
                                ->where('student_id', 'like', "%{$search}%")
                                ->orWhere('roll_no', 'like', "%{$search}%");
                        });
                    });
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($role !== 'all') {
            $query->where('role', $role);
        }

        $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$currentUserId]);
        $query->orderByRaw("CASE WHEN role='admin' THEN 1 WHEN role='staff' THEN 2 WHEN role='student' THEN 3 END");

        switch ($sort) {
            case 'name-asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name-desc':
                $query->orderBy('name', 'desc');
                break;
            case 'recently-added':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    private function normalizeAdminPerPage($value): int
    {
        $allowedValues = [10, 20, 50, 100];
        $perPage = (int) $value;

        return in_array($perPage, $allowedValues, true) ? $perPage : 10;
    }

    public function validateField(Request $request)
    {
        Gate::authorize('access-admin');

        $field = (string) $request->input('field');
        $allowedFields = [
            'name',
            'email',
            'role',
            'phone',
            'gender',
            'address',
            'department_id',
            'staff_id',
            'designation',
            'join_date',
            'roll_no',
            'batch',
            'semester',
            'status',
        ];

        if (!in_array($field, $allowedFields, true)) {
            return response()->json([
                'valid' => false,
                'message' => 'Unsupported validation field.',
            ], 422);
        }

        $user = null;
        if ($request->filled('user_id')) {
            $user = User::with('student')->find($request->input('user_id'));
        }

        $rules = AdminUserRequest::rulesFor($user, !$user);
        if (!array_key_exists($field, $rules)) {
            return response()->json([
                'valid' => false,
                'message' => 'Unsupported validation field.',
            ], 422);
        }

        $data = AdminUserRequest::normalizeInput($request->all());
        $validator = Validator::make(
            $data,
            [$field => $rules[$field]],
            AdminUserRequest::validationMessages()
        );

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'field' => $field,
                'message' => $validator->errors()->first($field),
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'field' => $field,
            'message' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminUserRequest $request)
    {
        $data = $request->validated();
        $invitationQueued = false;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => null,
            'role' => $data['role'],
            'status' => $this->resolveManagedStatus($data['role'], $data['status'] ?? 'inactive'),
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        if ($data['role'] === 'student') {
            $this->syncStudentRecord($user, $data);
        } elseif ($data['role'] === 'staff') {
            $this->syncStaffRecord($user, $data);
        }

        $user->load('student.department', 'staff.department');

        if (in_array($user->role, ['staff', 'student'], true)) {
            $invitationQueued = $this->invitationEmailService->sendRegistrationInvite($user);
        }

        // Log the activity with full details
        ActivityLogger::logActivity(
            'user_created',
            "Created User: {$user->name} (" . ucfirst($user->role) . ")",
            'user',
            'user',
            $user->id,
            ['role' => $user->role, 'email' => $user->email]
        );

        return response()->json([
            'success' => true,
            'message' => in_array($user->role, ['staff', 'student'], true)
                ? ($invitationQueued
                    ? 'Invited user created successfully. Registration email sent. They must complete registration to activate the account.'
                    : 'Invited user created successfully, but the registration email could not be queued. They must still complete registration to activate the account.')
                : 'User created successfully',
            'user' => $user,
            'rowHtml' => view('admin.partials.user-row', ['user' => $user])->render(),
        ]);
    }

    /**
     * Get user details for AJAX edit form
     */
    public function getDetails(User $user)
    {
        $profilePhotoUrl = null;
        if ($user->profile_photo) {
            $profilePhotoUrl = str_starts_with($user->profile_photo, 'http')
                ? $user->profile_photo
                : asset(str_starts_with($user->profile_photo, 'storage/')
                    ? $user->profile_photo
                    : 'storage/' . ltrim($user->profile_photo, '/'));
        }

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'address' => $user->address,
            'role' => $user->role,
            'status' => $user->status,
            'has_password' => $user->hasCompletedRegistration(),
            'profile_photo_url' => $profilePhotoUrl,
            'initial' => strtoupper(substr($user->name, 0, 1)),
            'is_current_user' => $user->id === auth()->id(),
            'last_login_at' => $user->last_login_at?->toDateTimeString(),
            'last_login_label' => $user->last_login_at?->format('M j, Y g:i A') ?? 'Never',
            'created_at' => $user->created_at?->toDateTimeString(),
            'created_at_label' => $user->created_at?->format('M j, Y g:i A') ?? 'Unknown',
            'department_name' => null,
        ];

        if ($user->role === 'student' && $user->student) {
            $data['department_name'] = data_get($user, 'student.department.name');
            $data['student'] = [
                'department_id' => $user->student->department_id,
                'department_name' => data_get($user, 'student.department.name'),
                'student_id' => $user->student->student_id ?: $user->student->roll_no,
                'roll_no' => $user->student->roll_no,
                'batch' => $user->student->batch,
                'semester' => $user->student->semester,
            ];
        } elseif ($user->role === 'staff' && $user->staff) {
            $data['department_name'] = data_get($user, 'staff.department.name');
            $data['staff'] = [
                'staff_id' => $user->staff->staff_id,
                'department_id' => $user->staff->department_id,
                'department_name' => data_get($user, 'staff.department.name'),
                'designation' => $user->staff->designation,
                'join_date' => $user->staff->join_date,
                'join_date_label' => $user->staff->join_date
                    ? \Carbon\Carbon::parse($user->staff->join_date)->format('M j, Y')
                    : null,
            ];
        }

        return response()->json([
            'success' => true,
            'user' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminUserRequest $request, User $user)
    {
        $data = $request->validated();

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'address' => $user->address,
        ];

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $user->hasCompletedRegistration()
                ? $user->status
                : $this->resolveManagedStatus($data['role'], $user->status),
        ]);

        // Handle student information
        if ($data['role'] === 'student') {
            if ($user->staff) {
                $user->staff->delete();
            }

            $this->syncStudentRecord($user, $data);
        } elseif ($data['role'] === 'staff') {
            if ($user->student) {
                $user->student->delete();
            }

            $this->syncStaffRecord($user, $data);
        } else {
            // Delete role-specific records if role changed to admin
            if ($user->student) {
                $user->student->delete();
            }

            if ($user->staff) {
                $user->staff->delete();
            }
        }

        // Prepare changes description
        $changes = [];
        if ($oldData['name'] !== $data['name']) {
            $changes[] = "name: {$oldData['name']} → {$data['name']}";
        }
        if ($oldData['email'] !== $data['email']) {
            $changes[] = "email: {$oldData['email']} → {$data['email']}";
        }
        if ($oldData['role'] !== $data['role']) {
            $changes[] = "role: {$oldData['role']} → {$data['role']}";
        }
        if (($oldData['gender'] ?? null) !== ($data['gender'] ?? null)) {
            $changes[] = 'gender updated';
        }

        $changesText = !empty($changes) ? 'Changes: ' . implode(', ', $changes) : 'No changes detected';

        // Log the activity
        ActivityLogger::logActivity(
            'user_updated',
            "Updated User: {$user->name} (" . ucfirst($user->role) . ")",
            'user',
            'user',
            $user->id,
            ['changes' => $changes, 'role' => $user->role]
        );

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user->load('student.department', 'staff.department'),
            'rowHtml' => view('admin.partials.user-row', ['user' => $user])->render(),
        ]);
    }

    private function syncStaffRecord(User $user, array $data): void
    {
        $staffData = [
            'staff_id' => $data['staff_id'],
            'department_id' => $data['department_id'],
            'designation' => $data['designation'] ?? null,
            'join_date' => $data['join_date'] ?? null,
        ];

        if ($user->staff) {
            $user->staff->update($staffData);
            return;
        }

        staff::create($staffData + [
            'user_id' => $user->id,
        ]);
    }

    private function syncStudentRecord(User $user, array $data): void
    {
        $studentData = [
            'department_id' => $data['department_id'],
            'student_id' => $data['roll_no'],
            'roll_no' => $data['roll_no'],
            'batch' => $data['batch'] ?? null,
            'semester' => $data['semester'],
            'address' => $data['address'] ?? null,
        ];

        if ($user->student) {
            $user->student->update($studentData);
            return;
        }

        Student::create($studentData + [
            'user_id' => $user->id,
        ]);
    }

    private function resolveManagedStatus(string $role, string $requestedStatus): string
    {
        if (in_array($role, ['staff', 'student'], true)) {
            return 'inactive';
        }

        return $requestedStatus === 'inactive' ? 'inactive' : 'active';
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(Request $request, User $user)
    {
        $oldStatus = $user->status;
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';

        if ($newStatus === 'active' && $user->requiresSelfRegistration()) {
            return response()->json([
                'success' => false,
                'message' => 'This invited account must complete registration before it can be activated.',
            ], 422);
        }

        $user->update([
            'status' => $newStatus
        ]);

        // Log the activity
        ActivityLogger::logActivity(
            'status_changed',
            "Changed status of {$user->name} (" . ucfirst($user->role) . ") from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus),
            'user',
            'user',
            $user->id,
            ['old_status' => $oldStatus, 'new_status' => $newStatus]
        );

        $this->notifyAccountStatusChangeIfNeeded($user, $newStatus);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'status' => $user->status
        ]);
    }

    protected function notifyAccountStatusChangeIfNeeded(User $user, string $newStatus): void
    {
        $user->loadMissing('student', 'staff');

        $accountRole = strtolower((string) $user->role);

        if (!in_array($accountRole, ['student', 'staff'], true)) {
            return;
        }

        $statusMessage = $newStatus === 'active' ? 'activated' : 'deactivated';
        $relatedModel = $accountRole === 'student' ? 'Student' : 'Staff';
        $relatedId = $accountRole === 'student'
            ? $user->student?->id
            : $user->staff?->id;

        Notification::notify(
            user: $user,
            type: 'account.status_changed',
            title: 'Account Status Changed',
            message: "Your account has been {$statusMessage} by admin",
            data: ['status' => $newStatus, 'changed_by' => auth()->user()?->name],
            relatedModel: $relatedModel,
            relatedId: $relatedId
        );

        $this->studentNotificationEmailService->sendUserStatusChangedEmail(
            $user,
            $newStatus,
            auth()->user()?->name,
            auth()->user()?->role,
        );
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user)
    {
        if ($user->requiresSelfRegistration()) {
            return response()->json([
                'success' => false,
                'message' => 'This invited account has not completed registration yet. Ask the user to finish registration instead.',
            ], 422);
        }

        // Generate temporary password
        $tempPassword = Str::random(10);
        try {
            DB::transaction(function () use ($user, $tempPassword) {
                $user->update([
                    'password' => Hash::make($tempPassword),
                    'force_password_change' => true,
                    'password_reset_at' => now()
                ]);

                Mail::to($user->email)->queue(new PasswordResetEmail(
                    $user->name,
                    $user->email,
                    $tempPassword
                ));
            });

            \Log::info('Queued administrator password reset email', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to queue password reset email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Password reset email could not be queued, so no changes were saved.',
            ], 500);
        }

        // Log the activity
        ActivityLogger::logActivity(
            'password_reset',
            "Reset password for user: {$user->name} ({$user->email})",
            'auth',
            'user',
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Temporary password has been sent to user email'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        \Log::info('=== DELETE USER START === User ID: ' . $user->id . ', Name: ' . $user->name);
        
        try {
            $userName = $user->name;
            $userRole = $user->role;
            $userId = $user->id;

            \Log::info('About to delete user: ' . $userName . ' (ID: ' . $userId . ', Role: ' . $userRole . ')');

            // Clean up issued_books where this user is the issuer (issued_by)
            // This handles the foreign key constraint issue
            DB::table('issued_books')->where('issued_by', $userId)->update(['issued_by' => null]);
            
            \Log::info('Cleared issued_books.issued_by references');

            // Now delete the user (will cascade delete student/staff records through foreign keys)
            $result = $user->delete();
            
            \Log::info('User.delete() returned: ' . ($result ? 'true' : 'false'));

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Throwable $e) {
            \Log::error('=== DELETE ERROR === ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            \Log::error('Exception class: ' . get_class($e));
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the activity logs for a specific user.
     */
    public function userActivityLogs(User $user)
    {
        $logs = ActivityLog::where('model_type', User::class)
            ->where('model_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }
}
