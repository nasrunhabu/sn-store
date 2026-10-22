<?php

namespace App\Http\Controllers\Admin;

use App\DemoStore;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    private const STATUSES = [
        'Menunggu pembayaran',
        'Diproses',
        'Dikirim',
        'Selesai',
        'Dibatalkan',
    ];

    public function index(DemoStore $store): View
    {
        $transactions = collect($store->all()['transactions'])->sortByDesc('id')->values();

        return view('pages.admin.transactions.index', [
            'transactions' => $transactions,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, string $transaction, DemoStore $store): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $store->update(function (array $data) use ($transaction, $validated): array {
            $index = collect($data['transactions'])->search(fn (array $item): bool => $item['id'] === $transaction);

            abort_if($index === false, 404);
            $data['transactions'][$index]['status'] = $validated['status'];

            return $data;
        });

        return to_route('admin.transactions.index')->with('status', 'Status transaksi contoh berhasil diperbarui.');
    }
}
