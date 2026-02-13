<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'book_overdue',
        'book_due_soon',
        'fine_created',
        'fine_reminder',
        'request_status_change',
        'new_book_available',
        'payment_confirmation',
    ];

    protected $casts = [
        'book_overdue' => 'boolean',
        'book_due_soon' => 'boolean',
        'fine_created' => 'boolean',
        'fine_reminder' => 'boolean',
        'request_status_change' => 'boolean',
        'new_book_available' => 'boolean',
        'payment_confirmation' => 'boolean',
    ];

    /**
     * Get the user that owns the preference
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
