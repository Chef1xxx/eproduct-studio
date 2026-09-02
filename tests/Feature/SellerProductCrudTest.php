<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('creates a product for the authenticated user without trusting client user_id', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($user)
        ->post(route('seller.products.store'), [
            'name' => 'Новый товар',
            'price' => 1999.5,
            'category_id' => $category->id,
            'short_description' => 'Кратко',
            'description' => 'Полное описание',
            'advantages' => 'Быстрый, лёгкий, тихий',
            'user_id' => 999,
            'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
        ])
        ->assertRedirect(route('seller.index'));

    $product = Product::query()->first();

    expect($product)->not->toBeNull()
        ->and($product->user_id)->toBe($user->id)
        ->and($product->name)->toBe('Новый товар')
        ->and($product->advantages)->toBe(['Быстрый', 'лёгкий', 'тихий'])
        ->and($product->image_path)->not->toBeNull()
        ->and($product->thumbnail_path)->not->toBeNull()
        ->and(str_ends_with($product->image_path, '.webp'))->toBeTrue()
        ->and(str_ends_with($product->thumbnail_path, '.webp'))->toBeTrue();

    Storage::disk('public')->assertExists($product->image_path);
    Storage::disk('public')->assertExists($product->thumbnail_path);
});

it('blocks guests from seller product routes', function () {
    $product = Product::factory()->create();

    $this->get(route('seller.products.create'))->assertRedirect(route('login'));
    $this->post(route('seller.products.store'), [])->assertRedirect(route('login'));
    $this->get(route('seller.products.edit', $product))->assertRedirect(route('login'));
    $this->put(route('seller.products.update', $product), [])->assertRedirect(route('login'));
    $this->delete(route('seller.products.destroy', $product))->assertRedirect(route('login'));
});

it('forbids updating and deleting another users product', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $category = Category::factory()->create();

    $product = Product::factory()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
    ]);

    $this->actingAs($stranger)
        ->put(route('seller.products.update', $product), [
            'name' => 'Хак',
            'price' => 100,
            'category_id' => $category->id,
            'short_description' => null,
            'description' => null,
            'advantages' => null,
        ])
        ->assertForbidden();

    $this->actingAs($stranger)
        ->delete(route('seller.products.destroy', $product))
        ->assertForbidden();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => $product->name,
    ]);
});

it('updates product fields for the owner', function () {
    $user = User::factory()->create();
    $oldCategory = Category::factory()->create();
    $newCategory = Category::factory()->create();

    $product = Product::factory()->create([
        'user_id' => $user->id,
        'category_id' => $oldCategory->id,
        'name' => 'Старое имя',
        'price' => 100,
        'advantages' => ['Старое'],
    ]);

    $this->actingAs($user)
        ->put(route('seller.products.update', $product), [
            'name' => 'Новое имя',
            'price' => 250.75,
            'category_id' => $newCategory->id,
            'short_description' => 'Обновлено',
            'description' => 'Новое описание',
            'advantages' => 'А, Б, В',
        ])
        ->assertRedirect(route('seller.index'));

    $product->refresh();

    expect($product->name)->toBe('Новое имя')
        ->and((string) $product->price)->toBe('250.75')
        ->and($product->category_id)->toBe($newCategory->id)
        ->and($product->advantages)->toBe(['А', 'Б', 'В']);
});

it('deletes owned product and its images', function () {
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'user_id' => $user->id,
        'image_path' => 'products/old.webp',
        'thumbnail_path' => 'products/old-thumb.webp',
    ]);

    Storage::disk('public')->put($product->image_path, 'fake');
    Storage::disk('public')->put($product->thumbnail_path, 'fake');

    $this->actingAs($user)
        ->delete(route('seller.products.destroy', $product))
        ->assertRedirect(route('seller.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    Storage::disk('public')->assertMissing('products/old.webp');
    Storage::disk('public')->assertMissing('products/old-thumb.webp');
});

it('replaces old images when a new image is uploaded', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();

    $product = Product::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'image_path' => 'products/old.webp',
        'thumbnail_path' => 'products/old-thumb.webp',
    ]);

    Storage::disk('public')->put($product->image_path, 'fake-old');
    Storage::disk('public')->put($product->thumbnail_path, 'fake-old-thumb');

    $this->actingAs($user)
        ->post(route('seller.products.update', $product), [
            '_method' => 'PUT',
            'name' => $product->name,
            'price' => $product->price,
            'category_id' => $category->id,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'advantages' => 'Один, Два',
            'image' => UploadedFile::fake()->image('new.png', 640, 480),
        ])
        ->assertRedirect(route('seller.index'));

    $product->refresh();

    expect($product->image_path)->not->toBe('products/old.webp')
        ->and($product->thumbnail_path)->not->toBe('products/old-thumb.webp');

    Storage::disk('public')->assertMissing('products/old.webp');
    Storage::disk('public')->assertMissing('products/old-thumb.webp');
    Storage::disk('public')->assertExists($product->image_path);
    Storage::disk('public')->assertExists($product->thumbnail_path);
});

it('rejects invalid product payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('seller.products.create'))
        ->post(route('seller.products.store'), [
            'name' => '',
            'price' => -10,
            'category_id' => 999999,
            'image' => UploadedFile::fake()->create('notes.txt', 100, 'text/plain'),
        ])
        ->assertRedirect(route('seller.products.create'))
        ->assertSessionHasErrors(['name', 'price', 'category_id', 'image']);

    expect(Product::query()->count())->toBe(0);
});