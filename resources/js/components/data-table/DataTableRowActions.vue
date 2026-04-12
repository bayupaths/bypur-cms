<script setup lang="ts">
import { MoreHorizontal } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface DataTableRowActionsProps {
    row: any;
}

defineProps<DataTableRowActionsProps>();

const emit = defineEmits<{
    edit: [row: any];
    delete: [row: any];
    view: [row: any];
}>();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                class="flex h-8 w-8 p-0 data-[state=open]:bg-muted"
            >
                <MoreHorizontal class="h-4 w-4" />
                <span class="sr-only">Open menu</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-40">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <slot name="actions" :row="row">
                <DropdownMenuItem @click="emit('view', row)">
                    View
                </DropdownMenuItem>
                <DropdownMenuItem @click="emit('edit', row)">
                    Edit
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                    class="text-red-600"
                    @click="emit('delete', row)"
                >
                    Delete
                </DropdownMenuItem>
            </slot>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
