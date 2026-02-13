@extends('Admin.layouts.app')

@section('title', 'Fines')

@push('styles')
    <style>
        /* CSS Variables - Define theme colors */
        :root {
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --bg-primary: #f9fafb;
            --bg-secondary: #ffffff;
            --border-color: #e5e7eb;
        }

        /* Dark theme variables */
        body.dark-theme {
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --border-color: #334155;
        }

        /* Base Styles - Matching My Books Page */
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

        /* Dashboard Layout - Matching My Books */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
        }

        /* Page Header */
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

        /* Stats Cards - Exactly like My Books Page */
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

        /* Stat card colors matching My Books */
        .stat-card.total-fines {
            border-left-color: #2563eb;
        }

        .stat-card.collected {
            border-left-color: #f59e0b;
        }

        .stat-card.pending {
            border-left-color: #ef4444;
        }

        .stat-card.waived {
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
        body.light-theme .stat-card.total-fines .stat-icon {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.light-theme .stat-card.collected .stat-icon {
            background-color: #fef3c7;
            color: #f59e0b;
        }

        body.light-theme .stat-card.pending .stat-icon {
            background-color: #fee2e2;
            color: #ef4444;
        }

        body.light-theme .stat-card.waived .stat-icon {
            background-color: #dcfce7;
            color: #10b981;
        }

        /* Dark theme stat icons */
        body.dark-theme .stat-card.total-fines .stat-icon {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        body.dark-theme .stat-card.collected .stat-icon {
            background-color: #78350f;
            color: #fbbf24;
        }

        body.dark-theme .stat-card.pending .stat-icon {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .stat-card.waived .stat-icon {
            background-color: #064e3b;
            color: #34d399;
        }

        .stat-number {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
            font-family: 'Inter', sans-serif;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        /* Search and Filter Container - Like My Books */
        .search-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
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
            flex: 1;
            min-width: 200px;
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

        /* Filters Container */
        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
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

        /* Fines Table with Stripes - Enhanced */
        .fines-table-container {
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        body.light-theme .fines-table-container {
            background: white;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .fines-table-container {
            background: #1e293b;
            border: 1px solid #334155;
        }

        .fines-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
            min-width: 700px;
        }

        .fines-table thead {
            border-bottom: 1px solid;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        body.light-theme .fines-table thead {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-color: #e5e7eb;
        }

        body.dark-theme .fines-table thead {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-color: #334155;
        }

        .fines-table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            white-space: nowrap;
        }

        .fines-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid;
            vertical-align: middle;
        }

        body.light-theme .fines-table td {
            border-color: #f1f5f9;
        }

        body.dark-theme .fines-table td {
            border-color: #334155;
        }

        /* Striped rows - clean alternating pattern */
        .fines-table tbody tr:nth-child(even) {
            background-color: rgba(241, 245, 249, 0.3);
        }

        body.dark-theme .fines-table tbody tr:nth-child(even) {
            background-color: rgba(30, 41, 59, 0.3);
        }

        /* Hover effects */
        .fines-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        body.dark-theme .fines-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .fines-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Status Badges - Same as My Books but customized for fines */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            gap: 0.3rem;
        }

        .status-pending {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .status-paid {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-waived {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.dark-theme .status-pending {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .status-paid {
            background-color: #14532d;
            color: #86efac;
        }

        body.dark-theme .status-waived {
            background-color: #1e3a8a;
            color: #93c5fd;
        }

        /* Fine Amount */
        .fine-amount {
            font-weight: 600;
            font-size: 0.75rem;
        }

        body.light-theme .fine-amount.pending {
            color: #dc2626;
        }

        body.dark-theme .fine-amount.pending {
            color: #f87171;
        }

        body.light-theme .fine-amount.paid {
            color: #16a34a;
        }

        body.dark-theme .fine-amount.paid {
            color: #86efac;
        }

        /* Action Buttons - Optimized */
        .action-buttons {
            display: flex;
            gap: 0.4rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.4rem 0.8rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .btn-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
        }

        .btn-paid:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
        }

        .btn-waive {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2);
        }

        .btn-waive:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
        }

        /* Email Button Styles */
        .btn-email {
            padding: 0.4rem 0.6rem;
            border-radius: 0.375rem;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: transparent;
            color: var(--text-secondary);
        }

        body.light-theme .btn-email {
            border-color: #e5e7eb;
            background-color: #f8fafc;
        }

        body.dark-theme .btn-email {
            border-color: #475569;
            background-color: #1e293b;
        }

        .btn-email:hover {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border-color: #f97316;
            transform: translateY(-1px);
        }

        /* Utility Classes for Layout */
        .text-center {
            text-align: center;
        }

        .flex {
            display: flex;
        }

        .justify-center {
            justify-content: center;
        }

        .items-center {
            align-items: center;
        }

        .py-8 {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        .gap-2 {
            gap: 0.5rem;
        }

        /* Pagination - Modern Style */
        .pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-top: 1px solid;
        }

        body.light-theme .pagination-container {
            border-top-color: #e5e7eb;
        }

        body.dark-theme .pagination-container {
            border-top-color: #334155;
        }

        .pagination-info {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .pagination-buttons {
            display: flex;
            gap: 0.3rem;
            align-items: center;
        }

        .pagination-btn {
            padding: 0.4rem 0.7rem;
            border: 1px solid;
            border-radius: 0.375rem;
            background: transparent;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.75rem;
            font-weight: 500;
            min-width: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
        }

        body.light-theme .pagination-btn {
            border-color: #e5e7eb;
        }

        body.dark-theme .pagination-btn {
            border-color: #475569;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: rgba(0, 0, 0, 0.02);
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        body.dark-theme .pagination-btn:hover:not(:disabled) {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: #64748b;
        }

        .pagination-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #2563eb;
        }

        .pagination-btn.active:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            transform: none !important;
        }

        .pagination-ellipsis {
            padding: 0.5rem 0.25rem;
            color: var(--text-secondary);
        }

        /* Empty State - Matching My Books */
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

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 1.25rem;
            height: 1.25rem;
            border: 3px solid;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        body.light-theme .loading-spinner {
            border-color: #f3f4f6;
        }

        body.dark-theme .loading-spinner {
            border-color: #374151;
            border-top-color: #60a5fa;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
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
                flex-direction: column;
                align-items: stretch;
                padding: 0.75rem;
                gap: 0.5rem;
            }

            .search-box,
            .filters-container {
                width: 100%;
            }

            .filter-select {
                min-width: 100%;
            }

            /* Table Styles - Matching UserManagement */
            .table-container {
                border-radius: 8px;
                overflow: hidden;
                border: 1px solid;
                margin-top: 12px;
            }

            body.light-theme .table-container {
                background-color: #ffffff;
                border-color: #e5e7eb;
            }

            body.dark-theme .table-container {
                background-color: #1e293b;
                border-color: #334155;
            }

            .table-wrapper {
                overflow-x: hidden;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                min-width: 1110px;
            }

            .table th {
                padding: 6px 8px;
                text-align: left;
                font-weight: 600;
                font-size: 11px;
                border-bottom: 1px solid;
                white-space: nowrap;
            }

            body.light-theme .table th {
                background-color: #f8fafc;
                border-color: #e2e8f0;
                color: #475569;
            }

            body.dark-theme .table th {
                background-color: #1e293b;
                border-color: #334155;
                color: #cbd5e1;
            }

            .table td {
                padding: 6px 8px;
                border-bottom: 1px solid;
                vertical-align: middle;
                font-size: 13px;
            }

            body.light-theme .table td {
                border-color: #e2e8f0;
                color: #0f172a;
            }

            body.dark-theme .table td {
                border-color: #334155;
                color: #f1f5f9;
            }

            /* Status Badge */
            .status-badge {
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: 500;
                display: inline-block;
            }

            .status-badge {
                background-color: #fef3c7;
                color: #92400e;
            }

            /* Action Buttons */
            .action-btn {
                padding: 4px 8px;
                margin-right: 4px;
                border: none;
                border-radius: 3px;
                cursor: pointer;
                font-size: 11px;
                font-weight: 500;
                color: white;
                transition: opacity 0.2s;
            }

            .action-btn:hover {
                opacity: 0.8;
            }

            .action-btn-paid {
                background: #2563eb;
            }

            .action-btn-waive {
                background: #10b981;
            }

            .action-btn-email {
                background: #6b7280;
            }

            /* Utility Classes */
            .text-center {
                text-align: center;
            }

            .text-secondary {
                color: #64748b;
            }

            body.dark-theme .text-secondary {
                color: #94a3b8;
            }

            .fines-table {
                display: block;
                overflow-x: auto;
            }

            .fines-table th,
            .fines-table td {
                padding: 0.5rem 0.75rem;
            }

            /* Utility Classes */
            .mt-4 {
                margin-top: 1rem;
            }

            /* Responsive Design */
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }

            .pagination-container {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .pagination-buttons {
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-container">
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h1>Fines Records</h1>
                    <p class="text-secondary">Manage and update fines for overdue books</p>
                </div>
                <button onclick="finesManager.exportToCSV()" class="action-btn btn-waive">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Export CSV
                </button>
            </div>

            <!-- Stats Cards - Matching My Books Page -->
            <div class="stats-grid">
                <!-- Total Fines Card -->
                <div class="stat-card total-fines">
                    <div class="stat-header">
                        <h3 class="stat-title">Total Fines</h3>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number" id="totalFines">₹{{ number_format($stats['total'] ?? 0, 2) }}</div>
                    <div class="stat-label">All issued fines</div>
                </div>

                <!-- Collected fines card -->
                <div class="stat-card collected">
                    <div class="stat-header">
                        <h3 class="stat-title">Collected</h3>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                                <path d="M4 22h16"></path>
                                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number" id="collectedFines">₹{{ number_format($stats['collected'] ?? 0, 2) }}</div>
                    <div class="stat-label">Total collected amount</div>
                </div>

                <!-- Pending Fine card -->
                <div class="stat-card pending">
                    <div class="stat-header">
                        <h3 class="stat-title">Pending</h3>
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
                    <div class="stat-number" id="pendingFines">₹{{ number_format($stats['pending'] ?? 0, 2) }}</div>
                    <div class="stat-label">To be collected</div>
                </div>

                <!-- Waived Fine card -->
                <div class="stat-card waived">
                    <div class="stat-header">
                        <h3 class="stat-title">Waived</h3>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number" id="waivedFines">₹{{ number_format($stats['waived'] ?? 0, 2) }}</div>
                    <div class="stat-label">Waived amount</div>
                </div>
            </div>

            <!-- Fines Table -->
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Book Title</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Fine Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="finesTableBody">
                            @forelse($finesTableData as $fine)
                                <tr>
                                    <td>{{ $fine['student_id'] }}</td>
                                    <td>{{ $fine['student_name'] }}</td>
                                    <td>{{ $fine['book_title'] }}</td>
                                    <td>{{ $fine['due_date'] }}</td>
                                    <td>{{ $fine['days_overdue'] }}</td>
                                    <td>{{ $fine['fine_amount'] }}</td>
                                    <td>
                                        <span class="status-badge">
                                            {{ $fine['status'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(strtolower($fine['status']) === 'pending')
                                            <button onclick="markFinePaid({{ json_encode($fine) }})" class="action-btn action-btn-paid">Paid</button>
                                            <button onclick="openWaiveModal({{ json_encode($fine) }})" class="action-btn action-btn-waive">Waive</button>
                                        @endif
                                        <button onclick="sendFineEmail({{ json_encode($fine) }})" class="action-btn action-btn-email">Email</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-secondary">No fines found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($fines && $fines->hasPages())
                <div id="paginationContainer" class="mt-4">
                    {{ $fines->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Waive Fine Modal -->
    <div id="waiveModal"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 50; display: none; background-color: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center;"
        class="flex items-center justify-center">
        <div class="card" style="width: 100%; max-width: 28rem; margin-left: 1rem; margin-right: 1rem;">
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-primary">Waive Fine</h3>
                <button onclick="finesManager.closeWaiveModal()" class="transition text-secondary hover:text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <p class="mb-4 text-secondary">Please provide a reason for waiving this fine:</p>
                <textarea id="waiveReason" rows="4"
                    class="w-full px-3 py-2 transition bg-transparent border border-gray-300 rounded-lg dark:border-gray-600 text-primary focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter reason..."></textarea>
                <div class="flex justify-end mt-6 space-x-3">
                    <button onclick="finesManager.closeWaiveModal()"
                        class="px-4 py-2 transition border border-gray-300 rounded-lg dark:border-gray-600 text-primary hover:bg-gray-50 dark:hover:bg-gray-800">
                        Cancel
                    </button>
                    <button onclick="finesManager.confirmWaiveFine()"
                        class="px-4 py-2 text-white transition bg-green-600 rounded-lg hover:bg-green-700">
                        Waive Fine
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 50; display: none; background-color: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center;"
        class="flex items-center justify-center">
        <div class="card" style="width: 100%; max-width: 24rem; margin-left: 1rem; margin-right: 1rem;">
            <div class="p-6 text-center">
                <div
                    class="flex items-center justify-center w-12 h-12 mx-auto mb-4 text-green-600 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h3 class="mb-2 text-lg font-semibold text-primary" id="successTitle">Action Successful!</h3>
                <p class="mb-6 text-secondary" id="successMessage">The action has been completed.</p>
                <button onclick="finesManager.closeSuccessModal()"
                    class="px-4 py-2 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                    OK
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Your backend JavaScript remains exactly the same - just updating event listeners for new filter system
        const finesManager = {
            state: {
                currentPage: 1,
                perPage: 10,
                filter: 'all',
                search: '',
                fines: [],
                stats: {},
                pagination: {}
            },

            // BroadcastChannel for cross-tab communication
            fineUpdateChannel: null,

            init() {
                console.log('[Fines] Initializing fines manager...');
                
                // Initialize state with pagination info from URL
                const urlParams = new URLSearchParams(window.location.search);
                this.state.search = urlParams.get('search') || '';
                this.state.filter = urlParams.get('status') || 'all';
                this.state.currentPage = parseInt(urlParams.get('page') || '1');

                // Setup event listeners and broadcast channel
                this.setupEventListeners();
                this.setupBroadcastChannel();
                
                console.log('[Fines] Page initialized with:', {
                    search: this.state.search,
                    filter: this.state.filter,
                    page: this.state.currentPage
                });
            },

            setupBroadcastChannel() {
                try {
                    this.fineUpdateChannel = new BroadcastChannel('fine_updates');
                    console.log('[Fines] BroadcastChannel object created:', typeof this.fineUpdateChannel);
                    console.log('[Fines] Channel name: fine_updates');

                    // Optional: Listen for messages from ViewStudent page
                    this.fineUpdateChannel.addEventListener('message', (event) => {
                        console.log('[Fines] Received message (should not happen):', event.data);
                    });

                    console.log('[Fines] BroadcastChannel initialized and listener attached');
                } catch (e) {
                    console.error('[Fines] BroadcastChannel ERROR:', e);
                    console.error('[Fines] Error message:', e.message);
                    console.error('[Fines] Error stack:', e.stack);
                }
            },

            notifyFineUpdate(fineId, status) {
                console.log('[Fines] notifyFineUpdate called with:', {
                    fineId,
                    status
                });

                // Notify via localStorage (for other tabs)
                try {
                    localStorage.setItem('fineUpdated', JSON.stringify({
                        fineId,
                        status,
                        timestamp: Date.now()
                    }));
                    console.log('[Fines] localStorage notification sent');
                } catch (e) {
                    console.error('[Fines] Error updating localStorage:', e);
                }

                // Notify via BroadcastChannel (for same browser window)
                if (this.fineUpdateChannel) {
                    try {
                        const messageData = {
                            type: 'fineUpdated',
                            fineId: fineId,
                            status: status,
                            timestamp: Date.now()
                        };
                        console.log('[Fines] About to send BroadcastChannel message:', messageData);
                        this.fineUpdateChannel.postMessage(messageData);
                        console.log('[Fines] ✓ BroadcastChannel message sent successfully');
                        console.log(
                            '[Fines] Message details - type: postMessage, channel: fine_updates, contains fineId:',
                            fineId);
                    } catch (e) {
                        console.error('[Fines] ✗ Error broadcasting fine update:', e);
                        console.error('[Fines] Error message:', e.message);
                    }
                } else {
                    console.warn('[Fines] ✗ BroadcastChannel not initialized (null)');
                }
            },

            setupEventListeners() {
                // Setup search from AdminDataTable component
                const searchInput = document.querySelector('[data-table-search]');
                if (searchInput) {
                    let debounceTimer;
                    searchInput.addEventListener('keyup', (e) => {
                        clearTimeout(debounceTimer);
                        this.state.search = e.target.value;
                        this.state.currentPage = 1;
                        // Note: AdminDataTable has built-in client-side search
                        // For server-side filtering, implement custom logic here
                    });
                }

                // Setup status filter from AdminDataTable component
                const filterBtns = document.querySelectorAll('[data-filter]');
                filterBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const filterValue = e.currentTarget.getAttribute('data-filter');
                        this.state.filter = filterValue;
                        this.state.currentPage = 1;
                        // Update active state
                        filterBtns.forEach(b => b.classList.remove('active'));
                        e.currentTarget.classList.add('active');
                        // For server-side filtering, call: this.loadFines();
                    });
                });
            },

            setFilter(status) {
                // This is kept for backward compatibility if needed
                this.state.filter = status;
                this.state.currentPage = 1;
                // this.loadFines(); // Uncomment if using server-side filtering
            },

            loadFines() {
                // Build query parameters for filtering/searching
                const params = new URLSearchParams();
                
                if (this.state.search) {
                    params.append('search', this.state.search);
                }
                if (this.state.filter && this.state.filter !== '') {
                    params.append('status', this.state.filter);
                }
                if (this.state.currentPage > 1) {
                    params.append('page', this.state.currentPage);
                }

                // Reload page with new parameters to display updated data
                const queryString = params.toString();
                const newUrl = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;
                
                console.log('[Fines] Reloading with filters:', {
                    search: this.state.search,
                    status: this.state.filter,
                    page: this.state.currentPage
                });
                
                window.location.href = newUrl;
            },

            renderTable() {
                // AdminDataTable component handles rendering
                // This function is kept for reference but no longer used
                console.log('[Fines] Table data updated. AdminDataTable component will handle rendering.');
            },

            getStatusClass(status) {
                const statusLower = status.toLowerCase();
                if (statusLower === 'paid') return 'status-paid';
                if (statusLower === 'waived') return 'status-waived';
                return 'status-pending';
            },

            getStatusIcon(status) {
                const statusLower = status.toLowerCase();
                if (statusLower === 'paid') return '<i class="fas fa-check-circle"></i>';
                if (statusLower === 'waived') return '<i class="fas fa-ban"></i>';
                return '<i class="fas fa-hourglass-half"></i>';
            },

            updateStats() {
                const stats = this.state.stats;
                document.getElementById('totalFines').textContent = `₹${(stats.total || 0).toFixed(2)}`;
                document.getElementById('collectedFines').textContent = `₹${(stats.collected || 0).toFixed(2)}`;
                document.getElementById('pendingFines').textContent = `₹${(stats.pending || 0).toFixed(2)}`;
                document.getElementById('waivedFines').textContent = `₹${(stats.waived || 0).toFixed(2)}`;
            },

            renderPagination() {
                // AdminPagination component handles rendering
                // After loadFines completes, pagination will be auto-updated via Laravel paginator
                console.log('[Fines] Pagination updated. AdminPagination component will handle rendering.');
            },

            goToPage(page) {
                this.state.currentPage = page;
                this.loadFines();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            },

            markAsPaid(fineId) {
                if (!confirm('Mark this fine as paid?')) return;

                fetch(`/admin/fines/${fineId}/mark-as-paid`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.showSuccess('Fine marked as paid');
                            // Notify other pages/tabs that fine was updated
                            this.notifyFineUpdate(fineId, 'paid');
                            this.loadFines();
                        } else {
                            this.showError(data.message || 'Error updating fine');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showError('Error updating fine: ' + error.message);
                    });
            },

            openWaiveModal(fineId) {
                this.state.currentFineId = fineId;
                document.getElementById('waiveReason').value = '';
                document.getElementById('waiveModal').style.display = 'flex';
            },

            closeWaiveModal() {
                document.getElementById('waiveModal').style.display = 'none';
                this.state.currentFineId = null;
            },

            confirmWaiveFine() {
                const reason = document.getElementById('waiveReason').value.trim();
                if (!reason) {
                    this.showError('Please enter a reason for waiving the fine');
                    return;
                }

                const fineId = this.state.currentFineId;
                fetch(`/admin/fines/${fineId}/waive`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            remarks: reason
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.closeWaiveModal();
                            this.showSuccess('Fine waived successfully');
                            // Notify other pages/tabs that fine was updated
                            this.notifyFineUpdate(this.state.currentFineId, 'waived');
                            this.loadFines();
                        } else {
                            this.showError(data.message || 'Error waiving fine');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showError('Error waiving fine: ' + error.message);
                    });
            },

            sendEmailNotification(fineId) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                fetch(`/admin/fines/${fineId}/send-email`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.showSuccess(data.message ||
                                'Email notification sent successfully based on fine status');
                        } else {
                            this.showError(data.message || 'Failed to send email notification');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showError('Error sending email: ' + error.message);
                    });
            },

            exportToCSV() {
                if (this.state.fines.length === 0) {
                    this.showError('No fines to export');
                    return;
                }

                const headers = ['Student ID', 'Student Name', 'Book Title', 'Due Date', 'Days Overdue', 'Fine Amount',
                    'Status'
                ];
                const rows = this.state.fines.map(fine => [
                    fine.studentId,
                    fine.studentName,
                    fine.bookTitle,
                    fine.dueDate,
                    fine.daysOverdue,
                    `₹${parseFloat(fine.fineAmount).toFixed(2)}`,
                    fine.status
                ]);

                const csvContent = [
                    headers.join(','),
                    ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
                ].join('\n');

                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `fines_export_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.display = 'none';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                setTimeout(() => URL.revokeObjectURL(url), 100);

                this.showSuccess('CSV exported successfully');
            },

            showSuccess(message) {
                const modal = document.getElementById('successModal');
                document.getElementById('successTitle').textContent = 'Success!';
                document.getElementById('successMessage').textContent = message;
                modal.style.display = 'flex';
                setTimeout(() => this.closeSuccessModal(), 3000);
            },

            closeSuccessModal() {
                document.getElementById('successModal').style.display = 'none';
            },

            showError(message) {
                alert('Error: ' + message);
            }
        };

        // Action handlers for AdminDataTable component
        function markFinePaid(rowData) {
            if (confirm(`Mark fine for ${rowData.student_name} as paid?`)) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                fetch(`/admin/fines/${rowData.id}/mark-paid`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({ status: 'paid' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        finesManager.showSuccess('Fine marked as paid successfully');
                        finesManager.notifyFineUpdate(rowData.id, 'paid');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        finesManager.showError(data.message || 'Error marking fine as paid');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    finesManager.showError('Error: ' + error.message);
                });
            }
        }

        function openWaiveModal(rowData) {
            finesManager.state.currentFineId = rowData.id;
            finesManager.openWaiveModal();
        }

        function sendFineEmail(rowData) {
            if (confirm(`Send email notification to ${rowData.student_name}?`)) {
                finesManager.sendEmailNotification(rowData.id);
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            finesManager.init();
        });
    </script>
@endpush
