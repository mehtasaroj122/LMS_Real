@props([
    'date' => null,
    'datetime' => null,
])

@php
    use App\Helpers\DateHelper;

    $initialDate = $date ?? DateHelper::formatAsBikramSambat();
    $machineDate = $datetime ?? now('Asia/Kathmandu')->toDateString();
@endphp

@once
    <style>
        .dashboard-date-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            flex: 0 0 auto;
            min-height: 2.35rem;
            padding: 0.55rem 0.85rem;
            border: 1px solid #dce4ef;
            border-radius: 0.7rem;
            background: #ffffff;
            color: #172033;
            box-shadow: 0 2px 5px rgba(15, 23, 42, 0.04);
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .dashboard-date-pill svg {
            width: 1rem;
            height: 1rem;
            flex: 0 0 auto;
            color: #64748b;
        }

        body.dark-theme .dashboard-date-pill {
            border-color: #334155;
            background: #1e293b;
            color: #f8fafc;
            box-shadow: 0 2px 5px rgba(2, 6, 23, 0.2);
        }

        body.dark-theme .dashboard-date-pill svg {
            color: #94a3b8;
        }

        @media (max-width: 640px) {
            .dashboard-date-pill {
                white-space: normal;
            }
        }
    </style>
@endonce

<div {{ $attributes->class(['dashboard-date-pill']) }} aria-label="Today's Bikram Sambat date">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M8 2v4"></path>
        <path d="M16 2v4"></path>
        <rect width="18" height="18" x="3" y="4" rx="2"></rect>
        <path d="M3 10h18"></path>
    </svg>
    <time datetime="{{ $machineDate }}" data-dashboard-bs-date>{{ $initialDate }}</time>
</div>

@once
    <script>
        (() => {
            const kathmanduHourFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                hour: 'numeric',
                hourCycle: 'h23',
            });

            const getGreeting = (date = new Date()) => {
                const hour = Number.parseInt(kathmanduHourFormatter.format(date), 10);

                if (hour < 12) return 'Good Morning';
                if (hour < 17) return 'Good Afternoon';
                return 'Good Evening';
            };

            const refreshGreeting = () => {
                const greeting = getGreeting();
                document.querySelectorAll('[data-dashboard-greeting]').forEach((node) => {
                    node.textContent = greeting;
                });
            };

            const initializeDashboardGreeting = () => {
                refreshGreeting();
                window.clearInterval(window.__dashboardGreetingIntervalId);
                window.__dashboardGreetingIntervalId = window.setInterval(refreshGreeting, 60 * 1000);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeDashboardGreeting, { once: true });
            } else {
                initializeDashboardGreeting();
            }
        })();
    </script>
@endonce
