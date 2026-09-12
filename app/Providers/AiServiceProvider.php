<?php

namespace App\Providers;

use App\Domain\AI\Services\AiProviderRegistry;
use App\Infrastructure\AI\GigaChatClient;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public const CLIENTS_TAG = 'ai.provider-clients';

    public function register(): void
    {
        $this->app->tag([
            GigaChatClient::class,
        ], self::CLIENTS_TAG);

        $this->app->bind(AiProviderRegistry::class, fn (Application $app) => new AiProviderRegistry(
            $app->tagged(self::CLIENTS_TAG),
        ));
    }
}
