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
        $roleError = $errors->first('role');
        $existingAccountNotice = $existingAccountMessage ?? null;

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

        <form method="POST" action="{{ route('register') }}" class="space-y-5" id="register-form" novalidate data-validate-field-url="{{ route('register.validate-field') }}">
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
                        autocomplete="off"
                    />
                </div>

                <div id="student-id-group" @class(['hidden' => $registerRole !== 'student'])>
                    <x-input-floating
                        name="student_id"
                        label="Student ID"
                        autocomplete="off"
                    />
                </div>

                <x-input-floating
                    name="phone"
                    label="Phone number"
                    type="tel"
                    required
                    autocomplete="tel"
                />

                <x-input-password
                    name="password"
                    label="Password"
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

            const roleSelect = document.getElementById('role');
            const staffGroup = document.getElementById('staff-id-group');
            const studentGroup = document.getElementById('student-id-group');
            const submitButton = document.getElementById('register-submit-button');
            const validateFieldUrl = form.dataset.validateFieldUrl || '';
            const csrfToken = form.querySelector('input[name="_token"]')?.value || '';
            const validationTokens = new Map();

            const fieldNames = [
                'role',
                'email',
                'staff_id',
                'student_id',
                'phone',
                'password',
                'password_confirmation',
            ];

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const phonePattern = /^\+[1-9]\d{7,14}$/;
            const identifierPattern = /^[A-Za-z0-9-]+$/;

            const getRole = () => roleSelect instanceof HTMLSelectElement ? roleSelect.value : 'staff';
            const getIdentifierFieldName = () => getRole() === 'staff' ? 'staff_id' : 'student_id';

            const getParts = (fieldName) => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                const group = input?.closest('[data-auth-field-group]') ?? (fieldName === 'role' ? form.querySelector('[data-auth-field-group="role"]') : null);
                const field = group?.querySelector('[data-auth-field]');
                const error = group?.querySelector('[data-auth-error]');
                const hint = group?.querySelector('[data-auth-hint]');

                return { input, group, field, error, hint };
            };

            const setFieldError = (fieldName, message = '') => {
                const { input, field, error, hint } = getParts(fieldName);
                const hasError = message !== '';

                field?.classList.toggle('is-error', hasError);
                field?.classList.toggle('is-invalid', hasError);

                if (error instanceof HTMLElement) {
                    error.textContent = message;
                    error.hidden = !hasError;
                    error.classList.toggle('is-visible', hasError);
                }

                if (hint instanceof HTMLElement) {
                    hint.classList.toggle('is-hidden', hasError);
                }

                if (input instanceof HTMLElement) {
                    input.setAttribute('aria-invalid', hasError ? 'true' : 'false');
                }
            };

            const clearFieldError = (fieldName) => setFieldError(fieldName, '');
            const clearAllFieldErrors = (exceptField = null) => {
                fieldNames.forEach((fieldName) => {
                    if (fieldName !== exceptField) {
                        clearFieldError(fieldName);
                    }
                });
            };

            const showOnlyFieldError = (fieldName, message) => {
                clearAllFieldErrors(fieldName);
                setFieldError(fieldName, message);
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
            const invalidateFieldValidation = (fieldName) => {
                validationTokens.set(fieldName, (validationTokens.get(fieldName) ?? 0) + 1);
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
                    staffIdInput.value = normalizeIdentifier(staffIdInput.value);
                }

                if (studentIdInput instanceof HTMLInputElement) {
                    studentIdInput.value = normalizeIdentifier(studentIdInput.value);
                }
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
                }

                if (studentGroup instanceof HTMLElement) {
                    const showStudent = role === 'student';
                    studentGroup.classList.toggle('hidden', !showStudent);
                    const input = getParts('student_id').input;

                    if (input instanceof HTMLInputElement) {
                        input.required = showStudent;
                        input.disabled = !showStudent;
                    }
                }

                invalidateFieldValidation('role');
                invalidateFieldValidation('email');
                invalidateFieldValidation('staff_id');
                invalidateFieldValidation('student_id');
                invalidateFieldValidation('phone');
                clearAllFieldErrors();
                clearFieldError('staff_id');
                clearFieldError('student_id');
            };

            const validateField = (fieldName) => {
                syncValues();

                const value = ((getParts(fieldName).input)?.value ?? '').trim();
                const role = getRole();
                const password = ((getParts('password').input)?.value ?? '');
                const passwordConfirmation = ((getParts('password_confirmation').input)?.value ?? '');

                switch (fieldName) {
                    case 'role':
                        return value ? '' : 'Select the role linked to your invitation.';
                    case 'email':
                        if (!value) return 'Enter the email address linked to your account.';
                        return emailPattern.test(value) ? '' : 'Enter a valid email address.';
                    case 'staff_id':
                        if (role !== 'staff') return '';
                        if (!value) return 'Enter your staff ID.';
                        return identifierPattern.test(value) ? '' : 'Staff ID can use letters, numbers, and hyphens only.';
                    case 'student_id':
                        if (role !== 'student') return '';
                        if (!value) return 'Enter your student ID.';
                        return identifierPattern.test(value) ? '' : 'Student ID can use letters, numbers, and hyphens only.';
                    case 'phone':
                        if (!value) return 'Enter the phone number linked to your invitation.';
                        return phonePattern.test(value) ? '' : 'Use an international phone format like +9779812345678.';
                    case 'password':
                        if (!password) return 'Create a password for your account.';
                        if (password.length < 8) return 'Password must be at least 8 characters.';
                        if (!/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/\d/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
                            return 'Password must include uppercase, lowercase, a number, and a symbol.';
                        }
                        return '';
                    case 'password_confirmation':
                        if (!passwordConfirmation) return 'Confirm your password.';
                        return passwordConfirmation === password ? '' : 'Password confirmation does not match.';
                    default:
                        return '';
                }
            };

            const validateAll = () => {
                clearAllFieldErrors();

                for (const fieldName of fieldNames) {
                    const message = validateField(fieldName);

                    if (message) {
                        showOnlyFieldError(fieldName, message);
                        return fieldName;
                    }
                }

                return null;
            };

            const shouldRunRemoteValidation = (fieldName) => {
                if (!validateFieldUrl || !csrfToken) {
                    return false;
                }

                const role = getRole();
                const emailValue = ((getParts('email').input)?.value ?? '').trim();
                const identifierField = getIdentifierFieldName();
                const identifierValue = ((getParts(identifierField).input)?.value ?? '').trim();
                const phoneValue = ((getParts('phone').input)?.value ?? '').trim();

                if (fieldName === 'email') {
                    return role !== '' && validateField('email') === '';
                }

                if (fieldName === identifierField) {
                    return validateField('email') === '' && validateField(identifierField) === '';
                }

                if (fieldName === 'phone') {
                    return emailValue !== '' && identifierValue !== '' && phoneValue !== ''
                        && validateField('email') === ''
                        && validateField(identifierField) === ''
                        && validateField('phone') === '';
                }

                return false;
            };

            const runRemoteValidation = async (fieldName) => {
                if (!shouldRunRemoteValidation(fieldName)) {
                    return true;
                }

                const requestToken = (validationTokens.get(fieldName) ?? 0) + 1;
                validationTokens.set(fieldName, requestToken);

                const payload = new URLSearchParams({
                    _token: csrfToken,
                    field: fieldName,
                    role: getRole(),
                    email: ((getParts('email').input)?.value ?? '').trim(),
                    phone: ((getParts('phone').input)?.value ?? '').trim(),
                    staff_id: ((getParts('staff_id').input)?.value ?? '').trim(),
                    student_id: ((getParts('student_id').input)?.value ?? '').trim(),
                });

                try {
                    const response = await fetch(validateFieldUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: payload.toString(),
                    });

                    const responseData = await response.json().catch(() => null);

                    if (validationTokens.get(fieldName) !== requestToken) {
                        return false;
                    }

                    if (!response.ok || responseData?.valid === false) {
                        showOnlyFieldError(fieldName, responseData?.message || 'Please review this field and try again.');
                        return false;
                    }

                    clearFieldError(fieldName);
                    return true;
                } catch (error) {
                    return true;
                }
            };

            const focusFirstVisibleError = () => {
                const firstErrorField = fieldNames.find((fieldName) => {
                    const error = getParts(fieldName).error;

                    return error instanceof HTMLElement && !error.hidden && error.textContent.trim() !== '';
                });

                getParts(firstErrorField || 'email').input?.focus();
            };

            fieldNames.forEach((fieldName) => {
                const input = getParts(fieldName).input;

                if (!(input instanceof HTMLElement)) {
                    return;
                }

                input.addEventListener('input', () => {
                    invalidateFieldValidation(fieldName);
                    clearFieldError(fieldName);
                });

                input.addEventListener('blur', async () => {
                    const message = validateField(fieldName);

                    if (message) {
                        showOnlyFieldError(fieldName, message);
                    } else {
                        clearFieldError(fieldName);
                        await runRemoteValidation(fieldName);
                    }
                });
            });

            roleSelect?.addEventListener('change', () => {
                syncRolePresentation();
                clearFieldError('role');

                const emailValue = ((getParts('email').input)?.value ?? '').trim();
                if (emailValue !== '') {
                    void runRemoteValidation('email');
                }
            });

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                const firstInvalid = validateAll();

                if (firstInvalid) {
                    getParts(firstInvalid).input?.focus();
                    return;
                }

                if (submitButton instanceof HTMLButtonElement) {
                    submitButton.disabled = true;
                    submitButton.setAttribute('aria-busy', 'true');
                }

                const remoteValidationOrder = ['email', getIdentifierFieldName(), 'phone'];

                for (const fieldName of remoteValidationOrder) {
                    const valid = await runRemoteValidation(fieldName);

                    if (!valid) {
                        if (submitButton instanceof HTMLButtonElement) {
                            submitButton.disabled = false;
                            submitButton.removeAttribute('aria-busy');
                        }

                        focusFirstVisibleError();
                        return;
                    }
                }

                form.submit();
            });

            syncRolePresentation();

            const firstVisibleError = fieldNames.find((fieldName) => {
                const error = getParts(fieldName).error;

                return error instanceof HTMLElement && !error.hidden && error.textContent.trim() !== '';
            });

            if (firstVisibleError) {
                showOnlyFieldError(firstVisibleError, getParts(firstVisibleError).error?.textContent.trim() || '');
            } else {
                const emailValue = ((getParts('email').input)?.value ?? '').trim();
                if (emailValue !== '') {
                    void runRemoteValidation('email');
                }
            }
        })();
    </script>
</x-layouts.auth>
