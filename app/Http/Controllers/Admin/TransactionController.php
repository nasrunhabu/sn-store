<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(OrderService $orderService): View
    {
        return view('pages.admin.transactions.index', [
            'orders' => $orderService->getOrders(),
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function show(Order $order): View
    {
        return view('pages.admin.transactions.show', [
            'order' => $order->load(['user', 'items']),
        ]);
    }

    public function edit(Order $order): View
    {
        return view('pages.admin.transactions.edit', [
            'order' => $order->load(['user', 'items']),
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function update(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(OrderStatus::class)],
        ]);

        $updated = $orderService->updateStatus($order, OrderStatus::from($validated['status']));

        if (! $updated) {
            return back()->withErrors([
                'status' => 'Status pesanan tidak dapat diubah dari status saat ini.',
            ]);
        }

        return to_route('admin.transactions.index')->with(
            'status',
            "Status pesanan #{$order->order_number} berhasil diperbarui."
        );
    }
}
