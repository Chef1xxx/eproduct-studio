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
        public ?UploadedFile $image = null,
    ) {}

    public static function fromValidated(array $validated): self
    {
        return new self(
            name: $validated['name'],
            price: isset($validated['price']) ? (string) $validated['price'] : null,
            categoryId: isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            shortDescription: $validated['short_description'] ?? null,
            description: $validated['description'] ?? null,
            advantages: $validated['advantages'] ?? null,
            image: $validated['image'] ?? null,
        );
    }

    public static function fromSnapshot(array $input): self
    {
        return self::fromValidated([...$input, 'image' => null]);
    }

    public function toSnapshot(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'category_id' => $this->categoryId,
            'short_description' => $this->shortDescription,
            'description' => $this->description,
            'advantages' => $this->advantages,
        ];
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
}
