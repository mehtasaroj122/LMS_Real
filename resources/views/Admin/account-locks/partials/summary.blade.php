<section class="lock-summary-grid" aria-label="Account lock summary">
    <article class="card lock-summary-card">
        <div class="lock-summary-icon info">
            <i class="fas fa-user-lock"></i>
        </div>
        <div>
            <div class="lock-summary-label">Accounts</div>
            <div class="lock-summary-value">{{ number_format($summary['active_users'] ?? 0) }}</div>
            <div class="lock-summary-copy">Users currently locked.</div>
        </div>
    </article>

    <article class="card lock-summary-card">
        <div class="lock-summary-icon warning">
            <i class="fas fa-network-wired"></i>
        </div>
        <div>
            <div class="lock-summary-label">Endpoints</div>
            <div class="lock-summary-value">{{ number_format($summary['active_ip_locks'] ?? 0) }}</div>
            <div class="lock-summary-copy">Email and IP pairs blocked.</div>
        </div>
    </article>

    <article class="card lock-summary-card">
        <div class="lock-summary-icon success">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div>
            <div class="lock-summary-label">Avg Wait</div>
            <div class="lock-summary-value">{{ number_format($summary['average_minutes_remaining'] ?? 0) }} min</div>
            <div class="lock-summary-copy">{{ number_format($summary['expiring_soon'] ?? 0) }} near expiry.</div>
        </div>
    </article>

    <article class="card lock-summary-card">
        <div class="lock-summary-icon primary">
            <i class="fas fa-gauge-high"></i>
        </div>
        <div>
            <div class="lock-summary-label">Policy</div>
            <div class="lock-summary-value">{{ $maxAttempts }} attempts / {{ $lockoutDuration }} min</div>
            <div class="lock-summary-copy">
                {{ $summary['latest_expiry'] ? 'Latest unlock ' . $summary['latest_expiry']->format('M d, h:i A') : 'No timers live.' }}
            </div>
        </div>
    </article>
</section>
