# Notification System - Quick Reference

## What Was Implemented

A complete real-time notification system for the Library Management System with:
- ✅ Database schema with flexible JSON storage
- ✅ RESTful API endpoints for managing notifications
- ✅ Dynamic frontend with auto-updating badges
- ✅ 9 notification types with icons and colors
- ✅ User notification preferences
- ✅ Real-time broadcasting infrastructure (ready to use)

## Key Files Created/Modified

### New Files (10)
1. `app/Models/NotificationPreference.php` - User preferences model
2. `app/Events/NotificationCreated.php` - Broadcasting event
3. `app/Http/Controllers/Student/NotificationController.php` - API controller
4. `app/Console/Commands/TestNotification.php` - Testing command
5. `database/migrations/2026_01_20_044018_create_notifications_table.php` - Notifications table
6. `database/migrations/2026_01_20_044020_create_notification_preferences_table.php` - Preferences table
7. `database/seeders/NotificationPreferenceSeeder.php` - Default preferences seeder
8. `config/broadcasting.php` - Broadcasting configuration
9. `NOTIFICATION_SYSTEM_DOCUMENTATION.md` - Full documentation

### Updated Files (3)
1. `app/Models/Notification.php` - Complete rewrite with all methods
2. `app/Models/User.php` - Added notification relationships
3. `routes/web.php` - Added notification routes
4. `resources/views/Student/layouts/app.blade.php` - Removed hardcoded notifications
5. `public/student/JS/appLayout.js` - Complete API integration

## Database Tables

### notifications
```
id, user_id, type, title, message, data (JSON), related_model, related_id, read_at, created_at, updated_at
Indexes: user_id, type, (user_id, read_at)
```

### notification_preferences
```
id, user_id (UNIQUE), book_overdue, book_due_soon, fine_created, fine_reminder,
request_status_change, new_book_available, payment_confirmation, created_at, updated_at
```

## API Endpoints

### All Protected by: `auth` middleware + `can:access-student` gate

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/student/notifications` | List all notifications (paginated, 10 per page) |
| GET | `/student/notifications/unread` | List only unread notifications |
| GET | `/student/notifications/unread-count` | Get unread count (for badge) |
| POST | `/student/notifications/{id}/read` | Mark single as read |
| POST | `/student/notifications/mark-all-read` | Mark all as read |
| DELETE | `/student/notifications/{id}` | Delete notification |
| POST | `/student/notifications/delete-all-read` | Delete all read notifications |

## Usage Examples

### Creating a Notification
```php
use App\Models\Notification;
use App\Models\User;

Notification::notify(
    user: User::find(1),
    type: 'book.overdue',
    title: 'Book Overdue',
    message: 'Your book is overdue.',
    data: ['book_id' => 5, 'days_overdue' => 2],
    relatedModel: 'IssuedBook',
    relatedId: 10
);
```

### Testing
```bash
# Create test notification
php artisan notification:test 1

# Check in browser
# Go to student dashboard - should see notifications appearing
```

### Frontend JavaScript
The layout now includes automatic:
- Load notifications on page load
- Auto-refresh every 30 seconds
- Mark as read on click
- Mark all as read button
- Delete notifications
- Real-time badge count updates

## Notification Types

| Type | Icon | Color | Use Case |
|------|------|-------|----------|
| `book.overdue` | alert-circle | danger | Book is overdue |
| `book.due_soon` | clock | warning | Book due date approaching |
| `fine.created` | dollar-sign | danger | New fine created |
| `fine.reminder` | alert-triangle | warning | Fine payment reminder |
| `request.approved` | check-circle | success | Book request approved |
| `request.rejected` | x-circle | danger | Book request rejected |
| `request.pending` | clock | info | New pending request |
| `book.new` | book | info | New book available |
| `payment.confirmed` | check-circle | success | Fine payment confirmed |

## Real-Time Broadcasting (Next Phase)

Currently: ✅ API-based polling (30-second refresh)
Ready for: 🔄 WebSocket real-time (Pusher/Reverb)

When you set up broadcasting:
1. Set `BROADCAST_DRIVER=pusher` or `reverb` in `.env`
2. Configure Pusher/Reverb credentials
3. Uncomment WebSocket listener in appLayout.js
4. Real-time notifications will update instantly

## Testing Endpoints

### In Browser Console
```javascript
// Load all notifications
fetch('/student/notifications').then(r => r.json()).then(d => console.log(d))

// Get unread count
fetch('/student/notifications/unread-count').then(r => r.json()).then(d => console.log(d))

// Mark notification as read
fetch('/student/notifications/1/read', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
}).then(r => r.json()).then(d => console.log(d))
```

### Using curl
```bash
# Get all notifications
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/student/notifications

# Get unread count
curl -H "Authorization: Bearer YOUR_TOKEN" http://localhost:8000/student/notifications/unread-count
```

## Integration Points

### Where to Create Notifications

1. **When book is issued** - `IssueBookController@issueBooks`
2. **When book is returned** - `ReturnBookController@returnBooks`
3. **When fine is created** - `FineController` (Admin/Staff)
4. **When fine is paid** - `FineController@markAsPaid`
5. **When request is processed** - `BookRequestController` (Staff)
6. **Scheduled tasks** - Future commands for overdue reminders

### Example Integration
```php
// In controller
use App\Models\Notification;

// Create notification
Notification::notify(
    user: $user,
    type: 'book.overdue',
    title: 'Overdue Book',
    message: "Your book '{$book->title}' is overdue.",
    data: ['book_id' => $book->id],
    relatedModel: 'IssuedBook',
    relatedId: $issuedBook->id
);
```

## Important Notes

1. ✅ All endpoints return proper HTTP status codes
2. ✅ CSRF protection enabled on all POST/DELETE
3. ✅ Pagination: 10 notifications per page
4. ✅ User isolation: Can only see own notifications
5. ✅ Database indexes for performance
6. ✅ JSON data flexible for any notification type

## File Locations
- Documentation: `NOTIFICATION_SYSTEM_DOCUMENTATION.md`
- Models: `app/Models/Notification.php`, `app/Models/NotificationPreference.php`
- Controller: `app/Http/Controllers/Student/NotificationController.php`
- Event: `app/Events/NotificationCreated.php`
- Routes: In `routes/web.php` under student routes group
- Frontend: `resources/views/Student/layouts/app.blade.php`, `public/student/JS/appLayout.js`
- Migrations: `database/migrations/2026_01_20_*`

## Status Summary

✅ **Fully Implemented:**
- Notification creation system
- API endpoints for CRUD operations
- Frontend integration (polling)
- Database with proper indexes
- User preferences system
- Broadcasting event structure
- Testing utilities

🔄 **Ready for Enhancement:**
- WebSocket real-time (just set BROADCAST_DRIVER)
- Email notifications (future)
- SMS notifications (future)
- Notification preferences UI (future)
- Scheduled reminders (future)
