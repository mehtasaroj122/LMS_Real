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

        .container {
            min-height: 100vh;
            padding: 0.75rem;
        }

        @media (min-width: 768px) {
            .container {
                padding: 0;
            }
        }

        /* ================== */
        /* HEADER SECTION */
        /* ================== */
        .header-section {
            background: var(--bg-gradient);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 1rem;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        @media (min-width: 768px) {
            .header-content {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .header-text h1 {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        @media (min-width: 768px) {
            .header-text h1 {
                font-size: 1.5rem;
            }
        }

        .header-text .subtitle {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.125rem;
        }

        .header-text .student-id {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .profile-avatar {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid white;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .profile-card-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.65rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
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
            font-size: 0.75rem;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
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
            margin-bottom: 1rem;
        }

        .chart-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .chart-container {
            position: relative;
            height: 250px;
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

        .data-card-header h3 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
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

        .data-card-list {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.125rem;
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
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="profile-card">
            <div class="profile-card-content">
                <div class="profile-card-avatar">
                    <img src="{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset('storage/' . $user->profile_photo)) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=fff&color=667eea' }}" alt="Profile">
                </div>
                <div class="profile-card-info">
                    <h1>{{ $user->name }}</h1>
                    <div class="profile-card-meta">
                        <span>🎓 {{ $student?->roll_no ?? 'N/A' }}</span>
                        <span>📚 {{ $department?->name ?? 'N/A' }}</span>
                        <span>📅 Year {{ $yearOfStudy }}</span>
                        <span>📧 {{ $user->email }}</span>
                    </div>
                </div>
            </div>

            <div class="profile-card-stats">
                <div class="profile-stat">
                    <div class="profile-stat-value">{{ $booksIssuedCount }}</div>
                    <div class="profile-stat-label">📚 Issued</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value">{{ $booksReturnedCount }}</div>
                    <div class="profile-stat-label">✅ Returned</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value">₹{{ $pendingFines }}</div>
                    <div class="profile-stat-label">₹ Fines</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-value">{{ $activeRequestsCount }}</div>
                    <div class="profile-stat-label">⏳ Requests</div>
                </div>
            </div>
        </div>

        <div class="charts-row">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>📊 Monthly Activity</h3>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-header">
                    <h3>📚 Reading by Category</h3>
                </div>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="tables-row">
            <div class="data-card">
                <div class="data-card-header">
                    <h3>📖 Currently Issued</h3>
                    <span class="data-card-count">{{ $booksIssuedCount }}</span>
                </div>
                <div class="data-card-list">
                    @forelse($issuedBooks as $issuedBook)
                        @php
                            $daysUntilDue = now()->startOfDay()->diffInDays($issuedBook->due_date->copy()->startOfDay(), false);
                        @endphp
                        <div class="data-item {{ $daysUntilDue < 0 ? 'overdue' : '' }}">
                            <div class="data-item-title">{{ $issuedBook->book->title }}</div>
                            <div class="data-item-meta">Issued: {{ $issuedBook->issue_date->format('M d, Y') }}</div>
                            <div class="data-item-meta">
                                Due: {{ $issuedBook->due_date->format('M d, Y') }}
                                @if($daysUntilDue < 0)
                                    <span class="alert-badge today">OVERDUE</span>
                                @elseif($daysUntilDue <= 3)
                                    <span class="alert-badge tomorrow">DUE SOON</span>
                                @endif
                            </div>
                            @if($issuedBook->fine)
                                <div class="data-item-meta">Fine: ₹{{ $issuedBook->fine->amount }}</div>
                            @endif
                        </div>
                    @empty
                        <p class="empty-state">No books currently issued.</p>
                    @endforelse
                </div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <h3>⏰ Due Soon</h3>
                    <span class="data-card-count">{{ $dueSoon->count() }}</span>
                </div>
                <div class="data-card-list">
                    @forelse($dueSoon as $book)
                        @php
                            $daysLeft = now()->startOfDay()->diffInDays($book->due_date->copy()->startOfDay(), false);
                            $alertClass = $daysLeft === 0 ? 'danger' : ($daysLeft === 1 ? 'warning' : '');
                            $badgeClass = $daysLeft === 0 ? 'today' : ($daysLeft === 1 ? 'tomorrow' : 'soon');
                            $badgeText = $daysLeft === 0 ? 'TODAY' : ($daysLeft === 1 ? 'TOMORROW' : $daysLeft . ' DAYS');
                        @endphp
                        <div class="alert-item {{ $alertClass }}">
                            <div class="alert-item-title">{{ $book->book->title }}</div>
                            <div class="alert-item-meta">
                                Due: {{ $book->due_date->format('M d, Y') }}
                                <span class="alert-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="empty-state">No books due soon. Great job!</p>
                    @endforelse
                </div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <h3>🔔 Notifications</h3>
                    <span class="data-card-count">{{ $notifications->count() }}</span>
                </div>
                <div class="data-card-list">
                    @forelse($notifications as $notification)
                        <div class="data-item">
                            <div class="data-item-title">{{ $notification->title ?? 'Notification' }}</div>
                            @if(!empty($notification->message))
                                <div class="data-item-meta">{{ $notification->message }}</div>
                            @endif
                            <div class="data-item-meta">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <p class="empty-state">No new notifications.</p>
                    @endforelse
                </div>
            </div>
        </div>

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
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
        let activityChart;
        let categoryChart;

        function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && prefersDarkScheme.matches)) {
                document.body.classList.add('dark-theme');
            } else {
                document.body.classList.remove('dark-theme');
            }
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
            const categoryCanvas = document.getElementById('categoryChart');

            if (!activityCanvas || !categoryCanvas) {
                return;
            }

            const theme = getChartTheme();
            const activityLabels = @json($monthlyActivity['labels'] ?? []);
            const activityIssued = @json($monthlyActivity['issued'] ?? []);
            const activityReturned = @json($monthlyActivity['returned'] ?? []);
            const categoryLabels = @json($readingCategories['labels'] ?? []);
            const categoryData = @json($readingCategories['data'] ?? []);
            const categoryColors = @json($readingCategories['colors'] ?? []);
            const hasCategoryData = categoryData.length > 0;

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

            if (categoryChart) {
                categoryChart.destroy();
            }

            activityChart = new Chart(activityCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: activityLabels,
                    datasets: [
                        {
                            label: 'Issued',
                            data: activityIssued,
                            backgroundColor: '#3b82f6',
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                        {
                            label: 'Returned',
                            data: activityReturned,
                            backgroundColor: '#10b981',
                            borderRadius: 4,
                            borderSkipped: false,
                        },
                    ],
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
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

            categoryChart = new Chart(categoryCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: hasCategoryData ? categoryLabels : ['No returned books yet'],
                    datasets: [{
                        data: hasCategoryData ? categoryData : [1],
                        backgroundColor: hasCategoryData ? categoryColors : ['#cbd5e1'],
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
                            callbacks: {
                                label(context) {
                                    if (!hasCategoryData) {
                                        return 'No returned books yet';
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

        document.addEventListener('DOMContentLoaded', function() {
            initTheme();
            renderCharts();

            const themeObserver = new MutationObserver(() => {
                renderCharts();
            });

            themeObserver.observe(document.body, {
                attributes: true,
                attributeFilter: ['class'],
            });

            prefersDarkScheme.addEventListener('change', () => {
                if (!localStorage.getItem('theme')) {
                    initTheme();
                }
            });
        });
    </script>
@endpush
