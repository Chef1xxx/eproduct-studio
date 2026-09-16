<?php

namespace App\Domain\AI\DTO;

final readonly class ProductAiOutcome
{
    public function __construct(
        public ?string $shortDescription,
        public ?string $description,
        public ?array $advantages,
        public ?int $categoryId,
        public ?GeneratedImage $generatedImage,
    ) {}

    public static function nothingGenerated(): self
    {
        return new self(
            shortDescription: null,
            description: null,
            advantages: null,
            categoryId: null,
            generatedImage: null,
        );
    }
}
