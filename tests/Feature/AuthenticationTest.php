<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'seller@example.com',
        'password' => 'password',
    ]);

    $this->post('/login', [
        'email' => 'seller@example.com',
        'password' => 'password',
    ])
        ->assertRedirect(route('seller.index'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    User::factory()->create([
        'email' => 'seller@example.com',
        'password' => 'password',
    ]);

    $this->from(route('login'))
        ->post('/login', [
            'email' => 'seller@example.com',
            'password' => 'wrong-password',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('home'));

    $this->assertGuest();
});