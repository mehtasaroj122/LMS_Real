{{--
    Status Badge Component
    
    Props:
    - $status: string - Status value (active, inactive, pending, warning)
    - $label: string - Custom label (optional)
    - $icon: string - Font Awesome icon (optional)
    
    Usage:
    <x-admin-status-badge status="active" />
    <x-admin-status-badge status="inactive" label="Disabled" icon="fa-lock" />
--}}

@props([
    'status' => 'pending',
    'label' => null,
    'icon' => null,
])

@php
    $statusConfig = [
        'active' => [
            'icon' => 'fa-check-circle',
            'label' => 'Active',
        ],
        'inactive' => [
            'icon' => 'fa-times-circle',
            'label' => 'Inactive',
        ],
        'pending' => [
            'icon' => 'fa-hourglass-half',
            'label' => 'Pending',
        ],
        'warning' => [
            'icon' => 'fa-exclamation-circle',
            'label' => 'Warning',
        ],
    ];

    $config = $statusConfig[$status] ?? $statusConfig['pending'];
    $finalLabel = $label ?? $config['label'];
    $finalIcon = $icon ?? $config['icon'];
@endphp

<span class="admin-status-badge {{ $status }}">
    <i class="fas {{ $finalIcon }}"></i>
    {{ $finalLabel }}
</span>
