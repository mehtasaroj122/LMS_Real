<section id="about" class="relative py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="Problem to Solution" title="Disconnected library workflows create delays. This platform brings them together." copy="Manual tracking and fragmented tools make it harder to trust inventory, request progress, and fine records. This system centralizes the operational flow so each role can work with better visibility." class="reveal" />

        <div class="mt-14 grid gap-6 lg:grid-cols-2">
            <article class="landing-surface landing-card-hover reveal rounded-[2rem] p-8">
                <div class="inline-flex items-center gap-3 rounded-full bg-red-500/10 px-4 py-2 text-sm font-bold text-red-700 dark:bg-red-500/15 dark:text-red-300">
                    <x-landing.icon name="close" class="h-4 w-4" />
                    Common Problems
                </div>

                <ul class="mt-6 space-y-4">
                    @foreach ($problems as $problem)
                        <li class="flex items-start gap-3 rounded-2xl border border-slate-200/70 bg-white/70 px-4 py-4 text-sm font-medium leading-6 text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                            <span class="mt-1 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-red-500"></span>
                            <span>{{ $problem }}</span>
                        </li>
                    @endforeach
                </ul>
            </article>

            <article class="landing-surface landing-card-hover reveal delay-100 rounded-[2rem] p-8">
                <div class="inline-flex items-center gap-3 rounded-full bg-emerald-500/10 px-4 py-2 text-sm font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                    <x-landing.icon name="automation" class="h-4 w-4" />
                    Centralized Solution
                </div>

                <div class="mt-6 rounded-[1.75rem] border border-slate-200/80 bg-gradient-to-br from-blue-500/10 via-white to-teal-500/10 p-6 dark:border-slate-700 dark:from-blue-500/15 dark:via-slate-900 dark:to-teal-500/15">
                    <h3 class="text-2xl font-black text-slate-950 dark:text-slate-50">One platform for library operations, user access, and accountability.</h3>
                    <p class="mt-4 text-base leading-7 text-slate-600 dark:text-slate-300">
                        The system connects catalog control, request management, circulation, fine handling, and role-based portals so institutions can operate with fewer gaps and better trust.
                    </p>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200/70 bg-white/75 p-4 dark:border-slate-700 dark:bg-slate-900/65"><p class="text-sm font-bold text-slate-950 dark:text-slate-50">Automation</p><p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Fine rules, reminders, and request updates reduce manual follow-up.</p></div>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/75 p-4 dark:border-slate-700 dark:bg-slate-900/65"><p class="text-sm font-bold text-slate-950 dark:text-slate-50">Tracking</p><p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Requests, issued books, returns, and account activity stay visible.</p></div>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/75 p-4 dark:border-slate-700 dark:bg-slate-900/65"><p class="text-sm font-bold text-slate-950 dark:text-slate-50">Access</p><p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Admins, staff, and students each get the right interface and permissions.</p></div>
                </div>
            </article>
        </div>
    </div>
</section>
