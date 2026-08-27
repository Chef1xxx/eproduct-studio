<?php

namespace App\Http\Controllers\Web\Auth;

use App\Domain\Auth\Services\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    public function createRegister(): Response
    {
        return Inertia::render('Auth/RegisterPage');
    }

    public function storeRegister(RegisterRequest $request): RedirectResponse
    {
        $this->auth->register(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        $request->session()->regenerate();

        return redirect()->route('seller.index');
    }

    public function createLogin(): Response
    {
        return Inertia::render('Auth/LoginPage');
    }

    public function storeLogin(LoginRequest $request): RedirectResponse
    {
        $this->auth->attemptLogin(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        $request->session()->regenerate();

        return redirect()->route('seller.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->auth->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}