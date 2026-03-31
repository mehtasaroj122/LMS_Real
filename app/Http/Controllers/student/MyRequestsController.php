<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\BookRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class MyRequestsController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('access-student');

        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return view('Student.MyRequests', [
                'requestsJson' => json_encode([]),
                'totalRequests' => 0,
                'pendingRequests' => 0,
                'approvedRequests' => 0,
                'rejectedRequests' => 0,
            ]);
        }

        // Get all book requests for the student
        $allRequests = BookRequest::where('student_id', $student->id)
            ->with(['book' => function($q) {
                $q->with('category');
            }])
            ->latest('request_date')
            ->get();

        // Calculate stats
        $totalRequests = $allRequests->count();
        $pendingRequests = $allRequests->where('status', 'pending')->count();
        $approvedRequests = $allRequests->where('status', 'approved')->count();
        $rejectedRequests = $allRequests->where('status', 'rejected')->count();

        // Transform requests data for JavaScript
        $requestsJson = json_encode($allRequests->map(function($bookRequest) use ($user) {
            $processedBy = $this->formatProcessedBy($bookRequest->processed_by, $user?->name);

            return [
                'id' => $bookRequest->id,
                'title' => $bookRequest->book->title,
                'author' => $bookRequest->book->author,
                'isbn' => $bookRequest->book->isbn,
                'category' => $bookRequest->book->category ? $bookRequest->book->category->name : 'uncategorized',
                'requestDate' => $bookRequest->request_date->format('F d, Y'),
                'requestDateRaw' => $bookRequest->request_date->toDateString(),
                'processedDate' => $bookRequest->processed_date ? $bookRequest->processed_date->format('F d, Y') : 'N/A',
                'processedDateRaw' => $bookRequest->processed_date ? $bookRequest->processed_date->toDateString() : null,
                'status' => $bookRequest->status,
                'processedBy' => $processedBy,
            ];
        })->toArray());

        return view('Student.MyRequests', [
            'requestsJson' => $requestsJson,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'rejectedRequests' => $rejectedRequests,
        ]);
    }

    public function cancelRequest($id)
    {
        try {
            Gate::authorize('access-student');

            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();

            if (!$student) {
                return response()->json(['success' => false, 'message' => 'Student record not found']);
            }

            $bookRequest = BookRequest::where('id', $id)
                ->where('student_id', $student->id)
                ->first();

            if (!$bookRequest) {
                return response()->json(['success' => false, 'message' => 'Request not found']);
            }

            if ($bookRequest->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Only pending requests can be cancelled']);
            }

            // Get the staff member's name who is cancelling (the student themselves)
            $cancelledBy = $user->name ?? 'Student';

            $bookRequest->status = 'cancelled';
            $bookRequest->processed_by = $cancelledBy;
            $bookRequest->processed_date = now();
            $bookRequest->save();

            return response()->json([
                'success' => true,
                'message' => 'Request cancelled successfully.',
                'processedBy' => $this->formatProcessedBy($cancelledBy, $user->name),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error cancelling request: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    private function formatProcessedBy(?string $processedBy, ?string $currentUserName): string
    {
        if (empty($processedBy)) {
            return 'N/A';
        }

        if (!empty($currentUserName) && strcasecmp($processedBy, $currentUserName) === 0) {
            return $processedBy . ' (you)';
        }

        return $processedBy;
    }
}
