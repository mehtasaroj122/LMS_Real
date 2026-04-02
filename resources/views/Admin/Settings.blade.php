@extends('Admin.layouts.app')

@section('title', 'Settings')

@php
    $profilePhotoUrl = $user->profile_photo
        ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo))
        : null;

    $profileErrorFields = ['name', 'email', 'phone', 'address', 'date_of_birth', 'profile_photo'];
    $passwordErrorFields = ['current_password', 'new_password', 'new_password_confirmation'];
    $libraryErrorFields = [
        'per_day_fine', 'grace_period_days', 'max_fine_amount', 'lost_book_penalty',
        'damaged_book_penalty', 'fair_condition_penalty', 'issue_duration_days', 'max_books_per_student',
        'renewal_limit', 'renewal_duration_days',
    ];

    $hasProfileErrors = collect($profileErrorFields)->contains(fn ($field) => $errors->has($field));
    $hasPasswordErrors = collect($passwordErrorFields)->contains(fn ($field) => $errors->has($field));
    $hasLibraryErrors = collect($libraryErrorFields)->contains(fn ($field) => $errors->has($field));
    $activeTab = $hasPasswordErrors ? 'password' : ($hasLibraryErrors ? 'library' : 'profile');
    $profileSummaryUsername = $user->username ?? (($user->email ?? null) ? explode('@', $user->email)[0] : 'admin');
    $profileSummaryMemberSince = optional($user->created_at)->format('F d, Y') ?? 'N/A';
    $profileSummaryLastLogin = optional($user->last_login_at)->format('M d, Y h:i A') ?? 'Never';
    $profileSummaryPhone = old('phone', $user->phone) ?: 'Not added';
    $defaultAvatarLetter = strtoupper(mb_substr(trim($user->name ?? 'A'), 0, 1));
    $defaultAvatarLetter = $defaultAvatarLetter !== '' ? $defaultAvatarLetter : 'A';
    $assetBaseUrl = rtrim(url('/'), '/');

@endphp

