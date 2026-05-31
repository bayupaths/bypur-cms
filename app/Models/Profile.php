<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'nickname',
        'tagline',
        'taglines',
        'avatar',
        'phone',
        'email',
        'location',
        'bio',
        'resume_url',
        'website_url',
        'is_available',
        // Personal
        'gender',
        'birth_date',
        // Address
        'address',
        'city',
        'country',
        'postal_code',
        // Social
        'socials',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'taglines'     => 'array',
            'birth_date'   => 'date',
            'socials'      => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class)->orderBy('order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('order');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class)->orderBy('order');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('order');
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'profile_skill')
            ->withPivot('level', 'order')
            ->withTimestamps()
            ->orderByPivot('order');
    }
}
