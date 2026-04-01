@extends('student.layouts.app')

@section('title', 'My Books')

@push('styles')
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
            min-height: 100vh;
        }

        /* Light Theme */
        body.light-theme {
            background-color: #f9fafb;
            color: #0f172a;
        }

        body.light-theme .card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }

        body.light-theme .text-primary {
            color: #0f172a;
        }

        body.light-theme .text-secondary {
            color: #64748b;
        }

        body.light-theme .text-muted {
            color: #64748b;
        }

        body.light-theme .soft-bg {
            background-color: #f1f5f9;
        }

        /* Dark Theme */
        body.dark-theme {
            background-color: #0f172a;
            color: #e2e8f0;
        }

        body.dark-theme .card {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
        }

        body.dark-theme .text-primary {
            color: #f1f5f9;
        }

        body.dark-theme .text-secondary {
            color: #94a3b8;
        }

        body.dark-theme .text-muted {
            color: #94a3b8;
        }

        body.dark-theme .soft-bg {
            background-color: #1e293b;
        }

        body.dark-theme .text-danger {
            color: #f87171;
        }

        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 0;
            overflow-y: auto;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
        }

        .page-title p {
            color: var(--text-secondary);
            font-size: 0.75rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .stat-card {
            padding: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            border-left: 3px solid;
        }

        body.light-theme .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
        }

        /* Stat card colors */
        .stat-card.issued {
            border-left-color: #2563eb;
        }

        .stat-card.borrowed {
            border-left-color: #f59e0b;
        }

        .stat-card.overdue {
            border-left-color: #ef4444;
        }

        .stat-card.fine {
            border-left-color: #10b981;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .stat-icon {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Light theme stat icons */
        body.light-theme .stat-card.issued .stat-icon {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.light-theme .stat-card.borrowed .stat-icon {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        body.light-theme .stat-card.overdue .stat-icon {
            background-color: #fee2e2;
            color: #ef4444;
        }

        body.light-theme .stat-card.fine .stat-icon {
            background-color: #dcfce7;
            color: #10b981;
        }

        /* Dark theme stat icons */
        body.dark-theme .stat-card.issued .stat-icon {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        body.dark-theme .stat-card.borrowed .stat-icon {
            background-color: #78350f;
            color: #fbbf24;
        }

        body.dark-theme .stat-card.overdue .stat-icon {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .stat-card.fine .stat-icon {
            background-color: #064e3b;
            color: #34d399;
        }

        .stat-number {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Search and Filters */
        .search-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            width: 100%;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            align-items: center;
        }

        body.light-theme .search-filter-container {
            background: white;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .search-filter-container {
            background: #1e293b;
            border: 1px solid #334155;
        }

        .search-box {
            flex: 0 1 350px;
            width: min(100%, 430px);
            min-width: 260px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.25rem;
            border-radius: 0.375rem;
            border: 1px solid;
            font-size: 0.75rem;
            transition: all 0.3s ease;
        }

        body.light-theme .search-input {
            background-color: #f8fafc;
            border-color: #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .search-input {
            background-color: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
        }

        .dark-theme .search-icon {
            color: #94a3b8;
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            width: min(100%, 760px);
        }

        .entries-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .entries-label,
        .entries-suffix,
        .pagination-page-summary {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .entries-select {
            min-width: 5.25rem;
        }

        .reset-filter-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        body.light-theme .reset-filter-btn {
            background: #f8fafc;
            border-color: #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .reset-filter-btn {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .reset-filter-btn:hover {
            border-color: #3b82f6;
            transform: translateY(-1px);
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            cursor: pointer;
            appearance: none;
            min-width: 120px;
            transition: all 0.3s ease;
        }

        body.light-theme .filter-select {
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .filter-select {
            background: #0f172a url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border: 1px solid #334155;
            color: #e2e8f0;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        /* Books Table */
        .books-table-container {
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        body.light-theme .books-table-container {
            background: white;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .books-table-container {
            background: #1e293b;
            border: 1px solid #334155;
        }

        .books-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 800px;
        }

        .books-table thead {
            border-bottom: 1px solid;
        }

        body.light-theme .books-table thead {
            border-color: #e5e7eb;
        }

        body.dark-theme .books-table thead {
            border-color: #334155;
        }

        .books-table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .books-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid;
        }

        body.light-theme .books-table td {
            border-color: #f1f5f9;
        }

        body.dark-theme .books-table td {
            border-color: #334155;
        }

        .books-table tbody tr:last-child td {
            border-bottom: none;
        }

        .books-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        body.dark-theme .books-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge.overdue {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .status-badge.issued {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .status-badge.due-soon {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status-badge.returned {
            background-color: #dcfce7;
            color: #16a34a;
        }

        /* Fine Status Badges */
        .fine-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .fine-status-badge.paid {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .fine-status-badge.unpaid {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .fine-status-badge.waived {
            background-color: #fef3c7;
            color: #d97706;
        }

        .fine-status-badge.none {
            background-color: #f1f5f9;
            color: #64748b;
        }

        body.dark-theme .status-badge.overdue {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .status-badge.issued {
            background-color: #1e3a8a;
            color: #93c5fd;
        }

        body.dark-theme .status-badge.due-soon {
            background-color: #78350f;
            color: #fcd34d;
        }

        body.dark-theme .status-badge.returned {
            background-color: #14532d;
            color: #86efac;
        }

        body.dark-theme .fine-status-badge.paid {
            background-color: #14532d;
            color: #86efac;
        }

        body.dark-theme .fine-status-badge.unpaid {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .fine-status-badge.waived {
            background-color: #78350f;
            color: #fbbf24;
        }

        body.dark-theme .fine-status-badge.none {
            background-color: #334155;
            color: #94a3b8;
        }

        /* Fine Amount */
        .fine-amount {
            font-weight: 600;
            font-size: 0.75rem;
        }

        body.light-theme .fine-amount {
            color: #dc2626;
        }

        body.dark-theme .fine-amount {
            color: #f87171;
        }

        /* Book Info */
        .book-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.125rem;
        }

        .book-isbn {
            font-size: 0.65rem;
            color: var(--text-muted);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--text-secondary);
            display: none;
        }

        .empty-state-icon {
            margin-bottom: 0.75rem;
            opacity: 0.5;
            color: var(--text-secondary);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-top: 1px solid;
            margin-top: 0;
            flex-wrap: wrap;
            gap: 1rem;
        }

        body.light-theme .pagination-container {
            border-color: #e5e7eb;
            background: white;
        }

        body.dark-theme .pagination-container {
            border-color: #334155;
            background: #1e293b;
        }

        .pagination-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        #pageNumbers {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pagination-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pagination-btn.page-number {
            min-width: 2.5rem;
            padding-inline: 0.625rem;
        }

        .pagination-btn.nav-btn {
            white-space: nowrap;
        }

        body.light-theme .pagination-btn {
            background: white;
            border-color: #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .pagination-btn {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .pagination-btn:hover:not(:disabled) {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        .pagination-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 0.75rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .search-filter-container {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .search-box,
            .filters-container,
            .entries-control {
                width: 100%;
            }

            .filter-select {
                min-width: 100%;
            }

            .entries-control {
                margin-left: 0;
                justify-content: flex-start;
            }

            .books-table {
                display: block;
                overflow-x: auto;
            }

            .books-table th,
            .books-table td {
                padding: 0.5rem 0.75rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .pagination-container {
                align-items: stretch;
            }

            .pagination-controls {
                width: 100%;
                justify-content: space-between;
            }

            #pageNumbers {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')

    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>My Books</h1>
                <p class="text-secondary">View your issued and returned books</p>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card card issued">
                <div class="stat-header">
                    <h3 class="stat-title">Total Issued</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-number" id="totalIssued">{{ $totalIssued }}</div>
                <div class="stat-label">Total books issued</div>
            </div>

            <div class="stat-card card borrowed">
                <div class="stat-header">
                    <h3 class="stat-title">Currently Borrowed</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-number" id="currentlyBorrowed">{{ $currentlyBorrowed }}</div>
                <div class="stat-label">Books currently with you</div>
            </div>

            <div class="stat-card card overdue">
                <div class="stat-header">
                    <h3 class="stat-title">Overdue Books</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                </div>
                <div class="stat-number" id="overdueBooks">{{ $overdueBooks }}</div>
                <div class="stat-label">Books past due date</div>
            </div>

            <div class="stat-card card fine">
                <div class="stat-header">
                    <h3 class="stat-title">Pending Fine</h3>
                    <div class="stat-icon">
                        ₹
                    </div>
                </div>
                <div class="stat-number" id="totalFine">₹{{ $totalFine }}</div>
                <div class="stat-label">Amount to be paid</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-filter-container">
            <div class="search-box">
                <div class="search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" class="search-input" id="searchInput"
                    placeholder="Search by book title, author, or ISBN">
            </div>

            <div class="filters-container">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="issued">Issued</option>
                    <option value="overdue">Overdue</option>
                    <option value="due-soon">Due Soon</option>
                    <option value="returned">Returned</option>
                </select>

                <select class="filter-select" id="fineStatusFilter">
                    <option value="all">All Fine Status</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="waived">Waived</option>
                </select>

                <select class="filter-select" id="categoryFilter">
                    <option value="all">All Categories</option>
                </select>

                <select class="filter-select" id="sortFilter">
                    <option value="due-date-asc">Due Date (Asc)</option>
                    <option value="due-date-desc">Due Date (Desc)</option>
                    <option value="issue-date">Issue Date</option>
                    <option value="fine-amount">Fine Amount</option>
                </select>

                <button type="button" class="reset-filter-btn" id="resetFiltersBtn">Reset</button>
            </div>

            <div class="entries-control">
                <label class="entries-label" for="entriesPerPage">Show</label>
                <select class="filter-select entries-select" id="entriesPerPage">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="entries-suffix">entries</span>
            </div>
        </div>

        <!-- Books Table -->
        <div class="books-table-container">
            <table class="books-table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine Amount</th>
                        <th>Fine Status</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody">
                    <!-- Books data will be loaded here by JavaScript -->
                </tbody>
            </table>
            <div id="emptyState" class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                        <path d="M9 9h6"></path>
                        <path d="M9 13h6"></path>
                    </svg>
                </div>
                <h3 style="margin-bottom: 0.25rem; font-size: 1rem; font-weight: 600;">No books found</h3>
                <p class="text-secondary">Try adjusting your search or filters</p>
            </div>
            <div id="paginationContainer" class="pagination-container" style="display: none;">
                <div class="pagination-info">
                    <span>Showing <span id="startRecord">1</span> to <span id="endRecord">10</span> of <span id="totalRecords">0</span> results</span>
                    <span class="pagination-page-summary">Page <span id="currentPageLabel">1</span> of <span id="totalPagesLabel">1</span></span>
                </div>
                <div class="pagination-controls">
                    <button type="button" class="pagination-btn nav-btn" id="prevBtn" onclick="previousPage()">← Previous</button>
                    <div id="pageNumbers"></div>
                    <button type="button" class="pagination-btn nav-btn" id="nextBtn" onclick="nextPage()">Next →</button>
                </div>
            </div>
        </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Book data from backend
        const booksData = {!! $issuedBooksJson !!};
        const DEFAULT_BOOKS_PER_PAGE = 10;
        const ALLOWED_PAGE_SIZES = [10, 20, 50, 100];
        let booksPerPage = getInitialBooksPerPage();
        let currentPage = getInitialPage();
        let filteredBooks = [];

        function getInitialBooksPerPage() {
            const perPage = Number(new URLSearchParams(window.location.search).get('per_page'));
            return ALLOWED_PAGE_SIZES.includes(perPage) ? perPage : DEFAULT_BOOKS_PER_PAGE;
        }

        function getInitialPage() {
            const page = Number(new URLSearchParams(window.location.search).get('page'));
            return Number.isInteger(page) && page > 0 ? page : 1;
        }

        function getTotalPages() {
            return Math.max(1, Math.ceil(filteredBooks.length / booksPerPage));
        }

        function syncPaginationState() {
            const params = new URLSearchParams(window.location.search);
            params.set('page', String(currentPage));
            params.set('per_page', String(booksPerPage));

            const queryString = params.toString();
            const nextUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
            window.history.replaceState({}, '', nextUrl);
        }

        function updatePaginationInfo(startRecord, endRecord, totalRecords, totalPages) {
            document.getElementById('startRecord').textContent = startRecord;
            document.getElementById('endRecord').textContent = endRecord;
            document.getElementById('totalRecords').textContent = totalRecords;
            document.getElementById('currentPageLabel').textContent = totalRecords === 0 ? 0 : currentPage;
            document.getElementById('totalPagesLabel').textContent = totalRecords === 0 ? 0 : totalPages;
        }

        // Render books table with pagination
        function renderBooks(books, { preservePage = false } = {}) {
            const tableBody = document.getElementById('booksTableBody');
            const emptyState = document.getElementById('emptyState');
            const paginationContainer = document.getElementById('paginationContainer');

            filteredBooks = books;

            if (!preservePage) {
                currentPage = 1;
            }

            currentPage = Math.min(Math.max(currentPage, 1), getTotalPages());
            updateStats(books);

            if (books.length === 0) {
                tableBody.innerHTML = '';
                emptyState.style.display = 'block';
                paginationContainer.style.display = 'none';
                updatePaginationInfo(0, 0, 0, 0);
                syncPaginationState();
                return;
            }

            emptyState.style.display = 'none';

            // Display current page
            displayPage(currentPage);

            paginationContainer.style.display = 'flex';
            updatePagination();
        }

        // Display a specific page
        function displayPage(page) {
            const tableBody = document.getElementById('booksTableBody');
            const totalPages = getTotalPages();
            const start = (page - 1) * booksPerPage;
            const end = start + booksPerPage;
            const booksToShow = filteredBooks.slice(start, end);

            tableBody.innerHTML = booksToShow.map(book => `
            <tr>
                <td>
                    <div class="book-title">${book.title}</div>
                    <div class="book-isbn">${book.isbn}</div>
                </td>
                <td>${book.author}</td>
                <td>${book.issueDate}</td>
                <td>${book.dueDate}</td>
                <td>${book.returnDate ? book.returnDate : '-'}</td>
                <td>
                    <span class="status-badge ${book.status}">
                        ${book.status === 'overdue' ? 'Overdue' : 
                          book.status === 'issued' ? 'Issued' : 
                          book.status === 'due-soon' ? 'Due Soon' : 'Returned'}
                    </span>
                </td>
                <td>
                    ${book.fine !== 'No Fine' ? 
                      `<span class="fine-amount">${book.fine}</span>` : 
                      '<span class="text-muted">No Fine</span>'}
                </td>
                <td>
                    <span class="fine-status-badge ${book.fineStatus}">
                        ${book.fineStatus === 'paid' ? 'Paid' : 
                          book.fineStatus === 'unpaid' ? 'Unpaid' : 
                          book.fineStatus === 'waived' ? 'Waived' : 'None'}
                    </span>
                </td>
            </tr>
        `).join('');

            // Update pagination info
            const startRecord = start + 1;
            const endRecord = Math.min(end, filteredBooks.length);
            updatePaginationInfo(startRecord, endRecord, filteredBooks.length, totalPages);
        }

        function getVisiblePageItems(totalPages, activePage) {
            if (totalPages <= 7) {
                return Array.from({ length: totalPages }, (_, index) => index + 1);
            }

            const pages = [1];
            let start = Math.max(2, activePage - 1);
            let end = Math.min(totalPages - 1, activePage + 1);

            if (activePage <= 4) {
                start = 2;
                end = 5;
            }

            if (activePage >= totalPages - 3) {
                start = totalPages - 4;
                end = totalPages - 1;
            }

            if (start > 2) {
                pages.push('ellipsis-start');
            }

            for (let page = start; page <= end; page++) {
                pages.push(page);
            }

            if (end < totalPages - 1) {
                pages.push('ellipsis-end');
            }

            pages.push(totalPages);

            return pages;
        }

        // Update pagination controls
        function updatePagination() {
            const totalPages = getTotalPages();
            const pageNumbersContainer = document.getElementById('pageNumbers');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            // Update prev/next button states
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages;

            // Generate page numbers
            pageNumbersContainer.innerHTML = '';

            getVisiblePageItems(totalPages, currentPage).forEach(item => {
                if (typeof item === 'string') {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    ellipsis.setAttribute('aria-hidden', 'true');
                    pageNumbersContainer.appendChild(ellipsis);
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'pagination-btn page-number' + (item === currentPage ? ' active' : '');
                btn.textContent = item;
                btn.onclick = () => goToPage(item);
                pageNumbersContainer.appendChild(btn);
            });

            syncPaginationState();
        }

        // Navigation functions
        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                displayPage(currentPage);
                updatePagination();
            }
        }

        function nextPage() {
            const totalPages = getTotalPages();
            if (currentPage < totalPages) {
                currentPage++;
                displayPage(currentPage);
                updatePagination();
            }
        }

        function goToPage(page) {
            currentPage = page;
            displayPage(currentPage);
            updatePagination();
        }

        function handleEntriesPerPageChange(event) {
            const nextPageSize = Number(event.target.value);

            if (!ALLOWED_PAGE_SIZES.includes(nextPageSize) || nextPageSize === booksPerPage) {
                return;
            }

            const firstVisibleIndex = filteredBooks.length > 0 ? (currentPage - 1) * booksPerPage : 0;
            booksPerPage = nextPageSize;
            currentPage = Math.floor(firstVisibleIndex / booksPerPage) + 1;

            renderBooks(filteredBooks, { preservePage: true });
        }

        function getNumericFineAmount(fineValue) {
            if (!fineValue || fineValue === 'No Fine') {
                return 0;
            }

            return parseFloat(String(fineValue).replace(/[^\d.]/g, '')) || 0;
        }

        function formatCurrencyAmount(amount) {
            const normalizedAmount = Number(amount) || 0;
            return `₹${normalizedAmount.toFixed(2).replace(/\.00$/, '')}`;
        }

        // Update statistics based on filtered books
        function updateStats(books) {
            const totalIssued = booksData.length;
            const currentlyBorrowed = books.filter(book => book.status !== 'returned').length;
            const overdueBooks = books.filter(book => book.status === 'overdue').length;
            const totalFine = books.reduce((sum, book) => {
                if (book.fine !== 'No Fine' && book.fineStatus === 'unpaid') {
                    return sum + getNumericFineAmount(book.fine);
                }
                return sum;
            }, 0);

            document.getElementById('totalIssued').textContent = totalIssued;
            document.getElementById('currentlyBorrowed').textContent = currentlyBorrowed;
            document.getElementById('overdueBooks').textContent = overdueBooks;
            document.getElementById('totalFine').textContent = formatCurrencyAmount(totalFine);
        }

        // Filter and search functionality
        function filterBooks() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const fineStatusFilter = document.getElementById('fineStatusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            const sortFilter = document.getElementById('sortFilter').value;

            // Filter books based on criteria
            let results = booksData.filter(book => {
                // Search filter
                const matchesSearch = searchTerm === '' ||
                    (book.title && book.title.toLowerCase().includes(searchTerm)) ||
                    (book.author && book.author.toLowerCase().includes(searchTerm)) ||
                    (book.isbn && book.isbn.toLowerCase().includes(searchTerm));

                // Status filter
                const matchesStatus = statusFilter === 'all' || book.status === statusFilter;

                // Fine status filter
                const matchesFineStatus = fineStatusFilter === 'all' || book.fineStatus === fineStatusFilter;

                // Category filter
                const matchesCategory = categoryFilter === 'all' || 
                    (book.category && book.category.toLowerCase() === categoryFilter.toLowerCase());

                return matchesSearch && matchesStatus && matchesFineStatus && matchesCategory;
            });

            // Sorting
            results.sort((a, b) => {
                switch (sortFilter) {
                    case 'due-date-asc':
                        return new Date(a.dueDate || 0) - new Date(b.dueDate || 0);
                    case 'due-date-desc':
                        return new Date(b.dueDate || 0) - new Date(a.dueDate || 0);
                    case 'issue-date':
                        return new Date(b.issueDate || 0) - new Date(a.issueDate || 0);
                    case 'fine-amount':
                        const fineA = getNumericFineAmount(a.fine);
                        const fineB = getNumericFineAmount(b.fine);
                        return fineB - fineA;
                    default:
                        return 0;
                }
            });

            renderBooks(results);
        }

        // Event listeners for filters
        document.addEventListener('DOMContentLoaded', function() {
            // Get unique categories from backend data
            function populateCategories() {
                const categories = [...new Set(booksData.map(book => book.category).filter(Boolean))].sort();
                const categoryFilter = document.getElementById('categoryFilter');
                
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category.charAt(0).toUpperCase() + category.slice(1);
                    categoryFilter.appendChild(option);
                });
            }

            function resetFilters() {
                if (searchInput) searchInput.value = '';
                if (statusFilter) statusFilter.value = 'all';
                if (fineStatusFilter) fineStatusFilter.value = 'all';
                if (categoryFilter) categoryFilter.value = 'all';
                if (sortFilter) sortFilter.value = 'due-date-asc';
                filterBooks();
            }

            // Attach event listeners
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const fineStatusFilter = document.getElementById('fineStatusFilter');
            const categoryFilter = document.getElementById('categoryFilter');
            const sortFilter = document.getElementById('sortFilter');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const entriesPerPage = document.getElementById('entriesPerPage');

            if (searchInput) searchInput.addEventListener('input', filterBooks);
            if (statusFilter) statusFilter.addEventListener('change', filterBooks);
            if (fineStatusFilter) fineStatusFilter.addEventListener('change', filterBooks);
            if (categoryFilter) categoryFilter.addEventListener('change', filterBooks);
            if (sortFilter) sortFilter.addEventListener('change', filterBooks);
            if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetFilters);
            if (entriesPerPage) {
                entriesPerPage.value = String(booksPerPage);
                entriesPerPage.addEventListener('change', handleEntriesPerPageChange);
            }

            // Initial render
            populateCategories();
            renderBooks(booksData, { preservePage: true });
        });
    </script>
@endpush
