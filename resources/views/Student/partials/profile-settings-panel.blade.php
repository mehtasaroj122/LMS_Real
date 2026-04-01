@php
    $addressLength = mb_strlen((string) $addressValue);
@endphp

<div class="settings-card-inner">
    <div class="settings-tabs" role="tablist" aria-label="Profile settings sections">
        <button type="button" class="tab-btn active" data-tab="profile" role="tab" aria-selected="true">
            <i data-lucide="user"></i>
            <span>Profile</span>
        </button>
        <button type="button" class="tab-btn" data-tab="photo" role="tab" aria-selected="false">
            <i data-lucide="camera"></i>
            <span>Photo</span>
        </button>
        <button type="button" class="tab-btn" data-tab="security" role="tab" aria-selected="false">
            <i data-lucide="lock"></i>
            <span>Security</span>
        </button>
    </div>

    <div class="tab-content active" id="profileTabPanel" role="tabpanel">
        <div class="section-header">
            <div class="section-header-main">
                <h2>Personal Information</h2>
                <p>Update your personal details</p>
            </div>
            <div class="section-header-actions">
                <span class="section-status" id="profileSectionStatus">Read only</span>
                <button type="button" class="btn btn-secondary" id="editProfileBtn">
                    <i data-lucide="square-pen"></i>
                    <span>Edit</span>
                </button>
                <button type="button" class="btn btn-outline" id="cancelProfileBtn" hidden>
                    <i data-lucide="x"></i>
                    <span>Cancel</span>
                </button>
                <button type="submit" class="btn btn-primary" id="saveProfileBtn" form="profileForm" hidden>
                    <i data-lucide="save"></i>
                    <span>Save</span>
                </button>
            </div>
        </div>

        <form id="profileForm" class="settings-form" method="POST" action="{{ route('student.profile.update-personal-info') }}" novalidate data-check-email-url="{{ route('student.profile.check-email') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label required">Full Name</label>
                <input type="text" id="name" name="name" data-validate="name" class="form-input" value="{{ $user->name }}" aria-describedby="error_name" aria-invalid="false" disabled>
                <div class="form-hint">Your full name as it appears in the system</div>
                <div class="field-error" id="error_name" aria-live="polite"></div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label required">Email</label>
                <input type="email" id="email" name="email" data-validate="email" class="form-input" value="{{ $user->email }}" aria-describedby="error_email" aria-invalid="false" disabled>
                <div class="form-hint">This is your login email</div>
                <div class="field-error" id="error_email" aria-live="polite"></div>
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" id="phone" name="phone" data-validate="phone" class="form-input" value="{{ $user->phone ?? '' }}" aria-describedby="error_phone" aria-invalid="false" disabled>
                <div class="form-hint">Your contact phone number</div>
                <div class="field-error" id="error_phone" aria-live="polite"></div>
            </div>

            <div class="form-group">
                <label for="department" class="form-label">Department</label>
                <input type="text" id="department" class="form-input" value="{{ $departmentName }}" disabled>
                <div class="form-hint">Department is assigned by admin and cannot be edited here</div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Address</label>
                <textarea id="address" name="address" data-validate="address" class="form-input" rows="4" maxlength="500" aria-describedby="error_address" aria-invalid="false" disabled>{{ $addressValue }}</textarea>
                <div class="field-meta">
                    <div class="form-hint">Your mailing address</div>
                    <div class="char-counter" id="addressCounter" aria-live="polite">
                        <span id="addressCount">{{ $addressLength }}</span>/500
                    </div>
                </div>
                <div class="field-error" id="error_address" aria-live="polite"></div>
            </div>
        </form>
    </div>

    <div class="tab-content" id="photoTabPanel" role="tabpanel">
        <div class="photo-upload-container">
            <div class="section-header">
                <div class="section-header-main">
                    <h2>Profile Photo</h2>
                    <p>Upload and manage your profile photo</p>
                </div>
            </div>

            <form id="photoForm" method="POST" action="{{ route('student.profile.upload-photo') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="photo-preview" id="photoPreview">
                    @if ($profilePhotoUrl)
                        <img src="{{ $profilePhotoUrl }}" alt="Preview" id="photoPreviewImg">
                    @else
                        <div class="photo-upload-icon" id="photoFallbackIcon">
                            <i data-lucide="user-round"></i>
                        </div>
                        <img src="" alt="Preview" id="photoPreviewImg" style="display: none;">
                    @endif
                </div>

                <div class="file-input-wrapper">
                    <input type="file" id="photoUpload" name="profile_photo" class="file-input" accept="image/*" aria-describedby="error_profile_photo">
                    <label for="photoUpload" class="file-input-label">
                        <i data-lucide="upload-cloud"></i>
                        <span>Choose File</span>
                    </label>
                    <span id="fileName">No file chosen</span>
                </div>

                <div class="field-error" id="error_profile_photo" aria-live="polite"></div>

                <div class="upload-requirements">
                    <h4>Recommended:</h4>
                    <p>Square image, at least 200x200px, max 2MB</p>
                </div>

                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="uploadPhotoBtn" disabled>
                        <i data-lucide="upload"></i>
                        <span>Upload Photo</span>
                    </button>
                    <button type="button" class="btn btn-secondary" id="removePhotoBtn" {{ $profilePhotoUrl ? '' : 'disabled' }}>
                        <i data-lucide="trash-2"></i>
                        <span>Remove</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="tab-content" id="securityTabPanel" role="tabpanel">
        <div class="security-section">
            <div class="section-header">
                <div class="section-header-main">
                    <h2>Change Password</h2>
                    <p>Update your account password</p>
                </div>
                <div class="section-header-actions">
                    <span class="section-status" id="passwordSectionStatus">Read only</span>
                    <button type="button" class="btn btn-outline" id="cancelPasswordBtn" hidden>
                        <i data-lucide="x"></i>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn btn-primary" id="updatePasswordBtn" form="passwordForm" hidden>
                        <i data-lucide="save"></i>
                        <span>Save</span>
                    </button>
                </div>
            </div>

            <div class="password-intro" id="passwordIntro">
                <div class="password-intro-icon" aria-hidden="true">
                    <i data-lucide="lock"></i>
                </div>
                <p class="password-intro-copy">Click "Change Password" to update your account password</p>
                <button type="button" class="btn btn-primary password-intro-action" id="showPasswordFormBtn" aria-controls="passwordForm" aria-expanded="false">
                    Change Password
                </button>
            </div>

            <form id="passwordForm" class="settings-form" method="POST" action="{{ route('student.profile.update-password') }}" novalidate hidden>
                @csrf

                <div class="form-group">
                    <label for="currentPassword" class="form-label required">Current Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="currentPassword" name="current_password" class="form-input" placeholder="Enter current password" aria-describedby="error_current_password" aria-invalid="false" autocomplete="current-password" disabled>
                        <button type="button" class="pwd-toggle" data-target="currentPassword" aria-label="Toggle current password visibility" disabled>
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <div class="field-error" id="error_current_password" aria-live="polite"></div>
                </div>

                <div class="form-group">
                    <label for="newPassword" class="form-label required">New Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="newPassword" name="password" class="form-input" placeholder="Enter new password" aria-describedby="error_password" aria-invalid="false" autocomplete="new-password" disabled>
                        <button type="button" class="pwd-toggle" data-target="newPassword" aria-label="Toggle new password visibility" disabled>
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <div class="field-error" id="error_password" aria-live="polite"></div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword" class="form-label required">Confirm New Password</label>
                    <div class="input-with-icon">
                        <input type="password" id="confirmPassword" name="password_confirmation" class="form-input" placeholder="Confirm new password" aria-describedby="error_password_confirmation" aria-invalid="false" autocomplete="new-password" disabled>
                        <button type="button" class="pwd-toggle" data-target="confirmPassword" aria-label="Toggle confirmation password visibility" disabled>
                            <i data-lucide="eye"></i>
                        </button>
                    </div>
                    <div class="field-error" id="error_password_confirmation" aria-live="polite"></div>
                </div>

                <div class="password-requirements">
                    <h4>Password Requirements:</h4>
                    <ul class="requirement-list">
                        <li id="reqLength" class="invalid"><span class="requirement-icon"></span> At least 8 characters</li>
                        <li id="reqUppercase" class="invalid"><span class="requirement-icon"></span> At least one uppercase letter</li>
                        <li id="reqLowercase" class="invalid"><span class="requirement-icon"></span> At least one lowercase letter</li>
                        <li id="reqNumber" class="invalid"><span class="requirement-icon"></span> At least one number</li>
                        <li id="reqMatch" class="invalid"><span class="requirement-icon"></span> Password and confirmation must match</li>
                        <li id="reqDifferent" class="invalid"><span class="requirement-icon"></span> New password must be different from current</li>
                    </ul>
                </div>
            </form>
        </div>
    </div>

    <div class="settings-modal-overlay" id="settingsConfirmModal" hidden aria-hidden="true">
        <div class="settings-modal-card" role="dialog" aria-modal="true" aria-labelledby="settingsConfirmTitle" aria-describedby="settingsConfirmMessage">
            <div class="settings-modal-header">
                <div class="settings-modal-icon danger" id="settingsConfirmIcon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 6V4h8v2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14H6L5 6" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6" />
                    </svg>
                </div>
                <div class="settings-modal-copy">
                    <h3 class="settings-modal-title" id="settingsConfirmTitle">Remove profile photo?</h3>
                    <p class="settings-modal-message" id="settingsConfirmMessage">Your current profile photo will be removed from your account.</p>
                </div>
            </div>
            <div class="settings-modal-actions">
                <button type="button" class="btn btn-outline" id="settingsConfirmCancelBtn">Keep Photo</button>
                <button type="button" class="btn btn-danger" id="settingsConfirmActionBtn">Remove Photo</button>
            </div>
        </div>
    </div>
</div>
