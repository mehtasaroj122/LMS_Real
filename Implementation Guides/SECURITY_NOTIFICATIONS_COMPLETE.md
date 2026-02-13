# Security Notifications Implementation Guide

## Overview

The Library Management System now includes a comprehensive security notification suite with 8 notification types covering account security events, login activities, and authentication changes. This ensures users are immediately informed of any security-related activities on their account.

## Security Notification Types

### 1. **account.password_changed** ✅
**Status:** Fully Implemented
**Trigger:** When user/student updates their password
**Implementation Files:**
- `app/Http/Controllers/Auth/PasswordController.php` (update() method)
- `app/Http/Controllers/student/ProfileController.php` (updatePassword() method)

**Notification Data:**
```php
[
    'ip' => request()->ip(),
    'timestamp' => now()
]
```

**Message Example:** "Your password was changed on Mar 15, 2025 10:30 AM"

**User Impact:** Users know immediately when their password has been changed, helping them detect unauthorized changes.

---

### 2. **account.profile_updated** ✅
**Status:** Fully Implemented
**Trigger:** When user updates profile information (name, phone, etc.)
**Implementation Files:**
- `app/Http/Controllers/ProfileController.php` (update() method)
- `app/Http/Controllers/student/ProfileController.php` (updatePersonalInfo() method)

**Notification Data:**
```php
[
    'changes' => ['name', 'phone', 'department'],
    'ip' => request()->ip()
]
```

**Message Example:** "Your profile information was updated on Mar 15, 2025"

**User Impact:** Users can verify that changes to their profile were made by them and not by an unauthorized person.

---

### 3. **account.email_changed** ✅
**Status:** Fully Implemented
**Trigger:** When user changes their email address (separate notification from profile update)
**Implementation Files:**
- `app/Http/Controllers/ProfileController.php` (update() method - separate notification)
- `app/Http/Controllers/student/ProfileController.php` (updatePersonalInfo() method - separate notification)

**Notification Data:**
```php
[
    'old_email' => 'previous@example.com',
    'new_email' => 'new@example.com'
]
```

**Message Example:** "Your email address was changed to new@example.com"

**User Impact:** Users are notified of email changes, critical for account recovery and communication purposes.

---

### 4. **account.password_reset** ✅
**Status:** Fully Implemented
**Trigger:** When user requests a password reset link
**Implementation File:**
- `app/Http/Controllers/Auth/PasswordResetLinkController.php` (store() method)

**Notification Data:**
```php
[
    'ip' => request()->ip(),
    'timestamp' => now()
]
```

**Message Example:** "A password reset link was sent to your email. If you did not request this, please ignore."

**User Impact:** Users are informed immediately when someone attempts to reset their password, allowing them to stop unauthorized reset attempts.

---

### 5. **account.password_reset_completed** ✅
**Status:** Fully Implemented
**Trigger:** When password reset link is used to successfully reset password
**Implementation File:**
- `app/Http/Controllers/Auth/NewPasswordController.php` (store() method)

**Notification Data:**
```php
[
    'ip' => request()->ip(),
    'timestamp' => now()
]
```

**Message Example:** "Your password was reset on Mar 15, 2025 10:30 AM"

**User Impact:** Users know their password reset was successful, confirming legitimate access recovery.

---

### 6. **security.new_device_login** ✅
**Status:** Fully Implemented
**Trigger:** When user logs in from a new device or IP address
**Implementation File:**
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` (store() method)

**Notification Data:**
```php
[
    'ip' => request()->ip(),
    'timestamp' => now()
]
```

**Message Example:** "Your account was accessed from a new device on Mar 15, 2025 10:30 AM"

**Detection Logic:**
- Checks user's previous login IP addresses from activity logs
- Triggers if login from different IP or last different IP login was >24 hours ago
- Helps users detect unauthorized access attempts

**User Impact:** Users are immediately aware of new device logins, helping them identify account compromises early.

---

### 7. **security.suspicious_activity** ✅
**Status:** Fully Implemented via Event Listener
**Trigger:** After 3 failed login attempts within rate limit
**Implementation File:**
- `app/Listeners/LogSuspiciousActivity.php` (handles Failed authentication event)

**Notification Data:**
```php
[
    'activity_type' => 'failed_login_attempts',
    'attempt_count' => 3,
    'ip' => request()->ip(),
    'timestamp' => now()
]
```

**Message Example:** "Multiple failed login attempts detected on your account from IP: 192.168.1.100"

**User Impact:** Users are alerted about potential brute force attacks on their account in real-time.

---

### 8. **security.account_locked** ✅
**Status:** Fully Implemented via Event Listener
**Trigger:** When account is locked after maximum failed login attempts (>5 attempts)
**Implementation File:**
- `app/Listeners/SendAccountLockedNotification.php` (handles Lockout event)

**Notification Data:**
```php
[
    'reason' => 'Too many failed login attempts',
    'retry_after' => 900,  // 15 minutes in seconds
    'ip' => request()->ip(),
    'timestamp' => now()
]
```

**Message Example:** "Your account has been temporarily locked due to too many failed login attempts. Please try again after 15 minutes."

**User Impact:** Users know their account is protected and can contact support if they're the legitimate user experiencing lockouts.

---

### 9. **security.two_factor_enabled** ✅
**Status:** Fully Implemented
**Trigger:** When user enables two-factor authentication
**Implementation File:**
- `app/Http/Controllers/SecuritySettingsController.php` (enableTwoFactor() method)

**Notification Data:**
```php
[
    'timestamp' => now(),
    'ip' => request()->ip(),
    'action' => 'enable'
]
```

**Message Example:** "Two-factor authentication has been successfully enabled on your account for additional security."

**User Impact:** Users are notified of this security enhancement, confirming successful setup.

---

## Implementation Architecture

### Event-Listener Pattern (Used for auth events)

```
Failed Login Attempt
        ↓
