@props([
    'subtitle' => "Here's what's happening in your library today",
])

@php
    $user = auth()->user();
    $userName = trim((string) ($user?->name ?? 'Library User')) ?: 'Library User';
    $roleKey = strtolower((string) ($user?->role ?? 'user'));
    $roleLabel = match ($roleKey) {
        'admin' => 'Admin',
        'staff' => 'Staff',
        'student' => 'Student',
        default => ucfirst($roleKey ?: 'User'),
    };
    $roleClass = in_array($roleKey, ['admin', 'staff', 'student'], true) ? $roleKey : 'user';

    $currentHour = now()->hour;
    $greeting = match (true) {
        $currentHour < 12 => 'Good morning',
        $currentHour < 17 => 'Good afternoon',
        default => 'Good evening',
    };
@endphp

@once
    <style>
        .dashboard-header-card {
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.14), transparent 34%),
                linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        .dashboard-header-card::before,
        .dashboard-header-card::after {
            content: "";
            position: absolute;
            border-radius: 9999px;
            pointer-events: none;
        }

        .dashboard-header-card::before {
            top: -4rem;
            right: -3.5rem;
            width: 9rem;
            height: 9rem;
            background: rgba(59, 130, 246, 0.1);
        }

        .dashboard-header-card::after {
            bottom: -4.75rem;
            left: -4rem;
            width: 10rem;
            height: 10rem;
            background: rgba(16, 185, 129, 0.08);
        }

        body.dark-theme .dashboard-header-card {
            border-color: rgba(148, 163, 184, 0.2);
            background:
                radial-gradient(circle at top right, rgba(96, 165, 250, 0.18), transparent 34%),
                linear-gradient(135deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.94));
            box-shadow: 0 24px 48px rgba(2, 6, 23, 0.34);
        }

        body.dark-theme .dashboard-header-card::before {
            background: rgba(96, 165, 250, 0.12);
        }

        body.dark-theme .dashboard-header-card::after {
            background: rgba(52, 211, 153, 0.08);
        }

        .dashboard-header-layout {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem 1.5rem;
            flex-wrap: wrap;
        }

        .dashboard-header-copy {
            flex: 1 1 20rem;
            min-width: 0;
        }

        .dashboard-header-kicker {
            margin: 0 0 0.35rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #2563eb;
        }

        body.dark-theme .dashboard-header-kicker {
            color: #93c5fd;
        }

        .dashboard-header-title {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 800;
            line-height: 1.15;
            color: #0f172a;
        }

        body.dark-theme .dashboard-header-title {
            color: #f8fafc;
        }

        .dashboard-header-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.85rem;
        }

        .dashboard-header-role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid transparent;
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.01em;
        }

        .dashboard-header-role-badge--admin {
            background: #dbeafe;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .dashboard-header-role-badge--staff {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .dashboard-header-role-badge--student {
            background: #ede9fe;
            border-color: #ddd6fe;
            color: #6d28d9;
        }

        .dashboard-header-role-badge--user {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #334155;
        }

        body.dark-theme .dashboard-header-role-badge--admin {
            background: rgba(37, 99, 235, 0.18);
            border-color: rgba(96, 165, 250, 0.28);
            color: #bfdbfe;
        }

        body.dark-theme .dashboard-header-role-badge--staff {
            background: rgba(22, 163, 74, 0.18);
            border-color: rgba(74, 222, 128, 0.24);
            color: #bbf7d0;
        }

        body.dark-theme .dashboard-header-role-badge--student {
            background: rgba(109, 40, 217, 0.2);
            border-color: rgba(167, 139, 250, 0.28);
            color: #ddd6fe;
        }

        body.dark-theme .dashboard-header-role-badge--user {
            background: rgba(51, 65, 85, 0.88);
            border-color: rgba(148, 163, 184, 0.2);
            color: #e2e8f0;
        }

        .dashboard-header-subtitle {
            margin: 0.85rem 0 0;
            max-width: 42rem;
            font-size: 0.92rem;
            line-height: 1.6;
            color: #475569;
        }

        body.dark-theme .dashboard-header-subtitle {
            color: #cbd5e1;
        }

        .dashboard-header-meta {
            flex: 0 0 auto;
            min-width: min(100%, 14.5rem);
        }

        .dashboard-header-date-card {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            min-width: 14.5rem;
            padding: 0.95rem 1rem;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
        }

        body.dark-theme .dashboard-header-date-card {
            border-color: rgba(148, 163, 184, 0.14);
            background: rgba(15, 23, 42, 0.48);
        }

        .dashboard-header-date-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
        }

        body.dark-theme .dashboard-header-date-label {
            color: #94a3b8;
        }

        .dashboard-header-date-value {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            color: #0f172a;
        }

        body.dark-theme .dashboard-header-date-value {
            color: #f8fafc;
        }

        .dashboard-header-time-value {
            margin: 0;
            font-size: 0.84rem;
            font-weight: 600;
            color: #475569;
        }

        body.dark-theme .dashboard-header-time-value {
            color: #cbd5e1;
        }

        @media (max-width: 767px) {
            .dashboard-header-card {
                padding: 1rem;
                border-radius: 1rem;
            }

            .dashboard-header-title {
                font-size: 1.3rem;
            }

            .dashboard-header-meta,
            .dashboard-header-date-card {
                width: 100%;
                min-width: 0;
            }
        }
    </style>
