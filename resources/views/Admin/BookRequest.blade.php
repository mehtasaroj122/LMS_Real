@extends('Admin.layouts.app')

@section('title', 'Book Request')

@push('styles')
    <style>
    /* ===== TABLE & PAGINATION STYLES (both themes) ===== */
    .table-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid;
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

    .requests-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1110px;
    }

    .requests-table th {
        padding: 6px 8px;
        font-weight: 600;
        font-size: 11px;
        border-bottom: 1px solid;
        white-space: nowrap;
        text-align: start;
        transition: background-color 0.3s, border-color 0.3s, color 0.3s;
    }

    /* Light theme th */
    body.light-theme .requests-table th {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #475569;
    }
    /* Dark theme th */
    body.dark-theme .requests-table th {
        background-color: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }

    .requests-table td {
        padding: 6px 8px;
        border-bottom: 1px solid;
        vertical-align: middle;
        font-size: 13px;
        transition: border-color 0.3s, color 0.3s;
    }

    /* Light theme td */
    body.light-theme .requests-table td {
        border-color: #e2e8f0;
        color: #0f172a;
    }
    /* Dark theme td */
    body.dark-theme .requests-table td {
        border-color: #334155;
        color: #f1f5f9;
    }

    .requests-table tr:last-child td {
        border-bottom: none;
    }

    .requests-table tr:hover {
        transition: background-color 0.3s;
    }
    body.light-theme .requests-table tr:hover {
        background-color: #f8fafc;
    }
    body.dark-theme .requests-table tr:hover {
        background-color: #2d3748;
    }

    /* Column width constraints */
    .requests-table th:nth-child(1),
    .requests-table td:nth-child(1) {
        padding-right: 4px;
        max-width: 140px;
    }
    .requests-table th:nth-child(2),
    .requests-table td:nth-child(2) {
           padding-left: 4px;
           max-width: 90px;
           overflow: hidden;
           text-overflow: ellipsis;
           white-space: nowrap;
    }
    .requests-table th:nth-child(3),
    .requests-table td:nth-child(3),
    .requests-table th:nth-child(4),
    .requests-table td:nth-child(4),
    .requests-table th:nth-child(5),
    .requests-table td:nth-child(5) {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .requests-table th:nth-child(6),
    .requests-table td:nth-child(6) {
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Text muted (secondary info) */
    .text-muted {
        transition: color 0.3s;
        font-size: 12px;
    }
    body.light-theme .text-muted {
        color: #64748b;
    }
    body.dark-theme .text-muted {
        color: #94a3b8;
    }

    /* Status Badges */
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
    
    body.light-theme .status-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }
    body.dark-theme .status-pending {
        background: linear-gradient(135deg, #78350f 0%, #451a03 100%);
        color: #fbbf24;
    }
    
    body.light-theme .status-approved {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
    }
    body.dark-theme .status-approved {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        color: #4ade80;
    }
    
    body.light-theme .status-rejected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }
    body.dark-theme .status-rejected {
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
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        white-space: nowrap;
    }
    
    /* Accept Button - Light Theme */
    body.light-theme .btn-accept {
        background-color: #dcfce7;
        color: #166534;
    }
    body.light-theme .btn-accept:hover {
        background-color: #bbf7d0;
        color: #15803d;
    }
    
    /* Accept Button - Dark Theme */
    body.dark-theme .btn-accept {
        background-color: #14532d;
        color: #86efac;
    }
    body.dark-theme .btn-accept:hover {
        background-color: #1b6e4e;
        color: #a7f3d0;
    }
    
    /* Reject Button - Light Theme */
    body.light-theme .btn-reject {
        background-color: #fee2e2;
        color: #991b1b;
    }
    body.light-theme .btn-reject:hover {
        background-color: #fecaca;
        color: #b91c1c;
    }
    
    /* Reject Button - Dark Theme */
    body.dark-theme .btn-reject {
        background-color: #7f1d1d;
        color: #fca5a5;
    }
    body.dark-theme .btn-reject:hover {
        background-color: #991b1b;
        color: #fecaca;
    }

    /* Muted and disabled action status */
    .action-status.accepted,
    .action-status.rejected {
        background: none !important;
        color: #94a3b8 !important; /* Muted text for light theme */
        opacity: 1;
        border-radius: 0;
        padding: 0;
        pointer-events: none;
        cursor: not-allowed;
        font-weight: 500;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    body.dark-theme .action-status.accepted,
    body.dark-theme .action-status.rejected {
        color: #64748b !important; /* Muted text for dark theme */
    }
    .action-status.accepted i,
    .action-status.rejected i {
        color: inherit !important;
        filter: grayscale(0.5);
    }

    /* Pagination styles */
    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
        margin: 16px 0 0;
        justify-content: end;
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

    /* Stats Cards */
    .stats-card {
        transition: all 0.3s ease;
    }

    /* Searchable Select Styles */
    .searchable-select-container {
        position: relative;
    }

    .searchable-select-input {
        width: 100%;
        padding: 0.5rem 0.75rem 0.5rem 2.5rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        background-color: white;
        color: #1e293b;
        transition: all 0.3s ease;
        font-size: 0.8rem;
    }

    .dark-theme .searchable-select-input {
        border-color: #475569;
        background-color: #1f2937;
        color: #f1f5f9;
    }

    .searchable-select-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .searchable-select-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 250px;
        overflow-y: auto;
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 50;
        display: none;
        margin-top: 0.25rem;
    }

    .dark-theme .searchable-select-dropdown {
        background-color: #1f2937;
        border-color: #374151;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .searchable-select-option {
        padding: 0.875rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
    }

    .dark-theme .searchable-select-option {
        color: #f1f5f9;
        border-bottom-color: #334155;
    }

    .searchable-select-option:hover {
        background-color: #f8fafc;
    }

    .dark-theme .searchable-select-option:hover {
        background-color: #334155;
    }

    .searchable-select-option.selected {
        background-color: #3b82f6;
        color: white;
    }

    .searchable-select-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }
    
