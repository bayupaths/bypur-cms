<?php

namespace App\Services\Auth;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoleService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Role::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
            )
            ->when($filters['guard'] ?? null, fn ($q, $guard) => $q->guard($guard))
            ->withCount('users')
            ->with('permissions')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Role::orderBy('level', 'desc')->get();
    }

    public function find(int $id): Role
    {
        return Role::with(['permissions', 'users'])->findOrFail($id);
    }

    public function create(array $data): Role
    {
        $role = Role::create([
            'name'         => $data['name'],
            'display_name' => $data['display_name'] ?? null,
            'description'  => $data['description'] ?? null,
            'guard_name'   => $data['guard_name'] ?? 'web',
            'level'        => $data['level'] ?? 0,
            'is_system'    => $data['is_system'] ?? false,
        ]);

        if (! empty($data['permissions'])) {
            $role->givePermission($data['permissions']);
        }

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $role->update(array_filter([
            'name'         => $data['name'] ?? null,
            'display_name' => $data['display_name'] ?? null,
            'description'  => $data['description'] ?? null,
            'guard_name'   => $data['guard_name'] ?? null,
            'level'        => $data['level'] ?? null,
            'is_system'    => $data['is_system'] ?? null,
        ], fn ($v) => $v !== null));

        if (array_key_exists('permissions', $data)) {
            $role->permissions()->sync(
                $this->resolvePermissionIds($data['permissions'])
            );
        }

        return $role->fresh('permissions');
    }

    public function delete(Role $role): bool
    {
        if ($role->is_system) {
            throw new \RuntimeException('Role sistem tidak dapat dihapus.');
        }

        return (bool) $role->delete();
    }

    public function syncPermissions(Role $role, array $permissions): Role
    {
        $role->permissions()->sync(
            $this->resolvePermissionIds($permissions)
        );

        return $role->fresh('permissions');
    }

    /** @param  array<string|int|Permission>  $permissions */
    private function resolvePermissionIds(array $permissions): array
    {
        return collect($permissions)->map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission->id;
            }

            if (is_string($permission)) {
                return Permission::where('name', $permission)->firstOrFail()->id;
            }

            return $permission;
        })->all();
    }
}
