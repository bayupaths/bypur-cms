<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function findByUser(int $userId): ?Profile
    {
        return Profile::where('user_id', $userId)->first();
    }

    public function findByUserOrFail(int $userId): Profile
    {
        return Profile::where('user_id', $userId)->firstOrFail();
    }

    /**
     * Find or create a profile for the given user.
     * Pre-fills name from the user record so it's never empty.
     */
    public function findOrCreate(User $user): Profile
    {
        return Profile::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => $user->name]
        );
    }

    /**
     * Update profile + user fields atomically inside a DB transaction.
     */
    public function updateProfile(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data) {
            $profile = Profile::firstOrNew(['user_id' => $user->id]);

            $profile->fill([
                'name'         => ($data['name'] ?? '') ?: $profile->name ?: $user->name,
                'nickname'     => $data['nickname'] ?? $profile->nickname,
                'tagline'      => $data['tagline'] ?? $profile->tagline,
                'taglines'     => $data['taglines'] ?? $profile->taglines,
                'phone'        => $data['phone'] ?? $profile->phone,
                'email'        => $data['email'] ?? $profile->email,
                'location'     => $data['location'] ?? $profile->location,
                'bio'          => $data['bio'] ?? $profile->bio,
                'resume_url'   => $data['resume_url'] ?? $profile->resume_url,
                'website_url'  => $data['website_url'] ?? $profile->website_url,
                'is_available' => $data['is_available'] ?? $profile->is_available ?? false,
                'gender'       => (($data['gender'] ?? null) ?: null) ?? $profile->gender,
                'birth_date'   => (($data['birth_date'] ?? null) ?: null) ?? $profile->birth_date,
                'address'      => $data['address'] ?? $profile->address,
                'city'         => $data['city'] ?? $profile->city,
                'country'      => $data['country'] ?? $profile->country,
                'postal_code'  => $data['postal_code'] ?? $profile->postal_code,
                'socials'      => $data['socials'] ?? $profile->socials,
            ]);

            $profile->save();

            return $profile->fresh();
        });
    }

    /** @deprecated Use updateProfile() instead */
    public function upsert(User $user, array $data): Profile
    {
        $profile = Profile::firstOrNew(['user_id' => $user->id]);

        $profile->fill([
            'name'         => $data['name'],
            'tagline'      => $data['tagline'] ?? $profile->tagline,
            'avatar'       => $data['avatar'] ?? $profile->avatar,
            'phone'        => $data['phone'] ?? $profile->phone,
            'email'        => $data['email'] ?? $profile->email,
            'location'     => $data['location'] ?? $profile->location,
            'bio'          => $data['bio'] ?? $profile->bio,
            'resume_url'   => $data['resume_url'] ?? $profile->resume_url,
            'is_available' => $data['is_available'] ?? $profile->is_available ?? false,
        ]);

        $profile->save();

        return $profile->fresh();
    }

    public function update(Profile $profile, array $data): Profile
    {
        $fillable = [
            'name', 'tagline', 'avatar', 'phone', 'email',
            'location', 'bio', 'resume_url', 'is_available',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $profile->$field = $data[$field];
            }
        }

        $profile->save();

        return $profile->fresh();
    }

    public function delete(Profile $profile): bool
    {
        return (bool) $profile->delete();
    }
}
