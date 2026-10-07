@props([
    'role' => null,
    'greeting' => null,
    'subtitle' => null,
])

@php
    use App\Helpers\DateHelper;

    $roleKey = strtolower((string) ($role ?? auth()->user()?->role ?? 'user'));
    $roleTitle = match ($roleKey) {
        'admin' => 'Admin',
        'staff' => 'Staff',
        'student' => 'Student',
        default => 'Library User',
    };
    $roleBadge = match ($roleKey) {
        'admin' => 'Library Admin',
        'staff' => 'Library Staff',
        'student' => 'Student',
        default => 'User',
    };
    $now = now('Asia/Kathmandu');
    $resolvedGreeting = trim((string) ($greeting ?? DateHelper::getTimeBasedGreeting($now))) ?: 'Good Morning';
    $resolvedSubtitle = trim((string) ($subtitle ?? "Here's what's happening across your library right now."));
@endphp

@once
    <style>
        .dashboard-welcome-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            min-height: 4.75rem;
            padding: 0.45rem 0.15rem 0.75rem;
        }

        .dashboard-welcome-copy {
            min-width: 0;
        }

        .dashboard-welcome-title-row {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .dashboard-welcome-title {
            margin: 0;
            color: #172033;
            font-size: clamp(1.3rem, 2vw, 1.55rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.035em;
        }

        .dashboard-welcome-role {
            display: inline-flex;
            align-items: center;
            min-height: 1.55rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
        }

        .dashboard-welcome-role--admin {
            background: #ffede6;
            color: #f4511e;
        }

        .dashboard-welcome-role--staff {
            background: #e8f2ff;
            color: #2563eb;
        }

        .dashboard-welcome-role--student,
        .dashboard-welcome-role--user {
            background: #eef2f7;
            color: #475569;
        }

        .dashboard-welcome-subtitle {
            margin: 0.32rem 0 0;
            color: #5f718d;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        body.dark-theme .dashboard-welcome-title {
            color: #f8fafc;
        }

        body.dark-theme .dashboard-welcome-subtitle {
            color: #94a3b8;
        }

        body.dark-theme .dashboard-welcome-role--admin {
            background: rgba(249, 115, 22, 0.18);
            color: #fdba74;
        }

        body.dark-theme .dashboard-welcome-role--staff {
            background: rgba(59, 130, 246, 0.18);
            color: #93c5fd;
        }

        @media (max-width: 640px) {
            .dashboard-welcome-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.85rem;
                padding: 0.35rem 0 0.6rem;
            }

            .dashboard-welcome-title {
                font-size: 1.3rem;
            }
        }
    </style>
@endonce

<section
    {{ $attributes->class(['dashboard-welcome-header']) }}
    aria-label="Dashboard welcome"
>
    <div class="dashboard-welcome-copy">
        <div class="dashboard-welcome-title-row">
            <h1 class="dashboard-welcome-title">
                <span data-dashboard-greeting>{{ $resolvedGreeting }}</span>, {{ $roleTitle }}
            </h1>
            <span class="dashboard-welcome-role dashboard-welcome-role--{{ $roleKey }}">
                {{ $roleBadge }}
            </span>
        </div>

        @if ($resolvedSubtitle !== '')
            <p class="dashboard-welcome-subtitle">{{ $resolvedSubtitle }}</p>
        @endif
    </div>

    <x-dashboard-date-time-panel
        :date="DateHelper::formatAsBikramSambat($now)"
        :datetime="$now->toDateString()"
    />
</section>
