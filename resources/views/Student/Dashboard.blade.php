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
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        @media (min-width: 768px) {
            .actions-grid-inner {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .action-btn {
            text-align: left;
            padding: 0.5rem;
            border: 1px solid;
            border-radius: 0.375rem;
            transition: all 0.2s ease;
            cursor: pointer;
            background-color: transparent;
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
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-content">
                <div class="header-text">
                    <h1>Welcome back, {{ $user->name }}!</h1>
                    <p class="subtitle">{{ $department->name ?? '' }} Department</p>
                    <p class="student-id">Student ID: {{ $student ? $student->roll_no : 'N/A' }}</p>
                </div>
                <div class="profile-avatar">
                    <img src="{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset('storage/' . $user->profile_photo)) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=7c3aed&color=fff' }}" alt="Profile">
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card issued">
                <div class="stat-header">
                    <h3 class="stat-title">Books Issued</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-number">{{ $booksIssuedCount }}</div>
                <div class="stat-label">Total books issued</div>

            </div>

            <div class="stat-card returned">
                <div class="stat-header">
                    <h3 class="stat-title">Books Returned</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12 11 14 15 10"></path>
                            <path d="M12 3a12 12 0 0 0 8.5 3A12 12 0 0 1 12 21 12 12 0 0 1 3.5 6 12 12 0 0 0 12 3"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-number">{{ $booksReturnedCount }}</div>
                <div class="stat-label">Total books returned</div>
            </div>

            <div class="stat-card fines">
                <div class="stat-header">
                    <h3 class="stat-title">Pending Fines</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="currentColor">
                            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="22" font-weight="bold" fill="currentColor">₹</text>
                        </svg>
                    </div>
                </div>
                <div class="stat-number">₹{{ $pendingFines }}</div>
                <div class="stat-label">Total pending fines</div>
            </div>

            <div class="stat-card requests">
                <div class="stat-header">
                    <h3 class="stat-title">Active Requests</h3>
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                </div>
                <div class="stat-number">{{ $activeRequestsCount }}</div>
                <div class="stat-label">Total pending Request</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="main-grid">
            <!-- Currently Issued Books -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Currently Issued Books</h2>
                    <a href="{{ route('student.my-books') }}" class="section-link view-all-link">
                        View All →
                    </a>
                </div>
                <div class="section-body">
                    @forelse($issuedBooks as $issuedBook)
                        <div class="book-item {{ $issuedBook->due_date < now() ? 'overdue' : '' }}">
                            <div class="book-info">
                                <h3>{{ $issuedBook->book->title }}</h3>
                                <div class="book-details">
                                    <p>Issued: <span class="detail-value">{{ $issuedBook->issue_date->format('M d, Y') }}</span></p>
                                    <p>Due: <span class="detail-value">{{ $issuedBook->due_date->format('M d, Y') }}</span></p>
                                </div>
                            </div>
                            <div class="book-status">
                                @if($issuedBook->due_date < now())
                                    <span class="badge badge-danger">Overdue</span>
                                    @if($issuedBook->fine)
                                        <p class="fine-amount">Fine: ${{ $issuedBook->fine->amount }}</p>
                                    @endif
                                @else
                                    <span class="badge badge-success">On Time</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p>No books currently issued.</p>
                    @endforelse
                </div>
            </div>

            <!-- Notifications Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2>Notifications</h2>
                </div>
                <div class="section-body">
                    @forelse($notifications as $notification)
                        <div class="notification-item">
                            <div class="notification-indicator {{ $notification->status ?? 'blue' }}"></div>
                            <div class="notification-content">
                                <h4>{{ $notification->title }}</h4>
                                <p>{{ $notification->message }}</p>
                            </div>
                        </div>
                    @empty
                        <p>No notifications.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- <!-- Pending Book Requests Section -->
        <div class="section-card" style="margin-bottom: 1rem;">
            <div class="section-header">
                <h2>Pending Book Requests</h2>
                <a href="#" class="section-link view-all-link">
                    View All →
                </a>
            </div>
            <div class="section-body">
                @forelse($pendingRequests as $request)
                    <div class="book-item">
                        <div class="book-info">
                            <h3>{{ $request->book->title }}</h3>
                            <div class="book-details">
                                <p>Author: <span class="detail-value">{{ $request->book->author }}</span></p>
                                <p>Requested: <span class="detail-value">{{ $request->request_date->format('M d, Y') }}</span></p>
                            </div>
                        </div>
                        <div class="book-status">
                            @if($request->status === 'pending')
                                <span class="badge" style="background-color: #fef3c7; color: #92400e; display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 600;">Pending</span>
                            @elseif($request->status === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p>No pending requests.</p>
                @endforelse
            </div>
        </div> --}}

        <!-- Separated Quick Actions and Profile Snapshot -->
        <div class="content-grid">
            <!-- Quick Actions -->
            <div class="quick-actions-section">
                <h2>Quick Actions</h2>
                <div class="actions-grid-inner">
                    <!-- Search Books -->
                    <button class="action-btn action-blue search-books-btn">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3>Search Books</h3>
                                <p>Browse library catalog</p>
                            </div>
                        </div>
                    </button>

                    <!-- View My Books -->
                    <button class="action-btn action-teal view-books-btn">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <h3>View My Books</h3>
                                <p>Check issued books</p>
                            </div>
                        </div>
                    </button>

                    <!-- Pay Fines -->
                    <button class="action-btn action-red pay-fines-btn">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
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
                    </button>

                    <!-- Request Book -->
                    <button class="action-btn action-purple request-book-btn">
                        <div class="btn-content">
                            <div class="btn-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3>Request Book</h3>
                                <p>Reserve a new book</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Profile Snapshot -->
            <div class="profile-section">
                <h2>Profile Snapshot</h2>
                <div class="profile-content">
                    <div class="profile-avatar-large">
                        <img src="{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset('storage/' . $user->profile_photo)) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=7c3aed&color=fff&size=128' }}" alt="Profile">
                    </div>
                    <h3 class="profile-name">{{ $user->name }}</h3>
                    <p class="profile-email">{{ $user->email }}</p>
                    <div class="profile-details">
                        <div class="detail-item">
                            <span class="detail-label">Department:</span>
                            <span class="detail-value">{{ $department->name ?? '' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Student ID:</span>
                            <span class="detail-value">{{ $student ? $student->roll_no : 'N/A' }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Account Status:</span>
                            <span class="detail-value">
                                <span class="status-badge {{ $user->status == 'active' ? 'active' : '' }}">{{ ucfirst($user->status) }}</span>
                            </span>
                        </div>
                    </div>
                    <button class="profile-btn view-profile-btn">
                        View Full Profile
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Theme initialization
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

        function initTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && prefersDarkScheme.matches)) {
                document.body.classList.add('dark-theme');
            } else {
                document.body.classList.remove('dark-theme');
            }
        }

        // Toast Notification Function
        function showToast(message) {
            const existingToast = document.querySelector('.toast');
            if (existingToast) {
                existingToast.classList.add('hide');
                setTimeout(() => existingToast.remove(), 300);
            }

            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `
            <div class="toast-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span>${message}</span>
        `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('hide');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            initTheme();

            // Quick actions button click handlers
            document.querySelector('.search-books-btn').addEventListener('click', () => {
                window.location.href = '{{ route("student.search") }}';
            });

            document.querySelector('.view-books-btn').addEventListener('click', () => {
                window.location.href = '{{ route("student.my-books") }}';
            });

            document.querySelector('.pay-fines-btn').addEventListener('click', () => {
                window.location.href = '{{ route("student.fines") }}';
            });

            document.querySelector('.request-book-btn').addEventListener('click', () => {
                window.location.href = '{{ route("student.requests") }}';
            });

            document.querySelector('.view-profile-btn').addEventListener('click', () => {
                window.location.href = '{{ route("student.profile") }}';
            });

            // Listen for system theme changes
            prefersDarkScheme.addEventListener('change', (e) => {
                if (!localStorage.getItem('theme')) {
                    initTheme();
                }
            });
        });
    </script>
@endpush
