<script setup lang="ts">
defineProps<{
    class?: string;
}>();
</script>

<template>
    <svg
        viewBox="0 0 100 100"
        xmlns="http://www.w3.org/2000/svg"
        :class="$props.class"
        fill="none"
    >
        <rect width="100" height="100" rx="20" class="fill-primary" />
        <text
            x="50"
            y="68"
            font-size="55"
            text-anchor="middle"
            font-family="sans-serif"
            class="fill-primary-foreground"
            font-weight="bold"
        >P</text>
    </svg>
</template>
