# OTP Verification System - Testing Guide

## ✅ Setup Status

- ✅ **Database Migration:** Successfully applied (added otp, otp_expires_at, is_verified columns)
- ✅ **Mail Configuration:** Already configured in .env
  - MAIL_MAILER: smtp
  - MAIL_HOST: smtp.gmail.com
  - MAIL_PORT: 587
  - MAIL_USERNAME: mehtasaroj315@gmail.com
  - MAIL_ENCRYPTION: tls
- ✅ **Laravel Server:** Running on http://localhost:8000

## 🧪 Test Flow: Register → Enter OTP → Auto-Login

### Test Case 1: Successful Registration and OTP Delivery

**Steps:**
1. Navigate to: `http://localhost:8000/register`
2. Fill in the registration form:
   - Full Name: `John Doe`
   - Email: `john.doe@example.com` (use a real email or Gmail account you can access)
   - Password: `Password123`
   - Confirm Password: `Password123`
3. Click "Create Account"

**Expected Behavior:**
- [ ] Form submits without errors
- [ ] Redirected to OTP verification page
- [ ] Page displays message: "OTP has been sent to your email"
- [ ] Email address is visible on the OTP page
- [ ] User record created in database with `is_verified = 0`
- [ ] Student record created with auto-generated enrollment_id (STU-000001, etc.)
- [ ] OTP email received (check inbox/spam folder)

**Database Verification:**
```sql
SELECT id, name, email, role, is_verified, otp, otp_expires_at FROM users 
WHERE email = 'john.doe@example.com';
```

---

### Test Case 2: OTP Entry and Verification

**Prerequisite:** Complete Test Case 1 first

**Steps:**
1. Check email for 6-digit OTP code
2. On the OTP verification page, enter the 6 digits:
   - Click on first input field
   - Enter digits one by one (auto-focus moves to next field)
3. Click "Verify OTP"

**Expected Behavior:**
- [ ] OTP digits auto-fill fields
- [ ] Auto-focus moves to next field after each digit
- [ ] "Verify OTP" button shows loading animation
- [ ] After verification: User automatically logged in
- [ ] Redirected to student dashboard
- [ ] User can access student area (not redirected to login)
- [ ] Database updated: `is_verified = 1`, `email_verified_at` populated

**Database Verification:**
```sql
SELECT id, email, is_verified, email_verified_at FROM users 
WHERE email = 'john.doe@example.com';
```

---

### Test Case 3: Wrong OTP Entry

**Steps:**
1. On OTP verification page, enter incorrect digits: `000000`
2. Click "Verify OTP"

**Expected Behavior:**
- [ ] Error message displays: "Invalid OTP"
- [ ] User remains on OTP page
- [ ] User NOT logged in
- [ ] No database changes (is_verified still 0)

---

### Test Case 4: OTP Resend Functionality

**Steps:**
1. On OTP verification page, click "Resend OTP" button
2. Observe the button behavior

**Expected Behavior:**
- [ ] New OTP generated and sent to email
- [ ] "Resend OTP" button becomes disabled
- [ ] Countdown timer displays: "Resend OTP in 60s"
- [ ] Timer counts down from 60 to 0
- [ ] Button becomes enabled after 60 seconds
- [ ] Can click again to resend
- [ ] Email received with new OTP code

---

### Test Case 5: OTP Copy-Paste Feature

**Steps:**
1. Copy OTP from email (all 6 digits: e.g., "123456")
2. On OTP verification page, click first input field
3. Paste the OTP (Ctrl+V)

**Expected Behavior:**
- [ ] All 6 input fields populate automatically
- [ ] Only numeric digits extracted
- [ ] Non-numeric characters ignored
- [ ] User can immediately click "Verify OTP"

---

### Test Case 6: OTP Expiration (Advanced)

**Prerequisites:** Modify database or wait 10 minutes

**Option A: Modify Database (Faster)**
```sql
UPDATE users SET otp_expires_at = NOW() - INTERVAL 1 MINUTE 
WHERE email = 'john.doe@example.com';
```

**Steps:**
1. On OTP verification page with expired OTP
2. Click "Verify OTP"

**Expected Behavior:**
- [ ] Error message: "OTP has expired"
- [ ] User NOT logged in
- [ ] Redirect to registration with error
- [ ] Must re-register to get new OTP

**Option B: Natural Expiration (10 minutes)**
1. Register and note the timestamp
2. Wait 10 minutes and 1 second
3. Try to verify OTP
4. Should see expiration error

---

### Test Case 7: Rate Limiting on Resend

**Steps:**
1. On OTP verification page, click "Resend OTP"
2. Immediately click "Resend OTP" again (before 60s timer completes)

**Expected Behavior:**
- [ ] Second click ignored (button disabled)
- [ ] Only one email sent per 60-second window
- [ ] Timer continues countdown
- [ ] No error messages (graceful handling)

---

### Test Case 8: Responsive Design Testing

**Mobile (480px breakpoint)**
1. Open developer tools (F12)
2. Set device to iPhone/Mobile (375px width)
3. Navigate to OTP page
4. Verify layout

