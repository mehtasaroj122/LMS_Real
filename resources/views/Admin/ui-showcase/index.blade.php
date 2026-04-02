@extends('Admin.layouts.app')

@section('title', 'UI Component Showcase')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/CSS/ui-showcase.css') }}">
@endpush

@section('content')
    @php
        $statusBadges = [
            'available' => ['label' => 'Available', 'class' => 'badge-success'],
            'issued' => ['label' => 'Issued', 'class' => 'badge-info'],
            'reserved' => ['label' => 'Reserved', 'class' => 'badge-warning'],
        ];
    @endphp

    <div class="showcase-container palette-blue">
        <div class="showcase-layout">
            <section class="showcase-hero card">
                <div>
                    <span class="showcase-eyebrow">Admin Design Lab</span>
                    <h1 class="showcase-page-title">UI Component Showcase</h1>
                    <p class="showcase-page-subtitle">Preview and test color palettes across the Library Management System admin experience.</p>
                </div>

                <div class="showcase-hero-actions">
                    <div class="showcase-palette-indicator border-palette">
                        <span class="showcase-palette-label">Active Palette</span>
                        <div class="showcase-palette-meta">
                            <span class="palette-swatch" id="activePaletteSwatch"></span>
                            <span class="text-palette" id="activePaletteLabel">Blue</span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-lg" id="quickThemeToggle">
                        <i class="fas fa-circle-half-stroke"></i>
                        <span>Toggle Light / Dark</span>
                    </button>
                </div>
            </section>

            <section class="showcase-section palette-switcher">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Color Palette Switcher</h2>
                        <p class="showcase-section-copy">Choose a palette to update buttons, badges, borders, accents, and supporting surfaces in real time.</p>
                    </div>
                </div>

                <div class="palette-switcher-grid">
                    @foreach ($palettes as $key => $palette)
                        <button
                            type="button"
                            class="palette-btn"
                            data-palette="{{ $key }}"
                            data-name="{{ $palette['name'] }}"
                            data-color="{{ $palette['primary'] }}"
                        >
                            <span class="palette-swatch" style="background-color: {{ $palette['primary'] }}"></span>
                            <span>{{ $palette['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Buttons Showcase</h2>
                        <p class="showcase-section-copy">Primary actions inherit the selected palette, while supporting actions keep semantic success, danger, warning, and info treatments.</p>
                    </div>
                </div>

                <div class="showcase-stack">
                    <div class="showcase-button-row">
                        <button type="button" class="btn btn-primary">Primary Button</button>
                        <button type="button" class="btn btn-secondary">Secondary Button</button>
                        <button type="button" class="btn btn-success">Success Button</button>
                        <button type="button" class="btn btn-danger">Danger Button</button>
                        <button type="button" class="btn btn-warning">Warning Button</button>
                        <button type="button" class="btn btn-info">Info Button</button>
                        <button type="button" class="btn btn-ghost">Ghost Button</button>
                    </div>

                    <div class="showcase-button-row">
                        <button type="button" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            <span>Add Book</span>
                        </button>
                        <button type="button" class="btn btn-secondary">
                            <i class="fas fa-sliders"></i>
                            <span>Adjust Filters</span>
                        </button>
                        <button type="button" class="btn btn-info">
                            <i class="fas fa-download"></i>
                            <span>Export Data</span>
                        </button>
                        <button type="button" class="btn btn-icon btn-primary" aria-label="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-icon btn-danger" aria-label="Delete">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>

                    <div class="showcase-button-row">
                        <button type="button" class="btn btn-primary btn-sm">Small Action</button>
                        <button type="button" class="btn btn-primary">Default Action</button>
                        <button type="button" class="btn btn-primary btn-lg">Large Action</button>
                    </div>
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Cards Showcase</h2>
                        <p class="showcase-section-copy">These cards mirror the admin shell with palette-aware accents for summaries, alerts, and stat highlights.</p>
                    </div>
                </div>

                <div class="showcase-card-grid">
                    <article class="card showcase-card-panel">
                        <div class="card-body">
                            <h3 class="showcase-card-title">Basic Card</h3>
                            <p class="text-muted">Use this pattern for quick summaries, lightweight instructions, or contextual descriptions above dense admin content.</p>
                        </div>
                    </article>

                    <article class="card showcase-card-panel">
                        <div class="card-header">
                            <h3 class="showcase-card-title">Card With Header</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Headers create clearer hierarchy for forms, analytics, and grouped tables.</p>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary btn-sm">Cancel</button>
                            <button type="button" class="btn btn-primary btn-sm">Save Changes</button>
                        </div>
                    </article>

                    <article class="card showcase-card-panel showcase-stat-card">
                        <div class="showcase-stat-icon">
                            <i class="fas fa-book-open-reader"></i>
                        </div>
                        <div>
                            <div class="showcase-stat-value">2,486</div>
                            <div class="showcase-stat-label">Books Cataloged</div>
                        </div>
                    </article>

                    <article class="card showcase-card-panel">
                        <div class="card-body">
                            <h3 class="showcase-card-title">Card With Nested Alert</h3>
                            <div class="alert alert-info mb-0">
                                <div class="alert-content">
                                    <strong>Heads up:</strong> This palette preview helps compare brand accents before applying them to the wider admin panel.
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="showcase-theme-preview-grid">
                    <article class="theme-preview-card theme-preview-light">
                        <div class="theme-preview-label">Light Theme Preview</div>
                        <h3>Circulation Snapshot</h3>
                        <p>High-contrast headings, soft borders, and brighter palette accents for dashboards and forms.</p>
                    </article>

                    <article class="theme-preview-card theme-preview-dark">
                        <div class="theme-preview-label">Dark Theme Preview</div>
                        <h3>Night Shift Console</h3>
                        <p>Muted surfaces, stronger glow accents, and readable content blocks for staff working after hours.</p>
                    </article>
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Tables Showcase</h2>
                        <p class="showcase-section-copy">The table demo uses sample book data, semantic status badges, icon actions, sortable header hints, and a deliberate empty state row.</p>
                    </div>
                </div>

                <div class="card showcase-table-shell">
                    <div class="overflow-x-auto showcase-table-wrapper">
                        <table class="table table-hover table-light">
                            <thead>
                                <tr>
                                    <th>ID <i class="fas fa-sort text-muted"></i></th>
                                    <th>Title <i class="fas fa-sort text-muted"></i></th>
                                    <th>Author <i class="fas fa-sort text-muted"></i></th>
                                    <th>Category <i class="fas fa-sort text-muted"></i></th>
                                    <th>Status <i class="fas fa-sort text-muted"></i></th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sampleData as $book)
                                    <tr>
                                        <td>#{{ $book['id'] }}</td>
                                        <td>
                                            <div class="font-semibold text-primary">{{ $book['title'] }}</div>
                                            <div class="text-muted text-sm">Showcase Row {{ $loop->iteration }}</div>
                                        </td>
                                        <td>{{ $book['author'] }}</td>
                                        <td>{{ $book['category'] }}</td>
                                        <td>
                                            <span class="badge {{ $statusBadges[$book['status']]['class'] ?? 'badge-default' }}">
                                                {{ $statusBadges[$book['status']]['label'] ?? ucfirst($book['status']) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <button type="button" class="btn btn-icon btn-secondary btn-sm" aria-label="Edit row">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-danger btn-sm" aria-label="Delete row">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="table-empty-row">
                                    <td colspan="6">No more data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Forms Showcase</h2>
                        <p class="showcase-section-copy">Inputs below cover common admin field states, grouped selections, and custom toggles with light and dark theme support.</p>
                    </div>
                </div>

                <div class="showcase-form-grid">
                    <div class="showcase-field">
                        <label for="showcaseTitle" class="form-label">Book Title</label>
                        <input id="showcaseTitle" type="text" class="form-control" value="Atomic Habits" placeholder="Enter book title">
                    </div>

                    <div class="showcase-field">
                        <label for="showcaseEmail" class="form-label">Notification Email</label>
                        <input id="showcaseEmail" type="email" class="form-control" value="librarian@library.edu" placeholder="Enter contact email">
                    </div>

                    <div class="showcase-field">
                        <label for="showcasePassword" class="form-label">Temporary Password</label>
                        <input id="showcasePassword" type="password" class="form-control" value="secret123" placeholder="Set temporary password">
                    </div>

                    <div class="showcase-field">
                        <label for="showcaseSelect" class="form-label">Collection Type</label>
                        <select id="showcaseSelect" class="form-control filter-select">
                            <option>General Collection</option>
                            <option>Reference</option>
                            <option>Archives</option>
                            <option>Digital Resources</option>
                        </select>
                    </div>

                    <div class="showcase-field showcase-field-span">
                        <label for="showcaseTextarea" class="form-label">Description</label>
                        <textarea id="showcaseTextarea" class="form-control" rows="4" placeholder="Add a short description for the selected item">A well-structured example field for testing spacing, focus states, and multiline content.</textarea>
                    </div>

                    <div class="showcase-field">
                        <label class="form-label" for="showcaseCheckbox">Single Checkbox</label>
                        <label class="choice-item" for="showcaseCheckbox">
                            <input id="showcaseCheckbox" type="checkbox" class="form-check-input" checked>
                            <span>Allow reservations for this title</span>
                        </label>
                    </div>

                    <div class="showcase-field">
                        <label class="form-label">Checkbox Group</label>
                        <div class="choice-list">
                            <label class="choice-item">
                                <input type="checkbox" class="form-check-input" checked>
                                <span>Students</span>
                            </label>
                            <label class="choice-item">
                                <input type="checkbox" class="form-check-input" checked>
                                <span>Faculty</span>
                            </label>
                            <label class="choice-item">
                                <input type="checkbox" class="form-check-input">
                                <span>Visitors</span>
                            </label>
                        </div>
                    </div>

                    <div class="showcase-field">
                        <label class="form-label">Radio Group</label>
                        <div class="choice-list choice-list-inline">
                            <label class="choice-item">
                                <input type="radio" name="availability" class="form-check-input" checked>
                                <span>Available</span>
                            </label>
                            <label class="choice-item">
                                <input type="radio" name="availability" class="form-check-input">
                                <span>Issued</span>
                            </label>
                            <label class="choice-item">
                                <input type="radio" name="availability" class="form-check-input">
                                <span>Reserved</span>
                            </label>
                        </div>
                    </div>

                    <div class="showcase-field">
                        <label for="showcaseFile" class="form-label">File Upload</label>
                        <input id="showcaseFile" type="file" class="form-control file-input">
                    </div>

                    <div class="showcase-field">
                        <label for="showcaseDate" class="form-label">Return Date</label>
                        <input id="showcaseDate" type="date" class="form-control" value="{{ now()->addWeek()->format('Y-m-d') }}">
                    </div>

                    <div class="showcase-field">
                        <label class="form-label">Toggle Switch</label>
                        <label class="toggle-field">
                            <input type="checkbox" checked>
                            <span class="toggle-switch"></span>
                            <span>Send automatic due reminders</span>
                        </label>
                    </div>

                    <div class="showcase-field">
                        <label for="showcaseValid" class="form-label">Valid Input Example</label>
                        <input id="showcaseValid" type="text" class="form-control is-valid" value="ISBN-9780141182636">
                        <div class="valid-feedback">Looks good. This field is ready to save.</div>
                    </div>

                    <div class="showcase-field">
                        <label for="showcaseInvalid" class="form-label">Invalid Input Example</label>
                        <input id="showcaseInvalid" type="text" class="form-control is-invalid" value="12">
                        <div class="invalid-feedback">Please enter at least 10 characters for this example.</div>
                    </div>
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Modals Showcase</h2>
                        <p class="showcase-section-copy">Launch each modal to check layering, spacing, animation, focus flow, and how palette accents behave inside overlays.</p>
                    </div>
                </div>

                <div class="showcase-button-row">
                    <button type="button" class="btn btn-primary" data-modal-open="addItemModal">
                        <i class="fas fa-plus"></i>
                        <span>Open Add Item Modal</span>
                    </button>
                    <button type="button" class="btn btn-secondary" data-modal-open="editItemModal">
                        <i class="fas fa-pen-to-square"></i>
                        <span>Open Edit Item Modal</span>
                    </button>
                    <button type="button" class="btn btn-danger" data-modal-open="deleteItemModal">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>Open Delete Confirmation</span>
                    </button>
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Alerts Showcase</h2>
                        <p class="showcase-section-copy">Dismissible alerts help validate semantic color handling, icon spacing, and exit animations.</p>
                    </div>
                </div>

                <div class="showcase-alert-stack">
                    <div class="alert alert-success">
                        <div class="alert-content">
                            <strong>Success:</strong> Inventory sync completed for 145 shelf records.
                        </div>
                        <button type="button" class="alert-close" onclick="dismissAlert(this)" aria-label="Dismiss success alert">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    <div class="alert alert-danger">
                        <div class="alert-content">
                            <strong>Error:</strong> The selected borrower account could not be activated.
                        </div>
                        <button type="button" class="alert-close" onclick="dismissAlert(this)" aria-label="Dismiss error alert">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    <div class="alert alert-warning">
                        <div class="alert-content">
                            <strong>Warning:</strong> Two copies are due back within the next 24 hours.
                        </div>
                        <button type="button" class="alert-close" onclick="dismissAlert(this)" aria-label="Dismiss warning alert">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    <div class="alert alert-info">
                        <div class="alert-content">
                            <strong>Info:</strong> Use this page to compare color systems before promoting one into the main admin theme.
                        </div>
                        <button type="button" class="alert-close" onclick="dismissAlert(this)" aria-label="Dismiss info alert">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Badges Showcase</h2>
                        <p class="showcase-section-copy">Default, semantic, palette-driven, and pill badges give you a quick read on density, contrast, and emphasis.</p>
                    </div>
                </div>

                <div class="showcase-badge-grid">
                    <span class="badge badge-default">Default</span>
                    <span class="badge badge-palette">Primary</span>
                    <span class="badge badge-success">Success</span>
                    <span class="badge badge-danger">Danger</span>
                    <span class="badge badge-warning">Warning</span>
                    <span class="badge badge-info">Info</span>
                    <span class="badge badge-default badge-pill">Gray Pill</span>
                    <span class="badge badge-palette badge-pill">Palette Pill</span>
                    <span class="badge badge-success badge-pill">Live</span>
                    <span class="badge badge-danger badge-pill">Blocked</span>
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Search &amp; Pagination</h2>
                        <p class="showcase-section-copy">Search affordances and the existing pagination component sit together here so spacing can be reviewed as a full list header/footer unit.</p>
                    </div>
                </div>

                <div class="card showcase-search-shell">
                    <div class="showcase-search-grid">
                        <label class="showcase-field">
                            <span class="form-label">Search Input</span>
                            <span class="search-field">
                                <i class="fas fa-search search-icon"></i>
                                <input type="search" class="form-control" placeholder="Search books, students, or requests">
                            </span>
                        </label>

                        <div class="showcase-field">
                            <label for="showcaseFilter" class="form-label">Search With Filter</label>
                            <div class="showcase-filter-row">
                                <span class="search-field">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="search" class="form-control" placeholder="Search catalog entries">
                                </span>
                                <select id="showcaseFilter" class="form-control filter-select">
                                    <option>All records</option>
                                    <option>Books</option>
                                    <option>Users</option>
                                    <option>Transactions</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    @include('shared.admin-table-pagination', ['paginator' => $paginator])
                </div>
            </section>

            <section class="showcase-section">
                <div class="showcase-section-head">
                    <div>
                        <h2 class="showcase-section-title">Navigation Preview</h2>
                        <p class="showcase-section-copy">These examples mirror sidebar states so you can test active accents, hover surfaces, and copy readability.</p>
                    </div>
                </div>

                <div class="showcase-nav-grid">
                    <div class="showcase-nav-card">
                        <div class="showcase-nav-label">Inactive</div>
                        <div class="showcase-nav-shell">
                            <div class="sidebar-item showcase-sidebar-item">
                                <i class="fas fa-chart-line"></i>
                                <span>Reports</span>
                            </div>
                        </div>
                    </div>

                    <div class="showcase-nav-card">
                        <div class="showcase-nav-label">Hover</div>
                        <div class="showcase-nav-shell">
                            <div class="sidebar-item showcase-sidebar-item showcase-sidebar-item-hover">
                                <i class="fas fa-book"></i>
                                <span>Book Management</span>
                            </div>
                        </div>
                    </div>

                    <div class="showcase-nav-card">
                        <div class="showcase-nav-label">Active</div>
                        <div class="showcase-nav-shell">
                            <div class="sidebar-item sidebar-item-active showcase-sidebar-item">
                                <i class="fas fa-gear"></i>
                                <span>Settings</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="modal-overlay" id="addItemModal" aria-hidden="true">
            <div class="modal-container modal-md" role="dialog" aria-modal="true" aria-labelledby="addItemModalTitle">
                <div class="modal-header">
                    <h3 id="addItemModalTitle">Add Item Modal</h3>
                    <button type="button" class="modal-close" data-modal-close="addItemModal" aria-label="Close add item modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="showcase-form-grid">
                        <div class="showcase-field">
                            <label for="modalBookTitle" class="form-label">Title</label>
                            <input id="modalBookTitle" type="text" class="form-control" placeholder="Enter a new title">
                        </div>

                        <div class="showcase-field">
                            <label for="modalBookAuthor" class="form-label">Author</label>
                            <input id="modalBookAuthor" type="text" class="form-control" placeholder="Enter author name">
                        </div>

                        <div class="showcase-field showcase-field-span">
                            <label for="modalBookNotes" class="form-label">Notes</label>
                            <textarea id="modalBookNotes" class="form-control" rows="4" placeholder="Add a short note or summary"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="addItemModal">Cancel</button>
                    <button type="button" class="btn btn-primary">Save Item</button>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="editItemModal" aria-hidden="true">
            <div class="modal-container modal-md" role="dialog" aria-modal="true" aria-labelledby="editItemModalTitle">
                <div class="modal-header">
                    <h3 id="editItemModalTitle">Edit Item Modal</h3>
                    <button type="button" class="modal-close" data-modal-close="editItemModal" aria-label="Close edit item modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="showcase-form-grid">
                        <div class="showcase-field">
                            <label for="editBookTitle" class="form-label">Title</label>
                            <input id="editBookTitle" type="text" class="form-control" value="The Midnight Library">
                        </div>

                        <div class="showcase-field">
                            <label for="editBookAuthor" class="form-label">Author</label>
                            <input id="editBookAuthor" type="text" class="form-control" value="Matt Haig">
                        </div>

                        <div class="showcase-field">
                            <label for="editBookStatus" class="form-label">Status</label>
                            <select id="editBookStatus" class="form-control filter-select">
                                <option>Available</option>
                                <option selected>Reserved</option>
                                <option>Issued</option>
                            </select>
                        </div>

                        <div class="showcase-field">
                            <label for="editBookCategory" class="form-label">Category</label>
                            <select id="editBookCategory" class="form-control filter-select">
                                <option>Fiction</option>
                                <option selected>Modern Fiction</option>
                                <option>Reference</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="editItemModal">Cancel</button>
                    <button type="button" class="btn btn-primary">Update Item</button>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="deleteItemModal" aria-hidden="true">
            <div class="modal-container modal-sm" role="dialog" aria-modal="true" aria-labelledby="deleteItemModalTitle">
                <div class="modal-header">
                    <h3 id="deleteItemModalTitle">Delete Confirmation</h3>
                    <button type="button" class="modal-close" data-modal-close="deleteItemModal" aria-label="Close delete confirmation modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="showcase-delete-icon">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <p class="showcase-delete-copy">Are you sure you want to remove this item from the catalog preview? This action is permanent in the real workflow.</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close="deleteItemModal">Cancel</button>
                    <button type="button" class="btn btn-danger">Confirm Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleModalScrollState() {
            const hasOpenModal = document.querySelector('.modal-overlay.active');
            document.body.style.overflow = hasOpenModal ? 'hidden' : '';
        }

        function openModal(id) {
            const modal = document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            toggleModalScrollState();
        }

        function closeModal(id) {
            const modal = document.getElementById(id);

            if (!modal) {
                return;
            }

            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            toggleModalScrollState();
        }

        function dismissAlert(btn) {
            const alert = btn.closest('.alert');

            if (!alert) {
                return;
            }

            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-6px)';

            setTimeout(() => {
                alert.remove();
            }, 300);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const showcaseContainer = document.querySelector('.showcase-container');
            const paletteButtons = document.querySelectorAll('.palette-btn');
            const activePaletteLabel = document.getElementById('activePaletteLabel');
            const activePaletteSwatch = document.getElementById('activePaletteSwatch');
            const quickThemeToggle = document.getElementById('quickThemeToggle');

            function applyPalette(button) {
                if (!showcaseContainer || !button) {
                    return;
                }

                const palette = button.dataset.palette;
                const paletteName = button.dataset.name || palette;
                const paletteColor = button.dataset.color || '';

                showcaseContainer.className = `showcase-container palette-${palette}`;
                localStorage.setItem('selected-palette', palette);

                paletteButtons.forEach((paletteButton) => paletteButton.classList.remove('active'));
                button.classList.add('active');

                if (activePaletteLabel) {
                    activePaletteLabel.textContent = paletteName;
                }

                if (activePaletteSwatch) {
                    activePaletteSwatch.style.backgroundColor = paletteColor;
                }
            }

            paletteButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    applyPalette(event.currentTarget);
                });
            });

            const savedPalette = localStorage.getItem('selected-palette') || 'blue';
            const initialPaletteButton = document.querySelector(`.palette-btn[data-palette="${savedPalette}"]`) || document.querySelector('.palette-btn[data-palette="blue"]');

            applyPalette(initialPaletteButton);

            if (quickThemeToggle) {
                quickThemeToggle.addEventListener('click', () => {
                    document.getElementById('themeToggle')?.click();
                });
            }

            document.querySelectorAll('[data-modal-open]').forEach((button) => {
                button.addEventListener('click', () => openModal(button.dataset.modalOpen));
            });

            document.querySelectorAll('[data-modal-close]').forEach((button) => {
                button.addEventListener('click', () => closeModal(button.dataset.modalClose));
            });

            document.querySelectorAll('.modal-overlay').forEach((overlay) => {
                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        closeModal(overlay.id);
                    }
                });
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach((modal) => {
                    closeModal(modal.id);
                });
            }
        });
    </script>
@endpush
