<?php

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

function makeStudentNotificationUser(array $overrides = []): User
{
    $unique = Str::lower(Str::random(8));

    return User::forceCreate(array_merge([
        'role' => 'student',
        'name' => 'Notification Student ' . $unique,
        'email' => "notification-student-{$unique}@example.com",
        'phone' => '98' . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'address' => 'Kathmandu, Nepal',
        'password' => Hash::make('Password!123'),
        'remember_token' => Str::random(10),
        'status' => 'active',
        'is_verified' => true,
    ], $overrides));
}

function makeStudentNotification(User $user, array $overrides = []): Notification
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

test('student notification detail endpoint returns only the signed-in students notification', function () {
    $student = makeStudentNotificationUser([
        'email' => 'student-notification-detail@example.com',
    ]);
    $otherStudent = makeStudentNotificationUser([
        'email' => 'other-student-notification-detail@example.com',
    ]);

    $notification = makeStudentNotification($student, [
        'title' => 'Book due soon',
        'message' => 'One of your borrowed books is due soon.',
    ]);
    $foreignNotification = makeStudentNotification($otherStudent, [
        'title' => 'Other student notification',
    ]);

    $this
        ->actingAs($student)
        ->getJson(route('student.notifications.show', $notification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $notification->id)
        ->assertJsonPath('data.title', 'Book due soon')
        ->assertJsonPath('data.message', 'One of your borrowed books is due soon.');

    $this
        ->actingAs($student)
        ->getJson(route('student.notifications.show', $foreignNotification))
        ->assertNotFound()
        ->assertJsonPath('success', false);
});

test('student notification index response includes unread count with the notification payload', function () {
    $student = makeStudentNotificationUser([
        'email' => 'student-notification-index@example.com',
    ]);

    makeStudentNotification($student, [
        'title' => 'Unread notification one',
    ]);
    makeStudentNotification($student, [
        'title' => 'Read notification',
        'read_at' => now()->subMinutes(10),
    ]);
    makeStudentNotification($student, [
        'title' => 'Unread notification two',
    ]);

    $this
        ->actingAs($student)
        ->getJson(route('student.notifications.index'))
        ->assertOk()
        ->assertJsonPath('total', 3)
        ->assertJsonPath('unread_count', 2);
});

test('student notification read and unread count endpoints use the custom notifications table', function () {
    $student = makeStudentNotificationUser([
        'email' => 'student-notification-read@example.com',
    ]);

    $unreadNotification = makeStudentNotification($student, [
        'title' => 'Unread notification',
    ]);
    makeStudentNotification($student, [
        'title' => 'Already read notification',
        'read_at' => now()->subHour(),
    ]);

    $this
        ->actingAs($student)
        ->getJson(route('student.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1);

    $this
        ->actingAs($student)
        ->postJson(route('student.notifications.mark-read', $unreadNotification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $unreadNotification->id);

    expect($unreadNotification->fresh()->read_at)->not->toBeNull();

    $this
        ->actingAs($student)
        ->getJson(route('student.notifications.unread-count'))
        ->assertOk()
        ->assertJsonPath('unread_count', 0);
});

test('student notification delete endpoint removes notifications from the custom notifications table', function () {
    $student = makeStudentNotificationUser([
        'email' => 'student-notification-delete@example.com',
    ]);

    $notification = makeStudentNotification($student, [
        'title' => 'Delete me',
    ]);

    $this
        ->actingAs($student)
        ->deleteJson(route('student.notifications.destroy', $notification))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Notification deleted');

    expect(Notification::query()->whereKey($notification->id)->exists())->toBeFalse();
});
