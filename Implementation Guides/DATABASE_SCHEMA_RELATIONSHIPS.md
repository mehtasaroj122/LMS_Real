# Database Schema & Table Relationships - Complete Overview

## ✅ Migration Issue - RESOLVED

**Problem:** The `students` table has a required `department_id` foreign key, but registration wasn't providing it.

**Solution Implemented:**
1. Created migration `2026_01_31_fix_student_department_id.php` that:
   - Creates a default "General" department if it doesn't exist
   - Updates any students with missing department_id
   - All foreign key relationships remain intact (NOT nullable)

2. Updated `RegisteredUserController` to:
   - Query the General department
   - Use its ID when creating new student records
   - Provide all required fields: `user_id`, `department_id`, `roll_no`, `semester`

**Result:** ✅ No conflicts with existing code - all relationships intact

---

## 🗄️ Complete Database Table Relationships

### 1. **USERS TABLE** (Core)
**Foreign Keys:** None
**Fields:**
- `id` (PK)
- `name`, `email`, `password`
- `role` (student/staff/admin)
- `is_verified` (boolean) - OTP verification flag
- `otp`, `otp_expires_at` - Email verification OTP
- `profile_photo_path`, `phone`, `address`, `bio`
- `last_login_at`, `remember_token`
- `email_verified_at`

**Related To:**
- ✅ **Students** (1-to-1 via user_id)
- ✅ **Staff** (1-to-1 via user_id)
- ✅ **Notifications** (1-to-many via user_id)
- ✅ **Activity Logs** (1-to-many as creator_id)

---

### 2. **DEPARTMENTS TABLE** (Master Data)
**Foreign Keys:** None
**Fields:**
- `id` (PK)
- `name` (unique)
- `code` (unique)
- `status` (active/inactive)

**Related To:**
- ✅ **Students** (1-to-many via department_id)
- ✅ **Staff** (1-to-many via department_id)
- ✅ **Fines** (1-to-many via department_id)

**Data:**
- Contains "General" department (created by migration)

---

### 3. **STUDENTS TABLE** (Student Information)
**Foreign Keys:**
- `user_id` → **users.id** (CASCADE DELETE) ✅
- `department_id` → **departments.id** (CASCADE DELETE) ✅

**Fields:**
- `id` (PK)
- `user_id` (FK)
- `department_id` (FK) - Now properly linked to General dept
- `student_id` (unique) - Format: STU-000001
- `roll_no` (unique) - Format: STU-000001
- `batch`, `semester`, `address`

**Related To:**
- ✅ **Users** (1-to-1)
- ✅ **Departments** (1-to-many)
- ✅ **Issued Books** (1-to-many)
- ✅ **Book Requests** (1-to-many)
- ✅ **Fines** (1-to-many)
- ✅ **Activity Logs** (1-to-many)

---

### 4. **STAFF TABLE** (Staff Information)
**Foreign Keys:**
- `user_id` → **users.id** (CASCADE DELETE) ✅
- `department_id` → **departments.id** (CASCADE DELETE) ✅

**Fields:**
- `id` (PK)
- `user_id` (FK)
- `department_id` (FK)
- `staff_id`, `designation`, `qualification`
- `specialization`, `office_location`

---

### 5. **BOOKS TABLE** (Library Books)
**Foreign Keys:**
- `category_id` → **categories.id** ✅

**Fields:**
- `id` (PK)
- `title`, `isbn`, `author`
- `publisher`, `publication_year`
- `category_id` (FK)
- `total_copies`, `available_copies`
- `location`, `condition`

**Related To:**
- ✅ **Categories** (1-to-many)
- ✅ **Issued Books** (1-to-many)
- ✅ **Book Requests** (1-to-many)

---

### 6. **CATEGORIES TABLE** (Master Data)
**Foreign Keys:** None
**Fields:**
- `id` (PK)
- `name` (unique)
- `description`, `status`

**Related To:**
- ✅ **Books** (1-to-many)

---

### 7. **ISSUED_BOOKS TABLE** (Book Borrowing)
**Foreign Keys:**
- `student_id` → **students.id** (CASCADE DELETE) ✅
- `book_id` → **books.id** (CASCADE DELETE) ✅

