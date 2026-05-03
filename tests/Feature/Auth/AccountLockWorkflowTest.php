<?php

use App\Notifications\AccountLockedNotification;
use App\Models\User;
use App\Support\AccountLockoutManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

function makeAccountLockUser(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'name' => 'Account Lock Test User',
        'email' => 'lock-test-' . Str::random(6) . '@example.com',
        'password' => Hash::make('password'),
        'remember_token' => Str::random(10),
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'role' => 'student',
        'status' => 'active',
    ], $overrides));
}

test('failed logins honor the configured lockout duration', function () {
    config([
        'security.rate_limiting.enabled' => true,
        'security.rate_limiting.max_attempts' => 5,
        'security.rate_limiting.lockout_duration' => 3,
    ]);

    $user = makeAccountLockUser();
    $throttleKey = AccountLockoutManager::throttleKey($user->email, '127.0.0.1');

    RateLimiter::clear($throttleKey);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('auth');

    $remainingSeconds = RateLimiter::availableIn($throttleKey);

    expect($remainingSeconds)->toBeGreaterThanOrEqual(170);
    expect($remainingSeconds)->toBeLessThanOrEqual(180);

    RateLimiter::clear($throttleKey);
});

test('account lockout queues a security email notification when email unlock is enabled', function () {
    Notification::fake();

    config([
        'security.rate_limiting.enabled' => true,
        'security.rate_limiting.max_attempts' => 1,
        'security.rate_limiting.lockout_duration' => 2,
        'security.rate_limiting.email_unlock_enabled' => true,
    ]);

    $user = makeAccountLockUser([
        'email' => 'security-lock@example.com',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('auth');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('auth');

    Notification::assertSentTo($user, AccountLockedNotification::class);
});

test('admin account lock dashboard lists and clears active lockouts', function () {
    config([
        'cache.default' => 'database',
        'security.rate_limiting.lockout_duration' => 5,
    ]);

    $admin = makeAccountLockUser([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $lockedUser = makeAccountLockUser([
        'email' => 'locked-user@example.com',
    ]);

    $throttleKey = AccountLockoutManager::throttleKey($lockedUser->email, '127.0.0.1');

    RateLimiter::clear($throttleKey);
    RateLimiter::hit($throttleKey, AccountLockoutManager::decaySeconds());

    $this->actingAs($admin)
        ->get(route('admin.account-locks.index'))
        ->assertOk()
        ->assertSee('Account Lock Management')
        ->assertSee('locked-user@example.com')
        ->assertSee('127.0.0.1');

    $this->actingAs($admin)
        ->post(route('admin.account-locks.unlock'), [
            'email' => $lockedUser->email,
            'ip' => '127.0.0.1',
        ])
        ->assertRedirect(route('admin.account-locks.index'));

    expect(RateLimiter::availableIn($throttleKey))->toBe(0);
});

test('account lock dashboard exposes ajax fragments for live refresh', function () {
    config([
        'cache.default' => 'database',
        'security.rate_limiting.lockout_duration' => 5,
    ]);

    $admin = makeAccountLockUser([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $lockedUser = makeAccountLockUser([
        'email' => 'fragment-lock@example.com',
    ]);

    $throttleKey = AccountLockoutManager::throttleKey($lockedUser->email, '127.0.0.1');

    RateLimiter::clear($throttleKey);
    RateLimiter::hit($throttleKey, AccountLockoutManager::decaySeconds());

    $response = $this->actingAs($admin)
        ->get(route('admin.account-locks.index', ['search' => 'fragment-lock']), [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('filters.search', 'fragment-lock')
        ->assertJsonStructure([
            'config' => [
                'max_attempts',
                'lockout_duration',
                'rate_limiting_enabled',
                'email_unlock_enabled',
                'supports_live_monitoring',
                'cache_store',
            ],
            'pagination' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to'],
            'fragments' => ['header_meta', 'summary', 'monitoring', 'feed', 'bulk'],
        ]);

    expect($response->json('fragments.feed'))->toContain('fragment-lock@example.com');
});

test('ajax unlock request clears the lock and returns json feedback', function () {
    config([
        'cache.default' => 'database',
        'security.rate_limiting.lockout_duration' => 5,
    ]);

    $admin = makeAccountLockUser([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $lockedUser = makeAccountLockUser([
        'email' => 'ajax-unlock@example.com',
    ]);

    $throttleKey = AccountLockoutManager::throttleKey($lockedUser->email, '127.0.0.1');

    RateLimiter::clear($throttleKey);
    RateLimiter::hit($throttleKey, AccountLockoutManager::decaySeconds());

    $this->actingAs($admin)
        ->post(route('admin.account-locks.unlock'), [
            'email' => $lockedUser->email,
            'ip' => '127.0.0.1',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', "Unlocked 'ajax-unlock@example.com' for IP '127.0.0.1'.");

    expect(RateLimiter::availableIn($throttleKey))->toBe(0);
});

test('account lock dashboard explains the policy ranges and minute unit clearly', function () {
    config([
        'security.rate_limiting.max_attempts' => 7,
        'security.rate_limiting.lockout_duration' => 12,
    ]);

    $admin = makeAccountLockUser([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.account-locks.index'))
        ->assertOk()
        ->assertSeeText('Allowed range: 1 to 20 failed attempts. Any value above 20 will be rejected.')
        ->assertSeeText('Lockout Duration (minutes)')
        ->assertSeeText('This value is in minutes. Example: 1 = 1 minute, 60 = 1 hour.')
        ->assertSeeText('7 attempts / 12 min');
});

test('ajax settings validation explains the max attempts cap clearly', function () {
    $admin = makeAccountLockUser([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.account-locks.settings'), [
            'max_attempts' => 21,
            'lockout_duration' => 5,
            'rate_limiting_enabled' => '1',
            'email_unlock_enabled' => '1',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['max_attempts'])
        ->assertJsonPath('errors.max_attempts.0', 'Max login attempts must be 20 or less.');
});
