<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
    class?: string;
    scrollable?: boolean;
}>();
</script>

<template>
    <div :class="cn('flex flex-1 flex-col gap-4 p-4 md:p-6', $props.class)">
        <slot />
    </div>
</template>
