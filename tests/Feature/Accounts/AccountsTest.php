<?php

use App\Models\User;

// تسجيل الدخول عبر الـ API (Sanctum)
test('api login returns a token and the user', function () {
    $user = User::factory()->create(['password' => 'password']);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'full_name']]);

    $this->withToken($response->json('token'))
        ->getJson('/api/user')
        ->assertOk()
        ->assertJsonPath('email', $user->email);
});

test('api login rejects a wrong password', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertUnprocessable();
});

test('blocked users cannot log in', function () {
    $user = User::factory()->create(['password' => 'password']);
    $user->forceFill(['is_blocked' => true])->save();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
        ->assertUnprocessable();
});

test('a user blocked during the session is logged out', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $user->forceFill(['is_blocked' => true])->save();

    $this->get('/profile')->assertRedirect(route('login'));
    $this->assertGuest();
});

test('profile location is saved approximately', function () {
    $user = User::factory()->create(['phone' => '0933111222', 'address' => 'المزة']);

    $this->actingAs($user)->patch('/profile', [
        'full_name' => $user->full_name,
        'email' => $user->email,
        'phone' => '0933111222',
        'address' => 'المزة',
        'city' => 'دمشق',
        'latitude' => 33.503215,
        'longitude' => 36.254189,
    ])->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->city)->toBe('دمشق')
        ->and($user->latitude)->toBe(33.503)
        ->and($user->longitude)->toBe(36.254);
});
