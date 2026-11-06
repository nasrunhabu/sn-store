@extends('layouts.checkout')

@section('title', 'Checkout')

@section('content')
    <div class="mb-8">
        <a href="{{ route('cart.index') }}" class="text-sm font-bold text-red-700 hover:text-red-800">← Kembali ke keranjang</a>
        <h1 class="mt-4 text-3xl font-extrabold tracking-tight text-gray-950">Checkout</h1>
        <p class="mt-2 text-sm text-gray-500">Isi alamat pengiriman dan konfirmasi pembayaran untuk menyelesaikan pesanan.</p>
    </div>

    @if ($errors->any())
        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]" x-data="regionSelect({
        citiesUrl: @js(route('regions.cities', ['province' => '__CODE__'])),
        districtsUrl: @js(route('regions.districts', ['city' => '__CODE__'])),
        villagesUrl: @js(route('regions.villages', ['district' => '__CODE__'])),
        shippingUrl: @js(route('checkout.shipping-cost')),
        subtotal: {{ (int) $total }},
        province: @js(old('province_code', '')),
        city: @js(old('city_code', '')),
        district: @js(old('district_code', '')),
        village: @js(old('village_code', '')),
        shippingKey: @js(old('shipping_option', '')),
        postalCode: @js(old('postal_code', '')),
    })">
        @csrf
        <div class="space-y-5">
            <section class="space-y-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-950">Informasi penerima</h2>
                    <p class="mt-1 text-sm text-gray-500">Pastikan nomor telepon dan alamat dapat digunakan untuk pengiriman.</p>
                </div>
                <div>
                    <label for="recipient_name" class="mb-2 block text-sm font-bold text-gray-700">Nama penerima</label>
                    <input id="recipient_name" name="recipient_name" value="{{ old('recipient_name', $user->name) }}" required maxlength="120" autocomplete="name" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    @error('recipient_name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="recipient_phone" class="mb-2 block text-sm font-bold text-gray-700">Nomor telepon</label>
                    <input id="recipient_phone" name="recipient_phone" type="tel" value="{{ old('recipient_phone') }}" required maxlength="30" autocomplete="tel" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    @error('recipient_phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="province_code" class="mb-2 block text-sm font-bold text-gray-700">Provinsi</label>
                        <select id="province_code" name="province_code" x-model="province" @change="loadCities(true)" required class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="">Pilih provinsi</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->code }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                        @error('province_code')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="city_code" class="mb-2 block text-sm font-bold text-gray-700">Kabupaten/Kota</label>
                        <select id="city_code" name="city_code" x-model="city" @change="loadDistricts(true)" required :disabled="!province || loading === 'city'" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="" x-text="loading === 'city' ? 'Memuat...' : 'Pilih kabupaten/kota'"></option>
                            <template x-for="item in cities" :key="item.code">
                                <option :value="item.code" x-text="item.name" :selected="item.code === city"></option>
                            </template>
                        </select>
                        @error('city_code')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="district_code" class="mb-2 block text-sm font-bold text-gray-700">Kecamatan</label>
                        <select id="district_code" name="district_code" x-model="district" @change="loadVillages(true)" required :disabled="!city || loading === 'district'" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="" x-text="loading === 'district' ? 'Memuat...' : 'Pilih kecamatan'"></option>
                            <template x-for="item in districts" :key="item.code">
                                <option :value="item.code" x-text="item.name" :selected="item.code === district"></option>
                            </template>
                        </select>
                        @error('district_code')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="village_code" class="mb-2 block text-sm font-bold text-gray-700">Desa/Kelurahan</label>
                        <select id="village_code" name="village_code" x-model="village" @change="applyVillagePostalCode(); loadShipping()" required :disabled="!district || loading === 'village'" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400">
                            <option value="" x-text="loading === 'village' ? 'Memuat...' : 'Pilih desa/kelurahan'"></option>
                            <template x-for="item in villages" :key="item.code">
                                <option :value="item.code" x-text="item.name" :selected="item.code === village"></option>
                            </template>
                        </select>
                        @error('village_code')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <p x-show="loadError" x-cloak class="text-xs text-red-700 sm:col-span-2">Data wilayah gagal dimuat. Muat ulang halaman lalu coba lagi.</p>
                </div>
                <div>
                    <label for="postal_code" class="mb-2 block text-sm font-bold text-gray-700">Kode pos</label>
                    <input id="postal_code" name="postal_code" type="text" inputmode="numeric" pattern="[0-9]{5}" maxlength="5" required x-model="postalCode" autocomplete="postal-code" placeholder="Terisi otomatis sesuai kecamatan" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500 sm:max-w-xs">
                    <p class="mt-1 text-xs text-gray-500">Terisi otomatis sesuai kecamatan/desa, dapat diubah bila kurang tepat.</p>
                    @error('postal_code')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="shipping_address" class="mb-2 block text-sm font-bold text-gray-700">Alamat lengkap</label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required maxlength="2000" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, RT/RW, patokan, kode pos" class="w-full rounded-xl border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-950">Pengiriman</h2>
                    <p class="mt-1 text-sm text-gray-500">Dikirim dari {{ config('services.rajaongkir.origin_label') }}. Ongkos kirim dihitung oleh RajaOngkir setelah desa/kelurahan dipilih.</p>
                </div>
                <p x-show="!village" class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500">Pilih provinsi hingga desa/kelurahan untuk melihat ongkos kirim.</p>
                <p x-show="shippingLoading" x-cloak class="rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500">Menghitung ongkos kirim...</p>
                <p x-show="shippingError" x-cloak x-text="shippingError" role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"></p>
                <div class="space-y-2.5" x-show="shippingOptions.length > 0" x-cloak>
                    <template x-for="option in shippingOptions" :key="option.key">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 p-3.5 transition hover:bg-gray-50" :class="shippingKey === option.key ? 'border-red-600 bg-red-50/40' : ''">
                            <input type="radio" name="shipping_option" :value="option.key" x-model="shippingKey" class="size-4 text-red-600 focus:ring-red-500">
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold text-gray-900" x-text="option.courier_name + ' - ' + option.service"></span>
                                <span class="block text-xs text-gray-500" x-text="[option.description, option.etd ? 'estimasi ' + option.etd : ''].filter(Boolean).join(' · ')"></span>
                            </span>
                            <span class="text-sm font-extrabold text-gray-900" x-text="formatRupiah(option.cost)"></span>
                        </label>
                    </template>
                </div>
                @error('shipping_option')<p class="text-xs text-red-700">{{ $message }}</p>@enderror
            </section>

            <section class="space-y-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-950">Pilihan Metode Pembayaran</h2>
                    <p class="mt-1 text-sm text-gray-500">Pilih payment gateway untuk memproses transaksi Anda.</p>
                </div>

                <div class="space-y-3">
                    <label class="relative flex cursor-pointer items-start gap-3.5 rounded-2xl border-2 border-red-600 bg-red-50/30 p-4 transition hover:bg-red-50/50">
                        <input type="radio" name="payment_gateway" value="midtrans" checked class="mt-1 size-4 text-red-600 focus:ring-red-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-sm font-extrabold text-gray-950">Payment Gateway Midtrans</span>
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-emerald-800">Otomatis & Terverifikasi</span>
                            </div>
                            <p class="mt-1 text-xs leading-relaxed text-gray-600">
                                Pembayaran diproses secara instan dan aman. Setelah mengklik tombol buat pesanan, jendela pembayaran Midtrans Snap akan langsung terbuka untuk memilih metode pembayaran yang Anda inginkan.
                            </p>
                            <div class="mt-3 flex flex-wrap items-center gap-1.5 text-[10px] font-bold text-gray-700">
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">QRIS</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">GoPay</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">ShopeePay</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">BCA VA</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">BNI VA</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">BRI VA</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">Mandiri Bill</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">Permata</span>
                                <span class="rounded-lg border border-gray-200 bg-white px-2 py-1 shadow-xs">Kartu Kredit</span>
                            </div>
                        </div>
                    </label>
                </div>
            </section>
        </div>

        <aside class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-extrabold text-gray-950">Ringkasan pesanan</h2>
            <div class="mt-4 space-y-3.5 border-b border-gray-100 pb-4">
                @foreach ($items as $item)
                    <div class="flex items-center gap-3">
                        @if ($item->product->image_url)
                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->image_alt }}" class="size-12 shrink-0 rounded-xl bg-gray-50 object-cover">
                        @else
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-[10px] font-bold text-gray-400">
                                SN
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-gray-900">{{ $item->product->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ $item->quantity }} × Rp {{ number_format($item->product->price, 0, ',', '.') }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-gray-900">Rp {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Subtotal produk</span>
                    <span class="font-bold text-gray-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <span>Ongkos kirim</span>
                    <span class="font-bold text-gray-700" x-text="selectedShipping ? formatRupiah(selectedShipping.cost) : 'Pilih pengiriman'"></span>
                </div>
                <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                    <span class="text-sm font-bold text-gray-700">Total pembayaran</span>
                    <span class="text-lg font-extrabold text-gray-950" x-text="formatRupiah(subtotal + (selectedShipping ? selectedShipping.cost : 0))">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs text-emerald-900">
                <div class="flex items-center gap-2 font-bold">
                    <svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    <span>Pembayaran aman dengan Midtrans</span>
                </div>
                <p class="mt-1 text-emerald-800">Didukung enkripsi standar industri perbankan.</p>
            </div>
            <button type="submit" :disabled="!selectedShipping" class="mt-5 flex w-full items-center justify-center rounded-xl bg-red-700 px-5 py-3.5 text-sm font-extrabold text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:bg-gray-300">Buat pesanan</button>
            <p class="mt-3 text-center text-xs leading-5 text-gray-500">Stok akan direservasi saat pesanan dibuat dan dikembalikan jika pesanan dibatalkan atau pembayaran gagal.</p>
        </aside>
    </form>

    <script>
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('regionSelect', (config) => ({
                ...config,
                cities: [],
                districts: [],
                villages: [],
                loading: null,
                loadError: false,
                shippingOptions: [],
                shippingLoading: false,
                shippingError: '',
                get selectedShipping() {
                    return this.shippingOptions.find((option) => option.key === this.shippingKey) ?? null;
                },
                formatRupiah(amount) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                },
                async init() {
                    if (this.province) {
                        await this.loadCities(false);
                    }
                },
                mostCommonPostalCode() {
                    const counts = {};

                    this.villages.forEach((item) => {
                        if (item.postal_code) {
                            counts[item.postal_code] = (counts[item.postal_code] ?? 0) + 1;
                        }
                    });

                    return Object.keys(counts).sort((a, b) => counts[b] - counts[a])[0] ?? '';
                },
                applyVillagePostalCode() {
                    const selected = this.villages.find((item) => item.code === this.village);

                    this.postalCode = selected?.postal_code ?? this.mostCommonPostalCode();
                },
                resetShipping() {
                    this.shippingOptions = [];
                    this.shippingKey = '';
                    this.shippingError = '';
                },
                async loadShipping() {
                    const previousKey = this.shippingKey;
                    this.resetShipping();

                    if (!this.village) {
                        return;
                    }

                    this.shippingLoading = true;

                    try {
                        const response = await fetch(`${this.shippingUrl}?village_code=${encodeURIComponent(this.village)}`, {
                            headers: { Accept: 'application/json' },
                        });
                        const data = await response.json();

                        if (!response.ok) {
                            this.shippingError = data.message ?? 'Ongkos kirim tidak dapat dihitung.';

                            return;
                        }

                        this.shippingOptions = data.options;
                        this.shippingKey = data.options.some((option) => option.key === previousKey) ? previousKey : '';
                    } catch (error) {
                        this.shippingError = 'Ongkos kirim gagal dimuat. Coba pilih ulang desa/kelurahan.';
                    } finally {
                        this.shippingLoading = false;
                    }
                },
                async fetchList(template, code, type) {
                    this.loading = type;
                    this.loadError = false;

                    try {
                        const response = await fetch(template.replace('__CODE__', encodeURIComponent(code)), {
                            headers: { Accept: 'application/json' },
                        });

                        if (!response.ok) {
                            throw new Error(response.statusText);
                        }

                        return await response.json();
                    } catch (error) {
                        this.loadError = true;

                        return [];
                    } finally {
                        this.loading = null;
                    }
                },
                async loadCities(reset) {
                    if (reset) {
                        this.city = this.district = this.village = '';
                        this.resetShipping();
                    }
                    this.cities = [];
                    this.districts = [];
                    this.villages = [];

                    if (!this.province) {
                        return;
                    }

                    this.cities = await this.fetchList(this.citiesUrl, this.province, 'city');

                    if (!reset && this.city) {
                        await this.loadDistricts(false);
                    }
                },
                async loadDistricts(reset) {
                    if (reset) {
                        this.district = this.village = '';
                        this.resetShipping();
                    }
                    this.districts = [];
                    this.villages = [];

                    if (!this.city) {
                        return;
                    }

                    this.districts = await this.fetchList(this.districtsUrl, this.city, 'district');

                    if (!reset && this.district) {
                        await this.loadVillages(false);
                    }
                },
                async loadVillages(reset) {
                    if (reset) {
                        this.village = '';
                        this.postalCode = '';
                        this.resetShipping();
                    }
                    this.villages = [];

                    if (!this.district) {
                        return;
                    }

                    this.villages = await this.fetchList(this.villagesUrl, this.district, 'village');

                    if (reset || !this.postalCode) {
                        this.postalCode = this.mostCommonPostalCode();
                    }

                    if (!reset && this.village) {
                        await this.loadShipping();
                    }
                },
            }));
        });
    </script>
@endsection