@push('styles')
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-shadow: rgba(37, 99, 235, 0.12);
            --success-color: #10b981;
            --success-soft: rgba(16, 185, 129, 0.12);
            --warning-color: #f59e0b;
            --warning-soft: rgba(245, 158, 11, 0.12);
            --danger-color: #ef4444;
            --danger-soft: rgba(239, 68, 68, 0.12);
            --card-bg: #fff;
            --card-border: #e5e7eb;
            --input-bg: #fff;
            --input-border: #d1d5db;
            --input-text: #111827;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --icon-bg: #eff6ff;
            --icon-color: #2563eb;
            --toggle-bg: #f8fafc;
            --toggle-hover: #f1f5f9;
            --toggle-off: #d1d5db;
        }

        .dark-theme {
            --primary-color: #60a5fa;
            --primary-hover: #93c5fd;
            --primary-shadow: rgba(96, 165, 250, 0.18);
            --success-color: #34d399;
            --success-soft: rgba(52, 211, 153, 0.12);
            --warning-color: #fbbf24;
            --warning-soft: rgba(251, 191, 36, 0.12);
            --danger-color: #f87171;
            --danger-soft: rgba(248, 113, 113, 0.12);
            --card-bg: #0f172a;
            --card-border: #1e293b;
            --input-bg: #020617;
            --input-border: #334155;
            --input-text: #f8fafc;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --icon-bg: rgba(96, 165, 250, 0.16);
            --icon-color: #93c5fd;
            --toggle-bg: rgba(15, 23, 42, 0.72);
            --toggle-hover: #1e293b;
            --toggle-off: #475569;
        }

        .settings-container { width: 100%; max-width: none; margin: -0.1rem 0 0; }
        .settings-header { margin-bottom: 0.9rem; }
        .settings-header h1 { margin: 0; font-size: 1.35rem; font-weight: 700; color: var(--text-primary); }
        .settings-header p { margin: 0.25rem 0 0; font-size: 0.92rem; color: var(--text-secondary); }

        .section-header {
            display: flex; align-items: flex-start; justify-content: space-between; gap: 0.85rem;
            padding-bottom: 0.85rem; margin-bottom: 1rem; border-bottom: 1px solid var(--card-border);
        }

        .section-header-title { display: flex; align-items: flex-start; gap: 0.75rem; }
        .section-icon {
            width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 0.8rem; background: var(--icon-bg); color: var(--icon-color); flex-shrink: 0;
        }

        .section-copy h2 { margin: 0; font-size: 0.98rem; font-weight: 700; color: var(--text-primary); }
        .section-copy p { margin: 0.22rem 0 0; color: var(--text-secondary); font-size: 0.84rem; }

        .section-header-actions { display: flex; align-items: center; justify-content: flex-end; gap: 0.55rem; flex-wrap: wrap; }

        .section-status {
            display: inline-flex; align-items: center; padding: 0.36rem 0.72rem; border-radius: 999px;
            background: var(--toggle-bg); color: var(--text-secondary); font-size: 0.72rem;
            font-weight: 700; text-transform: uppercase;
        }

        .section-status.editing { background: var(--primary-shadow); color: var(--primary-color); }
        .section-status.dirty { background: var(--warning-soft); color: var(--warning-color); }
        .section-status.success { background: var(--success-soft); color: var(--success-color); }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; padding: 0.6rem 0.88rem;
            border: 1px solid transparent; border-radius: 0.75rem; font-size: 0.86rem; font-weight: 600;
            cursor: pointer; transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .btn:hover:not(:disabled) { transform: translateY(-1px); }
        .btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .btn-primary { background: linear-gradient(135deg, var(--primary-color), var(--primary-hover)); color: #fff; }
        .btn-secondary { background: var(--toggle-bg); border-color: var(--card-border); color: var(--text-primary); }
        .btn-outline { background: transparent; border-color: var(--card-border); color: var(--text-primary); }

        .form-grid, .library-settings-grid {
            display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem 0.95rem;
        }

        .form-group { margin: 0; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-label { display: block; margin-bottom: 0.38rem; font-size: 0.78rem; font-weight: 700; color: var(--text-primary); }
        .required-marker { color: var(--danger-color); margin-left: 0.2rem; }

        .form-control {
            width: 100%; padding: 0.7rem 0.82rem; border: 1px solid var(--input-border); border-radius: 0.78rem;
            background: var(--input-bg); color: var(--input-text); font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        textarea.form-control { min-height: 96px; resize: vertical; }
        .form-control:focus { outline: none; border-color: var(--primary-color); box-shadow: 0 0 0 4px var(--primary-shadow); }
        .form-control[disabled] { opacity: 0.72; cursor: not-allowed; background: color-mix(in srgb, var(--toggle-bg) 88%, white 12%); }

        .form-group.has-error .form-control { border-color: var(--danger-color); box-shadow: 0 0 0 4px var(--danger-soft); }
        .form-group.has-success .form-control { border-color: var(--success-color); box-shadow: 0 0 0 4px var(--success-soft); }

        .field-error {
            display: block; min-height: 1rem; margin-top: 0.35rem; color: var(--danger-color);
            font-size: 0.76rem; line-height: 1.4;
        }

        .form-hint { margin-top: 0.35rem; color: var(--text-muted); font-size: 0.76rem; }

        .profile-photo-container {
            display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.95rem; align-items: center;
            margin-bottom: 1rem; padding: 0.85rem; border-radius: 0.9rem;
            background: linear-gradient(135deg, var(--toggle-bg), transparent); border: 1px solid var(--card-border);
        }

        .profile-photo-placeholder {
            width: 92px; height: 92px; display: flex; align-items: center; justify-content: center; overflow: hidden;
            border-radius: 0.95rem; border: 1px solid var(--card-border); background: var(--card-bg); color: var(--text-muted);
        }

        .profile-photo-placeholder img { width: 100%; height: 100%; object-fit: cover; }
        .profile-photo-upload h3 { margin: 0 0 0.25rem; font-size: 0.9rem; font-weight: 700; color: var(--text-primary); }
        .profile-photo-upload p { margin: 0; color: var(--text-secondary); font-size: 0.84rem; }
        .photo-actions { display: flex; flex-wrap: wrap; gap: 0.55rem; margin-top: 0.75rem; }

        .file-upload-btn {
            display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.6rem 0.82rem;
            border: 1px dashed var(--card-border); border-radius: 0.75rem; background: var(--card-bg);
            color: var(--text-primary); cursor: pointer; transition: border-color 0.2s ease, background 0.2s ease;
        }

        .file-upload-btn:hover { border-color: var(--primary-color); background: var(--toggle-hover); }
        .file-upload-btn.is-disabled { opacity: 0.55; cursor: not-allowed; pointer-events: none; }

        .role-display {
            display: inline-flex; align-items: center; gap: 0.42rem; padding: 0.6rem 0.82rem;
            border: 1px solid var(--card-border); border-radius: 0.75rem; background: var(--toggle-bg);
            color: var(--text-primary); font-size: 0.88rem; font-weight: 600;
        }

        .password-input-wrapper { position: relative; }
        .password-toggle {
            position: absolute; top: 50%; right: 0.75rem; transform: translateY(-50%);
            display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px;
            border: none; border-radius: 999px; background: transparent; color: var(--text-muted); cursor: pointer;
        }

        .password-toggle:hover:not(:disabled) { background: var(--toggle-bg); color: var(--text-primary); }
        .password-toggle:disabled { opacity: 0.45; cursor: not-allowed; }

        .password-requirements {
            background: var(--toggle-bg); border-radius: 0.9rem; padding: 1rem; margin-top: 0.2rem; border: 1px solid var(--card-border);
        }

        .requirements-title { margin: 0 0 0.85rem; font-size: 0.88rem; font-weight: 700; color: var(--text-primary); }
        .requirements-list { display: flex; flex-direction: column; gap: 0.55rem; }
        .requirement-item { display: flex; align-items: center; gap: 0.65rem; color: var(--text-muted); font-size: 0.82rem; transition: color 0.3s ease; }
        .requirement-item.met { color: var(--success-color); }

        .requirement-icon {
            width: 20px; height: 20px; border: 2px solid currentColor; border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all 0.3s ease;
        }

        .requirement-item.met .requirement-icon { background: var(--success-color); border-color: var(--success-color); color: white; }
        .requirement-icon svg { width: 12px; height: 12px; opacity: 0; transition: opacity 0.3s ease; }
        .requirement-item.met .requirement-icon svg { opacity: 1; }

        .strength-bar { height: 6px; margin-top: 1rem; border-radius: 999px; overflow: hidden; background: var(--card-border); }
        .strength-fill { height: 100%; width: 0; transition: all 0.35s ease; border-radius: 999px; }
        .strength-fill.weak { width: 33%; background: var(--danger-color); }
        .strength-fill.fair { width: 50%; background: var(--warning-color); }
        .strength-fill.good { width: 70%; background: #3b82f6; }
        .strength-fill.strong { width: 100%; background: var(--success-color); }
        .strength-text { margin-top: 0.5rem; text-align: center; color: var(--text-muted); font-size: 0.76rem; }

        .library-settings-section { margin-bottom: 1.35rem; padding-bottom: 1.15rem; border-bottom: 1px solid var(--card-border); }
        .library-settings-section:last-of-type { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .section-subtitle {
            margin: 0 0 0.85rem; font-size: 0.78rem; font-weight: 800; color: var(--text-secondary);
            letter-spacing: 0.08em; text-transform: uppercase;
        }

        .sr-only-hidden { display: none; }

        .settings-grid {
            display: grid;
            grid-template-columns: minmax(250px, 286px) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .settings-profile-column {
            min-width: 0;
            position: sticky;
            top: 1rem;
            align-self: start;
        }

        .settings-profile-card,
        .settings-main-card {
            border: 1px solid var(--card-border);
            border-radius: 0.9rem;
            background: var(--card-bg);
            box-shadow: 0 14px 30px -28px rgba(15, 23, 42, 0.38);
        }

        .settings-profile-card { overflow: hidden; }

        .settings-profile-card-content,
        .settings-main-card-content { padding: 1.15rem; }

        .settings-profile-card-content {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        .settings-profile-summary {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .settings-profile-avatar {
            width: 88px;
            height: 88px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
            color: #fff;
            box-shadow: 0 14px 24px -20px var(--primary-color);
        }

        .settings-profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .settings-profile-avatar-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            text-transform: uppercase;
            line-height: 1;
        }

        .settings-profile-name {
            margin: 0.8rem 0 0.22rem;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .settings-profile-email {
            margin: 0;
            font-size: 0.84rem;
            color: var(--text-secondary);
            word-break: break-word;
        }

        .settings-profile-role {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.7rem;
            padding: 0.36rem 0.72rem;
            border-radius: 999px;
            background: var(--icon-bg);
            color: var(--icon-color);
            font-size: 0.76rem;
            font-weight: 700;
        }

        .settings-profile-meta-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 1px solid var(--card-border);
        }

        .settings-profile-meta-item {
            display: grid;
            grid-template-columns: 34px minmax(0, 1fr);
            gap: 0.7rem;
            align-items: center;
        }

        .settings-profile-meta-icon {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            background: var(--toggle-bg);
            color: var(--icon-color);
        }

        .settings-profile-meta-label {
            margin: 0 0 0.15rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .settings-profile-meta-value {
            margin: 0;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-primary);
            line-height: 1.35;
            word-break: break-word;
        }

        .settings-tabs {
            display: flex;
            gap: 0.2rem;
            margin-bottom: 1rem;
            padding-bottom: 0;
            border-bottom: 1px solid var(--card-border);
            overflow-x: auto;
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.66rem 0.95rem;
            border: 1px solid transparent;
            border-radius: 0.65rem 0.65rem 0 0;
            background: transparent;
            color: var(--text-secondary);
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            top: 1px;
            white-space: nowrap;
            transition: color 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .tab-btn:hover { background: var(--toggle-bg); color: var(--text-primary); }

        .tab-btn.active {
            background: var(--card-bg);
            color: var(--primary-color);
            border-color: var(--card-border);
            border-bottom-color: var(--card-bg);
        }

        .tab-btn svg {
            width: 16px;
            height: 16px;
        }

        .settings-section {
            display: none;
            padding: 0;
            border: none;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            animation: fadeInSettings 0.22s ease;
        }

        .settings-section.active { display: block; }
        .settings-section[hidden] { display: none !important; }
        .settings-section.edit-mode { border: none; box-shadow: none; }

        .settings-main-card .section-header:first-child { margin-top: 0; }

        .avatar-letter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            text-transform: uppercase;
            color: var(--text-primary);
        }

        .avatar-letter--large { font-size: 2.3rem; }

        .profile-photo-placeholder {
            border-style: solid;
            background: linear-gradient(135deg, var(--toggle-bg), color-mix(in srgb, var(--card-bg) 82%, white 18%));
        }

        .profile-photo-placeholder .avatar-letter {
            color: var(--text-secondary);
        }

        @keyframes fadeInSettings {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 960px) {
            .section-header { flex-direction: column; }
            .section-header-actions { width: 100%; justify-content: flex-start; }
        }

        @media (max-width: 1024px) {
            .settings-grid { grid-template-columns: 1fr; }
            .settings-profile-column { position: static; }
        }

        @media (max-width: 768px) {
            .form-grid, .library-settings-grid { grid-template-columns: 1fr; }
            .profile-photo-container { grid-template-columns: 1fr; justify-items: flex-start; }
            .settings-profile-card-content,
            .settings-main-card-content { padding: 1rem; }
            .settings-tabs { margin-inline: -0.1rem; }
        }
    </style>
@endpush

@section('content')
    <div class="settings-container" id="adminSettingsPage" data-default-avatar-letter="{{ $defaultAvatarLetter }}" data-asset-base="{{ $assetBaseUrl }}">
        <div class="settings-header">
            <h1>Settings</h1>
            <p>Manage your admin account, password security, and core library rules from one place.</p>
        </div>

        <div class="settings-grid">
            <aside class="settings-profile-column">
                <div class="settings-profile-card">
                    <div class="settings-profile-card-content">
                        <div class="settings-profile-summary">
                            <div class="settings-profile-avatar">
                                <img src="{{ $profilePhotoUrl ?? '' }}" alt="Admin profile photo" id="adminAvatarImage" {{ $profilePhotoUrl ? '' : 'hidden' }}>
                                <span class="settings-profile-avatar-fallback" id="adminAvatarFallback" {{ $profilePhotoUrl ? 'hidden' : '' }}>{{ $defaultAvatarLetter }}</span>
                            </div>
                            <h2 class="settings-profile-name" id="adminCardName">{{ old('name', $user->name) }}</h2>
                            <p class="settings-profile-email" id="adminCardEmail">{{ old('email', $user->email) }}</p>
                            <span class="settings-profile-role">
                                <i data-lucide="shield-check" width="15" height="15"></i>
                                Admin
                            </span>
                        </div>

                        <div class="settings-profile-meta-list">
                            <div class="settings-profile-meta-item">
                                <div class="settings-profile-meta-icon"><i data-lucide="at-sign" width="18" height="18"></i></div>
                                <div>
                                    <p class="settings-profile-meta-label">Username</p>
                                    <p class="settings-profile-meta-value" id="adminCardUsername">{{ $profileSummaryUsername }}</p>
                                </div>
                            </div>

                            <div class="settings-profile-meta-item">
                                <div class="settings-profile-meta-icon"><i data-lucide="phone" width="18" height="18"></i></div>
                                <div>
                                    <p class="settings-profile-meta-label">Phone</p>
                                    <p class="settings-profile-meta-value" id="adminCardPhone">{{ $profileSummaryPhone }}</p>
                                </div>
                            </div>

                            <div class="settings-profile-meta-item">
                                <div class="settings-profile-meta-icon"><i data-lucide="calendar-days" width="18" height="18"></i></div>
                                <div>
                                    <p class="settings-profile-meta-label">Member Since</p>
                                    <p class="settings-profile-meta-value">{{ $profileSummaryMemberSince }}</p>
                                </div>
                            </div>

                            <div class="settings-profile-meta-item">
                                <div class="settings-profile-meta-icon"><i data-lucide="clock-3" width="18" height="18"></i></div>
                                <div>
                                    <p class="settings-profile-meta-label">Last Login</p>
                                    <p class="settings-profile-meta-value">{{ $profileSummaryLastLogin }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="settings-main-card">
                <div class="settings-main-card-content">
                    <div class="settings-tabs" role="tablist" aria-label="Admin settings sections">
                        <button class="tab-btn {{ $activeTab === 'profile' ? 'active' : '' }}" id="tab-profile" data-tab="profile" type="button" role="tab" aria-selected="{{ $activeTab === 'profile' ? 'true' : 'false' }}" aria-controls="profile-tab">
                            <i data-lucide="user" width="18" height="18"></i>
                            Profile
                        </button>
                        <button class="tab-btn {{ $activeTab === 'password' ? 'active' : '' }}" id="tab-password" data-tab="password" type="button" role="tab" aria-selected="{{ $activeTab === 'password' ? 'true' : 'false' }}" aria-controls="password-tab">
                            <i data-lucide="lock" width="18" height="18"></i>
                            Security
                        </button>
                        <button class="tab-btn {{ $activeTab === 'library' ? 'active' : '' }}" id="tab-library" data-tab="library" type="button" role="tab" aria-selected="{{ $activeTab === 'library' ? 'true' : 'false' }}" aria-controls="library-tab">
                            <i data-lucide="library" width="18" height="18"></i>
                            Library
                        </button>
                    </div>

        <section class="settings-section {{ $activeTab === 'profile' ? 'active' : '' }}" id="profile-tab" role="tabpanel" aria-labelledby="tab-profile" data-settings-section="profile" data-start-editing="{{ $hasProfileErrors ? 'true' : 'false' }}" {{ $activeTab === 'profile' ? '' : 'hidden' }}>
            <div class="section-header">
                <div class="section-header-title">
                    <div class="section-icon"><i data-lucide="user"></i></div>
                    <div class="section-copy">
                        <h2>Profile Settings</h2>
                        <p>Update your identity, contact details, and profile photo with inline validation feedback.</p>
                    </div>
                </div>
                <div class="section-header-actions">
                    <span class="section-status" data-section-status>Read only</span>
                    <button class="btn btn-secondary" data-action="edit" type="button"><i data-lucide="edit-2" width="16" height="16"></i>Edit</button>
                    <button class="btn btn-outline" data-action="cancel" type="button" hidden><i data-lucide="x" width="16" height="16"></i>Cancel</button>
                    <button class="btn btn-primary" data-action="save" form="profileForm" type="submit" hidden><i data-lucide="save" width="16" height="16"></i>Save</button>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="profileForm" novalidate>
                @csrf
                @method('PUT')
                <input type="hidden" name="remove_profile_photo" id="remove-profile-photo" value="0">

                <div class="profile-photo-container">
                    <div class="profile-photo-placeholder" id="profilePhotoPreview">
                        @if ($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="Profile Photo">
                        @else
                            <span class="avatar-letter avatar-letter--large">{{ $defaultAvatarLetter }}</span>
                        @endif
                    </div>
                    <div class="profile-photo-upload">
                        <h3>Profile Photo</h3>
                        <p>Use a clear image for admin identification. Supported formats: JPG, PNG, GIF. Maximum size: 2MB.</p>
                        <div class="photo-actions">
                            <label class="file-upload-btn is-disabled" id="profilePhotoLabel" data-photo-upload-label for="profile-photo">
                                <i data-lucide="upload" width="16" height="16"></i>
                                Upload Photo
                            </label>
                            <button class="btn btn-outline" id="removePhotoBtn" data-remove-photo type="button" {{ $profilePhotoUrl ? '' : 'hidden' }}>
                                <i data-lucide="trash-2" width="16" height="16"></i>
                                Remove
                            </button>
                        </div>
                        <input class="sr-only-hidden" type="file" id="profile-photo" name="profile_photo" accept=".jpg,.jpeg,.png,.gif" data-editable disabled>
                        <span class="field-error" data-field="profile_photo">{{ $errors->first('profile_photo') }}</span>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="profile-name">Full Name<span class="required-marker">*</span></label>
                        <input class="form-control" type="text" id="profile-name" name="name" value="{{ old('name', $user->name) }}" data-editable disabled>
                        <span class="field-error" data-field="name">{{ $errors->first('name') }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="profile-email">Email Address<span class="required-marker">*</span></label>
                        <input class="form-control" type="email" id="profile-email" name="email" value="{{ old('email', $user->email) }}" data-editable disabled>
                        <span class="field-error" data-field="email">{{ $errors->first('email') }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="profile-phone">Phone Number<span class="required-marker">*</span></label>
                        <input class="form-control" type="text" id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}" data-editable disabled>
                        <span class="field-error" data-field="phone">{{ $errors->first('phone') }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="profile-dob">Date of Birth</label>
                        <input class="form-control" type="date" id="profile-dob" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" max="{{ now()->subYears(16)->format('Y-m-d') }}" data-editable disabled>
                        <span class="field-error" data-field="date_of_birth">{{ $errors->first('date_of_birth') }}</span>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="profile-address">Address</label>
                        <textarea class="form-control" id="profile-address" name="address" rows="4" data-editable disabled>{{ old('address', $user->address) }}</textarea>
                        <span class="field-error" data-field="address">{{ $errors->first('address') }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <div class="role-display">
                            <i data-lucide="shield" width="16" height="16"></i>
                            {{ ucfirst($user->role) }}
                        </div>
                        <div class="form-hint">Role changes are managed elsewhere for security.</div>
                    </div>
                </div>
            </form>
        </section>

        <section class="settings-section {{ $activeTab === 'password' ? 'active' : '' }}" id="password-tab" role="tabpanel" aria-labelledby="tab-password" data-settings-section="password" data-start-editing="{{ $hasPasswordErrors ? 'true' : 'false' }}" {{ $activeTab === 'password' ? '' : 'hidden' }}>
            <div class="section-header">
                <div class="section-header-title">
                    <div class="section-icon"><i data-lucide="lock"></i></div>
                    <div class="section-copy">
                        <h2>Password Settings</h2>
                        <p>Use the live checklist below to create a stronger password before submitting.</p>
                    </div>
                </div>
                <div class="section-header-actions">
                    <span class="section-status" data-section-status>Read only</span>
                    <button class="btn btn-secondary" data-action="edit" type="button"><i data-lucide="key" width="16" height="16"></i>Edit</button>
                    <button class="btn btn-outline" data-action="cancel" type="button" hidden><i data-lucide="x" width="16" height="16"></i>Cancel</button>
                    <button class="btn btn-primary" data-action="save" form="passwordForm" type="submit" hidden><i data-lucide="save" width="16" height="16"></i>Save</button>
                </div>
            </div>

            <form action="{{ route('admin.settings.update-password') }}" method="POST" id="passwordForm" novalidate>
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label" for="current-password">Current Password</label>
                        <div class="password-input-wrapper">
                            <input class="form-control" type="password" id="current-password" name="current_password" autocomplete="current-password" placeholder="Enter current password" data-editable disabled>
                            <button class="password-toggle" data-password-toggle data-target="current-password" type="button" disabled><i data-lucide="eye" width="18" height="18"></i></button>
                        </div>
                        <span class="field-error" data-field="current_password">{{ $errors->first('current_password') }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new-password">New Password</label>
                        <div class="password-input-wrapper">
                            <input class="form-control" type="password" id="new-password" name="new_password" autocomplete="new-password" placeholder="Enter new password" data-editable disabled>
                            <button class="password-toggle" data-password-toggle data-target="new-password" type="button" disabled><i data-lucide="eye" width="18" height="18"></i></button>
                        </div>
                        <span class="field-error" data-field="new_password">{{ $errors->first('new_password') }}</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm-password">Confirm New Password</label>
                        <div class="password-input-wrapper">
                            <input class="form-control" type="password" id="confirm-password" name="new_password_confirmation" autocomplete="new-password" placeholder="Confirm new password" data-editable disabled>
                            <button class="password-toggle" data-password-toggle data-target="confirm-password" type="button" disabled><i data-lucide="eye" width="18" height="18"></i></button>
                        </div>
                        <span class="field-error" data-field="new_password_confirmation">{{ $errors->first('new_password_confirmation') }}</span>
                    </div>
                </div>

                <div class="password-requirements" id="passwordRequirements">
                    <h4 class="requirements-title">Password Requirements</h4>
                    <div class="requirements-list">
                        <div class="requirement-item" data-requirement="minLength"><span class="requirement-icon"><i data-lucide="check"></i></span><span class="requirement-text">Minimum 8 characters</span></div>
                        <div class="requirement-item" data-requirement="uppercase"><span class="requirement-icon"><i data-lucide="check"></i></span><span class="requirement-text">At least one uppercase letter</span></div>
                        <div class="requirement-item" data-requirement="lowercase"><span class="requirement-icon"><i data-lucide="check"></i></span><span class="requirement-text">At least one lowercase letter</span></div>
                        <div class="requirement-item" data-requirement="number"><span class="requirement-icon"><i data-lucide="check"></i></span><span class="requirement-text">At least one number</span></div>
                        <div class="requirement-item" data-requirement="special"><span class="requirement-icon"><i data-lucide="check"></i></span><span class="requirement-text">At least one special character (!@#$%^&*)</span></div>
                        <div class="requirement-item" data-requirement="match"><span class="requirement-icon"><i data-lucide="check"></i></span><span class="requirement-text">Must match confirmation</span></div>
                        <div class="requirement-item" data-requirement="different"><span class="requirement-icon"><i data-lucide="check"></i></span><span class="requirement-text">Different from current password</span></div>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <div class="strength-text" id="strengthText">Enter a password</div>
                </div>
            </form>
        </section>

        <section class="settings-section {{ $activeTab === 'library' ? 'active' : '' }}" id="library-tab" role="tabpanel" aria-labelledby="tab-library" data-settings-section="library" data-start-editing="{{ $hasLibraryErrors ? 'true' : 'false' }}" {{ $activeTab === 'library' ? '' : 'hidden' }}>
            <div class="section-header">
                <div class="section-header-title">
                    <div class="section-icon"><i data-lucide="library"></i></div>
                    <div class="section-copy">
                        <h2>Library Settings</h2>
                        <p>Configure circulation rules, renewals, hours, reminders, and automation toggles in one place.</p>
                    </div>
                </div>
                <div class="section-header-actions">
                    <span class="section-status" data-section-status>Read only</span>
                    <button class="btn btn-secondary" data-action="edit" type="button"><i data-lucide="edit-2" width="16" height="16"></i>Edit</button>
                    <button class="btn btn-outline" data-action="cancel" type="button" hidden><i data-lucide="x" width="16" height="16"></i>Cancel</button>
                    <button class="btn btn-primary" data-action="save" form="librarySettingsForm" type="submit" hidden><i data-lucide="save" width="16" height="16"></i>Save</button>
                </div>
            </div>

            <form action="{{ route('admin.settings.update-library') }}" method="POST" id="librarySettingsForm" novalidate>
                @csrf
                @method('PUT')

                <div class="library-settings-section">
                    <h3 class="section-subtitle">Fine Rules</h3>
                    <div class="library-settings-grid">
                        <div class="form-group">
                            <label class="form-label" for="per_day_fine">Fine Per Day</label>
                            <input class="form-control" type="number" id="per_day_fine" name="per_day_fine" value="{{ old('per_day_fine', $fineSetting->per_day_fine ?? 5) }}" step="0.01" min="0" data-editable disabled>
                            <span class="field-error" data-field="per_day_fine">{{ $errors->first('per_day_fine') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="grace_period_days">Grace Period (days)</label>
                            <input class="form-control" type="number" id="grace_period_days" name="grace_period_days" value="{{ old('grace_period_days', $fineSetting->grace_period_days ?? 2) }}" min="0" max="365" data-editable disabled>
                            <span class="field-error" data-field="grace_period_days">{{ $errors->first('grace_period_days') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="max_fine_amount">Maximum Fine Amount</label>
                            <input class="form-control" type="number" id="max_fine_amount" name="max_fine_amount" value="{{ old('max_fine_amount', $fineSetting->max_fine_amount) }}" step="0.01" min="0" data-editable disabled>
                            <span class="field-error" data-field="max_fine_amount">{{ $errors->first('max_fine_amount') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="lost_book_penalty">Lost Book Penalty</label>
                            <input class="form-control" type="number" id="lost_book_penalty" name="lost_book_penalty" value="{{ old('lost_book_penalty', $fineSetting->lost_book_penalty ?? 1000) }}" step="0.01" min="0" data-editable disabled>
                            <span class="field-error" data-field="lost_book_penalty">{{ $errors->first('lost_book_penalty') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="damaged_book_penalty">Damaged Book Penalty</label>
                            <input class="form-control" type="number" id="damaged_book_penalty" name="damaged_book_penalty" value="{{ old('damaged_book_penalty', $fineSetting->damaged_book_penalty) }}" step="0.01" min="0" data-editable disabled>
                            <span class="field-error" data-field="damaged_book_penalty">{{ $errors->first('damaged_book_penalty') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="fair_condition_penalty">Fair Book Penalty</label>
                            <input class="form-control" type="number" id="fair_condition_penalty" name="fair_condition_penalty" value="{{ old('fair_condition_penalty', $fineSetting->fair_condition_penalty ?? 50) }}" step="0.01" min="0" data-editable disabled>
                            <span class="field-error" data-field="fair_condition_penalty">{{ $errors->first('fair_condition_penalty') }}</span>
                        </div>
                    </div>
                </div>

                <div class="library-settings-section">
                    <h3 class="section-subtitle">Renewal & Lending</h3>
                    <div class="library-settings-grid">
                        <div class="form-group">
                            <label class="form-label" for="issue_duration_days">Issue Duration (days)</label>
                            <input class="form-control" type="number" id="issue_duration_days" name="issue_duration_days" value="{{ old('issue_duration_days', $fineSetting->issue_duration_days ?? 14) }}" min="1" max="365" data-editable disabled>
                            <span class="field-error" data-field="issue_duration_days">{{ $errors->first('issue_duration_days') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="max_books_per_student">Max Books Per Student</label>
                            <input class="form-control" type="number" id="max_books_per_student" name="max_books_per_student" value="{{ old('max_books_per_student', $fineSetting->max_books_per_student ?? 5) }}" min="1" max="100" data-editable disabled>
                            <span class="field-error" data-field="max_books_per_student">{{ $errors->first('max_books_per_student') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="renewal_limit">Renewal Limit</label>
                            <input class="form-control" type="number" id="renewal_limit" name="renewal_limit" value="{{ old('renewal_limit', $fineSetting->renewal_limit ?? 2) }}" min="0" max="10" data-editable disabled>
                            <span class="field-error" data-field="renewal_limit">{{ $errors->first('renewal_limit') }}</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="renewal_duration_days">Renewal Duration (days)</label>
                            <input class="form-control" type="number" id="renewal_duration_days" name="renewal_duration_days" value="{{ old('renewal_duration_days', $fineSetting->renewal_duration_days ?? 7) }}" min="1" max="30" data-editable disabled>
                            <span class="field-error" data-field="renewal_duration_days">{{ $errors->first('renewal_duration_days') }}</span>
                        </div>
                    </div>
                </div>

            </form>
        </section>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const settingsPage = document.getElementById('adminSettingsPage');
            if (!settingsPage) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const assetBaseUrl = settingsPage.dataset.assetBase ?? window.location.origin;
            const defaultAvatarLetter = settingsPage.dataset.defaultAvatarLetter ?? 'A';
            const sections = Array.from(settingsPage.querySelectorAll('[data-settings-section]'));
            const tabButtons = Array.from(settingsPage.querySelectorAll('.tab-btn'));
            const sectionControllers = new Map();

            const profilePhotoPreview = document.getElementById('profilePhotoPreview');
            const profilePhotoInput = document.getElementById('profile-photo');
            const profilePhotoLabel = document.getElementById('profilePhotoLabel');
            const removePhotoBtn = document.getElementById('removePhotoBtn');
            const removePhotoInput = document.getElementById('remove-profile-photo');
            const profileNameInput = document.getElementById('profile-name');
            const profileEmailInput = document.getElementById('profile-email');
            const profilePhoneInput = document.getElementById('profile-phone');
            const adminAvatarImage = document.getElementById('adminAvatarImage');
            const adminAvatarFallback = document.getElementById('adminAvatarFallback');
            const adminCardName = document.getElementById('adminCardName');
            const adminCardEmail = document.getElementById('adminCardEmail');
            const adminCardUsername = document.getElementById('adminCardUsername');
            const adminCardPhone = document.getElementById('adminCardPhone');

            const currentPasswordInput = document.getElementById('current-password');
            const newPasswordInput = document.getElementById('new-password');
            const confirmPasswordInput = document.getElementById('confirm-password');
            const requirementItems = Array.from(document.querySelectorAll('.requirement-item'));
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');

            initializeToastSystem();
            initializeTabs();
            initializeSections();
            initializeProfilePhotoHandlers();
            initializePasswordToggles();
            initializePasswordRequirements();
            initializeUnsavedChangesGuard();

            @if (session('success'))
                showToast({{ Js::from(session('success')) }}, 'success');
            @endif

            @if ($errors->any())
                showToast('Please correct the highlighted fields before saving.', 'error');
            @endif

            lucide.createIcons();
            syncProfileCard({ photoUrl: extractPreviewImageUrl() });

            function initializeTabs() {
                tabButtons.forEach((button) => {
                    const isActive = button.classList.contains('active');
                    button.setAttribute('tabindex', isActive ? '0' : '-1');

                    button.addEventListener('click', () => {
                        const targetTab = button.dataset.tab;
                        tabButtons.forEach((tab) => {
                            const active = tab === button;
                            tab.classList.toggle('active', active);
                            tab.setAttribute('aria-selected', active ? 'true' : 'false');
                            tab.setAttribute('tabindex', active ? '0' : '-1');
                        });

                        sections.forEach((section) => {
                            const active = section.id === `${targetTab}-tab`;
                            section.classList.toggle('active', active);
                            section.hidden = !active;
                        });
                    });
                });
            }

            function initializeSections() {
                sections.forEach((section) => {
                    const form = section.querySelector('form');
                    const controller = {
                        name: section.dataset.settingsSection,
                        section,
                        form,
                        status: section.querySelector('[data-section-status]'),
                        editButton: section.querySelector('[data-action="edit"]'),
                        cancelButton: section.querySelector('[data-action="cancel"]'),
                        saveButton: section.querySelector('[data-action="save"]'),
                        editing: false,
                        dirty: false,
                        saving: false,
                        justSaved: false,
                        snapshot: captureSnapshot(form),
                    };

                    sectionControllers.set(controller.name, controller);

                    controller.editButton?.addEventListener('click', () => {
                        controller.snapshot = captureSnapshot(form);
                        clearFormErrors(form);
                        setSectionMode(controller, true);

                        if (controller.name === 'password') {
                            form.reset();
                            updatePasswordRequirements();
                        }
                    });

                    controller.cancelButton?.addEventListener('click', () => {
                        restoreSnapshot(controller);
                        clearFormErrors(form);
                        controller.dirty = false;
                        setSectionMode(controller, false);

                        if (controller.name === 'password') {
                            updatePasswordRequirements();
                        }
                    });

                    form.addEventListener('submit', (event) => submitForm(event, controller));

                    form.querySelectorAll('[data-editable]').forEach((field) => {
                        field.addEventListener('input', () => handleFieldInteraction(controller, field));
                        field.addEventListener('change', () => handleFieldInteraction(controller, field));

                        if (controller.name === 'profile') {
                            field.addEventListener('blur', () => validateProfileField(field));
                        }
                    });

                    if (section.dataset.startEditing === 'true') {
                        setSectionMode(controller, true);
                    } else {
                        setSectionMode(controller, false);
                    }
                });
            }

            function initializeProfilePhotoHandlers() {
                const profileController = sectionControllers.get('profile');

                if (!profileController || !profilePhotoInput || !profilePhotoPreview || !removePhotoInput) {
                    return;
                }

                profilePhotoInput.addEventListener('change', () => {
                    if (!profileController.editing) {
                        return;
                    }

                    const file = profilePhotoInput.files?.[0];
                    removePhotoInput.value = '0';

                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (event) => {
                            const photoUrl = event.target?.result ?? '';
                            renderProfilePhotoPreview(photoUrl, profileNameInput?.value);
                            syncProfileCard({ photoUrl });
                            updateProfilePhotoControls(profileController);
                            updateDirtyState(profileController);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        syncProfileCard({ photoUrl: extractPreviewImageUrl() });
                        updateProfilePhotoControls(profileController);
                        updateDirtyState(profileController);
                    }
                });

                removePhotoBtn?.addEventListener('click', (event) => {
                    event.preventDefault();

                    if (!profileController.editing) {
                        return;
                    }

                    profilePhotoInput.value = '';
                    removePhotoInput.value = '1';
                    renderProfilePhotoPreview('', profileNameInput?.value);
                    syncProfileCard({ photoUrl: '' });
                    updateProfilePhotoControls(profileController);
                    updateDirtyState(profileController);
                });
            }

            function initializePasswordToggles() {
                document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const target = document.getElementById(button.dataset.target);
                        const icon = button.querySelector('i');

                        if (!target) {
                            return;
                        }

                        const passwordMode = target.type === 'password';
                        target.type = passwordMode ? 'text' : 'password';
                        icon?.setAttribute('data-lucide', passwordMode ? 'eye-off' : 'eye');
                        lucide.createIcons();
                    });
                });
            }

            function initializePasswordRequirements() {
                [currentPasswordInput, newPasswordInput, confirmPasswordInput].forEach((input) => {
                    input?.addEventListener('input', updatePasswordRequirements);
                });

                updatePasswordRequirements();
            }

            function initializeUnsavedChangesGuard() {
                window.addEventListener('beforeunload', (event) => {
                    const hasUnsavedChanges = Array.from(sectionControllers.values()).some((controller) => controller.editing && controller.dirty && !controller.saving);
                    if (!hasUnsavedChanges) {
                        return;
                    }

                    event.preventDefault();
                    event.returnValue = '';
                });
            }

            function captureSnapshot(form) {
                const snapshot = { comparable: {}, restore: {}, profilePreviewHtml: null };

                form.querySelectorAll('[data-editable]').forEach((field) => {
                    if (!field.name) {
                        return;
                    }

                    if (field.type === 'checkbox') {
                        snapshot.comparable[field.name] = field.checked;
                        snapshot.restore[field.name] = field.checked;
                        return;
                    }

                    if (field.type === 'file') {
                        snapshot.comparable[field.name] = field.files?.[0]?.name ?? '';
                        snapshot.restore[field.name] = '';
                        return;
                    }

                    snapshot.comparable[field.name] = field.value;
                    snapshot.restore[field.name] = field.value;
                });

                if (form.id === 'profileForm') {
                    snapshot.comparable.remove_profile_photo = removePhotoInput?.value ?? '0';
                    snapshot.restore.remove_profile_photo = removePhotoInput?.value ?? '0';
                    snapshot.profilePreviewHtml = profilePhotoPreview?.innerHTML ?? '';
                }

                return snapshot;
            }

            function restoreSnapshot(controller) {
                const snapshot = controller.snapshot;

                controller.form.querySelectorAll('[data-editable]').forEach((field) => {
                    if (!field.name || !(field.name in snapshot.restore)) {
                        return;
                    }

                    if (field.type === 'checkbox') {
                        field.checked = Boolean(snapshot.restore[field.name]);
                        return;
                    }

                    if (field.type === 'file') {
                        field.value = '';
                        return;
                    }

                    field.value = snapshot.restore[field.name] ?? '';
                });

                if (controller.name === 'profile' && profilePhotoPreview && removePhotoInput) {
                    removePhotoInput.value = snapshot.restore.remove_profile_photo ?? '0';

                    if (snapshot.profilePreviewHtml) {
                        profilePhotoPreview.innerHTML = snapshot.profilePreviewHtml;
                    } else {
                        renderProfilePhotoPreview('', profileNameInput?.value);
                    }

                    updateProfilePhotoControls(controller);
                    syncProfileCard({ photoUrl: extractPreviewImageUrl() });
                }
            }

            function toggleEditableFields(form, enabled) {
                form.querySelectorAll('[data-editable]').forEach((field) => {
                    field.disabled = !enabled;
                });

                form.querySelectorAll('[data-password-toggle]').forEach((button) => {
                    button.disabled = !enabled;
                });
            }

            function setSectionMode(controller, editing) {
                controller.editing = editing;
                controller.section.classList.toggle('edit-mode', editing);
                toggleEditableFields(controller.form, editing);
                controller.editButton.hidden = editing;
                controller.cancelButton.hidden = !editing;
                controller.saveButton.hidden = !editing;

                if (controller.name === 'profile') {
                    profilePhotoLabel?.classList.toggle('is-disabled', !editing);
                    updateProfilePhotoControls(controller);
                }

                if (controller.name === 'password') {
                    updatePasswordRequirements();
                }

                if (!editing) {
                    controller.dirty = false;
                }

                refreshSectionStatus(controller);
            }

            function refreshSectionStatus(controller) {
                if (!controller.status) {
                    return;
                }

                controller.status.classList.remove('editing', 'dirty', 'success');

                if (controller.saving) {
                    controller.status.textContent = 'Saving...';
                    controller.status.classList.add('editing');
                    return;
                }

                if (controller.dirty) {
                    controller.status.textContent = 'Unsaved changes';
                    controller.status.classList.add('dirty');
                    return;
                }

                if (controller.editing) {
                    controller.status.textContent = 'Editing';
                    controller.status.classList.add('editing');
                    return;
                }

                if (controller.justSaved) {
                    controller.status.textContent = 'Saved';
                    controller.status.classList.add('success');
                    return;
                }

                controller.status.textContent = 'Read only';
            }

            function captureComparableState(form) {
                const comparable = {};

                form.querySelectorAll('[data-editable]').forEach((field) => {
                    if (!field.name) {
                        return;
                    }

                    if (field.type === 'checkbox') {
                        comparable[field.name] = field.checked;
                        return;
                    }

                    if (field.type === 'file') {
                        comparable[field.name] = field.files?.[0]?.name ?? '';
                        return;
                    }

                    comparable[field.name] = field.value;
                });

                if (form.id === 'profileForm') {
                    comparable.remove_profile_photo = removePhotoInput?.value ?? '0';
                }

                return comparable;
            }

            function updateDirtyState(controller) {
                controller.dirty = JSON.stringify(captureComparableState(controller.form)) !== JSON.stringify(controller.snapshot.comparable);
                refreshSectionStatus(controller);
            }

            function handleFieldInteraction(controller, field) {
                if (!controller.editing) {
                    return;
                }

                if (controller.name === 'profile' && field.name !== 'profile_photo') {
                    validateProfileField(field, true);

                    if (field.name === 'name' && !extractPreviewImageUrl()) {
                        renderProfilePhotoPreview('', field.value);
                    }

                    syncProfileCard();
                } else {
                    clearFieldError(controller.form, field.name);
                }

                if (controller.name === 'password') {
                    updatePasswordRequirements();
                }

                updateDirtyState(controller);
            }

            async function submitForm(event, controller) {
                event.preventDefault();
                clearFormErrors(controller.form);

                if (controller.name === 'profile' && !validateProfileForm(controller.form)) {
                    showToast('Please correct the highlighted profile fields.', 'error');
                    return;
                }

                if (controller.name === 'password' && !getPasswordRequirementState().allMet) {
                    showToast('Please satisfy all password requirements before saving.', 'error');
                    return;
                }

                controller.saving = true;
                refreshSectionStatus(controller);
                setButtonLoading(controller.saveButton, true, 'Saving...');

                try {
                    const response = await fetch(controller.form.action, {
                        method: 'POST',
                        body: new FormData(controller.form),
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const payload = await parseResponse(response);

                    if (response.ok && payload?.success) {
                        finalizeSuccessfulSave(controller, payload);
                        showToast(payload.message ?? 'Settings updated successfully.', 'success');
                        return;
                    }

                    if (response.status === 422 && payload?.errors) {
                        applyFormErrors(controller.form, payload.errors);
                        showToast(payload.message ?? 'Please correct the highlighted fields.', 'error');
                        return;
                    }

                    showToast(payload?.message ?? 'Something went wrong while saving.', 'error');
                } catch (error) {
                    showToast(`Failed to save changes: ${error.message}`, 'error');
                } finally {
                    controller.saving = false;
                    setButtonLoading(controller.saveButton, false);
                    refreshSectionStatus(controller);
                }
            }

            function finalizeSuccessfulSave(controller, payload) {
                clearFormErrors(controller.form);

                if (controller.name === 'password') {
                    controller.form.reset();
                    updatePasswordRequirements();
                }

                if (controller.name === 'profile') {
                    applyProfilePayload(payload.user ?? null);
                    profilePhotoInput.value = '';
                    removePhotoInput.value = '0';

                    if (!payload.user?.profile_photo && profilePhotoPreview) {
                        renderProfilePhotoPreview('', profileNameInput?.value);
                    }

                    syncProfileCard({ photoUrl: extractPreviewImageUrl() });
                }

                controller.snapshot = captureSnapshot(controller.form);
                controller.dirty = false;
                controller.justSaved = true;
                setSectionMode(controller, false);

                window.clearTimeout(controller.savedTimeout);
                controller.savedTimeout = window.setTimeout(() => {
                    controller.justSaved = false;
                    refreshSectionStatus(controller);
                }, 2200);
            }

            function validateProfileForm(form) {
                const fields = ['name', 'email', 'phone', 'address', 'date_of_birth'];
                return fields.every((fieldName) => {
                    const field = form.querySelector(`[name="${fieldName}"]`);
                    return field ? validateProfileField(field) : true;
                });
            }

            function validateProfileField(field, soft = false) {
                if (!field?.name) {
                    return true;
                }

                const validators = {
                    name(value) {
                        if (!value) return 'This field is required.';
                        if (value.length < 2) return 'Minimum 2 characters required.';
                        if (value.length > 100) return 'Maximum 100 characters allowed.';
                        if (!/^[a-zA-Z\s]+$/.test(value)) return 'Name can only contain letters and spaces.';
                        return '';
                    },
                    email(value) {
                        if (!value) return 'This field is required.';
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Enter a valid email address.';
                        return '';
                    },
                    phone(value) {
                        if (!value) return 'This field is required.';
                        if (!/^[0-9]{10,15}$/.test(value)) return 'Phone must contain only numbers (10-15 digits).';
                        return '';
                    },
                    address(value) {
                        if (!value) return '';
                        if (value.length < 10) return 'Address must be at least 10 characters.';
                        if (value.length > 500) return 'Maximum 500 characters allowed.';
                        return '';
                    },
                    date_of_birth(value) {
                        if (!value) return '';

                        const birthDate = new Date(`${value}T00:00:00`);
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);

                        const hundredYearsAgo = new Date(today);
                        hundredYearsAgo.setFullYear(today.getFullYear() - 100);

                        if (Number.isNaN(birthDate.getTime())) return 'Enter a valid date of birth.';
                        if (birthDate >= today) return 'Date of birth must be in the past.';
                        if (birthDate < hundredYearsAgo) return 'Date of birth must be within the last 100 years.';
                        return '';
                    },
                };

                const value = field.value.trim();
                const error = validators[field.name] ? validators[field.name](value) : '';
                setFieldState(field, error, soft);
                return !error;
            }

            function setFieldState(field, error, soft = false) {
                const formGroup = field.closest('.form-group');
                const errorElement = field.form?.querySelector(`.field-error[data-field="${field.name}"]`);

                if (!formGroup) {
                    return;
                }

                formGroup.classList.toggle('has-error', Boolean(error));
                formGroup.classList.toggle('has-success', !error && Boolean(field.value.trim()) && !soft);

                if (errorElement) {
                    errorElement.textContent = error;
                }
            }

            function clearFormErrors(form) {
                form.querySelectorAll('.form-group').forEach((group) => {
                    group.classList.remove('has-error');
                    group.classList.remove('has-success');
                });

                form.querySelectorAll('.field-error').forEach((errorElement) => {
                    errorElement.textContent = '';
                });
            }

            function clearFieldError(form, fieldName) {
                if (!fieldName) {
                    return;
                }

                const field = form.querySelector(`[name="${fieldName}"]`);
                const formGroup = field?.closest('.form-group');
                const errorElement = form.querySelector(`.field-error[data-field="${fieldName}"]`);
                formGroup?.classList.remove('has-error');

                if (errorElement) {
                    errorElement.textContent = '';
                }
            }

            function applyFormErrors(form, errors) {
                clearFormErrors(form);

                Object.entries(errors).forEach(([fieldName, messages]) => {
                    const message = Array.isArray(messages) ? messages[0] : messages;
                    const errorElement = form.querySelector(`.field-error[data-field="${fieldName}"]`);
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    const formGroup = input?.closest('.form-group');

                    formGroup?.classList.add('has-error');
                    formGroup?.classList.remove('has-success');

                    if (errorElement) {
                        errorElement.textContent = message;
                    }
                });
            }

            function getPasswordRequirementState() {
                const context = {
                    current: currentPasswordInput?.value ?? '',
                    password: newPasswordInput?.value ?? '',
                    confirmation: confirmPasswordInput?.value ?? '',
                };

                const checks = {
                    minLength: ({ password }) => password.length >= 8,
                    uppercase: ({ password }) => /[A-Z]/.test(password),
                    lowercase: ({ password }) => /[a-z]/.test(password),
                    number: ({ password }) => /\d/.test(password),
                    special: ({ password }) => /[!@#$%^&*(),.?":{}|<>]/.test(password),
                    match: ({ password, confirmation }) => Boolean(password && confirmation && password === confirmation),
                    different: ({ password, current }) => Boolean(password && current && password !== current),
                };

                const results = {};
                let metCount = 0;

                Object.entries(checks).forEach(([key, validator]) => {
                    results[key] = validator(context);
                    if (results[key]) {
                        metCount++;
                    }
                });

                return {
                    results,
                    metCount,
                    allMet: Object.values(results).every(Boolean),
                    hasPassword: context.password.length > 0,
                };
            }

            function updatePasswordRequirements() {
                const state = getPasswordRequirementState();

                requirementItems.forEach((item) => {
                    item.classList.toggle('met', Boolean(state.results[item.dataset.requirement]));
                });

                let strengthClass = '';
                let strengthLabel = 'Enter a password';

                if (state.hasPassword && state.metCount <= 3) {
                    strengthClass = 'weak';
                    strengthLabel = 'Weak - Keep going!';
                } else if (state.hasPassword && state.metCount <= 5) {
                    strengthClass = 'fair';
                    strengthLabel = 'Fair - Add more requirements';
                } else if (state.hasPassword && state.metCount === 6) {
                    strengthClass = 'good';
                    strengthLabel = 'Good - Almost there!';
                } else if (state.hasPassword && state.metCount === 7) {
                    strengthClass = 'strong';
                    strengthLabel = 'Strong password!';
                }

                strengthFill.className = `strength-fill ${strengthClass}`.trim();
                strengthText.textContent = strengthLabel;

                const controller = sectionControllers.get('password');
                if (controller?.saveButton) {
                    controller.saveButton.disabled = controller.editing ? !state.allMet : false;
                }
            }

            function updateProfilePhotoControls(controller) {
                const hasPreviewImage = Boolean(profilePhotoPreview?.querySelector('img'));
                if (removePhotoBtn) {
                    removePhotoBtn.hidden = !(controller.editing && hasPreviewImage);
                }
            }

            function getAvatarLetter(name = '') {
                const normalizedName = String(name || '').trim();
                return (normalizedName.charAt(0) || defaultAvatarLetter).toUpperCase();
            }

            function resolvePhotoUrl(value = '') {
                const normalizedValue = String(value || '').trim();

                if (!normalizedValue) {
                    return '';
                }

                if (/^(?:https?:|data:|blob:)/i.test(normalizedValue)) {
                    return normalizedValue;
                }

                return `${assetBaseUrl}/${normalizedValue.replace(/^\/+/, '')}`;
            }

            function extractPreviewImageUrl() {
                return profilePhotoPreview?.querySelector('img')?.getAttribute('src') ?? '';
            }

            function renderProfilePhotoPreview(photoUrl = '', name = profileNameInput?.value ?? '') {
                if (!profilePhotoPreview) {
                    return;
                }

                profilePhotoPreview.innerHTML = '';

                if (photoUrl) {
                    const image = document.createElement('img');
                    image.src = photoUrl;
                    image.alt = 'Profile Photo Preview';
                    profilePhotoPreview.appendChild(image);
                    return;
                }

                const fallback = document.createElement('span');
                fallback.className = 'avatar-letter avatar-letter--large';
                fallback.textContent = getAvatarLetter(name);
                profilePhotoPreview.appendChild(fallback);
            }

            function renderProfileCardAvatar(photoUrl = '', name = profileNameInput?.value ?? '') {
                if (adminAvatarImage) {
                    adminAvatarImage.src = photoUrl || '';
                    adminAvatarImage.hidden = !photoUrl;
                }

                if (adminAvatarFallback) {
                    adminAvatarFallback.textContent = getAvatarLetter(name);
                    adminAvatarFallback.hidden = Boolean(photoUrl);
                }
            }

            function deriveUsername(email = '') {
                const normalizedEmail = String(email || '').trim();
                return normalizedEmail.includes('@') ? normalizedEmail.split('@')[0] : 'admin';
            }

            function syncProfileCard(overrides = {}) {
                const name = String(overrides.name ?? profileNameInput?.value ?? adminCardName?.textContent ?? '').trim() || 'Admin';
                const email = String(overrides.email ?? profileEmailInput?.value ?? adminCardEmail?.textContent ?? '').trim();
                const phone = String(overrides.phone ?? profilePhoneInput?.value ?? adminCardPhone?.textContent ?? '').trim();
                const photoUrl = Object.prototype.hasOwnProperty.call(overrides, 'photoUrl')
                    ? overrides.photoUrl
                    : extractPreviewImageUrl();

                if (adminCardName) {
                    adminCardName.textContent = name;
                }

                if (adminCardEmail) {
                    adminCardEmail.textContent = email || 'No email added';
                }

                if (adminCardUsername) {
                    adminCardUsername.textContent = deriveUsername(email);
                }

                if (adminCardPhone) {
                    adminCardPhone.textContent = phone || 'Not added';
                }

                renderProfileCardAvatar(photoUrl, name);
            }

            function applyProfilePayload(user) {
                if (!user) {
                    return;
                }

                const fieldMap = {
                    name: 'name',
                    email: 'email',
                    phone: 'phone',
                    address: 'address',
                    date_of_birth: 'date_of_birth',
                };

                Object.entries(fieldMap).forEach(([fieldName, payloadKey]) => {
                    const field = document.querySelector(`#profileForm [name="${fieldName}"]`);
                    if (!field || !(payloadKey in user)) {
                        return;
                    }

                    let fieldValue = user[payloadKey] ?? '';

                    if (fieldName === 'date_of_birth' && fieldValue) {
                        fieldValue = String(fieldValue).slice(0, 10);
                    }

                    field.value = fieldValue;
                });

                const resolvedPhotoUrl = resolvePhotoUrl(user.profile_photo ?? '');
                renderProfilePhotoPreview(resolvedPhotoUrl, user.name ?? profileNameInput?.value ?? '');
                syncProfileCard({
                    name: user.name ?? profileNameInput?.value ?? '',
                    email: user.email ?? profileEmailInput?.value ?? '',
                    phone: user.phone ?? profilePhoneInput?.value ?? '',
                    photoUrl: resolvedPhotoUrl,
                });
            }

            function parseResponse(response) {
                return response.text().then((text) => {
                    if (!text) {
                        return {};
                    }

                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error('Invalid server response.');
                    }
                });
            }

            function setButtonLoading(button, loading, label = 'Saving...') {
                if (!button) {
                    return;
                }

                if (loading) {
                    button.dataset.originalHtml = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = `<span style="width:16px;height:16px;border:2px solid rgba(255,255,255,.35);border-top-color:currentColor;border-radius:999px;display:inline-block;animation:spin .8s linear infinite;"></span><span>${label}</span>`;
                    return;
                }

                if (button.dataset.originalHtml) {
                    button.innerHTML = button.dataset.originalHtml;
                }

                button.disabled = false;
                lucide.createIcons();
            }

            function initializeToastSystem() {
                if (document.getElementById('toast-container')) {
                    return;
                }

                const container = document.createElement('div');
                container.id = 'toast-container';
                container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
                document.body.appendChild(container);

                const style = document.createElement('style');
                style.textContent = `
                    @keyframes slideInToast { from { transform: translateX(24px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
                    @keyframes slideOutToast { from { transform: translateX(0); opacity: 1; } to { transform: translateX(24px); opacity: 0; } }
                `;
                document.head.appendChild(style);
            }

            function showToast(message, type = 'info', duration = 4200) {
                const icons = {
                    success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
                    error: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                    info: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>',
                };

                const colors = { success: '#10b981', error: '#ef4444', info: '#3b82f6' };
                const container = document.getElementById('toast-container');

                if (!container) {
                    return;
                }

                const toast = document.createElement('div');
                toast.style.cssText = `
                    min-width:280px;max-width:360px;padding:14px 16px;border-radius:14px;
                    background:${colors[type] ?? colors.info};color:white;display:flex;align-items:center;gap:10px;
                    box-shadow:0 16px 28px -18px rgba(15,23,42,.6);font-size:.9rem;pointer-events:auto;
                    animation:slideInToast .2s ease-out forwards;
                `;

                toast.innerHTML = `
                    ${icons[type] ?? icons.info}
                    <span style="flex:1;">${message}</span>
                    <button type="button" style="border:none;background:transparent;color:inherit;cursor:pointer;font-size:1rem;line-height:1;">×</button>
                `;

                toast.querySelector('button')?.addEventListener('click', () => toast.remove());
                container.appendChild(toast);

                if (duration > 0) {
                    window.setTimeout(() => {
                        toast.style.animation = 'slideOutToast .2s ease-in forwards';
                        window.setTimeout(() => toast.remove(), 220);
                    }, duration);
                }
            }
        });
    </script>
@endpush
