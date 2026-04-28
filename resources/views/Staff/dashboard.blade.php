@extends('Staff.layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 640px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .stat-card {
            position: relative;
            border-radius: 0.75rem;
            padding: 0.9rem 1rem;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-height: 102px;
        }

        body.light-theme .stat-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        body.dark-theme .stat-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        body.dark-theme .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .stat-card-header {
            display: block;
            padding-right: 3.25rem;
        }

        .stat-card-body {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-value {
            font-size: 1.85rem;
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .stat-label {
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        body.light-theme .stat-label {
            color: #475569;
        }

        body.dark-theme .stat-label {
            color: #cbd5e1;
        }

        .stat-helper {
            font-size: 0.72rem;
            line-height: 1.35;
            margin-top: 0;
        }

        body.light-theme .stat-helper {
            color: #64748b;
        }

        body.dark-theme .stat-helper {
            color: #94a3b8;
        }

        .status-blue {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.dark-theme .status-blue {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .status-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .status-green {
            background-color: #14532d;
            color: #4ade80;
        }

        .status-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .status-red {
            background-color: #7f1d1d;
            color: #f87171;
        }

        .status-yellow {
            background-color: #fef3c7;
            color: #d97706;
        }

        body.dark-theme .status-yellow {
            background-color: #78350f;
            color: #fbbf24;
        }

        .status-purple {
            background-color: #f3e8ff;
            color: #7c3aed;
        }

        body.dark-theme .status-purple {
            background-color: #4c1d95;
            color: #a78bfa;
        }

        .section-card,
        .table-card {
            border-radius: 0.75rem;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        body.light-theme .section-card,
        body.light-theme .table-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .section-card,
        body.dark-theme .table-card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        .section-header,
        .table-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid;
        }

        body.light-theme .section-header,
        body.light-theme .table-card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .section-header,
        body.dark-theme .table-card-header {
            border-color: #334155;
        }

        .section-title,
        .table-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
        }

        .section-heading {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .section-description {
            font-size: 0.75rem;
            margin: 0;
            color: #64748b;
        }

        body.dark-theme .section-title,
        body.dark-theme .table-card-title {
            color: #e2e8f0;
        }

        body.dark-theme .section-description {
            color: #94a3b8;
        }

        .section-count {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            white-space: nowrap;
        }

        .count-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .count-red {
            background-color: #7f1d1d;
            color: #f87171;
        }

        .count-blue {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        body.dark-theme .count-blue {
            background-color: #1e3a8a;
            color: #93c5fd;
        }

        .charts-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 1024px) {
            .charts-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        .tables-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 1024px) {
            .tables-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .dashboard-bottom-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 1024px) {
            .dashboard-bottom-row {
                grid-template-columns: minmax(320px, 0.95fr) minmax(0, 1.45fr);
                align-items: stretch;
            }

            .dashboard-bottom-row > .section-card {
                min-height: 520px;
                max-height: 520px;
            }
        }

        .quick-actions-card {
            min-height: 100%;
        }

        .quick-actions-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-content: start;
        }

        @media (min-width: 640px) {
            .quick-actions-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .quick-actions-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .quick-action-btn {
            position: relative;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: start;
            gap: 0.9rem;
            min-height: 112px;
            padding: 1rem 1rem 1rem 1.05rem;
            border-radius: 1rem;
            border: 1px solid;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
            overflow: hidden;
            cursor: pointer;
            isolation: isolate;
        }

        .quick-action-btn::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--quick-accent, #2563eb);
            z-index: 0;
        }

        .quick-action-btn::after {
            content: none;
        }

        body.light-theme .quick-action-btn {
            background-color: #ffffff;
            border-color: var(--quick-border, rgba(148, 163, 184, 0.28));
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        }

        body.dark-theme .quick-action-btn {
            background-color: #1e293b;
            border-color: var(--quick-border, rgba(71, 85, 105, 0.65));
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            border-color: var(--quick-accent, #2563eb);
        }

        body.dark-theme .quick-action-btn:hover {
            box-shadow: 0 14px 28px rgba(2, 6, 23, 0.28);
        }

        .quick-action-btn:active {
            transform: translateY(0);
        }

        .quick-action-btn:focus-visible {
            outline: none;
            border-color: var(--quick-accent, #2563eb);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
        }

        .quick-action-btn > * {
            position: relative;
            z-index: 1;
        }

        .quick-action-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--quick-accent, #2563eb);
            flex-shrink: 0;
            border: 1px solid var(--quick-border, rgba(148, 163, 184, 0.28));
        }

        body.light-theme .quick-action-icon {
            background-color: var(--quick-soft, rgba(37, 99, 235, 0.12));
        }

        body.dark-theme .quick-action-icon {
            background-color: rgba(15, 23, 42, 0.68);
        }

        .quick-action-content {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 0.5rem;
            padding-top: 0.05rem;
        }

        .quick-action-top {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 0.45rem;
        }

        .quick-action-title {
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        body.light-theme .quick-action-title {
            color: #0f172a;
        }

        body.dark-theme .quick-action-title {
            color: #e2e8f0;
        }

        .quick-action-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.26rem 0.65rem;
            border-radius: 9999px;
            font-size: 0.71rem;
            font-weight: 700;
            white-space: nowrap;
            color: var(--quick-accent, #2563eb);
            border: 1px solid transparent;
            background-color: var(--quick-soft, rgba(37, 99, 235, 0.12));
        }

        .quick-action-meta {
            font-size: 0.79rem;
            line-height: 1.42;
            max-width: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        body.light-theme .quick-action-meta {
            color: #64748b;
        }

        body.dark-theme .quick-action-meta {
            color: #94a3b8;
        }

        .quick-action-arrow {
            width: 2rem;
            height: 2rem;
            padding: 0;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--quick-accent, #2563eb);
            flex-shrink: 0;
            margin-left: auto;
            align-self: start;
            border: 1px solid var(--quick-border, rgba(148, 163, 184, 0.28));
            background-color: transparent;
            transition: transform 0.2s ease, border-color 0.2s ease, background-color 0.2s ease;
        }

        body.light-theme .quick-action-arrow {
            background-color: #ffffff;
        }

        body.dark-theme .quick-action-arrow {
            background-color: #0f172a;
        }

        .quick-action-btn:hover .quick-action-arrow {
            transform: translateX(2px);
            border-color: var(--quick-accent, #2563eb);
            background-color: var(--quick-soft, rgba(37, 99, 235, 0.12));
        }

        .quick-action-arrow svg {
            width: 0.9rem;
            height: 0.9rem;
        }

        .chart-container {
            position: relative;
            height: 280px;
            padding: 0.25rem 0;
        }

        .circulation-panel,
        .activity-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .circulation-insights,
        .activity-insights {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .circulation-insights,
            .activity-insights {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1280px) {
            .circulation-insights,
            .activity-insights {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .circulation-insight,
        .activity-insight {
            border-radius: 0.85rem;
            border: 1px solid;
            border-top: 3px solid var(--accent-color, #cbd5e1);
            padding: 0.9rem 1rem;
        }

        body.light-theme .circulation-insight,
        body.light-theme .activity-insight {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), #ffffff);
            border-color: #e5e7eb;
        }

        body.dark-theme .circulation-insight,
        body.dark-theme .activity-insight {
            background: linear-gradient(180deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.96));
            border-color: #334155;
        }

        .circulation-insight-top,
        .circulation-insight-label-wrap,
        .activity-insight-top,
        .activity-insight-label-wrap {
            display: flex;
            align-items: center;
        }

        .circulation-insight-top,
        .activity-insight-top {
            justify-content: space-between;
            gap: 0.75rem;
        }

        .circulation-insight-label-wrap,
        .activity-insight-label-wrap {
            gap: 0.5rem;
            min-width: 0;
        }

        .circulation-dot,
        .activity-dot {
            width: 0.65rem;
            height: 0.65rem;
            border-radius: 9999px;
            background-color: var(--accent-color, #cbd5e1);
            flex-shrink: 0;
        }

        .circulation-insight-label,
        .activity-insight-label,
        .circulation-insight-share,
        .activity-insight-badge,
        .circulation-insight-meta,
        .activity-insight-meta {
            font-size: 0.75rem;
        }

        .circulation-insight-label,
        .activity-insight-label {
            font-weight: 600;
            color: #475569;
        }

        body.dark-theme .circulation-insight-label,
        body.dark-theme .activity-insight-label {
            color: #cbd5e1;
        }

        .circulation-insight-share,
        .activity-insight-badge {
            font-weight: 700;
            color: var(--accent-color, #475569);
        }

        .circulation-insight-value,
        .activity-insight-value {
            margin-top: 0.45rem;
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
            color: #0f172a;
        }

        body.dark-theme .circulation-insight-value,
        body.dark-theme .activity-insight-value {
            color: #f8fafc;
        }

        .circulation-insight-meta,
        .activity-insight-meta {
            margin-top: 0.35rem;
            color: #64748b;
        }

        body.dark-theme .circulation-insight-meta,
        body.dark-theme .activity-insight-meta {
            color: #94a3b8;
        }

        .table-card {
            max-height: 380px;
        }

        .table-card-heading {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .table-card-link {
            font-size: 0.75rem;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .table-card-link:hover {
            text-decoration: underline;
        }

        .list-show-more {
            display: flex;
            justify-content: center;
            padding-top: 0.85rem;
        }

        .show-more-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            width: 100%;
            padding: 0.7rem 0.95rem;
            border-radius: 0.7rem;
            border: 1px solid;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        body.light-theme .show-more-btn {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #2563eb;
        }

        body.dark-theme .show-more-btn {
            background-color: #0f172a;
            border-color: #334155;
            color: #93c5fd;
        }

        .show-more-btn:hover {
            transform: translateY(-1px);
        }

        .show-more-btn:disabled {
            cursor: wait;
            opacity: 0.72;
            transform: none;
        }

        body.light-theme .show-more-btn:hover {
            background-color: #eff6ff;
            border-color: #bfdbfe;
        }

        body.dark-theme .show-more-btn:hover {
            background-color: #172554;
            border-color: #1d4ed8;
        }

        .show-more-btn svg {
            flex-shrink: 0;
        }

        .table-card-count {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            background-color: #dbeafe;
            color: #1e40af;
            white-space: nowrap;
        }

        body.dark-theme .table-card-count {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .table-list {
            flex: 1;
            overflow-y: auto;
        }

        .table-item {
            padding: 0.875rem 0;
            border-bottom: 1px solid;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        body.light-theme .table-item {
            border-color: #f1f5f9;
        }

        body.dark-theme .table-item {
            border-color: #334155;
        }

        .table-item:last-child {
            border-bottom: none;
        }

        .table-item-top {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: flex-start;
        }

        .table-item-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: #0f172a;
        }

        body.dark-theme .table-item-title {
            color: #e2e8f0;
        }

        .table-item-subtitle {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .table-item-subtitle {
            color: #94a3b8;
        }

        .table-item-meta {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        body.dark-theme .table-item-meta {
            color: #64748b;
        }

        .table-request-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.25rem;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            text-align: center;
        }

        .table-empty-state {
            min-height: 180px;
        }

        .empty-icon {
            width: 3rem;
            height: 3rem;
            margin-bottom: 0.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.light-theme .empty-icon {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .empty-icon {
            background-color: #14532d;
            color: #4ade80;
        }

        .empty-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #0f172a;
        }

        body.dark-theme .empty-title {
            color: #e2e8f0;
        }

        .empty-subtitle {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .empty-subtitle {
            color: #94a3b8;
        }

        .overdue-list {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding-right: 4px;
            padding-bottom: 0.35rem;
        }

        .overdue-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            transition: background-color 0.2s ease;
        }

        body.light-theme .overdue-item {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
        }

        body.dark-theme .overdue-item {
            background-color: #7f1d1d;
            border: 1px solid #991b1b;
        }

        .overdue-item:last-child {
            margin-bottom: 0;
        }

        .overdue-info {
            flex: 1;
            min-width: 0;
        }

        .overdue-book {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.125rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body.dark-theme .overdue-book {
            color: #e2e8f0;
        }

        .overdue-student {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .overdue-student {
            color: #cbd5e1;
        }

        .overdue-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.35rem;
            flex-shrink: 0;
        }

        .overdue-date {
            font-size: 0.75rem;
            font-weight: 500;
            color: #dc2626;
            line-height: 1.25;
        }

        body.dark-theme .overdue-date {
            color: #f87171;
        }

        .overdue-days {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            padding: 0.3rem 0.7rem;
            border-radius: 9999px;
            background-color: #dc2626;
            color: #ffffff;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
        }

        body.dark-theme .overdue-days {
            background-color: #ef4444;
        }

        @media (max-width: 639px) {
            .overdue-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .overdue-details {
                width: 100%;
                align-items: flex-start;
            }
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            white-space: nowrap;
        }

        body.light-theme .action-btn {
            background-color: #2563eb;
            color: #ffffff;
        }

        body.dark-theme .action-btn {
            background-color: #1e40af;
            color: #ffffff;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        .action-btn.btn-accept {
            background-color: #16a34a;
            color: #ffffff;
        }

        .action-btn.btn-reject {
            background-color: #f59e0b;
            color: #000000;
        }

        body.dark-theme .action-btn.btn-reject {
            background-color: #b45309;
            color: #ffffff;
        }

        .action-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            padding: 0.5rem 0.75rem;
            color: #ffffff;
            border-radius: 0.375rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
            font-weight: 600;
            opacity: 0.95;
        }

        .toast.success {
            background: #16a34a;
        }

        .toast.error {
            background: #ef4444;
        }

        /* ================== */
        /* REQUEST MODAL      */
        /* ================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-dialog {
            background-color: #ffffff;
            border-radius: 1rem;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        body.dark-theme .modal-dialog {
            background-color: #1e293b;
        }

        .modal-overlay.active .modal-dialog {
            transform: scale(1) translateY(0);
        }

        /* Modal Header */
        .modal-header {
            padding: 1rem 1.25rem 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .modal-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .modal-icon.approve {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .modal-icon.approve {
            background-color: #14532d;
            color: #4ade80;
        }

        .modal-icon.reject {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .modal-icon.reject {
            background-color: #7f1d1d;
            color: #f87171;
        }

        .modal-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .modal-title-group {
            flex: 1;
        }

        .modal-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.2rem;
        }

        body.dark-theme .modal-title {
            color: #f1f5f9;
        }

        .modal-subtitle {
            font-size: 0.8rem;
            line-height: 1.45;
            color: #64748b;
        }

        body.dark-theme .modal-subtitle {
            color: #94a3b8;
        }

        .modal-close {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            background-color: transparent;
            color: #64748b;
        }

        .modal-close:hover {
            background-color: #f1f5f9;
        }

        body.dark-theme .modal-close:hover {
            background-color: #334155;
        }

        /* Modal Body */
        .modal-body {
            padding: 0 1.25rem 1rem;
        }

        .modal-message {
            font-size: 0.9rem;
            line-height: 1.55;
            color: #475569;
            margin: 0;
        }

        body.dark-theme .modal-message {
            color: #cbd5e1;
        }

        .request-summary {
            background-color: #f8fafc;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        body.dark-theme .request-summary {
            background-color: #0f172a;
        }

        .request-summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
        }

        .request-summary-item:not(:last-child) {
            border-bottom: 1px solid #e5e7eb;
        }

        body.dark-theme .request-summary-item:not(:last-child) {
            border-bottom-color: #334155;
        }

        .request-summary-item:last-child {
            padding-bottom: 0;
        }

        .request-summary-item:first-child {
            padding-top: 0;
        }

        .request-summary-label {
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme .request-summary-label {
            color: #94a3b8;
        }

        .request-summary-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
        }

        body.dark-theme .request-summary-value {
            color: #f1f5f9;
        }

        .request-summary-value.book-title {
            max-width: 200px;
            text-align: right;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Warning Box */
        .modal-warning {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .modal-warning.reject {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }

        body.dark-theme .modal-warning.reject {
            background-color: #450a0a;
            border-color: #7f1d1d;
        }

        .modal-warning-icon {
            flex-shrink: 0;
            color: #dc2626;
        }

        body.dark-theme .modal-warning-icon {
            color: #f87171;
        }

        .modal-warning-text {
            font-size: 0.875rem;
            color: #991b1b;
            line-height: 1.5;
        }

        body.dark-theme .modal-warning-text {
            color: #fca5a5;
        }

        /* Form Group */
        .modal-form-group {
            margin-bottom: 1rem;
        }

        .modal-form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        body.dark-theme .modal-form-label {
            color: #f1f5f9;
        }

        .modal-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            resize: vertical;
            min-height: 80px;
            font-family: inherit;
            background-color: #ffffff;
            color: #0f172a;
            transition: border-color 0.2s ease;
        }

        body.dark-theme .modal-textarea {
            background-color: #0f172a;
            border-color: #334155;
            color: #f1f5f9;
        }

        .modal-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modal-textarea::placeholder {
            color: #94a3b8;
        }

        .modal-hint {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.375rem;
        }

        body.dark-theme .modal-hint {
            color: #94a3b8;
        }

        /* Modal Footer */
        .modal-footer {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background-color: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }

        body.dark-theme .modal-footer {
            background-color: #0f172a;
            border-top-color: #334155;
        }

        .modal-btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-btn-cancel {
            background-color: #f1f5f9;
            color: #475569;
        }

        body.dark-theme .modal-btn-cancel {
            background-color: #334155;
            color: #cbd5e1;
        }

        .modal-btn-cancel:hover {
            background-color: #e2e8f0;
        }

        body.dark-theme .modal-btn-cancel:hover {
            background-color: #475569;
        }

        .modal-btn-approve {
            background-color: #16a34a;
            color: #ffffff;
        }

        .modal-btn-approve:hover {
            background-color: #15803d;
        }

        .modal-btn-reject {
            background-color: #dc2626;
            color: #ffffff;
        }

        .modal-btn-reject:hover {
            background-color: #b91c1c;
        }

        /* Loading State */
        .modal-btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .modal-btn.loading svg {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Approve confirmation styling */
        .modal-warning.approve {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        body.dark-theme .modal-warning.approve {
            background-color: #14532d;
            border-color: #166534;
        }

        .modal-warning.approve .modal-warning-icon {
            color: #16a34a;
        }

        body.dark-theme .modal-warning.approve .modal-warning-icon {
            color: #4ade80;
        }

        .modal-warning.approve .modal-warning-text {
            color: #166534;
        }

        body.dark-theme .modal-warning.approve .modal-warning-text {
            color: #86efac;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard">
        <div class="dashboard-grid">
            <div class="stat-card status-blue">
                <div class="stat-card-header">
                    <div class="stat-label">Currently Issued</div>
                    <div class="stat-icon status-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" />
                        </svg>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-value">{{ $currentlyIssued ?? 0 }}</div>
                    <div class="stat-helper">{{ $overdueCount ?? 0 }} overdue and {{ $dueToday ?? 0 }} due today</div>
                </div>
            </div>

            <div class="stat-card status-green">
                <div class="stat-card-header">
                    <div class="stat-label">Due Today</div>
                    <div class="stat-icon status-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-value">{{ $dueToday ?? 0 }}</div>
                    <div class="stat-helper">
                        {{ ($dueToday ?? 0) > 0 ? 'Returns expected before the day ends' : 'No returns scheduled for today' }}
                    </div>
                </div>
            </div>

            <div class="stat-card status-red">
                <div class="stat-card-header">
                    <div class="stat-label">Overdue</div>
                    <div class="stat-icon status-red">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-value">{{ $overdueCount ?? 0 }}</div>
                    <div class="stat-helper">
                        {{ ($overdueCount ?? 0) > 0 ? 'Immediate follow-up recommended' : 'Nothing overdue right now' }}
                    </div>
                </div>
            </div>

            <div class="stat-card status-yellow">
                <div class="stat-card-header">
                    <div class="stat-label">Pending Requests</div>
                    <div class="stat-icon status-yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="12" y1="18" x2="12" y2="12" />
                            <line x1="9" y1="15" x2="15" y2="15" />
                        </svg>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-value" data-pending-count-display>{{ $pendingRequestsCount ?? 0 }}</div>
                    <div class="stat-helper">
                        {{ ($pendingRequestsCount ?? 0) > 0 ? 'Awaiting approval or rejection' : 'Request queue is clear' }}
                    </div>
                </div>
            </div>

            <div class="stat-card status-purple">
                <div class="stat-card-header">
                    <div class="stat-label">Pending Fines</div>
                    <div class="stat-icon status-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M6 4h12" />
                            <path d="M6 8h12" />
                            <path d="M9 12h3a4 4 0 0 0 0-8" />
                            <path d="m8 12 7 8" />
                        </svg>
                    </div>
                </div>
                <div class="stat-card-body">
                    <div class="stat-value">₹{{ number_format($pendingFinesAmount ?? 0) }}</div>
                    <div class="stat-helper">
                        {{ ($pendingFinesAmount ?? 0) > 0 ? 'Outstanding amount awaiting collection' : 'No unpaid fines at the moment' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="charts-row">
            <div class="section-card">
                @php
                    $circulationTotal = (int) ($circulationOverview['total'] ?? 0);
                    $circulationInsights = [
                        [
                            'label' => 'Available',
                            'value' => (int) ($circulationOverview['available'] ?? 0),
                            'meta' => (($circulationOverview['availabilityRate'] ?? 0)) . '% of the current pool',
                            'color' => '#10b981',
                        ],
                        [
                            'label' => 'Issued On Time',
                            'value' => (int) ($circulationOverview['issuedOnTime'] ?? 0),
                            'meta' => 'Borrowed and due after today',
                            'color' => '#3b82f6',
                        ],
                        [
                            'label' => 'Due Today',
                            'value' => (int) ($circulationOverview['dueToday'] ?? 0),
                            'meta' => 'Needs follow-up before closing',
                            'color' => '#f59e0b',
                        ],
                        [
                            'label' => 'Overdue',
                            'value' => (int) ($circulationOverview['overdue'] ?? 0),
                            'meta' => 'Immediate attention needed',
                            'color' => '#ef4444',
                        ],
                    ];
                @endphp
                <div class="section-header">
                    <div class="section-heading">
                        <h2 class="section-title">Book Circulation</h2>
                        <p class="section-description">
                            {{ number_format($circulationOverview['issued'] ?? 0) }} active loans,
                            {{ number_format($circulationOverview['attentionNeeded'] ?? 0) }} need attention today.
                        </p>
                    </div>
                    <span class="section-count count-blue">{{ number_format($circulationTotal) }} tracked</span>
                </div>
                <div class="circulation-panel">
                    <div class="chart-container">
                        <canvas id="circulationChart"></canvas>
                    </div>
                    <div class="circulation-insights">
                        @foreach($circulationInsights as $insight)
                            @php
                                $share = $circulationTotal > 0
                                    ? (int) round(($insight['value'] / $circulationTotal) * 100)
                                    : 0;
                            @endphp
                            <div class="circulation-insight" style="--accent-color: {{ $insight['color'] }};">
                                <div class="circulation-insight-top">
                                    <div class="circulation-insight-label-wrap">
                                        <span class="circulation-dot" aria-hidden="true"></span>
                                        <span class="circulation-insight-label">{{ $insight['label'] }}</span>
                                    </div>
                                    <span class="circulation-insight-share">{{ $share }}%</span>
                                </div>
                                <div class="circulation-insight-value">{{ number_format($insight['value']) }}</div>
                                <div class="circulation-insight-meta">{{ $insight['meta'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-card">
                @php
                    $movementsTotal = (int) ($activityOverview['movementsTotal'] ?? 0);
                    $issuedTotal = (int) ($activityOverview['issuedTotal'] ?? 0);
                    $returnedTotal = (int) ($activityOverview['returnedTotal'] ?? 0);
                    $activityInsights = [
                        [
                            'label' => 'Issued',
                            'badge' => ($movementsTotal > 0 ? (int) round(($issuedTotal / $movementsTotal) * 100) : 0) . '%',
                            'value' => number_format($issuedTotal),
                            'meta' => 'Books issued during the last 7 days',
                            'color' => '#6366f1',
                        ],
                        [
                            'label' => 'Returned',
                            'badge' => ($movementsTotal > 0 ? (int) round(($returnedTotal / $movementsTotal) * 100) : 0) . '%',
                            'value' => number_format($returnedTotal),
                            'meta' => 'Books returned during the last 7 days',
                            'color' => '#f97316',
                        ],
                        [
                            'label' => 'Peak Activity',
                            'badge' => (string) ($activityOverview['peakDay']['label'] ?? '—'),
                            'value' => number_format((int) ($activityOverview['peakDay']['count'] ?? 0)),
                            'meta' => 'Highest combined issues and returns',
                            'color' => '#8b5cf6',
                        ],
                        [
                            'label' => 'Avg / Day',
                            'badge' => '7d',
                            'value' => number_format((float) ($activityOverview['averagePerDay'] ?? 0), 1),
                            'meta' => 'Average daily movement volume',
                            'color' => '#f59e0b',
                        ],
                    ];
                @endphp
                <div class="section-header">
                    <div class="section-heading">
                        <h2 class="section-title">7-Day Activity</h2>
                        <p class="section-description">
                            {{ number_format($issuedTotal) }} issues and {{ number_format($returnedTotal) }} returns logged this week.
                        </p>
                    </div>
                    <span class="section-count count-blue">{{ number_format($movementsTotal) }} movements</span>
                </div>
                <div class="activity-panel">
                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                    <div class="activity-insights">
                        @foreach($activityInsights as $insight)
                            <div class="activity-insight" style="--accent-color: {{ $insight['color'] }};">
                                <div class="activity-insight-top">
                                    <div class="activity-insight-label-wrap">
                                        <span class="activity-dot" aria-hidden="true"></span>
                                        <span class="activity-insight-label">{{ $insight['label'] }}</span>
                                    </div>
                                    <span class="activity-insight-badge">{{ $insight['badge'] }}</span>
                                </div>
                                <div class="activity-insight-value">{{ $insight['value'] }}</div>
                                <div class="activity-insight-meta">{{ $insight['meta'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="tables-row">
            <div class="table-card">
                <div class="table-card-header">
                    <div class="table-card-heading">
                        <h3 class="table-card-title">Pending Requests</h3>
                        <a href="{{ route('staff.book-requests.index') }}" class="table-card-link">View all</a>
                    </div>
                    <span class="table-card-count" data-pending-count-display>{{ $pendingRequestsCount ?? 0 }}</span>
                </div>

                <div class="table-list pending-requests-list js-dashboard-list" data-list-type="pending_requests" data-total-count="{{ (int) ($pendingRequestsCount ?? 0) }}" data-limit="10">
                    @if(isset($pendingRequests) && $pendingRequests->count())
                        @foreach($pendingRequests as $req)
                            <div class="table-item table-request-item" id="request-{{ $req->id }}" data-request-id="{{ $req->id }}" data-list-item="true">
                                <div class="table-item-top">
                                    <div>
                                        <div class="table-item-title">{{ optional($req->book)->title ?? 'Untitled' }}</div>
                                        <div class="table-item-subtitle">{{ data_get($req, 'student.user.name', 'Unknown') }}</div>
                                    </div>
                                    <div class="table-item-meta">{{ optional($req->request_date)->format('M d, Y') }}</div>
                                </div>
                                <div class="table-item-meta">
                                    {{ data_get($req, 'student.student_id') ?: data_get($req, 'student.roll_no', 'No ID') }}
                                </div>
                                <div class="table-request-actions">
                                    <button class="action-btn btn-accept" onclick="processBookRequest({{ $req->id }}, 'approved')">Accept</button>
                                    <button class="action-btn btn-reject" onclick="processBookRequest({{ $req->id }}, 'rejected')">Reject</button>
                                </div>
                            </div>
                        @endforeach
                        @if(($pendingRequestsCount ?? 0) > $pendingRequests->count())
                            <div class="list-show-more" data-show-more-wrapper>
                                <button type="button" class="show-more-btn" data-show-more-button data-list-type="pending_requests">
                                    <span data-show-more-label>Show more</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="empty-state table-empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 8v4l3 3" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                            </div>
                            <h3 class="empty-title">No pending requests</h3>
                            <p class="empty-subtitle">You're all caught up for now.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h3 class="table-card-title">Due Today</h3>
                    <span class="table-card-count">{{ $dueToday ?? 0 }}</span>
                </div>

                <div class="table-list js-dashboard-list" data-list-type="due_today" data-total-count="{{ (int) ($dueToday ?? 0) }}" data-limit="10">
                    @if(isset($dueTodayList) && $dueTodayList->count())
                        @foreach($dueTodayList as $item)
                            <div class="table-item" data-list-item="true">
                                <div class="table-item-top">
                                    <div>
                                        <div class="table-item-title">{{ optional($item->book)->title ?? 'Untitled' }}</div>
                                        <div class="table-item-subtitle">{{ data_get($item, 'student.user.name', 'Unknown') }}</div>
                                    </div>
                                    <div class="table-item-meta">Due today</div>
                                </div>
                                <div class="table-item-meta">Due: {{ optional($item->due_date)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                        @if(($dueToday ?? 0) > $dueTodayList->count())
                            <div class="list-show-more" data-show-more-wrapper>
                                <button type="button" class="show-more-btn" data-show-more-button data-list-type="due_today">
                                    <span data-show-more-label>Show more</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="empty-state table-empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                            </div>
                            <h3 class="empty-title">No books due today</h3>
                            <p class="empty-subtitle">All clear for today.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="table-card">
                <div class="table-card-header">
                    <h3 class="table-card-title">Recent Issues</h3>
                </div>

                <div class="table-list js-dashboard-list" data-list-type="recent_issues" data-total-count="{{ (int) ($recentlyIssuedCount ?? 0) }}" data-limit="10">
                    @if(isset($recentlyIssued) && $recentlyIssued->count())
                        @foreach($recentlyIssued as $item)
                            <div class="table-item" data-list-item="true">
                                <div class="table-item-top">
                                    <div>
                                        <div class="table-item-title">{{ optional($item->book)->title ?? 'Untitled' }}</div>
                                        <div class="table-item-subtitle">{{ data_get($item, 'student.user.name', 'Unknown') }}</div>
                                    </div>
                                    <div class="table-item-meta">{{ optional($item->issue_date)->diffForHumans() ?? 'Recently' }}</div>
                                </div>
                                <div class="table-item-meta">Issued on {{ optional($item->issue_date)->format('M d, Y') }}</div>
                            </div>
                        @endforeach
                        @if(($recentlyIssuedCount ?? 0) > $recentlyIssued->count())
                            <div class="list-show-more" data-show-more-wrapper>
                                <button type="button" class="show-more-btn" data-show-more-button data-list-type="recent_issues">
                                    <span data-show-more-label>Show more</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="empty-state table-empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 6v6l4 2" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                            </div>
                            <h3 class="empty-title">No recent issues</h3>
                            <p class="empty-subtitle">No books have been issued recently.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="dashboard-bottom-row">
            <div class="section-card quick-actions-card">
                <div class="section-header">
                    <div class="section-heading">
                        <h2 class="section-title">Quick Actions</h2>
                        <p class="section-description">Open the staff tools you are most likely to use next.</p>
                    </div>
                    <span class="section-count count-blue">4 shortcuts</span>
                </div>

                <div class="quick-actions-grid">
                    <a href="{{ route('staff.issue-book.index') }}" class="quick-action-btn" style="--quick-accent: #2563eb; --quick-accent-strong: #1d4ed8; --quick-soft: rgba(37, 99, 235, 0.16); --quick-border: rgba(37, 99, 235, 0.2);">
                        <span class="quick-action-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14" />
                                <path d="M5 12h14" />
                            </svg>
                        </span>
                        <span class="quick-action-content">
                            <span class="quick-action-top">
                                <span class="quick-action-title">Issue Book</span>
                                <span class="quick-action-badge">{{ number_format((int) ($currentlyIssued ?? 0)) }} active</span>
                            </span>
                            <span class="quick-action-meta">Start a new loan and keep circulation moving.</span>
                        </span>
                        <span class="quick-action-arrow" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </span>
                    </a>

                    <a href="{{ route('staff.return-book.index') }}" class="quick-action-btn" style="--quick-accent: #f59e0b; --quick-accent-strong: #d97706; --quick-soft: rgba(245, 158, 11, 0.17); --quick-border: rgba(245, 158, 11, 0.2);">
                        <span class="quick-action-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 19V5" />
                                <path d="m5 12 7 7 7-7" />
                            </svg>
                        </span>
                        <span class="quick-action-content">
                            <span class="quick-action-top">
                                <span class="quick-action-title">Return Book</span>
                                <span class="quick-action-badge">{{ number_format((int) ($dueToday ?? 0)) }} due today</span>
                            </span>
                            <span class="quick-action-meta">Process returns, condition checks, and late fees.</span>
                        </span>
                        <span class="quick-action-arrow" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </span>
                    </a>

                    <a href="{{ route('staff.book-requests.index') }}" class="quick-action-btn" style="--quick-accent: #8b5cf6; --quick-accent-strong: #7c3aed; --quick-soft: rgba(139, 92, 246, 0.16); --quick-border: rgba(139, 92, 246, 0.2);">
                        <span class="quick-action-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 12h6" />
                                <path d="M12 9v6" />
                                <path d="M19 21H8a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l6 6v10a2 2 0 0 1-2 2Z" />
                                <path d="M14 3v6h6" />
                            </svg>
                        </span>
                        <span class="quick-action-content">
                            <span class="quick-action-top">
                                <span class="quick-action-title">Review Requests</span>
                                <span class="quick-action-badge">{{ number_format((int) ($pendingRequestsCount ?? 0)) }} pending</span>
                            </span>
                            <span class="quick-action-meta">Approve or reject pending student requests.</span>
                        </span>
                        <span class="quick-action-arrow" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </span>
                    </a>

                    <a href="{{ route('staff.fines.index') }}" class="quick-action-btn" style="--quick-accent: #dc2626; --quick-accent-strong: #b91c1c; --quick-soft: rgba(220, 38, 38, 0.16); --quick-border: rgba(220, 38, 38, 0.2);">
                        <span class="quick-action-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 1v22" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </span>
                        <span class="quick-action-content">
                            <span class="quick-action-top">
                                <span class="quick-action-title">Manage Fines</span>
                                <span class="quick-action-badge">₹{{ number_format((float) ($pendingFinesAmount ?? 0)) }}</span>
                            </span>
                            <span class="quick-action-meta">Collect, waive, or review pending fine records.</span>
                        </span>
                        <span class="quick-action-arrow" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>

            <div class="section-card">
                <div class="section-header">
                    <div class="section-heading">
                        <h2 class="section-title">Overdue Books</h2>
                        <p class="section-description">Books that already need follow-up or recovery.</p>
                    </div>
                    <span class="section-count count-red">{{ $overdueCount ?? 0 }}</span>
                </div>

                <div class="overdue-list js-dashboard-list" data-list-type="overdue_books" data-total-count="{{ (int) ($overdueCount ?? 0) }}" data-limit="10">
                    @if(isset($overdues) && $overdues->count())
                        @foreach($overdues as $item)
                            @php
                                $overdueDays = (int) data_get($item, 'dashboard_overdue_days', 0);
                            @endphp
                            <div class="overdue-item" data-list-item="true">
                                <div class="overdue-info">
                                    <div class="overdue-book">{{ optional($item->book)->title ?? 'Untitled' }}</div>
                                    <div class="overdue-student">{{ data_get($item, 'student.user.name', 'Unknown') }}</div>
                                </div>
                                <div class="overdue-details">
                                    <div class="overdue-date">Due {{ optional($item->due_date)->format('M d, Y') }}</div>
                                    <div class="overdue-days">
                                        {{ number_format($overdueDays) }} {{ $overdueDays === 1 ? 'day' : 'days' }} overdue
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if(($overdueCount ?? 0) > $overdues->count())
                            <div class="list-show-more" data-show-more-wrapper>
                                <button type="button" class="show-more-btn" data-show-more-button data-list-type="overdue_books">
                                    <span data-show-more-label>Show more</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                            </div>
                            <h3 class="empty-title">No overdue books</h3>
                            <p class="empty-subtitle">Great, nothing is overdue right now.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Request Action Modal -->
        <div class="modal-overlay" id="requestModal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <div class="modal-icon" id="modalIcon">
                        <!-- Icon will be set by JS -->
                    </div>
                    <div class="modal-title-group">
                        <h3 class="modal-title" id="modalTitle">Confirm Action</h3>
                        <p class="modal-subtitle" id="modalSubtitle">Review the details below</p>
                    </div>
                    <button class="modal-close" onclick="closeRequestModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="modal-message" id="modalMessageText">Are you sure you want to continue?</p>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn modal-btn-cancel" onclick="closeRequestModal()">
                        Cancel
                    </button>
                    <button class="modal-btn" id="modalConfirmBtn" onclick="confirmRequestAction()">
                        <span id="confirmBtnText">Confirm</span>
                        <svg id="confirmBtnSpinner" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <line x1="12" y1="2" x2="12" y2="6"></line>
                            <line x1="12" y1="18" x2="12" y2="22"></line>
                            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                            <line x1="2" y1="12" x2="6" y2="12"></line>
                            <line x1="18" y1="12" x2="22" y2="12"></line>
                            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dashboardListDataUrl = @json(route('staff.dashboard.list-data'));
        const dashboardCharts = {
            circulation: null,
            activity: null
        };
        let dashboardThemeMode = null;
        let dashboardThemeObserverInitialized = false;
        let doughnutCenterPluginRegistered = false;

        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');

            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            initializeDashboardCharts();
            initializeDashboardLists();
            initializeDashboardThemeObserver();
        });

        function initializeDashboardThemeObserver() {
            if (dashboardThemeObserverInitialized || !document.body) {
                return;
            }

            dashboardThemeMode = document.body.classList.contains('dark-theme') ? 'dark' : 'light';

            const observer = new MutationObserver(() => {
                const nextThemeMode = document.body.classList.contains('dark-theme') ? 'dark' : 'light';

                if (nextThemeMode === dashboardThemeMode) {
                    return;
                }

                dashboardThemeMode = nextThemeMode;
                initializeDashboardCharts();
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });

            dashboardThemeObserverInitialized = true;
        }

        function initializeDashboardCharts() {
            if (typeof Chart === 'undefined') {
                return;
            }

            const isDark = document.body.classList.contains('dark-theme');
            const palette = {
                surface: isDark ? '#1e293b' : '#ffffff',
                grid: isDark ? 'rgba(148, 163, 184, 0.18)' : '#e5e7eb',
                text: isDark ? '#cbd5e1' : '#475569',
                textStrong: isDark ? '#f8fafc' : '#0f172a',
                textMuted: isDark ? '#94a3b8' : '#64748b',
                tooltipBackground: isDark ? '#0f172a' : '#ffffff',
                tooltipBorder: isDark ? '#334155' : '#dbe4f0'
            };

            const doughnutCenterText = {
                id: 'doughnutCenterText',
                afterDraw(chart, args, pluginOptions) {
                    if (chart.config.type !== 'doughnut' || !pluginOptions || !pluginOptions.value) {
                        return;
                    }

                    const {
                        ctx,
                        chartArea
                    } = chart;

                    if (!chartArea) {
                        return;
                    }

                    const centerX = (chartArea.left + chartArea.right) / 2;
                    const centerY = (chartArea.top + chartArea.bottom) / 2;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = pluginOptions.valueColor || palette.textStrong;
                    ctx.font = '700 24px Inter, sans-serif';
                    ctx.fillText(String(pluginOptions.value), centerX, centerY - 10);
                    ctx.fillStyle = pluginOptions.labelColor || palette.textMuted;
                    ctx.font = '500 11px Inter, sans-serif';
                    ctx.fillText(String(pluginOptions.label || ''), centerX, centerY + 14);
                    ctx.restore();
                }
            };

            if (dashboardCharts.circulation) {
                dashboardCharts.circulation.destroy();
                dashboardCharts.circulation = null;
            }

            if (dashboardCharts.activity) {
                dashboardCharts.activity.destroy();
                dashboardCharts.activity = null;
            }

            Chart.defaults.color = palette.text;
            Chart.defaults.borderColor = palette.grid;

            if (!doughnutCenterPluginRegistered) {
                Chart.register(doughnutCenterText);
                doughnutCenterPluginRegistered = true;
            }

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: palette.text,
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: palette.tooltipBackground,
                        borderColor: palette.tooltipBorder,
                        borderWidth: 1,
                        titleColor: palette.textStrong,
                        bodyColor: palette.text,
                        padding: 10,
                        displayColors: true
                    }
                }
            };

            const circulationCanvas = document.getElementById('circulationChart');
            if (circulationCanvas) {
                dashboardCharts.circulation = new Chart(circulationCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($circulationData['labels'] ?? []),
                        datasets: [{
                            data: @json($circulationData['data'] ?? []),
                            backgroundColor: @json($circulationData['colors'] ?? []),
                            borderWidth: 2,
                            borderColor: palette.surface
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '68%',
                        plugins: {
                            ...commonOptions.plugins,
                            tooltip: {
                                ...commonOptions.plugins.tooltip,
                                callbacks: {
                                    label(context) {
                                        const value = Number(context.raw || 0);
                                        const series = Array.isArray(context.dataset.data) ? context.dataset.data : [];
                                        const total = series.reduce((sum, item) => sum + Number(item || 0), 0);
                                        const share = total > 0 ? Math.round((value / total) * 100) : 0;

                                        return `${context.label}: ${value} (${share}%)`;
                                    }
                                }
                            },
                            doughnutCenterText: {
                                value: '{{ number_format($circulationOverview['total'] ?? 0) }}',
                                label: 'Trackable Copies',
                                valueColor: palette.textStrong,
                                labelColor: palette.textMuted
                            }
                        }
                    }
                });
            }

            const activityCanvas = document.getElementById('activityChart');
            if (activityCanvas) {
                dashboardCharts.activity = new Chart(activityCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($activityData['labels'] ?? []),
                        datasets: [{
                                label: 'Issued',
                                data: @json($activityData['issued'] ?? []),
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.14)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#6366f1',
                                pointBorderColor: palette.surface,
                                pointBorderWidth: 2
                            },
                            {
                                label: 'Returned',
                                data: @json($activityData['returned'] ?? []),
                                borderColor: '#f97316',
                                backgroundColor: 'rgba(249, 115, 22, 0.14)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#f97316',
                                pointBorderColor: palette.surface,
                                pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: palette.textMuted,
                                    maxTicksLimit: 7,
                                    precision: 0,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: palette.grid,
                                    borderColor: palette.grid
                                }
                            },
                            x: {
                                ticks: {
                                    color: palette.textMuted,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatOverdueLabel(value) {
            const days = Math.max(0, Number.parseInt(value ?? 0, 10) || 0);
            const unit = days === 1 ? 'day' : 'days';
            return `${days.toLocaleString()} ${unit} overdue`;
        }

        function showToast(message, type = 'success') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            toast.className = 'toast ' + (type === 'error' ? 'error' : 'success');
            toast.textContent = message;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.transition = 'opacity 300ms ease, transform 300ms ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                setTimeout(() => toast.remove(), 350);
            }, 3000);
        }

        function initializeDashboardLists() {
            document.querySelectorAll('.js-dashboard-list').forEach(container => {
                syncDashboardListState(container);
            });

            document.querySelectorAll('[data-show-more-button]').forEach(button => {
                button.addEventListener('click', function() {
                    loadMoreDashboardList(this);
                });
            });
        }

        function getDashboardListRenderedCount(container) {
            return container ? container.querySelectorAll('[data-list-item="true"]').length : 0;
        }

        function getDashboardListFooter(container) {
            return container ? container.querySelector('[data-show-more-wrapper]') : null;
        }

        function insertDashboardListItem(container, item) {
            if (!container || !item) {
                return;
            }

            const footer = getDashboardListFooter(container);

            if (footer) {
                container.insertBefore(item, footer);
                return;
            }

            container.appendChild(item);
        }

        function syncDashboardListState(container, totalCountOverride = null) {
            if (!container) {
                return;
            }

            if (totalCountOverride !== null) {
                container.dataset.totalCount = String(Math.max(0, Number(totalCountOverride) || 0));
            }

            const footer = getDashboardListFooter(container);
            const totalCount = Math.max(0, parseInt(container.dataset.totalCount || '0', 10) || 0);
            const renderedCount = getDashboardListRenderedCount(container);

            container.dataset.renderedCount = String(renderedCount);

            if (footer) {
                footer.hidden = renderedCount >= totalCount;
            }
        }

        function setShowMoreButtonLoading(button, isLoading) {
            if (!button) {
                return;
            }

            const label = button.querySelector('[data-show-more-label]');
            const defaultLabel = button.dataset.defaultLabel || (label ? label.textContent : 'Show more');

            button.dataset.defaultLabel = defaultLabel;
            button.disabled = isLoading;

            if (label) {
                label.textContent = isLoading ? 'Loading...' : defaultLabel;
            }
        }

        function createDueTodayItem(item) {
            const element = document.createElement('div');
            element.className = 'table-item';
            element.dataset.listItem = 'true';
            element.innerHTML = `
                <div class="table-item-top">
                    <div>
                        <div class="table-item-title">${escapeHtml(item.book_title || 'Untitled')}</div>
                        <div class="table-item-subtitle">${escapeHtml(item.student_name || 'Unknown')}</div>
                    </div>
                    <div class="table-item-meta">${escapeHtml(item.due_badge || 'Due today')}</div>
                </div>
                <div class="table-item-meta">Due: ${escapeHtml(item.due_date || 'N/A')}</div>
            `;

            return element;
        }

        function createRecentIssueItem(item) {
            const element = document.createElement('div');
            element.className = 'table-item';
            element.dataset.listItem = 'true';
            element.innerHTML = `
                <div class="table-item-top">
                    <div>
                        <div class="table-item-title">${escapeHtml(item.book_title || 'Untitled')}</div>
                        <div class="table-item-subtitle">${escapeHtml(item.student_name || 'Unknown')}</div>
                    </div>
                    <div class="table-item-meta">${escapeHtml(item.time_ago || 'Recently')}</div>
                </div>
                <div class="table-item-meta">Issued on ${escapeHtml(item.issued_on || 'N/A')}</div>
            `;

            return element;
        }

        function createOverdueItem(item) {
            const element = document.createElement('div');
            element.className = 'overdue-item';
            element.dataset.listItem = 'true';
            element.innerHTML = `
                <div class="overdue-info">
                    <div class="overdue-book">${escapeHtml(item.book_title || 'Untitled')}</div>
                    <div class="overdue-student">${escapeHtml(item.student_name || 'Unknown')}</div>
                </div>
                <div class="overdue-details">
                    <div class="overdue-date">Due ${escapeHtml(item.due_date || 'N/A')}</div>
                    <div class="overdue-days">${escapeHtml(formatOverdueLabel(item.days_overdue))}</div>
                </div>
            `;

            return element;
        }

        function createDashboardListItem(type, item) {
            switch (type) {
                case 'pending_requests':
                    return createPendingRequestItem(item);
                case 'due_today':
                    return createDueTodayItem(item);
                case 'recent_issues':
                    return createRecentIssueItem(item);
                case 'overdue_books':
                    return createOverdueItem(item);
                default:
                    return null;
            }
        }

        async function loadMoreDashboardList(button) {
            const listType = button?.dataset.listType;
            const container = button?.closest('.js-dashboard-list');

            if (!button || !container || !listType) {
                return;
            }

            const offset = getDashboardListRenderedCount(container);
            const limit = Math.max(1, parseInt(container.dataset.limit || '10', 10) || 10);

            setShowMoreButtonLoading(button, true);

            try {
                const url = `${dashboardListDataUrl}?type=${encodeURIComponent(listType)}&offset=${offset}&limit=${limit}`;
                const response = await fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json().catch(() => null);

                if (!response.ok || !data || data.success !== true) {
                    throw new Error(data?.message || `Unable to load more items (HTTP ${response.status})`);
                }

                (data.items || []).forEach(item => {
                    const element = createDashboardListItem(listType, item);
                    if (element) {
                        insertDashboardListItem(container, element);
                    }
                });

                syncDashboardListState(container, data.total ?? container.dataset.totalCount);
            } catch (error) {
                console.error('Failed to load more dashboard items', error);
                showToast('Unable to load more items right now.', 'error');
            } finally {
                setShowMoreButtonLoading(button, false);
            }
        }

        function updatePendingRequestsCount(delta) {
            const displays = document.querySelectorAll('[data-pending-count-display]');
            if (!displays.length) {
                return;
            }

            const current = parseInt(displays[0].textContent, 10) || 0;
            const next = Math.max(0, current + delta);

            displays.forEach(display => {
                display.textContent = next.toString();
            });

            const container = document.querySelector('.pending-requests-list');
            if (container) {
                container.dataset.totalCount = next.toString();
            }
        }

        function createPendingRequestItem(request) {
            const item = document.createElement('div');
            item.className = 'table-item table-request-item';
            item.id = `request-${request.id}`;
            item.dataset.requestId = request.id;
            item.dataset.listItem = 'true';
            item.innerHTML = `
                <div class="table-item-top">
                    <div>
                        <div class="table-item-title">${escapeHtml(request.book.title)}</div>
                        <div class="table-item-subtitle">${escapeHtml(request.student.name)}</div>
                    </div>
                    <div class="table-item-meta">${escapeHtml(request.request_date)}</div>
                </div>
                <div class="table-item-meta">${escapeHtml(request.student.student_id || 'No ID')}</div>
                <div class="table-request-actions">
                    <button class="action-btn btn-accept" onclick="processBookRequest(${request.id}, 'approved')">Accept</button>
                    <button class="action-btn btn-reject" onclick="processBookRequest(${request.id}, 'rejected')">Reject</button>
                </div>
            `;

            return item;
        }

        function renderPendingEmptyState(container) {
            if (!container) {
                return;
            }

            container.innerHTML = `
                <div class="empty-state table-empty-state">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M12 8v4l3 3"></path>
                            <circle cx="12" cy="12" r="10"></circle>
                        </svg>
                    </div>
                    <h3 class="empty-title">No pending requests</h3>
                    <p class="empty-subtitle">You're all caught up for now.</p>
                </div>
            `;

            syncDashboardListState(container, 0);
        }

        function fetchNextPending() {
            const container = document.querySelector('.pending-requests-list');
            if (!container) {
                return;
            }

            const existing = Array.from(container.querySelectorAll('[data-request-id]'))
                .map(element => element.dataset.requestId)
                .join(',');

            const url = `{{ route('staff.book-requests.next') }}?exclude=${encodeURIComponent(existing)}`;

            fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.request) {
                        const emptyState = container.querySelector('.empty-state');
                        if (emptyState) {
                            emptyState.remove();
                        }

                        insertDashboardListItem(container, createPendingRequestItem(data.request));
                        syncDashboardListState(container);
                        return;
                    }

                    if (!container.querySelector('[data-request-id]')) {
                        renderPendingEmptyState(container);
                        return;
                    }

                    syncDashboardListState(container);
                })
                .catch(error => {
                    console.error('Failed to fetch next pending request', error);
                });
        }

        // Modal state
        let currentRequestId = null;
        let currentRequestStatus = null;

        // Open request modal
        function openRequestModal(requestId, status, bookTitle, studentName, requestDate) {
            currentRequestId = requestId;
            currentRequestStatus = status;
            
            const modal = document.getElementById('requestModal');
            const modalIcon = document.getElementById('modalIcon');
            const modalTitle = document.getElementById('modalTitle');
            const modalSubtitle = document.getElementById('modalSubtitle');
            const modalMessageText = document.getElementById('modalMessageText');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            
            if (status === 'approved') {
                modalIcon.className = 'modal-icon approve';
                modalIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                `;
                modalTitle.textContent = 'Approve request?';
                modalSubtitle.textContent = requestDate ? `Requested on ${requestDate}` : 'Book request confirmation';
                modalMessageText.textContent = `Approve "${bookTitle || 'Untitled'}" for ${studentName || 'Unknown'}?`;
                confirmBtn.className = 'modal-btn modal-btn-approve';
                confirmBtnText.textContent = 'Approve';
            } else {
                modalIcon.className = 'modal-icon reject';
                modalIcon.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                `;
                modalTitle.textContent = 'Reject request?';
                modalSubtitle.textContent = requestDate ? `Requested on ${requestDate}` : 'Book request confirmation';
                modalMessageText.textContent = `Reject "${bookTitle || 'Untitled'}" for ${studentName || 'Unknown'}?`;
                confirmBtn.className = 'modal-btn modal-btn-reject';
                confirmBtnText.textContent = 'Reject';
            }
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close request modal
        function closeRequestModal() {
            const modal = document.getElementById('requestModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            currentRequestId = null;
            currentRequestStatus = null;
        }

        // Confirm request action
        async function confirmRequestAction() {
            if (!currentRequestId || !currentRequestStatus) return;
            
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            const confirmBtnSpinner = document.getElementById('confirmBtnSpinner');
            
            // Show loading state
            confirmBtn.classList.add('loading');
            confirmBtnText.textContent = currentRequestStatus === 'approved' ? 'Approving...' : 'Rejecting...';
            confirmBtnSpinner.style.display = 'inline';
            
            const requestElement = document.getElementById(`request-${currentRequestId}`);
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            try {
                const response = await fetch(`{{ url('staff/book-requests') }}/${currentRequestId}`, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        status: currentRequestStatus,
                        staff_message: ''
                    })
                });

                const data = await response.json().catch(() => null);

                if (!response.ok || !data || !data.success) {
                    console.error('Book request update failed', response.status, data);
                    showToast(data && data.message ? data.message : `Request failed (HTTP ${response.status})`, 'error');
                    return;
                }

                if (requestElement) {
                    const buttons = requestElement.querySelectorAll('.action-btn');
                    buttons.forEach(button => {
                        button.disabled = true;
                    });

                    const activeButton = requestElement.querySelector(currentRequestStatus === 'approved' ? '.btn-accept' : '.btn-reject');
                    if (activeButton) {
                        activeButton.textContent = currentRequestStatus === 'approved' ? 'Approved' : 'Rejected';
                    }
                }

                updatePendingRequestsCount(-1);
                showToast(currentRequestStatus === 'approved' ? 'Request approved successfully' : 'Request rejected', 'success');

                setTimeout(() => {
                    closeRequestModal();
                    if (requestElement) {
                        requestElement.remove();
                    }
                    syncDashboardListState(document.querySelector('.pending-requests-list'));
                    fetchNextPending();
                }, 500);
            } catch (error) {
                console.error('Network or JavaScript error while updating request', error);
                showToast('Error updating request. Please try again.', 'error');
            } finally {
                confirmBtn.classList.remove('loading');
                confirmBtnText.textContent = currentRequestStatus === 'approved' ? 'Approve' : 'Reject';
                confirmBtnSpinner.style.display = 'none';
            }
        }

        // Updated processBookRequest function to use modal
        async function processBookRequest(requestId, status) {
            const requestElement = document.getElementById(`request-${requestId}`);
            if (!requestElement) return;
            
            const bookTitle = requestElement.querySelector('.table-item-title')?.textContent || 'Untitled';
            const studentName = requestElement.querySelector('.table-item-subtitle')?.textContent || 'Unknown';
            const requestDate = requestElement.querySelector('.table-item-meta')?.textContent || '-';
            
            openRequestModal(requestId, status, bookTitle, studentName, requestDate);
        }

        // Close modal on overlay click
        document.getElementById('requestModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRequestModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRequestModal();
            }
        });
    </script>
@endpush
