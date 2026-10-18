<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $categories = ['Smartphone', 'Laptop', 'Audio', 'Aksesori'];

        $products = [
            [
                'name' => 'iPhone 15 128GB',
                'category' => 'Smartphone',
                'price' => 'Rp 13.499.000',
                'original_price' => 'Rp 14.999.000',
                'rating' => '4.9',
                'reviews' => 128,
                'badge' => 'Terlaris',
                'image' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Smartphone dengan layar penuh',
            ],
            [
                'name' => 'MacBook Air M3 13"',
                'category' => 'Laptop',
                'price' => 'Rp 18.999.000',
                'original_price' => null,
                'rating' => '4.9',
                'reviews' => 86,
                'badge' => 'Pilihan editor',
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Laptop tipis berwarna silver',
            ],
            [
                'name' => 'Galaxy Buds FE',
                'category' => 'Audio',
                'price' => 'Rp 899.000',
                'original_price' => 'Rp 1.199.000',
                'rating' => '4.8',
                'reviews' => 214,
                'badge' => 'Hemat 25%',
                'image' => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Earbud nirkabel warna putih',
            ],
            [
                'name' => 'Smartwatch Active 3',
                'category' => 'Aksesori',
                'price' => 'Rp 1.749.000',
                'original_price' => null,
                'rating' => '4.7',
                'reviews' => 73,
                'badge' => 'Terbaru',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Jam tangan pintar dengan tali hitam',
            ],
            [
                'name' => 'Canon EOS R50 Kit',
                'category' => 'Aksesori',
                'price' => 'Rp 12.499.000',
                'original_price' => 'Rp 13.299.000',
                'rating' => '4.8',
                'reviews' => 42,
                'badge' => 'Hemat 6%',
                'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Kamera digital dengan lensa',
            ],
            [
                'name' => 'JBL Flip 6',
                'category' => 'Audio',
                'price' => 'Rp 1.599.000',
                'original_price' => null,
                'rating' => '4.8',
                'reviews' => 109,
                'badge' => 'Favorit',
                'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Speaker portabel berwarna gelap',
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'category' => 'Audio',
                'price' => 'Rp 4.299.000',
                'original_price' => 'Rp 4.799.000',
                'rating' => '4.9',
                'reviews' => 67,
                'badge' => 'Hemat 10%',
                'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Headphone over-ear berwarna hitam',
            ],
            [
                'name' => 'Logitech MX Master 3S',
                'category' => 'Aksesori',
                'price' => 'Rp 1.499.000',
                'original_price' => null,
                'rating' => '4.8',
                'reviews' => 94,
                'badge' => 'Pilihan editor',
                'image' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Mouse komputer ergonomis',
            ],
            [
                'name' => 'LG UltraGear 27"',
                'category' => 'Aksesori',
                'price' => 'Rp 3.799.000',
                'original_price' => 'Rp 4.199.000',
                'rating' => '4.7',
                'reviews' => 38,
                'badge' => 'Hemat 9%',
                'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Monitor komputer di atas meja',
            ],
            [
                'name' => 'Galaxy Tab S9 FE',
                'category' => 'Smartphone',
                'price' => 'Rp 6.499.000',
                'original_price' => null,
                'rating' => '4.8',
                'reviews' => 55,
                'badge' => 'Terbaru',
                'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=85',
                'image_alt' => 'Tablet tipis berwarna silver',
            ],
        ];

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::in($categories)],
        ]);

        $searchTerm = trim($filters['search'] ?? '');
        $selectedCategory = $filters['category'] ?? '';

        $products = collect($products)
            ->filter(function (array $product) use ($searchTerm, $selectedCategory): bool {
                $matchesSearch = $searchTerm === ''
                    || stripos($product['name'].' '.$product['category'], $searchTerm) !== false;
                $matchesCategory = $selectedCategory === ''
                    || $product['category'] === $selectedCategory;

                return $matchesSearch && $matchesCategory;
            })
            ->values();

        return view('pages.public.home', [
            'categories' => $categories,
            'products' => $products,
            'searchTerm' => $searchTerm,
            'selectedCategory' => $selectedCategory,
        ]);
    }
}
