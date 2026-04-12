<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['sometimes', 'string', 'max:100', Rule::unique('menu_groups', 'name')->ignore($this->route('menuGroup'))],
            'display_name' => ['nullable', 'string', 'max:150'],
            'description'  => ['nullable', 'string', 'max:500'],
            'is_active'    => ['boolean'],
        ];
    }
}
