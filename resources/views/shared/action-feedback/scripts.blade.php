@once
    <script>
        (() => {
            if (typeof window.ActionFeedbackUI === 'function') {
                return;
            }

            const defaultIcons = {
                success: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"></path></svg>',
                error: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>',
                warning: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>',
                info: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>',
                close: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 6-12 12"></path><path d="m6 6 12 12"></path></svg>',
            };

            window.ActionFeedbackUI = class ActionFeedbackUI {
                constructor(config = {}) {
                    this.config = config;
                    this.ids = {
                        confirm: {
                            modalId: config.confirm?.modalId || 'actionFeedbackConfirmModal',
                            iconId: config.confirm?.iconId || 'actionFeedbackConfirmIcon',
                            titleId: config.confirm?.titleId || 'actionFeedbackConfirmTitle',
                            messageId: config.confirm?.messageId || 'actionFeedbackConfirmMessage',
                            detailId: config.confirm?.detailId || 'actionFeedbackConfirmDetail',
                            submitButtonId: config.confirm?.submitButtonId || 'actionFeedbackConfirmSubmitBtn',
                        },
                        toast: {
                            containerId: config.toast?.containerId || 'actionFeedbackToastContainer',
                            liveRegionId: config.toast?.liveRegionId || 'actionFeedbackLiveRegion',
                        },
                    };
                    this.defaultConfirmLabel = config.confirm?.confirmLabel || 'Continue';
                    this.toastIcons = { ...defaultIcons, ...(config.toastIcons || {}) };
                    this.confirmIcons = { ...defaultIcons, ...(config.confirmIcons || {}) };
                    this.elements = {};
                    this.modalState = new Map();
                    this.pendingConfirm = null;
                    this.cacheElements();
                }

                cacheElements() {
                    this.elements.confirmModal = document.getElementById(this.ids.confirm.modalId);
                    this.elements.confirmIcon = document.getElementById(this.ids.confirm.iconId);
                    this.elements.confirmTitle = document.getElementById(this.ids.confirm.titleId);
                    this.elements.confirmMessage = document.getElementById(this.ids.confirm.messageId);
                    this.elements.confirmDetail = document.getElementById(this.ids.confirm.detailId);
                    this.elements.confirmSubmitBtn = document.getElementById(this.ids.confirm.submitButtonId);
                    this.elements.toastContainer = document.getElementById(this.ids.toast.containerId);
                    this.elements.liveRegion = document.getElementById(this.ids.toast.liveRegionId);
                }

                getConfirmButton() {
                    return this.elements.confirmSubmitBtn || null;
                }

                getConfirmCancelButtons() {
                    if (!this.elements.confirmModal) {
                        return [];
                    }

                    return Array.from(
                        this.elements.confirmModal.querySelectorAll(`[data-modal-close="${this.ids.confirm.modalId}"]`)
                    );
                }

                openConfirm(options = {}) {
                    const variant = this.normalizeVariant(options.variant || 'primary');
                    const buttonVariant = this.normalizeVariant(options.buttonVariant || variant);
                    const title = options.title || 'Confirm Action';
                    const message = options.message || 'Are you sure you want to continue?';
                    const detail = options.detail || '';
                    const confirmText = options.confirmText || this.defaultConfirmLabel;
                    const cancelText = options.cancelText || options.cancelLabel || '';

                    if (this.elements.confirmIcon) {
                        this.elements.confirmIcon.className = `action-feedback-confirm-icon ${variant}`;
                        this.elements.confirmIcon.innerHTML = options.iconMarkup || this.confirmIcons[variant] || this.confirmIcons.info;
                    }

                    if (this.elements.confirmTitle) {
                        this.elements.confirmTitle.textContent = title;
                    }

                    if (this.elements.confirmMessage) {
                        this.elements.confirmMessage.textContent = message;
                    }

                    if (this.elements.confirmDetail) {
                        this.elements.confirmDetail.textContent = detail;
                        this.elements.confirmDetail.hidden = !detail;
                    }

                    if (this.elements.confirmSubmitBtn) {
                        this.elements.confirmSubmitBtn.className = `action-feedback-confirm-btn ${buttonVariant}`;
                        this.elements.confirmSubmitBtn.dataset.defaultLabel = confirmText;
                        this.setConfirmBusy(false, confirmText);
                    }

                    if (cancelText) {
                        this.getConfirmCancelButtons().forEach((button) => {
                            if (!button.dataset.defaultLabel) {
                                button.dataset.defaultLabel = button.textContent || 'Cancel';
                            }

                            button.textContent = cancelText;
                        });
                    }

                    this.openModal(this.ids.confirm.modalId, this.elements.confirmSubmitBtn);
                }

                closeConfirm() {
                    this.closeModal(this.ids.confirm.modalId);
                }

                resetConfirm() {
                    if (!this.elements.confirmSubmitBtn) {
                        return;
                    }

                    const label = this.elements.confirmSubmitBtn.dataset.defaultLabel || this.defaultConfirmLabel;
                    this.elements.confirmSubmitBtn.className = 'action-feedback-confirm-btn primary';
                    this.setConfirmBusy(false, label);
                }

                confirm(options = {}) {
                    if (!this.elements.confirmModal || !this.elements.confirmSubmitBtn) {
                        console.warn('Shared action feedback confirmation is unavailable.', options);
                        return Promise.resolve(false);
                    }

                    this.cancelPendingConfirm(false);

                    const modal = this.elements.confirmModal;
                    const submitButton = this.elements.confirmSubmitBtn;
                    const cancelButtons = this.getConfirmCancelButtons();
                    const cancelText = options.cancelText || options.cancelLabel || '';

                    cancelButtons.forEach((button) => {
                        if (!button.dataset.defaultLabel) {
                            button.dataset.defaultLabel = button.textContent || 'Cancel';
                        }

                        button.textContent = cancelText || button.dataset.defaultLabel;
                    });

                    this.openConfirm(options);

                    return new Promise((resolve) => {
                        const request = {
                            settled: false,
                            resolve: (value) => {
                                if (request.settled) {
                                    return;
                                }

                                request.settled = true;
                                request.cleanup?.();
                                request.restoreCancelButtons?.();
                                this.resetConfirm();
                                this.closeConfirm();
                                resolve(value);
                            },
                        };

                        const handleSubmit = (event) => {
                            event.preventDefault();
                            request.resolve(true);
                        };

                        const handleCancel = (event) => {
                            event.preventDefault();
                            request.resolve(false);
                        };

                        const handleBackdrop = (event) => {
                            if (event.target === modal) {
                                request.resolve(false);
                            }
                        };

                        const handleKeydown = (event) => {
                            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                                event.preventDefault();
                                request.resolve(false);
                            }
                        };

                        request.cleanup = () => {
                            submitButton.removeEventListener('click', handleSubmit);
                            cancelButtons.forEach((button) => button.removeEventListener('click', handleCancel));
                            modal.removeEventListener('click', handleBackdrop);
                            document.removeEventListener('keydown', handleKeydown);

                            if (this.pendingConfirm === request) {
                                this.pendingConfirm = null;
                            }
                        };

                        request.restoreCancelButtons = () => {
                            cancelButtons.forEach((button) => {
                                if (button.dataset.defaultLabel) {
                                    button.textContent = button.dataset.defaultLabel;
                                }
                            });
                        };

                        submitButton.addEventListener('click', handleSubmit);
                        cancelButtons.forEach((button) => button.addEventListener('click', handleCancel));
                        modal.addEventListener('click', handleBackdrop);
                        document.addEventListener('keydown', handleKeydown);

                        this.pendingConfirm = request;
                    });
                }

                cancelPendingConfirm(value = false) {
                    const pendingConfirm = this.pendingConfirm;

                    if (!pendingConfirm) {
                        return;
                    }

                    pendingConfirm.resolve?.(value);
                }

                setConfirmBusy(isBusy, label) {
                    const button = this.elements.confirmSubmitBtn;
                    if (!button) {
                        return;
                    }

                    const resolvedLabel = label || button.dataset.defaultLabel || this.defaultConfirmLabel;
                    if (typeof this.config.setButtonBusy === 'function') {
                        this.config.setButtonBusy(button, isBusy, resolvedLabel);
                        return;
                    }

                    button.disabled = Boolean(isBusy);
                    button.textContent = resolvedLabel;
                }

                showToast(type, title, message, timeout = 4200, detail = '') {
                    if (!this.elements.toastContainer) {
                        return;
                    }

                    const payload = typeof type === 'object' && type !== null
                        ? type
                        : { type, title, message, detail };
                    const normalizedType = this.normalizeToastType(payload.type || 'info');
                    const displayTimeout = Math.max(1800, Number(payload.timeout || timeout || 4200));
                    const toast = document.createElement('div');

                    toast.className = `action-feedback-toast ${normalizedType}`;
                    toast.setAttribute('role', 'status');
                    toast.style.setProperty('--action-feedback-toast-duration', `${displayTimeout}ms`);
                    toast.innerHTML = `
                        <div class="action-feedback-toast-icon" aria-hidden="true">
                            ${payload.iconMarkup || this.toastIcons[normalizedType] || this.toastIcons.info}
                        </div>
                        <div class="action-feedback-toast-copy">
                            <div class="action-feedback-toast-title">${this.escapeHtml(payload.title || 'Notice')}</div>
                            <div class="action-feedback-toast-message">${this.escapeHtml(payload.message || '')}</div>
                            ${payload.detail ? `<div class="action-feedback-toast-detail">${this.escapeHtml(payload.detail)}</div>` : ''}
                        </div>
                        <button type="button" class="action-feedback-toast-close" aria-label="Dismiss notification">
                            ${this.toastIcons.close}
                        </button>
                        <span class="action-feedback-toast-progress" aria-hidden="true"></span>
                    `;

                    this.elements.toastContainer.appendChild(toast);
                    toast.querySelector('.action-feedback-toast-close')?.addEventListener('click', () => this.dismissToast(toast));
                    this.announce(`${payload.title || 'Notice'}. ${payload.message || ''}`.trim());
                    window.setTimeout(() => this.dismissToast(toast), displayTimeout);
                }

                dismissToast(toast) {
                    if (!toast || !toast.parentNode) {
                        return;
                    }

                    toast.classList.add('is-leaving');
                    window.setTimeout(() => toast.remove(), 180);
                }

                announce(message) {
                    if (!this.elements.liveRegion) {
                        return;
                    }

                    this.elements.liveRegion.textContent = '';
                    window.setTimeout(() => {
                        if (this.elements.liveRegion) {
                            this.elements.liveRegion.textContent = message;
                        }
                    }, 20);
                }

                openModal(modalId, focusTarget) {
                    if (typeof this.config.openModal === 'function') {
                        this.config.openModal(modalId, focusTarget);
                        return;
                    }

                    const modal = document.getElementById(modalId);
                    if (!modal) {
                        return;
                    }

                    if (!modal.classList.contains('is-open')) {
                        this.modalState.set(modalId, {
                            previousFocus: document.activeElement instanceof HTMLElement ? document.activeElement : null,
                            previousBodyOverflow: document.body.style.overflow || '',
                        });
                    }

                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    window.setTimeout(() => focusTarget?.focus?.(), 20);
                }

                closeModal(modalId) {
                    if (typeof this.config.closeModal === 'function') {
                        this.config.closeModal(modalId);
                        return;
                    }

                    const modal = document.getElementById(modalId);
                    if (!modal) {
                        return;
                    }

                    const state = this.modalState.get(modalId) || {};

                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = state.previousBodyOverflow || '';
                    this.modalState.delete(modalId);

                    if (typeof state.previousFocus?.focus === 'function') {
                        window.setTimeout(() => state.previousFocus.focus(), 0);
                    }
                }

                normalizeVariant(variant) {
                    return ['primary', 'success', 'warning', 'danger', 'info'].includes(variant)
                        ? variant
                        : 'primary';
                }

                normalizeToastType(type) {
                    return ['success', 'error', 'warning', 'info'].includes(type)
                        ? type
                        : 'info';
                }

                escapeHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }
            };
        })();
    </script>
@endonce
