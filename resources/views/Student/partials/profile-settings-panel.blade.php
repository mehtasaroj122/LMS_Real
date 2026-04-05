@php
    $addressLength = mb_strlen((string) $addressValue);
@endphp

<div class="settings-card-inner">
    <div class="settings-tabs" role="tablist" aria-label="Profile settings sections">
        <button type="button" class="tab-btn active" id="profileTabButton" data-tab="profile" role="tab" aria-selected="true" aria-controls="profileTabPanel">
            <i data-lucide="user"></i>
            <span>Profile</span>
        </button>
        <button type="button" class="tab-btn" id="photoTabButton" data-tab="photo" role="tab" aria-selected="false" aria-controls="photoTabPanel">
            <i data-lucide="camera"></i>
            <span>Photo</span>
        </button>
        <button type="button" class="tab-btn" id="securityTabButton" data-tab="security" role="tab" aria-selected="false" aria-controls="securityTabPanel">
            <i data-lucide="lock"></i>
            <span>Security</span>
        </button>
    </div>

    <div class="tab-panels">
        <div class="tab-content active" id="profileTabPanel" role="tabpanel" aria-labelledby="profileTabButton" aria-hidden="false">
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
                <label for="gender" class="form-label">Gender</label>
                <select id="gender" name="gender" data-validate="gender" class="form-input" aria-describedby="error_gender" aria-invalid="false" disabled>
                    <option value="">Select gender</option>
                    <option value="male" @selected(($user->gender ?? null) === 'male')>Male</option>
                    <option value="female" @selected(($user->gender ?? null) === 'female')>Female</option>
                    <option value="other" @selected(($user->gender ?? null) === 'other')>Other</option>
                </select>
                <div class="form-hint">Personal profile information used across your library account</div>
                <div class="field-error" id="error_gender" aria-live="polite"></div>
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

        <div class="tab-content" id="photoTabPanel" role="tabpanel" aria-labelledby="photoTabButton" aria-hidden="true" hidden>
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

        <div class="tab-content" id="securityTabPanel" role="tabpanel" aria-labelledby="securityTabButton" aria-hidden="true" hidden>
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
    </div>
</div>
