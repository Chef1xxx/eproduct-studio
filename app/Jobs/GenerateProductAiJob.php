<?php

namespace App\Jobs;

use App\Domain\AI\DTO\ResolvedAiProvider;
use App\Domain\AI\Services\AiGenerationService;
use App\Domain\AI\Services\ProductAiService;
use App\Models\AiGeneration;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateProductAiJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 300;

    public bool $failOnTimeout = true;

    public function __construct(
        public int $generationId,
    ) {
        $this->onQueue('integrations');
    }

    public function handle(AiGenerationService $generations, ProductAiService $productAi): void
    {
        $generation = AiGeneration::query()->findOrFail($this->generationId);

        $generations->markProcessing($generation);

        $outcome = $productAi->generate(
            $generations->generationData($generation),
            $generations->inputImage($generation),
            fn (ResolvedAiProvider $provider) => $generations->attachProvider($generation, $provider),
        );

        $generations->complete($generation, $outcome);
    }

    public function failed(?Throwable $exception): void
    {
        $generation = AiGeneration::query()->find($this->generationId);

        if ($generation !== null) {
            app(AiGenerationService::class)->fail($generation);
        }
    }
}
