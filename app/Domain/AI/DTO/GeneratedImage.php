<?php

namespace App\Domain\AI\DTO;

final readonly class GeneratedImage
{
    public function __construct(
        public string $mimeType,
        public string $bytes,
    ) {}
}
