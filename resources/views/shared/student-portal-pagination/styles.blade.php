<style>
    .admin-table-entries-control {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        flex-wrap: nowrap;
        font-size: 0.78rem;
        font-weight: 500;
        color: #64748b;
        white-space: nowrap;
    }

    body.dark-theme .admin-table-entries-control {
        color: #94a3b8;
    }

    .admin-table-entries-select {
        min-width: 86px;
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border-radius: 0.65rem;
        border: 1px solid #dbe2ea;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.7rem center;
        color: #0f172a;
        font-size: 0.78rem;
        font-weight: 600;
        appearance: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    body.dark-theme .admin-table-entries-select {
        border-color: #475569;
        background: #1e293b url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.7rem center;
        color: #f8fafc;
    }

    .admin-table-entries-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    body.dark-theme .admin-table-entries-select:focus {
        box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.18);
    }

    .admin-table-pagination[hidden] {
        display: none !important;
    }

    .admin-table-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1rem;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    body.dark-theme .admin-table-pagination {
        border-top-color: #334155;
        background: #1e293b;
    }

    .admin-table-pagination-meta {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        flex-wrap: wrap;
        min-width: 0;
    }

    .admin-table-pagination-summary,
    .admin-table-pagination-page {
        font-size: 0.78rem;
        line-height: 1.5;
        color: #64748b;
    }

    body.dark-theme .admin-table-pagination-summary,
    body.dark-theme .admin-table-pagination-page {
        color: #94a3b8;
    }

    .admin-table-pagination-page {
        font-weight: 600;
        color: #475569;
    }

    body.dark-theme .admin-table-pagination-page {
        color: #cbd5e1;
    }

    .admin-table-pagination-nav {
        display: flex;
        align-items: center;
        gap: 0.38rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .admin-table-pagination-link,
    .admin-table-pagination-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.4rem;
        min-height: 2.4rem;
        padding: 0.45rem 0.78rem;
        border-radius: 0.75rem;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1;
    }

    .admin-table-pagination-link {
        border: 1px solid #dbe2ea;
        background: #f8fafc;
        color: #334155;
        text-decoration: none;
        transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    button.admin-table-pagination-link {
        cursor: pointer;
    }

    body.dark-theme .admin-table-pagination-link {
        border-color: #475569;
        background: #0f172a;
        color: #e2e8f0;
    }

    .admin-table-pagination-link:hover:not(:disabled) {
        border-color: #3b82f6;
        color: #2563eb;
        background: #eff6ff;
        transform: translateY(-1px);
    }

    body.dark-theme .admin-table-pagination-link:hover:not(:disabled) {
        border-color: #60a5fa;
        color: #bfdbfe;
        background: #1e3a8a;
    }

    .admin-table-pagination-link.is-active {
        border-color: #3b82f6;
        background: #3b82f6;
        color: #ffffff;
        pointer-events: none;
        box-shadow: 0 10px 20px rgba(59, 130, 246, 0.18);
    }

    body.dark-theme .admin-table-pagination-link.is-active {
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.24);
    }

    .admin-table-pagination-link.is-disabled,
    .admin-table-pagination-link:disabled {
        opacity: 0.55;
        pointer-events: none;
        cursor: not-allowed;
    }

    .admin-table-pagination-ellipsis {
        color: #94a3b8;
    }

    body.dark-theme .admin-table-pagination-ellipsis {
        color: #64748b;
    }

    @media (max-width: 768px) {
        .admin-table-pagination {
            align-items: stretch;
        }

        .admin-table-pagination-nav {
            justify-content: flex-start;
        }
    }
</style>
