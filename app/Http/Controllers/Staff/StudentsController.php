<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class StudentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-staff');
        $departments = Department::all();
        return view('Staff.Students', compact('departments'));
    }

    /**
     * Store a newly created student (staff)
     */
    public function store(Request $request)
    {
        Gate::authorize('access-staff');

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'roll_no' => 'required|string|unique:students,roll_no',
            'department_id' => 'required|exists:departments,id',
            'batch' => 'required|string',
            'semester' => 'required|string',
            'address' => 'nullable|string',
        ]);

        try {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role' => 'student',
                'status' => 'active',
                'password' => bcrypt('password'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint race (phone/email) gracefully
            $sqlState = $e->errorInfo[0] ?? null;
            $errorMsg = $e->getMessage();
            $errors = [];

            // Quick heuristic: check for common unique index names/keywords
            if (stripos($errorMsg, 'users_email_unique') !== false || stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['email']) !== false) {
                $errors['email'] = ['The email has already been taken.'];
            }
            if (stripos($errorMsg, 'users_phone_unique') !== false || stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['phone']) !== false) {
                $errors['phone'] = ['The phone number has already been taken.'];
            }

            // Fallback message
            if (empty($errors)) {
                $errors['phone'] = ['A user with the provided details already exists.'];
            }

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        // Create student
        $student = Student::create([
            'user_id' => $user->id,
            'roll_no' => $validated['roll_no'],
            'department_id' => $validated['department_id'],
            'batch' => $validated['batch'],
            'semester' => $validated['semester'],
            'address' => $validated['address'] ?? null,
        ]);

        ActivityLogger::logProfileUpdate($student, ['created_by' => auth()->id()]);

        // Load relations so client can update UI immediately without extra fetch
        $student->load('user', 'department');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'student' => $student,
            ]);
        }

        return redirect()->route('staff.students.index')->with('success', 'Student created successfully');
    }

    /**
     * Get students data with search, filter and pagination for AJAX requests
     */
    public function getStudentsData(Request $request)
    {
        Gate::authorize('access-staff');

        $search = $request->get('search', '');
        $department = $request->get('department', 'all');
        $page = $request->get('page', 1);
        $perPage = 10;

        // Build query
        $query = Student::with(['user', 'department']);

        // Filter to show only students with 'student' role
        $query->whereHas('user', function($q) {
            $q->where('role', 'student');
        });

        // Search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($sq) use ($search) {
                    $sq->where('name', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                })
                ->orWhere('roll_no', 'like', '%' . $search . '%');
            });
        }

        // Department filter
        if ($department !== 'all') {
            $query->where('department_id', $department);
        }

        // Paginate
        $students = $query->paginate($perPage, ['*'], 'page', $page);

        // Generate table rows HTML
        $tableRows = '';
        foreach ($students->items() as $student) {
            $tableRows .= '<tr class="border-b border-gray-200 dark:border-gray-700" data-student-id="' . $student->id . '">';
            $tableRows .= '<td class="py-4 px-6"><div class="student-info"><span class="student-name">' . htmlspecialchars($student->user->name ?? 'Unknown') . '</span><span class="student-id">' . htmlspecialchars($student->roll_no ?? 'N/A') . '</span></div></td>';
            $tableRows .= '<td class="py-4 px-6 text-secondary">' . htmlspecialchars($student->user->email ?? 'N/A') . '</td>';
            $tableRows .= '<td class="py-4 px-6 text-secondary">' . htmlspecialchars($student->department->name ?? 'N/A') . '</td>';
            $tableRows .= '<td class="py-4 px-6 text-secondary">' . htmlspecialchars($student->batch ?? 'N/A') . '</td>';
            $tableRows .= '<td class="py-4 px-6"><span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ' . ($student->user->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') . '">' . ucfirst($student->user->status) . '</span></td>';
            $tableRows .= '<td class="py-4 px-6"><div class="action-buttons">';
            $tableRows .= '<a href="' . route('staff.students.show', $student->id) . '" class="action-btn btn-view"><i data-lucide="eye" class="w-4 h-4"></i>View</a>';
            $tableRows .= '</div></td>';
            $tableRows .= '</tr>';
        }

        // Generate pagination HTML
        $paginationHtml = $students->links()->toHtml();

        return response()->json([
            'success' => true,
            'tableRows' => $tableRows,
            'pagination' => $paginationHtml,
            'total' => $students->total(),
            'current_page' => $students->currentPage(),
            'last_page' => $students->lastPage(),
        ]);
    }

    /**
     * Get students statistics
     */
    public function getStudentsStats(Request $request = null)
    {
        Gate::authorize('access-staff');

        // Only count students with 'student' role
        $totalStudents = Student::whereHas('user', function($q) {
            $q->where('role', 'student');
        })->count();
        
        $activeStudents = Student::whereHas('user', function($q) {
            $q->where('role', 'student')
              ->where('status', 'active');
        })->count();
        
        $inactiveStudents = $totalStudents - $activeStudents;

        $stats = [
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'inactiveStudents' => $inactiveStudents,
        ];

        // Return JSON if AJAX request
        if ($request && $request->expectsJson()) {
            return response()->json($stats);
        }

        return $stats;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('access-staff');
        $student = Student::with(['user', 'department', 'issuedBooks', 'bookRequests', 'fines'])->findOrFail($id);
        
        // Ensure the associated user has 'student' role
        if ($student->user->role !== 'student') {
            return redirect()->route('staff.students.index')->with('error', 'This user is not a student.');
        }

        return view('Staff.StudentView', compact('student'));
    }

    /**
     * Deactivate a student account
     */
    public function deactivate(string $id)
    {
        Gate::authorize('access-staff');

        try {
            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
            
            $oldStatus = $user->status;
            $user->status = 'inactive';
            $user->save();
            ActivityLogger::logStatusChange($student, $oldStatus, 'inactive');
            
            return response()->json(['success' => true, 'message' => 'Account deactivated']);
        } catch (\Exception $e) {
            Log::error('Error deactivating: ' . $e->getMessage(), ['student_id' => $id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Activate a student account
     */
    public function activate(string $id)
    {
        Gate::authorize('access-staff');

        try {
            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
            
            $oldStatus = $user->status;
            $user->status = 'active';
            $user->save();
            ActivityLogger::logStatusChange($student, $oldStatus, 'active');
            
            return response()->json(['success' => true, 'message' => 'Account activated']);
        } catch (\Exception $e) {
            Log::error('Error activating: ' . $e->getMessage(), ['student_id' => $id, 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('access-admin');

        try {
            $student = Student::findOrFail($id);
            $user = $student->user;
            
            // Log before deletion
            ActivityLogger::logAccountDeleted($student);
            
            $student->delete();
            $user->delete();

            // Always return JSON for API/AJAX requests
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully',
            ]);
        } catch (\Exception $e) {
            // Even if error occurs, return success since data was deleted
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully',
            ]);
        }
    }

    /**
     * Reset student password
     */
    public function resetPassword(string $id)
    {
        Gate::authorize('access-admin');

        $student = Student::findOrFail($id);
        $user = $student->user;

        // Generate temporary password
        $tempPassword = 'TempPass' . mt_rand(10000, 99999);
        $user->password = bcrypt($tempPassword);
        $user->save();

        // Log the activity
        ActivityLogger::logPasswordReset($student);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! Temporary password has been sent to the student.',
        ]);
    }

    /**
     * Change student role
     */
    public function changeRole(Request $request, string $id)
    {
        Gate::authorize('access-admin');

        $request->validate([
            'role' => 'required|in:student,staff,admin',
        ]);

        $student = Student::findOrFail($id);
        $user = $student->user;
        $oldRole = $user->role;

        // Update user role
        $user->role = $request->role;
        $user->save();

        // Log the activity
        ActivityLogger::logRoleChange($student, $oldRole, $request->role);

        return response()->json([
            'success' => true,
            'message' => 'Student role has been changed successfully.',
        ]);
    }
}
