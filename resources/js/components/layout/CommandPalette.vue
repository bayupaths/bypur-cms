<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupButton,
    InputGroupInput,
    InputGroupText,
} from '@/components/ui/input-group';
import { useMenuIcons } from '@/composables/shared/useMenuIcons';
import type { CommandItem } from '@/composables/shared/useCommandPalette';

const props = defineProps<{
    open: boolean;
    query: string;
    filtered: CommandItem[];
    selectedIndex: number;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'update:query': [value: string];
    'select-prev': [];
    'select-next': [];
    'confirm-selection': [];
    'select-item': [item: CommandItem];
    'hover-item': [index: number];
}>();

const { getIcon } = useMenuIcons();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent
            class="max-w-lg gap-0 overflow-hidden p-0"
            :show-close-button="false"
            @open-auto-focus="(e: Event) => e.preventDefault()"
        >
            <DialogTitle class="sr-only">Quick Search</DialogTitle>

            <!-- Search input row -->
            <InputGroup
                class="rounded-b-none border-x-0 border-t-0 px-2 shadow-none has-[[data-slot=input-group-control]:focus-visible]:ring-0"
            >
                <InputGroupAddon>
                    <InputGroupText>
                        <Search class="h-4 w-4" />
                    </InputGroupText>
                </InputGroupAddon>
                <InputGroupInput
                    :model-value="query"
                    autofocus
                    placeholder="Search pages..."
                    @update:model-value="emit('update:query', String($event))"
                    @keydown.up.prevent="emit('select-prev')"
                    @keydown.down.prevent="emit('select-next')"
                    @keydown.enter.prevent="emit('confirm-selection')"
                />
                <InputGroupAddon align="inline-end">
                    <InputGroupButton @click="emit('update:open', false)">
                        <X />
                    </InputGroupButton>
                </InputGroupAddon>
            </InputGroup>

            <!-- Results -->
            <div class="max-h-72 overflow-y-auto p-2">
                <template v-if="filtered.length > 0">
                    <button
                        v-for="(item, index) in filtered"
                        :key="item.route"
                        class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors"
                        :class="index === selectedIndex ? 'bg-accent text-accent-foreground' : 'hover:bg-accent/50'"
                        @click="emit('select-item', item)"
                        @mouseenter="emit('hover-item', index)"
                    >
                        <component :is="getIcon(item.icon)" class="h-4 w-4 shrink-0 text-muted-foreground" />
                        <span>{{ item.label }}</span>
                    </button>
                </template>
                <div v-else class="px-3 py-8 text-center text-sm text-muted-foreground">
                    No results found.
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>

