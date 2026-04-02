<div class="lock-status-row">
    <span class="lock-status-badge {{ $rateLimitingEnabled ? 'is-live' : 'is-muted' }}">
        <i class="fas fa-shield-halved"></i>
        <span>{{ $rateLimitingEnabled ? 'Rate limiting enabled' : 'Rate limiting disabled' }}</span>
    </span>
    <span class="lock-status-badge {{ $emailUnlockEnabled ? 'is-live' : 'is-muted' }}">
        <i class="fas fa-envelope-open-text"></i>
        <span>{{ $emailUnlockEnabled ? 'Email unlock links enabled' : 'Email unlock links disabled' }}</span>
    </span>
    <span class="lock-status-badge">
        <i class="fas fa-database"></i>
        <span>Cache: {{ strtoupper($cacheStore) }}</span>
    </span>
</div>
