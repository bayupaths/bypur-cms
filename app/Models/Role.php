<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'guard_name',
        'level',
        'is_system',
    ];

    protected $casts = [
        'level'     => 'integer',
        'is_system' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot('expires_at');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Tambahkan satu atau banyak permission ke role ini.
     *
     * @param  string|int|Permission|array<string|int|Permission>  $permissions
     */
    public function givePermission(string|int|Permission|array $permissions): static
    {
        $ids = $this->resolvePermissionIds($permissions);
        $this->permissions()->syncWithoutDetaching($ids);

        return $this;
    }

    /**
     * Cabut satu atau banyak permission dari role ini.
     *
     * @param  string|int|Permission|array<string|int|Permission>  $permissions
     */
    public function revokePermission(string|int|Permission|array $permissions): static
    {
        $ids = $this->resolvePermissionIds($permissions);
        $this->permissions()->detach($ids);

        return $this;
    }

    public function hasPermission(string|Permission $permission): bool
    {
        $name = $permission instanceof Permission ? $permission->name : $permission;

        return $this->permissions->contains('name', $name);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeGuard($query, string $guard = 'web')
    {
        return $query->where('guard_name', $guard);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

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
