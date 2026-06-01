<?php

namespace App\Services\Profile;

use App\Models\Education;
use App\Models\Profile;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EducationService
{
    public function allByProfile(Profile $profile): Collection
    {
        $rows = Cache::tags([CacheKeys::profileTag($profile->id)])
            ->remember(
                CacheKeys::educations($profile->id),
                CacheKeys::TTL_MEDIUM,
                fn() => $profile->educations()->get()->map(fn($m) => $m->getAttributes())->all()
            );

        return Education::hydrate($rows);
    }

    public function find(int $id): Education
    {
        return Education::findOrFail($id);
    }

    public function create(Profile $profile, array $data): Education
    {
        return $profile->educations()->create([
            'institution' => $data['institution'],
            'degree' => $data['degree'],
            'field' => $data['field'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'] ?? null,
            'is_current' => $data['is_current'] ?? false,
            'description' => $data['description'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);
    }

    public function update(Education $education, array $data): Education
    {
        $fillable = [
            'institution',
            'degree',
            'field',
            'started_at',
            'ended_at',
            'is_current',
            'description',
            'order',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $education->$field = $data[$field];
            }
        }

        $education->save();

        Cache::tags([CacheKeys::profileTag($education->profile_id)])->flush();

        return $education->fresh();
    }

    public function delete(Education $education): bool
    {
        Cache::tags([CacheKeys::profileTag($education->profile_id)])->flush();

        return (bool) $education->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Education::where('id', $id)->update(['order' => $order]);
        }
    }
}
