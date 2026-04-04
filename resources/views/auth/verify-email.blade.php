<x-layouts.auth
    title="Verify Email"
    eyebrow="Almost there"
    heading="Verify your email address"
    subheading="We need to confirm your email before you can continue using your account."
>
    <x-auth-card>
        <div class="space-y-5">
            <x-auth-alert
                variant="info"
                :message="__('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.')"
            />

            @if (session('status') === 'verification-link-sent')
                <x-auth-alert
                    variant="success"
                    :message="__('A new verification link has been sent to the email address you provided during registration.')"
                />
            @endif

            <div class="flex flex-col gap-3 sm:flex-row">
                <form method="POST" action="{{ route('verification.send') }}" class="flex-1" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf

                    <x-button-primary
                        text="Resend Verification Email"
                        loading="submitting"
                        loading-text="Sending verification email..."
                        icon="mail"
                    />
                </form>

                <form method="POST" action="{{ route('logout') }}" class="sm:w-auto">
                    @csrf

                    <button
                        type="submit"
                        class="inline-flex h-14 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-700 transition duration-200 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-200 dark:hover:bg-slate-900"
                    >
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </x-auth-card>
</x-layouts.auth>
