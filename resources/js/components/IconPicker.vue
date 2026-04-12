<script setup lang="ts">
import { computed, ref } from 'vue';
import * as LucideIcons from 'lucide-vue-next';
import { ChevronDown, Search, X } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const open = ref(false);
const search = ref('');

// Build icon list — uppercase keys that are functions or objects (covers all lucide-vue-next versions)
const allIconNames = Object.keys(LucideIcons).filter((key) => {
    if (!/^[A-Z]/.test(key)) return false;
    const v = (LucideIcons as Record<string, unknown>)[key];
    return typeof v === 'function' || (typeof v === 'object' && v !== null);
});

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return allIconNames.slice(0, 200);
    return allIconNames.filter((name) => name.toLowerCase().includes(q));
});

const currentIcon = computed(() =>
    props.modelValue ? (LucideIcons as Record<string, unknown>)[props.modelValue] ?? null : null,
);

function openPicker() {
    search.value = '';
    open.value = true;
}

function select(name: string) {
    emit('update:modelValue', name);
    open.value = false;
    search.value = '';
}

function clear(e: Event) {
    e.stopPropagation();
    emit('update:modelValue', '');
}
</script>

<template>
    <!-- Combobox trigger -->
    <button
        type="button"
        class="flex items-center justify-between w-full px-3 py-2 text-sm border rounded-md border-input bg-background text-foreground focus-visible:ring-ring h-9 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
        @click="openPicker"
    >
        <span class="flex items-center gap-2 truncate">
            <component :is="currentIcon" v-if="currentIcon" class="w-4 h-4 shrink-0" />
            <span class="truncate" :class="modelValue ? 'text-foreground' : 'text-muted-foreground'">
                {{ modelValue || placeholder || 'Pilih icon...' }}
            </span>
        </span>
        <span class="flex shrink-0 items-center gap-0.5">
            <span
                v-if="modelValue"
                role="button"
                tabindex="-1"
                class="rounded-sm text-muted-foreground hover:text-foreground"
                @click="clear"
            >
                <X class="h-3.5 w-3.5" />
            </span>
            <ChevronDown class="w-4 h-4 opacity-50 text-muted-foreground" />
        </span>
    </button>

    <!-- Dialog picker — reka-ui manages its own focus scope, no conflict with Sheet -->
    <Dialog v-model:open="open">
        <DialogContent class="flex flex-col gap-3 sm:max-w-xl" hide-overlay @open-auto-focus.prevent>
            <DialogHeader>
                <DialogTitle>Pilih Icon</DialogTitle>
                <DialogDescription>Cari dan pilih icon dari Lucide Icons</DialogDescription>
            </DialogHeader>

            <!-- Search -->
            <div class="relative">
                <Search class="text-muted-foreground pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2" />
                <Input v-model="search" placeholder="Cari icon..." class="pl-8" />
            </div>

            <!-- Icon grid -->
            <div class="overflow-y-auto border rounded-md h-72">
                <div v-if="filtered.length" class="grid grid-cols-8 gap-0.5 p-1.5">
                    <button
                        v-for="name in filtered"
                        :key="name"
                        type="button"
                        :title="name"
                        :class="[
                            'hover:bg-muted flex flex-col items-center gap-1 rounded px-1 py-2 transition-colors',
                            modelValue === name ? 'bg-primary/10 text-primary ring-primary ring-1' : 'text-foreground',
                        ]"
                        @click="select(name)"
                    >
                        <component :is="(LucideIcons as Record<string, unknown>)[name]" class="h-[18px] w-[18px]" />
                        <span class="w-full truncate text-center text-[9px] leading-tight">{{ name }}</span>
                    </button>
                </div>
                <p v-else class="py-10 text-sm text-center text-muted-foreground">Tidak ada icon ditemukan</p>
            </div>

            <!-- Footer -->
            <p class="text-xs text-muted-foreground">
                {{ search ? `${filtered.length} hasil` : `${filtered.length} dari ${allIconNames.length} icon` }}
            </p>
        </DialogContent>
    </Dialog>
</template>
