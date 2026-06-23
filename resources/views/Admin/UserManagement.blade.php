@extends('Admin.layouts.app')

@section('title', 'User Management')

@push('styles')
    <!-- Using FontAwesome for icons (alternative to Bootstrap) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* User Management Styles */
        .user-management {
            padding: 0;
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
            margin-left: auto;
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

        .action-btn.view:hover {
            color: #2563eb;
            background: #dbeafe;
        }

        body.dark-theme .action-btn.view:hover {
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
            position: relative;
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

        .conditional-field .form-label.required::after {
            content: ' *';
            color: #ef4444;
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
            padding-right: 35px;
        }

        .form-control.is-valid {
            border-color: #10b981 !important;
            padding-right: 35px;
        }

        .form-control.is-pending {
            border-color: #f59e0b !important;
            padding-right: 35px;
        }

        select.form-control.is-valid,
        select.form-control.is-invalid,
        select.form-control.is-pending {
            padding-right: 64px;
        }

        .status-radio.is-invalid {
            outline: 1px solid #ef4444;
            border-radius: 10px;
            padding: 8px 10px;
        }

        .status-radio.is-valid {
            outline: 1px solid #10b981;
            border-radius: 10px;
            padding: 8px 10px;
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

        select.form-control ~ .field-validation-icon {
            right: 38px;
        }

        .form-group.has-valid .field-validation-icon,
        .form-group.has-invalid .field-validation-icon,
        .form-group.has-pending .field-validation-icon {
            opacity: 1;
        }

        .form-group.has-valid .field-validation-icon {
            color: #10b981;
        }

        .form-group.has-invalid .field-validation-icon {
            color: #ef4444;
        }

        .form-group.has-pending .field-validation-icon {
            color: #f59e0b;
        }

        body.dark-theme .form-group.has-valid .field-validation-icon {
            color: #34d399;
        }

        body.dark-theme .form-group.has-invalid .field-validation-icon {
            color: #f87171;
        }

        body.dark-theme .form-group.has-pending .field-validation-icon {
            color: #fbbf24;
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
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

        .student-toast-container {
            position: fixed;
            top: 88px;
            right: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: min(360px, calc(100vw - 32px));
            z-index: 2100;
            pointer-events: none;
        }

        .student-toast {
            position: relative;
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 14px;
            padding: 16px 18px 18px;
            border-radius: 18px;
            overflow: hidden;
            pointer-events: auto;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(12px);
            color: #ffffff;
            animation: studentToastIn 0.24s ease;
        }

        .student-toast.is-leaving {
            animation: studentToastOut 0.18s ease forwards;
        }

        .student-toast.success {
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.96), rgba(5, 150, 105, 0.94));
        }

        .student-toast.error {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.97), rgba(190, 24, 93, 0.94));
        }

        .student-toast.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.97), rgba(217, 119, 6, 0.94));
        }

        .student-toast.info {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.97), rgba(79, 70, 229, 0.94));
        }

        .student-toast-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.14);
            font-size: 18px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18);
        }

        .student-toast-copy {
            min-width: 0;
        }

        .student-toast-title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.3;
        }

        .student-toast-message {
            margin-top: 2px;
            font-size: 13px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.96);
        }

        .student-toast-detail {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.4;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.78);
        }

        .student-toast-close {
            appearance: none;
            border: 0;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .student-toast-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .student-toast-progress {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.18);
        }

        .student-toast-progress::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.92);
            transform-origin: left center;
            animation: studentToastProgress 4.2s linear forwards;
        }

        .user-visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .role-confirm-overlay {
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(8px);
            z-index: 2050;
        }

        .role-confirm-card {
            width: min(440px, 100%);
            padding: 24px;
            border-radius: 24px;
            border: 1px solid;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.26);
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
            animation: modalSlideIn 0.3s ease;
        }

        body.light-theme .role-confirm-card {
            background: rgba(255, 255, 255, 0.97);
            border-color: rgba(226, 232, 240, 0.95);
            color: #0f172a;
        }

        body.dark-theme .role-confirm-card {
            background: rgba(15, 23, 42, 0.96);
            border-color: rgba(71, 85, 105, 0.88);
            color: #e2e8f0;
        }

        .role-confirm-header {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .role-confirm-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 22px;
        }

        .role-confirm-icon.danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #b91c1c;
        }

        body.dark-theme .role-confirm-icon.danger {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.85), rgba(127, 29, 29, 0.55));
            color: #fca5a5;
        }

        .role-confirm-icon.primary {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
        }

        body.dark-theme .role-confirm-icon.primary {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.85), rgba(30, 64, 175, 0.55));
            color: #bfdbfe;
        }

        .role-confirm-title {
            margin: 2px 0 6px;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.3;
        }

        .role-confirm-copy p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }

        body.light-theme .role-confirm-copy p {
            color: #475569;
        }

        body.dark-theme .role-confirm-copy p {
            color: #94a3b8;
        }

        .role-confirm-detail {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        body.light-theme .role-confirm-detail {
            background: #f8fafc;
            color: #475569;
        }

        body.dark-theme .role-confirm-detail {
            background: rgba(30, 41, 59, 0.85);
            color: #cbd5e1;
        }

        .role-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        .role-confirm-btn {
            appearance: none;
            border: 1px solid transparent;
            border-radius: 12px;
            min-width: 140px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .role-confirm-btn:hover {
            transform: translateY(-1px);
        }

        .role-confirm-btn.secondary {
            background: transparent;
        }

        body.light-theme .role-confirm-btn.secondary {
            border-color: #cbd5e1;
            color: #334155;
        }

        body.dark-theme .role-confirm-btn.secondary {
            border-color: #475569;
            color: #e2e8f0;
        }

        .role-confirm-btn.primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.24);
        }

        .role-confirm-btn.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.24);
        }

        .role-confirm-btn:disabled {
            opacity: 0.7;
            cursor: wait;
            transform: none;
        }

        .view-user-modal {
            max-width: 720px;
        }

        .user-profile-sheet {
            display: grid;
            gap: 22px;
        }

        .user-profile-hero {
            display: flex;
            gap: 18px;
            align-items: center;
            padding: 18px;
            border-radius: 18px;
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
            border: 1px solid #dbeafe;
        }

        body.dark-theme .user-profile-hero {
            background: linear-gradient(135deg, #172554 0%, #111827 100%);
            border-color: #1d4ed8;
        }

        .user-profile-avatar {
            width: 84px;
            height: 84px;
            border-radius: 9999px;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 30px;
            font-weight: 700;
            box-shadow: 0 18px 36px rgba(37, 99, 235, 0.22);
        }

        .user-profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .user-profile-avatar img[hidden] {
            display: none !important;
        }

        .user-profile-avatar span {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }

        .user-profile-avatar span[hidden] {
            display: none !important;
        }

        .user-profile-meta {
            min-width: 0;
            flex: 1;
        }

        .user-profile-name-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .user-profile-name {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.2;
            color: #0f172a;
        }

        body.dark-theme .user-profile-name {
            color: #f8fafc;
        }

        .user-profile-email {
            margin-top: 4px;
            font-size: 14px;
            color: #475569;
            word-break: break-word;
        }

        body.dark-theme .user-profile-email {
            color: #cbd5e1;
        }

        .user-profile-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 12px;
        }

        .user-profile-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
        }

        .user-profile-chip.role-admin {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .user-profile-chip.role-staff {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .user-profile-chip.role-student {
            background: #dcfce7;
            color: #166534;
        }

        .user-profile-chip.status-active {
            background: #dcfce7;
            color: #166534;
        }

        .user-profile-chip.status-inactive {
            background: #fee2e2;
            color: #b91c1c;
        }

        body.dark-theme .user-profile-chip.role-admin {
            background: #1e3a8a;
            color: #bfdbfe;
        }

        body.dark-theme .user-profile-chip.role-staff {
            background: #581c87;
            color: #e9d5ff;
        }

        body.dark-theme .user-profile-chip.role-student {
            background: #14532d;
            color: #bbf7d0;
        }

        body.dark-theme .user-profile-chip.status-active {
            background: #14532d;
            color: #bbf7d0;
        }

        body.dark-theme .user-profile-chip.status-inactive {
            background: #7f1d1d;
            color: #fecaca;
        }

        .user-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .user-detail-item {
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        body.dark-theme .user-detail-item {
            border-color: #334155;
            background: #0f172a;
        }

        .user-detail-item.full {
            grid-column: 1 / -1;
        }

        .user-detail-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
        }

        body.dark-theme .user-detail-label {
            color: #94a3b8;
        }

        .user-detail-value {
            margin-top: 6px;
            font-size: 14px;
            line-height: 1.55;
            color: #0f172a;
            word-break: break-word;
        }

        body.dark-theme .user-detail-value {
            color: #f8fafc;
        }

        @keyframes studentToastIn {
            from {
                opacity: 0;
                transform: translate3d(20px, -8px, 0) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @keyframes studentToastOut {
            from {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }

            to {
                opacity: 0;
                transform: translate3d(18px, -4px, 0) scale(0.98);
            }
        }

        @keyframes studentToastProgress {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        @media (max-width: 768px) {
            .student-toast-container {
                top: 76px;
                right: 16px;
                left: 16px;
                width: auto;
            }

            .user-profile-hero {
                flex-direction: column;
                align-items: flex-start;
            }

            .user-detail-grid {
                grid-template-columns: 1fr;
            }

            .role-confirm-actions,
            .modal-footer {
                flex-wrap: wrap;
            }

            .role-confirm-card {
                padding: 22px;
            }

            .role-confirm-btn {
                width: 100%;
            }
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
                    <input type="text" class="search-input" id="searchInput" placeholder="Search by name, email, username..." value="{{ $search ?? '' }}">
                </div>

                <div class="filters-container">
                    <select class="filter-select" id="roleFilter">
                        <option value="all" {{ ($role ?? 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
                        <option value="admin" {{ ($role ?? 'all') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ ($role ?? 'all') === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="student" {{ ($role ?? 'all') === 'student' ? 'selected' : '' }}>Student</option>
                    </select>

                    <select class="filter-select" id="statusFilter">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ ($status ?? 'all') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ ($status ?? 'all') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <select class="filter-select" id="sortFilter">
                        <option value="recently-added" {{ ($sort ?? 'recently-added') === 'recently-added' ? 'selected' : '' }}>Recently Added</option>
                        <option value="name-asc" {{ ($sort ?? 'recently-added') === 'name-asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name-desc" {{ ($sort ?? 'recently-added') === 'name-desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    </select>
                </div>

                <button type="button" class="btn btn-outline" id="resetFiltersBtn">
                    <i class="fas fa-rotate-left"></i>
                    Reset
                </button>

                <label class="admin-table-entries-control" for="usersEntriesSelect">
                    <span>Show</span>
                    <select class="admin-table-entries-select" id="usersEntriesSelect" aria-label="Show user entries">
                        @foreach ([10, 20, 50, 100] as $entryCount)
                            <option value="{{ $entryCount }}" {{ (int) ($perPage ?? 10) === $entryCount ? 'selected' : '' }}>{{ $entryCount }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </label>

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
                                <th>Identity / Department</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            @foreach ($users as $user)
                                @include('Admin.partials.user-row', compact('user'))
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="paginationContainer" class="mt-4">
                    {!! view('shared.admin-table-pagination', ['paginator' => $users])->render() !!}
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
                <p class="modal-description">Create an invited user account. Staff and students will finish registration themselves and set their own password.</p>

                <form id="addUserForm" novalidate>
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
                        <span class="field-error" id="addPhoneError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select class="form-control" name="gender" id="addGender">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="field-error" id="addGenderError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff,student">
                        <label class="form-label required">Department</label>
                        <select class="form-control" name="department_id" id="addDepartmentSelect">
                            <option value="">Select Department</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="field-error" id="addDepartmentError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label required">Staff ID</label>
                        <input type="text" class="form-control" name="staff_id" id="addStaffId" placeholder="STAFF-000001">
                        <span class="field-error" id="addStaffIdError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label required">Staff Designation</label>
                        <input type="text" class="form-control" name="designation" id="addDesignation" placeholder="Staff Member">
                        <span class="field-error" id="addDesignationError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="staff">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" name="join_date" id="addJoinDate">
                        <span class="field-error" id="addJoinDateError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label required" id="rollNoLabel">Roll Number</label>
                        <input type="text" class="form-control" name="roll_no" id="addRollNo" placeholder="CSE-2021-001">
                        <span class="field-error" id="addRollNoError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label required">Batch</label>
                        <input type="text" class="form-control" name="batch" id="addBatch" placeholder="2024">
                        <span class="field-error" id="addBatchError"></span>
                    </div>

                    <div class="form-group conditional-field" data-for="student">
                        <label class="form-label required">Semester</label>
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
                        <textarea class="form-control" name="address" id="addAddress" placeholder="123 Main St, City, Country" rows="3"></textarea>
                        <span class="field-error" id="addAddressError"></span>
                    </div>

                    <div class="form-group" id="addStatusGroup">
                        <label class="form-label">Status</label>
                        <div class="status-radio" id="addStatusRadio">
                            <label class="status-option">
                                <input type="radio" name="status" value="active" id="addStatusActiveRadio" checked>
                                <span>Active</span>
                            </label>
                            <label class="status-option">
                                <input type="radio" name="status" value="inactive">
                                <span>Inactive</span>
                            </label>
                        </div>
                        <span class="field-error" id="addStatusError"></span>
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

                <form id="editUserForm" novalidate>
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
                        <span class="field-error" id="editPhoneError"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select class="form-control" id="editGender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="field-error" id="editGenderError"></span>
                    </div>

                    <div class="form-group" id="editDepartmentGroup" style="display: none;">
                        <label class="form-label required">Department</label>
                        <select class="form-control" id="editDepartment" name="department_id">
                            <option value="">Select Department</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <span class="field-error" id="editDepartmentError"></span>
                    </div>

                    <div class="form-group" id="editStaffIdGroup" style="display: none;">
                        <label class="form-label required">Staff ID</label>
                        <input type="text" class="form-control" id="editStaffId" name="staff_id" placeholder="STAFF-000001">
                        <span class="field-error" id="editStaffIdError"></span>
                    </div>

                    <div class="form-group" id="editStaffDesignationGroup" style="display: none;">
                        <label class="form-label required">Staff Designation</label>
                        <input type="text" class="form-control" id="editDesignation" name="designation" placeholder="Staff Member">
                        <span class="field-error" id="editDesignationError"></span>
                    </div>

                    <div class="form-group" id="editStaffJoinDateGroup" style="display: none;">
                        <label class="form-label">Join Date</label>
                        <input type="date" class="form-control" id="editJoinDate" name="join_date">
                        <span class="field-error" id="editJoinDateError"></span>
                    </div>

                    <div class="form-group" id="editRollNoGroup" style="display: none;">
                        <label class="form-label required" id="editRollNoLabel">Roll Number</label>
                        <input type="text" class="form-control" id="editRollNo" name="roll_no"
                            placeholder="CSE-2021-001">
                        <span class="field-error" id="editRollNoError"></span>
                    </div>

                    <div class="form-group" id="editBatchGroup" style="display: none;">
                        <label class="form-label required">Batch</label>
                        <input type="text" class="form-control" id="editBatch" name="batch" placeholder="2024">
                        <span class="field-error" id="editBatchError"></span>
                    </div>

                    <div class="form-group" id="editSemesterGroup" style="display: none;">
                        <label class="form-label required">Semester</label>
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
                        <span class="field-error" id="editAddressError"></span>
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

    <!-- View User Modal -->
    <div id="viewUserModal" class="modal-overlay">
        <div class="modal view-user-modal">
            <div class="modal-header">
                <h3 class="modal-title">User Details</h3>
                <button type="button" class="modal-close-btn" id="closeViewUserModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="user-profile-sheet">
                    <div class="user-profile-hero">
                        <div class="user-profile-avatar" id="viewUserAvatar">
                            <img id="viewUserAvatarImage" src="" alt="User profile photo" hidden>
                            <span id="viewUserAvatarFallback">U</span>
                        </div>
                        <div class="user-profile-meta">
                            <div class="user-profile-name-row">
                                <h4 class="user-profile-name" id="viewUserName">Loading...</h4>
                                <span class="user-profile-chip role-admin" id="viewUserSelfChip" hidden>
                                    <i class="fas fa-user"></i>
                                    You
                                </span>
                            </div>
                            <div class="user-profile-email" id="viewUserEmail">loading@example.com</div>
                            <div class="user-profile-chip-row">
                                <span class="user-profile-chip role-admin" id="viewUserRoleChip">
                                    <i class="fas fa-shield-alt"></i>
                                    Admin
                                </span>
                                <span class="user-profile-chip status-active" id="viewUserStatusChip">
                                    <i class="fas fa-check-circle"></i>
                                    Active
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="user-detail-grid">
                        <div class="user-detail-item">
                            <div class="user-detail-label">Phone</div>
                            <div class="user-detail-value" id="viewUserPhone">Not provided</div>
                        </div>
                        <div class="user-detail-item">
                            <div class="user-detail-label">Gender</div>
                            <div class="user-detail-value" id="viewUserGender">Not provided</div>
                        </div>
                        <div class="user-detail-item">
                            <div class="user-detail-label">Department</div>
                            <div class="user-detail-value" id="viewUserDepartment">Not assigned</div>
                        </div>
                        <div class="user-detail-item" id="viewUserStaffIdItem" hidden>
                            <div class="user-detail-label">Staff ID</div>
                            <div class="user-detail-value" id="viewUserStaffId">-</div>
                        </div>
                        <div class="user-detail-item" id="viewUserDesignationItem" hidden>
                            <div class="user-detail-label">Designation</div>
                            <div class="user-detail-value" id="viewUserDesignation">-</div>
                        </div>
                        <div class="user-detail-item" id="viewUserJoinDateItem" hidden>
                            <div class="user-detail-label">Join Date</div>
                            <div class="user-detail-value" id="viewUserJoinDate">-</div>
                        </div>
                        <div class="user-detail-item" id="viewUserRollNoItem" hidden>
                            <div class="user-detail-label">Student ID</div>
                            <div class="user-detail-value" id="viewUserRollNo">-</div>
                        </div>
                        <div class="user-detail-item" id="viewUserBatchItem" hidden>
                            <div class="user-detail-label">Batch</div>
                            <div class="user-detail-value" id="viewUserBatch">-</div>
                        </div>
                        <div class="user-detail-item" id="viewUserSemesterItem" hidden>
                            <div class="user-detail-label">Semester</div>
                            <div class="user-detail-value" id="viewUserSemester">-</div>
                        </div>
                        <div class="user-detail-item">
                            <div class="user-detail-label">Last Login</div>
                            <div class="user-detail-value" id="viewUserLastLogin">Never</div>
                        </div>
                        <div class="user-detail-item">
                            <div class="user-detail-label">Created</div>
                            <div class="user-detail-value" id="viewUserCreatedAt">Unknown</div>
                        </div>
                        <div class="user-detail-item full">
                            <div class="user-detail-label">Address</div>
                            <div class="user-detail-value" id="viewUserAddress">Not provided</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" id="closeViewUserFooter">Close</button>
            </div>
        </div>
    </div>

    <!-- Reset Password Confirmation Modal -->
    <div id="resetPasswordModal" class="modal-overlay role-confirm-overlay" aria-hidden="true">
        <div class="role-confirm-card" role="dialog" aria-modal="true" aria-labelledby="resetPasswordModalTitle">
            <div class="role-confirm-header">
                <div class="role-confirm-icon primary" aria-hidden="true">
                    <i class="fas fa-key"></i>
                </div>
                <div class="role-confirm-copy">
                    <h3 id="resetPasswordModalTitle" class="role-confirm-title">Reset password</h3>
                    <p id="resetPasswordModalMessage">Send a temporary password to this user?</p>
                    <div id="resetPasswordModalDetail" class="role-confirm-detail">User account</div>
                </div>
            </div>

            <div class="role-confirm-actions">
                <button type="button" id="cancelResetPassword" class="role-confirm-btn secondary">Cancel</button>
                <button type="button" id="submitResetPassword" class="role-confirm-btn primary">Reset Password</button>
            </div>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div id="deleteUserModal" class="modal-overlay role-confirm-overlay" aria-hidden="true">
        <div class="role-confirm-card" role="dialog" aria-modal="true" aria-labelledby="deleteUserModalTitle">
            <div class="role-confirm-header">
                <div class="role-confirm-icon danger" aria-hidden="true">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div class="role-confirm-copy">
                    <h3 id="deleteUserModalTitle" class="role-confirm-title">Delete user</h3>
                    <p id="deleteUserModalMessage">This action cannot be undone.</p>
                    <div id="deleteUserModalDetail" class="role-confirm-detail">User account</div>
                </div>
            </div>

            <div class="role-confirm-actions">
                <button type="button" id="cancelDeleteUser" class="role-confirm-btn secondary">Cancel</button>
                <button type="button" id="confirmDeleteUser" class="role-confirm-btn danger">Delete User</button>
            </div>
        </div>
    </div>

    <div id="userToastContainer" class="student-toast-container" aria-live="polite" aria-atomic="true"></div>
    <div id="userLiveRegion" class="user-visually-hidden" aria-live="polite" aria-atomic="true"></div>
@endsection

@push('scripts')
    <script>
        const USER_VALIDATION_MESSAGES = {
            name: {
                required: 'Enter the user\'s full name.',
                min: 'Full name must be at least 2 characters long.',
                format: 'Full name can use letters and spaces only.',
            },
            email: {
                required: 'Enter the user\'s email address.',
                format: 'Enter a valid email address, like user@example.com.',
                unique: 'This email is already assigned to another user.',
            },
            role: {
                required: 'Select a user role.',
                invalid: 'Select a valid user role.',
            },
            phone: {
                format: 'Enter a valid phone number with country code, like +9779812345678.',
                unique: 'This phone number is already assigned to another user.',
            },
            gender: {
                invalid: 'Select a valid gender option.',
            },
            address: {
                min: 'Address must be at least 10 characters long.',
                unsafe: 'Address contains unsupported characters. Remove any HTML or script-like content.',
            },
            department_id: {
                required: 'Select a department.',
            },
            staff_id: {
                required: 'Enter the staff ID.',
                min: 'Staff ID must be at least 3 characters long.',
                format: 'Staff ID can use letters, numbers, and hyphens only.',
                unique: 'This staff ID is already in use.',
            },
            designation: {
                min: 'Staff designation must be at least 2 characters long.',
            },
            join_date: {
                invalid: 'Enter a valid join date.',
                future: 'Join date cannot be in the future.',
            },
            roll_no: {
                required: 'Enter the student ID.',
                min: 'Student ID must be at least 3 characters long.',
                format: 'Student ID can use letters, numbers, and hyphens only.',
                unique: 'This student ID is already in use.',
            },
            batch: {
                required: 'Enter the batch year.',
                format: 'Batch year must be a 4-digit year.',
            },
            semester: {
                required: 'Select the current semester.',
                format: 'Semester must be a number between 1 and 8.',
            },
            status: {
                required: 'Select the user status.',
            },
        };

        function normalizeLiveSingleSpaceValue(value) {
            const rawValue = String(value ?? '');
            const withoutLeadingWhitespace = rawValue.replace(/^\s+/, '');

            if (withoutLeadingWhitespace === '') {
                return '';
            }

            const hadTrailingWhitespace = /\s$/.test(withoutLeadingWhitespace);
            const normalized = withoutLeadingWhitespace.replace(/\s{2,}/g, ' ');

            if (!hadTrailingWhitespace) {
                return normalized;
            }

            return `${normalized.replace(/\s+$/, '')} `;
        }

        function normalizeUserLiveFieldValue(fieldName, value) {
            const rawValue = String(value ?? '');

            switch (fieldName) {
                case 'name':
                case 'address':
                case 'designation':
                    return normalizeLiveSingleSpaceValue(rawValue);
                case 'email':
                    return rawValue.trim().toLowerCase();
                case 'phone': {
                    const trimmed = rawValue.trim();
                    const digits = trimmed.replace(/\D/g, '');

                    if (!trimmed) {
                        return '';
                    }

                    return trimmed.startsWith('+') ? `+${digits}` : digits;
                }
                case 'staff_id':
                case 'roll_no':
                    return rawValue.trim().toUpperCase();
                default:
                    return rawValue.trim();
            }
        }

        class LiveUserFormValidator {
            constructor({ formId, submitButtonId, fields, createMode = false, getUserId = () => null }) {
                this.form = document.getElementById(formId);
                this.submitButton = document.getElementById(submitButtonId);
                this.fields = fields;
                this.createMode = createMode;
                this.getUserId = getUserId;
                this.abortControllers = {};
                this.pendingFields = new Set();
                this.verifiedValues = {};
                this.fieldState = {};
                this.isSubmitting = false;

                this.ensureFieldIcons();
                this.attachListeners();
                this.refreshVisibility();
            }

            getFieldConfig(fieldName) {
                return this.fields[fieldName] || null;
            }

            getFieldElement(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config) {
                    return null;
                }

                if (config.type === 'radio') {
                    return this.form?.querySelector(`input[name="${config.name}"]:checked`) || null;
                }

                return document.getElementById(config.id);
            }

            getFieldElements(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config || !this.form) {
                    return [];
                }

                if (config.type === 'radio') {
                    return Array.from(this.form.querySelectorAll(`input[name="${config.name}"]`));
                }

                const element = document.getElementById(config.id);
                return element ? [element] : [];
            }

            getFieldGroup(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config) {
                    return null;
                }

                if (config.groupId) {
                    return document.getElementById(config.groupId);
                }

                return this.getFieldElement(fieldName)?.closest('.form-group') || null;
            }

            getErrorElement(fieldName) {
                const config = this.getFieldConfig(fieldName);
                return config?.errorId ? document.getElementById(config.errorId) : null;
            }

            getFieldIcon(fieldName) {
                return this.getFieldGroup(fieldName)?.querySelector('.field-validation-icon') ?? null;
            }

            ensureFieldIcons() {
                if (!this.form) {
                    return;
                }

                Object.keys(this.fields).forEach((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    if (!config || config.type === 'radio') {
                        return;
                    }

                    const group = this.getFieldGroup(fieldName);
                    const error = this.getErrorElement(fieldName);
                    const element = document.getElementById(config.id);
                    if (!group || !element || group.querySelector('.field-validation-icon')) {
                        return;
                    }

                    const icon = document.createElement('span');
                    icon.className = 'field-validation-icon';
                    icon.setAttribute('aria-hidden', 'true');

                    if (error && error.parentElement === group) {
                        group.insertBefore(icon, error);
                    } else {
                        group.appendChild(icon);
                    }
                });
            }

            clearFieldVisualState(fieldName) {
                const group = this.getFieldGroup(fieldName);
                const error = this.getErrorElement(fieldName);
                const inputs = this.getFieldElements(fieldName);
                const icon = this.getFieldIcon(fieldName);
                const radioWrapper = fieldName === 'status' ? document.getElementById('addStatusRadio') : null;

                group?.classList.remove('error', 'has-valid', 'has-invalid', 'has-pending');
                inputs.forEach((input) => {
                    input.classList.remove('is-valid', 'is-invalid', 'is-pending');
                });
                radioWrapper?.classList.remove('is-valid', 'is-invalid');

                if (error) {
                    error.textContent = '';
                    error.classList.remove('visible');
                }

                if (icon) {
                    icon.innerHTML = '';
                }
            }

            clearAllVisualStates() {
                Object.keys(this.fields).forEach((fieldName) => this.clearFieldVisualState(fieldName));
            }

            focusField(fieldName) {
                const field = this.getFieldElement(fieldName) || this.getFieldElements(fieldName)[0];
                const group = this.getFieldGroup(fieldName) || field;

                if (!field || !group) {
                    return;
                }

                const modal = field.closest('.modal');
                if (modal) {
                    const modalRect = modal.getBoundingClientRect();
                    const groupRect = group.getBoundingClientRect();
                    const nextScrollTop = modal.scrollTop
                        + (groupRect.top - modalRect.top)
                        - (modal.clientHeight / 2)
                        + (groupRect.height / 2);

                    modal.scrollTo({
                        top: Math.max(0, nextScrollTop),
                        behavior: 'smooth',
                    });
                } else {
                    group.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                window.setTimeout(() => {
                    field.focus({ preventScroll: true });
                }, 160);
            }

            getRole() {
                return this.normalizeValue('role', this.getRawValue('role')) || 'student';
            }

            getActiveFields(role = this.getRole()) {
                return Object.keys(this.fields).filter((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    if (!config) {
                        return false;
                    }

                    if (!config.roles || config.roles.length === 0) {
                        return true;
                    }

                    return config.roles.includes(role);
                });
            }

            getRawValue(fieldName) {
                const config = this.getFieldConfig(fieldName);
                if (!config || !this.form) {
                    return '';
                }

                if (config.type === 'radio') {
                    return this.form.querySelector(`input[name="${config.name}"]:checked`)?.value ?? '';
                }

                return document.getElementById(config.id)?.value ?? '';
            }

            normalizeValue(fieldName, value) {
                const rawValue = String(value ?? '');

                switch (fieldName) {
                    case 'name':
                    case 'address':
                    case 'designation':
                        return rawValue.replace(/\s+/g, ' ').trim();
                    case 'email':
                        return rawValue.trim().toLowerCase();
                    case 'phone': {
                        const trimmed = rawValue.trim();
                        const digits = trimmed.replace(/\D/g, '');

                        if (!trimmed) {
                            return '';
                        }

                        return trimmed.startsWith('+') ? `+${digits}` : digits;
                    }
                    case 'roll_no':
                        return rawValue.trim().toUpperCase();
                    default:
                        return rawValue.trim();
                }
            }

            setValue(fieldName, value) {
                const config = this.getFieldConfig(fieldName);
                if (!config || !this.form) {
                    return;
                }

                if (config.type === 'radio') {
                    this.getFieldElements(fieldName).forEach((input) => {
                        input.checked = input.value === value;
                    });
                    return;
                }

                const element = document.getElementById(config.id);
                if (element) {
                    element.value = value;
                }
            }

            collectValues({ syncFields = true } = {}) {
                const values = {};

                Object.keys(this.fields).forEach((fieldName) => {
                    const normalized = this.normalizeValue(fieldName, this.getRawValue(fieldName));
                    values[fieldName] = normalized;

                    const config = this.getFieldConfig(fieldName);
                    if (syncFields && config?.type !== 'radio') {
                        const element = document.getElementById(config.id);
                        if (element && element.value !== normalized) {
                            element.value = normalized;
                        }
                    }
                });

                return values;
            }

            isUniqueField(fieldName, values) {
                if (fieldName === 'email') {
                    return Boolean(values.email);
                }

                if (fieldName === 'phone') {
                    return Boolean(values.phone);
                }

                if (fieldName === 'roll_no') {
                    return values.role === 'student' && Boolean(values.roll_no);
                }

                return false;
            }

            setState(fieldName, state, message = '') {
                const group = this.getFieldGroup(fieldName);
                const error = this.getErrorElement(fieldName);
                const inputs = this.getFieldElements(fieldName);
                const icon = this.getFieldIcon(fieldName);
                const radioWrapper = fieldName === 'status' ? document.getElementById('addStatusRadio') : null;

                group?.classList.remove('error', 'has-valid', 'has-invalid', 'has-pending');
                inputs.forEach((input) => input.classList.remove('is-valid', 'is-invalid', 'is-pending'));
                radioWrapper?.classList.remove('is-valid', 'is-invalid');

                if (state === 'valid') {
                    group?.classList.add('has-valid');
                    inputs.forEach((input) => input.classList.add('is-valid'));
                    radioWrapper?.classList.add('is-valid');
                } else if (state === 'invalid') {
                    group?.classList.add('error');
                    group?.classList.add('has-invalid');
                    inputs.forEach((input) => input.classList.add('is-invalid'));
                    radioWrapper?.classList.add('is-invalid');
                } else if (state === 'pending') {
                    group?.classList.add('has-pending');
                    inputs.forEach((input) => input.classList.add('is-pending'));
                }

                if (icon) {
                    icon.innerHTML = state === 'valid'
                        ? '<i class="fas fa-check-circle"></i>'
                        : state === 'invalid'
                            ? '<i class="fas fa-exclamation-circle"></i>'
                            : state === 'pending'
                                ? '<i class="fas fa-spinner fa-spin"></i>'
                                : '';
                }

                if (error) {
                    error.textContent = message;
                    error.classList.toggle('visible', state === 'invalid' && Boolean(message));
                }

                if (state === 'pending') {
                    this.pendingFields.add(fieldName);
                    this.fieldState[fieldName] = { valid: false };
                } else {
                    this.pendingFields.delete(fieldName);
                    this.fieldState[fieldName] = { valid: state === 'valid' };
                }

                this.updateSubmitState();
            }

            clearFieldState(fieldName, { logicalValid = false, keepState = true } = {}) {
                const group = this.getFieldGroup(fieldName);
                const error = this.getErrorElement(fieldName);
                const inputs = this.getFieldElements(fieldName);
                const icon = this.getFieldIcon(fieldName);
                const radioWrapper = fieldName === 'status' ? document.getElementById('addStatusRadio') : null;

                group?.classList.remove('error', 'has-valid', 'has-invalid', 'has-pending');
                inputs.forEach((input) => input.classList.remove('is-valid', 'is-invalid', 'is-pending'));
                radioWrapper?.classList.remove('is-valid', 'is-invalid');

                if (error) {
                    error.textContent = '';
                    error.classList.remove('visible');
                }

                if (icon) {
                    icon.innerHTML = '';
                }

                this.pendingFields.delete(fieldName);
                if (keepState) {
                    this.fieldState[fieldName] = { valid: logicalValid };
                } else {
                    delete this.fieldState[fieldName];
                }
                this.updateSubmitState();
            }

            isFieldRequired(fieldName, values = this.collectValues()) {
                const role = values.role;

                switch (fieldName) {
                    case 'name':
                    case 'email':
                    case 'role':
                        return true;
                    case 'status':
                        return this.createMode;
                    case 'department_id':
                        return role === 'student';
                    case 'staff_id':
                        return role === 'staff';
                    case 'roll_no':
                    case 'batch':
                    case 'semester':
                        return role === 'student';
                    default:
                        return false;
                }
            }

            getSyncMessage(fieldName, values) {
                const value = values[fieldName] ?? '';
                const role = values.role;

                switch (fieldName) {
                    case 'name':
                        if (!value) return USER_VALIDATION_MESSAGES.name.required;
                        if (value.length < 2) return USER_VALIDATION_MESSAGES.name.min;
                        if (!/^[A-Za-z ]+$/.test(value)) return USER_VALIDATION_MESSAGES.name.format;
                        return '';
                    case 'email':
                        if (!value) return USER_VALIDATION_MESSAGES.email.required;
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return USER_VALIDATION_MESSAGES.email.format;
                        return '';
                    case 'role':
                        if (!value) return USER_VALIDATION_MESSAGES.role.required;
                        if (!['admin', 'staff', 'student'].includes(value)) return USER_VALIDATION_MESSAGES.role.invalid;
                        return '';
                    case 'phone':
                        if (!value) return '';
                        if (!/^\+[1-9]\d{7,14}$/.test(value)) return USER_VALIDATION_MESSAGES.phone.format;
                        return '';
                    case 'gender':
                        if (!value) return '';
                        if (!['male', 'female', 'other'].includes(value)) return USER_VALIDATION_MESSAGES.gender.invalid;
                        return '';
                    case 'address':
                        if (!value) return '';
                        if (value.length < 10) return USER_VALIDATION_MESSAGES.address.min;
                        if (/<[^>]*>/.test(value)) return USER_VALIDATION_MESSAGES.address.unsafe;
                        return '';
                    case 'department_id':
                        if (role !== 'student') return '';
                        return value ? '' : USER_VALIDATION_MESSAGES.department_id.required;
                    case 'staff_id':
                        if (role !== 'staff') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.staff_id.required;
                        if (value.length < 3) return USER_VALIDATION_MESSAGES.staff_id.min;
                        if (!/^[A-Za-z0-9-]+$/.test(value)) return USER_VALIDATION_MESSAGES.staff_id.format;
                        return '';
                    case 'designation':
                        if (role !== 'staff') return '';
                        if (!value) return '';
                        if (value.length < 2) return USER_VALIDATION_MESSAGES.designation.min;
                        return '';
                    case 'join_date': {
                        if (role !== 'staff') return '';
                        if (!value) return '';
                        const joinDate = new Date(value);
                        if (Number.isNaN(joinDate.getTime())) return USER_VALIDATION_MESSAGES.join_date.invalid;
                        const today = new Date();
                        const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                        if (joinDate > todayMidnight) return USER_VALIDATION_MESSAGES.join_date.future;
                        return '';
                    }
                    case 'roll_no':
                        if (role !== 'student') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.roll_no.required;
                        if (value.length < 3) return USER_VALIDATION_MESSAGES.roll_no.min;
                        if (!/^[A-Za-z0-9-]+$/.test(value)) return USER_VALIDATION_MESSAGES.roll_no.format;
                        return '';
                    case 'batch':
                        if (role !== 'student') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.batch.required;
                        if (!/^(19|20)\d{2}$/.test(value)) return USER_VALIDATION_MESSAGES.batch.format;
                        return '';
                    case 'semester':
                        if (role !== 'student') return '';
                        if (!value) return USER_VALIDATION_MESSAGES.semester.required;
                        if (!/^[1-8]$/.test(value)) return USER_VALIDATION_MESSAGES.semester.format;
                        return '';
                    case 'status':
                        if (!this.createMode) return '';
                        return value ? '' : USER_VALIDATION_MESSAGES.status.required;
                    default:
                        return '';
                }
            }

            async runUniqueValidation(fieldName, value, values) {
                if (!this.isUniqueField(fieldName, values)) {
                    return true;
                }

                if (this.verifiedValues[fieldName] === value) {
                    this.setState(fieldName, 'valid');
                    return true;
                }

                this.abortControllers[fieldName]?.abort();
                const controller = new AbortController();
                this.abortControllers[fieldName] = controller;
                this.setState(fieldName, 'pending');

                try {
                    const payload = {
                        ...values,
                        field: fieldName,
                        user_id: this.getUserId(),
                    };

                    const response = await fetch('{{ route('admin.users.validate-field') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        signal: controller.signal,
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        const data = await response.json();
                        const message = data.message || USER_VALIDATION_MESSAGES[fieldName]?.unique || 'This value is already in use.';
                        this.setState(fieldName, 'invalid', message);
                        return false;
                    }

                    if (this.collectValues()[fieldName] !== value) {
                        return false;
                    }

                    this.verifiedValues[fieldName] = value;
                    this.setState(fieldName, 'valid');
                    return true;
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return false;
                    }

                    this.setState(fieldName, 'invalid', 'Could not verify this field right now. Please try again.');
                    return false;
                }
            }

            async validateField(fieldName, { runUniqueCheck = false, syncFields = true } = {}) {
                const values = this.collectValues({ syncFields });
                const activeFields = this.getActiveFields(values.role);

                if (!activeFields.includes(fieldName)) {
                    this.clearFieldState(fieldName);
                    return true;
                }

                const syncMessage = this.getSyncMessage(fieldName, values);
                if (syncMessage) {
                    this.setState(fieldName, 'invalid', syncMessage);
                    return false;
                }

                if (this.isUniqueField(fieldName, values)) {
                    if (!runUniqueCheck) {
                        this.setState(fieldName, 'valid');
                        return true;
                    }

                    return this.runUniqueValidation(fieldName, values[fieldName], values);
                }

                this.setState(fieldName, 'valid');
                return true;
            }

            async validateAll({ showFirstErrorOnly = false } = {}) {
                const values = this.collectValues();
                const activeFields = this.getActiveFields(values.role);
                let firstInvalidField = null;
                let allValid = true;

                if (showFirstErrorOnly) {
                    activeFields.forEach((fieldName) => this.clearFieldVisualState(fieldName));
                }

                for (const fieldName of activeFields) {
                    const isValid = await this.validateField(fieldName, { runUniqueCheck: true });
                    if (!isValid) {
                        allValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = fieldName;
                            if (showFirstErrorOnly) {
                                break;
                            }
                        }
                    }
                }

                if (!allValid && firstInvalidField) {
                    this.focusField(firstInvalidField);
                }

                return allValid;
            }

            refreshVisibility() {
                const activeFields = this.getActiveFields();
                const values = this.collectValues();
                Object.keys(this.fields).forEach((fieldName) => {
                    if (!activeFields.includes(fieldName)) {
                        this.abortControllers[fieldName]?.abort();
                        delete this.verifiedValues[fieldName];
                        this.clearFieldState(fieldName, { logicalValid: false, keepState: false });
                    } else {
                        const value = values[fieldName];
                        const isRequired = this.isFieldRequired(fieldName, values);
                        const shouldStayNeutral = !value && !['role', 'status'].includes(fieldName);

                        if (shouldStayNeutral) {
                            this.clearFieldState(fieldName, { logicalValid: !isRequired });
                        } else {
                            this.validateField(fieldName, { runUniqueCheck: false });
                        }
                    }
                });

                this.updateSubmitState();
            }

            resetForm() {
                this.form?.reset();
                Object.values(this.abortControllers).forEach((controller) => controller?.abort());
                this.abortControllers = {};
                this.pendingFields.clear();
                this.verifiedValues = {};
                this.fieldState = {};
                Object.keys(this.fields).forEach((fieldName) => this.clearFieldState(fieldName));
                this.refreshVisibility();
                this.clearAllVisualStates();
            }

            seed(values = {}) {
                this.resetForm();

                Object.keys(this.fields).forEach((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    if (!config) {
                        return;
                    }

                    let value = '';
                    if (fieldName === 'department_id') {
                        value = values.department_id ?? '';
                    } else {
                        value = values[fieldName] ?? '';
                    }

                    const normalized = this.normalizeValue(fieldName, value);
                    this.setValue(fieldName, normalized);

                    if (['email', 'phone', 'roll_no', 'staff_id'].includes(fieldName) && normalized) {
                        this.verifiedValues[fieldName] = normalized;
                    }
                });

                this.refreshVisibility();
                this.clearAllVisualStates();
            }

            updateSubmitState() {
                if (!this.submitButton) {
                    return;
                }

                this.submitButton.disabled = this.isSubmitting;
            }

            applyServerErrors(errors = {}, { showFirstErrorOnly = false } = {}) {
                const entries = Object.entries(errors);
                let firstFieldName = null;

                if (showFirstErrorOnly) {
                    this.clearAllVisualStates();
                }

                entries.forEach(([fieldName, fieldErrors]) => {
                    if (showFirstErrorOnly && firstFieldName) {
                        return;
                    }

                    const message = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors;
                    if (!message || !this.fields[fieldName]) {
                        return;
                    }

                    this.setState(fieldName, 'invalid', message);
                    firstFieldName = fieldName;
                });

                if (firstFieldName) {
                    this.focusField(firstFieldName);
                }
            }

            setSubmitting(isSubmitting) {
                this.isSubmitting = Boolean(isSubmitting);
                this.updateSubmitState();
            }

            toFormData() {
                const values = this.collectValues();
                const formData = new FormData(this.form);

                Object.entries(values).forEach(([fieldName, value]) => {
                    formData.set(fieldName, value);
                });

                return formData;
            }

            attachListeners() {
                Object.keys(this.fields).forEach((fieldName) => {
                    const config = this.getFieldConfig(fieldName);
                    const elements = this.getFieldElements(fieldName);

                    elements.forEach((element) => {
                        const inputEvent = config?.type === 'radio' || element.tagName === 'SELECT' || element.type === 'date' ? 'change' : 'input';

                        element.addEventListener(inputEvent, () => {
                            const normalized = inputEvent === 'input'
                                ? normalizeUserLiveFieldValue(fieldName, this.getRawValue(fieldName))
                                : this.normalizeValue(fieldName, this.getRawValue(fieldName));

                            if (config?.type !== 'radio' && element.value !== normalized) {
                                element.value = normalized;
                            }

                            if (['email', 'phone', 'roll_no', 'staff_id'].includes(fieldName) && this.verifiedValues[fieldName] !== normalized) {
                                delete this.verifiedValues[fieldName];
                            }

                            if (fieldName === 'role') {
                                this.refreshVisibility();
                                return;
                            }

                            this.validateField(fieldName, {
                                runUniqueCheck: false,
                                syncFields: inputEvent !== 'input',
                            });
                        });

                        element.addEventListener('blur', () => {
                            if (config?.type !== 'radio') {
                                const normalized = this.normalizeValue(fieldName, this.getRawValue(fieldName));
                                if (element.value !== normalized) {
                                    element.value = normalized;
                                }
                            }

                            this.validateField(fieldName, { runUniqueCheck: true });
                        });
                    });
                });
            }
        }

        const userToastIcons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-circle-xmark',
            warning: 'fas fa-triangle-exclamation',
            info: 'fas fa-circle-info',
        };

        function escapeUserToastHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function dismissUserToast(toast) {
            if (!toast) {
                return;
            }

            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 180);
        }

        function announceUserMessage(message) {
            const liveRegion = document.getElementById('userLiveRegion');

            if (liveRegion) {
                liveRegion.textContent = message;
            }
        }

        function showUserToast(message, type = 'info') {
            const container = document.getElementById('userToastContainer');

            if (!container) {
                return;
            }

            container.querySelectorAll('.student-toast').forEach((existingToast) => existingToast.remove());

            const normalizedType = ['success', 'error', 'warning', 'info'].includes(type)
                ? type
                : 'info';
            const payload = typeof message === 'object' && message !== null
                ? message
                : {
                    title: normalizedType === 'error'
                        ? 'Action Failed'
                        : normalizedType === 'warning'
                            ? 'Check Required Fields'
                            : normalizedType === 'success'
                                ? 'Success'
                                : 'Notice',
                    message: String(message || ''),
                };

            const toast = document.createElement('div');
            toast.className = `student-toast ${normalizedType}`;
            toast.innerHTML = `
                <div class="student-toast-icon" aria-hidden="true">
                    <i class="${escapeUserToastHtml(payload.icon || userToastIcons[normalizedType] || userToastIcons.info)}"></i>
                </div>
                <div class="student-toast-copy">
                    <div class="student-toast-title">${escapeUserToastHtml(payload.title || 'Notice')}</div>
                    <div class="student-toast-message">${escapeUserToastHtml(payload.message || '')}</div>
                    ${payload.detail ? `<div class="student-toast-detail">${escapeUserToastHtml(payload.detail)}</div>` : ''}
                </div>
                <button type="button" class="student-toast-close" aria-label="Dismiss notification">
                    <i class="fas fa-times"></i>
                </button>
                <span class="student-toast-progress" aria-hidden="true"></span>
            `;

            container.appendChild(toast);
            toast.querySelector('.student-toast-close')?.addEventListener('click', () => dismissUserToast(toast));
            announceUserMessage(`${payload.title || 'Notice'}. ${payload.message || ''}`.trim());
            window.setTimeout(() => dismissUserToast(toast), 4200);
        }

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
                const searchParams = new URLSearchParams(window.location.search);
                this.perPage = this.normalizePerPage(searchParams.get('per_page'));
                this.currentPage = Number(new URLSearchParams(window.location.search).get('page')) || 1;
                this.currentStatusFilter = searchParams.get('status') || 'all';
                this.currentRoleFilter = searchParams.get('role') || 'all';
                this.currentSortFilter = searchParams.get('sort') || 'recently-added';
                this.currentSearch = searchParams.get('search') || '';
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
                this.initValidators();

                // Initialize search functionality (LIVE SEARCH - DEBOUNCED)
                this.initSearch();

                // Initialize filter tabs (AJAX-BASED)
                this.initFilters();

                this.syncControlsFromState();

                // Initialize table action buttons
                this.initTableActions();

                // Initialize keyboard shortcuts
                this.initKeyboardShortcuts();

                // Initialize pagination click handlers
                this.initPagination();

                if (this.currentSearch || this.currentStatusFilter !== 'all' || this.currentRoleFilter !== 'all') {
                    this.refreshStats();
                }
            }

            initValidators() {
                this.addUserValidator = new LiveUserFormValidator({
                    formId: 'addUserForm',
                    submitButtonId: 'submitAddUser',
                    createMode: true,
                    fields: {
                        name: { id: 'addName', errorId: 'addNameError' },
                        email: { id: 'addEmail', errorId: 'addEmailError' },
                        role: { id: 'addRoleSelect', errorId: 'addRoleError' },
                        phone: { id: 'addPhone', errorId: 'addPhoneError' },
                        gender: { id: 'addGender', errorId: 'addGenderError' },
                        department_id: { id: 'addDepartmentSelect', errorId: 'addDepartmentError', roles: ['student', 'staff'] },
                        staff_id: { id: 'addStaffId', errorId: 'addStaffIdError', roles: ['staff'] },
                        designation: { id: 'addDesignation', errorId: 'addDesignationError', roles: ['staff'] },
                        join_date: { id: 'addJoinDate', errorId: 'addJoinDateError', roles: ['staff'] },
                        roll_no: { id: 'addRollNo', errorId: 'addRollNoError', roles: ['student'] },
                        batch: { id: 'addBatch', errorId: 'addBatchError', roles: ['student'] },
                        semester: { id: 'addSemester', errorId: 'addSemesterError', roles: ['student'] },
                        address: { id: 'addAddress', errorId: 'addAddressError' },
                        status: { name: 'status', type: 'radio', groupId: 'addStatusGroup', errorId: 'addStatusError' },
                    },
                });

                this.editUserValidator = new LiveUserFormValidator({
                    formId: 'editUserForm',
                    submitButtonId: 'submitEditUser',
                    createMode: false,
                    getUserId: () => this.currentUserId,
                    fields: {
                        name: { id: 'editFullName', errorId: 'editNameError' },
                        email: { id: 'editEmail', errorId: 'editEmailError' },
                        role: { id: 'editRole', errorId: 'editRoleError' },
                        phone: { id: 'editPhone', errorId: 'editPhoneError' },
                        gender: { id: 'editGender', errorId: 'editGenderError' },
                        department_id: { id: 'editDepartment', errorId: 'editDepartmentError', roles: ['student', 'staff'] },
                        staff_id: { id: 'editStaffId', errorId: 'editStaffIdError', roles: ['staff'] },
                        designation: { id: 'editDesignation', errorId: 'editDesignationError', roles: ['staff'] },
                        join_date: { id: 'editJoinDate', errorId: 'editJoinDateError', roles: ['staff'] },
                        roll_no: { id: 'editRollNo', errorId: 'editRollNoError', roles: ['student'] },
                        batch: { id: 'editBatch', errorId: 'editBatchError', roles: ['student'] },
                        semester: { id: 'editSemester', errorId: 'editSemesterError', roles: ['student'] },
                        address: { id: 'editAddress', errorId: 'editAddressError' },
                    },
                });
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

                const entriesSelect = document.getElementById('usersEntriesSelect');
                if (entriesSelect) {
                    entriesSelect.addEventListener('change', (e) => {
                        this.perPage = this.normalizePerPage(e.target.value);
                        this.currentPage = 1;
                        this.fetchUsersData(1);
                    });
                }

                const resetFiltersBtn = document.getElementById('resetFiltersBtn');
                if (resetFiltersBtn) {
                    resetFiltersBtn.addEventListener('click', () => {
                        this.resetFilters();
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
                    '<tr><td colspan="6" style="text-align: center; padding: 40px;"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

                const params = new URLSearchParams({
                    search: this.currentSearch,
                    status: this.currentStatusFilter,
                    role: this.currentRoleFilter,
                    sort: this.currentSortFilter,
                    page: page,
                    per_page: this.perPage
                });

                fetch(`/admin/users/data?${params.toString()}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf()
                        }
                    })
                    .then(res => this.parseJsonResponse(res, 'Unable to load users right now.'))
                    .then(data => {
                        console.log('Users data received:', data);

                        if (data.success) {
                            if (Number(page) > Number(data.last_page || 1)) {
                                this.fetchUsersData(data.last_page || 1);
                                return;
                            }

                            this.currentPage = Number(data.current_page || page) || 1;

                            // Update table rows
                            tableBody.innerHTML = data.tableRows;

                            // Update pagination
                            const paginationContainer = document.getElementById('paginationContainer');
                            if (paginationContainer) {
                                paginationContainer.innerHTML = data.pagination;
                            }

                            // Update stats based on filtered results
                            if (data.stats) {
                                this.updateStats(data.stats);
                            }

                            this.syncUrlState();

                            // Re-initialize table actions for new rows
                            this.initTableActions();

                            // Re-initialize pagination for new links
                            this.initPagination();
                        } else {
                            tableBody.innerHTML =
                                `<tr><td colspan="6" style="text-align: center; padding: 40px; color: #ef4444;">Error: ${data.message}</td></tr>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching users:', error);
                        tableBody.innerHTML =
                            '<tr><td colspan="6" style="text-align: center; padding: 40px; color: #ef4444;">Error loading users. Please try again.</td></tr>';
                        this.showNotification('Error fetching users: ' + error.message, 'error');
                    });
            }

            /**
             * Update stats cards with data from backend
             * @param {Object} stats - Statistics data including filtered values
             */
            updateStats(stats) {
                console.log('Updating stats:', stats);

                // When filters are applied, use filtered values
                // When no filters, use global values
                const hasFilters = this.currentSearch || this.currentStatusFilter !== 'all' || this.currentRoleFilter !== 'all';

                // Update total users count (show filtered count when filters applied, else global)
                const totalCount = hasFilters ? (stats.filteredTotal || 0) : (stats.totalUsers || 0);
                document.getElementById('totalUsersCount').textContent = totalCount;

                // Update active users count
                const activeCount = hasFilters ? (stats.filteredActive || 0) : (stats.activeUsers || 0);
                document.getElementById('activeUsersCount').textContent = activeCount;

                // Update inactive users count
                const inactiveCount = hasFilters ? (stats.filteredInactive || 0) : (stats.inactiveUsers || 0);
                document.getElementById('inactiveUsersCount').textContent = inactiveCount;

                // Update role counts (always use filtered counts so they match the filtered results)
                document.getElementById('roleCountAdmin').textContent = stats.roleCounts?.admin || 0;
                document.getElementById('roleCountStaff').textContent = stats.roleCounts?.staff || 0;
                document.getElementById('roleCountStudent').textContent = stats.roleCounts?.student || 0;
            }

            /**
             * Refresh stats from server without fetching user list
             * Used for real-time stat updates after delete/status change
             * Passes current filters so stats can show both global and filtered counts
             */
            refreshStats() {
                console.log('Refreshing stats with current filters...');

                // Fetch stats with current filters applied
                const params = new URLSearchParams({
                    search: this.currentSearch,
                    status: this.currentStatusFilter,
                    role: this.currentRoleFilter
                });

                fetch(`/admin/users/stats?${params}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf()
                        }
                    })
                    .then(res => this.parseJsonResponse(res, 'Unable to refresh user statistics.'))
                    .then(data => {
                        console.log('Stats received:', data);
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
                    page: this.currentPage,
                    per_page: this.perPage
                });

                fetch(`/admin/users/data?${params.toString()}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf()
                        }
                    })
                    .then(res => this.parseJsonResponse(res, 'Unable to refresh the user list.'))
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

            syncControlsFromState() {
                const searchInput = document.getElementById('searchInput');
                const roleFilter = document.getElementById('roleFilter');
                const statusFilter = document.getElementById('statusFilter');
                const sortFilter = document.getElementById('sortFilter');
                const entriesSelect = document.getElementById('usersEntriesSelect');

                if (searchInput) searchInput.value = this.currentSearch;
                if (roleFilter) roleFilter.value = this.currentRoleFilter;
                if (statusFilter) statusFilter.value = this.currentStatusFilter;
                if (sortFilter) sortFilter.value = this.currentSortFilter;
                if (entriesSelect) entriesSelect.value = String(this.perPage);
            }

            syncUrlState() {
                const params = new URLSearchParams();

                if (this.currentSearch) params.set('search', this.currentSearch);
                if (this.currentStatusFilter !== 'all') params.set('status', this.currentStatusFilter);
                if (this.currentRoleFilter !== 'all') params.set('role', this.currentRoleFilter);
                if (this.currentSortFilter !== 'recently-added') params.set('sort', this.currentSortFilter);
                if (this.currentPage > 1) params.set('page', String(this.currentPage));
                if (this.perPage !== 10) params.set('per_page', String(this.perPage));

                const nextUrl = params.toString()
                    ? `${window.location.pathname}?${params.toString()}`
                    : window.location.pathname;

                window.history.replaceState({ url: nextUrl }, '', nextUrl);
            }

            resetFilters() {
                this.currentSearch = '';
                this.currentRoleFilter = 'all';
                this.currentStatusFilter = 'all';
                this.currentSortFilter = 'recently-added';
                this.currentPage = 1;
                this.syncControlsFromState();
                this.fetchUsersData(1);
            }

            normalizePerPage(value) {
                const allowedValues = [10, 20, 50, 100];
                const perPage = Number(value);
                return allowedValues.includes(perPage) ? perPage : 10;
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
                        this.addUserValidator?.refreshVisibility();
                        this.addUserValidator?.clearAllVisualStates();
                    }
                });

                document.getElementById('submitAddUser').addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('Submit add user clicked');
                    this.submitAddUser();
                });

                document.getElementById('addRoleSelect').addEventListener('change', (e) => {
                    this.toggleAddUserFields(e.target.value);
                    this.addUserValidator?.refreshVisibility();
                });

                const addStatusActiveRadio = document.getElementById('addStatusActiveRadio');
                if (addStatusActiveRadio) {
                    addStatusActiveRadio.addEventListener('change', (event) => {
                        const role = document.getElementById('addRoleSelect')?.value || '';

                        if (!this.isInvitationManagedRole(role) || !(event.target instanceof HTMLInputElement) || !event.target.checked) {
                            return;
                        }

                        const inactiveRadio = document.querySelector('#addStatusRadio input[name="status"][value="inactive"]');
                        if (inactiveRadio instanceof HTMLInputElement) {
                            inactiveRadio.checked = true;
                        }

                        event.target.checked = false;
                        this.showManagedStatusToast(role);
                    });
                }

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
                    this.editUserValidator?.refreshVisibility();
                });

                document.getElementById('closeEditUserModal').addEventListener('click', () => {
                    this.closeModal('editUserModal');
                });

                document.getElementById('cancelEditUser').addEventListener('click', () => {
                    this.closeModal('editUserModal');
                });

                // View User Modal
                if (document.getElementById('closeViewUserModal')) {
                    document.getElementById('closeViewUserModal').addEventListener('click', () => {
                        this.closeModal('viewUserModal');
                    });
                }

                if (document.getElementById('closeViewUserFooter')) {
                    document.getElementById('closeViewUserFooter').addEventListener('click', () => {
                        this.closeModal('viewUserModal');
                    });
                }

                // Delete User Modal
                document.getElementById('confirmDeleteUser').addEventListener('click', () => {
                    console.log('Confirm delete clicked');
                    this.confirmDeleteUser();
                });

                document.getElementById('cancelDeleteUser').addEventListener('click', () => {
                    this.closeModal('deleteUserModal');
                });

                // Reset Password Modal
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
                document.querySelectorAll('.action-btn.view').forEach((btn) => {
                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);
                });

                document.querySelectorAll('.action-btn.edit').forEach((btn) => {
                    const newBtn = btn.cloneNode(true);
                    btn.parentNode.replaceChild(newBtn, btn);
                });

                // View buttons
                document.querySelectorAll('.action-btn.view').forEach((btn) => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const row = e.target.closest('tr');
                        if (!row) {
                            console.error('Could not find row');
                            return;
                        }
                        this.currentUserId = row.dataset.userId;
                        this.currentUserRow = row;
                        this.openViewModal(row);
                    });
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
                            case 'resetPasswordModal':
                                e.preventDefault();
                                this.confirmResetPassword();
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
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';

                    if (modalId === 'addUserModal') {
                        this.addUserValidator?.clearAllVisualStates();
                    }

                    if (modalId === 'editUserModal') {
                        this.editUserValidator?.clearAllVisualStates();
                    }

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
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                }

                this.currentModal = null;

                // Reset forms and clear errors
                if (modalId === 'addUserModal') {
                    this.addUserValidator?.resetForm();
                    // Reset field visibility to default (student)
                    this.toggleAddUserFields('student');
                }

                if (modalId === 'editUserModal') {
                    this.editUserValidator?.resetForm();
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

            getRowUserMeta(row = this.currentUserRow) {
                if (!row) {
                    return {
                        name: 'Unknown User',
                        email: 'No email available',
                        departmentName: '',
                        studentRollNo: '',
                        staffDesignation: '',
                        initial: 'U',
                        profilePhotoUrl: '',
                        isCurrentUser: false,
                    };
                }

                const name = row.dataset.name
                    || row.querySelector('.user-name span')?.textContent?.trim()
                    || 'Unknown User';
                const email = row.dataset.email
                    || row.querySelector('td:first-child .text-muted')?.textContent?.trim()
                    || 'No email available';

                return {
                    name,
                    email,
                    phone: row.dataset.phone || '',
                    gender: row.dataset.gender || '',
                    departmentName: row.dataset.departmentName || '',
                    studentId: row.dataset.studentId || row.dataset.studentRollNo || '',
                    studentRollNo: row.dataset.studentRollNo || '',
                    staffId: row.dataset.staffId || '',
                    staffDesignation: row.dataset.staffDesignation || '',
                    hasPassword: row.dataset.hasPassword === '1',
                    profilePhotoUrl: row.dataset.profilePhotoUrl || '',
                    initial: (name.charAt(0) || 'U').toUpperCase(),
                    isCurrentUser: row.dataset.isCurrentUser === '1',
                    role: row.dataset.role || '',
                    status: row.dataset.status || '',
                    lastLogin: row.dataset.lastLogin || 'Never',
                };
            }

            fetchUserDetails(userId) {
                return fetch(`/admin/users/${userId}/details`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(res => this.parseJsonResponse(res, 'Unable to load user details.'))
                    .then(data => {
                        if (!data.success || !data.user) {
                            throw new Error(data.message || 'Invalid response format');
                        }

                        return data.user;
                    });
            }

            getUserRoleMeta(role) {
                switch (role) {
                    case 'admin':
                        return {
                            label: 'Admin',
                            icon: 'fas fa-shield-alt',
                            className: 'role-admin',
                        };
                    case 'staff':
                        return {
                            label: 'Staff',
                            icon: 'fas fa-user-tie',
                            className: 'role-staff',
                        };
                    default:
                        return {
                            label: 'Student',
                            icon: 'fas fa-graduation-cap',
                            className: 'role-student',
                        };
                }
            }

            getUserStatusMeta(status) {
                return status === 'active'
                    ? {
                        label: 'Active',
                        icon: 'fas fa-check-circle',
                        className: 'status-active',
                    }
                    : {
                        label: 'Inactive',
                        icon: 'fas fa-times-circle',
                        className: 'status-inactive',
                    };
            }

            isInvitationManagedRole(role) {
                return role === 'staff' || role === 'student';
            }

            getManagedStatusToast(role) {
                const roleMeta = this.getUserRoleMeta(role);

                return {
                    type: 'warning',
                    payload: {
                        title: 'Status Set to Inactive',
                        message: `${roleMeta.label} accounts cannot start as active.`,
                        detail: 'They must complete registration from their invitation email before activation is allowed.',
                        icon: 'fas fa-user-clock',
                    },
                };
            }

            showManagedStatusToast(role) {
                const toast = this.getManagedStatusToast(role);
                this.showNotification(toast.payload, toast.type);
            }

            getRoleConfirmDetail(userMeta) {
                const role = userMeta.role || 'student';

                if (role === 'student') {
                    return userMeta.studentId
                        ? `Student ID ${userMeta.studentId}`
                        : userMeta.departmentName
                            ? `Student • ${userMeta.departmentName}`
                            : 'Student account';
                }

                if (role === 'staff') {
                    return userMeta.staffId
                        ? `Staff ID ${userMeta.staffId}`
                        : userMeta.staffDesignation
                            ? `Staff • ${userMeta.staffDesignation}`
                        : userMeta.departmentName
                            ? `Staff • ${userMeta.departmentName}`
                            : 'Staff account';
                }

                return 'Admin account';
            }

            getDeleteModalContent(userMeta) {
                const roleLabel = this.getUserRoleMeta(userMeta.role).label;

                return {
                    title: `Delete ${userMeta.name}?`,
                    message: `This will permanently remove ${userMeta.name} from the system and cannot be undone.`,
                    detail: this.getRoleConfirmDetail(userMeta),
                    confirmLabel: `Delete ${roleLabel}`,
                };
            }

            getResetPasswordModalContent(userMeta) {
                const roleLabel = this.getUserRoleMeta(userMeta.role).label;
                const email = userMeta.email && userMeta.email !== 'No email available'
                    ? userMeta.email
                    : 'this account';

                if ((userMeta.role === 'staff' || userMeta.role === 'student') && !userMeta.hasPassword) {
                    return {
                        title: `Registration pending for ${userMeta.name}`,
                        message: `${userMeta.name} has not completed self-registration yet, so password reset is not available.`,
                        detail: this.getRoleConfirmDetail(userMeta),
                        confirmLabel: 'Registration Pending',
                    };
                }

                return {
                    title: `Reset password for ${userMeta.name}?`,
                    message: `A temporary password will be sent to ${email} and must be changed after the next sign in.`,
                    detail: this.getRoleConfirmDetail(userMeta),
                    confirmLabel: `Reset ${roleLabel} Password`,
                };
            }

            showUserActionToast(action, data = {}) {
                const name = data.name || 'the user';
                const email = data.email || '';
                const roleMeta = data.role ? this.getUserRoleMeta(data.role) : null;
                const statusMeta = data.status ? this.getUserStatusMeta(data.status) : null;
                let type = 'success';
                let payload = {
                    title: 'User Updated',
                    message: `Saved changes for ${name}.`,
                };

                switch (action) {
                    case 'create':
                        if (this.isInvitationManagedRole(data.role)) {
                            payload = {
                                title: `${roleMeta?.label || 'User'} Invitation Created`,
                                message: `${name} was added as inactive until registration is completed.`,
                                detail: 'Active is unavailable for invited staff and student accounts until self-registration is finished.',
                                icon: 'fas fa-user-clock',
                            };
                            type = 'info';
                        } else {
                            payload = {
                                title: 'User Added',
                                message: `Created a new account for ${name}.`,
                                detail: email || roleMeta?.label || '',
                                icon: 'fas fa-user-plus',
                            };
                            type = 'success';
                        }
                        break;
                    case 'update':
                        payload = {
                            title: 'User Updated',
                            message: `Saved changes for ${name}.`,
                            detail: email || roleMeta?.label || '',
                            icon: 'fas fa-user-pen',
                        };
                        type = 'info';
                        break;
                    case 'delete':
                        payload = {
                            title: 'User Deleted',
                            message: `Removed ${name} from user management.`,
                            detail: email || '',
                            icon: 'fas fa-trash-alt',
                        };
                        type = 'error';
                        break;
                    case 'reset-password':
                        payload = {
                            title: 'Password Reset Sent',
                            message: `Sent a temporary password to ${name}.`,
                            detail: email || '',
                            icon: 'fas fa-key',
                        };
                        type = 'success';
                        break;
                    case 'status': {
                        const isActive = statusMeta?.label === 'Active';
                        const subjectLabel = roleMeta?.label || 'User';
                        payload = {
                            title: isActive ? `${subjectLabel} Activated` : `${subjectLabel} Deactivated`,
                            message: `${isActive ? 'Activated' : 'Deactivated'} ${name}.`,
                            detail: email || '',
                            icon: isActive ? 'fas fa-user-check' : 'fas fa-user-slash',
                        };
                        type = isActive ? 'success' : 'warning';
                        break;
                    }
                    default:
                        break;
                }

                this.showNotification(payload, type);
            }

            showUserValidationToast(message = 'Review the highlighted field before saving.') {
                this.showNotification({
                    title: 'Check Required Fields',
                    message,
                    detail: 'Only one field is highlighted at a time.',
                    icon: 'fas fa-circle-exclamation',
                }, 'warning');
            }

            formatSemesterLabel(semester) {
                if (!semester) {
                    return '';
                }

                const numericSemester = Number(semester);
                if (!Number.isInteger(numericSemester) || numericSemester < 1 || numericSemester > 8) {
                    return '';
                }

                const suffixMap = {
                    1: 'st',
                    2: 'nd',
                    3: 'rd',
                };
                const suffix = suffixMap[numericSemester] || 'th';
                return `${numericSemester}${suffix} Semester`;
            }

            setUserDetailItem(itemId, valueId, value, { placeholder = 'Not provided', hideWhenEmpty = false } = {}) {
                const item = document.getElementById(itemId);
                const valueElement = document.getElementById(valueId);

                if (!valueElement) {
                    return;
                }

                const hasValue = Boolean(value);
                valueElement.textContent = hasValue ? value : placeholder;

                if (item) {
                    item.hidden = hideWhenEmpty && !hasValue;
                }
            }

            populateViewModal(user) {
                const roleMeta = this.getUserRoleMeta(user.role);
                const statusMeta = this.getUserStatusMeta(user.status);
                const avatarImage = document.getElementById('viewUserAvatarImage');
                const avatarFallback = document.getElementById('viewUserAvatarFallback');
                const roleChip = document.getElementById('viewUserRoleChip');
                const statusChip = document.getElementById('viewUserStatusChip');
                const selfChip = document.getElementById('viewUserSelfChip');
                const departmentName = user.department_name
                    || user.student?.department_name
                    || user.staff?.department_name
                    || '';

                document.getElementById('viewUserName').textContent = user.name || 'Unknown User';
                document.getElementById('viewUserEmail').textContent = user.email || 'No email available';

                if (avatarImage && avatarFallback) {
                    const avatarLetter = (user.initial || user.name?.trim()?.charAt(0) || 'U').toUpperCase();
                    avatarFallback.textContent = avatarLetter;
                    avatarFallback.hidden = false;
                    avatarImage.hidden = true;
                    avatarImage.onload = null;
                    avatarImage.onerror = null;
                    avatarImage.removeAttribute('src');

                    if (user.profile_photo_url) {
                        avatarImage.onload = () => {
                            avatarImage.hidden = false;
                            avatarFallback.hidden = true;
                        };
                        avatarImage.onerror = () => {
                            avatarImage.hidden = true;
                            avatarImage.removeAttribute('src');
                            avatarImage.onload = null;
                            avatarImage.hidden = true;
                            avatarFallback.hidden = false;
                        };
                        avatarImage.src = user.profile_photo_url;
                    }
                }

                if (selfChip) {
                    selfChip.hidden = !user.is_current_user;
                }

                if (roleChip) {
                    roleChip.className = `user-profile-chip ${roleMeta.className}`;
                    roleChip.innerHTML = `<i class="${roleMeta.icon}"></i> ${roleMeta.label}`;
                }

                if (statusChip) {
                    statusChip.className = `user-profile-chip ${statusMeta.className}`;
                    statusChip.innerHTML = `<i class="${statusMeta.icon}"></i> ${statusMeta.label}`;
                }

                this.setUserDetailItem(null, 'viewUserPhone', user.phone, { placeholder: 'Not provided' });
                this.setUserDetailItem(null, 'viewUserGender', user.gender ? `${user.gender.charAt(0).toUpperCase()}${user.gender.slice(1)}` : null, { placeholder: 'Not provided' });
                this.setUserDetailItem(null, 'viewUserDepartment', departmentName, { placeholder: 'Not assigned' });
                this.setUserDetailItem('viewUserStaffIdItem', 'viewUserStaffId', user.staff?.staff_id, { hideWhenEmpty: true });
                this.setUserDetailItem('viewUserDesignationItem', 'viewUserDesignation', user.staff?.designation, { hideWhenEmpty: true });
                this.setUserDetailItem('viewUserJoinDateItem', 'viewUserJoinDate', user.staff?.join_date_label || user.staff?.join_date, { hideWhenEmpty: true });
                this.setUserDetailItem('viewUserRollNoItem', 'viewUserRollNo', user.student?.student_id || user.student?.roll_no, { hideWhenEmpty: true });
                this.setUserDetailItem('viewUserBatchItem', 'viewUserBatch', user.student?.batch, { hideWhenEmpty: true });
                this.setUserDetailItem('viewUserSemesterItem', 'viewUserSemester', this.formatSemesterLabel(user.student?.semester), { hideWhenEmpty: true });
                this.setUserDetailItem(null, 'viewUserLastLogin', user.last_login_label || 'Never', { placeholder: 'Never' });
                this.setUserDetailItem(null, 'viewUserCreatedAt', user.created_at_label || 'Unknown', { placeholder: 'Unknown' });
                this.setUserDetailItem(null, 'viewUserAddress', user.address, { placeholder: 'Not provided' });
            }

            openViewModal(row) {
                const userId = row.dataset.userId;
                this.currentUserId = userId;

                this.fetchUserDetails(userId)
                    .then(user => {
                        this.populateViewModal(user);
                        this.openModal('viewUserModal');
                    })
                    .catch(error => {
                        console.error('Error fetching user details:', error);
                        this.showNotification('Error loading user details: ' + error.message, 'error');
                    });
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
                this.fetchUserDetails(userId)
                    .then(user => {
                        console.log('User details:', user);

                        // Populate form with user data
                        document.getElementById('editFullName').value = user.name || '';
                        document.getElementById('editEmail').value = user.email || '';
                        document.getElementById('editPhone').value = user.phone || '';
                        document.getElementById('editGender').value = user.gender || '';
                        document.getElementById('editAddress').value = user.address || '';

                        // Set role
                        const role = user.role;
                        document.getElementById('editRole').value = role;

                        // Show/hide student-specific fields based on role
                        this.toggleStudentFields(role);

                        document.getElementById('editDepartment').value = '';
                        document.getElementById('editStaffId').value = '';
                        document.getElementById('editDesignation').value = '';
                        document.getElementById('editJoinDate').value = '';
                        document.getElementById('editRollNo').value = '';
                        document.getElementById('editBatch').value = '';
                        document.getElementById('editSemester').value = '';

                        // Populate role-specific fields
                        if (role === 'student' && user.student) {
                            document.getElementById('editDepartment').value = user.student.department_id || '';
                            document.getElementById('editRollNo').value = user.student.student_id || user.student.roll_no || '';
                            document.getElementById('editBatch').value = user.student.batch || '';
                            document.getElementById('editSemester').value = user.student.semester || '';
                        } else if (role === 'staff' && user.staff) {
                            document.getElementById('editDepartment').value = user.staff.department_id || '';
                            document.getElementById('editStaffId').value = user.staff.staff_id || '';
                            document.getElementById('editDesignation').value = user.staff.designation || '';
                            document.getElementById('editJoinDate').value = user.staff.join_date || '';
                        } else if (role === 'staff') {
                            document.getElementById('editStaffId').value = '';
                            document.getElementById('editDesignation').value = 'Staff Member';
                            document.getElementById('editJoinDate').value = this.getTodayDate();
                        }

                        this.editUserValidator?.seed({
                            name: user.name || '',
                            email: user.email || '',
                            role: user.role || 'student',
                            phone: user.phone || '',
                            gender: user.gender || '',
                            address: user.address || '',
                            department_id: user.student?.department_id || user.staff?.department_id || '',
                            staff_id: user.staff?.staff_id || '',
                            designation: user.staff?.designation || '',
                            join_date: user.staff?.join_date || '',
                            roll_no: user.student?.student_id || user.student?.roll_no || '',
                            batch: user.student?.batch || '',
                            semester: user.student?.semester || '',
                        });

                        // Open the modal
                        this.openModal('editUserModal');
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
                const staffIdGroup = document.getElementById('editStaffIdGroup');
                const designationGroup = document.getElementById('editStaffDesignationGroup');
                const joinDateGroup = document.getElementById('editStaffJoinDateGroup');
                const rollNoGroup = document.getElementById('editRollNoGroup');
                const batchGroup = document.getElementById('editBatchGroup');
                const semesterGroup = document.getElementById('editSemesterGroup');
                const rollNoLabel = document.getElementById('editRollNoLabel');
                const rollNoInput = document.getElementById('editRollNo');

                if (role === 'student') {
                    deptGroup.style.display = 'block';
                    staffIdGroup.style.display = 'none';
                    designationGroup.style.display = 'none';
                    joinDateGroup.style.display = 'none';
                    rollNoGroup.style.display = 'block';
                    batchGroup.style.display = 'block';
                    semesterGroup.style.display = 'block';
                    // Update Roll Number label for student
                    if (rollNoLabel) rollNoLabel.textContent = 'Student ID';
                    if (rollNoInput) rollNoInput.placeholder = 'STU-000001';
                } else if (role === 'staff') {
                    deptGroup.style.display = 'block';
                    staffIdGroup.style.display = 'block';
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
                    staffIdGroup.style.display = 'none';
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
                const statusRadioGroup = document.getElementById('addStatusRadio');
                const statusRadios = document.querySelectorAll('#addStatusRadio input[name="status"]');
                const shouldLockStatus = this.isInvitationManagedRole(role);
                const wasManagedRole = statusRadioGroup?.dataset.managedRole === 'true';

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
                        rollNoLabel.textContent = 'Student ID';
                        rollNoInput.placeholder = 'STU-000001';
                    } else {
                        rollNoLabel.textContent = 'Student ID';
                        rollNoInput.placeholder = 'STU-000001';
                    }
                }

                if (role === 'staff' && addJoinDate && !addJoinDate.value) {
                    addJoinDate.value = this.getTodayDate();
                }

                if (statusRadioGroup) {
                    statusRadioGroup.dataset.managedRole = shouldLockStatus ? 'true' : 'false';
                }

                statusRadios.forEach((radio) => {
                    radio.disabled = false;

                    if (shouldLockStatus) {
                        radio.checked = radio.value === 'inactive';
                    } else if (wasManagedRole) {
                        radio.checked = radio.value === 'active';
                    }
                });
            }

            getTodayDate() {
                return new Date().toISOString().split('T')[0];
            }

            /**
             * Open delete confirmation modal
             */
            openDeleteModal() {
                const userMeta = this.getRowUserMeta();
                const modalCopy = this.getDeleteModalContent(userMeta);

                document.getElementById('deleteUserModalTitle').textContent = modalCopy.title;
                document.getElementById('deleteUserModalMessage').textContent = modalCopy.message;
                document.getElementById('deleteUserModalDetail').textContent = modalCopy.detail;
                document.getElementById('confirmDeleteUser').textContent = modalCopy.confirmLabel;
                this.openModal('deleteUserModal');
                document.getElementById('confirmDeleteUser')?.focus();
            }

            /**
             * Open reset password confirmation modal
             */
            openResetPasswordModal() {
                const userMeta = this.getRowUserMeta();
                const modalCopy = this.getResetPasswordModalContent(userMeta);
                const submitResetPassword = document.getElementById('submitResetPassword');

                document.getElementById('resetPasswordModalTitle').textContent = modalCopy.title;
                document.getElementById('resetPasswordModalMessage').textContent = modalCopy.message;
                document.getElementById('resetPasswordModalDetail').textContent = modalCopy.detail;
                submitResetPassword.textContent = modalCopy.confirmLabel;
                submitResetPassword.disabled = (userMeta.role === 'staff' || userMeta.role === 'student') && !userMeta.hasPassword;
                this.openModal('resetPasswordModal');
                submitResetPassword?.focus();
            }

            /**
             * Get CSRF token from meta tag
             * @returns {string} CSRF token
             */
            csrf() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.content : '';
            }

            parseJsonResponse(response, fallbackMessage = 'Request failed.') {
                return response
                    .json()
                    .catch(() => ({}))
                    .then((data) => {
                        if (!response.ok) {
                            throw new Error(data?.message || fallbackMessage || `HTTP error! status: ${response.status}`);
                        }

                        return data;
                    });
            }

            /**
             * Show notification message
             * @param {string} message - Notification message
             * @param {string} type - Notification type: 'success', 'error', 'info', 'warning'
             */
            showNotification(message, type = 'info') {
                console.log('Notification:', message, type);
                showUserToast(message, type);
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
                        <td colspan="6" class="text-center py-8 text-gray-500">
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
            async submitAddUser() {
                console.log('Submitting add user form');
                const form = document.getElementById('addUserForm');
                const isValid = await this.addUserValidator?.validateAll({ showFirstErrorOnly: true });
                if (!isValid) {
                    this.showUserValidationToast('Review the highlighted field before saving.');
                    return;
                }

                const formData = this.addUserValidator.toFormData();
                this.submitAddUserAjax(form, formData);
            }

            /**
             * Clear all error states in the add user form
             */
            clearAddUserErrors() {
                const errorFields = ['addNameError', 'addEmailError', 'addRoleError', 'addPhoneError', 'addGenderError',
                    'addDepartmentError', 'addStaffIdError', 'addDesignationError', 'addJoinDateError', 'addRollNoError', 'addBatchError', 'addSemesterError', 'addAddressError'];
                const inputFields = ['addName', 'addEmail', 'addRoleSelect', 'addPhone', 'addGender',
                    'addDepartmentSelect', 'addStaffId', 'addDesignation', 'addJoinDate', 'addRollNo', 'addBatch', 'addSemester', 'addAddress'];

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

            bindUserFormErrorClearing(fieldIds) {
                fieldIds.forEach((id) => {
                    const inputEl = document.getElementById(id);
                    if (!inputEl) {
                        return;
                    }

                    const clear = () => this.clearUserFieldError(id);
                    inputEl.addEventListener('input', clear);
                    inputEl.addEventListener('change', clear);
                });
            }

            getUserFieldErrorElementId(fieldId) {
                const fieldMap = {
                    addName: 'addNameError',
                    addEmail: 'addEmailError',
                    addRoleSelect: 'addRoleError',
                    addPhone: 'addPhoneError',
                    addGender: 'addGenderError',
                    addDepartmentSelect: 'addDepartmentError',
                    addStaffId: 'addStaffIdError',
                    addDesignation: 'addDesignationError',
                    addJoinDate: 'addJoinDateError',
                    addRollNo: 'addRollNoError',
                    addBatch: 'addBatchError',
                    addSemester: 'addSemesterError',
                    addAddress: 'addAddressError',
                    editFullName: 'editNameError',
                    editEmail: 'editEmailError',
                    editRole: 'editRoleError',
                    editPhone: 'editPhoneError',
                    editGender: 'editGenderError',
                    editDepartment: 'editDepartmentError',
                    editStaffId: 'editStaffIdError',
                    editDesignation: 'editDesignationError',
                    editJoinDate: 'editJoinDateError',
                    editRollNo: 'editRollNoError',
                    editBatch: 'editBatchError',
                    editSemester: 'editSemesterError',
                    editAddress: 'editAddressError'
                };

                return fieldMap[fieldId] || `${fieldId}Error`;
            }

            clearUserFieldError(fieldId) {
                const inputEl = document.getElementById(fieldId);
                const errorEl = document.getElementById(this.getUserFieldErrorElementId(fieldId));

                if (inputEl) {
                    inputEl.classList.remove('is-invalid');
                }

                if (errorEl) {
                    errorEl.textContent = '';
                    errorEl.classList.remove('visible');
                }
            }

            getAddUserFieldIdFromServerField(fieldName) {
                const fieldMap = {
                    name: 'addName',
                    email: 'addEmail',
                    role: 'addRoleSelect',
                    phone: 'addPhone',
                    gender: 'addGender',
                    department_id: 'addDepartmentSelect',
                    staff_id: 'addStaffId',
                    designation: 'addDesignation',
                    join_date: 'addJoinDate',
                    roll_no: 'addRollNo',
                    batch: 'addBatch',
                    semester: 'addSemester',
                    address: 'addAddress',
                    status: 'addStatusGroup'
                };

                return fieldMap[fieldName] || null;
            }

            getEditUserFieldIdFromServerField(fieldName) {
                const fieldMap = {
                    name: 'editFullName',
                    email: 'editEmail',
                    role: 'editRole',
                    phone: 'editPhone',
                    gender: 'editGender',
                    department_id: 'editDepartment',
                    staff_id: 'editStaffId',
                    designation: 'editDesignation',
                    join_date: 'editJoinDate',
                    roll_no: 'editRollNo',
                    batch: 'editBatch',
                    semester: 'editSemester',
                    address: 'editAddress'
                };

                return fieldMap[fieldName] || null;
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
                    // 3. Role validation
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
                    // 4. Role-specific validations
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
                                if (!rollNo) return 'Please enter the student ID';
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
                        {
                            fieldId: 'addStaffId',
                            field: 'staff_id',
                            check: () => {
                                const staffId = formData.get('staff_id')?.trim();
                                if (!staffId) return 'Please enter the staff ID';
                                if (staffId.length < 3) return 'Staff ID must be at least 3 characters';
                                if (!/^[A-Za-z0-9-]+$/.test(staffId)) return 'Staff ID can only contain letters, numbers, and hyphens';
                                return null;
                            }
                        },
                        // Staff: Designation
                        {
                            fieldId: 'addDesignation',
                            field: 'designation',
                            check: () => {
                                const designation = formData.get('designation')?.trim();
                                if (!designation) return null;
                                if (designation.length < 2) return 'Staff designation must be at least 2 characters';
                                return null;
                            }
                        },
                        // Staff: Join Date
                        {
                            fieldId: 'addJoinDate',
                            field: 'join_date',
                            check: () => {
                                const joinDate = formData.get('join_date');
                                if (!joinDate) return null;
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
                const errorEl = document.getElementById(this.getUserFieldErrorElementId(fieldId));

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
                this.addUserValidator?.setSubmitting(true);
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
                                    const validationError = new Error(data.message || 'Validation error occurred');
                                    validationError.isValidationError = true;
                                    validationError.errors = data.errors;
                                    throw validationError;
                                }

                                throw new Error(data.message || 'Validation error occurred');
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showUserActionToast('create', {
                                name: data.user?.name,
                                email: data.user?.email,
                                role: data.user?.role,
                                status: data.user?.status,
                            });
                            this.closeModal('addUserModal');

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
                        if (error.isValidationError && error.errors) {
                            this.addUserValidator?.applyServerErrors(error.errors, { showFirstErrorOnly: true });
                            this.showUserValidationToast('Please correct the highlighted field and try again.');
                            return;
                        }
                        this.showNotification(error.message || 'Error adding user', 'error');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        this.addUserValidator?.setSubmitting(false);
                        submitBtn.disabled = false;
                    });
            }

            /**
             * Submit edit user form with one-error-at-a-time validation
             */
            async submitEditUser() {
                console.log('Submitting edit user form for user:', this.currentUserId);
                const isValid = await this.editUserValidator?.validateAll({ showFirstErrorOnly: true });
                if (!isValid) {
                    this.showUserValidationToast('Review the highlighted field before saving.');
                    return;
                }

                const formData = this.editUserValidator.toFormData();
                this.submitEditUserAjax(formData);
            }

            /**
             * Clear all error states in the edit user form
             */
            clearEditUserErrors() {
                const errorFields = ['editNameError', 'editEmailError', 'editRoleError', 'editPhoneError', 'editGenderError',
                    'editDepartmentError', 'editStaffIdError', 'editDesignationError', 'editJoinDateError', 'editRollNoError', 'editBatchError', 'editSemesterError', 'editAddressError'];
                const inputFields = ['editFullName', 'editEmail', 'editRole', 'editPhone', 'editGender',
                    'editDepartment', 'editStaffId', 'editDesignation', 'editJoinDate', 'editRollNo', 'editBatch', 'editSemester', 'editAddress'];

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
                                if (!rollNo) return 'Please enter the student ID';
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
                        {
                            fieldId: 'editStaffId',
                            check: () => {
                                const staffId = formData.get('staff_id')?.trim();
                                if (!staffId) return 'Please enter the staff ID';
                                if (staffId.length < 3) return 'Staff ID must be at least 3 characters';
                                if (!/^[A-Za-z0-9-]+$/.test(staffId)) return 'Staff ID can only contain letters, numbers, and hyphens';
                                return null;
                            }
                        },
                        // Staff: Designation
                        {
                            fieldId: 'editDesignation',
                            check: () => {
                                const designation = formData.get('designation')?.trim();
                                if (!designation) return null;
                                if (designation.length < 2) return 'Staff designation must be at least 2 characters';
                                return null;
                            }
                        },
                        // Staff: Join Date
                        {
                            fieldId: 'editJoinDate',
                            check: () => {
                                const joinDate = formData.get('join_date');
                                if (!joinDate) return null;
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
                const errorEl = document.getElementById(this.getUserFieldErrorElementId(fieldId));

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
                this.editUserValidator?.setSubmitting(true);
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
                                    const validationError = new Error(data.message || 'Validation error occurred');
                                    validationError.isValidationError = true;
                                    validationError.errors = data.errors;
                                    throw validationError;
                                }

                                throw new Error(data.message || 'Validation error occurred');
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showUserActionToast('update', {
                                name: data.user?.name,
                                email: data.user?.email,
                                role: data.user?.role,
                            });
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
                        if (error.isValidationError && error.errors) {
                            this.editUserValidator?.applyServerErrors(error.errors, { showFirstErrorOnly: true });
                            this.showUserValidationToast('Please correct the highlighted field and try again.');
                            return;
                        }
                        this.showNotification(error.message || 'Error updating user', 'error');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        this.editUserValidator?.setSubmitting(false);
                        submitBtn.disabled = false;
                    });
            }

            /**
             * Confirm and execute user deletion
             */
            confirmDeleteUser() {
                console.log('Confirming delete for user:', this.currentUserId);
                const userMeta = this.getRowUserMeta();

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
                        return this.parseJsonResponse(res, 'Unable to delete this user.');
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showUserActionToast('delete', userMeta);
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
                const userMeta = this.getRowUserMeta();

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
                        return this.parseJsonResponse(res, 'Unable to send a reset email for this user.');
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showUserActionToast('reset-password', userMeta);
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
                        return this.parseJsonResponse(res, 'Unable to update the user status.');
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showUserActionToast('status', {
                                ...this.getRowUserMeta(row),
                                status: data.status,
                            });
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
                        return this.parseJsonResponse(res, 'Unable to update the user status.');
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showUserActionToast('status', {
                                ...this.getRowUserMeta(row),
                                status: data.status,
                            });

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
                        <td colspan="6" class="text-center py-8 text-gray-500">
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

                const userMeta = this.getRowUserMeta();

                // Show confirmation
                const confirmed = confirm(
                    `Reset password for ${userMeta.name} (${userMeta.email})?\n\nA temporary password will be sent to their email address. They must change it upon first login.`
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
                        return this.parseJsonResponse(res, 'Unable to send a reset email for this user.');
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            this.showUserActionToast('reset-password', userMeta);
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
                showUserToast({
                    title: 'Action Failed',
                    message: 'Failed to initialize page functionality. Please refresh the page.',
                }, 'error');
            }
        });
    </script>
@endpush
