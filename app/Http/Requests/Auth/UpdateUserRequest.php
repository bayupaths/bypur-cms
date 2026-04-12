<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'         => ['sometimes', 'string', 'max:255'],
            'username'     => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'email'        => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password'     => ['nullable', 'string', 'min:8', 'confirmed'],
            // Profile
            'avatar'       => ['nullable', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'gender'       => ['nullable', 'in:male,female'],
            'birth_date'   => ['nullable', 'date'],
            'bio'          => ['nullable', 'string', 'max:500'],
            // Address
            'address'      => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:100'],
            'postal_code'  => ['nullable', 'string', 'max:10'],
            // Social
            'website'      => ['nullable', 'url', 'max:255'],
            'github'       => ['nullable', 'string', 'max:255'],
            'linkedin'     => ['nullable', 'string', 'max:255'],
            'twitter'      => ['nullable', 'string', 'max:255'],
            'instagram'    => ['nullable', 'string', 'max:255'],
            // Status
            'is_active'    => ['boolean'],
            'is_superadmin'=> ['boolean'],
            // Misc
            'meta'         => ['nullable', 'array'],
            // Roles
            'roles'        => ['nullable', 'array'],
            'roles.*'      => ['integer', 'exists:roles,id'],
        ];
    }
}
