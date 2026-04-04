<x-layouts.auth
    title="Account Inactive"
    eyebrow="Access restricted"
    heading="Account inactive"
>
    <x-auth-card>
        <div class="space-y-5">
            <x-auth-alert
                variant="warning"
                message="This account is inactive. Contact the library administrator if you need access restored."
            />

            <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-300">
                Need help?
                <a href="mailto:admin@librarysystem.com" class="font-medium text-sky-700 hover:underline dark:text-sky-300">
                    admin@librarysystem.com
                </a>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a
                    href="{{ route('login') }}"
                    class="inline-flex h-14 flex-1 items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-700 transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-200 dark:hover:bg-slate-900"
                >
                    Back to login
                </a>

                <a
                    href="{{ route('register') }}"
                    class="inline-flex h-14 flex-1 items-center justify-center rounded-2xl bg-slate-950 px-6 text-sm font-semibold text-white shadow-lg shadow-slate-950/20 transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400"
                >
                    Create a new account
                </a>
            </div>
        </div>
    </x-auth-card>
</x-layouts.auth>
