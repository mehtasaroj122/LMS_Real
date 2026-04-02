(function () {
    class AccountLockManager {
        constructor(config = {}) {
            this.config = config;
            this.state = {
                search: String(config?.state?.search || ''),
                perPage: this.normalizePerPage(config?.state?.perPage),
                page: Math.max(1, Number(config?.state?.page) || 1),
                loading: false,
            };
            this.pendingConfirmAction = null;
            this.feedbackUI = null;
            this.fetchController = null;
            this.searchTimer = null;
            this.requestSequence = 0;
            this.elements = {};
        }

        init() {
            this.cacheElements();

            if (!this.elements.root) {
                return;
            }

            this.initializeFeedbackUi();
            this.syncStateFromLocation();
            this.bindEvents();
            this.syncControls();
            this.restoreSettingsForm();
            this.consumeFlash();
        }

        cacheElements() {
            this.elements.root = document.getElementById('accountLockManagementRoot');
            this.elements.filterForm = document.getElementById('accountLockFiltersForm');
            this.elements.searchInput = document.getElementById('lockSearch');
            this.elements.perPageSelect = document.getElementById('accountLocksPerPage');
            this.elements.resetButton = document.getElementById('accountLockResetBtn');
            this.elements.feedContainer = document.getElementById('accountLockFeedContainer');
            this.elements.summaryContainer = document.getElementById('accountLockSummaryContainer');
            this.elements.monitoringContainer = document.getElementById('accountLockMonitoringContainer');
            this.elements.bulkContainer = document.getElementById('accountLockBulkContainer');
            this.elements.headerMeta = document.getElementById('accountLockHeaderMeta');
            this.elements.settingsForm = document.getElementById('accountLockSettingsForm');
            this.elements.settingsSubmitButton = document.getElementById('accountLockSettingsSubmitBtn');
            this.elements.confirmSubmitButton = document.getElementById('accountLockConfirmSubmitBtn');
        }

        initializeFeedbackUi() {
            if (typeof window.ActionFeedbackUI !== 'function') {
                return;
            }

            this.feedbackUI = new window.ActionFeedbackUI({
                confirm: {
                    modalId: 'accountLockConfirmModal',
                    iconId: 'accountLockConfirmIcon',
                    titleId: 'accountLockConfirmTitle',
                    messageId: 'accountLockConfirmMessage',
                    detailId: 'accountLockConfirmDetail',
                    submitButtonId: 'accountLockConfirmSubmitBtn',
                    confirmLabel: 'Continue',
                },
                toast: {
                    containerId: 'accountLockToastContainer',
                    liveRegionId: 'accountLockLiveRegion',
                },
                setButtonBusy: (button, isBusy, label) => this.setButtonBusy(button, isBusy, label),
            });
        }

        bindEvents() {
            this.elements.filterForm?.addEventListener('submit', (event) => {
                event.preventDefault();
                this.state.search = this.elements.searchInput?.value?.trim() || '';
                this.state.perPage = this.normalizePerPage(this.elements.perPageSelect?.value);
                this.state.page = 1;
                this.loadData();
            });

            this.elements.searchInput?.addEventListener('input', () => {
                window.clearTimeout(this.searchTimer);
                this.searchTimer = window.setTimeout(() => {
                    this.state.search = this.elements.searchInput?.value?.trim() || '';
                    this.state.page = 1;
                    this.loadData();
                }, 240);
            });

            this.elements.perPageSelect?.addEventListener('change', () => {
                this.state.perPage = this.normalizePerPage(this.elements.perPageSelect?.value);
                this.state.page = 1;
                this.loadData();
            });

            this.elements.resetButton?.addEventListener('click', () => {
                this.state.search = '';
                this.state.perPage = 10;
                this.state.page = 1;
                this.syncControls();
                this.clearValidationErrors();
                this.loadData();
            });

            this.elements.root?.addEventListener('click', (event) => {
                const paginationLink = event.target.closest('.admin-table-pagination-link[href]');
                if (paginationLink && this.elements.feedContainer?.contains(paginationLink)) {
                    event.preventDefault();
                    const url = new URL(paginationLink.href, window.location.origin);
                    this.state.page = Math.max(1, Number(url.searchParams.get('page')) || 1);
                    this.loadData({ preserveScroll: true });
                    return;
                }

                const closeButton = event.target.closest('[data-modal-close]');
                if (closeButton) {
                    this.closeConfirm();
                }
            });

            this.elements.root?.addEventListener('submit', (event) => {
                const form = event.target;

                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                if (form.matches('.js-lock-mutation-form')) {
                    event.preventDefault();
                    this.confirmMutation(form);
                }
            });

            this.elements.confirmSubmitButton?.addEventListener('click', async () => {
                if (typeof this.pendingConfirmAction !== 'function') {
                    this.closeConfirm();
                    return;
                }

                const action = this.pendingConfirmAction;
                this.pendingConfirmAction = null;

                try {
                    this.feedbackUI?.setConfirmBusy(true, 'Working...');
                    await action();
                } finally {
                    this.feedbackUI?.setConfirmBusy(false);
                    this.closeConfirm();
                }
            });

            this.elements.settingsForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                await this.submitSettings();
            });

            this.elements.settingsForm?.addEventListener('input', (event) => {
                this.clearValidationErrorForField(event.target);
            });

            this.elements.settingsForm?.addEventListener('change', (event) => {
                this.clearValidationErrorForField(event.target);
            });

            window.addEventListener('popstate', () => {
                this.syncStateFromLocation();
                this.syncControls();
                this.loadData({ updateHistory: false });
            });

            window.addEventListener('pageshow', () => {
                this.syncControls();
                this.restoreSettingsForm();
            });
        }

        async loadData(options = {}) {
            const requestId = ++this.requestSequence;
            const shouldUpdateHistory = options.updateHistory !== false;

            this.fetchController?.abort?.();

            this.state.loading = true;
            this.setLoadingState(true);
            this.fetchController = new AbortController();

            try {
                const url = new URL(this.config?.routes?.index || window.location.href, window.location.origin);
                url.searchParams.set('search', this.state.search);
                url.searchParams.set('per_page', String(this.state.perPage));
                url.searchParams.set('page', String(this.state.page));

                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: this.fetchController.signal,
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok || !payload?.success) {
                    throw new Error(payload?.message || 'Unable to refresh account lock data right now.');
                }

                if (requestId !== this.requestSequence) {
                    return;
                }

                this.applyPayload(payload);

                if (shouldUpdateHistory) {
                    this.pushState();
                }
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                this.showToast('error', 'Refresh failed', error?.message || 'Unable to refresh the account lock page right now.');
            } finally {
                if (requestId === this.requestSequence) {
                    this.state.loading = false;
                    this.setLoadingState(false);
                    this.fetchController = null;
                }
            }
        }

        applyPayload(payload) {
            this.config.settings = {
                ...(this.config?.settings || {}),
                ...(payload?.config || {}),
            };

            if (payload?.fragments?.header_meta && this.elements.headerMeta) {
                this.elements.headerMeta.innerHTML = payload.fragments.header_meta;
            }

            if (payload?.fragments?.summary && this.elements.summaryContainer) {
                this.elements.summaryContainer.innerHTML = payload.fragments.summary;
            }

            if (this.elements.monitoringContainer) {
                this.elements.monitoringContainer.innerHTML = payload?.fragments?.monitoring || '';
            }

            if (payload?.fragments?.feed && this.elements.feedContainer) {
                this.elements.feedContainer.innerHTML = payload.fragments.feed;
            }

            if (payload?.fragments?.bulk && this.elements.bulkContainer) {
                this.elements.bulkContainer.innerHTML = payload.fragments.bulk;
            }

            this.state.search = String(payload?.filters?.search || '');
            this.state.perPage = this.normalizePerPage(payload?.filters?.per_page);
            this.state.page = Math.max(1, Number(payload?.filters?.page) || 1);
            this.syncControls();
            this.syncSettingsForm(payload?.config || {});
        }

        async submitMutation(form) {
            const submitButton = form.querySelector('button[type="submit"]');
            const label = submitButton?.textContent?.trim() || 'Processing';
            this.setButtonBusy(submitButton, true, label);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(form),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok || !payload?.success) {
                    throw new Error(payload?.message || 'Unable to complete this security action.');
                }

                this.showToast('success', 'Action completed', payload.message || 'Security action completed successfully.');
                await this.loadData({ preserveScroll: true });
            } catch (error) {
                this.showToast('error', 'Action failed', error?.message || 'Unable to complete this security action.');
            } finally {
                this.setButtonBusy(submitButton, false, label);
            }
        }

        confirmMutation(form) {
            const title = form.dataset.confirmTitle || 'Confirm action';
            const message = form.dataset.confirmMessage || 'Are you sure you want to continue?';
            const detail = form.dataset.confirmDetail || '';
            const variant = form.dataset.confirmVariant || 'primary';

            if (!this.feedbackUI) {
                this.submitMutation(form);
                return;
            }

            this.pendingConfirmAction = () => this.submitMutation(form);
            this.feedbackUI.openConfirm({
                title,
                message,
                detail,
                variant,
                buttonVariant: variant === 'danger' ? 'danger' : 'primary',
                confirmText: 'Continue',
            });
        }

        async submitSettings() {
            const form = this.elements.settingsForm;

            if (!form) {
                return;
            }

            this.clearValidationErrors();
            this.setButtonBusy(this.elements.settingsSubmitButton, true, 'Saving...');

            const formData = new FormData(form);

            if (!formData.has('rate_limiting_enabled')) {
                formData.append('rate_limiting_enabled', '0');
            }

            if (!formData.has('email_unlock_enabled')) {
                formData.append('email_unlock_enabled', '0');
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const payload = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    this.applyValidationErrors(payload?.errors || {});
                    this.showToast('warning', 'Review fields', payload?.message || 'Please fix the highlighted settings fields.');
                    return;
                }

                if (!response.ok || !payload?.success) {
                    throw new Error(payload?.message || 'Unable to save the security settings right now.');
                }

                this.config.settings = {
                    ...(this.config?.settings || {}),
                    ...(payload?.settings || {}),
                };
                this.showToast('success', 'Settings saved', payload.message || 'Security settings updated successfully.');
                await this.loadData({ preserveScroll: true });
            } catch (error) {
                this.showToast('error', 'Save failed', error?.message || 'Unable to save the security settings right now.');
            } finally {
                this.setButtonBusy(this.elements.settingsSubmitButton, false, 'Save Policy');
            }
        }

        applyValidationErrors(errors) {
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const input = this.elements.settingsForm?.querySelector(`[name="${field}"]`);
                const errorNode = this.elements.settingsForm?.querySelector(`[data-error-for="${field}"]`);

                if (input) {
                    input.classList.add('is-invalid');
                    input.setAttribute('aria-invalid', 'true');
                }

                if (errorNode) {
                    errorNode.textContent = Array.isArray(messages) ? messages[0] : String(messages || '');
                    errorNode.hidden = false;
                }
            });
        }

        clearValidationErrors() {
            this.elements.settingsForm?.querySelectorAll('.is-invalid').forEach((element) => {
                element.classList.remove('is-invalid');
                element.removeAttribute('aria-invalid');
            });

            this.elements.settingsForm?.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
                element.hidden = true;
            });
        }

        clearValidationErrorForField(field) {
            if (!(field instanceof HTMLElement) || !this.elements.settingsForm?.contains(field)) {
                return;
            }

            const fieldName = field.getAttribute('name');

            if (!fieldName) {
                return;
            }

            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');

            const errorNode = this.elements.settingsForm.querySelector(`[data-error-for="${fieldName}"]`);

            if (errorNode) {
                errorNode.textContent = '';
                errorNode.hidden = true;
            }
        }

        restoreSettingsForm() {
            this.clearValidationErrors();
            this.syncSettingsForm(this.config?.settings || {});
        }

        syncSettingsForm(config) {
            if (!this.elements.settingsForm || !config) {
                return;
            }

            const maxAttempts = this.elements.settingsForm.querySelector('#max_attempts');
            const lockoutDuration = this.elements.settingsForm.querySelector('#lockout_duration');
            const rateLimitingEnabled = this.elements.settingsForm.querySelector('#rate_limiting_enabled');
            const emailUnlockEnabled = this.elements.settingsForm.querySelector('#email_unlock_enabled');

            if (maxAttempts && Number.isFinite(Number(config.max_attempts))) {
                maxAttempts.value = String(config.max_attempts);
            }

            if (lockoutDuration && Number.isFinite(Number(config.lockout_duration))) {
                lockoutDuration.value = String(config.lockout_duration);
            }

            if (rateLimitingEnabled) {
                rateLimitingEnabled.checked = Boolean(config.rate_limiting_enabled);
            }

            if (emailUnlockEnabled) {
                emailUnlockEnabled.checked = Boolean(config.email_unlock_enabled);
            }
        }

        syncControls() {
            if (this.elements.searchInput) {
                this.elements.searchInput.value = this.state.search;
            }

            if (this.elements.perPageSelect) {
                this.elements.perPageSelect.value = String(this.state.perPage);
            }
        }

        syncStateFromLocation() {
            const url = new URL(window.location.href);
            this.state.search = url.searchParams.get('search')?.trim() || '';
            this.state.perPage = this.normalizePerPage(url.searchParams.get('per_page') || this.state.perPage);
            this.state.page = Math.max(1, Number(url.searchParams.get('page')) || 1);
        }

        pushState() {
            const url = new URL(window.location.href);

            if (this.state.search) {
                url.searchParams.set('search', this.state.search);
            } else {
                url.searchParams.delete('search');
            }

            if (this.state.perPage !== 10) {
                url.searchParams.set('per_page', String(this.state.perPage));
            } else {
                url.searchParams.delete('per_page');
            }

            if (this.state.page > 1) {
                url.searchParams.set('page', String(this.state.page));
            } else {
                url.searchParams.delete('page');
            }

            window.history.replaceState({}, '', url.toString());
        }

        setLoadingState(isLoading) {
            this.elements.feedContainer?.classList.toggle('is-loading', Boolean(isLoading));
            this.elements.feedContainer?.setAttribute('aria-busy', isLoading ? 'true' : 'false');
            this.elements.summaryContainer?.classList.toggle('is-loading', Boolean(isLoading));
        }

        setButtonBusy(button, isBusy, label) {
            if (!button) {
                return;
            }

            const defaultMarkup = button.dataset.defaultMarkup || button.innerHTML;
            if (!button.dataset.defaultMarkup) {
                button.dataset.defaultMarkup = defaultMarkup;
            }

            button.disabled = Boolean(isBusy);
            button.innerHTML = isBusy
                ? `<span class="lock-inline-spinner" aria-hidden="true"></span><span>${this.escapeHtml(label || 'Working...')}</span>`
                : button.dataset.defaultMarkup;
        }

        closeConfirm() {
            if (this.feedbackUI) {
                this.feedbackUI.closeConfirm();
                this.feedbackUI.resetConfirm();
            }

            this.pendingConfirmAction = null;
        }

        consumeFlash() {
            if (this.config?.flash?.success) {
                this.showToast('success', 'Done', this.config.flash.success);
            }

            if (this.config?.flash?.error) {
                this.showToast('error', 'Notice', this.config.flash.error);
            }
        }

        showToast(type, title, message) {
            if (this.feedbackUI) {
                this.feedbackUI.showToast(type, title, message);
                return;
            }

            window.alert(message);
        }

        normalizePerPage(value) {
            const allowed = [10, 20, 50, 100];
            const perPage = Number(value) || 10;

            return allowed.includes(perPage) ? perPage : 10;
        }

        escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const manager = new AccountLockManager(window.accountLockManagementConfig || {});
        manager.init();
        window.accountLockManager = manager;
    });
})();
