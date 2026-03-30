@php
    $config = $bookRequestManagementConfig;
@endphp

<div class="book-request-page" id="bookRequestManagementRoot">
    <div class="request-content">
        <div class="request-page-header">
            <div class="request-page-title">
                <h1>{{ $config['labels']['pageTitle'] ?? 'Book Requests' }}</h1>
                <p>{{ $config['labels']['pageDescription'] ?? 'Manage student book requests' }}</p>
            </div>
        </div>

        <div class="request-stats-grid" aria-live="polite">
            <div class="request-stat-card pending is-loading" data-stat-card="pending">
                <div class="request-stat-header">
                    <h3 class="request-stat-title">{{ $config['labels']['stats']['pending'] ?? 'Pending' }}</h3>
                    <div class="request-stat-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                        </svg>
                    </div>
                </div>
                <p class="request-stat-value loading-line" id="pendingRequestCount">0</p>
                <div class="request-stat-meta" id="pendingRequestMeta">Queue details loading...</div>
            </div>

            <div class="request-stat-card approved is-loading" data-stat-card="approved">
                <div class="request-stat-header">
                    <h3 class="request-stat-title">{{ $config['labels']['stats']['approved'] ?? 'Approved' }}</h3>
                    <div class="request-stat-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m20 6-11 11-5-5" />
                        </svg>
                    </div>
                </div>
                <p class="request-stat-value loading-line" id="approvedRequestCount">0</p>
                <div class="request-stat-meta" id="approvedRequestMeta">Approval details loading...</div>
            </div>

            <div class="request-stat-card rejected is-loading" data-stat-card="rejected">
                <div class="request-stat-header">
                    <h3 class="request-stat-title">{{ $config['labels']['stats']['rejected'] ?? 'Rejected' }}</h3>
                    <div class="request-stat-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m18 6-12 12" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </div>
                </div>
                <p class="request-stat-value loading-line" id="rejectedRequestCount">0</p>
                <div class="request-stat-meta" id="rejectedRequestMeta">Rejection details loading...</div>
            </div>
        </div>

        <div class="request-section-header">
            <h2>{{ $config['labels']['sectionTitle'] ?? 'All Requests' }}</h2>
        </div>

        <section class="request-toolbar" aria-label="Book request search and filters">
            <div class="request-search-box">
                <span class="request-search-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                </span>
                <input
                    type="text"
                    id="requestSearchInput"
                    class="request-search-input"
                    autocomplete="off"
                    spellcheck="false"
                    placeholder="{{ $config['labels']['searchPlaceholder'] ?? 'Search by student, book, or date...' }}"
                    aria-label="Search book requests"
                >
            </div>

            <div class="request-filters">
                <div class="request-filter-field">
                    <select id="requestStatusFilter" class="request-filter-select" aria-label="Filter requests by status">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="request-filter-field">
                    <select id="requestSortFilter" class="request-filter-select" aria-label="Sort book requests">
                        <option value="date-desc">Date (Newest)</option>
                        <option value="date-asc">Date (Oldest)</option>
                        <option value="student-asc">Student (A-Z)</option>
                        <option value="book-asc">Book (A-Z)</option>
                    </select>
                </div>
            </div>

            <div class="request-toolbar-actions">
                <button type="button" id="requestResetFiltersBtn" class="request-btn" aria-label="Reset all request filters">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12a9 9 0 1 0 3-6.708L3 8" />
                        <path d="M3 3v5h5" />
                    </svg>
                    <span>{{ $config['labels']['resetButton'] ?? 'Reset' }}</span>
                </button>

                @if(($config['features']['create'] ?? true) === true)
                    <button type="button" id="createRequestBtn" class="request-btn primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        <span>{{ $config['labels']['createButton'] ?? 'Create Request' }}</span>
                    </button>
                @endif
            </div>
        </section>

        <div class="request-toolbar-meta">
            <div id="requestFilterSummary">Showing all requests</div>
            <div id="requestLastUpdated">Waiting for data...</div>
        </div>

        <div class="request-table-shell">
            <div class="request-table-container">
                <div class="request-table-wrapper" id="requestsTableWrapper" aria-busy="true">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th scope="col">Student</th>
                                <th scope="col">Book</th>
                                <th scope="col">Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Processed By</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="requestsTableBody">
                            <tr>
                                <td><span class="request-skeleton-line"></span></td>
                                <td><span class="request-skeleton-line"></span></td>
                                <td><span class="request-skeleton-line short"></span></td>
                                <td><span class="request-skeleton-line short"></span></td>
                                <td><span class="request-skeleton-line short"></span></td>
                                <td><span class="request-skeleton-line"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="requestsEmptyState" class="request-empty-state" aria-hidden="true">
                <div class="request-empty-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 6h13" />
                        <path d="M8 12h13" />
                        <path d="M8 18h13" />
                        <path d="M3 6h.01" />
                        <path d="M3 12h.01" />
                        <path d="M3 18h.01" />
                    </svg>
                </div>
                <h3>No requests found</h3>
                <p id="requestsEmptyMessage">Try adjusting your search or filters.</p>
                <div class="request-empty-actions">
                    <button type="button" class="request-btn" data-action="reset-request-filters">Clear filters</button>
                </div>
            </div>
        </div>

        <div id="requestPaginationContainer" class="request-pagination">
            <div class="request-pagination-info">
                Showing <span id="requestRecordCount">0-0</span> of <span id="requestTotalCount">0</span> requests
            </div>
            <div id="requestPaginationButtons" class="request-pagination-buttons"></div>
        </div>
    </div>

    <div id="createRequestModal" class="request-modal-backdrop" aria-hidden="true">
        <div class="request-modal-panel" role="dialog" aria-modal="true" aria-labelledby="createRequestTitle" aria-describedby="createRequestDescription">
            <div class="request-modal-header">
                <div>
                    <h3 id="createRequestTitle" class="request-modal-title">Create Book Request</h3>
                    <p id="createRequestDescription" class="request-modal-subtitle">Create a new book request for a student.</p>
                </div>
                <button type="button" class="request-modal-close" data-modal-close="createRequestModal" aria-label="Close create request dialog">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m18 6-12 12" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>

            <div class="request-modal-body">
                <div class="request-form-grid">
                    <div class="request-form-field">
                        <label class="request-label" for="studentSearch">
                            Student <span class="request-required">*</span>
                        </label>
                        <div class="request-selectbox searchable-select-container" data-select="student">
                            <input type="hidden" id="studentId" name="student_id">
                            <span class="request-selectbox-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            </span>
                            <input type="text" id="studentSearch" class="request-selectbox-input searchable-select-input" placeholder="Search for a student..." autocomplete="off" role="combobox" aria-expanded="false" aria-controls="studentDropdown">
                            <div id="studentDropdown" class="request-selectbox-dropdown searchable-select-dropdown" role="listbox">
                                @foreach($students as $student)
                                    <button type="button" class="request-selectbox-option searchable-select-option" data-value="{{ $student->id }}" data-text="{{ $student->user->name }} ({{ $student->roll_no }})" role="option">
                                        <span class="request-primary-text">{{ $student->user->name }}</span>
                                        <span class="request-secondary-text">{{ $student->roll_no }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <p id="studentFieldError" class="request-field-error" hidden>Please select a student.</p>
                    </div>

                    <div class="request-form-field">
                        <label class="request-label" for="bookSearch">
                            Book <span class="request-required">*</span>
                        </label>
                        <div class="request-selectbox searchable-select-container" data-select="book">
                            <input type="hidden" id="bookId" name="book_id">
                            <span class="request-selectbox-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                                </svg>
                            </span>
                            <input type="text" id="bookSearch" class="request-selectbox-input searchable-select-input" placeholder="Search for a book..." autocomplete="off" role="combobox" aria-expanded="false" aria-controls="bookDropdown">
                            <div id="bookDropdown" class="request-selectbox-dropdown searchable-select-dropdown" role="listbox">
                                @foreach($books as $book)
                                    <button type="button" class="request-selectbox-option searchable-select-option" data-value="{{ $book->id }}" data-text="{{ $book->title }} - {{ $book->author }}" role="option">
                                        <span class="request-primary-text">{{ $book->title }}</span>
                                        <span class="request-secondary-text">{{ $book->author }}</span>
                                        <span class="request-secondary-text">{{ $book->available_copies > 0 ? 'Available: ' . $book->available_copies : 'Out of stock' }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <p id="bookFieldError" class="request-field-error" hidden>Please select a book.</p>
                    </div>

                    <div id="requestSelectionSummary" class="request-selection-summary" aria-live="polite">
                        <h4>Selected Items</h4>
                        <p id="selectedStudentSummary" hidden><strong>Student:</strong> <span id="selectedStudentText"></span></p>
                        <p id="selectedBookSummary" hidden><strong>Book:</strong> <span id="selectedBookText"></span></p>
                    </div>
                </div>
            </div>

            <div class="request-modal-footer">
                <button type="button" class="request-modal-btn" data-modal-close="createRequestModal">Cancel</button>
                <button type="button" class="request-modal-btn primary" id="confirmCreateRequestBtn">Create Request</button>
            </div>
        </div>
    </div>

    <div id="requestActionModal" class="request-modal-backdrop" aria-hidden="true">
        <div class="request-modal-panel compact" role="dialog" aria-modal="true" aria-labelledby="requestActionTitle" aria-describedby="requestActionMessage">
            <div class="request-modal-body" style="text-align: center;">
                <div id="requestActionIcon" class="request-action-icon accept"></div>
                <h3 id="requestActionTitle" class="request-modal-title">Confirm Action</h3>
                <p id="requestActionMessage" class="request-modal-description">Please confirm this request update.</p>
                <p id="requestActionNote" class="request-modal-note"></p>
                <div id="requestActionDetail" class="request-action-detail" hidden></div>
            </div>
            <div class="request-modal-footer">
                <button type="button" class="request-modal-btn" data-modal-close="requestActionModal">Cancel</button>
                <button type="button" class="request-modal-btn primary" id="confirmRequestActionBtn">Confirm</button>
            </div>
        </div>
    </div>

    <div id="requestToastContainer" class="request-toast-container" aria-live="polite" aria-atomic="true"></div>
    <div id="requestLiveRegion" class="sr-only" aria-live="polite" aria-atomic="true"></div>
</div>
