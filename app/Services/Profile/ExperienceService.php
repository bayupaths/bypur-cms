<?php

namespace App\Services\Profile;

use App\Models\Experience;
use App\Models\Profile;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ExperienceService
{
    public function allByProfile(Profile $profile): Collection
    {
        $rows = Cache::tags([CacheKeys::profileTag($profile->id)])
            ->remember(
                CacheKeys::experiences($profile->id),
                CacheKeys::TTL_MEDIUM,
                fn() => $profile->experiences()->get()->map(fn($m) => $m->getAttributes())->all()
            );

        return Experience::hydrate($rows);
    }

    public function find(int $id): Experience
    {
        return Experience::findOrFail($id);
    }

    public function create(Profile $profile, array $data): Experience
    {
        $experience = $profile->experiences()->create([
            'company' => $data['company'],
            'position' => $data['position'],
            'location' => $data['location'],
            'type' => $data['type'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'] ?? null,
            'is_current' => $data['is_current'] ?? false,
            'description' => $data['description'] ?? null,
            'tech_stack' => $data['tech_stack'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);

        Cache::tags([CacheKeys::profileTag($profile->id)])->flush();

        return $experience;
    }

    public function update(Experience $experience, array $data): Experience
    {
        $fillable = [
            'company',
            'position',
            'location',
            'type',
            'started_at',
            'ended_at',
            'is_current',
            'description',
            'tech_stack',
            'order',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $experience->$field = $data[$field];
            }
        }

        $experience->save();

        Cache::tags([CacheKeys::profileTag($experience->profile_id)])->flush();

        return $experience->fresh();
    }

    public function delete(Experience $experience): bool
    {
        Cache::tags([CacheKeys::profileTag($experience->profile_id)])->flush();

        return (bool) $experience->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Experience::where('id', $id)->update(['order' => $order]);
        }

        // Resolve profile_id from first record to flush cache
        if (!empty($orderedIds)) {
            $profileId = Experience::find(reset($orderedIds))?->profile_id;
            if ($profileId) {
                Cache::tags([CacheKeys::profileTag($profileId)])->flush();
            }
        }
    }
}
