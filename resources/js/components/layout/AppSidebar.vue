<script setup lang="ts">
import type { SidebarProps } from "@/components/ui/sidebar";
import type { NavItem, SidebarMenuItem } from "@/types";

import NavMain from "@/components/navigation/NavMain.vue";
import NavUser from "@/components/navigation/NavUser.vue";
import AppSidebarHeader from "@/components/layout/AppSidebarHeader.vue";
import { useMenuIcons } from "@/composables/shared";

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarRail,
} from "@/components/ui/sidebar";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = withDefaults(defineProps<SidebarProps>(), {
    collapsible: "icon",
});

const page = usePage<{
    auth: { user: { name: string; email: string; avatar?: string } };
    sidebar: SidebarMenuItem[];
}>();

const user = computed(() => ({
    ...page.props.auth?.user,
    avatar: page.props.auth?.user?.avatar ?? "",
}));

const { getIcon } = useMenuIcons();

function resolveUrl(item: SidebarMenuItem): string {
    if (!item.url) return "#";
    if (item.is_route) {
        try {
            return route(item.url);
        } catch {
            return "#";
        }
    }
    return item.url;
}

function transformMenuItems(items: SidebarMenuItem[]): NavItem[] {
    return items.map((item) => ({
        title: item.title,
        href: resolveUrl(item),
        icon: item.icon ? getIcon(item.icon) : undefined,
        badge: item.badge ?? undefined,
        isDivider: item.is_divider,
        items: item.all_children?.length
            ? transformMenuItems(item.all_children)
            : undefined,
    }));
}

const navMain = computed(() => transformMenuItems(page.props.sidebar ?? []));
</script>

<template>
    <Sidebar v-bind="props">
        <SidebarHeader>
            <AppSidebarHeader />
        </SidebarHeader>
        <SidebarContent>
            <NavMain :items="navMain" />
        </SidebarContent>
        <SidebarFooter>
            <NavUser v-if="page.props.auth?.user" :user="user" />
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>
</template>