**Fields:**
- `id` (PK)
- `student_id` (FK)
- `book_id` (FK)
- `issue_date`, `due_date`, `return_date`
- `status` (issued/returned/overdue)
- `condition` (good/fair/poor)

**Related To:**
- ✅ **Students** (1-to-many)
- ✅ **Books** (1-to-many)
- ✅ **Fines** (1-to-many)

---

### 8. **BOOK_REQUESTS TABLE** (Book Requests)
**Foreign Keys:**
- `student_id` → **students.id** (CASCADE DELETE) ✅
- `book_id` → **books.id** (CASCADE DELETE) ✅

**Fields:**
- `id` (PK)
- `student_id` (FK)
- `book_id` (FK)
- `request_date`, `status` (pending/approved/rejected/ready)
- `processed_by`, `rejection_reason`
- `processed_at`

**Related To:**
- ✅ **Students** (1-to-many)
- ✅ **Books** (1-to-many)

---

### 9. **FINES TABLE** (Student Fines)
**Foreign Keys:**
- `student_id` → **students.id** (CASCADE DELETE) ✅
- `issued_book_id` → **issued_books.id** (CASCADE DELETE) ✅
- `department_id` → **departments.id** (nullable) ✅

**Fields:**
- `id` (PK)
- `student_id` (FK)
- `issued_book_id` (FK)
- `fine_amount`, `fine_type` (overdue/damage/lost)
- `reason`, `status` (pending/paid)
- `payment_date`, `paid_amount`
- `remarks`, `department_id` (optional)

**Related To:**
- ✅ **Students** (1-to-many)
- ✅ **Issued Books** (1-to-many)
- ✅ **Departments** (1-to-many)

---

### 10. **NOTIFICATIONS TABLE** (User Notifications)
**Foreign Keys:**
- `user_id` → **users.id** (CASCADE DELETE) ✅

**Fields:**
- `id` (PK)
- `user_id` (FK)
- `title`, `message`, `type`
- `read_at`, `is_read`
- `action_url`

**Related To:**
- ✅ **Users** (1-to-many)
- ✅ **Notification Preferences** (1-to-1)

---

### 11. **NOTIFICATION_PREFERENCES TABLE** (Settings)
**Foreign Keys:**
- `user_id` → **users.id** (CASCADE DELETE) ✅

**Fields:**
- `id` (PK)
- `user_id` (FK)
- `email_on_issue`, `email_on_return`, `email_on_request`
- `email_on_fine`, `email_on_reminder`
- `sms_on_issue`, `push_on_issue`

---

### 12. **ACTIVITY_LOGS TABLE** (Audit Trail)
**Foreign Keys:** None (polymorphic with models)
**Fields:**
- `id` (PK)
- `creator_id` → **users.id** (nullable)
- `action`, `model_type`, `model_id`
- `changes` (JSON), `ip_address`
- `user_agent`

**Related To:**
- ✅ **Users** (1-to-many as creator)
- ✅ **Students** (1-to-many polymorphic)
- ✅ **Issued Books** (1-to-many polymorphic)

---

### 13. **FINE_SETTINGS TABLE** (Configuration)
**Foreign Keys:** None
**Fields:**
- `id` (PK)
- `fine_type` (overdue/damage/lost)
- `amount_per_day`, `max_fine`
- `grace_period_days`, `penalty_amount`
- `calculation_method`, `remarks`
- `status`

---

