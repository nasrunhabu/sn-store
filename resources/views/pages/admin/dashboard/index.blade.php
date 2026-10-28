@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="relative isolate overflow-hidden rounded-[2rem] bg-gradient-to-br from-red-950 via-red-900 to-red-800 px-6 py-8 text-white shadow-xl shadow-red-950/10 sm:px-9 sm:py-10 lg:px-11">
            <div class="absolute -right-16 -top-28 size-80 rounded-full border-[38px] border-white/[0.06] sm:size-[26rem]"></div>
            <div class="absolute -bottom-40 right-1/4 size-72 rounded-full bg-red-500/20 blur-3xl"></div>
            <div class="relative flex flex-col justify-between gap-8 lg:flex-row lg:items-center">
                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.18em] text-red-100 backdrop-blur sm:text-xs">
                        <span class="size-1.5 rounded-full bg-emerald-300 shadow-[0_0_10px_rgba(110,231,183,0.8)]"></span>
                        Ruang kerja administrator
                    </span>
                    <h1 class="mt-5 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Selamat datang, {{ auth()->user()->name }}.</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-red-100/80 sm:text-base sm:leading-7">Pantau etalase dan kelola operasional SN Store dari satu tempat. Semua yang Anda butuhkan untuk mulai bekerja ada di sini.</p>
                    <a href="{{ route('home') }}" class="mt-7 inline-flex items-center gap-2.5 rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-red-900 shadow-lg shadow-red-950/20 transition hover:-translate-y-0.5 hover:bg-red-50">
                        Buka etalase publik
                        <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                </div>

                <div class="relative flex w-full items-center gap-4 rounded-2xl border border-white/15 bg-white/[0.08] p-4 backdrop-blur sm:max-w-xs sm:p-5">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-400/15 text-emerald-200">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-red-100/70">Status etalase</p>
                        <p class="mt-1 text-sm font-extrabold text-white">Toko publik aktif</p>
                    </div>
                    <span class="ml-auto size-2 rounded-full bg-emerald-300"></span>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-900">
            Kategori, produk, transaksi, dan pengguna di panel ini menggunakan data demo lokal. Data asli di database tidak dibaca atau diubah.
        </section>

        <section aria-labelledby="overview-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="overview-heading" class="text-lg font-extrabold tracking-tight text-slate-900">Ringkasan pengelolaan</h2>
                    <p class="mt-1 text-sm text-slate-500">Status modul utama SN Store.</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">
                    <span class="size-1.5 rounded-full bg-emerald-500"></span>
                    Sistem siap
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('admin.categories.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-500">Kategori</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($categoryCount, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs font-bold text-red-700">Kelola kategori →</p>
                </a>
                <a href="{{ route('admin.products.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-500">Produk demo</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($productCount, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs font-bold text-red-700">Kelola produk →</p>
                </a>
                <a href="{{ route('admin.transactions.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-500">Transaksi demo</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($transactionCount, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs font-bold text-red-700">Kelola transaksi →</p>
                </a>
                <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md">
                    <p class="text-sm font-semibold text-slate-500">Pengguna demo</p>
                    <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ number_format($userCount, 0, ',', '.') }}</p>
                    <p class="mt-2 text-xs font-bold text-red-700">Lihat pengguna →</p>
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-3" aria-label="Informasi toko">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.02] sm:p-6 xl:col-span-2">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-extrabold tracking-tight text-slate-900">Aktivitas terbaru</h2>
                        <p class="mt-1 text-sm text-slate-500">Pantau perubahan dan aktivitas toko.</p>
                    </div>
                    <span class="flex size-10 items-center justify-center rounded-xl bg-slate-50 text-slate-500">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </span>
                </div>
                <div class="mt-6 flex flex-col items-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 px-5 py-8 text-center">
                    <span class="flex size-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm ring-1 ring-slate-100">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8m-8 5h8m-8 5h5M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/>
                        </svg>
                    </span>
                    <h3 class="mt-4 text-sm font-extrabold text-slate-800">Aktivitas akan muncul di sini</h3>
                    <p class="mt-1.5 max-w-sm text-xs leading-5 text-slate-500">Kategori tersimpan di database, sedangkan produk, transaksi, dan pengguna masih menggunakan data demo. Produk aktif tampil di etalase.</p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-900/[0.02] sm:p-6">
                <div>
                    <h2 class="text-base font-extrabold tracking-tight text-slate-900">Kondisi toko</h2>
                    <p class="mt-1 text-sm text-slate-500">Status layanan SN Store saat ini.</p>
                </div>
                <ul class="mt-5 space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                <circle cx="12" cy="12" r="9"/>
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-xs font-bold text-slate-800">Etalase publik</span>
                            <span class="mt-0.5 block text-[11px] text-slate-400">Dapat diakses pengunjung</span>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Aktif</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                                <circle cx="12" cy="12" r="9"/>
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-xs font-bold text-slate-800">Panel administrator</span>
                            <span class="mt-0.5 block text-[11px] text-slate-400">Autentikasi aktif</span>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Aktif</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" d="M12 7v5l3 2"/>
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-xs font-bold text-slate-800">Manajemen data</span>
                            <span class="mt-0.5 block text-[11px] text-slate-400">Menggunakan berkas demo lokal</span>
                        </span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Aktif</span>
                    </li>
                </ul>
                <a href="{{ route('profile.edit') }}" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700">
                    Kelola profil akun
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            </div>
        </section>
    </div>
@endsection
