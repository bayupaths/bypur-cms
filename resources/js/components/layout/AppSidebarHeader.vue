<script setup lang="ts">
import AppLogo from '@/components/layout/AppLogo.vue';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
</script>

<template>
    <SidebarMenu>
        <SidebarMenuItem>
            <SidebarMenuButton size="lg" as-child>
                <AppLogo />
            </SidebarMenuButton>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
