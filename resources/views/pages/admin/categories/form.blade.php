@extends('layouts.admin')

@section('title', $pageTitle)
@section('page_title', $pageTitle)

@section('content')
    @php
        $isEditing = $category !== null;
        $formAction = $isEditing
            ? route('admin.categories.update', $category)
            : route('admin.categories.store');
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ $pageTitle }}</h1>
                <p class="mt-1 text-sm text-slate-500">Slug akan dibuat otomatis dari nama kategori.</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:text-red-800">Kembali ke kategori</a>
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

        <form method="POST" action="{{ $formAction }}" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-2 block text-sm font-bold text-slate-700">Nama kategori</label>
                    <input id="name" name="name" value="{{ old('name', $category?->name ?? '') }}" required maxlength="80" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="description" class="mb-2 block text-sm font-bold text-slate-700">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label>
                    <textarea id="description" name="description" rows="4" maxlength="5000" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">{{ old('description', $category?->description ?? '') }}</textarea>
                    @error('description')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true)) class="mt-0.5 rounded border-slate-300 text-red-700 focus:ring-red-500">
                <span>
                    <span class="block text-sm font-bold text-slate-800">Kategori aktif</span>
                    <span class="mt-1 block text-xs leading-5 text-slate-500">Nonaktifkan kategori untuk menyembunyikannya dari penggunaan publik.</span>
                </span>
            </label>

            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('admin.categories.index') }}" class="rounded-xl px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900">{{ $isEditing ? 'Simpan perubahan' : 'Simpan kategori' }}</button>
            </div>
        </form>
    </div>
@endsection
