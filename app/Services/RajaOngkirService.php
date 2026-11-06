<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravolt\Indonesia\Models\Village;
use RuntimeException;

class RajaOngkirService
{
    public function isConfigured(): bool
    {
        return filled(config('services.rajaongkir.api_key'));
    }

    public function weightForQuantity(int $quantity): int
    {
        return max(1, $quantity) * max(1, (int) config('services.rajaongkir.item_weight'));
    }

    /**
     * Daftar layanan pengiriman dari Kota Makassar menuju desa/kelurahan tujuan.
     *
     * @return list<array{key: string, courier: string, courier_name: string, service: string, description: string, cost: int, etd: string}>
     *
     * @throws RuntimeException berisi pesan yang aman ditampilkan ke pengguna
     */
    public function quoteForVillage(string $villageCode, int $weight): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Layanan ongkos kirim belum dikonfigurasi.');
        }

        $village = Village::query()->with('district.city.province')->where('code', $villageCode)->first();

        if ($village === null) {
            throw new RuntimeException('Desa/kelurahan tujuan tidak ditemukan.');
        }

        $destinationId = $this->findDestinationId(
            (string) $village->name,
            (string) $village->district->name,
            (string) $village->district->city->name,
        );

        if ($destinationId === null) {
            throw new RuntimeException('Tujuan pengiriman tidak ditemukan di RajaOngkir.');
        }

        return $this->getCosts($destinationId, $weight);
    }

    /**
     * Cocokkan nama wilayah Laravolt dengan ID tujuan RajaOngkir.
     * Bila desa tidak ditemukan, gunakan kecamatan yang sama sebagai pengganti.
     */
    public function findDestinationId(string $village, string $district, string $city): ?int
    {
        $cacheKey = 'rajaongkir.destination.'.md5($this->normalize($village.'|'.$district.'|'.$city));

        $cached = Cache::get($cacheKey);
        if (is_int($cached)) {
            return $cached;
        }

        $districtFallback = null;

        foreach ([$village, $district] as $term) {
            foreach ($this->searchDestinations($term) as $destination) {
                $sameArea = $this->normalize((string) ($destination['district_name'] ?? '')) === $this->normalize($district)
                    && $this->normalize((string) ($destination['city_name'] ?? '')) === $this->normalize($city);

                if (! $sameArea) {
                    continue;
                }

                if ($this->normalize((string) ($destination['subdistrict_name'] ?? '')) === $this->normalize($village)) {
                    return $this->remember($cacheKey, (int) $destination['id']);
                }

                $districtFallback ??= (int) $destination['id'];
            }
        }

        return $districtFallback === null ? null : $this->remember($cacheKey, $districtFallback);
    }

    /**
     * @return list<array{key: string, courier: string, courier_name: string, service: string, description: string, cost: int, etd: string}>
     *
     * @throws RuntimeException
     */
    public function getCosts(int $destinationId, int $weight): array
    {
        $origin = (int) config('services.rajaongkir.origin_id');
        $couriers = (string) config('services.rajaongkir.couriers');
        $cacheKey = 'rajaongkir.cost.'.md5("{$origin}|{$destinationId}|{$weight}|{$couriers}");

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $payload = $this->request('POST', '/calculate/domestic-cost', [
            'origin' => $origin,
            'destination' => $destinationId,
            'weight' => $weight,
            'courier' => $couriers,
        ]);

        $options = collect($payload['data'] ?? [])
            ->filter(fn ($row): bool => is_array($row) && (int) ($row['cost'] ?? 0) > 0)
            ->map(fn (array $row): array => [
                'key' => $row['code'].'|'.$row['service'],
                'courier' => (string) $row['code'],
                'courier_name' => (string) ($row['name'] ?? strtoupper((string) $row['code'])),
                'service' => (string) $row['service'],
                'description' => (string) ($row['description'] ?? ''),
                'cost' => (int) $row['cost'],
                'etd' => (string) ($row['etd'] ?? ''),
            ])
            ->unique('key')
            ->sortBy('cost')
            ->values()
            ->all();

        if ($options === []) {
            throw new RuntimeException('Tidak ada layanan pengiriman yang tersedia untuk tujuan ini.');
        }

        Cache::put($cacheKey, $options, now()->addMinutes(10));

        return $options;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchDestinations(string $term): array
    {
        try {
            $payload = $this->request('GET', '/destination/domestic-destination', [
                'search' => $term,
                'limit' => 50,
            ]);
        } catch (RuntimeException) {
            return [];
        }

        return array_values(array_filter($payload['data'] ?? [], 'is_array'));
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    private function request(string $method, string $path, array $params): array
    {
        $client = Http::baseUrl(rtrim((string) config('services.rajaongkir.base_url'), '/'))
            ->withHeaders(['key' => (string) config('services.rajaongkir.api_key')])
            ->connectTimeout(3)
            ->timeout(10)
            ->acceptJson();

        try {
            $response = $this->send($client, $method, $path, $params);
        } catch (ConnectionException $exception) {
            Log::warning('RajaOngkir connection failed', ['path' => $path]);

            throw new RuntimeException('Layanan ongkos kirim tidak dapat dihubungi. Coba lagi sebentar lagi.', previous: $exception);
        }

        if (! $response->successful()) {
            Log::warning('RajaOngkir request failed', ['path' => $path, 'status' => $response->status()]);

            throw new RuntimeException('Ongkos kirim tidak dapat dihitung untuk tujuan ini.');
        }

        return (array) $response->json();
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function send(PendingRequest $client, string $method, string $path, array $params)
    {
        return $method === 'GET'
            ? $client->get($path, $params)
            : $client->asForm()->post($path, $params);
    }

    private function remember(string $cacheKey, int $destinationId): int
    {
        Cache::put($cacheKey, $destinationId, now()->addDays(30));

        return $destinationId;
    }

    private function normalize(string $name): string
    {
        $name = strtoupper(trim($name));
        $name = preg_replace('/^(KOTA ADMINISTRASI|KOTA|KABUPATEN|KAB\.?)\s+/', '', $name) ?? $name;

        return preg_replace('/\s+/', ' ', $name) ?? $name;
    }
}
