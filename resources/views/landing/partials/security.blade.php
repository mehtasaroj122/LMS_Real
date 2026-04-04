<section id="privacy" class="py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="Security & Reliability" title="Built for secure, reliable library operations" copy="This section highlights the actual protection and accountability features already visible in the application codebase and workflow." class="reveal" />

        <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($securityPoints as $point)
                <article class="landing-surface landing-card-hover reveal rounded-[1.75rem] p-7 {{ $loop->iteration > 2 ? 'delay-100' : '' }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-900/5 text-slate-900 dark:bg-white/10 dark:text-white">
                        <x-landing.icon :name="$point['icon']" class="h-5 w-5" />
                    </div>

                    <h3 class="mt-5 text-lg font-black text-slate-950 dark:text-slate-50">{{ $point['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $point['description'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="landing-surface reveal mt-8 rounded-[1.9rem] p-7">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-lg font-black text-slate-950 dark:text-slate-50">Reminder automation supports reliability after login too.</p>
                    <p class="mt-2 max-w-3xl text-sm leading-7 text-slate-600 dark:text-slate-300">
                        Queued email jobs and scheduled reminder commands help institutions follow up on overdue books and pending fines without relying on manual tracking alone.
                    </p>
                </div>
                <div class="inline-flex items-center gap-3 rounded-full bg-slate-900/5 px-4 py-3 text-sm font-semibold text-slate-700 dark:bg-white/10 dark:text-slate-200">
                    <x-landing.icon name="automation" class="h-4 w-4" />
                    Scheduled reminders + activity logs
                </div>
            </div>
        </div>
    </div>
</section>
