# Library Management System - Use Case Diagram

**Date Created:** April 6, 2026  
**Author:** System Design Team  
**Project:** Library Management System V4

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Actors](#actors)
3. [Admin Use Cases](#admin-use-cases)
4. [Staff Use Cases](#staff-use-cases)
5. [Student Use Cases](#student-use-cases)
6. [System Use Cases](#system-use-cases)
7. [Use Case Relationships](#use-case-relationships)
8. [Key Cross-Actor Interactions](#key-cross-actor-interactions)

---

## Overview

The Library Management System is a role-based web application designed to manage library operations. The use case diagram illustrates all possible interactions between four main actor types and the system:

- **Administrators** - Full system access and configuration
- **Library Staff** - Daily circulation and operational tasks
- **Students** - Book borrowing and request management
- **System** - Automated background processes

The diagram is organized hierarchically, with parent use cases containing child use cases that represent more specific operations.

---

## Actors

### 👤 Administrator

**Who:** System administrators and library management personnel  
**Responsibility:** Overall system governance, configuration, and oversight  
**Access Level:** Full access to all system functions

**Key Permissions:**
- Add, modify, and remove any entity (books, users, students)
- Configure system settings and library policies
- View comprehensive reports and audit logs
- Manage user account lockouts and security
- Override student borrowing rules through privilege settings
- Process all types of requests and transactions

### 👤 Library Staff

**Who:** Librarians, circulation desk personnel, and support staff  
**Responsibility:** Day-to-day library operations and customer service  
**Access Level:** Limited to operational and management functions

**Key Permissions:**
- Manage daily book circulation (issue, return, process returns)
- Process book requests (approve, reject, bulk update)
- Manage fines and send payment reminders
- Search and manage student accounts
- Create and manage book categories
- Submit book deletion requests (admin must approve)
- Cannot delete books directly; must request deletion

### 👤 Student

**Who:** Library patrons (students of the institution)  
**Responsibility:** Borrowing and managing personal library interactions  
**Access Level:** Limited to self-service functions

**Key Permissions:**
- Search and filter books from the catalog
- Submit and cancel book requests
- View personal borrowing history
- Manage personal fines and download receipts
- Update personal profile and password
- Upload profile photo
- View personalized notifications

### ⚙️ System

**Who:** Automated background jobs and scheduled tasks  
**Responsibility:** Maintain system integrity and automate routine operations  
**Access Level:** Full access to backend operations

**Key Operations:**
- Calculate overdue fines automatically
- Trigger and queue email notifications
- Monitor account lockout status
- Verify email addresses
- Send reminder emails for pending payments

---

## Admin Use Cases

### 📊 View Dashboard
Displays system overview with key metrics and statistics.

### 📚 Manage Book Catalog
**Parent use case** for all book management operations.

**Included Use Cases:**
- **Add Book** - Create new book entries with ISBN, author, publisher, condition, quantity
- **Edit Book** - Modify existing book information
- **Delete Book** - Remove book from system (only if no active issues/requests)
- **Search Books** - Find books by various criteria
- **Create Category** - Add new book categories for organization

### 👥 Manage User Accounts
**Parent use case** for user and staff account management.

**Included Use Cases:**
- **Create User** - Add new admin or staff members with invitation email
- **Update User** - Modify user information and roles
- **Delete User** - Remove user accounts from system
- **Reset User Password** - Force password reset on next login
- **Manage Account Locks** - Monitor and unlock locked accounts
  - **Unlock Account** - Manually unlock a single locked account
  - **Unlock All Locked Accounts** - Bulk unlock all locked accounts

### 🎓 Manage Students
**Parent use case** for comprehensive student management.

**Included Use Cases:**
- **Create Student** - Add new student records with department, batch, semester, contact details
- **Update Student** - Modify student profile information
- **View Student Profile** - Display student details and history
- **Activate Student** - Enable inactive student account
- **Deactivate Student** - Disable active student account
- **Change Student Role** - Modify student's role in the system
- **Set Student Privileges** - Override global borrowing rules for individual students (e.g., extend due dates, increase borrowing limit)
- **View Student Fines** - Display all fines associated with a student
- **Generate Fine Receipt** - Create downloadable fine payment receipt

### 📋 Process Book Requests
**Parent use case** for handling student book requests.

**Included Use Cases:**
- **Approve Request** - Grant student's book request (issue book to student)
- **Reject Request** - Deny student's book request with optional reason
- **Bulk Update Requests** - Process multiple requests at once (approve/reject)

### 💳 Manage Transactions
**Parent use case** for book circulation operations.

**Included Use Cases:**
- **Issue Books** - Record book issuance to student with due date
- **Return Books** - Record book return from student, including condition tracking

### 💰 Manage Fines
**Parent use case** for financial penalty management.

**Included Use Cases:**
- **View Fines** - Display all fines with status (pending, paid, waived)
- **Mark Fine as Paid** - Record fine payment manually
- **Waive Fine** - Forgive fine amount (requires reason)
- **Adjust Fine Amount** - Manually change fine amount
- **View Fine History** - Display audit trail of changes
- **Send Fine Email** - Manually trigger reminder email to student
- **Bulk Process Fines** - Mark multiple fines as paid/waived simultaneously

### 📈 View Reports
**Parent use case** for generating system reports.

**Included Use Cases:**
- **Inventory Report** - Books by condition, category, availability
- **Transaction Report** - Issue/return activity summaries
- **Fine Report** - Fine collection and arrears data
- **User Report** - User account statistics and activity
- **Overdue Items Report** - Books past due date with associated students

### 📝 View Activity Logs
Display audit trail of all system actions with timestamp, user, IP address, and action details.

### ⚙️ Configure Settings
**Parent use case** for system configuration.

**Included Use Cases:**
- **Update Library Branding** - Configure library name, logo, and appearance
- **Update Fine Settings** - Set per-day fine amount, max fine, grace period, penalties
- **Update Security Settings** - Configure account lockout policies, password requirements, session timeouts

### 🔔 View Notifications
Display notifications for events like new requests, returns, overdue books, system alerts.

---

## Staff Use Cases

### 📊 View Dashboard
Displays staff-focused metrics: upcoming tasks, recent requests, overdue books, pending fines.

### 📚 Manage Book Catalog
**Parent use case** identical to Admin with one exception.

**Included Use Cases:**
- **Add Book** - Create new book entries
- **Edit Book** - Modify existing book information
- **Request Book Deletion** - Submit deletion request to admin (cannot delete directly)
- **Search Books** - Find books by various criteria
- **Create Category** - Add new book categories

### 📤 Issue Books
**Parent use case** for book issuance operations (daily task).

**Included Use Cases:**
- **Search Student** - Find student by ID, name, or email
- **Select Available Books** - Show available copies of requested book
- **Create Issue Transaction** - Record issuance with due date based on fine settings or student privileges

### 📥 Return Books
**Parent use case** for book return operations (daily task).

**Included Use Cases:**
- **Search Issued Books** - Find books currently issued to student
- **Process Return Transaction** - Record return and update inventory
- **Handle Damaged/Lost Items** - Apply fine penalties if applicable

### 📋 Process Book Requests
**Parent use case** for managing student requests.

**Included Use Cases:**
- **Approve Request** - Grant student's book request
- **Reject Request** - Deny student's book request with reason
- **Bulk Update Requests** - Process multiple requests simultaneously

### 💰 Manage Fines
**Parent use case** (Staff has subset of Admin capabilities).

**Included Use Cases:**
- **View Fines** - Display fines with status
- **Mark Fine as Paid** - Record manual payment
- **Waive Fine** - Forgive fine amount with reason
- **Send Fine Email** - Trigger reminder notification
- **Bulk Process Fines** - Handle multiple fines at once

### 🎓 Manage Student Accounts
**Parent use case** for student account operations.

**Included Use Cases:**
- **View Student Profile** - Display student details and borrowing history
- **Activate Student** - Re-enable inactive account
- **Deactivate Student** - Disable active account

### 📝 View Activity Logs
Display audit trail of staff actions and events.

### ⚙️ Update Profile Settings
**Parent use case** for personal account management.

**Included Use Cases:**
- **Update Profile Info** - Change personal information
- **Change Password** - Update account password
- **Upload Profile Photo** - Add or update profile picture

### 🔔 View Notifications
Display notifications for assigned tasks, approvals, and system events.

---

## Student Use Cases

### 📊 View Dashboard
Displays student's personal metrics: issued books, pending requests, outstanding fines, new notifications.

### 🔍 Search Books
**Parent use case** for book discovery.

**Included Use Cases:**
- **Filter by Category/Author** - Narrow search results by criteria
- **View Book Details** - Display book information including availability

### 📥 Request Books
**Parent use case** for book reservation.

**Included Use Cases:**
- **Submit Book Request** - Create reservation request (if not already issued or requested)

### 📋 My Requests
**Parent use case** for managing personal requests.

**Included Use Cases:**
- **View My Requests** - Display all requests and their status
- **Cancel Pending Request** - Withdraw request while still pending

### 📚 My Books
**Parent use case** for tracking borrowed items.

**Included Use Cases:**
- **View Current Books** - Display currently issued books with due dates
- **View Returned Books** - Display borrowing history of returned items
- **Check Due Date** - View specific book's due date and days remaining

### 💰 My Fines
**Parent use case** for fine management.

**Included Use Cases:**
- **View My Fines** - Display pending, paid, and waived fines
- **Pay Fine** - Submit payment (if payment gateway integrated)
- **Download Fine Receipt** - Generate PDF receipt for paid fines

### 👤 My Profile
**Parent use case** for personal account management.

**Included Use Cases:**
- **Update Profile Info** - Modify personal details (address, phone, email)
- **Change Password** - Update account password
- **Upload Profile Photo** - Add or update profile picture

### 🔔 View Notifications
Display notifications: request approvals/rejections, book issues, due date reminders, fine alerts.

---

## System Use Cases

### 💻 Calculate Overdue Fines
**Trigger:** Scheduled daily job  
**Responsibility:** Identify overdue books and calculate fines based on:
- Days overdue
- Per-day fine amount configured in settings
- Maximum fine cap
- Penalties for damaged/lost conditions
- Grace period exemptions

### 📧 Send Email Notifications
**Trigger:** Event-based and scheduled  
**Responsibility:** Queue and send emails for:
- Request approvals/rejections
- Book issue confirmation
- Due date reminders
- Overdue notifications
- Fine notifications and payment reminders
- Account security alerts

### 🔒 Check Account Lockout
**Trigger:** During login attempt, after X failed attempts  
**Responsibility:** Monitor and enforce account lockout policy:
- Track failed login attempts
- Lock account after threshold
- Queue security notification
- Enable admin unlock

### ✉️ Verify Email Address
**Trigger:** During user registration  
**Responsibility:** Validate email ownership:
- Send verification link
- Confirm email activation
- Prevent unverified accounts from full access

### 📬 Queue Reminder Emails
**Trigger:** Scheduled job, admin request  
**Responsibility:** Queue email reminders for:
- Pending fines
- Paid fine confirmations
- Waived fine notifications
- Bulk notification campaigns

---

## Use Case Relationships

### Include Relationships
An "includes" relationship indicates that a parent use case always invokes child use cases.

```
Example:
  Manage Students (parent) --includes--> Create Student (child)
  Manage Students (parent) --includes--> Update Student (child)
  Manage Students (parent) --includes--> View Student Profile (child)
```

All parent use cases in this system use "includes" relationships to show their child operations.

### Extend Relationships
Not explicitly shown in this diagram but conceptually:
- "Cancel Request" extends "My Requests" (optional when viewing requests)
- "Handle Damaged/Lost Items" extends "Return Books" (conditional on item condition)

### Association Relationships
Shown as arrows between actor and use case indicating "uses."

---

## Key Cross-Actor Interactions

### 1. Book Request Workflow
```
Student submits request
    ↓
Staff/Admin approves or rejects
    ↓
If approved: Staff issues book to student
    ↓
Student receives issued book with due date
    ↓
System calculates overdue fine if not returned by due date
    ↓
Staff/Admin marks fine as paid or waived
```

### 2. Overdue Fine Workflow
```
System calculates overdue fines (daily job)
    ↓
System queues reminder emails
    ↓
Staff/Admin sends fine notifications (optional manual)
    ↓
Student views fines and pays/Admin marks as paid
    ↓
System generates receipt
    ↓
Student downloads receipt/Admin views fine history
```

### 3. Book Management Workflow
```
Admin/Staff adds or edits book
    ↓
Book appears in student catalog
    ↓
Student searches and requests book
    ↓
Staff issues book from inventory
    ↓
Book availability decreases in system
    ↓
When copies depleted: book requests queue automatically
```

### 4. Account Lockout Workflow
```
Student fails login X times
    ↓
System locks account and queues security alert
    ↓
Admin receives notification of locked account
    ↓
Admin unlocks account manually
    ↓
Student can retry login
```

### 5. Student Privilege Override
```
Admin sets custom privilege for student
    ↓
When book is issued to privileged student:
    Due date = student's custom setting (not global setting)
    Borrowing limits = student's custom setting
    Fine rates = potentially student's custom setting
```

---

## Diagram Legend

| Color | Meaning |
|-------|---------|
| 🔵 Blue | Actor (Admin, Staff, Student) |
| 🔵 Cyan | System actor (background jobs) |
| 🔴 Pink | Primary use case (top-level) |
| 🟠 Orange | Sub-use case (included in parent) |
| 🟡 Yellow | Leaf use case (lowest level) |

| Symbol | Meaning |
|--------|---------|
| → | Actor uses use case |
| ← | Use case includes child use case |
| ⋯→ | System use case supports other use cases |

---

## System Business Rules Reflected in Use Cases

1. **Duplicate Prevention** - System prevents duplicate active requests/borrowings for same student-book pair
2. **Inventory Validation** - Books can only be issued if copies are available
3. **Fine Calculations** - Include grace period, penalties for damage/loss, max fine cap
4. **Role-Based Access** - Each actor has specific permissions and cannot exceed their role
5. **Audit Trail** - All actions logged with user, timestamp, IP, and metadata
6. **Email-First Notifications** - System queues emails for all major events
7. **Student Autonomy** - Students can only cancel pending requests, not approved ones
8. **Staff Limitation** - Staff cannot delete books; must request admin approval
9. **Privilege Override** - Admin can customize borrowing terms per student
10. **Account Security** - Failed logins trigger lockout; admin must manually unlock

---

## Future Enhancement Opportunities

1. **Payment Gateway Integration** - Allow direct payment from "Pay Fine" use case
2. **Book Renewal Extension** - Add "Renew Book" use case for students to extend due dates
3. **Wishlist** - Students request a "Add to Wishlist" use case
4. **Analytics Export** - Admin "Export Reports" use case with multiple formats
5. **SMS Notifications** - Extend "Send Notifications" to include SMS channel
6. **Book Reviews** - Student "Rate and Review" use case for peer recommendations
7. **Hold Expiration** - System "Release Expired Holds" automated use case
8. **Bulk Import** - Admin "Import Books from CSV" use case for bulk operations

---

## References

- **File Location:** `docs/use_case_diagram.mmd`
- **Format:** Mermaid flowchart syntax
- **Related Diagrams:**
  - Context Level Diagram: `context_level_diagram.mmd`
  - Entity Relationship Diagram: `ER_diagram.mmd`
  - DFD Diagrams: Data flow documentation in `docs/`

---

**Last Updated:** April 6, 2026  
**Status:** ✅ Complete
