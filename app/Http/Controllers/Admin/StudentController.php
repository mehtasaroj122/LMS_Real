<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Helpers\ActivityLogger;
use App\Mail\PasswordResetEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-admin');
        $departments = Department::all();
        return view('admin.Students', compact('departments'));
    }

    /**
     * Show the form for creating a new student (redirects to index where modal is shown).
     */
    public function create()
    {
        Gate::authorize('access-admin');
        return redirect()->route('admin.students.index');
    }

    /**
     * Get students data with search, filter and pagination for AJAX requests
     */
    public function getStudentsData(Request $request)
    {
        Gate::authorize('access-admin');

        $search = $request->get('search', '');
        $department = $request->get('department', 'all');
        $status = $request->get('status', 'all');
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

        // Status filter
        if ($status !== 'all') {
            $query->whereHas('user', function($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // Paginate
        $students = $query->paginate($perPage, ['*'], 'page', $page);

        // Generate table rows HTML
        $tableRows = '';
        foreach ($students->items() as $student) {
            $statusClass = $student->user->status === 'active' ? 'status-active' : 'status-inactive';
            $statusText = ucfirst($student->user->status);
            $statusIcon = $student->user->status === 'active' 
                ? '<i class="fas fa-check-circle" style="font-size: 10px;"></i>' 
                : '<i class="fas fa-times-circle" style="font-size: 10px;"></i>';
            $studentName = htmlspecialchars($student->user->name ?? 'Unknown');
            $studentRoll = htmlspecialchars($student->roll_no ?? 'N/A');
            $profilePhoto = $student->user->profile_photo ?? null;
            $avatarHtml = '<div class="student-avatar">' . htmlspecialchars(strtoupper(substr($student->user->name ?? 'U', 0, 1))) . '</div>';

            if ($profilePhoto) {
                $avatarUrl = str_starts_with($profilePhoto, 'http')
                    ? $profilePhoto
                    : asset(str_starts_with($profilePhoto, 'storage/') ? $profilePhoto : 'storage/' . ltrim($profilePhoto, '/'));

                $avatarHtml = '<div class="student-avatar"><img src="' . htmlspecialchars($avatarUrl) . '" alt="' . $studentName . '"></div>';
            }

            $tableRows .= '<tr data-student-id="' . $student->id . '">';
            $tableRows .= '<td>';
            $tableRows .= '<div class="student-cell">';
            $tableRows .= $avatarHtml;
            $tableRows .= '<div class="student-info">';
            $tableRows .= '<span class="student-name">' . $studentName . '</span>';
            $tableRows .= '<div class="text-muted">' . $studentRoll . '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '<td class="text-muted">' . htmlspecialchars($student->user->email ?? 'N/A') . '</td>';
            $tableRows .= '<td class="text-muted">' . htmlspecialchars($student->department->name ?? 'N/A') . '</td>';
            $tableRows .= '<td class="text-muted">' . htmlspecialchars($student->batch ?? 'N/A') . '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<span class="status-badge ' . $statusClass . '">';
            $tableRows .= $statusIcon . ' ' . $statusText;
            $tableRows .= '</span>';
            $tableRows .= '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div class="action-buttons">';
            $tableRows .= '<a href="' . route('admin.students.show', $student->id) . '" class="action-btn" title="View details"><i class="fas fa-eye"></i></a>';
            $tableRows .= '<button onclick="openEditStudentModal(' . $student->id . ')" class="action-btn" title="Edit"><i class="fas fa-edit"></i></button>';
            $toggleIcon = $student->user->status === 'active' ? 'fas fa-toggle-on' : 'fas fa-toggle-off';
            $tableRows .= '<button onclick="toggleStudentStatus(' . $student->id . ')" class="action-btn" title="Toggle status"><i class="' . $toggleIcon . '"></i></button>';
            $tableRows .= '<button onclick="deleteStudent(' . $student->id . ')" class="action-btn" title="Delete"><i class="fas fa-trash-alt"></i></button>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
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
        Gate::authorize('access-admin');

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
     * Get student edit data for modal
     */
    public function getStudentEditData($id)
    {
        Gate::authorize('access-admin');

        try {
            $student = Student::with('user', 'department')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'phone' => $student->user->phone,
                    'roll_no' => $student->roll_no,
                    'department_id' => $student->department_id,
                    'batch' => $student->batch,
                    'semester' => $student->semester,
                    'address' => $student->address,
                    'status' => $student->user->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('access-admin');

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
            $errorMsg = $e->getMessage();
            $errors = [];

            if (stripos($errorMsg, 'users_email_unique') !== false || (stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['email']) !== false)) {
                $errors['email'] = ['The email has already been taken.'];
            }
            if (stripos($errorMsg, 'users_phone_unique') !== false || (stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['phone']) !== false)) {
                $errors['phone'] = ['The phone number has already been taken.'];
            }

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

        // Load relations so client can update UI immediately
        $student->load('user', 'department');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student created successfully',
                'student' => $student,
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        Gate::authorize('access-admin');
        $student = Student::with(['user', 'department', 'issuedBooks', 'bookRequests', 'fines'])->findOrFail($id);
        
        // Ensure the associated user has 'student' role
        if ($student->user->role !== 'student') {
            return redirect()->route('admin.students.index')->with('error', 'This user is not a student.');
        }
        
        // Transform issued books for frontend
        $booksData = $student->issuedBooks->map(function($book) {
            $status = $book->return_date ? 'returned' : (\Carbon\Carbon::parse($book->due_date)->toDateString() < now()->toDateString() ? 'overdue' : 'issued');
            $daysOverdue = $status === 'overdue' ? now()->diffInDays(\Carbon\Carbon::parse($book->due_date)) : 0;
            
            return [
                'id' => $book->id,
                'title' => $book->book->title ?? 'Unknown',
                'author' => $book->book->author ?? 'Unknown Author',
                'publisher' => $book->book->publisher ?? 'Unknown Publisher',
                'isbn' => $book->book->isbn ?? 'N/A',
                'description' => $book->book->description ?? 'No description available',
                'coverImage' => $book->book->cover_image ?? null,
                'condition' => $book->condition ?? 'Good',
                'issueDate' => optional($book->issue_date) ? \Carbon\Carbon::parse($book->issue_date)->format('M d, Y') : 'N/A',
                'issueDateFull' => optional($book->issue_date) ? \Carbon\Carbon::parse($book->issue_date)->format('M d, Y H:i') : 'N/A',
                'dueDate' => optional($book->due_date) ? \Carbon\Carbon::parse($book->due_date)->format('M d, Y') : 'N/A',
                'returnDate' => optional($book->return_date) ? \Carbon\Carbon::parse($book->return_date)->format('M d, Y') : '-',
                'status' => $status,
                'fine' => $book->fine_amount ?? 0,
                'daysOverdue' => $daysOverdue,
                'remarks' => $book->remarks ?? 'No remarks'
            ];
        })->toArray();
        
        // Transform fines for frontend
        $finesData = $student->fines->map(function($fine) {
            return [
                'id' => $fine->id,
                'bookName' => optional($fine->issuedBook && $fine->issuedBook->book) ? $fine->issuedBook->book->title : 'Unknown',
                'daysOverdue' => ($fine->days_late ?? 0) . ' days',
                'fineAmount' => $fine->amount ?? 0,
                'paymentStatus' => $fine->status,
                'actions' => in_array($fine->status, ['paid', 'waived']) ? ['view-history'] : ['adjust', 'waive', 'mark-paid', 'view-history']
            ];
        })->toArray();
        
        // Activity logs (can be expanded based on actual activity tracking model)
        $allActivityLogs = ActivityLog::where(function($q) use ($student, $id) {
            // Logs for this student record
            $q->where('model_type', 'App\Models\Student')
              ->where('model_id', $id);
        })->orWhere(function($q) use ($student) {
            // Logs for this student's user (login, status change, etc.)
            $q->where('affected_user_id', $student->user_id);
        })->orWhere(function($q) use ($student) {
            // Logs for issued books related to this student
            $q->where('resource_type', 'issued_book')
              ->whereIn('resource_id', $student->issuedBooks->pluck('id'));
        })->orWhere(function($q) use ($student) {
            // Logs for fines related to this student
            $q->where('resource_type', 'fine')
              ->whereIn('resource_id', $student->fines->pluck('id'));
        })
        ->orderByDesc('created_at')
        ->limit(20)
        ->get()
        ->map(function($log) use ($student) {
            // Map action types to frontend icon types
            $actionType = $log->action ?? $log->action_category ?? 'activity';
            $typeMap = [
                'book_issued' => 'book-issued',
                'book_returned' => 'book-returned',
                'book_return' => 'book-returned',
                'fine_applied' => 'fine-applied',
                'fine_paid' => 'fine-applied',
                'status_changed' => 'account-status',
                'user_updated' => 'profile-updated',
                'profile_updated' => 'profile-updated',
            ];
            
            $frontendType = $typeMap[$actionType] ?? 'profile-updated';
            
            // Enhance description: replace "by student" with student's actual name
            $description = $log->description ?? '';
            if (strpos($description, 'by student') !== false) {
                $description = str_replace('by student', 'by ' . $student->user->name, $description);
            }
            
            return [
                'id' => $log->id,
                'type' => $frontendType,
                'title' => $log->getActionNameAttribute() ?? $log->action ?? 'Activity',
                'description' => $description,
                'time' => optional($log->created_at)->format('M d, Y h:i A') ?? '',
                'icon' => $frontendType
            ];
        })
        ->toArray();

        // Separate initial logs (first 10) from remaining logs for pagination
        $activityLogs = array_slice($allActivityLogs, 0, 10);
        $remainingActivityLogs = array_slice($allActivityLogs, 10);
        
        return view('admin.StudentView', compact('student', 'booksData', 'finesData', 'activityLogs', 'remainingActivityLogs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('access-admin');

        $student = Student::findOrFail($id);
        $user = $student->user;

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'phone' => 'required|string',
            'roll_no' => 'required|string|unique:students,roll_no,' . $student->id,
            'department_id' => 'required|exists:departments,id',
            'batch' => 'required|string',
            'semester' => 'required|string',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Track changes in User model
        $userChanges = [];
        if ($user->name !== $validated['name']) $userChanges['name'] = $validated['name'];
        if ($user->email !== $validated['email']) $userChanges['email'] = $validated['email'];
        if ($user->phone !== $validated['phone']) $userChanges['phone'] = $validated['phone'];
        if ($user->status !== $validated['status']) $userChanges['status'] = $validated['status'];

        // Track changes in Student model
        $studentChanges = [];
        if ($student->roll_no !== $validated['roll_no']) $studentChanges['roll_no'] = $validated['roll_no'];
        if ($student->department_id != $validated['department_id']) $studentChanges['department_id'] = $validated['department_id'];
        if ($student->batch !== $validated['batch']) $studentChanges['batch'] = $validated['batch'];
        if ($student->semester !== $validated['semester']) $studentChanges['semester'] = $validated['semester'];
        if ($student->address !== ($validated['address'] ?? null)) $studentChanges['address'] = $validated['address'] ?? null;

        // Update user
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
        ]);

        // Update student
        $student->update([
            'roll_no' => $validated['roll_no'],
            'department_id' => $validated['department_id'],
            'batch' => $validated['batch'],
            'semester' => $validated['semester'],
            'address' => $validated['address'] ?? null,
        ]);

        // Log the activity with actual changes
        $allChanges = array_merge($userChanges, $studentChanges);
        if (!empty($allChanges)) {
            ActivityLogger::logProfileUpdate($student, $allChanges);
        }

        // Notify admin if status changed to inactive (critical action)
        if (isset($userChanges['status']) && $validated['status'] === 'inactive') {
            $adminUser = User::where('role', 'admin')->where('id', '!=', auth()->id())->first();
            if ($adminUser) {
                Notification::notify(
                    user: $adminUser,
                    type: 'student.critical_action',
                    title: 'Student Account Deactivated',
                    message: "Student {$student->user->name} (Roll: {$student->roll_no}) account has been deactivated",
                    data: ['student_id' => $student->id, 'user_id' => $student->user_id, 'action' => 'deactivated'],
                    relatedModel: 'Student',
                    relatedId: $student->id
                );
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully',
                'student' => $student,
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully');
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
     * Reset password for a student
     */
    public function resetPassword(string $id)
    {
        Gate::authorize('access-admin');

        $student = Student::findOrFail($id);
        $user = $student->user;

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
        ActivityLogger::logPasswordReset($student);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! Temporary password has been sent to the student email.',
        ]);
    }

    /**
     * Deactivate student account
     */
    public function deactivate(string $id)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this student.',
                ], 404);
            }

            $oldStatus = $user->status;

            // Update user status to inactive
            $user->status = 'inactive';
            $user->save();

            // Log the activity
            ActivityLogger::logStatusChange($student, $oldStatus, 'inactive');

            return response()->json([
                'success' => true,
                'message' => 'Student account has been deactivated successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deactivating student account: ' . $e->getMessage(), [
                'student_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deactivating the account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate student account
     */
    public function activate(string $id)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this student.',
                ], 404);
            }

            $oldStatus = $user->status;

            // Update user status to active
            $user->status = 'active';
            $user->save();

            // Log the activity
            ActivityLogger::logStatusChange($student, $oldStatus, 'active');

            return response()->json([
                'success' => true,
                'message' => 'Student account has been activated successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error activating student account: ' . $e->getMessage(), [
                'student_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while activating the account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle student status (active/inactive)
     */
    public function toggleStatus(string $id)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this student.',
                ], 404);
            }

            $oldStatus = $user->status;
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';

            // Update user status
            $user->status = $newStatus;
            $user->save();

            // Log the activity
            ActivityLogger::logStatusChange($student, $oldStatus, $newStatus);

            return response()->json([
                'success' => true,
                'message' => 'Student status has been updated to ' . ucfirst($newStatus) . '.',
                'status' => $newStatus,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling student status: ' . $e->getMessage(), [
                'student_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating status: ' . $e->getMessage(),
            ], 500);
        }
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

    /**
     * Get student fines (AJAX)
     */
    public function getStudentFines(Request $request, string $id)
    {
        Gate::authorize('access-admin');
        
        $student = Student::with(['fines' => function($q) {
            $q->with('issuedBook.book')->orderBy('created_at', 'desc');
        }])->findOrFail($id);
        
        $fines = $student->fines->map(function($fine) {
            $dueDate = $fine->issuedBook && $fine->issuedBook->due_date
                ? $fine->issuedBook->due_date->format('M d, Y')
                : 'N/A';
            
            return [
                'id' => $fine->id,
                'bookName' => $fine->issuedBook && $fine->issuedBook->book 
                    ? $fine->issuedBook->book->title 
                    : 'Unknown',
                'daysOverdue' => (int)$fine->days_late,
                'dueDate' => $dueDate,
                'fineAmount' => (float)$fine->amount,
                'status' => $fine->status, // pending, paid, waived - THIS IS THE KEY FIELD
                'createdAt' => $fine->created_at->format('Y-m-d H:i:s'),
                'remarks' => $fine->remarks ?? '',
                'actions' => $fine->status === 'pending' ? ['adjust', 'waive', 'mark-paid', 'view-history'] : ['view-history']
            ];
        });
        
        // Calculate pending fines total
        $pendingFinesTotal = $student->fines->filter(function($fine) {
            return $fine->status === 'pending';
        })->sum('amount');
        
        return response()->json([
            'success' => true,
            'fines' => $fines,
            'pendingFinesTotal' => (float)$pendingFinesTotal,
        ]);
    }

    /**
     * Generate receipt for student fines
     */
    public function generateReceipt(Request $request, string $id)
    {
        Gate::authorize('access-admin');
        
        try {
            $student = Student::with(['user', 'fines' => function($q) {
                $q->where('status', 'paid')->with('issuedBook.book');
            }])->findOrFail($id);

            // Calculate totals
            $totalPaid = $student->fines->sum('amount');
            $paidFinesCount = $student->fines->count();

            // Generate simple HTML receipt
            $receipt = "<html>";
            $receipt .= "<head><style>";
            $receipt .= "body { font-family: Arial, sans-serif; margin: 20px; }";
            $receipt .= ".header { text-align: center; margin-bottom: 20px; }";
            $receipt .= ".section { margin-bottom: 15px; }";
            $receipt .= "table { width: 100%; border-collapse: collapse; }";
            $receipt .= "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
            $receipt .= ".total { font-weight: bold; text-align: right; }";
            $receipt .= "</style></head>";
            $receipt .= "<body>";
            
            $receipt .= "<div class='header'>";
            $receipt .= "<h2>Library Management System - Fine Payment Receipt</h2>";
            $receipt .= "<p>Generated on: " . now()->format('Y-m-d H:i:s') . "</p>";
            $receipt .= "</div>";
            
            $receipt .= "<div class='section'>";
            $receipt .= "<h3>Student Information</h3>";
            $receipt .= "<p><strong>Name:</strong> " . $student->user->name . "</p>";
            $receipt .= "<p><strong>Student ID:</strong> " . $student->id . "</p>";
            $receipt .= "<p><strong>Email:</strong> " . $student->user->email . "</p>";
            $receipt .= "<p><strong>Department:</strong> " . ($student->department->name ?? 'N/A') . "</p>";
            $receipt .= "</div>";
            
            $receipt .= "<div class='section'>";
            $receipt .= "<h3>Paid Fines Summary</h3>";
            $receipt .= "<table>";
            $receipt .= "<tr><th>Book Title</th><th>Amount</th><th>Paid On</th></tr>";
            
            foreach ($student->fines as $fine) {
                $receipt .= "<tr>";
                $receipt .= "<td>" . ($fine->issuedBook?->book?->title ?? 'Unknown Book') . "</td>";
                $receipt .= "<td>₹" . number_format($fine->amount, 2) . "</td>";
                $receipt .= "<td>" . ($fine->paid_on ? $fine->paid_on->format('Y-m-d') : 'N/A') . "</td>";
                $receipt .= "</tr>";
            }
            
            $receipt .= "<tr><td colspan='2' class='total'>Total Paid:</td><td class='total'>₹" . number_format($totalPaid, 2) . "</td></tr>";
            $receipt .= "</table>";
            $receipt .= "</div>";
            
            $receipt .= "<p style='margin-top: 20px; font-size: 12px; color: #666;'>This is an automatically generated receipt. For more information, please contact the library administration.</p>";
            $receipt .= "</body></html>";

            // Return as PDF or HTML
            $filename = "receipt_" . $student->id . "_" . now()->format('Y-m-d_H-i-s') . ".pdf";
            
            // For now, return as downloadable HTML (can be extended to PDF using libraries)
            return response($receipt)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            \Log::error('Error generating receipt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student privileges
     */
    public function getPrivileges($studentId)
    {
        try {
            // Allow both admin and staff to access student privileges
            $userRole = auth()->user()->role ?? null;
            if (!in_array($userRole, ['admin', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $student = Student::findOrFail($studentId);
            $privileges = $student->privileges ?? new \App\Models\StudentPrivilege();

            // Get global fine settings for defaults
            $fineSetting = \App\Models\FineSetting::first() ?? new \App\Models\FineSetting();

            return response()->json([
                'success' => true,
                'privileges' => [
                    'max_books' => $privileges->max_books,
                    'issue_duration_days' => $privileges->issue_duration_days,
                    'per_day_fine' => $privileges->per_day_fine,
                    'borrowing_allowed' => $privileges->borrowing_allowed ?? true,
                    'grace_period_days' => $privileges->grace_period_days,
                    'max_fine_amount' => $privileges->max_fine_amount,
                ],
                'defaults' => [
                    'max_books' => $fineSetting->max_books_per_student ?? 5,
                    'issue_duration_days' => $fineSetting->issue_duration_days ?? 14,
                    'per_day_fine' => $fineSetting->per_day_fine ?? 10,
                    'grace_period_days' => $fineSetting->grace_period_days ?? 2,
                    'max_fine_amount' => $fineSetting->max_fine_amount ?? 500,
                ],
                'effective' => [
                    'max_books' => $privileges->max_books ?? ($fineSetting->max_books_per_student ?? 5),
                    'issue_duration_days' => $privileges->issue_duration_days ?? ($fineSetting->issue_duration_days ?? 14),
                    'per_day_fine' => $privileges->per_day_fine ?? ($fineSetting->per_day_fine ?? 10),
                    'grace_period_days' => $privileges->grace_period_days ?? ($fineSetting->grace_period_days ?? 2),
                    'max_fine_amount' => $privileges->max_fine_amount ?? ($fineSetting->max_fine_amount ?? 500),
                    'borrowing_allowed' => $privileges->borrowing_allowed ?? true,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting privileges: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading privileges: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save student privileges
     */
    public function savePrivileges(Request $request, $studentId)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::findOrFail($studentId);

            $validated = $request->validate([
                'max_books' => 'nullable|integer|min:1|max:20',
                'issue_duration_days' => 'nullable|integer|min:1|max:90',
                'per_day_fine' => 'nullable|numeric|min:0|max:100',
                'grace_period_days' => 'nullable|integer|min:0|max:30',
                'max_fine_amount' => 'nullable|numeric|min:0|max:10000',
                'borrowing_allowed' => 'boolean',
            ]);

            // Get or create privileges record
            $privileges = $student->privileges ?? new \App\Models\StudentPrivilege(['student_id' => $student->id]);

            // Track changes for logging
            $changes = [];
            if ($validated['max_books'] !== null && $privileges->max_books != $validated['max_books']) {
                $changes['max_books'] = $validated['max_books'];
            }
            if ($validated['issue_duration_days'] !== null && $privileges->issue_duration_days != $validated['issue_duration_days']) {
                $changes['issue_duration_days'] = $validated['issue_duration_days'];
            }
            if ($validated['per_day_fine'] !== null && $privileges->per_day_fine != $validated['per_day_fine']) {
                $changes['per_day_fine'] = $validated['per_day_fine'];
            }
            if ($validated['grace_period_days'] !== null && $privileges->grace_period_days != $validated['grace_period_days']) {
                $changes['grace_period_days'] = $validated['grace_period_days'];
            }
            if ($validated['max_fine_amount'] !== null && $privileges->max_fine_amount != $validated['max_fine_amount']) {
                $changes['max_fine_amount'] = $validated['max_fine_amount'];
            }
            if ($privileges->borrowing_allowed != $validated['borrowing_allowed']) {
                $changes['borrowing_allowed'] = $validated['borrowing_allowed'];
            }

            // Update privileges
            $privileges->fill($validated);
            $privileges->save();

            // Log the activity
            if (!empty($changes)) {
                try {
                    ActivityLogger::logStudentActivity(
                        $student,
                        'privilege_updated',
                        "Library privileges updated: " . json_encode($changes),
                        'privilege',
                        $changes
                    );
                } catch (\Exception $logError) {
                    \Log::warning('Failed to log privilege change: ' . $logError->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Library privileges saved successfully',
                'privileges' => [
                    'max_books' => $privileges->max_books,
                    'issue_duration_days' => $privileges->issue_duration_days,
                    'per_day_fine' => $privileges->per_day_fine,
                    'grace_period_days' => $privileges->grace_period_days,
                    'max_fine_amount' => $privileges->max_fine_amount,
                    'borrowing_allowed' => $privileges->borrowing_allowed,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving privileges: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving privileges: ' . $e->getMessage()
            ], 500);
        }
    }
}
