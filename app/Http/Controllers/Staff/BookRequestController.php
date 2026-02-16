<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use App\Models\Student;
use App\Models\Book;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-staff');
        $students = Student::with('user')->whereHas('user', function($q) {
            $q->where('role', 'student');
        })->get();
        $books = Book::all();
        return view('Staff.BookRequest', compact('students', 'books'));
    }

    /**
     * Get requests data with search, filter and pagination for AJAX requests
     */
    public function getRequestsData(Request $request)
    {
        Gate::authorize('access-staff');

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
            $statusIcon = $req->status === 'approved' ? 'fa-check-circle' : ($req->status === 'rejected' ? 'fa-circle-xmark' : 'fa-hourglass-end');
            $statusText = ucfirst($req->status);
            $processedBy = $req->processed_by ?? 'N/A';
            $dateFormatted = $req->request_date->format('Y-m-d');

            $tableRows .= '<tr data-request-id="' . $req->id . '" data-status="' . $req->status . '">';
            
            // Student column
            $studentName = htmlspecialchars($req->student->user->name ?? 'Unknown');
            $studentRoll = htmlspecialchars($req->student->roll_no ?? 'N/A');
            $firstLetter = strtoupper(substr($studentName, 0, 1));
            
            $tableRows .= '<td><div style="display: flex; align-items: center; gap: 10px; min-width: 0;">';
            
            if ($req->student->user->profile_photo) {
                $studentPhoto = str_starts_with($req->student->user->profile_photo, 'http') 
                    ? $req->student->user->profile_photo 
                    : asset('storage/' . $req->student->user->profile_photo);
                $tableRows .= '<img src="' . $studentPhoto . '" alt="' . $studentName . '" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">';
            } else {
                $tableRows .= '<div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; flex-shrink: 0;">' . $firstLetter . '</div>';
            }
            
            $tableRows .= '<div style="flex: 1; min-width: 0;">';
            $tableRows .= '<span style="font-weight: 600; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' . $studentName . '</span>';
            $tableRows .= '<div class="text-muted" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">' . $studentRoll . '</div>';
            $tableRows .= '</div></div></td>';
            
            // Book column
            $tableRows .= '<td>' . htmlspecialchars($req->book->title) . '</td>';
            
            // Date column
            $tableRows .= '<td>' . $dateFormatted . '</td>';
            
            // Status column
            $tableRows .= '<td><span class="status-badge ' . $statusClass . '"><i class="fas ' . $statusIcon . '" style="font-size: 10px;"></i> ' . $statusText . '</span></td>';
            
            // Processed By column
            $tableRows .= '<td>' . htmlspecialchars($processedBy) . '</td>';
            
            // Actions column
                $tableRows .= '<td><div class="action-buttons">';
                if ($req->status === 'pending') {
                    $tableRows .= '<button class="action-btn btn-accept" onclick="processRequest(' . $req->id . ', \'approved\')" title="Approve"><i class="fas fa-check"></i> Accept</button>';
                    $tableRows .= '<button class="action-btn btn-reject" onclick="processRequest(' . $req->id . ', \'rejected\')" title="Reject"><i class="fas fa-times"></i> Reject</button>';
                } else if ($req->status === 'approved') {
                    $tableRows .= '<span class="action-status accepted"><i class="fas fa-check-circle"></i> Accepted</span>';
                } else if ($req->status === 'rejected') {
                    $tableRows .= '<span class="action-status rejected"><i class="fas fa-circle-xmark"></i> Rejected</span>';
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
        Gate::authorize('access-staff');

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
     * Return next pending request not in exclude list (AJAX)
     */
    public function getNextPending(Request $request)
    {
        Gate::authorize('access-staff');

        $exclude = $request->get('exclude', '');
        $excludeIds = array_filter(array_map('intval', array_filter(explode(',', $exclude))));

        $query = BookRequest::with(['student.user', 'book'])
            ->whereHas('student.user', function ($q) {
                $q->where('role', 'student');
            })
            ->where('status', 'pending');

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $req = $query->orderBy('request_date', 'desc')->first();

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'No more pending requests'
            ]);
        }

        return response()->json([
            'success' => true,
            'request' => [
                'id' => $req->id,
                'book' => [
                    'title' => $req->book->title ?? 'Untitled',
                    'author' => $req->book->author ?? ''
                ],
                'student' => [
                    'name' => $req->student->user->name ?? $req->student->name ?? 'Unknown',
                    'student_id' => $req->student->roll_no ?? ''
                ],
                'request_date' => $req->request_date->format('M d, Y')
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('access-staff');

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

        return redirect()->route('staff.book-requests.index')->with('success', 'Request created successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Gate::authorize('access-staff');

        $bookRequest = BookRequest::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $bookRequest->update([
            'status' => $validated['status'],
            'processed_by' => auth()->user()->name,
            'processed_date' => now(),
        ]);
        
        // Send notification to student about request status
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
                    'book_id' => $bookRequest->book_id,
                    'request_id' => $bookRequest->id,
                    'status' => $validated['status'],
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

        return redirect()->route('staff.book-requests.index')->with('success', 'Request updated successfully');
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
        Gate::authorize('access-staff');

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

        return redirect()->route('staff.book-requests.index')->with('success', 'Request deleted successfully');
    }
}
