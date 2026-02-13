<!--
    Role Badge Component
    
    Props:
    - $role: string - Role value (admin, staff, student, user)
    - $label: string - Custom label (optional)
    - $icon: string - Font Awesome icon (optional)
-->

@props([
    'role' => 'user',
    'label' => null,
    'icon' => null,
])

@php
    $roleConfig = [
        'admin' => [
            'icon' => 'fa-shield-alt',
            'label' => 'Admin',
        ],
        'staff' => [
            'icon' => 'fa-user-tie',
            'label' => 'Staff',
        ],
        'student' => [
            'icon' => 'fa-graduation-cap',
            'label' => 'Student',
        ],
        'user' => [
            'icon' => 'fa-user',
            'label' => 'User',
        ],
    ];

    $config = $roleConfig[$role] ?? $roleConfig['user'];
    $finalLabel = $label ?? $config['label'];
    $finalIcon = $icon ?? $config['icon'];
@endphp

<span class="admin-role-badge {{ $role }}">
    <i class="fas {{ $finalIcon }}"></i>
    {{ $finalLabel }}
</span>
