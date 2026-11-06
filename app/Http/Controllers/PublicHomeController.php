<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CartService;
use App\Services\HeroSlideService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(
        Request $request,
        ProductService $productService,
        CartService $cartService,
        HeroSlideService $heroSlideService,
    ): View {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
        ]);
        $catalog = $productService->getPublicCatalog($filters);
        $user = $request->user();

        return view('pages.public.home', [
            'heroSlides' => $heroSlideService->getActiveSlides(),
            'categories' => $catalog['categories'],
            'products' => $catalog['products'],
            'searchTerm' => trim($filters['search'] ?? ''),
            'selectedCategory' => $filters['category'] ?? '',
            'cartQuantity' => $user instanceof User ? $cartService->getQuantity($user) : 0,
        ]);
    }
}
