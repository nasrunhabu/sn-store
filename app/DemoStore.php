<?php

namespace App;

use Closure;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use UnexpectedValueException;

class DemoStore
{
    private const DATA_PATH = 'demo-store/data.json';

    private const LOCK_PATH = 'demo-store/data.lock';

    public function all(): array
    {
        return $this->withLock(fn (FilesystemAdapter $disk): array => $this->read($disk));
    }

    public function update(Closure $callback): array
    {
        return $this->withLock(function (FilesystemAdapter $disk) use ($callback): array {
            $data = $callback($this->read($disk));
            $this->write($disk, $data);

            return $data;
        });
    }

    private function withLock(Closure $callback): mixed
    {
        $disk = Storage::disk('local');

        if (! $disk->makeDirectory('demo-store')) {
            throw new RuntimeException('Unable to create the demo data directory.');
        }

        $lock = fopen($disk->path(self::LOCK_PATH), 'c+');

        if ($lock === false) {
            throw new RuntimeException('Unable to open the demo data lock.');
        }

        if (! flock($lock, LOCK_EX)) {
            fclose($lock);

            throw new RuntimeException('Unable to lock the demo data file.');
        }

        try {
            return $callback($disk);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function read(FilesystemAdapter $disk): array
    {
        if (! $disk->exists(self::DATA_PATH)) {
            $this->write($disk, $this->defaults());
        }

        $data = json_decode($disk->get(self::DATA_PATH), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data) || ! isset($data['categories'], $data['products'], $data['transactions'], $data['users'])) {
            throw new UnexpectedValueException('The demo store file has an invalid data structure.');
        }

        return $data;
    }

    private function write(FilesystemAdapter $disk, array $data): void
    {
        $contents = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        if (! $disk->put(self::DATA_PATH, $contents)) {
            throw new RuntimeException('Unable to save the demo store data.');
        }
    }

    private function defaults(): array
    {
        return [
            'categories' => [
                ['id' => 'cat-smartphone', 'name' => 'Smartphone', 'is_active' => true],
                ['id' => 'cat-laptop', 'name' => 'Laptop', 'is_active' => true],
                ['id' => 'cat-audio', 'name' => 'Audio', 'is_active' => true],
                ['id' => 'cat-accessories', 'name' => 'Aksesori', 'is_active' => true],
            ],
            'products' => [
                [
                    'id' => 'product-iphone-15',
                    'name' => 'iPhone 15 128GB',
                    'category_id' => 'cat-smartphone',
                    'price' => 13499000,
                    'original_price' => 14999000,
                    'rating' => '4.9',
                    'reviews' => 128,
                    'badge' => 'Terlaris',
                    'image' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Smartphone dengan layar penuh',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-macbook-air-m3',
                    'name' => 'MacBook Air M3 13"',
                    'category_id' => 'cat-laptop',
                    'price' => 18999000,
                    'original_price' => null,
                    'rating' => '4.9',
                    'reviews' => 86,
                    'badge' => 'Pilihan editor',
                    'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Laptop tipis berwarna silver',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-galaxy-buds-fe',
                    'name' => 'Galaxy Buds FE',
                    'category_id' => 'cat-audio',
                    'price' => 899000,
                    'original_price' => 1199000,
                    'rating' => '4.8',
                    'reviews' => 214,
                    'badge' => 'Hemat 25%',
                    'image' => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Earbud nirkabel warna putih',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-smartwatch-active-3',
                    'name' => 'Smartwatch Active 3',
                    'category_id' => 'cat-accessories',
                    'price' => 1749000,
                    'original_price' => null,
                    'rating' => '4.7',
                    'reviews' => 73,
                    'badge' => 'Terbaru',
                    'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Jam tangan pintar dengan tali hitam',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-canon-eos-r50',
                    'name' => 'Canon EOS R50 Kit',
                    'category_id' => 'cat-accessories',
                    'price' => 12499000,
                    'original_price' => 13299000,
                    'rating' => '4.8',
                    'reviews' => 42,
                    'badge' => 'Hemat 6%',
                    'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Kamera digital dengan lensa',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-jbl-flip-6',
                    'name' => 'JBL Flip 6',
                    'category_id' => 'cat-audio',
                    'price' => 1599000,
                    'original_price' => null,
                    'rating' => '4.8',
                    'reviews' => 109,
                    'badge' => 'Favorit',
                    'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Speaker portabel berwarna gelap',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-sony-wh1000xm5',
                    'name' => 'Sony WH-1000XM5',
                    'category_id' => 'cat-audio',
                    'price' => 4299000,
                    'original_price' => 4799000,
                    'rating' => '4.9',
                    'reviews' => 67,
                    'badge' => 'Hemat 10%',
                    'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Headphone over-ear berwarna hitam',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-logitech-mx-master',
                    'name' => 'Logitech MX Master 3S',
                    'category_id' => 'cat-accessories',
                    'price' => 1499000,
                    'original_price' => null,
                    'rating' => '4.8',
                    'reviews' => 94,
                    'badge' => 'Pilihan editor',
                    'image' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Mouse komputer ergonomis',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-lg-ultragear',
                    'name' => 'LG UltraGear 27"',
                    'category_id' => 'cat-accessories',
                    'price' => 3799000,
                    'original_price' => 4199000,
                    'rating' => '4.7',
                    'reviews' => 38,
                    'badge' => 'Hemat 9%',
                    'image' => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Monitor komputer di atas meja',
                    'is_active' => true,
                ],
                [
                    'id' => 'product-galaxy-tab-s9-fe',
                    'name' => 'Galaxy Tab S9 FE',
                    'category_id' => 'cat-smartphone',
                    'price' => 6499000,
                    'original_price' => null,
                    'rating' => '4.8',
                    'reviews' => 55,
                    'badge' => 'Terbaru',
                    'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=85',
                    'image_alt' => 'Tablet tipis berwarna silver',
                    'is_active' => true,
                ],
            ],
            'transactions' => [
                ['id' => 'SN-2026-001', 'customer' => 'Nadia Putri', 'product' => 'iPhone 15 128GB', 'total' => 13499000, 'status' => 'Diproses'],
                ['id' => 'SN-2026-002', 'customer' => 'Raka Pratama', 'product' => 'Galaxy Buds FE', 'total' => 899000, 'status' => 'Menunggu pembayaran'],
                ['id' => 'SN-2026-003', 'customer' => 'Dewi Anggraini', 'product' => 'MacBook Air M3 13"', 'total' => 18999000, 'status' => 'Dikirim'],
            ],
            'users' => [
                ['id' => 'user-demo-1', 'name' => 'Nadia Putri', 'email' => 'nadia@example.com', 'role' => 'Pelanggan', 'status' => 'Aktif'],
                ['id' => 'user-demo-2', 'name' => 'Raka Pratama', 'email' => 'raka@example.com', 'role' => 'Pelanggan', 'status' => 'Aktif'],
                ['id' => 'user-demo-3', 'name' => 'Dewi Anggraini', 'email' => 'dewi@example.com', 'role' => 'Pelanggan', 'status' => 'Aktif'],
                ['id' => 'user-demo-4', 'name' => 'Bima Saputra', 'email' => 'bima@example.com', 'role' => 'Pelanggan', 'status' => 'Aktif'],
            ],
        ];
    }
}
