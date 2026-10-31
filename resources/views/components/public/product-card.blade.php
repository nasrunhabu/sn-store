@props(['product'])

<article data-product-card class="group overflow-hidden rounded-2xl border border-gray-100 bg-white transition duration-300 hover:-translate-y-1 hover:border-red-100 hover:shadow-xl hover:shadow-gray-900/[0.06] sm:rounded-3xl">
    <div class="relative aspect-[4/3] overflow-hidden bg-gray-50">
        <img src="{{ $product['image'] }}" alt="{{ $product['image_alt'] }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        <span class="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-extrabold text-red-700 shadow-sm sm:left-4 sm:top-4 sm:text-[11px]">{{ $product['badge'] }}</span>
    </div>
    <div class="p-3.5 sm:p-4">
        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 sm:text-[11px]">{{ $product['category'] }}</p>
        <h3 class="mt-1.5 min-h-10 text-sm font-bold leading-5 text-gray-900 sm:text-base">{{ $product['name'] }}</h3>
        <div class="mt-2 flex items-center gap-1.5 text-xs">
            <svg class="size-3.5 text-amber-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="m10 1.5 2.63 5.33 5.88.85-4.25 4.14 1 5.86L10 14.91l-5.26 2.77 1-5.86L1.5 7.68l5.88-.85L10 1.5Z"/></svg>
            <span class="font-bold text-gray-700">{{ $product['rating'] }}</span>
            <span class="text-gray-400">({{ $product['reviews'] }})</span>
        </div>
        <div class="mt-3 flex min-h-12 items-end justify-between gap-2">
            <div class="min-w-0">
                <span class="block text-sm font-extrabold tracking-tight text-gray-950 sm:text-base">{{ $product['price'] }}</span>
                @if ($product['original_price'])
                    <span class="mt-0.5 block text-xs text-gray-400 line-through">{{ $product['original_price'] }}</span>
                @endif
                @if ($product['stock'] < 1)
                    <span class="mt-1 block text-xs font-bold text-red-700">Stok habis</span>
                @else
                    <span class="mt-1 block text-xs text-gray-500">Stok {{ $product['stock'] }}</span>
                @endif
            </div>
            @if ($product['stock'] > 0)
                @auth
                    <form method="POST" action="{{ route('cart.items.store', $product['id']) }}">
                        @csrf
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" title="Tambah ke keranjang" aria-label="Tambah {{ $product['name'] }} ke keranjang" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-700 text-white transition hover:bg-red-800">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 1.9-1.4L21 8H6"/>
                                <path stroke-linecap="round" d="M10 10v4m4-4v4"/>
                                <circle cx="10" cy="20" r="1"/>
                                <circle cx="18" cy="20" r="1"/>
                            </svg>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" title="Masuk untuk membeli" aria-label="Masuk untuk menambahkan {{ $product['name'] }} ke keranjang" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-red-50 hover:text-red-700">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 1.9-1.4L21 8H6"/>
                            <path stroke-linecap="round" d="M10 10v4m4-4v4"/>
                            <circle cx="10" cy="20" r="1"/>
                            <circle cx="18" cy="20" r="1"/>
                        </svg>
                    </a>
                @endauth
            @else
                <button type="button" disabled title="Stok habis" aria-label="{{ $product['name'] }} sedang habis" class="flex size-10 shrink-0 cursor-not-allowed items-center justify-center rounded-full bg-gray-100 text-gray-400">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 1.9-1.4L21 8H6"/>
                        <path stroke-linecap="round" d="M10 10v4m4-4v4"/>
                        <circle cx="10" cy="20" r="1"/>
                        <circle cx="18" cy="20" r="1"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</article>
