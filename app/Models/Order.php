<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'order_number',
    'status',
    'recipient_name',
    'recipient_phone',
    'shipping_address',
    'shipping_province',
    'shipping_city',
    'shipping_district',
    'shipping_village',
    'shipping_postal_code',
    'shipping_courier',
    'shipping_service',
    'shipping_etd',
    'shipping_weight',
    'shipping_cost',
    'total',
    'payment_reference',
    'snap_token',
    'paid_at',
    'failed_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total' => 'integer',
            'shipping_cost' => 'integer',
            'shipping_weight' => 'integer',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * Alamat jalan dilengkapi wilayah administratif (desa hingga provinsi).
     */
    protected function fullShippingAddress(): Attribute
    {
        return Attribute::get(fn (): string => collect([
            $this->shipping_address,
            $this->shipping_village,
            $this->shipping_district,
            $this->shipping_city,
            $this->shipping_province,
            $this->shipping_postal_code,
        ])->filter()->implode(', '));
    }

    /**
     * Total pesanan dikurangi ongkos kirim.
     */
    protected function itemsSubtotal(): Attribute
    {
        return Attribute::get(fn (): int => (int) $this->total - (int) $this->shipping_cost);
    }

    protected function shippingLabel(): Attribute
    {
        return Attribute::get(fn (): string => trim(strtoupper((string) $this->shipping_courier).' '.$this->shipping_service));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
