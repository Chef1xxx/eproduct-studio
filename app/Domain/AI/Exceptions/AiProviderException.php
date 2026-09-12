<?php

namespace App\Domain\AI\Exceptions;

use RuntimeException;
use Throwable;

final class AiProviderException extends RuntimeException
{
    public static function noActiveProvider(): self
    {
        return new self('Нет активного AI provider.');
    }

    public static function noActiveCredential(string $provider): self
    {
        return new self("У AI provider [{$provider}] нет активных ключей.");
    }

    public static function unknownDriver(string $driver): self
    {
        return new self("Не найдена реализация AI provider для driver [{$driver}].");
    }

    public static function requestFailed(string $operation, ?int $status = null): self
    {
        $suffix = $status === null ? ' (нет соединения или timeout)' : " (HTTP {$status})";

        return new self("Запрос к AI provider не удался: {$operation}{$suffix}.");
    }

    public static function connectionFailed(string $operation, Throwable $previous): self
    {
        return new self(
            "Не удалось соединиться с AI provider: {$operation}. {$previous->getMessage()}",
            previous: $previous,
        );
    }

    public static function invalidResponse(string $reason): self
    {
        return new self("Некорректный ответ AI provider: {$reason}.");
    }
}
