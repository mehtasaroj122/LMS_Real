<!--
    User Cell Component - Displays user with avatar
-->

@props(['user', 'showEmail' => true])

<div class="admin-user-cell">
    <div class="admin-user-avatar">
        @php
            $photoUrl = \App\Support\ProfilePhoto::resolveUrl($user->profile_photo);
        @endphp
        @if ($photoUrl)
            <img src="{{ $photoUrl }}" alt="{{ $user->name }}">
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
