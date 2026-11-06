@extends('layouts.admin')

@section('title', 'Detail transaksi')
@section('page_title', 'Detail transaksi')

@section('content')
    @php
        $badge = match ($order->status->value) {
            'paid' => 'bg-emerald-50 text-emerald-700',
            'failed' => 'bg-red-50 text-red-700',
            default => 'bg-amber-50 text-amber-700',
        };
    @endphp

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Transaksi #{{ $order->order_number }}</h1>
                <p class="mt-1 text-sm text-slate-500">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.transactions.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:text-red-800">Kembali</a>
                <a href="{{ route('admin.transactions.edit', $order) }}" class="rounded-xl bg-red-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-900">Edit status</a>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-slate-900">Pembayaran</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Status</dt>
                        <dd class="mt-1"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $badge }}">{{ $order->status->label() }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Referensi pembayaran</dt>
                        <dd class="break-all text-slate-700">{{ $order->payment_reference ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Dibayar pada</dt>
                        <dd class="text-slate-700">{{ $order->paid_at?->translatedFormat('d F Y, H:i') ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Gagal pada</dt>
                        <dd class="text-slate-700">{{ $order->failed_at?->translatedFormat('d F Y, H:i') ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-extrabold text-slate-900">Pelanggan &amp; pengiriman</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Akun</dt>
                        <dd class="text-slate-700">{{ $order->user?->name ?? '-' }} <span class="text-slate-500">{{ $order->user?->email }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Penerima</dt>
                        <dd class="font-semibold text-slate-900">{{ $order->recipient_name }} <span class="font-normal text-slate-500">({{ $order->recipient_phone }})</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Alamat</dt>
                        <dd class="leading-6 text-slate-700">{{ $order->full_shipping_address }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-extrabold text-slate-900">Produk dipesan</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] divide-y divide-slate-100 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3">Produk</th>
                            <th scope="col" class="px-5 py-3 text-right">Harga</th>
                            <th scope="col" class="px-5 py-3 text-right">Jumlah</th>
                            <th scope="col" class="px-5 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-slate-900">{{ $item->product_name }}</td>
                                <td class="px-5 py-3 text-right text-slate-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right text-slate-600">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if ($order->shipping_cost > 0)
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-right text-slate-600">Subtotal produk</td>
                                <td class="px-5 py-3 text-right text-slate-700">Rp {{ number_format($order->items_subtotal, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-right text-slate-600">Ongkos kirim ({{ $order->shipping_label }}@if ($order->shipping_etd), {{ $order->shipping_etd }}@endif, {{ number_format($order->shipping_weight) }} g)</td>
                                <td class="px-5 py-3 text-right text-slate-700">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr class="bg-slate-50">
                            <td colspan="3" class="px-5 py-3 text-right font-bold text-slate-700">Total</td>
                            <td class="px-5 py-3 text-right font-extrabold text-slate-900">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
@endsection