</style>
@endpush


@section('content')
    <div class="">
        <!-- Header -->
        <div class="mb-3">
            <h1 class="text-xl font-bold text-primary">Book Requests</h1>
            <p class="mt-0.5 text-xs text-secondary">Manage student book requests</p>
        <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-3">
            <div class="p-3 card stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Pending</p>
                        <h3 class="mt-1 text-2xl font-bold text-primary" id="pendingCount">0</h3>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-yellow-100 rounded-lg dark:bg-yellow-900">
                        <i data-lucide="clock" class="w-6 h-6 text-yellow-600 dark:text-yellow-400"></i>
                    </div>
                </div>
            </div>

            <div class="p-3 card stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Approved</p>
                        <h3 class="mt-1 text-2xl font-bold text-primary" id="approvedCount">0</h3>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg dark:bg-green-900">
                        <i data-lucide="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>

            <div class="p-3 card stats-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Rejected</p>
                        <h3 class="mt-1 text-2xl font-bold text-primary" id="rejectedCount">0</h3>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-lg dark:bg-red-900">
                        <i data-lucide="x-circle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Requests Section -->
        <div class="mb-2">
            <h2 class="text-base font-semibold text-primary">All Requests</h2>
        </div>

        <!-- Table Controls -->
        <div class="p-3 mb-3 card">
            <div class="flex flex-col items-start justify-between gap-2 md:flex-row md:items-center">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search requests..."
                               class="w-full py-2 pl-10 pr-4 transition-all bg-transparent border border-gray-300 rounded-lg dark:border-gray-600 text-primary focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent md:w-64">
                        <i data-lucide="search" class="absolute w-4 h-4 text-gray-400 left-3 top-2.5"></i>
                    </div>
                    <select id="statusFilter" class="px-3 py-2 transition-all bg-transparent border border-gray-300 rounded-lg filter-select dark:border-gray-600 text-primary focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <button id="createRequestBtn" class="flex items-center gap-2 px-3 py-1 font-medium text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Create Request
                </button>
            </div>
        </div>

        <!-- Requests Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <table class="requests-table">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Book</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Processed By</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                    <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <!-- Pagination links will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Create Request Modal -->
    <div id="createRequestModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-lg mx-4 card">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-primary">Create Book Request</h3>
                <p class="mt-0.5 text-xs text-secondary">Create a new book request for a student</p>
            </div>

            <div class="p-4">
                <!-- Student Selection -->
                <div class="mb-3">
                    <label class="block mb-1 text-xs font-medium text-primary">
                        Student <span class="text-red-500">*</span>
                    </label>
                    <div class="relative searchable-select-container">
                        <input type="hidden" id="studentId" name="student_id">
                        <input type="text" 
                               id="studentSearch" 
                               class="w-full px-3 py-2 pl-10 transition bg-transparent border border-gray-300 rounded-lg searchable-select-input dark:border-gray-600 text-primary focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                               placeholder="Search for a student..."
                               autocomplete="off">
                        <i data-lucide="user" class="w-5 h-5 searchable-select-icon"></i>
                        <div id="studentDropdown" class="searchable-select-dropdown">
                            @foreach($students as $student)
                                <div class="searchable-select-option" 
                                     data-value="{{ $student->id }}" 
                                     data-text="{{ $student->user->name }} ({{ $student->roll_no }})">
                                    <div class="font-medium">{{ $student->user->name }}</div>
                                    <div class="text-sm text-secondary">{{ $student->roll_no }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Book Selection -->
                <div class="mb-3">
                    <label class="block mb-1 text-xs font-medium text-primary">
                        Book <span class="text-red-500">*</span>
                    </label>
                    <div class="relative searchable-select-container">
                        <input type="hidden" id="bookId" name="book_id">
                        <input type="text" 
                               id="bookSearch" 
                               class="w-full px-3 py-2 pl-10 transition bg-transparent border border-gray-300 rounded-lg searchable-select-input dark:border-gray-600 text-primary focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                               placeholder="Search for a book..."
                               autocomplete="off">
                        <i data-lucide="book" class="w-5 h-5 searchable-select-icon"></i>
                        <div id="bookDropdown" class="searchable-select-dropdown">
                            @foreach($books as $book)
                                <div class="searchable-select-option" 
                                     data-value="{{ $book->id }}" 
                                     data-text="{{ $book->title }} - {{ $book->author }}">
                                    <div class="font-medium">{{ $book->title }}</div>
                                    <div class="text-sm text-secondary">{{ $book->author }}</div>
                                    @if($book->available_copies > 0)
                                        <div class="text-xs text-green-600">Available: {{ $book->available_copies }}</div>
                                    @else
                                        <div class="text-xs text-red-600">Out of stock</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Selected Items Display -->
                <div id="selectedItems" class="mb-3 space-y-2" style="display: none;">
                    <div class="p-2 border rounded-lg dark:border-gray-700">
                        <h4 class="mb-1 text-xs font-medium text-primary">Selected Items</h4>
                        <div id="selectedStudent" class="text-xs text-secondary" style="display: none;">
                            <span class="font-medium">Student:</span> <span id="selectedStudentText"></span>
                        </div>
                        <div id="selectedBook" class="text-xs text-secondary" style="display: none;">
                            <span class="font-medium">Book:</span> <span id="selectedBookText"></span>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex justify-end pt-3 space-x-2 border-t border-gray-200 dark:border-gray-700">
                    <button id="cancelCreateRequest" class="px-3 py-1 transition border border-gray-300 rounded-lg dark:border-gray-600 text-primary hover:bg-gray-50 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button id="confirmCreateRequest" class="px-3 py-1 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700" disabled>
                        Create Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-sm mx-4 card">
            <div class="p-4 text-center">
                <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-green-600 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-400">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <h3 class="mb-1 text-base font-semibold text-primary">Request Created!</h3>
                <p class="mb-4 text-xs text-secondary">The book request has been successfully created.</p>
                <button id="closeConfirmationModal" class="px-3 py-1 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                    OK
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        class SearchableSelect {
            constructor(container, options) {
                this.container = container;
                this.input = container.querySelector('.searchable-select-input');
                // Find the hidden input inside the container's parent div
                this.hiddenInput = container.parentElement.querySelector('input[type="hidden"]');
                
                // If not found, try to find it directly in the container
                if (!this.hiddenInput) {
                    this.hiddenInput = container.querySelector('input[type="hidden"]');
                }
                
                // If still not found, get the one before the container
                if (!this.hiddenInput) {
                    let prev = container.previousElementSibling;
                    while (prev) {
                        if (prev.type === 'hidden') {
                            this.hiddenInput = prev;
                            break;
                        }
                        prev = prev.previousElementSibling;
                    }
                }
                
                this.dropdown = container.querySelector('.searchable-select-dropdown');
                this.options = Array.from(this.dropdown.querySelectorAll('.searchable-select-option'));
                this.selectedOption = null;
                
                console.log('SearchableSelect initialized:', { 
                    container, 
                    input: this.input, 
                    hiddenInput: this.hiddenInput,
                    dropdown: this.dropdown,
                    optionsCount: this.options.length 
                });
                
                this.init();
            }

            init() {
                // Show dropdown on input focus
                this.input.addEventListener('focus', () => {
                    this.showDropdown();
                });

                // Search filtering
                this.input.addEventListener('input', (e) => {
                    this.filterOptions(e.target.value);
                });

                // Handle option selection
                this.dropdown.addEventListener('click', (e) => {
                    const option = e.target.closest('.searchable-select-option');
                    if (option && !option.classList.contains('no-results')) {
                        this.selectOption(option);
                    }
                });

                // Hide dropdown when clicking outside
                document.addEventListener('click', (e) => {
                    if (!this.container.contains(e.target)) {
                        this.hideDropdown();
                    }
                });

                // Keyboard navigation
                this.input.addEventListener('keydown', (e) => {
                    const visibleOptions = this.getVisibleOptions();
                    
                    switch(e.key) {
                        case 'ArrowDown':
                            e.preventDefault();
                            this.navigateOptions(1, visibleOptions);
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            this.navigateOptions(-1, visibleOptions);
                            break;
                        case 'Enter':
                            e.preventDefault();
                            const selected = this.dropdown.querySelector('.searchable-select-option.selected');
                            if (selected) {
                                this.selectOption(selected);
                            }
                            break;
                        case 'Escape':
                            this.hideDropdown();
                            break;
                    }
                });
            }

            filterOptions(searchTerm) {
                const term = searchTerm.toLowerCase();
                let hasVisibleOptions = false;

                this.options.forEach(option => {
                    let text = option.getAttribute('data-text') || '';
                    
                    // If data-text is empty, extract from DOM
                    if (!text || text.trim() === '') {
                        const nameDiv = option.querySelector('.font-medium');
                        const idDiv = option.querySelector('.text-sm');
                        if (nameDiv && idDiv) {
                            text = nameDiv.textContent + ' (' + idDiv.textContent + ')';
                        } else if (nameDiv) {
                            text = nameDiv.textContent;
                        }
                    }
                    
                    text = text.toLowerCase();
                    if (text.includes(term)) {
                        option.style.display = 'block';
                        hasVisibleOptions = true;
                    } else {
                        option.style.display = 'none';
                    }
                });

                // Show "no results" message if no options match
                let noResults = this.dropdown.querySelector('.no-results');
                if (!hasVisibleOptions) {
                    if (!noResults) {
                        noResults = document.createElement('div');
                        noResults.className = 'searchable-select-option no-results';
                        noResults.textContent = 'No results found';
                        this.dropdown.appendChild(noResults);
                    }
                } else if (noResults) {
                    noResults.remove();
                }

                this.showDropdown();
            }

            getVisibleOptions() {
                return Array.from(this.dropdown.querySelectorAll('.searchable-select-option:not(.no-results)'))
                    .filter(option => option.style.display !== 'none');
            }

            navigateOptions(direction, options) {
                const current = this.dropdown.querySelector('.searchable-select-option.selected');
                let index = current ? options.indexOf(current) : -1;
                
                index += direction;
                if (index < 0) index = options.length - 1;
                if (index >= options.length) index = 0;

                // Remove previous selection
                if (current) {
                    current.classList.remove('selected');
                }

                // Add new selection
                if (options[index]) {
                    options[index].classList.add('selected');
                    options[index].scrollIntoView({ block: 'nearest' });
                }
            }

            selectOption(option) {
                // Remove previous selection
                this.options.forEach(opt => opt.classList.remove('selected'));
                
                // Mark as selected
                option.classList.add('selected');
                this.selectedOption = option;
                
                // Get value
                const value = option.getAttribute('data-value');
                
                // Extract text from the option's content
                const nameDiv = option.querySelector('.font-medium');
                const idDiv = option.querySelector('.text-sm');
                
                let text = option.getAttribute('data-text');
                
                // If data-text is empty or just contains (), extract from DOM
                if (!text || text.trim() === '()' || text.trim() === '') {
                    if (nameDiv && idDiv) {
                        text = nameDiv.textContent.trim() + ' (' + idDiv.textContent.trim() + ')';
                    } else if (nameDiv) {
                        text = nameDiv.textContent.trim();
                    }
                }
                
                console.log('Selected option:', { value, text, option, nameDiv, idDiv });
                
                this.hiddenInput.value = value;
                this.input.value = text || '';
                
                // Ensure the input shows the text properly
                this.input.style.color = 'var(--text-primary)';
                
                this.hideDropdown();
                this.input.blur();
                
                // Dispatch change event
                this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            showDropdown() {
                this.dropdown.style.display = 'block';
                this.input.setAttribute('aria-expanded', 'true');
            }

            hideDropdown() {
                this.dropdown.style.display = 'none';
                this.input.setAttribute('aria-expanded', 'false');
            }

            getValue() {
                return this.hiddenInput.value;
            }

            getText() {
                return this.input.value;
            }

            clear() {
                this.hiddenInput.value = '';
                this.input.value = '';
                this.selectedOption = null;
                this.options.forEach(opt => opt.classList.remove('selected'));
                this.filterOptions('');
            }
        }

        class BookRequestManager {
            constructor() {
                this.currentStatus = 'all';
                this.searchDebounceTimer = null;
                this.studentSelect = null;
                this.bookSelect = null;
                this.init();
            }

            init() {
                // Initialize searchable selects - they will be reinitialized when modal opens
                this.initializeSelects();

                // Monitor selection changes
                document.getElementById('studentId')?.addEventListener('change', () => this.updateSelectedItems());
                document.getElementById('bookId')?.addEventListener('change', () => this.updateSelectedItems());

                // Modal event listeners
                document.getElementById('createRequestBtn').addEventListener('click', () => this.openCreateModal());
                document.getElementById('cancelCreateRequest').addEventListener('click', () => this.closeCreateModal());
                document.getElementById('confirmCreateRequest').addEventListener('click', () => this.submitCreateRequest());
                document.getElementById('closeConfirmationModal').addEventListener('click', () => this.closeConfirmationModal());

                // Close modals on overlay click
                document.getElementById('createRequestModal').addEventListener('click', (e) => {
                    if (e.target.id === 'createRequestModal') this.closeCreateModal();
                });
                document.getElementById('confirmationModal').addEventListener('click', (e) => {
                    if (e.target.id === 'confirmationModal') this.closeConfirmationModal();
                });

                // Search and filter
                const searchInput = document.getElementById('searchInput');
                const statusFilter = document.getElementById('statusFilter');

                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        clearTimeout(this.searchDebounceTimer);
                        this.searchDebounceTimer = setTimeout(() => {
                            this.fetchRequests();
                        }, 300);
                    });
                }

                if (statusFilter) {
                    statusFilter.addEventListener('change', (e) => {
                        this.currentStatus = e.target.value;
                        this.fetchRequests();
                    });
                }

                // Load initial data
                this.fetchRequests();
                this.refreshStats();
            }

            initializeSelects() {
                // Initialize searchable selects
                const studentContainer = document.querySelector('.searchable-select-container');
                const bookContainer = document.querySelectorAll('.searchable-select-container')[1];
                
                if (studentContainer) {
                    this.studentSelect = new SearchableSelect(studentContainer);
                }
                if (bookContainer) {
                    this.bookSelect = new SearchableSelect(bookContainer);
                }
            }

            updateSelectedItems() {
                const studentId = document.getElementById('studentId').value;
                const bookId = document.getElementById('bookId').value;
                const selectedItems = document.getElementById('selectedItems');
                const selectedStudent = document.getElementById('selectedStudent');
                const selectedBook = document.getElementById('selectedBook');
                const confirmButton = document.getElementById('confirmCreateRequest');

                // Update selected student text
                if (studentId && this.studentSelect) {
                    const studentText = this.studentSelect.getText();
                    if (studentText) {
                        document.getElementById('selectedStudentText').textContent = studentText;
                        selectedStudent.style.display = 'block';
                    } else {
                        selectedStudent.style.display = 'none';
                    }
                } else {
                    selectedStudent.style.display = 'none';
                }

                // Update selected book text
                if (bookId && this.bookSelect) {
                    const bookText = this.bookSelect.getText();
                    if (bookText) {
                        document.getElementById('selectedBookText').textContent = bookText;
                        selectedBook.style.display = 'block';
                    } else {
                        selectedBook.style.display = 'none';
                    }
                } else {
                    selectedBook.style.display = 'none';
                }

                // Show/hide selected items container
                if (studentId || bookId) {
                    selectedItems.style.display = 'block';
                } else {
                    selectedItems.style.display = 'none';
                }

                // Enable/disable confirm button
                confirmButton.disabled = !(studentId && bookId);
            }

            fetchRequests(page = 1) {
                const searchTerm = document.getElementById('searchInput')?.value || '';
                const status = this.currentStatus;

                fetch(`{{ route('admin.book-requests.data') }}?search=${encodeURIComponent(searchTerm)}&status=${status}&page=${page}`, {
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
                            document.getElementById('requestsTableBody').innerHTML = `
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block;"></i>
                                        <p style="font-size: 16px; margin: 0; font-weight: 500;">No requests found</p>
                                        <p style="font-size: 14px; margin-top: 8px; color: #9ca3af;">Try adjusting your search or filters</p>
                                    </td>
                                </tr>
                            `;
                            document.getElementById('paginationContainer').innerHTML = '';
                        } else {
                            document.getElementById('requestsTableBody').innerHTML = data.tableRows;
                            // Wrap pagination in proper HTML if needed
                            let paginationHtml = data.pagination;
                            if (paginationHtml && !paginationHtml.includes('<nav>')) {
                                paginationHtml = '<nav>' + paginationHtml + '</nav>';
                            }
                            document.getElementById('paginationContainer').innerHTML = paginationHtml;
                            this.attachEventListeners();
                        }
                    } else {
                        console.error('Error loading requests');
                    }
                })
                .catch(error => {
                    console.error('Error fetching requests:', error);
                });
            }

            attachEventListeners() {
                // Re-initialize Lucide icons for newly loaded content
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Attach pagination click handlers
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        this.fetchRequests(page);
                    });
                });
            }

            refreshStats() {
                fetch(`{{ route('admin.book-requests.stats') }}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('pendingCount').textContent = data.pendingCount;
                    document.getElementById('approvedCount').textContent = data.approvedCount;
                    document.getElementById('rejectedCount').textContent = data.rejectedCount;
                })
                .catch(error => console.error('Error fetching stats:', error));
            }

            openCreateModal() {
                // Reinitialize the selects to ensure proper binding
                this.initializeSelects();
                
                // Clear any previous selections
                if (this.studentSelect) this.studentSelect.clear();
                if (this.bookSelect) this.bookSelect.clear();
                document.getElementById('selectedItems').style.display = 'none';
                document.getElementById('confirmCreateRequest').disabled = true;
                
                // Show modal
                document.getElementById('createRequestModal').classList.remove('hidden');
                
                // Focus on first search input
                setTimeout(() => {
                    document.getElementById('studentSearch').focus();
                }, 100);
            }

            closeCreateModal() {
                document.getElementById('createRequestModal').classList.add('hidden');
            }

            closeConfirmationModal() {
                document.getElementById('confirmationModal').classList.add('hidden');
            }

            submitCreateRequest() {
                const studentId = document.getElementById('studentId').value;
                const bookId = document.getElementById('bookId').value;

                if (!studentId || !bookId) {
                    alert('Please select both student and book.');
                    return;
                }

                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('book_id', bookId);

                fetch('{{ route("admin.book-requests.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.closeCreateModal();
                        document.getElementById('confirmationModal').classList.remove('hidden');
                        this.fetchRequests(1);
                        this.refreshStats();
                    } else {
                        alert(data.message || 'Error creating request');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error creating request');
                });
            }
        }

        // Make processRequest globally available
        window.processRequest = function(requestId, action) {
            if (!confirm(`Are you sure you want to ${action} this request?`)) return;

            const formData = new FormData();
            formData.append('status', action);
            formData.append('_method', 'PUT');

            fetch(`{{ url('admin/book-requests') }}/${requestId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    manager.fetchRequests();
                    manager.refreshStats();
                } else {
                    alert(data.message || 'Error updating request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating request');
            });
        };

        let manager;
        document.addEventListener('DOMContentLoaded', () => {
            manager = new BookRequestManager();
        });
    </script>
@endpush