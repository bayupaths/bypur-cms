<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from './AppLogoIcon.vue';
</script>

<template>
    <Link :href="route('dashboard')" class="flex items-center gap-2">
        <AppLogoIcon class="size-8" />
        <span class="truncate font-semibold text-sm">Portfolio Admin</span>
    </Link>
</template>
