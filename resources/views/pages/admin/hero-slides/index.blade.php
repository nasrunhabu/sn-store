@extends('layouts.admin')

@section('title', 'Hero halaman publik')
@section('page_title', 'Hero halaman publik')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Hero halaman publik</h1>
                <p class="mt-1 text-sm text-slate-500">Atur slide promo yang tampil di bagian atas beranda.</p>
            </div>
            <a href="{{ route('admin.hero-slides.create') }}" class="rounded-xl bg-red-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-900">Tambah slide</a>
        </div>

        @if (session('hero_alert'))
            <div role="status" data-hero-alert-fallback class="sr-only">{{ session('hero_alert.text') }}</div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 class="font-extrabold text-slate-900">Daftar slide</h2>
                <p class="mt-1 text-xs text-slate-500">{{ $slides->where('is_active', true)->count() }} dari {{ $slides->count() }} slide aktif. Slide tampil berurutan sesuai nomor urut.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 sm:px-6">Urutan</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Gambar</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Konten</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Tombol</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Status</th>
                            <th scope="col" class="px-5 py-3 sm:px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($slides as $slide)
                            <tr>
                                <td class="px-5 py-4 font-semibold text-slate-900 sm:px-6">{{ $slide->sort_order }}</td>
                                <td class="px-5 py-4 sm:px-6">
                                    <img src="{{ $slide->image_url }}" alt="" class="h-14 w-24 rounded-lg object-cover" loading="lazy">
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <p class="font-semibold text-slate-900">{{ $slide->title }} {{ $slide->highlight }}</p>
                                    <p class="mt-1 max-w-sm truncate text-xs text-slate-500">{{ $slide->description ?: 'Tidak ada deskripsi' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600 sm:px-6">
                                    @if ($slide->button_text)
                                        {{ $slide->button_text }}
                                        <span class="block text-xs text-slate-400">{{ $slide->button_url }}</span>
                                    @else
                                        <span class="text-slate-400">Tanpa tombol</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $slide->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-800">Edit</a>
                                        <form method="POST" action="{{ route('admin.hero-slides.toggle', $slide) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-50">{{ $slide->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" data-hero-delete-form data-hero-title="{{ $slide->title }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500">Belum ada slide hero. Hero tidak ditampilkan di halaman publik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" referrerpolicy="no-referrer"></script>
    <script>
        @if (session('hero_alert'))
            const heroAlert = {{ \Illuminate\Support\Js::from(session('hero_alert')) }};

            if (window.Swal) {
                window.Swal.fire({
                    ...heroAlert,
                    confirmButtonColor: '#991b1b',
                });
            } else {
                document.querySelector('[data-hero-alert-fallback]')?.classList.remove('sr-only');
            }
        @endif

        document.querySelectorAll('[data-hero-delete-form]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const slideTitle = form.dataset.heroTitle;

                if (!window.Swal) {
                    if (!window.confirm(`Hapus slide "${slideTitle}"?`)) {
                        event.preventDefault();
                    }

                    return;
                }

                event.preventDefault();

                window.Swal.fire({
                    title: 'Hapus slide?',
                    text: `Slide "${slideTitle}" akan dihapus.`,
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
