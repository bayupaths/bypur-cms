<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps<{
    status: number;
    message?: string;
}>();

const statusMessages: Record<number, { title: string; description: string }> = {
    401: {
        title: '401 - Unauthorized',
        description: 'You need to be authenticated to access this page.',
    },
    403: {
        title: '403 - Forbidden',
        description: "You don't have permission to access this page.",
    },
    404: {
        title: '404 - Page Not Found',
        description: "Sorry, the page you're looking for doesn't exist.",
    },
    419: {
        title: '419 - Page Expired',
        description: 'Your session has expired. Please refresh the page and try again.',
    },
    429: {
        title: '429 - Too Many Requests',
        description: 'You have sent too many requests. Please wait a moment and try again.',
    },
    500: {
        title: '500 - Server Error',
        description: 'Something went wrong on our end. Please try again later.',
    },
    503: {
        title: '503 - Service Unavailable',
        description: 'The service is temporarily unavailable. Please try again later.',
    },
};

const errorInfo = statusMessages[props.status] ?? {
    title: `${props.status} - Error`,
    description: props.message ?? 'An unexpected error occurred.',
};
</script>

<template>
    <Head :title="errorInfo.title" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-background px-6">
        <div class="w-full max-w-md text-center space-y-8">
            <!-- Status Code -->
            <div class="relative select-none">
                <span class="text-[9rem] font-extrabold leading-none text-muted-foreground/15 block">
                    {{ status }}
                </span>
                <span class="absolute inset-0 flex items-center justify-center text-7xl font-extrabold tracking-tight text-foreground">
                    {{ status }}
                </span>
            </div>

            <!-- Divider -->
            <div class="flex items-center gap-4">
                <div class="flex-1 h-px bg-border" />
                <span class="text-xs font-medium text-muted-foreground uppercase tracking-widest">Error</span>
                <div class="flex-1 h-px bg-border" />
            </div>

            <!-- Message -->
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                    {{ errorInfo.title.replace(/^\d+ - /, '') }}
                </h1>
                <p class="text-sm text-muted-foreground leading-relaxed">
                    {{ errorInfo.description }}
                </p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <Button @click="router.visit(route('dashboard'))">
                    Back to Dashboard
                </Button>
                <Button variant="outline" @click="router.reload()">
                    Try Again
                </Button>
            </div>
        </div>
    </div>
</template>
