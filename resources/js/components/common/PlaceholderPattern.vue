<script setup lang="ts">
defineProps<{
    class?: string;
}>();
</script>

<template>
    <svg
        :class="['pointer-events-none absolute inset-0 h-full w-full', $props.class]"
        xmlns="http://www.w3.org/2000/svg"
    >
        <defs>
            <pattern id="pattern" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M-3 13 15-5M-5 5l18-18M-1 21 17 3" class="stroke-foreground/10 dark:stroke-foreground/5" stroke-width="1" />
            </pattern>
        </defs>
        <rect fill="url(#pattern)" width="100%" height="100%" />
    </svg>
</template>
