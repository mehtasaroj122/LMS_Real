
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
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            border-radius: var(--radius);
            align-items: center;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        .search-box {
            flex: 1;
            min-width: 200px;
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
        }

        .pagination-info {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .pagination-controls {
            display: flex;
            gap: 0.25rem;
            align-items: center;
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

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 0.75rem;
            max-width: 300px;
            width: 100%;
            border: 1px solid var(--border-color);
        }

        .modal h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.375rem;
        }

        .modal p {
            color: var(--text-secondary);
            margin-bottom: 0.75rem;
            line-height: 1.4;
            font-size: 0.8rem;
        }

        .modal-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
        }

        .modal-btn.cancel {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .modal-btn.confirm {
            background: var(--danger-color);
            border: 1px solid var(--danger-color);
            color: white;
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
            .filters-container {
                width: 100%;
            }

            .filter-select {
                min-width: 100%;
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

            .modal-actions {
                flex-direction: column;
            }

            .modal-btn {
                width: 100%;
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
        </div>
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
        <div class="pagination-container" id="paginationContainer" style="display: none;">
            <div class="pagination-info">
                Showing <span id="paginationStart">1</span> to <span id="paginationEnd">10</span> of <span id="paginationTotal">0</span> results
            </div>
            <div class="pagination-controls">
                <button class="pagination-btn" id="paginationPrev" onclick="previousPage()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="paginationNumbers"></div>
                <button class="pagination-btn" id="paginationNext" onclick="nextPage()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal-overlay">
        <div class="modal">
            <h3>Cancel Request</h3>
            <p>Are you sure you want to cancel this book request? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="modal-btn cancel" onclick="hideCancelModal()">No, Keep It</button>
                <button class="modal-btn confirm" onclick="confirmCancel()">Yes, Cancel Request</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Requests data from backend
        const requestsData = {!! $requestsJson !!};
        console.log('Requests data:', requestsData);

        // Store the ID of the request being cancelled
        let currentRequestId = null;

        // Render requests table and mobile cards
        function renderRequests(requests) {
            const tableBody = document.getElementById('requestsTableBody');
            const mobileContainer = document.getElementById('requestsMobile');
            const emptyState = document.getElementById('emptyState');

            if (requests.length === 0) {
                tableBody.innerHTML = '';
                mobileContainer.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            // Render desktop table
            tableBody.innerHTML = requests.map(request => `
            <tr data-id="${request.id}" data-status="${request.status}">
                <td>
                    <div class="book-info">
                        <div class="book-title">${request.title}</div>
                        <div class="book-author">${request.author}</div>
                    </div>
                </td>
                <td class="request-date">${request.requestDate}</td>
                <td>
                    <span class="status-badge status-${request.status}">
                        ${request.status === 'pending' ? 'Pending' : 
                          request.status === 'approved' ? 'Approved' : 
                          request.status === 'issued' ? 'Issued' :
                          request.status === 'returned' ? 'Returned' :
                          request.status === 'cancelled' ? 'Cancelled' : 'Rejected'}
                    </span>
                </td>
                <td>
                    ${request.processedBy !== 'N/A' ? 
                      `<span class="processed-by">${request.processedBy}</span>` : 
                      '<span class="processed-na">N/A</span>'}
                </td>
                <td>
                    ${request.status === 'pending' ? 
                      `<button class="cancel-btn" onclick="showCancelModal(${request.id})">
                            <i class="fas fa-trash-alt"></i>
                            Cancel
                           </button>` : 
                      '<span class="processed-na">—</span>'}
                </td>
            </tr>
        `).join('');

            // Render mobile cards
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
                    <div class="mobile-value request-date">${request.requestDate}</div>
                </div>
                <div class="mobile-row">
                    <span class="mobile-label">Status</span>
                    <div class="mobile-value">
                        <span class="status-badge status-${request.status}">
                            ${request.status === 'pending' ? 'Pending' : 
                              request.status === 'approved' ? 'Approved' : 
                              request.status === 'cancelled' ? 'Cancelled' : 'Rejected'}
                        </span>
                    </div>
                </div>
                <div class="mobile-row">
                    <span class="mobile-label">Processed By</span>
                    <div class="mobile-value">
                        ${request.processedBy !== 'N/A' ? 
                          `<span class="processed-by">${request.processedBy}</span>` : 
                          '<span class="processed-na">N/A</span>'}
                    </div>
                </div>
                <div class="mobile-actions">
                    ${request.status === 'pending' ? 
                      `<button class="cancel-btn" onclick="showCancelModal(${request.id})">
                            <i class="fas fa-trash-alt"></i>
                            Cancel Request
                           </button>` : 
                      '<span class="processed-na">No action required</span>'}
                </div>
            </div>
        `).join('');

            // Update stats
            updateStats(requests);
            updatePagination(requests);
        }

        // Pagination variables
        let currentPage = 1;
        const itemsPerPage = 10;
        let filteredRequests = [];

        // Update pagination display
        function updatePagination(requests) {
            filteredRequests = requests;
            currentPage = 1;

            if (requests.length <= itemsPerPage) {
                document.getElementById('paginationContainer').style.display = 'none';
                return;
            }

            document.getElementById('paginationContainer').style.display = 'flex';

            const totalPages = Math.ceil(requests.length / itemsPerPage);
            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(currentPage * itemsPerPage, requests.length);

            document.getElementById('paginationStart').textContent = start;
            document.getElementById('paginationEnd').textContent = end;
            document.getElementById('paginationTotal').textContent = requests.length;

            // Generate page numbers
            const numbersContainer = document.getElementById('paginationNumbers');
            numbersContainer.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
                btn.textContent = i;
                btn.onclick = () => goToPage(i);
                numbersContainer.appendChild(btn);
            }

            // Update prev/next buttons
            document.getElementById('paginationPrev').disabled = currentPage === 1;
            document.getElementById('paginationNext').disabled = currentPage === totalPages;
        }

        // Go to specific page
        function goToPage(page) {
            const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);
            if (page < 1 || page > totalPages) return;

            currentPage = page;
            displayCurrentPage();
        }

        // Display current page data
        function displayCurrentPage() {
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageRequests = filteredRequests.slice(start, end);

            const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);

            // Update pagination info
            document.getElementById('paginationStart').textContent = start + 1;
            document.getElementById('paginationEnd').textContent = Math.min(end, filteredRequests.length);

            // Update active page button
            const allPageBtns = document.querySelectorAll('#paginationNumbers .pagination-btn');
            allPageBtns.forEach((btn, index) => {
                btn.classList.toggle('active', index + 1 === currentPage);
            });

            // Update prev/next buttons
            document.getElementById('paginationPrev').disabled = currentPage === 1;
            document.getElementById('paginationNext').disabled = currentPage === totalPages;

            // Render only current page data
            renderPageRequests(pageRequests);
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
                <td class="request-date">${request.requestDate}</td>
                <td>
                    <span class="status-badge status-${request.status}">
                        ${request.status === 'pending' ? 'Pending' : 
                          request.status === 'approved' ? 'Approved' : 
                          request.status === 'issued' ? 'Issued' :
                          request.status === 'returned' ? 'Returned' :
                          request.status === 'cancelled' ? 'Cancelled' : 'Rejected'}
                    </span>
                </td>
                <td>
                    ${request.processedBy !== 'N/A' ? 
                      `<span class="processed-by">${request.processedBy}</span>` : 
                      '<span class="processed-na">N/A</span>'}
                </td>
                <td>
                    ${request.status === 'pending' ? 
                      `<button class="cancel-btn" onclick="showCancelModal(${request.id})">
                            <i class="fas fa-trash-alt"></i>
                            Cancel
                           </button>` : 
                      '<span class="processed-na">—</span>'}
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
                    <div class="mobile-value request-date">${request.requestDate}</div>
                </div>
                <div class="mobile-row">
                    <span class="mobile-label">Status</span>
                    <div class="mobile-value">
                        <span class="status-badge status-${request.status}">
                            ${request.status === 'pending' ? 'Pending' : 
                              request.status === 'approved' ? 'Approved' : 
                              request.status === 'cancelled' ? 'Cancelled' : 'Rejected'}
                        </span>
                    </div>
                </div>
                <div class="mobile-row">
                    <span class="mobile-label">Processed By</span>
                    <div class="mobile-value">
                        ${request.processedBy !== 'N/A' ? 
                          `<span class="processed-by">${request.processedBy}</span>` : 
                          '<span class="processed-na">N/A</span>'}
                    </div>
                </div>
                <div class="mobile-actions">
                    ${request.status === 'pending' ? 
                      `<button class="cancel-btn" onclick="showCancelModal(${request.id})">
                            <i class="fas fa-trash-alt"></i>
                            Cancel Request
                           </button>` : 
                      '<span class="processed-na">No action required</span>'}
                </div>
            </div>
        `).join('');
        }

        // Next page
        function nextPage() {
            const totalPages = Math.ceil(filteredRequests.length / itemsPerPage);
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
        function filterRequests() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;

            let filteredRequests = requestsData.filter(request => {
                // Search filter
                const matchesSearch = searchTerm === '' ||
                    request.title.toLowerCase().includes(searchTerm) ||
                    request.author.toLowerCase().includes(searchTerm);

                // Status filter
                const matchesStatus = statusFilter === 'all' || request.status === statusFilter;

                // Date filter
                let matchesDate = true;
                if (dateFilter !== 'all') {
                    const requestDate = parseDate(request.requestDate);
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
            filteredRequests.sort((a, b) => {
                const dateA = parseDate(a.requestDate);
                const dateB = parseDate(b.requestDate);
                return dateB - dateA;
            });

            renderRequests(filteredRequests);
        }

        function parseDate(dateString) {
            // Parse MM/DD/YYYY format
            const [month, day, year] = dateString.split('/').map(Number);
            return new Date(year, month - 1, day);
        }

        // Event listeners for filters
        document.getElementById('searchInput').addEventListener('input', filterRequests);
        document.getElementById('statusFilter').addEventListener('change', filterRequests);
        document.getElementById('dateFilter').addEventListener('change', filterRequests);

        // Initial render
        renderRequests(requestsData);

        function showCancelModal(requestId) {
            currentRequestId = requestId;
            document.getElementById('cancelModal').style.display = 'flex';
        }

        function hideCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
            currentRequestId = null;
        }

        function confirmCancel() {
            if (!currentRequestId) return;

            // Find and disable the cancel button for this request
            const cancelBtns = document.querySelectorAll(`.cancel-btn[onclick*="${currentRequestId}"]`);
            cancelBtns.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
            });

            // Call backend API to cancel request
            fetch(`/student/my-requests/${currentRequestId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update request status in data
                    const requestIndex = requestsData.findIndex(r => r.id === currentRequestId);
                    if (requestIndex !== -1) {
                        requestsData[requestIndex].status = 'cancelled';
                        requestsData[requestIndex].processedBy = 'You (Cancelled)';
                    }

                    // Re-render with updated data
                    filterRequests();

                    // Hide modal
                    hideCancelModal();

                    // Show success message
                    alert('Request cancelled successfully!');
                } else {
                    alert('Error: ' + data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while cancelling the request');
                location.reload();
            });
        }

        // Close modal when clicking outside
        document.getElementById('cancelModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideCancelModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideCancelModal();
            }
        });
    </script>
@endpush
