import { ref, computed } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
    id: number;
    type: ToastType;
    message: string;
    duration: number;
}

const toasts = ref<Toast[]>([]);
let toastId = 0;

function addToast(type: ToastType, message: string, duration = 5000) {
    const id = ++toastId;
    toasts.value.push({ id, type, message, duration });
    if (duration > 0) {
        setTimeout(() => removeToast(id), duration);
    }
    return id;
}

function removeToast(id: number) {
    const index = toasts.value.findIndex((t) => t.id === id);
    if (index > -1) {
        toasts.value.splice(index, 1);
    }
}

export function useToast() {
    return {
        toasts: computed(() => toasts.value),
        success: (message: string, duration?: number) => addToast('success', message, duration),
        error: (message: string, duration?: number) => addToast('error', message, duration),
        warning: (message: string, duration?: number) => addToast('warning', message, duration),
        info: (message: string, duration?: number) => addToast('info', message, duration),
        remove: removeToast,
        clear: () => (toasts.value = []),
    };
}

export const toast = {
    success: (message: string, duration?: number) => addToast('success', message, duration),
    error: (message: string, duration?: number) => addToast('error', message, duration),
    warning: (message: string, duration?: number) => addToast('warning', message, duration),
    info: (message: string, duration?: number) => addToast('info', message, duration),
    dismiss: removeToast,
    dismissAll: () => (toasts.value = []),
};
