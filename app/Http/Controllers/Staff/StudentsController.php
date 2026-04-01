<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentManagement\ListStudentsRequest;
use App\Services\StudentManagement\StudentManagementActionService;
use App\Services\StudentManagement\StudentManagementDataService;
use App\Services\StudentManagement\StudentProfileDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class StudentsController extends Controller
{
    public function index(StudentManagementDataService $dataService)
    {
        Gate::authorize('access-staff');

        return view('Staff.Students', [
            'departments' => $dataService->getDepartments(),
        ]);
    }

    public function getStudentsData(
        ListStudentsRequest $request,
        StudentManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        return response()->json([
            'success' => true,
            ...$dataService->getListingData($request->validated(), ['can_toggle_status' => true]),
        ]);
    }

    public function getStudentsStats(
        ListStudentsRequest $request,
        StudentManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        $stats = $dataService->getStats($request->validated());

        if ($request->expectsJson()) {
            return response()->json($stats);
        }

        return $stats;
    }

    public function show(string $id, StudentProfileDataService $profileDataService)
    {
        Gate::authorize('access-staff');

        $student = $profileDataService->loadStudent($id);

        if (($student->user?->role ?? null) !== 'student') {
            return redirect()
                ->route('staff.students.index')
                ->with('error', 'This user is not a student.');
        }

        $profileData = $profileDataService->buildStaffProfile($student);

        return view('Staff.StudentView', [
            'student' => $student,
            'studentSummary' => $profileData['summary'],
            'studentBooks' => $profileData['books'],
            'studentFines' => $profileData['fines'],
            'studentRequests' => $profileData['requests'],
            'studentActivities' => $profileData['activities'],
            'studentPrivileges' => $profileData['privileges'],
        ]);
    }

    public function deactivate(
        Request $request,
        string $id,
        StudentManagementActionService $actionService,
        StudentManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        return $this->changeStatus($request, $id, 'inactive', $actionService, $dataService);
    }

    public function activate(
        Request $request,
        string $id,
        StudentManagementActionService $actionService,
        StudentManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        return $this->changeStatus($request, $id, 'active', $actionService, $dataService);
    }

    protected function changeStatus(
        Request $request,
        string $id,
        string $status,
        StudentManagementActionService $actionService,
        StudentManagementDataService $dataService
    ) {
        try {
            $student = $dataService->findStudentById($id);
            $updatedStudent = $actionService->updateStatus($student, $status);

            return response()->json([
                'success' => true,
                'message' => $status === 'active'
                    ? 'Student account activated successfully.'
                    : 'Student account deactivated successfully.',
                'student' => $dataService->serializeStudent($updatedStudent, ['can_toggle_status' => true]),
                'stats' => $dataService->getStats($this->currentListingFilters($request)),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (\Exception $exception) {
            Log::error('Error updating student status', [
                'student_id' => $id,
                'status' => $status,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the student status.',
            ], 500);
        }
    }

    protected function currentListingFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->get('search', '')),
            'department' => (string) $request->get('department', 'all'),
            'status' => strtolower((string) $request->get('status', 'all')),
            'sort' => strtolower((string) $request->get('sort', 'created-desc')),
        ];
    }
}
