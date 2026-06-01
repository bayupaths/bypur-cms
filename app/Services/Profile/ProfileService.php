<?php

namespace App\Services\Profile;

use App\Models\Certificate;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Service;
use App\Models\Skill;
use App\Models\User;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function findByUser(int $userId): ?Profile
    {
        $attrs = Cache::tags([CacheKeys::profilesTag()])
            ->remember(
                CacheKeys::profileByUser($userId),
                CacheKeys::TTL_LONG,
                fn() => Profile::where('user_id', $userId)->first()?->getAttributes()
            );

        return $attrs ? Profile::hydrate([$attrs])->first() : null;
    }

    public function findByUserOrFail(int $userId): Profile
    {
        $attrs = Cache::tags([CacheKeys::profilesTag()])
            ->remember(
                CacheKeys::profileByUser($userId),
                CacheKeys::TTL_LONG,
                fn() => Profile::where('user_id', $userId)->firstOrFail()->getAttributes()
            );

        return Profile::hydrate([$attrs])->first();
    }

    /**
     * Find or create a profile for the given user.
     * Pre-fills name from the user record so it's never empty.
     */
    public function findOrCreate(User $user): Profile
    {
        $attrs = Cache::tags([CacheKeys::profilesTag()])
            ->remember(
                CacheKeys::profileByUser($user->id),
                CacheKeys::TTL_LONG,
                fn() => Profile::firstOrCreate(
                    ['user_id' => $user->id],
                    ['name' => $user->name]
                )->getAttributes()
            );

        return Profile::hydrate([$attrs])->first();
    }

    /**
     * Get the first profile (for public API endpoints).
     */
    public function getFirst(): Profile
    {
        $attrs = Cache::tags([CacheKeys::profilesTag()])
            ->remember(
                CacheKeys::profileFirst(),
                CacheKeys::TTL_LONG,
                fn() => Profile::orderBy('id')->firstOrFail()->getAttributes()
            );

        return Profile::hydrate([$attrs])->first();
    }

    /**
     * Get the first profile with all public relations (for public API endpoints).
     */
    public function getFirstWithRelations(): Profile
    {
        $data = Cache::tags([CacheKeys::profilesTag()])
            ->remember(
                CacheKeys::profileFirstWithRelations(),
                CacheKeys::TTL_LONG,
                function () {
                    $profile = Profile::with([
                        'experiences',
                        'educations',
                        'certificates',
                        'skills',
                        'services',
                    ])->orderBy('id')->firstOrFail();

                    return [
                        'profile' => $profile->getAttributes(),
                        'experiences' => $profile->experiences->map(fn($m) => $m->getAttributes())->all(),
                        'educations' => $profile->educations->map(fn($m) => $m->getAttributes())->all(),
                        'certificates' => $profile->certificates->map(fn($m) => $m->getAttributes())->all(),
                        'services' => $profile->services->map(fn($m) => $m->getAttributes())->all(),
                        'skills' => $profile->skills->map(fn($s) => array_merge(
                            $s->getAttributes(),
                            ['pivot_level' => $s->pivot->getAttribute('level'), 'pivot_order' => $s->pivot->getAttribute('order')]
                        ))->all(),
                    ];
                }
            );

        $profile = Profile::hydrate([$data['profile']])->first();
        $profile->setRelation('experiences', Experience::hydrate($data['experiences']));
        $profile->setRelation('educations', Education::hydrate($data['educations']));
        $profile->setRelation('certificates', Certificate::hydrate($data['certificates']));
        $profile->setRelation('services', Service::hydrate($data['services']));
        $profile->setRelation('skills', Skill::hydrate(
            array_map(fn($row) => array_diff_key($row, ['pivot_level' => '', 'pivot_order' => '']), $data['skills'])
        ));

        return $profile;
    }

    /**
     * Update profile + user fields atomically inside a DB transaction.
     */
    public function updateProfile(User $user, array $data): Profile
    {
        return DB::transaction(function () use ($user, $data) {
            Cache::tags([CacheKeys::profilesTag()])->forget(CacheKeys::profileByUser($user->id));
            Cache::tags([CacheKeys::profilesTag()])->forget(CacheKeys::profileFirst());
            Cache::tags([CacheKeys::profilesTag()])->forget(CacheKeys::profileFirstWithRelations());
            $profile = Profile::firstOrNew(['user_id' => $user->id]);

            $profile->fill([
                'name' => ($data['name'] ?? '') ?: $profile->name ?: $user->name,
                'nickname' => $data['nickname'] ?? $profile->nickname,
                'tagline' => $data['tagline'] ?? $profile->tagline,
                'taglines' => $data['taglines'] ?? $profile->taglines,
                'phone' => $data['phone'] ?? $profile->phone,
                'email' => $data['email'] ?? $profile->email,
                'location' => $data['location'] ?? $profile->location,
                'bio' => $data['bio'] ?? $profile->bio,
                'resume_url' => $data['resume_url'] ?? $profile->resume_url,
                'website_url' => $data['website_url'] ?? $profile->website_url,
                'is_available' => $data['is_available'] ?? $profile->is_available ?? false,
                'gender' => (($data['gender'] ?? null) ?: null) ?? $profile->gender,
                'birth_date' => (($data['birth_date'] ?? null) ?: null) ?? $profile->birth_date,
                'address' => $data['address'] ?? $profile->address,
                'city' => $data['city'] ?? $profile->city,
                'country' => $data['country'] ?? $profile->country,
                'postal_code' => $data['postal_code'] ?? $profile->postal_code,
                'socials' => $data['socials'] ?? $profile->socials,
            ]);

            $profile->save();

            $fresh = $profile->fresh();

            return $fresh;
        });
    }

    /** @deprecated Use updateProfile() instead */
    public function upsert(User $user, array $data): Profile
    {
        $profile = Profile::firstOrNew(['user_id' => $user->id]);

        $profile->fill([
            'name' => $data['name'],
            'tagline' => $data['tagline'] ?? $profile->tagline,
            'avatar' => $data['avatar'] ?? $profile->avatar,
            'phone' => $data['phone'] ?? $profile->phone,
            'email' => $data['email'] ?? $profile->email,
            'location' => $data['location'] ?? $profile->location,
            'bio' => $data['bio'] ?? $profile->bio,
            'resume_url' => $data['resume_url'] ?? $profile->resume_url,
            'is_available' => $data['is_available'] ?? $profile->is_available ?? false,
        ]);

        $profile->save();

        return $profile->fresh();
    }

    public function update(Profile $profile, array $data): Profile
    {
        $fillable = [
            'name',
            'tagline',
            'avatar',
            'phone',
            'email',
            'location',
            'bio',
            'resume_url',
            'is_available',
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
        Cache::tags([CacheKeys::profilesTag()])->forget(CacheKeys::profileByUser($profile->user_id));
        Cache::tags([CacheKeys::profileTag($profile->id)])->flush();

        return (bool) $profile->delete();
    }
}
