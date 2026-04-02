<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AccountLockoutManager
{
    public static function maxAttempts(): int
    {
        return max(1, (int) config('security.rate_limiting.max_attempts', 5));
    }

    public static function decayMinutes(): int
    {
        return max(1, (int) config('security.rate_limiting.lockout_duration', 60));
    }

    public static function decaySeconds(): int
    {
        return self::decayMinutes() * 60;
    }

    public static function rateLimitingEnabled(): bool
    {
        return (bool) config('security.rate_limiting.enabled', true);
    }

    public static function emailUnlockEnabled(): bool
    {
        return (bool) config('security.rate_limiting.email_unlock_enabled', true);
    }

    public static function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    public static function throttleKey(string $email, string $ip): string
    {
        return Str::transliterate(self::normalizeEmail($email) . '|' . trim($ip));
    }

    public static function notificationMarkerKey(string $throttleKey): string
    {
        return 'account-lock-notified:' . sha1($throttleKey);
    }

    public static function cacheStoreSupportsInspection(): bool
    {
        return config('cache.default') === 'database';
    }

    public static function activeTimerEntries(?string $email = null): Collection
    {
        if (!self::cacheStoreSupportsInspection()) {
            return collect();
        }

        $query = DB::table(config('cache.stores.database.table', 'cache'))
            ->select(['key', 'expiration'])
            ->where('key', 'like', '%:timer')
            ->where('expiration', '>', now()->timestamp);

        if ($email !== null && $email !== '') {
            $query->where('key', 'like', '%' . self::normalizeEmail($email) . '|%');
        }

        return $query->get();
    }

    public static function activeThrottleKeys(?string $email = null): Collection
    {
        return self::activeTimerEntries($email)
            ->map(fn ($entry) => self::extractThrottleKeyFromCacheKey((string) $entry->key))
            ->filter()
            ->values();
    }

    public static function extractThrottleKeyFromCacheKey(string $cacheKey): ?string
    {
        $prefix = (string) config('cache.prefix', '');
        $normalizedKey = $cacheKey;

        if ($prefix !== '' && Str::startsWith($normalizedKey, $prefix)) {
            $normalizedKey = Str::after($normalizedKey, $prefix);
        }

        if (!Str::endsWith($normalizedKey, ':timer')) {
            return null;
        }

        return Str::beforeLast($normalizedKey, ':timer');
    }

    public static function parseThrottleKey(string $throttleKey): ?array
    {
        if (!str_contains($throttleKey, '|')) {
            return null;
        }

        [$email, $ip] = explode('|', $throttleKey, 2);

        if ($email === '' || $ip === '') {
            return null;
        }

        return [
            'email' => self::normalizeEmail($email),
            'ip' => $ip,
        ];
    }

    public static function clearLock(string $email, string $ip): void
    {
        self::clearThrottleKey(self::throttleKey($email, $ip));
    }

    public static function clearThrottleKey(string $throttleKey): void
    {
        RateLimiter::clear($throttleKey);
        Cache::forget(self::notificationMarkerKey($throttleKey));
    }

    public static function clearLocksForEmail(string $email): int
    {
        return self::activeThrottleKeys($email)
            ->reduce(function (int $count, string $throttleKey): int {
                self::clearThrottleKey($throttleKey);

                return $count + 1;
            }, 0);
    }

    public static function clearAllActiveLocks(): int
    {
        return self::activeThrottleKeys()
            ->reduce(function (int $count, string $throttleKey): int {
                self::clearThrottleKey($throttleKey);

                return $count + 1;
            }, 0);
    }
}
