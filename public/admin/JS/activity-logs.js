document.addEventListener('DOMContentLoaded', function() {
    const asyncRoot = document.getElementById('activityLogAsyncRoot');
    if (!asyncRoot) {
        return;
    }

    let searchTimeout;
    let activeRequestController = null;

    const createIcons = () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    };

    const getControls = () => ({
        searchInput: asyncRoot.querySelector('#search'),
        roleFilter: asyncRoot.querySelector('#role'),
        periodFilter: asyncRoot.querySelector('#period'),
        actionCategoryFilter: asyncRoot.querySelector('#action_category'),
        eventTypeFilter: asyncRoot.querySelector('#event_type'),
        perPageFilter: asyncRoot.querySelector('#per_page'),
    });

    const buildFilterUrl = () => {
        const {
            searchInput,
            roleFilter,
            periodFilter,
            actionCategoryFilter,
            eventTypeFilter,
            perPageFilter
        } = getControls();

        const params = new URLSearchParams();

        if (searchInput && searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        }

        if (roleFilter && roleFilter.value) {
            params.set('role', roleFilter.value);
        }

        if (periodFilter && periodFilter.value && periodFilter.value !== 'all') {
            params.set('period', periodFilter.value);
        }

        if (actionCategoryFilter && actionCategoryFilter.value) {
            params.set('action_category', actionCategoryFilter.value);
        }

        if (eventTypeFilter?.value) params.set('event_type', eventTypeFilter.value);

        if (perPageFilter && perPageFilter.value) {
            params.set('per_page', perPageFilter.value);
        }

        const query = params.toString();
        return `${window.location.pathname}${query ? `?${query}` : ''}`;
    };

    const setLoadingState = (loading) => {
        asyncRoot.classList.toggle('is-loading', loading);
        asyncRoot.setAttribute('aria-busy', loading ? 'true' : 'false');
        document.getElementById('auditLoadingStatus').textContent = loading ? 'Loading audit entries…' : '';
        const trail = asyncRoot.querySelector('[data-audit-results]');
        if (trail) trail.inert = loading;
    };

    const renderNextState = (html, url, historyMode, scrollY, focusConfig, shellScrollTop) => {
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const nextRoot = parsed.getElementById('activityLogAsyncRoot');

        if (!nextRoot) {
            throw new Error('Async activity log container was not found in the response.');
        }

        window.LMSAuditDetails.close();
        asyncRoot.innerHTML = nextRoot.innerHTML;

        if (historyMode === 'push') {
            window.history.pushState({
                url
            }, '', url);
        } else if (historyMode === 'replace') {
            window.history.replaceState({
                url
            }, '', url);
        }

        if (parsed.title) {
            document.title = parsed.title;
        }

        createIcons();
        const shell = asyncRoot.closest('.pwa-shell-content');
        if (shell) shell.scrollTo({ top: shellScrollTop, behavior: 'instant' });

        window.scrollTo({
            top: scrollY,
            behavior: 'auto'
        });

        if (focusConfig?.targetId) {
            const focusTarget = asyncRoot.querySelector(`#${focusConfig.targetId}`);
            if (focusTarget) {
                focusTarget.focus({ preventScroll: true });

                if (typeof focusConfig.selectionStart === 'number' &&
                    typeof focusConfig.selectionEnd === 'number' &&
                    typeof focusTarget.setSelectionRange === 'function') {
                    focusTarget.setSelectionRange(focusConfig.selectionStart, focusConfig.selectionEnd);
                } else if (typeof focusTarget.select === 'function') {
                    focusTarget.select();
                }
            }
        }
    };

    const fetchAndRender = async (url, options = {}) => {
        const {
            historyMode = 'push',
            focusTargetId = null,
        } = options;

        const activeElement = document.activeElement;
        const focusConfig = focusTargetId ? {
            targetId: focusTargetId,
            selectionStart: activeElement && activeElement.id === focusTargetId ? activeElement.selectionStart : null,
            selectionEnd: activeElement && activeElement.id === focusTargetId ? activeElement.selectionEnd : null,
        } : null;

        clearTimeout(searchTimeout);
        const scrollY = window.scrollY;
        const shellScrollTop = asyncRoot.closest('.pwa-shell-content')?.scrollTop || 0;

        if (activeRequestController) {
            activeRequestController.abort();
        }

        const requestController = new AbortController();
        activeRequestController = requestController;
        setLoadingState(true);

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                credentials: 'same-origin',
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            const html = await response.text();
            if (activeRequestController !== requestController) return;
            renderNextState(html, url, historyMode, scrollY, focusConfig, shellScrollTop);
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = url;
            }
        } finally {
            if (activeRequestController === requestController) {
                activeRequestController = null;
                setLoadingState(false);
            }
        }
    };

    asyncRoot.addEventListener('input', function(event) {
        if (event.target.id !== 'search') {
            return;
        }

        clearTimeout(searchTimeout);
        if (activeRequestController) {
            const previousRequest = activeRequestController;
            activeRequestController = null;
            previousRequest.abort();
        }
        searchTimeout = setTimeout(() => {
            fetchAndRender(buildFilterUrl(), {
                historyMode: 'replace',
                focusTargetId: 'search',
            });
        }, 350);
    });

    asyncRoot.addEventListener('change', function(event) {
        if (!['role', 'period', 'action_category', 'event_type', 'per_page'].includes(event.target.id)) {
            return;
        }

        fetchAndRender(buildFilterUrl(), {
            historyMode: 'push',
            focusTargetId: event.target.id,
        });
    });

    asyncRoot.addEventListener('click', function(event) {
        const detailsButton = event.target.closest('[data-audit-details]');
        if (detailsButton) {
            const payload = asyncRoot.querySelector('[data-audit-payload]');
            const logs = JSON.parse(payload.textContent);
            const log = logs[detailsButton.dataset.auditDetails];
            if (log) window.LMSAuditDetails.open(log, detailsButton);
            return;
        }
        const chip = event.target.closest('[data-clear-filter]');
        if (chip) {
            const control = asyncRoot.querySelector(`#${chip.dataset.clearFilter}`);
            if (control) control.value = control.id === 'period' ? 'all' : '';
            fetchAndRender(buildFilterUrl(), { focusTargetId: control?.id });
            return;
        }
        const resetButton = event.target.closest('[data-reset-filters]');
        if (resetButton) {
            event.preventDefault();
            clearTimeout(searchTimeout);
            const resetUrl = new URL(window.location.pathname, window.location.origin);
            const perPageValue = document.getElementById('per_page')?.value || '10';
            if (perPageValue !== '10') {
                resetUrl.searchParams.set('per_page', perPageValue);
            }
            fetchAndRender(resetUrl.toString(), {
                historyMode: 'push',
                focusTargetId: 'search',
            });
            return;
        }

        const paginationLink = event.target.closest('.admin-table-pagination a');
        if (paginationLink && asyncRoot.contains(paginationLink)) {
            event.preventDefault();
            fetchAndRender(paginationLink.href, {
                historyMode: 'push',
                focusTargetId: 'auditTrailTable',
            });
        }
    });

    document.addEventListener('keydown', function(event) {
        const {
            searchInput
        } = getControls();

        if (!searchInput) {
            return;
        }

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchInput.focus();
            searchInput.select();
        } else if (event.key === 'Escape' && document.activeElement === searchInput) {
            event.preventDefault();

            if (searchInput.value !== '') {
                searchInput.value = '';
                clearTimeout(searchTimeout);
                fetchAndRender(buildFilterUrl(), {
                    historyMode: 'replace',
                    focusTargetId: 'search',
                });
            } else {
                searchInput.blur();
            }
        }
    });

    window.addEventListener('popstate', function() {
        fetchAndRender(window.location.href, {
            historyMode: 'none',
        });
    });

    window.history.replaceState({
        url: window.location.href
    }, '', window.location.href);
    document.getElementById('closeActivityDetailsBtn').addEventListener('click', () => window.LMSAuditDetails.close());
    document.getElementById('activityDetailsOverlay').addEventListener('click', event => {
        if (event.target.id === 'activityDetailsOverlay') window.LMSAuditDetails.close();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') window.LMSAuditDetails.close();
    });
    createIcons();
});
function closeActivityDetails() { window.LMSAuditDetails.close(); }
