<?php

namespace App\Domain\Products\Services;

use App\DTO\ProductDto;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

final class SellerProductService
{
    /**
     * @return Collection<int, ProductDto>
     */
    public function listForUser(User $user): Collection
    {
        return $user->products()
            ->with('category')
            ->latest()
            ->get()
            ->map(fn (Product $product) => ProductDto::fromModel($product));
    }
}