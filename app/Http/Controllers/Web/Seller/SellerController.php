<?php

namespace App\Http\Controllers\Web\Seller;

use App\Domain\Products\Services\SellerProductService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function __construct(
        private readonly SellerProductService $sellerProducts,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Seller/SellerProductsPage', [
            'products' => $this->sellerProducts->listForUser($user),
        ]);
    }
}