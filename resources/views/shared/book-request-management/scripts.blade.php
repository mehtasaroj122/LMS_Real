<script>
    (() => {
        const bookRequestManagementConfig = @json($bookRequestManagementConfig);

        const svgIcons = {
            pending: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>',
            approved: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"></path></svg>',
            rejected: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 6-12 12"></path><path d="m6 6 12 12"></path></svg>',
            search: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>',
            prev: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"></path></svg>',
            next: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"></path></svg>',
            spinner: '<span class="loading-spinner" aria-hidden="true"></span>',
            close: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 6-12 12"></path><path d="m6 6 12 12"></path></svg>',
        };

        class SearchableSelect {
            constructor(root, onChange) {
                this.root = root;
                this.onChange = onChange;
                this.input = root.querySelector('.searchable-select-input');
                this.hiddenInput = root.querySelector('input[type="hidden"]');
                this.dropdown = root.querySelector('.searchable-select-dropdown');
                this.options = Array.from(root.querySelectorAll('.searchable-select-option'));
                this.emptyState = null;
                this.activeOption = null;
                this.selectedOption = null;
                this.bindEvents();
            }

            bindEvents() {
                this.input?.addEventListener('focus', () => this.showDropdown());
                this.input?.addEventListener('input', (event) => {
                    const currentValue = String(event.target.value || '');
                    const selectedLabel = String(this.selectedOption?.dataset.text || '');

                    if (this.selectedOption && currentValue !== selectedLabel) {
                        this.selectedOption.classList.remove('is-selected');
                        this.selectedOption = null;

                        if (this.hiddenInput) {
                            this.hiddenInput.value = '';
                        }

                        this.onChange?.(this);
                        this.hiddenInput?.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    this.filterOptions(currentValue);
                });
                this.input?.addEventListener('keydown', (event) => this.handleKeydown(event));

                this.dropdown?.addEventListener('click', (event) => {
                    const option = event.target.closest('.searchable-select-option');
                    if (option) {
                        this.selectOption(option);
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!this.root.contains(event.target)) {
                        this.hideDropdown();
                    }
                });
            }

            handleKeydown(event) {
                const visibleOptions = this.getVisibleOptions();

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.moveActiveOption(1, visibleOptions);
                }

                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.moveActiveOption(-1, visibleOptions);
                }

                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.activeOption) {
                        this.selectOption(this.activeOption);
                    }
                }

                if (event.key === 'Escape') {
                    this.hideDropdown();
                }
            }

            filterOptions(term) {
                const query = String(term || '').trim().toLowerCase();
                let visibleCount = 0;

                this.options.forEach((option) => {
                    const optionSearchText = String(option.dataset.search || option.dataset.text || option.textContent || '').toLowerCase();
                    const normalizedSearchText = optionSearchText.replace(/[\s\-()]+/g, '');
                    const normalizedQuery = query.replace(/[\s\-()]+/g, '');
                    const isVisible = query === ''
                        || optionSearchText.includes(query)
                        || (normalizedQuery !== '' && normalizedSearchText.includes(normalizedQuery));
                    option.hidden = !isVisible;
                    option.classList.remove('is-active');
                    if (isVisible) {
                        visibleCount += 1;
                    }
                });

                this.activeOption = null;
                this.renderEmptyState(visibleCount === 0);
                this.showDropdown();
            }

            renderEmptyState(show) {
                if (!this.dropdown) return;

                if (!this.emptyState) {
                    this.emptyState = document.createElement('div');
                    this.emptyState.className = 'request-selectbox-empty';
                    this.emptyState.textContent = 'No results found';
                    this.dropdown.appendChild(this.emptyState);
                }

                this.emptyState.hidden = !show;
            }

            getVisibleOptions() {
                return this.options.filter((option) => !option.hidden);
            }

            moveActiveOption(direction, options) {
                if (options.length === 0) {
                    return;
                }

                const currentIndex = this.activeOption ? options.indexOf(this.activeOption) : -1;
                let nextIndex = currentIndex + direction;

                if (nextIndex < 0) nextIndex = options.length - 1;
                if (nextIndex >= options.length) nextIndex = 0;

                this.setActiveOption(options[nextIndex]);
            }

            setActiveOption(option) {
                this.options.forEach((item) => item.classList.remove('is-active'));
                this.activeOption = option;

                if (option) {
                    option.classList.add('is-active');
                    option.scrollIntoView({ block: 'nearest' });
                }
            }

            selectOption(option) {
                this.options.forEach((item) => item.classList.remove('is-selected'));
                option.classList.add('is-selected');
                this.selectedOption = option;
                this.hiddenInput.value = option.dataset.value || '';
                this.input.value = option.dataset.text || option.textContent.trim();
                this.hideDropdown();
                this.setInvalid(false);
                this.onChange?.(this);
                this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
            }

            showDropdown() {
                this.dropdown?.classList.add('is-open');
                this.input?.setAttribute('aria-expanded', 'true');
            }

            hideDropdown() {
                this.dropdown?.classList.remove('is-open');
                this.input?.setAttribute('aria-expanded', 'false');
                this.options.forEach((option) => option.classList.remove('is-active'));
                this.activeOption = null;
            }

            clear() {
                if (this.hiddenInput) this.hiddenInput.value = '';
                if (this.input) this.input.value = '';
                this.options.forEach((option) => option.classList.remove('is-selected', 'is-active'));
                this.options.forEach((option) => {
                    option.hidden = false;
                });
                this.selectedOption = null;
                this.activeOption = null;
                this.renderEmptyState(false);
                this.setInvalid(false);
                this.onChange?.(this);
            }

            setInvalid(isInvalid) {
                this.input?.classList.toggle('is-invalid', isInvalid);
                this.input?.setAttribute('aria-invalid', isInvalid ? 'true' : 'false');
            }

            getValue() {
                return this.hiddenInput?.value || '';
            }

            getText() {
                return this.input?.value || '';
            }
        }

        class BookRequestManager {
            constructor(config) {
                const searchParams = new URLSearchParams(window.location.search);
                this.config = config;
                this.elements = {};
                this.studentSelect = null;
                this.bookSelect = null;
                this.state = {
                    currentPage: Math.max(1, Number(searchParams.get('page')) || 1),
                    perPage: this.normalizePerPage(searchParams.get('per_page')),
                    search: String(searchParams.get('search') || '').trim(),
                    status: String(searchParams.get('status') || 'all').toLowerCase(),
                    sort: String(searchParams.get('sort') || 'date-desc').toLowerCase(),
                    requests: [],
                    selectedRequestIds: new Set(),
                    stats: {},
                    pagination: {},
                    fetchController: null,
                    searchTimer: null,
                    currentModalId: null,
                    lastFocusedElement: null,
                    pendingAction: null,
                    createInFlight: false,
                    actionInFlight: false,
                    lastUpdatedAt: null,
                };
                this.feedbackUI = null;
            }

            init() {
                this.cacheElements();
                if (!this.elements.root) {
                    return;
                }

                this.initializeFeedbackUI();
                this.initSelects();
                this.setupEventListeners();
                this.loadRequests();
            }

            cacheElements() {
                this.elements.root = document.getElementById('bookRequestManagementRoot');
                this.elements.searchInput = document.getElementById('requestSearchInput');
                this.elements.statusFilter = document.getElementById('requestStatusFilter');
                this.elements.sortFilter = document.getElementById('requestSortFilter');
                this.elements.entriesSelect = document.getElementById('requestEntriesSelect');
                this.elements.resetButton = document.getElementById('requestResetFiltersBtn');
                this.elements.createButton = document.getElementById('createRequestBtn');
                this.elements.bulkActionBar = document.getElementById('requestBulkActionBar');
                this.elements.bulkSummary = document.getElementById('requestBulkSummary');
                this.elements.bulkApproveButton = document.getElementById('bulkApproveRequestsBtn');
                this.elements.bulkRejectButton = document.getElementById('bulkRejectRequestsBtn');
                this.elements.clearSelectionButton = document.getElementById('clearSelectedRequestsBtn');
                this.elements.selectAllCheckbox = document.getElementById('requestSelectAll');
                this.elements.tableWrapper = document.getElementById('requestsTableWrapper');
                this.elements.tbody = document.getElementById('requestsTableBody');
                this.elements.emptyState = document.getElementById('requestsEmptyState');
                this.elements.emptyMessage = document.getElementById('requestsEmptyMessage');
                this.elements.filterSummary = document.getElementById('requestFilterSummary');
                this.elements.lastUpdated = document.getElementById('requestLastUpdated');
                this.elements.paginationContainer = document.getElementById('requestPaginationContainer');
                this.elements.paginationButtons = document.getElementById('requestPaginationButtons');
                this.elements.startCount = document.getElementById('requestStartCount');
                this.elements.endCount = document.getElementById('requestEndCount');
                this.elements.totalCount = document.getElementById('requestTotalCount');
                this.elements.pageInfo = document.getElementById('requestPageInfo');
                this.elements.statCards = Array.from(document.querySelectorAll('[data-stat-card]'));
                this.elements.createModal = document.getElementById('createRequestModal');
                this.elements.studentFieldError = document.getElementById('studentFieldError');
                this.elements.bookFieldError = document.getElementById('bookFieldError');
                this.elements.selectionSummary = document.getElementById('requestSelectionSummary');
                this.elements.selectedStudentSummary = document.getElementById('selectedStudentSummary');
                this.elements.selectedBookSummary = document.getElementById('selectedBookSummary');
                this.elements.selectedStudentText = document.getElementById('selectedStudentText');
                this.elements.selectedBookText = document.getElementById('selectedBookText');
                this.elements.createSubmitButton = document.getElementById('confirmCreateRequestBtn');
                this.elements.actionModal = document.getElementById('requestActionModal');
                this.elements.actionIcon = document.getElementById('requestActionIcon');
                this.elements.actionTitle = document.getElementById('requestActionTitle');
                this.elements.actionMessage = document.getElementById('requestActionMessage');
                this.elements.actionDetail = document.getElementById('requestActionDetail');
                this.elements.actionSubmitButton = document.getElementById('confirmRequestActionBtn');
                this.elements.toastContainer = document.getElementById('requestToastContainer');
                this.elements.liveRegion = document.getElementById('requestLiveRegion');
            }

            initializeFeedbackUI() {
                if (typeof window.ActionFeedbackUI !== 'function') {
                    return;
                }

                this.feedbackUI = new window.ActionFeedbackUI({
                    confirm: {
                        modalId: 'requestActionModal',
                        iconId: 'requestActionIcon',
                        titleId: 'requestActionTitle',
                        messageId: 'requestActionMessage',
                        detailId: 'requestActionDetail',
                        submitButtonId: 'confirmRequestActionBtn',
                        confirmLabel: 'Confirm',
                    },
                    toast: {
                        containerId: 'requestToastContainer',
                        liveRegionId: 'requestLiveRegion',
                    },
                    openModal: (modalId, focusTarget) => this.openModal(modalId, focusTarget),
                    closeModal: (modalId) => this.closeModal(modalId),
                    setButtonBusy: (button, isBusy, label) => this.setButtonBusy(button, isBusy, label),
                });
            }

            initSelects() {
                const studentRoot = this.elements.createModal?.querySelector('[data-select="student"]');
                const bookRoot = this.elements.createModal?.querySelector('[data-select="book"]');

                if (studentRoot) {
                    this.studentSelect = new SearchableSelect(studentRoot, () => this.updateSelectionSummary());
                }

                if (bookRoot) {
                    this.bookSelect = new SearchableSelect(bookRoot, () => this.updateSelectionSummary());
                }
            }

            setupEventListeners() {
                this.syncControlsFromState();

                this.elements.searchInput?.addEventListener('input', (event) => {
                    this.state.search = event.target.value.trim();
                    this.state.currentPage = 1;
                    this.scheduleLoad();
                });

                this.elements.statusFilter?.addEventListener('change', (event) => {
                    this.state.status = event.target.value;
                    this.state.currentPage = 1;
                    this.loadRequests();
                });

                this.elements.sortFilter?.addEventListener('change', (event) => {
                    this.state.sort = event.target.value;
                    this.state.currentPage = 1;
                    this.loadRequests();
                });

                this.elements.entriesSelect?.addEventListener('change', (event) => {
                    this.state.perPage = this.normalizePerPage(event.target.value);
                    this.state.currentPage = 1;
                    this.loadRequests();
                });

                this.elements.resetButton?.addEventListener('click', () => this.resetFilters());
                this.elements.createButton?.addEventListener('click', () => this.openCreateModal());
                this.elements.bulkApproveButton?.addEventListener('click', () => this.openBulkActionModal('approved'));
                this.elements.bulkRejectButton?.addEventListener('click', () => this.openBulkActionModal('rejected'));
                this.elements.clearSelectionButton?.addEventListener('click', () => this.clearSelection());
                this.elements.selectAllCheckbox?.addEventListener('change', (event) => {
                    this.toggleSelectAll(Boolean(event.target.checked));
                });
                this.elements.createSubmitButton?.addEventListener('click', () => this.submitCreateRequest());
                this.elements.actionSubmitButton?.addEventListener('click', () => this.executeRequestAction());

                this.elements.root?.addEventListener('click', (event) => {
                    const closeButton = event.target.closest('[data-modal-close]');
                    if (closeButton) {
                        this.closeModal(closeButton.getAttribute('data-modal-close'));
                    }

                    const actionButton = event.target.closest('[data-request-action]');
                    if (actionButton) {
                        this.openRequestActionModal(actionButton.dataset.requestId, actionButton.dataset.requestAction);
                    }

                    const resetAction = event.target.closest('[data-action="reset-request-filters"]');
                    if (resetAction) {
                        this.resetFilters();
                    }

                    const paginationButton = event.target.closest('[data-page]');
                    if (paginationButton) {
                        this.goToPage(Number(paginationButton.dataset.page));
                    }
                });

                this.elements.root?.addEventListener('change', (event) => {
                    const requestCheckbox = event.target.closest('[data-request-checkbox]');
                    if (requestCheckbox) {
                        this.setSelection(requestCheckbox.dataset.requestId, Boolean(requestCheckbox.checked));
                    }
                });

                document.querySelectorAll('.request-modal-backdrop, .action-feedback-confirm-overlay').forEach((modal) => {
                    modal.addEventListener('click', (event) => {
                        if (event.target === modal) {
                            this.closeModal(modal.id);
                        }
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                        event.preventDefault();
                        this.elements.searchInput?.focus();
                    }

                    if (event.key === 'Escape') {
                        if (this.state.currentModalId) {
                            this.closeModal(this.state.currentModalId);
                            return;
                        }

                        if (document.activeElement === this.elements.searchInput && this.elements.searchInput.value !== '') {
                            this.elements.searchInput.value = '';
                            this.state.search = '';
                            this.state.currentPage = 1;
                            this.loadRequests();
                        }
                    }
                });
            }

            scheduleLoad() {
                window.clearTimeout(this.state.searchTimer);
                this.state.searchTimer = window.setTimeout(() => this.loadRequests(), 320);
            }

            async loadRequests({ silent = false } = {}) {
                if (this.state.fetchController) {
                    this.state.fetchController.abort();
                }

                const controller = new AbortController();
                this.state.fetchController = controller;
                this.setStatsLoading(true);
                this.renderTableLoading();
                this.elements.tableWrapper?.setAttribute('aria-busy', 'true');

                const params = new URLSearchParams({
                    search: this.state.search,
                    status: this.state.status,
                    sort: this.state.sort,
                    page: String(this.state.currentPage),
                    per_page: String(this.state.perPage),
                });

                try {
                    const data = await this.requestJson(`${this.config.routes.data}?${params.toString()}`, {
                        method: 'GET',
                        signal: controller.signal,
                    });

                    this.state.requests = Array.isArray(data.requests) ? data.requests : [];
                    this.state.stats = data.stats || {};
                    this.state.pagination = data.pagination || {};
                    this.state.lastUpdatedAt = new Date();
                    this.reconcileSelection();

                    const lastPage = Math.max(1, Number(this.state.pagination.last_page || 1));
                    if (this.state.currentPage > lastPage) {
                        this.state.currentPage = lastPage;
                        await this.loadRequests({ silent: true });
                        return;
                    }

                    this.state.currentPage = Math.max(1, Number(this.state.pagination.current_page || this.state.currentPage || 1));
                    this.state.perPage = this.normalizePerPage(this.state.pagination.per_page || this.state.perPage);
                    this.renderAll();
                    this.syncControlsFromState();
                    this.updateBrowserUrl();

                    if (!silent) {
                        this.announce('Book requests updated.');
                    }
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error('[Book Requests] Failed to load requests:', error);
                    this.renderTableError();
                    this.setStatsLoading(false);
                    this.showToast('error', 'Could not load requests', this.resolveErrorMessage(error, 'Something went wrong while loading book requests.'));
                } finally {
                    if (this.state.fetchController === controller) {
                        this.state.fetchController = null;
                    }

                    this.elements.tableWrapper?.setAttribute('aria-busy', 'false');
                }
            }

            renderAll() {
                this.renderStats();
                this.renderTable();
                this.updateBulkActionState();
                this.renderToolbarMeta();
                this.renderPagination();
            }

            renderStats() {
                this.setStatsLoading(false);
                const stats = this.state.stats || {};
                const total = Number(stats.totalCount || 0);
                const pending = Number(stats.pendingCount || 0);
                const approved = Number(stats.approvedCount || 0);
                const rejected = Number(stats.rejectedCount || 0);
                const pendingMeta = String(stats.pendingMeta || (pending > 0 ? 'Queue details available' : 'No requests waiting in this view'));
                const approvedMeta = String(stats.approvedMeta || (approved > 0 ? 'Approval details available' : 'No approved requests in this view'));
                const rejectedMeta = String(stats.rejectedMeta || (rejected > 0 ? 'Rejection details available' : 'No rejected requests in this view'));

                this.setText('pendingRequestCount', pending);
                this.setText('approvedRequestCount', approved);
                this.setText('rejectedRequestCount', rejected);
                this.setText('pendingRequestMeta', pendingMeta);
                this.setText('approvedRequestMeta', approvedMeta);
                this.setText('rejectedRequestMeta', rejectedMeta);
                this.setText('requestTotalCount', total);
            }

            renderTableLoading() {
                if (!this.elements.tbody) return;
                this.toggleEmptyState(false);

                const row = `
                    <tr>
                        <td><span class="request-skeleton-line short"></span></td>
                        <td><span class="request-skeleton-line"></span></td>
                        <td><span class="request-skeleton-line"></span></td>
                        <td><span class="request-skeleton-line short"></span></td>
                        <td><span class="request-skeleton-line short"></span></td>
                        <td><span class="request-skeleton-line short"></span></td>
                        <td><span class="request-skeleton-line"></span></td>
                    </tr>
                `;

                this.elements.tbody.innerHTML = Array.from({ length: 5 }).map(() => row).join('');
            }

            renderTableError() {
                if (!this.elements.tbody) return;
                this.toggleEmptyState(false);
                this.elements.tbody.innerHTML = '<tr><td colspan="7" style="padding:1.2rem;text-align:center;color:var(--request-text-secondary);">Book requests could not be loaded.</td></tr>';
            }

            renderTable() {
                if (!this.elements.tbody) return;

                if (this.state.requests.length === 0) {
                    this.elements.tbody.innerHTML = '';
                    this.renderEmptyStateMessage();
                    this.toggleEmptyState(true);
                    this.setRecordRange(0, 0, Number(this.state.pagination.total || 0));
                    return;
                }

                this.toggleEmptyState(false);
                this.elements.tbody.innerHTML = this.state.requests.map((request) => this.buildRequestRow(request)).join('');
                this.setRecordRange(
                    Number(this.state.pagination.from || 0),
                    Number(this.state.pagination.to || 0),
                    Number(this.state.pagination.total || 0)
                );
            }

            buildRequestRow(request) {
                const status = String(request.status || 'pending').toLowerCase();
                const studentName = this.escapeHtml(request.studentName || 'Unknown');
                const studentRoll = this.escapeHtml(request.studentRoll || 'N/A');
                const bookTitle = this.escapeHtml(request.bookTitle || 'Unknown');
                const requestDate = this.escapeHtml(request.requestDate || 'N/A');
                const processedBy = this.escapeHtml(request.processedBy || 'N/A');

                return `
                    <tr data-request-id="${Number(request.id)}">
                        <td class="request-select-cell">
                            ${this.buildSelectionCell(request)}
                        </td>
                        <td>
                            <div class="request-student-cell">
                                <div class="request-student-avatar">${this.getAvatarMarkup(request)}</div>
                                <div class="request-student-details">
                                    <span class="request-primary-text">${studentName}</span>
                                    <span class="request-secondary-text">${studentRoll}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="request-book-details">
                                <span class="request-primary-text">${bookTitle}</span>
                                <span class="request-secondary-text">${this.escapeHtml(request.bookAuthor || '')}</span>
                            </div>
                        </td>
                        <td>${requestDate}</td>
                        <td>${this.buildStatusBadge(status)}</td>
                        <td>${processedBy}</td>
                        <td>
                            <div class="request-action-group">
                                ${this.buildActionMarkup(request, status)}
                            </div>
                        </td>
                    </tr>
                `;
            }

            buildSelectionCell(request) {
                const requestId = Number(request.id);
                const isSelectable = this.isRequestSelectable(request);
                const isSelected = this.state.selectedRequestIds.has(requestId);
                const label = isSelectable
                    ? `Select request for ${request.studentName || 'student'} and ${request.bookTitle || 'book'}`
                    : 'Only pending requests can be selected';

                return `
                    <label class="request-checkbox${isSelectable ? '' : ' is-disabled'}" title="${this.escapeAttribute(label)}">
                        <input
                            type="checkbox"
                            class="request-checkbox-input"
                            data-request-checkbox
                            data-request-id="${requestId}"
                            aria-label="${this.escapeAttribute(label)}"
                            ${isSelected ? 'checked' : ''}
                            ${isSelectable ? '' : 'disabled'}
                        >
                        <span class="request-checkbox-control" aria-hidden="true"></span>
                    </label>
                `;
            }

            buildActionMarkup(request, status) {
                if (status === 'pending') {
                    return `
                        <button type="button" class="request-action-btn accept" data-request-action="approved" data-request-id="${Number(request.id)}" aria-label="Accept request">
                            ${svgIcons.approved}
                            <span>Accept</span>
                        </button>
                        <button type="button" class="request-action-btn reject" data-request-action="rejected" data-request-id="${Number(request.id)}" aria-label="Reject request">
                            ${svgIcons.rejected}
                            <span>Reject</span>
                        </button>
                    `;
                }

                return `
                    <span class="request-action-status">
                        ${status === 'approved' ? svgIcons.approved : svgIcons.rejected}
                        <span>${this.escapeHtml(request.statusLabel || this.capitalize(status))}</span>
                    </span>
                `;
            }

            buildStatusBadge(status) {
                const label = this.capitalize(status);
                return `<span class="request-status-badge ${this.escapeAttribute(status)}">${svgIcons[status] || svgIcons.pending}<span>${this.escapeHtml(label)}</span></span>`;
            }

            isRequestSelectable(request) {
                return Boolean(request?.canApprove || request?.canReject || String(request?.status || '').toLowerCase() === 'pending');
            }

            reconcileSelection() {
                const visibleSelectableIds = new Set(this.getSelectableRequestIds());

                this.state.selectedRequestIds = new Set(
                    Array.from(this.state.selectedRequestIds).filter((requestId) => visibleSelectableIds.has(Number(requestId)))
                );
            }

            getSelectableRequestIds() {
                return this.state.requests
                    .filter((request) => this.isRequestSelectable(request))
                    .map((request) => Number(request.id));
            }

            getSelectedRequestIds() {
                return this.getSelectableRequestIds()
                    .filter((requestId) => this.state.selectedRequestIds.has(requestId));
            }

            getSelectedRequests() {
                const selectedIds = new Set(this.getSelectedRequestIds());

                return this.state.requests.filter((request) => selectedIds.has(Number(request.id)));
            }

            setSelection(requestId, isSelected) {
                const normalizedId = Number(requestId);
                if (!normalizedId) {
                    return;
                }

                if (isSelected) {
                    this.state.selectedRequestIds.add(normalizedId);
                } else {
                    this.state.selectedRequestIds.delete(normalizedId);
                }

                this.updateBulkActionState();
            }

            toggleSelectAll(shouldSelect) {
                this.getSelectableRequestIds().forEach((requestId) => {
                    if (shouldSelect) {
                        this.state.selectedRequestIds.add(requestId);
                    } else {
                        this.state.selectedRequestIds.delete(requestId);
                    }
                });

                this.updateBulkActionState();
            }

            clearSelection() {
                this.state.selectedRequestIds.clear();
                this.updateBulkActionState();
            }

            syncSelectionInputs() {
                this.elements.root?.querySelectorAll('[data-request-checkbox]').forEach((checkbox) => {
                    const requestId = Number(checkbox.dataset.requestId);
                    checkbox.checked = this.state.selectedRequestIds.has(requestId);
                });

                if (!this.elements.selectAllCheckbox) {
                    return;
                }

                const selectableIds = this.getSelectableRequestIds();
                const selectedCount = this.getSelectedRequestIds().length;
                const hasSelectable = selectableIds.length > 0;

                this.elements.selectAllCheckbox.disabled = !hasSelectable;
                this.elements.selectAllCheckbox.checked = hasSelectable && selectedCount === selectableIds.length;
                this.elements.selectAllCheckbox.indeterminate = hasSelectable && selectedCount > 0 && selectedCount < selectableIds.length;
            }

            updateBulkActionState() {
                const selectableCount = this.getSelectableRequestIds().length;
                const selectedCount = this.getSelectedRequestIds().length;
                const hasSelection = selectedCount > 0;

                if (this.elements.bulkActionBar) {
                    this.elements.bulkActionBar.classList.toggle('has-selection', hasSelection);
                }

                if (this.elements.bulkSummary) {
                    if (selectableCount === 0) {
                        this.elements.bulkSummary.textContent = 'No pending requests are available in this view yet. Bulk actions only work on pending requests.';
                    } else if (!hasSelection) {
                        this.elements.bulkSummary.textContent = `Select pending requests from the table to accept or reject up to ${selectableCount} request${selectableCount === 1 ? '' : 's'} on this page.`;
                    } else {
                        this.elements.bulkSummary.textContent = `${selectedCount} pending request${selectedCount === 1 ? '' : 's'} selected. Choose an action to process them together.`;
                    }
                }

                if (this.elements.bulkApproveButton) {
                    this.elements.bulkApproveButton.disabled = !hasSelection || this.state.actionInFlight;
                }

                if (this.elements.bulkRejectButton) {
                    this.elements.bulkRejectButton.disabled = !hasSelection || this.state.actionInFlight;
                }

                if (this.elements.clearSelectionButton) {
                    this.elements.clearSelectionButton.disabled = !hasSelection || this.state.actionInFlight;
                }

                this.syncSelectionInputs();
            }

            renderEmptyStateMessage() {
                if (!this.elements.emptyMessage) return;

                const filters = [];
                if (this.state.search) filters.push(`search "${this.state.search}"`);
                if (this.state.status !== 'all') filters.push(`${this.capitalize(this.state.status)} status`);

                this.elements.emptyMessage.textContent = filters.length > 0
                    ? `No requests match ${filters.join(', ')}. Try adjusting your filters.`
                    : 'There are no book requests to show right now.';
            }

            renderToolbarMeta() {
                const segments = [];

                if (this.state.search) {
                    segments.push(`Search: "${this.state.search}"`);
                }

                if (this.state.status !== 'all') {
                    segments.push(`Status: ${this.capitalize(this.state.status)}`);
                }

                segments.push(`Sort: ${this.getSortLabel(this.state.sort)}`);
                const total = Number(this.state.pagination.total || this.state.stats.totalCount || 0);
                const summary = segments.length > 0 ? segments.join(' • ') : 'Showing all requests';

                if (this.elements.filterSummary) {
                    this.elements.filterSummary.innerHTML = `${this.escapeHtml(summary)} • <strong>${total}</strong> matching ${total === 1 ? 'request' : 'requests'}`;
                }

                if (this.elements.lastUpdated) {
                    this.elements.lastUpdated.textContent = this.state.lastUpdatedAt
                        ? `Updated ${this.formatTimeRelative(this.state.lastUpdatedAt)}`
                        : 'Waiting for data...';
                }
            }

            renderPagination() {
                if (!this.elements.paginationButtons) return;

                const currentPage = Math.max(1, Number(this.state.pagination.current_page || this.state.currentPage || 1));
                const lastPage = Math.max(1, Number(this.state.pagination.last_page || 1));
                const total = Math.max(0, Number(this.state.pagination.total || this.state.stats.totalCount || 0));
                this.elements.paginationButtons.innerHTML = '';
                if (this.elements.paginationContainer) {
                    this.elements.paginationContainer.style.display = total > 0 ? 'flex' : 'none';
                }
                if (this.elements.pageInfo) {
                    this.elements.pageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
                }
                if (total === 0) {
                    return;
                }

                this.elements.paginationButtons.appendChild(this.buildPaginationButton(currentPage - 1, '&larr; Previous', currentPage === 1, 'Previous page'));

                this.buildPaginationPages(currentPage, lastPage).forEach((page) => {
                    if (page === null) {
                        const ellipsis = document.createElement('span');
                        ellipsis.className = 'request-pagination-ellipsis';
                        ellipsis.textContent = '...';
                        this.elements.paginationButtons.appendChild(ellipsis);
                        return;
                    }

                    this.elements.paginationButtons.appendChild(this.buildPaginationButton(page, String(page), false, `Page ${page}`, page === currentPage));
                });

                this.elements.paginationButtons.appendChild(this.buildPaginationButton(currentPage + 1, 'Next &rarr;', currentPage === lastPage, 'Next page'));
            }

            buildPaginationPages(currentPage, lastPage) {
                if (lastPage <= 7) {
                    return Array.from({ length: lastPage }, (_, index) => index + 1);
                }

                let startPage = Math.max(2, currentPage - 1);
                let endPage = Math.min(lastPage - 1, currentPage + 1);

                if (currentPage <= 3) {
                    endPage = 4;
                }

                if (currentPage >= lastPage - 2) {
                    startPage = lastPage - 3;
                }

                const pages = [1];

                if (startPage > 2) {
                    pages.push(null);
                }

                for (let page = startPage; page <= endPage; page += 1) {
                    pages.push(page);
                }

                if (endPage < lastPage - 1) {
                    pages.push(null);
                }

                pages.push(lastPage);

                return pages;
            }

            buildPaginationButton(page, label, disabled = false, ariaLabel = '', isActive = false) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = `request-pagination-btn${isActive ? ' is-active' : ''}`;
                button.disabled = disabled;
                button.dataset.page = String(page);
                button.innerHTML = label;
                button.setAttribute('aria-label', ariaLabel || `Page ${page}`);
                if (isActive) {
                    button.setAttribute('aria-current', 'page');
                }
                return button;
            }

            resetFilters() {
                if (this.elements.searchInput) this.elements.searchInput.value = '';
                if (this.elements.statusFilter) this.elements.statusFilter.value = 'all';
                if (this.elements.sortFilter) this.elements.sortFilter.value = 'date-desc';

                this.state.search = '';
                this.state.status = 'all';
                this.state.sort = 'date-desc';
                this.state.currentPage = 1;
                this.loadRequests();
            }

            syncControlsFromState() {
                if (this.elements.searchInput) this.elements.searchInput.value = this.state.search;
                if (this.elements.statusFilter) this.elements.statusFilter.value = this.state.status;
                if (this.elements.sortFilter) this.elements.sortFilter.value = this.state.sort;
                if (this.elements.entriesSelect) this.elements.entriesSelect.value = String(this.state.perPage);
            }

            normalizePerPage(value) {
                const allowedValues = [10, 20, 50, 100];
                const parsed = Number(value);
                return allowedValues.includes(parsed) ? parsed : 10;
            }

            updateBrowserUrl() {
                const params = new URLSearchParams();

                if (this.state.search) params.set('search', this.state.search);
                if (this.state.status !== 'all') params.set('status', this.state.status);
                if (this.state.sort !== 'date-desc') params.set('sort', this.state.sort);
                if (this.state.currentPage > 1) params.set('page', String(this.state.currentPage));
                if (this.state.perPage !== 10) params.set('per_page', String(this.state.perPage));

                const nextUrl = params.toString()
                    ? `${window.location.pathname}?${params.toString()}`
                    : window.location.pathname;

                window.history.replaceState({ url: nextUrl }, '', nextUrl);
            }

            openCreateModal() {
                this.resetCreateForm();
                if (this.elements.createSubmitButton) {
                    this.elements.createSubmitButton.dataset.defaultLabel = 'Create Request';
                    this.elements.createSubmitButton.innerHTML = '<span>Create Request</span>';
                }
                this.openModal('createRequestModal', document.getElementById('studentSearch'));
            }

            resetCreateForm() {
                this.studentSelect?.clear();
                this.bookSelect?.clear();
                this.updateSelectionSummary();
                this.setCreateFieldError('student', false);
                this.setCreateFieldError('book', false);
            }

            updateSelectionSummary() {
                const hasStudent = (this.studentSelect?.getValue() || '') !== '';
                const hasBook = (this.bookSelect?.getValue() || '') !== '';
                const studentText = hasStudent ? (this.studentSelect?.getText() || '') : '';
                const bookText = hasBook ? (this.bookSelect?.getText() || '') : '';

                this.elements.selectedStudentSummary.hidden = !hasStudent;
                this.elements.selectedBookSummary.hidden = !hasBook;
                this.elements.selectionSummary?.classList.toggle('is-visible', hasStudent || hasBook);

                if (this.elements.selectedStudentText) {
                    this.elements.selectedStudentText.textContent = studentText;
                }

                if (this.elements.selectedBookText) {
                    this.elements.selectedBookText.textContent = bookText;
                }
            }

            setCreateFieldError(field, isInvalid) {
                const isStudent = field === 'student';
                const select = isStudent ? this.studentSelect : this.bookSelect;
                const error = isStudent ? this.elements.studentFieldError : this.elements.bookFieldError;

                select?.setInvalid(isInvalid);
                if (error) {
                    error.hidden = !isInvalid;
                }
            }

            validateCreateForm() {
                const hasStudent = (this.studentSelect?.getValue() || '') !== '';
                const hasBook = (this.bookSelect?.getValue() || '') !== '';

                this.setCreateFieldError('student', !hasStudent);
                this.setCreateFieldError('book', !hasBook);

                return hasStudent && hasBook;
            }

            async submitCreateRequest() {
                if (this.state.createInFlight) {
                    return;
                }

                if (!this.validateCreateForm()) {
                    this.showToast('warning', 'Missing details', 'Please select both a student and a book before creating the request.');
                    return;
                }

                this.state.createInFlight = true;
                this.setButtonBusy(this.elements.createSubmitButton, true, 'Creating...');

                try {
                    await this.requestJson(this.config.routes.store, {
                        method: 'POST',
                        body: JSON.stringify({
                            student_id: this.studentSelect?.getValue(),
                            book_id: this.bookSelect?.getValue(),
                        }),
                    });

                    this.closeModal('createRequestModal');
                    this.state.currentPage = 1;
                    await this.loadRequests({ silent: true });
                    this.showToast('success', 'Request created', 'The book request was created successfully.');
                    this.announce('Book request created.');
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to create the book request.');
                    this.showToast('error', 'Create failed', message);
                } finally {
                    this.state.createInFlight = false;
                    this.setButtonBusy(
                        this.elements.createSubmitButton,
                        false,
                        this.elements.createSubmitButton?.dataset.defaultLabel || 'Create Request'
                    );
                }
            }

            openRequestActionModal(requestId, action) {
                const request = this.getRequestById(requestId);
                if (!request) {
                    return;
                }

                const isApprove = action === 'approved';
                const studentName = request.studentName || 'This student';
                const bookTitle = request.bookTitle || 'this book';
                const title = isApprove ? 'Accept Request?' : 'Reject Request?';
                const message = isApprove
                    ? `Approve ${studentName}'s request for "${bookTitle}"? The student will be notified once this request is accepted.`
                    : `Reject ${studentName}'s request for "${bookTitle}"? The student will be notified once this request is rejected.`;
                const detail = `Request date: ${request.requestDate || 'N/A'} • Current status: ${request.statusLabel || this.capitalize(request.status || 'pending')}`;
                const buttonLabel = isApprove ? 'Accept' : 'Reject';

                this.state.pendingAction = {
                    scope: 'single',
                    requestId: Number(requestId),
                    action,
                };
                if (this.feedbackUI) {
                    this.feedbackUI.openConfirm({
                        variant: isApprove ? 'success' : 'danger',
                        buttonVariant: isApprove ? 'success' : 'danger',
                        iconMarkup: isApprove ? svgIcons.approved : svgIcons.rejected,
                        title,
                        message,
                        detail,
                        confirmText: buttonLabel,
                    });
                    return;
                }

                this.elements.actionTitle.textContent = title;
                this.elements.actionMessage.textContent = message;
                this.elements.actionDetail.hidden = false;
                this.elements.actionDetail.textContent = detail;
                this.elements.actionSubmitButton.dataset.defaultLabel = buttonLabel;
                this.elements.actionSubmitButton.innerHTML = `<span>${buttonLabel}</span>`;
                this.openModal('requestActionModal', this.elements.actionSubmitButton);
            }

            openBulkActionModal(action) {
                const selectedRequests = this.getSelectedRequests();
                const selectedIds = selectedRequests.map((request) => Number(request.id));
                const selectedCount = selectedIds.length;

                if (selectedCount === 0) {
                    this.showToast('warning', 'No requests selected', 'Select at least one pending request before using a bulk action.');
                    return;
                }

                const isApprove = action === 'approved';
                const title = isApprove ? 'Accept selected requests?' : 'Reject selected requests?';
                const message = isApprove
                    ? `Accept ${selectedCount} selected request${selectedCount === 1 ? '' : 's'}? Each student will be notified once their request is approved.`
                    : `Reject ${selectedCount} selected request${selectedCount === 1 ? '' : 's'}? Each student will be notified once their request is rejected.`;
                const detail = selectedCount === 1
                    ? `1 pending request selected • ${selectedRequests[0]?.studentName || 'Student'} • ${selectedRequests[0]?.bookTitle || 'Book'}`
                    : `${selectedCount} pending requests selected on this page • Requests that are no longer pending will be skipped automatically.`;
                const buttonLabel = isApprove ? 'Accept Selected' : 'Reject Selected';

                this.state.pendingAction = {
                    scope: 'bulk',
                    requestIds: selectedIds,
                    action,
                };

                if (this.feedbackUI) {
                    this.feedbackUI.openConfirm({
                        variant: isApprove ? 'success' : 'danger',
                        buttonVariant: isApprove ? 'success' : 'danger',
                        iconMarkup: isApprove ? svgIcons.approved : svgIcons.rejected,
                        title,
                        message,
                        detail,
                        confirmText: buttonLabel,
                    });
                    return;
                }

                this.elements.actionTitle.textContent = title;
                this.elements.actionMessage.textContent = message;
                this.elements.actionDetail.hidden = false;
                this.elements.actionDetail.textContent = detail;
                this.elements.actionSubmitButton.dataset.defaultLabel = buttonLabel;
                this.elements.actionSubmitButton.innerHTML = `<span>${buttonLabel}</span>`;
                this.openModal('requestActionModal', this.elements.actionSubmitButton);
            }

            async executeRequestAction() {
                if (this.state.actionInFlight || !this.state.pendingAction) {
                    return;
                }

                const { scope, requestId, requestIds, action } = this.state.pendingAction;
                const isBulkAction = scope === 'bulk';
                this.state.actionInFlight = true;
                this.updateBulkActionState();
                if (this.feedbackUI) {
                    this.feedbackUI.setConfirmBusy(
                        true,
                        action === 'approved'
                            ? (isBulkAction ? 'Accepting selected...' : 'Accepting...')
                            : (isBulkAction ? 'Rejecting selected...' : 'Rejecting...')
                    );
                } else {
                    this.setButtonBusy(
                        this.elements.actionSubmitButton,
                        true,
                        action === 'approved'
                            ? (isBulkAction ? 'Accepting selected...' : 'Accepting...')
                            : (isBulkAction ? 'Rejecting selected...' : 'Rejecting...')
                    );
                }

                try {
                    const response = isBulkAction
                        ? await this.requestJson(this.config.routes.bulkUpdate, {
                            method: 'POST',
                            body: JSON.stringify({
                                status: action,
                                request_ids: requestIds,
                            }),
                        })
                        : await this.requestJson(this.buildRequestRoute(this.config.routes.update, requestId), {
                            method: 'PUT',
                            body: JSON.stringify({ status: action }),
                        });

                    this.closeModal('requestActionModal');
                    if (isBulkAction) {
                        this.clearSelection();
                    }
                    await this.loadRequests({ silent: true });

                    if (isBulkAction) {
                        const processedCount = Number(response.processedCount || 0);
                        const skippedCount = Number(response.skippedCount || 0);
                        const toastType = skippedCount > 0
                            ? 'warning'
                            : (action === 'approved' ? 'success' : 'warning');
                        const toastTitle = action === 'approved'
                            ? (processedCount > 0 ? 'Requests accepted' : 'No requests accepted')
                            : (processedCount > 0 ? 'Requests rejected' : 'No requests rejected');

                        this.showToast(toastType, toastTitle, response.message || 'Bulk request action completed.');
                        this.announce(action === 'approved' ? 'Selected requests accepted.' : 'Selected requests rejected.');
                    } else {
                        this.showToast(
                            action === 'approved' ? 'success' : 'warning',
                            action === 'approved' ? 'Request accepted' : 'Request rejected',
                            action === 'approved'
                                ? 'The request was marked as approved successfully.'
                                : 'The request was marked as rejected successfully.'
                        );
                        this.announce(action === 'approved' ? 'Request accepted.' : 'Request rejected.');
                    }
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to update the book request.');
                    this.showToast('error', 'Update failed', message);
                } finally {
                    this.state.actionInFlight = false;
                    this.updateBulkActionState();
                    if (this.feedbackUI) {
                        this.feedbackUI.setConfirmBusy(false, this.elements.actionSubmitButton?.dataset.defaultLabel || 'Confirm');
                    } else {
                        this.setButtonBusy(
                            this.elements.actionSubmitButton,
                            false,
                            this.elements.actionSubmitButton?.dataset.defaultLabel || 'Confirm'
                        );
                    }
                }
            }

            openModal(modalId, focusTarget) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                this.state.lastFocusedElement = document.activeElement;
                this.state.currentModalId = modalId;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                window.setTimeout(() => focusTarget?.focus(), 25);
            }

            closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                this.state.currentModalId = null;
                if (modalId === 'requestActionModal') {
                    this.state.pendingAction = null;
                    this.elements.actionDetail.hidden = true;
                    this.feedbackUI?.resetConfirm();
                }
                this.state.lastFocusedElement?.focus?.();
            }

            goToPage(page) {
                const lastPage = Math.max(1, Number(this.state.pagination.last_page || 1));
                const nextPage = Math.min(lastPage, Math.max(1, Number(page)));

                if (nextPage === this.state.currentPage) {
                    return;
                }

                this.state.currentPage = nextPage;
                this.loadRequests();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            toggleEmptyState(isVisible) {
                this.elements.emptyState?.classList.toggle('is-visible', isVisible);
                this.elements.emptyState?.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
            }

            setRecordRange(from, to, total) {
                if (this.elements.startCount) {
                    this.elements.startCount.textContent = String(from);
                }

                if (this.elements.endCount) {
                    this.elements.endCount.textContent = String(to);
                }

                if (this.elements.totalCount) {
                    this.elements.totalCount.textContent = String(total);
                }
            }

            setStatsLoading(isLoading) {
                this.elements.statCards.forEach((card) => {
                    card.classList.toggle('is-loading', isLoading);
                });
            }

            setText(id, value) {
                const element = document.getElementById(id);
                if (element) {
                    element.textContent = String(value);
                }
            }

            setButtonBusy(button, isBusy, label) {
                if (!button) return;
                button.disabled = isBusy;
                button.innerHTML = isBusy
                    ? `${svgIcons.spinner}<span>${this.escapeHtml(label)}</span>`
                    : `<span>${this.escapeHtml(label)}</span>`;
            }

            getRequestById(requestId) {
                return this.state.requests.find((request) => Number(request.id) === Number(requestId)) || null;
            }

            buildRequestRoute(template, requestId) {
                return String(template || '').replace('__REQUEST_ID__', String(requestId));
            }

            getAvatarMarkup(request) {
                if (request.studentAvatar) {
                    return `<img src="${this.escapeAttribute(request.studentAvatar)}" alt="${this.escapeAttribute(request.studentName || 'Student')}" loading="lazy">`;
                }

                return this.escapeHtml(this.getInitials(request.studentName || 'S'));
            }

            getInitials(name) {
                const parts = String(name || 'S')
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2);

                return parts.length > 0
                    ? parts.map((part) => part.charAt(0).toUpperCase()).join('')
                    : 'S';
            }

            getSortLabel(sort) {
                const labels = {
                    'date-desc': 'Newest first',
                    'date-asc': 'Oldest first',
                    'student-asc': 'Student A-Z',
                    'book-asc': 'Book A-Z',
                };

                return labels[sort] || 'Newest first';
            }

            formatTimeRelative(date) {
                const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
                if (seconds < 5) return 'just now';
                if (seconds < 60) return `${seconds}s ago`;
                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) return `${minutes}m ago`;
                const hours = Math.floor(minutes / 60);
                if (hours < 24) return `${hours}h ago`;
                return `${Math.floor(hours / 24)}d ago`;
            }

            announce(message) {
                if (!this.elements.liveRegion) return;
                this.elements.liveRegion.textContent = '';
                window.setTimeout(() => {
                    this.elements.liveRegion.textContent = message;
                }, 30);
            }

            async requestJson(url, options = {}) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers || {}),
                };

                const isGetRequest = String(options.method || 'GET').toUpperCase() === 'GET';
                if (!isGetRequest && !(options.body instanceof FormData)) {
                    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                }

                const response = await fetch(url, { ...options, headers });
                const data = await response.json().catch(() => ({}));

                if (!response.ok || data.success === false) {
                    const firstError = data.errors
                        ? Object.values(data.errors).flat().find(Boolean)
                        : null;
                    const error = new Error(data.message || firstError || `HTTP error! status: ${response.status}`);
                    error.name = response.status === 422 ? 'ValidationError' : 'RequestError';
                    error.status = response.status;
                    error.data = data;
                    throw error;
                }

                return data;
            }

            showToast(type, title, message, timeout = 4200) {
                if (this.feedbackUI) {
                    this.feedbackUI.showToast(type, title, message, timeout);
                    return;
                }

                if (!this.elements.toastContainer) return;

                const toast = document.createElement('div');
                toast.className = `request-toast ${type}`;
                toast.innerHTML = `
                    <div class="request-toast-icon">${this.getToastIcon(type)}</div>
                    <div>
                        <div class="request-toast-title">${this.escapeHtml(title)}</div>
                        <div class="request-toast-message">${this.escapeHtml(message)}</div>
                    </div>
                    <button type="button" class="request-toast-close" aria-label="Dismiss notification">${svgIcons.close}</button>
                    <span class="request-toast-progress" style="animation-duration:${timeout}ms;"></span>
                `;

                toast.querySelector('.request-toast-close')?.addEventListener('click', () => this.dismissToast(toast));
                this.elements.toastContainer.appendChild(toast);
                window.requestAnimationFrame(() => toast.classList.add('is-visible'));
                window.setTimeout(() => this.dismissToast(toast), timeout);
            }

            dismissToast(toast) {
                if (this.feedbackUI) {
                    this.feedbackUI.dismissToast(toast);
                    return;
                }

                if (!toast || !toast.parentNode) {
                    return;
                }

                toast.classList.remove('is-visible');
                window.setTimeout(() => toast.remove(), 180);
            }

            getToastIcon(type) {
                if (type === 'error') return svgIcons.rejected;
                if (type === 'info') return svgIcons.pending;
                return svgIcons.approved;
            }

            resolveErrorMessage(error, fallbackMessage) {
                return error?.message || fallbackMessage;
            }

            escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            escapeAttribute(value) {
                return this.escapeHtml(value);
            }

            capitalize(value) {
                const input = String(value || '');
                return input ? input.charAt(0).toUpperCase() + input.slice(1) : '';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const manager = new BookRequestManager(bookRequestManagementConfig);
            manager.init();
        });
    })();
</script>
