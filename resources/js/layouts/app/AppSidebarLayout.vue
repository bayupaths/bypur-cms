<script setup lang="ts">
import AppContent from '@/components/layout/AppContent.vue';
import AppHeader from '@/components/layout/AppHeader.vue';
import AppSidebar from '@/components/layout/AppSidebar.vue';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import FlashMessages from '@/components/common/FlashMessages.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import { useFlashMessages } from '@/composables/shared';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

// Handle flash messages dari Laravel sebagai toast
useFlashMessages();
</script>

<template>
    <SidebarProvider>
        <AppSidebar />
        <SidebarInset>
            <AppHeader :breadcrumbs="breadcrumbs" />
            <AppContent>
                <slot />
            </AppContent>
        </SidebarInset>
    </SidebarProvider>

    <!-- Global overlays -->
    <FlashMessages />
    <ConfirmDialog />
</template>
