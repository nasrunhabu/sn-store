@extends('layouts.admin')

@section('title', 'Transaksi')
@section('page_title', 'Transaksi')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('status'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h1 class="font-extrabold text-slate-900">Daftar transaksi</h1>
                <p class="mt-1 text-xs text-slate-500">{{ number_format($orders->total(), 0, ',', '.') }} transaksi tersimpan di database</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nomor pesanan</th>
                            <th scope="col" class="px-6 py-3">Pelanggan</th>
                            <th scope="col" class="px-6 py-3">Produk</th>
                            <th scope="col" class="px-6 py-3">Total</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($orders as $order)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="block font-bold text-slate-900">#{{ $order->order_number }}</span>
                                    <span class="block text-xs text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }} WIB</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="block font-semibold text-slate-900">{{ $order->recipient_name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $order->recipient_phone }}</span>
                                    @if ($order->user)
                                        <span class="block text-[11px] text-slate-400">{{ $order->user->email }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <ul class="space-y-1 text-xs">
                                        @foreach ($order->items as $item)
                                            <li>
                                                <span class="font-medium text-slate-800">{{ $item->product_name }}</span>
                                                <span class="text-slate-400">× {{ $item->quantity }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="font-bold text-slate-900">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                    @if ($order->payment_reference)
                                        <span class="block text-[10px] text-slate-400 truncate max-w-[140px]" title="{{ $order->payment_reference }}">{{ $order->payment_reference }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $badge = match ($order->status->value) {
                                            'paid' => 'bg-emerald-50 text-emerald-700',
                                            'failed' => 'bg-red-50 text-red-700',
                                            default => 'bg-amber-50 text-amber-700',
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $badge }}">{{ $order->status->label() }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <a href="{{ route('admin.transactions.show', $order) }}" class="mr-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-800">Detail</a>
                                    <a href="{{ route('admin.transactions.edit', $order) }}" class="rounded-lg bg-red-800 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-red-900">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada transaksi di database.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
