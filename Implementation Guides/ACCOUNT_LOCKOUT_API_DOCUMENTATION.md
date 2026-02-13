# Account Lockout System - API & Developer Documentation

## 🎯 Overview

Complete API documentation for the account lockout management system.

---

## 📡 HTTP Endpoints

### Admin Routes (Require Admin Permission)

**Base URL:** `/admin/account-locks`

#### 1. GET - List Locked Accounts

```http
GET /admin/account-locks
Authorization: Bearer {auth_token}
```

**Response:**
```blade
<!-- Returns rendered HTML view -->
<!-- Shows locked accounts table, settings form, info card -->
```

**Authorization:** Admin only (`access-admin` gate)

---

#### 2. POST - Unlock Specific Account

```http
POST /admin/account-locks/unlock
Authorization: Bearer {auth_token}
Content-Type: application/json

{
  "email": "user@example.com",
  "ip": "192.168.1.100"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Account lock cleared for 'user@example.com' from IP '192.168.1.100'"
}
```

**Response (404):**
```json
{
  "success": false,
  "message": "User not found"
}
```

**Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "ip": ["The ip field is required."]
  }
}
```

---

#### 3. POST - Unlock All Accounts

```http
POST /admin/account-locks/unlock-all
Authorization: Bearer {auth_token}
Content-Type: application/json
```

**Response (200):**
```json
{
  "success": true,
  "message": "All account locks have been cleared",
  "cleared_count": 5
}
```

---

#### 4. POST - Update Settings

```http
POST /admin/account-locks/settings
Authorization: Bearer {auth_token}
Content-Type: application/json

{
  "max_attempts": 5,
  "lockout_duration": 60,
  "rate_limiting_enabled": true,
  "email_unlock_enabled": true
}
```

**Parameters:**
- `max_attempts` (integer) - Min: 1, Max: 20
- `lockout_duration` (integer) - Min: 1, Max: 1440 (minutes)
- `rate_limiting_enabled` (boolean) - Enable/disable rate limiting
- `email_unlock_enabled` (boolean) - Enable/disable email unlock

**Response (200):**
```json
{
  "success": true,
  "message": "Settings updated successfully",
  "settings": {
    "max_attempts": 5,
    "lockout_duration": 60,
    "rate_limiting_enabled": true,
    "email_unlock_enabled": true
  }
}
```

**Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "max_attempts": ["The max_attempts must be between 1 and 20."]
  }
}
```

---

### Public Routes (No Authentication)

#### 5. GET - Unlock via Email Link

```http
GET /auth/unlock-account
?email=user@example.com
&ip=192.168.1.100
&signature={signed_hash}
```

**Parameters:**
- `email` (string, required) - User email address
- `ip` (string, required) - IP address to unlock
- `signature` (string, required) - Signed route signature

**Response (302 Redirect):**
```
Location: /login?message=Account+unlocked+successfully
```

**Success Message:** "Account unlocked successfully! Please log in."

**Response (404):**
```
<!-- Invalid or expired signature -->
<!-- 404 Not Found page -->
```

---

## 🔧 Artisan Commands

### CLI Command

```bash
php artisan auth:unlock-account {email} {options}
```

**Arguments:**
- `email` (required) - User email address

**Options:**
- `--all-ips` - Unlock from all IP addresses

**Examples:**

```bash
# Interactive unlock
php artisan auth:unlock-account john@example.com

# Unlock all IPs
php artisan auth:unlock-account john@example.com --all-ips

# With help
php artisan auth:unlock-account --help
```

**Output:**
```
✅ Account lock cleared for 'john@example.com' from IP '192.168.1.100'
📝 Logged to activity_logs
```

---

## 📋 Database Schema

### Cache Table (Rate Limiting Storage)

**Table:** `cache`

```sql
SELECT * FROM cache 
WHERE `key` LIKE 'throttle:%'

-- Example entry:
-- key: throttle:john@example.com|192.168.1.100
-- value: 5 (number of attempts)
-- expiration: 1674045600 (unix timestamp)
```

**Lock Key Format:**
```
throttle:{email}|{ip}
```

### Activity Logs Table (Optional)

**Table:** `activity_logs`

```sql
SELECT * FROM activity_logs 
WHERE action = 'account_unlocked'
ORDER BY created_at DESC

-- Columns:
-- id, user_id, action, description, ip_address, user_agent, created_at, updated_at
```

---

## 🔐 Security Features

### Email Link Signing

Email unlock links use Laravel's **signed routes** with cryptographic signatures:

