# Notification System - Integration Guide

## 🎯 How to Start Using Notifications

The notification system is ready to use. Here's how to integrate it into your existing controllers.

---

## Step 1: Import the Notification Model

```php
use App\Models\Notification;
```

Add this to any controller where you want to create notifications.

---

## Step 2: Create Notifications

Use the static `notify()` method anywhere in your code:

```php
Notification::notify(
    user: $user,                    // Required: User object
    type: 'notification.type',      // Required: One of 9 types below
    title: 'Short Title',           // Required: Display title
    message: 'Detailed message',    // Required: Description
    data: [],                       // Optional: JSON data object
    relatedModel: 'ModelName',      // Optional: Related model name
    relatedId: 1                    // Optional: Related model ID
);
```

---

## The 9 Notification Types

Choose the appropriate type for each notification:

1. **`book.overdue`** - When a book becomes overdue
2. **`book.due_soon`** - When a book is due in next few days
3. **`fine.created`** - When a fine is created
4. **`fine.reminder`** - Fine payment reminder
5. **`request.approved`** - Book request approved
6. **`request.rejected`** - Book request rejected
7. **`request.pending`** - New book request pending (notify staff)
8. **`book.new`** - New book added to library
9. **`payment.confirmed`** - Fine payment confirmed

---

## Integration Examples by Feature

### 📚 Book Issue Notification

**File:** `app/Http/Controllers/Staff/IssueBookController.php`

```php
<?php

namespace App\Http\Controllers\Staff;

use App\Models\Notification;
use App\Models\IssuedBook;

class IssueBookController extends Controller
{
    public function issueBooks(Request $request)
    {
        // ... validation code ...
        
        foreach ($books as $bookId) {
            $book = Book::find($bookId);
            $issuedBook = IssuedBook::create([
                'student_id' => $request->student_id,
                'book_id' => $book->id,
                'issued_date' => now(),
                'due_date' => now()->addDays(14),
                'issued_by' => Auth::id(),
            ]);
            
            // ✅ CREATE NOTIFICATION
            Notification::notify(
                user: $student->user,
                type: 'book.overdue',  // Note: will help track due dates
                title: 'New Book Issued',
                message: "You have been issued '{$book->title}'. Due date: " . $issuedBook->due_date->format('M d, Y'),
                data: [
                    'book_id' => $book->id,
                    'issued_book_id' => $issuedBook->id,
                    'due_date' => $issuedBook->due_date->toDateString(),
                    'days_allowed' => 14
                ],
                relatedModel: 'IssuedBook',
                relatedId: $issuedBook->id
            );
        }
        
        // ... rest of code ...
    }
}
```

---

### 📤 Book Return Notification

**File:** `app/Http/Controllers/Staff/ReturnBookController.php`

```php
<?php

namespace App\Http\Controllers\Staff;

use App\Models\Notification;

class ReturnBookController extends Controller
{
    public function returnBooks(Request $request)
    {
        // ... validation code ...
        
        foreach ($issuedBooks as $issuedBook) {
            $book = $issuedBook->book;
            
            // Mark as returned
            $issuedBook->update([
                'returned_date' => now(),
                'condition' => $request->condition,
            ]);
            
            // ✅ CREATE NOTIFICATION
            Notification::notify(
                user: $issuedBook->student->user,
                type: 'book.overdue',  // Changed from overdue (book returned!)
                title: 'Book Return Confirmed',
                message: "Your return of '{$book->title}' has been confirmed.",
                data: [
                    'book_id' => $book->id,
                    'issued_book_id' => $issuedBook->id,
                    'condition' => $request->condition
                ],
                relatedModel: 'IssuedBook',
                relatedId: $issuedBook->id
            );
        }
        
        // ... rest of code ...
    }
}
```

---

### 💰 Fine Creation Notification

**File:** `app/Http/Controllers/Admin/FineController.php` or wherever fines are created

```php
<?php

use App\Models\Notification;
use App\Models\Fine;

// When creating a fine
$fine = Fine::create([
    'student_id' => $studentId,
    'issued_book_id' => $issuedBookId,
    'amount' => $amount,
    'reason' => 'Book Overdue',
    'status' => 'unpaid'
]);

// ✅ CREATE NOTIFICATION
Notification::notify(
    user: $fine->student->user,
    type: 'fine.created',
    title: 'Fine Created',
    message: "A fine of Rs. {$fine->amount} has been created. Book: {$fine->issuedBook->book->title}",
    data: [
        'fine_id' => $fine->id,
        'amount' => $fine->amount,
        'reason' => $fine->reason,
        'book_title' => $fine->issuedBook->book->title,
        'due_date' => $fine->due_date
    ],
    relatedModel: 'Fine',
    relatedId: $fine->id
);
```

---

### ✅ Fine Payment Notification

**File:** `app/Http/Controllers/Admin/FineController.php`

```php
<?php

use App\Models\Notification;

public function markAsPaid(Fine $fine)
{
    // Mark fine as paid
    $fine->update([
        'paid_amount' => $fine->amount,
        'paid_date' => now(),
        'status' => 'paid'
    ]);
    
    // ✅ CREATE NOTIFICATION
    Notification::notify(
        user: $fine->student->user,
        type: 'payment.confirmed',
        title: 'Payment Confirmed',
        message: "Your payment of Rs. {$fine->amount} has been confirmed.",
        data: [
            'fine_id' => $fine->id,
            'amount' => $fine->amount,
            'paid_date' => now()->toDateString(),
            'transaction_id' => $transactionId
        ],
        relatedModel: 'Fine',
        relatedId: $fine->id
    );
    
    // ... rest of code ...
}
```

