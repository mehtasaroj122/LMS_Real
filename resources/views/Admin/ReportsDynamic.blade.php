@extends('Admin.layouts.app')

@section('title', 'Reports')

@push('styles')
<style>
    .reports-container {
        padding: clamp(8px, 1.5vw, 16px);
        max-width: 1600px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    .reports-header {
        margin-bottom: clamp(12px, 2vw, 18px);
    }

    .reports-header h1 {
        font-size: clamp(20px, 4vw, 28px);
        font-weight: 700;
        margin: 0 0 4px 0;
        line-height: 1.2;
    }

    .reports-header p {
        font-size: clamp(12px, 1.5vw, 13px);
        margin: 0;
    }

    body.light-theme .reports-header h1,
    body.light-theme .transaction-header h2,
    body.light-theme .chart-title,
    body.light-theme .table-title,
    body.light-theme .filter-group label {
        color: #0f172a;
    }

    body.dark-theme .reports-header h1,
    body.dark-theme .transaction-header h2,
    body.dark-theme .chart-title,
    body.dark-theme .table-title,
    body.dark-theme .filter-group label {
        color: #f1f5f9;
    }

    body.light-theme .reports-header p,
    body.light-theme .transaction-header p,
    body.light-theme .chart-description,
    body.light-theme .table-subtitle {
        color: #64748b;
    }

    body.dark-theme .reports-header p,
    body.dark-theme .transaction-header p,
    body.dark-theme .chart-description,
    body.dark-theme .table-subtitle {
        color: #94a3b8;
    }

    .filters-card,
    .stat-card,
    .chart-card,
    .table-card {
        border-radius: clamp(10px, 1vw, 14px);
        transition: all 0.3s ease;
    }

    .filters-card {
        padding: clamp(12px, 1.5vw, 16px);
        margin-bottom: clamp(12px, 2vw, 18px);
    }

    .stat-card,
    .chart-card,
    .table-card {
        padding: clamp(12px, 1.4vw, 18px);
    }

    body.light-theme .filters-card,
    body.light-theme .stat-card,
    body.light-theme .chart-card,
    body.light-theme .table-card {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    body.dark-theme .filters-card,
    body.dark-theme .stat-card,
    body.dark-theme .chart-card,
    body.dark-theme .table-card {
        background-color: #1e293b;
        border: 1px solid #334155;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.3);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(180px, 1fr) minmax(240px, auto) auto;
        gap: clamp(8px, 1.4vw, 12px);
        align-items: end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-group label {
        font-size: clamp(11px, 1.4vw, 12px);
        font-weight: 600;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: clamp(8px, 1vw, 10px) clamp(10px, 1.4vw, 12px);
        border-radius: 10px;
        font-size: clamp(11px, 1.4vw, 12px);
        font-family: inherit;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    body.light-theme .filter-group select,
    body.light-theme .filter-group input {
        background-color: #ffffff;
        border-color: #dbe3ef;
        color: #0f172a;
    }

    body.dark-theme .filter-group select,
    body.dark-theme .filter-group input {
        background-color: #334155;
        border-color: #475569;
        color: #f1f5f9;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .custom-range-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(110px, 1fr));
        gap: 8px;
    }

    .is-hidden {
        display: none !important;
    }

    .export-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .export-btn {
        border-radius: 10px;
        border: 1px solid #3b82f6;
        background: transparent;
        color: #3b82f6;
        font-size: clamp(11px, 1.4vw, 12px);
        font-weight: 600;
        padding: 10px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .export-btn:hover {
        transform: translateY(-1px);
    }

    body.light-theme .export-btn:hover {
        background-color: #3b82f6;
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(59, 130, 246, 0.2);
    }

    body.dark-theme .export-btn:hover {
        background-color: #60a5fa;
        border-color: #60a5fa;
        color: #0f172a;
        box-shadow: 0 8px 18px rgba(96, 165, 250, 0.25);
    }

    .stats-grid,
    .transaction-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: clamp(10px, 1.4vw, 14px);
        margin-bottom: clamp(12px, 1.8vw, 18px);
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon.books {
        background: rgba(59, 130, 246, 0.14);
        color: #3b82f6;
    }

    .stat-icon.available {
        background: rgba(16, 185, 129, 0.14);
        color: #10b981;
    }

    .stat-icon.issued {
        background: rgba(99, 102, 241, 0.14);
        color: #6366f1;
    }

    .stat-icon.additions {
        background: rgba(245, 158, 11, 0.14);
        color: #f59e0b;
    }

    .stat-icon.overdue {
        background: rgba(239, 68, 68, 0.14);
        color: #ef4444;
    }

    .stat-value {
        font-size: clamp(24px, 3vw, 30px);
        font-weight: 700;
        line-height: 1;
    }

    .stat-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .stat-subtitle {
        font-size: 11px;
    }

    body.light-theme .stat-value,
    body.light-theme .stat-label {
        color: #0f172a;
    }

    body.dark-theme .stat-value,
    body.dark-theme .stat-label {
        color: #f8fafc;
    }

    body.light-theme .stat-subtitle {
        color: #64748b;
    }

    body.dark-theme .stat-subtitle {
        color: #94a3b8;
    }

    .transaction-header {
        margin: clamp(16px, 2vw, 20px) 0 clamp(12px, 1.6vw, 16px);
    }

    .transaction-header h2 {
        font-size: clamp(16px, 2.2vw, 22px);
        margin: 0 0 4px 0;
    }

    .transaction-header p {
        margin: 0;
        font-size: 12px;
    }

    .trends-section {
        display: grid;
        gap: clamp(10px, 1.4vw, 14px);
    }

    .charts-grid,
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: clamp(10px, 1.4vw, 14px);
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .chart-title,
    .table-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 4px 0;
        font-size: 15px;
        font-weight: 700;
    }

    .chart-description,
    .table-subtitle {
        margin: 0 0 12px 0;
        font-size: 11px;
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: clamp(240px, 30vw, 320px);
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .report-table thead th {
        text-align: left;
        padding: 12px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .report-table tbody td {
        padding: 12px;
        vertical-align: middle;
    }

    .report-table .table-count {
        text-align: right;
    }

    .table-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 0 0 12px 0;
    }

    .table-pagination-info,
    .table-pagination-select {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
    }

    .table-pagination-select select {
        min-width: 68px;
        padding: 6px 32px 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-family: inherit;
        border: 1px solid transparent;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image:
            linear-gradient(45deg, transparent 50%, currentColor 50%),
            linear-gradient(135deg, currentColor 50%, transparent 50%);
        background-position:
            calc(100% - 16px) calc(50% - 2px),
            calc(100% - 11px) calc(50% - 2px);
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
    }

    body.light-theme .table-pagination-info,
    body.light-theme .table-pagination-select {
        color: #475569;
    }

    body.dark-theme .table-pagination-info,
    body.dark-theme .table-pagination-select {
        color: #cbd5e1;
    }

    body.light-theme .table-pagination-select select {
        background-color: #ffffff;
        border-color: #dbe3ef;
        color: #0f172a;
    }

    body.dark-theme .table-pagination-select select {
        background-color: #334155;
        border-color: #475569;
        color: #f8fafc;
    }

    .table-pagination-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-pagination-page {
        min-width: 72px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
    }

    .table-pagination-btn {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: transparent;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .table-pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    body.light-theme .table-pagination-btn {
        border-color: #cbd5e1;
        color: #0f172a;
        background: #ffffff;
    }

    body.dark-theme .table-pagination-btn {
        border-color: #475569;
        color: #f8fafc;
        background: #1e293b;
    }

    body.light-theme .report-table thead th {
        color: #475569;
        background: #f8fafc;
    }

    body.dark-theme .report-table thead th {
        color: #cbd5e1;
        background: #0f172a;
    }

    body.light-theme .report-table tbody td {
        color: #0f172a;
        border-top: 1px solid #edf2f7;
    }

    body.dark-theme .report-table tbody td {
        color: #f8fafc;
        border-top: 1px solid #334155;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-info {
        background: rgba(59, 130, 246, 0.14);
        color: #3b82f6;
    }

    .badge-success {
        background: rgba(16, 185, 129, 0.14);
        color: #10b981;
    }

    .badge-warning {
        background: rgba(245, 158, 11, 0.14);
        color: #f59e0b;
    }

    .section-transaction,
    .section-fines,
    .section-users,
    .section-overdue {
        display: none;
    }

    .section-transaction.active,
    .section-fines.active,
    .section-users.active,
    .section-overdue.active {
        display: block;
    }

    .section-library.hidden {
        display: none;
    }

    @media (max-width: 1024px) {
        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }

        .export-buttons {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .reports-container {
            padding: 6px;
        }

        .filters-grid,
        .charts-grid,
        .tables-grid {
            grid-template-columns: 1fr;
        }

        .custom-range-fields {
            grid-template-columns: 1fr;
        }

        .export-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .export-btn {
            justify-content: center;
        }

        .chart-container {
            height: 240px;
        }

        .report-table thead th,
        .report-table tbody td {
            padding: 9px 8px;
        }
    }
</style>
@endpush

@section('content')
    @php
        $formatCurrency = fn ($value) => '₹' . number_format((float) $value, 2);
    @endphp

    <div class="reports-container">
        <div class="reports-header">
            <div class="flex items-center gap-3 mb-2">
                <x-logo size="md" :lazy="false" />
                <h1>Reports</h1>
            </div>
            <p>Library analytics driven by live inventory, circulation, fine, user, and overdue data.</p>
        </div>

        <div class="filters-card">
            <form id="reportFiltersForm" method="GET" action="{{ route('admin.reports.index') }}">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="reportType">Report Type</label>
                        <select id="reportType" name="report_type">
                            <option value="inventory" {{ $filters['reportType'] === 'inventory' ? 'selected' : '' }}>Books Inventory</option>
                            <option value="transactions" {{ $filters['reportType'] === 'transactions' ? 'selected' : '' }}>Transactions</option>
                            <option value="fines" {{ $filters['reportType'] === 'fines' ? 'selected' : '' }}>Fine & Revenue</option>
                            <option value="users" {{ $filters['reportType'] === 'users' ? 'selected' : '' }}>User & Activity</option>
                            <option value="overdue" {{ $filters['reportType'] === 'overdue' ? 'selected' : '' }}>Overdue Books</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="timePeriod">Time Period</label>
                        <select id="timePeriod" name="time_period">
                            <option value="7days" {{ $filters['timePeriod'] === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30days" {{ $filters['timePeriod'] === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="90days" {{ $filters['timePeriod'] === '90days' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="year" {{ $filters['timePeriod'] === 'year' ? 'selected' : '' }}>Last Year</option>
                            <option value="custom" {{ $filters['timePeriod'] === 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>

                    <div id="customRangeGroup" class="filter-group {{ $filters['showCustomRange'] ? '' : 'is-hidden' }}">
                        <label for="startDate">Date Range</label>
                        <div class="custom-range-fields">
                            <input id="startDate" name="start_date" type="date" value="{{ $filters['startDateInput'] }}">
                            <input id="endDate" name="end_date" type="date" value="{{ $filters['endDateInput'] }}">
                        </div>
                    </div>

                    <div class="filter-group export-buttons">
                        <button id="applyFiltersBtn" type="submit" class="export-btn {{ $filters['showCustomRange'] ? '' : 'is-hidden' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                            Apply
                        </button>
                        <button type="button" class="export-btn" onclick="exportPDF()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            Export PDF
                        </button>
                        <button type="button" class="export-btn" onclick="exportCSV()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                            Export CSV
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="section-library {{ $filters['reportType'] === 'inventory' ? '' : 'hidden' }}">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" /></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['total_books']) }}</div>
                    </div>
                    <div class="stat-label">Total Books</div>
                    <div class="stat-subtitle">{{ number_format($inventoryReport['stats']['total_copies']) }} total copies</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['available_copies']) }}</div>
                    </div>
                    <div class="stat-label">Available</div>
                    <div class="stat-subtitle">Copies ready for issue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['current_issued']) }}</div>
                    </div>
                    <div class="stat-label">Currently Issued</div>
                    <div class="stat-subtitle">Books currently with students</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($inventoryReport['stats']['recent_additions']) }}</div>
                    </div>
                    <div class="stat-label">Recent Additions</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                        Books by Category
                    </h3>
                    <p class="chart-description">Current distribution of titles across categories.</p>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"></line><polyline points="4 7 12 2 20 7"></polyline><polyline points="4 17 12 22 20 17"></polyline></svg>
                        Books by Condition
                    </h3>
                    <p class="chart-description">Condition breakdown for books currently in the catalog.</p>
                    <div class="chart-container">
                        <canvas id="conditionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-transaction {{ $filters['reportType'] === 'transactions' ? 'active' : '' }}">
            <div class="transaction-header">
                <h2>Transaction Analytics</h2>
                <p>Circulation activity for {{ $filters['periodLabel'] }}.</p>
            </div>

            <div class="transaction-stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['books_issued']) }}</div>
                    </div>
                    <div class="stat-label">Books Issued</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['books_returned']) }}</div>
                    </div>
                    <div class="stat-label">Books Returned</div>
                    <div class="stat-subtitle">Completed within the range</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" /></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['currently_issued']) }}</div>
                    </div>
                    <div class="stat-label">Currently Issued</div>
                    <div class="stat-subtitle">Still out on loan</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($transactionsReport['stats']['average_issue_duration'], 1) }}</div>
                    </div>
                    <div class="stat-label">Avg. Issue Duration</div>
                    <div class="stat-subtitle">Days before return</div>
                </div>
            </div>

            <div class="trends-section">
                <div class="chart-card full-width">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline><polyline points="12 12 20 7.5"></polyline><polyline points="12 12 12 21"></polyline><polyline points="12 12 4 7.5"></polyline></svg>
                        Monthly Book Circulation
                    </h3>
                    <p class="chart-description">Issues and returns across the last 12 months.</p>
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <h3 class="chart-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                            Most Borrowed Books
                        </h3>
                        <p class="chart-description">Top titles issued within {{ $filters['periodLabel'] }}.</p>
                        <div class="chart-container">
                            <canvas id="borrowedChart"></canvas>
                        </div>
                    </div>

                    <div class="table-card">
                        <h3 class="table-title">Most Issued Books</h3>
                        <p class="table-subtitle">Top titles issued within {{ $filters['periodLabel'] }}</p>
                        <div style="overflow-x: auto;">
                            <table class="report-table">
                                <thead>
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Author</th>
                                        <th class="table-count">Times Issued</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transactionsReport['most_issued_books'] as $book)
                                        <tr>
                                            <td>{{ $book['title'] }}</td>
                                            <td>{{ $book['author'] }}</td>
                                            <td class="table-count">{{ number_format($book['count']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">No transaction data available for the selected period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="chart-card full-width">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M3 21v-5h5"></path></svg>
                        {{ $transactionsReport['activity_chart_title'] }}
                    </h3>
                    <p class="chart-description">{{ $transactionsReport['activity_chart_description'] }}</p>
                    <div class="chart-container">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-fines {{ $filters['reportType'] === 'fines' ? 'active' : '' }}">
            <div class="transaction-header">
                <h2>Fines & Revenue Overview</h2>
                <p>Collection performance for {{ $filters['periodLabel'] }}.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M7 15h0M2 9.5h20"></path></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['generated']) }}</div>
                    </div>
                    <div class="stat-label">Total Generated</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['collected']) }}</div>
                    </div>
                    <div class="stat-label">Total Collected</div>
                    <div class="stat-subtitle">Paid in the selected range</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon overdue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['pending']) }}</div>
                    </div>
                    <div class="stat-label">Total Pending</div>
                    <div class="stat-subtitle">Still unpaid from the range</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($finesReport['stats']['waived']) }}</div>
                    </div>
                    <div class="stat-label">Total Waived</div>
                    <div class="stat-subtitle">Waived during the range</div>
                </div>
            </div>

            <div class="trends-section">
                <div class="chart-card full-width">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22m4.5-18.5H3.5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h17a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2z"></path></svg>
                        Fine Collection Overview
                    </h3>
                    <p class="chart-description">Generated, collected, and pending fines over the last 12 months.</p>
                    <div class="chart-container">
                        <canvas id="fineCollectionChart"></canvas>
                    </div>
                </div>

                <div class="chart-card full-width">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 10"></polyline><path d="M7 4a1 1 0 0 0 0 2h10a1 1 0 1 0 0-2H7z"></path></svg>
                        Collection Efficiency Trend
                    </h3>
                    <p class="chart-description">Generated versus collected fines over the last 12 months.</p>
                    <div class="chart-container">
                        <canvas id="efficiencyChart"></canvas>
                    </div>
                </div>

                <div class="table-card full-width">
                    <h3 class="table-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        Top Defaulters
                    </h3>
                    <p class="table-subtitle">Current students with the highest pending fine balances.</p>
                    <div style="overflow-x: auto;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Student ID</th>
                                    <th class="table-count">Pending Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($finesReport['top_defaulters'] as $student)
                                    <tr>
                                        <td>{{ $student['student_name'] }}</td>
                                        <td>{{ $student['student_id'] }}</td>
                                        <td class="table-count"><span style="color: #ef4444; font-weight: 600;">{{ $formatCurrency($student['pending_amount']) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No pending defaulters found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-users {{ $filters['reportType'] === 'users' ? 'active' : '' }}">
            <div class="transaction-header">
                <h2>Users & Activity Overview</h2>
                <p>User counts and activity logs for {{ $filters['periodLabel'] }}.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['total_users']) }}</div>
                    </div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-subtitle">All registered accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['active_users']) }}</div>
                    </div>
                    <div class="stat-label">Active Users</div>
                    <div class="stat-subtitle">Active accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 18a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect><circle cx="12" cy="10" r="2"></circle><line x1="8" y1="2" x2="8" y2="4"></line><line x1="16" y1="2" x2="16" y2="4"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['inactive_users']) }}</div>
                    </div>
                    <div class="stat-label">Inactive Users</div>
                    <div class="stat-subtitle">Inactive accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($usersReport['stats']['new_users']) }}</div>
                    </div>
                    <div class="stat-label">New Users</div>
                    <div class="stat-subtitle">{{ $filters['periodLabel'] }}</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Users by Role
                    </h3>
                    <p class="chart-description">Distribution of users by role.</p>
                    <div class="chart-container">
                        <canvas id="usersByRoleChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        User Activity by Role
                    </h3>
                    <p class="chart-description">Activity log volume by role within {{ $filters['periodLabel'] }}.</p>
                    <div class="chart-container">
                        <canvas id="userActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="table-card">
                    <h3 class="table-title">Users by Role</h3>
                    <div style="overflow-x: auto;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th class="table-count">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($usersReport['users_by_role'] as $role)
                                    <tr>
                                        <td>{{ $role['role'] }}</td>
                                        <td class="table-count">{{ number_format($role['count']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No user data available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-card">
                    <h3 class="table-title">Most Active Readers</h3>
                    <p class="table-subtitle">Students with the most issues in {{ $filters['periodLabel'] }}.</p>
                    <div style="overflow-x: auto;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th class="table-count">Books Issued</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($usersReport['most_active_readers'] as $reader)
                                    <tr>
                                        <td>{{ $reader['student_name'] }}</td>
                                        <td class="table-count">{{ number_format($reader['issued_count']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No reader activity found for the selected period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-overdue {{ $filters['reportType'] === 'overdue' ? 'active' : '' }}">
            <div class="transaction-header">
                <h2>Overdue Books Management</h2>
                <p>Current overdue inventory and overdue trend monitoring.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon overdue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($overdueReport['stats']['total_overdue']) }}</div>
                    </div>
                    <div class="stat-label">Total Overdue Books</div>
                    <div class="stat-subtitle">Currently overdue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                        <div class="stat-value">{{ number_format($overdueReport['stats']['critical_overdue']) }}</div>
                    </div>
                    <div class="stat-label">30+ Days Overdue</div>
                    <div class="stat-subtitle">Critical overdue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                        <div class="stat-value">{{ $formatCurrency($overdueReport['stats']['total_fine_amount']) }}</div>
                    </div>
                    <div class="stat-label">Total Fine Amount</div>
                    <div class="stat-subtitle">Across current overdue books</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        </div>
                        <div class="stat-value">{{ number_format($overdueReport['stats']['average_days_overdue'], 1) }}</div>
                    </div>
                    <div class="stat-label">Avg Days Overdue</div>
                    <div class="stat-subtitle">Current overdue portfolio</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Overdue Duration
                    </h3>
                    <p class="chart-description">Current overdue books by days late.</p>
                    <div class="chart-container">
                        <canvas id="overdueDistributionChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 17"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        Overdue Trend
                    </h3>
                    <p class="chart-description">Overdue incidents and current critical cases over the last 12 months.</p>
                    <div class="chart-container">
                        <canvas id="overdueeTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="trends-section">
                <div class="table-card full-width">
                    <h3 class="table-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Overdue Books List
                    </h3>
                    <p class="table-subtitle">All books currently overdue by student.</p>
                    <div style="overflow-x: auto;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Book Title</th>
                                    <th class="table-count">Days Overdue</th>
                                    <th class="table-count">Fine Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overdueReport['overdue_books'] as $book)
                                    <tr>
                                        <td>{{ $book['student_name'] }}</td>
                                        <td>{{ $book['book_title'] }}</td>
                                        <td class="table-count"><span style="color: {{ $book['severity_color'] }}; font-weight: 600;">{{ number_format($book['days_overdue']) }}</span></td>
                                        <td class="table-count"><span style="color: {{ $book['severity_color'] }}; font-weight: 600;">{{ $formatCurrency($book['fine_amount']) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No overdue books found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-library tables-grid {{ $filters['reportType'] === 'inventory' ? '' : 'hidden' }}">
            <div class="table-card">
                <h3 class="table-title">Books by Category</h3>
                <div style="overflow-x: auto;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="table-count">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inventoryReport['category_rows'] as $category)
                                <tr>
                                    <td>{{ $category['name'] }}</td>
                                    <td class="table-count">{{ number_format($category['count']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No category data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-card">
                <h3 class="table-title">Books by Condition</h3>
                <div style="overflow-x: auto;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Condition</th>
                                <th class="table-count">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inventoryReport['condition_rows'] as $condition)
                                <tr>
                                    <td><span class="badge {{ $condition['badge_class'] }}">{{ $condition['label'] }}</span></td>
                                    <td class="table-count">{{ number_format($condition['count']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No condition data available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const reportCharts = @json($reportCharts);
        const chartInstances = {};
        const tablePaginators = new Map();
        const reportTypeSelect = document.getElementById('reportType');
        const timePeriodSelect = document.getElementById('timePeriod');
        const filterForm = document.getElementById('reportFiltersForm');
        const customRangeGroup = document.getElementById('customRangeGroup');
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');

        function getThemeColors() {
            const isDark = document.body.classList.contains('dark-theme');

            return {
                textColor: isDark ? '#e2e8f0' : '#1f2937',
                gridColor: isDark ? '#475569' : '#e5e7eb',
                surfaceBorder: isDark ? '#1e293b' : '#ffffff',
                axisBorder: isDark ? '#475569' : '#d1d5db',
                tooltipBg: isDark ? '#334155' : '#ffffff',
            };
        }

        function baseChartOptions(colors) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: colors.textColor,
                            font: { size: 12 },
                        },
                    },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: colors.textColor,
                        bodyColor: colors.textColor,
                        borderColor: colors.gridColor,
                        borderWidth: 1,
                    },
                },
            };
        }

        function createOrReplaceChart(id, config) {
            const ctx = document.getElementById(id);
            if (!ctx || typeof Chart === 'undefined') {
                return;
            }

            if (chartInstances[id]) {
                chartInstances[id].destroy();
            }

            chartInstances[id] = new Chart(ctx, config);
        }

        function buildSingleSeries(source, fallbackLabel) {
            const hasData = Array.isArray(source?.data) && source.data.some((value) => Number(value) > 0);

            return {
                labels: hasData && Array.isArray(source?.labels) && source.labels.length ? source.labels : [fallbackLabel],
                data: hasData && Array.isArray(source?.data) && source.data.length ? source.data : [1],
                empty: !hasData,
            };
        }

        function pointRadius(labelCount) {
            return labelCount > 60 ? 0 : 4;
        }

        function createCategoryChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.inventory.category, 'No categories');
            const options = baseChartOptions(colors);

            createOrReplaceChart('categoryChart', {
                type: 'pie',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#FFD93D', '#1DD1A1', '#00D2D3', '#FF6B6B', '#845EC2', '#00C9A7', '#F8B195', '#3b82f6', '#f97316', '#10b981'],
                        borderColor: colors.surfaceBorder,
                        borderWidth: 2,
                    }],
                },
                options: {
                    ...options,
                    plugins: {
                        ...options.plugins,
                        legend: {
                            position: 'right',
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 },
                                padding: 14,
                            },
                        },
                    },
                },
            });
        }

        function createConditionChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.inventory.condition, 'No condition data');

            createOrReplaceChart('conditionChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Books Count',
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#3b82f6', '#10b981', '#f59e0b', '#f97316'],
                        borderColor: colors.axisBorder,
                        borderWidth: 1,
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { display: false } },
                    },
                },
            });
        }

        function createMonthlyChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.transactions.monthly_circulation;

            createOrReplaceChart('monthlyChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Books Issued',
                            data: chartData.issues,
                            borderColor: '#60a5fa',
                            backgroundColor: 'rgba(96, 165, 250, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                        {
                            label: 'Books Returned',
                            data: chartData.returns,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createBorrowedChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.transactions.borrowed_books, 'No book issues');

            createOrReplaceChart('borrowedChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Times Borrowed',
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#8b5cf6', '#6366f1', '#3b82f6', '#0ea5e9', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#f97316'],
                        borderColor: colors.axisBorder,
                        borderWidth: 1,
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createDailyChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.transactions.activity;

            createOrReplaceChart('dailyChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Issues',
                            data: chartData.issues,
                            borderColor: '#60a5fa',
                            backgroundColor: 'rgba(96, 165, 250, 0.15)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                        {
                            label: 'Returns',
                            data: chartData.returns,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createFineCollectionChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.fines.collection_overview;

            createOrReplaceChart('fineCollectionChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Fines Generated', data: chartData.generated, backgroundColor: '#f97316', borderColor: colors.axisBorder, borderWidth: 1, borderRadius: 6 },
                        { label: 'Fines Collected', data: chartData.collected, backgroundColor: '#10b981', borderColor: colors.axisBorder, borderWidth: 1, borderRadius: 6 },
                        { label: 'Pending Collection', data: chartData.pending, backgroundColor: '#fbbf24', borderColor: colors.axisBorder, borderWidth: 1, borderRadius: 6 },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createEfficiencyChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.fines.efficiency;

            createOrReplaceChart('efficiencyChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Generated', data: chartData.generated, borderColor: '#f97316', backgroundColor: 'rgba(249, 115, 22, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                        { label: 'Collected', data: chartData.collected, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createUsersByRoleChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.users.users_by_role, 'No user data');

            createOrReplaceChart('usersByRoleChart', {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#3b82f6', '#10b981', '#f59e0b'],
                        borderColor: colors.surfaceBorder,
                        borderWidth: 2,
                    }],
                },
                options: baseChartOptions(colors),
            });
        }

        function createUserActivityChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.users.activity_by_role;

            createOrReplaceChart('userActivityChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Admin Activity', data: chartData.admin, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                        { label: 'Staff Activity', data: chartData.staff, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                        { label: 'Student Activity', data: chartData.student, borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createOverdueDistributionChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.overdue.distribution, 'No overdue data');

            createOrReplaceChart('overdueDistributionChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Number of Books',
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#fbbf24', '#f59e0b', '#f97316', '#ef4444', '#dc2626'],
                        borderColor: colors.axisBorder,
                        borderWidth: 1,
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { display: false } },
                    },
                },
            });
        }

        function createOverdueTrendChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.overdue.trend;

            createOrReplaceChart('overdueeTrendChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Total Overdue', data: chartData.total, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                        { label: 'Critical (30+ Days)', data: chartData.critical, borderColor: '#dc2626', backgroundColor: 'rgba(220, 38, 38, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function renderCharts() {
            createCategoryChart();
            createConditionChart();
            createMonthlyChart();
            createBorrowedChart();
            createDailyChart();
            createFineCollectionChart();
            createEfficiencyChart();
            createUsersByRoleChart();
            createUserActivityChart();
            createOverdueDistributionChart();
            createOverdueTrendChart();
        }

        function updateReportVisibility() {
            const reportType = reportTypeSelect.value;

            document.querySelectorAll('.section-library').forEach((section) => {
                section.classList.toggle('hidden', reportType !== 'inventory');
            });

            document.querySelector('.section-transaction').classList.toggle('active', reportType === 'transactions');
            document.querySelector('.section-fines').classList.toggle('active', reportType === 'fines');
            document.querySelector('.section-users').classList.toggle('active', reportType === 'users');
            document.querySelector('.section-overdue').classList.toggle('active', reportType === 'overdue');
        }

        function updateCustomRangeVisibility() {
            const showCustomRange = timePeriodSelect.value === 'custom';

            customRangeGroup.classList.toggle('is-hidden', !showCustomRange);
            applyFiltersBtn.classList.toggle('is-hidden', !showCustomRange);
        }

        function getRealTableRows(tbody) {
            return Array.from(tbody.querySelectorAll('tr')).filter((row) => {
                const cells = row.querySelectorAll('td');
                return !(cells.length === 1 && cells[0].hasAttribute('colspan'));
            });
        }

        function createPaginationControls(card, tableId) {
            const controls = document.createElement('div');
            controls.className = 'table-pagination';
            controls.innerHTML = `
                <div class="table-pagination-select">
                    <span>Show entries</span>
                    <select data-role="entries">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="table-pagination-info" data-role="summary"></div>
                <div class="table-pagination-actions">
                    <button type="button" class="table-pagination-btn" data-role="prev">Previous</button>
                    <div class="table-pagination-page" data-role="page"></div>
                    <button type="button" class="table-pagination-btn" data-role="next">Next</button>
                </div>
            `;

            const tableWrapper = card.querySelector('div[style*="overflow-x"]');
            if (tableWrapper) {
                card.insertBefore(controls, tableWrapper);
            } else {
                card.appendChild(controls);
            }

            const paginator = tablePaginators.get(tableId);
            const entriesSelect = controls.querySelector('[data-role="entries"]');
            const prevBtn = controls.querySelector('[data-role="prev"]');
            const nextBtn = controls.querySelector('[data-role="next"]');

            entriesSelect.addEventListener('change', () => {
                paginator.pageSize = Number(entriesSelect.value);
                paginator.currentPage = 1;
                renderPaginatedTable(tableId);
            });

            prevBtn.addEventListener('click', () => {
                if (paginator.currentPage > 1) {
                    paginator.currentPage -= 1;
                    renderPaginatedTable(tableId);
                }
            });

            nextBtn.addEventListener('click', () => {
                const totalPages = Math.max(1, Math.ceil(paginator.rows.length / paginator.pageSize));
                if (paginator.currentPage < totalPages) {
                    paginator.currentPage += 1;
                    renderPaginatedTable(tableId);
                }
            });

            paginator.controls = {
                container: controls,
                entriesSelect,
                summary: controls.querySelector('[data-role="summary"]'),
                page: controls.querySelector('[data-role="page"]'),
                prevBtn,
                nextBtn,
            };
        }

        function renderPaginatedTable(tableId) {
            const paginator = tablePaginators.get(tableId);
            if (!paginator) {
                return;
            }

            const { rows, tbody, controls } = paginator;
            const totalRows = rows.length;
            const totalPages = Math.max(1, Math.ceil(totalRows / paginator.pageSize));
            paginator.currentPage = Math.min(paginator.currentPage, totalPages);

            const startIndex = totalRows === 0 ? 0 : (paginator.currentPage - 1) * paginator.pageSize;
            const endIndex = Math.min(startIndex + paginator.pageSize, totalRows);

            rows.forEach((row, index) => {
                row.style.display = index >= startIndex && index < endIndex ? '' : 'none';
            });

            if (!controls) {
                return;
            }

            controls.entriesSelect.value = String(paginator.pageSize);
            controls.prevBtn.disabled = paginator.currentPage <= 1 || totalRows === 0;
            controls.nextBtn.disabled = paginator.currentPage >= totalPages || totalRows === 0;
            controls.page.textContent = totalRows === 0 ? 'Page 0 of 0' : `Page ${paginator.currentPage} of ${totalPages}`;

            if (totalRows === 0) {
                controls.summary.textContent = 'No entries available';
                return;
            }

            controls.summary.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalRows} entries`;
        }

        function initializePaginatedTables() {
            document.querySelectorAll('.report-table').forEach((table, index) => {
                const tbody = table.querySelector('tbody');
                if (!tbody) {
                    return;
                }

                const tableId = table.dataset.paginationId || `report-table-${index}`;
                table.dataset.paginationId = tableId;

                if (tablePaginators.has(tableId)) {
                    renderPaginatedTable(tableId);
                    return;
                }

                const rows = getRealTableRows(tbody);
                const noDataRow = rows.length === 0 ? tbody.querySelector('tr') : null;

                tablePaginators.set(tableId, {
                    table,
                    tbody,
                    rows,
                    noDataRow,
                    pageSize: 10,
                    currentPage: 1,
                    controls: null,
                });

                createPaginationControls(table.closest('.table-card'), tableId);
                renderPaginatedTable(tableId);
            });
        }

        function escapeCsv(value) {
            return `"${String(value ?? '').replace(/"/g, '""')}"`;
        }

        function exportPDF() {
            window.print();
        }

        function exportCSV() {
            const visibleSections = Array.from(document.querySelectorAll('.section-library, .section-transaction, .section-fines, .section-users, .section-overdue'))
                .filter((section) => window.getComputedStyle(section).display !== 'none');

            const tableCards = visibleSections.flatMap((section) => {
                return Array.from(section.querySelectorAll('.table-card')).map((card) => {
                    const title = card.querySelector('.table-title')?.textContent.trim() || 'Report Table';
                    const table = card.querySelector('table');

                    return table ? { title, table } : null;
                }).filter(Boolean);
            });

            if (!tableCards.length) {
                return;
            }

            const rows = [];
            const systemTitle = @json($libraryBranding['name'] ?? 'Library Management System');
            const logoUrl = @json($libraryBranding['image_url'] ?? null);

            rows.push(escapeCsv(systemTitle));
            if (logoUrl) {
                rows.push(`${escapeCsv('Library Logo')},${escapeCsv(logoUrl)}`);
            }
            rows.push('');

            tableCards.forEach(({ title, table }, index) => {
                const tableId = table.dataset.paginationId;
                const paginator = tableId ? tablePaginators.get(tableId) : null;

                if (index > 0) {
                    rows.push('');
                }

                rows.push(escapeCsv(title));

                const headerRow = table.querySelector('thead tr');
                if (headerRow) {
                    const headerCells = Array.from(headerRow.querySelectorAll('th, td')).map((cell) => escapeCsv(cell.innerText.trim()));
                    rows.push(headerCells.join(','));
                }

                const exportRows = paginator
                    ? (paginator.rows.length ? paginator.rows : paginator.noDataRow ? [paginator.noDataRow] : [])
                    : Array.from(table.querySelectorAll('tbody tr'));

                exportRows.forEach((row) => {
                    const cells = Array.from(row.querySelectorAll('th, td')).map((cell) => escapeCsv(cell.innerText.trim()));
                    rows.push(cells.join(','));
                });
            });

            const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            const dateStamp = new Date().toISOString().slice(0, 10);

            link.href = url;
            link.download = `library-report-${reportTypeSelect.value}-${dateStamp}.csv`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateReportVisibility();
            updateCustomRangeVisibility();
            initializePaginatedTables();
            renderCharts();

            reportTypeSelect.addEventListener('change', () => {
                updateReportVisibility();
                setTimeout(renderCharts, 50);
            });
            timePeriodSelect.addEventListener('change', () => {
                updateCustomRangeVisibility();

                if (timePeriodSelect.value !== 'custom') {
                    filterForm.submit();
                }
            });
        });

        const themeObserver = new MutationObserver((mutations) => {
            if (mutations.some((mutation) => mutation.attributeName === 'class')) {
                renderCharts();
            }
        });

        themeObserver.observe(document.body, { attributes: true });
    </script>
@endsection
