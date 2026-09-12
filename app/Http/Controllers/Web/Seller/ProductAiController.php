<?php

namespace App\Http\Controllers\Web\Seller;

use App\Domain\AI\Exceptions\AiProviderException;
use App\Domain\AI\Services\ProductAiService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\ProductGenerationRequest;
use Illuminate\Http\JsonResponse;

class ProductAiController extends Controller
{
    public function __construct(
        private readonly ProductAiService $productAi,
    ) {}

    public function generate(ProductGenerationRequest $request): JsonResponse
    {
        try {
            $result = $this->productAi->generate($request->toGenerationData());
        } catch (AiProviderException $exception) {
            report($exception);

            return response()->json(['message' => 'Не удалось выполнить генерацию'], 502);
        }

        return response()->json($result->toArray());
    }
}
