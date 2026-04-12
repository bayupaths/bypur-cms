<script setup lang="ts" generic="TData">
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender } from '@tanstack/vue-table';
import { watch } from 'vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    useDataTable,
    type DataTableMeta,
} from '@/composables/shared/useDataTable';
import DataTablePagination from './DataTablePagination.vue';
import DataTableToolbar from './DataTableToolbar.vue';


interface DataTableProps {
    columns: ColumnDef<TData, any>[];
    data: TData[];
    meta: DataTableMeta;
    searchPlaceholder?: string;
    pageSize?: number;
    enableRowSelection?: boolean;
    enableMultiRowSelection?: boolean;
    enableColumnVisibility?: boolean;
    enableToolbar?: boolean;
    loading?: boolean;
}

const props = withDefaults(defineProps<DataTableProps>(), {
    searchPlaceholder: 'Search...',
    pageSize: 10,
    enableRowSelection: false,
    enableMultiRowSelection: true,
    enableColumnVisibility: true,
    enableToolbar: true,
    loading: false,
});

const emit = defineEmits<{
    rowSelectionChange: [selectedRows: TData[]];
    serverRequest: [params: any];
}>();

// Handler untuk server request
const handleServerRequest = (params: any) => {
    emit('serverRequest', params);
};

const { table, selectedRows, resetSelection, fetchData, globalFilter } =
    useDataTable({
        data: () => props.data,
        columns: () => props.columns,
        meta: () => props.meta,
        enableRowSelection: props.enableRowSelection,
        enableMultiRowSelection: props.enableMultiRowSelection,
        onServerRequest: handleServerRequest,
    });

// Watch selected rows and emit
watch(
    selectedRows,
    (newValue) => {
        emit('rowSelectionChange', newValue);
    },
    { deep: true },
);

defineExpose({
    resetSelection,
    fetchData,
    globalFilter,
});
</script>

<template>
    <div class="space-y-4">
        <!-- Toolbar -->
        <DataTableToolbar
            v-if="enableToolbar"
            :table="table"
            :search-placeholder="searchPlaceholder"
            :enable-column-visibility="enableColumnVisibility"
        >
            <!-- Pass through filters slot -->
            <template #filters="slotProps">
                <slot name="filters" v-bind="slotProps" />
            </template>
            <!-- Pass through actions slot -->
            <template #actions="slotProps">
                <slot name="actions" v-bind="slotProps" />
            </template>
        </DataTableToolbar>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="loading">
                        <TableRow>
                            <TableCell
                                :colspan="columns.length"
                                class="h-24 text-center"
                            >
                                <div class="flex items-center justify-center">
                                    <div
                                        class="h-8 w-8 animate-spin rounded-full border-4 border-gray-300 border-t-primary"
                                    ></div>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else-if="table.getRowModel().rows?.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="
                                row.getIsSelected() ? 'selected' : undefined
                            "
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell
                                :colspan="columns.length"
                                class="h-24 text-center"
                            >
                                No results found.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <DataTablePagination :table="table" :meta="meta" :server-side="true" />
    </div>
</template>
