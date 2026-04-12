<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DataTableCollection extends ResourceCollection
{
    /**
     * @param  class-string|null  $collects  The resource class to use for each item.
     */
    public function __construct(mixed $resource, ?string $collects = null)
    {
        if ($collects) {
            $this->collects = $collects;
        }

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    /**
     * Normalise pagination info to match the TypeScript interfaces:
     *
     * DataTableMeta  { current_page, from, last_page, per_page, to, total }
     * DataTableLinks { first, last, prev, next }
     */
    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [
            'meta' => [
                'current_page' => $paginated['current_page'],
                'from'         => $paginated['from'],
                'last_page'    => $paginated['last_page'],
                'per_page'     => $paginated['per_page'],
                'to'           => $paginated['to'],
                'total'        => $paginated['total'],
            ],
            'links' => [
                'first' => $paginated['first_page_url'] ?? null,
                'last'  => $paginated['last_page_url'] ?? null,
                'prev'  => $paginated['prev_page_url'] ?? null,
                'next'  => $paginated['next_page_url'] ?? null,
            ],
        ];
    }
}
