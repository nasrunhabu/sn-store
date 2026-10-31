@extends('layouts.public')

@section('title', 'Keranjang')

@section('content')
    <section class="bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="mb-8">
                <span class="text-xs font-extrabold uppercase tracking-[0.18em] text-red-700">Belanjaanmu</span>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-gray-950 sm:text-4xl">Keranjang</h1>
            </div>

            @if (session('status'))
                <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($items->isNotEmpty())
                <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <div class="space-y-4">
                        @foreach ($items as $item)
                            <article class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:p-5">
                                <img src="{{ $item->product->image_url }}" alt="{{ $item->product->image_alt }}" class="size-24 rounded-xl bg-gray-50 object-cover">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">{{ $item->product->category->name }}</p>
                                    <h2 class="mt-1 font-extrabold text-gray-900">{{ $item->product->name }}</h2>
                                    <p class="mt-1 text-sm font-bold text-red-700">Rp {{ number_format($item->product->price, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs text-gray-500">Stok tersedia: {{ $item->product->stock }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <form method="POST" action="{{ route('cart.items.update', $item) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="quantity-{{ $item->id }}" class="sr-only">Jumlah {{ $item->product->name }}</label>
                                        <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ $item->product->stock }}" value="{{ $item->quantity }}" required class="w-20 rounded-lg border-gray-300 text-center text-sm focus:border-red-500 focus:ring-red-500">
                                        <button type="submit" class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50">Perbarui</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.items.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-50">Hapus</button>
                                    </form>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
                        <h2 class="text-lg font-extrabold text-gray-950">Ringkasan belanja</h2>
                        <div class="mt-5 flex items-center justify-between gap-4 border-t border-gray-100 pt-4 text-sm">
                            <span class="text-gray-500">Total</span>
                            <span class="text-lg font-extrabold text-gray-950">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('checkout.create') }}" class="mt-5 flex w-full items-center justify-center rounded-xl bg-red-700 px-5 py-3.5 text-sm font-extrabold text-white transition hover:bg-red-800">Lanjut ke checkout</a>
                        <a href="{{ route('products.index') }}" class="mt-3 flex w-full items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-600 transition hover:border-red-200 hover:text-red-700">Lanjut belanja</a>
                    </aside>
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center">
                    <h2 class="text-lg font-extrabold text-gray-900">Keranjang masih kosong</h2>
                    <p class="mt-2 text-sm text-gray-500">Pilih produk yang kamu inginkan untuk mulai berbelanja.</p>
                    <a href="{{ route('products.index') }}" class="mt-5 inline-flex rounded-full bg-red-700 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-800">Jelajahi produk</a>
                </div>
            @endif
        </div>
    </section>
@endsection
