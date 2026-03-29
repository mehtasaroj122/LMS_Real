<!-- resources/views/Admin/books/index.blade.php -->
@extends('Admin.layouts.app')

@section('title', 'Book Management')

@push('styles')
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Book Management Styles */
        .book-management {
            padding: 16px;
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

        /* Search & Filter Container (new design) */
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

        /* SEARCH BOX – width reduced */
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

        /* Filters Container */
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

        /* Toolbar */
        .toolbar {
            display: none;
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
            display: none;
        }

        /* Condition Badge */
        .condition-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .condition-new {
            background-color: #dcfce7;
            color: #166534;
        }

        body.dark-theme .condition-new {
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
            color: #4ade80;
        }

        .condition-good {
            background-color: #dbeafe;
            color: #1e40af;
        }

        body.dark-theme .condition-good {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #93c5fd;
        }

        .condition-damaged {
            background-color: #fee2e2;
            color: #991b1b;
        }

        body.dark-theme .condition-damaged {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        /* Table Styles */
        .table-container {
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            margin-top: 0;
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

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .table tr:hover {
            background-color: #334155;
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

        /* Form Error Styles - One Error at a Time */
        .form-control.error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        body.dark-theme .form-control.error {
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
            animation: slideDown 0.2s ease;
        }

        body.dark-theme .field-error-message {
            color: #f87171;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== Confirmation Popup Styles ===== */
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

        .confirmation-icon.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #ef4444;
        }

        .confirmation-icon.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #f59e0b;
        }

        .confirmation-icon.info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #3b82f6;
        }

        .confirmation-icon.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #10b981;
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

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            background: white;
        }

        body.dark-theme .pagination-container {
            background: #1e293b;
            border-top-color: #334155;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        body.dark-theme .pagination-info {
            color: #cbd5e1;
        }

        .pagination-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .pagination-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #e5e7eb;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            background: white;
            color: #0f172a;
            transition: all 0.3s ease;
        }

        body.dark-theme .pagination-btn {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        .pagination-btn:hover:not(:disabled) {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        body.dark-theme .pagination-btn:hover:not(:disabled) {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination-btn.active {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
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

            .table th,
            .table td {
                padding: 0.75rem;
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
                        <div class="stat-value" id="totalBooksCount">{{ $initialStats['totalBooks'] ?? 0 }}</div>
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
                        <div class="stat-value" id="totalCopiesCount">{{ $initialStats['totalCopies'] ?? 0 }}</div>
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
                        <div class="stat-value" id="availableCopiesCount">{{ $initialStats['availableCopies'] ?? 0 }}</div>
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
                        <div class="stat-value" id="categoriesCount">{{ count($initialStats['topCategories'] ?? []) }}</div>
                        <div class="stat-label">Categories</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Section -->
        <div>
            <!-- Search & Filter Container -->
            <div class="search-filter-container">
                <div class="search-box">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by title, author, ISBN..." value="{{ $search ?? '' }}">
                </div>

                <div class="filters-container">
                    <select class="filter-select" id="conditionFilter">
                        <option value="all" {{ ($condition ?? 'all') === 'all' ? 'selected' : '' }}>All Conditions</option>
                        <option value="new" {{ ($condition ?? 'all') === 'new' ? 'selected' : '' }}>New</option>
                        <option value="good" {{ ($condition ?? 'all') === 'good' ? 'selected' : '' }}>Good</option>
                        <option value="damaged" {{ ($condition ?? 'all') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                    </select>

                    <select class="filter-select" id="categoryFilter">
                        <option value="all" {{ ($selectedCategory ?? 'all') === 'all' ? 'selected' : '' }}>All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->name }}" {{ ($selectedCategory ?? 'all') === $category->name ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <select class="filter-select" id="availabilityFilter">
                        <option value="all" {{ ($availability ?? 'all') === 'all' ? 'selected' : '' }}>All Stock</option>
                        <option value="out-of-stock" {{ ($availability ?? 'all') === 'out-of-stock' ? 'selected' : '' }}>Out of Stock</option>
                        <option value="low-stock" {{ ($availability ?? 'all') === 'low-stock' ? 'selected' : '' }}>Low Stock (1-5)</option>
                        <option value="in-stock" {{ ($availability ?? 'all') === 'in-stock' ? 'selected' : '' }}>In Stock (6+)</option>
                    </select>

                    <select class="filter-select" id="sortFilter">
                        <option value="recently-added" {{ ($sort ?? 'recently-added') === 'recently-added' ? 'selected' : '' }}>Recently Added</option>
                        <option value="title-asc" {{ ($sort ?? 'recently-added') === 'title-asc' ? 'selected' : '' }}>Title (A-Z)</option>
                        <option value="title-desc" {{ ($sort ?? 'recently-added') === 'title-desc' ? 'selected' : '' }}>Title (Z-A)</option>
                        <option value="author-asc" {{ ($sort ?? 'recently-added') === 'author-asc' ? 'selected' : '' }}>Author (A-Z)</option>
                        <option value="author-desc" {{ ($sort ?? 'recently-added') === 'author-desc' ? 'selected' : '' }}>Author (Z-A)</option>
                        <option value="copies-desc" {{ ($sort ?? 'recently-added') === 'copies-desc' ? 'selected' : '' }}>Copies (High to Low)</option>
                    </select>
                </div>

                <div class="search-add-wrapper">
                    <button class="btn btn-primary" id="addBookBtn">
                        <i class="fas fa-plus"></i>
                        Add New Book
                    </button>
                </div>
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
                                        <button class="action-btn view" title="View Details"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit" title="Edit Book"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete" title="Delete Book"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div id="emptyState" style="text-align: center; padding: 2rem 1rem; color: #64748b; display: {{ $initialBooks->total() === 0 ? 'block' : 'none' }};">
                    <svg style="margin-bottom: 0.75rem; opacity: 0.5; width: 40px; height: 40px; margin-left: auto; margin-right: auto;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                        <path d="M9 9h6"></path>
                        <path d="M9 13h6"></path>
                    </svg>
                    <h3 style="margin-bottom: 0.25rem; font-size: 1rem; font-weight: 600;">No books found</h3>
                    <p style="color: #64748b;">Try adjusting your search or filters</p>
                </div>

                <!-- Pagination Container -->
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
                            <input type="text" name="isbn" class="form-control" placeholder="978-0-13-468599-1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Rack Number</label>
                            <input type="text" name="shelf_no" class="form-control" placeholder="A-12" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter book title" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Author</label>
                        <input type="text" name="author" class="form-control" placeholder="Enter author name" required>
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
                            <small style="color: #6b7280; margin-top: 4px; display: block;">Or create a new category</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Create New Category</label>
                            <input type="text" name="new_category" class="form-control" id="addNewCategory" placeholder="Enter new category name">
                            <small style="color: #6b7280; margin-top: 4px; display: block;">Leave empty to use existing category</small>
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
                            <input type="number" name="total_copies" class="form-control" placeholder="1" min="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Available Copies</label>
                            <input type="number" name="available_copies" class="form-control" placeholder="1" min="0" required>
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
                <button class="btn btn-outline" id="cancelAddBook">Cancel</button>
                <button class="btn btn-primary" id="submitAddBook">
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
                            <input type="text" name="isbn" class="form-control" id="editISBN" value="978-0-13-595785-9" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Rack Number</label>
                            <input type="text" name="shelf_no" class="form-control" id="editRack" value="A-15" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Title</label>
                        <input type="text" name="title" class="form-control" id="editTitle" value="Clean Code" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Author</label>
                        <input type="text" name="author" class="form-control" id="editAuthor" value="Robert C. Martin" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Publisher</label>
                            <input type="text" name="publisher" class="form-control" id="editPublisher" value="Prentice Hall">
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
                            <input type="number" name="total_copies" class="form-control" id="editTotalCopies" value="5" min="1" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label required">Available Copies</label>
                            <input type="number" name="available_copies" class="form-control" id="editAvailableCopies" value="2" min="0" required>
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
                <button class="btn btn-outline" id="cancelEditBook">Cancel</button>
                <button class="btn btn-primary" id="submitEditBook">
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
                <h3 class="modal-title">Delete Book</h3>
                <button class="modal-close-btn" id="closeDeleteBookModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirmation-popup">
                    <div class="confirmation-icon danger">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <h3 class="confirmation-title">Delete This Book?</h3>
                    <p class="confirmation-message">
                        You are about to permanently delete the following book:
                    </p>
                    <div class="confirmation-details">
                        <strong id="deleteBookTitle">Loading...</strong><br>
                        <span id="deleteBookISBN" style="color: #6b7280; font-size: 12px;">loading...</span>
                    </div>
                    <div class="confirmation-warning">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 6px; margin-top: 2px;"></i>
                        <span>This action cannot be undone. All book data will be permanently deleted.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: center; border-top: 1px solid #e5e7eb; padding-top: 16px;">
                <button class="btn btn-outline" id="cancelDeleteBook" style="min-width: 100px;">
                    Cancel
                </button>
                <button class="btn btn-confirm-danger" id="confirmDeleteBook" style="min-width: 100px;">
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
                this.currentBookISBN = null;
                this.currentSearch = '';
                this.currentConditionFilter = 'all';
                this.currentCategoryFilter = 'all';
                this.currentAvailabilityFilter = 'all';
                this.currentSortFilter = 'recently-added';
                this.searchDebounceTimer = null;
                this.storageBase = '{{ asset('storage') }}';
                this.allBooks = [];
                this.currentPage = Number(new URLSearchParams(window.location.search).get('page')) || 1;
                this.perPage = 15;
                this.init();
            }

            syncCurrentFiltersFromDom() {
                this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                this.currentConditionFilter = document.getElementById('conditionFilter')?.value || 'all';
                this.currentCategoryFilter = document.getElementById('categoryFilter')?.value || 'all';
                this.currentAvailabilityFilter = document.getElementById('availabilityFilter')?.value || 'all';
                this.currentSortFilter = document.getElementById('sortFilter')?.value || 'recently-added';
            }

            init() {
                // Modal listeners
                document.getElementById('addBookBtn').addEventListener('click', () => this.openModal('addBookModal'));
                document.getElementById('closeAddBookModal').addEventListener('click', () => this.closeModal('addBookModal'));
                document.getElementById('cancelAddBook').addEventListener('click', () => this.closeModal('addBookModal'));
                document.getElementById('submitAddBook').addEventListener('click', () => this.submitAddBook());

                document.getElementById('closeEditBookModal').addEventListener('click', () => this.closeModal('editBookModal'));
                document.getElementById('cancelEditBook').addEventListener('click', () => this.closeModal('editBookModal'));
                document.getElementById('submitEditBook').addEventListener('click', () => this.submitEditBook());

                document.getElementById('closeViewBookModal').addEventListener('click', () => this.closeModal('viewBookModal'));
                document.getElementById('closeViewBookBtn').addEventListener('click', () => this.closeModal('viewBookModal'));

                document.getElementById('closeDeleteBookModal').addEventListener('click', () => this.closeModal('deleteBookModal'));
                document.getElementById('cancelDeleteBook').addEventListener('click', () => this.closeModal('deleteBookModal'));
                document.getElementById('confirmDeleteBook').addEventListener('click', () => this.confirmDeleteBook());

                // Close modals on overlay click
                document.querySelectorAll('.modal-overlay').forEach(overlay => {
                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            this.closeCurrentModal();
                        }
                    });
                });

                // Initialize filter tabs
                this.initFilters();

                // Initialize search
                this.initSearch();

                // Keep JS state aligned with the current server-rendered filters
                this.syncCurrentFiltersFromDom();

                // Enable interaction for server-rendered initial state
                this.attachTableEventListeners();
                this.setupPaginationListeners();

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

                // Add input event listeners to clear errors when user starts typing
                this.initInputErrorListeners();
            }

            initInputErrorListeners() {
                // Add input listeners to Add Book form
                const addBookForm = document.getElementById('addBookForm');
                if (addBookForm) {
                    addBookForm.querySelectorAll('input, select, textarea').forEach(input => {
                        input.addEventListener('input', () => this.clearFieldError(input));
                        input.addEventListener('change', () => this.clearFieldError(input));
                    });
                }

                // Add input listeners to Edit Book form
                const editBookForm = document.getElementById('editBookForm');
                if (editBookForm) {
                    editBookForm.querySelectorAll('input, select, textarea').forEach(input => {
                        input.addEventListener('input', () => this.clearFieldError(input));
                        input.addEventListener('change', () => this.clearFieldError(input));
                    });
                }
            }

            clearFieldError(input) {
                // Remove error class
                input.classList.remove('error');
                
                // Remove error message element if exists
                const errorElement = input.parentElement.querySelector('.field-error-message');
                if (errorElement) {
                    errorElement.remove();
                }
            }

            initFilters() {
                // Condition filter
                const conditionFilter = document.getElementById('conditionFilter');
                if (conditionFilter) {
                    conditionFilter.addEventListener('change', (e) => {
                        this.currentConditionFilter = e.target.value;
                        this.currentPage = 1;
                        this.fetchBooksData(1);
                    });
                }

                // Category filter
                const categoryFilter = document.getElementById('categoryFilter');
                if (categoryFilter) {
                    categoryFilter.addEventListener('change', (e) => {
                        this.currentCategoryFilter = e.target.value;
                        this.currentPage = 1;
                        this.fetchBooksData(1);
                    });
                }

                // Availability filter
                const availabilityFilter = document.getElementById('availabilityFilter');
                if (availabilityFilter) {
                    availabilityFilter.addEventListener('change', (e) => {
                        this.currentAvailabilityFilter = e.target.value;
                        this.currentPage = 1;
                        this.fetchBooksData(1);
                    });
                }

                // Sort filter
                const sortFilter = document.getElementById('sortFilter');
                if (sortFilter) {
                    sortFilter.addEventListener('change', (e) => {
                        this.currentSortFilter = e.target.value;
                        this.currentPage = 1;
                        this.fetchBooksData(1);
                    });
                }
            }

            initSearch() {
                const searchInput = document.getElementById('searchInput');
                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        clearTimeout(this.searchDebounceTimer);
                        this.searchDebounceTimer = setTimeout(() => {
                            this.currentSearch = e.target.value.trim();
                            this.currentPage = 1;
                            this.fetchBooksData(1);
                        }, 300);
                    });
                }
            }
            fetchBooksData(page = 1) {
                const requestedPage = Number(page) || 1;
                this.currentPage = requestedPage;
                this.syncCurrentFiltersFromDom();
                const condition = this.currentConditionFilter;
                const category = this.currentCategoryFilter;
                const availability = this.currentAvailabilityFilter;
                const sort = this.currentSortFilter;
                const tableBody = document.getElementById('booksTableBody');
                const emptyState = document.getElementById('emptyState');
                const paginationContainer = document.getElementById('paginationContainer');

                const params = new URLSearchParams({
                    search: this.currentSearch,
                    condition: condition,
                    category: category,
                    availability: availability,
                    sort: sort,
                    page: requestedPage
                });

                if (tableBody) {
                    tableBody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
                }
                if (emptyState) {
                    emptyState.style.display = 'none';
                }
                if (paginationContainer) {
                    paginationContainer.style.display = 'none';
                }

                fetch(`{{ route('admin.books.data') }}?${params.toString()}`, {
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
                        if (data.last_page > 0 && requestedPage > data.last_page) {
                            return this.fetchBooksData(data.last_page);
                        }

                        this.currentPage = Number(data.current_page) || requestedPage;

                        try {
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
                        } catch (renderError) {
                            console.error('Error rendering books table:', renderError);
                        }

                        try {
                            if (data.stats) {
                                this.updateStats(data.stats);
                            }
                        } catch (statsError) {
                            console.error('Error updating book stats:', statsError);
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

            setupPaginationListeners() {
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        this.currentPage = page;
                        this.fetchBooksData(page);
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    });
                });
            }

            attachTableEventListeners() {
                document.querySelectorAll('.action-btn.view').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const row = e.target.closest('tr');
                        this.currentBookId = row.dataset.bookId;
                        this.currentBookTitle = row.cells[1].querySelector('strong')?.textContent || row.cells[1].textContent;
                        this.openViewModal(row);
                    });
                });

                document.querySelectorAll('.action-btn.edit').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const row = e.target.closest('tr');
                        this.currentBookId = row.dataset.bookId;
                        this.currentBookTitle = row.cells[1].querySelector('strong')?.textContent || row.cells[1].textContent;
                        this.openEditModal(row);
                    });
                });

                document.querySelectorAll('.action-btn.delete').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const row = e.target.closest('tr');
                        this.currentBookId = row.dataset.bookId;
                        this.currentBookTitle = row.cells[1].querySelector('strong')?.textContent || row.cells[1].textContent;
                        this.currentBookISBN = row.cells[0].textContent || '';
                        this.openDeleteModal();
                    });
                });
            }

            updateStats(stats) {
                const totalBooks = document.getElementById('totalBooksCount');
                const totalCopies = document.getElementById('totalCopiesCount');
                const availableCopies = document.getElementById('availableCopiesCount');
                const categories = document.getElementById('categoriesCount');

                if (totalBooks) totalBooks.textContent = stats?.totalBooks ?? 0;
                if (totalCopies) totalCopies.textContent = stats?.totalCopies ?? 0;
                if (availableCopies) availableCopies.textContent = stats?.availableCopies ?? 0;
                if (categories) categories.textContent = Object.keys(stats?.topCategories || {}).length;
            }

            refreshStats() {
                const params = new URLSearchParams({
                    search: this.currentSearch,
                    condition: this.currentConditionFilter,
                    category: this.currentCategoryFilter,
                    availability: this.currentAvailabilityFilter,
                });

                fetch(`{{ route('admin.books.stats') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    this.updateStats(data);
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
                fetch('{{ route('admin.books.index') }}', {
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
            }

            closeCurrentModal() {
                if (this.currentModal) {
                    this.closeModal(this.currentModal);
                }
            }

            openViewModal(row) {
                const cells = row.cells;
                const titleCell = cells[1];
                // Prefer data attributes (added server-side) for more complete info
                const dataset = row.dataset || {};
                const cover = dataset.cover || '';
                const description = dataset.description || '';
                const publisher = dataset.publisher || (titleCell.querySelector('div') ? titleCell.querySelector('div').textContent : 'Unknown');
                const isbn = dataset.isbn || cells[0].textContent || '';
                const shelf = dataset.shelf || cells[4].textContent || '';
                const totalCopies = dataset.totalCopies || (cells[5] && cells[5].querySelector('.copy-total') ? cells[5].querySelector('.copy-total').textContent : '0');
                const availableCopies = dataset.availableCopies || (cells[6] && cells[6].querySelector('.copy-available') ? cells[6].querySelector('.copy-available').textContent : '0');
                const categoryName = dataset.categoryName || cells[3].textContent || '';
                const author = cells[2].textContent || '';

                const storageBase = '{{ asset('storage') }}';
                const coverUrl = cover ? `${storageBase}/${cover}` : '';

                const detailsContent = `
                    <div style="display: grid; grid-template-columns: 180px 1fr; gap: 16px; align-items: start;">
                        <div>
                            <div class="book-cover-slot" style="width:100%; height:240px; border-radius:8px; overflow:hidden;"></div>
                        </div>
                        <div>
                            <h3 style="margin:0 0 8px 0;font-size:18px;font-weight:700;">${this.currentBookTitle}</h3>
                            <p style="margin:0 0 8px 0;color:#6b7280;"><strong>Author:</strong> ${author}</p>
                            <p style="margin:0 0 8px 0;color:#6b7280;"><strong>Category:</strong> ${categoryName}</p>
                            <p style="margin:0 0 8px 0;color:#6b7280;"><strong>Publisher:</strong> ${publisher}</p>
                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:8px;">
                                <div>
                                    <p style="color:#6b7280;font-size:10px;margin:0 0 2px;">ISBN</p>
                                    <p style="font-weight:600;margin:0;">${isbn}</p>
                                </div>
                                <div>
                                    <p style="color:#6b7280;font-size:10px;margin:0 0 2px;">Shelf / Rack</p>
                                    <p style="font-weight:600;margin:0;">${shelf}</p>
                                </div>
                            </div>

                            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-top:12px;">
                                <div>
                                    <p style="color:#6b7280;font-size:10px;margin:0 0 2px;">Total Copies</p>
                                    <p style="font-weight:600;margin:0;">${totalCopies}</p>
                                </div>
                                <div>
                                    <p style="color:#6b7280;font-size:10px;margin:0 0 2px;">Available Copies</p>
                                    <p style="font-weight:600;margin:0;">${availableCopies}</p>
                                </div>
                            </div>

                            <div style="margin-top:12px;">
                                <p style="color:#6b7280;font-size:12px;margin:0 0 4px;">Description</p>
                                <p style="margin:0;color:#374151;line-height:1.5;font-size:14px;">${description ? description : '<em>No description available.</em>'}</p>
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
                document.getElementById('editCategory').value = row.dataset.categoryId || '';
                document.getElementById('editRack').value = cells[4].textContent;
                document.getElementById('editTotalCopies').value = cells[5].querySelector('.copy-total').textContent;
                document.getElementById('editAvailableCopies').value = cells[6].querySelector('.copy-available').textContent;
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
                document.getElementById('deleteBookTitle').textContent = this.currentBookTitle || 'Unknown Book';
                document.getElementById('deleteBookISBN').textContent = `ISBN: ${this.currentBookISBN || 'N/A'}`;
                this.openModal('deleteBookModal');
            }

            submitAddBook() {
                const form = document.getElementById('addBookForm');
                const categorySelect = document.getElementById('addCategorySelect');
                const newCategoryInput = document.getElementById('addNewCategory');

                // Clear all previous errors
                this.clearFieldErrors(form);

                // Define field order for validation (top to bottom in form)
                const fieldOrder = ['isbn', 'shelf_no', 'title', 'author', 'publisher', 'category_id', 'new_category', 'condition', 'total_copies', 'available_copies', 'description'];

                // Validate category selection first (custom validation)
                const categoryError = this.validateCategory(categorySelect, newCategoryInput);
                if (categoryError) {
                    this.showFieldError(categoryError.input, categoryError.error);
                    return;
                }

                // Validate form fields one at a time
                const validationError = this.validateFormOneByOne(form, fieldOrder);
                if (validationError) {
                    this.showFieldError(validationError.input, validationError.error);
                    return;
                }

                // If creating new category, we need to create it first
                const newCategory = newCategoryInput.value.trim();
                if (newCategory) {
                    this.createNewCategory(newCategory, form);
                    return;
                }

                // All validations passed, submit the form
                this.submitAddBookForm(form);
            }

            createNewCategory(categoryName, form) {
                fetch('{{ route('admin.categories.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name: categoryName })
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
                        
                        for (let i = 1; i < options.length; i++) { // Start from 1 to skip the first placeholder option
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
                
                console.log('Submitting book with category_id:', formData.get('category_id'), 'new_category:', formData.get('new_category'));
                
                fetch('{{ route('admin.books.store') }}', {
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
                    // Handle server-side validation errors with one-error-at-a-time
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
                        this.currentPage = 1;
                        this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                        this.fetchBooksData(1);
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

                // Clear all previous errors
                this.clearFieldErrors(form);

                // Define field order for validation (top to bottom in form)
                const fieldOrder = ['isbn', 'shelf_no', 'title', 'author', 'publisher', 'category_id', 'condition', 'total_copies', 'available_copies', 'description'];

                // Validate form fields one at a time
                const validationError = this.validateFormOneByOne(form, fieldOrder);
                if (validationError) {
                    this.showFieldError(validationError.input, validationError.error);
                    return;
                }

                const formData = new FormData(form);
                formData.append('_method', 'PUT');
                
                fetch(`{{ url('admin/books') }}/${this.currentBookId}`, {
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
                    // Handle server-side validation errors with one-error-at-a-time
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
                        this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                        this.fetchBooksData(this.currentPage);
                    } else {
                        this.showNotification(data.message || 'Error updating book', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showNotification('Error updating book', 'error');
                });
            }

            confirmDeleteBook() {
                fetch(`{{ url('admin/books') }}/${this.currentBookId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        this.showNotification(`"${this.currentBookTitle}" deleted successfully`, 'success');
                        this.closeModal('deleteBookModal');
                        this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                        this.fetchBooksData(this.currentPage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.showNotification('Error deleting book', 'error');
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

            // One-error-at-a-time validation helpers
            clearFieldErrors(form) {
                // Remove error class and error messages from all form controls
                form.querySelectorAll('.form-control').forEach(input => {
                    input.classList.remove('error');
                    const existingError = input.parentElement.querySelector('.field-error-message');
                    if (existingError) {
                        existingError.remove();
                    }
                });
            }

            showFieldError(input, message) {
                // Clear all errors first
                const form = input.closest('form');
                if (form) {
                    this.clearFieldErrors(form);
                }

                // Add error class to the input
                input.classList.add('error');

                // Create error message element
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error-message';
                errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;

                // Insert error message after the input
                input.parentElement.appendChild(errorDiv);

                // Focus the input
                input.focus();

                // Scroll input into view
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            validateField(input, rules) {
                const value = input.value.trim();
                const name = input.name;
                const type = input.type;
                const isRequired = input.hasAttribute('required');

                // Skip validation for empty non-required fields
                if (!isRequired && value === '') {
                    return null;
                }

                // Check required fields
                if (rules.required && value === '') {
                    return rules.requiredMessage || 'This field is required';
                }

                // Check minimum length
                if (rules.minLength && value.length < rules.minLength) {
                    return rules.minLengthMessage || `Must be at least ${rules.minLength} characters`;
                }

                // Check maximum length
                if (rules.maxLength && value.length > rules.maxLength) {
                    return rules.maxLengthMessage || `Must not exceed ${rules.maxLength} characters`;
                }

                // Check numeric fields
                if (rules.numeric && value !== '' && isNaN(value)) {
                    return rules.numericMessage || 'Must be a valid number';
                }

                // Check minimum value for numeric fields
                if (rules.min !== undefined && value !== '' && parseFloat(value) < rules.min) {
                    return rules.minMessage || `Must be at least ${rules.min}`;
                }

                // Check maximum value for numeric fields
                if (rules.max !== undefined && value !== '' && parseFloat(value) > rules.max) {
                    return rules.maxMessage || `Must not exceed ${rules.max}`;
                }

                // Check ISBN format (basic validation)
                if (rules.isbn && value !== '') {
                    // Remove dashes and spaces
                    const cleanIsbn = value.replace(/[-\s]/g, '');
                    if (!/^(?:\d{10}|\d{13})$/.test(cleanIsbn)) {
                        return rules.isbnMessage || 'Invalid ISBN format (10 or 13 digits required)';
                    }
                }

                // Check file type
                if (rules.accept && type === 'file' && value !== '') {
                    const file = input.files[0];
                    if (file) {
                        const acceptedTypes = rules.accept.split(',').map(t => t.trim());
                        const fileType = file.type;
                        const fileName = file.name.toLowerCase();
                        const isValid = acceptedTypes.some(type => {
                            if (type.startsWith('.')) {
                                return fileName.endsWith(type);
                            }
                            return fileType.includes(type.replace('*', ''));
                        });
                        if (!isValid) {
                            return rules.acceptMessage || `File type not allowed. Accepted: ${rules.accept}`;
                        }
                    }
                }

                // Check file size
                if (rules.maxSize && type === 'file' && value !== '') {
                    const file = input.files[0];
                    if (file) {
                        const maxBytes = rules.maxSize * 1024 * 1024; // Convert MB to bytes
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
                        isbn: true,
                        isbnMessage: 'Invalid ISBN format (10 or 13 digits required)'
                    },
                    title: {
                        required: true,
                        requiredMessage: 'Title is required',
                        minLength: 2,
                        minLengthMessage: 'Title must be at least 2 characters'
                    },
                    author: {
                        required: true,
                        requiredMessage: 'Author is required',
                        minLength: 2,
                        minLengthMessage: 'Author must be at least 2 characters'
                    },
                    shelf_no: {
                        required: true,
                        requiredMessage: 'Rack number is required'
                    },
                    category_id: {
                        required: true,
                        requiredMessage: 'Category is required'
                    },
                    condition: {
                        required: true,
                        requiredMessage: 'Condition is required'
                    },
                    total_copies: {
                        required: true,
                        requiredMessage: 'Total copies is required',
                        numeric: true,
                        numericMessage: 'Total copies must be a number',
                        min: 1,
                        minMessage: 'Total copies must be at least 1'
                    },
                    available_copies: {
                        required: true,
                        requiredMessage: 'Available copies is required',
                        numeric: true,
                        numericMessage: 'Available copies must be a number',
                        min: 0,
                        minMessage: 'Available copies cannot be negative'
                    },
                    cover_image: {
                        maxSize: 2,
                        maxSizeMessage: 'Cover image must not exceed 2MB',
                        accept: 'image/*',
                        acceptMessage: 'Please select a valid image file (JPEG, PNG, GIF)'
                    }
                };

                // Find the first field with an error
                for (const fieldName of fieldOrder) {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (input && fieldRules[fieldName]) {
                        const error = this.validateField(input, fieldRules[fieldName]);
                        if (error) {
                            return { field: fieldName, error: error, input: input };
                        }
                    }
                }

                return null; // No errors
            }

            // Validate category selection for Add form
            validateCategory(categorySelect, newCategoryInput) {
                const selectedCategory = categorySelect.value.trim();
                const newCategory = newCategoryInput.value.trim();

                if (!selectedCategory && !newCategory) {
                    return { field: 'category_id', error: 'Please select a category or create a new one', input: categorySelect };
                }

                if (selectedCategory && newCategory) {
                    return { field: 'new_category', error: 'Please choose either existing OR new category, not both', input: newCategoryInput };
                }

                if (newCategory && newCategory.length < 2) {
                    return { field: 'new_category', error: 'New category name must be at least 2 characters', input: newCategoryInput };
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
