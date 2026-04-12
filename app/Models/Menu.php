<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'group_id',
        'parent_id',
        'title',
        'slug',
        'url',
        'is_route',
        'icon',
        'badge',
        'badge_variant',
        'target',
        'order',
        'is_active',
        'is_divider',
    ];

    protected $casts = [
        'is_route'   => 'boolean',
        'is_active'  => 'boolean',
        'is_divider' => 'boolean',
        'order'      => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'group_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /** Recursive: semua keturunan menu (nested). */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'menu_roles');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'menu_permissions');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Resolve URL:
     * jika is_route maka generate URL dari route name,
     * jika tidak kembalikan url langsung.
     */
    public function resolveUrl(array $params = []): ?string
    {
        if (! $this->url) {
            return null;
        }

        if ($this->is_route) {
            return route($this->url, $params);
        }

        return $this->url;
    }

    /**
     * Periksa apakah menu ini dapat diakses oleh user berdasarkan role/permission.
     * Jika tidak ada role/permission terdaftar, menu dianggap publik.
     */
    public function isAccessibleBy(User $user): bool
    {
        $hasRoles       = $this->roles()->exists();
        $hasPermissions = $this->permissions()->exists();

        if (! $hasRoles && ! $hasPermissions) {
            return true;
        }

        if ($hasRoles) {
            $roleNames = $this->roles->pluck('name')->all();
            if ($user->hasAnyRole($roleNames)) {
                return true;
            }
        }

        if ($hasPermissions) {
            foreach ($this->permissions as $permission) {
                if ($user->can($permission->name)) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Tambah role yang boleh mengakses menu ini. */
    public function allowRoles(string|int|Role|array $roles): static
    {
        $ids = $this->resolveRoleIds($roles);
        $this->roles()->syncWithoutDetaching($ids);

        return $this;
    }

    /** Tambah permission yang diperlukan untuk mengakses menu ini. */
    public function requirePermissions(string|int|Permission|array $permissions): static
    {
        $ids = $this->resolvePermissionIds($permissions);
        $this->permissions()->syncWithoutDetaching($ids);

        return $this;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function scopeForGroup($query, string $groupName)
    {
        return $query->whereHas('group', fn($q) => $q->where('name', $groupName));
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

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
