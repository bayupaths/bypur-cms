import * as LucideIcons from 'lucide-vue-next';
import type { Component } from 'vue';

const NON_ICON_KEYS = new Set(['createLucideIcon', 'default']);

type LucideIconsMap = Record<string, Component>;

export function useMenuIcons() {
    function getIcon(name: string): Component | undefined {
        if (NON_ICON_KEYS.has(name)) return undefined;
        return (LucideIcons as unknown as LucideIconsMap)[name];
    }

    function isValidIcon(name: string): boolean {
        return !NON_ICON_KEYS.has(name) && name in LucideIcons;
    }

    return { getIcon, isValidIcon };
}
