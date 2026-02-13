# Account Lockout System - FAQs & Troubleshooting

## ❓ Frequently Asked Questions

### General Questions

#### Q1: What happens when a user gets locked out?

**A:** When a user fails login 5 times (configurable):
1. Their account is locked
2. They see message: "Too many login attempts. Try again in 60 minutes."
3. Lock stored in database cache table
4. After 60 minutes (configurable), automatically unlocks
5. Or admin can unlock immediately via 3 methods

---

#### Q2: What are the 4 unlock methods?

**A:**
1. **Automatic** - Wait 60 minutes (locked time period)
2. **Admin CLI** - `php artisan auth:unlock-account user@example.com`
3. **Admin Dashboard** - Go to `/admin/account-locks` and click Unlock
4. **Email Link** - User clicks link in unlock email

---

#### Q3: How secure are the email unlock links?

**A:**
- ✅ Cryptographically signed with your APP_KEY
- ✅ Impossible to forge or tamper with
- ✅ Expires after 24 hours
- ✅ Specific to email + IP combination
- ✅ Laravel's built-in signed routes mechanism

---

#### Q4: Can I customize the lockout duration?

**A:**
Yes! Two methods:

**Via Dashboard:**
1. Go to `/admin/account-locks`
2. Find "Lockout Duration (minutes)"
3. Change from 60 to desired value (1-1440)
4. Click Save

**Via Environment Variable:**
```
SECURITY_LOCKOUT_DURATION=120
php artisan config:clear
```

---

#### Q5: Can I change the max failed attempts?

**A:**
Yes! Two methods:

**Via Dashboard:**
1. Go to `/admin/account-locks`
2. Find "Max Login Attempts"
3. Change from 5 to desired value (1-20)
4. Click Save

**Via Environment Variable:**
```
SECURITY_MAX_LOGIN_ATTEMPTS=3
php artisan config:clear
```

---

#### Q6: Who can access the admin dashboard?

**A:**
Only users with `access-admin` permission/gate. Typically admins/superusers.

To check a user:
```bash
php artisan tinker
>>> $user = User::find(1);
>>> $user->hasPermissionTo('access-admin');
# Should return true for admins
```

---

#### Q7: Are all unlock events logged?

**A:**
Yes! Multiple logging methods:

1. **Log File:** `storage/logs/laravel.log`
2. **Activity Logs:** `activity_logs` table (if configured)
3. **Example Entry:**
   ```
   [2026-01-31 10:35:00] laravel.INFO: 
   Account unlocked: john@example.com 
   (IP: 192.168.1.100, Method: cli, By: admin)
   ```

---

#### Q8: What if I disable rate limiting?

**A:**
Users can attempt unlimited logins without locking.

**To disable:**
1. Go to `/admin/account-locks`
2. Uncheck "Enable Rate Limiting"
3. Click Save

**Security Note:** Not recommended for production. Only for testing.

---

#### Q9: Can I test the lockout system?

**A:**
Yes! Easy testing:

1. Go to login page
2. Enter any email
3. Enter wrong password
4. Click Login
5. Repeat 5 times
6. On 5th attempt, get locked

Then test unlock methods.

---

#### Q10: What's the difference between per-IP and all-IPs unlock?

**A:**

**Per-IP Unlock:**
- Unlocks user only from specific IP
- Example: `192.168.1.100`
- User locked from different IP still locked

**All-IPs Unlock:**
- Unlocks user from ALL IPs
- User can login from anywhere
- Clears all lock entries

---

### Troubleshooting

## 🆘 Common Issues & Solutions

### Issue 1: "Command not found: auth:unlock-account"

**Error Message:**
```
Command "auth:unlock-account" is not defined
```

**Cause:** Autoloader hasn't discovered the command

**Solution:**
```bash
composer dump-autoload
php artisan cache:clear
php artisan auth:unlock-account --help
```

---

### Issue 2: Admin Dashboard Returns 404

**Error:** `/admin/account-locks` shows 404

**Cause:** Routes not reloaded

**Solution:**
```bash
php artisan route:clear
php artisan config:clear
# Then refresh page
```

---

### Issue 3: Settings Not Saving

**Problem:** Change max attempts on dashboard, but no change

**Cause:** Config not cleared

**Solution:**
```bash
php artisan config:clear
# Or restart browser session
```

---

### Issue 4: Email Links Not Working

**Problem:** Click email link, get 404 or error

**Cause:** Multiple possible issues

**Solutions:**

**Check 1: APP_KEY is set**
```bash
grep APP_KEY .env
# Should show: APP_KEY=base64:xxxxx...
```

**Check 2: Link not expired (>24 hours)**
```bash
# Links expire in 24 hours
# Try sending new email
```

