<script>
    (() => {
        if (window.ReportExportWorkflow) {
            return;
        }

        const defaultLibraryBranding = window.LibraryBranding?.normalize
            ? window.LibraryBranding.normalize(window.__LIBRARY_BRANDING__ ?? @json($libraryBranding))
            : (window.__LIBRARY_BRANDING__ ?? @json($libraryBranding));

        class ReportExportWorkflow {
            constructor(config = {}) {
                this.config = config;
                this.state = {
                    scope: 'page',
                    preparedAt: null,
                    allRows: [],
                    allRowsFilterKey: '',
                    allRowsGeneratedAt: null,
                    allRowsLoading: false,
                    fetchController: null,
                };
                this.elements = {};
                this.initialized = false;
            }

            init() {
                if (this.initialized) {
                    return this;
                }

                this.cacheElements();
                this.bindEvents();
                this.syncScopeControls();
                this.initialized = true;

                return this;
            }

            getIdPrefix() {
                return this.config.idPrefix || 'reportExport';
            }

            getModalId() {
                return this.config.modalId || `${this.getIdPrefix()}Modal`;
            }

            getScopeName() {
                return this.config.scopeName || `${this.getIdPrefix()}Scope`;
            }

            getElementId(name) {
                return `${this.getIdPrefix()}${name}`;
            }

            getLabels() {
                return {
                    printButton: 'Print Report',
                    allScopePrintButton: 'Print Full Report',
                    downloadButton: 'Download CSV',
                    allScopeDownloadButton: 'Download Full CSV',
                    loadingPrintButton: 'Preparing report...',
                    loadingDownloadButton: 'Preparing report...',
                    ...this.config.labels,
                };
            }

            getMessages() {
                return {
                    emptyTitle: 'Nothing to export',
                    emptyMessage: 'There are no records in the current result set.',
                    preparingTitle: 'Preparing full report',
                    preparingMessage: 'Please wait until the full report finishes loading.',
                    popupBlockedTitle: 'Popup blocked',
                    popupBlockedMessage: 'Allow popups for this site to open the print view.',
                    printReadyTitle: 'Print view ready',
                    printReadyMessage: 'The print dialog will open in a new window for the current report.',
                    fullPrintReadyMessage: 'The print dialog will open in a new window for the full filtered report.',
                    printFailedTitle: 'Print view failed',
                    printFailedMessage: 'The print preview could not be prepared. Please try again.',
                    exportReadyTitle: 'Export ready',
                    exportReadyMessage: 'The current report has been exported to CSV.',
                    fullExportReadyMessage: 'The full filtered report has been exported to CSV.',
                    exportRouteMissingTitle: 'Export route missing',
                    exportRouteMissingMessage: 'The full report endpoint is not available right now.',
                    fullLoadFailedTitle: 'Could not prepare full report',
                    fullLoadFailedMessage: 'Something went wrong while preparing the full report.',
                    ...this.config.messages,
                };
            }

            getDocument() {
                return {
                    systemTitle: defaultLibraryBranding?.name || 'Library Management System',
                    reportTitle: 'Report',
                    ...this.config.document,
                };
            }

            getBranding() {
                return this.config.branding || defaultLibraryBranding || {};
            }

            getColumns() {
                return Array.isArray(this.config.columns) ? this.config.columns : [];
            }

            getPreviewLimit() {
                return Math.max(1, Number(this.config.previewLimit || 12));
            }

            cacheElements() {
                this.elements.modal = document.getElementById(this.getModalId());
                this.elements.scopeInputs = Array.from(document.querySelectorAll(`input[name="${this.getScopeName()}"]`));
                this.elements.badge = document.getElementById(this.getElementId('Badge'));
                this.elements.headline = document.getElementById(this.getElementId('Headline'));
                this.elements.subtext = document.getElementById(this.getElementId('Subtext'));
                this.elements.summaryGrid = document.getElementById(this.getElementId('SummaryGrid'));
                this.elements.previewCaption = document.getElementById(this.getElementId('PreviewCaption'));
                this.elements.previewCount = document.getElementById(this.getElementId('PreviewCount'));
                this.elements.documentTimestamp = document.getElementById(this.getElementId('DocumentTimestamp'));
                this.elements.previewTableBody = document.getElementById(this.getElementId('PreviewTableBody'));
                this.elements.footerNote = document.getElementById(this.getElementId('FooterNote'));
                this.elements.downloadBtn = document.getElementById(this.getElementId('DownloadBtn'));
                this.elements.printBtn = document.getElementById(this.getElementId('PrintBtn'));
                this.elements.downloadBtnLabel = this.elements.downloadBtn?.querySelector('span') || null;
                this.elements.printBtnLabel = this.elements.printBtn?.querySelector('span') || null;
            }

            bindEvents() {
                this.elements.printBtn?.addEventListener('click', () => this.print());
                this.elements.downloadBtn?.addEventListener('click', () => this.downloadCsv());
                this.elements.scopeInputs.forEach((input) => {
                    input.addEventListener('change', (event) => {
                        void this.setScope(event.target.value);
                    });
                });
            }

            open() {
                const rows = this.getCurrentRows();
                if (rows.length === 0) {
                    const messages = this.getMessages();
                    this.showToast('error', messages.emptyTitle, messages.emptyMessage);
                    return false;
                }

                this.state.scope = 'page';
                this.state.preparedAt = new Date();
                this.syncScopeControls();
                this.render();

                if (typeof this.config.openModal === 'function') {
                    this.config.openModal(this.getModalId(), this.elements.printBtn || this.elements.downloadBtn);
                } else if (this.elements.modal) {
                    this.elements.modal.classList.add('is-open');
                    this.elements.modal.setAttribute('aria-hidden', 'false');
                    window.setTimeout(() => (this.elements.printBtn || this.elements.downloadBtn)?.focus(), 20);
                }

                return true;
            }

            handleModalClosed() {
                if (this.state.fetchController) {
                    this.state.fetchController.abort();
                    this.state.fetchController = null;
                }

                this.state.allRowsLoading = false;
            }

            clearCache({ resetScope = true } = {}) {
                this.handleModalClosed();
                this.state.allRows = [];
                this.state.allRowsFilterKey = '';
                this.state.allRowsGeneratedAt = null;

                if (resetScope) {
                    this.state.scope = 'page';
                    this.syncScopeControls();
                }
            }

            syncScopeControls() {
                this.elements.scopeInputs.forEach((input) => {
                    input.checked = input.value === this.state.scope;
                });
            }

            getListingState(fallbackCount = 0) {
                const listing = typeof this.config.getListingState === 'function'
                    ? (this.config.getListingState() || {})
                    : {};

                return {
                    total: Math.max(0, Number(listing.total || fallbackCount || 0)),
                    currentPage: Math.max(1, Number(listing.currentPage || 1)),
                    lastPage: Math.max(1, Number(listing.lastPage || 1)),
                    perPage: Math.max(1, Number(listing.perPage || fallbackCount || 10)),
                };
            }

            getScopeLabel(scope) {
                if (typeof this.config.getScopeLabel === 'function') {
                    return this.config.getScopeLabel(scope);
                }

                return scope === 'all' ? 'Entire filtered report' : 'Current page';
            }

            describeContext(baseContext) {
                if (typeof this.config.describeContext === 'function') {
                    return this.config.describeContext(baseContext) || {};
                }

                return {
                    badgeLabel: 'Current result set',
                    headline: `${baseContext.rowsReady} records ready`,
                    subtext: 'Review the report before printing or downloading it.',
                    previewCaption: 'Preview rows that will be used for print or download.',
                    previewCountText: `${baseContext.rowsReady} ${baseContext.rowsReady === 1 ? 'row' : 'rows'}`,
                    footerNote: 'Printing and CSV download use the selected report scope shown above.',
                    emptyMessage: 'No records are available for preview.',
                    summaryItems: this.buildDefaultSummaryItems(baseContext),
                };
            }

            buildDefaultSummaryItems(context) {
                const coverageLabel = context.total === 0
                    ? '0 of 0'
                    : (context.isAllScope ? `${context.rowsReady} of ${context.total}` : `${context.start}-${context.end} of ${context.total}`);

                return [
                    {
                        label: 'Report scope',
                        value: context.scopeLabel,
                    },
                    {
                        label: 'Rows included',
                        value: context.isLoading
                            ? 'Preparing...'
                            : `${context.rowsReady} ${context.rowsReady === 1 ? 'record' : 'records'}`,
                    },
                    {
                        label: 'Matching total',
                        value: `${context.total} ${context.total === 1 ? 'record' : 'records'}`,
                    },
                    {
                        label: context.isAllScope ? 'Pages covered' : 'Page',
                        value: context.isAllScope
                            ? `All ${context.lastPage} ${context.lastPage === 1 ? 'page' : 'pages'}`
                            : `${context.currentPage} of ${context.lastPage}`,
                    },
                    {
                        label: context.isAllScope ? 'Coverage' : 'Range',
                        value: coverageLabel,
                    },
                ];
            }

            getContext() {
                const currentRows = this.getCurrentRows();
                const listing = this.getListingState(currentRows.length);
                const isAllScope = this.state.scope === 'all';
                const rows = isAllScope ? this.state.allRows : currentRows;
                const rowsReady = rows.length;
                const isLoading = isAllScope && this.state.allRowsLoading;
                const start = listing.total === 0 ? 0 : ((listing.currentPage - 1) * listing.perPage) + 1;
                const end = listing.total === 0 ? 0 : Math.min(start + currentRows.length - 1, listing.total);
                const previewRows = isAllScope ? rows.slice(0, this.getPreviewLimit()) : rows;
                const generatedAt = isAllScope
                    ? (this.state.allRowsGeneratedAt || this.state.preparedAt || new Date())
                    : (this.state.preparedAt || new Date());
                const scopeLabel = this.getScopeLabel(this.state.scope);

                const baseContext = {
                    isAllScope,
                    isLoading,
                    rows,
                    previewRows,
                    rowsReady,
                    total: listing.total,
                    currentPage: listing.currentPage,
                    lastPage: listing.lastPage,
                    perPage: listing.perPage,
                    start,
                    end,
                    scopeLabel,
                    generatedAt,
                    generatedAtLabel: `Generated on ${this.formatDateTime(generatedAt)}`,
                };

                const describedContext = this.describeContext(baseContext);

                return {
                    ...baseContext,
                    badgeLabel: describedContext.badgeLabel || 'Current result set',
                    headline: describedContext.headline || `${rowsReady} records ready`,
                    subtext: describedContext.subtext || 'Review the report before printing or downloading it.',
                    previewCaption: describedContext.previewCaption || 'Preview rows that will be used for print or download.',
                    previewCountText: describedContext.previewCountText || `${rowsReady} ${rowsReady === 1 ? 'row' : 'rows'}`,
                    footerNote: describedContext.footerNote || 'Printing and CSV download use the selected report scope shown above.',
                    emptyMessage: describedContext.emptyMessage || 'No records are available for preview.',
                    summaryItems: Array.isArray(describedContext.summaryItems) ? describedContext.summaryItems : this.buildDefaultSummaryItems(baseContext),
                };
            }

            render() {
                const context = this.getContext();
                const labels = this.getLabels();
                const columns = this.getColumns();

                if (this.elements.badge) {
                    this.elements.badge.textContent = context.badgeLabel;
                }

                if (this.elements.headline) {
                    this.elements.headline.textContent = context.headline;
                }

                if (this.elements.subtext) {
                    this.elements.subtext.textContent = context.subtext;
                }

                if (this.elements.summaryGrid) {
                    this.elements.summaryGrid.innerHTML = context.summaryItems.map((item) => `
                        <div class="report-export-summary-card">
                            <span class="report-export-summary-label">${this.escapeHtml(item.label)}</span>
                            <span class="report-export-summary-value">${this.escapeHtml(item.value)}</span>
                        </div>
                    `).join('');
                }

                if (this.elements.previewCaption) {
                    this.elements.previewCaption.textContent = context.previewCaption;
                }

                if (this.elements.previewCount) {
                    this.elements.previewCount.textContent = context.previewCountText;
                }

                if (this.elements.documentTimestamp) {
                    this.elements.documentTimestamp.textContent = context.generatedAtLabel;
                }

                if (this.elements.footerNote) {
                    this.elements.footerNote.textContent = context.footerNote;
                }

                if (this.elements.previewTableBody) {
                    this.elements.previewTableBody.innerHTML = context.previewRows.length > 0
                        ? context.previewRows.map((row) => `
                            <tr>
                                ${columns.map((column) => `
                                    <td style="${this.escapeAttribute(this.buildColumnStyle(column, false))}">
                                        ${this.escapeHtml(this.getCellValue(row, column))}
                                    </td>
                                `).join('')}
                            </tr>
                        `).join('')
                        : `<tr><td colspan="${Math.max(1, columns.length)}" class="report-export-preview-empty">${this.escapeHtml(context.emptyMessage)}</td></tr>`;
                }

                if (this.elements.printBtnLabel) {
                    this.elements.printBtnLabel.textContent = context.isLoading
                        ? labels.loadingPrintButton
                        : (context.isAllScope ? labels.allScopePrintButton : labels.printButton);
                }

                if (this.elements.downloadBtnLabel) {
                    this.elements.downloadBtnLabel.textContent = context.isLoading
                        ? labels.loadingDownloadButton
                        : (context.isAllScope ? labels.allScopeDownloadButton : labels.downloadButton);
                }

                if (this.elements.printBtn) {
                    this.elements.printBtn.disabled = context.isLoading || context.rows.length === 0;
                }

                if (this.elements.downloadBtn) {
                    this.elements.downloadBtn.disabled = context.isLoading || context.rows.length === 0;
                }
            }

            getCurrentRows() {
                const rows = typeof this.config.getCurrentRows === 'function'
                    ? (this.config.getCurrentRows() || [])
                    : [];

                return Array.isArray(rows) ? rows.map((row) => this.mapRow(row)) : [];
            }

            mapRow(row) {
                return typeof this.config.mapRow === 'function' ? this.config.mapRow(row) : row;
            }

            buildFilterParams() {
                const params = typeof this.config.buildFilterParams === 'function'
                    ? this.config.buildFilterParams()
                    : new URLSearchParams();

                if (params instanceof URLSearchParams) {
                    return params;
                }

                return new URLSearchParams(params || {});
            }

            getFilterKey() {
                return this.buildFilterParams().toString();
            }

            async setScope(scope) {
                const normalizedScope = scope === 'all' ? 'all' : 'page';
                const shouldReloadAll = normalizedScope === 'all'
                    && (!this.state.allRows.length || this.state.allRowsFilterKey !== this.getFilterKey());

                if (normalizedScope === 'page' && this.state.fetchController) {
                    this.state.fetchController.abort();
                    this.state.fetchController = null;
                    this.state.allRowsLoading = false;
                }

                this.state.scope = normalizedScope;
                this.state.preparedAt = new Date();
                this.syncScopeControls();

                if (normalizedScope === 'all' && shouldReloadAll) {
                    await this.loadAllRows();
                    return;
                }

                this.render();
            }

            async loadAllRows() {
                const route = this.config.routes?.exportData;
                const messages = this.getMessages();

                if (!route) {
                    this.state.scope = 'page';
                    this.syncScopeControls();
                    this.render();
                    this.showToast('error', messages.exportRouteMissingTitle, messages.exportRouteMissingMessage);
                    return;
                }

                const filterKey = this.getFilterKey();
                if (this.state.allRowsFilterKey === filterKey && this.state.allRows.length > 0) {
                    this.state.preparedAt = this.state.allRowsGeneratedAt || new Date();
                    this.render();
                    return;
                }

                if (this.state.fetchController) {
                    this.state.fetchController.abort();
                }

                const controller = new AbortController();
                this.state.fetchController = controller;
                this.state.allRowsLoading = true;
                this.render();

                try {
                    const params = this.buildFilterParams();
                    const data = await this.requestJson(`${route}?${params.toString()}`, {
                        method: 'GET',
                        signal: controller.signal,
                    });

                    if (this.state.fetchController !== controller) {
                        return;
                    }

                    this.state.allRows = this.extractAllRows(data).map((row) => this.mapRow(row));
                    this.state.allRowsFilterKey = filterKey;
                    this.state.allRowsGeneratedAt = this.toDateObject(this.extractGeneratedAt(data)) || new Date();
                    this.state.preparedAt = this.state.allRowsGeneratedAt;
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return;
                    }

                    console.error('[ReportExportWorkflow] Failed to load full report rows:', error);
                    this.state.scope = 'page';
                    this.syncScopeControls();
                    this.showToast('error', messages.fullLoadFailedTitle, error?.message || messages.fullLoadFailedMessage);
                } finally {
                    if (this.state.fetchController === controller) {
                        this.state.fetchController = null;
                        this.state.allRowsLoading = false;
                    }

                    this.render();
                }
            }

            extractAllRows(data) {
                if (typeof this.config.extractAllRows === 'function') {
                    return this.config.extractAllRows(data) || [];
                }

                if (Array.isArray(data?.rows)) return data.rows;
                if (Array.isArray(data?.data)) return data.data;
                if (Array.isArray(data?.records)) return data.records;
                return [];
            }

            extractGeneratedAt(data) {
                if (typeof this.config.extractGeneratedAt === 'function') {
                    return this.config.extractGeneratedAt(data);
                }

                return data?.meta?.generated_at || null;
            }

            async requestJson(url, options = {}) {
                if (typeof this.config.requestJson === 'function') {
                    return this.config.requestJson(url, options);
                }

                const response = await fetch(url, options);
                return response.json();
            }

            showToast(type, title, message, timeout) {
                if (typeof this.config.showToast === 'function') {
                    this.config.showToast(type, title, message, timeout);
                    return;
                }

                const method = type === 'error' ? 'error' : 'log';
                console[method](`[${title}] ${message}`);
            }

            print() {
                const context = this.getContext();
                const messages = this.getMessages();

                if (context.isLoading) {
                    this.showToast('info', messages.preparingTitle, messages.preparingMessage);
                    return;
                }

                if (context.rows.length === 0) {
                    this.showToast('error', messages.emptyTitle, messages.emptyMessage);
                    return;
                }

                const printWindow = window.open('', '_blank', 'width=1100,height=760');
                if (!printWindow) {
                    this.showToast('error', messages.popupBlockedTitle, messages.popupBlockedMessage);
                    return;
                }

                try {
                    printWindow.document.open();
                    printWindow.document.write(this.buildPrintDocument(context));
                    printWindow.document.close();

                    if (typeof this.config.closeModal === 'function') {
                        this.config.closeModal(this.getModalId());
                    }

                    this.showToast(
                        'success',
                        messages.printReadyTitle,
                        context.isAllScope ? messages.fullPrintReadyMessage : messages.printReadyMessage
                    );
                } catch (error) {
                    console.error('[ReportExportWorkflow] Failed to build print document:', error);
                    printWindow.close();
                    this.showToast('error', messages.printFailedTitle, messages.printFailedMessage);
                }
            }

            buildPrintLogoMarkup(branding) {
                if (branding?.image_url) {
                    return `<img src="${this.escapeAttribute(branding.image_url)}" alt="${this.escapeAttribute(branding.alt || 'Library Logo')}" loading="eager">`;
                }

                return `<span class="print-logo-fallback">${this.escapeHtml(branding?.fallback_text || 'LMS')}</span>`;
            }

            buildPrintDocument(context) {
                const documentConfig = this.getDocument();
                const branding = this.getBranding();
                const columns = this.getColumns();
                const colgroup = columns.map((column) => `<col${column.width ? ` style="width:${this.escapeAttribute(String(column.width))}"` : ''}>`).join('');
                const headerCells = columns.map((column) => `
                    <th scope="col" style="${this.escapeAttribute(this.buildColumnStyle(column, true))}">
                        ${this.escapeHtml(column.label || '')}
                    </th>
                `).join('');
                const tableRows = context.rows.map((row) => `
                    <tr>
                        ${columns.map((column) => `
                            <td style="${this.escapeAttribute(this.buildColumnStyle(column, false))}">
                                ${this.escapeHtml(this.getCellValue(row, column))}
                            </td>
                        `).join('')}
                    </tr>
                `).join('');

                return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>${this.escapeHtml(documentConfig.reportTitle)}</title>
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
            margin-bottom: 10px;
            padding: 6px 0 16px;
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
                <div class="print-logo">${this.buildPrintLogoMarkup(branding)}</div>
                <div class="print-branding-copy">
                    <p class="print-system-title">${this.escapeHtml(documentConfig.systemTitle)}</p>
                    <p class="print-report-title">${this.escapeHtml(documentConfig.reportTitle)}</p>
                    <p class="print-report-meta">${this.escapeHtml(context.generatedAtLabel)}</p>
                </div>
            </div>
        </div>

        <table class="print-table">
            <colgroup>${colgroup}</colgroup>
            <thead>
                <tr>${headerCells}</tr>
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
            }

            downloadCsv() {
                const context = this.getContext();
                const messages = this.getMessages();

                if (context.isLoading) {
                    this.showToast('info', messages.preparingTitle, messages.preparingMessage);
                    return;
                }

                if (context.rows.length === 0) {
                    this.showToast('error', messages.emptyTitle, messages.emptyMessage);
                    return;
                }

                const columns = this.getColumns();
                const headers = columns.map((column) => column.label || '');
                const escapeCsvCell = (value) => `"${String(value ?? '').replace(/"/g, '""')}"`;
                const csvRows = context.rows.map((row) => columns.map((column) => this.getCellValue(row, column)));
                const csv = '\uFEFF' + [...this.getCsvMetaRows(context), headers, ...csvRows]
                    .map((row) => row.map((cell) => escapeCsvCell(cell)).join(','))
                    .join('\r\n');

                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = this.getFilename(context);
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                window.setTimeout(() => URL.revokeObjectURL(url), 100);

                if (typeof this.config.closeModal === 'function') {
                    this.config.closeModal(this.getModalId());
                }

                this.showToast(
                    'success',
                    messages.exportReadyTitle,
                    context.isAllScope ? messages.fullExportReadyMessage : messages.exportReadyMessage
                );
            }

            getCsvMetaRows(context) {
                if (typeof this.config.getCsvMetaRows === 'function') {
                    return this.config.getCsvMetaRows(context) || [];
                }

                const documentConfig = this.getDocument();
                const branding = this.getBranding();
                const logoRows = branding?.image_url ? [['Library Logo', branding.image_url], ['']] : [];
                return [
                    [documentConfig.systemTitle],
                    [documentConfig.reportTitle],
                    [context.generatedAtLabel],
                    ...logoRows,
                    ['Report Scope', context.scopeLabel],
                    ['Records Included', String(context.rows.length)],
                    [''],
                ];
            }

            getFilename(context) {
                if (typeof this.config.getFilename === 'function') {
                    return this.config.getFilename(context);
                }

                const generatedAt = this.toDateObject(context.generatedAt) || new Date();
                return `report-export-${generatedAt.toISOString().slice(0, 10)}.csv`;
            }

            buildColumnStyle(column, isHeader) {
                const styles = [];

                if (column.width) {
                    styles.push(`width:${String(column.width)}`);
                }

                if (column.align) {
                    styles.push(`text-align:${String(column.align)}`);
                }

                if (column.nowrap) {
                    styles.push('white-space:nowrap');
                }

                if (!isHeader && column.emphasis) {
                    styles.push('font-weight:600');
                }

                return styles.join('; ');
            }

            getCellValue(row, column) {
                if (typeof column.value === 'function') {
                    return column.value(row);
                }

                return row?.[column.key] ?? '';
            }

            formatDateTime(value) {
                const date = this.toDateObject(value);
                if (!date) {
                    return 'Unknown time';
                }

                return new Intl.DateTimeFormat(undefined, {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                }).format(date);
            }

            toDateObject(value) {
                const date = value instanceof Date ? value : new Date(value);
                return Number.isNaN(date.getTime()) ? null : date;
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
        }

        window.ReportExportWorkflow = ReportExportWorkflow;
    })();
</script>
