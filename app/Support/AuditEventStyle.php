<?php

namespace App\Support;

use Illuminate\Support\Str;

/** Presentation only. Stored categories, statuses and audit records are never changed. */
final class AuditEventStyle
{
    public static function resolve(?string $event, ?string $category = null, ?string $severity = null, array $metadata = []): array
    {
        $event = Str::snake(str_replace('-', '_', (string) $event));
        $category = strtolower((string) $category);
        $severity = strtolower((string) $severity);
        if ($event === 'status_changed') {
            $severity = match (strtolower((string) ($metadata['new_status'] ?? ''))) {
                'inactive', 'deactivated', 'blocked' => 'danger',
                'restricted' => 'warning',
                'active', 'activated' => 'success',
                default => $severity,
            };
        }
        $family = match (true) {
            str_contains($event, 'privilege'), str_contains($event, 'permission'), in_array($category, ['privilege', 'permission', 'permissions']) => 'privilege',
            str_contains($event, 'fine'), in_array($category, ['fine', 'payment', 'payments']) => 'fine',
            str_contains($event, 'request'), in_array($category, ['request', 'book_request']) => 'request',
            (bool) preg_match('/login|logout|password|otp|auth/', $event), in_array($category, ['auth', 'authentication', 'security']) => 'auth',
            str_contains($event, 'book'), in_array($category, ['book', 'books', 'circulation', 'transaction']) => 'book',
            (bool) preg_match('/user_|account_|profile_|role_|status_changed/', $event), in_array($category, ['user', 'student', 'staff', 'account', 'user_management']) => 'user',
            (bool) preg_match('/setting|config|logo/', $event), in_array($category, ['system', 'settings', 'setting']) => 'system',
            default => 'system',
        };
        $tone = $family;
        $meaning = 'routine';
        // A completed write is not automatically a success event (e.g. fine applied).
        if (preg_match('/deleted|deactivated|rejected|failed|failure|lost|denied/', $event) || in_array($severity, ['error', 'failed', 'critical', 'denied', 'rejected', 'danger'])) {
            $tone = 'danger';
            $meaning = 'danger';
        } elseif (preg_match('/returned|paid|payment|activated|approved|verified/', $event) || ($event === 'status_changed' && $severity === 'success')) {
            $tone = 'success';
            $meaning = 'success';
        } elseif ($event === 'fine_waived') {
            $tone = 'user'; // Teal outcome; the Fine category remains visible.
            $meaning = 'success';
        } elseif (preg_match('/overdue|escalat|restricted|suspicious/', $event) || in_array($severity, ['warning', 'pending', 'partial'])) {
            $tone = $family === 'fine' ? 'fine' : 'warning';
            $meaning = 'warning';
        } elseif (in_array($event, ['fine_applied', 'fine_adjusted', 'fine_reopened', 'fine_reminder'])) {
            $meaning = 'warning';
        }

        $icon = match ($family) {
            'fine' => 'receipt', 'auth' => 'log-in', 'book' => 'book-open',
            'user' => 'user-round', 'privilege' => 'shield-check', 'request' => 'clipboard-list',
            default => 'settings',
        };
        $icon = match ($meaning) {
            'danger' => str_contains($event, 'deleted') ? 'trash-2' : 'triangle-alert',
            'success' => 'circle-check',
            default => $icon,
        };

        return [
            'category' => $family,
            'categoryLabel' => $family === 'auth' ? 'Authentication' : ucfirst($family),
            'tone' => $tone,
            'class' => 'audit-tone-'.$tone,
            'categoryClass' => 'audit-tone-'.$family,
            'backgroundClass' => 'audit-soft', 'badgeClass' => 'audit-badge',
            'textClass' => 'audit-text', 'iconClass' => 'audit-icon', 'borderClass' => 'audit-border',
            'icon' => $icon,
            'severity' => $meaning,
            'severityLabel' => ['routine' => 'Routine', 'success' => 'Success', 'warning' => 'Warning', 'danger' => 'Danger'][$meaning],
        ];
    }

    public static function roleClass(?string $role): string
    {
        return 'audit-role-'.(in_array($role, ['admin', 'staff', 'student']) ? $role : 'system');
    }
}
