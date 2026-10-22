@extends('layouts.admin')

@section('title', 'Transaksi')
@section('page_title', 'Transaksi')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-900">
            Ini transaksi contoh untuk demo. Perubahan status hanya tersimpan di berkas data lokal, tidak memproses pembayaran atau mengubah database.
        </section>

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
                <h1 class="font-extrabold text-slate-900">Daftar transaksi demo</h1>
                <p class="mt-1 text-xs text-slate-500">{{ $transactions->count() }} transaksi contoh</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nomor</th>
                            <th scope="col" class="px-6 py-3">Pelanggan</th>
                            <th scope="col" class="px-6 py-3">Produk</th>
                            <th scope="col" class="px-6 py-3">Total</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3 text-right">Simpan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 font-bold text-slate-900">{{ $transaction['id'] }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $transaction['customer'] }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $transaction['product'] }}</td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-800">Rp {{ number_format($transaction['total'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <form id="transaction-{{ $transaction['id'] }}" method="POST" action="{{ route('admin.transactions.update', $transaction['id']) }}">
                                        @csrf
                                        @method('PUT')
                                        <label class="sr-only" for="status-{{ $transaction['id'] }}">Status transaksi {{ $transaction['id'] }}</label>
                                        <select id="status-{{ $transaction['id'] }}" name="status" class="rounded-lg border-slate-300 py-2 pl-3 pr-8 text-xs font-semibold text-slate-700 shadow-sm focus:border-red-500 focus:ring-red-500">
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status }}" @selected($transaction['status'] === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="submit" form="transaction-{{ $transaction['id'] }}" class="rounded-lg bg-red-800 px-3 py-2 text-xs font-bold text-white transition hover:bg-red-900">Simpan</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada transaksi demo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
