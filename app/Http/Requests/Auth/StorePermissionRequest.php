<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode'               => ['required', 'string', 'in:module_crud,custom'],
            'group'              => ['required', 'string', 'max:100'],
            'guard_name'         => ['nullable', 'string', 'max:50'],
            // module_crud mode
            'selected_actions'   => ['required_if:mode,module_crud', 'nullable', 'array', 'min:1'],
            'selected_actions.*' => ['string', 'in:view,create,edit,delete'],
            // custom mode
            'action'             => ['required_if:mode,custom', 'nullable', 'string', 'max:100'],
            'display_name'       => ['nullable', 'string', 'max:200'],
            'description'        => ['nullable', 'string', 'max:500'],
        ];
    }
}
