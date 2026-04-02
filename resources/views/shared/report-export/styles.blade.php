<style>
    .report-export-modal {
        --report-export-bg: #ffffff;
        --report-export-bg-secondary: #ffffff;
        --report-export-surface-muted: #f8fafc;
        --report-export-border: #dbe3ef;
        --report-export-text-primary: #0f172a;
        --report-export-text-secondary: #64748b;
        --report-export-focus: #2563eb;
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background: rgba(15, 23, 42, 0.56);
        z-index: 1200;
    }

    .report-export-modal.is-open {
        display: flex;
    }

    body.dark-theme .report-export-modal {
        --report-export-bg: #0f172a;
        --report-export-bg-secondary: #111c2e;
        --report-export-surface-muted: #162235;
        --report-export-border: #334155;
        --report-export-text-primary: #e2e8f0;
        --report-export-text-secondary: #94a3b8;
        --report-export-focus: #60a5fa;
        background: rgba(2, 6, 23, 0.72);
    }

    .report-export-panel {
        width: min(54rem, 100%);
        max-height: min(84vh, 48rem);
        display: flex;
        flex-direction: column;
        overflow-x: hidden;
        overflow-y: auto;
        border-radius: 1rem;
        border: 1px solid var(--report-export-border);
        background: var(--report-export-bg);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    body.dark-theme .report-export-panel {
        box-shadow: 0 24px 60px rgba(2, 6, 23, 0.42);
    }

    .report-export-header,
    .report-export-body,
    .report-export-footer {
        padding: 1rem 1.15rem;
    }

    .report-export-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        border-bottom: 1px solid var(--report-export-border);
    }

    .report-export-header-copy h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--report-export-text-primary);
    }

    .report-export-header-copy p {
        margin: 0.35rem 0 0;
        font-size: 0.84rem;
        line-height: 1.45;
        color: var(--report-export-text-secondary);
    }

    .report-export-close-btn {
        width: 2.25rem;
        height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 9999px;
        background: transparent;
        color: var(--report-export-text-secondary);
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    .report-export-close-btn:hover {
        color: var(--report-export-text-primary);
        border-color: var(--report-export-border);
        background: rgba(148, 163, 184, 0.08);
    }

    .report-export-close-btn:focus-visible,
    .report-export-btn:focus-visible,
    .report-export-scope-input:focus-visible + .report-export-scope-card {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.16);
    }

    .report-export-body {
        display: grid;
        gap: 0.75rem;
        overflow: visible;
    }

    .report-export-scope-picker {
        display: grid;
        gap: 0.6rem;
        padding: 0.8rem 0.9rem;
        border-radius: 0.9rem;
        border: 1px solid var(--report-export-border);
        background: var(--report-export-surface-muted);
    }

    .report-export-scope-heading {
        display: grid;
        gap: 0.22rem;
    }

    .report-export-scope-label {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--report-export-focus);
    }

    .report-export-scope-hint {
        font-size: 0.76rem;
        line-height: 1.4;
        color: var(--report-export-text-secondary);
    }

    .report-export-scope-options {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .report-export-scope-option {
        display: block;
        cursor: pointer;
    }

    .report-export-scope-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .report-export-scope-card {
        display: grid;
        gap: 0.24rem;
        min-height: 100%;
        padding: 0.75rem 0.85rem;
        border-radius: 0.85rem;
        border: 1px solid var(--report-export-border);
        background: var(--report-export-bg-secondary);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease, background 0.2s ease;
    }

    .report-export-scope-option:hover .report-export-scope-card {
        transform: translateY(-1px);
        border-color: rgba(59, 130, 246, 0.35);
        box-shadow: 0 12px 22px rgba(15, 23, 42, 0.06);
    }

    body.dark-theme .report-export-scope-option:hover .report-export-scope-card {
        box-shadow: 0 12px 22px rgba(2, 6, 23, 0.22);
    }

    .report-export-scope-input:checked + .report-export-scope-card {
        border-color: #3b82f6;
        background:
            radial-gradient(circle at top right, rgba(96, 165, 250, 0.18), transparent 52%),
            linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(14, 165, 233, 0.08));
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    body.dark-theme .report-export-scope-input:checked + .report-export-scope-card {
        background:
            radial-gradient(circle at top right, rgba(96, 165, 250, 0.14), transparent 52%),
            linear-gradient(135deg, rgba(30, 64, 175, 0.28), rgba(14, 116, 144, 0.18));
    }

    .report-export-scope-title {
        font-size: 0.86rem;
        font-weight: 700;
        color: var(--report-export-text-primary);
    }

    .report-export-scope-description {
        font-size: 0.74rem;
        line-height: 1.35;
        color: var(--report-export-text-secondary);
    }

    .report-export-hero {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem 0.9rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(59, 130, 246, 0.18);
        background:
            radial-gradient(circle at top right, rgba(96, 165, 250, 0.24), transparent 48%),
            linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(14, 165, 233, 0.12));
    }

    body.dark-theme .report-export-hero {
        border-color: rgba(96, 165, 250, 0.2);
        background:
            radial-gradient(circle at top right, rgba(96, 165, 250, 0.2), transparent 48%),
            linear-gradient(135deg, rgba(30, 64, 175, 0.32), rgba(14, 116, 144, 0.2));
    }

    .report-export-hero-icon {
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 0.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        box-shadow: 0 12px 28px rgba(37, 99, 235, 0.2);
    }

    .report-export-overline {
        margin-bottom: 0.18rem;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--report-export-focus);
    }

    .report-export-hero-copy h4 {
        margin: 0 0 0.18rem;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--report-export-text-primary);
    }

    .report-export-hero-copy p {
        margin: 0;
        font-size: 0.76rem;
        line-height: 1.4;
        color: var(--report-export-text-secondary);
    }

    .report-export-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 0.55rem;
    }

    .report-export-summary-card {
        min-width: 0;
        padding: 0.7rem 0.8rem;
        border-radius: 0.8rem;
        border: 1px solid var(--report-export-border);
        background: var(--report-export-bg-secondary);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
    }

    body.dark-theme .report-export-summary-card {
        box-shadow: inset 0 1px 0 rgba(148, 163, 184, 0.04);
    }

    .report-export-summary-label {
        display: block;
        margin-bottom: 0.24rem;
        font-size: 0.62rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--report-export-text-secondary);
    }

    .report-export-summary-value {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.35;
        color: var(--report-export-text-primary);
        word-break: break-word;
    }

    .report-export-preview-panel {
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid var(--report-export-border);
        background: var(--report-export-bg-secondary);
    }

    .report-export-preview-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.6rem;
        padding: 0.8rem 0.9rem;
        border-bottom: 1px solid var(--report-export-border);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.98), rgba(255, 255, 255, 0.94));
    }

    body.dark-theme .report-export-preview-header {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(30, 41, 59, 0.92));
    }

    .report-export-preview-header h4 {
        margin: 0 0 0.16rem;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--report-export-text-primary);
    }

    .report-export-preview-header p {
        margin: 0;
        font-size: 0.74rem;
        line-height: 1.35;
        color: var(--report-export-text-secondary);
    }

    .report-export-preview-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.32rem 0.6rem;
        border-radius: 9999px;
        background: rgba(37, 99, 235, 0.12);
        color: var(--report-export-focus);
        font-size: 0.7rem;
        font-weight: 700;
        white-space: nowrap;
    }

    body.dark-theme .report-export-preview-pill {
        background: rgba(96, 165, 250, 0.14);
        color: #93c5fd;
    }

    .report-export-document-header {
        padding: 0.75rem 0.9rem 0.7rem;
        text-align: center;
        border-bottom: 1px solid var(--report-export-border);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.94), rgba(241, 245, 249, 0.7));
    }

    body.dark-theme .report-export-document-header {
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.8));
    }

    .report-export-document-system,
    .report-export-document-title,
    .report-export-document-meta {
        margin: 0;
    }

    .report-export-document-system {
        font-size: 0.9rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--report-export-text-primary);
    }

    .report-export-document-title {
        margin-top: 0.25rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--report-export-focus);
    }

    .report-export-document-meta {
        margin-top: 0.3rem;
        font-size: 0.68rem;
        font-weight: 500;
        color: var(--report-export-text-secondary);
    }

    .report-export-preview-table-wrap {
        display: block;
        min-height: 0;
        max-height: none;
        overflow: visible;
    }

    .report-export-preview-table {
        width: 100%;
        min-width: 780px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .report-export-preview-table th,
    .report-export-preview-table td {
        padding: 0.62rem 0.8rem;
        text-align: left;
        border-bottom: 1px solid var(--report-export-border);
        vertical-align: top;
        font-size: 0.72rem;
        line-height: 1.4;
        word-break: break-word;
    }

    .report-export-preview-table th {
        position: sticky;
        top: 0;
        z-index: 1;
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--report-export-text-secondary);
        background: var(--report-export-bg-secondary);
    }

    .report-export-preview-table tbody tr:nth-child(even) {
        background: rgba(148, 163, 184, 0.05);
    }

    body.dark-theme .report-export-preview-table tbody tr:nth-child(even) {
        background: rgba(51, 65, 85, 0.32);
    }

    .report-export-preview-table tbody tr:hover {
        background: rgba(148, 163, 184, 0.06);
    }

    body.dark-theme .report-export-preview-table tbody tr:hover {
        background: rgba(51, 65, 85, 0.46);
    }

    .report-export-preview-empty {
        padding: 0.9rem 0.8rem !important;
        text-align: center;
        color: var(--report-export-text-secondary);
    }

    .report-export-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        border-top: 1px solid var(--report-export-border);
    }

    .report-export-footer-note {
        margin: 0;
        font-size: 0.72rem;
        line-height: 1.35;
        color: var(--report-export-text-secondary);
    }

    .report-export-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .report-export-btn {
        min-height: 2.45rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 0.95rem;
        border-radius: 0.75rem;
        border: 1px solid var(--report-export-border);
        background: var(--report-export-bg-secondary);
        color: var(--report-export-text-primary);
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .report-export-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }

    body.dark-theme .report-export-btn:hover {
        box-shadow: 0 10px 22px rgba(2, 6, 23, 0.24);
    }

    .report-export-btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .report-export-btn svg {
        flex-shrink: 0;
    }

    .report-export-btn-primary {
        color: #ffffff;
        border-color: transparent;
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
    }

    .report-export-btn-primary:hover {
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.24);
    }

    .report-export-btn-download {
        color: #ffffff;
        border-color: transparent;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 6px 14px rgba(5, 150, 105, 0.18);
    }

    .report-export-btn-download:hover {
        box-shadow: 0 12px 24px rgba(5, 150, 105, 0.24);
    }

    @media (max-width: 900px) {
        .report-export-scope-options {
            grid-template-columns: 1fr;
        }

        .report-export-footer {
            align-items: stretch;
        }

        .report-export-actions {
            width: 100%;
        }

        .report-export-actions .report-export-btn {
            flex: 1 1 0;
        }
    }

    @media (max-width: 640px) {
        .report-export-modal {
            padding: 0.85rem;
        }

        .report-export-header,
        .report-export-body,
        .report-export-footer {
            padding: 0.9rem;
        }

        .report-export-header {
            gap: 0.75rem;
        }

        .report-export-header-copy h3 {
            font-size: 1.08rem;
        }

        .report-export-preview-header,
        .report-export-document-header {
            padding-left: 0.8rem;
            padding-right: 0.8rem;
        }
    }
</style>
