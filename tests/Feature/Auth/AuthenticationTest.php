<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('student.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('auth')
        ->assertSessionDoesntHaveErrors(['email', 'password']);

    $this->assertGuest();
});

test('login validation errors stay attached to their fields', function () {
    $this->post('/login', [
        'email' => 'not-an-email',
        'password' => '',
    ])->assertSessionHasErrors(['email', 'password'])
        ->assertSessionDoesntHaveErrors('auth');
});

test('inactive users are redirected to the account inactive page', function () {
    $user = User::factory()->create([
        'status' => 'inactive',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('account.inactive'));

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
