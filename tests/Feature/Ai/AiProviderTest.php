<?php

use App\Domain\AI\Exceptions\AiProviderException;
use App\Domain\AI\Services\AiProviderRegistry;
use App\Domain\AI\Services\AiProviderResolver;
use App\Infrastructure\AI\GigaChatClient;
use App\Models\AiProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('stores the authorization key encrypted', function () {
    $credential = createGigaChatCredential(['authorization_key' => 'plain-secret-key']);

    $raw = DB::table('ai_provider_credentials')
        ->where('id', $credential->id)
        ->value('authorization_key');

    expect($raw)->not->toContain('plain-secret-key')
        ->and($credential->fresh()->authorization_key)->toBe('plain-secret-key')
        ->and($credential->toArray())->not->toHaveKey('authorization_key');
});

it('resolves the least recently used active credential and touches last_used_at', function () {
    $this->freezeSecond();

    $usedYesterday = createGigaChatCredential(['name' => 'A', 'last_used_at' => now()->subDay()]);
    createGigaChatCredential(['name' => 'B', 'last_used_at' => now()->subHour()]);
    createGigaChatCredential(['name' => 'C', 'is_active' => false]);

    $resolved = app(AiProviderResolver::class)->resolve();

    expect($resolved->credentialId)->toBe($usedYesterday->id)
        ->and($resolved->driver)->toBe('gigachat')
        ->and($resolved->scope)->toBe('GIGACHAT_API_PERS')
        ->and($resolved->authorizationKey)->toBe('test-authorization-key')
        ->and($resolved->providerId)->toBe($usedYesterday->ai_provider_id)
        ->and($usedYesterday->fresh()->last_used_at->equalTo(now()))->toBeTrue();
});

it('prefers a credential that has never been used', function () {
    createGigaChatCredential(['name' => 'Старый', 'last_used_at' => now()->subDay()]);
    $neverUsed = createGigaChatCredential(['name' => 'Новый']);

    expect(app(AiProviderResolver::class)->resolve()->credentialId)->toBe($neverUsed->id);
});

it('fails when there is no active provider', function () {
    createGigaChatCredential();
    AiProvider::query()->update(['is_active' => false]);

    expect(fn () => app(AiProviderResolver::class)->resolve())
        ->toThrow(AiProviderException::class);
});

it('maps driver to its client implementation', function () {
    $registry = app(AiProviderRegistry::class);

    expect($registry->get('gigachat'))->toBeInstanceOf(GigaChatClient::class);
    expect(fn () => $registry->get('unknown'))->toThrow(AiProviderException::class);
});
