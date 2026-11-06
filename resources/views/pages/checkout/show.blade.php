@extends('layouts.checkout')

@section('title', 'Pesanan '.$order->order_number)

@section('content')
    <div class="mx-auto max-w-2xl">
        <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm sm:p-9">
            @if (session('status'))
                <div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
            @endif

            @if ($order->status->value === 'pending')
                <span class="flex size-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                </span>
                <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-gray-950">Pesanan menunggu pembayaran</h1>
                <p class="mt-2 text-sm leading-6 text-gray-500">Silakan selesaikan pembayaran melalui Midtrans. Jendela pilihan metode pembayaran akan terbuka atau klik tombol di bawah.</p>

                <div class="mt-6 space-y-3">
                    @if (! empty($snapToken))
                        <button id="pay-button" type="button" class="flex w-full items-center justify-center gap-2.5 rounded-xl bg-red-700 px-6 py-4 text-base font-extrabold text-white shadow-lg shadow-red-700/20 transition hover:bg-red-800">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2"/>
                                <line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                            Bayar Sekarang via Midtrans
                        </button>

                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3 text-center text-xs text-gray-500">
                            <span class="font-bold text-gray-700">Metode tersedia:</span> QRIS, GoPay, ShopeePay, Virtual Account (BCA, Mandiri, BNI, BRI, Permata), dan Kartu Kredit.
                        </div>
                    @else
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
                            @if (! $gatewayConfigured)
                                <p class="font-bold">Pembayaran belum dikonfigurasi.</p>
                                <p class="mt-1 text-amber-800">Silakan hubungi admin untuk mengaktifkan pembayaran.</p>
                            @else
                                <p class="font-bold">Token pembayaran gagal disiapkan.</p>
                                <p class="mt-1 text-amber-800">Periksa koneksi lalu muat ulang halaman. Stok tetap direservasi selama pesanan menunggu pembayaran.</p>
                                <a href="{{ url()->current() }}" class="mt-2.5 inline-flex items-center gap-1.5 rounded-lg bg-amber-800 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-900">
                                    Coba lagi
                                </a>
                            @endif
                        </div>
                    @endif

                    <form id="sync-status-form" method="POST" action="{{ route('orders.check-status', $order) }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-xs font-bold text-gray-700 shadow-sm transition hover:bg-gray-50">
                            <svg class="size-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Cek Status Pembayaran
                        </button>
                    </form>
                </div>
            @elseif ($order->status->value === 'paid')
                <span class="flex size-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                </span>
                <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-gray-950">Pembayaran berhasil</h1>
                <p class="mt-2 text-sm leading-6 text-gray-500">Pembayaran untuk pesanan ini telah dikonfirmasi oleh Midtrans.</p>
            @else
                <span class="flex size-14 items-center justify-center rounded-2xl bg-red-50 text-red-700">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17"/></svg>
                </span>
                <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-gray-950">Pesanan dibatalkan</h1>
                <p class="mt-2 text-sm leading-6 text-gray-500">Pesanan tidak dibayar. Stok yang sebelumnya direservasi telah dikembalikan.</p>
            @endif

            <div class="mt-7 rounded-2xl bg-gray-50 p-4 sm:p-5">
                <div class="flex flex-wrap justify-between gap-2 text-sm">
                    <span class="text-gray-500">Nomor pesanan</span>
                    <span class="font-extrabold text-gray-900">{{ $order->order_number }}</span>
                </div>
                <div class="mt-3 flex flex-wrap justify-between gap-2 text-sm">
                    <span class="text-gray-500">Status</span>
                    <span class="font-bold capitalize text-gray-900">{{ $order->status->label() }}</span>
                </div>
                @if ($order->payment_reference)
                    <div class="mt-3 flex flex-wrap justify-between gap-2 text-sm">
                        <span class="text-gray-500">Referensi pembayaran</span>
                        <span class="font-bold text-gray-900">{{ $order->payment_reference }}</span>
                    </div>
                @endif
                <div class="mt-3 flex flex-wrap justify-between gap-2 text-sm">
                    <span class="text-gray-500">Waktu pemesanan</span>
                    <span class="text-gray-700">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-gray-100 p-4 sm:p-5">
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-gray-400">Alamat Pengiriman</h2>
                <p class="mt-2 text-sm font-bold text-gray-900">{{ $order->recipient_name }} <span class="font-normal text-gray-500">({{ $order->recipient_phone }})</span></p>
                <p class="mt-1 text-xs leading-5 text-gray-600">{{ $order->full_shipping_address }}</p>
            </div>

            <h2 class="mt-7 text-sm font-extrabold text-gray-900">Detail produk</h2>
            <div class="mt-3 space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex items-center gap-3">
                        @if ($item->product?->image_url)
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="size-12 shrink-0 rounded-xl bg-gray-50 object-cover">
                        @else
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-[10px] font-bold text-gray-400">
                                SN
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-[11px] text-gray-500">{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>

            @if ($order->shipping_cost > 0)
                <div class="mt-4 space-y-1.5 border-t border-gray-100 pt-4 text-sm text-gray-600">
                    <div class="flex justify-between gap-4">
                        <span>Subtotal produk</span>
                        <span class="font-semibold">Rp {{ number_format($order->items_subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>Ongkos kirim ({{ $order->shipping_label }}@if ($order->shipping_etd), {{ $order->shipping_etd }}@endif)</span>
                        <span class="font-semibold">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif

            <div class="mt-5 flex justify-between gap-4 border-t border-gray-100 pt-4">
                <span class="font-bold text-gray-600">Total pesanan</span>
                <span class="text-lg font-extrabold text-gray-950">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                @if ($order->status->value === 'pending')
                    <form method="POST" action="{{ route('orders.cancel', $order) }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border border-red-200 px-5 py-3 text-sm font-bold text-red-700 transition hover:bg-red-50">Batalkan pesanan</button>
                    </form>
                @endif
                <a href="{{ route('orders.index') }}" class="flex flex-1 items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50">Pesanan saya</a>
                <a href="{{ route('products.index') }}" class="flex flex-1 items-center justify-center rounded-xl bg-red-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-800">Belanja lagi</a>
            </div>
        </section>
    </div>

    @if ($order->status->value === 'pending' && ! empty($snapToken))
        <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const payButton = document.getElementById('pay-button');

                function openSnap() {
                    if (window.snap) {
                        window.snap.pay(@js($snapToken), {
                            onSuccess: function () {
                                document.getElementById('sync-status-form').submit();
                            },
                            onPending: function () {
                                document.getElementById('sync-status-form').submit();
                            },
                            onError: function (result) {
                                alert('Pembayaran gagal atau dibatalkan.');
                                window.location.reload();
                            },
                            onClose: function () {}
                        });
                    } else {
                        alert('Layanan pembayaran belum dapat dimuat. Silakan muat ulang halaman.');
                    }
                }

                if (payButton) {
                    payButton.addEventListener('click', openSnap);
                }

                @if (! empty($autoPay))
                    setTimeout(openSnap, 600);
                @endif
            });
        </script>
    @endif
@endsection
