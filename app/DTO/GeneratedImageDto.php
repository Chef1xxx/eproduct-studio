<?php

namespace App\DTO;

use App\Domain\AI\DTO\GeneratedImage;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class GeneratedImageDto extends Data
{
    public function __construct(
        public readonly string $mime_type,
        public readonly string $base64,
    ) {}

    public static function fromGeneratedImage(GeneratedImage $image): self
    {
        return new self(
            mime_type: $image->mimeType,
            base64: base64_encode($image->bytes),
        );
    }
}
