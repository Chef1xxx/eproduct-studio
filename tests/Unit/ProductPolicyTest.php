<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
);

it('allows the owner to update and delete a product', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $policy = new ProductPolicy();

    expect($policy->update($user, $product))->toBeTrue()
        ->and($policy->delete($user, $product))->toBeTrue();
});

it('denies another user from updating or deleting a product', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
    ]);

    $policy = new ProductPolicy();

    expect($policy->update($stranger, $product))->toBeFalse()
        ->and($policy->delete($stranger, $product))->toBeFalse();
});