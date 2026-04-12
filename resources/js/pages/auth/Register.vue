<script setup lang="ts">
import InputError from '@/components/common/InputError.vue';
import PasswordField from '@/components/common/PasswordField.vue';
import TextLink from '@/components/common/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <AuthSplitLayout title="Create an account" description="Enter your details below to get started">
        <Head title="Register" />

        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    placeholder="email@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <Label for="password">Password</Label>
                <PasswordField
                    id="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Password"
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
                    placeholder="Confirm password"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                Register
            </Button>

            <p class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')">Log in</TextLink>
            </p>
        </form>
    </AuthSplitLayout>
</template>
