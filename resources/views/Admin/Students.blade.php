@extends('Admin.layouts.app')

@section('title', 'Students')

@push('styles')
    <style>
        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.4rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 500;
            gap: 0.15rem;
        }

        /* Student info styling */
        .student-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .student-name {
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.4;
        }

        .student-id {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.3rem;
        }

        .action-btn {
            padding: 0.3rem 0.6rem;
            border-radius: 0.3rem;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.2rem;
            white-space: nowrap;
            text-decoration: none;
        }

        .btn-view {
            background-color: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background-color: #2563eb;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
        }

        /* Table column widths */
        .students-table th:nth-child(1),
        .students-table td:nth-child(1) {
            min-width: 200px;
            width: 20%;
        }

        .students-table th:nth-child(2),
        .students-table td:nth-child(2) {
            min-width: 200px;
            width: 22%;
        }

        .students-table th:nth-child(3),
        .students-table td:nth-child(3) {
            min-width: 150px;
            width: 15%;
        }

        .students-table th:nth-child(4),
        .students-table td:nth-child(4) {
            min-width: 100px;
            width: 12%;
        }

        .students-table th:nth-child(5),
        .students-table td:nth-child(5) {
            min-width: 110px;
            width: 12%;
        }

        .students-table th:nth-child(6),
        .students-table td:nth-child(6) {
            min-width: 150px;
            width: 19%;
        }

        /* Table row hover effect */
        .students-table tbody tr {
            transition: background-color 0.2s ease;
        }

        body.light-theme .students-table tbody tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .students-table tbody tr:hover {
            background-color: #1e293b;
        }

        /* Add Student Modal Theme Support */
        #addStudentModal {
            backdrop-filter: blur(4px);
        }

        #addStudentModal>div {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.light-theme #addStudentModal>div {
            background-color: #ffffff;
            color: #1f2937;
        }

        body.dark-theme #addStudentModal>div {
            background-color: #1e293b;
            color: #e5e7eb;
        }

        #addStudentModal h2 {
            transition: color 0.3s ease;
        }

        body.light-theme #addStudentModal h2 {
            color: #1f2937;
        }

        body.dark-theme #addStudentModal h2 {
            color: #e5e7eb;
        }

        #addStudentModal label {
            transition: color 0.3s ease;
        }

        body.light-theme #addStudentModal label {
            color: #374151;
        }

        body.dark-theme #addStudentModal label {
            color: #d1d5db;
        }

        #addStudentModal input,
        #addStudentModal textarea,
        #addStudentModal select {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.light-theme #addStudentModal input,
        body.light-theme #addStudentModal textarea,
        body.light-theme #addStudentModal select {
            background-color: #ffffff;
            color: #1f2937;
            border-color: #d1d5db;
        }

        body.dark-theme #addStudentModal input,
        body.dark-theme #addStudentModal textarea,
        body.dark-theme #addStudentModal select {
            background-color: #0f172a;
            color: #e5e7eb;
            border-color: #334155;
        }

        body.light-theme #addStudentModal input:focus,
        body.light-theme #addStudentModal textarea:focus,
        body.light-theme #addStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme #addStudentModal input:focus,
        body.dark-theme #addStudentModal textarea:focus,
        body.dark-theme #addStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        #addStudentModal button[type="button"],
        #addStudentModal button[type="submit"] {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.light-theme #addStudentModal button[type="button"] {
            background-color: white;
            color: #374151;
            border-color: #d1d5db;
        }

        body.light-theme #addStudentModal button[type="button"]:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme #addStudentModal button[type="button"] {
            background-color: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-theme #addStudentModal button[type="button"]:hover {
            background-color: #4b5563;
        }

        #addStudentModal button[onclick*="closeAddStudentModal"] {
            transition: color 0.3s ease;
        }

        body.light-theme #addStudentModal button[onclick*="closeAddStudentModal"] {
            color: #6b7280;
        }

        body.dark-theme #addStudentModal button[onclick*="closeAddStudentModal"] {
            color: #9ca3af;
        }

        #addStudentModal .error-message {
            transition: color 0.3s ease;
            display: none;
            color: #dc2626;
            font-size: 12px;
            font-weight: 500;
        }

        body.light-theme #addStudentModal .error-message {
            color: #dc2626;
        }

        body.dark-theme #addStudentModal .error-message {
            color: #fca5a5;
        }

        #addStudentModal .required-asterisk {
            color: #ef4444;
            font-weight: 600;
        }

        /* ---------------------------------------- */

        /* Keyboard navigation styling */
        .student-row:focus {
            outline: 2px solid #3b82f6;
            outline-offset: -2px;
        }

        .student-row.selected {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        body.dark-theme .student-row.selected {
            background-color: rgba(59, 130, 246, 0.2) !important;
        }

        /* Focus styles for interactive elements */
        .action-btn:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        #searchInput:focus,
        .filter-select:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        #addStudentBtn:focus {
            outline: 2px solid white;
            outline-offset: 2px;
        }

        /* ===== NEW SEARCH & FILTER CONTAINER STYLES ===== */
        .search-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            align-items: center;
            background: white;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        body.dark-theme .search-filter-container {
            background: #1f2937;
            border-color: #374151;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
            max-width: 250px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.25rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            font-size: 0.75rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
            color: #0f172a;
        }
        body.dark-theme .search-input {
            background-color: #374151;
            border-color: #4b5563;
            color: #f1f5f9;
        }
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
        }
        body.dark-theme .search-icon {
            color: #9ca3af;
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
            appearance: none;
            min-width: 120px;
            transition: all 0.3s ease;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }
        body.dark-theme .filter-select {
            background: #374151 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border-color: #4b5563;
            color: #f1f5f9;
        }
        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .reset-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            background: #f8fafc;
            color: #0f172a;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        body.dark-theme .reset-btn {
            background: #374151;
            border-color: #4b5563;
            color: #f1f5f9;
        }
        .reset-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }
        body.dark-theme .reset-btn:hover {
            border-color: #3b82f6;
            color: #93c5fd;
        }
    </style>
