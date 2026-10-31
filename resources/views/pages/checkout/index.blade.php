@extends('layouts.checkout')

@section('title', 'Pesanan Saya')

@section('content')
    <section class="bg-gray-50 py-10 sm:py-14">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-red-700">Riwayat Belanja</span>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">Pesanan Saya</h1>
                    <p class="mt-1 text-sm text-gray-500">Pantau status transaksi dan pembayaran pesanan Anda.</p>
                </div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 shadow-sm transition hover:border-red-200 hover:text-red-700">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Belanja lagi
                </a>
            </div>

            @if (session('status'))
                <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($orders->isNotEmpty())
                <div class="space-y-5">
                    @foreach ($orders as $order)
                        <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-md">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/70 px-5 py-3.5 sm:px-6">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs">
                                    <span class="font-extrabold text-gray-900">#{{ $order->order_number }}</span>
                                    <span class="text-gray-300">•</span>
                                    <time class="text-gray-500" datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('d M Y, H:i') }} WIB</time>
                                </div>
                                <div>
                                    @if ($order->status->value === 'pending')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800">
                                            <span class="size-1.5 rounded-full bg-amber-500"></span>
                                            Menunggu pembayaran
                                        </span>
                                    @elseif ($order->status->value === 'paid')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Pembayaran berhasil
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-800">
                                            <span class="size-1.5 rounded-full bg-red-500"></span>
                                            Dibatalkan
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">
                                <div class="space-y-3">
                                    @foreach ($order->items as $item)
                                        <div class="flex items-center gap-4 text-sm">
                                            @if ($item->product?->image_url)
                                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="size-14 shrink-0 rounded-xl bg-gray-50 object-cover">
                                            @else
                                                <div class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-xs font-bold text-gray-400">
                                                    SN
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <h2 class="truncate font-bold text-gray-900">{{ $item->product_name }}</h2>
                                                <p class="mt-0.5 text-xs text-gray-500">{{ $item->quantity }} barang × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="font-bold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-4">
                                    <div>
                                        <p class="text-xs text-gray-500">Total belanja</p>
                                        <p class="text-lg font-extrabold text-gray-950">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if ($order->status->value === 'pending')
                                            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-700 px-5 py-2.5 text-xs font-bold text-white transition hover:bg-red-800">
                                                Bayar sekarang
                                                <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/>
                                                </svg>
                                            </a>
                                        @else
                                            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-xs font-bold text-gray-700 transition hover:bg-gray-50 hover:text-red-700">
                                                Lihat detail pesanan
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    @if ($orders->hasPages())
                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center shadow-sm">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-red-50 text-red-700">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </span>
                    <h2 class="mt-4 text-lg font-extrabold text-gray-900">Belum ada transaksi</h2>
                    <p class="mt-1 text-sm text-gray-500">Anda belum memiliki riwayat pesanan. Mulai belanja produk favorit Anda sekarang!</p>
                    <a href="{{ route('products.index') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-red-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-red-800">
                        Jelajahi produk
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
