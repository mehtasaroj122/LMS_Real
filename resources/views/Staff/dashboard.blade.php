@extends('Staff.layouts.app')

@section('title', 'Dashboard')

@push('styles')
    <style>
        /* Header */
        .page-header {
            margin-bottom: 8px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        body.light-theme .page-title {
            color: #0f172a;
        }

        body.dark-theme .page-title {
            color: #f1f5f9;
        }

        .page-description {
            font-size: 12px;
            color: #64748b;
        }

        body.dark-theme .page-description {
            color: #94a3b8;
        }

        /* Dashboard Specific Styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 640px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .stat-card {
            border-radius: 0.75rem;
            padding: 1.25rem;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        body.light-theme .stat-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        body.dark-theme .stat-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        body.dark-theme .stat-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        body.light-theme .stat-label {
            color: #64748b;
        }

        body.dark-theme .stat-label {
            color: #94a3b8;
        }

        /* Status Colors */
        .status-blue {
            background-color: #dbeafe;
            color: #2563eb;
        }

        body.dark-theme .status-blue {
            background-color: #1e3a8a;
            color: #60a5fa;
        }

        .status-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .status-green {
            background-color: #14532d;
            color: #4ade80;
        }

        .status-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .status-red {
            background-color: #7f1d1d;
            color: #f87171;
        }

        .status-yellow {
            background-color: #fef3c7;
            color: #d97706;
        }

        body.dark-theme .status-yellow {
            background-color: #78350f;
            color: #fbbf24;
        }

        .status-purple {
            background-color: #f3e8ff;
            color: #7c3aed;
        }

        body.dark-theme .status-purple {
            background-color: #4c1d95;
            color: #a78bfa;
        }

        /* Two Column Layout for Due Today & Overdue */
        .two-column-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 1024px) {
            .two-column-layout {
                grid-template-columns: 1fr 2fr;
            }
        }

        .section-card {
            border-radius: 0.75rem;
            padding: 1.25rem;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        body.light-theme .section-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }

        body.dark-theme .section-card {
            background-color: #1e293b;
            border: 1px solid #334155;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid;
        }

        body.light-theme .section-header {
            border-color: #e5e7eb;
        }

        body.dark-theme .section-header {
            border-color: #334155;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
        }

        body.dark-theme .section-title {
            color: #e2e8f0;
        }

        .section-count {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
        }

        .count-green {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .count-green {
            background-color: #14532d;
            color: #4ade80;
        }

        .count-red {
            background-color: #fee2e2;
            color: #dc2626;
        }

        body.dark-theme .count-red {
            background-color: #7f1d1d;
            color: #f87171;
        }

        /* Due Today Section */
        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            text-align: center;
        }

        .empty-icon {
            width: 3rem;
            height: 3rem;
            margin-bottom: 0.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.light-theme .empty-icon {
            background-color: #dcfce7;
            color: #16a34a;
        }

        body.dark-theme .empty-icon {
            background-color: #14532d;
            color: #4ade80;
        }

        .empty-title {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #0f172a;
        }

        body.dark-theme .empty-title {
            color: #e2e8f0;
        }

        .empty-subtitle {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .empty-subtitle {
            color: #94a3b8;
        }

        /* Overdue Books List */
        .overdue-list {
            flex: 1;
            overflow-y: auto;
            max-height: 300px;
        }

        .overdue-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.2s ease;
        }

        body.light-theme .overdue-item {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
        }

        body.dark-theme .overdue-item {
            background-color: #7f1d1d;
            border: 1px solid #991b1b;
        }

        .overdue-item:last-child {
            margin-bottom: 0;
        }

        .overdue-info {
            flex: 1;
            min-width: 0;
        }

        .overdue-book {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.125rem;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        body.dark-theme .overdue-book {
            color: #e2e8f0;
        }

        .overdue-student {
            font-size: 0.75rem;
            color: #64748b;
        }

        body.dark-theme .overdue-student {
            color: #cbd5e1;
        }

        .overdue-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
            margin-left: 0.75rem;
        }

        .overdue-date {
            font-size: 0.75rem;
            font-weight: 500;
            color: #dc2626;
        }

        body.dark-theme .overdue-date {
            color: #f87171;
        }

        .overdue-days {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            background-color: #dc2626;
            color: white;
        }

        body.dark-theme .overdue-days {
            background-color: #ef4444;
        }

        /* Pending Book Requests Section */
        .request-list {
            flex: 1;
        }

        .request-item {
            padding: 1rem 0;
            border-bottom: 1px solid;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        body.light-theme .request-item {
            border-color: #e5e7eb;
        }

        body.dark-theme .request-item {
            border-color: #334155;
        }

        .request-item:last-child {
            border-bottom: none;
        }

        .request-info {
            flex: 1;
        }

        .request-book {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #0f172a;
        }

        body.dark-theme .request-book {
            color: #e2e8f0;
        }

        .request-student {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        body.dark-theme .request-student {
            color: #94a3b8;
        }

        .request-date {
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme .request-date {
            color: #94a3b8;
        }

        .request-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #d97706;
        }

        body.dark-theme .badge-pending {
            background-color: #78350f;
            color: #fbbf24;
        }

        .action-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            white-space: nowrap;
        }

        body.light-theme .action-btn {
            background-color: #2563eb;
            color: white;
        }

        body.dark-theme .action-btn {
            background-color: #1e40af;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
        }
        /* Specific action button variants */
        .action-btn.btn-reject {
            background-color: #f59e0b; /* amber-400 warning */
            color: #000000;
        }

        body.light-theme .action-btn.btn-reject {
            background-color: #f59e0b; /* ensure override in light theme */
            color: #000000;
        }

        body.dark-theme .action-btn.btn-reject {
            background-color: #b45309; /* amber-700 darker for dark theme */
            color: #ffffff;
        }

        .action-btn.btn-accept {
            background-color: #16a34a;
            color: #ffffff;
        }

        /* Disabled action button appearance after an action */
        .action-btn:disabled {
            cursor: not-allowed;
            opacity: 0.7;
            filter: grayscale(0.02);
        }
        .view-all-link {
            float: right;
            font-size: 0.9rem;
            color: #2563eb;
            text-decoration: none;
            margin-left: 0.5rem;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            padding: 0.5rem 0.75rem;
            color: #fff;
            border-radius: 0.375rem;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
            font-weight: 600;
            opacity: 0.95;
        }

        .toast.success { background: #16a34a; }
        .toast.error { background: #ef4444; }
    </style>
@endpush

@section('content')
    <div class="dashboard">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Staff Dashboard</h1>
            <p class="page-description">Manage daily library operations</p>
        </div>
        <!-- Dashboard Overview Cards -->
        <div class="dashboard-grid">
            <!-- Books Issued Card -->
            <div class="stat-card status-blue">
                <div class="stat-card-header">
                    <div class="stat-icon status-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20" />
                        </svg>
                    </div>
                    <div class="stat-value">{{ $currentlyIssued ?? 0 }}</div>
                </div>
                <div class="stat-label">Currently Issued</div>
            </div>

            <!-- Due Today Card -->
            <div class="stat-card status-green">
                <div class="stat-card-header">
                    <div class="stat-icon status-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                    <div class="stat-value">{{ $dueToday ?? 0 }}</div>
                </div>
                <div class="stat-label">Due Today</div>
            </div>

            <!-- Overdue Card -->
            <div class="stat-card status-red">
                <div class="stat-card-header">
                    <div class="stat-icon status-red">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    </div>
                    <div class="stat-value">{{ $overdueCount ?? 0 }}</div>
                </div>
                <div class="stat-label">Overdue</div>
            </div>

            <!-- Pending Requests Card -->
            <div class="stat-card status-yellow">
                <div class="stat-card-header">
                    <div class="stat-icon status-yellow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="12" y1="18" x2="12" y2="12" />
                            <line x1="9" y1="15" x2="15" y2="15" />
                        </svg>
                    </div>
                    <div class="stat-value">{{ $pendingRequestsCount ?? 0 }}</div>
                </div>
                <div class="stat-label">Pending Requests</div>
            </div>

            <!-- Pending Fines Card -->
            <div class="stat-card status-purple">
                <div class="stat-card-header">
                    <div class="stat-icon status-purple">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                    </div>
                    <div class="stat-value">₹{{ number_format($pendingFinesAmount ?? 0) }}</div>
                </div>
                <div class="stat-label">Pending Fines</div>
            </div>
        </div>

        <!-- Due Today & Overdue Books in Single Row -->
        <div class="two-column-layout">
            <!-- Due Today Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">Due Today</h2>
                    <span class="section-count count-green">0</span>
                </div>
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    </div>
                    <h3 class="empty-title">No books due today</h3>
                    <p class="empty-subtitle">All clear for today!</p>
                </div>
            </div>

            <!-- Overdue Books Section -->
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">Overdue Books</h2>
                    <span class="section-count count-red">{{ isset($overdues) ? $overdues->count() : ($overdueCount ?? 0) }}</span>
                </div>
                <div class="overdue-list">
                @if(isset($overdues) && $overdues->count())
                    @foreach($overdues as $item)
                        <div class="overdue-item">
                            <div class="overdue-info">
                                <div class="overdue-book">{{ optional($item->book)->title ?? 'Untitled' }}</div>
                                <div class="overdue-student">{{ optional($item->student)->user->name ?? optional($item->student)->name ?? 'Unknown' }}</div>
                            </div>
                            <div class="overdue-details">
                                <div class="overdue-date">Due: {{ optional($item->due_date)->format('n/j/Y') }}</div>
                                <div class="overdue-days">{{ (int) \Carbon\Carbon::parse($item->due_date)->diffInDays(\Carbon\Carbon::now()) }}d</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="overdue-item empty-state">
                        <div class="empty-title">No overdue books</div>
                        <p class="empty-subtitle">Great — nothing overdue right now.</p>
                    </div>
                @endif
            </div>
            </div>
        </div>

        <!-- Pending Book Requests Section -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">Pending Book Requests <a href="{{ route('staff.book-requests.index') }}" class="view-all-link">View all</a></h2>
            </div>
            <div class="request-list">
                @if(isset($pendingRequests) && $pendingRequests->count())
                    @foreach($pendingRequests as $req)
                        <div class="request-item" id="request-{{ $req->id }}">
                            <div class="request-info">
                                <div class="request-book">{{ optional($req->book)->title ?? 'Untitled' }}</div>
                                <div class="request-student">{{ optional($req->student)->user->name ?? optional($req->student)->name ?? 'Unknown' }} • {{ optional($req->student)->student_id ?? '' }}</div>
                                <div class="request-date">Requested: {{ optional($req->request_date)->format('M d, Y') }}</div>
                            </div>
                            <div class="request-status">
                                <div class="action-buttons">
                                    <button class="action-btn btn-accept" data-id="{{ $req->id }}" onclick="processBookRequest({{ $req->id }}, 'approved', this)">Accept</button>
                                    <button class="action-btn btn-reject" data-id="{{ $req->id }}" onclick="processBookRequest({{ $req->id }}, 'rejected', this)">Reject</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state" style="text-align:center;padding:1.5rem;">
                        <div class="empty-icon" aria-hidden="true" style="margin-bottom:0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 8v4l3 3"></path>
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                        </div>
                        <div class="empty-title">No pending requests</div>
                        <p class="empty-subtitle">You're all caught up — there are no pending book requests right now.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Simple hover effect enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');

            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // No inline button handlers here; buttons call `processBookRequest` directly.
        });

            // Utility to escape HTML when inserting from server
            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            // Simple toast
            function showToast(message, type = 'success') {
                let container = document.getElementById('toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-container';
                    container.className = 'toast-container';
                    document.body.appendChild(container);
                }
                const t = document.createElement('div');
                t.className = 'toast ' + (type === 'error' ? 'error' : 'success');
                t.textContent = message;
                container.appendChild(t);
                setTimeout(() => {
                    t.style.transition = 'opacity 300ms ease, transform 300ms ease';
                    t.style.opacity = '0';
                    t.style.transform = 'translateY(-8px)';
                    setTimeout(() => t.remove(), 350);
                }, 3000);
            }

            // Process a book request (accept or reject)
            function processBookRequest(requestId, status, btn) {
                if (!confirm(`Are you sure you want to ${status === 'approved' ? 'accept' : 'reject'} this request?`)) return;

                const url = `/staff/book-requests/${requestId}`;
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(async response => {
                    // Surface non-OK responses for easier debugging
                    if (!response.ok) {
                        const text = await response.text().catch(() => 'Unable to read response body');
                        console.error('Book request update failed', response.status, text);
                        alert(`Request failed: HTTP ${response.status}\n${text}`);
                        return;
                    }

                    const data = await response.json().catch(err => {
                        console.error('Failed to parse JSON response', err);
                        alert('Unexpected server response (invalid JSON)');
                        return null;
                    });

                    if (!data) return;

                    if (data.success) {
                        const requestEl = document.getElementById(`request-${requestId}`);
                        if (status === 'approved') {
                            // Update Accept button to Approved and disable both
                            const acceptBtn = requestEl.querySelector('.btn-accept');
                            const rejectBtn = requestEl.querySelector('.btn-reject');
                            acceptBtn.textContent = 'Approved';
                            acceptBtn.disabled = true;
                            acceptBtn.style.backgroundColor = '#16a34a';
                            acceptBtn.style.color = '#ffffff';
                            rejectBtn.disabled = true;
                            acceptBtn.style.cursor = 'not-allowed';
                            acceptBtn.style.opacity = '0.7';
                            // decrement pending count display
                            updatePendingRequestsCount(-1);
                        } else if (status === 'rejected') {
                            const rejectBtn = requestEl.querySelector('.btn-reject');
                            const acceptBtn = requestEl.querySelector('.btn-accept');
                            rejectBtn.textContent = 'Rejected';
                            rejectBtn.disabled = true;
                            rejectBtn.style.backgroundColor = '#ef4444';
                            rejectBtn.style.color = '#ffffff';
                            acceptBtn.disabled = true;
                            rejectBtn.style.cursor = 'not-allowed';
                            rejectBtn.style.opacity = '0.7';
                            updatePendingRequestsCount(-1);
                        }

                        // Show toast and remove the processed row after 3s, then try to fetch a replacement
                        showToast(status === 'approved' ? 'Request approved' : 'Request rejected', 'success');
                        setTimeout(() => {
                            const el = document.getElementById(`request-${requestId}`);
                            if (el) el.remove();
                            // attempt to fetch next pending to keep 10 items
                            fetchNextPending();
                        }, 3000);
                    } else {
                        alert('Error: ' + (data.message || 'Unable to update request'));
                    }
                })
                .catch(err => {
                    console.error('Network or JS error while updating request', err);
                    alert('Error updating request: ' + (err.message || err));
                });
            }

            function updatePendingRequestsCount(delta) {
                const pendingCountEl = document.querySelector('.stat-card.status-yellow .stat-value');
                if (!pendingCountEl) return;
                const current = parseInt(pendingCountEl.textContent) || 0;
                const next = Math.max(0, current + delta);
                pendingCountEl.textContent = next.toString();
            }

            // Fetch the next pending request not already displayed and append it
            function fetchNextPending() {
                const existing = Array.from(document.querySelectorAll('.request-item')).map(el => el.id.replace('request-', '')).join(',');
                const url = `/staff/book-requests/next?exclude=${existing}`;
                fetch(url, {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.request) {
                        const r = data.request;
                        const container = document.querySelector('.request-list');
                        if (!container) return;
                        const div = document.createElement('div');
                        div.className = 'request-item';
                        div.id = `request-${r.id}`;
                        div.innerHTML = `
                            <div class="request-info">
                                <div class="request-book">${escapeHtml(r.book.title)}</div>
                                <div class="request-student">${escapeHtml(r.student.name)} • ${escapeHtml(r.student.student_id)}</div>
                                <div class="request-date">Requested: ${r.request_date}</div>
                            </div>
                            <div class="request-status">
                                <div class="action-buttons">
                                    <button class="action-btn btn-accept" data-id="${r.id}" onclick="processBookRequest(${r.id}, 'approved', this)">Accept</button>
                                    <button class="action-btn btn-reject" data-id="${r.id}" onclick="processBookRequest(${r.id}, 'rejected', this)">Reject</button>
                                </div>
                            </div>
                        `;
                        container.appendChild(div);
                    }
                })
                .catch(err => console.error('Failed to fetch next pending', err));
            }
    </script>
@endpush
