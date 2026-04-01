<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountLockController extends \App\Http\Controllers\Controller
{
    /**
     * Show locked accounts list
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $perPage = $this->normalizeAdminPerPage($request->input('per_page', 10));
        $page = max(1, (int) $request->input('page', 1));

        $lockedAccounts = $this->getLockedAccounts();
        $lockedAccounts = $this->filterLockedAccounts($lockedAccounts, $search);
        $totalLockedAccounts = count($lockedAccounts);
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

        return view('admin.account-locks.index', [
            'lockedAccounts' => $lockedAccounts,
            'lockoutDuration' => config('security.rate_limiting.lockout_duration', 60),
            'maxAttempts' => config('security.rate_limiting.max_attempts', 5),
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Unlock a specific account
     */
    public function unlock(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'ip' => 'nullable|ip',
        ]);

        $email = $request->email;
        $ip = $request->ip;

        if ($ip) {
            // Unlock specific IP
            $throttleKey = strtolower($email) . '|' . $ip;
            RateLimiter::clear($throttleKey);
            
            Log::info('Account unlock - specific IP', [
                'email' => $email,
                'ip' => $ip,
                'admin' => auth()->user()?->email,
                'timestamp' => now(),
            ]);

            return redirect()->route('admin.account-locks.index')
                ->with('success', "✅ Account '{$email}' unlocked for IP '{$ip}'");
        } else {
            // Unlock all IPs for this email
            $this->unlockAllIps($email);

            Log::info('Account unlock - all IPs', [
                'email' => $email,
                'admin' => auth()->user()?->email,
                'timestamp' => now(),
            ]);

            return redirect()->route('admin.account-locks.index')
                ->with('success', "✅ Account '{$email}' unlocked for all IP addresses");
        }
    }

    /**
     * Unlock all accounts
     */
    public function unlockAll(Request $request): RedirectResponse
    {
        if (!$request->has('confirm')) {
            return redirect()->route('admin.account-locks.index')
                ->with('error', '❌ Confirmation required');
        }

        DB::table('cache')
            ->where('key', 'like', '%throttle%')
            ->delete();

        Log::warning('All account locks cleared', [
            'admin' => auth()->user()?->email,
            'timestamp' => now(),
        ]);

        return redirect()->route('admin.account-locks.index')
            ->with('success', '✅ All account locks cleared');
    }

    /**
     * Update rate limiting settings
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'max_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration' => 'required|integer|min:1|max:1440', // Max 24 hours
            'rate_limiting_enabled' => 'boolean',
            'email_unlock_enabled' => 'boolean',
        ]);

        // Update .env file
        $this->updateEnv([
            'SECURITY_MAX_LOGIN_ATTEMPTS' => $request->max_attempts,
            'SECURITY_LOCKOUT_DURATION' => $request->lockout_duration,
            'SECURITY_RATE_LIMITING_ENABLED' => $request->has('rate_limiting_enabled') ? 'true' : 'false',
            'SECURITY_EMAIL_UNLOCK_ENABLED' => $request->has('email_unlock_enabled') ? 'true' : 'false',
        ]);

        // Clear config cache
        \Artisan::call('config:clear');

        Log::info('Security settings updated', [
            'admin' => auth()->user()?->email,
            'settings' => $request->only(['max_attempts', 'lockout_duration']),
            'timestamp' => now(),
        ]);

        return redirect()->route('admin.account-locks.index')
            ->with('success', '✅ Security settings updated successfully');
    }

    /**
     * Get all currently locked accounts
     */
    protected function getLockedAccounts(): array
    {
        $lockedEntries = DB::table('cache')
            ->where('key', 'like', '%throttle%')
            ->where('expiration', '>', now()->timestamp)
            ->get();

        $lockedAccounts = [];
        $maxAttempts = config('security.rate_limiting.max_attempts', 5);

        foreach ($lockedEntries as $entry) {
            // Extract email and IP from key
            // Key format: laravel_cache:throttle|email@example.com|192.168.1.1
            $keyParts = explode('|', $entry->key);
            if (count($keyParts) >= 3) {
                $email = $keyParts[1];
                $ip = $keyParts[2] ?? null;

                $user = User::where('email', $email)->first();
                
                $lockoutTime = $entry->expiration - now()->timestamp;
                $minutesRemaining = ceil($lockoutTime / 60);

                if (!isset($lockedAccounts[$email])) {
                    $lockedAccounts[$email] = [
                        'user' => $user,
                        'email' => $email,
                        'ips' => [],
                        'locked_at' => now()->subMinutes($minutesRemaining + config('security.rate_limiting.lockout_duration', 60)),
                    ];
                }

                $lockedAccounts[$email]['ips'][] = [
                    'ip' => $ip,
                    'minutes_remaining' => $minutesRemaining,
                    'expires_at' => now()->addSeconds($lockoutTime),
                ];
            }
        }

        return array_values($lockedAccounts);
    }

    /**
     * Unlock all IPs for a user
     */
    protected function unlockAllIps(string $email): void
    {
        DB::table('cache')
            ->where('key', 'like', '%throttle|' . strtolower($email) . '%')
            ->delete();
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
