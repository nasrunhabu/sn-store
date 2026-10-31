<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Checkout') | SN Store</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="SN Store — Beranda">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-red-700 text-sm font-extrabold tracking-tight text-white">SN</span>
                    <span class="text-xl font-extrabold tracking-tight text-gray-950">sn<span class="text-red-700">-store</span></span>
                </a>
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="{{ route('orders.index') }}" class="text-xs font-bold text-gray-600 hover:text-red-700 sm:text-sm">Pesanan saya</a>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-800 sm:px-3">
                        <svg class="size-3.5 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                        </svg>
                        Checkout aman
                    </span>
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-12">
            @yield('content')
        </main>
        <footer class="border-t border-gray-200 bg-white">
            <p class="mx-auto max-w-5xl px-4 py-5 text-center text-xs text-gray-500 sm:px-6">© {{ date('Y') }} SN Store</p>
        </footer>
    </body>
</html>
