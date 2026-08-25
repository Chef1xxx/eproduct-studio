<?php

namespace App\Domain\Products\Services;

use App\DTO\ProductDto;
use App\Models\Product;
use Illuminate\Support\Collection;

final class ProductCatalogService
{
    /**
     * @return Collection<int, ProductDto>
     */
    public function listProducts(): Collection
    {
        return Product::query()
            ->with('category')
            ->latest()
            ->get()
            ->map(fn (Product $product) => ProductDto::fromModel($product));
    }

    public function getProduct(Product $product): ProductDto
    {
        $product->loadMissing('category');

        return ProductDto::fromModel($product);
    }
}