<?php

use App\Domain\AI\DTO\ProductGenerationInput;
use App\Domain\AI\DTO\ResolvedAiProvider;
use App\Domain\AI\Exceptions\AiProviderException;
use App\Infrastructure\AI\GigaChatClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
    useGigaChatTestConfig();
});

function gigaChatTestProvider(): ResolvedAiProvider
{
    return new ResolvedAiProvider(
        driver: 'gigachat',
        model: 'GigaChat-2-Max',
        scope: 'GIGACHAT_API_PERS',
        authorizationKey: 'test-authorization-key',
        credentialId: 1,
    );
}

function gigaChatTestInput(): ProductGenerationInput
{
    return new ProductGenerationInput(
        name: 'Беспроводные наушники',
        price: '4990',
        shortDescription: null,
        description: null,
        advantages: [],
        categoryId: null,
        categories: [
            ['id' => 1, 'name' => 'Электроника'],
            ['id' => 2, 'name' => 'Дом'],
        ],
        missingFields: ProductGenerationInput::FIELDS,
    );
}

it('requests structured product data with a bearer token', function () {
    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/chat/completions' => Http::response(gigaChatChatBody(json_encode([
            'short_description' => 'Лёгкие наушники с шумоподавлением',
            'description' => 'Беспроводные наушники для города и дороги.',
            'advantages' => ['Лёгкие', ' Тихие '],
            'category_id' => 1,
        ], JSON_UNESCAPED_UNICODE))),
    ]);

    $result = app(GigaChatClient::class)->generateProductData(gigaChatTestInput(), gigaChatTestProvider());

    expect($result->shortDescription)->toBe('Лёгкие наушники с шумоподавлением')
        ->and($result->advantages)->toBe(['Лёгкие', 'Тихие'])
        ->and($result->categoryId)->toBe(1);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/api/v2/oauth')
        && $request->hasHeader('Authorization', 'Basic test-authorization-key')
        && $request->hasHeader('RqUID')
        && $request['scope'] === 'GIGACHAT_API_PERS');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.giga.chat/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer test-access-token')
        && $request['model'] === 'GigaChat-2-Max'
        && $request['response_format']['type'] === 'json_schema'
        && $request['response_format']['strict'] === true
        && $request['response_format']['schema']['required'] === ProductGenerationInput::FIELDS);
});

it('rejects a response that is not JSON', function () {
    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/chat/completions' => Http::response(gigaChatChatBody('Извините, я не могу помочь')),
    ]);

    expect(fn () => app(GigaChatClient::class)->generateProductData(gigaChatTestInput(), gigaChatTestProvider()))
        ->toThrow(AiProviderException::class);
});

it('rejects a category outside the allowed list', function () {
    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/chat/completions' => Http::response(gigaChatChatBody(json_encode([
            'short_description' => 'Кратко',
            'description' => 'Описание',
            'advantages' => ['Плюс'],
            'category_id' => 999,
        ]))),
    ]);

    expect(fn () => app(GigaChatClient::class)->generateProductData(gigaChatTestInput(), gigaChatTestProvider()))
        ->toThrow(AiProviderException::class);
});

it('does not treat HTTP errors as a successful response', function () {
    Http::fake([
        'ngw.devices.sberbank.ru*' => gigaChatOAuthResponse(),
        'api.giga.chat/v1/chat/completions' => Http::response(['message' => 'Internal error'], 500),
    ]);

    expect(fn () => app(GigaChatClient::class)->generateProductData(gigaChatTestInput(), gigaChatTestProvider()))
        ->toThrow(AiProviderException::class);
});
