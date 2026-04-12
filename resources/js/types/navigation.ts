import type { Component } from 'vue';

export interface NavItem {
    title: string;
    href: string;
    icon?: Component;
    isActive?: boolean;
    isDivider?: boolean;
    badge?: string | number | null;
    items?: NavItem[];
}

export interface SidebarMenuItem {
    id: number;
    title: string;
    url: string | null;
    is_route: boolean;
    icon: string | null;
    badge: string | null;
    badge_variant: string | null;
    is_divider: boolean;
    order: number;
    all_children: SidebarMenuItem[];
}

export interface BreadcrumbItem {
    title: string;
    href?: string;
}
