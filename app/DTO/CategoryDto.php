<?php

namespace App\DTO;

use App\Models\Category;
use Spatie\LaravelData\Data;

final class CategoryDto extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
        );
    }
}