**Check 3: CACHE_DRIVER is database**
```bash
grep CACHE_DRIVER .env
# Should show: CACHE_DRIVER=database
```

**Check 4: Signature is valid**
```bash
# Don't modify URL
# Use exact link from email
```

---

### Issue 5: Dashboard Shows No Locked Accounts

**Problem:** Dashboard shows empty table

**Cause:** No accounts currently locked

**Solution:**
1. Try logging in with wrong password 5 times
2. Wait 10-30 seconds
3. Refresh dashboard
4. Should show locked account

---

### Issue 6: CLI Command Hangs or Times Out

**Problem:** `php artisan auth:unlock-account` hangs

**Cause:** Usually database connection issue

**Solution:**
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
# Should not error

# Or try with timeout
timeout 10 php artisan auth:unlock-account user@example.com
```

---

### Issue 7: Email Not Sending

**Problem:** Unlock email doesn't arrive

**Cause:** Mail configuration issue

**Solution:**

**For Testing:**
```bash
# .env
MAIL_MAILER=log
# Emails logged to storage/logs/laravel.log
```

**For Production:**
```bash
# .env
MAIL_MAILER=smtp
MAIL_HOST=your.smtp.server
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@yourapp.com
MAIL_FROM_NAME="Library Management"
```

---

### Issue 8: Permission Denied on Admin Dashboard

**Problem:** Get "403 Unauthorized" on `/admin/account-locks`

**Cause:** User doesn't have admin permission

**Solution:**
1. Login as superadmin/admin user
2. Or grant `access-admin` permission to user:

```bash
php artisan tinker
>>> $user = User::find(2);
>>> $user->givePermissionTo('access-admin');
>>> exit
```

---

### Issue 9: Settings Reset After Restart

**Problem:** Changed settings, but they reset

**Cause:** Not saved to .env, using session defaults

**Solution:**
1. After changing settings on dashboard
2. Check .env file:
```bash
grep SECURITY_ .env
```
Should show:
```
SECURITY_MAX_LOGIN_ATTEMPTS=3
SECURITY_LOCKOUT_DURATION=120
```

If not there, settings didn't save. Try again.

---

### Issue 10: Multiple Users Locked from Same IP

**Problem:** One IP trying login for many emails, locking all

**Cause:** Rate limiting per IP+email

**Solution:**
1. Investigate which user account is being attacked
2. Unlock legitimate users via dashboard
3. Block malicious IP in firewall (if applicable)
4. Consider increasing lockout duration

---

## 🔧 Advanced Troubleshooting

### Check Locked Accounts Directly

```bash
php artisan tinker

# View all locks
>>> use Illuminate\Support\Facades\DB;
>>> DB::table('cache')
    ->where('key', 'LIKE', 'throttle:%')
    ->get();

# View specific user locks
>>> DB::table('cache')
    ->where('key', 'LIKE', 'throttle:john@example.com%')
    ->get();

# Count all locks
>>> DB::table('cache')
    ->where('key', 'LIKE', 'throttle:%')
    ->count();

# Delete specific lock
>>> DB::table('cache')
    ->where('key', 'throttle:john@example.com|192.168.1.100')
    ->delete();

>>> exit
```

---

### Check Configuration

```bash
# View all security config
php artisan config:get security

# View specific setting
php artisan config:get security.rate_limiting.max_attempts

# View from env
php artisan config:get security.rate_limiting.enabled
```

---

### Clear All Locks Manually

```bash
php artisan tinker

>>> use Illuminate\Support\Facades\DB;
>>> DB::table('cache')
    ->where('key', 'LIKE', 'throttle:%')
    ->delete();

>>> exit

# Verify cleared
>>> DB::table('cache')
    ->where('key', 'LIKE', 'throttle:%')
    ->count();
# Should return: 0
```

---

### Test Email Sending

```bash
php artisan tinker

# Send test email
>>> use App\Models\User;
>>> use App\Notifications\AccountUnlockNotification;
>>> $user = User::find(1);
>>> $user->notify(new AccountUnlockNotification($user, '192.168.1.100'));

# For MAIL_MAILER=log, check logs
# tail -f storage/logs/laravel.log | grep AccountUnlock
```

---

## 📊 Performance Issues

### Issue: Admin Dashboard Slow

**Problem:** `/admin/account-locks` takes >5 seconds to load

**Cause:** Too many locked accounts

**Solution:**
```bash
# Check lock count
php artisan tinker
>>> DB::table('cache')->where('key', 'LIKE', 'throttle:%')->count();

# If >1000, consider cleanup
>>> DB::table('cache')
    ->where('key', 'LIKE', 'throttle:%')
    ->where('expiration', '<', time())
    ->delete();

