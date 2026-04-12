<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\DataTableResource;
use Illuminate\Http\Request;

class RoleResource extends DataTableResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'display_name' => $this->display_name,
            'guard_name'   => $this->guard_name,
            'level'        => $this->level,
            'is_system'    => (bool) $this->is_system,
            'description'  => $this->description,
            'permissions'    => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')->toArray()),
            'permission_ids' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('id')->toArray()),
            'users_count'  => $this->whenCounted('users'),
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
