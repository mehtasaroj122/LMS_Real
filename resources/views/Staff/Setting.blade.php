@extends('Staff.layouts.app')

@section('title', 'Settings')

@push('styles')
    <style>
        /* Settings Page Specific Styles */
        .settings-container {
            --settings-sticky-top: 0.5rem;
            width: 100%;
            max-width: none;
            margin: -0.25rem 0 0;
            padding: 0;
        }

        .settings-header {
            margin-bottom: 1.5rem;
        }

        .settings-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .settings-header p {
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Two Column Layout */
        .settings-grid {
            display: grid;
            grid-template-columns: minmax(280px, 320px) minmax(0, 1fr);
            gap: 20px;
            align-items: start;
            width: 100%;
        }

        /* Left Profile Card */
        .profile-card {
            border-radius: 12px;
            transition: background-color 0.3s ease, color 0.3s ease;
            height: fit-content;
            position: sticky;
            top: var(--settings-sticky-top);
            align-self: start;
        }

        body.light-theme .profile-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .profile-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .profile-card-content {
            padding: 1.5rem;
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1.25rem;
            overflow: hidden;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        body.dark-theme .profile-avatar {
            background-color: #334155;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar .avatar-placeholder {
            font-size: 2.5rem;
            font-weight: 600;
            color: #6b7280;
        }

        body.dark-theme .profile-avatar .avatar-placeholder {
            color: #9ca3af;
        }

        .profile-info h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }

        .profile-info p {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        body.dark-theme .profile-info p {
            color: #94a3b8;
        }

        .role-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        body.dark-theme .role-badge {
            background-color: #1e3a8a;
            color: #93c5fd;
        }

        .profile-stats {
            border-top: 1px solid;
            padding-top: 1.25rem;
        }

        body.light-theme .profile-stats {
            border-color: #e5e7eb;
        }

        body.dark-theme .profile-stats {
            border-color: #334155;
        }

        .stat-item {
            margin-bottom: 0.875rem;
            text-align: left;
        }

        .stat-item:last-child {
            margin-bottom: 0;
        }

        .stat-label {
            font-size: 0.8125rem;
            color: #64748b;
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }

        body.dark-theme .stat-label {
            color: #94a3b8;
        }

        .stat-value {
            font-size: 0.9375rem;
            font-weight: 500;
            line-height: 1.3;
        }

        /* Right Settings Card */
        .settings-card {
            border-radius: 12px;
            transition: background-color 0.3s ease, color 0.3s ease;
            min-width: 0;
        }

        body.light-theme .settings-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        body.dark-theme .settings-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .settings-card-content {
            padding: 1.5rem;
        }

        /* Tabs Navigation */
        .settings-tabs {
            display: flex;
            gap: 0.25rem;
            border-bottom: 1px solid;
            margin-bottom: 1.5rem;
            padding-bottom: 0;
        }

        body.light-theme .settings-tabs {
            border-color: #e5e7eb;
        }

        body.dark-theme .settings-tabs {
            border-color: #334155;
        }

        .tab-btn {
            padding: 0.75rem 1.25rem;
            background: transparent;
            border: none;
            border-radius: 6px 6px 0 0;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            top: 1px;
        }

        body.light-theme .tab-btn {
            color: #64748b;
        }

        body.dark-theme .tab-btn {
            color: #94a3b8;
        }

        .tab-btn:hover {
            background-color: rgba(59, 130, 246, 0.05);
        }

        body.dark-theme .tab-btn:hover {
            background-color: rgba(96, 165, 250, 0.05);
        }

        .tab-btn.active {
            background-color: #ffffff;
            color: #1e40af;
            border: 1px solid #e5e7eb;
            border-bottom-color: transparent;
        }

        body.dark-theme .tab-btn.active {
            background-color: #1e293b;
            color: #60a5fa;
            border-color: #334155;
            border-bottom-color: #1e293b;
        }

        .tab-btn i {
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        /* Profile Section Header */
        .section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 1rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .section-header-main {
            flex: 1;
            min-width: 0;
        }

        .section-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .section-header p {
            color: #64748b;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        body.dark-theme .section-header p {
            color: #94a3b8;
        }

        body.dark-theme .section-header {
            border-bottom-color: rgba(148, 163, 184, 0.16);
        }

        .section-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .section-status {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: #f8fafc;
            color: #64748b;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        body.dark-theme .section-status {
            background: rgba(15, 23, 42, 0.72);
            color: #94a3b8;
        }

        .section-status.editing {
            background: rgba(59, 130, 246, 0.12);
            color: #2563eb;
        }

        body.dark-theme .section-status.editing {
            background: rgba(96, 165, 250, 0.18);
            color: #93c5fd;
        }

        /* Form Styles */
        .settings-form {
            max-width: 100%;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        body.light-theme .form-label {
            color: #374151;
        }

        body.dark-theme .form-label {
            color: #e5e7eb;
        }

        .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid;
            border-radius: 6px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            height: 40px;
            line-height: 1.3;
        }

        body.light-theme .form-input {
            background-color: #ffffff;
            border-color: #d1d5db;
            color: #111827;
        }

        body.dark-theme .form-input {
            background-color: #0f172a;
            border-color: #475569;
            color: #f8fafc;
        }

        body.light-theme .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }

        body.dark-theme .form-input:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.1);
        }

        .form-input:disabled {
            background-color: #f9fafb;
            cursor: not-allowed;
        }

        #department.form-input:disabled {
            cursor: default;
        }

        body.dark-theme .form-input:disabled {
            background-color: #1e293b;
            opacity: 0.6;
        }

        textarea.form-input {
            height: auto;
            min-height: 80px;
            resize: vertical;
            line-height: 1.4;
        }

        /* Input with icon (password toggle) */
        .input-with-icon { position: relative; }
        .pwd-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: #64748b;
            font-size: 0.95rem;
            padding: 4px;
        }
        .pwd-toggle:focus { outline: none; }

        .required::after {
            content: " *";
            color: #ef4444;
        }

        .form-hint {
            font-size: 0.75rem;
            margin-top: 0.375rem;
            line-height: 1.3;
        }

        body.light-theme .form-hint {
            color: #6b7280;
        }

        body.dark-theme .form-hint {
            color: #9ca3af;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            line-height: 1.3;
        }

        .btn-primary {
            background-color: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        body.dark-theme .btn-primary {
            background-color: #2563eb;
        }

        body.dark-theme .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background-color: #d1d5db;
        }

        body.dark-theme .btn-secondary {
            background-color: #334155;
            color: #e5e7eb;
        }

        body.dark-theme .btn-secondary:hover {
            background-color: #475569;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid;
        }

        body.light-theme .btn-outline {
            border-color: #d1d5db;
            color: #374151;
        }

        body.dark-theme .btn-outline {
            border-color: #475569;
            color: #e5e7eb;
        }

        .btn-outline:hover {
            background-color: #f9fafb;
        }

        body.dark-theme .btn-outline:hover {
            background-color: #1e293b;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn i {
            font-size: 0.8125rem;
        }

        /* Photo Upload Section */
        .photo-upload-container {
            padding: 0.5rem 0;
        }

        .photo-preview {
            width: 180px;
            height: 180px;
            border-radius: 8px;
            margin: 0 auto 1.5rem;
            overflow: hidden;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        body.dark-theme .photo-preview {
            background-color: #334155;
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-upload-icon {
            font-size: 2.5rem;
            color: #9ca3af;
        }

        body.dark-theme .photo-upload-icon {
            color: #6b7280;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1.25rem;
        }

        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-input-label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background-color: #f3f4f6;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        body.dark-theme .file-input-label {
            background-color: #334155;
            color: #e5e7eb;
        }

        .file-input-label:hover {
            background-color: #e5e7eb;
        }

        body.dark-theme .file-input-label:hover {
            background-color: #475569;
        }

        #fileName {
            margin-left: 0.75rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        body.dark-theme #fileName {
            color: #94a3b8;
        }

        .upload-requirements {
            max-width: 400px;
            margin: 0 auto;
            text-align: left;
        }

        .upload-requirements h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.375rem;
        }

        .upload-requirements p {
            font-size: 0.8125rem;
            color: #64748b;
            line-height: 1.3;
        }

        body.dark-theme .upload-requirements p {
            color: #94a3b8;
        }

        /* Security Section */
        .security-section {
            max-width: 100%;
            padding: 0.5rem 0;
        }

        .password-intro {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            text-align: center;
            padding: 2.75rem 1rem 1.25rem;
        }

        .password-intro-icon {
            width: 84px;
            height: 84px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #1e293b;
            color: #cbd5e1;
            font-size: 2rem;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.16);
        }

        body.dark-theme .password-intro-icon {
            background: #0f172a;
            color: #e2e8f0;
        }

        .password-intro-copy {
            max-width: 34rem;
            font-size: 1rem;
            line-height: 1.6;
            color: #64748b;
        }

        body.dark-theme .password-intro-copy {
            color: #94a3b8;
        }

        .password-intro-action {
            min-width: 210px;
            justify-content: center;
            padding: 0.9rem 1.65rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
        }

        .password-requirements {
            margin-top: 1.5rem;
            padding: 1.25rem;
            border-radius: 8px;
            background-color: #f8fafc;
        }

        body.dark-theme .password-requirements {
            background-color: #1e293b;
        }

        .password-requirements h4 {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.875rem;
        }

        .requirement-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .requirement-list li {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 0.625rem;
            font-size: 0.8125rem;
            line-height: 1.3;
        }

        .requirement-list li:last-child {
            margin-bottom: 0;
        }

        .requirement-list li i {
            font-size: 0.75rem;
        }

        .requirement-list li.valid i {
            color: #10b981;
        }

        .requirement-list li.invalid i {
            color: #ef4444;
        }

        .field-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.375rem;
        }

        .char-counter {
            font-size: 0.75rem;
            color: #64748b;
            white-space: nowrap;
        }

        body.dark-theme .char-counter {
            color: #94a3b8;
        }

        .char-counter.is-limit {
            color: #ef4444;
        }

        .form-input.is-invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .form-input.is-valid {
            border-color: #10b981 !important;
            background-color: #f0fdf4 !important;
        }

        body.dark-theme .form-input.is-invalid {
            background-color: rgba(127, 29, 29, 0.25) !important;
        }

        body.dark-theme .form-input.is-valid {
            background-color: rgba(6, 95, 70, 0.22) !important;
        }

        .field-error {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.375rem;
            display: none;
        }

        .field-error.visible {
            display: block;
        }

        .form-input.validating {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24'%3E%3Cg fill='none' stroke='%233b82f6' stroke-linecap='round' stroke-width='2'%3E%3Cpath stroke-opacity='.25' d='M12 3a9 9 0 1 0 9 9'/%3E%3Cpath d='M21 12A9 9 0 0 0 12 3'%3E%3CanimateTransform attributeName='transform' attributeType='XML' dur='0.8s' from='0 12 12' repeatCount='indefinite' to='360 12 12' type='rotate'/%3E%3C/path%3E%3C/g%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 35px center;
            background-size: 18px 18px;
        }

        .tab-content {
            display: none;
            transform-origin: top center;
        }

        .tab-content.active {
            display: block;
            animation: staffTabFadeIn 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }

        @keyframes staffTabFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .top-edit-button {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.25rem;
        }

        .settings-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 0.875rem 1.25rem;
            border-radius: 10px;
            color: #ffffff;
            font-weight: 500;
            font-size: 0.875rem;
            z-index: 1100;
            max-width: 320px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
            animation: staffSettingsSlideIn 0.25s ease;
        }

        .settings-toast.success {
            background-color: #10b981;
        }

        .settings-toast.error {
            background-color: #ef4444;
        }

        .settings-toast.info {
            background-color: #3b82f6;
        }

        @keyframes staffSettingsSlideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes staffSettingsSlideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .profile-card {
                position: static;
                top: auto;
            }

            .profile-card-content {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .profile-stats {
                width: 100%;
                max-width: 300px;
            }
        }

        @media (max-width: 640px) {
            .settings-container {
                padding: 0;
            }

            .settings-grid {
                gap: 1rem;
            }

            .settings-card-content,
            .profile-card-content {
                padding: 1.25rem;
            }

            .field-meta {
                flex-direction: column;
                align-items: flex-start;
            }

            .section-header {
                flex-direction: column;
            }

            .section-header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }

            .top-edit-button .btn {
                width: auto;
            }

            .settings-toast {
                left: 16px;
                right: 16px;
                max-width: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .tab-content.active {
                animation: none;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $profileFields = ['name', 'email', 'phone', 'address'];
        $securityFields = ['current_password', 'password', 'password_confirmation'];
        $profileHasErrors = $errors->hasAny($profileFields);
        $photoHasErrors = $errors->has('profile_photo');
        $securityHasErrors = $errors->hasAny($securityFields);
        $activeTab = $securityHasErrors ? 'security' : ($photoHasErrors ? 'photo' : 'profile');
        $profilePhotoUrl = isset($user) && $user->profile_photo
            ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo))
            : null;
        $departmentName = old('department', $user->staff?->department?->name ?? '');
        $usernameValue = $user->username ?? (($user->email ?? null) ? explode('@', $user->email)[0] : 'staff1');
        $lastLoginValue = optional($user->last_login_at)->format('n/j/Y') ?? now()->format('n/j/Y');
        $addressValue = old('address', $user->address ?? '');
        $avatarInitial = collect(preg_split('/\s+/', trim($user->name ?? '')) ?: [])
            ->filter()
            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
            ->first();
        $avatarInitial = $avatarInitial ?: 'S';
    @endphp

    <div class="settings-container" id="staffSettingsPage">
        <div class="settings-header">
            <h1>Settings</h1>
            <p>Manage your account settings and preferences</p>
        </div>

        <div class="settings-grid">
            <div class="profile-card">
                <div class="profile-card-content">
                    <div class="profile-avatar" id="profileAvatar" data-default-initial="{{ $avatarInitial }}">
                        @if ($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="Profile Photo" id="avatarImage">
                        @else
                            <div class="avatar-placeholder">{{ $avatarInitial }}</div>
                        @endif
                    </div>

                    <div class="profile-info">
                        <h3 id="userName">{{ $user->name ?? 'John Librarian' }}</h3>
                        <p id="userEmail">{{ $user->email ?? 'staff1@library.edu' }}</p>
                        <span class="role-badge">{{ ucfirst($user->role ?? 'staff') }}</span>
                    </div>

                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-label">Username</div>
                            <div class="stat-value" id="username">{{ $usernameValue }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Member Since</div>
                            <div class="stat-value" id="memberSince">{{ optional($user->created_at)->format('n/j/Y') ?? '1/15/2024' }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Last Login</div>
                            <div class="stat-value" id="lastLogin">{{ $lastLoginValue }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="settings-card">
                <div class="settings-card-content">
                    <div class="settings-tabs" role="tablist" aria-label="Settings sections">
                        <button type="button" class="tab-btn {{ $activeTab === 'profile' ? 'active' : '' }}" data-tab="profile" role="tab" aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}">
                            <i class="fas fa-user"></i> Profile
                        </button>
                        <button type="button" class="tab-btn {{ $activeTab === 'photo' ? 'active' : '' }}" data-tab="photo" role="tab" aria-selected="{{ $activeTab === 'photo' ? 'true' : 'false' }}">
                            <i class="fas fa-camera"></i> Photo
                        </button>
                        <button type="button" class="tab-btn {{ $activeTab === 'security' ? 'active' : '' }}" data-tab="security" role="tab" aria-selected="{{ $activeTab === 'security' ? 'true' : 'false' }}">
                            <i class="fas fa-lock"></i> Security
                        </button>
                    </div>

                    <div class="tab-content {{ $activeTab === 'profile' ? 'active' : '' }}" id="profileTab" role="tabpanel">
                        <div class="section-header">
                            <div class="section-header-main">
                                <h2>Personal Information</h2>
                                <p>Update your personal details</p>
                            </div>
                            <div class="section-header-actions">
                                <span class="section-status" id="profileSectionStatus">Read only</span>
                                <button type="button" class="btn btn-secondary" id="editProfileBtn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-outline" id="cancelProfileBtn" hidden>
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary" id="saveProfileBtn" form="profileForm" hidden>
                                    <i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        </div>

                        <form id="profileForm" class="settings-form" method="POST" action="{{ route('staff.settings.update') }}" enctype="multipart/form-data" novalidate data-check-email-url="{{ route('staff.settings.check-email') }}">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="name" class="form-label required">Full Name</label>
                                <input type="text" id="name" name="name" data-validate="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" aria-describedby="error_name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                                <div class="form-hint">Your full name as it appears in the system</div>
                                <div class="field-error @error('name') visible @enderror" id="error_name" aria-live="polite">@error('name'){{ $message }}@enderror</div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" id="email" name="email" data-validate="email" class="form-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" aria-describedby="error_email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                                <div class="form-hint">This is your login email</div>
                                <div class="field-error @error('email') visible @enderror" id="error_email" aria-live="polite">@error('email'){{ $message }}@enderror</div>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" data-validate="phone" class="form-input @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone ?? '') }}" aria-describedby="error_phone" aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}">
                                <div class="form-hint">Your contact phone number</div>
                                <div class="field-error @error('phone') visible @enderror" id="error_phone" aria-live="polite">@error('phone'){{ $message }}@enderror</div>
                            </div>

                            <div class="form-group">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" id="department" class="form-input" value="{{ $departmentName }}">
                                <div class="form-hint">Department is assigned by admin and cannot be edited here</div>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" data-validate="address" class="form-input @error('address') is-invalid @enderror" rows="4" maxlength="500" aria-describedby="error_address" aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}">{{ $addressValue }}</textarea>
                                <div class="field-meta">
                                    <div class="form-hint">Your mailing address</div>
                                    <div class="char-counter" id="addressCounter" aria-live="polite"><span id="addressCount">{{ mb_strlen((string) $addressValue) }}</span>/500</div>
                                </div>
                                <div class="field-error @error('address') visible @enderror" id="error_address" aria-live="polite">@error('address'){{ $message }}@enderror</div>
                            </div>
                        </form>
                    </div>

                    <div class="tab-content {{ $activeTab === 'photo' ? 'active' : '' }}" id="photoTab" role="tabpanel">
                        <div class="photo-upload-container">
                            <div class="section-header">
                                <h2>Profile Photo</h2>
                                <p>Upload and manage your profile photo</p>
                            </div>

                            <form id="photoForm" action="{{ route('staff.settings.photo') }}" method="POST" enctype="multipart/form-data" novalidate>
                                @csrf

                                <div class="photo-preview" id="photoPreview">
                                    @if ($profilePhotoUrl)
                                        <img src="{{ $profilePhotoUrl }}" alt="Preview" id="photoPreviewImg">
                                    @else
                                        <div class="photo-upload-icon" id="photoFallbackIcon">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <img src="" alt="Preview" id="photoPreviewImg" style="display: none;">
                                    @endif
                                </div>

                                <div class="file-input-wrapper">
                                    <input type="file" id="photoUpload" name="profile_photo" class="file-input" accept="image/*" aria-describedby="error_profile_photo">
                                    <label for="photoUpload" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i> Choose File
                                    </label>
                                    <span id="fileName">No file chosen</span>
                                </div>

                                <div class="field-error @error('profile_photo') visible @enderror" id="error_profile_photo" aria-live="polite">@error('profile_photo'){{ $message }}@enderror</div>

                                <div class="upload-requirements">
                                    <h4>Recommended:</h4>
                                    <p>Square image, at least 200x200px, max 2MB</p>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" class="btn btn-primary" id="uploadPhotoBtn" disabled>
                                        <i class="fas fa-upload"></i> Upload Photo
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="removePhotoBtn" {{ $profilePhotoUrl ? '' : 'disabled' }}>
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="tab-content {{ $activeTab === 'security' ? 'active' : '' }}" id="securityTab" role="tabpanel">
                        <div class="security-section">
                            <div class="section-header">
                                <div class="section-header-main">
                                    <h2>Change Password</h2>
                                    <p>Update your account password</p>
                                </div>
                                <div class="section-header-actions">
                                    <span class="section-status" id="passwordSectionStatus">Read only</span>
                                    <button type="button" class="btn btn-outline" id="cancelPasswordBtn" hidden>
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="updatePasswordBtn" form="passwordForm" hidden>
                                        <i class="fas fa-save"></i> Save
                                    </button>
                                </div>
                            </div>

                            <div class="password-intro" id="passwordIntro" {{ $securityHasErrors ? 'hidden' : '' }}>
                                <div class="password-intro-icon" aria-hidden="true">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <p class="password-intro-copy">Click "Change Password" to update your account password</p>
                                <button type="button" class="btn btn-primary password-intro-action" id="showPasswordFormBtn" aria-controls="passwordForm" aria-expanded="{{ $securityHasErrors ? 'true' : 'false' }}">
                                    Change Password
                                </button>
                            </div>

                            <form id="passwordForm" class="settings-form" method="POST" action="{{ route('staff.settings.password') }}" novalidate {{ $securityHasErrors ? '' : 'hidden' }}>
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="currentPassword" class="form-label required">Current Password</label>
                                    <div class="input-with-icon">
                                        <input type="password" id="currentPassword" name="current_password" class="form-input @error('current_password') is-invalid @enderror" placeholder="Enter current password" aria-describedby="error_current_password" aria-invalid="{{ $errors->has('current_password') ? 'true' : 'false' }}" autocomplete="current-password">
                                        <button type="button" class="pwd-toggle" data-target="currentPassword" aria-label="Toggle current password visibility">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                    <div class="field-error @error('current_password') visible @enderror" id="error_current_password" aria-live="polite">@error('current_password'){{ $message }}@enderror</div>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword" class="form-label required">New Password</label>
                                    <div class="input-with-icon">
                                        <input type="password" id="newPassword" name="password" class="form-input @error('password') is-invalid @enderror" placeholder="Enter new password" aria-describedby="error_password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" autocomplete="new-password">
                                        <button type="button" class="pwd-toggle" data-target="newPassword" aria-label="Toggle new password visibility">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                    <div class="field-error @error('password') visible @enderror" id="error_password" aria-live="polite">@error('password'){{ $message }}@enderror</div>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword" class="form-label required">Confirm New Password</label>
                                    <div class="input-with-icon">
                                        <input type="password" id="confirmPassword" name="password_confirmation" class="form-input @error('password_confirmation') is-invalid @enderror" placeholder="Confirm new password" aria-describedby="error_password_confirmation" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}" autocomplete="new-password">
                                        <button type="button" class="pwd-toggle" data-target="confirmPassword" aria-label="Toggle confirmation password visibility">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                    <div class="field-error @error('password_confirmation') visible @enderror" id="error_password_confirmation" aria-live="polite">@error('password_confirmation'){{ $message }}@enderror</div>
                                </div>

                                <div class="password-requirements">
                                    <h4>Password Requirements:</h4>
                                    <ul class="requirement-list">
                                        <li id="reqLength" class="invalid"><i class="far fa-circle"></i> At least 8 characters</li>
                                        <li id="reqUppercase" class="invalid"><i class="far fa-circle"></i> At least one uppercase letter</li>
                                        <li id="reqLowercase" class="invalid"><i class="far fa-circle"></i> At least one lowercase letter</li>
                                        <li id="reqNumber" class="invalid"><i class="far fa-circle"></i> At least one number</li>
                                        <li id="reqMatch" class="invalid"><i class="far fa-circle"></i> Password and confirmation must match</li>
                                        <li id="reqDifferent" class="invalid"><i class="far fa-circle"></i> New password must be different from current</li>
                                    </ul>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const StaffSettings = (() => {
            const state = {
                profileEditing: false,
                profileSubmitting: false,
                passwordSubmitting: false,
                emailPending: false,
                emailTimer: null,
                emailAbort: null,
                fieldTimers: {},
                profileErrors: {},
                passwordErrors: {},
                originalProfile: {},
                flashSuccess: @json(session('success')),
                flashError: @json(session('error')),
                startProfileEditing: @json($profileHasErrors),
                startPasswordEditing: @json($securityHasErrors),
            };
            const profileFields = ['name', 'email', 'phone', 'address'];
            const passwordFields = ['current_password', 'password', 'password_confirmation'];
            const requirementText = {
                reqLength: 'At least 8 characters',
                reqUppercase: 'At least one uppercase letter',
                reqLowercase: 'At least one lowercase letter',
                reqNumber: 'At least one number',
                reqMatch: 'Password and confirmation must match',
                reqDifferent: 'New password must be different from current',
            };
            const validators = {
                name: (value) => !text(value) ? 'Full name is required' : text(value).length < 2 ? 'Name must be at least 2 characters' : !/^[a-zA-Z\s'-]+$/.test(text(value)) ? 'Name can only contain letters, spaces, hyphens and apostrophes' : text(value).length > 255 ? 'Name must be 255 characters or fewer' : null,
                email: (value) => !String(value || '').trim() ? 'Email address is required' : !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value || '').trim()) ? 'Please enter a valid email address' : String(value || '').trim().length > 255 ? 'Email address must be 255 characters or fewer' : null,
                phone: (value) => phone(value) && !/^\+?\d{10,20}$/.test(phone(value)) ? 'Please enter a valid phone number' : null,
                address: (value) => String(value || '').trim().length > 500 ? 'Address must be 500 characters or fewer' : null,
                current_password: (value) => String(value || '').trim() ? null : 'Enter your current password',
                password: (value) => !value ? 'Enter a new password' : value.length < 8 ? 'Password must be at least 8 characters' : !/[A-Z]/.test(value) ? 'Password must contain at least one uppercase letter' : !/[a-z]/.test(value) ? 'Password must contain at least one lowercase letter' : !/\d/.test(value) ? 'Password must contain at least one number' : els.currentPassword?.value && value === els.currentPassword.value ? 'New password must be different from your current password' : null,
                password_confirmation: (value) => !value ? 'Confirm your new password' : value.length < 8 ? 'Password confirmation must be at least 8 characters' : els.newPassword?.value && value !== els.newPassword.value ? 'Passwords do not match' : null,
            };
            const els = {};

            function init() {
                cache();
                if (!els.page) return;
                state.originalProfile = snapshot();
                hydrateErrors();
                bindTabs();
                bindProfile();
                bindPassword();
                bindPhoto();
                bindToggles();
                updateCounter();
                updatePasswordRequirements();
                state.startProfileEditing ? enterEdit() : cancelEdit();
                state.startPasswordEditing ? enterPasswordEdit(true) : setPasswordMode(false);
                flash();
                refreshIcons();
            }

            function cache() {
                els.page = document.getElementById('staffSettingsPage');
                els.tabButtons = [...document.querySelectorAll('.tab-btn')];
                els.panels = [...document.querySelectorAll('.tab-content')];
                els.profileForm = document.getElementById('profileForm');
                els.photoForm = document.getElementById('photoForm');
                els.passwordForm = document.getElementById('passwordForm');
                els.editProfileBtn = document.getElementById('editProfileBtn');
                els.saveProfileBtn = document.getElementById('saveProfileBtn');
                els.cancelProfileBtn = document.getElementById('cancelProfileBtn');
                els.profileSectionStatus = document.getElementById('profileSectionStatus');
                els.profileInputs = profileFields.map((field) => document.getElementById(field)).filter(Boolean);
                els.department = document.getElementById('department');
                els.address = document.getElementById('address');
                els.addressCount = document.getElementById('addressCount');
                els.addressCounter = document.getElementById('addressCounter');
                els.currentPassword = document.getElementById('currentPassword');
                els.newPassword = document.getElementById('newPassword');
                els.confirmPassword = document.getElementById('confirmPassword');
                els.passwordIntro = document.getElementById('passwordIntro');
                els.showPasswordFormBtn = document.getElementById('showPasswordFormBtn');
                els.editPasswordBtn = document.getElementById('editPasswordBtn');
                els.updatePasswordBtn = document.getElementById('updatePasswordBtn');
                els.cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
                els.passwordSectionStatus = document.getElementById('passwordSectionStatus');
                els.passwordToggles = [...document.querySelectorAll('#passwordForm .pwd-toggle')];
                els.photoUpload = document.getElementById('photoUpload');
                els.photoPreview = document.getElementById('photoPreview');
                els.photoPreviewImg = document.getElementById('photoPreviewImg');
                els.fileName = document.getElementById('fileName');
                els.uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
                els.removePhotoBtn = document.getElementById('removePhotoBtn');
                els.profileAvatar = document.getElementById('profileAvatar');
                els.userName = document.getElementById('userName');
                els.userEmail = document.getElementById('userEmail');
                els.username = document.getElementById('username');
            }

            function hydrateErrors() {
                [...profileFields, ...passwordFields, 'profile_photo'].forEach((field) => {
                    const message = errorEl(field)?.textContent.trim();
                    if (!message) return;
                    if (profileFields.includes(field)) state.profileErrors[field] = message;
                    if (passwordFields.includes(field)) state.passwordErrors[field] = message;
                });
            }

            function bindTabs() {
                els.tabButtons.forEach((button) => button.addEventListener('click', () => switchTab(button.dataset.tab)));
            }

            function switchTab(name) {
                const nextPanelId = `${name}Tab`;
                if (els.panels.some((panel) => panel.id === nextPanelId && panel.classList.contains('active'))) return;
                els.tabButtons.forEach((button) => {
                    const active = button.dataset.tab === name;
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-selected', active ? 'true' : 'false');
                });
                els.panels.forEach((panel) => panel.classList.toggle('active', panel.id === nextPanelId));
            }

            function bindProfile() {
                els.editProfileBtn?.addEventListener('click', enterEdit);
                els.cancelProfileBtn?.addEventListener('click', cancelEdit);
                els.profileForm?.addEventListener('submit', submitProfile);
                els.profileInputs.forEach((input) => {
                    const field = input.dataset.validate;
                    input.addEventListener('input', () => {
                        if (!state.profileEditing) return;
                        if (field === 'address') updateCounter();
                        clearTimeout(state.fieldTimers[field]);
                        state.fieldTimers[field] = setTimeout(() => validateProfileField(field, true, true), 300);
                    });
                    input.addEventListener('blur', () => {
                        if (!state.profileEditing) return;
                        clearTimeout(state.fieldTimers[field]);
                        validateProfileField(field, true, true, true);
                    });
                });
            }

            function enterEdit() {
                state.profileEditing = true;
                els.profileInputs.forEach((input) => input.disabled = false);
                if (els.department) {
                    els.department.disabled = true;
                    els.department.setAttribute('aria-disabled', 'true');
                }
                els.editProfileBtn.hidden = true;
                els.saveProfileBtn.hidden = false;
                els.cancelProfileBtn.hidden = false;
                els.profileSectionStatus?.classList.add('editing');
                if (els.profileSectionStatus) els.profileSectionStatus.textContent = 'Editing';
                updateCounter();
                updateProfileSave();
                els.profileInputs[0]?.focus();
            }

            function cancelEdit() {
                state.profileEditing = false;
                state.profileErrors = {};
                if (state.emailAbort) state.emailAbort.abort();
                clearTimeout(state.emailTimer);
                state.emailPending = false;
                Object.entries(state.originalProfile).forEach(([field, value]) => {
                    const input = document.getElementById(field);
                    if (input) input.value = value;
                });
                els.profileInputs.forEach((input) => {
                    input.disabled = true;
                    setError(input.id, null);
                    paint(input, 'default');
                });
                if (els.department) {
                    els.department.disabled = true;
                    els.department.setAttribute('aria-disabled', 'true');
                    paint(els.department, 'default');
                }
                els.editProfileBtn.hidden = false;
                els.saveProfileBtn.hidden = true;
                els.cancelProfileBtn.hidden = true;
                els.saveProfileBtn.disabled = true;
                els.profileSectionStatus?.classList.remove('editing');
                if (els.profileSectionStatus) els.profileSectionStatus.textContent = 'Read only';
                updateCounter();
            }

            function validateProfileField(field, show = true, asyncEmail = true, immediate = false) {
                const input = document.getElementById(field);
                const message = validators[field] ? validators[field](input?.value) : null;
                state.profileErrors[field] = message;
                if (show) setError(field, message);
                if (field === 'email') {
                    if (message) finishEmail();
                    else if (asyncEmail) queueEmail(immediate);
                } else {
                    paint(input, message ? 'invalid' : input?.value.trim() ? 'valid' : 'default');
                }
                updateProfileSave();
                return message;
            }

            function validateProfileForm(show = true) {
                return profileFields.every((field) => !validateProfileField(field, show, false));
            }

            function queueEmail(immediate = false) {
                const value = String(document.getElementById('email')?.value || '').trim().toLowerCase();
                if (!value || value === String(state.originalProfile.email || '').trim().toLowerCase()) {
                    state.profileErrors.email = null;
                    setError('email', null);
                    paint(document.getElementById('email'), value ? 'valid' : 'default');
                    finishEmail();
                    updateProfileSave();
                    return;
                }
                clearTimeout(state.emailTimer);
                state.emailTimer = setTimeout(() => runEmailCheck(value), immediate ? 0 : 500);
            }

            async function runEmailCheck(value) {
                const input = document.getElementById('email');
                if (!input) return true;
                if (state.emailAbort) state.emailAbort.abort();
                state.emailAbort = new AbortController();
                state.emailPending = true;
                paint(input, 'validating');
                updateProfileSave();
                try {
                    const response = await fetch(els.profileForm.dataset.checkEmailUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                        body: JSON.stringify({ email: value }),
                        signal: state.emailAbort.signal,
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (String(input.value || '').trim().toLowerCase() !== value) return false;
                    if (!response.ok || !payload.available) {
                        state.profileErrors.email = payload?.errors?.email?.[0] || 'This email address is already in use';
                        setError('email', state.profileErrors.email);
                        paint(input, 'invalid');
                        return false;
                    }
                    state.profileErrors.email = null;
                    setError('email', null);
                    paint(input, 'valid');
                    return true;
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        state.profileErrors.email = 'Unable to verify email availability right now';
                        setError('email', state.profileErrors.email);
                        paint(input, 'invalid');
                    }
                    return false;
                } finally {
                    finishEmail();
                    updateProfileSave();
                }
            }

            function finishEmail() {
                state.emailPending = false;
                document.getElementById('email')?.classList.remove('validating');
            }

            async function submitProfile(event) {
                event.preventDefault();
                if (!state.profileEditing || state.profileSubmitting) return;
                if (!validateProfileForm(true)) return focusFirstError(els.profileForm);
                const email = String(document.getElementById('email')?.value || '').trim().toLowerCase();
                const changedEmail = email !== String(state.originalProfile.email || '').trim().toLowerCase();
                if (changedEmail && !(await runEmailCheck(email))) return focusFirstError(els.profileForm);
                if (!profileChanged()) return showToast('Make a change before saving your profile.', 'info');
                state.profileSubmitting = true;
                els.saveProfileBtn.disabled = true;
                const oldLabel = els.saveProfileBtn.innerHTML;
                els.saveProfileBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                const formData = new FormData(els.profileForm);
                formData.set('_method', 'PUT');
                try {
                    const response = await fetch(els.profileForm.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: formData });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) return serverErrors(payload, 'profile');
                    applyProfile(payload.user || {});
                    state.originalProfile = snapshot();
                    cancelEdit();
                    showToast(payload.message || 'Profile updated successfully.', 'success');
                } catch (error) {
                    console.error(error);
                    showToast('Unable to save your profile right now.', 'error');
                } finally {
                    state.profileSubmitting = false;
                    els.saveProfileBtn.innerHTML = oldLabel;
                    updateProfileSave();
                }
            }

            function applyProfile(user) {
                if ('name' in user) { document.getElementById('name').value = user.name || ''; if (els.userName) els.userName.textContent = user.name || ''; }
                if ('email' in user) { document.getElementById('email').value = user.email || ''; if (els.userEmail) els.userEmail.textContent = user.email || ''; }
                if ('phone' in user) document.getElementById('phone').value = user.phone || '';
                if ('department' in user) document.getElementById('department').value = user.department || '';
                if ('address' in user) document.getElementById('address').value = user.address || '';
                if (els.username) els.username.textContent = user.username || username(user.email || '');
                if ('profile_photo_url' in user) {
                    renderPhoto(user.profile_photo_url || '');
                }
                renderAvatar('profile_photo_url' in user ? (user.profile_photo_url || '') : currentAvatarUrl(), user.name || els.userName?.textContent || '');
                updateCounter();
            }

            function bindPhoto() {
                els.photoUpload?.addEventListener('change', previewPhoto);
                els.photoForm?.addEventListener('submit', submitPhoto);
                els.removePhotoBtn?.addEventListener('click', removePhoto);
            }

            function previewPhoto() {
                setError('profile_photo', null);
                const file = els.photoUpload?.files?.[0];
                if (!file) {
                    els.fileName.textContent = 'No file chosen';
                    els.uploadPhotoBtn.disabled = true;
                    return;
                }
                if (!file.type.startsWith('image/')) return failPhoto('Please select a valid image file');
                if (file.size > 2 * 1024 * 1024) return failPhoto('Profile photo must not exceed 2MB');
                els.fileName.textContent = file.name;
                const reader = new FileReader();
                reader.onload = (loadEvent) => {
                    renderPhoto(loadEvent.target?.result || '');
                    els.uploadPhotoBtn.disabled = false;
                };
                reader.readAsDataURL(file);
            }

            async function submitPhoto(event) {
                event.preventDefault();
                if (!els.photoUpload?.files?.[0]) return failPhoto('Choose a photo before uploading');
                const oldLabel = els.uploadPhotoBtn.innerHTML;
                els.uploadPhotoBtn.disabled = true;
                els.uploadPhotoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                try {
                    const response = await fetch(els.photoForm.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: new FormData(els.photoForm) });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) return serverErrors(payload, 'photo');
                    const photoUrl = payload?.user?.profile_photo_url || '';
                    renderPhoto(photoUrl);
                    renderAvatar(photoUrl, els.userName?.textContent || '');
                    els.photoForm.reset();
                    els.fileName.textContent = 'No file chosen';
                    els.removePhotoBtn.disabled = !photoUrl;
                    showToast(payload.message || 'Photo uploaded successfully.', 'success');
                } catch (error) {
                    console.error(error);
                    showToast('Unable to upload your photo right now.', 'error');
                } finally {
                    els.uploadPhotoBtn.innerHTML = oldLabel;
                    els.uploadPhotoBtn.disabled = true;
                }
            }

            async function removePhoto() {
                if (els.removePhotoBtn?.disabled) return;
                if (!window.confirm('Are you sure you want to remove your profile photo?')) return;
                try {
                    const response = await fetch('{{ route("staff.settings.remove-photo") }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() } });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) return showToast(payload.message || 'Unable to remove your photo right now.', 'error');
                    renderPhoto('');
                    renderAvatar('', els.userName?.textContent || '');
                    els.photoForm.reset();
                    els.fileName.textContent = 'No file chosen';
                    els.uploadPhotoBtn.disabled = true;
                    els.removePhotoBtn.disabled = true;
                    showToast(payload.message || 'Profile photo removed successfully.', 'success');
                } catch (error) {
                    console.error(error);
                    showToast('Unable to remove your photo right now.', 'error');
                }
            }

            function failPhoto(message) {
                setError('profile_photo', message);
                if (els.photoUpload) els.photoUpload.value = '';
                if (els.fileName) els.fileName.textContent = 'No file chosen';
                if (els.uploadPhotoBtn) els.uploadPhotoBtn.disabled = true;
            }

            function renderPhoto(src) {
                if (!els.photoPreview) return;
                let img = els.photoPreviewImg;
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'photoPreviewImg';
                    img.alt = 'Preview';
                    els.photoPreview.appendChild(img);
                    els.photoPreviewImg = img;
                }
                let icon = document.getElementById('photoFallbackIcon');
                if (src) {
                    img.src = src;
                    img.style.display = 'block';
                    if (icon) icon.style.display = 'none';
                } else {
                    img.removeAttribute('src');
                    img.style.display = 'none';
                    if (!icon) {
                        icon = document.createElement('div');
                        icon.id = 'photoFallbackIcon';
                        icon.className = 'photo-upload-icon';
                        icon.innerHTML = '<i class="fas fa-user-circle"></i>';
                        els.photoPreview.prepend(icon);
                    }
                    icon.style.display = 'flex';
                }
            }

            function renderAvatar(src, name) {
                if (!els.profileAvatar) return;
                els.profileAvatar.innerHTML = '';
                if (src) {
                    const img = document.createElement('img');
                    img.id = 'avatarImage';
                    img.alt = 'Profile Photo';
                    img.src = src;
                    els.profileAvatar.appendChild(img);
                    return;
                }
                const node = document.createElement('div');
                node.className = 'avatar-placeholder';
                node.textContent = avatarInitial(name);
                els.profileAvatar.appendChild(node);
            }

            function bindPassword() {
                els.showPasswordFormBtn?.addEventListener('click', () => enterPasswordEdit());
                els.editPasswordBtn?.addEventListener('click', enterPasswordEdit);
                [els.currentPassword, els.newPassword, els.confirmPassword].filter(Boolean).forEach((input) => {
                    input.addEventListener('input', () => {
                        if (!isPasswordEditing()) return;
                        validatePasswordField(input.name, true);
                        if (input.name !== 'password_confirmation') validatePasswordField('password_confirmation', false);
                        updatePasswordRequirements();
                        updatePasswordSave();
                    });
                    input.addEventListener('blur', () => {
                        if (!isPasswordEditing()) return;
                        validatePasswordField(input.name, true);
                        updatePasswordRequirements();
                        updatePasswordSave();
                    });
                });
                els.passwordForm?.addEventListener('submit', submitPassword);
                els.cancelPasswordBtn?.addEventListener('click', () => {
                    resetPassword();
                    setPasswordMode(false);
                });
            }

            function enterPasswordEdit(preserveState = false) {
                if (!isPasswordEditing() && !preserveState) {
                    resetPassword();
                }
                setPasswordMode(true);
                els.currentPassword?.focus();
            }

            function setPasswordMode(editing) {
                [els.currentPassword, els.newPassword, els.confirmPassword].filter(Boolean).forEach((input) => {
                    input.disabled = !editing;
                });
                (els.passwordToggles || []).forEach((button) => {
                    button.disabled = !editing;
                });
                if (els.passwordIntro) els.passwordIntro.hidden = editing;
                if (els.passwordForm) els.passwordForm.hidden = !editing;
                if (els.showPasswordFormBtn) els.showPasswordFormBtn.setAttribute('aria-expanded', editing ? 'true' : 'false');
                if (els.editPasswordBtn) els.editPasswordBtn.hidden = true;
                if (els.cancelPasswordBtn) els.cancelPasswordBtn.hidden = !editing;
                if (els.updatePasswordBtn) {
                    els.updatePasswordBtn.hidden = !editing;
                }
                if (els.passwordSectionStatus) {
                    els.passwordSectionStatus.classList.toggle('editing', editing);
                    els.passwordSectionStatus.textContent = editing ? 'Editing' : 'Read only';
                }
                if (!editing) {
                    state.passwordSubmitting = false;
                }
                updatePasswordRequirements();
                updatePasswordSave();
            }

            function isPasswordEditing() {
                return !(els.currentPassword?.disabled ?? true);
            }

            function validatePasswordField(field, show = true) {
                const input = pwd(field);
                const message = validators[field] ? validators[field](input?.value || '') : null;
                state.passwordErrors[field] = message;
                if (show) setError(field, message);
                paint(input, message ? 'invalid' : input?.value ? 'valid' : 'default');
                return message;
            }

            function updatePasswordRequirements() {
                const current = els.currentPassword?.value || '';
                const next = els.newPassword?.value || '';
                const confirm = els.confirmPassword?.value || '';
                const checks = {
                    reqLength: next.length >= 8,
                    reqUppercase: /[A-Z]/.test(next),
                    reqLowercase: /[a-z]/.test(next),
                    reqNumber: /\d/.test(next),
                    reqMatch: next.length > 0 && next === confirm,
                    reqDifferent: next.length > 0 && next !== current,
                };
                Object.entries(checks).forEach(([id, ok]) => {
                    const item = document.getElementById(id);
                    if (!item) return;
                    item.classList.toggle('valid', ok);
                    item.classList.toggle('invalid', !ok);
                    item.innerHTML = `<i class="${ok ? 'fas fa-check-circle' : 'far fa-circle'}"></i> ${requirementText[id]}`;
                });
            }

            function updatePasswordSave() {
                const hasValue = passwordFields.some((field) => Boolean(pwd(field)?.value));
                const invalid = passwordFields.some((field) => Boolean(state.passwordErrors[field] || validatePasswordField(field, false)));
                if (els.updatePasswordBtn) els.updatePasswordBtn.disabled = !isPasswordEditing() || !hasValue || invalid || state.passwordSubmitting;
            }

            async function submitPassword(event) {
                event.preventDefault();
                if (state.passwordSubmitting) return;
                const invalid = passwordFields.some((field) => Boolean(validatePasswordField(field, true)));
                updatePasswordRequirements();
                updatePasswordSave();
                if (invalid) return focusFirstError(els.passwordForm);
                state.passwordSubmitting = true;
                els.updatePasswordBtn.disabled = true;
                const oldLabel = els.updatePasswordBtn.innerHTML;
                els.updatePasswordBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                const formData = new FormData(els.passwordForm);
                formData.set('_method', 'PUT');
                try {
                    const response = await fetch(els.passwordForm.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: formData });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) return serverErrors(payload, 'password');
                    showToast(payload.message || 'Password updated successfully.', 'success');
                    resetPassword();
                    setPasswordMode(false);
                } catch (error) {
                    console.error(error);
                    showToast('Unable to update your password right now.', 'error');
                } finally {
                    state.passwordSubmitting = false;
                    els.updatePasswordBtn.innerHTML = oldLabel;
                    updatePasswordSave();
                    refreshIcons();
                }
            }

            function resetPassword() {
                els.passwordForm?.reset();
                state.passwordErrors = {};
                passwordFields.forEach((field) => {
                    setError(field, null);
                    paint(pwd(field), 'default');
                });
                updatePasswordRequirements();
                updatePasswordSave();
            }

            function bindToggles() {
                document.querySelectorAll('.pwd-toggle').forEach((button) => {
                    button.addEventListener('click', () => {
                        const input = document.getElementById(button.dataset.target);
                        if (!input) return;
                        const hidden = input.type === 'password';
                        input.type = hidden ? 'text' : 'password';
                        button.innerHTML = `<i data-lucide="${hidden ? 'eye-off' : 'eye'}"></i>`;
                        refreshIcons();
                    });
                });
            }

            function serverErrors(payload, kind) {
                const errors = payload?.errors || {};
                const firstField = Object.keys(errors)[0];
                if (kind === 'profile') {
                    if (!state.profileEditing) enterEdit();
                    Object.entries(errors).forEach(([field, messages]) => {
                        state.profileErrors[field] = messages[0];
                        setError(field, messages[0]);
                        paint(document.getElementById(field), 'invalid');
                    });
                    switchTab('profile');
                    return firstField ? focusFirstError(els.profileForm) : showToast(payload?.message || 'Please review the highlighted profile fields.', 'error');
                }
                if (kind === 'password') {
                    if (!isPasswordEditing()) enterPasswordEdit();
                    Object.entries(errors).forEach(([field, messages]) => {
                        state.passwordErrors[field] = messages[0];
                        setError(field, messages[0]);
                        paint(pwd(field), 'invalid');
                    });
                    switchTab('security');
                    return firstField ? focusFirstError(els.passwordForm) : showToast(payload?.message || 'Please review the highlighted password fields.', 'error');
                }
                setError('profile_photo', errors.profile_photo?.[0] || payload?.message || 'Please review the selected photo.');
                switchTab('photo');
                focusFirstError(els.photoForm);
            }

            function setError(field, message) {
                const el = errorEl(field);
                const input = document.getElementById(field) || pwd(field);
                if (!el) return;
                el.textContent = message || '';
                el.classList.toggle('visible', Boolean(message));
                if (input) input.setAttribute('aria-invalid', message ? 'true' : 'false');
            }

            function paint(input, stateName) {
                if (!input) return;
                input.classList.remove('is-invalid', 'is-valid', 'validating');
                if (stateName === 'invalid') input.classList.add('is-invalid');
                if (stateName === 'valid') input.classList.add('is-valid');
                if (stateName === 'validating') input.classList.add('validating');
            }

            function updateCounter() {
                if (!els.address || !els.addressCount || !els.addressCounter) return;
                const size = els.address.value.length;
                els.addressCount.textContent = String(size);
                els.addressCounter.classList.toggle('is-limit', size >= 500);
            }

            function updateProfileSave() {
                const invalid = profileFields.some((field) => Boolean(state.profileErrors[field] || validators[field](document.getElementById(field)?.value || '')));
                if (els.saveProfileBtn) els.saveProfileBtn.disabled = !state.profileEditing || state.profileSubmitting || state.emailPending || invalid || !profileChanged();
            }

            function profileChanged() {
                const current = snapshot();
                return profileFields.some((field) => current[field] !== state.originalProfile[field]);
            }

            function snapshot() {
                return {
                    name: text(document.getElementById('name')?.value || ''),
                    email: String(document.getElementById('email')?.value || '').trim().toLowerCase(),
                    phone: phone(document.getElementById('phone')?.value || ''),
                    address: String(document.getElementById('address')?.value || '').trim(),
                };
            }

            function focusFirstError(container) {
                const error = container?.querySelector('.field-error.visible');
                if (!error) return;
                const field = error.id.replace(/^error_/, '');
                const target = document.getElementById(field) || pwd(field) || (field === 'profile_photo' ? els.photoUpload : null);
                error.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => target?.focus(), 150);
            }

            function flash() {
                if (state.flashSuccess) showToast(state.flashSuccess, 'success');
                if (state.flashError) showToast(state.flashError, 'error');
            }

            function showToast(message, type = 'info') {
                if (!message) return;
                const toast = document.createElement('div');
                toast.className = `settings-toast ${type}`;
                toast.setAttribute('role', 'status');
                toast.setAttribute('aria-live', 'polite');
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.animation = 'staffSettingsSlideOut 0.25s ease forwards';
                    setTimeout(() => toast.remove(), 250);
                }, 3200);
            }

            function errorEl(field) { return document.getElementById(`error_${field}`); }
            function pwd(field) { return { current_password: els.currentPassword, password: els.newPassword, password_confirmation: els.confirmPassword }[field] || null; }
            function text(value) { return String(value || '').replace(/\s+/g, ' ').trim(); }
            function phone(value) { return String(value || '').trim().replace(/[^\d+]/g, '').replace(/(?!^)\+/g, ''); }
            function username(email) { return String(email || '').split('@')[0] || 'staff1'; }
            function currentAvatarUrl() { return els.profileAvatar?.querySelector('img')?.getAttribute('src') || ''; }
            function avatarInitial(name) { return text(name).charAt(0).toUpperCase() || els.profileAvatar?.dataset.defaultInitial || 'S'; }
            function csrf() { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''; }
            function refreshIcons() { if (window.lucide && typeof window.lucide.createIcons === 'function') window.lucide.createIcons(); }

            return { init };
        })();

        document.addEventListener('DOMContentLoaded', StaffSettings.init);
    </script>
@endpush
