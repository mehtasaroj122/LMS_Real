{{--
    EXAMPLE: Controller showing how to prepare data for AdminDataTable component
    
    This is a reference implementation showing:
    1. How to fetch and structure data
    2. How to use the helper components
    3. How to pass data to the table component
    
    Place this logic in your actual controller (e.g., UserController.php)
--}}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class ExampleUserController extends Controller
{
    /**
     * Display the user management page
     * 
     * This example shows how to prepare data for the AdminDataTable component
     */
    public function index(): View
    {
        // Fetch users with relationships
        $users = User::with(['student.department', 'staff.department'])->paginate(50);
        
        // Calculate statistics
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();
        
        // Count users by role
        $roleCounts = [
            'admin' => User::where('role', 'admin')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'student' => User::where('role', 'student')->count(),
        ];
        
        // ========================================
        // PREPARE TABLE DATA
        // ========================================
        $tableData = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                
                // Column 1: User (with avatar via Blade component)
                'user' => view('components.admin-user-cell', [
                    'user' => $user,
                    'showEmail' => true
                ])->render(),
                
                // Column 2: Role (with icon badge)
                'role' => view('components.admin-role-badge', [
                    'role' => $user->role,
                ])->render(),
                
                // Column 3: Department
                'department' => match($user->role) {
                    'student' => $user->student?->department?->name ?? '-',
                    'staff' => $user->staff?->department?->name ?? '-',
                    default => '-'
                },
                
                // Column 4: Status (with icon badge)
                'status' => view('components.admin-status-badge', [
                    'status' => $user->status,
                ])->render(),
                
                // Column 5: Last Login
                'last_login' => $user->last_login_at 
                    ? $user->last_login_at->format('d-M-Y')
                    : 'Never',
            ];
        })->toArray();
        
        return view('admin.users.index', compact(
            'tableData',
            'totalUsers',
            'activeUsers',
            'inactiveUsers',
            'roleCounts'
        ));
    }

    /**
     * Example: Books/Inventory Table
     */
    public function booksIndex(): View
    {
        $books = Book::with('author')->paginate(50);
        
        $tableData = $books->map(function ($book) {
            // Determine status color based on quantity
            $statusClass = $book->quantity > 10 ? 'active' : ($book->quantity > 0 ? 'pending' : 'inactive');
            $statusLabel = $book->quantity > 10 ? 'In Stock' : ($book->quantity > 0 ? 'Low Stock' : 'Out of Stock');
            
            return [
                'id' => $book->id,
                'title' => $book->title,
                'isbn' => $book->isbn,
                'author' => $book->author->name,
                'quantity' => $book->quantity,
                'price' => '$' . number_format($book->price, 2),
                'status' => view('components.admin-status-badge', [
                    'status' => $statusClass,
                    'label' => $statusLabel,
                    'icon' => $statusClass === 'active' ? 'fa-check-circle' : 'fa-exclamation-circle'
                ])->render(),
            ];
        })->toArray();
        
        return view('admin.books.index', compact('tableData'));
    }

    /**
     * Example: Staff Management Table
     */
    public function staffIndex(): View
    {
        $staff = User::where('role', 'staff')->with('staff.department')->paginate(50);
        
        $tableData = $staff->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                
                // User with avatar
                'user' => view('components.admin-user-cell', [
                    'user' => $user,
                ])->render(),
                
                // Department
                'department' => $user->staff?->department?->name ?? '-',
                
                // Position
                'position' => $user->staff?->position ?? '-',
                
                // Status
                'status' => view('components.admin-status-badge', [
                    'status' => $user->status,
                ])->render(),
                
                // Hire Date
                'hire_date' => $user->staff?->hire_date?->format('d-M-Y') ?? '-',
            ];
        })->toArray();
        
        return view('admin.staff.index', compact('tableData'));
    }

    /**
     * Example: Student Management Table with Multiple Actions
     */
    public function studentsIndex(): View
    {
        $students = User::where('role', 'student')
            ->with(['student.department'])
            ->paginate(50);
        
        $tableData = $students->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                
                'profile' => view('components.admin-user-cell', [
                    'user' => $user,
                ])->render(),
                
                'department' => $user->student?->department?->name ?? '-',
                
                'semester' => $user->student?->semester ?? '-',
                
                'roll_no' => $user->student?->roll_no ?? '-',
                
                'status' => view('components.admin-status-badge', [
                    'status' => $user->status,
                ])->render(),
                
                'enrolled_date' => $user->created_at->format('d-M-Y'),
            ];
        })->toArray();
        
        return view('admin.students.index', compact('tableData'));
    }

    /**
     * Example: Activity Logs Table
     */
    public function activityLogsIndex(): View
    {
        $logs = ActivityLog::with('user')->paginate(50);
        
        $tableData = $logs->map(function ($log) {
            // Determine activity color
            $statusMap = [
                'created' => 'pending',
                'updated' => 'active',
                'deleted' => 'inactive',
                'viewed' => 'pending',
            ];
            
            return [
                'id' => $log->id,
                'action' => $log->action,
                'timestamp' => $log->created_at,
                
                // User who performed action
                'user' => view('components.admin-user-cell', [
                    'user' => $log->user,
                    'showEmail' => false
                ])->render(),
                
                // Action type badge
                'activity' => $log->action,
                
                'action_type' => view('components.admin-status-badge', [
                    'status' => $statusMap[$log->action] ?? 'pending',
                    'label' => ucfirst($log->action),
                    'icon' => match($log->action) {
                        'created' => 'fa-plus-circle',
                        'updated' => 'fa-edit',
                        'deleted' => 'fa-trash',
                        'viewed' => 'fa-eye',
                        default => 'fa-circle'
                    }
                ])->render(),
                
                // Entity
                'entity' => $log->loggable_type . ' #' . $log->loggable_id,
                
                // Timestamp
                'timestamp' => $log->created_at->format('d-M-Y H:i:s'),
            ];
        })->toArray();
        
        return view('admin.activity-logs.index', compact('tableData'));
    }
}
