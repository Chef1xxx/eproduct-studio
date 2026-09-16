<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\DTO\ResolvedAiProvider;
use App\Domain\AI\Exceptions\AiProviderException;
use App\Models\AiProvider;

final class AiProviderResolver
{
    public function resolve(): ResolvedAiProvider
    {
        $provider = AiProvider::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if ($provider === null) {
            throw AiProviderException::noActiveProvider();
        }

        $credential = $provider->credentials()
            ->where('is_active', true)
            ->orderByRaw('last_used_at IS NOT NULL')
            ->orderBy('last_used_at')
            ->orderBy('id')
            ->first();

        if ($credential === null) {
            throw AiProviderException::noActiveCredential($provider->name);
        }

        $credential->forceFill(['last_used_at' => now()])->save();

        return new ResolvedAiProvider(
            driver: $provider->driver,
            model: $provider->model,
            scope: $provider->scope,
            authorizationKey: $credential->authorization_key,
            providerId: $provider->id,
            credentialId: $credential->id,
        );
    }
}
