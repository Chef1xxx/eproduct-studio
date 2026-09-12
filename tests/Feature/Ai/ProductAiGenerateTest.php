<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    Storage::fake('public');
    useGigaChatTestConfig();
});

it('fills missing fields for the owner without saving a product', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Электроника']);
    createGigaChatCredential();

    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/files' => Http::response(['id' => 'uploaded-file-id', 'object' => 'file']),
        'api.giga.chat/v1/chat/completions' => Http::response(gigaChatChatBody(json_encode([
            'short_description' => 'Кратко от AI',
            'description' => 'Полное описание от AI',
            'advantages' => ['Быстрый', 'Тихий'],
            'category_id' => $category->id,
        ], JSON_UNESCAPED_UNICODE))),
    ]);

    $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), [
            'name' => 'Беспроводные наушники',
            'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        ])
        ->assertOk()
        ->assertJson([
            'short_description' => 'Кратко от AI',
            'description' => 'Полное описание от AI',
            'advantages' => ['Быстрый', 'Тихий'],
            'category_id' => $category->id,
            'generated_image' => null,
        ]);

    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/files') && $request->isMultipart());
    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/chat/completions')
        && $request['messages'][1]['attachments'] === ['uploaded-file-id']);

    expect(Product::query()->count())->toBe(0);
});

it('generates an image when the product has none', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    createGigaChatCredential();

    $imageBytes = UploadedFile::fake()->image('generated.jpg', 10, 10)->getContent();

    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/chat/completions' => Http::sequence()
            ->push(gigaChatChatBody(json_encode(['short_description' => 'Кратко от AI'], JSON_UNESCAPED_UNICODE)))
            ->push(gigaChatChatBody('<img src="a598ae3d-d3eb-454e-a8bf-0848193a603e" fuse="true"/>')),
        'api.giga.chat/v1/files/*/content' => Http::response($imageBytes, 200, ['Content-Type' => 'image/jpeg']),
    ]);

    $this->actingAs($user)
        ->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), [
            'name' => 'Настольная лампа',
            'category_id' => $category->id,
            'description' => 'Уже заполненное описание',
            'advantages' => 'Яркая, Тёплый свет',
        ])
        ->assertOk()
        ->assertJsonPath('short_description', 'Кратко от AI')
        ->assertJsonPath('description', null)
        ->assertJsonPath('generated_image.mime_type', 'image/jpeg')
        ->assertJsonPath('generated_image.base64', base64_encode($imageBytes));

    Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/chat/completions')
        && ($request['function_call'] ?? null) === 'auto');

    Http::assertSentCount(4);
});

it('forbids sending another users product to AI', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    createGigaChatCredential();

    $product = Product::factory()->create([
        'user_id' => $owner->id,
        'image_path' => 'products/secret.webp',
    ]);

    Http::fake();

    $this->actingAs($stranger)
        ->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), [
            'product_id' => $product->id,
            'name' => 'Чужой товар',
        ])
        ->assertForbidden();

    Http::assertNothingSent();
});

it('blocks guests from AI generation', function () {
    $this->withHeader('Accept', 'application/json')
        ->post(route('seller.products.ai.generate'), ['name' => 'Товар'])
        ->assertUnauthorized();
});
