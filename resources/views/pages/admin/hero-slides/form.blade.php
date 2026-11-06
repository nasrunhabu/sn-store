@extends('layouts.admin')

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@section('content')
    @php
        $isEditing = $slide !== null;
        $formAction = $isEditing
            ? route('admin.hero-slides.update', $slide)
            : route('admin.hero-slides.store');
        $inputClass = 'w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500';
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $pageTitle }}</h1>
                <p class="mt-1 text-sm text-slate-500">Judul dan teks sorot ditampilkan pada dua baris berbeda; teks sorot berwarna merah muda.</p>
            </div>
            <a href="{{ route('admin.hero-slides.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:text-red-800">Kembali ke hero</a>
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

        <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="badge" class="mb-2 block text-sm font-bold text-slate-700">Label kecil <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="badge" name="badge" value="{{ old('badge', $slide?->badge) }}" maxlength="60" class="{{ $inputClass }}">
                    @error('badge')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="title" class="mb-2 block text-sm font-bold text-slate-700">Judul</label>
                    <input id="title" name="title" value="{{ old('title', $slide?->title) }}" required maxlength="100" class="{{ $inputClass }}">
                    @error('title')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="highlight" class="mb-2 block text-sm font-bold text-slate-700">Teks sorot <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="highlight" name="highlight" value="{{ old('highlight', $slide?->highlight) }}" maxlength="100" class="{{ $inputClass }}">
                    @error('highlight')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-bold text-slate-700">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label>
                    <textarea id="description" name="description" rows="3" maxlength="300" class="{{ $inputClass }}">{{ old('description', $slide?->description) }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="button_text" class="mb-2 block text-sm font-bold text-slate-700">Teks tombol <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="button_text" name="button_text" value="{{ old('button_text', $slide?->button_text) }}" maxlength="40" class="{{ $inputClass }}">
                    @error('button_text')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="button_url" class="mb-2 block text-sm font-bold text-slate-700">Tautan tombol <span class="font-normal text-slate-500">(opsional)</span></label>
                    <input id="button_url" name="button_url" value="{{ old('button_url', $slide?->button_url) }}" maxlength="255" placeholder="/produk, #produk, atau https://..." class="{{ $inputClass }}">
                    @error('button_url')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="image" class="mb-2 block text-sm font-bold text-slate-700">Gambar latar @unless ($isEditing)<span class="font-normal text-slate-500">(JPG, PNG, WebP, maks. 5 MB)</span>@endunless</label>
                    @if ($isEditing)
                        <img src="{{ $slide->image_url }}" alt="" class="mb-3 h-28 w-48 rounded-xl object-cover">
                        <p class="mb-2 text-xs text-slate-500">Biarkan kosong untuk mempertahankan gambar saat ini.</p>
                    @endif
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $isEditing) class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-red-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-red-800 hover:file:bg-red-100">
                    @error('image')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sort_order" class="mb-2 block text-sm font-bold text-slate-700">Nomor urut</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" max="9999" value="{{ old('sort_order', $slide?->sort_order ?? 0) }}" required class="{{ $inputClass }}">
                    @error('sort_order')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slide?->is_active ?? true)) class="mt-0.5 rounded border-slate-300 text-red-700 focus:ring-red-500">
                <span>
                    <span class="block text-sm font-bold text-slate-800">Slide aktif</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">Hanya slide aktif yang tampil di halaman publik.</span>
                </span>
            </label>

            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.hero-slides.index') }}" class="rounded-xl px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900">{{ $isEditing ? 'Simpan perubahan' : 'Simpan slide' }}</button>
            </div>
        </form>
    </div>
@endsection
