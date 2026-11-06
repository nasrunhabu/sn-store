<?php

namespace App\Http\Requests\Admin;

class HeroSlideRules
{
    /**
     * Tombol hanya boleh mengarah ke path internal, anchor, atau URL http(s).
     *
     * @return array<string, array<int, string>>
     */
    public static function base(): array
    {
        return [
            'badge' => ['nullable', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:100'],
            'highlight' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'button_text' => ['nullable', 'required_with:button_url', 'string', 'max:40'],
            'button_url' => ['nullable', 'required_with:button_text', 'string', 'max:255', 'regex:/^(https?:\/\/|\/|#)\S*$/'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
