<?php

namespace App\Services\Page;

use App\Models\Page;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

class SectionService
{
    public function allByPage(Page $page): Collection
    {
        return $page->sections()->get();
    }

    public function find(int $id): Section
    {
        return Section::findOrFail($id);
    }

    public function create(Page $page, array $data): Section
    {
        return $page->sections()->create([
            'type'        => $data['type'] ?? 'hero',
            'heading'     => $data['heading'] ?? null,
            'subheading'  => $data['subheading'] ?? null,
            'cta_label'   => $data['cta_label'] ?? null,
            'cta_url'     => $data['cta_url'] ?? null,
            'image'       => $data['image'] ?? null,
            'content'     => $data['content'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
            'order'       => $data['order'] ?? 0,
        ]);
    }

    public function update(Section $section, array $data): Section
    {
        $fillable = [
            'type', 'heading', 'subheading', 'cta_label',
            'cta_url', 'image', 'content', 'is_active', 'order',
        ];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $section->$field = $data[$field];
            }
        }

        $section->save();

        return $section->fresh();
    }

    public function delete(Section $section): bool
    {
        return (bool) $section->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Section::where('id', $id)->update(['order' => $order]);
        }
    }
}
