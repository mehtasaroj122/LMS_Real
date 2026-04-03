@php
    $containerId = $containerId ?? 'paginationContainer';
    $fromId = $fromId ?? 'paginationStart';
    $toId = $toId ?? 'paginationEnd';
    $totalId = $totalId ?? 'paginationTotal';
    $pageInfoId = $pageInfoId ?? 'paginationPageInfo';
    $buttonsId = $buttonsId ?? 'paginationButtons';
    $label = $label ?? 'results';
@endphp

<div id="{{ $containerId }}" class="admin-table-pagination" hidden>
    <div class="admin-table-pagination-meta">
        <div class="admin-table-pagination-summary">
            Showing <span id="{{ $fromId }}">0</span> to <span id="{{ $toId }}">0</span> of <span id="{{ $totalId }}">0</span> {{ $label }}
        </div>
        <div class="admin-table-pagination-page" id="{{ $pageInfoId }}">Page 0 of 0</div>
    </div>
    <div class="admin-table-pagination-nav" id="{{ $buttonsId }}"></div>
</div>
