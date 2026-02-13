@extends('Admin.layouts.app')

@section('title', 'Activity Logs')

@push('styles')
    <style>
        /* Activity Logs Specific Styles */
        .page-header {
            margin-bottom: 1.2rem;
        }

        .page-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .page-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        body.dark-theme .page-header p {
            color: #94a3b8;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
            margin-bottom: 1.8rem;
        }

        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .stat-card {
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            position: relative;
            overflow: hidden;
        }

        body.light-theme .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-color: #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        body.dark-theme .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-color: #334155;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        body.light-theme .stat-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        body.dark-theme .stat-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        body.light-theme .stat-value {
            color: #0f172a;
        }

        body.dark-theme .stat-value {
            color: #f8fafc;
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        body.light-theme .stat-label {
            color: #64748b;
        }

        body.dark-theme .stat-label {
            color: #94a3b8;
        }

        .stat-description {
            font-size: 0.75rem;
        }

        body.light-theme .stat-description {
            color: #94a3b8;
        }

        body.dark-theme .stat-description {
            color: #64748b;
        }

        /* Filters Section */
        .filters-section {
            margin-bottom: 2rem;
        }

        .filters-header {
            margin-bottom: 1.5rem;
        }

        .filters-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        body.light-theme .filters-header h2 {
            color: #0f172a;
        }

        body.dark-theme .filters-header h2 {
            color: #f1f5f9;
        }

        .filters-header p {
            font-size: 0.875rem;
        }

        body.light-theme .filters-header p {
            color: #64748b;
        }

        body.dark-theme .filters-header p {
            color: #94a3b8;
        }

        .filters-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding: 1.5rem;
            border-radius: 0.75rem;
        }

        body.light-theme .filters-container {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .filters-container {
            background-color: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        @media (min-width: 768px) {
            .filters-container {
                grid-template-columns: 1fr auto auto;
                align-items: end;
            }
        }

        /* Search Input */
        .search-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: 1px solid;
            outline: none;
        }

        body.light-theme .search-input {
            background-color: #ffffff;
            border-color: #d1d5db;
            color: #111827;
        }

        body.dark-theme .search-input {
            background-color: #0f172a;
            border-color: #334155;
            color: #e5e7eb;
        }

        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme .search-input:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1rem;
            height: 1rem;
        }

        body.light-theme .search-icon {
            color: #6b7280;
        }

        body.dark-theme .search-icon {
            color: #9ca3af;
        }

        /* Form Groups */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        body.light-theme .form-label {
            color: #374151;
        }

        body.dark-theme .form-label {
            color: #d1d5db;
        }

        .form-select {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            border: 1px solid;
            outline: none;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }

        body.light-theme .form-select {
            background-color: #ffffff;
            border-color: #d1d5db;
            color: #111827;
        }

        body.dark-theme .form-select {
            background-color: #0f172a;
            border-color: #334155;
            color: #e5e7eb;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        }

        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme .form-select:focus {
            border-color: #60a5fa;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.1);
        }

        /* Entries Section */
        .entries-section {
            margin-top: 2.5rem;
        }

        .entries-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .entries-title-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .entries-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        body.light-theme .entries-title {
            color: #0f172a;
        }

        body.dark-theme .entries-title {
            color: #f1f5f9;
        }

        .entries-icon {
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.light-theme .entries-icon {
            color: #3b82f6;
        }

        body.dark-theme .entries-icon {
            color: #60a5fa;
        }

        .entries-subtext {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        body.light-theme .entries-subtext {
            color: #64748b;
        }

        body.dark-theme .entries-subtext {
            color: #94a3b8;
        }

        /* Table Container - Simplified */
        .table-container {
            border-radius: 0.75rem;
            overflow: hidden;
            overflow-x: auto;
        }

        /* Activity Table - Enhanced with Striped Rows and Reduced Padding */
        .activity-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
        }

        .activity-table thead tr {
            background-color: #f8fafc;
        }

        body.dark-theme .activity-table thead tr {
            background-color: #1e293b;
        }

        .activity-table th {
            text-align: left;
            padding: 0.75rem 1rem; /* Reduced padding */
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            white-space: nowrap;
        }

        body.dark-theme .activity-table th {
            border-bottom-color: #475569;
            color: #94a3b8;
        }

        .activity-table tbody tr {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid #f1f5f9;
        }

        body.dark-theme .activity-table tbody tr {
            border-bottom-color: #334155;
        }

        /* Striped rows */
        .activity-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        body.dark-theme .activity-table tbody tr:nth-child(even) {
            background-color: rgba(30, 41, 59, 0.4);
        }

        /* Hover effects */
        .activity-table tbody tr:hover {
            background-color: #e2e8f0 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .activity-table tbody tr:hover {
            background-color: #334155 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        .activity-table td {
            padding: 0.75rem 1rem; /* Reduced padding */
            font-size: 0.875rem;
            color: #475569;
            vertical-align: top;
        }

        body.dark-theme .activity-table td {
            color: #cbd5e1;
        }

        .activity-table td:first-child {
            border-top-left-radius: 0.5rem;
            border-bottom-left-radius: 0.5rem;
        }

        .activity-table td:last-child {
            border-top-right-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }

        /* Table Columns */
        .timestamp-cell {
            white-space: nowrap;
        }

        .timestamp-date {
            font-weight: 500;
            font-size: 0.875rem;
        }

        .timestamp-time {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            color: #64748b;
        }

        body.dark-theme .timestamp-time {
            color: #94a3b8;
        }

        .user-cell {
            min-width: 180px;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.8rem;
            display: block;
            color: #1e293b;
        }

        body.dark-theme .user-name {
            color: #f1f5f9;
        }

        .user-identifier {
            font-size: 0.7rem;
            margin-top: 0.2rem;
            display: block;
            color: #64748b;
        }

        body.dark-theme .user-identifier {
            color: #94a3b8;
        }

        /* Role Badge - Enhanced */
        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            min-width: 70px;
            transition: all 0.2s ease;
        }

        .role-badge:hover {
            transform: scale(1.05);
        }

        .role-badge-admin {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        body.dark-theme .role-badge-admin {
            background-color: #7f1d1d;
            color: #fca5a5;
            border-color: #991b1b;
        }

        .role-badge-staff {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        body.dark-theme .role-badge-staff {
            background-color: #1e3a8a;
            color: #93c5fd;
            border-color: #1e40af;
        }

        .role-badge-student {
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .role-badge-student {
            background-color: #334155;
            color: #cbd5e1;
            border-color: #475569;
        }

        /* Action Badge - Enhanced */
        .action-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            color: #374151;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        body.dark-theme .action-badge {
            background-color: #374151;
            border-color: #4b5563;
            color: #d1d5db;
        }

        .action-badge:hover {
            transform: scale(1.05);
        }

        /* Details cell styling */
        .activity-table td:nth-child(5) {
            max-width: 500px;
            min-width: 350px;
        }
        
        .activity-table td:nth-child(5) > div {
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }

        .details-info {
            font-size: 0.75rem;
            margin-top: 0.25rem;
            color: #64748b;
        }

        body.dark-theme .details-info {
            color: #94a3b8;
        }

        /* Empty State */
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

        /* Summary Section */
        .summary-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid;
        }

        body.light-theme .summary-section {
            border-top-color: #e5e7eb;
        }

        body.dark-theme .summary-section {
            border-top-color: #334155;
        }

        .summary-header {
            margin-bottom: 1.5rem;
        }

        .summary-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #0f172a;
        }

        body.dark-theme .summary-header h2 {
            color: #f1f5f9;
        }

        .summary-subtitle {
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme .summary-subtitle {
            color: #94a3b8;
        }

        /* Summary Grid */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Summary Item - Enhanced */
        .summary-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .summary-item {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        .summary-item:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .summary-item:hover {
            background-color: #334155;
            border-color: #475569;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        /* Action Name */
        .action-name {
            font-size: 0.875rem;
            font-weight: 500;
            flex: 1;
            color: #374151;
        }

        body.dark-theme .action-name {
            color: #e5e7eb;
        }

        .action-name.system-action {
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8125rem;
        }

        /* Count Badge - Enhanced */
        .count-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 2.5rem;
            text-align: center;
            margin-left: 1rem;
            flex-shrink: 0;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .count-badge:hover {
            transform: scale(1.05);
        }

        .count-high {
            background-color: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        body.dark-theme .count-high {
            background-color: #7f1d1d;
            color: #fca5a5;
            border-color: #991b1b;
        }

        .count-medium {
            background-color: #fef3c7;
            color: #d97706;
            border-color: #fde68a;
        }

        body.dark-theme .count-medium {
            background-color: #78350f;
            color: #fbbf24;
            border-color: #92400e;
        }

        .count-low {
            background-color: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        body.dark-theme .count-low {
            background-color: #1e3a8a;
            color: #93c5fd;
            border-color: #1e40af;
        }

        /* Pagination - Keep original */
        .pagination-container {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .activity-table {
                min-width: 100%;
            }
            
            .table-container {
                margin: 0;
                border-radius: 0;
                border: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h1>Activity Logs</h1>
        <p>Complete audit trail of all system activities</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $allActivities ?? 0 }}</div>
            <div class="stat-label">Total Activities</div>
            <div class="stat-description">All time records</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ $adminActions ?? 0 }}</div>
            <div class="stat-label">Admin Actions</div>
            <div class="stat-description">By administrators</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ $staffActions ?? 0 }}</div>
            <div class="stat-label">Staff Actions</div>
            <div class="stat-description">By staff members</div>
        </div>

        <div class="stat-card">
            <div class="stat-value">{{ $studentActions ?? 0 }}</div>
            <div class="stat-label">Student Actions</div>
            <div class="stat-description">By students</div>
        </div>
    </div>

    <!-- Filters & Search Section -->
    <div class="filters-section">
        <form id="activityFilters" method="GET" action="{{ request()->url() }}" class="filters-container">
            <div class="form-group">
                <div class="search-wrapper">
                    <svg class="search-icon" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z" />
                    </svg>
                    <input type="text" id="search" name="search" placeholder="Search by user, action, or details..."
                        class="search-input" value="{{ request('search') }}">
                </div>
            </div>

            <div class="form-group">
                <select id="role" name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>

            <div class="form-group">
                <select id="period" name="period" class="form-select">
                    <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>All Time</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="7days" {{ request('period') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30days" {{ request('period') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                </select>
            </div>

            <div class="form-group">
                <select id="action_category" name="action_category" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($actionCategories as $category)
                        <option value="{{ $category }}" {{ request('action_category') == $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Activity Log Entries Section -->
    <div class="entries-section">
        <div class="entries-header">
            <div class="entries-title-container">
                <div class="entries-icon">
                    <svg viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z" />
                    </svg>
                </div>
                <h2 class="entries-title">Activity Log Entries</h2>
            </div>
            <div class="entries-subtext">Showing {{ $activities->count() > 0 ? $activities->firstItem() : 0 }} to {{ $activities->count() > 0 ? $activities->lastItem() : 0 }} of {{ $totalActivities ?? 0 }} entries</div>
        </div>

        <div class="table-container">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td class="timestamp-cell">
                                <div class="timestamp-date">{{ $activity->created_at->format('n/d/Y') }}</div>
                                <div class="timestamp-time">{{ $activity->created_at->format('g:i:s A') }}</div>
                            </td>
                            <td class="user-cell">
                                <span class="user-name">{{ $activity->user_name ?? 'Unknown' }}</span>
                                <span class="user-identifier">{{ $activity->user_email ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @php
                                    $roleClass = 'role-badge-student';
                                    if ($activity->user_role === 'admin') {
                                        $roleClass = 'role-badge-admin';
                                    } elseif ($activity->user_role === 'staff') {
                                        $roleClass = 'role-badge-staff';
                                    }
                                @endphp
                                <span class="role-badge {{ $roleClass }}">{{ ucfirst($activity->user_role ?? 'User') }}</span>
                            </td>
                            <td>
                                <span class="action-badge">{{ strtoupper(str_replace('_', ' ', $activity->action)) }}</span>
                            </td>
                            <td>
                                <div title="{{ $activity->description }}">
                                    {{ Str::limit($activity->description, 100) }}
                                    @if($activity->browser || $activity->device_type)
                                        <div class="details-info">
                                            {{ $activity->browser }} • {{ ucfirst($activity->device_type) }} • {{ $activity->ip_address }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                <div class="empty-state">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <path d="M12 6v6m0 4v.01"></path>
                                    </svg>
                                    <p style="margin-top: 1rem; font-weight: 500; color: #374151;">No activities found</p>
                                    <p style="font-size: 0.875rem; margin-top: 0.5rem; color: #64748b;">Try adjusting your filters</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
            <div class="pagination-container">
                {{ $activities->links() }}
            </div>
        @endif
    </div>

    <!-- Activity Summary Section -->
    <div class="summary-section">
        <div class="summary-header">
            <h2>Activity Summary by Action Type</h2>
            <p class="summary-subtitle">Count of all activities by action type (across all users)</p>
        </div>

        @if($actionStats->count() > 0)
        <div class="summary-grid">
            @foreach($actionStats as $stat)
                @php
                    $count = $stat->count;
                    $badge = 'count-low';
                    if ($count >= 10) {
                        $badge = 'count-high';
                    } elseif ($count >= 5) {
                        $badge = 'count-medium';
                    }
                @endphp
                <div class="summary-item">
                    <div class="action-name system-action">{{ strtoupper(str_replace('_', ' ', $stat->action)) }}</div>
                    <div class="count-badge {{ $badge }}">{{ $count }}</div>
                </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <p style="font-weight: 500; color: #374151;">No activity data available yet</p>
            <p style="font-size: 0.875rem; margin-top: 0.5rem; color: #64748b;">Activities will appear here as users interact with the system</p>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const filtersForm = document.getElementById('activityFilters');

            // Auto-submit form on filter changes
            document.getElementById('role').addEventListener('change', () => filtersForm.submit());
            document.getElementById('period').addEventListener('change', () => filtersForm.submit());
            document.getElementById('action_category').addEventListener('change', () => filtersForm.submit());

            // Debounced search
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => filtersForm.submit(), 500);
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

                if (e.key === '/' && !searchInput.matches(':focus')) {
                    e.preventDefault();
                    searchInput.focus();
                }

                if (e.key === 'Escape' && searchInput.matches(':focus')) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                }
            });
        });
    </script>
@endpush