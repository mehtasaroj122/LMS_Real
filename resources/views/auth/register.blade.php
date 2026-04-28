<x-layouts.auth
    title="Register"
    eyebrow="Register"
    heading="Complete your account"
    subheading="Use your invited email, ID, and phone to finish setup."
    shellMaxWidthClass="max-w-6xl"
    panelGridClass="lg:grid-cols-[minmax(0,34rem)_minmax(0,1fr)]"
    contentMaxWidthClass="max-w-xl"
>
    @php
        $registerRole = old('role', in_array($prefillRole ?? 'staff', ['staff', 'student'], true) ? $prefillRole : 'staff');
        $prefilledEmail = old('email', $prefillEmail ?? '');
        $registerFieldNames = ['role', 'email', 'staff_id', 'student_id', 'phone', 'password', 'password_confirmation'];
        $registerServerErrors = [];
        $existingAccountNotice = $existingAccountMessage ?? null;

        foreach ($registerFieldNames as $registerFieldName) {
            $registerFieldMessage = trim((string) $errors->first($registerFieldName));

            if ($registerFieldMessage !== '') {
                $registerServerErrors[$registerFieldName] = $registerFieldMessage;
            }
        }

        $registerFieldOrder = array_values(array_filter([
            'role',
            'email',
            $registerRole === 'staff' ? 'staff_id' : 'student_id',
            'phone',
            'password',
            'password_confirmation',
            $registerRole === 'staff' ? 'student_id' : 'staff_id',
        ]));
        $firstRegisterErrorField = null;

        foreach ($registerFieldOrder as $registerFieldName) {
            if (filled($registerServerErrors[$registerFieldName] ?? null)) {
                $firstRegisterErrorField = $registerFieldName;

                break;
            }
        }

        $registerErrorFor = static fn (string $fieldName): ?string => $firstRegisterErrorField === $fieldName
            ? ($registerServerErrors[$fieldName] ?? null)
            : null;
        $roleError = $registerErrorFor('role');

        if (! $existingAccountNotice && $errors->any()) {
            $firstRegisterError = strtolower((string) $errors->first());

            if (str_contains($firstRegisterError, 'already active') || str_contains($firstRegisterError, 'sign in with your email')) {
                $existingAccountNotice = 'This account is already active. Please sign in with your email and password instead.';
            }
        }
    @endphp

    <x-auth-card>
        <div class="space-y-4">
            @if (session('status'))
                <x-auth-alert variant="success" :message="session('status')" />
            @endif

            @if ($existingAccountNotice)
                <x-auth-alert variant="info" :message="$existingAccountNotice" />
            @endif

            <div
                id="register-feedback"
                class="hidden rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-100"
                role="alert"
                aria-live="assertive"
            >
                <p id="register-feedback-text"></p>
            </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5" id="register-form" novalidate data-server-errors='@json($registerServerErrors)'>
            @csrf

            <div class="grid gap-4 xl:grid-cols-2">
                <div class="auth-field-group space-y-1.5" data-auth-field-group="role">
                    <div
                        @class(['auth-outlined-field', 'is-error' => filled($roleError)])
                        data-auth-field
                        data-filled="{{ $registerRole !== '' ? 'true' : 'false' }}"
                    >
                        <select
                            id="role"
                            name="role"
                            class="auth-outlined-input appearance-none"
                            required
                            aria-invalid="{{ filled($roleError) ? 'true' : 'false' }}"
                            aria-describedby="role-error"
                        >
                            <option value="staff" @selected($registerRole === 'staff')>Staff</option>
                            <option value="student" @selected($registerRole === 'student')>Student</option>
                        </select>

                        <label for="role" class="auth-outlined-label">Role</label>
                    </div>

                    <p
                        id="role-error"
                        class="auth-field-error {{ filled($roleError) ? 'is-visible' : '' }}"
                        data-auth-error
                        @if (! filled($roleError)) hidden @endif
                    >
                        {{ $roleError }}
                    </p>
                </div>

                <x-input-floating
                    name="email"
                    label="Email address"
                    type="email"
                    :error="$registerErrorFor('email')"
                    required
                    autocomplete="email"
                    autocapitalize="none"
                    spellcheck="false"
                    :value="$prefilledEmail"
                />

                <div id="staff-id-group" @class(['hidden' => $registerRole !== 'staff'])>
                    <x-input-floating
                        name="staff_id"
                        label="Staff ID"
                        :error="$registerErrorFor('staff_id')"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                    />
                </div>

                <div id="student-id-group" @class(['hidden' => $registerRole !== 'student'])>
                    <x-input-floating
                        name="student_id"
                        label="Student ID"
                        :error="$registerErrorFor('student_id')"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
                    />
                </div>

                <x-input-floating
                    name="phone"
                    label="Phone number"
                    type="tel"
                    :error="$registerErrorFor('phone')"
                    required
                    autocomplete="tel"
                />

                <x-input-password
                    name="password"
                    label="Password"
                    :error="$registerErrorFor('password')"
                    required
                    autocomplete="new-password"
                />

                <x-input-password
                    name="password_confirmation"
                    label="Confirm password"
                    :error="$registerErrorFor('password_confirmation')"
                    required
                    autocomplete="new-password"
                />
            </div>

            <x-button-primary text="Complete Registration" icon="user-plus" id="register-submit-button" />

            <p class="text-center text-sm text-slate-600 dark:text-slate-400">
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

            const feedback = document.getElementById('register-feedback');
            const feedbackText = document.getElementById('register-feedback-text');
            const submitButton = document.getElementById('register-submit-button');
            const roleSelect = document.getElementById('role');
            const staffGroup = document.getElementById('staff-id-group');
            const studentGroup = document.getElementById('student-id-group');
            const serverErrors = JSON.parse(form.dataset.serverErrors ?? '{}');
            const fieldNames = ['role', 'email', 'staff_id', 'student_id', 'phone', 'password', 'password_confirmation'];
            const submitButtonDefaultMarkup = submitButton instanceof HTMLButtonElement ? submitButton.innerHTML : '';
            let submitting = false;
            const getRole = () => roleSelect instanceof HTMLSelectElement && roleSelect.value === 'student' ? 'student' : 'staff';
            const getActiveIdentifierFieldName = () => getRole() === 'student' ? 'student_id' : 'staff_id';
            const getOrderedFieldNames = () => ['role', 'email', getActiveIdentifierFieldName(), 'phone', 'password', 'password_confirmation'];
            const invalidIcon = `
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="10" cy="10" r="7.5"></circle>
                    <path d="M10 6.5v4.5"></path>
                    <path d="M10 13.75h.01"></path>
                </svg>
            `;

            const getParts = (fieldName) => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                const group = input?.closest('[data-auth-field-group]') ?? (fieldName === 'role' ? form.querySelector('[data-auth-field-group="role"]') : null);
                const field = group?.querySelector('[data-auth-field]');
                const error = group?.querySelector('[data-auth-error]');
                const hint = group?.querySelector('[data-auth-hint]');
                const icon = group?.querySelector('[data-auth-icon]');

                return { input, group, field, error, hint, icon };
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
                        <span>Completing Registration...</span>
                    `
                    : submitButtonDefaultMarkup;
            };

            const focusField = (fieldName) => {
                const input = getParts(fieldName).input;

                if (!(input instanceof HTMLElement)) {
                    return;
                }

                window.setTimeout(() => {
                    input.focus({ preventScroll: true });

                    if (input instanceof HTMLInputElement && typeof input.setSelectionRange === 'function') {
                        const position = input.value.length;
                        input.setSelectionRange(position, position);
                    }
                }, 0);
            };

            const clearFieldError = (fieldName) => {
                const { input, field, error, hint, icon } = getParts(fieldName);

                field?.classList.remove('is-error', 'is-invalid');

                if (error instanceof HTMLElement) {
                    error.textContent = '';
                    error.hidden = true;
                    error.classList.remove('is-visible');
                }

                if (hint instanceof HTMLElement) {
                    hint.classList.remove('is-hidden');
                }

                if (icon instanceof HTMLElement) {
                    icon.innerHTML = '';
                }

                if (input instanceof HTMLElement) {
                    input.setAttribute('aria-invalid', 'false');
                }
            };

            const clearAllFieldErrors = () => {
                fieldNames.forEach((fieldName) => clearFieldError(fieldName));
            };

            const setFieldError = (fieldName, message) => {
                const { input, field, error, hint, icon } = getParts(fieldName);
                const showError = typeof message === 'string' && message.trim() !== '';

                clearFieldError(fieldName);

                if (!showError) {
                    return;
                }

                field?.classList.add('is-error');

                if (error instanceof HTMLElement) {
                    error.textContent = message.trim();
                    error.hidden = false;
                    error.classList.add('is-visible');
                }

                if (hint instanceof HTMLElement) {
                    hint.classList.add('is-hidden');
                }

                if (icon instanceof HTMLElement) {
                    icon.innerHTML = invalidIcon;
                }

                if (input instanceof HTMLElement) {
                    input.setAttribute('aria-invalid', 'true');
                }
            };

            const showSingleError = (fieldName, message) => {
                clearAllFieldErrors();
                setFieldError(fieldName, message);
                focusField(fieldName);
            };

            const normalizeEmail = (value) => value.trim().toLowerCase();
            const normalizePhone = (value) => {
                const trimmed = value.trim();

                if (!trimmed) {
                    return '';
                }

                const digits = trimmed.replace(/\D/g, '');

                return trimmed.startsWith('+') ? `+${digits}` : digits;
            };

            const normalizeIdentifier = (value) => value.trim().toUpperCase();

            const normalizeIdentifierInput = (input) => {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }

                const nextValue = normalizeIdentifier(input.value);

                if (input.value === nextValue) {
                    return;
                }

                const { selectionStart, selectionEnd } = input;

                input.value = nextValue;

                if (selectionStart !== null && selectionEnd !== null) {
                    input.setSelectionRange(selectionStart, selectionEnd);
                }
            };

            const validateIdentifier = (fieldName, label) => {
                const rawValue = getParts(fieldName).input?.value ?? '';
                const value = normalizeIdentifier(rawValue);

                if (rawValue.trim() === '') {
                    return `Enter your ${label}.`;
                }

                if (value.length < 3) {
                    return `${label.charAt(0).toUpperCase() + label.slice(1)} must be at least 3 characters long.`;
                }

                if (value.length > 50) {
                    return `${label.charAt(0).toUpperCase() + label.slice(1)} must be 50 characters or fewer.`;
                }

                if (!/^[A-Za-z0-9-]+$/.test(value)) {
                    return `${label.charAt(0).toUpperCase() + label.slice(1)} can use letters, numbers, and hyphens only.`;
                }

                return '';
            };

            const getClientError = (fieldName) => {
                switch (fieldName) {
                    case 'role':
                        return ['staff', 'student'].includes(getRole())
                            ? ''
                            : 'Select the role linked to your invitation.';

                    case 'email': {
                        const input = getParts('email').input;
                        const value = normalizeEmail(input?.value ?? '');

                        if (value === '') {
                            return 'Enter the email address linked to your account.';
                        }

                        if (value.length > 255) {
                            return 'Email address must be 255 characters or fewer.';
                        }

                        if (input instanceof HTMLInputElement && typeof input.checkValidity === 'function' && !input.checkValidity()) {
                            return 'Enter a valid email address.';
                        }

                        return '';
                    }

                    case 'staff_id':
                        return validateIdentifier('staff_id', 'staff ID');

                    case 'student_id':
                        return validateIdentifier('student_id', 'student ID');

                    case 'phone': {
                        const rawValue = getParts('phone').input?.value ?? '';
                        const value = normalizePhone(rawValue);

                        if (rawValue.trim() === '') {
                            return 'Enter the phone number linked to your invitation.';
                        }

                        if (!/^\+[1-9]\d{7,14}$/.test(value)) {
                            return 'Enter a valid phone number with country code, like +9779812345678.';
                        }

                        return '';
                    }

                    case 'password':
                        return (getParts('password').input?.value ?? '') === ''
                            ? 'Create a password for your account.'
                            : '';

                    case 'password_confirmation': {
                        const password = getParts('password').input?.value ?? '';
                        const confirmation = getParts('password_confirmation').input?.value ?? '';

                        if (confirmation === '') {
                            return 'Confirm your password.';
                        }

                        if (confirmation !== password) {
                            return 'Password confirmation does not match.';
                        }

                        return '';
                    }

                    default:
                        return '';
                }
            };

            const findFirstError = (errors) => {
                if (!errors || typeof errors !== 'object') {
                    return null;
                }

                for (const fieldName of getOrderedFieldNames()) {
                    const value = errors[fieldName];
                    const message = Array.isArray(value) ? value[0] : value;

                    if (typeof message === 'string' && message.trim() !== '') {
                        return { fieldName, message: message.trim() };
                    }
                }

                for (const [fieldName, value] of Object.entries(errors)) {
                    const message = Array.isArray(value) ? value[0] : value;

                    if (typeof message === 'string' && message.trim() !== '') {
                        return { fieldName, message: message.trim() };
                    }
                }

                return null;
            };

            const validateForm = () => {
                for (const fieldName of getOrderedFieldNames()) {
                    const message = getClientError(fieldName);

                    if (message !== '') {
                        return { fieldName, message };
                    }
                }

                return null;
            };

            const syncValues = () => {
                const emailInput = getParts('email').input;
                const phoneInput = getParts('phone').input;
                const staffIdInput = getParts('staff_id').input;
                const studentIdInput = getParts('student_id').input;

                if (emailInput instanceof HTMLInputElement) {
                    emailInput.value = normalizeEmail(emailInput.value);
                }

                if (phoneInput instanceof HTMLInputElement) {
                    phoneInput.value = normalizePhone(phoneInput.value);
                }

                if (staffIdInput instanceof HTMLInputElement) {
                    normalizeIdentifierInput(staffIdInput);
                }

                if (studentIdInput instanceof HTMLInputElement) {
                    normalizeIdentifierInput(studentIdInput);
                }
            };

            const bindFieldSurfaceFocus = (fieldName) => {
                const { field, input } = getParts(fieldName);

                if (!(field instanceof HTMLElement) || !(input instanceof HTMLElement)) {
                    return;
                }

                field.addEventListener('mousedown', (event) => {
                    const target = event.target;

                    if (!(target instanceof HTMLElement)) {
                        return;
                    }

                    if (target.closest('button') || target === input) {
                        return;
                    }

                    if (input instanceof HTMLSelectElement) {
                        input.focus({ preventScroll: true });
                        return;
                    }

                    event.preventDefault();
                    focusField(fieldName);
                });
            };

            const syncRolePresentation = () => {
                const roleField = getParts('role').field;
                const role = getRole();

                if (roleField instanceof HTMLElement) {
                    roleField.dataset.filled = role ? 'true' : 'false';
                }

                if (staffGroup instanceof HTMLElement) {
                    const showStaff = role === 'staff';
                    staffGroup.classList.toggle('hidden', !showStaff);
                    const input = getParts('staff_id').input;

                    if (input instanceof HTMLInputElement) {
                        input.required = showStaff;
                        input.disabled = !showStaff;
                    }

                    if (!showStaff) {
                        clearFieldError('staff_id');
                    }
                }

                if (studentGroup instanceof HTMLElement) {
                    const showStudent = role === 'student';
                    studentGroup.classList.toggle('hidden', !showStudent);
                    const input = getParts('student_id').input;

                    if (input instanceof HTMLInputElement) {
                        input.required = showStudent;
                        input.disabled = !showStudent;
                    }

                    if (!showStudent) {
                        clearFieldError('student_id');
                    }
                }
            };

            fieldNames.forEach((fieldName) => {
                const input = getParts(fieldName).input;

                if (!(input instanceof HTMLElement)) {
                    return;
                }

                bindFieldSurfaceFocus(fieldName);

                input.addEventListener('focus', () => {
                    clearFeedback();
                });

                input.addEventListener('input', () => {
                    clearFeedback();
                    clearFieldError(fieldName);

                    if (fieldName === 'staff_id' || fieldName === 'student_id') {
                        normalizeIdentifierInput(input);
                    }
                });
            });

            roleSelect?.addEventListener('change', () => {
                clearFeedback();
                syncRolePresentation();
                clearFieldError('role');
            });

            form.addEventListener('submit', async (event) => {
                if (submitting) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();
                clearFeedback();
                syncValues();

                const clientError = validateForm();

                if (clientError) {
                    showSingleError(clientError.fieldName, clientError.message);
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
                        const firstError = findFirstError(data.errors ?? {});

                        if (response.status === 422 && firstError) {
                            showSingleError(firstError.fieldName, firstError.message);
                            return;
                        }

                        throw new Error(data.message || 'Unable to complete registration right now.');
                    }

                    window.location.href = data.redirect || '{{ route('login') }}';
                } catch (error) {
                    showFeedback(error instanceof Error ? error.message : 'Unable to complete registration right now.');
                } finally {
                    setSubmittingState(false);
                }
            });

            syncRolePresentation();
            syncValues();

            const initialError = findFirstError(serverErrors);

            if (initialError) {
                showSingleError(initialError.fieldName, initialError.message);
            }
        })();
    </script>
</x-layouts.auth>
