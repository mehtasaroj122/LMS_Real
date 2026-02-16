@extends('Admin.layouts.app')

@section('title', 'Student Details')

@push('styles')
    <style>
        /* Student Details Page */
        .student-details-page {
            padding: 16px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* Header Row */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 12px 0;
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
            gap: 16px;
        }

        .back-link {
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 6px 12px;
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
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .header-right {
            display: flex;
            gap: 12px;
        }

        /* Buttons */
        .btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
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
            gap: 20px;
        }

        /* Profile Column */
        .profile-card {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 16px;
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
            margin-bottom: 16px;
        }

        .student-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
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
            margin-bottom: 20px;
        }

        .student-name {
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        .student-id {
            font-size: 14px;
            margin: 0 0 12px 0;
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
            gap: 8px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
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
            gap: 12px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
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
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid;
        }

        body.light-theme .system-info {
            border-color: #e5e7eb;
        }

        body.dark-theme .system-info {
            border-color: #334155;
        }

        .system-info h4 {
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 12px 0;
        }

        body.light-theme .system-info h4 {
            color: #64748b;
        }

        body.dark-theme .system-info h4 {
            color: #94a3b8;
        }

        .info-grid {
            display: grid;
            gap: 8px;
        }

        .info-grid .info-item {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 8px;
        }

        .info-label {
            font-size: 12px;
            font-weight: 500;
        }

        body.light-theme .info-label {
            color: #94a3b8;
        }

        body.dark-theme .info-label {
            color: #64748b;
        }

        .info-value {
            font-size: 13px;
            font-weight: 500;
        }

        /* Content Column */
        .content-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Summary Cards Grid */
        .summary-cards-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
        }

        .summary-card {
            border-radius: 8px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
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
            width: 40px;
            height: 40px;
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
            font-size: 12px;
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
            font-size: 20px;
            font-weight: 700;
        }

        /* Issued Books Section */
        .issued-books-section {
            border-radius: 8px;
            padding: 16px;
            border: 1px solid;
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
            margin-bottom: 16px;
        }

        .section-header h3 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .section-controls {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-container {
            position: relative;
            width: 200px;
        }

        .search-container svg {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
        }

        body.light-theme .search-container svg {
            color: #94a3b8;
        }

        body.dark-theme .search-container svg {
            color: #64748b;
        }

        #bookSearch {
            width: 100%;
            padding: 8px 10px 8px 32px;
            border-radius: 6px;
            font-size: 14px;
            border: 1px solid;
        }

        body.light-theme #bookSearch {
            background-color: #ffffff;
            border-color: #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme #bookSearch {
            background-color: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        body.light-theme #bookSearch::placeholder {
            color: #94a3b8;
        }

        body.dark-theme #bookSearch::placeholder {
            color: #64748b;
        }

        #bookSearch:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .status-filter {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            border: 1px solid;
            background-color: transparent;
        }

        body.light-theme .status-filter {
            color: #0f172a;
            border-color: #e5e7eb;
            background-color: #ffffff;
        }

        body.dark-theme .status-filter {
            color: #e2e8f0;
            border-color: #334155;
            background-color: #0f172a;
        }

        /* Books Table */
        .table-container {
            overflow-x: auto;
        }

        .books-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        .books-table th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            border-bottom: 1px solid;
        }

        body.light-theme .books-table th {
            color: #64748b;
            border-color: #e5e7eb;
            background-color: #f8fafc;
        }

        body.dark-theme .books-table th {
            color: #94a3b8;
            border-color: #334155;
            background-color: #0f172a;
        }

        .books-table td {
            padding: 16px;
            border-bottom: 1px solid;
        }

        body.light-theme .books-table td {
            border-color: #f1f5f9;
        }

        body.dark-theme .books-table td {
            border-color: #1e293b;
        }

        .books-table tbody tr:hover {
            transition: background-color 0.2s ease;
        }

        body.light-theme .books-table tbody tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .books-table tbody tr:hover {
            background-color: #0f172a;
        }

        /* Status Badges in Table */
        .table-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            display: inline-block;
        }

        .table-badge.issued {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .table-badge.issued {
            background-color: #14532d;
            color: #4ade80;
        }

        .table-badge.overdue {
            background-color: #fef2f2;
            color: #dc2626;
        }

        body.dark-theme .table-badge.overdue {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        .table-badge.returned {
            background-color: #f8fafc;
            color: #64748b;
        }

        body.dark-theme .table-badge.returned {
            background-color: #334155;
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

        /* Action Button */
        .view-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid;
            background: none;
            font-size: 12px;
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
                padding: 12px;
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

            .section-controls {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .search-container {
                width: 100%;
            }

            .status-filter {
                width: 100%;
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
            margin-top: 24px;
        }

        /* Main Grid for Part 2 */
        .main-grid-part2 {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        /* Account Management Card */
        .account-management-card {
            border-radius: 8px;
            padding: 16px;
            border: 1px solid;
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
            gap: 10px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid;
        }

        body.light-theme .account-management-card .card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .account-management-card .card-header {
            border-color: #334155;
        }

        .header-icon {
            width: 36px;
            height: 36px;
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
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        /* Account Action Buttons */
        .account-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .account-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
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
            padding: 16px;
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
            max-width: 600px;
            width: 90%;
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
            margin-bottom: 16px;
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
        .fine-history-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .fine-history-item {
            display: flex;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid;
            align-items: flex-start;
        }

        body.light-theme .fine-history-item {
            border-color: #e2e8f0;
        }

        body.dark-theme .fine-history-item {
            border-color: #334155;
        }

        .fine-history-item:last-child {
            border-bottom: none;
        }

        .fine-history-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #3b82f6;
            margin-top: 6px;
            flex-shrink: 0;
        }

        .fine-history-content h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
        }

        .fine-history-content p {
            margin: 0;
            font-size: 13px;
        }

        body.light-theme .fine-history-content p {
            color: #64748b;
        }

        body.dark-theme .fine-history-content p {
            color: #94a3b8;
        }

        .book-details-body {
            padding: 24px;
        }

        .book-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 8px 0;
        }

        .book-author {
            font-size: 16px;
            font-weight: 500;
            margin: 0 0 16px 0;
        }

        body.light-theme .book-author {
            color: #666;
        }

        body.dark-theme .book-author {
            color: #94a3b8;
        }

        .book-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .book-detail-item {
            border-radius: 8px;
            padding: 12px;
        }

        body.light-theme .book-detail-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .book-detail-item {
            background-color: #0f172a;
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
            margin-top: 4px;
        }

        .book-detail-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .book-detail-badge.status-issued {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .book-detail-badge.status-issued {
            background-color: #14532d;
            color: #4ade80;
        }

        .book-detail-badge.status-overdue {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .book-detail-badge.status-overdue {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        .book-detail-badge.status-returned {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.dark-theme .book-detail-badge.status-returned {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .book-description {
            margin-bottom: 16px;
            padding: 12px;
            border-radius: 8px;
        }

        body.light-theme .book-description {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        body.dark-theme .book-description {
            background-color: #0f172a;
            border: 1px solid #334155;
        }

        .book-description-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        body.light-theme .book-description-title {
            color: #64748b;
        }

        body.dark-theme .book-description-title {
            color: #94a3b8;
        }

        .book-description-text {
            font-size: 14px;
            line-height: 1.5;
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
        }

        .fines-management-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .generate-receipt-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background-color: #10b981;
            color: white;
            transition: background-color 0.2s ease;
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
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            border-bottom: 1px solid;
        }

        body.light-theme .fines-table th {
            color: #64748b;
            border-color: #e5e7eb;
            background-color: #f8fafc;
        }

        body.dark-theme .fines-table th {
            color: #94a3b8;
            border-color: #334155;
            background-color: #0f172a;
        }

        .fines-table td {
            padding: 16px;
            border-bottom: 1px solid;
        }

        body.light-theme .fines-table td {
            border-color: #f1f5f9;
        }

        body.dark-theme .fines-table td {
            border-color: #1e293b;
        }

        .fines-table tbody tr:hover {
            transition: background-color 0.2s ease;
        }

        body.light-theme .fines-table tbody tr:hover {
            background-color: #f8fafc;
        }

        body.dark-theme .fines-table tbody tr:hover {
            background-color: #0f172a;
        }

        /* Payment Status Badges */
        .payment-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            display: inline-block;
        }

        .payment-badge.unpaid {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .payment-badge.unpaid {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        .payment-badge.paid {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .payment-badge.paid {
            background-color: #14532d;
            color: #4ade80;
        }

        /* Fine Actions */
        .fine-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .action-btn-small {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid;
            transition: all 0.2s ease;
            white-space: nowrap;
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
            opacity: 0.9;
        }

        body.dark-theme .action-btn-small.adjust {
            background-color: #1e3a8a;
            color: #60a5fa;
            border-color: #1e3a8a;
        }

        body.dark-theme .action-btn-small.waive {
            background-color: #14532d;
            color: #4ade80;
            border-color: #14532d;
        }

        body.dark-theme .action-btn-small.mark-paid {
            background-color: #5b21b6;
            color: #c4b5fd;
            border-color: #5b21b6;
        }

        body.dark-theme .action-btn-small.view-history {
            background-color: #334155;
            color: #94a3b8;
            border-color: #334155;
        }

        body.dark-theme .action-btn-small:hover {
            opacity: 0.9;
        }

        /* Bottom Row */
        .bottom-row-part2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Privilege Settings Card */
        .privilege-settings-card {
            border-radius: 8px;
            padding: 16px;
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
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid;
        }

        body.light-theme .privilege-settings-card .card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .privilege-settings-card .card-header {
            border-color: #334155;
        }

        .privilege-settings-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        /* Settings Form */
        .settings-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 14px;
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
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 14px;
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
            margin-top: 8px;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background-color: #3b82f6;
            color: white;
            transition: background-color 0.2s ease;
            width: 100%;
        }

        .save-changes-btn:hover {
            background-color: #2563eb;
        }

        /* Activity Logs Card */
        .activity-logs-card {
            border-radius: 8px;
            padding: 16px;
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
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid;
        }

        body.light-theme .activity-logs-card .card-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .activity-logs-card .card-header {
            border-color: #334155;
        }

        .activity-logs-card h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        /* Activity Timeline */
        .activity-timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .activity-timeline::-webkit-scrollbar {
            width: 6px;
        }

        .activity-timeline::-webkit-scrollbar-track {
            background: transparent;
        }

        .activity-timeline::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
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
            display: flex;
            gap: 12px;
            position: relative;
            padding-left: 12px;
        }

        .activity-item:before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .activity-item.book-issued:before {
            background-color: #3b82f6;
        }

        .activity-item.book-returned:before {
            background-color: #10b981;
        }

        .activity-item.fine-applied:before {
            background-color: #ef4444;
        }

        .activity-item.account-status:before {
            background-color: #f59e0b;
        }

        .activity-item.profile-updated:before {
            background-color: #8b5cf6;
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-item.book-issued .activity-icon {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .activity-item.book-returned .activity-icon {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .activity-item.fine-applied .activity-icon {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .activity-item.account-status .activity-icon {
            background-color: #fef3c7;
            color: #d97706;
        }

        .activity-item.profile-updated .activity-icon {
            background-color: #f3e8ff;
            color: #7c3aed;
        }

        body.dark-theme .activity-item.book-issued .activity-icon {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        body.dark-theme .activity-item.book-returned .activity-icon {
            background-color: #14532d;
            color: #4ade80;
        }

        body.dark-theme .activity-item.fine-applied .activity-icon {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        body.dark-theme .activity-item.account-status .activity-icon {
            background-color: #78350f;
            color: #fbbf24;
        }

        body.dark-theme .activity-item.profile-updated .activity-icon {
            background-color: #5b21b6;
            color: #c4b5fd;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .activity-desc {
            font-size: 13px;
            margin-bottom: 4px;
        }

        body.light-theme .activity-desc {
            color: #6b7280;
        }

        body.dark-theme .activity-desc {
            color: #9ca3af;
        }

        .activity-time {
            font-size: 12px;
        }

        body.light-theme .activity-time {
            color: #9ca3af;
        }

        body.dark-theme .activity-time {
            color: #6b7280;
        }

        /* Show More Button */
        .show-more-btn {
            margin-top: 12px;
            padding: 10px 16px;
            border: 1px solid;
            background-color: transparent;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            align-self: flex-start;
        }

        body.light-theme .show-more-btn {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        body.light-theme .show-more-btn:hover {
            background-color: #eff6ff;
        }

        body.dark-theme .show-more-btn {
            border-color: #60a5fa;
            color: #60a5fa;
        }

        body.dark-theme .show-more-btn:hover {
            background-color: #0f172a;
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
            }

            .fine-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            .action-btn-small {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .account-btn,
            .generate-receipt-btn,
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
                <button class="btn btn-primary" id="sendNotificationBtn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    Send Notification
                </button>
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
                <div class="profile-card">
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
            <div class="content-column">
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
                                <line x1="12" y1="1" x2="12" y2="23"/>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
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
                <div class="issued-books-section">
                    <div class="section-header">
                        <h3>Issued Books</h3>
                        <div class="section-controls">
                            <div class="search-container">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                                <input type="text" id="bookSearch" placeholder="Search books...">
                            </div>
                            <select id="statusFilter" class="status-filter">
                                <option value="all">All Status</option>
                                <option value="issued">Issued</option>
                                <option value="overdue">Overdue</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>
                    </div>

                    <!-- Books Table -->
                    <div class="table-container">
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

                    <div class="table-container">
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
                    <button class="save-changes-btn" id="saveChangesBtn">
                        Save Changes
                    </button>
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

    <!-- Mark as Paid Modal -->
    <div class="fine-modal-overlay" id="markPaidOverlay">
        <div class="fine-modal">
            <div class="fine-modal-header">
                <h2>Mark Fine as Paid</h2>
                <button class="fine-modal-close" onclick="closeFineModal('paid')">&times;</button>
            </div>
            <div class="fine-modal-body">
                <div class="fine-info-box">
                    <div class="fine-info-label">Fine Amount</div>
                    <div class="fine-info-value" id="paidAmount">₹0</div>
                </div>
                <p style="margin: 0; padding: 12px; background-color: #f0fdf4; border-radius: 6px; border-left: 4px solid #10b981; color: #065f46; font-size: 14px;">
                    ⓘ Please ensure payment has been received before marking this fine as paid.
                </p>
            </div>
            <div class="fine-modal-actions">
                <button class="fine-modal-btn fine-modal-btn-cancel" onclick="closeFineModal('paid')">Cancel</button>
                <button class="fine-modal-btn fine-modal-btn-action" id="markPaidSubmitBtn" onclick="submitMarkAsPaid()" style="background-color: #10b981; border-color: #10b981;">Mark as Paid</button>
            </div>
        </div>
    </div>

    <!-- Fine History Modal -->
    <div class="fine-modal-overlay" id="historyOverlay">
        <div class="fine-modal">
            <div class="fine-modal-header">
                <h2>Fine Payment History</h2>
                <button class="fine-modal-close" onclick="closeFineModal('history')">&times;</button>
            </div>
            <div class="fine-modal-body">
                <div class="fine-info-box">
                    <div class="fine-info-label">Fine Amount</div>
                    <div class="fine-info-value" id="historyAmount">₹0</div>
                </div>
                <ul class="fine-history-list" id="fineHistoryList">
                    <li class="fine-history-item" style="text-align: center; padding: 20px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                        Loading history...
                    </li>
                </ul>
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
                <h2 class="book-title" id="bookDetailsTitle"></h2>
                <p class="book-author" id="bookDetailsAuthor"></p>
                
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
                        <div class="book-detail-label">Status</div>
                        <div class="book-detail-value" id="bookDetailsStatus"></div>
                    </div>
                    <div class="book-detail-item">
                        <div class="book-detail-label">Condition</div>
                        <div class="book-detail-value" id="bookDetailsCondition"></div>
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
                        <div class="book-detail-label">Fine Amount</div>
                        <div class="book-detail-value" id="bookDetailsFine"></div>
                    </div>
                </div>

                <div id="overdueInfo" style="display: none;" class="book-detail-item">
                    <div class="book-detail-label">Days Overdue</div>
                    <div class="book-detail-value" id="bookDetailsDaysOverdue"></div>
                </div>

                <div class="book-description">
                    <div class="book-description-title">Description</div>
                    <div class="book-description-text" id="bookDetailsDescription"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="toast-container"></div>
@endsection



@push('scripts')
    <script>
        // Data provided by server (transformed in controller)
        const booksData = @json($booksData ?? []);
        const finesData = @json($finesData ?? []);
        const activityLogs = @json($activityLogs ?? []);
        const remainingActivityLogs = @json($remainingActivityLogs ?? []);
        const studentId = @json($student->id);
        let studentStatus = '@json($student->user->status ?? 'active')';

        // DOM Elements
        const bookSearchInput = document.getElementById('bookSearch');
        const statusFilterSelect = document.getElementById('statusFilter');
        const booksTableBody = document.getElementById('booksTableBody');
        const sendNotificationBtn = document.getElementById('sendNotificationBtn');
        const printReportBtn = document.getElementById('printReportBtn');

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderBooksTable();
            setupEventListeners();
            setupKeyboardNavigation();
        });

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

            // Button actions
            if (sendNotificationBtn) {
                sendNotificationBtn.addEventListener('click', sendNotification);
            }

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

                // Escape to clear search
                if (e.key === 'Escape' && document.activeElement === bookSearchInput) {
                    clearBookSearch();
                }

                // Ctrl+P for print
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    printReport();
                }

                // Ctrl+N for notification
                if (e.ctrlKey && e.key === 'n') {
                    e.preventDefault();
                    sendNotification();
                }
            });
        }

        // Filter and search functions
        let filteredBooks = [...booksData];
        let currentSearchTerm = '';
        let currentStatusFilter = 'all';

        function handleBookSearch() {
            currentSearchTerm = bookSearchInput.value.toLowerCase().trim();
            applyFilters();
        }

        function handleStatusFilter() {
            currentStatusFilter = statusFilterSelect.value;
            applyFilters();
        }

        function applyFilters() {
            filteredBooks = booksData.filter(book => {
                // Apply search filter
                const matchesSearch = !currentSearchTerm ||
                    book.title.toLowerCase().includes(currentSearchTerm) ||
                    book.isbn.toLowerCase().includes(currentSearchTerm);

                // Apply status filter
                const matchesStatus = currentStatusFilter === 'all' ||
                    book.status === currentStatusFilter;

                return matchesSearch && matchesStatus;
            });

            renderBooksTable();
        }

        function clearBookSearch() {
            bookSearchInput.value = '';
            currentSearchTerm = '';
            applyFilters();
        }

        // Render books table
        function renderBooksTable() {
            if (!booksTableBody) return;

            booksTableBody.innerHTML = '';

            if (filteredBooks.length === 0) {
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
                return;
            }

            filteredBooks.forEach(book => {
                const row = document.createElement('tr');

                // Get status badge class and text
                let statusClass = '';
                let statusText = '';
                switch(book.status) {
                    case 'issued':
                        statusClass = 'issued';
                        statusText = 'Issued';
                        break;
                    case 'overdue':
                        statusClass = 'overdue';
                        statusText = 'Overdue';
                        break;
                    case 'returned':
                        statusClass = 'returned';
                        statusText = 'Returned';
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
                <span class="table-badge ${statusClass}">${statusText}</span>
            </td>
            <td>
                <span class="fine-amount ${book.fine > 0 ? 'has-fine' : ''}">
                    ₹${book.fine}
                </span>
            </td>
            <td>
                <button class="view-btn" onclick="viewBookDetails(${book.id})">
                    View
                </button>
            </td>
        `;

                booksTableBody.appendChild(row);
            });
        }

        // Action functions
        function sendNotification() {
            console.log('Sending notification...');
            alert('Notification sent to student!\n\nIn a real application, this would open a notification dialog.');
        }

        function printReport() {
            console.log('Printing report...');
            alert('Report generated!\n\nIn a real application, this would generate a PDF report for printing.');
        }

        function viewBookDetails(bookId) {
            const book = booksData.find(b => b.id === bookId);
            if (!book) {
                showToast('Book details not found.', 'error');
                return;
            }

            // Populate book cover using DOM to avoid inline onerror quoting issues
            const coverContainer = document.getElementById('bookCoverContent');
            coverContainer.innerHTML = '';
            if (book.coverImage) {
                const img = document.createElement('img');
                // Prepend storage base URL to relative path
                img.src = '{{ asset("storage") }}/' + book.coverImage;
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
            document.getElementById('bookDetailsAuthor').textContent = `by ${book.author}`;
            document.getElementById('bookDetailsISBN').textContent = book.isbn;
            document.getElementById('bookDetailsPublisher').textContent = book.publisher;
            document.getElementById('bookDetailsCondition').textContent = book.condition;
            document.getElementById('bookDetailsIssueDate').textContent = book.issueDateFull;
            document.getElementById('bookDetailsDueDate').textContent = book.dueDate;
            document.getElementById('bookDetailsReturnDate').textContent = book.returnDate;
            document.getElementById('bookDetailsFine').textContent = `₹${parseFloat(book.fine).toFixed(2)}`;
            document.getElementById('bookDetailsDescription').textContent = book.description;

            // Status badge
            const statusBadge = document.getElementById('bookDetailsStatus');
            let statusText = book.status.charAt(0).toUpperCase() + book.status.slice(1);
            statusBadge.innerHTML = `<span class="book-detail-badge status-${book.status}">${statusText}</span>`;

            // Show/hide overdue info
            const overdueInfo = document.getElementById('overdueInfo');
            if (book.status === 'overdue') {
                overdueInfo.style.display = 'block';
                document.getElementById('bookDetailsDaysOverdue').textContent = `${book.daysOverdue} days`;
            } else {
                overdueInfo.style.display = 'none';
            }

            // Show modal
            const overlay = document.getElementById('bookDetailsOverlay');
            overlay.classList.add('show');
        }

        function closeBookDetailsModal() {
            const overlay = document.getElementById('bookDetailsOverlay');
            overlay.classList.remove('show');
        }

        // Back button functionality
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.activeElement.tagName !== 'INPUT') {
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
            
            // Load privilege settings on page load
            loadPrivilegeSettings();

            // Fine action buttons will be set up in renderFinesTable

            // Book Details Modal
            document.getElementById('closeBookDetailsBtn')?.addEventListener('click', closeBookDetailsModal);
            document.getElementById('bookDetailsOverlay')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeBookDetailsModal();
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

            if (finesData.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
            <td colspan="5">
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <h3>No Fines & Payments</h3>
                    <p>No outstanding fines for this student. Fine records will appear here when applicable.</p>
                </div>
            </td>
        `;
                finesTableBody.appendChild(emptyRow);
                return;
            }

            finesData.forEach(fine => {
                const row = document.createElement('tr');

                // Get payment status badge
                const statusClass = fine.paymentStatus === 'paid' ? 'paid' : 'unpaid';
                const statusText = fine.paymentStatus === 'paid' ? 'Paid' : 'Unpaid';

                // Build action buttons
                let actionButtons = '';
                fine.actions.forEach(action => {
                    let buttonText = '';
                    let buttonClass = '';

                    switch(action) {
                        case 'adjust':
                            buttonText = 'Adjust';
                            buttonClass = 'adjust';
                            break;
                        case 'waive':
                            buttonText = 'Waive';
                            buttonClass = 'waive';
                            break;
                        case 'mark-paid':
                            buttonText = 'Mark Paid';
                            buttonClass = 'mark-paid';
                            break;
                        case 'view-history':
                            buttonText = 'View History';
                            buttonClass = 'view-history';
                            break;
                    }

                    actionButtons += `
                <button class="action-btn-small ${buttonClass}" onclick="handleFineAction('${action}', ${fine.id})">
                    ${buttonText}
                </button>
            `;
                });

                row.innerHTML = `
            <td><strong>${fine.bookName}</strong></td>
            <td>${fine.daysOverdue}</td>
            <td style="color: ${fine.paymentStatus === 'unpaid' ? '#dc2626' : '#16a34a'}; font-weight: 600;">
                ₹${fine.fineAmount}
            </td>
            <td>
                <span class="payment-badge ${statusClass}">${statusText}</span>
            </td>
            <td>
                <div class="fine-actions">
                    ${actionButtons}
                </div>
            </td>
        `;

                finesTableBody.appendChild(row);
            });
        }

        // Track activity logs display state
        let activityLogsIndex = 0;
        let allActivityLogs = [];

        // Render activity logs
        function renderActivityLogs() {
            const activityTimeline = document.getElementById('activityTimeline');
            if (!activityTimeline) return;

            activityTimeline.innerHTML = '';
            
            // Combine all logs
            allActivityLogs = [...activityLogs, ...(remainingActivityLogs || [])];

            // Check if there are no logs at all
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

            // Reset index and display first batch
            activityLogsIndex = 0;
            displayActivityLogsBatch();
        }

        function displayActivityLogsBatch() {
            const activityTimeline = document.getElementById('activityTimeline');
            if (!activityTimeline) return;

            // Display next 10 logs
            const logsToDisplay = allActivityLogs.slice(activityLogsIndex, activityLogsIndex + 10);
            logsToDisplay.forEach(log => {
                appendActivityItem(log, activityTimeline);
            });

            activityLogsIndex += 10;

            // Remove existing button if any
            const existingButton = activityTimeline.querySelector('.show-more-btn');
            if (existingButton) {
                existingButton.remove();
            }

            // Check if there are more logs to show
            if (activityLogsIndex < allActivityLogs.length) {
                // Add "Show More" button
                const showMoreButton = document.createElement('button');
                showMoreButton.className = 'show-more-btn';
                showMoreButton.textContent = 'Show More';
                showMoreButton.onclick = function() {
                    displayActivityLogsBatch();
                };
                activityTimeline.appendChild(showMoreButton);
            }
        }

        function appendActivityItem(log, container) {
            const activityItem = document.createElement('div');
            activityItem.className = `activity-item ${log.type}`;

            // Get icon based on type
            let iconSVG = '';
            switch(log.type) {
                case 'book-issued':
                    iconSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                </svg>`;
                    break;
                case 'book-returned':
                    iconSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>`;
                    break;
                case 'fine-applied':
                    iconSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>`;
                    break;
                case 'account-status':
                    iconSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="8.5" cy="7" r="4"/>
                    <polyline points="17 11 19 13 23 9"/>
                </svg>`;
                    break;
                case 'profile-updated':
                    iconSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>`;
                    break;
            }

            activityItem.innerHTML = `
            <div class="activity-icon">
                ${iconSVG}
            </div>
            <div class="activity-content">
                <div class="activity-title">${log.title}</div>
                <div class="activity-desc">${log.description}</div>
                <div class="activity-time">${log.time}</div>
            </div>
        `;

            container.appendChild(activityItem);
        }

        // Toast notification system
        function showToast(message, type = 'info') {
            const toastContainer = document.getElementById('toastContainer');
            if (!toastContainer) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            let iconSVG = '';
            switch(type) {
                case 'success':
                    iconSVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>`;
                    break;
                case 'error':
                    iconSVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>`;
                    break;
                case 'warning':
                    iconSVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>`;
                    break;
                default:
                    iconSVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>`;
            }

            toast.innerHTML = `
        <div class="toast-icon">${iconSVG}</div>
        <div>${message}</div>
    `;

            toastContainer.appendChild(toast);

            // Remove toast after animation
            setTimeout(() => {
                if (toast.parentNode === toastContainer) {
                    toastContainer.removeChild(toast);
                }
            }, 3000);
        }

        // Account Management Functions
        function resetPassword() {
            if (!confirm('Are you sure you want to reset the password for this student?\n\nA temporary password will be generated and sent to the student\'s email address.')) {
                return;
            }

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
                    showToast('Temporary password has been sent to the student\'s email address. The student must change it on first login.', 'success');
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

        function suspendAccount() {
            if (confirm('Are you sure you want to suspend this student account? The student will not be able to access the system until the account is reactivated.')) {
                // Simulate API call
                setTimeout(() => {
                    showToast('Account suspended successfully! The student has been notified.', 'warning');
                }, 500);
            }
        }

        function activateAccount() {
            if (confirm('Are you sure you want to activate this student account? The student will be able to access the system immediately.')) {
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
                    } else {
                        showToast(data.message || 'Failed to activate account.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while activating the account.', 'error');
                });
            }
        }

        function deactivateAccount() {
            if (confirm('Are you sure you want to deactivate this student account? The student will not be able to access the system until the account is activated.')) {
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
                    } else {
                        showToast(data.message || 'Failed to deactivate account.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while deactivating the account.', 'error');
                });
            }
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

        function submitRoleChange() {
            if (!selectedRole) return;

            const confirmBtn = document.getElementById('confirmRoleBtn');
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
                    showToast(`Role changed to ${selectedRole.charAt(0).toUpperCase() + selectedRole.slice(1)} successfully!`, 'success');
                    closeRoleModal();
                    // Optional: Reload page after delay to show updated role
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

        function deleteAccount() {
            if (!confirm('⚠️ WARNING: This action cannot be undone!\n\nAre you sure you want to permanently delete this student account? All associated data will be permanently removed.')) {
                return;
            }

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

        // Fine Management Functions
        function generateReceipt() {
            // Simulate receipt generation
            showToast('Generating receipt PDF...', 'info');

            setTimeout(() => {
                showToast('Receipt generated successfully! Download will start automatically.', 'success');
                // In real app: trigger PDF download
            }, 1000);
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

        // Load privilege settings when page loads
        function loadPrivilegeSettings() {
            fetch(`/admin/students/${currentStudentId}/privileges`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const privileges = data.privileges;
                    const effective = data.effective;
                    
                    // Set input values - use effective values OR empty to show global defaults
                    document.getElementById('maxBooks').value = privileges.max_books ?? effective.max_books;
                    document.getElementById('maxDays').value = privileges.issue_duration_days ?? effective.issue_duration_days;
                    document.getElementById('fineRate').value = privileges.per_day_fine ?? effective.per_day_fine;
                    document.getElementById('borrowingPermission').value = privileges.borrowing_allowed ? 'allowed' : 'restricted';
                    
                    // Store original values to detect changes
                    window.originalPrivileges = {
                        max_books: privileges.max_books,
                        issue_duration_days: privileges.issue_duration_days,
                        per_day_fine: privileges.per_day_fine,
                        borrowing_allowed: privileges.borrowing_allowed
                    };
                } else {
                    console.error('Failed to load privileges:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading privileges:', error);
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
            
            if (!newAmount || isNaN(newAmount) || newAmount < 0) {
                showToast('Please enter a valid amount.', 'error');
                return;
            }

            const submitBtn = document.getElementById('adjustFineSubmitBtn');
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
                    showToast('✅ Fine adjusted successfully!', 'success');
                    closeFineModal('adjust');
                    // Refresh fines table
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to adjust fine.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Adjust Fine';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network issue - fine may have been adjusted. Refreshing page...', 'warning');
                                setTimeout(() => location.reload(), 2000);
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
                    showToast('✅ Fine waived successfully! Email sent to student.', 'success');
                    closeFineModal('waive');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to waive fine.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Waive Fine';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network issue - fine may have been waived. Refreshing page...', 'warning');
                                setTimeout(() => location.reload(), 2000);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Waive Fine';
            });
        }

        function markFineAsPaid(fineId, fine) {
            currentFineId = fineId;
            document.getElementById('paidAmount').textContent = '₹' + parseFloat(fine.fineAmount).toFixed(2);
            document.getElementById('markPaidSubmitBtn').disabled = false;
            document.getElementById('markPaidSubmitBtn').textContent = 'Mark as Paid';
            document.getElementById('markPaidOverlay').classList.add('show');
        }

        function submitMarkAsPaid() {
            const submitBtn = document.getElementById('markPaidSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Marking...';

            fetch(`/admin/fines/${currentFineId}/mark-as-paid`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
                ,
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Fine marked as paid! Receipt email sent to student.', 'success');
                    closeFineModal('paid');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(data.message || 'Failed to mark fine as paid.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Mark as Paid';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network issue - fine may have been marked as paid. Refreshing page...', 'warning');
                setTimeout(() => location.reload(), 2000);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Mark as Paid';
            });
        }

        function viewFineHistory(fineId, fine) {
            currentFineId = fineId;
            document.getElementById('historyAmount').textContent = '₹' + parseFloat(fine.fineAmount).toFixed(2);
            document.getElementById('fineHistoryList').innerHTML = '<li class="fine-history-item" style="text-align: center; padding: 20px;"><span>Loading...</span></li>';
            document.getElementById('historyOverlay').classList.add('show');

            fetch(`/admin/fines/${fineId}/history`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
                ,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                const historyList = document.getElementById('fineHistoryList');
                if (data.success && data.history && data.history.length > 0) {
                    historyList.innerHTML = data.history.map(item => `
                        <li class="fine-history-item">
                            <div class="fine-history-dot"></div>
                            <div class="fine-history-content">
                                <h4>${item.action}</h4>
                                <p><strong>Date:</strong> ${item.date}</p>
                                <p><strong>By:</strong> ${item.user}</p>
                            </div>
                        </li>
                    `).join('');
                } else {
                    historyList.innerHTML = '<li class="fine-history-item" style="text-align: center; padding: 20px;"><p>No history available</p></li>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('fineHistoryList').innerHTML = '<li class="fine-history-item" style="text-align: center; padding: 20px;"><p style="color: #ef4444;">Unable to load history. Please try again.</p></li>';
            });
        }

        function closeFineModal(type) {
            if (type === 'adjust') document.getElementById('adjustFineOverlay').classList.remove('show');
            if (type === 'waive') document.getElementById('waiveFineOverlay').classList.remove('show');
            if (type === 'paid') document.getElementById('markPaidOverlay').classList.remove('show');
            if (type === 'history') document.getElementById('historyOverlay').classList.remove('show');
            currentFineId = null;
        }

        // Close modals on overlay click
        ['adjustFineOverlay', 'waiveFineOverlay', 'markPaidOverlay', 'historyOverlay'].forEach(id => {
            document.getElementById(id)?.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                    currentFineId = null;
                }
            });
        });

        // Privilege Settings Functions
        function savePrivilegeSettings() {
            const maxBooks = document.getElementById('maxBooks').value;
            const maxDays = document.getElementById('maxDays').value;
            const fineRate = document.getElementById('fineRate').value;
            const borrowingPermission = document.getElementById('borrowingPermission').value;

            // Validate inputs
            if (!maxBooks || maxBooks < 1 || maxBooks > 20) {
                showToast('Maximum books must be between 1 and 20', 'error');
                return;
            }

            if (!maxDays || maxDays < 1 || maxDays > 90) {
                showToast('Maximum issue duration must be between 1 and 90 days', 'error');
                return;
            }

            if (fineRate === null || fineRate === '' || fineRate < 0 || fineRate > 100) {
                showToast('Fine rate must be between ₹0 and ₹100 per day', 'error');
                return;
            }

            showToast('Saving privilege settings...', 'info');

            fetch(`/admin/students/${currentStudentId}/privileges`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    max_books: parseInt(maxBooks),
                    issue_duration_days: parseInt(maxDays),
                    per_day_fine: parseFloat(fineRate),
                    borrowing_allowed: borrowingPermission === 'allowed'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('✅ Privilege settings saved successfully!', 'success');
                    // Reload privileges to reflect changes
                    setTimeout(() => loadPrivilegeSettings(), 500);
                } else {
                    showToast(data.message || 'Failed to save privilege settings.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Network issue - privileges may have been saved. Refreshing...', 'warning');
                setTimeout(() => location.reload(), 2000);
            });
        }
    </script>
@endpush
