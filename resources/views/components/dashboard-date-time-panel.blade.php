@props([
    'label' => 'Local Time',
    'variant' => 'inverse',
    'date' => null,
    'time' => null,
])

@php
    use App\Helpers\DateHelper;

    $variantClasses = match ($variant) {
        'soft' => [
            'panel' => 'border border-slate-200/80 bg-white/80 shadow-sm shadow-slate-200/70',
            'label' => 'text-slate-500',
            'date' => 'text-slate-900',
            'time' => 'text-slate-950',
        ],
        default => [
            'panel' => 'border border-white/20 bg-white/10 shadow-lg shadow-slate-950/10',
            'label' => 'text-white/70',
            'date' => 'text-white',
            'time' => 'text-white',
        ],
    };

    // Use pre-formatted dates from props, or format now using DateHelper
    $initialDate = $date ?? DateHelper::formatAsBikramSambat();
    $initialTime = $time ?? DateHelper::formatAsTime();
@endphp

<div
    {{ $attributes->class([
        'min-w-[13.5rem] rounded-2xl px-4 py-3 backdrop-blur-md',
        $variantClasses['panel'],
    ]) }}
    data-dashboard-date-time-root
>
    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.28em] {{ $variantClasses['label'] }}">
        {{ $label }}
    </p>
    <p class="mt-2 min-h-[1.5rem] text-sm font-semibold leading-6 {{ $variantClasses['date'] }}" data-dashboard-bs-date>
        {{ $initialDate }}
    </p>
    <p class="mt-1 text-xl font-black tracking-tight {{ $variantClasses['time'] }}" data-dashboard-time>
        {{ $initialTime }}
    </p>
</div>

@once
    <script>
        (() => {
            /**
             * Live time and greeting formatter for Asia/Kathmandu timezone.
             * Only handles time/greeting updates - date conversion happens server-side.
             */
            const liveTimeFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });

            const kathmanduHourFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                hour: 'numeric',
                hourCycle: 'h23',
            });

            /**
             * Calculate greeting based on hour of day.
             */
            const getGreeting = (date = new Date()) => {
                const hour = parseInt(kathmanduHourFormatter.format(date), 10);
                
                if (hour < 12) {
                    return 'Good Morning';
                }
                if (hour < 17) {
                    return 'Good Afternoon';
                }
                return 'Good Evening';
            };

            /**
             * Update all dashboard elements with current time and greeting.
             */
            const refreshDashboard = (root = document) => {
                const now = new Date();
                const currentTime = liveTimeFormatter.format(now);
                const greeting = getGreeting(now);

                root.querySelectorAll('[data-dashboard-time]').forEach((node) => {
                    node.textContent = currentTime;
                });

                root.querySelectorAll('[data-dashboard-greeting]').forEach((node) => {
                    node.textContent = greeting;
                });
            };

            /**
             * Initialize time and greeting updates on component load.
             */
            const initializeDashboard = () => {
                // Update immediately
                refreshDashboard(document);

                // Clear any existing interval
                if (window.__dashboardIntervalId) {
                    window.clearInterval(window.__dashboardIntervalId);
                }

                // Update every second
                window.__dashboardIntervalId = window.setInterval(() => {
                    refreshDashboard(document);
                }, 1000);
            };

            // Wait for DOM or initialize immediately
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeDashboard, { once: true });
            } else {
                initializeDashboard();
            }
        })();
    </script>
@endonce
