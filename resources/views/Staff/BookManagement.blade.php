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
                padding: 0;
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const p = BookManager.prototype;
            p.submitAddBook = function() {
                const form = document.getElementById('addBookForm');
                const newCategoryInput = document.getElementById('addNewCategory');
                this.validateBookForm(form, { showErrors: true }).then((isValid) => {
                    if (!isValid) return;
                    const newCategory = newCategoryInput.value.trim();
                    if (newCategory) { this.createNewCategory(newCategory, form); return; }
                    this.submitAddBookForm(form);
                });
            };
            p.createNewCategory = function(categoryName, form) {
                fetch('{{ route('staff.categories.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name: categoryName })
                })
                .then((response) => {
                    if (!response.ok) {
                        return response.json().then((data) => { throw new Error(data.message || `HTTP error! status: ${response.status}`); }).catch(() => { throw new Error(`HTTP error! status: ${response.status}`); });
                    }
                    return response.json();
                })
                .then((data) => {
                    if (!(data.success && data.category)) { this.showNotification(data.message || 'Failed to create category', 'error'); return; }
                    const categorySelect = document.getElementById('addCategorySelect');
                    const newOption = document.createElement('option');
                    newOption.value = data.category.id;
                    newOption.textContent = data.category.name;
                    const options = Array.from(categorySelect.options);
                    let inserted = false;
                    for (let i = 1; i < options.length; i++) {
                        if (options[i].textContent.localeCompare(data.category.name) > 0) { categorySelect.insertBefore(newOption, options[i]); inserted = true; break; }
                    }
                    if (!inserted) categorySelect.appendChild(newOption);
                    categorySelect.value = data.category.id;
                    document.getElementById('addNewCategory').value = '';
                    this.setBookFieldState(categorySelect, 'valid');
                    this.setBookFieldState(document.getElementById('addNewCategory'), 'valid');
                    this.showNotification(`Category "${categoryName}" created successfully`, 'success');
                    setTimeout(() => this.submitAddBookForm(form), 300);
                })
                .catch((error) => {
                    console.error('Error creating category:', error.message);
                    this.showNotification(error.message || 'Error creating new category', 'error');
                });
            };
            p.submitAddBookForm = function(form) {
                const formData = new FormData(form);
                const newCategory = formData.get('new_category');
                if (!newCategory || newCategory.trim() === '') formData.delete('new_category');
                fetch('{{ route('staff.books.store') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: formData
                })
                .then((response) => response.json().catch(() => { throw new Error('Invalid JSON response'); }))
                .then((data) => {
                    if (data.errors && Object.keys(data.errors).length > 0) { this.applyBookServerErrors(form, data.errors); return; }
                    if (!data.success) { this.showNotification(data.message || 'Error adding book', 'error'); return; }
                    this.showNotification('Book added successfully', 'success');
                    this.closeModal('addBookModal');
                    form.reset();
                    document.getElementById('addNewCategory').value = '';
                    this.currentPage = 1;
                    this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                    this.fetchBooksData(1);
                })
                .catch((error) => { console.error('Error:', error.message); this.showNotification(error.message || 'Error adding book', 'error'); });
            };
            p.submitEditBook = function() {
                const form = document.getElementById('editBookForm');
                this.validateBookForm(form, { showErrors: true }).then((isValid) => {
                    if (!isValid) return;
                    const formData = new FormData(form);
                    formData.append('_method', 'PUT');
                    fetch(`{{ url('staff/books') }}/${this.currentBookId}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        body: formData
                    })
                    .then((response) => response.json().catch(() => { throw new Error('Invalid JSON response'); }))
                    .then((data) => {
                        if (data.errors && Object.keys(data.errors).length > 0) { this.applyBookServerErrors(form, data.errors); return; }
                        if (!data.success) { this.showNotification(data.message || 'Error updating book', 'error'); return; }
                        this.showNotification('Book updated successfully', 'success');
                        this.closeModal('editBookModal');
                        this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                        this.fetchBooksData(this.currentPage);
                    })
                    .catch((error) => { console.error('Error:', error); this.showNotification('Error updating book', 'error'); });
                });
            };
            p.confirmDeleteBook = function() {
                const confirmBtn = document.getElementById('confirmDeleteBook');
                const reasonInput = document.getElementById('deletionReason');
                const reasonError = document.getElementById('deletionReasonError');
                const reason = reasonInput?.value.trim() || '';
                if (reasonInput) reasonInput.classList.remove('has-error');
                if (reasonError) {
                    reasonError.hidden = true;
                    reasonError.textContent = 'Please enter a reason for this deletion request.';
                }
                if (!confirmBtn || !this.currentBookId) return;
                if (!reason) {
                    if (reasonInput) {
                        reasonInput.classList.add('has-error');
                        reasonInput.focus();
                    }
                    if (reasonError) reasonError.hidden = false;
                    return;
                }
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                fetch(`{{ url('staff/books') }}/${this.currentBookId}/request-deletion`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ reason })
                })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(data.message || 'Failed to submit deletion request');
                    return data;
                })
                .then((data) => {
                    this.showNotification(data.message || 'Deletion request submitted', 'success');
                    this.closeModal('deleteBookModal');
                })
                .catch((error) => {
                    console.error('Deletion request error:', error);
                    if (reasonInput) reasonInput.classList.add('has-error');
                    if (reasonError) {
                        reasonError.hidden = false;
                        reasonError.textContent = error.message || 'Error submitting deletion request';
                    }
                    this.showNotification(error.message || 'Error submitting deletion request', 'error');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
                });
            };
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const p = BookManager.prototype;
            p.setupPaginationListeners = function() {
                document.querySelectorAll('#paginationContainer a').forEach((link) => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        this.currentPage = page;
                        this.fetchBooksData(page);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                });
            };
            p.attachTableEventListeners = function() {
                document.querySelectorAll('.action-btn.view').forEach((btn) => btn.addEventListener('click', (e) => {
                    const row = e.target.closest('tr');
                    this.currentBookId = row.dataset.bookId;
                    this.currentBookTitle = row.cells[1].querySelector('strong')?.textContent || row.cells[1].textContent;
                    this.openViewModal(row);
                }));
                document.querySelectorAll('.action-btn.edit').forEach((btn) => btn.addEventListener('click', (e) => {
                    const row = e.target.closest('tr');
                    this.currentBookId = row.dataset.bookId;
                    this.currentBookTitle = row.cells[1].querySelector('strong')?.textContent || row.cells[1].textContent;
                    this.openEditModal(row);
                }));
                document.querySelectorAll('.action-btn.delete').forEach((btn) => btn.addEventListener('click', (e) => {
                    const row = e.target.closest('tr');
                    this.currentBookId = row.dataset.bookId;
                    this.currentBookTitle = row.cells[1].querySelector('strong')?.textContent || row.cells[1].textContent;
                    this.currentBookISBN = row.cells[0].textContent || '';
                    this.openDeleteModal();
                }));
            };
            p.updateStats = function(stats) {
                const totalBooks = document.getElementById('totalBooksCount');
                const totalCopies = document.getElementById('totalCopiesCount');
                const availableCopies = document.getElementById('availableCopiesCount');
                const categories = document.getElementById('categoriesCount');
                if (totalBooks) totalBooks.textContent = stats?.totalBooks ?? 0;
                if (totalCopies) totalCopies.textContent = stats?.totalCopies ?? 0;
                if (availableCopies) availableCopies.textContent = stats?.availableCopies ?? 0;
                if (categories) categories.textContent = Object.keys(stats?.topCategories || {}).length;
            };
            p.fetchBooksData = function(page = 1) {
                const requestedPage = Number(page) || 1;
                this.currentPage = requestedPage;
                this.syncCurrentFiltersFromDom();
                const tableBody = document.getElementById('booksTableBody');
                const emptyState = document.getElementById('emptyState');
                const paginationContainer = document.getElementById('paginationContainer');
                const params = new URLSearchParams({ search: this.currentSearch, condition: this.currentConditionFilter, category: this.currentCategoryFilter, availability: this.currentAvailabilityFilter, sort: this.currentSortFilter, page: requestedPage });
                if (tableBody) tableBody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
                if (emptyState) emptyState.style.display = 'none';
                if (paginationContainer) paginationContainer.style.display = 'none';
                fetch(`{{ route('staff.books.data') }}?${params.toString()}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => { if (!response.ok) throw new Error('Network response was not ok'); return response.json(); })
                    .then((data) => {
                        if (!data.success) { this.showNotification('Error loading books', 'error'); return; }
                        if (data.last_page > 0 && requestedPage > data.last_page) { this.fetchBooksData(data.last_page); return; }
                        this.currentPage = Number(data.current_page) || requestedPage;
                        if (data.total === 0) {
                            tableBody.innerHTML = '';
                            emptyState.style.display = 'block';
                            paginationContainer.style.display = 'none';
                        } else {
                            tableBody.innerHTML = data.tableRows || '';
                            emptyState.style.display = 'none';
                            if (data.pagination && Number(data.last_page) > 1) {
                                paginationContainer.innerHTML = data.pagination;
                                paginationContainer.style.display = 'block';
                                this.setupPaginationListeners();
                            } else {
                                paginationContainer.style.display = 'none';
                            }
                            this.attachTableEventListeners();
                        }
                        if (data.stats) this.updateStats(data.stats);
                    })
                    .catch((error) => { console.error('Error fetching books:', error); this.showNotification('Error loading books', 'error'); });
            };
            p.refreshStats = function() {
                const params = new URLSearchParams({ search: this.currentSearch, condition: this.currentConditionFilter, category: this.currentCategoryFilter, availability: this.currentAvailabilityFilter });
                fetch(`{{ route('staff.books.stats') }}?${params.toString()}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then((response) => response.json())
                    .then((data) => this.updateStats(data))
                    .catch((error) => console.error('Error fetching stats:', error));
            };
            p.resolveCoverUrl = function(cover) { return !cover ? '' : (cover.startsWith('http') ? cover : `${this.storageBase}/${cover}`); };
            p.refreshCategoriesDropdown = function() {
                fetch('{{ route('staff.books.index') }}', { method: 'GET', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
                    .then((response) => response.text())
                    .then((html) => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const sourceSelect = doc.querySelector('#addCategorySelect');
                        if (!sourceSelect) return;
                        const targetSelect = document.getElementById('addCategorySelect');
                        const options = sourceSelect.querySelectorAll('option');
                        while (targetSelect.options.length > 1) targetSelect.remove(1);
                        options.forEach((option, index) => { if (index > 0) targetSelect.appendChild(option.cloneNode(true)); });
                    })
                    .catch((error) => console.error('Error refreshing categories:', error));
            };
            p.openModal = function(modalId) {
                this.currentModal = modalId;
                if (modalId === 'addBookModal') {
                    this.refreshCategoriesDropdown();
                    const form = document.getElementById('addBookForm');
                    if (form) { form.reset(); this.resetBookFormValidation(form); this.seedBookFormValidation(form); }
                }
                const modal = document.getElementById(modalId);
                modal.classList.add('active');
                setTimeout(() => { const firstInput = modal.querySelector('input, select, textarea'); if (firstInput) firstInput.focus(); }, 100);
            };
            p.closeModal = function(modalId) {
                document.getElementById(modalId).classList.remove('active');
                if (modalId === 'addBookModal') this.resetBookFormValidation(document.getElementById('addBookForm'));
                if (modalId === 'editBookModal') this.resetBookFormValidation(document.getElementById('editBookForm'));
                if (modalId === 'deleteBookModal') {
                    const title = document.getElementById('deleteBookModalTitle');
                    const message = document.getElementById('deleteBookMessage');
                    if (message) message.textContent = 'Staff accounts cannot permanently delete books. Send a deletion request to administrators for review.';
                    if (title) title.textContent = 'Request Book Deletion';
                    const reasonInput = document.getElementById('deletionReason');
                    const reasonError = document.getElementById('deletionReasonError');
                    if (reasonInput) {
                        reasonInput.value = '';
                        reasonInput.classList.remove('has-error');
                    }
                    if (reasonError) {
                        reasonError.hidden = true;
                        reasonError.textContent = 'Please enter a reason for this deletion request.';
                    }
                    const confirmBtn = document.getElementById('confirmDeleteBook');
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
                    }
                }
                this.currentModal = null;
            };
            p.closeCurrentModal = function() { if (this.currentModal) this.closeModal(this.currentModal); };
            p.openViewModal = function(row) {
                const cells = row.cells, d = row.dataset || {}, coverUrl = this.resolveCoverUrl(d.cover || '');
                const details = `
                    <div style="display: grid; grid-template-columns: 180px 1fr; gap: 16px; align-items: start;">
                        <div><div class="book-cover-slot" style="width:100%; height:240px; border-radius:8px; overflow:hidden;"></div></div>
                        <div>
                            <h3 style="margin:0 0 8px 0;font-size:18px;font-weight:700;">${this.currentBookTitle}</h3>
                            <p style="margin:0 0 8px 0;color:#6b7280;"><strong>Author:</strong> ${cells[2].textContent || ''}</p>
                            <p style="margin:0 0 8px 0;color:#6b7280;"><strong>Category:</strong> ${d.categoryName || cells[3].textContent || ''}</p>
                            <p style="margin:0 0 8px 0;color:#6b7280;"><strong>Publisher:</strong> ${d.publisher || 'Unknown'}</p>
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:8px;">
                                <div><p style="color:#6b7280;font-size:10px;margin:0 0 2px;">ISBN</p><p style="font-weight:600;margin:0;">${d.isbn || cells[0].textContent || ''}</p></div>
                                <div><p style="color:#6b7280;font-size:10px;margin:0 0 2px;">Shelf / Rack</p><p style="font-weight:600;margin:0;">${d.shelf || cells[4].textContent || ''}</p></div>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:12px;">
                                <div><p style="color:#6b7280;font-size:10px;margin:0 0 2px;">Total Copies</p><p style="font-weight:600;margin:0;">${d.totalCopies || cells[5].querySelector('.copy-total')?.textContent || '0'}</p></div>
                                <div><p style="color:#6b7280;font-size:10px;margin:0 0 2px;">Available Copies</p><p style="font-weight:600;margin:0;">${d.availableCopies || cells[6].querySelector('.copy-available')?.textContent || '0'}</p></div>
                            </div>
                            <div style="margin-top:12px;"><p style="color:#6b7280;font-size:12px;margin:0 0 4px;">Description</p><p style="margin:0;color:#374151;line-height:1.5;font-size:14px;">${d.description ? d.description : '<em>No description available.</em>'}</p></div>
                        </div>
                    </div>`;
                document.getElementById('bookDetailsContent').innerHTML = details;
                const coverSlot = document.getElementById('bookDetailsContent').querySelector('.book-cover-slot');
                if (coverSlot) {
                    if (coverUrl) {
                        const img = document.createElement('img');
                        img.src = coverUrl; img.alt = this.currentBookTitle || ''; img.style.width = '100%'; img.style.height = '240px'; img.style.objectFit = 'cover'; img.style.borderRadius = '8px';
                        img.addEventListener('error', () => { coverSlot.innerHTML = `<div style="width:100%;height:240px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;font-size:48px;font-weight:700;">${(this.currentBookTitle || '').charAt(0).toUpperCase()}</div>`; });
                        coverSlot.appendChild(img);
                    } else coverSlot.innerHTML = `<div style="width:100%;height:240px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;font-size:48px;font-weight:700;">${(this.currentBookTitle || '').charAt(0).toUpperCase()}</div>`;
                }
                this.openModal('viewBookModal');
            };
            p.openEditModal = function(row) {
                document.getElementById('editISBN').value = row.cells[0].textContent;
                document.getElementById('editTitle').value = row.cells[1].querySelector('strong').textContent;
                document.getElementById('editAuthor').value = row.cells[2].textContent;
                document.getElementById('editCategory').value = row.dataset.categoryId || '';
                document.getElementById('editRack').value = row.dataset.shelf || row.cells[4].textContent;
                document.getElementById('editTotalCopies').value = row.dataset.totalCopies || row.cells[5].querySelector('.copy-total').textContent;
                document.getElementById('editAvailableCopies').value = row.dataset.availableCopies || row.cells[6].querySelector('.copy-available').textContent;
                document.getElementById('editCondition').value = row.dataset.condition || 'good';
                document.getElementById('editPublisher').value = row.dataset.publisher || '';
                document.getElementById('editDescription').value = row.dataset.description || '';
                const preview = document.getElementById('editCoverPreview');
                const placeholder = document.getElementById('editCoverPlaceholder');
                const coverUrl = this.resolveCoverUrl(row.dataset.cover || '');
                if (coverUrl) { preview.src = coverUrl; preview.style.display = 'block'; if (placeholder) placeholder.style.display = 'none'; }
                else { preview.src = ''; preview.style.display = 'none'; if (placeholder) placeholder.style.display = 'block'; }
                this.seedBookFormValidation(document.getElementById('editBookForm'));
                this.openModal('editBookModal');
            };
            p.openDeleteModal = function() {
                document.getElementById('deleteBookModalTitle').textContent = 'Request Book Deletion';
                document.getElementById('deleteBookMessage').textContent = 'Staff accounts cannot permanently delete books. Send a deletion request to administrators for review.';
                document.getElementById('deleteBookTitle').textContent = this.currentBookTitle || 'Unknown Book';
                document.getElementById('deleteBookISBN').textContent = `ISBN: ${this.currentBookISBN || 'N/A'}`;
                const reasonInput = document.getElementById('deletionReason');
                const reasonError = document.getElementById('deletionReasonError');
                if (reasonInput) {
                    reasonInput.value = '';
                    reasonInput.classList.remove('has-error');
                }
                if (reasonError) {
                    reasonError.hidden = true;
                    reasonError.textContent = 'Please enter a reason for this deletion request.';
                }
                this.openModal('deleteBookModal');
            };
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const p = BookManager.prototype;
            p.validateRemoteBookField = async function(form, input) {
                const state = this.getBookFormState(form);
                state.pending.add(input.name);
                this.setBookFieldState(input, 'pending');
                try {
                    const payload = { field: input.name, [input.name]: this.normalizeBookFieldValue(input.name, input.value), book_id: form.id === 'editBookForm' ? this.currentBookId : null };
                    const response = await fetch('{{ route('staff.books.validate-field') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });
                    state.pending.delete(input.name);
                    if (!response.ok) {
                        const data = await response.json();
                        this.setBookFieldState(input, 'error', data.message || 'This value is invalid.');
                        return false;
                    }
                    state.verified[input.name] = payload[input.name];
                    this.setBookFieldState(input, 'valid');
                    return true;
                } catch (error) {
                    state.pending.delete(input.name);
                    this.setBookFieldState(input, 'error', 'Could not verify this field right now. Please try again.');
                    return false;
                }
            };
            p.validateSingleBookField = async function(form, input, { showErrors = false, runRemote = false, activeInput = input } = {}) {
                const rules = this.getBookFieldRules()[input.name];
                if (input.name === 'category_id' || input.name === 'new_category') return this.validateCategoryState(form, { showErrors, activeInput });
                if (!rules) { input.dataset.valid = 'true'; this.updateBookSubmitState(form); return true; }
                const message = this.validateField(input, rules);
                if (message) { if (showErrors) this.setBookFieldState(input, 'error', message); else { input.dataset.valid = 'false'; this.updateBookSubmitState(form); } return false; }
                if (input.name === 'isbn') {
                    const normalized = this.normalizeBookFieldValue('isbn', input.value);
                    const state = this.getBookFormState(form);
                    if (runRemote) return this.validateRemoteBookField(form, input);
                    if (state.verified.isbn === normalized) { this.setBookFieldState(input, 'valid'); return true; }
                    this.setBookFieldState(input, 'neutral');
                    return false;
                }
                this.setBookFieldState(input, 'valid');
                return true;
            };
            p.validateBookForm = async function(form, { showErrors = true } = {}) {
                let isValid = true;
                let firstInvalidInput = null;
                for (const input of Array.from(form.querySelectorAll('input, select, textarea'))) {
                    const result = await this.validateSingleBookField(form, input, { showErrors, runRemote: input.name === 'isbn' && input.type !== 'file', activeInput: input });
                    isValid = result && isValid;
                    if (!result && !firstInvalidInput) firstInvalidInput = input;
                }
                if (!isValid && firstInvalidInput) this.focusBookField(firstInvalidInput);
                return isValid;
            };
            p.applyBookServerErrors = function(form, errors = {}) {
                if (!form || !errors || Object.keys(errors).length === 0) return;
                let firstInput = null;
                Object.entries(errors).forEach(([fieldName, messages]) => {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (!input) return;
                    this.setBookFieldState(input, 'error', Array.isArray(messages) ? messages[0] : messages);
                    if (!firstInput) firstInput = input;
                });
                if (firstInput) this.focusBookField(firstInput);
            };
            p.updateBookSubmitState = function(form) {
                if (!form) return;
                const button = this.getBookSubmitButton(form);
                if (!button) return;
                const state = this.getBookFormState(form);
                const invalidInputs = Array.from(form.querySelectorAll('.form-control')).some((input) => {
                    if (input.type === 'file') return input.files?.length ? input.dataset.valid !== 'true' : false;
                    if (['publisher', 'description'].includes(input.name)) return input.value.trim() !== '' && input.dataset.valid !== 'true';
                    if (input.name === 'new_category') {
                        const categorySelected = form.querySelector('[name="category_id"]')?.value.trim();
                        return input.dataset.valid !== 'true' && (!categorySelected || input.value.trim() !== '');
                    }
                    return input.dataset.valid !== 'true';
                });
                button.disabled = state.pending.size > 0 || invalidInputs;
            };
            p.initInputErrorListeners = function() {
                ['addBookForm', 'editBookForm'].forEach((formId) => {
                    const form = document.getElementById(formId);
                    if (!form) return;
                    this.ensureBookFieldIcons(form);
                    form.querySelectorAll('input, select, textarea').forEach((input) => {
                        const triggerEvent = input.type === 'file' || input.tagName === 'SELECT' ? 'change' : 'input';
                        input.addEventListener(triggerEvent, () => {
                            if (input.type !== 'file' && input.name !== 'isbn') {
                                const normalizedValue = this.normalizeBookFieldValue(input.name, input.value);
                                if (normalizedValue !== input.value) input.value = normalizedValue;
                            }
                            if (input.name === 'isbn') delete this.getBookFormState(form).verified.isbn;
                            this.validateSingleBookField(form, input, { showErrors: true, runRemote: false, activeInput: input });
                            if (input.name === 'total_copies') {
                                const availableInput = form.querySelector('[name="available_copies"]');
                                if (availableInput) this.validateSingleBookField(form, availableInput, { showErrors: false, runRemote: false, activeInput: input });
                            }
                        });
                        if (input.type !== 'file') {
                            input.addEventListener('blur', () => this.validateSingleBookField(form, input, { showErrors: true, runRemote: input.name === 'isbn', activeInput: input }));
                        }
                    });
                    this.updateBookSubmitState(form);
                });
            };
            p.resetBookFormValidation = function(form) {
                if (!form) return;
                const state = this.getBookFormState(form);
                state.pending.clear(); state.verified = {}; state.activeErrorField = null;
                form.querySelectorAll('.form-control').forEach((input) => {
                    input.classList.remove('error', 'valid', 'pending');
                    input.dataset.valid = 'false';
                    input.removeAttribute('aria-invalid');
                    input.closest('.form-group')?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                    const icon = input.closest('.form-group')?.querySelector('.field-validation-icon');
                    if (icon) icon.innerHTML = '';
                    const errorElement = input.parentElement.querySelector('.field-error-message');
                    if (errorElement) { errorElement.innerHTML = ''; errorElement.style.display = 'none'; }
                });
                ['add', 'edit'].forEach((prefix) => {
                    const preview = document.getElementById(`${prefix}CoverPreview`);
                    const placeholder = document.getElementById(`${prefix}CoverPlaceholder`);
                    if (preview) { preview.src = ''; preview.style.display = 'none'; }
                    if (placeholder) placeholder.style.display = 'block';
                });
                const coverInput = document.getElementById('editCover');
                if (coverInput) coverInput.value = '';
                this.updateBookSubmitState(form);
            };
            p.seedBookFormValidation = function(form) {
                if (!form) return;
                this.resetBookFormValidation(form);
                form.querySelectorAll('input, select, textarea').forEach((input) => {
                    if (input.type !== 'file' && input.name !== 'isbn') input.value = this.normalizeBookFieldValue(input.name, input.value);
                    if (input.name === 'category_id' || input.name === 'new_category') this.validateCategoryState(form, { showErrors: false });
                    else this.validateSingleBookField(form, input, { showErrors: false, runRemote: false });
                });
                form.querySelectorAll('.form-control').forEach((input) => this.clearBookFieldVisualState(input));
                this.updateBookSubmitState(form);
            };
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const p = BookManager.prototype;
            p.getBookFormState = function(form) { return this.bookFormStates[form.id]; };
            p.getBookSubmitButton = function(form) { return form.id === 'addBookForm' ? document.getElementById('submitAddBook') : document.getElementById('submitEditBook'); };
            p.getBookFieldGroup = function(input) { return input?.closest('.form-group') ?? null; };
            p.getBookFieldErrorElement = function(input, { createIfMissing = false } = {}) {
                if (!input) return null;
                const parent = input.parentElement;
                let errorElement = parent?.querySelector('.field-error-message') ?? null;
                if (!errorElement && createIfMissing && parent) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'field-error-message';
                    errorElement.setAttribute('role', 'alert');
                    parent.appendChild(errorElement);
                }
                return errorElement;
            };
            p.getBookFieldIcon = function(input) { return this.getBookFieldGroup(input)?.querySelector('.field-validation-icon') ?? null; };
            p.ensureBookFieldIcons = function(form) {
                form.querySelectorAll('input, select, textarea').forEach((input) => {
                    if (input.type === 'file') return;
                    const group = this.getBookFieldGroup(input);
                    if (!group || group.querySelector('.field-validation-icon')) return;
                    const icon = document.createElement('span');
                    icon.className = 'field-validation-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    group.appendChild(icon);
                });
            };
            p.clearDisplayedBookError = function(input) {
                if (!input) return;
                input.classList.remove('error');
                input.removeAttribute('aria-invalid');
                const errorElement = this.getBookFieldErrorElement(input);
                if (errorElement) {
                    errorElement.innerHTML = '';
                    errorElement.style.display = 'none';
                }
            };
            p.clearBookFieldVisualState = function(input) {
                if (!input) return;
                input.classList.remove('error', 'valid', 'pending');
                input.removeAttribute('aria-invalid');
                input.closest('.form-group')?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                const icon = this.getBookFieldIcon(input);
                if (icon) icon.innerHTML = '';
                const errorElement = this.getBookFieldErrorElement(input);
                if (errorElement) {
                    errorElement.innerHTML = '';
                    errorElement.style.display = 'none';
                }
            };
            p.focusBookField = function(input) {
                if (!input) return;
                const target = input.closest('.form-group') ?? input;
                const modal = input.closest('.modal');
                if (modal) {
                    const modalRect = modal.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();
                    const nextScrollTop = modal.scrollTop + (targetRect.top - modalRect.top) - (modal.clientHeight / 2) + (targetRect.height / 2);
                    modal.scrollTo({ top: Math.max(0, nextScrollTop), behavior: 'smooth' });
                } else {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                input.focus({ preventScroll: true });
            };
            p.normalizeBookFieldValue = function(fieldName, value) {
                const raw = String(value ?? '');
                if (fieldName === 'isbn') return raw.replace(/[\/\-\s]/g, '');
                if (['title', 'author', 'publisher', 'new_category', 'description'].includes(fieldName)) return raw.replace(/\s+/g, ' ').trim();
                if (fieldName === 'shelf_no') return raw.replace(/\s+/g, '').toUpperCase();
                return raw.trim();
            };
            p.getBookFieldRules = function() {
                return {
                    isbn: { required: true, pattern: /^\d{5,13}$/, requiredMessage: 'Enter the book ISBN.', patternMessage: 'ISBN must contain 5 to 13 digits. You may use / or - as separators.' },
                    shelf_no: { required: true, pattern: /^[A-Za-z0-9]+[-]?[A-Za-z0-9]*$/, maxLength: 20, requiredMessage: 'Enter the rack number.', patternMessage: 'Rack number must contain only letters, numbers, and an optional dash like A-12.', maxLengthMessage: 'Rack number must be 20 characters or fewer.' },
                    title: { required: true, minLength: 2, maxLength: 255, pattern: /^[A-Za-z0-9\s\-:'.&()]+$/, requiredMessage: 'Enter the book title.', minLengthMessage: 'Book title must be at least 2 characters long.', maxLengthMessage: 'Book title must be 255 characters or fewer.', patternMessage: 'Title can only contain letters, numbers, spaces, and - : \' . & ( ).' },
                    author: { required: true, minLength: 2, maxLength: 255, pattern: /^[A-Za-z\s.]+$/, requiredMessage: 'Enter the author name.', minLengthMessage: 'Author name must be at least 2 characters long.', maxLengthMessage: 'Author name must be 255 characters or fewer.', patternMessage: 'Author name can only contain letters, spaces, and periods.' },
                    publisher: { maxLength: 255, pattern: /^[A-Za-z0-9\s&.,'-]+$/, maxLengthMessage: 'Publisher name must be 255 characters or fewer.', patternMessage: 'Publisher name can only contain letters, numbers, spaces, and & . , \' -.' },
                    new_category: { minLength: 2, maxLength: 50, pattern: /^[A-Za-z\s&]+$/, minLengthMessage: 'New category name must be at least 2 characters long.', maxLengthMessage: 'New category name must be 50 characters or fewer.', patternMessage: 'Category name can only contain letters, spaces, and &.' },
                    total_copies: { required: true, numeric: true, min: 1, max: 9999, requiredMessage: 'Enter the total number of copies.', numericMessage: 'Total copies must be a whole number.', minMessage: 'Total copies must be at least 1.', maxMessage: 'Total copies must not exceed 9999.' },
                    available_copies: { required: true, numeric: true, min: 0, max: 9999, requiredMessage: 'Enter the available number of copies.', numericMessage: 'Available copies must be a whole number.', minMessage: 'Available copies cannot be negative.', maxMessage: 'Available copies must not exceed 9999.' },
                    condition: { required: true, requiredMessage: 'Select the book condition.' },
                    description: { maxLength: 2000, maxLengthMessage: 'Description must be 2000 characters or fewer.' },
                    cover_image: { accept: 'image/*', acceptMessage: 'Cover image must be an image file.', maxSize: 2, maxSizeMessage: 'Cover image must not exceed 2MB.' },
                };
            };
            p.setBookFieldState = function(input, state = 'neutral', message = '') {
                input.classList.remove('error', 'valid', 'pending');
                const form = input.closest('form');
                const group = this.getBookFieldGroup(input);
                const icon = this.getBookFieldIcon(input);
                const errorElement = this.getBookFieldErrorElement(input, { createIfMissing: state === 'error' });
                group?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                if (state === 'error') {
                    group?.classList.add('has-invalid');
                    input.classList.add('error');
                    input.setAttribute('aria-invalid', 'true');
                    input.dataset.valid = 'false';
                    errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                    errorElement.style.display = 'flex';
                    if (icon) icon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                } else {
                    input.removeAttribute('aria-invalid');
                    if (errorElement) { errorElement.innerHTML = ''; errorElement.style.display = 'none'; }
                    if (state === 'valid') {
                        group?.classList.add('has-valid');
                        input.classList.add('valid');
                        input.dataset.valid = 'true';
                        if (icon) icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    } else if (state === 'pending') {
                        group?.classList.add('has-pending');
                        input.classList.add('pending');
                        input.dataset.valid = 'false';
                        if (icon) icon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    } else {
                        input.dataset.valid = 'false';
                        if (icon) icon.innerHTML = '';
                    }
                }
                this.updateBookSubmitState(form);
            };
            p.clearFieldError = function(input, { clearValidityOnly = false } = {}) {
                this.clearDisplayedBookError(input);
                input.classList.remove('pending');
                this.getBookFieldGroup(input)?.classList.remove('has-invalid', 'has-pending');
                const icon = this.getBookFieldIcon(input);
                if (icon) icon.innerHTML = input.classList.contains('valid') ? '<i class="fas fa-check-circle"></i>' : '';
                if (!clearValidityOnly) {
                    this.getBookFieldGroup(input)?.classList.remove('has-valid');
                    input.classList.remove('valid');
                    input.dataset.valid = 'false';
                    if (icon) icon.innerHTML = '';
                }
            };
            p.validateField = function(input, rules) {
                const form = input.closest('form');
                const value = input.type === 'file' ? input.value : this.normalizeBookFieldValue(input.name, input.value);
                const isRequired = input.hasAttribute('required');
                if (input.type !== 'file' && input.name !== 'isbn') input.value = value;
                if (!isRequired && value === '') return null;
                if (rules.required && value === '') return rules.requiredMessage || 'This field is required.';
                if (rules.minLength && value.length < rules.minLength) return rules.minLengthMessage;
                if (rules.maxLength && value.length > rules.maxLength) return rules.maxLengthMessage;
                if (rules.pattern && value !== '' && !rules.pattern.test(value)) return rules.patternMessage;
                if (rules.numeric && value !== '' && (!/^\d+$/.test(value) || Number.isNaN(Number(value)))) return rules.numericMessage;
                if (rules.min !== undefined && value !== '' && Number(value) < rules.min) return rules.minMessage;
                if (rules.max !== undefined && value !== '' && Number(value) > rules.max) return rules.maxMessage;
                if (input.name === 'available_copies') {
                    const total = Number(form.querySelector('[name="total_copies"]')?.value || 0);
                    if (value !== '' && total && Number(value) > total) return 'Available copies cannot exceed total copies.';
                }
                if (rules.accept && input.type === 'file' && input.files?.length && !input.files[0].type.startsWith('image/')) return rules.acceptMessage;
                if (rules.maxSize && input.type === 'file' && input.files?.length && input.files[0].size > rules.maxSize * 1024 * 1024) return rules.maxSizeMessage;
                return null;
            };
            p.validateCategoryState = function(form, { showErrors = false, activeInput = null } = {}) {
                const categorySelect = form.querySelector('[name="category_id"]');
                const newCategoryInput = form.querySelector('[name="new_category"]');
                const target = activeInput && ['category_id', 'new_category'].includes(activeInput.name) ? activeInput : categorySelect;
                if (!categorySelect) return true;
                const selected = categorySelect.value.trim();
                if (!newCategoryInput) {
                    this.clearFieldError(categorySelect, { clearValidityOnly: true });
                    if (!selected) { if (showErrors) this.setBookFieldState(target, 'error', 'Select a valid category.'); else { categorySelect.dataset.valid = 'false'; this.updateBookSubmitState(form); } return false; }
                    this.setBookFieldState(categorySelect, 'valid'); return true;
                }
                const newCategory = this.normalizeBookFieldValue('new_category', newCategoryInput.value); newCategoryInput.value = newCategory;
                this.clearFieldError(categorySelect, { clearValidityOnly: true }); this.clearFieldError(newCategoryInput, { clearValidityOnly: true });
                if (!selected && !newCategory) { if (showErrors) this.setBookFieldState(target, 'error', 'Select an existing category or create a new one.'); else { categorySelect.dataset.valid = 'false'; newCategoryInput.dataset.valid = 'false'; this.updateBookSubmitState(form); } return false; }
                if (selected && newCategory) { if (showErrors) this.setBookFieldState(target, 'error', 'Choose either an existing category or a new category, not both.'); else { categorySelect.dataset.valid = 'false'; newCategoryInput.dataset.valid = 'false'; this.updateBookSubmitState(form); } return false; }
                if (newCategory) {
                    const message = this.validateField(newCategoryInput, this.getBookFieldRules().new_category);
                    if (message) { if (showErrors) this.setBookFieldState(newCategoryInput, 'error', message); else { newCategoryInput.dataset.valid = 'false'; this.updateBookSubmitState(form); } return false; }
                }
                this.setBookFieldState(categorySelect, selected ? 'valid' : 'neutral');
                this.setBookFieldState(newCategoryInput, newCategory ? 'valid' : 'neutral');
                return true;
            };
        });
    </script>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const p = BookManager.prototype;
            p.syncCurrentFiltersFromDom = function() {
                this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                this.currentConditionFilter = document.getElementById('conditionFilter')?.value || 'all';
                this.currentCategoryFilter = document.getElementById('categoryFilter')?.value || 'all';
                this.currentAvailabilityFilter = document.getElementById('availabilityFilter')?.value || 'all';
                this.currentSortFilter = document.getElementById('sortFilter')?.value || 'recently-added';
            };
            p.initCoverPreviews = function() {
                [['addCover', 'addCoverPreview', 'addCoverPlaceholder'], ['editCover', 'editCoverPreview', 'editCoverPlaceholder']].forEach(([inputId, previewId, placeholderId]) => {
                    const input = document.getElementById(inputId);
                    if (!input) return;
                    input.addEventListener('change', (e) => {
                        const file = e.target.files[0];
                        const preview = document.getElementById(previewId);
                        const placeholder = document.getElementById(placeholderId);
                        if (!preview || !placeholder) return;
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
                });
            };
            p.initFilters = function() {
                [['conditionFilter', 'currentConditionFilter'], ['categoryFilter', 'currentCategoryFilter'], ['availabilityFilter', 'currentAvailabilityFilter'], ['sortFilter', 'currentSortFilter']].forEach(([id, prop]) => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    el.addEventListener('change', (e) => {
                        this[prop] = e.target.value;
                        this.currentPage = 1;
                        this.fetchBooksData(1);
                    });
                });
            };
            p.initSearch = function() {
                const input = document.getElementById('searchInput');
                if (!input) return;
                input.addEventListener('input', (e) => {
                    clearTimeout(this.searchDebounceTimer);
                    this.searchDebounceTimer = setTimeout(() => {
                        this.currentSearch = e.target.value.trim();
                        this.currentPage = 1;
                        this.fetchBooksData(1);
                    }, 300);
                });
            };
            p.showNotification = function(message, type = 'info') {
                document.querySelectorAll('.notification').forEach((n) => n.remove());
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `${message}<button class="notification-close" type="button"><i class="fas fa-times"></i></button>`;
                document.body.appendChild(notification);
                notification.querySelector('.notification-close').addEventListener('click', () => notification.remove());
                setTimeout(() => {
                    if (notification.parentNode) notification.remove();
                }, 5000);
            };
            p.init = function() {
                this.currentBookISBN = null;
                this.currentSearch = '';
                this.currentCategoryFilter = 'all';
                this.currentAvailabilityFilter = 'all';
                this.currentSortFilter = 'recently-added';
                this.currentPage = Number(new URLSearchParams(window.location.search).get('page')) || 1;
                this.perPage = 15;
                this.bookFormStates = {
                    addBookForm: { pending: new Set(), verified: {}, activeErrorField: null },
                    editBookForm: { pending: new Set(), verified: {}, activeErrorField: null },
                };
                document.getElementById('addBookBtn')?.addEventListener('click', () => this.openModal('addBookModal'));
                document.getElementById('closeAddBookModal')?.addEventListener('click', () => this.closeModal('addBookModal'));
                document.getElementById('cancelAddBook')?.addEventListener('click', () => this.closeModal('addBookModal'));
                document.getElementById('submitAddBook')?.addEventListener('click', () => this.submitAddBook());
                document.getElementById('closeEditBookModal')?.addEventListener('click', () => this.closeModal('editBookModal'));
                document.getElementById('cancelEditBook')?.addEventListener('click', () => this.closeModal('editBookModal'));
                document.getElementById('submitEditBook')?.addEventListener('click', () => this.submitEditBook());
                document.getElementById('closeViewBookModal')?.addEventListener('click', () => this.closeModal('viewBookModal'));
                document.getElementById('closeViewBookBtn')?.addEventListener('click', () => this.closeModal('viewBookModal'));
                document.getElementById('closeDeleteBookModal')?.addEventListener('click', () => this.closeModal('deleteBookModal'));
                document.getElementById('cancelDeleteBook')?.addEventListener('click', () => this.closeModal('deleteBookModal'));
                document.getElementById('confirmDeleteBook')?.addEventListener('click', () => this.confirmDeleteBook());
                const deletionReasonInput = document.getElementById('deletionReason');
                if (deletionReasonInput && !deletionReasonInput.dataset.listenerBound) {
                    deletionReasonInput.addEventListener('input', () => {
                        deletionReasonInput.classList.remove('has-error');
                        const reasonError = document.getElementById('deletionReasonError');
                        if (reasonError) {
                            reasonError.hidden = true;
                            reasonError.textContent = 'Please enter a reason for this deletion request.';
                        }
                    });
                    deletionReasonInput.dataset.listenerBound = 'true';
                }
                document.querySelectorAll('.modal-overlay').forEach((overlay) => overlay.addEventListener('click', (e) => { if (e.target === overlay) this.closeCurrentModal(); }));
                this.initFilters();
                this.initSearch();
                this.syncCurrentFiltersFromDom();
                this.attachTableEventListeners();
                this.setupPaginationListeners();
                this.initInputErrorListeners();
                this.initCoverPreviews();
            };
        });
    </script>
@endpush

@push('styles')
    <style>
        .book-management {
            padding: 0;
        }

        .search-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            align-items: center;
            background: white;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .search-filter-container {
            background: #1e293b;
            border-color: #334155;
        }

        .search-box {
            flex: 0 0 auto;
            min-width: 200px;
            max-width: 250px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.25rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
            color: #0f172a;
        }

        body.dark-theme .search-input {
            background-color: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme .search-input:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 0.875rem;
            pointer-events: none;
        }

        body.dark-theme .search-icon {
            color: #94a3b8;
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            cursor: pointer;
            appearance: none;
            min-width: 120px;
            transition: all 0.3s ease;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .filter-select {
            background: #334155 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border-color: #475569;
            color: #e2e8f0;
        }

        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .search-add-wrapper {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-left: auto;
        }

        .toolbar,
        .book-count {
            display: none;
        }

        .table-container {
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-top: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
            font-size: 0.875rem;
        }

        .table thead {
            border-bottom: 1px solid #e5e7eb;
        }

        body.dark-theme .table thead {
            border-color: #334155;
        }

        .table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            white-space: nowrap;
        }

        body.light-theme .table th {
            background-color: #f8fafc;
            color: #64748b;
        }

        body.dark-theme .table th {
            background-color: #1e293b;
            color: #cbd5e1;
        }

        .table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        body.dark-theme .table td {
            border-color: #334155;
        }

        .table tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .table tr:hover {
            background-color: #334155;
        }

        .form-group {
            position: relative;
        }

        .form-control.valid {
            border-color: #16a34a !important;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12) !important;
        }

        .form-control.pending {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        }

        body.dark-theme .form-control.valid {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2) !important;
        }

        body.dark-theme .form-control.pending {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2) !important;
        }

        .field-validation-icon {
            position: absolute;
            right: 12px;
            top: 42px;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease, color 0.2s ease;
        }

        .form-group.has-valid .field-validation-icon,
        .form-group.has-invalid .field-validation-icon,
        .form-group.has-pending .field-validation-icon {
            opacity: 1;
        }

        .form-group.has-valid .field-validation-icon {
            color: #16a34a;
        }

        .form-group.has-invalid .field-validation-icon {
            color: #dc2626;
        }

        .form-group.has-pending .field-validation-icon {
            color: #2563eb;
        }

        body.dark-theme .form-group.has-valid .field-validation-icon {
            color: #22c55e;
        }

        body.dark-theme .form-group.has-invalid .field-validation-icon {
            color: #f87171;
        }

        body.dark-theme .form-group.has-pending .field-validation-icon {
            color: #60a5fa;
        }

        .confirmation-popup {
            text-align: center;
            padding: 8px 0;
        }

        .confirmation-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
        }

        .confirmation-icon.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #f59e0b;
        }

        .confirmation-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #0f172a;
        }

        body.dark-theme .confirmation-title {
            color: #f3f4f6;
        }

        .confirmation-message {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .confirmation-details {
            background: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        body.dark-theme .confirmation-details {
            background: #334155;
        }

        .confirmation-warning {
            display: flex;
            align-items: flex-start;
            background: #fef2f2;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            color: #991b1b;
            text-align: left;
        }

        body.dark-theme .confirmation-warning {
            background: #450a0a;
            color: #fca5a5;
        }

        .btn-confirm-danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
        }

        .btn-confirm-danger:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
        }

        #deleteBookModal .modal {
            max-width: 500px;
        }

        #deleteBookModal .modal-header {
            padding: 16px 18px;
        }

        #deleteBookModal .modal-body {
            padding: 16px 18px 12px;
        }

        #deleteBookModal .modal-footer {
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 18px 18px;
        }

        #deleteBookModal .confirmation-popup {
            text-align: left;
            padding: 0;
            display: grid;
            gap: 12px;
        }

        .delete-modal-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        #deleteBookModal .confirmation-icon {
            width: 52px;
            height: 52px;
            margin: 0;
            font-size: 21px;
            flex-shrink: 0;
        }

        .delete-modal-copy {
            min-width: 0;
        }

        #deleteBookModal .confirmation-title {
            font-size: 18px;
            margin: 0 0 4px;
        }

        #deleteBookModal .confirmation-message {
            margin: 0;
            line-height: 1.5;
        }

        #deleteBookModal .confirmation-details {
            margin-bottom: 0;
            padding: 10px 12px;
        }

        #deleteBookModal .confirmation-details strong {
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .delete-book-isbn {
            display: block;
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }

        body.dark-theme .delete-book-isbn {
            color: #94a3b8;
        }

        .delete-request-form {
            text-align: left;
            margin-top: 0;
        }

        .delete-request-form .form-label {
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .delete-request-textarea {
            min-height: 84px;
            resize: vertical;
        }

        #deleteBookModal .modal-footer .btn {
            min-width: 108px;
        }

        #deleteBookModal .modal-footer .btn-confirm-danger {
            min-width: 136px;
        }

        .delete-request-help {
            margin-top: 6px;
            margin-bottom: 0;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.45;
        }

        body.dark-theme .delete-request-help {
            color: #94a3b8;
        }

        .delete-request-error {
            margin-top: 6px;
            margin-bottom: 0;
            font-size: 12px;
            color: #dc2626;
            font-weight: 500;
        }

        body.dark-theme .delete-request-error {
            color: #fca5a5;
        }

        .delete-request-textarea.has-error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }

        @media (max-width: 768px) {
            .search-filter-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            .filters-container {
                width: 100%;
            }

            .search-add-wrapper {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>
@endpush

@section('content')
    <div class="book-management">
        <div class="page-header">
            <h1 class="page-title">Book Management</h1>
            <p class="page-description">Manage library book inventory</p>
        </div>

        <div class="stats-row">
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="totalBooksCount">{{ $initialStats['totalBooks'] ?? 0 }}</div>
                        <div class="stat-label">Total Books</div>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-available">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="totalCopiesCount">{{ $initialStats['totalCopies'] ?? 0 }}</div>
                        <div class="stat-label">Total Copies</div>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-borrowed">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="availableCopiesCount">{{ $initialStats['availableCopies'] ?? 0 }}</div>
                        <div class="stat-label">Available Copies</div>
                    </div>
                </div>
            </div>

            <div class="stat-card stat-categories">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="categoriesCount">{{ count($initialStats['topCategories'] ?? []) }}</div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="search-filter-container">
                <div class="search-box">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by title, author, ISBN..." value="{{ $search ?? '' }}" aria-label="Search books">
                </div>

                <div class="filters-container">
                    <select class="filter-select" id="conditionFilter" aria-label="Filter by condition">
                        <option value="all" {{ ($condition ?? 'all') === 'all' ? 'selected' : '' }}>All Conditions</option>
                        <option value="new" {{ ($condition ?? 'all') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="good" {{ ($condition ?? 'all') === 'good' ? 'selected' : '' }}>Good</option>
                        <option value="damaged" {{ ($condition ?? 'all') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                    </select>

                    <select class="filter-select" id="categoryFilter" aria-label="Filter by category">
                        <option value="all" {{ ($selectedCategory ?? 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}" {{ ($selectedCategory ?? 'all') === $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <select class="filter-select" id="availabilityFilter" aria-label="Filter by availability">
                        <option value="all" {{ ($availability ?? 'all') === 'all' ? 'selected' : '' }}>All Stock</option>
                        <option value="out-of-stock" {{ ($availability ?? 'all') === 'out-of-stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="low-stock" {{ ($availability ?? 'all') === 'low-stock' ? 'selected' : '' }}>Low Stock (1-5)</option>
                        <option value="in-stock" {{ ($availability ?? 'all') === 'in-stock' ? 'selected' : '' }}>In Stock (6+)</option>
                    </select>

                    <select class="filter-select" id="sortFilter" aria-label="Sort books">
                        <option value="recently-added" {{ ($sort ?? 'recently-added') === 'recently-added' ? 'selected' : '' }}>Recently Added</option>
                        <option value="title-asc" {{ ($sort ?? 'recently-added') === 'title-asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title-desc" {{ ($sort ?? 'recently-added') === 'title-desc' ? 'selected' : '' }}>Title (Z-A)</option>
                        <option value="author-asc" {{ ($sort ?? 'recently-added') === 'author-asc' ? 'selected' : '' }}>Author (A-Z)</option>
                        <option value="author-desc" {{ ($sort ?? 'recently-added') === 'author-desc' ? 'selected' : '' }}>Author (Z-A)</option>
                        <option value="copies-desc" {{ ($sort ?? 'recently-added') === 'copies-desc' ? 'selected' : '' }}>Copies (High to Low)</option>
                    </select>
                </div>

                <div class="search-add-wrapper">
                    <button class="btn btn-primary" id="addBookBtn" type="button">
                        <i class="fas fa-plus"></i>
                        Add New Book
                    </button>
                </div>
            </div>

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
                            @forelse($initialBooks as $book)
                                @php
                                    $conditionClass = $book->condition === 'new' ? 'condition-new' : ($book->condition === 'damaged' ? 'condition-damaged' : 'condition-good');
                                    $conditionIcon = $book->condition === 'new' ? 'fa-star' : ($book->condition === 'damaged' ? 'fa-exclamation-triangle' : 'fa-check-circle');
                                @endphp
                                <tr
                                    data-book-id="{{ $book->id }}"
                                    data-category-id="{{ $book->category->id ?? '' }}"
                                    data-category-name="{{ $book->category->name ?? 'N/A' }}"
                                    data-condition="{{ $book->condition }}"
                                    data-cover="{{ $book->cover_image ?? '' }}"
                                    data-description="{{ $book->description ?? '' }}"
                                    data-publisher="{{ $book->publisher ?? '' }}"
                                    data-isbn="{{ $book->isbn ?? '' }}"
                                    data-shelf="{{ $book->shelf_no ?? '' }}"
                                    data-total-copies="{{ $book->total_copies ?? 0 }}"
                                    data-available-copies="{{ $book->available_copies ?? 0 }}"
                                >
                                    <td>{{ $book->isbn }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            @if($book->cover_image)
                                                @php
                                                    $imageUrl = str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset('storage/' . $book->cover_image);
                                                @endphp
                                                <img src="{{ $imageUrl }}" alt="{{ $book->title }}" style="width: 40px; height: 50px; border-radius: 4px; object-fit: cover; border: 1px solid #e5e7eb;">
                                            @else
                                                <div style="width: 40px; height: 50px; min-width: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; border-radius: 4px; color: #ffffff; font-weight: 700; font-size: 20px; flex-shrink: 0;">
                                                    {{ strtoupper(substr($book->title, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <strong style="font-size: 13px;">{{ $book->title }}</strong>
                                                <div style="font-size: 10px; color: #9ca3af; margin-top: 1px;">{{ $book->publisher ?? 'Unknown' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $book->author }}</td>
                                    <td>{{ $book->category->name ?? 'N/A' }}</td>
                                    <td>{{ $book->shelf_no ?? 'N/A' }}</td>
                                    <td>
                                        <div class="copy-count">
                                            <span class="copy-total">{{ $book->total_copies }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="copy-count">
                                            <span class="copy-available">{{ $book->available_copies }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="condition-badge {{ $conditionClass }}">
                                            <i class="fas {{ $conditionIcon }}"></i>
                                            {{ ucfirst($book->condition) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn view" title="View Details" aria-label="View book details"><i class="fas fa-eye"></i></button>
                                            <button class="action-btn edit" title="Edit Book" aria-label="Edit book"><i class="fas fa-edit"></i></button>
                                            <button class="action-btn delete" title="Request Deletion" aria-label="Request book deletion"><i class="fas fa-trash-alt"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="emptyState" style="text-align: center; padding: 2rem 1rem; color: #64748b; display: {{ $initialBooks->total() === 0 ? 'block' : 'none' }};">
                    <svg style="margin-bottom: 0.75rem; opacity: 0.5; width: 40px; height: 40px; margin-left: auto; margin-right: auto;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                        <path d="M9 9h6"></path>
                        <path d="M9 13h6"></path>
                    </svg>
                    <h3 style="margin-bottom: 0.25rem; font-size: 1rem; font-weight: 600;">No books found</h3>
                    <p style="color: #64748b;">Try adjusting your search or filters</p>
                </div>

                <div id="paginationContainer" style="display: {{ $initialBooks->lastPage() > 1 ? 'block' : 'none' }};">
                    {!! $initialBooks->links()->toHtml() !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Book Modal -->
    <div id="addBookModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New Book</h3>
                <button class="modal-close-btn" id="closeAddBookModal" type="button" aria-label="Close add book dialog">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description">Enter the details of the new book to add to the library.</p>

                <form id="addBookForm" novalidate>
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
                <button class="modal-close-btn" id="closeEditBookModal" type="button" aria-label="Close edit book dialog">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description">Update book information and inventory details.</p>

                <form id="editBookForm" novalidate>
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
                <button class="modal-close-btn" id="closeViewBookModal" type="button" aria-label="Close book details dialog">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="bookDetailsContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="closeViewBookBtn" type="button">
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
                <h3 class="modal-title" id="deleteBookModalTitle">Request Book Deletion</h3>
                <button class="modal-close-btn" id="closeDeleteBookModal" type="button" aria-label="Close deletion request dialog">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirmation-popup">
                    <div class="delete-modal-header">
                        <div class="confirmation-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="delete-modal-copy">
                            <h3 class="confirmation-title">Send Deletion Request?</h3>
                            <p class="confirmation-message" id="deleteBookMessage">
                                Staff accounts cannot permanently delete books. Send a deletion request to administrators for review.
                            </p>
                        </div>
                    </div>
                    <div class="confirmation-details">
                        <strong id="deleteBookTitle">Loading...</strong>
                        <span id="deleteBookISBN" class="delete-book-isbn">loading...</span>
                    </div>
                    <div class="delete-request-form">
                        <label for="deletionReason" class="form-label required">Reason for deletion</label>
                        <textarea
                            id="deletionReason"
                            class="form-control delete-request-textarea"
                            rows="4"
                            maxlength="1000"
                            placeholder="Explain why this book should be removed, for example duplicate record, wrong entry, or damaged beyond repair."
                        ></textarea>
                        <p class="delete-request-help">Required. The book record will stay unchanged until an administrator reviews your request.</p>
                        <p class="delete-request-error" id="deletionReasonError" hidden>Please enter a reason for this deletion request.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="cancelDeleteBook" type="button">Cancel</button>
                <button class="btn btn-confirm-danger" id="confirmDeleteBook" type="button">
                    <i class="fas fa-paper-plane"></i>
                    Send Request
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
                this.currentBookISBN = null;
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
                const deletionReasonInput = document.getElementById('deletionReason');
                if (deletionReasonInput && !deletionReasonInput.dataset.listenerBound) {
                    deletionReasonInput.addEventListener('input', () => {
                        deletionReasonInput.classList.remove('has-error');
                        const reasonError = document.getElementById('deletionReasonError');
                        if (reasonError) {
                            reasonError.hidden = true;
                            reasonError.textContent = 'Please enter a reason for this deletion request.';
                        }
                    });
                    deletionReasonInput.dataset.listenerBound = 'true';
                }

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
                        this.currentBookISBN = row.cells[0].textContent || '';
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
                    const reasonInput = document.getElementById('deletionReason');
                    const reasonError = document.getElementById('deletionReasonError');
                    if (title) title.textContent = 'Request Book Deletion';
                    if (message) message.textContent = 'Staff accounts cannot permanently delete books. Send a deletion request to administrators for review.';
                    if (reasonInput) {
                        reasonInput.value = '';
                        reasonInput.classList.remove('has-error');
                    }
                    if (reasonError) {
                        reasonError.hidden = true;
                        reasonError.textContent = 'Please enter a reason for this deletion request.';
                    }
                    if (confirmBtn) {
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
                    }
                    if (cancelBtn) cancelBtn.textContent = 'Cancel';
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
                document.getElementById('deleteBookModalTitle').textContent = 'Request Book Deletion';
                document.getElementById('deleteBookMessage').textContent =
                    'Staff accounts cannot permanently delete books. Send a deletion request to administrators for review.';
                document.getElementById('deleteBookTitle').textContent = this.currentBookTitle || 'Unknown Book';
                const deleteBookIsbn = document.getElementById('deleteBookISBN');
                const reasonInput = document.getElementById('deletionReason');
                const reasonError = document.getElementById('deletionReasonError');
                if (deleteBookIsbn) {
                    deleteBookIsbn.textContent = this.currentBookISBN ? `ISBN: ${this.currentBookISBN}` : 'ISBN: N/A';
                }
                if (reasonInput) {
                    reasonInput.value = '';
                    reasonInput.classList.remove('has-error');
                }
                if (reasonError) {
                    reasonError.hidden = true;
                    reasonError.textContent = 'Please enter a reason for this deletion request.';
                }
                const confirmBtn = document.getElementById('confirmDeleteBook');
                const cancelBtn = document.getElementById('cancelDeleteBook');
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
                }
                if (cancelBtn) cancelBtn.textContent = 'Cancel';
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
                this.submitDeletionRequest();
            }

            submitDeletionRequest() {
                const confirmBtn = document.getElementById('confirmDeleteBook');
                const reasonInput = document.getElementById('deletionReason');
                const reasonError = document.getElementById('deletionReasonError');
                const reason = reasonInput?.value.trim() || '';
                if (!this.currentBookId || !confirmBtn) {
                    return;
                }

                if (reasonInput) reasonInput.classList.remove('has-error');
                if (reasonError) {
                    reasonError.hidden = true;
                    reasonError.textContent = 'Please enter a reason for this deletion request.';
                }
                if (!reason) {
                    if (reasonInput) {
                        reasonInput.classList.add('has-error');
                        reasonInput.focus();
                    }
                    if (reasonError) reasonError.hidden = false;
                    return;
                }

                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

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
                    .then(async response => {
                        const data = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to submit deletion request');
                        }
                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            this.showNotification('Deletion request submitted', 'success');
                            this.closeModal('deleteBookModal');
                        } else {
                            if (reasonInput) reasonInput.classList.add('has-error');
                            if (reasonError) {
                                reasonError.hidden = false;
                                reasonError.textContent = data.message || 'Failed to submit deletion request';
                            }
                            this.showNotification(data.message || 'Failed to submit deletion request', 'error');
                            confirmBtn.disabled = false;
                            confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
                        }
                    })
                    .catch(err => {
                        console.error('Deletion request error:', err);
                        if (reasonInput) reasonInput.classList.add('has-error');
                        if (reasonError) {
                            reasonError.hidden = false;
                            reasonError.textContent = err.message || 'Error submitting deletion request';
                        }
                        this.showNotification(err.message || 'Error submitting deletion request', 'error');
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Request';
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

            focusBookField(input) {
                if (!input) {
                    return;
                }

                const target = input.closest('.form-group') || input;
                const modal = input.closest('.modal');

                if (modal) {
                    const modalRect = modal.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();
                    const nextScrollTop = modal.scrollTop +
                        (targetRect.top - modalRect.top) -
                        (modal.clientHeight / 2) +
                        (targetRect.height / 2);

                    modal.scrollTo({
                        top: Math.max(0, nextScrollTop),
                        behavior: 'smooth'
                    });
                } else {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }

                input.focus({
                    preventScroll: true
                });
            }

            showFieldError(input, message) {
                this.clearFieldError(input);
                input.classList.add('error');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error-message';
                errorDiv.textContent = message;

                input.parentElement.appendChild(errorDiv);
                this.focusBookField(input);
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
                        pattern: /^[0-9]{5,13}$/,
                        patternMessage: 'ISBN must be 5-13 digits only. You may use / or - as separators (e.g., 978/013-4685991)',
                        transform: (value) => value.replace(/[\/\-\s]/g, '')
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
