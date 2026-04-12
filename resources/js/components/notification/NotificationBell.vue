<script setup lang="ts">
import { Bell } from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import NotificationList from './NotificationList.vue'
import { useNotifications } from '@/composables/shared/useNotifications'

const {
    notifications, isLoading, isLoadingMore,
    unreadCount, hasMore,
    fetchNotifications, markAsRead, markAllAsRead,
} = useNotifications()

function onOpen(open: boolean) {
    if (open) fetchNotifications(1)
}
</script>

<template>
    <DropdownMenu @update:open="onOpen">
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="relative">
                <Bell class="w-4 h-4" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center
                           rounded-full bg-destructive text-[10px] font-medium text-destructive-foreground"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="end" class="w-80 p-0">
            <!-- Header -->
            <div class="flex items-center justify-between px-3 py-2.5 border-b">
                <span class="text-sm font-semibold">Notifications</span>
                <div class="flex items-center gap-2">
                    <span
                        v-if="unreadCount > 0"
                        class="text-xs bg-destructive text-destructive-foreground
                               px-1.5 py-0.5 rounded-full font-medium"
                    >
                        {{ unreadCount }}
                    </span>
                    <button
                        v-if="unreadCount > 0"
                        class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                        @click="markAllAsRead"
                    >
                        Mark all read
                    </button>
                </div>
            </div>

            <!-- List -->
            <NotificationList
                :notifications="notifications"
                :is-loading="isLoading"
                :is-loading-more="isLoadingMore"
                :has-more="hasMore"
                @read="markAsRead"
                @load-more="fetchNotifications()"
            />

            <DropdownMenuSeparator />
            <Link
                href="/notifications"
                class="flex items-center justify-center py-2 text-xs text-muted-foreground
                       hover:text-foreground hover:bg-accent transition-colors"
            >
                View all notifications
            </Link>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
