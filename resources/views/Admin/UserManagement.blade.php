@extends('Admin.layouts.app')

@section('title', 'User Management')

@push('styles')
    <!-- Using FontAwesome for icons (alternative to Bootstrap) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* User Management Styles */
        .user-management {
            padding: 5px;
        }

        /* Stats Cards - Single Row */
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

        .stat-active .stat-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .stat-inactive .stat-icon {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .stat-roles .stat-icon {
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

        .roles-list {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid;
        }

        body.light-theme .roles-list {
            border-top-color: #e2e8f0;
        }

        body.dark-theme .roles-list {
            border-top-color: #334155;
        }

        .role-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 13px;
        }

        .role-count {
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

        /* Search-Filter Container */
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

        /* SEARCH BOX */
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
            overflow-x: hidden;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1110px;
        }

        .table th {
            padding: 6px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            border-bottom: 1px solid;
            white-space: nowrap;
        }

        .table th:nth-child(1) {
            padding-right: 4px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table th:nth-child(2) {
            padding-left: 4px;
            max-width: 100px;
        }

        .table th:nth-child(3),
        .table th:nth-child(4),
        .table th:nth-child(5) {
            max-width: 100px;
        }

        .table th:nth-child(6) {
            max-width: 90px;
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
            padding: 6px 8px;
            border-bottom: 1px solid;
            vertical-align: middle;
            font-size: 13px;
        }

        .table td:nth-child(1) {
            padding-right: 4px;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table td:nth-child(2) {
            padding-left: 4px;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table td:nth-child(3),
        .table td:nth-child(4),
        .table td:nth-child(5) {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table td:nth-child(6) {
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        /* Text Muted - for secondary text like email */
        .text-muted {
            color: #64748b;
        }

        body.dark-theme .text-muted {
            color: #94a3b8;
        }

        /* User Name - for primary text in table */
        .user-name {
            color: #0f172a;
        }

        body.dark-theme .user-name {
            color: #f1f5f9;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 600;
            gap: 4px;
        }

        .status-active {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }

        body.dark-theme .status-active {
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
            color: #4ade80;
        }

        .status-inactive {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        body.dark-theme .status-inactive {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        /* Action Buttons - 4 buttons visible */
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

        .action-btn.edit:hover {
            color: #3b82f6;
            background: #dbeafe;
        }

        body.dark-theme .action-btn.edit:hover {
            background: #1e3a8a;
        }

        .action-btn.password:hover {
            color: #10b981;
            background: #dcfce7;
        }

        body.dark-theme .action-btn.password:hover {
            background: #14532d;
        }

        .action-btn.toggle:hover {
            color: #f59e0b;
            background: #fef3c7;
        }

        body.dark-theme .action-btn.toggle:hover {
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
            max-width: 500px;
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

        .status-radio {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }

        .status-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .status-option input[type="radio"] {
            margin: 0;
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

        /* Responsive */
        @media (max-width: 768px) {
            .user-management {
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

        /* Conditional Field Styles */
        .conditional-field {
            transition: all 0.3s ease;
        }

        .conditional-field.hidden {
            display: none !important;
        }

        .conditional-field .form-label::after {
            content: '';
        }

        /* Field Error Styles */
        .field-error {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            display: none;
            font-weight: 500;
        }

        .field-error.visible {
            display: block;
        }

        .form-control.is-invalid {
            border-color: #ef4444 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23ef4444' viewBox='0 0 16 16'%3E%3Cpath d='M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z'/%3E%3Ccircle cx='8' cy='8' r='6' fill='none' stroke='%23ef4444' stroke-width='1.5'/%3E%3Cpath d='M8 5v4M8 10v.5' stroke='%23ef4444' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 35px;
        }

            .form-group.error .form-control {
            border-color: #ef4444;
        }

        /* ===== Confirmation Popup Styles ===== */
        .confirmation-popup {
            text-align: center;
            padding: 20px 10px;
        }

        .confirmation-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }

        .confirmation-icon.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        .confirmation-icon.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        .confirmation-icon.info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }

        .confirmation-icon.success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #059669;
        }

        .confirmation-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1f2937;
        }

        body.dark-theme .confirmation-title {
            color: #f3f4f6;
        }

        .confirmation-message {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        body.dark-theme .confirmation-message {
            color: #9ca3af;
        }

        .confirmation-warning {
            font-size: 13px;
            color: #dc2626;
            font-weight: 500;
            margin-top: 16px;
            padding: 10px;
            background: #fef2f2;
            border-radius: 8px;
            border: 1px solid #fecaca;
        }

        body.dark-theme .confirmation-warning {
            background: #450a0a;
            border-color: #7f1d1d;
            color: #fca5a5;
        }

        .confirmation-details {
            font-size: 13px;
            color: #374151;
            background: #f9fafb;
            padding: 12px;
            border-radius: 8px;
            margin-top: 16px;
        }

        body.dark-theme .confirmation-details {
            background: #1f2937;
            color: #d1d5db;
        }

        .confirmation-details strong {
            color: #111827;
        }

        body.dark-theme .confirmation-details strong {
            color: #f9fafb;
        }

        .confirmation-user-card {
            display: flex;
            align-items: center;
            gap: 14px;
            text-align: left;
            padding: 14px;
            border-radius: 12px;
            margin-top: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .confirmation-user-card {
            background: #162033;
            border-color: #334155;
        }

        .confirmation-user-avatar {
            width: 52px;
            height: 52px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
        }

        .confirmation-user-meta {
            min-width: 0;
            flex: 1;
        }

        .confirmation-user-name {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            word-break: break-word;
        }

        body.dark-theme .confirmation-user-name {
            color: #f8fafc;
        }

        .confirmation-user-email {
            margin-top: 4px;
            font-size: 12px;
            color: #64748b;
            word-break: break-word;
        }

        body.dark-theme .confirmation-user-email {
            color: #94a3b8;
        }

        .popup-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            justify-content: center;
        }

        .popup-buttons .btn {
            min-width: 120px;
            padding: 10px 20px;
            font-size: 14px;
        }

        .btn-confirm-danger {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
        }

        .btn-confirm-danger:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #991b1b 100%);
        }

        .btn-confirm-warning {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
            border: none;
        }

        .btn-confirm-warning:hover {
            background: linear-gradient(135deg, #b45309 0%, #92400e 100%);
        }

        .btn-confirm-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
        }

        .btn-confirm-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }
    </style>
@endpush

@section('content')
    <div class="user-management" data-auth-user-id="{{ auth()->id() }}">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">User Management</h1>
            <p class="page-description">Manage all users, their roles, and permissions</p>
        </div>

        <!-- Stats Row - 4 boxes in single row -->
        <div class="stats-row" id="statsContainer">
            <!-- Total Users -->
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="totalUsersCount">{{ $totalUsers }}</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="stat-card stat-active">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="activeUsersCount">{{ $activeUsers }}</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
            </div>

            <!-- Inactive Users -->
            <div class="stat-card stat-inactive">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="inactiveUsersCount">{{ $inactiveUsers }}</div>
                        <div class="stat-label">Inactive Users</div>
                    </div>
                </div>
            </div>

            <!-- By Role -->
            <div class="stat-card stat-roles">
                <div class="stat-content">
                    <div class="stat-icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value">By Role</div>
                        <div class="stat-label">Role Distribution</div>
                    </div>
                </div>

                <div class="roles-list" id="rolesListContainer">
                    <div class="role-item">
                        <span>Admins</span>
                        <span class="role-count" id="roleCountAdmin">{{ $roleCounts['admin'] ?? 0 }}</span>
                    </div>
                    <div class="role-item">
                        <span>Staff</span>
                        <span class="role-count" id="roleCountStaff">{{ $roleCounts['staff'] ?? 0 }}</span>
                    </div>
                    <div class="role-item">
                        <span>Students</span>
                        <span class="role-count" id="roleCountStudent">{{ $roleCounts['student'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Section -->
        <div>
            <!-- Search & Filter Container -->
            <div class="search-filter-container">
                <div class="search-box">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by name, email, username...">
                </div>

                <div class="filters-container">
                    <select class="filter-select" id="roleFilter">
                        <option value="all">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <option value="student">Student</option>
                    </select>

                    <select class="filter-select" id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <select class="filter-select" id="sortFilter">
                        <option value="recently-added">Recently Added</option>
                        <option value="name-asc">Name (A-Z)</option>
                        <option value="name-desc">Name (Z-A)</option>
                    </select>
                </div>

                <div class="search-add-wrapper">
                    <button class="btn btn-primary" id="addUserBtn">
                        <i class="fas fa-user-plus"></i>
                        Add New User
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="table-container">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @foreach ($users as $user)
                                @include('admin.partials.user-row', compact('user'))
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Add New User</h3>
                <button type="button" class="modal-close-btn" id="closeAddUserModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description">Create a new user account with appropriate role and permissions</p>

                <form id="addUserForm">
                    @csrf

                    <div class="form-group">
                        <label class="form-label required">Full Name</label>
                        <input type="text" class="form-control" name="name" id="addName" placeholder="John Doe">
                        <span class="field-error" id="addNameError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" name="email" id="addEmail" placeholder="john@example.com">
                        <span class="field-error" id="addEmailError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Password</label>
                        <input type="password" class="form-control" name="password" id="addPassword" placeholder="Min. 6 characters" minlength="6">
                        <span class="field-error" id="addPasswordError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Role</label>
                        <select class="form-control" name="role" id="addRoleSelect" required>
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="student" selected>Student</option>
                        </select>
                        <span class="field-error" id="addRoleError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" name="phone" id="addPhone" placeholder="+1 234 567 8900">
                    </div>

                    <div class="form-group conditional-field" data-for="staff,student">
                        <label class="form-label">Department</label>
                        <select class="form-control" name="department_id" id="addDepartmentSelect">
                            <option value="">Select Department</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="field-error" id="addDepartmentError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label">Staff Designation</label>
                        <input type="text" class="form-control" name="designation" id="addDesignation" placeholder="Staff Member">
                        <span class="field-error" id="addDesignationError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" name="join_date" id="addJoinDate">
                        <span class="field-error" id="addJoinDateError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label" id="rollNoLabel">Roll Number</label>
                        <input type="text" class="form-control" name="roll_no" id="addRollNo" placeholder="CSE-2021-001">
                        <span class="field-error" id="addRollNoError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label">Batch</label>
                        <input type="text" class="form-control" name="batch" id="addBatch" placeholder="2024">
                        <span class="field-error" id="addBatchError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label">Semester</label>
                        <select class="form-control" name="semester" id="addSemester">
                            <option value="">Select Semester</option>
                            <option value="1">1st</option>
                            <option value="2">2nd</option>
                            <option value="3">3rd</option>
                            <option value="4">4th</option>
                            <option value="5">5th</option>
                            <option value="6">6th</option>
                            <option value="7">7th</option>
                            <option value="8">8th</option>
                        </select>
                        <span class="field-error" id="addSemesterError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="address" placeholder="123 Main St, City, Country" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="status-radio">
                            <label class="status-option">
                                <input type="radio" name="status" value="active" checked>
                                <span>Active</span>
                            </label>
                            <label class="status-option">
                                <input type="radio" name="status" value="inactive">
                                <span>Inactive</span>
                            </label>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelAddUser">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitAddUser">
                    <i class="fas fa-user-plus"></i>
                    Add User
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Edit User</h3>
                <button type="button" class="modal-close-btn" id="closeEditUserModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-description">Update user information and permissions</p>

                <form id="editUserForm">
                    <div class="form-group">
                        <label class="form-label required">Full Name</label>
                        <input type="text" class="form-control" id="editFullName" name="name">
                        <span class="field-error" id="editNameError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email">
                        <span class="field-error" id="editEmailError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">Role</label>
                        <select class="form-control" id="editRole" name="role">
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="student">Student</option>
                        </select>
                        <span class="field-error" id="editRoleError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="editPhone" name="phone">
                    </div>

                    <div class="form-group" id="editDepartmentGroup" style="display: none;">
                        <label class="form-label">Department</label>
                        <select class="form-control" id="editDepartment" name="department_id">
                            <option value="">Select Department</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="field-error" id="editDepartmentError"></span>
                    </div>

                    <div class="form-group" id="editStaffDesignationGroup" style="display: none;">
                        <label class="form-label">Staff Designation</label>
                        <input type="text" class="form-control" id="editDesignation" name="designation" placeholder="Staff Member">
                        <span class="field-error" id="editDesignationError"></span>
                    </div>

                    <div class="form-group" id="editStaffJoinDateGroup" style="display: none;">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" id="editJoinDate" name="join_date">
                        <span class="field-error" id="editJoinDateError"></span>
                    </div>

                    <div class="form-group" id="editRollNoGroup" style="display: none;">
                        <label class="form-label" id="editRollNoLabel">Roll Number</label>
                        <input type="text" class="form-control" id="editRollNo" name="roll_no"
                            placeholder="CSE-2021-001">
                        <span class="field-error" id="editRollNoError"></span>
                    </div>

                    <div class="form-group" id="editBatchGroup" style="display: none;">
                        <label class="form-label">Batch</label>
                        <input type="text" class="form-control" id="editBatch" name="batch" placeholder="2024">
                        <span class="field-error" id="editBatchError"></span>
                    </div>

                    <div class="form-group" id="editSemesterGroup" style="display: none;">
                        <label class="form-label">Semester</label>
                        <select class="form-control" id="editSemester" name="semester">
                            <option value="">Select Semester</option>
                            <option value="1">1st</option>
                            <option value="2">2nd</option>
                            <option value="3">3rd</option>
                            <option value="4">4th</option>
                            <option value="5">5th</option>
                            <option value="6">6th</option>
                            <option value="7">7th</option>
                            <option value="8">8th</option>
                        </select>
                        <span class="field-error" id="editSemesterError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="cancelEditUser">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitEditUser">
                    <i class="fas fa-save"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Reset Password Confirmation Modal -->
    <div id="resetPasswordModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Reset Password</h3>
                <button class="modal-close-btn" id="closeResetPasswordModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirmation-popup">
                    <div class="confirmation-icon warning">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="confirmation-title">Reset User Password?</h3>
                    <p class="confirmation-message">
                        You are about to reset the password for:
                    </p>
                    <div class="confirmation-details">
                        <strong id="resetPasswordUserName">Loading...</strong><br>
                        <span id="resetPasswordUserEmail" style="color: #6b7280; font-size: 12px;">loading...</span>
                    </div>
                    <p class="confirmation-message" style="margin-top: 16px;">
                        A new temporary password will be generated and sent to the user's email address.
                    </p>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: center; border-top: 1px solid #e5e7eb; padding-top: 16px;">
                <button class="btn btn-outline" id="cancelResetPassword" style="min-width: 100px;">
                    Cancel
                </button>
                <button class="btn btn-confirm-warning" id="submitResetPassword" style="min-width: 100px;">
                    <i class="fas fa-paper-plane"></i>
                    Send Reset Link
                </button>
            </div>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div id="deleteUserModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Delete User</h3>
                <button class="modal-close-btn" id="closeDeleteUserModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirmation-popup">
                    <div class="confirmation-icon danger">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <h3 class="confirmation-title">Delete User?</h3>
                    <p class="confirmation-message">This will permanently remove this user.</p>
                    <div class="confirmation-user-card">
                        <div class="confirmation-user-avatar" id="deleteUserAvatar">U</div>
                        <div class="confirmation-user-meta">
                            <div class="confirmation-user-name" id="deleteUserName">Loading...</div>
                            <div class="confirmation-user-email" id="deleteUserEmail">loading...</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="justify-content: center; border-top: 1px solid #e5e7eb; padding-top: 16px;">
                <button type="button" class="btn btn-outline" id="cancelDeleteUser" style="min-width: 100px;">
                    Cancel
                </button>
                <button type="button" class="btn btn-confirm-danger" id="confirmDeleteUser" style="min-width: 100px;">
                    <i class="fas fa-trash-alt"></i>
                    Delete User
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /**
         * UserManager - Main class for managing user operations
         * Handles all user management functionality including CRUD operations, modals, and UI interactions
         * ENHANCED: All data comes from database via AJAX, no DOM-only filtering
         */
        class UserManager {
            constructor() {
                console.log('UserManager initialized');
                const userManagementRoot = document.querySelector('.user-management');
                this.currentModal = null;
                this.currentUserId = null;
                this.currentUserRow = null;
                this.loggedInUserId = Number(userManagementRoot?.dataset.authUserId || 0);
                this.perPage = 15;
                this.currentPage = Number(new URLSearchParams(window.location.search).get('page')) || 1;
                this.currentStatusFilter = 'all';
                this.currentRoleFilter = 'all';
                this.currentSortFilter = 'recently-added';
                this.currentSearch = '';
                this.searchTimeout = null;
                this.init();
            }

            /**
             * Initialize all event listeners and functionality
             */
            init() {
                console.log('Initializing UserManager...');

                // Initialize modal events
                this.initModalEvents();

                // Initialize search functionality (LIVE SEARCH - DEBOUNCED)
                this.initSearch();

                // Initialize filter tabs (AJAX-BASED)
                this.initFilters();

                // Initialize table action buttons
                this.initTableActions();

                // Initialize keyboard shortcuts
                this.initKeyboardShortcuts();

                // Initialize pagination click handlers
                this.initPagination();
            }

            /**
             * Initialize live search with debouncing and AJAX
             */
            initSearch() {
                const searchInput = document.getElementById('searchInput');
                if (!searchInput) return;

                searchInput.addEventListener('input', (e) => {
                    // Clear previous timeout
                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }

                    this.currentSearch = e.target.value.trim();

                    // Debounce search: wait 300ms after user stops typing
                    this.searchTimeout = setTimeout(() => {
                        console.log('Live search triggered:', this.currentSearch);
                        this.fetchUsersData(1); // Reset to page 1 when searching
                    }, 300);
                });
            }

            /**
             * Initialize filter dropdowns with AJAX
             */
            initFilters() {
                // Role Filter
                const roleFilter = document.getElementById('roleFilter');
                if (roleFilter) {
                    roleFilter.addEventListener('change', (e) => {
                        this.currentRoleFilter = e.target.value;
                        this.currentPage = 1;
                        this.fetchUsersData(1);
                    });
                }

                // Status Filter
                const statusFilter = document.getElementById('statusFilter');
                if (statusFilter) {
                    statusFilter.addEventListener('change', (e) => {
                        this.currentStatusFilter = e.target.value;
                        this.currentPage = 1;
                        this.fetchUsersData(1);
                    });
                }

                // Sort Filter
                const sortFilter = document.getElementById('sortFilter');
                if (sortFilter) {
                    sortFilter.addEventListener('change', (e) => {
                        this.currentSortFilter = e.target.value;
                        this.currentPage = 1;
                        this.fetchUsersData(1);
                    });
                }
            }

            /**
             * Initialize pagination click handlers
             */
            initPagination() {
                document.addEventListener('click', (e) => {
                    // Handle pagination links
                    const paginationLink = e.target.closest('a[href*="page="]');
                    if (paginationLink && paginationLink.closest('#paginationContainer')) {
                        e.preventDefault();

                        // Extract page number from URL
                        const url = new URL(paginationLink.href, window.location.origin);
                        const page = url.searchParams.get('page') || 1;

                        console.log('Pagination link clicked, page:', page);
                        this.fetchUsersData(page);
                    }
                });
            }

            /**
             * Fetch users data from backend with current filters and search
             * @param {number} page - Page number to fetch
             */
            fetchUsersData(page = 1) {
                this.currentPage = Number(page) || 1;
                console.log('Fetching users data - search:', this.currentSearch, 'status:', this.currentStatusFilter, 'role:', this.currentRoleFilter, 'sort:', this.currentSortFilter, 'page:', page);

                // Show loading state
                const tableBody = document.getElementById('usersTableBody');
                tableBody.innerHTML =
                    '<tr><td colspan="8" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

                const params = new URLSearchParams({
                    search: this.currentSearch,
                    status: this.currentStatusFilter,
                    role: this.currentRoleFilter,
                    sort: this.currentSortFilter,
                    page: page
                });

                fetch(`/admin/users/data?${params.toString()}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf()
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        console.log('Users data received:', data);

                        if (data.success) {
                            // Update table rows
                            tableBody.innerHTML = data.tableRows;

                            // Update pagination
                            const paginationContainer = document.getElementById('paginationContainer');
                            if (paginationContainer) {
                                paginationContainer.innerHTML = data.pagination;
                            }

                            // DO NOT update stats here - they will be updated by refreshStats()
                            // This prevents showing filtered stats temporarily

                            // Re-initialize table actions for new rows
                            this.initTableActions();

                            // Re-initialize pagination for new links
                            this.initPagination();
                        } else {
                            tableBody.innerHTML =
                                `<tr><td colspan="8" style="text-align: center; padding: 40px; color: #ef4444;">Error: ${data.message}</td></tr>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching users:', error);
                        tableBody.innerHTML =
                            '<tr><td colspan="8" style="text-align: center; padding: 40px; color: #ef4444;">Error loading users. Please try again.</td></tr>';
                        this.showNotification('Error fetching users: ' + error.message, 'error');
                    });
            }

            /**
             * Update stats cards with data from backend
             * @param {Object} stats - Statistics data
             */
            updateStats(stats) {
                console.log('Updating stats:', stats);

                // Update total users count
                document.getElementById('totalUsersCount').textContent = stats.totalUsers || 0;

                // Update active users count
                document.getElementById('activeUsersCount').textContent = stats.activeUsers || 0;

                // Update inactive users count
                document.getElementById('inactiveUsersCount').textContent = stats.inactiveUsers || 0;

                // Update role counts
                document.getElementById('roleCountAdmin').textContent = stats.roleCounts?.admin || 0;
                document.getElementById('roleCountStaff').textContent = stats.roleCounts?.staff || 0;
                document.getElementById('roleCountStudent').textContent = stats.roleCounts?.student || 0;
            }

            /**
             * Refresh stats from server without fetching user list
             * Used for real-time stat updates after delete/status change
             * Stats always show GLOBAL counts, not filtered by current tab
             */
            refreshStats() {
                console.log('Refreshing global stats...');

                // Always fetch GLOBAL stats (no filter), not filtered by current tab
                fetch(`/admin/users/stats`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf()
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        console.log('Global stats received:', data);
                        if (data.success) {
                            this.updateStats(data.stats);
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing stats:', error);
                    });
            }

            refreshPaginationOnly() {
                const params = new URLSearchParams({
                    search: this.currentSearch,
                    status: this.currentStatusFilter,
                    role: this.currentRoleFilter,
                    sort: this.currentSortFilter,
                    page: this.currentPage
                });

                fetch(`/admin/users/data?${params.toString()}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf()
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        if (!data.success) return;
                        const paginationContainer = document.getElementById('paginationContainer');
                        if (paginationContainer) {
                            paginationContainer.innerHTML = data.pagination;
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing pagination:', error);
                    });
            }

            /**
             * Initialize modal open/close event listeners
             */
            initModalEvents() {
                const addUserForm = document.getElementById('addUserForm');
                if (addUserForm) {
                    addUserForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.submitAddUser();
                    });
                }

                const editUserForm = document.getElementById('editUserForm');
                if (editUserForm) {
                    editUserForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.submitEditUser();
                    });
                }

                // Add User Modal
                document.getElementById('addUserBtn').addEventListener('click', () => {
                    console.log('Add user button clicked');
                    this.openModal('addUserModal');
                    // Initialize field visibility based on default role
                    const roleSelect = document.getElementById('addRoleSelect');
                    if (roleSelect) {
                        this.toggleAddUserFields(roleSelect.value);
                    }
                });

                document.getElementById('submitAddUser').addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('Submit add user clicked');
                    this.submitAddUser();
                });

                document.getElementById('addRoleSelect').addEventListener('change', (e) => {
                    this.toggleAddUserFields(e.target.value);
                });

                document.getElementById('closeAddUserModal').addEventListener('click', () => {
                    this.closeModal('addUserModal');
                });

                document.getElementById('cancelAddUser').addEventListener('click', () => {
                    this.closeModal('addUserModal');
                });

                // Edit User Modal
                document.getElementById('submitEditUser').addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('Submit edit user clicked');
                    this.submitEditUser();
                });

                document.getElementById('editRole').addEventListener('change', (e) => {
                    this.toggleStudentFields(e.target.value);
                });

                document.getElementById('closeEditUserModal').addEventListener('click', () => {
                    this.closeModal('editUserModal');
                });

                document.getElementById('cancelEditUser').addEventListener('click', () => {
                    this.closeModal('editUserModal');
                });

                // Delete User Modal
                document.getElementById('confirmDeleteUser').addEventListener('click', () => {
                    console.log('Confirm delete clicked');
                    this.confirmDeleteUser();
                });

                document.getElementById('closeDeleteUserModal').addEventListener('click', () => {
                    this.closeModal('deleteUserModal');
                });

                document.getElementById('cancelDeleteUser').addEventListener('click', () => {
                    this.closeModal('deleteUserModal');
                });

                // Reset Password Modal
                if (document.getElementById('closeResetPasswordModal')) {
                    document.getElementById('closeResetPasswordModal').addEventListener('click', () => {
                        this.closeModal('resetPasswordModal');
                    });
                }

                if (document.getElementById('cancelResetPassword')) {
                    document.getElementById('cancelResetPassword').addEventListener('click', () => {
                        this.closeModal('resetPasswordModal');
                    });
                }

                if (document.getElementById('submitResetPassword')) {
                    document.getElementById('submitResetPassword').addEventListener('click', () => {
                        this.confirmResetPassword();
                    });
                }

                // Status Toggle Modal
                if (document.getElementById('closeStatusToggleModal')) {
                    document.getElementById('closeStatusToggleModal').addEventListener('click', () => {
                        this.closeModal('statusToggleModal');
                    });
                }

                // Close modals when clicking on overlay
                document.querySelectorAll('.modal-overlay').forEach(overlay => {
                    overlay.addEventListener('click', (e) => {
                        if (e.target === overlay) {
                            this.closeCurrentModal();
                        }
                    });
                });
            }

            /**
             * Initialize table row action buttons
             */
            initTableActions() {
                // Remove previous event listeners to avoid duplicates
                document.querySelectorAll('.action-btn.edit').forEach((btn) => {
                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);
                });

                // Edit buttons
                document.querySelectorAll('.action-btn.edit').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Edit button clicked');
                        const row = e.target.closest('tr');
                        if (!row) {
                            console.error('Could not find row');
                            return;
                        }
                        this.currentUserId = row.dataset.userId;
                        this.currentUserRow = row;
                        console.log('User ID:', this.currentUserId);
                        this.openEditModal(row);
                    });
                });

                // Password reset buttons
                document.querySelectorAll('.action-btn.password').forEach((btn) => {
                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);
                });

                document.querySelectorAll('.action-btn.password').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Password reset button clicked');
                        const row = e.target.closest('tr');
                        if (!row) {
                            console.error('Could not find row');
                            return;
                        }
                        this.currentUserId = row.dataset.userId;
                        this.currentUserRow = row;
                        console.log('Opening password reset confirmation for user:', this.currentUserId);
                        this.openResetPasswordModal();
                    });
                });

                // Toggle status buttons
                document.querySelectorAll('.action-btn.toggle').forEach((btn) => {
                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);
                });

                document.querySelectorAll('.action-btn.toggle').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Toggle button clicked');
                        const row = e.target.closest('tr');
                        if (!row) {
                            console.error('Could not find row');
                            return;
                        }
                        this.currentUserId = row.dataset.userId;
                        this.currentUserRow = row;
                        console.log('Toggling status for user:', this.currentUserId);
                        this.toggleUserStatus(row, btn);
                    });
                });

                // Delete buttons
                document.querySelectorAll('.action-btn.delete').forEach((btn) => {
                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);
                });

                document.querySelectorAll('.action-btn.delete').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Delete button clicked');
                        const row = e.target.closest('tr');
                        if (!row) {
                            console.error('Could not find row');
                            return;
                        }
                        this.currentUserId = row.dataset.userId;
                        this.currentUserRow = row;
                        console.log('Deleting user:', this.currentUserId);
                        this.openDeleteModal();
                    });
                });
            }

            /**
             * Initialize keyboard shortcuts
             */
            initKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // ESC key - Close current modal
                    if (e.key === 'Escape' && this.currentModal) {
                        e.preventDefault();
                        this.closeCurrentModal();
                    }

                    // Enter key in modals - Submit form
                    if (e.key === 'Enter' && this.currentModal) {
                        // Don't trigger if focus is on cancel button or textarea
                        if (e.target.type === 'button' || e.target.tagName === 'TEXTAREA') {
                            return;
                        }

                        switch (this.currentModal) {
                            case 'addUserModal':
                                e.preventDefault();
                                this.submitAddUser();
                                break;
                            case 'editUserModal':
                                e.preventDefault();
                                this.submitEditUser();
                                break;
                            case 'deleteUserModal':
                                e.preventDefault();
                                this.confirmDeleteUser();
                                break;
                        }
                    }

                    // Ctrl/Cmd + F - Focus search input
                    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                        e.preventDefault();
                        const searchInput = document.getElementById('searchInput');
                        if (searchInput) {
                            searchInput.focus();
                            searchInput.select();
                        }
                    }

                    // Ctrl/Cmd + N - Open add user modal
                    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                        e.preventDefault();
                        this.openModal('addUserModal');
                    }
                });
            }

            /**
             * Open a modal by ID
             * @param {string} modalId - ID of the modal to open
             */
            openModal(modalId) {
                console.log('Opening modal:', modalId);
                this.currentModal = modalId;
                const modal = document.getElementById(modalId);

                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';

                    // Focus first input field after a short delay
                    setTimeout(() => {
                        const firstInput = modal.querySelector('input, select, textarea');
                        if (firstInput) firstInput.focus();
                    }, 100);
                } else {
                    console.error('Modal not found:', modalId);
                }
            }

            /**
             * Close a modal by ID
             * @param {string} modalId - ID of the modal to close
             */
            closeModal(modalId) {
                console.log('Closing modal:', modalId);
                const modal = document.getElementById(modalId);

                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }

                this.currentModal = null;

                // Reset forms and clear errors
                if (modalId === 'addUserModal') {
                    document.getElementById('addUserForm').reset();
                    // Clear all error states
                    this.clearAddUserErrors();
                    // Reset field visibility to default (student)
                    this.toggleAddUserFields('student');
                }

                if (modalId === 'editUserModal') {
                    document.getElementById('editUserForm').reset();
                    // Clear all error states
                    this.clearEditUserErrors();
                    // Reset field visibility to default
                    this.toggleStudentFields('student');
                }
            }

            /**
             * Close the currently open modal
             */
            closeCurrentModal() {
                if (this.currentModal) {
                    this.closeModal(this.currentModal);
                }
            }

            /**
             * Open edit modal and populate with user data
             * @param {HTMLElement} row - Table row element
             */
            openEditModal(row) {
                console.log('Opening edit modal for row:', row);

                // Get user ID from data attribute
                const userId = row.dataset.userId;
                this.currentUserId = userId;

                // Fetch user details via AJAX to get all data
                fetch(`/admin/users/${userId}/details`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('User details:', data);
                        if (data.success) {
                            const user = data.user;

                            // Populate form with user data
                            document.getElementById('editFullName').value = user.name || '';
                            document.getElementById('editEmail').value = user.email || '';
                            document.getElementById('editPhone').value = user.phone || '';
                            document.getElementById('editAddress').value = user.address || '';

                            // Set role
                            const role = user.role;
                            document.getElementById('editRole').value = role;

                            // Show/hide student-specific fields based on role
                            this.toggleStudentFields(role);

                            document.getElementById('editDepartment').value = '';
                            document.getElementById('editDesignation').value = '';
                            document.getElementById('editJoinDate').value = '';
                            document.getElementById('editRollNo').value = '';
                            document.getElementById('editBatch').value = '';
                            document.getElementById('editSemester').value = '';

                            // Populate role-specific fields
                            if (role === 'student' && user.student) {
                                document.getElementById('editDepartment').value = user.student.department_id || '';
                                document.getElementById('editRollNo').value = user.student.roll_no || '';
                                document.getElementById('editBatch').value = user.student.batch || '';
                                document.getElementById('editSemester').value = user.student.semester || '';
                            } else if (role === 'staff' && user.staff) {
                                document.getElementById('editDepartment').value = user.staff.department_id || '';
                                document.getElementById('editDesignation').value = user.staff.designation || '';
                                document.getElementById('editJoinDate').value = user.staff.join_date || '';
                            } else if (role === 'staff') {
                                document.getElementById('editDesignation').value = 'Staff Member';
                                document.getElementById('editJoinDate').value = this.getTodayDate();
                            }

                            // Open the modal
                            this.openModal('editUserModal');
                        } else {
                            throw new Error('Invalid response format');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching user details:', error);
                        this.showNotification('Error loading user details: ' + error.message, 'error');
                    });
            }

            /**
             * Toggle visibility of student-specific fields based on role
             */
            toggleStudentFields(role) {
                const deptGroup = document.getElementById('editDepartmentGroup');
                const designationGroup = document.getElementById('editStaffDesignationGroup');
                const joinDateGroup = document.getElementById('editStaffJoinDateGroup');
                const rollNoGroup = document.getElementById('editRollNoGroup');
                const batchGroup = document.getElementById('editBatchGroup');
                const semesterGroup = document.getElementById('editSemesterGroup');
                const rollNoLabel = document.getElementById('editRollNoLabel');
                const rollNoInput = document.getElementById('editRollNo');

                if (role === 'student') {
                    deptGroup.style.display = 'block';
                    designationGroup.style.display = 'none';
                    joinDateGroup.style.display = 'none';
                    rollNoGroup.style.display = 'block';
                    batchGroup.style.display = 'block';
                    semesterGroup.style.display = 'block';
                    // Update Roll Number label for student
                    if (rollNoLabel) rollNoLabel.textContent = 'Roll Number';
                    if (rollNoInput) rollNoInput.placeholder = 'CSE-2021-001';
                } else if (role === 'staff') {
                    deptGroup.style.display = 'block';
                    designationGroup.style.display = 'block';
                    joinDateGroup.style.display = 'block';
                    rollNoGroup.style.display = 'none';
                    batchGroup.style.display = 'none';
                    semesterGroup.style.display = 'none';
                    if (!document.getElementById('editJoinDate').value) {
                        document.getElementById('editJoinDate').value = this.getTodayDate();
                    }
                } else {
                    deptGroup.style.display = 'none';
                    designationGroup.style.display = 'none';
                    joinDateGroup.style.display = 'none';
                    rollNoGroup.style.display = 'none';
                    batchGroup.style.display = 'none';
                    semesterGroup.style.display = 'none';
                }
            }

            /**
             * Toggle visibility of role-specific fields in Add User modal
             * @param {string} role - Selected role (admin, staff, student)
             */
            toggleAddUserFields(role) {
                const conditionalFields = document.querySelectorAll('.conditional-field');
                const rollNoLabel = document.getElementById('rollNoLabel');
                const rollNoInput = document.getElementById('addRollNo');
                const addJoinDate = document.getElementById('addJoinDate');

                conditionalFields.forEach(field => {
                    const allowedRoles = field.getAttribute('data-for');
                    if (allowedRoles && allowedRoles.includes(role)) {
                        field.classList.remove('hidden');
                    } else {
                        field.classList.add('hidden');
                    }
                });

                // Update Roll Number label based on role
                if (rollNoLabel) {
                    if (role === 'student') {
                        rollNoLabel.textContent = 'Roll Number';
                        rollNoInput.placeholder = 'CSE-2021-001';
                    } else {
                        rollNoLabel.textContent = 'Roll Number';
                        rollNoInput.placeholder = 'CSE-2021-001';
                    }
                }

                if (role === 'staff' && addJoinDate && !addJoinDate.value) {
                    addJoinDate.value = this.getTodayDate();
                }
            }

            getTodayDate() {
                return new Date().toISOString().split('T')[0];
            }

            /**
             * Open delete confirmation modal
             */
            openDeleteModal() {
                const userName = this.currentUserRow.querySelector('.user-name span')?.textContent?.trim() || 'Unknown User';
                const userEmail = this.currentUserRow.querySelector('td:first-child .text-muted')?.textContent?.trim() || 'No email available';
                const userInitial = userName.charAt(0).toUpperCase() || 'U';
                
                // Update modal content
                document.getElementById('deleteUserName').textContent = userName;
                document.getElementById('deleteUserEmail').textContent = userEmail;
                document.getElementById('deleteUserAvatar').textContent = userInitial;
                
                this.openModal('deleteUserModal');
            }

            /**
             * Open reset password confirmation modal
             */
            openResetPasswordModal() {
                const userName = this.currentUserRow.cells[0].textContent;
                const userEmail = this.currentUserRow.dataset.email || '';
                
                // Update modal content
                document.getElementById('resetPasswordUserName').textContent = userName;
                document.getElementById('resetPasswordUserEmail').textContent = userEmail;
                
                this.openModal('resetPasswordModal');
            }

            /**
             * Get CSRF token from meta tag
             * @returns {string} CSRF token
             */
            csrf() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.content : '';
            }

            /**
             * Show notification message
             * @param {string} message - Notification message
             * @param {string} type - Notification type: 'success', 'error', 'info', 'warning'
             */
            showNotification(message, type = 'info') {
                console.log('Notification:', message, type);

                // Remove existing notifications
                document.querySelectorAll('.notification').forEach(n => n.remove());

                // Create notification element
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;

                // Set icon based on type
                const icons = {
                    success: 'fa-check-circle',
                    error: 'fa-exclamation-circle',
                    warning: 'fa-exclamation-triangle',
                    info: 'fa-info-circle'
                };

                notification.innerHTML = `
            <i class="fas ${icons[type] || 'fa-info-circle'}"></i>
            ${message}
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
        `;

                document.body.appendChild(notification);

                // Add close event
                notification.querySelector('.notification-close').addEventListener('click', () => {
                    notification.remove();
                });

                // Auto-remove after 5 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            }

            getUserRows() {
                return Array.from(document.querySelectorAll('#usersTableBody tr[data-user-id]'));
            }

            findUserRow(userId) {
                return document.querySelector(`#usersTableBody tr[data-user-id="${userId}"]`);
            }

            removeEmptyStateRow() {
                const emptyRow = document.querySelector('#usersTableBody tr:not([data-user-id])');
                if (emptyRow) {
                    emptyRow.remove();
                }
            }

            renderEmptyStateIfNeeded() {
                const tableBody = document.getElementById('usersTableBody');
                if (!tableBody || this.getUserRows().length > 0) {
                    return;
                }

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">
                            <i class="fas fa-users fa-2x mb-4"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                `;
            }

            createRowElement(rowHtml) {
                const template = document.createElement('template');
                template.innerHTML = rowHtml.trim();
                return template.content.firstElementChild;
            }

            doesUserMatchCurrentView(user) {
                if (!user) {
                    return false;
                }

                if (this.currentRoleFilter !== 'all' && user.role !== this.currentRoleFilter) {
                    return false;
                }

                if (this.currentStatusFilter !== 'all' && user.status !== this.currentStatusFilter) {
                    return false;
                }

                if (this.currentSearch) {
                    const search = this.currentSearch.toLowerCase();
                    const haystack = `${user.name || ''} ${user.email || ''}`.toLowerCase();
                    if (!haystack.includes(search)) {
                        return false;
                    }
                }

                return true;
            }

            getComparableName(rowElement, fallbackUser = null) {
                if (fallbackUser?.name) {
                    return fallbackUser.name.toLowerCase();
                }

                return rowElement?.querySelector('.user-name span')?.textContent?.trim().toLowerCase() || '';
            }

            insertRowIntoCurrentTable(rowElement, user, { replaceExisting = false } = {}) {
                const tableBody = document.getElementById('usersTableBody');
                if (!tableBody || !rowElement) {
                    return;
                }

                const existingRow = this.findUserRow(user.id);
                if (existingRow && replaceExisting) {
                    existingRow.remove();
                }

                this.removeEmptyStateRow();

                let rows = this.getUserRows();
                const isLoggedInUser = Number(user.id) === Number(this.loggedInUserId);

                if (isLoggedInUser) {
                    tableBody.prepend(rowElement);
                } else if (this.currentSortFilter === 'name-asc' || this.currentSortFilter === 'name-desc') {
                    const newName = this.getComparableName(rowElement, user);
                    const compare = this.currentSortFilter === 'name-asc'
                        ? (candidateName) => newName.localeCompare(candidateName) < 0
                        : (candidateName) => newName.localeCompare(candidateName) > 0;

                    let inserted = false;
                    for (const candidateRow of rows) {
                        if (Number(candidateRow.dataset.userId) === Number(this.loggedInUserId)) {
                            continue;
                        }

                        const candidateName = this.getComparableName(candidateRow);
                        if (compare(candidateName)) {
                            tableBody.insertBefore(rowElement, candidateRow);
                            inserted = true;
                            break;
                        }
                    }

                    if (!inserted) {
                        tableBody.appendChild(rowElement);
                    }
                } else {
                    const pinnedRow = this.findUserRow(this.loggedInUserId);
                    if (pinnedRow && pinnedRow.parentNode === tableBody) {
                        pinnedRow.insertAdjacentElement('afterend', rowElement);
                    } else {
                        tableBody.prepend(rowElement);
                    }
                }

                rows = this.getUserRows();
                while (rows.length > this.perPage) {
                    rows[rows.length - 1].remove();
                    rows = this.getUserRows();
                }

                this.initTableActions();
            }

            upsertVisibleUserRow(rowHtml, user) {
                if (!this.doesUserMatchCurrentView(user)) {
                    const existingRow = this.findUserRow(user.id);
                    if (existingRow) {
                        existingRow.remove();
                        this.renderEmptyStateIfNeeded();
                    }
                    return;
                }

                const rowElement = this.createRowElement(rowHtml);
                this.insertRowIntoCurrentTable(rowElement, user, { replaceExisting: true });
            }

            prependVisibleUserRow(rowHtml, user) {
                if (!this.doesUserMatchCurrentView(user)) {
                    return;
                }

                const rowElement = this.createRowElement(rowHtml);
                this.insertRowIntoCurrentTable(rowElement, user, { replaceExisting: false });
            }

            removeVisibleUserRow(userId) {
                const row = this.findUserRow(userId);
                if (!row) {
                    return false;
                }

                row.remove();
                this.renderEmptyStateIfNeeded();
                return true;
            }

            /**
             * Submit add user form with one-error-at-a-time validation
             */
            submitAddUser() {
                console.log('Submitting add user form');
                const form = document.getElementById('addUserForm');
                const formData = new FormData(form);

                // Clear all previous errors first
                this.clearAddUserErrors();

                // Get the role to determine which fields to validate
                const role = formData.get('role') || 'student';

                // Validate fields one by one and find the FIRST error
                const firstError = this.validateAddUserField(role, formData);

                // If there's an error, show it and stop
                if (firstError) {
                    this.showAddUserFieldError(firstError.fieldId, firstError.message);
                    this.scrollToElement(firstError.fieldId);
                    return;
                }

                // If no client-side errors, proceed with AJAX submission
                this.submitAddUserAjax(form, formData);
            }

            /**
             * Clear all error states in the add user form
             */
            clearAddUserErrors() {
                const errorFields = ['addNameError', 'addEmailError', 'addPasswordError', 'addRoleError',
                    'addDepartmentError', 'addDesignationError', 'addJoinDateError', 'addRollNoError', 'addBatchError', 'addSemesterError'];
                const inputFields = ['addName', 'addEmail', 'addPassword', 'addRoleSelect',
                    'addDepartmentSelect', 'addDesignation', 'addJoinDate', 'addRollNo', 'addBatch', 'addSemester'];

                errorFields.forEach(id => {
                    const errorEl = document.getElementById(id);
                    if (errorEl) {
                        errorEl.classList.remove('visible');
                        errorEl.textContent = '';
                    }
                });

                inputFields.forEach(id => {
                    const inputEl = document.getElementById(id);
                    if (inputEl) {
                        inputEl.classList.remove('is-invalid');
                    }
                });
            }

            /**
             * Validate form fields one by one and return the FIRST error
             * @param {string} role - The selected user role
             * @param {FormData} formData - Form data object
             * @returns {object|null} - First error object or null if no errors
             */
            validateAddUserField(role, formData) {
                // Priority order for validation
                const validations = [
                    // 1. Name validation
                    {
                        fieldId: 'addName',
                        field: 'name',
                        check: () => {
                            const name = formData.get('name')?.trim();
                            if (!name) return 'Please enter the user\'s full name';
                            if (name.length < 2) return 'Name must be at least 2 characters';
                            return null;
                        }
                    },
                    // 2. Email format validation
                    {
                        fieldId: 'addEmail',
                        field: 'email',
                        check: () => {
                            const email = formData.get('email')?.trim();
                            if (!email) return 'Please enter an email address';
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(email)) return 'Please enter a valid email address';
                            return null;
                        }
                    },
                    // 3. Password validation
                    {
                        fieldId: 'addPassword',
                        field: 'password',
                        check: () => {
                            const password = formData.get('password');
                            if (!password) return 'Please enter a password';
                            if (password.length < 6) return 'Password must be at least 6 characters';
                            return null;
                        }
                    },
                    // 4. Role validation
                    {
                        fieldId: 'addRoleSelect',
                        field: 'role',
                        check: () => {
                            const roleValue = formData.get('role');
                            if (!roleValue) return 'Please select a user role';
                            if (!['admin', 'staff', 'student'].includes(roleValue)) {
                                return 'Please select a valid user role';
                            }
                            return null;
                        }
                    },
                    // 5. Role-specific validations
                    ...(role === 'student' ? [
                        // Student: Department
                        {
                            fieldId: 'addDepartmentSelect',
                            field: 'department_id',
                            check: () => {
                                const dept = formData.get('department_id');
                                if (!dept) return 'Please select a department for the student';
                                return null;
                            }
                        },
                        // Student: Roll Number
                        {
                            fieldId: 'addRollNo',
                            field: 'roll_no',
                            check: () => {
                                const rollNo = formData.get('roll_no')?.trim();
                                if (!rollNo) return 'Please enter the student\'s roll number';
                                return null;
                            }
                        },
                        // Student: Semester
                        {
                            fieldId: 'addSemester',
                            field: 'semester',
                            check: () => {
                                const semester = formData.get('semester');
                                if (!semester) return 'Please select the current semester';
                                return null;
                            }
                        }
                    ] : role === 'staff' ? [
                        // Staff: Department
                        {
                            fieldId: 'addDepartmentSelect',
                            field: 'department_id',
                            check: () => {
                                const dept = formData.get('department_id');
                                if (!dept) return 'Please select a department for the staff member';
                                return null;
                            }
                        },
                        // Staff: Designation
                        {
                            fieldId: 'addDesignation',
                            field: 'designation',
                            check: () => {
                                const designation = formData.get('designation')?.trim();
                                if (!designation) return 'Please enter the staff designation';
                                return null;
                            }
                        },
                        // Staff: Join Date
                        {
                            fieldId: 'addJoinDate',
                            field: 'join_date',
                            check: () => {
                                const joinDate = formData.get('join_date');
                                if (!joinDate) return 'Please select the join date for the staff member';
                                return null;
                            }
                        }
                    ] : [])
                ];

                // Run validations in order and return the FIRST error
                for (const validation of validations) {
                    const error = validation.check();
                    if (error) {
                        return {
                            fieldId: validation.fieldId,
                            message: error
                        };
                    }
                }

                return null;
            }

            /**
             * Show error message below a specific field
             */
            showAddUserFieldError(fieldId, message) {
                const inputEl = document.getElementById(fieldId);
                const errorEl = document.getElementById(fieldId + 'Error');

                if (inputEl) {
                    inputEl.classList.add('is-invalid');
                }

                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.add('visible');
                }
            }

            /**
             * Scroll to an element
             */
            scrollToElement(elementId) {
                const element = document.getElementById(elementId);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Focus on the input after scrolling
                    setTimeout(() => {
                        element.focus();
                    }, 300);
                }
            }

            /**
             * Submit add user form via AJAX
             */
            submitAddUserAjax(form, formData) {
                // Show loading state
                const submitBtn = document.getElementById('submitAddUser');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                submitBtn.disabled = true;

                // Submit form via AJAX
                fetch("{{ route('admin.users.store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) {
                            return res.json().then(data => {
                                // Handle server-side validation errors (like unique constraints)
                                if (data.errors) {
                                    // Get the first error from server
                                    const firstErrorField = Object.keys(data.errors)[0];
                                    const firstErrorMessage = data.errors[firstErrorField][0];

                                    // Map field names to error element IDs
                                    const fieldMap = {
                                        'email': 'addEmailError',
                                        'roll_no': 'addRollNoError',
                                        'batch': 'addBatchError',
                                        'department_id': 'addDepartmentError',
                                        'designation': 'addDesignationError',
                                        'join_date': 'addJoinDateError',
                                        'semester': 'addSemesterError',
                                        'name': 'addNameError',
                                        'password': 'addPasswordError',
                                        'role': 'addRoleError'
                                    };

                                    const errorElementId = fieldMap[firstErrorField] || 'addNameError';
                                    throw new Error(firstErrorMessage);
                                }

                                throw new Error(data.message || 'Validation error occurred');
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showNotification(data.message || 'User added successfully', 'success');
                            this.closeModal('addUserModal');
                            form.reset();
                            this.clearAddUserErrors();
                            // Reset field visibility to default (student)
                            this.toggleAddUserFields('student');

                            // Immediately refresh stats for live update
                            this.refreshStats();

                            this.prependVisibleUserRow(data.rowHtml, data.user);
                            this.refreshPaginationOnly();
                        } else {
                            throw new Error(data.message || 'Error adding user');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Error adding user', 'error');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            }

            /**
             * Submit edit user form with one-error-at-a-time validation
             */
            submitEditUser() {
                console.log('Submitting edit user form for user:', this.currentUserId);

                // Clear all previous errors first
                this.clearEditUserErrors();

                // Create form data
                const formData = new FormData();
                formData.append('name', document.getElementById('editFullName').value);
                formData.append('email', document.getElementById('editEmail').value);
                formData.append('role', document.getElementById('editRole').value);
                formData.append('phone', document.getElementById('editPhone').value);
                formData.append('address', document.getElementById('editAddress').value);

                // Add student-specific fields if role is student
                const role = document.getElementById('editRole').value;
                if (role === 'student') {
                    formData.append('department_id', document.getElementById('editDepartment').value);
                    formData.append('roll_no', document.getElementById('editRollNo').value);
                    formData.append('batch', document.getElementById('editBatch').value);
                    formData.append('semester', document.getElementById('editSemester').value);
                } else if (role === 'staff') {
                    formData.append('department_id', document.getElementById('editDepartment').value);
                    formData.append('designation', document.getElementById('editDesignation').value);
                    formData.append('join_date', document.getElementById('editJoinDate').value);
                }

                // Validate fields one by one and find the FIRST error
                const firstError = this.validateEditUserField(role, formData);

                // If there's an error, show it and stop
                if (firstError) {
                    this.showEditUserFieldError(firstError.fieldId, firstError.message);
                    this.scrollToElement(firstError.fieldId);
                    return;
                }

                // If no client-side errors, proceed with AJAX submission
                this.submitEditUserAjax(formData);
            }

            /**
             * Clear all error states in the edit user form
             */
            clearEditUserErrors() {
                const errorFields = ['editNameError', 'editEmailError', 'editRoleError',
                    'editDepartmentError', 'editDesignationError', 'editJoinDateError', 'editRollNoError', 'editBatchError', 'editSemesterError'];
                const inputFields = ['editFullName', 'editEmail', 'editRole',
                    'editDepartment', 'editDesignation', 'editJoinDate', 'editRollNo', 'editBatch', 'editSemester'];

                errorFields.forEach(id => {
                    const errorEl = document.getElementById(id);
                    if (errorEl) {
                        errorEl.classList.remove('visible');
                        errorEl.textContent = '';
                    }
                });

                inputFields.forEach(id => {
                    const inputEl = document.getElementById(id);
                    if (inputEl) {
                        inputEl.classList.remove('is-invalid');
                    }
                });
            }

            /**
             * Validate form fields one by one and return the FIRST error for Edit form
             * @param {string} role - The selected user role
             * @param {FormData} formData - Form data object
             * @returns {object|null} - First error object or null if no errors
             */
            validateEditUserField(role, formData) {
                // Priority order for validation
                const validations = [
                    // 1. Name validation
                    {
                        fieldId: 'editFullName',
                        check: () => {
                            const name = formData.get('name')?.trim();
                            if (!name) return 'Please enter the user\'s full name';
                            if (name.length < 2) return 'Name must be at least 2 characters';
                            return null;
                        }
                    },
                    // 2. Email format validation
                    {
                        fieldId: 'editEmail',
                        check: () => {
                            const email = formData.get('email')?.trim();
                            if (!email) return 'Please enter an email address';
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(email)) return 'Please enter a valid email address';
                            return null;
                        }
                    },
                    // 3. Role validation
                    {
                        fieldId: 'editRole',
                        check: () => {
                            const roleValue = formData.get('role');
                            if (!roleValue) return 'Please select a user role';
                            if (!['admin', 'staff', 'student'].includes(roleValue)) {
                                return 'Please select a valid user role';
                            }
                            return null;
                        }
                    },
                    // 4. Role-specific validations
                    ...(role === 'student' ? [
                        // Student: Department
                        {
                            fieldId: 'editDepartment',
                            check: () => {
                                const dept = formData.get('department_id');
                                if (!dept) return 'Please select a department for the student';
                                return null;
                            }
                        },
                        // Student: Roll Number
                        {
                            fieldId: 'editRollNo',
                            check: () => {
                                const rollNo = formData.get('roll_no')?.trim();
                                if (!rollNo) return 'Please enter the student\'s roll number';
                                return null;
                            }
                        },
                        // Student: Semester
                        {
                            fieldId: 'editSemester',
                            check: () => {
                                const semester = formData.get('semester');
                                if (!semester) return 'Please select the current semester';
                                return null;
                            }
                        }
                    ] : role === 'staff' ? [
                        // Staff: Department
                        {
                            fieldId: 'editDepartment',
                            check: () => {
                                const dept = formData.get('department_id');
                                if (!dept) return 'Please select a department for the staff member';
                                return null;
                            }
                        },
                        // Staff: Designation
                        {
                            fieldId: 'editDesignation',
                            check: () => {
                                const designation = formData.get('designation')?.trim();
                                if (!designation) return 'Please enter the staff designation';
                                return null;
                            }
                        },
                        // Staff: Join Date
                        {
                            fieldId: 'editJoinDate',
                            check: () => {
                                const joinDate = formData.get('join_date');
                                if (!joinDate) return 'Please select the join date for the staff member';
                                return null;
                            }
                        }
                    ] : [])
                ];

                // Run validations in order and return the FIRST error
                for (const validation of validations) {
                    const error = validation.check();
                    if (error) {
                        return {
                            fieldId: validation.fieldId,
                            message: error
                        };
                    }
                }

                return null;
            }

            /**
             * Show error message below a specific field in Edit form
             */
            showEditUserFieldError(fieldId, message) {
                const inputEl = document.getElementById(fieldId);
                const errorEl = document.getElementById(fieldId + 'Error');

                if (inputEl) {
                    inputEl.classList.add('is-invalid');
                }

                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.add('visible');
                }
            }

            /**
             * Submit edit user form via AJAX
             */
            submitEditUserAjax(formData) {
                formData.append('_method', 'PUT');

                // Show loading state
                const submitBtn = document.getElementById('submitEditUser');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                submitBtn.disabled = true;

                // Submit form via AJAX
                fetch(`/admin/users/${this.currentUserId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) {
                            return res.json().then(data => {
                                // Handle server-side validation errors (like unique constraints)
                                if (data.errors) {
                                    const firstErrorField = Object.keys(data.errors)[0];
                                    const firstErrorMessage = data.errors[firstErrorField][0];

                                    // Map field names to error element IDs
                                    const fieldMap = {
                                        'email': 'editEmailError',
                                        'roll_no': 'editRollNoError',
                                        'batch': 'editBatchError',
                                        'department_id': 'editDepartmentError',
                                        'designation': 'editDesignationError',
                                        'join_date': 'editJoinDateError',
                                        'semester': 'editSemesterError',
                                        'name': 'editNameError',
                                        'role': 'editRoleError'
                                    };

                                    const errorElementId = fieldMap[firstErrorField] || 'editNameError';
                                    throw new Error(firstErrorMessage);
                                }

                                throw new Error(data.message || 'Validation error occurred');
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showNotification(data.message || 'User updated successfully', 'success');
                            this.closeModal('editUserModal');

                            // Immediately refresh stats for live update (role change may affect counts)
                            this.refreshStats();

                            this.upsertVisibleUserRow(data.rowHtml, data.user);
                            this.refreshPaginationOnly();
                        } else {
                            throw new Error(data.message || 'Error updating user');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Error updating user', 'error');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            }

            /**
             * Confirm and execute user deletion
             */
            confirmDeleteUser() {
                console.log('Confirming delete for user:', this.currentUserId);

                // Show loading state
                const deleteBtn = document.getElementById('confirmDeleteUser');
                const originalText = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                deleteBtn.disabled = true;

                // Delete user via AJAX
                fetch(`/admin/users/${this.currentUserId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showNotification(data.message || 'User deleted successfully', 'success');
                            this.closeModal('deleteUserModal');

                            // Immediately refresh stats for live update
                            this.refreshStats();

                            const removedFromCurrentTable = this.removeVisibleUserRow(this.currentUserId);

                            if (removedFromCurrentTable && this.getUserRows().length === 0 && this.currentPage > 1) {
                                this.fetchUsersData(this.currentPage - 1);
                            } else {
                                this.refreshPaginationOnly();
                            }
                        } else {
                            throw new Error(data.message || 'Error deleting user');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Error deleting user', 'error');
                    })
                    .finally(() => {
                        deleteBtn.innerHTML = originalText;
                        deleteBtn.disabled = false;
                    });
            }

            /**
             * Confirm and execute password reset
             */
            confirmResetPassword() {
                console.log('Confirming password reset for user:', this.currentUserId);

                // Show loading state
                const resetBtn = document.getElementById('submitResetPassword');
                const originalText = resetBtn.innerHTML;
                resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                resetBtn.disabled = true;

                // Reset password via AJAX
                fetch(`/admin/users/${this.currentUserId}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showNotification(data.message || 'Password reset email sent successfully', 'success');
                            this.closeModal('resetPasswordModal');
                        } else {
                            throw new Error(data.message || 'Error resetting password');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Error sending password reset email', 'error');
                    })
                    .finally(() => {
                        resetBtn.innerHTML = originalText;
                        resetBtn.disabled = false;
                    });
            }

            /**
             * Confirm and execute status toggle
             */
            confirmStatusToggle() {
                console.log('Confirming status toggle for user:', this.currentUserId);

                const row = this.statusToggleRow;
                const button = this.statusToggleButton;

                // Show loading state on button
                const confirmBtn = document.getElementById('confirmStatusToggle');
                const originalText = confirmBtn.innerHTML;
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                confirmBtn.disabled = true;

                // Toggle status via AJAX
                fetch(`/admin/users/${this.currentUserId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showNotification(data.message || 'Status updated successfully', 'success');
                            this.closeModal('statusToggleModal');

                            this.applyStatusUpdateToRow(row, button, data.status);

                            if (this.currentStatusFilter !== 'all' && data.status !== this.currentStatusFilter) {
                                this.removeRowAfterStatusChange(row);
                            }

                            this.refreshStats();
                        } else {
                            throw new Error(data.message || 'Error updating status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Error updating status', 'error');
                    })
                    .finally(() => {
                        confirmBtn.innerHTML = originalText;
                        confirmBtn.disabled = false;
                    });
            }

            /**
             * Toggle user status (active/inactive)
             * @param {HTMLElement} row - Table row element
             * @param {HTMLElement} button - Toggle button element
             */
            toggleUserStatus(row, button) {
                console.log('Toggling status for user:', this.currentUserId);

                // Show loading on button
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;

                // Toggle status via AJAX
                fetch(`/admin/users/${this.currentUserId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showNotification(data.message || 'Status updated successfully', 'success');

                            this.applyStatusUpdateToRow(row, button, data.status);

                            if (this.currentStatusFilter !== 'all' && data.status !== this.currentStatusFilter) {
                                this.removeRowAfterStatusChange(row);
                            }

                            this.refreshStats();
                        } else {
                            throw new Error(data.message || 'Error updating status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Error updating status', 'error');
                        // Reset button state on error
                        button.innerHTML = originalIcon;
                        button.disabled = false;
                    });
            }

            applyStatusUpdateToRow(row, button, newStatus) {
                row.dataset.status = newStatus;

                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.classList.toggle('status-active', newStatus === 'active');
                    statusBadge.classList.toggle('status-inactive', newStatus !== 'active');
                    statusBadge.innerHTML = `
                        <i class="fas ${newStatus === 'active' ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                        ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}
                    `;
                }

                button.dataset.status = newStatus;
                button.innerHTML = `<i class="fas ${newStatus === 'active' ? 'fa-toggle-on' : 'fa-toggle-off'}"></i>`;
                button.disabled = false;
            }

            removeRowAfterStatusChange(row) {
                row.remove();

                const tableBody = document.getElementById('usersTableBody');
                if (!tableBody || tableBody.children.length > 0) {
                    return;
                }

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-8 text-gray-500">
                            <i class="fas fa-users fa-2x mb-4"></i>
                            <p>No users found</p>
                        </td>
                    </tr>
                `;
            }

            /**
             * Send password reset email to user with temporary password
             */
            sendPasswordReset() {
                console.log('Sending password reset for user:', this.currentUserId);

                if (!this.currentUserId) {
                    this.showNotification('User ID not found', 'error');
                    return;
                }

                // Get user email and name from row
                const userName = this.currentUserRow.cells[0].textContent.trim();
                const userEmail = this.currentUserRow.cells[1].textContent.trim();

                // Show confirmation
                const confirmed = confirm(
                    `Reset password for ${userName} (${userEmail})?\n\nA temporary password will be sent to their email address. They must change it upon first login.`
                );

                if (!confirmed) {
                    return;
                }

                // Send password reset request
                fetch(`/admin/users/${this.currentUserId}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': this.csrf(),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(res => {
                        console.log('Response status:', res.status);
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showNotification(data.message || 'Password reset email sent successfully',
                                'success');
                            // Refresh user list
                            this.fetchUsersData(1);
                        } else {
                            throw new Error(data.message || 'Error sending password reset email');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.showNotification(error.message || 'Error sending password reset email', 'error');
                    });
            }
        }

        /**
         * Initialize UserManager when DOM is fully loaded
         */
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, initializing UserManager...');

            try {
                new UserManager();
                console.log('UserManager initialized successfully');
            } catch (error) {
                console.error('Failed to initialize UserManager:', error);

                // Show error notification
                const errorDiv = document.createElement('div');
                errorDiv.className = 'notification error';
                errorDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            Failed to initialize page functionality. Please refresh the page.
        `;
                document.body.appendChild(errorDiv);
            }
        });
    </script>
@endpush
