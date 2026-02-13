@extends('Staff.layouts.app')

@section('title', 'Settings')

@push('styles')
    <style>
        /* Settings Page Specific Styles */
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
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
            grid-template-columns: 300px 1fr;
            gap: 20px;
            align-items: start;
        }

        /* Left Profile Card */
        .profile-card {
            border-radius: 12px;
            transition: background-color 0.3s ease, color 0.3s ease;
            height: fit-content;
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
            margin-bottom: 1.5rem;
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

        .field-error {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.375rem;
            min-height: 1rem;
        }

        /* Top Edit Button */
        .top-edit-button {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1.25rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
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
                padding: 0.75rem;
            }

            .settings-grid {
                gap: 1rem;
            }

            .settings-card-content,
            .profile-card-content {
                padding: 1.25rem;
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
        }
    </style>
@endpush

@section('content')
    <div class="settings-container">
        <!-- Settings Header -->
        <div class="settings-header">
            <h1>Settings</h1>
            <p>Manage your account settings and preferences</p>
        </div>

        <!-- Two Column Layout -->
        <div class="settings-grid">
            <!-- Left Profile Card -->
            <div class="profile-card">
                <div class="profile-card-content">
                    <div class="profile-avatar" id="profileAvatar">
                        @if(isset($user) && $user->profile_photo)
                            <img src="{{ str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo) }}" alt="Profile Photo" id="avatarImage">
                        @else
                            @php
                                $initials = '';
                                if(isset($user) && $user->name) {
                                    $parts = explode(' ', $user->name);
                                    foreach($parts as $p) { $initials .= strtoupper(substr($p,0,1)); }
                                } else {
                                    $initials = 'JL';
                                }
                            @endphp
                            <div class="avatar-placeholder">{{ $initials }}</div>
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
                                <div class="stat-value" id="username">{{ $user->username ?? ($user->email ? explode('@',$user->email)[0] : 'staff1') }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Member Since</div>
                            <div class="stat-value" id="memberSince">{{ optional($user->created_at)->format('n/j/Y') ?? '1/15/2024' }}</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-label">Last Login</div>
                            <div class="stat-value" id="lastLogin">{{ optional($user->last_login)->format('n/j/Y') ?? now()->format('n/j/Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Settings Card -->
            <div class="settings-card">
                <div class="settings-card-content">
                    <!-- Tabs Navigation -->
                    <div class="settings-tabs">
                        <button class="tab-btn active" data-tab="profile">
                            <i class="fas fa-user"></i> Profile
                        </button>
                        <button class="tab-btn" data-tab="photo">
                            <i class="fas fa-camera"></i> Photo
                        </button>
                        <button class="tab-btn" data-tab="security">
                            <i class="fas fa-lock"></i> Security
                        </button>
                    </div>

                    <!-- Profile Tab Content -->
                    <div class="tab-content active" id="profileTab">
                        <div class="top-edit-button">
                            <button type="button" class="btn btn-primary" id="editProfileBtn">
                                <i class="fas fa-edit"></i> Edit Profile
                            </button>
                        </div>

                        <div class="section-header">
                            <h2>Personal Information</h2>
                            <p>Update your personal details</p>
                        </div>

                        <form id="profileForm" class="settings-form" method="POST" action="{{ route('staff.settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="fullName" class="form-label required">Full Name</label>
                                <input type="text" id="fullName" name="name" class="form-input" value="{{ old('name', $user->name ?? '') }}" disabled>
                                <div class="form-hint">Your full name as it appears in the system</div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" id="email" name="email" class="form-input" value="{{ old('email', $user->email ?? '') }}" disabled>
                                <div class="form-hint">This is your login email</div>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-input" value="{{ old('phone', $user->phone ?? '') }}" disabled>
                                <div class="form-hint">Your contact phone number</div>
                            </div>

                            <div class="form-group">
                                <label for="department" class="form-label">Department</label>
                                <input type="text" id="department" name="department" class="form-input" value="{{ old('department', $user->department ?? 'Library Services') }}" disabled>
                            </div>

                            <div class="form-group">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" class="form-input" rows="3" disabled>{{ old('address', $user->address ?? '') }}</textarea>
                            </div>

                            <div class="action-buttons">
                                <button type="submit" class="btn btn-secondary" id="saveProfileBtn" style="display: none;">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <button type="button" class="btn btn-outline" id="cancelProfileBtn" style="display: none;">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Photo Tab Content -->
                    <div class="tab-content" id="photoTab" style="display: none;">
                        <div class="photo-upload-container">
                            <div class="section-header">
                                <h2>Profile Photo</h2>
                                <p>Upload and manage your profile photo</p>
                            </div>

                            <form id="photoForm" action="{{ route('staff.settings.photo') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="photo-preview" id="photoPreview">
                                    @if(isset($user) && $user->profile_photo)
                                        <img src="{{ str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset($user->profile_photo) }}" alt="Preview" id="photoPreviewImg">
                                    @else
                                        <div class="photo-upload-icon">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <img src="" alt="Preview" id="photoPreviewImg" style="display:none;">
                                    @endif
                                </div>

                                <div class="file-input-wrapper">
                                    <input type="file" id="photoUpload" name="profile_photo" class="file-input" accept="image/*">
                                    <label for="photoUpload" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i> Choose File
                                    </label>
                                    <span id="fileName">No file chosen</span>
                                </div>

                            <div class="upload-requirements">
                                <h4>Recommended:</h4>
                                <p>Square image, at least 200x200px, max 2MB</p>
                            </div>

                                <div class="action-buttons">
                                    <button type="submit" class="btn btn-primary" id="uploadPhotoBtn" disabled>
                                        <i class="fas fa-upload"></i> Upload Photo
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="removePhotoBtn" {{ isset($user) && $user->profile_photo ? '' : 'disabled' }}>
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Security Tab Content -->
                    <div class="tab-content" id="securityTab" style="display: none;">
                        <div class="security-section">
                            <div class="section-header">
                                <h2>Change Password</h2>
                                <p>Update your account password</p>
                            </div>

                            <form id="passwordForm" class="settings-form" method="POST" action="{{ route('staff.settings.password') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <div class="input-with-icon">
                                        <input type="password" id="currentPassword" name="current_password" class="form-input"
                                            placeholder="Enter current password">
                                        <button type="button" class="pwd-toggle" data-target="currentPassword" aria-label="Toggle password visibility">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                    <div class="field-error" id="error_current_password"></div>
                                </div>

                                <div class="form-group">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <div class="input-with-icon">
                                        <input type="password" id="newPassword" name="password" class="form-input"
                                            placeholder="Enter new password (min. 6 characters)">
                                        <button type="button" class="pwd-toggle" data-target="newPassword" aria-label="Toggle password visibility">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                    <div class="field-error" id="error_password"></div>
                                </div>

                                <div class="form-group">
                                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                    <div class="input-with-icon">
                                        <input type="password" id="confirmPassword" name="password_confirmation" class="form-input"
                                            placeholder="Confirm new password">
                                        <button type="button" class="pwd-toggle" data-target="confirmPassword" aria-label="Toggle password visibility">
                                            <i data-lucide="eye"></i>
                                        </button>
                                    </div>
                                    <div class="field-error" id="error_password_confirmation"></div>
                                </div>

                                <div class="password-requirements">
                                    <h4>Password Requirements:</h4>
                                    <ul class="requirement-list">
                                        <li id="reqLength" class="invalid">
                                            <i data-lucide="circle"></i> Minimum 6 characters
                                        </li>
                                        <li id="reqMatch" class="invalid">
                                            <i data-lucide="circle"></i> Must match confirmation
                                        </li>
                                        <li id="reqDifferent" class="invalid">
                                            <i data-lucide="circle"></i> Different from current password
                                        </li>
                                    </ul>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" class="btn btn-primary" id="updatePasswordBtn" disabled>
                                        <i class="fas fa-key"></i> Update Password
                                    </button>
                                    <button type="button" class="btn btn-outline" id="cancelPasswordBtn">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching functionality
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');

                    // Update active tab button
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    // Show corresponding tab content
                    tabContents.forEach(content => {
                        content.style.display = 'none';
                        content.classList.remove('active');
                    });

                    const activeTab = document.getElementById(`${tabId}Tab`);
                    if (activeTab) {
                        activeTab.style.display = 'block';
                        activeTab.classList.add('active');
                    }
                });
            });

            // Profile editing functionality
            const editProfileBtn = document.getElementById('editProfileBtn');
            const saveProfileBtn = document.getElementById('saveProfileBtn');
            const cancelProfileBtn = document.getElementById('cancelProfileBtn');
            const profileInputs = document.querySelectorAll('#profileForm input, #profileForm textarea');

            let originalValues = {};

            editProfileBtn.addEventListener('click', function() {
                // Store original values
                profileInputs.forEach(input => {
                    originalValues[input.id] = input.value;
                    input.disabled = false;
                });

                // Switch button states
                editProfileBtn.style.display = 'none';
                saveProfileBtn.style.display = 'inline-flex';
                cancelProfileBtn.style.display = 'inline-flex';
            });

            cancelProfileBtn.addEventListener('click', function() {
                // Restore original values
                profileInputs.forEach(input => {
                    if (originalValues[input.id]) {
                        input.value = originalValues[input.id];
                    }
                    input.disabled = true;
                });

                // Switch button states
                editProfileBtn.style.display = 'inline-flex';
                saveProfileBtn.style.display = 'none';
                cancelProfileBtn.style.display = 'none';
            });

            // Submit profile form to server with basic validation
            const profileForm = document.getElementById('profileForm');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    const fullName = document.getElementById('fullName').value.trim();
                    const email = document.getElementById('email').value.trim();
                    if (!fullName || !email) {
                        e.preventDefault();
                        alert('Please fill in all required fields (marked with *)');
                        return;
                    }
                    // let the form submit normally to server (will redirect back)
                });
            }

            // Photo upload functionality
            const photoUpload = document.getElementById('photoUpload');
            const photoPreviewImg = document.getElementById('photoPreviewImg');
            const photoPreview = document.getElementById('photoPreview');
            const fileName = document.getElementById('fileName');
            const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
            const removePhotoBtn = document.getElementById('removePhotoBtn');
            const avatarImage = document.getElementById('avatarImage');
            const profileAvatar = document.getElementById('profileAvatar');

            photoUpload.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileName.textContent = file.name;

                    // Validate file size (2MB max)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        this.value = '';
                        fileName.textContent = 'No file chosen';
                        return;
                    }

                    // Validate file type
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file');
                        this.value = '';
                        fileName.textContent = 'No file chosen';
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = function(e) {
                        photoPreviewImg.src = e.target.result;
                        photoPreviewImg.style.display = 'block';
                        const _icon = photoPreview.querySelector('.photo-upload-icon');
                        if (_icon) _icon.style.display = 'none';
                        uploadPhotoBtn.disabled = false;
                        removePhotoBtn.disabled = false;
                    };

                    reader.readAsDataURL(file);
                }
            });

            // Photo upload: preview only; upload handled by photoForm submit
            const photoForm = document.getElementById('photoForm');
            uploadPhotoBtn.addEventListener('click', function() {
                // allow submit to proceed (button is submit)
            });

            removePhotoBtn.addEventListener('click', function() {
                if (!confirm('Are you sure you want to remove your profile photo?')) return;
                fetch('{{ route("staff.settings.remove-photo") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        showNotification('Profile photo removed', 'success');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showNotification(data.message || 'Failed to remove photo', 'error');
                    }
                }).catch(err => {
                    console.error(err);
                    showNotification('Failed to remove photo', 'error');
                });
            });

            // Password change functionality
            const currentPassword = document.getElementById('currentPassword');
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            const updatePasswordBtn = document.getElementById('updatePasswordBtn');
            const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');

            const reqLength = document.getElementById('reqLength');
            const reqMatch = document.getElementById('reqMatch');
            const reqDifferent = document.getElementById('reqDifferent');

            function validatePassword() {
                const currentPass = currentPassword.value;
                const newPass = newPassword.value;
                const confirmPass = confirmPassword.value;

                let isValid = true;

                // Check length
                if (newPass.length >= 6) {
                    reqLength.classList.remove('invalid');
                    reqLength.classList.add('valid');
                    reqLength.innerHTML = '<i data-lucide="check-circle"></i> Minimum 6 characters';
                } else {
                    reqLength.classList.remove('valid');
                    reqLength.classList.add('invalid');
                    reqLength.innerHTML = '<i data-lucide="circle"></i> Minimum 6 characters';
                    isValid = false;
                }

                // Check match
                if (newPass === confirmPass && newPass.length > 0) {
                    reqMatch.classList.remove('invalid');
                    reqMatch.classList.add('valid');
                    reqMatch.innerHTML = '<i data-lucide="check-circle"></i> Must match confirmation';
                } else {
                    reqMatch.classList.remove('valid');
                    reqMatch.classList.add('invalid');
                    reqMatch.innerHTML = '<i data-lucide="circle"></i> Must match confirmation';
                    isValid = false;
                }

                // Check if different
                if (newPass !== currentPass && newPass.length > 0) {
                    reqDifferent.classList.remove('invalid');
                    reqDifferent.classList.add('valid');
                    reqDifferent.innerHTML = '<i data-lucide="check-circle"></i> Different from current password';
                } else {
                    reqDifferent.classList.remove('valid');
                    reqDifferent.classList.add('invalid');
                    reqDifferent.innerHTML = '<i data-lucide="circle"></i> Different from current password';
                    isValid = false;
                }

                if (window.lucide && typeof lucide.createIcons === 'function') lucide.createIcons();

                updatePasswordBtn.disabled = !isValid;

                return isValid;
            }

            currentPassword.addEventListener('input', validatePassword);
            newPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);

            // Password form submit: validate then submit via AJAX for better UX
            const pwForm = document.getElementById('passwordForm');
            if (pwForm) {
                pwForm.addEventListener('submit', function(e) {
                    if (!validatePassword()) {
                        e.preventDefault();
                        return;
                    }

                    e.preventDefault();

                    const submitBtn = document.getElementById('updatePasswordBtn');
                    if (submitBtn) submitBtn.disabled = true;

                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const url = pwForm.action;

                    const formData = new FormData(pwForm);

                    // Use POST with _method=PUT when sending FormData — some servers do not parse multipart PUT bodies
                    formData.append('_method', 'PUT');
                    fetch(url, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async res => {
                        // Attempt JSON parse if possible
                        let data = null;
                        const contentType = res.headers.get('content-type') || '';
                        if (contentType.indexOf('application/json') !== -1) {
                            data = await res.json().catch(() => null);
                        }

                        // clear previous inline errors
                        document.querySelectorAll('.field-error').forEach(el => el.textContent = '');

                        if (res.ok) {
                            // Success
                            showNotification((data && data.message) ? data.message : 'Password updated', 'success');
                            // clear fields
                            pwForm.reset();
                            if (submitBtn) submitBtn.disabled = true;
                            return;
                        }

                        // Handle validation errors (422) or other errors
                        if (res.status === 422 && data && data.errors) {
                            // set inline errors where available, otherwise show first in toast
                            const fieldMap = {
                                'current_password': 'error_current_password',
                                'password': 'error_password',
                                'password_confirmation': 'error_password_confirmation'
                            };
                            let shown = false;
                            Object.keys(data.errors).forEach(field => {
                                const elId = fieldMap[field] || `error_${field}`;
                                const el = document.getElementById(elId);
                                const msg = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                                if (el) {
                                    el.textContent = msg;
                                    shown = true;
                                }
                            });
                            if (!shown) {
                                const firstField = Object.keys(data.errors)[0];
                                const msg = data.errors[firstField][0];
                                showNotification(msg, 'error');
                            }
                        } else if (data && data.message) {
                            showNotification(data.message, 'error');
                        } else {
                            const text = await res.text().catch(() => 'Unexpected server response');
                            showNotification(`Error: ${res.status} - ${text}`, 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Password update failed', err);
                        showNotification('Network error while updating password', 'error');
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
                });
            }

            // Password show/hide toggles (uses Lucide icons)
            document.querySelectorAll('.pwd-toggle').forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetId = btn.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;

                    if (input.type === 'password') {
                        input.type = 'text';
                        // Replace inner HTML with a lucide placeholder so createIcons() will render the new icon
                        btn.innerHTML = '<i data-lucide="eye-off"></i>';
                        if (window.lucide && typeof lucide.createIcons === 'function') lucide.createIcons();
                    } else {
                        input.type = 'password';
                        btn.innerHTML = '<i data-lucide="eye"></i>';
                        if (window.lucide && typeof lucide.createIcons === 'function') lucide.createIcons();
                    }
                });
            });

            cancelPasswordBtn.addEventListener('click', function() {
                // Clear the form
                currentPassword.value = '';
                newPassword.value = '';
                confirmPassword.value = '';

                // Reset validation
                reqLength.classList.remove('valid');
                reqLength.classList.add('invalid');
                reqLength.innerHTML = '<i data-lucide="circle"></i> Minimum 6 characters';

                reqMatch.classList.remove('valid');
                reqMatch.classList.add('invalid');
                reqMatch.innerHTML = '<i data-lucide="circle"></i> Must match confirmation';

                reqDifferent.classList.remove('valid');
                reqDifferent.classList.add('invalid');
                reqDifferent.innerHTML = '<i data-lucide="circle"></i> Different from current password';

                updatePasswordBtn.disabled = true;
            });

            // Notification function
            function showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 0.875rem 1.25rem;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            font-size: 0.875rem;
            z-index: 1000;
            animation: slideIn 0.3s ease;
            max-width: 300px;
        `;

                if (type === 'success') {
                    notification.style.backgroundColor = '#10b981';
                } else if (type === 'error') {
                    notification.style.backgroundColor = '#ef4444';
                } else {
                    notification.style.backgroundColor = '#3b82f6';
                }

                notification.textContent = message;

                document.body.appendChild(notification);

                // Remove notification after 3 seconds
                setTimeout(() => {
                    notification.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 3000);
            }

            // Add CSS for notifications
            const style = document.createElement('style');
            style.textContent = `
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
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
            document.head.appendChild(style);
        });
    </script>
@endpush
