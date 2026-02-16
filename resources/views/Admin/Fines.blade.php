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
            max-width: 250px;
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

        .reset-btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            border: 1px solid;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        body.light-theme .reset-btn {
            background: #f8fafc;
            border-color: #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .reset-btn {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .reset-btn:hover {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        body.dark-theme .reset-btn:hover {
            border-color: #3b82f6;
            color: #60a5fa;
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

            .fines-table {
                display: block;
                overflow-x: auto;
            }

            .fines-table th,
            .fines-table td {
                padding: 0.5rem 0.75rem;
            }

            .page-header {
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
                    <div class="stat-number" id="totalFines">₹0</div>
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
                    <div class="stat-number" id="collectedFines">₹0</div>
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
                    <div class="stat-number" id="pendingFines">₹0</div>
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
                    <div class="stat-number" id="waivedFines">₹0</div>
                    <div class="stat-label">Waived amount</div>
                </div>
            </div>

            <!-- Search and Filters - After search instead of tabs -->
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
                        placeholder="Search by student name, book title, or reason...">
                </div>

                <div class="filters-container">
                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="waived">Waived</option>
                    </select>

                    <select class="filter-select" id="sortFilter">
                        <option value="date-desc">Date (Newest)</option>
                        <option value="date-asc">Date (Oldest)</option>
                        <option value="amount-desc">Amount (High to Low)</option>
                        <option value="amount-asc">Amount (Low to High)</option>
                    </select>

                    <button id="resetFiltersBtn" class="reset-btn" title="Reset all filters">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline; margin-right: 4px; vertical-align: -2px;">
                            <polyline points="1 4 1 10 7 10"></polyline>
                            <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>

            <!-- Fines Table Container -->
            <div class="fines-table-container">
                <table class="fines-table">
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
                        <!-- Data will be loaded here by JavaScript -->
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="flex justify-center py-8">
                                    <div class="loading-spinner"></div>
                                </div>
                            </td>
                        </tr>
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
                    <h3 style="margin-bottom: 0.25rem; font-size: 1rem; font-weight: 600;">No fines found</h3>
                    <p class="text-secondary">Try adjusting your search or filters</p>
                </div>
            </div>

            <!-- Table Footer -->
            <div id="paginationContainer" class="pagination-container">
                <div class="pagination-info">
                    Showing <span id="recordCount">0</span> records out of <span id="totalCount">0</span>
                </div>
                <div id="paginationButtons" class="pagination-buttons">
                    <!-- Pagination will be generated here -->
                </div>
            </div>
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
                sort: 'date-desc',
                fines: [],
                stats: {},
                pagination: {}
            },

            // BroadcastChannel for cross-tab communication
            fineUpdateChannel: null,

            init() {
                console.log('[Fines] Initializing fines manager...');
                this.setupEventListeners();
                this.setupBroadcastChannel();
                console.log('[Fines] Calling loadFines from init...');
                this.loadFines();
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
                // Search input with debounce
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    let debounceTimer;
                    searchInput.addEventListener('input', (e) => {
                        clearTimeout(debounceTimer);
                        this.state.search = e.target.value;
                        this.state.currentPage = 1;
                        debounceTimer = setTimeout(() => this.loadFines(), 300);
                    });
                }

                // Status filter
                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter) {
                    statusFilter.addEventListener('change', (e) => {
                        this.state.filter = e.target.value;
                        this.state.currentPage = 1;
                        this.loadFines();
                    });
                }

                // Sort filter
                const sortFilter = document.getElementById('sortFilter');
                if (sortFilter) {
                    sortFilter.addEventListener('change', (e) => {
                        this.state.sort = e.target.value;
                        this.state.currentPage = 1;
                        this.loadFines();
                    });
                }

                // Reset filters button
                const resetBtn = document.getElementById('resetFiltersBtn');
                if (resetBtn) {
                    resetBtn.addEventListener('click', () => this.resetFilters());
                }

                // Keyboard shortcuts
                this.setupKeyboardShortcuts();
            },

            resetFilters() {
                document.getElementById('searchInput').value = '';
                document.getElementById('statusFilter').value = 'all';
                document.getElementById('sortFilter').value = 'date-desc';
                this.state.search = '';
                this.state.filter = 'all';
                this.state.sort = 'date-desc';
                this.state.currentPage = 1;
                this.loadFines();
            },

            setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // Ctrl+K or Cmd+K to focus search
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        document.getElementById('searchInput').focus();
                    }
                    // Escape in search input to clear it
                    else if (e.key === 'Escape' && document.activeElement.id === 'searchInput') {
                        if (document.getElementById('searchInput').value !== '') {
                            document.getElementById('searchInput').value = '';
                            this.state.search = '';
                            this.state.currentPage = 1;
                            this.loadFines();
                        } else {
                            document.getElementById('searchInput').blur();
                        }
                    }
                });
            },

            setFilter(status) {
                // This is kept for backward compatibility if needed
                this.state.filter = status;
                this.state.currentPage = 1;
                this.loadFines();
            },

            loadFines() {
                const params = new URLSearchParams({
                    search: this.state.search,
                    status: this.state.filter,
                    sort: this.state.sort || 'date-desc',
                    page: this.state.currentPage,
                    per_page: this.state.perPage
                });

                const tbody = document.getElementById('finesTableBody');
                const emptyState = document.getElementById('emptyState');

                if (tbody) {
                    tbody.innerHTML =
                        '<tr><td colspan="8" class="text-center"><div class="flex justify-center py-8"><div class="loading-spinner"></div></div></td></tr>';
                    if (emptyState) emptyState.style.display = 'none';
                }

                const apiUrl = `{{ route('admin.fines.data') }}?${params}`;
                console.log('[Fines] Loading fines from URL:', apiUrl);

                fetch(apiUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => {
                        console.log('[Fines] Response status:', response.status);
                        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                        return response.json();
                    })
                    .then(data => {
                        console.log('[Fines] Data received:', data);
                        if (data.success) {
                            this.state.fines = data.fines || [];
                            this.state.stats = data.stats || {};
                            this.state.pagination = data.pagination || {};
                            this.renderTable();
                            this.updateStats();
                            this.renderPagination();
                        } else {
                            this.showError(data.message || 'Error loading fines');
                        }
                    })
                    .catch(error => {
                        console.error('[Fines] Error loading fines:', error);
                        this.showError('Error loading fines: ' + error.message);
                    });
            },

            renderTable() {
                const tbody = document.getElementById('finesTableBody');
                const emptyState = document.getElementById('emptyState');

                if (!tbody) return;

                if (this.state.fines.length === 0) {
                    tbody.innerHTML = '';
                    if (emptyState) emptyState.style.display = 'block';
                    return;
                }

                if (emptyState) emptyState.style.display = 'none';

                tbody.innerHTML = this.state.fines.map(fine => `
            <tr data-fine-id="${fine.id}">
                <td>${fine.studentId}</td>
                <td>${fine.studentName}</td>
                <td>${fine.bookTitle}</td>
                <td class="text-secondary">${fine.dueDate}</td>
                <td class="text-secondary">${fine.daysOverdue}</td>
                <td>
                    <span class="fine-amount ${fine.status.toLowerCase()}">
                        ₹${parseFloat(fine.fineAmount).toFixed(2)}
                    </span>
                </td>
                <td>
                    <span class="status-badge ${this.getStatusClass(fine.status)}">
                        ${this.getStatusIcon(fine.status)}
                        ${fine.status}
                    </span>
                </td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="action-buttons">
                            ${fine.status.toLowerCase() === 'pending' ? `
                                            <button class="action-btn btn-paid" onclick="finesManager.markAsPaid(${fine.id})">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                                </svg>
                                                Paid
                                            </button>
                                            <button class="action-btn btn-waive" onclick="finesManager.openWaiveModal(${fine.id})">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                                </svg>
                                                Waive
                                            </button>
                                        ` : ''}
                        </div>
                        <button class="btn-email" onclick="finesManager.sendEmailNotification(${fine.id})" title="Send Email">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
            },

            getStatusClass(status) {
                const statusLower = status.toLowerCase();
                if (statusLower === 'paid') return 'status-paid';
                if (statusLower === 'waived') return 'status-waived';
                return 'status-pending';
            },

            getStatusIcon(status) {
                const statusLower = status.toLowerCase();
                if (statusLower === 'paid') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>`;
                }
                if (statusLower === 'waived') {
                    return `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>`;
                }
                return `<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>`;
            },

            updateStats() {
                const stats = this.state.stats;
                document.getElementById('totalFines').textContent = `₹${(stats.total || 0).toFixed(2)}`;
                document.getElementById('collectedFines').textContent = `₹${(stats.collected || 0).toFixed(2)}`;
                document.getElementById('pendingFines').textContent = `₹${(stats.pending || 0).toFixed(2)}`;
                document.getElementById('waivedFines').textContent = `₹${(stats.waived || 0).toFixed(2)}`;
            },

            renderPagination() {
                const pagination = this.state.pagination;
                const paginationButtons = document.getElementById('paginationButtons');
                if (!paginationButtons) return;

                paginationButtons.innerHTML = '';

                // Previous button
                const prevBtn = document.createElement('button');
                prevBtn.className = 'pagination-btn';
                prevBtn.disabled = pagination.current_page === 1;
                prevBtn.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>';
                prevBtn.onclick = () => this.goToPage(pagination.current_page - 1);
                paginationButtons.appendChild(prevBtn);

                // Page numbers
                const startPage = Math.max(1, pagination.current_page - 2);
                const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

                if (startPage > 1) {
                    const btn = document.createElement('button');
                    btn.className = 'pagination-btn';
                    btn.textContent = '1';
                    btn.onclick = () => this.goToPage(1);
                    paginationButtons.appendChild(btn);

                    if (startPage > 2) {
                        const dots = document.createElement('span');
                        dots.className = 'pagination-ellipsis';
                        dots.textContent = '...';
                        paginationButtons.appendChild(dots);
                    }
                }

                for (let i = startPage; i <= endPage; i++) {
                    const btn = document.createElement('button');
                    btn.className = i === pagination.current_page ? 'pagination-btn active' : 'pagination-btn';
                    btn.textContent = i;
                    btn.onclick = () => this.goToPage(i);
                    paginationButtons.appendChild(btn);
                }

                if (endPage < pagination.last_page) {
                    if (endPage < pagination.last_page - 1) {
                        const dots = document.createElement('span');
                        dots.className = 'pagination-ellipsis';
                        dots.textContent = '...';
                        paginationButtons.appendChild(dots);
                    }

                    const btn = document.createElement('button');
                    btn.className = 'pagination-btn';
                    btn.textContent = pagination.last_page;
                    btn.onclick = () => this.goToPage(pagination.last_page);
                    paginationButtons.appendChild(btn);
                }

                // Next button
                const nextBtn = document.createElement('button');
                nextBtn.className = 'pagination-btn';
                nextBtn.disabled = pagination.current_page === pagination.last_page;
                nextBtn.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>';
                nextBtn.onclick = () => this.goToPage(pagination.current_page + 1);
                paginationButtons.appendChild(nextBtn);

                // Update record count
                const startRecord = (pagination.current_page - 1) * this.state.perPage + 1;
                const endRecord = Math.min(pagination.current_page * this.state.perPage, pagination.total);

                document.getElementById('recordCount').textContent = `${startRecord}-${endRecord}`;
                document.getElementById('totalCount').textContent = pagination.total || 0;
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

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            finesManager.init();
        });
    </script>
@endpush
