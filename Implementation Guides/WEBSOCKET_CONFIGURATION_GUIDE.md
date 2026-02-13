# WebSocket Broadcasting Configuration Guide

## Overview
The notification system is fully configured for WebSocket broadcasting. You have two options:

## Option 1: Development (Using Log Driver - Current Setup)
Current configuration is set to `BROADCAST_DRIVER=log` which logs broadcast events to the application log.

**Advantages:**
- ✅ No external dependencies
- ✅ Perfect for development
- ✅ Works immediately
- ✅ Good for debugging

**Disadvantages:**
- ❌ Not real-time (polling only)
- ❌ Not suitable for production

## Option 2: Production (Using Pusher)

### Step 1: Install Pusher Composer Package
```bash
composer require pusher/pusher-php-server
```

### Step 2: Create Pusher Account
1. Visit https://pusher.com
2. Sign up for free account
3. Create a new channel app
4. Get your credentials:
   - `PUSHER_APP_ID`
   - `PUSHER_APP_KEY`
   - `PUSHER_APP_SECRET`
   - `PUSHER_APP_CLUSTER`

### Step 3: Update .env File
```dotenv
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST=api-mt1.pusher.com
PUSHER_PORT=443
PUSHER_SCHEME=https
```

### Step 4: Install Pusher JavaScript Library
Add to your layout files (already included in some):
```html
<script src="https://js.pusher.com/8.0.0/pusher.min.js"></script>
```

### Step 5: Update JavaScript Notification Listeners

In `public/admin/JS/appLayout.js`, `public/staff/JS/appLayout.js`, and `public/student/JS/appLayout.js`, add WebSocket listeners:

```javascript
// Initialize Pusher (optional - for real-time updates)
if (typeof Pusher !== 'undefined') {
    Pusher.logToConsole = true;
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        encrypted: true
    });
    
    // Listen to private notification channel
    const channel = pusher.subscribe('private-notifications.{{ auth()->user()->id }}');
    
    channel.bind('notification.created', function(data) {
        console.log('Real-time notification received:', data);
        loadNotifications(); // Reload notifications immediately
    });
}
```

### Step 6: Test Configuration
```bash
# Test Pusher connection
php artisan tinker
>>> \Illuminate\Broadcasting\BroadcastingManager::broadcast(
    new \App\Events\NotificationCreated(
        \App\Models\Notification::first()
    )
);
```

---

## Option 3: Production (Using Laravel Reverb)

### Step 1: Install Reverb
```bash
php artisan install:broadcasting
composer require laravel/reverb
```

### Step 2: Publish Configuration
```bash
php artisan vendor:publish --provider="Laravel\Reverb\ReverServiceProvider"
```

### Step 3: Update .env
```dotenv
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Step 4: Start Reverb Server
```bash
php artisan reverb:start
```

### Step 5: Update JavaScript
```javascript
const pusher = new Echo({
    broadcaster: 'reverb',
    key: '{{ env('REVERB_APP_KEY') }}',
    wsHost: '{{ env('REVERB_HOST') }}',
    wsPort: '{{ env('REVERB_PORT') }}',
    wssPort: null,
    forceTLS: false,
    encrypted: true,
    disableStats: true,
});

pusher.private(`notifications.{{ auth()->user()->id }}`)
    .listen('notification.created', (data) => {
        console.log('Real-time notification:', data);
        loadNotifications();
    });
```

---

## Testing Broadcasting

### Manual Test
```bash
# In terminal 1 - Run queue worker
php artisan queue:work

# In terminal 2 - Run Tinker
php artisan tinker
>>> \Illuminate\Support\Facades\Broadcast::channel('notifications.*');
>>> $user = \App\Models\User::first();
>>> \App\Events\NotificationCreated::dispatch(\App\Models\Notification::create([...]));
```

### Automated Test
```bash
# Run the test notification command
php artisan notification:test 1
```

---

## Current Status

✅ **Broadcasting Infrastructure:** Ready
- Event class created: `NotificationCreated`
- Broadcasting channels configured
- Models configured with ShouldBroadcast
- Middleware added to authorize channels

⏳ **Pusher/Reverb Setup:** Pending
- Choose your provider (Pusher, Reverb, or stay with log driver)
- Configure credentials in .env
- Install JavaScript library
- Update JavaScript listeners

---

## Switching Broadcast Drivers

### From Log to Pusher
```bash
# 1. Install
composer require pusher/pusher-php-server

# 2. Update .env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=...
PUSHER_APP_KEY=...
PUSHER_APP_SECRET=...
PUSHER_APP_CLUSTER=mt1

# 3. Clear config cache
php artisan config:clear

# 4. Test
php artisan notification:test 1
```

### From Pusher to Log (for debugging)
```bash
# 1. Update .env
BROADCAST_DRIVER=log

# 2. Clear cache
php artisan config:clear

# 3. Check logs
tail -f storage/logs/laravel.log | grep -i broadcast
```

---

## Troubleshooting

### Broadcasts not showing up
1. Check BROADCAST_DRIVER in .env
2. Run `php artisan config:clear`
3. Check application logs: `storage/logs/laravel.log`
4. Verify credentials if using Pusher

### Connection Issues
```bash
# Clear config
php artisan config:clear

# Clear cache
php artisan cache:clear

# Restart queue worker (if using)
php artisan queue:restart
```

### Testing Without Real-Time
- System works fine with 30-second polling
- Notifications still appear in all modules
- Real-time just makes updates instant
- Consider real-time as optional enhancement

---

## Notification Broadcasting Channel Structure

```
Private Channels:
└── notifications.{user_id}
    ├── user-admin-1
    ├── user-staff-1
    └── user-student-1

Event Flow:
1. Notification created in controller
2. Notification::notify() fires NotificationCreated event
3. Event broadcasts to private channel: notifications.{user_id}
4. JavaScript listener receives update
5. UI refreshes immediately (if WebSocket active)
6. OR refreshes on next poll (30s if WebSocket inactive)
```

---

## Production Checklist

- [ ] Choose Pusher or Reverb
- [ ] Create account and get credentials
- [ ] Update .env with credentials
- [ ] Run `php artisan config:clear`
- [ ] Install JavaScript library
- [ ] Test broadcasting in development
- [ ] Deploy to production
- [ ] Monitor logs for errors
- [ ] Set up backup notification polling

---

**Note:** The system is fully functional with the log driver. Real-time WebSocket is an optional enhancement that can be added anytime.
