<section id="features" class="landing-section-grid py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="Core Features" title="Built around the workflows real institutions need every day" copy="The page now focuses on practical outcomes instead of unsupported claims. Each module maps directly to the product already present in the application." class="reveal" />

        <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($features as $feature)
                <article class="landing-surface landing-card-hover reveal rounded-[1.75rem] p-7 {{ $loop->iteration > 3 ? 'delay-100' : '' }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/10 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300">
                        <x-landing.icon :name="$feature['icon']" class="h-5 w-5" />
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-950 dark:text-slate-50">{{ $feature['title'] }}</h3>

                    <p class="mt-4 text-sm leading-7 text-slate-600 dark:text-slate-300">
                        {{ $feature['description'] }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
