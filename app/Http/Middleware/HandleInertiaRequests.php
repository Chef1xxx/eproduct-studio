<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\DTO\UserDto;
use App\Models\User;


class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
    public function share(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user !== null
                    ? UserDto::fromModel($user)
                    : null,
            ],
        ];
    }
}