<?php

namespace App\DTO;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ProductGenerationResultDto extends Data
{
    public function __construct(
        public readonly ?string $short_description,
        public readonly ?string $description,
        public readonly ?array $advantages,
        public readonly ?int $category_id,
        public readonly ?GeneratedImageDto $generated_image,
    ) {}

    public static function nothingGenerated(): self
    {
        return new self(
            short_description: null,
            description: null,
            advantages: null,
            category_id: null,
            generated_image: null,
        );
    }
}
