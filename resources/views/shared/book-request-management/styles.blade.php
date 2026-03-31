<style>
    :root {
        --request-text-primary: #0f172a;
        --request-text-secondary: #64748b;
        --request-bg-primary: #f8fafc;
        --request-bg-secondary: #ffffff;
        --request-surface-muted: #f8fafc;
        --request-border-color: #e2e8f0;
        --request-focus-color: #3b82f6;
        --request-danger: #ef4444;
        --request-success: #16a34a;
        --request-warning: #f59e0b;
        --request-info: #2563eb;
    }

    body.dark-theme {
        --request-text-primary: #f1f5f9;
        --request-text-secondary: #94a3b8;
        --request-bg-primary: #0f172a;
        --request-bg-secondary: #1e293b;
        --request-surface-muted: #111827;
        --request-border-color: #334155;
    }

    .book-request-page,
    .book-request-page * {
        box-sizing: border-box;
    }

    .book-request-page {
        color: var(--request-text-primary);
        font-family: "Inter", sans-serif;
    }

    .book-request-page .request-content {
        padding: 0;
    }

    .book-request-page .request-page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .book-request-page .request-page-title h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--request-text-primary);
    }

    .book-request-page .request-page-title p {
        margin: 0.2rem 0 0;
        font-size: 0.76rem;
        line-height: 1.5;
        color: var(--request-text-secondary);
    }
    .book-request-page .request-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .book-request-page .request-stat-card {
        position: relative;
        min-height: 102px;
        padding: 0.9rem 1rem;
        padding-right: 4.5rem;
        border: 1px solid var(--request-border-color);
        border-radius: 1rem;
        background: var(--request-bg-secondary);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .book-request-page .request-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }

    body.dark-theme .book-request-page .request-stat-card:hover {
        box-shadow: 0 12px 28px rgba(2, 6, 23, 0.35);
        border-color: #475569;
    }

    .book-request-page .request-stat-header {
        display: block;
        margin-bottom: 0.15rem;
    }

    .book-request-page .request-stat-title {
        margin: 0;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--request-text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .book-request-page .request-stat-icon {
        position: absolute;
        top: 1rem;
        right: 1rem;
        width: 2.4rem;
        height: 2.4rem;
        flex-shrink: 0;
        border-radius: 0.9rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.25s ease;
    }

    .book-request-page .request-stat-card:hover .request-stat-icon {
        transform: scale(1.05);
    }

    body.light-theme .book-request-page .request-stat-card.pending .request-stat-icon {
        background: #fef3c7;
        color: #d97706;
    }

    body.light-theme .book-request-page .request-stat-card.approved .request-stat-icon {
        background: #dcfce7;
        color: #15803d;
    }

    body.light-theme .book-request-page .request-stat-card.rejected .request-stat-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    body.dark-theme .book-request-page .request-stat-card.pending .request-stat-icon {
        background: #78350f;
        color: #fcd34d;
    }

    body.dark-theme .book-request-page .request-stat-card.approved .request-stat-icon {
        background: #14532d;
        color: #6ee7b7;
    }

    body.dark-theme .book-request-page .request-stat-card.rejected .request-stat-icon {
        background: #7f1d1d;
        color: #fca5a5;
    }

    .book-request-page .request-stat-value {
        margin: 0 0 0.2rem;
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .book-request-page .request-stat-meta {
        font-size: 0.76rem;
        line-height: 1.45;
        color: var(--request-text-secondary);
    }

    .book-request-page .loading-line {
        position: relative;
        display: inline-flex;
        min-width: 3.5rem;
    }

    .book-request-page .request-stat-card.is-loading .loading-line {
        color: transparent !important;
    }

    .book-request-page .request-stat-card.is-loading .loading-line::after,
    .book-request-page .request-stat-card.is-loading .request-stat-meta::after,
    .book-request-page .request-skeleton-line::after {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 0.4rem;
        background: linear-gradient(90deg, rgba(148, 163, 184, 0.18), rgba(226, 232, 240, 0.45), rgba(148, 163, 184, 0.18));
        background-size: 200% 100%;
        animation: requestShimmer 1.2s linear infinite;
    }

    .book-request-page .request-stat-card.is-loading .request-stat-meta {
        position: relative;
        display: inline-flex;
        min-width: 8rem;
        color: transparent !important;
    }

    @keyframes requestShimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    .book-request-page .request-section-header {
        margin-bottom: 0.5rem;
    }

    .book-request-page .request-section-header h2 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: var(--request-text-primary);
    }

    .book-request-page .request-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        padding: 1rem;
        border-radius: 0.9rem;
        border: 1px solid var(--request-border-color);
        background: var(--request-bg-secondary);
    }

    .book-request-page .request-search-box {
        position: relative;
        flex: 0 0 auto;
        width: 310px;
        min-width: 240px;
        max-width: 360px;
    }

    .book-request-page .request-search-icon {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--request-text-secondary);
        pointer-events: none;
    }

    .book-request-page .request-search-input,
    .book-request-page .request-filter-select,
    .book-request-page .request-modal-input,
    .book-request-page .request-selectbox-input {
        width: 100%;
        min-height: 40px;
        padding: 0.55rem 0.75rem;
        border-radius: 0.75rem;
        border: 1px solid var(--request-border-color);
        background: var(--request-surface-muted);
        color: var(--request-text-primary);
        font-size: 0.78rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .book-request-page .request-search-input {
        padding-left: 2.35rem;
    }

    .book-request-page .request-search-input:focus,
    .book-request-page .request-filter-select:focus,
    .book-request-page .request-modal-input:focus,
    .book-request-page .request-selectbox-input:focus,
    .book-request-page .request-btn:focus-visible,
    .book-request-page .request-action-btn:focus-visible,
    .book-request-page .request-pagination-btn:focus-visible,
    .book-request-page .request-modal-close:focus-visible,
    .book-request-page .request-toast-close:focus-visible,
    .book-request-page .request-selectbox-option:focus-visible {
        outline: none;
        border-color: var(--request-focus-color);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .book-request-page .request-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        flex: 0 0 auto;
    }

    .book-request-page .request-filter-field {
        width: 190px;
        min-width: 190px;
        flex: 0 0 auto;
    }

    .book-request-page .request-filter-select {
        appearance: none;
        padding-right: 2rem;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
    }

    body.dark-theme .book-request-page .request-filter-select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    }

    .book-request-page .request-toolbar-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-left: auto;
    }
    .book-request-page .request-btn,
    .book-request-page .request-action-btn,
    .book-request-page .request-modal-btn,
    .book-request-page .request-pagination-btn,
    .book-request-page .request-selectbox-option {
        font: inherit;
    }

    .book-request-page .request-btn,
    .book-request-page .request-modal-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        min-height: 40px;
        padding: 0.55rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid var(--request-border-color);
        background: var(--request-surface-muted);
        color: var(--request-text-primary);
        font-size: 0.76rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        white-space: nowrap;
    }

    .book-request-page .request-btn:hover,
    .book-request-page .request-modal-btn:hover {
        border-color: var(--request-focus-color);
        color: var(--request-focus-color);
    }

    .book-request-page .request-btn.primary,
    .book-request-page .request-modal-btn.primary {
        border-color: transparent;
        color: #ffffff;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
    }

    .book-request-page .request-btn.primary:hover,
    .book-request-page .request-modal-btn.primary:hover {
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .book-request-page .request-btn svg,
    .book-request-page .request-action-btn svg,
    .book-request-page .request-modal-btn svg {
        width: 0.95rem;
        height: 0.95rem;
        flex-shrink: 0;
    }

    .book-request-page .request-toolbar-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.9rem;
        padding: 0 0.1rem;
        font-size: 0.75rem;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-toolbar-meta strong {
        color: var(--request-text-primary);
    }

    .book-request-page .request-table-shell {
        position: relative;
    }

    .book-request-page .request-table-container {
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid var(--request-border-color);
        background: var(--request-bg-secondary);
    }

    .book-request-page .request-table-wrapper {
        overflow-x: auto;
    }

    .book-request-page .requests-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .book-request-page .requests-table th,
    .book-request-page .requests-table td {
        padding: 0.8rem 0.9rem;
        text-align: left;
        vertical-align: middle;
        border-bottom: 1px solid var(--request-border-color);
    }

    .book-request-page .requests-table th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--request-text-secondary);
        background: color-mix(in srgb, var(--request-surface-muted) 85%, transparent);
    }

    .book-request-page .requests-table td {
        font-size: 0.81rem;
        color: var(--request-text-primary);
    }

    .book-request-page .requests-table tbody tr:last-child td {
        border-bottom: none;
    }

    .book-request-page .requests-table tbody tr:hover {
        background: rgba(59, 130, 246, 0.035);
    }

    body.dark-theme .book-request-page .requests-table tbody tr:hover {
        background: rgba(96, 165, 250, 0.08);
    }

    .book-request-page .request-student-cell {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-width: 0;
    }

    .book-request-page .request-student-avatar {
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
        font-size: 0.82rem;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .book-request-page .request-student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .book-request-page .request-student-details,
    .book-request-page .request-book-details {
        min-width: 0;
    }

    .book-request-page .request-primary-text {
        display: block;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .book-request-page .request-secondary-text {
        display: block;
        margin-top: 0.12rem;
        font-size: 0.74rem;
        color: var(--request-text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .book-request-page .request-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        white-space: nowrap;
    }

    .book-request-page .request-status-badge svg {
        width: 0.8rem;
        height: 0.8rem;
    }

    body.light-theme .book-request-page .request-status-badge.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    body.light-theme .book-request-page .request-status-badge.approved {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
    }

    body.light-theme .book-request-page .request-status-badge.rejected {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    body.dark-theme .book-request-page .request-status-badge.pending {
        background: linear-gradient(135deg, #78350f 0%, #451a03 100%);
        color: #fbbf24;
    }

    body.dark-theme .book-request-page .request-status-badge.approved {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        color: #4ade80;
    }

    body.dark-theme .book-request-page .request-status-badge.rejected {
        background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
        color: #f87171;
    }

    .book-request-page .request-action-group {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        flex-wrap: wrap;
    }

    .book-request-page .request-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        min-height: 34px;
        padding: 0.45rem 0.8rem;
        border-radius: 0.65rem;
        border: none;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        white-space: nowrap;
    }

    .book-request-page .request-action-btn:hover {
        transform: translateY(-1px);
    }

    body.light-theme .book-request-page .request-action-btn.accept {
        background: #dcfce7;
        color: #166534;
    }

    body.light-theme .book-request-page .request-action-btn.accept:hover {
        background: #bbf7d0;
    }

    body.light-theme .book-request-page .request-action-btn.reject {
        background: #fee2e2;
        color: #991b1b;
    }

    body.light-theme .book-request-page .request-action-btn.reject:hover {
        background: #fecaca;
    }

    body.dark-theme .book-request-page .request-action-btn.accept {
        background: #14532d;
        color: #86efac;
    }

    body.dark-theme .book-request-page .request-action-btn.accept:hover {
        background: #166534;
    }

    body.dark-theme .book-request-page .request-action-btn.reject {
        background: #7f1d1d;
        color: #fca5a5;
    }

    body.dark-theme .book-request-page .request-action-btn.reject:hover {
        background: #991b1b;
    }

    .book-request-page .request-action-status {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--request-text-secondary);
        font-size: 0.76rem;
        font-weight: 600;
    }

    .book-request-page .request-action-status svg {
        width: 0.9rem;
        height: 0.9rem;
    }
    .book-request-page .request-skeleton-line {
        position: relative;
        display: inline-flex;
        width: 100%;
        min-height: 0.85rem;
        border-radius: 0.4rem;
        overflow: hidden;
    }

    .book-request-page .request-skeleton-line.short {
        width: 72%;
    }

    .book-request-page .request-empty-state {
        display: none;
        text-align: center;
        padding: 3rem 1.5rem;
        border: 1px dashed var(--request-border-color);
        border-radius: 1rem;
        background: color-mix(in srgb, var(--request-bg-secondary) 84%, transparent);
        margin-top: 0.75rem;
    }

    .book-request-page .request-empty-state.is-visible {
        display: block;
    }

    .book-request-page .request-empty-icon {
        width: 3.3rem;
        height: 3.3rem;
        margin: 0 auto 1rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--request-text-secondary);
        background: rgba(148, 163, 184, 0.12);
    }

    .book-request-page .request-empty-state h3 {
        margin: 0 0 0.35rem;
        font-size: 1rem;
        font-weight: 700;
        color: var(--request-text-primary);
    }

    .book-request-page .request-empty-state p {
        margin: 0 auto;
        max-width: 420px;
        font-size: 0.8rem;
        line-height: 1.6;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-empty-actions {
        display: flex;
        justify-content: center;
        gap: 0.6rem;
        margin-top: 1rem;
    }

    .book-request-page .request-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 0.9rem;
        padding: 0.1rem 0.1rem 0;
    }

    .book-request-page .request-pagination-info {
        font-size: 0.75rem;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-pagination-buttons {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .book-request-page .request-pagination-btn,
    .book-request-page .request-pagination-ellipsis {
        min-width: 2.25rem;
        height: 2.25rem;
        padding: 0 0.7rem;
        border-radius: 0.7rem;
        border: 1px solid var(--request-border-color);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--request-bg-secondary);
        color: var(--request-text-primary);
        font-size: 0.76rem;
        font-weight: 600;
    }

    .book-request-page .request-pagination-btn {
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .book-request-page .request-pagination-btn:hover:not(:disabled) {
        border-color: var(--request-focus-color);
        color: var(--request-focus-color);
    }

    .book-request-page .request-pagination-btn.is-active {
        border-color: transparent;
        color: #ffffff;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .book-request-page .request-pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.45;
    }

    .book-request-page .request-pagination-btn svg {
        width: 0.95rem;
        height: 0.95rem;
    }

    .book-request-page .request-modal-backdrop {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.55);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        z-index: 70;
    }

    .book-request-page .request-modal-backdrop.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .book-request-page .request-modal-panel {
        width: min(100%, 580px);
        border-radius: 1rem;
        border: 1px solid var(--request-border-color);
        background: var(--request-bg-secondary);
        box-shadow: 0 25px 65px rgba(15, 23, 42, 0.22);
        overflow: hidden;
        transform: translateY(12px);
        transition: transform 0.2s ease;
    }

    body.dark-theme .book-request-page .request-modal-panel {
        box-shadow: 0 25px 70px rgba(2, 6, 23, 0.5);
    }

    .book-request-page .request-modal-backdrop.is-open .request-modal-panel {
        transform: translateY(0);
    }

    .book-request-page .request-modal-panel.compact {
        width: min(100%, 430px);
    }

    .book-request-page .request-modal-header,
    .book-request-page .request-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.2rem;
        border-bottom: 1px solid var(--request-border-color);
    }

    .book-request-page .request-modal-footer {
        justify-content: flex-end;
        border-top: 1px solid var(--request-border-color);
        border-bottom: none;
    }

    .book-request-page .request-modal-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--request-text-primary);
    }

    .book-request-page .request-modal-subtitle,
    .book-request-page .request-modal-description,
    .book-request-page .request-modal-note {
        margin: 0.2rem 0 0;
        font-size: 0.76rem;
        line-height: 1.55;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-modal-body {
        padding: 1.2rem;
    }

    .book-request-page .request-modal-close {
        width: 2rem;
        height: 2rem;
        border: 1px solid transparent;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        color: var(--request-text-secondary);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .book-request-page .request-modal-close:hover {
        color: var(--request-text-primary);
        background: rgba(148, 163, 184, 0.12);
    }
    .book-request-page .request-form-grid {
        display: grid;
        gap: 1rem;
    }

    .book-request-page .request-form-field {
        display: grid;
        gap: 0.4rem;
    }

    .book-request-page .request-label {
        font-size: 0.76rem;
        font-weight: 600;
        color: var(--request-text-primary);
    }

    .book-request-page .request-required {
        color: var(--request-danger);
    }

    .book-request-page .request-field-error {
        font-size: 0.72rem;
        color: var(--request-danger);
    }

    .book-request-page .request-field-error[hidden] {
        display: none !important;
    }

    .book-request-page .request-selectbox {
        position: relative;
    }

    .book-request-page .request-selectbox-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--request-text-secondary);
        pointer-events: none;
    }

    .book-request-page .request-selectbox-input {
        padding-left: 2.5rem;
        background: var(--request-bg-secondary);
    }

    .book-request-page .request-selectbox-input.is-invalid {
        border-color: var(--request-danger) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
    }

    .book-request-page .request-selectbox-dropdown {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 0.4rem);
        max-height: 250px;
        overflow-y: auto;
        padding: 0.4rem;
        border-radius: 0.9rem;
        border: 1px solid var(--request-border-color);
        background: var(--request-bg-secondary);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.15);
        display: none;
        z-index: 3;
    }

    body.dark-theme .book-request-page .request-selectbox-dropdown {
        box-shadow: 0 18px 42px rgba(2, 6, 23, 0.45);
    }

    .book-request-page .request-selectbox-dropdown.is-open {
        display: block;
    }

    .book-request-page .request-selectbox-option {
        width: 100%;
        padding: 0.7rem 0.75rem;
        border: 1px solid transparent;
        border-radius: 0.75rem;
        display: block;
        text-align: left;
        background: transparent;
        color: var(--request-text-primary);
        cursor: pointer;
        transition: background-color 0.18s ease, border-color 0.18s ease;
    }

    .book-request-page .request-selectbox-option[hidden],
    .book-request-page .request-selectbox-empty[hidden] {
        display: none !important;
    }

    .book-request-page .request-selectbox-option:hover,
    .book-request-page .request-selectbox-option.is-active {
        background: color-mix(in srgb, var(--request-surface-muted) 88%, transparent);
        border-color: var(--request-border-color);
    }

    .book-request-page .request-selectbox-option.is-selected {
        border-color: rgba(59, 130, 246, 0.35);
        background: rgba(59, 130, 246, 0.08);
    }

    .book-request-page .request-selectbox-empty {
        padding: 0.85rem 0.75rem;
        text-align: center;
        font-size: 0.76rem;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-selection-summary {
        display: none;
        padding: 0.85rem 0.95rem;
        border-radius: 0.9rem;
        border: 1px solid var(--request-border-color);
        background: color-mix(in srgb, var(--request-surface-muted) 92%, transparent);
    }

    .book-request-page .request-selection-summary.is-visible {
        display: block;
    }

    .book-request-page .request-selection-summary h4 {
        margin: 0 0 0.4rem;
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--request-text-primary);
    }

    .book-request-page .request-selection-summary p {
        margin: 0.2rem 0;
        font-size: 0.75rem;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-selection-summary strong {
        color: var(--request-text-primary);
    }

    .book-request-page .request-action-icon {
        width: 3.2rem;
        height: 3.2rem;
        margin: 0 auto 0.85rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .book-request-page .request-action-icon svg {
        width: 1.5rem;
        height: 1.5rem;
    }

    .book-request-page .request-action-icon.accept {
        background: #dcfce7;
        color: #15803d;
    }

    .book-request-page .request-action-icon.reject {
        background: #fee2e2;
        color: #dc2626;
    }

    body.dark-theme .book-request-page .request-action-icon.accept {
        background: rgba(34, 197, 94, 0.15);
        color: #4ade80;
    }

    body.dark-theme .book-request-page .request-action-icon.reject {
        background: rgba(248, 113, 113, 0.15);
        color: #f87171;
    }

    .book-request-page .request-action-detail {
        margin-top: 0.9rem;
        padding: 0.85rem;
        border-radius: 0.85rem;
        background: color-mix(in srgb, var(--request-surface-muted) 92%, transparent);
        font-size: 0.75rem;
        line-height: 1.6;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-toast-container {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 80;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
        width: min(100%, 360px);
    }

    .book-request-page .request-toast {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: start;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-radius: 0.95rem;
        border: 1px solid var(--request-border-color);
        background: var(--request-bg-secondary);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.14);
        opacity: 0;
        transform: translateY(-8px);
        transition: opacity 0.18s ease, transform 0.18s ease;
    }

    body.dark-theme .book-request-page .request-toast {
        box-shadow: 0 18px 48px rgba(2, 6, 23, 0.45);
    }

    .book-request-page .request-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .book-request-page .request-toast.success {
        border-color: rgba(16, 185, 129, 0.25);
    }

    .book-request-page .request-toast.error {
        border-color: rgba(239, 68, 68, 0.25);
    }

    .book-request-page .request-toast.info {
        border-color: rgba(37, 99, 235, 0.25);
    }

    .book-request-page .request-toast-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 0.05rem;
    }

    .book-request-page .request-toast.success .request-toast-icon {
        background: rgba(16, 185, 129, 0.12);
        color: var(--request-success);
    }

    .book-request-page .request-toast.error .request-toast-icon {
        background: rgba(239, 68, 68, 0.12);
        color: var(--request-danger);
    }

    .book-request-page .request-toast.info .request-toast-icon {
        background: rgba(37, 99, 235, 0.12);
        color: var(--request-info);
    }

    .book-request-page .request-toast-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--request-text-primary);
    }

    .book-request-page .request-toast-message {
        margin-top: 0.15rem;
        font-size: 0.74rem;
        line-height: 1.5;
        color: var(--request-text-secondary);
    }

    .book-request-page .request-toast-close {
        width: 1.8rem;
        height: 1.8rem;
        border: 1px solid transparent;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        color: var(--request-text-secondary);
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .book-request-page .request-toast-close:hover {
        background: rgba(148, 163, 184, 0.12);
        color: var(--request-text-primary);
    }

    .book-request-page .request-toast-progress {
        position: absolute;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 3px;
        background: currentColor;
        opacity: 0.18;
        transform-origin: left center;
        animation: requestToastProgress linear forwards;
    }

    @keyframes requestToastProgress {
        from {
            transform: scaleX(1);
        }

        to {
            transform: scaleX(0);
        }
    }
    .book-request-page .loading-spinner {
        width: 0.95rem;
        height: 0.95rem;
        border-radius: 9999px;
        border: 2px solid rgba(255, 255, 255, 0.35);
        border-top-color: currentColor;
        animation: requestSpin 0.7s linear infinite;
    }

    .book-request-page .request-btn:not(.primary) .loading-spinner,
    .book-request-page .request-modal-btn:not(.primary) .loading-spinner {
        border-color: rgba(59, 130, 246, 0.22);
        border-top-color: currentColor;
    }

    @keyframes requestSpin {
        to {
            transform: rotate(360deg);
        }
    }

    .book-request-page .request-btn:disabled,
    .book-request-page .request-action-btn:disabled,
    .book-request-page .request-modal-btn:disabled {
        cursor: not-allowed;
        opacity: 0.7;
        transform: none !important;
    }

    .book-request-page .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
        white-space: nowrap;
    }

    @media (max-width: 960px) {
        .book-request-page .request-toolbar {
            align-items: stretch;
        }

        .book-request-page .request-toolbar-actions {
            margin-left: 0;
            width: 100%;
            justify-content: flex-end;
        }
    }

    @media (max-width: 768px) {
        .book-request-page .request-content {
            padding: 0;
        }

        .book-request-page .request-page-header,
        .book-request-page .request-toolbar-meta,
        .book-request-page .request-pagination {
            flex-direction: column;
            align-items: flex-start;
        }

        .book-request-page .request-search-box,
        .book-request-page .request-filters,
        .book-request-page .request-filter-field,
        .book-request-page .request-toolbar-actions {
            width: 100%;
            max-width: none;
        }

        .book-request-page .request-btn,
        .book-request-page .request-modal-btn {
            justify-content: center;
        }

        .book-request-page .request-toast-container {
            left: 0.75rem;
            right: 0.75rem;
            width: auto;
        }
    }
</style>
