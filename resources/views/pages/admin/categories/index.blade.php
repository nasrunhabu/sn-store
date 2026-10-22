@extends('layouts.admin')

@section('title', 'Kategori')
@section('page_title', 'Kategori')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-900">
            Data pada modul ini adalah data demo dan disimpan sebagai berkas lokal, bukan di database.
        </section>

        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <h1 class="text-lg font-extrabold tracking-tight text-slate-900">Tambah kategori</h1>
                <p class="mt-1 text-sm text-slate-500">Kategori aktif tersedia sebagai filter di etalase publik.</p>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-5 flex flex-col gap-3 sm:flex-row">
                @csrf
                <label class="sr-only" for="new-category-name">Nama kategori</label>
                <input id="new-category-name" name="name" value="{{ old('name') }}" required maxlength="80" placeholder="Contoh: Kamera" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                <button type="submit" class="rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900">Tambah kategori</button>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-slate-900">Daftar kategori</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $categories->count() }} kategori demo</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($categories as $category)
                    <div class="flex flex-col gap-4 px-5 py-5 sm:px-6 lg:flex-row lg:items-center">
                        <form method="POST" action="{{ route('admin.categories.update', $category['id']) }}" class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                            @csrf
                            @method('PUT')
                            <label class="sr-only" for="category-{{ $category['id'] }}">Nama kategori</label>
                            <input id="category-{{ $category['id'] }}" name="name" value="{{ $category['name'] }}" required maxlength="80" class="min-w-0 flex-1 rounded-xl border-slate-300 text-sm font-semibold shadow-sm focus:border-red-500 focus:ring-red-500">
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                                <input type="checkbox" name="is_active" value="1" @checked($category['is_active']) class="rounded border-slate-300 text-red-700 focus:ring-red-500">
                                Tampilkan di publik
                            </label>
                            <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-800">Simpan</button>
                        </form>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category['id']) }}" onsubmit="return confirm('Hapus kategori ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl px-4 py-2.5 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                        </form>
                    </div>
                @empty
                    <p class="px-6 py-10 text-center text-sm text-slate-500">Belum ada kategori demo.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
