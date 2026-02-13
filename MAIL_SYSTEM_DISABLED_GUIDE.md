# Mail System - Disabled Guide

## Status
✋ **MAIL SYSTEM IS CURRENTLY DISABLED**

All email sending functionality has been commented out. The system logs email events instead of actually sending emails.

---

## Why Was It Disabled?

To prevent accidental email sending during development/testing without proper SMTP configuration.

---

## Re-Enable Mail System

### Step 1: Configure Mail Settings in `.env`

Edit your `.env` file and set the mail configuration:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io         # or your SMTP provider
MAIL_PORT=2525                      # or appropriate port
MAIL_USERNAME=your_username         # from mail provider
MAIL_PASSWORD=your_password         # from mail provider
MAIL_ENCRYPTION=tls                 # or ssl
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Library Management System"
```

### Step 2: Uncomment Mail Code

All Mail calls are commented with instructions. Find and uncomment them in these files:

#### 1. **Jobs** (4 files) - Lines with Mail::to()->send()
- `app/Jobs/SendBookIssuedEmail.php` - Line 28-35
- `app/Jobs/SendBookRequestStatusEmail.php` - Line 28-32
- `app/Jobs/SendFineEmail.php` - Line 28-36
- `app/Jobs/SendBookReturnedEmail.php` - Line 28-36

**Pattern to uncomment:**
```php
// Before (disabled):
// Mail::to($this->studentEmail)->send(new BookIssuedSimple(...));

// After (enabled):
Mail::to($this->studentEmail)->send(new BookIssuedSimple(...));
```

#### 2. **Auth Controllers** (2 files) - Lines with Mail::send()
- `app/Http/Controllers/Auth/RegisteredUserController.php` - Line 59-67
- `app/Http/Controllers/Auth/OTPVerificationController.php` - Line 170-180

**Pattern to uncomment:**
```php
// Before (disabled):
// try {
//     Mail::send('emails.otp-email', ...);
// } catch (...) { ... }

// After (enabled):
try {
    Mail::send('emails.otp-email', ...);
} catch (...) { ... }
```

#### 3. **Admin Controllers** - Lines with Mail::to()->queue()
- `app/Http/Controllers/Admin/UserController.php` - Line 407-415
- `app/Http/Controllers/Admin/StudentController.php` - Line 465-473

**Pattern to uncomment:**
```php
// Before (disabled):
// Mail::to($user->email)->queue(new PasswordResetEmail(...));

// After (enabled):
Mail::to($user->email)->queue(new PasswordResetEmail(...));
```

#### 4. **All Controllers** - Lines with dispatch() calls
- `app/Http/Controllers/Admin/TransactionController.php` - Lines ~272, ~420
- `app/Http/Controllers/Admin/FineController.php` - Lines ~202, ~247, ~295-301
- `app/Http/Controllers/Admin/BookRequestController.php` - Lines ~224
- `app/Http/Controllers/Staff/FineController.php` - Lines ~109, ~152-158
- `app/Http/Controllers/Staff/IssueBookController.php` - Lines ~206
- `app/Http/Controllers/Staff/ReturnBookController.php` - Lines ~215

**Pattern to uncomment:**
```php
// Before (disabled):
// SendBookIssuedEmail::dispatch(
//     $email, $name, ...
// );

// After (enabled):
SendBookIssuedEmail::dispatch(
    $email, $name, ...
);
```

### Step 3: Clear Cache

After uncommenting and setting `.env` values, clear Laravel cache:

```bash
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test Mail Configuration

Test if mail is working:

```bash
php artisan tinker
>>> Mail::raw('Test email', function($m) { $m->to('test@example.com'); });
```

If no errors, your mail is configured correctly.

---

## Popular SMTP Providers

### Mailtrap (Free - Development Only)
- **Host:** smtp.mailtrap.io
- **Port:** 2525 or 465
- **Site:** https://mailtrap.io
- **Use:** Great for testing, emails don't actually send

### Gmail
- **Host:** smtp.gmail.com
- **Port:** 587
- **Encryption:** tls
- **Setup:** Enable "App Password" in Gmail account settings

### Mailgun
- **Host:** smtp.mailgun.org
- **Port:** 587
- **Site:** https://www.mailgun.com
- **Use:** Production-ready

### SendGrid
- **Host:** smtp.sendgrid.net
- **Port:** 587
- **Site:** https://sendgrid.com
- **Use:** Production-ready

---

## How to Quickly Toggle Mail On/Off

### Disable Mail Using Environment Variable

Add to `.env`:
```env
DISABLE_MAIL=true
```

Then wrap mail code (optional, for quick testing):
```php
if (env('DISABLE_MAIL')) {
    \Log::info('Email disabled');
    return;
}
Mail::send(...);
```

### Quick Enable/Disable via .env

```env
# Disable mail
MAIL_MAILER=log   # Sends to logs instead of SMTP

# Enable mail
MAIL_MAILER=smtp
```

---

## Files Modified

All changes are in these locations:

**Jobs (4 files):**
- ✋ `app/Jobs/SendBookIssuedEmail.php`
- ✋ `app/Jobs/SendBookRequestStatusEmail.php`
- ✋ `app/Jobs/SendFineEmail.php`
- ✋ `app/Jobs/SendBookReturnedEmail.php`

**Auth Controllers (2 files):**
- ✋ `app/Http/Controllers/Auth/RegisteredUserController.php`
- ✋ `app/Http/Controllers/Auth/OTPVerificationController.php`

**Admin Controllers (4 files):**
- ✋ `app/Http/Controllers/Admin/UserController.php`
- ✋ `app/Http/Controllers/Admin/StudentController.php`
- ✋ `app/Http/Controllers/Admin/TransactionController.php`
- ✋ `app/Http/Controllers/Admin/FineController.php`
- ✋ `app/Http/Controllers/Admin/BookRequestController.php`

**Staff Controllers (3 files):**
- ✋ `app/Http/Controllers/Staff/FineController.php`
- ✋ `app/Http/Controllers/Staff/IssueBookController.php`
- ✋ `app/Http/Controllers/Staff/ReturnBookController.php`

---

## What Happens When Mail is Disabled

- ✓ All email code is commented
- ✓ Email addresses are logged (see `storage/logs/laravel.log`)
- ✓ User workflows continue normally
- ✓ No emails are sent to external addresses
- ✓ System remains fully functional

---

## Troubleshooting

### "SMTP Connection Refused"
- Check MAIL_HOST, MAIL_PORT are correct
- Verify MAIL_USERNAME, MAIL_PASSWORD
- Check firewall isn't blocking SMTP port

### "Authentication Failed"
- Verify credentials with mail provider
- Check for space/typo in .env
- Run `php artisan config:clear`

### "Email Sending But Not Receiving"
- Check spam/junk folder
- Verify MAIL_FROM_ADDRESS is valid
- Check mail provider's logs

---

## Command Reference

```bash
# Clear config cache
php artisan config:clear

# View mail config
php artisan tinker
>>> config('mail')

# Test sending
Mail::raw('Test', fn($m) => $m->to('test@test.com'));

# View mail logs
tail -f storage/logs/laravel.log
```

---

## Need Help?

Search for "MAIL SYSTEM DISABLED" in your codebase - all disabled sections have this comment.

Each disabled section includes:
- Location of the code
- Instructions to re-enable
- Comments explaining what was disabled
