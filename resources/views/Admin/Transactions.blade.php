@extends('Admin.layouts.app')

@section('title', 'Transaction')

@push('styles')
    <style>
        /* Form Elements */
        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        body.dark-theme .form-label {
            color: #e2e8f0;
        }

        .text-danger {
            color: #dc2626;
        }

        body.dark-theme .text-danger {
            color: #f87171;
        }

        .text-success {
            color: #16a34a;
        }

        body.dark-theme .text-success {
            color: #4ade80;
        }

        /* Search Input Styling */
        .search-container {
            position: relative;
            width: 100%;
        }

        .search-input {
            width: 100%;
            padding: 8px 10px 8px 32px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: #ffffff;
            color: #0f172a;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        body.dark-theme .search-input {
            background-color: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
        }

        body.dark-theme .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 14px;
            height: 14px;
            color: #6b7280;
        }

        body.dark-theme .search-icon {
            color: #94a3b8;
        }

        /* Search Results Dropdown */
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 280px;
            overflow-y: auto;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 3px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 50;
            display: none;
        }

        body.dark-theme .search-results {
            background-color: #1e293b;
            border-color: #334155;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        .result-item {
            padding: 8px 10px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #f3f4f6;
        }

        body.dark-theme .result-item {
            border-bottom-color: #334155;
        }

        .result-item:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme .result-item:hover {
            background-color: #2d3748;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        .result-item.active {
            background-color: #eff6ff;
        }

        body.dark-theme .result-item.active {
            background-color: #1e3a8a;
        }

        .result-title {
            font-weight: 600;
            color: #111827;
            margin-bottom: 2px;
        }

        body.dark-theme .result-title {
            color: #f3f4f6;
        }

        .result-subtitle {
            font-size: 12px;
            color: #6b7280;
        }

        body.dark-theme .result-subtitle {
            color: #9ca3af;
        }

        .selected-value {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 8px;
        }

        body.dark-theme .selected-value {
            background-color: #1e293b;
            border-color: #334155;
        }

        .clear-selection {
            color: #6b7280;
            cursor: pointer;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .clear-selection:hover {
            background-color: #e5e7eb;
            color: #374151;
        }

        body.dark-theme .clear-selection:hover {
            background-color: #4b5563;
            color: #d1d5db;
        }

        /* Selected Books List */
        .selected-books-list {
            margin-top: 16px;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
        }

        body.dark-theme .selected-books-list {
            border-color: #334155;
        }

        .selected-book-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background-color: #f9fafb;
            border-radius: 6px;
            margin-bottom: 6px;
        }

        body.dark-theme .selected-book-item {
            background-color: #1e293b;
        }

        .selected-book-item:last-child {
            margin-bottom: 0;
        }

        .book-info {
            flex: 1;
        }

        .book-title {
            font-weight: 600;
            font-size: 14px;
            color: #111827;
        }

        body.dark-theme .book-title {
            color: #f3f4f6;
        }

        .book-details {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }

        body.dark-theme .book-details {
            color: #9ca3af;
        }

        .remove-book {
            color: #dc2626;
            cursor: pointer;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .remove-book:hover {
            background-color: #fee2e2;
        }

        body.dark-theme .remove-book:hover {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        /* Book Selection Checkbox */
        .book-checkbox-container {
            display: flex;
            align-items: flex-start;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        body.dark-theme .book-checkbox-container {
            border-color: #334155;
        }

        .book-checkbox-container:hover {
            background-color: #f9fafb;
        }

        body.dark-theme .book-checkbox-container:hover {
            background-color: #2d3748;
        }

        .book-checkbox {
            margin-right: 12px;
            margin-top: 2px;
        }

        .book-info-full {
            flex: 1;
        }

        .book-meta {
            display: flex;
            gap: 16px;
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
        }

        body.dark-theme .book-meta {
            color: #9ca3af;
        }

        /* Condition Options */
        .condition-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 8px;
        }

        .condition-option {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        body.dark-theme .condition-option {
            border-color: #334155;
        }

        .condition-option.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
        }

        body.dark-theme .condition-option.selected {
            border-color: #3b82f6;
            background-color: #1e3a8a;
        }

        .condition-label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .condition-fine {
            font-size: 12px;
            color: #dc2626;
        }

        body.dark-theme .condition-fine {
            color: #fca5a5;
        }

        /* Detail Rows */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .detail-label {
            font-size: 13px;
            color: #64748b;
            font-weight: 500;
        }

        .detail-value {
            font-size: 14px;
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }

        body.dark-theme .detail-label {
            color: #94a3b8;
        }

        body.dark-theme .detail-value {
            color: #f1f5f9;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-ontime {
            background-color: #dcfce7;
            color: #166534;
        }

        body.dark-theme .status-overdue {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .status-ontime {
            background-color: #14532d;
            color: #86efac;
        }

        /* Warning Badge */
        .warning-badge {
            background-color: #fef3c7;
            color: #92400e;
        }

        body.dark-theme .warning-badge {
            background-color: #78350f;
            color: #fbbf24;
        }

        /* Info Box */
        .info-box {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
        }

        body.dark-theme .info-box {
            background-color: #1e3a8a;
        }

        /* Button */
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        body.dark-theme .btn-primary {
            background-color: #1e40af;
        }

        body.dark-theme .btn-primary:hover {
            background-color: #1e3a8a;
        }

        body.dark-theme .btn-primary:disabled {
            background-color: #475569;
        }

        /* Form Select */
        .form-select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: #ffffff;
            color: #0f172a;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 8px center;
            background-repeat: no-repeat;
            background-size: 20px;
            padding-right: 36px;
        }

        .form-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        body.dark-theme .form-select {
            background-color: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2394A3B8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        }

        body.dark-theme .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Progress Bar */
        .progress-bar {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }

        body.dark-theme .progress-bar {
            background-color: #374151;
        }

        .progress-fill {
            height: 100%;
            background-color: #2563eb;
            transition: width 0.3s ease;
        }

        body.dark-theme .progress-fill {
            background-color: #3b82f6;
        }

        /* Scrollable Container */
        .scrollable-container {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 4px;
        }

        /* Total Fine Display */
        .total-fine {
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
            text-align: right;
        }

        body.dark-theme .total-fine {
            color: #f87171;
        }
    </style>
@endpush

@section('content')
    <div class="p-6">
        <!-- Issue Book Section -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-primary">Issue Book</h1>
            <p class="mb-6 text-secondary">Issue multiple books to a student (Max 5 books)</p>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Issue Form -->
                <div class="p-6 card">
                    <h2 class="mb-6 text-lg font-semibold text-primary">Issue Form</h2>

                    <form id="issueForm">
                        <!-- Search Student -->
                        <div class="mb-5">
                            <label class="form-label">
                                Search Student<span class="text-danger">*</span>
                            </label>
                            <div class="search-container">
                                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" id="searchStudent" class="search-input"
                                    placeholder="Search by name, student ID, or email..." required>
                                <button type="button" id="clearStudentBtn" class="clear-btn"
                                    onclick="clearStudentSelection()"
                                    style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); padding: 0.2rem 0.5rem; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 0.375rem; cursor: pointer; font-size: 0.75rem; white-space: nowrap;">Clear</button>
                                <div class="search-results" id="studentResults"></div>
                            </div>
                            <input type="hidden" id="selectedStudentId">
                        </div>

                        <!-- Search Books -->
                        <div class="mb-4">
                            <label class="form-label">
                                Search Books<span class="text-danger">*</span>
                            </label>
                            <div class="search-container">
                                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" id="searchBook" class="search-input"
                                    placeholder="Search by title, author, or ISBN...">
                                <div class="search-results" id="bookResults"></div>
                            </div>
                        </div>

                        <!-- Selected Books List -->
                        <div class="selected-books-list" id="selectedBooksList" style="display: none;">
                            <div class="mb-2 text-sm text-secondary">Selected Books (Max: 5)</div>
                            <div id="selectedBooksContainer"></div>
                        </div>

                        <!-- Issue Button -->
                        <button type="submit" class="w-full py-3 mt-4 font-semibold rounded-lg btn-primary"
                            id="issueButton" disabled>
                            Issue Books (0/5)
                        </button>
                    </form>
                </div>

                <!-- Selected Details -->
                <div class="space-y-6">
                    <!-- Selected Student Details -->
                    <div class="p-6 card" id="selectedStudentCard" style="display: none;">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <h3 class="text-base font-semibold text-primary">Student Information</h3>
                        </div>

                        <div class="space-y-3">
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value" id="studentName">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Roll No:</span>
                                <span class="detail-value" id="studentID">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Department:</span>
                                <span class="detail-value" id="studentDepartment">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value" id="studentEmail">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Books Issued:</span>
                                <span class="detail-value" id="studentIssued">0 / 5</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" id="issuedProgress" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Available Books Summary -->
                    <div class="p-6 card" id="availableBooksCard" style="display: none;">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <h3 class="text-base font-semibold text-primary">Selected Books</h3>
                            </div>
                            <span class="text-sm text-secondary" id="selectedCount">0 books</span>
                        </div>

                        <div class="space-y-3">
                            <div class="detail-row">
                                <span class="detail-label">Total Books:</span>
                                <span class="detail-value" id="totalBooks">0</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Can Issue More:</span>
                                <span class="detail-value" id="canIssueMore">5</span>
                            </div>
                            <div class="p-3 mt-4 rounded-lg info-box">
                                <p class="text-sm">Select up to 5 different books for this student. Click on search results
                                    to add books.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Book Section -->
        <div>
            <h1 class="mb-2 text-3xl font-bold text-primary">Return Book</h1>
            <p class="mb-6 text-secondary">Process book returns and calculate fines</p>

            <!-- Info Cards -->
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
                <div class="p-6 text-center card">
                    <div class="mb-2 text-2xl font-bold text-primary">{{ $fineSettings->issue_duration_days }} days</div>
                    <div class="text-sm text-secondary">Issue Duration</div>
                </div>
                <div class="p-6 text-center card">
                    <div class="mb-2 text-2xl font-bold text-primary">₹{{ $fineSettings->per_day_fine }}/day</div>
                    <div class="text-sm text-secondary">Late Fine</div>
                </div>
                <div class="p-6 text-center card">
                    <div class="mb-2 text-2xl font-bold text-primary">₹{{ $fineSettings->lost_book_penalty }}</div>
                    <div class="text-sm text-secondary">Lost Book Fine</div>
                </div>
                <div class="p-6 text-center card">
                    <div class="mb-2 text-2xl font-bold text-primary">₹{{ $fineSettings->damaged_book_penalty }}</div>
                    <div class="text-sm text-secondary">Damaged Book Fine</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Return Form -->
                <div class="p-6 card">
                    <h2 class="mb-6 text-lg font-semibold text-primary">Return Form</h2>

                    <form id="returnForm">
                        <!-- Search Student -->
                        <div class="mb-5">
                            <label class="form-label">
                                Search Student<span class="text-danger">*</span>
                            </label>
                            <div class="search-container">
                                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" id="searchReturnStudent" class="search-input"
                                    placeholder="Search by name, student ID, or email..." required>
                                <button type="button" id="clearReturnStudentBtn" class="clear-btn"
                                    onclick="clearReturnStudentSelection()"
                                    style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); padding: 0.2rem 0.5rem; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 0.375rem; cursor: pointer; font-size: 0.75rem; white-space: nowrap;">Clear</button>
                                <div class="search-results" id="returnStudentResults"></div>
                            </div>
                            <input type="hidden" id="selectedReturnStudentId">
                        </div>

                        <!-- Issued Books List -->
                        <div class="mb-5" id="issuedBooksSection" style="display: none;">
                            <label class="form-label">
                                Select Books to Return<span class="text-danger">*</span>
                            </label>
                            <div class="scrollable-container" id="issuedBooksContainer"></div>
                        </div>

                        <!-- Book Condition for Selected Books -->
                        <div class="mb-5" id="bookConditionSection" style="display: none;">
                            <label class="form-label">
                                Book Condition & Fine<span class="text-danger">*</span>
                            </label>
                            <div class="condition-options">
                                <div class="condition-option" data-condition="good" data-fine="0"
                                    onclick="selectCondition('good')">
                                    <div class="condition-label">Good</div>
                                    <div class="text-success">No Fine</div>
                                </div>
                                <div class="condition-option" data-condition="fair"
                                    data-fine="{{ $fineSettings->fair_condition_penalty }}"
                                    onclick="selectCondition('fair')">
                                    <div class="condition-label">Fair</div>
                                    <div class="condition-fine">₹{{ $fineSettings->fair_condition_penalty }} Fine</div>
                                </div>
                                <div class="condition-option" data-condition="damaged"
                                    data-fine="{{ $fineSettings->damaged_book_penalty }}"
                                    onclick="selectCondition('damaged')">
                                    <div class="condition-label">Damaged</div>
                                    <div class="condition-fine">₹{{ $fineSettings->damaged_book_penalty }} Fine</div>
                                </div>
                                <div class="condition-option" data-condition="lost"
                                    data-fine="{{ $fineSettings->lost_book_penalty }}" onclick="selectCondition('lost')">
                                    <div class="condition-label">Lost</div>
                                    <div class="condition-fine">₹{{ $fineSettings->lost_book_penalty }} Fine</div>
                                </div>
                            </div>
                            <input type="hidden" id="selectedCondition" value="">
                        </div>

                        <!-- Process Return Button -->
                        <button type="submit" class="w-full py-3 font-semibold rounded-lg btn-primary" id="returnButton"
                            disabled>
                            Process Return
                        </button>
                    </form>
                </div>

                <!-- Return Details -->
                <div class="space-y-6">
                    <!-- Student Information -->
                    <div class="p-6 card" id="returnStudentCard" style="display: none;">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <h3 class="text-base font-semibold text-primary">Student Information</h3>
                        </div>

                        <div class="space-y-3">
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value" id="returnStudentName">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Roll No:</span>
                                <span class="detail-value" id="returnStudentID">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Department:</span>
                                <span class="detail-value" id="returnStudentDepartment">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Email:</span>
                                <span class="detail-value" id="returnStudentEmail">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Books Issued:</span>
                                <span class="detail-value" id="returnStudentIssued">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fine Calculation -->
                    <div class="p-6 card" id="fineCalculationCard" style="display: none;">
                        <h3 class="mb-4 text-base font-semibold text-primary">Fine Calculation</h3>

                        <div class="mb-4 space-y-3" id="fineDetails">
                            <!-- Fine details will be populated here -->
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="detail-row">
                                <span class="text-lg font-bold text-primary">Total Fine:</span>
                                <span class="total-fine" id="totalFine">₹0</span>
                            </div>
                        </div>

                        <div class="p-3 mt-4 rounded-lg info-box">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm">Select books and condition to calculate total fine</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Fine settings from backend
        const fineSettings = @json($fineSettings);

        // Issue Book Variables
        let selectedStudent = null;
        let selectedBooks = [];
        const maxBooksPerStudent = 5;

        // Return Book Variables
        let selectedReturnStudent = null;
        let selectedIssuedBooks = [];
        let selectedCondition = null;
        let returnSearchDebounceTimer = null;

        document.addEventListener('DOMContentLoaded', function() {
            // ========== ISSUE BOOK FUNCTIONALITY ==========

            // Issue Book Elements
            const searchStudentInput = document.getElementById('searchStudent');
            const studentResults = document.getElementById('studentResults');
            const selectedStudentId = document.getElementById('selectedStudentId');
            const selectedStudentCard = document.getElementById('selectedStudentCard');
            const availableBooksCard = document.getElementById('availableBooksCard');

            const searchBookInput = document.getElementById('searchBook');
            const bookResults = document.getElementById('bookResults');
            const selectedBooksList = document.getElementById('selectedBooksList');
            const selectedBooksContainer = document.getElementById('selectedBooksContainer');
            const issueButton = document.getElementById('issueButton');
            const issueForm = document.getElementById('issueForm');

            // ========== RETURN BOOK FUNCTIONALITY ==========

            // Return Book Elements
            const searchReturnStudentInput = document.getElementById('searchReturnStudent');
            const returnStudentResults = document.getElementById('returnStudentResults');
            const selectedReturnStudentId = document.getElementById('selectedReturnStudentId');
            const returnStudentCard = document.getElementById('returnStudentCard');
            const issuedBooksSection = document.getElementById('issuedBooksSection');
            const issuedBooksContainer = document.getElementById('issuedBooksContainer');
            const bookConditionSection = document.getElementById('bookConditionSection');
            const returnButton = document.getElementById('returnButton');
            const returnForm = document.getElementById('returnForm');
            const fineCalculationCard = document.getElementById('fineCalculationCard');
            const fineDetails = document.getElementById('fineDetails');
            const totalFineElement = document.getElementById('totalFine');

            // ========== ISSUE BOOK SEARCH FUNCTIONALITY ==========

            // Search Students for Issue
            searchStudentInput.addEventListener('input', function() {
                const query = this.value;
                studentResults.innerHTML = '';

                if (query.length < 2) {
                    studentResults.style.display = 'none';
                    return;
                }

                // Fetch students from API
                fetch(`{{ route('admin.transactions.students') }}?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(students => {
                        if (students.length === 0) {
                            studentResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No students found</div></div>';
                        } else {
                            students.forEach(student => {
                                const item = document.createElement('div');
                                item.className = 'result-item';
                                item.dataset.id = student.id;
                                item.innerHTML = `
                                    <div class="result-title">${student.name} (${student.roll_no})</div>
                                    <div class="result-subtitle">${student.department} • Books Issued: ${student.issued}/${student.maxBooks}</div>
                                `;

                                item.addEventListener('click', function() {
                                    selectStudentForIssue(student);
                                });

                                studentResults.appendChild(item);
                            });
                        }

                        studentResults.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        studentResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error loading students</div></div>';
                        studentResults.style.display = 'block';
                    });
            });

            // Search Books for Issue
            searchBookInput.addEventListener('input', function() {
                const query = this.value;
                bookResults.innerHTML = '';

                if (query.length < 2) {
                    bookResults.style.display = 'none';
                    return;
                }

                if (!selectedStudent) {
                    bookResults.innerHTML =
                        '<div class="result-item"><div class="result-title">Please select a student first</div></div>';
                    bookResults.style.display = 'block';
                    return;
                }

                // Fetch available books from API
                fetch(
                        `{{ route('admin.transactions.books') }}?query=${encodeURIComponent(query)}&studentId=${selectedStudent.id}`
                    )
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(books => {
                        if (books.length === 0) {
                            bookResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No available books found</div></div>';
                        } else {
                            books.forEach(book => {
                                // Skip if already selected
                                if (selectedBooks.some(b => b.id === book.id)) {
                                    return;
                                }

                                const item = document.createElement('div');
                                item.className = 'result-item';
                                item.dataset.id = book.id;
                                item.innerHTML = `
                                    <div class="result-title">${book.title}</div>
                                    <div class="result-subtitle">${book.author} • ISBN: ${book.isbn}</div>
                                `;

                                item.addEventListener('click', function() {
                                    addBookToSelection(book);
                                    searchBookInput.value = '';
                                    bookResults.style.display = 'none';
                                });

                                bookResults.appendChild(item);
                            });
                        }

                        bookResults.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error loading books:', error);
                        bookResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error loading books: ' +
                            error.message + '</div></div>';
                        bookResults.style.display = 'block';
                    });
            });

            // ========== RETURN BOOK SEARCH FUNCTIONALITY ==========
            // Search Students for Return (with debouncing to improve performance)
            searchReturnStudentInput.addEventListener('input', function() {
                const query = this.value;

                if (query.length < 2) {
                    returnStudentResults.style.display = 'none';
                    if (returnSearchDebounceTimer) clearTimeout(returnSearchDebounceTimer);
                    return;
                }

                // Show loading state
                returnStudentResults.innerHTML =
                    '<div class="result-item"><div class="result-title">Searching...</div></div>';
                returnStudentResults.style.display = 'block';

                // Clear previous timeout
                if (returnSearchDebounceTimer) clearTimeout(returnSearchDebounceTimer);

                // Fetch immediately without delay - show results as you type
                console.log('Fetching students for query:', query);
                fetch(
                        `{{ route('admin.transactions.students') }}?query=${encodeURIComponent(query)}`
                    )
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(students => {
                        console.log('Students received:', students);
                        if (students.length === 0) {
                            returnStudentResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No students found</div></div>';
                        } else {
                            returnStudentResults.innerHTML = '';

                            // Use Set to avoid duplicates based on student ID
                            const uniqueStudents = [...new Map(students.map(s => [s.id, s]))
                                .values()
                            ];

                            const studentItems = new Map();

                            // Create item elements for all students immediately
                            uniqueStudents.forEach(student => {
                                const item = document.createElement('div');
                                item.className = 'result-item';
                                item.dataset.id = student.id;
                                item.innerHTML = `
                                    <div class="result-title">${student.name} (${student.roll_no})</div>
                                    <div class="result-subtitle">${student.department} • <span class="book-count">...</span> books</div>
                                `;
                                item.style.cursor = 'pointer';

                                returnStudentResults.appendChild(item);
                                studentItems.set(student.id, {
                                    item,
                                    student
                                });
                            });

                            // Fetch issued books count for ALL students in parallel (quick lightweight call)
                            uniqueStudents.forEach(student => {
                                fetch(
                                        `{{ route('admin.transactions.issued-books') }}?studentId=${student.id}&countOnly=1`)
                                    .then(resp => resp.json())
                                    .then(data => {
                                        const count = data.count || 0;
                                        const {
                                            item
                                        } = studentItems.get(student.id);
                                        const bookCountSpan = item.querySelector(
                                            '.book-count');

                                        if (bookCountSpan) {
                                            bookCountSpan.textContent = count;

                                            // Disable if no books
                                            if (count === 0) {
                                                item.style.opacity = '0.6';
                                                item.style.cursor = 'not-allowed';
                                                item.style.pointerEvents = 'none';
                                            }
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Error fetching book count:', err);
                                        const {
                                            item
                                        } = studentItems.get(student.id);
                                        const bookCountSpan = item.querySelector(
                                            '.book-count');
                                        if (bookCountSpan) bookCountSpan.textContent = '0';
                                    });
                            });

                            // Attach click handler to fetch full book details when clicked
                            uniqueStudents.forEach(student => {
                                const {
                                    item
                                } = studentItems.get(student.id);
                                item.addEventListener('click', function() {
                                    console.log('Fetching issued books for student:',
                                        student.id);
                                    item.innerHTML = `
                                        <div class="result-title">${student.name} (${student.roll_no})</div>
                                        <div class="result-subtitle text-muted">Loading books...</div>
                                    `;

                                    fetch(
                                            `{{ route('admin.transactions.issued-books') }}?studentId=${student.id}`)
                                        .then(resp => resp.json())
                                        .then(issuedBooks => {
                                            selectStudentForReturn(student,
                                                issuedBooks);
                                        })
                                        .catch(err => {
                                            console.error(
                                                'Error fetching issued books:',
                                                err);
                                            selectStudentForReturn(student, []);
                                        });
                                });
                            });
                        }

                        returnStudentResults.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        returnStudentResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error loading students</div></div>';
                        returnStudentResults.style.display = 'block';
                    });
            });

            // ========== ISSUE BOOK FUNCTIONS ==========

            window.selectStudentForIssue = function(student) {
                selectedStudent = student;
                selectedStudentId.value = student.id;
                searchStudentInput.value = `${student.name} (${student.roll_no})`;
                studentResults.style.display = 'none';
                document.getElementById('clearStudentBtn').style.display = 'block';

                // Update student details
                document.getElementById('studentName').textContent = student.name;
                document.getElementById('studentID').textContent = student.roll_no;
                document.getElementById('studentDepartment').textContent = student.department;
                document.getElementById('studentEmail').textContent = student.email;
                document.getElementById('studentIssued').textContent =
                    `${student.issued} / ${student.maxBooks}`;

                // Update progress bar
                const progressPercent = (student.issued / student.maxBooks) * 100;
                document.getElementById('issuedProgress').style.width = `${progressPercent}%`;

                selectedStudentCard.style.display = 'block';
                availableBooksCard.style.display = 'block';

                // Reset book selection
                selectedBooks = [];
                updateSelectedBooksList();
                updateIssueButton();

                // Update available books info
                const canIssueMore = student.maxBooks - student.issued;
                document.getElementById('canIssueMore').textContent = canIssueMore;
                document.getElementById('selectedCount').textContent = '0 books';
                document.getElementById('totalBooks').textContent = '0';

                // Enable book search
                searchBookInput.disabled = false;
                searchBookInput.placeholder = "Search books to issue...";
            };

            window.addBookToSelection = function(book) {
                if (!selectedStudent) return;

                // Check if student can issue more books
                const canIssueMore = selectedStudent.maxBooks - selectedStudent.issued;
                if (selectedBooks.length >= canIssueMore) {
                    alert(`Student can only issue ${canIssueMore} more book(s)`);
                    return;
                }

                // Check if book is already selected
                if (selectedBooks.some(b => b.id === book.id)) {
                    alert('This book is already selected');
                    return;
                }

                // Check if book is available
                if (book.available <= 0) {
                    alert('This book is currently unavailable');
                    return;
                }

                selectedBooks.push(book);
                updateSelectedBooksList();
                updateIssueButton();
                updateAvailableBooksInfo();
            };

            window.removeBookFromSelection = function(bookId) {
                selectedBooks = selectedBooks.filter(book => book.id !== bookId);
                updateSelectedBooksList();
                updateIssueButton();
                updateAvailableBooksInfo();
            };

            window.updateSelectedBooksList = function() {
                selectedBooksContainer.innerHTML = '';

                if (selectedBooks.length === 0) {
                    selectedBooksList.style.display = 'none';
                    return;
                }

                selectedBooksList.style.display = 'block';

                selectedBooks.forEach(book => {
                    const bookItem = document.createElement('div');
                    bookItem.className = 'selected-book-item';
                    bookItem.innerHTML = `
                        <div class="book-info">
                            <div class="book-title">${book.title}</div>
                            <div class="book-details">${book.author} • ${book.category}</div>
                        </div>
                        <button type="button" class="remove-book" data-book-id="${book.id}">
                            Remove
                        </button>
                    `;
                    selectedBooksContainer.appendChild(bookItem);
                });

                // Attach remove button handlers
                document.querySelectorAll('.remove-book').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const bookId = parseInt(this.getAttribute('data-book-id'));
                        removeBookFromSelection(bookId);
                    });
                });
            };

            window.updateIssueButton = function() {
                const canIssueMore = selectedStudent ? selectedStudent.maxBooks - selectedStudent.issued : 0;
                const selectedCount = selectedBooks.length;

                if (selectedStudent && selectedCount > 0) {
                    issueButton.disabled = false;
                    issueButton.textContent = `Issue Books (${selectedCount}/${canIssueMore})`;
                } else {
                    issueButton.disabled = true;
                    issueButton.textContent = `Issue Books (0/${canIssueMore})`;
                }
            };

            window.updateAvailableBooksInfo = function() {
                if (!selectedStudent) return;

                const canIssueMore = selectedStudent.maxBooks - selectedStudent.issued;
                const selectedCount = selectedBooks.length;

                document.getElementById('selectedCount').textContent =
                    `${selectedCount} book${selectedCount !== 1 ? 's' : ''}`;
                document.getElementById('totalBooks').textContent = selectedCount;
                document.getElementById('canIssueMore').textContent = canIssueMore - selectedCount;
            };

            window.clearStudentSelection = function() {
                selectedStudent = null;
                selectedStudentId.value = '';
                searchStudentInput.value = '';
                document.getElementById('clearStudentBtn').style.display = 'none';
                selectedStudentCard.style.display = 'none';
                availableBooksCard.style.display = 'none';
                selectedBooks = [];
                updateSelectedBooksList();
                updateIssueButton();

                // Disable book search
                searchBookInput.disabled = true;
                searchBookInput.placeholder = "Select a student first...";
                searchBookInput.value = '';
                bookResults.style.display = 'none';
            };

            // ========== RETURN BOOK FUNCTIONS ==========
            window.selectStudentForReturn = function(student, issuedBooks) {
                selectedReturnStudent = student;
                selectedReturnStudentId.value = student.id;
                searchReturnStudentInput.value = `${student.name} (${student.roll_no})`;
                returnStudentResults.style.display = 'none';
                document.getElementById('clearReturnStudentBtn').style.display = 'block';

                // Update student details
                document.getElementById('returnStudentName').textContent = student.name;
                document.getElementById('returnStudentID').textContent = student.roll_no;
                document.getElementById('returnStudentDepartment').textContent = student.department;
                document.getElementById('returnStudentEmail').textContent = student.email;

                // Display issued books count
                document.getElementById('returnStudentIssued').textContent = issuedBooks.length;

                returnStudentCard.style.display = 'block';
                issuedBooksSection.style.display = 'block';
                fineCalculationCard.style.display = 'block';

                // Display issued books with checkboxes
                displayIssuedBooks(issuedBooks);

                // Reset selections
                selectedIssuedBooks = [];
                selectedCondition = null;
                resetConditionSelection();
                updateReturnButton();
                calculateTotalFine();
            };

            window.displayIssuedBooks = function(issuedBooks) {
                issuedBooksContainer.innerHTML = '';

                if (issuedBooks.length === 0) {
                    issuedBooksContainer.innerHTML =
                        '<div class="py-4 text-center text-secondary">No issued books found</div>';
                    return;
                }

                const today = new Date();

                issuedBooks.forEach(issuedBook => {
                    if (issuedBook.returned) return;

                    const dueDate = new Date(issuedBook.dueDate);
                    const diffTime = today - dueDate;
                    // Prefer server-provided value (keeps consistent with Carbon calculation)
                    // Fallback to client-side calculation using Math.floor to match Carbon's diffInDays behavior
                    const serverOverdue = parseInt(issuedBook.overdueDays);
                    const clientOverdue = Math.max(0, Math.floor(diffTime / (1000 * 60 * 60 * 24)));
                    const overdueDays = Number.isFinite(serverOverdue) ? serverOverdue : clientOverdue;
                    const isOverdue = overdueDays > 0;

                    const bookItem = document.createElement('div');
                    bookItem.className = 'book-checkbox-container';
                    bookItem.innerHTML = `
                        <input type="checkbox" class="book-checkbox" id="book-${issuedBook.id}" 
                               onchange="toggleIssuedBookSelection(${issuedBook.id}, this.checked)"
                               data-overdue-days="${overdueDays}">
                        <div class="book-info-full">
                            <div class="book-title">${issuedBook.bookTitle}</div>
                            <div class="book-meta">
                                <span>${issuedBook.author}</span>
                                <span>Issued: ${new Date(issuedBook.issueDate).toLocaleDateString()}</span>
                                <span>Due: ${dueDate.toLocaleDateString()}</span>
                            </div>
                            <div class="mt-2">
                                ${isOverdue ? 
                                    `<span class="status-badge status-overdue">Overdue by ${overdueDays} days</span>` :
                                    `<span class="status-badge status-ontime">On Time</span>`
                                }
                            </div>
                        </div>
                    `;
                    issuedBooksContainer.appendChild(bookItem);
                });
            }

            window.toggleIssuedBookSelection = function(bookId, isChecked) {
                const checkbox = document.getElementById(`book-${bookId}`);
                const overdueDays = parseInt(checkbox.dataset.overdueDays) || 0;

                if (isChecked) {
                    // Get the book title from the DOM
                    const bookElement = checkbox.closest('.book-checkbox-container');
                    const bookTitle = bookElement.querySelector('.book-title').textContent;

                    selectedIssuedBooks.push({
                        id: bookId,
                        bookId: bookId,
                        title: bookTitle,
                        overdueDays: overdueDays,
                        condition: null,
                        conditionFine: 0
                    });
                } else {
                    selectedIssuedBooks = selectedIssuedBooks.filter(b => b.id !== bookId);
                }

                // Show/hide condition section based on selection
                if (selectedIssuedBooks.length > 0) {
                    bookConditionSection.style.display = 'block';
                } else {
                    bookConditionSection.style.display = 'none';
                    selectedCondition = null;
                    window.resetConditionSelection();
                }

                window.updateReturnButton();
                window.calculateTotalFine();
            };

            window.selectCondition = function(condition) {
                selectedCondition = condition;

                // Update UI
                document.querySelectorAll('.condition-option').forEach(option => {
                    option.classList.remove('selected');
                });
                document.querySelector(`.condition-option[data-condition="${condition}"]`).classList.add(
                    'selected');

                // Update selected condition for all books
                selectedIssuedBooks.forEach(book => {
                    book.condition = condition;
                    book.conditionFine = parseInt(document.querySelector(
                        `.condition-option[data-condition="${condition}"]`).dataset.fine) || 0;
                });

                document.getElementById('selectedCondition').value = condition;
                window.updateReturnButton();
                window.calculateTotalFine();
            };

            window.resetConditionSelection = function() {
                document.querySelectorAll('.condition-option').forEach(option => {
                    option.classList.remove('selected');
                });
                document.getElementById('selectedCondition').value = '';
            };

            window.calculateTotalFine = function() {
                let totalFine = 0;
                fineDetails.innerHTML = '';

                if (selectedIssuedBooks.length === 0 || !selectedCondition) {
                    totalFineElement.textContent = '₹0';
                    return;
                }

                const perDayFine = fineSettings.per_day_fine || 5; // Use from settings or default to 5
                const gracePeriod = fineSettings.grace_period_days || 2; // Use from settings or default to 2
                const maxFine = fineSettings.max_fine_amount || 500; // Use from settings or default to 500

                selectedIssuedBooks.forEach(book => {
                    // Calculate overdue fine with grace period deduction (matching backend logic)
                    let overdueFine = 0;
                    if (book.overdueDays > gracePeriod) {
                        const chargeableDays = book.overdueDays - gracePeriod;
                        overdueFine = chargeableDays * perDayFine;
                        overdueFine = Math.min(overdueFine, maxFine); // Cap at max fine
                    }

                    const conditionFine = book.conditionFine || 0;
                    const bookTotal = overdueFine + conditionFine;
                    totalFine += bookTotal;

                    const fineItem = document.createElement('div');
                    fineItem.className = 'detail-row';
                    fineItem.innerHTML = `
                        <span class="detail-label">${book.title}:</span>
                        <span class="detail-value">₹${bookTotal.toLocaleString()}</span>
                    `;
                    fineDetails.appendChild(fineItem);

                    // Add breakdown if there are fines
                    if (overdueFine > 0 || conditionFine > 0) {
                        const breakdown = document.createElement('div');
                        breakdown.className = 'text-xs text-secondary ml-4';
                        let breakdownText = '';
                        if (overdueFine > 0) {
                            const chargeableDays = Math.max(0, book.overdueDays - gracePeriod);
                            breakdownText +=
                                `Overdue (${chargeableDays} chargeable days × ₹${perDayFine}) = ₹${overdueFine}`;
                        }
                        if (conditionFine > 0) {
                            if (breakdownText) breakdownText += '<br>';
                            breakdownText += `Condition (${book.condition}) = ₹${conditionFine}`;
                        }
                        breakdown.innerHTML = breakdownText;
                        fineDetails.appendChild(breakdown);
                    }
                });

                totalFineElement.textContent = `₹${totalFine.toLocaleString()}`;
            };

            window.updateReturnButton = function() {
                if (selectedIssuedBooks.length > 0 && selectedCondition) {
                    returnButton.disabled = false;
                    returnButton.textContent =
                        `Process Return (${selectedIssuedBooks.length} book${selectedIssuedBooks.length !== 1 ? 's' : ''})`;
                } else {
                    returnButton.disabled = true;
                    returnButton.textContent = 'Process Return';
                }
            };

            window.clearReturnStudentSelection = function() {
                selectedReturnStudent = null;
                selectedReturnStudentId.value = '';
                document.getElementById('clearReturnStudentBtn').style.display = 'none';
                returnStudentCard.style.display = 'none';
                issuedBooksSection.style.display = 'none';
                bookConditionSection.style.display = 'none';
                fineCalculationCard.style.display = 'none';
                selectedIssuedBooks = [];
                selectedCondition = null;
                resetConditionSelection();
                updateReturnButton();

                searchReturnStudentInput.value = '';
                returnStudentResults.style.display = 'none';
            };

            // ========== FORM SUBMISSION ==========

            // Issue Book Form Submit
            issueForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!selectedStudent || selectedBooks.length === 0) {
                    alert('Please select a student and at least one book');
                    return;
                }

                // Check if student can issue more books
                const canIssueMore = selectedStudent.maxBooks - selectedStudent.issued;
                if (selectedBooks.length > canIssueMore) {
                    alert(`Student can only issue ${canIssueMore} more book(s)`);
                    return;
                }

                // Submit to backend
                const bookIds = selectedBooks.map(book => book.id);

                fetch('{{ route('admin.transactions.issue') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: JSON.stringify({
                            student_id: selectedStudent.id,
                            book_ids: bookIds,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            // Reset form
                            issueForm.reset();
                            clearStudentSelection();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error issuing books: ' + error.message);
                    });
            });

            // Return Book Form Submit
            returnForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!selectedReturnStudent || selectedIssuedBooks.length === 0 || !selectedCondition) {
                    alert('Please select a student, at least one book, and condition');
                    return;
                }

                // Submit to backend
                const issuedBookIds = selectedIssuedBooks.map(book => book.id);

                fetch('{{ route('admin.transactions.return') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: JSON.stringify({
                            student_id: selectedReturnStudent.id,
                            issued_book_ids: issuedBookIds,
                            condition: selectedCondition,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message +
                                `\n\nTotal Fine: ₹${data.total_fine.toLocaleString()}`);

                            // Notify any open ViewStudent page to refresh fines
                            if (window.opener && !window.opener.closed) {
                                window.opener.postMessage({
                                    type: 'fines_updated',
                                    studentId: selectedReturnStudent.id
                                }, window.location.origin);
                            }

                            // Reset form
                            returnForm.reset();
                            clearReturnStudentSelection();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error returning books: ' + error.message);
                    });
            });

            // Listen for fine update messages from other windows
            window.addEventListener('message', function(event) {
                if (event.origin !== window.location.origin) return;
                if (event.data.type === 'fines_updated' && event.data.studentId) {
                    // Reload fines if this page is viewing that student
                    if (typeof loadStudentFines === 'function') {
                        loadStudentFines();
                    }
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container')) {
                    studentResults.style.display = 'none';
                    bookResults.style.display = 'none';
                    returnStudentResults.style.display = 'none';
                }
            });
        });
    </script>
@endpush
