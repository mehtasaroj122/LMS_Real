# Library Management System - Detailed Action Flows & Sequences

**Date:** April 6, 2026  
**Purpose:** Complete documentation of all system actions and workflows  
**Audience:** Developers, QA, Business Analysts

---

## 📋 Table of Contents

1. [Authentication Actions](#authentication-actions)
2. [Admin Actions](#admin-actions)
3. [Staff Actions](#staff-actions)
4. [Student Actions](#student-actions)
5. [System Automated Actions](#system-automated-actions)
6. [Complete Workflows](#complete-workflows)

---

## Authentication Actions

### 🔐 User Login

**Trigger:** User navigates to login page  
**Actors:** Any user (Admin, Staff, Student)  

**Steps:**

1. User visits login page (`/login1`)
2. System displays login form with:
   - Email field
   - Password field
   - "Remember Me" checkbox
   - "Forgot Password" link
3. User enters email and password
4. System validates rate limiting (5 attempts per minute per email/IP)
5. System checks for account lockout:
   - If locked: Display unlock page with email verification link
   - If not locked: Proceed to authentication
6. System authenticates credentials against users table
7. If authentication fails:
   - Increment failed attempt counter
   - Log activity with IP, browser, device type
   - If threshold reached (default: 5 attempts): Lock account
   - Display error message
   - Return to login page
8. If authentication succeeds:
   - Verify account status:
     - If inactive: Redirect to inactive account page
     - If active: Proceed
   - Check if password reset is forced:
     - If forced: Redirect to password change page
     - If not forced: Proceed
   - Verify email (if required):
     - If not verified: Send verification email, redirect to verify page
     - If verified: Proceed
9. Create session:
   - Store user ID, role, permissions in session
   - Set session expiration based on config
10. Redirect to role-based dashboard:
    - Admin → `/admin/dashboard`
    - Staff → `/staff/dashboard`
    - Student → `/student/dashboard`
11. Log successful login activity

**Response Data:**
```json
{
  "user_id": 1,
  "role": "admin",
  "email": "admin@library.com",
  "permissions": ["access-admin"],
  "session_token": "xxx",
  "session_expiry": "2026-04-06 18:00:00"
}
```

**Validations:**
- Email format validation
- Password not empty
- Rate limiting per IP and email
- Account lockout status check
- Account active/inactive status
- Email verification status
- Forced password change flag

---

### 🔑 Forgot Password Request

**Trigger:** User clicks "Forgot Password" on login page  
**Actors:** Any user (unauthenticated)

**Steps:**

1. User navigates to password reset page
2. System displays email input form
3. User enters email address
4. System validates:
   - Email exists in users table
   - Email format is valid
   - Rate limiting (5 requests per minute per email/IP)
5. System generates:
   - Reset token (signed, 60-minute expiration)
   - Memorable link: `/reset-password?token={token}&email={email}`
6. System sends email with:
   - User's name
   - Reset link
   - Expiration time
   - Security note: "If you didn't request this, ignore this email"
7. System queues email to "emails" queue
8. Display success message to user
9. Log password reset request activity

**Email Template:**
```
Subject: Reset Your Library Account Password

Hello [User Name],

We received a request to reset your password. Click the link below:

[Reset Link - valid for 60 minutes]

If you didn't request this, please ignore this email.

Security Note: This link is secure and unique to your account.
```

**Validations:**
- Email must exist in system
- Rate limiting enforced
- Token must be cryptographically signed
- Token must include user ID
- Email must match user record

---

### 🔄 Password Reset

**Trigger:** User clicks reset link from email  
**Actors:** Any user

**Steps:**

1. User clicks reset link from email
2. System verifies token:
   - Token signature is valid
   - Token is not expired (60 minutes)
   - Token matches user email
3. If token invalid: Display error and link to re-request
4. If token valid: Display password reset form with:
   - New password field
   - Confirm password field
   - Password requirement hints
5. User enters new password
6. System validates password:
   - Minimum 8 characters
   - Contains uppercase, lowercase, number, special character
   - Not same as current password
   - Confirmation matches
7. System updates user record:
   - Hash new password using bcrypt
   - Clear "force_password_change" flag
   - Add timestamp to password_changed_at
8. System invalidates all existing sessions for that user (force re-login)
9. Send confirmation email to user
10. Redirect to login page with success message
11. Log password reset action

**Response:**
```json
{
  "success": true,
  "message": "Password reset successful. Please log in.",
  "redirect": "/login1"
}
```

**Password Requirements:**
- Minimum 8 characters
- At least 1 uppercase letter
- At least 1 lowercase letter
- At least 1 number
- At least 1 special character (!@#$%^&*)

---

### 🔓 Unlock Account

**Trigger:** User clicks unlock link or Admin manually unlocks  
**Actors:** User (self), Administrator

**Route:** `/unlock-account?token={token}`

**Steps (User Self-Unlock):**

1. User receives unlock email after account lockout
2. Email contains 24-hour signed unlock link
3. User clicks link:
   - System verifies token (signed, 24-hour expiry)
   - System verifies token is for lockout unlock (not password reset)
4. System unlocks account:
   - Set locked_until = NULL
   - Reset failed_login_attempts = 0
5. Display success message
6. Redirect to login page
7. Log unlock action by user

**Steps (Admin Unlock):**

1. Admin navigates to `/admin/account-locks`
2. System displays list of locked accounts with:
   - User name/email
   - Lock time
   - Failed attempts
   - Action buttons (Unlock, Unlock All)
3. Admin clicks "Unlock" button for specific user
4. System confirms action with modal
5. Admin clicks confirm
6. System unlocks account:
   - Set locked_until = NULL
   - Reset failed_login_attempts = 0
7. System sends notification to user (optional)
8. Display success message to admin
9. Log unlock action performed by admin with admin ID
10. Refresh locked accounts list

**CLI Command (Alternative):**
```bash
php artisan auth:unlock-account {email}
```

---

### ✉️ Email Verification

**Trigger:** During registration, admin password reset, or manual request  
**Actors:** New user, Admin (for resets), User (manual request)

**Steps:**

1. System generates verification token (signed, 48-hour expiry)
2. System creates verification link: `/verify-email?token={token}`
3. System sends email with:
   - Verification link
   - Token expiration time
   - Note about link validity
4. User clicks verification link
5. System verifies token:
   - Signature is valid
   - Not expired (48 hours)
   - Token matches user email
6. If valid:
   - Set email_verified_at = current timestamp
   - Set email_verification_token = NULL
7. If invalid:
   - Display error
   - Show option to resend verification email
8. Log email verification action
9. Redirect to dashboard (if authenticated) or login

---

## Admin Actions

### 📚 Add Book

**Trigger:** Admin clicks "Add Book" button on book management page  
**Path:** `/admin/books/create` → POST `/admin/books`  

**Form Fields:**
- ISBN (unique, required)
- Title (required)
- Author (required)
- Publisher
- Category (select from dropdown, required)
- Edition
- Publication Year
- Total Copies (number, required)
- Available Copies (auto-calculated)
- Condition (New, Good, Fair, Damaged)
- Shelf Number
- Book Cover Image (optional, JPG/PNG)

**Steps:**

1. Admin navigates to book management
2. Clicks "Add New Book" button
3. System displays book creation form
4. Admin fills in book details
5. Admin (optional) uploads book cover image:
   - File validation: JPG, PNG only
   - Size limit: 5MB
   - Image stored in storage/app/public/book-covers/
   - Filename: {isbn}-cover.{ext}
6. Admin clicks "Save"
7. System validates:
   - ISBN format and uniqueness (server-side)
   - Title not empty
   - Author not empty
   - Category exists
   - Total Copies > 0
   - Available Copies ≤ Total Copies
   - Cover image dimensions (if provided)
8. System creates book record:
   - Insert into books table
   - Copy ISBN to slug for URL-friendly access
   - Set created_by = admin ID
   - Set created_at = current timestamp
9. If book cover uploaded:
   - Move temporary file to permanent location
   - Store path in book record
   - Trigger notification to students (optional)
10. Log activity: "Book created" with book ID
11. Display success message
12. Redirect to book list or new book detail page

**Validations:**
```
ISBN: Must be 10 or 13 digits, unique
Title: 1-255 characters
Author: 1-255 characters
Category: Must exist in categories table
Total Copies: > 0
Available Copies: 0 to Total Copies
Shelf Number: 1-50 characters
Cover Image: JPG/PNG, max 5MB, min 200x300px
```

**Activity Log Entry:**
```json
{
  "action": "Book Added",
  "user_id": 1,
  "resource_type": "Book",
  "resource_id": 42,
  "changes": {
    "isbn": "978-0-123456-78-9",
    "title": "Laravel Best Practices",
    "author": "John Doe"
  },
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0..."
}
```

---

### 📚 Edit Book

**Trigger:** Admin clicks edit icon on book in list or detail page  
**Path:** `/admin/books/{book}/edit` → PUT `/admin/books/{book}`

**Steps:**

1. Admin navigates to book list or book detail page
2. Clicks edit button/icon
3. System retrieves book data from database
4. System displays pre-filled edit form with:
   - Current values for all fields
   - Current book cover image preview
   - Option to replace cover image
5. Admin modifies fields
6. Admin (optional) uploads new cover image:
   - Old cover image deleted
   - New image uploaded following same rules as Add Book
7. Admin clicks "Save"
8. System validates all fields (same as Add Book)
9. System checks for conflicts:
   - If ISBN changed: Verify new ISBN is unique
   - If Total Copies decreased: Verify not below issued copies
10. System updates book record with changed fields
11. System creates audit trail of changes:
    - Records old values and new values
    - For each changed field
12. Log activity: "Book Updated" with field changes
13. Display success message
14. Optionally display change summary to admin

**Change Tracking:**
```json
{
  "field": "total_copies",
  "old_value": 5,
  "new_value": 10,
  "changed_by": "admin@library.com",
  "changed_at": "2026-04-06 14:30:00"
}
```

---

### 🗑️ Delete Book / Request Deletion

**Admin Path:** `/admin/books/{book}` → DELETE  
**Staff Path:** Submit deletion request → Admin approval

**Admin Delete Steps:**

1. Admin navigates to book list
2. Clicks delete button for book
3. System checks if deletion is allowed:
   - No active book issues for this book
   - No pending book requests for this book
4. If restrictions exist:
   - Display message: "Cannot delete: X active issues, Y pending requests"
   - Show option to view related issues/requests
   - Offer to archive instead of delete
5. If allowed:
   - Display confirmation modal with book title
   - Show book cover
   - Warn about permanent deletion
6. Admin confirms deletion
7. System marks book as deleted:
   - Set deleted_at = current timestamp (soft delete)
   - Or: Remove from database (hard delete)
8. Log deletion activity with reason
9. Display success message
10. Refresh book list

**Staff Request Deletion Steps:**

1. Staff navigates to book list
2. Clicks "Request Deletion" button for book
3. System checks if deletion request already exists:
   - If pending: Show existing request status
   - If approved/rejected: Allow new request
4. System creates book deletion request:
   - Staff ID recorded
   - Book ID linked
   - Reason field (optional)
   - Status = "pending"
   - Created timestamp recorded
5. System sends notification to admin:
   - Book deletion request pending
   - Link to review request
6. Display message to staff: "Deletion request submitted"
7. Log activity: "Book deletion requested" by staff

**Admin Review Deletion Request:**

1. Admin navigates to "Book Deletion Requests"
2. System displays list of pending requests with:
   - Book title, ISBN
   - Requesting staff member
   - Request date
   - Reason provided
   - Review buttons (Approve/Reject)
3. Admin clicks "Approve":
   - Delete book following normal deletion rules
   - Mark request as approved with timestamp
   - Notify requesting staff member
4. Admin clicks "Reject":
   - Display rejection reason modal
   - Record rejection reason
   - Mark request as rejected
   - Notify requesting staff member

---

### 👥 Create Admin/Staff User

**Trigger:** Admin clicks "Create User" button  
**Path:** `/admin/users/create` → POST `/admin/users`

**Form Fields:**
- Name (required)
- Email (required, unique)
- Role (Admin, Staff) - dropdown
- Department (optional)
- Phone (optional)

**Steps:**

1. Admin navigates to user management
2. Clicks "Add New User" button
3. System displays user creation form
4. Admin fills in details
5. Admin click "Send Invitation"
6. System validates:
   - Email format valid
   - Email not already in use
   - Email not in pending invitations
   - Name not empty
   - Role is valid (admin or staff)
7. System generates:
   - Unique invitation token (48-hour expiry)
   - Invitation link: `/register?token={token}&email={email}`
8. System creates invitation record:
   - invited_by = admin ID
   - invited_at = current timestamp
   - invited_email = email
   - invited_role = selected role
   - invitation_token = token
   - expires_at = now + 48 hours
9. System sends invitation email:
   ```
   Subject: Invitation to Join Library Management System
   
   Dear [Name],
   
   [Admin Name] has invited you to join the Library Management System.
   
   Click the link below to complete your registration:
   [Invitation Link - valid for 48 hours]
   
   Register with the email: [email]
   
   If you didn't expect this, please ignore.
   ```
10. Queue email to "emails" queue
11. Log activity: "User invitation sent"
12. Display success message to admin
13. Show invitation status in user list

**Invitee Registration (Completes Invitation):**

1. Invitee receives email with invitation link
2. Clicks link: `/register?token={token}&email={email}`
3. System verifies token:
   - Token signature valid
   - Token not expired (48 hours)
   - Email in invitation matches
   - No existing user with this email
4. If valid: Display registration form with:
   - Pre-filled email (read-only)
   - Pre-filled role (read-only)
   - Password field
   - Confirm password field
   - Accept terms checkbox
5. Invitee enters password
6. System validates password (same rules as password reset)
7. System creates user record:
   - All invitation details
   - Hash password with bcrypt
   - Set email_verified_at = current timestamp (pre-verified by invitation)
   - Set status = "active"
   - Delete invitation record
8. Send welcome email with:
   - Login instructions
   - System URL
   - Support contact
9. Log activity: "User registered"
10. Redirect to login page
11. Display success message

**Invitation Expiration:**

- System daily job checks for expired invitations
- Invitations older than 48 hours marked as "expired"
- Admin can re-invite or delete expired invitations
- Expired invitations cannot be used for registration

---

### 👥 Create/Invite Student

**Trigger:** Admin clicks "Create Student" button  
**Path:** `/admin/students/create` → POST `/admin/students`

**Form Fields:**
- First Name (required)
- Last Name (required)
- Email (required, unique)
- Phone (required)
- Student ID (required, unique)
- Department (dropdown, required)
- Batch/Year (dropdown, required)
- Semester (dropdown, required)
- Gender (optional)
- Address (optional)
- City (optional)
- State (optional)
- Postal Code (optional)

**Steps:**

1. Admin navigates to student management
2. Clicks "Add New Student" button
3. System displays student creation form
4. Admin fills in student details
5. Admin clicks "Send Invitation"
6. System validates:
   - Email format valid and unique
   - Student ID unique and valid format
   - Phone valid format
   - Required fields not empty
   - Department and batch/semester exist
7. System creates student invitation:
   - Similar to staff invitation
   - Set invited_role = "student"
   - Generate invitation token (48-hour expiry)
   - Create invitation link
8. System sends invitation email with:
   - Welcome message to student
   - Registration link
   - Instructions
9. Student confirms registration (same as staff)
10. Additional student-specific data:
    - Create default student privilege record (if settings require)
    - Initialize notification preferences to defaults
    - Create first activity log entry
11. Log activity: "Student created and invited"
12. Display success message

**Difference from Staff Invitation:**
- Student gets more profile fields (academic info)
- Student record created in `students` table (not `users` table)
- User record also created with linked student_id
- Student-specific activity logs and preferences initialized

---

### 🔄 Reset User Password

**Trigger:** Admin clicks reset button for user in user list  
**Path:** POST `/admin/users/{user}/reset-password`

**Steps:**

1. Admin navigates to user management list
2. Clicks three-dot menu for specific user
3. Selects "Reset Password"
4. System displays confirmation modal:
   - User name and email
   - Warning: "User will be forced to change password on next login"
   - Option to also send email notification
5. Admin confirms
6. System updates user record:
   - Generate random temporary password (12 characters)
   - Hash password with bcrypt
   - Set force_password_change = true
   - Set password_changed_at = NULL (forces change)
7. System sends email with:
   - Temporary password (if admin selected option)
   - Instructions to login and change password
   - Link to forgot password
8. System invalidates all existing sessions for that user
9. Log activity: "Password reset by admin"
10. Display success message
11. Optionally show generated password to admin (with copy button)

**Optional: Send Temporary Password**
```
Subject: Your Password Has Been Reset

Dear [User Name],

Your password has been reset. Here's your temporary password:

[Temporary Password]

At your next login, you will be required to change your password.

If you have any questions, contact the administrator.
```

---

### 🔒 Unlock Account / Manage Lockouts

**Trigger:** Admin navigates to Account Locks section  
**Path:** `/admin/account-locks`

**View Locked Accounts:**

1. Admin clicks "Account Locks" in admin menu
2. System queries all locked accounts:
   - SQL: `SELECT * FROM users WHERE locked_until IS NOT NULL AND locked_until > NOW()`
3. System displays table with:
   - User name, email
   - Lock time (when locked)
   - Failed attempts count
   - Unlock buttons
   - Lock reason (if recorded)
4. Pagination: Display 20 per page
5. Search/Filter by:
   - Email
   - Lock date range
   - Failed attempts range

**Unlock Single Account:**

1. Admin clicks "Unlock" button for specific user
2. System displays confirmation modal
3. Admin confirms
4. System updates user:
   - Set locked_until = NULL
   - Set failed_login_attempts = 0
5. System sends notification to user (optional):
   ```
   Subject: Your Account Has Been Unlocked
   
   Dear [User Name],
   
   Your account has been successfully unlocked.
   You can now log in with your credentials.
   ```
6. Log unlock activity with admin ID
7. Display success message
8. Refresh locked accounts list

**Unlock All Accounts:**

1. Admin clicks "Unlock All" button
2. System displays confirmation modal with count of locked accounts
3. Admin confirms
4. System iterates through all locked accounts:
   - For each: Set locked_until = NULL, failed_login_attempts = 0
   - Send notification emails (optional, batched)
5. Log bulk unlock activity
6. Display summary: "X accounts unlocked"
7. Refresh locked accounts list

**Account Lockout Settings:**

Admin can configure:
- Max failed attempts before lockout: default 5
- Lockout duration: default 30 minutes
- Email notification on lockout: yes/no
- IP-based lockout: yes/no

---

### 📊 View Dashboard

**Trigger:** Admin logs in, clicks Dashboard  
**Path:** `/admin/dashboard`

**Dashboard Components:**

**1. Key Statistics Cards:**
- Total Books: `SELECT COUNT(*) FROM books WHERE deleted_at IS NULL`
- Available Books: `SELECT SUM(available_copies) FROM books`
- Total Students: `SELECT COUNT(*) FROM students WHERE status = 'active'`
- Total Staff: `SELECT COUNT(*) FROM users WHERE role = 'staff' AND status = 'active'`
- Pending Requests: `SELECT COUNT(*) FROM book_requests WHERE status = 'pending'`
- Outstanding Fines: `SELECT COUNT(*) FROM fines WHERE status = 'pending'`
- Total Fine Amount: `SELECT SUM(amount) FROM fines WHERE status = 'pending'`
- Locked Accounts: `SELECT COUNT(*) FROM users WHERE locked_until > NOW()`

**2. Charts (JavaScript driven):**
- Fine Trend (7 days): Line chart of daily fines calculated
- Book Issues vs Returns: Bar chart comparing activity
- Category Distribution: Pie chart of books by category
- Request Status: Donut chart (pending/approved/rejected)

**3. Recent Activity Table:**
- Last 10 activities from activity_logs table
- Columns: User, Action, Resource, Timestamp, IP Address
- Sortable, searchable

**4. Upcoming Due Dates:**
- Books due in next 7 days
- Table: Student, Book, Due Date, Days Remaining
- Color coding: Green (> 3 days), Yellow (1-3 days), Red (overdue)

**5. Locked Accounts Alert:**
- If count > 0: Show alert box with quick link to unlock

**6. System Health:**
- Queue jobs pending: `SELECT COUNT(*) FROM jobs`
- Failed jobs: `SELECT COUNT(*) FROM failed_jobs`
- Cache status: UP/DOWN
- Database connection: UP/DOWN

**Steps:**

1. Admin logs in and navigates to dashboard
2. System loads dashboard page
3. JavaScript AJAX calls fetch data:
   - GET `/admin/dashboard` - Main stats
   - GET `/admin/dashboard/fine-trend` - Chart data (AJAX)
   - GET `/admin/books/stats` - Book statistics (AJAX)
   - GET `/admin/users/stats` - User statistics (AJAX)
4. Templates render with data
5. Charts initialize with Chart.js library
6. Page complete, dashboard displayed

**Response Example:**
```json
{
  "total_books": 450,
  "available_books": 320,
  "total_students": 500,
  "total_staff": 8,
  "pending_requests": 25,
  "outstanding_fines": 85,
  "total_fine_amount": 5240.50,
  "locked_accounts": 3
}
```

---

### 💰 Manage Fines - Mark as Paid

**Trigger:** Admin selects fine and clicks "Mark as Paid"  
**Path:** POST `/admin/fines/{fine}/mark-as-paid`

**Steps:**

1. Admin navigates to fine management page
2. System displays list of fines with filters:
   - Status: All, Pending, Paid, Waived
   - Student: Search by name/ID
   - Date range
3. Admin finds fine or selects from list
4. Admin clicks "Mark as Paid" button
5. System displays confirmation modal with:
   - Fine details: Student name, amount, reason (overdue/damage/loss)
   - Payment date field (default: today)
   - Optional receipt number field
6. Admin confirms or enters details
7. System updates fine record:
   - Set status = "paid"
   - Set paid_at = current timestamp (or admin-entered date)
   - Set paid_by = "admin" (method)
   - Set payment_reference = receipt number (if provided)
   - Add transaction record
8. System creates receipt:
   - Receipt number: AUTO-GENERATED format YYYY-MM-XXXXX
   - Fine details
   - Payment info
   - Timestamps
9. System sends confirmation email to student:
   ```
   Subject: Fine Payment Received - [Receipt Number]
   
   Dear [Student Name],
   
   Your fine payment has been received and processed.
   
   Fine Details:
   - Amount: $[amount]
   - Reason: [reason]
   - Receipt: [receipt_number]
   - Paid On: [date]
   
   Receipt attached or available in your account.
   ```
10. Log activity: "Fine marked as paid"
11. Update student fine summary cache
12. Display success message
13. Fine removed from pending list

---

### 💰 Waive Fine

**Trigger:** Admin clicks "Waive Fine" button  
**Path:** POST `/admin/fines/{fine}/waive`

**Steps:**

1. Admin navigates to fine management
2. Finds and selects fine to waive
3. Clicks "Waive Fine" button
4. System displays waive modal with:
   - Fine details
   - Required reason field for waiver
   - Reason suggestions dropdown:
     - "Damaged book condition"
     - "Exceptional circumstance"
     - "System error"
     - "Administrative decision"
     - "Other" (allows custom reason)
5. Admin selects reason and (optional) adds notes
6. System validates:
   - Reason provided
   - Reason length (max 500 chars)
7. System updates fine record:
   - Set status = "waived"
   - Set waived_at = current timestamp
   - Set waived_by = admin ID
   - Set waive_reason = provided reason
   - Set waive_notes = admin notes (if provided)
8. System creates waiver record:
   - Fine ID
   - Admin ID
   - Reason
   - Timestamp
9. System sends notification to student:
   ```
   Subject: Fine Waived - [Fine ID]
   
   Dear [Student Name],
   
   Your fine has been waived.
   
   Fine Details:
   - Amount: $[amount]
   - Reason: [reason]
   - Waived On: [date]
   - Waived By: [admin name]
   
   Thank you for your understanding.
   ```
10. Log activity: "Fine waived" with reason
11. Display success message
12. Remove from pending fines list

---

### 📋 Process Book Requests (Approve/Reject)

**Trigger:** Admin selects pending request, clicks Approve or Reject  
**Path:** PUT `/admin/book-requests/{request}`

**Steps:**

1. Admin navigates to "Book Requests" section
2. System displays requests filtered by status (default: Pending)
3. Table shows:
   - Student name/ID
   - Book title/ISBN
   - Request date
   - Status
   - Action buttons
4. Admin clicks "Approve" or "Reject" for request

**Approve Request:**

1. Admin clicks "Approve"
2. System checks if book is available:
   - `available_copies > 0` for the book
3. If not available:
   - Display warning: "Book not available, add to queue?"
   - Admin can queue request for later
4. If available:
   - Display confirmation modal with:
     - Student details
     - Book details
     - Option to auto-issue (auto-issue to student immediately)
5. Admin confirms
6. System updates request:
   - Set status = "approved"
   - Set approved_at = current timestamp
   - Set approved_by = admin ID
7. System optionally auto-issues if selected:
   - Decrease available_copies by 1
   - Create issued_book record
   - Set due_date = today + duration (from settings)
   - Set issued_at = current timestamp
   - Change request status = "issued"
8. If not auto-issuing:
   - Request status = "approved"
   - Staff must manually issue later
9. Send notification to student:
   ```
   Subject: Your Book Request Has Been Approved
   
   Dear [Student Name],
   
   Your request for "[Book Title]" has been approved!
   
   Book Details:
   - Title: [Book Title]
   - Author: [Author]
   - ISBN: [ISBN]
   - Due Date: [date] (if auto-issued)
   
   [If not issued: "The book is ready for pickup at the library desk."]
   [If issued: "The book has been issued. Return by [due date]."]
   ```
10. Log activity: "Request approved" with request ID
11. Display success message

**Reject Request:**

1. Admin clicks "Reject"
2. System displays rejection modal with:
   - Student name/book
   - Required reason field
   - Reason suggestions:
     - "Book out of stock"
     - "Student has overdue books"
     - "Student has outstanding fines"
     - "Other" (custom)
3. Admin selects/enters reason
4. System validates reason provided
5. System updates request:
   - Set status = "rejected"
   - Set rejected_at = current timestamp
   - Set rejected_by = admin ID
   - Set rejection_reason = provided reason
6. Send notification to student:
   ```
   Subject: Book Request Status - [Book Title]
   
   Dear [Student Name],
   
   Unfortunately, your request for "[Book Title]" has been rejected.
   
   Reason: [rejection_reason]
   
   Please contact the library for more information.
   ```
7. Log activity: "Request rejected" with reason
8. Display success message
9. Remove from pending list

---

### 📋 Bulk Process Requests

**Trigger:** Admin selects multiple requests, clicks bulk action  
**Path:** POST `/admin/book-requests/bulk-status`

**Steps:**

1. Admin navigates to book requests
2. System displays request list with checkboxes
3. Admin selects multiple requests using:
   - Individual checkboxes
   - "Select All" checkbox
   - Filter by status (auto-select all filtered)
4. Bulk action dropdown appears at top of page with options:
   - "Approve Selected"
   - "Reject Selected"
   - "Mark as Issued"
   - "Delete Selected"
5. Admin selects action
6. System displays confirmation modal showing:
   - Count of requests to process
   - Sample requests (first 5)
   - Action to be performed
7. Admin confirms
8. System processes each request:
   - For each request: Apply selected action
   - Skip requests that cannot be processed (e.g., missing book for issue)
   - Collect results
9. System sends batch notifications:
   - Group notifications by student
   - Send single email per student with all their request updates
10. Log bulk activity: "X requests bulk-processed" with action type
11. Display results summary:
    - "Successfully processed: X"
    - "Skipped: Y"
    - Details of any failures
12. Refresh request list

**Response Example:**
```json
{
  "success": true,
  "processed": 12,
  "skipped": 3,
  "failed": 0,
  "details": {
    "approved": 10,
    "rejected": 2
  },
  "message": "Successfully processed 12 requests"
}
```

---

## Staff Actions

### 📤 Issue Book

**Trigger:** Staff clicks "Issue Book" or navigates to issue page  
**Path:** `/staff/issue-book`

**Steps:**

1. Staff navigates to "Issue Book" section
2. System displays form with two dropdowns:
   - Student search (autocomplete): Search by Student ID, name, email
   - Book search (autocomplete): Search by ISBN, title, author
3. Staff searches and selects student
4. System loads student info:
   - Student ID, name, email
   - Current issued books count
   - Outstanding fines count
   - Locked status
5. System validates student eligibility:
   - Account active? If not: Show error
   - Account locked? If yes: Show error
   - Outstanding fines > threshold? If yes: Show warning (allow override)
6. Staff searches and selects book
7. System checks book availability:
   - If available_copies = 0: Show error, cannot issue
   - If available_copies > 0: Show available count
8. System checks for duplicate issue:
   - `SELECT * FROM issued_books WHERE book_id = ? AND student_id = ? AND returned_at IS NULL`
   - If found: Show error, "Student already has this book issued"
9. System loads issue details:
   - Book cover, ISBN, title
   - Current available copies
   - Default due date (from settings or student privilege)
10. Staff reviews and confirms
11. System calculates due date:
    - Check student privileges for custom due date
    - If no custom: Use global setting
    - Due date = today + duration (default: 14 days)
12. Staff clicks "Confirm Issue"
13. System creates issued_book record:
    - student_id
    - book_id
    - issued_date = current timestamp
    - due_date = calculated date
    - issued_by = staff ID
    - status = "active"
14. System updates book inventory:
    - Decrement available_copies by 1
    - `UPDATE books SET available_copies = available_copies - 1 WHERE id = ?`
15. System cancels related book request (if exists):
    - Find pending request for this book by this student
    - Set status = "issued"
    - If other students waiting: Adjust request queue
16. Send notification to student:
    ```
    Subject: Book Issued - [Book Title]
    
    Dear [Student Name],
    
    A book has been issued to your account.
    
    Book Details:
    - Title: [Title]
    - Author: [Author]
    - ISBN: [ISBN]
    - Issue Date: [date]
    - Due Date: [date]
    - Days to Return: [days]
    
    Return by the due date to avoid fines.
    ```
17. Log activity: "Book issued"
18. Print receipt (optional):
    - Issue receipt with book and due date details
19. Display success message
20. Clear form or show next issue option

**Validation Rules:**
- Student account must be active
- Student account must not be locked
- Book must exist and have available copies
- No duplicate active issue for same student-book pair
- Staff must be logged in

---

### 📥 Return Book

**Trigger:** Staff clicks "Return Book" button  
**Path:** `/staff/return-book`

**Steps:**

1. Staff navigates to "Return Book" section
2. System displays search form
3. Staff searches for issued book:
   - By student name/ID
   - By ISBN/book title
   - Combined search
4. Staff enters search criteria
5. System queries:
   - `SELECT * FROM issued_books WHERE returned_at IS NULL AND (student_id = ? OR book_id = ?)`
6. System displays results as table:
   - Student name
   - Book title
   - Issue date
   - Due date
   - Days overdue (if applicable)
   - Return buttons
7. Staff clicks "Process Return" for specific issue
8. System displays return modal with:
   - Book title, student name
   - Issue date, due date
   - Days overdue (if any)
   - Condition dropdown:
     - "Good" (no damage)
     - "Fair" (minor damage)
     - "Damaged" (major damage)
     - "Lost"
   - Optional notes field
   - Calculated fine (if applicable)
9. Staff selects condition
10. System calculates fine (if overdue):
    - Days overdue = today - due_date
    - Base fine = days_overdue × daily_rate
    - Apply penalties based on condition:
      - Good: 0% penalty
      - Fair: 15% penalty
      - Damaged: 50% penalty
      - Lost: max_fine or book_value
    - Display calculated fine to staff
11. Staff enters notes (optional):
    - "Water damage", "Page torn", etc.
12. Staff clicks "Confirm Return"
13. System creates fine record (if fine > 0):
    - student_id, book_id
    - fine_type = "overdue" or "damage" or "loss"
    - amount = calculated fine
    - reason = condition + notes
    - status = "pending"
    - created_at = current timestamp
14. System updates issued_book record:
    - Set returned_at = current timestamp
    - Set returned_by = staff ID
    - Set condition = selected condition
    - Set return_notes = entered notes
    - Set return_status = "returned"
15. System updates book inventory:
    - Increment available_copies by 1
    - `UPDATE books SET available_copies = available_copies + 1 WHERE id = ?`
16. System creates activity log
17. Send notification to student:
    ```
    Subject: Book Returned - [Book Title]
    
    Dear [Student Name],
    
    Your book return has been processed.
    
    Book Details:
    - Title: [Title]
    - Return Date: [date]
    - Condition: [condition]
    
    [If Fine Applied]:
    - Fine Amount: $[amount]
    - Reason: [reason]
    - Fine Status: Pending
    - Due Date: [date]
    ```
18. Display confirmation:
    - Success message
    - Fine amount (if applicable)
    - Receipt option
19. Clear form

---

### 💰 Waive/Mark Fine as Paid (Staff)

**Staff can:**
- Mark fine as paid (with receipt number)
- Waive fine (with reason - but may have admin restrictions)

**Steps: Mark as Paid**

1. Staff navigates to Fine Management
2. Searches for student or fine
3. Clicks "Mark as Paid" for specific fine
4. Modal displays:
   - Fine amount, reason
   - Payment date (default today)
   - Receipt/Reference number field
5. Staff enters receipt number
6. Clicks confirm
7. System updates fine:
   - status = "paid"
   - paid_at = timestamp
   - payment_reference = receipt number
8. Log activity
9. Send notification to student
10. Display success

---

## Student Actions

### 🔍 Search Books

**Trigger:** Student navigates to "Search Books" or library homepage  
**Path:** `/student/books` or `/student/search`

**Steps:**

1. Student clicks "Search Books" in menu
2. System displays book search interface with:
   - Text search field (ISBN, title, author)
   - Category filter dropdown (autocomplete)
   - Author filter (autocomplete)
   - Availability filter: All, Available, Unavailable
   - Sort options: Relevance, Title A-Z, Author, Newest
   - Search button
3. Student enters search criteria
4. Clicks "Search" or starts typing (auto-search after 3 chars)
5. System queries books table:
   - `SELECT * FROM books WHERE (title LIKE ? OR author LIKE ? OR isbn LIKE ?) AND deleted_at IS NULL`
   - Apply category filter if selected
   - Apply availability filter if selected
6. System returns results with:
   - Book cover image
   - Title, author
   - ISBN
   - Category
   - Availability status: "Available (5 copies)" or "Unavailable (0 copies)"
   - Request/View Details button
7. Student can:
   - Click book to view details
   - Click "Request" button to request book
   - Add to wishlist (if enabled)

**View Book Details:**

1. Student clicks book in search results
2. System displays detailed book view:
   - Large cover image
   - Title, author, publisher
   - ISBN, edition, publication year
   - Category, shelf number
   - Full description (if available)
   - Total copies, available copies
   - Condition (Good, Fair, Damaged, Lost)
   - Reviews/ratings (if enabled)
   - "Request Book" button
   - "Add to Wishlist" button (if enabled)
3. Student can request or go back to search

---

### 📥 Request Book

**Trigger:** Student clicks "Request" on book detail page  
**Path:** POST `/student/books/{book}/request`

**Steps:**

1. Student views book details
2. Clicks "Request Book" button
3. System checks prerequisites:
   - Student account active? If not: Show error
   - Book exists? If not: Show error
   - Duplicate active request? Check: `SELECT * FROM book_requests WHERE student_id = ? AND book_id = ? AND status IN ('pending','approved')`
   - If duplicate: Show error, "You already have an active request for this book"
   - Duplicate active issue? Check: `SELECT * FROM issued_books WHERE student_id = ? AND book_id = ? AND returned_at IS NULL`
   - If duplicate: Show error, "You already have this book issued"
4. If checks pass: Display confirmation modal:
   - Book title, author
   - "This book will be requested. Once approved, you'll be notified."
5. Student clicks "Confirm Request"
6. System creates book_request record:
   - student_id
   - book_id
   - status = "pending"
   - requested_at = current timestamp
   - version = 1
7. Send notification to staff/admin:
   ```
   Subject: New Book Request - [Book Title]
   
   [Admin Name],
   
   A new book request has been submitted.
   
   Student: [Name] ([ID])
   Book: [Title] by [Author]
   Availability: [X copies available]
   Request Date: [date]
   
   [Link to review request]
   ```
8. Send notification to student:
   ```
   Subject: Book Request Submitted - [Book Title]
   
   Dear [Student Name],
   
   Your request for "[Book Title]" has been received.
   
   We will notify you once the request is approved or rejected.
   
   Current Status: Pending
   ```
9. Log activity: "Book requested"
10. Display success message
11. Update request count in dashboard

---

### 📋 View/Cancel My Requests

**Trigger:** Student clicks "My Requests" in menu  
**Path:** `/student/my-requests`

**Steps:**

1. Student navigates to "My Requests"
2. System displays all requests for this student:
   - `SELECT * FROM book_requests WHERE student_id = ? ORDER BY created_at DESC`
3. Requests displayed in table/card view:
   - Book title
   - Request date
   - Status: Pending, Approved, Rejected, Issued, Returned, Cancelled
   - Status badge color:
     - Gray: Pending
     - Green: Approved
     - Red: Rejected
     - Blue: Issued
     - Purple: Returned
     - Orange: Cancelled
   - Action buttons: View, Cancel (if pending)
4. Student can filter by status, search by book title
5. Student clicks request to view details:
   - Book info
   - Request date
   - Current status
   - Status history (timeline)
   - If approved: "Pick up at library desk" message
   - If rejected: Rejection reason
6. To cancel request:
   - Student clicks "Cancel Request" button (only available if status = pending)
   - System displays confirmation modal
   - Student confirms
   - System updates request:
     - status = "cancelled"
     - cancelled_at = current timestamp
     - cancelled_by = "student"
   - Log activity
   - Send notification to admin
   - Display success message

---

### 📚 View My Issued Books

**Trigger:** Student clicks "My Books" in menu  
**Path:** `/student/my-books`

**Steps:**

1. Student navigates to "My Books"
2. System queries issued_books table:
   - `SELECT * FROM issued_books WHERE student_id = ? AND returned_at IS NULL`
3. System displays active issued books in card/table format:
   - Book cover image
   - Title, author
   - Issue date
   - Due date
   - **Days remaining** (calculated):
     - If due_date > today: Green "14 days remaining"
     - If 1-3 days: Yellow "2 days remaining"
     - If due_date < today: Red "2 days overdue"
   - Status: Active, Overdue, Due Soon
   - "View Details" button
4. Student can:
   - Sort by: Due Date, Title, Author
   - Filter by: All, Due Soon (< 3 days), Overdue
5. Clicking book shows details:
   - Full book info
   - Issue date, due date
   - Time remaining countdown
   - Library contact for renewal inquiry
6. System also shows "Returned Books" history:
   - Student clicks "Returned Books" tab
   - Query: `SELECT * FROM issued_books WHERE student_id = ? AND returned_at IS NOT NULL ORDER BY returned_at DESC LIMIT 20`
   - Display list with:
     - Book title, author
     - Issue date, return date
     - Condition returned in
     - Any fines associated

---

### 💰 View My Fines

**Trigger:** Student clicks "My Fines" in menu  
**Path:** `/student/my-fines`

**Steps:**

1. Student navigates to "My Fines"
2. System queries fines table:
   - `SELECT * FROM fines WHERE student_id = ? ORDER BY created_at DESC`
3. System displays summary stats:
   - Total outstanding fines: `SUM(amount) WHERE status = 'pending'`
   - Total paid fines: `SUM(amount) WHERE status = 'paid'`
   - Total waived fines: `SUM(amount) WHERE status = 'waived'`
4. System displays fines in table/card format:
   - Fine ID, amount
   - Related book (if applicable)
   - Fine type: Overdue, Damage, Loss
   - Fine reason (additional details)
   - Status badge: Pending (red), Paid (green), Waived (gray)
   - Created date
   - Action buttons: View, Download Receipt (if paid), Pay (if pending)
5. Student filters by:
   - Status: All, Pending, Paid, Waived
   - Date range
6. Clicking fine shows:
   - Detailed fine info
   - Related book details
   - Calculation breakdown
   - Payment history
   - Receipt (if paid)
7. To pay fine:
   - Student clicks "Pay Fine" button
   - System integrates with payment gateway (if configured)
   - Or: Displays message "Contact library staff to pay"
8. To download receipt:
   - Student clicks "Download Receipt"
   - System generates PDF:
     - Receipt header
     - Fine details
     - Payment info
     - Library stamp/signature
     - Receipt number
   - Browser downloads PDF
9. System provides:
   - Print button
   - Email receipt button
   - Share receipt option

---

### 📄 Download Fine Receipt

**Trigger:** Student clicks "Download Receipt" on paid fine  
**Path:** GET `/student/fines/{fine}/receipt`

**Steps:**

1. Student navigates to fine or fine detail page
2. Clicks "Download Receipt" button
3. System verifies:
   - Fine belongs to student
   - Fine status = "paid"
   - Receipt exists
4. System generates or retrieves PDF:
   - If generated once: Use cached PDF
   - If not: Generate on-the-fly using PDF library
5. PDF contains:
   ```
   ═════════════════════════════════════════
          LIBRARY FINE PAYMENT RECEIPT
   ═════════════════════════════════════════
   
   Receipt #: YYYY-MM-00001
   Date: April 6, 2026
   
   STUDENT INFORMATION
   Name: John Doe
   Student ID: 2023-001
   Email: john@university.edu
   
   FINE DETAILS
   Fine ID: 42
   Book: Laravel Best Practices by John Doe
   ISBN: 978-0-123456-78-9
   Fine Type: Overdue
   Reason: Book returned 5 days late
   
   AMOUNT DETAILS
   Original Amount: $5.00
   Penalties/Adjustments: $0.00
   Total Fine: $5.00
   Amount Paid: $5.00
   
   PAYMENT INFORMATION
   Payment Date: April 5, 2026
   Payment Method: Bank Transfer
   Payment Reference: TRANS-2026-04-001
   Paid By: John Doe
   Marked Paid By: Admin Staff
   
   Waiver Information: N/A
   
   ═════════════════════════════════════════
   This is an official receipt. Keep for your records.
   For support, contact: library@university.edu
   ═════════════════════════════════════════
   ```
6. Browser triggers download:
   - Filename: `receipt_YYYY_MM_XXXXX.pdf`
   - Content-Type: application/pdf
7. Log activity: "Fine receipt downloaded"

---

### 👤 Update Profile

**Trigger:** Student clicks "Profile Settings" in menu  
**Path:** `/student/profile`

**Steps:**

1. Student clicks "My Profile" or "Settings"
2. System displays profile edit form with current values:
   - First Name
   - Last Name
   - Email (read-only, cannot change)
   - Phone
   - Profile photo (upload field)
   - Address
   - City, State, Postal Code
   - Gender
   - Department (read-only)
   - Batch (read-only)
   - Semester (read-only)
3. Student can modify non-read-only fields
4. Optional: Upload/change profile photo:
   - File input (JPG, PNG, max 5MB)
   - Image preview
   - Crop/resize tool (optional)
5. Student clicks "Update Profile"
6. System validates:
   - Phone format valid (if changed)
   - Name fields not empty
   - Photo size/format (if uploaded)
7. System updates user record:
   - Update personal_info fields
   - If photo uploaded:
     - Store in storage/app/public/avatars/
     - Filename: `student_{student_id}.{ext}`
     - Update photo_path in users table
     - Delete old photo if exists
8. Send confirmation email
9. Log activity: "Profile updated"
10. Display success message
11. Clear form or refresh data

---

### 🔐 Change Password

**Trigger:** Student clicks "Change Password" in profile  
**Path:** PUT `/student/settings/password`

**Steps:**

1. Student navigates to profile settings
2. Clicks "Change Password" section
3. System displays form with:
   - Current password field
   - New password field
   - Confirm new password field
   - Password requirements hint
4. Student enters current password to verify identity
5. Student enters new password (must meet requirements)
6. Student confirms new password
7. Clicks "Save"
8. System validates:
   - Current password correct (bcrypt compare)
   - New password meets requirements
   - New password ≠ current password
   - Confirm password matches new password
9. System updates user record:
   - Hash new password with bcrypt
   - Set password_changed_at = current timestamp
10. System invalidates all other sessions (force logout):
    - Delete all session records for this user except current session
    - User remains logged in on current device only
11. Send confirmation email:
    ```
    Subject: Your Password Has Been Changed
    
    Your password was successfully changed on [date] [time].
    
    If you didn't make this change, please contact support immediately.
    ```
12. Log activity: "Password changed"
13. Display success message
14. Refresh form

---

## System Automated Actions

### 💻 Calculate Overdue Fines (Scheduled Job)

**Trigger:** Scheduled command (daily at 2:00 AM)  
**Command:** `php artisan fines:calculate-overdue`  
**Frequency:** Daily

**Steps:**

1. Scheduler triggers job at configured time
2. System queries overdue issued books:
   - `SELECT * FROM issued_books WHERE due_date < NOW() AND returned_at IS NULL`
3. For each overdue book:
   - Calculate days overdue: `DATEDIFF(NOW(), due_date)`
   - Query fine settings:
     - daily_rate ($ per day)
     - max_fine (maximum cap)
     - grace_period_days (days before fine starts)
     - Apply grace period:
       - If days_overdue ≤ grace_period: Skip fine
       - If days_overdue > grace_period: Calculate fine
   - Calculate base fine:
     - `fine_amount = (days_overdue - grace_period) × daily_rate`
     - Cap fine at max_fine if needed
   - Check for existing fine for this book:
     - `SELECT * FROM fines WHERE issued_book_id = ? AND fine_type = 'overdue'`
     - If fine exists: Skip (fine already created)
   - Create new fine record:
     - student_id, book_id, issued_book_id
     - fine_type = "overdue"
     - amount = calculated_fine
     - reason = "Book overdue by X days"
     - status = "pending"
     - created_at = current timestamp
4. For each created fine:
   - Send notification to student (optional, can batch)
   - Update student's outstanding fine amount cache
5. Log batch job result:
   - Fines created: X
   - Total amount: $Y
   - Skipped (already have fine): Z
6. Send admin notification (optional):
   - Summary of fines calculated
   - Total amount
   - Students notified

**Response Log:**
```
[2026-04-06 02:00:01] Fines::CalculateOverdue - Started
[2026-04-06 02:00:05] Found 45 overdue books
[2026-04-06 02:00:08] Created 42 fines
[2026-04-06 02:00:08] Skipped 3 (already have fine or grace period)
[2026-04-06 02:00:09] Total fine amount: $245.50
[2026-04-06 02:00:10] Fines::CalculateOverdue - Completed
```

---

### 📧 Send Email Notifications (Queued Job)

**Trigger:** Various events, queued job  
**Queue:** emails  
**Retry:** 3 attempts

**Steps:**

1. Event occurs (e.g., book requested, fine created)
2. System creates notification job:
   - Notification type (e.g., "BookRequested")
   - Recipient user ID
   - Data (book details, fine details, etc.)
   - Job queued to "emails" queue
3. Queue worker picks up job
4. System loads notification template:
   - `app/Mail/BookRequestedNotification.php` or similar
5. System personalizes email:
   - Find user by ID
   - Load template
   - Substitute variables:
     - {{user_name}}, {{user_email}}
     - {{book_title}}, {{book_author}}
     - {{fine_amount}}, {{due_date}}
     - Etc.
6. System renders HTML email using Blade template
7. System configures email (SMTP):
   - From: library@university.edu
   - To: student@university.edu
   - Subject: [Generated from template]
   - Headers: Authentication, tracking (optional)
8. System sends via SMTP provider (Gmail, SendGrid, etc.)
9. If send succeeds:
   - Mark job as complete
   - Log email delivery
10. If send fails:
    - Retry up to 3 times with exponential backoff
    - If all retries fail: Move to failed queue
    - Log failure with reason

**Notification Types:**

- **BookRequestApproved** - Request approved
- **BookRequestRejected** - Request rejected with reason
- **BookIssued** - Book issued with due date
- **BookOverdue** - Notification that book is overdue
- **FineCreated** - New fine created
- **FineReminder** - Reminder to pay fine
- **BookReturned** - Confirmation of return
- **AccountLocked** - Notification of account lockout with unlock link
- **PasswordReset** - Password reset link
- **WelcomeNewUser** - Welcome email for new staff/student

---

### 🔒 Check Account Lockout (On Login)

**Trigger:** During login attempt  
**Path:** Login authentication middleware

**Steps:**

1. User attempts login with email/password
2. System loads user by email
3. System checks lockout status:
   - `SELECT locked_until FROM users WHERE email = ?`
4. If locked_until is NULL:
   - Account not locked, proceed to password check
5. If locked_until is not NULL:
   - Check if lockout has expired:
     - Current time > locked_until?
   - If lockout expired:
     - Automatically unlock:
       - Set locked_until = NULL
       - Set failed_login_attempts = 0
       - Proceed to password check
   - If lockout still active:
     - Display error: "Account locked. Try again at [time] or click here to unlock."
     - Provide link to unlock page that sends unlock email
     - Return to login page
     - Log failed login attempt
6. If password authentication fails:
   - Check failed_login_attempts count
   - Increment failed_login_attempts
   - If failed_login_attempts ≥ threshold (default: 5):
     - Lock account:
       - Set locked_until = NOW() + lockout_duration (default: 30 minutes)
       - Send suspicious activity notification to user
     - Log suspicious activity
     - Display message: "Account locked due to repeated failed attempts"
   - If failed_login_attempts < threshold:
     - Display error: "Invalid credentials. X attempts remaining"
     - Log failed attempt with IP

---

### ✉️ Verify Email Address (On Registration)

**Trigger:** User completes registration  
**Path:** Registration controller

**Steps:**

1. User completes registration form
2. System creates user record with:
   - email_verified_at = NULL (not verified)
   - email_verification_token = random token
3. System generates verification link:
   - Token (signed, 48-hour expiry)
   - Link: `/verify-email?token={token}`
4. System sends verification email:
   ```
   Subject: Verify Your Email Address
   
   Please verify your email by clicking the link below:
   
   [Verification Link - valid for 48 hours]
   ```
5. System queues email to "emails" queue
6. User receives email
7. User clicks verification link
8. System verifies token:
   - Signature valid?
   - Token not expired?
   - Token matches user?
9. If valid:
   - Update user:
     - Set email_verified_at = current timestamp
     - Set email_verification_token = NULL
   - Allow full account access
   - Send welcome confirmation
10. If invalid:
    - Display error
    - Offer to resend verification email
11. Unverified users can:
    - View some features (if configured)
    - But not access full system until verified

---

### 📬 Queue Reminder Emails (Scheduled Job)

**Trigger:** Scheduled job (daily at 9:00 AM)  
**Command:** `php artisan notifications:queue-reminders`  
**Frequency:** Daily

**Steps:**

1. Scheduler triggers job
2. Query for pending fine reminders needed:
   - `SELECT * FROM fines WHERE status = 'pending' AND reminder_sent_at IS NULL`
   - Or: Get fines where last_reminded < now - 7 days (weekly reminders)
3. For each pending fine:
   - Create notification job:
     - Type: "FineReminder"
     - Student: fine.student_id
     - Data: Fine amount, due date, payment link
   - Queue to "emails" queue
   - Update fine: Set reminder_sent_at = now, reminder_count++
4. Query for due date notifications:
   - Books due in next 3 days
   - Issued books where due_date = today + 3
5. For each book due soon:
   - Create notification job:
     - Type: "BookDueSoon"
     - Student: issued_book.student_id  
     - Data: Book title, due date, renewal link
   - Queue notification
6. Query for overdue notifications (not yet sent):
   - Overdue books where student hasn't been notified today
7. For each overdue:
   - Create notification job
   - Queue notification
8. Log batch result:
   - Reminders queued: X
   - Overdue notifications: Y
   - Etc.
9. System processes queued jobs throughout the day

---

## Complete Workflows

### 🔄 Complete Book Request Workflow

```
STUDENT INITIATES REQUEST
├─ Student searches for book
├─ Student views book details
└─ Student clicks "Request Book"
   │
SYSTEM VALIDATES
├─ Student account active? ✓
├─ No duplicate active request? ✓
├─ No duplicate active issue? ✓
└─ Creates request record (status: pending)
   │
NOTIFICATIONS SENT
├─ Student: "Request received, pending approval"
└─ Staff: "New request needs approval"
   │
STAFF APPROVES REQUEST
├─ Staff navigates to "Book Requests"
├─ Staff reviews pending requests
├─ Staff selects request
├─ Staff clicks "Approve"
└─ Staff can:
   ├─ Auto-issue book (creates issued_book record)
   │  └─ Notification: "Book issued, due [date]"
   └─ Approve only (staff issues later)
      └─ Notification: "Request approved, ready for pickup"
   │
SYSTEM UPDATES
├─ Request status: pending → approved/issued
├─ If issued: Decreases available_copies
├─ Creates activity log entry
└─ Decreases request queue position
   │
STUDENT NOTIFIED
├─ Email: Approval/issuance notification
├─ Dashboard: Request shows "Approved" or "Issued"
└─ Shows due date if issued
```

---

### 💰 Complete Overdue Fine Workflow

```
SCHEDULED JOB RUNS (Daily 2 AM)
├─ Query all overdue issued books
├─ For each book overdue:
│  ├─ Calculate days overdue
│  ├─ Check grace period
│  ├─ Calculate fine (daily_rate × days)
│  ├─ Cap at max_fine
│  └─ Create fine record (status: pending)
└─ Log results
   │
SYSTEM QUEUES NOTIFICATIONS
├─ For each new fine:
│  └─ Queue email notification to student
└─ Queue batch admin summary
   │
STUDENT NOTIFIED
├─ Email received: "Fine notification: $X overdue"
├─ Student logs in to dashboard
├─ Fine appears in "My Fines" section
└─ Shows: Amount, reason, due date
   │
STUDENT VIEWS FINE DETAILS
├─ Student: "My Fines" → finds fine
├─ Sees breakdown:
│  ├─ Days overdue: 5
│  ├─ Daily rate: $1.00
│  ├─ Grace period applied: -2 days
│  ├─ Base fine: 3 × $1.00 = $3.00
│  └─ Total: $3.00
└─ Options: Pay (if gateway), Download receipt
   │
STAFF/ADMIN MANAGES FINE
├─ Option 1: Student pays via gateway
│  └─ System marks fine paid automatically
├─ Option 2: Manual payment
│  ├─ Staff receives payment
│  ├─ Staff marks fine as paid
│  └─ System generates receipt
└─ Option 3: Admin waives fine
   ├─ Admin enters waiver reason
   └─ Fine status: waived
   │
FINE RESOLVED
├─ Student notified: "Fine payment received" or "Fine waived"
├─ Receipt generated/sent
├─ Fine removed from pending list
└─ Activity logged
```

---

### 🔐 Complete Account Lockout & Recovery

```
STUDENT FAILS LOGIN 5 TIMES
├─ Attempt 1: "Invalid credentials"
├─ Attempt 2: "Invalid credentials"
├─ Attempt 3: "Invalid credentials"
├─ Attempt 4: "Invalid credentials (1 attempt remaining)"
└─ Attempt 5: LOCKED
   │
SYSTEM LOCKS ACCOUNT
├─ Set locked_until = now + 30 minutes
├─ Set failed_login_attempts = 5
├─ Log suspicious activity with IP address
└─ Queue security notification email
   │
STUDENT RECEIVES UNLOCK EMAIL
├─ Email: "Suspicious activity detected"
├─ Contains: 24-hour unlock link
├─ Contains: Security tips
└─ Message: "If not you, ignore this"
   │
STUDENT CLICKS UNLOCK LINK
├─ System verifies token
├─ System checks lockout status:
│  ├─ If still locked: Unlock account
│  │  ├─ Set locked_until = NULL
│  │  ├─ Set failed_login_attempts = 0
│  │  └─ Notify student: "Account unlocked"
│  └─ If unlocked (30 min passed): Already available
└─ Student can retry login
   │
STUDENT RETRIES LOGIN
├─ System checks:
│  └─ locked_until is NULL ✓
├─ Authenticate password
├─ On success: Normal login flow
└─ Session created, dashboard shown
   │
ALTERNATIVE: ADMIN UNLOCK
├─ Admin sees locked account in dashboard alert
├─ Admin navigates to "Account Locks"
├─ Admin selects student
├─ Admin clicks "Unlock"
├─ Confirmation modal shown
├─ Admin confirms
├─ System unlocks account
├─ Optional: Send notification to student
└─ Student can retry login
```

---

**Last Updated:** April 6, 2026  
**Status:** ✅ Complete Reference Document
