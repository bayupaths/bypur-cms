<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import NotificationItem from './NotificationItem.vue'
import type { Notification } from '@/types/notification'

const props = defineProps<{
    notifications: Notification[]
    isLoading: boolean
    isLoadingMore: boolean
    hasMore: boolean
}>()

const emit = defineEmits<{
    read: [id: string]
    loadMore: []
}>()

const scrollRef = ref<HTMLElement | null>(null)
const sentinelRef = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

function setupObserver() {
    if (!sentinelRef.value) return

    observer = new IntersectionObserver(
        (entries) => {
            const entry = entries[0]
            if (entry.isIntersecting && props.hasMore && !props.isLoadingMore) {
                emit('loadMore')
            }
        },
        { root: scrollRef.value, threshold: 0.5 }
    )

    observer.observe(sentinelRef.value)
}

onMounted(() => setupObserver())
onUnmounted(() => observer?.disconnect())
</script>

<template>
    <div ref="scrollRef" class="max-h-72 overflow-y-auto divide-y divide-border/50">

        <!-- Skeleton loading -->
        <template v-if="isLoading">
            <div
                v-for="i in 4"
                :key="i"
                class="flex gap-3 px-3 py-2.5 animate-pulse"
            >
                <div class="h-7 w-7 rounded-full bg-muted shrink-0 mt-0.5" />
                <div class="flex-1 space-y-1.5 py-0.5">
                    <div class="h-2.5 w-2/3 rounded bg-muted" />
                    <div class="h-2 w-5/6 rounded bg-muted" />
                    <div class="h-2 w-1/3 rounded bg-muted" />
                </div>
            </div>
        </template>

        <!-- Notification items -->
        <template v-else-if="notifications.length > 0">
            <NotificationItem
                v-for="notification in notifications"
                :key="notification.id"
                :notification="notification"
                @read="emit('read', $event)"
            />

            <!-- Infinite scroll sentinel -->
            <div ref="sentinelRef" class="px-3 py-2 flex justify-center">
                <template v-if="isLoadingMore">
                    <div class="flex items-center gap-2 text-xs text-muted-foreground">
                        <svg
                            class="h-3 w-3 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z"/>
                        </svg>
                        Loading more…
                    </div>
                </template>
                <template v-else-if="!hasMore && notifications.length > 0">
                    <span class="text-xs text-muted-foreground/60">All notifications loaded</span>
                </template>
            </div>
        </template>

        <!-- Empty state -->
        <div v-else class="flex flex-col items-center gap-2 px-3 py-8 text-center">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                <svg
                    class="h-5 w-5 text-muted-foreground"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium">All caught up</p>
                <p class="text-xs text-muted-foreground mt-0.5">No new notifications right now.</p>
            </div>
        </div>

    </div>
</template>
