<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeSortableAdmin(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'role' => 'admin',
        'name' => 'Sortable Admin',
        'email' => 'sortable-admin-' . Str::lower(Str::random(6)) . '@example.com',
        'phone' => '+9779800000100',
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Admin!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

test('admin user management page can be rendered', function () {
    $admin = makeSortableAdmin();

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.users.index'));

    $response
        ->assertOk()
        ->assertSee('User Management');
});

test('admin user management keeps the logged-in admin at the top while recently added sorts the remaining users', function () {
    $admin = makeSortableAdmin([
        'name' => 'Oldest Admin',
        'email' => 'oldest-admin@example.com',
        'phone' => '+9779800000101',
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);

    User::forceCreate([
        'role' => 'staff',
        'name' => 'Middle Staff',
        'email' => 'middle-staff@example.com',
        'phone' => '+9779800000102',
        'address' => 'Lalitpur, Nepal',
        'password' => Hash::make('Staff!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(5),
    ]);

    User::forceCreate([
        'role' => 'student',
        'name' => 'Newest Student',
        'email' => 'newest-student@example.com',
        'phone' => '+9779800000103',
        'address' => 'Pokhara, Nepal',
        'password' => Hash::make('Student!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->get(route('admin.users.data', ['sort' => 'recently-added']));

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    $tableRows = (string) $response->json('tableRows');

    expect(strpos($tableRows, 'Oldest Admin'))->toBeLessThan(strpos($tableRows, 'Newest Student'));
    expect(strpos($tableRows, 'Newest Student'))->toBeLessThan(strpos($tableRows, 'Middle Staff'));
});
