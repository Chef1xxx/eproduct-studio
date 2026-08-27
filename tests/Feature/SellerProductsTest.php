<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('redirects guests from seller to login', function () {
    $this->get(route('seller.index'))
        ->assertRedirect(route('login'));
});

it('shows only the current user products on seller page', function () {
    $category = Category::factory()->create();

    $owner = User::factory()->create();
    $other = User::factory()->create();

    $ownProduct = Product::factory()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'name' => 'Мой товар',
    ]);

    Product::factory()->create([
        'user_id' => $other->id,
        'category_id' => $category->id,
        'name' => 'Чужой товар',
    ]);

    $this->actingAs($owner)
        ->get(route('seller.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Seller/SellerProductsPage')
            ->has('products', 1)
            ->where('products.0.id', $ownProduct->id)
            ->where('products.0.name', 'Мой товар')
        );
});