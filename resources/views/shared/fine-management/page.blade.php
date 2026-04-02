@php
    $config = $fineManagementConfig;
    $reportExportConfig = [
        'modalId' => 'exportOptionsModal',
        'idPrefix' => 'reportExport',
        'scopeName' => 'reportExportScope',
        'labels' => [
            'title' => 'Export Fines',
            'description' => 'Print or download the fine report.',
            'scopeTitle' => 'Scope',
            'scopeHint' => 'Use this page or all filtered rows.',
            'pageOptionTitle' => 'Current page',
            'pageOptionDescription' => 'Only rows visible now.',
            'allOptionTitle' => 'Filtered report',
            'allOptionDescription' => 'All rows matching filters.',
            'badge' => 'Current page',
            'headline' => '0 fine records ready',
            'subtext' => 'Selected rows will be used for export.',
            'previewTitle' => 'Preview',
            'previewDescription' => 'Rows included in export.',
            'previewCount' => '0 rows',
            'emptyPreview' => 'No fine records selected for preview.',
            'footerNote' => 'Using current page for export.',
            'cancelButton' => 'Cancel',
            'downloadButton' => 'Download CSV',
            'printButton' => 'Print',
        ],
        'document' => [
            'systemTitle' => 'Library Management System',
            'reportTitle' => 'Fine Report',
        ],
        'columns' => [
            ['key' => 'studentId', 'label' => 'User ID', 'width' => '14%'],
            ['key' => 'studentName', 'label' => 'User Name', 'width' => '17%'],
            ['key' => 'bookTitle', 'label' => 'Book Title', 'width' => '28%', 'emphasis' => true],
            ['key' => 'dueDate', 'label' => 'Due Date', 'width' => '14%'],
            ['key' => 'daysOverdue', 'label' => 'Days Overdue', 'width' => '10%', 'align' => 'center'],
            ['key' => 'fineAmount', 'label' => 'Fine Amount', 'width' => '11%', 'align' => 'right', 'nowrap' => true],
            ['key' => 'status', 'label' => 'Status', 'width' => '10%', 'align' => 'center', 'nowrap' => true],
        ],
    ];
@endphp

