<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
    class?: string;
}>();
</script>

<template>
    <Link
        :href="href"
        :class="['text-primary underline-offset-4 hover:underline', $props.class]"
    >
        <slot />
    </Link>
</template>
