(function () {
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getRelativeTime(dateValue) {
        const date = dateValue ? new Date(dateValue) : null;

        if (!date || Number.isNaN(date.getTime())) {
            return 'Unknown time';
        }

        const seconds = Math.floor((Date.now() - date.getTime()) / 1000);
        const intervals = {
            year: 31536000,
            month: 2592000,
            week: 604800,
            day: 86400,
            hour: 3600,
            minute: 60,
        };

        for (const [label, amount] of Object.entries(intervals)) {
            const interval = Math.floor(seconds / amount);
            if (interval >= 1) {
                return interval === 1 ? `${interval} ${label} ago` : `${interval} ${label}s ago`;
            }
        }

        return 'Just now';
    }

    function renderNotificationItem(notification) {
        const isUnread = !notification.readAt;

        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${escapeHtml(notification.id)}">
                <div class="notification-item-main">
                    <div class="notification-icon ${escapeHtml(notification.category)}" aria-hidden="true">
                        <i data-lucide="${escapeHtml(notification.icon)}" class="w-5 h-5"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title-row">
                            <div class="notification-title">${escapeHtml(notification.title)}</div>
                            ${isUnread ? '<span class="notification-unread-dot" aria-label="Unread notification"></span>' : ''}
                        </div>
                        <div class="notification-message">${escapeHtml(notification.message)}</div>
                        <div class="notification-meta">
                            <span class="notification-time">${escapeHtml(getRelativeTime(notification.createdAt))}</span>
                            <span class="notification-type-chip ${escapeHtml(notification.category)}">${escapeHtml(notification.typeLabel)}</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="notification-delete-btn" data-notification-action="delete" data-id="${escapeHtml(notification.id)}" title="Delete notification" aria-label="Delete notification">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        `;
    }

    class AdminNotificationList {
        constructor(options = {}) {
            this.root = options.root || null;
            this.onNotificationClick = options.onNotificationClick || (() => {});
            this.onDeleteClick = options.onDeleteClick || (() => {});
            this.onRetryClick = options.onRetryClick || (() => {});
            this.notifications = [];

            this.handleClick = this.handleClick.bind(this);
            this.root?.addEventListener('click', this.handleClick);
        }

        handleClick(event) {
            const retryButton = event.target.closest('[data-notification-action="retry"]');
            if (retryButton) {
                this.onRetryClick();
                return;
            }

            const deleteButton = event.target.closest('[data-notification-action="delete"]');
            if (deleteButton) {
                event.stopPropagation();
                this.onDeleteClick(deleteButton.getAttribute('data-id'));
                return;
            }

            const item = event.target.closest('.notification-item[data-id]');
            if (item) {
                this.onNotificationClick(item.getAttribute('data-id'));
            }
        }

        setLoading() {
            if (!this.root) {
                return;
            }

            this.root.innerHTML = `
                <div class="notification-state">
                    <span class="notification-spinner" aria-hidden="true"></span>
                    <div class="notification-state-title">Loading notifications</div>
                    <div class="notification-state-copy">Checking for the latest updates...</div>
                </div>
            `;
        }

        setError(message) {
            if (!this.root) {
                return;
            }

            this.root.innerHTML = `
                <div class="notification-state">
                    <div class="notification-state-title">Unable to load notifications</div>
                    <div class="notification-state-copy">${escapeHtml(message || 'Something went wrong while loading notifications.')}</div>
                    <button type="button" class="notification-state-action" data-notification-action="retry">Try again</button>
                </div>
            `;
        }

        render(notifications) {
            this.notifications = Array.isArray(notifications) ? [...notifications] : [];

            if (!this.root) {
                return;
            }

            if (this.notifications.length === 0) {
                this.root.innerHTML = `
                    <div class="notification-state">
                        <div class="notification-state-title">No notifications</div>
                        <div class="notification-state-copy">You&apos;re all caught up for now.</div>
                    </div>
                `;
                return;
            }

            this.root.innerHTML = this.notifications.map(renderNotificationItem).join('');
            lucide.createIcons();
        }

        getNotification(notificationId) {
            return this.notifications.find((notification) => String(notification.id) === String(notificationId)) || null;
        }

        markAsRead(notificationId, readAt = null) {
            this.notifications = this.notifications.map((notification) => (
                String(notification.id) === String(notificationId)
                    ? { ...notification, readAt: readAt || notification.readAt || new Date().toISOString() }
                    : notification
            ));

            this.render(this.notifications);
        }

        markAllAsRead() {
            const now = new Date().toISOString();
            this.notifications = this.notifications.map((notification) => ({
                ...notification,
                readAt: notification.readAt || now,
            }));

            this.render(this.notifications);
        }

        remove(notificationId) {
            this.notifications = this.notifications.filter((notification) => String(notification.id) !== String(notificationId));
            this.render(this.notifications);
        }
    }

    window.AdminNotificationList = AdminNotificationList;
})();
