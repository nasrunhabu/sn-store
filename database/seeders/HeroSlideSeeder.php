<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        if (HeroSlide::query()->exists()) {
            return;
        }

        $slides = [
            [
                'badge' => 'Teknologi, tanpa kompromi',
                'title' => 'Upgrade harimu,',
                'highlight' => 'mulai dari sini.',
                'description' => 'Temukan gadget pilihan yang pas untuk kerja, berkarya, dan menikmati setiap momen.',
                'button_text' => 'Belanja sekarang',
                'button_url' => '#produk',
                'image_path' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=1800&q=90',
            ],
            [
                'badge' => 'Siap untuk produktif',
                'title' => 'Kerja lebih fokus,',
                'highlight' => 'ide makin lancar.',
                'description' => 'Laptop dan aksesori andalan untuk mendukung setiap target besarmu.',
                'button_text' => 'Jelajahi produk',
                'button_url' => '#produk',
                'image_path' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1800&q=90',
            ],
            [
                'badge' => 'Pilihan minggu ini',
                'title' => 'Teknologi hebat,',
                'highlight' => 'harga bersahabat.',
                'description' => 'Nikmati promo spesial untuk perangkat favoritmu. Belanja nyaman, tanpa khawatir.',
                'button_text' => 'Lihat produk',
                'button_url' => '#produk',
                'image_path' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=1800&q=90',
            ],
        ];

        foreach ($slides as $index => $slide) {
            HeroSlide::create([...$slide, 'sort_order' => $index + 1, 'is_active' => true]);
        }
    }
}
