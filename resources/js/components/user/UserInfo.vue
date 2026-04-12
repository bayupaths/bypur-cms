<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/shared/useInitials';

const props = defineProps<{
    user: {
        name: string;
        email: string;
        avatar?: string;
    };
}>();

const { getInitials } = useInitials();
</script>

<template>
    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
        <Avatar class="h-8 w-8 rounded-lg">
            <AvatarImage :src="user.avatar ?? ''" :alt="user.name" />
            <AvatarFallback class="rounded-lg">{{ getInitials(user.name) }}</AvatarFallback>
        </Avatar>
        <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-semibold">{{ user.name }}</span>
            <span class="truncate text-xs text-muted-foreground">{{ user.email }}</span>
        </div>
    </div>
</template>
