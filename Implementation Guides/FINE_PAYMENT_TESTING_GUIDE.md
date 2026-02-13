# Testing Guide - Fine & Payment Management Functionality

## Quick Start

### Prerequisites
- Application running on http://localhost:8000
- Admin user logged in
- Both Fines page and ViewStudent page open in different tabs

---

## Test Cases

### Test 1: Mark Fine as Paid
**Location:** ViewStudent Page (`/admin/students/27`)

**Steps:**
1. Find a fine with status "pending" in the fines table
2. Click the "Mark Paid" button
3. Confirm the action in the dialog
4. Observe the table refresh

**Expected Results:**
- ✓ Toast notification: "Marking fine as paid..."
- ✓ Fine status changes to "Paid" (green badge)
- ✓ Action buttons change to only "View History"
- ✓ Fines page tab updates automatically (if BroadcastChannel or within 5 seconds)
- ✓ Success toast: "Fine marked as paid successfully!"

**Backend Verification:**
- Database: `fines` table - status changed to 'paid'
- Database: `paid_on` field has current timestamp

---

### Test 2: Waive Fine
**Location:** ViewStudent Page (`/admin/students/27`)

**Steps:**
1. Find a fine with status "pending"
2. Click the "Waive" button
3. Confirm the waiver in the dialog
4. Enter a reason (e.g., "First-time waiver")
5. Observe the table update

**Expected Results:**
- ✓ Confirmation dialog appears: "Are you sure you want to waive this fine?"
- ✓ Prompt for reason appears
- ✓ Toast: "Waiving fine..."
- ✓ Fine status changes to "Waived" (blue badge)
- ✓ Reason is recorded in database
- ✓ Success toast: "Fine waived successfully!"

**Backend Verification:**
- Database: `fines` table - status changed to 'waived'
- Database: `remarks` field contains waiver reason

---

### Test 3: Adjust Fine Amount
**Location:** ViewStudent Page (`/admin/students/27`)

**Steps:**
1. Find a fine with status "pending"
2. Click the "Adjust" button
3. Enter a new amount (e.g., "30")
4. Observe the table update

**Expected Results:**
- ✓ Prompt appears: "Enter new fine amount (₹)"
- ✓ Shows current amount as default
- ✓ Toast: "Adjusting fine amount..."
- ✓ Fine amount updates in the table
- ✓ Success toast: "Fine amount adjusted to ₹30 successfully!"

**Validation Tests:**
- Try entering negative amount: Should show error "Fine amount cannot be negative"
- Try entering non-numeric: Should show error
- Cancel prompt: Should not update

**Backend Verification:**
- Database: `fines` table - `amount` field updated
- Database: `remarks` field includes change history

---

### Test 4: View Fine History
**Location:** ViewStudent Page (`/admin/students/27`)

**Steps:**
1. Find any fine (any status)
2. Click the "View History" button
3. Review the history information

**Expected Results:**
- ✓ Toast: "Loading fine payment history..."
- ✓ Success toast: "Fine History Loaded"
- ✓ Alert dialog shows:
  - Fine ID
  - Book name
  - Amount
  - Days overdue
  - Current status
  - Creation date
  - Payment/waiver dates (if applicable)

**Example History Display:**
```
Fine ID: 27
Book: [Book Name]
Amount: ₹30
Days Overdue: 3
Status: paid
Created: 2024-01-27 10:00:00

History:
- 2024-01-27 10:00:00: Fine created for ₹50
- 2024-01-27 11:30:00: Fine marked as paid
```

---

### Test 5: Generate Receipt
**Location:** ViewStudent Page (`/admin/students/27`)

**Steps:**
1. Click the "Generate Receipt" button (top right of Fine & Payment card)
2. Wait for the download
3. Open the downloaded receipt

**Expected Results:**
- ✓ Toast: "Generating receipt PDF..."
- ✓ File downloads with name: `receipt-27-YYYY-MM-DD.pdf`
- ✓ Receipt contains:
  - Student name
  - Student ID (27)
  - Email address
  - Department
  - Generation timestamp
  - Table of all paid fines with dates and amounts
  - Total amount paid
  - Professional formatting

**Example Receipt:**
```
Library Management System - Fine Payment Receipt
Generated on: 2024-01-27 15:45:30

Student Information
Name: [Student Name]
Student ID: 27
Email: student@example.com
Department: Computer Science

Paid Fines Summary
Book Title                    | Amount  | Paid On
Design Patterns              | ₹50.00  | 2024-01-20
Clean Code                   | ₹30.00  | 2024-01-25
                              
Total Paid:                          ₹80.00
```

---

### Test 6: Cross-Page Synchronization
**Location:** Two tabs (Fines and ViewStudent)

