<?php

namespace App\Services\Menu;

use App\Models\MenuGroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MenuGroupService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return MenuGroup::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
            )
            ->withCount('menus')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return MenuGroup::active()->get();
    }

    public function find(int $id): MenuGroup
    {
        return MenuGroup::with('rootMenus')->findOrFail($id);
    }

    public function create(array $data): MenuGroup
    {
        return MenuGroup::create([
            'name'         => $data['name'],
            'display_name' => $data['display_name'] ?? null,
            'description'  => $data['description'] ?? null,
            'is_active'    => $data['is_active'] ?? true,
        ]);
    }

    public function update(MenuGroup $group, array $data): MenuGroup
    {
        $group->update(array_filter([
            'name'         => $data['name'] ?? null,
            'display_name' => $data['display_name'] ?? null,
            'description'  => $data['description'] ?? null,
            'is_active'    => $data['is_active'] ?? null,
        ], fn ($v) => $v !== null));

        return $group->fresh();
    }

    public function delete(MenuGroup $group): bool
    {
        return (bool) $group->delete();
    }
}
