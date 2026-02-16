@extends('Staff.layouts.app')

@section('title', 'Issue Book')

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
        background: none;
        border: none;
    }

    .remove-book:hover {
        background-color: #fee2e2;
    }

    body.dark-theme .remove-book:hover {
        background-color: #7f1d1d;
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
</style>
@endpush

@section('content')
<div class="p-6">
    <div>
        <h1 class="section-title">Issue Book</h1>
        <p class="section-subtitle">Issue multiple books to a student (Max 5 books)</p>

        <div class="grid-container">
            <!-- Issue Form -->
            <div class="card">
                <h2 class="section-header">Issue Form</h2>

                <form id="issueForm">
                    <!-- Search Student -->
                    <div class="mb-5">
                        <label class="form-label">
                            Search Student<span class="text-danger">*</span>
                        </label>
                        <div class="search-container">
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchStudent" class="search-input" placeholder="Search by name, student ID, or email..." required>
                            <button type="button" id="clearStudentBtn" class="clear-btn" onclick="clearStudentSelection()" style="display: none;">Clear</button>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchBook" class="search-input" placeholder="Search by title, author, or ISBN...">
                            <div class="search-results" id="bookResults"></div>
                        </div>
                    </div>

                    <!-- Selected Books List -->
                    <div class="selected-books-list" id="selectedBooksList" style="display: none;">
                        <div class="mb-2 text-sm text-secondary">Selected Books (Max: 5)</div>
                        <div id="selectedBooksContainer"></div>
                    </div>

                    <!-- Issue Button -->
                    <button type="submit" class="btn-primary" id="issueButton" disabled>
                        Issue Books (0/5)
                    </button>
                </form>
            </div>

            <!-- Selected Details -->
            <div class="space-y-6">
                <!-- Selected Student Details -->
                <div class="card" id="selectedStudentCard" style="display: none;">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                <div class="card" id="availableBooksCard" style="display: none;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
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
                        <div class="info-box">
                            <p class="text-sm">Select up to 5 different books for this student. Click on search results to add books.</p>
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
    // Issue Book Variables
    let selectedStudent = null;
    let selectedBooks = [];
    const maxBooksPerStudent = 5;

    // Issue Book Elements - Global scope
    let searchStudentInput;
    let studentResults;
    let selectedStudentId;
    let selectedStudentCard;
    let availableBooksCard;
    let clearStudentBtn;
    let searchBookInput;
    let bookResults;
    let selectedBooksList;
    let selectedBooksContainer;
    let issueButton;
    let issueForm;
    let studentPrivileges = null;
    let fineSettings = null;

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
        // Initialize Issue Book Elements
        searchStudentInput = document.getElementById('searchStudent');
        studentResults = document.getElementById('studentResults');
        selectedStudentId = document.getElementById('selectedStudentId');
        selectedStudentCard = document.getElementById('selectedStudentCard');
        availableBooksCard = document.getElementById('availableBooksCard');
        clearStudentBtn = document.getElementById('clearStudentBtn');

        searchBookInput = document.getElementById('searchBook');
        bookResults = document.getElementById('bookResults');
        selectedBooksList = document.getElementById('selectedBooksList');
        selectedBooksContainer = document.getElementById('selectedBooksContainer');
        issueButton = document.getElementById('issueButton');
        issueForm = document.getElementById('issueForm');

        // Search Students for Issue
        searchStudentInput.addEventListener('input', function() {
            const query = this.value;
            studentResults.innerHTML = '';
            
            if (query.length < 2) {
                studentResults.style.display = 'none';
                return;
            }

            // Fetch students from API
            fetch(`{{ route('staff.transactions.students') }}?query=${encodeURIComponent(query)}`, { credentials: 'include' })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(students => {
                    if (students.length === 0) {
                        studentResults.innerHTML = '<div class="result-item"><div class="result-title">No students found</div></div>';
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
                    studentResults.innerHTML = '<div class="result-item"><div class="result-title">Error: ' + error.message + '</div></div>';
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
                bookResults.innerHTML = '<div class="result-item"><div class="result-title">Please select a student first</div></div>';
                bookResults.style.display = 'block';
                return;
            }

            // Fetch available books from API
            fetch(`{{ route('staff.transactions.books') }}?query=${encodeURIComponent(query)}&studentId=${selectedStudent.id}`, { credentials: 'include' })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.json();
                })
                .then(books => {
                    if (books.length === 0) {
                        bookResults.innerHTML = '<div class="result-item"><div class="result-title">No available books found</div></div>';
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
                    bookResults.innerHTML = '<div class="result-item"><div class="result-title">Error: ' + error.message + '</div></div>';
                    bookResults.style.display = 'block';
                });
        });

        // Issue Book Form Submit
        issueForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedStudent || selectedBooks.length === 0) {
                showCustomAlert('Missing Information', 'Please select a student and at least one book', 'warning');
                return;
            }

            if (!studentPrivileges) {
                showCustomAlert('Student Not Selected', 'Please wait for student information to load', 'error');
                return;
            }

            if (!studentPrivileges.borrowing_allowed) {
                showCustomAlert('Borrowing Permission Denied', `${selectedStudent.name} does not have borrowing permission.`, 'error');
                return;
            }

            // Check if student can issue more books
            const canIssueMore = studentPrivileges.max_books - selectedStudent.issued;
            if (selectedBooks.length > canIssueMore) {
                showCustomAlert('Exceeds Borrowing Limit', `Student can only issue ${canIssueMore} more book(s).`, 'error');
                return;
            }

            // Submit to backend
            const bookIds = selectedBooks.map(book => book.id);
            
            fetch('{{ route("staff.transactions.issue") }}', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    student_id: selectedStudent.id,
                    book_ids: bookIds,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showCustomAlert('Books Issued Successfully', data.message, 'success');
                    clearStudentSelection();
                } else {
                    const isPermError = data.message.includes('allowed to borrow') || data.message.includes('permission');
                    showCustomAlert(isPermError ? 'Permission Denied' : 'Issue Failed', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomAlert('Transaction Error', 'Failed to issue books: ' + error.message, 'error');
            });
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-container')) {
                studentResults.style.display = 'none';
                bookResults.style.display = 'none';
            }
        });
    });

    // Function to select student for issue
    function selectStudentForIssue(student) {
        selectedStudent = student;
        selectedStudentId.value = student.id;
        searchStudentInput.value = `${student.name} (${student.roll_no})`;
        studentResults.style.display = 'none';
        clearStudentBtn.style.display = 'block';
        
        // Fetch student privileges
        fetch(`/admin/students/${student.id}/privileges`, { credentials: 'include' })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showCustomAlert('Error', 'Failed to load student privileges', 'error');
                    return;
                }
                
                studentPrivileges = data.effective;
                
                if (!studentPrivileges.borrowing_allowed) {
                    showCustomAlert('Borrowing Not Allowed', `${student.name} cannot borrow books at this time.`, 'error');
                    clearStudentSelection();
                    return;
                }
                
                // Update student details with privileges
                document.getElementById('studentName').textContent = student.name;
                document.getElementById('studentID').textContent = student.roll_no;
                document.getElementById('studentDepartment').textContent = student.department;
                document.getElementById('studentEmail').textContent = student.email;
                document.getElementById('studentIssued').textContent = `${student.issued} / ${studentPrivileges.max_books}`;
                
                const progressPercent = (student.issued / studentPrivileges.max_books) * 100;
                document.getElementById('issuedProgress').style.width = `${progressPercent}%`;
                
                selectedStudentCard.style.display = 'block';
                availableBooksCard.style.display = 'block';
                
                selectedBooks = [];
                updateSelectedBooksList();
                updateIssueButton();
                
                const canIssueMore = studentPrivileges.max_books - student.issued;
                document.getElementById('canIssueMore').textContent = canIssueMore;
                document.getElementById('selectedCount').textContent = '0 books';
                document.getElementById('totalBooks').textContent = '0';
                
                searchBookInput.disabled = false;
                searchBookInput.placeholder = "Search books to issue...";
            })
            .catch(error => {
                console.error('Error:', error);
                showCustomAlert('Error', 'Failed to load student privileges', 'error');
                clearStudentSelection();
            });
    }

    // Function to add book to selection
    function addBookToSelection(book) {
        if (!selectedStudent || !studentPrivileges) return;
        
        // Check if student can issue more books (based on privileges)
        const canIssueMore = studentPrivileges.max_books - selectedStudent.issued;
        if (selectedBooks.length >= canIssueMore) {
            showCustomAlert('Book Limit Reached', `Student can only issue ${canIssueMore} more book(s).`, 'warning');
            return;
        }
        
        // Check if book is already selected
        if (selectedBooks.some(b => b.id === book.id)) {
            showCustomAlert('Book Already Selected', 'This book is already in the selection.', 'warning');
            return;
        }
        
        // Check if book is available
        if (book.available <= 0) {
            showCustomAlert('Book Unavailable', 'This book is not in stock.', 'error');
            return;
        }
        
        selectedBooks.push(book);
        updateSelectedBooksList();
        updateIssueButton();
        updateAvailableBooksInfo();
    }

    // Function to remove book from selection
    function removeBookFromSelection(bookId) {
        selectedBooks = selectedBooks.filter(book => book.id !== bookId);
        updateSelectedBooksList();
        updateIssueButton();
        updateAvailableBooksInfo();
    }

    // Function to update selected books list
    function updateSelectedBooksList() {
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
                    <div class="book-details">${book.author} • ${book.category || 'N/A'}</div>
                </div>
                <button type="button" class="remove-book" onclick="removeBookFromSelection(${book.id})">
                    Remove
                </button>
            `;
            selectedBooksContainer.appendChild(bookItem);
        });
    }

    // Function to update issue button
    function updateIssueButton() {
        const canIssueMore = selectedStudent && studentPrivileges ? studentPrivileges.max_books - selectedStudent.issued : 0;
        const selectedCount = selectedBooks.length;
        
        if (selectedStudent && studentPrivileges && selectedCount > 0) {
            issueButton.disabled = false;
            issueButton.textContent = `Issue Books (${selectedCount}/${canIssueMore})`;
        } else {
            issueButton.disabled = true;
            issueButton.textContent = `Issue Books (0/${canIssueMore})`;
        }
    }

    // Function to update available books info
    function updateAvailableBooksInfo() {
        if (!selectedStudent || !studentPrivileges) return;
        
        const canIssueMore = studentPrivileges.max_books - selectedStudent.issued;
        const selectedCount = selectedBooks.length;
        
        document.getElementById('selectedCount').textContent = `${selectedCount} book${selectedCount !== 1 ? 's' : ''}`;
        document.getElementById('totalBooks').textContent = selectedCount;
        document.getElementById('canIssueMore').textContent = canIssueMore - selectedCount;
    }

    // Function to clear student selection
    function clearStudentSelection() {
        selectedStudent = null;
        studentPrivileges = null;
        selectedStudentId.value = '';
        searchStudentInput.value = '';
        clearStudentBtn.style.display = 'none';
        selectedStudentCard.style.display = 'none';
        availableBooksCard.style.display = 'none';
        selectedBooks = [];
        updateSelectedBooksList();
        updateIssueButton();
        
        searchBookInput.disabled = true;
        searchBookInput.placeholder = "Select a student first...";
        searchBookInput.value = '';
        bookResults.style.display = 'none';
        
        issueForm.reset();
    }
</script>
@endpush