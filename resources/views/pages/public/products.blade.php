@extends('layouts.public')

@section('title', 'Produk')
@section('description', 'Jelajahi produk pilihan SN Store dan temukan gadget yang tepat untukmu.')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="mb-8">
                <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-red-700">Katalog SN Store</span>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">Semua Produk</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-gray-500 sm:text-base">Temukan gadget favorit dengan kualitas terbaik, harga yang pas, dan garansi resmi.</p>
            </div>

            <div class="grid items-start gap-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-8">
                <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" aria-label="Filter produk">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-base font-extrabold text-gray-950">Kategori</h2>
                        @if ($selectedCategory !== '')
                            <a href="{{ route('products.index', ['search' => $searchTerm ?: null]) }}" class="text-xs font-bold text-red-700 hover:text-red-800">Reset</a>
                        @endif
                    </div>
                    <nav class="mt-4 flex flex-wrap gap-2 lg:flex-col lg:gap-1" aria-label="Filter berdasarkan kategori">
                        <a href="{{ route('products.index', ['search' => $searchTerm ?: null]) }}" @class([
                            'rounded-xl px-3 py-2.5 text-sm font-semibold transition lg:w-full',
                            'bg-red-50 text-red-700' => $selectedCategory === '',
                            'text-gray-600 hover:bg-gray-50 hover:text-red-700' => $selectedCategory !== '',
                        ])>
                            Semua Produk
                        </a>
                        @foreach ($categories as $category)
                            <a href="{{ route('products.index', ['category' => $category, 'search' => $searchTerm ?: null]) }}" @class([
                                'rounded-xl px-3 py-2.5 text-sm font-semibold transition lg:w-full',
                                'bg-red-50 text-red-700' => $selectedCategory === $category,
                                'text-gray-600 hover:bg-gray-50 hover:text-red-700' => $selectedCategory !== $category,
                            ])>
                                {{ $category }}
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <div class="min-w-0">
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
                        <form action="{{ route('products.index') }}" method="GET" role="search" class="flex items-center rounded-full border border-gray-200 bg-gray-50 px-4 py-3 focus-within:border-red-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-red-50">
                            @if ($selectedCategory !== '')
                                <input type="hidden" name="category" value="{{ $selectedCategory }}">
                            @endif
                            <label for="products-search" class="sr-only">Cari produk</label>
                            <input id="products-search" type="search" name="search" value="{{ $searchTerm }}" placeholder="Cari nama produk atau kategori..." class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-700 placeholder:text-gray-400 focus:ring-0">
                            <button type="submit" class="ml-3 flex size-9 shrink-0 items-center justify-center rounded-full bg-red-700 text-white transition hover:bg-red-800" aria-label="Cari produk">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7"/>
                                    <path stroke-linecap="round" d="m20 20-4-4"/>
                                </svg>
                            </button>
                        </form>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm text-gray-500">
                                <span class="font-extrabold text-gray-900">{{ $products->count() }}</span> produk ditemukan
                                @if ($selectedCategory !== '')
                                    <span>di <span class="font-bold text-gray-700">{{ $selectedCategory }}</span></span>
                                @endif
                            </p>
                            @if ($searchTerm !== '' || $selectedCategory !== '')
                                <a href="{{ route('products.index') }}" class="text-sm font-bold text-red-700 hover:text-red-800">Hapus semua filter</a>
                            @endif
                        </div>
                    </div>

                    @if ($products->isNotEmpty())
                        <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-5 xl:grid-cols-3">
                            @foreach ($products as $product)
                                <x-public.product-card :product="$product" />
                            @endforeach
                        </div>
                    @else
                        <div class="mt-6 rounded-3xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center">
                            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-400">
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/></svg>
                            </span>
                            <h2 class="mt-4 text-lg font-extrabold text-gray-900">Produk belum ditemukan</h2>
                            <p class="mt-2 text-sm text-gray-500">Coba kata kunci lain atau pilih kategori yang berbeda.</p>
                            <a href="{{ route('products.index') }}" class="mt-5 inline-flex rounded-full bg-red-700 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-800">Lihat semua produk</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