@endonce

<section {{ $attributes->class('dashboard-header-card') }} data-dashboard-header-root>
    <div class="dashboard-header-layout">
        <div class="dashboard-header-copy">
            <p class="dashboard-header-kicker" data-dashboard-header-greeting>{{ $greeting }}</p>
            <h2 class="dashboard-header-title">Welcome back, {{ $userName }}</h2>

            <div class="dashboard-header-row">
                <span class="dashboard-header-role-badge dashboard-header-role-badge--{{ $roleClass }}">{{ $roleLabel }}</span>
            </div>

            <p class="dashboard-header-subtitle">{{ $subtitle }}</p>
        </div>

        <div class="dashboard-header-meta">
            <div class="dashboard-header-date-card">
                <span class="dashboard-header-date-label">Local time</span>
                <p class="dashboard-header-date-value" data-dashboard-header-date>{{ now()->format('l, F j, Y') }}</p>
                <p class="dashboard-header-time-value" data-dashboard-header-time>{{ now()->format('g:i A') }}</p>
            </div>
        </div>
    </div>
</section>

@once
    <script>
        (() => {
            const initializeDashboardHeaders = () => {
                document.querySelectorAll('[data-dashboard-header-root]').forEach((root) => {
                    if (root.dataset.dashboardHeaderInitialized === 'true') {
                        return;
                    }

                    root.dataset.dashboardHeaderInitialized = 'true';

                    const greetingNode = root.querySelector('[data-dashboard-header-greeting]');
                    const dateNode = root.querySelector('[data-dashboard-header-date]');
                    const timeNode = root.querySelector('[data-dashboard-header-time]');

                    const updateHeaderDateTime = () => {
                        const currentDate = new Date();
                        const currentHour = currentDate.getHours();
                        let greetingText = 'Good evening';

                        if (currentHour < 12) {
                            greetingText = 'Good morning';
                        } else if (currentHour < 17) {
                            greetingText = 'Good afternoon';
                        }

                        if (greetingNode) {
                            greetingNode.textContent = greetingText;
                        }

                        if (dateNode) {
                            dateNode.textContent = new Intl.DateTimeFormat(undefined, {
                                weekday: 'long',
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric',
                            }).format(currentDate);
                        }

                        if (timeNode) {
                            timeNode.textContent = new Intl.DateTimeFormat(undefined, {
                                hour: 'numeric',
                                minute: '2-digit',
                            }).format(currentDate);
                        }
                    };

                    updateHeaderDateTime();
                    window.setInterval(updateHeaderDateTime, 60000);
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeDashboardHeaders, { once: true });
            } else {
                initializeDashboardHeaders();
            }
        })();
    </script>
@endonce
