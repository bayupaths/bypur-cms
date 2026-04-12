<?php

namespace App\Http\Resources\Menu;

use App\Http\Resources\DataTableResource;
use Illuminate\Http\Request;

class MenuGroupResource extends DataTableResource
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
            'name'         => $this->name,
            'display_name' => $this->display_name,
            'description'  => $this->description,
            'is_active'    => $this->is_active,
            'menus_count'  => $this->whenCounted('menus'),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
