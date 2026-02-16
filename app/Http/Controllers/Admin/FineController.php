<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Jobs\SendFineEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use App\Helpers\ActivityLogger;
use Throwable;

class FineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('access-admin');
        
        // Get search, status, and pagination parameters
        $search = $request->get('search', '');
        $status = $request->get('status', 'all');
        $page = $request->get('page', 1);
        $perPage = 10;

        // Build query
        $query = \App\Models\Fine::with(['student.user', 'issuedBook.book'])
            ->whereHas('student.user', function($q) {
                $q->where('role', 'student');
            })
            ->orderBy('created_at', 'desc');

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
                ->orWhere('status', 'like', "%$search%");
            });
        }

        // Status filter
        if ($status !== 'all') {
            if (strtolower($status) === 'overdue') {
                $query->whereHas('issuedBook', function($q) {
                    $q->where('due_date', '<', now())->whereNull('return_date');
                });
            } else {
                $query->where('status', strtolower($status));
            }
        }

        // Paginate results
        $fines = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform fines data for the AdminDataTable component
        $finesTableData = $fines->getCollection()->map(function($fine) {
            $dueDate = $fine->issuedBook && $fine->issuedBook->due_date
                ? $fine->issuedBook->due_date->format('M d, Y')
                : 'N/A';
            
            // Map camelCase keys to snake_case for component
            return [
                'id' => $fine->id,
                'student_id' => $fine->student ? $fine->student->roll_no : 'N/A',
                'student_name' => $fine->student && $fine->student->user
                    ? $fine->student->user->name
                    : 'Unknown',
                'book_title' => $fine->issuedBook && $fine->issuedBook->book
                    ? $fine->issuedBook->book->title
                    : 'Unknown',
                'due_date' => $dueDate,
                'days_overdue' => (int)$fine->days_late,
                'fine_amount' => '₹' . number_format((float)$fine->amount, 2),
                'status' => ucfirst($fine->status),
                'created_at' => $fine->created_at->format('M d, Y'),
                'remarks' => $fine->remarks ?? ''
            ];
        })->toArray();

        // Calculate statistics (for all matching records, not just current page)
        $statsQuery = \App\Models\Fine::whereHas('student.user', function($q) {
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
                ->orWhere('status', 'like', "%$search%");
            });
        }
        
        if ($status !== 'all') {
            if (strtolower($status) === 'overdue') {
                $statsQuery->whereHas('issuedBook', function($q) {
                    $q->where('due_date', '<', now())->whereNull('return_date');
                });
            } else {
                $statsQuery->where('status', strtolower($status));
            }
        }
        
        $allFines = $statsQuery->get();
        
        return view('Admin.Fines', [
            'finesTableData' => $finesTableData,
            'fines' => $fines,
            'stats' => [
                'total' => $allFines->sum('amount'),
                'collected' => $allFines->where('status', 'paid')->sum('amount'),
                'pending' => $allFines->filter(function($fine) {
                    return strtolower($fine->status) === 'pending';
                })->sum('amount'),
                'waived' => $allFines->where('status', 'waived')->sum('amount'),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Mark fine as paid
     */
    public function markAsPaid(string $id)
    {
        try {
            Gate::authorize('access-admin');

            $fine = \App\Models\Fine::findOrFail($id);
            $fine->update([
                'status' => 'paid',
                'paid_on' => now()
            ]);
            
            // Log the activity
            try {
                if ($fine->student) {
                    ActivityLogger::logStudentActivity(
                        $fine->student,
                        'fine_paid',
                        "Fine of ₹{$fine->amount} marked as paid",
                        'fine'
                    );
                }
            } catch (Throwable $logError) {
                \Log::warning('Failed to log activity: ' . $logError->getMessage());
            }
            
            // Queue email to send 3 seconds later
            // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
            if ($fine->student && $fine->student->user && $fine->student->user->email) {
                // SendFineEmail::dispatch(
                //     $fine->student->user->email,
                //     $fine->student->user->name,
                //     $fine->amount,
                //     'paid'
                // );
                \Log::info('Fine email would have been sent to: ' . $fine->student->user->email);
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
            Gate::authorize('access-admin');

            $fine = \App\Models\Fine::findOrFail($id);
            
            // Get reason from either 'reason' or 'remarks' field (frontend sends 'reason')
            $reason = trim($request->get('reason') ?? $request->get('remarks') ?? '');
            
            if (empty($reason)) {
                $reason = 'Fine waived by admin';
            }
            
            $fine->update([
                'status' => 'waived',
                'remarks' => $reason
            ]);
            
            // Log the activity
            try {
                if ($fine->student) {
                    ActivityLogger::logStudentActivity(
                        $fine->student,
                        'fine_waived',
                        "Fine of ₹{$fine->amount} waived. Reason: {$reason}",
                        'fine'
                    );
                }
            } catch (Throwable $logError) {
                \Log::warning('Failed to log activity: ' . $logError->getMessage());
            }
            
            // Queue email to send 3 seconds later (include waiver reason)
            // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
            if ($fine->student && $fine->student->user && $fine->student->user->email) {
                // SendFineEmail::dispatch(
                //     $fine->student->user->email,
                //     $fine->student->user->name,
                //     $fine->amount,
                //     'waived',
                //     $reason
                // );
                \Log::info('Fine email would have been sent to: ' . $fine->student->user->email);
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
            Gate::authorize('access-admin');

            $fine = \App\Models\Fine::findOrFail($id);

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
            // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
            // if ($status === 'paid') {
            //     SendFineEmail::dispatch($studentEmail, $studentName, $fineAmount, 'paid', null);
            // } elseif ($status === 'waived') {
            //     $waiveReason = trim($fine->remarks ?? 'Fine waived by admin');
            //     SendFineEmail::dispatch($studentEmail, $studentName, $fineAmount, 'waived', $waiveReason);
            // } else {
            //     // For pending status, send a payment reminder
            //     SendFineEmail::dispatch($studentEmail, $studentName, $fineAmount, 'pending', null);
            // }
            \Log::info('Fine email would have been sent to: ' . $studentEmail . ' (Mail disabled)');

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
            Gate::authorize('access-admin');

            $search = $request->get('search', '');
            $status = $request->get('status', 'all');
            $sort = $request->get('sort', 'date-desc');
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $query = \App\Models\Fine::with(['student.user', 'issuedBook.book'])
                ->whereHas('student.user', function($q) {
                    $q->where('role', 'student');
                });

            // Apply sorting
            switch ($sort) {
                case 'date-asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'date-desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'amount-asc':
                    $query->orderBy('amount', 'asc');
                    break;
                case 'amount-desc':
                    $query->orderBy('amount', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

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
                    $query->where('status', strtolower($status));
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
            $statsQuery = \App\Models\Fine::whereHas('student.user', function($q) {
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
                    $statsQuery->where('status', strtolower($status));
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

    /**
     * Adjust fine amount
     */
    public function adjustFine(Request $request, string $id)
    {
        try {
            Gate::authorize('access-admin');

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'action' => 'required|in:adjust'
            ]);

            $fine = \App\Models\Fine::findOrFail($id);
            $oldAmount = $fine->amount;
            
            $fine->update([
                'amount' => $validated['amount'],
                'remarks' => ($fine->remarks ? $fine->remarks . ' | ' : '') . "Adjusted from ₹{$oldAmount} to ₹{$validated['amount']}"
            ]);

            // Log the activity
            // Log the activity (non-critical, wrap in try-catch)
            try {
                if ($fine->student) {
                    ActivityLogger::logStudentActivity(
                        $fine->student,
                        'fine_adjusted',
                        "Fine amount adjusted from ₹{$oldAmount} to ₹{$validated['amount']}",
                        'fine'
                    );
                }
            } catch (Throwable $logError) {
                \Log::warning('Failed to log activity: ' . $logError->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Fine amount adjusted to ₹{$validated['amount']}"
            ]);
        } catch (Throwable $e) {
            \Log::error('Error adjusting fine: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adjusting fine: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fine payment history
     */
    public function getFineHistory(Request $request, string $id)
    {
        try {
            Gate::authorize('access-admin');

            $fine = \App\Models\Fine::findOrFail($id);

            // Get activity logs related to this fine by looking up student->resource logs
            $historyLogs = \App\Models\ActivityLog::where(function($q) use ($fine) {
                    $q->where('resource_type', 'student')
                      ->where('resource_id', $fine->student_id);
                })
                ->where(function($q2) {
                    $q2->where('action_category', 'fine')
                       ->orWhere('action', 'fine_applied')
                       ->orWhere('action', 'fine_adjusted')
                       ->orWhere('action', 'fine_payment')
                       ->orWhere('action', 'fine_waived');
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $history = $historyLogs->map(function($log) {
                return [
                    'date' => $log->created_at->format('Y-m-d H:i:s'),
                    'action' => $log->description,
                    'user' => $log->user_name ?? ($log->user_id ? \App\Models\User::find($log->user_id)?->name : 'System')
                ];
            })->values()->all();

            // If no history, provide default entries
            if (empty($history)) {
                $history = [
                    [
                        'date' => $fine->created_at->format('Y-m-d H:i:s'),
                        'action' => "Fine created for ₹{$fine->amount}",
                        'user' => 'System'
                    ]
                ];

                if ($fine->status === 'paid' && $fine->paid_on) {
                    $history[] = [
                        'date' => $fine->paid_on->format('Y-m-d H:i:s'),
                        'action' => 'Fine marked as paid',
                        'user' => 'Admin'
                    ];
                }

                if ($fine->status === 'waived') {
                    $history[] = [
                        'date' => $fine->updated_at->format('Y-m-d H:i:s'),
                        'action' => 'Fine waived',
                        'user' => 'Admin'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'history' => $history
            ]);
        } catch (Throwable $e) {
            \Log::error('Error getting fine history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading fine history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk waive fines
     */
    public function bulkWaive(Request $request)
    {
        try {
            Gate::authorize('access-admin');

            $fineIds = $request->get('fine_ids', []);
            $reason = $request->get('reason', 'Bulk waived by admin');

            if (empty($fineIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No fines selected'
                ], 400);
            }

            $count = 0;
            $totalAmount = 0;
            foreach ($fineIds as $fineId) {
                $fine = \App\Models\Fine::findOrFail($fineId);
                if ($fine->status !== 'waived') {
                    $totalAmount += $fine->amount;
                    $fine->update([
                        'status' => 'waived',
                        'remarks' => $reason
                    ]);
                    $count++;
                }
            }

            // Notify admin about bulk operation
            $currentAdmin = auth()->user();
            if ($currentAdmin->role === 'admin') {
                $adminUser = User::where('role', 'admin')->where('id', '!=', $currentAdmin->id)->first();
                if ($adminUser) {
                    Notification::notify(
                        user: $adminUser,
                        type: 'system.bulk_operation',
                        title: 'Bulk Fine Waived',
                        message: "{$currentAdmin->name} waived {$count} fine(s) totaling ₹{$totalAmount}",
                        data: [
                            'count' => $count,
                            'total_amount' => $totalAmount,
                        ],
                        relatedModel: 'Fine',
                        relatedId: 0
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully waived {$count} fine(s)",
                'waived_count' => $count,
                'total_amount' => $totalAmount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk waiving fines: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error bulk waiving fines: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk mark as paid
     */
    public function bulkMarkAsPaid(Request $request)
    {
        try {
            Gate::authorize('access-admin');

            $fineIds = $request->get('fine_ids', []);

            if (empty($fineIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No fines selected'
                ], 400);
            }

            $count = 0;
            $totalAmount = 0;
            foreach ($fineIds as $fineId) {
                $fine = \App\Models\Fine::findOrFail($fineId);
                if ($fine->status !== 'paid') {
                    $totalAmount += $fine->amount;
                    $fine->update([
                        'status' => 'paid',
                        'paid_on' => now()
                    ]);
                    $count++;
                }
            }

            // Notify admin about bulk operation
            $currentAdmin = auth()->user();
            if ($currentAdmin->role === 'admin') {
                $adminUser = User::where('role', 'admin')->where('id', '!=', $currentAdmin->id)->first();
                if ($adminUser) {
                    Notification::notify(
                        user: $adminUser,
                        type: 'system.bulk_operation',
                        title: 'Bulk Fine Marked as Paid',
                        message: "{$currentAdmin->name} marked {$count} fine(s) as paid, totaling ₹{$totalAmount}",
                        data: [
                            'count' => $count,
                            'total_amount' => $totalAmount,
                        ],
                        relatedModel: 'Fine',
                        relatedId: 0
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully marked {$count} fine(s) as paid",
                'marked_count' => $count,
                'total_amount' => $totalAmount
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk marking fines as paid: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking fines as paid: ' . $e->getMessage()
            ], 500);
        }
    }
}

