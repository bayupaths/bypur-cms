<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')?->id ?? $this->route('permission');

        return [
            'name'                   => ['sometimes', 'string', 'max:150', Rule::unique('permissions', 'name')->ignore($permissionId)],
            'display_name'           => ['nullable', 'string', 'max:200'],
            'description'            => ['nullable', 'string', 'max:500'],
            'group'                  => ['nullable', 'string', 'max:100'],
            'guard_name'             => ['nullable', 'string', 'max:50'],
            'conditions'             => ['nullable', 'array'],
            'conditions.*.attribute' => ['required_with:conditions', 'string', 'max:200'],
            'conditions.*.operator'  => ['required_with:conditions', 'string', 'in:=,!=,>,>=,<,<=,in,not_in'],
            'conditions.*.value'     => ['required_with:conditions'],
        ];
    }
}
