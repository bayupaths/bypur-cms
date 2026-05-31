<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', Rule::unique('services', 'slug')->ignore($this->route('service'))],
            'description' => ['required', 'string'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'price_from'  => ['nullable', 'numeric', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
