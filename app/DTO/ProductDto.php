<?php

namespace App\DTO;

use App\Models\Product;
use Spatie\LaravelData\Data;

final class ProductDto extends Data
{
    /**
     * @param  list<string>|null  $advantages
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $price,
        public readonly ?string $short_description,
        public readonly ?string $description,
        public readonly ?array $advantages,
        public readonly ?string $image_path,
        public readonly ?CategoryDto $category,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            price: (string) $product->price,
            short_description: $product->short_description,
            description: $product->description,
            advantages: $product->advantages,
            image_path: $product->image_path,
            category: $product->relationLoaded('category') && $product->category !== null
                ? CategoryDto::fromModel($product->category)
                : null,
        );
    }
}