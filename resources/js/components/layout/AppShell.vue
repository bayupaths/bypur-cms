<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { TooltipProvider } from '@/components/ui/tooltip';

defineProps<{
    variant?: 'sidebar' | 'header';
}>();
</script>

<template>
    <TooltipProvider>
        <div class="flex flex-col min-h-screen">
            <SidebarProvider v-if="variant === 'sidebar' || !variant">
                <slot />
            </SidebarProvider>
            <template v-else>
                <slot />
            </template>
        </div>
    </TooltipProvider>
</template>
