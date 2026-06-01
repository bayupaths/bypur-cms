<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Models\Skill;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class SkillService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $page = request()->input('page', 1);
        $cacheKey = CacheKeys::skillsPaginated($page, array_merge(['perPage' => $perPage], $filters));

        $data = Cache::tags([CacheKeys::skillsTag()])
            ->remember(
                $cacheKey,
                CacheKeys::TTL_MEDIUM,
                fn() => Skill::query()
                    ->when(
                        $filters['search'] ?? null,
                        fn($q, $search) =>
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%")
                    )
                    ->when(
                        $filters['category'] ?? null,
                        fn($q, $cat) =>
                        $q->where('category', $cat)
                    )
                    ->orderBy('order')
                    ->paginate($perPage)
                    ->through(fn($skill) => $skill->getAttributes())
                    ->toArray()
            );

        return new LengthAwarePaginator(
            Skill::hydrate($data['data']),
            $data['total'],
            $data['per_page'],
            $data['current_page'],
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function all(): Collection
    {
        $rows = Cache::tags([CacheKeys::skillsTag()])
            ->remember(
                CacheKeys::skillsAll(),
                CacheKeys::TTL_LONG,
                fn() => Skill::orderBy('order')->get()->map(fn($m) => $m->getAttributes())->all()
            );

        return Skill::hydrate($rows);
    }

    public function find(int $id): Skill
    {
        return Skill::findOrFail($id);
    }

    public function create(array $data): Skill
    {
        $skill = Skill::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'icon' => $data['icon'] ?? null,
            'category' => $data['category'] ?? null,
            'level' => $data['level'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);

        Cache::tags([CacheKeys::skillsTag()])->flush();

        return $skill;
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

        Cache::tags([CacheKeys::skillsTag()])->flush();

        return $skill->fresh();
    }

    public function delete(Skill $skill): bool
    {
        Cache::tags([CacheKeys::skillsTag()])->flush();

        return (bool) $skill->delete();
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $order => $id) {
            Skill::where('id', $id)->update(['order' => $order]);
        }

        Cache::tags([CacheKeys::skillsTag()])->flush();
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

        Cache::tags([CacheKeys::profileTag($profile->id)])->flush();
    }
}
