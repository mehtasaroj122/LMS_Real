<script>
        const USER_VALIDATION_MESSAGES = {
            name: {
                required: 'Enter the user\'s full name.',
                min: 'Full name must be at least 2 characters long.',
                max: 'Full name must be 255 characters or fewer.',
                format: 'Full name can use letters and spaces only.',
            },
            email: {
                required: 'Enter the user\'s email address.',
                format: 'Enter a valid email address, like user@example.com.',
                max: 'Email address must be 255 characters or fewer.',
                unique: 'This email is already assigned to another user.',
            },
            role: {
                required: 'Select a user role.',
                invalid: 'Select a valid user role.',
            },
            phone: {
                format: 'Enter a valid phone number with country code, like +9779812345678.',
                unique: 'This phone number is already assigned to another user.',
            },
            gender: {
                invalid: 'Select a valid gender option.',
            },
            date_of_birth: {
                required: 'Select the student\'s date of birth.',
                invalid: 'Enter a valid date of birth.',
                future: 'Date of birth must be earlier than today.',
                age: 'Student age must be between 14 and 100 years.',
            },
            address: {
                min: 'Address must be at least 10 characters long.',
                max: 'Address must be 255 characters or fewer.',
                unsafe: 'Address contains unsupported characters. Remove any HTML or script-like content.',
            },
            department_id: {
                required: 'Select a department.',
            },
            staff_id: {
                required: 'Enter the staff ID.',
                min: 'Staff ID must be at least 3 characters long.',
                format: 'Staff ID can use letters, numbers, and hyphens only.',
                unique: 'This staff ID is already in use.',
            },
            designation: {
                min: 'Staff designation must be at least 2 characters long.',
            },
            join_date: {
                invalid: 'Enter a valid join date.',
                future: 'Join date cannot be in the future.',
            },
            roll_no: {
                required: 'Enter the student ID.',
                min: 'Student ID must be at least 3 characters long.',
                max: 'Student ID must be 100 characters or fewer.',
                format: 'Student ID can use letters, numbers, and hyphens only.',
                unique: 'This student ID is already in use.',
            },
            batch: {
                required: 'Enter the batch year.',
                format: 'Batch year must be a 4-digit year.',
            },
            semester: {
                required: 'Select the current semester.',
                format: 'Semester must be a number between 1 and 8.',
            },
            status: {
                required: 'Select the user status.',
            },
        };

        function normalizeLiveSingleSpaceValue(value) {
            const rawValue = String(value ?? '');
            const withoutLeadingWhitespace = rawValue.replace(/^\s+/, '');

            if (withoutLeadingWhitespace === '') {
                return '';
            }

            const hadTrailingWhitespace = /\s$/.test(withoutLeadingWhitespace);
            const normalized = withoutLeadingWhitespace.replace(/\s{2,}/g, ' ');

            if (!hadTrailingWhitespace) {
                return normalized;
            }

            return `${normalized.replace(/\s+$/, '')} `;
        }

        function normalizeUserLiveFieldValue(fieldName, value) {
            const rawValue = String(value ?? '');

            switch (fieldName) {
                case 'name':
                case 'address':
                case 'designation':
                    return normalizeLiveSingleSpaceValue(rawValue);
                case 'email':
                    return rawValue.trim().toLowerCase();
                case 'phone': {
                    const trimmed = rawValue.trim();
                    const digits = trimmed.replace(/\D/g, '');

                    if (!trimmed) {
                        return '';
                    }

                    return trimmed.startsWith('+') ? `+${digits}` : digits;
                }
                case 'staff_id':
                case 'roll_no':
                    return rawValue.trim().toUpperCase();
                default:
                    return rawValue.trim();
            }
        }

        class LiveUserFormValidator {
            constructor({ formId, submitButtonId, fields, createMode = false, getUserId = () => null }) {
                this.form = document.getElementById(formId);
                this.submitButton = document.getElementById(submitButtonId);
                this.fields = fields;
                this.createMode = createMode;
                this.getUserId = getUserId;
                this.abortControllers = {};
                this.pendingFields = new Set();
                this.verifiedValues = {};
                this.fieldState = {};
                this.isSubmitting = false;

                this.ensureFieldIcons();
                this.attachListeners();
                this.refreshVisibility();
            }

            getFieldConfig(fieldName) {
                return this.fields[fieldName] || null;
            }

            getFieldElement(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config) {
                    return null;
                }

                if (config.type === 'radio') {
                    return this.form?.querySelector(`input[name="${config.name}"]:checked`) || null;
                }

                return document.getElementById(config.id);
            }

            getFieldElements(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config || !this.form) {
                    return [];
                }

                if (config.type === 'radio') {
                    return Array.from(this.form.querySelectorAll(`input[name="${config.name}"]`));
                }

                const element = document.getElementById(config.id);
                return element ? [element] : [];
            }

            getFieldGroup(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config) {
                    return null;
                }

                if (config.groupId) {
                    return document.getElementById(config.groupId);
                }

                return this.getFieldElement(fieldName)?.closest('.form-group') || null;
            }

            getErrorElement(fieldName) {
                const config = this.getFieldConfig(fieldName);
                return config?.errorId ? document.getElementById(config.errorId) : null;
            }

            getFieldIcon(fieldName) {
                return this.getFieldGroup(fieldName)?.querySelector('.field-validation-icon') ?? null;
            }

            ensureFieldIcons() {
                if (!this.form) {
                    return;
                }

                Object.keys(this.fields).forEach((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    if (!config || config.type === 'radio') {
                        return;
                    }

                    const group = this.getFieldGroup(fieldName);
                    const error = this.getErrorElement(fieldName);
                    const element = document.getElementById(config.id);
                    if (!group || !element || group.querySelector('.field-validation-icon')) {
                        return;
                    }

                    const icon = document.createElement('span');
                    icon.className = 'field-validation-icon';
                    icon.setAttribute('aria-hidden', 'true');

                    if (error && error.parentElement === group) {
                        group.insertBefore(icon, error);
                    } else {
                        group.appendChild(icon);
                    }
                });
            }

            clearFieldVisualState(fieldName) {
                const group = this.getFieldGroup(fieldName);
                const error = this.getErrorElement(fieldName);
                const inputs = this.getFieldElements(fieldName);
                const icon = this.getFieldIcon(fieldName);
                const radioWrapper = fieldName === 'status' ? document.getElementById('addStatusRadio') : null;

                group?.classList.remove('error', 'has-valid', 'has-invalid', 'has-pending');
                inputs.forEach((input) => {
                    input.classList.remove('is-valid', 'is-invalid', 'is-pending');
                });
                radioWrapper?.classList.remove('is-valid', 'is-invalid');

                if (error) {
                    error.textContent = '';
                    error.classList.remove('visible');
                }

                if (icon) {
                    icon.innerHTML = '';
                }
            }

            clearAllVisualStates() {
                Object.keys(this.fields).forEach((fieldName) => this.clearFieldVisualState(fieldName));
            }

            focusField(fieldName) {
                const field = this.getFieldElement(fieldName) || this.getFieldElements(fieldName)[0];
                const group = this.getFieldGroup(fieldName) || field;

                if (!field || !group) {
                    return;
                }

                const modal = field.closest('.modal');
                if (modal) {
                    const modalRect = modal.getBoundingClientRect();
                    const groupRect = group.getBoundingClientRect();
                    const nextScrollTop = modal.scrollTop
                        + (groupRect.top - modalRect.top)
                        - (modal.clientHeight / 2)
                        + (groupRect.height / 2);

                    modal.scrollTo({
                        top: Math.max(0, nextScrollTop),
                        behavior: 'smooth',
                    });
                } else {
                    group.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                window.setTimeout(() => {
                    field.focus({ preventScroll: true });
                }, 160);
            }

            getRole() {
                return this.normalizeValue('role', this.getRawValue('role')) || 'student';
            }

            getActiveFields(role = this.getRole()) {
                return Object.keys(this.fields).filter((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    if (!config) {
                        return false;
                    }

                    if (!config.roles || config.roles.length === 0) {
                        return true;
                    }

                    return config.roles.includes(role);
                });
            }

            getRawValue(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config || !this.form) {
                    return '';
                }

                if (config.type === 'radio') {
                    return this.form.querySelector(`input[name="${config.name}"]:checked`)?.value ?? '';
                }

                return document.getElementById(config.id)?.value ?? '';
            }

            normalizeValue(fieldName, value) {
                const rawValue = String(value ?? '');

                switch (fieldName) {
                    case 'name':
                    case 'address':
                    case 'designation':
                        return rawValue.replace(/\s+/g, ' ').trim();
                    case 'email':
                        return rawValue.trim().toLowerCase();
                    case 'phone': {
                        const trimmed = rawValue.trim();
                        const digits = trimmed.replace(/\D/g, '');

                        if (!trimmed) {
                            return '';
                        }

                        return trimmed.startsWith('+') ? `+${digits}` : digits;
                    }
                    case 'roll_no':
                        return rawValue.trim().toUpperCase();
                    default:
                        return rawValue.trim();
                }
            }

            setValue(fieldName, value) {
                const config = this.getFieldConfig(fieldName);
                if (!config || !this.form) {
                    return;
                }

                if (config.type === 'radio') {
                    this.getFieldElements(fieldName).forEach((input) => {
                        input.checked = input.value === value;
                    });
                    return;
                }

                const element = document.getElementById(config.id);
                if (element) {
                    element.value = value;
                }
            }

            collectValues({ syncFields = true } = {}) {
                const values = {};

                Object.keys(this.fields).forEach((fieldName) => {
                    const normalized = this.normalizeValue(fieldName, this.getRawValue(fieldName));
                    values[fieldName] = normalized;

                    const config = this.getFieldConfig(fieldName);
                    if (syncFields && config?.type !== 'radio') {
                        const element = document.getElementById(config.id);
                        if (element && element.value !== normalized) {
                            element.value = normalized;
                        }
                    }
                });

                return values;
            }

            isUniqueField(fieldName, values) {
                if (fieldName === 'email') {
                    return Boolean(values.email);
                }

                if (fieldName === 'phone') {
                    return Boolean(values.phone);
                }

                if (fieldName === 'roll_no') {
                    return values.role === 'student' && Boolean(values.roll_no);
                }

                return false;
            }

            setState(fieldName, state, message = '') {
                const group = this.getFieldGroup(fieldName);
                const error = this.getErrorElement(fieldName);
                const inputs = this.getFieldElements(fieldName);
                const icon = this.getFieldIcon(fieldName);
                const radioWrapper = fieldName === 'status' ? document.getElementById('addStatusRadio') : null;

                group?.classList.remove('error', 'has-valid', 'has-invalid', 'has-pending');
                inputs.forEach((input) => input.classList.remove('is-valid', 'is-invalid', 'is-pending'));
                radioWrapper?.classList.remove('is-valid', 'is-invalid');

                if (state === 'valid') {
                    group?.classList.add('has-valid');
                    inputs.forEach((input) => input.classList.add('is-valid'));
                    radioWrapper?.classList.add('is-valid');
                } else if (state === 'invalid') {
                    group?.classList.add('error');
                    group?.classList.add('has-invalid');
                    inputs.forEach((input) => input.classList.add('is-invalid'));
                    radioWrapper?.classList.add('is-invalid');
                } else if (state === 'pending') {
                    group?.classList.add('has-pending');
                    inputs.forEach((input) => input.classList.add('is-pending'));
                }

                if (icon) {
                    icon.innerHTML = state === 'valid'
                        ? '<i class="fas fa-check-circle"></i>'
                        : state === 'invalid'
                            ? '<i class="fas fa-exclamation-circle"></i>'
                            : state === 'pending'
                                ? '<i class="fas fa-spinner fa-spin"></i>'
                                : '';
                }

                if (error) {
                    error.textContent = message;
                    error.classList.toggle('visible', state === 'invalid' && Boolean(message));
                }

                if (state === 'pending') {
                    this.pendingFields.add(fieldName);
                    this.fieldState[fieldName] = { valid: false };
                } else {
                    this.pendingFields.delete(fieldName);
                    this.fieldState[fieldName] = { valid: state === 'valid' };
                }

                this.updateSubmitState();
            }

            clearFieldState(fieldName, { logicalValid = false, keepState = true } = {}) {
                const group = this.getFieldGroup(fieldName);
                const error = this.getErrorElement(fieldName);
                const inputs = this.getFieldElements(fieldName);
                const icon = this.getFieldIcon(fieldName);
                const radioWrapper = fieldName === 'status' ? document.getElementById('addStatusRadio') : null;

                group?.classList.remove('error', 'has-valid', 'has-invalid', 'has-pending');
                inputs.forEach((input) => input.classList.remove('is-valid', 'is-invalid', 'is-pending'));
                radioWrapper?.classList.remove('is-valid', 'is-invalid');

                if (error) {
                    error.textContent = '';
                    error.classList.remove('visible');
                }

                if (icon) {
                    icon.innerHTML = '';
                }

                this.pendingFields.delete(fieldName);
                if (keepState) {
                    this.fieldState[fieldName] = { valid: logicalValid };
                } else {
                    delete this.fieldState[fieldName];
                }
                this.updateSubmitState();
            }

            isFieldRequired(fieldName, values = this.collectValues()) {
                const role = values.role;

                switch (fieldName) {
                    case 'name':
                    case 'email':
                    case 'role':
                        return true;
                    case 'status':
                        return this.createMode;
                    case 'department_id':
                    case 'date_of_birth':
                        return role === 'student';
                    case 'staff_id':
                        return role === 'staff';
                    case 'roll_no':
                    case 'batch':
                    case 'semester':
                        return role === 'student';
                    default:
                        return false;
                }
            }

            getSyncMessage(fieldName, values) {
                const value = values[fieldName] ?? '';
                const role = values.role;

                switch (fieldName) {
                    case 'name':
                        if (!value) return USER_VALIDATION_MESSAGES.name.required;
                        if (value.length < 2) return USER_VALIDATION_MESSAGES.name.min;
                        if (value.length > 255) return USER_VALIDATION_MESSAGES.name.max;
                        if (!/^[A-Za-z ]+$/.test(value)) return USER_VALIDATION_MESSAGES.name.format;
                        return '';
                    case 'email':
                        if (!value) return USER_VALIDATION_MESSAGES.email.required;
                        if (value.length > 255) return USER_VALIDATION_MESSAGES.email.max;
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return USER_VALIDATION_MESSAGES.email.format;
                        return '';
                    case 'role':
                        if (!value) return USER_VALIDATION_MESSAGES.role.required;
                        if (!['admin', 'staff', 'student'].includes(value)) return USER_VALIDATION_MESSAGES.role.invalid;
                        return '';
                    case 'phone':
                        if (!value) return '';
                        if (!/^\+[1-9]\d{7,14}$/.test(value)) return USER_VALIDATION_MESSAGES.phone.format;
                        return '';
                    case 'gender':
                        if (!value) return '';
                        if (!['male', 'female', 'other'].includes(value)) return USER_VALIDATION_MESSAGES.gender.invalid;
                        return '';
                    case 'date_of_birth': {
                        if (!value) return role === 'student' ? USER_VALIDATION_MESSAGES.date_of_birth.required : '';
                        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return USER_VALIDATION_MESSAGES.date_of_birth.invalid;
                        const [year, month, day] = value.split('-').map(Number);
                        const birthDate = new Date(year, month - 1, day);
                        if (birthDate.getFullYear() !== year || birthDate.getMonth() !== month - 1 || birthDate.getDate() !== day) {
                            return USER_VALIDATION_MESSAGES.date_of_birth.invalid;
                        }
                        const now = new Date();
                        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                        if (birthDate >= today) return USER_VALIDATION_MESSAGES.date_of_birth.future;
                        let age = today.getFullYear() - year;
                        if (today.getMonth() < month - 1 || (today.getMonth() === month - 1 && today.getDate() < day)) age--;
                        if (role === 'student' && (age < 14 || age > 100)) return USER_VALIDATION_MESSAGES.date_of_birth.age;
                        return '';
                    }
                    case 'address':
                        if (!value) return '';
                        if (value.length < 10) return USER_VALIDATION_MESSAGES.address.min;
                        if (value.length > 255) return USER_VALIDATION_MESSAGES.address.max;
                        if (/<[^>]*>/.test(value)) return USER_VALIDATION_MESSAGES.address.unsafe;
                        return '';
                    case 'department_id':
                        if (role !== 'student') return '';
                        return value ? '' : USER_VALIDATION_MESSAGES.department_id.required;
                    case 'staff_id':
                        if (role !== 'staff') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.staff_id.required;
                        if (value.length < 3) return USER_VALIDATION_MESSAGES.staff_id.min;
                        if (!/^[A-Za-z0-9-]+$/.test(value)) return USER_VALIDATION_MESSAGES.staff_id.format;
                        return '';
                    case 'designation':
                        if (role !== 'staff') return '';
                        if (!value) return '';
                        if (value.length < 2) return USER_VALIDATION_MESSAGES.designation.min;
                        return '';
                    case 'join_date': {
                        if (role !== 'staff') return '';
                        if (!value) return '';
                        const joinDate = new Date(value);
                        if (Number.isNaN(joinDate.getTime())) return USER_VALIDATION_MESSAGES.join_date.invalid;
                        const today = new Date();
                        const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                        if (joinDate > todayMidnight) return USER_VALIDATION_MESSAGES.join_date.future;
                        return '';
                    }
                    case 'roll_no':
                        if (role !== 'student') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.roll_no.required;
                        if (value.length < 3) return USER_VALIDATION_MESSAGES.roll_no.min;
                        if (value.length > 100) return USER_VALIDATION_MESSAGES.roll_no.max;
                        if (!/^[A-Za-z0-9-]+$/.test(value)) return USER_VALIDATION_MESSAGES.roll_no.format;
                        return '';
                    case 'batch':
                        if (role !== 'student') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.batch.required;
                        if (!/^(19|20)\d{2}$/.test(value)) return USER_VALIDATION_MESSAGES.batch.format;
                        return '';
                    case 'semester':
                        if (role !== 'student') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.semester.required;
                        if (!/^[1-8]$/.test(value)) return USER_VALIDATION_MESSAGES.semester.format;
                        return '';
                    case 'status':
                        if (!this.createMode) return '';
                        return value ? '' : USER_VALIDATION_MESSAGES.status.required;
                    default:
                        return '';
                }
            }

            async runUniqueValidation(fieldName, value, values) {
                if (!this.isUniqueField(fieldName, values)) {
                    return true;
                }

                if (this.verifiedValues[fieldName] === value) {
                    this.setState(fieldName, 'valid');
                    return true;
                }

                this.abortControllers[fieldName]?.abort();
                const controller = new AbortController();
                this.abortControllers[fieldName] = controller;
                this.setState(fieldName, 'pending');

                try {
                    const payload = {
                        ...values,
                        field: fieldName,
                        user_id: this.getUserId(),
                    };

                    const response = await fetch('{{ route('admin.users.validate-field') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        signal: controller.signal,
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        const data = await response.json();
                        if (this.collectValues()[fieldName] !== value || controller.signal.aborted) return false;
                        const message = data.message || USER_VALIDATION_MESSAGES[fieldName]?.unique || 'This value is already in use.';
                        this.setState(fieldName, 'invalid', message);
                        return false;
                    }

                    if (this.collectValues()[fieldName] !== value || controller.signal.aborted) {
                        return false;
                    }

                    this.verifiedValues[fieldName] = value;
                    this.setState(fieldName, 'valid');
                    return true;
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return false;
                    }

                    this.setState(fieldName, 'invalid', 'Could not verify this field right now. Please try again.');
                    return false;
                }
            }

            async validateField(fieldName, { runUniqueCheck = false, syncFields = true } = {}) {
                const values = this.collectValues({ syncFields });
                const activeFields = this.getActiveFields(values.role);

                if (!activeFields.includes(fieldName)) {
                    this.clearFieldState(fieldName);
                    return true;
                }

                const syncMessage = this.getSyncMessage(fieldName, values);
                if (syncMessage) {
                    this.setState(fieldName, 'invalid', syncMessage);
                    return false;
                }

                if (this.isUniqueField(fieldName, values)) {
                    if (!runUniqueCheck) {
                        this.setState(fieldName, 'valid');
                        return true;
                    }

                    return this.runUniqueValidation(fieldName, values[fieldName], values);
                }

                this.setState(fieldName, 'valid');
                return true;
            }

            async validateAll({ showFirstErrorOnly = false } = {}) {
                const values = this.collectValues();
                const activeFields = this.getActiveFields(values.role);
                let firstInvalidField = null;
                let allValid = true;

                if (showFirstErrorOnly) {
                    activeFields.forEach((fieldName) => this.clearFieldVisualState(fieldName));
                }

                for (const fieldName of activeFields) {
                    const isValid = await this.validateField(fieldName, { runUniqueCheck: true });
                    if (!isValid) {
                        allValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = fieldName;
                            if (showFirstErrorOnly) {
                                break;
                            }
                        }
                    }
                }

                if (!allValid && firstInvalidField) {
                    this.focusField(firstInvalidField);
                }

                return allValid;
            }

            refreshVisibility() {
                const activeFields = this.getActiveFields();
                const values = this.collectValues();
                this.form?.querySelectorAll('.conditional-field').forEach((group) => {
                    group.classList.toggle('hidden', !(group.dataset.for || '').split(',').includes(values.role));
                });
                this.form?.querySelectorAll('[data-student-required]').forEach((mark) => {
                    mark.hidden = values.role !== 'student';
                });
                Object.keys(this.fields).forEach((fieldName) => {
                    if (!activeFields.includes(fieldName)) {
                        this.abortControllers[fieldName]?.abort();
                        delete this.verifiedValues[fieldName];
                        this.clearFieldState(fieldName, { logicalValid: false, keepState: false });
                    } else {
                        const value = values[fieldName];
                        const isRequired = this.isFieldRequired(fieldName, values);
                        const shouldStayNeutral = !value && !['role', 'status'].includes(fieldName);

                        if (shouldStayNeutral) {
                            this.clearFieldState(fieldName, { logicalValid: !isRequired });
                        } else {
                            this.validateField(fieldName, { runUniqueCheck: false });
                        }
                    }
                });

                this.updateSubmitState();
            }

            resetForm() {
                this.form?.reset();
                Object.values(this.abortControllers).forEach((controller) => controller?.abort());
                this.abortControllers = {};
                this.pendingFields.clear();
                this.verifiedValues = {};
                this.fieldState = {};
                Object.keys(this.fields).forEach((fieldName) => this.clearFieldState(fieldName));
                this.refreshVisibility();
                this.clearAllVisualStates();
            }

            seed(values = {}) {
                this.resetForm();

                Object.keys(this.fields).forEach((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    if (!config) {
                        return;
                    }

                    let value = '';
                    if (fieldName === 'department_id') {
                        value = values.department_id ?? '';
                    } else {
                        value = values[fieldName] ?? '';
                    }

                    const normalized = this.normalizeValue(fieldName, value);
                    this.setValue(fieldName, normalized);

                    if (['email', 'phone', 'roll_no', 'staff_id'].includes(fieldName) && normalized) {
                        this.verifiedValues[fieldName] = normalized;
                    }
                });

                this.refreshVisibility();
                this.clearAllVisualStates();
            }

            updateSubmitState() {
                if (!this.submitButton) {
                    return;
                }

                this.submitButton.disabled = this.isSubmitting;
            }

            applyServerErrors(errors = {}, { showFirstErrorOnly = false } = {}) {
                const entries = Object.entries(errors);
                let firstFieldName = null;

                if (showFirstErrorOnly) {
                    this.clearAllVisualStates();
                }

                entries.forEach(([fieldName, fieldErrors]) => {
                    if (showFirstErrorOnly && firstFieldName) {
                        return;
                    }

                    const message = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors;
                    if (!message || !this.fields[fieldName]) {
                        return;
                    }

                    this.setState(fieldName, 'invalid', message);
                    firstFieldName = fieldName;
                });

                if (firstFieldName) {
                    this.focusField(firstFieldName);
                }
            }

            setSubmitting(isSubmitting) {
                this.isSubmitting = Boolean(isSubmitting);
                this.updateSubmitState();
            }

            toFormData() {
                const values = this.collectValues();
                const formData = new FormData(this.form);

                Object.entries(values).forEach(([fieldName, value]) => {
                    formData.set(fieldName, value);
                });

                return formData;
            }

            attachListeners() {
                Object.keys(this.fields).forEach((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    const elements = this.getFieldElements(fieldName);

                    elements.forEach((element) => {
                        const inputEvent = config?.type === 'radio' || element.tagName === 'SELECT' || element.type === 'date' ? 'change' : 'input';

                        element.addEventListener(inputEvent, () => {
                            const normalized = inputEvent === 'input'
                                ? normalizeUserLiveFieldValue(fieldName, this.getRawValue(fieldName))
                                : this.normalizeValue(fieldName, this.getRawValue(fieldName));

                            if (config?.type !== 'radio' && element.value !== normalized) {
                                element.value = normalized;
                            }

                            if (['email', 'phone', 'roll_no', 'staff_id'].includes(fieldName) && this.verifiedValues[fieldName] !== normalized) {
                                delete this.verifiedValues[fieldName];
                            }

                            if (fieldName === 'role') {
                                this.refreshVisibility();
                                return;
                            }

                            this.validateField(fieldName, {
                                runUniqueCheck: false,
                                syncFields: inputEvent !== 'input',
                            });
                        });

                        element.addEventListener('blur', () => {
                            if (config?.type !== 'radio') {
                                const normalized = this.normalizeValue(fieldName, this.getRawValue(fieldName));
                                if (element.value !== normalized) {
                                    element.value = normalized;
                                }
                            }

                            this.validateField(fieldName, { runUniqueCheck: true });
                        });
                    });
                });
            }
        }

        const USER_CREATE_FIELDS = {
                        name: { id: 'addName', errorId: 'addNameError' },
                        email: { id: 'addEmail', errorId: 'addEmailError' },
                        role: { id: 'addRoleSelect', errorId: 'addRoleError' },
                        phone: { id: 'addPhone', errorId: 'addPhoneError' },
                        gender: { id: 'addGender', errorId: 'addGenderError' },
                        date_of_birth: { id: 'addDateOfBirth', errorId: 'addDateOfBirthError' },
                        department_id: { id: 'addDepartmentSelect', errorId: 'addDepartmentError', roles: ['student', 'staff'] },
                        staff_id: { id: 'addStaffId', errorId: 'addStaffIdError', roles: ['staff'] },
                        designation: { id: 'addDesignation', errorId: 'addDesignationError', roles: ['staff'] },
                        join_date: { id: 'addJoinDate', errorId: 'addJoinDateError', roles: ['staff'] },
                        roll_no: { id: 'addRollNo', errorId: 'addRollNoError', roles: ['student'] },
                        batch: { id: 'addBatch', errorId: 'addBatchError', roles: ['student'] },
                        semester: { id: 'addSemester', errorId: 'addSemesterError', roles: ['student'] },
                        address: { id: 'addAddress', errorId: 'addAddressError' },
                        status: { name: 'status', type: 'radio', groupId: 'addStatusGroup', errorId: 'addStatusError' },
                    };
</script>
