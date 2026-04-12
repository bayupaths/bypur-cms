<script setup lang="ts">
import AppLogoIcon from '@/components/layout/AppLogoIcon.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen grid lg:grid-cols-2">
        <!-- Left panel - decorative -->
        <div class="hidden lg:flex flex-col bg-muted p-10 text-foreground relative">
            <div class="flex items-center gap-2 font-medium">
                <div class="flex h-9 w-9 items-center justify-center rounded-md">
                    <AppLogoIcon class="size-9 fill-current" />
                </div>
                <span class="text-xl font-bold">Portfolio Admin</span>
            </div>
            <div class="mt-auto">
                <blockquote class="space-y-2">
                    <p class="text-lg">
                        "Manage your portfolio with ease. Showcase your projects, skills, and experience."
                    </p>
                </blockquote>
            </div>
        </div>

        <!-- Right panel - form -->
        <div class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-background">
            <div class="w-full max-w-md space-y-6">
                <div class="flex flex-col gap-2">
                    <h1 class="text-2xl font-bold tracking-tight">{{ title }}</h1>
                    <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
