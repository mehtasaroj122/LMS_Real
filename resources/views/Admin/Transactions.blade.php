@extends('Admin.layouts.app')

@section('title', 'Transaction')

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

        .search-input-clearable {
            padding-right: 76px;
        }

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
            line-height: 1.2;
            color: #334155;
            white-space: nowrap;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .clear-btn:hover {
            background-color: #e2e8f0;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .clear-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }

        body.dark-theme .clear-btn {
            background-color: #334155;
            border-color: #475569;
            color: #cbd5e1;
        }

        body.dark-theme .clear-btn:hover {
            background-color: #475569;
            border-color: #64748b;
            color: #f8fafc;
        }

        .issued-books-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 8px;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background-color: #f8fafc;
            transition: border-color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
        }

        body.dark-theme .issued-books-toolbar {
            background-color: #0f172a;
            border-color: #334155;
        }

        .issued-books-toolbar.is-active {
            border-color: rgba(37, 99, 235, 0.32);
            background-color: #eff6ff;
        }

        body.dark-theme .issued-books-toolbar.is-active {
            border-color: rgba(96, 165, 250, 0.72);
            background-color: rgba(30, 41, 59, 0.96);
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.28);
        }

        .issued-books-toolbar[hidden] {
            display: none;
        }

        .bulk-select-label {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #0f172a;
            cursor: pointer;
            user-select: none;
        }

        body.dark-theme .bulk-select-label {
            color: #e2e8f0;
        }

        .bulk-select-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            cursor: pointer;
            position: relative;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .bulk-select-checkbox:hover {
            border-color: #93c5fd;
        }

        .bulk-select-checkbox:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }

        .bulk-select-checkbox:checked {
            border-color: #2563eb;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .bulk-select-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 4px;
            top: 0px;
            width: 5px;
            height: 9px;
            border: solid #ffffff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .bulk-select-checkbox:indeterminate {
            border-color: #2563eb;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .bulk-select-checkbox:indeterminate::after {
            content: '';
            position: absolute;
            left: 3px;
            top: 6px;
            width: 8px;
            height: 2px;
            border-radius: 9999px;
            background: #ffffff;
        }

        body.dark-theme .bulk-select-checkbox {
            border-color: #64748b;
            background-color: #0f172a;
        }

        body.dark-theme .bulk-select-checkbox:hover {
            border-color: #93c5fd;
        }

        body.dark-theme .bulk-select-checkbox:checked,
        body.dark-theme .bulk-select-checkbox:indeterminate {
            border-color: #60a5fa;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .issued-books-selection-summary {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }

        body.dark-theme .issued-books-selection-summary {
            color: #94a3b8;
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

        .selected-value {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 8px;
        }

        body.dark-theme .selected-value {
            background-color: #1e293b;
            border-color: #334155;
        }

        .clear-selection {
            color: #6b7280;
            cursor: pointer;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .clear-selection:hover {
            background-color: #e5e7eb;
            color: #374151;
        }

        body.dark-theme .clear-selection:hover {
            background-color: #4b5563;
            color: #d1d5db;
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
        }

        .remove-book:hover {
            background-color: #fee2e2;
        }

        body.dark-theme .remove-book:hover {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        /* Book Selection Checkbox */
        .book-checkbox-container {
            display: flex;
            align-items: flex-start;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        body.dark-theme .book-checkbox-container {
            border-color: #334155;
        }

        .book-checkbox-container:hover {
            background-color: #f9fafb;
        }

        body.dark-theme .book-checkbox-container:hover {
            background-color: #2d3748;
        }

        .book-checkbox-container.is-selected {
            border-color: rgba(37, 99, 235, 0.32);
            background-color: #eff6ff;
        }

        body.dark-theme .book-checkbox-container.is-selected {
            border-color: rgba(96, 165, 250, 0.72);
            background-color: rgba(30, 41, 59, 0.96);
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.28);
        }

        .book-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            margin-right: 12px;
            margin-top: 2px;
            flex-shrink: 0;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            background-color: #ffffff;
            cursor: pointer;
            position: relative;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease,
                transform 0.2s ease;
        }

        .book-checkbox:hover {
            border-color: #93c5fd;
        }

        .book-checkbox:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }

        .book-checkbox:checked {
            border-color: #2563eb;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }

        .book-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 1px;
            width: 5px;
            height: 10px;
            border: solid #ffffff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        body.dark-theme .book-checkbox {
            border-color: #64748b;
            background-color: #0f172a;
        }

        body.dark-theme .book-checkbox:hover {
            border-color: #93c5fd;
        }

        body.dark-theme .book-checkbox:checked {
            border-color: #60a5fa;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .book-info-full {
            flex: 1;
        }

        .book-meta {
            display: flex;
            gap: 16px;
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
        }

        body.dark-theme .book-meta {
            color: #9ca3af;
        }

        /* Condition Options */
        .condition-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 8px;
        }

        .condition-option {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        body.dark-theme .condition-option {
            border-color: #334155;
        }

        .condition-option.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
        }

        body.dark-theme .condition-option.selected {
            border-color: #3b82f6;
            background-color: #1e3a8a;
        }

        .condition-label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .condition-fine {
            font-size: 12px;
            color: #dc2626;
        }

        body.dark-theme .condition-fine {
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

        /* Status Badges */
        .status-badge {
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }

        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-ontime {
            background-color: #dcfce7;
            color: #166534;
        }

        body.dark-theme .status-overdue {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .status-ontime {
            background-color: #14532d;
            color: #86efac;
        }

        /* Warning Badge */
        .warning-badge {
            background-color: #fef3c7;
            color: #92400e;
        }

        body.dark-theme .warning-badge {
            background-color: #78350f;
            color: #fbbf24;
        }

        /* Info Box */
        .info-box {
            background-color: #eff6ff;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
        }

        body.dark-theme .info-box {
            background-color: #1e3a8a;
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

        /* Form Select */
        .form-select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background-color: #ffffff;
            color: #0f172a;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 8px center;
            background-repeat: no-repeat;
            background-size: 20px;
            padding-right: 36px;
        }

        .form-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        body.dark-theme .form-select {
            background-color: #1e293b;
            border-color: #334155;
            color: #e2e8f0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2394A3B8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        }

        body.dark-theme .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

        /* Scrollable Container */
        .scrollable-container {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 4px;
        }

        /* Total Fine Display */
        .total-fine {
            font-size: 24px;
            font-weight: 700;
            color: #dc2626;
            text-align: right;
        }

        body.dark-theme .total-fine {
            color: #f87171;
        }

        .return-total-summary {
            margin-top: 12px;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: linear-gradient(135deg, #fff7ed, #ffffff);
        }

        body.dark-theme .return-total-summary {
            border-color: #334155;
            background: linear-gradient(135deg, rgba(124, 45, 18, 0.28), rgba(15, 23, 42, 0.96));
        }

        .return-total-summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .return-total-summary-label {
            font-size: 13px;
            font-weight: 700;
            color: #9a3412;
        }

        body.dark-theme .return-total-summary-label {
            color: #fdba74;
        }

        .return-total-summary-note {
            margin-top: 6px;
            font-size: 12px;
            color: #78716c;
        }

        body.dark-theme .return-total-summary-note {
            color: #cbd5e1;
        }

        .return-student-shell {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .return-student-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        body.dark-theme .return-student-header {
            border-bottom-color: #334155;
        }

        .return-student-heading {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .return-student-heading-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .return-student-subtitle {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        body.dark-theme .return-student-subtitle {
            color: #94a3b8;
        }

        .return-issued-chip {
            min-width: 112px;
            padding: 12px 14px;
            border-radius: 14px;
            background: linear-gradient(135deg, #eff6ff, #ffffff);
            border: 1px solid #dbeafe;
            text-align: right;
        }

        body.dark-theme .return-issued-chip {
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.28), rgba(15, 23, 42, 0.96));
            border-color: rgba(96, 165, 250, 0.2);
        }

        .return-issued-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }

        body.dark-theme .return-issued-label {
            color: #94a3b8;
        }

        .return-issued-value {
            display: block;
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            color: #0f172a;
        }

        body.dark-theme .return-issued-value {
            color: #f8fafc;
        }

        .return-student-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .return-meta-card,
        .return-rule-card {
            padding: 14px 16px;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            background-color: #f8fafc;
        }

        body.dark-theme .return-meta-card,
        body.dark-theme .return-rule-card {
            border-color: #334155;
            background-color: #0f172a;
        }

        .return-meta-label,
        .return-rule-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }

        body.dark-theme .return-meta-label,
        body.dark-theme .return-rule-label {
            color: #94a3b8;
        }

        .return-meta-value,
        .return-rule-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
            word-break: break-word;
        }

        body.dark-theme .return-meta-value,
        body.dark-theme .return-rule-value {
            color: #f8fafc;
        }

        .return-rules-section {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .return-rules-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .return-rules-caption {
            font-size: 12px;
            color: #64748b;
        }

        body.dark-theme .return-rules-caption {
            color: #94a3b8;
        }

        .return-rules-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .return-rule-value .detail-stack {
            align-items: flex-start;
            text-align: left;
        }

        @media (max-width: 640px) {
            .return-student-header,
            .return-rules-heading {
                flex-direction: column;
                align-items: flex-start;
            }

            .return-issued-chip {
                width: 100%;
                text-align: left;
            }

            .return-student-meta-grid,
            .return-rules-grid {
                grid-template-columns: 1fr;
            }
        }

        .policy-note {
            border: 1px solid #dbeafe;
            background: linear-gradient(135deg, #eff6ff, #f8fbff);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 12px;
            line-height: 1.5;
            color: #1d4ed8;
        }

        body.dark-theme .policy-note {
            border-color: #1d4ed8;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.22), rgba(15, 23, 42, 0.92));
            color: #bfdbfe;
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

        .fine-breakdown-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            background-color: #f8fafc;
        }

        body.dark-theme .fine-breakdown-card {
            border-color: #334155;
            background-color: #0f172a;
        }

        .fine-breakdown-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
        }

        .fine-breakdown-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        body.dark-theme .fine-breakdown-title {
            color: #f8fafc;
        }

        .fine-breakdown-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 12px;
            color: #64748b;
        }

        body.dark-theme .fine-breakdown-meta {
            color: #94a3b8;
        }

        .fine-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border-radius: 9999px;
            padding: 4px 10px;
            background-color: #e2e8f0;
            color: #334155;
            font-weight: 600;
            font-size: 11px;
        }

        body.dark-theme .fine-chip {
            background-color: #1e293b;
            color: #cbd5e1;
        }

        .fine-breakdown-lines {
            display: grid;
            gap: 6px;
        }

        .fine-breakdown-line {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            font-size: 12px;
            color: #475569;
        }

        body.dark-theme .fine-breakdown-line {
            color: #cbd5e1;
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

        .transaction-policy-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 220px));
            gap: 16px;
            margin-bottom: 24px;
            justify-content: flex-start;
        }

        .transaction-policy-card {
            width: 100%;
            max-width: 220px;
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

        body.dark-theme .privilege-stat:hover {
            border-color: rgba(96, 165, 250, 0.28);
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

        body.dark-theme .privilege-info-card.is-restricted .privilege-counter-pill {
            background-color: rgba(127, 29, 29, 0.88);
            color: #fecaca;
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
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
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

        .privilege-info-card.is-restricted .counter-slot {
            color: #b91c1c;
            border-color: rgba(239, 68, 68, 0.22);
        }

        body.dark-theme .privilege-info-card.is-restricted .counter-slot {
            color: #fecaca;
            border-color: rgba(248, 113, 113, 0.26);
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

        body.dark-theme .counter-slot-empty {
            color: #94a3b8;
            background-color: rgba(15, 23, 42, 0.42);
            border-color: rgba(148, 163, 184, 0.18);
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

        body.dark-theme .privilege-warning-banner {
            border-color: rgba(248, 113, 113, 0.2);
            background-color: rgba(15, 23, 42, 0.54);
            color: #fecaca;
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
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .privilege-details-toggle:hover {
            background-color: rgba(255, 255, 255, 0.9);
            border-color: rgba(37, 99, 235, 0.22);
        }

        body.dark-theme .privilege-details-toggle {
            background-color: rgba(15, 23, 42, 0.44);
            color: #f8fafc;
            border-color: rgba(148, 163, 184, 0.16);
        }

        body.dark-theme .privilege-details-toggle:hover {
            border-color: rgba(96, 165, 250, 0.26);
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

        body.dark-theme .privilege-detail-item {
            background-color: rgba(15, 23, 42, 0.48);
            border-color: rgba(148, 163, 184, 0.16);
        }

        .privilege-detail-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }

        body.dark-theme .privilege-detail-label {
            color: #94a3b8;
        }

        .privilege-detail-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        body.dark-theme .privilege-detail-value {
            color: #f8fafc;
        }

        .privilege-detail-meta {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
        }

        body.dark-theme .privilege-detail-meta {
            color: #cbd5e1;
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

        .privilege-toast.warning {
            background: linear-gradient(135deg, #f97316, #ea580c);
        }

        .privilege-toast.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        body.dark-theme .privilege-toast {
            box-shadow: 0 18px 30px -24px rgba(2, 6, 23, 0.9);
        }

        @keyframes privilegePulse {
            0% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.55);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        @keyframes privilegeCardFade {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes privilegeToastIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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

            .transaction-policy-grid {
                grid-template-columns: 1fr;
            }

            .transaction-policy-card {
                max-width: none;
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
        <!-- Issue Book Section -->
        <div class="mb-8">
            <h1 class="mb-2 text-3xl font-bold text-primary">Issue Book</h1>
            <p class="mb-6 text-secondary">Issue multiple books to a student using their effective library privilege rules.</p>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Issue Form -->
                <div class="p-6 card">
                    <h2 class="mb-6 text-lg font-semibold text-primary">Issue Form</h2>

                    <form id="issueForm" autocomplete="off">
                        <!-- Search Student -->
                        <div class="mb-5">
                            <label class="form-label">
                                Search Student<span class="text-danger">*</span>
                            </label>
                            <div class="search-container">
                                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" id="searchStudent" class="search-input search-input-clearable"
                                    placeholder="Search by name, student ID, or email..." autocomplete="off" required>
                                <button type="button" id="clearStudentBtn" class="clear-btn"
                                    onclick="clearStudentSelection()" style="display: none;">Clear</button>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" id="searchBook" class="search-input"
                                    placeholder="Select a student first..." autocomplete="off" disabled>
                                <div class="search-results" id="bookResults"></div>
                            </div>
                        </div>

                        <!-- Selected Books List -->
                        <div class="selected-books-list" id="selectedBooksList" style="display: none;">
                            <div class="mb-2 text-sm text-secondary" id="selectedBooksHeader">Selected Books</div>
                            <div id="selectedBooksContainer"></div>
                        </div>

                        <!-- Issue Button -->
                        <button type="submit" class="w-full py-3 mt-4 font-semibold rounded-lg btn-primary"
                            id="issueButton" disabled>
                            Issue Books
                        </button>
                    </form>
                </div>

                <!-- Selected Details -->
                <div class="space-y-6">
                    <!-- Selected Student Details -->
                    <div class="p-6 card" id="selectedStudentCard" style="display: none;">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                    <div class="p-6 card" id="availableBooksCard" style="display: none;">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
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
                            <div class="p-3 mt-4 rounded-lg info-box">
                                <p class="text-sm" id="issueSelectionHint">Select books for this student. The selection limit follows the effective library privilege rules.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Book Section -->
        <div>
            <h1 class="mb-2 text-3xl font-bold text-primary">Return Book</h1>
            <p class="mb-6 text-secondary">Process book returns and calculate fines</p>

            <!-- Info Cards -->
            <div class="transaction-policy-grid">
                <div class="p-6 text-center card transaction-policy-card">
                    <div class="mb-2 text-2xl font-bold text-primary">{{ $fineSettings->issue_duration_days }} days</div>
                    <div class="text-sm text-secondary">Issue Duration</div>
                </div>
                <div class="p-6 text-center card transaction-policy-card">
                    <div class="mb-2 text-2xl font-bold text-primary">₹{{ $fineSettings->per_day_fine }}/day</div>
                    <div class="text-sm text-secondary">Late Fine</div>
                </div>
                <div class="p-6 text-center card transaction-policy-card">
                    <div class="mb-2 text-2xl font-bold text-primary">₹{{ $fineSettings->lost_book_penalty }}</div>
                    <div class="text-sm text-secondary">Lost Book Fine</div>
                </div>
                <div class="p-6 text-center card transaction-policy-card">
                    <div class="mb-2 text-2xl font-bold text-primary">₹{{ $fineSettings->damaged_book_penalty }}</div>
                    <div class="text-sm text-secondary">Damaged Book Fine</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Return Form -->
                <div class="p-6 card">
                    <h2 class="mb-6 text-lg font-semibold text-primary">Return Form</h2>

                    <form id="returnForm" autocomplete="off">
                        <!-- Search Student -->
                        <div class="mb-5">
                            <label class="form-label">
                                Search Student<span class="text-danger">*</span>
                            </label>
                            <div class="search-container">
                                <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" id="searchReturnStudent" class="search-input search-input-clearable"
                                    placeholder="Search by name, student ID, or email..." autocomplete="off" required>
                                <button type="button" id="clearReturnStudentBtn" class="clear-btn"
                                    onclick="clearReturnStudentSelection()" style="display: none;">Clear</button>
                                <div class="search-results" id="returnStudentResults"></div>
                            </div>
                            <input type="hidden" id="selectedReturnStudentId">
                        </div>

                        <!-- Issued Books List -->
                        <div class="mb-5" id="issuedBooksSection" style="display: none;">
                            <label class="form-label">
                                Select Books to Return<span class="text-danger">*</span>
                            </label>
                            <div class="issued-books-toolbar" id="issuedBooksToolbar" hidden>
                                <label class="bulk-select-label" for="selectAllIssuedBooks">
                                    <input type="checkbox" class="bulk-select-checkbox" id="selectAllIssuedBooks">
                                    <span>Select all books</span>
                                </label>
                                <span class="issued-books-selection-summary" id="issuedBooksSelectionSummary">0 of 0 selected</span>
                            </div>
                            <div class="scrollable-container" id="issuedBooksContainer"></div>
                        </div>

                        <!-- Book Condition for Selected Books -->
                        <div class="mb-5" id="bookConditionSection" style="display: none;">
                            <label class="form-label">
                                Book Condition & Fine<span class="text-danger">*</span>
                            </label>
                            <div class="condition-options">
                                <div class="condition-option" data-condition="good" data-fine="0"
                                    onclick="selectCondition('good')">
                                    <div class="condition-label">Good</div>
                                    <div class="text-success">No Fine</div>
                                </div>
                                <div class="condition-option" data-condition="fair"
                                    data-fine="{{ $fineSettings->fair_condition_penalty }}"
                                    onclick="selectCondition('fair')">
                                    <div class="condition-label">Fair</div>
                                    <div class="condition-fine">₹{{ $fineSettings->fair_condition_penalty }} Fine</div>
                                </div>
                                <div class="condition-option" data-condition="damaged"
                                    data-fine="{{ $fineSettings->damaged_book_penalty }}"
                                    onclick="selectCondition('damaged')">
                                    <div class="condition-label">Damaged</div>
                                    <div class="condition-fine">₹{{ $fineSettings->damaged_book_penalty }} Fine</div>
                                </div>
                                <div class="condition-option" data-condition="lost"
                                    data-fine="{{ $fineSettings->lost_book_penalty }}" onclick="selectCondition('lost')">
                                    <div class="condition-label">Lost</div>
                                    <div class="condition-fine">₹{{ $fineSettings->lost_book_penalty }} Fine</div>
                                </div>
                            </div>
                            <input type="hidden" id="selectedCondition" value="">
                        </div>

                        <!-- Process Return Button -->
                        <button type="submit" class="w-full py-3 font-semibold rounded-lg btn-primary" id="returnButton"
                            disabled>
                            Process Return
                        </button>

                        <div class="return-total-summary" id="returnTotalSummary" aria-live="polite">
                            <div class="return-total-summary-row">
                                <span class="return-total-summary-label">Total Fine</span>
                                <span class="total-fine" id="returnFormTotalFine">₹0</span>
                            </div>
                            <p class="return-total-summary-note" id="returnTotalSummaryNote">
                                Select books and a return condition to preview the payable fine.
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Return Details -->
                <div class="space-y-6">
                    <!-- Student Information -->
                    <div class="p-6 card" id="returnStudentCard" style="display: none;">
                        <div class="return-student-shell">
                            <div class="return-student-header">
                                <div class="return-student-heading">
                                    <svg class="w-5 h-5 mt-0.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <div class="return-student-heading-copy">
                                        <h3 class="text-base font-semibold text-primary">Student Information</h3>
                                        <p class="return-student-subtitle">
                                            Quick borrower summary with the effective return rules used for fine calculation.
                                        </p>
                                    </div>
                                </div>

                                <div class="return-issued-chip">
                                    <span class="return-issued-label">Books Issued</span>
                                    <span class="return-issued-value" id="returnStudentIssued">0</span>
                                </div>
                            </div>

                            <div class="return-student-meta-grid">
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Name</div>
                                    <div class="return-meta-value" id="returnStudentName">-</div>
                                </div>
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Roll No</div>
                                    <div class="return-meta-value" id="returnStudentID">-</div>
                                </div>
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Department</div>
                                    <div class="return-meta-value" id="returnStudentDepartment">-</div>
                                </div>
                                <div class="return-meta-card">
                                    <div class="return-meta-label">Email</div>
                                    <div class="return-meta-value" id="returnStudentEmail">-</div>
                                </div>
                            </div>

                            <div class="return-rules-section">
                                <div class="return-rules-heading">
                                    <h4 class="text-sm font-semibold text-primary">Effective Return Rules</h4>
                                    <span class="return-rules-caption">Compact policy view for the selected borrower</span>
                                </div>

                                <div class="return-rules-grid">
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Return Condition</div>
                                        <div class="return-rule-value" id="returnConditionSummary">Not selected</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Issue Duration Policy</div>
                                        <div class="return-rule-value" id="returnIssueDurationRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Late Fine Rule</div>
                                        <div class="return-rule-value" id="returnLateFineRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Grace Period</div>
                                        <div class="return-rule-value" id="returnGraceRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Fine Cap Per Book</div>
                                        <div class="return-rule-value" id="returnMaxFineRule">-</div>
                                    </div>
                                    <div class="return-rule-card">
                                        <div class="return-rule-label">Condition Fine</div>
                                        <div class="return-rule-value" id="returnConditionFineRule">-</div>
                                    </div>
                                </div>

                                <div class="policy-note" id="returnPrivilegeNote">
                                    Return fine calculations use the student's effective rules first, then the active default library settings when no override exists.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fine Calculation -->
                    <div class="p-6 card" id="fineCalculationCard" style="display: none;">
                        <h3 class="mb-4 text-base font-semibold text-primary">Fine Calculation</h3>

                        <div class="mb-4 policy-note" id="finePolicySummary">
                            Select at least one issued book and a return condition to preview the fine calculation.
                        </div>

                        <div class="mb-4 space-y-3" id="fineDetails">
                            <!-- Fine details will be populated here -->
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="detail-row">
                                <span class="text-lg font-bold text-primary">Total Fine:</span>
                                <span class="total-fine" id="totalFine">₹0</span>
                            </div>
                        </div>

                        <div class="p-3 mt-4 rounded-lg info-box">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm" id="fineHelperText">Select books and condition to calculate total fine</p>
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
        // Fine settings from backend
        const fineSettings = @json($fineSettings);

        // Issue Book Variables
        let selectedStudent = null;
        let selectedBooks = [];
        let studentPrivileges = null; // Store student-specific privileges
        const maxBooksPerStudent = 5;

        // Return Book Variables
        let selectedReturnStudent = null;
        let returnStudentPrivileges = null; // Store return student privileges
        let selectedIssuedBooks = [];
        let selectedCondition = null;
        let returnSearchDebounceTimer = null;
        let issueStudentSearchRequest = 0;
        let returnStudentSearchRequest = 0;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== Transaction Page Loaded ===');
            console.log('Fine Settings:', fineSettings);
            
            // Verify the page is ready
            console.log(
                '%cTransaction Page Ready - If you don\'t see search results, check the browser console for errors',
                'color: green; font-weight: bold; font-size: 14px'
            );

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

                const normalizedType = ['success', 'error', 'warning', 'info'].includes(type)
                    ? type
                    : 'info';
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
            // ========== ISSUE BOOK FUNCTIONALITY ==========

            // Issue Book Elements
            const searchStudentInput = document.getElementById('searchStudent');
            const studentResults = document.getElementById('studentResults');
            const selectedStudentId = document.getElementById('selectedStudentId');
            const selectedStudentCard = document.getElementById('selectedStudentCard');
            const availableBooksCard = document.getElementById('availableBooksCard');
            const privilegeInfoCard = document.getElementById('privilegeInfoCard');
            const privilegeWarningBanner = document.getElementById('privilegeWarningBanner');
            const privilegeWarningText = document.getElementById('privilegeWarningText');
            const privilegeDetailsPanel = document.getElementById('privilegeDetailsPanel');
            const privilegeDetailsToggle = document.getElementById('privilegeDetailsToggle');
            const privilegeDetailsLabel = document.getElementById('privilegeDetailsLabel');

            const searchBookInput = document.getElementById('searchBook');
            const bookResults = document.getElementById('bookResults');
            const selectedBooksList = document.getElementById('selectedBooksList');
            const selectedBooksContainer = document.getElementById('selectedBooksContainer');
            const issueButton = document.getElementById('issueButton');
            const issueForm = document.getElementById('issueForm');

            // ========== RETURN BOOK FUNCTIONALITY ==========

            // Return Book Elements
            const searchReturnStudentInput = document.getElementById('searchReturnStudent');
            const returnStudentResults = document.getElementById('returnStudentResults');
            const selectedReturnStudentId = document.getElementById('selectedReturnStudentId');
            const returnStudentCard = document.getElementById('returnStudentCard');
            const issuedBooksSection = document.getElementById('issuedBooksSection');
            const issuedBooksToolbar = document.getElementById('issuedBooksToolbar');
            const issuedBooksContainer = document.getElementById('issuedBooksContainer');
            const selectAllIssuedBooksCheckbox = document.getElementById('selectAllIssuedBooks');
            const issuedBooksSelectionSummary = document.getElementById('issuedBooksSelectionSummary');
            const bookConditionSection = document.getElementById('bookConditionSection');
            const returnButton = document.getElementById('returnButton');
            const returnForm = document.getElementById('returnForm');
            const fineCalculationCard = document.getElementById('fineCalculationCard');
            const fineDetails = document.getElementById('fineDetails');
            const totalFineElement = document.getElementById('totalFine');
            const returnFormTotalFineElement = document.getElementById('returnFormTotalFine');
            const returnTotalSummaryNote = document.getElementById('returnTotalSummaryNote');
            const finePolicySummary = document.getElementById('finePolicySummary');
            const fineHelperText = document.getElementById('fineHelperText');
            const selectedBooksHeader = document.getElementById('selectedBooksHeader');
            const issueSelectionHint = document.getElementById('issueSelectionHint');

            const privilegeFields = ['max_books', 'issue_duration_days', 'per_day_fine', 'grace_period_days',
                'max_fine_amount', 'borrowing_allowed'
            ];

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

            function toTitleCase(value) {
                if (!value) return 'Not selected';
                return String(value)
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, char => char.toUpperCase());
            }

            function setDetailValue(elementId, primary, secondary = '') {
                const element = document.getElementById(elementId);
                if (!element) return;

                if (!secondary) {
                    element.textContent = primary;
                    return;
                }

                element.innerHTML = `
                    <span class="detail-stack">
                        <span>${escapeHtml(primary)}</span>
                        <span class="detail-meta">${escapeHtml(secondary)}</span>
                    </span>
                `;
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
                    source(field) {
                        return rawPrivileges[field] !== null && rawPrivileges[field] !== undefined ?
                            'Student override' :
                            'Global default';
                    },
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

            function getReturnRules() {
                return returnStudentPrivileges?.effective ?? null;
            }

            function getConditionFine(condition) {
                if (!condition) return 0;
                const conditionOption = document.querySelector(`.condition-option[data-condition="${condition}"]`);
                return Number(conditionOption?.dataset.fine ?? 0);
            }

            function setReturnTotalFineDisplay(amount, note) {
                const formatted = formatCurrency(amount);
                totalFineElement.textContent = formatted;
                if (returnFormTotalFineElement) {
                    returnFormTotalFineElement.textContent = formatted;
                }
                if (returnTotalSummaryNote && note !== undefined) {
                    returnTotalSummaryNote.textContent = note;
                }
            }

            function animatePrivilegeCard() {
                if (!privilegeInfoCard) return;
                privilegeInfoCard.classList.remove('is-visible');
                void privilegeInfoCard.offsetWidth;
                privilegeInfoCard.classList.add('is-visible');
            }

            window.renderRestrictedState = function(student, privileges) {
                privilegeInfoCard.classList.add('is-restricted');
                privilegeWarningBanner.style.display = 'flex';
                privilegeWarningText.textContent =
                    `${student.name} is currently blocked from borrowing. Book search and issuing stay disabled until borrowing access is restored.`;
                issueSelectionHint.textContent =
                    'Borrowing is restricted for this student, so no new books can be selected right now.';
                setPrivilegeStat(
                    'privilegeCanIssue',
                    '0',
                    'Borrowing is currently restricted'
                );
            };

            window.updateBookCounter = function() {
                const slotsContainer = document.getElementById('bookCounterSlots');
                const counterText = document.getElementById('bookCounterText');
                const counterPill = document.getElementById('bookCounterPill');
                const rules = getIssueRules();

                if (!slotsContainer || !counterText || !counterPill) return;

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

                counterText.textContent =
                    `${selectedCount} selected out of ${totalSlots} available slot${totalSlots === 1 ? '' : 's'}.`;

                for (let index = 0; index < totalSlots; index++) {
                    const slot = document.createElement('div');
                    slot.className = `counter-slot ${index < selectedCount ? 'filled' : 'available'}`;
                    slot.textContent = String(index + 1);
                    slot.setAttribute(
                        'aria-label',
                        index < selectedCount ?
                        `Selected book slot ${index + 1}` :
                        `Available book slot ${index + 1}`
                    );
                    slotsContainer.appendChild(slot);
                }
            };

            window.renderPrivilegeInfoCard = function(student, privileges, rawPrivileges = {}, defaults = {}) {
                const rules = getIssueRules();
                if (!student || !privileges || !rules) return;

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
                setPrivilegeStat(
                    'privilegeDuration',
                    `${privileges.issue_duration_days}d`,
                    `Due ${dueDate}`
                );
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
                    `${remainingAfterSelection} slot${remainingAfterSelection === 1 ? '' : 's'} still open for new issues`
                );
                setPrivilegeDetail(
                    'privilegeDurationDetail',
                    `${privileges.issue_duration_days} day${Number(privileges.issue_duration_days) === 1 ? '' : 's'}`,
                    `${rawPrivileges.issue_duration_days !== null && rawPrivileges.issue_duration_days !== undefined ? 'Student-specific' : 'Default'} duration rule`
                );
                setPrivilegeDetail(
                    'privilegeDueDate',
                    dueDate,
                    `Preview if books are issued today at ${formatCurrency(privileges.per_day_fine, 0)}/day after the grace period`
                );

                selectedBooksHeader.textContent =
                    `Selected Books (${selectedBooks.length} selected, ${availableNow} slot${availableNow === 1 ? '' : 's'} available)`;
                issueSelectionHint.textContent = privileges.borrowing_allowed ?
                    `Select up to ${remainingAfterSelection} more book${remainingAfterSelection === 1 ? '' : 's'} for this student using the effective privilege rules.` :
                    'Borrowing is restricted for this student, so no books can be added to the issue list.';

                if (!privileges.borrowing_allowed) {
                    renderRestrictedState(student, privileges);
                }

                updateBookCounter();
                animatePrivilegeCard();
            };

            function renderIssuePrivilegeSummary() {
                const rules = getIssueRules();
                if (!selectedStudent || !rules) return;

                renderPrivilegeInfoCard(
                    selectedStudent,
                    rules,
                    studentPrivileges?.raw ?? {},
                    studentPrivileges?.defaults ?? {}
                );
            }

            window.togglePrivilegeDetails = function() {
                const isExpanded = privilegeDetailsToggle.getAttribute('aria-expanded') === 'true';

                if (isExpanded) {
                    privilegeDetailsToggle.setAttribute('aria-expanded', 'false');
                    privilegeDetailsLabel.textContent = 'Show Details';
                    privilegeDetailsPanel.setAttribute('aria-hidden', 'true');
                    privilegeDetailsPanel.classList.remove('open');
                    window.setTimeout(() => {
                        if (privilegeDetailsToggle.getAttribute('aria-expanded') === 'false') {
                            privilegeDetailsPanel.hidden = true;
                        }
                    }, 250);
                    return;
                }

                privilegeDetailsPanel.hidden = false;
                privilegeDetailsPanel.setAttribute('aria-hidden', 'false');
                privilegeDetailsToggle.setAttribute('aria-expanded', 'true');
                privilegeDetailsLabel.textContent = 'Hide Details';
                window.requestAnimationFrame(() => {
                    privilegeDetailsPanel.classList.add('open');
                });
            };

            function renderReturnPrivilegeSummary() {
                const rules = getReturnRules();
                if (!selectedReturnStudent || !rules) return;

                const conditionFine = getConditionFine(selectedCondition);
                const overrideCount = returnStudentPrivileges?.overrideCount?.() ?? 0;

                setDetailValue(
                    'returnConditionSummary',
                    selectedCondition ? toTitleCase(selectedCondition) : 'Not selected',
                    selectedCondition ? 'This condition fine is applied to each selected book.' :
                    'Choose a condition to complete the fine preview.'
                );
                setDetailValue(
                    'returnIssueDurationRule',
                    `${rules.issue_duration_days} day${Number(rules.issue_duration_days) === 1 ? '' : 's'}`,
                    returnStudentPrivileges.source('issue_duration_days')
                );
                setDetailValue(
                    'returnLateFineRule',
                    `${formatCurrency(rules.per_day_fine)}/day after grace`,
                    returnStudentPrivileges.source('per_day_fine')
                );
                setDetailValue(
                    'returnGraceRule',
                    `${rules.grace_period_days} day${Number(rules.grace_period_days) === 1 ? '' : 's'} grace`,
                    returnStudentPrivileges.source('grace_period_days')
                );
                setDetailValue(
                    'returnMaxFineRule',
                    `${formatCurrency(rules.max_fine_amount)} per book`,
                    returnStudentPrivileges.source('max_fine_amount')
                );
                setDetailValue(
                    'returnConditionFineRule',
                    selectedCondition ? `${formatCurrency(conditionFine)} per book` : formatCurrency(0),
                    selectedCondition ? 'Condition penalties come from the active return settings.' :
                    'Select a return condition to see the matching penalty.'
                );

                const note = document.getElementById('returnPrivilegeNote');
                if (note) {
                    note.textContent = overrideCount > 0 ?
                        `This return preview is using ${overrideCount} student-specific privilege override${overrideCount === 1 ? '' : 's'} where available, then default library settings for the rest.` :
                        'No student-specific return overrides were found, so the preview is using the active default library settings.';
                }
            }

            function resetIssuePrivilegeSummary() {
                privilegeInfoCard.style.display = 'none';
                privilegeInfoCard.classList.remove('is-restricted', 'is-visible');
                privilegeWarningBanner.style.display = 'none';
                setPrivilegeStat('privilegeMaxBooks', '-', 'Effective issue ceiling');
                setPrivilegeStat('privilegeDuration', '-', 'Standard issue period');
                setPrivilegeStat('privilegeFineRate', '-', 'Per overdue day');
                setPrivilegeStat('privilegeCanIssue', '-', 'Remaining today');
                setPrivilegeDetail('privilegeSource', '-', '-');
                setPrivilegeDetail('privilegeIssued', '-', '-');
                setPrivilegeDetail('privilegeDurationDetail', '-', '-');
                setPrivilegeDetail('privilegeDueDate', '-', '-');
                selectedBooksHeader.textContent = 'Selected Books';
                issueSelectionHint.textContent =
                    'Select books for this student. The selection limit follows the effective library privilege rules.';
                document.getElementById('privilegeCardHeading').textContent = 'Effective Borrowing Access';
                document.getElementById('privilegeSummaryText').textContent =
                    'Select a student to review borrowing eligibility, issue duration, and remaining capacity.';
                document.getElementById('privilegeStatusText').textContent = 'Allowed';
                privilegeDetailsLabel.textContent = 'Show Details';
                privilegeDetailsToggle.setAttribute('aria-expanded', 'false');
                privilegeDetailsPanel.classList.remove('open');
                privilegeDetailsPanel.hidden = true;
                privilegeDetailsPanel.setAttribute('aria-hidden', 'true');
                updateBookCounter();
            }

            function resetReturnPrivilegeSummary() {
                [
                    'returnConditionSummary', 'returnIssueDurationRule', 'returnLateFineRule', 'returnGraceRule',
                    'returnMaxFineRule', 'returnConditionFineRule'
                ].forEach(id => setDetailValue(id, id === 'returnConditionSummary' ? 'Not selected' : '-'));
                const note = document.getElementById('returnPrivilegeNote');
                if (note) {
                    note.textContent =
                        'Return fine calculations use the student\'s effective rules first, then the active default library settings when no override exists.';
                }
                finePolicySummary.textContent =
                    'Select at least one issued book and a return condition to preview the fine calculation.';
                fineHelperText.textContent = 'Select books and condition to calculate total fine';
            }

            // ========== VERIFY ELEMENTS ARE FOUND ==========

            // Log to verify event listeners are attached
            if (searchStudentInput) {
                console.log('✓ Student search event listener attached');
            } else {
                console.error('✗ searchStudentInput element not found!');
            }
            
            if (searchBookInput) {
                console.log('✓ Book search event listener attached');
            } else {
                console.error('✗ searchBookInput element not found!');
            }
            
            if (searchReturnStudentInput) {
                console.log('✓ Return student search event listener attached');
            } else {
                console.error('✗ searchReturnStudentInput element not found!');
            }

            selectAllIssuedBooksCheckbox?.addEventListener('change', function() {
                toggleAllIssuedBooks(this.checked);
            });

            // ========== ISSUE BOOK SEARCH FUNCTIONALITY ==========

            // Search Students for Issue
            searchStudentInput.addEventListener('input', function(e) {
                console.log('Student search input event fired:', this.value);
                const query = this.value;
                const requestId = ++issueStudentSearchRequest;
                studentResults.innerHTML = '';

                if (query.length < 2) {
                    studentResults.style.display = 'none';
                    return;
                }

                const studentUrl = `{{ route('admin.transactions.students') }}?query=${encodeURIComponent(query)}`;
                console.log('Fetching students from:', studentUrl);

                // Fetch students from API
                fetch(studentUrl, { credentials: 'include' })
                    .then(response => {
                        console.log('Student response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(students => {
                        if (requestId !== issueStudentSearchRequest) {
                            return;
                        }

                        console.log('Students received:', students);
                        studentResults.innerHTML = '';
                        const uniqueStudents = [...new Map((students || []).map(student => [student.id, student])).values()];

                        if (uniqueStudents.length === 0) {
                            studentResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No students found</div></div>';
                        } else {
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
                        studentResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error: ' + error.message + '</div></div>';
                        studentResults.style.display = 'block';
                    });
            });

            // Search Books for Issue
            searchBookInput.addEventListener('input', function() {
                console.log('Book search input event fired:', this.value);
                const query = this.value;
                bookResults.innerHTML = '';

                if (query.length < 2) {
                    bookResults.style.display = 'none';
                    return;
                }

                if (!selectedStudent) {
                    console.warn('No student selected for book search');
                    bookResults.innerHTML =
                        '<div class="result-item"><div class="result-title">Please select a student first</div></div>';
                    bookResults.style.display = 'block';
                    return;
                }

                const issueRules = getIssueRules();
                if (!issueRules || !issueRules.borrowing_allowed) {
                    bookResults.innerHTML =
                        '<div class="result-item"><div class="result-title">Borrowing is restricted for this student</div></div>';
                    bookResults.style.display = 'block';
                    return;
                }

                const booksUrl = `{{ route('admin.transactions.books') }}?query=${encodeURIComponent(query)}&studentId=${selectedStudent.id}`;
                console.log('Fetching books from:', booksUrl);

                // Fetch available books from API
                fetch(booksUrl, { credentials: 'include' })
                    .then(response => {
                        console.log('Books response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(books => {
                        console.log('Books received:', books);
                        if (books.length === 0) {
                            bookResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No available books found</div></div>';
                        } else {
                            books.forEach(book => {
                                // Skip if already selected
                                if (selectedBooks.some(b => b.id === book.id)) {
                                    return;
                                }

                                const item = document.createElement('div');
                                item.className = 'result-item';
                                item.dataset.id = book.id;
                                item.innerHTML = `
                                    <div class="result-title">${book.title}</div>
                                    <div class="result-subtitle">${book.author} • ISBN: ${book.isbn}</div>
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
                        bookResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error: ' + error.message + '</div></div>';
                        bookResults.style.display = 'block';
                    });
            });

            // ========== RETURN BOOK SEARCH FUNCTIONALITY ==========
            // Search Students for Return (with debouncing to improve performance)
            searchReturnStudentInput.addEventListener('input', function() {
                console.log('Return student search input event fired:', this.value);
                const query = this.value;
                const requestId = ++returnStudentSearchRequest;

                if (query.length < 2) {
                    returnStudentResults.style.display = 'none';
                    if (returnSearchDebounceTimer) clearTimeout(returnSearchDebounceTimer);
                    return;
                }

                // Show loading state
                returnStudentResults.innerHTML =
                    '<div class="result-item"><div class="result-title">Searching...</div></div>';
                returnStudentResults.style.display = 'block';

                // Clear previous timeout
                if (returnSearchDebounceTimer) clearTimeout(returnSearchDebounceTimer);

                // Fetch immediately without delay - show results as you type
                const returnUrl = `{{ route('admin.transactions.students') }}?query=${encodeURIComponent(query)}`;
                console.log('Fetching return students from:', returnUrl);
                
                fetch(returnUrl, { credentials: 'include' })
                    .then(response => {
                        console.log('Return Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(students => {
                        if (requestId !== returnStudentSearchRequest) {
                            return;
                        }

                        console.log('Return Students received:', students);
                        const uniqueStudents = [...new Map((students || []).map(s => [s.id, s])).values()];

                        if (uniqueStudents.length === 0) {
                            returnStudentResults.innerHTML =
                                '<div class="result-item"><div class="result-title">No students found</div></div>';
                        } else {
                            returnStudentResults.innerHTML = '';

                            const studentItems = new Map();

                            // Create item elements for all students immediately
                            uniqueStudents.forEach(student => {
                                const item = document.createElement('div');
                                item.className = 'result-item';
                                item.dataset.id = student.id;
                                item.innerHTML = `
                                    <div class="result-title">${student.name} (${student.roll_no})</div>
                                    <div class="result-subtitle">${student.department} • <span class="book-count">...</span> books</div>
                                `;
                                item.style.cursor = 'pointer';

                                returnStudentResults.appendChild(item);
                                studentItems.set(student.id, {
                                    item,
                                    student
                                });
                            });

                            // Fetch issued books count for ALL students in parallel (quick lightweight call)
                            uniqueStudents.forEach(student => {
                                fetch(
                                        `{{ route('admin.transactions.issued-books') }}?studentId=${student.id}&countOnly=1`,
                                        { credentials: 'include' })
                                    .then(resp => resp.json())
                                    .then(data => {
                                        const count = data.count || 0;
                                        const {
                                            item
                                        } = studentItems.get(student.id);
                                        const bookCountSpan = item.querySelector(
                                            '.book-count');

                                        if (bookCountSpan) {
                                            bookCountSpan.textContent = count;

                                            // Disable if no books
                                            if (count === 0) {
                                                item.style.opacity = '0.6';
                                                item.style.cursor = 'not-allowed';
                                                item.style.pointerEvents = 'none';
                                            }
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Error fetching book count:', err);
                                        const {
                                            item
                                        } = studentItems.get(student.id);
                                        const bookCountSpan = item.querySelector(
                                            '.book-count');
                                        if (bookCountSpan) bookCountSpan.textContent = '0';
                                    });
                            });

                            // Attach click handler to fetch full book details when clicked
                            uniqueStudents.forEach(student => {
                                const {
                                    item
                                } = studentItems.get(student.id);
                                item.addEventListener('click', function() {
                                    console.log('Fetching issued books for student:',
                                        student.id);
                                    item.innerHTML = `
                                        <div class="result-title">${student.name} (${student.roll_no})</div>
                                        <div class="result-subtitle text-muted">Loading books...</div>
                                    `;

                                    fetch(
                                            `{{ route('admin.transactions.issued-books') }}?studentId=${student.id}`,
                                            { credentials: 'include' })
                                        .then(resp => resp.json())
                                        .then(issuedBooks => {
                                            selectStudentForReturn(student,
                                                issuedBooks);
                                        })
                                        .catch(err => {
                                            console.error(
                                                'Error fetching issued books:',
                                                err);
                                            selectStudentForReturn(student, []);
                                        });
                                });
                            });
                        }

                        returnStudentResults.style.display = 'block';
                    })
                    .catch(error => {
                        if (requestId !== returnStudentSearchRequest) {
                            return;
                        }

                        console.error('Error fetching return students:', error);
                        returnStudentResults.innerHTML =
                            '<div class="result-item"><div class="result-title">Error: ' + error.message + '</div></div>';
                        returnStudentResults.style.display = 'block';
                    });
            });

            // ========== ISSUE BOOK FUNCTIONS ==========

            window.selectStudentForIssue = function(student) {
                selectedStudent = student;
                studentPrivileges = null;
                selectedBooks = [];
                selectedStudentId.value = student.id;
                searchStudentInput.value = `${student.name} (${student.roll_no})`;
                studentResults.style.display = 'none';
                document.getElementById('clearStudentBtn').style.display = 'block';
                selectedStudentCard.style.display = 'none';
                availableBooksCard.style.display = 'none';
                resetIssuePrivilegeSummary();
                searchBookInput.value = '';
                bookResults.style.display = 'none';
                setBookSearchState();
                updateSelectedBooksList();
                updateIssueButton();
                updateAvailableBooksInfo();

                // Fetch student privileges
                fetch(`/admin/students/${student.id}/privileges`, { credentials: 'include' })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            showCustomAlert('Privilege Load Failed', 'Failed to load student privileges.', 'error');
                            return;
                        }

                        studentPrivileges = createPrivilegeState({
                            ...data,
                            hasPrivilegeOverride: student.hasPrivilegeOverride
                        });
                        const rules = getIssueRules();

                        // Update student details with privilege information
                        document.getElementById('studentName').textContent = student.name;
                        document.getElementById('studentID').textContent = student.roll_no;
                        document.getElementById('studentDepartment').textContent = student.department;
                        document.getElementById('studentEmail').textContent = student.email;
                        document.getElementById('studentIssued').textContent =
                            `${student.issued} / ${rules.max_books}`;

                        // Update progress bar
                        const progressPercent = Math.min(100, (student.issued / rules.max_books) * 100);
                        document.getElementById('issuedProgress').style.width = `${progressPercent}%`;

                        selectedStudentCard.style.display = 'block';
                        availableBooksCard.style.display = 'block';

                        // Update available books info based on privilege
                        document.getElementById('canIssueMore').textContent = getRemainingIssueCapacity(rules, student);
                        document.getElementById('selectedCount').textContent = '0 books';
                        document.getElementById('totalBooks').textContent = '0';
                        renderIssuePrivilegeSummary();
                        setBookSearchState(student, rules);
                        updateIssueButton();
                    })
                    .catch(error => {
                        console.error('Error fetching privileges:', error);
                        showCustomAlert('Error', 'Failed to load student privileges: ' + error.message, 'error');
                        clearStudentSelection();
                    });
            };

            window.addBookToSelection = function(book) {
                const rules = getIssueRules();
                if (!selectedStudent || !rules) return;

                // Check if student can issue more books (based on privileges)
                const canIssueMore = getIssueCapacity(rules, selectedStudent);
                if (selectedBooks.length >= canIssueMore) {
                    showCustomAlert(
                        'Book Limit Reached',
                        `Student can only issue ${canIssueMore} more book(s). Privilege limit: ${rules.max_books} books maximum.`,
                        'warning'
                    );
                    return;
                }

                // Check if book is already selected
                if (selectedBooks.some(b => b.id === book.id)) {
                    showCustomAlert(
                        'Book Already Selected',
                        'This book is already in the selection list.',
                        'warning'
                    );
                    return;
                }

                // Check if book is available
                if (book.available <= 0) {
                    showCustomAlert(
                        'Book Unavailable',
                        'This book is currently unavailable. No copies in stock.',
                        'error'
                    );
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
                    return;
                }

                selectedBooksList.style.display = 'block';

                selectedBooks.forEach(book => {
                    const bookItem = document.createElement('div');
                    bookItem.className = 'selected-book-item';
                    bookItem.innerHTML = `
                        <div class="book-info">
                            <div class="book-title">${book.title}</div>
                            <div class="book-details">${book.author} • ${book.category}</div>
                        </div>
                        <button type="button" class="remove-book" data-book-id="${book.id}">
                            Remove
                        </button>
                    `;
                    selectedBooksContainer.appendChild(bookItem);
                });

                // Attach remove button handlers
                document.querySelectorAll('.remove-book').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const bookId = parseInt(this.getAttribute('data-book-id'));
                        removeBookFromSelection(bookId);
                    });
                });
            };

            window.updateIssueButton = function() {
                const rules = getIssueRules();
                const canIssueMore = selectedStudent && rules ?
                    getIssueCapacity(rules, selectedStudent) : 0;
                const selectedCount = selectedBooks.length;

                if (selectedStudent && rules && rules.borrowing_allowed && selectedCount > 0) {
                    issueButton.disabled = false;
                    issueButton.textContent = `Issue Books (${selectedCount}/${canIssueMore})`;
                } else {
                    issueButton.disabled = true;
                    issueButton.textContent = rules && !rules.borrowing_allowed ?
                        'Issuing Restricted' :
                        `Issue Books (${selectedCount}/${canIssueMore})`;
                }
            };

            window.updateAvailableBooksInfo = function() {
                const rules = getIssueRules();
                if (!selectedStudent || !rules) return;

                const canIssueMore = getIssueCapacity(rules, selectedStudent);
                const selectedCount = selectedBooks.length;

                document.getElementById('selectedCount').textContent =
                    `${selectedCount} book${selectedCount !== 1 ? 's' : ''}`;
                document.getElementById('totalBooks').textContent = selectedCount;
                document.getElementById('canIssueMore').textContent = Math.max(0, canIssueMore - selectedCount);
                renderIssuePrivilegeSummary();
            };

            window.clearStudentSelection = function() {
                selectedStudent = null;
                studentPrivileges = null;
                selectedStudentId.value = '';
                searchStudentInput.value = '';
                document.getElementById('clearStudentBtn').style.display = 'none';
                selectedStudentCard.style.display = 'none';
                availableBooksCard.style.display = 'none';
                selectedBooks = [];
                updateSelectedBooksList();
                updateIssueButton();
                resetIssuePrivilegeSummary();

                // Disable book search
                setBookSearchState();
                searchBookInput.value = '';
                bookResults.style.display = 'none';
            };

            // ========== RETURN BOOK FUNCTIONS ==========
            window.selectStudentForReturn = function(student, issuedBooks) {
                selectedReturnStudent = student;
                selectedReturnStudentId.value = student.id;
                searchReturnStudentInput.value = `${student.name} (${student.roll_no})`;
                returnStudentResults.style.display = 'none';
                document.getElementById('clearReturnStudentBtn').style.display = 'block';

                // Fetch student privileges for return calculations
                fetch(`/admin/students/${student.id}/privileges`, { credentials: 'include' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            returnStudentPrivileges = createPrivilegeState(data);
                        } else {
                            returnStudentPrivileges = createPrivilegeState();
                        }

                        // Update student details
                        document.getElementById('returnStudentName').textContent = student.name;
                        document.getElementById('returnStudentID').textContent = student.roll_no;
                        document.getElementById('returnStudentDepartment').textContent = student.department;
                        document.getElementById('returnStudentEmail').textContent = student.email;

                        // Display issued books count
                        document.getElementById('returnStudentIssued').textContent = issuedBooks.length;

                        returnStudentCard.style.display = 'block';
                        issuedBooksSection.style.display = 'block';
                        fineCalculationCard.style.display = 'block';

                        // Display issued books with checkboxes
                        displayIssuedBooks(issuedBooks);

                        // Reset selections
                        selectedIssuedBooks = [];
                        selectedCondition = null;
                        resetConditionSelection();
                        renderReturnPrivilegeSummary();
                        updateReturnButton();
                        calculateTotalFine();
                        syncIssuedBooksBulkState();
                    })
                    .catch(error => {
                        console.error('Error fetching privileges:', error);
                        returnStudentPrivileges = createPrivilegeState();

                        // Continue with return process
                        document.getElementById('returnStudentName').textContent = student.name;
                        document.getElementById('returnStudentID').textContent = student.roll_no;
                        document.getElementById('returnStudentDepartment').textContent = student.department;
                        document.getElementById('returnStudentEmail').textContent = student.email;
                        document.getElementById('returnStudentIssued').textContent = issuedBooks.length;

                        returnStudentCard.style.display = 'block';
                        issuedBooksSection.style.display = 'block';
                        fineCalculationCard.style.display = 'block';

                        displayIssuedBooks(issuedBooks);
                        selectedIssuedBooks = [];
                        selectedCondition = null;
                        resetConditionSelection();
                        renderReturnPrivilegeSummary();
                        updateReturnButton();
                        calculateTotalFine();
                        syncIssuedBooksBulkState();
                    });
            };

            window.displayIssuedBooks = function(issuedBooks) {
                issuedBooksContainer.innerHTML = '';

                if (issuedBooks.length === 0) {
                    if (issuedBooksToolbar) {
                        issuedBooksToolbar.hidden = true;
                    }
                    issuedBooksContainer.innerHTML =
                        '<div class="py-4 text-center text-secondary">No issued books found</div>';
                    return;
                }

                const today = new Date();

                issuedBooks.forEach(issuedBook => {
                    if (issuedBook.returned) return;

                    const dueDate = new Date(`${issuedBook.dueDate}T00:00:00`);
                    const diffTime = today - dueDate;
                    // Prefer server-provided value (keeps consistent with Carbon calculation)
                    // Fallback to client-side calculation using Math.floor to match Carbon's diffInDays behavior
                    const serverOverdue = parseInt(issuedBook.overdueDays);
                    const clientOverdue = Math.max(0, Math.floor(diffTime / (1000 * 60 * 60 * 24)));
                    const overdueDays = Number.isFinite(serverOverdue) ? serverOverdue : clientOverdue;
                    const isOverdue = overdueDays > 0;

                    const bookItem = document.createElement('div');
                    bookItem.className = 'book-checkbox-container';
                    bookItem.innerHTML = `
                        <input type="checkbox" class="book-checkbox" id="book-${issuedBook.id}" 
                               data-book-id="${issuedBook.id}"
                               onchange="toggleIssuedBookSelection(${issuedBook.id}, this.checked)"
                               data-overdue-days="${overdueDays}"
                               data-issue-date="${issuedBook.issueDate}"
                               data-due-date="${issuedBook.dueDate}">
                        <div class="book-info-full">
                            <div class="book-title">${issuedBook.bookTitle}</div>
                            <div class="book-meta">
                                <span>${issuedBook.author}</span>
                                <span>Issued: ${formatDate(issuedBook.issueDate)}</span>
                                <span>Due: ${formatDate(issuedBook.dueDate)}</span>
                            </div>
                            <div class="mt-2">
                                ${isOverdue ? 
                                    `<span class="status-badge status-overdue">Overdue by ${overdueDays} days</span>` :
                                    `<span class="status-badge status-ontime">On Time</span>`
                                }
                            </div>
                        </div>
                    `;

                    const checkbox = bookItem.querySelector('.book-checkbox');
                    const toggleBookSelection = () => {
                        if (!checkbox) {
                            return;
                        }

                        checkbox.checked = !checkbox.checked;
                        toggleIssuedBookSelection(issuedBook.id, checkbox.checked);
                    };

                    bookItem.setAttribute('tabindex', '0');
                    bookItem.setAttribute('role', 'checkbox');
                    bookItem.setAttribute('aria-checked', 'false');

                    bookItem.addEventListener('click', (event) => {
                        if (event.target === checkbox || event.target.closest('.book-checkbox')) {
                            return;
                        }

                        toggleBookSelection();
                    });

                    bookItem.addEventListener('keydown', (event) => {
                        if (event.key !== 'Enter' && event.key !== ' ') {
                            return;
                        }

                        if (event.target === checkbox || event.target.closest('.book-checkbox')) {
                            return;
                        }

                        event.preventDefault();
                        toggleBookSelection();
                    });

                    issuedBooksContainer.appendChild(bookItem);
                });

                syncIssuedBooksBulkState();
            }

            window.toggleIssuedBookSelection = function(bookId, isChecked) {
                const checkbox = document.getElementById(`book-${bookId}`);
                if (!checkbox) {
                    return;
                }

                const bookElement = checkbox?.closest('.book-checkbox-container');
                const overdueDays = parseInt(checkbox.dataset.overdueDays) || 0;

                if (bookElement) {
                    bookElement.classList.toggle('is-selected', isChecked);
                    bookElement.setAttribute('aria-checked', isChecked ? 'true' : 'false');
                }

                if (isChecked) {
                    // Get the book title from the DOM
                    const bookTitle = bookElement.querySelector('.book-title').textContent;

                    selectedIssuedBooks.push({
                        id: bookId,
                        bookId: bookId,
                        title: bookTitle,
                        overdueDays: overdueDays,
                        issueDate: checkbox.dataset.issueDate,
                        dueDate: checkbox.dataset.dueDate,
                        condition: null,
                        conditionFine: 0
                    });
                } else {
                    selectedIssuedBooks = selectedIssuedBooks.filter(b => b.id !== bookId);
                }

                // Show/hide condition section based on selection
                if (selectedIssuedBooks.length > 0) {
                    bookConditionSection.style.display = 'block';
                } else {
                    bookConditionSection.style.display = 'none';
                    selectedCondition = null;
                    window.resetConditionSelection();
                }

                renderReturnPrivilegeSummary();
                window.updateReturnButton();
                window.calculateTotalFine();
                syncIssuedBooksBulkState();
            };

            window.selectCondition = function(condition) {
                selectedCondition = condition;

                // Update UI
                document.querySelectorAll('.condition-option').forEach(option => {
                    option.classList.remove('selected');
                });
                document.querySelector(`.condition-option[data-condition="${condition}"]`).classList.add(
                    'selected');

                // Update selected condition for all books
                selectedIssuedBooks.forEach(book => {
                    book.condition = condition;
                    const fineValue = document.querySelector(
                        `.condition-option[data-condition="${condition}"]`).dataset.fine;
                    book.conditionFine = fineValue ? Number(fineValue.trim()) : 0;
                });

                document.getElementById('selectedCondition').value = condition;
                renderReturnPrivilegeSummary();
                window.updateReturnButton();
                window.calculateTotalFine();
            };

            window.resetConditionSelection = function() {
                document.querySelectorAll('.condition-option').forEach(option => {
                    option.classList.remove('selected');
                });
                document.getElementById('selectedCondition').value = '';
                renderReturnPrivilegeSummary();
            };

            window.calculateTotalFine = function() {
                let totalFine = 0;
                fineDetails.innerHTML = '';

                if (selectedIssuedBooks.length === 0 || !selectedCondition) {
                    setReturnTotalFineDisplay(
                        0,
                        'Select books and a return condition to preview the payable fine.'
                    );
                    finePolicySummary.textContent =
                        'Select at least one issued book and a return condition to preview the fine calculation.';
                    fineHelperText.textContent = 'Select books and condition to calculate total fine';
                    return;
                }

                // Use student-specific privileges if available, otherwise use global settings
                const rules = getReturnRules();
                const perDayFine = rules?.per_day_fine ?? fineSettings.per_day_fine ?? 5;
                const gracePeriod = rules?.grace_period_days ?? fineSettings.grace_period_days ?? 2;
                const maxFine = rules?.max_fine_amount ?? fineSettings.max_fine_amount ?? 500;
                const conditionFine = getConditionFine(selectedCondition);

                finePolicySummary.innerHTML = `
                    <strong>${selectedIssuedBooks.length}</strong> selected book${selectedIssuedBooks.length === 1 ? '' : 's'}
                    will be charged using <strong>${formatCurrency(perDayFine)}/day</strong> after
                    <strong>${gracePeriod}</strong> grace day${Number(gracePeriod) === 1 ? '' : 's'},
                    capped at <strong>${formatCurrency(maxFine)}</strong> per book, plus
                    <strong>${formatCurrency(conditionFine)}</strong> for the
                    <strong>${escapeHtml(toTitleCase(selectedCondition))}</strong> condition.
                `;
                fineHelperText.textContent =
                    'Each selected book is calculated separately using overdue days, grace period, late fine rate, fine cap, and the chosen condition penalty.';

                selectedIssuedBooks.forEach(book => {
                    // Calculate overdue fine with grace period deduction (matching backend logic)
                    let overdueFine = 0;
                    const chargeableDays = Math.max(0, book.overdueDays - gracePeriod);
                    const capped = book.overdueDays > gracePeriod && (chargeableDays * perDayFine) > maxFine;
                    if (book.overdueDays > gracePeriod) {
                        overdueFine = chargeableDays * perDayFine;
                        overdueFine = Math.min(overdueFine, maxFine); // Cap at max fine
                    }

                    const perBookConditionFine = book.conditionFine || 0;
                    const bookTotal = overdueFine + perBookConditionFine;
                    totalFine += bookTotal;

                    const fineItem = document.createElement('div');
                    fineItem.className = 'fine-breakdown-card';
                    fineItem.innerHTML = `
                        <div class="fine-breakdown-header">
                            <div class="fine-breakdown-title">${escapeHtml(book.title)}</div>
                            <div class="detail-value">${formatCurrency(bookTotal)}</div>
                        </div>
                        <div class="fine-breakdown-meta">
                            <span class="fine-chip">Issued ${escapeHtml(formatDate(book.issueDate))}</span>
                            <span class="fine-chip">Due ${escapeHtml(formatDate(book.dueDate))}</span>
                            <span class="fine-chip">${book.overdueDays > 0 ? `${book.overdueDays} overdue day${book.overdueDays === 1 ? '' : 's'}` : 'On time'}</span>
                        </div>
                        <div class="fine-breakdown-lines">
                            <div class="fine-breakdown-line">
                                <span>Overdue days</span>
                                <span>${book.overdueDays}</span>
                            </div>
                            <div class="fine-breakdown-line">
                                <span>Grace deduction</span>
                                <span>${gracePeriod} day${Number(gracePeriod) === 1 ? '' : 's'}</span>
                            </div>
                            <div class="fine-breakdown-line">
                                <span>Chargeable overdue days</span>
                                <span>${chargeableDays}</span>
                            </div>
                            <div class="fine-breakdown-line">
                                <span>Late fine</span>
                                <span>${chargeableDays > 0 ? `${chargeableDays} × ${formatCurrency(perDayFine)} = ${formatCurrency(overdueFine)}` : formatCurrency(0)}</span>
                            </div>
                            <div class="fine-breakdown-line">
                                <span>Condition fine (${escapeHtml(toTitleCase(book.condition))})</span>
                                <span>${formatCurrency(perBookConditionFine)}</span>
                            </div>
                            <div class="fine-breakdown-line">
                                <span>Book total</span>
                                <span>${formatCurrency(bookTotal)}</span>
                            </div>
                            ${capped ? `<div class="fine-breakdown-line"><span>Cap applied</span><span>Limited to ${formatCurrency(maxFine)}</span></div>` : ''}
                        </div>
                    `;
                    fineDetails.appendChild(fineItem);
                });

                setReturnTotalFineDisplay(
                    totalFine,
                    `${selectedIssuedBooks.length} selected book${selectedIssuedBooks.length === 1 ? '' : 's'} with ${toTitleCase(selectedCondition)} condition.`
                );
            };

            window.updateReturnButton = function() {
                if (selectedIssuedBooks.length > 0 && selectedCondition) {
                    returnButton.disabled = false;
                    returnButton.textContent =
                        `Process Return (${selectedIssuedBooks.length} book${selectedIssuedBooks.length !== 1 ? 's' : ''})`;
                } else {
                    returnButton.disabled = true;
                    returnButton.textContent = 'Process Return';
                }
            };

            window.clearReturnStudentSelection = function() {
                selectedReturnStudent = null;
                returnStudentPrivileges = null;
                selectedReturnStudentId.value = '';
                document.getElementById('clearReturnStudentBtn').style.display = 'none';
                returnStudentCard.style.display = 'none';
                issuedBooksSection.style.display = 'none';
                bookConditionSection.style.display = 'none';
                fineCalculationCard.style.display = 'none';
                if (issuedBooksToolbar) {
                    issuedBooksToolbar.hidden = true;
                }
                selectedIssuedBooks = [];
                selectedCondition = null;
                resetConditionSelection();
                updateReturnButton();
                setReturnTotalFineDisplay(
                    0,
                    'Select books and a return condition to preview the payable fine.'
                );
                fineDetails.innerHTML = '';
                resetReturnPrivilegeSummary();

                searchReturnStudentInput.value = '';
                returnStudentResults.style.display = 'none';
                syncIssuedBooksBulkState();
            };

            function resetTransactionPageState() {
                issueForm?.reset();
                returnForm?.reset();
                clearStudentSelection();
                clearReturnStudentSelection();
                studentResults.innerHTML = '';
                studentResults.style.display = 'none';
                bookResults.innerHTML = '';
                bookResults.style.display = 'none';
                returnStudentResults.innerHTML = '';
                returnStudentResults.style.display = 'none';
            }

            function getIssuedBookCheckboxes() {
                return Array.from(issuedBooksContainer.querySelectorAll('.book-checkbox'));
            }

            function syncIssuedBooksBulkState() {
                const checkboxes = getIssuedBookCheckboxes();
                const total = checkboxes.length;
                const selected = checkboxes.filter((checkbox) => checkbox.checked).length;

                if (issuedBooksToolbar) {
                    issuedBooksToolbar.hidden = total === 0;
                    issuedBooksToolbar.classList.toggle('is-active', selected > 0);
                }

                if (selectAllIssuedBooksCheckbox) {
                    selectAllIssuedBooksCheckbox.disabled = total === 0;
                    selectAllIssuedBooksCheckbox.checked = total > 0 && selected === total;
                    selectAllIssuedBooksCheckbox.indeterminate = selected > 0 && selected < total;
                }

                if (issuedBooksSelectionSummary) {
                    issuedBooksSelectionSummary.textContent = `${selected} of ${total} selected`;
                }
            }

            function toggleAllIssuedBooks(isChecked) {
                getIssuedBookCheckboxes().forEach((checkbox) => {
                    if (checkbox.checked === isChecked) {
                        return;
                    }

                    checkbox.checked = isChecked;
                    toggleIssuedBookSelection(Number(checkbox.dataset.bookId), isChecked);
                });

                syncIssuedBooksBulkState();
            }

            function setIssueSubmitting(isSubmitting) {
                if (!issueButton) {
                    return;
                }

                if (isSubmitting) {
                    issueButton.disabled = true;
                    issueButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Issuing...';
                    return;
                }

                updateIssueButton();
            }

            function setReturnSubmitting(isSubmitting) {
                if (!returnButton) {
                    return;
                }

                if (isSubmitting) {
                    returnButton.disabled = true;
                    returnButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                    return;
                }

                updateReturnButton();
            }

            function executeIssueTransaction({
                studentId,
                studentName,
                studentRollNo,
                bookIds,
                selectedCount,
                dueDatePreview,
                remainingAfterIssue,
                rulesSnapshot,
            }) {
                setIssueSubmitting(true);

                fetch('{{ route('admin.transactions.issue') }}', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: JSON.stringify({
                            student_id: studentId,
                            book_ids: bookIds,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showTransactionToast({
                                title: 'Books Issued',
                                message: `Issued ${selectedCount} book${selectedCount === 1 ? '' : 's'} to ${studentName}.`,
                                detail: `${studentRollNo || 'Student'} • Due ${dueDatePreview} • ${remainingAfterIssue} slot${remainingAfterIssue === 1 ? '' : 's'} left`,
                                icon: 'fas fa-book-open',
                            }, 'success');

                            issueForm.reset();
                            clearStudentSelection();
                            return;
                        }

                        const responseMessage = String(data.message || 'Unable to issue the selected books.');
                        const isPermissionError = responseMessage.includes('allowed to borrow') || responseMessage.includes('permission');
                        showTransactionToast({
                            title: isPermissionError ? 'Borrowing Permission Denied' : 'Issue Failed',
                            message: responseMessage,
                            detail: studentRollNo || '',
                            icon: isPermissionError ? 'fas fa-user-lock' : 'fas fa-book',
                        }, 'error');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showTransactionToast({
                            title: 'Transaction Error',
                            message: 'Failed to issue the selected books.',
                            detail: error.message,
                            icon: 'fas fa-exclamation-triangle',
                        }, 'error');
                    })
                    .finally(() => {
                        setIssueSubmitting(false);
                    });
            }

            function executeReturnTransaction({
                studentId,
                studentName,
                studentRollNo,
                issuedBookIds,
                selectedCount,
                condition,
                conditionLabel,
                totalFinePreview,
                rulesSnapshot,
            }) {
                setReturnSubmitting(true);

                fetch('{{ route('admin.transactions.return') }}', {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        },
                        body: JSON.stringify({
                            student_id: studentId,
                            issued_book_ids: issuedBookIds,
                            condition,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const totalFine = Number(data.total_fine ?? totalFinePreview ?? 0);
                            const hasFine = totalFine > 0;

                            showTransactionToast({
                                title: hasFine ? 'Return Processed' : 'Books Returned',
                                message: `Processed ${selectedCount} returned book${selectedCount === 1 ? '' : 's'} for ${studentName}.`,
                                detail: `${studentRollNo || 'Student'} • ${conditionLabel} • Fine ${formatCurrency(totalFine)}`,
                                icon: hasFine ? 'fas fa-coins' : 'fas fa-undo-alt',
                            }, hasFine ? 'warning' : 'success');

                            if (window.opener && !window.opener.closed) {
                                window.opener.postMessage({
                                    type: 'fines_updated',
                                    studentId
                                }, window.location.origin);
                            }

                            returnForm.reset();
                            clearReturnStudentSelection();
                            return;
                        }

                        showTransactionToast({
                            title: 'Return Failed',
                            message: String(data.message || 'Unable to process the selected returns.'),
                            detail: studentRollNo || '',
                            icon: 'fas fa-undo-alt',
                        }, 'error');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showTransactionToast({
                            title: 'Transaction Error',
                            message: 'Failed to process the selected returns.',
                            detail: error.message,
                            icon: 'fas fa-exclamation-triangle',
                        }, 'error');
                    })
                    .finally(() => {
                        setReturnSubmitting(false);
                    });
            }

            // ========== FORM SUBMISSION ==========

            // Issue Book Form Submit
            issueForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!selectedStudent || selectedBooks.length === 0) {
                    showCustomAlert(
                        'Missing Information',
                        'Please select a student and at least one book before issuing.',
                        'warning'
                    );
                    return;
                }

                // Verify student and privileges are loaded
                const rules = getIssueRules();
                if (!rules) {
                    showCustomAlert(
                        'Student Not Selected',
                        'Please select a student first and wait for their information to load.',
                        'error'
                    );
                    return;
                }

                // Check if borrowing is allowed (final verification)
                if (!rules.borrowing_allowed) {
                    showCustomAlert(
                        'Borrowing Permission Denied',
                        `This student (${selectedStudent.name}) does not have borrowing permission.\n\nPlease contact the administrator to enable borrowing for this student.`,
                        'error'
                    );
                    return;
                }

                // Check if student can issue more books (based on privilege)
                const canIssueMore = Math.max(0, rules.max_books - selectedStudent.issued);
                if (selectedBooks.length > canIssueMore) {
                    showCustomAlert(
                        'Exceeds Borrowing Limit',
                        `Cannot issue ${selectedBooks.length} books.\n\nStudent can only issue ${canIssueMore} more book(s) out of their ${rules.max_books} book limit.`,
                        'error'
                    );
                    return;
                }

                const selectedCount = selectedBooks.length;
                const bookIds = selectedBooks.map(book => book.id);
                const dueDatePreview = formatDate(addDaysFromToday(rules.issue_duration_days));
                const remainingAfterIssue = Math.max(
                    0,
                    Number(rules.max_books ?? 0) - Number(selectedStudent.issued ?? 0) - selectedCount
                );

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
                        rulesSnapshot: rules,
                    }),
                });
            });

            // Return Book Form Submit
            returnForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!selectedReturnStudent || selectedIssuedBooks.length === 0 || !selectedCondition) {
                    showCustomAlert(
                        'Incomplete Information',
                        `Please select:
• A student
• At least one book to return
• Book condition for each book`,
                        'warning'
                    );
                    return;
                }

                const issuedBookIds = selectedIssuedBooks.map(book => book.id);
                const selectedCount = selectedIssuedBooks.length;
                const conditionLabel = toTitleCase(selectedCondition);
                const totalFineLabel = document.getElementById('totalFine')?.textContent?.trim() || formatCurrency(0);
                const totalFinePreview = Number(totalFineLabel.replace(/[^0-9.]/g, '')) || 0;
                const confirmVariant = totalFinePreview > 0 || selectedCondition !== 'good' ? 'warning' : 'success';
                const confirmIconVariant = confirmVariant === 'warning' ? 'warning' : 'success';

                openTransactionConfirmModal({
                    title: `Process return for ${selectedReturnStudent.name}?`,
                    message: 'This will return the selected books and apply the chosen condition and fine rules.',
                    detail: `${selectedCount} book${selectedCount === 1 ? '' : 's'} • ${conditionLabel} • ${totalFineLabel}`,
                    icon: confirmVariant === 'warning' ? 'fas fa-coins' : 'fas fa-undo-alt',
                    iconVariant: confirmIconVariant,
                    confirmLabel: 'Process Return',
                    confirmIcon: confirmVariant === 'warning' ? 'fas fa-coins' : 'fas fa-undo-alt',
                    confirmVariant,
                    onConfirm: () => executeReturnTransaction({
                        studentId: selectedReturnStudent.id,
                        studentName: selectedReturnStudent.name,
                        studentRollNo: selectedReturnStudent.roll_no,
                        issuedBookIds,
                        selectedCount,
                        condition: selectedCondition,
                        conditionLabel,
                        totalFinePreview,
                        rulesSnapshot: getReturnRules(),
                    }),
                });
            });

            // Listen for fine update messages from other windows
            window.addEventListener('message', function(event) {
                if (event.origin !== window.location.origin) return;
                if (event.data.type === 'fines_updated' && event.data.studentId) {
                    // Reload fines if this page is viewing that student
                    if (typeof loadStudentFines === 'function') {
                        loadStudentFines();
                    }
                }
            });

            resetTransactionPageState();
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    resetTransactionPageState();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-container')) {
                    studentResults.style.display = 'none';
                    bookResults.style.display = 'none';
                    returnStudentResults.style.display = 'none';
                }
            });
        });
    </script>
@endpush
