{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Library Management</title>
    <link rel="stylesheet" href="{{ asset('staff/CSS/staff-appLayout.css') }}">
    <link rel="stylesheet" href="{{ asset('shared/CSS/notification-list-animations.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .admin-table-entries-control {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: nowrap;
            font-size: 0.78rem;
            font-weight: 500;
            color: #64748b;
            white-space: nowrap;
        }

        body.dark-theme .admin-table-entries-control {
            color: #94a3b8;
        }

        .admin-table-entries-select {
            min-width: 86px;
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border-radius: 0.65rem;
            border: 1px solid #dbe2ea;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.7rem center;
            color: #0f172a;
            font-size: 0.78rem;
            font-weight: 600;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        body.dark-theme .admin-table-entries-select {
            border-color: #475569;
            background: #1e293b url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 0.7rem center;
            color: #f8fafc;
        }

        .admin-table-entries-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        }

        body.dark-theme .admin-table-entries-select:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.18);
        }

        .admin-table-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
            background: #ffffff;
        }

        body.dark-theme .admin-table-pagination {
            border-top-color: #334155;
            background: #1e293b;
        }

        .admin-table-pagination-meta {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex-wrap: wrap;
            min-width: 0;
        }

        .admin-table-pagination-summary,
        .admin-table-pagination-page {
            font-size: 0.78rem;
            line-height: 1.5;
            color: #64748b;
        }

        body.dark-theme .admin-table-pagination-summary,
        body.dark-theme .admin-table-pagination-page {
            color: #94a3b8;
        }

        .admin-table-pagination-page {
            font-weight: 600;
            color: #475569;
        }

        body.dark-theme .admin-table-pagination-page {
            color: #cbd5e1;
        }

        .admin-table-pagination-nav {
            display: flex;
            align-items: center;
            gap: 0.38rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .admin-table-pagination-link,
        .admin-table-pagination-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.4rem;
            min-height: 2.4rem;
            padding: 0.45rem 0.78rem;
            border-radius: 0.75rem;
            font-size: 0.78rem;
            font-weight: 600;
            line-height: 1;
        }

        .admin-table-pagination-link {
            border: 1px solid #dbe2ea;
            background: #f8fafc;
            color: #334155;
            text-decoration: none;
            transition: border-color 0.2s ease, background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        button.admin-table-pagination-link {
            cursor: pointer;
        }

        body.dark-theme .admin-table-pagination-link {
            border-color: #475569;
            background: #0f172a;
            color: #e2e8f0;
        }

        .admin-table-pagination-link:hover {
            border-color: #3b82f6;
            color: #2563eb;
            background: #eff6ff;
            transform: translateY(-1px);
        }

        body.dark-theme .admin-table-pagination-link:hover {
            border-color: #60a5fa;
            color: #bfdbfe;
            background: #1e3a8a;
        }

        .admin-table-pagination-link.is-active {
            border-color: #3b82f6;
            background: #3b82f6;
            color: #ffffff;
            pointer-events: none;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.18);
        }

        body.dark-theme .admin-table-pagination-link.is-active {
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.24);
        }

        .admin-table-pagination-link.is-disabled {
            opacity: 0.55;
            pointer-events: none;
        }

        .admin-table-pagination-ellipsis {
            color: #94a3b8;
        }

        body.dark-theme .admin-table-pagination-ellipsis {
            color: #64748b;
        }

        @media (max-width: 768px) {
            .admin-table-pagination {
                align-items: stretch;
            }

            .admin-table-pagination-nav {
                justify-content: flex-start;
            }
        }
    </style>

    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="light-theme">
