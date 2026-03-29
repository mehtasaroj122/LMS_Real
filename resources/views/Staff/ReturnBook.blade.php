@extends('Staff.layouts.app')

@section('title', 'Return Book')

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

        .result-item.disabled {
            opacity: 0.6;
            cursor: not-allowed;
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

        /* Clear Button */
        .clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            padding: 4px 12px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            white-space: nowrap;
        }

        body.dark-theme .clear-btn {
            background-color: #334155;
            border-color: #475569;
            color: #cbd5e1;
        }

        body.dark-theme .clear-btn:hover {
            background-color: #475569;
        }

        /* Book Checkbox Container */
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

        .book-title {
            font-weight: 600;
            font-size: 14px;
            color: #111827;
            margin-bottom: 4px;
        }

        body.dark-theme .book-title {
            color: #f3f4f6;
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
            display: inline-block;
            margin-top: 4px;
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
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
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

        /* Info Box */
        .info-box {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 12px;
            margin-top: 16px;
        }

        body.dark-theme .info-box {
            background-color: #1e3a8a;
        }

        /* Card Styles */
        .card {
            border-radius: 0.75rem;
            padding: 1.5rem;
        }

        body.light-theme .card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        /* Layout */
        .grid-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 1024px) {
            .grid-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Section Headers */
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        body.dark-theme .section-title {
            color: #f1f5f9;
        }

        .section-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        body.dark-theme .section-subtitle {
            color: #94a3b8;
        }

        .section-header {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 1rem;
        }

        body.dark-theme .section-header {
            color: #e2e8f0;
        }

        /* Scrollable Container */
        .scrollable-container {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 4px;
            margin-top: 8px;
        }

        /* Total Fine Display */
        .total-fine {
            font-size: 1.5rem;
            font-weight: 700;
            color: #dc2626;
            text-align: right;
        }

        body.dark-theme .total-fine {
            color: #f87171;
        }

        /* Fine Settings Cards */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .settings-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .setting-card {
            text-align: center;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        body.light-theme .setting-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .setting-card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        .setting-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.25rem;
        }

        body.dark-theme .setting-value {
            color: #60a5fa;
        }

        .setting-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme .setting-label {
            color: #94a3b8;
        }

        .detail-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 4px;
        }

        .detail-meta {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
        }

        body.dark-theme .detail-meta {
            color: #94a3b8;
        }

        .policy-note,
        .return-total-summary {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(14, 165, 233, 0.12));
            border: 1px solid rgba(37, 99, 235, 0.16);
            border-radius: 14px;
            padding: 14px 16px;
        }

        body.dark-theme .policy-note,
        body.dark-theme .return-total-summary {
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.35), rgba(14, 116, 144, 0.28));
            border-color: rgba(96, 165, 250, 0.25);
        }

        .return-total-summary {
            margin-top: 14px;
        }

        .return-total-summary-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 16px;
        }

        .return-total-summary-label {
            font-size: 13px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .return-total-summary-note {
            margin: 10px 0 0;
            font-size: 12px;
            color: #475569;
        }

        body.dark-theme .return-total-summary-label {
            color: #bfdbfe;
        }

        body.dark-theme .return-total-summary-note {
            color: #cbd5e1;
        }

        .return-student-shell {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .return-student-header,
        .return-student-heading {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
        }

        .return-student-heading {
            justify-content: flex-start;
        }

        .return-student-heading-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .return-student-subtitle,
        .return-rules-caption {
            margin: 0;
            font-size: 12px;
            color: #64748b;
        }

        body.dark-theme .return-student-subtitle,
        body.dark-theme .return-rules-caption {
            color: #94a3b8;
        }

        .return-issued-chip {
            min-width: 120px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 12px 14px;
            text-align: center;
        }

        body.dark-theme .return-issued-chip {
            background: rgba(30, 64, 175, 0.25);
            border-color: rgba(96, 165, 250, 0.3);
        }

        .return-issued-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .return-issued-value {
            display: block;
            margin-top: 6px;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        body.dark-theme .return-issued-label {
            color: #93c5fd;
        }

        body.dark-theme .return-issued-value {
            color: #f8fafc;
        }

        .return-student-meta-grid,
        .return-rules-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .return-meta-card,
        .return-rule-card {
            border-radius: 14px;
            padding: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .return-meta-card,
        body.dark-theme .return-rule-card {
            background: #0f172a;
            border-color: #334155;
        }

        .return-meta-label,
        .return-rule-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
            margin-bottom: 6px;
        }

        .return-meta-value,
        .return-rule-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        body.dark-theme .return-meta-label,
        body.dark-theme .return-rule-label {
            color: #94a3b8;
        }

        body.dark-theme .return-meta-value,
        body.dark-theme .return-rule-value {
            color: #f8fafc;
        }

        .return-rules-section {
            display: flex;
            flex-direction: column;
            gap: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 18px;
        }

        body.dark-theme .return-rules-section {
            border-top-color: #334155;
        }

        .return-rules-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .fine-breakdown-card {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        body.dark-theme .fine-breakdown-card {
            border-color: #334155;
            background: #0f172a;
        }

        .fine-breakdown-header,
        .fine-breakdown-meta,
        .fine-breakdown-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .fine-breakdown-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        body.dark-theme .fine-breakdown-title {
            color: #f8fafc;
        }

        .fine-chip {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            background: #e0f2fe;
            color: #0c4a6e;
            font-size: 11px;
            font-weight: 700;
        }

        body.dark-theme .fine-chip {
            background: rgba(14, 116, 144, 0.35);
            color: #bae6fd;
        }

        .fine-breakdown-lines {
            display: grid;
            gap: 8px;
        }

        .fine-breakdown-line {
            font-size: 13px;
            color: #475569;
        }

        body.dark-theme .fine-breakdown-line {
            color: #cbd5e1;
        }

        @media (max-width: 767px) {
            .return-student-header,
            .return-student-heading,
            .return-rules-heading,
            .return-total-summary-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .return-student-meta-grid,
            .return-rules-grid {
                grid-template-columns: 1fr;
            }

            .book-meta {
                flex-direction: column;
                gap: 4px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="p-6">
        <div>
            <h1 class="section-title">Return Book</h1>
            <p class="section-subtitle">Process book returns and calculate fines</p>

            <!-- Fine Settings -->
            <div class="settings-grid">
                <div class="setting-card">
                    <div class="setting-value">{{ $fineSettings->issue_duration_days ?? 14 }} days</div>
                    <div class="setting-label">Issue Duration</div>
                </div>
                <div class="setting-card">
                    <div class="setting-value">₹{{ $fineSettings->per_day_fine ?? 5 }}/day</div>
                    <div class="setting-label">Late Fine</div>
                </div>
                <div class="setting-card">
                    <div class="setting-value">₹{{ $fineSettings->lost_book_penalty ?? 500 }}</div>
                    <div class="setting-label">Lost Book Fine</div>
                </div>
                <div class="setting-card">
                    <div class="setting-value">₹{{ $fineSettings->damaged_book_penalty ?? 200 }}</div>
                    <div class="setting-label">Damaged Book Fine</div>
                </div>
            </div>

            <div class="grid-container">
                <!-- Return Form -->
                <div class="card">
                    <h2 class="section-header">Return Form</h2>

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
                                    onclick="clearReturnStudentSelection()" style="display: none;">Clear</button>
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
                                    data-fine="{{ $fineSettings->fair_condition_penalty ?? 50 }}"
                                    onclick="selectCondition('fair')">
                                    <div class="condition-label">Fair</div>
                                    <div class="condition-fine">₹{{ $fineSettings->fair_condition_penalty ?? 50 }} Fine
                                    </div>
                                </div>
                                <div class="condition-option" data-condition="damaged"
                                    data-fine="{{ $fineSettings->damaged_book_penalty ?? 200 }}"
                                    onclick="selectCondition('damaged')">
                                    <div class="condition-label">Damaged</div>
                                    <div class="condition-fine">₹{{ $fineSettings->damaged_book_penalty ?? 200 }} Fine</div>
                                </div>
                                <div class="condition-option" data-condition="lost"
                                    data-fine="{{ $fineSettings->lost_book_penalty ?? 500 }}"
                                    onclick="selectCondition('lost')">
                                    <div class="condition-label">Lost</div>
                                    <div class="condition-fine">₹{{ $fineSettings->lost_book_penalty ?? 500 }} Fine</div>
                                </div>
                            </div>
                            <input type="hidden" id="selectedCondition" value="">
                        </div>

                        <!-- Process Return Button -->
                        <button type="submit" class="btn-primary" id="returnButton" disabled>
                            Process Return
                        </button>

                        <div class="return-total-summary" id="returnTotalSummary" aria-live="polite">
                            <div class="return-total-summary-row">
                                <span class="return-total-summary-label">Total Fine</span>
                                <span class="total-fine" id="returnFormTotalFine">₹0</span>
                            </div>
                            <p class="return-total-summary-note" id="returnTotalSummaryNote">
                                Select books and a return condition to preview the payable fine.
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Return Details -->
                <div class="space-y-6">
                    <!-- Student Information -->
                    <div class="card" id="returnStudentCard" style="display: none;">
                        <div class="return-student-shell">
                            <div class="return-student-header">
                                <div class="return-student-heading">
                                    <svg class="w-5 h-5 mt-0.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div class="return-student-heading-copy">
                                        <h3 class="text-base font-semibold text-primary">Student Information</h3>
                                        <p class="return-student-subtitle">
                                            Quick borrower summary with the effective return rules used for fine calculation.
                                        </p>
                                    </div>
                                </div>

                                <div class="return-issued-chip">
                                    <span class="return-issued-label">Books Issued</span>
                                    <span class="return-issued-value" id="returnStudentIssued">0</span>
                                </div>
                            </div>

                            <div class="return-student-meta-grid">
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Name</div>
                                    <div class="return-meta-value" id="returnStudentName">-</div>
                                </div>
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Roll No</div>
                                    <div class="return-meta-value" id="returnStudentID">-</div>
                                </div>
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Department</div>
                                    <div class="return-meta-value" id="returnStudentDepartment">-</div>
                                </div>
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Email</div>
                                    <div class="return-meta-value" id="returnStudentEmail">-</div>
                                </div>
                            </div>

                            <div class="return-rules-section">
                                <div class="return-rules-heading">
                                    <h4 class="text-sm font-semibold text-primary">Effective Return Rules</h4>
                                    <span class="return-rules-caption">Compact policy view for the selected borrower</span>
                                </div>

                                <div class="return-rules-grid">
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Return Condition</div>
                                        <div class="return-rule-value" id="returnConditionSummary">Not selected</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Issue Duration Policy</div>
                                        <div class="return-rule-value" id="returnIssueDurationRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Late Fine Rule</div>
                                        <div class="return-rule-value" id="returnLateFineRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Grace Period</div>
                                        <div class="return-rule-value" id="returnGraceRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Fine Cap Per Book</div>
                                        <div class="return-rule-value" id="returnMaxFineRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Condition Fine</div>
                                        <div class="return-rule-value" id="returnConditionFineRule">-</div>
                                    </div>
                                </div>

                                <div class="policy-note" id="returnPrivilegeNote">
                                    Return fine calculations use the student's effective rules first, then the active default library settings when no override exists.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fine Calculation -->
                    <div class="card" id="fineCalculationCard" style="display: none;">
                        <h3 class="mb-4 text-base font-semibold text-primary">Fine Calculation</h3>

                        <div class="mb-4 policy-note" id="finePolicySummary">
                            Select at least one issued book and a return condition to preview the fine calculation.
                        </div>

                        <div class="mb-4 space-y-3" id="fineDetails">
                            <!-- Fine details will be populated here -->
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="detail-row">
                                <span class="text-lg font-bold text-primary">Total Fine:</span>
                                <span class="total-fine" id="totalFine">₹0</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm" id="fineHelperText">Select books and condition to calculate total fine</p>
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
        const fineSettings = @json($fineSettings);

        // Return Book Variables
        let selectedReturnStudent = null;
        let selectedReturnStudentPrivileges = null;
        let selectedIssuedBooks = [];
        let selectedCondition = null;
        let returnStudentSearchRequest = 0;
        let selectedReturnStudentId;
        let searchReturnStudentInput;
        let returnStudentResults;
        let returnStudentCard;
        let issuedBooksSection;
        let issuedBooksContainer;
        let bookConditionSection;
        let returnButton;
        let returnForm;
        let fineCalculationCard;
        let fineDetails;
        let totalFineElement;
        let returnFormTotalFineElement;
        let returnTotalSummaryNote;
        let finePolicySummary;
        let fineHelperText;
        let clearReturnStudentBtn;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatCurrency(amount, minimumFractionDigits = 2) {
            const number = Number(amount ?? 0);
            return `₹${number.toLocaleString(undefined, {
                minimumFractionDigits,
                maximumFractionDigits: 2
            })}`;
        }

        function formatDate(dateValue) {
            if (!dateValue) return 'N/A';
            const date = new Date(dateValue);
            if (Number.isNaN(date.getTime())) return 'N/A';
            return date.toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        function toTitleCase(value) {
            if (!value) return 'Not selected';
            return String(value)
                .replace(/_/g, ' ')
                .replace(/\b\w/g, char => char.toUpperCase());
        }

        function createPrivilegeState(data = null) {
            const rawPrivileges = {
                ...(data?.privileges ?? {})
            };
            const defaults = {
                issue_duration_days: data?.defaults?.issue_duration_days ?? fineSettings.issue_duration_days ?? 14,
                per_day_fine: data?.defaults?.per_day_fine ?? fineSettings.per_day_fine ?? 5,
                grace_period_days: data?.defaults?.grace_period_days ?? fineSettings.grace_period_days ?? 2,
                max_fine_amount: data?.defaults?.max_fine_amount ?? fineSettings.max_fine_amount ?? 500,
            };
            const effective = {
                ...defaults,
                ...(data?.effective ?? {})
            };

            return {
                raw: rawPrivileges,
                defaults,
                effective,
            };
        }

        function getReturnRules() {
            return selectedReturnStudentPrivileges?.effective ?? null;
        }

        function getConditionFine(condition) {
            if (!condition) return 0;
            const option = document.querySelector(`.condition-option[data-condition="${condition}"]`);
            return Number(option?.dataset.fine ?? 0);
        }

        function setDetailValue(elementId, primary, secondary = '') {
            const element = document.getElementById(elementId);
            if (!element) return;

            if (!secondary) {
                element.textContent = primary;
                return;
            }

            element.innerHTML = `
                <span class="detail-stack">
                    <span>${escapeHtml(primary)}</span>
                    <span class="detail-meta">${escapeHtml(secondary)}</span>
                </span>
            `;
        }

        function setReturnTotalFineDisplay(amount, note) {
            const formatted = formatCurrency(amount);
            totalFineElement.textContent = formatted;
            if (returnFormTotalFineElement) {
                returnFormTotalFineElement.textContent = formatted;
            }
            if (returnTotalSummaryNote && note !== undefined) {
                returnTotalSummaryNote.textContent = note;
            }
        }

        function renderReturnPrivilegeSummary() {
            const rules = getReturnRules();

            if (!selectedReturnStudent || !rules) {
                resetReturnPrivilegeSummary();
                return;
            }

            setDetailValue(
                'returnConditionSummary',
                toTitleCase(selectedCondition),
                selectedCondition ? 'This condition fine is applied to each selected book.' : 'Select a condition to preview the per-book penalty.'
            );
            setDetailValue(
                'returnIssueDurationRule',
                `${rules.issue_duration_days} days`,
                selectedReturnStudentPrivileges?.raw?.issue_duration_days !== null &&
                selectedReturnStudentPrivileges?.raw?.issue_duration_days !== undefined ? 'Student override' : 'Global default'
            );
            setDetailValue(
                'returnLateFineRule',
                `${formatCurrency(rules.per_day_fine, 2)}/day after grace`,
                selectedReturnStudentPrivileges?.raw?.per_day_fine !== null &&
                selectedReturnStudentPrivileges?.raw?.per_day_fine !== undefined ? 'Student override' : 'Global default'
            );
            setDetailValue(
                'returnGraceRule',
                `${rules.grace_period_days} day${Number(rules.grace_period_days) === 1 ? '' : 's'} grace`,
                selectedReturnStudentPrivileges?.raw?.grace_period_days !== null &&
                selectedReturnStudentPrivileges?.raw?.grace_period_days !== undefined ? 'Student override' : 'Global default'
            );
            setDetailValue(
                'returnMaxFineRule',
                `${formatCurrency(rules.max_fine_amount, 2)} per book`,
                selectedReturnStudentPrivileges?.raw?.max_fine_amount !== null &&
                selectedReturnStudentPrivileges?.raw?.max_fine_amount !== undefined ? 'Student override' : 'Global default'
            );
            setDetailValue(
                'returnConditionFineRule',
                formatCurrency(getConditionFine(selectedCondition), 2),
                selectedCondition ? `${toTitleCase(selectedCondition)} condition` : 'Select a condition first'
            );

            const note = document.getElementById('returnPrivilegeNote');
            if (note) {
                note.textContent = selectedReturnStudentPrivileges?.raw &&
                    Object.keys(selectedReturnStudentPrivileges.raw).some(key => selectedReturnStudentPrivileges.raw[key] !== null && selectedReturnStudentPrivileges.raw[key] !== undefined)
                    ? 'This borrower has custom privilege rules, so return fines are using the override values shown above where applicable.'
                    : 'Return fine calculations use the active global library settings because this borrower does not have custom overrides.';
            }
        }

        function resetReturnPrivilegeSummary() {
            [
                'returnConditionSummary',
                'returnIssueDurationRule',
                'returnLateFineRule',
                'returnGraceRule',
                'returnMaxFineRule',
                'returnConditionFineRule'
            ].forEach(id => setDetailValue(id, id === 'returnConditionSummary' ? 'Not selected' : '-'));

            const note = document.getElementById('returnPrivilegeNote');
            if (note) {
                note.textContent = 'Return fine calculations use the student\'s effective rules first, then the active default library settings when no override exists.';
            }

            if (finePolicySummary) {
                finePolicySummary.textContent = 'Select at least one issued book and a return condition to preview the fine calculation.';
            }
            if (fineHelperText) {
                fineHelperText.textContent = 'Select books and condition to calculate total fine';
            }
            setReturnTotalFineDisplay(0, 'Select books and a return condition to preview the payable fine.');
        }

        // Custom Alert Function
        window.showCustomAlert = function(title, message, type = 'info') {
            const alertBox = document.createElement('div');
            alertBox.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 24px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                z-index: 9999;
                min-width: 400px;
                max-width: 500px;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            `;

            if (document.body.classList.contains('dark-theme')) {
                alertBox.style.background = '#1e293b';
                alertBox.style.color = '#f1f5f9';
            }

            let icon = '✓';
            let borderColor = '#10b981';
            let titleColor = '#059669';

            if (type === 'error') {
                icon = '✕';
                borderColor = '#ef4444';
                titleColor = '#dc2626';
            } else if (type === 'warning') {
                icon = '⚠';
                borderColor = '#f59e0b';
                titleColor = '#d97706';
            }

            alertBox.innerHTML = `
                <div style="border-left: 4px solid ${borderColor}; padding-left: 16px;">
                    <div style="font-size: 18px; font-weight: 700; color: ${titleColor}; margin-bottom: 8px;">
                        ${icon} ${title}
                    </div>
                    <div style="font-size: 14px; color: #64748b; line-height: 1.6; white-space: pre-wrap;">
                        ${message}
                    </div>
                    <button style="margin-top: 16px; padding: 8px 16px; background: ${borderColor}; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;" onclick="this.closest('div').parentElement.remove(); document.querySelector('div[style*=\"background: rgba\"]')?.remove();">OK</button>
                </div>
            `;

            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 9998;
            `;

            document.body.appendChild(overlay);
            document.body.appendChild(alertBox);

            alertBox.querySelector('button').onclick = () => {
                alertBox.remove();
                overlay.remove();
            };
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Return Book Elements
            searchReturnStudentInput = document.getElementById('searchReturnStudent');
            returnStudentResults = document.getElementById('returnStudentResults');
            selectedReturnStudentId = document.getElementById('selectedReturnStudentId');
            returnStudentCard = document.getElementById('returnStudentCard');
            issuedBooksSection = document.getElementById('issuedBooksSection');
            issuedBooksContainer = document.getElementById('issuedBooksContainer');
            bookConditionSection = document.getElementById('bookConditionSection');
            returnButton = document.getElementById('returnButton');
            returnForm = document.getElementById('returnForm');
            fineCalculationCard = document.getElementById('fineCalculationCard');
            fineDetails = document.getElementById('fineDetails');
            totalFineElement = document.getElementById('totalFine');
            returnFormTotalFineElement = document.getElementById('returnFormTotalFine');
            returnTotalSummaryNote = document.getElementById('returnTotalSummaryNote');
            finePolicySummary = document.getElementById('finePolicySummary');
            fineHelperText = document.getElementById('fineHelperText');
            clearReturnStudentBtn = document.getElementById('clearReturnStudentBtn');

            // Search Students for Return
            searchReturnStudentInput.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                const requestId = ++returnStudentSearchRequest;
                returnStudentResults.innerHTML = '';

                if (query.length < 2) {
                    returnStudentResults.style.display = 'none';
                    return;
                }

                returnStudentResults.innerHTML =
                    '<div class="result-item"><div class="result-title">Searching...</div></div>';
                returnStudentResults.style.display = 'block';

                fetch(
                        `{{ route('staff.transactions.students') }}?query=${encodeURIComponent(query)}`,
                        { credentials: 'include' }
                    )
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.json();
                    })
                    .then(students => {
                        if (requestId !== returnStudentSearchRequest) {
                            return;
                        }

                        const uniqueStudents = [...new Map((students || []).map(student => [student.id, student])).values()];

                        if (uniqueStudents.length === 0) {
                            returnStudentResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No students found</div></div>';
                        } else {
                            returnStudentResults.innerHTML = '';

                            const studentItems = new Map();

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

                            uniqueStudents.forEach(student => {
                                fetch(
                                        `{{ route('staff.transactions.issued-books') }}?studentId=${student.id}&countOnly=1`,
                                        { credentials: 'include' }
                                    )
                                    .then(resp => {
                                        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                                        return resp.json();
                                    })
                                    .then(data => {
                                        const count = data.count || 0;
                                        const {
                                            item
                                        } = studentItems.get(student.id);
                                        const bookCountSpan = item
                                            .querySelector(
                                                '.book-count');

                                        if (bookCountSpan) {
                                            bookCountSpan.textContent = count;

                                            // Disable if no books
                                            if (count === 0) {
                                                item.style.opacity = '0.6';
                                                item.style.cursor =
                                                    'not-allowed';
                                                item.style.pointerEvents =
                                                    'none';
                                            }
                                        }
                                    })
                                    .catch(err => {
                                        console.error(
                                            'Error fetching book count:',
                                            err);
                                        const {
                                            item
                                        } = studentItems.get(student.id);
                                        const bookCountSpan = item
                                            .querySelector(
                                                '.book-count');
                                        if (bookCountSpan) bookCountSpan
                                            .textContent = '0';
                                    });
                            });

                            uniqueStudents.forEach(student => {
                                const {
                                    item
                                } = studentItems.get(student.id);
                                item.addEventListener('click', function() {
                                    console.log(
                                        'Fetching issued books for student:',
                                        student.id);
                                    item.innerHTML = `
                                    <div class="result-title">${student.name} (${student.roll_no})</div>
                                    <div class="result-subtitle text-muted">Loading books...</div>
                                `;

                                    fetch(
                                            `{{ route('staff.transactions.issued-books') }}?studentId=${student.id}`,
                                            { credentials: 'include' }
                                        )
                                        .then(resp => {
                                            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                                            return resp.json();
                                        })
                                        .then(issuedBooks => {
                                            selectStudentForReturn(
                                                student,
                                                issuedBooks);
                                        })
                                        .catch(err => {
                                            console.error(
                                                'Error fetching issued books:',
                                                err);
                                            selectStudentForReturn(
                                                student, []);
                                        });
                                });
                            });
                        }

                        returnStudentResults.style.display = 'block';
                    })
                    .catch(error => {
                        if (requestId !== returnStudentSearchRequest) {
                            return;
                        }

                        console.error('Error:', error);
                        returnStudentResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error: ' + error.message + '</div></div>';
                        returnStudentResults.style.display = 'block';
                    });
            });

            // Return Book Form Submit
            returnForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!selectedReturnStudent || selectedIssuedBooks.length === 0 || !selectedCondition) {
                    showCustomAlert('Missing Information', `Please select:
• A student
• At least one book to return
• Book condition`, 'warning');
                    return;
                }

                // Submit to backend
                const issuedBookIds = selectedIssuedBooks.map(book => book.id);

                fetch('{{ route('staff.transactions.return') }}', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
                            const totalFine = data.total_fine || 0;
                            const message = data.message + (totalFine > 0 ?
                                `\n\nTotal Fine: ₹${totalFine.toLocaleString()}` : '');
                            showCustomAlert('Books Returned Successfully', message, 'success');
                            clearReturnStudentSelection();
                        } else {
                            const isFineError = data.message.toLowerCase().includes('fine') || 
                                              data.message.toLowerCase().includes('overdue');
                            showCustomAlert(isFineError ? 'Fine Notice' : 'Return Failed', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showCustomAlert('Transaction Error', 'Failed to return books: ' + error.message, 'error');
                    });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container')) {
                    returnStudentResults.style.display = 'none';
                }
            });

            resetReturnPrivilegeSummary();
        });

        function selectStudentForReturn(student, issuedBooks) {
            selectedReturnStudent = student;
            selectedReturnStudentId.value = student.id;
            searchReturnStudentInput.value = `${student.name} (${student.roll_no})`;
            returnStudentResults.style.display = 'none';
            clearReturnStudentBtn.style.display = 'block';

            fetch(`/admin/students/${student.id}/privileges`, { credentials: 'include' })
                .then(response => response.json())
                .then(data => {
                    selectedReturnStudentPrivileges = data.success ? createPrivilegeState(data) : createPrivilegeState();

                    document.getElementById('returnStudentName').textContent = student.name;
                    document.getElementById('returnStudentID').textContent = student.roll_no;
                    document.getElementById('returnStudentDepartment').textContent = student.department;
                    document.getElementById('returnStudentEmail').textContent = student.email;
                    document.getElementById('returnStudentIssued').textContent = issuedBooks.length;

                    returnStudentCard.style.display = 'block';
                    issuedBooksSection.style.display = 'block';
                    fineCalculationCard.style.display = 'block';

                    displayIssuedBooks(issuedBooks);

                    selectedIssuedBooks = [];
                    selectedCondition = null;
                    resetConditionSelection();
                    renderReturnPrivilegeSummary();
                    updateReturnButton();
                    calculateTotalFine();
                })
                .catch(error => {
                    console.error('Error:', error);
                    selectedReturnStudentPrivileges = createPrivilegeState();
                    document.getElementById('returnStudentName').textContent = student.name;
                    document.getElementById('returnStudentID').textContent = student.roll_no;
                    document.getElementById('returnStudentDepartment').textContent = student.department;
                    document.getElementById('returnStudentEmail').textContent = student.email;
                    document.getElementById('returnStudentIssued').textContent = issuedBooks.length;
                    returnStudentCard.style.display = 'block';
                    issuedBooksSection.style.display = 'block';
                    fineCalculationCard.style.display = 'block';
                    displayIssuedBooks(issuedBooks);
                    selectedIssuedBooks = [];
                    selectedCondition = null;
                    resetConditionSelection();
                    renderReturnPrivilegeSummary();
                    updateReturnButton();
                    calculateTotalFine();
                });
        }

        function displayIssuedBooks(issuedBooks) {
            issuedBooksContainer.innerHTML = '';

            if (issuedBooks.length === 0) {
                issuedBooksContainer.innerHTML = '<div class="py-4 text-center text-secondary">No issued books found</div>';
                return;
            }

            const today = new Date();

            issuedBooks.forEach(issuedBook => {
                if (issuedBook.returned) return;

                const dueDateValue = issuedBook.dueDate || issuedBook.due_date;
                const issueDateValue = issuedBook.issueDate || issuedBook.issue_date;
                const dueDate = new Date(`${dueDateValue}T00:00:00`);
                const diffTime = today - dueDate;
                const serverOverdue = parseInt(issuedBook.overdueDays ?? issuedBook.overdue_days);
                const clientOverdue = Math.max(0, Math.floor(diffTime / (1000 * 60 * 60 * 24)));
                const overdueDays = Number.isFinite(serverOverdue) ? serverOverdue : clientOverdue;
                const isOverdue = overdueDays > 0;

                const bookItem = document.createElement('div');
                bookItem.className = 'book-checkbox-container';
                bookItem.innerHTML = `
                <input type="checkbox" class="book-checkbox" id="book-${issuedBook.id}"
                       onchange="toggleIssuedBookSelection(${issuedBook.id}, this.checked)"
                       data-overdue-days="${overdueDays}"
                       data-issue-date="${issueDateValue}"
                       data-due-date="${dueDateValue}">
                <div class="book-info-full">
                    <div class="book-title">${issuedBook.bookTitle || issuedBook.book_title || issuedBook.title}</div>
                    <div class="book-meta">
                        <span>${issuedBook.author}</span>
                        <span>Issued: ${formatDate(issueDateValue)}</span>
                        <span>Due: ${formatDate(dueDateValue)}</span>
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

        function toggleIssuedBookSelection(bookId, isChecked) {
            const checkbox = document.getElementById(`book-${bookId}`);
            const overdueDays = parseInt(checkbox.dataset.overdueDays) || 0;

            if (isChecked) {
                const bookElement = checkbox.closest('.book-checkbox-container');
                const bookTitle = bookElement.querySelector('.book-title').textContent;

                selectedIssuedBooks.push({
                    id: bookId,
                    bookId: bookId,
                    title: bookTitle,
                    overdueDays: overdueDays,
                    issueDate: checkbox.dataset.issueDate,
                    dueDate: checkbox.dataset.dueDate,
                    condition: null,
                    conditionFine: 0
                });
            } else {
                selectedIssuedBooks = selectedIssuedBooks.filter(b => b.id !== bookId);
            }

            if (selectedIssuedBooks.length > 0) {
                bookConditionSection.style.display = 'block';
            } else {
                bookConditionSection.style.display = 'none';
                selectedCondition = null;
                resetConditionSelection();
            }

            renderReturnPrivilegeSummary();
            updateReturnButton();
            calculateTotalFine();
        }

        function selectCondition(condition) {
            selectedCondition = condition;

            document.querySelectorAll('.condition-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`.condition-option[data-condition="${condition}"]`).classList.add('selected');

            const conditionFine = getConditionFine(condition);

            selectedIssuedBooks.forEach(book => {
                book.condition = condition;
                book.conditionFine = conditionFine;
            });

            document.getElementById('selectedCondition').value = condition;
            renderReturnPrivilegeSummary();
            updateReturnButton();
            calculateTotalFine();
        }

        function resetConditionSelection() {
            document.querySelectorAll('.condition-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.getElementById('selectedCondition').value = '';
            renderReturnPrivilegeSummary();
        }

        function calculateTotalFine() {
            let totalFine = 0;
            fineDetails.innerHTML = '';

            if (selectedIssuedBooks.length === 0 || !selectedCondition) {
                setReturnTotalFineDisplay(0, 'Select books and a return condition to preview the payable fine.');
                finePolicySummary.textContent =
                    'Select at least one issued book and a return condition to preview the fine calculation.';
                fineHelperText.textContent = 'Select books and condition to calculate total fine';
                return;
            }

            const rules = getReturnRules();
            const perDayFine = rules?.per_day_fine ?? fineSettings.per_day_fine ?? 5;
            const gracePeriod = rules?.grace_period_days ?? fineSettings.grace_period_days ?? 2;
            const maxFine = rules?.max_fine_amount ?? fineSettings.max_fine_amount ?? 500;
            const conditionFine = getConditionFine(selectedCondition);

            finePolicySummary.innerHTML = `
                <strong>${selectedIssuedBooks.length}</strong> selected book${selectedIssuedBooks.length === 1 ? '' : 's'}
                will be charged using <strong>${formatCurrency(perDayFine)}/day</strong> after
                <strong>${gracePeriod}</strong> grace day${Number(gracePeriod) === 1 ? '' : 's'},
                capped at <strong>${formatCurrency(maxFine)}</strong> per book, plus
                <strong>${formatCurrency(conditionFine)}</strong> for the
                <strong>${escapeHtml(toTitleCase(selectedCondition))}</strong> condition.
            `;
            fineHelperText.textContent =
                'Each selected book is calculated separately using overdue days, grace period, late fine rate, fine cap, and the chosen condition penalty.';

            selectedIssuedBooks.forEach(book => {
                let overdueFine = 0;
                const chargeableDays = Math.max(0, book.overdueDays - gracePeriod);
                const capped = book.overdueDays > gracePeriod && (chargeableDays * perDayFine) > maxFine;
                if (book.overdueDays > gracePeriod) {
                    overdueFine = chargeableDays * perDayFine;
                    overdueFine = Math.min(overdueFine, maxFine);
                }

                const perBookConditionFine = book.conditionFine || 0;
                const bookTotal = overdueFine + perBookConditionFine;
                totalFine += bookTotal;

                const fineItem = document.createElement('div');
                fineItem.className = 'fine-breakdown-card';
                fineItem.innerHTML = `
                    <div class="fine-breakdown-header">
                        <div class="fine-breakdown-title">${escapeHtml(book.title)}</div>
                        <div class="detail-value">${formatCurrency(bookTotal)}</div>
                    </div>
                    <div class="fine-breakdown-meta">
                        <span class="fine-chip">Issued ${escapeHtml(formatDate(book.issueDate))}</span>
                        <span class="fine-chip">Due ${escapeHtml(formatDate(book.dueDate))}</span>
                        <span class="fine-chip">${book.overdueDays > 0 ? `${book.overdueDays} overdue day${book.overdueDays === 1 ? '' : 's'}` : 'On time'}</span>
                    </div>
                    <div class="fine-breakdown-lines">
                        <div class="fine-breakdown-line">
                            <span>Overdue days</span>
                            <span>${book.overdueDays}</span>
                        </div>
                        <div class="fine-breakdown-line">
                            <span>Grace deduction</span>
                            <span>${gracePeriod} day${Number(gracePeriod) === 1 ? '' : 's'}</span>
                        </div>
                        <div class="fine-breakdown-line">
                            <span>Chargeable overdue days</span>
                            <span>${chargeableDays}</span>
                        </div>
                        <div class="fine-breakdown-line">
                            <span>Late fine</span>
                            <span>${chargeableDays > 0 ? `${chargeableDays} × ${formatCurrency(perDayFine)} = ${formatCurrency(overdueFine)}` : formatCurrency(0)}</span>
                        </div>
                        <div class="fine-breakdown-line">
                            <span>Condition fine (${escapeHtml(toTitleCase(book.condition))})</span>
                            <span>${formatCurrency(perBookConditionFine)}</span>
                        </div>
                        <div class="fine-breakdown-line">
                            <span>Book total</span>
                            <span>${formatCurrency(bookTotal)}</span>
                        </div>
                        ${capped ? `<div class="fine-breakdown-line"><span>Cap applied</span><span>Limited to ${formatCurrency(maxFine)}</span></div>` : ''}
                    </div>
                `;
                fineDetails.appendChild(fineItem);
            });

            setReturnTotalFineDisplay(
                totalFine,
                `${selectedIssuedBooks.length} selected book${selectedIssuedBooks.length === 1 ? '' : 's'} with ${toTitleCase(selectedCondition)} condition.`
            );
        }

        function updateReturnButton() {
            if (selectedIssuedBooks.length > 0 && selectedCondition) {
                returnButton.disabled = false;
                returnButton.textContent =
                    `Process Return (${selectedIssuedBooks.length} book${selectedIssuedBooks.length !== 1 ? 's' : ''})`;
            } else {
                returnButton.disabled = true;
                returnButton.textContent = 'Process Return';
            }
        }

        function clearReturnStudentSelection() {
            selectedReturnStudent = null;
            selectedReturnStudentPrivileges = null;
            selectedReturnStudentId.value = '';
            searchReturnStudentInput.value = '';
            clearReturnStudentBtn.style.display = 'none';
            returnStudentCard.style.display = 'none';
            issuedBooksSection.style.display = 'none';
            bookConditionSection.style.display = 'none';
            fineCalculationCard.style.display = 'none';
            selectedIssuedBooks = [];
            selectedCondition = null;
            resetConditionSelection();
            updateReturnButton();
            returnStudentResults.style.display = 'none';
            resetReturnPrivilegeSummary();
            document.getElementById('returnForm').reset();
        }
    </script>
@endpush
