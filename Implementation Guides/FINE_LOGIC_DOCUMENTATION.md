# Fine Calculation Logic Documentation

**Date:** January 26, 2026  
**Version:** 1.0  
**System:** Library Management System - Fine Module

---

## Table of Contents
1. [Overview](#overview)
2. [Fine Calculation Logic](#fine-calculation-logic)
3. [Configuration](#configuration)
4. [Examples](#examples)
5. [Files Created/Modified](#files-createdmodified)
6. [How to Use](#how-to-use)
7. [Database Schema](#database-schema)

---

## Overview

The fine calculation system automatically calculates and manages fines for overdue, lost, and damaged books based on the `fine_settings` table. The system is designed to:

- **Automatically calculate fines** when books are returned late
- **Apply penalties** for lost or damaged books
- **Enforce grace periods** before charging begins
- **Cap maximum fines** to prevent excessive charges
- **Track all fine transactions** with payment status
- **Schedule daily automated processing** of overdue books

---

## Fine Calculation Logic

### 1. Overdue Fine Calculation

**Formula:**
```
Days Late = Return Date - Due Date

If Days Late ≤ Grace Period Days:
    Fine Amount = रु 0 (No charge)
Else:
    Chargeable Days = Days Late - Grace Period Days
    Fine Amount = Chargeable Days × Per Day Fine
    
Final Amount = MIN(Fine Amount, Max Fine Amount)
```

**Parameters from `fine_settings` table:**
- `per_day_fine`: Amount charged per day of overdue (Default: रु 5.00)
- `grace_period_days`: Days allowed before fine starts (Default: 2 days)
- `max_fine_amount`: Maximum cap on total fine (Default: रु 500.00)
- `issue_duration_days`: Standard borrowing period (Default: 14 days)

### 2. Lost Book Penalty

**Formula:**
```
Fine Amount = lost_book_penalty (from fine_settings)
Status: lost
Remarks: "Lost book penalty"
```

**Parameters:**
- `lost_book_penalty`: Fixed penalty for lost books (Default: रु 1000.00)

### 3. Damaged Book Penalty

**Formula:**
```
Fine Amount = damaged_book_penalty (from fine_settings)
Status: damaged
Remarks: "Damaged book penalty"
```

**Parameters:**
- `damaged_book_penalty`: Fixed penalty for damaged books (Default: रु 250.00)

---

## Configuration

### Fine Settings Table
**Database Table:** `fine_settings`

```sql
CREATE TABLE fine_settings (
    id BIGINT PRIMARY KEY,
    per_day_fine DECIMAL(10, 2) DEFAULT 5.00,
    grace_period_days INT DEFAULT 2,
    max_fine_amount DECIMAL(10, 2) DEFAULT 500.00,
    lost_book_penalty DECIMAL(10, 2) DEFAULT 1000.00,
    damaged_book_penalty DECIMAL(10, 2) DEFAULT 250.00,
    issue_duration_days INT DEFAULT 14,
    max_books_per_student INT DEFAULT 5,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Fines Table
**Database Table:** `fines`

```sql
CREATE TABLE fines (
    id BIGINT PRIMARY KEY,
    issued_book_id BIGINT (Foreign Key),
    student_id BIGINT (Foreign Key),
    amount DECIMAL(10, 2),
    days_late INT,
    status ENUM('pending', 'paid', 'waived') DEFAULT 'pending',
    paid_on DATE,
    payment_method ENUM('cash', 'card', 'online'),
    remarks VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### IssuedBooks Table
**Database Table:** `issued_books`

```sql
-- Additional columns used for fine calculation:
fine_amount DECIMAL(10, 2)   -- Current fine amount on this book
due_date DATE                -- When book is due
return_date DATE             -- When book was returned (NULL if not returned)
status ENUM('issued', 'returned', 'lost', 'damaged', 'pending')
```

---

## Examples

### Example 1: Normal Overdue Fine

**Scenario:**
- Book due date: January 20, 2026
- Book returned: January 25, 2026
- Per day fine: रु 5
- Grace period: 2 days
- Max fine: रु 500

**Calculation:**
```
Days Late = Jan 25 - Jan 20 = 5 days
Days within grace period = 2 days
Chargeable days = 5 - 2 = 3 days
Fine amount = 3 × रु 5 = रु 15
Final fine = MIN(रु 15, रु 500) = रु 15
```

**Result:** Fine of **रु 15** is generated with status "pending"

---

### Example 2: Maximum Fine Cap

**Scenario:**
- Book due date: January 1, 2026
- Book returned: January 30, 2026
- Per day fine: रु 5
- Grace period: 2 days
- Max fine: रु 500

**Calculation:**
```
Days Late = Jan 30 - Jan 1 = 29 days
Days within grace period = 2 days
Chargeable days = 29 - 2 = 27 days
Fine amount = 27 × रु 5 = रु 135
Final fine = MIN(रु 135, रु 500) = रु 135
```

**Result:** Fine of **रु 135** is generated

---

### Example 3: Extremely Overdue with Cap

**Scenario:**
- Book due date: January 1, 2026
- Book returned: August 1, 2026 (Very late!)
- Per day fine: रु 5
- Grace period: 2 days
- Max fine: रु 500

**Calculation:**
```
Days Late = Aug 1 - Jan 1 = 212 days
Days within grace period = 2 days
Chargeable days = 212 - 2 = 210 days
Fine amount = 210 × रु 5 = रु 1050
Final fine = MIN(रु 1050, रु 500) = रु 500  ← CAPPED
```

**Result:** Fine of **रु 500** (capped) is generated

---

### Example 4: Lost Book

**Scenario:**
- Book marked as lost
- Lost book penalty: रु 1000
- Grace period: Not applicable

**Calculation:**
```
Fine amount = रु 1000 (Fixed)
Status = "lost"
Remarks = "Lost book penalty"
```

**Result:** Fine of **रु 1000** is generated immediately

---

### Example 5: Damaged Book

**Scenario:**
- Book returned damaged
- Damaged book penalty: रु 250
- Grace period: Not applicable

**Calculation:**
```
Fine amount = रु 250 (Fixed)
Status = "damaged"
Remarks = "Damaged book penalty"
```

**Result:** Fine of **रु 250** is generated immediately

---

### Example 6: Within Grace Period (No Fine)

**Scenario:**
- Book due date: January 20, 2026
- Book returned: January 21, 2026 (1 day late)
- Per day fine: रु 5
- Grace period: 2 days
- Max fine: रु 500

**Calculation:**
```
Days Late = Jan 21 - Jan 20 = 1 day
Grace period = 2 days
Since 1 day ≤ 2 days → No fine charged
Fine amount = रु 0
```

**Result:** **No fine** is generated, book marked as returned

---

## Files Created/Modified

### New Files Created

#### 1. **Fine Calculation Service**
**Path:** `app/Services/FineCalculator.php`

**Purpose:** Core fine calculation engine

**Methods:**
- `calculateFine(IssuedBook)` - Calculate fine amount for overdue book
- `applyFine(IssuedBook)` - Create or update fine record
- `applyLostBookPenalty(IssuedBook)` - Apply lost book penalty
- `applyDamagedBookPenalty(IssuedBook)` - Apply damaged book penalty
- `markAsPaid(Fine, paymentMethod)` - Mark fine as paid
- `getStudentPendingFines(studentId)` - Get total pending fines for student
- `getSettings()` - Get active fine settings

**Key Features:**
- Automatically loads active fine settings from database
- Handles grace periods and maximum fine caps
- Updates both Fine and IssuedBook records
- Thread-safe for concurrent processing

---

#### 2. **Fine Calculation Command**
**Path:** `app/Console/Commands/CalculateOverdueFines.php`

**Purpose:** Scheduled/manual command to process overdue fines

**Usage:**
```bash
php artisan fines:calculate-overdue
```

**What it does:**
- Finds all unreturned books past due_date
- Calculates fine for each overdue book
- Creates or updates fine records
- Displays summary of processed fines

**Example Output:**
```
Found 5 overdue books.
Fine applied: John Doe - रु 15 (5 days late)
Fine applied: Jane Smith - रु 50 (12 days late)
Fine applied: Mike Johnson - रु 100 (25 days late)
-----------------------------------
Total fines applied: 3
Total amount: रु 165
```

---

#### 3. **Fine Controller**
**Path:** `app/Http/Controllers/FineController.php`

**Purpose:** Handle all fine-related HTTP requests

**Endpoints:**
| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/admin/fines` | List all fines with pagination |
| GET | `/admin/fines/student/{id}/list` | Get fines for specific student |
| POST | `/admin/fines/{id}/mark-as-paid` | Mark fine as paid |
| POST | `/admin/fines/{id}/waive` | Waive fine with reason |
| GET | `/admin/fines/dashboard/summary` | Get fines dashboard data |
| GET | `/admin/fines/overdue/books` | List overdue books |

**Response Format (JSON):**
```json
{
  "success": true,
  "data": [...],
  "summary": {
    "total_pending": 5000,
    "total_collected": 15000,
    "total_waived": 500
  }
}
```

---

#### 4. **Console Kernel**
**Path:** `app/Console/Kernel.php`

**Purpose:** Schedule fine calculation to run automatically

**Schedule:**
- Runs daily at 2:00 AM
- Command: `fines:calculate-overdue`
- Prevents overlapping executions
- Runs on single server only

**Configuration:**
```php
$schedule->command('fines:calculate-overdue')
    ->dailyAt('02:00')
    ->name('calculate-overdue-fines')
    ->withoutOverlapping()
    ->onOneServer();
```

---

### Modified Files

#### 1. **IssuedBook Observer**
**Path:** `app/Observers/IssuedBookObserver.php`

**Changes Made:**
- Added FineCalculator import
- When book is returned: `applyFine()` called automatically
- When status changes to "lost": `applyLostBookPenalty()` called
- When status changes to "damaged": `applyDamagedBookPenalty()` called
- All fine events logged to ActivityLog

**Triggers:**
```php
// Trigger 1: Book returned (return_date set from NULL)
if ($oldReturnDate === null && $newReturnDate !== null) {
    $fine = $fineCalculator->applyFine($issuedBook);
}

// Trigger 2: Status changed to lost
if ($newStatus === 'lost' && $oldStatus !== 'lost') {
    $fine = $fineCalculator->applyLostBookPenalty($issuedBook);
}

// Trigger 3: Status changed to damaged
if ($newStatus === 'damaged' && $oldStatus !== 'damaged') {
    $fine = $fineCalculator->applyDamagedBookPenalty($issuedBook);
}
```

---

#### 2. **Web Routes**
**Path:** `routes/web.php`

**Routes Added:**
```php
Route::resource('fines', FineController::class)->only(['index','update']);
Route::post('/fines/{fine}/mark-as-paid', [FineController::class, 'markAsPaid'])->name('fines.mark-as-paid');
Route::post('/fines/{fine}/waive', [FineController::class, 'waive'])->name('fines.waive');
Route::get('/fines/student/{student}/list', [FineController::class, 'studentFines'])->name('fines.student');
Route::get('/fines/dashboard/summary', [FineController::class, 'dashboard'])->name('fines.dashboard');
Route::get('/fines/overdue/books', [FineController::class, 'overdueBooksSummary'])->name('fines.overdue');
```

---

## How to Use

### 1. Configure Fine Settings

Go to Admin Dashboard → Settings → Library Settings

Update the following:
- **Fine Per Day:** रु 5 (or your preferred amount)
- **Grace Period:** 2 days
- **Maximum Fine Amount:** रु 500
- **Lost Book Penalty:** रु 1000
- **Damaged Book Penalty:** रु 250
- **Issue Duration:** 14 days

### 2. Process Overdue Books

**Option A: Automatic (Recommended)**
- System runs daily at 2:00 AM automatically
- No manual intervention required

**Option B: Manual Processing**
```bash
php artisan fines:calculate-overdue
```

### 3. View Fines

**Via API/Dashboard:**
```
GET /admin/fines
```

**Get student's fines:**
```
GET /admin/fines/student/{student_id}/list
```

**Get dashboard summary:**
```
GET /admin/fines/dashboard/summary
```

### 4. Mark Fine as Paid

**Via API:**
```
POST /admin/fines/{fine_id}/mark-as-paid
{
    "payment_method": "cash"  // or "card", "online"
}
```

### 5. Waive Fine

**Via API:**
```
POST /admin/fines/{fine_id}/waive
{
    "reason": "Granted due to hardship"
}
```

---

## Database Schema

### Relationships

```
IssuedBook (1) ──→ (1) Fine
    ↓
Book

Fine (Many) ──→ (1) Student
  ↓
IssuedBook
```

### Key Fields

**IssuedBooks Table:**
```
id, book_id, student_id, issued_by, 
issue_date, due_date, return_date, 
status (issued|returned|lost|damaged), 
fine_amount, remarks
```

**Fines Table:**
```
id, issued_book_id, student_id, 
amount, days_late, 
status (pending|paid|waived), 
paid_on, payment_method, remarks
```

**FineSetting Table:**
```
id, per_day_fine, grace_period_days, 
max_fine_amount, lost_book_penalty, 
damaged_book_penalty, issue_duration_days,
max_books_per_student, is_active
```

---

## Summary of Fine Flow

```
Book Issued
    ↓
[Due Date Passed]
    ↓
Scheduled Job / Manual Command (2 AM daily)
    ↓
Check if book returned?
    ├─→ NO → Calculate overdue fine → Create Fine record → Status: pending
    │
    └─→ YES (returned late) → Calculate overdue fine → Create Fine record → Status: pending
            ↓
            [Book Status Check]
            ├─→ LOST → Apply lost_book_penalty → Create Fine record → Status: pending
            └─→ DAMAGED → Apply damaged_book_penalty → Create Fine record → Status: pending

[Fine Created]
    ↓
[Admin Actions]
    ├─→ Mark as Paid → Status: paid, paid_on: today, payment_method: cash/card/online
    └─→ Waive → Status: waived, remarks: reason provided
```

---

## Important Notes

1. **Grace Period:** Fines are only charged after the grace period expires
2. **Maximum Cap:** Even if book is extremely late, fine won't exceed `max_fine_amount`
3. **Automatic Processing:** Always runs without manual intervention (scheduled daily)
4. **Lost/Damaged:** These are fixed penalties, grace period doesn't apply
5. **Fine Creation:** Fine record is created only if amount > 0
6. **Activity Logging:** All fine events are logged for audit trail
7. **Thread-Safe:** Scheduled job prevents duplicate processing

---

## Testing Fine Logic

### Test Case 1: Normal Overdue
```
Issue date: Jan 1
Due date: Jan 15
Return date: Jan 20
Expected fine: (20-15-2) × 5 = रु 15 ✓
```

### Test Case 2: Within Grace Period
```
Issue date: Jan 1
Due date: Jan 15
Return date: Jan 16 (1 day late)
Expected fine: रु 0 (within 2-day grace) ✓
```

### Test Case 3: Lost Book
```
Status: lost
Expected fine: रु 1000 ✓
```

### Test Case 4: Damaged Book
```
Status: damaged
Expected fine: रु 250 ✓
```

---

## Troubleshooting

**Q: Fine not calculated for overdue book?**
- Check if book's `return_date` is set (NULL means still borrowed)
- Run manual command: `php artisan fines:calculate-overdue`
- Check if FineSetting is active (`is_active = 1`)

**Q: Same fine calculated multiple times?**
- System updates existing fine record, doesn't create duplicates
- Check issued_book_id uniqueness in fines table

**Q: Fine amount seems wrong?**
- Verify fine_settings values in database
- Check if max_fine_amount is too low
- Manually verify using formula above

---

**End of Documentation**  
*For questions or updates, contact the development team.*
