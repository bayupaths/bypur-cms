<script setup lang="ts">
import InputError from '@/components/common/InputError.vue';
import PasswordField from '@/components/common/PasswordField.vue';
import TextLink from '@/components/common/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    token: string;
    email: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AuthSplitLayout title="Reset password" description="Enter your new password below">
        <Head title="Reset Password" />

        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <Label for="password">New Password</Label>
                <PasswordField
                    id="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="New password"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="space-y-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <PasswordField
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                Reset Password
            </Button>

            <p class="text-center text-sm text-muted-foreground">
                Remembered your password?
                <TextLink :href="route('login')">Back to login</TextLink>
            </p>
        </form>
    </AuthSplitLayout>
</template>
