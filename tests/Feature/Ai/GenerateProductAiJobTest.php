<?php

use App\Domain\AI\Enums\AiGenerationStatus;
use App\Domain\AI\Exceptions\AiProviderException;
use App\Domain\AI\Services\AiGenerationService;
use App\Events\ProductGenerationUpdated;
use App\Jobs\GenerateProductAiJob;
use App\Models\AiGeneration;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    Storage::fake('local');
    Event::fake([ProductGenerationUpdated::class]);
    useGigaChatTestConfig();
});

function createPendingGeneration(User $user, array $input = [], ?string $inputImagePath = null): AiGeneration
{
    return $user->aiGenerations()->create([
        'status' => AiGenerationStatus::Pending,
        'input' => [
            'name' => 'Беспроводные наушники',
            'price' => null,
            'category_id' => null,
            'short_description' => null,
            'description' => null,
            'advantages' => null,
            ...$input,
        ],
        'input_image_path' => $inputImagePath,
    ]);
}

it('completes a generation and broadcasts the completed status', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $credential = createGigaChatCredential();

    Storage::disk('local')->put('ai-generations/input.jpg', UploadedFile::fake()->image('photo.jpg', 50, 50)->getContent());

    $generation = createPendingGeneration($user, inputImagePath: 'ai-generations/input.jpg');

    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/files' => Http::response(['id' => 'uploaded-file-id']),
        'api.giga.chat/v1/chat/completions' => Http::response(gigaChatChatBody(json_encode([
            'short_description' => 'Кратко от AI',
            'description' => 'Полное описание от AI',
            'advantages' => ['Быстрые', 'Тихие'],
            'category_id' => $category->id,
        ], JSON_UNESCAPED_UNICODE))),
    ]);

    GenerateProductAiJob::dispatchSync($generation->id);

    $generation->refresh();

    expect($generation->status)->toBe(AiGenerationStatus::Completed)
        ->and($generation->result)->toBe([
            'short_description' => 'Кратко от AI',
            'description' => 'Полное описание от AI',
            'advantages' => ['Быстрые', 'Тихие'],
            'category_id' => $category->id,
        ])
        ->and($generation->started_at)->not->toBeNull()
        ->and($generation->completed_at)->not->toBeNull()
        ->and($generation->ai_provider_id)->toBe($credential->ai_provider_id)
        ->and($generation->ai_provider_credential_id)->toBe($credential->id)
        ->and($generation->generated_image_path)->toBeNull();

    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/files'));

    Event::assertDispatched(
        ProductGenerationUpdated::class,
        fn (ProductGenerationUpdated $event) => $event->generationId === $generation->id
            && $event->userId === $user->id
            && $event->status === AiGenerationStatus::Completed,
    );
});

it('stores a generated image when the form has none', function () {
    $user = User::factory()->create();
    createGigaChatCredential();

    $generation = createPendingGeneration($user, [
        'category_id' => Category::factory()->create()->id,
        'short_description' => 'Кратко',
        'description' => 'Описание',
        'advantages' => 'Яркая',
    ]);

    $imageBytes = UploadedFile::fake()->image('generated.jpg', 10, 10)->getContent();

    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/chat/completions' => Http::response(gigaChatChatBody('<img src="a598ae3d-d3eb-454e-a8bf-0848193a603e" fuse="true"/>')),
        'api.giga.chat/v1/files/*/content' => Http::response($imageBytes, 200, ['Content-Type' => 'image/jpeg']),
    ]);

    GenerateProductAiJob::dispatchSync($generation->id);

    $generation->refresh();

    expect($generation->status)->toBe(AiGenerationStatus::Completed)
        ->and($generation->generated_image_path)->toBe("ai-generations/{$generation->id}/generated.jpg")
        ->and(Storage::disk('local')->get($generation->generated_image_path))->toBe($imageBytes);
});

it('marks the generation failed with a safe error when AI fails', function () {
    $user = User::factory()->create();
    createGigaChatCredential(['authorization_key' => 'super-secret-key']);

    $generation = createPendingGeneration($user);

    Http::fake([
        'ngw.devices.sberbank.ru*' => Http::response(['message' => 'Unauthorized'], 401),
    ]);

    expect(fn () => GenerateProductAiJob::dispatchSync($generation->id))
        ->toThrow(AiProviderException::class);

    $generation->refresh();

    expect($generation->status)->toBe(AiGenerationStatus::Failed)
        ->and($generation->error)->toBe(AiGenerationService::SAFE_ERROR)
        ->and($generation->error)->not->toContain('super-secret-key');

    Event::assertDispatched(
        ProductGenerationUpdated::class,
        fn (ProductGenerationUpdated $event) => $event->status === AiGenerationStatus::Failed,
    );
});
