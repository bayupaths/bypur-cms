<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SyncConditionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conditions'             => ['required', 'array'],
            'conditions.*.attribute' => ['required', 'string', 'max:200'],
            'conditions.*.operator'  => ['required', 'string', 'in:=,!=,>,>=,<,<=,in,not_in'],
            'conditions.*.value'     => ['required'],
        ];
    }
}
