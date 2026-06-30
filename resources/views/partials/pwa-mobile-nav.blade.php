@php
    $role = $role ?? 'student';

    $itemsByRole = [
        'student' => [
            ['label' => 'Home', 'icon' => 'layout-dashboard', 'route' => 'student.dashboard', 'active' => 'student.dashboard'],
            ['label' => 'Search', 'icon' => 'search', 'route' => 'student.search', 'active' => 'student.search'],
            ['label' => 'Books', 'icon' => 'book-open', 'route' => 'student.my-books', 'active' => 'student.my-books'],
            ['label' => 'Requests', 'icon' => 'clipboard-list', 'route' => 'student.requests', 'active' => 'student.requests'],
            ['label' => 'Profile', 'icon' => 'user', 'route' => 'student.profile', 'active' => 'student.profile*'],
        ],
        'staff' => [
            ['label' => 'Home', 'icon' => 'layout-dashboard', 'route' => 'staff.dashboard', 'active' => 'staff.dashboard'],
            ['label' => 'Books', 'icon' => 'book-open', 'route' => 'staff.book-management.index', 'active' => 'staff.book-management.*'],
            ['label' => 'Issue', 'icon' => 'arrow-up-circle', 'route' => 'staff.issue-book.index', 'active' => 'staff.issue-book.*'],
            ['label' => 'Requests', 'icon' => 'clipboard-list', 'route' => 'staff.book-requests.index', 'active' => 'staff.book-requests.*'],
            ['label' => 'Settings', 'icon' => 'settings', 'route' => 'staff.settings.index', 'active' => 'staff.settings.*'],
        ],
        'admin' => [
            ['label' => 'Home', 'icon' => 'layout-dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
            ['label' => 'Books', 'icon' => 'book-open', 'route' => 'admin.books.index', 'active' => 'admin.books.*'],
            ['label' => 'Users', 'icon' => 'users', 'route' => 'admin.users.index', 'active' => 'admin.users.*'],
            ['label' => 'Requests', 'icon' => 'clipboard-list', 'route' => 'admin.book-requests.index', 'active' => 'admin.book-requests.*'],
            ['label' => 'Settings', 'icon' => 'settings', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
        ],
    ];

    $items = $itemsByRole[$role] ?? $itemsByRole['student'];
@endphp

<nav class="pwa-bottom-nav" style="--pwa-nav-count: {{ count($items) }}" aria-label="{{ ucfirst($role) }} mobile navigation">
    @foreach ($items as $item)
        <a
            href="{{ route($item['route']) }}"
            class="pwa-bottom-nav__item {{ request()->routeIs($item['active']) ? 'is-active' : '' }}"
            @if(request()->routeIs($item['active'])) aria-current="page" @endif
        >
            <i data-lucide="{{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
