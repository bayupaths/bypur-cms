<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ── Profile ──────────────────────────────────────────────────
            'name'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'nickname'     => ['sometimes', 'nullable', 'string', 'max:100'],
            'tagline'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'taglines'     => ['sometimes', 'nullable', 'array', 'max:5'],
            'taglines.*'   => ['nullable', 'string', 'max:100'],
            'phone'        => ['sometimes', 'nullable', 'string', 'max:50'],
            'email'        => ['sometimes', 'nullable', 'email', 'max:255'],
            'location'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'bio'          => ['sometimes', 'nullable', 'string', 'max:1000'],
            'resume_url'   => ['sometimes', 'nullable', 'url', 'max:500'],
            'resume_file'  => ['sometimes', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'is_available' => ['sometimes', 'boolean'],

            // ── Personal ─────────────────────────────────────────────────
            'gender'       => ['sometimes', 'nullable', 'in:male,female'],
            'birth_date'   => ['sometimes', 'nullable', 'date'],
            'address'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'city'         => ['sometimes', 'nullable', 'string', 'max:100'],
            'country'      => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code'  => ['sometimes', 'nullable', 'string', 'max:20'],

            // ── Social links ──────────────────────────────────────────────
            'website_url'    => ['sometimes', 'nullable', 'url', 'max:500'],
            'socials'        => ['sometimes', 'nullable', 'array'],
            'socials.*.key'  => ['required_with:socials', 'string', 'max:50'],
            'socials.*.url'  => ['required_with:socials', 'url', 'max:500'],
        ];
    }
}
