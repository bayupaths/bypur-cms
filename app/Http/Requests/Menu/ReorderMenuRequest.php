<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'        => ['required', 'array', 'min:1'],
            'items.*.id'   => ['required', 'integer', 'exists:menus,id'],
            'items.*.order'=> ['required', 'integer', 'min:0'],
        ];
    }
}
