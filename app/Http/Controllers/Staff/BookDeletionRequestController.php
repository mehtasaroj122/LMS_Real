<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\book;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookDeletionRequestController extends Controller
{
    public function store(Request $request, $bookId)
    {
        Gate::authorize('access-staff');

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $book = book::find($bookId);
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        // Log a deletion request for admins to review
        $message = 'Deletion request for book: ' . $book->title . ' (ID: ' . $book->id . ') by ' . (auth()->user()->name ?? 'Unknown');
        if (!empty($validated['reason'])) {
            $message .= ' — Reason: ' . $validated['reason'];
        }

        ActivityLogger::logActivity(
            'book_deletion_requested',
            $message,
            'book',
            'book',
            $book->id,
            ['reason' => $validated['reason'] ?? null]
        );

        return response()->json(['success' => true, 'message' => 'Deletion request submitted to administrators']);
    }
}
