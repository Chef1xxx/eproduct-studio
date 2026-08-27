<?php

namespace App\Domain\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    public function register(string $name, string $email, string $password): User
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        Auth::guard('web')->login($user);

        return $user;
    }

    public function attemptLogin(string $email, string $password): User
    {
        if (! Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
        ])) {
            throw ValidationException::withMessages([
                'email' => 'Неверный email или пароль',
            ]);
        }

        /** @var User $user */
        $user = Auth::guard('web')->user();

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}