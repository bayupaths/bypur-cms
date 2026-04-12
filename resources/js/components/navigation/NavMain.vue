<script setup lang="ts">
import type { NavItem } from "@/types";
import { computed } from "vue";
import { ChevronRight } from "lucide-vue-next";
import { Link } from "@inertiajs/vue3";
import { useActiveUrl } from "@/composables/shared";
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/ui/collapsible";
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    SidebarSeparator,
} from "@/components/ui/sidebar";

const props = defineProps<{
    items: NavItem[];
}>();

const { isActive } = useActiveUrl();

/** Split flat items list into groups separated by dividers. */
const groups = computed(() => {
    const result: NavItem[][] = [[]];
    for (const item of props.items) {
        if (item.isDivider) {
            result.push([]);
        } else {
            result[result.length - 1].push(item);
        }
    }
    return result.filter((g) => g.length > 0);
});

function hasActiveChild(items: NavItem[]): boolean {
    return items.some(
        (i) =>
            isActive(i.href) ||
            (i.items?.length ? hasActiveChild(i.items) : false),
    );
}

/**
 * Cek apakah sub-item aktif, dengan mempertimbangkan sibling.
 * Jika ada sibling lain yang exact-match URL saat ini,
 * item ini hanya aktif jika juga exact-match (bukan prefix).
 * Ini mencegah /menu ikut aktif saat /menu/groups sedang dibuka.
 */
function isSubItemActive(subItem: NavItem, siblings: NavItem[]): boolean {
    const anotherSiblingExactlyMatches = siblings
        .filter((s) => s.href !== subItem.href)
        .some((s) => isActive(s.href, true));

    if (anotherSiblingExactlyMatches) return isActive(subItem.href, true);
    return isActive(subItem.href);
}
</script>

<template>
    <template v-for="(group, gi) in groups" :key="gi">
        <SidebarSeparator v-if="gi > 0" />
        <SidebarGroup>
            <SidebarMenu>
                <template v-for="item in group" :key="item.title">
                    <!-- Leaf node: no children -->
                    <SidebarMenuItem v-if="!item.items?.length">
                        <SidebarMenuButton
                            as-child
                            :is-active="isActive(item.href)"
                            :tooltip="item.title"
                        >
                            <Link :href="item.href">
                                <component :is="item.icon" v-if="item.icon" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                        <SidebarMenuBadge v-if="item.badge">{{
                            item.badge
                        }}</SidebarMenuBadge>
                    </SidebarMenuItem>

                    <!-- Parent node: has children -->
                    <Collapsible
                        v-else
                        as-child
                        :default-open="
                            item.isActive || hasActiveChild(item.items ?? [])
                        "
                        class="group/collapsible"
                    >
                        <SidebarMenuItem>
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton :tooltip="item.title">
                                    <component
                                        :is="item.icon"
                                        v-if="item.icon"
                                    />
                                    <span>{{ item.title }}</span>
                                    <SidebarMenuBadge
                                        v-if="item.badge"
                                        class="ml-auto mr-1"
                                        >{{ item.badge }}</SidebarMenuBadge
                                    >
                                    <ChevronRight
                                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                    />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <SidebarMenuSubItem
                                        v-for="subItem in item.items"
                                        :key="subItem.title"
                                        class="relative"
                                    >
                                        <span
                                            v-if="isSubItemActive(subItem, item.items!)"
                                            class="absolute inset-y-0 -left-2.5 w-0.5 rounded-full bg-primary"
                                        />
                                        <SidebarMenuSubButton
                                            as-child
                                            :is-active="isSubItemActive(subItem, item.items!)"
                                            :class="isSubItemActive(subItem, item.items!) ? 'font-medium text-primary' : ''"
                                        >
                                            <Link :href="subItem.href">
                                                <component
                                                    :is="subItem.icon"
                                                    v-if="subItem.icon"
                                                />
                                                <span>{{ subItem.title }}</span>
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>
                </template>
            </SidebarMenu>
        </SidebarGroup>
    </template>
</template>
