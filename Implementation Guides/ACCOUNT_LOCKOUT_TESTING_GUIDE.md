# Account Lockout System - Testing Guide

## 🧪 Complete Testing Workflow

This guide walks you through testing all 4 unlock methods.

---

## Phase 1: Verify Installation ✅

### Step 1: Check Files Exist

```bash
# Run in terminal (PowerShell or Command Prompt)
dir app\Console\Commands\UnlockAccountCommand.php
dir app\Http\Controllers\Admin\AccountLockController.php
dir config\security.php
dir resources\views\admin\account-locks\index.blade.php
```

**Expected:** All files found ✅

### Step 2: Test CLI Command Help

```bash
php artisan auth:unlock-account --help
```

**Expected Output:**
```
Description:
  Unlock a user account by email address

Usage:
  auth:unlock-account <email> [options]

Arguments:
  email                 The user email to unlock

Options:
  --all-ips            Unlock from all IP addresses
  -h, --help           Display this help message
```

### Step 3: Check Configuration

```bash
php artisan config:get security
```

**Expected Output:**
```
[
  "rate_limiting" => [
    "max_attempts" => 5,
    "lockout_duration" => 60,
    "enabled" => true
  ],
  "logging" => [...]
]
```

**Status:** ✅ Installation verified

---

## Phase 2: Test Rate Limiting (Lockout Creation) 🔒

### Step 1: Prepare Test Account

```bash
# Create test user if needed
php artisan tinker
>>> $user = User::create([
...   'email' => 'lockout-test@example.com',
...   'password' => bcrypt('correct-password'),
...   'name' => 'Lockout Test User'
... ]);
>>> exit

# Or use existing user
```

### Step 2: Trigger Lockout

1. **Open browser** → Go to login page: `http://yourapp.com/login`
2. **Enter email:** `lockout-test@example.com`
3. **Enter wrong password** (e.g., `wrong1`)
4. **Click Login** → Get "Invalid credentials" ❌
5. **Repeat steps 3-4** → Total 4 more times (5 attempts total)
6. **Attempt 5:** Get message:
   ```
   ⚠️ Too many login attempts. Please try again in 60 minutes.
   ```

**Verification:**
```bash
# Check database for lock entries
php artisan tinker
>>> use Illuminate\Support\Facades\DB;
>>> DB::table('cache')->where('key', 'LIKE', '%throttle%')->get();
```

**Expected:** Cache entry with key like `throttle:lockout-test@example.com|192.168.1.x`

**Status:** ✅ Lockout triggered successfully

---

## Phase 3: Test Method 1 - Automatic Unlock ⏱️

### Step 1: Wait or Modify Lockout Duration

**Option A (Recommended for testing):** Change duration to 1 minute

```bash
# Via .env
SECURITY_LOCKOUT_DURATION=1

# Clear cache
php artisan config:clear
```

Then trigger lockout again with fresh user/IP.

**Option B:** Wait 60 minutes

### Step 2: Verify Auto-Unlock

After duration passes:
1. **Open login page** again
2. **Try correct password** on same email
3. **Should login successfully** ✅

**Verification in database:**
```bash
php artisan tinker
>>> DB::table('cache')->where('key', 'LIKE', '%lockout-test%')->first();
# Should be NULL after duration expires
>>> exit
```

**Status:** ✅ Auto-unlock works

---

## Phase 4: Test Method 2 - CLI Command 🔧

### Step 1: Create New Lockout

Follow Phase 2 steps (trigger lockout again with fresh test user)

### Step 2: Test Interactive Unlock

```bash
php artisan auth:unlock-account lockout-test@example.com
```

**Expected Interaction:**
```
📧 User found: lockout-test@example.com

🔍 Locked IPs:
  1) 192.168.1.100
  2) 203.0.113.45

? Select IP to unlock (or 0 for all):
```

**Enter:** `1`

**Expected Output:**
```
✅ Account lock cleared for 'lockout-test@example.com' from IP '192.168.1.100'
📝 Logged to activity_logs

? Unlock another? (yes/no): no
```

### Step 3: Test Bulk Unlock

```bash
php artisan auth:unlock-account lockout-test@example.com --all-ips
```

**Expected Output:**
```
✅ All account locks cleared for 'lockout-test@example.com'
📝 Activity logged
```

### Step 4: Verify Unlock

Try login again:
```bash
# Try login with correct password
# Should succeed ✅
```

