<?php

use App\Mail\PasswordResetLinkMail;
use App\Models\User;
use App\Notifications\CustomResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, CustomResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, CustomResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, CustomResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('reset password notification mail includes the user as a recipient', function () {
    $user = User::factory()->create();

    $mailable = (new CustomResetPassword('test-token'))->toMail($user);

    $mailable->assertHasTo($user->email, $user->name);
});

test('password reset email renders a white call to action label without a logo block', function () {
    $html = (new PasswordResetLinkMail(
        'http://localhost:8000/reset-password/test-token?email=test@example.com',
        'test@example.com',
        'Test User'
    ))->render();

    expect($html)->toContain('color:#ffffff !important;');
    expect($html)->not->toContain('class="brand-logo"');
    expect($html)->not->toContain('class="brand-badge"');
});
