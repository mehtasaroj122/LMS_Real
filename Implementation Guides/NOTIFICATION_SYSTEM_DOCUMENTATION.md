# Notification System Implementation Guide

## Overview
A complete real-time notification system for the Library Management System using Laravel Events and WebSocket broadcasting.

## Architecture

### Components Implemented

#### 1. **Database Layer**
- **notifications table**: Stores all notifications with flexible JSON data storage
  - Columns: id, user_id, type, title, message, data (JSON), related_model, related_id, read_at, timestamps
  - Indexes: user_id, type, [user_id, read_at] for query optimization
  
- **notification_preferences table**: User-configurable notification settings
  - Columns: id, user_id (unique), boolean flags for 7 notification types, timestamps
  - Allows users to control what notifications they receive

#### 2. **Models**
- **Notification** (`app/Models/Notification.php`)
  - Relationships: `user()` (belongsTo), `related_model` polymorphic support
  - Methods:
    - `static notify()`: Factory method to create notifications and broadcast events
    - `markAsRead()`: Mark single notification as read
    - `isUnread()`: Check if notification is unread
    - `scopeUnread()`: Query unread notifications
    - `scopeByType()`: Filter by notification type
    - `getIcon()`: Return icon name based on type
    - `getColor()`: Return color based on type
  - Notification Types:
    - `book.overdue`: Book is overdue
    - `book.due_soon`: Book due date approaching
    - `fine.created`: New fine created
    - `fine.reminder`: Fine payment reminder
    - `request.approved`: Book request approved
    - `request.rejected`: Book request rejected
    - `request.pending`: New book request pending
    - `book.new`: New book added to library
    - `payment.confirmed`: Fine payment confirmed

- **NotificationPreference** (`app/Models/NotificationPreference.php`)
  - Relationships: `user()` (belongsTo)
  - Manages user notification preferences

- **User Model Updates**
  - Added `notifications()` relationship (hasMany)
  - Added `notificationPreferences()` relationship (hasOne)
  - Added helper methods:
    - `getUnreadNotificationCount()`
    - `markAllNotificationsAsRead()`

#### 3. **Events**
- **NotificationCreated** (`app/Events/NotificationCreated.php`)
  - Implements `ShouldBroadcast` interface
  - Broadcasts to private channel: `notifications.{userId}`
  - Event name: `notification.created`
  - Includes notification data with icon and color

#### 4. **Controllers**
- **NotificationController** (`app/Http/Controllers/Student/NotificationController.php`)
  - `index()`: Get all notifications with pagination
  - `unread()`: Get all unread notifications
  - `unreadCount()`: Get unread notification count
  - `markAsRead()`: Mark single notification as read
  - `markAllAsRead()`: Mark all as read
  - `destroy()`: Delete a notification
  - `deleteAllRead()`: Delete all read notifications

#### 5. **Routes**
Student API Routes (protected by `auth` and `can:access-student` gate):
```
GET    /student/notifications              → index()
GET    /student/notifications/unread       → unread()
GET    /student/notifications/unread-count → unreadCount()
POST   /student/notifications/{id}/read    → markAsRead()
POST   /student/notifications/mark-all-read → markAllAsRead()
POST   /student/notifications/delete-all-read → deleteAllRead()
DELETE /student/notifications/{id}         → destroy()
```

#### 6. **Frontend Integration**
- **Layout Updates** (`resources/views/Student/layouts/app.blade.php`)
  - Removed hardcoded notifications
  - Dynamic notification container
  - Real-time badge count

- **JavaScript** (`public/student/JS/appLayout.js`)
  - `loadNotifications()`: Fetch notifications from API
  - `renderNotifications()`: Render notifications dynamically
  - `updateBadgeCount()`: Update unread count badge
  - `markNotificationAsRead()`: Mark single notification as read
  - Auto-refresh every 30 seconds
  - Real-time WebSocket listeners (ready for integration)

#### 7. **Database Seeder**
- **NotificationPreferenceSeeder** (`database/seeders/NotificationPreferenceSeeder.php`)
  - Creates default notification preferences for all users
  - All 7 notification types enabled by default

#### 8. **Testing Command**
- **TestNotification** (`app/Console/Commands/TestNotification.php`)
  - Create test notifications for development
  - Usage: `php artisan notification:test {user_id}`

#### 9. **Broadcasting Configuration**
- **config/broadcasting.php**: Configured with Pusher/Ably support
  - Default driver: `null` (set via env variable)
  - Supports Pusher, Ably, Redis, Log drivers

## Usage

### Creating Notifications Programmatically

```php
use App\Models\User;
use App\Models\Notification;

// Create a notification
Notification::notify(
    user: User::find(1),
    type: 'book.overdue',
    title: 'Book Overdue',
    message: 'Your book "The Great Gatsby" is overdue.',
    data: ['book_id' => 5, 'days_overdue' => 2],
    relatedModel: 'IssuedBook',
    relatedId: 10
);
```

The `notify()` method:
1. Creates the notification in the database
2. Automatically fires `NotificationCreated` event
3. Event broadcasts to user's private WebSocket channel
4. Frontend receives real-time update

### Testing

```bash
# Create test notification
php artisan notification:test 1

# Access notifications API
GET /student/notifications
GET /student/notifications/unread
GET /student/notifications/unread-count
POST /student/notifications/{id}/read
POST /student/notifications/mark-all-read
DELETE /student/notifications/{id}
```

## Frontend API Integration

