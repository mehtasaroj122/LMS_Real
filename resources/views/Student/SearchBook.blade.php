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
            padding: 0.75rem;
            border-radius: 0.5rem;
            background-color: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e5e7eb);
            margin-bottom: 1rem;
        }

        .search-bar {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.5rem;
            align-items: center;
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
            font-size: 0.95rem;
        }

        .filter-select {
            padding: 0.5rem 1.75rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid var(--border-color, #e5e7eb);
            background-color: var(--bg-input, #ffffff);
            color: var(--text-primary, #0f172a);
            font-size: 0.875rem;
            min-width: 140px;
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
            font-size: 0.85rem;
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
            margin-bottom: 0.5rem;
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
        }

        /* Action Button */
        .request-btn {
            width: 100%;
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
                grid-template-columns: 1fr;
                gap: 0.5rem;
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
                padding: 0.5rem;
            }
        }

        @media (max-width: 480px) {
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
            background-color: #fef3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
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
            <form method="GET" action="{{ route('student.search') }}" id="searchForm"
                style="width:100%; display:grid; grid-template-columns: 1fr auto; gap: 0.5rem; align-items: center;">
                <div class="search-input-group">
                    <span class="search-icon"></span>
                    <input type="text" name="q" class="search-input"
                        placeholder="Search by title, author, or ISBN..." value="{{ $search }}" id="searchInput">
                </div>
                <div class="select-wrapper">
                    <select name="category" class="filter-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $selectedCategory == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <span class="select-arrow"></span>
                </div>
            </form>
        </div>

        <!-- Search Result Info -->
        <div class="results-info">Found <span id="bookCount">{{ $books->count() }}</span> books</div>

        <!-- Books Grid -->
        <div class="books-grid" id="booksContainer">
            @forelse($books as $book)
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
                            <span class="metadata-label">Edition</span>
                            <span class="metadata-value">{{ $book->edition ?? '-' }}</span>
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
                    <p class="book-description">{{ $book->description }}</p>
                    <button class="request-btn" data-book-id="{{ $book->id }}">
                        <span>+</span>
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
                <div class="modal-icon">📚</div>
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
                <div class="modal-icon" style="background-color: #d1fae5; color: #059669;">✓</div>
                <h2 class="modal-title">Request Submitted</h2>
            </div>
            <div class="modal-body">
                <p class="modal-message">
                    Your request for "<span id="bookTitleSuccess"></span>" has been submitted successfully! The library will notify you when the book becomes available.
                </p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-ok" id="closeSuccessModalBtn" style="background-color: #059669;">OK</button>
            </div>
        </div>
    </div>

    <!-- Already Submitted Modal -->
    <div id="alreadySubmittedModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon" style="background-color: #dbeafe; color: #0284c7;">ℹ</div>
                <h2 class="modal-title">Request Already Submitted</h2>
            </div>
            <div class="modal-body">
                <p class="modal-message">
                    You have already submitted a request for "<span id="bookTitleAlready"></span>". Please wait for our notification when the book becomes available, or check your requests list for more details.
                </p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-ok" id="closeAlreadyModalBtn" style="background-color: #0284c7;">OK</button>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon" style="background-color: #fee2e2; color: #dc2626;">⚠</div>
                <h2 class="modal-title">Request Error</h2>
            </div>
            <div class="modal-body">
                <p class="modal-message" id="errorMessage">
                    An error occurred while processing your request. Please try again later.
                </p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-ok" id="closeErrorModalBtn" style="background-color: #dc2626;">OK</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const booksContainer = document.getElementById('booksContainer');
            const bookCount = document.getElementById('bookCount');
            const showMoreBtn = document.getElementById('showMoreBtn');
            const searchUrl = '{{ route('student.search') }}';

            const BOOKS_PER_PAGE = 12;
            let allBooks = [];
            let displayedBooks = 0;

            // Function to render paginated book cards
            function renderPaginatedBooks(booksToRender) {
                if (booksToRender.length === 0) {
                    booksContainer.innerHTML = '<p>No books found.</p>';
                    showMoreBtn.style.display = 'none';
                    return;
                }

                const booksHTML = booksToRender.map(book => `
                    <div class="book-card">
                        <div class="book-header">
                            <div>
                                <h3 class="book-title">${book.title}</h3>
                                <p class="book-author">${book.author}</p>
                            </div>
                            <span class="availability-badge ${book.status === 'available' ? 'available' : 'unavailable'}">${book.status === 'available' ? 'Available' : 'Unavailable'}</span>
                        </div>
                        <div class="book-metadata">
                            <div class="metadata-item">
                                <span class="metadata-label">Category</span>
                                <span class="category-badge">${book.category?.name || 'N/A'}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Publisher</span>
                                <span class="metadata-value">${book.publisher}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Edition</span>
                                <span class="metadata-value">${book.edition || '-'}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">ISBN</span>
                                <span class="metadata-value">${book.isbn}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Location</span>
                                <span class="metadata-value">${book.shelf_no || '-'}</span>
                            </div>
                            <div class="metadata-item">
                                <span class="metadata-label">Available Copies</span>
                                <span class="metadata-value">${book.available_copies} / ${book.total_copies}</span>
                            </div>
                        </div>
                        <p class="book-description">${book.description}</p>
                        <button class="request-btn" data-book-id="${book.id}">
                            <span>+</span>
                            <span>Request Book</span>
                        </button>
                    </div>
                `).join('');

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
            function showSuccessModal(bookTitle) {
                const modal = document.getElementById('successModal');
                const bookTitleSpan = document.getElementById('bookTitleSuccess');
                bookTitleSpan.textContent = bookTitle;
                modal.classList.add('show');
            }

            // Function to close success modal
            function closeSuccessModal() {
                const modal = document.getElementById('successModal');
                modal.classList.remove('show');
            }

            // Function to show already submitted modal
            function showAlreadySubmittedModal(bookTitle) {
                const modal = document.getElementById('alreadySubmittedModal');
                const bookTitleSpan = document.getElementById('bookTitleAlready');
                bookTitleSpan.textContent = bookTitle;
                modal.classList.add('show');
            }

            // Function to close already submitted modal
            function closeAlreadySubmittedModal() {
                const modal = document.getElementById('alreadySubmittedModal');
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
                const searchQuery = searchInput.value;
                const categoryId = categoryFilter.value;

                const params = new URLSearchParams();
                if (searchQuery) params.append('q', searchQuery);
                if (categoryId) params.append('category', categoryId);

                fetch(`${searchUrl}?${params.toString()}`, {
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
                        const availabilityBadge = bookCard.querySelector('.availability-badge').textContent.trim();
                        const bookStatus = availabilityBadge.toLowerCase();

                        // Check if book is unavailable
                        if (bookStatus !== 'available') {
                            showUnavailableModal(bookTitle);
                            return;
                        }

                        // Debug: Log the book ID
                        console.log('Requesting book with ID:', bookId, 'Title:', bookTitle);

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
                                console.log('Response:', data);
                                if (data.success) {
                                    this.innerHTML = '<span>✓</span><span>Requested</span>';
                                    this.style.backgroundColor = '#10b981';
                                    this.disabled = true;
                                    showSuccessModal(bookTitle);
                                } else {
                                    // Check if it's an already submitted error
                                    if (data.message && data.message.toLowerCase().includes('already')) {
                                        showAlreadySubmittedModal(bookTitle);
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

            const closeAlreadyModalBtn = document.getElementById('closeAlreadyModalBtn');
            closeAlreadyModalBtn.addEventListener('click', function() {
                closeAlreadySubmittedModal();
            });

            const closeErrorModalBtn = document.getElementById('closeErrorModalBtn');
            closeErrorModalBtn.addEventListener('click', function() {
                closeErrorModal();
            });

            // Close all modals when clicking outside of modal content
            window.addEventListener('click', function(event) {
                const unavailableModal = document.getElementById('unavailableModal');
                const successModal = document.getElementById('successModal');
                const alreadySubmittedModal = document.getElementById('alreadySubmittedModal');
                const errorModal = document.getElementById('errorModal');

                if (event.target === unavailableModal) {
                    closeUnavailableModal();
                }
                if (event.target === successModal) {
                    closeSuccessModal();
                }
                if (event.target === alreadySubmittedModal) {
                    closeAlreadySubmittedModal();
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
