<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentManagement\ListStudentsRequest;
use App\Http\Requests\StudentManagement\StoreStudentRequest;
use App\Services\StudentManagement\StudentManagementActionService;
use App\Services\StudentManagement\StudentManagementDataService;
use App\Services\StudentManagement\StudentProfileDataService;
use Illuminate\Database\QueryException;
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

    public function store(
        StoreStudentRequest $request,
        StudentManagementActionService $actionService,
        StudentManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        $validated = $request->validated();

        try {
            $student = $actionService->create($validated);
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $actionService->duplicateStudentErrors($exception, $validated),
            ], 422);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Student created successfully.',
                'student' => $dataService->serializeStudent($student, ['can_toggle_status' => true]),
            ]);
        }

        return redirect()
            ->route('staff.students.index')
            ->with('success', 'Student created successfully.');
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
        string $id,
        StudentManagementActionService $actionService,
        StudentManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        return $this->changeStatus($id, 'inactive', $actionService, $dataService);
    }

    public function activate(
        string $id,
        StudentManagementActionService $actionService,
        StudentManagementDataService $dataService
    ) {
        Gate::authorize('access-staff');

        return $this->changeStatus($id, 'active', $actionService, $dataService);
    }

    protected function changeStatus(
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
                'stats' => $dataService->getStats(),
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
}
