{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>@yield('title', 'Dashboard') - Library Management</title>
    <link rel="stylesheet" href="{{ asset('student/CSS/student-appLayout.css') }}">
    <link rel="stylesheet" href="{{ asset('shared/CSS/notification-list-animations.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('shared.student-portal-pagination.styles')
    @include('shared.action-feedback.styles')
    @stack('styles')
</head>

<body class="light-theme">
    @include('shared.library-branding.bootstrap')
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div class="flex h-screen overflow-hidden">
        <aside class="flex min-h-0 flex-col w-64 border-r sidebar shrink-0" id="sidebar">
            <div class="flex items-center justify-between p-4 logo-section">
                <button class="close-sidebar-btn" id="closeSidebarBtn" aria-label="Close menu">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
                <div class="flex items-center space-x-2">
                    <x-logo size="md" :lazy="false" />
                    <div>
                        <h1 class="text-sm font-bold leading-none text-primary">Library Management</h1>
                        <p class="text-xs text-secondary">Student Portal</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 min-h-0 px-2 py-2 space-y-0.5 overflow-y-auto">
                {{-- Dashboard --}}
                <a href="{{ route('student.dashboard') }}"
                    class="sidebar-item {{ request()->routeIs('student.dashboard') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i><span
                        class="text-sm font-medium">Dashboard</span>
                </a>

                {{-- Search Books --}}
                <a href="{{ route('student.search') }}"
                    class="sidebar-item {{ request()->routeIs('student.search') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="search" class="w-5 h-5"></i><span class="text-sm font-medium">Search Books</span>
                </a>

                {{-- My Books --}}
                <a href="{{ route('student.my-books') }}"
                    class="sidebar-item {{ request()->routeIs('student.my-books') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="book" class="w-5 h-5"></i><span class="text-sm font-medium">My Books</span>
                </a>

                {{-- My Requests --}}
                <a href="{{ route('student.requests') }}"
                    class="sidebar-item {{ request()->routeIs('student.requests') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i><span class="text-sm font-medium">My
                        Requests</span>
                </a>

                {{-- My Fines --}}
                <a href="{{ route('student.fines') }}"
                    class="sidebar-item {{ request()->routeIs('student.fines') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="indian-rupee" class="w-5 h-5"></i><span class="text-sm font-medium">My Fines</span>
                </a>

                {{-- Profile --}}
                <a href="{{ route('student.profile') }}"
                    class="sidebar-item {{ request()->routeIs('student.profile') ? 'sidebar-item-active' : '' }} flex items-center space-x-2 px-3 py-2 rounded-lg transition-colors">
                    <i data-lucide="user" class="w-5 h-5"></i><span class="text-sm font-medium">Profile</span>
                </a>
            </nav>
            <x-sidebar-profile :profile-url="route('student.profile')" />
        </aside>

        <main class="flex flex-col flex-1 min-w-0 overflow-hidden">
            <header class="flex items-center justify-between px-3 border-b h-14 header md:px-4 shrink-0">
                <div class="flex items-center space-x-2">
                    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div class="flex items-center space-x-2 md:hidden">
                        <x-logo size="sm" :lazy="false" />
                        <div>
                            <h1 class="text-sm font-semibold leading-none text-primary">Library Management</h1>
                            <p class="text-xs text-secondary">Student Portal</p>
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
                            <i data-lucide="moon" class="w-4 h-4 text-blue-400" id="moonIcon"
                                style="display: none;"></i>
                        </div>
                    </div>

                    <a href="{{ route('student.profile') }}" class="profile-link">
                        <div class="hidden text-right md:block">
                            <p class="text-sm font-semibold leading-tight text-primary">
                                {{ Auth::user()->name ?? 'Student' }}</p>
                            <p class="text-xs text-secondary">{{ Auth::user()->email ?? 'student@library.edu' }}</p>
                        </div>
                        @php
                            $photoUrl = \App\Support\ProfilePhoto::resolveUrl(Auth::user()->profile_photo);
                        @endphp
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ Auth::user()->name }}"
                                class="object-cover w-8 h-8 bg-blue-600 rounded-full md:w-10 md:h-10">
                        @else
                            <div
                                class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white bg-blue-600 rounded-full md:w-10 md:h-10 md:text-base">
                                {{ strtoupper(substr(Auth::user()->name ?? 'S', 0, 1)) }}
                            </div>
                        @endif
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            class="logout-btn hidden md:flex items-center space-x-2 px-3 py-1.5 border rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i><span>Logout</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="inline md:hidden">
                        @csrf
                        <button type="submit"
                            class="flex items-center justify-center w-8 h-8 transition-colors border rounded-lg logout-btn">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </header>

            <div class="notification-popup" id="notificationPopup">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <div class="notification-header-actions">
                        <button type="button" class="notification-header-link notification-header-link-primary"
                            id="markAllReadBtn">
                            Mark all as read
                        </button>
                        <button type="button" class="notification-header-link notification-header-link-danger"
                            id="deleteAllBtn" title="Delete all read notifications">
                            Clear all
                        </button>
                    </div>
                    <button class="notification-close-btn" id="notificationCloseBtn"
                        aria-label="Close notifications">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="notification-body" id="notificationBody" aria-live="polite">
                    <!-- Notifications will be loaded dynamically here -->
                </div>
            </div>

            <div id="notificationDetailModal" class="notification-detail-modal" aria-hidden="true">
                <div class="notification-detail-panel" role="dialog" aria-modal="true"
                    aria-labelledby="notificationDetailModalTitle" aria-describedby="notificationDetailModalBody">
                    <div class="notification-detail-header">
                        <h3 id="notificationDetailModalTitle">Notification Details</h3>
                        <button type="button" class="notification-detail-close-btn" data-notification-detail-close
                            aria-label="Close notification details">
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
                        <button type="button" class="notification-detail-btn"
                            data-notification-detail-close>Cancel</button>
                        <button type="button" class="notification-detail-btn primary"
                            id="notificationDetailOkBtn">OK</button>
                    </div>
                </div>
            </div>

            <div class="flex-1 p-3 overflow-y-auto md:p-2">
                @if (trim($__env->yieldContent('showDashboardHeader')))
                    <x-dashboard-header class="mb-4" />
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    @include('shared.action-feedback.markup')

    <!-- API URLs for Notifications -->
    <script>
        window.notificationAPI = {
            index: '{{ route('student.notifications.index') }}',
            show: '{{ route('student.notifications.show', '__ID__') }}',
            unreadCount: '{{ route('student.notifications.unread-count') }}',
            markRead: '{{ route('student.notifications.mark-read', '__ID__') }}',
            markAllRead: '{{ route('student.notifications.mark-all-read') }}',
            delete: '{{ route('student.notifications.destroy', '__ID__') }}',
            deleteAll: '{{ route('student.notifications.delete-all-read') }}'
        };
    </script>

    @include('shared.action-feedback.scripts')
    <script>
        (() => {
            const feedback = typeof window.ActionFeedbackUI === 'function' ?
                new window.ActionFeedbackUI() :
                null;

            window.StudentPortalFeedback = feedback;
            window.getStudentPortalFeedback = () => window.StudentPortalFeedback || null;
            window.showStudentPortalToast = (type, title, message, timeout = 4200, detail = '') => {
                const payload = typeof type === 'object' && type !== null ?
                    type :
                    {
                        type,
                        title,
                        message,
                        timeout,
                        detail
                    };
                const portalFeedback = window.getStudentPortalFeedback();

                if (portalFeedback) {
                    portalFeedback.showToast(payload);
                    return;
                }

                const normalizedType = payload.type === 'error' ? 'error' : 'log';
                console[normalizedType](`${payload.title || 'Notice'}: ${payload.message || ''}`);
            };
            window.confirmStudentPortalAction = (options = {}) => {
                const portalFeedback = window.getStudentPortalFeedback();

                if (portalFeedback?.confirm) {
                    return portalFeedback.confirm(options);
                }

                console.warn('Student portal action feedback confirmation is unavailable.', options);
                return Promise.resolve(false);
            };
        })();
    </script>
    <script src="{{ asset('student/JS/services/notification-api.js') }}"></script>
    <script src="{{ asset('shared/JS/components/notification-list-animator.js') }}"></script>
    <script src="{{ asset('student/JS/components/notification-list.js') }}"></script>
    <script src="{{ asset('student/JS/components/notification-detail-modal.js') }}"></script>
    <script src="{{ asset('student/JS/student-appLayout.js') }}"></script>

    @include('shared.student-portal-pagination.scripts')
    @stack('scripts')
</body>

</html>
