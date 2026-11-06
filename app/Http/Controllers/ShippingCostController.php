<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\RajaOngkirService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ShippingCostController extends Controller
{
    public function __invoke(Request $request, CartService $cartService, RajaOngkirService $rajaOngkir): JsonResponse
    {
        $validated = $request->validate([
            'village_code' => ['required', 'string', 'max:20'],
        ]);

        $quantity = $cartService->getQuantity($request->user());

        if ($quantity < 1) {
            return response()->json(['message' => 'Keranjang masih kosong.'], 422);
        }

        try {
            $weight = $rajaOngkir->weightForQuantity($quantity);

            return response()->json([
                'weight' => $weight,
                'options' => $rajaOngkir->quoteForVillage($validated['village_code'], $weight),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
