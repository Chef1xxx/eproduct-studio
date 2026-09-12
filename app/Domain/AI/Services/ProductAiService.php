<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\DTO\ProductGenerationData;
use App\Domain\AI\DTO\ProductGenerationInput;
use App\Domain\AI\DTO\ProductImageGenerationInput;
use App\Domain\AI\Exceptions\AiProviderException;
use App\Domain\Media\Services\ImageService;
use App\DTO\GeneratedImageDto;
use App\DTO\ProductGenerationResultDto;
use App\Models\Category;

final class ProductAiService
{
    public function __construct(
        private readonly AiProviderResolver $resolver,
        private readonly AiProviderRegistry $registry,
        private readonly ImageService $images,
    ) {}

    public function generate(ProductGenerationData $data): ProductGenerationResultDto
    {
        $missingFields = $this->missingFields($data);
        $needsImage = ! $data->hasImage();

        if ($missingFields === [] && ! $needsImage) {
            return ProductGenerationResultDto::nothingGenerated();
        }

        $provider = $this->resolver->resolve();
        $client = $this->registry->get($provider->driver);

        $result = $missingFields === []
            ? null
            : $client->generateProductData($this->buildInput($data, $missingFields), $provider);

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

        return new ProductGenerationResultDto(
            short_description: $shortDescription,
            description: $description,
            advantages: $advantages,
            category_id: $categoryId,
            generated_image: $generatedImage === null ? null : GeneratedImageDto::fromGeneratedImage($generatedImage),
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

    private function buildInput(ProductGenerationData $data, array $missingFields): ProductGenerationInput
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
            imageJpeg: $this->imageForVision($data),
        );
    }

    private function imageForVision(ProductGenerationData $data): ?string
    {
        if ($data->image !== null) {
            return $this->images->uploadToJpegBinary($data->image);
        }

        if ($data->existingImagePath !== null && $data->existingImagePath !== '') {
            return $this->images->storedToJpegBinary($data->existingImagePath);
        }

        return null;
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
