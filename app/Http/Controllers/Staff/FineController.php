<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Fine;
use App\Models\Notification;
use App\Jobs\SendFineEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-staff');
        return view('Staff.Fine');
    }

    /**
     * Mark fine as paid
     */
    public function markAsPaid(string $id)
    {
        try {
            Gate::authorize('access-staff');

            $fine = Fine::findOrFail($id);
            $fine->update([
                'status' => 'paid',
                'paid_on' => now()
            ]);
            
            // Send notification to student
            $student = $fine->student;
            if ($student && $student->user) {
                Notification::notify(
                    user: $student->user,
                    type: 'payment.confirmed',
                    title: 'Fine Payment Received',
                    message: "Your fine payment of ₹{$fine->amount} has been received and marked as paid.",
                    data: [
                        'fine_id' => $fine->id,
                        'amount' => $fine->amount,
                        'student_id' => $student->id,
                    ],
                    relatedModel: 'Fine',
                    relatedId: $fine->id
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Fine marked as paid'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking fine as paid: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating fine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Waive fine
     */
    public function waive(Request $request, string $id)
    {
        try {
            Gate::authorize('access-staff');

            $fine = Fine::findOrFail($id);
            
            // Get reason from either 'reason' or 'remarks' field (frontend sends 'reason')
            $reason = trim($request->get('reason') ?? $request->get('remarks') ?? '');
            
            if (empty($reason)) {
                $staffName = \Illuminate\Support\Facades\Auth::user()->name ?? 'Staff';
                $reason = "Fine waived by staff {$staffName}";
            }
            
            $fine->update([
                'status' => 'waived',
                'remarks' => $reason
            ]);
            
            // Send notification to student
            $student = $fine->student;
            if ($student && $student->user) {
                Notification::notify(
                    user: $student->user,
                    type: 'fine.reminder',
                    title: 'Fine Waived',
                    message: "Your fine of ₹{$fine->amount} has been waived. Reason: {$reason}",
                    data: [
                        'fine_id' => $fine->id,
                        'amount' => $fine->amount,
                        'reason' => $reason,
                    ],
                    relatedModel: 'Fine',
                    relatedId: $fine->id
                );
                // Also queue email to student (includes waiver reason)
                if ($student->user->email) {
                    SendFineEmail::dispatch($student->user->email, $student->user->name, $fine->amount, 'waived', $reason);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Fine waived successfully',
                'data' => $fine->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error waiving fine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error waiving fine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send email notification based on fine status
     */
    public function sendEmailNotification(Request $request, string $id)
    {
        try {
            Gate::authorize('access-staff');

            $fine = Fine::findOrFail($id);

            // Check if student and email exist
            if (!$fine->student || !$fine->student->user || !$fine->student->user->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student email not found'
                ], 400);
            }

            $studentEmail = $fine->student->user->email;
            $studentName = $fine->student->user->name;
            $fineAmount = $fine->amount;
            $status = strtolower($fine->status);

            // Dispatch appropriate email based on status
            if ($status === 'paid') {
                SendFineEmail::dispatch($studentEmail, $studentName, $fineAmount, 'paid', null);
            } elseif ($status === 'waived') {
                $waiveReason = trim($fine->remarks ?? 'Fine waived by staff');
                SendFineEmail::dispatch($studentEmail, $studentName, $fineAmount, 'waived', $waiveReason);
            } else {
                // For pending status, send a payment reminder
                SendFineEmail::dispatch($studentEmail, $studentName, $fineAmount, 'pending', null);
            }

            return response()->json([
                'success' => true,
                'message' => 'Email queued successfully and will be sent shortly'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error sending fine email notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all fines data (AJAX)
     */
    public function getFinesData(Request $request)
    {
        try {
            Gate::authorize('access-staff');

            $search = $request->get('search', '');
            $status = $request->get('status', 'all');
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            $sortBy = $request->get('sort', 'created_at');
            $order = $request->get('order', 'desc');

            $query = Fine::with(['student.user', 'issuedBook.book'])
                ->whereHas('student.user', function($q) {
                    $q->where('role', 'student');
                })
                ->orderBy($sortBy, $order);

            // Search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('student.user', function($sq) use ($search) {
                        $sq->where('name', 'like', "%$search%")
                           ->orWhere('email', 'like', "%$search%");
                    })
                    ->orWhereHas('issuedBook.book', function($sq) use ($search) {
                        $sq->where('title', 'like', "%$search%");
                    })
                    ->orWhere('remarks', 'like', "%$search%")
                    ->orWhere('amount', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ;
                });
            }

            // Status filter
            if ($status !== 'all') {
                if (strtolower($status) === 'overdue') {
                    $query->whereHas('issuedBook', function($q) {
                        $q->where('due_date', '<', now())->whereNull('return_date');
                    });
                } else {
                    $query->where('status', ucfirst(strtolower($status)));
                }
            }

            $paginated = $query->paginate($perPage, ['*'], 'page', $page);

            $fines = $paginated->getCollection()->map(function($fine) {
                $dueDate = $fine->issuedBook && $fine->issuedBook->due_date
                    ? $fine->issuedBook->due_date->format('M d, Y')
                    : 'N/A';
                return [
                    'id' => $fine->id,
                    'fineId' => 'FN-' . str_pad($fine->id, 6, '0', STR_PAD_LEFT),
                    'studentId' => $fine->student ? $fine->student->roll_no : 'N/A',
                    'studentName' => $fine->student && $fine->student->user
                        ? $fine->student->user->name
                        : 'Unknown',
                    'bookTitle' => $fine->issuedBook && $fine->issuedBook->book
                        ? $fine->issuedBook->book->title
                        : 'Unknown',
                    'dueDate' => $dueDate,
                    'daysOverdue' => (int)$fine->days_late,
                    'fineAmount' => (float)$fine->amount,
                    'status' => $fine->status,
                    'createdAt' => $fine->created_at->format('M d, Y'),
                    'remarks' => $fine->remarks ?? ''
                ];
            })->values();

            // Stats: always reflect real DB values (not just current page)
            $statsQuery = Fine::whereHas('student.user', function($q) {
                $q->where('role', 'student');
            });
            if (!empty($search)) {
                $statsQuery->where(function($q) use ($search) {
                    $q->whereHas('student.user', function($sq) use ($search) {
                        $sq->where('name', 'like', "%$search%")
                           ->orWhere('email', 'like', "%$search%");
                    })
                    ->orWhereHas('issuedBook.book', function($sq) use ($search) {
                        $sq->where('title', 'like', "%$search%");
                    })
                    ->orWhere('remarks', 'like', "%$search%")
                    ->orWhere('amount', 'like', "%$search%")
                    ->orWhere('status', 'like', "%$search%")
                    ;
                });
            }
            if ($status !== 'all') {
                if (strtolower($status) === 'overdue') {
                    $statsQuery->whereHas('issuedBook', function($q) {
                        $q->where('due_date', '<', now())->whereNull('return_date');
                    });
                } else {
                    $statsQuery->where('status', ucfirst(strtolower($status)));
                }
            }
            $allFines = $statsQuery->get();
            $totalFines = $allFines->sum('amount');
            $collectedFines = $allFines->where('status', 'paid')->sum('amount');
            $pendingFines = $allFines->filter(function($fine) {
                return strtolower($fine->status) === 'pending';
            })->sum('amount');
            $waivedFines = $allFines->where('status', 'waived')->sum('amount');

            return response()->json([
                'success' => true,
                'fines' => $fines,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ],
                'stats' => [
                    'total' => $totalFines,
                    'collected' => $collectedFines,
                    'pending' => $pendingFines,
                    'waived' => $waivedFines,
                    'count' => $allFines->count(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getFinesData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading fines: ' . $e->getMessage()
            ], 500);
        }
    }
}
