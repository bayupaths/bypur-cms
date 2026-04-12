<?php

namespace App\Services\Menu;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Menu::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('title', 'like', "%{$search}%")
            )
            ->when($filters['group_id'] ?? null, fn ($q, $groupId) =>
                $q->where('group_id', $groupId)
            )
            ->when($filters['is_active'] ?? null, fn ($q, $active) =>
                $q->where('is_active', $active)
            )
            ->with(['group', 'parent'])
            ->ordered()
            ->paginate($perPage);
    }

    public function find(int $id): Menu
    {
        return Menu::with(['group', 'parent', 'children', 'roles', 'permissions'])->findOrFail($id);
    }

    public function create(array $data): Menu
    {
        $menu = Menu::create([
            'group_id'     => $data['group_id'],
            'parent_id'    => $data['parent_id'] ?? null,
            'title'        => $data['title'],
            'slug'         => $data['slug'] ?? null,
            'url'          => $data['url'] ?? null,
            'is_route'     => $data['is_route'] ?? false,
            'icon'         => $data['icon'] ?? null,
            'badge'        => $data['badge'] ?? null,
            'badge_variant'=> $data['badge_variant'] ?? null,
            'target'       => $data['target'] ?? '_self',
            'order'        => $data['order'] ?? 0,
            'is_active'    => $data['is_active'] ?? true,
            'is_divider'   => $data['is_divider'] ?? false,
        ]);

        if (! empty($data['roles'])) {
            $menu->allowRoles($data['roles']);
        }

        if (! empty($data['permissions'])) {
            $menu->requirePermissions($data['permissions']);
        }

        return $menu;
    }

    public function update(Menu $menu, array $data): Menu
    {
        $updateData = array_filter([
            'group_id'     => $data['group_id'] ?? null,
            'title'        => $data['title'] ?? null,
            'slug'         => $data['slug'] ?? null,
            'url'          => $data['url'] ?? null,
            'is_route'     => $data['is_route'] ?? null,
            'icon'         => $data['icon'] ?? null,
            'badge'        => $data['badge'] ?? null,
            'badge_variant'=> $data['badge_variant'] ?? null,
            'target'       => $data['target'] ?? null,
            'order'        => $data['order'] ?? null,
            'is_active'    => $data['is_active'] ?? null,
            'is_divider'   => $data['is_divider'] ?? null,
        ], fn ($v) => $v !== null);

        // parent_id is handled separately because null is a valid value (removes parent)
        if (array_key_exists('parent_id', $data)) {
            $updateData['parent_id'] = $data['parent_id'];
        }

        $menu->update($updateData);

        if (array_key_exists('roles', $data)) {
            $menu->roles()->sync($data['roles']);
        }

        if (array_key_exists('permissions', $data)) {
            $menu->permissions()->sync($data['permissions']);
        }

        return $menu->fresh(['group', 'parent', 'roles', 'permissions']);
    }

    public function delete(Menu $menu): bool
    {
        return (bool) $menu->delete();
    }

    /**
     * Reorder menu item secara batch.
     *
     * @param  array<array{id: int, order: int}>  $items
     */
    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            Menu::where('id', $item['id'])->update(['order' => $item['order']]);
        }
    }

    /**
     * Ambil menu tree untuk group tertentu, difilter berdasarkan aksesibilitas user.
     *
     * @return Collection<int, Menu>
     */
    public function treeForUser(string $groupName, User $user): Collection
    {
        $menus = Menu::forGroup($groupName)
            ->root()
            ->active()
            ->ordered()
            ->with(['allChildren.roles', 'allChildren.permissions', 'roles', 'permissions'])
            ->get();

        return $this->filterAccessible($menus, $user);
    }

    /**
     * Ambil full menu tree untuk group (tanpa filter akses, untuk kebutuhan admin).
     *
     * @return Collection<int, Menu>
     */
    public function treeForGroup(string $groupName): Collection
    {
        return Menu::forGroup($groupName)
            ->root()
            ->ordered()
            ->with(['allChildren', 'roles', 'permissions'])
            ->get();
    }

    /** @param  Collection<int, Menu>  $menus */
    private function filterAccessible(Collection $menus, User $user): Collection
    {
        return $menus
            ->filter(fn (Menu $menu) => $menu->isAccessibleBy($user))
            ->map(function (Menu $menu) use ($user) {
                if ($menu->relationLoaded('allChildren') && $menu->allChildren->isNotEmpty()) {
                    $menu->setRelation(
                        'allChildren',
                        $this->filterAccessible($menu->allChildren, $user)
                    );
                }

                return $menu;
            })
            ->values();
    }
}
