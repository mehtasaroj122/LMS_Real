<x-layouts.auth
    title="Register"
    eyebrow="Create your account"
    heading="Join the library network"
>
    @php
        $registerFieldNames = ['name', 'email', 'password', 'password_confirmation'];
        $registerServerErrors = [];

        foreach ($registerFieldNames as $registerFieldName) {
            $message = trim((string) $errors->first($registerFieldName));

            if ($message !== '') {
                $registerServerErrors[$registerFieldName] = $message;
            }
        }
    @endphp

    <x-auth-card>
        <div class="space-y-5">
            @if (session('status'))
                <x-auth-alert variant="success" :message="session('status')" />
            @endif

            <form
                method="POST"
                action="{{ route('register') }}"
                class="space-y-6"
                id="register-form"
                data-email-check-url="{{ route('register.validate-field') }}"
                data-server-errors='@json($registerServerErrors)'
            >
                @csrf

                <div class="space-y-5">
                    <x-input-floating
                        name="name"
                        label="Full name"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <x-input-floating
                        name="email"
                        label="Email address"
                        type="email"
                        required
                        autocomplete="email"
                        autocapitalize="none"
                        spellcheck="false"
                    />

                    <x-input-password
                        name="password"
                        label="Password"
                        required
                        autocomplete="new-password"
                        hint="Use at least 8 characters with a mix of letters and numbers."
                    />

                    <x-input-password
                        name="password_confirmation"
                        label="Confirm password"
                        required
                        autocomplete="new-password"
                    />
                </div>

                <x-button-primary text="Create Account" icon="user-plus" id="register-submit-button" />

                <p class="text-center text-sm text-slate-600 dark:text-slate-400">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-semibold text-sky-700 underline-offset-4 transition hover:text-sky-800 hover:underline focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sky-500/20 dark:text-sky-300 dark:hover:text-sky-200">
                        Sign in
                    </a>
                </p>
            </form>
        </div>
    </x-auth-card>

    <script>
        (() => {
            const form = document.getElementById('register-form');

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const emailCheckUrl = form.dataset.emailCheckUrl ?? '';
            const submitButton = document.getElementById('register-submit-button');
            const serverErrors = JSON.parse(form.dataset.serverErrors ?? '{}');
            const touchedFields = new Set();
            const fieldStates = {};
            const emailState = {
                timer: null,
                controller: null,
                verifiedValue: '',
            };

            const icons = {
                valid: `
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 10.5 8 14.5 16 6.5" />
                    </svg>
                `,
                invalid: `
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="10" cy="10" r="7.5" />
                        <path d="M10 6.5v4.5" />
                        <path d="M10 13.75h.01" />
                    </svg>
                `,
                pending: `
                    <svg class="animate-spin" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 2.5a7.5 7.5 0 1 1-5.3 2.2" />
                        <path d="M4.5 2.75H10v5.5" />
                    </svg>
                `,
            };

            const fields = ['name', 'email', 'password', 'password_confirmation'];

            const getParts = (fieldName) => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                const group = input?.closest('[data-auth-field-group]');
                const field = group?.querySelector('[data-auth-field]');
                const error = group?.querySelector('[data-auth-error]');
                const hint = group?.querySelector('[data-auth-hint]');
                const icon = group?.querySelector('[data-auth-icon]');

                return { input, group, field, error, hint, icon };
            };

            const setFieldMessage = (fieldName, message) => {
                const { input, error, hint } = getParts(fieldName);

                if (error instanceof HTMLElement) {
                    const showError = message !== '';
                    error.textContent = message;
                    error.hidden = !showError;
                    error.classList.toggle('is-visible', showError);
                }

                if (hint instanceof HTMLElement) {
                    hint.classList.toggle('is-hidden', message !== '');
                }

                if (input instanceof HTMLInputElement) {
                    input.setAttribute('aria-invalid', message !== '' ? 'true' : 'false');
                }
            };

            const setFieldState = (fieldName, state, message = '') => {
                const { field, icon } = getParts(fieldName);

                field?.classList.remove('is-valid', 'is-invalid', 'is-error', 'is-pending');

                if (state === 'valid') {
                    field?.classList.add('is-valid');
                } else if (state === 'invalid') {
                    field?.classList.add('is-invalid');
                } else if (state === 'pending') {
                    field?.classList.add('is-pending');
                }

                if (icon instanceof HTMLElement) {
                    icon.innerHTML = state === 'valid'
                        ? icons.valid
                        : state === 'invalid'
                            ? icons.invalid
                            : state === 'pending'
                                ? icons.pending
                                : '';
                }

                setFieldMessage(fieldName, state === 'invalid' ? message : '');
                fieldStates[fieldName] = state;
            };

            const clearFieldState = (fieldName) => {
                setFieldState(fieldName, 'idle');
                delete fieldStates[fieldName];
            };

            const normalizeName = (value) => value.replace(/\s+/g, ' ').trim();
            const normalizeEmail = (value) => value.trim().toLowerCase();

            const getValues = () => {
                const name = getParts('name').input?.value ?? '';
                const email = getParts('email').input?.value ?? '';
                const password = getParts('password').input?.value ?? '';
                const passwordConfirmation = getParts('password_confirmation').input?.value ?? '';

                return {
                    name: normalizeName(name),
                    email: normalizeEmail(email),
                    password,
                    password_confirmation: passwordConfirmation,
                };
            };

            const syncNormalizedValues = () => {
                const values = getValues();
                const nameInput = getParts('name').input;
                const emailInput = getParts('email').input;

                if (nameInput instanceof HTMLInputElement) {
                    nameInput.value = values.name;
                }

                if (emailInput instanceof HTMLInputElement) {
                    emailInput.value = values.email;
                }

                return values;
            };

            const validateSync = (fieldName) => {
                const values = syncNormalizedValues();

                switch (fieldName) {
                    case 'name':
                        if (!values.name) return 'Please enter your full name.';
                        return '';
                    case 'email':
                        if (!values.email) return 'Please enter your email address.';
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(values.email)) return 'Please enter a valid email address.';
                        return '';
                    case 'password':
                        if (!values.password) return 'Please enter a password.';
                        if (values.password.length < 8) return 'Password must be at least 8 characters.';
                        return '';
                    case 'password_confirmation':
                        if (!values.password_confirmation) return 'Please confirm your password.';
                        if (values.password_confirmation !== values.password) return 'Passwords do not match.';
                        return '';
                    default:
                        return '';
                }
            };

            const focusField = (fieldName) => {
                const input = getParts(fieldName).input;
                input?.focus();
            };

            const updateSubmitState = () => {
                if (!(submitButton instanceof HTMLButtonElement)) {
                    return;
                }

                const hasPending = Object.values(fieldStates).includes('pending');
                submitButton.disabled = hasPending;
                submitButton.setAttribute('aria-busy', hasPending ? 'true' : 'false');
            };

            const runEmailAvailabilityCheck = async ({ force = false } = {}) => {
                const values = syncNormalizedValues();
                const localError = validateSync('email');

                if (localError !== '') {
                    emailState.controller?.abort();
                    emailState.verifiedValue = '';
                    setFieldState('email', 'invalid', localError);
                    updateSubmitState();
                    return false;
                }

                if (!force && emailState.verifiedValue === values.email) {
                    setFieldState('email', 'valid');
                    updateSubmitState();
                    return true;
                }

                emailState.controller?.abort();
                emailState.controller = new AbortController();
                setFieldState('email', 'pending');
                updateSubmitState();

                try {
                    const response = await fetch(emailCheckUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        signal: emailState.controller.signal,
                        body: JSON.stringify({
                            field: 'email',
                            email: values.email,
                        }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok || data.valid !== true) {
                        emailState.verifiedValue = '';
                        setFieldState('email', 'invalid', data.message || 'This email has already been taken.');
                        updateSubmitState();
                        return false;
                    }

                    emailState.verifiedValue = values.email;
                    setFieldState('email', 'valid');
                    updateSubmitState();
                    return true;
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return false;
                    }

                    emailState.verifiedValue = '';
                    clearFieldState('email');
                    updateSubmitState();
                    return true;
                }
            };

            const validateField = (fieldName, { forceEmailCheck = false } = {}) => {
                const syncError = validateSync(fieldName);

                if (syncError !== '') {
                    if (fieldName === 'email') {
                        emailState.controller?.abort();
                        emailState.verifiedValue = '';
                    }

                    setFieldState(fieldName, 'invalid', syncError);
                    updateSubmitState();
                    return Promise.resolve(false);
                }

                if (fieldName === 'email') {
                    return runEmailAvailabilityCheck({ force: forceEmailCheck });
                }

                setFieldState(fieldName, 'valid');
                updateSubmitState();
                return Promise.resolve(true);
            };

            const scheduleEmailValidation = () => {
                window.clearTimeout(emailState.timer);
                emailState.timer = window.setTimeout(() => {
                    if ((getParts('email').input?.value ?? '').trim() === '') {
                        return;
                    }

                    void validateField('email', { forceEmailCheck: true });
                }, 350);
            };

            fields.forEach((fieldName) => {
                const input = getParts(fieldName).input;

                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                if ((serverErrors[fieldName] ?? '').trim() !== '') {
                    setFieldState(fieldName, 'invalid', serverErrors[fieldName].trim());
                    touchedFields.add(fieldName);
                }

                input.addEventListener('input', () => {
                    if (fieldName === 'name') {
                        input.value = input.value.replace(/\s{2,}/g, ' ');
                    }

                    if (fieldName === 'email') {
                        emailState.verifiedValue = '';
                    }

                    const shouldValidateNow = touchedFields.has(fieldName) || input.value.trim() !== '';

                    if (!shouldValidateNow) {
                        clearFieldState(fieldName);
                        updateSubmitState();
                        return;
                    }

                    if (fieldName === 'email') {
                        const syncError = validateSync('email');

                        if (syncError !== '') {
                            emailState.controller?.abort();
                            setFieldState('email', 'invalid', syncError);
                        } else {
                            setFieldState('email', 'pending');
                            scheduleEmailValidation();
                        }
                    } else {
                        void validateField(fieldName);
                    }

                    if (fieldName === 'password' || fieldName === 'password_confirmation') {
                        const confirmationInput = getParts('password_confirmation').input;

                        if ((confirmationInput?.value ?? '').trim() !== '' || touchedFields.has('password_confirmation')) {
                            void validateField('password_confirmation');
                        }
                    }
                });

                input.addEventListener('blur', () => {
                    touchedFields.add(fieldName);

                    if (fieldName === 'email') {
                        void validateField('email', { forceEmailCheck: true });
                        return;
                    }

                    void validateField(fieldName);
                });
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                fields.forEach((fieldName) => touchedFields.add(fieldName));

                const results = await Promise.all([
                    validateField('name'),
                    validateField('email', { forceEmailCheck: true }),
                    validateField('password'),
                    validateField('password_confirmation'),
                ]);

                if (results.every(Boolean)) {
                    if (submitButton instanceof HTMLButtonElement) {
                        submitButton.disabled = true;
                        submitButton.setAttribute('aria-busy', 'true');
                    }

                    form.submit();
                    return;
                }

                const firstInvalidField = fields.find((fieldName) => fieldStates[fieldName] === 'invalid') ?? 'name';
                focusField(firstInvalidField);
            });

            updateSubmitState();
        })();
    </script>
</x-layouts.auth>
