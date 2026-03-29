<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Models\ActivityLog;
use App\Models\staff;
use App\Models\student;
use App\Models\User;
use App\Helpers\ActivityLogger;
use App\Mail\PasswordResetEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        Gate::authorize('access-admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUserId = auth()->id();

        $users = User::with([
            'student.department',
            'staff.department'
        ])
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$currentUserId])
            ->orderByRaw("CASE WHEN role='admin' THEN 1 WHEN role='staff' THEN 2 WHEN role='student' THEN 3 END")
            ->latest()
            ->simplePaginate(15);

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
            'departments'
        ));
    }

    /**
     * Get users data for AJAX requests (with filtering, search, pagination)
     */
    public function getUsersData(Request $request)
    {
        try {
            $currentUserId = auth()->id();
            $search = $request->input('search', '');
            $status = $request->input('status', 'all');
            $role = $request->input('role', 'all');
            $sort = $request->input('sort', 'recently-added');
            $page = $request->input('page', 1);

            // Build base query
            $query = User::query();

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Apply role filter
            if ($role !== 'all') {
                $query->where('role', $role);
            }

            // Keep the signed-in user pinned to the top of the current result set.
            $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$currentUserId]);

            // Apply sorting
            switch ($sort) {
                case 'recently-added':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'name-asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name-desc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            // Paginate results (15 users per page)
            $users = $query->paginate(15, ['*'], 'page', $page);

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
            $pagination = $users->links()->toHtml();

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdminUserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
            'status' => $data['status'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        if ($data['role'] === 'student') {
            Student::create([
                'user_id' => $user->id,
                'roll_no' => $data['roll_no'],
                'batch' => $data['batch'] ?? null,
                'department_id' => $data['department_id'],
                'semester' => $data['semester'],
                'address' => $data['address'] ?? null,
            ]);
        } elseif ($data['role'] === 'staff') {
            $this->syncStaffRecord($user, $data);
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
            'message' => 'User created successfully',
            'user' => $user->load('student.department', 'staff.department'),
            'rowHtml' => view('admin.partials.user-row', ['user' => $user])->render(),
        ]);
    }

    /**
     * Get user details for AJAX edit form
     */
    public function getDetails(User $user)
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'role' => $user->role,
            'status' => $user->status,
        ];

        if ($user->role === 'student' && $user->student) {
            $data['student'] = [
                'department_id' => $user->student->department_id,
                'roll_no' => $user->student->roll_no,
                'batch' => $user->student->batch,
                'semester' => $user->student->semester,
            ];
        } elseif ($user->role === 'staff' && $user->staff) {
            $data['staff'] = [
                'department_id' => $user->staff->department_id,
                'designation' => $user->staff->designation,
                'join_date' => $user->staff->join_date,
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
            'address' => $user->address,
        ];

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        // Handle student information
        if ($data['role'] === 'student') {
            if ($user->staff) {
                $user->staff->delete();
            }

            if ($user->student) {
                $user->student->update([
                    'department_id' => $data['department_id'],
                    'roll_no' => $data['roll_no'],
                    'batch' => $data['batch'] ?? null,
                    'semester' => $data['semester'],
                    'address' => $data['address'] ?? null,
                ]);
            } else {
                Student::create([
                    'user_id' => $user->id,
                    'department_id' => $data['department_id'],
                    'roll_no' => $data['roll_no'],
                    'batch' => $data['batch'] ?? null,
                    'semester' => $data['semester'],
                    'address' => $data['address'] ?? null,
                ]);
            }
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
            'department_id' => $data['department_id'],
            'designation' => $data['designation'],
            'join_date' => $data['join_date'],
        ];

        if ($user->staff) {
            $user->staff->update($staffData);
            return;
        }

        staff::create($staffData + [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(Request $request, User $user)
    {
        $oldStatus = $user->status;
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';

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

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'status' => $user->status
        ]);
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user)
    {
        // Generate temporary password
        $tempPassword = Str::random(10);

        $user->update([
            'password' => Hash::make($tempPassword),
            'force_password_change' => true,
            'password_reset_at' => now()
        ]);

        // Send email with temporary password
        try {
            Mail::to($user->email)->queue(new PasswordResetEmail(
                $user->name,
                $user->email,
                $tempPassword
            ));
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage());
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
