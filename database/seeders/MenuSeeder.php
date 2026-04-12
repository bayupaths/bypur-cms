<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/menus.json');
        $data     = json_decode(file_get_contents($jsonPath), associative: true);

        foreach ($data['groups'] as $groupData) {
            $group = MenuGroup::firstOrCreate(
                ['name' => $groupData['name']],
                [
                    'display_name' => $groupData['display_name'] ?? null,
                    'description'  => $groupData['description'] ?? null,
                    'is_active'    => true,
                ]
            );

            $this->command->info("  Seeding group: {$group->name}");

            foreach ($groupData['items'] as $itemData) {
                $this->seedMenuItem($group->id, null, $itemData);
            }
        }

        $this->command->info('  Menu berhasil di-seed.');
    }

    private function seedMenuItem(int $groupId, ?int $parentId, array $data): void
    {
        $menu = Menu::firstOrCreate(
            [
                'group_id'  => $groupId,
                'parent_id' => $parentId,
                'title'     => $data['title'],
            ],
            [
                'slug'          => $data['slug'] ?? null,
                'url'           => $data['url'] ?? null,
                'is_route'      => $data['is_route'] ?? false,
                'icon'          => $data['icon'] ?? null,
                'badge'         => $data['badge'] ?? null,
                'badge_variant' => $data['badge_variant'] ?? null,
                'target'        => $data['target'] ?? '_self',
                'order'         => $data['order'] ?? 0,
                'is_active'     => $data['is_active'] ?? true,
                'is_divider'    => $data['is_divider'] ?? false,
            ]
        );

        // Sync roles
        if (! empty($data['roles'])) {
            $roleIds = Role::whereIn('name', $data['roles'])->pluck('id');
            $menu->roles()->sync($roleIds);
        }

        // Sync permissions
        if (! empty($data['permissions'])) {
            $permissionIds = Permission::whereIn('name', $data['permissions'])->pluck('id');
            $menu->permissions()->sync($permissionIds);
        }

        // Rekursif untuk children
        if (! empty($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->seedMenuItem($groupId, $menu->id, $childData);
            }
        }
    }
}
