export type NotificationType = 'info' | 'success' | 'warning' | 'error' | 'message'

export interface Notification {
    id: string
    type: NotificationType
    title: string
    message: string
    read: boolean
    action_url?: string
    created_at: string
}

export interface NotificationMeta {
    total: number
    unread_count: number
    current_page: number
    last_page: number
}
