@extends('layouts.admin')

@section('title', 'Kategori')
@section('page_title', 'Kategori')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Kategori produk</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola kategori yang tersimpan di database.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900">Tambah kategori</a>
        </div>

        @if (session('category_alert'))
            <div role="status" data-category-alert-fallback class="sr-only">{{ session('category_alert.text') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-slate-900">Daftar kategori</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $categories->count() }} kategori tersimpan di database</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 sm:px-6">Nama</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Slug</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Deskripsi</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Status</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($categories as $category)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 sm:px-6">{{ $category->name }}</td>
                                <td class="px-5 py-4 text-slate-600 sm:px-6">{{ $category->slug }}</td>
                                <td class="px-5 py-4 text-slate-600 sm:px-6">{{ $category->description ?: 'Tidak ada deskripsi' }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-800">Edit</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-category-delete-form data-category-name="{{ $category->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada kategori di database.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" referrerpolicy="no-referrer"></script>
    <script>
        @if (session('category_alert'))
            const categoryAlert = {{ \Illuminate\Support\Js::from(session('category_alert')) }};

            if (window.Swal) {
                window.Swal.fire({
                    ...categoryAlert,
                    confirmButtonColor: '#991b1b',
                });
            } else {
                document.querySelector('[data-category-alert-fallback]')?.classList.remove('sr-only');
                console.error('SweetAlert2 gagal dimuat; pesan status kategori tersedia di halaman.');
            }
        @endif

        document.querySelectorAll('[data-category-delete-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const categoryName = form.dataset.categoryName;

                if (!window.Swal) {
                    if (!window.confirm(`Hapus kategori "${categoryName}"?`)) {
                        event.preventDefault();
                    }

                    return;
                }

                event.preventDefault();

                window.Swal.fire({
                    title: 'Hapus kategori?',
                    text: `Kategori "${categoryName}" akan dihapus.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#991b1b',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
