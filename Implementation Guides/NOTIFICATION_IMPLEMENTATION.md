# Notification System Implementation Guide

## Overview
A comprehensive real-time notification system for the Library Management System using Laravel Broadcasting with WebSocket support (Pusher/Reverb).

## Components Created

### 1. Database Migrations
- **notifications**: Stores all user notifications with flexible data field
- **notification_preferences**: User preferences for 7 notification types

### 2. Models
- **Notification** (`app/Models/Notification.php`)
  - Methods: `notify()` (static factory), `markAsRead()`, `isUnread()`, `getIcon()`, `getColor()`
  - Scopes: `unread()`, `byType()`
  - Relationships: `belongsTo(User)`

- **NotificationPreference** (`app/Models/NotificationPreference.php`)
  - Stores user notification preferences
  - Relationship: `belongsTo(User)`

### 3. Events
- **NotificationCreated** (`app/Events/NotificationCreated.php`)
  - Implements `ShouldBroadcast` for real-time delivery
  - Broadcasts to private channel: `notifications.{user_id}`
  - Event name: `notification.created`

### 4. Controllers
- **NotificationController** (`app/Http/Controllers/Student/NotificationController.php`)
  - `index()` - Get all notifications with pagination
  - `unread()` - Get unread notifications only
  - `unreadCount()` - Get count of unread notifications
  - `markAsRead()` - Mark single notification as read
  - `markAllAsRead()` - Mark all as read
  - `destroy()` - Delete notification
  - `deleteAllRead()` - Delete all read notifications

### 5. Routes
Added 7 API routes in `routes/web.php` under student prefix:
```
GET    /student/notifications              (index)
GET    /student/notifications/unread       (unread)
GET    /student/notifications/unread-count (unreadCount)
POST   /student/notifications/{id}/read    (markAsRead)
POST   /student/notifications/mark-all-read (markAllAsRead)
DELETE /student/notifications/{id}         (destroy)
POST   /student/notifications/delete-all-read (deleteAllRead)
```

### 6. Frontend Integration
- **Layout File**: `resources/views/Student/layouts/app.blade.php`
  - Replaced hardcoded notifications with dynamic loading
  - Notification body now empty and populated by JavaScript

- **JavaScript**: `public/student/JS/appLayout.js`
  - `loadNotifications()` - Fetch from API
  - `renderNotifications()` - Render in UI
  - `updateBadgeCount()` - Update unread count
  - Handles individual and bulk mark-as-read
  - Auto-refresh every 30 seconds

### 7. Commands
- **TestNotification** (`app/Console/Commands/TestNotification.php`)
  - Usage: `php artisan notification:test {user_id}`
  - Creates sample notification for testing

### 8. Seeders
- **NotificationPreferenceSeeder** (`database/seeders/NotificationPreferenceSeeder.php`)
  - Creates default preferences for all users
  - All notifications enabled by default

## Notification Types

| Type | Icon | Color | Use Case |
|------|------|-------|----------|
| `book.overdue` | alert-circle | danger | Book is past due date |
| `book.due_soon` | clock | warning | Book due within 3 days |
| `fine.created` | dollar-sign | danger | Fine assessed on user |
| `fine.reminder` | alert-triangle | warning | Fine payment reminder |
| `request.approved` | check-circle | success | Book request approved |
| `request.rejected` | x-circle | danger | Book request rejected |
| `request.pending` | clock | info | Request awaiting response |
| `book.new` | book | info | New book added to library |
| `payment.confirmed` | check-circle | success | Fine payment received |

## How to Use

### Creating a Notification
```php
use App\Models\Notification;

Notification::notify(
    user: $user,
    type: 'book.overdue',
    title: 'Book Overdue',
    message: 'Your book "The Great Gatsby" is overdue by 2 days.',
    data: ['book_id' => 1, 'days_overdue' => 2],
    relatedModel: 'Book',
    relatedId: 1
);
```

### Broadcasting Behavior
The `notify()` method automatically:
1. Creates notification in database
2. Fires `NotificationCreated` event
3. Event broadcasts to `notifications.{user_id}` channel
4. WebSocket sends real-time update to connected users

### Fetching Notifications
```php
// Get all notifications
$notifications = $user->notifications()->paginate(10);

// Get unread only
$unread = $user->notifications()->unread()->get();

// Get by type
$overdue = $user->notifications()->byType('book.overdue')->get();

// Mark as read
$notification->markAsRead();

// Mark all as read
$user->markAllNotificationsAsRead();

// Get unread count
$count = $user->getUnreadNotificationCount();
```

## Setup Instructions

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Notification Preferences
```bash
php artisan db:seed --class=NotificationPreferenceSeeder
```

### 3. Configure Broadcasting Driver
Update `.env`:
```env
BROADCAST_DRIVER=pusher
# or
BROADCAST_DRIVER=log    # for development/testing
```

### 4. Install Broadcasting Package (if using Pusher)
```bash
composer require pusher/pusher-php-server
```

### 5. Set Pusher Credentials (if using Pusher)
```env
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

### 6. Test Notification
```bash
php artisan notification:test 1
```

## API Response Format

### Index (All Notifications)
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "type": "book.overdue",
      "title": "Book Overdue",
      "message": "Your book is overdue by 2 days.",
      "data": {"book_id": 1},
      "related_model": "Book",
      "related_id": 1,
      "read_at": null,
      "created_at": "2024-01-31T10:30:00Z",
      "updated_at": "2024-01-31T10:30:00Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### Unread Count
```json
{
  "unread_count": 5
}
```

## Frontend Events

### Loading Notifications
- Automatic on page load
- When notification popup opened
- Every 30 seconds (auto-refresh)

### Real-Time Updates (WebSocket - Future)
When WebSocket is fully configured:
```javascript
Echo.private(`notifications.${userId}`)
    .listen('NotificationCreated', (data) => {
        console.log('New notification:', data);
        loadNotifications(); // Refresh list
    });
```

## Next Steps (For Future Enhancement)

1. **WebSocket Integration**: Set up Echo.js and Pusher/Reverb for real-time notifications
2. **Email Notifications**: Create email templates and queue jobs
3. **Scheduled Commands**: Create artisan commands for:
   - Overdue book reminders
   - Fine payment reminders
   - New book notifications
4. **Admin Panel**: Settings to configure notification rules
5. **Notification History**: Archive old notifications
6. **Sound & Desktop Notifications**: Add browser notifications API

## Troubleshooting

### Notifications Not Appearing
1. Check if migrations ran: `php artisan migrate:status`
2. Check if notification preferences exist: Query `notification_preferences` table
3. Test with: `php artisan notification:test 1`
4. Check browser console for JavaScript errors

### Broadcasting Not Working
1. Verify `BROADCAST_DRIVER` in `.env`
2. For Pusher: Check API credentials
3. For log driver: Check `storage/logs/laravel.log`
4. Run `php artisan config:cache` if you changed `.env`

## Security Notes

- Notifications are sent to private channels (`notifications.{user_id}`)
- User must be authenticated to access their notifications
- All routes protected with `auth` middleware and `access-student` gate
- Consider adding rate limiting for production

