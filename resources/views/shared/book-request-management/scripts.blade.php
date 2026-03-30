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
                this.input?.addEventListener('input', (event) => this.filterOptions(event.target.value));
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
                    const optionText = String(option.dataset.text || option.textContent || '').toLowerCase();
                    const isVisible = optionText.includes(query);
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
                this.config = config;
                this.elements = {};
                this.studentSelect = null;
                this.bookSelect = null;
                this.state = {
                    currentPage: 1,
                    perPage: 10,
                    search: '',
                    status: 'all',
                    sort: 'date-desc',
                    requests: [],
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
            }

            init() {
                this.cacheElements();
                if (!this.elements.root) {
                    return;
                }

                this.initSelects();
                this.setupEventListeners();
                this.loadRequests();
            }

            cacheElements() {
                this.elements.root = document.getElementById('bookRequestManagementRoot');
                this.elements.searchInput = document.getElementById('requestSearchInput');
                this.elements.statusFilter = document.getElementById('requestStatusFilter');
                this.elements.sortFilter = document.getElementById('requestSortFilter');
                this.elements.resetButton = document.getElementById('requestResetFiltersBtn');
                this.elements.createButton = document.getElementById('createRequestBtn');
                this.elements.tableWrapper = document.getElementById('requestsTableWrapper');
                this.elements.tbody = document.getElementById('requestsTableBody');
                this.elements.emptyState = document.getElementById('requestsEmptyState');
                this.elements.emptyMessage = document.getElementById('requestsEmptyMessage');
                this.elements.filterSummary = document.getElementById('requestFilterSummary');
                this.elements.lastUpdated = document.getElementById('requestLastUpdated');
                this.elements.paginationButtons = document.getElementById('requestPaginationButtons');
                this.elements.recordCount = document.getElementById('requestRecordCount');
                this.elements.totalCount = document.getElementById('requestTotalCount');
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
                this.elements.actionNote = document.getElementById('requestActionNote');
                this.elements.actionDetail = document.getElementById('requestActionDetail');
                this.elements.actionSubmitButton = document.getElementById('confirmRequestActionBtn');
                this.elements.toastContainer = document.getElementById('requestToastContainer');
                this.elements.liveRegion = document.getElementById('requestLiveRegion');
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

                this.elements.resetButton?.addEventListener('click', () => this.resetFilters());
                this.elements.createButton?.addEventListener('click', () => this.openCreateModal());
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

                document.querySelectorAll('.request-modal-backdrop').forEach((modal) => {
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

                    const lastPage = Math.max(1, Number(this.state.pagination.last_page || 1));
                    if (this.state.currentPage > lastPage) {
                        this.state.currentPage = lastPage;
                        await this.loadRequests({ silent: true });
                        return;
                    }

                    this.renderAll();

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

                this.setText('pendingRequestCount', pending);
                this.setText('approvedRequestCount', approved);
                this.setText('rejectedRequestCount', rejected);
                this.setText('pendingRequestMeta', `${pending} waiting for review`);
                this.setText('approvedRequestMeta', `${approved} accepted ${approved === 1 ? 'request' : 'requests'}`);
                this.setText('rejectedRequestMeta', `${rejected} rejected ${rejected === 1 ? 'request' : 'requests'}`);
                this.setText('requestTotalCount', total);
            }

            renderTableLoading() {
                if (!this.elements.tbody) return;
                this.toggleEmptyState(false);

                const row = `
                    <tr>
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
                this.elements.tbody.innerHTML = '<tr><td colspan="6" style="padding:1.2rem;text-align:center;color:var(--request-text-secondary);">Book requests could not be loaded.</td></tr>';
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
                this.elements.paginationButtons.innerHTML = '';

                this.elements.paginationButtons.appendChild(this.buildPaginationButton(currentPage - 1, svgIcons.prev, currentPage === 1, 'Previous page'));

                const pages = [];
                for (let page = 1; page <= lastPage; page += 1) {
                    if (page === 1 || page === lastPage || Math.abs(page - currentPage) <= 1) {
                        pages.push(page);
                    }
                }

                let previousPage = 0;
                pages.forEach((page) => {
                    if (page - previousPage > 1) {
                        const ellipsis = document.createElement('span');
                        ellipsis.className = 'request-pagination-ellipsis';
                        ellipsis.textContent = '...';
                        this.elements.paginationButtons.appendChild(ellipsis);
                    }

                    this.elements.paginationButtons.appendChild(this.buildPaginationButton(page, String(page), false, `Page ${page}`, page === currentPage));
                    previousPage = page;
                });

                this.elements.paginationButtons.appendChild(this.buildPaginationButton(currentPage + 1, svgIcons.next, currentPage === lastPage, 'Next page'));
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
                const studentText = this.studentSelect?.getText() || '';
                const bookText = this.bookSelect?.getText() || '';
                const hasStudent = studentText !== '';
                const hasBook = bookText !== '';

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
                    this.showToast('error', 'Missing details', 'Please select both a student and a book before creating the request.');
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

                this.state.pendingAction = { requestId: Number(requestId), action };
                this.elements.actionIcon.className = `request-action-icon ${isApprove ? 'accept' : 'reject'}`;
                this.elements.actionIcon.innerHTML = isApprove ? svgIcons.approved : svgIcons.rejected;
                this.elements.actionTitle.textContent = isApprove ? 'Accept Request?' : 'Reject Request?';
                this.elements.actionMessage.textContent = isApprove
                    ? `Approve ${studentName}'s request for "${bookTitle}"?`
                    : `Reject ${studentName}'s request for "${bookTitle}"?`;
                this.elements.actionNote.textContent = isApprove
                    ? 'The student will be notified that the request was accepted.'
                    : 'The student will be notified that the request was rejected.';
                this.elements.actionDetail.hidden = false;
                this.elements.actionDetail.textContent = `Request date: ${request.requestDate || 'N/A'} • Current status: ${request.statusLabel || this.capitalize(request.status || 'pending')}`;
                this.elements.actionSubmitButton.dataset.defaultLabel = isApprove ? 'Accept' : 'Reject';
                this.elements.actionSubmitButton.innerHTML = `<span>${isApprove ? 'Accept' : 'Reject'}</span>`;
                this.openModal('requestActionModal', this.elements.actionSubmitButton);
            }

            async executeRequestAction() {
                if (this.state.actionInFlight || !this.state.pendingAction) {
                    return;
                }

                const { requestId, action } = this.state.pendingAction;
                this.state.actionInFlight = true;
                this.setButtonBusy(this.elements.actionSubmitButton, true, action === 'approved' ? 'Accepting...' : 'Rejecting...');

                try {
                    await this.requestJson(this.buildRequestRoute(this.config.routes.update, requestId), {
                        method: 'PUT',
                        body: JSON.stringify({ status: action }),
                    });

                    this.closeModal('requestActionModal');
                    await this.loadRequests({ silent: true });
                    this.showToast(
                        'success',
                        action === 'approved' ? 'Request accepted' : 'Request rejected',
                        action === 'approved'
                            ? 'The request was marked as approved successfully.'
                            : 'The request was marked as rejected successfully.'
                    );
                    this.announce(action === 'approved' ? 'Request accepted.' : 'Request rejected.');
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to update the book request.');
                    this.showToast('error', 'Update failed', message);
                } finally {
                    this.state.actionInFlight = false;
                    this.state.pendingAction = null;
                    this.setButtonBusy(
                        this.elements.actionSubmitButton,
                        false,
                        this.elements.actionSubmitButton?.dataset.defaultLabel || 'Confirm'
                    );
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
                if (this.elements.recordCount) {
                    this.elements.recordCount.textContent = `${from}-${to}`;
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
