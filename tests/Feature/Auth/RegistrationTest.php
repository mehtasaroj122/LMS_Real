<?php

use App\Mail\OTPVerificationMail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('registration request queues otp email and stores pending registration data', function () {
    Mail::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Password!123',
        'password_confirmation' => 'Password!123',
    ]);

    $response->assertRedirect(route('verify.otp.page', ['email' => 'test@example.com'], false));
    $this->assertGuest();

    expect(session('registration_data.email'))->toBe('test@example.com');
    expect(session('registration_data.name'))->toBe('Test User');

    Mail::assertQueued(OTPVerificationMail::class, function (OTPVerificationMail $mail) {
        return $mail->hasTo('test@example.com');
    });
});

test('valid otp verification creates the account and queues welcome email', function () {
    Mail::fake();

    $this->withSession([
        'registration_data' => [
            'name' => 'OTP User',
            'email' => 'otp-user@example.com',
            'password' => bcrypt('Password!123'),
            'otp' => '123456',
            'otp_expires_at' => now()->addMinutes(10)->timestamp,
        ],
    ]);

    $response = $this->post(route('verify.otp'), [
        'email' => 'otp-user@example.com',
        'otp1' => '1',
        'otp2' => '2',
        'otp3' => '3',
        'otp4' => '4',
        'otp5' => '5',
        'otp6' => '6',
    ]);

    $response->assertRedirect(route('student.dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::where('email', 'otp-user@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();
    expect(session('registration_data'))->toBeNull();

    Mail::assertQueued(WelcomeEmail::class, function (WelcomeEmail $mail) {
        return $mail->hasTo('otp-user@example.com');
    });
});
