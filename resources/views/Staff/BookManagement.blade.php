@extends('Staff.layouts.app')

@section('title', 'Book Management')


@push('styles')
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Book Management Styles */
        .book-management {
            padding: 0px;
        }

        /* Stats Cards - Similar to User Management */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        @media (max-width: 1024px) {
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            padding: 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        body.light-theme .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        body.dark-theme .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .stat-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-total .stat-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .stat-available .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .stat-borrowed .stat-icon {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .stat-categories .stat-icon {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }

        .stat-info {
            flex: 1;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 500;
            opacity: 0.8;
        }

        .categories-list {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid;
            max-height: 250px;
            overflow-y: auto;
        }

        body.light-theme .categories-list {
            border-top-color: #e2e8f0;
        }

        body.dark-theme .categories-list {
            border-top-color: #334155;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
        }

        .category-count {
            font-weight: 600;
        }

        /* Header */
        .page-header {
            margin-bottom: 16px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        body.light-theme .page-title {
            color: #0f172a;
        }

        body.dark-theme .page-title {
            color: #f1f5f9;
        }

        .page-description {
            font-size: 12px;
            color: #64748b;
        }

        body.dark-theme .page-description {
            color: #94a3b8;
        }

        /* Toolbar */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .section-header {
            flex: 1;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .section-subtitle {
            font-size: 12px;
            color: #64748b;
        }

        body.dark-theme .section-subtitle {
            color: #94a3b8;
        }

        .search-add-wrapper {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            min-width: 250px;
        }

        .search-input {
            width: 100%;
            padding: 8px 12px 8px 32px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        body.light-theme .search-input {
            background-color: #ffffff;
            color: #0f172a;
        }

        body.dark-theme .search-input {
            background-color: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 13px;
        }

        body.dark-theme .search-icon {
            color: #9ca3af;
        }

        /* Button Styles */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        body.dark-theme .btn-outline {
            border-color: #475569;
            color: #cbd5e1;
        }

        .btn-outline:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme .btn-outline:hover {
            background-color: #334155;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        /* Book Count Badge */
        .book-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        /* Table Styles */
        .table-container {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid;
            margin-top: 12px;
        }

        body.light-theme .table-container {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .table-container {
            background-color: #1e293b;
            border-color: #334155;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        .table th {
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            border-bottom: 1px solid;
            white-space: nowrap;
        }

        body.light-theme .table th {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
        }

        body.dark-theme .table th {
            background-color: #1e293b;
            border-color: #334155;
            color: #cbd5e1;
        }

        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid;
            vertical-align: middle;
            font-size: 13px;
        }

        body.light-theme .table td {
            border-color: #e2e8f0;
        }

        body.dark-theme .table td {
            border-color: #334155;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .table tr:hover {
            background-color: #2d3748;
        }

        /* Condition Badge */
        .condition-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
            gap: 4px;
        }

        .condition-new {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }

        body.dark-theme .condition-new {
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
            color: #4ade80;
        }

        .condition-good {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        body.dark-theme .condition-good {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #93c5fd;
        }

        .condition-damaged {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        body.dark-theme .condition-damaged {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        /* Copy Count */
        .copy-count {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
        }

        .copy-total {
            color: #6b7280;
        }

        .copy-available {
            color: #10b981;
        }

        body.dark-theme .copy-total {
            color: #9ca3af;
        }

        body.dark-theme .copy-available {
            color: #34d399;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: flex-start;
        }

        .action-btn {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 13px;
        }

        body.light-theme .action-btn {
            color: #64748b;
            background-color: #f1f5f9;
        }

        body.dark-theme .action-btn {
            color: #94a3b8;
            background-color: #334155;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .action-btn.view:hover {
            color: #3b82f6;
            background: #dbeafe;
        }

        body.dark-theme .action-btn.view:hover {
            background: #1e3a8a;
        }

        .action-btn.edit:hover {
            color: #f59e0b;
            background: #fef3c7;
        }

        body.dark-theme .action-btn.edit:hover {
            background: #78350f;
        }

        .action-btn.delete:hover {
            color: #ef4444;
            background: #fee2e2;
        }

        body.dark-theme .action-btn.delete:hover {
            background: #7f1d1d;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            border-radius: 16px;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        body.light-theme .modal {
            background-color: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        body.dark-theme .modal {
            background-color: #1e293b;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px;
            border-bottom: 1px solid;
        }

        body.light-theme .modal-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .modal-header {
            border-color: #334155;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 600;
        }

        .modal-close-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            font-size: 20px;
            color: #64748b;
        }

        body.dark-theme .modal-close-btn {
            color: #94a3b8;
        }

        .modal-close-btn:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme .modal-close-btn:hover {
            background-color: #334155;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-description {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 24px;
        }

        body.dark-theme .modal-description {
            color: #94a3b8;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid;
        }

        body.light-theme .modal-footer {
            border-color: #e5e7eb;
        }

        body.dark-theme .modal-footer {
            border-color: #334155;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .required::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        body.light-theme .form-control {
            background-color: #ffffff;
            color: #0f172a;
        }

        body.dark-theme .form-control {
            background-color: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme .form-control:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }

        body.dark-theme select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 8px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .notification.success {
            background-color: #10b981;
            color: white;
        }

        .notification.error {
            background-color: #ef4444;
            color: white;
        }

        .notification.info {
            background-color: #3b82f6;
            color: white;
        }

        .notification.warning {
            background-color: #f59e0b;
            color: white;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .notification-close {
            background: transparent;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 20px;
            padding: 0;
            margin-left: 8px;
        }

        /* Validation Error Styles */
        .form-control.error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        body.dark-theme .form-control.error {
            border-color: #f87171 !important;
            background-color: rgba(239, 68, 68, 0.1) !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }

        .form-control.error:focus {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }

        body.dark-theme .form-control.error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3) !important;
        }

        .field-error-message {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .field-error-message::before {
            content: "⚠";
        }

        body.dark-theme .field-error-message {
            color: #f87171;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .book-management {
                padding: 16px;
            }

            .stats-row {
                gap: 16px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-icon {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }

            .stat-value {
                font-size: 24px;
            }

            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-add-wrapper {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .table th,
            .table td {
                padding: 12px;
            }

            .action-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .action-btn {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="book-management">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Book Management</h1>
            <p class="page-description">Manage library book inventory</p>
        </div>

        <!-- Stats Row - 4 boxes in single row -->
        <div class="stats-row">
            <!-- Total Books -->
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Total Books</div>
                    </div>
                </div>
            </div>

            <!-- Total Copies -->
            <div class="stat-card stat-available">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Total Copies</div>
                    </div>
                </div>
            </div>

            <!-- Available Copies -->
            <div class="stat-card stat-borrowed">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Available Copies</div>
                    </div>
                </div>
            </div>

            <!-- By Category -->
            <div class="stat-card stat-categories">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">0</div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Section -->
        <div>
            <div class="toolbar">
                <div class="section-header">
                    <h2 class="section-title">All Books</h2>
                    <p class="section-subtitle">View and manage all library books</p>
                </div>

                <div class="search-add-wrapper">
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="Search books by title, author, ISBN..."
                            id="searchInput">
                    </div>

                    <button class="btn btn-primary" id="addBookBtn">
                        <i class="fas fa-plus"></i>
                        Add New Book
                    </button>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div
                style="display: flex; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px;">
                <button class="filter-tab active" data-condition="all"
                    style="padding: 8px 16px; background: none; border: none; border-bottom: 2px solid #3b82f6; color: #3b82f6; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                    All Books
                </button>
                <button class="filter-tab" data-condition="new"
                    style="padding: 8px 16px; background: none; border: none; color: #6b7280; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                    New
                </button>
                <button class="filter-tab" data-condition="good"
                    style="padding: 8px 16px; background: none; border: none; color: #6b7280; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                    Good
                </button>
                <button class="filter-tab" data-condition="damaged"
                    style="padding: 8px 16px; background: none; border: none; color: #6b7280; cursor: pointer; font-weight: 500; transition: all 0.3s;">
                    Damaged
                </button>
            </div>

            <!-- Book Count -->
            <div class="book-count">
                <i class="fas fa-hashtag"></i>
                <span id="bookCountDisplay">0 books</span>
            </div>

            <!-- Books Table -->
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ISBN</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Rack</th>
                                <th>Total</th>
                                <th>Available</th>
                                <th>Condition</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="booksTableBody">
                            <!-- Books will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination Container -->
                <div id="paginationContainer" style="margin-top: 16px; display: flex; justify-content: center;"></div>
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div id="addBookModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Book</h3>
                <button class="modal-close-btn" id="closeAddBookModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description">Enter the details of the new book to add to the library.</p>

                <form id="addBookForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">ISBN</label>
                            <input type="text" name="isbn" class="form-control" placeholder="978-0-13-468599-1"
                                required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Rack Number</label>
                            <input type="text" name="shelf_no" class="form-control" placeholder="A-12" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter book title"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Author</label>
                        <input type="text" name="author" class="form-control" placeholder="Enter author name"
                            required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Publisher</label>
                            <input type="text" name="publisher" class="form-control" placeholder="Publisher name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control" id="addCategorySelect">
                                <option value="">Select existing category</option>
                                @forelse($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @empty
                                    <option value="">No categories available</option>
                                @endforelse
                            </select>
                            <small style="color: #6b7280; margin-top: 4px; display: block;">Or create a new
                                category</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Create New Category</label>
                            <input type="text" name="new_category" class="form-control" id="addNewCategory"
                                placeholder="Enter new category name">
                            <small style="color: #6b7280; margin-top: 4px; display: block;">Leave empty to use existing
                                category</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Condition</label>
                            <select name="condition" class="form-control" required>
                                <option value="">Select condition</option>
                                <option value="new">New</option>
                                <option value="good" selected>Good</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Total Copies</label>
                            <input type="number" name="total_copies" class="form-control" placeholder="1"
                                min="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Available Copies</label>
                            <input type="number" name="available_copies" class="form-control" placeholder="1"
                                min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" placeholder="Enter book description" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cover Image (optional)</label>
                        <div style="display:flex;gap:12px;align-items:center;">
                            <div style="width:100px; height:140px; border-radius:8px; overflow:hidden; background:linear-gradient(135deg,#eef2ff 0%,#e9d5ff 100%); display:flex;align-items:center;justify-content:center;">
                                <img id="addCoverPreview" src="" alt="Cover preview" style="width:100%;height:100%;object-fit:cover;display:none;" />
                                <div id="addCoverPlaceholder" style="font-weight:700;color:#4f46e5;font-size:20px;">No Image</div>
                            </div>
                            <div style="flex:1;">
                                <input type="file" name="cover_image" id="addCover" accept="image/*" class="form-control" />
                                <p class="upload-hint">Optional. JPEG/PNG/GIF. Max 2MB.</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelAddBook">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitAddBook">
                    <i class="fas fa-plus"></i>
                    Add Book
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div id="editBookModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit Book</h3>
                <button class="modal-close-btn" id="closeEditBookModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description">Update book information and inventory details.</p>

                <form id="editBookForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">ISBN</label>
                            <input type="text" name="isbn" class="form-control" id="editISBN"
                                value="978-0-13-595785-9" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Rack Number</label>
                            <input type="text" name="shelf_no" class="form-control" id="editRack" value="A-15"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" class="form-control" id="editTitle" value="Clean Code"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Author</label>
                        <input type="text" name="author" class="form-control" id="editAuthor"
                            value="Robert C. Martin" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Publisher</label>
                            <input type="text" name="publisher" class="form-control" id="editPublisher"
                                value="Prentice Hall">
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Category</label>
                            <select name="category_id" class="form-control" id="editCategory" required>
                                <option value="">Select category</option>
                                @forelse($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @empty
                                    <option value="">No categories available</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Condition</label>
                            <select name="condition" class="form-control" id="editCondition" required>
                                <option value="new">New</option>
                                <option value="good" selected>Good</option>
                                <option value="damaged">Damaged</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required">Total Copies</label>
                            <input type="number" name="total_copies" class="form-control" id="editTotalCopies"
                                value="5" min="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Available Copies</label>
                            <input type="number" name="available_copies" class="form-control" id="editAvailableCopies"
                                value="2" min="0" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" id="editDescription" rows="4">A handbook of agile software craftsmanship that helps you write better code with fewer bugs.</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cover Image (optional)</label>
                        <div style="display:flex;gap:12px;align-items:center;">
                            <div style="width:100px; height:140px; border-radius:8px; overflow:hidden; background:linear-gradient(135deg,#eef2ff 0%,#e9d5ff 100%); display:flex;align-items:center;justify-content:center;">
                                <img id="editCoverPreview" src="" alt="Cover preview" style="width:100%;height:100%;object-fit:cover;display:none;" />
                                <div id="editCoverPlaceholder" style="font-weight:700;color:#4f46e5;font-size:20px;">No Image</div>
                            </div>
                            <div style="flex:1;">
                                <input type="file" name="cover_image" id="editCover" accept="image/*" class="form-control" />
                                <p class="upload-hint">Optional. Upload a new cover to replace existing one. JPEG/PNG/GIF. Max 2MB.</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelEditBook">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitEditBook">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- View Book Details Modal -->
    <div id="viewBookModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Book Details</h3>
                <button class="modal-close-btn" id="closeViewBookModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="bookDetailsContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="closeViewBookBtn">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Book Modal -->
    <div id="deleteBookModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="deleteBookModalTitle">Delete Book</h3>
                <button class="modal-close-btn" id="closeDeleteBookModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description" id="deleteBookMessage">Are you sure you want to delete this book?</p>

                <!-- Deletion request form (hidden by default) -->
                <div id="deletionRequestForm" style="display:none; margin-top:12px;">
                    <label for="deletionReason" class="form-label">Reason for deletion (optional)</label>
                    <textarea id="deletionReason" class="form-control" rows="4"
                        placeholder="Describe why this book should be removed (e.g. duplicate, damaged beyond repair, wrong entry)"></textarea>
                    <p style="font-size:12px; color:#6b7280; margin-top:8px;">A deletion request will be sent to
                        administrators for review. They will contact you if they need more information.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBook">Cancel</button>
                <button class="btn btn-outline" id="requestDeletionBtn">
                    <i class="fas fa-exclamation-circle"></i>
                    Request Deletion
                </button>
                <button class="btn btn-danger" id="confirmDeleteBook">
                    <i class="fas fa-trash-alt"></i>
                    Delete Book
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        class BookManager {
            constructor() {
                this.currentModal = null;
                this.currentBookId = null;
                this.currentBookTitle = null;
                this.currentConditionFilter = 'all';
                this.searchDebounceTimer = null;
                this.storageBase = '{{ asset('storage') }}';
                this.init();
            }

            init() {
                // Modal listeners
                document.getElementById('addBookBtn').addEventListener('click', () => this.openModal('addBookModal'));
                document.getElementById('closeAddBookModal').addEventListener('click', () => this.closeModal(
                    'addBookModal'));
                document.getElementById('cancelAddBook').addEventListener('click', () => this.closeModal(
                    'addBookModal'));
                document.getElementById('submitAddBook').addEventListener('click', () => this.submitAddBook());
                const addBookForm = document.getElementById('addBookForm');
                if (addBookForm) {
                    addBookForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.submitAddBook();
                    });
                    addBookForm.querySelectorAll('input, select, textarea').forEach(input => {
                        input.addEventListener('input', () => this.clearFieldError(input));
                        input.addEventListener('change', () => this.clearFieldError(input));
                    });
                }

                document.getElementById('closeEditBookModal').addEventListener('click', () => this.closeModal(
                    'editBookModal'));
                document.getElementById('cancelEditBook').addEventListener('click', () => this.closeModal(
                    'editBookModal'));
                document.getElementById('submitEditBook').addEventListener('click', () => this.submitEditBook());
                const editBookForm = document.getElementById('editBookForm');
                if (editBookForm) {
                    editBookForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.submitEditBook();
                    });
                    editBookForm.querySelectorAll('input, select, textarea').forEach(input => {
                        input.addEventListener('input', () => this.clearFieldError(input));
                        input.addEventListener('change', () => this.clearFieldError(input));
                    });
                }

                document.getElementById('closeViewBookModal').addEventListener('click', () => this.closeModal(
                    'viewBookModal'));
                document.getElementById('closeViewBookBtn').addEventListener('click', () => this.closeModal(
                    'viewBookModal'));

                document.getElementById('closeDeleteBookModal').addEventListener('click', () => this.closeModal(
                    'deleteBookModal'));
                document.getElementById('cancelDeleteBook').addEventListener('click', () => this.closeModal(
                    'deleteBookModal'));
                document.getElementById('confirmDeleteBook').addEventListener('click', () => this.confirmDeleteBook());
                const reqBtn = document.getElementById('requestDeletionBtn');
                if (reqBtn) reqBtn.addEventListener('click', () => this.openDeletionRequestForm());

                // Close modals on overlay click
                document.querySelectorAll('.modal-overlay').forEach(overlay => {
                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            this.closeCurrentModal();
                        }
                    });
                });

                // Initialize filter tabs
                this.initFilterTabs();

                // Initialize search
                this.initSearch();

                // Load initial data
                this.fetchBooksData();

                // Refresh stats
                this.refreshStats();

                // Attach cover preview handlers (if inputs exist)
                const addCoverInput = document.getElementById('addCover');
                if (addCoverInput) {
                    addCoverInput.addEventListener('change', (e) => {
                        const file = e.target.files[0];
                        const preview = document.getElementById('addCoverPreview');
                        const placeholder = document.getElementById('addCoverPlaceholder');
                        if (file) {
                            preview.src = URL.createObjectURL(file);
                            preview.style.display = 'block';
                            placeholder.style.display = 'none';
                        } else {
                            preview.src = '';
                            preview.style.display = 'none';
                            placeholder.style.display = 'block';
                        }
                    });
                }

                const editCoverInput = document.getElementById('editCover');
                if (editCoverInput) {
                    editCoverInput.addEventListener('change', (e) => {
                        const file = e.target.files[0];
                        const preview = document.getElementById('editCoverPreview');
                        const placeholder = document.getElementById('editCoverPlaceholder');
                        if (file) {
                            preview.src = URL.createObjectURL(file);
                            preview.style.display = 'block';
                            placeholder.style.display = 'none';
                        } else {
                            preview.src = '';
                            preview.style.display = 'none';
                            placeholder.style.display = 'block';
                        }
                    });
                }
            }

            initFilterTabs() {
                document.querySelectorAll('.filter-tab').forEach(tab => {
                    tab.addEventListener('click', (e) => {
                        document.querySelectorAll('.filter-tab').forEach(t => {
                            t.style.color = '#6b7280';
                            t.style.borderBottom = 'none';
                        });
                        e.target.style.color = '#3b82f6';
                        e.target.style.borderBottom = '2px solid #3b82f6';

                        this.currentConditionFilter = e.target.dataset.condition;
                        this.fetchBooksData(1);
                    });
                });
            }

            initSearch() {
                const searchInput = document.getElementById('searchInput');
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(this.searchDebounceTimer);
                    this.searchDebounceTimer = setTimeout(() => {
                        this.fetchBooksData(1);
                    }, 300);
                });
            }

            fetchBooksData(page = 1) {
                const searchTerm = document.getElementById('searchInput').value;
                const condition = this.currentConditionFilter;

                fetch(`{{ route('staff.books.data') }}?search=${encodeURIComponent(searchTerm)}&condition=${condition}&page=${page}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Check if there are any results
                            if (data.total === 0) {
                                document.getElementById('booksTableBody').innerHTML = `
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px; color: #6b7280;">
                                        <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block;"></i>
                                        <p style="font-size: 16px; margin: 0; font-weight: 500;">No search results found</p>
                                        <p style="font-size: 14px; margin-top: 8px; color: #9ca3af;">Try adjusting your search terms</p>
                                    </td>
                                </tr>
                            `;
                                document.getElementById('paginationContainer').innerHTML = '';
                            } else {
                                document.getElementById('booksTableBody').innerHTML = data.tableRows;
                                document.getElementById('paginationContainer').innerHTML = data.pagination;
                                this.attachTableEventListeners();
                            }
                            const bookCountSpan = document.getElementById('bookCountDisplay');
                            if (bookCountSpan) {
                                bookCountSpan.textContent = `${data.total} book${data.total !== 1 ? 's' : ''}`;
                            }
                        } else {
                            this.showNotification('Error loading books', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching books:', error);
                        this.showNotification('Error loading books', 'error');
                    });
            }

            attachTableEventListeners() {
                document.querySelectorAll('.action-btn.view').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const row = e.target.closest('tr');
                        this.currentBookId = row.dataset.bookId;
                        this.currentBookTitle = row.cells[1].querySelector('strong').textContent;
                        this.openViewModal(row);
                    });
                });

                document.querySelectorAll('.action-btn.edit').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const row = e.target.closest('tr');
                        this.currentBookId = row.dataset.bookId;
                        this.currentBookTitle = row.cells[1].querySelector('strong').textContent;
                        this.openEditModal(row);
                    });
                });

                document.querySelectorAll('.action-btn.delete').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const row = e.target.closest('tr');
                        this.currentBookId = row.dataset.bookId;
                        this.currentBookTitle = row.cells[1].querySelector('strong').textContent;
                        this.openDeleteModal();
                    });
                });

                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        this.fetchBooksData(page);
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    });
                });
            }

            refreshStats() {
                fetch(`{{ route('staff.books.stats') }}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const totalBooksElement = document.querySelector('.stat-total .stat-value');
                        if (totalBooksElement) totalBooksElement.textContent = data.totalBooks;

                        const totalCopiesElement = document.querySelector('.stat-available .stat-value');
                        if (totalCopiesElement) totalCopiesElement.textContent = data.totalCopies;

                        const availableElement = document.querySelector('.stat-borrowed .stat-value');
                        if (availableElement) availableElement.textContent = data.availableCopies;

                        const categoriesElement = document.querySelector('.stat-categories .stat-value');
                        if (categoriesElement) categoriesElement.textContent = Object.keys(data.topCategories)
                            .length;
                    })
                    .catch(error => console.error('Error fetching stats:', error));
            }

            openModal(modalId) {
                this.currentModal = modalId;

                // Refresh categories if opening the add book modal
                if (modalId === 'addBookModal') {
                    this.refreshCategoriesDropdown();
                }

                const modal = document.getElementById(modalId);
                modal.classList.add('active');
                setTimeout(() => {
                    const firstInput = modal.querySelector('input, select, textarea');
                    if (firstInput) firstInput.focus();
                }, 100);
            }

            refreshCategoriesDropdown() {
                fetch('{{ route('staff.books.index') }}', {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Parse the HTML to extract categories
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');

                        // Get the category options from the parsed document
                        const sourceSelect = doc.querySelector('#addCategorySelect');
                        if (sourceSelect) {
                            const targetSelect = document.getElementById('addCategorySelect');
                            // Get all options except the first one (placeholder)
                            const options = sourceSelect.querySelectorAll('option');

                            // Clear existing options but keep the first one
                            while (targetSelect.options.length > 1) {
                                targetSelect.remove(1);
                            }

                            // Add fresh options
                            options.forEach((option, index) => {
                                if (index > 0) { // Skip the placeholder option
                                    const newOption = option.cloneNode(true);
                                    targetSelect.appendChild(newOption);
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing categories:', error);
                    });
            }

            closeModal(modalId) {
                document.getElementById(modalId).classList.remove('active');
                this.currentModal = null;

                // Reset delete modal UI when closed
                if (modalId === 'deleteBookModal') {
                    const title = document.getElementById('deleteBookModalTitle');
                    const message = document.getElementById('deleteBookMessage');
                    const confirmBtn = document.getElementById('confirmDeleteBook');
                    const cancelBtn = document.getElementById('cancelDeleteBook');
                    const reqForm = document.getElementById('deletionRequestForm');
                    const reqBtn = document.getElementById('requestDeletionBtn');
                    if (title) title.textContent = 'Delete Book';
                    if (message) message.textContent = 'Are you sure you want to delete this book?';
                    if (confirmBtn) confirmBtn.style.display = '';
                    if (cancelBtn) cancelBtn.textContent = 'Cancel';
                    if (reqForm) reqForm.style.display = 'none';
                    if (reqBtn) reqBtn.style.display = '';
                }
            }

            closeCurrentModal() {
                if (this.currentModal) {
                    this.closeModal(this.currentModal);
                }
            }

            openViewModal(row) {
                const cells = row.cells;
                const titleCell = cells[1];
                const dataset = row.dataset || {};
                const cover = dataset.cover || '';
                const publisher = titleCell.querySelector('div') ? titleCell.querySelector('div').textContent : '';
                const coverUrl = cover ? `${this.storageBase}/${cover}` : '';

                const detailsContent = `
                    <div style="display: grid; grid-template-columns: 180px 1fr; gap: 16px; align-items: start;">
                        <div>
                            <div class="book-cover-slot" style="width:100%; height:240px; border-radius:8px; overflow:hidden;"></div>
                        </div>
                        <div>
                            <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 4px;">${this.currentBookTitle}</h4>
                            <p style="color: #6b7280; margin-bottom: 6px; font-size: 12px;"><strong>Author:</strong> ${cells[2].textContent}</p>
                            <p style="color: #6b7280; margin-bottom: 0; font-size: 12px;"><strong>Category:</strong> ${cells[3].textContent}</p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
                                <div>
                                    <p style="color: #6b7280; font-size: 10px; margin-bottom: 2px;">ISBN</p>
                                    <p style="font-weight: 600; font-size: 12px;">${cells[0].textContent}</p>
                                </div>
                                <div>
                                    <p style="color: #6b7280; font-size: 10px; margin-bottom: 2px;">Rack Number</p>
                                    <p style="font-weight: 600; font-size: 12px;">${cells[4].textContent}</p>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px;">
                                <div>
                                    <p style="color: #6b7280; font-size: 10px; margin-bottom: 2px;">Total Copies</p>
                                    <p style="font-weight: 600; font-size: 12px;">${cells[5].querySelector('.copy-total').textContent}</p>
                                </div>
                                <div>
                                    <p style="color: #6b7280; font-size: 10px; margin-bottom: 2px;">Available Copies</p>
                                    <p style="font-weight: 600; font-size: 12px;">${cells[6].querySelector('.copy-available').textContent}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('bookDetailsContent').innerHTML = detailsContent;
                // Populate cover image using DOM to avoid inline onerror quoting issues
                const coverSlot = document.getElementById('bookDetailsContent').querySelector('.book-cover-slot');
                if (coverSlot) {
                    if (coverUrl) {
                        const imgEl = document.createElement('img');
                        imgEl.src = coverUrl;
                        imgEl.alt = this.currentBookTitle || '';
                        imgEl.style.width = '100%';
                        imgEl.style.height = '240px';
                        imgEl.style.objectFit = 'cover';
                        imgEl.style.borderRadius = '8px';
                        imgEl.addEventListener('error', function() {
                            coverSlot.innerHTML = `<div style="width:100%;height:240px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;font-size:48px;font-weight:700;">${(this.currentBookTitle||'').charAt(0).toUpperCase()}</div>`;
                        }.bind(this));
                        coverSlot.appendChild(imgEl);
                    } else {
                        coverSlot.innerHTML = `<div style="width:100%;height:240px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;font-size:48px;font-weight:700;">${this.currentBookTitle.charAt(0).toUpperCase()}</div>`;
                    }
                }
                this.openModal('viewBookModal');
            }

            openDeletionRequestForm() {
                const reqForm = document.getElementById('deletionRequestForm');
                const reqBtn = document.getElementById('requestDeletionBtn');
                const cancelBtn = document.getElementById('cancelDeleteBook');
                if (reqForm) reqForm.style.display = '';
                if (reqBtn) reqBtn.style.display = 'none';
                if (cancelBtn) cancelBtn.textContent = 'Cancel';

                // Add submit listener dynamically
                if (!this._deletionFormBound) {
                    const submitBtn = document.createElement('button');
                    submitBtn.className = 'btn btn-primary';
                    submitBtn.id = 'submitDeletionRequest';
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Request';
                    submitBtn.style.marginLeft = '8px';
                    const footer = document.querySelector('#deleteBookModal .modal-footer');
                    if (footer) footer.insertBefore(submitBtn, footer.querySelector('#confirmDeleteBook'));

                    submitBtn.addEventListener('click', () => this.submitDeletionRequest());
                    this._deletionFormBound = true;
                }
            }

            openEditModal(row) {
                const cells = row.cells;
                const titleCell = cells[1];
                const publisher = titleCell.querySelector('div') ? titleCell.querySelector('div').textContent : '';

                document.getElementById('editISBN').value = cells[0].textContent;
                document.getElementById('editTitle').value = titleCell.querySelector('strong').textContent;
                document.getElementById('editAuthor').value = cells[2].textContent;
                document.getElementById('editCategory').value = row.dataset.category;
                document.getElementById('editRack').value = cells[4].textContent;
                document.getElementById('editTotalCopies').value = cells[5].querySelector('.copy-total').textContent;
                document.getElementById('editAvailableCopies').value = cells[6].querySelector('.copy-available')
                    .textContent;
                document.getElementById('editCondition').value = row.dataset.condition;
                document.getElementById('editPublisher').value = publisher;
                // If the row has a cover path, show it in the edit preview
                const cover = row.dataset.cover || '';
                const editPreview = document.getElementById('editCoverPreview');
                const editPlaceholder = document.getElementById('editCoverPlaceholder');
                if (cover) {
                    editPreview.src = `${this.storageBase}/${cover}`;
                    editPreview.style.display = 'block';
                    if (editPlaceholder) editPlaceholder.style.display = 'none';
                } else {
                    if (editPreview) editPreview.style.display = 'none';
                    if (editPlaceholder) editPlaceholder.style.display = 'block';
                }

                this.openModal('editBookModal');
            }

            openDeleteModal() {
                // Show permission message for staff users instead of performing delete
                document.getElementById('deleteBookModalTitle').textContent = 'Permission Required';
                document.getElementById('deleteBookMessage').innerHTML =
                    `You do not have permission to delete "<strong>${this.currentBookTitle}</strong>". Only administrators can delete books. If you believe this is an error, please contact your administrator or submit a deletion request.`;
                // Hide the destructive confirm button (staff cannot delete) and reset request form
                const confirmBtn = document.getElementById('confirmDeleteBook');
                const cancelBtn = document.getElementById('cancelDeleteBook');
                const reqForm = document.getElementById('deletionRequestForm');
                const reqBtn = document.getElementById('requestDeletionBtn');
                if (confirmBtn) confirmBtn.style.display = 'none';
                if (cancelBtn) cancelBtn.textContent = 'Close';
                if (reqForm) reqForm.style.display = 'none';
                if (reqBtn) reqBtn.style.display = '';
                this.openModal('deleteBookModal');
            }

            submitAddBook() {
                const form = document.getElementById('addBookForm');
                const newCategoryInput = document.getElementById('addNewCategory');
                this.clearFieldErrors(form);
                const fieldOrder = ['isbn', 'shelf_no', 'title', 'author', 'publisher', 'category_id',
                    'new_category', 'condition', 'total_copies', 'available_copies', 'description',
                    'cover_image'
                ];
                const validationError = this.validateFormOneByOne(form, fieldOrder);
                if (validationError) {
                    this.showFieldError(validationError.input, validationError.error);
                    return;
                }

                const newCategory = newCategoryInput.value.trim();

                // If creating new category, we need to create it first
                if (newCategory) {
                    this.createNewCategory(newCategory, form);
                    return;
                }

                this.submitAddBookForm(form);
            }

            createNewCategory(categoryName, form) {
                fetch('{{ route('staff.books.categories.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            name: categoryName
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || `HTTP error! status: ${response.status}`);
                            }).catch(() => {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Category creation response:', data);
                        if (data.success && data.category) {
                            // Add the new category to the select dropdown in alphabetical order
                            const categorySelect = document.getElementById('addCategorySelect');
                            const newOption = document.createElement('option');
                            newOption.value = data.category.id;
                            newOption.textContent = data.category.name;

                            // Find the correct position to insert (alphabetically)
                            const options = Array.from(categorySelect.options);
                            let inserted = false;

                            for (let i = 1; i < options
                                .length; i++) { // Start from 1 to skip the first placeholder option
                                if (options[i].textContent.localeCompare(data.category.name) > 0) {
                                    categorySelect.insertBefore(newOption, options[i]);
                                    inserted = true;
                                    break;
                                }
                            }

                            // If not inserted, append at the end
                            if (!inserted) {
                                categorySelect.appendChild(newOption);
                            }

                            // Set the category_id to the new category and submit
                            categorySelect.value = data.category.id;

                            // Clear the new_category input
                            document.getElementById('addNewCategory').value = '';

                            this.showNotification(`Category "${categoryName}" created successfully`, 'success');
                            setTimeout(() => this.submitAddBookForm(form), 300);
                        } else {
                            this.showNotification(data.message || 'Failed to create category', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error creating category:', error.message);
                        this.showNotification(error.message || 'Error creating new category', 'error');
                    });
            }

            submitAddBookForm(form) {
                const formData = new FormData(form);

                // Remove new_category if it's empty to avoid sending it
                const newCategory = formData.get('new_category');
                if (!newCategory || newCategory.trim() === '') {
                    formData.delete('new_category');
                }

                console.log('Submitting book with category_id:', formData.get('category_id'), 'new_category:', formData
                    .get('new_category'));

                fetch('{{ route('staff.books.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => response.json().catch(() => {
                        throw new Error('Invalid JSON response');
                    }))
                    .then(data => {
                        if (data.errors && Object.keys(data.errors).length > 0) {
                            const firstErrorField = Object.keys(data.errors)[0];
                            const firstErrorMessage = data.errors[firstErrorField][0];
                            const input = form.querySelector(`[name="${firstErrorField}"]`);
                            if (input) {
                                this.showFieldError(input, firstErrorMessage);
                            } else {
                                this.showNotification(firstErrorMessage, 'error');
                            }
                            return;
                        }

                        console.log('Book submission response:', data);
                        if (data.success) {
                            this.showNotification('Book added successfully', 'success');
                            this.closeModal('addBookModal');
                            form.reset();
                            document.getElementById('addNewCategory').value = '';
                            this.fetchBooksData(1);
                            setTimeout(() => this.refreshStats(), 150);
                        } else {
                            this.showNotification(data.message || 'Error adding book', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error.message);
                        this.showNotification(error.message || 'Error adding book', 'error');
                    });
            }

            submitEditBook() {
                const form = document.getElementById('editBookForm');
                this.clearFieldErrors(form);
                const fieldOrder = ['isbn', 'shelf_no', 'title', 'author', 'publisher', 'category_id',
                    'condition', 'total_copies', 'available_copies', 'description', 'cover_image'
                ];
                const validationError = this.validateFormOneByOne(form, fieldOrder);
                if (validationError) {
                    this.showFieldError(validationError.input, validationError.error);
                    return;
                }

                const formData = new FormData(form);
                formData.append('_method', 'PUT');

                fetch(`{{ url('staff/books') }}/${this.currentBookId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => response.json().catch(() => {
                        throw new Error('Invalid JSON response');
                    }))
                    .then(data => {
                        if (data.errors && Object.keys(data.errors).length > 0) {
                            const firstErrorField = Object.keys(data.errors)[0];
                            const firstErrorMessage = data.errors[firstErrorField][0];
                            const input = form.querySelector(`[name="${firstErrorField}"]`);
                            if (input) {
                                this.showFieldError(input, firstErrorMessage);
                            } else {
                                this.showNotification(firstErrorMessage, 'error');
                            }
                            return;
                        }

                        if (data.success) {
                            this.showNotification('Book updated successfully', 'success');
                            this.closeModal('editBookModal');
                            this.fetchBooksData(1);
                            setTimeout(() => this.refreshStats(), 150);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification('Error updating book', 'error');
                    });
            }

            confirmDeleteBook() {
                // Staff users cannot delete books. Show permission message instead.
                this.showNotification(
                    'You do not have permission to delete books. Only administrators can perform deletions.',
                    'warning');
                this.closeModal('deleteBookModal');
            }

            submitDeletionRequest() {
                const reason = document.getElementById('deletionReason').value.trim();
                fetch(`{{ url('staff/books') }}/${this.currentBookId}/request-deletion`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reason
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.showNotification('Deletion request submitted', 'success');
                            this.closeModal('deleteBookModal');
                        } else {
                            this.showNotification(data.message || 'Failed to submit deletion request', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Deletion request error:', err);
                        this.showNotification('Error submitting deletion request', 'error');
                    });
            }

            showNotification(message, type = 'info') {
                document.querySelectorAll('.notification').forEach(n => n.remove());

                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    ${message}
                    <button class="notification-close">
                        <i class="fas fa-times"></i>
                    </button>
                `;

                document.body.appendChild(notification);

                notification.querySelector('.notification-close').addEventListener('click', () => {
                    notification.remove();
                });

                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            }

            clearFieldError(input) {
                input.classList.remove('error');
                const errorElement = input.parentElement.querySelector('.field-error-message');
                if (errorElement) {
                    errorElement.remove();
                }
            }

            clearFieldErrors(form) {
                form.querySelectorAll('.form-control').forEach(input => {
                    input.classList.remove('error');
                    const existingError = input.parentElement.querySelector('.field-error-message');
                    if (existingError) {
                        existingError.remove();
                    }
                });
            }

            showFieldError(input, message) {
                this.clearFieldError(input);
                input.classList.add('error');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error-message';
                errorDiv.textContent = message;

                input.parentElement.appendChild(errorDiv);
                input.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                input.focus();
            }

            validateField(input, rules) {
                const value = input.value.trim();
                const type = input.type;
                const isRequired = input.hasAttribute('required');

                if (!isRequired && value === '') {
                    return null;
                }

                if (rules.required && value === '') {
                    return rules.requiredMessage || 'This field is required';
                }

                if (rules.minLength && value.length < rules.minLength) {
                    return rules.minLengthMessage || `Must be at least ${rules.minLength} characters`;
                }

                if (rules.maxLength && value.length > rules.maxLength) {
                    return rules.maxLengthMessage || `Must not exceed ${rules.maxLength} characters`;
                }

                let processedValue = value;
                if (rules.transform) {
                    processedValue = rules.transform(value);
                }

                if (rules.numeric && processedValue !== '' && isNaN(processedValue)) {
                    return rules.numericMessage || 'Must be a valid number';
                }

                if (rules.min !== undefined && processedValue !== '' && parseFloat(processedValue) < rules.min) {
                    return rules.minMessage || `Must be at least ${rules.min}`;
                }

                if (rules.max !== undefined && processedValue !== '' && parseFloat(processedValue) > rules.max) {
                    return rules.maxMessage || `Must not exceed ${rules.max}`;
                }

                if (rules.pattern && processedValue !== '') {
                    if (!rules.pattern.test(processedValue)) {
                        return rules.patternMessage || 'Invalid format';
                    }
                }

                if (rules.custom) {
                    const customError = rules.custom(processedValue, input.closest('form'));
                    if (customError) {
                        return customError;
                    }
                }

                if (rules.accept && type === 'file' && value !== '') {
                    const file = input.files[0];
                    if (file) {
                        const acceptedTypes = rules.accept.split(',').map(t => t.trim());
                        const fileType = file.type;
                        const fileName = file.name.toLowerCase();
                        const isValid = acceptedTypes.some(acceptedType => {
                            if (acceptedType.startsWith('.')) {
                                return fileName.endsWith(acceptedType);
                            }

                            return fileType.includes(acceptedType.replace('*', ''));
                        });

                        if (!isValid) {
                            return rules.acceptMessage || `File type not allowed. Accepted: ${rules.accept}`;
                        }
                    }
                }

                if (rules.maxSize && type === 'file' && value !== '') {
                    const file = input.files[0];
                    if (file) {
                        const maxBytes = rules.maxSize * 1024 * 1024;
                        if (file.size > maxBytes) {
                            return rules.maxSizeMessage || `File size must not exceed ${rules.maxSize}MB`;
                        }
                    }
                }

                return null;
            }

            validateFormOneByOne(form, fieldOrder) {
                const fieldRules = {
                    isbn: {
                        required: true,
                        requiredMessage: 'ISBN is required',
                        pattern: /^[0-9]{10,13}$/,
                        patternMessage: 'ISBN must be 10-13 digits only (e.g., 9780134685991)',
                        transform: (value) => value.replace(/[-\s]/g, '')
                    },
                    shelf_no: {
                        required: true,
                        requiredMessage: 'Rack number is required',
                        pattern: /^[A-Za-z0-9]+[-]?[A-Za-z0-9]*$/,
                        patternMessage: 'Rack number must be alphanumeric (e.g., A-12, B5, Shelf1)',
                        maxLength: 20,
                        maxLengthMessage: 'Rack number must not exceed 20 characters'
                    },
                    title: {
                        required: true,
                        requiredMessage: 'Title is required',
                        minLength: 2,
                        minLengthMessage: 'Title must be at least 2 characters',
                        maxLength: 255,
                        maxLengthMessage: 'Title must not exceed 255 characters',
                        pattern: /^[A-Za-z0-9\s\-:'.&()]+$/,
                        patternMessage: 'Title can only contain letters, numbers, spaces, and: - : \' . & ( )'
                    },
                    author: {
                        required: true,
                        requiredMessage: 'Author is required',
                        minLength: 2,
                        minLengthMessage: 'Author name must be at least 2 characters',
                        maxLength: 255,
                        maxLengthMessage: 'Author name must not exceed 255 characters',
                        pattern: /^[A-Za-z\s.]+$/,
                        patternMessage: 'Author name can only contain letters, spaces, and periods'
                    },
                    publisher: {
                        maxLength: 255,
                        maxLengthMessage: 'Publisher name must not exceed 255 characters',
                        pattern: /^[A-Za-z0-9\s&.,'-]*$/,
                        patternMessage: 'Publisher can only contain letters, numbers, spaces, and: & . , \' -'
                    },
                    category_id: {
                        custom: (value, currentForm) => {
                            const newCategoryInput = currentForm?.querySelector('[name="new_category"]');
                            const newCategoryValue = newCategoryInput ? newCategoryInput.value.trim() : '';

                            if (!value && !newCategoryValue) {
                                return 'Please select a category or create a new one';
                            }

                            if (value && newCategoryValue) {
                                return 'Please choose either existing OR new category, not both';
                            }

                            return null;
                        }
                    },
                    new_category: {
                        minLength: 2,
                        minLengthMessage: 'Category name must be at least 2 characters',
                        maxLength: 50,
                        maxLengthMessage: 'Category name must not exceed 50 characters',
                        pattern: /^[A-Za-z\s&]*$/,
                        patternMessage: 'Category name can only contain letters, spaces, and &'
                    },
                    condition: {
                        required: true,
                        requiredMessage: 'Condition is required',
                        custom: (value) => {
                            if (!['new', 'good', 'damaged'].includes(value)) {
                                return 'Condition must be new, good, or damaged';
                            }

                            return null;
                        }
                    },
                    total_copies: {
                        required: true,
                        requiredMessage: 'Total copies is required',
                        numeric: true,
                        numericMessage: 'Total copies must be a valid number',
                        min: 1,
                        minMessage: 'Total copies must be at least 1',
                        max: 9999,
                        maxMessage: 'Total copies must not exceed 9999'
                    },
                    available_copies: {
                        required: true,
                        requiredMessage: 'Available copies is required',
                        numeric: true,
                        numericMessage: 'Available copies must be a valid number',
                        min: 0,
                        minMessage: 'Available copies cannot be negative',
                        max: 9999,
                        maxMessage: 'Available copies must not exceed 9999',
                        custom: (value, currentForm) => {
                            const totalCopies = parseInt(currentForm?.querySelector('[name="total_copies"]')
                                ?.value || 0, 10);
                            const available = parseInt(value, 10);
                            if (available > totalCopies) {
                                return 'Available copies cannot exceed total copies';
                            }
                            return null;
                        }
                    },
                    description: {
                        maxLength: 2000,
                        maxLengthMessage: 'Description must not exceed 2000 characters'
                    },
                    cover_image: {
                        maxSize: 2,
                        maxSizeMessage: 'Cover image must not exceed 2MB',
                        accept: '.jpeg,.jpg,.png,.gif,.svg',
                        acceptMessage: 'Please select a valid image file (JPEG, PNG, JPG, GIF, SVG)'
                    }
                };

                for (const fieldName of fieldOrder) {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (input && fieldRules[fieldName]) {
                        const error = this.validateField(input, fieldRules[fieldName]);
                        if (error) {
                            return {
                                field: fieldName,
                                error: error,
                                input: input
                            };
                        }
                    }
                }

                return null;
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new BookManager();
        });
    </script>
@endpush
