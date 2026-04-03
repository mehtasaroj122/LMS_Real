<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeStaffNotificationUser(array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => 'staff',
        'name' => 'Notification Staff ' . $unique,
        'email' => "notification-staff-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
    ], $overrides));
}

function makeStaffNotification(User $user, array $overrides = []): Notification
{
    return Notification::query()->create(array_merge([
        'user_id' => $user->id,
        'type' => 'fine.reminder',
        'title' => 'Fine reminder',
        'message' => 'A fine reminder notification is ready for review.',
        'data' => ['source' => 'tests'],
        'related_model' => 'Fine',
        'related_id' => 101,
        'read_at' => null,
    ], $overrides));
}

test('staff notification detail endpoint returns only the signed-in staff users notification', function () {
    $staff = makeStaffNotificationUser([
        'email' => 'staff-notification-detail@example.com',
    ]);
    $otherStaff = makeStaffNotificationUser([
        'email' => 'other-staff-notification-detail@example.com',
    ]);

    $notification = makeStaffNotification($staff, [
        'title' => 'Book overdue alert',
        'message' => 'A borrowed book is overdue and needs staff attention.',
    ]);
    $foreignNotification = makeStaffNotification($otherStaff, [
        'title' => 'Other staff notification',
    ]);

    $this
        ->actingAs($staff)
        ->getJson(route('staff.notifications.show', $notification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $notification->id)
        ->assertJsonPath('data.title', 'Book overdue alert')
        ->assertJsonPath('data.message', 'A borrowed book is overdue and needs staff attention.');

    $this
        ->actingAs($staff)
        ->getJson(route('staff.notifications.show', $foreignNotification))
        ->assertNotFound()
        ->assertJsonPath('success', false);
});

test('staff notification index response includes unread count with the notification payload', function () {
    $staff = makeStaffNotificationUser([
        'email' => 'staff-notification-index@example.com',
    ]);

    makeStaffNotification($staff, [
        'title' => 'Unread notification one',
    ]);
    makeStaffNotification($staff, [
        'title' => 'Read notification',
        'read_at' => now()->subMinutes(10),
    ]);
    makeStaffNotification($staff, [
        'title' => 'Unread notification two',
    ]);

    $this
        ->actingAs($staff)
        ->getJson(route('staff.notifications.index'))
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('unread_count', 2);
});

test('staff notification read and unread count endpoints use the custom notifications table', function () {
    $staff = makeStaffNotificationUser([
        'email' => 'staff-notification-read@example.com',
    ]);

    $unreadNotification = makeStaffNotification($staff, [
        'title' => 'Unread notification',
    ]);
    makeStaffNotification($staff, [
        'title' => 'Already read notification',
        'read_at' => now()->subHour(),
    ]);

    $this
        ->actingAs($staff)
        ->getJson(route('staff.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1);

    $this
        ->actingAs($staff)
        ->postJson(route('staff.notifications.mark-read', $unreadNotification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $unreadNotification->id);

    expect($unreadNotification->fresh()->read_at)->not->toBeNull();

    $this
        ->actingAs($staff)
        ->getJson(route('staff.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('unread_count', 0);
});

test('staff notification delete endpoint removes notifications from the custom notifications table', function () {
    $staff = makeStaffNotificationUser([
        'email' => 'staff-notification-delete@example.com',
    ]);

    $notification = makeStaffNotification($staff, [
        'title' => 'Delete me',
    ]);

    $this
        ->actingAs($staff)
        ->deleteJson(route('staff.notifications.destroy', $notification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Notification deleted');

    expect(Notification::query()->whereKey($notification->id)->exists())->toBeFalse();
});
