<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'issuer'         => ['required', 'string', 'max:255'],
            'issued_at'      => ['required', 'date'],
            'expired_at'     => ['nullable', 'date', 'after_or_equal:issued_at'],
            'credential_url' => ['nullable', 'url', 'max:500'],
            'image'          => ['nullable', 'string', 'max:500'],
        ];
    }
}
