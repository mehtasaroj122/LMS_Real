@php
    $bookDetailsUrl = $bookDetailsUrl ?? route('admin.books.show', '__BOOK__');
    $bookDetailsCanManage = $bookDetailsCanManage ?? auth()->user()?->can('access-admin');
@endphp

<div id="viewBookModal" class="modal-overlay" aria-hidden="true"
     data-details-url="{{ $bookDetailsUrl }}" data-time-zone="{{ config('app.timezone') }}">
    <div class="modal book-details-panel" role="dialog" aria-modal="true" aria-labelledby="bookDetailsTitle" aria-describedby="bookDetailsSubtitle" tabindex="-1">
        <div class="modal-header book-details-header">
            <div>
                <h2 class="modal-title" id="bookDetailsTitle">Book Details</h2>
                <p id="bookDetailsSubtitle">View book information and inventory details</p>
            </div>
            <button class="modal-close-btn" id="closeViewBookModal" type="button" aria-label="Close book details">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"></path></svg>
            </button>
        </div>
        <div class="modal-body book-details-body">
            <div id="bookDetailsContent" aria-busy="false"></div>
        </div>
        <div class="modal-footer book-details-footer">
            <button class="btn btn-secondary" id="closeViewBookBtn" type="button">Close</button>
            @if($bookDetailsCanManage)
                <a class="btn btn-secondary" id="bookDetailsManageCopies" aria-disabled="true" tabindex="-1">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5"></path></svg>
                    Manage Copies
                </a>
                <button class="btn btn-primary" id="bookDetailsAddCopies" type="button" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
                    Add Physical Copies
                </button>
            @endif
        </div>
        <span class="book-details-sr-only" id="bookDetailsStatus" role="status" aria-live="polite"></span>
    </div>
</div>

<template id="bookDetailsLoadedTemplate">
    <section class="book-details-overview" aria-labelledby="bookDetailsBookTitle">
        <div class="book-details-cover">
            <div class="book-details-cover-placeholder">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M6.5 3H20v19H6.5A2.5 2.5 0 0 1 4 19.5v-14A2.5 2.5 0 0 1 6.5 3ZM8 7h8M8 11h6"></path></svg>
                <span data-book-detail="initials" aria-hidden="true"></span>
                <small>No cover image</small>
            </div>
            <img data-book-detail="cover" hidden>
        </div>
        <div class="book-details-identity">
            <h3 id="bookDetailsBookTitle" data-book-detail="title"></h3>
            <p class="book-details-author" data-book-detail="author"></p>
            <dl class="book-details-identity-meta">
                <div><dt>Category</dt><dd data-book-detail="category"></dd></div>
                <div><dt>Publisher</dt><dd data-book-detail="publisher"></dd></div>
            </dl>
            <dl class="book-details-identifiers">
                <div><dt>ISBN</dt><dd data-book-detail="isbn"></dd></div>
                <div><dt>Book ID</dt><dd data-book-detail="id"></dd></div>
            </dl>
        </div>
    </section>

    <section class="book-details-section" aria-labelledby="bookDetailsInventoryHeading">
        <h3 id="bookDetailsInventoryHeading">Inventory Overview</h3>
        <p class="book-details-section-note">Current status of individual physical copies.</p>
        <dl class="book-details-stat-grid">
            <div class="book-details-stat" data-stat="total"><dt><span>Total Copies</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5"></path></svg></dt><dd data-book-count="total"></dd></div>
            <div class="book-details-stat" data-stat="available"><dt><span>Available Copies</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 3 3 5-6"></path></svg></dt><dd data-book-count="available"></dd></div>
            <div class="book-details-stat" data-stat="issued"><dt><span>Issued Copies</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg></dt><dd data-book-count="issued"></dd></div>
            <div class="book-details-stat" data-stat="unavailable"><dt><span>Unavailable Copies</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v6m0 3h.01"></path></svg></dt><dd data-book-count="unavailable"></dd></div>
        </dl>
        <div class="book-details-copy-overview">
            <h4>Physical Copy Overview</h4>
            <p data-book-detail="copy-types"></p>
            <div class="book-details-copy-statuses" data-book-detail="copy-statuses"></div>
        </div>
    </section>

    <section class="book-details-section" aria-labelledby="bookDetailsInformationHeading">
        <div class="book-details-section-heading">
            <h3 id="bookDetailsInformationHeading">Book Information</h3>
            @if($bookDetailsCanManage)
                <button type="button" class="btn btn-secondary book-details-edit" data-book-action="edit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m16 3 5 5-12 12-6 1 1-6L16 3ZM13 6l5 5"></path></svg>
                    Edit Book
                </button>
            @endif
        </div>
        <dl class="book-details-information">
            <div><dt>Catalogue Shelf / Rack</dt><dd data-book-detail="shelf_no"></dd></div>
            <div><dt>Physical Shelf Locations</dt><dd data-book-detail="shelf_locations"></dd></div>
            <div><dt>Date Added</dt><dd data-book-detail="created_at"></dd></div>
            <div><dt>Last Updated</dt><dd data-book-detail="updated_at"></dd></div>
            <div><dt>Catalogue Condition</dt><dd class="book-details-capitalize" data-book-detail="condition"></dd></div>
        </dl>
    </section>

    <section class="book-details-section book-details-description" aria-labelledby="bookDetailsDescriptionHeading">
        <h3 id="bookDetailsDescriptionHeading">About This Book</h3>
        <p data-book-detail="description"></p>
    </section>
</template>

<template id="bookDetailsLoadingTemplate">
    <div class="book-details-skeleton" aria-hidden="true">
        <div class="book-details-overview"><div class="book-details-skeleton-cover"></div><div class="book-details-skeleton-copy"><span></span><span></span><span></span><span></span></div></div>
        <div class="book-details-stat-grid"><span></span><span></span><span></span><span></span></div>
        <div class="book-details-skeleton-copy"><span></span><span></span><span></span></div>
    </div>
    <p class="book-details-loading-label">Loading book details...</p>
</template>

<template id="bookDetailsErrorTemplate">
    <div class="book-details-error">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v6m0 3h.01"></path></svg>
        <h3>Unable to load book details. Please try again.</h3>
        <button class="btn btn-primary" type="button" data-book-action="retry">Retry</button>
    </div>
</template>
