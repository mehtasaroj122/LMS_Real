@php
    $config = $studentManagementConfig;
@endphp

<div id="studentManagementRoot">
    <div class="mb-3">
        <h1 class="text-xl font-bold text-primary">{{ $config['labels']['pageTitle'] ?? 'Students' }}</h1>
        <p class="mt-0.5 text-xs text-secondary">Manage student records</p>
    </div>

    <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-3">
        <div class="p-3 card student-stat-card is-loading" data-stat-card="total">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-secondary">Total Students</p>
                    <div class="student-stat-copy">
                        <h3 class="mt-1 text-2xl font-bold text-primary student-stat-value" id="studentTotalCount">0</h3>
                        <p class="mt-1 text-xs text-secondary student-stat-description" id="studentTotalMeta">Live overview across all departments and batches</p>
                    </div>
                    <div class="student-stat-loading-lines" aria-hidden="true">
                        <span class="student-stat-loading-line short"></span>
                        <span class="student-stat-loading-line long"></span>
                    </div>
                </div>
                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg dark:bg-blue-900">
                    <i data-lucide="users" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
        </div>

        <div class="p-3 card student-stat-card is-loading" data-stat-card="active">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-secondary">Active</p>
                    <div class="student-stat-copy">
                        <h3 class="mt-1 text-2xl font-bold text-primary student-stat-value" id="studentActiveCount">0</h3>
                        <p class="mt-1 text-xs text-secondary student-stat-description" id="studentActiveMeta">Student accounts currently ready to borrow books</p>
                    </div>
                    <div class="student-stat-loading-lines" aria-hidden="true">
                        <span class="student-stat-loading-line short"></span>
                        <span class="student-stat-loading-line long"></span>
                    </div>
                </div>
                <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg dark:bg-green-900">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400"></i>
                </div>
            </div>
        </div>

        <div class="p-3 card student-stat-card is-loading" data-stat-card="inactive">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-secondary">Inactive</p>
                    <div class="student-stat-copy">
                        <h3 class="mt-1 text-2xl font-bold text-primary student-stat-value" id="studentInactiveCount">0</h3>
                        <p class="mt-1 text-xs text-secondary student-stat-description" id="studentInactiveMeta">Accounts currently paused from circulation activity</p>
                    </div>
                    <div class="student-stat-loading-lines" aria-hidden="true">
                        <span class="student-stat-loading-line short"></span>
                        <span class="student-stat-loading-line long"></span>
                    </div>
                </div>
                <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-lg dark:bg-red-900">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-2">
        <h2 class="text-base font-semibold text-primary">{{ $config['labels']['sectionTitle'] ?? 'All Students' }}</h2>
    </div>

    <div class="search-filter-container">
        <div class="search-box">
            <div class="search-icon">
                <i class="fas fa-search"></i>
            </div>
            <input
                type="text"
                id="studentSearchInput"
                class="search-input"
                placeholder="Search by name, email, or roll number..."
                autocomplete="off"
                aria-label="Search students"
            >
        </div>

        <div class="filters-container">
            @if(($config['features']['statusFilter'] ?? false) === true)
                <select id="studentStatusFilter" class="filter-select" aria-label="Filter students by status">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            @endif

            <select id="studentDepartmentFilter" class="filter-select" aria-label="Filter students by department">
                <option value="all">All Departments</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>

            <select id="studentSortFilter" class="filter-select" aria-label="Sort students">
                <option value="created-desc">Newest First</option>
                <option value="created-asc">Oldest First</option>
                <option value="name-asc">Alphabetical A-Z</option>
                <option value="name-desc">Alphabetical Z-A</option>
            </select>

            <button id="studentResetFiltersBtn" class="reset-btn" title="Reset all filters">
                <i class="fas fa-redo"></i>
                Reset
            </button>
        </div>

        @if(($config['features']['create'] ?? false) === true)
            <button id="studentCreateBtn" class="flex items-center gap-2 px-3 py-1 font-medium text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg" style="margin-left: auto;">
                <i class="fas fa-plus"></i>
                {{ $config['labels']['createButton'] ?? 'Add Student' }}
            </button>
        @endif
    </div>

    <div class="student-toolbar-meta">
        <div id="studentFilterSummary">Sort: Newest first • <strong>0</strong> matching students</div>
        <div id="studentLastUpdated">Waiting for data...</div>
    </div>

    <div class="table-container" id="studentTableShell">
        <div class="table-wrapper" id="studentTableWrapper">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Batch</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="studentTableBody" class="student-table-loading">
                    @for ($i = 0; $i < 5; $i++)
                        <tr>
                            <td><span class="student-skeleton-line long"></span></td>
                            <td><span class="student-skeleton-line medium"></span></td>
                            <td><span class="student-skeleton-line short"></span></td>
                            <td><span class="student-skeleton-line medium"></span></td>
                            <td><span class="student-skeleton-line short"></span></td>
                            <td><span class="student-skeleton-line short"></span></td>
                            <td><span class="student-skeleton-line long"></span></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div id="studentEmptyState" class="student-empty-state" hidden>
            <h3>No students found</h3>
            <p id="studentEmptyMessage">Try adjusting your search or filters.</p>
        </div>

        <div id="paginationContainer">
            <div class="student-pagination-buttons" id="studentPaginationButtons"></div>
        </div>
    </div>

    @if(($config['features']['create'] ?? false) === true)
        <div id="addStudentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
            <div style="border-radius: 8px; padding: 16px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Add New Student</h2>
                    <button type="button" id="studentCreateCloseBtn" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
                </div>

                <form id="studentCreateForm" novalidate>
                    @csrf

                    <div style="margin-bottom: 12px;">
                        <label for="student_name" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Full Name <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="student_name" name="name" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;" placeholder="Enter student's full name">
                        <span class="student-field-error" data-error-for="name"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_email" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Email <span style="color: #ef4444;">*</span></label>
                        <input type="email" id="student_email" name="email" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;" placeholder="student@example.com">
                        <span class="student-field-error" data-error-for="email"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_phone" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Phone <span style="color: #ef4444;">*</span></label>
                        <input type="tel" id="student_phone" name="phone" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;" placeholder="+9779812345678">
                        <span class="student-field-error" data-error-for="phone"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_date_of_birth" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Date of Birth <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="student_date_of_birth" name="date_of_birth" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <span class="student-field-error" data-error-for="date_of_birth"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_roll_no" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Student ID <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="student_roll_no" name="roll_no" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;" placeholder="Enter student ID">
                        <span class="student-field-error" data-error-for="roll_no"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_department_id" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Department <span style="color: #ef4444;">*</span></label>
                        <select id="student_department_id" name="department_id" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                            <option value="">Select department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        <span class="student-field-error" data-error-for="department_id"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_batch" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Batch <span style="color: #ef4444;">*</span></label>
                        <input type="text" id="student_batch" name="batch" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;" placeholder="e.g., 2024">
                        <span class="student-field-error" data-error-for="batch"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_semester" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Semester <span style="color: #ef4444;">*</span></label>
                        <input type="number" id="student_semester" name="semester" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;" min="1" max="12" placeholder="1">
                        <span class="student-field-error" data-error-for="semester"></span>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label for="student_address" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Address <span style="color: #ef4444;">*</span></label>
                        <textarea id="student_address" name="address" style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box; min-height: 80px;" placeholder="Enter student's address"></textarea>
                        <span class="student-field-error" data-error-for="address"></span>
                    </div>

                    <p class="student-helper">The student account is created as active with the system default password.</p>

                    <div style="display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px;">
                        <button type="button" id="studentCreateCancelBtn" style="padding: 8px 16px; border: 1px solid; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 13px;">Cancel</button>
                        <button type="submit" id="studentCreateSubmitBtn" style="padding: 8px 16px; border: none; border-radius: 6px; background: #3b82f6; color: white; font-weight: 500; cursor: pointer; font-size: 13px;">Add Student</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div id="studentToastContainer" class="student-toast-container" aria-live="polite" aria-atomic="true"></div>
    <div id="studentLiveRegion" class="sr-only" aria-live="polite" aria-atomic="true"></div>
</div>
