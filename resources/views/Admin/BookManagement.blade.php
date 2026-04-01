<!-- resources/views/Admin/books/index.blade.php -->
@extends('Admin.layouts.app')

@section('title', 'Book Management')

@push('styles')
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Book Management Styles */
        .book-management {
            padding: 0;
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
            gap: 0.6rem;
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
            min-width: 180px;
            max-width: 220px;
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
            gap: 0.45rem;
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            cursor: pointer;
            appearance: none;
            min-width: 108px;
            transition: all 0.3s ease;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }

        #conditionFilter {
            width: 144px;
        }

        #categoryFilter {
            width: 166px;
        }

        #availabilityFilter {
            width: 132px;
        }

        #sortFilter {
            width: 154px;
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
            gap: 0.6rem;
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

        #deleteBookModal.modal-overlay {
            background: rgba(15, 23, 42, 0.46);
            backdrop-filter: blur(4px);
        }

        #deleteBookModal .delete-book-modal {
            max-width: 640px;
            border-radius: 16px;
            max-height: 90vh;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--delete-border);
        }

        body.light-theme #deleteBookModal .delete-book-modal {
            background: #ffffff;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.16);
            --delete-surface: #ffffff;
            --delete-text: #0f172a;
            --delete-muted: #64748b;
            --delete-border: #e2e8f0;
            --delete-soft-border: #e2e8f0;
            --delete-hero-bg: #f8fafc;
            --delete-card-bg: #ffffff;
        }

        body.dark-theme #deleteBookModal .delete-book-modal {
            background: #0f172a;
            box-shadow: 0 22px 52px rgba(2, 6, 23, 0.56);
            --delete-surface: #0f172a;
            --delete-text: #f8fafc;
            --delete-muted: #94a3b8;
            --delete-border: #334155;
            --delete-soft-border: #334155;
            --delete-hero-bg: #111c2e;
            --delete-card-bg: #111827;
        }

        #deleteBookModal .delete-book-modal[data-delete-state="ready"] {
            --delete-accent: #dc2626;
            --delete-accent-rgb: 220, 38, 38;
            --delete-accent-soft: rgba(220, 38, 38, 0.1);
            --delete-status-bg: rgba(220, 38, 38, 0.08);
        }

        #deleteBookModal .delete-book-modal[data-delete-state="blocked"] {
            --delete-accent: #b45309;
            --delete-accent-rgb: 180, 83, 9;
            --delete-accent-soft: rgba(180, 83, 9, 0.1);
            --delete-status-bg: rgba(180, 83, 9, 0.08);
        }

        body.dark-theme #deleteBookModal .delete-book-modal[data-delete-state="ready"] {
            --delete-accent-soft: rgba(248, 113, 113, 0.16);
            --delete-status-bg: rgba(248, 113, 113, 0.14);
        }

        body.dark-theme #deleteBookModal .delete-book-modal[data-delete-state="blocked"] {
            --delete-accent-soft: rgba(251, 191, 36, 0.16);
            --delete-status-bg: rgba(251, 191, 36, 0.14);
        }

        #deleteBookModal .delete-modal-hero {
            padding: 20px 24px 16px;
            background: var(--delete-hero-bg);
            border-bottom: 1px solid var(--delete-soft-border);
        }

        #deleteBookModal .delete-modal-topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        #deleteBookModal .delete-modal-heading,
        #deleteBookModal .delete-modal-state-copy,
        #deleteBookModal .delete-modal-book-copy,
        #deleteBookModal .delete-modal-note-copy,
        #deleteBookModal .delete-modal-blocker-copy {
            min-width: 0;
        }

        #deleteBookModal .delete-modal-eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--delete-status-bg);
            color: var(--delete-accent);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        #deleteBookModal .delete-modal-title {
            margin: 10px 0 4px;
            font-size: 22px;
            line-height: 1.2;
            color: var(--delete-text);
        }

        #deleteBookModal .delete-modal-subtitle {
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
            color: var(--delete-muted);
            max-width: none;
        }

        #deleteBookModal .delete-modal-close {
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 10px;
            color: var(--delete-muted);
        }

        #deleteBookModal .delete-modal-state-row {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 14px;
            align-items: flex-start;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--delete-soft-border);
        }

        #deleteBookModal .delete-modal-state-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--delete-accent);
            background: var(--delete-accent-soft);
            border: 1px solid rgba(var(--delete-accent-rgb), 0.12);
            flex-shrink: 0;
        }

        #deleteBookModal .delete-modal-state-badge,
        #deleteBookModal .delete-modal-panel-label,
        #deleteBookModal .delete-modal-book-tag {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--delete-status-bg);
            color: var(--delete-accent);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }

        #deleteBookModal .delete-modal-state-title {
            margin: 8px 0 6px;
            font-size: 20px;
            line-height: 1.3;
            color: var(--delete-text);
        }

        #deleteBookModal .delete-modal-state-text,
        #deleteBookModal .delete-modal-panel-text,
        #deleteBookModal .delete-modal-note-text,
        #deleteBookModal .delete-modal-blocker-text {
            margin: 0;
            font-size: 14px;
            line-height: 1.55;
            color: var(--delete-muted);
        }

        #deleteBookModal .delete-modal-body {
            display: grid;
            gap: 14px;
            padding: 20px 24px;
            background: var(--delete-surface);
        }

        #deleteBookModal .delete-modal-book-card,
        #deleteBookModal .delete-modal-note,
        #deleteBookModal .delete-modal-blocker-panel {
            border-radius: 12px;
            border: 1px solid var(--delete-soft-border);
            background: var(--delete-card-bg);
        }

        #deleteBookModal .delete-modal-book-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
        }

        #deleteBookModal .delete-modal-book-avatar {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 20px;
            font-weight: 700;
            color: var(--delete-accent);
            background: var(--delete-accent-soft);
            border: 1px solid rgba(var(--delete-accent-rgb), 0.12);
            box-shadow: none;
        }

        #deleteBookModal .delete-modal-book-label {
            display: inline-flex;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--delete-muted);
        }

        #deleteBookModal .delete-modal-book-title {
            display: block;
            font-size: 18px;
            line-height: 1.35;
            color: var(--delete-text);
        }

        #deleteBookModal .delete-modal-book-isbn {
            display: block;
            margin-top: 4px;
            font-size: 13px;
            color: var(--delete-muted);
        }

        #deleteBookModal .delete-modal-book-tag {
            flex-shrink: 0;
            text-align: center;
        }

        #deleteBookModal .delete-modal-note {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 12px;
            padding: 14px 16px;
        }

        #deleteBookModal .delete-modal-note-icon,
        #deleteBookModal .delete-modal-blocker-icon {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
            color: var(--delete-accent);
            background: var(--delete-accent-soft);
        }

        #deleteBookModal .delete-modal-note-title,
        #deleteBookModal .delete-modal-blocker-title {
            display: block;
            font-size: 14px;
            line-height: 1.4;
            font-weight: 600;
            color: var(--delete-text);
        }

        #deleteBookModal .delete-modal-note-text,
        #deleteBookModal .delete-modal-blocker-text {
            margin-top: 2px;
        }

        #deleteBookModal .delete-modal-blocker-panel {
            padding: 16px;
        }

        #deleteBookModal .delete-modal-panel-head {
            margin-bottom: 12px;
        }

        #deleteBookModal .delete-modal-panel-label {
            margin-bottom: 8px;
        }

        #deleteBookModal .delete-modal-blocker-list {
            display: grid;
            gap: 10px;
        }

        #deleteBookModal .delete-modal-blocker-card {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            border: 1px solid var(--delete-soft-border);
            background: var(--delete-surface);
        }

        #deleteBookModal .delete-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 24px 20px;
            border-top: 1px solid var(--delete-soft-border);
            background: var(--delete-surface);
        }

        #deleteBookModal .delete-modal-secondary,
        #deleteBookModal .delete-modal-primary {
            min-width: 144px;
            min-height: 44px;
            padding: 0 16px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            justify-content: center;
        }

        #deleteBookModal .delete-modal-secondary {
            background: transparent;
        }

        #deleteBookModal .delete-modal-primary {
            color: #ffffff;
            background: var(--delete-accent);
            border: 1px solid var(--delete-accent);
            box-shadow: none;
        }

        #deleteBookModal .delete-modal-primary:hover:not(:disabled) {
            filter: brightness(0.95);
            transform: none;
        }

        #deleteBookModal .delete-book-modal[data-delete-state="blocked"] .delete-modal-primary:disabled {
            opacity: 1;
            color: #334155;
            background: #e2e8f0;
            border-color: #e2e8f0;
        }

        body.dark-theme #deleteBookModal .delete-book-modal[data-delete-state="blocked"] .delete-modal-primary:disabled {
            background: #475569;
            border-color: #475569;
            color: #e2e8f0;
        }

        #deleteBookModal .delete-book-modal[data-delete-state="ready"] .delete-modal-primary:disabled {
            opacity: 0.75;
        }

        @media (max-width: 640px) {
            #deleteBookModal .delete-book-modal {
                border-radius: 14px;
            }

            #deleteBookModal .delete-modal-hero,
            #deleteBookModal .delete-modal-body,
            #deleteBookModal .delete-modal-footer {
                padding-left: 18px;
                padding-right: 18px;
            }

            #deleteBookModal .delete-modal-state-row,
            #deleteBookModal .delete-modal-book-card {
                grid-template-columns: 1fr;
                flex-direction: column;
                align-items: flex-start;
            }

            #deleteBookModal .delete-modal-book-tag {
                width: 100%;
                justify-content: center;
            }

            #deleteBookModal .delete-modal-footer {
                flex-direction: column-reverse;
            }

            #deleteBookModal .delete-modal-secondary,
            #deleteBookModal .delete-modal-primary {
                width: 100%;
            }
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

        /* Form Validation Styles */
        .form-control.error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
        }

        .form-control.valid {
            border-color: #16a34a !important;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12) !important;
        }

        .form-control.pending {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12) !important;
        }

        body.dark-theme .form-control.error {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }

        body.dark-theme .form-control.valid {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2) !important;
        }

        body.dark-theme .form-control.pending {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2) !important;
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

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
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

                <button type="button" class="btn btn-outline" id="resetFiltersBtn">
                    <i class="fas fa-rotate-left"></i>
                    Reset
                </button>

                <label class="admin-table-entries-control" for="booksEntriesSelect">
                    <span>Show</span>
                    <select class="admin-table-entries-select" id="booksEntriesSelect" aria-label="Show book entries">
                        @foreach ([10, 20, 50, 100] as $entryCount)
                            <option value="{{ $entryCount }}" {{ (int) ($perPage ?? 10) === $entryCount ? 'selected' : '' }}>{{ $entryCount }}</option>
                        @endforeach
                    </select>
                    <span>entries</span>
                </label>

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
                                $activeIssuedCopiesCount = (int) ($book->active_issued_copies_count ?? 0);
                                $unresolvedRequestsCount = (int) ($book->unresolved_requests_count ?? 0);
                                $deleteBlockers = [];

                                if ($activeIssuedCopiesCount > 0) {
                                    $deleteBlockers[] = [
                                        'title' => $activeIssuedCopiesCount . ' issued ' . ($activeIssuedCopiesCount === 1 ? 'copy is' : 'copies are') . ' still active',
                                        'message' => 'Delete is disabled until every issued copy is returned.',
                                    ];
                                }

                                if ($unresolvedRequestsCount > 0) {
                                    $deleteBlockers[] = [
                                        'title' => $unresolvedRequestsCount . ' book ' . ($unresolvedRequestsCount === 1 ? 'request is' : 'requests are') . ' still open',
                                        'message' => 'Delete is disabled until every pending or approved request is resolved.',
                                    ];
                                }

                                $deleteDisabled = !empty($deleteBlockers);
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
                                data-active-issued-copies="{{ $activeIssuedCopiesCount }}"
                                data-unresolved-requests="{{ $unresolvedRequestsCount }}"
                                data-delete-blocked="{{ $deleteDisabled ? 'true' : 'false' }}"
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
                                        <button class="action-btn view" type="button" title="View Details"><i class="fas fa-eye"></i></button>
                                        <button class="action-btn edit" type="button" title="Edit Book"><i class="fas fa-edit"></i></button>
                                        <button class="action-btn delete" type="button" title="Delete Book">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
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
                <div id="paginationContainer" style="display: {{ $initialBooks->total() > 0 ? 'block' : 'none' }};">
                    {!! view('shared.admin-table-pagination', ['paginator' => $initialBooks])->render() !!}
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

                <form id="addBookForm" novalidate>
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

                <form id="editBookForm" novalidate>
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
        <div class="modal delete-book-modal" data-delete-state="ready">
            <div class="delete-modal-hero">
                <div class="delete-modal-topbar">
                    <div class="delete-modal-heading">
                        <span id="deleteBookEyebrow" class="delete-modal-eyebrow">Delete Book</span>
                        <h3 class="modal-title delete-modal-title">Delete Book</h3>
                        <p id="deleteBookHeroText" class="delete-modal-subtitle">Review the selected book before removing it from the catalog.</p>
                    </div>
                    <button class="modal-close-btn delete-modal-close" id="closeDeleteBookModal" type="button">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="delete-modal-state-row">
                    <div id="deleteBookStateIcon" class="delete-modal-state-icon">
                        <i class="fas fa-trash-alt"></i>
                    </div>
                    <div class="delete-modal-state-copy">
                        <span id="deleteBookStateBadge" class="delete-modal-state-badge">Permanent</span>
                        <h4 id="deleteBookStateTitle" class="delete-modal-state-title">Delete this book?</h4>
                        <p id="deleteBookStateText" class="delete-modal-state-text">This action permanently removes the selected book from the catalog.</p>
                    </div>
                </div>
            </div>

            <div class="modal-body delete-modal-body">
                <div class="delete-modal-book-card">
                    <div id="deleteBookInitial" class="delete-modal-book-avatar">B</div>
                    <div class="delete-modal-book-copy">
                        <span class="delete-modal-book-label">Selected Title</span>
                        <strong id="deleteBookTitle" class="delete-modal-book-title">Loading...</strong>
                        <span id="deleteBookISBN" class="delete-modal-book-isbn">ISBN: loading...</span>
                    </div>
                    <div id="deleteBookRecordTag" class="delete-modal-book-tag">Ready</div>
                </div>

                <div id="deleteBookBlockerPanel" class="delete-modal-blocker-panel" hidden>
                    <div class="delete-modal-panel-head">
                        <span class="delete-modal-panel-label">Open Dependencies</span>
                        <p class="delete-modal-panel-text">This book still has related library activity. Resolve these items first.</p>
                    </div>
                    <div id="deleteBookBlockerAlert" class="delete-modal-blocker-list" role="alert"></div>
                </div>

                <div id="deleteBookAllowedNote" class="delete-modal-note">
                    <div class="delete-modal-note-icon">
                        <i class="fas fa-circle-info"></i>
                    </div>
                    <div class="delete-modal-note-copy">
                        <span class="delete-modal-note-title">Before You Continue</span>
                        <span class="delete-modal-note-text">Use delete only when this title should be removed from the catalog permanently.</span>
                    </div>
                </div>
            </div>

            <div class="modal-footer delete-modal-footer">
                <button class="btn btn-outline delete-modal-secondary" id="cancelDeleteBook" type="button">
                    Keep Book
                </button>
                <button class="btn btn-confirm-danger delete-modal-primary" id="confirmDeleteBook" type="button">
                    <i class="fas fa-trash-alt"></i>
                    <span>Delete Book</span>
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
                this.currentDeleteBlockers = [];
                this.currentSearch = '';
                this.currentConditionFilter = 'all';
                this.currentCategoryFilter = 'all';
                this.currentAvailabilityFilter = 'all';
                this.currentSortFilter = 'recently-added';
                this.searchDebounceTimer = null;
                this.storageBase = '{{ asset('storage') }}';
                this.allBooks = [];
                const searchParams = new URLSearchParams(window.location.search);
                this.currentPage = Number(searchParams.get('page')) || 1;
                this.perPage = this.normalizePerPage(searchParams.get('per_page'));
                this.bookFormStates = {
                    addBookForm: { pending: new Set(), verified: {}, activeErrorField: null },
                    editBookForm: { pending: new Set(), verified: {}, activeErrorField: null },
                };
                this.init();
            }

            syncCurrentFiltersFromDom() {
                this.currentSearch = document.getElementById('searchInput')?.value.trim() || '';
                this.currentConditionFilter = document.getElementById('conditionFilter')?.value || 'all';
                this.currentCategoryFilter = document.getElementById('categoryFilter')?.value || 'all';
                this.currentAvailabilityFilter = document.getElementById('availabilityFilter')?.value || 'all';
                this.currentSortFilter = document.getElementById('sortFilter')?.value || 'recently-added';
                this.perPage = this.normalizePerPage(document.getElementById('booksEntriesSelect')?.value || this.perPage);
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
                ['addBookForm', 'editBookForm'].forEach(formId => {
                    const form = document.getElementById(formId);
                    if (!form) {
                        return;
                    }

                    this.ensureBookFieldIcons(form);

                    form.querySelectorAll('input, select, textarea').forEach(input => {
                        const triggerEvent = input.type === 'file' || input.tagName === 'SELECT' ? 'change' : 'input';

                        input.addEventListener(triggerEvent, () => {
                            if (input.type !== 'file' && input.name !== 'isbn') {
                                const normalizedValue = this.normalizeBookFieldValue(input.name, input.value);
                                if (normalizedValue !== input.value) {
                                    input.value = normalizedValue;
                                }
                            }

                            if (input.name === 'isbn') {
                                delete this.getBookFormState(form).verified.isbn;
                            }

                            this.validateSingleBookField(form, input, {
                                showErrors: true,
                                runRemote: false,
                                activeInput: input,
                            });

                            if (input.name === 'total_copies') {
                                const availableCopiesInput = form.querySelector('[name="available_copies"]');
                                if (availableCopiesInput) {
                                    this.validateSingleBookField(form, availableCopiesInput, {
                                        showErrors: false,
                                        runRemote: false,
                                        activeInput: input,
                                    });
                                }
                            }
                        });

                        if (input.type !== 'file') {
                            input.addEventListener('blur', () => {
                                this.validateSingleBookField(form, input, {
                                    showErrors: true,
                                    runRemote: input.name === 'isbn',
                                    activeInput: input,
                                });
                            });
                        }
                    });

                    this.updateBookSubmitState(form);
                });
            }

            getBookFormState(form) {
                return this.bookFormStates[form.id];
            }

            getBookSubmitButton(form) {
                return form.id === 'addBookForm'
                    ? document.getElementById('submitAddBook')
                    : document.getElementById('submitEditBook');
            }

            getBookFieldGroup(input) {
                return input?.closest('.form-group') ?? null;
            }

            getBookFieldErrorElement(input, { createIfMissing = false } = {}) {
                if (!input) {
                    return null;
                }

                const parent = input.parentElement;
                let errorElement = parent?.querySelector('.field-error-message') ?? null;

                if (!errorElement && createIfMissing && parent) {
                    errorElement = document.createElement('div');
                    errorElement.className = 'field-error-message';
                    errorElement.setAttribute('role', 'alert');
                    parent.appendChild(errorElement);
                }

                return errorElement;
            }

            getBookFieldIcon(input) {
                return this.getBookFieldGroup(input)?.querySelector('.field-validation-icon') ?? null;
            }

            ensureBookFieldIcons(form) {
                form.querySelectorAll('input, select, textarea').forEach(input => {
                    if (input.type === 'file') {
                        return;
                    }

                    const group = this.getBookFieldGroup(input);
                    if (!group || group.querySelector('.field-validation-icon')) {
                        return;
                    }

                    const icon = document.createElement('span');
                    icon.className = 'field-validation-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    group.appendChild(icon);
                });
            }

            clearDisplayedBookError(input) {
                if (!input) {
                    return;
                }

                input.classList.remove('error');
                input.removeAttribute('aria-invalid');

                const errorElement = this.getBookFieldErrorElement(input);
                if (errorElement) {
                    errorElement.innerHTML = '';
                    errorElement.style.display = 'none';
                }
            }

            clearBookFieldVisualState(input) {
                if (!input) {
                    return;
                }

                input.classList.remove('error', 'valid', 'pending');
                input.removeAttribute('aria-invalid');

                const group = this.getBookFieldGroup(input);
                group?.classList.remove('has-valid', 'has-invalid', 'has-pending');

                const icon = this.getBookFieldIcon(input);
                if (icon) {
                    icon.innerHTML = '';
                }

                const errorElement = this.getBookFieldErrorElement(input);
                if (errorElement) {
                    errorElement.innerHTML = '';
                    errorElement.style.display = 'none';
                }
            }

            focusBookField(input) {
                if (!input) {
                    return;
                }

                const target = input.closest('.form-group') ?? input;
                const modal = input.closest('.modal');

                if (modal) {
                    const modalRect = modal.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();
                    const nextScrollTop = modal.scrollTop
                        + (targetRect.top - modalRect.top)
                        - (modal.clientHeight / 2)
                        + (targetRect.height / 2);

                    modal.scrollTo({
                        top: Math.max(0, nextScrollTop),
                        behavior: 'smooth',
                    });
                } else {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                input.focus({ preventScroll: true });
            }

            normalizeBookFieldValue(fieldName, value) {
                const rawValue = String(value ?? '');

                switch (fieldName) {
                    case 'isbn':
                        return rawValue.replace(/[\/\-\s]/g, '');
                    case 'title':
                    case 'author':
                    case 'publisher':
                    case 'new_category':
                    case 'description':
                        return rawValue.replace(/\s+/g, ' ').trim();
                    case 'shelf_no':
                        return rawValue.replace(/\s+/g, '').toUpperCase();
                    default:
                        return rawValue.trim();
                }
            }

            getBookFieldRules() {
                return {
                    isbn: {
                        required: true,
                        pattern: /^\d{5,13}$/,
                        requiredMessage: 'Enter the book ISBN.',
                        patternMessage: 'ISBN must contain 5 to 13 digits. You may use / or - as separators.',
                    },
                    shelf_no: {
                        required: true,
                        pattern: /^[A-Za-z0-9]+[-]?[A-Za-z0-9]*$/,
                        maxLength: 20,
                        requiredMessage: 'Enter the rack number.',
                        patternMessage: 'Rack number must contain only letters, numbers, and an optional dash like A-12.',
                        maxLengthMessage: 'Rack number must be 20 characters or fewer.',
                    },
                    title: {
                        required: true,
                        minLength: 2,
                        maxLength: 255,
                        pattern: /^[A-Za-z0-9\s\-:'.&()]+$/,
                        requiredMessage: 'Enter the book title.',
                        minLengthMessage: 'Book title must be at least 2 characters long.',
                        maxLengthMessage: 'Book title must be 255 characters or fewer.',
                        patternMessage: 'Title can only contain letters, numbers, spaces, and - : \' . & ( ).',
                    },
                    author: {
                        required: true,
                        minLength: 2,
                        maxLength: 255,
                        pattern: /^[A-Za-z\s.]+$/,
                        requiredMessage: 'Enter the author name.',
                        minLengthMessage: 'Author name must be at least 2 characters long.',
                        maxLengthMessage: 'Author name must be 255 characters or fewer.',
                        patternMessage: 'Author name can only contain letters, spaces, and periods.',
                    },
                    publisher: {
                        maxLength: 255,
                        pattern: /^[A-Za-z0-9\s&.,'-]+$/,
                        maxLengthMessage: 'Publisher name must be 255 characters or fewer.',
                        patternMessage: 'Publisher name can only contain letters, numbers, spaces, and & . , \' -.',
                    },
                    new_category: {
                        minLength: 2,
                        maxLength: 50,
                        pattern: /^[A-Za-z\s&]+$/,
                        minLengthMessage: 'New category name must be at least 2 characters long.',
                        maxLengthMessage: 'New category name must be 50 characters or fewer.',
                        patternMessage: 'Category name can only contain letters, spaces, and &.',
                    },
                    total_copies: {
                        required: true,
                        numeric: true,
                        min: 1,
                        max: 9999,
                        requiredMessage: 'Enter the total number of copies.',
                        numericMessage: 'Total copies must be a whole number.',
                        minMessage: 'Total copies must be at least 1.',
                        maxMessage: 'Total copies must not exceed 9999.',
                    },
                    available_copies: {
                        required: true,
                        numeric: true,
                        min: 0,
                        max: 9999,
                        requiredMessage: 'Enter the available number of copies.',
                        numericMessage: 'Available copies must be a whole number.',
                        minMessage: 'Available copies cannot be negative.',
                        maxMessage: 'Available copies must not exceed 9999.',
                    },
                    condition: {
                        required: true,
                        requiredMessage: 'Select the book condition.',
                    },
                    description: {
                        maxLength: 2000,
                        maxLengthMessage: 'Description must be 2000 characters or fewer.',
                    },
                    cover_image: {
                        accept: 'image/*',
                        acceptMessage: 'Cover image must be an image file.',
                        maxSize: 2,
                        maxSizeMessage: 'Cover image must not exceed 2MB.',
                    },
                };
            }

            setBookFieldState(input, state = 'neutral', message = '') {
                input.classList.remove('error', 'valid', 'pending');
                const form = input.closest('form');
                const stateStore = form ? this.getBookFormState(form) : null;
                const group = this.getBookFieldGroup(input);
                const icon = this.getBookFieldIcon(input);
                const errorElement = this.getBookFieldErrorElement(input, {
                    createIfMissing: state === 'error',
                });

                group?.classList.remove('has-valid', 'has-invalid', 'has-pending');

                if (state === 'error') {
                    group?.classList.add('has-invalid');
                    input.classList.add('error');
                    input.setAttribute('aria-invalid', 'true');
                    input.dataset.valid = 'false';
                    errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
                    errorElement.style.display = 'flex';
                    if (icon) {
                        icon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                    }
                } else {
                    input.removeAttribute('aria-invalid');
                    if (errorElement) {
                        errorElement.innerHTML = '';
                        errorElement.style.display = 'none';
                    }

                    if (state === 'valid') {
                        group?.classList.add('has-valid');
                        input.classList.add('valid');
                        input.dataset.valid = 'true';
                        if (icon) {
                            icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                        }
                    } else if (state === 'pending') {
                        group?.classList.add('has-pending');
                        input.classList.add('pending');
                        input.dataset.valid = 'false';
                        if (icon) {
                            icon.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        }
                    } else {
                        input.dataset.valid = 'false';
                        if (icon) {
                            icon.innerHTML = '';
                        }
                    }
                }

                if (stateStore) {
                    stateStore.activeErrorField = state === 'error' ? input.name : null;
                }

                this.updateBookSubmitState(form);
            }

            clearFieldError(input, { clearValidityOnly = false } = {}) {
                this.clearDisplayedBookError(input);
                input.classList.remove('pending');
                this.getBookFieldGroup(input)?.classList.remove('has-invalid', 'has-pending');

                const icon = this.getBookFieldIcon(input);
                if (icon) {
                    icon.innerHTML = input.classList.contains('valid')
                        ? '<i class="fas fa-check-circle"></i>'
                        : '';
                }

                const form = input.closest('form');
                const state = form ? this.getBookFormState(form) : null;
                if (state?.activeErrorField === input.name) {
                    state.activeErrorField = null;
                }

                if (!clearValidityOnly) {
                    this.getBookFieldGroup(input)?.classList.remove('has-valid');
                    input.classList.remove('valid');
                    input.dataset.valid = 'false';
                    if (icon) {
                        icon.innerHTML = '';
                    }
                }
            }

            validateBookFieldValue(input, rules, form) {
                const value = input.type === 'file'
                    ? input.value
                    : this.normalizeBookFieldValue(input.name, input.value);
                const isRequired = input.hasAttribute('required');

                if (input.type !== 'file' && input.name !== 'isbn') {
                    input.value = value;
                }

                if (!isRequired && value === '') {
                    return null;
                }

                if (rules.required && value === '') {
                    return rules.requiredMessage || 'This field is required.';
                }

                if (rules.minLength && value.length < rules.minLength) {
                    return rules.minLengthMessage || `Must be at least ${rules.minLength} characters long.`;
                }

                if (rules.maxLength && value.length > rules.maxLength) {
                    return rules.maxLengthMessage || `Must be ${rules.maxLength} characters or fewer.`;
                }

                if (rules.pattern && value !== '' && !rules.pattern.test(value)) {
                    return rules.patternMessage || 'This value is not in the correct format.';
                }

                if (rules.numeric && value !== '' && (!/^\d+$/.test(value) || Number.isNaN(Number(value)))) {
                    return rules.numericMessage || 'Must be a whole number.';
                }

                if (rules.min !== undefined && value !== '' && Number(value) < rules.min) {
                    return rules.minMessage || `Must be at least ${rules.min}.`;
                }

                if (rules.max !== undefined && value !== '' && Number(value) > rules.max) {
                    return rules.maxMessage || `Must not exceed ${rules.max}.`;
                }

                if (input.name === 'available_copies') {
                    const totalCopies = Number(form.querySelector('[name="total_copies"]')?.value || 0);
                    if (value !== '' && totalCopies && Number(value) > totalCopies) {
                        return 'Available copies cannot exceed total copies.';
                    }
                }

                if (rules.accept && input.type === 'file' && input.files?.length) {
                    const file = input.files[0];
                    if (file && !file.type.startsWith('image/')) {
                        return rules.acceptMessage;
                    }
                }

                if (rules.maxSize && input.type === 'file' && input.files?.length) {
                    const file = input.files[0];
                    if (file && file.size > rules.maxSize * 1024 * 1024) {
                        return rules.maxSizeMessage;
                    }
                }

                return null;
            }

            validateCategoryState(form, { showErrors = false, activeInput = null } = {}) {
                const categorySelect = form.querySelector('[name="category_id"]');
                const newCategoryInput = form.querySelector('[name="new_category"]');
                const categoryTarget = activeInput && ['category_id', 'new_category'].includes(activeInput.name)
                    ? activeInput
                    : categorySelect;

                if (!categorySelect) {
                    return true;
                }

                if (!newCategoryInput) {
                    const selectedCategory = categorySelect.value.trim();
                    this.clearFieldError(categorySelect, { clearValidityOnly: true });

                    if (!selectedCategory) {
                        if (showErrors) {
                            this.setBookFieldState(categoryTarget, 'error', 'Select a valid category.');
                        } else {
                            categorySelect.dataset.valid = 'false';
                            this.updateBookSubmitState(form);
                        }
                        return false;
                    }

                    this.setBookFieldState(categorySelect, 'valid');
                    return true;
                }

                const selectedCategory = categorySelect.value.trim();
                const newCategory = this.normalizeBookFieldValue('new_category', newCategoryInput.value);
                newCategoryInput.value = newCategory;

                this.clearFieldError(categorySelect, { clearValidityOnly: true });
                this.clearFieldError(newCategoryInput, { clearValidityOnly: true });

                if (!selectedCategory && !newCategory) {
                    if (showErrors) {
                        this.setBookFieldState(categoryTarget, 'error', 'Select an existing category or create a new one.');
                    } else {
                        categorySelect.dataset.valid = 'false';
                        newCategoryInput.dataset.valid = 'false';
                        this.updateBookSubmitState(form);
                    }
                    return false;
                }

                if (selectedCategory && newCategory) {
                    if (showErrors) {
                        this.setBookFieldState(categoryTarget, 'error', 'Choose either an existing category or a new category, not both.');
                    } else {
                        categorySelect.dataset.valid = 'false';
                        newCategoryInput.dataset.valid = 'false';
                        this.updateBookSubmitState(form);
                    }
                    return false;
                }

                if (newCategory) {
                    const categoryRules = this.getBookFieldRules().new_category;
                    const categoryMessage = this.validateBookFieldValue(newCategoryInput, categoryRules, form);
                    if (categoryMessage) {
                        if (showErrors) {
                            this.setBookFieldState(newCategoryInput, 'error', categoryMessage);
                        } else {
                            newCategoryInput.dataset.valid = 'false';
                            this.updateBookSubmitState(form);
                        }
                        return false;
                    }
                }

                this.setBookFieldState(categorySelect, selectedCategory ? 'valid' : 'neutral');
                this.setBookFieldState(newCategoryInput, newCategory ? 'valid' : 'neutral');
                return true;
            }

            async validateRemoteBookField(form, input) {
                const state = this.getBookFormState(form);
                state.pending.add(input.name);
                this.setBookFieldState(input, 'pending');

                try {
                    const payload = {
                        field: input.name,
                        [input.name]: this.normalizeBookFieldValue(input.name, input.value),
                        book_id: form.id === 'editBookForm' ? this.currentBookId : null,
                    };

                    if (input.name === 'available_copies') {
                        payload.total_copies = form.querySelector('[name="total_copies"]')?.value || '';
                    }

                    const response = await fetch('{{ route('admin.books.validate-field') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
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
            }

            async validateSingleBookField(form, input, { showErrors = false, runRemote = false, activeInput = input } = {}) {
                const rules = this.getBookFieldRules()[input.name];

                if (input.name === 'category_id' || input.name === 'new_category') {
                    return this.validateCategoryState(form, { showErrors, activeInput });
                }

                if (!rules) {
                    input.dataset.valid = 'true';
                    this.updateBookSubmitState(form);
                    return true;
                }

                const message = this.validateBookFieldValue(input, rules, form);
                if (message) {
                    if (showErrors) {
                        this.setBookFieldState(input, 'error', message);
                    } else {
                        input.dataset.valid = 'false';
                        this.updateBookSubmitState(form);
                    }
                    return false;
                }

                if (input.name === 'isbn') {
                    const normalizedIsbn = this.normalizeBookFieldValue('isbn', input.value);
                    const state = this.getBookFormState(form);

                    if (runRemote) {
                        return this.validateRemoteBookField(form, input);
                    }

                    if (state.verified.isbn === normalizedIsbn) {
                        this.setBookFieldState(input, 'valid');
                        return true;
                    }

                    this.setBookFieldState(input, 'neutral');
                    return false;
                }

                this.setBookFieldState(input, 'valid');
                return true;
            }

            async validateBookForm(form, { showErrors = true } = {}) {
                let isValid = true;
                let firstInvalidInput = null;
                const inputs = Array.from(form.querySelectorAll('input, select, textarea'));

                for (const input of inputs) {
                    if (input.type === 'file') {
                        const result = await this.validateSingleBookField(form, input, {
                            showErrors,
                            runRemote: false,
                            activeInput: input,
                        });
                        isValid = result && isValid;
                        if (!result && !firstInvalidInput) {
                            firstInvalidInput = input;
                        }
                        continue;
                    }

                    const result = await this.validateSingleBookField(form, input, {
                        showErrors,
                        runRemote: input.name === 'isbn',
                        activeInput: input,
                    });
                    isValid = result && isValid;
                    if (!result && !firstInvalidInput) {
                        firstInvalidInput = input;
                    }
                }

                if (!isValid && firstInvalidInput) {
                    this.focusBookField(firstInvalidInput);
                }

                return isValid;
            }

            applyBookServerErrors(form, errors = {}) {
                if (!form || !errors || Object.keys(errors).length === 0) {
                    return;
                }

                let firstInput = null;

                Object.entries(errors).forEach(([fieldName, messages]) => {
                    const message = Array.isArray(messages) ? messages[0] : messages;
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (!input) {
                        return;
                    }

                    this.setBookFieldState(input, 'error', message);

                    if (!firstInput) {
                        firstInput = input;
                    }
                });

                if (firstInput) {
                    this.focusBookField(firstInput);
                }
            }

            updateBookSubmitState(form) {
                if (!form) {
                    return;
                }

                const button = this.getBookSubmitButton(form);
                if (!button) {
                    return;
                }

                const state = this.getBookFormState(form);
                const invalidInputs = Array.from(form.querySelectorAll('.form-control')).some(input => {
                    if (input.type === 'file') {
                        return input.files?.length ? input.dataset.valid !== 'true' : false;
                    }

                    if (['publisher', 'description'].includes(input.name)) {
                        return input.value.trim() !== '' && input.dataset.valid !== 'true';
                    }

                    if (input.name === 'new_category') {
                        const categorySelected = form.querySelector('[name="category_id"]')?.value.trim();
                        return input.dataset.valid !== 'true' && (!categorySelected || input.value.trim() !== '');
                    }

                    return input.dataset.valid !== 'true';
                });

                button.disabled = state.pending.size > 0 || invalidInputs;
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

                const entriesSelect = document.getElementById('booksEntriesSelect');
                if (entriesSelect) {
                    entriesSelect.addEventListener('change', (e) => {
                        this.perPage = this.normalizePerPage(e.target.value);
                        this.currentPage = 1;
                        this.fetchBooksData(1);
                    });
                }

                const resetFiltersBtn = document.getElementById('resetFiltersBtn');
                if (resetFiltersBtn) {
                    resetFiltersBtn.addEventListener('click', () => {
                        this.resetFilters();
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
                    page: requestedPage,
                    per_page: this.perPage
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
                        this.perPage = this.normalizePerPage(this.perPage);

                        try {
                            if (data.total === 0) {
                                tableBody.innerHTML = '';
                                emptyState.style.display = 'block';
                                paginationContainer.style.display = 'none';
                            } else {
                                tableBody.innerHTML = data.tableRows || '';
                                emptyState.style.display = 'none';

                                if (data.pagination) {
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

                        this.syncUrlState();

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

            syncUrlState() {
                const params = new URLSearchParams();

                if (this.currentSearch) params.set('search', this.currentSearch);
                if (this.currentConditionFilter !== 'all') params.set('condition', this.currentConditionFilter);
                if (this.currentCategoryFilter !== 'all') params.set('category', this.currentCategoryFilter);
                if (this.currentAvailabilityFilter !== 'all') params.set('availability', this.currentAvailabilityFilter);
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
                this.currentConditionFilter = 'all';
                this.currentCategoryFilter = 'all';
                this.currentAvailabilityFilter = 'all';
                this.currentSortFilter = 'recently-added';
                this.currentPage = 1;

                const searchInput = document.getElementById('searchInput');
                const conditionFilter = document.getElementById('conditionFilter');
                const categoryFilter = document.getElementById('categoryFilter');
                const availabilityFilter = document.getElementById('availabilityFilter');
                const sortFilter = document.getElementById('sortFilter');

                if (searchInput) searchInput.value = '';
                if (conditionFilter) conditionFilter.value = 'all';
                if (categoryFilter) categoryFilter.value = 'all';
                if (availabilityFilter) availabilityFilter.value = 'all';
                if (sortFilter) sortFilter.value = 'recently-added';

                this.fetchBooksData(1);
            }

            normalizePerPage(value) {
                const allowedValues = [10, 20, 50, 100];
                const perPage = Number(value);
                return allowedValues.includes(perPage) ? perPage : 10;
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
                        this.currentDeleteBlockers = this.getDeleteBlockers(row);
                        this.openDeleteModal();
                    });
                });
            }

            getDeleteBlockers(row) {
                if (!row) {
                    return [];
                }

                const activeIssuedCopies = Number(row.dataset.activeIssuedCopies || 0);
                const unresolvedRequests = Number(row.dataset.unresolvedRequests || 0);
                const blockers = [];

                if (activeIssuedCopies > 0) {
                    blockers.push({
                        title: `${activeIssuedCopies} issued ${activeIssuedCopies === 1 ? 'copy is' : 'copies are'} still active`,
                        message: 'Delete is disabled until every issued copy is returned.',
                    });
                }

                if (unresolvedRequests > 0) {
                    blockers.push({
                        title: `${unresolvedRequests} book ${unresolvedRequests === 1 ? 'request is' : 'requests are'} still open`,
                        message: 'Delete is disabled until every pending or approved request is resolved.',
                    });
                }

                return blockers;
            }

            renderDeleteBlockers(blockers = []) {
                if (!Array.isArray(blockers) || blockers.length === 0) {
                    return '';
                }

                return blockers.map(blocker => `
                    <div class="delete-modal-blocker-card">
                        <div class="delete-modal-blocker-icon" aria-hidden="true">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="delete-modal-blocker-copy">
                            <span class="delete-modal-blocker-title">${this.escapeHtml(blocker.title || '')}</span>
                            <span class="delete-modal-blocker-text">${this.escapeHtml(blocker.message || '')}</span>
                        </div>
                    </div>
                `).join('');
            }

            getDeleteBookInitial() {
                const title = (this.currentBookTitle || '').trim();
                return title ? title.charAt(0).toUpperCase() : '#';
            }

            updateDeleteConfirmButtonState() {
                const confirmDeleteButton = document.getElementById('confirmDeleteBook');

                if (!confirmDeleteButton) {
                    return;
                }

                if (this.currentDeleteBlockers.length > 0) {
                    confirmDeleteButton.disabled = true;
                    confirmDeleteButton.innerHTML = '<i class="fas fa-ban"></i><span>Cannot Delete</span>';
                    return;
                }

                confirmDeleteButton.disabled = false;
                confirmDeleteButton.innerHTML = '<i class="fas fa-trash-alt"></i><span>Delete Book</span>';
            }

            escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
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
                    const form = document.getElementById('addBookForm');
                    if (form) {
                        form.reset();
                        this.resetBookFormValidation(form);
                        this.seedBookFormValidation(form);
                    }
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
                if (modalId === 'addBookModal') {
                    this.resetBookFormValidation(document.getElementById('addBookForm'));
                }
                if (modalId === 'editBookModal') {
                    this.resetBookFormValidation(document.getElementById('editBookForm'));
                }
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
                const form = document.getElementById('editBookForm');
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

                this.seedBookFormValidation(form);
                this.openModal('editBookModal');
            }

            openDeleteModal() {
                const deleteModal = document.querySelector('#deleteBookModal .delete-book-modal');
                const blockerAlert = document.getElementById('deleteBookBlockerAlert');
                const blockerPanel = document.getElementById('deleteBookBlockerPanel');
                const allowedNote = document.getElementById('deleteBookAllowedNote');
                const eyebrow = document.getElementById('deleteBookEyebrow');
                const heroText = document.getElementById('deleteBookHeroText');
                const stateIcon = document.getElementById('deleteBookStateIcon');
                const stateBadge = document.getElementById('deleteBookStateBadge');
                const stateTitle = document.getElementById('deleteBookStateTitle');
                const stateText = document.getElementById('deleteBookStateText');
                const bookTitle = document.getElementById('deleteBookTitle');
                const bookIsbn = document.getElementById('deleteBookISBN');
                const bookInitial = document.getElementById('deleteBookInitial');
                const bookRecordTag = document.getElementById('deleteBookRecordTag');
                const isBlocked = this.currentDeleteBlockers.length > 0;

                if (deleteModal) {
                    deleteModal.dataset.deleteState = isBlocked ? 'blocked' : 'ready';
                }

                this.updateDeleteConfirmButtonState();

                if (bookTitle) {
                    bookTitle.textContent = this.currentBookTitle || 'Unknown Book';
                }

                if (bookIsbn) {
                    bookIsbn.textContent = `ISBN: ${this.currentBookISBN || 'N/A'}`;
                }

                if (bookInitial) {
                    bookInitial.textContent = this.getDeleteBookInitial();
                }

                if (bookRecordTag) {
                    bookRecordTag.textContent = isBlocked ? 'Blocked' : 'Ready';
                }

                if (eyebrow) {
                    eyebrow.textContent = isBlocked ? 'Delete Blocked' : 'Delete Book';
                }

                if (heroText) {
                    heroText.textContent = isBlocked
                        ? 'This book cannot be deleted yet because it still has active library records.'
                        : 'Review the selected book before removing it from the catalog.';
                }

                if (stateIcon) {
                    stateIcon.innerHTML = isBlocked
                        ? '<i class="fas fa-ban"></i>'
                        : '<i class="fas fa-trash-alt"></i>';
                }

                if (stateBadge) {
                    stateBadge.textContent = isBlocked ? 'Blocked' : 'Permanent';
                }

                if (stateTitle) {
                    stateTitle.textContent = isBlocked
                        ? 'Delete unavailable'
                        : 'Delete this book?';
                }

                if (stateText) {
                    stateText.textContent = isBlocked
                        ? 'Resolve the items below, then try again.'
                        : 'This action permanently removes the selected book from the catalog.';
                }

                if (blockerPanel) {
                    blockerPanel.hidden = !isBlocked;
                }

                if (allowedNote) {
                    allowedNote.hidden = isBlocked;
                }

                if (blockerAlert) {
                    blockerAlert.innerHTML = isBlocked
                        ? this.renderDeleteBlockers(this.currentDeleteBlockers)
                        : '';
                }

                this.openModal('deleteBookModal');
            }

            submitAddBook() {
                const form = document.getElementById('addBookForm');
                const newCategoryInput = document.getElementById('addNewCategory');

                this.validateBookForm(form, { showErrors: true }).then(isValid => {
                    if (!isValid) {
                        return;
                    }

                    const newCategory = newCategoryInput.value.trim();
                    if (newCategory) {
                        this.createNewCategory(newCategory, form);
                        return;
                    }

                    this.submitAddBookForm(form);
                });
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
                        this.setBookFieldState(categorySelect, 'valid');
                        this.setBookFieldState(document.getElementById('addNewCategory'), 'valid');
                        
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
                    if (data.errors && Object.keys(data.errors).length > 0) {
                        this.applyBookServerErrors(form, data.errors);
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
                this.validateBookForm(form, { showErrors: true }).then(isValid => {
                    if (!isValid) {
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
                        if (data.errors && Object.keys(data.errors).length > 0) {
                            this.applyBookServerErrors(form, data.errors);
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
                });
            }

            confirmDeleteBook() {
                if (this.currentDeleteBlockers.length > 0) {
                    return;
                }

                const deleteButton = document.getElementById('confirmDeleteBook');
                if (deleteButton) {
                    deleteButton.disabled = true;
                    deleteButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Deleting...</span>';
                }

                fetch(`{{ url('admin/books') }}/${this.currentBookId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(async response => {
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const error = new Error(data.message || `HTTP error! status: ${response.status}`);
                        error.response = response;
                        error.data = data;
                        throw error;
                    }

                    return data;
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
                    const isBlockedDelete = [409, 422].includes(error?.response?.status);
                    this.showNotification(error?.data?.message || error.message || 'Error deleting book', isBlockedDelete ? 'warning' : 'error');

                    if (isBlockedDelete) {
                        this.currentDeleteBlockers = Array.isArray(error?.data?.blockers) && error.data.blockers.length > 0
                            ? error.data.blockers
                            : [{
                                title: 'Deletion is still blocked',
                                message: error?.data?.message || 'This book cannot be deleted right now. Refresh the data and resolve any active activity before trying again.',
                            }];

                        this.openDeleteModal();

                        this.fetchBooksData(this.currentPage);
                    }
                })
                .finally(() => {
                    this.updateDeleteConfirmButtonState();
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

            resetBookFormValidation(form) {
                if (!form) {
                    return;
                }

                const state = this.getBookFormState(form);
                state.pending.clear();
                state.verified = {};
                state.activeErrorField = null;

                form.querySelectorAll('.form-control').forEach(input => {
                    input.classList.remove('error', 'valid', 'pending');
                    input.dataset.valid = 'false';
                    input.removeAttribute('aria-invalid');
                    input.closest('.form-group')?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                    const icon = input.closest('.form-group')?.querySelector('.field-validation-icon');
                    if (icon) {
                        icon.innerHTML = '';
                    }
                    const errorElement = input.parentElement.querySelector('.field-error-message');
                    if (errorElement) {
                        errorElement.innerHTML = '';
                        errorElement.style.display = 'none';
                    }
                });

                if (form.id === 'addBookForm') {
                    const preview = document.getElementById('addCoverPreview');
                    const placeholder = document.getElementById('addCoverPlaceholder');
                    if (preview) {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'block';
                    }
                }

                if (form.id === 'editBookForm') {
                    const preview = document.getElementById('editCoverPreview');
                    const placeholder = document.getElementById('editCoverPlaceholder');
                    const coverInput = document.getElementById('editCover');
                    if (preview) {
                        preview.src = '';
                        preview.style.display = 'none';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'block';
                    }
                    if (coverInput) {
                        coverInput.value = '';
                    }
                }

                this.updateBookSubmitState(form);
            }

            seedBookFormValidation(form) {
                if (!form) {
                    return;
                }

                this.resetBookFormValidation(form);

                form.querySelectorAll('input, select, textarea').forEach(input => {
                    if (input.type !== 'file' && input.name !== 'isbn') {
                        input.value = this.normalizeBookFieldValue(input.name, input.value);
                    }

                    if (input.name === 'category_id' || input.name === 'new_category') {
                        this.validateCategoryState(form, { showErrors: false });
                    } else {
                        this.validateSingleBookField(form, input, { showErrors: false, runRemote: false });
                    }
                });

                form.querySelectorAll('.form-control').forEach(input => {
                    this.clearBookFieldVisualState(input);
                });

                this.updateBookSubmitState(form);
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new BookManager();
        });
    </script>
@endpush
