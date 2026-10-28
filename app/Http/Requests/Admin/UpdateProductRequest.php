<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canAccessAdminPanel() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:1'],
            'original_price' => ['nullable', 'integer', 'min:1'],
            'badge' => ['required', 'string', 'max:40'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'image_alt' => ['required', 'string', 'max:160'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
