<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'badge',
    'title',
    'highlight',
    'description',
    'button_text',
    'button_url',
    'image_path',
    'sort_order',
    'is_active',
])]
class HeroSlide extends Model
{
    use HasFactory;

    /**
     * Gambar bisa berupa file yang diunggah atau URL eksternal (data bawaan).
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => Str::startsWith($this->image_path, ['http://', 'https://'])
            ? $this->image_path
            : Storage::disk('public')->url($this->image_path));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
