<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'slug'     => ['required', 'string', 'max:255', Rule::unique('skills', 'slug')->ignore($this->route('skill'))],
            'icon'     => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'in:frontend,backend,tools,ai,other'],
            'level'    => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }
}
