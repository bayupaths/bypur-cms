<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'group',
        'guard_name',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot('granted', 'expires_at')
            ->withTimestamps();
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(PermissionCondition::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Periksa apakah semua kondisi ABAC terpenuhi untuk konteks yang diberikan.
     *
     * @param  array<string, mixed>  $context  e.g. ['user.department' => 'IT', 'resource.owner_id' => 5]
     */
    public function checkConditions(array $context): bool
    {
        foreach ($this->conditions as $condition) {
            if (! $condition->evaluate($context)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeGuard($query, string $guard = 'web')
    {
        return $query->where('guard_name', $guard);
    }
}
