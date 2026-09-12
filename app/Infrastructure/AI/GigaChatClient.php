<?php

namespace App\Infrastructure\AI;

use App\Domain\AI\Contracts\AiProviderClientInterface;
use App\Domain\AI\DTO\GeneratedImage;
use App\Domain\AI\DTO\ProductGenerationInput;
use App\Domain\AI\DTO\ProductGenerationResult;
use App\Domain\AI\DTO\ProductImageGenerationInput;
use App\Domain\AI\DTO\ResolvedAiProvider;
use App\Domain\AI\Exceptions\AiProviderException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class GigaChatClient implements AiProviderClientInterface
{
    private const SYSTEM_PROMPT = <<<'TEXT'
        Ты заполняешь карточку товара интернет-магазина.
        Используй название, уже заполненные поля и изображение, если оно приложено.
        Не изменяй заполненные поля и не выдумывай характеристики, которых нет в данных.
        Категорию выбирай только из списка разрешённых.
        Пиши по-русски. Верни данные строго по JSON Schema.
        TEXT;

    private ?array $accessToken = null;

    public function identifier(): string
    {
        return 'gigachat';
    }

    public function generateProductData(ProductGenerationInput $input, ResolvedAiProvider $provider): ProductGenerationResult
    {
        $token = $this->accessToken($provider);

        $message = [
            'role' => 'user',
            'content' => $this->productPrompt($input),
        ];

        if ($input->imageJpeg !== null) {
            $message['attachments'] = [$this->uploadImage($token, $input->imageJpeg)];
        }

        $response = $this->send('chat completions', fn () => $this->api($token)->post('/chat/completions', [
            'model' => $provider->model,
            'messages' => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                $message,
            ],
            'response_format' => [
                'type' => 'json_schema',
                'schema' => $this->productSchema($input),
                'strict' => true,
            ],
        ]));

        $decoded = json_decode($this->messageContent($response), true);

        if (! is_array($decoded)) {
            throw AiProviderException::invalidResponse('ответ не является JSON-объектом');
        }

        return ProductGenerationResult::fromArray($decoded, $input->allowedCategoryIds());
    }

    public function generateImage(ProductImageGenerationInput $input, ResolvedAiProvider $provider): GeneratedImage
    {
        $token = $this->accessToken($provider);

        $prompt = "Создай квадратное рекламное изображение товара: {$input->name}";

        if ($input->description !== null) {
            $prompt .= "\n\nОписание:\n{$input->description}";
        }

        $prompt .= "\n\nОдин товар, нейтральный фон, без текста и логотипов.";

        $response = $this->send('image generation', fn () => $this->api($token, $this->imageTimeout())->post('/chat/completions', [
            'model' => $provider->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'function_call' => 'auto',
        ]));

        $fileId = $this->extractImageId($this->messageContent($response));

        $file = $this->send('image download', fn () => $this->http($this->imageTimeout())
            ->withToken($token)
            ->accept('application/jpg')
            ->get("/files/{$fileId}/content"));

        return new GeneratedImage(
            mimeType: $this->imageMimeType($file),
            bytes: $file->body(),
        );
    }

    private function accessToken(ResolvedAiProvider $provider): string
    {
        if ($this->accessToken !== null
            && $this->accessToken['credential_id'] === $provider->credentialId
            && $this->accessToken['expires_at'] > now()->addMinute()->getTimestampMs()) {
            return $this->accessToken['value'];
        }

        $response = $this->send('oauth', fn () => Http::asForm()
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Basic '.$provider->authorizationKey,
                'RqUID' => (string) Str::uuid(),
            ])
            ->connectTimeout($this->connectTimeout())
            ->timeout($this->timeout())
            ->post(config('services.gigachat.oauth_url'), [
                'scope' => $provider->scope,
            ]));

        $value = $response->json('access_token');
        $expiresAt = $response->json('expires_at');

        if (! is_string($value) || $value === '') {
            throw AiProviderException::invalidResponse('OAuth не вернул access_token');
        }

        $this->accessToken = [
            'credential_id' => $provider->credentialId,
            'value' => $value,
            'expires_at' => is_int($expiresAt) ? $expiresAt : now()->addMinutes(25)->getTimestampMs(),
        ];

        return $value;
    }

    private function uploadImage(string $token, string $jpeg): string
    {
        $response = $this->send('file upload', fn () => $this->api($token)
            ->attach('file', $jpeg, 'product.jpg', ['Content-Type' => 'image/jpeg'])
            ->post('/files', ['purpose' => 'general']));

        $id = $response->json('id');

        if (! is_string($id) || $id === '') {
            throw AiProviderException::invalidResponse('загрузка файла не вернула id');
        }

        return $id;
    }

    private function productPrompt(ProductGenerationInput $input): string
    {
        return implode("\n", [
            'Название: '.$input->name,
            'Цена: '.($input->price ?? 'не указана'),
            'Краткое описание: '.($input->shortDescription ?: 'не заполнено'),
            'Описание: '.($input->description ?: 'не заполнено'),
            'Преимущества: '.($input->advantages === [] ? 'не заполнены' : implode(', ', $input->advantages)),
            'Категория (id): '.($input->categoryId ?? 'не выбрана'),
            'Разрешённые категории: '.json_encode($input->categories, JSON_UNESCAPED_UNICODE),
            'Заполни только поля: '.implode(', ', $input->missingFields),
        ]);
    }

    private function productSchema(ProductGenerationInput $input): array
    {
        $categoryId = [
            'type' => 'integer',
            'description' => 'id категории из списка разрешённых',
        ];

        if ($input->allowedCategoryIds() !== []) {
            $categoryId['enum'] = $input->allowedCategoryIds();
        }

        $properties = array_intersect_key([
            'short_description' => [
                'type' => 'string',
                'description' => 'Краткое описание товара, одно предложение до 200 символов',
            ],
            'description' => [
                'type' => 'string',
                'description' => 'Полное описание товара, 2–4 предложения',
            ],
            'advantages' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => '3–5 коротких преимуществ товара',
            ],
            'category_id' => $categoryId,
        ], array_flip($input->missingFields));

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => array_keys($properties),
        ];
    }

    private function messageContent(Response $response): string
    {
        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw AiProviderException::invalidResponse('пустой message.content');
        }

        return $content;
    }

    private function extractImageId(string $content): string
    {
        if (preg_match('/<img[^>]+src="([a-zA-Z0-9-]+)"/i', $content, $matches) !== 1) {
            throw AiProviderException::invalidResponse('модель не вернула изображение');
        }

        return $matches[1];
    }

    private function imageMimeType(Response $response): string
    {
        $contentType = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

        return match (true) {
            str_contains($contentType, 'jpg'), str_contains($contentType, 'jpeg') => 'image/jpeg',
            str_contains($contentType, 'png') => 'image/png',
            str_starts_with($contentType, 'image/') => $contentType,
            default => throw AiProviderException::invalidResponse("неожиданный Content-Type изображения [{$contentType}]"),
        };
    }

    private function api(string $token, ?int $timeout = null): PendingRequest
    {
        return $this->http($timeout ?? $this->timeout())
            ->withToken($token)
            ->acceptJson();
    }

    private function http(int $timeout): PendingRequest
    {
        return Http::baseUrl(config('services.gigachat.base_url'))
            ->connectTimeout($this->connectTimeout())
            ->timeout($timeout);
    }

    private function send(string $operation, callable $request): Response
    {
        try {
            return $request()->throw();
        } catch (RequestException $exception) {
            throw AiProviderException::requestFailed($operation, $exception->response->status());
        } catch (ConnectionException $exception) {
            throw AiProviderException::connectionFailed($operation, $exception);
        }
    }

    private function connectTimeout(): int
    {
        return (int) config('services.gigachat.connect_timeout', 5);
    }

    private function timeout(): int
    {
        return (int) config('services.gigachat.timeout', 60);
    }

    private function imageTimeout(): int
    {
        return (int) config('services.gigachat.image_timeout', 120);
    }
}
