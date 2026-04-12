import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import type { Notification, NotificationMeta } from '@/types/notification'

const http = axios.create({
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': decodeURIComponent(
            document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
        ),
    },
})

export function timeAgo(dateStr: string): string {
    const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)
    if (diff < 60) return 'just now'
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
    if (diff < 2592000) return `${Math.floor(diff / 86400)}d ago`
    if (diff < 31536000) return `${Math.floor(diff / 2592000)}mo ago`
    return `${Math.floor(diff / 31536000)}y ago`
}

export function useNotifications() {
    const notifications = ref<Notification[]>([])
    const meta = ref<NotificationMeta | null>(null)
    const isLoading = ref(false)
    const isLoadingMore = ref(false)
    let pollingTimer: ReturnType<typeof setInterval> | null = null

    const unreadCount = computed(() => meta.value?.unread_count ?? 0)
    const hasMore = computed(() =>
        meta.value ? meta.value.current_page < meta.value.last_page : false
    )

    async function fetchNotifications(page = 1) {
        if (page === 1) isLoading.value = true
        else isLoadingMore.value = true

        try {
            const { data } = await http.get('/api/notifications', {
                params: { page, per_page: 10 },
            })
            if (page === 1) notifications.value = data.data
            else notifications.value.push(...data.data)
            meta.value = data.meta
        } finally {
            isLoading.value = false
            isLoadingMore.value = false
        }
    }

    async function fetchUnreadCount() {
        const { data } = await http.get('/api/notifications/unread-count')
        if (meta.value) meta.value.unread_count = data.count
        else meta.value = { total: 0, unread_count: data.count, current_page: 1, last_page: 1 }
    }

    async function markAsRead(id: string) {
        await http.patch(`/api/notifications/${id}/read`)
        const n = notifications.value.find(n => n.id === id)
        if (n && !n.read) {
            n.read = true
            if (meta.value) meta.value.unread_count = Math.max(0, meta.value.unread_count - 1)
        }
    }

    async function markAllAsRead() {
        await http.patch('/api/notifications/read-all')
        notifications.value.forEach(n => (n.read = true))
        if (meta.value) meta.value.unread_count = 0
    }

    function startPolling(interval = 30_000) {
        pollingTimer = setInterval(fetchUnreadCount, interval)
    }

    onMounted(() => {
        fetchUnreadCount()
        startPolling()
    })

    onUnmounted(() => {
        if (pollingTimer) clearInterval(pollingTimer)
    })

    return {
        notifications, meta, isLoading, isLoadingMore,
        unreadCount, hasMore,
        fetchNotifications, fetchUnreadCount, markAsRead, markAllAsRead,
    }
}
