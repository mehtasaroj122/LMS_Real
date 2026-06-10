@php
    $config = $studentProfileConfig;
    $status = strtolower((string) ($student->user?->status ?? 'inactive'));
    $fineActionsEnabled = ($config['features']['fineActions'] ?? false) === true;
    $studentRequestRows = $studentRequests ?? [];
    $avatar = \App\Support\ProfilePhoto::resolveUrl($student->user?->profile_photo);
    $initials = collect(explode(' ', trim((string) ($student->user?->name ?? 'Student'))))
        ->filter()
        ->take(2)
        ->map(fn($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
@endphp

<div class="student-profile-page" id="studentProfileRoot" data-student-id="{{ $student->id }}"
    data-student-status="{{ $status }}">
    <div class="student-profile-header">
        <a href="{{ $config['routes']['back'] }}" class="student-back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M19 12H5" />
                <path d="m12 19-7-7 7-7" />
            </svg>
            <span>Back to Students</span>
        </a>

        <div class="student-header-copy">
            <h1>Student Details</h1>
            <p>Review issued books, fines, activity, and library privileges.</p>
        </div>
    </div>

    <div class="student-profile-grid">
        <aside class="student-profile-card">
            <div class="student-profile-top">
                <div class="student-profile-avatar">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="{{ $student->user?->name }}">
                    @else
                        {{ $initials ?: 'ST' }}
                    @endif
                </div>

                <div class="student-profile-title">
                    <h2>{{ $student->user?->name ?? 'Unknown Student' }}</h2>
                    <div class="student-id-card">
                        <span class="student-id-label">Student ID</span>
                        <span class="student-id-value">{{ $student->roll_no ?? 'N/A' }}</span>
                    </div>

                    <div class="student-badge-row">
                        <span class="student-chip status-{{ $status }}"
                            id="studentStatusBadge">{{ ucfirst($status) }}</span>
                        <span class="student-chip role">{{ ucfirst($student->user?->role ?? 'student') }}</span>
                    </div>
                </div>
            </div>

            @if (($config['features']['toggleStatus'] ?? false) === true)
                <button type="button" id="studentStatusActionBtn"
                    class="student-account-action status-{{ $status }}">
                    <span>{{ $status === 'active' ? 'Deactivate Account' : 'Activate Account' }}</span>
                </button>
            @endif

            <div class="student-info-list">
                <div class="student-info-item">
                    <span class="student-info-label">Email</span>
                    <span class="student-info-value">{{ $student->user?->email ?? 'N/A' }}</span>
                </div>
                <div class="student-info-item">
                    <span class="student-info-label">Phone</span>
                    <span class="student-info-value">{{ $student->user?->phone ?? 'N/A' }}</span>
                </div>
                <div class="student-info-item">
                    <span class="student-info-label">Gender</span>
                    <span
                        class="student-info-value">{{ $student->user?->gender ? ucfirst($student->user->gender) : 'N/A' }}</span>
                </div>
                <div class="student-info-item">
                    <span class="student-info-label">Department</span>
                    <span class="student-info-value">{{ $student->department?->name ?? 'N/A' }}</span>
                </div>
                <div class="student-info-item">
                    <span class="student-info-label">Semester</span>
                    <span class="student-info-value">{{ $student->semester ?? 'N/A' }}</span>
                </div>
                <div class="student-info-item">
                    <span class="student-info-label">Batch</span>
                    <span class="student-info-value">{{ $student->batch ?? 'N/A' }}</span>
                </div>
            </div>

            <div class="student-system-grid">
                <div class="student-system-card">
                    <span class="student-info-label">Last Login</span>
                    <span
                        class="student-info-value">{{ optional($student->user?->last_login_at)->format('M d, Y h:i A') ?? 'Never' }}</span>
                </div>
            </div>
        </aside>

        <section class="student-pane-stack">
            <div class="student-summary-grid">
                <div class="student-summary-card">
                    <span class="student-summary-label">Total Issued</span>
                    <span class="student-summary-value">{{ $studentSummary['totalIssued'] ?? 0 }}</span>
                </div>
                <div class="student-summary-card">
                    <span class="student-summary-label">Currently Issued</span>
                    <span class="student-summary-value">{{ $studentSummary['currentlyIssued'] ?? 0 }}</span>
                </div>
                <div class="student-summary-card">
                    <span class="student-summary-label">Overdue</span>
                    <span class="student-summary-value">{{ $studentSummary['overdueCount'] ?? 0 }}</span>
                </div>
                <div class="student-summary-card">
                    <span class="student-summary-label">Pending Fine</span>
                    <span class="student-summary-value"
                        id="studentPendingFineValue">₹{{ number_format((float) ($studentSummary['pendingFineTotal'] ?? 0), 2) }}</span>
                </div>
                <div class="student-summary-card">
                    <span class="student-summary-label">Last Activity</span>
                    <span
                        class="student-summary-value">{{ $studentSummary['lastActivity'] ?? 'No activity yet' }}</span>
                </div>
            </div>

            <div class="student-pane student-pane-fixed student-books-pane">
                <div class="student-pane-header">
                    <div>
                        <h3>Issued Books</h3>
                        <p>Search and review the student's circulation status.</p>
                    </div>
                </div>

                <div class="search-filter-container student-profile-toolbar"
                    aria-label="Issued books search and filters">
                    <div class="search-box student-profile-search-box">
                        <div class="search-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <input type="text" id="studentBookSearch" class="search-input"
                            placeholder="Search by title, author, or ISBN..." aria-label="Search issued books">
                    </div>

                    <div class="filters-container student-profile-filters">
                        <select id="studentBookStatusFilter" class="filter-select"
                            aria-label="Filter issued books by status">
                            <option value="all">All Status</option>
                            <option value="issued">Issued</option>
                            <option value="overdue">Overdue</option>
                            <option value="returned">Returned</option>
                        </select>
                        <select id="studentBookSortFilter" class="filter-select" aria-label="Sort issued books">
                            <option value="issue-desc">Newest First</option>
                            <option value="issue-asc">Oldest First</option>
                            <option value="title-asc">Title A-Z</option>
                            <option value="title-desc">Title Z-A</option>
                            <option value="fine-desc">Highest Fine</option>
                        </select>
                        <button type="button" id="studentBookResetFiltersBtn" class="student-toolbar-reset"
                            aria-label="Reset issued book filters">
                            <i class="fas fa-rotate-left"></i>
                            Reset
                        </button>
                    </div>

                    <label class="admin-table-entries-control student-profile-entries-control"
                        for="studentBookEntries">
                        <span>Show</span>
                        <select id="studentBookEntries" class="admin-table-entries-select"
                            aria-label="Select issued book entries per page">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>entries</span>
                    </label>
                </div>

                <div class="student-pane-scroll student-pane-scroll-table student-books-table-panel">
                    <div class="student-table-scroller student-books-table-scroller">
                        <table class="student-data-table student-books-table">
                            <thead>
                                <tr>
                                    <th scope="col">Book</th>
                                    <th scope="col">ISBN</th>
                                    <th scope="col">Issue Date</th>
                                    <th scope="col">Due Date</th>
                                    <th scope="col">Return Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Fine</th>
                                </tr>
                            </thead>
                            <tbody id="studentBooksTableBody"></tbody>
                        </table>
                    </div>
                    <div id="studentBooksEmptyState" class="student-empty-card student-pane-empty" hidden>
                        <h3>No issued books found</h3>
                        <p>Try adjusting the search or status filter to locate a different borrowing record.</p>
                    </div>
                </div>

                <div class="admin-table-pagination student-profile-pagination">
                    <div class="admin-table-pagination-meta">
                        <div class="admin-table-pagination-summary" id="studentBookSummary">Showing 0 books</div>
                        <div class="admin-table-pagination-page" id="studentBookPageInfo">Page 1 of 1</div>
                    </div>
                    <div class="admin-table-pagination-nav" id="studentBookPagination"
                        aria-label="Issued books pagination"></div>
                </div>
            </div>
        </section>
    </div>

    <div class="student-profile-lower-grid">
        <section class="student-pane student-pane-fixed student-fines-pane">
            <div class="student-pane-header">
                <div>
                    <h3>Fine Overview</h3>
                    <p>
                        {{ $fineActionsEnabled
                            ? 'Review fine history and use quick actions for payments, waivers, and student email updates.'
                            : 'Read-only summary here. Use Fine Management for updates.' }}
                    </p>
                </div>
                @if (($config['features']['fineManagementLink'] ?? false) === true)
                    <a href="{{ $config['routes']['fineIndex'] }}" class="student-inline-link">Open Fine
                        Management</a>
                @endif
            </div>

            <div class="search-filter-container student-profile-toolbar"
                aria-label="Fine overview search and filters">
                <div class="search-box student-profile-search-box">
                    <div class="search-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" id="studentFineSearch" class="search-input"
                        placeholder="Search by book title or amount..." aria-label="Search fines">
                </div>

                <div class="filters-container student-profile-filters">
                    <select id="studentFineStatusFilter" class="filter-select" aria-label="Filter fines by status">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="waived">Waived</option>
                    </select>
                    <button type="button" id="studentFineResetFiltersBtn" class="student-toolbar-reset"
                        aria-label="Reset fine filters">
                        <i class="fas fa-rotate-left"></i>
                        Reset
                    </button>
                </div>

                <label class="admin-table-entries-control student-profile-entries-control" for="studentFineEntries">
                    <span>Show</span>
                    <select id="studentFineEntries" class="admin-table-entries-select"
                        aria-label="Select fine entries per page">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </label>
            </div>

            <div class="student-pane-scroll student-pane-scroll-table student-fines-table-panel">
                <div class="student-table-scroller student-fine-table-scroller">
                    <table class="student-data-table student-fine-table">
                        <thead>
                            <tr>
                                <th scope="col">Book</th>
                                <th scope="col">Due Date</th>
                                <th scope="col">Days Late</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Status</th>
                                @if ($fineActionsEnabled)
                                    <th scope="col">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="studentFinesTableBody"></tbody>
                    </table>
                </div>
                <div id="studentFinesEmptyState" class="student-empty-card student-pane-empty" hidden>
                    <h3>No fines found</h3>
                    <p>Try adjusting the search or status filter to locate a different fine record.</p>
                </div>
            </div>

            <div class="admin-table-pagination student-profile-pagination">
                <div class="admin-table-pagination-meta">
                    <div class="admin-table-pagination-summary" id="studentFineSummary">Showing 0 fines</div>
                    <div class="admin-table-pagination-page" id="studentFinePageInfo">Page 1 of 1</div>
                </div>
                <div class="admin-table-pagination-nav" id="studentFinePagination"
                    aria-label="Fine overview pagination"></div>
            </div>
        </section>

        <section class="student-pane student-privileges-pane">
            <div class="student-pane-header">
                <div>
                    <h3>Library Privileges</h3>
                    <p>Review effective limits here while admin-only changes remain restricted.</p>
                </div>
            </div>

            <div class="student-privilege-grid" id="studentPrivilegeGrid">
                @foreach ($studentPrivileges['items'] ?? [] as $item)
                    <div class="student-privilege-card">
                        <span class="student-privilege-label">{{ $item['label'] }}</span>
                        <span class="student-privilege-value">{{ $item['value'] }}</span>
                        <div class="student-privilege-meta">
                            <span
                                class="student-privilege-source {{ strtolower($item['source'] ?? 'default') }}">{{ $item['source'] ?? 'Default' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <div class="student-profile-bottom-grid">
        <section class="student-pane student-pane-fixed">
            <div class="student-pane-header">
                <div>
                    <h3>Recent Activity</h3>
                    <p>Recent circulation and account history for this student.</p>
                </div>
            </div>

            <div class="student-pane-scroll student-pane-scroll-activity">
                <div class="student-activity-list" id="studentActivityTimeline"></div>
                <div class="student-activity-more-row">
                    <button type="button" class="student-pane-action-btn student-activity-more-btn"
                        id="studentActivityShowMoreBtn" hidden>
                        Show More
                    </button>
                </div>
                <div id="studentActivityEmptyState" class="student-empty-card student-pane-empty" hidden>
                    <h3>No activity yet</h3>
                    <p>Activity entries will appear here as this student's account is updated or used in the library
                        workflow.</p>
                </div>
            </div>

            <div class="student-pane-footer">
                <span class="student-pane-meta" id="studentActivitySummary">Showing 0 activities</span>
            </div>
        </section>

        <section class="student-pane student-pane-fixed">
            <div class="student-pane-header">
                <div>
                    <h3>Book Requests</h3>
                    <p>Latest request history for this student.</p>
                </div>
            </div>

            <div class="student-request-toolbar">
                <label class="admin-table-entries-control student-profile-entries-control"
                    for="studentRequestEntries">
                    <span>Show</span>
                    <select id="studentRequestEntries" class="admin-table-entries-select"
                        aria-label="Select book request entries per page">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>entries</span>
                </label>
            </div>

            <div class="student-pane-scroll student-pane-scroll-table student-request-table-panel">
                <div class="student-table-scroller student-request-table-scroller">
                    <table class="student-data-table student-request-table">
                        <thead>
                            <tr>
                                <th scope="col">Book</th>
                                <th scope="col">Requested</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody id="studentRequestTableBody"></tbody>
                    </table>
                </div>
                <div id="studentRequestEmptyState" class="student-empty-card student-pane-empty" hidden>
                    <h3>No book requests</h3>
                    <p>This student has not submitted any book requests yet.</p>
                </div>
            </div>

            <div class="admin-table-pagination student-profile-pagination">
                <div class="admin-table-pagination-meta">
                    <div class="admin-table-pagination-summary" id="studentRequestSummary">Showing 0 requests</div>
                    <div class="admin-table-pagination-page" id="studentRequestPageInfo">Page 1 of 1</div>
                </div>
                <div class="admin-table-pagination-nav" id="studentRequestPagination"
                    aria-label="Book request pagination"></div>
            </div>
        </section>
    </div>

    @if ($fineActionsEnabled)
        <div id="studentFineConfirmModal" class="student-modal" hidden aria-hidden="true" role="dialog"
            aria-modal="true" aria-labelledby="studentFineConfirmTitle">
            <button type="button" class="student-modal-backdrop" data-student-modal-close="studentFineConfirmModal"
                aria-label="Close fine action dialog"></button>

            <div class="student-modal-dialog">
                <div class="student-modal-header">
                    <div id="studentFineConfirmIcon" class="student-modal-icon"></div>

                    <div class="student-modal-copy">
                        <h3 id="studentFineConfirmTitle">Confirm Action</h3>
                        <p id="studentFineConfirmMessage">Review this fine action before continuing.</p>
                    </div>
                </div>

                <p id="studentFineConfirmDetail" class="student-modal-detail"></p>

                <div class="student-modal-actions">
                    <button type="button" class="student-modal-btn secondary"
                        data-student-modal-close="studentFineConfirmModal">
                        Cancel
                    </button>
                    <button type="button" id="studentFineConfirmSubmit" class="student-modal-btn primary">
                        Continue
                    </button>
                </div>
            </div>
        </div>

        <div id="studentFineWaiveModal" class="student-modal" hidden aria-hidden="true" role="dialog"
            aria-modal="true" aria-labelledby="studentFineWaiveTitle">
            <button type="button" class="student-modal-backdrop" data-student-modal-close="studentFineWaiveModal"
                aria-label="Close fine waiver dialog"></button>

            <div class="student-modal-dialog">
                <div class="student-modal-header">
                    <div class="student-modal-icon waive">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>

                    <div class="student-modal-copy">
                        <h3 id="studentFineWaiveTitle">Waive Fine</h3>
                        <p id="studentFineWaiveDetail">Add a short reason before waiving this fine.</p>
                    </div>
                </div>

                <label for="studentFineWaiveReason" class="student-modal-field-label">Waiver Reason</label>
                <textarea id="studentFineWaiveReason" class="student-modal-textarea" rows="4" maxlength="500"
                    placeholder="Explain why this fine should be waived..." aria-describedby="studentFineWaiveError"></textarea>
                <p class="student-modal-help">This note will be stored with the fine and used for the waiver update.
                </p>
                <p id="studentFineWaiveError" class="student-modal-error" hidden>Please enter a reason for waiving
                    this fine.</p>

                <div class="student-modal-actions">
                    <button type="button" class="student-modal-btn secondary"
                        data-student-modal-close="studentFineWaiveModal">
                        Cancel
                    </button>
                    <button type="button" id="studentFineWaiveSubmit" class="student-modal-btn primary">
                        Waive Fine
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div id="studentProfileToastContainer" class="student-toast-container" aria-live="polite" aria-atomic="true">
    </div>
    <div id="studentProfileLiveRegion" class="sr-only" aria-live="polite" aria-atomic="true"></div>
</div>
