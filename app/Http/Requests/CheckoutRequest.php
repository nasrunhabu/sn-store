<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'province_code' => ['required', 'string', 'exists:'.(new Province)->getTable().',code'],
            'city_code' => ['required', 'string', 'exists:'.(new City)->getTable().',code'],
            'district_code' => ['required', 'string', 'exists:'.(new District)->getTable().',code'],
            'village_code' => ['required', 'string', 'exists:'.(new Village)->getTable().',code'],
            'postal_code' => ['required', 'digits:5'],
            'shipping_address' => ['required', 'string', 'max:2000'],
            'shipping_option' => ['required', 'string', 'max:80'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'province_code' => 'provinsi',
            'city_code' => 'kabupaten/kota',
            'district_code' => 'kecamatan',
            'village_code' => 'desa/kelurahan',
            'postal_code' => 'kode pos',
            'shipping_address' => 'alamat lengkap',
            'shipping_option' => 'layanan pengiriman',
        ];
    }

    /**
     * Memastikan setiap wilayah benar-benar berada di bawah wilayah induknya.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $data = $validator->getData();

            if (! City::query()->where('code', $data['city_code'])->where('province_code', $data['province_code'])->exists()) {
                $validator->errors()->add('city_code', 'Kabupaten/kota tidak sesuai dengan provinsi yang dipilih.');
            } elseif (! District::query()->where('code', $data['district_code'])->where('city_code', $data['city_code'])->exists()) {
                $validator->errors()->add('district_code', 'Kecamatan tidak sesuai dengan kabupaten/kota yang dipilih.');
            } elseif (! Village::query()->where('code', $data['village_code'])->where('district_code', $data['district_code'])->exists()) {
                $validator->errors()->add('village_code', 'Desa/kelurahan tidak sesuai dengan kecamatan yang dipilih.');
            }
        }];
    }

    /**
     * @return array<string, string>
     */
    public function shipping(): array
    {
        $validated = $this->validated();

        return [
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'shipping_address' => $validated['shipping_address'],
            'shipping_postal_code' => $validated['postal_code'],
            'shipping_province' => Province::query()->where('code', $validated['province_code'])->value('name'),
            'shipping_city' => City::query()->where('code', $validated['city_code'])->value('name'),
            'shipping_district' => District::query()->where('code', $validated['district_code'])->value('name'),
            'shipping_village' => Village::query()->where('code', $validated['village_code'])->value('name'),
        ];
    }
}
