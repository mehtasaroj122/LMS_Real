<script>
    (() => {
        const fineManagementConfig = @json($fineManagementConfig);
        const defaultLibraryBranding = window.LibraryBranding?.normalize
            ? window.LibraryBranding.normalize(window.__LIBRARY_BRANDING__ ?? @json($libraryBranding))
            : (window.__LIBRARY_BRANDING__ ?? @json($libraryBranding));
        const systemTitle = defaultLibraryBranding?.name || 'Library Management System';

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
                initialSearchParams: new URLSearchParams(window.location.search),
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
                selectedFineIds: new Set(),
                exportScope: 'page',
                exportPreparedAt: null,
                exportAllRows: [],
                exportAllRowsFilterKey: '',
                exportAllRowsGeneratedAt: null,
                exportAllRowsLoading: false,
                stats: {},
                pagination: {},
                currentWaiveContext: null,
                pendingConfirmAction: null,
                currentModalId: null,
                lastFocusedElement: null,
                lastUpdatedAt: null,
                fetchController: null,
                exportFetchController: null,
                inputDebounceTimer: null,
                broadcastChannel: null,
                storageListener: null,
                actionInFlight: false,
            },
            elements: {},
            feedbackUI: null,
            exportWorkflow: null,

            init() {
                this.cacheElements();
                if (!this.elements.root) {
                    return;
                }

                this.initializeFeedbackUI();
                this.initializeExportWorkflow();
                this.hydrateStateFromUrl();
                this.syncControlsFromState();
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
                this.elements.entriesSelect = document.getElementById('fineEntriesSelect');
                this.elements.exportButton = document.getElementById('exportFinesBtn');
                this.elements.bulkActionBar = document.getElementById('fineBulkActionBar');
                this.elements.bulkSummary = document.getElementById('fineBulkSummary');
                this.elements.bulkMarkPaidBtn = document.getElementById('bulkMarkPaidBtn');
                this.elements.bulkWaiveBtn = document.getElementById('bulkWaiveBtn');
                this.elements.bulkEmailBtn = document.getElementById('bulkEmailBtn');
                this.elements.clearSelectedFinesBtn = document.getElementById('clearSelectedFinesBtn');
                this.elements.selectAllCheckbox = document.getElementById('fineSelectAll');
                this.elements.tbody = document.getElementById('finesTableBody');
                this.elements.tableWrapper = document.getElementById('finesTableWrapper');
                this.elements.emptyState = document.getElementById('emptyState');
                this.elements.emptyStateMessage = document.getElementById('emptyStateMessage');
                this.elements.paginationContainer = document.getElementById('paginationContainer');
                this.elements.paginationButtons = document.getElementById('paginationButtons');
                this.elements.startCount = document.getElementById('startCount');
                this.elements.endCount = document.getElementById('endCount');
                this.elements.totalCount = document.getElementById('totalCount');
                this.elements.pageInfo = document.getElementById('pageInfo');
                this.elements.filterSummary = document.getElementById('filterSummary');
                this.elements.lastUpdatedLabel = document.getElementById('lastUpdatedLabel');
                this.elements.toastContainer = document.getElementById('fineToastContainer');
                this.elements.liveRegion = document.getElementById('fineLiveRegion');
                this.elements.waiveModalTitle = document.getElementById('waiveModalTitle');
                this.elements.waiveModalDescription = document.getElementById('waiveModalDescription');
                this.elements.waiveReason = document.getElementById('waiveReason');
                this.elements.waiveReasonError = document.getElementById('waiveReasonError');
                this.elements.waiveSubmitBtn = document.getElementById('waiveSubmitBtn');
                this.elements.confirmActionIcon = document.getElementById('confirmActionIcon');
                this.elements.confirmActionTitle = document.getElementById('confirmActionTitle');
                this.elements.confirmActionMessage = document.getElementById('confirmActionMessage');
                this.elements.confirmActionDetail = document.getElementById('confirmActionDetail');
                this.elements.confirmActionSubmitBtn = document.getElementById('confirmActionSubmitBtn');
                this.elements.exportScopeInputs = Array.from(document.querySelectorAll('input[name="exportScope"]'));
                this.elements.exportModalBadge = document.getElementById('exportModalBadge');
                this.elements.exportSummaryGrid = document.getElementById('exportSummaryGrid');
                this.elements.exportModalHeadline = document.getElementById('exportModalHeadline');
                this.elements.exportModalSubtext = document.getElementById('exportModalSubtext');
                this.elements.exportPreviewCaption = document.getElementById('exportPreviewCaption');
                this.elements.exportPreviewCount = document.getElementById('exportPreviewCount');
                this.elements.exportDocumentTimestamp = document.getElementById('exportDocumentTimestamp');
                this.elements.exportFooterNote = document.getElementById('exportFooterNote');
                this.elements.exportPreviewTableBody = document.getElementById('exportPreviewTableBody');
                this.elements.exportPrintBtn = document.getElementById('exportPrintBtn');
                this.elements.exportPrintBtnLabel = this.elements.exportPrintBtn?.querySelector('span') || null;
                this.elements.exportDownloadBtn = document.getElementById('exportDownloadBtn');
                this.elements.exportDownloadBtnLabel = this.elements.exportDownloadBtn?.querySelector('span') || null;
                this.elements.statCards = Array.from(document.querySelectorAll('[data-stat-card]'));
            },

            initializeFeedbackUI() {
                if (typeof window.ActionFeedbackUI !== 'function') {
                    return;
                }

                this.feedbackUI = new window.ActionFeedbackUI({
                    confirm: {
                        modalId: 'confirmActionModal',
                        iconId: 'confirmActionIcon',
                        titleId: 'confirmActionTitle',
                        messageId: 'confirmActionMessage',
                        detailId: 'confirmActionDetail',
                        submitButtonId: 'confirmActionSubmitBtn',
                        confirmLabel: 'Continue',
                    },
                    toast: {
                        containerId: 'fineToastContainer',
                        liveRegionId: 'fineLiveRegion',
                    },
                    openModal: (modalId, focusTarget) => this.openModal(modalId, focusTarget),
                    closeModal: (modalId) => this.closeModal(modalId),
                    setButtonBusy: (button, isBusy, label) => this.setButtonBusy(button, isBusy, label),
                });
            },

            initializeExportWorkflow() {
                if (typeof window.ReportExportWorkflow !== 'function') {
                    return;
                }

                this.exportWorkflow = new window.ReportExportWorkflow({
                    modalId: 'exportOptionsModal',
                    idPrefix: 'reportExport',
                    scopeName: 'reportExportScope',
                    downloadFormat: 'excel-xml',
                    sheetName: 'Fine Report',
                    routes: {
                        exportData: this.config.routes?.exportData,
                    },
                    document: {
                        systemTitle: systemTitle,
                        reportTitle: 'Fine Report',
                    },
                    labels: {
                        printButton: 'Print List',
                        allScopePrintButton: 'Print Full Report',
                        downloadButton: 'Download Excel',
                        allScopeDownloadButton: 'Download Full Excel',
                    },
                    messages: {
                        emptyMessage: 'There are no fine records in the current result set.',
                        preparingMessage: 'Please wait until the full fine report finishes loading.',
                        printReadyMessage: 'The print dialog will open in a new window for the current fine list.',
                        fullPrintReadyMessage: 'The print dialog will open in a new window for the full filtered fine report.',
                        exportReadyMessage: 'The current fine list has been exported to Excel.',
                        fullExportReadyMessage: 'The full filtered fine report has been exported to Excel.',
                        exportRouteMissingMessage: 'The full fine report endpoint is not available right now.',
                        fullLoadFailedMessage: 'Something went wrong while preparing the full fine report.',
                    },
                    columns: [
                        { key: 'studentId', label: 'User ID', width: '14%' },
                        { key: 'studentName', label: 'User Name', width: '17%' },
                        { key: 'bookTitle', label: 'Book Title', width: '28%', emphasis: true },
                        { key: 'dueDate', label: 'Due Date', width: '14%' },
                        { key: 'daysOverdue', label: 'Days Overdue', width: '10%', align: 'center' },
                        { key: 'fineAmount', label: 'Fine Amount', width: '11%', align: 'right', nowrap: true },
                        { key: 'status', label: 'Status', width: '10%', align: 'center', nowrap: true },
                    ],
                    openModal: (modalId, focusTarget) => this.openModal(modalId, focusTarget),
                    closeModal: (modalId) => this.closeModal(modalId),
                    showToast: (type, title, message, timeout) => this.showToast(type, title, message, timeout),
                    requestJson: (url, options) => this.requestJson(url, options),
                    getCurrentRows: () => this.state.fines,
                    mapRow: (fine) => this.mapFineToExportRow(fine),
                    buildFilterParams: () => this.buildExportFilterParams(),
                    getListingState: () => ({
                        total: Number(this.state.pagination.total || this.state.stats.count || this.state.fines.length),
                        currentPage: Math.max(1, Number(this.state.pagination.current_page || this.state.currentPage || 1)),
                        lastPage: Math.max(1, Number(this.state.pagination.last_page || 1)),
                        perPage: Math.max(1, Number(this.state.perPage || 10)),
                    }),
                    getScopeLabel: (scope) => this.getExportScopeLabel(scope),
                    getFilename: (context) => this.getExportFilename(context, 'excel'),
                    extractAllRows: (data) => Array.isArray(data?.fines) ? data.fines : [],
                    extractGeneratedAt: (data) => data?.meta?.generated_at || null,
                    getExportMetaRows: (context) => this.buildFineReportExportMetaRows(context),
                    describeContext: (context) => this.describeExportContext(context),
                }).init();
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

                this.elements.entriesSelect?.addEventListener('change', (event) => {
                    this.state.perPage = this.normalizePerPage(event.target.value);
                    this.state.currentPage = 1;
                    this.loadFines();
                });

                this.elements.bulkMarkPaidBtn?.addEventListener('click', () => this.openBulkMarkPaidModal());
                this.elements.bulkWaiveBtn?.addEventListener('click', () => this.openBulkWaiveModal());
                this.elements.bulkEmailBtn?.addEventListener('click', () => this.openBulkEmailModal());
                this.elements.clearSelectedFinesBtn?.addEventListener('click', () => this.clearSelection());
                this.elements.selectAllCheckbox?.addEventListener('change', (event) => {
                    this.toggleSelectAll(Boolean(event.target.checked));
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

                this.elements.root.addEventListener('change', (event) => {
                    const fineCheckbox = event.target.closest('[data-fine-checkbox]');
                    if (fineCheckbox) {
                        this.setSelection(fineCheckbox.dataset.fineId, Boolean(fineCheckbox.checked));
                    }
                });

                this.elements.waiveSubmitBtn?.addEventListener('click', () => this.confirmWaiveFine());
                this.elements.confirmActionSubmitBtn?.addEventListener('click', () => this.executeConfirmedAction());
                this.elements.exportPrintBtn?.addEventListener('click', () => this.printCurrentList());
                this.elements.exportDownloadBtn?.addEventListener('click', () => this.downloadExportFile());
                this.elements.exportScopeInputs.forEach((input) => {
                    input.addEventListener('change', (event) => {
                        void this.setExportScope(event.target.value);
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
                    this.state.exportPreparedAt = new Date();
                    this.clearAllExportRowsCache();
                    this.reconcileSelection();

                    const lastPage = Math.max(1, Number(this.state.pagination.last_page || 1));
                    if (this.state.currentPage > lastPage) {
                        this.state.currentPage = lastPage;
                        await this.loadFines({ silent: true });
                        return;
                    }

                    this.state.currentPage = Math.max(1, Number(this.state.pagination.current_page || this.state.currentPage || 1));
                    this.state.perPage = this.normalizePerPage(this.state.pagination.per_page || this.state.perPage);
                    this.renderAll();
                    this.syncControlsFromState();
                    this.updateBrowserUrl();

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
                        this.showToast('warning', 'Invalid amount range', 'The maximum amount must be greater than or equal to the minimum amount.');
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
                this.updateBulkActionState();
                this.updateExportState();
                if (this.state.currentModalId === (this.exportWorkflow?.getModalId?.() || 'exportOptionsModal')) {
                    this.renderExportPreview();
                }
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
                        <td><span class="table-skeleton-line short"></span></td>
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
                this.elements.tbody.innerHTML = '<tr><td colspan="8" class="text-muted" style="padding:1.25rem;text-align:center;">Fine records could not be loaded.</td></tr>';
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
                        <td class="fine-select-cell">
                            ${this.buildSelectionCell(fine)}
                        </td>
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

            buildSelectionCell(fine) {
                const fineId = Number(fine.id);
                const isSelected = this.state.selectedFineIds.has(fineId);
                const label = `Select fine for ${fine.studentName || 'student'} and ${fine.bookTitle || 'book'}`;

                return `
                    <label class="fine-checkbox" title="${this.escapeAttribute(label)}">
                        <input
                            type="checkbox"
                            class="fine-checkbox-input"
                            data-fine-checkbox
                            data-fine-id="${fineId}"
                            aria-label="${this.escapeAttribute(label)}"
                            ${isSelected ? 'checked' : ''}
                        >
                        <span class="fine-checkbox-control" aria-hidden="true"></span>
                    </label>
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

            isFineSelectable(fine) {
                return String(fine?.status || '').toLowerCase() === 'pending';
            },

            reconcileSelection() {
                const visibleFineIds = new Set(this.getVisibleFineIds());

                this.state.selectedFineIds = new Set(
                    Array.from(this.state.selectedFineIds).filter((fineId) => visibleFineIds.has(Number(fineId)))
                );
            },

            getVisibleFineIds() {
                return this.state.fines
                    .map((fine) => Number(fine.id))
                    .filter((fineId) => fineId > 0);
            },

            getSelectableFineIds() {
                return this.state.fines
                    .filter((fine) => this.isFineSelectable(fine))
                    .map((fine) => Number(fine.id));
            },

            getSelectedFineIds() {
                return this.getVisibleFineIds()
                    .filter((fineId) => this.state.selectedFineIds.has(fineId));
            },

            getSelectedFines() {
                const selectedIds = new Set(this.getSelectedFineIds());

                return this.state.fines.filter((fine) => selectedIds.has(Number(fine.id)));
            },

            getPendingSelectedFineIds() {
                return this.getSelectableFineIds()
                    .filter((fineId) => this.state.selectedFineIds.has(fineId));
            },

            getPendingSelectedFines() {
                const selectedIds = new Set(this.getPendingSelectedFineIds());

                return this.state.fines.filter((fine) => selectedIds.has(Number(fine.id)));
            },

            setSelection(fineId, isSelected) {
                const normalizedId = Number(fineId);
                if (!normalizedId) return;

                if (isSelected) {
                    this.state.selectedFineIds.add(normalizedId);
                } else {
                    this.state.selectedFineIds.delete(normalizedId);
                }

                this.updateBulkActionState();
            },

            toggleSelectAll(shouldSelect) {
                this.getVisibleFineIds().forEach((fineId) => {
                    if (shouldSelect) {
                        this.state.selectedFineIds.add(fineId);
                    } else {
                        this.state.selectedFineIds.delete(fineId);
                    }
                });

                this.updateBulkActionState();
            },

            clearSelection() {
                this.state.selectedFineIds.clear();
                this.updateBulkActionState();
            },

            syncSelectionInputs() {
                this.elements.root?.querySelectorAll('[data-fine-checkbox]').forEach((checkbox) => {
                    const fineId = Number(checkbox.dataset.fineId);
                    checkbox.checked = this.state.selectedFineIds.has(fineId);
                });

                if (!this.elements.selectAllCheckbox) {
                    return;
                }

                const visibleIds = this.getVisibleFineIds();
                const selectedCount = this.getSelectedFineIds().length;
                const hasVisibleRows = visibleIds.length > 0;

                this.elements.selectAllCheckbox.disabled = !hasVisibleRows;
                this.elements.selectAllCheckbox.checked = hasVisibleRows && selectedCount === visibleIds.length;
                this.elements.selectAllCheckbox.indeterminate = hasVisibleRows && selectedCount > 0 && selectedCount < visibleIds.length;
            },

            updateBulkActionState() {
                const visibleCount = this.getVisibleFineIds().length;
                const selectableCount = this.getSelectableFineIds().length;
                const selectedFines = this.getSelectedFines();
                const selectedCount = selectedFines.length;
                const pendingSelectedFines = this.getPendingSelectedFines();
                const pendingSelectedCount = pendingSelectedFines.length;
                const hasSelection = selectedCount > 0;
                const hasPendingSelection = pendingSelectedCount > 0;

                if (this.elements.bulkActionBar) {
                    this.elements.bulkActionBar.classList.toggle('has-selection', hasSelection);
                }

                if (this.elements.bulkSummary) {
                    if (visibleCount === 0) {
                        this.elements.bulkSummary.textContent = 'No fine records are available in this view right now.';
                    } else if (!hasSelection) {
                        this.elements.bulkSummary.textContent = selectableCount > 0
                            ? `Select fines from the table to send emails, or choose from ${selectableCount} pending fine${selectableCount === 1 ? '' : 's'} on this page to mark them as paid or waive them.`
                            : 'Select fines from the table to send emails. Mark as paid and waive are available only for pending fines.';
                    } else if (hasPendingSelection) {
                        const selectedAmount = selectedFines.reduce((sum, fine) => sum + Number(fine.fineAmount || 0), 0);
                        const pendingAmount = pendingSelectedFines.reduce((sum, fine) => sum + Number(fine.fineAmount || 0), 0);
                        const emailOnlyCount = selectedCount - pendingSelectedCount;

                        this.elements.bulkSummary.textContent = emailOnlyCount > 0
                            ? `${selectedCount} fine${selectedCount === 1 ? '' : 's'} selected for ${this.formatCurrency(selectedAmount)}. ${pendingSelectedCount} pending fine${pendingSelectedCount === 1 ? '' : 's'} totaling ${this.formatCurrency(pendingAmount)} can be marked as paid or waived, and ${emailOnlyCount} selected fine${emailOnlyCount === 1 ? '' : 's'} can receive email only.`
                            : `${pendingSelectedCount} pending fine${pendingSelectedCount === 1 ? '' : 's'} selected for ${this.formatCurrency(pendingAmount)}.`;
                    } else {
                        this.elements.bulkSummary.textContent = `${selectedCount} selected fine${selectedCount === 1 ? '' : 's'} can receive email. Mark as paid and waive only work on pending fines.`;
                    }
                }

                if (this.elements.bulkMarkPaidBtn) {
                    this.elements.bulkMarkPaidBtn.disabled = !hasPendingSelection || this.state.actionInFlight;
                }

                if (this.elements.bulkWaiveBtn) {
                    this.elements.bulkWaiveBtn.disabled = !hasPendingSelection || this.state.actionInFlight;
                }

                if (this.elements.bulkEmailBtn) {
                    this.elements.bulkEmailBtn.disabled = !hasSelection || this.state.actionInFlight || !this.config.routes?.bulkEmail;
                }

                if (this.elements.clearSelectedFinesBtn) {
                    this.elements.clearSelectedFinesBtn.disabled = !hasSelection || this.state.actionInFlight;
                }

                this.syncSelectionInputs();
            },

            renderPagination() {
                const pagination = this.state.pagination || {};
                const total = Number(pagination.total || 0);
                const currentPage = Number(pagination.current_page || 1);
                const lastPage = Math.max(1, Number(pagination.last_page || 1));

                if (this.elements.startCount) {
                    const start = total === 0 ? 0 : Number(pagination.from || ((currentPage - 1) * this.state.perPage) + 1);
                    this.elements.startCount.textContent = String(start);
                }

                if (this.elements.endCount) {
                    const end = total === 0 ? 0 : Number(pagination.to || Math.min(currentPage * this.state.perPage, total));
                    this.elements.endCount.textContent = String(end);
                }

                if (this.elements.totalCount) this.elements.totalCount.textContent = String(total);
                if (this.elements.pageInfo) this.elements.pageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
                if (this.elements.paginationContainer) this.elements.paginationContainer.style.display = total > 0 ? 'flex' : 'none';
                if (!this.elements.paginationButtons) return;

                this.elements.paginationButtons.innerHTML = '';
                if (total === 0) return;

                this.elements.paginationButtons.appendChild(this.createPaginationButton('&larr; Previous', currentPage === 1, () => this.goToPage(currentPage - 1), 'Previous page'));

                this.buildPaginationPages(currentPage, lastPage).forEach((page) => {
                    if (page === null) {
                        this.elements.paginationButtons.appendChild(this.createPaginationEllipsis());
                        return;
                    }

                    const button = this.createPaginationButton(String(page), false, () => this.goToPage(page), `Page ${page}`);
                    if (page === currentPage) {
                        button.classList.add('active');
                        button.setAttribute('aria-current', 'page');
                    }
                    this.elements.paginationButtons.appendChild(button);
                });

                this.elements.paginationButtons.appendChild(this.createPaginationButton('Next &rarr;', currentPage === lastPage, () => this.goToPage(currentPage + 1), 'Next page'));
            },

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

                if (action === 'export') return this.openExportModal();
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

            openExportModal() {
                if (this.exportWorkflow) {
                    this.exportWorkflow.open();
                    return;
                }

                if (this.state.fines.length === 0) {
                    this.showToast('warning', 'Nothing to export', 'There are no fine records in the current result set.');
                    return;
                }

                this.state.exportScope = 'page';
                this.state.exportPreparedAt = new Date();
                this.syncExportScopeControls();
                this.renderExportPreview();
                this.openModal('exportOptionsModal', this.elements.exportPrintBtn || this.elements.exportDownloadBtn);
            },

            hydrateStateFromUrl() {
                const params = this.state.initialSearchParams;
                this.state.currentPage = Math.max(1, Number(params.get('page')) || 1);
                this.state.perPage = this.normalizePerPage(params.get('per_page'));
                this.state.filter = String(params.get('status') || 'all').toLowerCase();
                this.state.search = String(params.get('search') || '').trim();
                this.state.sort = String(params.get('sort') || 'date-desc').toLowerCase();
                this.state.minAmount = String(params.get('min_amount') || '').trim();
                this.state.maxAmount = String(params.get('max_amount') || '').trim();
            },

            syncControlsFromState() {
                if (this.elements.searchInput) this.elements.searchInput.value = this.state.search;
                if (this.elements.statusFilter) this.elements.statusFilter.value = this.state.filter;
                if (this.elements.sortFilter) this.elements.sortFilter.value = this.state.sort;
                if (this.elements.minAmountFilter) this.elements.minAmountFilter.value = this.state.minAmount;
                if (this.elements.maxAmountFilter) this.elements.maxAmountFilter.value = this.state.maxAmount;
                if (this.elements.entriesSelect) this.elements.entriesSelect.value = String(this.state.perPage);
            },

            normalizePerPage(value) {
                const allowedValues = [10, 20, 50, 100];
                const parsed = Number(value);
                return allowedValues.includes(parsed) ? parsed : 10;
            },

            updateBrowserUrl() {
                const params = new URLSearchParams();

                if (this.state.search) params.set('search', this.state.search);
                if (this.state.filter !== 'all') params.set('status', this.state.filter);
                if (this.state.sort !== 'date-desc') params.set('sort', this.state.sort);
                if (this.state.minAmount) params.set('min_amount', this.state.minAmount);
                if (this.state.maxAmount) params.set('max_amount', this.state.maxAmount);
                if (this.state.currentPage > 1) params.set('page', String(this.state.currentPage));
                if (this.state.perPage !== 10) params.set('per_page', String(this.state.perPage));

                const nextUrl = params.toString()
                    ? `${window.location.pathname}?${params.toString()}`
                    : window.location.pathname;

                window.history.replaceState({ url: nextUrl }, '', nextUrl);
            },

            markAsPaid(fineId) {
                const fine = this.getFineRecord(fineId);
                if (!fine) return;
                this.openConfirmActionModal('paid', fineId, fine);
            },

            openBulkMarkPaidModal() {
                const selectedFines = this.getSelectedFines();
                const pendingSelectedFines = this.getPendingSelectedFines();
                const selectedIds = selectedFines.map((fine) => Number(fine.id));
                const pendingCount = pendingSelectedFines.length;

                if (pendingCount === 0) {
                    this.showToast('warning', 'No pending fines selected', 'Select at least one pending fine before using Mark Selected Paid.');
                    return;
                }

                const totalAmount = pendingSelectedFines.reduce((sum, fine) => sum + Number(fine.fineAmount || 0), 0);
                const skippedCount = selectedFines.length - pendingCount;
                const detail = skippedCount > 0
                    ? `${this.formatCurrency(totalAmount)} across ${pendingCount} pending fine${pendingCount === 1 ? '' : 's'} will be marked as paid. ${skippedCount} selected fine${skippedCount === 1 ? '' : 's'} already paid or waived will be skipped.`
                    : `${this.formatCurrency(totalAmount)} across ${pendingCount} pending fine${pendingCount === 1 ? '' : 's'} will be marked as paid.`;

                this.state.pendingConfirmAction = {
                    type: 'paid-bulk',
                    fineIds: selectedIds,
                    fines: selectedFines,
                };

                if (this.feedbackUI) {
                    this.feedbackUI.openConfirm({
                        variant: 'success',
                        buttonVariant: 'success',
                        iconMarkup: svgIcons.confirmPaid,
                        title: 'Mark selected fines as paid?',
                        message: `Record payment for ${pendingCount} pending fine${pendingCount === 1 ? '' : 's'} from the current selection now?`,
                        detail,
                        confirmText: 'Mark Selected Paid',
                    });
                    return;
                }

                this.elements.confirmActionTitle.textContent = 'Mark selected fines as paid?';
                this.elements.confirmActionMessage.textContent = `Record payment for ${pendingCount} pending fine${pendingCount === 1 ? '' : 's'} from the current selection now?`;
                this.elements.confirmActionDetail.textContent = detail;
                this.elements.confirmActionDetail.hidden = false;
                this.elements.confirmActionSubmitBtn.textContent = 'Mark Selected Paid';
                this.elements.confirmActionSubmitBtn.dataset.defaultLabel = 'Mark Selected Paid';
                this.openModal('confirmActionModal', this.elements.confirmActionSubmitBtn);
            },

            openBulkEmailModal() {
                const selectedFines = this.getSelectedFines();
                const selectedIds = selectedFines.map((fine) => Number(fine.id));
                const selectedCount = selectedIds.length;

                if (selectedCount === 0) {
                    this.showToast('warning', 'No fines selected', 'Select at least one fine before sending bulk emails.');
                    return;
                }

                const recipientCount = new Set(
                    selectedFines.map((fine) => String(fine.studentId || fine.studentName || fine.id))
                ).size;

                this.state.pendingConfirmAction = {
                    type: 'email-bulk',
                    fineIds: selectedIds,
                    fines: selectedFines,
                    extra: { recipientCount },
                };

                const detail = `${selectedCount} fine email${selectedCount === 1 ? '' : 's'} will be queued for ${recipientCount} student${recipientCount === 1 ? '' : 's'}. Students with multiple selected fines will receive one email per fine.`;

                if (this.feedbackUI) {
                    this.feedbackUI.openConfirm({
                        variant: 'primary',
                        buttonVariant: 'primary',
                        iconMarkup: svgIcons.confirmEmail,
                        title: 'Send selected fine emails?',
                        message: `Queue email updates for ${selectedCount} selected fine${selectedCount === 1 ? '' : 's'} now?`,
                        detail,
                        confirmText: 'Send Selected Emails',
                    });
                    return;
                }

                this.elements.confirmActionTitle.textContent = 'Send selected fine emails?';
                this.elements.confirmActionMessage.textContent = `Queue email updates for ${selectedCount} selected fine${selectedCount === 1 ? '' : 's'} now?`;
                this.elements.confirmActionDetail.textContent = detail;
                this.elements.confirmActionDetail.hidden = false;
                this.elements.confirmActionSubmitBtn.textContent = 'Send Selected Emails';
                this.elements.confirmActionSubmitBtn.dataset.defaultLabel = 'Send Selected Emails';
                this.openModal('confirmActionModal', this.elements.confirmActionSubmitBtn);
            },

            openWaiveModal(fineId) {
                const fine = this.getFineRecord(fineId);
                if (!fine) return;

                this.state.currentWaiveContext = {
                    scope: 'single',
                    fineIds: [Number(fineId)],
                    fines: [fine],
                };
                this.prepareWaiveModal({
                    title: 'Waive Fine',
                    description: 'Add a short reason. This note will be stored with the fine record and shown in the activity history.',
                    submitLabel: 'Waive Fine',
                });
                this.openModal('waiveModal', this.elements.waiveReason);
            },

            openBulkWaiveModal() {
                const selectedFines = this.getSelectedFines();
                const pendingSelectedFines = this.getPendingSelectedFines();
                const selectedIds = selectedFines.map((fine) => Number(fine.id));
                const pendingCount = pendingSelectedFines.length;

                if (pendingCount === 0) {
                    this.showToast('warning', 'No pending fines selected', 'Select at least one pending fine before using Waive Selected.');
                    return;
                }

                const totalAmount = pendingSelectedFines.reduce((sum, fine) => sum + Number(fine.fineAmount || 0), 0);
                const skippedCount = selectedFines.length - pendingCount;

                this.state.currentWaiveContext = {
                    scope: 'bulk',
                    fineIds: selectedIds,
                    fines: selectedFines,
                };
                this.prepareWaiveModal({
                    title: 'Waive Selected Fines',
                    description: skippedCount > 0
                        ? `Add one reason for ${pendingCount} pending fine${pendingCount === 1 ? '' : 's'} totaling ${this.formatCurrency(totalAmount)}. ${skippedCount} selected fine${skippedCount === 1 ? '' : 's'} already paid or waived will be skipped.`
                        : `Add one reason for all ${pendingCount} selected pending fine${pendingCount === 1 ? '' : 's'} totaling ${this.formatCurrency(totalAmount)}.`,
                    submitLabel: 'Waive Selected',
                });
                this.openModal('waiveModal', this.elements.waiveReason);
            },

            prepareWaiveModal({ title, description, submitLabel }) {
                if (this.elements.waiveModalTitle) this.elements.waiveModalTitle.textContent = title;
                if (this.elements.waiveModalDescription) this.elements.waiveModalDescription.textContent = description;
                if (this.elements.waiveReason) this.elements.waiveReason.value = '';
                if (this.elements.waiveReasonError) {
                    this.elements.waiveReasonError.hidden = true;
                    this.elements.waiveReasonError.textContent = 'Please enter a reason for waiving the fine.';
                }
                this.setFieldValidity(this.elements.waiveReason, true);
                if (this.elements.waiveSubmitBtn) {
                    this.elements.waiveSubmitBtn.dataset.defaultLabel = submitLabel;
                    this.elements.waiveSubmitBtn.innerHTML = `<span>${this.escapeHtml(submitLabel)}</span>`;
                }
            },

            async confirmWaiveFine() {
                if (this.state.actionInFlight) return;

                const waiveContext = this.state.currentWaiveContext;
                if (!waiveContext) return;

                const fineId = waiveContext.fineIds?.[0];
                const fine = waiveContext.fines?.[0] || this.getFineRecord(fineId);
                const reason = (this.elements.waiveReason?.value || '').trim();

                if (!reason) {
                    if (this.elements.waiveReasonError) {
                        this.elements.waiveReasonError.hidden = false;
                        this.elements.waiveReasonError.textContent = 'Please enter a reason for waiving the fine.';
                    }
                    this.setFieldValidity(this.elements.waiveReason, false);
                    this.elements.waiveReason?.focus();
                    return;
                }

                this.setFieldValidity(this.elements.waiveReason, true);
                if (this.elements.waiveReasonError) this.elements.waiveReasonError.hidden = true;

                this.state.actionInFlight = true;
                this.setButtonBusy(this.elements.waiveSubmitBtn, true, 'Saving...');

                try {
                    if (waiveContext.scope === 'bulk') {
                        const response = await this.requestJson(this.config.routes.bulkUpdate, {
                            method: 'POST',
                            body: JSON.stringify({
                                status: 'waived',
                                fine_ids: waiveContext.fineIds,
                                remarks: reason,
                            }),
                        });

                        this.closeModal('waiveModal');
                        this.clearSelection();
                        this.notifyFineUpdate({ fineIds: waiveContext.fineIds, status: 'waived', bulk: true });
                        await this.loadFines({ silent: true });
                        this.showToast(
                            Number(response.skippedCount || 0) > 0 ? 'warning' : 'info',
                            Number(response.processedCount || 0) > 0 ? 'Fines waived' : 'No fines waived',
                            response.message || 'Selected fines were waived successfully.'
                        );
                        this.announce('Selected fines waived successfully.');
                    } else {
                        await this.requestJson(this.buildFineRoute(this.config.routes.waive, fineId), {
                            method: 'POST',
                            body: JSON.stringify({ remarks: reason }),
                        });

                        this.closeModal('waiveModal');
                        this.notifyFineUpdate({ fineId, status: 'waived' });
                        this.applyFineStatusChange(fineId, 'waived', { reason });
                        this.showToast('warning', 'Fine waived', `${fine?.studentName || 'The student'}'s fine was waived successfully.`);
                        this.announce('Fine waived successfully.');
                    }
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to waive the selected fine.');
                    if (this.elements.waiveReasonError) {
                        this.elements.waiveReasonError.hidden = false;
                        this.elements.waiveReasonError.textContent = message;
                    }
                    this.showToast('error', 'Waiver failed', message);
                } finally {
                    this.state.actionInFlight = false;
                    this.setButtonBusy(
                        this.elements.waiveSubmitBtn,
                        false,
                        this.elements.waiveSubmitBtn?.dataset.defaultLabel || 'Waive Fine'
                    );
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
                        iconMarkup: svgIcons.confirmPaid,
                        title: 'Mark Fine as Paid?',
                        message: `Record ${studentName}'s payment now?`,
                        detail: `${fineAmount} for "${bookTitle}" will be marked as paid.`,
                        buttonLabel: 'Mark as Paid',
                        variant: 'primary',
                        buttonVariant: 'primary',
                    },
                    email: {
                        iconMarkup: svgIcons.confirmEmail,
                        title: 'Send Fine Email?',
                        message: `Send an email update to ${studentName}?`,
                        detail: `${fineAmount} for "${bookTitle}" will be emailed based on the current ${(extra.status || fine.status || 'pending')} status.`,
                        buttonLabel: 'Send Email',
                        variant: 'primary',
                        buttonVariant: 'primary',
                    },
                };

                const config = configs[type];
                if (!config) return;

                this.state.pendingConfirmAction = { type, fineId, fine, extra };
                if (this.feedbackUI) {
                    this.feedbackUI.openConfirm({
                        variant: config.variant,
                        buttonVariant: config.buttonVariant,
                        iconMarkup: config.iconMarkup,
                        title: config.title,
                        message: config.message,
                        detail: config.detail,
                        confirmText: config.buttonLabel,
                    });
                    return;
                }

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
                if (this.feedbackUI) {
                    this.feedbackUI.setConfirmBusy(true, 'Processing...');
                } else {
                    this.setButtonBusy(this.elements.confirmActionSubmitBtn, true, 'Processing...');
                }

                try {
                    if (pending.type === 'paid') {
                        await this.processMarkAsPaid(pending.fineId, pending.fine);
                    } else if (pending.type === 'paid-bulk') {
                        await this.processBulkMarkAsPaid(pending.fineIds, pending.fines);
                    } else if (pending.type === 'email-bulk') {
                        await this.processBulkSendEmailNotifications(pending.fineIds, pending.fines, pending.extra);
                    } else if (pending.type === 'email') {
                        await this.processSendEmailNotification(pending.fineId, pending.fine, pending.extra);
                    }
                } finally {
                    this.state.actionInFlight = false;
                    if (this.feedbackUI) {
                        this.feedbackUI.setConfirmBusy(false, this.elements.confirmActionSubmitBtn?.dataset.defaultLabel || 'Continue');
                    } else {
                        this.setButtonBusy(
                            this.elements.confirmActionSubmitBtn,
                            false,
                            this.elements.confirmActionSubmitBtn?.dataset.defaultLabel || 'Continue'
                        );
                    }
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

            async processBulkMarkAsPaid(fineIds, fines = []) {
                try {
                    const response = await this.requestJson(this.config.routes.bulkUpdate, {
                        method: 'POST',
                        body: JSON.stringify({
                            status: 'paid',
                            fine_ids: fineIds,
                        }),
                    });

                    this.closeModal('confirmActionModal');
                    this.clearSelection();
                    this.notifyFineUpdate({ fineIds, status: 'paid', bulk: true });
                    await this.loadFines({ silent: true });
                    this.showToast(
                        Number(response.skippedCount || 0) > 0 ? 'warning' : 'success',
                        Number(response.processedCount || 0) > 0 ? 'Fines marked as paid' : 'No fines updated',
                        response.message || 'Selected fines were marked as paid successfully.'
                    );
                    this.announce('Selected fines marked as paid.');
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to mark the selected fines as paid.');
                    this.showToast('error', 'Bulk payment update failed', message);
                    throw error;
                }
            },

            async processBulkSendEmailNotifications(fineIds, fines = [], extra = {}) {
                try {
                    const response = await this.requestJson(this.config.routes.bulkEmail, {
                        method: 'POST',
                        body: JSON.stringify({
                            fine_ids: fineIds,
                        }),
                    });

                    this.closeModal('confirmActionModal');
                    const processedCount = Number(response.processedCount || 0);
                    const skippedCount = Number(response.skippedCount || 0);
                    const recipientCount = Number(response.recipientCount || extra.recipientCount || 0);

                    this.showToast(
                        skippedCount > 0 ? 'warning' : 'info',
                        processedCount > 0 ? 'Emails queued' : 'No emails queued',
                        response.message || `${processedCount} fine email${processedCount === 1 ? '' : 's'} were queued for ${recipientCount} recipient${recipientCount === 1 ? '' : 's'}.`
                    );
                    this.announce(processedCount > 0 ? 'Selected fine emails queued successfully.' : 'No selected fine emails were queued.');
                } catch (error) {
                    const message = this.resolveErrorMessage(error, 'Unable to send emails for the selected fines.');
                    this.showToast('error', 'Bulk email failed', message);
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
                    this.showToast('info', 'Email queued', data.message || `${fine?.studentName || 'The student'} will receive the latest ${status} fine update shortly.`);
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

                this.state.selectedFineIds.delete(Number(fineId));

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
                if (modalId === 'waiveModal') this.state.currentWaiveContext = null;
                if (modalId === 'confirmActionModal') {
                    this.state.pendingConfirmAction = null;
                    this.feedbackUI?.resetConfirm();
                }
                if (modalId === this.exportWorkflow?.getModalId?.()) {
                    this.exportWorkflow.handleModalClosed();
                }
                if (modalId === (this.exportWorkflow?.getModalId?.() || 'exportOptionsModal') && this.state.exportFetchController) {
                    this.state.exportFetchController.abort();
                    this.state.exportFetchController = null;
                    this.state.exportAllRowsLoading = false;
                }
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

            renderExportPreview() {
                if (this.exportWorkflow) {
                    this.exportWorkflow.render();
                    return;
                }

                const context = this.getExportContext();

                if (this.elements.exportModalBadge) {
                    this.elements.exportModalBadge.textContent = context.badgeLabel;
                }

                if (this.elements.exportModalHeadline) {
                    this.elements.exportModalHeadline.textContent = context.headline;
                }

                if (this.elements.exportModalSubtext) {
                    this.elements.exportModalSubtext.textContent = context.subtext;
                }

                if (this.elements.exportSummaryGrid) {
                    this.elements.exportSummaryGrid.innerHTML = context.summaryItems.map((item) => `
                        <div class="export-summary-card">
                            <span class="export-summary-label">${this.escapeHtml(item.label)}</span>
                            <span class="export-summary-value">${this.escapeHtml(item.value)}</span>
                        </div>
                    `).join('');
                }

                if (this.elements.exportPreviewCaption) {
                    this.elements.exportPreviewCaption.textContent = context.previewCaption;
                }

                if (this.elements.exportPreviewCount) {
                    this.elements.exportPreviewCount.textContent = context.previewCountText;
                }

                if (this.elements.exportDocumentTimestamp) {
                    this.elements.exportDocumentTimestamp.textContent = context.generatedAtLabel;
                }

                if (this.elements.exportFooterNote) {
                    this.elements.exportFooterNote.textContent = context.footerNote;
                }

                if (this.elements.exportPreviewTableBody) {
                    this.elements.exportPreviewTableBody.innerHTML = context.previewRows.length > 0
                        ? context.previewRows.map((row) => `
                            <tr>
                                <td>${this.escapeHtml(row.studentId)}</td>
                                <td>${this.escapeHtml(row.studentName)}</td>
                                <td>${this.escapeHtml(row.bookTitle)}</td>
                                <td>${this.escapeHtml(row.dueDate)}</td>
                                <td>${this.escapeHtml(String(row.daysOverdue))}</td>
                                <td>${this.escapeHtml(row.fineAmount)}</td>
                                <td>${this.escapeHtml(row.status)}</td>
                            </tr>
                        `).join('')
                        : `<tr><td colspan="7" class="export-preview-empty">${this.escapeHtml(context.emptyMessage)}</td></tr>`;
                }

                if (this.elements.exportPrintBtnLabel) {
                    this.elements.exportPrintBtnLabel.textContent = context.isLoading
                        ? 'Preparing report...'
                        : (context.isAllScope ? 'Print Full Report' : 'Print List');
                }

                if (this.elements.exportPrintBtn) {
                    this.elements.exportPrintBtn.disabled = context.isLoading || context.rows.length === 0;
                }

                if (this.elements.exportDownloadBtnLabel) {
                    this.elements.exportDownloadBtnLabel.textContent = context.isLoading
                        ? 'Preparing report...'
                        : (context.isAllScope ? 'Download Full Excel' : 'Download Excel');
                }

                if (this.elements.exportDownloadBtn) {
                    this.elements.exportDownloadBtn.disabled = context.isLoading || context.rows.length === 0;
                }
            },

            describeExportContext(context) {
                let badgeLabel = 'Current page';
                let headline = `${context.rowsReady} fine ${context.rowsReady === 1 ? 'record' : 'records'} on this page`;
                let subtext = context.total > context.rowsReady
                    ? `Rows ${context.start}-${context.end} of ${context.total} matching records.`
                    : 'Visible fine records will be used.';
                let previewCaption = context.total > context.rowsReady
                    ? `Rows ${context.start}-${context.end} of ${context.total}.`
                    : 'Visible rows used for export.';
                let previewCountText = `${context.rowsReady} ${context.rowsReady === 1 ? 'row' : 'rows'}`;
                let footerNote = 'Using current page for print and spreadsheet download.';
                let emptyMessage = 'No fine records are available for preview.';

                if (context.isAllScope && context.isLoading) {
                    badgeLabel = 'Preparing full report';
                    headline = `Loading ${context.total} matching records...`;
                    subtext = 'Preparing the full filtered report.';
                    previewCaption = 'Preview updates when ready.';
                    previewCountText = 'Preparing...';
                    footerNote = 'Preparing full report.';
                    emptyMessage = 'Preparing full report preview...';
                } else if (context.isAllScope) {
                    badgeLabel = 'Filtered report';
                    headline = `${context.rowsReady} fine ${context.rowsReady === 1 ? 'record' : 'records'} in report`;
                    subtext = `All matching records across ${context.lastPage} ${context.lastPage === 1 ? 'page' : 'pages'}.`;
                    previewCaption = context.rowsReady > context.previewRows.length
                        ? `Showing first ${context.previewRows.length} of ${context.rowsReady}.`
                        : `All ${context.rowsReady} matching records included.`;
                    previewCountText = context.rowsReady > context.previewRows.length
                        ? `Showing ${context.previewRows.length} of ${context.rowsReady}`
                        : `${context.rowsReady} ${context.rowsReady === 1 ? 'row' : 'rows'}`;
                    footerNote = 'Using full filtered report for print and spreadsheet download.';
                }

                return {
                    badgeLabel,
                    headline,
                    subtext,
                    previewCaption,
                    previewCountText,
                    footerNote,
                    emptyMessage,
                    summaryItems: this.buildExportSummaryItems(context),
                };
            },

            getExportContext() {
                const isAllScope = this.state.exportScope === 'all';
                const pageRows = this.getCurrentPageExportRows();
                const total = Number(this.state.pagination.total || this.state.stats.count || pageRows.length);
                const currentPage = Math.max(1, Number(this.state.pagination.current_page || this.state.currentPage || 1));
                const lastPage = Math.max(1, Number(this.state.pagination.last_page || 1));
                const start = total === 0 ? 0 : ((currentPage - 1) * this.state.perPage) + 1;
                const end = total === 0 ? 0 : Math.min(currentPage * this.state.perPage, total);
                const rows = this.getExportRows();
                const rowsReady = rows.length;
                const isLoading = isAllScope && this.state.exportAllRowsLoading;
                const generatedAt = isAllScope
                    ? (this.state.exportAllRowsGeneratedAt || this.state.exportPreparedAt || this.state.lastUpdatedAt || new Date())
                    : (this.state.exportPreparedAt || this.state.lastUpdatedAt || new Date());
                const previewLimit = isAllScope ? 12 : Math.max(1, Number(this.state.perPage || rowsReady || 10));
                const previewRows = isAllScope ? rows.slice(0, previewLimit) : rows;
                const scopeLabel = this.getExportScopeLabel(this.state.exportScope);

                let badgeLabel = 'Current result set';
                let headline = `${rowsReady} fine ${rowsReady === 1 ? 'record' : 'records'} ready on this page`;
                let subtext = total > rowsReady
                    ? `You are previewing rows ${start}-${end} of ${total} matching fine records. Print and spreadsheet download use the current page only.`
                    : 'Print or download the currently visible fine records from this dialog.';
                let previewCaption = total > rowsReady
                    ? `Current page preview. Rows ${start}-${end} of ${total} matching fine records will be used.`
                    : 'Current filtered fine records that will be printed or downloaded.';
                let previewCountText = `${rowsReady} ${rowsReady === 1 ? 'row' : 'rows'}`;
                let footerNote = 'Printing and spreadsheet download use the current filtered page shown above.';
                let emptyMessage = 'No fine records are available for preview.';

                if (isAllScope && isLoading) {
                    badgeLabel = 'Preparing full report';
                    headline = 'Loading every matching fine record...';
                    subtext = `Fetching all ${total} matching fine ${total === 1 ? 'record' : 'records'} across ${lastPage} ${lastPage === 1 ? 'page' : 'pages'} for printing and spreadsheet download.`;
                    previewCaption = 'The full filtered report preview will appear here as soon as it is ready.';
                    previewCountText = 'Preparing...';
                    footerNote = 'Please wait while the entire filtered report is being prepared.';
                    emptyMessage = 'Preparing full report preview...';
                } else if (isAllScope) {
                    badgeLabel = 'Entire filtered report';
                    headline = `${rowsReady} fine ${rowsReady === 1 ? 'record' : 'records'} ready in the full report`;
                    subtext = `All ${rowsReady} matching fine ${rowsReady === 1 ? 'record' : 'records'} across ${lastPage} ${lastPage === 1 ? 'page' : 'pages'} will be used for print and spreadsheet download.`;
                    previewCaption = rowsReady > previewRows.length
                        ? `Previewing the first ${previewRows.length} rows. Print and spreadsheet download will include all ${rowsReady} matching fine records.`
                        : `All ${rowsReady} matching fine ${rowsReady === 1 ? 'record' : 'records'} will be used for print and spreadsheet download.`;
                    previewCountText = rowsReady > previewRows.length
                        ? `Showing ${previewRows.length} of ${rowsReady}`
                        : `${rowsReady} ${rowsReady === 1 ? 'row' : 'rows'}`;
                    footerNote = `Printing and spreadsheet download will use the entire filtered report with ${rowsReady} ${rowsReady === 1 ? 'record' : 'records'}.`;
                }

                return {
                    isAllScope,
                    isLoading,
                    rows,
                    previewRows,
                    rowsReady,
                    total,
                    currentPage,
                    lastPage,
                    start,
                    end,
                    badgeLabel,
                    headline,
                    subtext,
                    previewCaption,
                    previewCountText,
                    footerNote,
                    emptyMessage,
                    scopeLabel,
                    generatedAt,
                    generatedAtLabel: `Generated on ${this.formatDateTime(generatedAt)}`,
                    summaryItems: this.buildExportSummaryItems({
                        isAllScope,
                        isLoading,
                        rowsReady,
                        total,
                        currentPage,
                        lastPage,
                        start,
                        end,
                        scopeLabel,
                    }),
                };
            },

            buildExportSummaryItems(context) {
                const amountRange = this.getAmountRangeLabel();
                const summaryItems = [
                    {
                        label: 'Scope',
                        value: context.scopeLabel,
                    },
                    {
                        label: 'Rows',
                        value: context.isLoading
                            ? 'Preparing...'
                            : `${context.rowsReady} ${context.rowsReady === 1 ? 'record' : 'records'}`,
                    },
                    {
                        label: 'Total',
                        value: `${context.total} ${context.total === 1 ? 'record' : 'records'}`,
                    },
                    {
                        label: context.isAllScope ? 'Pages' : 'Page',
                        value: context.isAllScope
                            ? `${context.lastPage} ${context.lastPage === 1 ? 'page' : 'pages'}`
                            : `${context.currentPage}/${context.lastPage}`,
                    },
                ];

                const filterParts = [];

                if (this.state.filter !== 'all') {
                    filterParts.push(this.getStatusFilterLabel());
                }

                if (this.state.search) {
                    filterParts.push(`Search: ${this.state.search}`);
                }

                if (amountRange !== 'Any amount') {
                    filterParts.push(amountRange);
                }

                if (this.state.sort !== 'date-desc') {
                    filterParts.push(this.getSortLabel());
                }

                if (filterParts.length > 0) {
                    summaryItems.push({
                        label: 'Filters',
                        value: filterParts.join(' • '),
                    });
                }

                return summaryItems;
            },

            getExportHeaders() {
                return ['User ID', 'User Name', 'Book Title', 'Due Date', 'Days Overdue', 'Fine Amount', 'Status'];
            },

            buildFineReportExportMetaRows(context) {
                if (typeof window.ReportExportTemplates?.buildStandardMetaRows === 'function') {
                    return window.ReportExportTemplates.buildStandardMetaRows({
                        systemTitle,
                        reportTitle: 'Fine Report',
                        generatedAtLabel: context.generatedAtLabel,
                    });
                }

                return [
                    [systemTitle],
                    ['Fine Report'],
                    [context.generatedAtLabel],
                    [''],
                ];
            },

            syncExportScopeControls() {
                this.elements.exportScopeInputs.forEach((input) => {
                    input.checked = input.value === this.state.exportScope;
                });
            },

            clearAllExportRowsCache() {
                if (this.exportWorkflow) {
                    this.exportWorkflow.clearCache();
                    return;
                }

                if (this.state.exportFetchController) {
                    this.state.exportFetchController.abort();
                }

                this.state.exportFetchController = null;
                this.state.exportAllRowsLoading = false;
                this.state.exportAllRows = [];
                this.state.exportAllRowsFilterKey = '';
                this.state.exportAllRowsGeneratedAt = null;
                this.state.exportScope = 'page';
                this.syncExportScopeControls();
            },

            buildExportFilterParams() {
                const params = new URLSearchParams({
                    search: this.state.search,
                    status: this.state.filter,
                    sort: this.state.sort,
                });

                if (this.state.minAmount) params.set('min_amount', this.state.minAmount);
                if (this.state.maxAmount) params.set('max_amount', this.state.maxAmount);

                return params;
            },

            getExportFilterKey() {
                return this.buildExportFilterParams().toString();
            },

            async setExportScope(scope) {
                if (this.exportWorkflow) {
                    await this.exportWorkflow.setScope(scope);
                    return;
                }

                const normalizedScope = scope === 'all' ? 'all' : 'page';
                const shouldReloadAll = normalizedScope === 'all'
                    && (!this.state.exportAllRows.length || this.state.exportAllRowsFilterKey !== this.getExportFilterKey());

                if (normalizedScope === 'page' && this.state.exportFetchController) {
                    this.state.exportFetchController.abort();
                    this.state.exportFetchController = null;
                    this.state.exportAllRowsLoading = false;
                }

                this.state.exportScope = normalizedScope;
                this.state.exportPreparedAt = new Date();
                this.syncExportScopeControls();

                if (normalizedScope === 'all' && shouldReloadAll) {
                    await this.loadAllExportRows();
                    return;
                }

                this.renderExportPreview();
            },

            async loadAllExportRows() {
                const route = this.config.routes?.exportData;
                if (!route) {
                    this.state.exportScope = 'page';
                    this.syncExportScopeControls();
                    this.renderExportPreview();
                    this.showToast('error', 'Export route missing', 'The full fine report endpoint is not available right now.');
                    return;
                }

                const filterKey = this.getExportFilterKey();
                if (this.state.exportAllRowsFilterKey === filterKey && this.state.exportAllRows.length > 0) {
                    this.state.exportPreparedAt = this.state.exportAllRowsGeneratedAt || new Date();
                    this.renderExportPreview();
                    return;
                }

                if (this.state.exportFetchController) {
                    this.state.exportFetchController.abort();
                }

                const controller = new AbortController();
                this.state.exportFetchController = controller;
                this.state.exportAllRowsLoading = true;
                this.renderExportPreview();

                try {
                    const params = this.buildExportFilterParams();
                    const data = await this.requestJson(`${route}?${params.toString()}`, {
                        method: 'GET',
                        signal: controller.signal,
                    });

                    if (this.state.exportFetchController !== controller) {
                        return;
                    }

                    this.state.exportAllRows = Array.isArray(data.fines)
                        ? data.fines.map((fine) => this.mapFineToExportRow(fine))
                        : [];
                    this.state.exportAllRowsFilterKey = filterKey;
                    this.state.exportAllRowsGeneratedAt = this.toDateObject(data.meta?.generated_at) || new Date();
                    this.state.exportPreparedAt = this.state.exportAllRowsGeneratedAt;
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error('[Fines] Failed to load full export rows:', error);
                    this.state.exportScope = 'page';
                    this.syncExportScopeControls();
                    this.showToast('error', 'Could not prepare full report', this.resolveErrorMessage(error, 'Something went wrong while preparing the full fine report.'));
                } finally {
                    if (this.state.exportFetchController === controller) {
                        this.state.exportFetchController = null;
                        this.state.exportAllRowsLoading = false;
                    }

                    this.renderExportPreview();
                }
            },

            mapFineToExportRow(fine) {
                return {
                    studentId: fine.studentId || fine.student_id || 'N/A',
                    studentName: fine.studentName || fine.student_name || 'Unknown',
                    bookTitle: fine.bookTitle || fine.book_title || 'Unknown',
                    dueDate: fine.dueDate || fine.due_date || 'N/A',
                    daysOverdue: Number(fine.daysOverdue ?? fine.days_overdue ?? 0),
                    fineAmount: this.formatCurrency(fine.fineAmount ?? fine.amount ?? 0),
                    status: fine.statusLabel || fine.status_label || this.capitalize(fine.status || 'pending'),
                };
            },

            getCurrentPageExportRows() {
                return this.state.fines.map((fine) => this.mapFineToExportRow(fine));
            },

            getExportRows() {
                return this.state.exportScope === 'all'
                    ? this.state.exportAllRows
                    : this.getCurrentPageExportRows();
            },

            getExportScopeLabel(scope) {
                return scope === 'all' ? 'Entire filtered report' : 'Current page';
            },

            getExportFilename(context, format = 'excel') {
                const generatedAt = this.toDateObject(context?.generatedAt) || new Date();
                const datePart = generatedAt.toISOString().slice(0, 10);
                const extension = format === 'csv' ? 'csv' : 'xls';
                return context?.isAllScope
                    ? `fine-report-full-${datePart}.${extension}`
                    : `fine-report-page-${context?.currentPage || 1}-${datePart}.${extension}`;
            },

            getStatusFilterLabel() {
                return this.state.filter === 'all' ? 'All statuses' : this.capitalize(this.state.filter);
            },

            getSortLabel() {
                const sortLabels = {
                    'date-desc': 'Date (Newest)',
                    'date-asc': 'Date (Oldest)',
                    'amount-desc': 'Amount (High to Low)',
                    'amount-asc': 'Amount (Low to High)',
                };

                return sortLabels[this.state.sort] || 'Date (Newest)';
            },

            getAmountRangeLabel() {
                if (this.state.minAmount && this.state.maxAmount) {
                    return `${this.formatCurrency(this.state.minAmount)} to ${this.formatCurrency(this.state.maxAmount)}`;
                }

                if (this.state.minAmount) {
                    return `${this.formatCurrency(this.state.minAmount)} and above`;
                }

                if (this.state.maxAmount) {
                    return `Up to ${this.formatCurrency(this.state.maxAmount)}`;
                }

                return 'Any amount';
            },

            downloadExportFile() {
                if (this.exportWorkflow) {
                    this.exportWorkflow.download();
                    return;
                }

                this.exportToCSV();
            },

            printCurrentList() {
                if (this.exportWorkflow) {
                    this.exportWorkflow.print();
                    return;
                }

                const context = this.getExportContext();
                if (context.isLoading) {
                    this.showToast('info', 'Preparing full report', 'Please wait until the full fine report finishes loading.');
                    return;
                }

                const rows = context.rows;
                if (rows.length === 0) {
                    this.showToast('warning', 'Nothing to print', 'There are no fine records in the current result set.');
                    return;
                }

                const printWindow = window.open('', '_blank', 'width=1100,height=760');
                if (!printWindow) {
                    this.showToast('warning', 'Popup blocked', 'Allow popups for this site to open the print view.');
                    return;
                }

                try {
                    printWindow.document.open();
                    printWindow.document.write(this.buildPrintDocument(rows, context));
                    printWindow.document.close();
                    this.closeModal('exportOptionsModal');
                    this.showToast(
                        'info',
                        'Print view ready',
                        context.isAllScope
                            ? 'The print dialog will open in a new window for the full filtered fine report.'
                            : 'The print dialog will open in a new window for the current fine list.'
                    );
                } catch (error) {
                    console.error('[Fines] Failed to build print document:', error);
                    printWindow.close();
                    this.showToast('error', 'Print view failed', 'The print preview could not be prepared. Please try again.');
                }
            },

            buildPrintDocument(rows, context = null) {
                const generatedAt = this.formatDateTime(context?.generatedAt || new Date());
                const logoMarkup = defaultLibraryBranding?.image_url
                    ? `<img src="${this.escapeAttribute(defaultLibraryBranding.image_url)}" alt="${this.escapeAttribute(defaultLibraryBranding.alt || 'Library Logo')}" loading="eager">`
                    : `<span class="print-logo-fallback">${this.escapeHtml(defaultLibraryBranding?.fallback_text || 'LMS')}</span>`;
                const tableRows = rows.map((row) => `
                    <tr>
                        <td>${this.escapeHtml(row.studentId)}</td>
                        <td>${this.escapeHtml(row.studentName)}</td>
                        <td>${this.escapeHtml(row.bookTitle)}</td>
                        <td>${this.escapeHtml(row.dueDate)}</td>
                        <td>${this.escapeHtml(String(row.daysOverdue))}</td>
                        <td>${this.escapeHtml(row.fineAmount)}</td>
                        <td>${this.escapeHtml(row.status)}</td>
                    </tr>
                `).join('');

                return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fine Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Inter, Arial, sans-serif;
            color: #020617;
            background: #ffffff;
        }

        .table-shell {
            width: 100%;
            padding: 4px 6px 0;
        }

        .print-header {
            padding: 6px 0 16px;
            margin-bottom: 8px;
            border-bottom: 1px solid #cbd5e1;
        }

        .print-branding-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
        }

        .print-logo {
            width: 54px;
            height: 54px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 999px;
            background: linear-gradient(135deg, #1d4ed8 0%, #0f766e 100%);
            border: 1px solid rgba(148, 163, 184, 0.22);
        }

        .print-logo img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
            background: #ffffff;
        }

        .print-logo-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            padding: 0 6px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #ffffff;
        }

        .print-branding-copy {
            display: grid;
            gap: 2px;
            text-align: left;
        }

        .print-system-title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #0f172a;
        }

        .print-report-title {
            margin: 5px 0 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #334155;
        }

        .print-report-meta {
            margin: 7px 0 0;
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #cbd5e1;
        }

        .print-table thead {
            display: table-header-group;
        }

        .print-table tbody {
            display: table-row-group;
        }

        .print-table tr {
            page-break-inside: avoid;
        }

        .print-table th,
        .print-table td {
            padding: 10px 12px;
            text-align: left;
            vertical-align: top;
            font-size: 12px;
            line-height: 1.4;
            word-break: break-word;
            border: 1px solid #cbd5e1;
        }

        .print-table th {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #0f172a;
            background: #e2e8f0;
        }

        .print-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .print-table tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .print-table th:nth-child(1),
        .print-table td:nth-child(1) {
            width: 14%;
        }

        .print-table th:nth-child(2),
        .print-table td:nth-child(2) {
            width: 17%;
        }

        .print-table th:nth-child(3),
        .print-table td:nth-child(3) {
            width: 28%;
            font-weight: 600;
        }

        .print-table th:nth-child(4),
        .print-table td:nth-child(4) {
            width: 14%;
        }

        .print-table th:nth-child(5),
        .print-table td:nth-child(5) {
            width: 10%;
            text-align: center;
        }

        .print-table th:nth-child(6),
        .print-table td:nth-child(6) {
            width: 11%;
            white-space: nowrap;
            text-align: right;
        }

        .print-table th:nth-child(7),
        .print-table td:nth-child(7) {
            width: 10%;
            white-space: nowrap;
            text-align: center;
        }

        .print-table th:nth-child(6) {
            text-align: right;
        }

        @media print {
            .table-shell {
                padding: 0;
            }
        }
            </style>
</head>
<body>
    <div class="table-shell">
        <div class="print-header">
            <div class="print-branding-row">
                <div class="print-logo">${logoMarkup}</div>
                <div class="print-branding-copy">
                    <p class="print-system-title">${this.escapeHtml(systemTitle)}</p>
                    <p class="print-report-title">Fine Report</p>
                    <p class="print-report-meta">Generated on ${this.escapeHtml(generatedAt)}</p>
                </div>
            </div>
        </div>
        <table class="print-table">
            <thead>
                <tr>
                    ${this.getExportHeaders().map((header) => `<th scope="col">${this.escapeHtml(header)}</th>`).join('')}
                </tr>
            </thead>
            <tbody>${tableRows}</tbody>
        </table>
    </div>
    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () {
                window.focus();
                window.print();
            }, 120);
        });

        window.addEventListener('afterprint', function () {
            window.close();
        });
    <\/script>
</body>
</html>`;
            },

            exportToCSV() {
                if (this.exportWorkflow) {
                    this.exportWorkflow.download();
                    return;
                }

                const context = this.getExportContext();
                if (context.isLoading) {
                    this.showToast('info', 'Preparing full report', 'Please wait until the full fine report finishes loading.');
                    return;
                }

                const rows = context.rows;
                if (rows.length === 0) {
                    this.showToast('warning', 'Nothing to export', 'There are no fine records in the current result set.');
                    return;
                }

                const headers = this.getExportHeaders();
                const escapeCsvCell = (value) => `"${String(value ?? '').replace(/"/g, '""')}"`;
                const metaRows = this.buildFineReportExportMetaRows(context);
                const csvRows = rows.map((row) => [
                    row.studentId,
                    row.studentName,
                    row.bookTitle,
                    row.dueDate,
                    row.daysOverdue,
                    row.fineAmount,
                    row.status,
                ]);
                const csv = '\uFEFF' + [...metaRows, headers, ...csvRows]
                    .map((row) => row.map((cell) => escapeCsvCell(cell)).join(','))
                    .join('\r\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = this.getExportFilename(context, 'csv');
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.setTimeout(() => URL.revokeObjectURL(url), 100);
                this.closeModal('exportOptionsModal');
                this.showToast(
                    'success',
                    'Export ready',
                    context.isAllScope
                        ? 'The full filtered fine report has been exported to CSV.'
                        : 'The current fine list has been exported to CSV.'
                );
            },

            showToast(type, title, message, timeout = 4200) {
                if (this.feedbackUI) {
                    this.feedbackUI.showToast(type, title, message, timeout);
                    return;
                }

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
                if (this.feedbackUI) {
                    this.feedbackUI.dismissToast(toast);
                    return;
                }

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

            formatDateTime(value) {
                const date = this.toDateObject(value);
                if (!date) return 'Unknown time';
                return new Intl.DateTimeFormat(undefined, {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                }).format(date);
            },

            toDateObject(value) {
                const date = value instanceof Date ? value : new Date(value);
                return Number.isNaN(date.getTime()) ? null : date;
            },

            formatDateShort(value) {
                const date = this.toDateObject(value);
                if (!date) return value;
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
