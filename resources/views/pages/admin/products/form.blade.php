@extends('layouts.admin')

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@section('content')
    @php
        $isEditing = $product !== null;
        $formAction = $isEditing
            ? route('admin.products.update', $product['id'])
            : route('admin.products.store');
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $pageTitle }}</h1>
                <p class="mt-1 text-sm text-slate-500">Perubahan produk aktif akan langsung terlihat di halaman publik.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:text-red-800">Kembali ke produk</a>
        </div>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($categories->isEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Buat kategori terlebih dahulu sebelum menambahkan produk.</div>
        @endif

        <form method="POST" action="{{ $formAction }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Nama produk</label>
                    <input id="name" name="name" value="{{ old('name', $product['name'] ?? '') }}" required maxlength="120" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-bold text-slate-700">Kategori</label>
                    <select id="category_id" name="category_id" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}" @selected(old('category_id', $product['category_id'] ?? '') === $category['id'])>{{ $category['name'] }}{{ $category['is_active'] ? '' : ' (nonaktif)' }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="badge" class="mb-2 block text-sm font-bold text-slate-700">Label produk</label>
                    <input id="badge" name="badge" value="{{ old('badge', $product['badge'] ?? '') }}" required maxlength="40" placeholder="Contoh: Terbaru" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('badge')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="price" class="mb-2 block text-sm font-bold text-slate-700">Harga (rupiah)</label>
                    <input id="price" name="price" type="number" min="1" step="1" value="{{ old('price', $product['price'] ?? '') }}" required class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('price')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="original_price" class="mb-2 block text-sm font-bold text-slate-700">Harga sebelum diskon (opsional)</label>
                    <input id="original_price" name="original_price" type="number" min="1" step="1" value="{{ old('original_price', $product['original_price'] ?? '') }}" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('original_price')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="image" class="mb-2 block text-sm font-bold text-slate-700">URL gambar</label>
                    <input id="image" name="image" type="url" value="{{ old('image', $product['image'] ?? '') }}" required maxlength="2048" placeholder="https://" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('image')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="image_alt" class="mb-2 block text-sm font-bold text-slate-700">Deskripsi gambar</label>
                    <input id="image_alt" name="image_alt" value="{{ old('image_alt', $product['image_alt'] ?? '') }}" required maxlength="160" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('image_alt')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>

            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product['is_active'] ?? true)) class="mt-0.5 rounded border-slate-300 text-red-700 focus:ring-red-500">
                <span>
                    <span class="block text-sm font-bold text-slate-800">Tampilkan di etalase publik</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">Produk nonaktif tidak terlihat oleh pengunjung. Kategori produk juga harus aktif agar produk tampil.</span>
                </span>
            </label>

            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.products.index') }}" class="rounded-xl px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Batal</a>
                <button type="submit" @disabled($categories->isEmpty()) class="rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900 disabled:cursor-not-allowed disabled:opacity-50">{{ $isEditing ? 'Simpan perubahan' : 'Simpan produk' }}</button>
            </div>
        </form>
    </div>
@endsection
