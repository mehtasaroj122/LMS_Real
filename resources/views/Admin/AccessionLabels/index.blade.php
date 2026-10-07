@extends('Admin.layouts.app')

@section('title', 'Accession Number Generator')

@push('styles')
    @include('shared.action-feedback.styles')
    <link rel="stylesheet" href="{{ asset('admin/CSS/accession-labels.css') }}?v={{ filemtime(public_path('admin/CSS/accession-labels.css')) }}">
@endpush

@section('content')
<div class="accession-page" data-accession-page>
    <header class="accession-page-header">
        <div>
            <p class="accession-breadcrumb">Book Management / Accession Number Generator</p>
            <h1>Accession Number Generator</h1>
            <p>Generate, preview and print barcode labels for physical book copies.</p>
        </div>
        <div class="accession-next" aria-live="polite">
            <span>Next Available Accession Number</span>
            <strong data-next-accession>{{ $nextAvailable }}</strong>
            <button type="button" data-use-next>Use Next Available</button>
        </div>
    </header>

    <div class="accession-tabs" role="tablist" aria-label="Accession label modes">
        <button type="button" role="tab" aria-selected="true" aria-controls="generatePanel" id="generateTab" class="is-active" data-workspace-tab="generate">
            <i data-lucide="barcode" aria-hidden="true"></i> Generate Labels
        </button>
        <button type="button" role="tab" aria-selected="false" aria-controls="reprintPanel" id="reprintTab" data-workspace-tab="reprint">
            <i data-lucide="printer" aria-hidden="true"></i> Reprint Existing
        </button>
    </div>

    <section id="generatePanel" role="tabpanel" aria-labelledby="generateTab" data-workspace-panel="generate">
        <section class="accession-card" aria-labelledby="generation-settings-title">
            <div class="accession-section-heading">
                <div><span class="accession-kicker">Generation settings</span><h2 id="generation-settings-title">Generate accession numbers</h2></div>
                <span class="accession-limit">Maximum {{ number_format($maximum) }} printable labels · scan limit {{ number_format($maximumScan) }}</span>
            </div>

            <form class="accession-generate-form" data-generate-form novalidate>
                <fieldset class="accession-method-fieldset">
                    <legend>Generation Method</legend>
                    <label class="accession-method-option is-selected">
                        <input type="radio" name="method" value="quantity" checked>
                        <span><strong>Start + Quantity</strong><small>Recommended for batch labels</small></span>
                    </label>
                    <label class="accession-method-option">
                        <input type="radio" name="method" value="range">
                        <span><strong>From / To</strong><small>Use an exact sequential range</small></span>
                    </label>
                </fieldset>

                <div data-method-panel="quantity">
                    <div class="accession-input-grid">
                        <div class="accession-field">
                            <label for="start">Starting Accession Number</label>
                            <input id="start" name="start" value="{{ $nextAvailable }}" placeholder="ACC-001796" autocomplete="off" required>
                            <small data-field-error="start">Format: ACC followed by a six-digit number.</small>
                        </div>
                        <div class="accession-field">
                            <label for="quantity">Total Labels</label>
                            <input id="quantity" name="quantity" value="100" type="number" min="1" max="{{ $maximum }}" step="1" inputmode="numeric" required>
                            <small data-field-error="quantity">Quantity is the number of printable unique labels.</small>
                        </div>
                    </div>
                    <div class="accession-quick-row" aria-label="Quick quantities">
                        <span>Quick:</span>
                        @foreach ([10, 25, 50, 100, 250, 500] as $quick)
                            @if ($quick <= $maximum)<button type="button" data-quick-quantity="{{ $quick }}">{{ $quick }}</button>@endif
                        @endforeach
                    </div>
                    <label class="accession-skip-toggle">
                        <input type="checkbox" name="skip_existing" value="1" checked>
                        <span><strong>Skip existing accession numbers</strong><small>Continue scanning until the requested number of printable labels is found.</small></span>
                    </label>
                    <div class="accession-calculation">
                        <span>Calculated End <small>Server result may extend farther when existing numbers are skipped.</small></span>
                        <strong data-calculated-end>—</strong>
                    </div>
                </div>

                <div data-method-panel="range" hidden>
                    <div class="accession-input-grid">
                        <div class="accession-field">
                            <label for="from">From Accession Number</label>
                            <input id="from" name="from" value="{{ $nextAvailable }}" placeholder="ACC-001796" autocomplete="off">
                            <small data-field-error="from">Inclusive starting number.</small>
                        </div>
                        <div class="accession-field">
                            <label for="to">To Accession Number</label>
                            <input id="to" name="to" placeholder="ACC-001850" autocomplete="off">
                            <small data-field-error="to">Inclusive ending number.</small>
                        </div>
                    </div>
                    <div class="accession-calculation">
                        <span>Total Requested</span><strong data-range-total>—</strong>
                    </div>
                </div>

                <div class="accession-inline-error" data-generate-error role="alert" hidden></div>
                <div class="accession-actions">
                    <button type="submit" class="accession-button accession-button-primary" data-generate-button>
                        <i data-lucide="scan-line" aria-hidden="true"></i><span>Generate Preview</span>
                    </button>
                    <button type="button" class="accession-button accession-button-reset" data-reset-generation>
                        <i data-lucide="rotate-ccw" aria-hidden="true"></i> Reset
                    </button>
                </div>
            </form>
        </section>

        <div data-settings-anchor="generate"></div>
        <div class="accession-stale" data-preview-stale role="status" hidden>
            <i data-lucide="refresh-cw" aria-hidden="true"></i>
            <span>Generation settings changed. Generate preview again to update labels.</span>
        </div>
        <section class="accession-card accession-preview-section" data-generated-preview aria-live="polite" hidden></section>
        <section class="accession-card accession-preview-loading" data-generated-loading hidden aria-live="polite">
            <span class="accession-spinner" aria-hidden="true"></span><strong>Generating preview...</strong>
            <div class="accession-skeleton-grid">@for ($i = 0; $i < 8; $i++)<span></span>@endfor</div>
        </section>
    </section>

    <section id="reprintPanel" role="tabpanel" aria-labelledby="reprintTab" data-workspace-panel="reprint" hidden>
        <section class="accession-card" aria-labelledby="reprint-title">
            <div class="accession-section-heading">
                <div><span class="accession-kicker">Existing physical copies</span><h2 id="reprint-title">Reprint existing labels</h2></div>
            </div>
            <form class="accession-search-form" data-search-form>
                <div class="accession-reprint-toolbar">
                    <div class="accession-field accession-search-field"><label for="copySearch">Book title, ISBN or accession number</label><input id="copySearch" name="q" placeholder="Search title, ISBN or accession..." autocomplete="off"></div>
                    <div class="accession-field"><label for="statusFilter">Status</label><select id="statusFilter" name="status"><option value="">All Statuses</option>@foreach($filterOptions['statuses'] as $status)<option value="{{ $status }}">{{ Str::headline($status) }}</option>@endforeach</select></div>
                    <div class="accession-field"><label for="typeFilter">Book Type</label><select id="typeFilter" name="book_type"><option value="">All Types</option>@foreach($filterOptions['types'] as $type)<option value="{{ $type }}">{{ Str::headline($type) }}</option>@endforeach</select></div>
                    <div class="accession-field"><label for="conditionFilter">Condition</label><select id="conditionFilter" name="condition"><option value="">All Conditions</option>@foreach($filterOptions['conditions'] as $condition)<option value="{{ $condition }}">{{ Str::headline($condition) }}</option>@endforeach</select></div>
                    <div class="accession-field"><label for="sortFilter">Sort</label><select id="sortFilter" name="sort"><option value="book_title">Book Title A–Z</option><option value="newest">Newest</option><option value="oldest">Oldest</option><option value="accession_asc">Accession Ascending</option><option value="accession_desc">Accession Descending</option></select></div>
                    <button class="accession-button accession-button-reset" type="button" data-reset-search><i data-lucide="rotate-ccw" aria-hidden="true"></i> Reset</button>
                    <div class="accession-field accession-entries-field"><label for="entriesFilter">Show entries</label><select id="entriesFilter" name="per_page">@foreach([10,25,50,100] as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach</select></div>
                </div>
            </form>
            <div class="accession-inline-error" data-search-error role="alert" hidden></div>
        </section>

        <section class="accession-card accession-results-card" data-reprint-results>
            <div data-book-results hidden></div>
            <div data-copy-results hidden>
                <div class="accession-selected-book" data-selected-book-header></div>
                <button type="button" class="accession-button accession-button-secondary accession-back-button" data-back-to-books><i data-lucide="arrow-left" aria-hidden="true"></i> Back to Search Results</button>
            </div>
            <div class="accession-result-toolbar" data-result-toolbar hidden>
                <label><input type="checkbox" data-select-result-page> Select all on this page</label>
                <strong><span data-reprint-selected-count>0</span> selected</strong>
                <div>
                    <button type="button" class="accession-button accession-button-secondary" data-preview-selected disabled><i data-lucide="eye" aria-hidden="true"></i> Preview Selected</button>
                    <button type="button" class="accession-button accession-button-primary" data-print-reprints disabled><i data-lucide="printer" aria-hidden="true"></i> Print Selected</button>
                    <button type="button" class="accession-button accession-button-quiet" data-clear-reprints disabled>Clear Selection</button>
                </div>
            </div>
            <div class="accession-results-live" aria-live="polite" data-results-announcement></div>
            <div class="accession-table-wrap" data-results-table hidden>
                <table class="accession-results-table">
                    <thead><tr><th><span class="sr-only">Select</span></th><th>Accession Number</th><th>Status</th><th>Condition</th><th>Book Type</th><th>Shelf</th><th>Entry Date</th><th>Action</th></tr></thead>
                    <tbody data-results-body></tbody>
                </table>
            </div>
            <div class="accession-results-loading" data-results-loading hidden><span class="accession-spinner" aria-hidden="true"></span><strong data-results-loading-text>Searching books...</strong></div>
            <div class="accession-empty" data-results-empty><i data-lucide="book-search" aria-hidden="true"></i><strong>Search for a book or physical copy</strong><p>Enter a book title, ISBN, or accession number to find labels available for reprinting.</p></div>
            <div class="accession-pagination" data-pagination hidden></div>
        </section>
        <div data-settings-anchor="reprint"></div>
    </section>

    <section class="accession-card" aria-labelledby="print-settings-title" data-shared-print-settings>
        <div class="accession-section-heading">
            <div><span class="accession-kicker">Print settings</span><h2 id="print-settings-title">Label layout</h2></div>
            <span class="accession-saved-note"><i data-lucide="save" aria-hidden="true"></i> Preserved for this browser</span>
        </div>
        <form class="accession-settings-grid" data-print-settings>
            <div class="accession-field"><label for="label_size">Label Size</label><select id="label_size" name="label_size" data-label-size>@foreach ($labelSizes as $key => $size)<option value="{{ $key }}" data-max-columns="{{ $size['max_columns'] }}" @selected($defaults['label_size'] === $key)>{{ $size['label'] }} · {{ $size['width_mm'] }} × {{ $size['height_mm'] }} mm</option>@endforeach</select></div>
            <div class="accession-field"><label for="columns">Columns</label><select id="columns" name="columns" data-columns>@foreach ([2,3,4,5] as $column)<option value="{{ $column }}" @selected($defaults['columns'] === $column)>{{ $column }}</option>@endforeach</select></div>
            <div class="accession-field"><label for="page_size">Page Size</label><select id="page_size" name="page_size">@foreach(config('accession-labels.page_sizes') as $pageSize)<option value="{{ $pageSize }}" @selected($defaults['page_size'] === $pageSize)>{{ $pageSize }}</option>@endforeach</select></div>
            <div class="accession-field"><label for="orientation">Orientation</label><select id="orientation" name="orientation"><option value="portrait">Portrait</option><option value="landscape">Landscape</option></select></div>
            <div class="accession-field"><label for="barcode_height">Barcode Height</label><select id="barcode_height" name="barcode_height"><option value="48">Compact</option><option value="64" selected>Standard</option><option value="88">Tall</option></select></div>
            <div class="accession-field"><label for="copies_per_label">Copies per Label</label><input id="copies_per_label" name="copies_per_label" type="number" min="1" max="10" value="1"></div>
            <div class="accession-toggle-grid">
                <label><input type="checkbox" name="show_accession" value="1" checked> Show accession number</label>
                <label><input type="checkbox" name="show_library_name" value="1"> Show library name</label>
                <label class="{{ $branding['has_custom_logo'] ? '' : 'is-disabled' }}"><input type="checkbox" name="show_logo" value="1" @disabled(!$branding['has_custom_logo'])> Show library logo</label>
                <label><input type="checkbox" name="show_border" value="1" checked> Show label border</label>
            </div>
        </form>
    </section>

    <div class="accession-notice"><i data-lucide="info" aria-hidden="true"></i><p><strong>Labels are not reservations.</strong> They are not assigned to books until used during physical-copy creation. Availability is rechecked before printing.</p></div>

    <div class="accession-modal" data-preview-modal aria-hidden="true">
        <div class="accession-modal-panel" role="dialog" aria-modal="true" aria-labelledby="labelPreviewModalTitle">
            <div class="accession-modal-header"><div><span class="accession-kicker">Print preview</span><h2 id="labelPreviewModalTitle">Selected barcode labels</h2></div><button type="button" data-close-preview aria-label="Close preview"><i data-lucide="x"></i></button></div>
            <div class="accession-modal-summary" data-modal-summary></div>
            <div class="accession-modal-labels" data-modal-labels></div>
            <div class="accession-modal-actions"><button type="button" class="accession-button accession-button-secondary" data-close-preview>Close</button><button type="button" class="accession-button accession-button-primary" data-modal-print><i data-lucide="printer" aria-hidden="true"></i> Prepare Print</button></div>
        </div>
    </div>

    @include('shared.action-feedback.markup')
</div>
@endsection

@push('scripts')
    @include('shared.action-feedback.scripts')
    @php
        $accessionLabelClientConfig = [
            'csrf' => csrf_token(),
            'next' => route('admin.accession-labels.next'),
            'preview' => route('admin.accession-labels.preview'),
            'search' => route('admin.accession-labels.search'),
            'bookCopies' => route('admin.accession-labels.book-copies', '__BOOK__'),
            'existingPreview' => route('admin.accession-labels.existing-preview'),
            'preparePrint' => route('admin.accession-labels.print'),
            'nextAvailable' => $nextAvailable,
            'maximum' => $maximum,
            'previewPageSize' => $previewPageSize,
        ];
    @endphp
    <script>
        window.accessionLabelConfig = @json($accessionLabelClientConfig);
    </script>
    <script src="{{ asset('admin/JS/accession-labels.js') }}?v={{ filemtime(public_path('admin/JS/accession-labels.js')) }}" defer></script>
@endpush
