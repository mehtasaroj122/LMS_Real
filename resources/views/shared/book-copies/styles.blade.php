<style>
    .book-copies-page { width: 100%; padding: 20px; }
    .book-copies-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 16px; }
    .book-copies-crumb { color: #64748b; font-size: 11px; margin-bottom: 4px; }
    .book-copies-title { font-size: 20px; font-weight: 700; }
    .book-copies-subtitle { color: #64748b; font-size: 12px; margin-top: 4px; }
    .book-copies-back { color: #2563eb; font-size: 12px; white-space: nowrap; }
    .book-copies-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; width: 100%; margin-bottom: 16px; }
    .book-copies-stat { padding: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; }
    .book-copies-stat-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .book-copies-stat-label { font-size: 11px; color: #64748b; }
    .book-copies-stat-icon { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 28px; width: 28px; height: 28px; border-radius: 8px; background: #eff6ff; color: #2563eb; font-size: 13px; }
    .book-copies-stat-icon.available { background: #ecfdf5; color: #059669; }
    .book-copies-stat-icon.issued { background: #eef2ff; color: #4f46e5; }
    .book-copies-stat-icon.lost { background: #fff7ed; color: #ea580c; }
    .book-copies-stat-icon.damaged { background: #fef2f2; color: #dc2626; }
    .book-copies-stat-value { font-size: 20px; font-weight: 700; margin-top: 3px; }
    .book-copies-toolbar { display: flex; align-items: end; gap: 8px; flex-wrap: wrap; width: 100%; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 12px; }
    .book-copies-toolbar label { font-size: 12px; font-weight: 600; }
    .book-copies-search { width: min(220px, 100%); flex: 0 1 220px; }
    .book-copies-control { display: block; width: 100%; padding: 7px 8px; margin-top: 5px; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; font-size: 12px; }
    .book-copies-toolbar > .book-copies-control, .book-copies-search .book-copies-control { width: auto; margin-top: 0; }
    .book-copies-search .book-copies-control { width: 100%; }
    .book-copies-button { padding: 7px 10px; border-radius: 7px; cursor: pointer; border: 1px solid #cbd5e1; background: #fff; font-size: 12px; }
    .book-copies-button.primary { background: #2563eb; color: #fff; border-color: #2563eb; }
    .book-copies-button.danger { color: #b91c1c; border-color: #fecaca; font-weight: 600; }
    .book-copies-button:disabled, .book-copies-action:disabled { cursor: not-allowed; opacity: .55; }
    .book-copies-button:focus-visible, .book-copies-action:focus-visible, .book-copies-control:focus-visible, .copy-select-checkbox:focus-visible, .book-copies-select-all-label input:focus-visible { outline: 3px solid rgba(59, 130, 246, .45); outline-offset: 2px; }
    .book-copies-entries { display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
    .book-copies-entries select { width: auto; margin-top: 0; }
    .book-copies-add { margin-left: auto; white-space: nowrap; }
    .book-copies-bulk-bar { min-height: 52px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 8px 12px; margin-bottom: 12px; border: 1px solid #dbeafe; border-radius: 10px; background: #eff6ff; }
    .book-copies-selection-count { min-width: 145px; color: #1e40af; font-size: 12px; font-weight: 700; }
    .book-copies-bulk-select .book-copies-control { width: 210px; margin: 0; }
    .book-copies-bulk-note { margin-left: auto; color: #64748b; font-size: 11px; }
    .book-copies-table-wrap { overflow: auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; }
    .book-copies-table { width: 100%; border-collapse: collapse; min-width: 1000px; font-size: 12px; }
    .book-copies-table thead { background: #1e3a8a; color: #fff; text-align: left; }
    .book-copies-table th { padding: 9px 8px; font-size: 12px; }
    .book-copies-table td { padding: 7px 8px; border-top: 1px solid #e2e8f0; }
    .book-copies-select-column, .book-copies-select-cell { width: 76px; text-align: center; }
    .book-copies-select-all-label { display: inline-flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; white-space: nowrap; }
    .book-copies-select-all-label input, .copy-select-checkbox { width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer; }
    .copy-select-checkbox:disabled { cursor: not-allowed; }
    .book-copies-protected { display: inline-flex; margin-left: 4px; color: #64748b; font-size: 10px; vertical-align: 2px; }
    .book-copies-sr-only { position: absolute !important; width: 1px !important; height: 1px !important; padding: 0 !important; margin: -1px !important; overflow: hidden !important; clip: rect(0, 0, 0, 0) !important; white-space: nowrap !important; border: 0 !important; }
    .book-copies-table tr:not(.book-copies-edit-row) > td:last-child { white-space: nowrap; }
    .book-copies-table .table-skeleton-row td { height: 38px; }
    .book-copies-table .accession { font-weight: 600; }
    .book-copies-muted { color: #64748b; }
    .book-copies-action { padding: 5px 7px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; font-size: 11px; cursor: pointer; }
    .book-copies-action.delete { border-color: #fecaca; color: #b91c1c; }
    .book-copies-edit-row { display: none; background: #f8fafc; }
    .book-copies-edit-form { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 8px; align-items: end; }
    .book-copies-edit-form input, .book-copies-edit-form select, .book-copies-edit-form textarea { width: 100%; padding: 7px; border: 1px solid #cbd5e1; border-radius: 6px; }
    .book-copies-edit-form input[readonly] { background: #e2e8f0; }
    .book-copies-edit-form .remarks { grid-column: 1 / 5; }
    .book-copies-empty { padding: 20px !important; text-align: center; color: #64748b; }
    body.dark-theme .book-copies-stat, body.dark-theme .book-copies-toolbar, body.dark-theme .book-copies-table-wrap { background: #1e293b; border-color: #334155; }
    body.dark-theme .book-copies-bulk-bar { background: #172554; border-color: #1e3a8a; }
    body.dark-theme .book-copies-selection-count { color: #bfdbfe; }
    body.dark-theme .book-copies-control, body.dark-theme .book-copies-button, body.dark-theme .book-copies-action { background: #0f172a; border-color: #475569; color: #e2e8f0; }
    body.dark-theme .book-copies-button.primary { background: #2563eb; border-color: #2563eb; color: #fff; }
    body.dark-theme .book-copies-edit-row { background: #172033; }
    body.dark-theme .book-copies-stat-icon { background: #1e3a5f; color: #93c5fd; }
    body.dark-theme .book-copies-stat-icon.available { background: #064e3b; color: #6ee7b7; }
    body.dark-theme .book-copies-stat-icon.issued { background: #312e81; color: #c7d2fe; }
    body.dark-theme .book-copies-stat-icon.lost { background: #7c2d12; color: #fdba74; }
    body.dark-theme .book-copies-stat-icon.damaged { background: #7f1d1d; color: #fca5a5; }
    .action-feedback-confirm-detail { white-space: pre-line; text-transform: none; letter-spacing: normal; line-height: 1.65; }
    @media (max-width: 760px) { .book-copies-page { padding: 16px; } .book-copies-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } .book-copies-bulk-bar { align-items: stretch; } .book-copies-selection-count, .book-copies-bulk-select, .book-copies-bulk-select .book-copies-control, .book-copies-bulk-note { width: 100%; } .book-copies-bulk-note { margin-left: 0; } }
</style>
