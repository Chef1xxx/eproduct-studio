<?php

use App\DTO\ProductDto;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

uses(
    Tests\TestCase::class,
    \Illuminate\Foundation\Testing\RefreshDatabase::class,
);

it('maps a product model to ProductDto', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'Одежда',
        'slug' => 'clothing',
    ]);

    $product = Product::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'name' => 'Куртка',
        'price' => '4500.50',
        'short_description' => 'Кратко',
        'advantages' => ['Тёплая', 'Лёгкая'],
        'image_path' => null,
    ]);

    $product->load('category');

    $dto = ProductDto::fromModel($product);

    expect($dto->id)->toBe($product->id)
        ->and($dto->name)->toBe('Куртка')
        ->and($dto->price)->toBe('4500.50')
        ->and($dto->short_description)->toBe('Кратко')
        ->and($dto->advantages)->toBe(['Тёплая', 'Лёгкая'])
        ->and($dto->image_url)->toBeNull()
        ->and($dto->thumbnail_url)->toBeNull()
        ->and($dto->category)->not->toBeNull()
        ->and($dto->category->name)->toBe('Одежда');
});
