<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Support\AccountLockoutManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountLockController extends \App\Http\Controllers\Controller
{
    /**
     * Show locked accounts list
     */
    public function index(Request $request): View|JsonResponse
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = $this->normalizeAdminPerPage($request->input('per_page', 10));
        $page = max(1, (int) $request->input('page', 1));

        $allLockedAccounts = $this->getLockedAccounts();
        $summary = $this->buildSummary($allLockedAccounts);
        $lockedAccounts = $this->filterLockedAccounts($allLockedAccounts, $search);
        $totalLockedAccounts = count($lockedAccounts);
        $lastPage = max(1, (int) ceil($totalLockedAccounts / $perPage));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * $perPage;

        $lockedAccounts = new LengthAwarePaginator(
            array_slice($lockedAccounts, $offset, $perPage),
            $totalLockedAccounts,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $viewData = [
            'lockedAccounts' => $lockedAccounts,
            'summary' => $summary,
            'lockoutDuration' => AccountLockoutManager::decayMinutes(),
            'maxAttempts' => AccountLockoutManager::maxAttempts(),
            'rateLimitingEnabled' => AccountLockoutManager::rateLimitingEnabled(),
            'emailUnlockEnabled' => AccountLockoutManager::emailUnlockEnabled(),
            'supportsLiveMonitoring' => AccountLockoutManager::cacheStoreSupportsInspection(),
            'cacheStore' => config('cache.default'),
            'search' => $search,
            'perPage' => $perPage,
        ];

        if ($this->shouldReturnJson($request)) {
            return response()->json($this->buildIndexPayload($request, $viewData));
        }

        return view('Admin.account-locks.index', $viewData);
    }

    /**
     * Unlock a specific account
     */
    public function unlock(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'ip' => 'nullable|ip',
        ]);

        $email = $request->email;
        $ip = $request->ip;

        if ($ip) {
            AccountLockoutManager::clearLock($email, $ip);
            
            Log::info('Account unlock - specific IP', [
                'email' => $email,
                'ip' => $ip,
                'admin' => auth()->user()?->email,
                'timestamp' => now(),
            ]);

            $message = "Unlocked '{$email}' for IP '{$ip}'.";

            if ($this->shouldReturnJson($request)) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->route('admin.account-locks.index')
                ->with('success', $message);
        } else {
            $clearedLocks = AccountLockoutManager::clearLocksForEmail($email);

            Log::info('Account unlock - all IPs', [
                'email' => $email,
                'cleared_locks' => $clearedLocks,
                'admin' => auth()->user()?->email,
                'timestamp' => now(),
            ]);

            $message = "Unlocked '{$email}' across {$clearedLocks} active lock point(s).";

            if ($this->shouldReturnJson($request)) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->route('admin.account-locks.index')
                ->with('success', $message);
        }
    }

    /**
     * Unlock all accounts
     */
    public function unlockAll(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'confirm' => 'accepted',
        ]);

        $clearedLocks = AccountLockoutManager::clearAllActiveLocks();

        Log::warning('All account locks cleared', [
            'cleared_locks' => $clearedLocks,
            'admin' => auth()->user()?->email,
            'timestamp' => now(),
        ]);

        $message = "Cleared {$clearedLocks} active lock point(s).";

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('admin.account-locks.index')
            ->with('success', $message);
    }

    /**
     * Update rate limiting settings
     */
    public function updateSettings(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'max_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration' => 'required|integer|min:1|max:1440', // Max 24 hours
            'rate_limiting_enabled' => 'boolean',
            'email_unlock_enabled' => 'boolean',
        ], [
            'max_attempts.integer' => 'Max login attempts must be a whole number.',
            'max_attempts.min' => 'Max login attempts must be at least 1.',
            'max_attempts.max' => 'Max login attempts must be 20 or less.',
            'lockout_duration.integer' => 'Lockout duration must be a whole number of minutes.',
            'lockout_duration.min' => 'Lockout duration must be at least 1 minute.',
            'lockout_duration.max' => 'Lockout duration must be 1440 minutes or less (24 hours).',
        ], [
            'max_attempts' => 'max login attempts',
            'lockout_duration' => 'lockout duration',
        ]);

        // Update .env file
        $this->updateEnv([
            'SECURITY_MAX_LOGIN_ATTEMPTS' => $request->max_attempts,
            'SECURITY_LOCKOUT_DURATION' => $request->lockout_duration,
            'SECURITY_RATE_LIMITING_ENABLED' => $request->boolean('rate_limiting_enabled') ? 'true' : 'false',
            'SECURITY_EMAIL_UNLOCK_ENABLED' => $request->boolean('email_unlock_enabled') ? 'true' : 'false',
        ]);

        // Clear config cache
        \Artisan::call('config:clear');

        Log::info('Security settings updated', [
            'admin' => auth()->user()?->email,
            'settings' => $request->only(['max_attempts', 'lockout_duration']),
            'timestamp' => now(),
        ]);

        $message = 'Security settings updated successfully. New lock windows apply to future failed login attempts.';

        if ($this->shouldReturnJson($request)) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'settings' => [
                    'max_attempts' => (int) $request->max_attempts,
                    'lockout_duration' => (int) $request->lockout_duration,
                    'rate_limiting_enabled' => $request->boolean('rate_limiting_enabled'),
                    'email_unlock_enabled' => $request->boolean('email_unlock_enabled'),
                ],
            ]);
        }

        return redirect()->route('admin.account-locks.index')
            ->with('success', $message);
    }

    /**
     * Get all currently locked accounts
     */
    protected function getLockedAccounts(): array
    {
        if (!AccountLockoutManager::cacheStoreSupportsInspection()) {
            return [];
        }

        $lockedEntries = AccountLockoutManager::activeTimerEntries();

        if ($lockedEntries->isEmpty()) {
            return [];
        }

        $nowTimestamp = now()->timestamp;
        $parsedEntries = [];
        $emails = [];
        $lockedAccounts = [];

        foreach ($lockedEntries as $entry) {
            $throttleKey = AccountLockoutManager::extractThrottleKeyFromCacheKey((string) $entry->key);
            $parsedKey = $throttleKey ? AccountLockoutManager::parseThrottleKey($throttleKey) : null;

            if (!$parsedKey) {
                continue;
            }

            $secondsRemaining = max(0, (int) $entry->expiration - $nowTimestamp);

            if ($secondsRemaining === 0) {
                continue;
            }

            $email = $parsedKey['email'];
            $emails[$email] = $email;

            $parsedEntries[] = [
                'email' => $email,
                'ip' => $parsedKey['ip'],
                'seconds_remaining' => $secondsRemaining,
                'expires_at' => now()->copy()->addSeconds($secondsRemaining),
            ];
        }

        $usersByEmail = User::query()
            ->whereIn('email', array_values($emails))
            ->get()
            ->keyBy(fn (User $user) => AccountLockoutManager::normalizeEmail($user->email));

        foreach ($parsedEntries as $entry) {
            $email = $entry['email'];
            $minutesRemaining = max(1, (int) ceil($entry['seconds_remaining'] / 60));

            if (!isset($lockedAccounts[$email])) {
                $lockedAccounts[$email] = [
                    'user' => $usersByEmail->get($email),
                    'email' => $email,
                    'ips' => [],
                    'ip_count' => 0,
                    'max_minutes_remaining' => 0,
                    'latest_expires_at' => $entry['expires_at'],
                ];
            }

            $lockedAccounts[$email]['ips'][] = [
                'ip' => $entry['ip'],
                'minutes_remaining' => $minutesRemaining,
                'seconds_remaining' => $entry['seconds_remaining'],
                'expires_at' => $entry['expires_at'],
            ];

            $lockedAccounts[$email]['ip_count']++;
            $lockedAccounts[$email]['max_minutes_remaining'] = max(
                $lockedAccounts[$email]['max_minutes_remaining'],
                $minutesRemaining
            );

            if ($entry['expires_at']->gt($lockedAccounts[$email]['latest_expires_at'])) {
                $lockedAccounts[$email]['latest_expires_at'] = $entry['expires_at'];
            }
        }

        $accounts = array_values($lockedAccounts);

        foreach ($accounts as &$account) {
            usort($account['ips'], fn (array $left, array $right) => $right['seconds_remaining'] <=> $left['seconds_remaining']);
        }

        usort($accounts, function (array $left, array $right): int {
            return $right['max_minutes_remaining'] <=> $left['max_minutes_remaining']
                ?: strcmp($left['email'], $right['email']);
        });

        return $accounts;
    }

    protected function buildSummary(array $lockedAccounts): array
    {
        $ipLocks = collect($lockedAccounts)
            ->pluck('ips')
            ->flatten(1);

        $latestExpiryTimestamp = $ipLocks
            ->map(fn (array $lock) => $lock['expires_at']->timestamp ?? null)
            ->filter()
            ->max();

        return [
            'active_users' => count($lockedAccounts),
            'active_ip_locks' => $ipLocks->count(),
            'average_minutes_remaining' => $ipLocks->isNotEmpty()
                ? (int) ceil((float) $ipLocks->avg('minutes_remaining'))
                : 0,
            'expiring_soon' => $ipLocks
                ->filter(fn (array $lock) => ($lock['minutes_remaining'] ?? 0) <= 10)
                ->count(),
            'latest_expiry' => $latestExpiryTimestamp ? now()->copy()->setTimestamp($latestExpiryTimestamp) : null,
        ];
    }

    protected function buildIndexPayload(Request $request, array $viewData): array
    {
        /** @var \Illuminate\Pagination\LengthAwarePaginator $lockedAccounts */
        $lockedAccounts = $viewData['lockedAccounts'];

        return [
            'success' => true,
            'filters' => [
                'search' => $viewData['search'],
                'per_page' => $viewData['perPage'],
                'page' => $lockedAccounts->currentPage(),
            ],
            'config' => [
                'max_attempts' => $viewData['maxAttempts'],
                'lockout_duration' => $viewData['lockoutDuration'],
                'rate_limiting_enabled' => $viewData['rateLimitingEnabled'],
                'email_unlock_enabled' => $viewData['emailUnlockEnabled'],
                'supports_live_monitoring' => $viewData['supportsLiveMonitoring'],
                'cache_store' => $viewData['cacheStore'],
            ],
            'pagination' => [
                'current_page' => $lockedAccounts->currentPage(),
                'last_page' => $lockedAccounts->lastPage(),
                'per_page' => $lockedAccounts->perPage(),
                'total' => $lockedAccounts->total(),
                'from' => $lockedAccounts->firstItem() ?? 0,
                'to' => $lockedAccounts->lastItem() ?? 0,
            ],
            'fragments' => [
                'header_meta' => view('Admin.account-locks.partials.header-meta', $viewData)->render(),
                'summary' => view('Admin.account-locks.partials.summary', $viewData)->render(),
                'monitoring' => view('Admin.account-locks.partials.monitoring', $viewData)->render(),
                'feed' => view('Admin.account-locks.partials.feed', $viewData)->render(),
                'bulk' => view('Admin.account-locks.partials.bulk', $viewData)->render(),
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function filterLockedAccounts(array $lockedAccounts, string $search): array
    {
        if ($search === '') {
            return $lockedAccounts;
        }

        $needle = strtolower($search);

        return array_values(array_filter($lockedAccounts, function (array $account) use ($needle) {
            $ipAddresses = collect($account['ips'] ?? [])
                ->pluck('ip')
                ->filter()
                ->implode(' ');

            $haystack = strtolower(implode(' ', [
                $account['email'] ?? '',
                $account['user']?->name ?? '',
                $account['user']?->role ?? '',
                $ipAddresses,
            ]));

            return str_contains($haystack, $needle);
        }));
    }

    protected function normalizeAdminPerPage($value): int
    {
        $allowedValues = [10, 20, 50, 100];
        $perPage = (int) $value;

        return in_array($perPage, $allowedValues, true) ? $perPage : 10;
    }

    protected function shouldReturnJson(Request $request): bool
    {
        return $request->expectsJson()
            || $request->wantsJson()
            || $request->ajax();
    }

    /**
     * Update .env file
     */
    protected function updateEnv(array $values): void
    {
        $envFile = base_path('.env');

        foreach ($values as $key => $value) {
            $pattern = '/^' . preg_quote($key) . '=.*/m';
            $replacement = $key . '=' . $value;

            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);

                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, $replacement, $content);
                } else {
                    $content .= PHP_EOL . $replacement;
                }

                file_put_contents($envFile, $content);
            }
        }
    }
}
