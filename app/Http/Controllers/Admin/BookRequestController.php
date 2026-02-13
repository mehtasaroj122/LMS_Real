<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Student;
use App\Models\Book;
use App\Models\User;
use App\Models\Notification;
use App\Jobs\SendBookRequestStatusEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class BookRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-admin');
        $students = Student::with('user')->whereHas('user', function($q) {
            $q->where('role', 'student');
        })->get();
        $books = Book::all();
        return view('Admin.BookRequest', compact('students', 'books'));
    }

    /**
     * Get requests data with search, filter and pagination for AJAX requests
     */
    public function getRequestsData(Request $request)
    {
        Gate::authorize('access-admin');

        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $page = $request->get('page', 1);
        $perPage = 10;

        // Build query - only show student requests
        $query = BookRequest::with(['student.user', 'book'])->whereHas('student.user', function($q) {
            $q->where('role', 'student');
        });

        // Search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->whereHas('student.user', function($sq) use ($search) {
                    $sq->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('student', function($sq) use ($search) {
                    $sq->where('roll_no', 'like', '%' . $search . '%');
                })
                ->orWhereHas('book', function($bq) use ($search) {
                    $bq->where('title', 'like', '%' . $search . '%')
                      ->orWhere('author', 'like', '%' . $search . '%');
                });
            });
        }

        // Status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Order by request_date descending (latest first)
        $query->orderBy('request_date', 'desc');

        // Paginate
        $requests = $query->paginate($perPage, ['*'], 'page', $page);

        // Generate table rows HTML
        $tableRows = '';
        foreach ($requests->items() as $req) {
            $statusClass = $req->status === 'approved' ? 'status-approved' : ($req->status === 'rejected' ? 'status-rejected' : 'status-pending');
            $statusIcon = $req->status === 'approved' ? 'check-circle' : ($req->status === 'rejected' ? 'x-circle' : 'clock');
            $statusText = ucfirst($req->status);
            $processedBy = $req->processed_by ?? 'N/A';

            $tableRows .= '<tr class="border-b border-gray-200 dark:border-gray-700" data-request-id="' . $req->id . '" data-status="' . $req->status . '">';
            $tableRows .= '<td class="py-4 px-6"><div class="student-info"><span class="student-name">' . htmlspecialchars($req->student->user->name ?? 'Unknown') . '</span><span class="student-id">' . htmlspecialchars($req->student->roll_no ?? 'N/A') . '</span></div></td>';
            $tableRows .= '<td class="py-4 px-6"><div class="book-info"><div class="book-title">' . htmlspecialchars($req->book->title) . '</div><div class="book-author">' . htmlspecialchars($req->book->author) . '</div></div></td>';
            $tableRows .= '<td class="py-4 px-6 text-secondary">' . $req->request_date->format('Y-m-d H:i:s') . '</td>';
            $tableRows .= '<td class="py-4 px-6"><div class="status-badge ' . $statusClass . '"><i data-lucide="' . $statusIcon . '" class="w-3 h-3"></i>' . $statusText . '</div></td>';
            $tableRows .= '<td class="py-4 px-6 text-secondary">' . htmlspecialchars($processedBy) . '</td>';
            $tableRows .= '<td class="py-4 px-6"><div class="action-buttons">';
            
            if ($req->status === 'pending') {
                $tableRows .= '<button class="action-btn btn-accept" onclick="processRequest(' . $req->id . ', \'approved\')"><i data-lucide="check" class="w-3 h-3"></i>Accept</button>';
                $tableRows .= '<button class="action-btn btn-reject" onclick="processRequest(' . $req->id . ', \'rejected\')"><i data-lucide="x" class="w-3 h-3"></i>Reject</button>';
            }
            
            $tableRows .= '</div></td>';
            $tableRows .= '</tr>';
        }

        // Generate pagination HTML
        $paginationHtml = $requests->links()->toHtml();

        return response()->json([
            'success' => true,
            'tableRows' => $tableRows,
            'pagination' => $paginationHtml,
            'total' => $requests->total(),
            'current_page' => $requests->currentPage(),
            'last_page' => $requests->lastPage(),
        ]);
    }

    /**
     * Get requests statistics
     */
    public function getRequestStats(Request $request = null)
    {
        Gate::authorize('access-admin');

        $pendingCount = BookRequest::where('status', 'pending')->count();
        $approvedCount = BookRequest::where('status', 'approved')->count();
        $rejectedCount = BookRequest::where('status', 'rejected')->count();

        $stats = [
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ];

        // Return JSON if AJAX request
        if ($request && $request->expectsJson()) {
            return response()->json($stats);
        }

        return $stats;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('access-admin');

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'book_id' => 'required|exists:books,id',
        ]);

        // Check if student already has an active request for this book
        $existingRequest = BookRequest::where('student_id', $validated['student_id'])
            ->where('book_id', $validated['book_id'])
            ->whereIn('status', ['pending', 'approved', 'issued'])
            ->first();
        
        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'This student already has an active request for this book. Please wait until the book is returned before creating a new request.'
            ], 400);
        }

        $validated['request_date'] = now();
        $validated['status'] = 'pending';

        $bookRequest = BookRequest::create($validated);

        // Check if this is an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Request created successfully',
                'request' => $bookRequest,
            ]);
        }

        return redirect()->route('admin.book-requests.index')->with('success', 'Request created successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('access-admin');

        $bookRequest = BookRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $bookRequest->update([
            'status' => $validated['status'],
            'processed_by' => auth()->user()->name,
            'processed_date' => now(),
        ]);
        
        // Notify student about request status
        if ($bookRequest->student && $bookRequest->student->user) {
            $notificationType = ($validated['status'] === 'approved') ? 'request.approved' : 'request.rejected';
            $title = ($validated['status'] === 'approved') ? 'Request Approved' : 'Request Rejected';
            $message = ($validated['status'] === 'approved')
                ? "Your request for '{$bookRequest->book->title}' has been approved!"
                : "Your request for '{$bookRequest->book->title}' has been rejected.";
            
            Notification::notify(
                user: $bookRequest->student->user,
                type: $notificationType,
                title: $title,
                message: $message,
                data: [
                    'request_id' => $bookRequest->id,
                    'book_id' => $bookRequest->book_id,
                    'status' => $validated['status'],
                    'book_title' => $bookRequest->book->title,
                ],
                relatedModel: 'BookRequest',
                relatedId: $bookRequest->id
            );
            
            // Queue email to send 3 seconds later
            // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
            if ($bookRequest->student->user->email) {
                // SendBookRequestStatusEmail::dispatch(
                //     $bookRequest->student->user->email,
                //     $bookRequest->student->user->name,
                //     $bookRequest->book->title,
                //     $validated['status']
                // );
                \Log::info('Book request status email would have been sent to: ' . $bookRequest->student->user->email);
            }

        }
        
        // Notify admin about request processing
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            Notification::notify(
                user: $admin,
                type: 'request.pending',
                title: 'Book Request Processed',
                message: "Request from {$bookRequest->student->user->name} for '{$bookRequest->book->title}' has been {$validated['status']}",
                data: [
                    'request_id' => $bookRequest->id,
                    'status' => $validated['status'],
                    'student_id' => $bookRequest->student_id,
                    'book_id' => $bookRequest->book_id,
                ],
                relatedModel: 'BookRequest',
                relatedId: $bookRequest->id
            );
        }

        // Check if this is an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Request updated successfully',
                'request' => $bookRequest,
            ]);
        }

        return redirect()->route('admin.book-requests.index')->with('success', 'Request updated successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Gate::authorize('access-admin');

        $bookRequest = BookRequest::findOrFail($id);
        $bookRequest->delete();

        // Check if this is an AJAX request
        $request = request();
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Request deleted successfully',
            ]);
        }

        return redirect()->route('admin.book-requests.index')->with('success', 'Request deleted successfully');
    }
}
