<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'institution' => ['required', 'string', 'max:255'],
            'degree'      => ['required', 'string', 'max:255'],
            'field'       => ['nullable', 'string', 'max:255'],
            'started_at'  => ['required', 'date'],
            'ended_at'    => ['nullable', 'date', 'after_or_equal:started_at'],
            'is_current'  => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
