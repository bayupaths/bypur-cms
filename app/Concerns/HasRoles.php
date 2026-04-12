<?php

namespace App\Concerns;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot('expires_at');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('granted', 'expires_at');
    }

    // -------------------------------------------------------------------------
    // Role management
    // -------------------------------------------------------------------------
    public function assignRole(string|int|Role|array $roles): static
    {
        $ids = $this->resolveRoleIds($roles);
        $this->roles()->syncWithoutDetaching($ids);

        return $this;
    }

    public function removeRole(string|int|Role|array $roles): static
    {
        $ids = $this->resolveRoleIds($roles);
        $this->roles()->detach($ids);

        return $this;
    }

    public function syncRoles(string|int|Role|array $roles): static
    {
        $ids = $this->resolveRoleIds(is_array($roles) ? $roles : [$roles]);
        $this->roles()->sync($ids);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Permission management (direct)
    // -------------------------------------------------------------------------
    public function givePermission(string|int|Permission|array $permissions): static
    {
        $ids = $this->resolvePermissionIds($permissions);

        foreach ($ids as $id) {
            $this->permissions()->syncWithoutDetaching([
                $id => ['granted' => true],
            ]);
        }

        return $this;
    }

    public function denyPermission(string|int|Permission|array $permissions): static
    {
        $ids = $this->resolvePermissionIds($permissions);

        foreach ($ids as $id) {
            $this->permissions()->syncWithoutDetaching([
                $id => ['granted' => false],
            ]);
        }

        return $this;
    }

    public function revokePermission(string|int|Permission|array $permissions): static
    {
        $ids = $this->resolvePermissionIds($permissions);
        $this->permissions()->detach($ids);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Checks
    // -------------------------------------------------------------------------
    public function hasRole(string|Role $role): bool
    {
        $name = $role instanceof Role ? $role->name : $role;

        return $this->roles->contains('name', $name);
    }

    public function hasAnyRole(array $roles): bool
    {
        return collect($roles)->some(fn($role) => $this->hasRole($role));
    }

    public function hasAllRoles(array $roles): bool
    {
        return collect($roles)->every(fn($role) => $this->hasRole($role));
    }

    /**
     * Periksa apakah user memiliki permission (RBAC + ABAC).
     *
     * @param  string|Permission  $permission
     * @param  array<string, mixed>  $context  Konteks ABAC opsional
     */
    public function can($permission, $context = []): bool
    {
        $name = $permission instanceof Permission ? $permission->name : $permission;

        // 1. Cek direct permission yang belum expired
        $direct = $this->permissions()
            ->where('permissions.name', $name)
            ->first();

        if ($direct) {
            $pivot = $direct->pivot;

            if ($pivot->expires_at && now()->isAfter($pivot->expires_at)) {
                // sudah expired, abaikan
            } elseif (! $pivot->granted) {
                return false; // explicit deny
            } else {
                return $this->checkAbac($direct, $context);
            }
        }

        // 2. Cek via roles yang belum expired
        $activeRoleIds = $this->roles()
            ->where(function ($q) {
                $q->whereNull('user_roles.expires_at')
                    ->orWhere('user_roles.expires_at', '>', now());
            })
            ->pluck('roles.id');

        $viaRole = Permission::where('name', $name)
            ->whereHas('roles', fn($q) => $q->whereIn('roles.id', $activeRoleIds))
            ->first();

        if ($viaRole) {
            return $this->checkAbac($viaRole, $context);
        }

        return false;
    }

    public function cannot($permission, $context = []): bool
    {
        return ! $this->can($permission, $context);
    }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------
    /** Semua permission efektif (langsung + via role), tanpa duplikat. */
    public function allPermissions(): Collection
    {
        $direct = $this->permissions()->wherePivot('granted', true)->get();

        $viaRoles = Permission::whereHas('roles', function ($q) {
            $activeRoleIds = $this->roles()
                ->where(function ($q) {
                    $q->whereNull('user_roles.expires_at')
                        ->orWhere('user_roles.expires_at', '>', now());
                })
                ->pluck('roles.id');

            $q->whereIn('roles.id', $activeRoleIds);
        })->get();

        return $direct->merge($viaRoles)->unique('id')->values();
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------
    private function checkAbac(Permission $permission, array $context): bool
    {
        if ($permission->relationLoaded('conditions')) {
            return $permission->checkConditions($context);
        }

        return $permission->load('conditions')->checkConditions($context);
    }

    /** @param  string|int|Role|array<string|int|Role>  $roles */
    private function resolveRoleIds(string|int|Role|array $roles): array
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return collect($roles)->map(function ($role) {
            if ($role instanceof Role) {
                return $role->id;
            }

            if (is_string($role)) {
                return Role::where('name', $role)->firstOrFail()->id;
            }

            return $role;
        })->all();
    }

    /** @param  string|int|Permission|array<string|int|Permission>  $permissions */
    private function resolvePermissionIds(string|int|Permission|array $permissions): array
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

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
