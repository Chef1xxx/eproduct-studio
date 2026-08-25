<?php

namespace App\Http\Controllers\Web;

use App\Domain\Products\Services\ProductCatalogService;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalog,
    ) {}

    public function index(): Response
    {
        return Inertia::render('HomePage', [
            'products' => $this->catalog->listProducts(),
        ]);
    }

    public function show(Product $product): Response
    {
        return Inertia::render('ProductShowPage', [
            'product' => $this->catalog->getProduct($product),
        ]);
    }
}