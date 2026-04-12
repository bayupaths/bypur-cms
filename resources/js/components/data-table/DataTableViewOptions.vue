<script setup lang="ts">
import type { Column, Table } from '@tanstack/vue-table';
import { SlidersHorizontal } from 'lucide-vue-next';
import { computed, type ComputedRef } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';


interface DataTableViewOptionsProps {
    table: Table<any>;
}

const props = defineProps<DataTableViewOptionsProps>();

const columns: ComputedRef<Array<Column<any, unknown>>> = computed(() =>
    props.table
        .getAllColumns()
        .filter((column: Column<any, unknown>) => column.getCanHide()),
);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" size="sm" class="ml-auto h-8 lg:flex">
                <SlidersHorizontal class="mr-2 h-4 w-4" />
                View
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-37.5">
            <DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuCheckboxItem
                v-for="column in columns"
                :key="column.id"
                :checked="column.getIsVisible()"
                @select.prevent="column.toggleVisibility()"
            >
                {{ (column.columnDef.meta as any)?.label || column.id }}
            </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
