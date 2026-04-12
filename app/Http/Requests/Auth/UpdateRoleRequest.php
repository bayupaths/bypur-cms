<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id ?? $this->route('role');

        return [
            'name'          => ['sometimes', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($roleId)],
            'display_name'  => ['nullable', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:500'],
            'guard_name'    => ['nullable', 'string', 'max:50'],
            'level'         => ['nullable', 'integer', 'min:0'],
            'is_system'     => ['boolean'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }
}
