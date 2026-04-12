<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuGroup extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'group_id');
    }

    /** Hanya root menu (tanpa parent), diurutkan berdasarkan order. */
    public function rootMenus(): HasMany
    {
        return $this->hasMany(Menu::class, 'group_id')
            ->whereNull('parent_id')
            ->orderBy('order');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
