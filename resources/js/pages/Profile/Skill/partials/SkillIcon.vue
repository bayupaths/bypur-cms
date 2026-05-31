<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const props = defineProps<{ icon: string | null; name: string }>();

const loadError = ref(false);
watch(() => props.icon, () => { loadError.value = false; });

const src = computed(() => {
    const raw = props.icon?.trim();
    if (!raw) return '';
    let collection: string, iconName: string;
    if (raw.includes(':')) {
        const parts = raw.split(':');
        collection = parts[0];
        iconName = parts[1];
    } else {
        const stripped = raw.startsWith('i-') ? raw.slice(2) : raw;
        const firstDash = stripped.indexOf('-');
        if (firstDash === -1) return '';
        collection = stripped.slice(0, firstDash);
        iconName = stripped.slice(firstDash + 1);
    }
    if (!collection || !iconName) return '';
    return `https://api.iconify.design/${collection}/${iconName}.svg`;
});

const initials = computed(() => props.name.slice(0, 2).toUpperCase());
</script>

<template>
    <div
        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-linear-to-br from-muted to-muted/60 text-xs font-bold text-muted-foreground shadow-xs"
    >
        <img
            v-if="src && !loadError"
            :src="src"
            :alt="name"
            class="h-5 w-5"
            @error="loadError = true"
        />
        <span v-else>{{ initials }}</span>
    </div>
</template>