**Steps:**
1. Open `/admin/fines` in Tab A
2. Open `/admin/students/27` in Tab B (ViewStudent)
3. In Tab A, find fine ID 27
4. Click "Mark as Paid" on fine ID 27 in Tab A
5. Switch to Tab B
6. Check if fine status updated

**Expected Results:**
- ✓ Tab A shows immediate update
- ✓ Tab B shows update within 100ms (BroadcastChannel) OR 5 seconds (polling)
- ✓ Browser console shows BroadcastChannel message received (if using BroadcastChannel)
- ✓ Both tables show fine status as "Paid"

**Console Logs (Check F12):**
- On Fines page: `[Fines] ✓ BroadcastChannel message sent successfully`
- On ViewStudent page: `[ViewStudent] ✓✓ MESSAGE EVENT FIRED - Fine update received`

---

### Test 7: Error Handling
**Location:** Any test with invalid input

**Test Cases:**

a) **Invalid Amount:**
- Click Adjust button
- Enter "-50" and press Enter
- Expected: Error toast "Fine amount cannot be negative"

b) **Non-numeric Input:**
- Click Adjust button  
- Enter "abc" and press Enter
- Expected: Error handling (prompt rejects non-numeric)

c) **Network Error (Simulate):**
- Open DevTools Network tab
- Throttle to "Offline"
- Try to mark fine as paid
- Expected: Error toast with message

d) **API Server Down:**
- Stop Laravel server
- Try any action
- Expected: Error toast "Error marking fine as paid: ..."

---

### Test 8: Status Badge Colors
**Location:** ViewStudent Page - Fines Table

**Verify Colors:**

| Status | Expected Color | Test By |
|--------|---|---|
| Pending | Yellow/Amber | Create new fine |
| Paid | Green | Mark a fine as paid |
| Waived | Blue | Waive a fine |

**Light Mode Verification:**
- Pending: Yellow background `#fef3c7`
- Paid: Green background `#dcfce7`
- Waived: Blue background `#dbeafe`

**Dark Mode Verification:**
- Pending: Orange background `#78350f`
- Paid: Dark green background `#14532d`
- Waived: Dark blue background `#1e3a8a`

---

## Performance Benchmarks

| Operation | Expected Time | Actual Time |
|-----------|---|---|
| Mark as Paid | < 500ms | |
| Waive Fine | < 500ms | |
| Adjust Amount | < 500ms | |
| View History | < 500ms | |
| Generate Receipt | < 1000ms | |
| Cross-page sync | < 100ms (BC) or < 5s (polling) | |

---

## Database Verification Checklist

After each test, verify database changes:

```sql
-- View fine details
SELECT id, status, amount, paid_on, remarks, updated_at 
FROM fines 
WHERE id = 27;

-- View activity logs
SELECT * FROM activity_logs 
WHERE subject_id = 27 
AND subject_type = 'App\\Models\\Fine'
ORDER BY created_at DESC;
```

---

## Troubleshooting

### Issue: "Fine not found" error
- **Cause:** Invalid fine ID
- **Fix:** Ensure fine exists in database for that student

### Issue: Fine doesn't update in ViewStudent page
- **Cause:** BroadcastChannel not supported or polling disabled
- **Fix:** Check browser console, restart polling, refresh manually

### Issue: Receipt doesn't download
- **Cause:** No paid fines or server error
- **Fix:** Check DevTools Network tab for API response

### Issue: "Error updating fine" message
- **Cause:** Validation failed or authorization issue
- **Fix:** Check backend logs: `storage/logs/laravel.log`

---

## Browser DevTools Console Testing

Open F12 and check console for:

**Successful Fine Update:**
```
[Fines] ✓ BroadcastChannel message sent successfully
[ViewStudent] ✓✓ MESSAGE EVENT FIRED - Fine update received: {type: "fineUpdated", fineId: 27, status: "paid", timestamp: ...}
```

**Network Activity:**
- POST `/admin/fines/27/mark-as-paid` - Status 200
- GET `/admin/students/27/fines` - Status 200
- Both should have JSON response with `success: true`

---

## Regression Testing

When making changes, verify:
- [ ] All five action buttons still work
- [ ] Status badges display correctly
- [ ] Cross-page sync working
- [ ] No console errors
- [ ] Database updates correctly
- [ ] Toast notifications display
- [ ] API responses valid JSON
- [ ] Authorization checks working

---

## Production Readiness Checklist

- [x] All API endpoints implemented
- [x] Error handling in place
- [x] Validation on client and server
- [x] Authorization checks enabled
- [x] CSRF protection enabled
- [x] Database logging enabled
- [x] Toast notifications working
- [x] Cross-page sync working
- [x] Status badges styled correctly
- [x] Receipt generation working

---

**Last Updated:** January 27, 2026
**Status:** ✅ Ready for production testing

