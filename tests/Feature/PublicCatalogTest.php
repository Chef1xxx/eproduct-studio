<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('renders the public catalog page', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    Product::factory()
        ->count(2)
        ->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('HomePage')
            ->has('products', 2)
            ->has('products.0', fn (Assert $product) => $product
                ->has('id')
                ->has('name')
                ->has('price')
                ->has('category')
                ->etc()
            )
        );
});

it('renders a product page', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'name' => 'Электроника',
        'slug' => 'electronics',
    ]);

    $product = Product::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'name' => 'Тестовый товар',
        'price' => '1999.00',
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('ProductShowPage')
            ->where('product.name', 'Тестовый товар')
            ->where('product.price', '1999.00')
            ->where('product.category.name', 'Электроника')
        );
});

it('returns 404 for a missing product', function () {
    $this->get(route('products.show', 99999))
        ->assertNotFound();
});