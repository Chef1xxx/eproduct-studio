<?php

use App\Domain\AI\Enums\AiGenerationStatus;
use App\Domain\AI\Services\AiGenerationService;
use App\Jobs\GenerateProductAiJob;
use App\Models\AiGeneration;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    Queue::fake();
    Storage::fake('local');
    Storage::fake('public');
});

it('accepts a generation and queues the job without calling AI', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), [
            'name' => 'Беспроводные наушники',
            'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        ])
        ->assertStatus(202)
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('result', null);

    $generation = AiGeneration::query()->sole();

    expect($response->json('id'))->toBe($generation->id);

    Queue::assertPushedOn(
        'integrations',
        GenerateProductAiJob::class,
        fn (GenerateProductAiJob $job) => $job->generationId === $generation->id,
    );
});

it('stores a snapshot of the form and sets the owner on the server', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), [
            'user_id' => 999,
            'name' => 'Настольная лампа',
            'price' => 1990,
            'category_id' => $category->id,
            'short_description' => 'Кратко',
            'advantages' => 'Яркая, Тёплый свет',
            'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        ])
        ->assertStatus(202);

    $generation = AiGeneration::query()->sole();

    expect($generation->user_id)->toBe($user->id)
        ->and($generation->status)->toBe(AiGenerationStatus::Pending)
        ->and($generation->input)->toBe([
            'name' => 'Настольная лампа',
            'price' => '1990',
            'category_id' => $category->id,
            'short_description' => 'Кратко',
            'description' => null,
            'advantages' => 'Яркая, Тёплый свет',
        ])
        ->and($generation->input_image_path)->toBe("ai-generations/{$generation->id}/input.jpg");

    Storage::disk('local')->assertExists($generation->input_image_path);
});

it('forbids starting a generation for another users product', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $product = Product::factory()->create([
        'user_id' => $owner->id,
        'image_path' => 'products/secret.webp',
    ]);

    $this->actingAs($stranger)
        ->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), [
            'product_id' => $product->id,
            'name' => 'Чужой товар',
        ])
        ->assertForbidden();

    Queue::assertNothingPushed();

    expect(AiGeneration::query()->count())->toBe(0);
});

it('blocks guests from AI generation', function () {
    $this->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), ['name' => 'Товар'])
        ->assertUnauthorized();
});

it('returns a generation result only to its owner', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();

    $generation = $owner->aiGenerations()->create([
        'status' => AiGenerationStatus::Completed,
        'input' => ['name' => 'Лампа'],
        'result' => [
            'short_description' => 'Кратко от AI',
            'description' => null,
            'advantages' => ['Яркая'],
            'category_id' => null,
        ],
        'generated_image_path' => 'ai-generations/1/generated.jpg',
    ]);

    Storage::disk('local')->put($generation->generated_image_path, 'image-bytes');

    $this->actingAs($stranger)
        ->getJson(route('seller.ai-generations.show', $generation))
        ->assertForbidden();

    $this->actingAs($owner)
        ->getJson(route('seller.ai-generations.show', $generation))
        ->assertOk()
        ->assertJsonPath('status', 'completed')
        ->assertJsonPath('result.short_description', 'Кратко от AI')
        ->assertJsonPath('result.advantages', ['Яркая'])
        ->assertJsonPath('result.generated_image.mime_type', 'image/jpeg')
        ->assertJsonPath('result.generated_image.base64', base64_encode('image-bytes'))
        ->assertJsonPath('error', null);
});

it('returns a safe error for a failed generation', function () {
    $user = User::factory()->create();

    $generation = $user->aiGenerations()->create([
        'status' => AiGenerationStatus::Failed,
        'input' => ['name' => 'Лампа'],
        'error' => AiGenerationService::SAFE_ERROR,
    ]);

    $this->actingAs($user)
        ->getJson(route('seller.ai-generations.show', $generation))
        ->assertOk()
        ->assertJson([
            'status' => 'failed',
            'result' => null,
            'error' => AiGenerationService::SAFE_ERROR,
        ]);
});
