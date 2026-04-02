<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\AccountLockoutManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UnlockAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:unlock-account {email} {--all-ips}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock a locked user account by clearing rate limit locks. Use --all-ips to unlock all IP addresses.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $allIps = $this->option('all-ips');

        // Verify user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return self::FAILURE;
        }

        if ($allIps) {
            // Clear all possible IP locks for this email
            // This is a brute force approach - clear all cache entries matching the email
            $this->clearAllIpLocks($email);
            $this->info("✅ All account locks cleared for user '{$email}' across all IP addresses.");
        } else {
            // Get current request IP or allow user to specify
            $ip = $this->ask('Enter the IP address to unlock (leave blank for all)', null);

            if ($ip) {
                AccountLockoutManager::clearLock($email, $ip);
                $this->info("✅ Account lock cleared for '{$email}' from IP '{$ip}'");
                Log::info("Account unlocked", [
                    'email' => $email,
                    'ip' => $ip,
                    'admin' => auth()->user()?->email ?? 'command-line',
                    'timestamp' => now(),
                ]);
            } else {
                $this->clearAllIpLocks($email);
                $this->info("✅ All account locks cleared for user '{$email}' across all IP addresses.");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Clear all IP locks for a given email
     */
    protected function clearAllIpLocks(string $email): void
    {
        AccountLockoutManager::clearLocksForEmail($email);

        Log::info("All account locks cleared", [
            'email' => $email,
            'admin' => auth()->user()?->email ?? 'command-line',
            'timestamp' => now(),
        ]);
    }
}
