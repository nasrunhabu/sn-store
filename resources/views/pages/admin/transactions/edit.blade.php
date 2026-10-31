@extends('layouts.admin')

@section('title', 'Edit transaksi')
@section('page_title', 'Edit transaksi')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Edit transaksi</h1>
                <p class="mt-1 text-sm text-slate-500">Status otomatis berubah saat pembayaran Midtrans dikonfirmasi. Ubah manual hanya bila diperlukan.</p>
            </div>
            <a href="{{ route('admin.transactions.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-red-200 hover:text-red-800">Kembali</a>
        </div>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Nomor pesanan</dt>
                    <dd class="font-bold text-slate-900">#{{ $order->order_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Pelanggan</dt>
                    <dd class="font-semibold text-slate-900">{{ $order->recipient_name }} <span class="font-normal text-slate-500">({{ $order->recipient_phone }})</span></dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Total</dt>
                    <dd class="font-bold text-slate-900">Rp {{ number_format($order->total, 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Referensi pembayaran</dt>
                    <dd class="text-slate-700">{{ $order->payment_reference ?? '-' }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('admin.transactions.update', $order) }}" class="space-y-5 border-t border-slate-100 pt-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="status" class="mb-2 block text-sm font-bold text-slate-700">Status pesanan</label>
                    <select id="status" name="status" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $order->status->value) === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="rounded-xl bg-red-800 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-red-900">Simpan perubahan</button>
            </form>
        </section>
    </div>
@endsection
