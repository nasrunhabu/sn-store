@extends('layouts.admin')

@section('title', 'Produk')
@section('page_title', 'Produk')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="flex flex-col gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm leading-6 text-amber-900">Produk aktif akan langsung muncul di etalase publik. Data produk tersimpan di database dan gambar disimpan di penyimpanan lokal.</p>
            <a href="{{ route('admin.products.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900">Tambah produk</a>
        </section>

        @if (session('product_alert'))
            <div role="status" data-product-alert-fallback class="sr-only">{{ session('product_alert.text') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h1 class="font-extrabold text-slate-900">Katalog produk</h1>
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
                                        <img src="{{ $product->image_url }}" alt="" class="size-12 rounded-xl bg-slate-100 object-cover">
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $product->name }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $product->badge }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->category->name }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $product->is_active ? 'Tampil' : 'Disembunyikan' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:text-red-800">Edit</a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-product-delete-form data-product-name="{{ $product->name }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada produk. Tambahkan produk pertama untuk ditampilkan di etalase.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" referrerpolicy="no-referrer"></script>
    <script>
        @if (session('product_alert'))
            const productAlert = {{ \Illuminate\Support\Js::from(session('product_alert')) }};

            if (window.Swal) {
                window.Swal.fire({
                    ...productAlert,
                    confirmButtonColor: '#991b1b',
                });
            } else {
                document.querySelector('[data-product-alert-fallback]')?.classList.remove('sr-only');
                console.error('SweetAlert2 gagal dimuat; pesan status produk tersedia di halaman.');
            }
        @endif

        document.querySelectorAll('[data-product-delete-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const productName = form.dataset.productName;

                if (!window.Swal) {
                    if (!window.confirm(`Hapus produk "${productName}"?`)) {
                        event.preventDefault();
                    }

                    return;
                }

                event.preventDefault();

                window.Swal.fire({
                    title: 'Hapus produk?',
                    text: `Produk "${productName}" dan gambar lokalnya akan dihapus.`,
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
