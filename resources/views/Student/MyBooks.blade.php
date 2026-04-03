@extends('student.layouts.app')

@section('title', 'My Books')

@php
    $myBooksReportStudent = [
        'name' => auth()->user()->name ?? 'Student',
        'rollNo' => $student?->roll_no ?? ($student?->student_id ?? 'N/A'),
        'email' => auth()->user()->email ?? 'N/A',
        'department' => $student?->department?->name ?? 'N/A',
        'semester' => $student?->semester ?? 'N/A',
        'batch' => $student?->batch ? 'Batch ' . $student->batch : 'N/A',
    ];

    $myBooksReportExportConfig = [
        'modalId' => 'myBooksExportModal',
        'idPrefix' => 'myBooksExport',
        'scopeName' => 'myBooksExportScope',
        'labels' => [
            'title' => 'My Books Report',
            'description' => 'Print or download your books report.',
            'scopeTitle' => 'Scope',
            'scopeHint' => 'Use this page or all books matching the current filters.',
            'pageOptionTitle' => 'Current page',
            'pageOptionDescription' => 'Only the books visible on this page right now.',
            'allOptionTitle' => 'Filtered report',
            'allOptionDescription' => 'All books matching the current search and filters.',
            'badge' => 'Current page',
            'headline' => '0 books ready',
            'subtext' => 'Review your books report before printing or downloading it.',
            'previewTitle' => 'Preview',
            'previewDescription' => 'Books included in the report.',
            'previewCount' => '0 rows',
            'emptyPreview' => 'No books selected for preview.',
            'footerNote' => 'Using the current page for spreadsheet export.',
            'cancelButton' => 'Cancel',
            'downloadButton' => 'Download Excel',
            'printButton' => 'Print Report',
        ],
        'document' => [
            'systemTitle' => $libraryBranding['name'] ?? 'Library Management System',
            'reportTitle' => 'My Books Report',
        ],
        'columns' => [
            ['key' => 'bookTitle', 'label' => 'Book', 'width' => '22%', 'emphasis' => true],
            ['key' => 'author', 'label' => 'Author', 'width' => '13%'],
            ['key' => 'issueDate', 'label' => 'Issue Date', 'width' => '11%', 'nowrap' => true],
            ['key' => 'dueDate', 'label' => 'Due Date', 'width' => '11%', 'nowrap' => true],
            ['key' => 'returnDate', 'label' => 'Return Date', 'width' => '11%', 'nowrap' => true],
            ['key' => 'status', 'label' => 'Status', 'width' => '8%', 'align' => 'center', 'nowrap' => true],
            ['key' => 'fineAmount', 'label' => 'Fine Amount', 'width' => '12%', 'align' => 'right'],
            ['key' => 'fineStatus', 'label' => 'Fine Status', 'width' => '12%', 'align' => 'center'],
        ],
    ];
@endphp

