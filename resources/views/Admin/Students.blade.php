@extends('Admin.layouts.app')

@section('title', 'Students')

@push('styles')
    @include('shared.action-feedback.styles')
    <style>
        /* ===== TABLE & PAGINATION STYLES (dual theme) ===== */
        .table-container {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid;
            margin-top: 0;
            transition: background-color 0.3s, border-color 0.3s;
        }

        /* Light theme table container */
        body.light-theme .table-container {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }
        /* Dark theme table container */
        body.dark-theme .table-container {
            background-color: #1e293b;
            border-color: #334155;
        }

        .table-wrapper {
            overflow-x: hidden;
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

        /* Light theme th */
        body.light-theme .students-table th {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
        }
        /* Dark theme th */
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

        /* Light theme td */
        body.light-theme .students-table td {
            border-color: #e2e8f0;
            color: #0f172a;
        }
        /* Dark theme td */
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

        /* Column width constraints */
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
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .students-table th:nth-child(7),
        .students-table td:nth-child(7) {
            width: 15%;
            max-width: 15%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Text muted (secondary info) */
        .text-muted {
            transition: color 0.3s;
        }
        body.light-theme .text-muted {
            color: #64748b;
        }
        body.dark-theme .text-muted {
            color: #94a3b8;
        }

        /* Student info styling */
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

        .student-email {
            font-size: 0.75rem;
            line-height: 1.4;
        }

        /* Status badges with Font Awesome icons */
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

        /* Action buttons */
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
            transition: background-color 0.3s, color 0.3s;
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

        /* ===== PAGINATION STYLES (dual theme) ===== */
        .pagination {
            display: flex;
            gap: 8px;
            list-style: none;
            padding: 0;
            margin: 0;
            justify-content: flex-end;
        }
        .pagination li a,
        .pagination li span {
            display: block;
            padding: 6px 12px;
            border: 1px solid;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        /* Light theme pagination */
        body.light-theme .pagination li a,
        body.light-theme .pagination li span {
            background-color: #ffffff;
            border-color: #e2e8f0;
            color: #3b82f6;
        }
        body.light-theme .pagination li a:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }
        body.light-theme .pagination li.active span {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        body.light-theme .pagination li.disabled span {
            color: #94a3b8;
            background-color: #f1f5f9;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        /* Dark theme pagination */
        body.dark-theme .pagination li a,
        body.dark-theme .pagination li span {
            background-color: #1e293b;
            border-color: #475569;
            color: #94a3b8;
        }
        body.dark-theme .pagination li a:hover {
            background-color: #334155;
            color: #e2e8f0;
        }
        body.dark-theme .pagination li.active span {
            background-color: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }
        body.dark-theme .pagination li.disabled span {
            color: #64748b;
            background-color: #0f172a;
            border-color: #334155;
            cursor: not-allowed;
        }

        #paginationContainer {
            padding: 16px 0 0;
        }

        /* ===== SEARCH & FILTER CONTAINER STYLES ===== */
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
        .search-input:focus {
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
        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

        @media (max-width: 768px) {
            .student-toolbar-meta {
                flex-direction: column;
                align-items: flex-start;
            }
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

        @keyframes studentTableShimmer {
            100% {
                transform: translateX(100%);
            }
        }

        /* Add Student Modal Theme Support */
        #addStudentModal {
            backdrop-filter: blur(4px);
        }

        #addStudentModal>div {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.light-theme #addStudentModal>div {
            background-color: #ffffff;
            color: #1f2937;
        }

        body.dark-theme #addStudentModal>div {
            background-color: #1e293b;
            color: #e5e7eb;
        }

        #addStudentModal h2 {
            transition: color 0.3s ease;
        }

        body.light-theme #addStudentModal h2 {
            color: #1f2937;
        }

        body.dark-theme #addStudentModal h2 {
            color: #e5e7eb;
        }

        #addStudentModal label {
            transition: color 0.3s ease;
        }

        body.light-theme #addStudentModal label {
            color: #374151;
        }

        body.dark-theme #addStudentModal label {
            color: #d1d5db;
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

        body.light-theme #addStudentModal input:focus,
        body.light-theme #addStudentModal textarea:focus,
        body.light-theme #addStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme #addStudentModal input:focus,
        body.dark-theme #addStudentModal textarea:focus,
        body.dark-theme #addStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        #addStudentModal button[type="button"],
        #addStudentModal button[type="submit"] {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.light-theme #addStudentModal button[type="button"] {
            background-color: white;
            color: #374151;
            border-color: #d1d5db;
        }

        body.light-theme #addStudentModal button[type="button"]:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme #addStudentModal button[type="button"] {
            background-color: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-theme #addStudentModal button[type="button"]:hover {
            background-color: #4b5563;
        }

        #addStudentModal button[onclick*="closeAddStudentModal"] {
            transition: color 0.3s ease;
        }

        body.light-theme #addStudentModal button[onclick*="closeAddStudentModal"] {
            color: #6b7280;
        }

        body.dark-theme #addStudentModal button[onclick*="closeAddStudentModal"] {
            color: #9ca3af;
        }

        #addStudentModal .error-message {
            transition: color 0.3s ease;
            display: none;
            color: #dc2626;
            font-size: 12px;
            font-weight: 500;
        }

        body.light-theme #addStudentModal .error-message {
            color: #dc2626;
        }

        body.dark-theme #addStudentModal .error-message {
            color: #fca5a5;
        }

        #addStudentModal .required-asterisk {
            color: #ef4444;
            font-weight: 600;
        }

        /* ===== Edit Student Modal Theme Support ===== */
        #editStudentModal {
            backdrop-filter: blur(4px);
        }

        #editStudentModal>div {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.light-theme #editStudentModal>div {
            background-color: #ffffff;
            color: #1f2937;
        }

        body.dark-theme #editStudentModal>div {
            background-color: #1e293b;
            color: #e5e7eb;
        }

        #editStudentModal h2 {
            transition: color 0.3s ease;
        }

        body.light-theme #editStudentModal h2 {
            color: #1f2937;
        }

        body.dark-theme #editStudentModal h2 {
            color: #e5e7eb;
        }

        #editStudentModal label {
            transition: color 0.3s ease;
        }

        body.light-theme #editStudentModal label {
            color: #374151;
        }

        body.dark-theme #editStudentModal label {
            color: #d1d5db;
        }

        #editStudentModal input,
        #editStudentModal textarea,
        #editStudentModal select {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.light-theme #editStudentModal input,
        body.light-theme #editStudentModal textarea,
        body.light-theme #editStudentModal select {
            background-color: #ffffff;
            color: #1f2937;
            border-color: #d1d5db;
        }

        body.dark-theme #editStudentModal input,
        body.dark-theme #editStudentModal textarea,
        body.dark-theme #editStudentModal select {
            background-color: #0f172a;
            color: #e5e7eb;
            border-color: #334155;
        }

        body.light-theme #editStudentModal input:focus,
        body.light-theme #editStudentModal textarea:focus,
        body.light-theme #editStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme #editStudentModal input:focus,
        body.dark-theme #editStudentModal textarea:focus,
        body.dark-theme #editStudentModal select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        #editStudentModal button[type="button"],
        #editStudentModal button[type="submit"] {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        body.light-theme #editStudentModal button[type="button"] {
            background-color: white;
            color: #374151;
            border-color: #d1d5db;
        }

        body.light-theme #editStudentModal button[type="button"]:hover {
            background-color: #f3f4f6;
        }

        body.dark-theme #editStudentModal button[type="button"] {
            background-color: #374151;
            color: #e5e7eb;
            border-color: #4b5563;
        }

        body.dark-theme #editStudentModal button[type="button"]:hover {
            background-color: #4b5563;
        }

        #editStudentModal button[onclick*="closeEditStudentModal"] {
            transition: color 0.3s ease;
        }

        body.light-theme #editStudentModal button[onclick*="closeEditStudentModal"] {
            color: #6b7280;
        }

        body.dark-theme #editStudentModal button[onclick*="closeEditStudentModal"] {
            color: #9ca3af;
        }

        #editStudentModal .error-message {
            transition: color 0.3s ease;
            display: none;
            color: #dc2626;
            font-size: 12px;
            font-weight: 500;
        }

        /* Explicitly show error messages when .show class is applied */
        #editStudentModal .error-message.show {
            display: block !important;
            visibility: visible !important;
        }

        body.light-theme #editStudentModal .error-message {
            color: #dc2626;
        }

        body.dark-theme #editStudentModal .error-message {
            color: #fca5a5;
        }

        #editStudentModal .required-asterisk {
            color: #ef4444;
            font-weight: 600;
        }

        .student-form-field {
            position: relative;
        }

        .student-form-control {
            padding-right: 36px !important;
        }

        .student-form-field .error-message {
            display: none;
            margin-top: 6px;
            line-height: 1.4;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            animation: slideDown 0.2s ease;
        }

        .field-validation-icon {
            position: absolute;
            right: 12px;
            top: 36px;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease, color 0.2s ease;
        }

        .student-form-field.has-valid .field-validation-icon,
        .student-form-field.has-invalid .field-validation-icon,
        .student-form-field.has-pending .field-validation-icon {
            opacity: 1;
        }

        .student-form-field.has-valid .field-validation-icon {
            color: #16a34a;
        }

        .student-form-field.has-invalid .field-validation-icon {
            color: #dc2626;
        }

        .student-form-field.has-pending .field-validation-icon {
            color: #2563eb;
        }

        .student-form-field.has-invalid .error-message {
            display: flex;
        }

        body.light-theme .student-form-field.has-valid .student-form-control {
            border-color: #16a34a !important;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
        }

        body.light-theme .student-form-field.has-invalid .student-form-control {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }

        body.light-theme .student-form-field.has-pending .student-form-control {
            border-color: #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        body.dark-theme .student-form-field.has-valid .student-form-control {
            border-color: #22c55e !important;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
        }

        body.dark-theme .student-form-field.has-invalid .student-form-control {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.18);
        }

        body.dark-theme .student-form-field.has-pending .student-form-control {
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
        }

        .form-validation-summary {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
        }

        body.light-theme .form-validation-summary {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        body.dark-theme .form-validation-summary {
            background: rgba(127, 29, 29, 0.35);
            border-color: #7f1d1d;
            color: #fecaca;
        }

        .form-validation-summary i {
            margin-right: 6px;
        }

        .student-submit-btn[data-submitting="true"] {
            opacity: 0.92;
            cursor: progress !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Keyboard navigation styling */
        .student-row:focus {
            outline: 2px solid #3b82f6;
            outline-offset: -2px;
        }

        .student-row.selected {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        body.dark-theme .student-row.selected {
            background-color: rgba(59, 130, 246, 0.2) !important;
        }

        /* Focus styles for interactive elements */
        .action-btn:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        #searchInput:focus,
        .filter-select:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        .student-visually-hidden {
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

        .student-toast-container {
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

        .student-toast {
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
            animation: studentToastIn 0.24s ease;
        }

        .student-toast.is-leaving {
            animation: studentToastOut 0.18s ease forwards;
        }

        .student-toast.success {
            background: linear-gradient(135deg, rgba(22, 163, 74, 0.96), rgba(5, 150, 105, 0.94));
        }

        .student-toast.error {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.97), rgba(190, 24, 93, 0.94));
        }

        .student-toast.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.97), rgba(217, 119, 6, 0.94));
        }

        .student-toast.info {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.97), rgba(79, 70, 229, 0.94));
        }

        .student-toast-icon {
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

        .student-toast-copy {
            min-width: 0;
        }

        .student-toast-title {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.3;
        }

        .student-toast-message {
            margin-top: 2px;
            font-size: 13px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.96);
        }

        .student-toast-detail {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.4;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.78);
        }

        .student-toast-close {
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

        .student-toast-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
        }

        .student-toast-progress {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.18);
        }

        .student-toast-progress::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.92);
            transform-origin: left center;
            animation: studentToastProgress 4.2s linear forwards;
        }

        .student-confirm-modal {
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

        .student-confirm-card {
            width: min(440px, 100%);
            padding: 24px;
            border-radius: 24px;
            border: 1px solid;
            box-shadow: 0 28px 60px rgba(15, 23, 42, 0.26);
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }

        body.light-theme .student-confirm-card {
            background: rgba(255, 255, 255, 0.97);
            border-color: rgba(226, 232, 240, 0.95);
            color: #0f172a;
        }

        body.dark-theme .student-confirm-card {
            background: rgba(15, 23, 42, 0.96);
            border-color: rgba(71, 85, 105, 0.88);
            color: #e2e8f0;
        }

        .student-confirm-header {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .student-confirm-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 22px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #b91c1c;
        }

        body.dark-theme .student-confirm-icon {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.85), rgba(127, 29, 29, 0.55));
            color: #fca5a5;
        }

        .student-confirm-title {
            margin: 2px 0 6px;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.3;
        }

        .student-confirm-copy p {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }

        body.light-theme .student-confirm-copy p {
            color: #475569;
        }

        body.dark-theme .student-confirm-copy p {
            color: #94a3b8;
        }

        .student-confirm-detail {
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        body.light-theme .student-confirm-detail {
            background: #f8fafc;
            color: #475569;
        }

        body.dark-theme .student-confirm-detail {
            background: rgba(30, 41, 59, 0.85);
            color: #cbd5e1;
        }

        .student-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 22px;
        }

        .student-confirm-btn {
            appearance: none;
            border: 1px solid transparent;
            border-radius: 12px;
            min-width: 112px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .student-confirm-btn:hover {
            transform: translateY(-1px);
        }

        .student-confirm-btn.secondary {
            background: transparent;
        }

        body.light-theme .student-confirm-btn.secondary {
            border-color: #cbd5e1;
            color: #334155;
        }

        body.dark-theme .student-confirm-btn.secondary {
            border-color: #475569;
            color: #e2e8f0;
        }

        .student-confirm-btn.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            box-shadow: 0 12px 24px rgba(220, 38, 38, 0.24);
        }

        .student-confirm-btn:disabled {
            opacity: 0.7;
            cursor: wait;
            transform: none;
        }

        @keyframes studentToastIn {
            from {
                opacity: 0;
                transform: translate3d(0, -10px, 0) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
        }

        @keyframes studentToastOut {
            from {
                opacity: 1;
                transform: translate3d(0, 0, 0) scale(1);
            }
            to {
                opacity: 0;
                transform: translate3d(0, -8px, 0) scale(0.98);
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

        @media (max-width: 640px) {
            .student-toast-container {
                top: auto;
                right: 12px;
                bottom: 16px;
                left: 12px;
                width: auto;
            }

            .student-confirm-card {
                padding: 20px;
                border-radius: 20px;
            }

            .student-confirm-actions {
                flex-direction: column-reverse;
            }

            .student-confirm-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="">
        <!-- Header -->
        <div class="mb-3">
            <h1 class="text-xl font-bold text-primary">Students</h1>
            <p class="mt-0.5 text-xs text-secondary">Manage student records</p>
        </div>
        <div class="grid grid-cols-1 gap-3 mb-3 md:grid-cols-3">
            <div class="p-3 card student-stat-card is-loading" data-stat-card="total">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Total Students</p>
                        <div class="student-stat-copy">
                            <h3 class="mt-1 text-2xl font-bold text-primary" id="totalCount">0</h3>
                            <p class="mt-1 text-xs text-secondary" id="totalMeta">Live overview across all departments and batches</p>
                        </div>
                        <div class="student-stat-loading-lines" aria-hidden="true">
                            <span class="student-stat-loading-line short"></span>
                            <span class="student-stat-loading-line long"></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg dark:bg-blue-900">
                        <i data-lucide="users" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
            </div>

            <div class="p-3 card student-stat-card is-loading" data-stat-card="active">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Active</p>
                        <div class="student-stat-copy">
                            <h3 class="mt-1 text-2xl font-bold text-primary" id="activeCount">0</h3>
                            <p class="mt-1 text-xs text-secondary" id="activeMeta">Student accounts currently ready to borrow books</p>
                        </div>
                        <div class="student-stat-loading-lines" aria-hidden="true">
                            <span class="student-stat-loading-line short"></span>
                            <span class="student-stat-loading-line long"></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 rounded-lg dark:bg-green-900">
                        <i data-lucide="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
            </div>

            <div class="p-3 card student-stat-card is-loading" data-stat-card="inactive">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-secondary">Inactive</p>
                        <div class="student-stat-copy">
                            <h3 class="mt-1 text-2xl font-bold text-primary" id="inactiveCount">0</h3>
                            <p class="mt-1 text-xs text-secondary" id="inactiveMeta">Accounts currently paused from circulation activity</p>
                        </div>
                        <div class="student-stat-loading-lines" aria-hidden="true">
                            <span class="student-stat-loading-line short"></span>
                            <span class="student-stat-loading-line long"></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-red-100 rounded-lg dark:bg-red-900">
                        <i data-lucide="x-circle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Students Section -->
        <div class="mb-2">
            <h2 class="text-base font-semibold text-primary">All Students</h2>
        </div>

        <!-- Search & Filter Container -->
        <div class="search-filter-container">
            <div class="search-box">
                <div class="search-icon">
                    <i class="fas fa-search"></i>
                </div>
                <input type="text" class="search-input" id="searchInput" placeholder="Search by name, email, or roll number..." autocomplete="off" value="{{ request('search', '') }}">
            </div>

            <div class="filters-container">
                <select class="filter-select" id="statusFilter">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <select class="filter-select" id="departmentFilter">
                    <option value="all" {{ request('department', 'all') === 'all' ? 'selected' : '' }}>All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (string) request('department') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>

                <select class="filter-select" id="sortFilter">
                    <option value="created-desc" {{ request('sort', 'created-desc') === 'created-desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="created-asc" {{ request('sort') === 'created-asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="name-asc" {{ request('sort') === 'name-asc' ? 'selected' : '' }}>Alphabetical A-Z</option>
                    <option value="name-desc" {{ request('sort') === 'name-desc' ? 'selected' : '' }}>Alphabetical Z-A</option>
                </select>

                <button id="resetFiltersBtn" class="reset-btn" title="Reset all filters">
                    <i class="fas fa-redo"></i>
                    Reset
                </button>
            </div>

            <label class="admin-table-entries-control" for="studentsEntriesSelect">
                <span>Show</span>
                <select class="admin-table-entries-select" id="studentsEntriesSelect" aria-label="Show student entries">
                    @foreach ([10, 20, 50, 100] as $entryCount)
                        <option value="{{ $entryCount }}" {{ (int) request('per_page', 10) === $entryCount ? 'selected' : '' }}>{{ $entryCount }}</option>
                    @endforeach
                </select>
                <span>entries</span>
            </label>

            <button id="addStudentBtn" class="flex items-center gap-2 px-3 py-1 font-medium text-white transition-all bg-blue-600 rounded-lg hover:bg-blue-700 hover:shadow-lg" style="margin-left: auto;">
                <i class="fas fa-plus"></i>
                Add Student
            </button>
        </div>

        <div class="student-toolbar-meta">
            <div id="studentFilterSummary">Sort: Newest first • <strong>0</strong> matching students</div>
            <div id="studentLastUpdated">Waiting for data...</div>
        </div>

        <!-- Students Table -->
        <div class="table-container" id="studentTableShell">
            <div class="table-wrapper" id="studentTableWrapper" aria-busy="true">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Batch</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody" class="student-table-loading">
                        @for ($i = 0; $i < 5; $i++)
                            <tr>
                                <td><span class="student-skeleton-line long"></span></td>
                                <td><span class="student-skeleton-line medium"></span></td>
                                <td><span class="student-skeleton-line short"></span></td>
                                <td><span class="student-skeleton-line medium"></span></td>
                                <td><span class="student-skeleton-line short"></span></td>
                                <td><span class="student-skeleton-line short"></span></td>
                                <td><span class="student-skeleton-line long"></span></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="paginationContainer" style="padding: 16px;">
                <!-- Pagination links will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div
            style="border-radius: 8px; padding: 16px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Add New Student</h2>
                <button onclick="closeAddStudentModal()"
                    style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
            </div>

            <form action="{{ route('admin.students.store') }}" method="POST" id="addStudentForm" novalidate>
                @csrf

                <div id="addStudentValidationSummary" class="form-validation-summary" role="alert" aria-live="assertive" tabindex="-1" hidden></div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_name" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Full Name <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="modal_name" name="name" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter student's full name">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_email" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Email <span
                            class="required-asterisk">*</span></label>
                    <input type="email" id="modal_email" name="email" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="student@example.com">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_phone" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Phone <span
                            class="required-asterisk">*</span></label>
                    <input type="tel" id="modal_phone" name="phone" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="+9779812345678">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_gender" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Gender</label>
                    <select id="modal_gender" name="gender" class="student-form-control"
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_date_of_birth" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Date of Birth <span
                            class="required-asterisk">*</span></label>
                    <input type="date" id="modal_date_of_birth" name="date_of_birth" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_roll_no" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Student ID
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="modal_roll_no" name="roll_no" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter student ID">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_department_id"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Department <span
                            class="required-asterisk">*</span></label>
                    <select id="modal_department_id" name="department_id" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select a department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_batch" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Batch <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="modal_batch" name="batch" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 2024">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="modal_semester" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Semester
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="modal_semester" name="semester" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 1">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 16px;">
                    <label for="modal_address"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Address <span class="required-asterisk">*</span></label>
                    <textarea id="modal_address" name="address" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box; min-height: 80px;"
                        placeholder="Enter student's address"></textarea>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closeAddStudentModal()"
                        style="padding: 8px 16px; border: 1px solid; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Cancel
                    </button>
                    <button type="submit" class="student-submit-btn"
                        style="padding: 8px 16px; border: none; border-radius: 6px; background: #3b82f6; color: white; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div id="editStudentModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div
            style="border-radius: 8px; padding: 16px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h2 style="margin: 0; font-size: 18px; font-weight: 700;">Edit Student</h2>
                <button onclick="closeEditStudentModal()"
                    style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
            </div>

            <form id="editStudentForm" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div id="editStudentValidationSummary" class="form-validation-summary" role="alert" aria-live="assertive" tabindex="-1" hidden></div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_name" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Full Name <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="edit_name" name="name" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter student's full name">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_email" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Email <span
                            class="required-asterisk">*</span></label>
                    <input type="email" id="edit_email" name="email" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="student@example.com">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_phone" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Phone <span
                            class="required-asterisk">*</span></label>
                    <input type="tel" id="edit_phone" name="phone" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="+9779812345678">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_gender" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Gender</label>
                    <select id="edit_gender" name="gender" class="student-form-control"
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_date_of_birth" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Date of Birth <span
                            class="required-asterisk">*</span></label>
                    <input type="date" id="edit_date_of_birth" name="date_of_birth" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_roll_no" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Student ID
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="edit_roll_no" name="roll_no" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="Enter student ID">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_department_id"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Department <span
                            class="required-asterisk">*</span></label>
                    <select id="edit_department_id" name="department_id" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select a department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_batch" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Batch <span
                            class="required-asterisk">*</span></label>
                    <input type="text" id="edit_batch" name="batch" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 2024">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_semester" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Semester
                        <span class="required-asterisk">*</span></label>
                    <input type="text" id="edit_semester" name="semester" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;"
                        placeholder="e.g., 1">
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 16px;">
                    <label for="edit_address"
                        style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Address <span class="required-asterisk">*</span></label>
                    <textarea id="edit_address" name="address" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box; min-height: 80px;"
                        placeholder="Enter student's address"></textarea>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div class="student-form-field" style="margin-bottom: 12px;">
                    <label for="edit_status" style="display: block; margin-bottom: 4px; font-weight: 500; font-size: 13px;">Status <span
                            class="required-asterisk">*</span></label>
                    <select id="edit_status" name="status" class="student-form-control" required
                        style="width: 100%; padding: 8px 10px; border: 1px solid; border-radius: 6px; font-size: 13px; box-sizing: border-box;">
                        <option value="">Select Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <span class="error-message" style="font-size: 12px; display: none;"></span>
                </div>

                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button type="button" onclick="closeEditStudentModal()"
                        style="padding: 8px 16px; border: 1px solid; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Cancel
                    </button>
                    <button type="submit" class="student-submit-btn"
                        style="padding: 8px 16px; border: none; border-radius: 6px; background: #3b82f6; color: white; font-weight: 500; cursor: pointer; font-size: 13px;">
                        Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteStudentModal" class="student-confirm-modal" aria-hidden="true">
        <div class="student-confirm-card" role="dialog" aria-modal="true" aria-labelledby="deleteStudentModalTitle">
            <div class="student-confirm-header">
                <div class="student-confirm-icon" aria-hidden="true">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div class="student-confirm-copy">
                    <h2 id="deleteStudentModalTitle" class="student-confirm-title">Delete Student</h2>
                    <p id="deleteStudentModalMessage">Are you sure you want to delete this student? This action cannot be undone.</p>
                    <div id="deleteStudentModalDetail" class="student-confirm-detail">Student record</div>
                </div>
            </div>

            <div class="student-confirm-actions">
                <button type="button" id="deleteStudentCancelBtn" class="student-confirm-btn secondary">Cancel</button>
                <button type="button" id="deleteStudentConfirmBtn" class="student-confirm-btn danger">Delete Student</button>
            </div>
        </div>
    </div>

    @include('shared.action-feedback.markup', [
        'actionFeedbackConfig' => [
            'confirm' => [
                'modalId' => 'studentPasswordResetConfirmModal',
                'iconId' => 'studentPasswordResetConfirmIcon',
                'titleId' => 'studentPasswordResetConfirmTitle',
                'messageId' => 'studentPasswordResetConfirmMessage',
                'detailId' => 'studentPasswordResetConfirmDetail',
                'submitButtonId' => 'studentPasswordResetConfirmSubmitBtn',
                'cancelLabel' => 'Cancel',
                'confirmLabel' => 'Reset Student Password',
                'defaultTitle' => 'Reset Password',
                'defaultMessage' => 'Are you sure you want to reset this student password?',
            ],
            'toast' => [
                'containerId' => 'studentActionToastContainer',
                'liveRegionId' => 'studentActionLiveRegion',
            ],
        ],
    ])

    <div id="studentToastContainer" class="student-toast-container" aria-live="polite" aria-atomic="true"></div>
    <div id="studentLiveRegion" class="student-visually-hidden" aria-live="polite" aria-atomic="true"></div>

@endsection

@push('scripts')
    @include('shared.action-feedback.scripts')
    <script>
        class StudentManager {
            constructor() {
                const searchParams = new URLSearchParams(window.location.search);
                this.currentDepartment = searchParams.get('department') || 'all';
                this.currentStatus = searchParams.get('status') || 'all';
                this.currentSort = searchParams.get('sort') || 'created-desc';
                this.searchDebounceTimer = null;
                this.selectedRowIndex = -1;
                this.currentPage = Math.max(1, Number(searchParams.get('page')) || 1);
                this.totalRows = 0;
                this.rowsPerPage = this.normalizePerPage(searchParams.get('per_page'));
                this.lastUpdatedAt = null;
                this.statCards = document.querySelectorAll('[data-stat-card]');
                this.init();
            }

            init() {
                // Search and filter
                const searchInput = document.getElementById('searchInput');
                const departmentFilter = document.getElementById('departmentFilter');
                const statusFilter = document.getElementById('statusFilter');
                const sortFilter = document.getElementById('sortFilter');
                const entriesSelect = document.getElementById('studentsEntriesSelect');
                const resetBtn = document.getElementById('resetFiltersBtn');

                if (searchInput) {
                    searchInput.value = new URLSearchParams(window.location.search).get('search') || '';
                }
                if (departmentFilter) {
                    departmentFilter.value = this.currentDepartment;
                }
                if (statusFilter) {
                    statusFilter.value = this.currentStatus;
                }
                if (sortFilter) {
                    sortFilter.value = this.currentSort;
                }
                if (entriesSelect) {
                    entriesSelect.value = String(this.rowsPerPage);
                }

                if (searchInput) {
                    searchInput.addEventListener('input', (e) => {
                        clearTimeout(this.searchDebounceTimer);
                        this.searchDebounceTimer = setTimeout(() => {
                            this.selectedRowIndex = -1;
                            this.currentPage = 1;
                            this.reloadStudents();
                        }, 300);
                    });
                }

                if (departmentFilter) {
                    departmentFilter.addEventListener('change', (e) => {
                        this.currentDepartment = e.target.value;
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        this.reloadStudents();
                    });
                }

                if (statusFilter) {
                    statusFilter.addEventListener('change', (e) => {
                        this.currentStatus = e.target.value;
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        this.reloadStudents();
                    });
                }

                if (sortFilter) {
                    sortFilter.addEventListener('change', (e) => {
                        this.currentSort = e.target.value;
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        this.reloadStudents();
                    });
                }

                if (entriesSelect) {
                    entriesSelect.addEventListener('change', (e) => {
                        this.rowsPerPage = this.normalizePerPage(e.target.value);
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        this.reloadStudents();
                    });
                }

                if (resetBtn) {
                    resetBtn.addEventListener('click', () => {
                        this.resetFilters();
                    });
                }

                // Add student button
                const addBtn = document.getElementById('addStudentBtn');
                if (addBtn) {
                    addBtn.addEventListener('click', () => {
                        openAddStudentModal();
                    });
                }

                // Keyboard navigation
                document.addEventListener('keydown', (e) => this.handleKeyboardNavigation(e));

                // Setup keyboard shortcuts
                this.setupKeyboardShortcuts();

                // Load initial data
                this.reloadStudents();
            }

            setupKeyboardShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // Ctrl+K or Cmd+K to focus search
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        document.getElementById('searchInput').focus();
                    }
                    // Escape in search input to clear it
                    else if (e.key === 'Escape' && document.activeElement.id === 'searchInput') {
                        if (document.getElementById('searchInput').value !== '') {
                            document.getElementById('searchInput').value = '';
                            this.currentPage = 1;
                            this.reloadStudents();
                        } else {
                            document.getElementById('searchInput').blur();
                        }
                    }
                });
            }

            resetFilters() {
                document.getElementById('searchInput').value = '';
                document.getElementById('statusFilter').value = 'all';
                document.getElementById('departmentFilter').value = 'all';
                document.getElementById('sortFilter').value = 'created-desc';
                this.currentStatus = 'all';
                this.currentDepartment = 'all';
                this.currentSort = 'created-desc';
                this.currentPage = 1;
                this.reloadStudents();
            }

            fetchStudents(page = 1) {
                this.currentPage = page;
                const { search, department, status, sort } = this.getCurrentFilters();
                this.currentDepartment = department;
                this.currentStatus = status;
                this.currentSort = sort;
                this.setTableLoading(true);

                fetch(`{{ route('admin.students.data') }}?search=${encodeURIComponent(search)}&department=${department}&status=${status}&sort=${sort}&page=${page}&per_page=${this.rowsPerPage}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            this.lastUpdatedAt = new Date();
                            if (Number(page) > Number(data.last_page || 1)) {
                                this.fetchStudents(data.last_page || 1);
                                return;
                            }

                            this.currentPage = Number(data.current_page || page) || 1;
                            if (data.total === 0) {
                                document.getElementById('studentsTableBody').innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block;"></i>
                                <p style="font-size: 16px; margin: 0; font-weight: 500;">No students found</p>
                                <p style="font-size: 14px; margin-top: 8px; color: #9ca3af;">Try adjusting your search or filters</p>
                            </td>
                        </tr>
                    `;
                                document.getElementById('paginationContainer').innerHTML = '';
                                this.totalRows = 0;
                            } else {
                                document.getElementById('studentsTableBody').innerHTML = data.tableRows;
                                document.getElementById('paginationContainer').innerHTML = data.pagination;
                                this.totalRows = data.total;
                                this.attachEventListeners();
                                this.updateRowAccessibility();
                            }
                            this.syncUrlState(search);
                            this.renderToolbarMeta();
                        } else {
                            console.error('Error loading students');
                            this.renderTableError();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching students:', error);
                        this.renderTableError();
                    })
                    .finally(() => {
                        this.setTableLoading(false);
                    });
            }

            attachEventListeners() {
                // Re-initialize Lucide icons
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }

                // Attach pagination click handlers
                document.querySelectorAll('#paginationContainer a').forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        this.selectedRowIndex = -1;
                        this.fetchStudents(page);
                    });
                });

                // Add click handlers to table rows
                const rows = document.querySelectorAll('#studentsTableBody tr');
                rows.forEach((row, index) => {
                    row.setAttribute('tabindex', '0');
                    row.classList.add('student-row');
                    row.addEventListener('click', (e) => {
                        if (e.target.closest('.action-btn'))
                            return; // Don't select if clicking action buttons
                        this.selectRow(index);
                        row.focus();
                    });

                    // Allow Enter/Space to trigger view action
                    row.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.selectRow(index);
                            const viewBtn = row.querySelector('.btn-view');
                            if (viewBtn) viewBtn.click();
                        }
                    });
                });
            }

            updateRowAccessibility() {
                const rows = document.querySelectorAll('#studentsTableBody tr');
                rows.forEach((row, index) => {
                    row.setAttribute('role', 'row');
                    row.setAttribute('aria-label', `Student row ${index + 1}`);
                    row.setAttribute('tabindex', '0');
                });
            }

            selectRow(index) {
                // Remove selection from all rows
                document.querySelectorAll('#studentsTableBody tr').forEach(row => {
                    row.classList.remove('selected');
                    row.removeAttribute('aria-selected');
                });

                // Select new row
                const rows = document.querySelectorAll('#studentsTableBody tr');
                if (rows[index]) {
                    rows[index].classList.add('selected');
                    rows[index].setAttribute('aria-selected', 'true');
                    this.selectedRowIndex = index;
                }
            }

            handleKeyboardNavigation(e) {
                const rows = document.querySelectorAll('#studentsTableBody tr');
                if (rows.length === 0) return;

                // Don't interfere with form inputs
                if (e.target.tagName === 'INPUT' ||
                    e.target.tagName === 'SELECT' ||
                    e.target.tagName === 'TEXTAREA') {
                    return;
                }

                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        if (this.selectedRowIndex < rows.length - 1) {
                            this.selectedRowIndex++;
                            this.selectRow(this.selectedRowIndex);
                            rows[this.selectedRowIndex].focus();
                        }
                        break;

                    case 'ArrowUp':
                        e.preventDefault();
                        if (this.selectedRowIndex > 0) {
                            this.selectedRowIndex--;
                            this.selectRow(this.selectedRowIndex);
                            rows[this.selectedRowIndex].focus();
                        } else if (this.selectedRowIndex === -1 && rows.length > 0) {
                            this.selectedRowIndex = 0;
                            this.selectRow(this.selectedRowIndex);
                            rows[this.selectedRowIndex].focus();
                        }
                        break;

                    case 'Home':
                        e.preventDefault();
                        this.selectedRowIndex = 0;
                        this.selectRow(this.selectedRowIndex);
                        rows[this.selectedRowIndex].focus();
                        break;

                    case 'End':
                        e.preventDefault();
                        this.selectedRowIndex = rows.length - 1;
                        this.selectRow(this.selectedRowIndex);
                        rows[this.selectedRowIndex].focus();
                        break;

                    case 'Escape':
                        this.selectedRowIndex = -1;
                        document.querySelectorAll('#studentsTableBody tr').forEach(row => {
                            row.classList.remove('selected');
                            row.removeAttribute('aria-selected');
                        });
                        break;

                    case '/':
                        if (!e.ctrlKey && !e.metaKey) {
                            e.preventDefault();
                            const searchInput = document.getElementById('searchInput');
                            if (searchInput) {
                                searchInput.focus();
                                searchInput.select();
                            }
                        }
                        break;

                    case 'n':
                    case 'N':
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            openAddStudentModal();
                        }
                        break;

                    case 'Delete':
                        if (this.selectedRowIndex >= 0) {
                            const row = rows[this.selectedRowIndex];
                            const studentId = row.dataset.studentId;
                            if (studentId && confirm('Delete this student?')) {
                                deleteStudent(studentId);
                            }
                        }
                        break;

                    case 'Enter':
                        if (this.selectedRowIndex >= 0 && !e.shiftKey) {
                            const row = rows[this.selectedRowIndex];
                            const viewBtn = row.querySelector('.btn-view');
                            if (viewBtn) {
                                e.preventDefault();
                                viewBtn.click();
                            }
                        }
                        break;
                }
            }

            refreshStats() {
                const filters = this.getCurrentFilters();
                const params = new URLSearchParams(filters);
                this.setStatsLoading(true);

                fetch(`{{ route('admin.students.stats') }}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.renderStats(data, filters);
                    })
                    .catch(error => console.error('Error fetching stats:', error))
                    .finally(() => {
                        this.setStatsLoading(false);
                    });
            }

            reloadStudents(page = 1) {
                this.fetchStudents(page);
                this.refreshStats();
            }

            getCurrentFilters() {
                return {
                    search: document.getElementById('searchInput')?.value.trim() || '',
                    department: document.getElementById('departmentFilter')?.value || this.currentDepartment,
                    status: document.getElementById('statusFilter')?.value || this.currentStatus,
                    sort: document.getElementById('sortFilter')?.value || this.currentSort,
                };
            }

            hasActiveFilters(filters = this.getCurrentFilters()) {
                return filters.search !== '' || filters.department !== 'all' || filters.status !== 'all';
            }

            formatNumber(value) {
                return new Intl.NumberFormat().format(Number(value || 0));
            }

            formatTimeRelative(date) {
                const seconds = Math.max(0, Math.floor((Date.now() - date.getTime()) / 1000));

                if (seconds < 5) return 'just now';
                if (seconds < 60) return `${seconds}s ago`;

                const minutes = Math.floor(seconds / 60);
                if (minutes < 60) return `${minutes}m ago`;

                const hours = Math.floor(minutes / 60);
                if (hours < 24) return `${hours}h ago`;

                return `${Math.floor(hours / 24)}d ago`;
            }

            getDepartmentLabel() {
                return document.getElementById('departmentFilter')?.selectedOptions?.[0]?.textContent?.trim() || 'Selected department';
            }

            getSortLabel(sort) {
                const labels = {
                    'created-desc': 'Newest first',
                    'created-asc': 'Oldest first',
                    'name-asc': 'Alphabetical A-Z',
                    'name-desc': 'Alphabetical Z-A',
                };

                return labels[sort] || 'Newest first';
            }

            capitalize(value) {
                const input = String(value || '');
                return input ? input.charAt(0).toUpperCase() + input.slice(1) : '';
            }

            escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            renderToolbarMeta() {
                const segments = [];
                const total = Number(this.totalRows || 0);
                const search = document.getElementById('searchInput')?.value.trim() || '';

                if (search) {
                    segments.push(`Search: "${search}"`);
                }

                if (this.currentStatus !== 'all') {
                    segments.push(`Status: ${this.capitalize(this.currentStatus)}`);
                }

                if (this.currentDepartment !== 'all') {
                    segments.push(`Department: ${this.getDepartmentLabel()}`);
                }

                segments.push(`Sort: ${this.getSortLabel(this.currentSort)}`);

                const filterSummary = document.getElementById('studentFilterSummary');
                const lastUpdated = document.getElementById('studentLastUpdated');

                if (filterSummary) {
                    filterSummary.innerHTML = `${this.escapeHtml(segments.join(' • '))} • <strong>${this.formatNumber(total)}</strong> matching ${total === 1 ? 'student' : 'students'}`;
                }

                if (lastUpdated) {
                    lastUpdated.textContent = this.lastUpdatedAt
                        ? `Updated ${this.formatTimeRelative(this.lastUpdatedAt)}`
                        : 'Waiting for data...';
                }
            }

            renderStats(data, filters = this.getCurrentFilters()) {
                const totalStudents = Number(data.totalStudents || 0);
                const activeStudents = Number(data.activeStudents || 0);
                const inactiveStudents = Number(data.inactiveStudents || 0);
                const hasFilters = this.hasActiveFilters(filters);

                document.getElementById('totalCount').textContent = this.formatNumber(totalStudents);
                document.getElementById('activeCount').textContent = this.formatNumber(activeStudents);
                document.getElementById('inactiveCount').textContent = this.formatNumber(inactiveStudents);
                document.getElementById('totalMeta').textContent = hasFilters
                    ? 'Matches the current search and filter selection'
                    : 'Live overview across all departments and batches';
                document.getElementById('activeMeta').textContent = totalStudents > 0
                    ? `${Math.round((activeStudents / totalStudents) * 100)}% of visible students are active`
                    : 'No active student accounts in this view';
                document.getElementById('inactiveMeta').textContent = totalStudents > 0
                    ? `${Math.round((inactiveStudents / totalStudents) * 100)}% of visible students are inactive`
                    : 'No inactive student accounts in this view';
            }

            setStatsLoading(isLoading) {
                this.statCards.forEach(card => card.classList.toggle('is-loading', isLoading));
            }

            setTableLoading(isLoading) {
                const tableWrapper = document.getElementById('studentTableWrapper');
                const tableBody = document.getElementById('studentsTableBody');
                const pagination = document.getElementById('paginationContainer');

                tableWrapper?.setAttribute('aria-busy', String(isLoading));

                if (!tableBody) {
                    return;
                }

                if (isLoading) {
                    tableBody.classList.add('student-table-loading');
                    tableBody.innerHTML = this.tableSkeletonMarkup();
                    if (pagination) {
                        pagination.innerHTML = '';
                    }
                    return;
                }

                tableBody.classList.remove('student-table-loading');
            }

            renderTableError() {
                document.getElementById('studentsTableBody').innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-triangle-exclamation" style="font-size: 40px; margin-bottom: 16px; opacity: 0.6; display: block;"></i>
                            <p style="font-size: 16px; margin: 0; font-weight: 500;">Unable to load students</p>
                            <p style="font-size: 14px; margin-top: 8px; color: #9ca3af;">Please try again in a moment.</p>
                        </td>
                    </tr>
                `;
                document.getElementById('paginationContainer').innerHTML = '';
                this.totalRows = 0;
            }

            normalizePerPage(value) {
                const allowedValues = [10, 20, 50, 100];
                const perPage = Number(value);
                return allowedValues.includes(perPage) ? perPage : 10;
            }

            syncUrlState(search = document.getElementById('searchInput')?.value.trim() || '') {
                const params = new URLSearchParams();

                if (search) params.set('search', search);
                if (this.currentDepartment !== 'all') params.set('department', this.currentDepartment);
                if (this.currentStatus !== 'all') params.set('status', this.currentStatus);
                if (this.currentSort !== 'created-desc') params.set('sort', this.currentSort);
                if (this.currentPage > 1) params.set('page', String(this.currentPage));
                if (this.rowsPerPage !== 10) params.set('per_page', String(this.rowsPerPage));

                const nextUrl = params.toString()
                    ? `${window.location.pathname}?${params.toString()}`
                    : window.location.pathname;

                window.history.replaceState({ url: nextUrl }, '', nextUrl);
            }

            tableSkeletonMarkup(rows = 5) {
                return Array.from({ length: rows }, () => `
                    <tr>
                        <td><span class="student-skeleton-line long"></span></td>
                        <td><span class="student-skeleton-line medium"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line medium"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line long"></span></td>
                    </tr>
                `).join('');
            }
        }

        // Make deleteStudent globally available
        window.deleteStudent = function(studentId) {
            if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) return;

            fetch(`{{ url('admin/students') }}/${studentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Reset selection after deletion
                        manager.selectedRowIndex = -1;
                        manager.fetchStudents();
                        manager.refreshStats();
                    } else {
                        alert(data.message || 'Error deleting student');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting student');
                });
        };

        // Toggle student status
        window.toggleStudentStatus = function(studentId) {
            fetch(`{{ url('admin/students') }}/${studentId}/toggle-status`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Refresh the table to show updated status
                        manager.fetchStudents(manager.currentPage);
                        manager.refreshStats();

                        // Show toast notification
                        const toast = document.createElement('div');
                        toast.style.cssText = `
                            position: fixed;
                            top: 20px;
                            right: 20px;
                            background: #3b82f6;
                            color: white;
                            padding: 12px 16px;
                            border-radius: 6px;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            z-index: 2000;
                            font-size: 14px;
                            font-weight: 500;
                        `;
                        toast.textContent = 'Student status updated successfully!';
                        document.body.appendChild(toast);
                        setTimeout(() => toast.remove(), 3000);
                    } else {
                        alert(data.message || 'Error updating student status');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating student status');
                });
        };

        const studentToastIcons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-circle-xmark',
            warning: 'fas fa-triangle-exclamation',
            info: 'fas fa-circle-info',
        };

        function dismissStudentToast(toast) {
            if (!toast) {
                return;
            }

            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 180);
        }

        function announceStudentMessage(message) {
            const liveRegion = document.getElementById('studentLiveRegion');

            if (liveRegion) {
                liveRegion.textContent = message;
            }
        }

        function escapeStudentHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderStudentToast(message, type = 'info') {
            const container = document.getElementById('studentToastContainer');

            if (!container) {
                return;
            }

            const normalizedType = typeof type === 'string' && type.startsWith('#')
                ? 'error'
                : type;
            const payload = typeof message === 'object' && message !== null
                ? message
                : {
                    title: normalizedType === 'error'
                        ? 'Action Failed'
                        : normalizedType === 'warning'
                            ? 'Check Required Fields'
                            : normalizedType === 'success'
                                ? 'Success'
                                : 'Notice',
                    message: String(message || ''),
                };

            container.querySelectorAll('.student-toast').forEach((existingToast) => existingToast.remove());

            const toast = document.createElement('div');
            toast.className = `student-toast ${normalizedType}`;
            toast.innerHTML = `
                <div class="student-toast-icon" aria-hidden="true">
                    <i class="${escapeStudentHtml(payload.icon || studentToastIcons[normalizedType] || studentToastIcons.info)}"></i>
                </div>
                <div class="student-toast-copy">
                    <div class="student-toast-title">${escapeStudentHtml(payload.title || 'Notice')}</div>
                    <div class="student-toast-message">${escapeStudentHtml(payload.message || '')}</div>
                    ${payload.detail ? `<div class="student-toast-detail">${escapeStudentHtml(payload.detail)}</div>` : ''}
                </div>
                <button type="button" class="student-toast-close" aria-label="Dismiss notification">
                    <i class="fas fa-times"></i>
                </button>
                <span class="student-toast-progress" aria-hidden="true"></span>
            `;

            container.appendChild(toast);
            toast.querySelector('.student-toast-close')?.addEventListener('click', () => dismissStudentToast(toast));
            announceStudentMessage(`${payload.title || 'Notice'}. ${payload.message || ''}`.trim());
            window.setTimeout(() => dismissStudentToast(toast), 4200);
        }

        function showStudentToast(message, type = 'info') {
            renderStudentToast(message, type);
        }

        Object.assign(StudentManager.prototype, {
            init() {
                this.students = [];
                this.paginationData = this.createEmptyPaginationState();
                this.statsData = null;
                this.pendingDeleteStudent = null;
                this.isDeletingStudent = false;
                this.pendingPasswordResetStudent = null;
                this.isResettingPassword = false;
                this.toastContainer = document.getElementById('studentToastContainer');
                this.liveRegion = document.getElementById('studentLiveRegion');
                this.deleteModal = document.getElementById('deleteStudentModal');
                this.deleteConfirmButton = document.getElementById('deleteStudentConfirmBtn');
                this.deleteCancelButton = document.getElementById('deleteStudentCancelBtn');
                this.deleteModalTitle = document.getElementById('deleteStudentModalTitle');
                this.deleteModalMessage = document.getElementById('deleteStudentModalMessage');
                this.deleteModalDetail = document.getElementById('deleteStudentModalDetail');
                this.passwordResetModal = document.getElementById('studentPasswordResetConfirmModal');
                this.passwordResetConfirmButton = document.getElementById('studentPasswordResetConfirmSubmitBtn');
                this.feedbackUI = this.initializePasswordResetFeedback();

                const searchInput = document.getElementById('searchInput');
                const departmentFilter = document.getElementById('departmentFilter');
                const statusFilter = document.getElementById('statusFilter');
                const sortFilter = document.getElementById('sortFilter');
                const entriesSelect = document.getElementById('studentsEntriesSelect');
                const resetBtn = document.getElementById('resetFiltersBtn');
                const addBtn = document.getElementById('addStudentBtn');

                if (searchInput) {
                    searchInput.value = new URLSearchParams(window.location.search).get('search') || '';
                    searchInput.addEventListener('input', () => {
                        clearTimeout(this.searchDebounceTimer);
                        this.searchDebounceTimer = setTimeout(() => {
                            this.selectedRowIndex = -1;
                            this.currentPage = 1;
                            void this.reloadStudents();
                        }, 300);
                    });
                }

                if (departmentFilter) {
                    departmentFilter.value = this.currentDepartment;
                    departmentFilter.addEventListener('change', (event) => {
                        this.currentDepartment = event.target.value;
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        void this.reloadStudents();
                    });
                }

                if (statusFilter) {
                    statusFilter.value = this.currentStatus;
                    statusFilter.addEventListener('change', (event) => {
                        this.currentStatus = event.target.value;
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        void this.reloadStudents();
                    });
                }

                if (sortFilter) {
                    sortFilter.value = this.currentSort;
                    sortFilter.addEventListener('change', (event) => {
                        this.currentSort = event.target.value;
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        void this.reloadStudents();
                    });
                }

                if (entriesSelect) {
                    entriesSelect.value = String(this.rowsPerPage);
                    entriesSelect.addEventListener('change', (event) => {
                        this.rowsPerPage = this.normalizePerPage(event.target.value);
                        this.selectedRowIndex = -1;
                        this.currentPage = 1;
                        void this.reloadStudents();
                    });
                }

                resetBtn?.addEventListener('click', () => this.resetFilters());
                addBtn?.addEventListener('click', () => openAddStudentModal());

                this.bindDeleteModalEvents();
                this.bindPasswordResetFeedbackEvents();
                document.addEventListener('keydown', (event) => this.handleKeyboardNavigation(event));
                this.setupKeyboardShortcuts();
                void this.reloadStudents();
            },

            setupKeyboardShortcuts() {
                document.addEventListener('keydown', (event) => {
                    if (this.isDeleteModalVisible() || this.isPasswordResetModalVisible()) {
                        return;
                    }

                    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                        event.preventDefault();
                        document.getElementById('searchInput')?.focus();
                        return;
                    }

                    if (event.key === 'Escape' && document.activeElement?.id === 'searchInput') {
                        const input = document.getElementById('searchInput');

                        if (input?.value) {
                            input.value = '';
                            this.currentPage = 1;
                            void this.reloadStudents();
                        } else {
                            input?.blur();
                        }
                    }
                });
            },

            bindDeleteModalEvents() {
                this.deleteCancelButton?.addEventListener('click', () => this.closeDeleteModal());
                this.deleteConfirmButton?.addEventListener('click', () => {
                    void this.confirmDeleteStudent();
                });
                this.deleteModal?.addEventListener('click', (event) => {
                    if (event.target === this.deleteModal) {
                        this.closeDeleteModal();
                    }
                });
            },

            initializePasswordResetFeedback() {
                if (typeof window.ActionFeedbackUI !== 'function') {
                    return null;
                }

                return new window.ActionFeedbackUI({
                    confirm: {
                        modalId: 'studentPasswordResetConfirmModal',
                        iconId: 'studentPasswordResetConfirmIcon',
                        titleId: 'studentPasswordResetConfirmTitle',
                        messageId: 'studentPasswordResetConfirmMessage',
                        detailId: 'studentPasswordResetConfirmDetail',
                        submitButtonId: 'studentPasswordResetConfirmSubmitBtn',
                        confirmLabel: 'Reset Student Password',
                    },
                    toast: {
                        containerId: 'studentActionToastContainer',
                        liveRegionId: 'studentActionLiveRegion',
                    },
                    setButtonBusy: (button, isBusy, label) => this.setFeedbackButtonBusy(button, isBusy, label),
                });
            },

            bindPasswordResetFeedbackEvents() {
                this.passwordResetConfirmButton?.addEventListener('click', () => {
                    void this.confirmPasswordReset();
                });

                document.addEventListener('click', (event) => {
                    const closeButton = event.target.closest('[data-modal-close="studentPasswordResetConfirmModal"]');

                    if (closeButton) {
                        this.closePasswordResetModal();
                    }
                });

                this.passwordResetModal?.addEventListener('click', (event) => {
                    if (event.target === this.passwordResetModal) {
                        this.closePasswordResetModal();
                    }
                });
            },

            isDeleteModalVisible() {
                return this.deleteModal?.style.display === 'flex';
            },

            isPasswordResetModalVisible() {
                return this.passwordResetModal?.classList.contains('is-open');
            },

            setFeedbackButtonBusy(button, isBusy, label) {
                if (!button) {
                    return;
                }

                button.disabled = Boolean(isBusy);
                button.textContent = label || button.dataset.defaultLabel || 'Continue';
            },

            getPasswordResetModalContent(student) {
                const email = student?.email && student.email !== 'N/A'
                    ? student.email
                    : 'the student email address';
                const detailSegments = [];

                if (student?.rollNo && student.rollNo !== 'N/A') {
                    detailSegments.push(`Student ID ${student.rollNo}`);
                }

                if (student?.department && student.department !== 'N/A') {
                    detailSegments.push(student.department);
                }

                return {
                    title: `Reset password for ${student?.name || 'this student'}?`,
                    message: `A temporary password will be sent to ${email} and must be changed after the next sign in.`,
                    detail: detailSegments.join(' • ') || 'Student account',
                    confirmLabel: 'Reset Student Password',
                };
            },

            openPasswordResetModal(student) {
                if (!student || !this.feedbackUI) {
                    return;
                }

                this.pendingPasswordResetStudent = student;
                const modalCopy = this.getPasswordResetModalContent(student);

                this.feedbackUI.openConfirm({
                    title: modalCopy.title,
                    message: modalCopy.message,
                    detail: modalCopy.detail,
                    confirmText: modalCopy.confirmLabel,
                    variant: 'warning',
                    buttonVariant: 'warning',
                });

                this.feedbackUI.getConfirmButton()?.focus();
            },

            closePasswordResetModal(force = false) {
                if (!force && this.isResettingPassword) {
                    return;
                }

                this.feedbackUI?.resetConfirm();
                this.feedbackUI?.closeConfirm();
                this.pendingPasswordResetStudent = null;
            },

            openDeleteModal(student) {
                if (!student || !this.deleteModal) {
                    return;
                }

                this.pendingDeleteStudent = student;
                this.deleteModalTitle.textContent = `Delete ${student.name || 'Student'}?`;
                this.deleteModalMessage.textContent = `This will permanently remove ${student.name || 'this student'} from the system and cannot be undone.`;
                this.deleteModalDetail.textContent = student.rollNo ? `Student ID ${student.rollNo}` : 'Student record';
                this.deleteModal.style.display = 'flex';
                this.deleteModal.setAttribute('aria-hidden', 'false');
                this.deleteConfirmButton?.focus();
            },

            closeDeleteModal(force = false) {
                if (!force && this.isDeletingStudent) {
                    return;
                }

                if (this.deleteModal) {
                    this.deleteModal.style.display = 'none';
                    this.deleteModal.setAttribute('aria-hidden', 'true');
                }

                this.pendingDeleteStudent = null;
            },

            requestDeleteStudent(studentId) {
                const student = this.getStudentById(studentId);

                if (!student) {
                    this.showToast({
                        title: 'Student Not Found',
                        message: 'We could not find that student in the current list.',
                        icon: 'fas fa-circle-info',
                    }, 'warning');
                    return;
                }

                this.openDeleteModal(student);
            },

            requestPasswordReset(studentId) {
                const student = this.getStudentById(studentId);

                if (!student) {
                    this.showActionToast({
                        title: 'Student Not Found',
                        message: 'We could not find that student in the current list.',
                        detail: 'Refresh the roster and try again.',
                    }, 'warning');
                    return;
                }

                if (this.feedbackUI) {
                    this.openPasswordResetModal(student);
                    return;
                }

                const confirmed = window.confirm(
                    `Reset password for ${student.name}?\n\nA temporary password will be sent to ${student.email}. They must change it after the next sign in.`
                );

                if (confirmed) {
                    this.pendingPasswordResetStudent = student;
                    void this.confirmPasswordReset();
                }
            },

            resetFilters() {
                document.getElementById('searchInput').value = '';
                document.getElementById('statusFilter').value = 'all';
                document.getElementById('departmentFilter').value = 'all';
                document.getElementById('sortFilter').value = 'created-desc';
                this.currentStatus = 'all';
                this.currentDepartment = 'all';
                this.currentSort = 'created-desc';
                this.currentPage = 1;
                this.selectedRowIndex = -1;
                void this.reloadStudents();
            },

            getCurrentFilters() {
                return {
                    search: document.getElementById('searchInput')?.value.trim() || '',
                    department: document.getElementById('departmentFilter')?.value || this.currentDepartment,
                    status: document.getElementById('statusFilter')?.value || this.currentStatus,
                    sort: document.getElementById('sortFilter')?.value || this.currentSort,
                };
            },

            buildListingParams({ includePage = true } = {}) {
                const filters = this.getCurrentFilters();
                const params = new URLSearchParams({
                    search: filters.search,
                    department: filters.department,
                    status: filters.status,
                    sort: filters.sort,
                });

                if (includePage) {
                    params.set('page', String(this.currentPage));
                    params.set('per_page', String(this.rowsPerPage));
                }

                return params;
            },

            buildMutationUrl(url) {
                const params = this.buildListingParams({ includePage: false });
                return `${url}${url.includes('?') ? '&' : '?'}${params.toString()}`;
            },

            createEmptyPaginationState(total = 0) {
                return {
                    current_page: this.currentPage,
                    last_page: Math.max(1, Math.ceil(Number(total || 0) / this.rowsPerPage) || 1),
                    per_page: this.rowsPerPage,
                    total: Number(total || 0),
                    from: 0,
                    to: 0,
                };
            },

            async fetchStudents(page = 1) {
                this.currentPage = Math.max(1, Number(page) || 1);
                const filters = this.getCurrentFilters();
                this.currentDepartment = filters.department;
                this.currentStatus = filters.status;
                this.currentSort = filters.sort;
                this.setStatsLoading(true);
                this.setTableLoading(true);

                try {
                    const params = this.buildListingParams();
                    const response = await fetch(`{{ route('admin.students.data') }}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to load students.');
                    }

                    const pagination = data.paginationData || {
                        current_page: Number(data.current_page || this.currentPage),
                        last_page: Number(data.last_page || 1),
                        per_page: this.rowsPerPage,
                        total: Number(data.total || 0),
                        from: 0,
                        to: 0,
                    };

                    if (this.currentPage > Number(pagination.last_page || 1)) {
                        await this.fetchStudents(Number(pagination.last_page || 1));
                        return;
                    }

                    this.students = Array.isArray(data.students) ? data.students : [];
                    this.paginationData = pagination;
                    this.currentPage = Math.max(1, Number(pagination.current_page || this.currentPage || 1));
                    this.rowsPerPage = this.normalizePerPage(pagination.per_page || this.rowsPerPage);
                    this.statsData = data.stats || this.statsData;
                    this.lastUpdatedAt = new Date();
                    this.syncPaginationState(Number(this.statsData?.totalStudents ?? pagination.total ?? 0));
                    this.syncUrlState(filters.search);
                    this.renderAll(filters);
                } catch (error) {
                    console.error('Error fetching students:', error);
                    this.showToast(error.message || 'Unable to load students right now.', 'error');
                    this.renderTableError();
                } finally {
                    this.setTableLoading(false);
                    this.setStatsLoading(false);
                }
            },

            reloadStudents(page = 1) {
                return this.fetchStudents(page);
            },
        });

        Object.assign(StudentManager.prototype, {
            renderAll(filters = this.getCurrentFilters()) {
                this.renderTable();
                this.renderPagination();
                this.renderToolbarMeta();
                this.renderStats(this.statsData || {}, filters);
            },

            renderTable() {
                const tableBody = document.getElementById('studentsTableBody');

                if (!tableBody) {
                    return;
                }

                tableBody.classList.remove('student-table-loading');

                if (!this.students.length) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                                <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block;"></i>
                                <p style="font-size: 16px; margin: 0; font-weight: 500;">No students found</p>
                                <p style="font-size: 14px; margin-top: 8px; color: #9ca3af;">Try adjusting your search or filters.</p>
                            </td>
                        </tr>
                    `;
                    this.selectedRowIndex = -1;
                    return;
                }

                tableBody.innerHTML = this.students.map((student) => this.studentRowMarkup(student)).join('');
                this.attachEventListeners();
                this.updateRowAccessibility();

                if (this.selectedRowIndex >= 0) {
                    this.selectRow(Math.min(this.selectedRowIndex, this.students.length - 1));
                }
            },

            studentRowMarkup(student) {
                const fallbackInitial = String(student.name || 'S').trim().charAt(0).toUpperCase() || 'S';
                const avatar = student.avatar
                    ? `<div class="student-avatar"><img src="${this.escapeHtml(student.avatar)}" alt="${this.escapeHtml(student.name)}"></div>`
                    : `<div class="student-avatar">${this.escapeHtml(fallbackInitial)}</div>`;
                const statusClass = student.status === 'active' ? 'status-active' : 'status-inactive';
                const statusIconMarkup = student.status === 'active'
                    ? '<i class="fas fa-check-circle" style="font-size: 10px;"></i>'
                    : '<i class="fas fa-times-circle" style="font-size: 10px;"></i>';
                const toggleIcon = student.status === 'active' ? 'fas fa-toggle-on' : 'fas fa-toggle-off';
                const toggleTitle = student.status === 'active' ? 'Deactivate student' : 'Activate student';

                return `
                    <tr data-student-id="${student.id}" class="student-row">
                        <td>
                            <div class="student-cell">
                                ${avatar}
                                <div class="student-info">
                                    <span class="student-name">${this.escapeHtml(student.name)}</span>
                                    <div class="text-muted">${this.escapeHtml(student.rollNo)}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">${this.escapeHtml(student.email)}</td>
                        <td class="text-muted">${this.escapeHtml(student.phone)}</td>
                        <td class="text-muted">${this.escapeHtml(student.department)}</td>
                        <td class="text-muted">${this.escapeHtml(student.batch)}</td>
                        <td>
                            <span class="status-badge ${statusClass}">${statusIconMarkup} ${this.escapeHtml(student.statusLabel || this.capitalize(student.status))}</span>
                            ${student.registrationPending
                                ? '<div class="text-muted" style="font-size: 11px; margin-top: 4px;">Registration pending</div>'
                                : ''}
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ url('admin/students') }}/${student.id}" class="action-btn btn-view" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="action-btn btn-edit" onclick="openEditStudentModal(${student.id})" title="Edit student">
                                    <i class="fas fa-edit"></i>
                                </button>
                                ${student.registrationPending
                                    ? `<button type="button" class="action-btn btn-password" title="Complete registration first" disabled>
                                            <i class="fas fa-key"></i>
                                       </button>`
                                    : `<button type="button" class="action-btn btn-password" onclick="resetStudentPassword(${student.id})" title="Reset password">
                                            <i class="fas fa-key"></i>
                                       </button>`}
                                <button
                                    type="button"
                                    class="action-btn"
                                    data-action="toggle-status"
                                    data-student-id="${student.id}"
                                    title="${toggleTitle}"
                                    onclick="toggleStudentStatus(${student.id})"
                                >
                                    <i class="${toggleIcon}"></i>
                                </button>
                                <button type="button" class="action-btn btn-delete" onclick="deleteStudent(${student.id})" title="Delete student">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            },

            renderPagination() {
                const pagination = document.getElementById('paginationContainer');

                if (!pagination) {
                    return;
                }

                const total = Number(this.paginationData?.total || 0);
                const currentPage = Math.max(1, Number(this.paginationData?.current_page || this.currentPage || 1));
                const lastPage = Math.max(1, Number(this.paginationData?.last_page || 1));
                const from = Number(this.paginationData?.from || 0);
                const to = Number(this.paginationData?.to || 0);

                if (total === 0) {
                    pagination.innerHTML = '';
                    return;
                }

                const buttons = [
                    this.paginationButton('&larr; Previous', currentPage - 1, currentPage === 1, false, 'Previous page'),
                ];

                this.buildPaginationSequence(currentPage, lastPage).forEach((page) => {
                    if (page === null) {
                        buttons.push('<span class="admin-table-pagination-ellipsis" aria-hidden="true">&hellip;</span>');
                        return;
                    }

                    buttons.push(this.paginationButton(String(page), page, false, page === currentPage, `Page ${page}`));
                });

                buttons.push(this.paginationButton('Next &rarr;', currentPage + 1, currentPage === lastPage, false, 'Next page'));

                pagination.innerHTML = `
                    <div class="admin-table-pagination">
                        <div class="admin-table-pagination-meta">
                            <div class="admin-table-pagination-summary">Showing ${this.formatNumber(from)} to ${this.formatNumber(to)} of ${this.formatNumber(total)} results</div>
                            <div class="admin-table-pagination-page">Page ${currentPage} of ${lastPage}</div>
                        </div>
                        <nav class="admin-table-pagination-nav" aria-label="Pagination navigation">
                            ${buttons.join('')}
                        </nav>
                    </div>
                `;

                pagination.querySelectorAll('[data-page]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const nextPage = Number(button.getAttribute('data-page'));

                        if (!Number.isNaN(nextPage) && nextPage > 0 && nextPage !== this.currentPage) {
                            this.selectedRowIndex = -1;
                            void this.fetchStudents(nextPage);
                        }
                    });
                });
            },

            paginationButton(label, page, disabled = false, active = false, ariaLabel = '') {
                return `
                    <button
                        type="button"
                        class="admin-table-pagination-link${active ? ' is-active' : ''}${disabled ? ' is-disabled' : ''}"
                        data-page="${page}"
                        aria-label="${this.escapeHtml(active ? `Current page, ${ariaLabel || label}` : (ariaLabel || `Page ${label}`))}"
                        ${disabled ? 'disabled aria-disabled="true"' : ''}
                        ${active ? 'aria-current="page"' : ''}
                    >
                        ${label}
                    </button>
                `;
            },

            buildPaginationSequence(currentPage, lastPage) {
                if (lastPage <= 7) {
                    return Array.from({ length: lastPage }, (_, index) => index + 1);
                }

                const pages = [1];
                let startPage = Math.max(2, currentPage - 1);
                let endPage = Math.min(lastPage - 1, currentPage + 1);

                if (currentPage <= 3) {
                    endPage = 4;
                }

                if (currentPage >= lastPage - 2) {
                    startPage = lastPage - 3;
                }

                if (startPage > 2) {
                    pages.push(null);
                }

                for (let page = startPage; page <= endPage; page += 1) {
                    pages.push(page);
                }

                if (endPage < lastPage - 1) {
                    pages.push(null);
                }

                pages.push(lastPage);
                return pages;
            },

            attachEventListeners() {
                document.querySelectorAll('#studentsTableBody tr[data-student-id]').forEach((row, index) => {
                    row.setAttribute('tabindex', '0');
                    row.classList.add('student-row');
                    row.addEventListener('click', (event) => {
                        if (event.target.closest('.action-btn')) {
                            return;
                        }

                        this.selectRow(index);
                        row.focus();
                    });

                    row.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            this.selectRow(index);
                            row.querySelector('.btn-view')?.click();
                        }
                    });
                });
            },

            updateRowAccessibility() {
                document.querySelectorAll('#studentsTableBody tr[data-student-id]').forEach((row, index) => {
                    const student = this.students[index];
                    row.setAttribute('role', 'row');
                    row.setAttribute('aria-label', student?.name ? `${student.name} student row` : `Student row ${index + 1}`);
                    row.setAttribute('tabindex', '0');
                });
            },

            selectRow(index) {
                document.querySelectorAll('#studentsTableBody tr[data-student-id]').forEach((row) => {
                    row.classList.remove('selected');
                    row.removeAttribute('aria-selected');
                });

                const rows = document.querySelectorAll('#studentsTableBody tr[data-student-id]');

                if (!rows[index]) {
                    this.selectedRowIndex = -1;
                    return;
                }

                rows[index].classList.add('selected');
                rows[index].setAttribute('aria-selected', 'true');
                this.selectedRowIndex = index;
            },

            handleKeyboardNavigation(event) {
                if (this.isDeleteModalVisible()) {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        this.closeDeleteModal();
                    }
                    return;
                }

                if (this.isPasswordResetModalVisible()) {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        this.closePasswordResetModal();
                    }
                    return;
                }

                const rows = document.querySelectorAll('#studentsTableBody tr[data-student-id]');

                if (!rows.length || ['INPUT', 'SELECT', 'TEXTAREA'].includes(event.target.tagName)) {
                    return;
                }

                switch (event.key) {
                    case 'ArrowDown':
                        event.preventDefault();
                        if (this.selectedRowIndex < rows.length - 1) {
                            this.selectedRowIndex += 1;
                        } else if (this.selectedRowIndex === -1) {
                            this.selectedRowIndex = 0;
                        }
                        this.selectRow(this.selectedRowIndex);
                        rows[this.selectedRowIndex]?.focus();
                        break;

                    case 'ArrowUp':
                        event.preventDefault();
                        if (this.selectedRowIndex > 0) {
                            this.selectedRowIndex -= 1;
                        } else if (this.selectedRowIndex === -1 && rows.length > 0) {
                            this.selectedRowIndex = 0;
                        }
                        this.selectRow(this.selectedRowIndex);
                        rows[this.selectedRowIndex]?.focus();
                        break;

                    case 'Home':
                        event.preventDefault();
                        this.selectRow(0);
                        rows[0]?.focus();
                        break;

                    case 'End':
                        event.preventDefault();
                        this.selectRow(rows.length - 1);
                        rows[rows.length - 1]?.focus();
                        break;

                    case 'Escape':
                        this.selectedRowIndex = -1;
                        document.querySelectorAll('#studentsTableBody tr[data-student-id]').forEach((row) => {
                            row.classList.remove('selected');
                            row.removeAttribute('aria-selected');
                        });
                        break;

                    case '/':
                        if (!event.ctrlKey && !event.metaKey) {
                            event.preventDefault();
                            document.getElementById('searchInput')?.focus();
                            document.getElementById('searchInput')?.select();
                        }
                        break;

                    case 'n':
                    case 'N':
                        if (event.ctrlKey || event.metaKey) {
                            event.preventDefault();
                            openAddStudentModal();
                        }
                        break;

                    case 'Delete':
                        if (this.selectedRowIndex >= 0) {
                            const studentId = rows[this.selectedRowIndex]?.dataset.studentId;

                            if (studentId) {
                                deleteStudent(studentId);
                            }
                        }
                        break;

                    case 'Enter':
                        if (this.selectedRowIndex >= 0 && !event.shiftKey) {
                            const viewBtn = rows[this.selectedRowIndex]?.querySelector('.btn-view');

                            if (viewBtn) {
                                event.preventDefault();
                                viewBtn.click();
                            }
                        }
                        break;
                }
            },

            getStudentById(studentId) {
                return this.students.find((student) => Number(student.id) === Number(studentId)) || null;
            },

            matchesCurrentFilters(student) {
                if (!student) {
                    return false;
                }

                const searchValue = (this.getCurrentFilters().search || '').toLowerCase();
                const haystack = [
                    student.name,
                    student.email,
                    student.phone,
                    student.rollNo,
                    student.department,
                ].map((value) => String(value || '').toLowerCase()).join(' ');

                if (searchValue && !haystack.includes(searchValue)) {
                    return false;
                }

                if (this.currentDepartment !== 'all' && String(student.departmentId || '') !== String(this.currentDepartment)) {
                    return false;
                }

                if (this.currentStatus !== 'all' && String(student.status || '').toLowerCase() !== this.currentStatus) {
                    return false;
                }

                return true;
            },

            sortVisibleStudents() {
                if (this.currentSort === 'name-asc' || this.currentSort === 'name-desc') {
                    this.students.sort((left, right) => {
                        const leftName = String(left.name || '').toLowerCase();
                        const rightName = String(right.name || '').toLowerCase();

                        return this.currentSort === 'name-asc'
                            ? leftName.localeCompare(rightName)
                            : rightName.localeCompare(leftName);
                    });
                }
            },

            syncPaginationState(totalOverride = null) {
                const total = Math.max(0, Number(totalOverride ?? this.paginationData?.total ?? this.totalRows ?? 0));
                const lastPage = Math.max(1, Math.ceil(total / this.rowsPerPage) || 1);

                if (this.currentPage > lastPage) {
                    this.currentPage = lastPage;

                    if (total > 0) {
                        return { requiresFetch: true, page: lastPage };
                    }
                }

                this.paginationData = {
                    ...this.paginationData,
                    current_page: this.currentPage,
                    last_page: lastPage,
                    per_page: this.rowsPerPage,
                    total,
                    from: total === 0 ? 0 : ((this.currentPage - 1) * this.rowsPerPage) + 1,
                    to: total === 0 ? 0 : Math.min((((this.currentPage - 1) * this.rowsPerPage) + this.students.length), total),
                };
                this.totalRows = total;

                return { requiresFetch: false, page: this.currentPage };
            },

            applyLocalStudentCreate(student) {
                const total = Number(this.statsData?.totalStudents ?? this.totalRows + 1);

                if (student && this.matchesCurrentFilters(student) && this.currentPage === 1) {
                    if (this.currentSort === 'created-desc' || total === 1) {
                        this.students.unshift(student);
                    } else if (this.currentSort === 'name-asc' || this.currentSort === 'name-desc') {
                        this.students.push(student);
                        this.sortVisibleStudents();
                    } else if (total === 1) {
                        this.students.unshift(student);
                    }

                    this.students = this.students.slice(0, this.rowsPerPage);
                }

                return this.syncPaginationState(total);
            },

            applyLocalStudentUpdate(student) {
                const index = this.students.findIndex((item) => Number(item.id) === Number(student?.id));
                const total = Number(this.statsData?.totalStudents ?? this.totalRows);

                if (index === -1) {
                    return this.syncPaginationState(total);
                }

                if (!this.matchesCurrentFilters(student)) {
                    this.students.splice(index, 1);

                    if (this.selectedRowIndex === index) {
                        this.selectedRowIndex = -1;
                    } else if (this.selectedRowIndex > index) {
                        this.selectedRowIndex -= 1;
                    }

                    return this.syncPaginationState(total);
                }

                this.students.splice(index, 1, student);
                this.sortVisibleStudents();
                return this.syncPaginationState(total);
            },

            applyLocalStudentDelete(studentId) {
                const index = this.students.findIndex((student) => Number(student.id) === Number(studentId));

                if (index !== -1) {
                    this.students.splice(index, 1);

                    if (this.selectedRowIndex === index) {
                        this.selectedRowIndex = -1;
                    } else if (this.selectedRowIndex > index) {
                        this.selectedRowIndex -= 1;
                    }
                }

                return this.syncPaginationState(Number(this.statsData?.totalStudents ?? Math.max(0, this.totalRows - 1)));
            },
        });

        Object.assign(StudentManager.prototype, {
            async handleStudentMutation(action, payload) {
                if (payload?.stats) {
                    this.statsData = payload.stats;
                }

                let mutation = { requiresFetch: false, page: this.currentPage };

                if (action === 'create' && payload?.student) {
                    mutation = this.applyLocalStudentCreate(payload.student);
                } else if (action === 'update' && payload?.student) {
                    mutation = this.applyLocalStudentUpdate(payload.student);
                }

                this.lastUpdatedAt = new Date();

                if (mutation.requiresFetch) {
                    await this.fetchStudents(mutation.page);
                    return;
                }

                this.renderAll();
            },

            async confirmDeleteStudent() {
                if (!this.pendingDeleteStudent || this.isDeletingStudent) {
                    return;
                }

                const confirmLabel = this.deleteConfirmButton?.innerHTML || 'Delete Student';
                this.isDeletingStudent = true;

                if (this.deleteConfirmButton) {
                    this.deleteConfirmButton.disabled = true;
                    this.deleteConfirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                }

                if (this.deleteCancelButton) {
                    this.deleteCancelButton.disabled = true;
                }

                try {
                    const response = await fetch(this.buildMutationUrl(`{{ url('admin/students') }}/${this.pendingDeleteStudent.id}`), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to delete the student.');
                    }

                    const deletedStudent = data.deletedStudent || this.pendingDeleteStudent;

                    if (data.stats) {
                        this.statsData = data.stats;
                    }

                    const mutation = this.applyLocalStudentDelete(deletedStudent.id);
                    this.lastUpdatedAt = new Date();
                    this.closeDeleteModal(true);

                    if (mutation.requiresFetch) {
                        await this.fetchStudents(mutation.page);
                    } else {
                        this.renderAll();
                    }

                    this.showToast({
                        title: 'Student Deleted',
                        message: `Deleted ${deletedStudent.name || 'the student'} from the roster.`,
                        detail: deletedStudent.rollNo ? `Student ID ${deletedStudent.rollNo}` : '',
                        icon: 'fas fa-trash-alt',
                    }, 'error');
                    this.announce(`${deletedStudent.name || 'Student'} deleted successfully.`);
                } catch (error) {
                    console.error('Error deleting student:', error);
                    this.showToast(error.message || 'Unable to delete the student.', 'error');
                } finally {
                    this.isDeletingStudent = false;

                    if (this.deleteConfirmButton) {
                        this.deleteConfirmButton.disabled = false;
                        this.deleteConfirmButton.innerHTML = confirmLabel;
                    }

                    if (this.deleteCancelButton) {
                        this.deleteCancelButton.disabled = false;
                    }
                }
            },

            async confirmPasswordReset() {
                if (!this.pendingPasswordResetStudent || this.isResettingPassword) {
                    return;
                }

                this.isResettingPassword = true;
                this.feedbackUI?.setConfirmBusy(true, 'Sending...');

                try {
                    const response = await fetch(`{{ url('admin/students') }}/${this.pendingPasswordResetStudent.id}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to reset the student password.');
                    }

                    const student = this.pendingPasswordResetStudent;
                    this.closePasswordResetModal(true);
                    this.showActionToast({
                        title: 'Password Reset Sent',
                        message: `Sent a temporary password to ${student?.name || 'the student'}.`,
                        detail: student?.email && student.email !== 'N/A' ? student.email : '',
                    }, 'success');
                } catch (error) {
                    console.error('Error resetting student password:', error);
                    this.showActionToast({
                        title: 'Password Reset Failed',
                        message: error.message || 'Unable to reset the student password right now.',
                    }, 'error');
                } finally {
                    this.isResettingPassword = false;
                    this.feedbackUI?.resetConfirm();
                }
            },

            async performStatusToggle(studentId) {
                const button = document.querySelector(`[data-action="toggle-status"][data-student-id="${studentId}"]`);
                const originalMarkup = button?.innerHTML || '';
                const currentStudent = this.getStudentById(studentId);

                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }

                try {
                    const response = await fetch(this.buildMutationUrl(`{{ url('admin/students') }}/${studentId}/toggle-status`), {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Unable to update the student status.');
                    }

                    if (data.stats) {
                        this.statsData = data.stats;
                    }

                    const updatedStudent = data.student || currentStudent;
                    const mutation = this.applyLocalStudentUpdate(updatedStudent);
                    this.lastUpdatedAt = new Date();

                    if (mutation.requiresFetch) {
                        await this.fetchStudents(mutation.page);
                    } else {
                        this.renderAll();
                    }

                    const isActive = String(updatedStudent?.status || '').toLowerCase() === 'active';
                    this.showToast({
                        title: isActive ? 'Student Activated' : 'Student Deactivated',
                        message: `${isActive ? 'Activated' : 'Deactivated'} ${updatedStudent?.name || 'the student'}${updatedStudent?.rollNo ? ` (${updatedStudent.rollNo})` : ''}.`,
                        detail: updatedStudent?.department || '',
                        icon: isActive ? 'fas fa-user-check' : 'fas fa-user-slash',
                    }, isActive ? 'success' : 'warning');
                    this.announce(`${updatedStudent?.name || 'Student'} has been ${isActive ? 'activated' : 'deactivated'}.`);
                } catch (error) {
                    console.error('Error updating student status:', error);
                    this.showToast(error.message || 'Unable to update the student status.', 'error');

                    if (button) {
                        button.disabled = false;
                        button.innerHTML = originalMarkup;
                    }
                }
            },

            refreshStats() {
                this.renderStats(this.statsData || {}, this.getCurrentFilters());
            },

            hasActiveFilters(filters = this.getCurrentFilters()) {
                return filters.search !== '' || filters.department !== 'all' || filters.status !== 'all';
            },

            renderToolbarMeta() {
                const segments = [];
                const total = Number(this.totalRows || 0);
                const search = document.getElementById('searchInput')?.value.trim() || '';

                if (search) {
                    segments.push(`Search: "${search}"`);
                }

                if (this.currentStatus !== 'all') {
                    segments.push(`Status: ${this.capitalize(this.currentStatus)}`);
                }

                if (this.currentDepartment !== 'all') {
                    segments.push(`Department: ${this.getDepartmentLabel()}`);
                }

                segments.push(`Sort: ${this.getSortLabel(this.currentSort)}`);

                const filterSummary = document.getElementById('studentFilterSummary');
                const lastUpdated = document.getElementById('studentLastUpdated');

                if (filterSummary) {
                    filterSummary.innerHTML = `${this.escapeHtml(segments.join(' • '))} • <strong>${this.formatNumber(total)}</strong> matching ${total === 1 ? 'student' : 'students'}`;
                }

                if (lastUpdated) {
                    lastUpdated.textContent = this.lastUpdatedAt
                        ? `Updated ${this.formatTimeRelative(this.lastUpdatedAt)}`
                        : 'Waiting for data...';
                }
            },

            renderStats(data, filters = this.getCurrentFilters()) {
                const totalStudents = Number(data.totalStudents || 0);
                const activeStudents = Number(data.activeStudents || 0);
                const inactiveStudents = Number(data.inactiveStudents || 0);
                const hasFilters = this.hasActiveFilters(filters);

                document.getElementById('totalCount').textContent = this.formatNumber(totalStudents);
                document.getElementById('activeCount').textContent = this.formatNumber(activeStudents);
                document.getElementById('inactiveCount').textContent = this.formatNumber(inactiveStudents);
                document.getElementById('totalMeta').textContent = hasFilters
                    ? 'Matches the current search and filter selection'
                    : 'Live overview across all departments and batches';
                document.getElementById('activeMeta').textContent = totalStudents > 0
                    ? `${Math.round((activeStudents / totalStudents) * 100)}% of visible students are active`
                    : 'No active student accounts in this view';
                document.getElementById('inactiveMeta').textContent = totalStudents > 0
                    ? `${Math.round((inactiveStudents / totalStudents) * 100)}% of visible students are inactive`
                    : 'No inactive student accounts in this view';
            },

            setStatsLoading(isLoading) {
                this.statCards.forEach((card) => card.classList.toggle('is-loading', isLoading));
            },

            setTableLoading(isLoading) {
                const tableWrapper = document.getElementById('studentTableWrapper');
                const tableBody = document.getElementById('studentsTableBody');
                const pagination = document.getElementById('paginationContainer');

                tableWrapper?.setAttribute('aria-busy', String(isLoading));

                if (!tableBody) {
                    return;
                }

                if (isLoading) {
                    tableBody.classList.add('student-table-loading');
                    tableBody.innerHTML = this.tableSkeletonMarkup();
                    pagination.innerHTML = '';
                    return;
                }

                tableBody.classList.remove('student-table-loading');
            },

            renderTableError() {
                document.getElementById('studentsTableBody').innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-triangle-exclamation" style="font-size: 40px; margin-bottom: 16px; opacity: 0.6; display: block;"></i>
                            <p style="font-size: 16px; margin: 0; font-weight: 500;">Unable to load students</p>
                            <p style="font-size: 14px; margin-top: 8px; color: #9ca3af;">Please try again in a moment.</p>
                        </td>
                    </tr>
                `;
                document.getElementById('paginationContainer').innerHTML = '';
                this.students = [];
                this.paginationData = this.createEmptyPaginationState();
                this.totalRows = 0;
            },

            normalizePerPage(value) {
                const allowedValues = [10, 20, 50, 100];
                const perPage = Number(value);
                return allowedValues.includes(perPage) ? perPage : 10;
            },

            syncUrlState(search = document.getElementById('searchInput')?.value.trim() || '') {
                const params = new URLSearchParams();

                if (search) params.set('search', search);
                if (this.currentDepartment !== 'all') params.set('department', this.currentDepartment);
                if (this.currentStatus !== 'all') params.set('status', this.currentStatus);
                if (this.currentSort !== 'created-desc') params.set('sort', this.currentSort);
                if (this.currentPage > 1) params.set('page', String(this.currentPage));
                if (this.rowsPerPage !== 10) params.set('per_page', String(this.rowsPerPage));

                const nextUrl = params.toString()
                    ? `${window.location.pathname}?${params.toString()}`
                    : window.location.pathname;

                window.history.replaceState({ url: nextUrl }, '', nextUrl);
            },

            tableSkeletonMarkup(rows = 5) {
                return Array.from({ length: rows }, () => `
                    <tr>
                        <td><span class="student-skeleton-line long"></span></td>
                        <td><span class="student-skeleton-line medium"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line medium"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line short"></span></td>
                        <td><span class="student-skeleton-line long"></span></td>
                    </tr>
                `).join('');
            },

            announce(message) {
                announceStudentMessage(message);
            },

            showToast(message, type = 'info') {
                showStudentToast(message, type);
            },

            showActionToast(message, type = 'info') {
                if (this.feedbackUI) {
                    const payload = typeof message === 'object' && message !== null
                        ? { ...message, type }
                        : { type, title: 'Notice', message: String(message || '') };

                    this.feedbackUI.showToast(payload);
                    return;
                }

                this.showToast(message, type);
            },
        });

        window.deleteStudent = function(studentId) {
            manager?.requestDeleteStudent(studentId);
        };

        window.resetStudentPassword = function(studentId) {
            void manager?.requestPasswordReset(studentId);
        };

        window.toggleStudentStatus = function(studentId) {
            void manager?.performStatusToggle(studentId);
        };

        // Open Edit Student Modal
        window.openEditStudentModal = function(studentId) {
            // Get the modal
            const modal = document.getElementById('editStudentModal');
            
            // IMMEDIATELY clear ALL error messages from previous failed submissions
            const errorElements = modal.querySelectorAll('.error-message');
            console.log('📋 Opening edit modal - Clearing', errorElements.length, 'error elements');
            errorElements.forEach((el, index) => {
                el.textContent = '';
                el.innerHTML = '';
                el.classList.remove('show');
                // Force hide with inline style
                el.style.cssText = 'display: none !important;';
                console.log(`  ✓ Cleared error ${index + 1}`);
            });
            console.log('✅ All errors cleared. Classes:', modal.querySelector('.error-message')?.className);
            
            // Fetch student data
            fetch(`{{ url('admin/students') }}/${studentId}/edit-data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Populate form fields
                        document.getElementById('edit_name').value = data.data.name || '';
                        document.getElementById('edit_email').value = data.data.email || '';
                        document.getElementById('edit_phone').value = data.data.phone || '';
                        document.getElementById('edit_gender').value = data.data.gender || '';
                        document.getElementById('edit_roll_no').value = data.data.roll_no || '';
                        document.getElementById('edit_department_id').value = data.data.department_id || '';
                        document.getElementById('edit_batch').value = data.data.batch || '';
                        document.getElementById('edit_semester').value = data.data.semester || '';
                        document.getElementById('edit_address').value = data.data.address || '';
                        document.getElementById('edit_status').value = data.data.status || '';

                        // Update form action
                        const form = document.getElementById('editStudentForm');
                        form.action = `{{ url('admin/students') }}/${studentId}`;

                        // Store studentId for later use
                        form.dataset.studentId = studentId;

                        // Show modal
                        if (modal) {
                            modal.style.display = 'flex';
                        }
                    } else {
                        alert(data.message || 'Error loading student data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading student data');
                });
        };

        // Close Edit Student Modal
        function closeEditStudentModal() {
            console.log('📤 Closing edit modal');
            const modal = document.getElementById('editStudentModal');
            if (modal) {
                modal.style.display = 'none';
            }
            // Reset form and clear ALL errors completely
            const form = document.getElementById('editStudentForm');
            if (form) {
                form.reset();
                // Clear error messages using both class removal and inline styles
                const errors = document.querySelectorAll('#editStudentModal .error-message');
                console.log('  ✓ Cleared', errors.length, 'error messages');
                errors.forEach(el => {
                    el.textContent = '';
                    el.classList.remove('show');
                    el.style.cssText = 'display: none !important;';
                });
            }
        }

        // Validate Edit Form
        function validateEditStudentForm() {
            let isValid = true;
            const errors = {};

            // Name validation
            const name = document.getElementById('edit_name').value.trim();
            if (!name) {
                errors['name'] = 'Full name is required';
                isValid = false;
            }

            // Email validation
            if (isValid) {
                const email = document.getElementById('edit_email').value.trim();
                if (!email) {
                    errors['email'] = 'Email is required';
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    errors['email'] = 'Please enter a valid email address';
                    isValid = false;
                }
            }

            // Phone validation
            if (isValid) {
                const phone = document.getElementById('edit_phone').value.trim();
                if (!phone) {
                    errors['phone'] = 'Phone number is required';
                    isValid = false;
                } else if (!/^[\d]{7,15}$/.test(phone.replace(/[^\d]/g, ''))) {
                    errors['phone'] = 'Phone number must be 7-15 digits';
                    isValid = false;
                }
            }

            // Roll Number validation
            if (isValid) {
                const rollNo = document.getElementById('edit_roll_no').value.trim();
                if (!rollNo) {
                    errors['roll_no'] = 'Roll number is required';
                    isValid = false;
                }
            }

            // Department validation
            if (isValid) {
                const departmentId = document.getElementById('edit_department_id').value;
                if (!departmentId) {
                    errors['department_id'] = 'Please select a department';
                    isValid = false;
                }
            }

            // Batch validation
            if (isValid) {
                const batch = document.getElementById('edit_batch').value.trim();
                if (!batch) {
                    errors['batch'] = 'Batch is required';
                    isValid = false;
                }
            }

            // Semester validation
            if (isValid) {
                const semester = document.getElementById('edit_semester').value.trim();
                if (!semester) {
                    errors['semester'] = 'Semester is required';
                    isValid = false;
                }
            }

            // Status validation
            if (isValid) {
                const status = document.getElementById('edit_status').value;
                if (!status) {
                    errors['status'] = 'Please select a status';
                    isValid = false;
                }
            }

            // Display ONLY THE FIRST error
            document.querySelectorAll('#editStudentModal .error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
                el.style.cssText = 'display: none !important;';
            });
            
            // Show only the first error that occurred
            if (!isValid && Object.keys(errors).length > 0) {
                const firstErrorField = Object.keys(errors)[0];
                const input = document.getElementById('edit_' + firstErrorField);
                if (input) {
                    const errorEl = input.parentElement.querySelector('.error-message');
                    if (errorEl) {
                        errorEl.textContent = errors[firstErrorField];
                        errorEl.classList.add('show');
                        errorEl.style.cssText = 'display: block !important;';
                        console.log(`❌ Validation error: ${firstErrorField} - ${errors[firstErrorField]}`);
                    }
                }
            }

            return isValid;
        }

        // Modal functions for Add Student
        function openAddStudentModal() {
            const modal = document.getElementById('addStudentModal');
            if (modal) {
                modal.style.display = 'flex';
                // Clear any previous errors
                document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            }
        }

        function closeAddStudentModal() {
            const modal = document.getElementById('addStudentModal');
            if (modal) {
                modal.style.display = 'none';
            }
            // Reset form
            const form = document.getElementById('addStudentForm');
            if (form) {
                form.reset();
                // Clear error messages
                document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            }
        }

        function validateAddStudentForm() {
            let isValid = true;
            const errors = {};

            // Name validation
            const name = document.getElementById('modal_name').value.trim();
            if (!name) {
                errors['name'] = 'Full name is required';
                isValid = false;
            }

            // Email validation
            if (isValid) {
                const email = document.getElementById('modal_email').value.trim();
                if (!email) {
                    errors['email'] = 'Email is required';
                    isValid = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    errors['email'] = 'Please enter a valid email address';
                    isValid = false;
                }
            }

            // Phone validation
            if (isValid) {
                const phone = document.getElementById('modal_phone').value.trim();
                if (!phone) {
                    errors['phone'] = 'Phone number is required';
                    isValid = false;
                } else if (!/^[\d]{7,15}$/.test(phone.replace(/[^\d]/g, ''))) {
                    errors['phone'] = 'Phone number must be 7-15 digits';
                    isValid = false;
                }
            }

            // Roll Number validation
            if (isValid) {
                const rollNo = document.getElementById('modal_roll_no').value.trim();
                if (!rollNo) {
                    errors['roll_no'] = 'Roll number is required';
                    isValid = false;
                }
            }

            // Department validation
            if (isValid) {
                const departmentId = document.getElementById('modal_department_id').value;
                if (!departmentId) {
                    errors['department_id'] = 'Please select a department';
                    isValid = false;
                }
            }

            // Batch validation
            if (isValid) {
                const batch = document.getElementById('modal_batch').value.trim();
                if (!batch) {
                    errors['batch'] = 'Batch is required';
                    isValid = false;
                }
            }

            // Semester validation
            if (isValid) {
                const semester = document.getElementById('modal_semester').value.trim();
                if (!semester) {
                    errors['semester'] = 'Semester is required';
                    isValid = false;
                }
            }

            // Display ONLY THE FIRST error
            document.querySelectorAll('#addStudentModal .error-message').forEach(el => {
                el.textContent = '';
                el.classList.remove('show');
                el.style.cssText = 'display: none !important;';
            });
            
            // Show only the first error that occurred
            if (!isValid && Object.keys(errors).length > 0) {
                const firstErrorField = Object.keys(errors)[0];
                const input = document.getElementById('modal_' + firstErrorField);
                if (input) {
                    const errorEl = input.parentElement.querySelector('.error-message');
                    if (errorEl) {
                        errorEl.textContent = errors[firstErrorField];
                        errorEl.classList.add('show');
                        errorEl.style.cssText = 'display: block !important;';
                        console.log(`❌ Validation error: ${firstErrorField} - ${errors[firstErrorField]}`);
                    }
                }
            }

            return isValid;
        }

        const STUDENT_VALIDATION_MESSAGES = {
            name: {
                required: 'Enter the student\'s full name.',
                min: 'Full name must be at least 2 characters long.',
                format: 'Full name can use letters and spaces only.',
            },
            email: {
                required: 'Enter the student\'s email address.',
                format: 'Enter a valid email address, like student@example.com.',
                unique: 'This email is already assigned to another user.',
            },
            phone: {
                required: 'Enter the student\'s phone number with country code.',
                format: 'Enter a valid phone number with country code, like +9779812345678.',
                unique: 'This phone number is already assigned to another user.',
            },
            date_of_birth: {
                required: 'Select the student\'s date of birth.',
                invalid: 'Enter a valid date of birth.',
                future: 'Date of birth must be earlier than today.',
                age: 'Student age must be between 14 and 100 years.',
            },
            roll_no: {
                required: 'Enter the student ID.',
                min: 'Student ID must be at least 3 characters long.',
                format: 'Student ID can use letters, numbers, and hyphens only.',
                unique: 'This student ID is already in use.',
            },
            department_id: {
                required: 'Select a department.',
            },
            batch: {
                required: 'Enter the batch year.',
                format: 'Batch year must be a 4-digit year.',
            },
            semester: {
                required: 'Enter the semester number.',
                format: 'Semester must be a number between 1 and 12.',
            },
            address: {
                required: 'Enter the student\'s address.',
                min: 'Address must be at least 10 characters long.',
                unsafe: 'Address contains unsupported characters. Remove any HTML or script-like content.',
            },
            status: {
                required: 'Select the student status.',
            },
        };

        const STUDENT_FIELD_ORDER = ['name', 'email', 'phone', 'date_of_birth', 'roll_no', 'department_id', 'batch', 'semester', 'address', 'status'];
        const STUDENT_UNIQUE_FIELDS = new Set(['email', 'phone', 'roll_no']);

        function normalizeStudentFieldValue(fieldName, value) {
            const rawValue = String(value ?? '');

            switch (fieldName) {
                case 'name':
                case 'address':
                    return rawValue.replace(/\s+/g, ' ').trim();
                case 'email':
                    return rawValue.trim().toLowerCase();
                case 'phone': {
                    const trimmed = rawValue.trim();
                    const digits = trimmed.replace(/\D/g, '');

                    if (!trimmed) {
                        return '';
                    }

                    return trimmed.startsWith('+') ? `+${digits}` : digits;
                }
                case 'roll_no':
                    return rawValue.trim().toUpperCase();
                default:
                    return rawValue.trim();
            }
        }

        function normalizeStudentLiveFieldValue(fieldName, value) {
            const rawValue = String(value ?? '');

            if (fieldName === 'name') {
                return rawValue
                    .replace(/^\s+/, '')
                    .replace(/\s{2,}/g, ' ');
            }

            return normalizeStudentFieldValue(fieldName, value);
        }

        function getStudentAge(dateOfBirth) {
            if (!dateOfBirth) {
                return null;
            }

            const birthDate = new Date(dateOfBirth);
            if (Number.isNaN(birthDate.getTime())) {
                return null;
            }

            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age -= 1;
            }

            return age;
        }

        function showStudentToast(message, type = 'info') {
            renderStudentToast(message, type);
        }

        class LiveStudentFormValidator {
            constructor({
                formId,
                modalId,
                prefix,
                summaryId,
                submitUrl,
                successMessage,
                submitMethod = 'POST',
                includeStatus = false,
                validateSeedValues = true,
            }) {
                this.form = document.getElementById(formId);
                this.modal = document.getElementById(modalId);
                this.prefix = prefix;
                this.summary = document.getElementById(summaryId);
                this.submitButton = this.form?.querySelector('.student-submit-btn');
                this.submitUrl = submitUrl;
                this.successMessage = successMessage;
                this.submitMethod = submitMethod;
                this.includeStatus = includeStatus;
                this.validateSeedValues = validateSeedValues;
                this.fieldNames = STUDENT_FIELD_ORDER.filter(fieldName => (fieldName !== 'status' || includeStatus) && this.getField(fieldName));
                this.abortControllers = {};
                this.pendingFields = new Set();
                this.touchedFields = new Set();
                this.verifiedValues = {};
                this.fieldState = {};
                this.isSubmitting = false;

                if (this.submitButton && !this.submitButton.dataset.defaultLabel) {
                    this.submitButton.dataset.defaultLabel = this.submitButton.textContent.trim();
                }

                this.ensureFieldIcons();
                this.attachListeners();
                this.updateSubmitState();
            }

            getField(fieldName) {
                return document.getElementById(`${this.prefix}_${fieldName}`);
            }

            getFieldGroup(fieldName) {
                return this.getField(fieldName)?.closest('.student-form-field') ?? null;
            }

            getFieldError(fieldName) {
                return this.getFieldGroup(fieldName)?.querySelector('.error-message') ?? null;
            }

            getModalPanel() {
                return this.modal?.firstElementChild ?? null;
            }

            ensureFieldIcons() {
                this.fieldNames.forEach(fieldName => {
                    const group = this.getFieldGroup(fieldName);
                    if (!group || group.querySelector('.field-validation-icon')) {
                        return;
                    }

                    const icon = document.createElement('span');
                    icon.className = 'field-validation-icon';
                    icon.setAttribute('aria-hidden', 'true');
                    group.appendChild(icon);
                });
            }

            attachListeners() {
                if (!this.form) {
                    return;
                }

                this.fieldNames.forEach(fieldName => {
                    const field = this.getField(fieldName);
                    const triggerEvent = field.tagName === 'SELECT' || field.type === 'date' ? 'change' : 'input';

                    field.addEventListener(triggerEvent, () => {
                        const normalizedValue = triggerEvent === 'input'
                            ? normalizeStudentLiveFieldValue(fieldName, field.value)
                            : normalizeStudentFieldValue(fieldName, field.value);
                        field.value = normalizedValue;
                        this.touchedFields.add(fieldName);
                        this.clearSummary();

                        if (STUDENT_UNIQUE_FIELDS.has(fieldName) && this.verifiedValues[fieldName] !== normalizedValue) {
                            delete this.verifiedValues[fieldName];
                        }

                        this.validateField(fieldName, {
                            showSummary: false,
                            runUniqueCheck: false,
                            syncFields: triggerEvent !== 'input',
                        });
                    });

                    field.addEventListener('blur', () => {
                        field.value = normalizeStudentFieldValue(fieldName, field.value);
                        this.touchedFields.add(fieldName);
                        this.validateField(fieldName, { showSummary: false, runUniqueCheck: true });
                    });
                });

                this.form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (this.isSubmitting) {
                        return;
                    }

                    const isValid = await this.validateAll({ showSummary: true, showFirstErrorOnly: true });
                    if (!isValid) {
                        showStudentToast({
                            title: 'Check Required Fields',
                            message: 'Review the highlighted field before saving.',
                            detail: 'Only the first invalid field is highlighted.',
                            icon: 'fas fa-circle-exclamation',
                        }, 'warning');
                        return;
                    }

                    await this.submit();
                });
            }

            getValues({ syncFields = true } = {}) {
                const values = {};
                this.fieldNames.forEach(fieldName => {
                    const field = this.getField(fieldName);
                    const normalizedValue = normalizeStudentFieldValue(fieldName, field.value);
                    if (syncFields) {
                        field.value = normalizedValue;
                    }
                    values[fieldName] = normalizedValue;
                });
                return values;
            }

            clearSummary() {
                if (!this.summary) {
                    return;
                }

                this.summary.textContent = '';
                this.summary.hidden = true;
            }

            showSummary(message, focusSummary = false) {
                if (!this.summary) {
                    return;
                }

                this.summary.innerHTML = `<i class="fas fa-circle-exclamation" aria-hidden="true"></i>${escapeStudentHtml(message || 'Please review the highlighted fields before saving.')}`;
                this.summary.hidden = false;

                if (focusSummary) {
                    this.summary.focus({ preventScroll: true });
                }
            }

            focusField(fieldName) {
                const field = this.getField(fieldName);
                const target = this.getFieldGroup(fieldName) ?? field;
                const panel = this.getModalPanel();

                if (!field || !target) {
                    return;
                }

                if (panel) {
                    const panelRect = panel.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();
                    const nextScrollTop = panel.scrollTop
                        + (targetRect.top - panelRect.top)
                        - (panel.clientHeight / 2)
                        + (targetRect.height / 2);

                    panel.scrollTo({
                        top: Math.max(0, nextScrollTop),
                        behavior: 'smooth',
                    });
                } else {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                field.focus({ preventScroll: true });
            }

            clearDisplayedError(fieldName, { clearValidityOnly = false } = {}) {
                const group = this.getFieldGroup(fieldName);
                const field = this.getField(fieldName);
                const icon = group?.querySelector('.field-validation-icon');
                const error = this.getFieldError(fieldName);

                if (!field) {
                    return;
                }

                group?.classList.remove('has-invalid', 'has-pending');
                field.classList.remove('error', 'pending');
                field.removeAttribute('aria-invalid');

                if (error) {
                    error.innerHTML = '';
                    error.style.display = 'none';
                }

                this.pendingFields.delete(fieldName);

                if (!clearValidityOnly) {
                    group?.classList.remove('has-valid');
                    field.classList.remove('valid');
                }

                const isValid = field.classList.contains('valid');
                if (isValid) {
                    group?.classList.add('has-valid');
                    if (icon) {
                        icon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    }
                } else if (icon) {
                    icon.innerHTML = '';
                }

                this.fieldState[fieldName] = { valid: isValid };
                this.updateSubmitState();
            }

            clearDisplayedErrors({ clearValidityOnly = false } = {}) {
                this.fieldNames.forEach(fieldName => this.clearDisplayedError(fieldName, { clearValidityOnly }));
            }

            setNeutral(fieldName) {
                const group = this.getFieldGroup(fieldName);
                const field = this.getField(fieldName);
                const icon = group?.querySelector('.field-validation-icon');
                const error = this.getFieldError(fieldName);

                group?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                field?.classList.remove('error', 'valid', 'pending');
                field?.removeAttribute('aria-invalid');
                if (icon) icon.innerHTML = '';
                if (error) {
                    error.innerHTML = '';
                    error.style.display = 'none';
                }

                this.pendingFields.delete(fieldName);
                this.fieldState[fieldName] = { valid: false };
                this.updateSubmitState();
            }

            setState(fieldName, state, message = '', { showValidState = true, showPendingState = true } = {}) {
                const group = this.getFieldGroup(fieldName);
                const field = this.getField(fieldName);
                const icon = group?.querySelector('.field-validation-icon');
                const error = this.getFieldError(fieldName);
                const renderValidState = state !== 'valid' || showValidState;
                const renderPendingState = state !== 'pending' || showPendingState;
                const shouldRenderState = state === 'invalid'
                    || (state === 'valid' && renderValidState)
                    || (state === 'pending' && renderPendingState);

                group?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                field?.classList.remove('error', 'valid', 'pending');
                if (shouldRenderState) {
                    group?.classList.add(`has-${state}`);
                }

                if (field) {
                    field.setAttribute('aria-invalid', state === 'invalid' ? 'true' : 'false');
                    if (state === 'invalid') {
                        field.classList.add('error');
                    } else if (state === 'valid' && renderValidState) {
                        field.classList.add('valid');
                    } else if (state === 'pending' && renderPendingState) {
                        field.classList.add('pending');
                    }
                }

                if (icon) {
                    icon.innerHTML = !shouldRenderState
                        ? ''
                        : state === 'valid'
                            ? '<i class="fas fa-check-circle"></i>'
                            : state === 'invalid'
                                ? '<i class="fas fa-exclamation-circle"></i>'
                                : '<i class="fas fa-spinner fa-spin"></i>';
                }

                if (error) {
                    error.innerHTML = state === 'invalid'
                        ? `<i class="fas fa-exclamation-circle" aria-hidden="true"></i>${escapeStudentHtml(message)}`
                        : '';
                    error.style.display = state === 'invalid' ? 'flex' : 'none';
                }

                if (state === 'pending') {
                    this.pendingFields.add(fieldName);
                    this.fieldState[fieldName] = { valid: false };
                } else {
                    this.pendingFields.delete(fieldName);
                    this.fieldState[fieldName] = { valid: state === 'valid' };
                }

                this.updateSubmitState();
            }

            getSyncMessage(fieldName, values) {
                const value = values[fieldName] ?? '';

                switch (fieldName) {
                    case 'name':
                        if (!value) return STUDENT_VALIDATION_MESSAGES.name.required;
                        if (value.length < 2) return STUDENT_VALIDATION_MESSAGES.name.min;
                        if (!/^[A-Za-z ]+$/.test(value)) return STUDENT_VALIDATION_MESSAGES.name.format;
                        return '';
                    case 'email':
                        if (!value) return STUDENT_VALIDATION_MESSAGES.email.required;
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return STUDENT_VALIDATION_MESSAGES.email.format;
                        return '';
                    case 'phone':
                        if (!value) return STUDENT_VALIDATION_MESSAGES.phone.required;
                        if (!/^\+[1-9]\d{7,14}$/.test(value)) return STUDENT_VALIDATION_MESSAGES.phone.format;
                        return '';
                    case 'date_of_birth': {
                        if (!value) return STUDENT_VALIDATION_MESSAGES.date_of_birth.required;
                        const birthDate = new Date(value);
                        if (Number.isNaN(birthDate.getTime())) return STUDENT_VALIDATION_MESSAGES.date_of_birth.invalid;
                        const today = new Date();
                        const todayMidnight = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                        if (birthDate >= todayMidnight) return STUDENT_VALIDATION_MESSAGES.date_of_birth.future;
                        const age = getStudentAge(value);
                        if (age === null) return STUDENT_VALIDATION_MESSAGES.date_of_birth.invalid;
                        if (age < 14 || age > 100) return STUDENT_VALIDATION_MESSAGES.date_of_birth.age;
                        return '';
                    }
                    case 'roll_no':
                        if (!value) return STUDENT_VALIDATION_MESSAGES.roll_no.required;
                        if (value.length < 3) return STUDENT_VALIDATION_MESSAGES.roll_no.min;
                        if (!/^[A-Za-z0-9-]+$/.test(value)) return STUDENT_VALIDATION_MESSAGES.roll_no.format;
                        return '';
                    case 'department_id':
                        return value ? '' : STUDENT_VALIDATION_MESSAGES.department_id.required;
                    case 'batch':
                        if (!value) return STUDENT_VALIDATION_MESSAGES.batch.required;
                        if (!/^(19|20)\d{2}$/.test(value)) return STUDENT_VALIDATION_MESSAGES.batch.format;
                        return '';
                    case 'semester':
                        if (!value) return STUDENT_VALIDATION_MESSAGES.semester.required;
                        if (!/^(?:[1-9]|1[0-2])$/.test(value)) return STUDENT_VALIDATION_MESSAGES.semester.format;
                        return '';
                    case 'address':
                        if (!value) return STUDENT_VALIDATION_MESSAGES.address.required;
                        if (value.length < 10) return STUDENT_VALIDATION_MESSAGES.address.min;
                        if (/<[^>]*>/.test(value)) return STUDENT_VALIDATION_MESSAGES.address.unsafe;
                        return '';
                    case 'status':
                        return value ? '' : STUDENT_VALIDATION_MESSAGES.status.required;
                    default:
                        return '';
                }
            }

            async runUniqueValidation(fieldName, value, showSummary, { showValidState = true, showPendingState = true } = {}) {
                if (!STUDENT_UNIQUE_FIELDS.has(fieldName) || !value) {
                    return true;
                }

                if (this.verifiedValues[fieldName] === value) {
                    this.setState(fieldName, 'valid', '', { showValidState });
                    return true;
                }

                this.abortControllers[fieldName]?.abort();
                const controller = new AbortController();
                this.abortControllers[fieldName] = controller;
                this.setState(fieldName, 'pending', '', { showPendingState });

                try {
                    const response = await fetch('{{ route('admin.students.validate-field') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin',
                        signal: controller.signal,
                        body: JSON.stringify({
                            field: fieldName,
                            [fieldName]: value,
                            student_id: this.form?.dataset.studentId || null,
                        }),
                    });

                    if (!response.ok) {
                        const data = await response.json();
                        const message = data.message || STUDENT_VALIDATION_MESSAGES[fieldName]?.unique || 'This value is already in use.';
                        this.setState(fieldName, 'invalid', message);
                        if (showSummary) {
                            this.showSummary(message, false);
                        }
                        return false;
                    }

                    if (this.getValues()[fieldName] !== value) {
                        return false;
                    }

                    this.verifiedValues[fieldName] = value;
                    this.setState(fieldName, 'valid', '', { showValidState });
                    return true;
                } catch (error) {
                    if (error.name === 'AbortError') {
                        return false;
                    }

                    const message = 'Could not verify this field right now. Please try again.';
                    this.setState(fieldName, 'invalid', message);
                    if (showSummary) {
                        this.showSummary(message, false);
                    }
                    return false;
                }
            }

            async validateField(fieldName, {
                showSummary = false,
                runUniqueCheck = false,
                showValidState = true,
                showPendingState = true,
                syncFields = true,
            } = {}) {
                const values = this.getValues({ syncFields });
                const syncMessage = this.getSyncMessage(fieldName, values);

                if (syncMessage) {
                    this.setState(fieldName, 'invalid', syncMessage);
                    if (showSummary) {
                        this.showSummary(syncMessage, false);
                    }
                    return false;
                }

                if (STUDENT_UNIQUE_FIELDS.has(fieldName)) {
                    if (!runUniqueCheck) {
                        // Keep the form submittable once the local format is valid.
                        // Uniqueness is still verified on blur and again on submit.
                        this.setState(fieldName, 'valid', '', { showValidState });
                        return true;
                    }

                    return this.runUniqueValidation(fieldName, values[fieldName], showSummary, {
                        showValidState,
                        showPendingState,
                    });
                }

                this.setState(fieldName, 'valid', '', { showValidState });
                return true;
            }

            async validateAll({ showSummary = true, focusSummary = false, showFirstErrorOnly = false } = {}) {
                this.clearSummary();
                if (showFirstErrorOnly) {
                    this.clearDisplayedErrors({ clearValidityOnly: true });
                }

                let firstInvalidField = null;
                let firstInvalidMessage = '';
                let allValid = true;

                for (const fieldName of this.fieldNames) {
                    const isTouched = this.touchedFields.has(fieldName);
                    const isValid = await this.validateField(fieldName, {
                        showSummary: false,
                        runUniqueCheck: true,
                        showValidState: isTouched,
                        showPendingState: isTouched,
                    });
                    if (!isValid) {
                        allValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = fieldName;
                            firstInvalidMessage = this.getFieldError(fieldName)?.textContent?.trim() || '';
                            if (showFirstErrorOnly) {
                                break;
                            }
                        }
                    }
                }

                if (!allValid && firstInvalidField) {
                    if (showSummary) {
                        this.showSummary(firstInvalidMessage || 'Please review the highlighted field before saving.', focusSummary);
                    }
                    this.focusField(firstInvalidField);
                }

                return allValid;
            }

            applyServerErrors(errors = {}, { showFirstErrorOnly = false } = {}) {
                this.clearSummary();
                this.clearDisplayedErrors({ clearValidityOnly: true });

                let firstField = '';

                for (const [fieldName, fieldErrors] of Object.entries(errors)) {
                    if (showFirstErrorOnly && firstField) {
                        break;
                    }

                    const message = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors;
                    if (!message || !this.getField(fieldName)) {
                        continue;
                    }

                    this.setState(fieldName, 'invalid', message);

                    if (!firstField) {
                        firstField = fieldName;
                    }
                }

                if (firstField) {
                    this.focusField(firstField);
                }
            }

            resetForm() {
                this.form?.reset();
                this.clearSummary();
                this.pendingFields.clear();
                this.touchedFields.clear();
                this.verifiedValues = {};
                Object.values(this.abortControllers).forEach(controller => controller?.abort());
                this.abortControllers = {};
                this.fieldNames.forEach(fieldName => this.setNeutral(fieldName));
                this.updateSubmitState();
            }

            seed(values = {}) {
                this.resetForm();

                this.fieldNames.forEach(fieldName => {
                    const field = this.getField(fieldName);
                    if (!field) {
                        return;
                    }

                    const normalizedValue = normalizeStudentFieldValue(fieldName, values[fieldName] ?? '');
                    field.value = normalizedValue;

                    if (!this.validateSeedValues) {
                        return;
                    }

                    if (STUDENT_UNIQUE_FIELDS.has(fieldName) && normalizedValue) {
                        this.verifiedValues[fieldName] = normalizedValue;
                        this.setState(fieldName, 'valid');
                    } else {
                        this.validateField(fieldName, { showSummary: false, runUniqueCheck: false });
                    }
                });

                this.updateSubmitState();
            }

            updateSubmitState() {
                if (!this.submitButton) {
                    return;
                }

                this.submitButton.disabled = false;
                this.submitButton.removeAttribute('disabled');
                this.submitButton.dataset.submitting = this.isSubmitting ? 'true' : 'false';
                if (this.isSubmitting) {
                    this.submitButton.setAttribute('aria-busy', 'true');
                } else {
                    this.submitButton.removeAttribute('aria-busy');
                }
                this.submitButton.dataset.pendingValidation = this.pendingFields.size > 0 ? 'true' : 'false';
            }

            setSubmittingState(isSubmitting) {
                if (!this.submitButton) {
                    return;
                }

                this.isSubmitting = isSubmitting;
                const defaultLabel = this.submitButton.dataset.defaultLabel || this.submitButton.textContent.trim() || 'Save';
                const busyLabel = this.submitMethod === 'POST' ? 'Saving...' : 'Updating...';

                if (isSubmitting) {
                    this.submitButton.innerHTML = `<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> ${busyLabel}`;
                } else {
                    this.submitButton.textContent = defaultLabel;
                }

                this.updateSubmitState();
            }

            async submit() {
                if (this.isSubmitting) {
                    return;
                }

                const values = this.getValues();
                const requestUrl = manager
                    ? manager.buildMutationUrl(this.submitMethod === 'POST' ? this.submitUrl : this.form.action)
                    : (this.submitMethod === 'POST' ? this.submitUrl : this.form.action);

                try {
                    this.setSubmittingState(true);
                    let response;
                    let formData = null;

                    if (this.submitMethod === 'POST') {
                        formData = new FormData(this.form);
                        Object.entries(values).forEach(([fieldName, fieldValue]) => formData.set(fieldName, fieldValue));

                        response = await fetch(requestUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });
                    }

                    if (this.submitMethod !== 'POST') {
                        response = await fetch(requestUrl, {
                            method: this.submitMethod,
                            body: JSON.stringify(values),
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            credentials: 'same-origin',
                        });
                    }

                    if (!response.ok && response.status === 422) {
                        const data = await response.json();
                        this.applyServerErrors(data.errors || {}, { showFirstErrorOnly: true });
                        this.showSummary(data.message || 'Please correct the highlighted field and try again.', false);
                        showStudentToast({
                            title: 'Check Required Fields',
                            message: 'Please correct the highlighted field and try again.',
                            detail: 'Only the first invalid field is highlighted.',
                            icon: 'fas fa-circle-exclamation',
                        }, 'warning');
                        return;
                    }

                    const data = await response.json();
                    if (!data.success) {
                        const message = data.message || 'Unable to save the student right now. Please try again.';
                        this.showSummary(message, false);
                        showStudentToast({
                            title: 'Unable to Save Student',
                            message,
                            icon: 'fas fa-circle-xmark',
                        }, 'error');
                        return;
                    }

                    this.modal.style.display = 'none';
                    this.resetForm();
                    const mutationType = this.submitMethod === 'POST' ? 'create' : 'update';
                    const toastType = mutationType === 'create' ? 'success' : 'info';
                    const toastPayload = {
                        title: mutationType === 'create' ? 'Student Added' : 'Student Updated',
                        message: `${mutationType === 'create' ? 'Created a new record for' : 'Saved changes for'} ${data.student?.name || 'the student'}.`,
                        detail: data.student?.rollNo ? `Student ID ${data.student.rollNo}` : '',
                        icon: mutationType === 'create' ? 'fas fa-user-plus' : 'fas fa-user-pen',
                    };

                    if (manager) {
                        await manager.handleStudentMutation(mutationType, data);
                    }

                    showStudentToast(toastPayload, toastType);
                    announceStudentMessage(`${data.student?.name || 'Student'} ${mutationType === 'create' ? 'created' : 'updated'} successfully.`);
                } catch (error) {
                    console.error('Student form submission failed:', error);
                    const message = 'Unable to save the student right now. Please try again.';
                    this.showSummary(message, false);
                    showStudentToast({
                        title: 'Unable to Save Student',
                        message,
                        icon: 'fas fa-circle-xmark',
                    }, 'error');
                } finally {
                    this.setSubmittingState(false);
                }
            }
        }

        let addStudentValidator;
        let editStudentValidator;

        window.openEditStudentModal = function(studentId) {
            const modal = document.getElementById('editStudentModal');
            const form = document.getElementById('editStudentForm');

            fetch(`{{ url('admin/students') }}/${studentId}/edit-data`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Error loading student data');
                    }

                    form.action = `{{ url('admin/students') }}/${studentId}`;
                    form.dataset.studentId = studentId;
                    editStudentValidator.seed(data.data);
                    modal.style.display = 'flex';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showStudentToast({
                        title: 'Unable to Load Student',
                        message: 'We could not open the student editor right now.',
                        icon: 'fas fa-circle-xmark',
                    }, 'error');
                });
        };

        function closeEditStudentModal() {
            const modal = document.getElementById('editStudentModal');
            const form = document.getElementById('editStudentForm');

            if (modal) {
                modal.style.display = 'none';
            }

            if (form) {
                form.dataset.studentId = '';
            }

            editStudentValidator?.resetForm();
        }

        function openAddStudentModal() {
            const modal = document.getElementById('addStudentModal');
            if (modal) {
                addStudentValidator?.resetForm();
                modal.style.display = 'flex';
            }
        }

        function closeAddStudentModal() {
            const modal = document.getElementById('addStudentModal');
            if (modal) {
                modal.style.display = 'none';
            }

            addStudentValidator?.resetForm();
        }

        document.addEventListener('DOMContentLoaded', () => {
            addStudentValidator = new LiveStudentFormValidator({
                formId: 'addStudentForm',
                modalId: 'addStudentModal',
                prefix: 'modal',
                summaryId: 'addStudentValidationSummary',
                submitUrl: '{{ route('admin.students.store') }}',
                successMessage: 'Student added successfully!',
            });

            editStudentValidator = new LiveStudentFormValidator({
                formId: 'editStudentForm',
                modalId: 'editStudentModal',
                prefix: 'edit',
                summaryId: 'editStudentValidationSummary',
                submitUrl: '',
                successMessage: 'Student updated successfully!',
                submitMethod: 'PUT',
                includeStatus: true,
                validateSeedValues: false,
            });

            const addModal = document.getElementById('addStudentModal');
            if (addModal) {
                addModal.addEventListener('click', (e) => {
                    if (e.target === addModal) {
                        closeAddStudentModal();
                    }
                });
            }

            const editModal = document.getElementById('editStudentModal');
            if (editModal) {
                editModal.addEventListener('click', (e) => {
                    if (e.target === editModal) {
                        closeEditStudentModal();
                    }
                });
            }
        });

        let manager;
        document.addEventListener('DOMContentLoaded', () => {
            manager = new StudentManager();
        });
    </script>
@endpush
