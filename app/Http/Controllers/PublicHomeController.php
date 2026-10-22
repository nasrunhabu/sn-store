<?php

namespace App\Http\Controllers;

use App\DemoStore;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(Request $request, DemoStore $store): View
    {
        $data = $store->all();
        $activeCategories = collect($data['categories'])
            ->where('is_active', true)
            ->sortBy('name')
            ->values();
        $categoryNames = $activeCategories->pluck('name')->all();
        $categoryNamesById = $activeCategories->keyBy('id');

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:80'],
        ]);

        $searchTerm = trim($filters['search'] ?? '');
        $selectedCategory = $filters['category'] ?? '';

        $products = collect($data['products'])
            ->filter(function (array $product) use ($categoryNamesById): bool {
                return $product['is_active']
                    && $categoryNamesById->has($product['category_id']);
            })
            ->map(function (array $product) use ($categoryNamesById): array {
                $product['category'] = $categoryNamesById->get($product['category_id'])['name'];
                $product['price'] = 'Rp '.number_format($product['price'], 0, ',', '.');
                $product['original_price'] = $product['original_price'] === null
                    ? null
                    : 'Rp '.number_format($product['original_price'], 0, ',', '.');

                return $product;
            })
            ->filter(function (array $product) use ($searchTerm, $selectedCategory): bool {
                $matchesSearch = $searchTerm === ''
                    || mb_stripos($product['name'].' '.$product['category'], $searchTerm) !== false;
                $matchesCategory = $selectedCategory === ''
                    || $product['category'] === $selectedCategory;

                return $matchesSearch && $matchesCategory;
            })
            ->values();

        return view('pages.public.home', [
            'categories' => $categoryNames,
            'products' => $products,
            'searchTerm' => $searchTerm,
            'selectedCategory' => $selectedCategory,
        ]);
    }
}
