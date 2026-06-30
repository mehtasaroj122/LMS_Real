<style>
    .table-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid;
        margin-top: 0;
        transition: background-color 0.3s, border-color 0.3s;
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
        -webkit-overflow-scrolling: touch;
    }

    .students-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1080px;
        table-layout: fixed;
    }

    .students-table th {
        padding: 6px 8px;
        font-weight: 600;
        font-size: 11px;
        border-bottom: 1px solid;
        white-space: nowrap;
        text-align: start;
        transition: background-color 0.3s, border-color 0.3s, color 0.3s;
    }

    body.light-theme .students-table th {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #475569;
    }

    body.dark-theme .students-table th {
        background-color: #1e293b;
        border-color: #334155;
        color: #cbd5e1;
    }

    .students-table td {
        padding: 6px 8px;
        border-bottom: 1px solid;
        vertical-align: middle;
        font-size: 13px;
        transition: border-color 0.3s, color 0.3s;
    }

    body.light-theme .students-table td {
        border-color: #e2e8f0;
        color: #0f172a;
    }

    body.dark-theme .students-table td {
        border-color: #334155;
        color: #f1f5f9;
    }

    .students-table tr:last-child td {
        border-bottom: none;
    }

    .students-table tr:hover {
        transition: background-color 0.3s;
    }

    body.light-theme .students-table tr:hover {
        background-color: #f8fafc;
    }

    body.dark-theme .students-table tr:hover {
        background-color: #2d3748;
    }

    .students-table th:nth-child(1),
    .students-table td:nth-child(1) {
        padding-right: 4px;
        width: 18%;
        max-width: 18%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .students-table th:nth-child(2),
    .students-table td:nth-child(2) {
        padding-left: 4px;
        width: 16%;
        max-width: 16%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .students-table th:nth-child(3),
    .students-table td:nth-child(3) {
        width: 12%;
        max-width: 12%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .students-table th:nth-child(4),
    .students-table td:nth-child(4),
    .students-table th:nth-child(5),
    .students-table td:nth-child(5),
    .students-table th:nth-child(6),
    .students-table td:nth-child(6) {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .students-table th:nth-child(4),
    .students-table td:nth-child(4) {
        width: 20%;
        max-width: 20%;
    }

    .students-table th:nth-child(5),
    .students-table td:nth-child(5) {
        width: 8%;
        max-width: 8%;
    }

    .students-table th:nth-child(6),
    .students-table td:nth-child(6) {
        width: 11%;
        max-width: 11%;
    }

    .students-table th:nth-child(7),
    .students-table td:nth-child(7) {
        width: 15%;
        max-width: 15%;
    }

    .text-muted {
        transition: color 0.3s;
    }

    body.light-theme .text-muted {
        color: #64748b;
    }

    body.dark-theme .text-muted {
        color: #94a3b8;
    }

    .student-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .student-avatar {
        width: 36px;
        height: 36px;
        border-radius: 9999px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    body.light-theme .student-avatar {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #ffffff;
    }

    body.dark-theme .student-avatar {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        color: #ffffff;
    }

    .student-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .student-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        min-width: 0;
    }

    .student-name {
        font-weight: 600;
        color: var(--text-primary);
        line-height: 1.4;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        gap: 4px;
        transition: background 0.3s, color 0.3s;
    }

    body.light-theme .status-active {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
    }

    body.dark-theme .status-active {
        background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
        color: #4ade80;
    }

    body.light-theme .status-inactive {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    body.dark-theme .status-inactive {
        background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
        color: #f87171;
    }

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
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: background-color 0.3s, color 0.3s, opacity 0.3s;
        cursor: pointer;
        text-decoration: none;
    }

    body.light-theme .action-btn {
        color: #64748b;
        background-color: #f1f5f9;
    }

    body.dark-theme .action-btn {
        color: #94a3b8;
        background-color: #334155;
    }

    body.light-theme .action-btn:hover {
        color: #3b82f6;
        background-color: #e0e7ff;
    }

    body.dark-theme .action-btn:hover {
        color: #93c5fd;
        background-color: #1e40af;
    }

    .action-btn:disabled {
        cursor: wait;
        opacity: 0.7;
    }

    .search-filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
        padding: 1rem;
        border-radius: 0.5rem;
        align-items: center;
        background: white;
        border: 1px solid #e5e7eb;
        transition: all 0.3s ease;
    }

    body.dark-theme .search-filter-container {
        background: #1f2937;
        border-color: #374151;
    }

    .search-box {
        flex: 1;
        min-width: 200px;
        max-width: 250px;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 0.5rem 1rem 0.5rem 2.25rem;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        font-size: 0.75rem;
        transition: all 0.3s ease;
        background-color: #f8fafc;
        color: #0f172a;
    }

    body.dark-theme .search-input {
        background-color: #374151;
        border-color: #4b5563;
        color: #f1f5f9;
    }

    .search-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #64748b;
        pointer-events: none;
    }

    body.dark-theme .search-icon {
        color: #9ca3af;
    }

    .filters-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }

    .student-entries-control {
        margin-left: auto;
    }

    .filter-select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        cursor: pointer;
        appearance: none;
        min-width: 120px;
        transition: all 0.3s ease;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
        border: 1px solid #e5e7eb;
        color: #0f172a;
    }

    body.dark-theme .filter-select {
        background: #374151 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
        border-color: #4b5563;
        color: #f1f5f9;
    }

    .reset-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        background: #f8fafc;
        color: #0f172a;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    body.dark-theme .reset-btn {
        background: #374151;
        border-color: #4b5563;
        color: #f1f5f9;
    }

    .reset-btn:hover {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    body.dark-theme .reset-btn:hover {
        border-color: #3b82f6;
        color: #93c5fd;
    }

    .student-toolbar-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.9rem;
        padding: 0 0.1rem;
        font-size: 0.75rem;
    }

    body.light-theme .student-toolbar-meta {
        color: #64748b;
    }

    body.dark-theme .student-toolbar-meta {
        color: #94a3b8;
    }

    .student-toolbar-meta strong {
        color: var(--text-primary);
    }

    .student-stat-card {
        min-height: 90px;
    }

    .student-stat-copy {
        display: block;
    }

    .student-stat-loading-lines {
        display: none;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
    }

    .student-stat-card.is-loading .student-stat-copy {
        display: none;
    }

    .student-stat-card.is-loading .student-stat-loading-lines {
        display: flex;
    }

    .student-stat-loading-line,
    .student-table-loading .student-skeleton-line {
        display: block;
        height: 13px;
        border-radius: 999px;
        position: relative;
        overflow: hidden;
    }

    .student-stat-loading-line.short,
    .student-table-loading .student-skeleton-line.short {
        width: 64px;
    }

    .student-table-loading .student-skeleton-line.short {
        width: 58%;
    }

    .student-stat-loading-line.long {
        width: 148px;
        height: 12px;
    }

    .student-table-loading .student-skeleton-line.medium {
        width: 72%;
    }

    .student-table-loading .student-skeleton-line.long {
        width: 90%;
    }

    body.light-theme .student-stat-loading-line,
    body.light-theme .student-table-loading .student-skeleton-line {
        background-color: #e2e8f0;
    }

    body.dark-theme .student-stat-loading-line,
    body.dark-theme .student-table-loading .student-skeleton-line {
        background-color: #334155;
    }

    .student-stat-loading-line::after,
    .student-table-loading .student-skeleton-line::after {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-100%);
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.7) 50%, transparent 100%);
        animation: studentTableShimmer 1.2s infinite;
    }

    body.dark-theme .student-stat-loading-line::after,
    body.dark-theme .student-table-loading .student-skeleton-line::after {
        background: linear-gradient(90deg, transparent 0%, rgba(148, 163, 184, 0.18) 50%, transparent 100%);
    }

    .student-table-loading .student-skeleton-line {
        width: 100%;
    }

    .student-empty-state {
        padding: 24px 16px 0;
        color: #64748b;
        text-align: center;
    }

    body.dark-theme .student-empty-state {
        color: #94a3b8;
    }

    .student-empty-state h3 {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 600;
    }

    .student-empty-state p {
        margin: 0;
        font-size: 13px;
    }

    #studentPaginationContainer {
        border-top: 1px solid #e2e8f0;
    }

    body.dark-theme #studentPaginationContainer {
        border-top-color: #334155;
    }

    #addStudentModal {
        backdrop-filter: blur(4px);
    }

    #addStudentModal > div {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    body.light-theme #addStudentModal > div {
        background-color: #ffffff;
        color: #1f2937;
    }

    body.dark-theme #addStudentModal > div {
        background-color: #1e293b;
        color: #e5e7eb;
    }

    #addStudentModal input,
    #addStudentModal textarea,
    #addStudentModal select {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }

    body.light-theme #addStudentModal input,
    body.light-theme #addStudentModal textarea,
    body.light-theme #addStudentModal select {
        background-color: #ffffff;
        color: #1f2937;
        border-color: #d1d5db;
    }

    body.dark-theme #addStudentModal input,
    body.dark-theme #addStudentModal textarea,
    body.dark-theme #addStudentModal select {
        background-color: #0f172a;
        color: #e5e7eb;
        border-color: #334155;
    }

    .student-field-error {
        display: none;
        color: #dc2626;
        font-size: 12px;
        font-weight: 500;
    }

    body.dark-theme .student-field-error {
        color: #fca5a5;
    }

    .student-field-error.show {
        display: block;
    }

    .student-helper {
        margin: 0;
        font-size: 12px;
        color: #64748b;
        line-height: 1.6;
    }

    body.dark-theme .student-helper {
        color: #94a3b8;
    }

    .student-toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1200;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }

    .student-toast {
        position: relative;
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 0.85rem;
        align-items: start;
        min-width: min(360px, calc(100vw - 40px));
        max-width: 420px;
        padding: 0.95rem 1rem 1rem;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        color: #ffffff;
        pointer-events: auto;
        opacity: 0;
        transform: translateX(18px) scale(0.98);
        animation: studentToastIn 0.24s ease forwards;
    }

    .student-toast.success {
        background: linear-gradient(135deg, #047857 0%, #10b981 100%);
    }

    .student-toast.error {
        background: linear-gradient(135deg, #b91c1c 0%, #ef4444 100%);
    }

    .student-toast.warning {
        background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
    }

    .student-toast.info {
        background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
    }

    .student-toast-icon {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.18);
        font-size: 1rem;
        flex-shrink: 0;
    }

    .student-toast-copy {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.18rem;
    }

    .student-toast-title {
        font-size: 0.92rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .student-toast-message {
        font-size: 0.83rem;
        line-height: 1.45;
        color: rgba(255, 255, 255, 0.95);
    }

    .student-toast-detail {
        font-size: 0.76rem;
        line-height: 1.4;
        color: rgba(255, 255, 255, 0.78);
    }

    .student-toast-close {
        width: 1.85rem;
        height: 1.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        color: rgba(255, 255, 255, 0.9);
        cursor: pointer;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .student-toast-close:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: scale(1.04);
    }

    .student-toast-progress {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.28);
        transform-origin: left center;
        animation: studentToastProgress 4.2s linear forwards;
    }

    .student-toast.is-leaving {
        animation: studentToastOut 0.18s ease forwards;
    }

    @keyframes studentToastIn {
        to {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }

    @keyframes studentToastOut {
        to {
            opacity: 0;
            transform: translateX(12px) scale(0.96);
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

    .sr-only {
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

    @keyframes studentTableShimmer {
        100% {
            transform: translateX(100%);
        }
    }

    @media (max-width: 768px) {
        .search-filter-container {
            flex-direction: column;
            align-items: stretch;
        }

        .student-toolbar-meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-box {
            max-width: none;
        }

        .filters-container {
            width: 100%;
        }

        .student-entries-control {
            margin-left: 0;
        }

        .student-toast-container {
            left: 16px;
            right: 16px;
            top: 16px;
        }
    }
</style>
