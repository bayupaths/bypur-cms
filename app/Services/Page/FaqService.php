<?php

namespace App\Services\Page;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    public function allByPage(Page $page): Collection
    {
        return $page->faqs()->get();
    }

    public function find(int $id): Faq
    {
        return Faq::findOrFail($id);
    }

    public function create(Page $page, array $data): Faq
    {
        return $page->faqs()->create([
            'question' => $data['question'],
            'answer'   => $data['answer'],
            'is_active' => $data['is_active'] ?? true,
            'order'    => $data['order'] ?? 0,
        ]);
    }

    public function update(Faq $faq, array $data): Faq
    {
        $fillable = ['question', 'answer', 'is_active', 'order'];

        foreach ($fillable as $field) {
            if (array_key_exists($field, $data)) {
                $faq->$field = $data[$field];
            }
        }

        $faq->save();

        return $faq->fresh();
    }

    public function delete(Faq $faq): bool
    {
        return (bool) $faq->delete();
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $order => $id) {
            Faq::where('id', $id)->update(['order' => $order]);
        }
    }
}
