<?php

namespace App\Services\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public function paginate(int $perPage = 24, array $filters = []): LengthAwarePaginator
    {
        return Media::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
            )
            ->when($filters['collection'] ?? null, fn ($q, $collection) =>
                $q->where('collection_name', $collection)
            )
            ->when($filters['mime'] ?? null, fn ($q, $mime) =>
                $q->where('mime_type', 'like', "{$mime}%")
            )
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): Media
    {
        return Media::findOrFail($id);
    }

    public function upload(HasMedia $model, UploadedFile $file, array $options = []): Media
    {
        $collection = $options['collection'] ?? 'default';

        return $model
            ->addMedia($file)
            ->withCustomProperties([
                'alt'    => $options['alt'] ?? null,
                'folder' => $options['folder'] ?? 'general',
            ])
            ->usingName($options['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->toMediaCollection($collection);
    }

    public function update(Media $media, array $data): Media
    {
        if (array_key_exists('name', $data)) {
            $media->name = $data['name'];
        }

        if (array_key_exists('alt', $data)) {
            $media->setCustomProperty('alt', $data['alt']);
        }

        if (array_key_exists('folder', $data)) {
            $media->setCustomProperty('folder', $data['folder']);
        }

        $media->save();

        return $media->fresh();
    }

    public function delete(Media $media): bool
    {
        $media->delete();

        return true;
    }
}
