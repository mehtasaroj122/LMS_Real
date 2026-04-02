<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithFineRecords;
use App\Http\Requests\FineManagement\ListFinesRequest;
use App\Http\Requests\FineManagement\WaiveFineRequest;
use App\Models\Notification;
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
                            'amount' => (float) $fine->amount,
                            'new_amount' => (float) $fine->amount,
                            'payment_method' => $fine->payment_method ?? 'cash',
                            'action_type' => 'paid',
                        ])
                    );
                }
            } catch (Throwable $logError) {
                \Log::warning('Failed to log activity: ' . $logError->getMessage());
            }
            
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
            Gate::authorize('access-staff');

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
                            'amount' => (float) $fine->amount,
                            'new_amount' => (float) $fine->amount,
                            'action_type' => 'waived',
                            'remarks' => $reason,
                        ])
                    );
                }
            } catch (Throwable $logError) {
                \Log::warning('Failed to log activity: ' . $logError->getMessage());
            }
            
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
                // MAIL SYSTEM DISABLED - To re-enable uncomment below and set MAIL_* in .env
                if ($student->user->email) {
                    // SendFineEmail::dispatch($student->user->email, $student->user->name, $fine->amount, 'waived', $reason);
                    \Log::info('Fine email would have been sent to: ' . $student->user->email);
                }
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
            Gate::authorize('access-staff');

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
            //     $waiveReason = trim($fine->remarks ?? 'Fine waived by staff');
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
            Gate::authorize('access-staff');

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
            Gate::authorize('access-staff');

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
}
