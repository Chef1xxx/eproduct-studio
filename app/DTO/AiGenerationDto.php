<?php

namespace App\DTO;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class AiGenerationDto extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $status,
        public readonly ?ProductGenerationResultDto $result,
        public readonly ?string $error,
    ) {}
}
