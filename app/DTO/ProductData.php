<?php

namespace App\DTO;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

final class ProductData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string|float|int $price,
        public readonly int $category_id,
        public readonly ?string $short_description,
        public readonly ?string $description,
        public readonly ?string $advantages,
        public readonly ?UploadedFile $image = null,
    ) {}
}