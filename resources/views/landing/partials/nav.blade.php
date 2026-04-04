<nav id="site-nav" class="fixed inset-x-0 top-0 z-50 transition-all duration-300">
    <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
        <div class="landing-surface flex items-center justify-between rounded-full px-4 py-3 sm:px-5">
            <a href="#home" class="flex items-center gap-3" aria-label="{{ $brandName }} home">
                <x-logo size="sm" :lazy="false" />
                <span class="max-w-[12rem] text-sm font-black leading-tight text-slate-950 dark:text-slate-50 sm:max-w-none sm:text-base">
                    {{ $brandName }}
                </span>
            </a>

            <div class="hidden items-center gap-7 lg:flex">
                <a href="#about" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950 dark:text-slate-300 dark:hover:text-slate-50">About</a>
                <a href="#features" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950 dark:text-slate-300 dark:hover:text-slate-50">Features</a>
                <a href="#roles" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950 dark:text-slate-300 dark:hover:text-slate-50">Roles</a>
                <a href="#workflow" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950 dark:text-slate-300 dark:hover:text-slate-50">Workflow</a>
                <a href="#previews" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950 dark:text-slate-300 dark:hover:text-slate-50">Preview</a>
                <a href="#privacy" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950 dark:text-slate-300 dark:hover:text-slate-50">Security</a>
                <a href="#contact" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950 dark:text-slate-300 dark:hover:text-slate-50">Contact</a>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" data-theme-toggle class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white/70 text-slate-700 transition hover:border-slate-300 hover:text-slate-950 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:text-white" aria-label="Toggle color theme">
                    <x-landing.icon name="moon" class="theme-icon-dark h-5 w-5" />
                    <x-landing.icon name="sun" class="theme-icon-light h-5 w-5" />
                </button>

                <a href="{{ route('login') }}" class="landing-btn landing-btn--primary hidden px-5 py-3 text-sm sm:inline-flex">
                    Login
                </a>

                <button type="button" data-nav-toggle aria-expanded="false" aria-controls="landing-mobile-nav" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white/70 text-slate-700 transition hover:border-slate-300 hover:text-slate-950 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-slate-600 dark:hover:text-white lg:hidden">
                    <x-landing.icon name="menu" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <div id="landing-mobile-nav" data-nav-panel class="landing-surface mt-3 hidden rounded-3xl p-4 lg:hidden">
            <div class="grid gap-2">
                <a href="#about" class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80">About</a>
                <a href="#features" class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80">Features</a>
                <a href="#roles" class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80">Roles</a>
                <a href="#workflow" class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80">Workflow</a>
                <a href="#previews" class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80">Preview</a>
                <a href="#privacy" class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80">Security</a>
                <a href="#contact" class="rounded-2xl px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800/80">Contact</a>
                <div class="mt-2 grid gap-2 sm:grid-cols-2">
                    <a href="{{ route('login') }}" class="landing-btn landing-btn--primary w-full px-5 py-3 text-sm">Login</a>
                    <a href="{{ route('register') }}" class="landing-btn landing-btn--secondary w-full px-5 py-3 text-sm">Student Registration</a>
                </div>
            </div>
        </div>
    </div>
</nav>
