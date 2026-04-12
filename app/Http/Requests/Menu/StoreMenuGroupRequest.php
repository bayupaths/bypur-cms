<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100', 'unique:menu_groups,name'],
            'display_name' => ['nullable', 'string', 'max:150'],
            'description'  => ['nullable', 'string', 'max:500'],
            'is_active'    => ['boolean'],
        ];
    }
}
