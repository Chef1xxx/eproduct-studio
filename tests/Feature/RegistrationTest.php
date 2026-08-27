<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a user and redirects to seller', function () {
    $response = $this->post('/register', [
        'name' => 'Максим',
        'email' => 'maxim@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('seller.index'));

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'maxim@example.com',
        'name' => 'Максим',
    ]);
});

it('rejects registration with duplicate email', function () {
    User::factory()->create([
        'email' => 'taken@example.com',
    ]);

    $this->from(route('register'))
        ->post('/register', [
            'name' => 'Другой',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});