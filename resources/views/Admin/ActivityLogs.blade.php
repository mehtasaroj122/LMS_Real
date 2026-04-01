@extends('Admin.layouts.app')

@section('title', 'Activity Logs')

@push('styles')
    <style>
        .activity-page {
            --audit-blue: #2563eb;
            --audit-emerald: #059669;
            --audit-amber: #d97706;
            --audit-rose: #e11d48;
            --audit-indigo: #4f46e5;
            --audit-slate: #64748b;
        }

        .activity-async-root {
            position: relative;
            transition: opacity 0.2s ease;
        }

        .activity-async-root>* {
            transition: opacity 0.2s ease;
        }

        .activity-async-root.is-loading {
            pointer-events: none;
        }

        .activity-async-root.is-loading>* {
            opacity: 0.45;
        }

        .activity-async-root.is-loading::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.35);
            border-radius: 1rem;
            pointer-events: none;
        }

        body.dark-theme .activity-async-root.is-loading::after {
            background: rgba(15, 23, 42, 0.35);
        }

        .activity-async-root.is-loading::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 10rem;
            width: 2.1rem;
            height: 2.1rem;
            margin-left: -1.05rem;
            border-radius: 999px;
            border: 3px solid rgba(37, 99, 235, 0.2);
            border-top-color: #2563eb;
            animation: activity-log-spin 0.8s linear infinite;
            z-index: 2;
            pointer-events: none;
        }

        @keyframes activity-log-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .activity-page-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .activity-page-kicker {
            margin: 0 0 0.25rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .activity-page-title {
            margin: 0;
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.15;
        }

        .activity-page-note {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.82rem;
            font-weight: 600;
        }

        body.dark-theme .activity-page-note {
            border-color: rgba(96, 165, 250, 0.22);
            background: rgba(37, 99, 235, 0.14);
            color: #bfdbfe;
        }

        .activity-page-note i {
            width: 1rem;
            height: 1rem;
        }

        .activity-stat-card {
            position: relative;
            overflow: hidden;
            border: 1px solid transparent;
        }

        .activity-stat-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--card-accent, var(--audit-blue));
        }

        .activity-stat-card::after {
            content: '';
            position: absolute;
            right: -2.5rem;
            top: -2.75rem;
            width: 6.5rem;
            height: 6.5rem;
            border-radius: 999px;
            background: var(--card-soft, rgba(37, 99, 235, 0.1));
            pointer-events: none;
        }

        .activity-stat-icon {
            width: 2rem;
            height: 2rem;
            padding: 0.45rem;
            border-radius: 0.75rem;
            background: var(--card-soft, rgba(37, 99, 235, 0.1));
            color: var(--card-accent, var(--audit-blue));
        }

        body.dark-theme .activity-stat-card {
            border-color: rgba(148, 163, 184, 0.12);
        }

        .activity-panel {
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
        }

        .activity-filter-panel {
            padding: 0.9rem !important;
        }

        body.dark-theme .activity-panel {
            border-color: #334155;
        }

        .activity-panel-header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .activity-panel-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .activity-panel-copy {
            margin: 0.25rem 0 0;
            font-size: 0.82rem;
            color: #64748b;
        }

        body.dark-theme .activity-panel-copy {
            color: #94a3b8;
        }

        .activity-results-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.55rem 0.85rem;
            border-radius: 999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
        }

        body.dark-theme .activity-results-chip {
            background: rgba(148, 163, 184, 0.08);
            border-color: #334155;
            color: #cbd5e1;
        }

        .activity-filter-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) repeat(3, minmax(0, 1fr)) auto;
            gap: 0.75rem;
            align-items: end;
        }

        .activity-filter-panel .activity-filter-grid {
            gap: 0.55rem;
        }

        .activity-filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .activity-filter-panel .activity-filter-group {
            gap: 0.25rem;
        }

        .activity-filter-label {
            font-size: 0.74rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        body.dark-theme .activity-filter-label {
            color: #94a3b8;
        }

        .activity-filter-panel .activity-filter-label {
            font-size: 0.68rem;
        }

        .activity-search-shell {
            position: relative;
        }

        .activity-search-shell i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            width: 1rem;
            height: 1rem;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .search-input,
        .filter-select {
            width: 100%;
            min-height: 2.95rem;
            border-radius: 0.85rem;
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            color: #0f172a;
            padding: 0.78rem 0.95rem;
            font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            outline: none;
        }

        .activity-filter-panel .search-input,
        .activity-filter-panel .filter-select {
            min-height: 2.55rem;
            padding-top: 0.62rem;
            padding-bottom: 0.62rem;
            font-size: 0.85rem;
            border-radius: 0.75rem;
        }

        .search-input {
            padding-left: 2.65rem;
        }

        .activity-filter-panel .search-input {
            padding-left: 2.45rem;
        }

        .filter-select {
            appearance: none;
            background-image:
                linear-gradient(45deg, transparent 50%, #64748b 50%),
                linear-gradient(135deg, #64748b 50%, transparent 50%);
            background-position:
                calc(100% - 18px) calc(1.15rem),
                calc(100% - 12px) calc(1.15rem);
            background-size: 6px 6px, 6px 6px;
            background-repeat: no-repeat;
            padding-right: 2.6rem;
        }

        body.dark-theme .search-input,
        body.dark-theme .filter-select {
            border-color: #334155;
            background: #0f172a;
            color: #f8fafc;
        }

        body.dark-theme .filter-select {
            background-image:
                linear-gradient(45deg, transparent 50%, #94a3b8 50%),
                linear-gradient(135deg, #94a3b8 50%, transparent 50%);
        }

        .search-input:focus,
        .filter-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            background: #fff;
        }

        body.dark-theme .search-input:focus,
        body.dark-theme .filter-select:focus {
            background: #111827;
        }

        .activity-reset-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 2.95rem;
            padding: 0.8rem 1rem;
            border-radius: 0.85rem;
            border: 1px solid #dbe2ea;
            background: transparent;
            color: #334155;
            font-size: 0.87rem;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .activity-filter-panel .activity-reset-btn {
            min-height: 2.55rem;
            padding: 0.68rem 0.95rem;
            border-radius: 0.75rem;
            font-size: 0.82rem;
        }

        .activity-reset-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: rgba(37, 99, 235, 0.06);
        }

        body.dark-theme .activity-reset-btn {
            border-color: #334155;
            color: #e2e8f0;
        }

        body.dark-theme .activity-reset-btn:hover {
            border-color: #60a5fa;
            color: #bfdbfe;
            background: rgba(96, 165, 250, 0.08);
        }

        .activity-table-wrapper {
            overflow-x: auto;
            border-radius: 0.95rem;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .activity-table-wrapper {
            border-color: #334155;
        }

        .activity-table {
            width: 100%;
            min-width: 900px;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: auto;
        }

        .activity-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 0.78rem 0.72rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            text-align: left;
        }

        body.dark-theme .activity-table thead th {
            background: #0f172a;
            border-bottom-color: #334155;
            color: #94a3b8;
        }

        .activity-table tbody tr {
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .activity-table tbody tr:nth-child(even) {
            background: rgba(248, 250, 252, 0.78);
        }

        body.dark-theme .activity-table tbody tr:nth-child(even) {
            background: rgba(15, 23, 42, 0.58);
        }

        .activity-table tbody tr:hover {
            background: rgba(37, 99, 235, 0.05);
        }

        body.dark-theme .activity-table tbody tr:hover {
            background: rgba(37, 99, 235, 0.1);
        }

        .activity-table td {
            padding: 0.78rem 0.72rem;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
            font-size: 0.88rem;
        }

        body.dark-theme .activity-table td {
            border-bottom-color: #1e293b;
        }

        .activity-time-col {
            width: 9.5rem;
            min-width: 9.5rem;
        }

        .activity-table td:nth-child(2) {
            width: 18rem;
            min-width: 18rem;
        }

        .activity-table td:nth-child(3) {
            width: 12.5rem;
            min-width: 12.5rem;
        }

        .time-stack,
        .event-stack {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .time-date,
        .activity-actor-name,
        .event-title {
            color: #0f172a;
            font-weight: 700;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        body.dark-theme .time-date,
        body.dark-theme .activity-actor-name,
        body.dark-theme .event-title {
            color: #f8fafc;
        }

        .time-hour,
        .time-day,
        .activity-actor-email,
        .event-caption {
            color: #64748b;
            font-size: 0.75rem;
            line-height: 1.4;
        }

        body.dark-theme .time-hour,
        body.dark-theme .time-day,
        body.dark-theme .activity-actor-email,
        body.dark-theme .event-caption {
            color: #94a3b8;
        }

        .activity-actor {
            display: flex;
            gap: 0.7rem;
            align-items: flex-start;
        }

        .activity-avatar {
            width: 2.35rem;
            height: 2.35rem;
            border-radius: 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1d4ed8;
            font-size: 0.78rem;
            font-weight: 800;
        }

        body.dark-theme .activity-avatar {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.22) 0%, rgba(14, 165, 233, 0.22) 100%);
            color: #dbeafe;
        }

        .activity-actor-copy {
            min-width: 0;
        }

        .activity-actor-name-row {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            flex-wrap: wrap;
            margin-bottom: 0.12rem;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.22rem 0.5rem;
            border-radius: 999px;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .role-badge-admin {
            background: #fee2e2;
            color: #b91c1c;
        }

        .role-badge-staff {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .role-badge-student {
            background: #dcfce7;
            color: #15803d;
        }

        .role-badge-system {
            background: #e2e8f0;
            color: #475569;
        }

        body.dark-theme .role-badge-admin {
            background: rgba(248, 113, 113, 0.18);
            color: #fca5a5;
        }

        body.dark-theme .role-badge-staff {
            background: rgba(96, 165, 250, 0.18);
            color: #bfdbfe;
        }

        body.dark-theme .role-badge-student {
            background: rgba(74, 222, 128, 0.18);
            color: #bbf7d0;
        }

        body.dark-theme .role-badge-system {
            background: rgba(148, 163, 184, 0.18);
            color: #cbd5e1;
        }

        .event-badges,
        .context-meta {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .event-badge,
        .context-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.24rem 0.5rem;
            border-radius: 999px;
            font-size: 0.66rem;
            font-weight: 700;
            line-height: 1.25;
        }

        .event-badge.primary {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .event-badge.secondary {
            background: #eef2ff;
            color: #4338ca;
        }

        body.dark-theme .event-badge.primary {
            background: rgba(96, 165, 250, 0.16);
            color: #bfdbfe;
        }

        body.dark-theme .event-badge.secondary {
            background: rgba(99, 102, 241, 0.18);
            color: #c7d2fe;
        }

        .context-col {
            width: auto;
            max-width: none;
        }

        .context-description {
            color: #334155;
            line-height: 1.5;
            font-size: 0.86rem;
            margin-bottom: 0.5rem;
        }

        body.dark-theme .context-description {
            color: #dbe4f0;
        }

        .context-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        body.dark-theme .context-pill {
            background: rgba(148, 163, 184, 0.08);
            border-color: #334155;
            color: #cbd5e1;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            padding: 3.25rem 1.5rem;
            text-align: center;
        }

        .empty-state-icon {
            width: 3rem;
            height: 3rem;
            color: #94a3b8;
        }

        .empty-state-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        body.dark-theme .empty-state-title {
            color: #f8fafc;
        }

        .empty-state-copy {
            margin: 0;
            max-width: 28rem;
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        body.dark-theme .empty-state-copy {
            color: #94a3b8;
        }

        .pagination-container {
            margin-top: 1.25rem;
            display: flex;
            justify-content: center;
        }

        .activity-summary-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            gap: 0.85rem;
            align-items: center;
            padding: 0.85rem 0.95rem;
            border-radius: 0.85rem;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        body.dark-theme .summary-item {
            border-color: #334155;
            background: #0f172a;
        }

        .summary-item-name {
            margin: 0;
            color: #0f172a;
            font-weight: 700;
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .summary-item-copy {
            margin: 0.14rem 0 0;
            color: #64748b;
            font-size: 0.74rem;
        }

        body.dark-theme .summary-item-name {
            color: #f8fafc;
        }

        body.dark-theme .summary-item-copy {
            color: #94a3b8;
        }

        .summary-item-count {
            min-width: 2.4rem;
            padding: 0.32rem 0.65rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.76rem;
            font-weight: 800;
            text-align: center;
        }

        body.dark-theme .summary-item-count {
            background: rgba(96, 165, 250, 0.16);
            color: #bfdbfe;
        }

        @media (min-width: 768px) {
            .activity-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1100px) {
            .activity-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 1120px) {
            .activity-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 720px) {
            .activity-filter-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .activity-filter-panel {
                padding: 0.8rem !important;
            }

            .activity-table,
            .activity-table thead,
            .activity-table tbody,
            .activity-table th,
            .activity-table td,
            .activity-table tr {
                display: block;
                min-width: 0;
            }

            .activity-table thead {
                display: none;
            }

            .activity-table tbody tr {
                padding: 0.35rem 0;
                border-bottom: 1px solid #e5e7eb;
            }

            body.dark-theme .activity-table tbody tr {
                border-bottom-color: #334155;
            }

            .activity-table td {
                border-bottom: none;
                padding: 0.6rem 1rem 0.6rem;
            }

            .activity-table td::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 0.4rem;
                color: #64748b;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            body.dark-theme .activity-table td::before {
                color: #94a3b8;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $cards = [
            [
                'label' => 'All Activity Records',
                'value' => $allActivities ?? 0,
                'note' => 'Lifetime audit entries',
                'icon' => 'history',
                'accent' => '#2563eb',
                'soft' => 'rgba(37, 99, 235, 0.12)',
            ],
            [
                'label' => 'Current Results',
                'value' => $totalActivities ?? 0,
                'note' => 'Entries matching current filters',
                'icon' => 'filter',
                'accent' => '#4f46e5',
                'soft' => 'rgba(79, 70, 229, 0.12)',
            ],
            [
                'label' => 'Admin Actions',
                'value' => $adminActions ?? 0,
                'note' => 'Performed by administrators',
                'icon' => 'shield',
                'accent' => '#e11d48',
                'soft' => 'rgba(225, 29, 72, 0.12)',
            ],
            [
                'label' => 'Staff Actions',
                'value' => $staffActions ?? 0,
                'note' => 'Performed by library staff',
                'icon' => 'briefcase',
                'accent' => '#059669',
                'soft' => 'rgba(5, 150, 105, 0.12)',
            ],
            [
                'label' => 'Student Actions',
                'value' => $studentActions ?? 0,
                'note' => 'Performed by students',
                'icon' => 'graduation-cap',
                'accent' => '#d97706',
                'soft' => 'rgba(217, 119, 6, 0.12)',
            ],
            [
                'label' => 'Action Categories',
                'value' => $actionCategories->count(),
                'note' => 'Distinct audit categories tracked',
                'icon' => 'layers',
                'accent' => '#64748b',
                'soft' => 'rgba(100, 116, 139, 0.12)',
            ],
        ];

        $activeFilterCount = collect([
            request('search'),
            request('role'),
            request('action_category'),
            request('period') && request('period') !== 'all' ? request('period') : null,
        ])->filter(fn($value) => filled($value))->count();
    @endphp

    <div id="activityLogAsyncRoot" class="activity-page activity-async-root">
        <div class="activity-page-header">
            <div>
                <h1 class="activity-page-title">Activity Logs</h1>
                <p class="activity-page-kicker text-muted">Admin Audit Center</p>
                {{-- <p class="mt-2 text-sm text-muted">Search, filter, and review system events with clearer actor, event,
                    and context grouping.</p> --}}
            </div>
            <div class="activity-page-note">
                <i data-lucide="shield-check"></i>
                <span>{{ number_format($totalActivities ?? 0) }} matching {{ $activeFilterCount ?: 'all' }} audit
                    entries</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-2 mb-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($cards as $card)
                <div class="p-4 shadow-sm card activity-stat-card"
                    style="--card-accent: {{ $card['accent'] }}; --card-soft: {{ $card['soft'] }};">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-xs font-medium text-muted">{{ $card['label'] }}</p>
                            <h3 class="text-2xl font-bold">{{ number_format($card['value']) }}</h3>
                        </div>
                        <i data-lucide="{{ $card['icon'] }}" class="activity-stat-icon"></i>
                    </div>
                    <p class="text-xs text-muted">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="p-4 mb-4 shadow-sm card activity-panel activity-filter-panel">
            <div class="activity-filter-grid">
                <div class="activity-filter-group">
                    {{-- <label for="search" class="activity-filter-label">Search</label> --}}
                    <div class="activity-search-shell">
                        {{-- <i data-lucide="search"></i> --}}
                        <input type="text" id="search" name="search" class="search-input"
                            placeholder="Search name, email, action, or description" autocomplete="off"
                            value="{{ request('search') }}">
                    </div>
                </div>

                <div class="activity-filter-group">
                    {{-- <label for="role" class="activity-filter-label">Role</label> --}}
                    <select id="role" name="role" class="filter-select">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>

                <div class="activity-filter-group">
                    {{-- <label for="period" class="activity-filter-label">Period</label> --}}
                    <select id="period" name="period" class="filter-select">
                        <option value="all" {{ request('period') == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="7days" {{ request('period') == '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ request('period') == '30days' ? 'selected' : '' }}>Last 30 Days</option>
                    </select>
                </div>

                <div class="activity-filter-group">
                    {{-- <label for="action_category" class="activity-filter-label">Category</label> --}}
                    <select id="action_category" name="action_category" class="filter-select">
                        <option value="">All Categories</option>
                        @foreach ($actionCategories as $category)
                            <option value="{{ $category }}"
                                {{ request('action_category') == $category ? 'selected' : '' }}>
                                {{ \Illuminate\Support\Str::headline($category) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="activity-filter-group">
                    <select id="per_page" name="per_page" class="filter-select" aria-label="Show activity entries">
                        @foreach ([10, 20, 50, 100] as $entryCount)
                            <option value="{{ $entryCount }}" {{ (int) request('per_page', 10) === $entryCount ? 'selected' : '' }}>
                                Show {{ $entryCount }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button id="resetFiltersBtn" type="button" class="activity-reset-btn">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>

        <div class="p-4 shadow-sm card activity-panel">
            <div class="activity-panel-header">
                <div>
                    <h2 class="activity-panel-title">Audit Trail</h2>
                    <p class="activity-panel-copy">
                        Showing {{ $activities->count() > 0 ? $activities->firstItem() : 0 }} to
                        {{ $activities->count() > 0 ? $activities->lastItem() : 0 }} of
                        {{ number_format($totalActivities ?? 0) }} entries.
                    </p>
                </div>
                <div class="activity-results-chip">
                    <i data-lucide="table" class="w-4 h-4"></i>
                    <span>Grouped by time, actor, event, and context</span>
                </div>
            </div>

            <div class="activity-table-wrapper">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Actor</th>
                            <th>Event</th>
                            <th>Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            @php
                                $userName = trim($activity->user_name ?: 'Unknown User');
                                $nameParts = preg_split('/\s+/', $userName) ?: [];
                                $initials = collect($nameParts)
                                    ->filter()
                                    ->take(2)
                                    ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                                    ->implode('');
                                $role = strtolower($activity->user_role ?: 'system');
                                $roleClass = 'role-badge-system';

                                if ($role === 'admin') {
                                    $roleClass = 'role-badge-admin';
                                } elseif ($role === 'staff') {
                                    $roleClass = 'role-badge-staff';
                                } elseif ($role === 'student') {
                                    $roleClass = 'role-badge-student';
                                }

                                $actionLabel = \Illuminate\Support\Str::headline($activity->action ?: 'Activity');
                                $categoryLabel = $activity->action_category
                                    ? \Illuminate\Support\Str::headline($activity->action_category)
                                    : null;
                                $description = $activity->readable_description ?: $activity->description;
                            @endphp
                            <tr>
                                <td data-label="Time" class="activity-time-col">
                                    <div class="time-stack">
                                        <span class="time-date">{{ $activity->created_at->format('M j, Y') }}</span>
                                        <span class="time-hour"
                                            title="{{ $activity->created_at->format('F j, Y g:i:s A') }}">
                                            {{ $activity->created_at->format('g:i A') }}
                                        </span>
                                        <span class="time-day">{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td data-label="Actor">
                                    <div class="activity-actor">
                                        <div class="activity-avatar">{{ $initials ?: 'U' }}</div>
                                        <div class="activity-actor-copy">
                                            <div class="activity-actor-name-row">
                                                <span class="activity-actor-name">{{ $userName }}</span>
                                                <span class="role-badge {{ $roleClass }}">{{ ucfirst($role) }}</span>
                                            </div>
                                            <div class="activity-actor-email">
                                                {{ $activity->user_email ?: 'No email recorded' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Event">
                                    <div class="event-stack">
                                        <div class="event-badges">
                                            <span class="event-badge primary">{{ $actionLabel }}</span>
                                            @if ($categoryLabel)
                                                <span class="event-badge secondary">{{ $categoryLabel }}</span>
                                            @endif
                                        </div>
                                        <span class="event-title">Entry #{{ $activity->id }}</span>
                                        <span class="event-caption">
                                            {{ $activity->user_role ? ucfirst($activity->user_role) : 'System' }}
                                            activity log
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Context" class="context-col">
                                    <div class="context-description" title="{{ $description }}">
                                        {{ \Illuminate\Support\Str::limit($description, 220) }}
                                    </div>
                                    <div class="context-meta">
                                        @if ($activity->browser)
                                            <span class="context-pill">{{ $activity->browser }}</span>
                                        @endif
                                        @if ($activity->device_type)
                                            <span
                                                class="context-pill">{{ \Illuminate\Support\Str::headline($activity->device_type) }}</span>
                                        @endif
                                        @if ($activity->ip_address)
                                            <span class="context-pill">{{ $activity->ip_address }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i data-lucide="search-x" class="empty-state-icon"></i>
                                        <p class="empty-state-title">No activities found</p>
                                        <p class="empty-state-copy">Try broadening the search or clearing a few filters to
                                            bring more audit entries back into view.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activities->total() > 0)
                <div class="pagination-container">
                    {!! view('shared.admin-table-pagination', ['paginator' => $activities])->render() !!}
                </div>
            @endif
        </div>

        <div class="p-4 mt-4 shadow-sm card activity-panel">
            <div class="activity-panel-header">
                <div>
                    <h2 class="activity-panel-title">Most Frequent Action Types</h2>
                    <p class="activity-panel-copy">A quick summary of the busiest audit events across the system.</p>
                </div>
                <div class="activity-results-chip">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    <span>Top {{ $actionStats->count() }} action patterns</span>
                </div>
            </div>

            @if ($actionStats->count() > 0)
                <div class="activity-summary-grid">
                    @foreach ($actionStats as $stat)
                        <div class="summary-item">
                            <div>
                                <p class="summary-item-name">{{ \Illuminate\Support\Str::headline($stat->action) }}</p>
                                <p class="summary-item-copy">{{ number_format($stat->count) }}
                                    {{ \Illuminate\Support\Str::plural('entry', $stat->count) }} recorded</p>
                            </div>
                            <span class="summary-item-count">{{ number_format($stat->count) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i data-lucide="list" class="empty-state-icon"></i>
                    <p class="empty-state-title">No summary data available yet</p>
                    <p class="empty-state-copy">Action patterns will appear here automatically as new activities are
                        logged.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const asyncRoot = document.getElementById('activityLogAsyncRoot');
            if (!asyncRoot) {
                return;
            }

            let searchTimeout;
            let activeRequestController = null;

            const createIcons = () => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            };

            const getControls = () => ({
                searchInput: asyncRoot.querySelector('#search'),
                roleFilter: asyncRoot.querySelector('#role'),
                periodFilter: asyncRoot.querySelector('#period'),
                actionCategoryFilter: asyncRoot.querySelector('#action_category'),
                perPageFilter: asyncRoot.querySelector('#per_page'),
            });

            const buildFilterUrl = () => {
                const {
                    searchInput,
                    roleFilter,
                    periodFilter,
                    actionCategoryFilter,
                    perPageFilter
                } = getControls();

                const params = new URLSearchParams();

                if (searchInput && searchInput.value.trim()) {
                    params.set('search', searchInput.value.trim());
                }

                if (roleFilter && roleFilter.value) {
                    params.set('role', roleFilter.value);
                }

                if (periodFilter && periodFilter.value && periodFilter.value !== 'all') {
                    params.set('period', periodFilter.value);
                }

                if (actionCategoryFilter && actionCategoryFilter.value) {
                    params.set('action_category', actionCategoryFilter.value);
                }

                if (perPageFilter && perPageFilter.value) {
                    params.set('per_page', perPageFilter.value);
                }

                const query = params.toString();
                return `${window.location.pathname}${query ? `?${query}` : ''}`;
            };

            const setLoadingState = (loading) => {
                asyncRoot.classList.toggle('is-loading', loading);
                asyncRoot.setAttribute('aria-busy', loading ? 'true' : 'false');
            };

            const renderNextState = (html, url, historyMode, scrollY, focusConfig) => {
                const parsed = new DOMParser().parseFromString(html, 'text/html');
                const nextRoot = parsed.getElementById('activityLogAsyncRoot');

                if (!nextRoot) {
                    throw new Error('Async activity log container was not found in the response.');
                }

                asyncRoot.innerHTML = nextRoot.innerHTML;

                if (historyMode === 'push') {
                    window.history.pushState({
                        url
                    }, '', url);
                } else if (historyMode === 'replace') {
                    window.history.replaceState({
                        url
                    }, '', url);
                }

                if (parsed.title) {
                    document.title = parsed.title;
                }

                createIcons();

                window.scrollTo({
                    top: scrollY,
                    behavior: 'auto'
                });

                if (focusConfig?.targetId) {
                    const focusTarget = asyncRoot.querySelector(`#${focusConfig.targetId}`);
                    if (focusTarget) {
                        focusTarget.focus();

                        if (typeof focusConfig.selectionStart === 'number' &&
                            typeof focusConfig.selectionEnd === 'number' &&
                            typeof focusTarget.setSelectionRange === 'function') {
                            focusTarget.setSelectionRange(focusConfig.selectionStart, focusConfig.selectionEnd);
                        } else if (typeof focusTarget.select === 'function') {
                            focusTarget.select();
                        }
                    }
                }
            };

            const fetchAndRender = async (url, options = {}) => {
                const {
                    historyMode = 'push',
                    focusTargetId = null,
                } = options;

                const activeElement = document.activeElement;
                const focusConfig = focusTargetId ? {
                    targetId: focusTargetId,
                    selectionStart: activeElement && activeElement.id === focusTargetId ? activeElement.selectionStart : null,
                    selectionEnd: activeElement && activeElement.id === focusTargetId ? activeElement.selectionEnd : null,
                } : null;

                const scrollY = window.scrollY;

                if (activeRequestController) {
                    activeRequestController.abort();
                }

                const requestController = new AbortController();
                activeRequestController = requestController;
                setLoadingState(true);

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        },
                        credentials: 'same-origin',
                        signal: requestController.signal,
                    });

                    if (!response.ok) {
                        throw new Error(`Request failed with status ${response.status}`);
                    }

                    const html = await response.text();
                    renderNextState(html, url, historyMode, scrollY, focusConfig);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        window.location.href = url;
                    }
                } finally {
                    if (activeRequestController === requestController) {
                        activeRequestController = null;
                        setLoadingState(false);
                    }
                }
            };

            asyncRoot.addEventListener('input', function(event) {
                if (event.target.id !== 'search') {
                    return;
                }

                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetchAndRender(buildFilterUrl(), {
                        historyMode: 'replace',
                        focusTargetId: 'search',
                    });
                }, 350);
            });

            asyncRoot.addEventListener('change', function(event) {
                if (!['role', 'period', 'action_category', 'per_page'].includes(event.target.id)) {
                    return;
                }

                fetchAndRender(buildFilterUrl(), {
                    historyMode: 'push',
                });
            });

            asyncRoot.addEventListener('click', function(event) {
                const resetButton = event.target.closest('#resetFiltersBtn');
                if (resetButton) {
                    event.preventDefault();
                    clearTimeout(searchTimeout);
                    fetchAndRender(window.location.pathname, {
                        historyMode: 'push',
                    });
                    return;
                }

                const paginationLink = event.target.closest('.admin-table-pagination a');
                if (paginationLink && asyncRoot.contains(paginationLink)) {
                    event.preventDefault();
                    fetchAndRender(paginationLink.href, {
                        historyMode: 'push',
                    });
                }
            });

            document.addEventListener('keydown', function(event) {
                const {
                    searchInput
                } = getControls();

                if (!searchInput) {
                    return;
                }

                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                } else if (event.key === 'Escape' && document.activeElement === searchInput) {
                    event.preventDefault();

                    if (searchInput.value !== '') {
                        searchInput.value = '';
                        clearTimeout(searchTimeout);
                        fetchAndRender(buildFilterUrl(), {
                            historyMode: 'replace',
                            focusTargetId: 'search',
                        });
                    } else {
                        searchInput.blur();
                    }
                }
            });

            window.addEventListener('popstate', function() {
                fetchAndRender(window.location.href, {
                    historyMode: 'none',
                });
            });

            window.history.replaceState({
                url: window.location.href
            }, '', window.location.href);
            createIcons();
        });
    </script>
@endpush
