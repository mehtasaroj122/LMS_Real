<!--
    User Cell Component - Displays user with avatar
-->

@props(['user', 'showEmail' => true])

<div class="admin-user-cell">
    <div class="admin-user-avatar">
        @if ($user->profile_photo && str_starts_with($user->profile_photo, 'http'))
            <img src="{{ $user->profile_photo }}" alt="{{ $user->name }}">
        @elseif ($user->profile_photo)
            <img src="{{ asset(str_starts_with($user->profile_photo, 'storage/') ? $user->profile_photo : 'storage/' . ltrim($user->profile_photo, '/')) }}" alt="{{ $user->name }}">
        @else
            {{ strtoupper(substr($user->name, 0, 1)) }}
        @endif
    </div>
    <div class="admin-user-info">
        <div class="admin-user-name">{{ $user->name }}</div>
        @if ($showEmail)
            <div class="admin-user-email">{{ $user->email }}</div>
        @endif
    </div>
</div>
