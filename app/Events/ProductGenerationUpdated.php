<?php

namespace App\Events;

use App\Domain\AI\Enums\AiGenerationStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class ProductGenerationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly int $userId,
        public readonly int $generationId,
        public readonly AiGenerationStatus $status,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'product-generation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'generation_id' => $this->generationId,
            'status' => $this->status->value,
        ];
    }
}
