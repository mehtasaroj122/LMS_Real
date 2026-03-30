<?php

use App\Models\FineSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function makeAdmin(array $overrides = []): User
{
    return User::forceCreate(array_merge([
        'role' => 'admin',
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'phone' => '9841234567',
        'address' => 'Kathmandu Metropolitan City, Nepal',
        'password' => Hash::make('Current!Pass123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
    ], $overrides));
}

function makeTinyPngUpload(): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'profile_');
    file_put_contents(
        $path,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+XxqQAAAAASUVORK5CYII=')
    );

    return new UploadedFile($path, 'profile.png', 'image/png', null, true);
}

test('admin profile can be updated with date of birth and photo', function () {
    Storage::fake('public');

    $admin = makeAdmin();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.settings.update'), [
            '_method' => 'PUT',
            'name' => 'Admin Settings',
            'email' => 'settings-admin@example.com',
            'phone' => '9800000001',
            'address' => 'Ward 10, Kathmandu, Nepal, Near Central Library',
            'date_of_birth' => '1990-05-15',
            'profile_photo' => makeTinyPngUpload(),
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    $admin->refresh();

    expect($admin->name)->toBe('Admin Settings');
    expect($admin->email)->toBe('settings-admin@example.com');
    expect($admin->phone)->toBe('9800000001');
    expect(optional($admin->date_of_birth)->format('Y-m-d'))->toBe('1990-05-15');
    expect($admin->profile_photo)->not->toBeNull();

    Storage::disk('public')->assertExists(str_replace('storage/', '', $admin->profile_photo));
});

test('admin profile update validates new profile rules', function () {
    $admin = makeAdmin();
    makeAdmin([
        'email' => 'duplicate@example.com',
        'phone' => '9800000002',
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.settings.update'), [
            '_method' => 'PUT',
            'name' => 'A1',
            'email' => 'duplicate@example.com',
            'phone' => '98abc',
            'address' => '',
            'date_of_birth' => now()->addDay()->toDateString(),
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'phone', 'date_of_birth'])
        ->assertJsonMissingValidationErrors(['address']);
});

test('admin profile can be updated without address', function () {
    $admin = makeAdmin([
        'address' => 'Existing saved address for admin user',
    ]);

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.settings.update'), [
            '_method' => 'PUT',
            'name' => 'Admin Optional Address',
            'email' => 'optional-address@example.com',
            'phone' => '9800000003',
            'address' => '',
            'date_of_birth' => '1992-06-10',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    $admin->refresh();

    expect($admin->address)->toBeNull();
});

test('admin password can be updated when all requirements are met', function () {
    Http::fake([
        'https://api.pwnedpasswords.com/*' => Http::response('', 200),
    ]);

    $admin = makeAdmin();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.settings.update-password'), [
            '_method' => 'PUT',
            'current_password' => 'Current!Pass123',
            'new_password' => 'EvenStronger!Pass456',
            'new_password_confirmation' => 'EvenStronger!Pass456',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Hash::check('EvenStronger!Pass456', $admin->fresh()->password))->toBeTrue();
});

test('admin library settings persist the remaining extended fields', function () {
    $admin = makeAdmin();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.settings.update-library'), [
            '_method' => 'PUT',
            'per_day_fine' => 12.50,
            'grace_period_days' => 3,
            'max_fine_amount' => 1500,
            'lost_book_penalty' => 2500,
            'damaged_book_penalty' => 500,
            'fair_condition_penalty' => 120,
            'issue_duration_days' => 21,
            'max_books_per_student' => 6,
            'renewal_limit' => 3,
            'renewal_duration_days' => 10,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    $settings = FineSetting::query()->first();

    expect((float) $settings->per_day_fine)->toBe(12.5);
    expect((float) $settings->fair_condition_penalty)->toBe(120.0);
    expect($settings->renewal_limit)->toBe(3);
    expect($settings->renewal_duration_days)->toBe(10);
});

test('admin library settings fill missing fields with defaults', function () {
    $admin = makeAdmin();

    $response = $this
        ->actingAs($admin)
        ->withHeader('Accept', 'application/json')
        ->post(route('admin.settings.update-library'), [
            '_method' => 'PUT',
            'per_day_fine' => 9.75,
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true);

    $settings = FineSetting::query()->firstOrFail();

    expect((float) $settings->per_day_fine)->toBe(9.75);
    expect($settings->grace_period_days)->toBe(2);
    expect((float) $settings->max_fine_amount)->toBe(500.0);
    expect((float) $settings->lost_book_penalty)->toBe(1000.0);
    expect((float) $settings->damaged_book_penalty)->toBe(250.0);
    expect((float) $settings->fair_condition_penalty)->toBe(50.0);
    expect($settings->issue_duration_days)->toBe(14);
    expect($settings->max_books_per_student)->toBe(5);
    expect($settings->renewal_limit)->toBe(2);
    expect($settings->renewal_duration_days)->toBe(7);
});
