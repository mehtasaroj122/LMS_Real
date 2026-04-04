<x-layouts.auth
    title="Forgot Password"
    eyebrow="Account recovery"
    heading="Reset your password"
    subheading="Enter your email address and we will send you a secure password reset link."
>
    <x-auth-card>
        <div class="space-y-5">
            @if (session('status'))
                <x-auth-alert variant="success" :message="session('status')" />
            @endif

            @if ($errors->any())
                <x-auth-alert variant="danger" :message="$errors->first()" />
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <x-input-floating
                    name="email"
                    label="Email address"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    autocapitalize="none"
                    spellcheck="false"
                />

                <x-button-primary text="Email Password Reset Link" loading="submitting" loading-text="Sending reset link..." icon="mail" />

                <p class="text-center text-sm text-slate-600 dark:text-slate-400">
                    Remembered your password?
                    <a href="{{ route('login') }}" class="font-semibold text-sky-700 underline-offset-4 transition hover:text-sky-800 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:text-sky-300 dark:hover:text-sky-200">
                        Back to login
                    </a>
                </p>
            </form>
        </div>
    </x-auth-card>
</x-layouts.auth>
