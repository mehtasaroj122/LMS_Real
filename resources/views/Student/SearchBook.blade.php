@extends('student.layouts.app')

@section('title', 'Search Books')

@push('styles')
    <style>
        /* Search Page Specific Styles (reduced spacing) */
        .page-header {
            margin-bottom: 1rem;
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--text-primary, #0f172a);
        }

        .page-subtitle {
            font-size: 0.9375rem;
            color: var(--text-secondary, #64748b);
        }

        /* Search Bar */
        .search-container {
            width: 100%;
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e5e7eb);
            margin-bottom: 1rem;
        }

        .search-bar {
            display: grid;
            width: min(100%, 1040px);
            grid-template-columns: minmax(260px, 2.2fr) repeat(4, minmax(140px, 1fr)) auto;
            gap: 0.75rem;
            align-items: end;
        }

        .filter-field {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .search-input-group {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 0.75rem 0.5rem 2rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color, #e5e7eb);
            background-color: var(--bg-input, #ffffff);
            color: var(--text-primary, #0f172a);
            font-size: 0.875rem;
            transition: all 0.15s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color, #3b82f6);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary, #64748b);
            pointer-events: none;
            width: 1rem;
            height: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .search-icon svg {
            width: 1rem;
            height: 1rem;
        }

        .filter-select {
            width: 100%;
            padding: 0.5rem 1.75rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color, #e5e7eb);
            background-color: var(--bg-input, #ffffff);
            color: var(--text-primary, #0f172a);
            font-size: 0.875rem;
            appearance: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color, #3b82f6);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.08);
        }

        .select-wrapper {
            position: relative;
        }

        .select-arrow {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary, #64748b);
            pointer-events: none;
            width: 0.9rem;
            height: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .select-arrow svg {
            width: 0.9rem;
            height: 0.9rem;
        }

        .filter-actions {
            display: flex;
            align-items: flex-end;
        }

        .reset-filters-btn {
            height: 2.5rem;
            padding: 0 1rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color, #e5e7eb);
            background-color: var(--soft-bg, #f8fafc);
            color: var(--text-primary, #0f172a);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .reset-filters-btn:hover {
            transform: translateY(-1px);
            border-color: var(--primary-color, #3b82f6);
        }

        .dark-theme .reset-filters-btn {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        /* Results Info */
        .results-info {
            font-size: 0.875rem;
            color: var(--text-secondary, #64748b);
            margin-bottom: 1rem;
            padding: 0;
        }

        /* Books Grid */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 0.75rem;
        }

        .book-card {
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e5e7eb);
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.15s ease;
        }

        .book-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        }

        .book-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .book-header > div {
            min-height: 3.75rem;
        }

        .book-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary, #0f172a);
            margin-bottom: 0.125rem;
            line-height: 1.25;
        }

        .book-author {
            font-size: 0.8125rem;
            color: var(--text-secondary, #64748b);
        }

        .availability-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.1875rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            color: white;
            white-space: nowrap;
        }

        .availability-badge.available {
            background-color: #10b981;
        }

        .availability-badge.unavailable {
            background-color: #fcd34d;
            color: #92400e;
            font-weight: 700;
        }

        /* Book Metadata */
        .book-metadata {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .metadata-item {
            display: flex;
            flex-direction: column;
        }

        .metadata-label {
            font-size: 0.6875rem;
            color: var(--text-secondary, #64748b);
            margin-bottom: 0.125rem;
        }

        .metadata-value {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-primary, #0f172a);
        }

        .condition-value {
            text-transform: capitalize;
        }

        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.1875rem 0.375rem;
            border-radius: 0.3125rem;
            font-size: 0.6875rem;
            font-weight: 500;
            background-color: var(--soft-bg, #f1f5f9);
            color: var(--text-secondary, #64748b);
            width: fit-content;
        }

        /* Description */
        .book-description {
            font-size: 0.8125rem;
            color: var(--text-secondary, #64748b);
            line-height: 1.4;
            margin-bottom: 0.75rem;
            flex-grow: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Action Button */
        .request-btn {
            width: 100%;
            margin-top: auto;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            border: none;
            background-color: #0f172a;
            color: white;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            transition: all 0.15s ease;
        }

        .request-btn svg {
            width: 0.95rem;
            height: 0.95rem;
            flex-shrink: 0;
        }

        .request-btn:hover {
            background-color: #1e293b;
            transform: translateY(-1px);
        }

        .request-btn:active {
            transform: translateY(0);
        }

        .request-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        /* Dark Theme - Button Override */
        .dark-theme .request-btn {
            background-color: #3b82f6;
            color: white;
        }

        .dark-theme .request-btn:hover {
            background-color: #2563eb;
        }

        /* Show More Button */
        .show-more-btn {
            padding: 0.75rem 1.5rem;
            background-color: #0f172a;
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.15s ease;
        }

        .show-more-btn:hover {
            background-color: #1e293b;
            transform: translateY(-1px);
        }

        .show-more-btn:active {
            transform: translateY(0);
        }

        /* Dark Theme - Show More Button */
        .dark-theme .show-more-btn {
            background-color: #3b82f6;
        }

        .dark-theme .show-more-btn:hover {
            background-color: #2563eb;
        }

        /* Theme Variables */
        .light-theme {
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --soft-bg: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --bg-input: #ffffff;
            --primary-color: #3b82f6;
        }

        .dark-theme {
            --card-bg: #1e293b;
            --border-color: #334155;
            --soft-bg: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --bg-input: #0f172a;
            --primary-color: #60a5fa;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-bar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 0.65rem;
            }

            .books-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 0.5rem;
            }

            .book-metadata {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 1.25rem;
            }

            .search-container {
                width: 100%;
                padding: 0.75rem;
            }

            .filter-field:first-child {
                grid-column: 1 / -1;
            }

            .filter-actions {
                grid-column: 1 / -1;
            }

            .reset-filters-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .search-bar {
                grid-template-columns: 1fr;
            }

            .filter-field:first-child,
            .filter-actions {
                grid-column: auto;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .book-card {
                padding: 0.75rem;
            }
        }

        /* Modal Popup Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            margin: 50% auto;
            padding: 2rem;
            border-radius: 0.75rem;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .modal-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .modal-icon-warning {
            background-color: #fef3c7;
            color: #d97706;
        }

        .modal-icon-success {
            background-color: #d1fae5;
            color: #059669;
        }

        .modal-icon-info {
            background-color: #dbeafe;
            color: #0284c7;
        }

        .modal-icon-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .modal-body {
            margin-bottom: 1.5rem;
        }

        .modal-message {
            font-size: 0.9375rem;
            color: #475569;
            line-height: 1.6;
            margin: 0;
        }

        .modal-book-title {
            font-weight: 700;
            color: #0f172a;
        }

        .modal-footer {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 0.625rem 1.5rem;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .modal-btn-ok {
            background-color: #0f172a;
            color: white;
        }

        .modal-btn-ok:hover {
            background-color: #1e293b;
            transform: translateY(-1px);
        }

        .modal-btn-success {
            background-color: #059669;
        }

        .modal-btn-success:hover {
            background-color: #047857;
        }

        .modal-btn-info {
            background-color: #0284c7;
        }

        .modal-btn-info:hover {
            background-color: #0369a1;
        }

        .modal-btn-warning {
            background-color: #d97706;
        }

        .modal-btn-warning:hover {
            background-color: #b45309;
        }

        .modal-btn-danger {
            background-color: #dc2626;
        }

        .modal-btn-danger:hover {
            background-color: #b91c1c;
        }

        .modal-btn-ok:active {
            transform: translateY(0);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Dark Theme Support */
        .dark-theme .modal-content {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-color: #334155;
        }

        .dark-theme .modal-title {
            color: #f1f5f9;
        }

        .dark-theme .modal-message {
            color: #cbd5e1;
        }

        .dark-theme .modal-book-title {
            color: #f8fafc;
        }
    </style>
@endpush

@section('content')
    <div class="search-page">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Search Books</h1>
            <p class="page-subtitle">Browse and search library collection</p>
        </div>

        <!-- Search and Filter Bar -->
        <div class="search-container">
            <form method="GET" action="{{ route('student.search') }}" id="searchForm" class="search-bar">
                <div class="filter-field">
                    <div class="search-input-group">
                        <span class="search-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="m20 20-3.5-3.5"></path>
                            </svg>
                        </span>
                    <input type="text" name="q" class="search-input"
                        placeholder="Search by title, author, ISBN, publisher, or shelf no..." value="{{ $search }}" id="searchInput">
                    </div>
                </div>

                <div class="filter-field">
                    <div class="select-wrapper">
                        <select name="category" class="filter-select" id="categoryFilter">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $selectedCategory == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <span class="select-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="filter-field">
                    <div class="select-wrapper">
                        <select name="availability" class="filter-select" id="availabilityFilter">
                            <option value="">All Availability</option>
                            <option value="available" {{ ($selectedAvailability ?? '') === 'available' ? 'selected' : '' }}>Available Only</option>
                            <option value="unavailable" {{ ($selectedAvailability ?? '') === 'unavailable' ? 'selected' : '' }}>Unavailable Only</option>
                        </select>
                        <span class="select-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="filter-field">
                    <div class="select-wrapper">
                        <select name="condition" class="filter-select" id="conditionFilter">
                            <option value="">All Conditions</option>
                            <option value="new" {{ ($selectedCondition ?? '') === 'new' ? 'selected' : '' }}>New</option>
                            <option value="good" {{ ($selectedCondition ?? '') === 'good' ? 'selected' : '' }}>Good</option>
                            <option value="damaged" {{ ($selectedCondition ?? '') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                        </select>
                        <span class="select-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="filter-field">
                    <div class="select-wrapper">
                        <select name="sort" class="filter-select" id="sortFilter">
                            <option value="title_asc" {{ ($selectedSort ?? 'title_asc') === 'title_asc' ? 'selected' : '' }}>Title A-Z</option>
                            <option value="title_desc" {{ ($selectedSort ?? '') === 'title_desc' ? 'selected' : '' }}>Title Z-A</option>
                            <option value="author_asc" {{ ($selectedSort ?? '') === 'author_asc' ? 'selected' : '' }}>Author A-Z</option>
                            <option value="copies_desc" {{ ($selectedSort ?? '') === 'copies_desc' ? 'selected' : '' }}>Most Available</option>
                            <option value="recent" {{ ($selectedSort ?? '') === 'recent' ? 'selected' : '' }}>Recently Added</option>
                        </select>
                        <span class="select-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m6 9 6 6 6-6"></path>
                            </svg>
                        </span>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="button" class="reset-filters-btn" id="resetFiltersBtn">Reset</button>
                </div>
            </form>
        </div>

        <!-- Search Result Info -->
        <div class="results-info">Found <span id="bookCount">{{ $books->count() }}</span> books</div>

        <!-- Books Grid -->
        <div class="books-grid" id="booksContainer">
            @forelse($books as $book)
                @php
                    $normalizedDescription = trim(preg_replace('/\s+/', ' ', (string) ($book->description ?? '')));
                    $displayDescription = in_array(\Illuminate\Support\Str::lower($normalizedDescription), [
                        '',
                        'comprehensive learning resource',
                        'a comprehensive learning resource',
                    ], true)
                        ? null
                        : \Illuminate\Support\Str::limit($normalizedDescription, 120);
                @endphp
                <div class="book-card">
                    <div class="book-cover">
                        @if ($book->cover_image)
                            <img src="{{ str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;">
                        @else
                            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ffffff; font-size: 72px; font-weight: 700;">
                                {{ strtoupper(substr($book->title, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="book-header">
                        <div>
                            <h3 class="book-title">{{ $book->title }}</h3>
                            <p class="book-author">{{ $book->author }}</p>
                        </div>
                        <span
                            class="availability-badge {{ $book->status == 'available' ? 'available' : 'unavailable' }}">{{ $book->status == 'available' ? 'Available' : 'Unavailable' }}</span>
                    </div>
                    <div class="book-metadata">
                        <div class="metadata-item">
                            <span class="metadata-label">Category</span>
                            <span class="category-badge">{{ $book->category->name ?? 'N/A' }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Publisher</span>
                            <span class="metadata-value">{{ $book->publisher }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Condition</span>
                            <span class="metadata-value condition-value">{{ $book->condition ?? '-' }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">ISBN</span>
                            <span class="metadata-value">{{ $book->isbn }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Location</span>
                            <span class="metadata-value">{{ $book->shelf_no ?? '-' }}</span>
                        </div>
                        <div class="metadata-item">
                            <span class="metadata-label">Available Copies</span>
                            <span class="metadata-value">{{ $book->available_copies }} / {{ $book->total_copies }}</span>
                        </div>
                    </div>
                    @if ($displayDescription)
                        <p class="book-description">{{ $displayDescription }}</p>
                    @endif
                    <button class="request-btn" data-book-id="{{ $book->id }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                        <span>Request Book</span>
                    </button>
                </div>
            @empty
                <p>No books found.</p>
            @endforelse
        </div>

        <!-- Show More Button -->
        <div style="text-align: center; margin-top: 2rem;">
            <button id="showMoreBtn" class="show-more-btn" style="display: none;">
                Show More Books
            </button>
        </div>
    </div>

    <!-- Unavailable Book Modal -->
    <div id="unavailableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon modal-icon-warning">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                    </svg>
                </div>
                <h2 class="modal-title">Book Unavailable</h2>
            </div>
            <div class="modal-body">
                <p class="modal-message">
                    Sorry! The book "<span id="bookTitleInModal"></span>" is currently unavailable. All copies are currently checked out. Please check back later or request another book from our collection.
                </p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-ok" id="closeModalBtn">OK</button>
            </div>
        </div>
    </div>

    <!-- Success Request Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon modal-icon-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />
                    </svg>
                </div>
                <h2 class="modal-title">Request Submitted</h2>
            </div>
            <div class="modal-body">
                <p class="modal-message" id="successMessage">
                    Your request has been submitted successfully.
                </p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-ok modal-btn-success" id="closeSuccessModalBtn">OK</button>
            </div>
        </div>
    </div>

    <!-- Request State Modal -->
    <div id="requestStateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon modal-icon-info" id="requestStateIcon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 12h1v4h1" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" />
                    </svg>
                </div>
                <h2 class="modal-title" id="requestStateTitle">Request Update</h2>
            </div>
            <div class="modal-body">
                <p class="modal-message" id="requestStateMessage"></p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-ok modal-btn-info" id="closeRequestStateModalBtn">OK</button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon modal-icon-danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.01" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>
                </div>
                <h2 class="modal-title">Request Error</h2>
            </div>
            <div class="modal-body">
                <p class="modal-message" id="errorMessage">
                    An error occurred while processing your request. Please try again later.
                </p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-ok modal-btn-danger" id="closeErrorModalBtn">OK</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const availabilityFilter = document.getElementById('availabilityFilter');
            const conditionFilter = document.getElementById('conditionFilter');
            const sortFilter = document.getElementById('sortFilter');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const booksContainer = document.getElementById('booksContainer');
            const bookCount = document.getElementById('bookCount');
            const showMoreBtn = document.getElementById('showMoreBtn');
            const searchUrl = '{{ route('student.search') }}';

            const BOOKS_PER_PAGE = 12;
            const genericDescriptions = [
                'comprehensive learning resource',
                'a comprehensive learning resource'
            ];
            let allBooks = [];
            let displayedBooks = 0;

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function formatConditionLabel(condition) {
                const normalized = String(condition ?? '').trim().toLowerCase();

                if (!normalized) {
                    return '-';
                }

                return normalized.charAt(0).toUpperCase() + normalized.slice(1);
            }

            function getDisplayDescription(book) {
                const rawDescription = String(book.display_description ?? book.description ?? '')
                    .replace(/\s+/g, ' ')
                    .trim();

                if (!rawDescription || genericDescriptions.includes(rawDescription.toLowerCase())) {
                    return '';
                }

                return rawDescription;
            }

            function getRequestButtonIconSvg(type) {
                const icons = {
                    plus: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                    `,
                    check: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5" />
                        </svg>
                    `
                };

                return icons[type] || icons.plus;
            }

            function getModalIconSvg(type) {
                const icons = {
                    info: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 12h1v4h1" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" />
                        </svg>
                    `,
                    pending: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" />
                        </svg>
                    `,
                    approved: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v6c0 5 3.5 8 7 9 3.5-1 7-4 7-9V6l-7-3Z" />
                        </svg>
                    `,
                    borrowed: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                        </svg>
                    `,
                    issued: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 4h4v4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14 18 6" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H12v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
                        </svg>
                    `,
                    error: `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.01" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                        </svg>
                    `
                };

                return icons[type] || icons.info;
            }

            function getRequestStateConfig(reason) {
                const states = {
                    already_borrowed: {
                        title: 'Already Borrowed',
                        tone: 'warning',
                        icon: 'borrowed'
                    },
                    request_pending: {
                        title: 'Request Pending',
                        tone: 'info',
                        icon: 'pending'
                    },
                    request_approved: {
                        title: 'Request Approved',
                        tone: 'success',
                        icon: 'approved'
                    },
                    request_issued: {
                        title: 'Already Issued',
                        tone: 'warning',
                        icon: 'issued'
                    },
                    request_exists: {
                        title: 'Active Request Found',
                        tone: 'info',
                        icon: 'info'
                    }
                };

                return states[reason] || {
                    title: 'Request Update',
                    tone: 'info',
                    icon: 'info'
                };
            }

            function getCurrentFilters() {
                const params = new URLSearchParams();

                if (searchInput.value.trim()) params.append('q', searchInput.value.trim());
                if (categoryFilter.value) params.append('category', categoryFilter.value);
                if (availabilityFilter.value) params.append('availability', availabilityFilter.value);
                if (conditionFilter.value) params.append('condition', conditionFilter.value);
                if (sortFilter.value && sortFilter.value !== 'title_asc') params.append('sort', sortFilter.value);

                return params;
            }

            function syncBrowserUrl(params) {
                const nextUrl = params.toString() ? `${searchUrl}?${params.toString()}` : searchUrl;
                window.history.replaceState({}, '', nextUrl);
            }

            function renderBookCard(book) {
                const description = getDisplayDescription(book);

                return `
                    <div class="book-card">
                        <div class="book-header">
                            <div>
                                <h3 class="book-title">${escapeHtml(book.title)}</h3>
                                <p class="book-author">${escapeHtml(book.author || 'Unknown author')}</p>
                            </div>
                            <span class="availability-badge ${book.status === 'available' ? 'available' : 'unavailable'}">${book.status === 'available' ? 'Available' : 'Unavailable'}</span>
                        </div>
                        <div class="book-metadata">
                            <div class="metadata-item">
                                <span class="metadata-label">Category</span>
                                <span class="category-badge">${escapeHtml(book.category?.name || 'N/A')}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Publisher</span>
                                <span class="metadata-value">${escapeHtml(book.publisher || '-')}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Condition</span>
                                <span class="metadata-value condition-value">${escapeHtml(formatConditionLabel(book.condition))}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">ISBN</span>
                                <span class="metadata-value">${escapeHtml(book.isbn || '-')}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Location</span>
                                <span class="metadata-value">${escapeHtml(book.shelf_no || '-')}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Available Copies</span>
                                <span class="metadata-value">${escapeHtml(book.available_copies)} / ${escapeHtml(book.total_copies)}</span>
                            </div>
                        </div>
                        ${description ? `<p class="book-description">${escapeHtml(description)}</p>` : ''}
                        <button class="request-btn" data-book-id="${escapeHtml(book.id)}">
                            ${getRequestButtonIconSvg('plus')}
                            <span>Request Book</span>
                        </button>
                    </div>
                `;
            }

            // Function to render paginated book cards
            function renderPaginatedBooks(booksToRender) {
                if (booksToRender.length === 0) {
                    booksContainer.innerHTML = '<p>No books found.</p>';
                    showMoreBtn.style.display = 'none';
                    return;
                }

                const booksHTML = booksToRender.map(renderBookCard).join('');

                booksContainer.innerHTML = booksHTML;

                // Show/hide "Show More" button
                if (booksToRender.length < allBooks.length) {
                    showMoreBtn.style.display = 'block';
                } else {
                    showMoreBtn.style.display = 'none';
                }

                // Re-attach request button listeners
                attachRequestButtonListeners();
            }

            // Function to display initial 12 books
            function displayInitialBooks() {
                displayedBooks = BOOKS_PER_PAGE;
                renderPaginatedBooks(allBooks.slice(0, BOOKS_PER_PAGE));
            }

            // Function to load more books
            function loadMoreBooks() {
                const newDisplayCount = displayedBooks + BOOKS_PER_PAGE;
                displayedBooks = newDisplayCount;
                renderPaginatedBooks(allBooks.slice(0, displayedBooks));
            }

            // Function to show unavailable modal
            function showUnavailableModal(bookTitle) {
                const modal = document.getElementById('unavailableModal');
                const bookTitleSpan = document.getElementById('bookTitleInModal');
                bookTitleSpan.textContent = bookTitle;
                modal.classList.add('show');
            }

            // Function to close unavailable modal
            function closeUnavailableModal() {
                const modal = document.getElementById('unavailableModal');
                modal.classList.remove('show');
            }

            // Function to show success modal
            function showSuccessModal(bookTitle, nextStep = '') {
                const modal = document.getElementById('successModal');
                const successMessage = document.getElementById('successMessage');
                const messageParts = [
                    `Your request for "<span class="modal-book-title">${escapeHtml(bookTitle)}</span>" has been submitted successfully.`
                ];

                if (nextStep) {
                    messageParts.push(escapeHtml(nextStep));
                }

                successMessage.innerHTML = messageParts.join('<br>');
                modal.classList.add('show');
            }

            // Function to close success modal
            function closeSuccessModal() {
                const modal = document.getElementById('successModal');
                modal.classList.remove('show');
            }

            function showRequestStateModal(bookTitle, reason, message) {
                const modal = document.getElementById('requestStateModal');
                const icon = document.getElementById('requestStateIcon');
                const title = document.getElementById('requestStateTitle');
                const messageElement = document.getElementById('requestStateMessage');
                const closeButton = document.getElementById('closeRequestStateModalBtn');
                const config = getRequestStateConfig(reason);

                icon.className = `modal-icon modal-icon-${config.tone}`;
                icon.innerHTML = getModalIconSvg(config.icon);
                title.textContent = config.title;
                closeButton.className = `modal-btn modal-btn-ok modal-btn-${config.tone}`;
                messageElement.innerHTML = `
                    <span class="modal-book-title">${escapeHtml(bookTitle)}</span><br>
                    ${escapeHtml(message || 'This book already has an active request on your account.')}
                `;
                modal.classList.add('show');
            }

            // Function to close request state modal
            function closeRequestStateModal() {
                const modal = document.getElementById('requestStateModal');
                modal.classList.remove('show');
            }

            // Function to show error modal
            function showErrorModal(errorMsg) {
                const modal = document.getElementById('errorModal');
                const errorMessage = document.getElementById('errorMessage');
                errorMessage.textContent = errorMsg || 'An error occurred while processing your request. Please try again later.';
                modal.classList.add('show');
            }

            // Function to close error modal
            function closeErrorModal() {
                const modal = document.getElementById('errorModal');
                modal.classList.remove('show');
            }

            // Function to fetch and display filtered books
            function fetchBooks() {
                const params = getCurrentFilters();
                syncBrowserUrl(params);
                const requestUrl = params.toString() ? `${searchUrl}?${params.toString()}` : searchUrl;

                fetch(requestUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        allBooks = data.books;
                        displayedBooks = 0;
                        displayInitialBooks();
                        bookCount.textContent = data.count;
                    })
                    .catch(error => console.error('Error fetching books:', error));
            }

            // Function to attach request button listeners
            function attachRequestButtonListeners() {
                document.querySelectorAll('.request-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const bookId = this.getAttribute('data-book-id');
                        const bookCard = this.closest('.book-card');
                        const bookTitle = bookCard.querySelector('.book-title').textContent;

                        // Disable button while processing
                        const originalText = this.innerHTML;
                        this.disabled = true;
                        this.innerHTML = '<span>Requesting...</span>';

                        // Submit request to backend
                        fetch('{{ route('student.book-request') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content'),
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify({
                                    book_id: bookId
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    this.innerHTML = `${getRequestButtonIconSvg('check')}<span>Requested</span>`;
                                    this.style.backgroundColor = '#10b981';
                                    this.disabled = true;
                                    showSuccessModal(bookTitle, data.next_step || '');
                                } else {
                                    if (data.reason === 'book_unavailable') {
                                        showUnavailableModal(bookTitle);
                                    } else if (data.reason === 'already_borrowed' ||
                                        data.reason === 'request_pending' ||
                                        data.reason === 'request_approved' ||
                                        data.reason === 'request_issued' ||
                                        data.reason === 'request_exists') {
                                        showRequestStateModal(
                                            bookTitle,
                                            data.reason,
                                            data.message || 'This book already has an active request on your account.'
                                        );
                                    } else {
                                        showErrorModal(data.message || 'Error submitting request. Please try again.');
                                    }
                                    this.innerHTML = originalText;
                                    this.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                this.innerHTML = originalText;
                                this.disabled = false;
                                showErrorModal('Error submitting request. Please try again.');
                            });
                            });
                });
            }

            function resetFilters() {
                searchInput.value = '';
                categoryFilter.value = '';
                availabilityFilter.value = '';
                conditionFilter.value = '';
                sortFilter.value = 'title_asc';
                fetchBooks();
            }

            searchForm.addEventListener('submit', function(event) {
                event.preventDefault();
                fetchBooks();
            });

            // Live search with debounce
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetchBooks();
                }, 300);
            });

            // Live filter on category change
            categoryFilter.addEventListener('change', function() {
                fetchBooks();
            });

            availabilityFilter.addEventListener('change', function() {
                fetchBooks();
            });

            conditionFilter.addEventListener('change', function() {
                fetchBooks();
            });

            sortFilter.addEventListener('change', function() {
                fetchBooks();
            });

            resetFiltersBtn.addEventListener('click', function() {
                resetFilters();
            });

            // Show More button click handler
            showMoreBtn.addEventListener('click', function() {
                loadMoreBooks();
            });

            // Modal close button click handlers
            const closeModalBtn = document.getElementById('closeModalBtn');
            closeModalBtn.addEventListener('click', function() {
                closeUnavailableModal();
            });

            const closeSuccessModalBtn = document.getElementById('closeSuccessModalBtn');
            closeSuccessModalBtn.addEventListener('click', function() {
                closeSuccessModal();
            });

            const closeRequestStateModalBtn = document.getElementById('closeRequestStateModalBtn');
            closeRequestStateModalBtn.addEventListener('click', function() {
                closeRequestStateModal();
            });

            const closeErrorModalBtn = document.getElementById('closeErrorModalBtn');
            closeErrorModalBtn.addEventListener('click', function() {
                closeErrorModal();
            });

            // Close all modals when clicking outside of modal content
            window.addEventListener('click', function(event) {
                const unavailableModal = document.getElementById('unavailableModal');
                const successModal = document.getElementById('successModal');
                const requestStateModal = document.getElementById('requestStateModal');
                const errorModal = document.getElementById('errorModal');

                if (event.target === unavailableModal) {
                    closeUnavailableModal();
                }
                if (event.target === successModal) {
                    closeSuccessModal();
                }
                if (event.target === requestStateModal) {
                    closeRequestStateModal();
                }
                if (event.target === errorModal) {
                    closeErrorModal();
                }
            });

            // Initial load - fetch and display first 12 books
            fetchBooks();

            // Initial attachment of request button listeners (will be reattached after fetch)
            attachRequestButtonListeners();
        });
    </script>
@endpush
