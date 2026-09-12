<?php

namespace App\Domain\AI\DTO;

final readonly class ProductImageGenerationInput
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {}
}
