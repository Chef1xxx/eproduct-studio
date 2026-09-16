<?php

namespace App\Models;

use App\Domain\AI\Enums\AiGenerationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    protected $fillable = [
        'product_id',
        'ai_provider_id',
        'ai_provider_credential_id',
        'status',
        'input',
        'result',
        'input_image_path',
        'generated_image_path',
        'error',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AiGenerationStatus::class,
            'input' => 'array',
            'result' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    public function credential(): BelongsTo
    {
        return $this->belongsTo(AiProviderCredential::class, 'ai_provider_credential_id');
    }
}
