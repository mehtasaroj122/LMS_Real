<script>
    (() => {
        const fineManagementConfig = @json($fineManagementConfig);

        const svgIcons = {
            paidBadge: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"></path></svg>',
            waivedBadge: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
            pendingBadge: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>',
            paidAction: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"></rect><path d="M1 10h22"></path></svg>',
            waiveAction: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
            emailAction: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 7L2 7"></path></svg>',
            toastSuccess: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"></path></svg>',
            toastError: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>',
            toastInfo: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>',
            confirmPaid: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path><path d="M7 15h.01"></path><path d="M11 15h2"></path></svg>',
            confirmEmail: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>',
            paginationPrev: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"></path></svg>',
            paginationNext: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"></path></svg>',
            spinner: '<span class="loading-spinner" aria-hidden="true"></span>',
        };

        const finesManager = {
            config: fineManagementConfig,
            state: {
                instanceId: (window.crypto && typeof window.crypto.randomUUID === 'function')
                    ? window.crypto.randomUUID()
                    : `fine-${Date.now()}-${Math.random().toString(36).slice(2)}`,
                currentPage: 1,
                perPage: 10,
                filter: 'all',
                search: '',
                sort: 'date-desc',
                minAmount: '',
                maxAmount: '',
                fines: [],
                stats: {},
                pagination: {},
                currentFineId: null,
                pendingConfirmAction: null,
                currentModalId: null,
                lastFocusedElement: null,
                lastUpdatedAt: null,
                fetchController: null,
                inputDebounceTimer: null,
                broadcastChannel: null,
                storageListener: null,
                actionInFlight: false,
            },
            elements: {},

            init() {
                this.cacheElements();
                if (!this.elements.root) {
                    return;
                }

                this.setupEventListeners();
                this.setupRealtimeChannels();
                this.loadFines();
            },

            cacheElements() {
                this.elements.root = document.getElementById('fineManagementRoot');
                this.elements.searchInput = document.getElementById('searchInput');
                this.elements.statusFilter = document.getElementById('statusFilter');
                this.elements.sortFilter = document.getElementById('sortFilter');
                this.elements.minAmountFilter = document.getElementById('minAmountFilter');
                this.elements.maxAmountFilter = document.getElementById('maxAmountFilter');
                this.elements.exportButton = document.getElementById('exportFinesBtn');
                this.elements.tbody = document.getElementById('finesTableBody');
                this.elements.tableWrapper = document.getElementById('finesTableWrapper');
                this.elements.emptyState = document.getElementById('emptyState');
                this.elements.emptyStateMessage = document.getElementById('emptyStateMessage');
                this.elements.paginationContainer = document.getElementById('paginationContainer');
                this.elements.paginationButtons = document.getElementById('paginationButtons');
                this.elements.recordCount = document.getElementById('recordCount');
                this.elements.totalCount = document.getElementById('totalCount');
                this.elements.filterSummary = document.getElementById('filterSummary');
                this.elements.lastUpdatedLabel = document.getElementById('lastUpdatedLabel');
                this.elements.toastContainer = document.getElementById('fineToastContainer');
                this.elements.liveRegion = document.getElementById('fineLiveRegion');
                this.elements.waiveReason = document.getElementById('waiveReason');
                this.elements.waiveReasonError = document.getElementById('waiveReasonError');
                this.elements.waiveSubmitBtn = document.getElementById('waiveSubmitBtn');
                this.elements.confirmActionIcon = document.getElementById('confirmActionIcon');
                this.elements.confirmActionTitle = document.getElementById('confirmActionTitle');
                this.elements.confirmActionMessage = document.getElementById('confirmActionMessage');
                this.elements.confirmActionDetail = document.getElementById('confirmActionDetail');
                this.elements.confirmActionSubmitBtn = document.getElementById('confirmActionSubmitBtn');
                this.elements.statCards = Array.from(document.querySelectorAll('[data-stat-card]'));
            },

            setupEventListeners() {
                this.elements.searchInput?.addEventListener('input', (event) => {
                    this.state.search = event.target.value.trim();
                    this.state.currentPage = 1;
                    this.scheduleLoad();
                });

                this.elements.statusFilter?.addEventListener('change', (event) => {
                    this.state.filter = event.target.value;
                    this.state.currentPage = 1;
                    this.loadFines();
                });

                this.elements.sortFilter?.addEventListener('change', (event) => {
                    this.state.sort = event.target.value;
                    this.state.currentPage = 1;
                    this.loadFines();
                });

                ['minAmountFilter', 'maxAmountFilter'].forEach((key) => {
                    const element = this.elements[key];
                    element?.addEventListener('input', () => {
                        this.syncFiltersFromInputs();
                        this.state.currentPage = 1;
                        this.scheduleLoad();
                    });
                });

                this.elements.root.addEventListener('click', (event) => {
                    const actionButton = event.target.closest('[data-action]');
                    if (actionButton) {
                        this.handleActionClick(actionButton);
                    }

                    const closeButton = event.target.closest('[data-modal-close]');
                    if (closeButton) {
                        this.closeModal(closeButton.getAttribute('data-modal-close'));
                    }
                });

                this.elements.waiveSubmitBtn?.addEventListener('click', () => this.confirmWaiveFine());
                this.elements.confirmActionSubmitBtn?.addEventListener('click', () => this.executeConfirmedAction());

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
                            this.resetSearchInput();
                        }
                    }
                });
            },

            setupRealtimeChannels() {
                try {
                    this.state.broadcastChannel = new BroadcastChannel('fine_updates');
                    this.state.broadcastChannel.addEventListener('message', (event) => this.handleExternalFineUpdate(event.data));
                } catch (error) {
                    console.warn('[Fines] BroadcastChannel unavailable:', error);
                }

                this.state.storageListener = (event) => {
                    if (event.key !== 'fineUpdated' || !event.newValue) {
                        return;
                    }

                    try {
                        this.handleExternalFineUpdate(JSON.parse(event.newValue));
                    } catch (error) {
                        console.warn('[Fines] Failed to parse fine update payload:', error);
                    }
                };

                window.addEventListener('storage', this.state.storageListener);
            },

            syncFiltersFromInputs() {
                this.state.minAmount = this.elements.minAmountFilter?.value || '';
                this.state.maxAmount = this.elements.maxAmountFilter?.value || '';
            },

            scheduleLoad() {
                window.clearTimeout(this.state.inputDebounceTimer);
                this.state.inputDebounceTimer = window.setTimeout(() => this.loadFines(), 320);
            },

            async loadFines({ silent = false } = {}) {
                this.syncFiltersFromInputs();

                if (!this.validateFilters({ showToast: !silent })) {
                    return;
                }

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
                    status: this.state.filter,
                    sort: this.state.sort,
                    page: String(this.state.currentPage),
                    per_page: String(this.state.perPage),
                });

                if (this.state.minAmount) params.set('min_amount', this.state.minAmount);
                if (this.state.maxAmount) params.set('max_amount', this.state.maxAmount);

                try {
                    const data = await this.requestJson(`${this.config.routes.data}?${params.toString()}`, {
                        method: 'GET',
                        signal: controller.signal,
                    });

                    this.state.fines = Array.isArray(data.fines) ? data.fines : [];
                    this.state.stats = data.stats || {};
                    this.state.pagination = data.pagination || {};
                    this.state.lastUpdatedAt = new Date();
                    this.renderAll();

                    if (!silent) {
                        this.announce('Fine records updated.');
                    }
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error('[Fines] Failed to load fines:', error);
                    this.renderTableError();
                    this.setStatsLoading(false);
                    this.showToast('error', 'Could not load fines', this.resolveErrorMessage(error, 'Something went wrong while loading fine records.'));
                } finally {
                    if (this.state.fetchController === controller) {
                        this.state.fetchController = null;
                    }

                    this.elements.tableWrapper?.setAttribute('aria-busy', 'false');
                }
            },

            validateFilters({ showToast = true } = {}) {
                const invalidFields = [];

                [this.elements.minAmountFilter, this.elements.maxAmountFilter]
                    .filter(Boolean)
                    .forEach((element) => this.setFieldValidity(element, true));

                if (this.state.minAmount && this.state.maxAmount && Number(this.state.minAmount) > Number(this.state.maxAmount)) {
                    invalidFields.push(this.elements.minAmountFilter, this.elements.maxAmountFilter);
                    if (showToast && invalidFields.length <= 2) {
                        this.showToast('error', 'Invalid amount range', 'The maximum amount must be greater than or equal to the minimum amount.');
                    }
                }

                invalidFields.filter(Boolean).forEach((element) => this.setFieldValidity(element, false));
                return invalidFields.length === 0;
            },

            setFieldValidity(element, isValid) {
                if (!element) return;
                element.classList.toggle('is-invalid', !isValid);
                element.setAttribute('aria-invalid', isValid ? 'false' : 'true');
            },

            renderAll() {
                this.renderTable();
                this.renderStats();
                this.renderPagination();
                this.renderToolbarMeta();
                this.updateExportState();
            },

            setStatsLoading(isLoading) {
                this.elements.statCards.forEach((card) => {
                    card.classList.toggle('is-loading', isLoading);
                });
            },

            renderStats() {
                this.setStatsLoading(false);
                const stats = this.state.stats;
                const totalCount = Number(stats.count || 0);
                const paidCount = Number(stats.paid_count || 0);
                const unpaidCount = Number(stats.unpaid_count || 0);
                const waivedCount = Number(stats.waived_count || 0);
                const overdueCount = Number(stats.overdue_count || 0);

                this.setText('totalFines', this.formatCurrency(stats.total || 0));
                this.setText('collectedFines', this.formatCurrency(stats.collected || 0));
                this.setText('pendingFines', this.formatCurrency(stats.pending || 0));
                this.setText('waivedFines', this.formatCurrency(stats.waived || 0));
                this.setText('totalFinesMeta', `${totalCount} ${totalCount === 1 ? 'record' : 'records'}`);
                this.setText('collectedFinesMeta', `${paidCount} paid ${paidCount === 1 ? 'fine' : 'fines'}`);
                this.setText('pendingFinesMeta', `${unpaidCount} unpaid ${unpaidCount === 1 ? 'fine' : 'fines'} • ${overdueCount} overdue`);
                this.setText('waivedFinesMeta', `${waivedCount} waived ${waivedCount === 1 ? 'fine' : 'fines'}`);
            },

            renderTableLoading() {
                if (!this.elements.tbody) return;
                this.elements.emptyState?.classList.remove('is-visible');
                this.elements.emptyState?.setAttribute('aria-hidden', 'true');
                const skeletonRow = `
                    <tr>
                        <td><span class="table-skeleton-line"></span></td>
                        <td><span class="table-skeleton-line"></span></td>
                        <td><span class="table-skeleton-line short"></span></td>
                        <td><span class="table-skeleton-line short"></span></td>
                        <td><span class="table-skeleton-line short"></span></td>
                        <td><span class="table-skeleton-line short"></span></td>
                        <td><span class="table-skeleton-line"></span></td>
                    </tr>
                `;
                this.elements.tbody.innerHTML = Array.from({ length: 5 }).map(() => skeletonRow).join('');
            },

            renderTableError() {
                if (!this.elements.tbody) return;
                this.elements.tbody.innerHTML = '<tr><td colspan="7" class="text-muted" style="padding:1.25rem;text-align:center;">Fine records could not be loaded.</td></tr>';
            },

            renderTable() {
                if (!this.elements.tbody) return;

                if (this.state.fines.length === 0) {
                    this.elements.tbody.innerHTML = '';
                    this.elements.emptyState?.classList.add('is-visible');
                    this.elements.emptyState?.setAttribute('aria-hidden', 'false');
                    this.renderEmptyStateMessage();
                    return;
                }

                this.elements.emptyState?.classList.remove('is-visible');
                this.elements.emptyState?.setAttribute('aria-hidden', 'true');
                this.elements.tbody.innerHTML = this.state.fines.map((fine) => this.buildFineRow(fine)).join('');
            },

            renderEmptyStateMessage() {
                if (!this.elements.emptyStateMessage) return;
                const activeFilters = [];
                if (this.state.search) activeFilters.push(`search "${this.state.search}"`);
                if (this.state.filter !== 'all') activeFilters.push(`${this.capitalize(this.state.filter)} status`);
                if (this.state.minAmount || this.state.maxAmount) activeFilters.push('an amount range');
                this.elements.emptyStateMessage.textContent = activeFilters.length > 0
                    ? `No fines match ${activeFilters.join(', ')}. Try adjusting your filters.`
                    : 'No fine records are available right now.';
            },

            buildFineRow(fine) {
                const status = (fine.status || 'pending').toLowerCase();
                const isOverdue = Boolean(fine.isOverdue) && status === 'pending';
                const studentName = this.escapeHtml(fine.studentName || 'Unknown');
                const studentId = this.escapeHtml(fine.studentId || 'N/A');
                const bookTitle = this.escapeHtml(fine.bookTitle || 'Unknown');
                const dueDate = this.escapeHtml(fine.dueDate || 'N/A');
                const statusLabel = this.escapeHtml(fine.statusLabel || this.capitalize(status));

                return `
                    <tr data-fine-id="${Number(fine.id)}" class="${isOverdue ? 'is-overdue' : ''}">
                        <td>
                            <div class="student-cell">
                                <div class="student-avatar">${this.getStudentAvatarMarkup(fine)}</div>
                                <div class="student-details">
                                    <div class="student-name">${studentName}</div>
                                    <div class="student-roll">${studentId}</div>
                                </div>
                            </div>
                        </td>
                        <td title="${bookTitle}">${bookTitle}</td>
                        <td>
                            <div class="due-date-cell">
                                <span class="text-muted">${dueDate}</span>
                                ${isOverdue ? '<span class="row-indicator overdue">Overdue</span>' : ''}
                            </div>
                        </td>
                        <td class="text-muted">${Number(fine.daysOverdue || 0)}</td>
                        <td><span class="fine-amount ${status}">${this.formatCurrency(fine.fineAmount || 0)}</span></td>
                        <td>
                            <span class="status-badge ${this.getStatusClass(status)}">
                                ${this.getStatusIcon(status)}
                                ${statusLabel}
                            </span>
                        </td>
                        <td>
                            <div class="fine-actions-cell">
                                <div class="action-buttons">
                                    ${this.buildActionButtons(fine)}
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
            },

            buildActionButtons(fine) {
                const status = (fine.status || 'pending').toLowerCase();
                const buttons = [];
                if ((this.config.features.markPaid ?? true) && status === 'pending') {
                    buttons.push(`<button type="button" class="action-btn btn-paid" data-action="mark-paid" data-fine-id="${Number(fine.id)}" aria-label="Mark ${this.escapeAttribute(fine.studentName || 'student')} fine as paid">${svgIcons.paidAction}<span>Paid</span></button>`);
                }
                if ((this.config.features.waive ?? true) && status === 'pending') {
                    buttons.push(`<button type="button" class="action-btn btn-waive" data-action="open-waive" data-fine-id="${Number(fine.id)}" aria-label="Waive ${this.escapeAttribute(fine.studentName || 'student')} fine">${svgIcons.waiveAction}<span>Waive</span></button>`);
                }
                if (this.config.features.sendEmail ?? true) {
                    buttons.push(`<button type="button" class="btn-email" data-action="send-email" data-fine-id="${Number(fine.id)}" aria-label="Send fine email to ${this.escapeAttribute(fine.studentName || 'student')}" title="Send Email">${svgIcons.emailAction}</button>`);
                }
                return buttons.join('');
            },

            getStatusClass(status) {
                if (status === 'paid') return 'status-paid';
                if (status === 'waived') return 'status-waived';
                return 'status-pending';
            },

            getStatusIcon(status) {
                if (status === 'paid') return svgIcons.paidBadge;
                if (status === 'waived') return svgIcons.waivedBadge;
                return svgIcons.pendingBadge;
            },

            renderPagination() {
                const pagination = this.state.pagination || {};
                const total = Number(pagination.total || 0);
                const currentPage = Number(pagination.current_page || 1);
                const lastPage = Math.max(1, Number(pagination.last_page || 1));

                if (this.elements.recordCount) {
                    if (total === 0) {
                        this.elements.recordCount.textContent = '0-0';
                    } else {
                        const start = (currentPage - 1) * this.state.perPage + 1;
                        const end = Math.min(currentPage * this.state.perPage, total);
                        this.elements.recordCount.textContent = `${start}-${end}`;
                    }
                }

                if (this.elements.totalCount) this.elements.totalCount.textContent = String(total);
                if (this.elements.paginationContainer) this.elements.paginationContainer.style.display = total > 0 ? 'flex' : 'none';
                if (!this.elements.paginationButtons) return;

                this.elements.paginationButtons.innerHTML = '';
                if (total === 0) return;

                this.elements.paginationButtons.appendChild(this.createPaginationButton(svgIcons.paginationPrev, currentPage === 1, () => this.goToPage(currentPage - 1), 'Previous page'));
                const startPage = Math.max(1, currentPage - 2);
                const endPage = Math.min(lastPage, currentPage + 2);

                if (startPage > 1) {
                    this.elements.paginationButtons.appendChild(this.createPaginationButton('1', false, () => this.goToPage(1)));
                    if (startPage > 2) this.elements.paginationButtons.appendChild(this.createPaginationEllipsis());
                }

                for (let page = startPage; page <= endPage; page += 1) {
                    const button = this.createPaginationButton(String(page), false, () => this.goToPage(page));
                    if (page === currentPage) {
                        button.classList.add('active');
                        button.setAttribute('aria-current', 'page');
                    }
                    this.elements.paginationButtons.appendChild(button);
                }

                if (endPage < lastPage) {
                    if (endPage < lastPage - 1) this.elements.paginationButtons.appendChild(this.createPaginationEllipsis());
                    this.elements.paginationButtons.appendChild(this.createPaginationButton(String(lastPage), false, () => this.goToPage(lastPage)));
                }

                this.elements.paginationButtons.appendChild(this.createPaginationButton(svgIcons.paginationNext, currentPage === lastPage, () => this.goToPage(currentPage + 1), 'Next page'));
            },

            createPaginationButton(label, disabled, onClick, ariaLabel = '') {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'pagination-btn';
                button.disabled = disabled;
                button.innerHTML = label;
                if (ariaLabel) button.setAttribute('aria-label', ariaLabel);
                button.addEventListener('click', onClick);
                return button;
            },

            createPaginationEllipsis() {
                const span = document.createElement('span');
                span.className = 'pagination-ellipsis';
                span.textContent = '...';
                return span;
            },

            goToPage(page) {
                const lastPage = Math.max(1, Number(this.state.pagination.last_page || 1));
                const safePage = Math.min(lastPage, Math.max(1, Number(page)));
                if (safePage === this.state.currentPage) return;
                this.state.currentPage = safePage;
                this.loadFines();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            renderToolbarMeta() {
                if (this.elements.filterSummary) {
                    const segments = [];
                    if (this.state.search) segments.push(`Search: "${this.state.search}"`);
                    if (this.state.filter !== 'all') segments.push(`Status: ${this.capitalize(this.state.filter)}`);
                    if (this.state.minAmount || this.state.maxAmount) {
                        const minLabel = this.state.minAmount ? this.formatCurrency(this.state.minAmount) : this.formatCurrency(0);
                        const maxLabel = this.state.maxAmount ? this.formatCurrency(this.state.maxAmount) : 'any';
                        segments.push(`Amount: ${minLabel} to ${maxLabel}`);
                    }
                    const total = Number(this.state.pagination.total || this.state.stats.count || 0);
                    const summaryPrefix = segments.length > 0 ? segments.join(' • ') : 'Showing all fines';
                    this.elements.filterSummary.innerHTML = `${this.escapeHtml(summaryPrefix)} • <strong>${total}</strong> matching ${total === 1 ? 'record' : 'records'}`;
                }

                if (this.elements.lastUpdatedLabel) {
                    this.elements.lastUpdatedLabel.textContent = this.state.lastUpdatedAt
                        ? `Updated ${this.formatTimeRelative(this.state.lastUpdatedAt)}`
                        : 'Waiting for data...';
                }
            },

            updateExportState() {
                if (this.elements.exportButton) {
                    this.elements.exportButton.disabled = this.state.fines.length === 0;
                }
            },

            handleActionClick(button) {
                const action = button.getAttribute('data-action');
                const fineId = Number(button.getAttribute('data-fine-id'));

                if (action === 'export') return this.exportToCSV();
                if (action === 'reset-filters') return this.resetFilters();
                if (!fineId) return;
                if (action === 'mark-paid') return this.markAsPaid(fineId);
                if (action === 'open-waive') return this.openWaiveModal(fineId);
                if (action === 'send-email') return this.sendEmailNotification(fineId);
            },

            resetFilters() {
                this.resetSearchInput();
                this.state.filter = 'all';
                this.state.sort = 'date-desc';
                this.state.minAmount = '';
                this.state.maxAmount = '';
                this.state.currentPage = 1;

                if (this.elements.statusFilter) this.elements.statusFilter.value = 'all';
                if (this.elements.sortFilter) this.elements.sortFilter.value = 'date-desc';
                if (this.elements.minAmountFilter) this.elements.minAmountFilter.value = '';
                if (this.elements.maxAmountFilter) this.elements.maxAmountFilter.value = '';

                [this.elements.minAmountFilter, this.elements.maxAmountFilter]
                    .filter(Boolean)
                    .forEach((element) => this.setFieldValidity(element, true));

                this.loadFines();
            },

            resetSearchInput() {
                this.state.search = '';
                this.state.currentPage = 1;
                if (this.elements.searchInput) this.elements.searchInput.value = '';
            },

            markAsPaid(fineId) {
                const fine = this.getFineRecord(fineId);
                if (!fine) return;
                this.openConfirmActionModal('paid', fineId, fine);
            },

            openWaiveModal(fineId) {
                this.state.currentFineId = fineId;
                if (this.elements.waiveReason) this.elements.waiveReason.value = '';
                if (this.elements.waiveReasonError) this.elements.waiveReasonError.hidden = true;
                this.setFieldValidity(this.elements.waiveReason, true);
                this.openModal('waiveModal', this.elements.waiveReason);
            },

            async confirmWaiveFine() {
                if (this.state.actionInFlight) return;

                const fineId = this.state.currentFineId;
                const fine = this.getFineRecord(fineId);
                const reason = (this.elements.waiveReason?.value || '').trim();

                if (!reason) {
                    if (this.elements.waiveReasonError) this.elements.waiveReasonError.hidden = false;
                    this.setFieldValidity(this.elements.waiveReason, false);
                    this.elements.waiveReason?.focus();
                    return;
                }

                this.setFieldValidity(this.elements.waiveReason, true);
                if (this.elements.waiveReasonError) this.elements.waiveReasonError.hidden = true;

                this.state.actionInFlight = true;
                this.setButtonBusy(this.elements.waiveSubmitBtn, true, 'Saving...');

                try {
                    await this.requestJson(this.buildFineRoute(this.config.routes.waive, fineId), {
                        method: 'POST',
                        body: JSON.stringify({ remarks: reason }),
                    });

                    this.closeModal('waiveModal');
                    this.notifyFineUpdate({ fineId, status: 'waived' });
                    this.applyFineStatusChange(fineId, 'waived', { reason });
                    this.showToast('success', 'Fine waived', `${fine?.studentName || 'The student'}'s fine was waived successfully.`);
                    this.announce('Fine waived successfully.');
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to waive the selected fine.');
                    if (this.elements.waiveReasonError) {
                        this.elements.waiveReasonError.hidden = false;
                        this.elements.waiveReasonError.textContent = message;
                    }
                    this.showToast('error', 'Waiver failed', message);
                } finally {
                    this.state.actionInFlight = false;
                    this.setButtonBusy(this.elements.waiveSubmitBtn, false, 'Waive Fine');
                }
            },

            sendEmailNotification(fineId) {
                const fine = this.getFineRecord(fineId);
                if (!fine) return;
                this.openConfirmActionModal('email', fineId, fine, { status: (fine.status || 'pending').toLowerCase() });
            },

            openConfirmActionModal(type, fineId, fine, extra = {}) {
                if (!fine) return;

                const fineAmount = this.formatCurrency(fine.fineAmount || 0);
                const bookTitle = fine.bookTitle || 'this book';
                const studentName = fine.studentName || 'this student';
                const configs = {
                    paid: {
                        iconClass: 'paid',
                        iconMarkup: svgIcons.confirmPaid,
                        title: 'Mark Fine as Paid?',
                        message: `Record ${studentName}'s payment now?`,
                        detail: `${fineAmount} for "${bookTitle}" will be marked as paid.`,
                        buttonLabel: 'Mark as Paid',
                    },
                    email: {
                        iconClass: 'email',
                        iconMarkup: svgIcons.confirmEmail,
                        title: 'Send Fine Email?',
                        message: `Send an email update to ${studentName}?`,
                        detail: `${fineAmount} for "${bookTitle}" will be emailed based on the current ${(extra.status || fine.status || 'pending')} status.`,
                        buttonLabel: 'Send Email',
                    },
                };

                const config = configs[type];
                if (!config) return;

                this.state.pendingConfirmAction = { type, fineId, fine, extra };
                this.elements.confirmActionIcon.className = `action-popup-icon ${config.iconClass}`;
                this.elements.confirmActionIcon.innerHTML = config.iconMarkup;
                this.elements.confirmActionTitle.textContent = config.title;
                this.elements.confirmActionMessage.textContent = config.message;
                this.elements.confirmActionDetail.textContent = config.detail;
                this.elements.confirmActionDetail.hidden = !config.detail;
                this.elements.confirmActionSubmitBtn.textContent = config.buttonLabel;
                this.elements.confirmActionSubmitBtn.dataset.defaultLabel = config.buttonLabel;
                this.openModal('confirmActionModal', this.elements.confirmActionSubmitBtn);
            },

            async executeConfirmedAction() {
                if (this.state.actionInFlight || !this.state.pendingConfirmAction) return;

                const pending = this.state.pendingConfirmAction;
                this.state.actionInFlight = true;
                this.setButtonBusy(this.elements.confirmActionSubmitBtn, true, 'Processing...');

                try {
                    if (pending.type === 'paid') {
                        await this.processMarkAsPaid(pending.fineId, pending.fine);
                    } else if (pending.type === 'email') {
                        await this.processSendEmailNotification(pending.fineId, pending.fine, pending.extra);
                    }
                } finally {
                    this.state.actionInFlight = false;
                    this.state.pendingConfirmAction = null;
                    this.setButtonBusy(
                        this.elements.confirmActionSubmitBtn,
                        false,
                        this.elements.confirmActionSubmitBtn?.dataset.defaultLabel || 'Continue'
                    );
                }
            },

            async processMarkAsPaid(fineId, fine) {
                try {
                    await this.requestJson(this.buildFineRoute(this.config.routes.markPaid, fineId), {
                        method: 'POST',
                    });

                    this.closeModal('confirmActionModal');
                    this.notifyFineUpdate({ fineId, status: 'paid' });
                    this.applyFineStatusChange(fineId, 'paid');
                    this.showToast('success', 'Fine marked as paid', `${fine?.studentName || 'The student'}'s payment was recorded successfully.`);
                    this.announce('Fine marked as paid.');
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to mark this fine as paid.');
                    this.showToast('error', 'Payment update failed', message);
                    throw error;
                }
            },

            async processSendEmailNotification(fineId, fine, extra = {}) {
                try {
                    const data = await this.requestJson(this.buildFineRoute(this.config.routes.sendEmail, fineId), {
                        method: 'POST',
                        body: JSON.stringify({}),
                    });

                    this.closeModal('confirmActionModal');
                    const status = extra.status || fine?.status || 'pending';
                    this.showToast('success', 'Email queued', data.message || `${fine?.studentName || 'The student'} will receive the latest ${status} fine update shortly.`);
                    this.announce('Fine email queued successfully.');
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to send the fine email.');
                    this.showToast('error', 'Email failed', message);
                    throw error;
                }
            },

            applyFineStatusChange(fineId, nextStatus, extra = {}) {
                const fine = this.getFineRecord(fineId);
                if (!fine) return;

                const previousStatus = (fine.status || '').toLowerCase();
                const targetStatus = (nextStatus || '').toLowerCase();
                if (!targetStatus || previousStatus === targetStatus) return;

                this.updateStatsLocally(fine, previousStatus, targetStatus);
                fine.status = targetStatus;
                fine.statusLabel = this.capitalize(targetStatus);
                if (extra.reason) fine.remarks = extra.reason;

                const matchesStatusFilter = this.state.filter === 'all'
                    || this.state.filter === targetStatus
                    || (this.state.filter === 'overdue' && Boolean(fine.isOverdue) && targetStatus === 'pending');

                if (!matchesStatusFilter) {
                    this.state.fines = this.state.fines.filter((item) => Number(item.id) !== Number(fineId));
                    this.updatePaginationAfterRemoval();

                    if (this.state.fines.length === 0 && Number(this.state.pagination.total || 0) > 0 && this.state.currentPage > 1) {
                        this.state.currentPage -= 1;
                        this.loadFines({ silent: true });
                        return;
                    }
                }

                this.renderAll();
            },

            updateStatsLocally(fine, fromStatus, toStatus) {
                const amount = Number(fine.fineAmount || 0);
                const stats = this.state.stats;
                const bucketMap = { pending: 'pending', paid: 'collected', waived: 'waived' };
                const countMap = { pending: 'unpaid_count', paid: 'paid_count', waived: 'waived_count' };
                const fromBucket = bucketMap[fromStatus];
                const toBucket = bucketMap[toStatus];
                const fromCountKey = countMap[fromStatus];
                const toCountKey = countMap[toStatus];

                if (fromBucket) stats[fromBucket] = Math.max(0, Number(stats[fromBucket] || 0) - amount);
                if (toBucket) stats[toBucket] = Number(stats[toBucket] || 0) + amount;
                if (fromCountKey) stats[fromCountKey] = Math.max(0, Number(stats[fromCountKey] || 0) - 1);
                if (toCountKey) stats[toCountKey] = Number(stats[toCountKey] || 0) + 1;
                if (Boolean(fine.isOverdue) && fromStatus === 'pending' && toStatus !== 'pending') {
                    stats.overdue_count = Math.max(0, Number(stats.overdue_count || 0) - 1);
                }
            },

            updatePaginationAfterRemoval() {
                const pagination = this.state.pagination || {};
                const currentTotal = Number(pagination.total || 0);
                const newTotal = Math.max(0, currentTotal - 1);
                const lastPage = Math.max(1, Math.ceil(newTotal / this.state.perPage));
                pagination.total = newTotal;
                pagination.last_page = lastPage;
                pagination.current_page = Math.min(this.state.currentPage, lastPage);
                this.state.currentPage = pagination.current_page;
            },

            notifyFineUpdate(payload) {
                const enrichedPayload = { ...payload, sourceId: this.state.instanceId, timestamp: Date.now() };
                try { localStorage.setItem('fineUpdated', JSON.stringify(enrichedPayload)); } catch (error) { console.warn('[Fines] localStorage sync failed:', error); }
                try { this.state.broadcastChannel?.postMessage(enrichedPayload); } catch (error) { console.warn('[Fines] Broadcast sync failed:', error); }
            },

            handleExternalFineUpdate(payload) {
                if (!payload || payload.sourceId === this.state.instanceId) return;
                this.loadFines({ silent: true });
            },

            openModal(modalId, focusTarget) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                this.state.lastFocusedElement = document.activeElement;
                this.state.currentModalId = modalId;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                window.setTimeout(() => focusTarget?.focus(), 20);
            },

            closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
                this.state.currentModalId = null;
                if (modalId === 'waiveModal') this.state.currentFineId = null;
                if (modalId === 'confirmActionModal') this.state.pendingConfirmAction = null;
                this.state.lastFocusedElement?.focus?.();
            },

            setButtonBusy(button, isBusy, label) {
                if (!button) return;
                button.disabled = isBusy;
                button.innerHTML = isBusy ? `${svgIcons.spinner}<span>${this.escapeHtml(label)}</span>` : `<span>${this.escapeHtml(label)}</span>`;
            },

            async requestJson(url, options = {}) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers || {}),
                };
                const isFormData = options.body instanceof FormData;
                if (!isFormData && options.method && options.method.toUpperCase() !== 'GET') {
                    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                }

                const response = await fetch(url, { ...options, headers });
                const data = await response.json().catch(() => ({}));
                if (!response.ok || data.success === false) {
                    const message = data.message || data.errors?.reason?.[0] || data.errors?.remarks?.[0] || data.errors?.fine?.[0] || `HTTP error! status: ${response.status}`;
                    const error = new Error(message);
                    error.name = response.status === 422 ? 'ValidationError' : 'RequestError';
                    error.status = response.status;
                    error.data = data;
                    throw error;
                }
                return data;
            },

            exportToCSV() {
                if (this.state.fines.length === 0) {
                    this.showToast('error', 'Nothing to export', 'There are no fine records in the current result set.');
                    return;
                }

                const headers = ['User ID', 'User Name', 'Book Title', 'Due Date', 'Days Overdue', 'Fine Amount', 'Status'];
                const rows = this.state.fines.map((fine) => [
                    fine.studentId || 'N/A',
                    fine.studentName || 'Unknown',
                    fine.bookTitle || 'Unknown',
                    fine.dueDate || 'N/A',
                    Number(fine.daysOverdue || 0),
                    this.formatCurrency(fine.fineAmount || 0),
                    fine.statusLabel || this.capitalize(fine.status || 'pending'),
                ]);

                const csv = [headers.join(','), ...rows.map((row) => row.map((cell) => `"${String(cell).replace(/"/g, '""')}"`).join(','))].join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `fines_export_${new Date().toISOString().slice(0, 10)}.csv`;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.setTimeout(() => URL.revokeObjectURL(url), 100);
                this.showToast('success', 'Export ready', 'The current fine list has been exported to CSV.');
            },

            showToast(type, title, message, timeout = 4200) {
                if (!this.elements.toastContainer) return;

                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.setAttribute('role', 'status');
                toast.innerHTML = `
                    <div class="toast-icon">${this.getToastIcon(type)}</div>
                    <div>
                        <div class="toast-title">${this.escapeHtml(title)}</div>
                        <div class="toast-message">${this.escapeHtml(message)}</div>
                    </div>
                    <button type="button" class="toast-close" aria-label="Dismiss notification">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 6-12 12"></path><path d="m6 6 12 12"></path></svg>
                    </button>
                    <span class="toast-progress" style="animation-duration:${timeout}ms;"></span>
                `;

                toast.querySelector('.toast-close')?.addEventListener('click', () => this.dismissToast(toast));
                this.elements.toastContainer.appendChild(toast);
                window.requestAnimationFrame(() => toast.classList.add('is-visible'));
                window.setTimeout(() => this.dismissToast(toast), timeout);
            },

            dismissToast(toast) {
                if (!toast || !toast.parentNode) return;
                toast.classList.remove('is-visible');
                window.setTimeout(() => toast.remove(), 180);
            },

            getToastIcon(type) {
                if (type === 'error') return svgIcons.toastError;
                if (type === 'info') return svgIcons.toastInfo;
                return svgIcons.toastSuccess;
            },

            resolveErrorMessage(error, fallbackMessage) {
                return error?.message || fallbackMessage;
            },

            buildFineRoute(template, fineId) {
                return String(template || '').replace('__FINE_ID__', String(fineId));
            },

            getFineRecord(fineId) {
                return this.state.fines.find((fine) => Number(fine.id) === Number(fineId)) || null;
            },

            getStudentAvatarMarkup(fine) {
                if (fine.studentAvatar) {
                    const safeName = this.escapeAttribute(fine.studentName || 'Student');
                    const safeSrc = this.escapeAttribute(fine.studentAvatar);
                    return `<img src="${safeSrc}" alt="${safeName}" loading="lazy">`;
                }
                return this.escapeHtml(this.getInitials(fine.studentName || 'U'));
            },

            getInitials(name) {
                return String(name || 'U').trim().charAt(0).toUpperCase();
            },

            formatCurrency(amount) {
                const numericAmount = Number(amount || 0);
                return `₹${numericAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            },

            formatDateShort(value) {
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return value;
                return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            },

            formatTimeRelative(date) {
                const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));
                if (seconds < 5) return 'just now';
                if (seconds < 60) return `${seconds}s ago`;
                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) return `${minutes}m ago`;
                const hours = Math.floor(minutes / 60);
                if (hours < 24) return `${hours}h ago`;
                return this.formatDateShort(date.toISOString());
            },

            setText(id, value) {
                const element = document.getElementById(id);
                if (element) element.textContent = value;
            },

            announce(message) {
                if (!this.elements.liveRegion) return;
                this.elements.liveRegion.textContent = '';
                window.setTimeout(() => {
                    this.elements.liveRegion.textContent = message;
                }, 20);
            },

            escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            },

            escapeAttribute(value) {
                return this.escapeHtml(value);
            },

            capitalize(value) {
                const input = String(value || '');
                return input ? input.charAt(0).toUpperCase() + input.slice(1) : '';
            },
        };

        document.addEventListener('DOMContentLoaded', () => finesManager.init());
    })();
</script>
