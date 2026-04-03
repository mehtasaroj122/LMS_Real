
@extends('Student.layouts.app')

@section('title', 'My Request')

@push('styles')
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ===== MY REQUESTS PAGE STYLES ===== */
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
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-bottom: 0.125rem;
        }

        .page-header h1 i {
            color: var(--primary-color);
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 0.8rem;
            line-height: 1.4;
        }

        /* ===== STATS CARDS - Consistent with My Books page ===== */
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

        .stat-card.total {
            border-left-color: #2563eb;
        }

        .stat-card.pending {
            border-left-color: #f59e0b;
        }

        .stat-card.approved {
            border-left-color: #10b981;
        }

        .stat-card.rejected {
            border-left-color: #ef4444;
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

        body.light-theme .stat-card.total .stat-icon {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.light-theme .stat-card.pending .stat-icon {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        body.light-theme .stat-card.approved .stat-icon {
            background-color: #dcfce7;
            color: #10b981;
        }

        body.light-theme .stat-card.rejected .stat-icon {
            background-color: #fee2e2;
            color: #ef4444;
        }

        body.dark-theme .stat-card.total .stat-icon {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        body.dark-theme .stat-card.pending .stat-icon {
            background-color: #78350f;
            color: #fbbf24;
        }

        body.dark-theme .stat-card.approved .stat-icon {
            background-color: #064e3b;
            color: #34d399;
        }

        body.dark-theme .stat-card.rejected .stat-icon {
            background-color: #7f1d1d;
            color: #fca5a5;
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

        /* ===== SEARCH & FILTER BAR - Consistent with My Books page ===== */
        .search-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            width: 100%;
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            border-radius: var(--radius);
            align-items: center;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        .search-box {
            flex: 0 1 440px;
            width: min(100%, 440px);
            min-width: 240px;
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
            width: min(100%, 420px);
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
            min-width: 120px;
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

        /* Section Header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.375rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-header h2 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        /* Requests Table */
        .requests-container {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        /* Desktop Table */
        .requests-table {
            width: 100%;
            border-collapse: collapse;
            display: table;
        }

        .requests-table thead {
            background: var(--body-bg);
        }

        .requests-table th {
            padding: 0.375rem 0.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 2px solid var(--border-color);
        }

        .requests-table td {
            padding: 0.375rem 0.5rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .requests-table tbody tr:hover {
            background: var(--body-bg);
        }

        /* Book Info */
        .book-info {
            display: flex;
            flex-direction: column;
        }

        .book-title {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.125rem;
            font-size: 0.8rem;
        }

        .book-author {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* Request Date */
        .request-date {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.8rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.125rem 0.375rem;
            border-radius: 1rem;
            font-size: 0.7rem;
            font-weight: 500;
            white-space: nowrap;
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-approved {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status-issued {
            background: rgba(59, 130, 246, 0.1);
            color: var(--info-color);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .status-returned {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .status-cancelled {
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
            border: 1px solid rgba(107, 114, 128, 0.2);
        }

        /* Processed By */
        .processed-by {
            color: var(--text-primary);
            font-size: 0.8rem;
        }

        .processed-na {
            color: var(--text-muted);
            font-style: italic;
            font-size: 0.75rem;
        }

        /* Action Button */
        .cancel-btn {
            padding: 0.25rem 0.5rem;
            background: transparent;
            border: 1px solid var(--danger-color);
            border-radius: var(--radius-sm);
            color: var(--danger-color);
            font-size: 0.7rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.125rem;
        }

        .cancel-btn svg {
            width: 0.8rem;
            height: 0.8rem;
            flex-shrink: 0;
        }

        .cancel-btn:hover:not(:disabled) {
            background: rgba(239, 68, 68, 0.1);
        }

        .cancel-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Mobile Cards */
        .requests-mobile {
            display: none;
            flex-direction: column;
            gap: 0.5rem;
        }

        .request-mobile-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 0.5rem;
            border: 1px solid var(--border-color);
        }

        .mobile-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.375rem;
            padding-bottom: 0.375rem;
            border-bottom: 1px solid var(--border-color);
        }

        .mobile-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .mobile-label {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.7rem;
            min-width: 70px;
        }

        .mobile-value {
            flex: 1;
            text-align: right;
        }

        .mobile-actions {
            margin-top: 0.375rem;
            padding-top: 0.375rem;
            border-top: 1px solid var(--border-color);
            text-align: right;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 1rem 0.5rem;
            color: var(--text-muted);
            display: none;
        }

        .empty-state i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
        }

        .empty-state h3 {
            font-size: 1rem;
            margin-bottom: 0.125rem;
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

            .requests-table {
                display: none;
            }

            .requests-mobile {
                display: flex;
            }

            .search-filter-container {
                flex-direction: column;
                align-items: stretch;
                padding: 0.5rem;
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

            .requests-table th,
            .requests-table td {
                padding: 0.5rem 0.75rem;
            }

            .mobile-row {
                flex-direction: column;
                gap: 0.125rem;
            }

            .mobile-label {
                min-width: auto;
                width: 100%;
            }

            .mobile-value {
                text-align: left;
                width: 100%;
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
    <div class="page-header">
        <h1>Book Requests</h1>
        <p>Manage your book requests</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card card total">
            <div class="stat-header">
                <h3 class="stat-title">Total Request</h3>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-number" id="totalRequests">{{ $totalRequests }}</div>
            <div class="stat-label">Total requests made</div>
        </div>

        <div class="stat-card card pending">
            <div class="stat-header">
                <h3 class="stat-title">Pending Request</h3>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-number" id="pendingRequests">{{ $pendingRequests }}</div>
            <div class="stat-label">Awaiting approval</div>
        </div>

        <div class="stat-card card approved">
            <div class="stat-header">
                <h3 class="stat-title">Approved Request</h3>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12 11 14 15 10"></path>
                        <path d="M12 3a12 12 0 0 0 8.5 3A12 12 0 0 1 12 21 12 12 0 0 1 3.5 6 12 12 0 0 0 12 3"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-number" id="approvedRequests">{{ $approvedRequests }}</div>
            <div class="stat-label">Requests approved</div>
        </div>

        <div class="stat-card card rejected">
            <div class="stat-header">
                <h3 class="stat-title">Rejected Request</h3>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="8" y1="8" x2="16" y2="16"></line>
                        <line x1="16" y1="8" x2="8" y2="16"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-number" id="rejectedRequests">{{ $rejectedRequests }}</div>
            <div class="stat-label">Requests rejected</div>
        </div>
    </div>

    <!-- All Requests Section -->
    <div class="section-header">
        <h2>
            <i class="fas fa-list"></i>
            All Requests
        </h2>
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
            <input type="text" class="search-input" id="searchInput" placeholder="Search by book title or author">
        </div>

        <div class="filters-container">
            <select class="filter-select" id="statusFilter">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <select class="filter-select" id="dateFilter">
                <option value="all">All Dates</option>
                <option value="7">Last 7 days</option>
                <option value="30">Last 30 days</option>
                <option value="year">This year</option>
            </select>

            <button type="button" class="reset-filter-btn" id="resetFiltersBtn">Reset</button>
        </div>

        <label class="entries-control admin-table-entries-control" for="entriesPerPage">
            <span class="entries-label">Show</span>
            <select class="filter-select entries-select admin-table-entries-select" id="entriesPerPage">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            <span class="entries-suffix">entries</span>
        </label>
    </div>

    <div class="requests-container">
        <!-- Desktop Table -->
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Processed By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="requestsTableBody">
                <!-- Books data will be loaded here by JavaScript -->
            </tbody>
        </table>

        <!-- Mobile Cards -->
        <div class="requests-mobile" id="requestsMobile">
            <!-- Mobile cards will be loaded here by JavaScript -->
        </div>

        <!-- Empty State -->
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
            <h3>No requests found</h3>
            <p>Try adjusting your search or filters</p>
        </div>

        <!-- Pagination -->
        @include('shared.student-portal-pagination.markup', [
            'containerId' => 'paginationContainer',
            'fromId' => 'paginationStart',
            'toId' => 'paginationEnd',
            'totalId' => 'paginationTotal',
            'pageInfoId' => 'paginationPageInfo',
            'buttonsId' => 'paginationButtons',
            'label' => 'requests',
        ])
    </div>

@endsection

@push('scripts')
    <script>
        // Requests data from backend
        const requestsData = {!! $requestsJson !!};
        const currentUserName = @json(auth()->user()?->name ?? 'Student');
        const requestFeedback = window.getStudentPortalFeedback?.() || null;

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function showRequestToast(type, title, message, detail = '', timeout = 4200) {
            if (typeof window.showStudentPortalToast === 'function') {
                window.showStudentPortalToast(type, title, message, timeout, detail);
                return;
            }

            if (requestFeedback) {
                requestFeedback.showToast({ type, title, message, detail, timeout });
                return;
            }

            const logger = type === 'error' ? console.error : console.log;
            logger(`${title || 'Notice'}: ${message || ''}`);
        }

        function getStatusLabel(status) {
            if (status === 'pending') return 'Pending';
            if (status === 'approved') return 'Approved';
            if (status === 'issued') return 'Issued';
            if (status === 'returned') return 'Returned';
            if (status === 'cancelled') return 'Cancelled';
            return 'Rejected';
        }

        function renderProcessedByValue(request) {
            return request.processedBy !== 'N/A'
                ? `<span class="processed-by">${escapeHtml(request.processedBy)}</span>`
                : '<span class="processed-na">N/A</span>';
        }

        function getCancelButtonHtml(label) {
            return `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6" />
                </svg>
                <span>${escapeHtml(label)}</span>
            `;
        }

        function renderCancelButton(request, label = 'Cancel') {
            if (request.status !== 'pending') {
                return '<span class="processed-na">—</span>';
            }

            return `
                <button class="cancel-btn" data-request-id="${request.id}" data-label="${escapeHtml(label)}" onclick="showCancelModal(${request.id})">
                    ${getCancelButtonHtml(label)}
                </button>
            `;
        }

        function renderMobileAction(request) {
            if (request.status !== 'pending') {
                return '<span class="processed-na">No action required</span>';
            }

            return renderCancelButton(request, 'Cancel Request');
        }

        function getRequestDisplayDate(request) {
            return request.requestDate || 'N/A';
        }

        function getRequestDateValue(request) {
            return request.requestDateRaw || request.requestDate || null;
        }

        const DEFAULT_ITEMS_PER_PAGE = 10;
        const ALLOWED_PAGE_SIZES = [10, 20, 50, 100];
        let itemsPerPage = getInitialItemsPerPage();
        let currentPage = getInitialPage();
        let filteredRequests = [];
        let requestsPagination = null;

        function getInitialItemsPerPage() {
            const perPage = Number(new URLSearchParams(window.location.search).get('per_page'));
            return ALLOWED_PAGE_SIZES.includes(perPage) ? perPage : DEFAULT_ITEMS_PER_PAGE;
        }

        function getInitialPage() {
            const page = Number(new URLSearchParams(window.location.search).get('page'));
            return Number.isInteger(page) && page > 0 ? page : 1;
        }

        function getTotalPages() {
            return Math.max(1, Math.ceil(filteredRequests.length / itemsPerPage));
        }

        function syncPaginationState() {
            const params = new URLSearchParams(window.location.search);
            params.set('page', String(currentPage));
            params.set('per_page', String(itemsPerPage));

            const queryString = params.toString();
            const nextUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
            window.history.replaceState({}, '', nextUrl);
        }

        function renderPagination(totalRecords, startRecord, endRecord, totalPages) {
            requestsPagination?.render({
                total: totalRecords,
                from: startRecord,
                to: endRecord,
                currentPage,
                lastPage: totalPages,
                onPageChange: (page) => goToPage(page),
            });
            syncPaginationState();
        }

        // Render requests table and mobile cards
        function renderRequests(requests, { preservePage = false } = {}) {
            const tableBody = document.getElementById('requestsTableBody');
            const mobileContainer = document.getElementById('requestsMobile');
            const emptyState = document.getElementById('emptyState');

            filteredRequests = requests;

            if (!preservePage) {
                currentPage = 1;
            }

            currentPage = Math.min(Math.max(currentPage, 1), getTotalPages());
            updateStats(requests);

            if (requests.length === 0) {
                tableBody.innerHTML = '';
                mobileContainer.innerHTML = '';
                emptyState.style.display = 'block';
                renderPagination(0, 0, 0, 0);
                syncPaginationState();
                return;
            }

            emptyState.style.display = 'none';
            displayCurrentPage();
        }

        function handleEntriesPerPageChange(event) {
            const nextPageSize = Number(event.target.value);

            if (!ALLOWED_PAGE_SIZES.includes(nextPageSize) || nextPageSize === itemsPerPage) {
                return;
            }

            const firstVisibleIndex = filteredRequests.length > 0 ? (currentPage - 1) * itemsPerPage : 0;
            itemsPerPage = nextPageSize;
            currentPage = Math.floor(firstVisibleIndex / itemsPerPage) + 1;

            renderRequests(filteredRequests, { preservePage: true });
        }

        // Go to specific page
        function goToPage(page) {
            const totalPages = getTotalPages();
            if (page < 1 || page > totalPages) return;

            currentPage = page;
            displayCurrentPage();
        }

        // Display current page data
        function displayCurrentPage() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageRequests = filteredRequests.slice(start, end);
            const totalPages = getTotalPages();

            renderPageRequests(pageRequests);
            renderPagination(filteredRequests.length, start + 1, Math.min(end, filteredRequests.length), totalPages);
        }

        // Render specific page requests
        function renderPageRequests(requests) {
            const tableBody = document.getElementById('requestsTableBody');
            const mobileContainer = document.getElementById('requestsMobile');

            tableBody.innerHTML = requests.map(request => `
            <tr data-id="${request.id}" data-status="${request.status}">
                <td>
                    <div class="book-info">
                        <div class="book-title">${request.title}</div>
                        <div class="book-author">${request.author}</div>
                    </div>
                </td>
                <td class="request-date">${getRequestDisplayDate(request)}</td>
                <td>
                    <span class="status-badge status-${request.status}">
                        ${getStatusLabel(request.status)}
                    </span>
                </td>
                <td>
                    ${renderProcessedByValue(request)}
                </td>
                <td>
                    ${renderCancelButton(request)}
                </td>
            </tr>
        `).join('');

            mobileContainer.innerHTML = requests.map(request => `
            <div class="request-mobile-card" data-id="${request.id}" data-status="${request.status}">
                <div class="mobile-row">
                    <span class="mobile-label">Book</span>
                    <div class="mobile-value book-info">
                        <div class="book-title">${request.title}</div>
                        <div class="book-author">${request.author}</div>
                    </div>
                </div>
                <div class="mobile-row">
                    <span class="mobile-label">Date</span>
                    <div class="mobile-value request-date">${getRequestDisplayDate(request)}</div>
                </div>
                <div class="mobile-row">
                    <span class="mobile-label">Status</span>
                    <div class="mobile-value">
                        <span class="status-badge status-${request.status}">
                            ${getStatusLabel(request.status)}
                        </span>
                    </div>
                </div>
                <div class="mobile-row">
                    <span class="mobile-label">Processed By</span>
                    <div class="mobile-value">
                        ${renderProcessedByValue(request)}
                    </div>
                </div>
                <div class="mobile-actions">
                    ${renderMobileAction(request)}
                </div>
            </div>
        `).join('');
        }

        // Next page
        function nextPage() {
            const totalPages = getTotalPages();
            if (currentPage < totalPages) {
                currentPage++;
                displayCurrentPage();
            }
        }

        // Previous page
        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                displayCurrentPage();
            }
        }

        // Update statistics based on filtered requests
        function updateStats(requests) {
            const totalRequests = requests.length;
            const pendingRequests = requests.filter(request => request.status === 'pending').length;
            const approvedRequests = requests.filter(request => request.status === 'approved').length;
            const rejectedRequests = requests.filter(request => request.status === 'rejected').length;

            document.getElementById('totalRequests').textContent = totalRequests;
            document.getElementById('pendingRequests').textContent = pendingRequests;
            document.getElementById('approvedRequests').textContent = approvedRequests;
            document.getElementById('rejectedRequests').textContent = rejectedRequests;
        }

        // Filter and search functionality
        function filterRequests({ preservePage = false } = {}) {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;

            let nextFilteredRequests = requestsData.filter(request => {
                // Search filter
                const matchesSearch = searchTerm === '' ||
                    request.title.toLowerCase().includes(searchTerm) ||
                    request.author.toLowerCase().includes(searchTerm);

                // Status filter
                const matchesStatus = statusFilter === 'all' || request.status === statusFilter;

                // Date filter
                let matchesDate = true;
                if (dateFilter !== 'all') {
                    const requestDate = parseDate(getRequestDateValue(request));
                    const today = new Date();

                    switch (dateFilter) {
                        case '7':
                            const sevenDaysAgo = new Date();
                            sevenDaysAgo.setDate(today.getDate() - 7);
                            matchesDate = requestDate >= sevenDaysAgo;
                            break;
                        case '30':
                            const thirtyDaysAgo = new Date();
                            thirtyDaysAgo.setDate(today.getDate() - 30);
                            matchesDate = requestDate >= thirtyDaysAgo;
                            break;
                        case 'year':
                            matchesDate = requestDate.getFullYear() === today.getFullYear();
                            break;
                    }
                }

                return matchesSearch && matchesStatus && matchesDate;
            });

            // Sort by request date (newest first)
            nextFilteredRequests.sort((a, b) => {
                const dateA = parseDate(getRequestDateValue(a));
                const dateB = parseDate(getRequestDateValue(b));
                return dateB - dateA;
            });

            renderRequests(nextFilteredRequests, { preservePage });
        }

        function parseDate(dateString) {
            if (!dateString) {
                return new Date(0);
            }

            if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                return new Date(`${dateString}T00:00:00`);
            }

            const parsedDate = new Date(dateString);
            if (!Number.isNaN(parsedDate.getTime())) {
                return parsedDate;
            }

            const [month, day, year] = String(dateString).split('/').map(Number);
            return new Date(year, month - 1, day);
        }

        function resetFilters() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');

            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = 'all';
            if (dateFilter) dateFilter.value = 'all';

            filterRequests();
        }

        document.addEventListener('DOMContentLoaded', function() {
            requestsPagination = new window.StudentPortalPagination({
                containerId: 'paginationContainer',
                fromId: 'paginationStart',
                toId: 'paginationEnd',
                totalId: 'paginationTotal',
                pageInfoId: 'paginationPageInfo',
                buttonsId: 'paginationButtons',
            });

            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const entriesPerPage = document.getElementById('entriesPerPage');

            if (searchInput) searchInput.addEventListener('input', () => filterRequests());
            if (statusFilter) statusFilter.addEventListener('change', () => filterRequests());
            if (dateFilter) dateFilter.addEventListener('change', () => filterRequests());
            if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetFilters);
            if (entriesPerPage) {
                entriesPerPage.value = String(itemsPerPage);
                entriesPerPage.addEventListener('change', handleEntriesPerPageChange);
            }

            renderRequests(requestsData, { preservePage: true });
        });

        function setCancelButtonsLoading(requestId, isLoading) {
            const cancelButtons = document.querySelectorAll(`.cancel-btn[data-request-id="${requestId}"]`);

            cancelButtons.forEach(button => {
                const label = button.dataset.label || 'Cancel';
                button.disabled = isLoading;
                button.innerHTML = isLoading ? '<span>Cancelling...</span>' : getCancelButtonHtml(label);
            });
        }

        function getRequestById(requestId) {
            return requestsData.find(request => Number(request.id) === Number(requestId)) || null;
        }

        async function showCancelModal(requestId) {
            const request = getRequestById(requestId);

            if (!request) {
                return;
            }

            const confirmOptions = {
                variant: 'warning',
                buttonVariant: 'danger',
                title: 'Cancel request?',
                message: 'Are you sure you want to cancel this book request? This action cannot be undone.',
                detail: `${request.title} • Requested on ${getRequestDisplayDate(request)}`,
                confirmText: 'Yes, Cancel Request',
                cancelText: 'No, Keep It',
            };
            const confirmed = typeof window.confirmStudentPortalAction === 'function'
                ? await window.confirmStudentPortalAction(confirmOptions)
                : requestFeedback?.confirm
                    ? await requestFeedback.confirm(confirmOptions)
                    : false;

            if (!confirmed) {
                return;
            }

            await cancelRequest(requestId);
        }

        async function cancelRequest(requestId) {
            const request = getRequestById(requestId);
            setCancelButtonsLoading(requestId, true);

            try {
                const response = await fetch(`/student/my-requests/${requestId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                let data = {};

                try {
                    data = await response.json();
                } catch (error) {
                    data = {
                        success: false,
                        message: 'Unable to process this request right now.',
                    };
                }

                if (response.ok && data.success) {
                    const requestIndex = requestsData.findIndex(r => r.id === requestId);
                    if (requestIndex !== -1) {
                        requestsData[requestIndex].status = 'cancelled';
                        requestsData[requestIndex].processedBy = data.processedBy || `${currentUserName} (you)`;
                    }

                    filterRequests({ preservePage: true });
                    showRequestToast(
                        'success',
                        'Request cancelled',
                        data.message || 'Your request has been cancelled successfully.',
                        request?.title || 'Book request',
                        4800
                    );
                    return;
                }

                setCancelButtonsLoading(requestId, false);
                showRequestToast(
                    'error',
                    'Unable to cancel',
                    data.message || 'An error occurred while cancelling the request.',
                    request?.title || 'Book request'
                );
            } catch (error) {
                console.error('Error:', error);
                setCancelButtonsLoading(requestId, false);
                showRequestToast(
                    'error',
                    'Unable to cancel',
                    'An error occurred while cancelling the request.',
                    request?.title || 'Book request'
                );
            }
        }
    </script>
@endpush
