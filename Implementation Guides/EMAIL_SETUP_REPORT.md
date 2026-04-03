# Email Setup Report

## Scope

This pass audited the full Laravel application for email-related behavior, re-enabled disabled email flows, standardized the templates, added queue-based delivery with a 3-second delay, and verified the most important auth, notification, admin, staff, and scheduled reminder paths.

## Audit Findings

### Email features already present in the codebase

- Signup OTP verification email
- Password reset link email
- Admin-triggered temporary password reset email
- Account lock and account unlock security emails
- Book request approval/rejection emails
- Book issue emails
- Book return emails
- Fine reminder, fine paid, and fine waived emails
- Scheduled overdue reminder and fine reminder commands
- Email verification link routes and tests

### Problems found during the audit

- Four queued email jobs existed but the actual send calls were commented out.
- Book request and fine services only logged placeholder messages instead of queueing emails.
- Admin and staff transaction controllers had disabled email dispatch blocks.
- OTP and password reset email Blade files contained literal markdown code fences, which would render broken email HTML.
- `OTPVerificationMail` pointed to a non-existent Blade view.
- `PasswordResetLinkMail` pointed to a non-existent Blade view.
- `AccountLockedNotification` used the wrong signed route name for the unlock link.
- Fine reminder scheduling used the wrong lifecycle status (`unpaid` instead of the app’s actual `pending` status).
- Student fine summary screens also queried `unpaid` instead of `pending`.
- The app code and tests expected `email_verified_at`, but the users table did not actually have that column.
- The user factory used an invalid default role (`user`) that did not match the schema enum.
- Mail configuration only read `MAIL_SCHEME`, while many environments still use the legacy `MAIL_ENCRYPTION` variable.

## What Was Enabled

### Auth emails

- Registration now queues the OTP email instead of using an inline send.
- OTP verification now queues a welcome email after successful account creation.
- Forgot-password flow now safely queues password reset emails and logs queue failures.
- Email verification link support is now consistent with the schema because `email_verified_at` exists and `User` implements `MustVerifyEmail`.

### Security emails

- Account lock emails now queue correctly and generate a valid signed unlock URL.
- Account unlock emails remain queued and now use the shared branded template system.

### Admin and staff emails

- Admin password resets now queue safely inside a transaction. If queueing fails, the password change is rolled back.
- Student password resets from the admin side follow the same transactional behavior.
- Book request approval/rejection now queues request status emails.
- Admin and staff issue-book actions now queue issued-book emails.
- Admin and staff return-book actions now queue returned-book emails.
- Fine paid/waived/manual reminder flows now queue fine emails instead of logging placeholders.

### Scheduled reminder emails

- Daily overdue reminder command now queues overdue-book reminder emails.
- Weekly fine reminder command now queues pending-fine reminder emails.

## Queue and Retry Behavior

- All fast user-triggered emails are delayed by 3 seconds.
- Delay is controlled by `MAIL_SEND_DELAY_SECONDS` and defaults to `3`.
- Email queue name is controlled by `MAIL_QUEUE` and defaults to `emails`.
- Queued mailables, queued notifications, and email jobs retry 3 times with backoff intervals of `10`, `30`, and `60` seconds.
- Email failures are logged and do not crash the main user workflow.

## Template Standardization

### Shared template system

- Added a shared responsive email shell at `resources/views/emails/layouts/base.blade.php`.
- Templates now use consistent branding, spacing, typography, and footer content.
- Templates resolve library branding through `App\Support\LibraryBranding`.

### Standardized templates

- Welcome email
- OTP verification
- Password reset link
- Temporary password reset
- Account locked
- Account unlocked
- Book issued
- Book returned
- Book overdue
- Request approved
- Request rejected
- Fine pending
- Fine paid
- Fine waived

## Configuration

### Environment variables used

