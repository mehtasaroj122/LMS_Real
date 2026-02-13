<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications
     */
    public function index()
    {
        Gate::authorize('access-admin');

        $notifications = auth()->user()->notifications()
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => $notifications->items(),
            'total' => $notifications->total(),
        ]);
    }

    /**
     * Get unread notifications only
     */
    public function unread()
    {
        Gate::authorize('access-admin');

        $notifications = auth()->user()->notifications()
            ->whereNull('read_at')
            ->latest()
            ->get();

        return response()->json([
            'data' => $notifications,
        ]);
    }

    /**
     * Get count of unread notifications
     */
    public function unreadCount()
    {
        Gate::authorize('access-admin');

        $unreadCount = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        Gate::authorize('access-admin');

        $notification = DatabaseNotification::find($notificationId);

        if ($notification && $notification->notifiable_id === auth()->id()) {
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
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
        Gate::authorize('access-admin');

        auth()->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        Gate::authorize('access-admin');

        auth()->user()->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All read notifications deleted',
        ]);
    }

    /**
     * Delete a specific notification
     */
    public function destroy($notificationId)
    {
        Gate::authorize('access-admin');

        $notification = DatabaseNotification::find($notificationId);

        if ($notification && $notification->notifiable_id === auth()->id()) {
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
}