<div class="fine-page" id="fineManagementRoot">
    <div class="dashboard-container">
        <div class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <div class="page-title-row">
                        <h1>{{ $config['labels']['pageTitle'] ?? 'Fines Records' }}</h1>
                    </div>
                    <p class="text-secondary">{{ $config['labels']['pageDescription'] ?? 'Manage and update fines for overdue books' }}</p>
                </div>

                @if(($config['features']['export'] ?? true) === true)
                    <button type="button" class="toolbar-btn primary" data-action="export" id="exportFinesBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m7 10 5 5 5-5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15V3" />
                        </svg>
                        <span>{{ $config['labels']['exportButton'] ?? 'Export CSV' }}</span>
                    </button>
                @endif
            </div>

            <div class="stats-grid" aria-live="polite">
                <div class="stat-card total-fines is-loading" data-stat-card="total">
                    <div class="stat-header">
                        <h3 class="stat-title">{{ $config['labels']['stats']['totalTitle'] ?? 'Total Fines' }}</h3>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 3h12" />
                                <path d="M6 8h12" />
                                <path d="m6 13 8.5 8" />
                                <path d="M6 13h3" />
                                <path d="M9 13c6.667 0 6.667-10 0-10" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number loading-line" id="totalFines">₹0.00</div>
                    <div class="stat-label" id="totalFinesMeta">0 records</div>
                </div>

                <div class="stat-card collected is-loading" data-stat-card="collected">
                    <div class="stat-header">
                        <h3 class="stat-title">{{ $config['labels']['stats']['collectedTitle'] ?? 'Collected' }}</h3>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6" />
                                <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18" />
                                <path d="M4 22h16" />
                                <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22" />
                                <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22" />
                                <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number loading-line" id="collectedFines">₹0.00</div>
                    <div class="stat-label" id="collectedFinesMeta">0 paid fines</div>
                </div>

                <div class="stat-card pending is-loading" data-stat-card="pending">
                    <div class="stat-header">
                        <h3 class="stat-title">{{ $config['labels']['stats']['pendingTitle'] ?? 'Pending' }}</h3>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4" />
                                <path d="M12 16h.01" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number loading-line" id="pendingFines">₹0.00</div>
                    <div class="stat-label" id="pendingFinesMeta">0 unpaid fines • 0 overdue</div>
                </div>

                <div class="stat-card waived is-loading" data-stat-card="waived">
                    <div class="stat-header">
                        <h3 class="stat-title">{{ $config['labels']['stats']['waivedTitle'] ?? 'Waived' }}</h3>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-number loading-line" id="waivedFines">₹0.00</div>
                    <div class="stat-label" id="waivedFinesMeta">0 waived fines</div>
                </div>
            </div>

            <section class="search-filter-container" aria-label="Fine search and filters">
                <div class="search-box">
                    <span class="search-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.35-4.35" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        class="search-input"
                        id="searchInput"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="{{ $config['labels']['searchPlaceholder'] ?? 'Search by user, book title, amount, or reason...' }}"
                        aria-label="Search fines"
                    >
                </div>

                <div class="filters-container">
                    <div class="filter-field">
                        <select class="filter-select" id="statusFilter" aria-label="Filter by status">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="waived">Waived</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>

                    <div class="filter-field">
                        <select class="filter-select" id="sortFilter" aria-label="Sort fines">
                            <option value="date-desc">Date (Newest)</option>
                            <option value="date-asc">Date (Oldest)</option>
                            <option value="amount-desc">Amount (High to Low)</option>
                            <option value="amount-asc">Amount (Low to High)</option>
                        </select>
                    </div>

                    <div class="filter-field">
                        <input type="number" class="filter-input" id="minAmountFilter" min="0" step="0.01" placeholder="Min amount" aria-label="Minimum fine amount">
                    </div>

                    <div class="filter-field">
                        <input type="number" class="filter-input" id="maxAmountFilter" min="0" step="0.01" placeholder="Max amount" aria-label="Maximum fine amount">
                    </div>

                    <button type="button" id="resetFiltersBtn" class="reset-btn" data-action="reset-filters" aria-label="Reset all filters">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.708L3 8m0 0V3m0 5h5" />
                        </svg>
                        <span>Reset</span>
                    </button>

                    <label class="admin-table-entries-control fine-entries-control" for="fineEntriesSelect">
                        <span>Show</span>
                        <select id="fineEntriesSelect" class="admin-table-entries-select" aria-label="Show fine entries">
                            @foreach ([10, 20, 50, 100] as $entryCount)
                                <option value="{{ $entryCount }}">{{ $entryCount }}</option>
                            @endforeach
                        </select>
                        <span>entries</span>
                    </label>
                </div>

                <div class="toolbar-meta">
                    <div id="filterSummary">Showing all fines</div>
                    <div id="lastUpdatedLabel">Waiting for data...</div>
                </div>
            </section>

            <div class="fines-table-container" aria-live="polite">
                <div class="fines-table-wrapper" id="finesTableWrapper" aria-busy="true">
                    <table class="fines-table">
                        <thead>
                            <tr>
                                <th scope="col">User</th>
                                <th scope="col">Book Title</th>
                                <th scope="col">Due Date</th>
                                <th scope="col">Days Overdue</th>
                                <th scope="col">Fine Amount</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="finesTableBody">
                            <tr>
                                <td colspan="7">
                                    <span class="table-skeleton-line"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="emptyState" class="empty-state" aria-hidden="true">
                    <div class="empty-state-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" />
                            <path d="M9 9h6" />
                            <path d="M9 13h6" />
                        </svg>
                    </div>
                    <h3>No fines found</h3>
                    <p id="emptyStateMessage">Try adjusting your search or filters.</p>
                    <div class="empty-state-actions">
                        <button type="button" class="reset-btn" data-action="reset-filters">Clear filters</button>
                    </div>
                </div>
            </div>

            <div id="paginationContainer" class="pagination-container">
                <div class="pagination-meta">
                    <div class="pagination-info">
                        Showing <span id="startCount">0</span> to <span id="endCount">0</span> of <span id="totalCount">0</span> results
                    </div>
                    <div class="pagination-page" id="pageInfo">Page 1 of 1</div>
                </div>
                <div id="paginationButtons" class="pagination-buttons"></div>
            </div>
        </div>
    </div>

    <div id="waiveModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="waiveModalTitle" aria-describedby="waiveModalDescription">
            <div class="modal-header">
                <h3 id="waiveModalTitle">Waive Fine</h3>
                <button type="button" class="modal-close-btn" data-modal-close="waiveModal" aria-label="Close waive fine dialog">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m18 6-12 12" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description" id="waiveModalDescription">Add a short reason. This note will be stored with the fine record and shown in the activity history.</p>
                <textarea id="waiveReason" class="modal-textarea" rows="4" maxlength="500" placeholder="Enter waiver reason..." aria-label="Waiver reason"></textarea>
                <p id="waiveReasonError" class="modal-error" hidden>Please enter a reason for waiving the fine.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn" data-modal-close="waiveModal">Cancel</button>
                <button type="button" class="modal-btn primary" id="waiveSubmitBtn">Waive Fine</button>
            </div>
        </div>
    </div>

    @include('shared.report-export.modal', ['reportExportConfig' => $reportExportConfig])
    @include('shared.action-feedback.markup', [
        'actionFeedbackConfig' => [
            'confirm' => [
                'modalId' => 'confirmActionModal',
                'iconId' => 'confirmActionIcon',
                'titleId' => 'confirmActionTitle',
                'messageId' => 'confirmActionMessage',
                'detailId' => 'confirmActionDetail',
                'submitButtonId' => 'confirmActionSubmitBtn',
                'cancelLabel' => 'Cancel',
                'confirmLabel' => 'Continue',
                'defaultTitle' => 'Confirm Action',
                'defaultMessage' => 'Are you sure you want to continue?',
            ],
            'toast' => [
                'containerId' => 'fineToastContainer',
                'liveRegionId' => 'fineLiveRegion',
            ],
        ],
    ])
</div>
