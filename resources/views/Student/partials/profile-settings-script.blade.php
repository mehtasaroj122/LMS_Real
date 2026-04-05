<script>
    const StudentProfileSettings = (() => {
        const state = {
            profileEditing: false,
            profileSubmitting: false,
            passwordSubmitting: false,
            emailPending: false,
            emailTimer: null,
            emailAbort: null,
            fieldTimers: {},
            profileErrors: {},
            passwordErrors: {},
            originalProfile: {},
            flashSuccess: @json(session('success')),
            flashError: @json(session('error')),
            startProfileEditing: false,
            startPasswordEditing: false,
            feedbackUI: null,
        };

        const profileFields = ['name', 'email', 'phone', 'gender', 'address'];
        const passwordFields = ['current_password', 'password', 'password_confirmation'];
        const requirementText = {
            reqLength: 'At least 8 characters',
            reqUppercase: 'At least one uppercase letter',
            reqLowercase: 'At least one lowercase letter',
            reqNumber: 'At least one number',
            reqMatch: 'Password and confirmation must match',
            reqDifferent: 'New password must be different from current',
        };

        const validators = {
            name: (value) => !text(value)
                ? 'Full name is required'
                : text(value).length < 2
                    ? 'Name must be at least 2 characters'
                    : !/^[a-zA-Z\s'-]+$/.test(text(value))
                        ? 'Name can only contain letters, spaces, hyphens and apostrophes'
                        : text(value).length > 255
                            ? 'Name must be 255 characters or fewer'
                            : null,
            email: (value) => !String(value || '').trim()
                ? 'Email address is required'
                : !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim())
                    ? 'Please enter a valid email address'
                    : String(value || '').trim().length > 255
                        ? 'Email address must be 255 characters or fewer'
                        : null,
            phone: (value) => phone(value) && !/^\+?\d{10,20}$/.test(phone(value))
                ? 'Please enter a valid phone number'
                : null,
            gender: (value) => String(value || '').trim() && !['male', 'female', 'other'].includes(String(value || '').trim())
                ? 'Please select a valid gender option'
                : null,
            address: (value) => String(value || '').trim().length > 500
                ? 'Address must be 500 characters or fewer'
                : null,
            current_password: (value) => String(value || '').trim()
                ? null
                : 'Enter your current password',
            password: (value) => !value
                ? 'Enter a new password'
                : value.length < 8
                    ? 'Password must be at least 8 characters'
                    : !/[A-Z]/.test(value)
                        ? 'Password must contain at least one uppercase letter'
                        : !/[a-z]/.test(value)
                            ? 'Password must contain at least one lowercase letter'
                            : !/\d/.test(value)
                                ? 'Password must contain at least one number'
                                : els.currentPassword?.value && value === els.currentPassword.value
                                    ? 'New password must be different from your current password'
                                    : null,
            password_confirmation: (value) => !value
                ? 'Confirm your new password'
                : value.length < 8
                    ? 'Password confirmation must be at least 8 characters'
                    : els.newPassword?.value && value !== els.newPassword.value
                        ? 'Passwords do not match'
                        : null,
        };

        const els = {};

        function init() {
            cache();
            if (!els.page) {
                return;
            }

            state.originalProfile = snapshot();
            hydrateErrors();
            initializeFeedbackUI();
            bindTabs();
            bindProfile();
            bindPhoto();
            bindPassword();
            bindToggles();
            updateCounter();
            updatePasswordRequirements();

            if (errorEl('profile_photo')?.textContent.trim()) {
                switchTab('photo');
            } else if (state.startPasswordEditing) {
                switchTab('security');
            } else {
                switchTab('profile');
            }

            state.startProfileEditing ? enterEdit() : cancelEdit();
            state.startPasswordEditing ? enterPasswordEdit(true) : setPasswordMode(false);
            flash();
            refreshIcons();
        }

        function cache() {
            els.page = document.getElementById('profileSettings');
            els.tabButtons = [...els.page.querySelectorAll('.tab-btn')];
            els.panels = [...els.page.querySelectorAll('.tab-content')];
            els.profileForm = document.getElementById('profileForm');
            els.photoForm = document.getElementById('photoForm');
            els.passwordForm = document.getElementById('passwordForm');
            els.editProfileBtn = document.getElementById('editProfileBtn');
            els.saveProfileBtn = document.getElementById('saveProfileBtn');
            els.cancelProfileBtn = document.getElementById('cancelProfileBtn');
            els.profileSectionStatus = document.getElementById('profileSectionStatus');
            els.profileInputs = profileFields.map((field) => document.getElementById(field)).filter(Boolean);
            els.department = document.getElementById('department');
            els.address = document.getElementById('address');
            els.addressCount = document.getElementById('addressCount');
            els.addressCounter = document.getElementById('addressCounter');
            els.currentPassword = document.getElementById('currentPassword');
            els.newPassword = document.getElementById('newPassword');
            els.confirmPassword = document.getElementById('confirmPassword');
            els.passwordIntro = document.getElementById('passwordIntro');
            els.showPasswordFormBtn = document.getElementById('showPasswordFormBtn');
            els.updatePasswordBtn = document.getElementById('updatePasswordBtn');
            els.cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
            els.passwordSectionStatus = document.getElementById('passwordSectionStatus');
            els.passwordToggles = [...document.querySelectorAll('#passwordForm .pwd-toggle')];
            els.photoUpload = document.getElementById('photoUpload');
            els.photoPreview = document.getElementById('photoPreview');
            els.photoPreviewImg = document.getElementById('photoPreviewImg');
            els.fileName = document.getElementById('fileName');
            els.uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
            els.removePhotoBtn = document.getElementById('removePhotoBtn');
            els.leftProfileAvatar = document.getElementById('leftProfileAvatar');
            els.leftAvatarImage = document.getElementById('leftAvatarImage');
            els.leftAvatarInitial = document.getElementById('leftAvatarInitial');
            els.leftUserName = document.getElementById('leftUserName');
            els.leftUserEmail = document.getElementById('leftUserEmail');
            els.leftUsernameValue = document.getElementById('leftUsernameValue');
        }

        function initializeFeedbackUI() {
            state.feedbackUI = window.getStudentPortalFeedback?.() || null;
        }

        function hydrateErrors() {
            profileFields.forEach((field) => {
                const message = errorEl(field)?.textContent.trim();
                if (message) {
                    state.profileErrors[field] = message;
                }
            });

            passwordFields.forEach((field) => {
                const message = errorEl(field)?.textContent.trim();
                if (message) {
                    state.passwordErrors[field] = message;
                }
            });

            state.startProfileEditing = profileFields.some((field) => Boolean(state.profileErrors[field]));
            state.startPasswordEditing = passwordFields.some((field) => Boolean(state.passwordErrors[field]));
        }

        function bindTabs() {
            els.tabButtons.forEach((button) => {
                button.addEventListener('click', () => switchTab(button.dataset.tab));
            });
        }

        function switchTab(name) {
            const nextPanelId = `${name}TabPanel`;

            els.tabButtons.forEach((button) => {
                const active = button.dataset.tab === name;
                button.classList.toggle('active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
                button.setAttribute('tabindex', active ? '0' : '-1');
            });

            els.panels.forEach((panel) => {
                const active = panel.id === nextPanelId;
                panel.classList.toggle('active', active);
                panel.hidden = !active;
                panel.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
        }

        function bindProfile() {
            els.editProfileBtn?.addEventListener('click', enterEdit);
            els.cancelProfileBtn?.addEventListener('click', cancelEdit);
            els.profileForm?.addEventListener('submit', submitProfile);

            els.profileInputs.forEach((input) => {
                const field = input.dataset.validate;
                if (!field) {
                    return;
                }

                input.addEventListener('input', () => {
                    if (!state.profileEditing) {
                        return;
                    }

                    if (field === 'address') {
                        updateCounter();
                    }

                    clearTimeout(state.fieldTimers[field]);
                    state.fieldTimers[field] = window.setTimeout(() => validateProfileField(field, true, true), 300);
                });

                input.addEventListener('blur', () => {
                    if (!state.profileEditing) {
                        return;
                    }

                    clearTimeout(state.fieldTimers[field]);
                    validateProfileField(field, true, true, true);
                });
            });
        }

        function enterEdit() {
            state.profileEditing = true;
            els.profileInputs.forEach((input) => {
                input.disabled = false;
            });

            if (els.department) {
                els.department.disabled = true;
                els.department.setAttribute('aria-disabled', 'true');
            }

            if (els.editProfileBtn) {
                els.editProfileBtn.hidden = true;
            }

            if (els.saveProfileBtn) {
                els.saveProfileBtn.hidden = false;
            }

            if (els.cancelProfileBtn) {
                els.cancelProfileBtn.hidden = false;
            }

            if (els.profileSectionStatus) {
                els.profileSectionStatus.classList.add('editing');
                els.profileSectionStatus.textContent = 'Editing';
            }

            updateCounter();
            updateProfileSave();
            els.profileInputs[0]?.focus();
        }

        function cancelEdit() {
            state.profileEditing = false;
            state.profileErrors = {};

            if (state.emailAbort) {
                state.emailAbort.abort();
            }

            clearTimeout(state.emailTimer);
            state.emailPending = false;

            Object.entries(state.originalProfile).forEach(([field, value]) => {
                const input = document.getElementById(field);
                if (input) {
                    input.value = value;
                }
            });

            els.profileInputs.forEach((input) => {
                input.disabled = true;
                setError(input.id, null);
                paint(input, 'default');
            });

            if (els.department) {
                els.department.disabled = true;
                els.department.setAttribute('aria-disabled', 'true');
                paint(els.department, 'default');
            }

            if (els.editProfileBtn) {
                els.editProfileBtn.hidden = false;
            }

            if (els.saveProfileBtn) {
                els.saveProfileBtn.hidden = true;
                els.saveProfileBtn.disabled = true;
            }

            if (els.cancelProfileBtn) {
                els.cancelProfileBtn.hidden = true;
            }

            if (els.profileSectionStatus) {
                els.profileSectionStatus.classList.remove('editing');
                els.profileSectionStatus.textContent = 'Read only';
            }

            updateCounter();
        }

        function validateProfileField(field, show = true, asyncEmail = true, immediate = false) {
            const input = document.getElementById(field);
            const message = validators[field] ? validators[field](input?.value) : null;
            state.profileErrors[field] = message;

            if (show) {
                setError(field, message);
            }

            if (field === 'email') {
                if (message) {
                    finishEmail();
                    paint(input, 'invalid');
                } else if (asyncEmail) {
                    queueEmail(immediate);
                } else {
                    paint(input, input?.value.trim() ? 'valid' : 'default');
                }
            } else {
                paint(input, message ? 'invalid' : input?.value.trim() ? 'valid' : 'default');
            }

            updateProfileSave();
            return message;
        }

        function validateProfileForm(show = true) {
            return profileFields.every((field) => !validateProfileField(field, show, false));
        }

        function queueEmail(immediate = false) {
            const value = String(document.getElementById('email')?.value || '').trim().toLowerCase();
            const originalEmail = String(state.originalProfile.email || '').trim().toLowerCase();

            if (!value || value === originalEmail) {
                state.profileErrors.email = null;
                setError('email', null);
                paint(document.getElementById('email'), value ? 'valid' : 'default');
                finishEmail();
                updateProfileSave();
                return;
            }

            clearTimeout(state.emailTimer);
            state.emailTimer = window.setTimeout(() => runEmailCheck(value), immediate ? 0 : 450);
        }

        async function runEmailCheck(value) {
            const input = document.getElementById('email');
            if (!input) {
                return true;
            }

            if (state.emailAbort) {
                state.emailAbort.abort();
            }

            const controller = new AbortController();
            state.emailAbort = controller;
            state.emailPending = true;
            paint(input, 'validating');
            updateProfileSave();

            try {
                const response = await fetch(els.profileForm.dataset.checkEmailUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: JSON.stringify({ email: value }),
                    signal: controller.signal,
                });

                const payload = await parseResponse(response);

                if (String(input.value || '').trim().toLowerCase() !== value) {
                    return false;
                }

                if (!response.ok || payload.available === false) {
                    state.profileErrors.email = payload?.errors?.email?.[0] || 'This email address is already in use';
                    setError('email', state.profileErrors.email);
                    paint(input, 'invalid');
                    return false;
                }

                state.profileErrors.email = null;
                setError('email', null);
                paint(input, 'valid');
                return true;
            } catch (error) {
                if (error.name !== 'AbortError') {
                    state.profileErrors.email = 'Unable to verify email availability right now';
                    setError('email', state.profileErrors.email);
                    paint(input, 'invalid');
                }
                return false;
            } finally {
                if (state.emailAbort === controller) {
                    state.emailAbort = null;
                    finishEmail();
                }
                updateProfileSave();
            }
        }

        function finishEmail() {
            state.emailPending = false;
            document.getElementById('email')?.classList.remove('validating');
        }

        async function submitProfile(event) {
            event.preventDefault();
            if (!state.profileEditing || state.profileSubmitting) {
                return;
            }

            if (!validateProfileForm(true)) {
                focusFirstError(els.profileForm);
                return;
            }

            const email = String(document.getElementById('email')?.value || '').trim().toLowerCase();
            const changedEmail = email !== String(state.originalProfile.email || '').trim().toLowerCase();

            if (changedEmail && !(await runEmailCheck(email))) {
                focusFirstError(els.profileForm);
                return;
            }

            if (!profileChanged()) {
                showToast({ type: 'info', title: 'No changes to save', message: 'Make a change before saving your profile.' });
                return;
            }

            state.profileSubmitting = true;
            if (els.saveProfileBtn) {
                els.saveProfileBtn.disabled = true;
            }

            const oldLabel = els.saveProfileBtn?.innerHTML || '';
            if (els.saveProfileBtn) {
                els.saveProfileBtn.innerHTML = '<span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.35);border-top-color:currentColor;border-radius:999px;display:inline-block;animation:spin .8s linear infinite;"></span><span>Saving...</span>';
            }

            try {
                const response = await fetch(els.profileForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: new FormData(els.profileForm),
                });

                const payload = await parseResponse(response);

                if (!response.ok || payload?.success === false) {
                    serverErrors(payload, 'profile');
                    return;
                }

                applyUser(payload.user || {});
                state.originalProfile = snapshot();
                cancelEdit();
                showToast({ type: 'success', title: 'Profile updated', message: payload.message || 'Profile updated successfully.' });
            } catch (error) {
                console.error(error);
                showToast({ type: 'error', title: 'Profile update failed', message: 'Unable to save your profile right now.' });
            } finally {
                state.profileSubmitting = false;
                if (els.saveProfileBtn) {
                    els.saveProfileBtn.innerHTML = oldLabel;
                }
                updateProfileSave();
                refreshIcons();
            }
        }

        function applyUser(user) {
            if ('name' in user) {
                document.getElementById('name').value = user.name || '';
                if (els.leftUserName) {
                    els.leftUserName.textContent = user.name || '';
                }
            }

            if ('email' in user) {
                document.getElementById('email').value = user.email || '';
                if (els.leftUserEmail) {
                    els.leftUserEmail.textContent = user.email || '';
                }
            }

            if ('phone' in user) {
                document.getElementById('phone').value = user.phone || '';
            }

            if ('gender' in user && document.getElementById('gender')) {
                document.getElementById('gender').value = user.gender || '';
            }

            if ('department' in user && els.department) {
                els.department.value = user.department || '';
            }

            if ('address' in user) {
                document.getElementById('address').value = user.address || '';
            }

            if (els.leftUsernameValue) {
                els.leftUsernameValue.textContent = user.username || username(user.email || document.getElementById('email')?.value || '');
            }

            if ('profile_photo_url' in user) {
                renderPhoto(user.profile_photo_url || '');
                renderAvatar(user.profile_photo_url || '', user.name || els.leftUserName?.textContent || '');
                if (els.removePhotoBtn) {
                    els.removePhotoBtn.disabled = !user.profile_photo_url;
                }
            }

            updateCounter();
        }

        function bindPhoto() {
            els.photoUpload?.addEventListener('change', previewPhoto);
            els.photoForm?.addEventListener('submit', submitPhoto);
            els.removePhotoBtn?.addEventListener('click', removePhoto);
        }

        function previewPhoto() {
            setError('profile_photo', null);
            const file = els.photoUpload?.files?.[0];

            if (!file) {
                if (els.fileName) {
                    els.fileName.textContent = 'No file chosen';
                }
                if (els.uploadPhotoBtn) {
                    els.uploadPhotoBtn.disabled = true;
                }
                return;
            }

            if (!file.type.startsWith('image/')) {
                failPhoto('Please select a valid image file');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                failPhoto('Profile photo must not exceed 2MB');
                return;
            }

            if (els.fileName) {
                els.fileName.textContent = file.name;
            }

            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                renderPhoto(loadEvent.target?.result || '');
                if (els.uploadPhotoBtn) {
                    els.uploadPhotoBtn.disabled = false;
                }
            };
            reader.readAsDataURL(file);
        }

        async function submitPhoto(event) {
            event.preventDefault();

            if (!els.photoUpload?.files?.[0]) {
                failPhoto('Choose a photo before uploading');
                return;
            }

            const oldLabel = els.uploadPhotoBtn?.innerHTML || '';
            if (els.uploadPhotoBtn) {
                els.uploadPhotoBtn.disabled = true;
                els.uploadPhotoBtn.innerHTML = '<span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.35);border-top-color:currentColor;border-radius:999px;display:inline-block;animation:spin .8s linear infinite;"></span><span>Uploading...</span>';
            }

            let uploaded = false;

            try {
                const response = await fetch(els.photoForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: new FormData(els.photoForm),
                });

                const payload = await parseResponse(response);

                if (!response.ok || payload?.success === false) {
                    serverErrors(payload, 'photo');
                    return;
                }

                const photoUrl = payload?.user?.profile_photo_url || payload?.photoUrl || '';
                setError('profile_photo', null);
                renderPhoto(photoUrl);
                renderAvatar(photoUrl, els.leftUserName?.textContent || document.getElementById('name')?.value || '');
                els.photoForm.reset();

                if (els.fileName) {
                    els.fileName.textContent = 'No file chosen';
                }

                if (els.removePhotoBtn) {
                    els.removePhotoBtn.disabled = !photoUrl;
                }

                uploaded = true;
                showToast({ type: 'success', title: 'Photo uploaded', message: payload.message || 'Photo uploaded successfully.' });
            } catch (error) {
                console.error(error);
                showToast({ type: 'error', title: 'Upload failed', message: 'Unable to upload your photo right now.' });
            } finally {
                if (els.uploadPhotoBtn) {
                    els.uploadPhotoBtn.innerHTML = oldLabel;
                    els.uploadPhotoBtn.disabled = uploaded ? true : !Boolean(els.photoUpload?.files?.[0]);
                }
                refreshIcons();
            }
        }

        async function removePhoto() {
            if (els.removePhotoBtn?.disabled) {
                return;
            }

            const confirmed = await openConfirmModal({
                title: 'Remove profile photo?',
                message: 'Your current profile photo will be removed from your account. This action can be reversed later by uploading a new photo.',
                detail: 'Profile photo',
                confirmLabel: 'Remove Photo',
                cancelLabel: 'Keep Photo',
                variant: 'danger',
                buttonVariant: 'danger',
            });

            if (!confirmed) {
                return;
            }

            const oldLabel = els.removePhotoBtn?.innerHTML || '';
            if (els.removePhotoBtn) {
                els.removePhotoBtn.disabled = true;
                els.removePhotoBtn.innerHTML = '<span style="width:16px;height:16px;border:2px solid rgba(59,130,246,.25);border-top-color:currentColor;border-radius:999px;display:inline-block;animation:spin .8s linear infinite;"></span><span>Removing...</span>';
            }

            let removed = false;

            try {
                const response = await fetch('{{ route("student.profile.remove-photo") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                });

                const payload = await parseResponse(response);

                if (!response.ok || payload?.success === false) {
                    showToast({ type: 'error', title: 'Remove failed', message: payload?.message || 'Unable to remove your photo right now.' });
                    return;
                }

                setError('profile_photo', null);
                renderPhoto('');
                renderAvatar('', els.leftUserName?.textContent || document.getElementById('name')?.value || '');
                els.photoForm.reset();

                if (els.fileName) {
                    els.fileName.textContent = 'No file chosen';
                }

                if (els.uploadPhotoBtn) {
                    els.uploadPhotoBtn.disabled = true;
                }

                if (els.removePhotoBtn) {
                    els.removePhotoBtn.disabled = true;
                }

                removed = true;
                showToast({ type: 'success', title: 'Photo removed', message: payload.message || 'Profile photo removed successfully.' });
            } catch (error) {
                console.error(error);
                showToast({ type: 'error', title: 'Remove failed', message: 'Unable to remove your photo right now.' });
            } finally {
                if (els.removePhotoBtn) {
                    els.removePhotoBtn.innerHTML = oldLabel;
                    els.removePhotoBtn.disabled = removed ? true : false;
                }
                refreshIcons();
            }
        }

        function failPhoto(message) {
            setError('profile_photo', message);
            if (els.photoUpload) {
                els.photoUpload.value = '';
            }
            if (els.fileName) {
                els.fileName.textContent = 'No file chosen';
            }
            if (els.uploadPhotoBtn) {
                els.uploadPhotoBtn.disabled = true;
            }
        }

        function renderPhoto(src) {
            if (!els.photoPreview) {
                return;
            }

            let img = els.photoPreviewImg;
            if (!img) {
                img = document.createElement('img');
                img.id = 'photoPreviewImg';
                img.alt = 'Preview';
                img.style.display = 'none';
                els.photoPreview.appendChild(img);
                els.photoPreviewImg = img;
            }

            let icon = document.getElementById('photoFallbackIcon');
            if (src) {
                img.src = src;
                img.style.display = 'block';
                if (icon) {
                    icon.style.display = 'none';
                }
            } else {
                img.removeAttribute('src');
                img.style.display = 'none';
                if (!icon) {
                    icon = document.createElement('div');
                    icon.id = 'photoFallbackIcon';
                    icon.className = 'photo-upload-icon';
                    icon.innerHTML = '<i data-lucide="user-round"></i>';
                    els.photoPreview.prepend(icon);
                }
                icon.style.display = 'flex';
            }

            refreshIcons();
        }

        function renderAvatar(src, name) {
            if (!els.leftAvatarImage || !els.leftAvatarInitial) {
                return;
            }

            if (src) {
                els.leftAvatarImage.src = src;
                els.leftAvatarImage.style.display = 'block';
                els.leftAvatarInitial.style.display = 'none';
                return;
            }

            els.leftAvatarImage.removeAttribute('src');
            els.leftAvatarImage.style.display = 'none';
            const fallbackInitials = text(name)
                ? initials(name)
                : (els.leftProfileAvatar?.dataset.defaultInitials || 'ST');
            els.leftAvatarInitial.textContent = fallbackInitials;
            els.leftAvatarInitial.style.display = 'block';
        }

        function bindPassword() {
            els.showPasswordFormBtn?.addEventListener('click', () => enterPasswordEdit());

            [els.currentPassword, els.newPassword, els.confirmPassword].filter(Boolean).forEach((input) => {
                input.addEventListener('input', () => {
                    if (!isPasswordEditing()) {
                        return;
                    }

                    validatePasswordField(input.name, true);
                    if (input.name !== 'password_confirmation') {
                        validatePasswordField('password_confirmation', false);
                    }
                    updatePasswordRequirements();
                    updatePasswordSave();
                });

                input.addEventListener('blur', () => {
                    if (!isPasswordEditing()) {
                        return;
                    }

                    validatePasswordField(input.name, true);
                    updatePasswordRequirements();
                    updatePasswordSave();
                });
            });

            els.passwordForm?.addEventListener('submit', submitPassword);
            els.cancelPasswordBtn?.addEventListener('click', () => {
                resetPassword();
                setPasswordMode(false);
            });
        }

        function enterPasswordEdit(preserveState = false) {
            if (!isPasswordEditing() && !preserveState) {
                resetPassword();
            }
            setPasswordMode(true);
            els.currentPassword?.focus();
        }

        function setPasswordMode(editing) {
            [els.currentPassword, els.newPassword, els.confirmPassword].filter(Boolean).forEach((input) => {
                input.disabled = !editing;
            });

            (els.passwordToggles || []).forEach((button) => {
                button.disabled = !editing;
            });

            if (els.passwordIntro) {
                els.passwordIntro.hidden = editing;
            }

            if (els.passwordForm) {
                els.passwordForm.hidden = !editing;
            }

            if (els.showPasswordFormBtn) {
                els.showPasswordFormBtn.setAttribute('aria-expanded', editing ? 'true' : 'false');
            }

            if (els.cancelPasswordBtn) {
                els.cancelPasswordBtn.hidden = !editing;
            }

            if (els.updatePasswordBtn) {
                els.updatePasswordBtn.hidden = !editing;
            }

            if (els.passwordSectionStatus) {
                els.passwordSectionStatus.classList.toggle('editing', editing);
                els.passwordSectionStatus.textContent = editing ? 'Editing' : 'Read only';
            }

            if (!editing) {
                state.passwordSubmitting = false;
            }

            updatePasswordRequirements();
            updatePasswordSave();
        }

        function isPasswordEditing() {
            return !(els.currentPassword?.disabled ?? true);
        }

        function validatePasswordField(field, show = true) {
            const input = pwd(field);
            const message = validators[field] ? validators[field](input?.value || '') : null;
            state.passwordErrors[field] = message;

            if (show) {
                setError(field, message);
            }

            paint(input, message ? 'invalid' : input?.value ? 'valid' : 'default');
            return message;
        }

        function updatePasswordRequirements() {
            const current = els.currentPassword?.value || '';
            const next = els.newPassword?.value || '';
            const confirm = els.confirmPassword?.value || '';
            const checks = {
                reqLength: next.length >= 8,
                reqUppercase: /[A-Z]/.test(next),
                reqLowercase: /[a-z]/.test(next),
                reqNumber: /\d/.test(next),
                reqMatch: next.length > 0 && next === confirm,
                reqDifferent: next.length > 0 && next !== current,
            };

            Object.entries(checks).forEach(([id, ok]) => {
                const item = document.getElementById(id);
                if (!item) {
                    return;
                }

                item.classList.toggle('valid', ok);
                item.classList.toggle('invalid', !ok);
                item.innerHTML = `<span class="requirement-icon">${ok ? '<i data-lucide="check"></i>' : ''}</span> ${requirementText[id]}`;
            });

            refreshIcons();
        }

        function updatePasswordSave() {
            const hasValue = passwordFields.some((field) => Boolean(pwd(field)?.value));
            const invalid = passwordFields.some((field) => Boolean(state.passwordErrors[field] || validatePasswordField(field, false)));

            if (els.updatePasswordBtn) {
                els.updatePasswordBtn.disabled = !isPasswordEditing() || !hasValue || invalid || state.passwordSubmitting;
            }
        }

        async function submitPassword(event) {
            event.preventDefault();
            if (state.passwordSubmitting) {
                return;
            }

            const invalid = passwordFields.some((field) => Boolean(validatePasswordField(field, true)));
            updatePasswordRequirements();
            updatePasswordSave();

            if (invalid) {
                focusFirstError(els.passwordForm);
                return;
            }

            state.passwordSubmitting = true;
            const oldLabel = els.updatePasswordBtn?.innerHTML || '';
            if (els.updatePasswordBtn) {
                els.updatePasswordBtn.disabled = true;
                els.updatePasswordBtn.innerHTML = '<span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.35);border-top-color:currentColor;border-radius:999px;display:inline-block;animation:spin .8s linear infinite;"></span><span>Saving...</span>';
            }

            try {
                const response = await fetch(els.passwordForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: new FormData(els.passwordForm),
                });

                const payload = await parseResponse(response);

                if (!response.ok || payload?.success === false) {
                    serverErrors(payload, 'password');
                    return;
                }

                showToast({ type: 'success', title: 'Password updated', message: payload.message || 'Password updated successfully.' });
                resetPassword();
                setPasswordMode(false);
            } catch (error) {
                console.error(error);
                showToast({ type: 'error', title: 'Password update failed', message: 'Unable to update your password right now.' });
            } finally {
                state.passwordSubmitting = false;
                if (els.updatePasswordBtn) {
                    els.updatePasswordBtn.innerHTML = oldLabel;
                }
                updatePasswordSave();
                refreshIcons();
            }
        }

        function resetPassword() {
            els.passwordForm?.reset();
            state.passwordErrors = {};
            passwordFields.forEach((field) => {
                setError(field, null);
                paint(pwd(field), 'default');
            });

            [els.currentPassword, els.newPassword, els.confirmPassword].filter(Boolean).forEach((input) => {
                input.type = 'password';
            });

            (els.passwordToggles || []).forEach((button) => setPasswordToggleIcon(button, false));
            updatePasswordRequirements();
            updatePasswordSave();
        }

        function bindToggles() {
            document.querySelectorAll('.pwd-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.target);
                    if (!input) {
                        return;
                    }

                    const visible = input.type === 'password';
                    input.type = visible ? 'text' : 'password';
                    setPasswordToggleIcon(button, visible);
                });
            });
        }

        function openConfirmModal({
            title = 'Are you sure?',
            message = 'Please confirm this action.',
            detail = '',
            confirmLabel = 'Confirm',
            cancelLabel = 'Cancel',
            variant = 'warning',
            buttonVariant = variant,
        } = {}) {
            const options = {
                title,
                message,
                detail,
                confirmText: confirmLabel,
                cancelText: cancelLabel,
                variant,
                buttonVariant,
            };

            if (typeof window.confirmStudentPortalAction === 'function') {
                return window.confirmStudentPortalAction(options);
            }

            if (state.feedbackUI?.confirm) {
                return state.feedbackUI.confirm(options);
            }

            console.warn('Student profile confirmation is unavailable.', options);
            return Promise.resolve(false);
        }

        function setPasswordToggleIcon(button, visible) {
            button.innerHTML = `<i data-lucide="${visible ? 'eye-off' : 'eye'}"></i>`;
            refreshIcons();
        }

        function serverErrors(payload, kind) {
            const errors = payload?.errors || {};
            const firstField = Object.keys(errors)[0];

            if (kind === 'profile') {
                if (!state.profileEditing) {
                    enterEdit();
                }

                Object.entries(errors).forEach(([field, messages]) => {
                    const message = Array.isArray(messages) ? messages[0] : messages;
                    state.profileErrors[field] = message;
                    setError(field, message);
                    paint(document.getElementById(field), 'invalid');
                });

                switchTab('profile');
                showToast({ type: 'error', title: 'Review your profile details', message: payload?.message || 'Please review the highlighted profile fields.' });

                if (firstField) {
                    focusFirstError(els.profileForm);
                }
                return;
            }

            if (kind === 'password') {
                if (!isPasswordEditing()) {
                    enterPasswordEdit(true);
                }

                Object.entries(errors).forEach(([field, messages]) => {
                    const message = Array.isArray(messages) ? messages[0] : messages;
                    state.passwordErrors[field] = message;
                    setError(field, message);
                    paint(pwd(field), 'invalid');
                });

                switchTab('security');
                showToast({ type: 'error', title: 'Review your password details', message: payload?.message || 'Please review the highlighted password fields.' });

                if (firstField) {
                    focusFirstError(els.passwordForm);
                }
                return;
            }

            setError('profile_photo', errors.profile_photo?.[0] || payload?.message || 'Please review the selected photo.');
            switchTab('photo');
            showToast({ type: 'error', title: 'Review your photo selection', message: payload?.message || 'Please review the selected photo.' });
            focusFirstError(els.photoForm);
        }

        function setError(field, message) {
            const el = errorEl(field);
            const input = document.getElementById(field) || pwd(field);
            if (!el) {
                return;
            }

            el.textContent = message || '';
            el.classList.toggle('visible', Boolean(message));

            if (input) {
                input.setAttribute('aria-invalid', message ? 'true' : 'false');
            }
        }

        function paint(input, stateName) {
            if (!input) {
                return;
            }

            input.classList.remove('is-invalid', 'is-valid', 'validating');

            if (stateName === 'invalid') {
                input.classList.add('is-invalid');
            }

            if (stateName === 'valid') {
                input.classList.add('is-valid');
            }

            if (stateName === 'validating') {
                input.classList.add('validating');
            }
        }

        function updateCounter() {
            if (!els.address || !els.addressCount || !els.addressCounter) {
                return;
            }

            const size = els.address.value.length;
            els.addressCount.textContent = String(size);
            els.addressCounter.classList.toggle('is-limit', size >= 500);
        }

        function updateProfileSave() {
            const invalid = profileFields.some((field) => Boolean(state.profileErrors[field] || validators[field](document.getElementById(field)?.value || '')));
            if (els.saveProfileBtn) {
                els.saveProfileBtn.disabled = !state.profileEditing || state.profileSubmitting || state.emailPending || invalid || !profileChanged();
            }
        }

        function profileChanged() {
            const current = snapshot();
            return profileFields.some((field) => current[field] !== state.originalProfile[field]);
        }

        function snapshot() {
            return {
                name: text(document.getElementById('name')?.value || ''),
                email: String(document.getElementById('email')?.value || '').trim().toLowerCase(),
                phone: phone(document.getElementById('phone')?.value || ''),
                gender: String(document.getElementById('gender')?.value || '').trim().toLowerCase(),
                address: String(document.getElementById('address')?.value || '').trim(),
            };
        }

        function focusFirstError(container) {
            const error = container?.querySelector('.field-error.visible');
            if (!error) {
                return;
            }

            const field = error.id.replace(/^error_/, '');
            const target = document.getElementById(field) || pwd(field) || (field === 'profile_photo' ? els.photoUpload : null);
            error.scrollIntoView({ behavior: 'smooth', block: 'center' });
            window.setTimeout(() => target?.focus(), 150);
        }

        function flash() {
            if (state.flashSuccess) {
                showToast({ type: 'success', title: 'Success', message: state.flashSuccess });
            }

            if (state.flashError) {
                showToast({ type: 'error', title: 'Notice', message: state.flashError });
            }
        }

        function showToast(message, type = 'info', title = '', detail = '') {
            const payload = typeof message === 'object' && message !== null
                ? message
                : { message, type, title, detail };

            if (!payload.message) {
                return;
            }

            const normalizedType = ['success', 'error', 'warning', 'info'].includes(payload.type)
                ? payload.type
                : 'info';
            const resolvedTitle = payload.title || defaultToastTitle(normalizedType);

            if (state.feedbackUI) {
                state.feedbackUI.showToast({
                    type: normalizedType,
                    title: resolvedTitle,
                    message: payload.message,
                    detail: payload.detail || '',
                    timeout: payload.timeout || 4200,
                });
                return;
            }

            console[normalizedType === 'error' ? 'error' : 'log'](`${resolvedTitle}: ${payload.message}`);
        }

        function defaultToastTitle(type) {
            return {
                success: 'Success',
                error: 'Something went wrong',
                warning: 'Check this',
                info: 'Notice',
            }[type] || 'Notice';
        }

        async function parseResponse(response) {
            const textContent = await response.text();
            if (!textContent) {
                return {};
            }

            try {
                return JSON.parse(textContent);
            } catch (error) {
                return {};
            }
        }

        function errorEl(field) {
            return document.getElementById(`error_${field}`);
        }

        function pwd(field) {
            return {
                current_password: els.currentPassword,
                password: els.newPassword,
                password_confirmation: els.confirmPassword,
            }[field] || null;
        }

        function text(value) {
            return String(value || '').replace(/\s+/g, ' ').trim();
        }

        function phone(value) {
            return String(value || '').trim().replace(/[^\d+]/g, '').replace(/(?!^)\+/g, '');
        }

        function username(email) {
            return String(email || '').split('@')[0] || 'student';
        }

        function initials(name) {
            const pieces = text(name).split(' ').filter(Boolean);
            return pieces.map((part) => part[0]?.toUpperCase() || '').join('') || (els.leftProfileAvatar?.dataset.defaultInitials || 'ST');
        }

        function csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }

        function refreshIcons() {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        }

        return { init, switchTab };
    })();

    document.addEventListener('DOMContentLoaded', StudentProfileSettings.init);
    window.switchToPhotoTab = () => StudentProfileSettings.switchTab('photo');
    window.switchToPersonalTab = () => StudentProfileSettings.switchTab('profile');
    window.switchToSecurityTab = () => StudentProfileSettings.switchTab('security');
</script>
