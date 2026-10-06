{{-- Uses the existing details endpoint and the Admin portal's shared dialog focus handling. --}}
<div id="viewUserModal" class="modal-overlay" aria-hidden="true">
    <div class="modal view-user-modal" role="dialog" aria-modal="true" aria-labelledby="viewUserTitle" aria-describedby="viewUserSubtitle" tabindex="-1">
        <header class="modal-header">
            <div>
                <h2 class="modal-title" id="viewUserTitle">User Details</h2>
                <p class="user-details-subtitle" id="viewUserSubtitle">Profile, role and account information</p>
            </div>
            <button type="button" class="modal-close-btn" id="closeViewUserModal" aria-label="Close">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </header>
        <div class="modal-body" tabindex="0" aria-label="User profile information">
            <div class="user-details-state" id="viewUserState">
                <i class="fas fa-circle-notch fa-spin" id="viewUserLoadingIcon" aria-hidden="true"></i>
                <p id="viewUserFeedback" role="status" aria-live="polite" aria-atomic="true">Loading user details…</p>
                <button type="button" class="btn btn-outline" id="retryViewUser" hidden>Try again</button>
            </div>
            <div class="user-profile-sheet" id="viewUserContent" hidden aria-busy="false">
                <section class="user-profile-hero" aria-labelledby="viewUserName">
                    <div class="user-profile-avatar" id="viewUserAvatar" aria-hidden="true">
                        <img id="viewUserAvatarImage" alt="" hidden>
                        <span id="viewUserAvatarFallback">U</span>
                    </div>
                    <div class="user-profile-meta">
                        <div class="user-profile-name-row">
                            <h3 class="user-profile-name" id="viewUserName"></h3>
                            <span class="user-profile-chip user-self-chip" id="viewUserSelfChip" hidden>You</span>
                        </div>
                        <p class="user-profile-email" id="viewUserEmail"></p>
                        <div class="user-profile-chip-row">
                            <span class="user-profile-chip" id="viewUserRoleChip"></span>
                            <span class="user-profile-chip" id="viewUserStatusChip"></span>
                        </div>
                    </div>
                </section>

                <section class="user-details-section" aria-labelledby="viewUserPersonalHeading">
                    <h3 class="user-details-heading" id="viewUserPersonalHeading"><i class="fas fa-user" aria-hidden="true"></i> Personal Information</h3>
                    <dl class="user-detail-grid">
                        <div class="user-detail-item"><dt>Phone</dt><dd id="viewUserPhone"></dd></div>
                        <div class="user-detail-item"><dt>Gender</dt><dd id="viewUserGender"></dd></div>
                        <div class="user-detail-item full"><dt>Address</dt><dd id="viewUserAddress"></dd></div>
                    </dl>
                </section>

                <section class="user-details-section" aria-labelledby="viewUserRoleHeading">
                    <h3 class="user-details-heading" id="viewUserRoleHeading"><i class="fas fa-shield-alt" id="viewUserRoleIcon" aria-hidden="true"></i> <span id="viewUserRoleHeadingText">Administrative Information</span></h3>
                    <dl class="user-detail-grid">
                        <div class="user-detail-item" id="viewUserStaffIdItem" hidden><dt>Staff ID</dt><dd id="viewUserStaffId"></dd></div>
                        <div class="user-detail-item" id="viewUserRollNoItem" hidden><dt>Student ID</dt><dd id="viewUserRollNo"></dd></div>
                        <div class="user-detail-item"><dt>Department</dt><dd id="viewUserDepartment"></dd></div>
                        <div class="user-detail-item" id="viewUserDesignationItem" hidden><dt>Designation</dt><dd id="viewUserDesignation"></dd></div>
                        <div class="user-detail-item" id="viewUserJoinDateItem" hidden><dt>Join Date</dt><dd id="viewUserJoinDate"></dd></div>
                        <div class="user-detail-item" id="viewUserBatchItem" hidden><dt>Batch</dt><dd id="viewUserBatch"></dd></div>
                        <div class="user-detail-item" id="viewUserSemesterItem" hidden><dt>Semester</dt><dd id="viewUserSemester"></dd></div>
                    </dl>
                </section>

                <section class="user-details-section user-account-section" aria-labelledby="viewUserAccountHeading">
                    <h3 class="user-details-heading" id="viewUserAccountHeading"><i class="fas fa-shield-alt" aria-hidden="true"></i> Account Information</h3>
                    <dl class="user-detail-grid">
                        <div class="user-detail-item"><dt><i class="fas fa-clock" aria-hidden="true"></i> Last Login</dt><dd id="viewUserLastLogin"></dd></div>
                        <div class="user-detail-item"><dt><i class="fas fa-calendar-alt" aria-hidden="true"></i> Created Date</dt><dd id="viewUserCreatedAt"></dd></div>
                    </dl>
                </section>
            </div>
        </div>
        <footer class="modal-footer">
            <button type="button" class="btn btn-outline" id="closeViewUserFooter">Close</button>
        </footer>
    </div>
</div>
