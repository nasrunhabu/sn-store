<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'category_id',
    'name',
    'price',
    'original_price',
    'rating',
    'reviews',
    'badge',
    'image_path',
    'image_alt',
    'is_active',
])]
class Product extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'original_price' => 'integer',
            'rating' => 'decimal:1',
            'reviews' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk('public')->url($this->image_path));
    }
}
