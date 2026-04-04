<footer id="contact" class="border-t border-slate-200/70 py-14 dark:border-slate-800">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_repeat(3,minmax(0,1fr))]">
            <div>
                <div class="flex items-center gap-3">
                    <x-logo size="sm" :lazy="false" />
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.22em] text-slate-500 dark:text-slate-400">Library Platform</p>
                        <p class="text-lg font-black text-slate-950 dark:text-slate-50">{{ $brandName }}</p>
                    </div>
                </div>

                <p class="mt-5 max-w-md text-sm leading-7 text-slate-600 dark:text-slate-300">
                    Secure, role-based library software for institutions that need clearer operations, better student experience, and stronger accountability.
                </p>
            </div>

            <div>
                <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Explore</h3>
                <ul class="mt-5 space-y-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                    <li><a href="#about" class="transition hover:text-blue-700 dark:hover:text-blue-300">About</a></li>
                    <li><a href="#features" class="transition hover:text-blue-700 dark:hover:text-blue-300">Features</a></li>
                    <li><a href="#why-us" class="transition hover:text-blue-700 dark:hover:text-blue-300">Why Choose Us</a></li>
                    <li><a href="#use-cases" class="transition hover:text-blue-700 dark:hover:text-blue-300">Use Cases</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Account</h3>
                <ul class="mt-5 space-y-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                    <li><a href="{{ route('login') }}" class="transition hover:text-blue-700 dark:hover:text-blue-300">Login</a></li>
                    <li><a href="{{ route('register') }}" class="transition hover:text-blue-700 dark:hover:text-blue-300">Student Registration</a></li>
                    <li><a href="#privacy" class="transition hover:text-blue-700 dark:hover:text-blue-300">Privacy Policy</a></li>
                    <li><a href="#final-cta" class="transition hover:text-blue-700 dark:hover:text-blue-300">Get Started</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Contact</h3>
                <div class="mt-5 space-y-4 text-sm text-slate-600 dark:text-slate-300">
                    <p>
                        Email:
                        <a href="mailto:{{ $contactEmail }}" class="font-semibold text-slate-950 transition hover:text-blue-700 dark:text-slate-50 dark:hover:text-blue-300">
                            {{ $contactEmail }}
                        </a>
                    </p>
                    <p>Version 4.0</p>
                    <p>Created by Saroj Mehta</p>
                </div>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-3 border-t border-slate-200/70 pt-6 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <p>© 2026 {{ $brandName }}. All rights reserved.</p>
            <p>Institution-first onboarding, student-friendly access, and operational clarity.</p>
        </div>
    </div>
</footer>
