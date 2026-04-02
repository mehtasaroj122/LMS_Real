@extends('Admin.layouts.app')

@section('title', 'Reports')

@push('styles')
@include('shared.report-export.styles')
<style>
    .reports-container {
        padding: 0 clamp(4px, 0.9vw, 10px) clamp(12px, 1.5vw, 18px);
        max-width: none;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    .reports-header {
        margin-bottom: clamp(10px, 1.6vw, 14px);
    }

    .reports-header h1 {
        font-size: clamp(20px, 4vw, 28px);
        font-weight: 700;
        margin: 0 0 4px 0;
        line-height: 1.2;
    }

    .reports-header p {
        font-size: clamp(12px, 1.5vw, 13px);
        margin: 0;
    }

    body.light-theme .reports-header h1,
    body.light-theme .transaction-header h2,
    body.light-theme .chart-title,
    body.light-theme .table-title,
    body.light-theme .filter-group label {
        color: #0f172a;
    }

    body.dark-theme .reports-header h1,
    body.dark-theme .transaction-header h2,
    body.dark-theme .chart-title,
    body.dark-theme .table-title,
    body.dark-theme .filter-group label {
        color: #f1f5f9;
    }

    body.light-theme .reports-header p,
    body.light-theme .transaction-header p,
    body.light-theme .chart-description,
    body.light-theme .table-subtitle {
        color: #64748b;
    }

    body.dark-theme .reports-header p,
    body.dark-theme .transaction-header p,
    body.dark-theme .chart-description,
    body.dark-theme .table-subtitle {
        color: #94a3b8;
    }

    .filters-card,
    .stat-card,
    .chart-card,
    .table-card {
        border-radius: clamp(10px, 1vw, 14px);
        transition: all 0.3s ease;
    }

    .filters-card {
        padding: clamp(12px, 1.5vw, 16px);
        margin-bottom: clamp(10px, 1.6vw, 14px);
        max-width: none;
        width: 100%;
    }

    .stat-card,
    .chart-card,
    .table-card {
        padding: clamp(12px, 1.4vw, 18px);
    }

    body.light-theme .filters-card,
    body.light-theme .stat-card,
    body.light-theme .chart-card,
    body.light-theme .table-card {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    body.dark-theme .filters-card,
    body.dark-theme .stat-card,
    body.dark-theme .chart-card,
    body.dark-theme .table-card {
        background-color: #1e293b;
        border: 1px solid #334155;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.3);
    }

    .table-card {
        display: flex;
        flex-direction: column;
        height: clamp(360px, 40vw, 430px);
        min-height: 0;
        overflow: hidden;
    }

    .reports-content-root {
        min-height: 320px;
    }

    .reports-loading-state,
    .reports-error-state {
        border-radius: clamp(10px, 1vw, 14px);
        border: 1px solid #dbe3ef;
        background: #ffffff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    }

    body.dark-theme .reports-loading-state,
    body.dark-theme .reports-error-state {
        border-color: #334155;
        background: #1e293b;
        box-shadow: 0 10px 28px rgba(2, 6, 23, 0.3);
    }

    .reports-loading-state {
        display: grid;
        gap: 18px;
        padding: 18px;
    }

    .reports-loading-header {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .reports-loading-spinner {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 3px solid rgba(59, 130, 246, 0.16);
        border-top-color: #2563eb;
        animation: reportsSpin 0.9s linear infinite;
        flex-shrink: 0;
    }

    .reports-loading-title,
    .reports-error-copy h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }

    body.dark-theme .reports-loading-title,
    body.dark-theme .reports-error-copy h3 {
        color: #f8fafc;
    }

    .reports-loading-subtitle,
    .reports-error-copy p {
        margin: 4px 0 0;
        font-size: 13px;
        line-height: 1.5;
        color: #64748b;
    }

    body.dark-theme .reports-loading-subtitle,
    body.dark-theme .reports-error-copy p {
        color: #94a3b8;
    }

    .reports-skeleton-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .reports-skeleton-panels {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 12px;
    }

    .reports-skeleton-card,
    .reports-skeleton-panel {
        min-height: 110px;
        border-radius: 14px;
        background: linear-gradient(90deg, rgba(226, 232, 240, 0.95) 0%, rgba(241, 245, 249, 1) 50%, rgba(226, 232, 240, 0.95) 100%);
        background-size: 220% 100%;
        animation: reportsSkeleton 1.3s ease-in-out infinite;
    }

    .reports-skeleton-panel {
        min-height: 240px;
    }

    .reports-skeleton-panel-lg {
        min-height: 320px;
    }

    body.dark-theme .reports-skeleton-card,
    body.dark-theme .reports-skeleton-panel {
        background: linear-gradient(90deg, rgba(51, 65, 85, 0.95) 0%, rgba(71, 85, 105, 0.92) 50%, rgba(51, 65, 85, 0.95) 100%);
        background-size: 220% 100%;
    }

    .reports-error-state {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px;
        margin-top: 4px;
    }

    .reports-error-icon {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 20px;
        font-weight: 700;
        color: #ffffff;
        background: linear-gradient(135deg, #ef4444, #f97316);
    }

    .reports-error-copy {
        min-width: 0;
        flex: 1 1 auto;
    }

    .reports-error-retry {
        border: 0;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #ffffff;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        cursor: pointer;
    }

    .reports-toast-container {
        position: fixed;
        top: 88px;
        right: 18px;
        z-index: 1250;
        display: grid;
        gap: 10px;
        width: min(340px, calc(100vw - 24px));
    }

    .reports-toast {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 14px 12px;
        border-radius: 14px;
        color: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.22);
        animation: reportsToastIn 0.18s ease;
    }

    .reports-toast.is-leaving {
        opacity: 0;
        transform: translateY(-6px);
        transition: opacity 0.18s ease, transform 0.18s ease;
    }

    .reports-toast-info {
        background: linear-gradient(135deg, #2563eb, #0ea5e9);
    }

    .reports-toast-success {
        background: linear-gradient(135deg, #059669, #10b981);
    }

    .reports-toast-error {
        background: linear-gradient(135deg, #dc2626, #f97316);
    }

    .reports-toast-copy {
        display: grid;
        gap: 4px;
    }

    .reports-toast-copy strong,
    .reports-toast-copy span {
        color: inherit;
    }

    .reports-toast-copy strong {
        font-size: 13px;
        font-weight: 700;
    }

    .reports-toast-copy span {
        font-size: 12px;
        line-height: 1.45;
        opacity: 0.96;
    }

    .reports-toast-close {
        border: 0;
        background: transparent;
        color: inherit;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
    }

    @keyframes reportsSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes reportsSkeleton {
        0% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0 50%;
        }
    }

    @keyframes reportsToastIn {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .filters-grid {
        display: grid;
        grid-template-columns: minmax(280px, 420px) minmax(280px, 420px) minmax(320px, 460px) minmax(0, 1fr) auto;
        gap: 10px 12px;
        align-items: end;
        justify-content: start;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filters-grid > .filter-group:not(.export-buttons) {
        width: 100%;
        min-width: 280px;
        justify-self: start;
        max-width: 420px;
    }

    #customRangeGroup {
        grid-column: 3;
        min-width: 320px;
        max-width: 460px;
    }

    .filters-grid > .filter-group:nth-child(1) {
        grid-column: 1;
    }

    .filters-grid > .filter-group:nth-child(2) {
        grid-column: 2;
    }

    .filter-group label {
        font-size: clamp(11px, 1.4vw, 12px);
        font-weight: 600;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: clamp(8px, 1vw, 10px) clamp(10px, 1.4vw, 12px);
        border-radius: 10px;
        font-size: clamp(11px, 1.4vw, 12px);
        font-family: inherit;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    body.light-theme .filter-group select,
    body.light-theme .filter-group input {
        background-color: #ffffff;
        border-color: #dbe3ef;
        color: #0f172a;
    }

    body.dark-theme .filter-group select,
    body.dark-theme .filter-group input {
        background-color: #334155;
        border-color: #475569;
        color: #f1f5f9;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
    }

    .custom-range-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(110px, 1fr));
        gap: 8px;
    }

    .is-hidden {
        display: none !important;
    }

    .export-buttons {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
        grid-column: 5;
        justify-content: flex-end;
        justify-self: end;
        align-self: end;
    }

    .export-btn {
        border-radius: 10px;
        border: 1px solid #3b82f6;
        background: transparent;
        color: #3b82f6;
        font-size: clamp(11px, 1.4vw, 12px);
        font-weight: 600;
        padding: 10px 14px;
        min-width: 156px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .export-btn:hover {
        transform: translateY(-1px);
    }

    body.light-theme .export-btn:hover {
        background-color: #3b82f6;
        color: #ffffff;
        box-shadow: 0 8px 18px rgba(59, 130, 246, 0.2);
    }

    body.dark-theme .export-btn:hover {
        background-color: #60a5fa;
        border-color: #60a5fa;
        color: #0f172a;
        box-shadow: 0 8px 18px rgba(96, 165, 250, 0.25);
    }

    .stats-grid,
    .transaction-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-bottom: clamp(10px, 1.6vw, 16px);
    }

    .stat-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-height: 94px;
        padding: 12px;
        overflow: hidden;
    }

    .stat-card-header {
        order: 2;
        display: block;
        margin: 0;
        padding-right: 54px;
    }

    .stat-icon {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.35);
    }

    .stat-icon.books {
        background: rgba(59, 130, 246, 0.14);
        color: #2563eb;
    }

    .stat-icon.available {
        background: rgba(34, 197, 94, 0.16);
        color: #16a34a;
    }

    .stat-icon.issued {
        background: rgba(16, 185, 129, 0.16);
        color: #059669;
    }

    .stat-icon.additions {
        background: rgba(245, 158, 11, 0.16);
        color: #d97706;
    }

    .stat-icon.overdue {
        background: rgba(248, 113, 113, 0.18);
        color: #ef4444;
    }

    .stat-value {
        display: block;
        font-size: clamp(18px, 2.3vw, 24px);
        font-weight: 800;
        line-height: 1.08;
        letter-spacing: -0.03em;
    }

    .stat-label {
        order: 1;
        margin: 0;
        padding-right: 54px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.05em;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .stat-subtitle {
        order: 3;
        margin: 1px 0 0 0;
        max-width: 100%;
        font-size: 11px;
        line-height: 1.3;
    }

    body.light-theme .stat-card:hover {
        border-color: #d9e4f4;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        transform: translateY(-1px);
    }

    body.dark-theme .stat-card:hover {
        border-color: #475569;
        box-shadow: 0 12px 24px rgba(2, 6, 23, 0.32);
        transform: translateY(-1px);
    }

    body.light-theme .stat-value {
        color: #0f172a;
    }

    body.light-theme .stat-label {
        color: #64748b;
    }

    body.dark-theme .stat-value {
        color: #f8fafc;
    }

    body.dark-theme .stat-label {
        color: #94a3b8;
    }

    body.light-theme .stat-subtitle {
        color: #64748b;
    }

    body.dark-theme .stat-subtitle {
        color: #94a3b8;
    }

    .transaction-header {
        margin: clamp(12px, 1.6vw, 16px) 0 clamp(10px, 1.4vw, 14px);
    }

    .transaction-header h2 {
        font-size: clamp(16px, 2.2vw, 22px);
        margin: 0 0 4px 0;
    }

    .transaction-header p {
        margin: 0;
        font-size: 12px;
    }

    .trends-section {
        display: grid;
        gap: clamp(10px, 1.4vw, 14px);
    }

    .charts-grid,
    .tables-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: clamp(10px, 1.4vw, 14px);
    }

    .section-spacing-top {
        margin-top: clamp(12px, 1.8vw, 20px);
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .chart-title,
    .table-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 4px 0;
        font-size: 15px;
        font-weight: 700;
    }

    .chart-description,
    .table-subtitle {
        margin: 0 0 12px 0;
        font-size: 11px;
    }

    .chart-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .chart-card-header .chart-description {
        margin-bottom: 0;
    }

    .chart-heading {
        flex: 1 1 auto;
        min-width: 0;
    }

    .chart-note {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        font-size: 11px;
        font-weight: 600;
    }

    .chart-note-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .chart-actions {
        display: flex;
        align-items: flex-start;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        flex-shrink: 0;
    }

    .chart-action-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .chart-action-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .chart-action-select,
    .chart-action-btn {
        min-height: 38px;
        border-radius: 10px;
        border: 1px solid transparent;
        font-size: 12px;
        font-weight: 600;
        font-family: inherit;
        transition: all 0.2s ease;
    }

    .chart-action-select {
        min-width: 112px;
        padding: 8px 34px 8px 12px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image:
            linear-gradient(45deg, transparent 50%, currentColor 50%),
            linear-gradient(135deg, currentColor 50%, transparent 50%);
        background-position:
            calc(100% - 16px) calc(50% - 2px),
            calc(100% - 11px) calc(50% - 2px);
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
        cursor: pointer;
    }

    .chart-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 12px;
        background: transparent;
        cursor: pointer;
        white-space: nowrap;
    }

    .chart-action-btn:hover {
        transform: translateY(-1px);
    }

    .chart-action-select:focus,
    .chart-action-btn:focus-visible,
    .chart-view-btn:focus-visible {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.14);
    }

    .chart-view-switch {
        display: inline-flex;
        align-items: center;
        padding: 4px;
        border-radius: 999px;
        border: 1px solid transparent;
    }

    .chart-view-btn {
        border: none;
        background: transparent;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 11px;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .chart-view-btn.is-active {
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.24);
    }

    .condition-legend {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .legend-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: 11px;
        font-weight: 600;
    }

    .legend-swatch {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        flex-shrink: 0;
    }

    .chart-container.category-chart-container {
        height: clamp(300px, 36vw, 420px);
    }

    .chart-container {
        position: relative;
        width: 100%;
        height: clamp(240px, 30vw, 320px);
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .table-card .table-title,
    .table-card .table-subtitle,
    .table-card .table-pagination {
        flex-shrink: 0;
    }

    .table-scroll-area {
        flex: 1 1 auto;
        min-height: 0;
        overflow-x: auto;
        overflow-y: auto;
        scrollbar-gutter: stable;
        overscroll-behavior: contain;
        padding-right: 2px;
    }

    .table-scroll-area::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-scroll-area::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.5);
    }

    .table-scroll-area::-webkit-scrollbar-track {
        background: transparent;
    }

    .report-table thead th {
        text-align: left;
        padding: 12px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .report-table tbody td {
        padding: 12px;
        vertical-align: middle;
    }

    .report-table .table-count {
        text-align: right;
    }

    .table-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 0 0 12px 0;
    }

    .table-pagination-info,
    .table-pagination-select {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
    }

    .table-pagination-select select {
        min-width: 68px;
        padding: 6px 32px 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-family: inherit;
        border: 1px solid transparent;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image:
            linear-gradient(45deg, transparent 50%, currentColor 50%),
            linear-gradient(135deg, currentColor 50%, transparent 50%);
        background-position:
            calc(100% - 16px) calc(50% - 2px),
            calc(100% - 11px) calc(50% - 2px);
        background-size: 5px 5px, 5px 5px;
        background-repeat: no-repeat;
    }

    body.light-theme .table-pagination-info,
    body.light-theme .table-pagination-select {
        color: #475569;
    }

    body.dark-theme .table-pagination-info,
    body.dark-theme .table-pagination-select {
        color: #cbd5e1;
    }

    body.light-theme .table-pagination-select select {
        background-color: #ffffff;
        border-color: #dbe3ef;
        color: #0f172a;
    }

    body.dark-theme .table-pagination-select select {
        background-color: #334155;
        border-color: #475569;
        color: #f8fafc;
    }

    .table-pagination-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-pagination-page {
        min-width: 72px;
        text-align: center;
        font-size: 12px;
        font-weight: 600;
    }

    .table-pagination-btn {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        background: transparent;
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .table-pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    body.light-theme .table-pagination-btn {
        border-color: #cbd5e1;
        color: #0f172a;
        background: #ffffff;
    }

    body.dark-theme .table-pagination-btn {
        border-color: #475569;
        color: #f8fafc;
        background: #1e293b;
    }

    body.light-theme .report-table thead th {
        color: #475569;
        background: #f8fafc;
    }

    body.dark-theme .report-table thead th {
        color: #cbd5e1;
        background: #0f172a;
    }

    body.light-theme .report-table tbody td {
        color: #0f172a;
        border-top: 1px solid #edf2f7;
    }

    body.dark-theme .report-table tbody td {
        color: #f8fafc;
        border-top: 1px solid #334155;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }

    .badge-info {
        background: rgba(59, 130, 246, 0.14);
        color: #3b82f6;
    }

    .badge-success {
        background: rgba(16, 185, 129, 0.14);
        color: #10b981;
    }

    .badge-warning {
        background: rgba(245, 158, 11, 0.14);
        color: #f59e0b;
    }

    .badge-danger {
        background: rgba(249, 115, 22, 0.14);
        color: #f97316;
    }

    body.light-theme .chart-note,
    body.light-theme .chart-action-label {
        color: #475569;
    }

    body.dark-theme .chart-note,
    body.dark-theme .chart-action-label {
        color: #cbd5e1;
    }

    body.light-theme .chart-note-pill {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
    }

    body.dark-theme .chart-note-pill {
        background: rgba(96, 165, 250, 0.16);
        color: #93c5fd;
    }

    body.light-theme .chart-action-select,
    body.light-theme .chart-action-btn,
    body.light-theme .chart-view-switch,
    body.light-theme .legend-chip {
        background-color: #ffffff;
        border-color: #dbe3ef;
        color: #0f172a;
    }

    body.dark-theme .chart-action-select,
    body.dark-theme .chart-action-btn,
    body.dark-theme .chart-view-switch,
    body.dark-theme .legend-chip {
        background-color: #0f172a;
        border-color: #334155;
        color: #f8fafc;
    }

    body.light-theme .chart-action-btn:hover {
        background: rgba(37, 99, 235, 0.06);
        border-color: #93c5fd;
        color: #1d4ed8;
    }

    body.dark-theme .chart-action-btn:hover {
        background: rgba(37, 99, 235, 0.22);
        border-color: rgba(96, 165, 250, 0.4);
        color: #dbeafe;
    }

    body.light-theme .chart-view-btn {
        color: #475569;
    }

    body.dark-theme .chart-view-btn {
        color: #cbd5e1;
    }

    body.report-pdf-preview-open {
        overflow: hidden;
    }

    .report-pdf-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, 0.58);
        z-index: 1400;
    }

    .report-pdf-modal.is-open {
        display: flex;
    }

    .report-pdf-panel {
        width: min(1120px, 100%);
        max-height: min(90vh, 860px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-radius: 20px;
        border: 1px solid rgba(148, 163, 184, 0.24);
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.2);
    }

    body.light-theme .report-pdf-panel {
        background: #ffffff;
        border-color: #dbe3ef;
    }

    body.dark-theme .report-pdf-panel {
        background: #0f172a;
        border-color: #334155;
        box-shadow: 0 30px 80px rgba(2, 6, 23, 0.46);
    }

    .report-pdf-header,
    .report-pdf-footer {
        padding: 18px 22px;
    }

    .report-pdf-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid transparent;
    }

    body.light-theme .report-pdf-header,
    body.light-theme .report-pdf-footer {
        border-color: #e2e8f0;
    }

    body.dark-theme .report-pdf-header,
    body.dark-theme .report-pdf-footer {
        border-color: #1e293b;
    }

    .report-pdf-header-copy h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.15;
    }

    .report-pdf-header-copy p {
        margin: 6px 0 0;
        font-size: 13px;
        line-height: 1.45;
    }

    body.light-theme .report-pdf-header-copy h3,
    body.light-theme .report-pdf-section-title,
    body.light-theme .report-pdf-table-title,
    body.light-theme .report-pdf-chart-title,
    body.light-theme .report-pdf-preview-count {
        color: #0f172a;
    }

    body.dark-theme .report-pdf-header-copy h3,
    body.dark-theme .report-pdf-section-title,
    body.dark-theme .report-pdf-table-title,
    body.dark-theme .report-pdf-chart-title,
    body.dark-theme .report-pdf-preview-count {
        color: #f8fafc;
    }

    body.light-theme .report-pdf-header-copy p,
    body.light-theme .report-pdf-meta-pill,
    body.light-theme .report-pdf-preview-note,
    body.light-theme .report-pdf-chart-description,
    body.light-theme .report-pdf-table-description,
    body.light-theme .report-pdf-footer-note,
    body.light-theme .report-pdf-empty {
        color: #64748b;
    }

    body.dark-theme .report-pdf-header-copy p,
    body.dark-theme .report-pdf-meta-pill,
    body.dark-theme .report-pdf-preview-note,
    body.dark-theme .report-pdf-chart-description,
    body.dark-theme .report-pdf-table-description,
    body.dark-theme .report-pdf-footer-note,
    body.dark-theme .report-pdf-empty {
        color: #94a3b8;
    }

    .report-pdf-close {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        border: 1px solid transparent;
        background: transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    body.light-theme .report-pdf-close {
        color: #64748b;
    }

    body.dark-theme .report-pdf-close {
        color: #cbd5e1;
    }

    .report-pdf-close:hover {
        transform: translateY(-1px);
    }

    body.light-theme .report-pdf-close:hover {
        background: rgba(37, 99, 235, 0.06);
        border-color: #dbe3ef;
        color: #0f172a;
    }

    body.dark-theme .report-pdf-close:hover {
        background: rgba(37, 99, 235, 0.18);
        border-color: #334155;
        color: #f8fafc;
    }

    .report-pdf-close:focus-visible,
    .report-pdf-btn:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18);
    }

    .report-pdf-body {
        flex: 1 1 auto;
        overflow: auto;
        padding: 22px;
        background:
            radial-gradient(circle at top left, rgba(96, 165, 250, 0.08), transparent 26%),
            linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(241, 245, 249, 0.82));
    }

    body.dark-theme .report-pdf-body {
        background:
            radial-gradient(circle at top left, rgba(96, 165, 250, 0.12), transparent 28%),
            linear-gradient(180deg, rgba(15, 23, 42, 0.96), rgba(15, 23, 42, 0.9));
    }

    .report-pdf-preview {
        width: min(980px, 100%);
        margin: 0 auto;
        padding: 28px;
        border-radius: 18px;
        border: 1px solid transparent;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    }

    body.light-theme .report-pdf-preview {
        background: #ffffff;
        border-color: #dbe3ef;
    }

    body.dark-theme .report-pdf-preview {
        background: #0f172a;
        border-color: #334155;
        box-shadow: 0 16px 44px rgba(2, 6, 23, 0.34);
    }

    .report-pdf-document-header {
        display: grid;
        gap: 14px;
        padding-bottom: 18px;
        border-bottom: 1px solid transparent;
    }

    body.light-theme .report-pdf-document-header {
        border-color: #e2e8f0;
    }

    body.dark-theme .report-pdf-document-header {
        border-color: #1e293b;
    }

    .report-pdf-document-kicker {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #2563eb;
    }

    .report-pdf-document-title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .report-pdf-document-title {
        margin: 0;
        font-size: 28px;
        font-weight: 800;
        line-height: 1.1;
        color: inherit;
    }

    .report-pdf-preview-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        white-space: nowrap;
    }

    body.light-theme .report-pdf-preview-count {
        background: rgba(37, 99, 235, 0.08);
    }

    body.dark-theme .report-pdf-preview-count {
        background: rgba(37, 99, 235, 0.16);
    }

    .report-pdf-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .report-pdf-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
        line-height: 1.2;
        border: 1px solid transparent;
    }

    body.light-theme .report-pdf-meta-pill {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    body.dark-theme .report-pdf-meta-pill {
        background: #111c2e;
        border-color: #334155;
    }

    .report-pdf-preview-note {
        margin: 0;
        font-size: 13px;
        line-height: 1.5;
    }

    .report-pdf-preview-section {
        margin-top: 22px;
    }

    .report-pdf-section-title {
        margin: 0 0 12px;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.2;
    }

    .report-pdf-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .report-pdf-stat-card {
        padding: 14px;
        border-radius: 14px;
        border: 1px solid transparent;
    }

    body.light-theme .report-pdf-stat-card {
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    body.dark-theme .report-pdf-stat-card {
        background: #111c2e;
        border-color: #334155;
    }

    .report-pdf-stat-label {
        margin: 0 0 6px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }

    body.dark-theme .report-pdf-stat-label {
        color: #94a3b8;
    }

    .report-pdf-stat-value {
        margin: 0;
        font-size: 21px;
        font-weight: 800;
        line-height: 1.1;
        color: inherit;
    }

    .report-pdf-stat-subtitle {
        margin: 6px 0 0;
        font-size: 11px;
        line-height: 1.4;
        color: inherit;
        opacity: 0.75;
    }

    .report-pdf-chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 14px;
    }

    .report-pdf-chart-card,
    .report-pdf-table-card {
        border-radius: 16px;
        border: 1px solid transparent;
        overflow: hidden;
    }

    body.light-theme .report-pdf-chart-card,
    body.light-theme .report-pdf-table-card {
        background: #ffffff;
        border-color: #e2e8f0;
    }

    body.dark-theme .report-pdf-chart-card,
    body.dark-theme .report-pdf-table-card {
        background: #111c2e;
        border-color: #334155;
    }

    .report-pdf-chart-copy,
    .report-pdf-table-copy {
        padding: 14px 16px 0;
    }

    .report-pdf-chart-title,
    .report-pdf-table-title {
        margin: 0;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.2;
    }

    .report-pdf-chart-description,
    .report-pdf-table-description {
        margin: 6px 0 0;
        font-size: 12px;
        line-height: 1.45;
    }

    .report-pdf-chart-figure {
        padding: 14px 16px 16px;
    }

    .report-pdf-chart-image {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    .report-pdf-table-grid {
        display: grid;
        gap: 14px;
    }

    .report-pdf-table-wrap {
        padding: 14px 16px 16px;
        overflow: auto;
    }

    .report-pdf-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .report-pdf-table th,
    .report-pdf-table td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
        border-bottom: 1px solid transparent;
    }

    .report-pdf-table .table-count {
        text-align: right;
    }

    body.light-theme .report-pdf-table th,
    body.light-theme .report-pdf-table td {
        border-color: #e2e8f0;
    }

    body.dark-theme .report-pdf-table th,
    body.dark-theme .report-pdf-table td {
        border-color: #334155;
    }

    .report-pdf-table th {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }

    body.dark-theme .report-pdf-table th {
        color: #94a3b8;
    }

    .report-pdf-table tbody tr:last-child td {
        border-bottom: none;
    }

    .report-pdf-table-caption {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 16px 12px;
    }

    .report-pdf-table-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
    }

    body.light-theme .report-pdf-table-pill {
        background: rgba(37, 99, 235, 0.08);
        color: #2563eb;
    }

    body.dark-theme .report-pdf-table-pill {
        background: rgba(37, 99, 235, 0.16);
        color: #93c5fd;
    }

    .report-pdf-empty {
        padding: 18px 16px 16px;
        font-size: 13px;
        line-height: 1.5;
    }

    .report-pdf-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-top: 1px solid transparent;
    }

    .report-pdf-footer-note {
        margin: 0;
        font-size: 12px;
        line-height: 1.45;
    }

    .report-pdf-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .report-pdf-btn {
        min-height: 40px;
        border-radius: 12px;
        border: 1px solid transparent;
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 700;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .report-pdf-btn:hover {
        transform: translateY(-1px);
    }

    .report-pdf-btn-secondary {
        background: transparent;
    }

    body.light-theme .report-pdf-btn-secondary {
        background: #ffffff;
        border-color: #dbe3ef;
        color: #0f172a;
    }

    body.dark-theme .report-pdf-btn-secondary {
        background: #0f172a;
        border-color: #334155;
        color: #f8fafc;
    }

    .report-pdf-btn-print {
        background: transparent;
        border-color: #cbd5e1;
        color: #2563eb;
    }

    body.dark-theme .report-pdf-btn-print {
        border-color: #475569;
        color: #93c5fd;
    }

    .report-pdf-btn-primary {
        border-color: #2563eb;
        background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
        color: #ffffff;
        box-shadow: 0 14px 24px rgba(37, 99, 235, 0.22);
    }

    .report-pdf-btn-primary:hover {
        box-shadow: 0 16px 26px rgba(37, 99, 235, 0.28);
    }

    .section-transaction,
    .section-fines,
    .section-users,
    .section-overdue {
        display: none;
    }

    .section-transaction.active,
    .section-fines.active,
    .section-users.active,
    .section-overdue.active {
        display: block;
    }

    .section-library.hidden {
        display: none;
    }

    @media (max-width: 1024px) {
        .filters-grid {
            grid-template-columns: 1fr 1fr;
        }

        .filters-grid > .filter-group:not(.export-buttons),
        #customRangeGroup {
            min-width: 0;
            max-width: none;
        }

        .filters-grid > .filter-group:nth-child(1),
        .filters-grid > .filter-group:nth-child(2),
        #customRangeGroup,
        .export-buttons {
            grid-column: auto;
        }

        .export-buttons {
            justify-content: flex-start;
            justify-self: start;
        }

        .report-pdf-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .report-pdf-actions {
            width: 100%;
            justify-content: stretch;
        }

        .report-pdf-actions .report-pdf-btn {
            flex: 1 1 0;
        }

        .chart-card-header {
            flex-direction: column;
        }

        .chart-actions,
        .condition-legend {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .reports-container {
            padding: 0 2px 12px;
        }

        .report-pdf-modal {
            padding: 10px;
        }

        .report-pdf-header,
        .report-pdf-body,
        .report-pdf-footer {
            padding: 14px;
        }

        .report-pdf-preview {
            padding: 18px;
        }

        .report-pdf-document-title-row {
            flex-direction: column;
        }

        .stat-card {
            min-height: 88px;
            padding: 12px;
        }

        .stat-card-header,
        .stat-label {
            padding-right: 54px;
        }

        .stat-icon {
            top: 12px;
            right: 12px;
            width: 34px;
            height: 34px;
            border-radius: 12px;
        }

        .filters-grid,
        .charts-grid,
        .tables-grid {
            grid-template-columns: 1fr;
        }

        .custom-range-fields {
            grid-template-columns: 1fr;
        }

        .export-buttons {
            flex-direction: row;
            align-items: center;
        }

        .export-btn {
            justify-content: center;
            min-width: 148px;
        }

        .chart-container {
            height: 240px;
        }

        .table-card {
            height: 360px;
        }

        .chart-container.category-chart-container {
            height: 340px;
        }

        .chart-actions {
            width: 100%;
            flex-direction: column;
            align-items: stretch;
        }

        .chart-action-group,
        .chart-action-select,
        .chart-action-btn,
        .chart-view-switch {
            width: 100%;
        }

        .chart-view-switch {
            justify-content: space-between;
        }

        .chart-view-btn {
            flex: 1 1 0;
        }

        .report-table thead th,
        .report-table tbody td {
            padding: 9px 8px;
        }

        .reports-loading-header,
        .reports-error-state {
            flex-direction: column;
            align-items: flex-start;
        }

        .reports-skeleton-panels {
            grid-template-columns: 1fr;
        }

        .reports-toast-container {
            top: 74px;
            right: 12px;
            left: 12px;
            width: auto;
        }
    }
</style>
@endpush

@section('content')
    @php
        $formatCurrency = fn ($value) => '₹' . number_format((float) $value, 2);
        $reportExportConfig = [
            'modalId' => 'reportExportModal',
            'idPrefix' => 'reportExport',
            'scopeName' => 'reportExportScope',
            'labels' => [
                'title' => 'Export Report',
                'description' => 'Print or download the selected report.',
                'scopeTitle' => 'Scope',
                'scopeHint' => 'Use current table pages or the full filtered report.',
                'pageOptionTitle' => 'Current page',
                'pageOptionDescription' => 'Only rows visible now.',
                'allOptionTitle' => 'Filtered report',
                'allOptionDescription' => 'All rows matching the selected filters.',
                'badge' => 'Current page',
                'headline' => '0 report rows ready',
                'subtext' => 'Selected report content will be used for export.',
                'previewTitle' => 'Preview',
                'previewDescription' => 'Rows included in export.',
                'previewCount' => '0 rows',
                'emptyPreview' => 'No report rows selected for preview.',
                'footerNote' => 'Using current page for export.',
                'cancelButton' => 'Cancel',
                'downloadButton' => 'Export PDF',
                'printButton' => 'Print',
            ],
            'document' => [
                'systemTitle' => $libraryBranding['name'] ?? 'Library Management System',
                'reportTitle' => 'Library Report',
            ],
            'columns' => [
                ['key' => 'section', 'label' => 'Section', 'width' => '22%', 'emphasis' => true],
                ['key' => 'source', 'label' => 'Item', 'width' => '24%'],
                ['key' => 'details', 'label' => 'Details', 'width' => '54%'],
            ],
        ];
    @endphp

    <div class="reports-container">
        <div class="reports-header">
            <h1>Reports</h1>
            <p>Library analytics driven by live inventory, circulation, fine, user, and overdue data.</p>
        </div>

        <div class="filters-card">
            <form id="reportFiltersForm" method="GET" action="{{ route('admin.reports.index') }}">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="reportType">Report Type</label>
                        <select id="reportType" name="report_type">
                            <option value="inventory" {{ $filters['reportType'] === 'inventory' ? 'selected' : '' }}>Books Inventory</option>
                            <option value="transactions" {{ $filters['reportType'] === 'transactions' ? 'selected' : '' }}>Transactions</option>
                            <option value="fines" {{ $filters['reportType'] === 'fines' ? 'selected' : '' }}>Fine & Revenue</option>
                            <option value="users" {{ $filters['reportType'] === 'users' ? 'selected' : '' }}>User & Activity</option>
                            <option value="overdue" {{ $filters['reportType'] === 'overdue' ? 'selected' : '' }}>Overdue Books</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="timePeriod">Time Period</label>
                        <select id="timePeriod" name="time_period">
                            <option value="7days" {{ $filters['timePeriod'] === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30days" {{ $filters['timePeriod'] === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="90days" {{ $filters['timePeriod'] === '90days' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="year" {{ $filters['timePeriod'] === 'year' ? 'selected' : '' }}>Last Year</option>
                            <option value="custom" {{ $filters['timePeriod'] === 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>

                    <div id="customRangeGroup" class="filter-group {{ $filters['showCustomRange'] ? '' : 'is-hidden' }}">
                        <label for="startDate">Date Range</label>
                        <div class="custom-range-fields">
                            <input id="startDate" name="start_date" type="date" value="{{ $filters['startDateInput'] }}">
                            <input id="endDate" name="end_date" type="date" value="{{ $filters['endDateInput'] }}">
                        </div>
                    </div>

                    <div class="filter-group export-buttons">
                        <button id="applyFiltersBtn" type="submit" class="export-btn {{ $filters['showCustomRange'] ? '' : 'is-hidden' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                            Apply
                        </button>
                        <button type="button" class="export-btn" onclick="exportPDF()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            Export PDF
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="reportsContent" class="reports-content-root" aria-live="polite" aria-busy="true">
            <div class="reports-loading-state" data-loading-state>
                <div class="reports-loading-header">
                    <div class="reports-loading-spinner" aria-hidden="true"></div>
                    <div>
                        <p class="reports-loading-title">Loading report data</p>
                        <p class="reports-loading-subtitle">Fetching the latest analytics for the selected filters.</p>
                    </div>
                </div>
                <div class="reports-skeleton-grid">
                    <div class="reports-skeleton-card"></div>
                    <div class="reports-skeleton-card"></div>
                    <div class="reports-skeleton-card"></div>
                    <div class="reports-skeleton-card"></div>
                </div>
                <div class="reports-skeleton-panels">
                    <div class="reports-skeleton-panel reports-skeleton-panel-lg"></div>
                    <div class="reports-skeleton-panel"></div>
                </div>
            </div>
        </div>
    </div>
    @include('shared.report-export.modal', ['reportExportConfig' => $reportExportConfig])

    <div id="reportPdfPreviewModal" class="report-pdf-modal" aria-hidden="true">
        <div class="report-pdf-panel" role="dialog" aria-modal="true" aria-labelledby="reportPdfPreviewTitle">
            <div class="report-pdf-header">
                <div class="report-pdf-header-copy">
                    <h3 id="reportPdfPreviewTitle">Report Export Preview</h3>
                    <p>Review the current report layout before printing or exporting it as a PDF.</p>
                </div>
                <button type="button" id="reportPdfPreviewClose" class="report-pdf-close" aria-label="Close PDF preview">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m18 6-12 12"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="report-pdf-body">
                <div id="reportPdfPreviewContent" class="report-pdf-preview" aria-live="polite"></div>
            </div>

            <div class="report-pdf-footer">
                <p id="reportPdfPreviewFooterNote" class="report-pdf-footer-note">Export PDF downloads a branded PDF copy of this report.</p>
                <div class="report-pdf-actions">
                    <button type="button" id="reportPdfPreviewCancel" class="report-pdf-btn report-pdf-btn-secondary">Cancel</button>
                    <button type="button" id="reportPdfPreviewPrint" class="report-pdf-btn report-pdf-btn-print">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9V2h12v7"></path>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <path d="M6 14h12v8H6z"></path>
                        </svg>
                        Print
                    </button>
                    <button type="button" id="reportPdfPreviewExport" class="report-pdf-btn report-pdf-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <path d="m7 10 5 5 5-5"></path>
                            <path d="M12 15V3"></path>
                        </svg>
                        Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('shared.report-export.scripts')
    <script>
        window.adminReportsPageConfig = {
            endpoint: @json(route('admin.reports.data')),
            initialFilters: @json($filters),
        };
    </script>
    <script src="{{ asset('admin/JS/services/report-api.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const adminReportsPageConfig = window.adminReportsPageConfig || {};
        let reportCharts = getEmptyReportCharts();
        const reportDocumentConfig = @json($reportExportConfig['document']);
        const reportBranding = window.LibraryBranding?.normalize
            ? window.LibraryBranding.normalize(window.__LIBRARY_BRANDING__ ?? {})
            : (window.__LIBRARY_BRANDING__ ?? {});
        const chartInstances = {};
        const tablePaginators = new Map();
        const reportsContent = document.getElementById('reportsContent');
        const reportTypeSelect = document.getElementById('reportType');
        const timePeriodSelect = document.getElementById('timePeriod');
        const filterForm = document.getElementById('reportFiltersForm');
        const customRangeGroup = document.getElementById('customRangeGroup');
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');
        let categoryChartLimitSelect = document.getElementById('categoryChartLimit');
        let categoryChartInsight = document.getElementById('categoryChartInsight');
        let categoryViewButtons = Array.from(document.querySelectorAll('[data-category-view]'));
        let exportChartButtons = Array.from(document.querySelectorAll('[data-export-chart]'));
        const reportExportModal = document.getElementById('reportExportModal');
        const reportPdfPreviewModal = document.getElementById('reportPdfPreviewModal');
        const reportPdfPreviewClose = document.getElementById('reportPdfPreviewClose');
        const reportPdfPreviewCancel = document.getElementById('reportPdfPreviewCancel');
        const reportPdfPreviewPrint = document.getElementById('reportPdfPreviewPrint');
        const reportPdfPreviewExport = document.getElementById('reportPdfPreviewExport');
        const reportPdfPreviewContent = document.getElementById('reportPdfPreviewContent');
        const reportPdfPreviewFooterNote = document.getElementById('reportPdfPreviewFooterNote');
        const CATEGORY_BAR_COLORS = ['#1d4ed8', '#2563eb', '#3b82f6', '#60a5fa', '#0ea5e9', '#06b6d4', '#14b8a6', '#10b981', '#34d399', '#6ee7b7'];
        const CATEGORY_DONUT_COLORS = ['#2563eb', '#3b82f6', '#0ea5e9', '#06b6d4', '#14b8a6', '#10b981'];
        const CONDITION_COLOR_MAP = {
            New: '#16a34a',
            Good: '#2563eb',
            Damaged: '#f97316',
        };
        const reportPdfPreviewState = {
            snapshot: null,
            lastFocusedElement: null,
        };
        const reportExportModalState = {
            lastFocusedElement: null,
        };
        const reportState = {
            filters: { ...(adminReportsPageConfig.initialFilters || {}) },
            cache: new Map(),
            requestController: null,
            activeRequestKey: '',
            contentLoaded: false,
        };
        let reportExportWorkflow = null;

        function getEmptyReportCharts() {
            return {
                inventory: {
                    category: { labels: [], data: [] },
                    condition: { labels: [], data: [] },
                },
                transactions: {
                    monthly_circulation: { labels: [], issues: [], returns: [] },
                    borrowed_books: { labels: [], data: [] },
                    activity: { labels: [], issues: [], returns: [] },
                },
                fines: {
                    collection_overview: { labels: [], generated: [], collected: [], pending: [] },
                    efficiency: { labels: [], generated: [], collected: [] },
                },
                users: {
                    users_by_role: { labels: [], data: [] },
                    activity_by_role: { labels: [], admin: [], staff: [], student: [] },
                },
                overdue: {
                    distribution: { labels: [], data: [] },
                    trend: { labels: [], total: [], critical: [] },
                },
            };
        }

        function syncDynamicContentElements() {
            categoryChartLimitSelect = document.getElementById('categoryChartLimit');
            categoryChartInsight = document.getElementById('categoryChartInsight');
            categoryViewButtons = Array.from(document.querySelectorAll('[data-category-view]'));
            exportChartButtons = Array.from(document.querySelectorAll('[data-export-chart]'));
        }

        function getCurrentFilters() {
            return {
                reportType: reportTypeSelect?.value || reportState.filters.reportType || 'inventory',
                timePeriod: timePeriodSelect?.value || reportState.filters.timePeriod || '30days',
                startDateInput: document.getElementById('startDate')?.value || reportState.filters.startDateInput || '',
                endDateInput: document.getElementById('endDate')?.value || reportState.filters.endDateInput || '',
            };
        }

        function buildReportRequestKey(filters = {}) {
            return JSON.stringify({
                reportType: filters.reportType || 'inventory',
                timePeriod: filters.timePeriod || '30days',
                startDateInput: filters.startDateInput || '',
                endDateInput: filters.endDateInput || '',
            });
        }

        function destroyCharts() {
            Object.keys(chartInstances).forEach((key) => {
                chartInstances[key]?.destroy?.();
                delete chartInstances[key];
            });
        }

        function setFiltersBusy(isBusy) {
            filterForm?.querySelectorAll('select, input, button').forEach((element) => {
                element.disabled = isBusy;
            });
        }

        function buildLoadingStateMarkup() {
            return `
                <div class="reports-loading-state" data-loading-state>
                    <div class="reports-loading-header">
                        <div class="reports-loading-spinner" aria-hidden="true"></div>
                        <div>
                            <p class="reports-loading-title">Loading report data</p>
                            <p class="reports-loading-subtitle">Fetching the latest analytics for the selected filters.</p>
                        </div>
                    </div>
                    <div class="reports-skeleton-grid">
                        <div class="reports-skeleton-card"></div>
                        <div class="reports-skeleton-card"></div>
                        <div class="reports-skeleton-card"></div>
                        <div class="reports-skeleton-card"></div>
                    </div>
                    <div class="reports-skeleton-panels">
                        <div class="reports-skeleton-panel reports-skeleton-panel-lg"></div>
                        <div class="reports-skeleton-panel"></div>
                    </div>
                </div>
            `;
        }

        function buildErrorStateMarkup(message) {
            return `
                <div class="reports-error-state" role="alert">
                    <div class="reports-error-icon" aria-hidden="true">!</div>
                    <div class="reports-error-copy">
                        <h3>Unable to load report</h3>
                        <p>${escapeHtml(message || 'Please try again in a moment.')}</p>
                    </div>
                    <button type="button" class="reports-error-retry" data-report-retry>Retry</button>
                </div>
            `;
        }

        function renderLoadingState() {
            if (!reportsContent) {
                return;
            }

            destroyCharts();
            reportsContent.innerHTML = buildLoadingStateMarkup();
            reportsContent.setAttribute('aria-busy', 'true');
            syncDynamicContentElements();
        }

        function renderErrorState(message) {
            if (!reportsContent) {
                return;
            }

            destroyCharts();
            reportsContent.innerHTML = buildErrorStateMarkup(message);
            reportsContent.setAttribute('aria-busy', 'false');
            syncDynamicContentElements();
        }

        function ensureToastContainer() {
            let container = document.querySelector('.reports-toast-container');

            if (!container) {
                container = document.createElement('div');
                container.className = 'reports-toast-container';
                document.body.appendChild(container);
            }

            return container;
        }

        function showReportToast(type, title, message) {
            const container = ensureToastContainer();
            const toast = document.createElement('div');

            toast.className = `reports-toast reports-toast-${type || 'info'}`;
            toast.innerHTML = `
                <div class="reports-toast-copy">
                    <strong>${escapeHtml(title || 'Notice')}</strong>
                    <span>${escapeHtml(message || '')}</span>
                </div>
                <button type="button" class="reports-toast-close" aria-label="Dismiss notification">×</button>
            `;

            const dismiss = () => {
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 180);
            };

            toast.querySelector('.reports-toast-close')?.addEventListener('click', dismiss);
            container.appendChild(toast);
            window.setTimeout(dismiss, 3600);
        }

        function syncFilterControls(filters = {}) {
            reportState.filters = { ...reportState.filters, ...filters };

            if (reportTypeSelect && filters.reportType) {
                reportTypeSelect.value = filters.reportType;
            }

            if (timePeriodSelect && filters.timePeriod) {
                timePeriodSelect.value = filters.timePeriod;
            }

            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');

            if (startDateInput && Object.prototype.hasOwnProperty.call(filters, 'startDateInput')) {
                startDateInput.value = filters.startDateInput || '';
            }

            if (endDateInput && Object.prototype.hasOwnProperty.call(filters, 'endDateInput')) {
                endDateInput.value = filters.endDateInput || '';
            }

            updateCustomRangeVisibility();
        }

        function updateBrowserHistory(filters, options = {}) {
            if (!window.AdminReportApi?.buildReportQuery) {
                return;
            }

            const params = window.AdminReportApi.buildReportQuery(filters);
            const nextUrl = `${window.location.pathname}${params.toString() ? `?${params.toString()}` : ''}`;
            const historyMethod = options.replace ? 'replaceState' : 'pushState';

            window.history[historyMethod]({ filters }, '', nextUrl);
        }

        function getThemeColors() {
            const isDark = document.body.classList.contains('dark-theme');

            return {
                textColor: isDark ? '#e2e8f0' : '#1f2937',
                gridColor: isDark ? '#475569' : '#e5e7eb',
                surfaceBorder: isDark ? '#1e293b' : '#ffffff',
                axisBorder: isDark ? '#475569' : '#d1d5db',
                tooltipBg: isDark ? '#334155' : '#ffffff',
            };
        }

        function baseChartOptions(colors) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 900,
                    easing: 'easeOutCubic',
                },
                interaction: {
                    mode: 'nearest',
                    intersect: false,
                },
                layout: {
                    padding: {
                        top: 8,
                        right: 12,
                        bottom: 8,
                        left: 8,
                    },
                },
                plugins: {
                    legend: {
                        labels: {
                            color: colors.textColor,
                            font: { size: 12, weight: '600' },
                            usePointStyle: true,
                            boxWidth: 10,
                            boxHeight: 10,
                            padding: 14,
                        },
                    },
                    tooltip: {
                        backgroundColor: colors.tooltipBg,
                        titleColor: colors.textColor,
                        bodyColor: colors.textColor,
                        borderColor: colors.gridColor,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 10,
                        displayColors: true,
                    },
                },
            };
        }

        const valueLabelPlugin = {
            id: 'valueLabelPlugin',
            afterDatasetsDraw(chart, args, pluginOptions) {
                if (!pluginOptions?.enabled || chart.config.type !== 'bar') {
                    return;
                }

                const datasetIndex = pluginOptions.datasetIndex ?? 0;
                const dataset = chart.data.datasets?.[datasetIndex];
                const meta = chart.getDatasetMeta(datasetIndex);

                if (!dataset || !meta?.data?.length) {
                    return;
                }

                const { ctx, chartArea } = chart;
                const isHorizontal = (chart.options.indexAxis || 'x') === 'y';

                ctx.save();
                ctx.fillStyle = pluginOptions.color || '#0f172a';
                ctx.font = `600 ${pluginOptions.fontSize || 11}px sans-serif`;
                ctx.textBaseline = 'middle';

                dataset.data.forEach((rawValue, index) => {
                    const value = Number(rawValue ?? 0);
                    const element = meta.data[index];

                    if (!element || value <= 0) {
                        return;
                    }

                    if (isHorizontal) {
                        const desiredX = element.x + 10;
                        const maxX = chartArea.right - 6;

                        ctx.textAlign = desiredX >= maxX ? 'right' : 'left';
                        ctx.fillText(
                            formatNumber(value),
                            desiredX >= maxX ? maxX : desiredX,
                            element.y
                        );

                        return;
                    }

                    ctx.textAlign = 'center';
                    ctx.fillText(
                        formatNumber(value),
                        element.x,
                        Math.max(element.y - 10, chartArea.top + 10)
                    );
                });

                ctx.restore();
            },
        };

        function createOrReplaceChart(id, config) {
            const ctx = document.getElementById(id);
            if (!ctx || typeof Chart === 'undefined') {
                return;
            }

            if (chartInstances[id]) {
                chartInstances[id].destroy();
            }

            chartInstances[id] = new Chart(ctx, config);
        }

        function formatNumber(value) {
            return Number(value ?? 0).toLocaleString();
        }

        function truncateLabel(label, maxLength = 22) {
            const text = String(label ?? '');
            return text.length > maxLength ? `${text.slice(0, maxLength - 1)}...` : text;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatDateTime(dateValue = new Date()) {
            const date = dateValue instanceof Date ? dateValue : new Date(dateValue);

            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            });
        }

        function buildReportGeneratedLabel(dateValue = new Date()) {
            return `Generated on ${formatDateTime(dateValue)}`;
        }

        function getReportSystemTitle() {
            return String(reportBranding?.name || reportDocumentConfig?.systemTitle || 'Library Management System');
        }

        function getReportDocumentTitle() {
            return String(reportDocumentConfig?.reportTitle || 'Library Report');
        }

        function getReportBrandingFallbackText() {
            return String(reportBranding?.fallback_text || 'LMS').trim().slice(0, 4).toUpperCase() || 'LMS';
        }

        function buildPrintLogoMarkup(branding = reportBranding) {
            if (branding?.image_url) {
                return `<img src="${escapeHtml(branding.image_url)}" alt="${escapeHtml(branding.alt || 'Library Logo')}" loading="eager">`;
            }

            return `<span class="print-logo-fallback">${escapeHtml(getReportBrandingFallbackText())}</span>`;
        }

        function getActiveReportType() {
            return reportTypeSelect?.value || 'inventory';
        }

        function getActiveReportTypeLabel() {
            return reportTypeSelect?.selectedOptions?.[0]?.textContent?.trim() || 'Report';
        }

        function getActiveSectionBlocks() {
            const reportType = getActiveReportType();

            if (reportType === 'inventory') {
                return Array.from(document.querySelectorAll('.section-library')).filter((section) => !section.classList.contains('hidden'));
            }

            const activeSection = document.querySelector(`.section-${reportType}.active`);
            return activeSection ? [activeSection] : [];
        }

        function getActiveSectionHeading(blocks) {
            const header = blocks.map((block) => block.querySelector('.transaction-header')).find(Boolean);
            const fallbackMap = {
                inventory: {
                    title: 'Books Inventory Overview',
                    description: 'Inventory composition, category distribution, condition status, and table summaries.',
                },
                transactions: {
                    title: 'Transaction Analytics',
                    description: 'Circulation activity, borrowing trends, and transaction summaries for the selected period.',
                },
                fines: {
                    title: 'Fines & Revenue Overview',
                    description: 'Collection performance, pending balances, and fine analytics for the selected period.',
                },
                users: {
                    title: 'Users & Activity Overview',
                    description: 'User counts, role mix, and activity reporting within the selected period.',
                },
                overdue: {
                    title: 'Overdue Books Management',
                    description: 'Current overdue exposure, aging distribution, and overdue recovery monitoring.',
                },
            };

            if (header) {
                return {
                    title: header.querySelector('h2')?.textContent?.trim() || fallbackMap[getActiveReportType()]?.title || getActiveReportTypeLabel(),
                    description: header.querySelector('p')?.textContent?.trim() || fallbackMap[getActiveReportType()]?.description || '',
                };
            }

            return fallbackMap[getActiveReportType()] || {
                title: getActiveReportTypeLabel(),
                description: 'Report overview and export preview.',
            };
        }

        function getFilterMetaItems(scope = 'all', generatedAt = new Date()) {
            const items = [
                `Report Type: ${getActiveReportTypeLabel()}`,
                `Time Period: ${timePeriodSelect?.selectedOptions?.[0]?.textContent?.trim() || ''}`,
            ];

            if (timePeriodSelect?.value === 'custom') {
                const startDate = document.getElementById('startDate')?.value || '';
                const endDate = document.getElementById('endDate')?.value || '';

                if (startDate || endDate) {
                    items.push(`Range: ${startDate || '--'} to ${endDate || '--'}`);
                }
            } else {
                items.push(`Range: ${reportState.filters.periodLabel || 'Selected period'}`);
            }

            items.push(`Export Scope: ${scope === 'all' ? 'Filtered report' : 'Current page'}`);
            items.push(`Generated: ${formatDateTime(generatedAt)}`);

            return items;
        }

        function getExportRowsForTable(table) {
            const tableId = table.dataset.paginationId;
            const paginator = tableId ? tablePaginators.get(tableId) : null;

            if (paginator) {
                return paginator.rows.length ? paginator.rows : [];
            }

            const tbody = table.querySelector('tbody');
            return tbody ? getRealTableRows(tbody) : [];
        }

        function getCurrentPageRowsForTable(table) {
            const tableId = table.dataset.paginationId;
            const paginator = tableId ? tablePaginators.get(tableId) : null;

            if (!paginator) {
                const tbody = table.querySelector('tbody');
                return tbody ? getRealTableRows(tbody) : [];
            }

            const totalRows = paginator.rows.length;
            const totalPages = Math.max(1, Math.ceil(totalRows / paginator.pageSize));
            const currentPage = Math.min(Math.max(1, paginator.currentPage), totalPages);
            const startIndex = totalRows === 0 ? 0 : (currentPage - 1) * paginator.pageSize;
            const endIndex = Math.min(startIndex + paginator.pageSize, totalRows);

            return paginator.rows.slice(startIndex, endIndex);
        }

        function getTableRowsForScope(table, scope = 'all') {
            return scope === 'page'
                ? getCurrentPageRowsForTable(table)
                : getExportRowsForTable(table);
        }

        function collectReportStats(blocks) {
            return blocks.flatMap((block) => {
                return Array.from(block.querySelectorAll('.stats-grid .stat-card, .transaction-stats-grid .stat-card')).map((card) => ({
                    label: card.querySelector('.stat-label')?.textContent?.trim() || '',
                    value: card.querySelector('.stat-value')?.textContent?.trim() || '',
                    subtitle: card.querySelector('.stat-subtitle')?.textContent?.trim() || '',
                }));
            }).filter((item) => item.label || item.value);
        }

        function collectReportCharts(blocks) {
            return blocks.flatMap((block) => {
                return Array.from(block.querySelectorAll('.chart-card')).map((card) => {
                    const canvas = card.querySelector('canvas');
                    const chartId = canvas?.id || '';
                    const chart = chartId ? chartInstances[chartId] : null;
                    const image = chart?.toBase64Image
                        ? chart.toBase64Image('image/png', 1)
                        : (canvas?.toDataURL ? canvas.toDataURL('image/png', 1) : '');

                    return {
                        title: card.querySelector('.chart-title')?.textContent?.replace(/\s+/g, ' ').trim() || '',
                        description: card.querySelector('.chart-description')?.textContent?.trim() || '',
                        image,
                    };
                });
            }).filter((chart) => chart.title && chart.image);
        }

        function collectReportTables(blocks, scope = 'all') {
            return blocks.flatMap((block) => {
                return Array.from(block.querySelectorAll('.table-card')).map((card) => {
                    const table = card.querySelector('table');
                    const headers = table
                        ? Array.from(table.querySelectorAll('thead th')).map((cell) => cell.textContent.trim())
                        : [];
                    const rows = table
                        ? getTableRowsForScope(table, scope).map((row) => Array.from(row.querySelectorAll('th, td')).map((cell) => cell.innerText.trim()))
                        : [];

                    return {
                        title: card.querySelector('.table-title')?.textContent?.replace(/\s+/g, ' ').trim() || '',
                        description: card.querySelector('.table-subtitle')?.textContent?.trim() || '',
                        headers,
                        rows,
                    };
                });
            }).filter((table) => table.title && table.headers.length);
        }

        function buildReportSnapshot(scope = 'all', generatedAt = new Date()) {
            const blocks = getActiveSectionBlocks();
            const heading = getActiveSectionHeading(blocks);

            return {
                generatedAt,
                reportType: getActiveReportType(),
                reportTypeLabel: getActiveReportTypeLabel(),
                heading,
                metaItems: getFilterMetaItems(scope, generatedAt),
                stats: collectReportStats(blocks),
                charts: collectReportCharts(blocks),
                tables: collectReportTables(blocks, scope),
            };
        }

        function buildReportExportRows(scope = 'page') {
            const snapshot = buildReportSnapshot(scope);
            const tableRows = snapshot.tables.flatMap((table) => {
                return table.rows.map((row) => ({
                    section: table.title,
                    source: row[0] || table.title,
                    details: row
                        .map((cell, index) => {
                            const header = table.headers[index] || `Column ${index + 1}`;
                            return `${header}: ${cell}`;
                        })
                        .join(' | '),
                }));
            });

            if (tableRows.length > 0) {
                return tableRows;
            }

            const summaryRows = snapshot.stats.map((stat) => ({
                section: 'Summary',
                source: stat.label,
                details: [stat.value, stat.subtitle].filter(Boolean).join(' | '),
            }));
            const chartRows = snapshot.charts.map((chart) => ({
                section: 'Chart',
                source: chart.title,
                details: chart.description || 'Included in selected report export.',
            }));

            return [...summaryRows, ...chartRows];
        }

        function buildStatsPreviewMarkup(stats) {
            if (!stats.length) {
                return '<p class="report-pdf-empty">No summary metrics are available for this report.</p>';
            }

            return `
                <div class="report-pdf-stats-grid">
                    ${stats.map((stat) => `
                        <div class="report-pdf-stat-card">
                            <p class="report-pdf-stat-label">${escapeHtml(stat.label)}</p>
                            <p class="report-pdf-stat-value">${escapeHtml(stat.value)}</p>
                            <p class="report-pdf-stat-subtitle">${escapeHtml(stat.subtitle)}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        function buildChartsPreviewMarkup(charts) {
            if (!charts.length) {
                return '<p class="report-pdf-empty">No charts are available to include in this preview.</p>';
            }

            return `
                <div class="report-pdf-chart-grid">
                    ${charts.map((chart) => `
                        <article class="report-pdf-chart-card">
                            <div class="report-pdf-chart-copy">
                                <h4 class="report-pdf-chart-title">${escapeHtml(chart.title)}</h4>
                                <p class="report-pdf-chart-description">${escapeHtml(chart.description)}</p>
                            </div>
                            <figure class="report-pdf-chart-figure">
                                <img class="report-pdf-chart-image" src="${chart.image}" alt="${escapeHtml(chart.title)} preview">
                            </figure>
                        </article>
                    `).join('')}
                </div>
            `;
        }

        function buildTablesPreviewMarkup(tables, { limitRows = 6 } = {}) {
            if (!tables.length) {
                return '<p class="report-pdf-empty">No tables are available to include in this preview.</p>';
            }

            return `
                <div class="report-pdf-table-grid">
                    ${tables.map((table) => {
                        const previewRows = table.rows.slice(0, limitRows);
                        const countLabel = `${previewRows.length} of ${table.rows.length} rows`;

                        return `
                            <article class="report-pdf-table-card">
                                <div class="report-pdf-table-copy">
                                    <h4 class="report-pdf-table-title">${escapeHtml(table.title)}</h4>
                                    <p class="report-pdf-table-description">${escapeHtml(table.description)}</p>
                                </div>
                                <div class="report-pdf-table-caption">
                                    <span class="report-pdf-preview-note">${escapeHtml(table.rows.length ? 'Preview of included rows for export.' : 'No rows available for this table.')}</span>
                                    <span class="report-pdf-table-pill">${escapeHtml(countLabel)}</span>
                                </div>
                                <div class="report-pdf-table-wrap">
                                    <table class="report-pdf-table">
                                        <thead>
                                            <tr>
                                                ${table.headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${previewRows.length
                                                ? previewRows.map((row) => `
                                                    <tr>
                                                        ${row.map((cell, index) => `<td class="${table.headers[index]?.toLowerCase().includes('count') || table.headers[index]?.toLowerCase().includes('amount') || table.headers[index]?.toLowerCase().includes('days') ? 'table-count' : ''}">${escapeHtml(cell)}</td>`).join('')}
                                                    </tr>
                                                `).join('')
                                                : `<tr><td colspan="${Math.max(1, table.headers.length)}" class="report-pdf-empty">No rows available for preview.</td></tr>`}
                                        </tbody>
                                    </table>
                                </div>
                            </article>
                        `;
                    }).join('')}
                </div>
            `;
        }

        function buildReportPreviewMarkup(snapshot, options = {}) {
            const limitRows = Number(options.limitRows || 6);
            const totalRows = snapshot.tables.reduce((sum, table) => sum + table.rows.length, 0);

            return `
                <div class="report-pdf-document-header">
                    <span class="report-pdf-document-kicker">Library Report Preview</span>
                    <div class="report-pdf-document-title-row">
                        <div>
                            <h4 class="report-pdf-document-title">${escapeHtml(snapshot.heading.title)}</h4>
                            <p class="report-pdf-preview-note">${escapeHtml(snapshot.heading.description)}</p>
                        </div>
                        <span class="report-pdf-preview-count">${escapeHtml(formatNumber(totalRows))} table rows ready</span>
                    </div>
                    <div class="report-pdf-meta">
                        ${snapshot.metaItems.map((item) => `<span class="report-pdf-meta-pill">${escapeHtml(item)}</span>`).join('')}
                    </div>
                </div>

                <section class="report-pdf-preview-section">
                    <h4 class="report-pdf-section-title">Summary</h4>
                    ${buildStatsPreviewMarkup(snapshot.stats)}
                </section>

                <section class="report-pdf-preview-section">
                    <h4 class="report-pdf-section-title">Charts Included</h4>
                    ${buildChartsPreviewMarkup(snapshot.charts)}
                </section>

                <section class="report-pdf-preview-section">
                    <h4 class="report-pdf-section-title">Tables Included</h4>
                    ${buildTablesPreviewMarkup(snapshot.tables, { limitRows })}
                </section>
            `;
        }

        function sanitizeReportFilenamePart(value) {
            return String(value ?? '')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '')
                || 'report';
        }

        function buildReportPdfFilename(snapshot) {
            const generatedAt = snapshot?.generatedAt instanceof Date ? snapshot.generatedAt : new Date(snapshot?.generatedAt ?? Date.now());
            const datePart = Number.isNaN(generatedAt.getTime())
                ? new Date().toISOString().slice(0, 10)
                : generatedAt.toISOString().slice(0, 10);
            const reportSlug = sanitizeReportFilenamePart(snapshot?.reportTypeLabel || snapshot?.heading?.title || getReportDocumentTitle());

            return `${sanitizeReportFilenamePart(getReportDocumentTitle())}-${reportSlug}-${datePart}.pdf`;
        }

        function getReportPdfLib() {
            const { PDFDocument, StandardFonts, rgb } = window.PDFLib || {};

            if (!PDFDocument || !StandardFonts || !rgb) {
                throw new Error('PDF export support is not available right now.');
            }

            return { PDFDocument, StandardFonts, rgb };
        }

        function normalizePdfTextValue(value) {
            return String(value ?? '')
                .replace(/\u20B9/g, 'Rs.')
                .replace(/[\u2013\u2014]/g, '-')
                .replace(/[\u2018\u2019]/g, "'")
                .replace(/[\u201C\u201D]/g, '"')
                .replace(/\u2022/g, '-')
                .replace(/\u2026/g, '...')
                .replace(/\u00A0/g, ' ');
        }

        function splitPdfTextLines(text, maxWidth, font, fontSize) {
            const normalizedText = normalizePdfTextValue(text).replace(/\s+/g, ' ').trim();

            if (!normalizedText) {
                return [];
            }

            const words = normalizedText.split(' ');
            const lines = [];
            let currentLine = '';

            const commitWordSegments = (word) => {
                let segment = '';

                Array.from(word).forEach((character) => {
                    const candidate = `${segment}${character}`;

                    if (!segment || font.widthOfTextAtSize(candidate, fontSize) <= maxWidth) {
                        segment = candidate;
                        return;
                    }

                    lines.push(segment);
                    segment = character;
                });

                currentLine = segment;
            };

            words.forEach((word) => {
                const candidate = currentLine ? `${currentLine} ${word}` : word;

                if (font.widthOfTextAtSize(candidate, fontSize) <= maxWidth) {
                    currentLine = candidate;
                    return;
                }

                if (currentLine) {
                    lines.push(currentLine);
                    currentLine = '';
                }

                if (font.widthOfTextAtSize(word, fontSize) <= maxWidth) {
                    currentLine = word;
                    return;
                }

                commitWordSegments(word);
            });

            if (currentLine) {
                lines.push(currentLine);
            }

            return lines;
        }

        function drawPdfTextLines(page, lines, options) {
            const {
                x,
                y,
                font,
                fontSize,
                lineHeight,
                color,
            } = options;

            lines.forEach((line, index) => {
                page.drawText(line, {
                    x,
                    y: y - (index * lineHeight),
                    font,
                    size: fontSize,
                    color,
                });
            });

            return lines.length * lineHeight;
        }

        async function safeEmbedPdfImage(pdfDoc, source) {
            const normalizedSource = typeof source === 'string' ? source.trim() : source;
            const candidates = [];

            if (normalizedSource) {
                candidates.push(normalizedSource);
            }

            if (typeof normalizedSource === 'string' && normalizedSource.includes(',')) {
                candidates.push(normalizedSource.split(',').pop() || '');
            }

            for (const candidate of candidates) {
                if (!candidate) {
                    continue;
                }

                try {
                    return await pdfDoc.embedPng(candidate);
                } catch (pngError) {
                    try {
                        return await pdfDoc.embedJpg(candidate);
                    } catch (jpgError) {
                        continue;
                    }
                }
            }

            return null;
        }

        async function loadReportBrandingImage(pdfDoc) {
            const imageUrl = String(reportBranding?.image_url || '').trim();

            if (!imageUrl) {
                return null;
            }

            try {
                const response = await fetch(imageUrl, { credentials: 'same-origin' });

                if (!response.ok) {
                    throw new Error(`Failed to load branding image: ${response.status}`);
                }

                const bytes = new Uint8Array(await response.arrayBuffer());
                return await safeEmbedPdfImage(pdfDoc, bytes);
            } catch (error) {
                console.warn('[Reports] Failed to load branding image for PDF export:', error);
                return null;
            }
        }

        async function downloadReportPdf(scope = 'all', generatedAt = new Date()) {
            const { PDFDocument, StandardFonts, rgb } = getReportPdfLib();
            const timestamp = generatedAt instanceof Date ? generatedAt : new Date(generatedAt);
            const snapshot = buildReportSnapshot(scope, Number.isNaN(timestamp.getTime()) ? new Date() : timestamp);
            const totalRows = snapshot.tables.reduce((sum, table) => sum + table.rows.length, 0);
            const documentDetails = [
                ...buildReportExportDocumentDetails(scope),
                { label: 'Rows Included', value: `${formatNumber(totalRows)} table rows` },
                { label: 'Charts Included', value: `${formatNumber(snapshot.charts.length)} charts` },
                { label: 'Tables Included', value: `${formatNumber(snapshot.tables.length)} tables` },
            ];
            const pdfDoc = await PDFDocument.create();
            const fonts = {
                regular: await pdfDoc.embedFont(StandardFonts.Helvetica),
                bold: await pdfDoc.embedFont(StandardFonts.HelveticaBold),
            };
            const toColor = (hex, fallback = '#0f172a') => {
                const raw = String(hex || fallback).replace('#', '').trim();
                const normalized = raw.length === 3
                    ? raw.split('').map((char) => `${char}${char}`).join('')
                    : raw.padEnd(6, '0').slice(0, 6);
                const red = Number.parseInt(normalized.slice(0, 2), 16) / 255;
                const green = Number.parseInt(normalized.slice(2, 4), 16) / 255;
                const blue = Number.parseInt(normalized.slice(4, 6), 16) / 255;

                return rgb(
                    Number.isFinite(red) ? red : 15 / 255,
                    Number.isFinite(green) ? green : 23 / 255,
                    Number.isFinite(blue) ? blue : 42 / 255
                );
            };
            const colors = {
                ink: toColor('#0f172a'),
                body: toColor('#475569'),
                muted: toColor('#64748b'),
                accent: toColor('#2563eb'),
                accentSoft: toColor('#dbeafe'),
                border: toColor('#cbd5e1'),
                surface: toColor('#f8fafc'),
                headerFill: toColor('#e2e8f0'),
                white: rgb(1, 1, 1),
            };
            const logoImage = await loadReportBrandingImage(pdfDoc);
            const pageSize = { width: 595.28, height: 841.89 };
            const margins = { top: 38, right: 40, bottom: 42, left: 40 };
            const contentWidth = pageSize.width - margins.left - margins.right;
            let page = null;
            let y = 0;
            let pageNumber = 0;

            pdfDoc.setTitle(`${getReportDocumentTitle()} - ${snapshot.heading.title}`);
            pdfDoc.setAuthor('Library Management System');
            pdfDoc.setCreator('Library Management System');
            pdfDoc.setProducer('Library Management System');
            pdfDoc.setSubject(snapshot.heading.description || snapshot.heading.title || getActiveReportTypeLabel());
            pdfDoc.setCreationDate(snapshot.generatedAt);
            pdfDoc.setModificationDate(new Date());

            const drawPageHeader = () => {
                const topY = pageSize.height - margins.top;
                const logoSize = 44;
                const brandingGap = 14;
                const systemTitle = normalizePdfTextValue(getReportSystemTitle().toUpperCase());
                const reportTitle = normalizePdfTextValue(getReportDocumentTitle().toUpperCase());
                const generatedLabel = normalizePdfTextValue(buildReportGeneratedLabel(snapshot.generatedAt));
                const brandingTextWidth = Math.max(
                    fonts.bold.widthOfTextAtSize(systemTitle, 18),
                    fonts.bold.widthOfTextAtSize(reportTitle, 11.5),
                    fonts.regular.widthOfTextAtSize(generatedLabel, 10)
                );
                const brandingGroupWidth = logoSize + brandingGap + brandingTextWidth;
                const logoX = margins.left + Math.max(0, (contentWidth - brandingGroupWidth) / 2);
                const logoY = topY - logoSize + 2;
                const textX = logoX + logoSize + brandingGap;
                const fallbackText = getReportBrandingFallbackText();

                if (logoImage) {
                    page.drawImage(logoImage, {
                        x: logoX,
                        y: logoY,
                        width: logoSize,
                        height: logoSize,
                    });
                } else {
                    page.drawCircle({
                        x: logoX + (logoSize / 2),
                        y: logoY + (logoSize / 2),
                        size: logoSize / 2,
                        color: colors.accentSoft,
                        borderColor: colors.border,
                        borderWidth: 1,
                    });

                    const fallbackSize = 12;
                    const fallbackWidth = fonts.bold.widthOfTextAtSize(fallbackText, fallbackSize);

                    page.drawText(normalizePdfTextValue(fallbackText), {
                        x: logoX + ((logoSize - fallbackWidth) / 2),
                        y: logoY + ((logoSize - fallbackSize) / 2) + 2,
                        font: fonts.bold,
                        size: fallbackSize,
                        color: colors.accent,
                    });
                }

                page.drawText(normalizePdfTextValue(systemTitle), {
                    x: textX,
                    y: topY - 2,
                    font: fonts.bold,
                    size: 18,
                    color: colors.ink,
                });
                page.drawText(normalizePdfTextValue(reportTitle), {
                    x: textX,
                    y: topY - 20,
                    font: fonts.bold,
                    size: 11.5,
                    color: colors.accent,
                });
                page.drawText(normalizePdfTextValue(generatedLabel), {
                    x: textX,
                    y: topY - 36,
                    font: fonts.regular,
                    size: 10,
                    color: colors.body,
                });
                page.drawText(normalizePdfTextValue(`Page ${pageNumber}`), {
                    x: pageSize.width - margins.right - 34,
                    y: topY - 36,
                    font: fonts.regular,
                    size: 9,
                    color: colors.muted,
                });
                page.drawLine({
                    start: { x: Math.max(margins.left, logoX - 4), y: topY - 46 },
                    end: { x: Math.min(pageSize.width - margins.right, logoX + brandingGroupWidth + 4), y: topY - 46 },
                    thickness: 1,
                    color: colors.border,
                });

                return topY - 66;
            };

            const addPage = () => {
                page = pdfDoc.addPage([pageSize.width, pageSize.height]);
                pageNumber += 1;
                y = drawPageHeader();
            };

            const ensureSpace = (heightNeeded = 24) => {
                if (!page) {
                    addPage();
                    return;
                }

                if ((y - heightNeeded) < margins.bottom) {
                    addPage();
                }
            };

            const drawSectionTitle = (title) => {
                ensureSpace(24);
                page.drawText(normalizePdfTextValue(title), {
                    x: margins.left,
                    y,
                    font: fonts.bold,
                    size: 13,
                    color: colors.ink,
                });
                y -= 20;
            };

            const drawDetailCards = (items) => {
                if (!items.length) {
                    return;
                }

                const gap = 10;
                const cardWidth = (contentWidth - gap) / 2;

                for (let index = 0; index < items.length; index += 2) {
                    const rowItems = items.slice(index, index + 2);
                    const rowHeight = Math.max(...rowItems.map((item) => {
                        const valueLines = splitPdfTextLines(item.value, cardWidth - 20, fonts.regular, 10.5);
                        return Math.max(38, 24 + (valueLines.length * 13));
                    }));

                    ensureSpace(rowHeight + 6);
                    const rowTop = y;

                    rowItems.forEach((item, columnIndex) => {
                        const cardX = margins.left + (columnIndex * (cardWidth + gap));
                        const valueLines = splitPdfTextLines(item.value, cardWidth - 20, fonts.regular, 10.5);

                        page.drawRectangle({
                            x: cardX,
                            y: rowTop - rowHeight,
                            width: cardWidth,
                            height: rowHeight,
                            color: colors.surface,
                            borderColor: colors.border,
                            borderWidth: 1,
                        });
                        page.drawText(normalizePdfTextValue(String(item.label || '').toUpperCase()), {
                            x: cardX + 10,
                            y: rowTop - 14,
                            font: fonts.bold,
                            size: 8.2,
                            color: colors.muted,
                        });
                        drawPdfTextLines(page, valueLines, {
                            x: cardX + 10,
                            y: rowTop - 28,
                            font: fonts.regular,
                            fontSize: 10.5,
                            lineHeight: 13,
                            color: colors.ink,
                        });
                    });

                    y = rowTop - rowHeight - 10;
                }
            };

            const drawStatsSection = () => {
                if (!snapshot.stats.length) {
                    return;
                }

                drawSectionTitle('Summary');

                const gap = 10;
                const cardWidth = (contentWidth - gap) / 2;

                for (let index = 0; index < snapshot.stats.length; index += 2) {
                    const rowStats = snapshot.stats.slice(index, index + 2);
                    const rowHeight = Math.max(...rowStats.map((stat) => {
                        const subtitleLines = splitPdfTextLines(stat.subtitle || '', cardWidth - 20, fonts.regular, 9);
                        return Math.max(56, 36 + (subtitleLines.length * 11));
                    }));

                    ensureSpace(rowHeight + 8);
                    const rowTop = y;

                    rowStats.forEach((stat, columnIndex) => {
                        const cardX = margins.left + (columnIndex * (cardWidth + gap));
                        const subtitleLines = splitPdfTextLines(stat.subtitle || '', cardWidth - 20, fonts.regular, 9);

                        page.drawRectangle({
                            x: cardX,
                            y: rowTop - rowHeight,
                            width: cardWidth,
                            height: rowHeight,
                            color: colors.surface,
                            borderColor: colors.border,
                            borderWidth: 1,
                        });
                        page.drawText(normalizePdfTextValue(String(stat.label || '').toUpperCase()), {
                            x: cardX + 10,
                            y: rowTop - 14,
                            font: fonts.bold,
                            size: 8,
                            color: colors.muted,
                        });
                        page.drawText(normalizePdfTextValue(String(stat.value || '')), {
                            x: cardX + 10,
                            y: rowTop - 32,
                            font: fonts.bold,
                            size: 15,
                            color: colors.ink,
                        });

                        if (subtitleLines.length) {
                            drawPdfTextLines(page, subtitleLines, {
                                x: cardX + 10,
                                y: rowTop - 47,
                                font: fonts.regular,
                                fontSize: 9,
                                lineHeight: 11,
                                color: colors.body,
                            });
                        }
                    });

                    y = rowTop - rowHeight - 10;
                }
            };

            const drawChartsSection = async () => {
                if (!snapshot.charts.length) {
                    return;
                }

                drawSectionTitle('Charts');

                for (const chart of snapshot.charts) {
                    const image = await safeEmbedPdfImage(pdfDoc, chart.image);
                    const titleLines = splitPdfTextLines(chart.title || 'Chart', contentWidth - 24, fonts.bold, 12);
                    const descriptionLines = splitPdfTextLines(chart.description || 'Included in this report export.', contentWidth - 24, fonts.regular, 9.2);
                    const imageSize = image
                        ? image.scaleToFit(contentWidth - 24, 180)
                        : { width: 0, height: 0 };
                    const cardHeight = Math.max(
                        72,
                        20 + (titleLines.length * 14) + (descriptionLines.length * 11) + (image ? imageSize.height + 18 : 0)
                    );

                    ensureSpace(cardHeight + 8);
                    const cardTop = y;
                    const cardX = margins.left;

                    page.drawRectangle({
                        x: cardX,
                        y: cardTop - cardHeight,
                        width: contentWidth,
                        height: cardHeight,
                        color: colors.white,
                        borderColor: colors.border,
                        borderWidth: 1,
                    });

                    let textY = cardTop - 14;

                    drawPdfTextLines(page, titleLines, {
                        x: cardX + 12,
                        y: textY,
                        font: fonts.bold,
                        fontSize: 12,
                        lineHeight: 14,
                        color: colors.ink,
                    });
                    textY -= Math.max(14, titleLines.length * 14);

                    if (descriptionLines.length) {
                        drawPdfTextLines(page, descriptionLines, {
                            x: cardX + 12,
                            y: textY,
                            font: fonts.regular,
                            fontSize: 9.2,
                            lineHeight: 11,
                            color: colors.body,
                        });
                    }

                    if (image) {
                        page.drawImage(image, {
                            x: cardX + ((contentWidth - imageSize.width) / 2),
                            y: cardTop - cardHeight + 12,
                            width: imageSize.width,
                            height: imageSize.height,
                        });
                    }

                    y = cardTop - cardHeight - 10;
                }
            };

            const getTableColumnWidths = (headers) => {
                const count = Math.max(1, headers.length);
                return Array.from({ length: count }, () => contentWidth / count);
            };

            const getTableRowMetrics = (cells, widths, isHeader = false) => {
                const font = isHeader ? fonts.bold : fonts.regular;
                const fontSize = isHeader ? 8.5 : 8.2;
                const lineHeight = isHeader ? 10.5 : 10;
                const paddingX = 5;
                const paddingY = 4;
                const lines = cells.map((cell, index) => splitPdfTextLines(
                    cell,
                    Math.max(26, widths[index] - (paddingX * 2)),
                    font,
                    fontSize
                ));
                const rowHeight = Math.max(
                    18,
                    ...lines.map((cellLines) => (cellLines.length * lineHeight) + (paddingY * 2))
                );

                return {
                    font,
                    fontSize,
                    lineHeight,
                    paddingX,
                    paddingY,
                    lines,
                    rowHeight,
                };
            };

            const drawTableRow = (cells, widths, options = {}) => {
                const {
                    isHeader = false,
                    rowIndex = 0,
                } = options;
                const metrics = getTableRowMetrics(cells, widths, isHeader);
                const fillColor = isHeader
                    ? colors.headerFill
                    : (rowIndex % 2 === 0 ? colors.white : colors.surface);

                ensureSpace(metrics.rowHeight + 2);
                const rowTop = y;
                let cellX = margins.left;

                cells.forEach((cell, index) => {
                    const cellWidth = widths[index] || widths[widths.length - 1] || contentWidth;

                    page.drawRectangle({
                        x: cellX,
                        y: rowTop - metrics.rowHeight,
                        width: cellWidth,
                        height: metrics.rowHeight,
                        color: fillColor,
                        borderColor: colors.border,
                        borderWidth: 0.8,
                    });
                    drawPdfTextLines(page, metrics.lines[index], {
                        x: cellX + metrics.paddingX,
                        y: rowTop - metrics.paddingY - metrics.fontSize + 1,
                        font: metrics.font,
                        fontSize: metrics.fontSize,
                        lineHeight: metrics.lineHeight,
                        color: isHeader ? colors.ink : colors.body,
                    });

                    cellX += cellWidth;
                });

                y = rowTop - metrics.rowHeight;
            };

            const drawTableHeading = (table, isContinuation = false) => {
                const titleText = isContinuation ? `${table.title} (continued)` : table.title;
                const titleLines = splitPdfTextLines(titleText, contentWidth, fonts.bold, 11.5);
                const descriptionLines = isContinuation
                    ? []
                    : splitPdfTextLines(table.description || 'Included table data for this report.', contentWidth, fonts.regular, 9.2);
                const neededHeight = (titleLines.length * 13) + (descriptionLines.length * 11) + 8;

                ensureSpace(neededHeight + 10);
                drawPdfTextLines(page, titleLines, {
                    x: margins.left,
                    y,
                    font: fonts.bold,
                    fontSize: 11.5,
                    lineHeight: 13,
                    color: colors.ink,
                });
                y -= Math.max(13, titleLines.length * 13);

                if (descriptionLines.length) {
                    drawPdfTextLines(page, descriptionLines, {
                        x: margins.left,
                        y,
                        font: fonts.regular,
                        fontSize: 9.2,
                        lineHeight: 11,
                        color: colors.body,
                    });
                    y -= Math.max(11, descriptionLines.length * 11);
                }

                y -= 6;
            };

            const drawTablesSection = () => {
                if (!snapshot.tables.length) {
                    return;
                }

                drawSectionTitle('Tables');

                snapshot.tables.forEach((table) => {
                    const widths = getTableColumnWidths(table.headers);

                    drawTableHeading(table);
                    drawTableRow(table.headers, widths, { isHeader: true, rowIndex: 0 });

                    if (!table.rows.length) {
                        ensureSpace(22);
                        page.drawText(normalizePdfTextValue('No rows available for this table.'), {
                            x: margins.left,
                            y: y - 10,
                            font: fonts.regular,
                            size: 9.5,
                            color: colors.body,
                        });
                        y -= 24;
                        return;
                    }

                    table.rows.forEach((row, rowIndex) => {
                        const metrics = getTableRowMetrics(row, widths, false);

                        if ((y - metrics.rowHeight) < margins.bottom) {
                            addPage();
                            drawTableHeading(table, true);
                            drawTableRow(table.headers, widths, { isHeader: true, rowIndex: 0 });
                        }

                        drawTableRow(row, widths, { isHeader: false, rowIndex });
                    });

                    y -= 12;
                });
            };

            addPage();

            const introTitleLines = splitPdfTextLines(snapshot.heading.title, contentWidth, fonts.bold, 16.5);
            const introDescriptionLines = splitPdfTextLines(snapshot.heading.description, contentWidth, fonts.regular, 10.2);
            ensureSpace((introTitleLines.length * 18) + (introDescriptionLines.length * 12) + 12);
            drawPdfTextLines(page, introTitleLines, {
                x: margins.left,
                y,
                font: fonts.bold,
                fontSize: 16.5,
                lineHeight: 18,
                color: colors.ink,
            });
            y -= Math.max(18, introTitleLines.length * 18);

            if (introDescriptionLines.length) {
                drawPdfTextLines(page, introDescriptionLines, {
                    x: margins.left,
                    y,
                    font: fonts.regular,
                    fontSize: 10.2,
                    lineHeight: 12,
                    color: colors.body,
                });
                y -= Math.max(12, introDescriptionLines.length * 12);
            }

            y -= 10;
            drawDetailCards(documentDetails);
            drawStatsSection();
            await drawChartsSection();
            drawTablesSection();

            const pdfBytes = await pdfDoc.save();
            const pdfBlob = new Blob([pdfBytes], { type: 'application/pdf' });
            const pdfUrl = URL.createObjectURL(pdfBlob);
            const link = document.createElement('a');

            link.href = pdfUrl;
            link.download = buildReportPdfFilename(snapshot);
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            window.setTimeout(() => URL.revokeObjectURL(pdfUrl), 1000);

            return snapshot;
        }

        function openReportPdfPreviewModal() {
            const snapshot = buildReportSnapshot();
            reportPdfPreviewState.snapshot = snapshot;
            reportPdfPreviewState.lastFocusedElement = document.activeElement;

            if (reportPdfPreviewContent) {
                reportPdfPreviewContent.innerHTML = buildReportPreviewMarkup(snapshot, { limitRows: 6 });
            }

            if (reportPdfPreviewFooterNote) {
                reportPdfPreviewFooterNote.textContent = 'Export PDF downloads a branded PDF copy of this report.';
            }

            reportPdfPreviewModal?.classList.add('is-open');
            reportPdfPreviewModal?.setAttribute('aria-hidden', 'false');
            document.body.classList.add('report-pdf-preview-open');
            reportPdfPreviewModal?.querySelector('.report-pdf-body')?.scrollTo?.({ top: 0, behavior: 'auto' });

            window.setTimeout(() => {
                reportPdfPreviewClose?.focus?.();
            }, 20);
        }

        function closeReportPdfPreviewModal() {
            reportPdfPreviewModal?.classList.remove('is-open');
            reportPdfPreviewModal?.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('report-pdf-preview-open');

            const focusTarget = reportPdfPreviewState.lastFocusedElement;
            if (typeof focusTarget?.focus === 'function') {
                window.setTimeout(() => focusTarget.focus(), 20);
            }
        }

        function buildReportPrintDocument(snapshot) {
            const totalRows = snapshot.tables.reduce((sum, table) => sum + table.rows.length, 0);
            const visibleMetaItems = snapshot.metaItems.filter((item) => !String(item).startsWith('Generated:'));

            return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>${escapeHtml(snapshot.heading.title)}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: Inter, Arial, sans-serif;
            color: #0f172a;
            background: #ffffff;
        }
        .print-shell {
            padding: 8px 10px 0;
        }
        .print-header {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #cbd5e1;
        }
        .print-branding-row {
            display: flex;
            align-items: center;
            gap: 14px;
            justify-content: center;
            width: fit-content;
            max-width: 100%;
            margin: 0 auto;
        }
        .print-logo {
            width: 62px;
            height: 62px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 999px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            flex-shrink: 0;
        }
        .print-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            background: #ffffff;
        }
        .print-logo-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #2563eb;
        }
        .print-branding-copy {
            min-width: 0;
            text-align: left;
        }
        .print-system-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .print-report-title {
            margin: 4px 0 0;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #2563eb;
        }
        .print-report-meta {
            margin: 6px 0 0;
            font-size: 11px;
            font-weight: 500;
            color: #475569;
        }
        .print-header-divider {
            max-width: 640px;
            margin: 12px auto 0;
            border-top: 1px solid #dbe3ef;
        }
        .print-overview {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-top: 16px;
        }
        .print-overview-title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }
        .print-overview-description {
            margin: 6px 0 0;
            font-size: 12px;
            line-height: 1.5;
            color: #475569;
        }
        .print-count {
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            color: #1d4ed8;
            background: rgba(37, 99, 235, 0.08);
            white-space: nowrap;
        }
        .print-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .print-meta-pill {
            padding: 7px 10px;
            border-radius: 999px;
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
        }
        .print-section {
            margin-bottom: 20px;
        }
        .print-section-title {
            margin: 0 0 10px;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }
        .print-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }
        .print-stat-card {
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #dbe3ef;
            background: #f8fafc;
        }
        .print-stat-label {
            margin: 0 0 6px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
        }
        .print-stat-value {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
        }
        .print-stat-subtitle {
            margin: 6px 0 0;
            font-size: 11px;
            line-height: 1.4;
            color: #475569;
        }
        .print-chart-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .print-chart-card {
            border: 1px solid #dbe3ef;
            border-radius: 14px;
            overflow: hidden;
            background: #ffffff;
            page-break-inside: avoid;
        }
        .print-chart-copy {
            padding: 12px 12px 0;
        }
        .print-chart-title {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }
        .print-chart-description {
            margin: 6px 0 0;
            font-size: 11px;
            line-height: 1.4;
            color: #64748b;
        }
        .print-chart-image-wrap {
            padding: 12px;
        }
        .print-chart-image {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .print-table-card {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .print-table-title {
            margin: 0 0 6px;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
        }
        .print-table-description {
            margin: 0 0 10px;
            font-size: 11px;
            line-height: 1.4;
            color: #64748b;
        }
        .print-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #cbd5e1;
        }
        .print-table th,
        .print-table td {
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
            border: 1px solid #cbd5e1;
            word-break: break-word;
            font-size: 11px;
            line-height: 1.35;
        }
        .print-table th {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #0f172a;
            background: #e2e8f0;
        }
        .print-table .table-count {
            text-align: right;
        }
        .print-empty {
            font-size: 12px;
            line-height: 1.5;
            color: #64748b;
        }
        @media print {
            .print-chart-card,
            .print-table-card {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-shell">
        <div class="print-header">
            <div class="print-branding-row">
                <div class="print-logo">${buildPrintLogoMarkup()}</div>
                <div>
                    <p class="print-system-title">${escapeHtml(getReportSystemTitle())}</p>
                    <p class="print-report-title">${escapeHtml(getReportDocumentTitle())}</p>
                    <p class="print-report-meta">${escapeHtml(buildReportGeneratedLabel(snapshot.generatedAt))}</p>
                </div>
            </div>
            <div class="print-header-divider"></div>
            <div class="print-overview">
                <div>
                    <h1 class="print-overview-title">${escapeHtml(snapshot.heading.title)}</h1>
                    <p class="print-overview-description">${escapeHtml(snapshot.heading.description)}</p>
                </div>
                <span class="print-count">${escapeHtml(formatNumber(totalRows))} table rows</span>
            </div>
            <div class="print-meta">
                ${visibleMetaItems.map((item) => `<span class="print-meta-pill">${escapeHtml(item)}</span>`).join('')}
            </div>
        </div>

        <section class="print-section">
            <h2 class="print-section-title">Summary</h2>
            <div class="print-stats-grid">
                ${snapshot.stats.map((stat) => `
                    <div class="print-stat-card">
                        <p class="print-stat-label">${escapeHtml(stat.label)}</p>
                        <p class="print-stat-value">${escapeHtml(stat.value)}</p>
                        <p class="print-stat-subtitle">${escapeHtml(stat.subtitle)}</p>
                    </div>
                `).join('')}
            </div>
        </section>

        <section class="print-section">
            <h2 class="print-section-title">Charts</h2>
            <div class="print-chart-grid">
                ${snapshot.charts.map((chart) => `
                    <article class="print-chart-card">
                        <div class="print-chart-copy">
                            <h3 class="print-chart-title">${escapeHtml(chart.title)}</h3>
                            <p class="print-chart-description">${escapeHtml(chart.description)}</p>
                        </div>
                        <div class="print-chart-image-wrap">
                            <img class="print-chart-image" src="${chart.image}" alt="${escapeHtml(chart.title)}">
                        </div>
                    </article>
                `).join('')}
            </div>
        </section>

        <section class="print-section">
            <h2 class="print-section-title">Tables</h2>
            ${snapshot.tables.map((table) => `
                <article class="print-table-card">
                    <h3 class="print-table-title">${escapeHtml(table.title)}</h3>
                    <p class="print-table-description">${escapeHtml(table.description)}</p>
                    ${table.rows.length
                        ? `
                            <table class="print-table">
                                <thead>
                                    <tr>
                                        ${table.headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('')}
                                    </tr>
                                </thead>
                                <tbody>
                                    ${table.rows.map((row) => `
                                        <tr>
                                            ${row.map((cell, index) => `<td class="${table.headers[index]?.toLowerCase().includes('count') || table.headers[index]?.toLowerCase().includes('amount') || table.headers[index]?.toLowerCase().includes('days') ? 'table-count' : ''}">${escapeHtml(cell)}</td>`).join('')}
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        `
                        : `<p class="print-empty">No rows available for this table.</p>`}
                </article>
            `).join('')}
        </section>
    </div>
    <script>
        window.addEventListener('load', function () {
            window.setTimeout(function () {
                window.focus();
                window.print();
            }, 160);
        });
        window.addEventListener('afterprint', function () {
            window.close();
        });
    <\/script>
</body>
</html>`;
        }

        function openReportPrintWindow(mode = 'print', scope = 'all', workflow = null) {
            const generatedAt = workflow?.getContext?.()?.generatedAt || new Date();
            const snapshot = buildReportSnapshot(scope, generatedAt);
            const printWindow = window.open('', '_blank', 'width=1180,height=820');

            if (!printWindow) {
                if (workflow && typeof workflow.showToast === 'function') {
                    const messages = workflow.getMessages();
                    workflow.showToast('error', messages.popupBlockedTitle, messages.popupBlockedMessage);
                } else {
                    window.alert(mode === 'pdf'
                        ? 'Please allow popups so the browser can open the PDF export dialog.'
                        : 'Please allow popups so the browser can open the print dialog.');
                }
                return false;
            }

            const documentMarkup = buildReportPrintDocument(snapshot);

            printWindow.document.open();
            printWindow.document.write(documentMarkup);
            printWindow.document.close();

            if (workflow && typeof workflow.config?.closeModal === 'function') {
                workflow.config.closeModal(workflow.getModalId());
            } else {
                closeReportPdfPreviewModal();
            }

            return true;
        }

        function openReportExportModal(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            reportExportModalState.lastFocusedElement = document.activeElement;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            modal.scrollTop = 0;

            const panel = modal.querySelector('.report-export-panel');
            panel?.scrollTo?.({ top: 0, behavior: 'auto' });

            window.setTimeout(() => {
                panel?.querySelector('.report-export-close-btn')?.focus?.();
            }, 20);
        }

        function closeReportExportModal(modalId = 'reportExportModal') {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            const wasOpen = modal.classList.contains('is-open');
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            if (wasOpen) {
                reportExportWorkflow?.handleModalClosed?.();
            }

            const focusTarget = reportExportModalState.lastFocusedElement;
            if (wasOpen && typeof focusTarget?.focus === 'function') {
                window.setTimeout(() => focusTarget.focus(), 20);
            }
        }

        function showReportExportMessage(type, title, message) {
            if (type === 'error' || type === 'info') {
                window.alert(`${title}\n\n${message}`);
                return;
            }

            console.info(`[${title}] ${message}`);
        }

        function buildReportExportDocumentDetails(scope = 'page') {
            const details = [
                { label: 'Report Type', value: getActiveReportTypeLabel() },
                { label: 'Time Period', value: timePeriodSelect?.selectedOptions?.[0]?.textContent?.trim() || 'Selected period' },
                { label: 'Scope', value: scope === 'all' ? 'Filtered report' : 'Current page' },
            ];

            if (timePeriodSelect?.value === 'custom') {
                const startDate = document.getElementById('startDate')?.value || '--';
                const endDate = document.getElementById('endDate')?.value || '--';
                details.push({ label: 'Date Range', value: `${startDate} to ${endDate}` });
            }

            return details;
        }

        function initReportExportWorkflow() {
            if (typeof window.ReportExportWorkflow !== 'function' || reportExportWorkflow) {
                return;
            }

            reportExportWorkflow = new window.ReportExportWorkflow({
                modalId: 'reportExportModal',
                idPrefix: 'reportExport',
                scopeName: 'reportExportScope',
                document: {
                    reportTitle: 'Library Report',
                },
                labels: {
                    printButton: 'Print',
                    allScopePrintButton: 'Print Full Report',
                    downloadButton: 'Export PDF',
                    allScopeDownloadButton: 'Export Full PDF',
                },
                messages: {
                    emptyMessage: 'There are no report rows in the selected view.',
                    preparingMessage: 'Please wait while the full report is prepared.',
                    popupBlockedMessage: 'Allow popups for this site to open the print view.',
                },
                columns: [
                    { key: 'section', label: 'Section', width: '22%', emphasis: true },
                    { key: 'source', label: 'Item', width: '24%' },
                    { key: 'details', label: 'Details', width: '54%' },
                ],
                openModal: (modalId, focusTarget) => openReportExportModal(modalId, focusTarget),
                closeModal: (modalId) => closeReportExportModal(modalId),
                showToast: (type, title, message) => showReportExportMessage(type, title, message),
                buildFilterParams: () => {
                    const params = new URLSearchParams();

                    params.set('report_type', getActiveReportType());
                    params.set('time_period', timePeriodSelect?.value || '');

                    if (timePeriodSelect?.value === 'custom') {
                        params.set('start_date', document.getElementById('startDate')?.value || '');
                        params.set('end_date', document.getElementById('endDate')?.value || '');
                    }

                    return params;
                },
                getCurrentRows: () => buildReportExportRows('page'),
                getAllRows: () => ({
                    rows: buildReportExportRows('all'),
                    generatedAt: new Date(),
                }),
                getListingState: () => {
                    const currentRows = buildReportExportRows('page');
                    const allRows = buildReportExportRows('all');

                    return {
                        total: allRows.length,
                        currentPage: 1,
                        lastPage: 1,
                        perPage: Math.max(1, currentRows.length || allRows.length || 1),
                    };
                },
                getScopeLabel: (scope) => scope === 'all' ? 'Filtered report' : 'Current page',
                getDocumentDetails: (context) => buildReportExportDocumentDetails(context.isAllScope ? 'all' : 'page'),
                describeContext: (context) => {
                    const scope = context.isAllScope ? 'all' : 'page';
                    const snapshot = buildReportSnapshot(scope);
                    const rowLabel = `${formatNumber(context.rowsReady)} ${context.rowsReady === 1 ? 'row' : 'rows'}`;

                    return {
                        badgeLabel: context.isAllScope ? 'Filtered report' : 'Current page',
                        headline: `${rowLabel} ready for export`,
                        subtext: `${snapshot.heading.title} includes ${formatNumber(snapshot.charts.length)} charts, ${formatNumber(snapshot.tables.length)} tables, and ${formatNumber(snapshot.stats.length)} summary metrics.`,
                        previewCaption: context.isAllScope
                            ? 'Rows from all tables matching the selected report filters.'
                            : 'Rows currently visible on the active table pages.',
                        previewCountText: rowLabel,
                        footerNote: context.isAllScope
                            ? 'Export PDF and print will include the full filtered report.'
                            : 'Export PDF and print will include only the rows visible on the current table pages.',
                        emptyMessage: 'No report rows are available for the selected scope.',
                        summaryItems: [
                            { label: 'Report', value: snapshot.heading.title },
                            { label: 'Scope', value: context.isAllScope ? 'Filtered report' : 'Current page' },
                            { label: 'Summary', value: `${formatNumber(snapshot.stats.length)} metrics` },
                            { label: 'Charts', value: `${formatNumber(snapshot.charts.length)} included` },
                            { label: 'Tables', value: `${formatNumber(snapshot.tables.length)} included` },
                            { label: 'Rows', value: rowLabel },
                        ],
                    };
                },
            }).init();

            reportExportWorkflow.print = function () {
                const context = this.getContext();
                const messages = this.getMessages();

                if (context.isLoading) {
                    this.showToast('info', messages.preparingTitle, messages.preparingMessage);
                    return;
                }

                if (context.rows.length === 0) {
                    this.showToast('error', messages.emptyTitle, messages.emptyMessage);
                    return;
                }

                openReportPrintWindow('print', this.state.scope === 'all' ? 'all' : 'page', this);
            };

            reportExportWorkflow.download = async function () {
                const context = this.getContext();
                const messages = this.getMessages();

                if (context.isLoading) {
                    this.showToast('info', messages.preparingTitle, messages.preparingMessage);
                    return;
                }

                if (context.rows.length === 0) {
                    this.showToast('error', messages.emptyTitle, messages.emptyMessage);
                    return;
                }

                const downloadButton = this.elements?.downloadBtn;
                const downloadButtonLabel = this.elements?.downloadBtnLabel;
                const originalLabel = downloadButtonLabel?.textContent || 'Export PDF';

                if (downloadButton) {
                    downloadButton.disabled = true;
                }

                if (downloadButtonLabel) {
                    downloadButtonLabel.textContent = 'Generating PDF...';
                }

                try {
                    await downloadReportPdf(this.state.scope === 'all' ? 'all' : 'page', context.generatedAt);

                    if (typeof this.config.closeModal === 'function') {
                        this.config.closeModal(this.getModalId());
                    }

                    this.showToast(
                        'success',
                        messages.exportReadyTitle,
                        context.isAllScope
                            ? 'The full filtered report PDF has been downloaded.'
                            : 'The current report PDF has been downloaded.'
                    );
                } catch (error) {
                    console.error('[Reports] Failed to export report PDF:', error);
                    this.showToast('error', 'Export failed', 'The PDF file could not be generated. Please try again.');
                } finally {
                    if (downloadButtonLabel) {
                        downloadButtonLabel.textContent = originalLabel;
                    }

                    this.render();
                }
            };
        }

        function buildDoughnutChartOptions(colors, config = {}) {
            const options = baseChartOptions(colors);
            const total = Number(config.total || 0);

            return {
                ...options,
                interaction: {
                    mode: 'point',
                    intersect: true,
                },
                onHover(event, activeElements, chart) {
                    chart.canvas.style.cursor = activeElements.length ? 'pointer' : 'default';
                },
                plugins: {
                    ...options.plugins,
                    legend: {
                        position: config.legendPosition || 'top',
                        labels: {
                            ...options.plugins.legend.labels,
                            font: { size: 11, weight: '600' },
                            padding: config.legendPosition === 'bottom' ? 12 : 16,
                        },
                    },
                    tooltip: {
                        ...options.plugins.tooltip,
                        position: 'nearest',
                        callbacks: {
                            title(context) {
                                return context[0]?.label || '';
                            },
                            label(context) {
                                if (config.emptyMessage && total === 0) {
                                    return config.emptyMessage;
                                }

                                const value = Number(context.raw ?? 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                                return `${formatNumber(value)} ${config.valueLabel || 'items'} (${percentage}%)`;
                            },
                        },
                    },
                },
            };
        }

        function buildSingleSeries(source, fallbackLabel) {
            const hasData = Array.isArray(source?.data) && source.data.some((value) => Number(value) > 0);

            return {
                labels: hasData && Array.isArray(source?.labels) && source.labels.length ? source.labels : [fallbackLabel],
                data: hasData && Array.isArray(source?.data) && source.data.length ? source.data : [1],
                empty: !hasData,
            };
        }

        function pointRadius(labelCount) {
            return labelCount > 60 ? 0 : 4;
        }

        function getActiveCategoryView() {
            return categoryViewButtons.find((button) => button.classList.contains('is-active'))?.dataset.categoryView || 'bar';
        }

        function updateCategoryViewButtons(activeView) {
            categoryViewButtons.forEach((button) => {
                const isActive = button.dataset.categoryView === activeView;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function buildRankedCategorySeries(source, limit) {
            const items = (Array.isArray(source?.labels) ? source.labels : [])
                .map((label, index) => ({
                    label,
                    count: Number(source?.data?.[index] ?? 0),
                }))
                .filter((item) => item.count > 0)
                .sort((left, right) => right.count - left.count || left.label.localeCompare(right.label));

            if (!items.length) {
                return {
                    labels: ['No categories'],
                    data: [1],
                    items: [],
                    total: 0,
                    empty: true,
                    limit,
                    originalCategoryCount: 0,
                    visibleCategoryCount: 0,
                    groupedCategoryCount: 0,
                };
            }

            const safeLimit = Math.max(1, Math.min(limit, items.length));
            const visibleItems = items.slice(0, safeLimit);
            const groupedItems = items.slice(safeLimit);
            const othersTotal = groupedItems.reduce((total, item) => total + item.count, 0);

            if (othersTotal > 0) {
                visibleItems.push({
                    label: 'Others',
                    count: othersTotal,
                    isOthers: true,
                });
            }

            return {
                labels: visibleItems.map((item) => item.label),
                data: visibleItems.map((item) => item.count),
                items: visibleItems,
                total: items.reduce((total, item) => total + item.count, 0),
                empty: false,
                limit: safeLimit,
                originalCategoryCount: items.length,
                visibleCategoryCount: Math.min(safeLimit, items.length),
                groupedCategoryCount: groupedItems.length,
            };
        }

        function buildCategoryColors(series, view) {
            if (series.empty) {
                return ['#cbd5e1'];
            }

            const palette = view === 'doughnut' ? CATEGORY_DONUT_COLORS : CATEGORY_BAR_COLORS;

            return series.items.map((item, index) => item.isOthers ? '#94a3b8' : palette[index % palette.length]);
        }

        function updateCategoryChartInsight(series, view) {
            if (!categoryChartInsight) {
                return;
            }

            if (series.empty) {
                categoryChartInsight.innerHTML = `
                    <span class="chart-note-pill">No Data</span>
                    <span>Category distribution will appear here once books are assigned to categories.</span>
                `;

                return;
            }

            const viewLabel = view === 'doughnut' ? `Top ${series.limit} Share` : `Top ${series.limit} Ranked`;
            const groupedSummary = series.groupedCategoryCount > 0
                ? `${formatNumber(series.groupedCategoryCount)} additional categories are grouped into Others.`
                : 'All active categories are visible in this chart.';

            categoryChartInsight.innerHTML = `
                <span class="chart-note-pill">${viewLabel}</span>
                <span>Showing ${formatNumber(series.visibleCategoryCount)} of ${formatNumber(series.originalCategoryCount)} categories by book count. ${groupedSummary}</span>
            `;
        }

        function exportChartImage(chartId, fileBaseName) {
            const chart = chartInstances[chartId];

            if (!chart) {
                return;
            }

            const link = document.createElement('a');
            const dateStamp = new Date().toISOString().slice(0, 10);

            link.href = chart.toBase64Image('image/png', 1);
            link.download = `${fileBaseName}-${dateStamp}.png`;

            document.body.appendChild(link);
            link.click();
            link.remove();
        }

        function createCategoryChart() {
            const colors = getThemeColors();
            const options = baseChartOptions(colors);
            const selectedLimit = Number(categoryChartLimitSelect?.value || 8);
            const activeView = getActiveCategoryView();
            const effectiveLimit = activeView === 'doughnut' ? Math.min(selectedLimit, 6) : selectedLimit;
            const chartData = buildRankedCategorySeries(reportCharts.inventory.category, effectiveLimit);
            const totalBooks = chartData.total;
            const chartColors = buildCategoryColors(chartData, activeView);

            updateCategoryChartInsight(chartData, activeView);

            if (activeView === 'doughnut') {
                createOrReplaceChart('categoryChart', {
                    type: 'doughnut',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: chartColors,
                            borderColor: colors.surfaceBorder,
                            borderWidth: 2,
                            hoverOffset: 8,
                            spacing: 2,
                            cutout: '62%',
                        }],
                    },
                    options: buildDoughnutChartOptions(colors, {
                        legendPosition: 'bottom',
                        total: totalBooks,
                        valueLabel: 'books',
                        emptyMessage: 'No category data',
                    }),
                });

                return;
            }

            createOrReplaceChart('categoryChart', {
                type: 'bar',
                plugins: [valueLabelPlugin],
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Books',
                        data: chartData.data,
                        backgroundColor: chartColors,
                        borderColor: chartColors,
                        borderWidth: 0,
                        borderRadius: 12,
                        borderSkipped: false,
                        barThickness: 18,
                        maxBarThickness: 22,
                    }],
                },
                options: {
                    ...options,
                    indexAxis: 'y',
                    layout: {
                        padding: {
                            top: 8,
                            right: 40,
                            bottom: 8,
                            left: 8,
                        },
                    },
                    plugins: {
                        ...options.plugins,
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            ...options.plugins.tooltip,
                            callbacks: {
                                title(context) {
                                    return context[0]?.label || '';
                                },
                                label(context) {
                                    if (chartData.empty) {
                                        return 'No category data';
                                    }

                                    const value = Number(context.raw ?? 0);
                                    const percentage = totalBooks > 0 ? ((value / totalBooks) * 100).toFixed(1) : '0.0';

                                    return `${formatNumber(value)} books (${percentage}%)`;
                                },
                            },
                        },
                        valueLabelPlugin: {
                            enabled: !chartData.empty,
                            color: colors.textColor,
                            fontSize: 11,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                color: colors.textColor,
                                precision: 0,
                            },
                            grid: {
                                color: colors.gridColor,
                                drawBorder: false,
                            },
                            border: {
                                display: false,
                            },
                        },
                        y: {
                            ticks: {
                                color: colors.textColor,
                                font: { size: 12, weight: '600' },
                                callback(value, index) {
                                    return truncateLabel(chartData.labels[index] || '');
                                },
                            },
                            grid: {
                                display: false,
                            },
                            border: {
                                display: false,
                            },
                        },
                    },
                },
            });
        }

        function createConditionChart() {
            const colors = getThemeColors();
            const options = baseChartOptions(colors);
            const chartData = buildSingleSeries(reportCharts.inventory.condition, 'No condition data');
            const conditionColors = chartData.empty
                ? ['#cbd5e1']
                : chartData.labels.map((label) => CONDITION_COLOR_MAP[label] || '#64748b');

            createOrReplaceChart('conditionChart', {
                type: 'bar',
                plugins: [valueLabelPlugin],
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Books',
                        data: chartData.data,
                        backgroundColor: conditionColors,
                        borderColor: conditionColors,
                        borderWidth: 0,
                        borderRadius: 14,
                        borderSkipped: false,
                        maxBarThickness: 68,
                        categoryPercentage: 0.58,
                        barPercentage: 0.88,
                    }],
                },
                options: {
                    ...options,
                    layout: {
                        padding: {
                            top: 22,
                            right: 12,
                            bottom: 8,
                            left: 8,
                        },
                    },
                    plugins: {
                        ...options.plugins,
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            ...options.plugins.tooltip,
                            callbacks: {
                                title(context) {
                                    return context[0]?.label || '';
                                },
                                label(context) {
                                    if (chartData.empty) {
                                        return 'No condition data';
                                    }

                                    return `${formatNumber(context.raw ?? 0)} books`;
                                },
                            },
                        },
                        valueLabelPlugin: {
                            enabled: !chartData.empty,
                            color: colors.textColor,
                            fontSize: 11,
                        },
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: colors.textColor,
                                font: { size: 12, weight: '600' },
                            },
                            grid: {
                                display: false,
                            },
                            border: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: colors.textColor,
                                precision: 0,
                            },
                            grid: {
                                color: colors.gridColor,
                                drawBorder: false,
                            },
                            border: {
                                display: false,
                            },
                        },
                    },
                },
            });
        }

        function createMonthlyChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.transactions.monthly_circulation;

            createOrReplaceChart('monthlyChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Books Issued',
                            data: chartData.issues,
                            borderColor: '#60a5fa',
                            backgroundColor: 'rgba(96, 165, 250, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                        {
                            label: 'Books Returned',
                            data: chartData.returns,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createBorrowedChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.transactions.borrowed_books, 'No book issues');

            createOrReplaceChart('borrowedChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Times Borrowed',
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#8b5cf6', '#6366f1', '#3b82f6', '#0ea5e9', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#f97316'],
                        borderColor: colors.axisBorder,
                        borderWidth: 1,
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createDailyChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.transactions.activity;

            createOrReplaceChart('dailyChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Issues',
                            data: chartData.issues,
                            borderColor: '#60a5fa',
                            backgroundColor: 'rgba(96, 165, 250, 0.15)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                        {
                            label: 'Returns',
                            data: chartData.returns,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                        },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createFineCollectionChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.fines.collection_overview;

            createOrReplaceChart('fineCollectionChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Fines Generated', data: chartData.generated, backgroundColor: '#f97316', borderColor: colors.axisBorder, borderWidth: 1, borderRadius: 6 },
                        { label: 'Fines Collected', data: chartData.collected, backgroundColor: '#10b981', borderColor: colors.axisBorder, borderWidth: 1, borderRadius: 6 },
                        { label: 'Pending Collection', data: chartData.pending, backgroundColor: '#fbbf24', borderColor: colors.axisBorder, borderWidth: 1, borderRadius: 6 },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createEfficiencyChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.fines.efficiency;

            createOrReplaceChart('efficiencyChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Generated', data: chartData.generated, borderColor: '#f97316', backgroundColor: 'rgba(249, 115, 22, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                        { label: 'Collected', data: chartData.collected, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createUsersByRoleChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.users.users_by_role, 'No user data');
            const totalUsers = chartData.empty
                ? 0
                : chartData.data.reduce((sum, value) => sum + Number(value || 0), 0);

            createOrReplaceChart('usersByRoleChart', {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#3b82f6', '#10b981', '#f59e0b'],
                        borderColor: colors.surfaceBorder,
                        borderWidth: 2,
                        hoverOffset: 8,
                        spacing: 2,
                        cutout: '58%',
                    }],
                },
                options: buildDoughnutChartOptions(colors, {
                    legendPosition: 'top',
                    total: totalUsers,
                    valueLabel: 'users',
                    emptyMessage: 'No user data',
                }),
            });
        }

        function createUserActivityChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.users.activity_by_role;

            createOrReplaceChart('userActivityChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Admin Activity', data: chartData.admin, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                        { label: 'Staff Activity', data: chartData.staff, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                        { label: 'Student Activity', data: chartData.student, borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.1)', fill: true, tension: 0.35, borderWidth: 2, pointRadius: pointRadius(chartData.labels.length) },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function createOverdueDistributionChart() {
            const colors = getThemeColors();
            const chartData = buildSingleSeries(reportCharts.overdue.distribution, 'No overdue data');

            createOrReplaceChart('overdueDistributionChart', {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Number of Books',
                        data: chartData.data,
                        backgroundColor: chartData.empty ? ['#cbd5e1'] : ['#fbbf24', '#f59e0b', '#f97316', '#ef4444', '#dc2626'],
                        borderColor: colors.axisBorder,
                        borderWidth: 1,
                        borderRadius: 6,
                    }],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                        y: { ticks: { color: colors.textColor }, grid: { display: false } },
                    },
                },
            });
        }

        function createOverdueTrendChart() {
            const colors = getThemeColors();
            const chartData = reportCharts.overdue.trend;

            createOrReplaceChart('overdueeTrendChart', {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Total Overdue',
                            data: chartData.total,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: pointRadius(chartData.labels.length),
                            pointBackgroundColor: '#f59e0b',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                        },
                        {
                            label: 'Critical (30+ Days)',
                            data: chartData.critical,
                            borderColor: '#dc2626',
                            backgroundColor: 'rgba(220, 38, 38, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            borderDash: [6, 4],
                            pointRadius: pointRadius(chartData.labels.length),
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#dc2626',
                            pointBorderWidth: 2,
                        },
                    ],
                },
                options: {
                    ...baseChartOptions(colors),
                    scales: {
                        x: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor, drawBorder: false } },
                        y: { ticks: { color: colors.textColor }, grid: { color: colors.gridColor } },
                    },
                },
            });
        }

        function renderCharts() {
            createCategoryChart();
            createConditionChart();
            createMonthlyChart();
            createBorrowedChart();
            createDailyChart();
            createFineCollectionChart();
            createEfficiencyChart();
            createUsersByRoleChart();
            createUserActivityChart();
            createOverdueDistributionChart();
            createOverdueTrendChart();
        }

        function updateReportVisibility() {
            return;
        }

        function updateCustomRangeVisibility() {
            const showCustomRange = timePeriodSelect.value === 'custom';

            customRangeGroup.classList.toggle('is-hidden', !showCustomRange);
            applyFiltersBtn.classList.toggle('is-hidden', !showCustomRange);
        }

        function getRealTableRows(tbody) {
            return Array.from(tbody.querySelectorAll('tr')).filter((row) => {
                const cells = row.querySelectorAll('td');
                return !(cells.length === 1 && cells[0].hasAttribute('colspan'));
            });
        }

        function createPaginationControls(card, tableId) {
            const controls = document.createElement('div');
            controls.className = 'table-pagination';
            controls.innerHTML = `
                <div class="table-pagination-select">
                    <span>Show entries</span>
                    <select data-role="entries">
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="table-pagination-info" data-role="summary"></div>
                <div class="table-pagination-actions">
                    <button type="button" class="table-pagination-btn" data-role="prev">Previous</button>
                    <div class="table-pagination-page" data-role="page"></div>
                    <button type="button" class="table-pagination-btn" data-role="next">Next</button>
                </div>
            `;

            const tableWrapper = card.querySelector('.table-scroll-area');
            if (tableWrapper) {
                card.insertBefore(controls, tableWrapper);
            } else {
                card.appendChild(controls);
            }

            const paginator = tablePaginators.get(tableId);
            const entriesSelect = controls.querySelector('[data-role="entries"]');
            const prevBtn = controls.querySelector('[data-role="prev"]');
            const nextBtn = controls.querySelector('[data-role="next"]');

            entriesSelect.addEventListener('change', () => {
                paginator.pageSize = Number(entriesSelect.value);
                paginator.currentPage = 1;
                renderPaginatedTable(tableId);
            });

            prevBtn.addEventListener('click', () => {
                if (paginator.currentPage > 1) {
                    paginator.currentPage -= 1;
                    renderPaginatedTable(tableId);
                }
            });

            nextBtn.addEventListener('click', () => {
                const totalPages = Math.max(1, Math.ceil(paginator.rows.length / paginator.pageSize));
                if (paginator.currentPage < totalPages) {
                    paginator.currentPage += 1;
                    renderPaginatedTable(tableId);
                }
            });

            paginator.controls = {
                container: controls,
                entriesSelect,
                summary: controls.querySelector('[data-role="summary"]'),
                page: controls.querySelector('[data-role="page"]'),
                prevBtn,
                nextBtn,
            };
        }

        function renderPaginatedTable(tableId) {
            const paginator = tablePaginators.get(tableId);
            if (!paginator) {
                return;
            }

            const { rows, tbody, controls } = paginator;
            const totalRows = rows.length;
            const totalPages = Math.max(1, Math.ceil(totalRows / paginator.pageSize));
            paginator.currentPage = Math.min(paginator.currentPage, totalPages);

            const startIndex = totalRows === 0 ? 0 : (paginator.currentPage - 1) * paginator.pageSize;
            const endIndex = Math.min(startIndex + paginator.pageSize, totalRows);

            rows.forEach((row, index) => {
                row.style.display = index >= startIndex && index < endIndex ? '' : 'none';
            });

            if (!controls) {
                return;
            }

            controls.entriesSelect.value = String(paginator.pageSize);
            controls.prevBtn.disabled = paginator.currentPage <= 1 || totalRows === 0;
            controls.nextBtn.disabled = paginator.currentPage >= totalPages || totalRows === 0;
            controls.page.textContent = totalRows === 0 ? 'Page 0 of 0' : `Page ${paginator.currentPage} of ${totalPages}`;

            if (totalRows === 0) {
                controls.summary.textContent = 'No entries available';
                return;
            }

            controls.summary.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalRows} entries`;
        }

        function initializePaginatedTables() {
            tablePaginators.clear();
            document.querySelectorAll('.report-table').forEach((table, index) => {
                const tbody = table.querySelector('tbody');
                if (!tbody) {
                    return;
                }

                const tableId = table.dataset.paginationId || `report-table-${index}`;
                table.dataset.paginationId = tableId;
                const rows = getRealTableRows(tbody);
                const noDataRow = rows.length === 0 ? tbody.querySelector('tr') : null;

                tablePaginators.set(tableId, {
                    table,
                    tbody,
                    rows,
                    noDataRow,
                    pageSize: 10,
                    currentPage: 1,
                    controls: null,
                });

                createPaginationControls(table.closest('.table-card'), tableId);
                renderPaginatedTable(tableId);
            });
        }

        function bindDynamicContentEvents() {
            syncDynamicContentElements();

            if (categoryChartLimitSelect) {
                categoryChartLimitSelect.addEventListener('change', () => {
                    createCategoryChart();
                });
            }

            categoryViewButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    updateCategoryViewButtons(button.dataset.categoryView || 'bar');
                    createCategoryChart();
                });
            });

            exportChartButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    exportChartImage(
                        button.dataset.exportChart || '',
                        button.dataset.exportFilename || 'report-chart'
                    );
                });
            });

            reportsContent?.querySelector('[data-report-retry]')?.addEventListener('click', () => {
                void loadReportData(getCurrentFilters(), { updateHistory: false });
            });
        }

        function applyReportPayload(payload, options = {}) {
            if (!reportsContent || !payload) {
                return;
            }

            const responseFilters = payload.filters || getCurrentFilters();

            syncFilterControls(responseFilters);
            destroyCharts();
            reportCharts = getEmptyReportCharts();
            reportCharts[payload.reportType || responseFilters.reportType || 'inventory'] = payload.charts || {};
            reportsContent.innerHTML = payload.content || buildErrorStateMarkup('No report content was returned by the server.');
            reportsContent.setAttribute('aria-busy', 'false');
            reportState.contentLoaded = true;
            syncDynamicContentElements();
            initializePaginatedTables();
            updateCategoryViewButtons(getActiveCategoryView());
            bindDynamicContentEvents();
            renderCharts();
            reportExportWorkflow?.clearCache?.();

            if (options.updateHistory !== false) {
                updateBrowserHistory(responseFilters, { replace: options.replaceHistory === true });
            }
        }

        async function loadReportData(filters = getCurrentFilters(), options = {}) {
            if (!window.AdminReportApi?.fetchReportData || !adminReportsPageConfig.endpoint) {
                renderErrorState('The report service is not available right now.');
                return;
            }

            const requestFilters = { ...filters };
            const requestKey = buildReportRequestKey(requestFilters);
            const useCache = options.useCache !== false;
            closeReportExportModal('reportExportModal');
            closeReportPdfPreviewModal();
            reportExportWorkflow?.clearCache?.();

            if (useCache && reportState.cache.has(requestKey)) {
                applyReportPayload(reportState.cache.get(requestKey), options);
                return;
            }

            if (reportState.requestController) {
                reportState.requestController.abort();
            }

            reportState.requestController = new AbortController();
            reportState.activeRequestKey = requestKey;
            setFiltersBusy(true);
            renderLoadingState();

            try {
                const payload = await window.AdminReportApi.fetchReportData(
                    adminReportsPageConfig.endpoint,
                    requestFilters,
                    { signal: reportState.requestController.signal }
                );

                if (reportState.activeRequestKey !== requestKey) {
                    return;
                }

                reportState.cache.set(requestKey, payload);
                applyReportPayload(payload, options);

                if (options.toast === true) {
                    showReportToast('success', 'Report Updated', 'The selected report has been refreshed.');
                }
            } catch (error) {
                if (error?.name === 'AbortError') {
                    return;
                }

                console.error('[Reports] Failed to load report data:', error);
                renderErrorState(error?.message || 'Something went wrong while loading the report.');
                showReportToast('error', 'Refresh Failed', error?.message || 'Something went wrong while loading the report.');
            } finally {
                if (reportState.activeRequestKey === requestKey) {
                    reportState.activeRequestKey = '';
                    reportState.requestController = null;
                    setFiltersBusy(false);
                }
            }
        }

        function parseFiltersFromLocation() {
            const params = new URLSearchParams(window.location.search);

            return {
                reportType: params.get('report_type') || reportState.filters.reportType || 'inventory',
                timePeriod: params.get('time_period') || reportState.filters.timePeriod || '30days',
                startDateInput: params.get('start_date') || '',
                endDateInput: params.get('end_date') || '',
            };
        }

        function debounce(callback, delay = 180) {
            let timeoutId = null;

            return (...args) => {
                window.clearTimeout(timeoutId);
                timeoutId = window.setTimeout(() => callback(...args), delay);
            };
        }

        const debouncedFilterReload = debounce(() => {
            void loadReportData(getCurrentFilters(), {
                updateHistory: true,
                toast: reportState.contentLoaded,
            });
        }, 180);

        function exportPDF() {
            if (!reportExportWorkflow) {
                initReportExportWorkflow();
            }

            reportExportWorkflow?.open();
        }

        document.addEventListener('DOMContentLoaded', () => {
            syncFilterControls(reportState.filters);
            renderLoadingState();
            initReportExportWorkflow();

            document.addEventListener('click', (event) => {
                const closeButton = event.target.closest('[data-modal-close="reportExportModal"]');
                if (closeButton) {
                    closeReportExportModal('reportExportModal');
                }
            });

            reportExportModal?.addEventListener('click', (event) => {
                if (event.target === reportExportModal) {
                    closeReportExportModal('reportExportModal');
                }
            });

            filterForm?.addEventListener('submit', (event) => {
                event.preventDefault();
                void loadReportData(getCurrentFilters(), {
                    updateHistory: true,
                    toast: reportState.contentLoaded,
                });
            });

            reportTypeSelect?.addEventListener('change', () => {
                debouncedFilterReload();
            });

            timePeriodSelect?.addEventListener('change', () => {
                updateCustomRangeVisibility();

                if (timePeriodSelect.value !== 'custom') {
                    debouncedFilterReload();
                }
            });

            void loadReportData(parseFiltersFromLocation(), {
                updateHistory: false,
                replaceHistory: true,
                useCache: true,
            });
        });

        window.addEventListener('popstate', () => {
            const nextFilters = parseFiltersFromLocation();
            syncFilterControls(nextFilters);

            void loadReportData(nextFilters, {
                updateHistory: false,
                replaceHistory: true,
                useCache: true,
            });
        });

        const themeObserver = new MutationObserver((mutations) => {
            if (mutations.some((mutation) => mutation.attributeName === 'class')) {
                renderCharts();
            }
        });

        themeObserver.observe(document.body, { attributes: true });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && reportExportModal?.classList.contains('is-open')) {
                closeReportExportModal('reportExportModal');
            }
        });
    </script>
@endsection
