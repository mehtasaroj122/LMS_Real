<?php

use App\Models\User;
use App\Notifications\CustomResetPassword;
use Illuminate\Support\Facades\Notification as NotificationFacade;

test('mobile forgot password sends reset link for an existing user', function () {
    NotificationFacade::fake();

    $user = User::factory()->create([
        'email' => 'mobile-reset@example.com',
        'phone' => '+9779812345001',
    ]);

    $this->postJson('/api/forgot-password', [
        'email' => 'mobile-reset@example.com',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'We have emailed your password reset link.')
        ->assertJsonPath('data', []);

    NotificationFacade::assertSentTo($user, CustomResetPassword::class);

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => 'mobile-reset@example.com',
    ]);
});

test('mobile forgot password rejects an unknown email address', function () {
    NotificationFacade::fake();

    $this->postJson('/api/forgot-password', [
        'email' => 'missing-mobile-reset@example.com',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', "We can't find a user with that email address.")
        ->assertJsonValidationErrors('email')
        ->assertJsonPath('errors.email.0', "We can't find a user with that email address.");

    NotificationFacade::assertNothingSent();

    $this->assertDatabaseMissing('password_reset_tokens', [
        'email' => 'missing-mobile-reset@example.com',
    ]);
});

test('mobile forgot password validates email format', function () {
    $this->postJson('/api/forgot-password', [
        'email' => 'not-an-email',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'The given data was invalid.')
        ->assertJsonValidationErrors('email');
});
