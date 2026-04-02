<div class="lock-feed-head">
    <span class="lock-panel-pill">{{ number_format($lockedAccounts->total()) }} matched</span>

    @if ($lockedAccounts->total() > 0)
        <div class="lock-feed-inline">
            Showing {{ $lockedAccounts->firstItem() ?? 0 }} to {{ $lockedAccounts->lastItem() ?? 0 }} of {{ $lockedAccounts->total() }}
        </div>
    @endif
</div>

@if ($lockedAccounts->total() > 0)
    <div class="lock-record-list">
        @foreach ($lockedAccounts as $account)
            <article class="lock-record">
                <div class="lock-record-top">
                    <div class="lock-user-block">
                        <div class="lock-user-avatar">
                            {{ strtoupper(substr($account['user']?->name ?? $account['email'], 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="lock-user-name">{{ $account['user']?->name ?? 'User record unavailable' }}</h3>
                            <p class="lock-user-email">{{ $account['email'] }}</p>
                        </div>
                    </div>

                    <div class="lock-record-actions">
                        <span class="lock-record-pill">
                            {{ $account['ip_count'] }} {{ \Illuminate\Support\Str::plural('endpoint', $account['ip_count']) }}
                        </span>

                        <form
                            action="{{ route('admin.account-locks.unlock') }}"
                            method="POST"
                            class="js-lock-mutation-form"
                            data-confirm-title="Unlock user"
                            data-confirm-message="Clear every active lock point for {{ $account['email'] }}?"
                            data-confirm-detail="This removes all blocked IP combinations currently tied to this account."
                            data-confirm-variant="info"
                        >
                            @csrf
                            <input type="hidden" name="email" value="{{ $account['email'] }}">
                            <button type="submit" class="btn btn-secondary btn-sm">
                                <i class="fas fa-unlock-keyhole"></i>
                                <span>Unlock User</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lock-meta-grid">
                    <div class="lock-meta-card">
                        <span class="lock-meta-label">Role</span>
                        <strong class="lock-meta-value">{{ ucfirst($account['user']?->role ?? 'Unknown') }}</strong>
                    </div>
                    <div class="lock-meta-card">
                        <span class="lock-meta-label">Max Wait</span>
                        <strong class="lock-meta-value">{{ $account['max_minutes_remaining'] }} min</strong>
                    </div>
                    <div class="lock-meta-card">
                        <span class="lock-meta-label">Final Unlock</span>
                        <strong class="lock-meta-value">{{ $account['latest_expires_at']->format('M d, h:i A') }}</strong>
                    </div>
                    <div class="lock-meta-card">
                        <span class="lock-meta-label">Policy</span>
                        <strong class="lock-meta-value">{{ $maxAttempts }} attempts</strong>
                    </div>
                </div>

                <div class="lock-ip-grid">
                    @foreach ($account['ips'] as $ip)
                        <section class="lock-ip-card">
                            <div class="lock-ip-head">
                                <div>
                                    <span class="lock-ip-label">Source IP</span>
                                    <strong class="lock-ip-address">{{ $ip['ip'] }}</strong>
                                </div>

                                <span class="lock-time-pill {{ $ip['minutes_remaining'] <= 10 ? 'is-warning' : 'is-live' }}">
                                    {{ $ip['minutes_remaining'] }} min
                                </span>
                            </div>

                            <p class="lock-ip-copy">Auto unlocks on {{ $ip['expires_at']->format('M d, Y h:i A') }}.</p>

                            <form
                                action="{{ route('admin.account-locks.unlock') }}"
                                method="POST"
                                class="js-lock-mutation-form"
                                data-confirm-title="Unlock IP"
                                data-confirm-message="Clear the lock for {{ $account['email'] }} on {{ $ip['ip'] }}?"
                                data-confirm-detail="Other active IP locks for this user will stay protected."
                                data-confirm-variant="info"
                            >
                                @csrf
                                <input type="hidden" name="email" value="{{ $account['email'] }}">
                                <input type="hidden" name="ip" value="{{ $ip['ip'] }}">
                                <button type="submit" class="btn btn-ghost btn-sm">
                                    <i class="fas fa-unlock"></i>
                                    <span>Unlock This IP</span>
                                </button>
                            </form>
                        </section>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>

    <div class="lock-pagination-wrap">
        {!! view('shared.admin-table-pagination', ['paginator' => $lockedAccounts])->render() !!}
    </div>
@else
    <div class="lock-empty-state">
        <div class="lock-empty-icon">
            <i class="fas fa-shield-check"></i>
        </div>
        <h3>{{ $search ? 'No matching lockouts found' : 'No active account lockouts' }}</h3>
        <p>
            {{ $search ? 'Try widening your search or reset the filter to review all active timers.' : 'Everything is currently clear. New lockouts will appear here automatically.' }}
        </p>
    </div>
@endif
