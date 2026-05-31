<?php

namespace App\Services\Profile;

use App\Models\Experience;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Collection;

class ExperienceService
{
    public function allByProfile(Profile $profile): Collection
    {
        return $profile->experiences()->get();
    }

    public function find(int $id): Experience
    {
        return Experience::findOrFail($id);
    }

    public function create(Profile $profile, array $data): Experience
    {
        return $profile->experiences()->create([
            'company'     => $data['company'],
            'position'    => $data['position'],
            'location'    => $data['location'],
            'type'        => $data['type'],
            'started_at'  => $data['started_at'],
            'ended_at'    => $data['ended_at'] ?? null,
            'is_current'  => $data['is_current'] ?? false,
            'description' => $data['description'] ?? null,
            'tech_stack'  => $data['tech_stack'] ?? null,
            'order'       => $data['order'] ?? 0,
        ]);
    }

    public function update(Experience $experience, array $data): Experience
    {
        $fillable = [
            'company', 'position', 'location', 'type',
            'started_at', 'ended_at', 'is_current', 'description', 'tech_stack', 'order',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $experience->$field = $data[$field];
            }
        }

        $experience->save();

        return $experience->fresh();
    }

    public function delete(Experience $experience): bool
    {
        return (bool) $experience->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Experience::where('id', $id)->update(['order' => $order]);
        }
    }
}
