<style>
    .student-profile-page {
        --profile-surface: #ffffff;
        --profile-surface-muted: #f8fafc;
        --profile-border: #dbe4f0;
        --profile-border-strong: #c7d2e3;
        --profile-text: #102033;
        --profile-text-muted: #64748b;
        --profile-blue: #2563eb;
        --profile-green: #15803d;
        --profile-red: #dc2626;
        --profile-amber: #d97706;
        --profile-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
        --student-secondary-left-column: minmax(0, 1.2fr);
        --student-secondary-right-column: minmax(320px, 0.8fr);
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    body.dark-theme .student-profile-page {
        --profile-surface: #162235;
        --profile-surface-muted: #0f1726;
        --profile-border: #2a3b54;
        --profile-border-strong: #36506f;
        --profile-text: #edf2fb;
        --profile-text-muted: #93a8c4;
        --profile-shadow: 0 10px 26px rgba(2, 6, 23, 0.26);
    }

    .student-profile-header,
    .student-pane-header,
    .student-summary-grid,
    .student-info-list,
    .student-system-grid,
    .student-profile-lower-grid {
        display: grid;
        gap: 10px;
    }

    .student-profile-header {
        grid-template-columns: auto 1fr;
        align-items: center;
    }

    .student-back-link,
    .student-inline-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--profile-blue);
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .student-header-copy h1 {
        margin: 0;
        font-size: clamp(1.3rem, 1.7vw, 1.55rem);
        color: var(--profile-text);
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .student-header-copy p,
    .student-pane-header p,
    .student-muted-copy {
        margin: 3px 0 0;
        color: var(--profile-text-muted);
        font-size: 0.76rem;
        line-height: 1.45;
    }

    .student-profile-grid {
        display: grid;
        grid-template-columns: minmax(280px, 320px) minmax(0, 1fr);
        gap: 14px;
    }

    .student-pane-stack {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .student-profile-card,
    .student-pane {
        background: var(--profile-surface);
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        box-shadow: var(--profile-shadow);
    }

    .student-profile-card {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .student-profile-top {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-profile-avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(135deg, #dbeafe 0%, #d1fae5 100%);
        color: #1d4ed8;
        font-size: 1.02rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    body.dark-theme .student-profile-avatar {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.24) 0%, rgba(16, 185, 129, 0.22) 100%);
        color: #bfdbfe;
    }

    .student-profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .student-profile-title h2,
    .student-pane-header h3,
    .student-empty-card h3 {
        margin: 0;
        color: var(--profile-text);
        font-weight: 800;
    }

    .student-profile-title {
        display: grid;
        gap: 6px;
        min-width: 0;
    }

    .student-profile-title h2 {
        font-size: 1.02rem;
    }

    .student-profile-title p {
        margin: 3px 0 0;
        color: var(--profile-text-muted);
        font-weight: 600;
        font-size: 0.76rem;
    }

    .student-id-card {
        display: inline-grid;
        gap: 2px;
        width: fit-content;
        min-width: 136px;
        padding: 7px 9px;
        border-radius: 8px;
        border: 1px solid var(--profile-border);
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(16, 185, 129, 0.06) 100%);
    }

    body.dark-theme .student-id-card {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.18) 0%, rgba(16, 185, 129, 0.1) 100%);
    }

    .student-id-label {
        color: var(--profile-text-muted);
        font-size: 0.61rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .student-id-value {
        color: var(--profile-text);
        font-size: 0.84rem;
        font-weight: 800;
        letter-spacing: 0.01em;
        line-height: 1.3;
    }

    .student-badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 6px;
    }

    .student-chip,
    .student-status-pill,
    .student-activity-role,
    .student-privilege-source {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.24rem 0.5rem;
        border-radius: 999px;
        font-size: 0.64rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .student-chip.status-active,
    .student-status-pill.status-issued,
    .student-status-pill.status-paid,
    .student-activity-role.role-student {
        background: rgba(34, 197, 94, 0.14);
        color: var(--profile-green);
    }

    .student-chip.status-inactive,
    .student-status-pill.status-overdue,
    .student-status-pill.status-pending {
        background: rgba(239, 68, 68, 0.14);
        color: var(--profile-red);
    }

    .student-status-pill.status-returned,
    .student-status-pill.status-waived,
    .student-activity-role.role-system,
    .student-privilege-source.default {
        background: rgba(148, 163, 184, 0.16);
        color: var(--profile-text-muted);
    }

    .student-chip.role,
    .student-activity-role.role-staff,
    .student-privilege-source.custom {
        background: rgba(37, 99, 235, 0.12);
        color: var(--profile-blue);
    }

    .student-activity-role.role-admin {
        background: rgba(239, 68, 68, 0.14);
        color: #b91c1c;
    }

    body.dark-theme .student-activity-role.role-admin {
        background: rgba(239, 68, 68, 0.22);
        color: #fca5a5;
    }

    .student-info-list,
    .student-system-grid {
        grid-template-columns: 1fr;
    }

    .student-info-item,
    .student-system-card,
    .student-privilege-card,
    .student-summary-card,
    .student-activity-card {
        background: var(--profile-surface-muted);
        border: 1px solid var(--profile-border);
        border-radius: 8px;
    }

    .student-info-item {
        padding: 8px 10px;
    }

    .student-system-card {
        padding: 8px 10px;
    }

    .student-info-label,
    .student-summary-label,
    .student-privilege-label {
        display: block;
        color: var(--profile-text-muted);
        font-size: 0.64rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 3px;
    }

    .student-info-value,
    .student-summary-value,
    .student-privilege-value {
        color: var(--profile-text);
        font-weight: 700;
        font-size: 0.8rem;
        line-height: 1.4;
    }

    .student-account-action {
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 0.62rem 0.82rem;
        font-size: 0.78rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: transform 0.16s ease, opacity 0.16s ease;
    }

    .student-account-action:hover {
        transform: translateY(-1px);
    }

    .student-account-action.status-active {
        background: rgba(239, 68, 68, 0.12);
        color: var(--profile-red);
    }

    .student-account-action.status-inactive {
        background: rgba(34, 197, 94, 0.14);
        color: var(--profile-green);
    }

    .student-account-action:disabled {
        cursor: wait;
        opacity: 0.72;
        transform: none;
    }

    .student-summary-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 8px;
    }

    .student-summary-card {
        padding: 12px;
    }

    .student-summary-value {
        font-size: 0.98rem;
        letter-spacing: -0.03em;
    }

    .student-pane {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .student-pane-fixed {
        height: 620px;
        min-height: 620px;
        max-height: 620px;
    }

    .student-books-pane {
        height: 590px;
        min-height: 590px;
        max-height: 590px;
    }

    .student-fines-pane {
        height: auto;
        min-height: 400px;
        max-height: none;
    }

    .student-privileges-pane {
        height: auto;
        min-height: 0;
        max-height: none;
    }

    .student-pane-header {
        grid-template-columns: 1fr auto;
        align-items: start;
    }

    .student-pane-scroll {
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 4px;
    }

    .student-pane-scroll-activity {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
    }

    .student-pane-scroll-table {
        flex: 1 1 auto;
        border: 1px solid var(--profile-border);
        border-radius: 8px;
        background: var(--profile-surface-muted);
    }

    .student-books-table-panel,
    .student-fines-table-panel,
    .student-request-table-panel {
        display: flex;
        flex-direction: column;
    }

    .student-books-table-scroller,
    .student-fine-table-scroller,
    .student-request-table-scroller {
        flex: 1 1 auto;
        min-height: 0;
    }

    .student-pane-scroll::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .student-pane-scroll::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.42);
        border-radius: 999px;
    }

    .student-pane-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .student-pane-footer,
    .student-request-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .student-pane-meta {
        color: var(--profile-text-muted);
        font-size: 0.7rem;
        font-weight: 600;
    }

    .student-pane-action-btn,
    .student-pagination-btn {
        min-height: 32px;
        padding: 0.42rem 0.72rem;
        border-radius: 8px;
        border: 1px solid var(--profile-border);
        background: var(--profile-surface-muted);
        color: var(--profile-text);
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
        transition: border-color 0.16s ease, color 0.16s ease, background-color 0.16s ease, opacity 0.16s ease;
    }

    .student-pane-action-btn:hover,
    .student-pagination-btn:hover {
        border-color: rgba(37, 99, 235, 0.42);
        color: var(--profile-blue);
    }

    .student-pane-action-btn:disabled,
    .student-pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.55;
    }

    .student-pagination {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .student-pagination-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        color: var(--profile-text-muted);
        font-size: 0.74rem;
        font-weight: 700;
    }

    .student-pagination-btn.is-active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #1d4ed8;
        color: #ffffff;
    }

    .student-entries-control {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--profile-text-muted);
        font-size: 0.7rem;
        font-weight: 700;
    }

    .student-entries-select {
        width: 80px;
        min-width: 80px;
        padding-right: 2rem;
    }

    .student-table-filters {
        display: grid;
        grid-template-columns: minmax(300px, 340px) minmax(160px, 190px) minmax(160px, 190px) auto;
        align-items: center;
        justify-content: start;
        gap: 8px;
    }

    .student-table-input,
    .student-table-select {
        width: 100%;
        border-radius: 8px;
        border: 1px solid var(--profile-border);
        background: var(--profile-surface-muted);
        color: var(--profile-text);
        font-size: 0.78rem;
        padding: 0.55rem 0.72rem;
    }

    .student-filter-reset {
        min-height: 34px;
        padding: 0.55rem 0.82rem;
        border-radius: 8px;
        border: 1px solid var(--profile-border);
        background: var(--profile-surface-muted);
        color: var(--profile-text);
        font-size: 0.74rem;
        font-weight: 700;
        cursor: pointer;
        transition: border-color 0.16s ease, color 0.16s ease, background-color 0.16s ease;
        white-space: nowrap;
    }

    .student-filter-reset:hover {
        border-color: rgba(37, 99, 235, 0.45);
        color: var(--profile-blue);
    }

    .student-table-input:focus,
    .student-table-select:focus {
        outline: none;
        border-color: rgba(37, 99, 235, 0.5);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .student-filter-reset:focus {
        outline: none;
        border-color: rgba(37, 99, 235, 0.5);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .student-table-scroller {
        overflow-x: auto;
    }

    .student-data-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .student-fine-table-scroller {
        overflow-x: hidden;
    }

    .student-fine-table {
        width: 100%;
        min-width: 0;
        table-layout: fixed;
    }

    .student-fine-table th,
    .student-fine-table td {
        white-space: nowrap;
    }

    .student-fine-table th:nth-child(1),
    .student-fine-table td:nth-child(1) {
        width: 23%;
        white-space: normal;
    }

    .student-fine-table th:nth-child(2),
    .student-fine-table td:nth-child(2) {
        width: 12%;
    }

    .student-fine-table th:nth-child(3),
    .student-fine-table td:nth-child(3) {
        width: 8%;
    }

    .student-fine-table th:nth-child(4),
    .student-fine-table td:nth-child(4) {
        width: 12%;
    }

    .student-fine-table th:nth-child(5),
    .student-fine-table td:nth-child(5) {
        width: 11%;
    }

    .student-fine-table th:nth-child(6),
    .student-fine-table td:nth-child(6) {
        width: 34%;
        padding-left: 6px;
        padding-right: 6px;
    }

    .student-data-table th,
    .student-data-table td {
        padding: 7px 9px;
        border-bottom: 1px solid var(--profile-border);
        text-align: left;
        vertical-align: middle;
    }

    .student-data-table th {
        color: var(--profile-text-muted);
        font-size: 0.66rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: var(--profile-surface-muted);
    }

    .student-data-table td {
        color: var(--profile-text);
        font-size: 0.77rem;
    }

    .student-request-table {
        min-width: 0;
    }

    .student-books-table {
        min-width: 0;
    }

    .student-books-table th,
    .student-books-table td {
        white-space: nowrap;
    }

    .student-books-table th:nth-child(1),
    .student-books-table td:nth-child(1) {
        width: 27%;
        white-space: normal;
    }

    .student-books-table th:nth-child(2),
    .student-books-table td:nth-child(2) {
        width: 15%;
    }

    .student-books-table th:nth-child(3),
    .student-books-table td:nth-child(3),
    .student-books-table th:nth-child(4),
    .student-books-table td:nth-child(4),
    .student-books-table th:nth-child(5),
    .student-books-table td:nth-child(5) {
        width: 13%;
    }

    .student-books-table th:nth-child(6),
    .student-books-table td:nth-child(6) {
        width: 10%;
    }

    .student-books-table th:nth-child(7),
    .student-books-table td:nth-child(7) {
        width: 9%;
    }

    .student-request-table th,
    .student-request-table td {
        padding: 8px 10px;
    }

    .student-request-table th:nth-child(1),
    .student-request-table td:nth-child(1) {
        width: 48%;
    }

    .student-request-table th:nth-child(2),
    .student-request-table td:nth-child(2) {
        width: 24%;
        white-space: nowrap;
    }

    .student-request-table th:nth-child(3),
    .student-request-table td:nth-child(3) {
        width: 28%;
    }

    .student-data-table tbody tr:hover {
        background: rgba(37, 99, 235, 0.04);
    }

    .student-data-table tbody tr.is-overdue {
        background-image: linear-gradient(90deg, rgba(239, 68, 68, 0.08), transparent 14%);
    }

    body.dark-theme .student-data-table tbody tr.is-overdue {
        background-image: linear-gradient(90deg, rgba(248, 113, 113, 0.12), transparent 14%);
    }

    .student-table-book strong,
    .student-activity-card h4 {
        display: block;
        margin: 0;
        color: var(--profile-text);
        font-weight: 800;
    }

    .student-table-book span,
    .student-activity-card p {
        display: block;
        margin-top: 2px;
        color: var(--profile-text-muted);
    }

    .student-amount.overdue {
        color: var(--profile-red);
        font-weight: 800;
    }

    .student-fine-actions {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 4px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .student-fine-action-btn {
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.28rem;
        white-space: nowrap;
        padding: 0.34rem 0.52rem;
        border-radius: 6px;
        font-size: 0.68rem;
        font-weight: 700;
        transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease, opacity 0.16s ease;
    }

    .student-fine-action-btn svg {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
    }

    .student-fine-table .student-status-pill {
        padding: 0.2rem 0.42rem;
        font-size: 0.6rem;
        letter-spacing: 0.03em;
    }

    .student-fine-action-btn:hover {
        transform: translateY(-1px);
    }

    .student-fine-action-btn:focus-visible,
    .student-modal-btn:focus-visible,
    .student-modal-textarea:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
    }

    .student-fine-action-btn:disabled,
    .student-modal-btn:disabled {
        opacity: 0.58;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .student-fine-action-btn.btn-paid {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2);
    }

    .student-fine-action-btn.btn-paid:hover {
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.24);
    }

    .student-fine-action-btn.btn-waive {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: #ffffff;
        box-shadow: 0 1px 2px rgba(59, 130, 246, 0.2);
    }

    .student-fine-action-btn.btn-waive:hover {
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.24);
    }

    .student-fine-action-btn.btn-email {
        background: rgba(148, 163, 184, 0.16);
        color: var(--profile-text-muted);
        border: 1px solid var(--profile-border);
    }

    .student-fine-action-btn.btn-email:hover {
        color: #f97316;
        background: #ffedd5;
        border-color: rgba(249, 115, 22, 0.18);
    }

    body.dark-theme .student-fine-action-btn.btn-email {
        background: #24354b;
        color: #cbd5e1;
        border-color: #2f4562;
    }

    body.dark-theme .student-fine-action-btn.btn-email:hover {
        background: #9a3412;
        color: #fdba74;
        border-color: #9a3412;
    }

    .student-request-status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem 0.62rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .student-request-status-pill.status-pending {
        background: rgba(217, 119, 6, 0.14);
        color: #b45309;
    }

    .student-request-status-pill.status-approved,
    .student-request-status-pill.status-issued {
        background: rgba(37, 99, 235, 0.12);
        color: var(--profile-blue);
    }

    .student-request-status-pill.status-returned {
        background: rgba(34, 197, 94, 0.14);
        color: var(--profile-green);
    }

    .student-request-status-pill.status-rejected {
        background: rgba(239, 68, 68, 0.14);
        color: var(--profile-red);
    }

    .student-request-status-pill.status-cancelled {
        background: rgba(148, 163, 184, 0.16);
        color: var(--profile-text-muted);
    }

    body.dark-theme .student-request-status-pill.status-pending {
        background: rgba(217, 119, 6, 0.2);
        color: #fcd34d;
    }

    body.dark-theme .student-request-status-pill.status-approved,
    body.dark-theme .student-request-status-pill.status-issued {
        background: rgba(37, 99, 235, 0.2);
        color: #93c5fd;
    }

    body.dark-theme .student-request-status-pill.status-returned {
        background: rgba(22, 163, 74, 0.2);
        color: #86efac;
    }

    body.dark-theme .student-request-status-pill.status-rejected {
        background: rgba(239, 68, 68, 0.22);
        color: #fca5a5;
    }

    body.dark-theme .student-request-status-pill.status-cancelled {
        background: rgba(71, 85, 105, 0.26);
        color: #cbd5e1;
    }

    .student-profile-lower-grid,
    .student-profile-bottom-grid {
        display: grid;
        grid-template-columns: var(--student-secondary-left-column) var(--student-secondary-right-column);
        gap: 16px;
        align-items: start;
    }

    .student-profile-lower-grid {
        grid-template-columns: minmax(0, 1.3fr) minmax(340px, 0.7fr);
        grid-auto-rows: min-content;
        align-items: stretch;
    }

    .student-profile-lower-grid > .student-pane {
        align-self: stretch;
        height: auto;
        min-height: 0;
    }

    .student-privilege-grid,
    .student-activity-list {
        display: grid;
        gap: 10px;
    }

    .student-privilege-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-content: start;
    }

    .student-privilege-card,
    .student-activity-card {
        padding: 12px;
    }

    .student-privilege-card {
        display: grid;
        gap: 6px;
        align-content: start;
    }

    .student-privilege-meta,
    .student-activity-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .student-activity-list {
        gap: 10px;
    }

    .student-activity-more-row {
        display: flex;
        justify-content: center;
        padding-top: 4px;
    }

    .student-activity-more-btn {
        min-width: 112px;
    }

    .student-activity-entry {
        --activity-accent: #475569;
        --activity-soft: rgba(148, 163, 184, 0.16);
        --activity-border: rgba(148, 163, 184, 0.28);
        display: grid;
        grid-template-columns: 16px minmax(0, 1fr);
        gap: 10px;
        align-items: stretch;
    }

    .student-activity-entry.type-book {
        --activity-accent: #2563eb;
        --activity-soft: rgba(37, 99, 235, 0.12);
        --activity-border: rgba(37, 99, 235, 0.18);
    }

    .student-activity-entry.type-fine {
        --activity-accent: #dc2626;
        --activity-soft: rgba(239, 68, 68, 0.12);
        --activity-border: rgba(239, 68, 68, 0.18);
    }

    .student-activity-entry.type-account {
        --activity-accent: #7c3aed;
        --activity-soft: rgba(124, 58, 237, 0.12);
        --activity-border: rgba(124, 58, 237, 0.18);
    }

    .student-activity-entry.type-profile {
        --activity-accent: #ca8a04;
        --activity-soft: rgba(234, 179, 8, 0.14);
        --activity-border: rgba(234, 179, 8, 0.2);
    }

    body.dark-theme .student-activity-entry.type-book {
        --activity-soft: rgba(37, 99, 235, 0.18);
        --activity-border: rgba(96, 165, 250, 0.24);
    }

    body.dark-theme .student-activity-entry.type-fine {
        --activity-soft: rgba(220, 38, 38, 0.18);
        --activity-border: rgba(252, 165, 165, 0.24);
    }

    body.dark-theme .student-activity-entry.type-account {
        --activity-soft: rgba(124, 58, 237, 0.18);
        --activity-border: rgba(196, 181, 253, 0.24);
    }

    body.dark-theme .student-activity-entry.type-profile {
        --activity-soft: rgba(234, 179, 8, 0.18);
        --activity-border: rgba(250, 204, 21, 0.24);
    }

    .student-activity-rail {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100%;
        padding-top: 10px;
    }

    .student-activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: var(--activity-accent);
        box-shadow: 0 0 0 3px var(--activity-soft);
        z-index: 1;
    }

    .student-activity-line {
        width: 2px;
        flex: 1;
        min-height: 34px;
        margin-top: 6px;
        border-radius: 999px;
        background: linear-gradient(180deg, var(--activity-accent) 0%, rgba(148, 163, 184, 0.08) 100%);
        opacity: 0.6;
    }

    .student-activity-entry.is-last .student-activity-line {
        opacity: 0;
    }

    .student-activity-surface {
        border: 1px solid var(--activity-border);
        border-left: 3px solid var(--activity-accent);
        border-radius: 12px;
        padding: 12px 12px 10px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
    }

    body.dark-theme .student-activity-surface {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.9) 0%, rgba(22, 34, 53, 0.92) 100%);
        box-shadow: 0 12px 24px rgba(2, 6, 23, 0.24);
    }

    .student-activity-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 8px;
    }

    .student-activity-head-copy {
        min-width: 0;
        display: grid;
        gap: 4px;
    }

    .student-activity-type-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        padding: 0.22rem 0.52rem;
        border-radius: 999px;
        background: var(--activity-soft);
        color: var(--activity-accent);
        font-size: 0.63rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .student-activity-title {
        margin: 0;
        color: var(--profile-text);
        font-size: 0.86rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .student-activity-time {
        display: grid;
        gap: 4px;
        justify-items: end;
        min-width: 136px;
        text-align: right;
    }

    .student-activity-time-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.28rem 0.56rem;
        border-radius: 999px;
        background: var(--profile-surface-muted);
        border: 1px solid var(--profile-border);
        color: var(--profile-text);
        font-size: 0.68rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .student-activity-time-detail {
        color: var(--profile-text-muted);
        font-size: 0.68rem;
        line-height: 1.35;
    }

    .student-activity-description {
        margin: 0;
        color: var(--profile-text-muted);
        font-size: 0.76rem;
        line-height: 1.48;
    }

    .student-activity-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px dashed var(--profile-border);
    }

    .student-activity-actor {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .student-activity-avatar {
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
        color: #1d4ed8;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    body.dark-theme .student-activity-avatar {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(79, 70, 229, 0.22) 100%);
        color: #bfdbfe;
    }

    .student-activity-actor-copy {
        min-width: 0;
        display: grid;
        gap: 3px;
    }

    .student-activity-actor-row {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .student-activity-actor-name {
        color: var(--profile-text);
        font-size: 0.76rem;
        font-weight: 700;
    }

    .student-activity-actor-note {
        color: var(--profile-text-muted);
        font-size: 0.68rem;
        line-height: 1.35;
    }

    .student-activity-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.24rem 0.52rem;
        border-radius: 999px;
        background: var(--activity-soft);
        color: var(--activity-accent);
        font-size: 0.64rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .student-empty-card {
        border-radius: 10px;
        border: 1px dashed var(--profile-border-strong);
        padding: 20px 16px;
        text-align: center;
        color: var(--profile-text-muted);
    }

    .student-pane-empty {
        display: flex;
        flex: 1 1 auto;
        min-height: 100%;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        margin: 0;
    }

    .student-empty-card p {
        margin: 6px auto 0;
        max-width: 420px;
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .student-toast-container {
        position: fixed;
        top: 16px;
        right: 16px;
        z-index: 1200;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .student-toast {
        min-width: 280px;
        max-width: 340px;
        padding: 12px 14px;
        border-radius: 10px;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.15);
        color: #ffffff;
        font-weight: 700;
        animation: studentToastIn 0.22s ease;
    }

    .student-toast.success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    }

    .student-toast.error {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .student-toast.info {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .student-spinner {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid currentColor;
        border-right-color: transparent;
        animation: studentSpin 0.7s linear infinite;
    }

    .student-modal {
        position: fixed;
        inset: 0;
        z-index: 1300;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.18s ease;
    }

    .student-modal[hidden] {
        display: none;
    }

    .student-modal.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .student-modal-backdrop {
        position: absolute;
        inset: 0;
        border: none;
        background: rgba(15, 23, 42, 0.5);
        cursor: pointer;
    }

    .student-modal-dialog {
        position: relative;
        z-index: 1;
        width: min(100%, 460px);
        background: var(--profile-surface);
        border: 1px solid var(--profile-border);
        border-radius: 14px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
        padding: 18px;
        display: grid;
        gap: 14px;
    }

    body.dark-theme .student-modal-dialog {
        box-shadow: 0 26px 68px rgba(2, 6, 23, 0.42);
    }

    .student-modal-header {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 12px;
        align-items: start;
    }

    .student-modal-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: var(--profile-blue);
    }

    .student-modal-icon svg {
        width: 20px;
        height: 20px;
    }

    .student-modal-icon.paid {
        background: rgba(16, 185, 129, 0.14);
        color: #059669;
    }

    .student-modal-icon.email {
        background: rgba(249, 115, 22, 0.14);
        color: #ea580c;
    }

    .student-modal-icon.waive {
        background: rgba(59, 130, 246, 0.14);
        color: var(--profile-blue);
    }

    .student-modal-copy h3 {
        margin: 0;
        color: var(--profile-text);
        font-size: 1rem;
        font-weight: 800;
    }

    .student-modal-copy p,
    .student-modal-detail,
    .student-modal-help {
        margin: 4px 0 0;
        color: var(--profile-text-muted);
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .student-modal-field-label {
        color: var(--profile-text);
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .student-modal-textarea {
        width: 100%;
        resize: vertical;
        min-height: 108px;
        border-radius: 10px;
        border: 1px solid var(--profile-border);
        background: var(--profile-surface-muted);
        color: var(--profile-text);
        padding: 0.8rem 0.9rem;
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .student-modal-error {
        margin: -4px 0 0;
        color: var(--profile-red);
        font-size: 0.78rem;
        font-weight: 700;
    }

    .student-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .student-modal-btn {
        border: 1px solid transparent;
        border-radius: 10px;
        min-height: 40px;
        padding: 0.68rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 0.82rem;
        font-weight: 800;
        cursor: pointer;
        transition: transform 0.16s ease, opacity 0.16s ease, box-shadow 0.16s ease;
    }

    .student-modal-btn:hover {
        transform: translateY(-1px);
    }

    .student-modal-btn.secondary {
        background: var(--profile-surface-muted);
        color: var(--profile-text);
        border-color: var(--profile-border);
    }

    .student-modal-btn.primary {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        box-shadow: 0 10px 18px rgba(37, 99, 235, 0.18);
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

    @keyframes studentToastIn {
        from {
            opacity: 0;
            transform: translateY(-8px) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes studentSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 1080px) {
        .student-profile-grid,
        .student-profile-lower-grid,
        .student-profile-bottom-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .student-profile-header,
        .student-pane-header {
            grid-template-columns: 1fr;
        }

        .student-profile-top {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-table-filters {
            grid-template-columns: 1fr 1fr;
        }

        .student-activity-head,
        .student-activity-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .student-activity-time {
            min-width: 0;
            justify-items: start;
            text-align: left;
        }

        .student-pane-footer,
        .student-request-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .student-pagination {
            justify-content: flex-start;
        }

        .student-modal-actions {
            justify-content: stretch;
        }

        .student-modal-btn {
            flex: 1 1 0;
        }
    }

    @media (max-width: 560px) {
        .student-summary-grid {
            grid-template-columns: 1fr;
        }

        .student-privilege-grid {
            grid-template-columns: 1fr;
        }

        .student-table-filters {
            grid-template-columns: 1fr;
        }

        .student-pane-fixed {
            height: auto;
            min-height: auto;
            max-height: none;
        }

        .student-pane-scroll {
            overflow: visible;
            padding-right: 0;
        }

        .student-pagination {
            width: 100%;
        }

        .student-pagination-btn,
        .student-pane-action-btn {
            flex: 1 1 auto;
        }

        .student-activity-entry {
            grid-template-columns: 1fr;
        }

        .student-activity-rail {
            display: none;
        }

        .student-toast-container {
            left: 16px;
            right: 16px;
            top: 16px;
        }

        .student-toast {
            min-width: auto;
            max-width: none;
        }

        .student-modal {
            padding: 14px;
        }

        .student-modal-header {
            grid-template-columns: 1fr;
        }

        .student-modal-icon {
            width: 40px;
            height: 40px;
        }
    }
</style>
