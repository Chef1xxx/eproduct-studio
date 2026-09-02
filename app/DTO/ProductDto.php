<?php

namespace App\DTO;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
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
        public readonly ?string $image_url,
        public readonly ?string $thumbnail_url,
        public readonly ?CategoryDto $category,
        public readonly ?string $created_at,
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
            image_url: self::publicUrl($product->image_path),
            thumbnail_url: self::publicUrl($product->thumbnail_path),
            category: $product->relationLoaded('category') && $product->category !== null
                ? CategoryDto::fromModel($product->category)
                : null,
            created_at: $product->created_at?->toDateTimeString() ?? '',
        );
    }

    private static function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        return asset('storage/'.$path);
    }
}