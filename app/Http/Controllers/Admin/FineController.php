<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Fine;
use App\Models\FineSetting;
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

        $this->applyFineSearch($query, $search);

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
            $profilePhoto = $fine->student && $fine->student->user
                ? $fine->student->user->profile_photo
                : null;
            $studentAvatar = !empty($profilePhoto)
                ? (str_starts_with($profilePhoto, 'http') ? $profilePhoto : asset('storage/' . $profilePhoto))
                : null;

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
                'remarks' => $fine->remarks ?? '',
                'student_avatar' => $studentAvatar,
            ];
        })->toArray();

        // Calculate statistics (for all matching records, not just current page)
        $statsQuery = \App\Models\Fine::whereHas('student.user', function($q) {
            $q->where('role', 'student');
        });
        
        $this->applyFineSearch($statsQuery, $search);
        
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

            $fine = Fine::with(['student.user', 'student.privileges', 'issuedBook.book'])->findOrFail($id);
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
                        'fine',
                        $this->buildFineHistoryMetadata($fine, [
                            'action_type' => 'paid',
                            'amount' => (float) $fine->amount,
                            'new_amount' => (float) $fine->amount,
                            'payment_method' => $fine->payment_method ?? 'cash',
                        ])
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

            $fine = Fine::with(['student.user', 'student.privileges', 'issuedBook.book'])->findOrFail($id);
            
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
                        'fine',
                        $this->buildFineHistoryMetadata($fine, [
                            'action_type' => 'waived',
                            'amount' => (float) $fine->amount,
                            'new_amount' => (float) $fine->amount,
                            'remarks' => $reason,
                        ])
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

            $this->applyFineSearch($query, $search);

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
                $profilePhoto = $fine->student && $fine->student->user
                    ? $fine->student->user->profile_photo
                    : null;
                $studentAvatar = !empty($profilePhoto)
                    ? (str_starts_with($profilePhoto, 'http') ? $profilePhoto : asset('storage/' . $profilePhoto))
                    : null;
                return [
                    'id' => $fine->id,
                    'fineId' => 'FN-' . str_pad($fine->id, 6, '0', STR_PAD_LEFT),
                    'studentId' => $fine->student ? $fine->student->roll_no : 'N/A',
                    'studentName' => $fine->student && $fine->student->user
                        ? $fine->student->user->name
                        : 'Unknown',
                    'studentAvatar' => $studentAvatar,
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
            $this->applyFineSearch($statsQuery, $search);
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

    private function applyFineSearch($query, ?string $search): void
    {
        $search = trim((string) ($search ?? ''));

        if ($search === '') {
            return;
        }

        $query->where(function($q) use ($search) {
            $q->whereHas('student.user', function($sq) use ($search) {
                $sq->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhereHas('student', function($sq) use ($search) {
                $sq->where('roll_no', 'like', "%{$search}%");
            })
            ->orWhereHas('issuedBook.book', function($sq) use ($search) {
                $sq->where('title', 'like', "%{$search}%");
            })
            ->orWhere('remarks', 'like', "%{$search}%")
            ->orWhere('amount', 'like', "%{$search}%")
            ->orWhere('status', 'like', "%{$search}%");
        });
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

            $fine = Fine::with(['student.user', 'student.privileges', 'issuedBook.book'])->findOrFail($id);
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
                        'fine',
                        $this->buildFineHistoryMetadata($fine, [
                            'action_type' => 'adjusted',
                            'old_amount' => (float) $oldAmount,
                            'new_amount' => (float) $validated['amount'],
                            'amount_change' => round((float) $validated['amount'] - (float) $oldAmount, 2),
                            'remarks' => "Adjusted from ₹{$oldAmount} to ₹{$validated['amount']}",
                        ])
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

            $fine = Fine::with(['student.user', 'student.privileges', 'issuedBook.book'])->findOrFail($id);
            $currentAmount = (float) $fine->amount;
            $perDayRate = $this->resolveFinePerDayRate($fine);

            $historyLogs = ActivityLog::with('user')
                ->where(function($q) use ($fine) {
                    $q->where('resource_type', 'student')
                      ->where('resource_id', $fine->student_id);
                })
                ->where(function($q2) {
                    $q2->where('action_category', 'fine')
                       ->orWhereIn('action', ['fine_applied', 'fine_adjusted', 'fine_payment', 'fine_paid', 'fine_waived']);
                })
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get();

            $history = $historyLogs
                ->map(fn ($log) => $this->transformFineHistoryLog($log, $fine))
                ->filter()
                ->unique('_dedupe')
                ->values()
                ->map(function ($item) {
                    unset($item['_dedupe']);
                    return $item;
                });

            $originalAmount = $this->resolveOriginalFineAmount($fine, $history);

            if ($history->isEmpty()) {
                $history = collect($this->buildFallbackFineHistory($fine, $originalAmount, $historyLogs));
            }

            $calculation = [
                'baseRate' => $perDayRate,
                'daysLate' => (int) ($fine->days_late ?? 0),
                'subtotal' => $originalAmount,
                'adjustments' => round($currentAmount - $originalAmount, 2),
                'finalAmount' => $currentAmount,
            ];

            return response()->json([
                'success' => true,
                'fineDetails' => [
                    'currentAmount' => $currentAmount,
                    'originalAmount' => $originalAmount,
                    'status' => strtolower((string) $fine->status),
                    'daysLate' => (int) ($fine->days_late ?? 0),
                    'bookTitle' => $fine->issuedBook?->book?->title ?? 'Unknown Book',
                    'isbn' => $fine->issuedBook?->book?->isbn ?? 'N/A',
                    'perDayRate' => $perDayRate,
                ],
                'calculation' => $calculation,
                'history' => $history->values()->all(),
            ]);
        } catch (Throwable $e) {
            \Log::error('Error getting fine history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading fine history: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function buildFineHistoryMetadata(Fine $fine, array $overrides = []): array
    {
        $book = $fine->issuedBook?->book;

        return array_filter([
            'fine_id' => $fine->id,
            'issued_book_id' => $fine->issued_book_id,
            'book_title' => $book?->title,
            'isbn' => $book?->isbn,
            'days_late' => (int) ($fine->days_late ?? 0),
            'status' => strtolower((string) $fine->status),
            ...$overrides,
        ], fn ($value) => $value !== null && $value !== '');
    }

    protected function resolveFinePerDayRate(Fine $fine): float
    {
        if ($fine->student?->privileges?->per_day_fine !== null) {
            return (float) $fine->student->privileges->per_day_fine;
        }

        $fineSetting = FineSetting::where('is_active', true)->first() ?? FineSetting::first();

        return (float) ($fineSetting?->per_day_fine ?? 5);
    }

    protected function decodeFineHistoryMetadata($metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_string($metadata) && $metadata !== '') {
            $decoded = json_decode($metadata, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    protected function normalizeFineHistoryActionType(string $action, string $description = ''): string
    {
        return match ($action) {
            'fine_applied' => 'created',
            'fine_adjusted' => 'adjusted',
            'fine_payment', 'fine_paid' => 'paid',
            'fine_waived' => 'waived',
            default => str_contains(strtolower($description), 'adjust')
                ? 'adjusted'
                : (str_contains(strtolower($description), 'waiv') ? 'waived' : 'created'),
        };
    }

    protected function getFineHistoryActionLabel(string $actionType): string
    {
        return match ($actionType) {
            'created' => 'Created',
            'adjusted' => 'Adjusted',
            'paid' => 'Paid',
            'waived' => 'Waived',
            default => 'Updated',
        };
    }

    protected function extractFineHistoryRemarks(string $actionType, ?string $description): ?string
    {
        if (!$description) {
            return null;
        }

        if ($actionType === 'waived' && preg_match('/Reason:\s*(.+)$/i', $description, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function transformFineHistoryLog(ActivityLog $log, Fine $fine): ?array
    {
        $metadata = $this->decodeFineHistoryMetadata($log->metadata);
        $metadataFineId = isset($metadata['fine_id']) ? (int) $metadata['fine_id'] : null;
        $metadataIssuedBookId = isset($metadata['issued_book_id']) ? (int) $metadata['issued_book_id'] : null;

        if ($metadataFineId !== $fine->id && $metadataIssuedBookId !== (int) $fine->issued_book_id) {
            return null;
        }

        $actionType = $this->normalizeFineHistoryActionType((string) $log->action, (string) $log->description);
        $oldAmount = array_key_exists('old_amount', $metadata) ? (float) $metadata['old_amount'] : null;
        $newAmount = array_key_exists('new_amount', $metadata)
            ? (float) $metadata['new_amount']
            : (array_key_exists('amount', $metadata) ? (float) $metadata['amount'] : null);
        $amountChange = array_key_exists('amount_change', $metadata)
            ? (float) $metadata['amount_change']
            : ($oldAmount !== null && $newAmount !== null ? round($newAmount - $oldAmount, 2) : null);
        $paymentMethod = $actionType === 'paid'
            ? strtolower((string) ($metadata['payment_method'] ?? $fine->payment_method ?? 'cash'))
            : null;
        $remarks = $metadata['remarks'] ?? $this->extractFineHistoryRemarks($actionType, $log->description);

        return [
            'date' => optional($log->created_at)->format('M d, Y h:i A') ?? 'N/A',
            'actionType' => $actionType,
            'action' => $this->getFineHistoryActionLabel($actionType),
            'description' => $metadata['description'] ?? $log->description,
            'user' => $log->user?->name ?? $log->user_name ?? 'System',
            'userRole' => $log->user?->role ?? $log->user_role ?? 'system',
            'amountChange' => $amountChange,
            'oldAmount' => $oldAmount,
            'newAmount' => $newAmount,
            'paymentMethod' => $paymentMethod,
            'remarks' => $remarks,
            '_dedupe' => implode('|', [
                $actionType,
                optional($log->created_at)->format('Y-m-d H:i:s') ?? 'N/A',
                $log->user_name ?? 'System',
                $oldAmount ?? '',
                $newAmount ?? '',
            ]),
        ];
    }

    protected function resolveOriginalFineAmount(Fine $fine, $history): float
    {
        $historyCollection = collect($history);
        $createdEntry = $historyCollection->firstWhere('actionType', 'created');

        if ($createdEntry && $createdEntry['newAmount'] !== null) {
            return (float) $createdEntry['newAmount'];
        }

        $earliestAdjustment = $historyCollection
            ->filter(fn ($item) => ($item['actionType'] ?? null) === 'adjusted' && $item['oldAmount'] !== null)
            ->last();

        if ($earliestAdjustment) {
            return (float) $earliestAdjustment['oldAmount'];
        }

        if ($fine->remarks && preg_match('/Adjusted from\s*₹?([0-9]+(?:\.[0-9]{1,2})?)/i', $fine->remarks, $matches)) {
            return (float) $matches[1];
        }

        return (float) $fine->amount;
    }

    protected function resolveFallbackActor($logs, array $actions = []): array
    {
        $matchedLog = collect($logs)->first(function ($log) use ($actions) {
            return empty($actions) || in_array($log->action, $actions, true);
        });

        return [
            'name' => $matchedLog?->user?->name ?? $matchedLog?->user_name ?? 'System',
            'role' => $matchedLog?->user?->role ?? $matchedLog?->user_role ?? 'system',
        ];
    }

    protected function buildFallbackFineHistory(Fine $fine, float $originalAmount, $logs = null): array
    {
        $currentAmount = (float) $fine->amount;
        $logs = collect($logs);
        $createdActor = $this->resolveFallbackActor($logs, ['fine_applied']);
        $adjustedActor = $this->resolveFallbackActor($logs, ['fine_adjusted']);
        $paidActor = $this->resolveFallbackActor($logs, ['fine_paid', 'fine_payment']);
        $waivedActor = $this->resolveFallbackActor($logs, ['fine_waived']);

        $history = [[
            'date' => optional($fine->created_at)->format('M d, Y h:i A') ?? 'N/A',
            'actionType' => 'created',
            'action' => 'Created',
            'description' => "Fine created for ₹{$originalAmount}" . ($fine->issuedBook?->book?->title ? " on '{$fine->issuedBook->book->title}'" : ''),
            'user' => $createdActor['name'],
            'userRole' => $createdActor['role'],
            'amountChange' => null,
            'oldAmount' => null,
            'newAmount' => $originalAmount,
            'paymentMethod' => null,
            'remarks' => null,
        ]];

        if (round($currentAmount - $originalAmount, 2) !== 0.0) {
            $history[] = [
                'date' => optional($fine->updated_at)->format('M d, Y h:i A') ?? 'N/A',
                'actionType' => 'adjusted',
                'action' => 'Adjusted',
                'description' => 'Fine amount adjusted',
                'user' => $adjustedActor['name'],
                'userRole' => $adjustedActor['role'],
                'amountChange' => round($currentAmount - $originalAmount, 2),
                'oldAmount' => $originalAmount,
                'newAmount' => $currentAmount,
                'paymentMethod' => null,
                'remarks' => preg_match('/Adjusted from/i', (string) $fine->remarks) ? $fine->remarks : null,
            ];
        }

        if ($fine->status === 'paid') {
            $history[] = [
                'date' => optional($fine->paid_on)->format('M d, Y') ?? optional($fine->updated_at)->format('M d, Y h:i A') ?? 'N/A',
                'actionType' => 'paid',
                'action' => 'Paid',
                'description' => 'Fine marked as paid',
                'user' => $paidActor['name'],
                'userRole' => $paidActor['role'],
                'amountChange' => null,
                'oldAmount' => null,
                'newAmount' => $currentAmount,
                'paymentMethod' => strtolower((string) ($fine->payment_method ?? 'cash')),
                'remarks' => null,
            ];
        }

        if ($fine->status === 'waived') {
            $history[] = [
                'date' => optional($fine->updated_at)->format('M d, Y h:i A') ?? 'N/A',
                'actionType' => 'waived',
                'action' => 'Waived',
                'description' => 'Fine waived',
                'user' => $waivedActor['name'],
                'userRole' => $waivedActor['role'],
                'amountChange' => null,
                'oldAmount' => null,
                'newAmount' => $currentAmount,
                'paymentMethod' => null,
                'remarks' => $fine->remarks,
            ];
        }

        return array_reverse($history);
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
