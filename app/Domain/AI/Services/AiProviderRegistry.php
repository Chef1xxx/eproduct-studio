<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\Contracts\AiProviderClientInterface;
use App\Domain\AI\Exceptions\AiProviderException;

final class AiProviderRegistry
{
    private array $clients = [];

    public function __construct(iterable $clients)
    {
        foreach ($clients as $client) {
            $this->clients[$client->identifier()] = $client;
        }
    }

    public function get(string $driver): AiProviderClientInterface
    {
        return $this->clients[$driver] ?? throw AiProviderException::unknownDriver($driver);
    }
}
