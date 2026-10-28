<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(Request $request, ProductService $productService): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
        ]);
        $catalog = $productService->getPublicCatalog($filters);

        return view('pages.public.home', [
            'categories' => $catalog['categories'],
            'products' => $catalog['products'],
            'searchTerm' => trim($filters['search'] ?? ''),
            'selectedCategory' => $filters['category'] ?? '',
        ]);
    }
}