---

### 📖 Book Request Notification

**File:** `app/Http/Controllers/Staff/BookRequestController.php`

```php
<?php

use App\Models\Notification;
use App\Models\BookRequest;

// When approving a request
public function approve(BookRequest $request)
{
    $request->update(['status' => 'approved']);
    
    // ✅ CREATE NOTIFICATION
    Notification::notify(
        user: $request->student->user,
        type: 'request.approved',
        title: 'Book Request Approved',
        message: "Your request for '{$request->book->title}' has been approved!",
        data: [
            'request_id' => $request->id,
            'book_id' => $request->book->id,
            'book_title' => $request->book->title
        ],
        relatedModel: 'BookRequest',
        relatedId: $request->id
    );
    
    // ... rest of code ...
}

// When rejecting a request
public function reject(BookRequest $request)
{
    $request->update(['status' => 'rejected']);
    
    // ✅ CREATE NOTIFICATION
    Notification::notify(
        user: $request->student->user,
        type: 'request.rejected',
        title: 'Book Request Rejected',
        message: "Your request for '{$request->book->title}' could not be fulfilled.",
        data: [
            'request_id' => $request->id,
            'book_id' => $request->book->id,
            'book_title' => $request->book->title,
            'reason' => 'Not available'
        ],
        relatedModel: 'BookRequest',
        relatedId: $request->id
    );
    
    // ... rest of code ...
}
```

---

### 📕 New Book Added Notification

**File:** `app/Http/Controllers/Admin/BookController.php`

```php
<?php

use App\Models\Notification;
use App\Models\User;

public function store(Request $request)
{
    $book = Book::create($request->validated());
    
    // Notify all students about new book
    $students = User::where('role', 'student')->get();
    
    foreach ($students as $student) {
        // ✅ CREATE NOTIFICATION
        Notification::notify(
            user: $student,
            type: 'book.new',
            title: 'New Book Available',
            message: "A new book '{$book->title}' has been added to the library!",
            data: [
                'book_id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn
            ],
            relatedModel: 'Book',
            relatedId: $book->id
        );
    }
    
    // ... rest of code ...
}
```

---

## Testing Your Integration

After adding notifications to your controllers:

### 1. Test with Command
```bash
php artisan notification:test 1
```

### 2. Check in Database
```bash
php artisan tinker
>>> App\Models\Notification::where('user_id', 1)->latest()->first()
```

### 3. Test API Endpoint
```javascript
// In browser console
fetch('/student/notifications').then(r => r.json()).then(console.log)
```

### 4. View in Student Dashboard
- Login as a student
- Check notification bell in header
- Notifications should appear dynamically

---

## Best Practices

1. **Always Use notify()** - Don't create Notification records directly
   ```php
   // ❌ DON'T
   Notification::create([...]);
   
   // ✅ DO
   Notification::notify(...);
   ```

2. **Include Related ID** - Helps with linking
   ```php
   // ✅ Good
   Notification::notify(
       ...
       relatedModel: 'IssuedBook',
       relatedId: $issuedBook->id
   );
   ```

3. **Store Useful Data** - JSON data can contain anything
   ```php
   // ✅ Useful data
   data: [
       'book_id' => $book->id,
       'due_date' => $dueDate,
       'days_allowed' => 14
   ]
   ```

4. **Use Consistent Titles** - For better UX
   ```php
   // ✅ Consistent
   type: 'book.overdue'
   title: 'Book Overdue'
   
   // ❌ Inconsistent
   type: 'book.overdue'
   title: 'Your book is late!'
   ```

---

## Common Patterns

### Notification in Transaction
```php
DB::transaction(function () use ($student, $book) {
    $issuedBook = IssuedBook::create([...]);
    
    Notification::notify(
        user: $student->user,
        type: 'book.overdue',
        title: 'Book Issued',
        message: "Issued: {$book->title}",
        data: ['book_id' => $book->id],
        relatedModel: 'IssuedBook',
        relatedId: $issuedBook->id
    );
});
```

### Bulk Notifications
```php
$students = Student::all();

foreach ($students as $student) {
    Notification::notify(
        user: $student->user,
        type: 'book.new',
        title: 'New Book Available',
        message: "New book added: {$book->title}",
        data: ['book_id' => $book->id],
        relatedModel: 'Book',
        relatedId: $book->id
    );
}
```

### Conditional Notifications
```php
if ($issuedBook->due_date->diffInDays(now()) <= 3) {
    Notification::notify(
        user: $issuedBook->student->user,
        type: 'book.due_soon',
        title: 'Book Due Soon',
        message: "Your book '{$book->title}' is due in 3 days",
        data: ['book_id' => $book->id, 'days_left' => 3],
        relatedModel: 'IssuedBook',
        relatedId: $issuedBook->id
    );
}
```

---

## Checklist for Integration

- [ ] Import `Notification` model in your controller
- [ ] Identify where notifications should be created
- [ ] Choose the correct notification type
- [ ] Add `Notification::notify()` call
- [ ] Include relevant data in `data` parameter
- [ ] Add `relatedModel` and `relatedId`
- [ ] Test with `php artisan notification:test {user_id}`
- [ ] Verify notification appears in student dashboard
- [ ] Check unread count updates correctly

---

## Support

For detailed information, see:
- `NOTIFICATION_SYSTEM_DOCUMENTATION.md` - Full technical guide
- `NOTIFICATION_QUICK_START.md` - Quick reference
- Controller code comments - Implementation details

---

**You're ready to integrate notifications into your Library Management System!** 🚀
