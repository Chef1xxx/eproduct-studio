<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{   
    use HasFactory;
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'price',
        'short_description',
        'description',
        'advantages',
        'image_path',
        'thumbnail_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'advantages' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}