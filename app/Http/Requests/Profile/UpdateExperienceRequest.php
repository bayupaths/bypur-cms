<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company'      => ['required', 'string', 'max:255'],
            'position'     => ['required', 'string', 'max:255'],
            'location'     => ['nullable', 'string', 'max:255'],
            'type'         => ['nullable', 'string', 'max:100'],
            'started_at'   => ['required', 'date'],
            'ended_at'     => ['nullable', 'date', 'after_or_equal:started_at'],
            'is_current'   => ['boolean'],
            'description'  => ['nullable', 'string'],
            'tech_stack'   => ['nullable', 'array', 'max:20'],
            'tech_stack.*' => ['string', 'max:50'],
        ];
    }
}
