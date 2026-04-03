@extends('Staff.layouts.app')

@section('title', 'Issue Book')

@push('styles')
<style>
    /* Form Elements */
    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    body.dark-theme .form-label {
        color: #e2e8f0;
    }

    .text-danger {
        color: #dc2626;
    }

    body.dark-theme .text-danger {
        color: #f87171;
    }

    .text-success {
        color: #16a34a;
    }

    body.dark-theme .text-success {
        color: #4ade80;
    }

    /* Search Input Styling */
    .search-container {
        position: relative;
        width: 100%;
    }

    .search-input {
        width: 100%;
        padding: 8px 10px 8px 32px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        background-color: #ffffff;
        color: #0f172a;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    body.dark-theme .search-input {
        background-color: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
    }

    body.dark-theme .search-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        color: #6b7280;
    }

    body.dark-theme .search-icon {
        color: #94a3b8;
    }

    /* Search Results Dropdown */
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 280px;
        overflow-y: auto;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        margin-top: 3px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        z-index: 50;
        display: none;
    }

    body.dark-theme .search-results {
        background-color: #1e293b;
        border-color: #334155;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
    }

    .result-item {
        padding: 8px 10px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f3f4f6;
    }

    body.dark-theme .result-item {
        border-bottom-color: #334155;
    }

    .result-item:hover {
        background-color: #f3f4f6;
    }

    body.dark-theme .result-item:hover {
        background-color: #2d3748;
    }

    .result-item:last-child {
        border-bottom: none;
    }

    .result-item.active {
        background-color: #eff6ff;
    }

    body.dark-theme .result-item.active {
        background-color: #1e3a8a;
    }

    .result-title {
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
    }

    body.dark-theme .result-title {
        color: #f3f4f6;
    }

    .result-subtitle {
        font-size: 12px;
        color: #6b7280;
    }

    body.dark-theme .result-subtitle {
        color: #9ca3af;
    }

    /* Selected Books List */
    .selected-books-list {
        margin-top: 16px;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px;
    }

    body.dark-theme .selected-books-list {
        border-color: #334155;
    }

    .selected-book-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        background-color: #f9fafb;
        border-radius: 6px;
        margin-bottom: 6px;
    }

    body.dark-theme .selected-book-item {
        background-color: #1e293b;
    }

    .selected-book-item:last-child {
        margin-bottom: 0;
    }

    .book-info {
        flex: 1;
    }

    .book-title {
        font-weight: 600;
        font-size: 14px;
        color: #111827;
    }

    body.dark-theme .book-title {
        color: #f3f4f6;
    }

    .book-details {
        font-size: 12px;
        color: #6b7280;
        margin-top: 2px;
    }

    body.dark-theme .book-details {
        color: #9ca3af;
    }

    .remove-book {
        color: #dc2626;
        cursor: pointer;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 4px;
        transition: all 0.2s ease;
        background: none;
        border: none;
    }

    .remove-book:hover {
        background-color: #fee2e2;
    }

    body.dark-theme .remove-book:hover {
        background-color: #7f1d1d;
        color: #fca5a5;
    }

    /* Detail Rows */
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
    }

    .detail-label {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    .detail-value {
        font-size: 14px;
        color: #0f172a;
        font-weight: 600;
        text-align: right;
    }

    body.dark-theme .detail-label {
        color: #94a3b8;
    }

    body.dark-theme .detail-value {
        color: #f1f5f9;
    }

    /* Progress Bar */
    .progress-bar {
        height: 8px;
        background-color: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 4px;
    }

    body.dark-theme .progress-bar {
        background-color: #374151;
    }

    .progress-fill {
        height: 100%;
        background-color: #2563eb;
        transition: width 0.3s ease;
    }

    body.dark-theme .progress-fill {
        background-color: #3b82f6;
    }

    /* Clear Button */
    .clear-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        padding: 4px 12px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        white-space: nowrap;
    }

    body.dark-theme .clear-btn {
        background-color: #334155;
        border-color: #475569;
        color: #cbd5e1;
    }

    body.dark-theme .clear-btn:hover {
        background-color: #475569;
    }

    /* Button */
    .btn-primary {
        background-color: #2563eb;
        color: #ffffff;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        width: 100%;
    }

    .btn-primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .btn-primary:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    body.dark-theme .btn-primary {
        background-color: #1e40af;
    }

    body.dark-theme .btn-primary:hover {
        background-color: #1e3a8a;
    }

    body.dark-theme .btn-primary:disabled {
        background-color: #475569;
    }

    /* Info Box */
    .info-box {
        background-color: #eff6ff;
        border-radius: 8px;
        padding: 12px;
        margin-top: 16px;
    }

    body.dark-theme .info-box {
        background-color: #1e3a8a;
    }

    /* Card Styles */
    .card {
        border-radius: 0.75rem;
        padding: 1.5rem;
    }

    body.light-theme .card {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
    }

    body.dark-theme .card {
        background-color: #1e293b;
        border: 1px solid #334155;
    }

    body.issue-modal-open {
        overflow: hidden;
    }

    .issue-confirm-modal[hidden] {
        display: none;
    }

    .issue-confirm-modal {
        position: fixed;
        inset: 0;
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .issue-confirm-backdrop {
        position: absolute;
        inset: 0;
        border: none;
        background: rgba(15, 23, 42, 0.58);
        cursor: pointer;
    }

    .issue-confirm-dialog {
        position: relative;
        width: min(520px, 100%);
        max-height: calc(100vh - 2.5rem);
        overflow-y: auto;
        border-radius: 1.05rem;
        padding: 1.1rem 1.15rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.25);
    }

    body.light-theme .issue-confirm-dialog {
        background-color: #ffffff;
    }

    body.dark-theme .issue-confirm-dialog {
        background-color: #1e293b;
        border-color: #334155;
        box-shadow: 0 30px 70px rgba(2, 6, 23, 0.5);
    }

    .issue-confirm-topbar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.9rem;
        margin-bottom: 0.9rem;
    }

    .issue-confirm-header {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        flex: 1;
    }

    .issue-confirm-icon {
        width: 2.65rem;
        height: 2.65rem;
        border-radius: 0.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #2563eb;
        background-color: #dbeafe;
    }

    body.dark-theme .issue-confirm-icon {
        color: #93c5fd;
        background-color: rgba(37, 99, 235, 0.2);
    }

    .issue-confirm-close {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 9999px;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        color: #64748b;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .issue-confirm-close:hover {
        color: #0f172a;
        border-color: #cbd5e1;
        background-color: #f8fafc;
    }

    body.dark-theme .issue-confirm-close {
        background-color: #0f172a;
        color: #94a3b8;
        border-color: #334155;
    }

    body.dark-theme .issue-confirm-close:hover {
        color: #f8fafc;
        border-color: #475569;
        background-color: #1e293b;
    }

    .issue-confirm-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 0.15rem;
    }

    body.dark-theme .issue-confirm-title {
        color: #f8fafc;
    }

    .issue-confirm-subtitle {
        font-size: 0.82rem;
        line-height: 1.45;
        color: #64748b;
        margin: 0;
    }

    body.dark-theme .issue-confirm-subtitle {
        color: #94a3b8;
    }

    .issue-confirm-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.65rem;
        margin-bottom: 0.85rem;
    }

    .issue-confirm-stat {
        border-radius: 0.8rem;
        border: 1px solid #e5e7eb;
        padding: 0.72rem 0.8rem;
    }

    .issue-confirm-stat.student {
        grid-column: 1 / -1;
    }

    body.light-theme .issue-confirm-stat {
        background-color: #f8fafc;
    }

    body.dark-theme .issue-confirm-stat {
        background-color: #0f172a;
        border-color: #334155;
    }

    .issue-confirm-stat-label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.18rem;
    }

    body.dark-theme .issue-confirm-stat-label {
        color: #94a3b8;
    }

    .issue-confirm-stat-value {
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1.3;
        color: #0f172a;
        word-break: break-word;
    }

    body.dark-theme .issue-confirm-stat-value {
        color: #f8fafc;
    }

    .issue-confirm-note {
        border-radius: 0.8rem;
        padding: 0.7rem 0.85rem;
        margin-bottom: 0.85rem;
        font-size: 0.79rem;
        line-height: 1.5;
        color: #475569;
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
    }

    body.dark-theme .issue-confirm-note {
        color: #cbd5e1;
        background-color: rgba(30, 64, 175, 0.16);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .issue-confirm-books {
        margin-bottom: 0.9rem;
        border-radius: 0.9rem;
        border: 1px solid #e5e7eb;
        padding: 0.85rem;
    }

    body.light-theme .issue-confirm-books {
        background-color: #f8fafc;
    }

    body.dark-theme .issue-confirm-books {
        background-color: #0f172a;
        border-color: #334155;
    }

    .issue-confirm-books-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.55rem;
    }

    .issue-confirm-books-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
    }

    body.dark-theme .issue-confirm-books-title {
        color: #f8fafc;
    }

    .issue-confirm-books-count {
        font-size: 0.78rem;
        font-weight: 600;
        color: #2563eb;
    }

    body.dark-theme .issue-confirm-books-count {
        color: #93c5fd;
    }

    .issue-confirm-book-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        max-height: 160px;
        overflow-y: auto;
    }

    .issue-confirm-book-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
    }

    body.light-theme .issue-confirm-book-item {
        background-color: #ffffff;
    }

    body.dark-theme .issue-confirm-book-item {
        background-color: #0f172a;
        border-color: #334155;
    }

    .issue-confirm-book-index {
        width: 1.45rem;
        height: 1.45rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.68rem;
        font-weight: 700;
        color: #2563eb;
        background-color: #dbeafe;
        flex-shrink: 0;
    }

    body.dark-theme .issue-confirm-book-index {
        color: #93c5fd;
        background-color: rgba(37, 99, 235, 0.2);
    }

    .issue-confirm-book-copy {
        min-width: 0;
    }

    .issue-confirm-book-name {
        font-size: 0.84rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0.15rem;
    }

    body.dark-theme .issue-confirm-book-name {
        color: #f8fafc;
    }

    .issue-confirm-book-meta {
        font-size: 0.73rem;
        line-height: 1.4;
        color: #64748b;
    }

    body.dark-theme .issue-confirm-book-meta {
        color: #94a3b8;
    }

    .issue-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 0.1rem;
    }

    .issue-confirm-btn {
        border: none;
        border-radius: 0.7rem;
        padding: 0.72rem 1rem;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
    }

    .issue-confirm-btn.secondary {
        background-color: #e2e8f0;
        color: #334155;
    }

    .issue-confirm-btn.secondary:hover {
        background-color: #cbd5e1;
    }

    body.dark-theme .issue-confirm-btn.secondary {
        background-color: #334155;
        color: #e2e8f0;
    }

    body.dark-theme .issue-confirm-btn.secondary:hover {
        background-color: #475569;
    }

    .issue-confirm-btn.primary {
        background-color: #2563eb;
        color: #ffffff;
        min-width: 128px;
    }

    .issue-confirm-btn.primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
    }

    body.dark-theme .issue-confirm-btn.primary {
        background-color: #1e40af;
    }

    body.dark-theme .issue-confirm-btn.primary:hover {
        background-color: #1e3a8a;
    }

    .issue-confirm-btn:disabled {
        opacity: 0.72;
        cursor: not-allowed;
    }

    @media (max-width: 640px) {
        .issue-confirm-modal {
            padding: 1rem;
        }

        .issue-confirm-topbar {
            gap: 0.75rem;
        }

        .issue-confirm-summary {
            grid-template-columns: 1fr;
        }

        .issue-confirm-actions {
            flex-direction: column-reverse;
        }

        .issue-confirm-btn {
            width: 100%;
        }
    }

    /* Layout */
    .grid-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    @media (min-width: 1024px) {
        .grid-container {
            grid-template-columns: 1fr 1fr;
        }
    }

    /* Section Headers */
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    body.dark-theme .section-title {
        color: #f1f5f9;
    }

    .section-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 1.5rem;
    }

    body.dark-theme .section-subtitle {
        color: #94a3b8;
    }

    .section-header {
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 1rem;
    }

    body.dark-theme .section-header {
        color: #e2e8f0;
    }

    .result-item-body {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .result-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }

    .result-title-block {
        min-width: 0;
    }

    .result-badges {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 6px;
        flex-shrink: 0;
    }

    .result-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.02em;
        background-color: #dbeafe;
        color: #1d4ed8;
    }

    .result-badge.restricted {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .result-badge.override {
        background-color: #fef3c7;
        color: #b45309;
    }

    .result-badge-icon {
        font-size: 10px;
        line-height: 1;
    }

    body.dark-theme .result-badge {
        background-color: rgba(30, 64, 175, 0.35);
        color: #bfdbfe;
    }

    body.dark-theme .result-badge.restricted {
        background-color: rgba(127, 29, 29, 0.9);
        color: #fecaca;
    }

    body.dark-theme .result-badge.override {
        background-color: rgba(120, 53, 15, 0.95);
        color: #fde68a;
    }

    .book-count {
        font-weight: 700;
        color: #1d4ed8;
    }

    .book-count.full {
        color: #dc2626;
    }

    body.dark-theme .book-count {
        color: #93c5fd;
    }

    body.dark-theme .book-count.full {
        color: #fca5a5;
    }

    .detail-stack {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
    }

    .detail-meta {
        font-size: 11px;
        color: #64748b;
        font-weight: 500;
    }

    body.dark-theme .detail-meta {
        color: #94a3b8;
    }

    .privilege-info-card {
        position: relative;
        overflow: hidden;
        border-radius: 18px;
        padding: 20px;
        border: 1px solid rgba(59, 130, 246, 0.18);
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 52%, #f8fbff 100%);
        box-shadow: 0 20px 45px -28px rgba(37, 99, 235, 0.55);
        color: #0f172a;
    }

    .privilege-info-card.is-visible {
        animation: privilegeCardFade 0.35s ease;
    }

    .privilege-info-card.is-restricted {
        border-color: rgba(239, 68, 68, 0.2);
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 52%, #fff7f7 100%);
        box-shadow: 0 20px 45px -28px rgba(220, 38, 38, 0.5);
    }

    body.dark-theme .privilege-info-card {
        border-color: rgba(96, 165, 250, 0.24);
        background: linear-gradient(135deg, rgba(30, 64, 175, 0.32) 0%, rgba(15, 23, 42, 0.96) 100%);
        color: #e2e8f0;
    }

    body.dark-theme .privilege-info-card.is-restricted {
        border-color: rgba(248, 113, 113, 0.24);
        background: linear-gradient(135deg, rgba(127, 29, 29, 0.6) 0%, rgba(15, 23, 42, 0.96) 100%);
    }

    .privilege-info-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
    }

    .privilege-eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #2563eb;
        margin-bottom: 6px;
    }

    .privilege-info-card.is-restricted .privilege-eyebrow {
        color: #dc2626;
    }

    body.dark-theme .privilege-eyebrow {
        color: #93c5fd;
    }

    body.dark-theme .privilege-info-card.is-restricted .privilege-eyebrow {
        color: #fca5a5;
    }

    .privilege-title {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    body.dark-theme .privilege-title {
        color: #f8fafc;
    }

    .privilege-summary {
        font-size: 13px;
        line-height: 1.6;
        color: #334155;
        max-width: 560px;
    }

    body.dark-theme .privilege-summary {
        color: #cbd5e1;
    }

    .privilege-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 9999px;
        background-color: rgba(255, 255, 255, 0.78);
        color: #166534;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid rgba(34, 197, 94, 0.18);
        white-space: nowrap;
    }

    .privilege-info-card.is-restricted .privilege-status-badge {
        color: #b91c1c;
        border-color: rgba(239, 68, 68, 0.2);
    }

    body.dark-theme .privilege-status-badge {
        background-color: rgba(15, 23, 42, 0.48);
        color: #86efac;
        border-color: rgba(74, 222, 128, 0.24);
    }

    body.dark-theme .privilege-info-card.is-restricted .privilege-status-badge {
        color: #fecaca;
        border-color: rgba(248, 113, 113, 0.24);
    }

    .privilege-status-dot {
        width: 10px;
        height: 10px;
        border-radius: 9999px;
        background-color: #22c55e;
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55);
        animation: privilegePulse 1.8s infinite;
    }

    .privilege-info-card.is-restricted .privilege-status-dot {
        background-color: #ef4444;
        box-shadow: none;
        animation: none;
    }

    .privilege-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .privilege-stat {
        padding: 14px;
        border-radius: 14px;
        background-color: rgba(255, 255, 255, 0.76);
        border: 1px solid rgba(148, 163, 184, 0.18);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .privilege-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px -24px rgba(15, 23, 42, 0.55);
        border-color: rgba(37, 99, 235, 0.24);
    }

    body.dark-theme .privilege-stat {
        background-color: rgba(15, 23, 42, 0.5);
        border-color: rgba(148, 163, 184, 0.16);
    }

    .privilege-stat-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 8px;
    }

    body.dark-theme .privilege-stat-label {
        color: #94a3b8;
    }

    .privilege-stat-value {
        font-size: 24px;
        line-height: 1.1;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    body.dark-theme .privilege-stat-value {
        color: #f8fafc;
    }

    .privilege-stat-meta {
        font-size: 12px;
        color: #475569;
        line-height: 1.5;
    }

    body.dark-theme .privilege-stat-meta {
        color: #cbd5e1;
    }

    .privilege-counter-board {
        margin-top: 16px;
        padding: 16px;
        border-radius: 16px;
        background-color: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    body.dark-theme .privilege-counter-board {
        background-color: rgba(15, 23, 42, 0.48);
        border-color: rgba(148, 163, 184, 0.16);
    }

    .privilege-counter-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: flex-start;
    }

    .privilege-counter-title {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    body.dark-theme .privilege-counter-title {
        color: #f8fafc;
    }

    .privilege-counter-subtitle {
        font-size: 12px;
        color: #64748b;
    }

    body.dark-theme .privilege-counter-subtitle {
        color: #94a3b8;
    }

    .privilege-counter-pill {
        padding: 6px 10px;
        border-radius: 9999px;
        background-color: #dbeafe;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .privilege-info-card.is-restricted .privilege-counter-pill {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    body.dark-theme .privilege-counter-pill {
        background-color: rgba(30, 64, 175, 0.4);
        color: #bfdbfe;
    }

    .counter-slot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(46px, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .counter-slot {
        min-height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 700;
        border: 1px dashed rgba(37, 99, 235, 0.24);
        background-color: rgba(255, 255, 255, 0.64);
        color: #2563eb;
    }

    .counter-slot.filled {
        border-style: solid;
        border-color: transparent;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 14px 30px -24px rgba(37, 99, 235, 0.8);
    }

    body.dark-theme .counter-slot {
        background-color: rgba(15, 23, 42, 0.62);
        color: #93c5fd;
        border-color: rgba(96, 165, 250, 0.3);
    }

    .counter-slot-empty {
        grid-column: 1 / -1;
        border-radius: 12px;
        padding: 14px;
        text-align: center;
        font-size: 12px;
        color: #64748b;
        background-color: rgba(255, 255, 255, 0.58);
        border: 1px dashed rgba(148, 163, 184, 0.25);
    }

    .privilege-warning-banner {
        display: none;
        align-items: flex-start;
        gap: 10px;
        margin-top: 16px;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid rgba(239, 68, 68, 0.16);
        background-color: rgba(255, 255, 255, 0.78);
        color: #991b1b;
    }

    .privilege-warning-icon {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .privilege-warning-title {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .privilege-warning-copy {
        font-size: 12px;
        line-height: 1.6;
    }

    .privilege-details-toggle {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 16px;
        padding: 12px 14px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 14px;
        background-color: rgba(255, 255, 255, 0.68);
        color: #0f172a;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }

    body.dark-theme .privilege-details-toggle {
        background-color: rgba(15, 23, 42, 0.44);
        color: #f8fafc;
        border-color: rgba(148, 163, 184, 0.16);
    }

    .privilege-details-chevron {
        width: 16px;
        height: 16px;
        transition: transform 0.25s ease;
    }

    .privilege-details-toggle[aria-expanded="true"] .privilege-details-chevron {
        transform: rotate(180deg);
    }

    .privilege-details-panel {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transform: translateY(-8px);
        transition: max-height 0.3s ease, opacity 0.25s ease, transform 0.25s ease, margin-top 0.25s ease;
        margin-top: 0;
    }

    .privilege-details-panel.open {
        max-height: 340px;
        opacity: 1;
        transform: translateY(0);
        margin-top: 12px;
    }

    .privilege-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .privilege-detail-item {
        padding: 14px;
        border-radius: 14px;
        background-color: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    .privilege-detail-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 8px;
    }

    .privilege-detail-value {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
    }

    .privilege-detail-meta {
        font-size: 12px;
        color: #475569;
        line-height: 1.5;
    }

    .privilege-toast-container {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 9995;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }

    .privilege-toast {
        min-width: 260px;
        max-width: 340px;
        padding: 12px 14px;
        border-radius: 14px;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 18px 30px -24px rgba(15, 23, 42, 0.7);
        font-size: 13px;
        line-height: 1.5;
        border: 1px solid rgba(255, 255, 255, 0.12);
        pointer-events: auto;
        animation: privilegeToastIn 0.24s ease;
    }

    @keyframes privilegePulse {
        0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55); }
        70% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    @keyframes privilegeCardFade {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes privilegeToastIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .transaction-visually-hidden {
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

    .transaction-toast-container {
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

    .transaction-toast {
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
        animation: transactionToastIn 0.24s ease;
    }

    .transaction-toast.is-leaving {
        animation: transactionToastOut 0.18s ease forwards;
    }

    .transaction-toast.success {
        background: linear-gradient(135deg, rgba(22, 163, 74, 0.96), rgba(5, 150, 105, 0.94));
    }

    .transaction-toast.error {
        background: linear-gradient(135deg, rgba(220, 38, 38, 0.97), rgba(190, 24, 93, 0.94));
    }

    .transaction-toast.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.97), rgba(217, 119, 6, 0.94));
    }

    .transaction-toast.info {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.97), rgba(79, 70, 229, 0.94));
    }

    .transaction-toast-icon {
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

    .transaction-toast-copy {
        min-width: 0;
    }

    .transaction-toast-title {
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
    }

    .transaction-toast-message {
        margin-top: 2px;
        font-size: 13px;
        line-height: 1.5;
        color: rgba(255, 255, 255, 0.96);
    }

    .transaction-toast-detail {
        margin-top: 6px;
        font-size: 11px;
        line-height: 1.4;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.78);
    }

    .transaction-toast-close {
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

    .transaction-toast-close:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
    }

    .transaction-toast-progress {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 4px;
        background: rgba(255, 255, 255, 0.18);
    }

    .transaction-toast-progress::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.92);
        transform-origin: left center;
        animation: transactionToastProgress 4.2s linear forwards;
    }

    .transaction-confirm-overlay {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(8px);
        z-index: 2050;
    }

    .transaction-confirm-overlay.active {
        display: flex;
    }

    .transaction-confirm-card {
        width: min(440px, 100%);
        padding: 24px;
        border-radius: 24px;
        border: 1px solid;
        box-shadow: 0 28px 60px rgba(15, 23, 42, 0.26);
        transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
    }

    body.light-theme .transaction-confirm-card {
        background: rgba(255, 255, 255, 0.97);
        border-color: rgba(226, 232, 240, 0.95);
        color: #0f172a;
    }

    body.dark-theme .transaction-confirm-card {
        background: rgba(15, 23, 42, 0.96);
        border-color: rgba(71, 85, 105, 0.88);
        color: #e2e8f0;
    }

    .transaction-confirm-header {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }

    .transaction-confirm-icon {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 22px;
    }

    .transaction-confirm-icon.primary {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1d4ed8;
    }

    body.dark-theme .transaction-confirm-icon.primary {
        background: linear-gradient(135deg, rgba(30, 58, 138, 0.85), rgba(30, 64, 175, 0.55));
        color: #bfdbfe;
    }

    .transaction-confirm-icon.success {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
    }

    body.dark-theme .transaction-confirm-icon.success {
        background: linear-gradient(135deg, rgba(20, 83, 45, 0.85), rgba(21, 128, 61, 0.55));
        color: #bbf7d0;
    }

    .transaction-confirm-icon.warning {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #b45309;
    }

    body.dark-theme .transaction-confirm-icon.warning {
        background: linear-gradient(135deg, rgba(120, 53, 15, 0.85), rgba(180, 83, 9, 0.55));
        color: #fcd34d;
    }

    .transaction-confirm-icon.danger {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #b91c1c;
    }

    body.dark-theme .transaction-confirm-icon.danger {
        background: linear-gradient(135deg, rgba(127, 29, 29, 0.85), rgba(127, 29, 29, 0.55));
        color: #fca5a5;
    }

    .transaction-confirm-title {
        margin: 2px 0 6px;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.3;
    }

    .transaction-confirm-copy p {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
    }

    body.light-theme .transaction-confirm-copy p {
        color: #475569;
    }

    body.dark-theme .transaction-confirm-copy p {
        color: #94a3b8;
    }

    .transaction-confirm-detail {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    body.light-theme .transaction-confirm-detail {
        background: #f8fafc;
        color: #475569;
    }

    body.dark-theme .transaction-confirm-detail {
        background: rgba(30, 41, 59, 0.85);
        color: #cbd5e1;
    }

    .transaction-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
    }

    .transaction-confirm-btn {
        appearance: none;
        border: 1px solid transparent;
        border-radius: 12px;
        min-width: 132px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .transaction-confirm-btn:hover {
        transform: translateY(-1px);
    }

    .transaction-confirm-btn.secondary {
        background: transparent;
    }

    body.light-theme .transaction-confirm-btn.secondary {
        border-color: #cbd5e1;
        color: #334155;
    }

    body.dark-theme .transaction-confirm-btn.secondary {
        border-color: #475569;
        color: #e2e8f0;
    }

    .transaction-confirm-btn.primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, 0.24);
    }

    .transaction-confirm-btn.success {
        background: linear-gradient(135deg, #16a34a, #059669);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(22, 163, 74, 0.24);
    }

    .transaction-confirm-btn.warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(217, 119, 6, 0.24);
    }

    .transaction-confirm-btn.danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
        box-shadow: 0 12px 24px rgba(220, 38, 38, 0.24);
    }

    .transaction-confirm-btn:disabled {
        opacity: 0.7;
        cursor: wait;
        transform: none;
    }

    @keyframes transactionToastIn {
        from {
            opacity: 0;
            transform: translate3d(20px, -8px, 0) scale(0.98);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @keyframes transactionToastOut {
        from {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }

        to {
            opacity: 0;
            transform: translate3d(18px, -4px, 0) scale(0.98);
        }
    }

    @keyframes transactionToastProgress {
        from {
            transform: scaleX(1);
        }

        to {
            transform: scaleX(0);
        }
    }

    .issue-alert-stack {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
        pointer-events: none;
    }

    .issue-alert {
        position: relative;
        width: min(360px, calc(100vw - 32px));
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 12px;
        align-items: start;
        padding: 14px 14px 16px;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        background-color: rgba(255, 255, 255, 0.98);
        box-shadow: 0 22px 48px -28px rgba(15, 23, 42, 0.55);
        opacity: 0;
        transform: translateY(-8px) scale(0.98);
        transition: opacity 0.22s ease, transform 0.22s ease;
        overflow: hidden;
        pointer-events: auto;
        backdrop-filter: blur(10px);
    }

    body.dark-theme .issue-alert {
        background-color: rgba(30, 41, 59, 0.98);
        border-color: #334155;
        box-shadow: 0 22px 48px -28px rgba(2, 6, 23, 0.72);
    }

    .issue-alert.show {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

    .issue-alert-icon {
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 800;
        flex-shrink: 0;
    }

    .issue-alert-body {
        min-width: 0;
    }

    .issue-alert-title {
        font-size: 0.95rem;
        font-weight: 700;
        line-height: 1.3;
        margin: 0 0 0.2rem;
        color: #0f172a;
    }

    body.dark-theme .issue-alert-title {
        color: #f8fafc;
    }

    .issue-alert-message {
        margin: 0;
        font-size: 0.82rem;
        line-height: 1.5;
        color: #64748b;
        white-space: pre-wrap;
        word-break: break-word;
    }

    body.dark-theme .issue-alert-message {
        color: #cbd5e1;
    }

    .issue-alert-close {
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 9999px;
        border: none;
        background: transparent;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.2s ease, color 0.2s ease;
        margin: -0.15rem -0.2rem 0 0;
    }

    .issue-alert-close:hover {
        background-color: #f1f5f9;
        color: #475569;
    }

    body.dark-theme .issue-alert-close:hover {
        background-color: #334155;
        color: #e2e8f0;
    }

    .issue-alert-progress {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        transform-origin: left;
        animation: issueAlertProgress linear forwards;
    }

    .issue-alert.issue-alert-success .issue-alert-progress {
        animation-duration: 2s;
    }

    .issue-alert-success {
        border-color: #bbf7d0;
    }

    body.dark-theme .issue-alert-success {
        border-color: rgba(34, 197, 94, 0.35);
    }

    .issue-alert-success .issue-alert-icon {
        color: #059669;
        background-color: #d1fae5;
    }

    body.dark-theme .issue-alert-success .issue-alert-icon {
        color: #6ee7b7;
        background-color: rgba(16, 185, 129, 0.18);
    }

    .issue-alert-success .issue-alert-title {
        color: #059669;
    }

    body.dark-theme .issue-alert-success .issue-alert-title {
        color: #6ee7b7;
    }

    .issue-alert-success .issue-alert-progress {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .issue-alert-warning {
        border-color: #fde68a;
    }

    body.dark-theme .issue-alert-warning {
        border-color: rgba(245, 158, 11, 0.35);
    }

    .issue-alert-warning .issue-alert-icon {
        color: #b45309;
        background-color: #fef3c7;
    }

    body.dark-theme .issue-alert-warning .issue-alert-icon {
        color: #fbbf24;
        background-color: rgba(245, 158, 11, 0.16);
    }

    .issue-alert-warning .issue-alert-title {
        color: #b45309;
    }

    body.dark-theme .issue-alert-warning .issue-alert-title {
        color: #fbbf24;
    }

    .issue-alert-error {
        border-color: #fecaca;
    }

    body.dark-theme .issue-alert-error {
        border-color: rgba(239, 68, 68, 0.35);
    }

    .issue-alert-error .issue-alert-icon {
        color: #dc2626;
        background-color: #fee2e2;
    }

    body.dark-theme .issue-alert-error .issue-alert-icon {
        color: #fca5a5;
        background-color: rgba(239, 68, 68, 0.16);
    }

    .issue-alert-error .issue-alert-title {
        color: #dc2626;
    }

    body.dark-theme .issue-alert-error .issue-alert-title {
        color: #fca5a5;
    }

    .issue-alert-info {
        border-color: #bfdbfe;
    }

    body.dark-theme .issue-alert-info {
        border-color: rgba(59, 130, 246, 0.35);
    }

    .issue-alert-info .issue-alert-icon {
        color: #2563eb;
        background-color: #dbeafe;
    }

    body.dark-theme .issue-alert-info .issue-alert-icon {
        color: #93c5fd;
        background-color: rgba(59, 130, 246, 0.16);
    }

    .issue-alert-info .issue-alert-title {
        color: #2563eb;
    }

    body.dark-theme .issue-alert-info .issue-alert-title {
        color: #93c5fd;
    }

    @keyframes issueAlertProgress {
        from { transform: scaleX(1); }
        to { transform: scaleX(0); }
    }

    @media (max-width: 1024px) {
        .privilege-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .result-title-row,
        .privilege-info-header,
        .privilege-counter-header {
            flex-direction: column;
        }

        .result-badges {
            justify-content: flex-start;
        }

        .privilege-stats-grid,
        .privilege-detail-grid {
            grid-template-columns: 1fr;
        }

        .privilege-toast-container {
            left: 16px;
            right: 16px;
            bottom: 16px;
        }

        .privilege-toast {
            min-width: 0;
            max-width: none;
        }

        .issue-alert-stack {
            left: 16px;
            right: 16px;
            top: 16px;
        }

        .issue-alert {
            width: 100%;
        }
    }

    @media (max-width: 1024px) {
        .transaction-toast-container {
            top: 76px;
            right: 16px;
            left: 16px;
            width: auto;
        }

        .transaction-confirm-actions {
            flex-wrap: wrap;
        }

        .transaction-confirm-card {
            padding: 22px;
        }

        .transaction-confirm-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div>
    <div>
        <h1 class="section-title">Issue Book</h1>
        <p class="section-subtitle">Issue multiple books to a student (Max 5 books)</p>

        <div class="grid-container">
            <!-- Issue Form -->
            <div class="card">
                <h2 class="section-header">Issue Form</h2>

                <form id="issueForm" autocomplete="off">
                    <!-- Search Student -->
                    <div class="mb-5">
                        <label class="form-label">
                            Search Student<span class="text-danger">*</span>
                        </label>
                        <div class="search-container">
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchStudent" class="search-input" placeholder="Search by name, student ID, or email..." autocomplete="off" required>
                            <button type="button" id="clearStudentBtn" class="clear-btn" onclick="clearStudentSelection()" style="display: none;">Clear</button>
                            <div class="search-results" id="studentResults"></div>
                        </div>
                        <input type="hidden" id="selectedStudentId">
                    </div>

                    <!-- Search Books -->
                    <div class="mb-4">
                        <label class="form-label">
                            Search Books<span class="text-danger">*</span>
                        </label>
                        <div class="search-container">
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" id="searchBook" class="search-input" placeholder="Select a student first..." autocomplete="off" disabled>
                            <div class="search-results" id="bookResults"></div>
                        </div>
                    </div>

                    <!-- Selected Books List -->
                    <div class="selected-books-list" id="selectedBooksList" style="display: none;">
                        <div class="mb-2 text-sm text-secondary" id="selectedBooksHeader">Selected Books</div>
                        <div id="selectedBooksContainer"></div>
                    </div>

                    <!-- Issue Button -->
                    <button type="submit" class="btn-primary" id="issueButton" disabled>
                        Issue Books (0/5)
                    </button>
                </form>
            </div>

            <!-- Selected Details -->
            <div class="space-y-6">
                <!-- Selected Student Details -->
                <div class="card" id="selectedStudentCard" style="display: none;">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h3 class="text-base font-semibold text-primary">Student Information</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value" id="studentName">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Roll No:</span>
                            <span class="detail-value" id="studentID">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Department:</span>
                            <span class="detail-value" id="studentDepartment">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value" id="studentEmail">-</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Books Issued:</span>
                            <span class="detail-value" id="studentIssued">0 / 5</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="issuedProgress" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div class="privilege-info-card" id="privilegeInfoCard" style="display: none;" aria-live="polite">
                    <div class="privilege-info-header">
                        <div>
                            <div class="privilege-eyebrow">Issue Privileges</div>
                            <h3 class="privilege-title" id="privilegeCardHeading">Effective Borrowing Access</h3>
                            <p class="privilege-summary" id="privilegeSummaryText">
                                Select a student to review borrowing eligibility, issue duration, and remaining capacity.
                            </p>
                        </div>
                        <span class="privilege-status-badge" id="privilegeStatusBadge" role="status" aria-live="polite">
                            <span class="privilege-status-dot" aria-hidden="true"></span>
                            <span id="privilegeStatusText">Allowed</span>
                        </span>
                    </div>

                    <div class="privilege-stats-grid">
                        <div class="privilege-stat">
                            <div class="privilege-stat-label">Max Books</div>
                            <div class="privilege-stat-value" id="privilegeMaxBooksValue">-</div>
                            <div class="privilege-stat-meta" id="privilegeMaxBooksMeta">Effective issue ceiling</div>
                        </div>
                        <div class="privilege-stat">
                            <div class="privilege-stat-label">Duration</div>
                            <div class="privilege-stat-value" id="privilegeDurationValue">-</div>
                            <div class="privilege-stat-meta" id="privilegeDurationMeta">Standard issue period</div>
                        </div>
                        <div class="privilege-stat">
                            <div class="privilege-stat-label">Fine Rate</div>
                            <div class="privilege-stat-value" id="privilegeFineRateValue">-</div>
                            <div class="privilege-stat-meta" id="privilegeFineRateMeta">Per overdue day</div>
                        </div>
                        <div class="privilege-stat">
                            <div class="privilege-stat-label">Can Issue</div>
                            <div class="privilege-stat-value" id="privilegeCanIssueValue">-</div>
                            <div class="privilege-stat-meta" id="privilegeCanIssueMeta">Remaining today</div>
                        </div>
                    </div>

                    <div class="privilege-counter-board" aria-live="polite">
                        <div class="privilege-counter-header">
                            <div>
                                <div class="privilege-counter-title">Book Selection Slots</div>
                                <div class="privilege-counter-subtitle" id="bookCounterText">
                                    0 selected out of 0 available slots
                                </div>
                            </div>
                            <div class="privilege-counter-pill" id="bookCounterPill">0 / 0</div>
                        </div>
                        <div class="counter-slot-grid" id="bookCounterSlots" aria-label="Book selection slots"></div>
                    </div>

                    <div class="privilege-warning-banner" id="privilegeWarningBanner" role="alert">
                        <svg class="privilege-warning-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A2 2 0 004.5 20h15a2 2 0 001.71-3.14l-7.5-13a2 2 0 00-3.42 0z" />
                        </svg>
                        <div>
                            <div class="privilege-warning-title">Borrowing Restricted</div>
                            <p class="privilege-warning-copy" id="privilegeWarningText">
                                This student cannot receive new book issues right now.
                            </p>
                        </div>
                    </div>

                    <button type="button" class="privilege-details-toggle" id="privilegeDetailsToggle"
                        aria-expanded="false" aria-controls="privilegeDetailsPanel" onclick="togglePrivilegeDetails()">
                        <span id="privilegeDetailsLabel">Show Details</span>
                        <svg class="privilege-details-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div class="privilege-details-panel" id="privilegeDetailsPanel" hidden aria-hidden="true">
                        <div class="privilege-detail-grid">
                            <div class="privilege-detail-item">
                                <div class="privilege-detail-label">Setting Source</div>
                                <div class="privilege-detail-value" id="privilegeSourceValue">-</div>
                                <div class="privilege-detail-meta" id="privilegeSourceMeta">-</div>
                            </div>
                            <div class="privilege-detail-item">
                                <div class="privilege-detail-label">Books Issued</div>
                                <div class="privilege-detail-value" id="privilegeIssuedValue">-</div>
                                <div class="privilege-detail-meta" id="privilegeIssuedMeta">-</div>
                            </div>
                            <div class="privilege-detail-item">
                                <div class="privilege-detail-label">Issue Duration</div>
                                <div class="privilege-detail-value" id="privilegeDurationDetailValue">-</div>
                                <div class="privilege-detail-meta" id="privilegeDurationDetailMeta">-</div>
                            </div>
                            <div class="privilege-detail-item">
                                <div class="privilege-detail-label">Due Date Preview</div>
                                <div class="privilege-detail-value" id="privilegeDueDateValue">-</div>
                                <div class="privilege-detail-meta" id="privilegeDueDateMeta">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Books Summary -->
                <div class="card" id="availableBooksCard" style="display: none;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <h3 class="text-base font-semibold text-primary">Selected Books</h3>
                        </div>
                        <span class="text-sm text-secondary" id="selectedCount">0 books</span>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="detail-row">
                            <span class="detail-label">Total Books:</span>
                            <span class="detail-value" id="totalBooks">0</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Can Issue More:</span>
                            <span class="detail-value" id="canIssueMore">5</span>
                        </div>
                        <div class="info-box">
                            <p class="text-sm" id="issueSelectionHint">Select books for this student. The selection limit follows the effective library privilege rules.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="transactionConfirmModal" class="transaction-confirm-overlay" aria-hidden="true">
    <div class="transaction-confirm-card" role="dialog" aria-modal="true" aria-labelledby="transactionConfirmTitle">
        <div class="transaction-confirm-header">
            <div id="transactionConfirmIconWrap" class="transaction-confirm-icon primary" aria-hidden="true">
                <i id="transactionConfirmIcon" class="fas fa-check"></i>
            </div>
            <div class="transaction-confirm-copy">
                <h3 id="transactionConfirmTitle" class="transaction-confirm-title">Confirm action</h3>
                <p id="transactionConfirmMessage">Review this action before continuing.</p>
                <div id="transactionConfirmDetail" class="transaction-confirm-detail">Details</div>
            </div>
        </div>

        <div class="transaction-confirm-actions">
            <button type="button" id="cancelTransactionConfirm" class="transaction-confirm-btn secondary">Cancel</button>
            <button type="button" id="confirmTransactionConfirm" class="transaction-confirm-btn primary">
                <i id="transactionConfirmButtonIcon" class="fas fa-check"></i>
                <span id="transactionConfirmButtonText">Confirm</span>
            </button>
        </div>
    </div>
</div>

<div id="transactionToastContainer" class="transaction-toast-container" aria-live="polite" aria-atomic="true"></div>
<div id="transactionLiveRegion" class="transaction-visually-hidden" aria-live="polite" aria-atomic="true"></div>
@endsection

@push('scripts')
<script>
    const fineSettings = @json($fineSettings);

    let selectedStudent = null;
    let selectedBooks = [];
    let studentPrivileges = null;
    let issueStudentSearchRequest = 0;

    const privilegeFields = ['max_books', 'issue_duration_days', 'per_day_fine', 'grace_period_days',
        'max_fine_amount', 'borrowing_allowed'
    ];

    const searchStudentInput = document.getElementById('searchStudent');
    const studentResults = document.getElementById('studentResults');
    const selectedStudentId = document.getElementById('selectedStudentId');
    const selectedStudentCard = document.getElementById('selectedStudentCard');
    const availableBooksCard = document.getElementById('availableBooksCard');
    const clearStudentBtn = document.getElementById('clearStudentBtn');
    const searchBookInput = document.getElementById('searchBook');
    const bookResults = document.getElementById('bookResults');
    const selectedBooksList = document.getElementById('selectedBooksList');
    const selectedBooksContainer = document.getElementById('selectedBooksContainer');
    const selectedBooksHeader = document.getElementById('selectedBooksHeader');
    const issueSelectionHint = document.getElementById('issueSelectionHint');
    const issueButton = document.getElementById('issueButton');
    const issueForm = document.getElementById('issueForm');
    const privilegeInfoCard = document.getElementById('privilegeInfoCard');
    const privilegeStatusBadge = document.getElementById('privilegeStatusBadge');
    const privilegeWarningBanner = document.getElementById('privilegeWarningBanner');
    const privilegeWarningText = document.getElementById('privilegeWarningText');
    const privilegeDetailsPanel = document.getElementById('privilegeDetailsPanel');
    const privilegeDetailsToggle = document.getElementById('privilegeDetailsToggle');
    const privilegeDetailsLabel = document.getElementById('privilegeDetailsLabel');
    const transactionToastIcons = {
        success: 'fas fa-check-circle',
        error: 'fas fa-times-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle',
    };

    const transactionConfirmState = {
        onConfirm: null,
    };

    const transactionConfirmModal = document.getElementById('transactionConfirmModal');
    const transactionConfirmTitle = document.getElementById('transactionConfirmTitle');
    const transactionConfirmMessage = document.getElementById('transactionConfirmMessage');
    const transactionConfirmDetail = document.getElementById('transactionConfirmDetail');
    const transactionConfirmIconWrap = document.getElementById('transactionConfirmIconWrap');
    const transactionConfirmIcon = document.getElementById('transactionConfirmIcon');
    const transactionConfirmButton = document.getElementById('confirmTransactionConfirm');
    const transactionConfirmButtonIcon = document.getElementById('transactionConfirmButtonIcon');
    const transactionConfirmButtonText = document.getElementById('transactionConfirmButtonText');
    const cancelTransactionConfirm = document.getElementById('cancelTransactionConfirm');
    let isIssueSubmitting = false;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatCurrency(amount, minimumFractionDigits = 2) {
        const number = Number(amount ?? 0);
        return `₹${number.toLocaleString(undefined, {
            minimumFractionDigits,
            maximumFractionDigits: 2
        })}`;
    }

    function formatDate(dateValue) {
        if (!dateValue) return 'N/A';
        const date = new Date(dateValue);
        if (Number.isNaN(date.getTime())) return 'N/A';
        return date.toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function addDaysFromToday(days) {
        const date = new Date();
        date.setHours(0, 0, 0, 0);
        date.setDate(date.getDate() + Number(days ?? 0));
        return date;
    }

    function formatBookCount(count) {
        const total = Number(count ?? 0);
        return `${total} book${total === 1 ? '' : 's'}`;
    }

    function getIssueDueDatePreview(rules = getIssueRules()) {
        if (!rules) return 'N/A';
        return formatDate(addDaysFromToday(rules.issue_duration_days));
    }

    function escapeTransactionToastHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function announceTransactionMessage(message) {
        const liveRegion = document.getElementById('transactionLiveRegion');

        if (liveRegion) {
            liveRegion.textContent = message;
        }
    }

    function dismissTransactionToast(toast) {
        if (!toast) {
            return;
        }

        toast.classList.add('is-leaving');
        window.setTimeout(() => toast.remove(), 180);
    }

    window.showTransactionToast = function(message, type = 'info') {
        const container = document.getElementById('transactionToastContainer');

        if (!container) {
            return;
        }

        const normalizedType = ['success', 'error', 'warning', 'info'].includes(type) ? type : 'info';
        const payload = typeof message === 'object' && message !== null
            ? message
            : {
                title: normalizedType === 'error'
                    ? 'Action Failed'
                    : normalizedType === 'warning'
                        ? 'Check Required'
                        : normalizedType === 'success'
                            ? 'Success'
                            : 'Notice',
                message: String(message || ''),
            };

        const toast = document.createElement('div');
        toast.className = `transaction-toast ${normalizedType}`;
        toast.innerHTML = `
            <div class="transaction-toast-icon" aria-hidden="true">
                <i class="${escapeTransactionToastHtml(payload.icon || transactionToastIcons[normalizedType] || transactionToastIcons.info)}"></i>
            </div>
            <div class="transaction-toast-copy">
                <div class="transaction-toast-title">${escapeTransactionToastHtml(payload.title || 'Notice')}</div>
                <div class="transaction-toast-message">${escapeTransactionToastHtml(payload.message || '')}</div>
                ${payload.detail ? `<div class="transaction-toast-detail">${escapeTransactionToastHtml(payload.detail)}</div>` : ''}
            </div>
            <button type="button" class="transaction-toast-close" aria-label="Dismiss notification">
                <i class="fas fa-times"></i>
            </button>
            <span class="transaction-toast-progress" aria-hidden="true"></span>
        `;

        container.appendChild(toast);
        toast.querySelector('.transaction-toast-close')?.addEventListener('click', () => dismissTransactionToast(toast));
        announceTransactionMessage(`${payload.title || 'Notice'}. ${payload.message || ''}`.trim());
        window.setTimeout(() => dismissTransactionToast(toast), 4200);
    };

    function buildLegacyAlertPayload(title, message, type = 'info') {
        const lines = String(message ?? '')
            .replace(/\r/g, '')
            .split('\n')
            .map((line) => line.replace(/^•\s*/, '').trim())
            .filter(Boolean);

        return {
            title: title || (type === 'error' ? 'Action Failed' : 'Notice'),
            message: lines[0] || '',
            detail: lines.length > 1 ? lines.slice(1).join(' • ') : '',
            icon: transactionToastIcons[type] || transactionToastIcons.info,
        };
    }

    function closeTransactionConfirmModal() {
        if (!transactionConfirmModal) {
            return;
        }

        transactionConfirmModal.classList.remove('active');
        transactionConfirmModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        transactionConfirmState.onConfirm = null;
    }

    function openTransactionConfirmModal({
        title = 'Confirm action',
        message = 'Review this action before continuing.',
        detail = '',
        icon = 'fas fa-check',
        iconVariant = 'primary',
        confirmLabel = 'Confirm',
        confirmIcon = 'fas fa-check',
        confirmVariant = 'primary',
        onConfirm = null,
    } = {}) {
        if (!transactionConfirmModal || typeof onConfirm !== 'function') {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
            return;
        }

        transactionConfirmState.onConfirm = onConfirm;
        transactionConfirmTitle.textContent = title;
        transactionConfirmMessage.textContent = message;
        transactionConfirmDetail.textContent = detail;
        transactionConfirmDetail.hidden = !detail;
        transactionConfirmIconWrap.className = `transaction-confirm-icon ${iconVariant}`;
        transactionConfirmIcon.className = icon;
        transactionConfirmButton.className = `transaction-confirm-btn ${confirmVariant}`;
        transactionConfirmButtonIcon.className = confirmIcon;
        transactionConfirmButtonText.textContent = confirmLabel;

        transactionConfirmModal.classList.add('active');
        transactionConfirmModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        window.setTimeout(() => transactionConfirmButton?.focus(), 60);
    }

    transactionConfirmButton?.addEventListener('click', () => {
        const action = transactionConfirmState.onConfirm;
        closeTransactionConfirmModal();
        if (typeof action === 'function') {
            action();
        }
    });

    cancelTransactionConfirm?.addEventListener('click', () => {
        closeTransactionConfirmModal();
    });

    transactionConfirmModal?.addEventListener('click', (event) => {
        if (event.target === transactionConfirmModal) {
            closeTransactionConfirmModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && transactionConfirmModal?.classList.contains('active')) {
            closeTransactionConfirmModal();
        }
    });

    function setIssueSubmitting(isSubmitting) {
        isIssueSubmitting = isSubmitting;

        if (isSubmitting) {
            issueButton.disabled = true;
            issueButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Issuing...';
            return;
        }

        updateIssueButton();
    }

    async function executeIssueTransaction({
        studentId,
        studentName,
        studentRollNo,
        bookIds,
        selectedCount,
        dueDatePreview,
        remainingAfterIssue,
    }) {
        if (isIssueSubmitting) {
            return;
        }

        setIssueSubmitting(true);

        try {
            const response = await fetch('{{ route('staff.transactions.issue') }}', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    student_id: studentId,
                    book_ids: bookIds,
                })
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success) {
                showTransactionToast({
                    title: 'Books Issued',
                    message: `Issued ${selectedCount} book${selectedCount === 1 ? '' : 's'} to ${studentName}.`,
                    detail: `${studentRollNo || 'Student'} • Due ${dueDatePreview} • ${remainingAfterIssue} slot${remainingAfterIssue === 1 ? '' : 's'} left`,
                    icon: 'fas fa-book-open',
                }, 'success');
                clearStudentSelection();
            } else {
                const message = data.message || 'Unable to issue books.';
                const isPermError = message.toLowerCase().includes('borrow') || message.toLowerCase().includes('permission');
                showTransactionToast({
                    title: isPermError ? 'Borrowing Permission Denied' : 'Issue Failed',
                    message,
                    detail: studentRollNo || '',
                    icon: isPermError ? 'fas fa-user-lock' : 'fas fa-book',
                }, 'error');
            }
        } catch (error) {
            console.error('Issue submit error:', error);
            showTransactionToast({
                title: 'Transaction Error',
                message: 'Failed to issue the selected books.',
                detail: error.message,
                icon: 'fas fa-exclamation-triangle',
            }, 'error');
        } finally {
            setIssueSubmitting(false);
        }
    }

    function setPrivilegeStat(baseId, value, meta) {
        const valueElement = document.getElementById(`${baseId}Value`);
        const metaElement = document.getElementById(`${baseId}Meta`);
        if (valueElement) valueElement.textContent = value;
        if (metaElement) metaElement.textContent = meta;
    }

    function setPrivilegeDetail(baseId, value, meta) {
        const valueElement = document.getElementById(`${baseId}Value`);
        const metaElement = document.getElementById(`${baseId}Meta`);
        if (valueElement) valueElement.textContent = value;
        if (metaElement) metaElement.textContent = meta;
    }

    function createPrivilegeState(data = null) {
        const hasPrivilegeOverride = Boolean(data?.hasPrivilegeOverride);
        const rawPrivileges = {
            ...(data?.privileges ?? {})
        };

        if (!hasPrivilegeOverride && rawPrivileges.borrowing_allowed === true) {
            rawPrivileges.borrowing_allowed = null;
        }

        const defaults = {
            max_books: data?.defaults?.max_books ?? fineSettings.max_books_per_student ?? 5,
            issue_duration_days: data?.defaults?.issue_duration_days ?? fineSettings.issue_duration_days ?? 14,
            per_day_fine: data?.defaults?.per_day_fine ?? fineSettings.per_day_fine ?? 5,
            grace_period_days: data?.defaults?.grace_period_days ?? fineSettings.grace_period_days ?? 2,
            max_fine_amount: data?.defaults?.max_fine_amount ?? fineSettings.max_fine_amount ?? 500,
            borrowing_allowed: true
        };
        const effective = {
            ...defaults,
            ...(data?.effective ?? {})
        };

        effective.borrowing_allowed = data?.effective?.borrowing_allowed ??
            rawPrivileges.borrowing_allowed ?? true;

        return {
            raw: rawPrivileges,
            defaults,
            effective,
            hasPrivilegeOverride,
            overrideCount() {
                return privilegeFields.filter(field =>
                    rawPrivileges[field] !== null && rawPrivileges[field] !== undefined
                ).length;
            }
        };
    }

    function getIssueRules() {
        return studentPrivileges?.effective ?? null;
    }

    function getIssueCapacity(rules = getIssueRules(), student = selectedStudent) {
        if (!rules || !student) return 0;
        if (!rules.borrowing_allowed) return 0;
        return Math.max(0, Number(rules.max_books ?? 0) - Number(student.issued ?? 0));
    }

    function getRemainingIssueCapacity(rules = getIssueRules(), student = selectedStudent) {
        return Math.max(0, getIssueCapacity(rules, student) - selectedBooks.length);
    }

    function setBookSearchState(student = selectedStudent, rules = getIssueRules()) {
        if (!student || !rules) {
            searchBookInput.disabled = true;
            searchBookInput.placeholder = 'Select a student first...';
            return;
        }

        if (!rules.borrowing_allowed) {
            searchBookInput.disabled = true;
            searchBookInput.placeholder = 'Borrowing is restricted for this student';
            return;
        }

        searchBookInput.disabled = false;
        searchBookInput.placeholder = 'Search books to issue...';
    }

    function buildStudentResultMarkup(student) {
        const borrowingAllowed = student.borrowingAllowed !== false;
        const hasOverride = Boolean(student.hasPrivilegeOverride);
        const canIssueMore = Number(student.canIssueMore ?? Math.max(0, Number(student.maxBooks ?? 0) - Number(student.issued ?? 0)));
        const badges = [];

        if (!borrowingAllowed) {
            badges.push('<span class="result-badge restricted">Restricted</span>');
        }

        if (hasOverride) {
            badges.push('<span class="result-badge override"><span class="result-badge-icon" aria-hidden="true">&#9733;</span>Custom</span>');
        }

        return `
            <div class="result-item-body">
                <div class="result-title-row">
                    <div class="result-title-block">
                        <div class="result-title">${escapeHtml(student.name)} (${escapeHtml(student.roll_no)})</div>
                    </div>
                    <div class="result-badges">${badges.join('')}</div>
                </div>
                <div class="result-subtitle">
                    ${escapeHtml(student.department)} • Books Issued:
                    <span class="book-count ${canIssueMore === 0 ? 'full' : ''}">${escapeHtml(student.issued)}/${escapeHtml(student.maxBooks)}</span>
                    • Can issue:
                    <span class="book-count ${canIssueMore === 0 ? 'full' : ''}">${escapeHtml(canIssueMore)} left</span>
                </div>
            </div>
        `;
    }

    function animatePrivilegeCard() {
        privilegeInfoCard.classList.remove('is-visible');
        void privilegeInfoCard.offsetWidth;
        privilegeInfoCard.classList.add('is-visible');
    }

    window.showCustomAlert = function(title, message, type = 'info') {
        window.showTransactionToast(buildLegacyAlertPayload(title, message, type), type);
    };

    window.showPrivilegeToast = function(message, type = 'info') {
        window.showTransactionToast({
            title: type === 'warning' ? 'Selection Warning' : 'Selection Updated',
            message: String(message || ''),
            icon: type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-book',
        }, type);
    };

    window.togglePrivilegeDetails = function() {
        const isOpen = privilegeDetailsToggle.getAttribute('aria-expanded') === 'true';
        privilegeDetailsToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        privilegeDetailsLabel.textContent = isOpen ? 'Show Details' : 'Hide Details';
        privilegeDetailsPanel.hidden = isOpen;
        privilegeDetailsPanel.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
        privilegeDetailsPanel.classList.toggle('open', !isOpen);
    };

    window.updateBookCounter = function() {
        const slotsContainer = document.getElementById('bookCounterSlots');
        const counterText = document.getElementById('bookCounterText');
        const counterPill = document.getElementById('bookCounterPill');
        const rules = getIssueRules();

        slotsContainer.innerHTML = '';

        if (!selectedStudent || !rules) {
            counterText.textContent = '0 selected out of 0 available slots';
            counterPill.textContent = '0 / 0';
            const empty = document.createElement('div');
            empty.className = 'counter-slot-empty';
            empty.textContent = 'Choose a student to unlock the current issue slots.';
            slotsContainer.appendChild(empty);
            return;
        }

        const totalSlots = getIssueCapacity(rules, selectedStudent);
        const selectedCount = Math.min(selectedBooks.length, totalSlots);
        counterPill.textContent = `${selectedCount} / ${totalSlots}`;

        if (!rules.borrowing_allowed) {
            counterText.textContent = 'Borrowing is restricted, so no issue slots are available.';
            const empty = document.createElement('div');
            empty.className = 'counter-slot-empty';
            empty.textContent = 'This student is restricted from borrowing until the privilege setting changes.';
            slotsContainer.appendChild(empty);
            return;
        }

        if (totalSlots === 0) {
            counterText.textContent = 'No issue slots remain under the current borrowing limit.';
            const empty = document.createElement('div');
            empty.className = 'counter-slot-empty';
            empty.textContent = 'The student is already at the effective borrowing limit.';
            slotsContainer.appendChild(empty);
            return;
        }

        counterText.textContent = `${selectedCount} selected out of ${totalSlots} available slot${totalSlots === 1 ? '' : 's'}.`;

        for (let index = 0; index < totalSlots; index++) {
            const slot = document.createElement('div');
            slot.className = `counter-slot ${index < selectedCount ? 'filled' : 'available'}`;
            slot.textContent = String(index + 1);
            slot.setAttribute(
                'aria-label',
                index < selectedCount ? `Selected book slot ${index + 1}` : `Available book slot ${index + 1}`
            );
            slotsContainer.appendChild(slot);
        }
    };

    window.renderRestrictedState = function(student) {
        privilegeInfoCard.classList.add('is-restricted');
        privilegeStatusBadge.classList.add('restricted');
        privilegeWarningBanner.style.display = 'flex';
        privilegeWarningText.textContent = `${student.name} is currently blocked from borrowing. Book search and issuing stay disabled until borrowing access is restored.`;
        issueSelectionHint.textContent = 'Borrowing is restricted for this student, so no new books can be selected right now.';
        selectedBooksHeader.textContent = 'Selected Books';
        setPrivilegeStat('privilegeCanIssue', '0', 'Borrowing is currently restricted');
    };

    window.renderPrivilegeInfoCard = function(student, privileges, rawPrivileges = {}, defaults = {}) {
        const availableNow = getIssueCapacity(privileges, student);
        const remainingAfterSelection = getRemainingIssueCapacity(privileges, student);
        const overrideCount = privilegeFields.filter(field =>
            rawPrivileges[field] !== null && rawPrivileges[field] !== undefined
        ).length;
        const dueDate = formatDate(addDaysFromToday(privileges.issue_duration_days));
        const sourceLabel = overrideCount > 0 ? 'Student Override' : 'Global Default';
        const sourceMeta = overrideCount > 0 ?
            `${overrideCount} custom privilege rule${overrideCount === 1 ? '' : 's'} active on this account.` :
            'Every issue rule is currently coming from the active global library defaults.';

        privilegeInfoCard.style.display = 'block';
        privilegeInfoCard.classList.remove('is-restricted');
        privilegeStatusBadge.classList.remove('restricted');
        privilegeWarningBanner.style.display = 'none';

        document.getElementById('privilegeCardHeading').textContent = `${student.name}'s Issue Privileges`;
        document.getElementById('privilegeStatusText').textContent = privileges.borrowing_allowed ? 'Allowed' : 'Restricted';
        document.getElementById('privilegeSummaryText').textContent = privileges.borrowing_allowed ?
            (availableNow > 0 ?
                `${student.name} can still borrow ${remainingAfterSelection} more book${remainingAfterSelection === 1 ? '' : 's'} right now under the effective issue policy.` :
                `${student.name} is allowed to borrow, but the current book limit has already been reached.`) :
            `${student.name}'s current privilege settings block new borrowing until access is restored.`;

        setPrivilegeStat(
            'privilegeMaxBooks',
            String(privileges.max_books),
            rawPrivileges.max_books !== null && rawPrivileges.max_books !== undefined ?
            'Custom book limit is active' :
            `Default limit from library policy (${defaults.max_books ?? privileges.max_books})`
        );
        setPrivilegeStat('privilegeDuration', `${privileges.issue_duration_days}d`, `Due ${dueDate}`);
        setPrivilegeStat(
            'privilegeFineRate',
            formatCurrency(privileges.per_day_fine, 0),
            `${rawPrivileges.per_day_fine !== null && rawPrivileges.per_day_fine !== undefined ? 'Custom' : 'Default'} late fine rate`
        );
        setPrivilegeStat(
            'privilegeCanIssue',
            String(remainingAfterSelection),
            privileges.borrowing_allowed ?
            `${selectedBooks.length} selected, ${student.issued} already issued` :
            'Borrowing is currently restricted'
        );

        setPrivilegeDetail('privilegeSource', sourceLabel, sourceMeta);
        setPrivilegeDetail(
            'privilegeIssued',
            `${student.issued} / ${privileges.max_books}`,
            `${remainingAfterSelection} additional book${remainingAfterSelection === 1 ? '' : 's'} can still be selected.`
        );
        setPrivilegeDetail(
            'privilegeDurationDetail',
            `${privileges.issue_duration_days} days`,
            `Items issued today would be due on ${dueDate}.`
        );
        setPrivilegeDetail(
            'privilegeDueDate',
            dueDate,
            `Calculated from the effective issue duration of ${privileges.issue_duration_days} days.`
        );

        if (!privileges.borrowing_allowed) {
            renderRestrictedState(student);
        }

        updateBookCounter();
        animatePrivilegeCard();
    };

    window.renderIssuePrivilegeSummary = function() {
        const rules = getIssueRules();
        if (!selectedStudent || !rules) {
            resetIssuePrivilegeSummary();
            return;
        }

        renderPrivilegeInfoCard(selectedStudent, rules, studentPrivileges?.raw ?? {}, studentPrivileges?.defaults ?? {});
        const remainingCount = getRemainingIssueCapacity(rules, selectedStudent);

        selectedBooksHeader.textContent = remainingCount === 0 ? 'Selected Books • Limit reached' : 'Selected Books';
        issueSelectionHint.textContent = rules.borrowing_allowed ?
            (remainingCount > 0 ?
                `You can still choose ${remainingCount} more book${remainingCount === 1 ? '' : 's'} for this student.` :
                'The current borrowing limit has been reached for this student.') :
            'Borrowing is restricted for this student, so no new books can be selected right now.';
    };

    window.resetIssuePrivilegeSummary = function() {
        privilegeInfoCard.style.display = 'none';
        privilegeInfoCard.classList.remove('is-visible', 'is-restricted');
        privilegeStatusBadge.classList.remove('restricted');
        privilegeWarningBanner.style.display = 'none';
        privilegeDetailsToggle.setAttribute('aria-expanded', 'false');
        privilegeDetailsLabel.textContent = 'Show Details';
        privilegeDetailsPanel.hidden = true;
        privilegeDetailsPanel.setAttribute('aria-hidden', 'true');
        privilegeDetailsPanel.classList.remove('open');
        selectedBooksHeader.textContent = 'Selected Books';
        issueSelectionHint.textContent = 'Select books for this student. The selection limit follows the effective library privilege rules.';
        updateBookCounter();
    };

    function shouldResetIssueFormOnLoad() {
        const navigationEntry = performance.getEntriesByType('navigation')[0];
        return navigationEntry?.type === 'reload' || navigationEntry?.type === 'back_forward';
    }

    function resetIssueFormState() {
        if (!issueForm) {
            return;
        }

        window.clearStudentSelection();
    }

    document.addEventListener('DOMContentLoaded', function() {
        searchStudentInput.addEventListener('input', function() {
            const query = this.value.trim();
            const requestId = ++issueStudentSearchRequest;

            if (query.length < 2) {
                studentResults.style.display = 'none';
                return;
            }

            studentResults.innerHTML = '<div class="result-item"><div class="result-title">Searching...</div></div>';
            studentResults.style.display = 'block';

            fetch(`{{ route('staff.transactions.students') }}?query=${encodeURIComponent(query)}`, { credentials: 'include' })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(students => {
                    if (requestId !== issueStudentSearchRequest) {
                        return;
                    }

                    const uniqueStudents = [...new Map((students || []).map(student => [student.id, student])).values()];

                    if (uniqueStudents.length === 0) {
                        studentResults.innerHTML = '<div class="result-item"><div class="result-title">No students found</div></div>';
                    } else {
                        studentResults.innerHTML = '';

                        uniqueStudents.forEach(student => {
                            const item = document.createElement('div');
                            item.className = 'result-item';
                            item.dataset.id = student.id;
                            item.innerHTML = buildStudentResultMarkup(student);
                            item.addEventListener('click', function() {
                                selectStudentForIssue(student);
                            });

                            studentResults.appendChild(item);
                        });
                    }

                    studentResults.style.display = 'block';
                })
                .catch(error => {
                    if (requestId !== issueStudentSearchRequest) {
                        return;
                    }

                    console.error('Error fetching students:', error);
                    studentResults.innerHTML = `<div class="result-item"><div class="result-title">Error: ${escapeHtml(error.message)}</div></div>`;
                    studentResults.style.display = 'block';
                });
        });

        searchBookInput.addEventListener('input', function() {
            const query = this.value.trim();
            bookResults.innerHTML = '';

            if (query.length < 2) {
                bookResults.style.display = 'none';
                return;
            }

            const rules = getIssueRules();
            if (!selectedStudent || !rules) {
                bookResults.innerHTML = '<div class="result-item"><div class="result-title">Please select a student first</div></div>';
                bookResults.style.display = 'block';
                return;
            }

            if (!rules.borrowing_allowed) {
                bookResults.innerHTML = '<div class="result-item"><div class="result-title">Borrowing is restricted for this student</div></div>';
                bookResults.style.display = 'block';
                return;
            }

            fetch(`{{ route('staff.transactions.books') }}?query=${encodeURIComponent(query)}&studentId=${selectedStudent.id}`, { credentials: 'include' })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    return response.json();
                })
                .then(books => {
                    const availableBooks = (books || []).filter(book => !selectedBooks.some(selectedBook => selectedBook.id === book.id));

                    if (availableBooks.length === 0) {
                        bookResults.innerHTML = '<div class="result-item"><div class="result-title">No available books found</div></div>';
                    } else {
                        availableBooks.forEach(book => {
                            const item = document.createElement('div');
                            item.className = 'result-item';
                            item.dataset.id = book.id;
                            item.innerHTML = `
                                <div class="result-title">${escapeHtml(book.title)}</div>
                                <div class="result-subtitle">${escapeHtml(book.author)} • ISBN: ${escapeHtml(book.isbn)}</div>
                            `;

                            item.addEventListener('click', function() {
                                addBookToSelection(book);
                                searchBookInput.value = '';
                                bookResults.style.display = 'none';
                            });

                            bookResults.appendChild(item);
                        });
                    }

                    bookResults.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error fetching books:', error);
                    bookResults.innerHTML = `<div class="result-item"><div class="result-title">Error: ${escapeHtml(error.message)}</div></div>`;
                    bookResults.style.display = 'block';
                });
        });

        issueForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const rules = getIssueRules();

            if (!selectedStudent || selectedBooks.length === 0) {
                showCustomAlert('Missing Information', 'Please select a student and at least one book.', 'warning');
                return;
            }

            if (!rules) {
                showCustomAlert('Student Not Ready', 'Please wait for privilege information to finish loading.', 'error');
                return;
            }

            if (!rules.borrowing_allowed) {
                showCustomAlert('Borrowing Permission Denied', `${selectedStudent.name} does not have borrowing permission right now.`, 'error');
                return;
            }

            const canIssueMore = getIssueCapacity(rules, selectedStudent);
            if (selectedBooks.length > canIssueMore) {
                showCustomAlert('Exceeds Borrowing Limit', `Student can only issue ${canIssueMore} more book(s).`, 'error');
                return;
            }

            const selectedCount = selectedBooks.length;
            const bookIds = selectedBooks.map(book => book.id);
            const dueDatePreview = getIssueDueDatePreview(rules);
            const remainingAfterIssue = Math.max(0, getIssueCapacity(rules, selectedStudent) - selectedCount);

            openTransactionConfirmModal({
                title: `Issue ${selectedCount} book${selectedCount === 1 ? '' : 's'} to ${selectedStudent.name}?`,
                message: 'This will issue the selected books using the student\'s effective borrowing rules.',
                detail: `${selectedStudent.roll_no || 'Student'} • Due ${dueDatePreview}`,
                icon: 'fas fa-book-open',
                iconVariant: 'primary',
                confirmLabel: 'Issue Books',
                confirmIcon: 'fas fa-book-open',
                confirmVariant: 'primary',
                onConfirm: () => executeIssueTransaction({
                    studentId: selectedStudent.id,
                    studentName: selectedStudent.name,
                    studentRollNo: selectedStudent.roll_no,
                    bookIds,
                    selectedCount,
                    dueDatePreview,
                    remainingAfterIssue,
                }),
            });
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.search-container')) {
                studentResults.style.display = 'none';
                bookResults.style.display = 'none';
            }
        });

        resetIssuePrivilegeSummary();
        setBookSearchState();
        updateIssueButton();

        if (shouldResetIssueFormOnLoad()) {
            window.requestAnimationFrame(resetIssueFormState);
        }

        window.addEventListener('pageshow', function(event) {
            if (!event.persisted) {
                return;
            }

            window.requestAnimationFrame(resetIssueFormState);
        });
    });

    window.selectStudentForIssue = function(student) {
        selectedStudent = student;
        studentPrivileges = null;
        selectedBooks = [];
        selectedStudentId.value = student.id;
        searchStudentInput.value = `${student.name} (${student.roll_no})`;
        studentResults.style.display = 'none';
        clearStudentBtn.style.display = 'block';
        selectedStudentCard.style.display = 'none';
        availableBooksCard.style.display = 'none';
        resetIssuePrivilegeSummary();
        searchBookInput.value = '';
        bookResults.style.display = 'none';
        setBookSearchState();
        updateSelectedBooksList();
        updateIssueButton();
        updateAvailableBooksInfo();

        fetch(`/admin/students/${student.id}/privileges`, { credentials: 'include' })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error('Failed to load student privileges');
                }

                studentPrivileges = createPrivilegeState({
                    ...data,
                    hasPrivilegeOverride: student.hasPrivilegeOverride
                });

                const rules = getIssueRules();

                document.getElementById('studentName').textContent = student.name;
                document.getElementById('studentID').textContent = student.roll_no;
                document.getElementById('studentDepartment').textContent = student.department;
                document.getElementById('studentEmail').textContent = student.email;
                document.getElementById('studentIssued').textContent = `${student.issued} / ${rules.max_books}`;

                const progressPercent = Math.min(100, (Number(student.issued) / Math.max(1, Number(rules.max_books))) * 100);
                document.getElementById('issuedProgress').style.width = `${progressPercent}%`;

                selectedStudentCard.style.display = 'block';
                availableBooksCard.style.display = 'block';
                document.getElementById('selectedCount').textContent = '0 books';
                document.getElementById('totalBooks').textContent = '0';
                document.getElementById('canIssueMore').textContent = getRemainingIssueCapacity(rules, student);

                renderIssuePrivilegeSummary();
                setBookSearchState(student, rules);
                updateIssueButton();
            })
            .catch(error => {
                console.error('Error fetching privileges:', error);
                showCustomAlert('Error', `Failed to load student privileges: ${error.message}`, 'error');
                clearStudentSelection();
            });
    };

    window.addBookToSelection = function(book) {
        const rules = getIssueRules();
        if (!selectedStudent || !rules) return;

        const canIssueMore = getIssueCapacity(rules, selectedStudent);
        if (!rules.borrowing_allowed) {
            showCustomAlert('Borrowing Restricted', `${selectedStudent.name} cannot receive new issues right now.`, 'error');
            return;
        }

        if (selectedBooks.length >= canIssueMore) {
            showCustomAlert(
                'Book Limit Reached',
                `Student can only issue ${canIssueMore} more book(s). Privilege limit: ${rules.max_books} books maximum.`,
                'warning'
            );
            return;
        }

        if (selectedBooks.some(currentBook => currentBook.id === book.id)) {
            showCustomAlert('Book Already Selected', 'This book is already in the selection list.', 'warning');
            return;
        }

        if (Number(book.available ?? 1) <= 0) {
            showCustomAlert('Book Unavailable', 'This book is currently unavailable. No copies in stock.', 'error');
            return;
        }

        selectedBooks.push(book);
        updateSelectedBooksList();
        updateIssueButton();
        updateAvailableBooksInfo();
        const remainingCount = getRemainingIssueCapacity(rules, selectedStudent);
        showTransactionToast({
            title: 'Book Added',
            message: `Added ${book.title} to the issue selection.`,
            detail: `${remainingCount} issue slot${remainingCount === 1 ? '' : 's'} remaining`,
            icon: 'fas fa-book',
        }, 'info');
    };

    window.removeBookFromSelection = function(bookId) {
        selectedBooks = selectedBooks.filter(book => book.id !== bookId);
        updateSelectedBooksList();
        updateIssueButton();
        updateAvailableBooksInfo();
    };

    window.updateSelectedBooksList = function() {
        selectedBooksContainer.innerHTML = '';

        if (selectedBooks.length === 0) {
            selectedBooksList.style.display = 'none';
            updateBookCounter();
            return;
        }

        selectedBooksList.style.display = 'block';

        selectedBooks.forEach(book => {
            const bookItem = document.createElement('div');
            bookItem.className = 'selected-book-item';
            bookItem.innerHTML = `
                <div class="book-info">
                    <div class="book-title">${escapeHtml(book.title)}</div>
                    <div class="book-details">${escapeHtml(book.author)} • ${escapeHtml(book.category || 'N/A')}</div>
                </div>
                <button type="button" class="remove-book" data-book-id="${book.id}">
                    Remove
                </button>
            `;
            selectedBooksContainer.appendChild(bookItem);
        });

        document.querySelectorAll('.remove-book').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                removeBookFromSelection(Number(this.getAttribute('data-book-id')));
            });
        });

        updateBookCounter();
    };

    window.updateIssueButton = function() {
        const rules = getIssueRules();
        const canIssueMore = selectedStudent && rules ? getIssueCapacity(rules, selectedStudent) : 0;
        const selectedCount = selectedBooks.length;

        if (selectedStudent && rules && rules.borrowing_allowed && selectedCount > 0) {
            issueButton.disabled = false;
            issueButton.textContent = `Issue Books (${selectedCount}/${canIssueMore})`;
            return;
        }

        issueButton.disabled = true;
        issueButton.textContent = rules && !rules.borrowing_allowed ?
            'Issuing Restricted' :
            `Issue Books (${selectedCount}/${canIssueMore})`;
    };

    window.updateAvailableBooksInfo = function() {
        const rules = getIssueRules();
        if (!selectedStudent || !rules) return;

        const canIssueMore = getIssueCapacity(rules, selectedStudent);
        const selectedCount = selectedBooks.length;

        document.getElementById('selectedCount').textContent = `${selectedCount} book${selectedCount !== 1 ? 's' : ''}`;
        document.getElementById('totalBooks').textContent = selectedCount;
        document.getElementById('canIssueMore').textContent = Math.max(0, canIssueMore - selectedCount);
        renderIssuePrivilegeSummary();
    };

    window.clearStudentSelection = function() {
        selectedStudent = null;
        studentPrivileges = null;
        selectedBooks = [];
        issueForm?.reset();
        selectedStudentId.value = '';
        searchStudentInput.value = '';
        clearStudentBtn.style.display = 'none';
        selectedStudentCard.style.display = 'none';
        availableBooksCard.style.display = 'none';
        updateSelectedBooksList();
        updateIssueButton();
        resetIssuePrivilegeSummary();
        setBookSearchState();
        searchBookInput.value = '';
        bookResults.style.display = 'none';
    };
</script>
@endpush
