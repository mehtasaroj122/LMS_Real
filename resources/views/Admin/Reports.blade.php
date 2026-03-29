@extends('Admin.layouts.app')

@section('title', 'Reports')

@push('styles')
<style>
    /* ============================= */
    /* REPORTS PAGE - RESPONSIVE & THEME-AWARE */
    /* ============================= */

    .reports-container {
        padding: clamp(8px, 1.5vw, 16px);
        max-width: 1600px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    /* Page Header */
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

    body.light-theme .reports-header h1 {
        color: #0f172a;
    }

    body.dark-theme .reports-header h1 {
        color: #f1f5f9;
    }

    body.light-theme .reports-header p {
        color: #64748b;
    }

    body.dark-theme .reports-header p {
        color: #94a3b8;
    }

    /* Filter Section */
    .filters-card {
        border-radius: clamp(6px, 1vw, 10px);
        padding: clamp(12px, 1.5vw, 16px);
        margin-bottom: clamp(12px, 2vw, 18px);
        transition: all 0.3s ease;
    }

    body.light-theme .filters-card {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    body.dark-theme .filters-card {
        background-color: #1e293b;
        border: 1px solid #334155;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .filters-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto auto;
        gap: clamp(8px, 1.5vw, 12px);
        align-items: flex-end;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .filter-group.export-buttons {
        display: flex;
        flex-direction: row;
        gap: clamp(6px, 1vw, 10px);
        grid-column: auto;
    }

    .filter-group label {
        font-size: clamp(11px, 1.5vw, 12px);
        font-weight: 600;
    }

    body.light-theme .filter-group label {
        color: #0f172a;
    }

    body.dark-theme .filter-group label {
        color: #f1f5f9;
    }

    .filter-group select {
        padding: clamp(6px, 0.8vw, 10px) clamp(8px, 1.2vw, 12px);
        border-radius: clamp(5px, 0.8vw, 7px);
        font-size: clamp(11px, 1.5vw, 12px);
        font-family: inherit;
        transition: all 0.3s ease;
    }

    body.light-theme .filter-group select {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        color: #0f172a;
    }

    body.dark-theme .filter-group select {
        background-color: #334155;
        border: 1px solid #475569;
        color: #f1f5f9;
    }

    .filter-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    body.light-theme .filter-group select:focus {
        border-color: #3b82f6;
    }

    body.dark-theme .filter-group select:focus {
        border-color: #60a5fa;
    }

    .export-buttons {
        display: flex;
        gap: clamp(6px, 1vw, 10px);
        flex-wrap: nowrap;
        align-items: flex-end;
    }

    .export-btn {
        padding: clamp(6px, 0.8vw, 10px) clamp(10px, 1.5vw, 14px);
        border-radius: clamp(5px, 0.8vw, 7px);
        border: 1px solid;
        font-size: clamp(11px, 1.5vw, 12px);
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
        flex: 0 1 auto;
        min-width: auto;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .export-btn svg {
        width: 14px;
        height: 14px;
    }

    body.light-theme .export-btn {
        border-color: #3b82f6;
        color: #3b82f6;
        background-color: transparent;
    }

    body.light-theme .export-btn:hover {
        background-color: #3b82f6;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    body.dark-theme .export-btn {
        border-color: #60a5fa;
        color: #60a5fa;
        background-color: transparent;
    }

    body.dark-theme .export-btn:hover {
        background-color: #1e40af;
        color: white;
        border-color: #1e40af;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(96, 165, 250, 0.3);
    }

    /* Statistics Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: clamp(8px, 1.5vw, 12px);
        margin-bottom: clamp(12px, 2vw, 18px);
    }

    /* ============================= */
    /* STAT CARD STYLES - Dashboard Style */
    /* ============================= */
    .stat-card {
        border-radius: 0.75rem;
        padding: 1.25rem;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    body.light-theme .stat-card {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    body.dark-theme .stat-card {
        background-color: #1e293b;
        border: 1px solid #334155;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    body.dark-theme .stat-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        border-color: #60a5fa;
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
        gap: 4px;
    }

    .stat-label {
        font-size: 0.875rem;
        font-weight: 500;
    }

    body.light-theme .stat-label {
        color: #64748b;
    }

    body.dark-theme .stat-label {
        color: #94a3b8;
    }

    .stat-icon {
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
    }

    .stat-value {
        font-size: 1.875rem;
        font-weight: 700;
        line-height: 1;
    }

    .stat-subtitle {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 2px;
    }

    body.light-theme .stat-value {
        color: #0f172a;
    }

    body.dark-theme .stat-value {
        color: #f1f5f9;
    }

    body.light-theme .stat-subtitle {
        color: #64748b;
    }

    body.dark-theme .stat-subtitle {
        color: #94a3b8;
    }

    /* Status Colors for Icons */
    .stat-icon.books { color: #3b82f6; background-color: rgba(59, 130, 246, 0.1); }
    .stat-icon.available { color: #10b981; background-color: rgba(16, 185, 129, 0.1); }
    .stat-icon.issued { color: #f59e0b; background-color: rgba(245, 158, 11, 0.1); }
    .stat-icon.additions { color: #8b5cf6; background-color: rgba(139, 92, 246, 0.1); }
    .stat-icon.overdue { color: #ef4444; background-color: rgba(239, 68, 68, 0.1); }

    body.dark-theme .stat-icon.books { color: #60a5fa; background-color: rgba(96, 165, 250, 0.15); }
    body.dark-theme .stat-icon.available { color: #34d399; background-color: rgba(52, 211, 153, 0.15); }
    body.dark-theme .stat-icon.issued { color: #fbbf24; background-color: rgba(251, 191, 36, 0.15); }
    body.dark-theme .stat-icon.additions { color: #c4b5fd; background-color: rgba(196, 181, 253, 0.15); }
    body.dark-theme .stat-icon.overdue { color: #f87171; background-color: rgba(248, 113, 113, 0.15); }

    /* Alternative status-based colors */
    .status-blue { background-color: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .status-green { background-color: rgba(16, 185, 129, 0.1); color: #10b981; }
    .status-red { background-color: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .status-yellow { background-color: rgba(245, 158, 11, 0.1); color: #f59e0b; }
    .status-purple { background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6; }

    body.dark-theme .status-blue { background-color: rgba(96, 165, 250, 0.15); color: #60a5fa; }
    body.dark-theme .status-green { background-color: rgba(52, 211, 153, 0.15); color: #34d399; }
    body.dark-theme .status-red { background-color: rgba(252, 165, 165, 0.15); color: #f87171; }
    body.dark-theme .status-yellow { background-color: rgba(251, 191, 36, 0.15); color: #fbbf24; }
    body.dark-theme .status-purple { background-color: rgba(196, 181, 253, 0.15); color: #a78bfa; }

    /* Charts Section */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: clamp(12px, 1.5vw, 16px);
        margin-bottom: clamp(12px, 2vw, 16px);
    }

    .chart-card {
        padding: clamp(12px, 1.5vw, 16px);
        border-radius: clamp(6px, 0.8vw, 10px);
        border: 1px solid;
        transition: all 0.3s ease;
    }

    body.light-theme .chart-card {
        background-color: #ffffff;
        border-color: #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }

    body.dark-theme .chart-card {
        background-color: #1e293b;
        border-color: #334155;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .chart-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    body.dark-theme .chart-card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .chart-title {
        font-size: clamp(12px, 1.8vw, 14px);
        font-weight: 600;
        margin: 0 0 2px 0;
    }

    .chart-description {
        font-size: clamp(10px, 1.2vw, 11px);
        margin: 0 0 clamp(6px, 1vw, 10px) 0;
    }

    body.light-theme .chart-title {
        color: #0f172a;
    }

    body.dark-theme .chart-title {
        color: #f1f5f9;
    }

    body.light-theme .chart-description {
        color: #64748b;
    }

    body.dark-theme .chart-description {
        color: #94a3b8;
    }

    .chart-container {
        position: relative;
        height: clamp(160px, 30vw, 220px);
        width: 100%;
    }

    /* Visibility Control */
    .section-transaction {
        display: none;
    }

    .section-transaction.active {
        display: block;
    }

    .section-fines {
        display: none;
    }

    .section-fines.active {
        display: block;
    }

    .section-users {
        display: none;
    }

    .section-users.active {
        display: block;
    }

    .section-overdue {
        display: none;
    }

    .section-overdue.active {
        display: block;
    }

    .section-overdue.active {
        display: block;
    }

    .section-library {
        display: block;
    }

    .section-library.hidden {
        display: none;
    }

    /* Transaction Analytics Section */
    .transaction-header {
        margin-bottom: clamp(10px, 1.5vw, 14px);
        margin-top: clamp(12px, 2vw, 18px);
    }

    .transaction-header h2 {
        font-size: clamp(14px, 2.5vw, 20px);
        font-weight: 700;
        margin: 0 0 2px 0;
        line-height: 1.2;
    }

    .transaction-header p {
        font-size: clamp(11px, 1.5vw, 12px);
        margin: 0;
    }

    body.light-theme .transaction-header h2 {
        color: #0f172a;
    }

    body.dark-theme .transaction-header h2 {
        color: #f1f5f9;
    }

    body.light-theme .transaction-header p {
        color: #64748b;
    }

    body.dark-theme .transaction-header p {
        color: #94a3b8;
    }

    .transaction-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: clamp(8px, 1.2vw, 10px);
        margin-bottom: clamp(10px, 1.5vw, 14px);
    }

    .trends-section {
        display: grid;
        gap: clamp(10px, 1.2vw, 14px);
        margin-bottom: clamp(10px, 1.5vw, 14px);
    }

    .chart-card.full-width {
        grid-column: 1 / -1;
    }

    /* Tables Section */
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: clamp(10px, 1.2vw, 14px);
        margin-bottom: clamp(10px, 1.5vw, 14px);
    }

    .table-card.full-width {
        grid-column: 1 / -1;
    }

    .table-card {
        padding: clamp(14px, 1.5vw, 20px);
        border-radius: 0.75rem;
        border: 1px solid;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    body.light-theme .table-card {
        background-color: #ffffff;
        border-color: #e5e7eb;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    body.dark-theme .table-card {
        background-color: #1e293b;
        border-color: #334155;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .table-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }

    body.dark-theme .table-card:hover {
        border-color: #60a5fa;
        box-shadow: 0 4px 12px rgba(96, 165, 250, 0.15);
    }

    .table-title {
        font-size: clamp(13px, 1.5vw, 16px);
        font-weight: 700;
        margin: 0 0 4px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-subtitle {
        font-size: clamp(11px, 1.2vw, 13px);
        margin: 0 0 clamp(10px, 1.2vw, 14px) 0;
        opacity: 0.8;
    }

    body.light-theme .table-title {
        color: #0f172a;
    }

    body.dark-theme .table-title {
        color: #f1f5f9;
    }

    body.light-theme .table-subtitle {
        color: #64748b;
    }

    body.dark-theme .table-subtitle {
        color: #94a3b8;
    }

    body.dark-theme .table-title {
        color: #f1f5f9;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: clamp(11px, 1.2vw, 13px);
        border-radius: 8px;
        overflow: hidden;
    }

    .report-table thead th {
        padding: clamp(10px, 1.2vw, 14px) clamp(8px, 1vw, 12px);
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid;
        white-space: nowrap;
    }

    body.light-theme .report-table thead th {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
        color: #475569;
        text-transform: uppercase;
        font-size: clamp(9px, 1vw, 11px);
        letter-spacing: 0.5px;
    }

    body.dark-theme .report-table thead th {
        background-color: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
        text-transform: uppercase;
        font-size: clamp(9px, 1vw, 11px);
        letter-spacing: 0.5px;
    }

    .report-table tbody td {
        padding: clamp(10px, 1.2vw, 14px) clamp(8px, 1vw, 12px);
        border-bottom: 1px solid;
        vertical-align: middle;
    }

    body.light-theme .report-table tbody td {
        border-color: #f1f5f9;
        color: #334155;
    }

    body.dark-theme .report-table tbody td {
        border-color: #1e293b;
        color: #e2e8f0;
    }

    .report-table tbody tr {
        transition: all 0.2s ease;
    }

    body.light-theme .report-table tbody tr:nth-child(even) {
        background-color: #f8fafc;
    }

    body.dark-theme .report-table tbody tr:nth-child(even) {
        background-color: #1e293b;
    }

    body.light-theme .report-table tbody tr:hover {
        background-color: #e0f2fe;
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    body.dark-theme .report-table tbody tr:hover {
        background-color: #334155;
        transform: scale(1.005);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .table-count {
        text-align: right;
        font-weight: 700;
    }

    .report-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: clamp(2px, 0.6vw, 4px) clamp(5px, 1vw, 8px);
        border-radius: 8px;
        font-size: clamp(8px, 1vw, 10px);
        font-weight: 600;
    }

    .badge-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    body.dark-theme .badge-success {
        background-color: rgba(16, 185, 129, 0.2);
        color: #34d399;
    }

    .badge-warning {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    body.dark-theme .badge-warning {
        background-color: rgba(245, 158, 11, 0.2);
        color: #fbbf24;
    }

    .badge-info {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    body.dark-theme .badge-info {
        background-color: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
    }

    .badge-secondary {
        background-color: rgba(107, 114, 128, 0.1);
        color: #6b7280;
    }

    body.dark-theme .badge-secondary {
        background-color: rgba(107, 114, 128, 0.2);
        color: #9ca3af;
    }

    /* Responsive Adjustments */
    @media (max-width: 1024px) {
        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }

        .export-buttons {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 768px) {
        .reports-header h1 {
            font-size: clamp(16px, 3vw, 22px);
        }

        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }

        .export-buttons {
            grid-column: 1 / -1;
            flex-wrap: wrap;
        }

        .export-btn {
            flex: 1;
            min-width: 100px;
            justify-content: center;
            font-size: 10px;
        }

        .export-btn svg {
            display: none;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }

        .transaction-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .tables-grid {
            grid-template-columns: 1fr;
        }

        .chart-container {
            height: clamp(140px, 45vw, 200px);
        }
    }

    @media (max-width: 640px) {
        .reports-container {
            padding: clamp(4px, 0.8vw, 8px);
        }

        .filters-grid {
            grid-template-columns: 1fr;
            gap: clamp(4px, 0.8vw, 8px);
        }

        .filter-group label {
            font-size: 10px;
        }

        .filter-group select {
            font-size: 10px;
            padding: 5px 7px;
        }

        .export-buttons {
            grid-column: 1 / -1;
            flex-direction: column;
        }

        .export-btn {
            flex: 1 1 auto;
            min-width: 100%;
            padding: 6px 8px;
            font-size: 10px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: clamp(6px, 1vw, 10px);
        }

        .stat-card {
            padding: clamp(8px, 1vw, 12px);
        }

        .stat-value {
            font-size: clamp(18px, 4vw, 22px);
        }

        .stat-label {
            font-size: clamp(9px, 1vw, 10px);
        }

        .chart-container {
            height: clamp(130px, 50vw, 200px);
        }

        .transaction-stats-grid {
            grid-template-columns: 1fr;
        }

        .report-table {
            font-size: 10px;
        }

        .report-table th,
        .report-table td {
            padding: 5px 3px;
        }
    }

    @media (max-width: 480px) {
        .reports-header h1 {
            font-size: 16px;
        }

        .filter-group label {
            font-size: 10px;
        }

        .filter-group select {
            font-size: 10px;
            padding: 5px 6px;
        }

        .export-btn {
            font-size: 9px;
            padding: 5px 8px;
        }

        .stat-value {
            font-size: 20px;
        }

        .stat-label {
            font-size: 9px;
        }

        .chart-title {
            font-size: 11px;
        }

        .chart-description {
            font-size: 9px;
        }

        .report-table {
            font-size: 9px;
        }

        .report-table th,
        .report-table td {
            padding: 4px 2px;
        }
    }
            font-size: 20px;
        }

        .report-table {
            font-size: 11px;
        }

        .report-table thead th,
        .report-table tbody td {
            padding: 8px;
        }

        .export-btn {
            padding: 8px 12px;
            font-size: 12px;
        }
    }
</style>
@endpush

@section('content')
    <div class="reports-container">
        <!-- Page Header -->
        <div class="reports-header">
            <h1>Reports</h1>
            <p>Generate and export comprehensive library reports with visual analytics</p>
        </div>

        <!-- Filters Section -->
        <div class="filters-card">
            <div class="filters-grid">
                <!-- Report Type Dropdown -->
                <div class="filter-group">
                    <label for="reportType">Report Type</label>
                    <select id="reportType" onchange="updateReport()">
                        <option value="inventory">Books Inventory</option>
                        <option value="transactions">Transactions</option>
                        <option value="fines">Fine & Revenue</option>
                        <option value="users">User & Activity</option>
                        <option value="overdue">Overdue Books</option>
                    </select>
                </div>

                <!-- Time Period Dropdown -->
                <div class="filter-group">
                    <label for="timePeriod">Time Period</label>
                    <select id="timePeriod" onchange="updateReport()">
                        <option value="7days">Last 7 Days</option>
                        <option value="30days" selected>Last 30 Days</option>
                        <option value="90days">Last 90 Days</option>
                        <option value="year">Last Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <!-- Export Buttons -->
                <div class="filter-group export-buttons">
                    <button class="export-btn" onclick="exportPDF()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:4px; vertical-align:middle;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>Export PDF
                    </button>
                    <button class="export-btn" onclick="exportCSV()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:4px; vertical-align:middle;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="9"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>Export CSV
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="section-library">
        <div class="stats-grid">
            <!-- Total Books Card -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon books">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" /></svg>
                    </div>
                    <div class="stat-value">7</div>
                </div>
                <div class="stat-label">Total Books</div>
                <div class="stat-subtitle">64 total copies</div>
            </div>

            <!-- Available Card -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon available">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div class="stat-value">47</div>
                </div>
                <div class="stat-label">Available</div>
                <div class="stat-subtitle">Ready for issue</div>
            </div>

            <!-- Currently Issued Card -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon issued">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    </div>
                    <div class="stat-value">17</div>
                </div>
                <div class="stat-label">Currently Issued</div>
                <div class="stat-subtitle">With students</div>
            </div>

            <!-- Recent Additions Card -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon additions">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    </div>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-label">Recent Additions</div>
                <div class="stat-subtitle">In month period</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-grid">
            <!-- Books by Category Chart -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                    Books by Category
                </h3>
                <p class="chart-description">Distribution of books across categories</p>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Books by Condition Chart -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><line x1="12" y1="2" x2="12" y2="22"></line><polyline points="4 7 12 2 20 7"></polyline><polyline points="4 17 12 22 20 17"></polyline></svg>
                    Books by Condition
                </h3>
                <p class="chart-description">Physical condition of books in library</p>
                <div class="chart-container">
                    <canvas id="conditionChart"></canvas>
                </div>
            </div>
        </div>
        </div>

        <!-- Transaction Statistics Section -->
        <div class="section-transaction">
            <div class="transaction-header">
                <h2>Transaction Analytics</h2>
                <p>Detailed insights into book issues and returns</p>
            </div>

        <div class="transaction-stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon issued">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    </div>
                    <div class="stat-value">4</div>
                </div>
                <div class="stat-label">Books Issued</div>
                <div class="stat-subtitle">In month period</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon available">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div class="stat-value">5</div>
                </div>
                <div class="stat-label">Books Returned</div>
                <div class="stat-subtitle">In month period</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon issued">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" /></svg>
                    </div>
                    <div class="stat-value">4</div>
                </div>
                <div class="stat-label">Currently Issued</div>
                <div class="stat-subtitle">Not yet returned</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon additions">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="stat-value">17</div>
                </div>
                <div class="stat-label">Avg. Issue Duration</div>
                <div class="stat-subtitle">Days</div>
            </div>
        </div>

        <!-- Trend Charts Section -->
        <div class="trends-section">
            <!-- Monthly Book Circulation Chart -->
            <div class="chart-card full-width">
                <h3 class="chart-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><polyline points="12 3 20 7.5 20 16.5 12 21 4 16.5 4 7.5 12 3"></polyline><polyline points="12 12 20 7.5"></polyline><polyline points="12 12 12 21"></polyline><polyline points="12 12 4 7.5"></polyline></svg>
                    Monthly Book Circulation
                </h3>
                <p class="chart-description">Book issues and returns over the last 12 months</p>
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- Most Borrowed Books & Most Issued Books Row -->
            <div class="charts-grid">
                <!-- Most Borrowed Books Bar Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    Most Borrowed Books
                    </h3>
                    <p class="chart-description">Top 10 books with highest circulation</p>
                    <div class="chart-container">
                        <canvas id="borrowedChart"></canvas>
                    </div>
                </div>

                <!-- Most Issued Books Table -->
                <div class="table-card">
                    <h3 class="table-title">Most Issued Books</h3>
                    <p class="table-subtitle">Top 5 books issued in month period</p>
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
                                <tr>
                                    <td>Clean Code</td>
                                    <td>Robert C. Martin</td>
                                <td class="table-count">3</td>
                            </tr>
                            <tr>
                                <td>Artificial Intelligence: A Modern Approach</td>
                                <td>Stuart Russell, Peter Norvig</td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td>The Pragmatic Programmer</td>
                                <td>David Thomas, Andrew Hunt</td>
                                <td class="table-count">2</td>
                            </tr>
                            <tr>
                                <td>Introduction to Algorithms</td>
                                <td>Cormen, Leiserson, Rivest</td>
                                <td class="table-count">2</td>
                            </tr>
                            <tr>
                                <td>Design Patterns</td>
                                <td>Gang of Four</td>
                                <td class="table-count">1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

            <!-- Daily Activity Trend Chart -->
            <div class="chart-card full-width">
                <h3 class="chart-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M3 21v-5h5"></path></svg>
                    Daily Activity Trend
                </h3>
                <p class="chart-description">Daily book issues and returns</p>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
        </div>

        <!-- Fines & Revenue Section -->
        <div class="section-fines">
            <div class="transaction-header">
                <h2>Fines & Revenue Overview</h2>
                <p>Fine collection performance and analysis</p>
            </div>

            <!-- Fines Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="M7 15h0M2 9.5h20"></path></svg>
                        </div>
                        <div class="stat-value">₹8,350</div>
                    </div>
                    <div class="stat-label">Total Generated</div>
                    <div class="stat-subtitle">In month period</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div class="stat-value">₹2,110</div>
                    </div>
                    <div class="stat-label">Total Collected</div>
                    <div class="stat-subtitle">Paid fines</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon overdue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">₹6,255</div>
                    </div>
                    <div class="stat-label">Total Pending</div>
                    <div class="stat-subtitle">Unpaid fines</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path></svg>
                        </div>
                        <div class="stat-value">₹0</div>
                    </div>
                    <div class="stat-label">Total Waived</div>
                    <div class="stat-subtitle">Waived fines</div>
                </div>
            </div>

            <!-- Fine Charts Section -->
            <div class="trends-section">
                <!-- Fine Collection Overview Chart -->
                <div class="chart-card full-width">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><path d="M12 1v22m4.5-18.5H3.5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h17a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2z"></path></svg>
                        Fine Collection Overview
                    </h3>
                    <p class="chart-description">Monthly fine generation and collection trends</p>
                    <div class="chart-container">
                        <canvas id="fineCollectionChart"></canvas>
                    </div>
                </div>

                <!-- Collection Efficiency Trend Chart -->
                <div class="chart-card full-width">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><polyline points="21 8 21 21 3 21 3 10"></polyline><path d="M7 4a1 1 0 0 0 0 2h10a1 1 0 1 0 0-2H7z"></path></svg>
                        Collection Efficiency Trend
                    </h3>
                    <p class="chart-description">Fine collection performance over time</p>
                    <div class="chart-container">
                        <canvas id="efficiencyChart"></canvas>
                    </div>
                </div>

                <!-- Top Defaulters Table -->
                <div class="table-card full-width">
                    <h3 class="table-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        Top Defaulters
                    </h3>
                    <p class="table-subtitle">Students with highest pending fines</p>
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
                                <tr>
                                    <td>Alice Johnson</td>
                                    <td>STU2024001</td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">₹4165</span></td>
                                </tr>
                                <tr>
                                    <td>Bob Smith</td>
                                    <td>STU2024002</td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">₹2075</span></td>
                                </tr>
                                <tr>
                                    <td>Charlie Brown</td>
                                    <td>STU2024003</td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">₹15</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users & Activity Section -->
        <div class="section-users">
            <div class="transaction-header">
                <h2>Users & Activity Overview</h2>
                <p>User statistics and activity analysis</p>
            </div>

            <!-- User Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        </div>
                        <div class="stat-value">6</div>
                    </div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-subtitle">All registered users</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                        </div>
                        <div class="stat-value">3</div>
                    </div>
                    <div class="stat-label">Active Users</div>
                    <div class="stat-subtitle">Active accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon issued">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 18a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect><circle cx="12" cy="10" r="2"></circle><line x1="8" y1="2" x2="8" y2="4"></line><line x1="16" y1="2" x2="16" y2="4"></line></svg>
                        </div>
                        <div class="stat-value">3</div>
                    </div>
                    <div class="stat-label">Inactive Users</div>
                    <div class="stat-subtitle">Inactive accounts</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                        </div>
                        <div class="stat-value">1</div>
                    </div>
                    <div class="stat-label">New Users</div>
                    <div class="stat-subtitle">In month period</div>
                </div>
            </div>

            <!-- User Charts Section -->
            <div class="charts-grid">
                <!-- Users by Role Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Users by Role
                    </h3>
                    <p class="chart-description">Distribution of users by role</p>
                    <div class="chart-container">
                        <canvas id="usersByRoleChart"></canvas>
                    </div>
                </div>

                <!-- User Activity by Role Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        User Activity by Role
                    </h3>
                    <p class="chart-description">Activity distribution by user role</p>
                    <div class="chart-container">
                        <canvas id="userActivityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- User Tables Section -->
            <div class="charts-grid">
                <!-- Users by Role Table -->
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
                                <tr>
                                    <td>Admin</td>
                                    <td class="table-count">1</td>
                                </tr>
                                <tr>
                                    <td>Staff</td>
                                    <td class="table-count">1</td>
                                </tr>
                                <tr>
                                    <td>Student</td>
                                    <td class="table-count">4</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Most Active Readers Table -->
                <div class="table-card">
                    <h3 class="table-title">Most Active Readers</h3>
                    <p class="table-subtitle">Students with most books issued</p>
                    <div style="overflow-x: auto;">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th class="table-count">Books Issued</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Bob Smith</td>
                                    <td class="table-count">4</td>
                                </tr>
                                <tr>
                                    <td>Alice Johnson</td>
                                    <td class="table-count">3</td>
                                </tr>
                                <tr>
                                    <td>Charlie Brown</td>
                                    <td class="table-count">2</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Books Section -->
        <div class="section-overdue">
            <div class="transaction-header">
                <h2>Overdue Books Management</h2>
                <p>Track and manage overdue books and associated penalties</p>
            </div>

            <!-- Overdue Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon overdue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        </div>
                        <div class="stat-value">8</div>
                    </div>
                    <div class="stat-label">Total Overdue Books</div>
                    <div class="stat-subtitle">Across all students</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon additions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        </div>
                        <div class="stat-value">3</div>
                    </div>
                    <div class="stat-label">30+ Days Overdue</div>
                    <div class="stat-subtitle">Critical overdue</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon books">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        </div>
                        <div class="stat-value">₹1,450</div>
                    </div>
                    <div class="stat-label">Total Fine Amount</div>
                    <div class="stat-subtitle">Accumulated fines</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon available">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        </div>
                        <div class="stat-value">24</div>
                    </div>
                    <div class="stat-label">Avg Days Overdue</div>
                    <div class="stat-subtitle">Days</div>
                </div>
            </div>

            <!-- Overdue Charts Section -->
            <div class="charts-grid">
                <!-- Overdue Duration Distribution -->
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Overdue Duration
                    </h3>
                    <p class="chart-description">Books by days overdue</p>
                    <div class="chart-container">
                        <canvas id="overdueDistributionChart"></canvas>
                    </div>
                </div>

                <!-- Overdue Trend Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><polyline points="23 6 13.5 15.5 8.5 10.5 1 17"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        Overdue Trend
                    </h3>
                    <p class="chart-description">Monthly overdue books trend</p>
                    <div class="chart-container">
                        <canvas id="overdueeTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Overdue Books Table -->
            <div class="trends-section">
                <div class="table-card full-width">
                    <h3 class="table-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-right:6px; vertical-align:middle;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Overdue Books List
                    </h3>
                    <p class="table-subtitle">All books currently overdue by student</p>
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
                                <tr>
                                    <td>Alice Johnson</td>
                                    <td>Clean Code</td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">45</span></td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">₹450</span></td>
                                </tr>
                                <tr>
                                    <td>Bob Smith</td>
                                    <td>Design Patterns</td>
                                    <td class="table-count"><span style="color: #f97316; font-weight: 600;">28</span></td>
                                    <td class="table-count"><span style="color: #f97316; font-weight: 600;">₹280</span></td>
                                </tr>
                                <tr>
                                    <td>Charlie Brown</td>
                                    <td>The Pragmatic Programmer</td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">38</span></td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">₹380</span></td>
                                </tr>
                                <tr>
                                    <td>Diana Lee</td>
                                    <td>Introduction to Algorithms</td>
                                    <td class="table-count"><span style="color: #f97316; font-weight: 600;">15</span></td>
                                    <td class="table-count"><span style="color: #f97316; font-weight: 600;">₹150</span></td>
                                </tr>
                                <tr>
                                    <td>Emma Wilson</td>
                                    <td>Software Engineering</td>
                                    <td class="table-count"><span style="color: #fbbf24; font-weight: 600;">8</span></td>
                                    <td class="table-count"><span style="color: #fbbf24; font-weight: 600;">₹80</span></td>
                                </tr>
                                <tr>
                                    <td>Frank Miller</td>
                                    <td>Database Design</td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">52</span></td>
                                    <td class="table-count"><span style="color: #ef4444; font-weight: 600;">₹520</span></td>
                                </tr>
                                <tr>
                                    <td>Grace Taylor</td>
                                    <td>Artificial Intelligence</td>
                                    <td class="table-count"><span style="color: #fbbf24; font-weight: 600;">12</span></td>
                                    <td class="table-count"><span style="color: #fbbf24; font-weight: 600;">₹120</span></td>
                                </tr>
                                <tr>
                                    <td>Henry Adams</td>
                                    <td>Code Complete</td>
                                    <td class="table-count"><span style="color: #f97316; font-weight: 600;">22</span></td>
                                    <td class="table-count"><span style="color: #f97316; font-weight: 600;">₹220</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="section-library tables-grid">
            <!-- Books by Category Table -->
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
                            <tr>
                                <td>Software Engineering</td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td>Artificial Intelligence</td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td>Programming</td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td>Database</td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td>Mathematics</td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td>Physics</td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td>Computer Science</td>
                                <td class="table-count">1</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Books by Condition Table -->
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
                            <tr>
                                <td><span class="badge badge-success">Good</span></td>
                                <td class="table-count">4</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-info">New</span></td>
                                <td class="table-count">1</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-warning">Damaged</span></td>
                                <td class="table-count">2</td>
                            </tr>
                            <tr>
                                <td><span class="badge badge-secondary">Fair</span></td>
                                <td class="table-count">3</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Detect current theme
        function getThemeColors() {
            const isDark = document.body.classList.contains('dark-theme');
            return {
                textColor: isDark ? '#e2e8f0' : '#1f2937',
                gridColor: isDark ? '#475569' : '#e5e7eb',
                isDark: isDark
            };
        }

        // Chart Colors
        const categoryColors = [
            '#FFD93D',  // Yellow
            '#1DD1A1',  // Teal
            '#00D2D3',  // Cyan
            '#FF6B6B',  // Red
            '#845EC2',  // Purple
            '#00C9A7',  // Green
            '#F8B195'   // Orange
        ];

        const conditionColors = ['#3b82f6', '#f59e0b', '#10b981'];

        // Books by Category Chart
        function createCategoryChart() {
            const ctx = document.getElementById('categoryChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const categoryChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Programming', 'Artificial Intelligence', 'Software Engineering', 'Database', 'Mathematics', 'Physics', 'Computer Science'],
                    datasets: [{
                        data: [1, 1, 1, 1, 1, 1, 1],
                        backgroundColor: categoryColors,
                        borderColor: colors.isDark ? '#1e293b' : '#ffffff',
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'right',
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 },
                                padding: 15,
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    return data.labels.map((label, i) => {
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = ((data.datasets[0].data[i] / total) * 100).toFixed(0);
                                        return {
                                            text: label + ': ' + percentage + '%',
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(0);
                                    return context.label + ': ' + percentage + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Books by Condition Chart
        function createConditionChart() {
            const ctx = document.getElementById('conditionChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const conditionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Good', 'Damaged', 'Fair', 'New'],
                    datasets: [{
                        label: 'Books Count',
                        data: [4, 2, 3, 1],
                        backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#3b82f6'],
                        borderColor: colors.isDark ? '#475569' : '#d1d5db',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'x',
                    plugins: {
                        legend: { 
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Monthly Book Circulation Chart
        function createMonthlyChart() {
            const ctx = document.getElementById('monthlyChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const monthlyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mar 2025', 'Apr 2025', 'May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025', 'Sep 2025', 'Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026', 'Feb 2026'],
                    datasets: [
                        {
                            label: 'Books Issued',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 4, 0],
                            borderColor: '#60a5fa',
                            backgroundColor: 'rgba(96, 165, 250, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#60a5fa',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Books Returned',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 5, 0],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor, drawBorder: false }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Daily Activity Trend Chart
        function createDailyChart() {
            const ctx = document.getElementById('dailyChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const dailyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Sat, Jan 3', 'Sun, Jan 4', 'Mon, Jan 5', 'Tue, Jan 6', 'Wed, Jan 7', 'Thu, Jan 8', 'Fri, Jan 9', 'Sat, Jan 10', 'Sun, Jan 11', 'Mon, Jan 12', 'Tue, Jan 13', 'Wed, Jan 14', 'Thu, Jan 15', 'Fri, Jan 16', 'Sat, Jan 17', 'Sun, Jan 18', 'Mon, Jan 19', 'Tue, Jan 20', 'Wed, Jan 21', 'Thu, Jan 22', 'Fri, Jan 23', 'Sat, Jan 24', 'Sun, Jan 25', 'Mon, Jan 26', 'Tue, Jan 27', 'Wed, Jan 28', 'Thu, Jan 29', 'Fri, Jan 30', 'Sun, Feb 1'],
                    datasets: [
                        {
                            label: 'Issues',
                            data: [2, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 1, 0, 0, 0, 2, 0],
                            borderColor: '#60a5fa',
                            backgroundColor: 'rgba(96, 165, 250, 0.15)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#60a5fa',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Returns',
                            data: [4, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 4,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor, drawBorder: false }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Most Borrowed Books Bar Chart
        function createBorrowedChart() {
            const ctx = document.getElementById('borrowedChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const borrowedColors = [
                '#8b5cf6',  // Purple
                '#6366f1',  // Indigo
                '#3b82f6',  // Blue
                '#0ea5e9',  // Cyan
                '#06b6d4',  // Sky
                '#10b981',  // Green
                '#f59e0b',  // Amber
                '#ef4444',  // Red
                '#ec4899',  // Pink
                '#f97316'   // Orange
            ];

            const borrowedChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Clean Code', 'The Pragmatic Programmer', 'Introduction to Algorithms', 'Design Patterns', 'Artificial Intelligence: A Modern Approach', 'Code Complete', 'Refactoring', 'Software Engineering', 'The Mythical Man-Month', 'Effective Java'],
                    datasets: [{
                        label: 'Times Borrowed',
                        data: [12, 9, 8, 7, 6, 5, 5, 4, 4, 3],
                        backgroundColor: borrowedColors,
                        borderColor: colors.isDark ? '#475569' : '#d1d5db',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Fine Collection Overview Chart
        function createFineCollectionChart() {
            const ctx = document.getElementById('fineCollectionChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const fineCollectionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'],
                    datasets: [
                        {
                            label: 'Fines Generated',
                            data: [0, 0, 10000, 0, 0, 0, 0, 0, 0, 8500, 0, 0],
                            backgroundColor: '#f97316',
                            borderColor: colors.isDark ? '#475569' : '#d1d5db',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'Fines Collected',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 2110, 0, 0],
                            backgroundColor: '#10b981',
                            borderColor: colors.isDark ? '#475569' : '#d1d5db',
                            borderWidth: 1,
                            borderRadius: 6
                        },
                        {
                            label: 'Pending Collection',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 6390, 0, 0],
                            backgroundColor: '#fbbf24',
                            borderColor: colors.isDark ? '#475569' : '#d1d5db',
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Collection Efficiency Trend Chart
        function createEfficiencyChart() {
            const ctx = document.getElementById('efficiencyChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const efficiencyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'],
                    datasets: [
                        {
                            label: 'Generated',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 10000, 5000, 3000],
                            borderColor: '#f97316',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#f97316',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Collected',
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 2110, 1500, 2000],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor, drawBorder: false }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Users by Role Chart
        function createUsersByRoleChart() {
            const ctx = document.getElementById('usersByRoleChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const usersByRoleChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Admin', 'Staff', 'Student'],
                    datasets: [{
                        data: [1, 1, 4],
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
                        borderColor: colors.isDark ? '#1e293b' : '#ffffff',
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(0);
                                    return context.label + ': ' + percentage + '%';
                                }
                            }
                        }
                    }
                }
            });
        }

        // User Activity by Role Chart
        function createUserActivityChart() {
            const ctx = document.getElementById('userActivityChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const userActivityChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [
                        {
                            label: 'Admin Activity',
                            data: [12, 15, 18, 22],
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Staff Activity',
                            data: [8, 10, 12, 14],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Student Activity',
                            data: [25, 28, 32, 35],
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#f59e0b',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor, drawBorder: false }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Overdue Distribution Chart
        function createOverdueDistributionChart() {
            const ctx = document.getElementById('overdueDistributionChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const overdueDistributionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['7-14 Days', '15-21 Days', '22-30 Days', '31-45 Days', '45+ Days'],
                    datasets: [{
                        label: 'Number of Books',
                        data: [1, 2, 2, 2, 1],
                        backgroundColor: ['#fbbf24', '#f97316', '#f97316', '#ef4444', '#dc2626'],
                        borderColor: colors.isDark ? '#475569' : '#d1d5db',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'x',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Overdue Trend Chart
        function createOverdueTrendChart() {
            const ctx = document.getElementById('overdueeTrendChart');
            if (!ctx) return;
            
            const colors = getThemeColors();
            const overdueTrendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb'],
                    datasets: [
                        {
                            label: 'Total Overdue',
                            data: [2, 4, 6, 8, 10, 7, 5, 6, 7, 8, 9, 8],
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#ef4444',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Critical (30+ Days)',
                            data: [0, 1, 2, 3, 4, 2, 1, 1, 2, 3, 3, 3],
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220, 38, 38, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 5,
                            pointBackgroundColor: '#dc2626',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: colors.textColor,
                                font: { size: 12 },
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.isDark ? '#334155' : '#ffffff',
                            titleColor: colors.textColor,
                            bodyColor: colors.textColor,
                            borderColor: colors.gridColor,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor, drawBorder: false }
                        },
                        y: {
                            ticks: { color: colors.textColor },
                            grid: { color: colors.gridColor }
                        }
                    }
                }
            });
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', function() {
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
        });

        // Update report
        function updateReport() {
            const reportType = document.getElementById('reportType').value;
            const timePeriod = document.getElementById('timePeriod').value;
            
            const transactionSection = document.querySelector('.section-transaction');
            const finesSection = document.querySelector('.section-fines');
            const usersSection = document.querySelector('.section-users');
            const overdueSection = document.querySelector('.section-overdue');
            const librarySection = document.querySelectorAll('.section-library');
            
            if (reportType === 'transactions') {
                transactionSection.classList.add('active');
                finesSection.classList.remove('active');
                usersSection.classList.remove('active');
                overdueSection.classList.remove('active');
                librarySection.forEach(section => section.classList.add('hidden'));
            } else if (reportType === 'fines') {
                finesSection.classList.add('active');
                transactionSection.classList.remove('active');
                usersSection.classList.remove('active');
                overdueSection.classList.remove('active');
                librarySection.forEach(section => section.classList.add('hidden'));
            } else if (reportType === 'users') {
                usersSection.classList.add('active');
                transactionSection.classList.remove('active');
                finesSection.classList.remove('active');
                overdueSection.classList.remove('active');
                librarySection.forEach(section => section.classList.add('hidden'));
            } else if (reportType === 'overdue') {
                overdueSection.classList.add('active');
                transactionSection.classList.remove('active');
                finesSection.classList.remove('active');
                usersSection.classList.remove('active');
                librarySection.forEach(section => section.classList.add('hidden'));
            } else {
                transactionSection.classList.remove('active');
                finesSection.classList.remove('active');
                usersSection.classList.remove('active');
                overdueSection.classList.remove('active');
                librarySection.forEach(section => section.classList.remove('hidden'));
            }
            
            console.log('Report Type:', reportType, 'Time Period:', timePeriod);
        }

        // Export functions
        function exportPDF() {
            alert('PDF Export functionality coming soon...');
        }

        function exportCSV() {
            alert('CSV Export functionality coming soon...');
        }

        // Theme change listener
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    // Recreate charts when theme changes
                    Chart.helpers.each(Chart.instances, function(instance) {
                        instance.destroy();
                    });
                    setTimeout(() => {
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
                    }, 100);
                }
            });
        });

        observer.observe(document.body, { attributes: true });
    </script>
@endsection
