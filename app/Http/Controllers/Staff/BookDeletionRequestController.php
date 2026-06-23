<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\Notification;
use App\Models\User;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class BookDeletionRequestController extends Controller
{
    public function store(Request $request, $bookId)
    {
        Gate::authorize('access-staff');

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Please enter a reason for this deletion request.',
        ]);

        $book = book::find($bookId);
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        $existingRequest = ActivityLog::query()
            ->where('action', 'book_deletion_requested')
            ->where('user_id', auth()->id())
            ->where('resource_type', 'book')
            ->where('resource_id', $book->id)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->latest('created_at')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'A deletion request for this book was already submitted recently. Please wait for admin review.',
            ], 422);
        }

        // Log a deletion request for admins to review
        $message = 'Deletion request for book: ' . $book->title . ' (ID: ' . $book->id . ') by ' . (auth()->user()->name ?? 'Unknown');
        if (!empty($validated['reason'])) {
            $message .= ' - Reason: ' . $validated['reason'];
        }

        ActivityLogger::logActivity(
            'book_deletion_requested',
            $message,
            'book',
            'book',
            $book->id,
            ['reason' => $validated['reason'] ?? null]
        );

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::notify(
                user: $admin,
                type: 'book.deletion-requested',
                title: 'Book Deletion Request',
                message: auth()->user()->name . " requested deletion review for '{$book->title}'",
                data: [
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'isbn' => $book->isbn,
                    'reason' => $validated['reason'] ?? null,
                    'requested_by' => auth()->user()->name,
                ],
                relatedModel: 'Book',
                relatedId: $book->id
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Deletion request submitted to administrators',
        ]);
    }
}
