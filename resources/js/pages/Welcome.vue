<script setup lang="ts">
defineProps<{
    appName: string;
}>();
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-4">
                Welcome to {{ appName }}
            </h1>
            <p class="text-gray-500 dark:text-gray-400">
                Laravel + Inertia.js + Vue 3 is ready.
            </p>
        </div>
    </div>
</template>
