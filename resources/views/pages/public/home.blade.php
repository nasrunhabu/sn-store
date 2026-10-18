@extends('layouts.public')

@section('title', 'Gadget Pilihan untuk Semua')
@section('description', 'Belanja smartphone, laptop, audio, dan aksesori pilihan dengan promo menarik di SN Store.')

@section('content')
    <div class="bg-gray-50">
        <section class="mx-auto max-w-7xl px-4 pb-8 pt-7 sm:px-6 sm:pt-10 lg:px-8" aria-label="Promo pilihan">
            <div class="relative overflow-hidden rounded-[2rem] bg-red-950 text-white shadow-2xl shadow-red-900/10" x-data="{ activeSlide: 0 }">
                <div x-show="activeSlide === 0" x-transition.opacity class="relative isolate min-h-[420px] overflow-hidden sm:min-h-[460px]">
                    <img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=1800&q=90" alt="" class="absolute inset-y-0 right-0 h-full w-full object-cover object-center sm:w-3/4" fetchpriority="high">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-950 via-red-950/90 to-red-950/20"></div>
                    <div class="relative flex min-h-[420px] max-w-2xl flex-col items-start justify-center px-7 py-12 sm:min-h-[460px] sm:px-12 lg:px-16">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-red-100 backdrop-blur">
                            <span class="size-1.5 rounded-full bg-red-300"></span>
                            Teknologi, tanpa kompromi
                        </span>
                        <h1 class="mt-6 text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">Upgrade harimu,<br><span class="text-red-300">mulai dari sini.</span></h1>
                        <p class="mt-5 max-w-lg text-sm leading-7 text-red-50/85 sm:text-base">Temukan gadget pilihan yang pas untuk kerja, berkarya, dan menikmati setiap momen.</p>
                        <a href="#produk" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-extrabold text-red-800 shadow-lg shadow-red-950/20 transition hover:-translate-y-0.5 hover:bg-red-50">
                            Belanja sekarang
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div x-show="activeSlide === 1" x-cloak x-transition.opacity class="relative isolate min-h-[420px] overflow-hidden sm:min-h-[460px]">
                    <img src="https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1800&q=90" alt="" class="absolute inset-y-0 right-0 h-full w-full object-cover object-center sm:w-3/4">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-950 via-red-950/90 to-red-950/20"></div>
                    <div class="relative flex min-h-[420px] max-w-2xl flex-col items-start justify-center px-7 py-12 sm:min-h-[460px] sm:px-12 lg:px-16">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-red-100 backdrop-blur">Siap untuk produktif</span>
                        <h2 class="mt-6 text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">Kerja lebih fokus,<br><span class="text-red-300">ide makin lancar.</span></h2>
                        <p class="mt-5 max-w-lg text-sm leading-7 text-red-50/85 sm:text-base">Laptop dan aksesori andalan untuk mendukung setiap target besarmu.</p>
                        <a href="{{ route('home', ['category' => 'Laptop']) }}#produk" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-extrabold text-red-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-red-50">
                            Jelajahi laptop
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                    </div>
                </div>

                <div x-show="activeSlide === 2" x-cloak x-transition.opacity class="relative isolate min-h-[420px] overflow-hidden sm:min-h-[460px]">
                    <img src="https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=1800&q=90" alt="" class="absolute inset-y-0 right-0 h-full w-full object-cover object-center sm:w-3/4">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-950 via-red-950/90 to-red-950/20"></div>
                    <div class="relative flex min-h-[420px] max-w-2xl flex-col items-start justify-center px-7 py-12 sm:min-h-[460px] sm:px-12 lg:px-16">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-red-100 backdrop-blur">Pilihan minggu ini</span>
                        <h2 class="mt-6 text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">Teknologi hebat,<br><span class="text-red-300">harga bersahabat.</span></h2>
                        <p class="mt-5 max-w-lg text-sm leading-7 text-red-50/85 sm:text-base">Nikmati promo spesial untuk perangkat favoritmu. Belanja nyaman, tanpa khawatir.</p>
                        <a href="#produk" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-extrabold text-red-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-red-50">
                            Lihat produk
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                        </a>
                    </div>
                </div>

                <div class="absolute inset-x-7 bottom-6 flex items-center justify-between sm:inset-x-12 lg:inset-x-16">
                    <div class="flex items-center gap-2" aria-label="Pilih slide">
                        <button type="button" @click="activeSlide = 0" :aria-current="activeSlide === 0 ? 'true' : 'false'" class="h-2 rounded-full bg-white/40 transition-all aria-[current=true]:w-8 aria-[current=true]:bg-white" aria-label="Slide 1"></button>
                        <button type="button" @click="activeSlide = 1" :aria-current="activeSlide === 1 ? 'true' : 'false'" class="h-2 rounded-full bg-white/40 transition-all aria-[current=true]:w-8 aria-[current=true]:bg-white" aria-label="Slide 2"></button>
                        <button type="button" @click="activeSlide = 2" :aria-current="activeSlide === 2 ? 'true' : 'false'" class="h-2 rounded-full bg-white/40 transition-all aria-[current=true]:w-8 aria-[current=true]:bg-white" aria-label="Slide 3"></button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="activeSlide = (activeSlide + 2) % 3" class="flex size-9 items-center justify-center rounded-full border border-white/25 text-white transition hover:bg-white/15" aria-label="Promo sebelumnya">
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16 10a.75.75 0 0 1-.75.75H4.56l4.22 4.22a.75.75 0 1 1-1.06 1.06l-5.5-5.5a.75.75 0 0 1 0-1.06l5.5-5.5a.75.75 0 1 1 1.06 1.06L4.56 9.25h10.69A.75.75 0 0 1 16 10Z" clip-rule="evenodd"/></svg>
                        </button>
                        <button type="button" @click="activeSlide = (activeSlide + 1) % 3" class="flex size-9 items-center justify-center rounded-full border border-white/25 text-white transition hover:bg-white/15" aria-label="Promo berikutnya">
                            <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4 10a.75.75 0 0 1 .75-.75h10.69l-4.22-4.22a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-7xl grid-cols-2 gap-5 px-4 pb-10 sm:grid-cols-4 sm:px-6 lg:px-8" aria-label="Keunggulan SN Store">
            <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm shadow-gray-900/[0.03]">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                </span>
                <span><span class="block text-xs font-extrabold text-gray-900 sm:text-sm">Produk original</span><span class="mt-1 block text-[11px] text-gray-500 sm:text-xs">Garansi resmi</span></span>
            </div>
            <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm shadow-gray-900/[0.03]">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v11H3zM14 10h4l3 3v5h-7z"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="17.5" cy="18" r="1.5"/></svg>
                </span>
                <span><span class="block text-xs font-extrabold text-gray-900 sm:text-sm">Pengiriman cepat</span><span class="mt-1 block text-[11px] text-gray-500 sm:text-xs">Aman sampai tujuan</span></span>
            </div>
            <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm shadow-gray-900/[0.03]">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 1 1 2.34 5.66L4 20l.7-3.3A7.97 7.97 0 0 1 4 12Z"/><path stroke-linecap="round" d="M9 12h6"/></svg>
                </span>
                <span><span class="block text-xs font-extrabold text-gray-900 sm:text-sm">Bantuan ramah</span><span class="mt-1 block text-[11px] text-gray-500 sm:text-xs">Kami siap membantu</span></span>
            </div>
            <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm shadow-gray-900/[0.03]">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M17 7.5c0-1.38-2.24-2.5-5-2.5S7 6.12 7 7.5 9.24 10 12 10s5 1.12 5 2.5-2.24 2.5-5 2.5-5-1.12-5-2.5"/></svg>
                </span>
                <span><span class="block text-xs font-extrabold text-gray-900 sm:text-sm">Harga bersaing</span><span class="mt-1 block text-[11px] text-gray-500 sm:text-xs">Belanja lebih hemat</span></span>
            </div>
        </section>
    </div>

    <section id="produk" class="scroll-mt-32 mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-red-700">Pilihan untukmu</span>
                <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">
                    @if ($searchTerm !== '' || $selectedCategory !== '')
                        Hasil produk
                    @else
                        10 Produk Terbaru
                    @endif
                </h2>
                <p class="mt-3 max-w-xl text-sm leading-6 text-gray-500 sm:text-base">
                    @if ($searchTerm !== '' || $selectedCategory !== '')
                        @if ($searchTerm !== '')
                            Hasil pencarian untuk “{{ $searchTerm }}”
                        @endif
                        @if ($selectedCategory !== '')
                            {{ $searchTerm !== '' ? ' · ' : '' }}Kategori {{ $selectedCategory }}
                        @endif
                    @else
                        Gadget favorit dengan kualitas terbaik, harga yang pas, dan garansi resmi.
                    @endif
                </p>
            </div>
            @if ($searchTerm !== '' || $selectedCategory !== '')
                <a href="{{ route('home') }}#produk" class="inline-flex w-fit items-center gap-2 rounded-full border border-gray-200 px-5 py-2.5 text-sm font-bold text-gray-600 transition hover:border-red-200 hover:text-red-700">
                    Hapus filter
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </a>
            @else
                <a href="#kategori" class="inline-flex w-fit items-center gap-2 text-sm font-extrabold text-red-700 transition hover:gap-3">
                    Lihat kategori
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </a>
            @endif
        </div>

        <div id="kategori" class="mt-8 flex flex-wrap gap-2.5" aria-label="Filter kategori produk">
            <a href="{{ route('home') }}#produk" class="{{ $selectedCategory === '' ? 'border-red-700 bg-red-700 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-red-200 hover:text-red-700' }} rounded-full border px-4 py-2 text-xs font-bold transition sm:text-sm">Semua</a>
            @foreach ($categories as $category)
                <a href="{{ route('home', ['category' => $category]) }}#produk" class="{{ $selectedCategory === $category ? 'border-red-700 bg-red-700 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-red-200 hover:text-red-700' }} rounded-full border px-4 py-2 text-xs font-bold transition sm:text-sm">{{ $category }}</a>
            @endforeach
        </div>

        @if ($products->isNotEmpty())
            <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-3 xl:grid-cols-5">
                @foreach ($products as $product)
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
                            <div class="mt-3 flex min-h-12 flex-col justify-end">
                                <span class="text-sm font-extrabold tracking-tight text-gray-950 sm:text-base">{{ $product['price'] }}</span>
                                @if ($product['original_price'])
                                    <span class="mt-0.5 text-xs text-gray-400 line-through">{{ $product['original_price'] }}</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="mt-8 rounded-3xl border border-dashed border-gray-200 bg-gray-50 px-6 py-16 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-white text-gray-400 shadow-sm">
                    <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/></svg>
                </span>
                <h3 class="mt-4 text-lg font-extrabold text-gray-900">Produk belum ditemukan</h3>
                <p class="mt-2 text-sm text-gray-500">Coba kata kunci lain atau jelajahi semua produk kami.</p>
                <a href="{{ route('home') }}#produk" class="mt-5 inline-flex rounded-full bg-red-700 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-800">Lihat semua produk</a>
            </div>
        @endif
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 sm:pb-20 lg:px-8">
        <div class="relative isolate overflow-hidden rounded-[2rem] bg-red-700 px-7 py-12 text-white sm:px-12 sm:py-14 lg:px-16">
            <div class="absolute -right-10 -top-24 size-72 rounded-full border-[36px] border-white/10 sm:size-96"></div>
            <div class="absolute -bottom-36 right-48 size-72 rounded-full bg-red-600/70 blur-2xl"></div>
            <div class="relative flex flex-col gap-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="max-w-2xl">
                    <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-red-100">Waktunya upgrade</span>
                    <h2 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Teknologi yang tepat<br class="hidden sm:block"> bikin semua terasa lebih mudah.</h2>
                    <p class="mt-3 max-w-lg text-sm leading-6 text-red-100/90 sm:text-base">Temukan perangkat yang mendukung langkahmu berikutnya bersama SN Store.</p>
                </div>
                <a href="{{ route('home') }}#produk" class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-extrabold text-red-800 shadow-lg transition hover:-translate-y-0.5 hover:bg-red-50">
                    Mulai belanja
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                </a>
            </div>
        </div>
    </section>
@endsection
