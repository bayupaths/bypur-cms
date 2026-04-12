<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasRoles;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name', 'username', 'email', 'password',
    // Profile
    'avatar', 'phone', 'gender', 'birth_date', 'bio',
    // Address
    'address', 'city', 'country', 'postal_code',
    // Social
    'website', 'github', 'linkedin', 'twitter', 'instagram',
    // Status
    'is_active', 'is_superadmin',
    // Security
    'last_login_at', 'last_login_ip', 'login_attempts', 'locked_until',
    // Misc
    'meta',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date'        => 'date',
            'last_login_at'     => 'datetime',
            'locked_until'      => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'is_superadmin'     => 'boolean',
            'meta'              => 'array',
        ];
    }
}
