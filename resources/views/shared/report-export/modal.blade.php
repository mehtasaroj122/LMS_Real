@php
    $reportExportConfig = $reportExportConfig ?? [];
    $labels = array_merge([
        'title' => 'Export Report',
        'description' => 'Print or download this report.',
        'scopeTitle' => 'Scope',
        'scopeHint' => 'Use this page or all filtered rows.',
        'pageOptionTitle' => 'Current page',
        'pageOptionDescription' => 'Only visible rows.',
        'allOptionTitle' => 'Filtered report',
        'allOptionDescription' => 'All rows matching filters.',
        'badge' => 'Current page',
        'headline' => '0 records ready',
        'subtext' => 'Selected rows will be used for export.',
        'previewTitle' => 'Preview',
        'previewDescription' => 'Rows included in export.',
        'previewCount' => '0 rows',
        'emptyPreview' => 'No records selected for preview.',
        'footerNote' => 'Using current page for export.',
        'cancelButton' => 'Cancel',
        'downloadButton' => 'Download CSV',
        'printButton' => 'Print',
    ], $reportExportConfig['labels'] ?? []);

    $document = array_merge([
        'systemTitle' => 'Library Management System',
        'reportTitle' => 'Report',
    ], $reportExportConfig['document'] ?? []);

    $columns = $reportExportConfig['columns'] ?? [];
    $modalId = $reportExportConfig['modalId'] ?? 'reportExportModal';
    $idPrefix = $reportExportConfig['idPrefix'] ?? 'reportExport';
    $scopeName = $reportExportConfig['scopeName'] ?? ($idPrefix . 'Scope');

    $ids = [
        'title' => $idPrefix . 'Title',
        'description' => $idPrefix . 'Description',
        'scopeLabel' => $idPrefix . 'ScopeLabel',
        'scopeHint' => $idPrefix . 'ScopeHint',
        'scopePage' => $idPrefix . 'ScopePage',
        'scopeAll' => $idPrefix . 'ScopeAll',
        'badge' => $idPrefix . 'Badge',
        'headline' => $idPrefix . 'Headline',
        'subtext' => $idPrefix . 'Subtext',
        'summaryGrid' => $idPrefix . 'SummaryGrid',
        'previewCaption' => $idPrefix . 'PreviewCaption',
        'previewCount' => $idPrefix . 'PreviewCount',
        'documentTimestamp' => $idPrefix . 'DocumentTimestamp',
        'previewTableBody' => $idPrefix . 'PreviewTableBody',
        'footerNote' => $idPrefix . 'FooterNote',
        'downloadBtn' => $idPrefix . 'DownloadBtn',
        'printBtn' => $idPrefix . 'PrintBtn',
    ];
@endphp

<div id="{{ $modalId }}" class="report-export-modal" aria-hidden="true">
    <div class="report-export-panel" role="dialog" aria-modal="true" aria-labelledby="{{ $ids['title'] }}" aria-describedby="{{ $ids['description'] }}">
        <div class="report-export-header">
            <div class="report-export-header-copy">
                <h3 id="{{ $ids['title'] }}">{{ $labels['title'] }}</h3>
                <p id="{{ $ids['description'] }}">{{ $labels['description'] }}</p>
            </div>
            <button type="button" class="report-export-close-btn" data-modal-close="{{ $modalId }}" aria-label="Close report export dialog">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m18 6-12 12" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </div>

        <div class="report-export-body">
            <div class="report-export-scope-picker" role="radiogroup" aria-labelledby="{{ $ids['scopeLabel'] }}" aria-describedby="{{ $ids['scopeHint'] }}">
                <div class="report-export-scope-heading">
                    <span class="report-export-scope-label" id="{{ $ids['scopeLabel'] }}">{{ $labels['scopeTitle'] }}</span>
                    <span class="report-export-scope-hint" id="{{ $ids['scopeHint'] }}">{{ $labels['scopeHint'] }}</span>
                </div>

                <div class="report-export-scope-options">
                    <label class="report-export-scope-option">
                        <input type="radio" class="report-export-scope-input" name="{{ $scopeName }}" id="{{ $ids['scopePage'] }}" value="page" checked>
                        <span class="report-export-scope-card">
                            <span class="report-export-scope-title">{{ $labels['pageOptionTitle'] }}</span>
                            <span class="report-export-scope-description">{{ $labels['pageOptionDescription'] }}</span>
                        </span>
                    </label>

                    <label class="report-export-scope-option">
                        <input type="radio" class="report-export-scope-input" name="{{ $scopeName }}" id="{{ $ids['scopeAll'] }}" value="all">
                        <span class="report-export-scope-card">
                            <span class="report-export-scope-title">{{ $labels['allOptionTitle'] }}</span>
                            <span class="report-export-scope-description">{{ $labels['allOptionDescription'] }}</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="report-export-hero">
                <div class="report-export-hero-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9V2h12v7" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 14h12v8H6z" />
                    </svg>
                </div>

                <div class="report-export-hero-copy">
                    <div class="report-export-overline" id="{{ $ids['badge'] }}">{{ $labels['badge'] }}</div>
                    <h4 id="{{ $ids['headline'] }}">{{ $labels['headline'] }}</h4>
                    <p id="{{ $ids['subtext'] }}">{{ $labels['subtext'] }}</p>
                </div>
            </div>

            <div id="{{ $ids['summaryGrid'] }}" class="report-export-summary-grid" aria-live="polite"></div>

            <div class="report-export-preview-panel">
                <div class="report-export-preview-header">
                    <div>
                        <h4>{{ $labels['previewTitle'] }}</h4>
                        <p id="{{ $ids['previewCaption'] }}">{{ $labels['previewDescription'] }}</p>
                    </div>
                    <span class="report-export-preview-pill" id="{{ $ids['previewCount'] }}">{{ $labels['previewCount'] }}</span>
                </div>

                <div class="report-export-document-header">
                    <p class="report-export-document-system">{{ $document['systemTitle'] }}</p>
                    <p class="report-export-document-title">{{ $document['reportTitle'] }}</p>
                    <p class="report-export-document-meta" id="{{ $ids['documentTimestamp'] }}">Generated on --</p>
                </div>

                <div class="report-export-preview-table-wrap">
                    <table class="report-export-preview-table">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                    <th scope="col">{{ $column['label'] ?? '' }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="{{ $ids['previewTableBody'] }}">
                            <tr>
                                <td colspan="{{ max(1, count($columns)) }}" class="report-export-preview-empty">{{ $labels['emptyPreview'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="report-export-footer">
            <p class="report-export-footer-note" id="{{ $ids['footerNote'] }}">{{ $labels['footerNote'] }}</p>
            <div class="report-export-actions">
                <button type="button" class="report-export-btn" data-modal-close="{{ $modalId }}">{{ $labels['cancelButton'] }}</button>

                <button type="button" class="report-export-btn report-export-btn-download" id="{{ $ids['downloadBtn'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <path d="m7 10 5 5 5-5" />
                        <path d="M12 15V3" />
                    </svg>
                    <span>{{ $labels['downloadButton'] }}</span>
                </button>

                <button type="button" class="report-export-btn report-export-btn-primary" id="{{ $ids['printBtn'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9V2h12v7" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 14h12v8H6z" />
                    </svg>
                    <span>{{ $labels['printButton'] }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
