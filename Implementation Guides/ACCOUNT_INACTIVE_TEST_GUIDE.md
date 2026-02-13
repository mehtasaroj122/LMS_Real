# 🧪 Quick Test Guide - Account Inactive Feature

## 60-Second Test

### Option 1: Using Tinker (Recommended)

```bash
# 1. Open tinker
php artisan tinker

# 2. Create inactive test user
App\Models\User::create([
    'name' => 'Test Inactive',
    'email' => 'inactive@test.local',
    'password' => Hash::make('password'),
    'role' => 'student',
    'status' => 'inactive'
]);

# 3. Exit tinker
exit
```

### Then Test Login:

1. Go to: `http://localhost:8000/login`
2. Email: `inactive@test.local`
3. Password: `password`
4. Click Login

**Result**: Should see Account Inactive page ✅

---

## Full Test Suite

### Test 1: Create Test Users

```php
# Inactive user
App\Models\User::create([
    'name' => 'Inactive Student',
    'email' => 'inactive@test.local',
    'password' => Hash::make('password123'),
    'role' => 'student',
    'status' => 'inactive'
]);

# Active user
App\Models\User::create([
    'name' => 'Active Student',
    'email' => 'active@test.local',
    'password' => Hash::make('password123'),
    'role' => 'student',
    'status' => 'active'
]);
```

---

### Test 2: Inactive User Login (Should FAIL)

| Field | Value |
|-------|-------|
| Email | `inactive@test.local` |
| Password | `password123` |
| Expected | Account Inactive page |
| Status | ✅ Should redirect to `/account-inactive` |

**Verify**:
- [ ] Not logged in
- [ ] Redirected to `/account-inactive`
- [ ] Page shows error message
- [ ] Contact info is visible
- [ ] Back to Login button works
- [ ] Create New Account button works

---

### Test 3: Active User Login (Should SUCCEED)

| Field | Value |
|-------|-------|
| Email | `active@test.local` |
| Password | `password123` |
| Expected | Student Dashboard |
| Status | ✅ Should redirect to `/student/dashboard` |

**Verify**:
- [ ] Successfully logged in
- [ ] Redirected to dashboard
- [ ] User menu shows name
- [ ] Can access system
- [ ] Can logout

---

### Test 4: Inactive Page Accessibility

**Test**: Can anyone access `/account-inactive`?

```
GET http://localhost:8000/account-inactive
```

**Expected Result**: ✅ Page displays (no auth required)

---

### Test 5: Admin Deactivation

**Test**: Admin deactivates an active user

1. Go to Admin Panel
2. Find "Active Student" in users
3. Click Deactivate
4. Logout
5. Try logging in as that user

**Expected Result**: ✅ Should now show Account Inactive page

---

### Test 6: Admin Reactivation

**Test**: Admin reactivates an inactive user

1. Go to Admin Panel
2. Find "Inactive Student" in users
3. Click Activate
4. Logout
5. Try logging in as that user

**Expected Result**: ✅ Should now login successfully

---

### Test 7: Mobile Responsiveness

**Test**: On mobile device or mobile view

1. Open `/account-inactive` on phone
2. Verify it displays properly
3. Try clicking buttons
4. Check text is readable

**Expected Result**: ✅ Page adapts to screen size

---

### Test 8: Contact Links

**Test**: Click contact links on inactive page

- [ ] Email link: `mailto:admin@librarysystem.com`
- [ ] Phone number displayed: `+1 (555) 123-4567`
- [ ] Office hours displayed: `Mon - Fri: 9 AM - 5 PM`

---

## Browser Console Errors Check

**Open DevTools** (F12) and check:

1. Go to `/account-inactive`
2. Open Console tab
3. Look for errors
4. Should see: **0 errors**

**Expected**: ✅ No JavaScript errors

---

## Database Verification

**Check user status field**:

```php
# In tinker
App\Models\User::where('email', 'inactive@test.local')->first();

# Should show: status => "inactive"
```

---

## Quick Reference - Test URLs

| Test | URL | Expected |
|------|-----|----------|
| Login Page | `/login` | Login form |
| Inactive Account Page | `/account-inactive` | Professional error page |
| Dashboard (Active) | `/admin/dashboard` | Redirects if logged in |
| Dashboard (Student) | `/student/dashboard` | Redirects if logged in |

---

## Troubleshooting Quick Fixes

### Issue: Page not found (404)

**Solution**: 
```bash
php artisan route:clear
php artisan config:clear
```

### Issue: Styling looks wrong

**Solution**:
```bash
npm run dev
# or
npm run build
```

### Issue: Database error

**Solution**:
```bash
php artisan migrate
```

### Issue: Can login with inactive account

**Check**: `users.status` column exists and = `'inactive'`

---

## Rollback (If Needed)

If you need to revert the changes:

1. Remove from `LoginRequest.php` (lines 44-48)
2. Remove try-catch from `AuthenticatedSessionController.php` (lines 28-36)
3. Remove route from `auth.php` (lines 36-39)
4. Delete `account-inactive.blade.php`

---

## Success Indicators

✅ **All should be TRUE**:
- [ ] Inactive users cannot login
- [ ] Active users can login
- [ ] Professional page displays
- [ ] Contact info is visible
- [ ] Navigation buttons work
- [ ] Page is responsive
- [ ] No console errors
- [ ] Admin functions work

---

## Test Data Script

Save as `test_inactive_accounts.php` in project root:

```php
<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Create test users
$inactive = User::firstOrCreate(
    ['email' => 'test_inactive@local'],
    [
        'name' => 'Test Inactive',
        'password' => Hash::make('password123'),
        'role' => 'student',
        'status' => 'inactive'
    ]
);

$active = User::firstOrCreate(
    ['email' => 'test_active@local'],
    [
        'name' => 'Test Active',
        'password' => Hash::make('password123'),
        'role' => 'student',
        'status' => 'active'
    ]
);

echo "✅ Test users created:\n";
echo "  Inactive: {$inactive->email} (status: {$inactive->status})\n";
echo "  Active: {$active->email} (status: {$active->status})\n";
echo "\nTest account logins:\n";
echo "  Inactive - should show Account Inactive page\n";
echo "  Active - should login to dashboard\n";
```

Run with:
```bash
php test_inactive_accounts.php
```

---

## Summary Checklist

Before considering testing complete:

- [ ] Read this guide
- [ ] Created test users
- [ ] Tested inactive login (failed as expected)
- [ ] Tested active login (succeeded as expected)
- [ ] Tested page navigation
- [ ] Checked mobile responsiveness
- [ ] Verified no console errors
- [ ] Tested admin deactivate/activate
- [ ] Reviewed all documentation

---

## Questions?

1. Check `ACCOUNT_INACTIVE_VISUAL_GUIDE.md` for flowcharts
2. Check `ACCOUNT_INACTIVE_IMPLEMENTATION_GUIDE.md` for details
3. Review the code in the modified files
4. Test each scenario in this guide

---

**Time to Complete**: ~15 minutes  
**Difficulty Level**: Easy  
**Status**: ✅ Ready to Test
