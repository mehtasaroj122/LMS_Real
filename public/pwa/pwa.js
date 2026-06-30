(() => {
    const SW_URL = '/sw.js';
    const INSTALL_DISMISSED_KEY = 'lms.pwa.install.dismissedAt';
    const LAST_ROUTE_KEY = 'lms.pwa.lastRoute';
    const INSTALL_DISMISS_DAYS = 7;

    const state = {
        deferredPrompt: null,
        waitingWorker: null,
        refreshing: false,
    };

    const isStandalone = () => (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true
    );

    const isSecurePwaContext = () => (
        window.isSecureContext ||
        ['localhost', '127.0.0.1', '::1'].includes(window.location.hostname)
    );

    const installBanner = document.getElementById('pwaInstallPrompt');
    const installButton = document.getElementById('pwaInstallButton');
    const installDismissButton = document.getElementById('pwaInstallDismissButton');
    const updateBanner = document.getElementById('pwaUpdatePrompt');
    const updateButton = document.getElementById('pwaUpdateButton');
    const updateDismissButton = document.getElementById('pwaUpdateDismissButton');
    const offlineIndicator = document.getElementById('pwaOfflineIndicator');

    function wasInstallRecentlyDismissed() {
        const dismissedAt = Number(window.localStorage.getItem(INSTALL_DISMISSED_KEY));
        if (!Number.isFinite(dismissedAt) || dismissedAt <= 0) {
            return false;
        }

        return Date.now() - dismissedAt < INSTALL_DISMISS_DAYS * 24 * 60 * 60 * 1000;
    }

    function showInstallBanner() {
        if (!installBanner || isStandalone() || wasInstallRecentlyDismissed()) {
            return;
        }

        installBanner.classList.add('is-visible');
        installBanner.removeAttribute('hidden');
    }

    function hideInstallBanner() {
        installBanner?.classList.remove('is-visible');
        window.setTimeout(() => installBanner?.setAttribute('hidden', 'hidden'), 260);
    }

    function showUpdateBanner(worker) {
        state.waitingWorker = worker;
        if (!updateBanner) {
            return;
        }

        updateBanner.classList.add('is-visible');
        updateBanner.removeAttribute('hidden');
    }

    function hideUpdateBanner() {
        updateBanner?.classList.remove('is-visible');
        window.setTimeout(() => updateBanner?.setAttribute('hidden', 'hidden'), 260);
    }

    function syncOfflineIndicator() {
        offlineIndicator?.classList.toggle('is-visible', !navigator.onLine);
        offlineIndicator?.toggleAttribute('hidden', navigator.onLine);
        document.documentElement.classList.toggle('is-offline', !navigator.onLine);
    }

    function rememberCurrentRoute() {
        if (!window.location.pathname.startsWith('/admin') &&
            !window.location.pathname.startsWith('/staff') &&
            !window.location.pathname.startsWith('/student')) {
            return;
        }

        try {
            window.localStorage.setItem(LAST_ROUTE_KEY, `${window.location.pathname}${window.location.search}`);
        } catch (error) {
            // Route persistence is a convenience only.
        }
    }

    function setupViewTransitions() {
        if (!document.startViewTransition) {
            return;
        }

        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link || event.defaultPrevented || event.button !== 0) {
                return;
            }

            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            if (link.target && link.target !== '_self') {
                return;
            }

            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin || url.pathname === window.location.pathname && url.search === window.location.search) {
                return;
            }

            if (link.hasAttribute('download') || link.dataset.noPwaTransition === 'true') {
                return;
            }

            event.preventDefault();
            document.startViewTransition(() => {
                window.location.assign(url.href);
            });
        });
    }

    async function registerServiceWorker() {
        if (!('serviceWorker' in navigator) || !isSecurePwaContext()) {
            return;
        }

        try {
            const registration = await navigator.serviceWorker.register(SW_URL, { scope: '/' });

            if (registration.waiting && navigator.serviceWorker.controller) {
                showUpdateBanner(registration.waiting);
            }

            registration.addEventListener('updatefound', () => {
                const worker = registration.installing;
                if (!worker) {
                    return;
                }

                worker.addEventListener('statechange', () => {
                    if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdateBanner(worker);
                    }
                });
            });

            window.setInterval(() => registration.update().catch(() => {}), 60 * 60 * 1000);
        } catch (error) {
            console.warn('[PWA] Service worker registration failed:', error);
        }
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        state.deferredPrompt = event;
        showInstallBanner();
    });

    window.addEventListener('appinstalled', () => {
        state.deferredPrompt = null;
        hideInstallBanner();
        try {
            window.localStorage.removeItem(INSTALL_DISMISSED_KEY);
        } catch (error) {}
    });

    installButton?.addEventListener('click', async () => {
        if (!state.deferredPrompt) {
            hideInstallBanner();
            return;
        }

        state.deferredPrompt.prompt();
        try {
            await state.deferredPrompt.userChoice;
        } finally {
            state.deferredPrompt = null;
            hideInstallBanner();
        }
    });

    installDismissButton?.addEventListener('click', () => {
        try {
            window.localStorage.setItem(INSTALL_DISMISSED_KEY, String(Date.now()));
        } catch (error) {}
        hideInstallBanner();
    });

    updateButton?.addEventListener('click', () => {
        const worker = state.waitingWorker;
        if (!worker) {
            hideUpdateBanner();
            return;
        }

        worker.postMessage({ type: 'SKIP_WAITING' });
    });

    updateDismissButton?.addEventListener('click', hideUpdateBanner);

    navigator.serviceWorker?.addEventListener('controllerchange', () => {
        if (state.refreshing) {
            return;
        }

        state.refreshing = true;
        window.location.reload();
    });

    window.addEventListener('online', syncOfflineIndicator);
    window.addEventListener('offline', syncOfflineIndicator);
    window.addEventListener('pagehide', rememberCurrentRoute);

    document.addEventListener('DOMContentLoaded', () => {
        syncOfflineIndicator();
        setupViewTransitions();
        rememberCurrentRoute();

        if (window.lucide?.createIcons) {
            window.lucide.createIcons();
        }
    });

    registerServiceWorker();
})();
