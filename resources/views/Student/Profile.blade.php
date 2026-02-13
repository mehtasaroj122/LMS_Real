@extends('student.layouts.app')

@section('title', 'My Profile')

@push('styles')
    <style>
        /* Profile Page Specific Styles */
        .profile-card {
            border-radius: 1rem;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.light-theme .profile-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            color: #0f172a;
        }

        body.dark-theme .profile-card {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: #e5e7f0;
        }

        .profile-avatar {
            width: 128px;
            height: 128px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar-initial {
            color: white;
            font-size: 2.25rem;
            font-weight: bold;
        }

        .profile-avatar-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 36px;
            height: 36px;
            background-color: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid;
        }

        body.light-theme .profile-avatar-upload {
            border-color: #ffffff;
        }

        body.dark-theme .profile-avatar-upload {
            border-color: #1e293b;
        }

        .profile-avatar-upload:hover {
            background-color: #2563eb;
        }

        .profile-detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .stat-card {
            padding: 1rem;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .stat-card-blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }

        .stat-card-orange {
            background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
        }

        .stat-card-purple {
            background: linear-gradient(135deg, #e9d5ff 0%, #d8b4fe 100%);
        }

        .stat-card-red {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
        }

        .stat-card-green {
            background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
        }

        .stat-card-indigo {
            background: linear-gradient(135deg, #c7d2fe 0%, #a5b4fc 100%);
        }

        body.dark-theme .stat-card-blue {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(59, 130, 246, 0.2) 100%);
        }

        body.dark-theme .stat-card-orange {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2) 0%, rgba(251, 146, 60, 0.2) 100%);
        }

        body.dark-theme .stat-card-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2) 0%, rgba(192, 132, 252, 0.2) 100%);
        }

        body.dark-theme .stat-card-red {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(248, 113, 113, 0.2) 100%);
        }

        body.dark-theme .stat-card-green {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2) 0%, rgba(74, 222, 128, 0.2) 100%);
        }

        body.dark-theme .stat-card-indigo {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(129, 140, 248, 0.2) 100%);
        }

        .tab-button {
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }

        .tab-button.active {
            border-bottom-color: #3b82f6;
            color: #3b82f6;
        }

        body.dark-theme .tab-button.active {
            color: #60a5fa;
            border-bottom-color: #60a5fa;
        }

        .profile-input {
            width: 100%;
            padding: 0.5rem 1rem;
            border: 1px solid;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        body.light-theme .profile-input {
            background-color: #f9fafb;
            border-color: transparent;
            color: #0f172a;
        }

        body.dark-theme .profile-input {
            background-color: #1e293b;
            border-color: transparent;
            color: #e5e7f0;
        }

        body.light-theme .profile-input:enabled {
            background-color: #ffffff;
            border-color: #e5e7eb;
        }

        body.dark-theme .profile-input:enabled {
            background-color: #0f172a;
            border-color: #334155;
        }

        .profile-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        .primary-button {
            padding: 0.5rem 1rem;
            background-color: #3b82f6;
            color: white;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .primary-button:hover {
            background-color: #2563eb;
        }

        body.dark-theme .primary-button {
            background-color: #1e40af;
        }

        body.dark-theme .primary-button:hover {
            background-color: #1e3a8a;
        }

        .secondary-button {
            padding: 0.5rem 1rem;
            border: 1px solid;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        body.light-theme .secondary-button {
            border-color: #e5e7eb;
            color: #374151;
        }

        body.light-theme .secondary-button:hover {
            background-color: #f9fafb;
        }

        body.dark-theme .secondary-button {
            border-color: #4b5563;
            color: #d1d5db;
        }

        body.dark-theme .secondary-button:hover {
            background-color: #374151;
        }

        .password-requirements {
            border-radius: 0.5rem;
            padding: 1rem;
            border: 1px solid;
        }

        body.light-theme .password-requirements {
            background-color: #f9fafb;
            border-color: #e5e7eb;
        }

        body.dark-theme .password-requirements {
            background-color: #1e293b;
            border-color: #374151;
        }

        .password-requirement-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .upload-photo-container {
            text-align: center;
            padding: 2rem 0;
        }

        .upload-preview {
            width: 192px;
            height: 192px;
            border-radius: 50%;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-preview-initial {
            color: white;
            font-size: 3.5rem;
            font-weight: bold;
        }

        .upload-instructions {
            background-color: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: left;
        }

        body.dark-theme .upload-instructions {
            background-color: #1e293b;
            border-color: #334155;
        }

        .upload-instructions ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-top: 0.5rem;
        }

        .upload-instructions li {
            margin-bottom: 0.5rem;
            color: #6b7280;
        }

        body.dark-theme .upload-instructions li {
            color: #9ca3af;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .file-input-wrapper input[type="file"] {
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
            padding: 0.75rem 1.5rem;
            background-color: #3b82f6;
            color: white;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .file-input-label:hover {
            background-color: #2563eb;
        }

        .upload-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .compact-only {
            display: none;
        }

        @media (max-width: 1024px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column;
                width: 100%;
            }

            .button-group button {
                width: 100%;
            }

            .upload-actions {
                flex-direction: column;
            }

            .upload-actions button {
                width: 100%;
            }

            .upload-preview {
                width: 160px;
                height: 160px;
            }

            .upload-preview-initial {
                font-size: 2.5rem;
            }
        }

        /* Compact-only overrides scoped to `.profile-page` (preserve colors & classes) */
        .profile-page .profile-card {
            padding: 0.75rem !important;
        }

        .profile-page .stat-card {
            padding: 0.6rem !important;
        }

        .profile-page h1.text-3xl {
            font-size: 1.5rem !important;
            line-height: 1.1 !important;
        }

        .profile-page .text-2xl {
            font-size: 1.25rem !important;
        }

        .profile-page h3.text-lg {
            font-size: 1rem !important;
            margin-bottom: 0.25rem !important;
        }

        .profile-page .text-sm,
        .profile-page .text-secondary {
            font-size: 0.85rem !important;
        }

        .profile-page .profile-avatar {
            width: 96px !important;
            height: 96px !important;
        }

        .profile-page .profile-avatar-initial {
            font-size: 1.5rem !important;
        }

        .profile-page .grid.gap-6 {
            gap: 0.75rem !important;
        }

        .profile-page .p-6 {
            padding: 0.75rem !important;
        }

        .profile-page .p-4 {
            padding: 0.6rem !important;
        }

        .profile-page .mb-8 {
            margin-bottom: 1rem !important;
        }

        .profile-page .compact-only {
            display: inline-block;
        }

        .profile-page .full-only {
            display: none;
        }

        #removePhotoBtn {
            transition: all 0.2s ease;
        }

        #removePhotoBtn:hover {
            background-color: rgba(239, 68, 68, 0.2) !important;
            border-color: rgba(239, 68, 68, 0.3) !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.15);
        }

        #removePhotoBtn:active {
            transform: translateY(0);
        }

        #removePhotoBtn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .password-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrapper .profile-input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--color-secondary);
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 10;
            pointer-events: auto;
            flex-shrink: 0;
        }

        .password-toggle:hover {
            color: var(--color-primary);
            transform: scale(1.1);
        }

        .password-toggle:active {
            transform: scale(0.95);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        .password-toggle .hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container px-1 py-1 mx-auto profile-page">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary">My Profile</h1>
            <p class="mt-2 text-secondary">View and manage your personal information and account settings</p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 profile-container lg:grid-cols-3">
            <!-- Left Column - User Profile Card -->
            <div class="lg:col-span-1">
                <div class="sticky p-6 profile-card top-6">
                    <!-- Avatar -->
                    <div class="flex flex-col items-center mb-6">
                        <div class="profile-avatar" id="leftProfileAvatar">
                            <img src="{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset('storage/' . $user->profile_photo)) : '' }}" alt="Profile Photo" id="leftAvatarImage" style="display: {{ $user->profile_photo ? 'block' : 'none' }};">
                            <span class="profile-avatar-initial" id="leftAvatarInitial" style="display: {{ $user->profile_photo ? 'none' : 'block' }};">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                            <div class="profile-avatar-upload" onclick="switchToPhotoTab()" title="Upload Profile Photo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                            </div>
                        </div>
                        <h2 class="mb-1 text-xl font-bold text-primary">{{ $user->name }}</h2>
                        <p class="mb-3 text-secondary">{{ $user->email }}</p>
                        <span
                            class="px-4 py-1 text-sm font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                            Student
                        </span>
                    </div>

                    <!-- Divider -->
                    <div class="my-6 border-t border-border"></div>

                    <!-- User Details -->
                    <div class="space-y-4">
                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Username</p>
                                <p class="font-medium text-primary">{{ $student ? substr($user->email, 0, strpos($user->email, '@')) : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path
                                    d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-4 0v-9a2 2 0 0 1 2-2h2" />
                                <path d="M18 14h-8" />
                                <path d="M15 18h-5" />
                                <path d="M10 6h8v4h-8V6z" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Student ID</p>
                                <p class="font-medium text-primary">{{ $student ? $student->student_id : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z" />
                                <path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9" />
                                <path d="M12 3v6" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Department</p>
                                <p class="font-medium text-primary">{{ $student && $student->department ? $student->department->name : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Member Since</p>
                                <p class="font-medium text-primary">{{ $user->created_at ? $user->created_at->format('F d, Y') : 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="profile-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="flex-shrink-0 text-secondary">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                            <div>
                                <p class="text-sm text-secondary">Last Login</p>
                                <p class="font-medium text-primary">{{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-2">
                <!-- Account Statistics -->
                <div class="p-6 mb-6 profile-card">
                    <h2 class="mb-4 text-xl font-bold text-primary">Account Statistics</h2>
                    <div class="grid grid-cols-2 gap-4 stats-grid md:grid-cols-3">
                        <!-- Books Issued -->
                        <div class="stat-card stat-card-blue">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $booksIssuedCount }}</p>
                            <p class="mt-1 text-sm text-secondary">Books Issued</p>
                        </div>

                        <!-- Unpaid Fines -->
                        <div class="stat-card stat-card-orange">
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $unpaidFinesCount }}</p>
                            <p class="mt-1 text-sm text-secondary">Unpaid Fines</p>
                        </div>

                        <!-- Pending Requests -->
                        <div class="stat-card stat-card-purple">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $pendingRequestsCount }}</p>
                            <p class="mt-1 text-sm text-secondary">Pending Requests</p>
                        </div>

                        <!-- Total Fines -->
                        <div class="stat-card stat-card-red">
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">₹{{ $totalFinesAmount }}</p>
                            <p class="mt-1 text-sm text-secondary">Total Fines</p>
                        </div>

                        <!-- Fines Paid -->
                        <div class="stat-card stat-card-green">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">₹{{ $finesPaidAmount }}</p>
                            <p class="mt-1 text-sm text-secondary">Fines Paid</p>
                        </div>

                        <!-- Approved Requests -->
                        <div class="stat-card stat-card-indigo">
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $approvedRequestsCount }}</p>
                            <p class="mt-1 text-sm text-secondary">Approved Requests</p>
                        </div>
                    </div>
                </div>

                <!-- Profile & Settings -->
                <div class="p-6 profile-card" id="profileSettings">
                    <!-- Tabs -->
                    <div class="flex mb-6 border-b border-border">
                        <button id="personalTab" class="tab-button active">
                            Personal Information
                        </button>
                        <button id="photoTab" class="tab-button">
                            Upload Photo
                        </button>
                        <button id="securityTab" class="tab-button">
                            Security
                        </button>
                    </div>

                    <!-- Personal Information Tab -->
                    <div id="personalContent">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-primary">Personal Details</h3>
                            <button id="editProfileBtn" class="primary-button">
                                Edit Profile
                            </button>
                        </div>

                        <div class="space-y-4">
                            <!-- Full Name -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-primary">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="fullName" disabled class="profile-input"
                                    value="{{ $user->name }}">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-primary">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="email" disabled class="profile-input"
                                    value="{{ $user->email }}">
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-primary">
                                    Phone Number
                                </label>
                                <input type="tel" id="phone" disabled class="profile-input"
                                    value="{{ $user->phone ?? 'Not provided' }}">
                            </div>

                            <!-- Department -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-primary">
                                    Department
                                </label>
                                <input type="text" id="department" disabled class="profile-input"
                                    value="{{ $student && $student->department ? $student->department->name : 'N/A' }}">
                            </div>

                            <!-- Address -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-primary">
                                    Address
                                </label>
                                <textarea id="address" disabled class="resize-none profile-input" rows="3">{{ $user->address ?? 'Not provided' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Photo Tab (Hidden by default) -->
                    <div id="photoContent" class="hidden">
                        <div class="upload-photo-container">
                            <div class="upload-preview" id="uploadPreview">
                                <img src="{{ $user->profile_photo ? (str_starts_with($user->profile_photo, 'http') ? $user->profile_photo : asset('storage/' . $user->profile_photo)) : '' }}" alt="Profile Photo Preview" id="previewImage"
                                    style="display: {{ $user->profile_photo ? 'block' : 'none' }};">
                                <span class="upload-preview-initial" id="previewInitial" style="display: {{ $user->profile_photo ? 'none' : 'block' }};">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            </div>

                            <div class="file-input-wrapper">
                                <input type="file" id="photoUpload" accept="image/jpeg,image/png,image/gif"
                                    style="display: none;">
                                <label for="photoUpload" class="file-input-label">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="17 8 12 3 7 8" />
                                        <line x1="12" y1="3" x2="12" y2="15" />
                                    </svg>
                                    Choose Photo
                                </label>
                                <button type="button" id="removePhotoBtn" style="display: {{ $user->profile_photo ? 'inline-flex' : 'none' }}; margin-left: 0.5rem; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background-color: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 0.375rem; cursor: pointer; transition: all 0.2s ease; font-size: 0.875rem; font-weight: 500;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <path d="M10 11v6" />
                                        <path d="M14 11v6" />
                                        <path d="M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14" />
                                    </svg>
                                    <span>Remove Photo</span>
                                </button>
                            </div>

                            <p class="mt-2 text-sm text-secondary">Choose a JPG, PNG or GIF image (Max 2MB)</p>

                            <div class="upload-actions" id="uploadActions" style="display: none;">
                                <button id="cancelUpload" class="secondary-button">
                                    Cancel
                                </button>
                                <button id="savePhoto" class="primary-button">
                                    Save Photo
                                </button>
                            </div>

                            <div class="upload-instructions">
                                <h4 class="font-medium text-primary">Photo Requirements:</h4>
                                <ul>
                                    <li>File size must be less than 2MB</li>
                                    <li>Accepted formats: JPG, PNG, GIF</li>
                                    <li>Recommended size: 400x400 pixels or larger</li>
                                    <li>Image should be clear and well-lit</li>
                                    <li>Your face should be clearly visible</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab (Hidden by default) -->
                    <div id="securityContent" class="hidden">
                        <div id="securityInitialView" class="py-8 text-center">
                            <div
                                class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full dark:bg-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="text-secondary">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                            </div>
                            <p class="mb-6 text-secondary">Click "Change Password" to update your account password</p>
                            <button id="changePasswordBtn" class="primary-button">
                                Change Password
                            </button>
                        </div>

                        <!-- Password Change Form (Hidden by default) -->
                        <div id="passwordForm" class="hidden">
                            <h3 class="mb-6 text-lg font-semibold text-primary">Change Password</h3>

                            <!-- Password Form -->
                            <div class="mb-6 space-y-4">
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-primary">
                                        Current Password
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="currentPassword" class="profile-input"
                                            placeholder="Enter current password" autocomplete="current-password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('currentPassword')" title="Show/Hide password">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden eye-off-icon">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                <line x1="1" y1="1" x2="23" y2="23"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-primary">
                                        New Password
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="newPassword" class="profile-input"
                                            placeholder="Enter new password" autocomplete="new-password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('newPassword')" title="Show/Hide password">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden eye-off-icon">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                <line x1="1" y1="1" x2="23" y2="23"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block mb-1 text-sm font-medium text-primary">
                                        Confirm New Password
                                    </label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="confirmPassword" class="profile-input"
                                            placeholder="Confirm new password" autocomplete="new-password">
                                        <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')" title="Show/Hide password">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-icon">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden eye-off-icon">
                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                                <line x1="1" y1="1" x2="23" y2="23"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Password Requirements -->
                            <div class="mb-6 password-requirements">
                                <h4 class="mb-2 font-medium text-primary">Password Requirements</h4>
                                <ul class="space-y-1 text-sm text-secondary">
                                    <li class="password-requirement-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="text-green-500">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        Minimum 6 characters
                                    </li>
                                    <li class="password-requirement-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="text-green-500">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        Must match confirmation
                                    </li>
                                    <li class="password-requirement-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="text-green-500">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        Must be different from current password
                                    </li>
                                </ul>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 button-group">
                                <button id="cancelPasswordBtn" class="secondary-button">
                                    Cancel
                                </button>
                                <button id="updatePasswordBtn" class="primary-button">
                                    Update Password
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Helper function to show messages
        function showMessage(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `p-4 mb-4 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'}`;
            alertDiv.textContent = message;
            
            const container = document.getElementById('profileSettings');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 4000);
        }

        // Profile Settings JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Switching
            const personalTab = document.getElementById('personalTab');
            const photoTab = document.getElementById('photoTab');
            const securityTab = document.getElementById('securityTab');
            const personalContent = document.getElementById('personalContent');
            const photoContent = document.getElementById('photoContent');
            const securityContent = document.getElementById('securityContent');

            function switchToPersonalTab() {
                personalTab.classList.add('active');
                photoTab.classList.remove('active');
                securityTab.classList.remove('active');
                personalContent.classList.remove('hidden');
                photoContent.classList.add('hidden');
                securityContent.classList.add('hidden');
            }

            function switchToPhotoTab() {
                photoTab.classList.add('active');
                personalTab.classList.remove('active');
                securityTab.classList.remove('active');
                photoContent.classList.remove('hidden');
                personalContent.classList.add('hidden');
                securityContent.classList.add('hidden');
            }

            function switchToSecurityTab() {
                securityTab.classList.add('active');
                personalTab.classList.remove('active');
                photoTab.classList.remove('active');
                securityContent.classList.remove('hidden');
                personalContent.classList.add('hidden');
                photoContent.classList.add('hidden');
            }

            personalTab.addEventListener('click', switchToPersonalTab);
            photoTab.addEventListener('click', switchToPhotoTab);
            securityTab.addEventListener('click', switchToSecurityTab);

            // Edit Profile Toggle
            const editProfileBtn = document.getElementById('editProfileBtn');
            const inputs = document.querySelectorAll('#personalContent input, #personalContent textarea');
            let isEditing = false;

            function enableEditing() {
                if (!isEditing) {
                    // Enable editing (but keep department disabled)
                    inputs.forEach(input => {
                        if (input.id !== 'department') {
                            input.disabled = false;
                        }
                    });

                    // Change button to Save/Cancel
                    const buttonGroup = document.createElement('div');
                    buttonGroup.className = 'button-group flex gap-2';
                    buttonGroup.innerHTML = `
                    <button id="cancelEditBtn" class="secondary-button" type="button">
                        Cancel
                    </button>
                    <button id="saveEditBtn" class="primary-button" type="button">
                        Save Changes
                    </button>
                `;

                    editProfileBtn.parentNode.replaceChild(buttonGroup, editProfileBtn);

                    isEditing = true;

                    // Add event listeners for new buttons
                    document.getElementById('cancelEditBtn').addEventListener('click', cancelEdit);
                    document.getElementById('saveEditBtn').addEventListener('click', saveEdit);
                }
            }

            function cancelEdit() {
                // Disable inputs
                inputs.forEach(input => {
                    input.disabled = true;

                    // Reset values to original
                    if (input.id === 'fullName') input.value = '{{ $user->name }}';
                    if (input.id === 'email') input.value = '{{ $user->email }}';
                    if (input.id === 'phone') input.value = '{{ $user->phone ?? "Not provided" }}';
                    if (input.id === 'department') input.value = '{{ $student && $student->department ? $student->department->name : "N/A" }}';
                    if (input.id === 'address') input.value =
                        '{{ $user->address ?? "Not provided" }}';
                });

                // Reset button
                const newButton = document.createElement('button');
                newButton.id = 'editProfileBtn';
                newButton.className = 'primary-button';
                newButton.textContent = 'Edit Profile';
                newButton.type = 'button';

                const buttonGroup = document.querySelector('#personalContent .button-group');
                if (buttonGroup) {
                    buttonGroup.parentNode.replaceChild(newButton, buttonGroup);
                }

                // Reattach event listener
                document.getElementById('editProfileBtn').addEventListener('click', enableEditing);

                isEditing = false;
            }

            function saveEdit() {
                const fullName = document.getElementById('fullName').value;
                const email = document.getElementById('email').value;
                const phone = document.getElementById('phone').value;
                const address = document.getElementById('address').value;

                // Client-side validation
                if (!fullName || fullName.trim() === '') {
                    showMessage('Full name cannot be empty', 'error');
                    return;
                }

                if (!email || email.trim() === '') {
                    showMessage('Email cannot be empty', 'error');
                    return;
                }

                // Simple email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showMessage('Please enter a valid email address', 'error');
                    return;
                }

                // Show loading state
                const saveBtn = document.getElementById('saveEditBtn');
                saveBtn.disabled = true;
                saveBtn.textContent = 'Saving...';

                fetch('{{ route("student.profile.update-personal-info") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        fullName: fullName,
                        email: email,
                        phone: phone,
                        address: address,
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');

                        // Disable inputs
                        inputs.forEach(input => {
                            input.disabled = true;
                        });

                        // Reset button
                        const newButton = document.createElement('button');
                        newButton.id = 'editProfileBtn';
                        newButton.className = 'primary-button';
                        newButton.textContent = 'Edit Profile';
                        newButton.type = 'button';

                        const buttonGroup = document.querySelector('#personalContent .button-group');
                        if (buttonGroup) {
                            buttonGroup.parentNode.replaceChild(newButton, buttonGroup);
                        }

                        document.getElementById('editProfileBtn').addEventListener('click', enableEditing);
                        isEditing = false;
                    } else {
                        showMessage(data.message, 'error');
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Save Changes';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error updating profile. Please try again.', 'error');
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save Changes';
                });
            }

            editProfileBtn.addEventListener('click', enableEditing);

            // Photo Upload Functionality
            const photoUpload = document.getElementById('photoUpload');
            const previewImage = document.getElementById('previewImage');
            const previewInitial = document.getElementById('previewInitial');
            const uploadActions = document.getElementById('uploadActions');
            const cancelUpload = document.getElementById('cancelUpload');
            const savePhoto = document.getElementById('savePhoto');
            const removePhotoBtn = document.getElementById('removePhotoBtn');
            let selectedFile = null;

            photoUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Check file size (max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        showMessage('File size must be less than 2MB', 'error');
                        this.value = '';
                        return;
                    }

                    // Check file type
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        showMessage('Only JPG, PNG and GIF files are allowed', 'error');
                        this.value = '';
                        return;
                    }

                    selectedFile = file;

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewImage.src = event.target.result;
                        previewImage.style.display = 'block';
                        previewInitial.style.display = 'none';
                        uploadActions.style.display = 'flex';
                    };
                    reader.readAsDataURL(file);
                }
            });

            cancelUpload.addEventListener('click', function() {
                photoUpload.value = '';
                previewImage.style.display = 'none';
                previewInitial.style.display = 'block';
                uploadActions.style.display = 'none';
                selectedFile = null;
            });

            removePhotoBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to remove your profile photo?')) {
                    removePhotoBtn.disabled = true;
                    const originalText = removePhotoBtn.innerHTML;
                    removePhotoBtn.innerHTML = 'Removing...';

                    fetch('{{ route("student.profile.remove-photo") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showMessage(data.message, 'success');

                            // Update left profile avatar
                            const leftAvatarImage = document.getElementById('leftAvatarImage');
                            const leftAvatarInitial = document.getElementById('leftAvatarInitial');

                            leftAvatarImage.style.display = 'none';
                            leftAvatarInitial.style.display = 'block';

                            // Update preview image in upload tab
                            previewImage.style.display = 'none';
                            previewInitial.style.display = 'block';

                            // Hide remove photo button
                            removePhotoBtn.style.display = 'none';
                        } else {
                            showMessage(data.message, 'error');
                        }
                        removePhotoBtn.disabled = false;
                        removePhotoBtn.innerHTML = originalText;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('Error removing photo. Please try again.', 'error');
                        removePhotoBtn.disabled = false;
                        removePhotoBtn.innerHTML = originalText;
                    });
                }
            });

            savePhoto.addEventListener('click', function() {
                if (!selectedFile) {
                    showMessage('Please select a photo first', 'error');
                    return;
                }

                // Show loading state
                savePhoto.disabled = true;
                savePhoto.innerHTML = 'Uploading...';

                const formData = new FormData();
                formData.append('photo', selectedFile);

                fetch('{{ route("student.profile.upload-photo") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');

                        // Update left profile avatar
                        const leftAvatarImage = document.getElementById('leftAvatarImage');
                        const leftAvatarInitial = document.getElementById('leftAvatarInitial');

                        leftAvatarImage.src = data.photoUrl;
                        leftAvatarImage.style.display = 'block';
                        leftAvatarInitial.style.display = 'none';

                        // Update preview image in upload tab
                        previewImage.src = data.photoUrl;
                        previewImage.style.display = 'block';
                        previewInitial.style.display = 'none';

                        // Show remove photo button
                        removePhotoBtn.style.display = 'inline-block';

                        // Reset upload state
                        uploadActions.style.display = 'none';
                        photoUpload.value = '';
                        selectedFile = null;

                        savePhoto.disabled = false;
                        savePhoto.innerHTML = 'Save Photo';
                    } else {
                        showMessage(data.message, 'error');
                        savePhoto.disabled = false;
                        savePhoto.innerHTML = 'Save Photo';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error uploading photo. Please try again.', 'error');
                    savePhoto.disabled = false;
                    savePhoto.innerHTML = 'Save Photo';
                });
            });

            // Password Change Toggle
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
            const updatePasswordBtn = document.getElementById('updatePasswordBtn');
            const securityInitialView = document.getElementById('securityInitialView');
            const passwordForm = document.getElementById('passwordForm');

            function showPasswordForm() {
                if (securityInitialView && passwordForm) {
                    securityInitialView.classList.add('hidden');
                    passwordForm.classList.remove('hidden');
                }
            }

            function hidePasswordForm() {
                if (securityInitialView && passwordForm) {
                    passwordForm.classList.add('hidden');
                    securityInitialView.classList.remove('hidden');

                    // Clear password fields
                    if (document.getElementById('currentPassword')) {
                        document.getElementById('currentPassword').value = '';
                        document.getElementById('currentPassword').type = 'password';
                    }
                    if (document.getElementById('newPassword')) {
                        document.getElementById('newPassword').value = '';
                        document.getElementById('newPassword').type = 'password';
                    }
                    if (document.getElementById('confirmPassword')) {
                        document.getElementById('confirmPassword').value = '';
                        document.getElementById('confirmPassword').type = 'password';
                    }

                    // Reset all password toggle icons to show eye icon
                    const toggleButtons = document.querySelectorAll('.password-toggle');
                    toggleButtons.forEach(button => {
                        const eyeIcon = button.querySelector('.eye-icon');
                        const eyeOffIcon = button.querySelector('.eye-off-icon');
                        if (eyeIcon) eyeIcon.classList.remove('hidden');
                        if (eyeOffIcon) eyeOffIcon.classList.add('hidden');
                    });
                }
            }

            function togglePassword(fieldId) {
                const field = document.getElementById(fieldId);
                const wrapper = field.closest('.password-input-wrapper');
                const button = wrapper.querySelector('.password-toggle');
                const eyeIcon = button.querySelector('.eye-icon');
                const eyeOffIcon = button.querySelector('.eye-off-icon');

                if (field.type === 'password') {
                    field.type = 'text';
                    eyeIcon.classList.add('hidden');
                    eyeOffIcon.classList.remove('hidden');
                } else {
                    field.type = 'password';
                    eyeIcon.classList.remove('hidden');
                    eyeOffIcon.classList.add('hidden');
                }
            }

            function validatePasswordChange() {
                const currentPass = document.getElementById('currentPassword').value;
                const newPass = document.getElementById('newPassword').value;
                const confirmPass = document.getElementById('confirmPassword').value;

                if (!currentPass || !newPass || !confirmPass) {
                    showMessage('Please fill in all password fields', 'error');
                    return false;
                }

                if (newPass.length < 6) {
                    showMessage('New password must be at least 6 characters', 'error');
                    return false;
                }

                if (newPass !== confirmPass) {
                    showMessage('New passwords do not match', 'error');
                    return false;
                }

                if (currentPass === newPass) {
                    showMessage('New password must be different from current password', 'error');
                    return false;
                }

                return true;
            }

            function updatePassword() {
                if (!validatePasswordChange()) {
                    return;
                }

                const currentPass = document.getElementById('currentPassword').value;
                const newPass = document.getElementById('newPassword').value;
                const confirmPass = document.getElementById('confirmPassword').value;

                // Show loading state
                const updateBtn = document.getElementById('updatePasswordBtn');
                updateBtn.disabled = true;
                updateBtn.textContent = 'Updating...';

                fetch('{{ route("student.profile.update-password") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        currentPassword: currentPass,
                        newPassword: newPass,
                        newPassword_confirmation: confirmPass,
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.message, 'success');
                        hidePasswordForm();
                    } else {
                        showMessage(data.message, 'error');
                    }
                    updateBtn.disabled = false;
                    updateBtn.textContent = 'Update Password';
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Error updating password. Please try again.', 'error');
                    updateBtn.disabled = false;
                    updateBtn.textContent = 'Update Password';
                });
            }

            // Attach event listeners if elements exist
            if (changePasswordBtn) {
                changePasswordBtn.addEventListener('click', showPasswordForm);
            }

            if (cancelPasswordBtn) {
                cancelPasswordBtn.addEventListener('click', hidePasswordForm);
            }

            if (updatePasswordBtn) {
                updatePasswordBtn.addEventListener('click', updatePassword);
            }

            // Make functions available globally for event handlers
            window.enableEditing = enableEditing;
            window.cancelEdit = cancelEdit;
            window.saveEdit = saveEdit;
            window.showPasswordForm = showPasswordForm;
            window.hidePasswordForm = hidePasswordForm;
            window.updatePassword = updatePassword;
            window.switchToPersonalTab = switchToPersonalTab;
            window.switchToPhotoTab = switchToPhotoTab;
            window.switchToSecurityTab = switchToSecurityTab;
            window.togglePassword = togglePassword;
        });

        // Global function to switch to photo tab
        function switchToPhotoTab() {
            document.getElementById('photoTab').click();
        }
    </script>
@endpush
