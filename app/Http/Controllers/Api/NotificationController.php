<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ResolvesApiUsers;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    use ResolvesApiUsers;

    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate($this->perPage($request));

        return NotificationResource::collection($notifications);
    }

    public function unread(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()
            ->notifications()
            ->unread()
            ->latest()
            ->paginate($this->perPage($request));

        return NotificationResource::collection($notifications);
    }

    public function count(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => (int) $request->user()->notifications()->unread()->count(),
            'total_count' => (int) $request->user()->notifications()->count(),
        ]);
    }

    public function read(Request $request, int $id): JsonResponse
    {
        $notification = Notification::query()->findOrFail($id);

        if ((int) $notification->user_id !== (int) $request->user()->id) {
            return $this->forbid();
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => new NotificationResource($notification->fresh()),
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $updated = $request->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'data' => [
                'updated_count' => $updated,
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = Notification::query()->findOrFail($id);

        if ((int) $notification->user_id !== (int) $request->user()->id) {
            return $this->forbid();
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully.',
        ]);
    }
}
