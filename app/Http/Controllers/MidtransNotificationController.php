<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function __invoke(
        Request $request,
        MidtransService $midtransService,
        OrderService $orderService,
    ): JsonResponse {
        /** @var array<string, mixed> $payload */
        $payload = $request->all();

        $handled = $midtransService->handleNotification($payload, $orderService);

        if (! $handled) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification could not be processed or invalid signature.',
            ], 400);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Notification processed successfully.',
        ]);
    }
}
