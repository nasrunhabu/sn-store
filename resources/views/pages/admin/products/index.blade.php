@extends('layouts.admin')

@section('title', 'Produk')
@section('page_title', 'Produk')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="flex flex-col gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm leading-6 text-amber-900">Produk aktif dari data demo ini akan langsung muncul di etalase publik. Data disimpan sebagai berkas lokal, bukan di database.</p>
            <a href="{{ route('admin.products.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900">Tambah produk</a>
        </section>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h1 class="font-extrabold text-slate-900">Katalog demo</h1>
                <p class="mt-1 text-xs text-slate-500">{{ $products->count() }} produk</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Produk</th>
                            <th scope="col" class="px-6 py-3">Kategori</th>
                            <th scope="col" class="px-6 py-3">Harga</th>
                            <th scope="col" class="px-6 py-3">Status publik</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $product['image'] }}" alt="" class="size-12 rounded-xl bg-slate-100 object-cover">
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $product['name'] }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $product['badge'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $categories->get($product['category_id'])['name'] ?? 'Kategori tidak ditemukan' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">Rp {{ number_format($product['price'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider {{ $product['is_active'] ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $product['is_active'] ? 'Tampil' : 'Disembunyikan' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.products.edit', $product['id']) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:text-red-800">Edit</a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product['id']) }}" onsubmit="return confirm('Hapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada produk demo. Tambahkan produk pertama untuk ditampilkan di etalase.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
