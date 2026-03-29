<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        if (str_contains((string) $this->description, '•')) {
            return (string) $this->description;
        }

        // Format based on action type
        $formatted = $this->readable_description;

        // Add user info if available
        if ($this->user) {
            $formatted .= "\n" . $this->user->name . '•' . $this->created_at->format('n/j/Y, g:i:s A') . "\n" . $this->user->role;
        }

        return $formatted;
    }

    /**
     * Get a human-friendly description, including cleanup for legacy JSON fragments.
     */
    public function getReadableDescriptionAttribute(): string
    {
        $description = trim((string) ($this->description ?? 'Activity recorded'));
        if ($description === '') {
            return 'Activity recorded';
        }

        $action = strtolower((string) ($this->action ?? ''));
        $category = strtolower((string) ($this->action_category ?? ''));

        if ($action === 'privilege_updated' || $category === 'privilege') {
            return $this->formatPrivilegeDescription($this->extractChangePayload($description));
        }

        $embeddedPayload = $this->extractEmbeddedJsonPayload($description);
        if ($embeddedPayload) {
            $summary = $this->formatGenericChangeSummary($embeddedPayload['changes']);
            if ($summary !== null) {
                return rtrim($embeddedPayload['prefix'], ':') . ': ' . $summary;
            }
        }

        return $description;
    }

    protected function extractChangePayload(string $description): array
    {
        if (is_array($this->metadata) && !empty($this->metadata)) {
            $metadata = isset($this->metadata['changes']) && is_array($this->metadata['changes'])
                ? $this->metadata['changes']
                : $this->metadata;

            return collect($metadata)
                ->except(['session_id'])
                ->toArray();
        }

        $embeddedPayload = $this->extractEmbeddedJsonPayload($description);

        return $embeddedPayload['changes'] ?? [];
    }

    protected function extractEmbeddedJsonPayload(string $description): ?array
    {
        if (!preg_match('/^(?<prefix>.*?):\s*(?<json>\{.*\})$/', $description, $matches)) {
            return null;
        }

        $decoded = json_decode($matches['json'], true);
        if (!is_array($decoded)) {
            return null;
        }

        return [
            'prefix' => trim($matches['prefix']),
            'changes' => $decoded,
        ];
    }

    protected function formatPrivilegeDescription(array $changes): string
    {
        if (empty($changes)) {
            return 'Library privileges updated.';
        }

        $messages = [];

        foreach ($changes as $field => $value) {
            switch ((string) $field) {
                case 'borrowing_allowed':
                    $messages[] = 'borrowing permission set to ' . ($value ? 'allowed' : 'restricted');
                    break;
                case 'max_books':
                    $messages[] = 'maximum books set to ' . $value;
                    break;
                case 'issue_duration_days':
                    $messages[] = 'issue duration set to ' . $value . ' days';
                    break;
                case 'per_day_fine':
                    $messages[] = 'per-day fine set to Rs. ' . number_format((float) $value, 2);
                    break;
                case 'grace_period_days':
                    $messages[] = 'grace period set to ' . $value . ' days';
                    break;
                case 'max_fine_amount':
                    $messages[] = 'maximum fine amount set to Rs. ' . number_format((float) $value, 2);
                    break;
                default:
                    $messages[] = $this->formatFieldLabel($field) . ' set to ' . $this->formatValue($value);
                    break;
            }
        }

        return 'Library privileges updated: ' . implode(', ', $messages) . '.';
    }

    protected function formatGenericChangeSummary(array $changes): ?string
    {
        $messages = [];

        foreach ($changes as $field => $value) {
            if ($field === 'session_id') {
                continue;
            }

            $messages[] = $this->formatFieldLabel($field) . ' set to ' . $this->formatValue($value);
        }

        if (empty($messages)) {
            return null;
        }

        return implode(', ', $messages) . '.';
    }

    protected function formatFieldLabel(string $field): string
    {
        return Str::of($field)
            ->replace(['_', '-'], ' ')
            ->lower()
            ->toString();
    }

    protected function formatValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'enabled' : 'disabled';
        }

        if (is_array($value)) {
            if (array_is_list($value)) {
                return collect($value)
                    ->map(fn ($item) => $this->formatValue($item))
                    ->implode(', ');
            }

            return collect($value)
                ->map(function ($item, $key) {
                    return $this->formatFieldLabel((string) $key) . ': ' . $this->formatValue($item);
                })
                ->implode(', ');
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return (string) $value;
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('M d, Y h:i A');
    }
}
