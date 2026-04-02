<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithFineRecords;
use App\Http\Requests\FineManagement\BulkSendFineEmailRequest;
use App\Http\Requests\FineManagement\BulkUpdateFineStatusRequest;
use App\Http\Requests\FineManagement\ListFinesRequest;
use App\Http\Requests\FineManagement\WaiveFineRequest;
use App\Services\FineManagement\FineManagementActionService;
use App\Services\FineManagement\FineManagementDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

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
    public function markAsPaid(string $id, FineManagementActionService $actionService)
    {
        try {
            Gate::authorize('access-staff');

            $fine = $this->loadFineRecord($id);
            $actionService->markAsPaid($fine, [
                'notify_student' => true,
                'log_email' => false,
            ]);

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
    public function waive(
        WaiveFineRequest $request,
        string $id,
        FineManagementActionService $actionService
    )
    {
        try {
            Gate::authorize('access-staff');

            $fine = $this->loadFineRecord($id);
            $waiverReason = $request->waiverReason();
            $updatedFine = $actionService->waive($fine, $waiverReason, [
                'notify_student' => true,
                'log_email' => true,
            ]);

            // Notify student about fine waived with reason
            if ($fine->student && $fine->student->user) {
                \App\Models\Notification::notify(
                    user: $fine->student->user,
                    type: 'fine.waived_by_staff',
                    title: 'Fine Waived',
                    message: "Your fine of ₹{$fine->amount} has been waived by staff. Reason: {$waiverReason}",
                    data: [
                        'fine_id' => $fine->id,
                        'amount' => $fine->amount,
                        'reason' => $waiverReason,
                        'staff_name' => auth()->user()?->name,
                    ],
                    relatedModel: 'Fine',
                    relatedId: $fine->id
                );
            }

            // Notify admin about fine waived by staff with reason
            $staffName = auth()->user()?->name ?? 'Staff Member';
            $admin = \App\Models\User::where('role', 'admin')->first();
            if ($admin) {
                \App\Models\Notification::notify(
                    user: $admin,
                    type: 'staff.fine_waived',
                    title: 'Fine Waived by Staff',
                    message: "{$staffName} waived fine of ₹{$fine->amount} for {$fine->student?->user?->name}",
                    data: [
                        'fine_id' => $fine->id,
                        'amount' => $fine->amount,
                        'student_name' => $fine->student?->user?->name,
                        'reason' => $waiverReason,
                        'staff_name' => $staffName,
                    ],
                    relatedModel: 'Fine',
                    relatedId: $fine->id
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Fine waived successfully',
                'data' => $updatedFine,
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
    public function sendEmailNotification(
        Request $request,
        string $id,
        FineManagementActionService $actionService
    )
    {
        try {
            Gate::authorize('access-staff');

            $fine = $this->loadFineRecord($id);
            $actionService->sendEmailNotification($fine);

            return response()->json([
                'success' => true,
                'message' => 'Email queued successfully and will be sent shortly'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Student email not found',
                'errors' => $e->errors(),
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Error sending fine email notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkSendEmail(
        BulkSendFineEmailRequest $request,
        FineManagementActionService $actionService
    ) {
        try {
            Gate::authorize('access-staff');

            $results = $actionService->bulkSendEmailNotifications($request->fineIds());

            return response()->json([
                'success' => true,
                'message' => $this->buildBulkEmailMessage(
                    (int) $results['processed_count'],
                    (int) $results['skipped_count'],
                    (int) $results['recipient_count']
                ),
                'processedCount' => (int) $results['processed_count'],
                'skippedCount' => (int) $results['skipped_count'],
                'recipientCount' => (int) $results['recipient_count'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Unable to queue emails for the selected fines.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error bulk sending fine emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending emails: ' . $e->getMessage(),
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

    public function bulkUpdate(
        BulkUpdateFineStatusRequest $request,
        FineManagementActionService $actionService
    ) {
        try {
            Gate::authorize('access-staff');

            $status = $request->status();
            $results = $actionService->bulkUpdateStatus(
                $request->fineIds(),
                $status,
                [
                    'notify_student' => true,
                    'log_email' => $status === 'waived',
                ],
                $request->waiverReason()
            );

            return response()->json([
                'success' => true,
                'message' => $this->buildBulkActionMessage(
                    $status,
                    (int) $results['updated_count'],
                    (int) $results['skipped_count'],
                    (float) $results['total_amount']
                ),
                'status' => $status,
                'processedCount' => (int) $results['updated_count'],
                'skippedCount' => (int) $results['skipped_count'],
                'totalAmount' => (float) $results['total_amount'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?: 'Unable to update the selected fines.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error bulk updating fines: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating fines: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildBulkActionMessage(string $status, int $processedCount, int $skippedCount, float $totalAmount): string
    {
        $actionLabel = $status === 'paid' ? 'marked as paid' : 'waived';
        $amountLabel = '₹' . number_format($totalAmount, 2);

        if ($processedCount === 0 && $skippedCount > 0) {
            return "No selected fines were {$actionLabel} because they were no longer pending.";
        }

        if ($skippedCount > 0) {
            return "{$processedCount} fine(s) {$actionLabel} for {$amountLabel}. {$skippedCount} selected fine(s) were skipped because they were no longer pending.";
        }

        return "{$processedCount} fine(s) {$actionLabel} for {$amountLabel}.";
    }

    protected function buildBulkEmailMessage(int $processedCount, int $skippedCount, int $recipientCount): string
    {
        $recipientLabel = $recipientCount === 1 ? 'recipient' : 'recipients';

        if ($processedCount === 0 && $skippedCount > 0) {
            return 'No fine emails were queued because the selected records no longer have a student email address.';
        }

        if ($skippedCount > 0) {
            return "{$processedCount} fine email(s) queued for {$recipientCount} {$recipientLabel}. {$skippedCount} selected fine(s) were skipped because a student email address was unavailable.";
        }

        return "{$processedCount} fine email(s) queued for {$recipientCount} {$recipientLabel}.";
    }
}
