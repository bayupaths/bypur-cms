<script setup lang="ts">
import { Info, CheckCircle2, AlertTriangle, XCircle, MessageSquare } from 'lucide-vue-next'
import { timeAgo } from '@/composables/shared/useNotifications'
import type { Notification } from '@/types/notification'

defineProps<{ notification: Notification }>()
defineEmits<{ read: [id: string] }>()

const iconMap = {
    info:    { icon: Info,          class: 'text-blue-500' },
    success: { icon: CheckCircle2,  class: 'text-green-500' },
    warning: { icon: AlertTriangle, class: 'text-yellow-500' },
    error:   { icon: XCircle,       class: 'text-red-500' },
    message: { icon: MessageSquare, class: 'text-purple-500' },
}
</script>

<template>
    <component
        :is="notification.action_url ? 'a' : 'div'"
        :href="notification.action_url"
        class="flex gap-3 px-3 py-2.5 hover:bg-accent/50 transition-colors cursor-pointer"
        :class="!notification.read ? 'bg-accent/30' : ''"
        @click="$emit('read', notification.id)"
    >
        <!-- Icon -->
        <div class="mt-0.5 shrink-0">
            <component
                :is="iconMap[notification.type].icon"
                class="w-4 h-4"
                :class="iconMap[notification.type].class"
            />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <p class="text-sm font-medium leading-snug truncate">{{ notification.title }}</p>
                <span
                    v-if="!notification.read"
                    class="h-1.5 w-1.5 shrink-0 rounded-full bg-primary"
                />
            </div>
            <p class="text-xs text-muted-foreground line-clamp-2 mt-0.5">{{ notification.message }}</p>
            <p class="text-xs text-muted-foreground/60 mt-1">{{ timeAgo(notification.created_at) }}</p>
        </div>
    </component>
</template>
