<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
            )
            ->with('roles')
            ->paginate($perPage);
    }

    public function find(int $id): array
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($id);

        return array_merge($user->toArray(), [
            'roles_list' => $user->roles->map(fn ($r) => [
                'id'           => $r->id,
                'name'         => $r->name,
                'display_name' => $r->display_name,
            ]),
        ]);
    }

    public function create(array $data): User
    {
        $user = User::create([
            'name'          => $data['name'],
            'username'      => $data['username'] ?? null,
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            // Profile
            'avatar'        => $data['avatar'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'gender'        => $data['gender'] ?? null,
            'birth_date'    => $data['birth_date'] ?? null,
            'bio'           => $data['bio'] ?? null,
            // Address
            'address'       => $data['address'] ?? null,
            'city'          => $data['city'] ?? null,
            'country'       => $data['country'] ?? 'Indonesia',
            'postal_code'   => $data['postal_code'] ?? null,
            // Social
            'website'       => $data['website'] ?? null,
            'github'        => $data['github'] ?? null,
            'linkedin'      => $data['linkedin'] ?? null,
            'twitter'       => $data['twitter'] ?? null,
            'instagram'     => $data['instagram'] ?? null,
            // Status
            'is_active'     => $data['is_active'] ?? true,
            'is_superadmin' => $data['is_superadmin'] ?? false,
            // Misc
            'meta'          => $data['meta'] ?? null,
        ]);

        if (! empty($data['roles'])) {
            $user->assignRole($data['roles']);
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $nullable = [
            'username', 'avatar', 'phone', 'gender', 'birth_date', 'bio',
            'address', 'city', 'country', 'postal_code',
            'website', 'github', 'linkedin', 'twitter', 'instagram',
            'meta',
        ];

        $payload = array_filter([
            'name'  => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
        ], fn ($v) => $v !== null);

        foreach ($nullable as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        foreach (['is_active', 'is_superadmin'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $payload[$flag] = $data[$flag];
            }
        }

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles']);
        }

        return $user->fresh(['roles']);
    }

    public function delete(User $user): bool
    {
        abort_if(Auth::id() === $user->id, 403, 'Anda tidak dapat menghapus akun sendiri.');

        return (bool) $user->delete();
    }

    public function all(): Collection
    {
        return User::with('roles')->get();
    }
}
