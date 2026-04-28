@once
    <script>
        (() => {
            if (window.LibraryAccountDeletionUI) {
                return;
            }

            class LibraryAccountDeletionUI {
                constructor(root) {
                    this.root = root;
                    this.state = {
                        allowed: root.dataset.allowed === 'true',
                        adminBlocked: root.dataset.adminBlocked === 'true',
                        requestInFlight: false,
                        previousFocus: null,
                    };
                    this.elements = {
                        trigger: root.querySelector('[data-account-deletion-trigger]'),
                        feedback: root.querySelector('[data-account-deletion-feedback]'),
                        feedbackTitle: root.querySelector('.account-deletion-zone__feedback-title'),
                        feedbackMessage: root.querySelector('.account-deletion-zone__feedback-message'),
                        feedbackDetail: root.querySelector('.account-deletion-zone__feedback-detail'),
                        modal: root.querySelector('[data-account-deletion-modal]'),
                        modalMessage: root.querySelector('[data-account-deletion-modal-message]'),
                        modalDetail: root.querySelector('[data-account-deletion-modal-detail]'),
                        confirmationGroup: root.querySelector('[data-account-deletion-confirmation-group]'),
                        confirmationInput: root.querySelector('[data-account-deletion-confirmation-input]'),
                        confirmationError: root.querySelector('[data-account-deletion-error]'),
                        confirmButton: root.querySelector('[data-account-deletion-confirm]'),
                        confirmLabel: root.querySelector('[data-account-deletion-confirm-label]'),
                        closeButtons: Array.from(root.querySelectorAll('[data-account-deletion-close]')),
                    };
                    this.toastManager = null;
                }

                init() {
                    if (!this.elements.trigger || !this.elements.modal || !this.elements.confirmButton) {
                        return;
                    }

                    this.elements.trigger.addEventListener('click', () => this.handleTriggerClick());
                    this.elements.confirmationInput?.addEventListener('input', () => this.handleConfirmationInput());
                    this.elements.closeButtons.forEach((button) => {
                        button.addEventListener('click', () => this.closeModal());
                    });

                    this.elements.modal.addEventListener('click', (event) => {
                        if (event.target === this.elements.modal && !this.state.requestInFlight) {
                            this.closeModal();
                        }
                    });

                    this.elements.confirmButton.addEventListener('click', () => this.handleConfirmClick());
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape' && !this.elements.modal.hidden && !this.state.requestInFlight) {
                            this.closeModal();
                        }
                    });

                    this.handleConfirmationInput();
                }

                handleTriggerClick() {
                    if (this.elements.trigger.disabled && !this.state.adminBlocked) {
                        return;
                    }

                    this.clearConfirmationError();
                    this.showModal();
                }

                showModal() {
                    this.state.previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
                    this.elements.modal.hidden = false;
                    document.body.style.overflow = 'hidden';

                    if (this.state.allowed) {
                        this.elements.confirmationInput.value = '';
                        this.handleConfirmationInput();
                        window.setTimeout(() => this.elements.confirmationInput?.focus(), 30);
                    } else {
                        window.setTimeout(() => this.elements.confirmButton.focus(), 30);
                    }
                }

                closeModal() {
                    this.elements.modal.hidden = true;
                    document.body.style.overflow = '';
                    this.state.requestInFlight = false;
                    this.setBusy(false);
                    this.clearConfirmationError();
                    this.state.previousFocus?.focus?.();
                }

                handleConfirmationInput() {
                    if (!this.state.allowed) {
                        return;
                    }

                    const value = String(this.elements.confirmationInput?.value || '');
                    const matches = value === 'DELETE';

                    this.elements.confirmButton.disabled = !matches || this.state.requestInFlight;

                    if (value.length === 0) {
                        this.clearConfirmationError();
                        return;
                    }

                    if (!matches) {
                        this.setConfirmationError('Please type DELETE to confirm');
                        return;
                    }

                    this.clearConfirmationError();
                }

                async handleConfirmClick() {
                    if (!this.state.allowed) {
                        this.closeModal();
                        if (this.state.adminBlocked) {
                            this.showFeedback('warning', 'Action blocked', this.root.dataset.status === 'blocked_admin'
                                ? 'You cannot delete your account because you are an administrator.'
                                : 'Account deletion is not available right now.',
                            'Please contact another administrator for this action.');
                        }
                        return;
                    }

                    if (this.state.requestInFlight) {
                        return;
                    }

                    const confirmationText = String(this.elements.confirmationInput?.value || '');
                    if (confirmationText !== 'DELETE') {
                        this.setConfirmationError('Please type DELETE to confirm');
                        this.elements.confirmButton.disabled = true;
                        this.elements.confirmationInput?.focus();
                        return;
                    }

                    this.state.requestInFlight = true;
                    this.setBusy(true);
                    this.clearConfirmationError();

                    try {
                        const response = await fetch(this.root.dataset.deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            body: JSON.stringify({
                                confirmation_text: confirmationText,
                            }),
                        });

                        const payload = await this.parseResponse(response);

                        if (!response.ok || payload.success === false) {
                            this.handleFailure(response.status, payload);
                            return;
                        }

                        this.closeModal();
                        this.elements.trigger.disabled = true;
                        this.elements.trigger.classList.add('is-disabled');
                        const redirectMessage = payload.message || 'Your account has been deleted successfully.';
                        const redirectDetail = 'Redirecting to the landing page in 3 seconds.';
                        this.showFeedback(
                            'success',
                            'Account deleted',
                            redirectMessage,
                            redirectDetail
                        );

                        const delay = Number(payload.redirect_delay || this.root.dataset.redirectDelay || 3000);
                        const redirectUrl = payload.redirect || this.root.dataset.redirectUrl || '/';
                        this.showToast('success', 'Account deleted', redirectMessage, delay, redirectDetail);

                        window.setTimeout(() => {
                            window.location.assign(redirectUrl);
                        }, delay);
                    } catch (error) {
                        this.showFeedback(
                            'error',
                            'Deletion failed',
                            'Unable to delete your account right now.',
                            'Please try again in a moment.'
                        );
                    } finally {
                        this.state.requestInFlight = false;
                        this.setBusy(false);
                        this.handleConfirmationInput();
                    }
                }

                showToast(type, title, message, timeout = 3000, detail = '') {
                    const studentFeedback = window.getStudentPortalFeedback?.() || null;
                    if (studentFeedback?.showToast) {
                        studentFeedback.showToast({
                            type,
                            title,
                            message,
                            detail,
                            timeout,
                        });
                        return;
                    }

                    const toastManager = this.getToastManager();
                    if (toastManager?.showToast) {
                        toastManager.showToast({
                            type,
                            title,
                            message,
                            detail,
                            timeout,
                        });
                    }
                }

                getToastManager() {
                    if (this.toastManager) {
                        return this.toastManager;
                    }

                    if (typeof window.ActionFeedbackUI !== 'function') {
                        return null;
                    }

                    const candidates = [
                        { containerId: 'staffSettingsToastContainer', liveRegionId: 'staffSettingsLiveRegion' },
                        { containerId: 'adminSettingsToastContainer', liveRegionId: 'adminSettingsLiveRegion' },
                        { containerId: 'actionFeedbackToastContainer', liveRegionId: 'actionFeedbackLiveRegion' },
                    ];

                    const match = candidates.find((candidate) => {
                        return document.getElementById(candidate.containerId) && document.getElementById(candidate.liveRegionId);
                    });

                    if (!match) {
                        return null;
                    }

                    this.toastManager = new window.ActionFeedbackUI({
                        toast: match,
                    });

                    return this.toastManager;
                }

                handleFailure(status, payload) {
                    const validationErrors = payload?.errors || {};
                    const confirmationError = validationErrors.confirmation_text?.[0] || validationErrors.confirmation?.[0] || '';
                    const accountError = validationErrors.account?.[0] || '';
                    const message = payload?.message || accountError || confirmationError || 'Unable to delete your account right now.';
                    const detail = payload?.detail || '';

                    if (status === 422 && confirmationError) {
                        this.setConfirmationError(confirmationError);
                        this.elements.confirmationInput?.focus();
                        return;
                    }

                    if (status === 403) {
                        this.closeModal();
                    }

                    this.showFeedback(
                        status === 403 ? 'warning' : 'error',
                        status === 403 ? 'Action blocked' : 'Deletion failed',
                        message,
                        detail
                    );
                }

                showFeedback(type, title, message, detail = '') {
                    if (!this.elements.feedback || !this.elements.feedbackTitle || !this.elements.feedbackMessage || !this.elements.feedbackDetail) {
                        return;
                    }

                    this.elements.feedback.dataset.type = type;
                    this.elements.feedbackTitle.textContent = title;
                    this.elements.feedbackMessage.textContent = message;
                    this.elements.feedbackDetail.textContent = detail;
                    this.elements.feedbackDetail.hidden = detail === '';
                    this.elements.feedback.classList.add('is-visible');
                }

                setConfirmationError(message) {
                    if (this.elements.confirmationError) {
                        this.elements.confirmationError.textContent = message;
                    }
                }

                clearConfirmationError() {
                    this.setConfirmationError('');
                }

                setBusy(loading) {
                    if (!this.elements.confirmButton || !this.elements.confirmLabel) {
                        return;
                    }

                    this.elements.confirmButton.disabled = loading || (!this.state.allowed);

                    if (loading) {
                        this.elements.confirmLabel.dataset.originalText = this.elements.confirmLabel.textContent || 'Delete Account';
                        this.elements.confirmLabel.innerHTML = '<span class="account-deletion-zone__button-spinner" aria-hidden="true"></span><span>Deleting...</span>';
                        return;
                    }

                    const label = this.elements.confirmLabel.dataset.originalText || (this.state.allowed ? 'Delete Account' : 'Close');
                    this.elements.confirmLabel.textContent = label;
                }

                csrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                }

                async parseResponse(response) {
                    const text = await response.text();
                    if (!text) {
                        return {};
                    }

                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        return {};
                    }
                }
            }

            window.LibraryAccountDeletionUI = LibraryAccountDeletionUI;

            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-account-deletion-root]').forEach((root) => {
                    new LibraryAccountDeletionUI(root).init();
                });
            });
        })();
    </script>
@endonce