@include('shared.library-branding.bootstrap')
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="flex h-screen min-h-0 overflow-hidden">
    <aside class="flex min-h-0 flex-col w-64 border-r sidebar shrink-0" id="sidebar">
        <div class="flex items-center justify-between p-4 logo-section">
            <button class="close-sidebar-btn" id="closeSidebarBtn" aria-label="Close menu">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            <div class="flex items-center space-x-2">
                <x-logo size="md" :lazy="false" />
                <div>
                    <h1 class="text-sm font-bold leading-none text-primary">Library Management</h1>
                    <p class="text-xs text-secondary">Staff Portal</p> {{-- Changed from Admin Portal --}}
                </div>
            </div>
        </div>
        <nav class="flex-1 min-h-0 px-2 py-2 space-y-0.5 overflow-y-auto">
            {{-- Dashboard --}}
            <a href="{{ route('staff.dashboard') }}" class="sidebar-item {{ request()->routeIs('staff.dashboard') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span class="text-sm font-medium">Dashboard</span>
            </a>
            
            {{-- Book Management --}}
            <a href="{{ route('staff.book-management.index') }}" class="sidebar-item {{ request()->routeIs('staff.book-management.*') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="book" class="w-5 h-5"></i><span class="text-sm font-medium">Book Management</span>
            </a>
            
            {{-- Issue Book --}}
            <a href="{{ route('staff.issue-book.index') }}" class="sidebar-item {{ request()->routeIs('staff.issue-book.*') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="arrow-up-circle" class="w-5 h-5"></i><span class="text-sm font-medium">Issue Book</span>
            </a>
            
            {{-- Return Book --}}
            <a href="{{ route('staff.return-book.index') }}" class="sidebar-item {{ request()->routeIs('staff.return-book.*') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="arrow-down-circle" class="w-5 h-5"></i><span class="text-sm font-medium">Return Book</span>
            </a>
            
            {{-- Fines --}}
            <a href="{{ route('staff.fines.index') }}" class="sidebar-item {{ request()->routeIs('staff.fines.*') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="indian-rupee" class="w-5 h-5"></i><span class="text-sm font-medium">Fines</span>
            </a>
            
            {{-- Book Requests --}}
            <a href="{{ route('staff.book-requests.index') }}" class="sidebar-item {{ request()->routeIs('staff.book-requests.*') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i><span class="text-sm font-medium">Book Requests</span>
            </a>
            
            {{-- Students --}}
            <a href="{{ route('staff.students.index') }}" class="sidebar-item {{ request()->routeIs('staff.students.*') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="users" class="w-5 h-5"></i><span class="text-sm font-medium">Students</span>
            </a>
            
            {{-- Setting --}}
            <a href="{{ route('staff.settings.index') }}" class="sidebar-item {{ request()->routeIs('staff.settings.*') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                <i data-lucide="settings" class="w-5 h-5"></i><span class="text-sm font-medium">Setting</span>
            </a>
        </nav>
        <x-sidebar-profile :profile-url="route('staff.settings.index')" />
    </aside>

    <main class="flex min-h-0 flex-col flex-1 min-w-0 overflow-hidden">
        <header class="flex items-center justify-between px-3 border-b h-14 header md:px-4 shrink-0">
            <div class="flex items-center space-x-2">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center space-x-2 md:hidden">
                    <x-logo size="sm" :lazy="false" />
                    <div>
                        <h1 class="text-sm font-semibold leading-none text-primary">Library Management</h1>
                        <p class="text-xs text-secondary">Staff Portal</p> {{-- Changed from Admin Portal --}}
                    </div>
                </div>
            </div>

            <div class="flex items-center ml-auto space-x-2 md:space-x-3">
                <button class="notification-btn" id="notificationBtn" aria-label="Notifications">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                </button>

                <div class="theme-toggle" id="themeToggle">
                    <div class="theme-toggle-slider">
                        <i data-lucide="sun" class="w-4 h-4 text-yellow-500" id="sunIcon"></i>
                        <i data-lucide="moon" class="w-4 h-4 text-blue-400" id="moonIcon" style="display: none;"></i>
                    </div>
                </div>

                <a href="{{ route('staff.settings.index') }}" class="profile-link">
                    <div class="hidden text-right md:block">
                        <p class="text-sm font-semibold leading-tight text-primary">{{ Auth::user()->name ?? 'Staff' }}</p>
                        <p class="text-xs text-secondary">{{ Auth::user()->email ?? 'staff@library.edu' }}</p>
                    </div>
                    @if(Auth::user()->profile_photo)
                        <img src="{{ str_starts_with(Auth::user()->profile_photo, 'http') ? Auth::user()->profile_photo : asset(Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="object-cover w-8 h-8 bg-blue-600 rounded-full md:w-10 md:h-10">
                    @else
                        <div class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white bg-blue-600 rounded-full md:w-10 md:h-10 md:text-base">
                            {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                </a>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="logout-btn hidden md:flex items-center space-x-2 px-3 py-1.5 border rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i><span>Logout</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="inline md:hidden">
                    @csrf
                    <button type="submit" class="flex items-center justify-center w-8 h-8 transition-colors border rounded-lg logout-btn">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </header>

        <div class="notification-popup" id="notificationPopup">
            <div class="notification-header">
                <h3>Notifications</h3>
                <div class="notification-header-actions">
                    <button type="button" class="notification-header-link notification-header-link-primary" id="markAllReadBtn">
                        Mark all as read
                    </button>
                    <button type="button" class="notification-header-link notification-header-link-danger" id="deleteAllBtn" title="Delete all read notifications">
                        Clear all
                    </button>
                </div>
                <button class="notification-close-btn" id="notificationCloseBtn" aria-label="Close notifications">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="notification-body" id="notificationBody" aria-live="polite">
                <!-- Notifications will be loaded dynamically here -->
            </div>
        </div>

        <div id="notificationDetailModal" class="notification-detail-modal" aria-hidden="true">
            <div class="notification-detail-panel" role="dialog" aria-modal="true" aria-labelledby="notificationDetailModalTitle" aria-describedby="notificationDetailModalBody">
                <div class="notification-detail-header">
                    <h3 id="notificationDetailModalTitle">Notification Details</h3>
                    <button type="button" class="notification-detail-close-btn" data-notification-detail-close aria-label="Close notification details">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="notification-detail-body" id="notificationDetailModalBody">
                    <div class="notification-detail-loading">
                        <span class="notification-spinner" aria-hidden="true"></span>
                        <p>Loading notification details...</p>
                    </div>
                </div>
                <div class="notification-detail-footer">
                    <button type="button" class="notification-detail-btn" data-notification-detail-close>Cancel</button>
                    <button type="button" class="notification-detail-btn primary" id="notificationDetailOkBtn">OK</button>
                </div>
            </div>
        </div>

        <div id="notificationClearConfirmModal" class="notification-detail-modal notification-detail-modal-danger" aria-hidden="true">
            <div class="notification-detail-panel" role="dialog" aria-modal="true" aria-labelledby="notificationClearConfirmTitle" aria-describedby="notificationClearConfirmMessage">
                <div class="notification-detail-header">
                    <h3 id="notificationClearConfirmTitle">Clear Read Notifications</h3>
                    <button type="button" class="notification-detail-close-btn" data-notification-clear-close aria-label="Close clear notifications confirmation">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="notification-detail-body">
                    <div class="notification-detail-hero">
                        <div class="notification-detail-hero-icon" aria-hidden="true">
                            <i data-lucide="trash-2" class="w-6 h-6"></i>
                        </div>
                        <div class="notification-detail-hero-copy">
                            <span class="notification-detail-overline">Confirmation</span>
                            <h4 class="notification-detail-hero-title" id="notificationClearConfirmHeading">Remove read notifications</h4>
                            <p id="notificationClearConfirmMessage">Clear every read notification from the list? Unread notifications will stay in place.</p>
                        </div>
                    </div>
                </div>
                <div class="notification-detail-footer">
                    <button type="button" class="notification-detail-btn" data-notification-clear-close>Cancel</button>
                    <button type="button" class="notification-detail-btn primary" id="notificationClearConfirmOkBtn">Clear all</button>
                </div>
            </div>
        </div>

        <div class="flex-1 min-h-0 p-3 overflow-y-auto md:p-2">
            <x-dashboard-header class="mb-4" />
            @yield('content')
        </div>
    </main>
</div>

<script>
    window.notificationAPI = {
        index: '{{ route("staff.notifications.index") }}',
        show: '{{ route("staff.notifications.show", "__ID__") }}',
        unreadCount: '{{ route("staff.notifications.unread-count") }}',
        markRead: '{{ route("staff.notifications.mark-read", "__ID__") }}',
        markAllRead: '{{ route("staff.notifications.mark-all-read") }}',
        delete: '{{ route("staff.notifications.destroy", "__ID__") }}',
        deleteAll: '{{ route("staff.notifications.delete-all-read") }}'
    };
</script>

<script src="{{ asset('staff/JS/services/notification-api.js') }}"></script>
<script src="{{ asset('shared/JS/components/notification-list-animator.js') }}"></script>
<script src="{{ asset('staff/JS/components/notification-list.js') }}"></script>
<script src="{{ asset('staff/JS/components/notification-detail-modal.js') }}"></script>
<script src="{{ asset('staff/JS/staff-appLayout.js') }}"></script>

@stack('scripts')
</body>
</html>
