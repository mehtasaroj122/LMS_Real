<style>
    :root {
        --fine-text-primary: #0f172a;
        --fine-text-secondary: #64748b;
        --fine-bg-primary: #f9fafb;
        --fine-bg-secondary: #ffffff;
        --fine-border-color: #e5e7eb;
        --fine-surface-muted: #f8fafc;
        --fine-focus-color: #3b82f6;
        --fine-danger: #ef4444;
        --fine-success: #10b981;
        --fine-warning: #f59e0b;
        --fine-info: #2563eb;
    }

    body.dark-theme {
        --fine-text-primary: #f1f5f9;
        --fine-text-secondary: #94a3b8;
        --fine-bg-primary: #0f172a;
        --fine-bg-secondary: #1e293b;
        --fine-border-color: #334155;
        --fine-surface-muted: #0f172a;
    }

    .fine-page,
    .fine-page * {
        box-sizing: border-box;
    }

    .fine-page {
        font-family: "Inter", sans-serif;
        color: var(--fine-text-primary);
        min-height: 0;
    }

    .fine-page .dashboard-container {
        display: flex;
        min-height: 0;
    }

    .fine-page .main-content {
        flex: 1;
        min-width: 0;
        padding: 0;
        overflow: visible;
        min-height: 0;
    }

    .fine-page .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .fine-page .page-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

    .fine-page .page-title h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.125rem;
    }

    .fine-page .page-title p {
        color: var(--fine-text-secondary);
        font-size: 0.75rem;
    }

    .fine-page .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .fine-page .stat-card {
        position: relative;
        padding: 0.9rem 1rem;
        padding-right: 3.8rem;
        border-radius: 0.5rem;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        border: 1px solid var(--fine-border-color);
        background: var(--fine-bg-secondary);
        min-height: 102px;
    }

    .fine-page .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    body.dark-theme .fine-page .stat-card:hover {
        box-shadow: 0 10px 24px rgba(2, 6, 23, 0.35);
    }

    .fine-page .stat-header {
        display: block;
        margin-bottom: 0.15rem;
    }

    .fine-page .stat-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fine-text-secondary);
    }

    .fine-page .stat-icon {
        position: absolute;
        top: 0.9rem;
        right: 1rem;
        width: 1.8rem;
        height: 1.8rem;
        flex-shrink: 0;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.25s ease;
    }

    .fine-page .stat-card:hover .stat-icon {
        transform: scale(1.05) rotate(4deg);
    }

    body.light-theme .fine-page .stat-card.total-fines .stat-icon {
        background-color: #dbeafe;
        color: #2563eb;
    }

    body.light-theme .fine-page .stat-card.collected .stat-icon {
        background-color: #fef3c7;
        color: #f59e0b;
    }

    body.light-theme .fine-page .stat-card.pending .stat-icon {
        background-color: #fee2e2;
        color: #ef4444;
    }

    body.light-theme .fine-page .stat-card.waived .stat-icon {
        background-color: #dcfce7;
        color: #10b981;
    }

    body.dark-theme .fine-page .stat-card.total-fines .stat-icon {
        background-color: #1e3a8a;
        color: #93c5fd;
    }

    body.dark-theme .fine-page .stat-card.collected .stat-icon {
        background-color: #78350f;
        color: #fcd34d;
    }

    body.dark-theme .fine-page .stat-card.pending .stat-icon {
        background-color: #7f1d1d;
        color: #fca5a5;
    }

    body.dark-theme .fine-page .stat-card.waived .stat-icon {
        background-color: #064e3b;
        color: #6ee7b7;
    }

    .fine-page .stat-number {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.2rem;
        line-height: 1.2;
    }

    .fine-page .stat-label {
        font-size: 0.75rem;
        color: var(--fine-text-secondary);
        line-height: 1.45;
    }

    .fine-page .loading-line {
        position: relative;
        display: inline-flex;
        min-width: 4.5rem;
    }

    .fine-page .stat-card.is-loading .loading-line,
    .fine-page .stat-card.is-loading .stat-label {
        color: transparent !important;
    }

    .fine-page .stat-card.is-loading .loading-line::after,
    .fine-page .stat-card.is-loading .stat-label::after,
    .fine-page .table-skeleton-line::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 0.35rem;
        background: linear-gradient(90deg, rgba(148, 163, 184, 0.18), rgba(226, 232, 240, 0.45), rgba(148, 163, 184, 0.18));
        background-size: 200% 100%;
        animation: fineShimmer 1.3s linear infinite;
    }

    .fine-page .stat-card.is-loading .stat-label {
        position: relative;
        display: inline-flex;
        min-width: 7rem;
    }

    .fine-page .stat-card.is-loading .stat-label::after {
        inset: 2px 0;
    }

    @keyframes fineShimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .fine-page .search-filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding: 1rem;
        border-radius: 0.5rem;
        align-items: flex-start;
        background: var(--fine-bg-secondary);
        border: 1px solid var(--fine-border-color);
    }

    .fine-page .search-box {
        flex: 1 1 260px;
        min-width: 220px;
        max-width: 320px;
        position: relative;
    }

    .fine-page .search-input,
    .fine-page .filter-select,
    .fine-page .filter-input,
    .fine-page .filter-date {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid var(--fine-border-color);
        background-color: var(--fine-surface-muted);
        color: var(--fine-text-primary);
        font-size: 0.75rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .fine-page .search-input {
        padding-left: 2.25rem;
    }

    .fine-page .search-input:focus,
    .fine-page .filter-select:focus,
    .fine-page .filter-input:focus,
    .fine-page .filter-date:focus,
    .fine-page .modal-textarea:focus,
    .fine-page .action-btn:focus-visible,
    .fine-page .btn-email:focus-visible,
    .fine-page .pagination-btn:focus-visible,
    .fine-page .reset-btn:focus-visible,
    .fine-page .toolbar-btn:focus-visible,
    .fine-page .modal-btn:focus-visible,
    .fine-page .modal-close-btn:focus-visible,
    .fine-page .toast-close:focus-visible {
        outline: none;
        border-color: var(--fine-focus-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .fine-page .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--fine-text-secondary);
        pointer-events: none;
    }

    .fine-page .filters-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        flex: 1 1 420px;
    }

    .fine-page .filter-field {
        min-width: 130px;
        flex: 1 1 130px;
    }

    .fine-page .filter-select {
        padding-right: 2rem;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.55rem center;
    }

    body.dark-theme .fine-page .filter-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    }

    .fine-page .filter-input[type="number"] {
        appearance: textfield;
    }

    .fine-page .filter-input[type="number"]::-webkit-outer-spin-button,
    .fine-page .filter-input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .fine-page .is-invalid {
        border-color: var(--fine-danger) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
    }

    .fine-page .reset-btn,
    .fine-page .toolbar-btn,
    .fine-page .modal-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        border: 1px solid var(--fine-border-color);
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
        background: var(--fine-surface-muted);
        color: var(--fine-text-primary);
    }

    .fine-page .reset-btn:hover,
    .fine-page .toolbar-btn:hover,
    .fine-page .modal-btn:hover {
        border-color: var(--fine-focus-color);
        color: var(--fine-focus-color);
    }

    .fine-page .toolbar-btn.primary,
    .fine-page .modal-btn.primary {
        color: #ffffff;
        border-color: transparent;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.2);
    }

    .fine-page .toolbar-btn.primary:hover,
    .fine-page .modal-btn.primary:hover {
        color: #ffffff;
        border-color: transparent;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .fine-page .toolbar-btn svg,
    .fine-page .reset-btn svg,
    .fine-page .modal-btn svg {
        width: 0.95rem;
        height: 0.95rem;
        vertical-align: -2px;
    }

    .fine-page .toolbar-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        flex: 1 1 100%;
        margin-top: 0.15rem;
        padding-top: 0.25rem;
        font-size: 0.72rem;
        color: var(--fine-text-secondary);
        flex-wrap: wrap;
    }

    .fine-page .toolbar-meta strong {
        color: var(--fine-text-primary);
        font-weight: 600;
    }

    .fine-page .fine-entries-control {
        align-self: center;
    }

    .fine-page .fines-table-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--fine-border-color);
        margin-bottom: 1rem;
        transition: background-color 0.25s ease, border-color 0.25s ease;
        background-color: var(--fine-bg-secondary);
    }

    .fine-page .fines-table-wrapper {
        overflow-x: auto;
    }

    .fine-page .fines-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1080px;
        table-layout: fixed;
    }

    .fine-page .fines-table thead {
        border-bottom: 1px solid var(--fine-border-color);
    }

    .fine-page .fines-table th {
        padding: 6px 8px;
        font-weight: 600;
        font-size: 11px;
        border-bottom: 1px solid var(--fine-border-color);
        white-space: nowrap;
        text-align: start;
        color: #475569;
        background-color: var(--fine-surface-muted);
    }

    body.dark-theme .fine-page .fines-table th {
        color: #cbd5e1;
    }

    .fine-page .fines-table td {
        padding: 6px 8px;
        border-bottom: 1px solid var(--fine-border-color);
        vertical-align: middle;
        font-size: 13px;
        color: var(--fine-text-primary);
    }

    .fine-page .fines-table tbody tr:last-child td {
        border-bottom: none;
    }

    .fine-page .fines-table tbody tr:hover {
        background-color: rgba(148, 163, 184, 0.08);
    }

    body.dark-theme .fine-page .fines-table tbody tr:hover {
        background-color: rgba(51, 65, 85, 0.65);
    }

    .fine-page .fines-table tbody tr.is-overdue {
        background-image: linear-gradient(90deg, rgba(239, 68, 68, 0.08), transparent 14%);
    }

    body.dark-theme .fine-page .fines-table tbody tr.is-overdue {
        background-image: linear-gradient(90deg, rgba(248, 113, 113, 0.12), transparent 14%);
    }

    .fine-page .fines-table th:nth-child(1),
    .fine-page .fines-table td:nth-child(1) {
        width: 18%;
        max-width: 18%;
    }

    .fine-page .fines-table th:nth-child(2),
    .fine-page .fines-table td:nth-child(2) {
        width: 22%;
        max-width: 22%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fine-page .fines-table th:nth-child(3),
    .fine-page .fines-table td:nth-child(3),
    .fine-page .fines-table th:nth-child(4),
    .fine-page .fines-table td:nth-child(4),
    .fine-page .fines-table th:nth-child(5),
    .fine-page .fines-table td:nth-child(5),
    .fine-page .fines-table th:nth-child(6),
    .fine-page .fines-table td:nth-child(6) {
        width: 10%;
        max-width: 10%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fine-page .fines-table th:nth-child(7),
    .fine-page .fines-table td:nth-child(7) {
        width: 20%;
        max-width: 20%;
    }

    .fine-page .student-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .fine-page .student-avatar {
        width: 36px;
        height: 36px;
        min-width: 36px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.875rem;
        overflow: hidden;
        color: #ffffff;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        flex-shrink: 0;
        text-transform: uppercase;
    }

    .fine-page .student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .fine-page .student-details {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .fine-page .student-name {
        font-weight: 600;
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fine-page .student-roll {
        font-size: 0.75rem;
        font-weight: 500;
        line-height: 1.4;
        color: var(--fine-text-secondary);
    }

    .fine-page .text-muted {
        color: var(--fine-text-secondary);
    }

    .fine-page .due-date-cell {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .fine-page .row-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        width: fit-content;
        padding: 0.18rem 0.45rem;
        border-radius: 9999px;
    }

    .fine-page .row-indicator.overdue {
        background: #fee2e2;
        color: #dc2626;
    }

    body.dark-theme .fine-page .row-indicator.overdue {
        background: #7f1d1d;
        color: #fecaca;
    }

    .fine-page .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        gap: 4px;
    }

    .fine-page .status-badge svg {
        width: 12px;
        height: 12px;
    }

    .fine-page .status-pending {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .fine-page .status-paid {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .fine-page .status-waived {
        background-color: #dbeafe;
        color: #2563eb;
    }

    body.dark-theme .fine-page .status-pending {
        background-color: #7f1d1d;
        color: #fca5a5;
    }

    body.dark-theme .fine-page .status-paid {
        background-color: #14532d;
        color: #86efac;
    }

    body.dark-theme .fine-page .status-waived {
        background-color: #1e3a8a;
        color: #93c5fd;
    }

    .fine-page .fine-amount {
        font-weight: 600;
        font-size: 13px;
    }

    .fine-page .fine-amount.pending {
        color: #dc2626;
    }

    .fine-page .fine-amount.paid {
        color: #16a34a;
    }

    .fine-page .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        align-items: center;
    }

    .fine-page .action-btn,
    .fine-page .btn-email {
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        white-space: nowrap;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, opacity 0.2s ease;
    }

    .fine-page .action-btn {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        color: #ffffff;
    }

    .fine-page .fine-actions-cell {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: flex-start;
        white-space: nowrap;
    }

    .fine-page .action-btn:hover,
    .fine-page .btn-email:hover {
        transform: translateY(-1px);
    }

    .fine-page .action-btn:disabled,
    .fine-page .btn-email:disabled,
    .fine-page .pagination-btn:disabled,
    .fine-page .toolbar-btn:disabled,
    .fine-page .modal-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .fine-page .btn-paid {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
    }

    .fine-page .btn-paid:hover {
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
    }

    .fine-page .btn-waive {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2);
    }

    .fine-page .btn-waive:hover {
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
    }

    .fine-page .btn-email {
        width: 30px;
        height: 30px;
        padding: 0;
        border-radius: 6px;
        color: var(--fine-text-secondary);
        background-color: rgba(148, 163, 184, 0.16);
    }

    .fine-page .btn-email:hover {
        color: #f97316;
        background: #ffedd5;
    }

    body.dark-theme .fine-page .btn-email {
        background-color: #334155;
    }

    body.dark-theme .fine-page .btn-email:hover {
        background: #9a3412;
        color: #fdba74;
    }

    .fine-page .table-skeleton-line {
        position: relative;
        display: inline-flex;
        width: 100%;
        min-height: 0.95rem;
        color: transparent;
    }

    .fine-page .table-skeleton-line.short {
        width: 60%;
    }

    .fine-page .empty-state {
        text-align: center;
        padding: 2.5rem 1rem;
        color: var(--fine-text-secondary);
        display: none;
    }

    .fine-page .empty-state.is-visible {
        display: block;
    }

    .fine-page .empty-state-icon {
        margin-bottom: 0.75rem;
        opacity: 0.5;
        color: var(--fine-text-secondary);
    }

    .fine-page .empty-state h3 {
        margin-bottom: 0.25rem;
        font-size: 1rem;
        font-weight: 600;
        color: var(--fine-text-primary);
    }

    .fine-page .empty-state-actions {
        margin-top: 1rem;
    }

    .fine-page .pagination-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 0 0;
        border-top: 1px solid var(--fine-border-color);
    }

    .fine-page .pagination-meta {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
        min-width: 0;
    }

    .fine-page .pagination-info {
        font-size: 0.78rem;
        line-height: 1.5;
        color: var(--fine-text-secondary);
    }

    .fine-page .pagination-page {
        font-size: 0.78rem;
        line-height: 1.5;
        font-weight: 600;
        color: var(--fine-text-primary);
    }

    .fine-page .pagination-buttons {
        display: flex;
        gap: 0.38rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-left: auto;
    }

    .fine-page .pagination-btn {
        padding: 0.72rem 0.88rem;
        border: 1px solid var(--fine-border-color);
        border-radius: 0.9rem;
        background: #f8fafc;
        color: #334155;
        cursor: pointer;
        transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        font-size: 0.82rem;
        font-weight: 600;
        min-width: 2.5rem;
        min-height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
        line-height: 1;
    }

    body.dark-theme .fine-page .pagination-btn {
        border-color: #475569;
        background: #0f172a;
        color: #e2e8f0;
    }

    .fine-page .pagination-btn:hover:not(:disabled) {
        border-color: #3b82f6;
        color: #2563eb;
        background: #eff6ff;
        transform: translateY(-1px);
    }

    body.dark-theme .fine-page .pagination-btn:hover:not(:disabled) {
        border-color: #60a5fa;
        color: #bfdbfe;
        background: #1e3a8a;
    }

    .fine-page .pagination-btn.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
        transform: translateY(-1px);
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.18);
    }

    body.dark-theme .fine-page .pagination-btn.active {
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.24);
    }

    .fine-page .pagination-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        min-height: 2.5rem;
        padding: 0.72rem 0.25rem;
        color: var(--fine-text-secondary);
        font-size: 0.82rem;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .fine-page .pagination-buttons {
            margin-left: 0;
            justify-content: flex-start;
        }
    }

    .fine-page .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 60;
        display: none;
        align-items: center;
        justify-content: center;
        background-color: rgba(15, 23, 42, 0.58);
        padding: 1rem;
    }

    .fine-page .modal-backdrop.is-open {
        display: flex;
    }

    .fine-page .modal-panel {
        width: 100%;
        max-width: 28rem;
        border-radius: 0.85rem;
        border: 1px solid var(--fine-border-color);
        background: var(--fine-bg-secondary);
        color: var(--fine-text-primary);
        box-shadow: 0 22px 52px rgba(15, 23, 42, 0.18);
    }

    body.dark-theme .fine-page .modal-panel {
        box-shadow: 0 22px 52px rgba(2, 6, 23, 0.45);
    }

    .fine-page .modal-header,
    .fine-page .modal-body,
    .fine-page .modal-footer {
        padding: 1.25rem 1.35rem;
    }

    .fine-page .modal-header {
        border-bottom: 1px solid var(--fine-border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }

    .fine-page .modal-header h3 {
        font-size: 1.05rem;
        font-weight: 600;
    }

    .fine-page .modal-close-btn {
        width: 2rem;
        height: 2rem;
        border: none;
        background: transparent;
        color: var(--fine-text-secondary);
        cursor: pointer;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .fine-page .modal-close-btn:hover {
        background: rgba(148, 163, 184, 0.12);
        color: var(--fine-text-primary);
    }

    .fine-page .modal-description {
        color: var(--fine-text-secondary);
        font-size: 0.84rem;
        line-height: 1.55;
        margin-bottom: 0.75rem;
    }

    .fine-page .modal-textarea {
        width: 100%;
        min-height: 120px;
        resize: vertical;
        padding: 0.85rem 0.95rem;
        border-radius: 0.7rem;
        border: 1px solid var(--fine-border-color);
        background: var(--fine-surface-muted);
        color: var(--fine-text-primary);
        font-size: 0.84rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .fine-page .modal-error {
        margin-top: 0.55rem;
        color: #dc2626;
        font-size: 0.72rem;
        font-weight: 500;
    }

    .fine-page .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        border-top: 1px solid var(--fine-border-color);
    }

    .fine-page .action-popup-icon {
        width: 4rem;
        height: 4rem;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        color: #fff;
    }

    .fine-page .action-popup-icon svg {
        width: 1.9rem;
        height: 1.9rem;
    }

    .fine-page .action-popup-icon.paid {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.25);
    }

    .fine-page .action-popup-icon.email {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        box-shadow: 0 10px 30px rgba(249, 115, 22, 0.25);
    }

    .fine-page .action-popup-detail {
        margin-top: 0.75rem;
        padding: 0.875rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.8rem;
        line-height: 1.5;
        background: var(--fine-surface-muted);
        border: 1px solid var(--fine-border-color);
        color: var(--fine-text-primary);
    }

    .fine-page .toast-container {
        position: fixed;
        top: 1.15rem;
        right: 1.15rem;
        z-index: 80;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: min(360px, calc(100vw - 2rem));
        pointer-events: none;
    }

    .fine-page .toast {
        pointer-events: auto;
        position: relative;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 0.75rem;
        align-items: start;
        padding: 0.95rem 1rem 1rem;
        border-radius: 0.9rem;
        border: 1px solid var(--fine-border-color);
        background: var(--fine-bg-secondary);
        color: var(--fine-text-primary);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.14);
        overflow: hidden;
        transform: translateY(-8px);
        opacity: 0;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    body.dark-theme .fine-page .toast {
        box-shadow: 0 14px 32px rgba(2, 6, 23, 0.42);
    }

    .fine-page .toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .fine-page .toast-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        flex-shrink: 0;
    }

    .fine-page .toast-success .toast-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .fine-page .toast-error .toast-icon {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .fine-page .toast-info .toast-icon {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .fine-page .toast-title {
        font-size: 0.84rem;
        font-weight: 700;
        margin-bottom: 0.12rem;
    }

    .fine-page .toast-message {
        font-size: 0.78rem;
        line-height: 1.45;
        color: var(--fine-text-secondary);
    }

    .fine-page .toast-close {
        border: none;
        background: transparent;
        color: var(--fine-text-secondary);
        cursor: pointer;
        padding: 0;
        width: 1.1rem;
        height: 1.1rem;
    }

    .fine-page .toast-progress {
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 100%;
        transform-origin: left;
        animation: fineToastProgress linear forwards;
    }

    .fine-page .toast-success .toast-progress {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .fine-page .toast-error .toast-progress {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }

    .fine-page .toast-info .toast-progress {
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
    }

    @keyframes fineToastProgress {
        from {
            transform: scaleX(1);
        }

        to {
            transform: scaleX(0);
        }
    }

    .fine-page .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid rgba(255, 255, 255, 0.45);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: fineSpin 0.8s linear infinite;
    }

    @keyframes fineSpin {
        to {
            transform: rotate(360deg);
        }
    }

    .fine-page .sr-only {
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

    @media (max-width: 1024px) {
        .fine-page .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .fine-page .main-content {
            padding: 0;
        }

        .fine-page .page-header,
        .fine-page .pagination-container,
        .fine-page .toolbar-meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .fine-page .stats-grid {
            grid-template-columns: 1fr;
        }

        .fine-page .search-filter-container {
            padding: 0.75rem;
            gap: 0.6rem;
        }

        .fine-page .search-box,
        .fine-page .filters-container,
        .fine-page .filter-field {
            width: 100%;
            max-width: 100%;
            flex-basis: 100%;
        }

        .fine-page .fines-table {
            min-width: 1040px;
        }

        .fine-page .action-buttons {
            flex-direction: column;
            align-items: stretch;
            width: 100%;
        }

        .fine-page .action-btn {
            width: 100%;
        }
    }
</style>
