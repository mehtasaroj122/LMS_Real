<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithFineRecords;
use App\Http\Requests\FineManagement\ListFinesRequest;
use App\Http\Requests\FineManagement\WaiveFineRequest;
use App\Models\ActivityLog;
use App\Models\Fine;
use App\Models\FineSetting;
use App\Models\Notification;
use App\Models\User;
use App\Services\FineManagement\FineManagementDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use Throwable;

class FineController extends Controller
{
    use InteractsWithFineRecords;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('access-admin');

        return view('Admin.Fines');
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

            $fine = $this->loadFineRecord($id);
            $this->ensureFineIsActionable($fine);
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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['fine'][0] ?? 'Only pending fines can be updated.',
            ], 422);
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
    public function waive(WaiveFineRequest $request, string $id)
    {
        try {
            Gate::authorize('access-admin');

            $fine = $this->loadFineRecord($id);
            $this->ensureFineIsActionable($fine);
            $reason = $request->waiverReason();
            
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
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['fine'][0] ?? $e->errors()['reason'][0] ?? 'Unable to waive this fine.',
                'errors' => $e->errors(),
            ], 422);
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

            $fine = $this->loadFineRecord($id);

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
            $status = strtolower((string) $fine->status);

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
    public function getFinesData(ListFinesRequest $request, FineManagementDataService $fineManagementDataService)
    {
        try {
            Gate::authorize('access-admin');

            $listing = $fineManagementDataService->getListingData($request->validated());

            return response()->json([
                'success' => true,
                ...$listing,
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
     * Get export-ready fine data (AJAX)
     */
    public function getExportData(ListFinesRequest $request, FineManagementDataService $fineManagementDataService)
    {
        try {
            Gate::authorize('access-admin');

            $exportData = $fineManagementDataService->getExportData($request->validated());

            return response()->json([
                'success' => true,
                ...$exportData,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getExportData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading export data: ' . $e->getMessage()
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

    protected function resolveFinePerDayRate(Fine $fine): float
    {
        if ($fine->student?->privileges?->per_day_fine !== null) {
            return (float) $fine->student->privileges->per_day_fine;
        }

        return (float) FineSetting::resolveActive()->per_day_fine;
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
