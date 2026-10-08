/* Presentation and accessibility only. This file never submits a form, fetches data,
   changes a field value, or replaces a module's action/confirmation handlers. */
(() => {
    'use strict';
    if (!document.body.classList.contains('admin-portal')) return;
    const studentPortal = document.body.classList.contains('student-portal');

    const buttonSelector = [
        'button', 'a.btn', 'a.book-copies-button', 'a.student-back-link',
        'a.back-link', 'a.report-export-btn', 'a.admin-table-pagination-link',
        'a.action-btn', 'a.admin-action-btn', 'a.activity-action-btn', 'a.btn-email',
        'a.view-btn', 'a.book-copies-back', 'a.request-btn', 'a.toolbar-btn',
    ].concat(studentPortal ? ['a.profile-btn'] : []).join(',');
    const paginationSelector = '.admin-table-pagination-link, .admin-pagination-btn, .pagination-btn, .table-pagination-btn, nav[aria-label*="Pagination"] a, nav[aria-label*="Pagination"] span[aria-current]';
    const badgeSelector = [
        '.badge', '.admin-status-badge', '.admin-role-badge', '.status-badge',
        '.condition-badge', '.table-badge', '.payment-badge', '.role-badge',
        '.request-status-badge', '.student-request-status-pill', '.student-status-pill',
        '.fine-status-badge', '.activity-status-badge', '.activity-role-badge',
        '.history-status-badge', '.history-action-badge', '.fine-history-role-badge',
        '.lock-status-badge', '.result-badge', '.book-detail-badge', '.section-status', '.row-indicator',
        '.activity-insight-badge', '.quick-action-badge', '.section-count', '.privilege-status-badge',
    ].concat(studentPortal ? ['.availability-badge', '.category-badge', '.condition-value', '.fine-reason', '.alert-badge', '.privilege-badge', '.chart-count', '.data-card-count', '.notification-type-chip', '.notification-detail-badge'] : []).join(',');
    const panelSelector = [
        '.modal', '.modal-dialog', '.modal-panel', '.modal-container', '.request-modal-panel',
        '.fine-modal', '.activity-modal', '.book-details-modal', '.role-modal',
        '.student-modal-dialog', '.student-confirm-modal', '.notification-detail-panel',
        '.action-feedback-confirm-card', '.transaction-confirm-card', '.role-confirm-card',
        '.account-deletion-modal__panel', '.report-export-panel', '.report-pdf-panel',
        '.logo-cropper-panel', '#addStudentModal > div',
    ].filter(selector => !studentPortal || selector !== '.modal')
        .concat(studentPortal ? ['.modal-content'] : []).join(',');
    const actionSelector = '.action-btn, .admin-action-btn, .book-copies-action, .request-action-btn, .student-fine-action-btn, .activity-action-btn, .action-btn-small';
    const primarySelector = '.btn-primary, .btn-success, .btn-info, .btn-accept, .modal-btn-approve, .save-changes-btn, .student-submit-btn, .generate-receipt-btn, .report-export-btn-primary, .report-export-btn-download, .report-pdf-btn-primary, .reports-error-retry, .export-btn, .btn-paid, .btn-activate, #modalConfirmBtn, #studentCreateSubmitBtn, #studentCreateBtn';
    const dangerSelector = '.btn-danger, .btn-confirm-danger, .btn-delete, .btn-deactivate, .account-deletion-zone__button, .delete-modal-primary, .remove-book';
    const unstyledSelector = '.sidebar-item, .tab-btn, .chart-view-btn, .request-selectbox-option, .physical-book-result, .palette-btn, .fine-period-label, .activity-toggle-btn, .privilege-details-toggle, .student-modal-backdrop, .accession-tabs button, .accession-button';
    const variants = ['primary', 'secondary', 'danger', 'neutral'];
    const busyStates = new WeakMap();
    let sequence = 0;
    let activeDialog = null;
    let previousFocus = null;
    let lastOutsideFocus = document.activeElement;

    function each(root, selector, callback) {
        if (root instanceof Element && root.matches(selector)) callback(root);
        root.querySelectorAll?.(selector).forEach(callback);
    }

    function visibleText(element) {
        const copy = element.cloneNode(true);
        copy.querySelectorAll('svg, .notification-badge, .admin-action-btn-tooltip, .action-tooltip, [aria-hidden="true"]').forEach(node => node.remove());
        return copy.textContent.replace(/\s+/g, ' ').trim();
    }

    function iconName(button) {
        const icon = button.querySelector('i, svg');
        const description = `${icon?.getAttribute('data-lucide') || ''} ${icon?.getAttribute('class') || ''} ${button.id}`;
        const names = [
            [/trash|delete/i, 'Delete'], [/eye|view/i, 'View details'],
            [/edit|pencil/i, 'Edit'], [/copy|copies|layers/i, 'Manage copies'],
            [/key|password/i, 'Reset password'], [/times|close|\bx\b/i, 'Close dialog'],
            [/bell/i, 'Notifications'], [/menu|bars/i, 'Open menu'],
            [/log-out|logout/i, 'Logout'], [/chevron.*left|arrow.*left/i, 'Previous page'],
            [/chevron.*right|arrow.*right/i, 'Next page'], [/check/i, 'Approve'],
            [/ban/i, 'Reject'], [/ellipsis|more/i, 'More details'],
            [/download/i, 'Download'], [/print/i, 'Print'],
        ];
        return names.find(([pattern]) => pattern.test(description))?.[1] || '';
    }

    function styleButton(button) {
        if (button.closest('.sidebar')) return;
        button.querySelectorAll('i, svg').forEach(icon => {
            if (icon.getAttribute('aria-hidden') !== 'true') icon.setAttribute('aria-hidden', 'true');
            if (icon.tagName.toLowerCase() === 'svg') icon.setAttribute('focusable', 'false');
        });
        if (button.matches(paginationSelector)) {
            button.classList.add('admin-ui-pagination');
            if (button.matches('.active, .is-active')) button.setAttribute('aria-current', 'page');
            else if (button.getAttribute('aria-current') === 'page') button.removeAttribute('aria-current');
            return;
        }
        if (button.matches(unstyledSelector) || (studentPortal && button.matches('.actions-grid-inner .action-btn'))) return;

        const text = visibleText(button);
        const iconOnly = !text || /^[×✕✖]$/.test(text);
        const action = button.matches(actionSelector) || (iconOnly && Boolean(button.closest('tbody')))
            || (studentPortal && button.matches('.cancel-btn, .dashboard-notification-delete-btn, .notification-delete-btn'));
        const close = button.matches('[data-modal-close], [data-account-deletion-close], [data-notification-detail-close], [data-notification-clear-close]')
            || /close|password-toggle|notification-btn|mobile-menu-btn|chart-action-btn|toast-close/.test(button.className)
            || /^studentCreateCloseBtn$/.test(button.id)
            || (studentPortal && button.matches('.pwd-toggle'));
        let variant = 'neutral';
        // Remove generated classes from classification so dynamic confirmations can change tone.
        const tokens = [...button.classList].filter(name => !name.startsWith('admin-ui-'));
        if (button.matches(primarySelector) || tokens.includes('primary') || tokens.includes('accept')
            || tokens.includes('paid') || button.classList.contains('bg-blue-600')
            || button.classList.contains('modal-btn-action') || button.classList.contains('fine-modal-btn-action')) variant = 'primary';
        if (button.matches('.transaction-confirm-btn, .action-feedback-confirm-btn, .role-confirm-btn')
            && tokens.some(name => ['success', 'warning'].includes(name))) variant = 'primary';
        if (button.matches('.btn-outline') && !close) variant = 'secondary';
        if (studentPortal && button.matches('.request-btn, .report-trigger-btn, .modal-btn-ok, .primary-button, .profile-btn, .notification-state-action')) variant = 'primary';
        if (studentPortal && button.matches('.show-more-btn, .dashboard-show-more, .secondary-button, .notification-header-link-primary')) variant = 'secondary';
        if (button.matches(dangerSelector) || tokens.some(name => ['danger', 'delete', 'reject'].includes(name))
            || /^(delete|remove|reject|permanently delete|clear all|deactivate)\b/i.test(text)) variant = 'danger';
        if (studentPortal && button.matches('.cancel-btn, .dashboard-notification-delete-btn, .notification-delete-btn, #removePhotoBtn')) variant = 'danger';
        if (close && variant !== 'danger') variant = 'neutral';
        if (/^(cancel|close)$/i.test(text) && !(studentPortal && button.matches('.cancel-btn'))) variant = 'neutral';
        if (action && variant !== 'danger' && !tokens.includes('accept') && !tokens.includes('btn-paid')) variant = 'neutral';

        button.classList.add('admin-ui-button');
        variants.forEach(name => button.classList.toggle(`admin-ui-${name}`, name === variant));
        button.classList.toggle('admin-ui-action', action || (close && iconOnly));
        button.classList.toggle('admin-ui-icon-button', iconOnly);
        if (iconOnly) {
            const nativeTitle = button.getAttribute('title');
            const name = button.getAttribute('aria-label') || nativeTitle || button.dataset.adminUiTooltip
                || (close ? 'Close dialog' : iconName(button));
            if (name && !button.hasAttribute('aria-label')) button.setAttribute('aria-label', name);
            // The portal provides one positioned tooltip below. Keeping a native title
            // as well makes browsers display a second tooltip after their hover delay.
            // Preserve its text for the shared tooltip, then suppress the native one.
            const tooltipText = nativeTitle || button.dataset.adminUiTooltip || name;
            if (tooltipText) button.dataset.adminUiTooltip = tooltipText;
            if (button.hasAttribute('title')) button.removeAttribute('title');
        }

        // Existing modules already disable controls and set busy text/spinners. Reflect that
        // state accessibly and retain the resting width; do not introduce submission handlers.
        const state = busyStates.get(button);
        const busy = Boolean((!state?.ownsAria && button.getAttribute('aria-busy') === 'true')
            || button.classList.contains('is-loading')
            || (button.disabled && (/\b(saving|adding|deleting|sending|processing|loading|updating|exporting|issuing|returning)\b/i.test(text)
                || (studentPortal && /\b(uploading|removing|requesting|cancelling)\b/i.test(text))
                || Boolean(button.querySelector('.fa-spinner, .fa-spin, .spinner')))));
        if (busy && !state?.busy) {
            const naturalWidth = button.getBoundingClientRect().width;
            const resting = state?.width || naturalWidth;
            busyStates.set(button, { busy: true, width: resting, inlineWidth: button.style.width, minWidth: button.style.minWidth, compact: naturalWidth > resting + 1, ownsAria: !button.hasAttribute('aria-busy') });
            button.style.minWidth = `${Math.ceil(resting)}px`;
            button.style.width = `${resting}px`;
        } else if (!busy && state?.busy) {
            button.style.minWidth = state.minWidth;
            button.style.width = state.inlineWidth;
            button.classList.remove('admin-ui-busy-compact');
            if (state.ownsAria) button.removeAttribute('aria-busy');
            busyStates.set(button, { busy: false, width: button.getBoundingClientRect().width });
        } else if (!busy) {
            busyStates.set(button, { busy: false, width: button.getBoundingClientRect().width });
        }
        button.classList.toggle('admin-ui-busy', busy);
        button.classList.toggle('admin-ui-busy-compact', busy && Boolean(busyStates.get(button)?.compact));
        if (busy && busyStates.get(button)?.ownsAria && button.getAttribute('aria-busy') !== 'true') button.setAttribute('aria-busy', 'true');
    }

    function styleBadge(badge) {
        const source = `${[...badge.classList].filter(name => !name.startsWith('admin-ui-')).join(' ')} ${badge.textContent}`.toLowerCase();
        let tone = 'neutral';
        if (/\b(success|active|available|paid|approved|allowed|eligible|good|returned|waived|ontime|is-live)\b/.test(source)) tone = 'success';
        if (/\b(info|new|processing|primary|admin)\b/.test(source)) tone = 'info';
        if (/\b(warning|pending|issued|borrowed|due soon|due today|fair|override)\b/.test(source)) tone = 'warning';
        if (/\b(danger|damaged|lost|overdue|rejected|inactive|restricted|unavailable)\b/.test(source)) tone = 'danger';
        if (studentPortal && /\b(alert|unpaid|outstanding)\b/.test(source)) tone = 'danger';
        if (studentPortal && /\b(today|tomorrow|soon|due-soon)\b/.test(source)) tone = 'warning';
        badge.classList.add('admin-ui-badge');
        ['success', 'warning', 'danger', 'info'].forEach(name => badge.classList.toggle(`admin-ui-badge-${name}`, tone === name));
    }

    function labelField(field) {
        if (field.type === 'hidden') return;
        if (!field.labels?.length && !field.hasAttribute('aria-label') && !field.hasAttribute('aria-labelledby')) {
            const group = field.closest('.form-group, .filter-field, .request-filter-field, .activity-filter-group, .request-selectbox');
            const label = group?.querySelector('label');
            if (label && !label.htmlFor) {
                field.id ||= `admin-ui-field-${++sequence}`;
                label.htmlFor = field.id;
            } else {
                const studentLabels = studentPortal ? {
                    statusFilter: 'Filter by status', fineStatusFilter: 'Filter by fine status',
                    categoryFilter: 'Filter by category', sortFilter: 'Sort books',
                    reasonFilter: 'Filter by fine reason', timeFilter: 'Filter by time period',
                    dateFilter: 'Filter by request date',
                } : {};
                const name = studentLabels[field.id] || field.getAttribute('placeholder') || field.getAttribute('name')?.replace(/[_\[\]]+/g, ' ');
                if (name) field.setAttribute('aria-label', name);
            }
        }
        // Connect existing inline error messages without changing validation or messages.
        const error = field.closest('.form-group')?.querySelector('.field-error, .request-field-error, .student-field-error, .physical-field-error, .form-error');
        if (error) {
            error.id ||= `admin-ui-error-${++sequence}`;
            const describedBy = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
            describedBy.add(error.id);
            const value = [...describedBy].join(' ');
            if (field.getAttribute('aria-describedby') !== value) field.setAttribute('aria-describedby', value);
        }
    }

    function annotateDialog(panel) {
        panel.setAttribute('role', 'dialog');
        panel.setAttribute('aria-modal', 'true');
        if (!panel.hasAttribute('aria-labelledby') && !panel.hasAttribute('aria-label')) {
            const heading = panel.querySelector('h2, h3, h4');
            if (heading) {
                heading.id ||= `admin-ui-dialog-title-${++sequence}`;
                panel.setAttribute('aria-labelledby', heading.id);
            }
        }
        if (!panel.hasAttribute('tabindex')) panel.tabIndex = -1;
    }

    function decorate(root) {
        each(root, buttonSelector, styleButton);
        each(root, paginationSelector, styleButton);
        each(root, badgeSelector, styleBadge);
        each(root, 'input, select, textarea', labelField);
        each(root, 'thead th', header => { if (!header.hasAttribute('scope')) header.scope = 'col'; });
        each(root, 'tbody', body => {
            body.dataset.adminUiRows = '';
            let index = 0;
            [...body.children].forEach(row => {
                if (row.matches('tr') && !row.matches('.book-copies-edit-row, .table-skeleton-row') && !row.querySelector('td[colspan]')) {
                    row.classList.toggle('admin-ui-row-even', ++index % 2 === 0);
                }
            });
        });
        each(root, panelSelector, annotateDialog);
    }

    function isVisible(element) {
        return element.isConnected && element.getClientRects().length > 0 && getComputedStyle(element).visibility !== 'hidden'
            && !element.closest('[hidden], [aria-hidden="true"]');
    }
    const focusSelector = 'a[href], button:not(:disabled), input:not(:disabled):not([type="hidden"]), select:not(:disabled), textarea:not(:disabled), [tabindex]:not([tabindex="-1"])';
    function syncDialogFocus() {
        const panels = [...document.querySelectorAll(panelSelector)].filter(isVisible);
        const next = panels.at(-1) || null;
        if (next === activeDialog) return;
        const old = activeDialog;
        activeDialog = next;
        if (next) {
            if (!old) previousFocus = lastOutsideFocus;
            if (!next.contains(document.activeElement)) {
                const focusable = [...next.querySelectorAll(focusSelector)].filter(isVisible);
                (focusable.find(node => node.matches('[autofocus], input, select, textarea')) || focusable[0] || next).focus({ preventScroll: true });
            }
        } else if (old && previousFocus?.isConnected && isVisible(previousFocus)) {
            const restore = studentPortal && previousFocus.matches(':disabled, [aria-disabled="true"]')
                ? document.querySelector('.pwa-shell-content')?.querySelector(focusSelector)
                : previousFocus;
            restore?.focus({ preventScroll: true });
            previousFocus = null;
        }
    }

    decorate(document);
    syncDialogFocus();
    // Update asynchronously inserted AJAX rows and existing class-based busy/modal states.
    const pending = new Set();
    let frame = null;
    const observer = new MutationObserver(records => {
        for (const record of records) {
            if (record.type === 'childList') {
                record.addedNodes.forEach(node => { if (node instanceof Element) pending.add(node); });
                const button = record.target.closest?.(buttonSelector);
                if (button) pending.add(button);
                const body = record.target.closest?.('tbody');
                if (body) pending.add(body);
            } else if (record.type === 'attributes') {
                if (record.attributeName === 'class') {
                    // Ignore our own generated classes to avoid observer feedback loops.
                    const clean = value => (value || '').split(/\s+/).filter(name => !name.startsWith('admin-ui-')).join(' ');
                    if (clean(record.oldValue) === clean(record.target.getAttribute('class'))) continue;
                }
                if (record.attributeName !== 'style' || record.target.matches(panelSelector) || /modal|overlay|backdrop/.test(record.target.className) || record.target.id === 'addStudentModal') pending.add(record.target);
            }
        }
        if (frame !== null || !pending.size) return;
        frame = requestAnimationFrame(() => {
            frame = null;
            const roots = [...pending];
            pending.clear();
            roots.filter(root => root.isConnected && !roots.some(other => other !== root && other.contains(root))).forEach(decorate);
            syncDialogFocus();
        });
    });
    observer.observe(document.body, { subtree: true, childList: true, attributes: true, attributeOldValue: true, attributeFilter: ['class', 'disabled', 'hidden', 'aria-hidden', 'aria-busy', 'title', 'style'] });

    document.addEventListener('focusin', event => {
        if (!activeDialog || !activeDialog.contains(event.target)) lastOutsideFocus = event.target;
    });
    document.addEventListener('keydown', event => {
        // Let existing Escape handlers run first. For legacy forms without one,
        // activate their existing close control, including its busy-state guards.
        if (event.key === 'Escape' && !event.defaultPrevented && activeDialog && isVisible(activeDialog)
            && !activeDialog.querySelector('[aria-busy="true"], .admin-ui-busy')) {
            const close = activeDialog.querySelector('.modal-close-btn, .modal-close, .request-modal-close, .fine-modal-close, .activity-modal-close, .book-details-close, .notification-detail-close-btn, .report-export-close-btn, .report-pdf-close, .account-deletion-modal__close, #studentCreateCloseBtn, [data-modal-close], [data-student-modal-close]'
                + (studentPortal ? ', .modal-btn-ok' : ''));
            if (close && !close.disabled && close.getAttribute('aria-disabled') !== 'true') close.click();
        }
        if (event.key !== 'Tab' || !activeDialog || !isVisible(activeDialog)) return;
        const controls = [...activeDialog.querySelectorAll(focusSelector)].filter(node => isVisible(node) && node.getAttribute('aria-disabled') !== 'true');
        const first = controls[0];
        const last = controls.at(-1);
        if (!first) { event.preventDefault(); activeDialog.focus(); }
        else if (event.shiftKey && (document.activeElement === first || !activeDialog.contains(document.activeElement))) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && (document.activeElement === last || !activeDialog.contains(document.activeElement))) { event.preventDefault(); first.focus(); }
    });

    // Reuse native click handlers for the existing div-based theme and clear controls.
    each(document, '#themeToggle, .clear-selection', element => {
        element.setAttribute('role', 'button');
        element.tabIndex = 0;
        element.setAttribute('aria-label', element.id === 'themeToggle' ? 'Toggle color theme' : 'Clear selection');
    });
    document.addEventListener('keydown', event => {
        if (event.target.matches('#themeToggle, .clear-selection') && ['Enter', ' '].includes(event.key)) {
            event.preventDefault();
            event.target.click();
        }
    });

    // One tooltip shared by all icon buttons, including buttons in AJAX rows.
    const tooltip = document.createElement('div');
    tooltip.className = 'admin-ui-tooltip';
    tooltip.id = 'adminUiTooltip';
    tooltip.setAttribute('role', 'tooltip');
    tooltip.hidden = true;
    document.body.appendChild(tooltip);
    let tooltipButton = null;
    function hideTooltip() {
        if (tooltipButton) {
            const ids = (tooltipButton.getAttribute('aria-describedby') || '').split(/\s+/).filter(id => id && id !== tooltip.id);
            if (ids.length) tooltipButton.setAttribute('aria-describedby', ids.join(' '));
            else tooltipButton.removeAttribute('aria-describedby');
        }
        tooltip.hidden = true;
        tooltipButton = null;
    }
    function showTooltip(button) {
        const text = button.dataset.adminUiTooltip || button.getAttribute('aria-label');
        if (!text || button.disabled) return;
        hideTooltip();
        tooltipButton = button;
        tooltip.textContent = text;
        tooltip.hidden = false;
        const rect = button.getBoundingClientRect();
        tooltip.style.left = `${Math.max(8, Math.min(innerWidth - tooltip.offsetWidth - 8, rect.left + (rect.width - tooltip.offsetWidth) / 2))}px`;
        tooltip.style.top = `${rect.top >= tooltip.offsetHeight + 12 ? rect.top - tooltip.offsetHeight - 6 : rect.bottom + 6}px`;
        const ids = new Set((button.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
        ids.add(tooltip.id);
        button.setAttribute('aria-describedby', [...ids].join(' '));
    }
    for (const type of ['pointerover', 'focusin']) document.addEventListener(type, event => {
        const button = event.target.closest('.admin-ui-icon-button');
        if (button && tooltipButton !== button) showTooltip(button);
    });
    for (const type of ['pointerout', 'focusout']) document.addEventListener(type, event => {
        if (tooltipButton && !tooltipButton.contains(event.relatedTarget)) hideTooltip();
    });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') hideTooltip(); });
    document.addEventListener('scroll', hideTooltip, true);
    window.addEventListener('resize', hideTooltip);
})();
