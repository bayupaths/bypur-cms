<script setup lang="ts">
import type { Table } from '@tanstack/vue-table';
import { Search, X } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
} from '@/components/ui/input-group';
import DataTableViewOptions from './DataTableViewOptions.vue';


interface DataTableToolbarProps {
    table: Table<any>;
    searchPlaceholder?: string;
    enableColumnVisibility?: boolean;
}

const props = withDefaults(defineProps<DataTableToolbarProps>(), {
    searchPlaceholder: 'Search...',
    enableColumnVisibility: true,
});

const isFiltered = computed(
    () => props.table.getState().columnFilters.length > 0,
);
</script>

<template>
    <div class="flex items-center justify-between">
        <div class="flex flex-1 items-center space-x-2">
            <!-- Global Search Input -->
            <InputGroup class="w-38.5 lg:w-75.5">
                <InputGroupInput
                    :placeholder="searchPlaceholder"
                    :model-value="
                        (table.getState().globalFilter as string) ?? ''
                    "
                    @update:model-value="table.setGlobalFilter($event)"
                />
                <InputGroupAddon>
                    <Search />
                </InputGroupAddon>
            </InputGroup>

            <!-- Reset Filters Button -->
            <Button
                v-if="isFiltered"
                variant="outline"
                class="h-8 px-2 lg:px-3"
                @click="table.resetColumnFilters()"
            >
                Reset
                <X class="ml-2 h-4 w-4" />
            </Button>

            <!-- Custom Slot for Additional Filters -->
            <slot name="filters" :table="table" />
        </div>

        <div class="flex items-center space-x-2">
            <!-- Custom Slot for Actions -->
            <slot name="actions" :table="table" />

            <!-- Column Visibility -->
            <DataTableViewOptions
                v-if="enableColumnVisibility"
                :table="table"
            />
        </div>
    </div>
</template>
