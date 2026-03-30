@extends('Admin.layouts.app')

@section('title', 'Students')

@push('styles')
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
            display: block;
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

        .student-submit-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed !important;
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
                <input type="text" class="search-input" id="searchInput" placeholder="Search by name, email, or roll number..." autocomplete="off">
            </div>

            <div class="filters-container">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <select class="filter-select" id="departmentFilter">
                    <option value="all">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>

                <select class="filter-select" id="sortFilter">
                    <option value="created-desc">Newest First</option>
                    <option value="created-asc">Oldest First</option>
                    <option value="name-asc">Alphabetical A-Z</option>
                    <option value="name-desc">Alphabetical Z-A</option>
                </select>

                <button id="resetFiltersBtn" class="reset-btn" title="Reset all filters">
                    <i class="fas fa-redo"></i>
                    Reset
                </button>
            </div>

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

@endsection

@push('scripts')
    <script>
        class StudentManager {
            constructor() {
                this.currentDepartment = 'all';
                this.currentStatus = 'all';
                this.currentSort = 'created-desc';
                this.searchDebounceTimer = null;
                this.selectedRowIndex = -1;
                this.currentPage = 1;
                this.totalRows = 0;
                this.rowsPerPage = 10;
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
                const resetBtn = document.getElementById('resetFiltersBtn');

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

                fetch(`{{ route('admin.students.data') }}?search=${encodeURIComponent(search)}&department=${department}&status=${status}&sort=${sort}&page=${page}`, {
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

        function showStudentToast(message, background = '#10b981') {
            const toast = document.createElement('div');
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${background};
                color: white;
                padding: 12px 16px;
                border-radius: 6px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                z-index: 2000;
                font-size: 14px;
                font-weight: 500;
            `;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        class LiveStudentFormValidator {
            constructor({ formId, modalId, prefix, summaryId, submitUrl, successMessage, submitMethod = 'POST', includeStatus = false }) {
                this.form = document.getElementById(formId);
                this.modal = document.getElementById(modalId);
                this.prefix = prefix;
                this.summary = document.getElementById(summaryId);
                this.submitButton = this.form?.querySelector('.student-submit-btn');
                this.submitUrl = submitUrl;
                this.successMessage = successMessage;
                this.submitMethod = submitMethod;
                this.includeStatus = includeStatus;
                this.fieldNames = STUDENT_FIELD_ORDER.filter(fieldName => (fieldName !== 'status' || includeStatus) && this.getField(fieldName));
                this.abortControllers = {};
                this.pendingFields = new Set();
                this.verifiedValues = {};
                this.fieldState = {};

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
                        const normalizedValue = normalizeStudentFieldValue(fieldName, field.value);
                        field.value = normalizedValue;
                        this.clearSummary();

                        if (STUDENT_UNIQUE_FIELDS.has(fieldName) && this.verifiedValues[fieldName] !== normalizedValue) {
                            delete this.verifiedValues[fieldName];
                        }

                        this.validateField(fieldName, { showSummary: false, runUniqueCheck: false });
                    });

                    field.addEventListener('blur', () => {
                        this.validateField(fieldName, { showSummary: true, runUniqueCheck: true });
                    });
                });

                this.form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const isValid = await this.validateAll({ showSummary: true, focusSummary: true });
                    if (!isValid) {
                        return;
                    }

                    await this.submit();
                });
            }

            getValues() {
                const values = {};
                this.fieldNames.forEach(fieldName => {
                    const field = this.getField(fieldName);
                    const normalizedValue = normalizeStudentFieldValue(fieldName, field.value);
                    field.value = normalizedValue;
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
                this.clearSummary();
            }

            setNeutral(fieldName) {
                const group = this.getFieldGroup(fieldName);
                const field = this.getField(fieldName);
                const icon = group?.querySelector('.field-validation-icon');
                const error = this.getFieldError(fieldName);

                group?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                field?.removeAttribute('aria-invalid');
                if (icon) icon.innerHTML = '';
                if (error) {
                    error.textContent = '';
                    error.style.display = 'none';
                }

                this.pendingFields.delete(fieldName);
                this.fieldState[fieldName] = { valid: false };
                this.updateSubmitState();
            }

            setState(fieldName, state, message = '') {
                const group = this.getFieldGroup(fieldName);
                const field = this.getField(fieldName);
                const icon = group?.querySelector('.field-validation-icon');
                const error = this.getFieldError(fieldName);

                group?.classList.remove('has-valid', 'has-invalid', 'has-pending');
                group?.classList.add(`has-${state}`);

                if (field) {
                    field.setAttribute('aria-invalid', state === 'invalid' ? 'true' : 'false');
                }

                if (icon) {
                    icon.innerHTML = state === 'valid'
                        ? '<i class="fas fa-check-circle"></i>'
                        : state === 'invalid'
                            ? '<i class="fas fa-exclamation-circle"></i>'
                            : '<i class="fas fa-spinner fa-spin"></i>';
                }

                if (error) {
                    error.textContent = message;
                    error.style.display = state === 'invalid' ? 'block' : 'none';
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

            async runUniqueValidation(fieldName, value, showSummary) {
                if (!STUDENT_UNIQUE_FIELDS.has(fieldName) || !value) {
                    return true;
                }

                if (this.verifiedValues[fieldName] === value) {
                    this.setState(fieldName, 'valid');
                    return true;
                }

                this.abortControllers[fieldName]?.abort();
                const controller = new AbortController();
                this.abortControllers[fieldName] = controller;
                this.setState(fieldName, 'pending');

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
                    this.setState(fieldName, 'valid');
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

            async validateField(fieldName, { showSummary = false, runUniqueCheck = false } = {}) {
                const values = this.getValues();
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
                        this.setState(fieldName, 'valid');
                        return true;
                    }

                    return this.runUniqueValidation(fieldName, values[fieldName], showSummary);
                }

                this.setState(fieldName, 'valid');
                return true;
            }

            async validateAll({ showSummary = true, focusSummary = false } = {}) {
                this.clearSummary();
                let firstInvalidField = null;
                let allValid = true;

                for (const fieldName of this.fieldNames) {
                    const isValid = await this.validateField(fieldName, { showSummary, runUniqueCheck: true });
                    if (!isValid) {
                        allValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = fieldName;
                        }
                    }
                }

                if (!allValid && firstInvalidField) {
                    this.getField(firstInvalidField)?.focus();
                }

                return allValid;
            }

            applyServerErrors(errors = {}) {
                this.clearSummary();

                let firstField = '';
                let firstMessage = '';

                Object.entries(errors).forEach(([fieldName, fieldErrors]) => {
                    const message = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors;
                    if (!message || !this.getField(fieldName)) {
                        return;
                    }

                    this.setState(fieldName, 'invalid', message);

                    if (!firstMessage) {
                        firstField = fieldName;
                        firstMessage = message;
                    }
                });

                if (firstMessage) {
                    this.getField(firstField)?.focus();
                }
            }

            resetForm() {
                this.form?.reset();
                this.clearSummary();
                this.pendingFields.clear();
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

                const hasPending = this.pendingFields.size > 0;
                const hasInvalid = this.fieldNames.some(fieldName => !this.fieldState[fieldName]?.valid);
                this.submitButton.disabled = hasPending || hasInvalid;
            }

            async submit() {
                const values = this.getValues();

                try {
                    let response;

                    if (this.submitMethod === 'POST') {
                        const formData = new FormData(this.form);
                        Object.entries(values).forEach(([fieldName, fieldValue]) => formData.set(fieldName, fieldValue));

                        response = await fetch(this.submitUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            credentials: 'same-origin',
                        });
                    } else {
                        response = await fetch(this.form.action, {
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
                        this.applyServerErrors(data.errors || {});
                        return;
                    }

                    const data = await response.json();
                    if (!data.success) {
                        this.showSummary(data.message || 'Unable to save the student right now. Please try again.', true);
                        return;
                    }

                    this.modal.style.display = 'none';
                    this.resetForm();
                    showStudentToast(this.successMessage);

                    if (manager) {
                        manager.fetchStudents(manager.currentPage);
                        manager.refreshStats();
                    }
                } catch (error) {
                    console.error('Student form submission failed:', error);
                    this.showSummary('Unable to save the student right now. Please try again.', true);
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
                    showStudentToast('Error loading student data', '#ef4444');
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
