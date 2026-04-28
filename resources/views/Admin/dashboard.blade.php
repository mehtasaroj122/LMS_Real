@extends('Admin.layouts.app')

@section('title', 'Dashboard')
@section('showDashboardHeader', '1')

@push('styles')
    <style>
        .dashboard-section-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .dashboard-section-grid > * {
            min-width: 0;
        }

        .fine-graph-card {
            padding: 1rem;
            min-width: 0;
            overflow: hidden;
        }

        .fine-trend-card-shell {
            transition: opacity 0.18s ease;
            min-width: 0;
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

        .fine-donut-canvas {
            width: 100%;
            height: 100%;
            display: block;
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
            cursor: pointer;
            transition:
                opacity 0.15s ease,
                color 0.15s ease;
        }

        .fine-legend-label {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }

        .fine-legend-item:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 4px;
            border-radius: 0.5rem;
        }

        .fine-legend-item.is-hidden {
            opacity: 0.42;
        }

        .fine-legend-item.is-hidden .fine-legend-text,
        .fine-legend-item.is-hidden .fine-legend-value {
            text-decoration: line-through;
        }

        .fine-legend-dot {
            width: 0.78rem;
            height: 0.78rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        .fine-trend-shell {
            position: relative;
            min-height: 21rem;
            min-width: 0;
            overflow: hidden;
        }

        .fine-trend-wrap {
            position: relative;
            min-width: 0;
        }

        .fine-trend-canvas-shell {
            position: relative;
            width: 100%;
            min-height: 21rem;
            height: 21rem;
            min-width: 0;
            overflow: hidden;
        }

        .fine-trend-chart {
            width: 100%;
            height: 100%;
            display: block;
            max-width: 100%;
        }

        .fine-transaction-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.85rem;
            min-width: 0;
        }

        .fine-transaction-heading {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            min-width: 0;
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

            .fine-trend-shell,
            .fine-trend-canvas-shell {
                min-height: 18rem;
                height: 18rem;
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
                    <div class="fine-donut-wrap">
                        <canvas
                            class="fine-donut-canvas"
                            aria-label="Fine status chart"
                            data-fine-status-chart
                            data-fine-status-total="{{ number_format($fineChartTotal, 2) }}"
                            data-fine-status-caption="Total Fine Value"
                            data-fine-status-labels='@json(collect($fineStatusLegend)->pluck("label")->all())'
                            data-fine-status-values='@json(collect($fineStatusLegend)->pluck("amount")->map(fn ($amount) => round((float) $amount, 2))->all())'
                            data-fine-status-colors='@json(collect($fineStatusLegend)->pluck("color")->all())'
                        ></canvas>
                    </div>

                    <div class="fine-legend">
                        @foreach($fineStatusLegend as $item)
                            <div
                                class="fine-legend-item text-primary"
                                data-fine-legend-item
                                data-fine-legend-index="{{ $loop->index }}"
                                role="button"
                                tabindex="0"
                                aria-pressed="true"
                            >
                                <span class="fine-legend-label">
                                    <span class="fine-legend-dot" style="background-color: {{ $item['color'] }}"></span>
                                    <span class="fine-legend-text">{{ $item['label'] }}</span>
                                </span>
                                <span class="fine-legend-value">₹{{ number_format($item['amount'], 2) }}</span>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const adminDashboardCharts = {
            fineStatus: null,
        };
        let adminDashboardThemeMode = null;
        let adminDashboardThemeObserverInitialized = false;
        let adminDashboardChartExtensionsRegistered = false;

        function getAdminDashboardPalette() {
            const isDark = document.body.classList.contains('dark-theme');

            return {
                surface: isDark ? '#172033' : '#ffffff',
                grid: isDark ? 'rgba(148, 163, 184, 0.18)' : 'rgba(148, 163, 184, 0.24)',
                text: isDark ? '#cbd5e1' : '#475569',
                textStrong: isDark ? '#f8fafc' : '#0f172a',
                textMuted: isDark ? '#94a3b8' : '#64748b',
                tooltipBackground: isDark ? '#0f172a' : '#ffffff',
                tooltipBorder: isDark ? '#334155' : '#dbe4f0',
            };
        }

        function formatFineCurrency(value) {
            return `₹${Number(value || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })}`;
        }

        function registerAdminDashboardChartExtensions() {
            if (typeof Chart === 'undefined' || adminDashboardChartExtensionsRegistered) {
                return;
            }

            if (Chart.Tooltip && Chart.Tooltip.positioners && typeof Chart.Tooltip.positioners.cursor !== 'function') {
                Chart.Tooltip.positioners.cursor = function (items, eventPosition) {
                    return eventPosition;
                };
            }

            Chart.register({
                id: 'adminDoughnutCenterText',
                afterDraw(chart, args, pluginOptions) {
                    if (chart.config.type !== 'doughnut' || !pluginOptions || !pluginOptions.value) {
                        return;
                    }

                    const chartArea = chart.chartArea;

                    if (!chartArea) {
                        return;
                    }

                    const ctx = chart.ctx;
                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = pluginOptions.valueColor || '#0f172a';
                    ctx.font = pluginOptions.valueFont || '700 18px Inter, sans-serif';
                    ctx.fillText(String(pluginOptions.value), centerX, centerY - 6);
                    ctx.fillStyle = pluginOptions.labelColor || '#64748b';
                    ctx.font = pluginOptions.labelFont || '600 11px Inter, sans-serif';
                    ctx.fillText(String(pluginOptions.label || ''), centerX, centerY + 16);
                    ctx.restore();
                },
            });

            adminDashboardChartExtensionsRegistered = true;
        }

        function parseChartDataset(element, datasetKey, fallback = []) {
            if (!element || !element.dataset || typeof element.dataset[datasetKey] !== 'string') {
                return fallback;
            }

            try {
                return JSON.parse(element.dataset[datasetKey]);
            } catch (error) {
                return fallback;
            }
        }

        function destroyFineStatusChart() {
            if (!adminDashboardCharts.fineStatus) {
                return;
            }

            adminDashboardCharts.fineStatus.destroy();
            adminDashboardCharts.fineStatus = null;
        }

        function destroyFineTrendChart(container) {
            if (!container || !container._fineTrendChart) {
                return;
            }

            container._fineTrendChart.destroy();
            container._fineTrendChart = null;
        }

        function syncFineStatusLegendState() {
            const chart = adminDashboardCharts.fineStatus;

            document.querySelectorAll('[data-fine-legend-item]').forEach(function (item) {
                const index = Number(item.dataset.fineLegendIndex || 0);
                const isVisible = chart ? chart.getDataVisibility(index) : true;

                item.classList.toggle('is-hidden', !isVisible);
                item.setAttribute('aria-pressed', String(isVisible));
            });
        }

        function bindFineStatusLegend() {
            document.querySelectorAll('[data-fine-legend-item]').forEach(function (item) {
                if (item.dataset.legendBound === 'true') {
                    return;
                }

                item.dataset.legendBound = 'true';

                const toggleLegendItem = function () {
                    const chart = adminDashboardCharts.fineStatus;

                    if (!chart) {
                        return;
                    }

                    const index = Number(item.dataset.fineLegendIndex || 0);
                    chart.toggleDataVisibility(index);
                    chart.update();
                    syncFineStatusLegendState();
                };

                item.addEventListener('click', toggleLegendItem);
                item.addEventListener('keydown', function (event) {
                    if (event.key !== 'Enter' && event.key !== ' ') {
                        return;
                    }

                    event.preventDefault();
                    toggleLegendItem();
                });
            });
        }

        function initializeFineStatusChart() {
            if (typeof Chart === 'undefined') {
                return;
            }

            const canvas = document.querySelector('[data-fine-status-chart]');
            destroyFineStatusChart();

            if (!canvas) {
                return;
            }

            registerAdminDashboardChartExtensions();

            const palette = getAdminDashboardPalette();
            const labels = parseChartDataset(canvas, 'fineStatusLabels', []);
            const values = parseChartDataset(canvas, 'fineStatusValues', []).map(function (value) {
                return Number(value || 0);
            });
            const colors = parseChartDataset(canvas, 'fineStatusColors', []);
            const total = canvas.dataset.fineStatusTotal || Number(values.reduce(function (sum, value) {
                return sum + Number(value || 0);
            }, 0)).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
            const caption = canvas.dataset.fineStatusCaption || 'Total Fine Value';

            Chart.defaults.color = palette.text;
            Chart.defaults.borderColor = palette.grid;

            adminDashboardCharts.fineStatus = new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderColor: palette.surface,
                        borderWidth: 0,
                        hoverOffset: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    layout: {
                        padding: 0,
                    },
                    onHover(event, elements, chart) {
                        chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            position: 'cursor',
                            backgroundColor: palette.tooltipBackground,
                            borderColor: palette.tooltipBorder,
                            borderWidth: 1,
                            titleColor: palette.textStrong,
                            bodyColor: palette.text,
                            padding: 10,
                            displayColors: true,
                            callbacks: {
                                label(context) {
                                    const value = Number(context.raw || 0);
                                    const total = values.reduce(function (sum, item) {
                                        return sum + Number(item || 0);
                                    }, 0);
                                    const share = total > 0 ? Math.round((value / total) * 100) : 0;

                                    return `${context.label}: ${formatFineCurrency(value)} (${share}%)`;
                                },
                            },
                        },
                        adminDoughnutCenterText: {
                            value: `₹${total}`,
                            label: caption,
                            valueColor: palette.textStrong,
                            labelColor: palette.textMuted,
                        },
                    },
                },
            });

            bindFineStatusLegend();
            syncFineStatusLegendState();
        }

        function initializeFineTrendChart(container) {
            if (typeof Chart === 'undefined' || !container) {
                return;
            }

            const canvas = container.querySelector('[data-fine-trend-chart]');
            destroyFineTrendChart(container);

            if (!canvas) {
                return;
            }

            registerAdminDashboardChartExtensions();

            const palette = getAdminDashboardPalette();
            const labels = parseChartDataset(canvas, 'fineTrendLabels', []);
            const axisLabels = parseChartDataset(canvas, 'fineTrendAxisLabels', labels);
            const pending = parseChartDataset(canvas, 'fineTrendPending', []).map(function (value) {
                return Number(value || 0);
            });
            const collected = parseChartDataset(canvas, 'fineTrendCollected', []).map(function (value) {
                return Number(value || 0);
            });
            const waived = parseChartDataset(canvas, 'fineTrendWaived', []).map(function (value) {
                return Number(value || 0);
            });

            Chart.defaults.color = palette.text;
            Chart.defaults.borderColor = palette.grid;

            container._fineTrendChart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Pending Fines',
                        data: pending,
                        borderColor: '#f87171',
                        backgroundColor: 'rgba(248, 113, 113, 0.12)',
                        fill: true,
                        tension: 0.34,
                        borderWidth: 4,
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#f87171',
                        pointBorderColor: palette.surface,
                        pointBorderWidth: 3,
                    }, {
                        label: 'Collected Fines',
                        data: collected,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.34,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: palette.surface,
                        pointBorderWidth: 3,
                    }, {
                        label: 'Waived Fines',
                        data: waived,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        fill: true,
                        tension: 0.34,
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: palette.surface,
                        pointBorderWidth: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 0,
                            right: 0,
                            bottom: 0,
                            left: 0,
                        },
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    onHover(event, elements, chart) {
                        chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'center',
                            labels: {
                                color: palette.textStrong,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 22,
                                boxWidth: 10,
                                boxHeight: 10,
                                font: {
                                    size: 12,
                                    weight: '600',
                                },
                            },
                        },
                        tooltip: {
                            position: 'cursor',
                            backgroundColor: palette.tooltipBackground,
                            borderColor: palette.tooltipBorder,
                            borderWidth: 1,
                            titleColor: palette.textStrong,
                            bodyColor: palette.text,
                            padding: 10,
                            displayColors: true,
                            callbacks: {
                                title(context) {
                                    const point = Array.isArray(context) ? context[0] : null;
                                    return point ? labels[point.dataIndex] || '' : '';
                                },
                                label(context) {
                                    return `${context.dataset.label}: ${formatFineCurrency(context.parsed.y)}`;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: palette.textStrong,
                                font: {
                                    size: 11,
                                    weight: '600',
                                },
                                maxTicksLimit: 6,
                                callback(value) {
                                    return `₹${Number(value || 0).toLocaleString('en-IN')}`;
                                },
                            },
                            grid: {
                                color: palette.grid,
                            },
                            border: {
                                display: false,
                            },
                        },
                        x: {
                            offset: false,
                            bounds: 'data',
                            ticks: {
                                color: palette.textStrong,
                                font: {
                                    size: 11,
                                    weight: '600',
                                },
                                align: 'inner',
                                autoSkip: false,
                                maxRotation: 0,
                                padding: 0,
                                callback(value, index) {
                                    return axisLabels[index] ?? labels[index] ?? '';
                                },
                            },
                            grid: {
                                color: palette.grid,
                                offset: false,
                            },
                            border: {
                                display: false,
                            },
                        },
                    },
                },
            });
        }

        function initializeAdminDashboardThemeObserver() {
            if (adminDashboardThemeObserverInitialized || !document.body) {
                return;
            }

            adminDashboardThemeMode = document.body.classList.contains('dark-theme') ? 'dark' : 'light';

            const observer = new MutationObserver(function () {
                const nextThemeMode = document.body.classList.contains('dark-theme') ? 'dark' : 'light';

                if (nextThemeMode === adminDashboardThemeMode) {
                    return;
                }

                adminDashboardThemeMode = nextThemeMode;
                initializeFineStatusChart();
                document.querySelectorAll('[data-fine-trend-card]').forEach(initializeFineTrendChart);
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class'],
            });

            adminDashboardThemeObserverInitialized = true;
        }

        function clearFineTrendHistory() {
            const url = new URL(window.location.href);

            if (!url.searchParams.has('fine_period')) {
                return;
            }

            url.searchParams.delete('fine_period');
            window.history.replaceState({}, '', url);
        }

        function refreshLucideIcons() {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        }

        function bindFineTrendCard(container) {
            if (!container) {
                return;
            }

            initializeFineTrendChart(container);

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

                        destroyFineTrendChart(container);
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
        }

        document.addEventListener('DOMContentLoaded', function () {
            clearFineTrendHistory();
            initializeFineStatusChart();
            document.querySelectorAll('[data-fine-trend-card]').forEach(bindFineTrendCard);
            initializeAdminDashboardThemeObserver();
        });
    </script>
@endpush
