# Account Lockout & Unlock Guide - Rate Limiting System

## 🔒 How Account Lockout Works

### Trigger Mechanism
```
Failed Login Attempt → RateLimiter::hit()
    ↓
5+ failed attempts with SAME email + IP address
    ↓
RateLimiter::tooManyAttempts($key, 5) returns TRUE
    ↓
Lockout event fired
    ↓
Account locked for 60 MINUTES
    ↓
User sees: "Please wait before retrying login"
```

### Current Configuration (From LoginRequest.php)
- **Threshold:** 5 failed login attempts
- **Duration:** 60 minutes (default Laravel)
- **Lock Key:** `email|ip_address`
- **Status:** ✅ Active and working

---

## 🔑 How to Unlock an Account

### Option 1: ⏰ Automatic Unlock (Recommended)
**Wait 60 minutes** - Account automatically unlocks after the rate limit expires.

```
Lockout Time: 5:00 PM
↓
+60 minutes
↓
Unlock Time: 6:00 PM → Can login again
```

---

### Option 2: 🛠️ Manual Unlock (Admin Command)

Create an Artisan command to unlock accounts manually:

```bash
php artisan cache:clear
```

**However, for a cleaner approach, create a dedicated unlock command:**

Run this command in your terminal:
```bash
php artisan tinker
```

Then execute:
```php
// Method 1: Clear all rate limit keys
use Illuminate\Support\Facades\RateLimiter;

// Clear specific user's lockout
RateLimiter::clear('john@example.com|192.168.1.100');

// Output: true (unlocked)
exit();
```

---

### Option 3: 🗄️ Database Unlock (Direct)

Rate limits are stored in the **cache table**. Clear them directly:

```sql
-- See all rate limit entries
SELECT * FROM cache WHERE `key` LIKE '%throttle%' OR `key` LIKE '%login%';

-- Delete specific user's lockout
DELETE FROM cache WHERE `key` = 'laravel_cache:throttle|john@example.com|192.168.1.100';

-- Verify it's deleted
SELECT * FROM cache WHERE `key` LIKE '%throttle%';
```

---

### Option 4: 📋 Create Admin Interface (Best Practice)

I can help you create an admin panel to unlock accounts. Would you like me to create:

**A. Admin Command:**
```bash
php artisan auth:unlock-account {email}
```

**B. Admin API Endpoint:**
```
POST /admin/users/{id}/unlock
```

**C. Admin Dashboard Feature:**
- List locked accounts
- One-click unlock button
- View lockout timeline

---

## 🔍 How to Check Locked Accounts

### Check from Tinker:
```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

// Check if specific account is locked
$email = 'john@example.com';
$ip = '192.168.1.100';
$throttleKey = strtolower($email) . '|' . $ip;

// Check attempts
$attempts = RateLimiter::attempts($throttleKey);
echo "Attempts: " . $attempts;

// Check if locked
$locked = RateLimiter::tooManyAttempts($throttleKey, 5);
echo "Locked: " . ($locked ? 'YES' : 'NO');

// Check minutes remaining
if ($locked) {
    $minutes = ceil(RateLimiter::availableIn($throttleKey) / 60);
    echo "Unlock in: " . $minutes . " minutes";
}

exit();
```

### Check from Database:
```sql
SELECT `key`, `value`, `expiration` FROM cache 
WHERE `key` LIKE '%throttle%' 
AND `expiration` > NOW()
ORDER BY `expiration` DESC;
```

---

## 🚨 Rate Limit Configuration

### Current Settings (LoginRequest.php)
```php
// Threshold: 5 failed attempts
RateLimiter::tooManyAttempts($throttleKey, 5)

// Duration: Check config/cache.php
// Default: 60 minutes (1 hour)

// Scope: Email + IP address combination
Str::lower($email) . '|' . $this->ip()
```

### To Change the Limit (If Needed)

**Edit:** `app/Http/Requests/Auth/LoginRequest.php`

```php
// Change from 5 to 3 attempts
if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {  // ← Change 5 to 3
    return;
}
```

### To Change the Duration (If Needed)

**Edit:** `app/Http/Requests/Auth/LoginRequest.php`

```php
public function ensureIsNotRateLimited(): void
{
    if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
        return;
    }

    event(new Lockout($this));

    $seconds = RateLimiter::availableIn($this->throttleKey());
    
    // ✅ To change from 60 min to 30 min, override here:
    // $seconds = 30 * 60; // 30 minutes in seconds

    throw ValidationException::withMessages([
        'email' => trans('auth.throttle', [
            'seconds' => $seconds,
            'minutes' => ceil($seconds / 60),
        ]),
    ]);
}
```

---

## 📊 Rate Limiting Events & Notifications

### When Lockout Occurs:
1. ✅ `Illuminate\Auth\Events\Lockout` event fires
2. ✅ `SendAccountLockedNotification` listener triggered
3. ✅ User receives notification: "Account locked due to too many failed attempts"
4. ✅ Activity logged in `activity_logs` table
5. ✅ IP address recorded for security audit

### Security Features Active:
- ✅ IP address tracking
- ✅ Timestamp recording
- ✅ Activity audit logging
- ✅ Real-time user notification
- ✅ Failed attempt counting

---

## 🎯 Quick Solutions

### Problem 1: "Please wait before retrying"
**Solution:** 
- Wait 60 minutes, OR
- Run `php artisan tinker` and clear the rate limit

### Problem 2: Same IP but Different User Locked
**Solution:**
- Each user has separate lockout by IP
- User A locked from 192.168.1.1 ≠ User B locked from 192.168.1.1
- Unlock is: `email|ip`

### Problem 3: User Locked from Multiple IPs
**Solution:**
- Each IP has separate lockout
- User must wait from each IP OR clear each one:
```php
RateLimiter::clear('john@example.com|192.168.1.1');
RateLimiter::clear('john@example.com|192.168.1.2');
RateLimiter::clear('john@example.com|192.168.1.3');
```

### Problem 4: Forgot Password While Locked
**Solution:**
- User can click "Forgot Password" (no rate limit there)
- Reset password via email link
- Then login with new password
- Lockout status remains (same email + IP)
- So user still needs to wait 60 min OR use different IP

---

## 🛡️ Security Best Practices

✅ **Never lock longer than 1 hour** - Too frustrating
✅ **Set to at least 3-5 attempts** - Allows for typos
✅ **Track by IP + Email** - Prevents abuse
✅ **Notify user immediately** - They know they're locked
✅ **Keep logs** - For security investigation
✅ **Allow password reset** - Even when locked

---

## 📝 Current System Status

| Feature | Status | Details |
|---------|--------|---------|
| Rate Limiting | ✅ Active | 5 attempts trigger lockout |
| Lockout Duration | ✅ 60 minutes | Automatic unlock |
| IP Tracking | ✅ Active | Per IP address |
| Notifications | ✅ Active | User notified on lockout |
| Activity Logging | ✅ Active | All attempts logged |
| Manual Unlock | ⚠️ Manual | Need Tinker/SQL command |

---

## 🎁 Would You Like Me To Add?

1. **Admin Dashboard Feature** - UI to unlock accounts
2. **CLI Command** - `php artisan auth:unlock-account {email}`
3. **Configurable Limits** - Change 5 attempts to X
4. **Configurable Duration** - Change 60 min to X
5. **Unlock History** - Track who unlocked what
6. **Email Reset Link** - Auto-unlock option via email
7. **IP Whitelist** - Exempt certain IPs from rate limiting

**Let me know which you'd like implemented! ✨**