Add or confirm these values in `.env`:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_QUEUE=emails
MAIL_SEND_DELAY_SECONDS=3
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Library Management System"
QUEUE_CONNECTION=database
```

### Notes

- `MAIL_SCHEME=null` is correct for typical STARTTLS SMTP on port `587`.
- `MAIL_SCHEME=smtps` is appropriate for implicit TLS on port `465`.
- The app now tolerates legacy `MAIL_ENCRYPTION=ssl` by mapping it to `smtps`.
- Sensitive credentials stay in `.env` and should never be committed.

## Deployment / Local Setup Steps

1. Run the new migration:

```bash
php artisan migrate
```

2. Clear cached config after changing mail settings:

```bash
php artisan config:clear
php artisan cache:clear
```

3. Start a queue worker for the email queue:

```bash
php artisan queue:work --queue=emails,default
```

4. Ensure the scheduler is running in environments where reminders should be sent:

```bash
php artisan schedule:work
```

### Scheduled reminder timings already configured

- `notifications:overdue-reminders` runs daily at `08:00`
- `notifications:fine-reminders` runs weekly on Monday at `09:00`

## Files Modified

### Core configuration and schema

- `.env.example`
- `config/mail.php`
- `database/migrations/2026_04_03_000002_add_email_verified_at_to_users_table.php`
- `database/factories/UserFactory.php`
- `app/Models/User.php`

### Auth and security flow

- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `app/Http/Controllers/Auth/OTPVerificationController.php`
- `app/Http/Controllers/Auth/PasswordResetLinkController.php`
- `app/Notifications/CustomResetPassword.php`
- `app/Notifications/AccountLockedNotification.php`
- `app/Notifications/AccountUnlockNotification.php`

### Admin, staff, and service layer

- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/StudentController.php`
- `app/Http/Controllers/Admin/TransactionController.php`
- `app/Http/Controllers/Staff/IssueBookController.php`
- `app/Http/Controllers/Staff/ReturnBookController.php`
- `app/Services/BookRequestManagement/BookRequestManagementActionService.php`
- `app/Services/FineManagement/FineManagementActionService.php`
- `app/Console/Commands/SendOverdueReminders.php`
- `app/Console/Commands/SendFineReminders.php`
- `app/Http/Controllers/student/ProfileController.php`
- `app/Http/Controllers/student/MyFinesController.php`

### Email jobs, mailables, and queue helpers

- `app/Jobs/Concerns/HandlesQueuedEmail.php`
- `app/Jobs/SendBookIssuedEmail.php`
- `app/Jobs/SendBookRequestStatusEmail.php`
- `app/Jobs/SendBookReturnedEmail.php`
- `app/Jobs/SendFineEmail.php`
- `app/Jobs/SendOverdueReminderEmail.php`
- `app/Mail/Concerns/QueuesLibraryMail.php`
- `app/Mail/OTPVerificationMail.php`
- `app/Mail/PasswordResetEmail.php`
- `app/Mail/PasswordResetLinkMail.php`
- `app/Mail/BookIssuedSimple.php`
- `app/Mail/BookReturnedSimple.php`
- `app/Mail/BookRequestStatusSimple.php`
- `app/Mail/FineSimple.php`
- `app/Mail/WelcomeEmail.php`
- `app/Mail/OverdueBookReminderMail.php`
- `app/Notifications/Concerns/QueuesLibraryNotification.php`

### Email templates

- `resources/views/emails/layouts/base.blade.php`
- `resources/views/emails/otp-email.blade.php`
- `resources/views/emails/password-reset-email.blade.php`
- `resources/views/emails/password-reset.blade.php`
- `resources/views/emails/welcome.blade.php`
- `resources/views/emails/account-locked.blade.php`
- `resources/views/emails/account-unlocked.blade.php`
- `resources/views/emails/book-issued.blade.php`
- `resources/views/emails/book-returned.blade.php`
- `resources/views/emails/book-overdue.blade.php`
- `resources/views/emails/request-approved.blade.php`
- `resources/views/emails/request-rejected.blade.php`
- `resources/views/emails/fine-pending.blade.php`
- `resources/views/emails/fine-paid.blade.php`
- `resources/views/emails/fine-waived.blade.php`

### Tests

- `tests/Feature/Auth/RegistrationTest.php`
- `tests/Feature/Auth/PasswordResetTest.php`
- `tests/Feature/Auth/AccountLockWorkflowTest.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- `tests/Feature/FineBulkActionTest.php`
- `tests/Feature/BookRequestBulkActionTest.php`
- `tests/Feature/ReminderEmailCommandTest.php`

## Bugs Fixed

- Re-enabled all commented email sends in queued jobs.
- Replaced placeholder “would have been sent” logging with real queued email dispatch.
- Fixed broken OTP and password reset templates.
- Fixed incorrect/missing Blade view references.
- Fixed invalid account unlock route generation inside security emails.
- Fixed reminder status mismatch (`unpaid` vs `pending`) in scheduled reminders and student fine summaries.
- Added the missing `email_verified_at` column expected by verification logic.
- Aligned the test factory with the real user-role schema.
- Preserved admin/student password reset safety by rolling back the password change if the email cannot be queued.

## Validation Performed

Focused feature tests executed successfully:

```bash
php artisan test tests/Feature/Auth/RegistrationTest.php \
  tests/Feature/Auth/PasswordResetTest.php \
  tests/Feature/Auth/AccountLockWorkflowTest.php \
  tests/Feature/FineBulkActionTest.php \
  tests/Feature/BookRequestBulkActionTest.php \
  tests/Feature/ReminderEmailCommandTest.php

php artisan test tests/Feature/Auth/EmailVerificationTest.php

php artisan test tests/Feature/Auth/AuthenticationTest.php
```

### Result

- `29` targeted tests passed
- `0` failures in the email-focused validation set

## Final Notes

- No successful-login email feature existed in the application, so none was added to avoid noisy or redundant messaging.
- Queue workers must be running in non-test environments or emails will remain queued and unsent.
- If you use a real SMTP provider in production, rotate credentials if any old development mailbox password has been broadly shared.
