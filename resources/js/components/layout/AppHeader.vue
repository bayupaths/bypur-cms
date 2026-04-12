<script setup lang="ts">
import { computed } from 'vue';
import { ExternalLink, Maximize2, Minimize2, Moon, Search, Sun } from 'lucide-vue-next';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import Breadcrumbs from '@/components/layout/Breadcrumbs.vue';
import CommandPalette from '@/components/layout/CommandPalette.vue';
import NotificationBell from '@/components/notification/NotificationBell.vue';
import { useAppearance } from '@/composables/shared/useAppearance';
import { useCommandPalette, type CommandItem } from '@/composables/shared/useCommandPalette';
import { useFullscreen } from '@/composables/shared/useFullscreen';
import { usePublicUrl } from '@/composables/shared/usePublicUrl';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const { resolvedAppearance, updateAppearance } = useAppearance();
const { isFullscreen, toggle: toggleFullscreen } = useFullscreen();
const { openPublicSite } = usePublicUrl();

const NAV_ITEMS: CommandItem[] = [
    { label: 'Dashboard', icon: 'LayoutDashboard', route: '/dashboard' },
    { label: 'Projects', icon: 'FolderKanban', route: '/projects' },
    { label: 'Skills', icon: 'Wrench', route: '/skills' },
    { label: 'Experience', icon: 'Briefcase', route: '/experience' },
    { label: 'Messages', icon: 'MessageSquare', route: '/messages' },
    { label: 'Settings', icon: 'Settings', route: '/settings' },
];

const { isOpen: isPaletteOpen, query, filtered, selectedIndex, open: openPalette, selectPrev, selectNext, confirmSelection, selectItem } =
    useCommandPalette(NAV_ITEMS);

const isDark = computed(() => resolvedAppearance.value === 'dark');
function toggleTheme() {
    updateAppearance(isDark.value ? 'light' : 'dark');
}
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12"
    >
        <div class="flex items-center w-full gap-2 px-4">
            <SidebarTrigger class="-ml-1" />
            <Separator orientation="vertical" class="h-4 mr-2" />
            <Breadcrumbs v-if="breadcrumbs?.length" :items="breadcrumbs" />

            <div class="flex items-center gap-1 ml-auto">
                <slot name="actions" />

                <!-- Quick Search -->
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button variant="ghost" size="icon" @click="openPalette">
                            <Search class="w-4 h-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Quick search (Ctrl+K)</TooltipContent>
                </Tooltip>

                <!-- Notification Bell -->
                <NotificationBell />

                <!-- Preview Public Site -->
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button variant="ghost" size="icon" @click="openPublicSite">
                            <ExternalLink class="w-4 h-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>Preview public site</TooltipContent>
                </Tooltip>

                <!-- Dark / Light toggle -->
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button variant="ghost" size="icon" @click="toggleTheme">
                            <Sun v-if="isDark" class="w-4 h-4" />
                            <Moon v-else class="w-4 h-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>{{ isDark ? 'Light mode' : 'Dark mode' }}</TooltipContent>
                </Tooltip>

                <!-- Fullscreen toggle -->
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button variant="ghost" size="icon" @click="toggleFullscreen">
                            <Minimize2 v-if="isFullscreen" class="w-4 h-4" />
                            <Maximize2 v-else class="w-4 h-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>{{ isFullscreen ? 'Exit fullscreen' : 'Fullscreen' }}</TooltipContent>
                </Tooltip>
            </div>
        </div>
    </header>

    <!-- Command Palette -->
    <CommandPalette
        v-model:open="isPaletteOpen"
        v-model:query="query"
        :filtered="filtered"
        :selected-index="selectedIndex"
        @select-prev="selectPrev"
        @select-next="selectNext"
        @confirm-selection="confirmSelection"
        @select-item="selectItem"
        @hover-item="selectedIndex = $event"
    />
</template>
