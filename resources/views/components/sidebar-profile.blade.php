@props([
    'profileUrl' => null,
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
    $resolvedProfileUrl = $profileUrl ?: match ($roleKey) {
        'admin' => route('admin.settings.index'),
        'staff' => route('staff.settings.index'),
        'student' => route('student.profile'),
        default => '#',
    };
    $profilePhotoUrl = filled($user?->profile_photo) 
        ? \App\Support\ProfilePhoto::resolveUrl($user->profile_photo) 
        : null;

    $initials = collect(preg_split('/\s+/', $userName, -1, PREG_SPLIT_NO_EMPTY))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    if ($initials === '') {
        $initials = strtoupper(mb_substr($userName, 0, 1));
    }

    $profileTooltip = trim($userName . ' • ' . $roleLabel);
@endphp

@once
    <style>
        .sidebar-profile-shell {
            position: sticky;
            bottom: 0;
            z-index: 2;
            flex-shrink: 0;
            margin-top: auto;
            padding: 0.85rem 0.75rem 1rem;
            border-top: 1px solid rgba(148, 163, 184, 0.16);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0), rgba(248, 250, 252, 0.96) 18%, #f8fafc 100%);
            backdrop-filter: blur(14px);
        }

        body.dark-theme .sidebar-profile-shell {
            border-top-color: rgba(148, 163, 184, 0.12);
            background: linear-gradient(180deg, rgba(30, 41, 59, 0), rgba(15, 23, 42, 0.92) 18%, #0f172a 100%);
        }

        .sidebar-profile-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem;
            border-radius: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.16);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.94)),
                radial-gradient(circle at top left, rgba(59, 130, 246, 0.12), transparent 48%);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
            animation: sidebarProfileFadeIn 0.42s ease both;
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease,
                background-color 0.2s ease;
        }

        .sidebar-profile-card:hover,
        .sidebar-profile-card:focus-within {
            transform: translateY(-1px);
            border-color: rgba(59, 130, 246, 0.24);
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.1);
        }

        body.dark-theme .sidebar-profile-card {
            border-color: rgba(148, 163, 184, 0.12);
            background:
                linear-gradient(135deg, rgba(30, 41, 59, 0.96), rgba(15, 23, 42, 0.94)),
                radial-gradient(circle at top left, rgba(96, 165, 250, 0.14), transparent 48%);
            box-shadow: 0 18px 34px rgba(2, 6, 23, 0.26);
        }

        body.dark-theme .sidebar-profile-card:hover,
        body.dark-theme .sidebar-profile-card:focus-within {
            border-color: rgba(96, 165, 250, 0.24);
            box-shadow: 0 20px 38px rgba(2, 6, 23, 0.32);
        }

        .sidebar-profile-main {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
            min-width: 0;
            text-decoration: none;
            color: inherit;
        }

        .sidebar-profile-main:focus-visible,
        .sidebar-profile-logout-button:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 3px;
        }

        .sidebar-profile-avatar,
        .sidebar-profile-avatar-fallback {
            width: 2.8rem;
            height: 2.8rem;
            border-radius: 9999px;
            flex-shrink: 0;
        }

        .sidebar-profile-avatar {
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.92);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.12);
        }

        .sidebar-profile-avatar-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #ffffff;
            background: linear-gradient(135deg, #2563eb 0%, #0f766e 100%);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.24);
        }

        body.dark-theme .sidebar-profile-avatar {
            border-color: rgba(15, 23, 42, 0.9);
            box-shadow: 0 8px 18px rgba(2, 6, 23, 0.28);
        }

        .sidebar-profile-copy {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.22rem;
        }

        .sidebar-profile-name {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1.2;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body.dark-theme .sidebar-profile-name {
            color: #f8fafc;
        }

        .sidebar-profile-role-row {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            min-width: 0;
            flex-wrap: wrap;
        }

        .sidebar-profile-role {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            max-width: 100%;
            padding: 0.22rem 0.55rem;
            border-radius: 9999px;
            border: 1px solid transparent;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .sidebar-profile-role--admin {
            background: #dbeafe;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .sidebar-profile-role--staff {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #15803d;
        }

        .sidebar-profile-role--student {
            background: #ede9fe;
            border-color: #ddd6fe;
            color: #6d28d9;
        }

        .sidebar-profile-role--user {
            background: #e2e8f0;
            border-color: #cbd5e1;
            color: #334155;
        }

        body.dark-theme .sidebar-profile-role--admin {
            background: rgba(37, 99, 235, 0.18);
            border-color: rgba(96, 165, 250, 0.22);
            color: #bfdbfe;
        }

        body.dark-theme .sidebar-profile-role--staff {
            background: rgba(22, 163, 74, 0.18);
            border-color: rgba(74, 222, 128, 0.22);
            color: #bbf7d0;
        }

        body.dark-theme .sidebar-profile-role--student {
            background: rgba(109, 40, 217, 0.18);
            border-color: rgba(167, 139, 250, 0.22);
            color: #ddd6fe;
        }

        body.dark-theme .sidebar-profile-role--user {
            background: rgba(51, 65, 85, 0.88);
            border-color: rgba(148, 163, 184, 0.18);
            color: #e2e8f0;
        }

        .sidebar-profile-logout-form {
            flex-shrink: 0;
        }

        .sidebar-profile-logout-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.85rem;
            border: 1px solid rgba(148, 163, 184, 0.18);
            background: rgba(255, 255, 255, 0.86);
            color: #475569;
            cursor: pointer;
            transition:
                background-color 0.2s ease,
                border-color 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease;
        }

        .sidebar-profile-logout-button:hover {
            transform: translateY(-1px);
            border-color: rgba(239, 68, 68, 0.24);
            background: #fef2f2;
            color: #dc2626;
        }

        body.dark-theme .sidebar-profile-logout-button {
            border-color: rgba(148, 163, 184, 0.14);
            background: rgba(15, 23, 42, 0.84);
            color: #cbd5e1;
        }

        body.dark-theme .sidebar-profile-logout-button:hover {
            border-color: rgba(248, 113, 113, 0.22);
            background: rgba(127, 29, 29, 0.32);
            color: #fca5a5;
        }

        .sidebar.is-collapsed .sidebar-profile-shell {
            padding-inline: 0.5rem;
        }

        .sidebar.is-collapsed .sidebar-profile-card {
            justify-content: center;
            padding: 0.7rem 0.55rem;
        }

        .sidebar.is-collapsed .sidebar-profile-copy {
            width: 0;
            opacity: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .sidebar.is-collapsed .sidebar-profile-main {
            flex: 0 1 auto;
        }

        .sidebar.is-collapsed .sidebar-profile-card::after {
            content: attr(data-sidebar-profile-tooltip);
            position: absolute;
            top: 50%;
            left: calc(100% + 0.75rem);
            transform: translateY(-50%) scale(0.98);
            transform-origin: left center;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
            padding: 0.45rem 0.7rem;
            border-radius: 0.7rem;
            background: rgba(15, 23, 42, 0.92);
            color: #f8fafc;
            font-size: 0.72rem;
            font-weight: 600;
            box-shadow: 0 14px 28px rgba(2, 6, 23, 0.24);
            transition:
                opacity 0.18s ease,
                transform 0.18s ease;
        }

        .sidebar.is-collapsed .sidebar-profile-card:hover::after,
        .sidebar.is-collapsed .sidebar-profile-card:focus-within::after {
            opacity: 1;
            transform: translateY(-50%) scale(1);
        }

        @media (max-width: 768px) {
            .sidebar-profile-shell {
                padding-bottom: 1.1rem;
            }

            .sidebar-profile-card::after {
                display: none;
            }
        }

        @keyframes sidebarProfileFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endonce

<div class="sidebar-profile-shell" data-sidebar-profile-shell>
    <div class="sidebar-profile-card" data-sidebar-profile-tooltip="{{ $profileTooltip }}">
        <a href="{{ $resolvedProfileUrl }}" class="sidebar-profile-main" title="{{ $profileTooltip }}" aria-label="Open profile for {{ $userName }}">
            @if ($profilePhotoUrl)
                <img src="{{ $profilePhotoUrl }}" alt="{{ $userName }}" class="sidebar-profile-avatar">
            @else
                <span class="sidebar-profile-avatar-fallback" aria-hidden="true">{{ $initials }}</span>
            @endif

            <div class="sidebar-profile-copy">
                <p class="sidebar-profile-name">{{ $userName }}</p>
                <div class="sidebar-profile-role-row">
                    <span class="sidebar-profile-role sidebar-profile-role--{{ $roleClass }}">{{ $roleLabel }}</span>
                </div>
            </div>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="sidebar-profile-logout-form">
            @csrf
            <button type="submit" class="sidebar-profile-logout-button" title="Log out" aria-label="Log out">
                <i data-lucide="log-out" class="w-4 h-4"></i>
            </button>
        </form>
    </div>
</div>
