# Email OTP Verification System - Implementation Guide

## Overview
Complete email OTP verification flow for new student registrations with 6-digit OTP validation, 10-minute expiration, and resend functionality.

## 📋 Implementation Details

### 1. Database Changes
**Migration File:** `database/migrations/2024_01_31_add_otp_to_users_table.php`

Added three new columns to `users` table:
- `otp` (string): Stores the 6-digit OTP
- `otp_expires_at` (timestamp): OTP expiration time (10 minutes from generation)
- `is_verified` (boolean): Tracks if user has verified their email

**Run Migration:**
```bash
php artisan migrate
```

### 2. Model Updates
**File:** `app/Models/User.php`

**Updated `$fillable` array:**
```php
'otp',
'otp_expires_at',
'is_verified',
```

**Updated `casts()` method:**
```php
'otp_expires_at' => 'datetime',
'is_verified' => 'boolean',
```

### 3. Registration Flow Changes
**File:** `app/Http/Controllers/Auth/RegisteredUserController.php`

**Key Changes:**
1. Generate 6-digit OTP: `str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT)`
2. Create user with `is_verified = false`
3. Set `otp_expires_at` to 10 minutes from now
4. Create Student record automatically
5. Send OTP via email using Laravel Mail
6. Redirect to OTP verification page instead of auto-login

**Process:**
```
Register → Generate OTP → Send Email → Redirect to Verify OTP Page
```

### 4. New OTP Verification Controller
**File:** `app/Http/Controllers/Auth/OTPVerificationController.php`

**Three Main Methods:**

#### a) `showVerificationPage(Request $request)`
- Displays OTP verification form
- Validates email format
- Checks if user exists and is unverified
- Checks OTP expiration
- Returns verify-otp view

#### b) `verifyOTP(Request $request)`
- Validates all 6 OTP input fields (numeric)
- Combines OTP digits into single 6-digit code
- Verifies against stored OTP
- Checks OTP expiration
- Marks user as verified if valid
- Logs user in automatically
- Redirects to student dashboard

#### c) `resendOTP(Request $request)` - AJAX
- Generates new OTP
- Implements rate limiting (wait 9 minutes before resending)
- Sends new OTP via email
- Returns JSON response
- Validates email parameter

### 5. New Routes
**File:** `routes/auth.php`

Added three new guest routes:
```php
// Show OTP verification page
Route::get('verify-otp', [OTPVerificationController::class, 'showVerificationPage'])
    ->name('verify.otp.page');

// Submit OTP for verification
Route::post('verify-otp', [OTPVerificationController::class, 'verifyOTP'])
    ->name('verify.otp');

// Resend OTP via AJAX
Route::post('resend-otp', [OTPVerificationController::class, 'resendOTP'])
    ->name('resend.otp');
```

### 6. OTP Verification Page UI
**File:** `resources/views/auth/verify-otp.blade.php`

**Features:**
- 6 separate numeric input fields for OTP digits
- Auto-focus to next field when digit entered
- Support for copy-paste (extracts digits only)
- Backspace navigation between fields
- "Verify OTP" button with loading animation
- "Resend OTP" button with 60-second countdown timer
- Display of user's email address
- Error message display with validation feedback
- Aesthetic purple gradient design matching login/register pages
- Fully responsive layout (mobile, tablet, desktop)
- Blue gradient book icon with floating animation

**User Interactions:**
1. User enters 6 digits (auto-moves to next field)
2. Can paste OTP code (auto-extracts digits)
3. Can use backspace to navigate back
4. Click "Verify OTP" to submit
5. Resend button disabled for 60 seconds, then enabled
6. Real-time countdown timer display

### 7. Validation Rules

**OTP Verification Validation:**
```php
'email' => 'required|email',
'otp1' => 'required|numeric|max:9',
'otp2' => 'required|numeric|max:9',
'otp3' => 'required|numeric|max:9',
'otp4' => 'required|numeric|max:9',
'otp5' => 'required|numeric|max:9',
'otp6' => 'required|numeric|max:9',
```