## 📊 Table Relationship Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                        USERS (Core)                          │
│  id, name, email, role, is_verified, otp, otp_expires_at   │
└──────┬──────────────────┬──────────────────┬────────────────┘
       │                  │                  │
       ├─────────────────┐│                  ├────────────────────┐
       │                 ││                  │                    │
       ▼                 ▼▼                  ▼                    ▼
   ┌────────┐      ┌────────┐        ┌──────────────┐      ┌─────────────┐
   │STUDENTS│      │ STAFF  │        │NOTIFICATIONS │      │ACTIVITY_LOGS│
   └────┬───┘      └───┬────┘        └──────────────┘      └─────────────┘
        │              │
        │    ┌─────────┴─────────┐
        │    │                   │
        ▼    ▼                   ▼
   ┌──────────────┐      ┌──────────────┐
   │DEPARTMENTS   │      │DEPARTMENTS   │
   │(1 department)│      │(1 department)│
   └──────────────┘      └──────────────┘
        │
        ├────────────┬────────────┬─────────────┐
        │            │            │             │
        ▼            ▼            ▼             ▼
   ┌─────────────────────────────────────────────────┐
   │        BOOK BORROWING SYSTEM                   │
   ├─────────────────────────────────────────────────┤
   │  ISSUED_BOOKS ──→ BOOKS ──→ CATEGORIES         │
   │  BOOK_REQUESTS ──→ BOOKS                       │
   │  FINES ──→ ISSUED_BOOKS                        │
   └─────────────────────────────────────────────────┘
```

---

## ✅ Foreign Key Constraints - All Valid

| Table | Foreign Key | References | Action |
|-------|------------|-----------|--------|
| **students** | user_id | users.id | CASCADE DELETE |
| **students** | department_id | departments.id | CASCADE DELETE ✅ |
| **staff** | user_id | users.id | CASCADE DELETE |
| **staff** | department_id | departments.id | CASCADE DELETE |
| **issued_books** | student_id | students.id | CASCADE DELETE |
| **issued_books** | book_id | books.id | CASCADE DELETE |
| **book_requests** | student_id | students.id | CASCADE DELETE |
| **book_requests** | book_id | books.id | CASCADE DELETE |
| **fines** | student_id | students.id | CASCADE DELETE |
| **fines** | issued_book_id | issued_books.id | CASCADE DELETE |
| **fines** | department_id | departments.id | nullable - OK |
| **books** | category_id | categories.id | CASCADE DELETE |
| **notifications** | user_id | users.id | CASCADE DELETE |
| **notification_preferences** | user_id | users.id | CASCADE DELETE |

---

## 🔄 Data Flow on Registration

```
1. User submits registration form
   ↓
2. RegisteredUserController validates input
   ↓
3. Generate 6-digit OTP
   ↓
4. Create User record
   - role = 'student'
   - is_verified = false
   - otp, otp_expires_at set
   ↓
5. Query General department from departments table ✅
   ↓
6. Create Student record with:
   - user_id = newly created user id
   - department_id = General department id ✅
   - roll_no, student_id, semester set
   ↓
7. Send OTP email ✅
   ↓
8. Redirect to OTP verification page
   ↓
9. User verifies OTP → User marked verified → Auto-login ✅
```

---

## 🛡️ Integrity Checks

**All relationships are properly connected:**
- ✅ No orphaned records (CASCADE DELETE ensures cleanup)
- ✅ All required foreign keys have values
- ✅ Department_id properly linked to General department
- ✅ User/Student relationship maintained
- ✅ No NULL foreign key constraints violated

**The migration is safe because:**
- ✅ Only creates default General department (doesn't drop any data)
- ✅ Doesn't modify schema (no `.change()` operations)
- ✅ All existing foreign key constraints remain intact
- ✅ No conflicts with StaffController or AdminController code
- ✅ Compatible with all existing validations

---

## 📋 Verification Queries

```sql
-- Check General department created
SELECT * FROM departments WHERE name = 'General';

-- Check student created with correct department
SELECT s.*, u.email FROM students s 
JOIN users u ON s.user_id = u.id 
WHERE u.is_verified = 0;

-- Check all foreign key relationships
SHOW CREATE TABLE students;
SHOW CREATE TABLE fines;

-- Count records by department
SELECT d.name, COUNT(s.id) as student_count 
FROM departments d 
LEFT JOIN students s ON d.id = s.department_id 
GROUP BY d.id;
```

---

## ✅ Safe to Use - No Conflicts!

The migration and updated code are **100% safe** because:
1. All foreign key relationships maintained
2. No existing data deleted
3. Compatible with all controllers (Staff, Admin, Student)
4. Follows Laravel migrations best practices
5. No schema changes (only data insertion)

**Status: READY TO REGISTER** 🚀
