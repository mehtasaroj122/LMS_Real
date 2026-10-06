@extends('Admin.layouts.app')
@section('title', 'Activity Logs')

@push('styles')
    @foreach (['audit-event-theme', 'audit-event-details', 'activity-logs'] as $stylesheet)
        <link rel="stylesheet" href="{{ asset('admin/CSS/'.$stylesheet.'.css') }}?v={{ filemtime(public_path('admin/CSS/'.$stylesheet.'.css')) }}">
    @endforeach
@endpush

@section('content')
    @php
        $cards = [
            ['label' => 'Total Audit Events', 'value' => $allActivities, 'note' => 'All recorded audit entries', 'icon' => 'history', 'tone' => 'book'],
            ['label' => 'Current Results', 'value' => $totalActivities, 'note' => 'Entries matching current filters', 'icon' => 'filter', 'tone' => 'privilege'],
            ['label' => 'Admin Actions', 'value' => $adminActions, 'note' => 'Across all audit logs', 'icon' => 'shield', 'tone' => 'danger'],
            ['label' => 'Staff Actions', 'value' => $staffActions, 'note' => 'Across all audit logs', 'icon' => 'briefcase', 'tone' => 'user'],
            ['label' => 'Student Actions', 'value' => $studentActions, 'note' => 'Across all audit logs', 'icon' => 'graduation-cap', 'tone' => 'warning'],
            ['label' => 'Event Categories', 'value' => $actionCategories->count(), 'note' => 'Distinct recorded categories', 'icon' => 'layers', 'tone' => 'system'],
        ];
        $filterLabels = ['search' => 'Search', 'role' => 'Role', 'action_category' => 'Category', 'event_type' => 'Event', 'period' => 'Time'];
        $activeFilters = collect($filterLabels)->filter(fn ($label, $key) => filled(request($key)) && ($key !== 'period' || request($key) !== 'all'));
        $timeLabels = ['today' => 'Today', '7days' => 'Last 7 Days', '30days' => 'Last 30 Days'];
    @endphp

    <div id="activityLogAsyncRoot" class="activity-page activity-async-root" aria-busy="false">
        <header class="activity-page-header">
            <div>
                <h1 class="activity-page-title">Activity Logs</h1>
                <p class="activity-page-subtitle">System Audit &amp; Activity Center</p>
                <p class="activity-page-description">Track user actions, circulation events, account changes and security activity.</p>
            </div>
            <div class="activity-page-note">
                <i data-lucide="shield-check" aria-hidden="true"></i>
                <span>{{ number_format($totalActivities) }} {{ $activeFilters->isNotEmpty() ? 'matching' : 'total' }} audit entries</span>
                @if ($activeFilters->isNotEmpty())
                    <span class="activity-filter-count">{{ $activeFilters->count() }} {{ Str::plural('filter', $activeFilters->count()) }} active</span>
                @endif
            </div>
        </header>

        <div class="activity-stat-grid" aria-label="Audit summary">
            @foreach ($cards as $card)
                <div class="card activity-stat-card audit-tone-{{ $card['tone'] }}">
                    <span class="activity-stat-icon audit-soft audit-icon"><i data-lucide="{{ $card['icon'] }}" aria-hidden="true"></i></span>
                    <div><p class="activity-stat-label">{{ $card['label'] }}</p><p class="activity-stat-value">{{ number_format($card['value']) }}</p></div>
                    <p class="activity-stat-note">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </div>

        <section class="card activity-panel activity-filter-panel" aria-label="Filter audit logs">
            <div class="activity-filter-grid">
                <div class="activity-filter-group activity-search-group">
                    <label for="search" class="audit-sr-only">Search logs</label>
                    <div class="activity-search-shell">
                        <i data-lucide="search" class="activity-search-icon" aria-hidden="true"></i>
                        <input type="search" id="search" name="search" class="search-input" placeholder="Search actor, email, event or description..." autocomplete="off" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="activity-filter-group">
                    <label for="role" class="audit-sr-only">Role</label>
                    <select id="role" name="role" class="filter-select">
                        <option value="">All roles</option>
                        @foreach (['admin', 'staff', 'student'] as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="activity-filter-group">
                    <label for="action_category" class="audit-sr-only">Category</label>
                    <select id="action_category" name="action_category" class="filter-select">
                        <option value="">All categories</option>
                        @foreach ($actionCategories as $category)
                            <option value="{{ $category }}" @selected(request('action_category') === $category)>{{ Str::headline($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="activity-filter-group">
                    <label for="event_type" class="audit-sr-only">Event type</label>
                    <select id="event_type" name="event_type" class="filter-select">
                        <option value="">All events</option>
                        @foreach ($eventTypes as $event)
                            <option value="{{ $event }}" @selected(request('event_type') === $event)>{{ Str::headline($event) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="activity-filter-group">
                    <label for="period" class="audit-sr-only">Time range</label>
                    <select id="period" name="period" class="filter-select">
                        <option value="all" @selected(!request('period') || request('period') === 'all')>All time</option>
                        @foreach ($timeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('period') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button id="resetFiltersBtn" data-reset-filters type="button" class="activity-reset-btn admin-ui-button admin-ui-neutral"><i data-lucide="rotate-ccw" aria-hidden="true"></i>Reset</button>
                <label class="admin-table-entries-control" for="per_page"><span>Show</span>
                    <select id="per_page" name="per_page" class="admin-table-entries-select">
                        @foreach ([10, 20, 50, 100] as $count)
                            <option value="{{ $count }}" @selected($activities->perPage() === $count)>{{ $count }}</option>
                        @endforeach
                    </select><span>entries</span>
                </label>
            </div>
            @if ($activeFilters->isNotEmpty())
                <div class="activity-filter-chips" aria-label="Active filters">
                    @foreach ($activeFilters as $key => $label)
                        @php
                            $value = $key === 'period' ? ($timeLabels[request($key)] ?? request($key)) : ($key === 'search' ? request($key) : Str::headline(request($key)));
                        @endphp
                        <button type="button" data-clear-filter="{{ $key }}" class="activity-filter-chip" aria-label="Remove {{ $label }} filter: {{ $value }}"><span>{{ $label }}: {{ $value }}</span><i data-lucide="x" aria-hidden="true"></i></button>
                    @endforeach
                    <button type="button" data-reset-filters class="activity-clear-all">Clear all</button>
                </div>
            @endif
        </section>

        <section class="card activity-panel activity-trail" data-audit-results aria-labelledby="auditTrailHeading">
            <div class="activity-panel-header">
                <div><h2 id="auditTrailHeading">Audit Trail</h2><p>Showing {{ $activities->firstItem() ?? 0 }}–{{ $activities->lastItem() ?? 0 }} of {{ number_format($totalActivities) }} entries</p></div>
                <span class="activity-results-chip"><i data-lucide="clock-3" aria-hidden="true"></i>Newest first</span>
            </div>
            <div id="auditTrailTable" class="activity-table-wrapper" role="region" aria-label="Audit trail, scroll horizontally for all columns" tabindex="0">
                <table class="activity-table">
                    <caption class="audit-sr-only">System activity: time, actor, event and details</caption>
                    <thead><tr><th scope="col">Time</th><th scope="col">Actor</th><th scope="col">Event</th><th scope="col">Details</th></tr></thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            @php
                                $detail = $auditDetails[$activity->id];
                                $style = $detail['eventStyle'];
                                $userName = $detail['userName'];
                                $role = $detail['userRole'];
                                $initials = collect(explode(' ', $userName))->filter()->take(2)->map(fn ($part) => Str::upper(Str::substr($part, 0, 1)))->implode('');
                                $photo = $activity->user?->profile_photo;
                                $photoUrl = filled($photo) ? \App\Support\ProfilePhoto::resolveUrl($photo) : null;
                            @endphp
                            <tr class="{{ $style['class'] }}">
                                <td class="activity-time-col"><div class="time-stack"><time class="time-date" datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->format('M j, Y') }}</time><span class="time-hour" title="{{ $detail['fullTimestamp'] }}">{{ $activity->created_at->format('g:i A') }}</span><span class="time-relative">{{ $activity->created_at->diffForHumans() }}</span></div></td>
                                <td><div class="activity-actor"><span class="activity-avatar {{ $detail['roleClass'] }}" aria-hidden="true">@if ($photoUrl)<img src="{{ $photoUrl }}" alt="">@else{{ $initials ?: 'S' }}@endif</span><div class="activity-actor-copy"><div class="activity-actor-name-row"><span class="activity-actor-name">{{ $userName }}</span><span class="activity-role {{ $detail['roleClass'] }}">{{ ucfirst($role) }}</span></div><span class="activity-actor-email">{{ $activity->user_email ?: $activity->user?->email ?: 'No email recorded' }}</span></div></div></td>
                                <td><div class="event-stack"><span class="activity-event audit-badge"><i data-lucide="{{ $style['icon'] }}" aria-hidden="true"></i>{{ $detail['title'] }}</span><div class="activity-event-meta"><span class="activity-category {{ $style['categoryClass'] }} audit-badge">{{ $style['category'] }}</span><span>Entry #{{ $activity->id }}</span></div><span class="activity-severity audit-text">{{ $style['severityLabel'] }}</span></div></td>
                                <td class="activity-details-col"><p class="context-description">@foreach (\App\Support\AuditDescription::segments($activity) as $segment)@if ($segment['emphasis'])<strong>{{ $segment['text'] }}</strong>@else{{ $segment['text'] }}@endif @endforeach</p><div class="context-meta">
                                    @if ($activity->browser)<span class="context-pill" title="Browser"><i data-lucide="globe" aria-hidden="true"></i>{{ $activity->browser }}</span>@endif
                                    @if ($activity->device_type)<span class="context-pill" title="Device type"><i data-lucide="monitor" aria-hidden="true"></i>{{ Str::headline($activity->device_type) }}</span>@endif
                                    @if ($activity->ip_address)<span class="context-pill" title="IP address"><i data-lucide="network" aria-hidden="true"></i>{{ $activity->ip_address }}</span>@endif
                                </div><button type="button" class="activity-details-button" data-audit-details="{{ $activity->id }}" aria-label="View details for {{ $detail['title'] }}, entry {{ $activity->id }}"><i data-lucide="eye" aria-hidden="true"></i>View details</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state"><i data-lucide="search-x" class="empty-state-icon" aria-hidden="true"></i><h3>No activity logs found</h3><p>Try changing your search or filters.</p><button type="button" data-reset-filters class="admin-ui-button admin-ui-secondary">Reset Filters</button></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @include('shared.admin-table-pagination', ['paginator' => $activities, 'resultLabel' => 'entries'])
            <div class="activity-loading" aria-hidden="true"><span class="activity-loading-spinner"></span><span>Loading audit entries…</span><div class="activity-skeleton"></div><div class="activity-skeleton"></div><div class="activity-skeleton"></div></div>
        </section>

        <section class="card activity-panel activity-analytics" aria-labelledby="frequentEventsHeading">
            <div class="activity-panel-header"><div><h2 id="frequentEventsHeading">Most Frequent Events</h2><p>Most common audit events across the system.</p></div><span class="activity-results-chip">Across all audit logs · {{ number_format($allActivities) }} entries</span></div>
            @if ($actionStats->isNotEmpty())
                <ol class="activity-frequency-list">
                    @foreach ($actionStats as $stat)
                        @php
                            $style = \App\Support\AuditEventStyle::resolve($stat->action, $stat->action_category);
                            $percentage = $allActivities > 0 ? $stat->count / $allActivities * 100 : 0;
                        @endphp
                        <li class="activity-frequency-row {{ $style['class'] }}"><span class="activity-frequency-rank" aria-label="Rank {{ $loop->iteration }}">{{ $loop->iteration }}</span><span class="activity-frequency-icon audit-soft audit-icon"><i data-lucide="{{ $style['icon'] }}" aria-hidden="true"></i></span><div class="activity-frequency-name"><span>{{ Str::headline($stat->action ?: 'Activity') }}</span><small class="{{ $style['categoryClass'] }} audit-text">{{ $style['categoryLabel'] }}</small></div><div class="activity-frequency-track" aria-hidden="true"><span style="width: {{ round($percentage, 3) }}%"></span></div><span class="activity-frequency-count">{{ number_format($stat->count) }} <small>events</small></span><span class="activity-frequency-percent">{{ number_format($percentage, 1) }}%</span></li>
                    @endforeach
                </ol>
            @else
                <div class="empty-state"><h3>No event analytics yet</h3><p>Event frequencies will appear as activity is recorded.</p></div>
            @endif
        </section>
        <script type="application/json" data-audit-payload>{!! json_encode($auditDetails, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) !!}</script>
    </div>
    <span id="auditLoadingStatus" class="audit-sr-only" role="status" aria-live="polite"></span>
    @include('partials.admin-audit-event-details-modal')
@endsection

@push('scripts')
    @foreach (['audit-event-details', 'activity-logs'] as $script)
        <script src="{{ asset('admin/JS/'.$script.'.js') }}?v={{ filemtime(public_path('admin/JS/'.$script.'.js')) }}"></script>
    @endforeach
@endpush
