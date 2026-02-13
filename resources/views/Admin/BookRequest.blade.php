@extends('Admin.layouts.app')

@section('title', 'Book Request')

@push('styles')
    <style>
    /* Book Requests Styles - Enhanced with Striped Table and Hover Effects */
    .requests-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .requests-table thead tr {
        background-color: #f8fafc;
    }

    body.dark-theme .requests-table thead tr {
        background-color: #1e293b;
    }

    .requests-table th {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.65rem;
        padding: 0.6rem 0.8rem;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
    }

    body.dark-theme .requests-table th {
        border-bottom-color: #475569;
        color: #94a3b8;
    }

    .requests-table tbody tr {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-bottom: 1px solid #f1f5f9;
    }

    body.dark-theme .requests-table tbody tr {
        border-bottom-color: #334155;
    }

    /* Striped rows */
    .requests-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    body.dark-theme .requests-table tbody tr:nth-child(even) {
        background-color: rgba(30, 41, 59, 0.4);
    }

    /* Hover effects */
    .requests-table tbody tr:hover {
        background-color: #e2e8f0 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    body.dark-theme .requests-table tbody tr:hover {
        background-color: #334155 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
    }

    .requests-table td {
        padding: 0.4rem 0.6rem;
        font-size: 0.8rem;
        color: #475569;
    }

    body.dark-theme .requests-table td {
        color: #cbd5e1;
    }

    .requests-table td:first-child {
        border-top-left-radius: 0.5rem;
        border-bottom-left-radius: 0.5rem;
    }

    .requests-table td:last-child {
        border-top-right-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }

    /* Status Badges */
    .status-badge {
        padding: 0.2rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.2rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }

    .status-badge:hover {
        transform: scale(1.05);
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
        border-color: #fde68a;
    }

    body.dark-theme .status-pending {
        background-color: #78350f;
        color: #fbbf24;
        border-color: #92400e;
    }

    .status-approved {
        background-color: #dcfce7;
        color: #166534;
        border-color: #bbf7d0;
    }

    body.dark-theme .status-approved {
        background-color: #14532d;
        color: #4ade80;
        border-color: #166534;
    }

    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
        border-color: #fecaca;
    }

    body.dark-theme .status-rejected {
        background-color: #7f1d1d;
        color: #f87171;
        border-color: #991b1b;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.3rem;
    }

    .action-btn {
        padding: 0.4rem 0.8rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .btn-accept {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-accept:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
    }

    .btn-reject {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .btn-reject:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
    }

    /* Student info styling */
    .student-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .student-name {
        font-weight: 600;
        color: #1e293b;
        line-height: 1.4;
    }

    body.dark-theme .student-name {
        color: #f1f5f9;
    }

    .student-id {
        font-size: 0.8rem;
        color: #64748b;
        line-height: 1.4;
    }

    body.dark-theme .student-id {
        color: #94a3b8;
    }

    /* Book info styling */
    .book-info {
        max-width: 300px;
    }

    .book-title {
        font-weight: 500;
        color: #1e293b;
        margin-bottom: 0.125rem;
    }

    body.dark-theme .book-title {
        color: #f1f5f9;
    }

    .book-author {
        font-size: 0.8rem;
        color: #64748b;
        font-style: italic;
    }

    body.dark-theme .book-author {
        color: #94a3b8;
    }

    /* Table column widths */
    .requests-table th:nth-child(1),
    .requests-table td:nth-child(1) {
        min-width: 200px;
        width: 18%;
    }

    .requests-table th:nth-child(2),
    .requests-table td:nth-child(2) {
        min-width: 250px;
        width: 28%;
    }

    .requests-table th:nth-child(3),
    .requests-table td:nth-child(3) {
        min-width: 120px;
        width: 12%;
    }

    .requests-table th:nth-child(4),
    .requests-table td:nth-child(4) {
        min-width: 110px;
        width: 12%;
    }

    .requests-table th:nth-child(5),
    .requests-table td:nth-child(5) {
        min-width: 140px;
        width: 15%;
    }

    .requests-table th:nth-child(6),
    .requests-table td:nth-child(6) {
        min-width: 150px;
        width: 15%;
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
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.8rem;
    }

    .dark-theme .searchable-select-input {
        border-color: #475569;
        background-color: #1f2937;
        color: #f1f5f9;
    }

    .searchable-select-input::placeholder {
        color: #94a3b8;
    }

    .dark-theme .searchable-select-input::placeholder {
        color: #64748b;
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
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        z-index: 50;
        display: none;
        margin-top: 0.25rem;
    }

    .dark-theme .searchable-select-dropdown {
        background-color: #1f2937;
        border-color: #374151;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
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

    .searchable-select-option:last-child {
        border-bottom: none;
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

    .searchable-select-option.no-results {
        color: #64748b;
        cursor: default;
        font-style: italic;
        padding: 1rem;
        text-align: center;
    }

    .searchable-select-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
    }

    /* Stats Cards Enhancement */
    .stats-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
    }

    body.dark-theme .stats-card {
        border-color: #334155;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }

    body.dark-theme .stats-card:hover {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        border-color: #475569;
    }

    /* Card Enhancement */
    .card {
        border-radius: 1rem;
        background: white;
        border: 1px solid #e2e8f0;
    }

    body.dark-theme .card {
        background: #1e293b;
        border-color: #334155;
    }

    /* Search Bar Enhancement */
    #searchInput {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e2e8f0;
        color: #1e293b;
        background-color: white;
    }

    body.dark-theme #searchInput {
        color: #f1f5f9;
        background-color: #1e293b;
    }

    #searchInput:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    body.dark-theme #searchInput:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
    }

    /* Filter Select Enhancement */
    .filter-select {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #e2e8f0;
        color: #1e293b;
        background-color: white;
    }

    body.dark-theme .filter-select {
        color: #f1f5f9;
        background-color: #1e293b;
    }

    .filter-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    body.dark-theme .filter-select:focus {
        border-color: #60a5fa;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
    }

    /* Create Request Button Enhancement */
    #createRequestBtn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }

    #createRequestBtn:hover {
        transform: translateY(-1px);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }

    /* Empty State Styling */
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
    }

    .empty-state-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.4;
        color: #64748b;
    }

    body.dark-theme .empty-state-icon {
        color: #94a3b8;
    }

    /* Selected Items Display */
    #selectedItems .border {
        border: 2px solid #e2e8f0;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    body.dark-theme #selectedItems .border {
        border-color: #475569;
    }

    /* Modal Button Enhancement */
    #confirmCreateRequest {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        transition: all 0.3s ease;
    }

    #confirmCreateRequest:hover:not(:disabled) {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }

    #confirmCreateRequest:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Text color fixes for consistency */
    .text-primary {
        color: #1e293b;
    }

    body.dark-theme .text-primary {
        color: #f1f5f9;
    }

    .text-secondary {
        color: #64748b;
    }

    body.dark-theme .text-secondary {
        color: #94a3b8;
    }

    /* Modal title colors */
    #createRequestModal h3 {
        color: #1e293b;
    }

    body.dark-theme #createRequestModal h3 {
        color: #f1f5f9;
    }

    /* Label colors */
    label.text-primary {
        color: #1e293b;
    }

    body.dark-theme label.text-primary {
        color: #f1f5f9;
    }

    /* Required asterisk color */
    .text-red-500 {
        color: #ef4444;
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
        <div class="p-0 overflow-hidden card">
            <div class="overflow-x-auto">
                <table class="w-full requests-table">
                    <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Student</th>
                        <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Book</th>
                        <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Request Date</th>
                        <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Status</th>
                        <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Processed By</th>
                        <th class="px-4 py-2 text-xs font-medium text-left text-secondary">Actions</th>
                    </tr>
                    </thead>
                    <tbody id="requestsTableBody">
                    <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination - Keeping original style -->
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
                            document.getElementById('paginationContainer').innerHTML = data.pagination;
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