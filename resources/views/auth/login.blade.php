@php
    $formErrorMessage = $errors->first(\App\Http\Requests\Auth\LoginRequest::FORM_ERROR_KEY);
    $emailErrorMessage = $errors->first('email');
    $friendlyFormError = $formErrorMessage === \App\Http\Requests\Auth\LoginRequest::ACCOUNT_INACTIVE_ERROR
        ? 'This account is currently inactive. Please contact the library administrator for help.'
        : $formErrorMessage;
@endphp

<x-layouts.auth
    title="Login"
    eyebrow="Secure member access"
    heading="Welcome back"
    subheading="Sign in with your registered email to manage circulation, requests, fines, and library activity from one place."
>
    <x-auth-card>
        <div class="space-y-5">
            @if (session('status'))
                <x-auth-alert variant="success" :message="session('status')" />
            @endif

            @if ($friendlyFormError)
                <x-auth-alert
                    variant="danger"
                    :message="$friendlyFormError"
                />
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <div class="space-y-5">
                    <x-input-floating
                        name="email"
                        label="Email address"
                        type="email"
                        :error="$emailErrorMessage"
                        required
                        autofocus
                        autocomplete="username"
                        autocapitalize="none"
                        spellcheck="false"
                    />

                    <x-input-password
                        name="password"
                        label="Password"
                        :error="$errors->first('password')"
                        required
                        autocomplete="current-password"
                    />
                </div>

                <div class="flex flex-col gap-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                    <label for="remember" class="inline-flex w-fit items-center gap-3 text-slate-600 dark:text-slate-300">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            value="1"
                            class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-900 dark:text-sky-400 dark:focus:ring-sky-400"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <span>Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="font-medium text-sky-700 underline-offset-4 transition hover:text-sky-800 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:text-sky-300 dark:hover:text-sky-200">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <x-button-primary text="Login" loading="submitting" loading-text="Signing in..." icon="arrow" />

                @if (Route::has('register'))
                    <p class="text-center text-sm text-slate-600 dark:text-slate-400">
                        Need an account?
                        <a href="{{ route('register') }}" class="font-semibold text-sky-700 underline-offset-4 transition hover:text-sky-800 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:text-sky-300 dark:hover:text-sky-200">
                            Sign up
                        </a>
                    </p>
                @endif
            </form>
        </div>
    </x-auth-card>
</x-layouts.auth>
