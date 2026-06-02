<script setup lang="ts">
import InputError from '@/components/common/InputError.vue';
import TextLink from '@/components/common/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <AuthSplitLayout
        title="Forgot password"
        description="Enter your email and we'll send you a reset link"
    >
        <Head title="Forgot Password" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                Send Reset Link
            </Button>

            <p class="text-center text-sm text-muted-foreground">
                Remembered your password?
                <TextLink :href="route('login')">Back to login</TextLink>
            </p>
        </form>
    </AuthSplitLayout>
</template>
