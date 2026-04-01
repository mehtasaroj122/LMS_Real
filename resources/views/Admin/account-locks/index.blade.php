@extends('Admin.layouts.app')

@section('title', 'Account Lock Management')

@section('content')
    <div class="py-4 container-fluid">
        <!-- Header -->
        <div class="mb-4 row">
            <div class="col-md-8">
                <h1 class="mb-0 h3">
                    <i class="fas fa-lock"></i> Account Lock Management
                </h1>
                <p class="mt-2 text-muted">Manage locked accounts and rate limiting settings</p>
            </div>
            <div class="col-md-4 text-end">
                @if ($lockedAccounts->total() > 0)
                    <form action="{{ route('admin.account-locks.unlock-all') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to unlock ALL accounts?')">
                            <i class="fas fa-unlock"></i> Unlock All
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! session('error') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Locked Accounts Card -->
            <div class="mb-4 col-lg-8">
                <div class="shadow-sm card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 card-title">
                            <i class="fas fa-lock-open text-danger"></i>
                            Locked Accounts ({{ $lockedAccounts->total() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="account-lock-filter-bar">
                            <div class="account-lock-search">
                                <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control"
                                    placeholder="Search by user, email, role, or IP">
                            </div>
                            <label class="admin-table-entries-control" for="accountLocksPerPage">
                                <span>Show</span>
                                <select id="accountLocksPerPage" name="per_page" class="admin-table-entries-select" onchange="this.form.submit()">
                                    @foreach ([10, 20, 50, 100] as $entryCount)
                                        <option value="{{ $entryCount }}" {{ (int) ($perPage ?? 10) === $entryCount ? 'selected' : '' }}>{{ $entryCount }}</option>
                                    @endforeach
                                </select>
                                <span>entries</span>
                            </label>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            @if (!empty($search))
                                <a href="{{ route('admin.account-locks.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                            @endif
                        </form>

                        @if ($lockedAccounts->total() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>User</th>
                                            <th>Email</th>
                                            <th>IP Address</th>
                                            <th>Minutes Remaining</th>
                                            <th>Expires At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($lockedAccounts as $account)
                                            @foreach ($account['ips'] as $ip)
                                                <tr>
                                                    <td>
                                                        @if ($account['user'])
                                                            <strong>{{ $account['user']->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $account['user']->role }}</small>
                                                        @else
                                                            <span class="text-muted">Unknown User</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <code>{{ $account['email'] }}</code>
                                                    </td>
                                                    <td>
                                                        <code>{{ $ip['ip'] ?? 'Unknown' }}</code>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning text-dark">
                                                            {{ $ip['minutes_remaining'] }} min
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>{{ $ip['expires_at']->format('M d, Y H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        <form action="{{ route('admin.account-locks.unlock') }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="email"
                                                                value="{{ $account['email'] }}">
                                                            <input type="hidden" name="ip"
                                                                value="{{ $ip['ip'] }}">
                                                            <button type="submit" class="btn btn-sm btn-primary"
                                                                title="Unlock this IP">
                                                                <i class="fas fa-unlock"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {!! view('shared.admin-table-pagination', ['paginator' => $lockedAccounts])->render() !!}
                            </div>
                        @else
                            <div class="mb-0 alert alert-info">
                                <i class="fas fa-check-circle"></i>
                                {{ !empty($search) ? 'No locked accounts match the current search.' : 'No locked accounts at the moment. System is secure!' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="mb-4 col-lg-4">
                <div class="shadow-sm card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 card-title">
                            <i class="fas fa-cog text-primary"></i> Rate Limiting Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.account-locks.settings') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="max_attempts" class="form-label">Max Login Attempts</label>
                                <input type="number" class="form-control @error('max_attempts') is-invalid @enderror"
                                    id="max_attempts" name="max_attempts" value="{{ old('max_attempts', $maxAttempts) }}"
                                    min="1" max="20" required>
                                <small class="form-text text-muted">Attempts before account lockout</small>
                                @error('max_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="lockout_duration" class="form-label">Lockout Duration (minutes)</label>
                                <input type="number" class="form-control @error('lockout_duration') is-invalid @enderror"
                                    id="lockout_duration" name="lockout_duration"
                                    value="{{ old('lockout_duration', $lockoutDuration) }}" min="1" max="1440"
                                    required>
                                <small class="form-text text-muted">How long the account stays locked (max 24 hours)</small>
                                @error('lockout_duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rate_limiting_enabled"
                                        name="rate_limiting_enabled" value="1" checked>
                                    <label class="form-check-label" for="rate_limiting_enabled">
                                        Enable Rate Limiting
                                    </label>
                                </div>
                                <small class="form-text text-muted">Disable to allow unlimited login attempts (not
                                    recommended)</small>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_unlock_enabled"
                                        name="email_unlock_enabled" value="1" checked>
                                    <label class="form-check-label" for="email_unlock_enabled">
                                        Enable Email Unlock Links
                                    </label>
                                </div>
                                <small class="form-text text-muted">Allow users to unlock via email links</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="mt-3 shadow-sm card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 card-title">
                            <i class="fas fa-info-circle text-info"></i> System Info
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Current Settings:</small>
                            <p class="mb-0">
                                <strong>Max Attempts:</strong> {{ $maxAttempts }}<br>
                                <strong>Lockout Duration:</strong> {{ $lockoutDuration }} minutes
                            </p>
                        </div>
                        <hr>
                        <small class="text-muted">CLI Command:</small>
                        <p class="mb-0">
                            <code>php artisan auth:unlock-account {email}</code>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .account-lock-filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .account-lock-search {
            flex: 1 1 260px;
            min-width: 220px;
        }

        body.dark-theme .account-lock-search .form-control {
            background-color: #1e293b;
            border-color: #475569;
            color: #f8fafc;
        }

        body.dark-theme .account-lock-search .form-control::placeholder {
            color: #94a3b8;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        code {
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            color: #d63384;
        }

        body.dark-theme code {
            background-color: #0f172a;
            color: #f472b6;
        }
    </style>
@endsection