### Fetch All Notifications
```javascript
const response = await fetch('/student/notifications');
const data = await response.json();
const notifications = data.data; // paginated results
```

### Get Unread Count
```javascript
const response = await fetch('/student/notifications/unread-count');
const { unread_count } = await response.json();
```

### Mark as Read
```javascript
await fetch(`/student/notifications/${notificationId}/read`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token }
});
```

### Mark All as Read
```javascript
await fetch('/student/notifications/mark-all-read', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token }
});
```

## Real-Time Broadcasting Setup

### Current Status: API Ready (WebSocket Ready for Integration)

The system is fully prepared for real-time WebSocket integration:

#### Option 1: Using Pusher (Recommended for Production)
1. Register at [pusher.com](https://pusher.com)
2. Set environment variables in `.env`:
   ```
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_HOST=api-*.pusher.com
   PUSHER_PORT=443
   PUSHER_SCHEME=https
   PUSHER_CLUSTER=mt
   ```

3. Install Pusher client (if not already):
   ```bash
   composer require pusher/pusher-php-server
   npm install pusher-js
   ```

4. Configure Laravel Echo in `resources/js/app.js`:
   ```javascript
   import Echo from 'laravel-echo';
   import Pusher from 'pusher-js';
   
   window.Pusher = Pusher;
   
   window.Echo = new Echo({
       broadcaster: 'pusher',
       key: import.meta.env.VITE_PUSHER_APP_KEY,
       cluster: import.meta.env.VITE_PUSHER_CLUSTER,
       encrypted: true,
   });
   ```

#### Option 2: Using Laravel Reverb (Local Development)
1. Install Reverb:
   ```bash
   php artisan reverb:install
   ```

2. Update `.env`:
   ```
   BROADCAST_DRIVER=reverb
   REVERB_APP_ID=your_app_id
   REVERB_APP_KEY=your_app_key
   REVERB_APP_SECRET=your_app_secret
   ```

3. Start Reverb server:
   ```bash
   php artisan reverb:start
   ```

### JavaScript WebSocket Listener (When Broadcasting is Configured)

```javascript
// In appLayout.js or dedicated websocket file
if (window.Echo) {
    Echo.private(`notifications.${userId}`)
        .listen('NotificationCreated', (event) => {
            // event.notification contains the notification data
            addNotificationToUI(event.notification);
            updateBadgeCount();
            showToast(event.notification.title);
        });
}
```

## Current Implementation Status

✅ **COMPLETED:**
- Database schema (notifications, notification_preferences tables)
- Notification model with all methods
- NotificationCreated event class
- NotificationController with all endpoints
- API routes
- User model relationships
- Frontend API integration (fetch/display)
- JavaScript notification rendering
- Dynamic badge count updates
- Notification preferences model
- Database seeder
- Testing command
- Broadcasting configuration file

🔄 **NEXT STEPS (When Broadcasting is Configured):**
1. Configure Pusher or Reverb credentials in `.env`
2. Set `BROADCAST_DRIVER=pusher` or `BROADCAST_DRIVER=reverb`
3. Add Laravel Echo configuration to frontend
4. Uncomment WebSocket listener in JavaScript
5. Test real-time notifications

## File Structure

```
app/
├── Models/
│   ├── Notification.php (NEW - complete implementation)
│   ├── NotificationPreference.php (NEW)
│   └── User.php (UPDATED - added relationships)
├── Events/
│   └── NotificationCreated.php (NEW)
├── Http/Controllers/Student/
│   └── NotificationController.php (NEW)
└── Console/Commands/
    └── TestNotification.php (NEW)

database/
├── migrations/
│   ├── 2026_01_20_044018_create_notifications_table.php (NEW)
│   └── 2026_01_20_044020_create_notification_preferences_table.php (NEW)
└── seeders/
    └── NotificationPreferenceSeeder.php (NEW)

routes/
└── web.php (UPDATED - added notification routes)

resources/views/Student/layouts/
└── app.blade.php (UPDATED - removed hardcoded, added dynamic)

public/student/JS/
└── appLayout.js (UPDATED - complete API integration)

config/
└── broadcasting.php (NEW)
```

## Security Considerations

1. **Authentication**: All endpoints protected by `auth` middleware
2. **Authorization**: All student routes protected by `can:access-student` gate
3. **CSRF Protection**: All POST/DELETE requests require CSRF token
4. **Private Channels**: WebSocket broadcasts only to user's private channel
5. **User Isolation**: Each user can only access/modify their own notifications

## Performance Optimization

1. **Database Indexes**: Added on user_id, type, and [user_id, read_at] for fast queries
2. **JSON Storage**: Flexible data storage without separate tables
3. **Pagination**: Notifications API returns 10 per page
4. **Auto-refresh**: Frontend refreshes every 30 seconds (configurable)
5. **Lazy Loading**: Notifications loaded on demand, not on page load

## Testing

Create test notification:
```bash
php artisan notification:test 1
```

Access API endpoints:
```bash
# Get all notifications
curl http://localhost:8000/student/notifications

# Get unread count
curl http://localhost:8000/student/notifications/unread-count

# Mark as read
curl -X POST http://localhost:8000/student/notifications/1/read
```

## Future Enhancements

1. Email notifications (currently deferred)
2. SMS notifications
3. In-app notification center page
4. User notification preferences UI
5. Scheduled notifications for overdue reminders
6. Notification templates for different scenarios
7. Bulk notification creation for admin
8. Notification history and analytics
