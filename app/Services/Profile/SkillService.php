<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SkillService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Skill::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
            )
            ->when($filters['category'] ?? null, fn ($q, $cat) =>
                $q->where('category', $cat)
            )
            ->orderBy('order')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Skill::orderBy('order')->get();
    }

    public function find(int $id): Skill
    {
        return Skill::findOrFail($id);
    }

    public function create(array $data): Skill
    {
        return Skill::create([
            'name'     => $data['name'],
            'slug'     => $data['slug'],
            'icon'     => $data['icon'] ?? null,
            'category' => $data['category'] ?? null,
            'level'    => $data['level'] ?? null,
            'order'    => $data['order'] ?? 0,
        ]);
    }

    public function update(Skill $skill, array $data): Skill
    {
        $fillable = ['name', 'slug', 'icon', 'category', 'level', 'order'];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $skill->$field = $data[$field];
            }
        }

        $skill->save();

        return $skill->fresh();
    }

    public function delete(Skill $skill): bool
    {
        return (bool) $skill->delete();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $order => $id) {
            Skill::where('id', $id)->update(['order' => $order]);
        }
    }

    public function syncToProfile(Profile $profile, array $skills): void
    {
        // $skills = [['id' => 1, 'level' => 3, 'order' => 0], ...]
        $sync = [];
        foreach ($skills as $skill) {
            $sync[$skill['id']] = [
                'level' => $skill['level'] ?? null,
                'order' => $skill['order'] ?? 0,
            ];
        }

        $profile->skills()->sync($sync);
    }
}
