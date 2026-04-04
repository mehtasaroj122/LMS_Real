<section id="why-us" class="py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-landing.section-heading eyebrow="Why Choose Us" title="Designed around the strengths of this Library Management System" copy="The value proposition here is not generic software language. It is grounded in the role separation, automation, and controls that already exist in the app." class="reveal" />

        <div class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($uspPoints as $point)
                <article class="landing-surface landing-card-hover reveal rounded-[1.75rem] p-7 {{ $loop->iteration > 2 ? 'delay-100' : '' }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                        <x-landing.icon :name="$point['icon']" class="h-5 w-5" />
                    </div>

                    <h3 class="mt-5 text-lg font-black text-slate-950 dark:text-slate-50">{{ $point['title'] }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $point['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
