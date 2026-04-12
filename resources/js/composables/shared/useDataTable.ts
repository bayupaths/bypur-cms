import { router } from '@inertiajs/vue3';
import {
    getCoreRowModel,
    useVueTable,
    type ColumnFiltersState,
    type PaginationState,
    type RowSelectionState,
    type SortingState,
    type VisibilityState,
} from '@tanstack/vue-table';
import { computed, nextTick, ref, watch, type Ref } from 'vue';
import { valueUpdater } from '@/lib/utils';
import type {
    DataTableMeta,
    DataTableLinks,
    DataTableResponse,
    DataTableParams,
    UseDataTableOptions,
} from '@/types';

export type { DataTableMeta, DataTableLinks, DataTableResponse, DataTableParams, UseDataTableOptions };

export function useDataTable<TData>(options: UseDataTableOptions<TData>) {
    const {
        data: dataOption,
        columns: columnsOption,
        meta: metaOption,
        enableRowSelection = false,
        enableMultiRowSelection = true,
        onServerRequest,
    } = options;

    // Handle both direct values and getter functions
    const getData = () =>
        typeof dataOption === 'function' ? dataOption() : dataOption;
    const getColumns = () =>
        typeof columnsOption === 'function' ? columnsOption() : columnsOption;
    const getMeta = () =>
        typeof metaOption === 'function' ? metaOption() : metaOption;

    // Local state
    const rowSelection: Ref<RowSelectionState> = ref({});
    const columnVisibility: Ref<VisibilityState> = ref({});
    const columnFilters: Ref<ColumnFiltersState> = ref([]);
    const sorting: Ref<SortingState> = ref([]);
    const globalFilter: Ref<string> = ref('');

    // Server-side pagination state
    const pagination: Ref<PaginationState> = ref({
        pageIndex: getMeta().current_page - 1,
        pageSize: getMeta().per_page,
    });

    // Flag to prevent fetching when pagination is updated from server response
    const isUpdatingFromServer = ref(false);

    // Watch meta changes to update pagination state
    watch(
        () => getMeta(),
        (newMeta, oldMeta) => {
            if (
                oldMeta &&
                oldMeta.current_page === newMeta.current_page &&
                oldMeta.per_page === newMeta.per_page
            ) {
                return;
            }

            isUpdatingFromServer.value = true;
            pagination.value = {
                pageIndex: newMeta.current_page - 1,
                pageSize: newMeta.per_page,
            };
            nextTick(() => {
                isUpdatingFromServer.value = false;
            });
        },
        { deep: true },
    );

    // Create table instance
    const table = useVueTable({
        get data() {
            return getData();
        },
        get columns() {
            return getColumns();
        },
        getCoreRowModel: getCoreRowModel(),
        manualPagination: true,
        manualSorting: true,
        manualFiltering: true,
        get pageCount() {
            return getMeta().last_page;
        },
        getRowId: (row: TData, index: number) => {
            const rowWithId = row as any;
            return rowWithId.id !== undefined
                ? String(rowWithId.id)
                : String(index);
        },
        state: {
            get sorting() {
                return sorting.value;
            },
            get columnFilters() {
                return columnFilters.value;
            },
            get columnVisibility() {
                return columnVisibility.value;
            },
            get rowSelection() {
                return rowSelection.value;
            },
            get globalFilter() {
                return globalFilter.value;
            },
            get pagination() {
                return pagination.value;
            },
        },
        enableRowSelection: enableRowSelection,
        enableMultiRowSelection: enableMultiRowSelection,
        onSortingChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, sorting),
        onColumnFiltersChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, columnFilters),
        onColumnVisibilityChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, columnVisibility),
        onRowSelectionChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, rowSelection),
        onGlobalFilterChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, globalFilter),
        onPaginationChange: (updaterOrValue) =>
            valueUpdater(updaterOrValue, pagination),
    });

    // Build server request params
    const buildServerParams = (): DataTableParams => {
        const params: DataTableParams = {
            page: pagination.value.pageIndex + 1,
            per_page: pagination.value.pageSize,
        };

        const excludedColumns = ['select', 'actions', '_index'];

        if (globalFilter.value) {
            params.search = globalFilter.value;
        }

        if (sorting.value.length > 0) {
            const sortColumn = sorting.value[0].id;
            if (!excludedColumns.includes(sortColumn)) {
                params.sort_by = sortColumn;
                params.sort_order = sorting.value[0].desc ? 'desc' : 'asc';
            }
        }

        if (columnFilters.value.length > 0) {
            params.filters = {};
            columnFilters.value.forEach((filter) => {
                if (params.filters && !excludedColumns.includes(filter.id)) {
                    params.filters[filter.id] = filter.value;
                }
            });
        }

        return params;
    };

    // Make server request
    const fetchData = () => {
        const params = buildServerParams();

        if (onServerRequest) {
            onServerRequest(params);
        } else {
            router.reload({
                data: params,
            });
        }
    };

    // Track if initial load to avoid duplicate request
    const isInitialLoad = ref(true);

    // Watch for changes and trigger server requests
    watch(
        [pagination, sorting, columnFilters, globalFilter],
        (newVal, oldVal) => {
            if (isUpdatingFromServer.value) {
                return;
            }

            if (isInitialLoad.value && oldVal === undefined) {
                isInitialLoad.value = false;
                return;
            }

            if (isInitialLoad.value) {
                isInitialLoad.value = false;
            }

            fetchData();
        },
        { deep: true },
    );

    // Get selected rows
    const selectedRows = computed(() => {
        return table
            .getFilteredSelectedRowModel()
            .rows.map((row) => row.original);
    });

    // Reset selection
    const resetSelection = () => {
        rowSelection.value = {};
    };

    // Watch for data changes to clear selection
    watch(
        () => getData(),
        () => {
            resetSelection();
        },
    );

    return {
        table,
        selectedRows,
        resetSelection,
        globalFilter,
        columnFilters,
        columnVisibility,
        sorting,
        pagination,
        buildServerParams,
        fetchData,
    };
}