**Status:** ✅ CLI unlock works

---

## Phase 5: Test Method 3 - Admin Dashboard 📊

### Step 1: Access Dashboard

1. **Login as admin** (ensure you have `access-admin` permission)
2. **Open admin menu**
3. **Click "Account Locks"** or navigate to: `http://yourapp.com/admin/account-locks`

**Expected:** Dashboard loads with:
- ✅ Locked accounts table
- ✅ Settings form
- ✅ System info card

### Step 2: Create Test Lockout

Trigger lockout on fresh test user (different IP if possible)

### Step 3: View Locked Accounts

**On dashboard:**
- Should see new locked account in table
- Email, IP, time remaining visible
- Expiration time shown

### Step 4: Unlock Specific IP

1. **Find locked account row**
2. **Click "Unlock" button**
3. **Confirmation appears**
4. **Click "Confirm"**

**Expected:**
```
✅ Account lock cleared
🔄 Page refreshes
⚠️ Account disappears from locked list
```

### Step 5: Test Bulk Unlock All

1. **Create 2-3 test lockouts** (different users/IPs)
2. **On dashboard, click "Unlock All" button**
3. **Confirmation dialog appears:**
   ```
   ⚠️ This will unlock ALL accounts. Continue?
   [Cancel] [Confirm]
   ```
4. **Click "Confirm"**

**Expected:**
```
✅ All locks cleared
🔄 Page refreshes
📭 Locked accounts list is empty
```

**Status:** ✅ Admin dashboard works

---

## Phase 6: Test Method 4 - Email Unlock Links 📧

### Step 1: Configure Mail (if not already done)

In `.env`:
```
MAIL_MAILER=log  # For testing (logs to laravel.log)
# or
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

### Step 2: Create Lockout

Trigger lockout with test user

### Step 3: Send Unlock Email

```bash
php artisan tinker
>>> $user = User::where('email', 'lockout-test@example.com')->first();
>>> $user->notify(new App\Notifications\AccountUnlockNotification($user, '192.168.1.100'));
>>> exit
```

### Step 4: Verify Email Sent

**If MAIL_MAILER=log:**
```bash
# Check laravel.log
tail -f storage/logs/laravel.log | grep "AccountUnlockNotification"
```

**Expected:** Email message in logs with:
- ✅ Unlock link with signature
- ✅ User email in link
- ✅ IP address in link
- ✅ Signature parameter
- ✅ Link expires in 24h

### Step 5: Click Unlock Link

1. **Find unlock link in email/logs**
2. **Example format:**
   ```
   http://yourapp.com/auth/unlock-account?
   email=lockout-test@example.com&
   ip=192.168.1.100&
   signature=...
   ```
3. **Click or open in browser**

**Expected:**
```
✅ Your account has been unlocked
🔄 Redirects to login page
📝 Success message displayed
```

### Step 6: Verify Can Login

1. **On login page** (after redirect)
2. **Enter correct credentials**
3. **Should login successfully** ✅

**Status:** ✅ Email unlock works

---

## Phase 7: Test Configuration Changes ⚙️

### Step 1: Change Max Attempts via Dashboard

1. **Go to `/admin/account-locks`**
2. **Scroll to "Settings"**
3. **Change "Max Login Attempts" to 3**
4. **Click "Save Settings"**

**Expected:**
```
✅ Settings updated successfully
```

### Step 2: Test New Limit

1. **Go to login**
2. **Try wrong password 3 times**
3. **On 3rd attempt, get locked:**
   ```
   ⚠️ Too many login attempts...
   ```

**Verification:**
```bash
php artisan config:get security.rate_limiting.max_attempts
# Should return: 3
```

### Step 3: Change Lockout Duration

1. **Go to `/admin/account-locks`**
2. **Change "Lockout Duration" to 5**
3. **Click "Save Settings"**

**Expected:**
```
✅ Settings updated successfully
```

### Step 4: Test New Duration

1. **Trigger new lockout** (3 wrong attempts)
2. **Wait 5 minutes**
3. **Should be able to login** ✅

**Status:** ✅ Configuration changes work

---

## Phase 8: Test Disable/Enable Features 🎛️

### Step 1: Disable Rate Limiting

1. **Go to `/admin/account-locks`**
2. **Uncheck "Enable Rate Limiting"**
3. **Click "Save Settings"**

### Step 2: Test Disabled State

1. **Try unlimited wrong passwords**
2. **Should never get locked** ✅

### Step 3: Re-enable Rate Limiting

1. **Check "Enable Rate Limiting"**
2. **Click "Save Settings"**

### Step 4: Verify Re-enabled

1. **Try 5 wrong passwords**
2. **Should get locked again** ✅

**Status:** ✅ Feature toggles work

---

## Phase 9: Verify Activity Logging 📋

### Step 1: Check Log File

```bash
tail -f storage/logs/laravel.log | grep -i "account"
```

**Should see entries like:**
```
[2026-01-31 10:30:00] laravel.INFO: Account locked for email: john@example.com (IP: 192.168.1.100)
[2026-01-31 10:35:00] laravel.INFO: Account unlocked: john@example.com (Method: cli, By: admin)
[2026-01-31 10:40:00] laravel.INFO: Account unlocked: john@example.com (Method: email)
```

### Step 2: Check Database

```bash
php artisan tinker
>>> use App\Models\ActivityLog;
>>> ActivityLog::where('action', 'account_unlocked')->latest()->limit(5)->get();
```

**Expected:** Entries for all unlock actions

**Status:** ✅ Logging works

---

## Phase 10: Security Testing 🔐

### Test 1: Email Link Expiry

```bash
# Get old unlock link (older than 24 hours)
# Try to use it

