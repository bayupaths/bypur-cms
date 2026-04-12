<script setup lang="ts">
import UserInfo from '@/components/user/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { router, usePage } from '@inertiajs/vue3';
import { BadgeCheck, Bell, ChevronsUpDown, CreditCard, LogOut, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<{
    auth: { user: { name: string; email: string; avatar?: string } };
}>();

const user = computed(() => page.props.auth.user);

const props = defineProps<{
    showUserInfo?: boolean;
}>();

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <DropdownMenuLabel v-if="showUserInfo" class="p-0 font-normal">
        <UserInfo :user="user" />
    </DropdownMenuLabel>
    <DropdownMenuSeparator v-if="showUserInfo" />

    <DropdownMenuGroup>
        <DropdownMenuItem>
            <Sparkles class="w-4 h-4 mr-2" />
            Upgrade to Pro
        </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator />

    <DropdownMenuGroup>
        <DropdownMenuItem :as="'a'" :href="route('profile.edit')">
            <BadgeCheck class="w-4 h-4 mr-2" />
            Account
        </DropdownMenuItem>
        <DropdownMenuItem>
            <CreditCard class="w-4 h-4 mr-2" />
            Billing
        </DropdownMenuItem>
        <DropdownMenuItem>
            <Bell class="w-4 h-4 mr-2" />
            Notifications
        </DropdownMenuItem>
    </DropdownMenuGroup>

    <DropdownMenuSeparator />

    <DropdownMenuItem variant="destructive" @select="logout">
        <LogOut class="w-4 h-4 mr-2" />
        Log out
    </DropdownMenuItem>
</template>
