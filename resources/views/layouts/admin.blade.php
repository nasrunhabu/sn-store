<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#450a0a">

        <title>@yield('title', 'Dashboard') | SN Store Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <div x-data="{ sidebarOpen: false }">
            <button
                type="button"
                x-cloak
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
                aria-label="Tutup navigasi"
            ></button>

            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-red-950 text-white shadow-2xl shadow-red-950/20 transition-transform duration-300 lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                aria-label="Navigasi admin"
            >
                <div class="flex h-20 shrink-0 items-center justify-between border-b border-white/10 px-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="SN Store — buka etalase">
                        <span class="flex size-11 items-center justify-center rounded-2xl bg-white text-sm font-extrabold tracking-tight text-red-800 shadow-lg shadow-black/10">SN</span>
                        <span>
                            <span class="block text-lg font-extrabold tracking-tight">sn<span class="text-red-300">-store</span></span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.2em] text-red-200/70">Admin panel</span>
                        </span>
                    </a>
                    <button type="button" @click="sidebarOpen = false" class="flex size-9 items-center justify-center rounded-xl text-red-100 transition hover:bg-white/10 lg:hidden" aria-label="Tutup navigasi">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>
                        </svg>
                    </button>
                </div>

                <nav class="flex-1 space-y-8 overflow-y-auto px-4 py-7" aria-label="Menu utama">
                    <div>
                        <p class="px-3 text-[10px] font-extrabold uppercase tracking-[0.2em] text-red-200/50">Workspace</p>
                        <ul class="mt-3 space-y-1.5">
                            <li>
                                <a href="{{ route('dashboard') }}" aria-current="page" @click="sidebarOpen = false" class="flex items-center gap-3 rounded-xl bg-white px-3.5 py-3 text-sm font-bold text-red-900 shadow-lg shadow-black/10">
                                    <svg class="size-5 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <rect x="3" y="3" width="8" height="8" rx="2"/>
                                        <rect x="13" y="3" width="8" height="5" rx="2"/>
                                        <rect x="13" y="10" width="8" height="11" rx="2"/>
                                        <rect x="3" y="13" width="8" height="8" rx="2"/>
                                    </svg>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <span aria-disabled="true" class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold text-red-100/65">
                                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.8 7.5 4.4 7.5-4.4M12 12.2V21"/>
                                    </svg>
                                    <span class="flex-1">Kategori</span>
                                    <span class="rounded-md bg-white/10 px-1.5 py-1 text-[9px] font-bold uppercase tracking-wider text-red-100/60">Segera</span>
                                </span>
                            </li>
                            <li>
                                <span aria-disabled="true" class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold text-red-100/65">
                                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4 7 8-4 8 4-8 4-8-4Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 12l8 4 8-4M4 17l8 4 8-4"/>
                                    </svg>
                                    <span class="flex-1">Produk</span>
                                    <span class="rounded-md bg-white/10 px-1.5 py-1 text-[9px] font-bold uppercase tracking-wider text-red-100/60">Segera</span>
                                </span>
                            </li>
                            <li>
                                <span aria-disabled="true" class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold text-red-100/65">
                                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6.5h18v11H3z"/>
                                        <path stroke-linecap="round" d="M7 10h4m-4 4h10"/>
                                    </svg>
                                    <span class="flex-1">Transaksi</span>
                                    <span class="rounded-md bg-white/10 px-1.5 py-1 text-[9px] font-bold uppercase tracking-wider text-red-100/60">Segera</span>
                                </span>
                            </li>
                            <li>
                                <span aria-disabled="true" class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-semibold text-red-100/65">
                                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                        <circle cx="9" cy="8" r="3.5"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 20a6.5 6.5 0 0 1 13 0m2-9.5a3.5 3.5 0 1 0 0-7m1 10a6.5 6.5 0 0 1 3 5.5"/>
                                    </svg>
                                    <span class="flex-1">Pengguna</span>
                                    <span class="rounded-md bg-white/10 px-1.5 py-1 text-[9px] font-bold uppercase tracking-wider text-red-100/60">Segera</span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </nav>

                <div class="shrink-0 border-t border-white/10 p-4">
                    <a href="{{ route('home') }}" class="group flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.06] p-3.5 transition hover:border-white/20 hover:bg-white/10">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-500/20 text-red-200">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"/>
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-xs font-bold text-white">Lihat etalase</span>
                            <span class="mt-1 block text-[11px] text-red-100/60">Kunjungi toko publik</span>
                        </span>
                        <svg class="size-4 text-red-100/50 transition group-hover:translate-x-0.5 group-hover:text-white" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-sm font-semibold text-red-100/70 transition hover:bg-white/10 hover:text-white">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                            </svg>
                            Keluar dari akun
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-h-screen lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl">
                    <div class="flex h-20 items-center justify-between gap-4 px-4 sm:px-6 lg:px-10">
                        <div class="flex min-w-0 items-center gap-3">
                            <button type="button" @click="sidebarOpen = true" class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-red-200 hover:text-red-700 lg:hidden" aria-label="Buka navigasi">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                                </svg>
                            </button>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-slate-900">@yield('page_title', 'Dashboard')</p>
                                <p class="mt-0.5 hidden text-xs font-medium text-slate-400 sm:block">SN Store <span class="px-1 text-slate-300">/</span> Panel admin</p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-3">
                            <a href="{{ route('home') }}" class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-600 shadow-sm transition hover:border-red-200 hover:text-red-700 sm:inline-flex">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"/>
                                </svg>
                                Lihat toko
                            </a>
                            <span class="hidden h-9 w-px bg-slate-200 sm:block" aria-hidden="true"></span>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-xl py-1 pl-1 pr-2 transition hover:bg-slate-50" aria-label="Buka profil {{ auth()->user()->name }}">
                                <span class="flex size-10 items-center justify-center rounded-xl bg-red-100 text-sm font-extrabold text-red-800">
                                    {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                                </span>
                                <span class="hidden min-w-0 text-left sm:block">
                                    <span class="block max-w-36 truncate text-xs font-bold text-slate-800">{{ auth()->user()->name }}</span>
                                    <span class="mt-0.5 block text-[11px] text-slate-400">Administrator</span>
                                </span>
                                <svg class="hidden size-4 text-slate-400 sm:block" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </header>

                <main class="px-4 py-7 sm:px-6 sm:py-9 lg:px-10">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
