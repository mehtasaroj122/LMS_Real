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
                    </form>
                </div>

                <!-- Return Details -->
                <div class="space-y-6">
                    <!-- Student Information -->
                    <div class="card" id="returnStudentCard" style="display: none;">
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
                    <div class="card" id="fineCalculationCard" style="display: none;">
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

                        <div class="info-box">
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
        // Fine settings from backend with defaults
        const fineSettings = {
            per_day_fine: {{ $fineSettings->per_day_fine ?? 5 }},
            grace_period_days: {{ $fineSettings->grace_period_days ?? 2 }},
            max_fine_amount: {{ $fineSettings->max_fine_amount ?? 500 }},
            fair_condition_penalty: {{ $fineSettings->fair_condition_penalty ?? 50 }},
            damaged_book_penalty: {{ $fineSettings->damaged_book_penalty ?? 200 }},
            lost_book_penalty: {{ $fineSettings->lost_book_penalty ?? 500 }}
        };

        // Return Book Variables
        let selectedReturnStudent = null;
        let selectedIssuedBooks = [];
        let selectedCondition = null;
        let returnSearchDebounceTimer = null;
        let cachedStudents = {};
        let selectedReturnStudentId;
        let returnStudentCard;
        let issuedBooksSection;
        let issuedBooksContainer;
        let bookConditionSection;
        let returnButton;
        let returnForm;
        let fineCalculationCard;
        let fineDetails;
        let totalFineElement;
        let clearReturnStudentBtn;

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
            clearReturnStudentBtn = document.getElementById('clearReturnStudentBtn');

            // Search Students for Return
            searchReturnStudentInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                returnStudentResults.innerHTML = '';

                if (query.length < 2) {
                    returnStudentResults.style.display = 'none';
                    return;
                }

                // Clear previous debounce timer
                if (returnSearchDebounceTimer) clearTimeout(returnSearchDebounceTimer);

                // Show loading state immediately
                returnStudentResults.innerHTML =
                    '<div class="result-item"><div class="result-title">Searching...</div></div>';
                returnStudentResults.style.display = 'block';

                // Fetch students from API immediately (no debounce delay)
                fetch(
                        `{{ route('staff.transactions.students') }}?query=${encodeURIComponent(query)}`
                    )
                    .then(response => response.json())
                    .then(students => {
                        // Cache the results
                        students.forEach(s => {
                            cachedStudents[s.id] = s;
                        });

                        if (students.length === 0) {
                            returnStudentResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No students found</div></div>';
                        } else {
                            // Clear "Searching..." message
                            returnStudentResults.innerHTML = '';

                            const studentItems = new Map();

                            // Create item elements for all students immediately
                            students.forEach(student => {
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
                            students.forEach(student => {
                                fetch(
                                        `{{ route('staff.transactions.issued-books') }}?studentId=${student.id}&countOnly=1`
                                    )
                                    .then(resp => resp.json())
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

                            // Attach click handler to fetch full book details when clicked
                            students.forEach(student => {
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
                                            `{{ route('staff.transactions.issued-books') }}?studentId=${student.id}`
                                        )
                                        .then(resp => resp.json())
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
                        console.error('Error:', error);
                        returnStudentResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error loading students</div></div>';
                        returnStudentResults.style.display = 'block';
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

                fetch('{{ route('staff.transactions.return') }}', {
                        method: 'POST',
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
                            alert(data.message + (totalFine > 0 ?
                                `\n\nTotal Fine: ₹${totalFine.toLocaleString()}` : ''));

                            // Reset form
                            clearReturnStudentSelection();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error returning books');
                    });
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container')) {
                    returnStudentResults.style.display = 'none';
                }
            });
        });

        // Function to select student for return
        function selectStudentForReturn(student, issuedBooks) {
            selectedReturnStudent = student;
            selectedReturnStudentId.value = student.id;
            searchReturnStudentInput.value = `${student.name} (${student.roll_no})`;
            returnStudentResults.style.display = 'none';
            clearReturnStudentBtn.style.display = 'block';

            // Update student details
            document.getElementById('returnStudentName').textContent = student.name;
            document.getElementById('returnStudentID').textContent = student.roll_no;
            document.getElementById('returnStudentDepartment').textContent = student.department;
            document.getElementById('returnStudentEmail').textContent = student.email;
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
        }

        // Function to display issued books
        function displayIssuedBooks(issuedBooks) {
            issuedBooksContainer.innerHTML = '';

            if (issuedBooks.length === 0) {
                issuedBooksContainer.innerHTML = '<div class="py-4 text-center text-secondary">No issued books found</div>';
                return;
            }

            const today = new Date();

            issuedBooks.forEach(issuedBook => {
                if (issuedBook.returned) return;

                const dueDate = new Date(issuedBook.due_date);
                const diffTime = today - dueDate;
                // Prefer server-provided value (keeps consistent with Carbon calculation)
                // Fallback to client-side calculation using Math.floor to match Carbon's diffInDays behavior
                const serverOverdue = parseInt(issuedBook.overdue_days);
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
                    <div class="book-title">${issuedBook.book_title}</div>
                    <div class="book-meta">
                        <span>${issuedBook.author}</span>
                        <span>Issued: ${new Date(issuedBook.issue_date).toLocaleDateString()}</span>
                        <span>Due: ${dueDate.toLocaleDateString()}</span>
                    </div>
                    <div>
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

        // Function to toggle issued book selection
        function toggleIssuedBookSelection(bookId, isChecked) {
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
                resetConditionSelection();
            }

            updateReturnButton();
            calculateTotalFine();
        }

        // Function to select condition
        function selectCondition(condition) {
            selectedCondition = condition;

            // Update UI
            document.querySelectorAll('.condition-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`.condition-option[data-condition="${condition}"]`).classList.add('selected');

            // Update selected condition for all books
            const conditionElement = document.querySelector(`.condition-option[data-condition="${condition}"]`);
            const conditionFine = parseInt(conditionElement.dataset.fine) || 0;

            selectedIssuedBooks.forEach(book => {
                book.condition = condition;
                book.conditionFine = conditionFine;
            });

            document.getElementById('selectedCondition').value = condition;
            updateReturnButton();
            calculateTotalFine();
        }

        // Function to reset condition selection
        function resetConditionSelection() {
            document.querySelectorAll('.condition-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.getElementById('selectedCondition').value = '';
        }

        // Function to calculate total fine
        function calculateTotalFine() {
            let totalFine = 0;
            fineDetails.innerHTML = '';

            if (selectedIssuedBooks.length === 0 || !selectedCondition) {
                totalFineElement.textContent = '₹0';
                return;
            }

            const perDayFine = fineSettings.per_day_fine;
            const gracePeriod = fineSettings.grace_period_days;
            const maxFine = fineSettings.max_fine_amount;

            selectedIssuedBooks.forEach(book => {
                // Calculate overdue fine with grace period deduction
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
        }

        // Function to update return button
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

        // Function to clear return student selection
        function clearReturnStudentSelection() {
            selectedReturnStudent = null;
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

            // Reset form
            document.getElementById('returnForm').reset();
        }
    </script>
@endpush
