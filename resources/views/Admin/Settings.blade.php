@extends('Admin.layouts.app')

@section('page-title', 'Settings')

@push('styles')
    <style>
        /* Settings Page Styles */
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .settings-header {
            margin-bottom: 1.2rem;
        }

        .settings-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: var(--text-primary);
        }

        .settings-header p {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Settings Sections */
        .settings-section {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 0.8rem;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            display: none;
        }

        .settings-section.active {
            display: block;
        }

        /* Tabs Navigation */
        .tabs-container {
            display: flex;
            gap: 0;
            border-bottom: 2px solid var(--card-border);
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }

        .tab-button {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .tab-button:hover {
            color: var(--text-primary);
            background-color: var(--toggle-bg);
        }

        .tab-button.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section-header-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
        }

        .section-header-actions {
            display: flex;
            gap: 0.5rem;
        }

        .section-header h2 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .section-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--icon-bg);
            color: var(--icon-color);
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.8rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--input-border);
            border-radius: 0.75rem;
            background-color: var(--input-bg);
            color: var(--input-text);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-shadow);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        /* Profile Photo */
        .profile-photo-container {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .profile-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--card-border);
        }

        .profile-photo-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--icon-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid var(--card-border);
        }

        .profile-photo-upload {
            flex: 1;
        }

        .upload-hint {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }

        /* Role Display */
        .role-display {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background-color: var(--success-light);
            color: var(--success-color);
            border-radius: 2rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .dark-theme .role-display {
            background-color: rgba(34, 197, 94, 0.2);
        }

        /* Password Input Wrapper */
        .password-input-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--text-primary);
        }

        /* Library Settings */
        .library-settings-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 640px) {
            .library-settings-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .library-settings-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* Toggle Switches */
        .toggle-group {
            margin-bottom: 1.5rem;
        }

        .toggle-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background-color: var(--toggle-bg);
            border-radius: 0.75rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .toggle-item:hover {
            background-color: var(--toggle-hover);
        }

        .toggle-label {
            flex: 1;
            margin-right: 1rem;
        }

        .toggle-label h3 {
            font-size: 0.95rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .toggle-label p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--toggle-off);
            border-radius: 34px;
            transition: .4s;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            border-radius: 50%;
            transition: .4s;
        }

        input:checked + .toggle-slider {
            background-color: var(--primary-color);
        }

        input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        /* Buttons */
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-primary);
            border: 1px solid var(--card-border);
        }

        .btn-outline:hover {
            background-color: var(--toggle-bg);
            border-color: var(--primary-color);
        }

        .btn-secondary {
            background-color: var(--toggle-bg);
            color: var(--text-primary);
            border: 1px solid var(--card-border);
        }

        .btn-secondary:hover {
            background-color: var(--toggle-hover);
            border-color: var(--primary-color);
        }

        .btn-full {
            width: 100%;
            justify-content: center;
        }

        /* Button Group */
        .button-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            justify-content: flex-start;
        }

        .button-group .btn {
            white-space: nowrap;
        }

        /* Compact Button (for header) */
        .btn-compact {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-compact svg {
            width: 14px;
            height: 14px;
        }

        /* File Upload Button */
        .file-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--toggle-bg);
            color: var(--text-primary);
            border: 1px dashed var(--card-border);
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-btn:hover {
            border-color: var(--primary-color);
            background-color: var(--toggle-hover);
        }

        .file-remove-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .file-remove-btn:hover {
            background-color: #dc2626;
        }

        .file-remove-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 0.5rem;
            transition: background-color 0.3s ease;
        }

        .file-remove-btn:hover {
            background-color: #dc2626;
        }

        /* CSS Variables for Themes */
        :root {
            /* Light Theme Variables */
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-shadow: rgba(37, 99, 235, 0.1);
            --success-color: #059669;
            --success-light: #d1fae5;
            --card-bg: #ffffff;
            --card-border: #e5e7eb;
            --input-bg: #ffffff;
            --input-border: #d1d5db;
            --input-text: #1f2937;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --icon-bg: #f3f4f6;
            --icon-color: #4b5563;
            --toggle-bg: #f9fafb;
            --toggle-hover: #f3f4f6;
            --toggle-off: #d1d5db;
        }

        .dark-theme {
            /* Dark Theme Variables */
            --primary-color: #3b82f6;
            --primary-hover: #60a5fa;
            --primary-shadow: rgba(59, 130, 246, 0.2);
            --success-color: #10b981;
            --success-light: rgba(16, 185, 129, 0.1);
            --card-bg: #1e293b;
            --card-border: #334155;
            --input-bg: #0f172a;
            --input-border: #475569;
            --input-text: #f1f5f9;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --icon-bg: #334155;
            --icon-color: #cbd5e1;
            --toggle-bg: #1e293b;
            --toggle-hover: #334155;
            --toggle-off: #475569;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .settings-section {
                padding: 1rem;
            }

            .profile-photo-container {
                flex-direction: column;
                align-items: flex-start;
            }

            .library-settings-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .tab-button {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 640px) {
            .settings-section {
                padding: 1rem;
            }

            .library-settings-grid {
                grid-template-columns: repeat(1, 1fr);
            }

            .tabs-container {
                gap: 0;
                overflow-x: auto;
            }

            .tab-button {
                padding: 0.75rem 0.75rem;
                font-size: 0.8rem;
                min-width: fit-content;
            }
        }
    </style>
