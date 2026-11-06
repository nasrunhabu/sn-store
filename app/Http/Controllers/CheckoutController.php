<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderService;
use App\Services\RajaOngkirService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravolt\Indonesia\Models\Province;
use RuntimeException;

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
            'provinces' => Province::query()->orderBy('name')->get(['code', 'name']),
        ]);
    }

    public function store(
        CheckoutRequest $request,
        OrderService $orderService,
        MidtransService $midtransService,
        CartService $cartService,
        RajaOngkirService $rajaOngkir,
    ): RedirectResponse {
        $weight = $rajaOngkir->weightForQuantity($cartService->getQuantity($request->user()));
        $option = $this->resolveShippingOption($rajaOngkir, $request->validated('village_code'), $weight, $request->validated('shipping_option'));
        $order = $orderService->checkout($request->user(), [
            ...$request->shipping(),
            'shipping_courier' => $option['courier'],
            'shipping_service' => $option['service'],
            'shipping_etd' => $option['etd'],
            'shipping_weight' => $weight,
            'shipping_cost' => $option['cost'],
        ]);

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

    /**
     * Ongkos kirim selalu dihitung ulang di server; nilai dari browser tidak dipercaya.
     *
     * @return array{courier: string, service: string, etd: string, cost: int}
     */
    private function resolveShippingOption(RajaOngkirService $rajaOngkir, string $villageCode, int $weight, string $selectedKey): array
    {
        try {
            $options = $rajaOngkir->quoteForVillage($villageCode, $weight);
        } catch (RuntimeException $exception) {
            throw ValidationException::withMessages(['shipping_option' => $exception->getMessage()]);
        }

        $option = collect($options)->firstWhere('key', $selectedKey);

        if ($option === null) {
            throw ValidationException::withMessages([
                'shipping_option' => 'Layanan pengiriman yang dipilih tidak tersedia. Pilih ulang layanan pengiriman.',
            ]);
        }

        return $option;
    }
}
