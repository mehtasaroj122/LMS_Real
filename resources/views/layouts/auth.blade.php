@props([
    'title' => 'Authentication',
    'eyebrow' => 'Secure access',
    'heading' => 'Welcome',
    'subheading' => null,
    'heroTitle' => null,
    'heroCopy' => null,
    'heroTagline' => null,
    'heroFeatures' => null,
    'shellMaxWidthClass' => 'max-w-5xl',
    'panelGridClass' => 'lg:grid-cols-[minmax(0,26rem)_minmax(0,1fr)]',
    'contentMaxWidthClass' => 'max-w-md',
])

@php
    $branding = $libraryBranding ?? [];
    $brandName = trim((string) ($branding['name'] ?? 'Library Management System')) ?: 'Library Management System';
    $brandTagline = trim((string) ($branding['tagline'] ?? 'Modern library access for students, staff, and administrators.'))
        ?: 'Modern library access for students, staff, and administrators.';
    $pageTitle = trim((string) $title) !== '' ? trim((string) $title) . ' - ' . $brandName : $brandName;

    $heroPresets = [
        'Login' => [
            'title' => 'Pick up where your library work left off.',
            'copy' => 'Sign in to review loans, monitor requests, and stay on top of due dates, notices, and daily library activity from one secure portal.',
            'tagline' => 'Secure access keeps circulation, alerts, and account activity in one trusted workspace.',
            'features' => [
                [
                    'title' => 'Circulation overview',
                    'copy' => 'Check loans, returns, and requests as soon as you enter your dashboard.',
                ],
                [
                    'title' => 'Notice center',
                    'copy' => 'See reminders, fines, and account messages in one familiar place.',
                ],
                [
                    'title' => 'Role-aware access',
                    'copy' => 'Open the tools that match your student, staff, or admin permissions.',
                ],
            ],
        ],
        'Register' => [
            'title' => 'Complete your invited library account.',
            'copy' => 'Verify your invited details and set your password.',
            'tagline' => 'Staff and students finish setup here.',
            'features' => [
                [
                    'title' => 'Invited identity',
                    'copy' => 'Use the email, ID, and phone linked to your account.',
                ],
                [
                    'title' => 'Secure password',
                    'copy' => 'Choose your own password to finish setup.',
                ],
            ],
        ],
        'Forgot Password' => [
            'title' => 'Recover your account without losing your place.',
            'copy' => 'We will send a reset link so you can return to holds, fines, borrowing history, and upcoming due dates safely.',
            'tagline' => 'Password recovery protects access while keeping your library activity within reach.',
            'features' => [
                [
                    'title' => 'Secure reset link',
                    'copy' => 'Reset access through your registered email without exposing your account data.',
                ],
                [
                    'title' => 'Protected member data',
                    'copy' => 'Recovery is designed to safeguard borrowing history and account details.',
                ],
                [
                    'title' => 'Fast return to access',
                    'copy' => 'Get back to circulation tools and notices with only a few steps.',
                ],
            ],
        ],
        'Reset Password' => [
            'title' => 'Set a new password for your library account.',
            'copy' => 'Choose a fresh password to protect your borrowing history, notices, saved activity, and future sign-ins across the portal.',
            'tagline' => 'A stronger password helps keep library records and personal account activity secure.',
            'features' => [
                [
                    'title' => 'Stronger sign-in',
                    'copy' => 'Use a new password that is easier to trust and harder for others to guess.',
                ],
                [
                    'title' => 'Protected history',
                    'copy' => 'Keep your loans, renewals, and personal library activity tied to you alone.',
                ],
                [
                    'title' => 'Secure notifications',
                    'copy' => 'Preserve safe access to reminders, alerts, and future account recovery messages.',
                ],
            ],
        ],
        'Confirm Password' => [
            'title' => 'Confirm your identity before sensitive actions.',
            'copy' => 'This quick password check protects settings, secure workflows, and higher-trust actions inside the library management portal.',
            'tagline' => 'Confirmation adds another layer of protection before critical account or system changes.',
            'features' => [
                [
                    'title' => 'Settings protection',
                    'copy' => 'Reduce accidental changes when entering protected areas of the portal.',
                ],
                [
                    'title' => 'Administrative trust',
                    'copy' => 'Help secure pages that handle permissions, records, and restricted workflows.',
                ],
                [
                    'title' => 'Safer secure actions',
                    'copy' => 'Re-check identity before completing tasks with higher security impact.',
                ],
            ],
        ],
        'Verify Email' => [
            'title' => 'Verify your email to activate library communication.',
            'copy' => 'Email verification ensures you can receive due-date reminders, password recovery links, and important account notices on time.',
            'tagline' => 'A trusted inbox keeps alerts, resets, and verification messages dependable.',
            'features' => [
                [
                    'title' => 'Due-date reminders',
                    'copy' => 'Stay informed about returns, renewals, and upcoming deadlines through email.',
                ],
                [
                    'title' => 'Account recovery',
                    'copy' => 'Keep password resets and security messages flowing to the right address.',
                ],
                [
                    'title' => 'Trusted notifications',
                    'copy' => 'Receive announcements and verification updates without delivery confusion.',
                ],
            ],
        ],
        'Verify OTP' => [
            'title' => 'Complete account activation with your one-time code.',
            'copy' => 'Enter the verification code from your inbox to finish registration and unlock borrowing, requests, and library updates.',
            'tagline' => 'OTP verification confirms the right inbox before full library access begins.',
            'features' => [
                [
                    'title' => 'Safe onboarding',
                    'copy' => 'Add one more identity check before a new account can access the portal.',
                ],
                [
                    'title' => 'Faster activation',
                    'copy' => 'Finish verification quickly so the member account is ready to use.',
                ],
                [
                    'title' => 'Verified access',
                    'copy' => 'Connect the right user to the right email before borrowing and notices begin.',
                ],
            ],
        ],
        'Change Password' => [
            'title' => 'Replace your temporary password with one you control.',
            'copy' => 'Before entering the portal, set a secure password that protects your loans, notices, account activity, and future sessions.',
            'tagline' => 'Password updates help secure member records and staff actions from the very first sign-in.',
            'features' => [
                [
                    'title' => 'Account ownership',
                    'copy' => 'Move from a temporary credential to a password known only to you.',
                ],
                [
                    'title' => 'Safer sessions',
                    'copy' => 'Start future sign-ins with stronger protection around your portal access.',
                ],
                [
                    'title' => 'Protected records',
                    'copy' => 'Secure borrowing activity, notices, and account information from the start.',
                ],
            ],
        ],
        'Account Inactive' => [
            'title' => 'Your library access is paused while we review the account.',
            'copy' => 'Inactive status helps protect circulation tools and member data until permissions, membership, or verification requirements are confirmed.',
            'tagline' => 'Access remains branded and familiar while status checks are completed by the library team.',
            'features' => [
                [
                    'title' => 'Membership review',
                    'copy' => 'Allow the library to confirm account eligibility before access is restored.',
                ],
                [
                    'title' => 'Permission checks',
                    'copy' => 'Protect staff tools and member services while status or roles are being reviewed.',
                ],
                [
                    'title' => 'Support follow-up',
                    'copy' => 'Use the provided contact paths to resolve account issues with the library team.',
                ],
            ],
        ],
    ];

    $heroPreset = $heroPresets[trim((string) $title)] ?? [];
    $resolvedHeroTitle = trim((string) ($heroTitle ?? ($heroPreset['title'] ?? 'A calmer way to run your library.')))
        ?: 'A calmer way to run your library.';
    $resolvedHeroCopy = trim((string) ($heroCopy ?? ($heroPreset['copy'] ?? 'Give staff and students a faster, more accessible portal for book circulation, notifications, and day-to-day library operations.')))
        ?: 'Give staff and students a faster, more accessible portal for book circulation, notifications, and day-to-day library operations.';
    $resolvedHeroTagline = trim((string) ($heroTagline ?? ($heroPreset['tagline'] ?? $brandTagline)))
        ?: $brandTagline;

    $heroFeatures = is_array($heroFeatures) && count($heroFeatures) > 0
        ? $heroFeatures
        : ($heroPreset['features'] ?? [
            [
                'title' => 'Real-time tracking',
                'copy' => 'Follow requests, returns, and availability without extra manual work.',
            ],
            [
                'title' => 'Role-based access',
                'copy' => 'Give admins, staff, and students the tools each role actually needs.',
            ],
            [
                'title' => 'Responsive by design',
                'copy' => 'Stay productive on large displays while keeping mobile access clean and focused.',
            ],
        ]);
    $heroFeatureCount = count($heroFeatures);
    $heroFeatureGridClass = match (true) {
        $heroFeatureCount <= 1 => 'mt-6 grid gap-3 text-left',
        $heroFeatureCount === 2 => 'mt-6 grid gap-3 text-left sm:grid-cols-2',
        default => 'mt-6 grid gap-3 text-left sm:grid-cols-3',
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">

    <title>{{ $pageTitle }}</title>

    <script>
        (() => {
            try {
                const storedTheme = window.localStorage.getItem('theme');
                const normalizedTheme = typeof storedTheme === 'string' ? storedTheme.trim().toLowerCase() : '';
                const isDark = normalizedTheme === 'dark-theme' || normalizedTheme === 'dark'
                    ? true
                    : normalizedTheme === 'light-theme' || normalizedTheme === 'light'
                        ? false
                        : false;

                document.documentElement.classList.toggle('dark', isDark);
            } catch (error) {
                // Fall back to the default light theme if theme bootstrapping fails.
            }
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full bg-slate-100 text-slate-950 antialiased transition-colors duration-300 dark:bg-slate-950 dark:text-slate-50">
    @include('shared.library-branding.bootstrap')

    <div class="relative isolate min-h-screen overflow-hidden">
        <div class="pointer-events-none absolute left-1/2 top-0 -z-10 h-72 w-[90vw] max-w-5xl -translate-x-1/2 rounded-full bg-sky-200/60 blur-3xl dark:bg-sky-900/25"></div>
        <div class="pointer-events-none absolute bottom-[-8rem] right-[-6rem] -z-10 h-80 w-80 rounded-full bg-amber-200/70 blur-3xl dark:bg-amber-500/10"></div>
        <div class="pointer-events-none absolute left-[-6rem] top-1/3 -z-10 h-72 w-72 rounded-full bg-teal-200/60 blur-3xl dark:bg-teal-500/10"></div>

        <main class="mx-auto flex min-h-screen {{ $shellMaxWidthClass }} items-center px-3 py-4 sm:px-4 sm:py-6 lg:px-5">
            <div class="grid w-full overflow-hidden rounded-[1.5rem] border border-white/70 bg-white/72 shadow-[0_28px_80px_-46px_rgba(15,23,42,0.35)] backdrop-blur-xl dark:border-slate-800/80 dark:bg-slate-900/75 {{ $panelGridClass }}">
                <section class="relative flex items-center justify-center px-5 py-7 sm:px-7 lg:px-8 xl:px-9">
                    <div class="w-full {{ $contentMaxWidthClass }}">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-3 rounded-full border border-slate-200/80 bg-white/75 px-3 py-2 text-[0.72rem] font-medium text-slate-600 shadow-sm shadow-slate-950/5 backdrop-blur transition hover:border-sky-300 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-300 dark:hover:border-sky-400/40 dark:hover:text-white">
                            <x-logo size="sm" :lazy="false" />
                            <span class="truncate">{{ $brandName }}</span>
                        </a>

                        <div class="mt-5">
                            @if ($eyebrow)
                                <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-sky-700 dark:bg-sky-500/10 dark:text-sky-200">
                                    {{ $eyebrow }}
                                </span>
                            @endif

                            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white sm:text-[2.15rem]">
                                {{ $heading }}
                            </h1>

                            @if ($subheading)
                                <p class="mt-3 max-w-md text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    {{ $subheading }}
                                </p>
                            @endif
                        </div>

                        <div class="{{ $subheading ? 'mt-5' : 'mt-3' }}">
                            {{ $slot }}
                        </div>
                    </div>
                </section>

                <aside class="relative hidden overflow-hidden lg:flex lg:min-h-[38rem]">
                    <img
                        src="https://images.unsplash.com/photo-1568667256549-094345857637?q=80&w=715&auto=format&fit=crop"
                        alt=""
                        aria-hidden="true"
                        loading="lazy"
                        decoding="async"
                        class="absolute inset-0 h-full w-full scale-105 object-cover blur-[2px] brightness-[0.6]"
                    >
                    <div class="absolute inset-0 bg-slate-950/35"></div>
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-400/10 via-slate-950/25 to-slate-950/75"></div>
                    <div class="absolute -top-20 left-12 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute bottom-8 right-8 h-64 w-64 rounded-full bg-sky-200/15 blur-3xl"></div>

                    <div class="relative z-10 flex flex-1 items-center justify-center p-8 xl:p-10">
                        <div class="max-w-lg text-center text-white">
                            <div class="mx-auto flex w-fit items-center gap-3 rounded-full border border-white/20 bg-white/10 px-4 py-2.5 shadow-2xl backdrop-blur-md">
                                <x-logo size="2xl" :lazy="false" />
                                <div class="text-left">
                                    <p class="text-xs font-semibold uppercase tracking-[0.26em] text-sky-100/80">
                                        Connected library
                                    </p>
                                    <p class="mt-1 text-lg font-semibold text-white">
                                        {{ $brandName }}
                                    </p>
                                </div>
                            </div>

                            <h2 class="mt-7 text-2xl font-semibold leading-tight tracking-tight text-white xl:text-3xl">
                                {{ $resolvedHeroTitle }}
                            </h2>
                            <p class="mt-4 text-sm leading-6 text-slate-100/85 xl:text-[0.96rem]">
                                {{ $resolvedHeroCopy }}
                            </p>

                            <div class="mt-5 rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm leading-6 text-slate-100/80 backdrop-blur-md">
                                {{ $resolvedHeroTagline }}
                            </div>

                            @if ($heroFeatureCount > 0)
                                <div class="{{ $heroFeatureGridClass }}">
                                    @foreach ($heroFeatures as $feature)
                                        <div class="rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-md">
                                            <p class="text-sm font-semibold text-white">
                                                {{ $feature['title'] ?? '' }}
                                            </p>
                                            <p class="mt-2 text-sm leading-6 text-slate-100/80">
                                                {{ $feature['copy'] ?? '' }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </aside>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
