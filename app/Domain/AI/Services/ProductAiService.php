<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\DTO\ProductAiOutcome;
use App\Domain\AI\DTO\ProductGenerationData;
use App\Domain\AI\DTO\ProductGenerationInput;
use App\Domain\AI\DTO\ProductImageGenerationInput;
use App\Models\Category;
use Closure;

final class ProductAiService
{
    public function __construct(
        private readonly AiProviderResolver $resolver,
        private readonly AiProviderRegistry $registry,
    ) {}

    public function generate(ProductGenerationData $data, ?string $imageJpeg, ?Closure $onProviderResolved = null): ProductAiOutcome
    {
        $missingFields = $this->missingFields($data);
        $needsImage = $imageJpeg === null;

        if ($missingFields === [] && ! $needsImage) {
            return ProductAiOutcome::nothingGenerated();
        }

        $provider = $this->resolver->resolve();

        if ($onProviderResolved !== null) {
            $onProviderResolved($provider);
        }

        $client = $this->registry->get($provider->driver);

        $result = $missingFields === []
            ? null
            : $client->generateProductData($this->buildInput($data, $missingFields, $imageJpeg), $provider);

        $shortDescription = in_array('short_description', $missingFields, true) ? $result?->shortDescription : null;
        $description = in_array('description', $missingFields, true) ? $result?->description : null;
        $advantages = in_array('advantages', $missingFields, true) ? $result?->advantages : null;
        $categoryId = in_array('category_id', $missingFields, true) ? $result?->categoryId : null;

        $generatedImage = $needsImage
            ? $client->generateImage(new ProductImageGenerationInput(
                name: $data->name,
                description: $this->firstFilled($data->shortDescription, $shortDescription, $data->description, $description),
            ), $provider)
            : null;

        return new ProductAiOutcome(
            shortDescription: $shortDescription,
            description: $description,
            advantages: $advantages,
            categoryId: $categoryId,
            generatedImage: $generatedImage,
        );
    }

    private function missingFields(ProductGenerationData $data): array
    {
        return array_values(array_filter([
            $this->isBlank($data->shortDescription) ? 'short_description' : null,
            $this->isBlank($data->description) ? 'description' : null,
            $data->advantagesList() === [] ? 'advantages' : null,
            $data->categoryId === null ? 'category_id' : null,
        ]));
    }

    private function buildInput(ProductGenerationData $data, array $missingFields, ?string $imageJpeg): ProductGenerationInput
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category) => ['id' => $category->id, 'name' => $category->name])
            ->values()
            ->all();

        return new ProductGenerationInput(
            name: $data->name,
            price: $data->price,
            shortDescription: $data->shortDescription,
            description: $data->description,
            advantages: $data->advantagesList(),
            categoryId: $data->categoryId,
            categories: $categories,
            missingFields: $missingFields,
            imageJpeg: $imageJpeg,
        );
    }

    private function firstFilled(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if (! $this->isBlank($value)) {
                return trim($value);
            }
        }

        return null;
    }

    private function isBlank(?string $value): bool
    {
        return $value === null || trim($value) === '';
    }
}
