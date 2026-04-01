@extends('student.layouts.app')

@section('title', 'My Fines')

@push('styles')
    <style>
        /* ===== MY FINES PAGE STYLES ===== */
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-color: #64748b;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --success-color: #10b981;
            --info-color: #3b82f6;
            --border-color: #e5e7eb;
            --card-bg: #ffffff;
            --body-bg: #f9fafb;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            --radius: 8px;
            --radius-sm: 6px;
        }

        body.dark-theme {
            --primary-color: #60a5fa;
            --primary-hover: #93c5fd;
            --secondary-color: #94a3b8;
            --danger-color: #f87171;
            --warning-color: #fbbf24;
            --success-color: #34d399;
            --info-color: #60a5fa;
            --border-color: #334155;
            --card-bg: #1e293b;
            --body-bg: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Page Header */
        .page-header {
            margin-bottom: 0.75rem;
        }

        .page-header h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.125rem;
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 0.8rem;
        }

        /* ===== STATS CARDS - Consistent with other pages ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-card {
            padding: 0.75rem;
            border-radius: var(--radius);
            transition: all 0.3s ease;
            border-left: 3px solid;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .stat-card.outstanding {
            border-left-color: #ef4444;
        }

        .stat-card.paid {
            border-left-color: #10b981;
        }

        .stat-card.waived {
            border-left-color: #f59e0b;
        }

        .stat-card.overdue {
            border-left-color: #8b5cf6;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.375rem;
        }

        .stat-title {
            font-size: 0.7rem;
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

        body.light-theme .stat-card.outstanding .stat-icon {
            background-color: #fee2e2;
            color: #ef4444;
        }

        body.light-theme .stat-card.paid .stat-icon {
            background-color: #dcfce7;
            color: #10b981;
        }

        body.light-theme .stat-card.waived .stat-icon {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        body.light-theme .stat-card.overdue .stat-icon {
            background-color: #f5f3ff;
            color: #8b5cf6;
        }

        body.dark-theme .stat-card.outstanding .stat-icon {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .stat-card.paid .stat-icon {
            background-color: #064e3b;
            color: #34d399;
        }

        body.dark-theme .stat-card.waived .stat-icon {
            background-color: #78350f;
            color: #fbbf24;
        }

        body.dark-theme .stat-card.overdue .stat-icon {
            background-color: #4c1d95;
            color: #c4b5fd;
        }

        .stat-number {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
            color: var(--text-primary);
        }

        .stat-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        /* ===== SEARCH & FILTER BAR - Consistent with other pages ===== */
        .search-filter-container {
            width: 100%;
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            border-radius: var(--radius);
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        .search-filter-inner {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            width: 100%;
            align-items: center;
        }

        .search-box {
            flex: 0 1 410px;
            width: min(100%, 410px);
            min-width: 260px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.375rem 0.5rem 0.375rem 1.75rem;
            border-radius: var(--radius-sm);
            border: 1px solid;
            font-size: 0.8rem;
            transition: all 0.3s ease;
            background-color: var(--body-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-icon {
            position: absolute;
            left: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            flex: 1 1 auto;
            min-width: 0;
            align-items: center;
        }

        .entries-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            flex: 0 0 auto;
            margin-left: auto;
        }

        .entries-label,
        .entries-suffix,
        .pagination-page-summary {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .entries-select {
            min-width: 5.25rem;
        }

        .filter-select {
            padding: 0.375rem 1.75rem 0.375rem 0.5rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            cursor: pointer;
            appearance: none;
            min-width: 130px;
            transition: all 0.3s ease;
            background: var(--body-bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        body.dark-theme .filter-select {
            background: var(--body-bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
        }

        .reset-filter-btn {
            padding: 0.375rem 0.875rem;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            background: var(--body-bg);
            color: var(--text-primary);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .reset-filter-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-1px);
        }

        /* Fines Table */
        .fines-table-container {
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 0.75rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        .fines-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 700px;
        }

        .fines-table thead {
            border-bottom: 1px solid var(--border-color);
            background: var(--body-bg);
        }

        .fines-table th {
            padding: 0.5rem 0.75rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .fines-table td {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .fines-table tbody tr:last-child td {
            border-bottom: none;
        }

        .fines-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        body.dark-theme .fines-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Book Title */
        .book-title {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.8rem;
        }

        /* Fine Reason with Soft Highlight */
        .fine-reason {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .fine-reason.overdue {
            /* background-color: rgba(239, 68, 68, 0.08); */
            color: #dc2626;
            /* border-left: 3px solid #ef4444; */
        }

        .fine-reason.damage {
            /* background-color: rgba(245, 158, 11, 0.08); */
            color: #d97706;
            /* border-left: 3px solid #f59e0b; */
        }

        .fine-reason.fair {
            /* background-color: rgba(59, 130, 246, 0.08); */
            color: #2563eb;
            /* border-left: 3px solid #3b82f6; */
        }

        .fine-reason.lost {
            /* background-color: rgba(139, 92, 246, 0.08); */
            color: #7c3aed;
            /* border-left: 3px solid #8b5cf6; */
        }

        .fine-reason.other {
            background-color: rgba(100, 116, 139, 0.08);
            color: var(--text-secondary);
            border-left: 3px solid var(--border-color);
        }

        body.dark-theme .fine-reason.overdue {
            background-color: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }

        body.dark-theme .fine-reason.damage {
            background-color: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
        }

        body.dark-theme .fine-reason.fair {
            background-color: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
        }

        body.dark-theme .fine-reason.lost {
            background-color: rgba(139, 92, 246, 0.15);
            color: #c4b5fd;
        }

        body.dark-theme .fine-reason.other {
            background-color: rgba(100, 116, 139, 0.15);
            color: var(--text-muted);
        }

        .fine-reason-icon {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        /* Fine Amount */
        .fine-amount {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.8rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-badge.unpaid {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .status-badge.paid {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-badge.waived {
            background-color: #fef3c7;
            color: #d97706;
        }

        .status-badge.pending {
            background-color: #f0f4f8;
            color: #475569;
        }

        body.dark-theme .status-badge.unpaid {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .status-badge.paid {
            background-color: #14532d;
            color: #86efac;
        }

        body.dark-theme .status-badge.waived {
            background-color: #78350f;
            color: #fbbf24;
        }

        body.dark-theme .status-badge.pending {
            background-color: #1e293b;
            color: #cbd5e1;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem 0.75rem;
            color: var(--text-muted);
            display: none;
        }

        .empty-state-icon {
            margin-bottom: 0.75rem;
            opacity: 0.5;
            color: var(--text-secondary);
        }

        .empty-state h3 {
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .empty-state p {
            font-size: 0.8rem;
        }

        /* Pagination Styles */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-top: 1px solid var(--border-color);
            margin-top: 0.75rem;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .pagination-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        #paginationNumbers {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .pagination-btn {
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--border-color);
            background: var(--card-bg);
            color: var(--text-primary);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            min-width: 36px;
            text-align: center;
        }

        .pagination-btn.page-number {
            min-width: 2.5rem;
            padding-inline: 0.625rem;
        }

        .pagination-btn.nav-btn {
            white-space: nowrap;
        }

        .pagination-btn:hover:not(:disabled) {
            background: var(--body-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.125rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .search-filter-container {
                padding: 0.5rem;
            }

            .search-filter-inner {
                flex-direction: column;
                align-items: stretch;
                width: 100%;
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

            .fines-table {
                display: block;
                overflow-x: auto;
            }

            .fines-table th,
            .fines-table td {
                padding: 0.375rem 0.5rem;
            }

            .mobile-row {
                flex-direction: column;
                gap: 0.125rem;
            }

            .pagination-container {
                align-items: stretch;
            }

            .pagination-controls {
                width: 100%;
                justify-content: space-between;
            }

            #paginationNumbers {
                flex: 1;
                justify-content: center;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
@endpush

@section('content')
    <div class="fines-container">
        <!-- Header -->
        <div class="page-header">
            <h1>My Fines</h1>
            <p>View and track your library fines</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card card outstanding">
                <div class="stat-header">
                    <h3 class="stat-title">Outstanding</h3>
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
                <div class="stat-number" id="outstandingAmount">₹{{ $outstandingAmount }}</div>
                <div class="stat-label" id="outstandingLabel">0 pending fines</div>
            </div>

            <div class="stat-card card paid">
                <div class="stat-header">
                    <h3 class="stat-title">Paid</h3>
                    <div class="stat-icon">
                        ₹
                    </div>
                </div>
                <div class="stat-number" id="paidAmount">₹{{ $paidAmount }}</div>
                <div class="stat-label" id="paidLabel">0 paid fines</div>
            </div>

            <div class="stat-card card waived">
                <div class="stat-header">
                    <h3 class="stat-title">Waived</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M9 12 11 14 15 10"></path>
                            <path d="M12 3a12 12 0 0 0 8.5 3A12 12 0 0 1 12 21 12 12 0 0 1 3.5 6 12 12 0 0 0 12 3"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-number" id="waivedAmount">₹{{ $waivedAmount }}</div>
                <div class="stat-label" id="waivedLabel">0 waived fines</div>
            </div>

            <div class="stat-card card overdue">
                <div class="stat-header">
                    <h3 class="stat-title">Overdue Books</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="stat-number" id="overdueCount">{{ $overdueCount }}</div>
                <div class="stat-label">Overdue books</div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="search-filter-container">
            <div class="search-filter-inner">
                <div class="search-box">
                    <div class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by book title...">
                </div>

                <div class="filters-container">
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Fines</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="waived">Waived</option>
                    </select>

                    <select class="filter-select" id="reasonFilter">
                        <option value="all">All Reasons</option>
                        <option value="overdue">Overdue</option>
                        <option value="damage">Damage</option>
                        <option value="fair">Fair Condition</option>
                        <option value="lost">Lost</option>
                    </select>

                    <select class="filter-select" id="timeFilter">
                        <option value="all">All Time</option>
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
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
        </div>

        <!-- Fines Table -->
        <div class="fines-table-container">
            <table class="fines-table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Fine Reason</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Fine Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="finesTableBody">
                    <!-- Data will be populated here by JavaScript -->
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-container" id="paginationContainer" style="display: none;">
                <div class="pagination-info">
                    <span>Showing <span id="paginationStart">1</span> to <span id="paginationEnd">10</span> of <span id="paginationTotal">0</span> results</span>
                    <span class="pagination-page-summary">Page <span id="paginationCurrentPage">1</span> of <span id="paginationTotalPages">1</span></span>
                </div>
                <div class="pagination-controls">
                    <button type="button" class="pagination-btn nav-btn" id="paginationPrev" onclick="previousPage()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </button>
                    <div id="paginationNumbers"></div>
                    <button type="button" class="pagination-btn nav-btn" id="paginationNext" onclick="nextPage()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state">
                <div class="empty-state-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        <line x1="11" y1="8" x2="11" y2="12"></line>
                        <line x1="11" y1="16" x2="11.01" y2="16"></line>
                    </svg>
                </div>
                <h3>No fines found</h3>
                <p>Try adjusting your search or filters</p>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        // Fines data from backend
        const finesData = {!! $finesJson !!};

        // Get reason icon
        function getReasonIcon(reason) {
            switch (reason) {
                case 'overdue':
                    return `<svg class="fine-reason-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`;
                case 'damage':
                    return `<svg class="fine-reason-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
                case 'fair':
                    return `<svg class="fine-reason-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline><path d="M9 13h.01"></path><path d="M12 13h.01"></path><path d="M15 13h.01"></path></svg>`;
                case 'lost':
                    return `<svg class="fine-reason-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>`;
                default:
                    return `<svg class="fine-reason-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
            }
        }

        // Get reason text
        function getReasonText(reason) {
            switch (reason) {
                case 'overdue':
                    return 'Overdue';
                case 'damage':
                    return 'Damage';
                case 'fair':
                    return 'Fair Condition';
                case 'lost':
                    return 'Lost';
                default:
                    return 'Other';
            }
        }

        const DEFAULT_ITEMS_PER_PAGE = 10;
        const ALLOWED_PAGE_SIZES = [10, 20, 50, 100];
        let currentPage = getInitialPage();
        let itemsPerPage = getInitialItemsPerPage();
        let filteredFines = [];

        function getInitialItemsPerPage() {
            const perPage = Number(new URLSearchParams(window.location.search).get('per_page'));
            return ALLOWED_PAGE_SIZES.includes(perPage) ? perPage : DEFAULT_ITEMS_PER_PAGE;
        }

        function getInitialPage() {
            const page = Number(new URLSearchParams(window.location.search).get('page'));
            return Number.isInteger(page) && page > 0 ? page : 1;
        }

        function getTotalPages() {
            return Math.max(1, Math.ceil(filteredFines.length / itemsPerPage));
        }

        function syncPaginationState() {
            const params = new URLSearchParams(window.location.search);
            params.set('page', String(currentPage));
            params.set('per_page', String(itemsPerPage));

            const queryString = params.toString();
            const nextUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
            window.history.replaceState({}, '', nextUrl);
        }

        function updatePaginationInfo(startRecord, endRecord, totalRecords, totalPages) {
            document.getElementById('paginationStart').textContent = startRecord;
            document.getElementById('paginationEnd').textContent = endRecord;
            document.getElementById('paginationTotal').textContent = totalRecords;
            document.getElementById('paginationCurrentPage').textContent = totalRecords === 0 ? 0 : currentPage;
            document.getElementById('paginationTotalPages').textContent = totalRecords === 0 ? 0 : totalPages;
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

        function getNumericFineAmount(value) {
            return parseFloat(String(value ?? '').replace(/[^\d.]/g, '')) || 0;
        }

        function formatCurrencyAmount(amount) {
            const normalizedAmount = Number(amount) || 0;
            return `₹${normalizedAmount.toFixed(2).replace(/\.00$/, '')}`;
        }

        // Render fines table
        function renderFines(fines, { preservePage = false } = {}) {
            filteredFines = fines;
            const tableBody = document.getElementById('finesTableBody');
            const emptyState = document.getElementById('emptyState');
            const paginationContainer = document.getElementById('paginationContainer');

            if (!preservePage) {
                currentPage = 1;
            }

            currentPage = Math.min(Math.max(currentPage, 1), getTotalPages());
            updateStats(fines);

            if (fines.length === 0) {
                tableBody.innerHTML = '';
                emptyState.style.display = 'block';
                paginationContainer.style.display = 'none';
                updatePaginationInfo(0, 0, 0, 0);
                syncPaginationState();
                return;
            }

            emptyState.style.display = 'none';
            paginationContainer.style.display = 'flex';
            displayCurrentPage();
            updatePagination();
        }

        // Update pagination display
        function updatePagination() {
            const totalPages = getTotalPages();
            const paginationContainer = document.getElementById('paginationContainer');
            const numbersContainer = document.getElementById('paginationNumbers');
            const previousButton = document.getElementById('paginationPrev');
            const nextButton = document.getElementById('paginationNext');

            paginationContainer.style.display = filteredFines.length > 0 ? 'flex' : 'none';
            previousButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;

            numbersContainer.innerHTML = '';

            getVisiblePageItems(totalPages, currentPage).forEach(item => {
                if (typeof item === 'string') {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'pagination-ellipsis';
                    ellipsis.textContent = '...';
                    ellipsis.setAttribute('aria-hidden', 'true');
                    numbersContainer.appendChild(ellipsis);
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = `pagination-btn page-number${item === currentPage ? ' active' : ''}`;
                btn.textContent = item;
                btn.onclick = () => goToPage(item);
                numbersContainer.appendChild(btn);
            });

            syncPaginationState();
        }

        function handleEntriesPerPageChange(event) {
            const nextPageSize = Number(event.target.value);

            if (!ALLOWED_PAGE_SIZES.includes(nextPageSize) || nextPageSize === itemsPerPage) {
                return;
            }

            const firstVisibleIndex = filteredFines.length > 0 ? (currentPage - 1) * itemsPerPage : 0;
            itemsPerPage = nextPageSize;
            currentPage = Math.floor(firstVisibleIndex / itemsPerPage) + 1;

            renderFines(filteredFines, { preservePage: true });
        }

        // Go to specific page
        function goToPage(page) {
            const totalPages = getTotalPages();
            if (page < 1 || page > totalPages) return;

            currentPage = page;
            displayCurrentPage();
            updatePagination();
        }

        // Display current page data
        function displayCurrentPage() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageFines = filteredFines.slice(start, end);
            const totalPages = getTotalPages();

            renderPageFines(pageFines);
            updatePaginationInfo(start + 1, Math.min(end, filteredFines.length), filteredFines.length, totalPages);
        }

        // Render specific page fines
        function renderPageFines(fines) {
            const tableBody = document.getElementById('finesTableBody');

            tableBody.innerHTML = fines.map(fine => `
            <tr>
                <td>
                    <div class="book-title">${fine.bookTitle}</div>
                </td>
                <td>
                    <div class="fine-reason ${fine.fineReason}">
                        ${getReasonIcon(fine.fineReason)}
                        <span>${getReasonText(fine.fineReason)}</span>
                    </div>
                </td>
                <td>${fine.dueDate}</td>
                <td>${fine.daysOverdue}</td>
                <td class="fine-amount">${fine.fineAmount}</td>
                <td>
                    <span class="status-badge ${fine.status}">${fine.status.charAt(0).toUpperCase() + fine.status.slice(1)}</span>
                </td>
            </tr>
        `).join('');
        }

        // Next page
        function nextPage() {
            const totalPages = getTotalPages();
            if (currentPage < totalPages) {
                currentPage++;
                displayCurrentPage();
                updatePagination();
            }
        }

        // Previous page
        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                displayCurrentPage();
                updatePagination();
            }
        }

        // Update statistics based on filtered fines
        function updateStats(fines) {
            const outstandingFines = fines.filter(fine => fine.status === 'unpaid');
            const paidFines = fines.filter(fine => fine.status === 'paid');
            const waivedFines = fines.filter(fine => fine.status === 'waived');
            const overdueBooks = fines.filter(fine => fine.fineReason === 'overdue' && fine.status === 'unpaid');

            const outstandingAmount = outstandingFines.reduce((sum, fine) => {
                return sum + getNumericFineAmount(fine.fineAmount);
            }, 0);

            const paidAmount = paidFines.reduce((sum, fine) => {
                return sum + getNumericFineAmount(fine.fineAmount);
            }, 0);

            const waivedAmount = waivedFines.reduce((sum, fine) => {
                return sum + getNumericFineAmount(fine.fineAmount);
            }, 0);

            document.getElementById('outstandingAmount').textContent = formatCurrencyAmount(outstandingAmount);
            document.getElementById('outstandingLabel').textContent = `${outstandingFines.length} pending ${outstandingFines.length === 1 ? 'fine' : 'fines'}`;
            
            document.getElementById('paidAmount').textContent = formatCurrencyAmount(paidAmount);
            document.getElementById('paidLabel').textContent = `${paidFines.length} paid ${paidFines.length === 1 ? 'fine' : 'fines'}`;
            
            document.getElementById('waivedAmount').textContent = formatCurrencyAmount(waivedAmount);
            document.getElementById('waivedLabel').textContent = `${waivedFines.length} waived ${waivedFines.length === 1 ? 'fine' : 'fines'}`;
            
            document.getElementById('overdueCount').textContent = overdueBooks.length;
        }

        // Filter and search functionality
        function filterFines({ preservePage = false } = {}) {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const reasonFilter = document.getElementById('reasonFilter').value;
            const timeFilter = document.getElementById('timeFilter').value;

            let nextFilteredFines = finesData.filter(fine => {
                // Search filter
                const matchesSearch = searchTerm === '' ||
                    fine.bookTitle.toLowerCase().includes(searchTerm);

                // Status filter
                const matchesStatus = statusFilter === 'all' || fine.status === statusFilter;

                // Reason filter
                const matchesReason = reasonFilter === 'all' || fine.fineReason === reasonFilter;

                // Time filter
                let matchesTime = true;
                if (timeFilter !== 'all') {
                    const days = parseInt(timeFilter);
                    const currentDate = new Date();
                    const fineDate = new Date(fine.lastUpdated);
                    const diffTime = Math.abs(currentDate - fineDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    matchesTime = diffDays <= days;
                }

                return matchesSearch && matchesStatus && matchesReason && matchesTime;
            });

            // Sort by due date (newest first)
            nextFilteredFines.sort((a, b) => {
                const dateA = parseDate(a.dueDate);
                const dateB = parseDate(b.dueDate);
                return dateB - dateA;
            });

            renderFines(nextFilteredFines, { preservePage });
        }

        function parseDate(dateString) {
            if (!dateString || dateString === 'N/A') {
                return new Date(0);
            }

            const parsedDate = new Date(dateString);
            if (!Number.isNaN(parsedDate.getTime())) {
                return parsedDate;
            }

            // Parse MMM DD, YYYY format
            const [month, day, year] = dateString.split(/[\s,]+/);
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthIndex = monthNames.indexOf(month);

            if (monthIndex === -1 || Number.isNaN(Number(day)) || Number.isNaN(Number(year))) {
                return new Date(0);
            }

            return new Date(year, monthIndex, parseInt(day));
        }

        function resetFilters() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const reasonFilter = document.getElementById('reasonFilter');
            const timeFilter = document.getElementById('timeFilter');

            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = 'all';
            if (reasonFilter) reasonFilter.value = 'all';
            if (timeFilter) timeFilter.value = 'all';

            filterFines();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const reasonFilter = document.getElementById('reasonFilter');
            const timeFilter = document.getElementById('timeFilter');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const entriesPerPage = document.getElementById('entriesPerPage');

            if (searchInput) searchInput.addEventListener('input', () => filterFines());
            if (statusFilter) statusFilter.addEventListener('change', () => filterFines());
            if (reasonFilter) reasonFilter.addEventListener('change', () => filterFines());
            if (timeFilter) timeFilter.addEventListener('change', () => filterFines());
            if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetFilters);
            if (entriesPerPage) {
                entriesPerPage.value = String(itemsPerPage);
                entriesPerPage.addEventListener('change', handleEntriesPerPageChange);
            }

            renderFines(finesData, { preservePage: true });
        });
    </script>
@endpush
