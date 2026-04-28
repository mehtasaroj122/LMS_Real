@props([
    'name' => null,
    'role' => null,
    'greeting' => null,
    'subtitle' => null,
    'dateTime' => [],
])

@php
    $user = auth()->user();
    $userName = trim((string) ($name ?? $user?->name ?? 'Library User')) ?: 'Library User';
    $roleKey = strtolower((string) ($role ?? $user?->role ?? 'user'));
    $roleLabel = match ($roleKey) {
        'admin' => 'ADMIN',
        'staff' => 'STAFF',
        'student' => 'STUDENT',
        default => strtoupper($roleKey ?: 'USER'),
    };

    $now = now('Asia/Kathmandu');
    $resolvedGreeting = trim((string) ($greeting ?? match (true) {
        $now->hour < 12 => 'Good Morning',
        $now->hour < 17 => 'Good Afternoon',
        default => 'Good Evening',
    })) ?: 'Good Morning';

    $resolvedSubtitle = trim((string) ($subtitle ?? match ($roleKey) {
        'admin' => 'System overview and insights',
        'staff' => 'Manage daily library operations',
        'student' => 'Track your reading activity and requests',
        default => "Here's what's happening in your library today",
    })) ?: "Here's what's happening in your library today";

    $theme = match ($roleKey) {
        'admin' => [
            'shell' => 'bg-gradient-to-br from-slate-950 via-blue-950 to-sky-800',
            'orbPrimary' => 'bg-sky-300/25',
            'orbSecondary' => 'bg-cyan-200/15',
            'dot' => 'bg-sky-200',
            'badge' => 'border-sky-200/35 bg-sky-200/15 text-sky-50',
        ],
        'staff' => [
            'shell' => 'bg-gradient-to-br from-slate-950 via-blue-950 to-sky-800',
            'orbPrimary' => 'bg-sky-300/25',
            'orbSecondary' => 'bg-cyan-200/15',
            'dot' => 'bg-emerald-200',
            'badge' => 'border-emerald-200/35 bg-emerald-200/15 text-emerald-50',
        ],
        'student' => [
            'shell' => 'bg-gradient-to-br from-indigo-900 via-violet-800 to-fuchsia-700',
            'orbPrimary' => 'bg-violet-200/20',
            'orbSecondary' => 'bg-fuchsia-200/15',
            'dot' => 'bg-violet-100',
            'badge' => 'border-violet-200/35 bg-violet-200/15 text-violet-50',
        ],
        default => [
            'shell' => 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700',
            'orbPrimary' => 'bg-white/15',
            'orbSecondary' => 'bg-slate-200/10',
            'dot' => 'bg-white',
            'badge' => 'border-white/20 bg-white/10 text-white',
        ],
    };
@endphp

<section
    {{ $attributes->class([
        'relative overflow-hidden rounded-[1.75rem] px-5 py-5 text-white shadow-[0_24px_60px_rgba(15,23,42,0.18)] sm:px-6 lg:px-7',
        $theme['shell'],
    ]) }}
>
    <div class="pointer-events-none absolute -right-10 -top-16 h-40 w-40 rounded-full blur-3xl {{ $theme['orbPrimary'] }}"></div>
    <div class="pointer-events-none absolute -bottom-20 left-0 h-44 w-44 rounded-full blur-3xl {{ $theme['orbSecondary'] }}"></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.18),transparent_34%),linear-gradient(180deg,rgba(255,255,255,0.05),transparent)]"></div>

    <div class="relative flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="min-w-0 flex-1">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[0.72rem] font-semibold uppercase tracking-[0.24em] text-white/85 shadow-sm backdrop-blur-sm">
                <span class="h-2 w-2 rounded-full {{ $theme['dot'] }}"></span>
                <span data-dashboard-greeting>{{ $resolvedGreeting }}</span>
            </div>

            <h2 class="mt-4 text-[1.9rem] font-black leading-tight tracking-tight text-white sm:text-[2.25rem]">
                Welcome back, {{ $userName }}
            </h2>

            <div class="mt-4 flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-full border px-3.5 py-1.5 text-sm font-extrabold uppercase tracking-[0.28em] shadow-sm backdrop-blur-sm {{ $theme['badge'] }}">
                    {{ $roleLabel }}
                </span>
                <p class="max-w-2xl text-sm leading-6 text-white/78">
                    {{ $resolvedSubtitle }}
                </p>
            </div>
        </div>

        <x-dashboard-date-time-panel
            :date-time="$dateTime"
            class="w-full max-w-sm text-right xl:w-auto"
        />
    </div>
</section>
