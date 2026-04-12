<?php

namespace App\Http\Resources\Menu;

use App\Http\Resources\DataTableResource;
use Illuminate\Http\Request;

class MenuResource extends DataTableResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'group_id'     => $this->group_id,
            'parent_id'    => $this->parent_id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'url'          => $this->url,
            'is_route'     => $this->is_route,
            'icon'         => $this->icon,
            'badge'        => $this->badge,
            'badge_variant'=> $this->badge_variant,
            'target'       => $this->target,
            'order'        => $this->order,
            'is_active'    => $this->is_active,
            'is_divider'   => $this->is_divider,
            'group'        => $this->whenLoaded('group', fn () => [
                'id'   => $this->group->id,
                'name' => $this->group->name,
            ]),
            'parent'       => $this->whenLoaded('parent', fn () => [
                'id'    => $this->parent->id,
                'title' => $this->parent->title,
            ]),
            'roles'        => $this->whenLoaded('roles', fn () => $this->roles->pluck('id')->values()),
            'permissions'  => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('id')->values()),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
