<?php

namespace App\Http\Requests\Menu;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_id'     => ['required', 'integer', 'exists:menu_groups,id'],
            'parent_id'    => ['nullable', 'integer', 'exists:menus,id'],
            'title'        => ['required', 'string', 'max:150'],
            'slug'         => ['nullable', 'string', 'max:150', 'unique:menus,slug'],
            'url'          => ['nullable', 'string', 'max:500'],
            'is_route'     => ['boolean'],
            'icon'         => ['nullable', 'string', 'max:100'],
            'badge'        => ['nullable', 'string', 'max:50'],
            'badge_variant'=> ['nullable', 'string', 'max:50'],
            'target'       => ['nullable', 'in:_self,_blank'],
            'order'        => ['integer', 'min:0'],
            'is_active'    => ['boolean'],
            'is_divider'   => ['boolean'],
            'roles'        => ['nullable', 'array'],
            'roles.*'      => ['integer', 'exists:roles,id'],
            'permissions'  => ['nullable', 'array'],
            'permissions.*'=> ['integer', 'exists:permissions,id'],
        ];
    }
}
