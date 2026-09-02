<?php

namespace App\Http\Controllers\Web\Seller;

use App\Domain\Products\Services\ProductManagementService;
use App\DTO\CategoryDto;
use App\DTO\ProductDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreProductRequest;
use App\Http\Requests\Seller\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerProductController extends Controller
{
    public function __construct(
        private readonly ProductManagementService $products,
    ) {}

    public function create(): Response
    {
        return Inertia::render('Seller/ProductFormPage', [
            'product' => null,
            'categories' => $this->categories(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->products->create($user, $request->toProductData());

        return redirect()
            ->route('seller.index')
            ->with('success', 'Товар создан');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        $product->load('category');

        return Inertia::render('Seller/ProductFormPage', [
            'product' => ProductDto::fromModel($product),
            'categories' => $this->categories(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $request->toProductData());

        return redirect()
            ->route('seller.index')
            ->with('success', 'Товар обновлён');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->products->delete($product);

        return redirect()
            ->route('seller.index')
            ->with('success', 'Товар удалён');
    }

    /**
     * @return list<CategoryDto>
     */
    private function categories(): array
    {
        return Category::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => CategoryDto::fromModel($category))
            ->all();
    }
}