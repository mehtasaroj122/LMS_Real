<div class="book-copies-page" id="bookCopiesPage" data-base-url="{{ url('/' . $role . '/book-copies') }}">
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
        <div class="book-copies-search"><input class="book-copies-control" id="copySearch" name="search" type="search" value="{{ $search }}" placeholder="Accession, student, ID..." aria-label="Search copies" maxlength="100"></div>
        <select class="book-copies-control" id="copyStatusFilter" name="status" aria-label="Status"><option value="all">All statuses</option>@foreach(['available' => 'Available', 'issued' => 'Issued', 'lost' => 'Lost', 'damaged' => 'Damaged'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select>
        <select class="book-copies-control" id="copyTypeFilter" name="book_type" aria-label="Book Type"><option value="all">All types</option><option value="borrowing" @selected($bookType === 'borrowing')>Borrowing</option><option value="reference" @selected($bookType === 'reference')>Reference</option></select>
        <button type="button" class="book-copies-button" id="resetCopyFilters"><i class="fas fa-rotate-left" aria-hidden="true"></i> Reset</button>
        <label class="book-copies-entries" for="copyEntriesSelect"><span>Show</span><select class="book-copies-control" id="copyEntriesSelect" name="per_page" aria-label="Show copy entries">@foreach([10, 20, 50, 100] as $count)<option value="{{ $count }}" @selected($perPage === $count)>{{ $count }}</option>@endforeach</select><span>entries</span></label>
        <button type="button" class="book-copies-button primary book-copies-add" id="addPhysicalBookBtn"><i class="fas fa-plus" aria-hidden="true"></i> Add Copies</button>
    </form>

    <template id="copyTableSkeleton"><x-table-skeleton :rows="5" :columns="10" label="Loading copies..." /></template>

    <div class="book-copies-table-wrap">
        <table class="book-copies-table" id="copyTable">
            <thead><tr><th>Accession No.</th><th>Entry Date</th><th>Book Type</th><th>Status</th><th>Borrowed By</th><th>Shelf</th><th>Condition</th><th>Price</th><th>Remarks</th><th>Actions</th></tr></thead>
            <tbody id="copyTableBody">
                @include('shared.book-copies.rows')
            </tbody>
        </table>
        <div id="copyPagination">@include('shared.admin-table-pagination', ['paginator' => $copies])</div>
    </div>
</div>
@include('partials.physical-book-modal', ['physicalFixedBook' => $book, 'physicalModalTitle' => 'Add Copies', 'physicalModalDescription' => 'Generate and save borrowing and reference copies for this book.'])
@include('shared.action-feedback.markup')
