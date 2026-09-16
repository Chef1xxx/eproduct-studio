<?php

namespace App\Http\Controllers\Web\Seller;

use App\Domain\AI\Services\AiGenerationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\ProductGenerationRequest;
use App\Jobs\GenerateProductAiJob;
use App\Models\AiGeneration;
use Illuminate\Http\JsonResponse;

class ProductAiController extends Controller
{
    public function __construct(
        private readonly AiGenerationService $generations,
    ) {}

    public function generate(ProductGenerationRequest $request): JsonResponse
    {
        $generation = $this->generations->create(
            $request->user(),
            $request->toGenerationData(),
            $request->product(),
        );

        GenerateProductAiJob::dispatch($generation->id);

        return response()->json($this->generations->result($generation)->toArray(), 202);
    }

    public function show(AiGeneration $generation): JsonResponse
    {
        $this->authorize('view', $generation);

        return response()->json($this->generations->result($generation)->toArray());
    }
}
