<?php

namespace App\Domain\AI\DTO;

final readonly class ResolvedAiProvider
{
    public function __construct(
        public string $driver,
        public string $model,
        public string $scope,
        public string $authorizationKey,
        public int $providerId,
        public int $credentialId,
    ) {}

    public function __debugInfo(): array
    {
        return [
            'driver' => $this->driver,
            'model' => $this->model,
            'scope' => $this->scope,
            'authorizationKey' => '***',
            'providerId' => $this->providerId,
            'credentialId' => $this->credentialId,
        ];
    }
}
