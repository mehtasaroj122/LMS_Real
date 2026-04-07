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

        <form method="POST" action="{{ route('register') }}" class="space-y-5" id="register-form" novalidate x-data="{ submitting: false }" x-on:submit="submitting = true">
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
                        autocapitalize="characters"
                        spellcheck="false"
                    />
                </div>

                <div id="student-id-group" @class(['hidden' => $registerRole !== 'student'])>
                    <x-input-floating
                        name="student_id"
                        label="Student ID"
                        autocomplete="off"
                        autocapitalize="characters"
                        spellcheck="false"
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

            <x-button-primary text="Complete Registration" loading="submitting" loading-text="Completing Registration..." icon="user-plus" id="register-submit-button" />

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
            const fieldNames = ['role', 'email', 'staff_id', 'student_id', 'phone', 'password', 'password_confirmation'];
            const getRole = () => roleSelect instanceof HTMLSelectElement && roleSelect.value === 'student' ? 'student' : 'staff';

            const getParts = (fieldName) => {
                const input = form.querySelector(`[name="${fieldName}"]`);
                const group = input?.closest('[data-auth-field-group]') ?? (fieldName === 'role' ? form.querySelector('[data-auth-field-group="role"]') : null);
                const field = group?.querySelector('[data-auth-field]');
                const error = group?.querySelector('[data-auth-error]');
                const hint = group?.querySelector('[data-auth-hint]');

                return { input, group, field, error, hint };
            };

            const clearFieldError = (fieldName) => {
                const { input, field, error, hint } = getParts(fieldName);

                field?.classList.remove('is-error', 'is-invalid');

                if (error instanceof HTMLElement) {
                    error.textContent = '';
                    error.hidden = true;
                    error.classList.remove('is-visible');
                }

                if (hint instanceof HTMLElement) {
                    hint.classList.remove('is-hidden');
                }

                if (input instanceof HTMLElement) {
                    input.setAttribute('aria-invalid', 'false');
                }
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

            const normalizeIdentifier = (value) => value.toUpperCase();

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

                input.addEventListener('input', () => {
                    clearFieldError(fieldName);

                    if (fieldName === 'staff_id' || fieldName === 'student_id') {
                        normalizeIdentifierInput(input);
                    }
                });
            });

            roleSelect?.addEventListener('change', () => {
                syncRolePresentation();
                clearFieldError('role');
            });

            form.addEventListener('submit', () => {
                syncValues();
            });

            syncRolePresentation();
            syncValues();
        })();
    </script>
</x-layouts.auth>