**Error Handling:**
- Invalid email format
- User not found
- Already verified users
- Expired OTP (older than 10 minutes)
- Incorrect OTP entered
- OTP not resendable before 1 minute
- Email sending failures logged

### 8. Email Sending
Uses Laravel Mail to send OTP:
```php
Mail::raw("Your OTP for Library Management System is: {$otp}\n\nThis OTP is valid for 10 minutes.", 
    function ($message) use ($user) {
        $message->to($user->email)
            ->subject('Email Verification - Library Management System');
    }
);
```

**Fallback:**
- If email fails to send, error is logged
- User can still attempt to verify manually
- Resend functionality allows retry

## 🔐 Security Features

1. **OTP Expiration:** 10-minute validity window
2. **Rate Limiting:** Cannot resend within 1 minute
3. **Numeric Validation:** Only 0-9 digits accepted
4. **User Verification:** Check if already verified
5. **Email Validation:** Check format and uniqueness
6. **Error Logging:** Failed email sends logged for debugging
7. **Safe Auto-Login:** Only logs in after successful verification

## 📱 Responsive Design

- **Desktop:** 2-column layout (form left, branding right)
- **Tablet:** Stacked layout with reduced padding
- **Mobile:** Single column, optimized input sizes (40x40px)
- **Scrollable:** Form container scrolls on small screens

## 🎨 Aesthetic Elements

- Deep purple gradient background (#512da8 - #6a1b9a)
- Light purple text (#e1bee7)
- Semi-transparent input fields
- Blue gradient book icon
- Smooth animations and transitions
- Loading spinner on submit
- Error messages with icons
- Floating animation on icon

## 🚀 Execution Flow

```
1. User registers with name, email, password
   ↓
2. System generates 6-digit OTP
   ↓
3. OTP sent via email
   ↓
4. User redirected to verify-otp page
   ↓
5. User enters 6-digit code
   ↓
6. System validates OTP (format, expiration, correctness)
   ↓
7. If valid:
   - Mark user as verified
   - Auto-login user
   - Create student record
   - Redirect to student dashboard
   
8. If invalid:
   - Show error message
   - Allow retry
   - Allow resend (after 60s)
```

## 📧 Email Template

**Subject:** Email Verification - Library Management System

**Body:**
```
Your OTP for Library Management System is: 123456

This OTP is valid for 10 minutes.
```

## 🛠️ Commands to Run

```bash
# 1. Run migration
php artisan migrate

# 2. Clear cache (optional)
php artisan cache:clear

# 3. Test registration flow
# Navigate to: http://127.0.0.1:8000/register
```

## ✅ Testing Checklist

- [ ] Register new student account
- [ ] Receive OTP in email
- [ ] Enter OTP digits (auto-focus works)
- [ ] Test copy-paste OTP
- [ ] Submit valid OTP
- [ ] User logged in and redirected to dashboard
- [ ] Test invalid OTP (shows error)
- [ ] Test OTP resend
- [ ] Test resend timer (60 seconds)
- [ ] Test OTP expiration (wait 10+ minutes)
- [ ] Test email validation
- [ ] Test responsive design on mobile

## 🔄 Database Schema

```sql
ALTER TABLE users ADD COLUMN otp VARCHAR(255) NULLABLE;
ALTER TABLE users ADD COLUMN otp_expires_at TIMESTAMP NULLABLE;
ALTER TABLE users ADD COLUMN is_verified BOOLEAN DEFAULT 0;
```

## 📝 Notes

1. OTP valid for exactly 10 minutes
2. User cannot resend OTP more than once per minute
3. Account not verified until OTP confirmed
4. Student record created immediately (optional flag to activate after OTP)
5. Email failures are logged but don't block registration
6. All times use `now()` for timezone consistency
7. Student automatically logged in after verification
8. Enrollment ID auto-generated: STU-{padded_user_id}

## 🎯 Future Enhancements

- SMS OTP option
- Email template customization
- Configurable OTP length
- Configurable expiration time
- Admin override capability
- OTP attempt limiting (max 3 wrong attempts)
- WhatsApp notification option
- Account activation email
