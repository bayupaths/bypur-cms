<script setup lang="ts">
import AppLogoIcon from '@/components/layout/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-background py-12 px-4">
        <div class="w-full max-w-md space-y-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <a :href="route('dashboard')" class="flex flex-col items-center gap-2 font-medium" aria-label="Go to home">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md">
                        <AppLogoIcon class="size-9 fill-current text-foreground dark:text-white" />
                    </div>
                </a>
                <div class="space-y-1">
                    <h1 class="text-xl font-bold tracking-tight">{{ title }}</h1>
                    <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
                </div>
            </div>
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <slot />
            </div>
        </div>
    </div>
</template>
