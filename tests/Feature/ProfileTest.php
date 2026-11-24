<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/profile')->assertOk();
});

test('profile edit page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile/edit');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'full_name' => 'Test User',
            'phone' => '0912345678',
            'address' => 'حي المزة، دمشق',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile/edit');

    $user->refresh();

    $this->assertSame('Test User', $user->full_name);
    $this->assertSame('0912345678', $user->phone);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'full_name' => 'Test User',
            'phone' => '0912345678',
            'address' => 'حي المزة، دمشق',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile/edit');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('password can be changed from the profile form', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => '0912345678',
            'address' => 'حي المزة، دمشق',
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasNoErrors();

    $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
});

test('password is not changed when the current password is wrong', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->patch('/profile', [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => '0912345678',
            'address' => 'حي المزة، دمشق',
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertSessionHasErrors('current_password');

    $this->assertTrue(Hash::check('password', $user->refresh()->password));
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile/edit')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile/edit');

    $this->assertNotNull($user->fresh());
});
