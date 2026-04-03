lucide.createIcons();

const body = document.body;
const themeToggle = document.getElementById('themeToggle');
const sunIcon = document.getElementById('sunIcon');
const moonIcon = document.getElementById('moonIcon');
const savedTheme = localStorage.getItem('theme') || 'light-theme';

body.classList.remove('light-theme', 'dark-theme');
body.classList.add(savedTheme);
updateThemeIcons(savedTheme);

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const isLight = body.classList.contains('light-theme');
        const newTheme = isLight ? 'dark-theme' : 'light-theme';

        body.classList.toggle('light-theme', !isLight);
        body.classList.toggle('dark-theme', isLight);
        localStorage.setItem('theme', newTheme);
        updateThemeIcons(newTheme);
        lucide.createIcons();
    });
}

function updateThemeIcons(theme) {
    const isDark = theme === 'dark-theme';

    if (sunIcon) {
        sunIcon.style.display = isDark ? 'none' : 'block';
    }

    if (moonIcon) {
        moonIcon.style.display = isDark ? 'block' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initializeStudentNotifications();
    initializeSidebar();
});

function initializeStudentNotifications() {
    const notificationCacheKey = 'student-notifications-cache-v1';
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationPopup = document.getElementById('notificationPopup');
    const notificationCloseBtn = document.getElementById('notificationCloseBtn');
    const notificationBody = document.getElementById('notificationBody');
    const notificationBadge = document.getElementById('notificationBadge');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const deleteAllBtn = document.getElementById('deleteAllBtn');
    const clearConfirmModal = document.getElementById('notificationClearConfirmModal');
    const clearConfirmOkBtn = document.getElementById('notificationClearConfirmOkBtn');

    if (
        !notificationBtn ||
        !notificationPopup ||
        !notificationBody ||
        !notificationBadge ||
        !window.notificationAPI ||
        !window.AdminNotificationApi ||
        !window.AdminNotificationList ||
        !window.AdminNotificationDetailModal
    ) {
        return;
    }

    const api = window.AdminNotificationApi;
    const list = new window.AdminNotificationList({
        root: notificationBody,
        onNotificationClick: handleNotificationClick,
        onDeleteClick: handleDeleteNotification,
        onRetryClick: () => loadNotifications(),
    });
    const modal = new window.AdminNotificationDetailModal();

    const state = {
        unreadCount: 0,
        notificationsCache: [],
        hasLoadedOnce: false,
        fetchPromise: null,
        lastLoadedAt: 0,
        refreshTimer: null,
    };

    notificationBtn.addEventListener('click', (event) => {
        event.stopPropagation();

        const shouldOpen = !notificationPopup.classList.contains('active');
        setPopupOpen(shouldOpen);

        if (shouldOpen) {
            openNotificationsPanel();
        }
    });

    notificationCloseBtn?.addEventListener('click', () => {
        setPopupOpen(false);
    });

    markAllReadBtn?.addEventListener('click', async (event) => {
        event.stopPropagation();

        try {
            await api.markAllAsRead();
            list.markAllAsRead();
            syncCacheFromList();
            setUnreadCount(0);
            await refreshUnreadCount();
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            window.alert(error.message || 'Unable to mark all notifications as read right now.');
        }
    });

    deleteAllBtn?.addEventListener('click', async (event) => {
        event.stopPropagation();
        openClearConfirmModal();
    });

    document.addEventListener('click', (event) => {
        if (
            !isAnyNotificationModalOpen() &&
            !notificationPopup.contains(event.target) &&
            !notificationBtn.contains(event.target)
        ) {
            setPopupOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && clearConfirmModal?.classList.contains('is-open')) {
            closeClearConfirmModal();
            return;
        }

        if (event.key === 'Escape' && notificationPopup.classList.contains('active') && !isAnyNotificationModalOpen()) {
            setPopupOpen(false);
        }
    });

    window.addEventListener('resize', () => {
        if (notificationPopup.classList.contains('active')) {
            positionNotificationPopup();
        }
    });

    hydrateNotificationsCache();
    startAutoRefresh();
    prefetchNotifications({ suppressErrors: true }).catch(() => {});
    bindClearConfirmModal();

    async function handleNotificationClick(notificationId) {
        const summary = list.getNotification(notificationId);

        if (!summary) {
            return;
        }

        const wasUnread = !summary.readAt;
        const optimisticReadAt = wasUnread ? new Date().toISOString() : summary.readAt;
        const immediateNotification = {
            ...summary,
            readAt: optimisticReadAt,
        };

        if (wasUnread) {
            list.markAsRead(notificationId, optimisticReadAt);
            syncCacheFromList();
            setUnreadCount(state.unreadCount - 1);
        }

        setPopupOpen(false);
        modal.showNotification(immediateNotification);

        api.fetchNotificationDetail(notificationId)
            .then((detailNotification) => {
                if (String(modal.getActiveNotificationId()) !== String(notificationId)) {
                    return;
                }

                modal.showNotification({
                    ...detailNotification,
                    readAt: detailNotification.readAt || optimisticReadAt,
                });
            })
            .catch((error) => {
                console.error('Error loading notification details:', error);
            });

        if (wasUnread) {
            api.markAsRead(notificationId)
                .then((response) => {
                    const syncedNotification = response?.data;

                    if (syncedNotification?.readAt) {
                        list.markAsRead(notificationId, syncedNotification.readAt);
                    }

                    refreshUnreadCount();
                })
                .catch((error) => {
                    console.error('Error syncing notification read state:', error);
                    refreshUnreadCount();
                });
        }
    }

    async function handleDeleteNotification(notificationId) {
        const notification = list.getNotification(notificationId);

        try {
            await api.deleteNotification(notificationId);
            list.remove(notificationId);
            syncCacheFromList();

            if (!notification?.readAt) {
                setUnreadCount(state.unreadCount - 1);
            }

            if (modal.isOpen() && String(modal.getActiveNotificationId()) === String(notificationId)) {
                modal.close();
            }

            await refreshUnreadCount();
        } catch (error) {
            console.error('Error deleting notification:', error);
            window.alert(error.message || 'Unable to delete this notification right now.');
        }
    }

    function setPopupOpen(isOpen) {
        notificationPopup.classList.toggle('active', Boolean(isOpen));

        if (isOpen) {
            positionNotificationPopup();
        }
    }

    function isAnyNotificationModalOpen() {
        return modal.isOpen() || clearConfirmModal?.classList.contains('is-open');
    }

    function bindClearConfirmModal() {
        if (!clearConfirmModal || !clearConfirmOkBtn) {
            return;
        }

        clearConfirmModal.querySelectorAll('[data-notification-clear-close]').forEach((element) => {
            element.addEventListener('click', closeClearConfirmModal);
        });

        clearConfirmModal.addEventListener('click', (event) => {
            if (event.target === clearConfirmModal) {
                closeClearConfirmModal();
            }
        });

        clearConfirmOkBtn.addEventListener('click', async () => {
            if (clearConfirmOkBtn.disabled) {
                return;
            }

            const defaultLabel = clearConfirmOkBtn.dataset.defaultLabel || clearConfirmOkBtn.textContent || 'Clear all';
            clearConfirmOkBtn.dataset.defaultLabel = defaultLabel;
            clearConfirmOkBtn.disabled = true;
            clearConfirmOkBtn.textContent = 'Clearing...';

            try {
                await api.deleteAllRead();
                closeClearConfirmModal();
                await loadNotifications({ silent: true, force: true });
                await refreshUnreadCount();

                if (modal.isOpen()) {
                    const activeNotificationId = modal.getActiveNotificationId();
                    if (!list.getNotification(activeNotificationId)) {
                        modal.close();
                    }
                }
            } catch (error) {
                console.error('Error clearing read notifications:', error);
                closeClearConfirmModal();
                window.alert(error.message || 'Unable to clear notifications right now.');
            } finally {
                clearConfirmOkBtn.disabled = false;
                clearConfirmOkBtn.textContent = defaultLabel;
            }
        });
    }

    function openClearConfirmModal() {
        if (!clearConfirmModal) {
            return;
        }

        clearConfirmModal.classList.add('is-open');
        clearConfirmModal.setAttribute('aria-hidden', 'false');
        window.setTimeout(() => clearConfirmOkBtn?.focus(), 20);
    }

    function closeClearConfirmModal() {
        if (!clearConfirmModal) {
            return;
        }

        clearConfirmModal.classList.remove('is-open');
        clearConfirmModal.setAttribute('aria-hidden', 'true');
    }

    function positionNotificationPopup() {
        const popupRect = notificationPopup.getBoundingClientRect();
        const buttonRect = notificationBtn.getBoundingClientRect();
        const popupWidth = Math.min(
            popupRect.width || notificationPopup.offsetWidth || 360,
            window.innerWidth - 24
        );
        const horizontalCenter = buttonRect.left + (buttonRect.width / 2) - (popupWidth / 2);
        const clampedLeft = Math.max(12, Math.min(horizontalCenter, window.innerWidth - popupWidth - 12));
        const top = buttonRect.bottom + 12;

        notificationPopup.style.left = `${Math.round(clampedLeft)}px`;
        notificationPopup.style.top = `${Math.round(top)}px`;
    }

    function openNotificationsPanel() {
        if (state.hasLoadedOnce) {
            list.render(state.notificationsCache);
            prefetchNotifications({ suppressErrors: true }).catch(() => {});
            return;
        }

        list.setLoading();
        prefetchNotifications().catch(() => {});
    }

    function setUnreadCount(count) {
        state.unreadCount = Math.max(0, Number(count) || 0);

        if (state.unreadCount > 0) {
            notificationBadge.textContent = state.unreadCount > 99 ? '99+' : String(state.unreadCount);
            notificationBadge.style.display = 'flex';
            persistNotificationsCache();
            return;
        }

        notificationBadge.textContent = '0';
        notificationBadge.style.display = 'none';
        persistNotificationsCache();
    }

    async function loadNotifications(options = {}) {
        const silent = Boolean(options.silent);
        const force = Boolean(options.force);

        if (!silent && !state.hasLoadedOnce) {
            list.setLoading();
        }

        try {
            await prefetchNotifications({
                force,
                suppressErrors: silent,
            });
        } catch (error) {
            console.error('Error loading notifications:', error);

            if (!silent && !state.hasLoadedOnce) {
                list.setError(error.message || 'Unable to load notifications right now.');
            }
        }
    }

    async function refreshUnreadCount() {
        try {
            const payload = await api.fetchUnreadCount();
            setUnreadCount(payload?.unread_count || 0);
        } catch (error) {
            console.error('Error refreshing unread count:', error);
        }
    }

    function startAutoRefresh() {
        if (state.refreshTimer) {
            window.clearInterval(state.refreshTimer);
        }

        state.refreshTimer = window.setInterval(() => {
            prefetchNotifications({
                force: true,
                suppressErrors: true,
            }).catch(() => {});
        }, 30000);
    }

    function hydrateNotificationsCache() {
        try {
            const cachedValue = window.sessionStorage.getItem(notificationCacheKey);

            if (!cachedValue) {
                return;
            }

            const payload = JSON.parse(cachedValue);

            if (!Array.isArray(payload?.data)) {
                return;
            }

            state.notificationsCache = payload.data;
            state.hasLoadedOnce = true;
            state.lastLoadedAt = Number(payload.timestamp) || 0;
            setUnreadCount(payload.unread_count ?? countUnreadNotifications(payload.data));
        } catch (error) {
            console.error('Error hydrating notification cache:', error);
        }
    }

    function persistNotificationsCache() {
        try {
            window.sessionStorage.setItem(notificationCacheKey, JSON.stringify({
                data: state.notificationsCache,
                unread_count: state.unreadCount,
                timestamp: state.lastLoadedAt,
            }));
        } catch (error) {
            console.error('Error persisting notification cache:', error);
        }
    }

    function countUnreadNotifications(notifications) {
        return Array.isArray(notifications)
            ? notifications.reduce((total, notification) => total + (notification?.readAt ? 0 : 1), 0)
            : 0;
    }

    function syncCacheFromList() {
        state.notificationsCache = Array.isArray(list.notifications) ? [...list.notifications] : [];
        state.hasLoadedOnce = true;
        persistNotificationsCache();
    }

    function applyNotificationPayload(payload) {
        state.notificationsCache = Array.isArray(payload?.data) ? payload.data : [];
        state.hasLoadedOnce = true;
        state.lastLoadedAt = Date.now();

        if (notificationPopup.classList.contains('active')) {
            list.render(state.notificationsCache);
        }

        setUnreadCount(payload?.unread_count ?? countUnreadNotifications(state.notificationsCache));
        persistNotificationsCache();
    }

    async function prefetchNotifications(options = {}) {
        const force = Boolean(options.force);
        const suppressErrors = Boolean(options.suppressErrors);

        if (state.fetchPromise && !force) {
            return state.fetchPromise;
        }

        const requestPromise = api.fetchNotifications()
            .then((payload) => {
                applyNotificationPayload(payload);
                return payload;
            })
            .catch((error) => {
                if (notificationPopup.classList.contains('active') && !state.hasLoadedOnce && !suppressErrors) {
                    list.setError(error.message || 'Unable to load notifications right now.');
                }

                throw error;
            })
            .finally(() => {
                if (state.fetchPromise === requestPromise) {
                    state.fetchPromise = null;
                }
            });

        state.fetchPromise = requestPromise;
        return requestPromise;
    }
}

function initializeSidebar() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !sidebarOverlay) {
        return;
    }

    mobileMenuBtn?.addEventListener('click', () => {
        sidebar.classList.add('active');
        sidebarOverlay.classList.add('active');
    });

    closeSidebarBtn?.addEventListener('click', closeSidebar);
    sidebarOverlay.addEventListener('click', closeSidebar);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('active')) {
            closeSidebar();
        }
    });

    document.querySelectorAll('.sidebar-item').forEach((item) => {
        item.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    function closeSidebar() {
        sidebar.classList.remove('active');
        sidebarOverlay.classList.remove('active');
    }
}
