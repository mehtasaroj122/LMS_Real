<x-layouts.auth
    title="Confirm Password"
    eyebrow="Security check"
    heading="Confirm your password"
    subheading="This is a protected area. Please re-enter your password before continuing."
>
    <x-auth-card>
        <div class="space-y-5">
            <x-auth-alert variant="info" :message="__('This is a secure area of the application. Please confirm your password before continuing.')" />

            @if ($errors->any())
                <x-auth-alert variant="danger" :message="$errors->first()" />
            @endif

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <x-input-password
                    name="password"
                    label="Password"
                    required
                    autofocus
                    autocomplete="current-password"
                />

                <x-button-primary text="Confirm Password" loading="submitting" loading-text="Confirming..." icon="shield" />
            </form>
        </div>
    </x-auth-card>
</x-layouts.auth>
