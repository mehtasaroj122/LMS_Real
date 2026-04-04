<section id="workflow" class="py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="How It Works" title="Structured circulation from login to fine follow-up" copy="The workflow below mirrors the real request, issue, return, and notification flow found in the application." class="reveal" />

        <div class="workflow-grid mt-14 grid gap-5 lg:grid-cols-6">
            @foreach ($workflowSteps as $step)
                <article class="landing-surface landing-card-hover reveal rounded-[1.75rem] p-6 {{ $loop->iteration > 1 ? 'delay-100' : '' }}">
                    <div class="flex items-center justify-between gap-3">
                        <span class="workflow-step-index">{{ $loop->iteration }}</span>
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">
                            <x-landing.icon :name="$step['icon']" class="h-5 w-5" />
                        </div>
                    </div>

                    <h3 class="mt-6 text-lg font-black text-slate-950 dark:text-slate-50">{{ $step['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $step['description'] }}</p>
                </article>
            @endforeach
        </div>

        <div class="landing-surface reveal mt-8 rounded-[1.75rem] p-6 text-sm leading-7 text-slate-600 dark:text-slate-300">
            <strong class="text-slate-950 dark:text-slate-50">Supporting note:</strong>
            Staff can also issue books directly without a prior request for walk-in circulation, while the system continues to track due dates, returns, and fine-related notifications.
        </div>
    </div>
</section>
