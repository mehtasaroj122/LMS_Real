@extends('student.layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        /* ============================= */
        /* THEME VARIABLES & BASE STYLES */
        /* ============================= */
        :root {
            --font-family: "Inter", sans-serif;
            --transition-speed: 0.3s;
            --bg-primary: #f9fafb;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-gradient: linear-gradient(to right, #7c3aed, #4f46e5);
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #64748b;
            --border-color: #e5e7eb;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        .dark-theme {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #1e293b;
            --bg-gradient: linear-gradient(to right, #5b21b6, #3730a3);
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --shadow-color: rgba(0, 0, 0, 0.4);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: all var(--transition-speed) ease;
        }

        .dashboard-page {
            min-height: 100vh;
            width: 100%;
            max-width: none;
            padding: 0.75rem;
        }

        @media (min-width: 768px) {
            .dashboard-page {
                padding: 0;
            }
        }

        /* ================== */
        /* STATS CARDS */
        /* ================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .stat-card {
            padding: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            border-left: 3px solid;
        }

        .light-theme .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
        }

        .dark-theme .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
        }

        .stat-card.issued {
            border-left-color: #2563eb;
        }

        .stat-card.returned {
            border-left-color: #10b981;
        }

        .stat-card.fines {
            border-left-color: #ef4444;
        }

        .stat-card.requests {
            border-left-color: #8b5cf6;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .stat-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
        }

        .stat-icon {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .light-theme .stat-card.issued .stat-icon {
            background-color: #dbeafe;
            color: #2563eb;
        }

        .light-theme .stat-card.returned .stat-icon {
            background-color: #dcfce7;
            color: #10b981;
        }

        .light-theme .stat-card.fines .stat-icon {
            background-color: #fee2e2;
            color: #ef4444;
        }

        .light-theme .stat-card.requests .stat-icon {
            background-color: #f5f3ff;
            color: #8b5cf6;
        }

        .dark-theme .stat-card.issued .stat-icon {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .dark-theme .stat-card.returned .stat-icon {
            background-color: #064e3b;
            color: #34d399;
        }

        .dark-theme .stat-card.fines .stat-icon {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        .dark-theme .stat-card.requests .stat-icon {
            background-color: #4c1d95;
            color: #c4b5fd;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.125rem;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }

        .status-badge.active {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .status-badge.purple {
            background-color: #f5f3ff;
            color: #7c3aed;
        }

        .dark-theme .status-badge.active {
            background-color: #14532d;
            color: #86efac;
        }

        .dark-theme .status-badge.purple {
            background-color: #4c1d95;
            color: #c4b5fd;
        }

        /* ================== */
        /* MAIN CONTENT GRID */
        /* ================== */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 1024px) {
            .main-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        /* Issued Books Section */
        .section-card {
            background-color: var(--bg-card);
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-header h2 {
            font-size: 1rem;
            font-weight: bold;
        }

        .section-link {
            font-size: 0.75rem;
            font-weight: 500;
            color: #7c3aed;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.125rem;
        }

        .dark-theme .section-link {
            color: #a78bfa;
        }

        .section-body {
            padding: 0.75rem;
        }

        .book-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .book-item:hover {
            background-color: var(--bg-primary);
            transform: translateY(-1px);
        }

        .book-item.overdue {
            background-color: #fef2f2;
        }

        .dark-theme .book-item.overdue {
            background-color: #450a0a;
        }

        .book-info h3 {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.125rem;
        }

        .book-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        .book-status {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 600;
            border-radius: 9999px;
            margin-bottom: 0.125rem;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .dark-theme .badge-success {
            background-color: #064e3b;
            color: #a7f3d0;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .dark-theme .badge-danger {
            background-color: #7f1d1d;
            color: #fca5a5;
        }

        .fine-amount {
            font-size: 0.7rem;
            font-weight: 600;
            color: #dc2626;
        }

        .dark-theme .fine-amount {
            color: #f87171;
        }

        /* Notifications Section */
        .notification-item {
            display: flex;
            padding-bottom: 0.5rem;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .notification-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .notification-indicator {
            width: 3px;
            border-radius: 1.5px;
            margin-right: 0.5rem;
            flex-shrink: 0;
        }

        .notification-indicator.red {
            background-color: #ef4444;
        }

        .notification-indicator.yellow {
            background-color: #f59e0b;
        }

        .notification-indicator.green {
            background-color: #10b981;
        }

        .notification-indicator.blue {
            background-color: #3b82f6;
        }

        .notification-content h4 {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.125rem;
        }

        .notification-content p {
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        /* ================== */
        /* QUICK ACTIONS */
        /* ================== */
        .quick-actions-section {
            background-color: var(--bg-card);
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            margin-bottom: 0.75rem;
        }

        .quick-actions-section h2 {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .actions-grid-inner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.5rem;
        }

        .action-btn {
            display: block;
            text-align: left;
            padding: 0.5rem;
            border: 1px solid;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            cursor: pointer;
            background-color: transparent;
            color: var(--text-primary);
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-1px);
        }

        .action-btn .btn-content {
            display: flex;
            align-items: center;
        }

        .action-btn .btn-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
        }

        .action-btn h3 {
            font-size: 0.875rem;
            font-weight: bold;
            margin-bottom: 0.125rem;
        }

        .action-btn p {
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        /* Action button colors */
        .action-blue {
            border-color: #93c5fd;
            background-color: #eff6ff;
        }

        .dark-theme .action-blue {
            border-color: #1e40af;
            background-color: #1e3a8a;
        }

        .action-teal {
            border-color: #5eead4;
            background-color: #f0fdfa;
        }

        .dark-theme .action-teal {
            border-color: #0f766e;
            background-color: #134e4a;
        }

        .action-red {
            border-color: #fca5a5;
            background-color: #fef2f2;
        }

        .dark-theme .action-red {
            border-color: #7f1d1d;
            background-color: #450a0a;
        }

        .action-purple {
            border-color: #c4b5fd;
            background-color: #faf5ff;
        }

        .dark-theme .action-purple {
            border-color: #5b21b6;
            background-color: #3c0764;
        }

        .action-slate {
            border-color: #cbd5e1;
            background-color: #f8fafc;
        }

        .dark-theme .action-slate {
            border-color: #475569;
            background-color: #0f172a;
        }

        /* ================== */
        /* PROFILE CARD (NEW) */
        /* ================== */
        .profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.28);
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .profile-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .profile-card-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
            z-index: 1;
        }

        @media (min-width: 640px) {
            .profile-card-content {
                flex-direction: row;
                align-items: center;
            }
        }

        .profile-card-avatar {
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
            flex-shrink: 0;
            background: white;
        }

        .profile-card-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-card-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .profile-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.875rem;
            opacity: 0.95;
        }

        .profile-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
        }

        .profile-meta-icon {
            width: 1rem;
            height: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .profile-meta-icon svg {
            width: 1rem;
            height: 1rem;
        }

        .profile-card-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        @media (min-width: 640px) {
            .profile-card-stats {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .profile-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            text-align: center;
            padding: 0.85rem;
            border-radius: 0.9rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(14px);
        }

        .profile-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .profile-stat-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .profile-stat-icon {
            width: 0.95rem;
            height: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .profile-stat-icon svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        /* ================== */
        /* CHARTS ROW (NEW)   */
        /* ================== */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 1024px) {
            .charts-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        .chart-card {
            background-color: var(--bg-card);
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            padding: 1rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
        }

        .dark-theme .chart-card {
            box-shadow: none;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .chart-heading {
            min-width: 0;
        }

        .chart-title {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .chart-title-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 0.625rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .chart-title-icon svg {
            width: 1.05rem;
            height: 1.05rem;
        }

        .chart-title-icon.activity {
            background: #dbeafe;
            color: #2563eb;
        }

        .chart-title-icon.requests {
            background: #ede9fe;
            color: #7c3aed;
        }

        .dark-theme .chart-title-icon.activity {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .dark-theme .chart-title-icon.requests {
            background: #4c1d95;
            color: #d8b4fe;
        }

        .chart-description {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-top: 0.45rem;
            line-height: 1.5;
        }

        .chart-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .chart-count.requests {
            background: #ede9fe;
            color: #7c3aed;
        }

        .dark-theme .chart-count {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .dark-theme .chart-count.requests {
            background: #4c1d95;
            color: #d8b4fe;
        }

        .chart-container {
            position: relative;
            height: 240px;
        }

        @media (max-width: 639px) {
            .chart-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* ================== */
        /* TABLES ROW (NEW)   */
        /* ================== */
        .tables-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 1024px) {
            .tables-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .data-card {
            background-color: var(--bg-card);
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            padding: 1rem;
            max-height: 350px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
        }

        .dark-theme .data-card {
            box-shadow: none;
        }

        .data-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
            gap: 0.75rem;
        }

        .data-card-count {
            background-color: #dbeafe;
            color: #1e40af;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            white-space: nowrap;
        }

        .dark-theme .data-card-count {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .data-card-title {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .data-card-title-icon {
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 0.55rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .data-card-title-icon svg {
            width: 0.95rem;
            height: 0.95rem;
        }

        .data-card-title-icon.issued {
            background: #dbeafe;
            color: #2563eb;
        }

        .data-card-title-icon.due {
            background: #fef3c7;
            color: #d97706;
        }

        .data-card-title-icon.notifications {
            background: #ede9fe;
            color: #7c3aed;
        }

        .dark-theme .data-card-title-icon.issued {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .dark-theme .data-card-title-icon.due {
            background: #78350f;
            color: #fcd34d;
        }

        .dark-theme .data-card-title-icon.notifications {
            background: #4c1d95;
            color: #d8b4fe;
        }

        .data-card-list {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.125rem;
        }

        .dashboard-show-more-item {
            display: flex;
            margin-top: 0.5rem;
        }

        .dashboard-show-more {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            width: 100%;
            padding: 0.75rem 0.9rem;
            border-radius: 0.5rem;
            border: 1px dashed var(--border-color);
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .dashboard-show-more:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px var(--shadow-color);
            border-color: rgba(59, 130, 246, 0.35);
        }

        .dashboard-show-more svg {
            width: 0.9rem;
            height: 0.9rem;
        }

        .data-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
            background-color: transparent;
        }

        .data-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .data-item.overdue {
            background-color: #fee2e2;
            border-color: #fecaca;
        }

        .dark-theme .data-item.overdue {
            background-color: #450a0a;
            border-color: #7f1d1d;
        }

        .data-item-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .data-item-meta {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .notification-card.notification-card-unread {
            border-left: 3px solid #3b82f6;
            background: #eff6ff;
        }

        .dark-theme .notification-card.notification-card-unread {
            background: #172554;
            border-left-color: #60a5fa;
        }

        .notification-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .notification-card-body {
            flex: 1;
            min-width: 0;
        }

        .dashboard-notification-delete-btn {
            width: 1.85rem;
            height: 1.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #fecaca;
            border-radius: 9999px;
            background: #fef2f2;
            color: #dc2626;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .dashboard-notification-delete-btn:hover {
            color: #b91c1c;
            background: #fee2e2;
            border-color: #fca5a5;
        }

        .dashboard-notification-delete-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .dashboard-notification-delete-btn svg {
            width: 0.9rem;
            height: 0.9rem;
        }

        .dark-theme .dashboard-notification-delete-btn:hover {
            color: #fecaca;
            background: #450a0a;
            border-color: #7f1d1d;
        }

        .dark-theme .dashboard-notification-delete-btn {
            color: #f87171;
            border-color: #7f1d1d;
            background: rgba(127, 29, 29, 0.15);
        }

        .alert-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            border-left: 3px solid;
        }

        .alert-item.warning {
            background-color: #fef3c7;
            border-left-color: #f59e0b;
        }

        .alert-item.danger {
            background-color: #fee2e2;
            border-left-color: #ef4444;
        }

        .dark-theme .alert-item.warning {
            background-color: #451a03;
            border-left-color: #fbbf24;
        }

        .dark-theme .alert-item.danger {
            background-color: #450a0a;
            border-left-color: #f87171;
        }

        .alert-item-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .alert-item-meta {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .alert-badge {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            margin-top: 0.35rem;
        }

        .alert-badge.today {
            background-color: #ef4444;
            color: white;
        }

        .alert-badge.tomorrow {
            background-color: #f59e0b;
            color: white;
        }

        .alert-badge.soon {
            background-color: #3b82f6;
            color: white;
        }

        .empty-state {
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-align: center;
            padding: 1rem;
        }

        /* ================== */
        /* PROFILE SNAPSHOT */
        /* ================== */
        .profile-section {
            background-color: var(--bg-card);
            border-radius: 0.5rem;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
        }

        .profile-section h2 {
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .profile-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .profile-avatar-large {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--bg-card);
            margin-bottom: 0.5rem;
        }

        .profile-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 0.875rem;
            font-weight: bold;
            margin-bottom: 0.125rem;
        }

        .profile-email {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .profile-details {
            width: 100%;
            padding: 0.5rem;
            border-radius: 0.375rem;
            background-color: var(--bg-primary);
            text-align: left;
            margin-bottom: 0.5rem;
        }

        .detail-item {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: flex-start;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            min-width: 100px;
            padding-right: 0.5rem;
        }

        .detail-value {
            font-size: 0.7rem;
            font-weight: 500;
            flex: 1;
        }

        .profile-btn {
            width: 100%;
            padding: 0.375rem;
            background: var(--bg-gradient);
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.2s ease;
        }

        .profile-btn:hover {
            opacity: 0.9;
        }

        /* ================== */
        /* RESPONSIVE LAYOUT */
        /* ================== */
        @media (min-width: 1024px) {
            .content-grid {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 0.75rem;
            }

            .quick-actions-section {
                margin-bottom: 0;
            }
        }

        /* ============================= */
        /* DASHBOARD BOTTOM ROW LAYOUT   */
        /* ============================= */
        .dashboard-bottom-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }

        .dashboard-bottom-row .quick-actions-section {
            margin-bottom: 0;
        }

        @media (min-width: 1024px) {
            .dashboard-bottom-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        .dashboard-bottom-row > .quick-actions-section,
        .dashboard-bottom-row > .privileges-section {
            height: 100%;
        }

        .dashboard-bottom-row > .quick-actions-section,
        .dashboard-bottom-row > .privileges-section,
        .usage-progress-container {
            display: flex;
            flex-direction: column;
        }

        /* ============================= */
        /* PRIVILEGE SECTION STYLES      */
        /* ============================= */
        .privileges-section {
            background: var(--bg-card);
            border-radius: 0.75rem;
            padding: 1.25rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px var(--shadow-color);
        }

        .privileges-header {
            margin-bottom: 1rem;
        }

        .privileges-title {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .section-icon {
            width: 2rem;
            height: 2rem;
            border-radius: 0.625rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #2563eb;
            flex-shrink: 0;
        }

        .section-icon svg {
            width: 1.1rem;
            height: 1.1rem;
        }

        .privilege-badge {
            font-size: 0.625rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-left: auto;
        }

        .privilege-badge-custom {
            background: #fef3c7;
            color: #92400e;
        }

        .dark-theme .privilege-badge-custom {
            background: #78350f;
            color: #fcd34d;
        }

        .privilege-badge-default {
            background: #f3f4f6;
            color: #374151;
        }

        .dark-theme .privilege-badge-default {
            background: #374151;
            color: #d1d5db;
        }

        .section-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .privileges-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 640px) {
            .privileges-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .privilege-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            border-left: 4px solid;
            background: var(--bg-secondary);
            transition: all 0.2s ease;
        }

        .privilege-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        .card-max-books {
            border-left-color: #3b82f6;
        }

        .card-duration {
            border-left-color: #10b981;
        }

        .card-fine {
            border-left-color: #f59e0b;
        }

        .card-status.status-active {
            border-left-color: #22c55e;
        }

        .card-status.status-restricted {
            border-left-color: #ef4444;
        }

        .privilege-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #eff6ff;
            color: #2563eb;
        }

        .privilege-icon svg {
            width: 1.35rem;
            height: 1.35rem;
        }

        .privilege-content {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .privilege-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .privilege-label {
            font-size: 0.6875rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .card-max-books .privilege-icon {
            background: #dbeafe;
            color: #2563eb;
        }

        .card-duration .privilege-icon {
            background: #dcfce7;
            color: #059669;
        }

        .card-fine .privilege-icon {
            background: #fef3c7;
            color: #d97706;
        }

        .card-status.status-active .privilege-icon {
            background: #dcfce7;
            color: #16a34a;
        }

        .card-status.status-restricted .privilege-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .card-status.status-active .privilege-value {
            color: #15803d;
        }

        .card-status.status-restricted .privilege-value {
            color: #dc2626;
        }

        .dark-theme .section-icon {
            background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
            color: #bfdbfe;
        }

        .dark-theme .card-max-books .privilege-icon {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .dark-theme .card-duration .privilege-icon {
            background: #064e3b;
            color: #6ee7b7;
        }

        .dark-theme .card-fine .privilege-icon {
            background: #78350f;
            color: #fcd34d;
        }

        .dark-theme .card-status.status-active .privilege-icon {
            background: #14532d;
            color: #86efac;
        }

        .dark-theme .card-status.status-restricted .privilege-icon {
            background: #7f1d1d;
            color: #fca5a5;
        }

        .dark-theme .card-status.status-active .privilege-value {
            color: #86efac;
        }

        .dark-theme .card-status.status-restricted .privilege-value {
            color: #fca5a5;
        }

        .usage-progress-container {
            padding: 1rem;
            background: var(--bg-primary);
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin-top: auto;
        }

        .usage-progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .usage-fraction {
            color: var(--text-secondary);
            font-weight: 600;
            white-space: nowrap;
        }

        .usage-progress-bar {
            height: 8px;
            background: var(--border-color);
            border-radius: 9999px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .usage-progress-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease;
        }

        .progress-success {
            background: linear-gradient(90deg, #22c55e, #16a34a);
        }

        .progress-warning {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .progress-danger {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .usage-hint {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin: 0;
        }

        @media (max-width: 639px) {
            .usage-progress-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-page">
        <div class="profile-card">
            <div class="profile-card-content">
                <div class="profile-card-avatar">
                    <img src="{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset('storage/' . $user->profile_photo)) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=fff&color=667eea' }}" alt="Profile">
                </div>
                <div class="profile-card-info">
                    <h1>{{ $user->name }}</h1>
                    <div class="profile-card-meta">
                        <span class="profile-meta-item">
                            <span class="profile-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                                    <path d="M7 10h4M7 14h6M17 9h.01"></path>
                                </svg>
                            </span>
                            <span>{{ $student?->roll_no ?? 'N/A' }}</span>
                        </span>
                        <span class="profile-meta-item">
                            <span class="profile-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 17A2.5 2.5 0 0 0 4 19.5V6.5A2.5 2.5 0 0 1 6.5 4H20v13"></path>
                                </svg>
                            </span>
                            <span>{{ $department?->name ?? 'N/A' }}</span>
                        </span>
                        <span class="profile-meta-item">
                            <span class="profile-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                    <path d="M16 3v4M8 3v4M3 9h18"></path>
                                </svg>
                            </span>
                            <span>Year {{ $yearOfStudy }}</span>
                        </span>
                        <span class="profile-meta-item">
                            <span class="profile-meta-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <path d="m4 7 8 6 8-6"></path>
                                </svg>
                            </span>
                            <span>{{ $user->email }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="profile-card-stats">
                <div class="profile-stat">
                    <div class="profile-stat-value">{{ $booksIssuedCount }}</div>
                    <div class="profile-stat-label">
                        <span class="profile-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <path d="M3.5 6.5A2.5 2.5 0 0 1 6 4h5v15H6a2.5 2.5 0 0 0-2.5 2.5V6.5Z"></path>
                                <path d="M20.5 6.5A2.5 2.5 0 0 0 18 4h-5v15h5a2.5 2.5 0 0 1 2.5 2.5V6.5Z"></path>
                            </svg>
                        </span>
                        <span>Issued Books</span>
                    </div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value">{{ $booksReturnedCount }}</div>
                    <div class="profile-stat-label">
                        <span class="profile-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="m8.5 12 2.5 2.5 4.5-5"></path>
                            </svg>
                        </span>
                        <span>Returned Books</span>
                    </div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value">₹{{ number_format((float) $pendingFines, 2) }}</div>
                    <div class="profile-stat-label">
                        <span class="profile-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                                <circle cx="12" cy="12" r="2.5"></circle>
                                <path d="M7 10h.01M17 14h.01"></path>
                            </svg>
                        </span>
                        <span>Pending Fines</span>
                    </div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value">{{ $activeRequestsCount }}</div>
                    <div class="profile-stat-label">
                        <span class="profile-stat-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <rect x="5" y="3" width="14" height="18" rx="2"></rect>
                                <path d="M9 8h6M9 12h6M9 16h4"></path>
                            </svg>
                        </span>
                        <span>Active Requests</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-heading">
                        <h3 class="chart-title">
                            <span class="chart-title-icon activity" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <path d="M4 19h16"></path>
                                    <path d="M7 16V9"></path>
                                    <path d="M12 16V5"></path>
                                    <path d="M17 16v-4"></path>
                                </svg>
                            </span>
                            Monthly Activity
                        </h3>
                        <p class="chart-description">
                            @if(($activityOverview['movements_total'] ?? 0) > 0)
                                {{ number_format($activityOverview['issued_total'] ?? 0) }} issues and {{ number_format($activityOverview['returned_total'] ?? 0) }} returns tracked across the last 30 days.
                            @else
                                No issue or return activity recorded in the last 30 days.
                            @endif
                        </p>
                    </div>
                    <span class="chart-count">{{ number_format($activityOverview['movements_total'] ?? 0) }} movements</span>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-heading">
                        <h3 class="chart-title">
                            <span class="chart-title-icon requests" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                    <rect x="5" y="3" width="14" height="18" rx="2"></rect>
                                    <path d="M9 8h6M9 12h6M9 16h4"></path>
                                </svg>
                            </span>
                            Request Status Overview
                        </h3>
                        <p class="chart-description">
                            @if(($requestOverview['total'] ?? 0) > 0)
                                You have {{ number_format($requestOverview['total'] ?? 0) }} request{{ ($requestOverview['total'] ?? 0) === 1 ? '' : 's' }}: {{ number_format($requestOverview['active'] ?? 0) }} active, {{ number_format($requestOverview['returned'] ?? 0) }} returned{{ ($requestOverview['closed'] ?? 0) > 0 ? ', and ' . number_format($requestOverview['closed'] ?? 0) . ' closed' : '' }}.
                            @else
                                You have not made any requests yet.
                            @endif
                        </p>
                    </div>
                    <span class="chart-count requests">{{ number_format($requestOverview['active'] ?? 0) }} active</span>
                </div>
                <div class="chart-container">
                    <canvas id="requestStatusChart"></canvas>
                </div>
            </div>
        </div>

        @php
            $todayStart = now()->startOfDay();
            $issuedBookItems = $issuedBooks->map(function ($issuedBook) use ($todayStart) {
                $dueDate = $issuedBook->due_date ? $issuedBook->due_date->copy()->startOfDay() : null;
                $daysUntilDue = $dueDate ? $todayStart->diffInDays($dueDate, false) : null;

                return [
                    'id' => (int) $issuedBook->id,
                    'title' => optional($issuedBook->book)->title ?? 'Untitled',
                    'issue_date' => optional($issuedBook->issue_date)->format('M d, Y'),
                    'due_date' => optional($issuedBook->due_date)->format('M d, Y'),
                    'fine_amount' => $issuedBook->fine ? number_format((float) $issuedBook->fine->amount, 2) : null,
                    'is_overdue' => $daysUntilDue !== null && $daysUntilDue < 0,
                    'due_badge_class' => $daysUntilDue !== null && $daysUntilDue < 0
                        ? 'today'
                        : ($daysUntilDue !== null && $daysUntilDue <= 3 ? 'tomorrow' : null),
                    'due_badge_text' => $daysUntilDue !== null && $daysUntilDue < 0
                        ? 'OVERDUE'
                        : ($daysUntilDue !== null && $daysUntilDue <= 3 ? 'DUE SOON' : null),
                ];
            })->values();

            $dueSoonItems = $dueSoon->map(function ($book) use ($todayStart) {
                $dueDate = $book->due_date ? $book->due_date->copy()->startOfDay() : null;
                $daysLeft = $dueDate ? $todayStart->diffInDays($dueDate, false) : null;

                return [
                    'id' => (int) $book->id,
                    'title' => optional($book->book)->title ?? 'Untitled',
                    'due_date' => optional($book->due_date)->format('M d, Y'),
                    'alert_class' => $daysLeft === 0 ? 'danger' : ($daysLeft === 1 ? 'warning' : ''),
                    'badge_class' => $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'tomorrow' : 'soon'),
                    'badge_text' => $daysLeft === 0
                        ? 'TODAY'
                        : ($daysLeft === 1 ? 'TOMORROW' : ($daysLeft !== null ? $daysLeft . ' DAYS' : 'SOON')),
                ];
            })->values();

            $notificationItems = $notifications->map(function ($notification) {
                return [
                    'id' => (int) $notification->id,
                    'title' => $notification->title ?: 'Notification',
                    'message' => $notification->message,
                    'time' => optional($notification->created_at)->diffForHumans(),
                    'is_unread' => $notification->read_at === null,
                ];
            })->values();
        @endphp

        <div class="tables-row">
            <div class="data-card">
                <div class="data-card-header">
                    <h3 class="data-card-title">
                        <span class="data-card-title-icon issued" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <path d="M3.5 6.5A2.5 2.5 0 0 1 6 4h5v15H6a2.5 2.5 0 0 0-2.5 2.5V6.5Z"></path>
                                <path d="M20.5 6.5A2.5 2.5 0 0 0 18 4h-5v15h5a2.5 2.5 0 0 1 2.5 2.5V6.5Z"></path>
                            </svg>
                        </span>
                        <span>Currently Issued</span>
                    </h3>
                    <span class="data-card-count">{{ $booksIssuedCount }}</span>
                </div>
                <div class="data-card-list" id="issuedBooksList"></div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <h3 class="data-card-title">
                        <span class="data-card-title-icon due" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M12 7v5l3 2"></path>
                            </svg>
                        </span>
                        <span>Due Soon</span>
                    </h3>
                    <span class="data-card-count">{{ $dueSoon->count() }}</span>
                </div>
                <div class="data-card-list" id="dueSoonList"></div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <h3 class="data-card-title">
                        <span class="data-card-title-icon notifications" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
                                <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
                                <path d="M10 20a2 2 0 0 0 4 0"></path>
                            </svg>
                        </span>
                        <span>Notifications</span>
                    </h3>
                    <span class="data-card-count" id="notificationsCount">{{ $notifications->count() }}</span>
                </div>
                <div class="data-card-list" id="notificationsList"></div>
            </div>
        </div>

        <div class="dashboard-bottom-row">
            <div class="quick-actions-section">
                <h2>Quick Actions</h2>
                <div class="actions-grid-inner">
                    <a href="{{ route('student.search') }}" class="action-btn action-blue">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3>Search Books</h3>
                                <p>Browse library catalog</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('student.my-books') }}" class="action-btn action-teal">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3>My Books</h3>
                                <p>Check issued books</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('student.requests') }}" class="action-btn action-purple">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3>My Requests</h3>
                                <p>Track or create requests</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('student.fines') }}" class="action-btn action-red">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3>Pay Fines</h3>
                                <p>Clear pending dues</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('student.profile') }}" class="action-btn action-slate">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20 21a8 8 0 0 0-16 0"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <h3>My Profile</h3>
                                <p>View account details</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="privileges-section">
                <div class="privileges-header">
                    <h2 class="privileges-title">
                        <span class="section-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                <circle cx="12" cy="15" r="1"></circle>
                            </svg>
                        </span>
                        Library Privilege Settings
                        @if($privilegeSettings['is_custom'])
                            <span class="privilege-badge privilege-badge-custom">Custom</span>
                        @else
                            <span class="privilege-badge privilege-badge-default">Default</span>
                        @endif
                    </h2>
                    <p class="section-subtitle">Your current borrowing permissions and limits</p>
                </div>

                <div class="privileges-grid">
                    <div class="privilege-card card-max-books">
                        <div class="privilege-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3.5 6.5A2.5 2.5 0 0 1 6 4h5v15H6a2.5 2.5 0 0 0-2.5 2.5V6.5Z"></path>
                                <path d="M20.5 6.5A2.5 2.5 0 0 0 18 4h-5v15h5a2.5 2.5 0 0 1 2.5 2.5V6.5Z"></path>
                            </svg>
                        </div>
                        <div class="privilege-content">
                            <span class="privilege-value">{{ $privilegeSettings['max_books'] }}</span>
                            <span class="privilege-label">Max Books</span>
                        </div>
                    </div>

                    <div class="privilege-card card-duration">
                        <div class="privilege-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                                <path d="M16 3v4M8 3v4M3 9h18"></path>
                                <path d="M8 13h.01M12 13h.01M16 13h.01M8 17h.01M12 17h.01M16 17h.01"></path>
                            </svg>
                        </div>
                        <div class="privilege-content">
                            <span class="privilege-value">{{ $privilegeSettings['issue_duration_days'] }}</span>
                            <span class="privilege-label">Days</span>
                        </div>
                    </div>

                    <div class="privilege-card card-fine">
                        <div class="privilege-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="6" width="18" height="12" rx="2"></rect>
                                <circle cx="12" cy="12" r="2.5"></circle>
                                <path d="M7 10h.01M17 14h.01"></path>
                            </svg>
                        </div>
                        <div class="privilege-content">
                            <span class="privilege-value">₹{{ number_format((float) $privilegeSettings['per_day_fine'], 2) }}</span>
                            <span class="privilege-label">Per Day Fine</span>
                        </div>
                    </div>

                    <div
                        class="privilege-card card-status {{ $privilegeSettings['borrowing_allowed'] ? 'status-active' : 'status-restricted' }}">
                        <div class="privilege-icon" aria-hidden="true">
                            @if($privilegeSettings['borrowing_allowed'])
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="m8.5 12 2.5 2.5 4.5-5"></path>
                                </svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="m9 9 6 6M15 9l-6 6"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="privilege-content">
                            <span class="privilege-value">{{ $privilegeSettings['borrowing_allowed'] ? 'Active' : 'Restricted' }}</span>
                            <span class="privilege-label">Borrowing</span>
                        </div>
                    </div>
                </div>

                <div class="usage-progress-container">
                    <div class="usage-progress-header">
                        <span>Current Usage</span>
                        <span class="usage-fraction">{{ $privilegeSettings['books_issued'] }} / {{ $privilegeSettings['max_books'] }} Books</span>
                    </div>
                    <div class="usage-progress-bar">
                        @php
                            $usagePercent = $privilegeSettings['max_books'] > 0
                                ? min(100, ($privilegeSettings['books_issued'] / $privilegeSettings['max_books']) * 100)
                                : 0;
                            $usageColor = $usagePercent >= 90 ? 'danger' : ($usagePercent >= 70 ? 'warning' : 'success');
                        @endphp
                        <div class="usage-progress-fill progress-{{ $usageColor }}" style="width: {{ $usagePercent }}%"></div>
                    </div>
                    <p class="usage-hint">
                        @if(!$privilegeSettings['borrowing_allowed'])
                            Borrowing is currently restricted for your account.
                        @elseif($privilegeSettings['remaining_slots'] > 0)
                            You can borrow {{ $privilegeSettings['remaining_slots'] }} more book{{ $privilegeSettings['remaining_slots'] > 1 ? 's' : '' }}.
                        @else
                            You've reached your book limit. Return books to borrow more.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
        let activityChart;
        let requestStatusChartInstance;

        function getResolvedTheme() {
            const savedTheme = localStorage.getItem('theme');

            if (savedTheme === 'dark-theme' || savedTheme === 'light-theme') {
                return savedTheme;
            }

            if (document.body.classList.contains('dark-theme')) {
                return 'dark-theme';
            }

            if (document.body.classList.contains('light-theme')) {
                return 'light-theme';
            }

            return prefersDarkScheme.matches ? 'dark-theme' : 'light-theme';
        }

        function initTheme() {
            const theme = getResolvedTheme();

            document.body.classList.remove('light-theme', 'dark-theme');
            document.body.classList.add(theme);
        }

        function getChartTheme() {
            const styles = getComputedStyle(document.body);

            return {
                textPrimary: styles.getPropertyValue('--text-primary').trim() || '#0f172a',
                textSecondary: styles.getPropertyValue('--text-secondary').trim() || '#64748b',
                gridColor: document.body.classList.contains('dark-theme') ? '#334155' : '#e5e7eb',
                cardColor: document.body.classList.contains('dark-theme') ? '#1e293b' : '#ffffff',
            };
        }

        function renderCharts() {
            if (typeof Chart === 'undefined') {
                return;
            }

            const activityCanvas = document.getElementById('activityChart');
            const requestStatusCanvas = document.getElementById('requestStatusChart');

            if (!activityCanvas || !requestStatusCanvas) {
                return;
            }

            if (Chart?.Tooltip?.positioners && !Chart.Tooltip.positioners.cursor) {
                Chart.Tooltip.positioners.cursor = function(_items, eventPosition) {
                    return {
                        x: eventPosition.x,
                        y: eventPosition.y,
                    };
                };
            }

            const theme = getChartTheme();
            const activityLabels = @json($monthlyActivity['labels'] ?? []);
            const activityIssued = @json($monthlyActivity['issued'] ?? []);
            const activityReturned = @json($monthlyActivity['returned'] ?? []);
            const hasActivityData = activityIssued.some(value => Number(value) > 0) || activityReturned.some(value => Number(value) > 0);
            const requestStatusLabels = @json($requestStatusChart['labels'] ?? []);
            const requestStatusData = @json($requestStatusChart['data'] ?? []);
            const requestStatusColors = @json($requestStatusChart['colors'] ?? []);
            const hasRequestStatusData = requestStatusData.length > 0;

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            color: theme.textSecondary,
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                            },
                        },
                    },
                },
            };

            if (activityChart) {
                activityChart.destroy();
            }

            if (requestStatusChartInstance) {
                requestStatusChartInstance.destroy();
            }

            activityChart = new Chart(activityCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: activityLabels,
                    datasets: [
                        {
                            label: 'Issued',
                            data: activityIssued,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.14)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 3,
                            pointRadius(context) {
                                return hasActivityData && Number(context.raw) > 0 ? 4 : 0;
                            },
                            pointHoverRadius(context) {
                                return hasActivityData && Number(context.raw) > 0 ? 6 : 0;
                            },
                            pointBackgroundColor: '#3b82f6',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                        },
                        {
                            label: 'Returned',
                            data: activityReturned,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.14)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 3,
                            pointRadius(context) {
                                return hasActivityData && Number(context.raw) > 0 ? 4 : 0;
                            },
                            pointHoverRadius(context) {
                                return hasActivityData && Number(context.raw) > 0 ? 6 : 0;
                            },
                            pointBackgroundColor: '#10b981',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                        },
                    ],
                },
                options: {
                    ...commonOptions,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        ...commonOptions.plugins,
                        tooltip: {
                            position: 'cursor',
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label(context) {
                                    return `${context.dataset.label}: ${context.formattedValue}`;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                maxTicksLimit: 6,
                                color: theme.textSecondary,
                                font: {
                                    size: 11,
                                },
                            },
                            grid: {
                                color: theme.gridColor,
                            },
                            border: {
                                display: false,
                            },
                        },
                        x: {
                            ticks: {
                                color: theme.textSecondary,
                                autoSkip: true,
                                maxTicksLimit: Math.min(10, Math.max(1, activityLabels.length)),
                                maxRotation: 0,
                                font: {
                                    size: 11,
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

            requestStatusChartInstance = new Chart(requestStatusCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: hasRequestStatusData ? requestStatusLabels : ['No requests yet'],
                    datasets: [{
                        data: hasRequestStatusData ? requestStatusData : [1],
                        backgroundColor: hasRequestStatusData ? requestStatusColors : ['#cbd5e1'],
                        borderWidth: 2,
                        borderColor: theme.cardColor,
                    }],
                },
                options: {
                    ...commonOptions,
                    cutout: '65%',
                    plugins: {
                        ...commonOptions.plugins,
                        tooltip: {
                            position: 'cursor',
                            callbacks: {
                                label(context) {
                                    if (!hasRequestStatusData) {
                                        return 'No requests yet';
                                    }

                                    return `${context.label}: ${context.formattedValue}`;
                                },
                            },
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: theme.textSecondary,
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 11,
                                },
                            },
                        },
                    },
                },
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function buildNotificationUrl(template, notificationId) {
            return String(template || '')
                .replace('__ID__', String(notificationId))
                .replace(':id', String(notificationId))
                .replace('%3Aid', String(notificationId));
        }

        const dashboardListStates = {
            issuedBooks: {
                data: @json($issuedBookItems),
                batchSize: 10,
                visibleCount: 0,
                containerId: 'issuedBooksList',
                emptyHtml: '<p class="empty-state">No books currently issued.</p>',
                renderItem(item) {
                    return `
                        <div class="data-item ${item.is_overdue ? 'overdue' : ''}">
                            <div class="data-item-title">${escapeHtml(item.title)}</div>
                            <div class="data-item-meta">Issued: ${escapeHtml(item.issue_date)}</div>
                            <div class="data-item-meta">
                                Due: ${escapeHtml(item.due_date)}
                                ${item.due_badge_text ? `<span class="alert-badge ${escapeHtml(item.due_badge_class)}">${escapeHtml(item.due_badge_text)}</span>` : ''}
                            </div>
                            ${item.fine_amount ? `<div class="data-item-meta">Fine: ₹${escapeHtml(item.fine_amount)}</div>` : ''}
                        </div>
                    `;
                },
            },
            dueSoon: {
                data: @json($dueSoonItems),
                batchSize: 10,
                visibleCount: 0,
                containerId: 'dueSoonList',
                emptyHtml: '<p class="empty-state">No books due soon. Great job!</p>',
                renderItem(item) {
                    return `
                        <div class="alert-item ${escapeHtml(item.alert_class)}">
                            <div class="alert-item-title">${escapeHtml(item.title)}</div>
                            <div class="alert-item-meta">
                                Due: ${escapeHtml(item.due_date)}
                                <span class="alert-badge ${escapeHtml(item.badge_class)}">${escapeHtml(item.badge_text)}</span>
                            </div>
                        </div>
                    `;
                },
            },
            notifications: {
                data: @json($notificationItems),
                batchSize: 10,
                visibleCount: 0,
                containerId: 'notificationsList',
                emptyHtml: '<p class="empty-state">No notifications yet.</p>',
                renderItem(item) {
                    return `
                        <div class="data-item notification-card ${item.is_unread ? 'notification-card-unread' : ''}" data-notification-id="${item.id}">
                            <div class="notification-card-header">
                                <div class="notification-card-body">
                                    <div class="data-item-title">${escapeHtml(item.title)}</div>
                                    ${item.message ? `<div class="data-item-meta">${escapeHtml(item.message)}</div>` : ''}
                                    <div class="data-item-meta">${escapeHtml(item.time)}</div>
                                </div>
                                <button type="button" class="dashboard-notification-delete-btn" data-notification-delete="${item.id}" aria-label="Delete notification" title="Delete notification">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18"></path>
                                        <path d="M8 6V4h8v2"></path>
                                        <path d="M19 6l-1 14H6L5 6"></path>
                                        <path d="M10 11v6M14 11v6"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                },
            },
        };

        function renderDashboardList(listKey) {
            const state = dashboardListStates[listKey];
            const container = document.getElementById(state.containerId);

            if (!state || !container) {
                return;
            }

            if (state.data.length === 0) {
                container.innerHTML = state.emptyHtml;
                return;
            }

            state.visibleCount = Math.min(
                Math.max(state.visibleCount || state.batchSize, state.batchSize),
                state.data.length
            );

            container.innerHTML = state.data
                .slice(0, state.visibleCount)
                .map(item => state.renderItem(item))
                .join('')
                + (state.visibleCount < state.data.length
                    ? `
                        <div class="dashboard-show-more-item">
                            <button type="button" class="dashboard-show-more" data-list-key="${listKey}">
                                <span>Show More</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="m6 9 6 6 6-6"></path>
                                </svg>
                            </button>
                        </div>
                    `
                    : '');
        }

        function initializeDashboardLists() {
            Object.keys(dashboardListStates).forEach(listKey => {
                const state = dashboardListStates[listKey];
                state.visibleCount = Math.min(state.batchSize, state.data.length);
                renderDashboardList(listKey);
            });
        }

        function showMoreDashboardItems(listKey) {
            const state = dashboardListStates[listKey];

            if (!state) {
                return;
            }

            state.visibleCount = Math.min(state.data.length, state.visibleCount + state.batchSize);
            renderDashboardList(listKey);
        }

        function updateNotificationCount(count) {
            const countElement = document.getElementById('notificationsCount');

            if (countElement) {
                countElement.textContent = count;
            }
        }

        function adjustGlobalNotificationBadge(delta) {
            const badge = document.getElementById('notificationBadge');

            if (!badge) {
                return;
            }

            const currentCount = parseInt(badge.textContent || '0', 10) || 0;
            const nextCount = Math.max(0, currentCount + delta);

            badge.textContent = String(nextCount);
            badge.style.display = nextCount > 0 ? 'inline-flex' : 'none';
        }

        async function deleteDashboardNotification(notificationId, button) {
            const state = dashboardListStates.notifications;
            const notification = state.data.find(item => item.id === notificationId);

            if (!state || !notification) {
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(buildNotificationUrl(window.notificationAPI.delete, notificationId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || data.success === false) {
                    throw new Error(data.message || `Unable to delete notification (HTTP ${response.status})`);
                }

                state.data = state.data.filter(item => item.id !== notificationId);
                state.visibleCount = Math.min(
                    Math.max(state.visibleCount, Math.min(state.batchSize, state.data.length)),
                    state.data.length
                );

                renderDashboardList('notifications');
                updateNotificationCount(state.data.length);

                if (notification.is_unread) {
                    adjustGlobalNotificationBadge(-1);
                }
            } catch (error) {
                console.error('Failed to delete dashboard notification', error);
                button.disabled = false;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
            renderCharts();
            initializeDashboardLists();

            document.addEventListener('click', function(event) {
                const showMoreButton = event.target.closest('[data-list-key]');
                if (showMoreButton) {
                    showMoreDashboardItems(showMoreButton.dataset.listKey);
                    return;
                }

                const deleteButton = event.target.closest('[data-notification-delete]');
                if (deleteButton) {
                    deleteDashboardNotification(Number(deleteButton.dataset.notificationDelete), deleteButton);
                }
            });

            const themeObserver = new MutationObserver(() => {
                renderCharts();
            });

            themeObserver.observe(document.body, {
                attributes: true,
                attributeFilter: ['class'],
            });

            prefersDarkScheme.addEventListener('change', () => {
                if (
                    !localStorage.getItem('theme') &&
                    !document.body.classList.contains('light-theme') &&
                    !document.body.classList.contains('dark-theme')
                ) {
                    initTheme();
                }
            });
        });
    </script>
@endpush
