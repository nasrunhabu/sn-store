@extends('layouts.checkout')

@section('title', 'Checkout')

@section('content')
    <div class="mb-8">
        <a href="{{ route('cart.index') }}" class="text-sm font-bold text-red-700 hover:text-red-800">← Kembali ke keranjang</a>
        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-gray-950">Checkout</h1>
        <p class="mt-2 text-sm text-gray-500">Isi alamat pengiriman dan konfirmasi pembayaran untuk menyelesaikan pesanan.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        @csrf
        <div class="space-y-5">
            <section class="space-y-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-950">Informasi penerima</h2>
                    <p class="mt-1 text-sm text-gray-500">Pastikan nomor telepon dan alamat dapat digunakan untuk pengiriman.</p>
                </div>
                <div>
                    <label for="recipient_name" class="mb-2 block text-sm font-bold text-gray-700">Nama penerima</label>
                    <input id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $user->name) }}" required maxlength="120" autocomplete="name" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    @error('recipient_name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="recipient_phone" class="mb-2 block text-sm font-bold text-gray-700">Nomor telepon</label>
                    <input id="recipient_phone" name="recipient_phone" type="tel" value="{{ old('recipient_phone') }}" required maxlength="30" autocomplete="tel" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    @error('recipient_phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="shipping_address" class="mb-2 block text-sm font-bold text-gray-700">Alamat lengkap</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required maxlength="2000" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-950">Pilihan Metode Pembayaran</h2>
                    <p class="mt-1 text-sm text-gray-500">Pilih payment gateway untuk memproses transaksi Anda.</p>
                </div>

                <div class="space-y-3">
                    <label class="relative flex cursor-pointer items-start gap-3.5 rounded-2xl border-2 border-red-600 bg-red-50/30 p-4 transition hover:bg-red-50/50">
                        <input type="radio" name="payment_gateway" value="midtrans" checked class="mt-1 size-4 text-red-600 focus:ring-red-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-sm font-extrabold text-gray-950">Payment Gateway Midtrans</span>
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-800">Otomatis & Terverifikasi</span>
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600">
                                Pembayaran diproses secara instan dan aman. Setelah mengklik tombol buat pesanan, jendela pembayaran Midtrans Snap akan langsung terbuka untuk memilih metode pembayaran yang Anda inginkan.
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[10px] font-bold text-gray-700">
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">QRIS</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">GoPay</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">ShopeePay</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">BCA VA</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">BNI VA</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">BRI VA</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">Mandiri Bill</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">Permata</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">Kartu Kredit</span>
                            </div>
                        </div>
                    </label>
                </div>
            </section>
        </div>

        <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-extrabold text-gray-950">Ringkasan pesanan</h2>
            <div class="mt-4 space-y-3.5 border-b border-gray-100 pb-4">
                @foreach ($items as $item)
                    <div class="flex items-center gap-3">
                        @if ($item->product->image_url)
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->image_alt }}" class="size-12 shrink-0 rounded-xl bg-gray-50 object-cover">
                        @else
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-[10px] font-bold text-gray-400">
                                SN
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-gray-900">{{ $item->product->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ $item->quantity }} × Rp {{ number_format($item->product->price, 0, ',', '.') }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-gray-900">Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Subtotal produk</span>
                    <span class="font-bold text-gray-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Ongkos kirim</span>
                    <span class="font-bold text-emerald-700">Gratis (Rp 0)</span>
                </div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                    <span class="text-sm font-bold text-gray-700">Total pembayaran</span>
                    <span class="text-lg font-extrabold text-gray-950">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-900">
                <div class="flex items-center gap-2 font-bold">
                    <svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    <span>Pembayaran aman dengan Midtrans</span>
                </div>
                <p class="mt-1 text-emerald-800">Didukung enkripsi standar industri perbankan.</p>
            </div>
            <button type="submit" class="mt-5 flex w-full items-center justify-center rounded-xl bg-red-700 px-5 py-3.5 text-sm font-extrabold text-white transition hover:bg-red-800">Buat pesanan</button>
            <p class="mt-3 text-center text-xs leading-5 text-gray-500">Stok akan direservasi saat pesanan dibuat dan dikembalikan jika pesanan dibatalkan atau pembayaran gagal.</p>
        </aside>
    </form>
@endsection
