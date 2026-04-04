<x-layouts.auth
    title="Reset Password"
    eyebrow="Set a new password"
    heading="Choose a fresh password"
    subheading="Use the reset link from your email to secure your account and get back into the system."
>
    <x-auth-card>
        <div class="space-y-5">
            @if (session('status'))
                <x-auth-alert variant="success" :message="session('status')" />
            @endif

            @if ($errors->any())
                <x-auth-alert variant="danger" :message="$errors->first()" />
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="space-y-5">
                    <x-input-floating
                        name="email"
                        label="Email address"
                        type="email"
                        :value="old('email', $request->email)"
                        required
                        autofocus
                        autocomplete="email"
                        autocapitalize="none"
                        spellcheck="false"
                    />

                    <x-input-password
                        name="password"
                        label="New password"
                        required
                        autocomplete="new-password"
                    />

                    <x-input-password
                        name="password_confirmation"
                        label="Confirm password"
                        required
                        autocomplete="new-password"
                    />
                </div>

                {{-- <div class="px-4 py-3 text-sm border rounded-2xl border-slate-200/80 bg-slate-50/80 text-slate-600 dark:border-slate-800 dark:bg-slate-950/50 dark:text-slate-300">
                    Choose something unique that you do not reuse on other services.
                </div> --}}

                <x-button-primary text="Reset Password" loading="submitting" loading-text="Resetting password..." icon="check" />

                <p class="text-sm text-center text-slate-600 dark:text-slate-400">
                    Need to return?
                    <a href="{{ route('login') }}" class="font-semibold transition text-sky-700 underline-offset-4 hover:text-sky-800 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:text-sky-300 dark:hover:text-sky-200">
                        Back to login
                    </a>
                </p>
            </form>
        </div>
    </x-auth-card>
</x-layouts.auth>