>>> exit
```

---

### Issue: CLI Command Slow

**Problem:** `php artisan auth:unlock-account` takes >5 seconds

**Cause:** Usually database query issue

**Solution:**
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getDatabaseName();

# Test query speed
>>> use Illuminate\Support\Facades\DB;
>>> $start = microtime(true);
>>> DB::table('cache')
    ->where('key', 'LIKE', 'throttle:user@example.com%')
    ->get();
>>> echo (microtime(true) - $start) . ' seconds';
```

---

## 🔐 Security Issues

### Issue: Suspicious Unlock Activity

**Problem:** Seeing unlocks from IPs you don't recognize

**Cause:** Could be security issue

**Solution:**
1. Check logs: `tail -f storage/logs/laravel.log | grep unlocked`
2. Review who has admin access
3. Check for unauthorized logins
4. Consider resetting credentials
5. Enable 2FA for admins

---

### Issue: Brute Force Attack

**Problem:** Seeing many lockouts from same IP

**Cause:** Active brute force attempt

**Solution:**
1. Note attacking IP
2. Block it in firewall: `iptables -I INPUT -s 192.168.1.100 -j DROP`
3. Unlock legitimate users: `php artisan auth:unlock-account user@example.com --all-ips`
4. Monitor logs for more attacks
5. Consider IP rate limiting in firewall

---

## 📝 Debug Mode

### Enable Debug Logging

Add to .env:
```
APP_DEBUG=true
LOG_LEVEL=debug
SECURITY_LOG_FAILED_ATTEMPTS=true
SECURITY_LOG_LOCKOUTS=true
SECURITY_LOG_UNLOCKS=true
```

Then check logs:
```bash
tail -f storage/logs/laravel.log | grep -i "security\|lock\|unlock"
```

---

### Database Query Logging

Enable query logging:
```bash
php artisan tinker

>>> \DB::listen(function ($query) {
...   echo $query->sql . "\n";
... });

# Run commands
# Exit with Ctrl+C
>>> exit
```

---

## 🆘 Getting Help

### When to Check Each Guide

| Issue | Check This |
|-------|-----------|
| General questions | Quick Reference |
| Feature not working | Testing Guide |
| API details needed | API Documentation |
| Step-by-step help | Complete Guide |
| Setup issues | Implementation Checklist |
| Deployment | Complete Guide |
| Code integration | API Documentation |

---

## ✅ Verification Checklist

### Everything Working?

- [ ] CLI command runs: `php artisan auth:unlock-account --help`
- [ ] Dashboard accessible: `/admin/account-locks`
- [ ] Can create lockout (5 wrong passwords)
- [ ] Can unlock via CLI
- [ ] Can unlock via dashboard
- [ ] Can unlock via email link
- [ ] Settings save correctly
- [ ] All events logged
- [ ] No console errors
- [ ] No database errors

If all checked, **system is working perfectly!** ✅

---

## 📞 Support Resources

- **Quick Answer:** See FAQ above
- **Specific Issue:** Search troubleshooting section
- **General Help:** Read Complete Guide
- **Testing:** Follow Testing Guide
- **Development:** Review API Documentation
- **Deployment:** Check Implementation Checklist

---

## 💡 Pro Tips

### Tip 1: Batch Unlock Multiple Users

```bash
# Create file: unlock_users.txt
user1@example.com
user2@example.com
user3@example.com

# Then:
while read email; do
  php artisan auth:unlock-account "$email" --all-ips
done < unlock_users.txt
```

---

### Tip 2: Monitor Locked Accounts

```bash
# Check every minute
watch -n 60 'php artisan tinker --execute "echo Illuminate\Support\Facades\DB::table(\"cache\")->where(\"key\", \"LIKE\", \"throttle:%\")->count(); exit"'
```

---

### Tip 3: Auto-Report Locked Accounts

```bash
# Add to crontab
* * * * * php artisan auth:report-locked-accounts

# This would send daily report (if you implement it)
```

---

### Tip 4: Test Different Settings

```bash
# Temporarily change for testing
SECURITY_MAX_LOGIN_ATTEMPTS=2
SECURITY_LOCKOUT_DURATION=5

# Test with 2 attempts
# Test with 5 minute unlock
# Change back when done
```

---

## 🎓 Learning Path

1. **Start Here:** Read `ACCOUNT_LOCKOUT_QUICK_REFERENCE.md`
2. **Understand:** Read `ACCOUNT_LOCKOUT_COMPLETE_GUIDE.md`
3. **Practice:** Follow `ACCOUNT_LOCKOUT_TESTING_GUIDE.md`
4. **Develop:** Study `ACCOUNT_LOCKOUT_API_DOCUMENTATION.md`
5. **Deploy:** Use `ACCOUNT_LOCKOUT_IMPLEMENTATION_CHECKLIST.md`

---

**Got another question? Check the guides or troubleshooting section above!** 🚀

