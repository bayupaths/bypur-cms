<script setup lang="ts">
import { useAppearance } from '@/composables/shared/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', label: 'Light', icon: Sun },
    { value: 'dark', label: 'Dark', icon: Moon },
    { value: 'system', label: 'System', icon: Monitor },
] as const;
</script>

<template>
    <div class="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
        <button
            v-for="tab in tabs"
            :key="tab.value"
            :class="[
                'inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition-all',
                appearance === tab.value
                    ? 'bg-white shadow-sm dark:bg-neutral-700 text-foreground'
                    : 'text-muted-foreground hover:text-foreground',
            ]"
            @click="updateAppearance(tab.value)"
        >
            <component :is="tab.icon" class="h-4 w-4" />
            {{ tab.label }}
        </button>
    </div>
</template>
