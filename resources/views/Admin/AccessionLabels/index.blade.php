@extends('Admin.layouts.app')

@section('title', 'Accession Number Generator')

@push('styles')
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
        <div class="accession-next" aria-label="Next available accession number">
            <span>Next Available Accession Number</span>
            <strong>{{ $nextAvailable }}</strong>
        </div>
    </header>

    <nav class="accession-tabs" aria-label="Accession label modes">
        <a href="{{ route('admin.accession-labels.index') }}" class="{{ $mode === 'generate' ? 'is-active' : '' }}" @if($mode === 'generate') aria-current="page" @endif>
            <i data-lucide="barcode" aria-hidden="true"></i> Generate Range
        </a>
        <a href="{{ route('admin.accession-labels.index', ['mode' => 'reprint']) }}" class="{{ $mode === 'reprint' ? 'is-active' : '' }}" @if($mode === 'reprint') aria-current="page" @endif>
            <i data-lucide="printer" aria-hidden="true"></i> Reprint Existing
        </a>
    </nav>

    @if ($errors->any())
        <div class="accession-alert accession-alert-error" role="alert">
            <i data-lucide="circle-alert" aria-hidden="true"></i>
            <div><strong>Please correct the following:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        </div>
    @endif

    @if ($mode === 'generate')
        <section class="accession-card" aria-labelledby="generation-settings-title">
            <div class="accession-section-heading">
                <div><span class="accession-kicker">Generation settings</span><h2 id="generation-settings-title">Generate accession numbers</h2></div>
                <span class="accession-limit">Maximum {{ number_format($maximum) }} labels per generation</span>
            </div>
            <form action="{{ route('admin.accession-labels.preview') }}" method="POST" class="accession-generate-form" data-generate-form>
                @csrf
                <div class="accession-use-next">
                    <span>Start with <strong>{{ $nextAvailable }}</strong></span>
                    <button type="button" class="accession-link-button" data-use-next="{{ $nextAvailable }}">Use Next Available</button>
                </div>
                <div class="accession-input-grid">
                    <div class="accession-field">
                        <label for="from">From Accession Number</label>
                        <input id="from" name="from" value="{{ old('from', $preview['from'] ?? $nextAvailable) }}" placeholder="ACC-001800" autocomplete="off" aria-describedby="from-help" @error('from') aria-invalid="true" aria-describedby="from-error" @enderror required>
                        <small id="from-help">Format: ACC followed by a six-digit number.</small>
                        @error('from')<small id="from-error" class="accession-field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="accession-field">
                        <label for="to">To Accession Number</label>
                        <input id="to" name="to" value="{{ old('to', $preview['to'] ?? '') }}" placeholder="ACC-001850" autocomplete="off" @error('to') aria-invalid="true" aria-describedby="to-error" @enderror required>
                        <small>Inclusive ending number.</small>
                        @error('to')<small id="to-error" class="accession-field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
                <div class="accession-actions">
                    <button type="submit" class="accession-button accession-button-primary" data-generate-button>
                        <i data-lucide="scan-line" aria-hidden="true"></i><span>Generate Preview</span>
                    </button>
                    <a href="{{ route('admin.accession-labels.index') }}" class="accession-button accession-button-secondary">
                        <i data-lucide="rotate-ccw" aria-hidden="true"></i> Reset
                    </a>
                </div>
            </form>
        </section>
    @else
        <section class="accession-card" aria-labelledby="reprint-title">
            <div class="accession-section-heading">
                <div><span class="accession-kicker">Existing physical copies</span><h2 id="reprint-title">Find a label to reprint</h2></div>
            </div>
            <form method="GET" action="{{ route('admin.accession-labels.index') }}" class="accession-search-form">
                <input type="hidden" name="mode" value="reprint">
                <div class="accession-field">
                    <label for="q">Accession number, book title or ISBN</label>
                    <div class="accession-search-control">
                        <input id="q" name="q" value="{{ $search }}" placeholder="ACC-001752, Clean Code, or ISBN" required>
                        <button class="accession-button accession-button-primary" type="submit"><i data-lucide="search" aria-hidden="true"></i> Search</button>
                    </div>
                </div>
            </form>

            @if ($search !== '')
                <div class="accession-search-results" aria-live="polite">
                    <p class="accession-result-count">{{ $copies->count() }} {{ Str::plural('copy', $copies->count()) }} found</p>
                    @forelse ($copies as $copy)
                        <article class="accession-copy-result">
                            <div class="accession-copy-icon"><i data-lucide="book-open" aria-hidden="true"></i></div>
                            <div class="accession-copy-copy">
                                <strong>{{ $copy->book?->title ?? 'Unknown title' }}</strong>
                                <span>{{ $copy->accession_number }} @if($copy->book?->isbn) · ISBN {{ $copy->book->isbn }} @endif</span>
                            </div>
                            <span class="accession-status">{{ ucfirst($copy->status) }}</span>
                            <button type="button" class="accession-button accession-button-secondary" data-reprint-copy="{{ $copy->id }}">
                                <i data-lucide="printer" aria-hidden="true"></i> Preview &amp; Print
                            </button>
                        </article>
                    @empty
                        <div class="accession-empty"><i data-lucide="search-x" aria-hidden="true"></i><strong>No matching physical copies</strong><p>Try a complete accession number, a title, or an ISBN.</p></div>
                    @endforelse
                </div>
            @endif
        </section>
    @endif

    <form method="POST" action="{{ route('admin.accession-labels.print') }}" target="_blank" id="accessionPrintForm">
        @csrf
        <input type="hidden" name="action_type" value="range" data-action-type>
        <input type="hidden" name="from" value="{{ $preview['from'] ?? '' }}">
        <input type="hidden" name="to" value="{{ $preview['to'] ?? '' }}">
        <input type="hidden" name="print_scope" value="selected" data-print-scope-input>
        <input type="hidden" name="copy_id" value="" data-copy-id>

        <section class="accession-card" aria-labelledby="print-settings-title">
            <div class="accession-section-heading">
                <div><span class="accession-kicker">Print settings</span><h2 id="print-settings-title">Label layout</h2></div>
            </div>
            <div class="accession-settings-grid">
                <div class="accession-field">
                    <label for="label_size">Label Size</label>
                    <select id="label_size" name="label_size" data-label-size>
                        @foreach ($labelSizes as $key => $size)
                            <option value="{{ $key }}" data-max-columns="{{ $size['max_columns'] }}" @selected($defaults['label_size'] === $key)>{{ $size['label'] }} · {{ $size['width_mm'] }} × {{ $size['height_mm'] }} mm</option>
                        @endforeach
                    </select>
                </div>
                <div class="accession-field">
                    <label for="columns">Columns</label>
                    <select id="columns" name="columns" data-columns>
                        @foreach ([2, 3, 4, 5] as $column)<option value="{{ $column }}" @selected($defaults['columns'] === $column)>{{ $column }}</option>@endforeach
                    </select>
                </div>
                <div class="accession-field">
                    <label for="page_size">Page Size</label>
                    <select id="page_size" name="page_size">
                        @foreach (config('accession-labels.page_sizes') as $pageSize)<option value="{{ $pageSize }}" @selected($defaults['page_size'] === $pageSize)>{{ $pageSize }}</option>@endforeach
                    </select>
                </div>
                <div class="accession-toggle-grid">
                    <label><input type="hidden" name="show_accession" value="0"><input type="checkbox" name="show_accession" value="1" checked> Show accession number</label>
                    <label><input type="hidden" name="show_library_name" value="0"><input type="checkbox" name="show_library_name" value="1"> Show library name</label>
                    <label class="{{ $branding['has_custom_logo'] ? '' : 'is-disabled' }}"><input type="hidden" name="show_logo" value="0"><input type="checkbox" name="show_logo" value="1" @disabled(!$branding['has_custom_logo'])> Show library logo</label>
                    <label><input type="hidden" name="show_border" value="0"><input type="checkbox" name="show_border" value="1" checked> Show label border</label>
                </div>
            </div>
        </section>

        @if ($preview)
            <section class="accession-card accession-preview-section" aria-labelledby="preview-title">
                <div class="accession-preview-header">
                    <div><span class="accession-kicker">Preview</span><h2 id="preview-title">{{ $preview['available'] }} printable {{ Str::plural('label', $preview['available']) }}</h2><p>From {{ $preview['from'] }} to {{ $preview['to'] }}</p></div>
                    @if ($preview['available'] > 0)
                        <div class="accession-selection-actions"><button type="button" data-select-all>Select All</button><button type="button" data-clear-selection>Clear Selection</button></div>
                    @endif
                </div>

                <div class="accession-summary" aria-label="Generation summary">
                    <div><span>Requested</span><strong>{{ $preview['requested'] }}</strong></div>
                    <div><span>Available</span><strong>{{ $preview['available'] }}</strong></div>
                    <div><span>Already Existing</span><strong>{{ count($preview['existing']) }}</strong></div>
                    <div><span>Selected</span><strong data-selected-count>{{ $preview['available'] }}</strong></div>
                </div>

                @if ($preview['available'] > 0)
                    <div class="accession-preview-grid">
                        @foreach ($preview['labels'] as $label)
                            <label class="accession-label-preview is-selected">
                                <input type="checkbox" name="labels[]" value="{{ $label['accession'] }}" checked>
                                <span class="accession-checkmark" aria-hidden="true"><i data-lucide="check"></i></span>
                                <span class="accession-barcode">{!! $label['svg'] !!}</span>
                                <strong>{{ $label['accession'] }}</strong>
                                <span class="sr-only">Select {{ $label['accession'] }} for printing</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="accession-print-actions">
                        <span><strong data-selected-footer>{{ $preview['available'] }}</strong> labels selected</span>
                        <div><button type="button" class="accession-button accession-button-secondary" data-print="selected"><i data-lucide="list-checks" aria-hidden="true"></i> Print Selected</button><button type="button" class="accession-button accession-button-primary" data-print="all"><i data-lucide="printer" aria-hidden="true"></i> Print All</button></div>
                    </div>
                @else
                    <div class="accession-empty"><i data-lucide="badge-x" aria-hidden="true"></i><strong>No printable accession numbers were generated</strong><p>Every number in this range already exists.</p></div>
                @endif

                @if (count($preview['existing']) > 0)
                    <details class="accession-skipped" open>
                        <summary>Skipped Accession Numbers ({{ count($preview['existing']) }})</summary>
                        <div>@foreach ($preview['existing'] as $accession)<span><strong>{{ $accession }}</strong> — Already exists</span>@endforeach</div>
                    </details>
                @endif
            </section>
        @endif
    </form>

    <div class="accession-notice">
        <i data-lucide="info" aria-hidden="true"></i>
        <p><strong>Labels are not reservations.</strong> They are not assigned to books until used during physical-copy creation. Availability is checked when previewing and again before printing.</p>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/JS/accession-labels.js') }}?v={{ filemtime(public_path('admin/JS/accession-labels.js')) }}" defer></script>
@endpush
