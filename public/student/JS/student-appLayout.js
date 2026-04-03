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
        pendingRemovals: new Map(),
        isClearingAll: false,
        requestSequence: 0,
        minResponseSequence: 0,
    };

    const showPortalToast = (type, title, message, timeout = 4200, detail = '') => {
        if (typeof window.showStudentPortalToast === 'function') {
            window.showStudentPortalToast(type, title, message, timeout, detail);
            return;
        }

        const fallbackMessage = message || title || 'Something went wrong.';
        const logger = type === 'error' ? console.error : console.log;
        logger(fallbackMessage);
    };

    const openPortalConfirm = (options = {}) => {
        if (typeof window.confirmStudentPortalAction === 'function') {
            return window.confirmStudentPortalAction(options);
        }

        console.warn('Student portal action feedback confirmation is unavailable.', options);
        return Promise.resolve(false);
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

        if (markAllReadBtn.disabled) {
            return;
        }

        try {
            await api.markAllAsRead();
            list.markAllAsRead();
            syncCacheFromList();
            setUnreadCount(0);
            await refreshUnreadCount();
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            showPortalToast('error', 'Unable to mark all as read', error.message || 'Unable to mark all notifications as read right now.');
        }
    });

    deleteAllBtn?.addEventListener('click', async (event) => {
        event.stopPropagation();

        if (deleteAllBtn.disabled) {
            return;
        }

        await confirmClearReadNotifications();
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
    syncActionAvailability();

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
                        syncCacheFromList();
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
        const resolvedId = String(notificationId);
        const notification = list.getNotification(resolvedId);

        if (
            !notification ||
            state.isClearingAll ||
            state.pendingRemovals.has(resolvedId) ||
            list.isAnimating(resolvedId)
        ) {
            return;
        }

        const previousNotifications = [...list.notifications];
        const previousUnreadCount = state.unreadCount;

        advanceMutationBarrier();
        rememberPendingRemoval(notification);
        syncActionAvailability();

        await list.animateRemove(resolvedId);
        list.remove(resolvedId);
        syncCacheFromList();

        if (!notification.readAt) {
            setUnreadCount(previousUnreadCount - 1);
        }

        if (modal.isOpen() && String(modal.getActiveNotificationId()) === resolvedId) {
            modal.close();
        }

        try {
            await api.deleteNotification(resolvedId);
        } catch (error) {
            forgetPendingRemoval(resolvedId);
            restoreNotificationSnapshot(previousNotifications, previousUnreadCount);
            console.error('Error deleting notification:', error);
            showPortalToast('error', 'Unable to delete notification', error.message || 'Unable to delete this notification right now.');
            return;
        }

        advanceMutationBarrier();
        forgetPendingRemoval(resolvedId);
        syncActionAvailability();
        await loadNotifications({ silent: true, force: true });
        await refreshUnreadCount();
    }

    function setPopupOpen(isOpen) {
        notificationPopup.classList.toggle('active', Boolean(isOpen));

        if (isOpen) {
            positionNotificationPopup();
        }

        syncActionAvailability();
    }

    function isAnyNotificationModalOpen() {
        const portalConfirmOpen = Boolean(
            window.getStudentPortalFeedback?.()?.elements?.confirmModal?.classList.contains('is-open')
        );

        return modal.isOpen() || portalConfirmOpen;
    }

    async function confirmClearReadNotifications() {
        const readNotifications = list.notifications.filter((notification) => notification?.readAt);

        if (readNotifications.length === 0) {
            syncActionAvailability();
            return;
        }

        const confirmed = await openPortalConfirm({
            variant: 'danger',
            buttonVariant: 'danger',
            title: 'Clear read notifications?',
            message: 'Remove every read notification from your list? Unread notifications will stay in place.',
            detail: `${readNotifications.length} read notification${readNotifications.length === 1 ? '' : 's'} will be removed.`,
            confirmText: 'Clear all',
            cancelText: 'Cancel',
        });

        if (!confirmed) {
            syncActionAvailability();
            return;
        }

        const previousNotifications = [...list.notifications];
        const previousUnreadCount = state.unreadCount;
        const removedIds = readNotifications.map((notification) => String(notification.id));
        const activeNotificationId = modal.getActiveNotificationId();
        const closesActiveModal = modal.isOpen() && removedIds.includes(String(activeNotificationId));

        advanceMutationBarrier();
        state.isClearingAll = true;
        readNotifications.forEach((notification) => rememberPendingRemoval(notification));
        syncActionAvailability();

        await list.animateRemoveMany(removedIds, { staggerStep: 44 });
        list.removeMany(removedIds);
        syncCacheFromList();

        if (closesActiveModal) {
            modal.close();
        }

        try {
            await api.deleteAllRead();
        } catch (error) {
            state.isClearingAll = false;
            removedIds.forEach((notificationId) => forgetPendingRemoval(notificationId));
            restoreNotificationSnapshot(previousNotifications, previousUnreadCount);
            console.error('Error clearing read notifications:', error);
            showPortalToast('error', 'Unable to clear notifications', error.message || 'Unable to clear notifications right now.');
            return;
        }

        advanceMutationBarrier();
        state.isClearingAll = false;
        removedIds.forEach((notificationId) => forgetPendingRemoval(notificationId));
        syncActionAvailability();
        await loadNotifications({ silent: true, force: true });
        await refreshUnreadCount();
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
            syncActionAvailability();
            prefetchNotifications({ suppressErrors: true }).catch(() => {});
            return;
        }

        list.setLoading();
        syncActionAvailability();
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
            syncActionAvailability();
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
                syncActionAvailability();
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

    function countPendingUnreadRemovals() {
        let total = 0;
        state.pendingRemovals.forEach((notification) => {
            if (!notification?.readAt) {
                total += 1;
            }
        });

        return total;
    }

    function filterPendingRemovals(notifications) {
        if (state.pendingRemovals.size === 0) {
            return Array.isArray(notifications) ? notifications : [];
        }

        return (Array.isArray(notifications) ? notifications : []).filter((notification) => (
            !state.pendingRemovals.has(String(notification?.id))
        ));
    }

    function rememberPendingRemoval(notification) {
        if (!notification?.id) {
            return;
        }

        state.pendingRemovals.set(String(notification.id), notification);
    }

    function forgetPendingRemoval(notificationId) {
        state.pendingRemovals.delete(String(notificationId));
    }

    function advanceMutationBarrier() {
        state.minResponseSequence = state.requestSequence + 1;
    }

    function restoreNotificationSnapshot(notifications, unreadCount) {
        list.render(notifications);
        syncCacheFromList();
        setUnreadCount(unreadCount);
        syncActionAvailability();
    }

    function syncCacheFromList() {
        state.notificationsCache = Array.isArray(list.notifications) ? [...list.notifications] : [];
        state.hasLoadedOnce = true;
        persistNotificationsCache();
        syncActionAvailability();
    }

    function syncActionAvailability() {
        const sourceNotifications = notificationPopup.classList.contains('active')
            ? list.notifications
            : state.notificationsCache;
        const notifications = Array.isArray(sourceNotifications) ? sourceNotifications : [];
        const isBusy = state.isClearingAll || state.pendingRemovals.size > 0 || list.hasActiveAnimations();
        const hasUnreadNotifications = notifications.some((notification) => !notification?.readAt);
        const hasReadNotifications = notifications.some((notification) => notification?.readAt);

        if (markAllReadBtn) {
            markAllReadBtn.disabled = isBusy || !hasUnreadNotifications;
        }

        if (deleteAllBtn) {
            deleteAllBtn.disabled = isBusy || !hasReadNotifications;
        }
    }

    function applyNotificationPayload(payload) {
        const filteredNotifications = filterPendingRemovals(payload?.data);
        const unreadCountFromServer = Number(payload?.unread_count);

        state.notificationsCache = filteredNotifications;
        state.hasLoadedOnce = true;
        state.lastLoadedAt = Date.now();

        if (notificationPopup.classList.contains('active') && !list.hasActiveAnimations()) {
            list.render(state.notificationsCache);
        }

        setUnreadCount(
            Number.isFinite(unreadCountFromServer)
                ? Math.max(0, unreadCountFromServer - countPendingUnreadRemovals())
                : countUnreadNotifications(state.notificationsCache)
        );
        persistNotificationsCache();
        syncActionAvailability();
    }

    async function prefetchNotifications(options = {}) {
        const force = Boolean(options.force);
        const suppressErrors = Boolean(options.suppressErrors);

        if (state.fetchPromise && !force) {
            return state.fetchPromise;
        }

        const requestSequence = ++state.requestSequence;
        const requestPromise = api.fetchNotifications()
            .then((payload) => {
                if (requestSequence >= state.minResponseSequence) {
                    applyNotificationPayload(payload);
                }

                return payload;
            })
            .catch((error) => {
                if (notificationPopup.classList.contains('active') && !state.hasLoadedOnce && !suppressErrors) {
                    list.setError(error.message || 'Unable to load notifications right now.');
                    syncActionAvailability();
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
