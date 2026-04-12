<script setup lang="ts">
import AppContent from '@/components/layout/AppContent.vue';
import AppHeader from '@/components/layout/AppHeader.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <AppHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </div>
</template>
