<?php

namespace App\Domain\AI\DTO;

final readonly class ProductGenerationInput
{
    public const FIELDS = ['short_description', 'description', 'advantages', 'category_id'];

    public function __construct(
        public string $name,
        public ?string $price,
        public ?string $shortDescription,
        public ?string $description,
        public array $advantages,
        public ?int $categoryId,
        public array $categories,
        public array $missingFields,
        public ?string $imageJpeg = null,
    ) {}

    public function allowedCategoryIds(): array
    {
        return array_map(static fn (array $category): int => $category['id'], $this->categories);
    }
}
