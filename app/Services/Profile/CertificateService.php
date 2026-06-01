<?php

namespace App\Services\Profile;

use App\Models\Certificate;
use App\Models\Profile;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CertificateService
{
    public function allByProfile(Profile $profile): Collection
    {
        $rows = Cache::tags([CacheKeys::profileTag($profile->id)])
            ->remember(
                CacheKeys::certificates($profile->id),
                CacheKeys::TTL_MEDIUM,
                fn() => $profile->certificates()->get()->map(fn($m) => $m->getAttributes())->all()
            );

        return Certificate::hydrate($rows);
    }

    public function find(int $id): Certificate
    {
        return Certificate::findOrFail($id);
    }

    public function create(Profile $profile, array $data): Certificate
    {
        return $profile->certificates()->create([
            'title' => $data['title'],
            'issuer' => $data['issuer'],
            'issued_at' => $data['issued_at'],
            'expired_at' => $data['expired_at'] ?? null,
            'credential_url' => $data['credential_url'] ?? null,
            'image' => $data['image'] ?? null,
            'order' => $data['order'] ?? 0,
        ]);
    }

    public function update(Certificate $certificate, array $data): Certificate
    {
        $fillable = [
            'title',
            'issuer',
            'issued_at',
            'expired_at',
            'credential_url',
            'image',
            'order',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $certificate->$field = $data[$field];
            }
        }

        $certificate->save();

        Cache::tags([CacheKeys::profileTag($certificate->profile_id)])->flush();

        return $certificate->fresh();
    }

    public function delete(Certificate $certificate): bool
    {
        Cache::tags([CacheKeys::profileTag($certificate->profile_id)])->flush();

        return (bool) $certificate->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Certificate::where('id', $id)->update(['order' => $order]);
        }
    }
}
