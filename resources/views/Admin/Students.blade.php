@extends('Admin.layouts.app')

@section('title', 'Students')

@push('styles')
    <style>
        /* ===== TABLE & PAGINATION STYLES (dual theme) ===== */
        .table-container {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid;
            margin-top: 12px;
            transition: background-color 0.3s, border-color 0.3s;
        }

        /* Light theme table container */
        body.light-theme .table-container {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }
        /* Dark theme table container */
        body.dark-theme .table-container {
            background-color: #1e293b;
            border-color: #334155;
        }

        .table-wrapper {
            overflow-x: hidden;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1110px;
        }

        .students-table th {
            padding: 6px 8px;
            font-weight: 600;
            font-size: 11px;
            border-bottom: 1px solid;
            white-space: nowrap;
            text-align: start;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        /* Light theme th */
        body.light-theme .students-table th {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
        }
        /* Dark theme th */
        body.dark-theme .students-table th {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }

        .students-table td {
            padding: 6px 8px;
            border-bottom: 1px solid;
            vertical-align: middle;
            font-size: 13px;
            transition: border-color 0.3s, color 0.3s;
        }

        /* Light theme td */
        body.light-theme .students-table td {
            border-color: #e2e8f0;
            color: #0f172a;
        }
        /* Dark theme td */
        body.dark-theme .students-table td {
            border-color: #334155;
            color: #f1f5f9;
        }

        .students-table tr:last-child td {
            border-bottom: none;
        }

        .students-table tr:hover {
            transition: background-color 0.3s;
        }
        body.light-theme .students-table tr:hover {
            background-color: #f8fafc;
        }
        body.dark-theme .students-table tr:hover {
            background-color: #2d3748;
        }

        /* Column width constraints */
        .students-table th:nth-child(1),
        .students-table td:nth-child(1) {
            padding-right: 4px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .students-table th:nth-child(2),
        .students-table td:nth-child(2) {
            padding-left: 4px;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .students-table th:nth-child(3),
        .students-table td:nth-child(3),
        .students-table th:nth-child(4),
        .students-table td:nth-child(4),
        .students-table th:nth-child(5),
        .students-table td:nth-child(5) {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .students-table th:nth-child(6),
        .students-table td:nth-child(6) {
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Text muted (secondary info) */
        .text-muted {
            transition: color 0.3s;
        }
        body.light-theme .text-muted {
            color: #64748b;
        }
        body.dark-theme .text-muted {
            color: #94a3b8;
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

        .student-email {
            font-size: 0.75rem;
            line-height: 1.4;
        }

        /* Status badges with Font Awesome icons */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
            gap: 4px;
            transition: background 0.3s, color 0.3s;
        }
        body.light-theme .status-active {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }
        body.dark-theme .status-active {
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
            color: #4ade80;
        }
        body.light-theme .status-inactive {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }
        body.dark-theme .status-inactive {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: flex-start;
        }
        .action-btn {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: background-color 0.3s, color 0.3s;
            cursor: pointer;
            text-decoration: none;
        }
        body.light-theme .action-btn {
            color: #64748b;
            background-color: #f1f5f9;
        }
        body.dark-theme .action-btn {
            color: #94a3b8;
            background-color: #334155;
        }
        body.light-theme .action-btn:hover {
            color: #3b82f6;
            background-color: #e0e7ff;
        }
        body.dark-theme .action-btn:hover {
            color: #93c5fd;
            background-color: #1e40af;
        }

        /* ===== PAGINATION STYLES (dual theme) ===== */
        .pagination {
            display: flex;
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 0;
            justify-content: flex-end;
        }
        .pagination li a,
        .pagination li span {
            display: block;
            padding: 6px 12px;
            border: 1px solid;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        /* Light theme pagination */
        body.light-theme .pagination li a,
        body.light-theme .pagination li span {
            background-color: #ffffff;
            border-color: #e2e8f0;
            color: #3b82f6;
        }
        body.light-theme .pagination li a:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }
        body.light-theme .pagination li.active span {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        body.light-theme .pagination li.disabled span {
            color: #94a3b8;
            background-color: #f1f5f9;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        /* Dark theme pagination */
        body.dark-theme .pagination li a,
        body.dark-theme .pagination li span {
            background-color: #1e293b;
            border-color: #475569;
            color: #94a3b8;
        }
        body.dark-theme .pagination li a:hover {
            background-color: #334155;
            color: #e2e8f0;
        }
        body.dark-theme .pagination li.active span {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        body.dark-theme .pagination li.disabled span {
            color: #64748b;
            background-color: #0f172a;
            border-color: #334155;
            cursor: not-allowed;
        }

        #paginationContainer {
            padding: 16px 0 0;
        }

        /* ===== SEARCH & FILTER CONTAINER STYLES ===== */
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

        /* ===== Edit Student Modal Theme Support ===== */
        #editStudentModal {
            backdrop-filter: blur(4px);
        }

        #editStudentModal>div {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.light-theme #editStudentModal>div {
            background-color: #ffffff;
            color: #1f2937;
        }

        body.dark-theme #editStudentModal>div {
            background-color: #1e293b;
            color: #e5e7eb;
        }

        #editStudentModal h2 {
            transition: color 0.3s ease;
        }

        body.light-theme #editStudentModal h2 {
            color: #1f2937;
        }

        body.dark-theme #editStudentModal h2 {
            color: #e5e7eb;
        }

        #editStudentModal label {
            transition: color 0.3s ease;
        }

        body.light-theme #editStudentModal label {
            color: #374151;
        }

        body.dark-theme #editStudentModal label {
            color: #d1d5db;
        }

        #editStudentModal input,
        #editStudentModal textarea,
        #editStudentModal select {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.light-theme #editStudentModal input,
        body.light-theme #editStudentModal textarea,
        body.light-theme #editStudentModal select {
            background-color: #ffffff;
            color: #1f2937;
            border-color: #d1d5db;
        }

        body.dark-theme #editStudentModal input,
        body.dark-theme #editStudentModal textarea,
        body.dark-theme #editStudentModal select {
            background-color: #0f172a;
            color: #e5e7eb;
            border-color: #334155;
        }

        body.light-theme #editStudentModal input:focus,
        body.light-theme #editStudentModal textarea:focus,
        body.light-theme #editStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme #editStudentModal input:focus,
        body.dark-theme #editStudentModal textarea:focus,
        body.dark-theme #editStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        #editStudentModal button[type="button"],
        #editStudentModal button[type="submit"] {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.light-theme #editStudentModal button[type="button"] {
            background-color: white;
            color: #374151;
            border-color: #d1d5db;
        }

        body.light-theme #editStudentModal button[type="button"]:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme #editStudentModal button[type="button"] {
            background-color: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-theme #editStudentModal button[type="button"]:hover {
            background-color: #4b5563;
        }

        #editStudentModal button[onclick*="closeEditStudentModal"] {
            transition: color 0.3s ease;
        }

        body.light-theme #editStudentModal button[onclick*="closeEditStudentModal"] {
            color: #6b7280;
        }

        body.dark-theme #editStudentModal button[onclick*="closeEditStudentModal"] {
            color: #9ca3af;
        }

        #editStudentModal .error-message {
            transition: color 0.3s ease;
            display: none !important;
            color: #dc2626;
            font-size: 12px;
            font-weight: 500;
        }

        /* Explicitly show error messages when .show class is applied */
        #editStudentModal .error-message.show {
            display: block !important;
            visibility: visible !important;
        }

        body.light-theme #editStudentModal .error-message {
            color: #dc2626;
        }

        body.dark-theme #editStudentModal .error-message {
            color: #fca5a5;
        }

        #editStudentModal .required-asterisk {
            color: #ef4444;
            font-weight: 600;
        }

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
                    <i class="fas fa-search"></i>
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
                    <i class="fas fa-redo"></i>
                    Reset
                </button>
            </div>

            <button id="addStudentBtn" class="flex items-center gap-2 px-3 py-1 font-medium text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg" style="margin-left: auto;">
                <i class="fas fa-plus"></i>
                Add Student
            </button>
        </div>

        <!-- Students Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Batch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" style="padding: 16px;">
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

    <!-- Edit Student Modal -->
    <div id="editStudentModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div
            style="border-radius: 8px; padding: 16px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Edit Student</h2>
                <button onclick="closeEditStudentModal()"
                    style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
            </div>

            <form id="editStudentForm" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 12px;">
                    <label for="edit_name" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Full Name <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="edit_name" name="name" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter student's full name">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="edit_email" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Email <span
                            class="required-asterisk">*</span></label>
                    <input type="email" id="edit_email" name="email" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="student@example.com">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="edit_phone" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Phone <span
                            class="required-asterisk">*</span></label>
                    <input type="tel" id="edit_phone" name="phone" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter phone number">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="edit_roll_no" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Roll Number
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="edit_roll_no" name="roll_no" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter roll number">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="edit_department_id"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Department <span
                            class="required-asterisk">*</span></label>
                    <select id="edit_department_id" name="department_id" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select a department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="edit_batch" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Batch <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="edit_batch" name="batch" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 2024">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="edit_semester" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Semester
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="edit_semester" name="semester" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 1">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 16px;">
                    <label for="edit_address"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Address</label>
                    <textarea id="edit_address" name="address"
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box; min-height: 80px;"
                        placeholder="Enter student's address"></textarea>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="margin-bottom: 12px;">
                    <label for="edit_status" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Status <span
                            class="required-asterisk">*</span></label>
                    <select id="edit_status" name="status" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closeEditStudentModal()"
                        style="padding: 8px 16px; border: 1px solid; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding: 8px 16px; border: none; border-radius: 6px; background: #3b82f6; color: white; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Update Student
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

        // Toggle student status
        window.toggleStudentStatus = function(studentId) {
            fetch(`{{ url('admin/students') }}/${studentId}/toggle-status`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Refresh the table to show updated status
                        manager.fetchStudents(manager.currentPage);
                        manager.refreshStats();

                        // Show toast notification
                        const toast = document.createElement('div');
                        toast.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: #3b82f6;
                            color: white;
                            padding: 12px 16px;
                            border-radius: 6px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            z-index: 2000;
                            font-size: 14px;
                            font-weight: 500;
                        `;
                        toast.textContent = 'Student status updated successfully!';
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 3000);
                    } else {
                        alert(data.message || 'Error updating student status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating student status');
                });
        };

        // Open Edit Student Modal
        window.openEditStudentModal = function(studentId) {
            // Get the modal
            const modal = document.getElementById('editStudentModal');
            
            // IMMEDIATELY clear ALL error messages from previous failed submissions
            const errorElements = modal.querySelectorAll('.error-message');
            console.log('📋 Opening edit modal - Clearing', errorElements.length, 'error elements');
            errorElements.forEach((el, index) => {
                el.textContent = '';
                el.innerHTML = '';
                el.classList.remove('show');
                // Force hide with inline style
                el.style.cssText = 'display: none !important;';
                console.log(`  ✓ Cleared error ${index + 1}`);
            });
            console.log('✅ All errors cleared. Classes:', modal.querySelector('.error-message')?.className);
            
            // Fetch student data
            fetch(`{{ url('admin/students') }}/${studentId}/edit-data`, {
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
                        // Populate form fields
                        document.getElementById('edit_name').value = data.data.name || '';
                        document.getElementById('edit_email').value = data.data.email || '';
                        document.getElementById('edit_phone').value = data.data.phone || '';
                        document.getElementById('edit_roll_no').value = data.data.roll_no || '';
                        document.getElementById('edit_department_id').value = data.data.department_id || '';
                        document.getElementById('edit_batch').value = data.data.batch || '';
                        document.getElementById('edit_semester').value = data.data.semester || '';
                        document.getElementById('edit_address').value = data.data.address || '';
                        document.getElementById('edit_status').value = data.data.status || '';

                        // Update form action
                        const form = document.getElementById('editStudentForm');
                        form.action = `{{ url('admin/students') }}/${studentId}`;

                        // Store studentId for later use
                        form.dataset.studentId = studentId;

                        // Show modal
                        if (modal) {
                            modal.style.display = 'flex';
                        }
                    } else {
                        alert(data.message || 'Error loading student data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading student data');
                });
        };

        // Close Edit Student Modal
        function closeEditStudentModal() {
            console.log('📤 Closing edit modal');
            const modal = document.getElementById('editStudentModal');
            if (modal) {
                modal.style.display = 'none';
            }
            // Reset form and clear ALL errors completely
            const form = document.getElementById('editStudentForm');
            if (form) {
                form.reset();
                // Clear error messages using both class removal and inline styles
                const errors = document.querySelectorAll('#editStudentModal .error-message');
                console.log('  ✓ Cleared', errors.length, 'error messages');
                errors.forEach(el => {
                    el.textContent = '';
                    el.classList.remove('show');
                    el.style.cssText = 'display: none !important;';
                });
            }
        }

        // Validate Edit Form
        function validateEditStudentForm() {
            let isValid = true;
            const errors = {};

            // Name validation
            const name = document.getElementById('edit_name').value.trim();
            if (!name) {
                errors['name'] = 'Full name is required';
                isValid = false;
            }

            // Email validation
            if (isValid) {
                const email = document.getElementById('edit_email').value.trim();
                if (!email) {
                    errors['email'] = 'Email is required';
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    errors['email'] = 'Please enter a valid email address';
                    isValid = false;
                }
            }

            // Phone validation
            if (isValid) {
                const phone = document.getElementById('edit_phone').value.trim();
                if (!phone) {
                    errors['phone'] = 'Phone number is required';
                    isValid = false;
                } else if (!/^[\d]{7,15}$/.test(phone.replace(/[^\d]/g, ''))) {
                    errors['phone'] = 'Phone number must be 7-15 digits';
                    isValid = false;
                }
            }

            // Roll Number validation
            if (isValid) {
                const rollNo = document.getElementById('edit_roll_no').value.trim();
                if (!rollNo) {
                    errors['roll_no'] = 'Roll number is required';
                    isValid = false;
                }
            }

            // Department validation
            if (isValid) {
                const departmentId = document.getElementById('edit_department_id').value;
                if (!departmentId) {
                    errors['department_id'] = 'Please select a department';
                    isValid = false;
                }
            }

            // Batch validation
            if (isValid) {
                const batch = document.getElementById('edit_batch').value.trim();
                if (!batch) {
                    errors['batch'] = 'Batch is required';
                    isValid = false;
                }
            }

            // Semester validation
            if (isValid) {
                const semester = document.getElementById('edit_semester').value.trim();
                if (!semester) {
                    errors['semester'] = 'Semester is required';
                    isValid = false;
                }
            }

            // Status validation
            if (isValid) {
                const status = document.getElementById('edit_status').value;
                if (!status) {
                    errors['status'] = 'Please select a status';
                    isValid = false;
                }
            }

            // Display ONLY THE FIRST error
            document.querySelectorAll('#editStudentModal .error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
                el.style.cssText = 'display: none !important;';
            });
            
            // Show only the first error that occurred
            if (!isValid && Object.keys(errors).length > 0) {
                const firstErrorField = Object.keys(errors)[0];
                const input = document.getElementById('edit_' + firstErrorField);
                if (input) {
                    const errorEl = input.parentElement.querySelector('.error-message');
                    if (errorEl) {
                        errorEl.textContent = errors[firstErrorField];
                        errorEl.classList.add('show');
                        errorEl.style.cssText = 'display: block !important;';
                        console.log(`❌ Validation error: ${firstErrorField} - ${errors[firstErrorField]}`);
                    }
                }
            }

            return isValid;
        }

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
            if (isValid) {
                const email = document.getElementById('modal_email').value.trim();
                if (!email) {
                    errors['email'] = 'Email is required';
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    errors['email'] = 'Please enter a valid email address';
                    isValid = false;
                }
            }

            // Phone validation
            if (isValid) {
                const phone = document.getElementById('modal_phone').value.trim();
                if (!phone) {
                    errors['phone'] = 'Phone number is required';
                    isValid = false;
                } else if (!/^[\d]{7,15}$/.test(phone.replace(/[^\d]/g, ''))) {
                    errors['phone'] = 'Phone number must be 7-15 digits';
                    isValid = false;
                }
            }

            // Roll Number validation
            if (isValid) {
                const rollNo = document.getElementById('modal_roll_no').value.trim();
                if (!rollNo) {
                    errors['roll_no'] = 'Roll number is required';
                    isValid = false;
                }
            }

            // Department validation
            if (isValid) {
                const departmentId = document.getElementById('modal_department_id').value;
                if (!departmentId) {
                    errors['department_id'] = 'Please select a department';
                    isValid = false;
                }
            }

            // Batch validation
            if (isValid) {
                const batch = document.getElementById('modal_batch').value.trim();
                if (!batch) {
                    errors['batch'] = 'Batch is required';
                    isValid = false;
                }
            }

            // Semester validation
            if (isValid) {
                const semester = document.getElementById('modal_semester').value.trim();
                if (!semester) {
                    errors['semester'] = 'Semester is required';
                    isValid = false;
                }
            }

            // Display ONLY THE FIRST error
            document.querySelectorAll('#addStudentModal .error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
                el.style.cssText = 'display: none !important;';
            });
            
            // Show only the first error that occurred
            if (!isValid && Object.keys(errors).length > 0) {
                const firstErrorField = Object.keys(errors)[0];
                const input = document.getElementById('modal_' + firstErrorField);
                if (input) {
                    const errorEl = input.parentElement.querySelector('.error-message');
                    if (errorEl) {
                        errorEl.textContent = errors[firstErrorField];
                        errorEl.classList.add('show');
                        errorEl.style.cssText = 'display: block !important;';
                        console.log(`❌ Validation error: ${firstErrorField} - ${errors[firstErrorField]}`);
                    }
                }
            }

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

            // Handle edit form submission
            const editForm = document.getElementById('editStudentForm');
            if (editForm) {
                editForm.addEventListener('submit', (e) => {
                    e.preventDefault();

                    // Clear previous errors completely using consistent approach
                    document.querySelectorAll('#editStudentModal .error-message').forEach(el => {
                        el.textContent = '';
                        el.classList.remove('show');
                        el.style.cssText = 'display: none !important;';
                    });

                    // Client-side validation
                    if (!validateEditStudentForm()) {
                        return;
                    }

                    // Get studentId from form data attribute
                    const studentId = editForm.dataset.studentId;
                    if (!studentId) {
                        alert('Error: Student ID not found');
                        return;
                    }

                    // Collect form data manually to ensure it's captured
                    const formPayload = {
                        name: document.getElementById('edit_name').value,
                        email: document.getElementById('edit_email').value,
                        phone: document.getElementById('edit_phone').value,
                        roll_no: document.getElementById('edit_roll_no').value,
                        department_id: document.getElementById('edit_department_id').value,
                        batch: document.getElementById('edit_batch').value,
                        semester: document.getElementById('edit_semester').value,
                        address: document.getElementById('edit_address').value,
                        status: document.getElementById('edit_status').value,
                    };
                    
                    // Log all form data for debugging
                    console.log('📤 Form submission - Data to send:');
                    Object.entries(formPayload).forEach(([key, value]) => {
                        console.log(`  ${key}: ${value}`);
                    });

                    // Submit the form
                    fetch(editForm.action, {
                            method: 'PUT',
                            body: JSON.stringify(formPayload),
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
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
                                closeEditStudentModal();
                                
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
                                `;
                                toast.textContent = 'Student updated successfully!';
                                document.body.appendChild(toast);
                                
                                setTimeout(() => toast.remove(), 3000);
                                
                                // Refresh the table
                                if (manager) {
                                    manager.fetchStudents(manager.currentPage);
                                    manager.refreshStats();
                                }
                            } else {
                                alert(data.message || 'Error updating student');
                            }
                        })
                        .catch(error => {
                            console.error('❌ Update failed:', error);
                            if (error.validation && error.errors) {
                                console.log('🔴 Validation errors received:', error.errors);
                                // Display validation errors inline
                                Object.keys(error.errors).forEach(field => {
                                    const input = document.getElementById('edit_' + field);
                                    if (input) {
                                        const errorEl = input.parentElement.querySelector(
                                            '.error-message');
                                        if (errorEl) {
                                            errorEl.textContent = error.errors[field][0];
                                            errorEl.classList.add('show');
                                            errorEl.style.cssText = 'display: block !important;';
                                            console.log(`  🔴 ${field}: ${error.errors[field][0]}`);
                                        }
                                    }
                                });
                            } else {
                                alert('Error updating student');
                            }
                        });
                });
            }

            // Close modals when clicking outside
            const editModal = document.getElementById('editStudentModal');
            if (editModal) {
                editModal.addEventListener('click', (e) => {
                    if (e.target === editModal) {
                        closeEditStudentModal();
                    }
                });
            }
        });

        let manager;
        document.addEventListener('DOMContentLoaded', () => {
            manager = new StudentManager();
        });
    </script>
@endpush
