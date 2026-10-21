<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="@yield('description', 'Temukan gadget dan elektronik pilihan dengan harga terbaik di SN Store.')">
        <meta name="theme-color" content="#b91c1c">

        <title>@yield('title', 'Belanja Elektronik') | SN Store</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-sans text-gray-900 antialiased">
        <div class="bg-red-700 text-white">
            <div class="mx-auto flex max-w-7xl items-center justify-center gap-2 px-4 py-2 text-center text-xs font-medium sm:text-sm">
                <svg class="size-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2 3.75A1.75 1.75 0 0 1 3.75 2h8.5A1.75 1.75 0 0 1 14 3.75V5h1.21c.59 0 1.15.28 1.5.75l1.79 2.39c.33.44.5.97.5 1.52v3.59A1.75 1.75 0 0 1 17.25 15h-.4a2.75 2.75 0 0 1-5.2 0H8.35a2.75 2.75 0 0 1-5.2 0h-.4A1.75 1.75 0 0 1 1 13.25v-7.5A1.75 1.75 0 0 1 2.75 4H2v-.25ZM14 6.5v3h3.5v-.25a1 1 0 0 0-.2-.6L15.5 6.5H14Zm-8.25 9a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Zm8.5 0a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Z"/>
                </svg>
                Gratis ongkir untuk pesanan pertama — gunakan kode <span class="font-extrabold tracking-wide">SNSTORE</span>
            </div>
        </div>

        <header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 shadow-sm shadow-gray-900/[0.02] backdrop-blur" x-data="{ mobileMenuOpen: false }">
            <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="SN Store — Beranda">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-red-700 text-sm font-extrabold tracking-tight text-white shadow-lg shadow-red-700/20">SN</span>
                    <span class="text-xl font-extrabold tracking-tight text-gray-950">sn<span class="text-red-700">-store</span></span>
                </a>

                <nav class="hidden items-center gap-7 text-sm font-semibold text-gray-600 xl:flex" aria-label="Navigasi utama">
                    <a href="{{ route('home') }}" class="text-red-700 transition hover:text-red-800">Beranda</a>
                    <a href="{{ route('home') }}#produk" class="transition hover:text-red-700">Produk</a>
                    <details class="group relative">
                        <summary class="flex cursor-pointer list-none items-center gap-1.5 transition hover:text-red-700">
                            Kategori
                            <svg class="size-4 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                            </svg>
                        </summary>
                        <div class="absolute left-0 top-full z-50 mt-3 w-52 rounded-2xl border border-gray-100 bg-white p-2 shadow-xl shadow-gray-900/10">
                            @foreach ($categories as $category)
                                <a href="{{ route('home', ['category' => $category]) }}#produk" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-red-50 hover:text-red-700">{{ $category }}</a>
                            @endforeach
                        </div>
                    </details>
                </nav>

                <form action="{{ route('home') }}" method="GET" role="search" class="ml-auto hidden w-full max-w-sm items-center rounded-full border border-gray-200 bg-gray-50 px-4 py-2.5 focus-within:border-red-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-red-50 lg:flex xl:ml-0">
                    <label for="desktop-search" class="sr-only">Cari produk</label>
                    <input id="desktop-search" type="search" name="search" value="{{ $searchTerm }}" placeholder="Cari gadget impianmu..." class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-700 placeholder:text-gray-400 focus:ring-0">
                    <button type="submit" class="ml-2 text-gray-400 transition hover:text-red-700" aria-label="Cari">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" d="m20 20-4-4"/>
                        </svg>
                    </button>
                </form>

                <div class="ml-auto flex shrink-0 items-center gap-2 sm:gap-3 xl:ml-0">
                    @auth
                        <details class="group relative hidden sm:block">
                            <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-full border border-gray-200 bg-white px-3.5 py-2.5 text-sm font-bold text-gray-700 transition hover:border-red-200 hover:text-red-700 [&::-webkit-details-marker]:hidden">
                                <svg class="size-4 text-red-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
                                </svg>
                                Akun saya
                                <svg class="size-4 text-gray-400 transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                                </svg>
                            </summary>
                            <div class="absolute right-0 top-full z-50 mt-2 w-56 rounded-2xl border border-gray-100 bg-white p-2 shadow-xl shadow-gray-900/10">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-red-50 hover:text-red-700">
                                    <svg class="size-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <circle cx="12" cy="8" r="3.5"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
                                    </svg>
                                    Pengaturan profil
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-gray-700 transition hover:bg-red-50 hover:text-red-700">
                                        <svg class="size-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
                                        </svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </details>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('dashboard') }}" class="hidden rounded-full bg-red-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-red-800 sm:inline-flex">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-full border border-gray-200 px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:border-red-200 hover:text-red-700 sm:inline-flex">Masuk</a>
                    @endauth

                    <button type="button" class="relative flex size-10 items-center justify-center rounded-full bg-gray-50 text-gray-700 transition hover:bg-red-50 hover:text-red-700" aria-label="Keranjang belanja, 0 item">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.1 10.1a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 1.9-1.4L21 8H6"/>
                            <circle cx="10" cy="20" r="1"/>
                            <circle cx="18" cy="20" r="1"/>
                        </svg>
                        <span class="absolute -right-1 -top-1 flex size-5 items-center justify-center rounded-full bg-red-700 text-[10px] font-bold text-white">0</span>
                    </button>

                    <button type="button" class="flex size-10 items-center justify-center rounded-full text-gray-700 transition hover:bg-gray-100 xl:hidden" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen.toString()" aria-controls="mobile-navigation" aria-label="Buka menu">
                        <svg x-show="!mobileMenuOpen" class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8 lg:hidden">
                <form action="{{ route('home') }}" method="GET" role="search" class="flex items-center rounded-full border border-gray-200 bg-gray-50 px-4 py-2.5 focus-within:border-red-300 focus-within:bg-white focus-within:ring-4 focus-within:ring-red-50">
                    <label for="mobile-search" class="sr-only">Cari produk</label>
                    <input id="mobile-search" type="search" name="search" value="{{ $searchTerm }}" placeholder="Cari gadget impianmu..." class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-700 placeholder:text-gray-400 focus:ring-0">
                    <button type="submit" class="ml-2 text-gray-400 transition hover:text-red-700" aria-label="Cari">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path stroke-linecap="round" d="m20 20-4-4"/>
                        </svg>
                    </button>
                </form>
            </div>

            <nav id="mobile-navigation" x-show="mobileMenuOpen" x-cloak x-transition.origin.top aria-label="Navigasi seluler" class="border-t border-gray-100 bg-white px-4 py-3 xl:hidden">
                <div class="mx-auto flex max-w-7xl flex-col gap-1 sm:px-2">
                    <a href="{{ route('home') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-50">Beranda</a>
                    <a href="{{ route('home') }}#produk" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Produk</a>
                    <span class="px-3 pb-1 pt-3 text-xs font-bold uppercase tracking-wider text-gray-400">Kategori</span>
                    @foreach ($categories as $category)
                        <a href="{{ route('home', ['category' => $category]) }}#produk" class="rounded-xl px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-red-50 hover:text-red-700">{{ $category }}</a>
                    @endforeach
                    @auth
                        <span class="px-3 pb-1 pt-3 text-xs font-bold uppercase tracking-wider text-gray-400">Akun</span>
                        <a href="{{ route('profile.edit') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-red-50 hover:text-red-700">Pengaturan profil</a>
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('dashboard') }}" class="mt-2 rounded-xl bg-red-700 px-3 py-2.5 text-center text-sm font-bold text-white hover:bg-red-800">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="mt-1">
                            @csrf
                            <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-gray-600 transition hover:bg-red-50 hover:text-red-700">Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="mt-2 rounded-xl bg-red-700 px-3 py-2.5 text-center text-sm font-bold text-white hover:bg-red-800 sm:hidden">Masuk</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="border-t border-gray-100 bg-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
                <div class="md:col-span-2 lg:col-span-2">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5" aria-label="SN Store — Beranda">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-red-700 text-sm font-extrabold tracking-tight text-white">SN</span>
                        <span class="text-xl font-extrabold tracking-tight text-gray-950">sn<span class="text-red-700">-store</span></span>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-7 text-gray-500">Tempat tepercaya untuk menemukan teknologi yang membuat hidup lebih mudah. Produk pilihan, harga bersahabat, dan layanan sepenuh hati.</p>
                    <div class="mt-5 flex items-center gap-3 text-sm text-gray-500">
                        <span class="flex size-9 items-center justify-center rounded-full bg-red-50 text-red-700">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h3l2 5-2 1.5a14 14 0 0 0 4.5 4.5L16 12l5 2v3a2 2 0 0 1-2 2A16 16 0 0 1 5 5a2 2 0 0 1 2-2Z"/>
                            </svg>
                        </span>
                        <a href="tel:+6281234567890" class="transition hover:text-red-700">+62 812-3456-7890</a>
                    </div>
                    <a href="mailto:halo@sn-store.id" class="mt-3 inline-block text-sm text-gray-500 transition hover:text-red-700">halo@sn-store.id</a>
                </div>

                <div>
                    <h2 class="text-sm font-extrabold text-gray-950">Jelajahi</h2>
                    <ul class="mt-4 space-y-3 text-sm text-gray-500">
                        <li><a href="{{ route('home') }}" class="transition hover:text-red-700">Beranda</a></li>
                        <li><a href="{{ route('home') }}#produk" class="transition hover:text-red-700">Semua produk</a></li>
                        <li><a href="{{ route('home', ['category' => 'Smartphone']) }}#produk" class="transition hover:text-red-700">Smartphone</a></li>
                        <li><a href="{{ route('home', ['category' => 'Audio']) }}#produk" class="transition hover:text-red-700">Audio</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-extrabold text-gray-950">Layanan pelanggan</h2>
                    <ul class="mt-4 space-y-3 text-sm text-gray-500">
                        <li><a href="mailto:halo@sn-store.id" class="transition hover:text-red-700">Hubungi kami</a></li>
                        <li><a href="{{ route('login') }}" class="transition hover:text-red-700">Akun saya</a></li>
                        <li><span>Senin–Sabtu, 09.00–17.00 WIB</span></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-100">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 text-xs text-gray-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span>© {{ date('Y') }} SN Store. Semua hak dilindungi.</span>
                    <span>Belanja nyaman, teknologi jadi mudah.</span>
                </div>
            </div>
        </footer>
    </body>
</html>