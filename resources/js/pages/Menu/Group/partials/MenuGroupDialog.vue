<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { menuGroupSchema, type MenuGroupForm } from '@/schemas/menuSchema';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import type { MenuGroup } from '../columns';

const props = defineProps<{
    open: boolean;
    group?: MenuGroup | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    is_active: true,
});

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.group) {
            form.name = props.group.name;
            form.display_name = props.group.display_name ?? '';
            form.description = props.group.description ?? '';
            form.is_active = props.group.is_active;
        } else {
            form.reset();
            form.is_active = true;
        }
    },
);

function close() {
    emit('update:open', false);
}

function submit() {
    const result = menuGroupSchema.safeParse(form.data());
    if (!result.success) {
        result.error.issues.forEach((e) => {
            form.setError(e.path[0] as keyof MenuGroupForm, e.message);
        });
        return;
    }

    if (props.group) {
        form.put(route('menu.groups.update', props.group.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('menu.groups.store'), {
            preserveScroll: true,
            onSuccess: () => {
                close();
                form.reset();
                form.is_active = true;
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>{{ group ? 'Edit Group' : 'Tambah Group' }}</DialogTitle>
            </DialogHeader>

            <form class="grid gap-3" @submit.prevent="submit">
                <!-- Name -->
                <div class="grid gap-1.5">
                    <Label for="name">Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        placeholder="e.g. main-navigation"
                        :class="{ 'border-destructive': form.errors.name }"
                        autofocus
                    />
                    <p v-if="form.errors.name" class="text-xs text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>

                <!-- Display Name -->
                <div class="grid gap-1.5">
                    <Label for="display_name">Display Name</Label>
                    <Input
                        id="display_name"
                        v-model="form.display_name"
                        placeholder="e.g. Main Navigation"
                        :class="{ 'border-destructive': form.errors.display_name }"
                    />
                    <p v-if="form.errors.display_name" class="text-xs text-destructive">
                        {{ form.errors.display_name }}
                    </p>
                </div>

                <!-- Description -->
                <div class="grid gap-1.5">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Deskripsi singkat..."
                        :rows="2"
                        :class="{ 'border-destructive': form.errors.description }"
                    />
                    <p v-if="form.errors.description" class="text-xs text-destructive">
                        {{ form.errors.description }}
                    </p>
                </div>

                <!-- Active -->
                <div class="flex items-center gap-2">
                    <Switch id="is_active" v-model:checked="form.is_active" />
                    <Label for="is_active" class="cursor-pointer">Active</Label>
                </div>

                <DialogFooter class="mt-1">
                    <Button type="button" variant="outline" size="sm" @click="close">
                        Cancel
                    </Button>
                    <Button type="submit" size="sm" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
