<?php

namespace App\Domain\AI\DTO;

use Illuminate\Http\UploadedFile;

final readonly class ProductGenerationData
{
    public function __construct(
        public string $name,
        public ?string $price,
        public ?int $categoryId,
        public ?string $shortDescription,
        public ?string $description,
        public ?string $advantages,
        public ?UploadedFile $image,
        public ?string $existingImagePath,
    ) {}

    public static function fromValidated(array $validated, ?string $existingImagePath): self
    {
        return new self(
            name: $validated['name'],
            price: isset($validated['price']) ? (string) $validated['price'] : null,
            categoryId: isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            shortDescription: $validated['short_description'] ?? null,
            description: $validated['description'] ?? null,
            advantages: $validated['advantages'] ?? null,
            image: $validated['image'] ?? null,
            existingImagePath: $existingImagePath,
        );
    }

    public function advantagesList(): array
    {
        if ($this->advantages === null) {
            return [];
        }

        return array_values(array_filter(
            array_map('trim', explode(',', $this->advantages)),
            static fn (string $item): bool => $item !== '',
        ));
    }

    public function hasImage(): bool
    {
        return $this->image !== null
            || ($this->existingImagePath !== null && $this->existingImagePath !== '');
    }
}
