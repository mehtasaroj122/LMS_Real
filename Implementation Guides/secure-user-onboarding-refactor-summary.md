# Secure User Onboarding and Management Refactor

## Objective

This refactor moves the system away from admin-assigned passwords and toward secure, identity-validated self-registration for `staff` and `student` accounts.

## What Changed

### 1. Admin-managed accounts no longer set passwords

- Removed password handling from admin user creation and edit flows.
- Admins now create invited `staff` and `student` accounts with profile and role data only.
- Invited staff and student accounts remain inactive until registration is completed by the account owner.
- Admin password reset is blocked for invited accounts that have not completed registration yet.

### 2. Role-based invited self-registration

- Rebuilt the registration flow around pre-created invited accounts.
- Registration now supports:
  - `staff`: name, email, staff ID, phone, password, password confirmation
  - `student`: name, email, student ID, phone, password, password confirmation
- Registration verifies:
  - role
  - email
  - staff ID or student ID
  - phone number match if one was already stored
  - phone uniqueness if none was stored before
- Successful registration:
  - sets the user password
  - marks the account active
  - marks the account verified
  - logs the user in
  - queues the welcome email

### 3. Validation and security improvements

- Added dedicated `FormRequest` validation for invited registration.
- Added a reusable custom rule to validate invited identity records.
- Added strong password rules using Laravel `Password` validation.
- Added registration rate limiting keyed by email and IP address.
- Restricted registration to invited `staff` and `student` accounts already present in the database.
- Added invitation emails from admin user creation flows so invited users receive a direct registration link and the identity details needed to activate their account.

### 4. Database and model updates

- `users` table:
  - added nullable `gender`
  - made `password` nullable for invited accounts
- `staff` table:
  - added unique `staff_id`
  - backfilled missing staff IDs for existing records
  - relaxed required staff-only fields so admin invitation records can be created cleanly
- `students` table:
  - added unique `student_id`
  - backfilled `student_id` from `roll_no` when needed
- Updated models so `student_id` and `roll_no` stay synchronized and user registration state can be queried cleanly.

### 5. UI and admin workflow updates

- Updated admin user management:
  - removed password inputs
  - added `gender`
  - added `staff_id` for staff
  - exposed identity information in the table and view modal
  - shows pending-registration state for invited accounts
- Updated admin student management:
  - added `gender`
  - disabled reset-password actions for invited students who have not registered yet
- Updated student profile UI to manage `gender`
- Replaced the registration Blade page with a dynamic role-aware self-registration form
- Added queued invitation emails for both admin user management and admin student creation pages

## Main Files Updated

- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `app/Services/Auth/InvitedUserRegistrationService.php`
- `app/Http/Requests/Auth/CompleteRegistrationRequest.php`
- `app/Rules/MatchesInvitedUserIdentity.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/StudentController.php`
- `app/Http/Requests/AdminUserRequest.php`
- `app/Http/Requests/StudentManagement/StoreStudentRequest.php`
- `app/Http/Requests/StudentManagement/UpdateStudentRequest.php`
- `app/Models/User.php`
- `app/Models/staff.php`
- `app/Models/student.php`
- `database/migrations/2026_04_05_000001_refactor_user_onboarding_schema.php`
- `resources/views/auth/register.blade.php`
- `resources/views/Admin/UserManagement.blade.php`
- `resources/views/Admin/Students.blade.php`
- `resources/views/Admin/partials/user-row.blade.php`
- `resources/views/Student/partials/profile-settings-panel.blade.php`
- `resources/views/Student/partials/profile-settings-script.blade.php`
- `resources/views/shared/student-profile/page.blade.php`

## Resulting Flow

1. Admin creates a `staff` or `student` account without a password.
2. The invited user opens the registration page.
3. The invited user selects the correct role and enters email, ID, phone, and password.
4. The system validates the invited identity against existing records.
5. The system activates the account only after successful self-registration.

## Backward Compatibility Notes

- Existing roles remain unchanged.
- Existing authentication continues to work for already-registered users.
- OTP-related routes and controller code were left in place so the broader auth module is not abruptly broken, even though invited self-registration is now the primary path.

## Optional Items Not Added

- No new `pending` status enum was introduced. Pending registration is represented by `inactive` plus a null password.
- Email verification before activation was not added because the system currently activates invited accounts immediately after identity-validated registration.

## Verification Completed

- `php artisan view:cache`
- `php artisan test tests/Feature/Auth/RegistrationTest.php tests/Feature/Auth/AuthenticationTest.php tests/Feature/ActivityLogAuditFormattingTest.php tests/Feature/Admin/StudentPrivilegeResetTest.php`

All of the above checks passed after the refactor.
