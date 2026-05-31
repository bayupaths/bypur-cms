<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class StoreSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'slug'     => ['required', 'string', 'max:255', 'unique:skills,slug'],
            'icon'     => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'in:frontend,backend,tools,ai,other'],
            'level'    => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
