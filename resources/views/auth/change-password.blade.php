@php
    $changePasswordFieldNames = ['password', 'password_confirmation'];
    $changePasswordServerErrors = [];

    foreach ($changePasswordFieldNames as $changePasswordFieldName) {
        $message = trim((string) $errors->first($changePasswordFieldName));

        if ($message !== '') {
            $changePasswordServerErrors[$changePasswordFieldName] = $message;
        }
    }
@endphp

<x-layouts.auth
    title="Change Password"
    eyebrow="Password update"
    heading="Change password"
>
    <x-auth-card class="space-y-5">
        <div
            id="change-password-feedback"
            class="hidden rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-100"
            role="alert"
            aria-live="assertive"
        >
            <p id="change-password-feedback-text"></p>
        </div>

        <form
            method="POST"
            action="{{ route('force-password.update') }}"
            class="space-y-5"
            id="change-password-form"
            data-account-email="{{ mb_strtolower((string) $user->email) }}"
            data-server-errors='@json($changePasswordServerErrors)'
        >
            @csrf

            <p class="text-sm text-slate-600 dark:text-slate-300">
                Account:
                <span class="font-medium text-slate-800 dark:text-white">{{ $user->email }}</span>
            </p>

            <div class="space-y-3">
                <x-input-password
                    name="password"
                    label="New password"
                    required
                    autofocus
                    autocomplete="new-password"
                />

                <div class="space-y-2">
                    <x-input-password
                        name="password_confirmation"
                        label="Confirm password"
                        required
                        autocomplete="new-password"
                    />

                    <p
                        id="change-password-match"
                        class="hidden text-xs font-medium"
                        aria-live="polite"
                    ></p>
                </div>
            </div>

            <div class="grid gap-1.5 rounded-2xl border border-slate-200/80 bg-slate-50/75 px-4 py-3 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-950/45 dark:text-slate-400">
                <p class="flex items-center gap-2" data-password-rule-item="length">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600" data-password-rule-dot></span>
                    <span>8+ characters</span>
                </p>
                <p class="flex items-center gap-2" data-password-rule-item="uppercase">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600" data-password-rule-dot></span>
                    <span>One uppercase letter</span>
                </p>
                <p class="flex items-center gap-2" data-password-rule-item="lowercase">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600" data-password-rule-dot></span>
                    <span>One lowercase letter</span>
                </p>
                <p class="flex items-center gap-2" data-password-rule-item="number">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600" data-password-rule-dot></span>
                    <span>One number</span>
                </p>
                <p class="flex items-center gap-2" data-password-rule-item="symbol">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600" data-password-rule-dot></span>
                    <span>One special character</span>
                </p>
                <p class="flex items-center gap-2" data-password-rule-item="email">
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600" data-password-rule-dot></span>
                    <span>Different from your email</span>
                </p>
            </div>

            <x-button-primary text="Update Password" icon="check" id="change-password-submit" />
        </form>
    </x-auth-card>

    <script>
        (() => {
            const form = document.getElementById('change-password-form');

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const submitButton = document.getElementById('change-password-submit');
            const feedback = document.getElementById('change-password-feedback');
            const feedbackText = document.getElementById('change-password-feedback-text');
            const matchMessage = document.getElementById('change-password-match');
            const serverErrors = JSON.parse(form.dataset.serverErrors ?? '{}');
            const accountEmail = (form.dataset.accountEmail ?? '').trim().toLowerCase();
            const touchedFields = new Set();
            const fieldStates = {};
            let submitting = false;
            const submitButtonDefaultMarkup = submitButton instanceof HTMLButtonElement ? submitButton.innerHTML : '';

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
            };

            const getParts = (fieldName) => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                const group = input?.closest('[data-auth-field-group]');
                const field = group?.querySelector('[data-auth-field]');
                const error = group?.querySelector('[data-auth-error]');
                const hint = group?.querySelector('[data-auth-hint]');
                const icon = group?.querySelector('[data-auth-icon]');

                return { input, group, field, error, hint, icon };
            };

            const getValues = () => ({
                password: getParts('password').input?.value ?? '',
                password_confirmation: getParts('password_confirmation').input?.value ?? '',
            });

            const getRuleStates = (password) => ({
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                symbol: /[^A-Za-z0-9]/.test(password),
                email: password !== '' && password.toLowerCase() !== accountEmail,
            });

            const updateRuleList = (password) => {
                const ruleStates = getRuleStates(password);

                Object.entries(ruleStates).forEach(([ruleName, isValid]) => {
                    const item = form.querySelector(`[data-password-rule-item="${ruleName}"]`);
                    const dot = item?.querySelector('[data-password-rule-dot]');

                    if (!(item instanceof HTMLElement) || !(dot instanceof HTMLElement)) {
                        return;
                    }

                    item.classList.toggle('text-emerald-700', isValid);
                    item.classList.toggle('dark:text-emerald-300', isValid);
                    item.classList.toggle('text-slate-500', !isValid);
                    item.classList.toggle('dark:text-slate-400', !isValid);

                    dot.classList.toggle('bg-emerald-500', isValid);
                    dot.classList.toggle('dark:bg-emerald-400', isValid);
                    dot.classList.toggle('bg-slate-300', !isValid);
                    dot.classList.toggle('dark:bg-slate-600', !isValid);
                });
            };

            const showFeedback = (message) => {
                if (!(feedback instanceof HTMLElement) || !(feedbackText instanceof HTMLElement)) {
                    return;
                }

                feedback.hidden = false;
                feedback.classList.remove('hidden');
                feedbackText.textContent = message;
            };

            const clearFeedback = () => {
                if (!(feedback instanceof HTMLElement) || !(feedbackText instanceof HTMLElement)) {
                    return;
                }

                feedback.hidden = true;
                feedback.classList.add('hidden');
                feedbackText.textContent = '';
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
                }

                if (icon instanceof HTMLElement) {
                    icon.innerHTML = state === 'valid'
                        ? icons.valid
                        : state === 'invalid'
                            ? icons.invalid
                            : '';
                }

                setFieldMessage(fieldName, state === 'invalid' ? message : '');
                fieldStates[fieldName] = state;
            };

            const clearFieldState = (fieldName) => {
                const { field, icon } = getParts(fieldName);

                field?.classList.remove('is-valid', 'is-invalid', 'is-error', 'is-pending');

                if (icon instanceof HTMLElement) {
                    icon.innerHTML = '';
                }

                setFieldMessage(fieldName, '');
                delete fieldStates[fieldName];
            };

            const getPasswordError = (password) => {
                if (!password) return 'Please enter a new password.';
                if (password.length < 8) return 'Password must be at least 8 characters.';
                if (!/[A-Z]/.test(password)) return 'Password must contain at least one uppercase letter.';
                if (!/[a-z]/.test(password)) return 'Password must contain at least one lowercase letter.';
                if (!/\d/.test(password)) return 'Password must contain at least one number.';
                if (!/[^A-Za-z0-9]/.test(password)) return 'Password must contain at least one special character.';
                if (accountEmail !== '' && password.toLowerCase() === accountEmail) return 'Password must be different from your email address.';
                return '';
            };

            const getPasswordConfirmationError = ({ password, password_confirmation: passwordConfirmation }) => {
                if (!passwordConfirmation) return 'Please confirm your new password.';
                if (passwordConfirmation !== password) return 'Passwords do not match.';
                return '';
            };

            const updateMatchMessage = ({ password, password_confirmation: passwordConfirmation }) => {
                if (!(matchMessage instanceof HTMLElement)) {
                    return;
                }

                if (password === '' || passwordConfirmation === '') {
                    matchMessage.hidden = true;
                    matchMessage.className = 'hidden text-xs font-medium';
                    matchMessage.textContent = '';
                    return;
                }

                if (password === passwordConfirmation) {
                    matchMessage.hidden = false;
                    matchMessage.className = 'text-xs font-medium text-emerald-700 dark:text-emerald-300';
                    matchMessage.textContent = 'Passwords match.';
                    return;
                }

                matchMessage.hidden = true;
                matchMessage.className = 'hidden text-xs font-medium';
                matchMessage.textContent = '';
            };

            const validateField = (fieldName) => {
                const values = getValues();

                if (fieldName === 'password') {
                    const message = getPasswordError(values.password);
                    updateRuleList(values.password);

                    if (message !== '') {
                        setFieldState('password', 'invalid', message);
                        return false;
                    }

                    setFieldState('password', 'valid');
                    return true;
                }

                const message = getPasswordConfirmationError(values);
                updateMatchMessage(values);

                if (message !== '') {
                    setFieldState('password_confirmation', 'invalid', message);
                    return false;
                }

                setFieldState('password_confirmation', 'valid');
                return true;
            };

            const setSubmittingState = (state) => {
                submitting = state;

                if (!(submitButton instanceof HTMLButtonElement)) {
                    return;
                }

                submitButton.disabled = state;
                submitButton.setAttribute('aria-busy', state ? 'true' : 'false');
                submitButton.innerHTML = state
                    ? `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                        </svg>
                        <span>Updating password...</span>
                    `
                    : submitButtonDefaultMarkup;
            };

            const focusField = (fieldName) => {
                const input = getParts(fieldName).input;
                input?.focus();
            };

            const applyServerErrors = (errors) => {
                const passwordError = Array.isArray(errors.password) ? errors.password[0] : '';
                const confirmationError = Array.isArray(errors.password_confirmation) ? errors.password_confirmation[0] : '';

                if (passwordError) {
                    setFieldState('password', 'invalid', passwordError);
                }

                if (confirmationError) {
                    setFieldState('password_confirmation', 'invalid', confirmationError);
                }
            };

            ['password', 'password_confirmation'].forEach((fieldName) => {
                const input = getParts(fieldName).input;

                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                if ((serverErrors[fieldName] ?? '').trim() !== '') {
                    setFieldState(fieldName, 'invalid', serverErrors[fieldName].trim());
                    touchedFields.add(fieldName);
                }

                input.addEventListener('input', () => {
                    clearFeedback();
                    touchedFields.add(fieldName);

                    if (fieldName === 'password') {
                        updateRuleList(input.value);
                    }

                    if (input.value.trim() === '') {
                        clearFieldState(fieldName);
                        updateMatchMessage(getValues());

                        if (fieldName === 'password' && (getParts('password_confirmation').input?.value ?? '') !== '') {
                            validateField('password_confirmation');
                        }

                        return;
                    }

                    validateField(fieldName);

                    if (fieldName === 'password' && ((getParts('password_confirmation').input?.value ?? '') !== '' || touchedFields.has('password_confirmation'))) {
                        validateField('password_confirmation');
                    }
                });

                input.addEventListener('blur', () => {
                    touchedFields.add(fieldName);
                    validateField(fieldName);

                    if (fieldName === 'password' && ((getParts('password_confirmation').input?.value ?? '') !== '' || touchedFields.has('password_confirmation'))) {
                        validateField('password_confirmation');
                    }
                });
            });

            updateRuleList(getValues().password);
            updateMatchMessage(getValues());

            form.addEventListener('submit', async (event) => {
                if (submitting) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();
                clearFeedback();
                touchedFields.add('password');
                touchedFields.add('password_confirmation');

                const isPasswordValid = validateField('password');
                const isConfirmationValid = validateField('password_confirmation');

                if (!isPasswordValid || !isConfirmationValid) {
                    focusField(!isPasswordValid ? 'password' : 'password_confirmation');
                    return;
                }

                setSubmittingState(true);

                try {
                    const response = await fetch(form.action, {
                        method: form.method.toUpperCase(),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: new FormData(form),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok || data.success !== true) {
                        if (response.status === 422 && data.errors) {
                            clearFieldState('password');
                            clearFieldState('password_confirmation');
                            applyServerErrors(data.errors);

                            if (data.errors.password || data.errors.password_confirmation) {
                                focusField(data.errors.password ? 'password' : 'password_confirmation');
                                return;
                            }
                        }

                        throw new Error(data.message || 'Unable to update password right now.');
                    }

                    window.location.href = data.redirect || '/';
                } catch (error) {
                    showFeedback(error.message || 'Unable to update password right now.');
                } finally {
                    setSubmittingState(false);
                }
            });
        })();
    </script>
</x-layouts.auth>
