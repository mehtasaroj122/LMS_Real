@extends('Admin.layouts.app')

@section('title', 'Account Lock Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/CSS/account-locks.css') }}">
    @include('shared.action-feedback.styles')
@endpush

@section('content')
    <div
        class="lock-console"
        id="accountLockManagementRoot"
        data-index-url="{{ route('admin.account-locks.index') }}"
        data-unlock-url="{{ route('admin.account-locks.unlock') }}"
        data-unlock-all-url="{{ route('admin.account-locks.unlock-all') }}"
        data-settings-url="{{ route('admin.account-locks.settings') }}"
    >
        <section class="card lock-pagebar">
            <div class="lock-pagebar-head">
                <div class="lock-pagebar-copy">
                    <span class="lock-eyebrow">Security Console</span>
                    <h1 class="lock-title">Account Lock Management</h1>
                    <p class="lock-subtitle">Monitor lockouts, clear false positives, and tune policy without leaving the page.</p>
                </div>

                <div class="lock-pagebar-actions">
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-clock-rotate-left"></i>
                        <span>Audit Trail</span>
                    </a>
                    <a href="#securitySettingsPanel" class="btn btn-primary btn-sm">
                        <i class="fas fa-sliders"></i>
                        <span>Update Policy</span>
                    </a>
                </div>
            </div>

            <div id="accountLockHeaderMeta">
                @include('Admin.account-locks.partials.header-meta')
            </div>
        </section>

        <div id="accountLockSummaryContainer">
            @include('Admin.account-locks.partials.summary')
        </div>

        <div id="accountLockMonitoringContainer">
            @include('Admin.account-locks.partials.monitoring')
        </div>

        <div class="lock-main-grid">
            <section class="card lock-panel">
                <div class="lock-panel-head compact">
                    <div>
                        <h2 class="lock-panel-title">Live Lockout Feed</h2>
                        <p class="lock-panel-copy">Search, paginate, and release specific lock points instantly.</p>
                    </div>
                </div>

                <form id="accountLockFiltersForm" class="lock-filter-bar">
                    <div class="lock-search-field">
                        <label class="form-label" for="lockSearch">Search</label>
                        <div class="lock-input-shell">
                            <i class="fas fa-magnifying-glass"></i>
                            <input
                                id="lockSearch"
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                class="form-control"
                                placeholder="User, email, role, or IP"
                            >
                        </div>
                    </div>

                    <div class="lock-filter-field">
                        <label class="form-label" for="accountLocksPerPage">Rows</label>
                        <select id="accountLocksPerPage" name="per_page" class="form-control filter-select">
                            @foreach ([10, 20, 50, 100] as $entryCount)
                                <option value="{{ $entryCount }}" {{ (int) ($perPage ?? 10) === $entryCount ? 'selected' : '' }}>{{ $entryCount }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lock-filter-actions">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i>
                            <span>Apply</span>
                        </button>
                        <button type="button" id="accountLockResetBtn" class="btn btn-ghost btn-sm">
                            <i class="fas fa-rotate-left"></i>
                            <span>Reset</span>
                        </button>
                    </div>
                </form>

                <div id="accountLockFeedContainer">
                    @include('Admin.account-locks.partials.feed')
                </div>
            </section>

            <aside class="lock-sidebar">
                <section class="card lock-panel" id="securitySettingsPanel">
                    <div class="lock-panel-head compact">
                        <div>
                            <h2 class="lock-panel-title">Security Policy</h2>
                            <p class="lock-panel-copy">Settings save live and future lockouts use the updated policy immediately.</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.account-locks.settings') }}" method="POST" class="lock-settings-form" id="accountLockSettingsForm" novalidate autocomplete="off">
                        @csrf

                        <div class="lock-field-grid">
                            <div class="lock-field">
                                <label for="max_attempts" class="form-label">Max Login Attempts</label>
                                <input
                                    id="max_attempts"
                                    type="number"
                                    name="max_attempts"
                                    min="1"
                                    max="20"
                                    value="{{ $maxAttempts }}"
                                    class="form-control"
                                    autocomplete="off"
                                    aria-describedby="maxAttemptsHint"
                                    required
                                >
                                <p class="lock-field-copy" id="maxAttemptsHint">Allowed range: 1 to 20 failed attempts. Any value above 20 will be rejected.</p>
                                <div class="invalid-feedback" data-error-for="max_attempts" hidden></div>
                            </div>

                            <div class="lock-field">
                                <label for="lockout_duration" class="form-label">Lockout Duration (minutes)</label>
                                <input
                                    id="lockout_duration"
                                    type="number"
                                    name="lockout_duration"
                                    min="1"
                                    max="1440"
                                    value="{{ $lockoutDuration }}"
                                    class="form-control"
                                    autocomplete="off"
                                    aria-describedby="lockoutDurationHint"
                                    required
                                >
                                <p class="lock-field-copy" id="lockoutDurationHint">This value is in minutes. Example: 1 = 1 minute, 60 = 1 hour.</p>
                                <div class="invalid-feedback" data-error-for="lockout_duration" hidden></div>
                            </div>
                        </div>

                        <div class="lock-choice-group">
                            <label class="lock-choice-card" for="rate_limiting_enabled">
                                <div class="lock-choice-copy">
                                    <span class="lock-choice-title">Enable Rate Limiting</span>
                                    <span class="lock-choice-text">Keep repeated sign-in attempts under control.</span>
                                </div>
                                <input
                                    id="rate_limiting_enabled"
                                    type="checkbox"
                                    name="rate_limiting_enabled"
                                    value="1"
                                    class="form-check-input"
                                    {{ $rateLimitingEnabled ? 'checked' : '' }}
                                >
                            </label>

                            <label class="lock-choice-card" for="email_unlock_enabled">
                                <div class="lock-choice-copy">
                                    <span class="lock-choice-title">Enable Email Unlock Links</span>
                                    <span class="lock-choice-text">Allow signed unlock links for legitimate users.</span>
                                </div>
                                <input
                                    id="email_unlock_enabled"
                                    type="checkbox"
                                    name="email_unlock_enabled"
                                    value="1"
                                    class="form-check-input"
                                    {{ $emailUnlockEnabled ? 'checked' : '' }}
                                >
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary lock-submit-btn" id="accountLockSettingsSubmitBtn">
                            <i class="fas fa-floppy-disk"></i>
                            <span>Save Policy</span>
                        </button>
                    </form>
                </section>

                <section class="card lock-panel">
                    <div class="lock-panel-head compact">
                        <div>
                            <h2 class="lock-panel-title">Bulk Recovery</h2>
                            <p class="lock-panel-copy">Use only when you need a broad reset after testing or a false-positive event.</p>
                        </div>
                    </div>

                    <div id="accountLockBulkContainer">
                        @include('Admin.account-locks.partials.bulk')
                    </div>
                </section>

                <section class="card lock-panel">
                    <div class="lock-panel-head compact">
                        <div>
                            <h2 class="lock-panel-title">Quick Notes</h2>
                            <p class="lock-panel-copy">Shortcuts for support and incident response.</p>
                        </div>
                    </div>

                    <div class="lock-note-stack">
                        <div class="lock-note-card">
                            <i class="fas fa-terminal"></i>
                            <div>
                                <strong>CLI fallback</strong>
                                <p><code>php artisan auth:unlock-account user@example.com --all-ips</code></p>
                            </div>
                        </div>

                        <div class="lock-note-card">
                            <i class="fas fa-wave-square"></i>
                            <div>
                                <strong>Monitoring source</strong>
                                <p>The live feed reads Laravel rate-limiter timers from the shared cache store.</p>
                            </div>
                        </div>

                    </div>
                </section>
            </aside>
        </div>

        @include('shared.action-feedback.markup', [
            'actionFeedbackConfig' => [
                'confirm' => [
                    'modalId' => 'accountLockConfirmModal',
                    'iconId' => 'accountLockConfirmIcon',
                    'titleId' => 'accountLockConfirmTitle',
                    'messageId' => 'accountLockConfirmMessage',
                    'detailId' => 'accountLockConfirmDetail',
                    'submitButtonId' => 'accountLockConfirmSubmitBtn',
                    'cancelLabel' => 'Cancel',
                    'confirmLabel' => 'Continue',
                ],
                'toast' => [
                    'containerId' => 'accountLockToastContainer',
                    'liveRegionId' => 'accountLockLiveRegion',
                ],
            ],
        ])
    </div>
@endsection

@push('scripts')
    @include('shared.action-feedback.scripts')
    <script>
        window.accountLockManagementConfig = {
            routes: {
                index: @json(route('admin.account-locks.index')),
            },
            state: {
                search: @json($search),
                perPage: @json($perPage),
                page: @json($lockedAccounts->currentPage()),
            },
            settings: {
                max_attempts: @json($maxAttempts),
                lockout_duration: @json($lockoutDuration),
                rate_limiting_enabled: @json($rateLimitingEnabled),
                email_unlock_enabled: @json($emailUnlockEnabled),
            },
            flash: {
                success: @json(session('success')),
                error: @json(session('error')),
            },
        };
    </script>
    <script src="{{ asset('admin/JS/account-locks.js') }}"></script>
@endpush
