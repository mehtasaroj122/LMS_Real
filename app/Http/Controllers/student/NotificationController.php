<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Gate;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications
     */
    public function index()
    {
        Gate::authorize('access-student');

        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);
        $unreadCount = Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'data' => $notifications->items(),
            'total' => $notifications->total(),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread notifications only
     */
    public function unread()
    {
        Gate::authorize('access-student');

        $notifications = Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->latest()
            ->get();

        return response()->json([
            'data' => $notifications,
        ]);
    }

    /**
     * Display a specific notification
     */
    public function show($notificationId)
    {
        Gate::authorize('access-student');

        $notification = $this->findUserNotification($notificationId);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    /**
     * Get count of unread notifications
     */
    public function unreadCount()
    {
        Gate::authorize('access-student');

        $unreadCount = Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        Gate::authorize('access-student');

        $notification = $this->findUserNotification($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $notification->fresh(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found',
        ], 404);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Gate::authorize('access-student');

        Notification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy($notificationId)
    {
        Gate::authorize('access-student');

        $notification = $this->findUserNotification($notificationId);

        if ($notification) {
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found',
        ], 404);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        Gate::authorize('access-student');

        Notification::query()
            ->where('user_id', auth()->id())
            ->whereNotNull('read_at')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All read notifications deleted',
        ]);
    }

    protected function findUserNotification($notificationId): ?Notification
    {
        return Notification::query()
            ->where('user_id', auth()->id())
            ->find($notificationId);
    }
}
