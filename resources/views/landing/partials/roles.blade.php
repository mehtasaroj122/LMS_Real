<section id="roles" class="py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="Role-Based Benefits" title="One system, three focused experiences." copy="Each portal is aligned to the responsibilities already built into the application, so people see what matters to their role instead of everything at once." class="reveal" />

        <div class="mt-8 flex justify-center">
            <p class="rounded-full border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-semibold text-blue-800 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-200">
                One system, three focused experiences.
            </p>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @foreach ($roles as $role)
                <article class="landing-surface landing-card-hover reveal rounded-[1.9rem] p-8 {{ $loop->iteration === 2 ? 'delay-100' : '' }} {{ $loop->iteration === 3 ? 'delay-200' : '' }}">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-500/10 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300">
                            <x-landing.icon :name="$role['icon']" class="h-5 w-5" />
                        </div>
                        <span class="preview-pill">{{ $role['title'] }} Portal</span>
                    </div>

                    <h3 class="mt-6 text-2xl font-black text-slate-950 dark:text-slate-50">{{ $role['title'] }}</h3>
                    <p class="mt-3 text-base font-semibold text-slate-700 dark:text-slate-200">{{ $role['summary'] }}</p>

                    <ul class="mt-6 space-y-3">
                        @foreach ($role['points'] as $point)
                            <li class="flex items-start gap-3 text-sm leading-7 text-slate-600 dark:text-slate-300">
                                <span class="mt-2 inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-gradient-to-r from-blue-600 to-teal-500"></span>
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </div>
</section>