@endpush

@section('content')
    <div class="">
        <!-- Header -->
        <div class="mb-3">
            <h1 class="text-xl font-bold text-primary">Students</h1>
            <p class="mt-0.5 text-xs text-secondary">Manage student records</p>
        <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-3">
            <div class="p-3 card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Total Students</p>
                        <h3 class="mt-1 text-2xl font-bold text-primary" id="totalCount">0</h3>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <i data-lucide="users" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>

            <div class="p-3 card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Active</p>
                        <h3 class="mt-1 text-2xl font-bold text-primary" id="activeCount">0</h3>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg dark:bg-green-900">
                        <i data-lucide="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>

            <div class="p-3 card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Inactive</p>
                        <h3 class="mt-1 text-2xl font-bold text-primary" id="inactiveCount">0</h3>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-lg dark:bg-red-900">
                        <i data-lucide="x-circle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Students Section -->
        <div class="mb-2">
            <h2 class="text-base font-semibold text-primary">All Students</h2>
        </div>

        <!-- Search & Filter Container -->
        <div class="search-filter-container">
            <div class="search-box">
                <div class="search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" class="search-input" id="searchInput" placeholder="Search by name, email, or roll number..." autocomplete="off">
            </div>

            <div class="filters-container">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <select class="filter-select" id="departmentFilter">
                    <option value="all">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>

                <button id="resetFiltersBtn" class="reset-btn" title="Reset all filters">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 4px; vertical-align: -2px;">
                        <polyline points="1 4 1 10 7 10"></polyline>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                    </svg>
                    Reset
                </button>
            </div>

            <button id="addStudentBtn" class="flex items-center gap-2 px-3 py-1 font-medium text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg" style="margin-left: auto;">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Student
            </button>
        </div>

        <!-- Students Table -->
        <div class="p-0 overflow-hidden card">
            <div class="overflow-x-auto">
                <table class="w-full students-table">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Student</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Email</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Department</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Batch</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Status</th>
                            <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                <!-- Pagination links will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div
            style="border-radius: 8px; padding: 16px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Add New Student</h2>
                <button onclick="closeAddStudentModal()"
                    style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
            </div>

            <form action="{{ route('admin.students.store') }}" method="POST" id="addStudentForm">
                @csrf

                <div style="margin-bottom: 12px;">
                    <label for="modal_name" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Full Name <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="modal_name" name="name" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter student's full name">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="modal_email" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Email <span
                            class="required-asterisk">*</span></label>
                    <input type="email" id="modal_email" name="email" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="student@example.com">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="modal_phone" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Phone <span
                            class="required-asterisk">*</span></label>
                    <input type="tel" id="modal_phone" name="phone" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter phone number">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="modal_roll_no" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;\">Roll Number
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="modal_roll_no" name="roll_no" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter roll number">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="modal_department_id"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;\">Department <span
                            class="required-asterisk">*</span></label>
                    <select id="modal_department_id" name="department_id" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select a department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="modal_batch" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Batch <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="modal_batch" name="batch" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 2024">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="modal_semester" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Semester
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="modal_semester" name="semester" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 1">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="modal_address"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Address</label>
                    <textarea id="modal_address" name="address"
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box; min-height: 80px;"
                        placeholder="Enter student's address"></textarea>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closeAddStudentModal()"
                        style="padding: 8px 16px; border: 1px solid; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding: 8px 16px; border: none; border-radius: 6px; background: #3b82f6; color: white; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        class StudentManager {
            constructor() {
                this.currentDepartment = 'all';
                this.currentStatus = 'all';
                this.searchDebounceTimer = null;
                this.selectedRowIndex = -1;
                this.currentPage = 1;
                this.totalRows = 0;
                this.rowsPerPage = 10;
                this.init();
            }

            init() {
                // Search and filter
                const searchInput = document.getElementById('searchInput');
                const departmentFilter = document.getElementById('departmentFilter');
                const statusFilter = document.getElementById('statusFilter');
                const resetBtn = document.getElementById('resetFiltersBtn');

                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        clearTimeout(this.searchDebounceTimer);
                        this.searchDebounceTimer = setTimeout(() => {
                            this.selectedRowIndex = -1;
                            this.fetchStudents();
                        }, 300);
                    });
                }

                if (departmentFilter) {
                    departmentFilter.addEventListener('change', (e) => {
                        this.currentDepartment = e.target.value;
                        this.selectedRowIndex = -1;
                        this.fetchStudents();
                    });
                }

                if (statusFilter) {
                    statusFilter.addEventListener('change', (e) => {
                        this.currentStatus = e.target.value;
                        this.selectedRowIndex = -1;
                        this.fetchStudents();
                    });
                }

                if (resetBtn) {
                    resetBtn.addEventListener('click', () => {
                        this.resetFilters();
                    });
                }

                // Add student button
                const addBtn = document.getElementById('addStudentBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => {
                        openAddStudentModal();
                    });
                }

                // Keyboard navigation
                document.addEventListener('keydown', (e) => this.handleKeyboardNavigation(e));

                // Setup keyboard shortcuts
                this.setupKeyboardShortcuts();

                // Load initial data
                this.fetchStudents();
                this.refreshStats();
            }

            setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // Ctrl+K or Cmd+K to focus search
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        document.getElementById('searchInput').focus();
                    }
                    // Escape in search input to clear it
                    else if (e.key === 'Escape' && document.activeElement.id === 'searchInput') {
                        if (document.getElementById('searchInput').value !== '') {
                            document.getElementById('searchInput').value = '';
                            this.fetchStudents();
                        } else {
                            document.getElementById('searchInput').blur();
                        }
                    }
                });
            }

            resetFilters() {
                document.getElementById('searchInput').value = '';
                document.getElementById('statusFilter').value = 'all';
                document.getElementById('departmentFilter').value = 'all';
                this.currentStatus = 'all';
                this.currentDepartment = 'all';
                this.fetchStudents();
            }

            fetchStudents(page = 1) {
                this.currentPage = page;
                const searchTerm = document.getElementById('searchInput')?.value || '';
                const department = this.currentDepartment;
                const status = this.currentStatus;

                fetch(`{{ route('admin.students.data') }}?search=${encodeURIComponent(searchTerm)}&department=${department}&status=${status}&page=${page}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.total === 0) {
                                document.getElementById('studentsTableBody').innerHTML = `
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block;"></i>
                                <p style="font-size: 16px; margin: 0; font-weight: 500;">No students found</p>
                                <p style="font-size: 14px; margin-top: 8px; color: #9ca3af;">Try adjusting your search or filters</p>
                            </td>
                        </tr>
                    `;
                                document.getElementById('paginationContainer').innerHTML = '';
                                this.totalRows = 0;
                            } else {
                                document.getElementById('studentsTableBody').innerHTML = data.tableRows;
                                document.getElementById('paginationContainer').innerHTML = data.pagination;
                                this.totalRows = data.total;
                                this.attachEventListeners();
                                this.updateRowAccessibility();
                            }
                        } else {
                            console.error('Error loading students');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching students:', error);
                    });
            }

            attachEventListeners() {
                // Re-initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Attach pagination click handlers
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        this.selectedRowIndex = -1;
                        this.fetchStudents(page);
                    });
                });

                // Add click handlers to table rows
                const rows = document.querySelectorAll('#studentsTableBody tr');
                rows.forEach((row, index) => {
                    row.setAttribute('tabindex', '0');
                    row.classList.add('student-row');
                    row.addEventListener('click', (e) => {
                        if (e.target.closest('.action-btn'))
                            return; // Don't select if clicking action buttons
                        this.selectRow(index);
                        row.focus();
                    });

                    // Allow Enter/Space to trigger view action
                    row.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.selectRow(index);
                            const viewBtn = row.querySelector('.btn-view');
                            if (viewBtn) viewBtn.click();
                        }
                    });
                });
            }

            updateRowAccessibility() {
                const rows = document.querySelectorAll('#studentsTableBody tr');
                rows.forEach((row, index) => {
                    row.setAttribute('role', 'row');
                    row.setAttribute('aria-label', `Student row ${index + 1}`);
                    row.setAttribute('tabindex', '0');
                });
            }

            selectRow(index) {
                // Remove selection from all rows
                document.querySelectorAll('#studentsTableBody tr').forEach(row => {
                    row.classList.remove('selected');
                    row.removeAttribute('aria-selected');
                });

                // Select new row
                const rows = document.querySelectorAll('#studentsTableBody tr');
                if (rows[index]) {
                    rows[index].classList.add('selected');
                    rows[index].setAttribute('aria-selected', 'true');
                    this.selectedRowIndex = index;
                }
            }

            handleKeyboardNavigation(e) {
                const rows = document.querySelectorAll('#studentsTableBody tr');
                if (rows.length === 0) return;

                // Don't interfere with form inputs
                if (e.target.tagName === 'INPUT' ||
                    e.target.tagName === 'SELECT' ||
                    e.target.tagName === 'TEXTAREA') {
                    return;
                }

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (this.selectedRowIndex < rows.length - 1) {
                            this.selectedRowIndex++;
                            this.selectRow(this.selectedRowIndex);
                            rows[this.selectedRowIndex].focus();
                        }
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        if (this.selectedRowIndex > 0) {
                            this.selectedRowIndex--;
                            this.selectRow(this.selectedRowIndex);
                            rows[this.selectedRowIndex].focus();
                        } else if (this.selectedRowIndex === -1 && rows.length > 0) {
                            this.selectedRowIndex = 0;
                            this.selectRow(this.selectedRowIndex);
                            rows[this.selectedRowIndex].focus();
                        }
                        break;

                    case 'Home':
                        e.preventDefault();
                        this.selectedRowIndex = 0;
                        this.selectRow(this.selectedRowIndex);
                        rows[this.selectedRowIndex].focus();
                        break;

                    case 'End':
                        e.preventDefault();
                        this.selectedRowIndex = rows.length - 1;
                        this.selectRow(this.selectedRowIndex);
                        rows[this.selectedRowIndex].focus();
                        break;

                    case 'Escape':
                        this.selectedRowIndex = -1;
                        document.querySelectorAll('#studentsTableBody tr').forEach(row => {
                            row.classList.remove('selected');
                            row.removeAttribute('aria-selected');
                        });
                        break;

                    case '/':
                        if (!e.ctrlKey && !e.metaKey) {
                            e.preventDefault();
                            const searchInput = document.getElementById('searchInput');
                            if (searchInput) {
                                searchInput.focus();
                                searchInput.select();
                            }
                        }
                        break;

                    case 'n':
                    case 'N':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            openAddStudentModal();
                        }
                        break;

                    case 'Delete':
                        if (this.selectedRowIndex >= 0) {
                            const row = rows[this.selectedRowIndex];
                            const studentId = row.dataset.studentId;
                            if (studentId && confirm('Delete this student?')) {
                                deleteStudent(studentId);
                            }
                        }
                        break;

                    case 'Enter':
                        if (this.selectedRowIndex >= 0 && !e.shiftKey) {
                            const row = rows[this.selectedRowIndex];
                            const viewBtn = row.querySelector('.btn-view');
                            if (viewBtn) {
                                e.preventDefault();
                                viewBtn.click();
                            }
                        }
                        break;
                }
            }

            refreshStats() {
                fetch(`{{ route('admin.students.stats') }}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('totalCount').textContent = data.totalStudents;
                        document.getElementById('activeCount').textContent = data.activeStudents;
                        document.getElementById('inactiveCount').textContent = data.inactiveStudents;
                    })
                    .catch(error => console.error('Error fetching stats:', error));
            }
        }

        // Make deleteStudent globally available
        window.deleteStudent = function(studentId) {
            if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) return;

            fetch(`{{ url('admin/students') }}/${studentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Reset selection after deletion
                        manager.selectedRowIndex = -1;
                        manager.fetchStudents();
                        manager.refreshStats();
                    } else {
                        alert(data.message || 'Error deleting student');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting student');
                });
        };

        // Modal functions for Add Student
        function openAddStudentModal() {
            const modal = document.getElementById('addStudentModal');
            if (modal) {
                modal.style.display = 'flex';
                // Clear any previous errors
                document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            }
        }

        function closeAddStudentModal() {
            const modal = document.getElementById('addStudentModal');
            if (modal) {
                modal.style.display = 'none';
            }
            // Reset form
            const form = document.getElementById('addStudentForm');
            if (form) {
                form.reset();
                // Clear error messages
                document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            }
        }

        function validateAddStudentForm() {
            let isValid = true;
            const errors = {};

            // Name validation
            const name = document.getElementById('modal_name').value.trim();
            if (!name) {
                errors['name'] = 'Full name is required';
                isValid = false;
            }

            // Email validation
            const email = document.getElementById('modal_email').value.trim();
            if (!email) {
                errors['email'] = 'Email is required';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors['email'] = 'Please enter a valid email address';
                isValid = false;
            }

            // Phone validation
            const phone = document.getElementById('modal_phone').value.trim();
            if (!phone) {
                errors['phone'] = 'Phone number is required';
                isValid = false;
            } else if (!/^[\d]{7,15}$/.test(phone.replace(/[^\d]/g, ''))) {
                errors['phone'] = 'Phone number must be 7-15 digits';
                isValid = false;
            }

            // Roll Number validation
            const rollNo = document.getElementById('modal_roll_no').value.trim();
            if (!rollNo) {
                errors['roll_no'] = 'Roll number is required';
                isValid = false;
            }

            // Department validation
            const departmentId = document.getElementById('modal_department_id').value;
            if (!departmentId) {
                errors['department_id'] = 'Please select a department';
                isValid = false;
            }

            // Batch validation
            const batch = document.getElementById('modal_batch').value.trim();
            if (!batch) {
                errors['batch'] = 'Batch is required';
                isValid = false;
            }

            // Semester validation
            const semester = document.getElementById('modal_semester').value.trim();
            if (!semester) {
                errors['semester'] = 'Semester is required';
                isValid = false;
            }

            // Display errors
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            Object.keys(errors).forEach(field => {
                const input = document.getElementById('modal_' + field);
                if (input) {
                    const errorEl = input.parentElement.querySelector('.error-message');
                    if (errorEl) {
                        errorEl.textContent = errors[field];
                        errorEl.style.display = 'block';
                    }
                }
            });

            return isValid;
        }

        // Close modal when clicking outside of it
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('addStudentModal');
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeAddStudentModal();
                    }
                });
            }

            // Handle form submission
            const form = document.getElementById('addStudentForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();

                    // Clear previous errors
                    document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

                    // Client-side validation
                    if (!validateAddStudentForm()) {
                        return;
                    }

                    // Submit the form
                    const formData = new FormData(form);
                    fetch('{{ route('admin.students.store') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => {
                            if (!response.ok && response.status === 422) {
                                return response.json().then(data => {
                                    throw {
                                        validation: true,
                                        errors: data.errors
                                    };
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                closeAddStudentModal();
                                
                                // Show toast notification
                                const toast = document.createElement('div');
                                toast.style.cssText = `
                                    position: fixed;
                                    top: 20px;
                                    right: 20px;
                                    background: #10b981;
                                    color: white;
                                    padding: 12px 16px;
                                    border-radius: 6px;
                                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                                    z-index: 2000;
                                    font-size: 14px;
                                    font-weight: 500;
                                    animation: slideIn 0.3s ease;
                                `;
                                toast.textContent = 'Student added successfully!';
                                document.body.appendChild(toast);
                                
                                setTimeout(() => toast.remove(), 3000);
                                
                                // Refresh the table without full reload
                                if (manager) {
                                    manager.fetchStudents(manager.currentPage);
                                    manager.refreshStats();
                                }
                            } else {
                                alert(data.message || 'Error adding student');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (error.validation && error.errors) {
                                // Display validation errors inline
                                Object.keys(error.errors).forEach(field => {
                                    const input = document.getElementById('modal_' + field);
                                    if (input) {
                                        const errorEl = input.parentElement.querySelector(
                                            '.error-message');
                                        if (errorEl) {
                                            errorEl.textContent = error.errors[field][0];
                                            errorEl.style.display = 'block';
                                        }
                                    }
                                });
                            } else {
                                alert('Error adding student');
                            }
                        });
                });
            }
        });

        let manager;
        document.addEventListener('DOMContentLoaded', () => {
            manager = new StudentManager();
        });
    </script>
@endpush
