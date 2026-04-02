(function () {
    const routeIdPatterns = [/__ID__/g, /:id\b/g, /%3Aid/g];

    function buildNotificationUrl(template, notificationId) {
        let resolved = String(template || '');

        routeIdPatterns.forEach((pattern) => {
            resolved = resolved.replace(pattern, String(notificationId));
        });

        return resolved;
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    async function request(url, options = {}) {
        const settings = { method: 'GET', credentials: 'same-origin', ...options };
        const method = String(settings.method || 'GET').toUpperCase();
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(settings.headers || {}),
        };

        if (method !== 'GET' && method !== 'HEAD') {
            headers['X-CSRF-TOKEN'] = getCsrfToken();
            headers['Content-Type'] = headers['Content-Type'] || 'application/json';
        }

        settings.method = method;
        settings.headers = headers;

        const response = await fetch(url, settings);
        let payload = null;

        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!response.ok) {
            throw new Error(payload?.message || payload?.error || `Request failed with status ${response.status}`);
        }

        return payload;
    }

    function resolveCategory(type) {
        const normalizedType = String(type || '').toLowerCase();

        if (
            normalizedType.includes('overdue') ||
            normalizedType.includes('reminder') ||
            normalizedType.includes('rejected') ||
            normalizedType.includes('locked') ||
            normalizedType.includes('suspicious')
        ) {
            return 'alert';
        }

        if (
            normalizedType.includes('warning') ||
            normalizedType.includes('due_soon') ||
            normalizedType.includes('pending')
        ) {
            return 'warning';
        }

        if (
            normalizedType.includes('approved') ||
            normalizedType.includes('paid') ||
            normalizedType.includes('confirmed') ||
            normalizedType.includes('success') ||
            normalizedType.includes('unlocked')
        ) {
            return 'success';
        }

        return 'info';
    }

    function resolveIcon(type, category) {
        const icons = {
            'book.overdue': 'alert-circle',
            'book.due_soon': 'clock',
            'book.returned': 'book-open',
            'book.new': 'book-open',
            'fine.created': 'indian-rupee',
            'fine.reminder': 'alert-triangle',
            'fine.waived_by_staff': 'shield-check',
            'request.approved': 'check-circle',
            'request.rejected': 'x-circle',
            'request.pending': 'clock',
            'payment.confirmed': 'wallet',
            'security.account_locked': 'shield-alert',
            'security.account_unlock': 'shield-check',
        };

        if (icons[type]) {
            return icons[type];
        }

        switch (category) {
            case 'alert':
                return 'alert-triangle';
            case 'warning':
                return 'clock';
            case 'success':
                return 'check-circle';
            default:
                return 'bell';
        }
    }

    function resolveTypeLabel(category) {
        switch (category) {
            case 'alert':
                return 'Alert';
            case 'warning':
                return 'Warning';
            case 'success':
                return 'Success';
            default:
                return 'Info';
        }
    }

    function normalizeNotification(notification) {
        const data = notification || {};
        const category = data.category || resolveCategory(data.type);

        return {
            id: data.id,
            title: data.title || 'Notification',
            message: data.message || '',
            type: data.type || 'general',
            category,
            typeLabel: data.type_label || resolveTypeLabel(category),
            icon: data.icon || resolveIcon(data.type, category),
            createdAt: data.created_at || null,
            updatedAt: data.updated_at || null,
            readAt: data.read_at || null,
            payload: data.data || {},
            relatedModel: data.related_model || null,
            relatedId: data.related_id || null,
        };
    }

    async function fetchNotifications() {
        const payload = await request(window.notificationAPI.index);

        return {
            ...payload,
            data: Array.isArray(payload?.data) ? payload.data.map(normalizeNotification) : [],
        };
    }

    async function fetchNotificationDetail(notificationId) {
        const payload = await request(buildNotificationUrl(window.notificationAPI.show, notificationId));
        return normalizeNotification(payload?.data || payload?.notification || payload);
    }

    async function fetchUnreadCount() {
        return request(window.notificationAPI.unreadCount);
    }

    async function markAsRead(notificationId) {
        const payload = await request(buildNotificationUrl(window.notificationAPI.markRead, notificationId), {
            method: 'POST',
        });

        return {
            ...payload,
            data: payload?.data ? normalizeNotification(payload.data) : null,
        };
    }

    async function markAllAsRead() {
        return request(window.notificationAPI.markAllRead, {
            method: 'POST',
        });
    }

    async function deleteNotification(notificationId) {
        return request(buildNotificationUrl(window.notificationAPI.delete, notificationId), {
            method: 'DELETE',
        });
    }

    async function deleteAllRead() {
        return request(window.notificationAPI.deleteAll, {
            method: 'POST',
        });
    }

    window.AdminNotificationApi = {
        buildNotificationUrl,
        fetchNotifications,
        fetchNotificationDetail,
        fetchUnreadCount,
        markAsRead,
        markAllAsRead,
        deleteNotification,
        deleteAllRead,
        normalizeNotification,
        resolveCategory,
        resolveIcon,
        resolveTypeLabel,
    };
})();