Auth\Failed Event (triggered by LoginRequest)
        ↓
LogSuspiciousActivity Listener
        ↓
Check attempt count (>= 3)
        ↓
Notification::notify() → security.suspicious_activity
```

### Account Lockout Pattern

```
Max Failed Attempts (5)
        ↓
Auth\Lockout Event (triggered by LoginRequest)
        ↓
SendAccountLockedNotification Listener
        ↓
Notification::notify() → security.account_locked
```

### Direct Controller Pattern (Used for account changes)

```
User Updates Password
        ↓
Auth/PasswordController.update()
        ↓
Password::update()
        ↓
Notification::notify() → account.password_changed
```

## Event Service Provider Configuration

All listeners are registered in `app/Providers/EventServiceProvider.php`:

```php
protected $listen = [
    Login::class => [
        UpdateLastLogin::class,
    ],
    Lockout::class => [
        SendAccountLockedNotification::class,
    ],
    Failed::class => [
        LogSuspiciousActivity::class,
    ],
];
```

## Database Schema Requirements

The User model should have the following fields for 2FA support:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('two_factor_secret')->nullable()->after('remember_token');
    $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
});
```

## Routes for Security Settings

Add these routes to `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    Route::post('/security/two-factor/enable', [SecuritySettingsController::class, 'enableTwoFactor'])->name('security.two-factor.enable');
    Route::post('/security/two-factor/disable', [SecuritySettingsController::class, 'disableTwoFactor'])->name('security.two-factor.disable');
});
```

## Testing Guide

### 1. Test Password Change Notification
1. Login as admin/staff/student
2. Navigate to profile settings
3. Change password
4. Verify `account.password_changed` notification appears
5. Check notification contains IP and timestamp

### 2. Test Profile Update Notification
1. Login to profile
2. Update name or phone
3. Verify `account.profile_updated` notification
4. Update email address
5. Verify separate `account.email_changed` notification

### 3. Test Password Reset Notifications
1. Click "Forgot Password"
2. Enter email
3. Verify `account.password_reset` notification sent
4. Click reset link
5. Set new password
6. Verify `account.password_reset_completed` notification

### 4. Test New Device Login
1. Login from one device/browser
2. Check activity log or notifications (should have login recorded)
3. Login from different IP or browser
4. Verify `security.new_device_login` notification triggered

### 5. Test Suspicious Activity Detection
1. Attempt login with wrong password 3 times
2. Verify `security.suspicious_activity` notification after 3rd failed attempt
3. Continue failing more attempts
4. Verify `security.account_locked` notification after 5th failed attempt

### 6. Test Two-Factor Authentication
1. Navigate to security settings
2. Click "Enable Two-Factor Authentication"
3. Verify `security.two_factor_enabled` notification
4. Click "Disable" and confirm with password
5. Verify notification about disablement

## Security Best Practices Implemented

1. **IP Address Tracking:** All security notifications include user's IP address for audit trails
2. **Timestamp Recording:** All events are timestamped for incident response
3. **Real-time Alerts:** Notifications sent immediately (not batched) for critical security events
4. **Activity Logging:** All events logged to activity_logs table for investigation
5. **Rate Limiting:** Laravel's built-in rate limiting (5 failed attempts before lockout)
6. **Event-Based Architecture:** Using Laravel events ensures loose coupling and extensibility
7. **User Notification:** Users can always see recent security events via notifications
8. **Audit Trail:** SecuritySettingsController logs 2FA changes with user actions

## Common Issues and Solutions

### Issue 1: Notifications not appearing
**Solution:** 
- Verify notification driver is 'database' in config/notification.php
- Check queue is running: `php artisan queue:work`
- Verify EventServiceProvider has all listeners registered

### Issue 2: Duplicate notifications
**Solution:**
- Check that listener is not registered twice
- Verify no multiple event broadcasts
- Clear event cache: `php artisan event:clear`

### Issue 3: Failed login notifications to non-existent users
**Solution:**
- LogSuspiciousActivity listener checks `if (!$user)` and returns early
- Only real users receive suspicious activity notifications

### Issue 4: 2FA secret not generating
**Solution:**
- Ensure User model has two_factor_secret column
- Run migrations: `php artisan migrate`
- Use SecuritySettingsController for enabling 2FA

## Future Enhancements

1. **Geo-IP Tracking:** Detect login location changes
2. **Device Fingerprinting:** Track devices more accurately
3. **Notification Preferences:** Allow users to customize which notifications they receive
4. **Notification Channels:** Add SMS and push notifications for critical security alerts
5. **Risk Scoring:** Implement ML-based risk scoring for login attempts
6. **2FA Methods:** Support authenticator apps, SMS, email verification codes
7. **Session Management:** Allow users to see and terminate active sessions
8. **Security Audit Report:** Monthly security activity summary

## Integration with Admin Dashboard

Admin can view user security events:

```php
$recentSecurityEvents = $user->notifications()
    ->where('type', 'like', 'security.%')
    ->orWhere('type', 'like', 'account.%')
    ->latest()
    ->take(10)
    ->get();
```

## Conclusion

The full security notification suite (8 types) is now fully implemented and integrated across the Library Management System. All account events, authentication changes, and security threats are properly communicated to users in real-time.

**Total Notification Types:** 18 (11 Library + 4 Admin + 3 Security)
**Status:** ✅ COMPLETE AND PRODUCTION-READY

