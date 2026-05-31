<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceService
{
    public function allByProfile(Profile $profile): Collection
    {
        return $profile->services()->get();
    }

    public function find(int $id): Service
    {
        return Service::findOrFail($id);
    }

    public function create(Profile $profile, array $data): Service
    {
        return $profile->services()->create([
            'title'       => $data['title'],
            'slug'        => $data['slug'],
            'description' => $data['description'],
            'icon'        => $data['icon'] ?? null,
            'price_from'  => $data['price_from'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
            'order'       => $data['order'] ?? 0,
        ]);
    }

    public function update(Service $service, array $data): Service
    {
        $fillable = [
            'title', 'slug', 'description', 'icon',
            'price_from', 'is_active', 'order',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $service->$field = $data[$field];
            }
        }

        $service->save();

        return $service->fresh();
    }

    public function delete(Service $service): bool
    {
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
        $service->is_active = ! $service->is_active;
        $service->save();

        return $service->fresh();
    }
}