```php
// Generated link
route('auth.unlock-account', [
    'email' => 'user@example.com',
    'ip' => '192.168.1.100'
], true) // 'true' = signed URL

// Example:
// /auth/unlock-account?email=user@example.com&ip=192.168.1.100&signature=abc123...
```

**Features:**
- ✅ Cryptographically signed with `APP_KEY`
- ✅ Expires after 24 hours
- ✅ Cannot be tampered with
- ✅ Unique signature per link

### Rate Limiting Checks

```php
// Location: app/Http/Requests/Auth/LoginRequest.php

public function ensureIsNotRateLimited()
{
    if (!config('security.rate_limiting.enabled')) {
        return;
    }
    
    // Uses configurable max_attempts from config/security.php
    return $this->authenticate();
}
```

---

## 📧 Notification Events

### AccountUnlockNotification

**Triggers:** When admin sends unlock email or user clicks email link

**Channels:**
1. **Mail** - Sends email with unlock link
2. **Database** - Creates database notification record

**Email Template Location:**
```
app/Notifications/AccountUnlockNotification.php
```

**Email Contents:**
- Account locked reason
- IP address
- Lockout duration
- Secure unlock link (24-hour expiry)
- Security tips
- Support contact info

**Example Email:**
```
Subject: Your Account Has Been Locked

Hi John,

Your account has been temporarily locked due to multiple failed login attempts.

Account Details:
- Email: john@example.com
- IP Address: 192.168.1.100
- Locked Since: 2026-01-31 10:00:00
- Will Auto-Unlock: 2026-01-31 11:00:00

Quick Unlock:
[Unlock Account Button] (link expires in 24 hours)

Security Tips:
- Change your password if compromised
- Review login history
- Enable two-factor authentication

Need help? Contact support@library.com
```

---

## 🎯 Configuration

### config/security.php

```php
<?php

return [
    'rate_limiting' => [
        'max_attempts' => env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('SECURITY_LOCKOUT_DURATION', 60),
        'enabled' => env('SECURITY_RATE_LIMITING_ENABLED', true),
    ],
    
    'email_unlock' => [
        'enabled' => env('SECURITY_EMAIL_UNLOCK_ENABLED', true),
        'expiration' => 24, // hours
    ],
    
    'logging' => [
        'log_failed_attempts' => env('SECURITY_LOG_FAILED_ATTEMPTS', true),
        'log_lockouts' => env('SECURITY_LOG_LOCKOUTS', true),
        'log_unlocks' => env('SECURITY_LOG_UNLOCKS', true),
    ],
];
```

### Environment Variables

**.env:**
```env
# Rate Limiting Settings
SECURITY_MAX_LOGIN_ATTEMPTS=5
SECURITY_LOCKOUT_DURATION=60
SECURITY_RATE_LIMITING_ENABLED=true
SECURITY_EMAIL_UNLOCK_ENABLED=true

# Logging
SECURITY_LOG_FAILED_ATTEMPTS=true
SECURITY_LOG_LOCKOUTS=true
SECURITY_LOG_UNLOCKS=true
```

---

## 🔄 Workflow Examples

### Example 1: Admin Unlocks User via CLI

```bash
# Step 1: Run command
php artisan auth:unlock-account john@example.com

# Step 2: Select IP or unlock all
? Select IP to unlock (or 0 for all): 1

# Step 3: Confirmation
✅ Account lock cleared for 'john@example.com' from IP '192.168.1.100'
📝 Logged to activity_logs

# Step 4: User can now login
```

**Activity Log Entry:**
```
{
  "user_id": 1,
  "action": "account_unlocked",
  "description": "Account unlocked by admin via CLI",
  "ip_address": "203.0.113.50",
  "unlocked_user": "john@example.com",
  "created_at": "2026-01-31 10:35:00"
}
```

---

### Example 2: User Unlocks via Email Link

```
1. User locked after 5 failed attempts
   ↓
2. Admin sends unlock email
   ↓
3. User receives email with link
   ↓
4. User clicks: /auth/unlock-account?email=john@example.com&ip=192.168.1.100&signature=...
   ↓
5. Controller verifies signature (signed routes)
   ↓
6. Clears cache entry: throttle:john@example.com|192.168.1.100
   ↓
7. Logs unlock action
   ↓
8. Redirects to login: /login?message=Account+unlocked
   ↓
9. User can now login
```