@endpush

@section('content')
    <div class="settings-container">
        <div class="settings-header">
            <h1 class="text-primary">Settings</h1>
            <p class="text-secondary">Manage your account and library configuration</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <button class="tab-button active" data-tab="profile">
                <i data-lucide="user" width="18" height="18"></i>
                Profile Settings
            </button>
            <button class="tab-button" data-tab="password">
                <i data-lucide="lock" width="18" height="18"></i>
                Password Settings
            </button>
            <button class="tab-button" data-tab="library">
                <i data-lucide="library" width="18" height="18"></i>
                Library Settings
            </button>
        </div>

        <!-- Profile Settings -->
        <div class="settings-section active" id="profile-tab">
            <div class="section-header">
                <div class="section-header-title">
                    <div class="section-icon">
                        <i data-lucide="user"></i>
                    </div>
                    <h2>Profile Settings</h2>
                    <button type="button" class="btn btn-compact btn-secondary edit-profile-btn" id="editProfileBtn" title="Edit Profile" style="margin-left: 0.5rem;">
                        <i data-lucide="edit-2" width="14" height="14"></i>
                        Edit
                    </button>
                    <button type="button" class="btn btn-compact btn-outline cancel-profile-btn" id="cancelProfileBtn" style="display: none; margin-left: 0.5rem;" title="Cancel">
                        <i data-lucide="x" width="14" height="14"></i>
                    </button>
                    <button type="button" class="btn btn-compact btn-primary submit-profile-btn" id="submitProfileBtn" form="profileForm" style="display: none; margin-left: 0.5rem;" title="Save">
                        <i data-lucide="check" width="14" height="14"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                @csrf
                @method('PUT')

                <div class="profile-photo-container">
                    <div class="profile-photo-placeholder" id="profilePhotoPreview">
                        @if($user->profile_photo)
                            <img src="{{ str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo) }}" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        @else
                            <i data-lucide="user" width="48" height="48"></i>
                        @endif
                    </div>
                    <div class="profile-photo-upload">
                        <label class="form-label">Profile Photo</label>
                        <div>
                            <label for="profile-photo" class="file-upload-btn" id="profilePhotoLabel">
                                <i data-lucide="upload" width="16" height="16"></i>
                                Upload Photo
                            </label>
                            @if($user->profile_photo)
                                <button type="button" class="file-remove-btn" id="removePhotoBtn">
                                    <i data-lucide="trash-2" width="16" height="16"></i>
                                    Remove Photo
                                </button>
                            @endif
                            <input type="file" id="profile-photo" name="profile_photo" accept=".jpg,.jpeg,.png,.gif" class="hidden" disabled>
                            <p class="upload-hint">JPG, PNG or GIF. Max size 2MB</p>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" value="{{ $user->name }}" required disabled>
                        @error('name')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" value="{{ $user->email }}" required disabled>
                        @error('email')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="{{ $user->phone }}" disabled>
                        @error('phone')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" value="{{ $user->address }}" disabled>
                        @error('address')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <div class="role-display">
                            <i data-lucide="shield" width="14" height="14"></i>
                            {{ ucfirst($user->role) }}
                        </div>
                    </div>
                </div>

                <div class="button-group" style="display: none;">
                    <button type="button" class="btn btn-secondary edit-profile-btn" id="editProfileBtn">
                        <i data-lucide="edit-2" width="18" height="18"></i>
                        Edit Profile
                    </button>
                    <button type="button" class="btn btn-outline cancel-profile-btn" id="cancelProfileBtn" style="display: none;">
                        <i data-lucide="x" width="18" height="18"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary submit-profile-btn" id="submitProfileBtn" style="display: none;">
                        <i data-lucide="save" width="18" height="18"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Settings -->
        <div class="settings-section" id="password-tab">
            <div class="section-header">
                <div class="section-header-title">
                    <div class="section-icon">
                        <i data-lucide="lock"></i>
                    </div>
                    <h2>Password Settings</h2>
                    <button type="button" class="btn btn-compact btn-secondary edit-password-btn" id="editPasswordBtn" title="Change Password" style="margin-left: 0.5rem;">
                        <i data-lucide="edit-2" width="14" height="14"></i>
                        Edit
                    </button>
                    <button type="button" class="btn btn-compact btn-outline cancel-password-btn" id="cancelPasswordBtn" style="display: none; margin-left: 0.5rem;" title="Cancel">
                        <i data-lucide="x" width="14" height="14"></i>
                    </button>
                    <button type="button" class="btn btn-compact btn-primary submit-password-btn" id="submitPasswordBtn" form="passwordForm" style="display: none; margin-left: 0.5rem;" title="Save">
                        <i data-lucide="check" width="14" height="14"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.settings.update-password') }}" method="POST" id="passwordForm">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label class="form-label">Current Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" name="current_password" id="current-password" placeholder="Enter current password" disabled>
                            <button type="button" class="password-toggle" onclick="togglePassword('current-password', this)">
                                <i data-lucide="eye" width="18" height="18"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" name="new_password" id="new-password" placeholder="Enter new password" disabled>
                            <button type="button" class="password-toggle" onclick="togglePassword('new-password', this)">
                                <i data-lucide="eye" width="18" height="18"></i>
                            </button>
                        </div>
                        @error('new_password')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" class="form-control" name="new_password_confirmation" id="confirm-password" placeholder="Confirm new password" disabled>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm-password', this)">
                                <i data-lucide="eye" width="18" height="18"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 1.5rem;">
                    Must be at least 8 characters with uppercase, lowercase, and number
                </p>

                <div class="button-group" style="display: none;">
                    <button type="button" class="btn btn-secondary edit-password-btn" id="editPasswordBtn">
                        <i data-lucide="edit-2" width="18" height="18"></i>
                        Change Password
                    </button>
                    <button type="button" class="btn btn-outline cancel-password-btn" id="cancelPasswordBtn" style="display: none;">
                        <i data-lucide="x" width="18" height="18"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary submit-password-btn" id="submitPasswordBtn" style="display: none;">
                        <i data-lucide="key" width="18" height="18"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Library Settings -->
        <div class="settings-section" id="library-tab">
            <div class="section-header">
                <div class="section-header-title">
                    <div class="section-icon">
                        <i data-lucide="library"></i>
                    </div>
                    <h2>Library Settings</h2>
                    <button type="button" class="btn btn-compact btn-secondary edit-library-btn" id="editLibraryBtn" title="Edit Settings" style="margin-left: 0.5rem;">
                        <i data-lucide="edit-2" width="14" height="14"></i>
                        Edit
                    </button>
                    <button type="button" class="btn btn-compact btn-outline cancel-library-btn" id="cancelLibraryBtn" style="display: none; margin-left: 0.5rem;" title="Cancel">
                        <i data-lucide="x" width="14" height="14"></i>
                    </button>
                    <button type="button" class="btn btn-compact btn-primary submit-library-btn" id="submitLibraryBtn" form="librarySettingsForm" style="display: none; margin-left: 0.5rem;" title="Save">
                        <i data-lucide="check" width="14" height="14"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('admin.settings.update-library') }}" method="POST" id="librarySettingsForm">
                @csrf
                @method('PUT')

                <div class="library-settings-grid">
                    <div class="form-group">
                        <label class="form-label">Fine Per Day (₹)</label>
                        <div class="relative">
                            <span class="absolute text-gray-500 transform -translate-y-1/2 left-3 top-1/2">₹</span>
                            <input type="number" class="pl-8 form-control" name="per_day_fine" value="{{ $fineSetting->per_day_fine ?? 0.50 }}" step="0.01" min="0" required disabled>
                        </div>
                        @error('per_day_fine')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Issue Duration (days)</label>
                        <input type="number" class="form-control" name="issue_duration_days" value="{{ $fineSetting->issue_duration_days ?? 14 }}" min="1" max="365" required disabled>
                        @error('issue_duration_days')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Grace Period (days)</label>
                        <input type="number" class="form-control" name="grace_period_days" value="{{ $fineSetting->grace_period_days ?? 2 }}" min="0" max="365" required disabled>
                        @error('grace_period_days')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Maximum Books Per Student</label>
                        <input type="number" class="form-control" name="max_books_per_student" value="{{ $fineSetting->max_books_per_student ?? 5 }}" min="1" max="100" required disabled>
                        @error('max_books_per_student')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lost Book Penalty (₹)</label>
                        <div class="relative">
                            <span class="absolute text-gray-500 transform -translate-y-1/2 left-3 top-1/2">₹</span>
                            <input type="number" class="pl-8 form-control" name="lost_book_penalty" value="{{ $fineSetting->lost_book_penalty ?? 500 }}" step="0.01" min="0" required disabled>
                        </div>
                        @error('lost_book_penalty')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Damaged Book Penalty (₹)</label>
                        <div class="relative">
                            <span class="absolute text-gray-500 transform -translate-y-1/2 left-3 top-1/2">₹</span>
                            <input type="number" class="pl-8 form-control" name="damaged_book_penalty" value="{{ $fineSetting->damaged_book_penalty ?? 100 }}" step="0.01" min="0" disabled>
                        </div>
                        @error('damaged_book_penalty')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Maximum Fine Amount (₹)</label>
                        <div class="relative">
                            <span class="absolute text-gray-500 transform -translate-y-1/2 left-3 top-1/2">₹</span>
                            <input type="number" class="pl-8 form-control" name="max_fine_amount" value="{{ $fineSetting->max_fine_amount ?? 5000 }}" step="0.01" min="0" disabled>
                        </div>
                        @error('max_fine_amount')
                            <span style="color: #dc2626; font-size: 12px;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="toggle-group">
                    <div class="toggle-item">
                        <div class="toggle-label">
                            <h3>Enable Book Requests</h3>
                            <p>Allow members to request books that are currently unavailable</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="toggle-item">
                        <div class="toggle-label">
                            <h3>Enable Email Notifications</h3>
                            <p>Send automatic emails for due dates, overdue books, and reservations</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="button-group" style="display: none;">
                    <button type="button" class="btn btn-secondary edit-library-btn" id="editLibraryBtn">
                        <i data-lucide="edit-2" width="18" height="18"></i>
                        Edit Settings
                    </button>
                    <button type="button" class="btn btn-outline cancel-library-btn" id="cancelLibraryBtn" style="display: none;">
                        <i data-lucide="x" width="18" height="18"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary submit-library-btn" id="submitLibraryBtn" style="display: none;">
                        <i data-lucide="save" width="18" height="18"></i>
                        Save Library Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Toast Notification System
        function showToast(message, type = 'info', duration = 5000) {
            const icons = {
                success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
                error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
                info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
            };

            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const toastId = Date.now();

            toast.id = `toast-${toastId}`;
            toast.style.cssText = `
                background-color: ${colors[type]};
                color: white;
                padding: 16px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                font-size: 14px;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 12px;
                animation: slideInToast 0.3s ease-out;
                pointer-events: auto;
                max-width: 400px;
                min-width: 300px;
                word-wrap: break-word;
            `;

            toast.innerHTML = `
                ${icons[type]}
                <span style="flex: 1;">${message}</span>
                <button onclick="document.getElementById('toast-${toastId}').remove()" 
                        style="background: none; border: none; color: white; cursor: pointer; font-size: 18px; padding: 0; line-height: 1;">
                    ×
                </button>
            `;

            container.appendChild(toast);

            if (duration > 0) {
                setTimeout(() => {
                    toast.style.animation = 'slideOutToast 0.3s ease-out forwards';
                    setTimeout(() => {
                        if (document.getElementById(`toast-${toastId}`)) {
                            document.getElementById(`toast-${toastId}`).remove();
                        }
                    }, 300);
                }, duration);
            }
        }

        // Initialize toast container
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(container);

            // Add animations
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInToast {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                @keyframes slideOutToast {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Initialize Lucide icons
        lucide.createIcons();

        // Tab switching functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and sections
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.settings-section').forEach(section => section.classList.remove('active'));
                
                // Add active class to clicked button and corresponding section
                this.classList.add('active');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });

        // Enable/Disable form fields functionality
        function enableFormFields(formId, inputSelector = 'input, textarea, select') {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll(inputSelector);
            inputs.forEach(input => {
                input.disabled = false;
            });
        }

        function disableFormFields(formId, inputSelector = 'input, textarea, select') {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll(inputSelector);
            inputs.forEach(input => {
                input.disabled = true;
            });
        }

        // Profile Settings Edit/Cancel/Submit
        document.getElementById('editProfileBtn').addEventListener('click', function() {
            enableFormFields('profileForm');
            document.getElementById('profilePhotoLabel').style.pointerEvents = 'auto';
            
            // Hide inline edit button
            this.style.display = 'none';
            
            // Find and show bottom button group
            const profileForm = document.getElementById('profileForm');
            const buttonGroup = profileForm.querySelector('.button-group');
            if (buttonGroup) {
                buttonGroup.style.display = 'flex';
                // Hide the Edit button inside the button-group
                const editBtnInGroup = buttonGroup.querySelector('.edit-profile-btn');
                if (editBtnInGroup) editBtnInGroup.style.display = 'none';
                // Show Cancel and Save buttons
                const cancelBtn = buttonGroup.querySelector('.cancel-profile-btn');
                const saveBtn = buttonGroup.querySelector('.submit-profile-btn');
                if (cancelBtn) cancelBtn.style.display = 'flex';
                if (saveBtn) saveBtn.style.display = 'flex';
            }
        });

        // Profile Cancel button (bottom button group)
        const profileCancelBtnBottom = document.querySelector('#profileForm .button-group .cancel-profile-btn');
        if (profileCancelBtnBottom) {
            profileCancelBtnBottom.addEventListener('click', function(e) {
                e.preventDefault();
                disableFormFields('profileForm');
                document.getElementById('profilePhotoLabel').style.pointerEvents = 'none';
                
                // Show inline edit button
                document.getElementById('editProfileBtn').style.display = 'flex';
                
                // Hide bottom button group
                const profileForm = document.getElementById('profileForm');
                const buttonGroup = profileForm.querySelector('.button-group');
                if (buttonGroup) {
                    buttonGroup.style.display = 'none';
                }
                
                document.getElementById('profileForm').reset();
            });
        }

        // Password Settings Edit/Cancel/Submit
        document.getElementById('editPasswordBtn').addEventListener('click', function() {
            enableFormFields('passwordForm');
            
            // Hide inline edit button
            this.style.display = 'none';
            
            // Find and show bottom button group
            const passwordForm = document.getElementById('passwordForm');
            const buttonGroup = passwordForm.querySelector('.button-group');
            if (buttonGroup) {
                buttonGroup.style.display = 'flex';
                // Hide the Edit button inside the button-group
                const editBtnInGroup = buttonGroup.querySelector('.edit-password-btn');
                if (editBtnInGroup) editBtnInGroup.style.display = 'none';
                // Show Cancel and Save buttons
                const cancelBtn = buttonGroup.querySelector('.cancel-password-btn');
                const saveBtn = buttonGroup.querySelector('.submit-password-btn');
                if (cancelBtn) cancelBtn.style.display = 'flex';
                if (saveBtn) saveBtn.style.display = 'flex';
            }
        });

        // Password Cancel button (bottom button group)
        const passwordCancelBtnBottom = document.querySelector('#passwordForm .button-group .cancel-password-btn');
        if (passwordCancelBtnBottom) {
            passwordCancelBtnBottom.addEventListener('click', function(e) {
                e.preventDefault();
                disableFormFields('passwordForm');
                
                // Show inline edit button
                document.getElementById('editPasswordBtn').style.display = 'flex';
                
                // Hide bottom button group
                const passwordForm = document.getElementById('passwordForm');
                const buttonGroup = passwordForm.querySelector('.button-group');
                if (buttonGroup) {
                    buttonGroup.style.display = 'none';
                }
                
                document.getElementById('passwordForm').reset();
            });
        }

        // Library Settings Edit/Cancel/Submit
        document.getElementById('editLibraryBtn').addEventListener('click', function() {
            enableFormFields('librarySettingsForm');
            
            // Hide inline edit button
            this.style.display = 'none';
            
            // Find and show bottom button group
            const libraryForm = document.getElementById('librarySettingsForm');
            const buttonGroup = libraryForm.querySelector('.button-group');
            if (buttonGroup) {
                buttonGroup.style.display = 'flex';
                // Hide the Edit button inside the button-group
                const editBtnInGroup = buttonGroup.querySelector('.edit-library-btn');
                if (editBtnInGroup) editBtnInGroup.style.display = 'none';
                // Show Cancel and Save buttons
                const cancelBtn = buttonGroup.querySelector('.cancel-library-btn');
                const saveBtn = buttonGroup.querySelector('.submit-library-btn');
                if (cancelBtn) cancelBtn.style.display = 'flex';
                if (saveBtn) saveBtn.style.display = 'flex';
            }
        });

        // Library Cancel button (bottom button group)
        const libraryCancelBtnBottom = document.querySelector('#librarySettingsForm .button-group .cancel-library-btn');
        if (libraryCancelBtnBottom) {
            libraryCancelBtnBottom.addEventListener('click', function(e) {
                e.preventDefault();
                disableFormFields('librarySettingsForm');
                
                // Show inline edit button
                document.getElementById('editLibraryBtn').style.display = 'flex';
                
                // Hide bottom button group
                const libraryForm = document.getElementById('librarySettingsForm');
                const buttonGroup = libraryForm.querySelector('.button-group');
                if (buttonGroup) {
                    buttonGroup.style.display = 'none';
                }
            });
        }

        // Password visibility toggle
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }

            lucide.createIcons();
        }

        // File upload preview
        document.getElementById('profile-photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create image preview
                    const container = document.querySelector('.profile-photo-placeholder');
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'profile-photo';
                    img.alt = 'Profile photo';

                    // Replace icon with image
                    container.innerHTML = '';
                    container.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });

        // Form validation and submission
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle switch changes
            document.querySelectorAll('.toggle-switch input').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    console.log('Toggle changed:', this.checked);
                });
            });

            // Profile photo upload preview
            const profilePhotoInput = document.getElementById('profile-photo');
            const profilePhotoPreview = document.getElementById('profilePhotoPreview');

            if (profilePhotoInput) {
                profilePhotoInput.addEventListener('change', function(e) {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            profilePhotoPreview.innerHTML = `<img src="${event.target.result}" alt="Profile Photo Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">`;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Handle profile form submission
            const profileForm = document.getElementById('profileForm');
            if (profileForm) {
                profileForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    try {
                        const formData = new FormData(this);
                        
                        const response = await fetch('{{ route("admin.settings.update") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            }
                        });

                        console.log('Profile response status:', response.status);
                        
                        // Get response text first to debug
                        const responseText = await response.text();
                        console.log('Profile response text:', responseText);
                        
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('JSON parse error:', parseError);
                            showToast('Error: Invalid response from server', 'error');
                            return;
                        }

                        if (response.ok && data.success) {
                            showToast('Profile updated successfully!', 'success');
                            
                            // Disable form and hide bottom buttons
                            disableFormFields('profileForm');
                            document.getElementById('profilePhotoLabel').style.pointerEvents = 'none';
                            
                            // Show inline edit button
                            document.getElementById('editProfileBtn').style.display = 'flex';
                            
                            // Hide bottom button group
                            const buttonGroup = document.querySelector('#profileForm .button-group');
                            if (buttonGroup) {
                                buttonGroup.style.display = 'none';
                            }
                        } else {
                            showToast(data.message || 'Failed to update profile', 'error');
                        }
                    } catch (error) {
                        showToast('Error updating profile: ' + error.message, 'error');
                        console.error('Error:', error);
                    }
                });
            }

            // Handle password form submission
            const passwordForm = document.getElementById('passwordForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    try {
                        const formData = new FormData(this);
                        const data = Object.fromEntries(formData);

                        console.log('Password form data:', data);

                        const response = await fetch('{{ route("admin.settings.update-password") }}', {
                            method: 'PUT',
                            body: JSON.stringify(data),
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            }
                        });

                        console.log('Password response status:', response.status);

                        const responseText = await response.text();
                        console.log('Password response text:', responseText);
                        
                        let responseData;
                        try {
                            responseData = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('JSON parse error:', parseError);
                            showToast('Error: Invalid response from server', 'error');
                            return;
                        }

                        console.log('Password update response:', responseData);

                        if (response.ok && responseData.success) {
                            showToast('Password updated successfully!', 'success');
                            passwordForm.reset();
                            
                            // Disable form and hide bottom buttons
                            disableFormFields('passwordForm');
                            
                            // Show inline edit button
                            document.getElementById('editPasswordBtn').style.display = 'flex';
                            
                            // Hide bottom button group
                            const buttonGroup = document.querySelector('#passwordForm .button-group');
                            if (buttonGroup) {
                                buttonGroup.style.display = 'none';
                            }
                        } else {
                            // Show validation errors if they exist
                            if (responseData.errors) {
                                const errorMessages = Object.values(responseData.errors).flat().join(', ');
                                showToast(errorMessages, 'error');
                            } else {
                                showToast(responseData.message || 'Failed to update password', 'error');
                            }
                        }
                    } catch (error) {
                        showToast('Error updating password: ' + error.message, 'error');
                        console.error('Error:', error);
                    }
                });
            }

            // Handle library settings form submission
            const librarySettingsForm = document.getElementById('librarySettingsForm');
            if (librarySettingsForm) {
                librarySettingsForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    try {
                        const formData = new FormData(this);
                        const data = Object.fromEntries(formData);

                        console.log('Library settings form data:', data);

                        const response = await fetch('{{ route("admin.settings.update-library") }}', {
                            method: 'PUT',
                            body: JSON.stringify(data),
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            }
                        });

                        console.log('Library settings response status:', response.status);

                        const responseText = await response.text();
                        console.log('Library settings response text:', responseText);
                        
                        let responseData;
                        try {
                            responseData = JSON.parse(responseText);
                        } catch (parseError) {
                            console.error('JSON parse error:', parseError);
                            showToast('Error: Invalid response from server', 'error');
                            return;
                        }

                        console.log('Library settings update response:', responseData);

                        if (response.ok && responseData.success) {
                            showToast('Library settings updated successfully!', 'success');
                            
                            // Disable form and hide bottom buttons
                            disableFormFields('librarySettingsForm');
                            
                            // Show inline edit button
                            document.getElementById('editLibraryBtn').style.display = 'flex';
                            
                            // Hide bottom button group
                            const buttonGroup = document.querySelector('#librarySettingsForm .button-group');
                            if (buttonGroup) {
                                buttonGroup.style.display = 'none';
                            }
                        } else {
                            // Show validation errors if they exist
                            if (responseData.errors) {
                                const errorMessages = Object.values(responseData.errors).flat().join(', ');
                                showToast(errorMessages, 'error');
                            } else {
                                showToast(responseData.message || 'Failed to update library settings', 'error');
                            }
                        }
                    } catch (error) {
                        showToast('Error updating library settings: ' + error.message, 'error');
                        console.error('Error:', error);
                    }
                });
            }

            // Theme compatibility - ensure icons update on theme change
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        // Recreate icons when theme changes
                        setTimeout(() => lucide.createIcons(), 100);
                    }
                });
            });

            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });

            // Handle remove photo button
            const removePhotoBtn = document.getElementById('removePhotoBtn');
            if (removePhotoBtn) {
                removePhotoBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (confirm('Are you sure you want to remove your profile photo?')) {
                        fetch('{{ route("admin.settings.remove-photo") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Profile photo removed successfully!', 'success');
                                // Update preview to show default icon
                                document.getElementById('profilePhotoPreview').innerHTML = '<i data-lucide="user" width="48" height="48"></i>';
                                // Remove the button
                                removePhotoBtn.remove();
                                // Recreate icons
                                setTimeout(() => lucide.createIcons(), 100);
                            } else {
                                showToast(data.message || 'Failed to remove photo', 'error');
                            }
                        })
                        .catch(error => {
                            showToast('Error: ' + error.message, 'error');
                            console.error('Error:', error);
                        });
                    }
                });
            }
        });
    </script>
@endpush
