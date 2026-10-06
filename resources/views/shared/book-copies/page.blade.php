<div
    class="book-copies-page"
    id="bookCopiesPage"
    data-base-url="{{ url('/' . $role . '/book-copies') }}"
    data-bulk-preview-url="{{ route($role . '.books.copies.bulk-preview', $book) }}"
    data-bulk-delete-url="{{ route($role . '.books.copies.bulk-delete', $book) }}"
    data-book-title="{{ $book->title }}"
    data-book-isbn="{{ $book->isbn }}"
>
    <div class="book-copies-header">
        <div>
            <p class="book-copies-crumb">Book Management / Physical Copies</p>
            <h1 class="book-copies-title">Manage Book Copies</h1>
            <p class="book-copies-subtitle">{{ $book->title }} · Book ID {{ $book->id }} · ISBN {{ $book->isbn }}</p>
        </div>
        <a class="book-copies-back" href="{{ $role === 'admin' ? route('admin.books.index') : route('staff.book-management.index') }}">Back to books</a>
    </div>

    <div class="book-copies-summary">
        @foreach(['total' => ['label' => 'Total Copies', 'icon' => 'fa-copy'], 'available' => ['label' => 'Available', 'icon' => 'fa-circle-check'], 'issued' => ['label' => 'Issued', 'icon' => 'fa-book-reader'], 'lost' => ['label' => 'Lost', 'icon' => 'fa-magnifying-glass'], 'damaged' => ['label' => 'Damaged', 'icon' => 'fa-triangle-exclamation']] as $key => $stat)
            <div class="book-copies-stat">
                <div class="book-copies-stat-top">
                    <div class="book-copies-stat-label">{{ $stat['label'] }}</div>
                    <span class="book-copies-stat-icon {{ $key }}" aria-hidden="true"><i class="fas {{ $stat['icon'] }}"></i></span>
                </div>
                <div class="book-copies-stat-value" data-copy-summary="{{ $key }}">{{ $summary[$key] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    <form id="copyFilters" class="book-copies-toolbar" method="get" action="{{ route($role . '.books.copies.index', $book) }}">
        <div class="book-copies-search">
            @if($role === 'admin')
                <svg class="book-copies-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
            @endif
            <input class="book-copies-control" id="copySearch" name="search" type="search" value="{{ $search }}" placeholder="Accession, student, ID..." aria-label="Search copies" maxlength="100">
        </div>
        <select class="book-copies-control" id="copyStatusFilter" name="status" aria-label="Status"><option value="all">All statuses</option>@foreach(['available' => 'Available', 'issued' => 'Issued', 'lost' => 'Lost', 'damaged' => 'Damaged'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select>
        <select class="book-copies-control" id="copyTypeFilter" name="book_type" aria-label="Book Type"><option value="all">All types</option><option value="borrowing" @selected($bookType === 'borrowing')>Borrowing</option><option value="reference" @selected($bookType === 'reference')>Reference</option></select>
        <button type="button" class="book-copies-button" id="resetCopyFilters"><i class="fas fa-rotate-left" aria-hidden="true"></i> Reset</button>
        <label class="book-copies-entries" for="copyEntriesSelect"><span>Show</span><select class="book-copies-control" id="copyEntriesSelect" name="per_page" aria-label="Show copy entries">@foreach([10, 20, 50, 100] as $count)<option value="{{ $count }}" @selected($perPage === $count)>{{ $count }}</option>@endforeach</select><span>entries</span></label>
        <button type="button" class="book-copies-button primary book-copies-add" id="addPhysicalBookBtn"><i class="fas fa-plus" aria-hidden="true"></i> Add Copies</button>
    </form>

    <div class="book-copies-bulk-bar" id="copyBulkBar" aria-label="Bulk copy actions">
        <div class="book-copies-selection-count" aria-live="polite"><i class="fas fa-check-square" aria-hidden="true"></i> <span id="copySelectionCount">0 copies selected</span></div>
        <label class="book-copies-bulk-select" for="copyBulkAction">
            <span class="book-copies-sr-only">Bulk action</span>
            <select class="book-copies-control" id="copyBulkAction">
                <option value="">Bulk Actions</option>
                <option value="delete_selected">Delete Selected</option>
                <option value="delete_available">Delete Available Copies</option>
                <option value="delete_damaged">Delete Damaged Copies</option>
                <option value="delete_lost">Delete Lost Copies</option>
                <option value="delete_all_eligible">Delete All Eligible Copies</option>
            </select>
        </label>
        <button type="button" class="book-copies-button danger" id="applyCopyBulkAction" disabled>Apply</button>
        <button type="button" class="book-copies-button" id="clearCopySelection" disabled>Clear Selection</button>
        <span class="book-copies-bulk-note">Borrowed and historically protected copies always remain.</span>
    </div>

    <template id="copyTableSkeleton"><x-table-skeleton :rows="5" :columns="11" label="Loading copies..." /></template>

    <div class="book-copies-table-wrap">
        <table class="book-copies-table" id="copyTable">
            <thead><tr><th class="book-copies-select-column"><label class="book-copies-select-all-label"><input type="checkbox" id="selectAllCopies" aria-label="Select all eligible copies on this page"><span>Select All</span></label></th><th>Accession No.</th><th>Entry Date</th><th>Book Type</th><th>Status</th><th>Borrowed By</th><th>Shelf</th><th>Condition</th><th>Price</th><th>Remarks</th><th>Actions</th></tr></thead>
            <tbody id="copyTableBody">
                @include('shared.book-copies.rows')
            </tbody>
        </table>
        <div id="copyPagination">@include('shared.admin-table-pagination', ['paginator' => $copies])</div>
    </div>
</div>
@include('partials.physical-book-modal', ['physicalFixedBook' => $book, 'physicalModalTitle' => 'Add Copies', 'physicalModalDescription' => 'Generate and save borrowing and reference copies for this book.'])
@include('shared.action-feedback.markup')
