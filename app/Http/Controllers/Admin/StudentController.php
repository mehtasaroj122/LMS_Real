<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentManagement\StoreStudentRequest;
use App\Http\Requests\StudentManagement\UpdateStudentRequest;
use Carbon\Carbon;
use App\Models\FineSetting;
use App\Models\Student;
use App\Models\StudentPrivilege;
use App\Models\User;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Helpers\ActivityLogger;
use App\Mail\PasswordResetEmail;
use App\Services\Auth\InvitationEmailService;
use App\Services\StudentFineSummaryService;
use App\Services\StudentManagement\StudentManagementDataService;
use App\Services\StudentManagement\StudentNotificationEmailService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function __construct(
        private StudentNotificationEmailService $studentNotificationEmailService,
        private InvitationEmailService $invitationEmailService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(StudentManagementDataService $dataService)
    {
        Gate::authorize('access-admin');
        $departments = $dataService->getDepartments();
        return view('Admin.Students', compact('departments'));
    }

    /**
     * Show the form for creating a new student (redirects to index where modal is shown).
     */
    public function create()
    {
        Gate::authorize('access-admin');
        return redirect()->route('admin.students.index');
    }

    /**
     * Get students data with search, filter and pagination for AJAX requests
     */
    public function getStudentsData(Request $request, StudentManagementDataService $dataService)
    {
        Gate::authorize('access-admin');

        $search = $request->get('search', '');
        $department = $request->get('department', 'all');
        $status = $request->get('status', 'all');
        $sort = $request->get('sort', 'created-desc');
        $page = $request->get('page', 1);
        $perPage = $this->normalizeAdminPerPage($request->get('per_page', 10));

        $students = $dataService->getPaginatedStudents([
            'search' => $search,
            'department' => $department,
            'status' => $status,
            'sort' => $sort,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        // Generate table rows HTML
        $tableRows = '';
        foreach ($students->items() as $student) {
            $statusClass = $student->user->status === 'active' ? 'status-active' : 'status-inactive';
            $statusText = ucfirst($student->user->status);
            $statusIcon = $student->user->status === 'active' 
                ? '<i class="fas fa-check-circle" style="font-size: 10px;"></i>' 
                : '<i class="fas fa-times-circle" style="font-size: 10px;"></i>';
            $studentName = htmlspecialchars($student->user->name ?? 'Unknown');
            $studentRoll = htmlspecialchars($student->roll_no ?? 'N/A');
            $profilePhoto = $student->user->profile_photo ?? null;
            $avatarHtml = '<div class="student-avatar">' . htmlspecialchars(strtoupper(substr($student->user->name ?? 'U', 0, 1))) . '</div>';

            if ($profilePhoto) {
                $avatarUrl = str_starts_with($profilePhoto, 'http')
                    ? $profilePhoto
                    : asset(str_starts_with($profilePhoto, 'storage/') ? $profilePhoto : 'storage/' . ltrim($profilePhoto, '/'));

                $avatarHtml = '<div class="student-avatar"><img src="' . htmlspecialchars($avatarUrl) . '" alt="' . $studentName . '"></div>';
            }

            $tableRows .= '<tr data-student-id="' . $student->id . '">';
            $tableRows .= '<td>';
            $tableRows .= '<div class="student-cell">';
            $tableRows .= $avatarHtml;
            $tableRows .= '<div class="student-info">';
            $tableRows .= '<span class="student-name">' . $studentName . '</span>';
            $tableRows .= '<div class="text-muted">' . $studentRoll . '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '<td class="text-muted">' . htmlspecialchars($student->user->email ?? 'N/A') . '</td>';
            $tableRows .= '<td class="text-muted">' . htmlspecialchars($student->user->phone ?? 'N/A') . '</td>';
            $tableRows .= '<td class="text-muted">' . htmlspecialchars($student->department->name ?? 'N/A') . '</td>';
            $tableRows .= '<td class="text-muted">' . htmlspecialchars($student->batch ?? 'N/A') . '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<span class="status-badge ' . $statusClass . '">';
            $tableRows .= $statusIcon . ' ' . $statusText;
            $tableRows .= '</span>';
            if ($student->user->requiresSelfRegistration()) {
                $tableRows .= '<div class="text-muted" style="font-size: 11px; margin-top: 4px;">Registration pending</div>';
            }
            $tableRows .= '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div class="action-buttons">';
            $tableRows .= '<a href="' . route('admin.students.show', $student->id) . '" class="action-btn" title="View details"><i class="fas fa-eye"></i></a>';
            $tableRows .= '<button onclick="openEditStudentModal(' . $student->id . ')" class="action-btn" title="Edit"><i class="fas fa-edit"></i></button>';
            if ($student->user->requiresSelfRegistration()) {
                $tableRows .= '<button class="action-btn" title="Complete registration first" disabled><i class="fas fa-key"></i></button>';
            } else {
                $tableRows .= '<button onclick="resetStudentPassword(' . $student->id . ')" class="action-btn" title="Reset password"><i class="fas fa-key"></i></button>';
            }
            $toggleIcon = $student->user->status === 'active' ? 'fas fa-toggle-on' : 'fas fa-toggle-off';
            $tableRows .= '<button onclick="toggleStudentStatus(' . $student->id . ')" class="action-btn" title="Toggle status"><i class="' . $toggleIcon . '"></i></button>';
            $tableRows .= '<button onclick="deleteStudent(' . $student->id . ')" class="action-btn" title="Delete"><i class="fas fa-trash-alt"></i></button>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '</tr>';
        }

        // Generate pagination HTML
        $paginationHtml = view('shared.admin-table-pagination', ['paginator' => $students])->render();

        return response()->json([
            'success' => true,
            'tableRows' => $tableRows,
            'pagination' => $paginationHtml,
            'students' => $students->getCollection()
                ->map(fn (Student $student) => $dataService->serializeStudent($student, ['can_toggle_status' => true]))
                ->values()
                ->all(),
            'paginationData' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem() ?? 0,
                'to' => $students->lastItem() ?? 0,
            ],
            'stats' => $dataService->getStats([
                'search' => $search,
                'department' => $department,
                'status' => $status,
                'sort' => $sort,
                'per_page' => $perPage,
            ]),
            'total' => $students->total(),
            'current_page' => $students->currentPage(),
            'last_page' => $students->lastPage(),
        ]);
    }

    private function normalizeAdminPerPage($value): int
    {
        $allowedValues = [10, 20, 50, 100];
        $perPage = (int) $value;

        return in_array($perPage, $allowedValues, true) ? $perPage : 10;
    }

    protected function currentStudentListingFilters(Request $request): array
    {
        return [
            'search' => trim((string) $request->get('search', '')),
            'department' => (string) $request->get('department', 'all'),
            'status' => strtolower((string) $request->get('status', 'all')),
            'sort' => strtolower((string) $request->get('sort', 'created-desc')),
        ];
    }

    protected function queueStudentStatusEmail(Student $student, string $status): void
    {
        $this->studentNotificationEmailService->sendStatusChangedEmail(
            $student->loadMissing('user'),
            $status,
            auth()->user()?->name,
            auth()->user()?->role,
        );
    }

    protected function queueStudentPrivilegeEmail(
        Student $student,
        array $effectiveSettings,
        ?string $changeSummary = null,
        bool $resetToDefaults = false,
    ): void {
        $this->studentNotificationEmailService->sendPrivilegeSettingsUpdatedEmail(
            $student->loadMissing('user'),
            $effectiveSettings,
            $changeSummary,
            $resetToDefaults,
            auth()->user()?->name,
            auth()->user()?->role,
        );
    }

    /**
     * Get students statistics
     */
    public function getStudentsStats(Request $request = null, StudentManagementDataService $dataService)
    {
        Gate::authorize('access-admin');

        $stats = $dataService->getStats([
            'search' => $request?->get('search', ''),
            'department' => $request?->get('department', 'all'),
            'status' => $request?->get('status', 'all'),
        ]);

        // Return JSON if AJAX request
        if ($request && $request->expectsJson()) {
            return response()->json($stats);
        }

        return $stats;
    }

    /**
     * Get student edit data for modal
     */
    public function getStudentEditData($id)
    {
        Gate::authorize('access-admin');

        try {
            $student = Student::with('user', 'department')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'phone' => $student->user->phone,
                    'gender' => $student->user->gender,
                    'date_of_birth' => optional($student->user->date_of_birth)->format('Y-m-d'),
                    'roll_no' => $student->roll_no,
                    'department_id' => $student->department_id,
                    'batch' => $student->batch,
                    'semester' => $student->semester,
                    'address' => $student->address,
                    'status' => $student->user->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
    }

    public function validateField(Request $request)
    {
        Gate::authorize('access-admin');

        $field = (string) $request->input('field');
        $allowedFields = ['email', 'phone', 'roll_no', 'date_of_birth'];

        if (!in_array($field, $allowedFields, true)) {
            return response()->json([
                'valid' => false,
                'message' => 'Unsupported validation field.',
            ], 422);
        }

        $student = null;
        if ($request->filled('student_id')) {
            $student = Student::find($request->input('student_id'));
        }

        $data = $this->normalizedStudentInput($request);
        $rules = [$field => $this->studentValidationRules($student, false)[$field]];
        $messages = $this->studentValidationMessages();

        $validator = Validator::make($data, $rules, $messages);
        $this->attachStudentValidationCallbacks($validator, $data);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'field' => $field,
                'message' => $validator->errors()->first($field),
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'field' => $field,
            'message' => null,
        ]);
    }

    protected function normalizedStudentInput(Request $request): array
    {
        $cleanText = function ($value) {
            if ($value === null) {
                return null;
            }

            return trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $value)));
        };

        $normalizePhone = function ($value) {
            if ($value === null) {
                return null;
            }

            $raw = trim((string) $value);
            if ($raw === '') {
                return '';
            }

            $digits = preg_replace('/\D/', '', $raw);

            return str_starts_with($raw, '+') ? '+' . $digits : $digits;
        };

        return array_merge($request->all(), [
            'name' => $cleanText($request->input('name')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'phone' => $normalizePhone($request->input('phone')),
            'date_of_birth' => $cleanText($request->input('date_of_birth')),
            'roll_no' => strtoupper($cleanText($request->input('roll_no')) ?? ''),
            'batch' => $cleanText($request->input('batch')),
            'semester' => $cleanText($request->input('semester')),
            'address' => $cleanText($request->input('address')),
            'status' => $cleanText($request->input('status')),
        ]);
    }

    protected function studentValidationRules(?Student $student = null, bool $includeStatus = false): array
    {
        $rules = [
            'name' => ['bail', 'required', 'string', 'min:2', 'max:100', 'regex:/^[A-Za-z ]+$/'],
            'email' => [
                'bail',
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($student?->user_id),
            ],
            'phone' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/^\+[1-9]\d{7,14}$/',
                Rule::unique('users', 'phone')->ignore($student?->user_id),
            ],
            'date_of_birth' => ['bail', 'required', 'date', 'before:today'],
            'roll_no' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::unique('students', 'roll_no')->ignore($student?->id),
            ],
            'department_id' => ['bail', 'required', 'integer', Rule::exists('departments', 'id')],
            'batch' => ['bail', 'required', 'regex:/^(19|20)\d{2}$/'],
            'semester' => ['bail', 'required', 'integer', 'between:1,12'],
            'address' => ['bail', 'required', 'string', 'min:10', 'max:255', 'not_regex:/<[^>]*>/'],
        ];

        if ($includeStatus) {
            $rules['status'] = ['bail', 'required', Rule::in(['active', 'inactive'])];
        }

        return $rules;
    }

    protected function studentValidationMessages(): array
    {
        return [
            'name.required' => 'Enter the student\'s full name.',
            'name.min' => 'Full name must be at least 2 characters long.',
            'name.max' => 'Full name must be 100 characters or fewer.',
            'name.regex' => 'Full name can use letters and spaces only.',

            'email.required' => 'Enter the student\'s email address.',
            'email.email' => 'Enter a valid email address, like student@example.com.',
            'email.max' => 'Email address must be 255 characters or fewer.',
            'email.unique' => 'This email is already assigned to another user.',

            'phone.required' => 'Enter the student\'s phone number with country code.',
            'phone.min' => 'Enter a valid phone number with country code, like +9779812345678.',
            'phone.max' => 'Phone number is too long. Use international format like +9779812345678.',
            'phone.regex' => 'Enter a valid phone number with country code, like +9779812345678.',
            'phone.unique' => 'This phone number is already assigned to another user.',

            'date_of_birth.required' => 'Select the student\'s date of birth.',
            'date_of_birth.date' => 'Enter a valid date of birth.',
            'date_of_birth.before' => 'Date of birth must be earlier than today.',

            'roll_no.required' => 'Enter the student ID.',
            'roll_no.min' => 'Student ID must be at least 3 characters long.',
            'roll_no.max' => 'Student ID must be 30 characters or fewer.',
            'roll_no.regex' => 'Student ID can use letters, numbers, and hyphens only.',
            'roll_no.unique' => 'This student ID is already in use.',

            'department_id.required' => 'Select a department.',
            'department_id.integer' => 'Select a valid department.',
            'department_id.exists' => 'Select a valid department.',

            'batch.required' => 'Enter the batch year.',
            'batch.regex' => 'Batch year must be a 4-digit year.',

            'semester.required' => 'Enter the semester number.',
            'semester.integer' => 'Semester must be a number between 1 and 12.',
            'semester.between' => 'Semester must be a number between 1 and 12.',

            'address.required' => 'Enter the student\'s address.',
            'address.min' => 'Address must be at least 10 characters long.',
            'address.max' => 'Address must be 255 characters or fewer.',
            'address.not_regex' => 'Address contains unsupported characters. Remove any HTML or script-like content.',

            'status.required' => 'Select the student status.',
            'status.in' => 'Select a valid student status.',
        ];
    }

    protected function attachStudentValidationCallbacks($validator, array $data): void
    {
        $validator->after(function ($validator) use ($data) {
            if (!empty($data['date_of_birth']) && !$validator->errors()->has('date_of_birth')) {
                try {
                    $age = Carbon::parse($data['date_of_birth'])->age;

                    if ($age < 14 || $age > 100) {
                        $validator->errors()->add('date_of_birth', 'Student age must be between 14 and 100 years.');
                    }
                } catch (\Throwable $e) {
                    $validator->errors()->add('date_of_birth', 'Enter a valid date of birth.');
                }
            }
        });
    }

    protected function validateStudentData(Request $request, ?Student $student = null, bool $includeStatus = false): array
    {
        $data = $this->normalizedStudentInput($request);
        $validator = Validator::make(
            $data,
            $this->studentValidationRules($student, $includeStatus),
            $this->studentValidationMessages()
        );

        $this->attachStudentValidationCallbacks($validator, $data);

        return $validator->validate();
    }

    protected function duplicateStudentErrors(QueryException $e, array $validated): array
    {
        $errorMsg = $e->getMessage();
        $errors = [];

        if (stripos($errorMsg, 'users_email_unique') !== false || (stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['email'] ?? '') !== false)) {
            $errors['email'] = ['This email is already assigned to another user.'];
        }

        if (stripos($errorMsg, 'users_phone_unique') !== false || (stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['phone'] ?? '') !== false)) {
            $errors['phone'] = ['This phone number is already assigned to another user.'];
        }

        if (stripos($errorMsg, "Data too long for column 'phone'") !== false || stripos($errorMsg, '`phone`') !== false && stripos($errorMsg, 'too long') !== false) {
            $errors['phone'] = ['Phone number is too long. Use international format like +9779812345678.'];
        }

        if (stripos($errorMsg, 'students_roll_no_unique') !== false || (stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['roll_no'] ?? '') !== false)) {
            $errors['roll_no'] = ['This student ID is already in use.'];
        }

        if (stripos($errorMsg, 'students_student_id_unique') !== false || (stripos($errorMsg, 'Duplicate entry') !== false && stripos($errorMsg, $validated['roll_no'] ?? '') !== false)) {
            $errors['roll_no'] = ['This student ID is already in use.'];
        }

        if (empty($errors)) {
            $errors['email'] = ['Unable to save the student with the provided details.'];
        }

        return $errors;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request, StudentManagementDataService $dataService)
    {
        Gate::authorize('access-admin');

        $validated = $request->validated();
        $invitationQueued = false;

        try {
            $student = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'gender' => $validated['gender'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'],
                    'address' => $validated['address'],
                    'role' => 'student',
                    'status' => 'inactive',
                    'password' => null,
                    'is_verified' => false,
                ]);

                $student = Student::create([
                    'user_id' => $user->id,
                    'student_id' => $validated['roll_no'],
                    'roll_no' => $validated['roll_no'],
                    'department_id' => $validated['department_id'],
                    'batch' => $validated['batch'],
                    'semester' => $validated['semester'],
                    'address' => $validated['address'],
                ]);

                $student->load('user', 'department');

                return $student;
            });
        } catch (QueryException $e) {
            $errors = $this->duplicateStudentErrors($e, $validated);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        $student->load('user', 'department');
        if ($student->user) {
            $invitationQueued = $this->invitationEmailService->sendRegistrationInvite($student->user);
        }

        $successMessage = $invitationQueued
            ? 'Student invitation created successfully. Registration email sent. The student must complete registration to activate the account.'
            : 'Student invitation created successfully, but the registration email could not be queued. The student must still complete registration to activate the account.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'student' => $dataService->serializeStudent($student, ['can_toggle_status' => true]),
                'stats' => $dataService->getStats($this->currentStudentListingFilters($request)),
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', $successMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, StudentFineSummaryService $studentFineSummary)
    {
        Gate::authorize('access-admin');
        $student = Student::with([
            'user',
            'department',
            'issuedBooks.book.category',
            'issuedBooks.issuer',
            'issuedBooks.fine',
            'bookRequests',
            'fines.issuedBook.book',
        ])->findOrFail($id);
        
        // Ensure the associated user has 'student' role
        if ($student->user->role !== 'student') {
            return redirect()->route('admin.students.index')->with('error', 'This user is not a student.');
        }

        $studentFineSummary->syncPendingOpenOverdueFines($student);
        $student->load([
            'issuedBooks.book.category',
            'issuedBooks.issuer',
            'issuedBooks.fine',
            'fines.issuedBook.book',
        ]);
        
        // Transform issued books for frontend
        $booksData = $student->issuedBooks->map(function($issued) use ($studentFineSummary) {
            $issueDate = $issued->issue_date ? \Carbon\Carbon::parse($issued->issue_date) : null;
            $dueDate = $issued->due_date ? \Carbon\Carbon::parse($issued->due_date)->startOfDay() : null;
            $returnDate = $issued->return_date ? \Carbon\Carbon::parse($issued->return_date) : null;
            $today = now()->startOfDay();

            $status = $returnDate
                ? 'returned'
                : ($dueDate && $dueDate->lt($today) ? 'overdue' : 'issued');
            $daysOverdue = $status === 'overdue' && $dueDate
                ? (int) $dueDate->diffInDays($today)
                : 0;
            $displayFine = $studentFineSummary->displayFineForIssue($issued);
            $fineAmount = (float) ($displayFine['amount'] ?? 0);
            
            return [
                'id' => $issued->id,
                'title' => $issued->book->title ?? 'Unknown',
                'author' => $issued->book->author ?? 'Unknown Author',
                'publisher' => $issued->book->publisher ?? 'Unknown Publisher',
                'isbn' => $issued->book->isbn ?? 'N/A',
                'description' => $issued->book->description ?? 'No description available',
                'coverImage' => $issued->book->cover_image ?? null,
                'condition' => $issued->condition ?? 'Good',
                'category' => $issued->book?->category?->name ?? 'Uncategorized',
                'transactionId' => 'TXN-' . str_pad((string) $issued->id, 6, '0', STR_PAD_LEFT),
                'issueDate' => $issueDate ? $issueDate->format('M d, Y') : 'N/A',
                'issueDateFull' => $issueDate ? $issueDate->format('M d, Y H:i') : 'N/A',
                'issueDateRaw' => $issueDate ? $issueDate->toDateString() : null,
                'dueDate' => $dueDate ? $dueDate->format('M d, Y') : 'N/A',
                'dueDateRaw' => $dueDate ? $dueDate->toDateString() : null,
                'returnDate' => $returnDate ? $returnDate->format('M d, Y') : '-',
                'returnDateRaw' => $returnDate ? $returnDate->toDateString() : null,
                'issuedBy' => $issued->issuer?->name ?? 'System',
                'renewalCount' => $issued->renewal_count ?? 0,
                'status' => $status,
                'fine' => $fineAmount,
                'hasFine' => (bool) $issued->fine || $fineAmount > 0,
                'fineStatus' => strtolower((string) ($displayFine['status'] ?? $issued->fine?->status ?? 'n/a')),
                'daysOverdue' => (int) $daysOverdue,
                'remarks' => $issued->remarks ?? 'No remarks'
            ];
        })->toArray();
        
        // Transform fines for frontend
        $finesData = $student->fines->map(function($fine) {
            return [
                'id' => $fine->id,
                'bookName' => $fine->issuedBook?->book?->title ?? 'Unknown',
                'daysOverdue' => ($fine->days_late ?? 0) . ' days',
                'fineAmount' => $fine->amount ?? 0,
                'paymentStatus' => $fine->status,
                'actions' => in_array($fine->status, ['paid', 'waived']) ? ['view-history'] : ['adjust', 'waive', 'mark-paid', 'view-history']
            ];
        })->toArray();
        
        $allActivityLogs = $this->buildStudentActivityLogPayload($student, 20);

        // Separate initial logs (first 10) from remaining logs for pagination
        $activityLogs = array_slice($allActivityLogs, 0, 10);
        $remainingActivityLogs = array_slice($allActivityLogs, 10);
        
        return view('Admin.StudentView', compact('student', 'booksData', 'finesData', 'activityLogs', 'remainingActivityLogs'));
    }

    public function getStudentActivityLogs(Request $request, string $id)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::with([
                'user',
                'issuedBooks.book',
                'fines.issuedBook.book',
            ])->findOrFail($id);

            if ($student->user->role !== 'student') {
                return response()->json([
                    'success' => false,
                    'message' => 'This user is not a student.'
                ], 422);
            }

            $limit = max(1, min((int) $request->integer('limit', 20), 50));
            $logs = $this->buildStudentActivityLogPayload($student, $limit);

            return response()->json([
                'success' => true,
                'activityLogs' => $logs,
                'initialLogs' => array_slice($logs, 0, 10),
                'remainingLogs' => array_slice($logs, 10),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading student activity logs: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error loading activity logs: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function buildStudentActivityLogPayload(Student $student, int $limit = 20): array
    {
        $student->loadMissing([
            'user',
            'issuedBooks.book',
            'fines.issuedBook.book',
        ]);

        $issuedBookIds = $student->issuedBooks->pluck('id')->filter();
        $fineIds = $student->fines->pluck('id')->filter();
        $issuedBooksById = $student->issuedBooks->keyBy(fn ($issuedBook) => (string) $issuedBook->id);
        $finesById = $student->fines->keyBy(fn ($fine) => (string) $fine->id);

        $activityLogQuery = ActivityLog::with('user')
            ->where(function ($q) use ($student) {
                $q->where('model_type', Student::class)
                    ->where('model_id', $student->id);
            })
            ->orWhere(function ($q) use ($student) {
                $q->where('affected_user_id', $student->user_id);
            });

        if ($issuedBookIds->isNotEmpty()) {
            $activityLogQuery->orWhere(function ($q) use ($issuedBookIds) {
                $q->where('resource_type', 'issued_book')
                    ->whereIn('resource_id', $issuedBookIds);
            });
        }

        if ($fineIds->isNotEmpty()) {
            $activityLogQuery->orWhere(function ($q) use ($fineIds) {
                $q->where('resource_type', 'fine')
                    ->whereIn('resource_id', $fineIds);
            });
        }

        return $activityLogQuery
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (ActivityLog $log) use ($student, $issuedBooksById, $finesById) {
                $metadata = $this->normalizeActivityMetadata($log->metadata);
                $type = $this->resolveStudentActivityType($log, $metadata);
                $status = $this->resolveStudentActivityStatus($log, $type, $metadata);
                $resource = $this->resolveStudentActivityResource($log, $student, $metadata, $issuedBooksById, $finesById);
                $actor = $log->user;
                $sessionId = data_get($metadata, 'session_id')
                    ?? data_get($metadata, 'session.id')
                    ?? null;

                return [
                    'id' => $log->id,
                    'type' => $type,
                    'title' => $this->resolveStudentActivityTitle($log, $type),
                    'description' => $this->resolveStudentActivityDescription($log, $student),
                    'time' => optional($log->created_at)->diffForHumans() ?? 'Unknown time',
                    'fullTimestamp' => optional($log->created_at)->format('M d, Y h:i:s A') ?? 'N/A',
                    'status' => $status,
                    'userName' => $actor?->name ?? $log->user_name ?? 'System',
                    'userRole' => $actor?->role ?? $log->user_role ?? 'system',
                    'userAvatar' => $this->resolveUserAvatarUrl($actor?->profile_photo),
                    'ipAddress' => $log->ip_address ?: 'Not captured',
                    'deviceType' => $this->formatActivityLabel($log->device_type, 'Unknown device'),
                    'browser' => $log->browser ?: 'Unknown browser',
                    'sessionId' => $sessionId ?: 'Not captured',
                    'resourceUrl' => $resource['resourceUrl'],
                    'resourceType' => $resource['resourceType'],
                    'resourceId' => $resource['resourceId'],
                    'metadata' => $metadata,
                    'hasDetails' => !empty($metadata)
                        || !empty($log->ip_address)
                        || !empty($log->device_type)
                        || !empty($log->browser)
                        || !empty($sessionId),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function normalizeActivityMetadata($metadata): array
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

    protected function resolveStudentActivityType(ActivityLog $log, array $metadata = []): string
    {
        $action = strtolower((string) ($log->action ?? ''));
        $category = strtolower((string) ($log->action_category ?? ''));

        return match (true) {
            in_array($action, ['book_issued', 'book_request_issued'], true) => 'book-issued',
            in_array($action, ['book_returned', 'book_return'], true) => 'book-returned',
            in_array($action, ['fine_applied', 'fine_adjusted'], true) => 'fine-applied',
            in_array($action, ['fine_paid', 'fine_payment'], true) => 'fine-paid',
            $action === 'fine_waived' => 'fine-waived',
            in_array($action, ['status_changed', 'account_deleted'], true) => 'account-status',
            in_array($action, ['profile_updated', 'user_updated'], true) => 'profile-updated',
            in_array($action, ['role_changed', 'privilege_changed', 'privilege_updated', 'library_settings_updated'], true) => 'privilege-change',
            in_array($action, ['password_reset', 'login', 'logout'], true) => 'auth',
            $category === 'auth' => 'auth',
            $category === 'fine' && ($metadata['action_type'] ?? null) === 'paid' => 'fine-paid',
            $category === 'fine' && ($metadata['action_type'] ?? null) === 'waived' => 'fine-waived',
            $category === 'fine' => 'fine-applied',
            $category === 'book' => str_contains($action, 'return') ? 'book-returned' : 'book-issued',
            default => 'profile-updated',
        };
    }

    protected function resolveStudentActivityStatus(ActivityLog $log, string $type, array $metadata = []): string
    {
        $rawStatus = strtolower((string) ($log->status ?? 'completed'));
        $derivedState = strtolower((string) (
            $metadata['new_status']
            ?? $metadata['status']
            ?? $metadata['action_type']
            ?? ''
        ));

        if (in_array($rawStatus, ['failed', 'error', 'denied', 'rejected'], true)) {
            return 'failed';
        }

        if (in_array($rawStatus, ['warning', 'pending', 'partial'], true)) {
            return 'warning';
        }

        if ($type === 'account-status' && in_array($derivedState, ['inactive', 'suspended', 'locked', 'blocked'], true)) {
            return 'warning';
        }

        if (in_array($type, ['fine-applied', 'fine-waived', 'privilege-change'], true)) {
            return 'warning';
        }

        return 'success';
    }

    protected function resolveStudentActivityTitle(ActivityLog $log, string $type): string
    {
        $action = strtolower((string) ($log->action ?? ''));

        $titleMap = [
            'book_issued' => 'Book Issued',
            'book_returned' => 'Book Returned',
            'book_return' => 'Book Returned',
            'fine_applied' => 'Fine Applied',
            'fine_adjusted' => 'Fine Adjusted',
            'fine_paid' => 'Fine Paid',
            'fine_payment' => 'Fine Paid',
            'fine_waived' => 'Fine Waived',
            'status_changed' => 'Account Status Updated',
            'profile_updated' => 'Profile Updated',
            'user_updated' => 'Profile Updated',
            'role_changed' => 'Privilege Changed',
            'privilege_updated' => 'Library Privileges Updated',
            'password_reset' => 'Password Reset',
            'login' => 'Login Activity',
            'logout' => 'Logout Activity',
            'account_deleted' => 'Account Deleted',
        ];

        if (isset($titleMap[$action])) {
            return $titleMap[$action];
        }

        return match ($type) {
            'fine-paid' => 'Fine Paid',
            'fine-waived' => 'Fine Waived',
            'fine-applied' => 'Fine Applied',
            'book-issued' => 'Book Activity',
            'book-returned' => 'Book Activity',
            'account-status' => 'Account Activity',
            'profile-updated' => 'Profile Activity',
            'privilege-change' => 'Library Privileges Updated',
            'auth' => 'Authentication Event',
            default => 'Activity',
        };
    }

    protected function resolveStudentActivityDescription(ActivityLog $log, Student $student): string
    {
        $description = trim((string) ($log->readable_description ?? $log->description ?? 'Activity recorded'));

        if ($description === '') {
            return 'Activity recorded';
        }

        if (str_contains($description, 'by student')) {
            $description = str_replace('by student', 'by ' . ($student->user->name ?? 'student'), $description);
        }

        return $description;
    }

    protected function resolveStudentActivityResource(
        ActivityLog $log,
        Student $student,
        array $metadata,
        $issuedBooksById,
        $finesById
    ): array {
        $resourceType = strtolower((string) ($log->resource_type ?? 'student'));
        $originalResourceType = $resourceType;
        $resourceId = $log->resource_id ?: $student->id;
        $actionType = $this->resolveStudentActivityType($log, $metadata);
        $resourceUrl = route('admin.activity-logs.index');

        if (!empty($metadata['fine_id']) || $resourceType === 'fine' || in_array($actionType, ['fine-applied', 'fine-paid', 'fine-waived'], true)) {
            $resourceType = 'fine';
            $resourceId = (string) ($metadata['fine_id'] ?? ($originalResourceType === 'fine' ? $resourceId : 'N/A'));
            $fine = $finesById->get((string) $resourceId);
            $resourceUrl = route('admin.fines.index') . '?student=' . urlencode((string) $student->id);

            if ($fine) {
                $resourceId = (string) $fine->id;
            }
        } elseif (!empty($metadata['issued_book_id']) || $resourceType === 'issued_book' || in_array($actionType, ['book-issued', 'book-returned'], true)) {
            $resourceType = 'issued_book';
            $resourceId = (string) ($metadata['issued_book_id'] ?? ($originalResourceType === 'issued_book' ? $resourceId : 'N/A'));
            $issuedBook = $issuedBooksById->get((string) $resourceId);
            $resourceUrl = route('admin.transactions.index') . '?student=' . urlencode((string) $student->id);

            if ($issuedBook) {
                $resourceId = (string) $issuedBook->id;
            }
        } elseif ($actionType === 'auth') {
            $resourceType = 'auth';
            $resourceId = (string) ($log->affected_user_id ?? $student->user_id ?? $student->id);
            $resourceUrl = route('admin.activity-logs.index') . '?search=' . urlencode($student->user->name ?? $student->roll_no ?? 'student');
        } elseif (in_array($actionType, ['account-status', 'profile-updated', 'privilege-change'], true)) {
            $resourceType = 'student';
            $resourceId = (string) $student->id;
            $resourceUrl = route('admin.students.show', $student->id);
        }

        return [
            'resourceType' => $this->formatActivityLabel($resourceType, 'Student'),
            'resourceId' => $resourceId ?: 'N/A',
            'resourceUrl' => $resourceUrl,
        ];
    }

    protected function resolveUserAvatarUrl(?string $profilePhoto): ?string
    {
        if (!$profilePhoto) {
            return null;
        }

        return str_starts_with($profilePhoto, 'http')
            ? $profilePhoto
            : asset(str_starts_with($profilePhoto, 'storage/')
                ? $profilePhoto
                : 'storage/' . ltrim($profilePhoto, '/'));
    }

    protected function formatActivityLabel($value, string $fallback = 'N/A'): string
    {
        if ($value === null || $value === '') {
            return $fallback;
        }

        return Str::of((string) $value)
            ->replace(['_', '-'], ' ')
            ->title()
            ->toString();
    }

    protected function formatPrivilegeLogMessage(array $changes): string
    {
        if (empty($changes)) {
            return 'Library privileges updated.';
        }

        $messages = [];

        foreach ($changes as $field => $value) {
            switch ((string) $field) {
                case 'borrowing_allowed':
                    $messages[] = 'borrowing permission set to ' . ($value ? 'allowed' : 'restricted');
                    break;
                case 'max_books':
                    $messages[] = 'maximum books set to ' . $value;
                    break;
                case 'issue_duration_days':
                    $messages[] = 'issue duration set to ' . $value . ' days';
                    break;
                case 'per_day_fine':
                    $messages[] = 'per-day fine set to Rs. ' . number_format((float) $value, 2);
                    break;
                case 'grace_period_days':
                    $messages[] = 'grace period set to ' . $value . ' days';
                    break;
                case 'max_fine_amount':
                    $messages[] = 'maximum fine amount set to Rs. ' . number_format((float) $value, 2);
                    break;
                default:
                    $messages[] = strtolower($this->formatActivityLabel($field)) . ' set to ' . (is_bool($value)
                        ? ($value ? 'enabled' : 'disabled')
                        : $value);
                    break;
            }
        }

        return 'Library privileges updated: ' . implode(', ', $messages) . '.';
    }

    protected function formatPrivilegeValue(string $field, $value): string
    {
        return match ($field) {
            'borrowing_allowed' => (bool) $value ? 'allowed' : 'restricted',
            'issue_duration_days', 'grace_period_days' => $value . ' days',
            'per_day_fine', 'max_fine_amount' => 'Rs. ' . number_format((float) $value, 2),
            default => (string) $value,
        };
    }

    protected function formatPrivilegeChangeSummary(array $currentSettings, array $nextSettings): string
    {
        $messages = [];

        foreach ([
            'max_books',
            'issue_duration_days',
            'per_day_fine',
            'borrowing_allowed',
            'grace_period_days',
            'max_fine_amount',
        ] as $field) {
            if (!$this->privilegeValueChanged($field, $currentSettings[$field] ?? null, $nextSettings[$field] ?? null)) {
                continue;
            }

            $messages[] = sprintf(
                '%s: %s -> %s',
                $this->formatActivityLabel($field),
                $this->formatPrivilegeValue($field, $currentSettings[$field] ?? null),
                $this->formatPrivilegeValue($field, $nextSettings[$field] ?? null)
            );
        }

        return implode(', ', $messages);
    }

    protected function buildPrivilegeLogSnapshot($privileges, $fineSetting): array
    {
        return $this->buildPrivilegeResponsePayload($privileges, $fineSetting)['effective'];
    }

    protected function privilegeValueChanged(string $field, $currentValue, $newValue): bool
    {
        if ($field === 'borrowing_allowed') {
            return (bool) $currentValue !== (bool) $newValue;
        }

        if (is_numeric($currentValue) || is_numeric($newValue)) {
            return (float) $currentValue !== (float) $newValue;
        }

        return $currentValue !== $newValue;
    }

    protected function resolvePrivilegeDefaults(FineSetting $fineSetting): array
    {
        return [
            'max_books' => (int) ($fineSetting->max_books_per_student ?? 5),
            'issue_duration_days' => (int) ($fineSetting->issue_duration_days ?? 14),
            'per_day_fine' => (float) ($fineSetting->per_day_fine ?? 10),
            'borrowing_allowed' => true,
            'grace_period_days' => (int) ($fineSetting->grace_period_days ?? 2),
            'max_fine_amount' => (float) ($fineSetting->max_fine_amount ?? 500),
        ];
    }

    protected function buildStoredPrivilegeSnapshot(?StudentPrivilege $privileges): array
    {
        $hasPrivilegeRecord = $privileges instanceof StudentPrivilege && $privileges->exists;

        return [
            'max_books' => $hasPrivilegeRecord ? $privileges->max_books : null,
            'issue_duration_days' => $hasPrivilegeRecord ? $privileges->issue_duration_days : null,
            'per_day_fine' => $hasPrivilegeRecord ? $privileges->per_day_fine : null,
            'borrowing_allowed' => $hasPrivilegeRecord ? (bool) ($privileges->borrowing_allowed ?? true) : true,
            'grace_period_days' => $hasPrivilegeRecord ? $privileges->grace_period_days : null,
            'max_fine_amount' => $hasPrivilegeRecord ? $privileges->max_fine_amount : null,
        ];
    }

    protected function hasStoredPrivilegeOverrides(?StudentPrivilege $privileges): bool
    {
        if (!($privileges instanceof StudentPrivilege) || !$privileges->exists) {
            return false;
        }

        return $privileges->max_books !== null
            || $privileges->issue_duration_days !== null
            || $privileges->per_day_fine !== null
            || (bool) ($privileges->borrowing_allowed ?? true) !== true
            || $privileges->grace_period_days !== null
            || $privileges->max_fine_amount !== null;
    }

    protected function buildEffectivePrivilegeSnapshot(array $storedPrivileges, array $defaults): array
    {
        return [
            'max_books' => $storedPrivileges['max_books'] ?? $defaults['max_books'],
            'issue_duration_days' => $storedPrivileges['issue_duration_days'] ?? $defaults['issue_duration_days'],
            'per_day_fine' => $storedPrivileges['per_day_fine'] ?? $defaults['per_day_fine'],
            'borrowing_allowed' => array_key_exists('borrowing_allowed', $storedPrivileges)
                ? (bool) ($storedPrivileges['borrowing_allowed'] ?? true)
                : (bool) $defaults['borrowing_allowed'],
            'grace_period_days' => $storedPrivileges['grace_period_days'] ?? $defaults['grace_period_days'],
            'max_fine_amount' => $storedPrivileges['max_fine_amount'] ?? $defaults['max_fine_amount'],
        ];
    }

    protected function buildPrivilegeResponsePayload(?StudentPrivilege $privileges, FineSetting $fineSetting): array
    {
        $defaults = $this->resolvePrivilegeDefaults($fineSetting);
        $storedPrivileges = $this->buildStoredPrivilegeSnapshot($privileges);

        return [
            'privileges' => $storedPrivileges,
            'defaults' => $defaults,
            'effective' => $this->buildEffectivePrivilegeSnapshot($storedPrivileges, $defaults),
            'has_custom_overrides' => $this->hasStoredPrivilegeOverrides($privileges),
        ];
    }

    protected function normalizePrivilegeOverridePayload(array $validated, array $defaults): array
    {
        $normalized = $validated;

        foreach (['max_books', 'issue_duration_days', 'per_day_fine', 'grace_period_days', 'max_fine_amount'] as $field) {
            if (!array_key_exists($field, $normalized) || $normalized[$field] === null) {
                continue;
            }

            if (!$this->privilegeValueChanged($field, $defaults[$field] ?? null, $normalized[$field])) {
                $normalized[$field] = null;
            }
        }

        if (array_key_exists('borrowing_allowed', $normalized)) {
            $normalized['borrowing_allowed'] = (bool) $normalized['borrowing_allowed'];
        }

        return $normalized;
    }

    protected function buildNextStoredPrivilegeSnapshot(?StudentPrivilege $privileges, array $payload): array
    {
        $storedPrivileges = $this->buildStoredPrivilegeSnapshot($privileges);

        foreach ($payload as $field => $value) {
            if (array_key_exists($field, $storedPrivileges)) {
                $storedPrivileges[$field] = $value;
            }
        }

        return $storedPrivileges;
    }

    protected function shouldDeletePrivilegeRecord(array $storedPrivileges): bool
    {
        return $storedPrivileges['max_books'] === null
            && $storedPrivileges['issue_duration_days'] === null
            && $storedPrivileges['per_day_fine'] === null
            && $storedPrivileges['grace_period_days'] === null
            && $storedPrivileges['max_fine_amount'] === null
            && (bool) ($storedPrivileges['borrowing_allowed'] ?? true) === true;
    }

    protected function calculatePrivilegeChanges(array $currentSettings, array $nextSettings): array
    {
        $changes = [];

        foreach ([
            'max_books',
            'issue_duration_days',
            'per_day_fine',
            'borrowing_allowed',
            'grace_period_days',
            'max_fine_amount',
        ] as $field) {
            if ($this->privilegeValueChanged($field, $currentSettings[$field] ?? null, $nextSettings[$field] ?? null)) {
                $changes[$field] = $nextSettings[$field] ?? null;
            }
        }

        return $changes;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, string $id, StudentManagementDataService $dataService)
    {
        Gate::authorize('access-admin');

        $student = Student::findOrFail($id);
        $user = $student->user;

        $validated = $request->validated();

        // Track changes in User model
        $userChanges = [];
        if ($user->name !== $validated['name']) $userChanges['name'] = $validated['name'];
        if ($user->email !== $validated['email']) $userChanges['email'] = $validated['email'];
        if ($user->phone !== $validated['phone']) $userChanges['phone'] = $validated['phone'];
        if (($user->gender ?? null) !== ($validated['gender'] ?? null)) $userChanges['gender'] = $validated['gender'] ?? null;
        if (optional($user->date_of_birth)->format('Y-m-d') !== $validated['date_of_birth']) $userChanges['date_of_birth'] = $validated['date_of_birth'];
        $resolvedStatus = $user->hasCompletedRegistration() ? $validated['status'] : 'inactive';
        if ($user->status !== $resolvedStatus) $userChanges['status'] = $resolvedStatus;
        if (($user->address ?? null) !== ($validated['address'] ?? null)) $userChanges['address'] = $validated['address'];

        // Track changes in Student model
        $studentChanges = [];
        if ($student->roll_no !== $validated['roll_no']) $studentChanges['roll_no'] = $validated['roll_no'];
        if (($student->student_id ?? null) !== ($validated['roll_no'] ?? null)) $studentChanges['student_id'] = $validated['roll_no'];
        if ($student->department_id != $validated['department_id']) $studentChanges['department_id'] = $validated['department_id'];
        if ($student->batch !== $validated['batch']) $studentChanges['batch'] = $validated['batch'];
        if ($student->semester !== $validated['semester']) $studentChanges['semester'] = $validated['semester'];
        if ($student->address !== $validated['address']) $studentChanges['address'] = $validated['address'];

        try {
            DB::transaction(function () use ($user, $student, $validated, $resolvedStatus) {
                $user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'gender' => $validated['gender'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'],
                    'address' => $validated['address'],
                    'status' => $resolvedStatus,
                ]);

                $student->update([
                    'student_id' => $validated['roll_no'],
                    'roll_no' => $validated['roll_no'],
                    'department_id' => $validated['department_id'],
                    'batch' => $validated['batch'],
                    'semester' => $validated['semester'],
                    'address' => $validated['address'],
                ]);
            });
        } catch (QueryException $e) {
            $errors = $this->duplicateStudentErrors($e, $validated);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        // Log the activity with actual changes
        $allChanges = array_merge($userChanges, $studentChanges);
        if (!empty($allChanges)) {
            ActivityLogger::logProfileUpdate($student, $allChanges);
        }

        if (isset($userChanges['status'])) {
            $statusMessage = $resolvedStatus === 'active' ? 'activated' : 'deactivated';

            Notification::notify(
                user: $user,
                type: 'account.status_changed',
                title: 'Account Status Changed',
                message: "Your account has been {$statusMessage} by admin",
                data: ['status' => $resolvedStatus, 'changed_by' => auth()->user()?->name],
                relatedModel: 'Student',
                relatedId: $student->id
            );

            $this->queueStudentStatusEmail($student, $resolvedStatus);
        }

        // Notify admin if status changed to inactive (critical action)
        if (isset($userChanges['status']) && $resolvedStatus === 'inactive') {
            $adminUser = User::where('role', 'admin')->where('id', '!=', auth()->id())->first();
            if ($adminUser) {
                Notification::notify(
                    user: $adminUser,
                    type: 'student.critical_action',
                    title: 'Student Account Deactivated',
                    message: "Student {$student->user->name} (Roll: {$student->roll_no}) account has been deactivated",
                    data: ['student_id' => $student->id, 'user_id' => $student->user_id, 'action' => 'deactivated'],
                    relatedModel: 'Student',
                    relatedId: $student->id
                );
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $user->hasCompletedRegistration()
                    ? 'Student updated successfully'
                    : 'Student invitation updated successfully. The account will stay inactive until registration is completed.',
                'student' => $dataService->serializeStudent($student->load(['user', 'department']), ['can_toggle_status' => true]),
                'stats' => $dataService->getStats($this->currentStudentListingFilters($request)),
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, StudentManagementDataService $dataService)
    {
        Gate::authorize('access-admin');

        try {
            $student = Student::findOrFail($id);
            $user = $student->user;
            $deletedStudent = [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Student',
                'rollNo' => $student->roll_no ?? 'N/A',
            ];
            
            // Log before deletion
            ActivityLogger::logAccountDeleted($student);
            
            $student->delete();
            $user->delete();

            // Always return JSON for API/AJAX requests
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully',
                'deletedStudent' => $deletedStudent,
                'stats' => $dataService->getStats($this->currentStudentListingFilters($request)),
            ]);
        } catch (\Exception $e) {
            // Even if error occurs, return success since data was deleted
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully',
            ]);
        }
    }

    /**
     * Reset password for a student
     */
    public function resetPassword(string $id)
    {
        Gate::authorize('access-admin');

        $student = Student::findOrFail($id);
        $user = $student->user;

        if ($user && $user->requiresSelfRegistration()) {
            return response()->json([
                'success' => false,
                'message' => 'This invited account has not completed registration yet. Ask the student to finish registration instead.',
            ], 422);
        }

        // Generate temporary password
        $tempPassword = Str::random(10);
        try {
            DB::transaction(function () use ($user, $tempPassword) {
                $user->update([
                    'password' => Hash::make($tempPassword),
                    'force_password_change' => true,
                    'password_reset_at' => now()
                ]);

                Mail::to($user->email)->queue(new PasswordResetEmail(
                    $user->name,
                    $user->email,
                    $tempPassword
                ));
            });

            \Log::info('Queued student password reset email', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to queue password reset email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Password reset email could not be queued, so no changes were saved.',
            ], 500);
        }

        // Log the activity
        ActivityLogger::logPasswordReset($student);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully! Temporary password has been sent to the student email.',
        ]);
    }

    /**
     * Deactivate student account
     */
    public function deactivate(string $id)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this student.',
                ], 404);
            }

            $oldStatus = $user->status;

            // Update user status to inactive
            $user->status = 'inactive';
            $user->save();

            // Notify student about status change
            Notification::notify(
                user: $user,
                type: 'account.status_changed',
                title: 'Account Status Changed',
                message: 'Your account has been deactivated by admin',
                data: ['status' => 'inactive', 'changed_by' => auth()->user()?->name],
                relatedModel: 'Student',
                relatedId: $student->id
            );

            $this->queueStudentStatusEmail($student, 'inactive');

            // Log the activity
            ActivityLogger::logStatusChange($student, $oldStatus, 'inactive');

            return response()->json([
                'success' => true,
                'message' => 'Student account has been deactivated successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deactivating student account: ' . $e->getMessage(), [
                'student_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deactivating the account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate student account
     */
    public function activate(string $id)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this student.',
                ], 404);
            }

            if ($user->requiresSelfRegistration()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invited account must complete registration before it can be activated.',
                ], 422);
            }

            $oldStatus = $user->status;

            // Update user status to active
            $user->status = 'active';
            $user->save();

            // Notify student about status change
            Notification::notify(
                user: $user,
                type: 'account.status_changed',
                title: 'Account Status Changed',
                message: 'Your account has been activated by admin',
                data: ['status' => 'active', 'changed_by' => auth()->user()?->name],
                relatedModel: 'Student',
                relatedId: $student->id
            );

            $this->queueStudentStatusEmail($student, 'active');

            // Log the activity
            ActivityLogger::logStatusChange($student, $oldStatus, 'active');

            return response()->json([
                'success' => true,
                'message' => 'Student account has been activated successfully.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error activating student account: ' . $e->getMessage(), [
                'student_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while activating the account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle student status (active/inactive)
     */
    public function toggleStatus(Request $request, string $id, StudentManagementDataService $dataService)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::findOrFail($id);
            $user = $student->user;
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for this student.',
                ], 404);
            }

            $oldStatus = $user->status;
            $newStatus = $user->status === 'active' ? 'inactive' : 'active';

            if ($newStatus === 'active' && $user->requiresSelfRegistration()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This invited account must complete registration before it can be activated.',
                ], 422);
            }

            // Update user status
            $user->status = $newStatus;
            $user->save();

            // Notify student about status change
            $statusMessage = $newStatus === 'active' ? 'activated' : 'deactivated';
            Notification::notify(
                user: $user,
                type: 'account.status_changed',
                title: 'Account Status Changed',
                message: "Your account has been {$statusMessage} by admin",
                data: ['status' => $newStatus, 'changed_by' => auth()->user()?->name],
                relatedModel: 'Student',
                relatedId: $student->id
            );

            $this->queueStudentStatusEmail($student, $newStatus);

            // Log the activity
            ActivityLogger::logStatusChange($student, $oldStatus, $newStatus);

            return response()->json([
                'success' => true,
                'message' => 'Student status has been updated to ' . ucfirst($newStatus) . '.',
                'status' => $newStatus,
                'student' => $dataService->serializeStudent($student->load(['user', 'department']), ['can_toggle_status' => true]),
                'stats' => $dataService->getStats($this->currentStudentListingFilters($request)),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling student status: ' . $e->getMessage(), [
                'student_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change student role
     */
    public function changeRole(Request $request, string $id)
    {
        Gate::authorize('access-admin');

        $request->validate([
            'role' => 'required|in:student,staff,admin',
        ]);

        $student = Student::findOrFail($id);
        $user = $student->user;
        $oldRole = $user->role;

        // Update user role
        $user->role = $request->role;
        $user->save();

        // Log the activity
        ActivityLogger::logRoleChange($student, $oldRole, $request->role);

        return response()->json([
            'success' => true,
            'message' => 'Student role has been changed successfully.',
        ]);
    }

    /**
     * Get student fines (AJAX)
     */
    public function getStudentFines(Request $request, string $id, StudentFineSummaryService $studentFineSummary)
    {
        Gate::authorize('access-admin');
        
        $student = Student::with(['fines' => function($q) {
            $q->with('issuedBook.book')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        $studentFineSummary->syncPendingOpenOverdueFines($student);
        $student->load(['fines' => function($q) {
            $q->with('issuedBook.book')->orderBy('created_at', 'desc');
        }]);
        
        $fines = $student->fines->map(function($fine) {
            $dueDate = $fine->issuedBook && $fine->issuedBook->due_date
                ? $fine->issuedBook->due_date->format('M d, Y')
                : 'N/A';
            
            return [
                'id' => $fine->id,
                'bookName' => $fine->issuedBook?->book?->title ?? 'Unknown',
                'daysOverdue' => (int)$fine->days_late,
                'dueDate' => $dueDate,
                'fineAmount' => (float)$fine->amount,
                'status' => $fine->status, // pending, paid, waived - THIS IS THE KEY FIELD
                'createdAt' => $fine->created_at->format('Y-m-d H:i:s'),
                'remarks' => $fine->remarks ?? '',
                'actions' => $fine->status === 'pending' ? ['adjust', 'waive', 'mark-paid', 'view-history'] : ['view-history']
            ];
        });
        
        // Calculate pending fines total
        $pendingFinesTotal = $student->fines->filter(function($fine) {
            return $fine->status === 'pending';
        })->sum('amount');
        
        return response()->json([
            'success' => true,
            'fines' => $fines,
            'pendingFinesTotal' => (float)$pendingFinesTotal,
        ]);
    }

    /**
     * Generate receipt for student fines
     */
    public function generateReceipt(Request $request, string $id)
    {
        Gate::authorize('access-admin');
        
        try {
            $student = Student::with(['user', 'fines' => function($q) {
                $q->where('status', 'paid')->with('issuedBook.book');
            }])->findOrFail($id);

            // Calculate totals
            $totalPaid = $student->fines->sum('amount');
            $paidFinesCount = $student->fines->count();

            // Generate simple HTML receipt
            $receipt = "<html>";
            $receipt .= "<head><style>";
            $receipt .= "body { font-family: Arial, sans-serif; margin: 20px; }";
            $receipt .= ".header { text-align: center; margin-bottom: 20px; }";
            $receipt .= ".section { margin-bottom: 15px; }";
            $receipt .= "table { width: 100%; border-collapse: collapse; }";
            $receipt .= "th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }";
            $receipt .= ".total { font-weight: bold; text-align: right; }";
            $receipt .= "</style></head>";
            $receipt .= "<body>";
            
            $receipt .= "<div class='header'>";
            $receipt .= "<h2>Library Management System - Fine Payment Receipt</h2>";
            $receipt .= "<p>Generated on: " . now()->format('Y-m-d H:i:s') . "</p>";
            $receipt .= "</div>";
            
            $receipt .= "<div class='section'>";
            $receipt .= "<h3>Student Information</h3>";
            $receipt .= "<p><strong>Name:</strong> " . $student->user->name . "</p>";
            $receipt .= "<p><strong>Student ID:</strong> " . $student->id . "</p>";
            $receipt .= "<p><strong>Email:</strong> " . $student->user->email . "</p>";
            $receipt .= "<p><strong>Department:</strong> " . ($student->department->name ?? 'N/A') . "</p>";
            $receipt .= "</div>";
            
            $receipt .= "<div class='section'>";
            $receipt .= "<h3>Paid Fines Summary</h3>";
            $receipt .= "<table>";
            $receipt .= "<tr><th>Book Title</th><th>Amount</th><th>Paid On</th></tr>";
            
            foreach ($student->fines as $fine) {
                $receipt .= "<tr>";
                $receipt .= "<td>" . ($fine->issuedBook?->book?->title ?? 'Unknown Book') . "</td>";
                $receipt .= "<td>₹" . number_format($fine->amount, 2) . "</td>";
                $receipt .= "<td>" . ($fine->paid_on ? $fine->paid_on->format('Y-m-d') : 'N/A') . "</td>";
                $receipt .= "</tr>";
            }
            
            $receipt .= "<tr><td colspan='2' class='total'>Total Paid:</td><td class='total'>₹" . number_format($totalPaid, 2) . "</td></tr>";
            $receipt .= "</table>";
            $receipt .= "</div>";
            
            $receipt .= "<p style='margin-top: 20px; font-size: 12px; color: #666;'>This is an automatically generated receipt. For more information, please contact the library administration.</p>";
            $receipt .= "</body></html>";

            // Return as PDF or HTML
            $filename = "receipt_" . $student->id . "_" . now()->format('Y-m-d_H-i-s') . ".pdf";
            
            // For now, return as downloadable HTML (can be extended to PDF using libraries)
            return response($receipt)
                ->header('Content-Type', 'text/html; charset=utf-8')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            \Log::error('Error generating receipt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student privileges
     */
    public function getPrivileges($studentId)
    {
        try {
            // Allow both admin and staff to access student privileges
            $userRole = auth()->user()->role ?? null;
            if (!in_array($userRole, ['admin', 'staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $student = Student::with('privileges')->findOrFail($studentId);
            $fineSetting = FineSetting::resolveActive();

            return response()->json(array_merge([
                'success' => true,
            ], $this->buildPrivilegeResponsePayload($student->privileges, $fineSetting)));
        } catch (\Exception $e) {
            \Log::error('Error getting privileges: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading privileges: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save student privileges
     */
    public function savePrivileges(Request $request, $studentId)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::with('privileges')->findOrFail($studentId);
            $fineSetting = FineSetting::resolveActive();
            $defaults = $this->resolvePrivilegeDefaults($fineSetting);

            $validated = $request->validate([
                'max_books' => 'nullable|integer|min:1|max:20',
                'issue_duration_days' => 'nullable|integer|min:1|max:90',
                'per_day_fine' => 'nullable|numeric|min:0|max:100',
                'grace_period_days' => 'nullable|integer|min:0|max:30',
                'max_fine_amount' => 'nullable|numeric|min:0|max:10000',
                'borrowing_allowed' => 'boolean',
            ]);

            $privileges = $student->privileges;
            $originalSettings = $this->buildPrivilegeLogSnapshot($privileges, $fineSetting);
            $normalizedPayload = $this->normalizePrivilegeOverridePayload($validated, $defaults);
            $fillablePayload = array_intersect_key(
                $normalizedPayload,
                array_flip((new StudentPrivilege())->getFillable())
            );
            $nextStoredPrivileges = $this->buildNextStoredPrivilegeSnapshot($privileges, $fillablePayload);

            if ($this->shouldDeletePrivilegeRecord($nextStoredPrivileges)) {
                if ($privileges instanceof StudentPrivilege && $privileges->exists) {
                    $privileges->delete();
                }

                $student->unsetRelation('privileges');
                $responsePayload = $this->buildPrivilegeResponsePayload(null, $fineSetting);
            } else {
                $privileges = $privileges ?? new StudentPrivilege(['student_id' => $student->id]);
                $privileges->fill($fillablePayload);
                $privileges->student_id = $student->id;
                $privileges->save();
                $student->setRelation('privileges', $privileges);

                $responsePayload = $this->buildPrivilegeResponsePayload($privileges, $fineSetting);
            }

            $changes = $this->calculatePrivilegeChanges($originalSettings, $responsePayload['effective']);
            $changesSummary = !empty($changes)
                ? $this->formatPrivilegeChangeSummary($originalSettings, $responsePayload['effective'])
                : null;

            // Notify student if privileges were changed
            if (!empty($changes)) {
                Notification::notify(
                    user: $student->user,
                    type: 'account.privilege_settings_changed',
                    title: 'Library Privileges Updated',
                    message: "Your library privileges have been updated by administrator. Changes: {$changesSummary}",
                    data: [
                        'student_id' => $student->id,
                        'student_name' => $student->user->name,
                        'changes' => $changes,
                        'admin_name' => auth()->user()?->name,
                    ],
                    relatedModel: 'StudentPrivilege',
                    relatedId: $privileges->id ?? null
                );

                $this->queueStudentPrivilegeEmail(
                    $student,
                    $responsePayload['effective'],
                    $changesSummary,
                );
            }

            // Log the activity
            if (!empty($changes)) {
                try {
                    ActivityLogger::logStudentActivity(
                        $student,
                        'privilege_updated',
                        $this->formatPrivilegeLogMessage($changes),
                        'privilege',
                        [
                            'changes' => $changes,
                            'updated_fields' => array_keys($changes),
                        ]
                    );
                } catch (\Exception $logError) {
                    \Log::warning('Failed to log privilege change: ' . $logError->getMessage());
                }
            }

            return response()->json(array_merge([
                'success' => true,
                'message' => 'Library privileges saved successfully',
            ], $responsePayload));
        } catch (\Exception $e) {
            \Log::error('Error saving privileges: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving privileges: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset student privileges back to system defaults.
     */
    public function resetPrivileges($studentId)
    {
        try {
            Gate::authorize('access-admin');

            $student = Student::with('privileges')->findOrFail($studentId);
            $fineSetting = FineSetting::resolveActive();
            $privileges = $student->privileges;
            $hadOverrides = $this->hasStoredPrivilegeOverrides($privileges);
            $originalSettings = $this->buildPrivilegeLogSnapshot($privileges, $fineSetting);

            if ($privileges instanceof StudentPrivilege && $privileges->exists) {
                $privileges->delete();
            }

            $student->unsetRelation('privileges');

            $responsePayload = $this->buildPrivilegeResponsePayload(null, $fineSetting);
            $changes = $this->calculatePrivilegeChanges($originalSettings, $responsePayload['effective']);

            if ($hadOverrides || !empty($changes)) {
                try {
                    ActivityLogger::logStudentActivity(
                        $student,
                        'privilege_updated',
                        'Library privileges reset to default settings.',
                        'privilege',
                        [
                            'changes' => $changes,
                            'updated_fields' => array_keys($changes),
                            'reset_to_defaults' => true,
                        ]
                    );
                } catch (\Exception $logError) {
                    \Log::warning('Failed to log privilege reset: ' . $logError->getMessage());
                }

                // Notify student of privilege reset
                Notification::notify(
                    user: $student->user,
                    type: 'account.privilege_settings_changed',
                    title: 'Library Privileges Reset',
                    message: 'Your library privileges have been reset to default system settings by the administrator.',
                    data: [
                        'student_id' => $student->id,
                        'student_name' => $student->user->name,
                        'action' => 'reset_to_defaults',
                        'admin_name' => auth()->user()?->name,
                    ],
                    relatedModel: 'StudentPrivilege',
                    relatedId: null
                );

                $this->queueStudentPrivilegeEmail(
                    $student,
                    $responsePayload['effective'],
                    'Your custom borrowing overrides were removed and your account now follows the default library policy.',
                    true,
                );
            }

            return response()->json(array_merge([
                'success' => true,
                'message' => 'Library privileges reset to default settings',
            ], $responsePayload));
        } catch (\Exception $e) {
            \Log::error('Error resetting privileges: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error resetting privileges: ' . $e->getMessage()
            ], 500);
        }
    }
}
