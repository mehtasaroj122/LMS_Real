<section id="use-cases" class="py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="Use Cases" title="Flexible enough for different institutional library setups" copy="This section uses grounded scenarios instead of invented testimonials, so the page stays credible while still showing where the system fits." class="reveal" />

        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @foreach ($useCases as $useCase)
                <article class="landing-surface landing-card-hover reveal rounded-[1.85rem] p-8 {{ $loop->iteration === 2 ? 'delay-100' : '' }} {{ $loop->iteration === 3 ? 'delay-200' : '' }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-500/10 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
                        <x-landing.icon :name="$useCase['icon']" class="h-5 w-5" />
                    </div>

                    <h3 class="mt-6 text-2xl font-black text-slate-950 dark:text-slate-50">{{ $useCase['title'] }}</h3>
                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $useCase['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
