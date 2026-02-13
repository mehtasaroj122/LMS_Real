<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'related_model',
        'related_id',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Create and send notification
     */
    public static function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        array $data = [],
        ?string $relatedModel = null,
        ?int $relatedId = null
    ): self {
        $notification = self::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'related_model' => $relatedModel,
            'related_id' => $relatedId,
        ]);

        // Trigger event for real-time WebSocket broadcast
        event(new \App\Events\NotificationCreated($user, $notification));

        return $notification;
    }

    /**
     * Scope: Get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: Get notifications by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get notification icon based on type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'book.overdue' => 'alert-circle',
            'book.due_soon' => 'clock',
            'fine.created' => 'dollar-sign',
            'fine.reminder' => 'alert-circle',
            'request.approved' => 'check-circle',
            'request.rejected' => 'x-circle',
            'request.pending' => 'clock',
            'book.new' => 'book-open',
            'payment.confirmed' => 'check-circle',
            default => 'bell',
        };
    }

    /**
     * Get notification color based on type
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'book.overdue', 'fine.reminder' => 'red',
            'book.due_soon' => 'yellow',
            'fine.created' => 'orange',
            'request.approved', 'payment.confirmed' => 'green',
            'request.rejected' => 'red',
            'request.pending' => 'blue',
            'book.new' => 'purple',
            default => 'blue',
        };
    }
}
