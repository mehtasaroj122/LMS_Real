@extends('Staff.layouts.app')

@section('title', 'Student Details')

@push('styles')
<style>
    /* Student Details Page - FULLY RESPONSIVE */
    .student-details-page {
        padding: 16px;
        max-width: 1600px;
        margin: 0 auto;
        width: 100%;
        box-sizing: border-box;
    }

    /* Header Row */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid;
        flex-wrap: wrap;
        gap: 12px;
    }

    body.light-theme .page-header {
        border-color: #e5e7eb;
    }

    body.dark-theme .page-header {
        border-color: #334155;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    body.light-theme .breadcrumb {
        color: #64748b;
    }

    body.dark-theme .breadcrumb {
        color: #94a3b8;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
    }

    .breadcrumb-current {
        font-weight: 600;
        color: #3b82f6;
    }

    body.dark-theme .breadcrumb-current {
        color: #60a5fa;
    }

    .page-title {
        font-size: clamp(24px, 4vw, 28px);
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    /* Student Profile Card - Updated Design */
    .profile-section {
        margin-bottom: 24px;
    }

    .profile-card {
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        border: 1px solid;
        position: relative;
        overflow: hidden;
    }

    body.light-theme .profile-card {
        background-color: #ffffff;
        border-color: #e5e7eb;
    }

    body.dark-theme .profile-card {
        background-color: #1e293b;
        border-color: #334155;
    }

    .profile-header {
        padding: 20px;
        border-bottom: 1px solid;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }

    body.light-theme .profile-header {
        border-color: #e5e7eb;
    }

    body.dark-theme .profile-header {
        border-color: #334155;
    }

    .profile-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
    }

    .account-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-deactivate {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .btn-deactivate:hover {
        background-color: #fecaca;
    }

    body.dark-theme .btn-deactivate {
        background-color: #7f1d1d;
        color: #fca5a5;
    }

    body.dark-theme .btn-deactivate:hover {
        background-color: #991b1b;
    }

    .btn-activate {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .btn-activate:hover {
        background-color: #bbf7d0;
    }

    body.dark-theme .btn-activate {
        background-color: #14532d;
        color: #4ade80;
    }

    body.dark-theme .btn-activate:hover {
        background-color: #15803d;
    }

    .profile-content {
        padding: 20px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media (min-width: 768px) {
        .profile-grid {
            grid-template-columns: auto 1fr;
            gap: 30px;
            align-items: start;
        }
    }

    .profile-left {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 16px;
    }

    .student-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 600;
        margin: 0 auto;
    }

    body.light-theme .student-avatar {
        background-color: #3b82f6;
        color: white;
    }

    body.dark-theme .student-avatar {
        background-color: #1e40af;
        color: white;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .status-active {
        background-color: #dcfce7;
        color: #16a34a;
    }

    body.dark-theme .status-active {
        background-color: #14532d;
        color: #4ade80;
    }

    .status-inactive {
        background-color: #fee2e2;
        color: #dc2626;
    }

    body.dark-theme .status-inactive {
        background-color: #7f1d1d;
        color: #fca5a5;
    }

    .profile-details {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    @media (min-width: 640px) {
        .profile-details {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .profile-details {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .info-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .info-label {
        font-size: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    body.light-theme .info-label {
        color: #64748b;
    }

    body.dark-theme .info-label {
        color: #94a3b8;
    }

    .info-value {
        font-size: 16px;
        font-weight: 600;
        line-height: 1.4;
        word-break: break-word;
    }

    .info-value.address {
        white-space: pre-line;
        line-height: 1.6;
    }

    /* Summary Cards Grid */
    .summary-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .summary-card {
        border-radius: 8px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid;
        min-width: 0;
    }

    body.light-theme .summary-card {
        background-color: #ffffff;
        border-color: #e5e7eb;
    }

    body.dark-theme .summary-card {
        background-color: #1e293b;
        border-color: #334155;
    }

    .summary-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    body.light-theme .summary-icon {
        background-color: #f8fafc;
        color: #3b82f6;
    }

    body.dark-theme .summary-icon {
        background-color: #0f172a;
        color: #60a5fa;
    }

    .summary-content {
        flex: 1;
        min-width: 0;
        overflow: hidden;
    }

    .summary-title {
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    body.light-theme .summary-title {
        color: #64748b;
    }

    body.dark-theme .summary-title {
        color: #94a3b8;
    }

    .summary-value {
        font-size: 20px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Issued Books Section */
    .issued-books-section {
        border-radius: 8px;
        padding: 16px;
        border: 1px solid;
        margin-top: 24px;
    }

    body.light-theme .issued-books-section {
        background-color: #ffffff;
        border-color: #e5e7eb;
    }

    body.dark-theme .issued-books-section {
        background-color: #1e293b;
        border-color: #334155;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .section-header h3 {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        line-height: 1.2;
    }

    .section-controls {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .search-container {
        position: relative;
        min-width: 150px;
        flex: 1;
    }

    .search-container svg {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        z-index: 1;
    }

    body.light-theme .search-container svg {
        color: #94a3b8;
    }

    body.dark-theme .search-container svg {
        color: #64748b;
    }

    #bookSearch {
        width: 100%;
        padding: 8px 10px 8px 32px;
        border-radius: 6px;
        font-size: 14px;
        border: 1px solid;
        box-sizing: border-box;
    }

    body.light-theme #bookSearch {
        background-color: #ffffff;
        border-color: #e5e7eb;
        color: #0f172a;
    }

    body.dark-theme #bookSearch {
        background-color: #0f172a;
        border-color: #334155;
        color: #e2e8f0;
    }

    #bookSearch:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .status-filter {
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 14px;
        border: 1px solid;
        background-color: transparent;
        min-width: 120px;
        box-sizing: border-box;
    }

    body.light-theme .status-filter {
        color: #0f172a;
        border-color: #e5e7eb;
        background-color: #ffffff;
    }

    body.dark-theme .status-filter {
        color: #e2e8f0;
        border-color: #334155;
        background-color: #0f172a;
    }

    /* Books Table */
    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0 -16px;
        padding: 0 16px;
    }

    .books-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .books-table th {
        padding: 12px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
        border-bottom: 1px solid;
    }

    body.light-theme .books-table th {
        color: #64748b;
        border-color: #e5e7eb;
        background-color: #f8fafc;
    }

    body.dark-theme .books-table th {
        color: #94a3b8;
        border-color: #334155;
        background-color: #0f172a;
    }

    .books-table td {
        padding: 16px;
        border-bottom: 1px solid;
        font-size: 14px;
    }

    body.light-theme .books-table td {
        border-color: #f1f5f9;
    }

    body.dark-theme .books-table td {
        border-color: #1e293b;
    }

    .books-table tbody tr:hover {
        transition: background-color 0.2s ease;
    }

    body.light-theme .books-table tbody tr:hover {
        background-color: #f8fafc;
    }

    body.dark-theme .books-table tbody tr:hover {
        background-color: #0f172a;
    }

    /* Status Badges in Table */
    .table-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        display: inline-block;
        white-space: nowrap;
    }

    .table-badge.issued {
        background-color: #dcfce7;
        color: #16a34a;
    }

    body.dark-theme .table-badge.issued {
        background-color: #14532d;
        color: #4ade80;
    }

    .table-badge.overdue {
        background-color: #fef2f2;
        color: #dc2626;
    }

    body.dark-theme .table-badge.overdue {
        background-color: #7f1d1d;
        color: #fca5a5;
    }

    .table-badge.returned {
        background-color: #f8fafc;
        color: #64748b;
    }

    body.dark-theme .table-badge.returned {
        background-color: #334155;
        color: #94a3b8;
    }

    /* Fine Amount */
    .fine-amount {
        font-weight: 600;
        white-space: nowrap;
    }

    .fine-amount.has-fine {
        color: #dc2626;
    }

    body.dark-theme .fine-amount.has-fine {
        color: #f87171;
    }

    /* Action Button */
    .view-btn {
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid;
        background: none;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    body.light-theme .view-btn {
        border-color: #e5e7eb;
        color: #3b82f6;
    }

    body.dark-theme .view-btn {
        border-color: #334155;
        color: #60a5fa;
    }

    body.light-theme .view-btn:hover {
        background-color: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    body.dark-theme .view-btn:hover {
        background-color: #1e40af;
        color: white;
        border-color: #1e40af;
    }

    /* Responsive Breakpoints */
    @media (max-width: 768px) {
        .student-details-page {
            padding: 12px;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .profile-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .profile-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .profile-left {
            text-align: left;
            flex-direction: row;
            align-items: center;
            gap: 16px;
        }

        .student-avatar {
            width: 80px;
            height: 80px;
            font-size: 24px;
            margin: 0;
        }

        .summary-cards-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .section-controls {
            width: 100%;
        }

        .search-container {
            width: 100%;
        }

        .table-container {
            margin: 0 -12px;
            padding: 0 12px;
        }
    }

    @media (max-width: 480px) {
        .student-details-page {
            padding: 8px;
        }

        .profile-content {
            padding: 16px;
        }

        .profile-details {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .summary-cards-grid {
            grid-template-columns: 1fr;
        }

        .issued-books-section {
            padding: 12px;
        }

        .books-table td,
        .books-table th {
            padding: 12px;
            font-size: 13px;
        }
    }

    /* Toast Notification */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 350px;
    }

    .toast {
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .toast-success {
        background-color: #10b981;
        color: white;
    }

    .toast-error {
        background-color: #ef4444;
        color: white;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="student-details-page" data-student-id="{{ $student->id }}">
    <!-- Header Row -->
    <div class="page-header">
        <div class="header-left">
            <div class="breadcrumb">
                <a href="{{ route('staff.students.index') }}" class="text-secondary hover:text-primary">
                    Students
                </a>
                <span class="breadcrumb-separator">›</span>
                <span class="breadcrumb-current">Student Details</span>
            </div>
            <h1 class="page-title">Student Details</h1>
        </div>
    </div>

    <!-- Student Profile Section -->
    <div class="profile-section">
        <div class="profile-card">
            <div class="profile-header">
                <h2 class="profile-title">Student Profile</h2>
                <button class="account-btn {{ $student->user->status === 'active' ? 'btn-deactivate' : 'btn-activate' }}" 
                        id="deactivateAccountBtn">
                    @if($student->user->status === 'active')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/>
                            <line x1="12" y1="2" x2="12" y2="12"/>
                        </svg>
                        Deactivate account
                    @else
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/>
                            <line x1="12" y1="12" x2="12" y2="2"/>
                        </svg>
                        Activate Account
                    @endif
                </button>
            </div>
            
            <div class="profile-content">
                <div class="profile-grid">
                    <div class="profile-left">
                        @if($student->user->profile_photo)
                            <div class="student-avatar">
                                <img src="{{ str_starts_with($student->user->profile_photo, 'http') ? $student->user->profile_photo : asset('storage/' . $student->user->profile_photo) }}" alt="{{ $student->user->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        @else
                            <div class="student-avatar">
                                {{ substr($student->user->name, 0, 1) }}{{ strpos($student->user->name, ' ') !== false ? substr($student->user->name, strpos($student->user->name, ' ') + 1, 1) : '' }}
                            </div>
                        @endif
                        <div class="status-badge {{ $student->user->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ ucfirst($student->user->status) }}
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        <div class="info-group">
                            <span class="info-label">Full Name</span>
                            <span class="info-value">{{ $student->user->name }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ $student->user->email }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Student ID</span>
                            <span class="info-value">{{ $student->roll_no }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Phone Number</span>
                            <span class="info-value">{{ $student->user->phone ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Department</span>
                            <span class="info-value">{{ $student->department->name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Enrollment Year</span>
                            <span class="info-value">{{ $student->batch ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="info-group">
                            <span class="info-label">Address</span>
                            <span class="info-value address">{{ $student->address ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards-grid">
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                </svg>
            </div>
            <div class="summary-content">
                <div class="summary-title">Total Issued</div>
                <div class="summary-value">{{ $student->issuedBooks->count() }}</div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="summary-content">
                <div class="summary-title">Currently Issued</div>
                <div class="summary-value">{{ $student->issuedBooks->whereNull('return_date')->count() }}</div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div class="summary-content">
                <div class="summary-title">Overdue</div>
                <div class="summary-value">
                    {{ $student->issuedBooks->where('return_date', null)->where('due_date', '<', now()->toDateString())->count() }}
                </div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="summary-content">
                <div class="summary-title">Pending Fine</div>
                <div class="summary-value" id="pendingFineValue">
                    ₹{{ $student->fines->where('status', 'pending')->sum('amount') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Issued Books Section -->
    <div class="issued-books-section">
        <div class="section-header">
            <h3>Issued Books</h3>
            <div class="section-controls">
                <div class="search-container">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text" id="bookSearch" placeholder="Search books...">
                </div>
                <select id="statusFilter" class="status-filter">
                    <option value="all">All Status</option>
                    <option value="issued">Issued</option>
                    <option value="overdue">Overdue</option>
                    <option value="returned">Returned</option>
                </select>
            </div>
        </div>

        <!-- Books Table -->
        <div class="table-container">
            <table class="books-table">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>ISBN</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="booksTableBody">
                    <!-- Data will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Toast Notification Container -->
<div id="toastContainer" class="toast-container"></div>
@endsection

@push('scripts')
<script>
    // Get data from PHP/Blade
    const studentIssuedBooks = {!! json_encode(
        $student->issuedBooks->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->book->title ?? 'Unknown',
                    'isbn' => $book->book->isbn ?? 'N/A',
                    'issueDate' => $book->issue_date ? \Carbon\Carbon::parse($book->issue_date)->format('M d, Y') : 'N/A',
                    'dueDate' => $book->due_date ? \Carbon\Carbon::parse($book->due_date)->format('M d, Y') : 'N/A',
                    'returnDate' => $book->return_date ? \Carbon\Carbon::parse($book->return_date)->format('M d, Y') : '-',
                    'status' => $book->return_date
                        ? 'returned'
                        : (\Carbon\Carbon::parse($book->due_date)->toDateString() < now()->toDateString()
                            ? 'overdue'
                            : 'issued'),
                    'fine' => $book->fine_amount ?? 0,
                ];
            })->toArray(),
    ) !!};

    // Sample data for books (fallback)
    const booksData = studentIssuedBooks.length > 0 ? studentIssuedBooks : [
        {
            id: 1,
            title: "Introduction to Algorithms",
            isbn: "978-0262033848",
            issueDate: "Dec 15, 2024",
            dueDate: "Dec 29, 2024",
            returnDate: "-",
            status: "issued",
            fine: 0
        },
        {
            id: 2,
            title: "Clean Code",
            isbn: "978-0132350884",
            issueDate: "Dec 10, 2024",
            dueDate: "Dec 24, 2024",
            returnDate: "-",
            status: "overdue",
            fine: 45
        },
        {
            id: 3,
            title: "The Pragmatic Programmer",
            isbn: "978-0135957059",
            issueDate: "Dec 20, 2024",
            dueDate: "Jan 3, 2025",
            returnDate: "-",
            status: "issued",
            fine: 0
        },
        {
            id: 4,
            title: "Design Patterns",
            isbn: "978-0201633612",
            issueDate: "Nov 20, 2024",
            dueDate: "Dec 4, 2024",
            returnDate: "Dec 3, 2024",
            status: "returned",
            fine: 0
        }
    ];

    // DOM Elements
    const bookSearchInput = document.getElementById('bookSearch');
    const statusFilterSelect = document.getElementById('statusFilter');
    const booksTableBody = document.getElementById('booksTableBody');
    const deactivateAccountBtn = document.getElementById('deactivateAccountBtn');
    let pendingFineValueElement = null;

    // Student ID
    const studentId = document.querySelector('[data-student-id]')?.getAttribute('data-student-id');

    // Filter and search variables
    let filteredBooks = [...booksData];
    let currentSearchTerm = '';
    let currentStatusFilter = 'all';

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Get pending fine element
        pendingFineValueElement = document.getElementById('pendingFineValue');
        
        // Setup event listeners
        setupEventListeners();
        
        // Render initial table
        renderBooksTable();
        
        // Setup keyboard navigation
        setupKeyboardNavigation();
        
        console.log('[StudentView] Page initialized');
    });

    // Setup event listeners
    function setupEventListeners() {
        // Search functionality
        if (bookSearchInput) {
            bookSearchInput.addEventListener('input', handleBookSearch);
        }

        // Status filter
        if (statusFilterSelect) {
            statusFilterSelect.addEventListener('change', handleStatusFilter);
        }

        // Deactivate/Activate account button
        if (deactivateAccountBtn) {
            deactivateAccountBtn.addEventListener('click', handleAccountStatus);
        }
    }

    // Keyboard navigation
    function setupKeyboardNavigation() {
        document.addEventListener('keydown', function(e) {
            // Ctrl+F to focus search
            if ((e.ctrlKey && e.key === 'f') || e.key === '/') {
                e.preventDefault();
                if (bookSearchInput) {
                    bookSearchInput.focus();
                    bookSearchInput.select();
                }
            }

            // Escape to clear search
            if (e.key === 'Escape' && document.activeElement === bookSearchInput) {
                clearBookSearch();
            }
        });
    }

    // Handle book search
    function handleBookSearch() {
        currentSearchTerm = bookSearchInput.value.toLowerCase().trim();
        applyFilters();
    }

    // Handle status filter
    function handleStatusFilter() {
        currentStatusFilter = statusFilterSelect.value;
        applyFilters();
    }

    // Apply both filters
    function applyFilters() {
        filteredBooks = booksData.filter(book => {
            // Apply search filter
            const matchesSearch = !currentSearchTerm ||
                book.title.toLowerCase().includes(currentSearchTerm) ||
                book.isbn.toLowerCase().includes(currentSearchTerm);

            // Apply status filter
            const matchesStatus = currentStatusFilter === 'all' ||
                book.status === currentStatusFilter;

            return matchesSearch && matchesStatus;
        });

        renderBooksTable();
    }

    // Clear search
    function clearBookSearch() {
        bookSearchInput.value = '';
        currentSearchTerm = '';
        applyFilters();
    }

    // Render books table
    function renderBooksTable() {
        if (!booksTableBody) return;

        booksTableBody.innerHTML = '';

        if (filteredBooks.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = `
                <td colspan="8" style="text-align: center; padding: 40px; color: #64748b;">
                    No books found matching your criteria.
                </td>
            `;
            booksTableBody.appendChild(emptyRow);
            return;
        }

        filteredBooks.forEach(book => {
            const row = document.createElement('tr');

            // Get status badge class and text
            let statusClass = '';
            let statusText = '';
            switch (book.status) {
                case 'issued':
                    statusClass = 'issued';
                    statusText = 'Issued';
                    break;
                case 'overdue':
                    statusClass = 'overdue';
                    statusText = 'Overdue';
                    break;
                case 'returned':
                    statusClass = 'returned';
                    statusText = 'Returned';
                    break;
            }

            row.innerHTML = `
                <td>
                    <strong>${book.title}</strong>
                </td>
                <td>${book.isbn}</td>
                <td>${book.issueDate}</td>
                <td>${book.dueDate}</td>
                <td>${book.returnDate}</td>
                <td>
                    <span class="table-badge ${statusClass}">${statusText}</span>
                </td>
                <td>
                    <span class="fine-amount ${book.fine > 0 ? 'has-fine' : ''}">
                        ₹${book.fine}
                    </span>
                </td>
                <td>
                    <button class="view-btn" onclick="viewBookDetails(${book.id})">
                        View
                    </button>
                </td>
            `;

            booksTableBody.appendChild(row);
        });
    }

    // Handle account status change
    function handleAccountStatus() {
        const isCurrentlyActive = deactivateAccountBtn.innerHTML.includes('Deactivate account');
        const action = isCurrentlyActive ? 'deactivate' : 'activate';
        const confirmMessage = isCurrentlyActive ?
            'Are you sure you want to deactivate this student account? The student will not be able to access the system.' :
            'Are you sure you want to activate this student account? The student will be able to access the system again.';

        if (confirm(confirmMessage)) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '{{ csrf_token() }}';

            // Determine the endpoint based on current status
            const endpoint = isCurrentlyActive ?
                `/staff/students/${studentId}/deactivate` :
                `/staff/students/${studentId}/activate`;

            fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button text and color
                        if (isCurrentlyActive) {
                            deactivateAccountBtn.classList.remove('btn-deactivate');
                            deactivateAccountBtn.classList.add('btn-activate');
                            deactivateAccountBtn.innerHTML = `
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/>
                                    <line x1="12" y1="12" x2="12" y2="2"/>
                                </svg>
                                Activate Account
                            `;
                            showToast('Account deactivated successfully!', 'warning');

                            // Update status badge
                            const statusBadge = document.querySelector('.status-badge');
                            if (statusBadge) {
                                statusBadge.classList.remove('status-active');
                                statusBadge.classList.add('status-inactive');
                                statusBadge.textContent = 'Inactive';
                            }
                        } else {
                            deactivateAccountBtn.classList.remove('btn-activate');
                            deactivateAccountBtn.classList.add('btn-deactivate');
                            deactivateAccountBtn.innerHTML = `
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18.36 6.64a9 9 0 1 1-12.73 0"/>
                                    <line x1="12" y1="2" x2="12" y2="12"/>
                                </svg>
                                Deactivate account
                            `;
                            showToast('Account activated successfully!', 'success');

                            // Update status badge
                            const statusBadge = document.querySelector('.status-badge');
                            if (statusBadge) {
                                statusBadge.classList.remove('status-inactive');
                                statusBadge.classList.add('status-active');
                                statusBadge.textContent = 'Active';
                            }
                        }
                    } else {
                        showToast(data.message || `Failed to ${action} account.`, 'error');
                    }
                })
                .catch(error => {
                    console.error(`Error ${action}ing account:`, error);
                    showToast(`An error occurred while ${action}ing account.`, 'error');
                });
        }
    }

    // View book details
    function viewBookDetails(bookId) {
        console.log(`Viewing book details: ${bookId}`);
        // In a real application, this would navigate to book details
        showToast(`Viewing details for book ID: ${bookId}`, 'info');
    }

    // Toast notification system
    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        let iconSVG = '';
        switch (type) {
            case 'success':
                iconSVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>`;
                break;
            case 'error':
                iconSVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>`;
                break;
            default:
                iconSVG = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>`;
        }

        toast.innerHTML = `
            <div class="toast-icon">${iconSVG}</div>
            <div>${message}</div>
        `;

        toastContainer.appendChild(toast);

        // Remove toast after animation
        setTimeout(() => {
            if (toast.parentNode === toastContainer) {
                toastContainer.removeChild(toast);
            }
        }, 3000);
    }
</script>
@endpush