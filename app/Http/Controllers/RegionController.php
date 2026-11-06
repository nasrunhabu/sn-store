<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class RegionController extends Controller
{
    public function cities(string $province): JsonResponse
    {
        return response()->json(
            City::query()->where('province_code', $province)->orderBy('name')->get(['code', 'name'])
        );
    }

    public function districts(string $city): JsonResponse
    {
        return response()->json(
            District::query()->where('city_code', $city)->orderBy('name')->get(['code', 'name'])
        );
    }

    public function villages(string $district): JsonResponse
    {
        return response()->json(
            Village::query()->where('district_code', $district)->orderBy('name')->get(['code', 'name', 'meta'])
                ->map(fn (Village $village): array => [
                    'code' => $village->code,
                    'name' => $village->name,
                    'postal_code' => $village->meta['pos'] ?? null,
                ])
                ->values()
        );
    }
}
