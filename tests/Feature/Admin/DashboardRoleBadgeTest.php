<?php

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('recent activity role badges use readable paired design-system colors', function () {
    $admin = User::forceCreate([
        'role' => 'admin',
        'name' => 'Dashboard Admin',
        'email' => 'dashboard-admin@example.com',
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    $staff = User::forceCreate([
        'role' => 'staff',
        'name' => 'Dashboard Staff',
        'email' => 'dashboard-staff@example.com',
        'password' => Hash::make('Password!123'),
        'status' => 'active',
        'is_verified' => true,
    ]);

    foreach ([$admin, $staff] as $user) {
        ActivityLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => 'login',
            'description' => "{$user->name} logged in",
        ]);
    }

    $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('dashboard-role-admin', false)
        ->assertSee('dashboard-role-staff', false)
        ->assertDontSee('dark:bg-red-600', false)
        ->assertDontSee('dark:bg-blue-600', false);
});
