# How to Enable/Disable Email Notifications

## Current Status: ❌ ALL DISABLED

All 6 email notifications are currently **disabled** (commented out).

---

## How to Re-Enable Emails in the Future

### Option 1: Enable Specific Email (Recommended)

Find the email you want to enable and **uncomment** the Mail::send() block:

```php
// BEFORE (Disabled):
/*
if ($student->user->email) {
    Mail::send('emails.book-issued', [...], function ($message) use ($student) {
        $message->to($student->user->email)->subject('Book Issued - Library Management System');
    });
}
*/

// AFTER (Enabled):
if ($student->user->email) {
    Mail::send('emails.book-issued', [...], function ($message) use ($student) {
        $message->to($student->user->email)->subject('Book Issued - Library Management System');
    });
}
```

---

## Locations of Email Code

### 1. **Book Issued Email** 📚
- **File**: `app/Http/Controllers/Staff/IssueBookController.php`
- **Line**: ~170
- **Search for**: `// Send email to student` (in issueBooks method)
- **Uncomment**: The if block with `Mail::send('emails.book-issued'`

### 2. **Book Returned Email** ✅
- **File**: `app/Http/Controllers/Staff/ReturnBookController.php`
- **Line**: ~189
- **Search for**: `// Send email to student` (in returnBooks method)
- **Uncomment**: The if block with `Mail::send('emails.book-returned'`

### 3. **Fine Paid Email** 💚
- **File**: `app/Http/Controllers/Admin/FineController.php`
- **Line**: ~90
- **Search for**: `// Send email to student` (in markAsPaid method)
- **Uncomment**: The if block with `Mail::send('emails.fine-paid'`

### 4. **Fine Waived Email** 🎉
- **File**: `app/Http/Controllers/Admin/FineController.php`
- **Line**: ~137
- **Search for**: `// Send email to student` (in waive method)
- **Uncomment**: The if block with `Mail::send('emails.fine-waived'`

### 5. **Request Approved Email** 🎉
- **File**: `app/Http/Controllers/Admin/BookRequestController.php`
- **Lines**: ~211-222
- **Search for**: `// Send email to student` (in update method)
- **Uncomment**: The if block with `Mail::send($emailTemplate` (for approved requests)

### 6. **Request Rejected Email** 📋
- **File**: `app/Http/Controllers/Admin/BookRequestController.php`
- **Lines**: ~211-222
- **Search for**: `// Send email to student` (in update method)
- **Uncomment**: The if block with `Mail::send($emailTemplate` (for rejected requests)

---

## Quick Enable All Script

To enable ALL emails at once using Find & Replace:

1. Open VS Code
2. Press `Ctrl + H` (Find and Replace)
3. **Find**: `// DISABLED: Uncomment to enable email notifications\n            /*`
4. **Replace**: `//`
5. Click "Replace All"

Then find and replace:
- **Find**: `*/` (at end of Mail::send block)
- **Replace**: `` (delete it)

---

## Email Templates Folder

All email template files are located in:
```
resources/views/emails/
```

Files:
- ✅ `book-issued.blade.php`
- ✅ `book-returned.blade.php`
- ✅ `fine-paid.blade.php`
- ✅ `fine-waived.blade.php`
- ✅ `request-approved.blade.php`
- ✅ `request-rejected.blade.php`

---

## Testing After Enabling

After uncommenting emails, test by:

1. **Book Issued**: Issue a book to student → Check email
2. **Book Returned**: Return a book → Check email
3. **Fine Paid**: Mark fine as paid → Check email
4. **Fine Waived**: Waive a fine → Check email
5. **Request Approved**: Approve book request → Check email
6. **Request Rejected**: Reject book request → Check email

---

## Mail Configuration

Make sure `.env` is configured:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="LibraryMS"
```

---

## Troubleshooting

### Emails still not sending?
1. Check `.env` mail configuration
2. Verify student email addresses are valid
3. Check Laravel logs: `storage/logs/laravel.log`
4. Ensure PHP mail extension is installed

### Want to disable again?
Just comment out the Mail::send() blocks again using:
```php
/*
Mail::send(...);
*/
```

---

## Summary

- ✅ All email templates: **Created & Ready**
- ✅ All controllers: **Updated with email code**
- ✅ Current status: **Disabled (commented out)**
- ✅ To enable: **Uncomment the Mail::send() blocks**
- ✅ Notifications still work: **Yes (in-app only)**

Simply uncomment the code when you're ready to enable emails!
