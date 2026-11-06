<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;

    protected string $clientKey;

    protected bool $isProduction;

    public function __construct(
        ?string $serverKey = null,
        ?string $clientKey = null,
        ?bool $isProduction = null,
    ) {
        $this->serverKey = $serverKey ?? (string) config('services.midtrans.server_key', '');
        $this->clientKey = $clientKey ?? (string) config('services.midtrans.client_key', '');
        $this->isProduction = $isProduction ?? (bool) config('services.midtrans.is_production', false);
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function isConfigured(): bool
    {
        return $this->serverKey !== '' && $this->clientKey !== '';
    }

    private function hasServerKey(): bool
    {
        return $this->serverKey !== '';
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function getSnapApiUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function getStatusApiUrl(string $orderNumber): string
    {
        $base = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        return "{$base}/{$orderNumber}/status";
    }

    public function getCancelApiUrl(string $orderNumber): string
    {
        $base = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        return "{$base}/{$orderNumber}/cancel";
    }

    /**
     * Get existing Snap token or generate a new one from Midtrans.
     */
    public function getOrCreateSnapToken(Order $order): ?string
    {
        if ($order->status !== OrderStatus::Pending) {
            return null;
        }

        if (! $this->isConfigured()) {
            Log::error('Midtrans credentials are not configured', [
                'order' => $order->order_number,
            ]);

            return null;
        }

        if (! empty($order->snap_token)) {
            return $order->snap_token;
        }

        $items = $order->relationLoaded('items') ? $order->items : $order->items()->get();

        $itemDetails = $items->map(function ($item): array {
            return [
                'id' => (string) ($item->product_id ?? $item->id),
                'price' => (int) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'name' => mb_substr($item->product_name, 0, 50),
            ];
        })->values()->all();

        if ((int) $order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => mb_substr('Ongkos kirim '.$order->shipping_label, 0, 50),
            ];
        }

        $itemsTotal = array_sum(array_map(fn (array $i): int => (int) $i['price'] * (int) $i['quantity'], $itemDetails));
        $grossAmount = (int) $order->total;

        if ($itemsTotal !== $grossAmount || empty($itemDetails)) {
            Log::error('Midtrans item total does not match order total', [
                'order' => $order->order_number,
            ]);

            return null;
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $order->recipient_name,
                'phone' => $order->recipient_phone,
                'email' => $order->user?->email,
                'shipping_address' => [
                    'first_name' => $order->recipient_name,
                    'phone' => $order->recipient_phone,
                    'address' => $order->full_shipping_address,
                ],
            ],
            'item_details' => $itemDetails,
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->connectTimeout(3)
                ->timeout(10)
                ->acceptJson()
                ->post($this->getSnapApiUrl(), $payload);

            if (! $response->successful()) {
                Log::error('Midtrans Snap request failed', [
                    'order' => $order->order_number,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $token = $response->json('token');
            if (! is_string($token) || $token === '') {
                Log::error('Midtrans Snap response did not contain a token', [
                    'order' => $order->order_number,
                ]);

                return null;
            }

            $order->update(['snap_token' => $token]);

            return $token;
        } catch (ConnectionException $exception) {
            Log::error('Midtrans Snap request could not connect', [
                'order' => $order->order_number,
                'exception' => $exception::class,
            ]);
        }

        return null;
    }

    /**
     * Verify Midtrans notification signature key.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        if (! $this->hasServerKey()
            || $orderId === ''
            || ! ctype_digit($statusCode)
            || $grossAmount === ''
            || preg_match('/\A[a-f0-9]{128}\z/i', $signatureKey) !== 1) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }

    /**
     * Handle incoming notification webhook from Midtrans.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handleNotification(array $payload, OrderService $orderService): bool
    {
        $orderId = $this->payloadString($payload, 'order_id');
        $statusCode = $this->payloadString($payload, 'status_code');
        $grossAmount = $this->payloadString($payload, 'gross_amount');
        $signatureKey = $this->payloadString($payload, 'signature_key');

        if (! $this->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning('Midtrans signature verification failed', [
                'order_id' => $orderId,
            ]);

            return false;
        }

        $order = Order::query()->where('order_number', $orderId)->first();
        if (! $order) {
            Log::warning('Midtrans order not found', ['order_id' => $orderId]);

            return false;
        }

        if (! $this->grossAmountMatchesOrder($grossAmount, $order)) {
            Log::warning('Midtrans notification amount does not match order total', [
                'order_id' => $orderId,
            ]);

            return false;
        }

        $transactionStatus = $this->payloadString($payload, 'transaction_status');
        $fraudStatus = $this->payloadString($payload, 'fraud_status');
        $paymentType = $this->payloadString($payload, 'payment_type') ?: 'midtrans';
        $transactionId = $this->payloadString($payload, 'transaction_id') ?: $paymentType;
        $reference = "midtrans:{$paymentType}:{$transactionId}";

        $this->applyTransactionStatus($order, $transactionStatus, $fraudStatus, $reference, $orderService);

        return true;
    }

    /**
     * Apply transaction status to the order.
     */
    public function applyTransactionStatus(
        Order $order,
        string $transactionStatus,
        string $fraudStatus,
        string $reference,
        OrderService $orderService,
    ): bool {
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $orderService->markPaid($order, $reference);

                return true;
            }

            if ($fraudStatus === 'challenge') {
                return true;
            }
        }

        if ($transactionStatus === 'settlement') {
            $orderService->markPaid($order, $reference);

            return true;
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
            $orderService->markFailed($order);

            return true;
        }

        return true;
    }

    /**
     * Sync order status directly from Midtrans status API.
     */
    public function syncStatus(Order $order, OrderService $orderService): Order
    {
        if ($order->status !== OrderStatus::Pending) {
            return $order;
        }

        if (! $this->hasServerKey()) {
            Log::error('Midtrans server key is not configured', [
                'order' => $order->order_number,
            ]);

            return $order;
        }

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->connectTimeout(3)
                ->timeout(10)
                ->acceptJson()
                ->get($this->getStatusApiUrl($order->order_number));

            if ($response->successful()) {
                /** @var array<string, mixed> $payload */
                $payload = $response->json();
                if (! is_array($payload)) {
                    Log::warning('Midtrans status response was not a JSON object', [
                        'order_id' => $order->order_number,
                    ]);

                    return $order->fresh();
                }

                $remoteOrderId = $this->payloadString($payload, 'order_id');
                $grossAmount = $this->payloadString($payload, 'gross_amount');

                if (! hash_equals($order->order_number, $remoteOrderId)
                    || ! $this->grossAmountMatchesOrder($grossAmount, $order)) {
                    Log::warning('Midtrans status response does not match order', [
                        'order_id' => $order->order_number,
                    ]);

                    return $order->fresh();
                }

                $transactionStatus = $this->payloadString($payload, 'transaction_status');
                $fraudStatus = $this->payloadString($payload, 'fraud_status');
                $paymentType = $this->payloadString($payload, 'payment_type') ?: 'midtrans';
                $transactionId = $this->payloadString($payload, 'transaction_id') ?: $paymentType;
                $reference = "midtrans:{$paymentType}:{$transactionId}";

                $this->applyTransactionStatus($order, $transactionStatus, $fraudStatus, $reference, $orderService);
            } else {
                Log::warning('Midtrans status request failed', [
                    'order' => $order->order_number,
                    'status' => $response->status(),
                ]);
            }
        } catch (ConnectionException $exception) {
            Log::error('Midtrans status request could not connect', [
                'order' => $order->order_number,
                'exception' => $exception::class,
            ]);
        }

        return $order->fresh();
    }

    /**
     * Request cancellation and only release reserved stock after Midtrans confirms a terminal status.
     */
    public function cancelTransaction(Order $order, OrderService $orderService): Order
    {
        if ($order->status !== OrderStatus::Pending) {
            return $order->fresh();
        }

        if (! $this->hasServerKey()) {
            Log::error('Midtrans server key is not configured', [
                'order' => $order->order_number,
            ]);

            return $order->fresh();
        }

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->connectTimeout(3)
                ->timeout(10)
                ->acceptJson()
                ->post($this->getCancelApiUrl($order->order_number));

            if (! $response->successful()) {
                Log::warning('Midtrans cancel request failed', [
                    'order' => $order->order_number,
                    'status' => $response->status(),
                ]);
            }
        } catch (ConnectionException $exception) {
            Log::error('Midtrans cancel request could not connect', [
                'order' => $order->order_number,
                'exception' => $exception::class,
            ]);
        }

        return $this->syncStatus($order, $orderService);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function payloadString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        return is_string($value) || is_int($value) || is_float($value)
            ? (string) $value
            : '';
    }

    private function grossAmountMatchesOrder(string $grossAmount, Order $order): bool
    {
        if (preg_match('/\A\d+(?:\.0{1,2})?\z/', $grossAmount) !== 1) {
            return false;
        }

        $integerAmount = explode('.', $grossAmount, 2)[0];
        $normalizedAmount = ltrim($integerAmount, '0') ?: '0';

        return hash_equals((string) $order->total, $normalizedAmount);
    }
}
