<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProviderCredential extends Model
{
    protected $fillable = [
        'name',
        'authorization_key',
        'is_active',
        'last_used_at',
    ];

    protected $hidden = [
        'authorization_key',
    ];

    protected function casts(): array
    {
        return [
            'authorization_key' => 'encrypted',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
