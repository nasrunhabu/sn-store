<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request, OrderService $orderService): View
    {
        $orders = $orderService->getUserOrders($request->user());

        return view('pages.checkout.index', [
            'orders' => $orders,
        ]);
    }

    public function create(Request $request, CartService $cartService): View|RedirectResponse
    {
        $items = $cartService->getItems($request->user());

        if ($items->isEmpty()) {
            return to_route('cart.index')->with('status', 'Tambahkan produk sebelum melanjutkan checkout.');
        }

        return view('pages.checkout.create', [
            'items' => $items,
            'total' => $items->sum(fn ($item): int => $item->product->price * $item->quantity),
            'user' => $request->user(),
        ]);
    }

    public function store(
        Request $request,
        OrderService $orderService,
        MidtransService $midtransService,
    ): RedirectResponse {
        $shipping = $request->validate([
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'shipping_address' => ['required', 'string', 'max:2000'],
        ]);
        $order = $orderService->checkout($request->user(), $shipping);

        $midtransService->getOrCreateSnapToken($order);

        return to_route('orders.show', $order)->with('autoPay', true);
    }

    public function show(
        Request $request,
        Order $order,
        MidtransService $midtransService,
    ): View {
        abort_unless($order->user_id === $request->user()->getKey(), 404);

        $order->load('items');

        $snapToken = null;
        if ($order->status === OrderStatus::Pending) {
            $snapToken = $midtransService->getOrCreateSnapToken($order);
        }

        return view('pages.checkout.show', [
            'order' => $order,
            'snapToken' => $snapToken,
            'snapJsUrl' => $midtransService->getSnapJsUrl(),
            'clientKey' => $midtransService->getClientKey(),
            'gatewayConfigured' => $midtransService->isConfigured(),
            'autoPay' => session('autoPay', false) || $request->boolean('pay'),
        ]);
    }

    public function cancel(
        Request $request,
        Order $order,
        OrderService $orderService,
        MidtransService $midtransService,
    ): RedirectResponse {
        abort_unless($order->user_id === $request->user()->getKey(), 404);

        $updatedOrder = $midtransService->cancelTransaction($order, $orderService);

        $message = match ($updatedOrder->status) {
            OrderStatus::Paid => 'Pembayaran sudah berhasil dikonfirmasi. Pesanan tidak dibatalkan.',
            OrderStatus::Failed => 'Pesanan dibatalkan dan stok telah dikembalikan.',
            OrderStatus::Pending => 'Pembatalan belum dikonfirmasi Midtrans. Stok tetap ditahan sementara; silakan cek kembali.',
        };

        return to_route('orders.show', $order)->with('status', $message);
    }

    public function checkStatus(
        Request $request,
        Order $order,
        OrderService $orderService,
        MidtransService $midtransService,
    ): RedirectResponse {
        abort_unless($order->user_id === $request->user()->getKey(), 404);

        $updatedOrder = $midtransService->syncStatus($order, $orderService);

        $message = match ($updatedOrder->status) {
            OrderStatus::Paid => 'Pembayaran berhasil dikonfirmasi!',
            OrderStatus::Failed => 'Pesanan dibatalkan atau pembayaran kadaluarsa.',
            OrderStatus::Pending => 'Status pembayaran belum dapat dikonfirmasi. Silakan coba kembali.',
        };

        return to_route('orders.show', $order)->with('status', $message);
    }
}