**Expected Behavior:**
- [ ] Form stacked vertically
- [ ] Input fields properly sized
- [ ] Buttons readable and clickable
- [ ] No horizontal scrolling
- [ ] Icon visible and appropriately sized
- [ ] Branding content below form

**Tablet (768px breakpoint)**
1. Set device to iPad/Tablet (768px width)
2. Navigate to OTP page

**Expected Behavior:**
- [ ] Responsive adjustments applied
- [ ] Good spacing and readability
- [ ] Form and branding properly proportioned

---

## 🔍 Testing Checklist

**Registration Flow:**
- [ ] Can navigate to /register
- [ ] Register form validates input
- [ ] Password confirmation works
- [ ] Duplicate emails rejected
- [ ] Successful registration creates User and Student
- [ ] OTP generated as 6 digits
- [ ] OTP email sent successfully
- [ ] Redirects to OTP verification page

**OTP Verification:**
- [ ] OTP page displays correctly
- [ ] Email address shown on page
- [ ] Input fields accept numeric only
- [ ] Auto-focus works between fields
- [ ] Valid OTP verifies successfully
- [ ] Invalid OTP shows error
- [ ] Expired OTP shows error
- [ ] User auto-logged in after verification
- [ ] Redirects to student dashboard

**User Experience:**
- [ ] Loading animations display
- [ ] Error messages are clear
- [ ] Success messages confirm action
- [ ] Timer counts down correctly
- [ ] Paste functionality works
- [ ] Backspace navigation works
- [ ] Responsive on mobile/tablet/desktop

**Database:**
- [ ] User created with role='student'
- [ ] is_verified=0 before OTP verification
- [ ] is_verified=1 after verification
- [ ] email_verified_at populated after verification
- [ ] OTP cleared after verification
- [ ] Student record created
- [ ] enrollment_id auto-generated

---

## 📧 Email Testing

### Email Provider: Gmail

If emails not received:
1. **Check Spam Folder** - Gmail may filter Laravel emails
2. **Enable Less Secure Apps** (if using personal Gmail):
   - Go to: https://myaccount.google.com/security
   - Enable "Less secure app access"
3. **Use App Password** (if 2FA enabled):
   - Generate app-specific password
   - Use in .env MAIL_PASSWORD
4. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Alternative Testing (Mailtrap)

For development testing without real email:
1. Create free account at: https://mailtrap.io
2. Update .env:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_username
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="test@example.com"
   ```
3. Check inbox at mailtrap.io

---

## 🐛 Troubleshooting

### Issue: "Email not received"
- [ ] Check spam/promotions folder
- [ ] Check .env mail configuration
- [ ] Check Laravel logs: `storage/logs/laravel.log`
- [ ] Verify MAIL_FROM_ADDRESS is set
- [ ] Ensure MAIL_PASSWORD is correct (App password for Gmail with 2FA)

### Issue: "OTP verification button not working"
- [ ] Clear browser cache (Ctrl+Shift+Delete)
- [ ] Refresh page
- [ ] Check browser console for JavaScript errors (F12)
- [ ] Verify all 6 OTP fields have digits

### Issue: "User not logged in after OTP verification"
- [ ] Check that `Auth::login($user)` is called in controller
- [ ] Verify SESSION_DRIVER in .env (set to 'database' or 'cookie')
- [ ] Check that users table has correct record
- [ ] Clear Laravel cache: `php artisan cache:clear`

### Issue: "OTP expires too quickly"
- [ ] Check `otp_expires_at` column in database
- [ ] Verify server time is correct
- [ ] Check that `now()->addMinutes(10)` is used in controller

### Issue: "Migration failed"
```bash
# Rollback and try again
php artisan migrate:rollback
php artisan migrate

# Or check migration status
php artisan migrate:status
```

---

## 📊 Success Metrics

✅ **System working correctly if all pass:**
1. User can register successfully
2. OTP email received within 1 minute
3. OTP verification page displays correctly
4. Valid OTP successfully verifies user
5. User auto-logged in and redirected to dashboard
6. User remains logged in on page refresh
7. Invalid OTP rejected with error message
8. Resend OTP works with rate limiting
9. OTP expires after 10 minutes
10. All responsive design breakpoints work

---

## 🚀 Next Steps After Testing

1. **Email Template Enhancement** (Optional)
   - Create HTML email template in `resources/views/emails/verify-otp.blade.php`
   - Update Mail::raw() to Mail::view()
   - Add logo and styling

2. **Security Enhancements** (Future)
   - Add OTP attempt limiting (max 3 wrong attempts)
   - Add brute-force protection
   - Add SMS OTP option

3. **User Features** (Future)
   - Add option to request new email
   - Add phone number verification option
   - Add account recovery options

---

## 📝 Testing Notes

**Date Tested:** ____________________
**Tester Name:** ____________________
**Laravel Version:** 11.x
**PHP Version:** 8.2+
**Browser:** ____________________

**Issues Found:**
1. ________________________________
2. ________________________________
3. ________________________________

**Approved By:** ____________________
**Date Approved:** ____________________
