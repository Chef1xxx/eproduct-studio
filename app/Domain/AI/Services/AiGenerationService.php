<?php

namespace App\Domain\AI\Services;

use App\Domain\AI\DTO\GeneratedImage;
use App\Domain\AI\DTO\ProductAiOutcome;
use App\Domain\AI\DTO\ProductGenerationData;
use App\Domain\AI\DTO\ResolvedAiProvider;
use App\Domain\AI\Enums\AiGenerationStatus;
use App\Domain\Media\Services\ImageService;
use App\DTO\AiGenerationDto;
use App\DTO\GeneratedImageDto;
use App\DTO\ProductGenerationResultDto;
use App\Events\ProductGenerationUpdated;
use App\Models\AiGeneration;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class AiGenerationService
{
    public const SAFE_ERROR = 'Не удалось выполнить генерацию';

    private const DISK = 'local';

    public function __construct(
        private readonly ImageService $images,
    ) {}

    public function create(User $user, ProductGenerationData $data, ?Product $product): AiGeneration
    {
        $generation = $user->aiGenerations()->create([
            'product_id' => $product?->id,
            'status' => AiGenerationStatus::Pending,
            'input' => $data->toSnapshot(),
        ]);

        $jpeg = $this->inputJpeg($data, $product);

        if ($jpeg !== null) {
            $path = $this->directory($generation).'/input.jpg';

            Storage::disk(self::DISK)->put($path, $jpeg);

            $generation->update(['input_image_path' => $path]);
        }

        return $generation;
    }

    public function markProcessing(AiGeneration $generation): void
    {
        $generation->update([
            'status' => AiGenerationStatus::Processing,
            'started_at' => now(),
        ]);
    }

    public function attachProvider(AiGeneration $generation, ResolvedAiProvider $provider): void
    {
        $generation->update([
            'ai_provider_id' => $provider->providerId,
            'ai_provider_credential_id' => $provider->credentialId,
        ]);
    }

    public function complete(AiGeneration $generation, ProductAiOutcome $outcome): void
    {
        $generatedImagePath = $outcome->generatedImage === null
            ? null
            : $this->storeGeneratedImage($generation, $outcome->generatedImage);

        $generation->update([
            'status' => AiGenerationStatus::Completed,
            'result' => [
                'short_description' => $outcome->shortDescription,
                'description' => $outcome->description,
                'advantages' => $outcome->advantages,
                'category_id' => $outcome->categoryId,
            ],
            'generated_image_path' => $generatedImagePath,
            'completed_at' => now(),
        ]);

        $this->broadcast($generation);
    }

    public function fail(AiGeneration $generation): void
    {
        $generation->update([
            'status' => AiGenerationStatus::Failed,
            'error' => self::SAFE_ERROR,
        ]);

        $this->broadcast($generation);
    }

    public function generationData(AiGeneration $generation): ProductGenerationData
    {
        return ProductGenerationData::fromSnapshot($generation->input);
    }

    public function inputImage(AiGeneration $generation): ?string
    {
        return $this->readPrivate($generation->input_image_path);
    }

    public function result(AiGeneration $generation): AiGenerationDto
    {
        return new AiGenerationDto(
            id: $generation->id,
            status: $generation->status->value,
            result: $generation->status === AiGenerationStatus::Completed ? $this->resultDto($generation) : null,
            error: $generation->status === AiGenerationStatus::Failed ? $generation->error : null,
        );
    }

    private function resultDto(AiGeneration $generation): ProductGenerationResultDto
    {
        $result = $generation->result ?? [];
        $bytes = $this->readPrivate($generation->generated_image_path);

        return new ProductGenerationResultDto(
            short_description: $result['short_description'] ?? null,
            description: $result['description'] ?? null,
            advantages: $result['advantages'] ?? null,
            category_id: $result['category_id'] ?? null,
            generated_image: $bytes === null ? null : GeneratedImageDto::fromGeneratedImage(new GeneratedImage(
                mimeType: $this->mimeTypeFor($generation->generated_image_path),
                bytes: $bytes,
            )),
        );
    }

    private function inputJpeg(ProductGenerationData $data, ?Product $product): ?string
    {
        if ($data->image !== null) {
            return $this->images->uploadToJpegBinary($data->image);
        }

        if ($product?->image_path !== null && $product->image_path !== '') {
            return $this->images->storedToJpegBinary($product->image_path);
        }

        return null;
    }

    private function storeGeneratedImage(AiGeneration $generation, GeneratedImage $image): string
    {
        $extension = match ($image->mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        $path = $this->directory($generation).'/generated.'.$extension;

        Storage::disk(self::DISK)->put($path, $image->bytes);

        return $path;
    }

    private function mimeTypeFor(string $path): string
    {
        return match (pathinfo($path, PATHINFO_EXTENSION)) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    private function readPrivate(?string $path): ?string
    {
        if ($path === null || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->get($path);
    }

    private function directory(AiGeneration $generation): string
    {
        return 'ai-generations/'.$generation->id;
    }

    private function broadcast(AiGeneration $generation): void
    {
        rescue(fn () => ProductGenerationUpdated::dispatch($generation->user_id, $generation->id, $generation->status));
    }
}