# Expected: 404 or "Invalid signature" error ✅
```

### Test 2: Tampered Signature

```bash
# Take valid unlock link
# Change signature parameter
# Try to use it

# Expected: 404 or signature validation error ✅
```

### Test 3: Unauthorized Dashboard Access

```bash
# Logout from admin account
# Try to access /admin/account-locks

# Expected: Redirected to login ✅
```

### Test 4: Wrong User Email

```bash
# Create unlock link for user1
# Try to use for user2

# Expected: Error or no unlock ✅
```

**Status:** ✅ Security verified

---

## 📊 Test Results Summary

Create a checklist to track completion:

```
Phase 1: Installation
  ☐ CLI command found
  ☐ Controller found
  ☐ Config file found
  ☐ Views found

Phase 2: Rate Limiting
  ☐ Lockout triggered after 5 attempts
  ☐ Lock stored in database
  ☐ Error message displayed

Phase 3: Auto-Unlock
  ☐ Can login after duration passes
  ☐ Cache entry cleared

Phase 4: CLI Unlock
  ☐ Interactive unlock works
  ☐ Bulk unlock works
  ☐ Can login after unlock

Phase 5: Admin Dashboard
  ☐ Dashboard loads
  ☐ Locked accounts visible
  ☐ Per-IP unlock works
  ☐ Bulk unlock works

Phase 6: Email Unlock
  ☐ Email sent successfully
  ☐ Link opens
  ☐ Account unlocked
  ☐ Can login

Phase 7: Configuration
  ☐ Max attempts changeable
  ☐ Duration changeable
  ☐ New settings apply

Phase 8: Feature Toggles
  ☐ Rate limiting disabled
  ☐ Rate limiting re-enabled

Phase 9: Logging
  ☐ Log file entries present
  ☐ Database entries present

Phase 10: Security
  ☐ Expired links fail
  ☐ Tampered signatures fail
  ☐ Unauthorized access blocked
  ☐ Wrong user access blocked

OVERALL STATUS: ✅ ALL TESTS PASSED
```

---

## 🚀 Production Deployment

When ready for production:

1. ✅ Run all tests above
2. ✅ Configure real email driver (.env)
3. ✅ Set appropriate limits for your security policy
4. ✅ Test with real user accounts
5. ✅ Monitor logs for issues
6. ✅ Train admins on dashboard usage
7. ✅ Document for your team

---

## 💡 Troubleshooting During Testing

| Issue | Solution |
|-------|----------|
| Command not found | `composer dump-autoload` |
| Dashboard 404 | `php artisan route:clear` |
| Settings not saving | `php artisan config:clear` |
| Email not sending | Check .env MAIL_* settings |
| Link not working | Check APP_KEY is set |

---

## 📞 Questions?

- **Full Guide:** See `ACCOUNT_LOCKOUT_COMPLETE_GUIDE.md`
- **Quick Ref:** See `ACCOUNT_LOCKOUT_QUICK_REFERENCE.md`
- **Logs:** Check `storage/logs/laravel.log`
- **Database:** Query `cache` table for locks

**Happy testing! 🎉**

