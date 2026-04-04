<section id="home" class="relative overflow-hidden pt-32 sm:pt-36">
    <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-64 bg-gradient-to-b from-blue-500/10 via-transparent to-transparent"></div>
    <div class="pointer-events-none absolute left-[-8rem] top-24 -z-10 h-72 w-72 rounded-full bg-blue-500/10 blur-3xl"></div>
    <div class="pointer-events-none absolute right-[-6rem] top-16 -z-10 h-80 w-80 rounded-full bg-teal-500/10 blur-3xl"></div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-14 lg:grid-cols-[1.08fr_minmax(0,0.92fr)]">
            <div class="reveal">
                <span class="landing-kicker">Institution-First Library Operations</span>

                <h1 class="mt-6 max-w-4xl text-4xl font-black tracking-tight text-slate-950 dark:text-slate-50 sm:text-5xl lg:text-6xl xl:text-7xl">
                    Manage Books, Students, Requests, and Fines in One Secure System
                </h1>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600 dark:text-slate-300 sm:text-xl">
                    A complete Library Management System designed for institutions to streamline operations, empower staff, and give students a better library experience.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('login') }}" class="landing-btn landing-btn--primary">
                        Login to Your Library
                        <x-landing.icon name="arrow-right" class="h-4 w-4" />
                    </a>

                    <a href="{{ route('login') }}" title="Admin and staff accounts are typically provisioned by the institution." class="landing-btn landing-btn--secondary">
                        Setup Your Library (Admin)
                    </a>

                    <a href="{{ route('register') }}" class="landing-btn landing-btn--ghost">
                        Student? Join Here
                    </a>
                </div>

                <p class="mt-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                    Admin and staff accounts are usually created by the institution. Student signup remains available through the verified student flow.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @foreach ($heroProofs as $proof)
                        <span class="landing-chip">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-gradient-to-r from-blue-600 to-teal-500"></span>
                            {{ $proof }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="reveal delay-100">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="mini-window sm:col-span-2">
                        <div class="mini-window__toolbar">
                            <div class="mini-window__dots"><span></span><span></span><span></span></div>
                            <span class="preview-pill">Admin Command Center</span>
                        </div>
                        <div class="mini-window__body">
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="mini-window__stat"><strong>Books & Categories</strong><span>Catalog coverage and availability visibility</span></div>
                                <div class="mini-window__stat"><strong>Students & Roles</strong><span>Status changes, privileges, and account controls</span></div>
                                <div class="mini-window__stat"><strong>Reports & Fines</strong><span>Operational monitoring with oversight metrics</span></div>
                            </div>
                            <div class="rounded-2xl border border-slate-200/70 bg-gradient-to-r from-blue-500/10 via-white to-teal-500/10 px-4 py-4 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:from-blue-500/15 dark:via-slate-900 dark:to-teal-500/15 dark:text-slate-200">
                                Admins manage policies, dashboards, reports, and security controls from one oversight layer.
                            </div>
                        </div>
                    </div>

                    <div class="mini-window">
                        <div class="mini-window__toolbar">
                            <div class="mini-window__dots"><span></span><span></span><span></span></div>
                            <span class="preview-pill">Staff Workspace</span>
                        </div>
                        <div class="mini-window__body">
                            <div class="mini-window__stat"><strong>Requests Queue</strong><span>Approve, reject, issue, or return with clarity</span></div>
                            <div class="mini-window__stat"><strong>Fine Handling</strong><span>Mark paid, waive, and notify students faster</span></div>
                        </div>
                    </div>

                    <div class="mini-window">
                        <div class="mini-window__toolbar">
                            <div class="mini-window__dots"><span></span><span></span><span></span></div>
                            <span class="preview-pill">Student Portal</span>
                        </div>
                        <div class="mini-window__body">
                            <div class="mini-window__stat"><strong>Search & Request</strong><span>Find books, request access, and monitor status</span></div>
                            <div class="mini-window__stat"><strong>My Books & Fines</strong><span>Track due dates, fines, and notifications in one place</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
