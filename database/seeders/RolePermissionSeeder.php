<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar semua permissions yang tersedia di sistem.
     * Format: 'group' => ['action', ...]
     */
    private array $permissions = [
        'users' => [
            'viewAny', 'view', 'create', 'update', 'delete',
        ],
        'roles' => [
            'viewAny', 'view', 'create', 'update', 'delete', 'syncPermissions',
        ],
        'permissions' => [
            'viewAny', 'view', 'create', 'update', 'delete', 'syncConditions',
        ],
        'menus' => [
            'viewAny', 'view', 'create', 'update', 'delete', 'reorder',
        ],
        'menu-groups' => [
            'viewAny', 'view', 'create', 'update', 'delete',
        ],
    ];

    /**
     * Daftar roles beserta level hierarki dan permissions yang didapat.
     * 'permissions' => '*' artinya semua permission.
     */
    private array $roles = [
        [
            'name'         => 'super-admin',
            'display_name' => 'Super Administrator',
            'description'  => 'Memiliki akses penuh ke seluruh sistem.',
            'level'        => 100,
            'permissions'  => '*',
        ],
    ];

    public function run(): void
    {
        // ── 1. Buat semua permissions ─────────────────────────────────────
        $createdPermissions = collect();

        foreach ($this->permissions as $group => $actions) {
            foreach ($actions as $action) {
                $permission = Permission::firstOrCreate(
                    ['name' => "{$group}.{$action}"],
                    [
                        'display_name' => ucfirst($action) . ' ' . ucwords(str_replace('-', ' ', $group)),
                        'group'        => $group,
                        'guard_name'   => 'web',
                    ]
                );

                $createdPermissions->put($permission->name, $permission);
            }
        }

        // ── 2. Buat semua roles ───────────────────────────────────────────
        foreach ($this->roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description'  => $roleData['description'],
                    'guard_name'   => 'web',
                    'level'        => $roleData['level'],
                ]
            );

            // Sync permissions role
            if ($roleData['permissions'] === '*') {
                $role->permissions()->sync($createdPermissions->pluck('id'));
            } elseif (! empty($roleData['permissions'])) {
                $ids = $createdPermissions
                    ->only($roleData['permissions'])
                    ->pluck('id');

                $role->permissions()->sync($ids);
            } else {
                $role->permissions()->sync([]);
            }
        }

        // ── 3. Buat akun super-admin default ─────────────────────────────
        $superAdmin = User::firstOrCreate(
            ['email' => 'bayupur1710@gmail.com'],
            [
                'name'     => 'Bayu Purnomo',
                'password' => Hash::make('By@011017p!'),
            ]
        );

        $superAdminRole = Role::where('name', 'super-admin')->first();
        if ($superAdminRole && ! $superAdmin->roles()->where('role_id', $superAdminRole->id)->exists()) {
            $superAdmin->assignRole([$superAdminRole->id]);
        }

        $this->command->info('  Roles dan permissions berhasil di-seed.');
        $this->command->table(
            ['Role', 'Level', 'Jumlah Permission'],
            Role::withCount('permissions')->get()->map(fn ($r) => [
                $r->name, $r->level, $r->permissions_count,
            ])->toArray()
        );
    }
}
