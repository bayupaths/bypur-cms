<script setup lang="ts">
import { CircleCheck, CircleX, Info, TriangleAlert, X } from 'lucide-vue-next';
import { useToast, type ToastType } from '@/composables/shared/useToast';

const { toasts, remove } = useToast();

const typeConfig: Record<ToastType, { bg: string; border: string; icon: string; component: typeof CircleCheck }> = {
    success: {
        bg: 'bg-green-50 dark:bg-green-950',
        border: 'border-green-200 dark:border-green-800',
        icon: 'text-green-600 dark:text-green-400',
        component: CircleCheck,
    },
    error: {
        bg: 'bg-red-50 dark:bg-red-950',
        border: 'border-red-200 dark:border-red-800',
        icon: 'text-red-600 dark:text-red-400',
        component: CircleX,
    },
    warning: {
        bg: 'bg-yellow-50 dark:bg-yellow-950',
        border: 'border-yellow-200 dark:border-yellow-800',
        icon: 'text-yellow-600 dark:text-yellow-400',
        component: TriangleAlert,
    },
    info: {
        bg: 'bg-blue-50 dark:bg-blue-950',
        border: 'border-blue-200 dark:border-blue-800',
        icon: 'text-blue-600 dark:text-blue-400',
        component: Info,
    },
};

const getConfig = (type: ToastType) => typeConfig[type];
</script>

<template>
    <Teleport to="body">
        <div class="fixed right-4 top-4 z-99999 flex w-full max-w-md flex-col gap-2">
            <TransitionGroup
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-x-full"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-full"
                move-class="transition-all duration-300"
            >
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    :class="[getConfig(t.type).bg, getConfig(t.type).border]"
                    class="flex items-center gap-3 rounded-lg border p-4 shadow-lg"
                >
                    <component
                        :is="getConfig(t.type).component"
                        :class="getConfig(t.type).icon"
                        class="h-5 w-5 shrink-0"
                    />
                    <p class="flex-1 text-sm font-medium text-foreground">{{ t.message }}</p>
                    <button
                        class="shrink-0 rounded-full p-1 text-muted-foreground transition-colors hover:bg-black/5 hover:text-foreground dark:hover:bg-white/10"
                        @click="remove(t.id)"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
