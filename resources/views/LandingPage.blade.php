@php
    $brandName = 'Library Management System';
    $contactEmail = 'librarymanagementsystem270@gmail.com';

    $heroProofs = [
        'Role-Based Portals',
        'Automated Fines',
        'Secure Access & Audit Logs',
    ];

    $problems = [
        'Lost or untracked books make inventory harder to trust.',
        'Delayed approvals create confusion between students and staff.',
        'Manual fine follow-up leads to missed payments and inconsistent records.',
    ];

    $features = [
        ['icon' => 'books', 'title' => 'Book Catalog Control', 'description' => 'Keep your entire inventory organized with categories, ISBNs, shelf numbers, copy counts, condition tracking, and live availability.'],
        ['icon' => 'students', 'title' => 'Student Records & Privileges', 'description' => 'Manage student accounts, departments, semesters, status changes, and borrowing privileges from one consistent workflow.'],
        ['icon' => 'requests', 'title' => 'Request Tracking', 'description' => 'Never lose track of pending, approved, issued, returned, rejected, and cancelled book requests.'],
        ['icon' => 'workflow', 'title' => 'Issue & Return Workflow', 'description' => 'Help staff issue and return books quickly with clear steps, due dates, condition handling, and request fulfillment.'],
        ['icon' => 'fines', 'title' => 'Fine & Payment Management', 'description' => 'Automatically calculate overdue fines, apply penalties, and manage paid or waived records with better control.'],
        ['icon' => 'notifications', 'title' => 'Notifications & Reminders', 'description' => 'Keep users informed with in-app notifications, queued emails, overdue reminders, and request status updates.'],
    ];

    $roles = [
        ['icon' => 'admin', 'title' => 'Admin', 'summary' => 'Full system control and visibility.', 'points' => ['Manage users, students, reports, and settings.', 'Oversee account lockouts, policies, and audit activity.', 'Monitor fines, requests, and system-wide operational health.']],
        ['icon' => 'staff', 'title' => 'Staff', 'summary' => 'Faster day-to-day library operations.', 'points' => ['Process requests and fulfill approved circulation quickly.', 'Issue and return books with structured workflows.', 'Handle fines, reminders, and inventory updates efficiently.']],
        ['icon' => 'student', 'title' => 'Student', 'summary' => 'A clearer self-service library experience.', 'points' => ['Search books and place requests from the student portal.', 'Track due dates, active books, fines, and notifications.', 'Manage profile details and stay informed about library activity.']],
    ];

    $workflowSteps = [
        ['icon' => 'login', 'title' => 'Login', 'description' => 'Users enter the portal matched to their role and permissions.'],
        ['icon' => 'search', 'title' => 'Search or Request', 'description' => 'Students browse available books and submit requests when needed.'],
        ['icon' => 'approve', 'title' => 'Approve or Reject', 'description' => 'Staff or admins review pending requests and update status clearly.'],
        ['icon' => 'issue', 'title' => 'Issue Book', 'description' => 'Books are issued with due dates, student limits, and activity tracking.'],
        ['icon' => 'return', 'title' => 'Return Book', 'description' => 'Returns update inventory, condition, and request lifecycle automatically.'],
        ['icon' => 'fine-notification', 'title' => 'Fine / Notification', 'description' => 'Overdues, payments, waivers, and reminders keep everyone informed.'],
    ];

    $previewCards = [
        ['kind' => 'admin', 'title' => 'Admin Dashboard', 'subtitle' => 'Books, users, fines, and reports', 'caption' => 'A clean overview of the system with the numbers and signals that matter most.'],
        ['kind' => 'staff', 'title' => 'Staff Workspace', 'subtitle' => 'Requests, circulation, and fine handling', 'caption' => 'A focused workspace for day-to-day actions without extra noise.'],
        ['kind' => 'student', 'title' => 'Student Portal', 'subtitle' => 'Search, requests, books, and reminders', 'caption' => 'A simple self-service view for finding books and tracking library activity.'],
    ];

    $securityPoints = [
        ['icon' => 'security', 'title' => 'Email Verification & OTP Signup', 'description' => 'Student registration is backed by OTP verification and email confirmation for stronger account trust.'],
        ['icon' => 'lock', 'title' => 'Rate Limiting & Account Lock Controls', 'description' => 'Login attempts are rate limited and admins get dedicated account lock management tools.'],
        ['icon' => 'portal', 'title' => 'Role-Based Access', 'description' => 'Admin, Staff, and Student portals keep the right actions in the right hands.'],
        ['icon' => 'audit', 'title' => 'Audit Trails & Scheduled Reminders', 'description' => 'Activity logs, overdue reminders, and fine reminder jobs support accountability over time.'],
    ];

    $uspPoints = [
        ['icon' => 'portal', 'title' => 'Dedicated Portals', 'description' => 'Give admins, staff, and students focused interfaces instead of one crowded dashboard.'],
        ['icon' => 'automation', 'title' => 'Fine Automation', 'description' => 'Apply overdue rules, caps, and penalties through configurable library settings.'],
        ['icon' => 'notifications', 'title' => 'Workflow-Aware Alerts', 'description' => 'Notify users when requests are processed, books are issued or returned, and fines need attention.'],
        ['icon' => 'security', 'title' => 'Stronger Account Protection', 'description' => 'Combine OTP, verification, lockouts, password resets, and audit logging in one platform.'],
    ];

    $useCases = [
        ['icon' => 'college', 'title' => 'College Libraries', 'description' => 'Manage departments, semesters, large student populations, and higher request volume with better visibility.'],
        ['icon' => 'school', 'title' => 'School Libraries', 'description' => 'Simplify borrowing, overdue follow-up, and day-to-day circulation for school staff and students.'],
        ['icon' => 'department', 'title' => 'Department Libraries', 'description' => 'Support focused collections with staff efficiency and admin oversight across smaller academic units.'],
    ];
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="description" content="A secure, role-based Library Management System for institutions to manage books, students, requests, fines, notifications, and library workflows from one platform.">
    <meta property="og:title" content="{{ $brandName }} | Role-Based Library Platform">
    <meta property="og:description" content="Manage books, students, requests, fines, and notifications in one secure system built for institutions.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    @include('partials.favicon')
    <meta property="og:image" content="{{ asset('favicon.ico') }}">
    <title>{{ $brandName }} | Secure, Role-Based Library Platform</title>

    <script>
        (() => {
            try {
                const storedTheme = window.localStorage.getItem('theme');
                const normalizedTheme = typeof storedTheme === 'string' ? storedTheme.trim().toLowerCase() : '';
                const isDark = normalizedTheme === 'dark-theme' || normalizedTheme === 'dark';

                document.documentElement.classList.toggle('dark', isDark);
            } catch (error) {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { color-scheme: light; --landing-bg: #f5f7fb; --landing-surface: rgba(255, 255, 255, 0.78); --landing-surface-strong: rgba(255, 255, 255, 0.92); --landing-border: rgba(148, 163, 184, 0.24); --landing-shadow: 0 24px 64px -40px rgba(15, 23, 42, 0.45); --landing-text: #0f172a; --landing-muted: #475569; --landing-muted-soft: #64748b; --landing-accent: #1d4ed8; --landing-accent-strong: #0f766e; --landing-accent-soft: rgba(29, 78, 216, 0.12); }
        .dark { color-scheme: dark; --landing-bg: #020617; --landing-surface: rgba(15, 23, 42, 0.72); --landing-surface-strong: rgba(15, 23, 42, 0.88); --landing-border: rgba(148, 163, 184, 0.18); --landing-shadow: 0 28px 72px -46px rgba(2, 6, 23, 0.85); --landing-text: #e2e8f0; --landing-muted: #cbd5e1; --landing-muted-soft: #94a3b8; --landing-accent: #60a5fa; --landing-accent-strong: #2dd4bf; --landing-accent-soft: rgba(96, 165, 250, 0.16); }
        body { min-height: 100vh; font-family: 'Figtree', sans-serif; background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.15), transparent 28%), radial-gradient(circle at 85% 18%, rgba(13, 148, 136, 0.16), transparent 24%), linear-gradient(180deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0)), var(--landing-bg); color: var(--landing-text); }
        .dark body { background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.18), transparent 28%), radial-gradient(circle at 85% 18%, rgba(45, 212, 191, 0.16), transparent 24%), linear-gradient(180deg, rgba(15, 23, 42, 0.4), rgba(2, 6, 23, 0)), var(--landing-bg); }
        .landing-surface { background: var(--landing-surface); border: 1px solid var(--landing-border); box-shadow: var(--landing-shadow); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); }
        .landing-section-grid { background-image: linear-gradient(to right, rgba(148, 163, 184, 0.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(148, 163, 184, 0.08) 1px, transparent 1px); background-size: 34px 34px; }
        .landing-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.625rem; border-radius: 999px; padding: 0.9rem 1.35rem; font-size: 0.95rem; font-weight: 700; line-height: 1; transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease; }
        .landing-btn:hover { transform: translateY(-1px); }
        .landing-btn:active { transform: translateY(0); }
        .landing-btn--primary { background: linear-gradient(135deg, #1d4ed8 0%, #0f766e 100%); color: #ffffff; box-shadow: 0 18px 34px -20px rgba(29, 78, 216, 0.75); }
        .landing-btn--secondary { background: rgba(15, 23, 42, 0.04); color: var(--landing-text); border: 1px solid var(--landing-border); }
        .dark .landing-btn--secondary { background: rgba(148, 163, 184, 0.08); }
        .landing-btn--ghost { background: transparent; color: var(--landing-text); border: 1px dashed rgba(29, 78, 216, 0.32); }
        .landing-chip { display: inline-flex; align-items: center; gap: 0.55rem; border-radius: 999px; border: 1px solid rgba(29, 78, 216, 0.16); background: rgba(255, 255, 255, 0.72); padding: 0.7rem 0.95rem; font-size: 0.875rem; font-weight: 600; color: var(--landing-muted); }
        .dark .landing-chip { background: rgba(15, 23, 42, 0.76); }
        .landing-kicker { display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 999px; background: rgba(29, 78, 216, 0.12); padding: 0.42rem 0.8rem; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--landing-accent); }
        .dark .landing-kicker { background: rgba(96, 165, 250, 0.16); }
        .landing-card-hover { transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease; }
        .landing-card-hover:hover { transform: translateY(-4px); box-shadow: 0 28px 72px -48px rgba(15, 23, 42, 0.7); border-color: rgba(29, 78, 216, 0.24); }
        .dark .landing-card-hover:hover { box-shadow: 0 28px 72px -48px rgba(2, 6, 23, 0.95); border-color: rgba(96, 165, 250, 0.28); }
        .mini-window, .preview-canvas { position: relative; overflow: hidden; border-radius: 1.55rem; border: 1px solid rgba(148, 163, 184, 0.24); background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.12), transparent 34%), linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(248, 250, 252, 0.98)); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.68), var(--landing-shadow); transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease; }
        .mini-window::after, .preview-canvas::after { content: ''; position: absolute; inset: 0; border-radius: inherit; border: 1px solid rgba(255, 255, 255, 0.42); pointer-events: none; }
        .dark .mini-window, .dark .preview-canvas { background: radial-gradient(circle at top right, rgba(96, 165, 250, 0.16), transparent 36%), linear-gradient(180deg, rgba(15, 23, 42, 0.94), rgba(15, 23, 42, 0.82)); box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.08), var(--landing-shadow); }
        .mini-window__toolbar, .preview-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; border-bottom: 1px solid var(--landing-border); background: linear-gradient(180deg, rgba(255, 255, 255, 0.78), rgba(255, 255, 255, 0.56)); padding: 0.9rem 1.05rem; }
        .dark .mini-window__toolbar, .dark .preview-toolbar { background: linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)); }
        .mini-window__dots, .preview-dots { display: inline-flex; gap: 0.35rem; }
        .mini-window__dots span, .preview-dots span { height: 0.55rem; width: 0.55rem; border-radius: 999px; background: rgba(148, 163, 184, 0.58); }
        .mini-window__body, .preview-canvas__body { display: grid; gap: 0.8rem; padding: 0.95rem; }
        .mini-window__stat, .preview-stat, .preview-list-row { border-radius: 1rem; border: 1px solid rgba(148, 163, 184, 0.16); background: linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(255, 255, 255, 0.7)); padding: 0.85rem 0.95rem; box-shadow: 0 18px 30px -28px rgba(15, 23, 42, 0.32); }
        .dark .mini-window__stat, .dark .preview-stat, .dark .preview-list-row { background: linear-gradient(180deg, rgba(30, 41, 59, 0.8), rgba(15, 23, 42, 0.72)); box-shadow: 0 18px 30px -28px rgba(2, 6, 23, 0.72); }
        .mini-window__stat strong, .preview-stat strong { display: block; font-size: 0.98rem; color: var(--landing-text); }
        .mini-window__stat span, .preview-stat span, .preview-caption { display: block; margin-top: 0.2rem; font-size: 0.78rem; color: var(--landing-muted-soft); }
        .preview-metric-grid { display: grid; gap: 0.75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .preview-chart { height: 8rem; border-radius: 1rem; border: 1px solid rgba(148, 163, 184, 0.16); background: linear-gradient(180deg, rgba(29, 78, 216, 0.1), rgba(29, 78, 216, 0)), rgba(255, 255, 255, 0.8); padding: 0.85rem; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.42); }
        .dark .preview-chart { background: linear-gradient(180deg, rgba(96, 165, 250, 0.16), rgba(96, 165, 250, 0)), rgba(30, 41, 59, 0.78); box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.08); }
        .preview-chart__bars { display: flex; height: 100%; align-items: end; gap: 0.55rem; }
        .preview-chart__bars span { flex: 1 1 0; border-radius: 999px 999px 0.55rem 0.55rem; background: linear-gradient(180deg, rgba(29, 78, 216, 0.88), rgba(13, 148, 136, 0.78)); }
        .preview-pill { display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 999px; border: 1px solid rgba(29, 78, 216, 0.12); background: rgba(29, 78, 216, 0.08); padding: 0.36rem 0.68rem; font-size: 0.7rem; font-weight: 700; color: var(--landing-accent); }
        .dark .preview-pill { border-color: rgba(96, 165, 250, 0.18); background: rgba(96, 165, 250, 0.12); }
        .landing-card-hover:hover .preview-canvas { transform: translateY(-2px) scale(1.012); border-color: rgba(29, 78, 216, 0.2); box-shadow: 0 28px 56px -42px rgba(15, 23, 42, 0.42); }
        .dark .landing-card-hover:hover .preview-canvas { border-color: rgba(96, 165, 250, 0.24); box-shadow: 0 28px 56px -42px rgba(2, 6, 23, 0.88); }
        .workflow-grid { position: relative; }
        @media (min-width: 1024px) { .workflow-grid::before { content: ''; position: absolute; top: 3rem; left: 6%; right: 6%; height: 1px; background: linear-gradient(90deg, rgba(29, 78, 216, 0), rgba(29, 78, 216, 0.28), rgba(13, 148, 136, 0.28), rgba(29, 78, 216, 0)); } }
        .workflow-step-index { display: inline-flex; height: 2.8rem; width: 2.8rem; align-items: center; justify-content: center; border-radius: 999px; background: linear-gradient(135deg, #1d4ed8 0%, #0f766e 100%); color: #ffffff; font-weight: 800; box-shadow: 0 14px 28px -18px rgba(29, 78, 216, 0.72); }
        .theme-icon-light { display: none; }
        .dark .theme-icon-light { display: inline-flex; }
        .dark .theme-icon-dark { display: none; }
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.8s ease, transform 0.8s ease; }
        .reveal.is-visible { opacity: 1; transform: translateY(0); }
        .delay-100 { transition-delay: 0.08s; }
        .delay-200 { transition-delay: 0.16s; }
        #site-nav.is-scrolled .landing-surface { background: var(--landing-surface-strong); box-shadow: 0 24px 50px -38px rgba(15, 23, 42, 0.72); }
        #scroll-progress { transform-origin: left center; transform: scaleX(0); }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } *, *::before, *::after { animation: none !important; transition-duration: 0.01ms !important; transition-delay: 0ms !important; scroll-behavior: auto !important; } .reveal { opacity: 1; transform: none; } #scroll-progress { transition: none !important; } }
    </style>
</head>
<body class="antialiased">
    @include('shared.library-branding.bootstrap')

    <div id="scroll-progress" class="fixed left-0 top-0 z-[70] h-1 w-full bg-gradient-to-r from-blue-600 via-sky-500 to-teal-500"></div>

    @include('landing.partials.nav', ['brandName' => $brandName])

    <main>
        @include('landing.partials.hero', ['heroProofs' => $heroProofs])
        @include('landing.partials.problem-solution', ['problems' => $problems])
        @include('landing.partials.features', ['features' => $features])
        @include('landing.partials.roles', ['roles' => $roles])
        @include('landing.partials.workflow', ['workflowSteps' => $workflowSteps])
        @include('landing.partials.previews', ['previewCards' => $previewCards])
        @include('landing.partials.security', ['securityPoints' => $securityPoints])
        @include('landing.partials.why-choose', ['uspPoints' => $uspPoints])
        @include('landing.partials.use-cases', ['useCases' => $useCases])
        @include('landing.partials.final-cta')
    </main>

    @include('landing.partials.footer', ['brandName' => $brandName, 'contactEmail' => $contactEmail])
    <script>
        (() => {
            const root = document.documentElement;
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const nav = document.getElementById('site-nav');
            const progress = document.getElementById('scroll-progress');
            const navToggle = document.querySelector('[data-nav-toggle]');
            const navPanel = document.querySelector('[data-nav-panel]');
            const themeButtons = document.querySelectorAll('[data-theme-toggle]');

            const setTheme = (isDark) => {
                root.classList.toggle('dark', isDark);
                try { localStorage.setItem('theme', isDark ? 'dark-theme' : 'light-theme'); } catch (error) {}
            };

            themeButtons.forEach((button) => button.addEventListener('click', () => setTheme(!root.classList.contains('dark'))));

            const toggleNav = (forceOpen = null) => {
                if (!navPanel || !navToggle) return;
                const isOpen = forceOpen === null ? navPanel.classList.contains('hidden') : forceOpen;
                navPanel.classList.toggle('hidden', !isOpen);
                navToggle.setAttribute('aria-expanded', String(isOpen));
            };

            navToggle?.addEventListener('click', () => toggleNav(navPanel?.classList.contains('hidden')));
            document.querySelectorAll('[data-nav-panel] a').forEach((link) => link.addEventListener('click', () => toggleNav(false)));

            const updateProgress = () => {
                const scrollTop = window.scrollY;
                const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                const ratio = scrollHeight > 0 ? scrollTop / scrollHeight : 0;
                progress.style.transform = `scaleX(${Math.min(1, Math.max(0, ratio))})`;
                nav?.classList.toggle('is-scrolled', scrollTop > 32);
            };

            updateProgress();
            window.addEventListener('scroll', updateProgress, { passive: true });

            const revealElements = document.querySelectorAll('.reveal');
            if (prefersReducedMotion) {
                revealElements.forEach((element) => element.classList.add('is-visible'));
            } else {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.16 });
                revealElements.forEach((element) => observer.observe(element));
            }

        })();
    </script>
</body>
</html>
