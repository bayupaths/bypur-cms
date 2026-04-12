<script setup lang="ts">
defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ title }}</h1>
            <p v-if="description" class="text-muted-foreground text-sm">{{ description }}</p>
        </div>
        <slot />
    </div>
</template>
