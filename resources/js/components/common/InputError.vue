<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <p v-if="message" class="text-xs text-destructive">
        {{ message }}
    </p>
</template>
