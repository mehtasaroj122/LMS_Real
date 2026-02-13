<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'user_email',
        'action',
        'action_category',
        'status',
        'model_type',
        'model_id',
        'description',
        'ip_address',
        'browser',
        'device_type',
        'metadata',
        'resource_type',
        'resource_id',
        'affected_user_id'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model.
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to filter by action type.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to filter by model type.
     */
    public function scopeByModel($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get readable action names.
     */
    public function getActionNameAttribute()
    {
        $actions = [
            'user_created' => 'User Created',
            'user_updated' => 'User Updated',
            'status_changed' => 'Status Changed',
            'user_deleted' => 'User Deleted',
            'password_reset' => 'Password Reset',
            'profile_updated' => 'Profile Updated',
            'login' => 'User Login',
            'logout' => 'User Logout',
            'book_issued' => 'Book Issued',
            'book_returned' => 'Book Returned',
            'book_created' => 'Book Created',
            'book_updated' => 'Book Updated',
            'book_deleted' => 'Book Deleted',
            'fine_paid' => 'Fine Paid',
            'library_settings_updated' => 'Library Settings Updated',
        ];

        return $actions[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Format the description for display
     */
    public function getFormattedDescriptionAttribute()
    {
        // If description already has proper format, return as is
        if (strpos($this->description, '•') !== false) {
            return $this->description;
        }

        // Format based on action type
        $formatted = $this->description;

        // Add user info if available
        if ($this->user) {
            $formatted .= "\n" . $this->user->name . '•' . $this->created_at->format('n/j/Y, g:i:s A') . "\n" . $this->user->role;
        }

        return $formatted;
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }
}
