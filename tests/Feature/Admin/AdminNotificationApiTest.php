<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeAdminNotificationUser(array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => 'admin',
        'name' => 'Notification Admin ' . $unique,
        'email' => "notification-admin-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
    ], $overrides));
}

function makeAdminNotification(User $user, array $overrides = []): Notification
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

test('admin notification detail endpoint returns only the signed-in admins notification', function () {
    $admin = makeAdminNotificationUser([
        'email' => 'admin-notification-detail@example.com',
    ]);
    $otherAdmin = makeAdminNotificationUser([
        'email' => 'other-admin-notification-detail@example.com',
    ]);

    $notification = makeAdminNotification($admin, [
        'title' => 'Book overdue alert',
        'message' => 'A borrowed book is overdue and needs attention.',
    ]);
    $foreignNotification = makeAdminNotification($otherAdmin, [
        'title' => 'Other admin notification',
    ]);

    $this
        ->actingAs($admin)
        ->getJson(route('admin.notifications.show', $notification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $notification->id)
        ->assertJsonPath('data.title', 'Book overdue alert')
        ->assertJsonPath('data.message', 'A borrowed book is overdue and needs attention.');

    $this
        ->actingAs($admin)
        ->getJson(route('admin.notifications.show', $foreignNotification))
        ->assertNotFound()
        ->assertJsonPath('success', false);
});

test('admin notification index response includes unread count with the notification payload', function () {
    $admin = makeAdminNotificationUser([
        'email' => 'admin-notification-index@example.com',
    ]);

    makeAdminNotification($admin, [
        'title' => 'Unread notification one',
    ]);
    makeAdminNotification($admin, [
        'title' => 'Read notification',
        'read_at' => now()->subMinutes(10),
    ]);
    makeAdminNotification($admin, [
        'title' => 'Unread notification two',
    ]);

    $this
        ->actingAs($admin)
        ->getJson(route('admin.notifications.index'))
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('unread_count', 2);
});

test('admin notification read and unread count endpoints use the custom notifications table', function () {
    $admin = makeAdminNotificationUser([
        'email' => 'admin-notification-read@example.com',
    ]);

    $unreadNotification = makeAdminNotification($admin, [
        'title' => 'Unread notification',
    ]);
    makeAdminNotification($admin, [
        'title' => 'Already read notification',
        'read_at' => now()->subHour(),
    ]);

    $this
        ->actingAs($admin)
        ->getJson(route('admin.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1);

    $this
        ->actingAs($admin)
        ->postJson(route('admin.notifications.mark-read', $unreadNotification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $unreadNotification->id);

    expect($unreadNotification->fresh()->read_at)->not->toBeNull();

    $this
        ->actingAs($admin)
        ->getJson(route('admin.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('unread_count', 0);
});

test('admin notification delete endpoint removes notifications from the custom notifications table', function () {
    $admin = makeAdminNotificationUser([
        'email' => 'admin-notification-delete@example.com',
    ]);

    $notification = makeAdminNotification($admin, [
        'title' => 'Delete me',
    ]);

    $this
        ->actingAs($admin)
        ->deleteJson(route('admin.notifications.destroy', $notification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Notification deleted');

    expect(Notification::query()->whereKey($notification->id)->exists())->toBeFalse();
});
