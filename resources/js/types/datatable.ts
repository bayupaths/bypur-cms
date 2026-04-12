import type { ColumnDef } from '@tanstack/vue-table';

// ─── Response shape dari Laravel DataTableCollection ─────────────────────────

export interface DataTableMeta {
    current_page: number;
    from: number | null;
    last_page: number;
    per_page: number;
    to: number | null;
    total: number;
}

export interface DataTableLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

/**
 * Struktur response paginated datatable dari backend.
 * Cocok dengan output DataTableCollection.php.
 *
 * @example
 *   defineProps<{ users: DataTableResponse<User> }>()
 */
export interface DataTableResponse<TData> {
    data: TData[];
    meta: DataTableMeta;
    links: DataTableLinks;
}

// ─── Request params ke server ─────────────────────────────────────────────────

export interface DataTableParams {
    page: number;
    per_page: number;
    search?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    filters?: Record<string, any>;
    [key: string]: any;
}

// ─── Options untuk useDataTable composable ────────────────────────────────────

export interface UseDataTableOptions<TData> {
    data: TData[] | (() => TData[]);
    columns: ColumnDef<TData, any>[] | (() => ColumnDef<TData, any>[]);
    meta: DataTableMeta | (() => DataTableMeta);
    enableRowSelection?: boolean;
    enableMultiRowSelection?: boolean;
    onServerRequest?: (params: DataTableParams) => void;
}
