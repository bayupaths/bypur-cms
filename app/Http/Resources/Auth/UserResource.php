<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\DataTableResource;
use Illuminate\Http\Request;

class UserResource extends DataTableResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'username'        => $this->username,
            'email'           => $this->email,
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),

            // Profile
            'avatar'          => $this->avatar,
            'phone'           => $this->phone,
            'gender'          => $this->gender,
            'birth_date'      => $this->birth_date,
            'bio'             => $this->bio,

            // Address
            'address'         => $this->address,
            'city'            => $this->city,
            'country'         => $this->country,
            'postal_code'     => $this->postal_code,

            // Social
            'website'         => $this->website,
            'github'          => $this->github,
            'linkedin'        => $this->linkedin,
            'twitter'         => $this->twitter,
            'instagram'       => $this->instagram,

            // Status
            'is_active'       => $this->is_active,
            'is_superadmin'   => $this->is_superadmin,

            // Security
            'last_login_at'   => $this->last_login_at?->toDateTimeString(),
            'last_login_ip'   => $this->last_login_ip,
            'login_attempts'  => $this->login_attempts,
            'locked_until'    => $this->locked_until?->toDateTimeString(),

            // Roles
            'roles_list'      => $this->whenLoaded('roles', fn () => $this->roles->map(fn ($r) => [
                'id'           => $r->id,
                'name'         => $r->name,
                'display_name' => $r->display_name,
            ])),

            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
            'deleted_at'      => $this->deleted_at?->toDateTimeString(),
        ];
    }
}
