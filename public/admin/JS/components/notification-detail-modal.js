(function () {
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDateTime(dateValue) {
        const date = dateValue ? new Date(dateValue) : null;

        if (!date || Number.isNaN(date.getTime())) {
            return 'Unknown';
        }

        return new Intl.DateTimeFormat(undefined, {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date);
    }

    class AdminNotificationDetailModal {
        constructor(options = {}) {
            this.elements = {
                modal: document.getElementById(options.modalId || 'notificationDetailModal'),
                body: document.getElementById(options.bodyId || 'notificationDetailModalBody'),
                okButton: document.getElementById(options.okButtonId || 'notificationDetailOkBtn'),
            };
            this.state = {
                activeNotificationId: null,
                previousFocus: null,
            };

            this.boundHandleBackdropClick = this.handleBackdropClick.bind(this);
            this.boundHandleKeydown = this.handleKeydown.bind(this);

            this.bindEvents();
        }

        bindEvents() {
            if (!this.elements.modal) {
                return;
            }

            this.elements.modal.addEventListener('click', this.boundHandleBackdropClick);
            this.elements.modal.querySelectorAll('[data-notification-detail-close]').forEach((element) => {
                element.addEventListener('click', () => this.close());
            });
            this.elements.okButton?.addEventListener('click', () => this.close());
            document.addEventListener('keydown', this.boundHandleKeydown);
        }

        handleBackdropClick(event) {
            if (event.target === this.elements.modal) {
                this.close();
            }
        }

        handleKeydown(event) {
            if (event.key === 'Escape' && this.isOpen()) {
                this.close();
            }
        }

        isOpen() {
            return Boolean(this.elements.modal?.classList.contains('is-open'));
        }

        openLoading(notification) {
            this.open(notification?.id);

            if (!this.elements.body) {
                return;
            }

            this.elements.body.innerHTML = `
                <div class="notification-detail-loading">
                    <span class="notification-spinner" aria-hidden="true"></span>
                    <p>Loading details for ${escapeHtml(notification?.title || 'this notification')}...</p>
                </div>
            `;
        }

        showNotification(notification, options = {}) {
            if (!this.elements.body) {
                return;
            }

            const alertMarkup = options.errorMessage
                ? `<div class="notification-detail-alert">${escapeHtml(options.errorMessage)}</div>`
                : '';

            this.open(notification?.id);
            this.elements.body.innerHTML = `
                ${alertMarkup}
                <div class="notification-detail-hero">
                    <div class="notification-detail-hero-icon" aria-hidden="true">
                        <i data-lucide="${escapeHtml(notification.icon)}" class="w-6 h-6"></i>
                    </div>
                    <div class="notification-detail-hero-copy">
                        <span class="notification-detail-overline">Admin Notification</span>
                        <h4 class="notification-detail-hero-title">${escapeHtml(notification.title)}</h4>
                        <p>${escapeHtml(options.stale ? 'Showing the latest available content from the list while the full detail request is unavailable.' : 'Review the complete notification message and timestamp below.')}</p>
                        <div class="notification-detail-type-row">
                            <span class="notification-detail-badge ${escapeHtml(notification.category)}">${escapeHtml(notification.typeLabel)}</span>
                        </div>
                    </div>
                </div>
                <div class="notification-detail-meta-grid">
                    <div class="notification-detail-meta-card">
                        <span class="notification-detail-meta-label">Date & Time</span>
                        <span class="notification-detail-meta-value">${escapeHtml(formatDateTime(notification.createdAt))}</span>
                    </div>
                    <div class="notification-detail-meta-card">
                        <span class="notification-detail-meta-label">Notification Type</span>
                        <span class="notification-detail-meta-value">${escapeHtml(notification.type || 'general')}</span>
                    </div>
                </div>
                <div class="notification-detail-message-panel">
                    <span class="notification-detail-message-label">Full Message</span>
                    <p class="notification-detail-message-text">${escapeHtml(notification.message || 'No additional message was provided.')}</p>
                </div>
            `;

            lucide.createIcons();
        }

        showError(message) {
            this.open(null);

            if (!this.elements.body) {
                return;
            }

            this.elements.body.innerHTML = `
                <div class="notification-detail-empty">
                    <div class="notification-detail-alert">${escapeHtml(message || 'Unable to load notification details right now.')}</div>
                </div>
            `;
        }

        open(notificationId) {
            if (!this.elements.modal) {
                return;
            }

            if (!this.isOpen()) {
                this.state.previousFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
            }

            this.state.activeNotificationId = notificationId;
            this.elements.modal.classList.add('is-open');
            this.elements.modal.setAttribute('aria-hidden', 'false');
            window.setTimeout(() => this.elements.okButton?.focus(), 20);
        }

        close() {
            if (!this.elements.modal) {
                return;
            }

            this.elements.modal.classList.remove('is-open');
            this.elements.modal.setAttribute('aria-hidden', 'true');
            this.state.activeNotificationId = null;
            this.state.previousFocus?.focus?.();
        }

        getActiveNotificationId() {
            return this.state.activeNotificationId;
        }
    }

    window.AdminNotificationDetailModal = AdminNotificationDetailModal;
})();
