<script setup lang="ts">
import type { Table } from '@tanstack/vue-table';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { DataTableMeta } from '@/composables/shared/useDataTable';


interface DataTablePaginationProps {
    table: Table<any>;
    pageSizeOptions?: number[];
    serverSide?: boolean;
    meta?: DataTableMeta;
}

const props = withDefaults(defineProps<DataTablePaginationProps>(), {
    pageSizeOptions: () => [10, 20, 30, 40, 50],
    serverSide: false,
});

// Computed untuk menampilkan info yang berbeda antara client-side dan server-side
const selectedRowsCount = computed(() => {
    return props.table.getFilteredSelectedRowModel().rows.length;
});

const totalRowsCount = computed(() => {
    if (props.serverSide && props.meta) {
        return props.meta.total;
    }
    return props.table.getFilteredRowModel().rows.length;
});

const currentPage = computed(() => {
    return props.table.getState().pagination.pageIndex + 1;
});

const totalPages = computed(() => {
    if (props.serverSide && props.meta) {
        return props.meta.last_page;
    }
    return props.table.getPageCount();
});

const fromItem = computed(() => {
    if (props.serverSide && props.meta) {
        return props.meta.from ?? 0;
    }
    const pageSize = props.table.getState().pagination.pageSize;
    const pageIndex = props.table.getState().pagination.pageIndex;
    return pageIndex * pageSize + 1;
});

const toItem = computed(() => {
    if (props.serverSide && props.meta) {
        return props.meta.to ?? 0;
    }
    const pageSize = props.table.getState().pagination.pageSize;
    const pageIndex = props.table.getState().pagination.pageIndex;
    const totalRows = totalRowsCount.value;
    return Math.min((pageIndex + 1) * pageSize, totalRows);
});
</script>

<template>
    <div class="flex flex-col gap-3 px-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-muted-foreground">
            <template v-if="serverSide">
                {{ selectedRowsCount }} of {{ totalRowsCount }} row(s) selected.
                <span class="ml-2"
                    >Showing {{ fromItem }} to {{ toItem }} of
                    {{ totalRowsCount }} results.</span
                >
            </template>
            <template v-else>
                {{ selectedRowsCount }} of {{ totalRowsCount }} row(s) selected.
            </template>
        </div>
        <div class="flex flex-wrap items-center gap-4 sm:gap-6 lg:gap-8">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium">Rows per page</p>
                <Select
                    :model-value="`${table.getState().pagination.pageSize}`"
                    @update:model-value="table.setPageSize(Number($event))"
                >
                    <SelectTrigger class="h-8 w-17.5">
                        <SelectValue
                            :placeholder="`${table.getState().pagination.pageSize}`"
                        />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem
                            v-for="pageSize in pageSizeOptions"
                            :key="pageSize"
                            :value="`${pageSize}`"
                        >
                            {{ pageSize }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div
                class="flex w-25 items-center justify-center text-sm font-medium"
            >
                Page {{ currentPage }} of {{ totalPages }}
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    class="hidden h-8 w-8 p-0 lg:flex"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.setPageIndex(0)"
                >
                    <span class="sr-only">Go to first page</span>
                    <ChevronsLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    class="h-8 w-8 p-0"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    <span class="sr-only">Go to previous page</span>
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    class="h-8 w-8 p-0"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    <span class="sr-only">Go to next page</span>
                    <ChevronRight class="h-4 w-4" />
                </Button>
                <Button
                    variant="outline"
                    class="hidden h-8 w-8 p-0 lg:flex"
                    :disabled="!table.getCanNextPage()"
                    @click="table.setPageIndex(table.getPageCount() - 1)"
                >
                    <span class="sr-only">Go to last page</span>
                    <ChevronsRight class="h-4 w-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
