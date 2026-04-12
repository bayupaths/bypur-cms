<?php

namespace App\Services\Auth;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PermissionService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Permission::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
            )
            ->when($filters['group'] ?? null, fn ($q, $group) => $q->group($group))
            ->when($filters['guard'] ?? null, fn ($q, $guard) => $q->guard($guard))
            ->with('conditions')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Permission::orderBy('group')->orderBy('name')->get();
    }

    public function allGrouped(): Collection
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy('group');
    }

    public function find(int $id): Permission
    {
        return Permission::with(['roles', 'conditions'])->findOrFail($id);
    }

    public function create(array $data): Permission
    {
        $permission = Permission::create([
            'name'         => $data['name'],
            'display_name' => $data['display_name'] ?? null,
            'description'  => $data['description'] ?? null,
            'group'        => $data['group'] ?? null,
            'guard_name'   => $data['guard_name'] ?? 'web',
        ]);

        if (! empty($data['conditions'])) {
            $this->syncConditions($permission, $data['conditions']);
        }

        return $permission;
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update(array_filter([
            'name'         => $data['name'] ?? null,
            'display_name' => $data['display_name'] ?? null,
            'description'  => $data['description'] ?? null,
            'group'        => $data['group'] ?? null,
            'guard_name'   => $data['guard_name'] ?? null,
        ], fn ($v) => $v !== null));

        if (array_key_exists('conditions', $data)) {
            $this->syncConditions($permission, $data['conditions']);
        }

        return $permission->fresh('conditions');
    }

    public function delete(Permission $permission): bool
    {
        return (bool) $permission->delete();
    }

    /**
     * Ganti semua kondisi ABAC untuk permission ini.
     *
     * @param  array<array{attribute: string, operator: string, value: string, description?: string}>  $conditions
     */
    public function syncConditions(Permission $permission, array $conditions): void
    {
        $permission->conditions()->delete();

        foreach ($conditions as $condition) {
            $permission->conditions()->create([
                'attribute'   => $condition['attribute'],
                'operator'    => $condition['operator'],
                'value'       => $condition['value'],
                'description' => $condition['description'] ?? null,
            ]);
        }
    }

    public function groups(): Collection
    {
        return Permission::whereNotNull('group')
            ->distinct()
            ->pluck('group')
            ->sort()
            ->values();
    }
}
