@extends('Admin.layouts.app')

@section('page-title', 'Dashboard')

@push('styles')
    <style>
        .dashboard-section-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .fine-graph-card {
            padding: 1rem;
        }

        .fine-trend-card-shell {
            transition: opacity 0.18s ease;
        }

        .fine-trend-card-shell.is-loading {
            opacity: 0.64;
        }

        .fine-graph-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .fine-graph-title {
            font-size: 1rem;
            font-weight: 700;
        }

        .fine-graph-subtitle {
            margin-top: 0.2rem;
            font-size: 0.78rem;
            color: #64748b;
        }

        body.dark-theme .fine-graph-subtitle {
            color: #94a3b8;
        }

        .fine-status-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: center;
        }

        .fine-donut-wrap {
            position: relative;
            width: 14rem;
            height: 14rem;
            margin: 0 auto;
        }

        .fine-donut-wrap svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
            overflow: visible;
        }

        .fine-donut-track {
            fill: none;
            stroke-width: 16;
        }

        body.light-theme .fine-donut-track {
            stroke: #e5e7eb;
        }

        body.dark-theme .fine-donut-track {
            stroke: #334155;
        }

        .fine-donut-segment {
            fill: none;
            stroke-width: 16;
            cursor: pointer;
            transition:
                opacity 0.15s ease,
                filter 0.15s ease;
        }

        .fine-donut-segment:hover {
            opacity: 0.92;
            filter: brightness(1.05);
        }

        .fine-donut-center {
            position: absolute;
            inset: 50%;
            width: 8rem;
            height: 8rem;
            transform: translate(-50%, -50%);
            border-radius: 9999px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        body.light-theme .fine-donut-center {
            background: #ffffff;
            color: #0f172a;
        }

        body.dark-theme .fine-donut-center {
            background: #172033;
            color: #f1f5f9;
        }

        .fine-donut-total {
            font-size: 1.55rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .fine-tooltip {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 15;
            pointer-events: none;
            opacity: 0;
            transform: translate(-50%, calc(-100% - 12px)) scale(0.96);
            padding: 0.5rem 0.7rem;
            border-radius: 0.75rem;
            min-width: 140px;
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.18);
            transition:
                opacity 0.12s ease,
                transform 0.12s ease;
        }

        .fine-tooltip.is-visible {
            opacity: 1;
            transform: translate(-50%, calc(-100% - 12px)) scale(1);
        }

        body.light-theme .fine-tooltip {
            background: rgba(15, 23, 42, 0.96);
            color: #f8fafc;
        }

        body.dark-theme .fine-tooltip {
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
        }

        .fine-tooltip-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.76rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .fine-tooltip-dot {
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        .fine-tooltip-meta {
            margin-top: 0.3rem;
            font-size: 0.72rem;
            opacity: 0.9;
        }

        .fine-legend {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .fine-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.82rem;
            font-weight: 500;
            justify-content: space-between;
        }

        .fine-legend-label {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }

        .fine-legend-dot {
            width: 0.78rem;
            height: 0.78rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        .fine-trend-shell {
            position: relative;
        }

        .fine-trend-wrap {
            overflow-x: auto;
            overflow-y: hidden;
            position: relative;
            z-index: 1;
        }

        .fine-trend-chart {
            width: 100%;
            min-width: 560px;
            height: auto;
            display: block;
        }

        .fine-grid-line {
            stroke: rgba(148, 163, 184, 0.22);
            stroke-width: 1;
        }

        .fine-axis-label {
            font-size: 12px;
            fill: #64748b;
            font-weight: 500;
        }

        body.dark-theme .fine-axis-label {
            fill: #cbd5e1;
        }

        .fine-generated-area {
            fill: rgba(248, 113, 113, 0.10);
        }

        .fine-collected-area {
            fill: rgba(16, 185, 129, 0.10);
        }

        .fine-waived-area {
            fill: rgba(245, 158, 11, 0.10);
        }

        .fine-generated-line {
            fill: none;
            stroke: #f87171;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .fine-collected-line {
            fill: none;
            stroke: #10b981;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .fine-waived-line {
            fill: none;
            stroke: #f59e0b;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .fine-generated-point,
        .fine-collected-point,
        .fine-waived-point {
            stroke-width: 3;
            cursor: pointer;
        }

        .fine-generated-point {
            fill: #f87171;
            stroke: #ffffff;
        }

        .fine-collected-point {
            fill: #10b981;
            stroke: #ffffff;
        }

        .fine-waived-point {
            fill: #f59e0b;
            stroke: #ffffff;
        }

        body.dark-theme .fine-generated-point,
        body.dark-theme .fine-collected-point,
        body.dark-theme .fine-waived-point {
            stroke: #172033;
        }

        .fine-transaction-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.85rem;
        }

        .fine-transaction-heading {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .fine-transaction-icon {
            width: 1.1rem;
            height: 1.1rem;
            color: #111827;
            flex-shrink: 0;
            margin-top: 0.15rem;
        }

        body.dark-theme .fine-transaction-icon {
            color: #f1f5f9;
        }

        .fine-transaction-title {
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .fine-transaction-subtitle {
            margin-top: 0.25rem;
            font-size: 0.82rem;
            color: #64748b;
        }

        body.dark-theme .fine-transaction-subtitle {
            color: #94a3b8;
        }

        .fine-period-form {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: wrap;
        }

        .fine-period-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #64748b;
        }

        body.dark-theme .fine-period-label {
            color: #94a3b8;
        }

        .fine-period-select {
            min-width: 10.75rem;
            padding: 0.45rem 0.75rem;
            border-radius: 0.8rem;
            border: 1px solid #cbd5e1;
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.2;
            outline: none;
            transition:
                border-color 0.15s ease,
                box-shadow 0.15s ease;
        }

        .fine-period-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        body.light-theme .fine-period-select {
            background: #ffffff;
            color: #0f172a;
        }

        body.dark-theme .fine-period-select {
            background: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }

        .fine-period-select:disabled {
            cursor: wait;
            opacity: 0.8;
        }

        .fine-trend-legend {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.1rem 1.5rem;
            margin: 0.4rem 0 0.75rem;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .fine-trend-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .fine-trend-legend-dot {
            width: 0.95rem;
            height: 0.95rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        .fine-trend-tooltip {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 60;
            pointer-events: none;
            opacity: 0;
            transform: translate(-50%, calc(-100% - 12px)) scale(0.96);
            padding: 0.5rem 0.7rem;
            border-radius: 0.75rem;
            min-width: 150px;
            box-shadow: 0 16px 35px rgba(15, 23, 42, 0.18);
            transition:
                opacity 0.12s ease,
                transform 0.12s ease;
            white-space: nowrap;
        }

        .fine-trend-tooltip.is-visible {
            opacity: 1;
            transform: translate(-50%, calc(-100% - 12px)) scale(1);
        }

        body.light-theme .fine-trend-tooltip {
            background: rgba(15, 23, 42, 0.96);
            color: #f8fafc;
        }

        body.dark-theme .fine-trend-tooltip {
            background: rgba(255, 255, 255, 0.96);
            color: #0f172a;
        }

        .fine-trend-tooltip-label {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.76rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .fine-trend-tooltip-dot {
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        .fine-trend-tooltip-meta {
            margin-top: 0.3rem;
            font-size: 0.72rem;
            opacity: 0.9;
        }

        @media (min-width: 1100px) {
            .dashboard-section-grid {
                grid-template-columns: 0.9fr 1.3fr;
            }
        }

        @media (max-width: 640px) {
            .fine-donut-wrap {
                width: 12rem;
                height: 12rem;
            }

            .fine-donut-center {
                width: 6.8rem;
                height: 6.8rem;
            }

            .fine-trend-legend {
                justify-content: flex-start;
                margin-left: 0.5rem;
            }

            .fine-period-form {
                width: 100%;
            }

            .fine-period-select {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="w-full">

        <div class="mb-2">
            <h2 class="text-lg font-bold text-primary">Admin Dashboard</h2>
            <p class="text-xs text-muted">Overview of library statistics and activities</p>
        </div>

        <div class="grid grid-cols-1 gap-2 mb-4 md:grid-cols-2 lg:grid-cols-4">

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Total Books</p>
                        <h3 class="text-2xl font-bold">{{ $totalBooks }}</h3>
                    </div>
                    <i data-lucide="book" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">{{ $availableBooks }} available</p>
            </div>

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Books Issued</p>
                        <h3 class="text-2xl font-bold">{{ $issuedBooks }}</h3>
                    </div>
                    <i data-lucide="trending-up" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Currently issued</p>
            </div>

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Overdue Fines</p>
                        <h3 class="text-2xl font-bold text-danger">₹{{ number_format($pendingFines, 2) }}</h3>
                    </div>
                    <i data-lucide="alert-circle" class="w-5 h-5 text-danger"></i>
                </div>
                <p class="text-xs text-muted">Need attention</p>
            </div>

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Total Users</p>
                        <h3 class="text-2xl font-bold">{{ $totalStudents }}</h3>
                    </div>
                    <i data-lucide="users" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">{{ $totalStudents }} students</p>
            </div>

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Pending Fines</p>
                        <h3 class="text-2xl font-bold">₹{{ number_format($pendingFines, 2) }}</h3>
                    </div>
                    <i data-lucide="indian-rupee" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Unpaid fines</p>
            </div>

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Fines Collected</p>
                        <h3 class="text-2xl font-bold text-green-500">₹{{ number_format($collectedFines, 2) }}</h3>
                    </div>
                    <i data-lucide="indian-rupee" class="w-5 h-5 text-green-500"></i>
                </div>
                <p class="text-xs text-muted">Total collected</p>
            </div>

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Today's Activity</p>
                        <h3 class="text-2xl font-bold">{{ $todayActivities }}</h3>
                    </div>
                    <i data-lucide="calendar" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Actions logged</p>
            </div>

            <div class="p-4 shadow-sm card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-xs font-medium text-muted">Book Categories</p>
                        <h3 class="text-2xl font-bold">{{ $totalCategories }}</h3>
                    </div>
                    <i data-lucide="book-copy" class="w-5 h-5 text-muted"></i>
                </div>
                <p class="text-xs text-muted">Different categories</p>
            </div>

        </div>

        @php
            $fineChartTotal = collect($fineStatusLegend)->sum('amount');
            $radius = 54;
            $circumference = 2 * pi() * $radius;
            $offset = 0;
        @endphp

        <div class="dashboard-section-grid mb-3">

            <div class="shadow-sm card fine-graph-card">
                <div class="fine-graph-header">
                    <div>
                        <h4 class="fine-graph-title text-primary">Fine Status Overview</h4>
                        <p class="fine-graph-subtitle">Current distribution of fine amounts</p>
                    </div>
                </div>

                <div class="fine-status-layout">
                    <div class="fine-donut-wrap" data-fine-chart>
                        <svg viewBox="0 0 120 120" aria-label="Fine status chart">
                            <circle class="fine-donut-track" cx="60" cy="60" r="{{ $radius }}"></circle>

                            @foreach($fineStatusLegend as $item)
                                @php
                                    $segmentLength = $fineChartTotal > 0
                                        ? ($item['amount'] / $fineChartTotal) * $circumference
                                        : 0;
                                @endphp

                                @if($segmentLength > 0)
                                    <circle
                                        class="fine-donut-segment"
                                        cx="60"
                                        cy="60"
                                        r="{{ $radius }}"
                                        stroke="{{ $item['color'] }}"
                                        stroke-dasharray="{{ $segmentLength }} {{ $circumference - $segmentLength }}"
                                        stroke-dashoffset="{{ -$offset }}"
                                        data-label="{{ $item['label'] }}"
                                        data-amount="{{ number_format($item['amount'], 2) }}"
                                        data-percentage="{{ number_format($item['percentage'], 1) }}"
                                        data-color="{{ $item['color'] }}"
                                    ></circle>
                                @endif

                                @php
                                    $offset += $segmentLength;
                                @endphp
                            @endforeach
                        </svg>

                        <div class="fine-donut-center">
                            <span class="fine-donut-total">₹{{ number_format($fineChartTotal, 2) }}</span>
                            <p class="mt-1 text-xs font-medium text-muted">Total Fine Value</p>
                        </div>

                        <div class="fine-tooltip" data-fine-tooltip>
                            <div class="fine-tooltip-label">
                                <span class="fine-tooltip-dot" data-tooltip-dot></span>
                                <span data-tooltip-label></span>
                            </div>
                            <div class="fine-tooltip-meta">
                                <span data-tooltip-amount></span>
                                <span> • </span>
                                <span data-tooltip-percentage></span>
                            </div>
                        </div>
                    </div>

                    <div class="fine-legend">
                        @foreach($fineStatusLegend as $item)
                            <div class="fine-legend-item text-primary">
                                <span class="fine-legend-label">
                                    <span class="fine-legend-dot" style="background-color: {{ $item['color'] }}"></span>
                                    <span>{{ $item['label'] }}</span>
                                </span>
                                <span>₹{{ number_format($item['amount'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div
                class="fine-trend-card-shell"
                data-fine-trend-card
                data-fine-trend-url="{{ route('admin.dashboard.fine-trend') }}"
            >
                @include('Admin.partials.dashboard.fine-trend-card')
            </div>

        </div>

        <div class="grid grid-cols-1 gap-3 mb-3 lg:grid-cols-2">

            <div class="p-3 shadow-sm card">
                <h4 class="mb-2 text-sm font-bold">Overdue Fines</h4>

                @forelse($pendingFinesList as $fine)
                    <div class="flex items-start justify-between gap-2 pb-2 mb-2 border-b last:border-b-0 last:mb-0 last:pb-0">
                        <div class="flex-1">
                            <h5 class="text-xs font-semibold">
                                {{ optional(optional($fine->issuedBook)->book)->title ?? 'Book record unavailable' }}
                            </h5>
                            <p class="mt-0.5 text-xs text-muted">
                                {{ optional(optional($fine->student)->user)->name ?? 'Unknown student' }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted">
                                {{ (int) $fine->days_late }} day(s) late
                            </p>
                        </div>

                        <span class="inline-flex items-center justify-center flex-shrink-0 px-1.5 py-0.5 text-xs font-bold text-white bg-red-600 rounded-full whitespace-nowrap">
                            ₹{{ number_format((float) $fine->amount, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-muted">No pending fines 🎉</p>
                @endforelse

                <div class="pt-3 mt-3 text-center border-t">
                    <a
                        href="{{ route('admin.fines.index') }}"
                        class="inline-flex items-center px-4 py-2 text-xs font-semibold text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700"
                    >
                        View All Fines
                    </a>
                </div>
            </div>

            <div class="p-3 shadow-sm card">
                <h4 class="mb-2 text-sm font-bold">Recent Activity</h4>

                <div class="space-y-1">
                    @forelse($recentActivities as $activity)
                        <div class="pb-1 border-b last:border-b-0 last:pb-0">
                            <div class="flex items-start justify-between gap-1 mb-0.5">
                                <p class="flex-1 text-xs font-semibold line-clamp-2">
                                    {{ $activity->description ?? $activity->action }}
                                </p>

                                @if($activity->user)
                                    <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full flex-shrink-0 {{ 
                                        $activity->user->role === 'admin' 
                                            ? 'bg-white text-red-600 dark:bg-red-600 dark:text-white' :
                                        ($activity->user->role === 'staff' 
                                            ? 'bg-white text-blue-600 dark:bg-blue-600 dark:text-white' :
                                            'bg-white text-green-600 dark:bg-green-600 dark:text-white')
                                    }}">
                                        {{ ucfirst($activity->user->role) }}
                                    </span>
                                @elseif($activity->user_role)
                                    <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-semibold rounded-full flex-shrink-0 {{ 
                                        $activity->user_role === 'admin' 
                                            ? 'bg-white text-red-600 dark:bg-red-600 dark:text-white' :
                                        ($activity->user_role === 'staff' 
                                            ? 'bg-white text-blue-600 dark:bg-blue-600 dark:text-white' :
                                            'bg-white text-green-600 dark:bg-green-600 dark:text-white')
                                    }}">
                                        {{ ucfirst($activity->user_role) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center flex-shrink-0 px-1.5 py-0.5 text-xs font-semibold text-gray-600 bg-white rounded-full dark:bg-gray-600 dark:text-white">
                                        Unknown
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-1 text-xs text-muted">
                                <span>
                                    @if($activity->user)
                                        {{ $activity->user->name }}
                                    @elseif($activity->user_name)
                                        {{ $activity->user_name }}
                                    @else
                                        Unknown User
                                    @endif
                                </span>
                                <span>•</span>
                                <span>{{ $activity->created_at->format('n/d/Y, g:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-2 text-center">
                            <p class="text-xs text-muted">No activities yet</p>
                        </div>
                    @endforelse
                </div>

                <div class="pt-1 mt-2 border-t">
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-xs font-medium text-primary hover:underline">
                        View All Activities →
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const initFineChartTooltips = function (root) {
                root.querySelectorAll('[data-fine-chart]').forEach(function (chart) {
                    if (chart.dataset.tooltipBound === 'true') {
                        return;
                    }

                    const tooltip = chart.querySelector('[data-fine-tooltip]');
                    const label = tooltip?.querySelector('[data-tooltip-label]');
                    const amount = tooltip?.querySelector('[data-tooltip-amount]');
                    const percentage = tooltip?.querySelector('[data-tooltip-percentage]');
                    const dot = tooltip?.querySelector('[data-tooltip-dot]');

                    if (!tooltip || !label || !amount || !percentage || !dot) {
                        return;
                    }

                    chart.dataset.tooltipBound = 'true';

                    const showTooltip = function (event) {
                        const segment = event.currentTarget;
                        label.textContent = segment.dataset.label || '';
                        amount.textContent = `₹${segment.dataset.amount || 0}`;
                        percentage.textContent = `${segment.dataset.percentage || 0}%`;
                        dot.style.backgroundColor = segment.dataset.color || '#ef4444';
                        tooltip.classList.add('is-visible');
                        moveTooltip(event);
                    };

                    const moveTooltip = function (event) {
                        const rect = chart.getBoundingClientRect();
                        const x = event.clientX - rect.left;
                        const y = event.clientY - rect.top;

                        tooltip.style.left = `${x}px`;
                        tooltip.style.top = `${y}px`;
                    };

                    const hideTooltip = function () {
                        tooltip.classList.remove('is-visible');
                    };

                    chart.querySelectorAll('.fine-donut-segment').forEach(function (segment) {
                        segment.addEventListener('mouseenter', showTooltip);
                        segment.addEventListener('mousemove', moveTooltip);
                        segment.addEventListener('mouseleave', hideTooltip);
                    });

                    chart.addEventListener('mouseleave', hideTooltip);
                });
            };

            const initFineTrendTooltips = function (root) {
                root.querySelectorAll('[data-fine-trend]').forEach(function (chart) {
                    if (chart.dataset.tooltipBound === 'true') {
                        return;
                    }

                    const tooltip = chart.querySelector('[data-fine-trend-tooltip]');
                    const series = tooltip?.querySelector('[data-trend-tooltip-series]');
                    const month = tooltip?.querySelector('[data-trend-tooltip-month]');
                    const amount = tooltip?.querySelector('[data-trend-tooltip-amount]');
                    const dot = tooltip?.querySelector('[data-trend-tooltip-dot]');

                    if (!tooltip || !series || !month || !amount || !dot) {
                        return;
                    }

                    chart.dataset.tooltipBound = 'true';

                    const showTooltip = function (event) {
                        const point = event.currentTarget;
                        series.textContent = point.dataset.trendSeries || '';
                        month.textContent = point.dataset.trendMonth || '';
                        amount.textContent = `₹${point.dataset.trendAmount || 0}`;
                        dot.style.backgroundColor = point.dataset.trendColor || '#ef4444';
                        tooltip.classList.add('is-visible');
                        moveTooltip(event);
                    };

                    const moveTooltip = function (event) {
                        const rect = chart.getBoundingClientRect();
                        const x = event.clientX - rect.left;
                        const y = event.clientY - rect.top;

                        tooltip.style.left = `${x}px`;
                        tooltip.style.top = `${y}px`;
                    };

                    const hideTooltip = function () {
                        tooltip.classList.remove('is-visible');
                    };

                    chart.querySelectorAll('.fine-generated-point, .fine-collected-point, .fine-waived-point').forEach(function (point) {
                        point.addEventListener('mouseenter', showTooltip);
                        point.addEventListener('mousemove', moveTooltip);
                        point.addEventListener('mouseleave', hideTooltip);
                    });

                    chart.addEventListener('mouseleave', hideTooltip);
                });
            };

            const clearFineTrendHistory = function () {
                const url = new URL(window.location.href);

                if (!url.searchParams.has('fine_period')) {
                    return;
                }

                url.searchParams.delete('fine_period');
                window.history.replaceState({}, '', url);
            };

            const refreshLucideIcons = function () {
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
            };

            const bindFineTrendCard = function (container) {
                if (!container) {
                    return;
                }

                initFineTrendTooltips(container);

                const form = container.querySelector('[data-fine-period-form]');
                const select = container.querySelector('[data-fine-period-select]');
                const endpoint = form?.dataset.fineTrendUrl || container.dataset.fineTrendUrl;

                if (!form || !select || !endpoint) {
                    return;
                }

                const serverPeriod = select.dataset.serverPeriod || '12months';
                form.reset();
                select.value = serverPeriod;

                if (select.dataset.ajaxBound === 'true') {
                    return;
                }

                select.dataset.ajaxBound = 'true';

                select.addEventListener('change', function () {
                    const nextPeriod = select.value || '12months';

                    if (container._fineTrendAbortController) {
                        container._fineTrendAbortController.abort();
                    }

                    const controller = new AbortController();
                    const requestUrl = new URL(endpoint, window.location.origin);
                    requestUrl.searchParams.set('fine_period', nextPeriod);

                    container._fineTrendAbortController = controller;
                    container.classList.add('is-loading');
                    select.disabled = true;

                    fetch(requestUrl.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal,
                    })
                        .then(function (response) {
                            if (!response.ok) {
                                throw new Error('Failed to load fine trend data.');
                            }

                            return response.json();
                        })
                        .then(function (payload) {
                            if (!payload || typeof payload.html !== 'string') {
                                throw new Error('Invalid fine trend response.');
                            }

                            container.innerHTML = payload.html;
                            container.classList.remove('is-loading');
                            container._fineTrendAbortController = null;
                            refreshLucideIcons();
                            bindFineTrendCard(container);
                        })
                        .catch(function (error) {
                            container.classList.remove('is-loading');
                            container._fineTrendAbortController = null;

                            if (error.name === 'AbortError') {
                                return;
                            }

                            select.disabled = false;

                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit();
                                return;
                            }

                            form.submit();
                        });
                });
            };

            clearFineTrendHistory();
            initFineChartTooltips(document);
            document.querySelectorAll('[data-fine-trend-card]').forEach(bindFineTrendCard);
        });
    </script>
@endpush
