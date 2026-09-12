<?php

use App\Models\AiProvider;
use App\Models\AiProviderCredential;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function createGigaChatCredential(array $attributes = []): AiProviderCredential
{
    $provider = AiProvider::query()->firstOrCreate(
        ['driver' => 'gigachat'],
        [
            'name' => 'GigaChat',
            'model' => 'GigaChat-2-Max',
            'scope' => 'GIGACHAT_API_PERS',
            'is_active' => true,
        ],
    );

    return $provider->credentials()->create([
        'name' => 'Тестовый ключ',
        'authorization_key' => 'test-authorization-key',
        'is_active' => true,
        ...$attributes,
    ]);
}

function useGigaChatTestConfig(): void
{
    config([
        'services.gigachat.base_url' => 'https://api.giga.chat/v1',
        'services.gigachat.oauth_url' => 'https://ngw.devices.sberbank.ru:9443/api/v2/oauth',
    ]);
}

function gigaChatOAuthResponse(): PromiseInterface
{
    return Http::response([
        'access_token' => 'test-access-token',
        'expires_at' => now()->addMinutes(30)->getTimestampMs(),
    ]);
}

function gigaChatChatBody(string $content): array
{
    return [
        'choices' => [[
            'index' => 0,
            'finish_reason' => 'stop',
            'message' => [
                'role' => 'assistant',
                'content' => $content,
            ],
        ]],
    ];
}
