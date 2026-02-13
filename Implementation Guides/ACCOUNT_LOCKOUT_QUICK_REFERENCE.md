# Account Lockout Management - Quick Reference

## 🎯 At a Glance

Your account lockout system has **4 unlock methods**:

| Method | Who | Where | Time |
|--------|-----|-------|------|
| **Automatic** | User | N/A | 60 min |
| **Admin CLI** | Admin | Terminal | Instant |
| **Admin Dashboard** | Admin | Web UI | Instant |
| **Email Link** | User | Email | 24 hours |

---

## 🔧 Quick Commands

```bash
# Unlock one user (interactive)
php artisan auth:unlock-account john@example.com

# Unlock one user (all IPs)
php artisan auth:unlock-account john@example.com --all-ips

# View current settings
php artisan config:get security
```

---

## 📊 Admin Dashboard

**URL:** `http://yourapp.com/admin/account-locks`

**Features:**
- 👁️ See all locked accounts
- 🔓 Click to unlock any account
- ⚡ Bulk unlock all at once
- ⚙️ Configure settings in real-time
- 📋 View IP addresses and expiration times

---

## 🔧 Configuration

| Setting | Default | Min | Max | Where |
|---------|---------|-----|-----|-------|
| Max Attempts | 5 | 1 | 20 | Config or Dashboard |
| Duration (min) | 60 | 1 | 1440 | Config or Dashboard |

### Change via Dashboard:
1. Go to `/admin/account-locks`
2. Scroll to "Settings"
3. Update values
4. Click Save

### Change via .env:
```
SECURITY_MAX_LOGIN_ATTEMPTS=3
SECURITY_LOCKOUT_DURATION=120
```

Then: `php artisan config:clear`

---

## 📧 Email Unlock

**How it works:**
1. Account gets locked
2. Send unlock email to user
3. User clicks link (expires 24h)
4. Account unlocked automatically
5. User can login

**Link Format:**
```
/auth/unlock-account?email=john@example.com&ip=192.168.1.1&signature=...
```

---

## 🚨 Important Files

### Created Files:
- ✅ `config/security.php` - Rate limit settings
- ✅ `app/Console/Commands/UnlockAccountCommand.php` - CLI command
- ✅ `app/Http/Controllers/Admin/AccountLockController.php` - Admin logic
- ✅ `app/Http/Controllers/Auth/AccountUnlockController.php` - Email unlock
- ✅ `app/Notifications/AccountUnlockNotification.php` - Email template
- ✅ `resources/views/admin/account-locks/index.blade.php` - Dashboard UI

### Modified Files:
- ✅ `app/Http/Requests/Auth/LoginRequest.php` - Use config settings
- ✅ `routes/web.php` - Added admin routes
- ✅ `routes/auth.php` - Added email route

---

## 🧪 Testing Lockout

```bash
# 1. Try wrong password 5 times on login form
# Result: "Too many login attempts. Try again in X minutes."

# 2. Verify locked in database:
SELECT * FROM cache WHERE `key` LIKE '%throttle%';

# 3. Unlock via CLI:
php artisan auth:unlock-account test@example.com --all-ips

# 4. Try login again - should work!
```

---

## 📝 Logged Events

All actions logged to `storage/logs/laravel.log`:

```
[2026-01-31 10:30:00] laravel.INFO: Account locked: john@example.com (IP: 192.168.1.100)
[2026-01-31 10:35:00] laravel.INFO: Account unlocked: john@example.com (By: Admin UI)
```

---

## 🔐 Security

✅ Signed email links (24-hour expiry)
✅ Rate limiting enabled/configurable
✅ IP-based tracking
✅ Admin-only dashboard
✅ Full audit logging
✅ No credentials exposed

---

## 💡 Common Scenarios

### Scenario 1: User Locked at 9 AM
**Auto-unlock:** 10 AM (60 min default)

### Scenario 2: User Locked, Needs Immediate Access
**Admin:** Go to `/admin/account-locks` → Click Unlock

### Scenario 3: Too Many Attacks from Specific IP
**Admin:** Change max attempts to 3 in dashboard

### Scenario 4: User Forgot Password + Locked
**Solution:** 
1. Admin sends password reset email
2. Admin unlocks account
3. User resets password and logs in

---

## 🆘 Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| Command not found | Run `composer dump-autoload` |
| Dashboard empty | Create lockout by trying 5 wrong passwords |
| Settings not saving | Run `php artisan config:clear` |
| Email link expired | Send new email (link expires in 24h) |
| Can't access dashboard | Ensure user has `access-admin` permission |

---

## 📞 Need Help?

1. **See full guide:** Open `ACCOUNT_LOCKOUT_COMPLETE_GUIDE.md`
2. **Check logs:** `storage/logs/laravel.log`
3. **Test command:** `php artisan auth:unlock-account --help`
4. **View all commands:** `php artisan list | grep auth`

---

## ✅ Implementation Status

| Item | Status |
|------|--------|
| Rate limiting | ✅ Working |
| Configurable limits | ✅ Working |
| Admin command | ✅ Working |
| Admin dashboard | ✅ Working |
| Email unlocks | ✅ Ready |
| Audit logging | ✅ Working |
| Security | ✅ Verified |

**System is production-ready! 🚀**

