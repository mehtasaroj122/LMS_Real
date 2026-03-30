{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Library Management</title>
    <link rel="stylesheet" href="{{ asset('staff/CSS/staff-appLayout.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="light-theme">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="flex h-screen overflow-hidden">
    <aside class="flex flex-col w-64 border-r sidebar shrink-0" id="sidebar">
        <div class="flex items-center justify-between p-4 logo-section">
            <button class="close-sidebar-btn" id="closeSidebarBtn" aria-label="Close menu">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
            <div class="flex items-center space-x-2">
                <div class="p-2 bg-blue-600 rounded-lg"><i data-lucide="book-open" class="w-5 h-5 text-white"></i></div>
                <div>
                    <h1 class="text-sm font-bold leading-none text-primary">Library Management</h1>
                    <p class="text-xs text-secondary">Staff Portal</p> {{-- Changed from Admin Portal --}}
                </div>
            </div>
        </div>
        <nav class="flex-1 px-2 py-2 space-y-0.5 overflow-y-auto">
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
    </aside>

    <main class="flex flex-col flex-1 min-w-0 overflow-hidden">
        <header class="flex items-center justify-between px-3 border-b h-14 header md:px-4 shrink-0">
            <div class="flex items-center space-x-2">
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center space-x-2 md:hidden">
                    <div class="p-1.5 bg-blue-600 rounded-lg"><i data-lucide="book-open" class="w-4 h-4 text-white"></i></div>
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
                <button class="text-sm font-medium text-blue-600 hover:text-blue-700" id="markAllReadBtn" style="margin-right: auto; margin-left: 12px;">
                    Mark all as read
                </button>
                <button class="text-sm font-medium text-red-600 hover:text-red-700" id="deleteAllBtn" style="margin-right: 8px;" title="Delete all notifications">
                    Clear all
                </button>
                <button class="notification-close-btn" id="notificationCloseBtn" aria-label="Close notifications">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="notification-body" id="notificationBody">
                <!-- Notifications will be loaded dynamically here -->
            </div>
        </div>

        <div class="flex-1 p-3 overflow-y-auto md:p-2">
            @yield('content')
        </div>
    </main>
</div>

<script>
    window.notificationAPI = {
        index: '{{ route("staff.notifications.index") }}',
        unreadCount: '{{ route("staff.notifications.unread-count") }}',
        markRead: '{{ route("staff.notifications.mark-read", ":id") }}',
        markAllRead: '{{ route("staff.notifications.mark-all-read") }}',
        delete: '{{ route("staff.notifications.destroy", ":id") }}',
        deleteAll: '{{ route("staff.notifications.delete-all-read") }}'
    };
</script>

<script src="{{ asset('staff/JS/staff-appLayout.js') }}"></script>

@stack('scripts')
</body>
</html>
