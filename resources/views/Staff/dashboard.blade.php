@extends('Staff.layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        .page-header {
            margin-bottom: 8px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 2px;
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

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 640px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .stat-card {
            border-radius: 0.75rem;
            padding: 1.25rem;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        body.light-theme .stat-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        body.dark-theme .stat-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        body.dark-theme .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        body.light-theme .stat-label {
            color: #64748b;
        }

        body.dark-theme .stat-label {
            color: #94a3b8;
        }

        .status-blue {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.dark-theme .status-blue {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .status-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .status-green {
            background-color: #14532d;
            color: #4ade80;
        }

        .status-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .status-red {
            background-color: #7f1d1d;
            color: #f87171;
        }

        .status-yellow {
            background-color: #fef3c7;
            color: #d97706;
        }

        body.dark-theme .status-yellow {
            background-color: #78350f;
            color: #fbbf24;
        }

        .status-purple {
            background-color: #f3e8ff;
            color: #7c3aed;
        }

        body.dark-theme .status-purple {
            background-color: #4c1d95;
            color: #a78bfa;
        }

        .section-card,
        .table-card {
            border-radius: 0.75rem;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        body.light-theme .section-card,
        body.light-theme .table-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .section-card,
        body.dark-theme .table-card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        .section-header,
        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid;
        }

        body.light-theme .section-header,
        body.light-theme .table-card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .section-header,
        body.dark-theme .table-card-header {
            border-color: #334155;
        }

        .section-title,
        .table-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
        }

        body.dark-theme .section-title,
        body.dark-theme .table-card-title {
            color: #e2e8f0;
        }

        .section-count {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            white-space: nowrap;
        }

        .count-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .count-red {
            background-color: #7f1d1d;
            color: #f87171;
        }

        .charts-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 1024px) {
            .charts-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        .tables-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 1024px) {
            .tables-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .chart-container {
            position: relative;
            height: 280px;
            padding: 0.25rem 0;
        }

        .table-card {
            max-height: 380px;
        }

        .table-card-heading {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .table-card-link {
            font-size: 0.75rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .table-card-link:hover {
            text-decoration: underline;
        }

        .table-card-count {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            background-color: #dbeafe;
            color: #1e40af;
            white-space: nowrap;
        }

        body.dark-theme .table-card-count {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .table-list {
            flex: 1;
            overflow-y: auto;
        }

        .table-item {
            padding: 0.875rem 0;
            border-bottom: 1px solid;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        body.light-theme .table-item {
            border-color: #f1f5f9;
        }

        body.dark-theme .table-item {
            border-color: #334155;
        }

        .table-item:last-child {
            border-bottom: none;
        }

        .table-item-top {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .table-item-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: #0f172a;
        }

        body.dark-theme .table-item-title {
            color: #e2e8f0;
        }

        .table-item-subtitle {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .table-item-subtitle {
            color: #94a3b8;
        }

        .table-item-meta {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        body.dark-theme .table-item-meta {
            color: #64748b;
        }

        .table-request-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.25rem;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            text-align: center;
        }

        .table-empty-state {
            min-height: 180px;
        }

        .empty-icon {
            width: 3rem;
            height: 3rem;
            margin-bottom: 0.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.light-theme .empty-icon {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .empty-icon {
            background-color: #14532d;
            color: #4ade80;
        }

        .empty-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #0f172a;
        }

        body.dark-theme .empty-title {
            color: #e2e8f0;
        }

        .empty-subtitle {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .empty-subtitle {
            color: #94a3b8;
        }

        .overdue-list {
            flex: 1;
            overflow-y: auto;
            max-height: 340px;
        }

        .overdue-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            transition: background-color 0.2s ease;
        }

        body.light-theme .overdue-item {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
        }

        body.dark-theme .overdue-item {
            background-color: #7f1d1d;
            border: 1px solid #991b1b;
        }

        .overdue-item:last-child {
            margin-bottom: 0;
        }

        .overdue-info {
            flex: 1;
            min-width: 0;
        }

        .overdue-book {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.125rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body.dark-theme .overdue-book {
            color: #e2e8f0;
        }

        .overdue-student {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .overdue-student {
            color: #cbd5e1;
        }

        .overdue-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
        }

        .overdue-date {
            font-size: 0.75rem;
            font-weight: 500;
            color: #dc2626;
        }

        body.dark-theme .overdue-date {
            color: #f87171;
        }

        .overdue-days {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            background-color: #dc2626;
            color: #ffffff;
        }

        body.dark-theme .overdue-days {
            background-color: #ef4444;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            white-space: nowrap;
        }

        body.light-theme .action-btn {
            background-color: #2563eb;
            color: #ffffff;
        }

        body.dark-theme .action-btn {
            background-color: #1e40af;
            color: #ffffff;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        .action-btn.btn-accept {
            background-color: #16a34a;
            color: #ffffff;
        }

        .action-btn.btn-reject {
            background-color: #f59e0b;
            color: #000000;
        }

        body.dark-theme .action-btn.btn-reject {
            background-color: #b45309;
            color: #ffffff;
        }

        .action-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            padding: 0.5rem 0.75rem;
            color: #ffffff;
            border-radius: 0.375rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            font-weight: 600;
            opacity: 0.95;
        }

        .toast.success {
            background: #16a34a;
        }

        .toast.error {
            background: #ef4444;
        }

        /* ================== */
        /* REQUEST MODAL      */
        /* ================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-dialog {
            background-color: #ffffff;
            border-radius: 1rem;
            width: 90%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        body.dark-theme .modal-dialog {
            background-color: #1e293b;
        }

        .modal-overlay.active .modal-dialog {
            transform: scale(1) translateY(0);
        }

        /* Modal Header */
        .modal-header {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        body.dark-theme .modal-header {
            border-bottom-color: #334155;
        }

        .modal-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .modal-icon.approve {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .modal-icon.approve {
            background-color: #14532d;
            color: #4ade80;
        }

        .modal-icon.reject {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .modal-icon.reject {
            background-color: #7f1d1d;
            color: #f87171;
        }

        .modal-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .modal-title-group {
            flex: 1;
        }

        .modal-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        body.dark-theme .modal-title {
            color: #f1f5f9;
        }

        .modal-subtitle {
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme .modal-subtitle {
            color: #94a3b8;
        }

        .modal-close {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            background-color: transparent;
            color: #64748b;
        }

        .modal-close:hover {
            background-color: #f1f5f9;
        }

        body.dark-theme .modal-close:hover {
            background-color: #334155;
        }

        /* Modal Body */
        .modal-body {
            padding: 1.5rem;
        }

        .request-summary {
            background-color: #f8fafc;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        body.dark-theme .request-summary {
            background-color: #0f172a;
        }

        .request-summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .request-summary-item:not(:last-child) {
            border-bottom: 1px solid #e5e7eb;
        }

        body.dark-theme .request-summary-item:not(:last-child) {
            border-bottom-color: #334155;
        }

        .request-summary-item:last-child {
            padding-bottom: 0;
        }

        .request-summary-item:first-child {
            padding-top: 0;
        }

        .request-summary-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme .request-summary-label {
            color: #94a3b8;
        }

        .request-summary-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
        }

        body.dark-theme .request-summary-value {
            color: #f1f5f9;
        }

        .request-summary-value.book-title {
            max-width: 200px;
            text-align: right;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Warning Box */
        .modal-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .modal-warning.reject {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }

        body.dark-theme .modal-warning.reject {
            background-color: #450a0a;
            border-color: #7f1d1d;
        }

        .modal-warning-icon {
            flex-shrink: 0;
            color: #dc2626;
        }

        body.dark-theme .modal-warning-icon {
            color: #f87171;
        }

        .modal-warning-text {
            font-size: 0.875rem;
            color: #991b1b;
            line-height: 1.5;
        }

        body.dark-theme .modal-warning-text {
            color: #fca5a5;
        }

        /* Form Group */
        .modal-form-group {
            margin-bottom: 1rem;
        }

        .modal-form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        body.dark-theme .modal-form-label {
            color: #f1f5f9;
        }

        .modal-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
            background-color: #ffffff;
            color: #0f172a;
            transition: border-color 0.2s ease;
        }

        body.dark-theme .modal-textarea {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        .modal-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-textarea::placeholder {
            color: #94a3b8;
        }

        .modal-hint {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.375rem;
        }

        body.dark-theme .modal-hint {
            color: #94a3b8;
        }

        /* Modal Footer */
        .modal-footer {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background-color: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }

        body.dark-theme .modal-footer {
            background-color: #0f172a;
            border-top-color: #334155;
        }

        .modal-btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-btn-cancel {
            background-color: #f1f5f9;
            color: #475569;
        }

        body.dark-theme .modal-btn-cancel {
            background-color: #334155;
            color: #cbd5e1;
        }

        .modal-btn-cancel:hover {
            background-color: #e2e8f0;
        }

        body.dark-theme .modal-btn-cancel:hover {
            background-color: #475569;
        }

        .modal-btn-approve {
            background-color: #16a34a;
            color: #ffffff;
        }

        .modal-btn-approve:hover {
            background-color: #15803d;
        }

        .modal-btn-reject {
            background-color: #dc2626;
            color: #ffffff;
        }

        .modal-btn-reject:hover {
            background-color: #b91c1c;
        }

        /* Loading State */
        .modal-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .modal-btn.loading svg {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Approve confirmation styling */
        .modal-warning.approve {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        body.dark-theme .modal-warning.approve {
            background-color: #14532d;
            border-color: #166534;
        }

        .modal-warning.approve .modal-warning-icon {
            color: #16a34a;
        }

        body.dark-theme .modal-warning.approve .modal-warning-icon {
            color: #4ade80;
        }

        .modal-warning.approve .modal-warning-text {
            color: #166534;
        }

        body.dark-theme .modal-warning.approve .modal-warning-text {
            color: #86efac;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard">
        <div class="page-header">
            <h1 class="page-title">Staff Dashboard</h1>
            <p class="page-description">Manage daily library operations</p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card status-blue">
                <div class="stat-card-header">
                    <div class="stat-icon status-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" />
                        </svg>
                    </div>
                    <div class="stat-value">{{ $currentlyIssued ?? 0 }}</div>
                </div>
                <div class="stat-label">Currently Issued</div>
            </div>

            <div class="stat-card status-green">
                <div class="stat-card-header">
                    <div class="stat-icon status-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                    <div class="stat-value">{{ $dueToday ?? 0 }}</div>
                </div>
                <div class="stat-label">Due Today</div>
            </div>

            <div class="stat-card status-red">
                <div class="stat-card-header">
                    <div class="stat-icon status-red">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <div class="stat-value">{{ $overdueCount ?? 0 }}</div>
                </div>
                <div class="stat-label">Overdue</div>
            </div>

            <div class="stat-card status-yellow">
                <div class="stat-card-header">
                    <div class="stat-icon status-yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="12" y1="18" x2="12" y2="12" />
                            <line x1="9" y1="15" x2="15" y2="15" />
                        </svg>
                    </div>
                    <div class="stat-value" data-pending-count-display>{{ $pendingRequestsCount ?? 0 }}</div>
                </div>
                <div class="stat-label">Pending Requests</div>
            </div>

            <div class="stat-card status-purple">
                <div class="stat-card-header">
                    <div class="stat-icon status-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div class="stat-value">₹{{ number_format($pendingFinesAmount ?? 0) }}</div>
                </div>
                <div class="stat-label">Pending Fines</div>
            </div>
        </div>

        <div class="charts-row">
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">Book Circulation</h2>
                </div>
                <div class="chart-container">
                    <canvas id="circulationChart"></canvas>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">7-Day Activity</h2>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="tables-row">
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-heading">
                        <h3 class="table-card-title">Pending Requests</h3>
                        <a href="{{ route('staff.book-requests.index') }}" class="table-card-link">View all</a>
                    </div>
                    <span class="table-card-count" data-pending-count-display>{{ $pendingRequestsCount ?? 0 }}</span>
                </div>

                <div class="table-list pending-requests-list">
                    @if(isset($pendingRequests) && $pendingRequests->count())
                        @foreach($pendingRequests as $req)
                            <div class="table-item table-request-item" id="request-{{ $req->id }}" data-request-id="{{ $req->id }}">
                                <div class="table-item-top">
                                    <div>
                                        <div class="table-item-title">{{ optional($req->book)->title ?? 'Untitled' }}</div>
                                        <div class="table-item-subtitle">{{ data_get($req, 'student.user.name', 'Unknown') }}</div>
                                    </div>
                                    <div class="table-item-meta">{{ optional($req->request_date)->format('M d, Y') }}</div>
                                </div>
                                <div class="table-item-meta">
                                    {{ data_get($req, 'student.student_id') ?: data_get($req, 'student.roll_no', 'No ID') }}
                                </div>
                                <div class="table-request-actions">
                                    <button class="action-btn btn-accept" onclick="processBookRequest({{ $req->id }}, 'approved')">Accept</button>
                                    <button class="action-btn btn-reject" onclick="processBookRequest({{ $req->id }}, 'rejected')">Reject</button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state table-empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 8v4l3 3" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                            </div>
                            <h3 class="empty-title">No pending requests</h3>
                            <p class="empty-subtitle">You're all caught up for now.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h3 class="table-card-title">Due Today</h3>
                    <span class="table-card-count">{{ $dueToday ?? 0 }}</span>
                </div>

                <div class="table-list">
                    @if(isset($dueTodayList) && $dueTodayList->count())
                        @foreach($dueTodayList as $item)
                            <div class="table-item">
                                <div class="table-item-top">
                                    <div>
                                        <div class="table-item-title">{{ optional($item->book)->title ?? 'Untitled' }}</div>
                                        <div class="table-item-subtitle">{{ data_get($item, 'student.user.name', 'Unknown') }}</div>
                                    </div>
                                    <div class="table-item-meta">Due today</div>
                                </div>
                                <div class="table-item-meta">Due: {{ optional($item->due_date)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state table-empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                            </div>
                            <h3 class="empty-title">No books due today</h3>
                            <p class="empty-subtitle">All clear for today.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h3 class="table-card-title">Recent Issues</h3>
                </div>

                <div class="table-list">
                    @if(isset($recentlyIssued) && $recentlyIssued->count())
                        @foreach($recentlyIssued as $item)
                            <div class="table-item">
                                <div class="table-item-top">
                                    <div>
                                        <div class="table-item-title">{{ optional($item->book)->title ?? 'Untitled' }}</div>
                                        <div class="table-item-subtitle">{{ data_get($item, 'student.user.name', 'Unknown') }}</div>
                                    </div>
                                    <div class="table-item-meta">{{ optional($item->issue_date)->diffForHumans() ?? 'Recently' }}</div>
                                </div>
                                <div class="table-item-meta">Issued on {{ optional($item->issue_date)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state table-empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 6v6l4 2" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                            </div>
                            <h3 class="empty-title">No recent issues</h3>
                            <p class="empty-subtitle">No books have been issued recently.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">Overdue Books</h2>
                <span class="section-count count-red">{{ $overdueCount ?? 0 }}</span>
            </div>

            <div class="overdue-list">
                @if(isset($overdues) && $overdues->count())
                    @foreach($overdues as $item)
                        <div class="overdue-item">
                            <div class="overdue-info">
                                <div class="overdue-book">{{ optional($item->book)->title ?? 'Untitled' }}</div>
                                <div class="overdue-student">{{ data_get($item, 'student.user.name', 'Unknown') }}</div>
                            </div>
                            <div class="overdue-details">
                                <div class="overdue-date">Due: {{ optional($item->due_date)->format('M d, Y') }}</div>
                                <div class="overdue-days">{{ optional($item->due_date)->diffInDays(now()) ?? 0 }}d</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <div class="empty-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </div>
                        <h3 class="empty-title">No overdue books</h3>
                        <p class="empty-subtitle">Great, nothing is overdue right now.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Request Action Modal -->
        <div class="modal-overlay" id="requestModal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-icon" id="modalIcon">
                        <!-- Icon will be set by JS -->
                    </div>
                    <div class="modal-title-group">
                        <h3 class="modal-title" id="modalTitle">Confirm Action</h3>
                        <p class="modal-subtitle" id="modalSubtitle">Review the details below</p>
                    </div>
                    <button class="modal-close" onclick="closeRequestModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="request-summary" id="requestSummary">
                        <div class="request-summary-item">
                            <span class="request-summary-label">Book</span>
                            <span class="request-summary-value book-title" id="summaryBook">-</span>
                        </div>
                        <div class="request-summary-item">
                            <span class="request-summary-label">Student</span>
                            <span class="request-summary-value" id="summaryStudent">-</span>
                        </div>
                        <div class="request-summary-item">
                            <span class="request-summary-label">Request Date</span>
                            <span class="request-summary-value" id="summaryDate">-</span>
                        </div>
                    </div>
                    
                    <div class="modal-warning" id="modalWarning">
                        <div class="modal-warning-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <p class="modal-warning-text" id="warningText">Please review the request details carefully.</p>
                    </div>
                    
                    <div class="modal-form-group">
                        <label class="modal-form-label" for="modalMessage">Message (Optional)</label>
                        <textarea class="modal-textarea" id="modalMessage" placeholder="Add a note for the student..."></textarea>
                        <p class="modal-hint">This message will be sent to the student with the notification.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn modal-btn-cancel" onclick="closeRequestModal()">
                        Cancel
                    </button>
                    <button class="modal-btn" id="modalConfirmBtn" onclick="confirmRequestAction()">
                        <span id="confirmBtnText">Confirm</span>
                        <svg id="confirmBtnSpinner" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <line x1="12" y1="2" x2="12" y2="6"></line>
                            <line x1="12" y1="18" x2="12" y2="22"></line>
                            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                            <line x1="2" y1="12" x2="6" y2="12"></line>
                            <line x1="18" y1="12" x2="22" y2="12"></line>
                            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');

            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            initializeDashboardCharts();
        });

        function initializeDashboardCharts() {
            if (typeof Chart === 'undefined') {
                return;
            }

            const isDark = document.body.classList.contains('dark-theme');
            const palette = {
                surface: isDark ? '#1e293b' : '#ffffff',
                grid: isDark ? '#334155' : '#e5e7eb',
                text: isDark ? '#cbd5e1' : '#475569'
            };

            Chart.defaults.color = palette.text;
            Chart.defaults.borderColor = palette.grid;

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    }
                }
            };

            const circulationCanvas = document.getElementById('circulationChart');
            if (circulationCanvas) {
                new Chart(circulationCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($circulationData['labels'] ?? []),
                        datasets: [{
                            data: @json($circulationData['data'] ?? []),
                            backgroundColor: @json($circulationData['colors'] ?? []),
                            borderWidth: 2,
                            borderColor: palette.surface
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '65%'
                    }
                });
            }

            const activityCanvas = document.getElementById('activityChart');
            if (activityCanvas) {
                new Chart(activityCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($activityData['labels'] ?? []),
                        datasets: [{
                                label: 'Issued',
                                data: @json($activityData['issued'] ?? []),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.12)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            },
                            {
                                label: 'Returned',
                                data: @json($activityData['returned'] ?? []),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: palette.grid
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'toast ' + (type === 'error' ? 'error' : 'success');
            toast.textContent = message;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                setTimeout(() => toast.remove(), 350);
            }, 3000);
        }

        function updatePendingRequestsCount(delta) {
            const displays = document.querySelectorAll('[data-pending-count-display]');
            if (!displays.length) {
                return;
            }

            const current = parseInt(displays[0].textContent, 10) || 0;
            const next = Math.max(0, current + delta);

            displays.forEach(display => {
                display.textContent = next.toString();
            });
        }

        function createPendingRequestItem(request) {
            const item = document.createElement('div');
            item.className = 'table-item table-request-item';
            item.id = `request-${request.id}`;
            item.dataset.requestId = request.id;
            item.innerHTML = `
                <div class="table-item-top">
                    <div>
                        <div class="table-item-title">${escapeHtml(request.book.title)}</div>
                        <div class="table-item-subtitle">${escapeHtml(request.student.name)}</div>
                    </div>
                    <div class="table-item-meta">${escapeHtml(request.request_date)}</div>
                </div>
                <div class="table-item-meta">${escapeHtml(request.student.student_id || 'No ID')}</div>
                <div class="table-request-actions">
                    <button class="action-btn btn-accept" onclick="processBookRequest(${request.id}, 'approved')">Accept</button>
                    <button class="action-btn btn-reject" onclick="processBookRequest(${request.id}, 'rejected')">Reject</button>
                </div>
            `;

            return item;
        }

        function renderPendingEmptyState(container) {
            if (!container) {
                return;
            }

            container.innerHTML = `
                <div class="empty-state table-empty-state">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M12 8v4l3 3"></path>
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                    </div>
                    <h3 class="empty-title">No pending requests</h3>
                    <p class="empty-subtitle">You're all caught up for now.</p>
                </div>
            `;
        }

        function fetchNextPending() {
            const container = document.querySelector('.pending-requests-list');
            if (!container) {
                return;
            }

            const existing = Array.from(container.querySelectorAll('[data-request-id]'))
                .map(element => element.dataset.requestId)
                .join(',');

            const url = `{{ route('staff.book-requests.next') }}?exclude=${encodeURIComponent(existing)}`;

            fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.request) {
                        const emptyState = container.querySelector('.empty-state');
                        if (emptyState) {
                            emptyState.remove();
                        }

                        container.appendChild(createPendingRequestItem(data.request));
                        return;
                    }

                    if (!container.querySelector('[data-request-id]')) {
                        renderPendingEmptyState(container);
                    }
                })
                .catch(error => {
                    console.error('Failed to fetch next pending request', error);
                });
        }

        // Modal state
        let currentRequestId = null;
        let currentRequestStatus = null;

        // Open request modal
        function openRequestModal(requestId, status, bookTitle, studentName, requestDate) {
            currentRequestId = requestId;
            currentRequestStatus = status;
            
            const modal = document.getElementById('requestModal');
            const modalIcon = document.getElementById('modalIcon');
            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');
            const modalWarning = document.getElementById('modalWarning');
            const warningText = document.getElementById('warningText');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            const modalMessage = document.getElementById('modalMessage');
            
            // Set summary values
            document.getElementById('summaryBook').textContent = bookTitle || 'Untitled';
            document.getElementById('summaryStudent').textContent = studentName || 'Unknown';
            document.getElementById('summaryDate').textContent = requestDate || '-';
            
            // Clear previous message
            modalMessage.value = '';
            
            if (status === 'approved') {
                modalIcon.className = 'modal-icon approve';
                modalIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                `;
                modalTitle.textContent = 'Approve Book Request';
                modalSubtitle.textContent = 'This will notify the student that their request has been approved.';
                modalWarning.className = 'modal-warning approve';
                warningText.textContent = 'Once approved, the book will be marked as available for the student to pick up. The student will receive a notification.';
                confirmBtn.className = 'modal-btn modal-btn-approve';
                confirmBtnText.textContent = 'Approve Request';
            } else {
                modalIcon.className = 'modal-icon reject';
                modalIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                `;
                modalTitle.textContent = 'Reject Book Request';
                modalSubtitle.textContent = 'Please provide a reason for rejecting this request.';
                modalWarning.className = 'modal-warning reject';
                warningText.textContent = 'This action will reject the request and notify the student. Please provide a reason (optional) to help the student understand.';
                confirmBtn.className = 'modal-btn modal-btn-reject';
                confirmBtnText.textContent = 'Reject Request';
            }
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close request modal
        function closeRequestModal() {
            const modal = document.getElementById('requestModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            currentRequestId = null;
            currentRequestStatus = null;
        }

        // Confirm request action
        async function confirmRequestAction() {
            if (!currentRequestId || !currentRequestStatus) return;
            
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            const confirmBtnSpinner = document.getElementById('confirmBtnSpinner');
            const modalMessage = document.getElementById('modalMessage');
            
            // Show loading state
            confirmBtn.classList.add('loading');
            confirmBtnText.textContent = currentRequestStatus === 'approved' ? 'Approving...' : 'Rejecting...';
            confirmBtnSpinner.style.display = 'inline';
            
            const requestElement = document.getElementById(`request-${currentRequestId}`);
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const message = modalMessage.value.trim();
            
            try {
                const response = await fetch(`{{ url('staff/book-requests') }}/${currentRequestId}`, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        status: currentRequestStatus,
                        staff_message: message
                    })
                });

                const data = await response.json().catch(() => null);

                if (!response.ok || !data || !data.success) {
                    console.error('Book request update failed', response.status, data);
                    showToast(data && data.message ? data.message : `Request failed (HTTP ${response.status})`, 'error');
                    return;
                }

                if (requestElement) {
                    const buttons = requestElement.querySelectorAll('.action-btn');
                    buttons.forEach(button => {
                        button.disabled = true;
                    });

                    const activeButton = requestElement.querySelector(currentRequestStatus === 'approved' ? '.btn-accept' : '.btn-reject');
                    if (activeButton) {
                        activeButton.textContent = currentRequestStatus === 'approved' ? 'Approved' : 'Rejected';
                    }
                }

                updatePendingRequestsCount(-1);
                showToast(currentRequestStatus === 'approved' ? 'Request approved successfully' : 'Request rejected', 'success');

                setTimeout(() => {
                    closeRequestModal();
                    if (requestElement) {
                        requestElement.remove();
                    }
                    fetchNextPending();
                }, 500);
            } catch (error) {
                console.error('Network or JavaScript error while updating request', error);
                showToast('Error updating request. Please try again.', 'error');
            } finally {
                confirmBtn.classList.remove('loading');
                confirmBtnText.textContent = currentRequestStatus === 'approved' ? 'Approve Request' : 'Reject Request';
                confirmBtnSpinner.style.display = 'none';
            }
        }

        // Updated processBookRequest function to use modal
        async function processBookRequest(requestId, status) {
            const requestElement = document.getElementById(`request-${requestId}`);
            if (!requestElement) return;
            
            const bookTitle = requestElement.querySelector('.table-item-title')?.textContent || 'Untitled';
            const studentName = requestElement.querySelector('.table-item-subtitle')?.textContent || 'Unknown';
            const requestDate = requestElement.querySelector('.table-item-meta')?.textContent || '-';
            
            openRequestModal(requestId, status, bookTitle, studentName, requestDate);
        }

        // Close modal on overlay click
        document.getElementById('requestModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRequestModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRequestModal();
            }
        });
    </script>
@endpush