**Code Flow:**
```php
// In AccountUnlockController@unlockFromEmail()

// 1. Validate signature (Laravel does automatically)
// 2. Verify user exists
$user = User::where('email', $request->email)->first();

// 3. Clear rate limit
RateLimiter::clear('throttle:' . $email . '|' . $ip);

// 4. Log activity
ActivityLogger::log('account_unlocked', "Unlocked via email link", $ip);

// 5. Notify user
$user->notify(new AccountUnlockedNotification());

// 6. Redirect
return redirect('/login')->with('success', 'Account unlocked!');
```

---

### Example 3: Change Settings Programmatically

```php
// In PHP/Tinker
$settings = [
    'max_attempts' => 3,
    'lockout_duration' => 120,
    'rate_limiting_enabled' => true
];

// Via POST to /admin/account-locks/settings
// Or programmatically:

$env = file_get_contents('.env');
$env = preg_replace(
    '/SECURITY_MAX_LOGIN_ATTEMPTS=(.*)/',
    'SECURITY_MAX_LOGIN_ATTEMPTS=3',
    $env
);
file_put_contents('.env', $env);

// Clear cache
Artisan::call('config:clear');
```

---

## 📊 Data Models

### User Model (Important Methods)

```php
class User extends Model
{
    // Check if user account is locked (via cache)
    public function isAccountLocked(): bool
    {
        // Check if any throttle entries exist for this user
        return DB::table('cache')
            ->where('key', 'LIKE', 'throttle:' . $this->email . '%')
            ->exists();
    }
    
    // Get locked IPs
    public function getLockedIps(): array
    {
        return DB::table('cache')
            ->where('key', 'LIKE', 'throttle:' . $this->email . '%')
            ->get()
            ->map(fn ($entry) => explode('|', str_replace('throttle:', '', $entry->key))[1])
            ->toArray();
    }
}
```

---

## 🚀 Integration Examples

### Send Email Unlock

```php
// In controller or job
use App\Models\User;
use App\Notifications\AccountUnlockNotification;

$user = User::find(1);
$ip = '192.168.1.100';

$user->notify(new AccountUnlockNotification($user, $ip));
```

### Check Rate Limit Programmatically

```php
use Illuminate\Support\Facades\RateLimiter;

$key = 'throttle:' . $email . '|' . $ip;
$attempts = RateLimiter::attempts($key);
$maxAttempts = config('security.rate_limiting.max_attempts');

if ($attempts >= $maxAttempts) {
    // Account is locked
}
```

### Clear Specific Lock

```php
use Illuminate\Support\Facades\RateLimiter;

$key = 'throttle:user@example.com|192.168.1.100';
RateLimiter::clear($key);
```

### Clear All Locks for User

```php
use Illuminate\Support\Facades\DB;

$email = 'user@example.com';
DB::table('cache')
    ->where('key', 'LIKE', 'throttle:' . $email . '%')
    ->delete();
```

---

## 📈 Monitoring & Logs

### View Recent Unlocks

```bash
tail -f storage/logs/laravel.log | grep "account_unlocked"
```

### Query Activity Logs

```bash
php artisan tinker
>>> use App\Models\ActivityLog;
>>> ActivityLog::where('action', 'account_unlocked')->latest()->limit(10)->get();
```

### Monitor Cache Locks

```bash
php artisan tinker
>>> use Illuminate\Support\Facades\DB;
>>> DB::table('cache')->where('key', 'LIKE', 'throttle:%')->get();
```

---

## 🧪 Testing

### Unit Test Example

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\RateLimiter;

class AccountLockTest extends TestCase
{
    public function test_user_locked_after_max_attempts()
    {
        $user = User::factory()->create();
        
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit('throttle:' . $user->email . '|127.0.0.1');
        }
        
        $this->assertTrue($user->isAccountLocked());
    }
    
    public function test_unlock_via_artisan_command()
    {
        $user = User::factory()->create();
        
        // Lock account
        RateLimiter::hit('throttle:' . $user->email . '|127.0.0.1', 5);
        
        // Run command
        $this->artisan('auth:unlock-account', [
            'email' => $user->email,
            '--all-ips' => true
        ])->assertSuccessful();
        
        // Verify unlocked
        $this->assertFalse($user->isAccountLocked());
    }
}
```

---

## 📞 Support

- **Commands:** `php artisan list | grep auth`
- **Routes:** `php artisan route:list | grep lock`
- **Config:** `php artisan config:get security`
- **Help:** See `ACCOUNT_LOCKOUT_COMPLETE_GUIDE.md`

---

**API documentation complete! 🚀**

