<?php

namespace App\Services\Page;

use App\Models\Page;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class PageService
{
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return Page::query()
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
            )
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id): Page
    {
        return Page::with(['sections', 'faqs'])->findOrFail($id);
    }

    public function create(User $user, array $data): Page
    {
        return Page::create([
            'user_id'    => $user->id,
            'slug'       => $data['slug'],
            'title'      => $data['title'],
            'content'    => $data['content'] ?? null,
            'status'     => $data['status'] ?? 'draft',
            'meta_title' => $data['meta_title'] ?? null,
            'meta_desc'  => $data['meta_desc'] ?? null,
        ]);
    }

    public function update(Page $page, array $data): Page
    {
        $fillable = ['slug', 'title', 'content', 'status', 'meta_title', 'meta_desc'];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $page->$field = $data[$field];
            }
        }

        $page->save();

        return $page->fresh();
    }

    public function delete(Page $page): bool
    {
        return (bool) $page->delete();
    }
}
