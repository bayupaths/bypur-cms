<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait HasDataTable
{
    /**
     * Build a server-side datatable response from a query.
     *
     * Handles: search, sort, column filters, and pagination.
     * Returns a LengthAwarePaginator — wrap result with Resource::collection().
     *
     * @param  Builder  $query         Eloquent query builder
     * @param  Request  $request       Incoming request
     * @param  array    $searchable    Column names to search against (for global search)
     * @return LengthAwarePaginator
     */
    protected function dataTable(
        Builder $query,
        Request $request,
        array $searchable = [],
    ): LengthAwarePaginator {
        // Global search
        if ($request->filled('search') && ! empty($searchable)) {
            $search = $request->string('search')->trim();

            $query->where(function (Builder $q) use ($search, $searchable) {
                foreach ($searchable as $column) {
                    if (str_contains($column, '.')) {
                        // Support relationship: e.g. 'profile.bio'
                        [$relation, $col] = explode('.', $column, 2);
                        $q->orWhereHas($relation, fn (Builder $r) => $r->where($col, 'like', "%{$search}%"));
                    } else {
                        $q->orWhere($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortBy    = $request->string('sort_by')->toString();
            $sortOrder = $request->input('sort_order', 'asc') === 'desc' ? 'desc' : 'asc';

            // Only allow sorting on actual columns to prevent SQL injection
            $allowedColumns = $this->getAllowedSortColumns($query);
            if (in_array($sortBy, $allowedColumns, true)) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        // Column filters  { filters: { status: 'active', role: 'admin' } }
        if ($request->filled('filters') && is_array($request->input('filters'))) {
            foreach ($request->input('filters') as $column => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min($perPage, 100)); // clamp between 1–100

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get column names from the query's model table.
     * Used to whitelist sortable columns (prevent SQL injection).
     */
    private function getAllowedSortColumns(Builder $query): array
    {
        try {
            return $query->getModel()->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($query->getModel()->getTable());
        } catch (\Throwable) {
            return [];
        }
    }
}
