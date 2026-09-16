<?php

namespace App\Domain\AI\Contracts;

use App\Domain\AI\DTO\GeneratedImage;
use App\Domain\AI\DTO\ProductGenerationInput;
use App\Domain\AI\DTO\ProductGenerationResult;
use App\Domain\AI\DTO\ProductImageGenerationInput;
use App\Domain\AI\DTO\ResolvedAiProvider;

interface AiProviderClientInterface
{
    public function identifier(): string;

    public function generateProductData(ProductGenerationInput $input, ResolvedAiProvider $provider): ProductGenerationResult;

    public function generateImage(ProductImageGenerationInput $input, ResolvedAiProvider $provider): GeneratedImage;
}
