<script setup lang="ts">
import AppLogoIcon from '@/components/layout/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center gap-6 bg-background p-6">
        <a :href="route('dashboard')" class="flex items-center gap-2 font-medium" aria-label="Go to home">
            <div class="flex h-9 w-9 items-center justify-center rounded-md">
                <AppLogoIcon class="size-9 fill-current text-foreground dark:text-white" />
            </div>
        </a>

        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold tracking-tight">{{ title }}</h1>
                <p v-if="description" class="mt-1 text-sm text-muted-foreground">{{ description }}</p>
            </div>
            <slot />
        </div>
    </div>
</template>
