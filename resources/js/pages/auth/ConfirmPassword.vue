<script setup lang="ts">
import InputError from '@/components/common/InputError.vue';
import PasswordField from '@/components/common/PasswordField.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AuthSplitLayout from '@/layouts/auth/AuthSplitLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    password: '',
});

function submit() {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <AuthSplitLayout
        title="Confirm password"
        description="Please confirm your password before continuing"
    >
        <Head title="Confirm Password" />

        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
                <Label for="password">Password</Label>
                <PasswordField
                    id="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Password"
                    autofocus
                />
                <InputError :message="form.errors.password" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                Confirm
            </Button>
        </form>
    </AuthSplitLayout>
</template>
