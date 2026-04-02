@extends('Admin.layouts.app')

@section('title', 'Student Details')

@push('styles')
    @include('shared.report-export.styles')
    @include('shared.action-feedback.styles')
    <style>
        /* Student Details Page */
        .student-details-page {
            padding: 4px 14px 14px;
            width: 100%;
            max-width: none;
            margin: 0;
            box-sizing: border-box;
        }

        /* Header Row */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
            padding: 4px 0 10px;
            border-bottom: 1px solid;
        }

        body.light-theme .page-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .page-header {
            border-color: #334155;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        body.light-theme .back-link {
            color: #64748b;
        }

        body.dark-theme .back-link {
            color: #94a3b8;
        }

        body.light-theme .back-link:hover {
            background-color: #f1f5f9;
        }

        body.dark-theme .back-link:hover {
            background-color: #1e293b;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .header-right {
            display: flex;
            gap: 10px;
        }

        /* Buttons */
        .btn {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .btn-secondary {
            background-color: transparent;
        }

        body.light-theme .btn-secondary {
            color: #64748b;
            border-color: #e5e7eb;
        }

        body.dark-theme .btn-secondary {
            color: #94a3b8;
            border-color: #475569;
        }

        body.light-theme .btn-secondary:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .btn-secondary:hover {
            background-color: #1e293b;
        }

        /* Main Grid Layout */
        .main-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 16px;
            align-items: start;
        }

        /* Profile Column */
        .profile-card {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 14px;
            border: 1px solid;
        }

        body.light-theme .profile-card {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .profile-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        .avatar-container {
            display: flex;
            justify-content: center;
            margin-bottom: 14px;
        }

        .student-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: 600;
        }

        body.light-theme .student-avatar {
            background-color: #3b82f6;
            color: white;
        }

        body.dark-theme .student-avatar {
            background-color: #1e40af;
            color: white;
        }

        .student-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .student-info {
            text-align: center;
            margin-bottom: 16px;
        }

        .student-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        .student-id {
            font-size: 13px;
            margin: 0 0 10px 0;
        }

        body.light-theme .student-id {
            color: #64748b;
        }

        body.dark-theme .student-id {
            color: #94a3b8;
        }

        /* Status Badges */
        .status-badges {
            display: flex;
            gap: 6px;
            justify-content: center;
            margin-bottom: 16px;
        }

        .badge {
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .badge-success {
            background-color: #14532d;
            color: #4ade80;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.dark-theme .badge-info {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .badge-danger {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        /* Info List */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 13px;
        }

        .info-item svg {
            flex-shrink: 0;
            margin-top: 2px;
        }

        body.light-theme .info-item svg {
            color: #64748b;
        }

        body.dark-theme .info-item svg {
            color: #94a3b8;
        }

        .info-item span {
            line-height: 1.4;
        }

        /* System Info */
        .system-info {
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid;
        }

        body.light-theme .system-info {
            border-color: #e5e7eb;
        }

        body.dark-theme .system-info {
            border-color: #334155;
        }

        .system-info h4 {
            font-size: 13px;
            font-weight: 600;
            margin: 0 0 10px 0;
        }

        body.light-theme .system-info h4 {
            color: #64748b;
        }

        body.dark-theme .system-info h4 {
            color: #94a3b8;
        }

        .info-grid {
            display: grid;
            gap: 6px;
        }

        .info-grid .info-item {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 8px;
        }

        .info-label {
            font-size: 11px;
            font-weight: 500;
        }

        body.light-theme .info-label {
            color: #94a3b8;
        }

        body.dark-theme .info-label {
            color: #64748b;
        }

        .info-value {
            font-size: 12px;
            font-weight: 500;
        }

        /* Content Column */
        .content-column {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-height: 0;
            align-self: start;
            overflow: hidden;
        }

        /* Summary Cards Grid */
        .summary-cards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }

        .summary-card {
            border-radius: 8px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid;
        }

        body.light-theme .summary-card {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .summary-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        .summary-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        body.light-theme .summary-icon {
            background-color: #f8fafc;
            color: #3b82f6;
        }

        body.dark-theme .summary-icon {
            background-color: #0f172a;
            color: #60a5fa;
        }

        .summary-content {
            flex: 1;
            min-width: 0;
        }

        .summary-title {
            font-size: 11px;
            font-weight: 500;
            margin-bottom: 4px;
        }

        body.light-theme .summary-title {
            color: #64748b;
        }

        body.dark-theme .summary-title {
            color: #94a3b8;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
        }

        /* Issued Books Section */
        .issued-books-section {
            border-radius: 8px;
            padding: 14px;
            border: 1px solid;
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            max-height: none;
            overflow: hidden;
        }

        body.light-theme .issued-books-section {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .issued-books-section {
            background-color: #1e293b;
            border-color: #334155;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .section-header h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .search-filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            transition: all 0.3s ease;
        }

        body.dark-theme .search-filter-container {
            background: #1e293b;
            border-color: #334155;
        }

        .student-table-toolbar {
            margin-bottom: 12px;
        }

        .search-box {
            position: relative;
            flex: 0 1 240px;
            min-width: 220px;
            max-width: 280px;
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
            color: #64748b;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.25rem;
            border-radius: 0.375rem;
            font-size: 13px;
            border: 1px solid #e5e7eb;
            background-color: #f8fafc;
            color: #0f172a;
            transition: all 0.3s ease;
        }

        body.dark-theme .search-input {
            background-color: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }

        body.light-theme .search-input::placeholder {
            color: #94a3b8;
        }

        body.dark-theme .search-input::placeholder {
            color: #64748b;
        }

        .search-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filters-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }

        .filter-select {
            min-width: 132px;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 13px;
            cursor: pointer;
            appearance: none;
            border: 1px solid #e5e7eb;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            color: #0f172a;
            transition: all 0.3s ease;
        }

        body.dark-theme .filter-select {
            background: #334155 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.5rem center;
            border-color: #475569;
            color: #e2e8f0;
        }

        .student-toolbar-reset {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.5rem 0.95rem;
            border-radius: 0.375rem;
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            color: #334155;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease;
        }

        body.dark-theme .student-toolbar-reset {
            border-color: #475569;
            background: #0f172a;
            color: #e2e8f0;
        }

        .student-toolbar-reset:hover {
            border-color: #3b82f6;
            color: #2563eb;
            background: #eff6ff;
            transform: translateY(-1px);
        }

        body.dark-theme .student-toolbar-reset:hover {
            border-color: #60a5fa;
            color: #bfdbfe;
            background: #1e3a8a;
        }

        .student-table-toolbar .admin-table-entries-control {
            margin-left: auto;
        }

        .student-table-pagination {
            margin-top: 10px;
        }

        /* Books Table */
        .table-container {
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .paginated-table {
            overflow-y: auto;
        }

        .issued-books-section .paginated-table {
            flex: 1 1 auto;
            min-height: 0;
            max-height: none;
            height: 100%;
        }

        .fines-management-card .paginated-table {
            max-height: 360px;
        }

        .paginated-table::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .paginated-table::-webkit-scrollbar-track {
            background: transparent;
        }

        .paginated-table::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.42);
            border-radius: 999px;
        }

        body.dark-theme .paginated-table::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.55);
        }

        body.light-theme .table-container {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }
        body.dark-theme .table-container {
            background-color: #1e293b;
            border-color: #334155;
        }

        .books-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }

        .books-table th {
            padding: 5px 7px;
            text-align: left;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            border-bottom: 1px solid;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        body.light-theme .books-table th {
            color: #475569;
            border-color: #e2e8f0;
            background-color: #f8fafc;
        }

        body.dark-theme .books-table th {
            color: #cbd5e1;
            border-color: #334155;
            background-color: #1e293b;
        }

        .books-table td {
            padding: 5px 7px;
            border-bottom: 1px solid;
            vertical-align: middle;
            font-size: 12.5px;
            transition: border-color 0.3s, color 0.3s;
        }

        body.light-theme .books-table td {
            border-color: #e2e8f0;
            color: #0f172a;
        }

        body.dark-theme .books-table td {
            border-color: #334155;
            color: #f1f5f9;
        }

        .books-table tr:last-child td {
            border-bottom: none;
        }

        .books-table tbody tr:hover {
            transition: background-color 0.3s;
        }

        body.light-theme .books-table tbody tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .books-table tbody tr:hover {
            background-color: #2d3748;
        }

        .paginated-table::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .paginated-table::-webkit-scrollbar-track {
            background: transparent;
        }

        .paginated-table::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.42);
            border-radius: 999px;
        }

        body.dark-theme .paginated-table::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.55);
        }

        /* Status Badges in Table */
        .table-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 9px;
            border-radius: 16px;
            font-size: 10.5px;
            font-weight: 600;
            gap: 4px;
            transition: background 0.3s, color 0.3s;
            white-space: nowrap;
        }

        .table-badge i {
            display: inline-block;
            line-height: 1;
        }

        .table-badge.issued {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }

        body.dark-theme .table-badge.issued {
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
            color: #4ade80;
        }

        .table-badge.overdue {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        body.dark-theme .table-badge.overdue {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        .table-badge.returned {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #64748b;
        }

        body.dark-theme .table-badge.returned {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            color: #94a3b8;
        }

        /* Fine Amount */
        .fine-amount {
            font-weight: 600;
        }

        .fine-amount.has-fine {
            color: #dc2626;
        }

        body.dark-theme .fine-amount.has-fine {
            color: #f87171;
        }

        /* Action Buttons */
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
            transition: all 0.3s ease;
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
            background-color: #e0e7ff;
            color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        body.dark-theme .action-btn:hover {
            background-color: #1e40af;
            color: #93c5fd;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Legacy view-btn support */
        .view-btn {
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid;
            background: none;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        body.light-theme .view-btn {
            border-color: #e5e7eb;
            color: #3b82f6;
        }

        body.dark-theme .view-btn {
            border-color: #334155;
            color: #60a5fa;
        }

        body.light-theme .view-btn:hover {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        body.dark-theme .view-btn:hover {
            background-color: #1e40af;
            color: white;
            border-color: #1e40af;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .profile-column {
                max-width: 100%;
            }

            .summary-cards-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 768px) {
            .student-details-page {
                padding: 6px 12px 12px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .header-right {
                width: 100%;
                justify-content: flex-start;
            }

            .summary-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .student-table-toolbar {
                width: 100%;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
                max-width: none;
            }

            .filters-container {
                width: 100%;
            }

            .filter-select,
            .student-toolbar-reset {
                width: 100%;
            }

            .student-table-toolbar .admin-table-entries-control {
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }
        }

        @media (max-width: 480px) {
            .summary-cards-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                padding: 12px;
            }

            .issued-books-section {
                padding: 12px;
            }

            .books-table th,
            .books-table td {
                padding: 12px;
            }
        }


        /* Add this CSS to your push('styles') section - Place it AFTER your Part 1 CSS */

        /* Part 2 Styles */
        .details-section-part2 {
            margin-top: 20px;
            --student-management-card-height: clamp(360px, 52vh, 500px);
        }

        /* Main Grid for Part 2 */
        .main-grid-part2 {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .left-column-part2,
        .right-column-part2 {
            display: flex;
            min-height: 0;
        }

        /* Account Management Card */
        .account-management-card {
            border-radius: 8px;
            padding: 14px;
            border: 1px solid;
            width: 100%;
            height: var(--student-management-card-height);
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        body.light-theme .account-management-card {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .account-management-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        .account-management-card .card-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid;
        }

        body.light-theme .account-management-card .card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .account-management-card .card-header {
            border-color: #334155;
        }

        .header-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.light-theme .header-icon {
            background-color: #f0f4ff;
            color: #3b82f6;
        }

        body.dark-theme .header-icon {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .account-management-card h3 {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }

        /* Account Action Buttons */
        .account-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding-right: 4px;
        }

        .account-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid;
            transition: all 0.2s ease;
            width: 100%;
            text-align: left;
        }

        .account-btn svg {
            flex-shrink: 0;
        }

        /* Role Selection Modal */
        .role-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .role-modal-overlay.show {
            display: flex;
        }

        .role-modal {
            background-color: white;
            border-radius: 12px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: modalSlideIn 0.3s ease;
        }

        body.dark-theme .role-modal {
            background-color: #1e293b;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .role-modal-header {
            margin-bottom: 24px;
        }

        .role-modal-header h2 {
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 700;
        }

        .role-modal-header p {
            margin: 0;
            font-size: 14px;
        }

        body.light-theme .role-modal-header p {
            color: #64748b;
        }

        body.dark-theme .role-modal-header p {
            color: #94a3b8;
        }

        .role-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .role-option {
            padding: 14px;
            border: 2px solid;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        body.light-theme .role-option {
            border-color: #e5e7eb;
            background-color: #f8fafc;
        }

        body.dark-theme .role-option {
            border-color: #334155;
            background-color: #0f172a;
        }

        body.light-theme .role-option:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        body.dark-theme .role-option:hover {
            border-color: #3b82f6;
            background-color: #1e3a8a;
        }

        .role-option-icon {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .role-option-icon svg {
            width: 24px;
            height: 24px;
            stroke: currentColor;
        }

        .role-option.student .role-option-icon {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.dark-theme .role-option.student .role-option-icon {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .role-option.staff .role-option-icon {
            background-color: #fef3c7;
            color: #d97706;
        }

        body.dark-theme .role-option.staff .role-option-icon {
            background-color: #78350f;
            color: #fbbf24;
        }

        .role-option.admin .role-option-icon {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .role-option.admin .role-option-icon {
            background-color: #14532d;
            color: #4ade80;
        }

        .role-option-content {
            flex: 1;
        }

        .role-option-title {
            font-weight: 600;
            font-size: 15px;
            margin: 0 0 4px 0;
        }

        .role-option-description {
            font-size: 13px;
            margin: 0;
        }

        body.light-theme .role-option-description {
            color: #64748b;
        }

        body.dark-theme .role-option-description {
            color: #94a3b8;
        }

        .role-modal-actions {
            display: flex;
            gap: 12px;
        }

        .modal-btn {
            flex: 1;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .modal-btn-cancel {
            background-color: transparent;
            color: inherit;
        }

        body.light-theme .modal-btn-cancel {
            color: #64748b;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .modal-btn-cancel {
            color: #94a3b8;
            border: 1px solid #334155;
        }

        body.light-theme .modal-btn-cancel:hover {
            background-color: #f1f5f9;
        }

        body.dark-theme .modal-btn-cancel:hover {
            background-color: #1e293b;
        }

        .modal-btn-action {
            background-color: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
        }

        .modal-btn-action:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .modal-btn-action:disabled {
            background-color: #cbd5e1;
            border-color: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .btn-reset-password {
            background-color: #dbeafe;
            color: #2563eb;
            border-color: #dbeafe;
        }

        body.light-theme .btn-reset-password:hover {
            background-color: #bfdbfe;
        }

        body.dark-theme .btn-reset-password {
            background-color: #1e3a8a;
            color: #60a5fa;
            border-color: #1e3a8a;
        }

        body.dark-theme .btn-reset-password:hover {
            background-color: #1e40af;
        }

        .btn-suspend {
            background-color: #fef3c7;
            color: #d97706;
            border-color: #fef3c7;
        }

        body.light-theme .btn-suspend:hover {
            background-color: #fde68a;
        }

        body.dark-theme .btn-suspend {
            background-color: #78350f;
            color: #fbbf24;
            border-color: #78350f;
        }

        body.dark-theme .btn-suspend:hover {
            background-color: #92400e;
        }

        .btn-activate {
            background-color: #dcfce7;
            color: #16a34a;
            border-color: #dcfce7;
        }

        body.light-theme .btn-activate:hover {
            background-color: #bbf7d0;
        }

        body.dark-theme .btn-activate {
            background-color: #14532d;
            color: #4ade80;
            border-color: #14532d;
        }

        body.dark-theme .btn-activate:hover {
            background-color: #15803d;
        }

        .btn-deactivate {
            background-color: #fef3c7;
            color: #d97706;
            border-color: #fef3c7;
        }

        body.light-theme .btn-deactivate:hover {
            background-color: #fde68a;
        }

        body.dark-theme .btn-deactivate {
            background-color: #78350f;
            color: #fbbf24;
            border-color: #78350f;
        }

        body.dark-theme .btn-deactivate:hover {
            background-color: #92400e;
        }

        .btn-change-role {
            background-color: #f3e8ff;
            color: #7c3aed;
            border-color: #f3e8ff;
        }

        body.light-theme .btn-change-role:hover {
            background-color: #e9d5ff;
        }

        body.dark-theme .btn-change-role {
            background-color: #5b21b6;
            color: #c4b5fd;
            border-color: #5b21b6;
        }

        body.dark-theme .btn-change-role:hover {
            background-color: #6d28d9;
        }

        /* Confirmation Modal */
        .confirmation-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            backdrop-filter: blur(4px);
        }

        .confirmation-modal-overlay.show {
            display: flex;
        }

        .confirmation-modal {
            background-color: white;
            border-radius: 12px;
            max-width: 480px;
            width: calc(100% - 32px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: confirmationSlideIn 0.3s ease;
            overflow: hidden;
        }

        body.dark-theme .confirmation-modal {
            background-color: #1e293b;
        }

        @keyframes confirmationSlideIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .confirmation-modal-header {
            padding: 24px 24px 16px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .confirmation-modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .confirmation-modal-icon.warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #d97706;
        }

        body.dark-theme .confirmation-modal-icon.warning {
            background: linear-gradient(135deg, #78350f 0%, #451a03 100%);
            color: #fbbf24;
        }

        .confirmation-modal-icon.danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }

        body.dark-theme .confirmation-modal-icon.danger {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        .confirmation-modal-icon.info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #2563eb;
        }

        body.dark-theme .confirmation-modal-icon.info {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #60a5fa;
        }

        .confirmation-modal-content {
            flex: 1;
        }

        .confirmation-modal-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        body.light-theme .confirmation-modal-title {
            color: #1f2937;
        }

        body.dark-theme .confirmation-modal-title {
            color: #f1f5f9;
        }

        .confirmation-modal-message {
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        body.light-theme .confirmation-modal-message {
            color: #6b7280;
        }

        body.dark-theme .confirmation-modal-message {
            color: #94a3b8;
        }

        .confirmation-modal-body {
            padding: 0 24px 20px;
        }

        .confirmation-modal-details {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 12px 16px;
            margin-top: 12px;
        }

        body.dark-theme .confirmation-modal-details {
            background-color: #0f172a;
        }

        .confirmation-modal-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }

        .confirmation-modal-detail-label {
            font-weight: 500;
        }

        body.light-theme .confirmation-modal-detail-label {
            color: #6b7280;
        }

        body.dark-theme .confirmation-modal-detail-label {
            color: #94a3b8;
        }

        .confirmation-modal-detail-value {
            font-weight: 600;
        }

        body.light-theme .confirmation-modal-detail-value {
            color: #1f2937;
        }

        body.dark-theme .confirmation-modal-detail-value {
            color: #f1f5f9;
        }

        .confirmation-modal-actions {
            display: flex;
            gap: 12px;
            padding: 16px 24px;
            border-top: 1px solid;
        }

        body.light-theme .confirmation-modal-actions {
            border-color: #e5e7eb;
        }

        body.dark-theme .confirmation-modal-actions {
            border-color: #334155;
        }

        .confirmation-modal-btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }

        .confirmation-modal-btn-cancel {
            background-color: transparent;
        }

        body.light-theme .confirmation-modal-btn-cancel {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .confirmation-modal-btn-cancel {
            background-color: #334155;
            color: #cbd5e1;
            border: 1px solid #475569;
        }

        body.light-theme .confirmation-modal-btn-cancel:hover {
            background-color: #e2e8f0;
        }

        body.dark-theme .confirmation-modal-btn-cancel:hover {
            background-color: #475569;
        }

        .confirmation-modal-btn-confirm {
            color: white;
        }

        .confirmation-modal-btn-confirm.primary {
            background-color: #3b82f6;
        }

        .confirmation-modal-btn-confirm.primary:hover {
            background-color: #2563eb;
        }

        .confirmation-modal-btn-confirm.danger {
            background-color: #dc2626;
        }

        .confirmation-modal-btn-confirm.danger:hover {
            background-color: #b91c1c;
        }

        .confirmation-modal-btn-confirm.warning {
            background-color: #d97706;
        }

        .confirmation-modal-btn-confirm.warning:hover {
            background-color: #b45309;
        }

        .confirmation-modal-btn-confirm.success {
            background-color: #16a34a;
        }

        .confirmation-modal-btn-confirm.success:hover {
            background-color: #15803d;
        }

        .confirmation-modal-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Book Details Modal */
        .book-details-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .book-details-overlay.show {
            display: flex;
        }

        .book-details-modal {
            background-color: white;
            border-radius: 12px;
            max-width: 860px;
            width: 92%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s ease;
        }

        body.dark-theme .book-details-modal {
            background-color: #1e293b;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .book-details-header {
            position: relative;
            height: 250px;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .book-cover-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .book-cover-image {
            width: auto;
            height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .book-cover-avatar {
            width: 120px;
            height: 160px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .book-details-close {
            position: absolute;
            top: 12px;
            right: 12px;
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
            font-size: 20px;
        }

        .book-details-close:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        /* Fine Action Modals */
        .fine-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .fine-modal-overlay.show {
            display: flex;
        }

        .fine-modal {
            background-color: white;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.3s ease;
        }

        body.dark-theme .fine-modal {
            background-color: #1e293b;
        }

        .fine-modal.wide {
            max-width: 920px;
            width: 94%;
        }

        .fine-modal-header {
            padding: 20px;
            border-bottom: 1px solid;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .fine-modal-header h2 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .fine-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            transition: background-color 0.2s ease;
        }

        .fine-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .fine-modal-body {
            padding: 24px;
        }

        .fine-form-group {
            margin-bottom: 12px;
        }

        .fine-form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .fine-form-input,
        .fine-form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.2s ease;
        }

        body.light-theme .fine-form-input,
        body.light-theme .fine-form-textarea {
            border-color: #e2e8f0;
            background-color: #f8fafc;
        }

        body.dark-theme .fine-form-input,
        body.dark-theme .fine-form-textarea {
            border-color: #334155;
            background-color: #0f172a;
            color: white;
        }

        .fine-form-input:focus,
        .fine-form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }

        body.light-theme .fine-form-input:focus,
        body.light-theme .fine-form-textarea:focus {
            background-color: #ffffff;
        }

        body.dark-theme .fine-form-input:focus,
        body.dark-theme .fine-form-textarea:focus {
            background-color: #1e293b;
        }

        .fine-form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .fine-info-box {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            background-color: #f0f4ff;
            border-left: 4px solid #3b82f6;
        }

        body.dark-theme .fine-info-box {
            background-color: #1e3a8a;
            border-left-color: #60a5fa;
        }

        .fine-info-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            opacity: 0.7;
            margin-bottom: 4px;
        }

        .fine-info-value {
            font-size: 18px;
            font-weight: 700;
            color: #3b82f6;
        }

        body.dark-theme .fine-info-value {
            color: #93c5fd;
        }

        .fine-modal-actions {
            display: flex;
            gap: 12px;
            padding: 20px;
            border-top: 1px solid;
        }

        body.light-theme .fine-modal-actions {
            border-color: #e2e8f0;
        }

        body.dark-theme .fine-modal-actions {
            border-color: #334155;
        }

        .fine-modal-btn {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fine-modal-btn-cancel {
            background-color: transparent;
            border: 1px solid;
        }

        body.light-theme .fine-modal-btn-cancel {
            border-color: #e2e8f0;
            color: #64748b;
        }

        body.dark-theme .fine-modal-btn-cancel {
            border-color: #334155;
            color: #94a3b8;
        }

        body.light-theme .fine-modal-btn-cancel:hover {
            background-color: #f1f5f9;
        }

        body.dark-theme .fine-modal-btn-cancel:hover {
            background-color: #0f172a;
        }

        .fine-modal-btn-action {
            background-color: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
        }

        .fine-modal-btn-action:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .fine-modal-btn-action:disabled {
            background-color: #cbd5e1;
            border-color: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* Fine History */
        .fine-history-modal-body {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .fine-history-summary {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.9fr);
            gap: 16px;
        }

        .fine-history-hero,
        .fine-summary-card,
        .fine-history-section,
        .fine-calculation-table {
            border-radius: 14px;
            border: 1px solid;
        }

        body.light-theme .fine-history-hero,
        body.light-theme .fine-summary-card,
        body.light-theme .fine-history-section,
        body.light-theme .fine-calculation-table {
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        body.light-theme .fine-history-hero {
            background: linear-gradient(135deg, #eff6ff 0%, #f8fafc 100%);
        }

        body.dark-theme .fine-history-hero,
        body.dark-theme .fine-summary-card,
        body.dark-theme .fine-history-section,
        body.dark-theme .fine-calculation-table {
            background-color: #0f172a;
            border-color: #334155;
        }

        body.dark-theme .fine-history-hero {
            background: linear-gradient(135deg, #172554 0%, #0f172a 100%);
        }

        .fine-history-hero {
            padding: 20px;
        }

        .fine-history-amount {
            font-size: 34px;
            font-weight: 800;
            margin: 8px 0 12px;
            line-height: 1.1;
        }

        .fine-history-book-title {
            font-size: 15px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .fine-history-book-meta {
            font-size: 13px;
        }

        body.light-theme .fine-history-book-meta {
            color: #64748b;
        }

        body.dark-theme .fine-history-book-meta {
            color: #94a3b8;
        }

        .fine-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .fine-summary-card {
            padding: 16px;
        }

        .fine-summary-label,
        .fine-history-section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        body.light-theme .fine-summary-label,
        body.light-theme .fine-history-section-title {
            color: #64748b;
        }

        body.dark-theme .fine-summary-label,
        body.dark-theme .fine-history-section-title {
            color: #94a3b8;
        }

        .fine-summary-value {
            font-size: 18px;
            font-weight: 700;
            margin-top: 8px;
            line-height: 1.35;
        }

        .history-status-badge,
        .history-action-badge,
        .fine-history-amount-change {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .history-status-badge.status-pending {
            background-color: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }

        body.dark-theme .history-status-badge.status-pending {
            background-color: #78350f;
            color: #fcd34d;
            border-color: #92400e;
        }

        .history-action-badge.adjusted,
        .fine-history-amount-change {
            background-color: #f3e8ff;
            color: #7c3aed;
            border-color: #ddd6fe;
        }

        body.dark-theme .history-action-badge.adjusted,
        body.dark-theme .fine-history-amount-change {
            background-color: #4c1d95;
            color: #c4b5fd;
            border-color: #6d28d9;
        }

        .history-status-badge.status-paid,
        .history-action-badge.paid {
            background-color: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        body.dark-theme .history-status-badge.status-paid,
        body.dark-theme .history-action-badge.paid {
            background-color: #14532d;
            color: #86efac;
            border-color: #166534;
        }

        .history-status-badge.status-waived,
        .history-action-badge.waived {
            background-color: #ffedd5;
            color: #c2410c;
            border-color: #fdba74;
        }

        body.dark-theme .history-status-badge.status-waived,
        body.dark-theme .history-action-badge.waived {
            background-color: #7c2d12;
            color: #fdba74;
            border-color: #9a3412;
        }

        .history-action-badge.created {
            background-color: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        body.dark-theme .history-action-badge.created {
            background-color: #1e3a8a;
            color: #93c5fd;
            border-color: #1d4ed8;
        }

        .fine-history-section {
            padding: 18px;
        }

        .fine-history-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .fine-history-item {
            position: relative;
            padding-left: 42px;
        }

        .fine-history-item::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 28px;
            bottom: -16px;
            width: 2px;
        }

        body.light-theme .fine-history-item::before {
            background-color: #e2e8f0;
        }

        body.dark-theme .fine-history-item::before {
            background-color: #334155;
        }

        .fine-history-item:last-child::before {
            display: none;
        }

        .fine-history-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            position: absolute;
            left: 0;
            top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 11px;
            font-weight: 700;
        }

        body.light-theme .fine-history-dot {
            box-shadow: 0 0 0 4px #ffffff;
        }

        body.dark-theme .fine-history-dot {
            box-shadow: 0 0 0 4px #1e293b;
        }

        .fine-history-dot.created {
            background-color: #3b82f6;
        }

        .fine-history-dot.adjusted {
            background-color: #8b5cf6;
        }

        .fine-history-dot.paid {
            background-color: #10b981;
        }

        .fine-history-dot.waived {
            background-color: #f97316;
        }

        .fine-history-entry {
            border-radius: 12px;
            padding: 16px;
            border: 1px solid;
        }

        body.light-theme .fine-history-entry {
            background-color: #ffffff;
            border-color: #e2e8f0;
        }

        body.dark-theme .fine-history-entry {
            background-color: #111827;
            border-color: #334155;
        }

        .fine-history-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .fine-history-date {
            font-size: 13px;
            font-weight: 500;
        }

        body.light-theme .fine-history-date {
            color: #64748b;
        }

        body.dark-theme .fine-history-date {
            color: #94a3b8;
        }

        .fine-history-action {
            margin: 0 0 8px;
            font-size: 15px;
            font-weight: 700;
        }

        .fine-history-description {
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }

        body.light-theme .fine-history-description {
            color: #334155;
        }

        body.dark-theme .fine-history-description {
            color: #cbd5e1;
        }

        .fine-history-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 14px;
            margin-top: 12px;
        }

        .fine-history-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .fine-history-actor {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .fine-history-actor-name {
            font-weight: 600;
        }

        .fine-history-role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid transparent;
            line-height: 1;
        }

        .fine-history-role-badge.role-admin {
            background-color: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        body.dark-theme .fine-history-role-badge.role-admin {
            background-color: #7f1d1d;
            color: #fca5a5;
            border-color: #b91c1c;
        }

        .fine-history-role-badge.role-staff {
            background-color: #dbeafe;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }

        body.dark-theme .fine-history-role-badge.role-staff {
            background-color: #1e3a8a;
            color: #93c5fd;
            border-color: #1d4ed8;
        }

        .fine-history-role-badge.role-student {
            background-color: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        body.dark-theme .fine-history-role-badge.role-student {
            background-color: #14532d;
            color: #86efac;
            border-color: #166534;
        }

        .fine-history-role-badge.role-system,
        .fine-history-role-badge.role-user {
            background-color: #e2e8f0;
            color: #475569;
            border-color: #cbd5e1;
        }

        body.dark-theme .fine-history-role-badge.role-system,
        body.dark-theme .fine-history-role-badge.role-user {
            background-color: #334155;
            color: #cbd5e1;
            border-color: #475569;
        }

        body.light-theme .fine-history-meta-item {
            color: #475569;
        }

        body.dark-theme .fine-history-meta-item {
            color: #cbd5e1;
        }

        .fine-history-amount-change {
            margin-top: 12px;
        }

        .fine-history-remarks {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 10px;
            border-left: 3px solid;
            font-size: 13px;
            line-height: 1.6;
            font-style: italic;
        }

        body.light-theme .fine-history-remarks {
            background-color: #f8fafc;
            border-left-color: #cbd5e1;
            color: #475569;
        }

        body.dark-theme .fine-history-remarks {
            background-color: #1e293b;
            border-left-color: #475569;
            color: #cbd5e1;
        }

        .fine-calculation-table {
            padding: 4px 16px;
        }

        .fine-calculation-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid;
        }

        body.light-theme .fine-calculation-row {
            border-color: #e2e8f0;
        }

        body.dark-theme .fine-calculation-row {
            border-color: #334155;
        }

        .fine-calculation-row:last-child {
            border-bottom: none;
        }

        .fine-calculation-row.total {
            font-size: 16px;
            font-weight: 800;
        }

        .fine-history-empty {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            border: 1px dashed;
            font-size: 14px;
        }

        body.light-theme .fine-history-empty {
            border-color: #cbd5e1;
            color: #64748b;
            background-color: #f8fafc;
        }

        body.dark-theme .fine-history-empty {
            border-color: #475569;
            color: #94a3b8;
            background-color: #0f172a;
        }

        @media (max-width: 768px) {
            .fine-modal.wide {
                width: calc(100% - 24px);
                max-height: calc(100vh - 24px);
            }

            .fine-history-summary,
            .fine-summary-grid {
                grid-template-columns: 1fr;
            }

            .fine-history-top {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .fine-history-amount {
                font-size: 28px;
            }

            .fine-history-item {
                padding-left: 34px;
            }
        }

        /* Fine Action Success Popup Styles */
        .fine-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.6);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .fine-popup-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .fine-popup {
            background: white;
            border-radius: 16px;
            padding: 32px;
            max-width: 420px;
            width: 90%;
            text-align: center;
            transform: scale(0.8);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
        }

        body.dark-theme .fine-popup {
            background: #1e293b;
        }

        .fine-popup-overlay.show .fine-popup {
            transform: scale(1);
        }

        .fine-popup-icon {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            animation: popupIconPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes popupIconPop {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .fine-popup-icon.paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.4);
        }

        .fine-popup-icon.waived {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 8px 32px rgba(245, 158, 11, 0.4);
        }

        .fine-popup-icon.adjusted {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            box-shadow: 0 8px 32px rgba(139, 92, 246, 0.4);
        }

        .fine-popup-icon.email-sending {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.4);
        }

        .fine-popup-icon svg {
            width: 44px;
            height: 44px;
            color: white;
        }

        .fine-popup-icon .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .fine-popup-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e293b;
        }

        body.dark-theme .fine-popup-title {
            color: #f1f5f9;
        }

        .fine-popup-message {
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
            color: #64748b;
        }

        body.dark-theme .fine-popup-message {
            color: #94a3b8;
        }

        .fine-popup-details {
            background: #f8fafc;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
        }

        body.dark-theme .fine-popup-details {
            background: #334155;
        }

        .fine-popup-details .amount {
            font-weight: 800;
            font-size: 32px;
            display: block;
            margin-bottom: 4px;
        }

        body.light-theme .fine-popup-details .amount {
            color: #1e293b;
        }

        body.dark-theme .fine-popup-details .amount {
            color: #f1f5f9;
        }

        .fine-popup-details .detail-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        body.dark-theme .fine-popup-details .detail-label {
            color: #94a3b8;
        }

        .fine-popup-email-indicator {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #eff6ff;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #1d4ed8;
        }

        body.dark-theme .fine-popup-email-indicator {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .fine-popup-email-indicator svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .fine-popup-email-indicator.sending {
            background: #fef3c7;
            color: #92400e;
        }

        body.dark-theme .fine-popup-email-indicator.sending {
            background: #78350f;
            color: #fde68a;
        }

        .fine-popup-email-indicator.sent {
            background: #dcfce7;
            color: #166534;
        }

        body.dark-theme .fine-popup-email-indicator.sent {
            background: #14532d;
            color: #86efac;
        }

        .fine-popup-btn {
            padding: 14px 40px;
            border-radius: 10px;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fine-popup-btn.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4);
        }

        .fine-popup-btn.primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        }

        .fine-popup-btn.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
        }

        .fine-popup-btn.success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .fine-popup-btn.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.4);
        }

        .fine-popup-btn.warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }

        .fine-popup-btn.purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.4);
        }

        .fine-popup-btn.purple:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        }

        .fine-popup-footer {
            margin-top: 16px;
            font-size: 13px;
            color: #94a3b8;
        }

        .book-details-body {
            padding: 24px;
        }

        .book-title-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .book-heading {
            min-width: 0;
            flex: 1;
        }

        .book-title-wrap {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .book-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            line-height: 1.25;
        }

        .book-author {
            font-size: 16px;
            font-weight: 500;
            margin: 8px 0 0 0;
        }

        body.light-theme .book-author {
            color: #666;
        }

        body.dark-theme .book-author {
            color: #94a3b8;
        }

        .book-category-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            border: 1px solid transparent;
        }

        body.light-theme .book-category-badge {
            background-color: #ede9fe;
            color: #6d28d9;
            border-color: #ddd6fe;
        }

        body.dark-theme .book-category-badge {
            background-color: #312e81;
            color: #c4b5fd;
            border-color: #4338ca;
        }

        .book-details-columns {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }

        .book-details-column,
        .book-status-section,
        .book-description {
            border-radius: 12px;
            padding: 16px;
            border: 1px solid;
        }

        body.light-theme .book-details-column,
        body.light-theme .book-status-section,
        body.light-theme .book-description {
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        body.dark-theme .book-details-column,
        body.dark-theme .book-status-section,
        body.dark-theme .book-description {
            background-color: #0f172a;
            border-color: #334155;
        }

        .column-title,
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 14px;
        }

        body.light-theme .column-title,
        body.light-theme .section-title {
            color: #64748b;
        }

        body.dark-theme .column-title,
        body.dark-theme .section-title {
            color: #94a3b8;
        }

        .book-details-grid {
            display: grid;
            gap: 12px;
        }

        .book-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .book-detail-item {
            border-radius: 10px;
            padding: 12px;
            min-height: 76px;
            border: 1px solid;
        }

        body.light-theme .book-detail-item {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .book-detail-item {
            background-color: #111827;
            border: 1px solid #334155;
        }

        .book-detail-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        body.light-theme .book-detail-label {
            color: #64748b;
        }

        body.dark-theme .book-detail-label {
            color: #94a3b8;
        }

        .book-detail-value {
            font-size: 15px;
            font-weight: 600;
            margin-top: 6px;
            line-height: 1.4;
            word-break: break-word;
        }

        .book-detail-badge,
        .fine-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            border: 1px solid transparent;
        }

        .book-detail-badge.status-issued {
            background-color: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        body.dark-theme .book-detail-badge.status-issued {
            background-color: #14532d;
            color: #4ade80;
            border-color: #166534;
        }

        .book-detail-badge.status-overdue {
            background-color: #fee2e2;
            color: #dc2626;
            border-color: #fecaca;
        }

        body.dark-theme .book-detail-badge.status-overdue {
            background-color: #7f1d1d;
            color: #fca5a5;
            border-color: #b91c1c;
        }

        .book-detail-badge.status-returned {
            background-color: #dbeafe;
            color: #2563eb;
            border-color: #bfdbfe;
        }

        body.dark-theme .book-detail-badge.status-returned {
            background-color: #1e3a8a;
            color: #60a5fa;
            border-color: #1d4ed8;
        }

        .fine-status-badge.fine-status-pending {
            background-color: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }

        body.dark-theme .fine-status-badge.fine-status-pending {
            background-color: #78350f;
            color: #fcd34d;
            border-color: #92400e;
        }

        .fine-status-badge.fine-status-paid {
            background-color: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        body.dark-theme .fine-status-badge.fine-status-paid {
            background-color: #14532d;
            color: #4ade80;
            border-color: #166534;
        }

        .fine-status-badge.fine-status-waived {
            background-color: #ffedd5;
            color: #c2410c;
            border-color: #fdba74;
        }

        body.dark-theme .fine-status-badge.fine-status-waived {
            background-color: #7c2d12;
            color: #fdba74;
            border-color: #9a3412;
        }

        .fine-status-badge.fine-status-na {
            background-color: #e2e8f0;
            color: #475569;
            border-color: #cbd5e1;
        }

        body.dark-theme .fine-status-badge.fine-status-na {
            background-color: #334155;
            color: #cbd5e1;
            border-color: #475569;
        }

        .book-description-text {
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-line;
        }

        .book-detail-item[hidden] {
            display: none !important;
        }

        @media (max-width: 768px) {
            .book-details-modal {
                width: calc(100% - 24px);
                max-height: calc(100vh - 24px);
            }

            .book-details-header {
                height: 220px;
            }

            .book-details-body {
                padding: 18px;
            }

            .book-title-row,
            .book-title-wrap {
                flex-direction: column;
                align-items: flex-start;
            }

            .book-details-columns,
            .book-status-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .book-details-header {
                height: 200px;
            }

            .book-cover-avatar {
                width: 100px;
                height: 140px;
                font-size: 30px;
            }
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #dc2626;
            border-color: #fee2e2;
        }

        body.light-theme .btn-delete:hover {
            background-color: #fecaca;
        }

        body.dark-theme .btn-delete {
            background-color: #7f1d1d;
            color: #fca5a5;
            border-color: #7f1d1d;
        }

        body.dark-theme .btn-delete:hover {
            background-color: #991b1b;
        }

        /* Fine & Payment Management Card */
        .fines-management-card {
            border-radius: 8px;
            padding: 16px;
            border: 1px solid;
            width: 100%;
            height: var(--student-management-card-height);
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        body.light-theme .fines-management-card {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .fines-management-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        .fines-management-card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .fines-management-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            flex: 1;
        }

        .generate-receipt-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background-color: #10b981;
            color: white;
            transition: background-color 0.2s ease;
            flex-shrink: 0;
        }

        .generate-receipt-btn:hover {
            background-color: #059669;
        }

        /* Fines Table */
        .fines-table {
            width: 100%;
            border-collapse: collapse;
        }

        .fines-table th {
            padding: 5px 7px;
            text-align: left;
            font-size: 10.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            border-bottom: 1px solid;
            transition: background-color 0.3s, border-color 0.3s, color 0.3s;
        }

        body.light-theme .fines-table th {
            color: #475569;
            border-color: #e2e8f0;
            background-color: #f8fafc;
        }

        body.dark-theme .fines-table th {
            color: #cbd5e1;
            border-color: #334155;
            background-color: #1e293b;
        }

        .fines-table td {
            padding: 5px 7px;
            border-bottom: 1px solid;
            vertical-align: middle;
            font-size: 12.5px;
            transition: border-color 0.3s, color 0.3s;
        }

        body.light-theme .fines-table td {
            border-color: #e2e8f0;
            color: #0f172a;
        }

        body.dark-theme .fines-table td {
            border-color: #334155;
            color: #f1f5f9;
        }

        .fines-table tr:last-child td {
            border-bottom: none;
        }

        .fines-table tbody tr:hover {
            transition: background-color 0.3s;
        }

        body.light-theme .fines-table tbody tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .fines-table tbody tr:hover {
            background-color: #2d3748;
        }

        /* Payment Status Badges */
        .payment-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 3px 9px;
            border-radius: 16px;
            font-size: 10.5px;
            font-weight: 600;
            gap: 4px;
            transition: background 0.3s, color 0.3s;
            white-space: nowrap;
        }

        .payment-badge i {
            display: inline-block;
            line-height: 1;
        }

        .payment-badge.unpaid {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
        }

        body.dark-theme .payment-badge.unpaid {
            background: linear-gradient(135deg, #7f1d1d 0%, #450a0a 100%);
            color: #f87171;
        }

        .payment-badge.paid {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
        }

        body.dark-theme .payment-badge.paid {
            background: linear-gradient(135deg, #14532d 0%, #052e16 100%);
            color: #4ade80;
        }

        .payment-badge.waive {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            color: #92400e;
        }

        body.dark-theme .payment-badge.waive {
            background: linear-gradient(135deg, #78350f 0%, #451a03 100%);
            color: #f59e0b;
        }

        /* Fine Actions */
        .fine-actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-btn-small {
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .action-btn-small i {
            display: inline-block;
            line-height: 1;
            font-size: 11px;
        }

        .action-btn-small.adjust {
            background-color: #dbeafe;
            color: #2563eb;
            border-color: #dbeafe;
        }

        .action-btn-small.waive {
            background-color: #dcfce7;
            color: #16a34a;
            border-color: #dcfce7;
        }

        .action-btn-small.mark-paid {
            background-color: #f3e8ff;
            color: #7c3aed;
            border-color: #f3e8ff;
        }

        .action-btn-small.view-history {
            background-color: #f8fafc;
            color: #64748b;
            border-color: #f8fafc;
        }

        body.light-theme .action-btn-small:hover {
            background-color: #bfdbfe;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .action-btn-small.adjust {
            background-color: #1e3a8a;
            color: #60a5fa;
            border-color: #1e3a8a;
        }

        body.dark-theme .action-btn-small.adjust:hover {
            background-color: #1e40af;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        body.dark-theme .action-btn-small.waive {
            background-color: #14532d;
            color: #4ade80;
            border-color: #14532d;
        }

        body.dark-theme .action-btn-small.waive:hover {
            background-color: #15803d;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        body.dark-theme .action-btn-small.mark-paid {
            background-color: #5b21b6;
            color: #c4b5fd;
            border-color: #5b21b6;
        }

        body.dark-theme .action-btn-small.mark-paid:hover {
            background-color: #6d28d9;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        body.dark-theme .action-btn-small.view-history {
            background-color: #334155;
            color: #94a3b8;
            border-color: #334155;
        }

        body.dark-theme .action-btn-small.view-history:hover {
            background-color: #475569;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Bottom Row */
        .bottom-row-part2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .fines-management-card .search-filter-container,
        .fines-management-card .student-table-pagination {
            flex-shrink: 0;
        }

        .fines-management-card .table-container.paginated-table {
            flex: 1;
            min-height: 0;
            overflow: auto;
        }

        /* Privilege Settings Card */
        .privilege-settings-card {
            border-radius: 8px;
            padding: 14px;
            border: 1px solid;
        }

        body.light-theme .privilege-settings-card {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .privilege-settings-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        .privilege-settings-card .card-header {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid;
        }

        body.light-theme .privilege-settings-card .card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .privilege-settings-card .card-header {
            border-color: #334155;
        }

        .privilege-settings-card h3 {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }

        /* Settings Form */
        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .settings-actions {
            display: flex;
            gap: 12px;
            margin-top: 6px;
        }

        .settings-actions > button {
            flex: 1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 500;
        }

        body.light-theme .form-group label {
            color: #374151;
        }

        body.dark-theme .form-group label {
            color: #d1d5db;
        }

        .form-group input,
        .form-group select {
            padding: 9px 11px;
            border-radius: 6px;
            font-size: 13px;
            border: 1px solid;
            background-color: transparent;
        }

        body.light-theme .form-group input,
        body.light-theme .form-group select {
            color: #0f172a;
            border-color: #d1d5db;
            background-color: #ffffff;
        }

        body.dark-theme .form-group input,
        body.dark-theme .form-group select {
            color: #e5e7eb;
            border-color: #4b5563;
            background-color: #0f172a;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .save-changes-btn {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background-color: #3b82f6;
            color: white;
            transition: background-color 0.2s ease, opacity 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .save-changes-btn:hover {
            background-color: #2563eb;
        }

        .save-changes-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .set-default-btn {
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        body.light-theme .set-default-btn {
            background-color: #fff7ed;
            color: #c2410c;
            border-color: #fdba74;
        }

        body.dark-theme .set-default-btn {
            background-color: #431407;
            color: #fdba74;
            border-color: #9a3412;
        }

        body.light-theme .set-default-btn:hover {
            background-color: #ffedd5;
        }

        body.dark-theme .set-default-btn:hover {
            background-color: #7c2d12;
        }

        .set-default-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        /* Activity Logs Card */
        .activity-logs-card {
            border-radius: 8px;
            padding: 14px;
            border: 1px solid;
        }

        body.light-theme .activity-logs-card {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .activity-logs-card {
            background-color: #1e293b;
            border-color: #334155;
        }

        .activity-logs-card .card-header {
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid;
        }

        body.light-theme .activity-logs-card .card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .activity-logs-card .card-header {
            border-color: #334155;
        }

        .activity-logs-card h3 {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
        }

        .activity-timeline {
            display: flex;
            flex-direction: column;
            gap: 14px;
            max-height: 520px;
            overflow-y: auto;
            padding-right: 6px;
        }

        .activity-timeline::-webkit-scrollbar {
            width: 6px;
        }

        .activity-timeline::-webkit-scrollbar-track {
            background: transparent;
        }

        .activity-timeline::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        body.dark-theme .activity-timeline::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .activity-timeline::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        body.dark-theme .activity-timeline::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        .activity-item {
            --activity-accent: #64748b;
            --activity-soft: #e2e8f0;
            --activity-border: rgba(148, 163, 184, 0.35);
            display: grid;
            grid-template-columns: 20px minmax(0, 1fr);
            gap: 12px;
            align-items: flex-start;
        }

        .activity-item.book-issued {
            --activity-accent: #2563eb;
            --activity-soft: #dbeafe;
            --activity-border: rgba(37, 99, 235, 0.18);
        }

        .activity-item.book-returned {
            --activity-accent: #16a34a;
            --activity-soft: #dcfce7;
            --activity-border: rgba(22, 163, 74, 0.18);
        }

        .activity-item.fine-applied {
            --activity-accent: #dc2626;
            --activity-soft: #fee2e2;
            --activity-border: rgba(220, 38, 38, 0.18);
        }

        .activity-item.fine-paid {
            --activity-accent: #15803d;
            --activity-soft: #dcfce7;
            --activity-border: rgba(21, 128, 61, 0.18);
        }

        .activity-item.fine-waived {
            --activity-accent: #ea580c;
            --activity-soft: #ffedd5;
            --activity-border: rgba(234, 88, 12, 0.2);
        }

        .activity-item.account-status {
            --activity-accent: #9333ea;
            --activity-soft: #f3e8ff;
            --activity-border: rgba(147, 51, 234, 0.2);
        }

        .activity-item.profile-updated {
            --activity-accent: #ca8a04;
            --activity-soft: #fef3c7;
            --activity-border: rgba(202, 138, 4, 0.2);
        }

        .activity-item.privilege-change {
            --activity-accent: #4f46e5;
            --activity-soft: #e0e7ff;
            --activity-border: rgba(79, 70, 229, 0.2);
        }

        .activity-item.auth {
            --activity-accent: #475569;
            --activity-soft: #e2e8f0;
            --activity-border: rgba(71, 85, 105, 0.2);
        }

        body.dark-theme .activity-item.book-issued {
            --activity-soft: rgba(37, 99, 235, 0.16);
            --activity-border: rgba(96, 165, 250, 0.28);
        }

        body.dark-theme .activity-item.book-returned {
            --activity-soft: rgba(22, 163, 74, 0.18);
            --activity-border: rgba(74, 222, 128, 0.24);
        }

        body.dark-theme .activity-item.fine-applied {
            --activity-soft: rgba(220, 38, 38, 0.18);
            --activity-border: rgba(252, 165, 165, 0.24);
        }

        body.dark-theme .activity-item.fine-paid {
            --activity-soft: rgba(21, 128, 61, 0.2);
            --activity-border: rgba(74, 222, 128, 0.24);
        }

        body.dark-theme .activity-item.fine-waived {
            --activity-soft: rgba(234, 88, 12, 0.2);
            --activity-border: rgba(251, 146, 60, 0.26);
        }

        body.dark-theme .activity-item.account-status {
            --activity-soft: rgba(147, 51, 234, 0.18);
            --activity-border: rgba(196, 181, 253, 0.24);
        }

        body.dark-theme .activity-item.profile-updated {
            --activity-soft: rgba(202, 138, 4, 0.18);
            --activity-border: rgba(250, 204, 21, 0.22);
        }

        body.dark-theme .activity-item.privilege-change {
            --activity-soft: rgba(79, 70, 229, 0.18);
            --activity-border: rgba(165, 180, 252, 0.24);
        }

        body.dark-theme .activity-item.auth {
            --activity-soft: rgba(71, 85, 105, 0.22);
            --activity-border: rgba(148, 163, 184, 0.24);
        }

        .activity-connector {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100%;
        }

        .activity-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--activity-accent);
            box-shadow: 0 0 0 4px var(--activity-soft);
            margin-top: 18px;
            z-index: 1;
        }

        .activity-line {
            width: 2px;
            flex: 1;
            min-height: 48px;
            margin-top: 8px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--activity-accent) 0%, rgba(148, 163, 184, 0.08) 100%);
            opacity: 0.6;
        }

        .activity-item.is-last .activity-line {
            opacity: 0;
        }

        .activity-card {
            position: relative;
            border: 1px solid var(--activity-border);
            border-left: 3px solid var(--activity-accent);
            border-radius: 16px;
            padding: 16px 16px 14px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        body.light-theme .activity-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.98) 100%);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        }

        body.dark-theme .activity-card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.92) 0%, rgba(30, 41, 59, 0.92) 100%);
            box-shadow: 0 12px 28px rgba(2, 6, 23, 0.24);
        }

        .activity-card:hover,
        .activity-item:focus-within .activity-card {
            transform: translateY(-2px);
        }

        .activity-main {
            display: grid;
            grid-template-columns: 48px minmax(0, 1fr);
            gap: 14px;
        }

        .activity-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: var(--activity-soft);
            color: var(--activity-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .activity-content {
            min-width: 0;
        }

        .activity-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .activity-title {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.35;
        }

        .activity-desc {
            margin: 0 0 12px;
            font-size: 13px;
            line-height: 1.55;
        }

        body.light-theme .activity-desc {
            color: #475569;
        }

        body.dark-theme .activity-desc {
            color: #cbd5e1;
        }

        .activity-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .activity-status-badge.status-success {
            background-color: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }

        .activity-status-badge.status-failed {
            background-color: #fee2e2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .activity-status-badge.status-warning {
            background-color: #fef3c7;
            color: #b45309;
            border-color: #fde68a;
        }

        body.light-theme .activity-title {
            color: #0f172a;
        }

        body.dark-theme .activity-title {
            color: #f8fafc;
        }

        body.dark-theme .activity-status-badge.status-success {
            background-color: rgba(22, 163, 74, 0.2);
            color: #86efac;
            border-color: rgba(74, 222, 128, 0.25);
        }

        body.dark-theme .activity-status-badge.status-failed {
            background-color: rgba(220, 38, 38, 0.22);
            color: #fca5a5;
            border-color: rgba(252, 165, 165, 0.24);
        }

        body.dark-theme .activity-status-badge.status-warning {
            background-color: rgba(217, 119, 6, 0.2);
            color: #fcd34d;
            border-color: rgba(251, 191, 36, 0.22);
        }

        .activity-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .activity-user {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .activity-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        body.light-theme .activity-avatar {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            color: #1d4ed8;
        }

        body.dark-theme .activity-avatar {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(79, 70, 229, 0.22) 100%);
            color: #bfdbfe;
        }

        .activity-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .activity-user-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .activity-user-name-row {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .activity-user-name {
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 240px;
        }

        body.light-theme .activity-user-name {
            color: #1e293b;
        }

        body.dark-theme .activity-user-name {
            color: #f1f5f9;
        }

        .activity-role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .activity-role-badge.role-admin {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .activity-role-badge.role-staff {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .activity-role-badge.role-student {
            background-color: #dcfce7;
            color: #166534;
        }

        .activity-role-badge.role-system {
            background-color: #e2e8f0;
            color: #475569;
        }

        body.dark-theme .activity-role-badge.role-admin {
            background-color: rgba(220, 38, 38, 0.22);
            color: #fca5a5;
        }

        body.dark-theme .activity-role-badge.role-staff {
            background-color: rgba(37, 99, 235, 0.2);
            color: #93c5fd;
        }

        body.dark-theme .activity-role-badge.role-student {
            background-color: rgba(22, 163, 74, 0.2);
            color: #86efac;
        }

        body.dark-theme .activity-role-badge.role-system {
            background-color: rgba(71, 85, 105, 0.26);
            color: #cbd5e1;
        }

        .activity-user-resource {
            font-size: 12px;
        }

        body.light-theme .activity-user-resource {
            color: #64748b;
        }

        body.dark-theme .activity-user-resource {
            color: #94a3b8;
        }

        .activity-meta-controls {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .activity-time-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        body.light-theme .activity-time-chip {
            background-color: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .activity-time-chip {
            background-color: rgba(15, 23, 42, 0.85);
            color: #cbd5e1;
            border: 1px solid rgba(71, 85, 105, 0.6);
        }

        .activity-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            background: transparent;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            padding: 6px 0;
            transition: color 0.2s ease;
        }

        body.light-theme .activity-toggle-btn {
            color: var(--activity-accent);
        }

        body.dark-theme .activity-toggle-btn {
            color: color-mix(in srgb, var(--activity-accent) 62%, #ffffff 38%);
        }

        .activity-toggle-btn.is-disabled {
            cursor: default;
            opacity: 0.55;
            pointer-events: none;
        }

        .activity-technical-panel {
            margin-top: 14px;
            border-radius: 14px;
            padding: 14px;
            border: 1px solid var(--activity-border);
            background: color-mix(in srgb, var(--activity-soft) 58%, transparent);
        }

        body.dark-theme .activity-technical-panel {
            background: color-mix(in srgb, var(--activity-soft) 72%, rgba(15, 23, 42, 0.85));
        }

        .activity-technical-panel[hidden] {
            display: none !important;
        }

        .activity-technical-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
        }

        .activity-technical-item {
            padding: 10px 12px;
            border-radius: 12px;
        }

        body.light-theme .activity-technical-item {
            background-color: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(226, 232, 240, 0.95);
        }

        body.dark-theme .activity-technical-item {
            background-color: rgba(15, 23, 42, 0.88);
            border: 1px solid rgba(51, 65, 85, 0.9);
        }

        .activity-technical-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        body.light-theme .activity-technical-label {
            color: #64748b;
        }

        body.dark-theme .activity-technical-label {
            color: #94a3b8;
        }

        .activity-technical-value {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
            word-break: break-word;
        }

        body.light-theme .activity-technical-value {
            color: #1e293b;
        }

        body.dark-theme .activity-technical-value {
            color: #e2e8f0;
        }

        .activity-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
            opacity: 0;
            transform: translateY(6px);
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .activity-card:hover .activity-actions,
        .activity-item:focus-within .activity-actions {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .activity-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 10px;
            border: 1px solid transparent;
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        .activity-action-btn:hover {
            transform: translateY(-1px);
        }

        .activity-action-btn.view-resource {
            background-color: var(--activity-soft);
            color: var(--activity-accent);
            border-color: var(--activity-border);
        }

        .activity-action-btn.more-details {
            background-color: transparent;
        }

        body.light-theme .activity-action-btn.more-details {
            color: #334155;
            border-color: #cbd5e1;
        }

        body.dark-theme .activity-action-btn.more-details {
            color: #e2e8f0;
            border-color: rgba(100, 116, 139, 0.7);
        }

        .activity-action-btn.is-disabled {
            opacity: 0.55;
            cursor: not-allowed;
            pointer-events: none;
        }

        .show-more-btn {
            width: 100%;
            margin-top: 4px;
            padding: 12px 16px;
            border: 1px dashed;
            background-color: transparent;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        body.light-theme .show-more-btn {
            border-color: #93c5fd;
            color: #2563eb;
            background-color: #eff6ff;
        }

        body.light-theme .show-more-btn:hover {
            background-color: #dbeafe;
        }

        body.dark-theme .show-more-btn {
            border-color: rgba(96, 165, 250, 0.45);
            color: #93c5fd;
            background-color: rgba(30, 64, 175, 0.16);
        }

        body.dark-theme .show-more-btn:hover {
            background-color: rgba(37, 99, 235, 0.22);
        }

        .activity-modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.62);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1100;
            padding: 20px;
        }

        .activity-modal-overlay.show {
            display: flex;
        }

        .activity-modal {
            width: min(860px, 100%);
            max-height: min(88vh, 900px);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid;
            display: flex;
            flex-direction: column;
        }

        body.light-theme .activity-modal {
            background-color: #ffffff;
            border-color: #e2e8f0;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        }

        body.dark-theme .activity-modal {
            background-color: #0f172a;
            border-color: #334155;
            box-shadow: 0 28px 70px rgba(2, 6, 23, 0.48);
        }

        .activity-modal-header {
            padding: 22px 24px;
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: flex-start;
        }

        .activity-modal-kicker {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.75;
        }

        .activity-modal-header h2 {
            margin: 0;
            font-size: 22px;
            line-height: 1.3;
        }

        .activity-modal-header p {
            margin: 8px 0 0;
            font-size: 14px;
            opacity: 0.92;
        }

        .activity-modal-close {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s ease;
        }

        .activity-modal-close:hover {
            background: rgba(255, 255, 255, 0.24);
        }

        .activity-modal-body {
            padding: 22px 24px 18px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .activity-modal-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .activity-detail-card {
            border-radius: 16px;
            padding: 14px 16px;
            border: 1px solid;
        }

        body.light-theme .activity-detail-card {
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        body.dark-theme .activity-detail-card {
            background-color: #111c30;
            border-color: #334155;
        }

        .activity-detail-label {
            display: block;
            margin-bottom: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        body.light-theme .activity-detail-label {
            color: #64748b;
        }

        body.dark-theme .activity-detail-label {
            color: #94a3b8;
        }

        .activity-detail-value {
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
            word-break: break-word;
        }

        body.light-theme .activity-detail-value {
            color: #0f172a;
        }

        body.dark-theme .activity-detail-value {
            color: #f8fafc;
        }

        .activity-modal-section {
            border-radius: 18px;
            padding: 18px;
            border: 1px solid;
        }

        body.light-theme .activity-modal-section {
            background-color: #ffffff;
            border-color: #e2e8f0;
        }

        body.dark-theme .activity-modal-section {
            background-color: #111827;
            border-color: #334155;
        }

        .activity-modal-section h4 {
            margin: 0 0 14px;
            font-size: 15px;
            font-weight: 700;
        }

        .activity-modal-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .activity-modal-description {
            margin: 0;
            font-size: 14px;
            line-height: 1.7;
        }

        body.light-theme .activity-modal-description {
            color: #475569;
        }

        body.dark-theme .activity-modal-description {
            color: #cbd5e1;
        }

        .activity-metadata-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .activity-metadata-item {
            border-radius: 14px;
            padding: 12px 14px;
        }

        body.light-theme .activity-metadata-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .activity-metadata-item {
            background-color: #0f172a;
            border: 1px solid #334155;
        }

        .activity-metadata-key {
            display: block;
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        body.light-theme .activity-metadata-key {
            color: #64748b;
        }

        body.dark-theme .activity-metadata-key {
            color: #94a3b8;
        }

        .activity-metadata-value {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
            word-break: break-word;
            white-space: pre-wrap;
        }

        body.light-theme .activity-metadata-value {
            color: #1e293b;
        }

        body.dark-theme .activity-metadata-value {
            color: #e2e8f0;
        }

        .activity-modal-empty {
            font-size: 13px;
            padding: 14px;
            border-radius: 14px;
        }

        body.light-theme .activity-modal-empty {
            background-color: #f8fafc;
            color: #64748b;
            border: 1px dashed #cbd5e1;
        }

        body.dark-theme .activity-modal-empty {
            background-color: rgba(15, 23, 42, 0.9);
            color: #94a3b8;
            border: 1px dashed rgba(100, 116, 139, 0.7);
        }

        .activity-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 0 24px 24px;
        }

        @media (max-width: 768px) {
            .activity-main {
                grid-template-columns: 1fr;
            }

            .activity-icon {
                width: 42px;
                height: 42px;
                border-radius: 12px;
            }

            .activity-header,
            .activity-meta {
                flex-direction: column;
                align-items: flex-start;
            }

            .activity-meta-controls {
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }

            .activity-technical-grid,
            .activity-modal-grid,
            .activity-metadata-list {
                grid-template-columns: 1fr;
            }

            .activity-actions {
                opacity: 1;
                transform: translateY(0);
                pointer-events: auto;
            }

            .activity-modal-overlay {
                padding: 12px;
            }

            .activity-modal-header,
            .activity-modal-body,
            .activity-modal-actions {
                padding-left: 18px;
                padding-right: 18px;
            }
        }

        @media (max-width: 480px) {
            .activity-item {
                grid-template-columns: 16px minmax(0, 1fr);
                gap: 10px;
            }

            .activity-dot {
                width: 12px;
                height: 12px;
                margin-top: 16px;
            }

            .activity-card {
                padding: 14px;
            }

            .activity-action-btn,
            .show-more-btn {
                width: 100%;
            }

            .activity-modal-header h2 {
                font-size: 19px;
            }
        }

        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 40px;
            text-align: center;
        }

        .empty-state svg {
            margin-bottom: 16px;
        }

        body.light-theme .empty-state svg {
            color: #cbd5e1;
        }

        body.dark-theme .empty-state svg {
            color: #475569;
        }

        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
        }

        body.light-theme .empty-state h3 {
            color: #334155;
        }

        body.dark-theme .empty-state h3 {
            color: #cbd5e1;
        }

        .empty-state p {
            font-size: 14px;
            margin: 0;
            max-width: 400px;
        }

        body.light-theme .empty-state p {
            color: #94a3b8;
        }

        body.dark-theme .empty-state p {
            color: #64748b;
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s;
            max-width: 350px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toast-success {
            background-color: #10b981;
            color: white;
        }

        .toast-error {
            background-color: #ef4444;
            color: white;
        }

        .toast-warning {
            background-color: #f59e0b;
            color: white;
        }

        .toast-info {
            background-color: #3b82f6;
            color: white;
        }

        .toast-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        /* Responsive Design for Part 2 */
        @media (max-width: 1024px) {
            .main-grid-part2 {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .bottom-row-part2 {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        @media (max-width: 768px) {
            .details-section-part2 {
                margin-top: 16px;
                --student-management-card-height: clamp(340px, 60vh, 460px);
            }

            .fine-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-btn-small {
                width: 100%;
            }

            .settings-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .account-btn,
            .generate-receipt-btn,
            .set-default-btn,
            .save-changes-btn {
                padding: 10px 12px;
                font-size: 13px;
            }

            .fines-table th,
            .fines-table td {
                padding: 12px;
            }
        }
    </style>
@endpush


@section('content')
    <div class="student-details-page">
        <!-- Header Row -->
        <div class="page-header">
            <div class="header-left">
                <a href="{{ route('admin.students.index') }}" class="back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Students
                </a>
                <h1 class="page-title">Student Details</h1>
            </div>
            <div class="header-right">
                <button class="btn btn-secondary" id="printReportBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 9V2h12v7"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <path d="M6 14h12v8H6z"/>
                    </svg>
                    Print Report
                </button>
            </div>
        </div>

        <!-- Main Grid Layout -->
        <div class="main-grid">
            <!-- Left Column - Student Profile -->
            <div class="profile-column">
                <div class="profile-card" id="adminStudentProfileCard">
                            <div class="avatar-container">
                                @if(optional($student->user)->profile_photo)
                                    <div class="student-avatar">
                                        <img src="{{ str_starts_with($student->user->profile_photo, 'http') ? $student->user->profile_photo : asset('storage/' . $student->user->profile_photo) }}" alt="{{ $student->user->name }}">
                                    </div>
                                @else
                                    <div class="student-avatar">{{ substr($student->user->name, 0, 1) }}{{ strpos($student->user->name, ' ') !== false ? substr($student->user->name, strpos($student->user->name, ' ') + 1, 1) : '' }}</div>
                                @endif
                            </div>
                    <div class="student-info">
                        <h2 class="student-name">{{ $student->user->name }}</h2>
                        <p class="student-id">{{ $student->roll_no ?? 'N/A' }}</p>

                        <div class="status-badges">
                            <span class="badge {{ $student->user->status === 'inactive' ? 'badge-danger' : 'badge-success' }}" id="studentStatusBadge">{{ ucfirst($student->user->status ?? 'N/A') }}</span>
                            <span class="badge badge-info">{{ ucfirst($student->user->role ?? 'N/A') }}</span>
                        </div>

                        <div class="info-list">
                            <div class="info-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                                <span>{{ $student->user->email }}</span>
                            </div>
                            <div class="info-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                </svg>
                                <span>{{ $student->user->phone ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 19l7-7 3 3-7 7-3-3z"/>
                                    <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>
                                    <path d="M2 2l7.586 7.586"/>
                                    <circle cx="11" cy="11" r="2"/>
                                </svg>
                                <span>{{ $student->department->name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                </svg>
                                <span>{{ $student->semester ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>Batch {{ $student->batch ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="system-info">
                        <h4>System Information</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Created:</span>
                                <span class="info-value">{{ optional($student->created_at)->format('M d, Y') ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Last Login:</span>
                                <span class="info-value">{{ optional($student->user->last_login_at)->format('M d, Y h:i A') ?? 'Never' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Created By:</span>
                                <span class="info-value">{{ $student->created_by_name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Modified By:</span>
                                <span class="info-value">{{ $student->modified_by_name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Main Content -->
            <div class="content-column" id="adminStudentTopContent">
                <!-- Summary Cards -->
                <div class="summary-cards-grid">
                    <div class="summary-card">
                        <div class="summary-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                            </svg>
                        </div>
                        <div class="summary-content">
                            <div class="summary-title">Total Issued</div>
                            <div class="summary-value">{{ $student->issuedBooks->count() }}</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </div>
                        <div class="summary-content">
                            <div class="summary-title">Currently Issued</div>
                            <div class="summary-value">{{ $student->issuedBooks()->whereNull('return_date')->count() }}</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        </div>
                        <div class="summary-content">
                            <div class="summary-title">Overdue</div>
                            <div class="summary-value">{{ $student->issuedBooks()->whereNull('return_date')->where('due_date','<', now()->toDateString())->count() }}</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <text x="12" y="16" text-anchor="middle" font-size="18" font-weight="700" fill="currentColor" stroke="none">₹</text>
                            </svg>
                        </div>
                        <div class="summary-content">
                            <div class="summary-title">Pending Fine</div>
                            <div class="summary-value" id="pendingFineValue">₹{{ number_format($student->fines->where('status','pending')->sum('amount'), 2) }}</div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div class="summary-content">
                            <div class="summary-title">Last Activity</div>
                            <div class="summary-value">{{ $student->issuedBooks->sortByDesc('created_at')->first()?->created_at?->format('M d') ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Issued Books Section -->
                <div class="issued-books-section" id="adminIssuedBooksSection">
                    <div class="section-header">
                        <h3>Issued Books</h3>
                    </div>

                    <div class="search-filter-container student-table-toolbar" aria-label="Issued books search and filters">
                        <div class="search-box">
                            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" class="search-input" id="bookSearch" placeholder="Search by title, author, or ISBN...">
                        </div>

                        <div class="filters-container">
                            <select id="statusFilter" class="filter-select" aria-label="Filter issued books by status">
                                <option value="all">All Status</option>
                                <option value="issued">Issued</option>
                                <option value="overdue">Overdue</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>

                        <button type="button" class="student-toolbar-reset" id="bookResetFiltersBtn" aria-label="Reset issued books filters">
                            <i class="fas fa-rotate-left"></i>
                            Reset
                        </button>

                        <label class="admin-table-entries-control" for="booksEntriesSelect">
                            <span>Show</span>
                            <select id="booksEntriesSelect" class="admin-table-entries-select" aria-label="Select issued book entries per page">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>entries</span>
                        </label>
                    </div>

                    <!-- Books Table -->
                    <div class="table-container paginated-table">
                        <table class="books-table">
                            <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>ISBN</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Fine</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody id="booksTableBody">
                            <!-- Data will be populated here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-table-pagination student-table-pagination" id="booksPaginationContainer">
                        <div class="admin-table-pagination-meta">
                            <div class="admin-table-pagination-summary" id="booksTableSummary">Showing 0 books</div>
                            <div class="admin-table-pagination-page" id="booksTablePageInfo">Page 1 of 1</div>
                        </div>
                        <div class="admin-table-pagination-nav" id="booksTablePagination" aria-label="Issued books pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Put this code RIGHT AFTER the closing </div> of your Part 1 code -->
    <!-- i.e., after the </div> of .issued-books-section -->

    <div class="details-section-part2">
        <!-- Main Grid Layout for Part 2 -->
        <div class="main-grid-part2">
            <!-- Left Column - Account Management -->
            <div class="left-column-part2">
                <div class="account-management-card">
                    <div class="card-header">
                        <div class="header-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <h3>Account Management</h3>
                    </div>
                    <div class="account-actions">
                        <button class="account-btn btn-reset-password" id="resetPasswordBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 7h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2v-4h-1"/>
                                <path d="M10 7H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h1"/>
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                            Reset Password
                        </button>
                        @if($student->user->status === 'inactive')
                        <button class="account-btn btn-activate" id="activateAccountBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Activate Account
                        </button>
                        @else
                        <button class="account-btn btn-deactivate" id="deactivateAccountBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 9.2 1"/>
                            </svg>
                            Deactivate Account
                        </button>
                        @endif
                        <button class="account-btn btn-change-role" id="changeRoleBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Change Role
                        </button>
                        <button class="account-btn btn-delete" id="deleteAccountBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 6h18"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <line x1="10" y1="11" x2="10" y2="17"/>
                                <line x1="14" y1="11" x2="14" y2="17"/>
                            </svg>
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Fine & Payment Management -->
            <div class="right-column-part2">
                <div class="fines-management-card">
                    <div class="card-header">
                        <h3>Fine & Payment Management</h3>
                        <button class="generate-receipt-btn" id="generateReceiptBtn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            Generate Receipt
                        </button>
                    </div>

                    <div class="search-filter-container student-table-toolbar" aria-label="Fine and payment search and filters">
                        <div class="search-box">
                            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" class="search-input" id="fineSearchInput" placeholder="Search by book, amount, status, or days overdue...">
                        </div>

                        <div class="filters-container">
                            <select id="fineStatusFilter" class="filter-select" aria-label="Filter fines by status">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="waived">Waived</option>
                            </select>
                        </div>

                        <button type="button" class="student-toolbar-reset" id="fineResetFiltersBtn" aria-label="Reset fine filters">
                            <i class="fas fa-rotate-left"></i>
                            Reset
                        </button>

                        <label class="admin-table-entries-control" for="finesEntriesSelect">
                            <span>Show</span>
                            <select id="finesEntriesSelect" class="admin-table-entries-select" aria-label="Select fine entries per page">
                                <option value="10" selected>10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>entries</span>
                        </label>
                    </div>

                    <div class="table-container paginated-table">
                        <table class="fines-table">
                            <thead>
                            <tr>
                                <th>Book Name</th>
                                <th>Days Overdue</th>
                                <th>Fine Amount</th>
                                <th>Payment Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="finesTableBody">
                            <!-- Data will be populated here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="admin-table-pagination student-table-pagination" id="finesPaginationContainer">
                        <div class="admin-table-pagination-meta">
                            <div class="admin-table-pagination-summary" id="finesTableSummary">Showing 0 fines</div>
                            <div class="admin-table-pagination-page" id="finesTablePageInfo">Page 1 of 1</div>
                        </div>
                        <div class="admin-table-pagination-nav" id="finesTablePagination" aria-label="Fines pagination"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Row - Two Column Layout -->
        <div class="bottom-row-part2">
            <!-- Left - Library Privilege Settings -->
            <div class="privilege-settings-card">
                <div class="card-header">
                    <h3>Library Privilege Settings</h3>
                </div>
                <div class="settings-form">
                    <div class="form-group">
                        <label for="maxBooks">Maximum Books Allowed</label>
                        <input type="number" id="maxBooks" value="5" min="1" max="20">
                    </div>
                    <div class="form-group">
                        <label for="maxDays">Maximum Issue Duration (Days)</label>
                        <input type="number" id="maxDays" value="14" min="1" max="90">
                    </div>
                    <div class="form-group">
                        <label for="fineRate">Fine Rate Per Day (₹)</label>
                        <input type="number" id="fineRate" value="10" min="0" max="100" step="0.5">
                    </div>
                    <div class="form-group">
                        <label for="borrowingPermission">Borrowing Permission</label>
                        <select id="borrowingPermission">
                            <option value="allowed">Allowed</option>
                            <option value="restricted">Restricted</option>
                        </select>
                    </div>
                    <div class="settings-actions">
                        <button type="button" class="set-default-btn" id="setDefaultPrivilegesBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                <polyline points="3 3 3 9 9 9"/>
                            </svg>
                            <span data-privilege-reset-label>Set Default</span>
                        </button>
                        <button type="button" class="save-changes-btn" id="saveChangesBtn">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right - Activity & Audit Logs -->
            <div class="activity-logs-card">
                <div class="card-header">
                    <h3>Activity & Audit Logs</h3>
                </div>
                <div class="activity-timeline" id="activityTimeline">
                    <!-- Activity logs will be populated here -->
                </div>
            </div>
        </div>

        <div class="activity-modal-overlay" id="activityDetailsOverlay">
            <div class="activity-modal" role="dialog" aria-modal="true" aria-labelledby="activityDetailsTitle">
                <div class="activity-modal-header">
                    <div>
                        <p class="activity-modal-kicker">Audit Event</p>
                        <h2 id="activityDetailsTitle">Activity Details</h2>
                        <p id="activityDetailsSubtitle">Review technical details and metadata for this activity.</p>
                    </div>
                    <button type="button" class="activity-modal-close" id="closeActivityDetailsBtn" aria-label="Close activity details">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="activity-modal-body">
                    <div class="activity-modal-grid">
                        <div class="activity-detail-card">
                            <span class="activity-detail-label">Status</span>
                            <div class="activity-detail-value" id="activityDetailsStatus"></div>
                        </div>
                        <div class="activity-detail-card">
                            <span class="activity-detail-label">Resource</span>
                            <div class="activity-detail-value" id="activityDetailsResource"></div>
                        </div>
                        <div class="activity-detail-card">
                            <span class="activity-detail-label">Timestamp</span>
                            <div class="activity-detail-value" id="activityDetailsTimestamp"></div>
                        </div>
                        <div class="activity-detail-card">
                            <span class="activity-detail-label">Session</span>
                            <div class="activity-detail-value" id="activityDetailsSession"></div>
                        </div>
                    </div>

                    <div class="activity-modal-section">
                        <h4>Performed By</h4>
                        <div class="activity-modal-user" id="activityDetailsUser"></div>
                    </div>

                    <div class="activity-modal-section">
                        <h4>Description</h4>
                        <p class="activity-modal-description" id="activityDetailsDescription"></p>
                    </div>

                    <div class="activity-modal-section">
                        <h4>Technical Details</h4>
                        <div class="activity-technical-grid">
                            <div class="activity-technical-item">
                                <span class="activity-technical-label">IP Address</span>
                                <span class="activity-technical-value" id="activityDetailsIp"></span>
                            </div>
                            <div class="activity-technical-item">
                                <span class="activity-technical-label">Device Type</span>
                                <span class="activity-technical-value" id="activityDetailsDevice"></span>
                            </div>
                            <div class="activity-technical-item">
                                <span class="activity-technical-label">Browser</span>
                                <span class="activity-technical-value" id="activityDetailsBrowser"></span>
                            </div>
                            <div class="activity-technical-item">
                                <span class="activity-technical-label">Session ID</span>
                                <span class="activity-technical-value" id="activityDetailsSessionPanel"></span>
                            </div>
                        </div>
                    </div>

                    <div class="activity-modal-section">
                        <h4>Metadata</h4>
                        <div class="activity-metadata-list" id="activityMetadataList"></div>
                    </div>
                </div>
                <div class="activity-modal-actions">
                    <a href="#" class="activity-action-btn view-resource" id="activityDetailsResourceLink" target="_blank" rel="noopener">
                        <i class="fas fa-external-link-alt"></i>
                        View Resource
                    </a>
                    <button type="button" class="activity-action-btn more-details" onclick="closeActivityDetails()">
                        <i class="fas fa-times-circle"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Selection Modal -->
    <div class="role-modal-overlay" id="roleModalOverlay">
        <div class="role-modal">
            <div class="role-modal-header">
                <h2>Change Student Role</h2>
                <p>Select the new role for this student. Each role has different permissions and access levels.</p>
            </div>
            <div class="role-options">
                <div class="role-option student" data-role="student">
                    <div class="role-option-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="role-option-content">
                        <p class="role-option-title">Student</p>
                        <p class="role-option-description">Can browse library, issue books, pay fines</p>
                    </div>
                </div>
                <div class="role-option staff" data-role="staff">
                    <div class="role-option-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div class="role-option-content">
                        <p class="role-option-title">Staff</p>
                        <p class="role-option-description">Can manage books, process transactions</p>
                    </div>
                </div>
                <div class="role-option admin" data-role="admin">
                    <div class="role-option-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="1"/>
                            <path d="M12 1v6m0 6v6"/>
                            <path d="M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24"/>
                            <path d="M1 12h6m6 0h6"/>
                            <path d="M4.22 19.78l4.24-4.24m5.08-5.08l4.24-4.24"/>
                        </svg>
                    </div>
                    <div class="role-option-content">
                        <p class="role-option-title">Admin</p>
                        <p class="role-option-description">Full system access, manage all operations</p>
                    </div>
                </div>
            </div>
            <div class="role-modal-actions">
                <button class="modal-btn modal-btn-cancel" id="cancelRoleBtn">Cancel</button>
                <button class="modal-btn modal-btn-action" id="confirmRoleBtn" disabled>Change Role</button>
            </div>
        </div>
    </div>

    <!-- Fine Action Modals -->
    <!-- Adjust Fine Modal -->
    <div class="fine-modal-overlay" id="adjustFineOverlay">
        <div class="fine-modal">
            <div class="fine-modal-header">
                <h2>Adjust Fine Amount</h2>
                <button class="fine-modal-close" onclick="closeFineModal('adjust')">&times;</button>
            </div>
            <div class="fine-modal-body">
                <div class="fine-info-box">
                    <div class="fine-info-label">Current Amount</div>
                    <div class="fine-info-value" id="adjustCurrentAmount">₹0</div>
                </div>
                <div class="fine-form-group">
                    <label class="fine-form-label" for="adjustNewAmount">New Amount (₹)</label>
                    <input type="number" id="adjustNewAmount" class="fine-form-input" placeholder="Enter new fine amount" min="0" step="0.01">
                </div>
            </div>
            <div class="fine-modal-actions">
                <button class="fine-modal-btn fine-modal-btn-cancel" onclick="closeFineModal('adjust')">Cancel</button>
                <button class="fine-modal-btn fine-modal-btn-action" id="adjustFineSubmitBtn" onclick="submitAdjustFine()">Adjust Fine</button>
            </div>
        </div>
    </div>

    <!-- Waive Fine Modal -->
    <div class="fine-modal-overlay" id="waiveFineOverlay">
        <div class="fine-modal">
            <div class="fine-modal-header">
                <h2>Waive Fine</h2>
                <button class="fine-modal-close" onclick="closeFineModal('waive')">&times;</button>
            </div>
            <div class="fine-modal-body">
                <div class="fine-info-box">
                    <div class="fine-info-label">Fine Amount to Waive</div>
                    <div class="fine-info-value" id="waiveAmount">₹0</div>
                </div>
                <div class="fine-form-group">
                    <label class="fine-form-label" for="waiveReason">Reason for Waiving</label>
                    <textarea id="waiveReason" class="fine-form-textarea" placeholder="Enter reason for waiving this fine..."></textarea>
                </div>
            </div>
            <div class="fine-modal-actions">
                <button class="fine-modal-btn fine-modal-btn-cancel" onclick="closeFineModal('waive')">Cancel</button>
                <button class="fine-modal-btn fine-modal-btn-action" id="waiveFineSubmitBtn" onclick="submitWaiveFine()">Waive Fine</button>
            </div>
        </div>
    </div>

    <!-- Fine History Modal -->
    <div class="fine-modal-overlay" id="historyOverlay">
        <div class="fine-modal wide">
            <div class="fine-modal-header">
                <h2>Fine Payment History</h2>
                <button class="fine-modal-close" onclick="closeFineModal('history')">&times;</button>
            </div>
            <div class="fine-modal-body fine-history-modal-body">
                <div class="fine-history-summary">
                    <div class="fine-history-hero">
                        <div class="fine-summary-label">Current Fine Amount</div>
                        <div class="fine-history-amount" id="historyAmount">₹0.00</div>
                        <p class="fine-history-book-title" id="historyBookTitle">Loading book details...</p>
                        <div class="fine-history-book-meta" id="historyBookIsbn">ISBN: N/A</div>
                    </div>

                    <div class="fine-summary-grid">
                        <div class="fine-summary-card">
                            <div class="fine-summary-label">Original Amount</div>
                            <div class="fine-summary-value" id="historyOriginalAmount">₹0.00</div>
                        </div>
                        <div class="fine-summary-card">
                            <div class="fine-summary-label">Status</div>
                            <div class="fine-summary-value" id="historyStatus">Pending</div>
                        </div>
                        <div class="fine-summary-card">
                            <div class="fine-summary-label">Days Late</div>
                            <div class="fine-summary-value" id="historyDaysLate">0 days</div>
                        </div>
                    </div>
                </div>

                <div class="fine-history-section">
                    <div class="fine-history-section-title">Enhanced Timeline</div>
                    <ul class="fine-history-list" id="fineHistoryList">
                        <li class="fine-history-empty">Loading history...</li>
                    </ul>
                </div>

                <div class="fine-history-section" id="historyCalculationSection" hidden>
                    <div class="fine-history-section-title">Calculation Breakdown</div>
                    <div class="fine-calculation-table">
                        <div class="fine-calculation-row">
                            <span>Per day rate</span>
                            <strong id="historyCalcBaseRate">₹0.00</strong>
                        </div>
                        <div class="fine-calculation-row">
                            <span>Days late</span>
                            <strong id="historyCalcDaysLate">0</strong>
                        </div>
                        <div class="fine-calculation-row">
                            <span>Subtotal</span>
                            <strong id="historyCalcSubtotal">₹0.00</strong>
                        </div>
                        <div class="fine-calculation-row" id="historyCalcAdjustmentsRow" hidden>
                            <span>Adjustments</span>
                            <strong id="historyCalcAdjustments">₹0.00</strong>
                        </div>
                        <div class="fine-calculation-row total">
                            <span>Final Amount</span>
                            <strong id="historyCalcFinalAmount">₹0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Book Details Modal -->
    <div class="book-details-overlay" id="bookDetailsOverlay">
        <div class="book-details-modal">
            <div class="book-details-header">
                <div class="book-cover-container">
                    <div id="bookCoverContent"></div>
                </div>
                <button class="book-details-close" id="closeBookDetailsBtn">&times;</button>
            </div>
            <div class="book-details-body">
                <div class="book-title-row">
                    <div class="book-heading">
                        <div class="book-title-wrap">
                            <h2 class="book-title" id="bookDetailsTitle"></h2>
                            <span class="book-category-badge" id="bookDetailsCategory"></span>
                        </div>
                        <p class="book-author" id="bookDetailsAuthor"></p>
                    </div>
                </div>

                <div class="book-details-columns">
                    <div class="book-details-column">
                        <div class="column-title">Book Information</div>
                        <div class="book-details-grid">
                            <div class="book-detail-item">
                                <div class="book-detail-label">ISBN</div>
                                <div class="book-detail-value" id="bookDetailsISBN"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Publisher</div>
                                <div class="book-detail-value" id="bookDetailsPublisher"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Condition</div>
                                <div class="book-detail-value" id="bookDetailsCondition"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Category</div>
                                <div class="book-detail-value" id="bookDetailsCategoryValue"></div>
                            </div>
                        </div>
                    </div>

                    <div class="book-details-column">
                        <div class="column-title">Transaction Details</div>
                        <div class="book-details-grid">
                            <div class="book-detail-item">
                                <div class="book-detail-label">Transaction ID</div>
                                <div class="book-detail-value" id="bookDetailsTransactionId"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Issue Date</div>
                                <div class="book-detail-value" id="bookDetailsIssueDate"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Due Date</div>
                                <div class="book-detail-value" id="bookDetailsDueDate"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Return Date</div>
                                <div class="book-detail-value" id="bookDetailsReturnDate"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Issued By</div>
                                <div class="book-detail-value" id="bookDetailsIssuedBy"></div>
                            </div>
                            <div class="book-detail-item">
                                <div class="book-detail-label">Renewal Count</div>
                                <div class="book-detail-value" id="bookDetailsRenewalCount"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="book-status-section">
                    <div class="section-title">Status &amp; Fines</div>
                    <div class="book-status-grid">
                        <div class="book-detail-item">
                            <div class="book-detail-label">Status</div>
                            <div class="book-detail-value" id="bookDetailsStatus"></div>
                        </div>
                        <div class="book-detail-item" id="overdueInfo" hidden>
                            <div class="book-detail-label">Days Overdue</div>
                            <div class="book-detail-value" id="bookDetailsDaysOverdue"></div>
                        </div>
                        <div class="book-detail-item">
                            <div class="book-detail-label">Fine Amount</div>
                            <div class="book-detail-value" id="bookDetailsFine"></div>
                        </div>
                        <div class="book-detail-item" id="fineStatusInfo" hidden>
                            <div class="book-detail-label">Fine Status</div>
                            <div class="book-detail-value" id="bookDetailsFineStatus"></div>
                        </div>
                    </div>
                </div>

                <div class="book-description">
                    <div class="section-title">Description</div>
                    <div class="book-description-text" id="bookDetailsDescription"></div>
                </div>
            </div>
        </div>
    </div>

    @php
        $issuedBooksReportStudent = [
            'name' => $student->user->name ?? 'N/A',
            'rollNo' => $student->roll_no ?? 'N/A',
            'email' => $student->user->email ?? 'N/A',
            'department' => $student->department->name ?? 'N/A',
            'semester' => $student->semester ?? 'N/A',
            'batch' => $student->batch ? 'Batch ' . $student->batch : 'N/A',
        ];

        $issuedBooksReportExportConfig = [
            'modalId' => 'issuedBooksExportModal',
            'idPrefix' => 'issuedBooksExport',
            'scopeName' => 'issuedBooksExportScope',
            'labels' => [
                'title' => 'Issued Books Report',
                'description' => 'Print or download the issued books report for this student.',
                'scopeTitle' => 'Scope',
                'scopeHint' => 'Use this page or all issued books matching the current filters.',
                'pageOptionTitle' => 'Current page',
                'pageOptionDescription' => 'Only rows visible in the table now.',
                'allOptionTitle' => 'Filtered report',
                'allOptionDescription' => 'All issued books matching the current search and status filter.',
                'badge' => 'Current page',
                'headline' => '0 issued books ready',
                'subtext' => 'Review the issued books report before printing or downloading it.',
                'previewTitle' => 'Preview',
                'previewDescription' => 'Rows included in the issued books report.',
                'previewCount' => '0 rows',
                'emptyPreview' => 'No issued books selected for preview.',
                'footerNote' => 'Using the current page for spreadsheet export.',
                'cancelButton' => 'Cancel',
                'downloadButton' => 'Download Excel',
                'printButton' => 'Print Report',
            ],
            'document' => [
                'systemTitle' => $libraryBranding['name'] ?? 'Library Management System',
                'reportTitle' => 'Issued Books Report',
            ],
            'columns' => [
                ['key' => 'title', 'label' => 'Book Title', 'width' => '28%', 'emphasis' => true],
                ['key' => 'isbn', 'label' => 'ISBN', 'width' => '14%', 'nowrap' => true],
                ['key' => 'issueDate', 'label' => 'Issue Date', 'width' => '12%', 'nowrap' => true],
                ['key' => 'dueDate', 'label' => 'Due Date', 'width' => '12%', 'nowrap' => true],
                ['key' => 'returnDate', 'label' => 'Return Date', 'width' => '12%', 'nowrap' => true],
                ['key' => 'status', 'label' => 'Status', 'width' => '10%', 'align' => 'center', 'nowrap' => true],
                ['key' => 'fineAmount', 'label' => 'Fine Amount', 'width' => '12%', 'align' => 'right', 'nowrap' => true],
            ],
        ];

        $fineReceiptReportExportConfig = [
            'modalId' => 'fineReceiptExportModal',
            'idPrefix' => 'fineReceiptExport',
            'scopeName' => 'fineReceiptExportScope',
            'labels' => [
                'title' => 'Fine Receipt',
                'description' => 'Print or download the fine and payment receipt for this student.',
                'scopeTitle' => 'Scope',
                'scopeHint' => 'Use this page or all fines matching the current filters.',
                'pageOptionTitle' => 'Current page',
                'pageOptionDescription' => 'Only fine rows visible in the table now.',
                'allOptionTitle' => 'Filtered receipt',
                'allOptionDescription' => 'All fine rows matching the current search and status filter.',
                'badge' => 'Current page',
                'headline' => '0 fine records ready',
                'subtext' => 'Review the fine receipt before printing or downloading it.',
                'previewTitle' => 'Preview',
                'previewDescription' => 'Fine rows included in the receipt.',
                'previewCount' => '0 rows',
                'emptyPreview' => 'No fine rows selected for preview.',
                'footerNote' => 'Using the current page for spreadsheet export.',
                'cancelButton' => 'Cancel',
                'downloadButton' => 'Download Excel',
                'printButton' => 'Print Receipt',
            ],
            'document' => [
                'systemTitle' => $libraryBranding['name'] ?? 'Library Management System',
                'reportTitle' => 'Fine Receipt',
            ],
            'columns' => [
                ['key' => 'bookName', 'label' => 'Book Name', 'width' => '40%', 'emphasis' => true],
                ['key' => 'daysOverdue', 'label' => 'Days Overdue', 'width' => '16%', 'align' => 'center', 'nowrap' => true],
                ['key' => 'fineAmount', 'label' => 'Fine Amount', 'width' => '18%', 'align' => 'right', 'nowrap' => true],
                ['key' => 'paymentStatus', 'label' => 'Payment Status', 'width' => '26%', 'align' => 'center', 'nowrap' => true],
            ],
        ];
    @endphp

    @include('shared.report-export.modal', ['reportExportConfig' => $issuedBooksReportExportConfig])
    @include('shared.report-export.modal', ['reportExportConfig' => $fineReceiptReportExportConfig])

    @include('shared.action-feedback.markup', [
        'actionFeedbackConfig' => [
            'confirm' => [
                'modalId' => 'confirmActionModal',
                'iconId' => 'confirmActionIcon',
                'titleId' => 'confirmActionTitle',
                'messageId' => 'confirmActionMessage',
                'detailId' => 'confirmActionDetail',
                'submitButtonId' => 'confirmActionSubmitBtn',
                'cancelLabel' => 'Cancel',
                'confirmLabel' => 'Continue',
                'defaultTitle' => 'Confirm Action',
                'defaultMessage' => 'Are you sure you want to continue?',
            ],
            'toast' => [
                'containerId' => 'fineToastContainer',
                'liveRegionId' => 'fineLiveRegion',
            ],
        ],
    ])
@endsection



@push('scripts')
    @include('shared.report-export.scripts')
    @include('shared.action-feedback.scripts')
    <script>
        // Data provided by server (transformed in controller)
        const booksData = (@json($booksData ?? []) || []).map(normalizeStudentBookRecord);
        const finesData = @json($finesData ?? []);
        const activityLogs = @json($activityLogs ?? []);
        const remainingActivityLogs = @json($remainingActivityLogs ?? []);
        const studentId = @json($student->id);
        let studentStatus = @json($student->user->status ?? 'active');
        const issuedBooksReportStudent = @json($issuedBooksReportStudent);
        const issuedBooksReportBranding = window.LibraryBranding?.normalize
            ? window.LibraryBranding.normalize(window.__LIBRARY_BRANDING__ ?? {})
            : (window.__LIBRARY_BRANDING__ ?? {});
        const issuedBooksReportSystemTitle = issuedBooksReportBranding?.name || 'Library Management System';

        // DOM Elements
        const bookSearchInput = document.getElementById('bookSearch');
        const statusFilterSelect = document.getElementById('statusFilter');
        const bookResetFiltersBtn = document.getElementById('bookResetFiltersBtn');
        const booksTableBody = document.getElementById('booksTableBody');
        const booksEntriesSelect = document.getElementById('booksEntriesSelect');
        const booksTableSummary = document.getElementById('booksTableSummary');
        const booksTablePageInfo = document.getElementById('booksTablePageInfo');
        const booksTablePagination = document.getElementById('booksTablePagination');
        const fineSearchInput = document.getElementById('fineSearchInput');
        const fineStatusFilterSelect = document.getElementById('fineStatusFilter');
        const fineResetFiltersBtn = document.getElementById('fineResetFiltersBtn');
        const finesEntriesSelect = document.getElementById('finesEntriesSelect');
        const finesTableSummary = document.getElementById('finesTableSummary');
        const finesTablePageInfo = document.getElementById('finesTablePageInfo');
        const finesTablePagination = document.getElementById('finesTablePagination');
        const adminStudentProfileCard = document.getElementById('adminStudentProfileCard');
        const adminStudentTopContent = document.getElementById('adminStudentTopContent');
        const printReportBtn = document.getElementById('printReportBtn');
        const issuedBooksExportModal = document.getElementById('issuedBooksExportModal');
        const fineReceiptExportModal = document.getElementById('fineReceiptExportModal');
        const actionFeedbackConfirmModal = document.getElementById('confirmActionModal');
        const actionFeedbackConfirmSubmitBtn = document.getElementById('confirmActionSubmitBtn');
        let pendingConfirmationAction = null;
        let issuedBooksExportWorkflow = null;
        let issuedBooksExportLastTrigger = null;
        let fineReceiptExportWorkflow = null;
        let fineReceiptExportLastTrigger = null;

        function setFeedbackButtonBusy(button, isBusy, label) {
            if (!button) return;

            button.disabled = Boolean(isBusy);
            if (label) {
                button.textContent = label;
            }
        }

        const feedbackUI = typeof window.ActionFeedbackUI === 'function'
            ? new window.ActionFeedbackUI({
                confirm: {
                    modalId: 'confirmActionModal',
                    iconId: 'confirmActionIcon',
                    titleId: 'confirmActionTitle',
                    messageId: 'confirmActionMessage',
                    detailId: 'confirmActionDetail',
                    submitButtonId: 'confirmActionSubmitBtn',
                    confirmLabel: 'Continue',
                },
                toast: {
                    containerId: 'fineToastContainer',
                    liveRegionId: 'fineLiveRegion',
                },
                setButtonBusy: setFeedbackButtonBusy,
            })
            : null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            syncAdminStudentTopLayout();
            renderBooksTable();
            setupEventListeners();
            setupKeyboardNavigation();
            setupActionFeedback();
            setupIssuedBooksExportWorkflow();
            setupFineReceiptExportWorkflow();
        });

        window.addEventListener('load', syncAdminStudentTopLayout);
        window.addEventListener('resize', syncAdminStudentTopLayout);

        if (window.ResizeObserver && adminStudentProfileCard) {
            const adminStudentTopLayoutObserver = new ResizeObserver(() => {
                syncAdminStudentTopLayout();
            });

            adminStudentTopLayoutObserver.observe(adminStudentProfileCard);
        }

        function setupActionFeedback() {
            actionFeedbackConfirmSubmitBtn?.addEventListener('click', handleConfirmationSubmit);

            document.addEventListener('click', function(event) {
                const closeButton = event.target.closest('[data-modal-close]');
                if (!closeButton) {
                    return;
                }

                const modalId = closeButton.getAttribute('data-modal-close');
                if (modalId === 'confirmActionModal') {
                    closeConfirmationModal();
                    return;
                }

                if (modalId === 'issuedBooksExportModal') {
                    closeIssuedBooksExportModal(modalId);
                    return;
                }

                if (modalId === 'fineReceiptExportModal') {
                    closeFineReceiptExportModal(modalId);
                }
            });

            actionFeedbackConfirmModal?.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeConfirmationModal();
                }
            });

            issuedBooksExportModal?.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeIssuedBooksExportModal();
                }
            });

            fineReceiptExportModal?.addEventListener('click', function(event) {
                if (event.target === this) {
                    closeFineReceiptExportModal();
                }
            });
        }

        function syncAdminStudentTopLayout() {
            if (!adminStudentProfileCard || !adminStudentTopContent) return;

            if (window.innerWidth <= 1200) {
                adminStudentTopContent.style.height = '';
                adminStudentTopContent.style.maxHeight = '';
                return;
            }

            const profileHeight = adminStudentProfileCard.offsetHeight;

            if (profileHeight > 0) {
                adminStudentTopContent.style.height = `${profileHeight}px`;
                adminStudentTopContent.style.maxHeight = `${profileHeight}px`;
            }
        }

        // Setup event listeners
        function setupEventListeners() {
            // Search functionality
            if (bookSearchInput) {
                bookSearchInput.addEventListener('input', handleBookSearch);
            }

            // Status filter
            if (statusFilterSelect) {
                statusFilterSelect.addEventListener('change', handleStatusFilter);
            }

            if (bookResetFiltersBtn) {
                bookResetFiltersBtn.addEventListener('click', resetBookFilters);
            }

            if (fineSearchInput) {
                fineSearchInput.addEventListener('input', handleFineSearch);
            }

            if (fineStatusFilterSelect) {
                fineStatusFilterSelect.addEventListener('change', handleFineStatusFilter);
            }

            if (fineResetFiltersBtn) {
                fineResetFiltersBtn.addEventListener('click', resetFineFilters);
            }

            if (booksEntriesSelect) {
                booksEntriesSelect.addEventListener('change', handleBooksPerPageChange);
            }

            if (finesEntriesSelect) {
                finesEntriesSelect.addEventListener('change', handleFinesPerPageChange);
            }

            // Button actions
            if (printReportBtn) {
                printReportBtn.addEventListener('click', printReport);
            }
        }

        // Keyboard navigation
        function setupKeyboardNavigation() {
            document.addEventListener('keydown', function(e) {
                // Ctrl+F to focus search
                if ((e.ctrlKey && e.key === 'f') || e.key === '/') {
                    e.preventDefault();
                    if (bookSearchInput) {
                        bookSearchInput.focus();
                        bookSearchInput.select();
                    }
                }

                if (e.key === 'Escape' && issuedBooksExportModal?.classList.contains('is-open')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    closeIssuedBooksExportModal();
                    return;
                }

                if (e.key === 'Escape' && fineReceiptExportModal?.classList.contains('is-open')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    closeFineReceiptExportModal();
                    return;
                }

                // Escape to clear search
                if (e.key === 'Escape' && document.activeElement === bookSearchInput) {
                    clearBookSearch();
                }

                if (e.key === 'Escape' && actionFeedbackConfirmModal?.classList.contains('is-open')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    closeConfirmationModal();
                }

                // Ctrl+P for print
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    printReport();
                }
            });
        }

        // Filter and search functions
        let filteredBooks = [...booksData];
        let currentSearchTerm = '';
        let currentStatusFilter = 'all';
        let currentBooksPage = 1;
        let booksPerPage = 10;
        let currentFineSearchTerm = '';
        let currentFineStatusFilter = 'all';
        let currentFinesPage = 1;
        let finesPerPage = 10;

        function handleBookSearch() {
            currentSearchTerm = bookSearchInput.value.toLowerCase().trim();
            applyFilters();
        }

        function handleStatusFilter() {
            currentStatusFilter = statusFilterSelect.value;
            applyFilters();
        }

        function handleBooksPerPageChange() {
            booksPerPage = Number(booksEntriesSelect?.value || 10);
            currentBooksPage = 1;
            renderBooksTable();
        }

        function handleFineSearch() {
            currentFineSearchTerm = fineSearchInput?.value.toLowerCase().trim() || '';
            currentFinesPage = 1;
            fineReceiptExportWorkflow?.clearCache({ resetScope: false });
            renderFinesTable();
        }

        function handleFineStatusFilter() {
            currentFineStatusFilter = fineStatusFilterSelect?.value || 'all';
            currentFinesPage = 1;
            fineReceiptExportWorkflow?.clearCache({ resetScope: false });
            renderFinesTable();
        }

        function handleFinesPerPageChange() {
            finesPerPage = Number(finesEntriesSelect?.value || 10);
            currentFinesPage = 1;
            renderFinesTable();
        }

        function applyFilters() {
            filteredBooks = booksData.filter(book => {
                // Apply search filter
                const matchesSearch = !currentSearchTerm ||
                    book.title.toLowerCase().includes(currentSearchTerm) ||
                    (book.author || '').toLowerCase().includes(currentSearchTerm) ||
                    book.isbn.toLowerCase().includes(currentSearchTerm);

                // Apply status filter
                const matchesStatus = currentStatusFilter === 'all' ||
                    book.status === currentStatusFilter;

                return matchesSearch && matchesStatus;
            });

            currentBooksPage = 1;
            issuedBooksExportWorkflow?.clearCache({ resetScope: false });
            renderBooksTable();
        }

        function clearBookSearch() {
            bookSearchInput.value = '';
            currentSearchTerm = '';
            applyFilters();
        }

        function resetBookFilters() {
            if (bookSearchInput) bookSearchInput.value = '';
            if (statusFilterSelect) statusFilterSelect.value = 'all';
            currentSearchTerm = '';
            currentStatusFilter = 'all';
            currentBooksPage = 1;
            applyFilters();
        }

        function resetFineFilters() {
            if (fineSearchInput) fineSearchInput.value = '';
            if (fineStatusFilterSelect) fineStatusFilterSelect.value = 'all';
            currentFineSearchTerm = '';
            currentFineStatusFilter = 'all';
            currentFinesPage = 1;
            fineReceiptExportWorkflow?.clearCache({ resetScope: false });
            renderFinesTable();
        }

        function getFilteredFines() {
            return finesData.filter(fine => {
                const normalizedStatus = String(fine.paymentStatus || 'pending').toLowerCase();
                const matchesStatus = currentFineStatusFilter === 'all' || normalizedStatus === currentFineStatusFilter;
                const searchHaystack = [
                    fine.bookName || '',
                    normalizedStatus,
                    normalizedStatus === 'pending' ? 'unpaid pending' : '',
                    String(fine.fineAmount ?? ''),
                    String(fine.daysOverdue ?? '')
                ].join(' ').toLowerCase();
                const matchesSearch = !currentFineSearchTerm || searchHaystack.includes(currentFineSearchTerm);

                return matchesStatus && matchesSearch;
            });
        }

        // Render books table
        function renderBooksTable() {
            if (!booksTableBody) return;

            booksTableBody.innerHTML = '';

            const totalBooks = filteredBooks.length;
            const totalPages = Math.max(1, Math.ceil(totalBooks / booksPerPage));
            currentBooksPage = Math.min(currentBooksPage, totalPages);

            if (totalBooks === 0) {
                if (booksTableSummary) booksTableSummary.textContent = 'Showing 0 books';
                if (booksTablePageInfo) booksTablePageInfo.textContent = 'Page 0 of 0';
                if (booksTablePagination) booksTablePagination.innerHTML = '';
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
            <td colspan="8">
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                    </svg>
                    <h3>No Books Found</h3>
                    <p>No books found matching your criteria. Books issued to this student will appear here.</p>
                </div>
            </td>
        `;
                booksTableBody.appendChild(emptyRow);
                if (issuedBooksExportModal?.classList.contains('is-open')) {
                    issuedBooksExportWorkflow?.render();
                }
                return;
            }

            const startIndex = (currentBooksPage - 1) * booksPerPage;
            const endIndex = Math.min(startIndex + booksPerPage, totalBooks);
            const pageBooks = filteredBooks.slice(startIndex, endIndex);

            pageBooks.forEach(book => {
                const row = document.createElement('tr');

                // Get status badge class, text and icon
                let statusClass = '';
                let statusText = '';
                let statusIcon = '';
                switch(book.status) {
                    case 'issued':
                        statusClass = 'issued';
                        statusText = 'Issued';
                        statusIcon = 'fas fa-book-open';
                        break;
                    case 'overdue':
                        statusClass = 'overdue';
                        statusText = 'Overdue';
                        statusIcon = 'fas fa-exclamation-circle';
                        break;
                    case 'returned':
                        statusClass = 'returned';
                        statusText = 'Returned';
                        statusIcon = 'fas fa-redo';
                        break;
                }

                row.innerHTML = `
            <td>
                <strong>${book.title}</strong>
            </td>
            <td>${book.isbn}</td>
            <td>${book.issueDate}</td>
            <td>${book.dueDate}</td>
            <td>${book.returnDate}</td>
            <td>
                <span class="table-badge ${statusClass}"><i class="${statusIcon}" style="font-size: 10px; margin-right: 2px;"></i>${statusText}</span>
            </td>
            <td>
                <span class="fine-amount ${book.fine > 0 ? 'has-fine' : ''}">
                    ₹${book.fine}
                </span>
            </td>
            <td>
                <button class="view-btn" onclick="viewBookDetails(${book.id})" title="View Details">
                    <i class="fas fa-eye"></i> View
                </button>
            </td>
        `;

                booksTableBody.appendChild(row);
            });

            if (booksTableSummary) {
                booksTableSummary.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalBooks} books`;
            }

            if (booksTablePageInfo) {
                booksTablePageInfo.textContent = `Page ${currentBooksPage} of ${totalPages}`;
            }

            if (booksTablePagination) {
                booksTablePagination.innerHTML = renderPaginationControls(currentBooksPage, totalPages, 'changeBooksPage');
            }

            if (issuedBooksExportModal?.classList.contains('is-open')) {
                issuedBooksExportWorkflow?.render();
            }

        }

        function setupIssuedBooksExportWorkflow() {
            if (typeof window.ReportExportWorkflow !== 'function') {
                return;
            }

            issuedBooksExportWorkflow = new window.ReportExportWorkflow({
                modalId: 'issuedBooksExportModal',
                idPrefix: 'issuedBooksExport',
                scopeName: 'issuedBooksExportScope',
                downloadFormat: 'excel-xml',
                sheetName: 'Issued Books',
                document: {
                    systemTitle: issuedBooksReportSystemTitle,
                    reportTitle: 'Issued Books Report',
                },
                labels: {
                    printButton: 'Print Report',
                    allScopePrintButton: 'Print Full Report',
                    downloadButton: 'Download Excel',
                    allScopeDownloadButton: 'Download Full Excel',
                },
                messages: {
                    emptyMessage: 'There are no issued books in the current result set.',
                    preparingMessage: 'Preparing the full issued books report. Please wait.',
                    printReadyMessage: 'The print dialog will open in a new window for the current issued books page.',
                    fullPrintReadyMessage: 'The print dialog will open in a new window for the full filtered issued books report.',
                    exportReadyMessage: 'The current issued books page has been exported to Excel.',
                    fullExportReadyMessage: 'The full filtered issued books report has been exported to Excel.',
                    exportRouteMissingMessage: 'The full issued books report is not available right now.',
                    fullLoadFailedMessage: 'Something went wrong while preparing the issued books report.',
                },
                columns: [
                    { key: 'title', label: 'Book Title', width: '28%', emphasis: true },
                    { key: 'isbn', label: 'ISBN', width: '14%', nowrap: true },
                    { key: 'issueDate', label: 'Issue Date', width: '12%', nowrap: true },
                    { key: 'dueDate', label: 'Due Date', width: '12%', nowrap: true },
                    { key: 'returnDate', label: 'Return Date', width: '12%', nowrap: true },
                    { key: 'status', label: 'Status', width: '10%', align: 'center', nowrap: true },
                    { key: 'fineAmount', label: 'Fine Amount', width: '12%', align: 'right', nowrap: true },
                ],
                openModal: (modalId, focusTarget) => openIssuedBooksExportModal(modalId, focusTarget),
                closeModal: (modalId) => closeIssuedBooksExportModal(modalId),
                showToast: (type, title, message, timeout) => showToast(message, type, { title, timeout }),
                getCurrentRows: () => getCurrentBooksPageRows(),
                getAllRows: () => ({
                    rows: filteredBooks,
                    generatedAt: new Date().toISOString(),
                }),
                mapRow: (book) => mapIssuedBookToExportRow(book),
                buildFilterParams: () => {
                    const params = new URLSearchParams();
                    const searchValue = bookSearchInput?.value?.trim();

                    if (searchValue) {
                        params.set('search', searchValue);
                    }

                    if (currentStatusFilter && currentStatusFilter !== 'all') {
                        params.set('status', currentStatusFilter);
                    }

                    return params;
                },
                getListingState: () => ({
                    total: filteredBooks.length,
                    currentPage: Math.max(1, currentBooksPage),
                    lastPage: Math.max(1, Math.ceil(filteredBooks.length / booksPerPage)),
                    perPage: Math.max(1, booksPerPage),
                }),
                getScopeLabel: (scope) => scope === 'all' ? 'Entire filtered issued books list' : 'Current page',
                getFilename: (context) => buildIssuedBooksExportFilename(context),
                getDocumentDetails: () => getIssuedBooksDocumentDetails(),
                getExportMetaRows: (context) => buildIssuedBooksExportMetaRows(context),
                describeContext: (context) => describeIssuedBooksExportContext(context),
            }).init();
        }

        function openIssuedBooksExportModal(modalId, focusTarget) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            const panel = modal.querySelector('.report-export-panel');
            const closeButton = modal.querySelector('.report-export-close-btn');

            const activeElement = document.activeElement;
            issuedBooksExportLastTrigger = activeElement && !modal.contains(activeElement)
                ? activeElement
                : focusTarget;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            modal.scrollTop = 0;
            panel?.scrollTo?.({ top: 0, behavior: 'auto' });

            window.setTimeout(() => {
                const nextFocusTarget = closeButton || panel || focusTarget;

                if (typeof nextFocusTarget?.focus === 'function') {
                    try {
                        nextFocusTarget.focus({ preventScroll: true });
                    } catch (error) {
                        nextFocusTarget.focus();
                    }
                }
            }, 20);
        }

        function closeIssuedBooksExportModal(modalId = 'issuedBooksExportModal') {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            issuedBooksExportWorkflow?.handleModalClosed?.();

            const focusTarget = issuedBooksExportLastTrigger;
            issuedBooksExportLastTrigger = null;

            if (typeof focusTarget?.focus === 'function') {
                window.setTimeout(() => focusTarget.focus(), 20);
            }
        }

        function setupFineReceiptExportWorkflow() {
            if (typeof window.ReportExportWorkflow !== 'function') {
                return;
            }

            fineReceiptExportWorkflow = new window.ReportExportWorkflow({
                modalId: 'fineReceiptExportModal',
                idPrefix: 'fineReceiptExport',
                scopeName: 'fineReceiptExportScope',
                downloadFormat: 'excel-xml',
                sheetName: 'Fine Receipt',
                document: {
                    systemTitle: issuedBooksReportSystemTitle,
                    reportTitle: 'Fine Receipt',
                },
                labels: {
                    printButton: 'Print Receipt',
                    allScopePrintButton: 'Print Full Receipt',
                    downloadButton: 'Download Excel',
                    allScopeDownloadButton: 'Download Full Excel',
                },
                messages: {
                    emptyMessage: 'There are no fine records in the current result set.',
                    preparingMessage: 'Preparing the full fine receipt. Please wait.',
                    printReadyMessage: 'The print dialog will open in a new window for the current fine receipt page.',
                    fullPrintReadyMessage: 'The print dialog will open in a new window for the full filtered fine receipt.',
                    exportReadyMessage: 'The current fine receipt page has been exported to Excel.',
                    fullExportReadyMessage: 'The full filtered fine receipt has been exported to Excel.',
                    exportRouteMissingMessage: 'The full fine receipt is not available right now.',
                    fullLoadFailedMessage: 'Something went wrong while preparing the fine receipt.',
                },
                columns: [
                    { key: 'bookName', label: 'Book Name', width: '40%', emphasis: true },
                    { key: 'daysOverdue', label: 'Days Overdue', width: '16%', align: 'center', nowrap: true },
                    { key: 'fineAmount', label: 'Fine Amount', width: '18%', align: 'right', nowrap: true },
                    { key: 'paymentStatus', label: 'Payment Status', width: '26%', align: 'center', nowrap: true },
                ],
                openModal: (modalId, focusTarget) => openFineReceiptExportModal(modalId, focusTarget),
                closeModal: (modalId) => closeFineReceiptExportModal(modalId),
                showToast: (type, title, message, timeout) => showToast(message, type, { title, timeout }),
                getCurrentRows: () => getCurrentFinesPageRows(),
                getAllRows: () => ({
                    rows: getFilteredFines(),
                    generatedAt: new Date().toISOString(),
                }),
                mapRow: (fine) => mapFineToReceiptRow(fine),
                buildFilterParams: () => {
                    const params = new URLSearchParams();
                    const searchValue = fineSearchInput?.value?.trim();

                    if (searchValue) {
                        params.set('search', searchValue);
                    }

                    if (currentFineStatusFilter && currentFineStatusFilter !== 'all') {
                        params.set('status', currentFineStatusFilter);
                    }

                    return params;
                },
                getListingState: () => ({
                    total: getFilteredFines().length,
                    currentPage: Math.max(1, currentFinesPage),
                    lastPage: Math.max(1, Math.ceil(getFilteredFines().length / finesPerPage)),
                    perPage: Math.max(1, finesPerPage),
                }),
                getScopeLabel: (scope) => scope === 'all' ? 'Entire filtered fine receipt' : 'Current page',
                getFilename: (context) => buildFineReceiptExportFilename(context),
                getDocumentDetails: () => getIssuedBooksDocumentDetails(),
                getExportMetaRows: (context) => buildFineReceiptExportMetaRows(context),
                describeContext: (context) => describeFineReceiptExportContext(context),
            }).init();
        }

        function openFineReceiptExportModal(modalId, focusTarget) {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            const panel = modal.querySelector('.report-export-panel');
            const closeButton = modal.querySelector('.report-export-close-btn');

            const activeElement = document.activeElement;
            fineReceiptExportLastTrigger = activeElement && !modal.contains(activeElement)
                ? activeElement
                : focusTarget;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            modal.scrollTop = 0;
            panel?.scrollTo?.({ top: 0, behavior: 'auto' });

            window.setTimeout(() => {
                const nextFocusTarget = closeButton || panel || focusTarget;

                if (typeof nextFocusTarget?.focus === 'function') {
                    try {
                        nextFocusTarget.focus({ preventScroll: true });
                    } catch (error) {
                        nextFocusTarget.focus();
                    }
                }
            }, 20);
        }

        function closeFineReceiptExportModal(modalId = 'fineReceiptExportModal') {
            const modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            fineReceiptExportWorkflow?.handleModalClosed?.();

            const focusTarget = fineReceiptExportLastTrigger;
            fineReceiptExportLastTrigger = null;

            if (typeof focusTarget?.focus === 'function') {
                window.setTimeout(() => focusTarget.focus(), 20);
            }
        }

        function getCurrentBooksPageRows() {
            const startIndex = Math.max(0, (currentBooksPage - 1) * booksPerPage);
            return filteredBooks.slice(startIndex, startIndex + booksPerPage);
        }

        function getCurrentFinesPageRows() {
            const filteredFines = getFilteredFines();
            const startIndex = Math.max(0, (currentFinesPage - 1) * finesPerPage);
            return filteredFines.slice(startIndex, startIndex + finesPerPage);
        }

        function mapIssuedBookToExportRow(book) {
            return {
                title: book?.title || 'N/A',
                isbn: book?.isbn || 'N/A',
                issueDate: book?.issueDate || 'N/A',
                dueDate: book?.dueDate || 'N/A',
                returnDate: book?.returnDate || '-',
                status: formatDisplayLabel(book?.status, 'N/A'),
                fineAmount: formatCurrency(book?.fine),
            };
        }

        function getIssuedBooksFilterSummary() {
            const searchValue = bookSearchInput?.value?.trim() || '';
            const selectedStatusLabel = statusFilterSelect?.selectedOptions?.[0]?.textContent?.trim() || '';

            return {
                search: searchValue || 'All books',
                status: currentStatusFilter === 'all'
                    ? 'All statuses'
                    : (selectedStatusLabel || formatDisplayLabel(currentStatusFilter, 'All statuses')),
            };
        }

        function getIssuedBooksDocumentDetails() {
            return [
                { label: 'Student Name', value: issuedBooksReportStudent.name },
                { label: 'Roll No', value: issuedBooksReportStudent.rollNo },
                { label: 'Email', value: issuedBooksReportStudent.email },
                { label: 'Department', value: issuedBooksReportStudent.department },
                { label: 'Semester', value: String(issuedBooksReportStudent.semester || 'N/A') },
                { label: 'Batch', value: issuedBooksReportStudent.batch },
            ];
        }

        function buildIssuedBooksExportMetaRows(context) {
            if (typeof window.ReportExportTemplates?.buildStandardMetaRows === 'function') {
                return window.ReportExportTemplates.buildStandardMetaRows({
                    systemTitle: issuedBooksReportSystemTitle,
                    reportTitle: 'Issued Books Report',
                    generatedAtLabel: context.generatedAtLabel,
                    documentDetails: getIssuedBooksDocumentDetails(),
                });
            }

            return [
                [issuedBooksReportSystemTitle],
                ['Issued Books Report'],
                [context.generatedAtLabel],
                [''],
                ...getIssuedBooksDocumentDetails().map((detail) => [detail.label, detail.value]),
                [''],
            ];
        }

        function buildIssuedBooksExportFilename(context) {
            const generatedAt = context?.generatedAt instanceof Date
                ? context.generatedAt
                : new Date(context?.generatedAt || Date.now());
            const dateStamp = Number.isNaN(generatedAt.getTime())
                ? new Date().toISOString().slice(0, 10)
                : generatedAt.toISOString().slice(0, 10);
            const scopeLabel = context?.isAllScope ? 'full' : 'page';
            const studentSlug = String(issuedBooksReportStudent.rollNo || issuedBooksReportStudent.name || 'student')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '') || 'student';

            return `issued-books-${studentSlug}-${scopeLabel}-${dateStamp}.xls`;
        }

        function mapFineToReceiptRow(fine) {
            const statusMeta = getFineStatusMeta(fine?.paymentStatus);

            return {
                bookName: fine?.bookName || 'Unknown',
                daysOverdue: fine?.daysOverdue || '0 days',
                fineAmount: formatCurrency(fine?.fineAmount),
                paymentStatus: statusMeta.label,
            };
        }

        function getFineReceiptFilterSummary() {
            const searchValue = fineSearchInput?.value?.trim() || '';
            const selectedStatusLabel = fineStatusFilterSelect?.selectedOptions?.[0]?.textContent?.trim() || '';

            return {
                search: searchValue || 'All fines',
                status: currentFineStatusFilter === 'all'
                    ? 'All statuses'
                    : (selectedStatusLabel || formatDisplayLabel(currentFineStatusFilter, 'All statuses')),
            };
        }

        function getFineReceiptTotals(fines = getFilteredFines()) {
            return fines.reduce((summary, fine) => {
                const amountValue = fine?.fineAmount;
                const amount = typeof amountValue === 'string'
                    ? Number(String(amountValue).replace(/[^0-9.-]+/g, ''))
                    : Number(amountValue || 0);
                const safeAmount = Number.isFinite(amount) ? amount : 0;
                const statusKey = getFineStatusMeta(fine?.paymentStatus).key;

                summary.totalAmount += safeAmount;

                if (statusKey === 'pending') {
                    summary.pendingAmount += safeAmount;
                }

                return summary;
            }, {
                totalAmount: 0,
                pendingAmount: 0,
            });
        }

        function buildFineReceiptExportMetaRows(context) {
            const totals = getFineReceiptTotals(context.rows);

            if (typeof window.ReportExportTemplates?.buildStandardMetaRows === 'function') {
                return window.ReportExportTemplates.buildStandardMetaRows({
                    systemTitle: issuedBooksReportSystemTitle,
                    reportTitle: 'Fine Receipt',
                    generatedAtLabel: context.generatedAtLabel,
                    documentDetails: getIssuedBooksDocumentDetails(),
                    extraRows: [
                        ['Receipt Total', formatCurrency(totals.totalAmount)],
                        ['Pending Total', formatCurrency(totals.pendingAmount)],
                    ],
                });
            }

            return [
                [issuedBooksReportSystemTitle],
                ['Fine Receipt'],
                [context.generatedAtLabel],
                [''],
                ...getIssuedBooksDocumentDetails().map((detail) => [detail.label, detail.value]),
                ['Receipt Total', formatCurrency(totals.totalAmount)],
                ['Pending Total', formatCurrency(totals.pendingAmount)],
                [''],
            ];
        }

        function buildFineReceiptExportFilename(context) {
            const generatedAt = context?.generatedAt instanceof Date
                ? context.generatedAt
                : new Date(context?.generatedAt || Date.now());
            const dateStamp = Number.isNaN(generatedAt.getTime())
                ? new Date().toISOString().slice(0, 10)
                : generatedAt.toISOString().slice(0, 10);
            const scopeLabel = context?.isAllScope ? 'full' : 'page';
            const studentSlug = String(issuedBooksReportStudent.rollNo || issuedBooksReportStudent.name || 'student')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '') || 'student';

            return `fine-receipt-${studentSlug}-${scopeLabel}-${dateStamp}.xls`;
        }

        function describeFineReceiptExportContext(context) {
            const filters = getFineReceiptFilterSummary();
            const totals = getFineReceiptTotals(context.rows);
            const rowLabel = context.rowsReady === 1 ? 'fine record' : 'fine records';

            return {
                badgeLabel: context.isAllScope ? 'Filtered receipt' : 'Current page',
                headline: `${context.rowsReady} ${rowLabel} ready`,
                subtext: `Print or download the fine receipt for ${issuedBooksReportStudent.name}.`,
                previewCaption: context.isAllScope
                    ? 'Preview of the first rows from the full filtered fine receipt.'
                    : 'Preview of the current fine page that will be printed or downloaded.',
                previewCountText: `${context.rowsReady} ${context.rowsReady === 1 ? 'row' : 'rows'}`,
                footerNote: `Filters applied: Search ${filters.search} | ${filters.status}.`,
                emptyMessage: 'No fine records are available for this receipt.',
                summaryItems: [
                    { label: 'Student', value: issuedBooksReportStudent.name },
                    { label: 'Scope', value: context.scopeLabel },
                    { label: 'Rows included', value: `${context.rowsReady} ${rowLabel}` },
                    { label: 'Receipt total', value: formatCurrency(totals.totalAmount) },
                    { label: 'Pending total', value: formatCurrency(totals.pendingAmount) },
                    { label: 'Status filter', value: filters.status },
                ],
            };
        }

        function describeIssuedBooksExportContext(context) {
            const filters = getIssuedBooksFilterSummary();
            const rowLabel = context.rowsReady === 1 ? 'book' : 'books';

            return {
                badgeLabel: context.isAllScope ? 'Filtered report' : 'Current page',
                headline: `${context.rowsReady} issued ${rowLabel} ready`,
                subtext: `Print or download the issued books report for ${issuedBooksReportStudent.name}.`,
                previewCaption: context.isAllScope
                    ? 'Preview of the first rows from the full filtered issued books report.'
                    : 'Preview of the current issued books page that will be printed or downloaded.',
                previewCountText: `${context.rowsReady} ${rowLabel}`,
                footerNote: `Filters applied: Search ${filters.search} | ${filters.status}.`,
                emptyMessage: 'No issued books are available for this report.',
                summaryItems: [
                    { label: 'Student', value: issuedBooksReportStudent.name },
                    { label: 'Roll No', value: issuedBooksReportStudent.rollNo },
                    { label: 'Scope', value: context.scopeLabel },
                    { label: 'Rows included', value: `${context.rowsReady} ${rowLabel}` },
                    { label: 'Search', value: filters.search },
                    { label: 'Status filter', value: filters.status },
                ],
            };
        }

        function printReport() {
            if (!issuedBooksExportWorkflow) {
                showToast('Issued books export is unavailable right now.', 'error');
                return;
            }

            issuedBooksExportWorkflow.open();
        }

        function formatDisplayLabel(value, fallback = 'N/A') {
            if (value === null || value === undefined || value === '') {
                return fallback;
            }

            return String(value)
                .replace(/[_-]+/g, ' ')
                .replace(/\b\w/g, character => character.toUpperCase());
        }

        function getFineStatusMeta(status) {
            const normalizedStatus = String(status || 'n/a').toLowerCase() === 'unpaid'
                ? 'pending'
                : String(status || 'n/a').toLowerCase();

            const labels = {
                pending: 'Pending',
                paid: 'Paid',
                waived: 'Waived',
                'n/a': 'N/A',
            };

            return {
                key: labels[normalizedStatus] ? normalizedStatus : 'n/a',
                label: labels[normalizedStatus] || 'N/A',
            };
        }

        function viewBookDetails(bookId) {
            const rawBook = booksData.find(b => b.id === bookId);
            if (!rawBook) {
                showToast('Book details not found.', 'error');
                return;
            }
            const book = normalizeStudentBookRecord(rawBook);

            // Populate book cover using DOM to avoid inline onerror quoting issues
            const coverContainer = document.getElementById('bookCoverContent');
            coverContainer.innerHTML = '';
            if (book.coverImage) {
                const img = document.createElement('img');
                const normalizedCoverPath = String(book.coverImage).replace(/^storage\//, '');
                img.src = /^https?:\/\//i.test(book.coverImage)
                    ? book.coverImage
                    : '{{ asset("storage") }}/' + normalizedCoverPath;
                img.alt = book.title || '';
                img.className = 'book-cover-image';
                img.style.maxHeight = '240px';
                img.style.maxWidth = '160px';
                img.addEventListener('error', function() {
                    coverContainer.innerHTML = `<div class="book-cover-avatar">${(book.title || '').charAt(0).toUpperCase()}</div>`;
                });
                coverContainer.appendChild(img);
            } else {
                coverContainer.innerHTML = `<div class="book-cover-avatar">${(book.title || '').charAt(0).toUpperCase()}</div>`;
            }

            // Populate book details
            document.getElementById('bookDetailsTitle').textContent = book.title;
            document.getElementById('bookDetailsAuthor').textContent = `by ${book.author || 'Unknown Author'}`;
            document.getElementById('bookDetailsISBN').textContent = book.isbn;
            document.getElementById('bookDetailsPublisher').textContent = book.publisher;
            document.getElementById('bookDetailsCondition').textContent = formatDisplayLabel(book.condition, 'Good');
            document.getElementById('bookDetailsCategory').textContent = book.category || 'Uncategorized';
            document.getElementById('bookDetailsCategoryValue').textContent = book.category || 'Uncategorized';
            document.getElementById('bookDetailsTransactionId').textContent = book.transactionId || 'N/A';
            document.getElementById('bookDetailsIssueDate').textContent = book.issueDate || 'N/A';
            document.getElementById('bookDetailsDueDate').textContent = book.dueDate;
            document.getElementById('bookDetailsReturnDate').textContent = book.returnDate;
            document.getElementById('bookDetailsIssuedBy').textContent = book.issuedBy || 'System';
            document.getElementById('bookDetailsRenewalCount').textContent = String(book.renewalCount ?? 0);
            document.getElementById('bookDetailsFine').textContent = `₹${parseFloat(book.fine).toFixed(2)}`;
            document.getElementById('bookDetailsDescription').textContent = book.description || 'No description available';

            // Status badge
            const statusBadge = document.getElementById('bookDetailsStatus');
            let statusText = formatDisplayLabel(book.status, 'Issued');
            statusBadge.innerHTML = `<span class="book-detail-badge status-${book.status}">${statusText}</span>`;

            // Show/hide overdue info
            const overdueInfo = document.getElementById('overdueInfo');
            if (book.status === 'overdue') {
                overdueInfo.hidden = false;
                document.getElementById('bookDetailsDaysOverdue').textContent = `${book.daysOverdue} days`;
            } else {
                overdueInfo.hidden = true;
                document.getElementById('bookDetailsDaysOverdue').textContent = '';
            }

            // Fine status is only visible when a fine record exists
            const fineStatusInfo = document.getElementById('fineStatusInfo');
            const fineStatusBadge = document.getElementById('bookDetailsFineStatus');
            if (book.hasFine) {
                const fineStatus = getFineStatusMeta(book.fineStatus);
                fineStatusInfo.hidden = false;
                fineStatusBadge.innerHTML = `<span class="fine-status-badge fine-status-${fineStatus.key}">${fineStatus.label}</span>`;
            } else {
                fineStatusInfo.hidden = true;
                fineStatusBadge.innerHTML = '';
            }

            // Show modal
            const overlay = document.getElementById('bookDetailsOverlay');
            overlay.classList.add('show');
        }

        function closeBookDetailsModal() {
            const overlay = document.getElementById('bookDetailsOverlay');
            overlay.classList.remove('show');
        }

        function closeActivityDetails() {
            const overlay = document.getElementById('activityDetailsOverlay');
            overlay?.classList.remove('show');
        }

        // Back button functionality
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.activeElement.tagName !== 'INPUT') {
                if (actionFeedbackConfirmModal?.classList.contains('is-open')) {
                    closeConfirmationModal();
                    return;
                }

                const roleOverlay = document.getElementById('roleModalOverlay');
                if (roleOverlay?.classList.contains('show')) {
                    closeRoleModal();
                    return;
                }

                const adjustOverlay = document.getElementById('adjustFineOverlay');
                if (adjustOverlay?.classList.contains('show')) {
                    closeFineModal('adjust');
                    return;
                }

                const waiveOverlay = document.getElementById('waiveFineOverlay');
                if (waiveOverlay?.classList.contains('show')) {
                    closeFineModal('waive');
                    return;
                }

                const historyOverlay = document.getElementById('historyOverlay');
                if (historyOverlay?.classList.contains('show')) {
                    closeFineModal('history');
                    return;
                }

                if (issuedBooksExportModal?.classList.contains('is-open')) {
                    closeIssuedBooksExportModal();
                    return;
                }

                if (fineReceiptExportModal?.classList.contains('is-open')) {
                    closeFineReceiptExportModal();
                    return;
                }

                const bookDetailsOverlay = document.getElementById('bookDetailsOverlay');
                if (bookDetailsOverlay?.classList.contains('show')) {
                    closeBookDetailsModal();
                    return;
                }

                const activityDetailsOverlay = document.getElementById('activityDetailsOverlay');
                if (activityDetailsOverlay?.classList.contains('show')) {
                    closeActivityDetails();
                    return;
                }

                // Navigate back
                const backLink = document.querySelector('.back-link');
                if (backLink && backLink.href) {
                    window.location.href = backLink.href;
                }
            }
        });


        // Add this JavaScript to your push('scripts') section - Place it AFTER your Part 1 JavaScript

        // Initialize Part 2
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                renderFinesTable();
                renderActivityLogs();
                setupPart2EventListeners();
                setupPart2KeyboardNavigation();
            }, 100);
        });

        // Setup event listeners for Part 2
        function setupPart2EventListeners() {
            // Account Management Buttons
            document.getElementById('resetPasswordBtn')?.addEventListener('click', resetPassword);
            document.getElementById('activateAccountBtn')?.addEventListener('click', activateAccount);
            document.getElementById('deactivateAccountBtn')?.addEventListener('click', deactivateAccount);
            document.getElementById('changeRoleBtn')?.addEventListener('click', changeRole);
            document.getElementById('deleteAccountBtn')?.addEventListener('click', deleteAccount);

            // Role Modal Event Listeners
            document.querySelectorAll('.role-option').forEach(option => {
                option.addEventListener('click', function() {
                    const role = this.getAttribute('data-role');
                    selectRole(role);
                });
            });
            document.getElementById('cancelRoleBtn')?.addEventListener('click', closeRoleModal);
            document.getElementById('confirmRoleBtn')?.addEventListener('click', submitRoleChange);

            // Close modal on overlay click
            document.getElementById('roleModalOverlay')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeRoleModal();
                }
            });

            // Fine Management
            document.getElementById('generateReceiptBtn')?.addEventListener('click', generateReceipt);

            // Privilege Settings
            document.getElementById('saveChangesBtn')?.addEventListener('click', savePrivilegeSettings);
            document.getElementById('setDefaultPrivilegesBtn')?.addEventListener('click', resetPrivilegeSettings);
            
            // Load privilege settings on page load
            loadPrivilegeSettings().catch(() => {});

            // Fine action buttons will be set up in renderFinesTable

            // Book Details Modal
            document.getElementById('closeBookDetailsBtn')?.addEventListener('click', closeBookDetailsModal);
            document.getElementById('bookDetailsOverlay')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeBookDetailsModal();
                }
            });

            document.getElementById('closeActivityDetailsBtn')?.addEventListener('click', closeActivityDetails);
            document.getElementById('activityDetailsOverlay')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeActivityDetails();
                }
            });
        }

        // Setup keyboard navigation for Part 2
        function setupPart2KeyboardNavigation() {
            document.addEventListener('keydown', function(e) {
                // Alt+R - Reset Password
                if (e.altKey && e.key === 'r') {
                    e.preventDefault();
                    resetPassword();
                }

                // Alt+A - Activate/Deactivate Account
                if (e.altKey && e.key === 'a') {
                    e.preventDefault();
                    if (studentStatus === 'inactive') {
                        activateAccount();
                    } else {
                        deactivateAccount();
                    }
                }

                // Alt+C - Change Role
                if (e.altKey && e.key === 'c') {
                    e.preventDefault();
                    changeRole();
                }

                // Alt+D - Delete Account (with confirmation)
                if (e.altKey && e.key === 'd') {
                    e.preventDefault();
                    deleteAccount();
                }

                // Alt+G - Generate Receipt
                if (e.altKey && e.key === 'g') {
                    e.preventDefault();
                    generateReceipt();
                }

                // Alt+P - Save Privilege Settings
                if (e.altKey && e.key === 'p') {
                    e.preventDefault();
                    savePrivilegeSettings();
                }
            });
        }

        // Render fines table
        function renderFinesTable() {
            const finesTableBody = document.getElementById('finesTableBody');
            if (!finesTableBody) return;

            finesTableBody.innerHTML = '';

            const filteredFines = getFilteredFines();
            const totalFines = filteredFines.length;
            const totalPages = Math.max(1, Math.ceil(totalFines / finesPerPage));
            currentFinesPage = Math.min(currentFinesPage, totalPages);

            if (totalFines === 0) {
                if (finesTableSummary) finesTableSummary.textContent = 'Showing 0 fines';
                if (finesTablePageInfo) finesTablePageInfo.textContent = 'Page 0 of 0';
                if (finesTablePagination) finesTablePagination.innerHTML = '';
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
            <td colspan="5">
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <text x="12" y="16" text-anchor="middle" font-size="18" font-weight="700" fill="currentColor" stroke="none">₹</text>
                    </svg>
                    <h3>No Fines & Payments</h3>
                    <p>${currentFineSearchTerm || currentFineStatusFilter !== 'all'
                        ? 'Try adjusting the fine search or status filter.'
                        : 'No outstanding fines for this student. Fine records will appear here when applicable.'}</p>
                </div>
            </td>
        `;
                finesTableBody.appendChild(emptyRow);
                if (fineReceiptExportModal?.classList.contains('is-open')) {
                    fineReceiptExportWorkflow?.render();
                }
                return;
            }

            const startIndex = (currentFinesPage - 1) * finesPerPage;
            const endIndex = Math.min(startIndex + finesPerPage, totalFines);
            const pageFines = filteredFines.slice(startIndex, endIndex);

            pageFines.forEach(fine => {
                const row = document.createElement('tr');
                const normalizedPaymentStatus = String(fine.paymentStatus || 'pending').toLowerCase();

                // Get payment status badge
                let statusClass = '';
                let statusText = '';
                let statusIcon = '';
                
                if (normalizedPaymentStatus === 'paid') {
                    statusClass = 'paid';
                    statusText = 'Paid';
                    statusIcon = 'fas fa-check-circle';
                } else if (normalizedPaymentStatus === 'waived') {
                    statusClass = 'waive';
                    statusText = 'Waived';
                    statusIcon = 'fas fa-ban';
                } else {
                    statusClass = 'unpaid';
                    statusText = 'Pending';
                    statusIcon = 'fas fa-times-circle';
                }

                // Build action buttons
                let actionButtons = '';
                fine.actions.forEach(action => {
                    let buttonText = '';
                    let buttonClass = '';
                    let iconClass = '';

                    switch(action) {
                        case 'adjust':
                            buttonText = 'Adjust';
                            buttonClass = 'adjust';
                            iconClass = 'fas fa-sliders-h';
                            break;
                        case 'waive':
                            buttonText = 'Waive';
                            buttonClass = 'waive';
                            iconClass = 'fas fa-check-circle';
                            break;
                        case 'mark-paid':
                            buttonText = 'Mark Paid';
                            buttonClass = 'mark-paid';
                            iconClass = 'fas fa-credit-card';
                            break;
                        case 'view-history':
                            buttonText = 'View History';
                            buttonClass = 'view-history';
                            iconClass = 'fas fa-history';
                            break;
                    }

                    actionButtons += `
                <button class="action-btn-small ${buttonClass}" onclick="handleFineAction('${action}', ${fine.id})" title="${buttonText}">
                    <i class="${iconClass}"></i> ${buttonText}
                </button>
            `;
                });

                row.innerHTML = `
            <td><strong>${fine.bookName}</strong></td>
            <td>${fine.daysOverdue}</td>
            <td style="color: ${normalizedPaymentStatus === 'pending' ? '#dc2626' : normalizedPaymentStatus === 'waived' ? '#ea580c' : '#16a34a'}; font-weight: 600;">
                ₹${fine.fineAmount}
            </td>
            <td>
                <span class="payment-badge ${statusClass}"><i class="${statusIcon}" style="font-size: 10px; margin-right: 2px;"></i>${statusText}</span>
            </td>
            <td>
                <div class="fine-actions">
                    ${actionButtons}
                </div>
            </td>
        `;

                finesTableBody.appendChild(row);
            });

            if (finesTableSummary) {
                finesTableSummary.textContent = `Showing ${startIndex + 1}-${endIndex} of ${totalFines} fines`;
            }

            if (finesTablePageInfo) {
                finesTablePageInfo.textContent = `Page ${currentFinesPage} of ${totalPages}`;
            }

            if (finesTablePagination) {
                finesTablePagination.innerHTML = renderPaginationControls(currentFinesPage, totalPages, 'changeFinesPage');
            }

            if (fineReceiptExportModal?.classList.contains('is-open')) {
                fineReceiptExportWorkflow?.render();
            }

        }

        function changeBooksPage(page) {
            const nextPage = Number(page || 1);
            if (!nextPage) return;
            currentBooksPage = nextPage;
            renderBooksTable();
        }

        function changeFinesPage(page) {
            const nextPage = Number(page || 1);
            if (!nextPage) return;
            currentFinesPage = nextPage;
            renderFinesTable();
        }

        function getVisiblePaginationPages(currentPage, totalPages) {
            if (totalPages <= 7) {
                return Array.from({ length: totalPages }, (_, index) => index + 1);
            }

            const pages = [1];
            const startPage = Math.max(2, currentPage - 1);
            const endPage = Math.min(totalPages - 1, currentPage + 1);

            if (startPage > 2) {
                pages.push('ellipsis');
            }

            for (let page = startPage; page <= endPage; page += 1) {
                pages.push(page);
            }

            if (endPage < totalPages - 1) {
                pages.push('ellipsis');
            }

            pages.push(totalPages);

            return pages;
        }

        function renderPaginationControls(currentPage, totalPages, changeHandlerName) {
            if (totalPages <= 1) {
                return '';
            }

            const controls = [];
            controls.push(`
                <button type="button" class="admin-table-pagination-link${currentPage === 1 ? ' is-disabled' : ''}" onclick="${changeHandlerName}(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    &larr; Previous
                </button>
            `);

            getVisiblePaginationPages(currentPage, totalPages).forEach(page => {
                if (page === 'ellipsis') {
                    controls.push('<span class="admin-table-pagination-ellipsis" aria-hidden="true">&hellip;</span>');
                    return;
                }

                controls.push(`
                    <button type="button" class="admin-table-pagination-link ${page === currentPage ? 'is-active' : ''}" onclick="${changeHandlerName}(${page})" ${page === currentPage ? 'aria-current="page"' : ''}>
                        ${page}
                    </button>
                `);
            });

            controls.push(`
                <button type="button" class="admin-table-pagination-link${currentPage === totalPages ? ' is-disabled' : ''}" onclick="${changeHandlerName}(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    Next &rarr;
                </button>
            `);

            return controls.join('');
        }

        let activityLogsIndex = 0;
        let allActivityLogs = [];
        let activityLogLookup = new Map();

        function renderActivityLogs() {
            const activityTimeline = document.getElementById('activityTimeline');
            if (!activityTimeline) return;

            activityTimeline.innerHTML = '';
            allActivityLogs = [...activityLogs, ...(remainingActivityLogs || [])].map(log => ({
                ...log,
                id: String(log.id ?? ''),
                type: String(log.type || 'auth'),
                status: String(log.status || 'success'),
                metadata: log.metadata && typeof log.metadata === 'object' ? log.metadata : {},
            }));

            activityLogLookup = new Map(allActivityLogs.map(log => [String(log.id), log]));

            if (allActivityLogs.length === 0) {
                const emptyState = document.createElement('div');
                emptyState.className = 'empty-state';
                emptyState.innerHTML = `
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3>No Activity & Audit Logs</h3>
                    <p>No activity records found for this student yet. Activity logs will appear here as the student interacts with the system.</p>
                `;
                activityTimeline.appendChild(emptyState);
                return;
            }

            activityLogsIndex = 0;
            displayActivityLogsBatch();
        }

        function displayActivityLogsBatch() {
            const activityTimeline = document.getElementById('activityTimeline');
            if (!activityTimeline) return;

            const existingButton = activityTimeline.querySelector('.show-more-btn');
            if (existingButton) {
                existingButton.remove();
            }

            const logsToDisplay = allActivityLogs.slice(activityLogsIndex, activityLogsIndex + 10);
            logsToDisplay.forEach(log => appendActivityItem(log, activityTimeline));
            activityLogsIndex += 10;

            if (activityLogsIndex < allActivityLogs.length) {
                const showMoreButton = document.createElement('button');
                showMoreButton.type = 'button';
                showMoreButton.className = 'show-more-btn';
                showMoreButton.textContent = 'Show More';
                showMoreButton.addEventListener('click', displayActivityLogsBatch);
                activityTimeline.appendChild(showMoreButton);
            }

            refreshActivityTimelineTail();
        }

        function refreshActivityTimelineTail() {
            const items = document.querySelectorAll('#activityTimeline .activity-item');
            items.forEach(item => item.classList.remove('is-last'));
            items[items.length - 1]?.classList.add('is-last');
        }

        function appendActivityItem(log, container) {
            const activityItem = document.createElement('article');
            const typeConfig = getActivityTypeConfig(log.type);
            const statusConfig = getActivityStatusConfig(log.status);
            const roleMeta = getActivityRoleMeta(log.userRole);
            const technicalPanelId = `activityTechnicalDetails-${log.id}`;
            const resourceLabel = getActivityResourceLabel(log);

            activityItem.className = `activity-item ${String(log.type || 'auth').replace(/[^a-z0-9-_]/gi, '')}`;
            activityItem.innerHTML = `
                <div class="activity-connector" aria-hidden="true">
                    <span class="activity-dot"></span>
                    <span class="activity-line"></span>
                </div>
                <div class="activity-card">
                    <div class="activity-main">
                        <div class="activity-icon">
                            <i class="${escapeHtml(typeConfig.icon)}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <h4 class="activity-title">${escapeHtml(log.title || typeConfig.label)}</h4>
                                <span class="activity-status-badge status-${escapeHtml(statusConfig.key)}">${escapeHtml(statusConfig.label)}</span>
                            </div>
                            <p class="activity-desc">${escapeHtml(log.description || 'Activity recorded')}</p>
                            <div class="activity-meta">
                                <div class="activity-user">
                                    ${getActivityAvatarMarkup(log)}
                                    <div class="activity-user-info">
                                        <div class="activity-user-name-row">
                                            <span class="activity-user-name">${escapeHtml(log.userName || 'System')}</span>
                                            <span class="activity-role-badge role-${escapeHtml(roleMeta.roleClass)}">${escapeHtml(roleMeta.label)}</span>
                                        </div>
                                        <span class="activity-user-resource">${escapeHtml(resourceLabel)}</span>
                                    </div>
                                </div>
                                <div class="activity-meta-controls">
                                    <span class="activity-time-chip" title="${escapeHtml(log.fullTimestamp || log.time || 'N/A')}">
                                        <i class="far fa-clock"></i>
                                        ${escapeHtml(log.time || 'N/A')}
                                    </span>
                                    <button type="button" class="activity-toggle-btn${log.hasDetails ? '' : ' is-disabled'}" data-action="toggle-details" aria-expanded="false" aria-controls="${escapeHtml(technicalPanelId)}">
                                        <i class="fas fa-chevron-down"></i>
                                        ${log.hasDetails ? 'Details' : 'No Details'}
                                    </button>
                                </div>
                            </div>
                            <div class="activity-technical-panel" id="${escapeHtml(technicalPanelId)}" hidden>
                                <div class="activity-technical-grid">
                                    <div class="activity-technical-item">
                                        <span class="activity-technical-label">IP Address</span>
                                        <span class="activity-technical-value">${escapeHtml(log.ipAddress || 'Not captured')}</span>
                                    </div>
                                    <div class="activity-technical-item">
                                        <span class="activity-technical-label">Device Type</span>
                                        <span class="activity-technical-value">${escapeHtml(log.deviceType || 'Unknown device')}</span>
                                    </div>
                                    <div class="activity-technical-item">
                                        <span class="activity-technical-label">Browser</span>
                                        <span class="activity-technical-value">${escapeHtml(log.browser || 'Unknown browser')}</span>
                                    </div>
                                    <div class="activity-technical-item">
                                        <span class="activity-technical-label">Session ID</span>
                                        <span class="activity-technical-value">${escapeHtml(log.sessionId || 'Not captured')}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="activity-actions">
                                ${log.resourceUrl
                                    ? `<a class="activity-action-btn view-resource" href="${escapeHtml(log.resourceUrl)}" target="_blank" rel="noopener">
                                            <i class="fas fa-external-link-alt"></i>
                                            View Resource
                                       </a>`
                                    : `<span class="activity-action-btn view-resource is-disabled">
                                            <i class="fas fa-ban"></i>
                                            View Resource
                                       </span>`
                                }
                                <button type="button" class="activity-action-btn more-details" data-action="show-details">
                                    <i class="fas fa-info-circle"></i>
                                    More Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            activityItem.querySelector('[data-action="toggle-details"]')?.addEventListener('click', () => toggleTechnicalDetails(log.id));
            activityItem.querySelector('[data-action="show-details"]')?.addEventListener('click', () => showActivityDetails(log.id));
            container.appendChild(activityItem);
        }

        function toggleTechnicalDetails(logId) {
            const normalizedId = String(logId);
            const log = activityLogLookup.get(normalizedId);
            if (!log || !log.hasDetails) {
                return;
            }

            const panel = document.getElementById(`activityTechnicalDetails-${normalizedId}`);
            const toggleButton = document.querySelector(`[aria-controls="activityTechnicalDetails-${normalizedId}"]`);
            if (!panel || !toggleButton) return;

            const shouldOpen = panel.hidden;
            panel.hidden = !shouldOpen;
            toggleButton.setAttribute('aria-expanded', String(shouldOpen));
            toggleButton.innerHTML = `
                <i class="fas fa-chevron-${shouldOpen ? 'up' : 'down'}"></i>
                ${shouldOpen ? 'Hide Details' : 'Details'}
            `;
        }

        function showActivityDetails(logId) {
            const log = activityLogLookup.get(String(logId));
            const overlay = document.getElementById('activityDetailsOverlay');
            if (!log || !overlay) {
                showToast('Activity details are unavailable right now.', 'warning');
                return;
            }

            const typeConfig = getActivityTypeConfig(log.type);
            const statusConfig = getActivityStatusConfig(log.status);
            const roleMeta = getActivityRoleMeta(log.userRole);
            const resourceLabel = getActivityResourceLabel(log);
            const metadataList = document.getElementById('activityMetadataList');
            const resourceLink = document.getElementById('activityDetailsResourceLink');

            document.getElementById('activityDetailsTitle').textContent = log.title || typeConfig.label;
            document.getElementById('activityDetailsSubtitle').textContent = typeConfig.label;
            document.getElementById('activityDetailsStatus').innerHTML = `<span class="activity-status-badge status-${escapeHtml(statusConfig.key)}">${escapeHtml(statusConfig.label)}</span>`;
            document.getElementById('activityDetailsResource').textContent = resourceLabel;
            document.getElementById('activityDetailsTimestamp').textContent = log.fullTimestamp || log.time || 'N/A';
            document.getElementById('activityDetailsSession').textContent = log.sessionId || 'Not captured';
            document.getElementById('activityDetailsUser').innerHTML = `
                ${getActivityAvatarMarkup(log)}
                <div class="activity-user-info">
                    <div class="activity-user-name-row">
                        <span class="activity-user-name">${escapeHtml(log.userName || 'System')}</span>
                        <span class="activity-role-badge role-${escapeHtml(roleMeta.roleClass)}">${escapeHtml(roleMeta.label)}</span>
                    </div>
                    <span class="activity-user-resource">${escapeHtml(typeConfig.label)}</span>
                </div>
            `;
            document.getElementById('activityDetailsDescription').textContent = log.description || 'Activity recorded';
            document.getElementById('activityDetailsIp').textContent = log.ipAddress || 'Not captured';
            document.getElementById('activityDetailsDevice').textContent = log.deviceType || 'Unknown device';
            document.getElementById('activityDetailsBrowser').textContent = log.browser || 'Unknown browser';
            document.getElementById('activityDetailsSessionPanel').textContent = log.sessionId || 'Not captured';

            if (metadataList) {
                const metadataMarkup = getActivityMetadataMarkup(log.metadata);
                metadataList.innerHTML = metadataMarkup || '<div class="activity-modal-empty">No additional metadata was captured for this activity.</div>';
            }

            if (resourceLink) {
                if (log.resourceUrl) {
                    resourceLink.href = log.resourceUrl;
                    resourceLink.classList.remove('is-disabled');
                    resourceLink.setAttribute('aria-disabled', 'false');
                } else {
                    resourceLink.href = '#';
                    resourceLink.classList.add('is-disabled');
                    resourceLink.setAttribute('aria-disabled', 'true');
                }
            }

            overlay.classList.add('show');
        }

        function getActivityTypeConfig(type) {
            const typeMap = {
                'book-issued': { label: 'Book Issued', icon: 'fas fa-book-open' },
                'book-returned': { label: 'Book Returned', icon: 'fas fa-undo-alt' },
                'fine-applied': { label: 'Fine Applied', icon: 'fas fa-indian-rupee-sign' },
                'fine-paid': { label: 'Fine Paid', icon: 'fas fa-check-circle' },
                'fine-waived': { label: 'Fine Waived', icon: 'fas fa-ban' },
                'account-status': { label: 'Account Status', icon: 'fas fa-user-shield' },
                'profile-updated': { label: 'Profile Updated', icon: 'fas fa-user-edit' },
                'privilege-change': { label: 'Privilege Change', icon: 'fas fa-user-cog' },
                auth: { label: 'Authentication', icon: 'fas fa-shield-alt' },
            };

            return typeMap[type] || typeMap.auth;
        }

        function getActivityStatusConfig(status) {
            const normalizedStatus = String(status || 'success').toLowerCase();
            const statusMap = {
                success: { key: 'success', label: 'Success' },
                failed: { key: 'failed', label: 'Failed' },
                warning: { key: 'warning', label: 'Warning' },
            };

            return statusMap[normalizedStatus] || statusMap.success;
        }

        function getActivityRoleMeta(role) {
            const normalizedRole = String(role || 'system').toLowerCase();
            const allowedRoles = ['admin', 'staff', 'student'];

            return {
                roleClass: allowedRoles.includes(normalizedRole) ? normalizedRole : 'system',
                label: formatDisplayLabel(normalizedRole, 'System'),
            };
        }

        function getActivityAvatarMarkup(log) {
            if (log.userAvatar) {
                return `<span class="activity-avatar"><img src="${escapeHtml(log.userAvatar)}" alt="${escapeHtml(log.userName || 'User')}"></span>`;
            }

            return `<span class="activity-avatar">${escapeHtml(getActivityInitials(log.userName || 'System'))}</span>`;
        }

        function getActivityInitials(name) {
            const parts = String(name || 'System')
                .trim()
                .split(/\s+/)
                .filter(Boolean)
                .slice(0, 2);

            if (parts.length === 0) {
                return 'SY';
            }

            return parts.map(part => part.charAt(0).toUpperCase()).join('');
        }

        function getActivityMetadataMarkup(metadata) {
            const filteredEntries = Object.entries(metadata || {}).filter(([key]) => !['session_id'].includes(String(key)));
            if (filteredEntries.length === 0) {
                return '';
            }

            return filteredEntries.map(([key, value]) => `
                <div class="activity-metadata-item">
                    <span class="activity-metadata-key">${escapeHtml(formatDisplayLabel(key))}</span>
                    <div class="activity-metadata-value">${escapeHtml(formatActivityMetadataValue(value))}</div>
                </div>
            `).join('');
        }

        function formatActivityMetadataValue(value) {
            if (value === null || value === undefined || value === '') {
                return 'Not captured';
            }

            if (Array.isArray(value)) {
                return value.map(item => formatActivityMetadataValue(item)).join(', ');
            }

            if (typeof value === 'object') {
                return JSON.stringify(value, null, 2);
            }

            return String(value);
        }

        function getActivityResourceLabel(log) {
            const resourceType = log.resourceType || 'Resource';
            const resourceId = String(log.resourceId || '').trim();

            return resourceId && resourceId !== 'N/A'
                ? `${resourceType} #${resourceId}`
                : resourceType;
        }

        // Toast notification system
        function showToast(message, type = 'info', options = {}) {
            if (!feedbackUI) return;

            const titles = {
                success: 'Success',
                error: 'Action failed',
                warning: 'Please review',
                info: 'Notice',
            };

            feedbackUI.showToast({
                type,
                title: options.title || titles[type] || titles.info,
                message,
                detail: options.detail || '',
                timeout: options.timeout || 4200,
            });
        }

        function buildConfirmationDetail(details = []) {
            if (!Array.isArray(details) || details.length === 0) {
                return '';
            }

            return details
                .map(detail => `${detail.label}: ${detail.value}`)
                .join(' | ');
        }

        // Confirmation Modal Helper
        function showConfirmationModal(config) {
            const {
                title = 'Confirm Action',
                message = 'Are you sure you want to proceed with this action?',
                iconType = 'warning',
                confirmText = 'Confirm',
                confirmClass = 'primary',
                details = null,
                onConfirm = () => {}
            } = config;

            pendingConfirmationAction = onConfirm;

            if (!feedbackUI) {
                return;
            }

            feedbackUI.openConfirm({
                variant: iconType,
                buttonVariant: confirmClass,
                title,
                message,
                detail: buildConfirmationDetail(details),
                confirmText,
            });
        }

        function closeConfirmationModal() {
            pendingConfirmationAction = null;
            feedbackUI?.closeConfirm();
        }

        function handleConfirmationSubmit() {
            const callback = pendingConfirmationAction;
            pendingConfirmationAction = null;
            feedbackUI?.closeConfirm();

            if (typeof callback === 'function') {
                callback();
            }
        }

        function confirmAction() {
            handleConfirmationSubmit();
        }

        // Account Management Functions
        function resetPassword() {
            showConfirmationModal({
                title: 'Reset Student Password',
                message: 'A temporary password will be generated and sent to the student\'s email address. The student must change it on first login.',
                iconType: 'warning',
                confirmText: 'Reset Password',
                confirmClass: 'primary',
                details: [
                    { label: 'Action', value: 'Password Reset' },
                    { label: 'Impact', value: 'Student will receive new temporary password via email' }
                ],
                onConfirm: () => {
                    const resetBtn = document.getElementById('resetPasswordBtn');
                    resetBtn.disabled = true;
                    resetBtn.textContent = 'Resetting...';

                    fetch(`/admin/students/${studentId}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to reset password');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast('Temporary password has been sent to the student\'s email address.', 'success');
                            refreshStudentLiveSections({ refreshActivityLogs: true }).catch(error => {
                                console.error('Live refresh failed after password reset:', error);
                            });
                        } else {
                            showToast(data.message || 'Failed to reset password.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while resetting the password.', 'error');
                    })
                    .finally(() => {
                        resetBtn.disabled = false;
                        resetBtn.textContent = 'Reset Password';
                    });
                }
            });
        }

        function suspendAccount() {
            showConfirmationModal({
                title: 'Suspend Student Account',
                message: 'The student will not be able to access the system until the account is reactivated. All active sessions will be terminated.',
                iconType: 'warning',
                confirmText: 'Suspend Account',
                confirmClass: 'warning',
                details: [
                    { label: 'Action', value: 'Account Suspension' },
                    { label: 'Impact', value: 'Student will lose access to library services' },
                    { label: 'Reversible', value: 'Yes, can be reactivated later' }
                ],
                onConfirm: () => {
                    // Simulate API call
                    setTimeout(() => {
                        showToast('Account suspended successfully! The student has been notified.', 'warning');
                    }, 500);
                }
            });
        }

        function activateAccount() {
            showConfirmationModal({
                title: 'Activate Student Account',
                message: 'The student will be able to access the system immediately and use all library services.',
                iconType: 'info',
                confirmText: 'Activate Account',
                confirmClass: 'success',
                details: [
                    { label: 'Action', value: 'Account Activation' },
                    { label: 'Impact', value: 'Student will regain full access to library services' },
                    { label: 'Status', value: 'Account will be set to Active' }
                ],
                onConfirm: () => {
                    // Make API call to activate account
                    fetch(`/admin/students/${studentId}/activate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Account activated successfully! The student has been notified.', 'success');
                            studentStatus = 'active';
                            // Update the badge without page reload
                            updateStatusBadge();
                            // Update the button
                            updateAccountStatusButton();
                            refreshStudentLiveSections({ refreshActivityLogs: true }).catch(error => {
                                console.error('Live refresh failed after account activation:', error);
                            });
                        } else {
                            showToast(data.message || 'Failed to activate account.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while activating the account.', 'error');
                    });
                }
            });
        }

        function deactivateAccount() {
            showConfirmationModal({
                title: 'Deactivate Student Account',
                message: 'The student will not be able to access the system until the account is activated. This is a security measure.',
                iconType: 'warning',
                confirmText: 'Deactivate Account',
                confirmClass: 'warning',
                details: [
                    { label: 'Action', value: 'Account Deactivation' },
                    { label: 'Impact', value: 'Student will lose access to library services' },
                    { label: 'Reversible', value: 'Yes, can be reactivated later' }
                ],
                onConfirm: () => {
                    // Make API call to deactivate account
                    fetch(`/admin/students/${studentId}/deactivate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Account deactivated successfully! The student has been notified.', 'warning');
                            studentStatus = 'inactive';
                            // Update the badge without page reload
                            updateStatusBadge();
                            // Update the button
                            updateAccountStatusButton();
                            refreshStudentLiveSections({ refreshActivityLogs: true }).catch(error => {
                                console.error('Live refresh failed after account deactivation:', error);
                            });
                        } else {
                            showToast(data.message || 'Failed to deactivate account.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while deactivating the account.', 'error');
                    });
                }
            });
        }

        function updateStatusBadge() {
            const statusBadge = document.getElementById('studentStatusBadge');
            if (!statusBadge) return;

            // Update text
            statusBadge.textContent = studentStatus === 'inactive' ? 'Inactive' : 'Active';

            // Update classes
            statusBadge.classList.remove('badge-success', 'badge-danger');
            if (studentStatus === 'inactive') {
                statusBadge.classList.add('badge-danger');
            } else {
                statusBadge.classList.add('badge-success');
            }
        }

        function updateAccountStatusButton() {
            const accountActions = document.querySelector('.account-actions');
            if (!accountActions) return;

            // Remove existing activate/deactivate button
            const existingBtn = accountActions.querySelector('#activateAccountBtn, #deactivateAccountBtn');
            if (existingBtn) {
                existingBtn.remove();
            }

            // Find the reset password button
            const resetPasswordBtn = accountActions.querySelector('#resetPasswordBtn');
            if (!resetPasswordBtn) return;

            // Create new button based on current status
            const newButton = document.createElement('button');
            newButton.className = 'account-btn';
            
            if (studentStatus === 'inactive') {
                newButton.id = 'activateAccountBtn';
                newButton.className += ' btn-activate';
                newButton.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Activate Account
                `;
                newButton.onclick = activateAccount;
            } else {
                newButton.id = 'deactivateAccountBtn';
                newButton.className += ' btn-deactivate';
                newButton.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 9.2 1"/>
                    </svg>
                    Deactivate Account
                `;
                newButton.onclick = deactivateAccount;
            }

            // Insert after reset password button
            resetPasswordBtn.parentNode.insertBefore(newButton, resetPasswordBtn.nextSibling);
        }

        let selectedRole = null;

        function changeRole() {
            // Show the role selection modal
            const modal = document.getElementById('roleModalOverlay');
            modal?.classList.add('show');
            selectedRole = null;
            updateConfirmButton();
        }

        function submitRoleChange() {
            if (!selectedRole) return;
            const confirmBtn = document.getElementById('confirmRoleBtn');
            const formattedRole = selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1);

            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Changing...';

            fetch(`/admin/students/${studentId}/change-role`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ role: selectedRole })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to change role');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeRoleModal();
                    showToast(`Role changed to ${formattedRole} successfully!`, 'success', {
                        title: 'Role updated',
                        detail: `New role: ${formattedRole}`,
                    });
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Failed to change role.', 'error');
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = 'Change Role';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while changing the role.', 'error');
                confirmBtn.disabled = false;
                confirmBtn.textContent = 'Change Role';
            });
        }

        function selectRole(role) {
            // Remove active state from all role options
            document.querySelectorAll('.role-option').forEach(option => {
                option.style.borderColor = '';
                option.style.opacity = '1';
            });

            // Set the selected role
            selectedRole = role;

            // Highlight the selected option
            const selectedOption = document.querySelector(`.role-option[data-role="${role}"]`);
            if (selectedOption) {
                selectedOption.style.borderWidth = '2px';
            }

            updateConfirmButton();
        }

        function updateConfirmButton() {
            const confirmBtn = document.getElementById('confirmRoleBtn');
            if (confirmBtn) {
                confirmBtn.disabled = !selectedRole;
            }
        }

        function closeRoleModal() {
            const modal = document.getElementById('roleModalOverlay');
            modal?.classList.remove('show');
            selectedRole = null;
        }



        function deleteAccount() {
            showConfirmationModal({
                title: '⚠️ Delete Student Account',
                message: 'This action cannot be undone! All associated data including issued books, fines, and activity logs will be permanently removed.',
                iconType: 'danger',
                confirmText: 'Delete Permanently',
                confirmClass: 'danger',
                details: [
                    { label: 'Action', value: 'Permanent Account Deletion' },
                    { label: 'Impact', value: 'All student data will be permanently removed' },
                    { label: 'Reversible', value: 'NO - This action cannot be undone' },
                    { label: 'Data Affected', value: 'Profile, issued books, fines, activity logs' }
                ],
                onConfirm: () => {
                    const deleteBtn = document.getElementById('deleteAccountBtn');
                    deleteBtn.disabled = true;
                    deleteBtn.textContent = 'Deleting...';

                    showToast('Deleting account... This may take a few moments.', 'info');

                    fetch(`/admin/students/${studentId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to delete account');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast('Account deleted successfully! Redirecting to students list...', 'success');
                            setTimeout(() => {
                                window.location.href = '/admin/students';
                            }, 3000);
                        } else {
                            showToast(data.message || 'Failed to delete account.', 'error');
                            deleteBtn.disabled = false;
                            deleteBtn.textContent = 'Delete Account';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred while deleting the account.', 'error');
                        deleteBtn.disabled = false;
                        deleteBtn.textContent = 'Delete Account';
                    });
                }
            });
        }

        // Fine Management Functions
        function generateReceipt() {
            if (!fineReceiptExportWorkflow) {
                showToast('Fine receipt export is unavailable right now.', 'error');
                return;
            }

            fineReceiptExportWorkflow.open();
        }

        function handleFineAction(action, fineId) {
            const fine = finesData.find(f => f.id === fineId);
            if (!fine) {
                showToast('Fine data not found.', 'error');
                return;
            }

            switch(action) {
                case 'adjust':
                    adjustFine(fineId, fine);
                    break;
                case 'waive':
                    waiveFine(fineId, fine);
                    break;
                case 'mark-paid':
                    markFineAsPaid(fineId, fine);
                    break;
                case 'view-history':
                    viewFineHistory(fineId, fine);
                    break;
            }
        }

        let currentFineId = null;
        let currentStudentId = {{ $student->id }};

        function replaceArrayContents(targetArray, nextItems = []) {
            targetArray.splice(0, targetArray.length, ...nextItems);
        }

        function parseStudentOverdueDays(value) {
            if (value === null || value === undefined || value === '') {
                return null;
            }

            const numericValue = typeof value === 'string'
                ? Number.parseFloat(String(value).replace(/[^0-9.-]+/g, ''))
                : Number(value);

            return Number.isFinite(numericValue) ? numericValue : null;
        }

        function calculateOverdueDaysFromDate(dateValue) {
            if (!dateValue) {
                return null;
            }

            const parsedDate = new Date(dateValue);
            if (Number.isNaN(parsedDate.getTime())) {
                return null;
            }

            parsedDate.setHours(0, 0, 0, 0);

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            return Math.max(0, Math.floor((today.getTime() - parsedDate.getTime()) / 86400000));
        }

        function resolveStudentBookDaysOverdue(book = {}) {
            const numericDays = parseStudentOverdueDays(book.daysOverdue);
            const dueDateDays = calculateOverdueDaysFromDate(book.dueDateRaw || book.dueDate);

            if (dueDateDays !== null && (numericDays === null || numericDays < 0 || !Number.isInteger(numericDays))) {
                return dueDateDays;
            }

            if (numericDays !== null) {
                return Math.max(0, Math.floor(Math.abs(numericDays)));
            }

            return dueDateDays ?? 0;
        }

        function normalizeStudentBookRecord(book = {}) {
            const normalizedStatus = String(book.status || 'issued').toLowerCase();
            const fineAmount = Number(book.fine ?? 0);

            return {
                ...book,
                id: Number(book.id),
                status: normalizedStatus,
                fine: Number.isFinite(fineAmount) ? fineAmount : 0,
                daysOverdue: normalizedStatus === 'overdue'
                    ? resolveStudentBookDaysOverdue(book)
                    : 0,
            };
        }

        function normalizeStudentFineRecord(fine) {
            const normalizedStatus = String(fine.paymentStatus ?? fine.status ?? 'pending').toLowerCase();
            const statusKey = normalizedStatus === 'unpaid' ? 'pending' : normalizedStatus;
            const overdueValue = Number(fine.daysOverdue ?? fine.days_late ?? 0);

            return {
                id: Number(fine.id),
                bookName: fine.bookName ?? fine.bookTitle ?? 'Unknown',
                daysOverdue: typeof fine.daysOverdue === 'string'
                    ? fine.daysOverdue
                    : `${overdueValue} day${overdueValue === 1 ? '' : 's'}`,
                fineAmount: Number(fine.fineAmount ?? fine.amount ?? 0),
                paymentStatus: statusKey,
                actions: Array.isArray(fine.actions)
                    ? fine.actions
                    : (['paid', 'waived'].includes(statusKey)
                        ? ['view-history']
                        : ['adjust', 'waive', 'mark-paid', 'view-history']),
            };
        }

        function updatePendingFineSummary() {
            const pendingFineValue = document.getElementById('pendingFineValue');
            if (!pendingFineValue) {
                return;
            }

            const pendingTotal = finesData.reduce((total, fine) => {
                return String(fine.paymentStatus || '').toLowerCase() === 'pending'
                    ? total + Number(fine.fineAmount || 0)
                    : total;
            }, 0);

            pendingFineValue.textContent = `₹${pendingTotal.toFixed(2)}`;
        }

        function syncStudentFines(nextFines = []) {
            replaceArrayContents(finesData, nextFines.map(normalizeStudentFineRecord));
            fineReceiptExportWorkflow?.clearCache({ resetScope: false });
            renderFinesTable();
            updatePendingFineSummary();
        }

        function normalizeStudentActivityLog(log) {
            return {
                ...log,
                id: String(log.id ?? ''),
                type: String(log.type || 'auth'),
                status: String(log.status || 'success'),
                metadata: log.metadata && typeof log.metadata === 'object' ? log.metadata : {},
            };
        }

        function syncStudentActivityLogs(nextLogs = []) {
            const normalizedLogs = nextLogs.map(normalizeStudentActivityLog);
            replaceArrayContents(activityLogs, normalizedLogs.slice(0, 10));
            replaceArrayContents(remainingActivityLogs, normalizedLogs.slice(10));
            renderActivityLogs();
        }

        function loadStudentFines() {
            return fetch(`/admin/students/${currentStudentId}/fines`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => response.json().then(data => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || !data.success) {
                    throw new Error(data.message || 'Failed to load fines');
                }

                syncStudentFines(Array.isArray(data.fines) ? data.fines : []);
                return data;
            });
        }

        function loadStudentActivityLogs() {
            return fetch(`/admin/students/${currentStudentId}/activity-logs`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => response.json().then(data => ({ ok: response.ok, data })))
            .then(({ ok, data }) => {
                if (!ok || !data.success) {
                    throw new Error(data.message || 'Failed to load activity logs');
                }

                syncStudentActivityLogs(Array.isArray(data.activityLogs) ? data.activityLogs : []);
                return data;
            });
        }

        function refreshStudentLiveSections(options = {}) {
            const {
                refreshFines = false,
                refreshPrivileges = false,
                refreshActivityLogs = true,
            } = options;

            const tasks = [];

            if (refreshFines) {
                tasks.push(loadStudentFines());
            }

            if (refreshPrivileges) {
                tasks.push(loadPrivilegeSettings());
            }

            if (refreshActivityLogs) {
                tasks.push(loadStudentActivityLogs());
            }

            if (tasks.length === 0) {
                return Promise.resolve();
            }

            return Promise.allSettled(tasks).then(results => {
                const rejected = results.find(result => result.status === 'rejected');
                if (rejected) {
                    throw rejected.reason;
                }

                return results;
            });
        }

        function parseJsonResponse(response, fallbackMessage) {
            return response.json()
                .catch(() => ({}))
                .then(data => {
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || fallbackMessage);
                    }

                    return data;
                });
        }

        function normalizePrivilegeSettingsPayload(payload = {}) {
            return {
                max_books: payload.max_books ?? null,
                issue_duration_days: payload.issue_duration_days ?? null,
                per_day_fine: payload.per_day_fine ?? null,
                borrowing_allowed: payload.borrowing_allowed !== false,
                grace_period_days: payload.grace_period_days ?? null,
                max_fine_amount: payload.max_fine_amount ?? null,
            };
        }

        function applyPrivilegeSettingsState(data = {}) {
            const defaults = normalizePrivilegeSettingsPayload(data.defaults || {});
            const stored = normalizePrivilegeSettingsPayload(data.privileges || {});
            const effective = normalizePrivilegeSettingsPayload(data.effective || defaults);

            document.getElementById('maxBooks').value = effective.max_books ?? defaults.max_books ?? '';
            document.getElementById('maxDays').value = effective.issue_duration_days ?? defaults.issue_duration_days ?? '';
            document.getElementById('fineRate').value = effective.per_day_fine ?? defaults.per_day_fine ?? '';
            document.getElementById('borrowingPermission').value = effective.borrowing_allowed ? 'allowed' : 'restricted';

            window.originalPrivileges = stored;
            window.privilegeDefaults = defaults;
            window.effectivePrivileges = effective;
            window.hasCustomPrivilegeOverrides = Boolean(data.has_custom_overrides);
        }

        function setPrivilegeButtonsBusy(activeAction = null, isBusy = false) {
            const saveButton = document.getElementById('saveChangesBtn');
            const resetButton = document.getElementById('setDefaultPrivilegesBtn');
            const resetLabel = resetButton?.querySelector('[data-privilege-reset-label]');

            if (saveButton) {
                saveButton.disabled = isBusy;
                saveButton.textContent = isBusy && activeAction === 'save' ? 'Saving...' : 'Save Changes';
            }

            if (resetButton) {
                resetButton.disabled = isBusy;
            }

            if (resetLabel) {
                resetLabel.textContent = isBusy && activeAction === 'reset' ? 'Resetting...' : 'Set Default';
            }
        }

        function getPrivilegeFormValues() {
            return {
                max_books: parseInt(document.getElementById('maxBooks').value, 10),
                issue_duration_days: parseInt(document.getElementById('maxDays').value, 10),
                per_day_fine: parseFloat(document.getElementById('fineRate').value),
                borrowing_allowed: document.getElementById('borrowingPermission').value === 'allowed',
            };
        }

        function getPrivilegeDefaultDetails(defaults = window.privilegeDefaults || {}) {
            const normalizedDefaults = normalizePrivilegeSettingsPayload(defaults);

            return [
                { label: 'Max Books', value: `${normalizedDefaults.max_books ?? 5} books` },
                { label: 'Issue Duration', value: `${normalizedDefaults.issue_duration_days ?? 14} days` },
                { label: 'Fine Rate', value: `₹${normalizedDefaults.per_day_fine ?? 10} per day` },
                { label: 'Borrowing', value: normalizedDefaults.borrowing_allowed ? 'Allowed' : 'Restricted' }
            ];
        }

        // Load privilege settings when page loads
        function loadPrivilegeSettings() {
            return fetch(`/admin/students/${currentStudentId}/privileges`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => parseJsonResponse(response, 'Failed to load privileges'))
            .then(data => {
                applyPrivilegeSettingsState(data);
                return data;
            })
            .catch(error => {
                console.error('Error loading privileges:', error);
                throw error;
            });
        }

        function adjustFine(fineId, fine) {
            currentFineId = fineId;
            document.getElementById('adjustCurrentAmount').textContent = '₹' + parseFloat(fine.fineAmount).toFixed(2);
            document.getElementById('adjustNewAmount').value = '';
            document.getElementById('adjustFineSubmitBtn').disabled = false;
            document.getElementById('adjustFineSubmitBtn').textContent = 'Adjust Fine';
            document.getElementById('adjustFineOverlay').classList.add('show');
        }

        function submitAdjustFine() {
            const newAmount = document.getElementById('adjustNewAmount').value;
            const oldAmount = parseFloat(document.getElementById('adjustCurrentAmount').textContent.replace(/[₹,]/g, '')) || 0;
            
            if (!newAmount || isNaN(newAmount) || newAmount < 0) {
                showToast('Please enter a valid amount.', 'error');
                return;
            }

            const submitBtn = document.getElementById('adjustFineSubmitBtn');
            const nextAmount = parseFloat(newAmount);
            const delta = nextAmount - oldAmount;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Adjusting...';

            fetch(`/admin/fines/${currentFineId}/adjust`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify({ amount: newAmount, action: 'adjust' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeFineModal('adjust');
                    showToast(`Fine updated from ₹${oldAmount.toFixed(2)} to ₹${nextAmount.toFixed(2)}.`, 'success', {
                        title: 'Fine adjusted',
                        detail: `Change: ${delta >= 0 ? '+' : '-'}₹${Math.abs(delta).toFixed(2)}`,
                    });
                    refreshStudentLiveSections({
                        refreshFines: true,
                        refreshActivityLogs: true,
                    }).catch(error => {
                        console.error('Live refresh failed after fine adjustment:', error);
                        showToast('Fine updated, but the page could not refresh live. Please refresh manually if needed.', 'warning');
                    });
                } else {
                    showToast(data.message || 'Failed to adjust fine.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Adjust Fine';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                refreshStudentLiveSections({
                    refreshFines: true,
                    refreshActivityLogs: true,
                }).catch(() => {});
                showToast('Network issue - the fine may have been updated. Live refresh attempted.', 'warning');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Adjust Fine';
            });
        }



        function waiveFine(fineId, fine) {
            currentFineId = fineId;
            document.getElementById('waiveAmount').textContent = '₹' + parseFloat(fine.fineAmount).toFixed(2);
            document.getElementById('waiveReason').value = '';
            document.getElementById('waiveFineSubmitBtn').disabled = false;
            document.getElementById('waiveFineSubmitBtn').textContent = 'Waive Fine';
            document.getElementById('waiveFineOverlay').classList.add('show');
        }

        function submitWaiveFine() {
            const reason = document.getElementById('waiveReason').value.trim();
            const amount = parseFloat(document.getElementById('waiveAmount').textContent.replace(/[₹,]/g, '')) || 0;
            
            if (!reason) {
                showToast('Please enter a reason for waiving this fine.', 'error');
                return;
            }

            const submitBtn = document.getElementById('waiveFineSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Waiving...';

            fetch(`/admin/fines/${currentFineId}/waive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeFineModal('waive');
                    showToast('The fine was waived successfully.', 'warning', {
                        title: 'Fine waived',
                        detail: `Amount: ₹${amount.toFixed(2)} | Reason: ${reason}`,
                    });
                    refreshStudentLiveSections({
                        refreshFines: true,
                        refreshActivityLogs: true,
                    }).catch(error => {
                        console.error('Live refresh failed after fine waiver:', error);
                        showToast('Fine waived, but the page could not refresh live. Please refresh manually if needed.', 'warning');
                    });
                } else {
                    showToast(data.message || 'Failed to waive fine.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Waive Fine';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                refreshStudentLiveSections({
                    refreshFines: true,
                    refreshActivityLogs: true,
                }).catch(() => {});
                showToast('Network issue - the fine may have been waived. Live refresh attempted.', 'warning');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Waive Fine';
            });
        }



        function markFineAsPaid(fineId, fine) {
            currentFineId = fineId;
            const amount = Number(fine.fineAmount || 0);

            showConfirmationModal({
                title: 'Mark Fine as Paid?',
                message: 'Record this student payment now?',
                iconType: 'info',
                confirmText: 'Mark as Paid',
                confirmClass: 'success',
                details: [
                    { label: 'Book', value: fine.bookName || 'Unknown' },
                    { label: 'Amount', value: `₹${amount.toFixed(2)}` },
                    { label: 'Impact', value: 'Fine status will move to paid immediately' }
                ],
                onConfirm: () => {
                    fetch(`/admin/fines/${currentFineId}/mark-as-paid`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin',
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('The payment was recorded successfully.', 'success', {
                                title: 'Fine marked as paid',
                                detail: `Amount: ₹${amount.toFixed(2)}`,
                            });
                            refreshStudentLiveSections({
                                refreshFines: true,
                                refreshActivityLogs: true,
                            }).catch(error => {
                                console.error('Live refresh failed after marking fine as paid:', error);
                                showToast('Fine marked as paid, but the page could not refresh live. Please refresh manually if needed.', 'warning');
                            });
                        } else {
                            showToast(data.message || 'Failed to mark fine as paid.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        refreshStudentLiveSections({
                            refreshFines: true,
                            refreshActivityLogs: true,
                        }).catch(() => {});
                        showToast('Network issue - the fine may have been marked as paid. Live refresh attempted.', 'warning');
                    });
                }
            });
        }



        function viewFineHistory(fineId, fine) {
            currentFineId = fineId;
            document.getElementById('historyAmount').textContent = formatCurrency(fine.fineAmount);
            document.getElementById('historyOriginalAmount').textContent = 'Loading...';
            document.getElementById('historyStatus').innerHTML = renderHistoryStatusBadge(fine.paymentStatus || 'pending');
            document.getElementById('historyDaysLate').textContent = fine.daysOverdue || '0 days';
            document.getElementById('historyBookTitle').textContent = fine.bookName || 'Loading book details...';
            document.getElementById('historyBookIsbn').textContent = 'ISBN: Loading...';
            document.getElementById('historyCalculationSection').hidden = true;
            document.getElementById('fineHistoryList').innerHTML = '<li class="fine-history-empty">Loading history...</li>';
            document.getElementById('historyOverlay').classList.add('show');

            fetch(`/admin/fines/${fineId}/history`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
                ,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load fine history');
                }

                return response.json();
            })
            .then(data => {
                const historyList = document.getElementById('fineHistoryList');
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load fine history');
                }

                const fineDetails = data.fineDetails || {};
                const calculation = data.calculation || null;
                const history = Array.isArray(data.history) ? data.history : [];

                document.getElementById('historyAmount').textContent = formatCurrency(fineDetails.currentAmount ?? fine.fineAmount);
                document.getElementById('historyOriginalAmount').textContent = formatCurrency(fineDetails.originalAmount ?? fine.fineAmount);
                document.getElementById('historyStatus').innerHTML = renderHistoryStatusBadge(fineDetails.status || fine.paymentStatus || 'pending');
                document.getElementById('historyDaysLate').textContent = `${fineDetails.daysLate ?? 0} day${Number(fineDetails.daysLate ?? 0) === 1 ? '' : 's'}`;
                document.getElementById('historyBookTitle').textContent = fineDetails.bookTitle || fine.bookName || 'Unknown Book';
                document.getElementById('historyBookIsbn').textContent = `ISBN: ${fineDetails.isbn || 'N/A'}`;

                const calculationSection = document.getElementById('historyCalculationSection');
                const adjustmentsRow = document.getElementById('historyCalcAdjustmentsRow');
                if (calculation) {
                    calculationSection.hidden = false;
                    document.getElementById('historyCalcBaseRate').textContent = formatCurrency(calculation.baseRate);
                    document.getElementById('historyCalcDaysLate').textContent = `${calculation.daysLate ?? 0}`;
                    document.getElementById('historyCalcSubtotal').textContent = formatCurrency(calculation.subtotal);

                    const adjustmentValue = Number(calculation.adjustments ?? 0);
                    adjustmentsRow.hidden = adjustmentValue === 0;
                    document.getElementById('historyCalcAdjustments').textContent = formatSignedCurrency(adjustmentValue);
                    document.getElementById('historyCalcFinalAmount').textContent = formatCurrency(calculation.finalAmount);
                } else {
                    calculationSection.hidden = true;
                }

                if (history.length > 0) {
                    historyList.innerHTML = history.map(renderFineHistoryItem).join('');
                } else {
                    historyList.innerHTML = '<li class="fine-history-empty">No history available for this fine yet.</li>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('fineHistoryList').innerHTML = '<li class="fine-history-empty">Unable to load history. Please try again.</li>';
            });
        }

        function formatCurrency(value) {
            const numericValue = Number(value);
            return `₹${Number.isFinite(numericValue) ? numericValue.toFixed(2) : '0.00'}`;
        }

        function formatSignedCurrency(value) {
            const numericValue = Number(value);
            if (!Number.isFinite(numericValue) || numericValue === 0) {
                return formatCurrency(0);
            }

            return `${numericValue > 0 ? '+' : '-'}${formatCurrency(Math.abs(numericValue))}`;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function renderHistoryStatusBadge(status) {
            const normalizedStatus = String(status || 'pending').toLowerCase();
            return `<span class="history-status-badge status-${escapeHtml(normalizedStatus)}">${escapeHtml(formatDisplayLabel(normalizedStatus, 'Pending'))}</span>`;
        }

        function getFineHistoryActionConfig(actionType) {
            const normalizedType = String(actionType || 'created').toLowerCase();
            const actionMap = {
                created: { label: 'Created', icon: 'fa-plus' },
                adjusted: { label: 'Adjusted', icon: 'fa-sliders-h' },
                paid: { label: 'Paid', icon: 'fa-check' },
                waived: { label: 'Waived', icon: 'fa-ban' },
            };

            return {
                type: actionMap[normalizedType] ? normalizedType : 'created',
                ...(actionMap[normalizedType] || actionMap.created),
            };
        }

        function getPaymentMethodMeta(paymentMethod) {
            const normalizedMethod = String(paymentMethod || '').toLowerCase();
            const methodMap = {
                cash: { icon: 'fa-indian-rupee-sign', label: 'Cash' },
                card: { icon: 'fa-credit-card', label: 'Card' },
                online: { icon: 'fa-globe', label: 'Online' },
            };

            return methodMap[normalizedMethod] || null;
        }

        function getRoleMeta(userRole) {
            const normalizedRole = String(userRole || 'system').toLowerCase();
            const roleMap = {
                admin: { icon: 'fa-user-shield', label: 'Admin' },
                staff: { icon: 'fa-user-tie', label: 'Staff' },
                student: { icon: 'fa-user-graduate', label: 'Student' },
                system: { icon: 'fa-microchip', label: 'System' },
                user: { icon: 'fa-user', label: 'User' },
            };

            return {
                roleClass: roleMap[normalizedRole] ? normalizedRole : 'user',
                ...(roleMap[normalizedRole] || roleMap.user),
            };
        }

        function renderFineHistoryItem(item) {
            const actionConfig = getFineHistoryActionConfig(item.actionType);
            const roleMeta = getRoleMeta(item.userRole);
            const amountDelta = item.amountChange !== null
                ? `<span>${escapeHtml(formatSignedCurrency(item.amountChange))}</span>`
                : '';
            const amountChangeMarkup = item.actionType === 'adjusted' && item.oldAmount !== null && item.newAmount !== null
                ? `<div class="fine-history-amount-change">${formatCurrency(item.oldAmount)} &rarr; ${formatCurrency(item.newAmount)} ${amountDelta}</div>`
                : '';
            const paymentMethod = getPaymentMethodMeta(item.paymentMethod);
            const paymentMethodMarkup = paymentMethod
                ? `<span class="fine-history-meta-item"><i class="fas ${paymentMethod.icon}"></i>${escapeHtml(paymentMethod.label)}</span>`
                : '';
            const remarksMarkup = item.remarks
                ? `<div class="fine-history-remarks">"${escapeHtml(item.remarks)}"</div>`
                : '';

            return `
                <li class="fine-history-item">
                    <div class="fine-history-dot ${actionConfig.type}">
                        <i class="fas ${actionConfig.icon}"></i>
                    </div>
                    <div class="fine-history-entry">
                        <div class="fine-history-top">
                            <span class="history-action-badge ${actionConfig.type}">
                                <i class="fas ${actionConfig.icon}"></i>
                                ${escapeHtml(item.action || actionConfig.label)}
                            </span>
                            <span class="fine-history-date">${escapeHtml(item.date || 'N/A')}</span>
                        </div>
                        <p class="fine-history-action">${escapeHtml(item.action || actionConfig.label)}</p>
                        <p class="fine-history-description">${escapeHtml(item.description || item.action || actionConfig.label)}</p>
                        ${amountChangeMarkup}
                        <div class="fine-history-meta">
                            <span class="fine-history-meta-item">
                                <span class="fine-history-actor">
                                    <span class="fine-history-actor-name">${escapeHtml(item.user || 'System')}</span>
                                    <span class="fine-history-role-badge role-${escapeHtml(roleMeta.roleClass)}">
                                        <i class="fas ${roleMeta.icon}"></i>
                                        ${escapeHtml(roleMeta.label)}
                                    </span>
                                </span>
                            </span>
                            ${paymentMethodMarkup}
                        </div>
                        ${remarksMarkup}
                    </div>
                </li>
            `;
        }

        function closeFineModal(type) {
            if (type === 'adjust') document.getElementById('adjustFineOverlay').classList.remove('show');
            if (type === 'waive') document.getElementById('waiveFineOverlay').classList.remove('show');
            if (type === 'history') document.getElementById('historyOverlay').classList.remove('show');
            currentFineId = null;
        }

        // Close modals on overlay click
        ['adjustFineOverlay', 'waiveFineOverlay', 'historyOverlay'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                    currentFineId = null;
                }
            });
        });

        // Privilege Settings Functions
        function savePrivilegeSettings() {
            const privilegeValues = getPrivilegeFormValues();

            // Validate inputs
            if (!privilegeValues.max_books || Number.isNaN(privilegeValues.max_books) || privilegeValues.max_books < 1 || privilegeValues.max_books > 20) {
                showToast('Maximum books must be between 1 and 20', 'error');
                return;
            }

            if (!privilegeValues.issue_duration_days || Number.isNaN(privilegeValues.issue_duration_days) || privilegeValues.issue_duration_days < 1 || privilegeValues.issue_duration_days > 90) {
                showToast('Maximum issue duration must be between 1 and 90 days', 'error');
                return;
            }

            if (Number.isNaN(privilegeValues.per_day_fine) || privilegeValues.per_day_fine < 0 || privilegeValues.per_day_fine > 100) {
                showToast('Fine rate must be between ₹0 and ₹100 per day', 'error');
                return;
            }

            showConfirmationModal({
                title: 'Save Library Privilege Settings',
                message: 'These settings will override the global defaults for this student. Changes will take effect immediately.',
                iconType: 'warning',
                confirmText: 'Save Changes',
                confirmClass: 'primary',
                details: [
                    { label: 'Max Books', value: `${privilegeValues.max_books} books` },
                    { label: 'Issue Duration', value: `${privilegeValues.issue_duration_days} days` },
                    { label: 'Fine Rate', value: `₹${privilegeValues.per_day_fine} per day` },
                    { label: 'Borrowing', value: privilegeValues.borrowing_allowed ? 'Allowed' : 'Restricted' }
                ],
                onConfirm: () => {
                    setPrivilegeButtonsBusy('save', true);
                    showToast('Saving privilege settings...', 'info', {
                        title: 'Updating library settings',
                    });

                    fetch(`/admin/students/${currentStudentId}/privileges`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            max_books: privilegeValues.max_books,
                            issue_duration_days: privilegeValues.issue_duration_days,
                            per_day_fine: privilegeValues.per_day_fine,
                            borrowing_allowed: privilegeValues.borrowing_allowed
                        })
                    })
                    .then(response => parseJsonResponse(response, 'Failed to save privilege settings.'))
                    .then(data => {
                        applyPrivilegeSettingsState(data);
                        showToast('Privilege settings saved successfully.', 'success', {
                            title: 'Library settings updated',
                            detail: 'Changes take effect immediately for this student.',
                        });

                        refreshStudentLiveSections({
                            refreshActivityLogs: true,
                        }).catch(error => {
                            console.error('Live refresh failed after saving privilege settings:', error);
                            showToast('Privileges saved, but activity logs could not refresh live. Please refresh manually if needed.', 'warning');
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (error instanceof TypeError) {
                            refreshStudentLiveSections({
                                refreshPrivileges: true,
                                refreshActivityLogs: true,
                            }).catch(() => {});
                            showToast('Network issue - privileges may have been saved. Live refresh attempted.', 'warning');
                            return;
                        }

                        showToast(error.message || 'Failed to save privilege settings.', 'error');
                    })
                    .finally(() => {
                        setPrivilegeButtonsBusy(null, false);
                    });
                }
            });
        }

        function resetPrivilegeSettings() {
            showConfirmationModal({
                title: 'Reset to Default Settings',
                message: 'Are you sure you want to reset to default settings? All custom library privilege overrides for this student will be removed immediately.',
                iconType: 'warning',
                confirmText: 'Reset',
                confirmClass: 'warning',
                details: getPrivilegeDefaultDetails(),
                onConfirm: () => {
                    setPrivilegeButtonsBusy('reset', true);
                    showToast('Resetting privilege settings to default values...', 'info', {
                        title: 'Restoring defaults',
                    });

                    fetch(`/admin/students/${currentStudentId}/privileges/reset`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => parseJsonResponse(response, 'Failed to reset privilege settings.'))
                    .then(data => {
                        applyPrivilegeSettingsState(data);
                        showToast('Library privileges have been reset to the default values.', 'success', {
                            title: 'Defaults restored',
                            detail: 'Custom overrides were cleared immediately.',
                        });

                        refreshStudentLiveSections({
                            refreshActivityLogs: true,
                        }).catch(error => {
                            console.error('Live refresh failed after resetting privilege settings:', error);
                            showToast('Defaults were restored, but activity logs could not refresh live. Please refresh manually if needed.', 'warning');
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (error instanceof TypeError) {
                            refreshStudentLiveSections({
                                refreshPrivileges: true,
                                refreshActivityLogs: true,
                            }).catch(() => {});
                            showToast('Network issue - privileges may have been reset. Live refresh attempted.', 'warning');
                            return;
                        }

                        showToast(error.message || 'Failed to reset privilege settings.', 'error');
                    })
                    .finally(() => {
                        setPrivilegeButtonsBusy(null, false);
                    });
                }
            });
        }
    </script>
@endpush