@push('styles')
    @include('shared.report-export.styles')
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

        .page-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-left: auto;
        }

        .report-trigger-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 2.5rem;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
            white-space: nowrap;
        }

        .report-trigger-btn svg {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        body.light-theme .report-trigger-btn {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #bfdbfe;
            color: #1d4ed8;
            box-shadow: 0 10px 18px rgba(37, 99, 235, 0.12);
        }

        body.dark-theme .report-trigger-btn {
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.3) 0%, rgba(14, 116, 144, 0.28) 100%);
            border-color: #1d4ed8;
            color: #bfdbfe;
            box-shadow: 0 12px 22px rgba(2, 6, 23, 0.32);
        }

        .report-trigger-btn:hover:not(:disabled) {
            transform: translateY(-1px);
        }

        body.light-theme .report-trigger-btn:hover:not(:disabled) {
            border-color: #93c5fd;
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.16);
        }

        body.dark-theme .report-trigger-btn:hover:not(:disabled) {
            border-color: #60a5fa;
            box-shadow: 0 16px 26px rgba(2, 6, 23, 0.38);
        }

        .report-trigger-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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

            .page-actions {
                width: 100%;
                margin-left: 0;
                justify-content: flex-start;
            }

            .report-trigger-btn {
                width: 100%;
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

            <div class="page-actions">
                <button
                    type="button"
                    class="report-trigger-btn"
                    id="printReportBtn"
                    aria-controls="myBooksExportModal"
                    aria-haspopup="dialog"
                    {{ $totalIssued === 0 ? 'disabled' : '' }}
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M6 9V2h12v7" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 14h12v8H6z" />
                    </svg>
                    <span>Print Report</span>
                </button>
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
            @include('shared.student-portal-pagination.markup', [
                'containerId' => 'paginationContainer',
                'fromId' => 'startRecord',
                'toId' => 'endRecord',
                'totalId' => 'totalRecords',
                'pageInfoId' => 'pageInfo',
                'buttonsId' => 'paginationButtons',
                'label' => 'books',
            ])
        </div>
        </div>
    </div>

    @include('shared.report-export.modal', ['reportExportConfig' => $myBooksReportExportConfig])

@endsection

@push('scripts')
    @include('shared.report-export.scripts')
    <script>
        // Book data from backend
        const booksData = {!! $issuedBooksJson !!};
        const myBooksReportStudent = @json($myBooksReportStudent);
        const myBooksReportBranding = window.LibraryBranding?.normalize
            ? window.LibraryBranding.normalize(window.__LIBRARY_BRANDING__ ?? {})
            : (window.__LIBRARY_BRANDING__ ?? {});
        const myBooksReportSystemTitle = myBooksReportBranding?.name || 'Library Management System';
        const printReportBtn = document.getElementById('printReportBtn');
        const myBooksExportModal = document.getElementById('myBooksExportModal');
        const DEFAULT_BOOKS_PER_PAGE = 10;
        const ALLOWED_PAGE_SIZES = [10, 20, 50, 100];
        let booksPerPage = getInitialBooksPerPage();
        let currentPage = getInitialPage();
        let filteredBooks = [];
        let booksPagination = null;
        let booksReportFeedbackUI = null;
        let myBooksExportWorkflow = null;
        let myBooksExportLastTrigger = null;

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

        function renderPagination(totalRecords, startRecord, endRecord, totalPages) {
            booksPagination?.render({
                total: totalRecords,
                from: startRecord,
                to: endRecord,
                currentPage,
                lastPage: totalPages,
                onPageChange: (page) => goToPage(page),
            });
            syncPaginationState();
        }

        // Render books table with pagination
        function renderBooks(books, { preservePage = false } = {}) {
            const tableBody = document.getElementById('booksTableBody');
            const emptyState = document.getElementById('emptyState');

            filteredBooks = books;
            updateReportButtonState(books.length);

            if (!preservePage) {
                currentPage = 1;
            }

            currentPage = Math.min(Math.max(currentPage, 1), getTotalPages());
            updateStats(books);

            if (books.length === 0) {
                tableBody.innerHTML = '';
                emptyState.style.display = 'block';
                renderPagination(0, 0, 0, 0);
                syncPaginationState();
                return;
            }

            emptyState.style.display = 'none';

            // Display current page
            displayPage(currentPage);
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
            renderPagination(filteredBooks.length, startRecord, endRecord, totalPages);
        }

        // Navigation functions
        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                displayPage(currentPage);
            }
        }

        function nextPage() {
            const totalPages = getTotalPages();
            if (currentPage < totalPages) {
                currentPage++;
                displayPage(currentPage);
            }
        }

        function goToPage(page) {
            currentPage = page;
            displayPage(currentPage);
            syncPaginationState();
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

        function updateReportButtonState(totalRecords = filteredBooks.length) {
            if (!printReportBtn) {
                return;
            }

            printReportBtn.disabled = Number(totalRecords || 0) === 0;
        }

        function setupReportFeedback() {
            booksReportFeedbackUI = window.getStudentPortalFeedback?.() || null;
        }

        function showReportToast(type, title, message, timeout) {
            if (booksReportFeedbackUI) {
                booksReportFeedbackUI.showToast({
                    type,
                    title,
                    message,
                    timeout,
                });
                return;
            }

            window.showStudentPortalToast?.(type, title, message, timeout);
        }

        function formatDisplayLabel(value, fallback = 'N/A') {
            const normalizedValue = String(value ?? '').trim();

            if (!normalizedValue) {
                return fallback;
            }

            return normalizedValue
                .replace(/[-_]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim()
                .replace(/\b\w/g, (character) => character.toUpperCase());
        }

        function formatBookStatusLabel(status) {
            return formatDisplayLabel(status, 'Issued');
        }

        function formatFineStatusLabel(status) {
            if (String(status ?? '').toLowerCase() === 'none') {
                return 'No Fine';
            }

            return formatDisplayLabel(status, 'No Fine');
        }

        function formatBookFineAmount(fineValue) {
            return fineValue && fineValue !== 'No Fine' ? fineValue : 'No Fine';
        }

        function normalizeReportIsbn(isbn) {
            return String(isbn ?? '')
                .replace(/^isbn:\s*/i, '')
                .trim();
        }

        function buildBooksReportFilterParams() {
            const params = new URLSearchParams();
            const searchValue = document.getElementById('searchInput')?.value?.trim();
            const statusValue = document.getElementById('statusFilter')?.value || 'all';
            const fineStatusValue = document.getElementById('fineStatusFilter')?.value || 'all';
            const categoryValue = document.getElementById('categoryFilter')?.value || 'all';
            const sortValue = document.getElementById('sortFilter')?.value || 'due-date-asc';

            if (searchValue) {
                params.set('search', searchValue);
            }

            if (statusValue !== 'all') {
                params.set('status', statusValue);
            }

            if (fineStatusValue !== 'all') {
                params.set('fine_status', fineStatusValue);
            }

            if (categoryValue !== 'all') {
                params.set('category', categoryValue);
            }

            if (sortValue !== 'due-date-asc') {
                params.set('sort', sortValue);
            }

            return params;
        }

        function getBooksReportFilterSummary() {
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const fineStatusFilter = document.getElementById('fineStatusFilter');
            const categoryFilter = document.getElementById('categoryFilter');
            const sortFilter = document.getElementById('sortFilter');

            return {
                search: searchInput?.value?.trim() || 'All books',
                status: statusFilter?.value === 'all'
                    ? 'All statuses'
                    : (statusFilter?.selectedOptions?.[0]?.textContent?.trim() || formatDisplayLabel(statusFilter?.value, 'All statuses')),
                fineStatus: fineStatusFilter?.value === 'all'
                    ? 'All fine statuses'
                    : (fineStatusFilter?.selectedOptions?.[0]?.textContent?.trim() || formatFineStatusLabel(fineStatusFilter?.value)),
                category: categoryFilter?.value === 'all'
                    ? 'All categories'
                    : (categoryFilter?.selectedOptions?.[0]?.textContent?.trim() || formatDisplayLabel(categoryFilter?.value, 'All categories')),
                sort: sortFilter?.selectedOptions?.[0]?.textContent?.trim() || 'Due Date (Asc)',
            };
        }

        function getMyBooksDocumentDetails() {
            return [
                { label: 'Student Name', value: myBooksReportStudent.name || 'Student' },
                { label: 'Roll No', value: myBooksReportStudent.rollNo || 'N/A' },
                { label: 'Email', value: myBooksReportStudent.email || 'N/A' },
                { label: 'Department', value: myBooksReportStudent.department || 'N/A' },
                { label: 'Semester', value: myBooksReportStudent.semester || 'N/A' },
                { label: 'Batch', value: myBooksReportStudent.batch || 'N/A' },
            ];
        }

        function buildMyBooksExportMetaRows(context) {
            if (typeof window.ReportExportTemplates?.buildStandardMetaRows === 'function') {
                return window.ReportExportTemplates.buildStandardMetaRows({
                    systemTitle: myBooksReportSystemTitle,
                    reportTitle: 'My Books Report',
                    generatedAtLabel: context.generatedAtLabel,
                    documentDetails: getMyBooksDocumentDetails(),
                });
            }

            return [
                [myBooksReportSystemTitle],
                ['My Books Report'],
                [context.generatedAtLabel],
                [''],
                ...getMyBooksDocumentDetails().map((detail) => [detail.label, detail.value]),
                [''],
            ];
        }

        function buildMyBooksExportFilename(context) {
            const generatedAt = context?.generatedAt instanceof Date
                ? context.generatedAt
                : new Date(context?.generatedAt || Date.now());
            const dateStamp = Number.isNaN(generatedAt.getTime())
                ? new Date().toISOString().slice(0, 10)
                : generatedAt.toISOString().slice(0, 10);
            const scopeLabel = context?.isAllScope ? 'full' : 'page';
            const studentSlug = String(myBooksReportStudent.name || myBooksReportStudent.email || 'student')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '') || 'student';

            return `my-books-${studentSlug}-${scopeLabel}-${dateStamp}.xls`;
        }

        function describeMyBooksExportContext(context) {
            const filters = getBooksReportFilterSummary();

            return {
                badgeLabel: context.isAllScope ? 'Filtered report' : 'Current page',
                headline: `${context.rowsReady} ${context.rowsReady === 1 ? 'book' : 'books'} ready`,
                subtext: context.isAllScope
                    ? 'Print or download every book that matches your current filters.'
                    : 'Print or download only the books visible on this page.',
                previewCaption: context.isAllScope
                    ? 'Preview of the first books from your full filtered report.'
                    : 'Preview of the books on the current page.',
                previewCountText: `${context.rowsReady} ${context.rowsReady === 1 ? 'book' : 'books'}`,
                footerNote: context.isAllScope
                    ? 'Using all filtered books for print and spreadsheet export.'
                    : 'Using the current page for print and spreadsheet export.',
                emptyMessage: 'No books match the selected filters.',
                summaryItems: [
                    { label: 'Report scope', value: context.scopeLabel },
                    {
                        label: 'Books included',
                        value: context.isLoading
                            ? 'Preparing...'
                            : `${context.rowsReady} ${context.rowsReady === 1 ? 'book' : 'books'}`,
                    },
                    { label: 'Matching total', value: `${context.total} ${context.total === 1 ? 'book' : 'books'}` },
                    {
                        label: context.isAllScope ? 'Pages covered' : 'Page',
                        value: context.isAllScope
                            ? `All ${context.lastPage} ${context.lastPage === 1 ? 'page' : 'pages'}`
                            : `${context.currentPage} of ${context.lastPage}`,
                    },
                    { label: 'Status', value: filters.status },
                    { label: 'Fine Status', value: filters.fineStatus },
                    { label: 'Category', value: filters.category },
                    { label: 'Search', value: filters.search },
                ],
            };
        }

        function getCurrentBooksPageRows() {
            const startIndex = Math.max(0, (currentPage - 1) * booksPerPage);
            return filteredBooks.slice(startIndex, startIndex + booksPerPage);
        }

        function mapBookToExportRow(book) {
            const normalizedIsbn = normalizeReportIsbn(book?.isbn);

            return {
                bookTitle: normalizedIsbn
                    ? `${book?.title || 'N/A'} (${normalizedIsbn})`
                    : (book?.title || 'N/A'),
                author: book?.author || 'N/A',
                issueDate: book?.issueDate || 'N/A',
                dueDate: book?.dueDate || 'N/A',
                returnDate: book?.returnDate || '-',
                status: formatBookStatusLabel(book?.status),
                fineAmount: formatBookFineAmount(book?.fine),
                fineStatus: formatFineStatusLabel(book?.fineStatus),
            };
        }

        function setupMyBooksExportWorkflow() {
            if (typeof window.ReportExportWorkflow !== 'function') {
                return;
            }

            myBooksExportWorkflow = new window.ReportExportWorkflow({
                modalId: 'myBooksExportModal',
                idPrefix: 'myBooksExport',
                scopeName: 'myBooksExportScope',
                downloadFormat: 'excel-xml',
                sheetName: 'My Books Report',
                document: {
                    systemTitle: myBooksReportSystemTitle,
                    reportTitle: 'My Books Report',
                },
                labels: {
                    printButton: 'Print Report',
                    allScopePrintButton: 'Print Full Report',
                    downloadButton: 'Download Excel',
                    allScopeDownloadButton: 'Download Full Excel',
                },
                messages: {
                    emptyMessage: 'There are no books in the current result set.',
                    preparingMessage: 'Preparing your full My Books report. Please wait.',
                    printReadyMessage: 'The print dialog will open in a new window for the current My Books page.',
                    fullPrintReadyMessage: 'The print dialog will open in a new window for the full filtered My Books report.',
                    exportReadyMessage: 'The current My Books page has been exported to Excel.',
                    fullExportReadyMessage: 'The full filtered My Books report has been exported to Excel.',
                    fullLoadFailedMessage: 'Something went wrong while preparing your My Books report.',
                },
                columns: [
                    { key: 'bookTitle', label: 'Book', width: '22%', emphasis: true },
                    { key: 'author', label: 'Author', width: '13%' },
                    { key: 'issueDate', label: 'Issue Date', width: '11%', nowrap: true },
                    { key: 'dueDate', label: 'Due Date', width: '11%', nowrap: true },
                    { key: 'returnDate', label: 'Return Date', width: '11%', nowrap: true },
                    { key: 'status', label: 'Status', width: '8%', align: 'center', nowrap: true },
                    { key: 'fineAmount', label: 'Fine Amount', width: '12%', align: 'right' },
                    { key: 'fineStatus', label: 'Fine Status', width: '12%', align: 'center' },
                ],
                openModal: (modalId, focusTarget) => openMyBooksExportModal(modalId, focusTarget),
                closeModal: (modalId) => closeMyBooksExportModal(modalId),
                showToast: (type, title, message, timeout) => showReportToast(type, title, message, timeout),
                getCurrentRows: () => getCurrentBooksPageRows(),
                getAllRows: () => ({
                    rows: filteredBooks,
                    generatedAt: new Date().toISOString(),
                }),
                mapRow: (book) => mapBookToExportRow(book),
                buildFilterParams: () => buildBooksReportFilterParams(),
                getListingState: () => ({
                    total: filteredBooks.length,
                    currentPage: Math.max(1, currentPage),
                    lastPage: Math.max(1, getTotalPages()),
                    perPage: Math.max(1, booksPerPage),
                }),
                getScopeLabel: (scope) => scope === 'all' ? 'Entire filtered books report' : 'Current page',
                getFilename: (context) => buildMyBooksExportFilename(context),
                getDocumentDetails: () => getMyBooksDocumentDetails(),
                getExportMetaRows: (context) => buildMyBooksExportMetaRows(context),
                describeContext: (context) => describeMyBooksExportContext(context),
            }).init();
        }

        function openMyBooksExportModal(modalId, focusTarget) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            const panel = modal.querySelector('.report-export-panel');
            const closeButton = modal.querySelector('.report-export-close-btn');
            const activeElement = document.activeElement;

            myBooksExportLastTrigger = activeElement && !modal.contains(activeElement)
                ? activeElement
                : focusTarget;

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            modal.scrollTop = 0;
            panel?.scrollTo?.({ top: 0, behavior: 'auto' });

            window.setTimeout(() => {
                const nextFocusTarget = closeButton || panel || focusTarget;

                if (typeof nextFocusTarget?.focus === 'function') {
                    try {
                        nextFocusTarget.focus({ preventScroll: true });
                    } catch (error) {
                        nextFocusTarget.focus();
                    }
                }
            }, 20);
        }

        function closeMyBooksExportModal(modalId = 'myBooksExportModal') {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            myBooksExportWorkflow?.handleModalClosed?.();

            const focusTarget = myBooksExportLastTrigger;
            myBooksExportLastTrigger = null;

            if (typeof focusTarget?.focus === 'function') {
                window.setTimeout(() => focusTarget.focus(), 20);
            }
        }

        function setupMyBooksExportModalEvents() {
            document.addEventListener('click', (event) => {
                const closeButton = event.target.closest('[data-modal-close="myBooksExportModal"]');
                if (closeButton) {
                    closeMyBooksExportModal('myBooksExportModal');
                    return;
                }

                if (event.target === myBooksExportModal) {
                    closeMyBooksExportModal('myBooksExportModal');
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && myBooksExportModal?.classList.contains('is-open')) {
                    closeMyBooksExportModal('myBooksExportModal');
                }
            });
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
            booksPagination = new window.StudentPortalPagination({
                containerId: 'paginationContainer',
                fromId: 'startRecord',
                toId: 'endRecord',
                totalId: 'totalRecords',
                pageInfoId: 'pageInfo',
                buttonsId: 'paginationButtons',
            });

            setupReportFeedback();
            setupMyBooksExportWorkflow();
            setupMyBooksExportModalEvents();

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
            const handleReportOpen = () => {
                if (!myBooksExportWorkflow) {
                    showReportToast('error', 'Report unavailable', 'My Books report export is unavailable right now.');
                    return;
                }

                myBooksExportWorkflow.open();
            };

            if (searchInput) searchInput.addEventListener('input', filterBooks);
            if (statusFilter) statusFilter.addEventListener('change', filterBooks);
            if (fineStatusFilter) fineStatusFilter.addEventListener('change', filterBooks);
            if (categoryFilter) categoryFilter.addEventListener('change', filterBooks);
            if (sortFilter) sortFilter.addEventListener('change', filterBooks);
            if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetFilters);
            if (printReportBtn) printReportBtn.addEventListener('click', handleReportOpen);
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
