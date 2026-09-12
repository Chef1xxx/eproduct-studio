<?php

namespace App\Domain\AI\DTO;

use App\Domain\AI\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final readonly class ProductGenerationResult
{
    public function __construct(
        public ?string $shortDescription,
        public ?string $description,
        public ?array $advantages,
        public ?int $categoryId,
    ) {}

    public static function fromArray(array $data, array $allowedCategoryIds): self
    {
        $validator = Validator::make($data, [
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'advantages' => ['nullable', 'array', 'max:10'],
            'advantages.*' => ['string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::in($allowedCategoryIds)],
        ]);

        if ($validator->fails()) {
            throw AiProviderException::invalidResponse('некорректные поля '.implode(', ', $validator->errors()->keys()));
        }

        $advantages = isset($data['advantages'])
            ? array_values(array_filter(
                array_map('trim', $data['advantages']),
                static fn (string $item): bool => $item !== '',
            ))
            : [];

        return new self(
            shortDescription: self::nullableString($data['short_description'] ?? null),
            description: self::nullableString($data['description'] ?? null),
            advantages: $advantages === [] ? null : $advantages,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
        );
    }

    private static function nullableString(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
