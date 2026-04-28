@props([
    'name' => null,
    'role' => null,
    'greeting' => null,
    'subtitle' => null,
])

@php
    use App\Helpers\DateHelper;

    $user = auth()->user();
    $userName = trim((string) ($name ?? $user?->name ?? 'Library User')) ?: 'Library User';
    $roleKey = strtolower((string) ($role ?? $user?->role ?? 'user'));
    $roleLabel = match ($roleKey) {
        'admin' => 'ADMIN',
        'staff' => 'STAFF',
        'student' => 'STUDENT',
        default => strtoupper($roleKey ?: 'USER'),
    };
    $roleIcon = match ($roleKey) {
        'admin' => <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 3l7 4v5c0 4.2-2.7 8.1-7 9-4.3-.9-7-4.8-7-9V7l7-4Z"></path>
    <path d="m9.5 12 1.7 1.7 3.3-3.4"></path>
</svg>
SVG,
        'staff' => <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
    <rect x="3" y="7" width="18" height="12" rx="2"></rect>
    <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
    <path d="M3 12h18"></path>
</svg>
SVG,
        'student' => <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
    <path d="m2 8 10-5 10 5-10 5L2 8Z"></path>
    <path d="M6 10.5V15c0 1.4 2.7 3 6 3s6-1.6 6-3v-4.5"></path>
    <path d="M22 8v6"></path>
</svg>
SVG,
        default => <<<'SVG'
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"></path>
    <path d="M4 20a8 8 0 0 1 16 0"></path>
</svg>
SVG,
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
            'iconShell' => 'bg-sky-100/15 text-sky-100',
        ],
        'staff' => [
            'shell' => 'bg-gradient-to-br from-slate-950 via-blue-950 to-sky-800',
            'orbPrimary' => 'bg-sky-300/25',
            'orbSecondary' => 'bg-cyan-200/15',
            'dot' => 'bg-emerald-200',
            'badge' => 'border-emerald-200/35 bg-emerald-200/15 text-emerald-50',
            'iconShell' => 'bg-emerald-100/15 text-emerald-100',
        ],
        'student' => [
            'shell' => 'bg-gradient-to-br from-indigo-900 via-violet-800 to-fuchsia-700',
            'orbPrimary' => 'bg-violet-200/20',
            'orbSecondary' => 'bg-fuchsia-200/15',
            'dot' => 'bg-violet-100',
            'badge' => 'border-amber-200/35 bg-amber-200/15 text-amber-50',
            'iconShell' => 'bg-amber-100/15 text-amber-100',
        ],
        default => [
            'shell' => 'bg-gradient-to-br from-slate-900 via-slate-800 to-slate-700',
            'orbPrimary' => 'bg-white/15',
            'orbSecondary' => 'bg-slate-200/10',
            'dot' => 'bg-white',
            'badge' => 'border-white/20 bg-white/10 text-white',
            'iconShell' => 'bg-white/10 text-white',
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
                    <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full {{ $theme['iconShell'] }}" aria-hidden="true">
                        {!! $roleIcon !!}
                    </span>
                    {{ $roleLabel }}
                </span>
                <p class="max-w-2xl text-sm leading-6 text-white/78">
                    {{ $resolvedSubtitle }}
                </p>
            </div>
        </div>

        <x-dashboard-date-time-panel
            :date="DateHelper::formatAsBikramSambat()"
            :time="DateHelper::formatAsTime()"
            class="w-full max-w-sm text-right xl:w-auto"
        />
    </div>
</section>
