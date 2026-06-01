<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Models\Service;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ServiceService
{
    public function allByProfile(Profile $profile): Collection
    {
        $rows = Cache::tags([CacheKeys::profileTag($profile->id)])
            ->remember(
                CacheKeys::services($profile->id),
                CacheKeys::TTL_MEDIUM,
                fn() => $profile->services()->get()->map(fn($m) => $m->getAttributes())->all()
            );

        return Service::hydrate($rows);
    }

    public function find(int $id): Service
    {
        return Service::findOrFail($id);
    }

    public function create(Profile $profile, array $data): Service
    {
        return $profile->services()->create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'icon' => $data['icon'] ?? null,
            'price_from' => $data['price_from'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'order' => $data['order'] ?? 0,
        ]);
    }

    public function update(Service $service, array $data): Service
    {
        $fillable = [
            'title',
            'slug',
            'description',
            'icon',
            'price_from',
            'is_active',
            'order',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $service->$field = $data[$field];
            }
        }

        $service->save();

        Cache::tags([CacheKeys::profileTag($service->profile_id)])->flush();

        return $service->fresh();
    }

    public function delete(Service $service): bool
    {
        Cache::tags([CacheKeys::profileTag($service->profile_id)])->flush();

        return (bool) $service->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Service::where('id', $id)->update(['order' => $order]);
        }
    }

    public function toggleActive(Service $service): Service
    {
        $service->is_active = !$service->is_active;
        $service->save();

        return $service->fresh();
    }
}
