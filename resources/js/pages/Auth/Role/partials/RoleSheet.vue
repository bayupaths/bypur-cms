<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import InputError from '@/components/common/InputError.vue';
import type { Role } from '@/types';

const props = defineProps<{
    open: boolean;
    role?: Role | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isEdit = computed(() => !!props.role?.id);

const form = useForm({
    name: '',
    display_name: '',
    description: '',
    guard_name: 'web',
    level: undefined as number | undefined,
    is_system: false,
});

watch(
    () => props.open,
    (val) => {
        if (!val) {
            form.reset();
            form.clearErrors();
            return;
        }
        if (props.role) {
            form.name = props.role.name;
            form.display_name = props.role.display_name ?? '';
            form.description = props.role.description ?? '';
            form.guard_name = props.role.guard_name ?? 'web';
            form.level = props.role.level ?? undefined;
            form.is_system = props.role.is_system ?? false;
        } else {
            form.reset();
            form.clearErrors();
            form.guard_name = 'web';
        }
    },
);

function close() {
    emit('update:open', false);
}

function submit() {
    if (isEdit.value && props.role) {
        form.put(route('auth.roles.update', props.role.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('auth.roles.store'), {
            preserveScroll: true,
            onSuccess: () => {
                close();
                form.reset();
                form.guard_name = 'web';
            },
        });
    }
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex w-full flex-col overflow-hidden sm:max-w-md">
            <SheetHeader class="border-b px-6 py-4">
                <SheetTitle>{{ isEdit ? 'Edit Role' : 'Tambah Role' }}</SheetTitle>
                <SheetDescription>
                    {{ isEdit ? 'Ubah informasi role.' : 'Buat role baru untuk mengatur hak akses.' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                <div class="space-y-3 px-6 py-4">
                    <!-- Name -->
                    <div class="grid gap-1.5">
                        <Label>Nama <span class="text-destructive">*</span></Label>
                        <Input
                            v-model="form.name"
                            placeholder="contoh: admin, editor"
                            :aria-invalid="!!form.errors.name"
                        />
                        <p class="text-xs text-muted-foreground">Nama unik untuk sistem (lowercase, gunakan underscore)</p>
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Display Name -->
                    <div class="grid gap-1.5">
                        <Label>Display Name</Label>
                        <Input
                            v-model="form.display_name"
                            placeholder="contoh: Administrator"
                            :aria-invalid="!!form.errors.display_name"
                        />
                        <InputError :message="form.errors.display_name" />
                    </div>

                    <!-- Guard & Level -->
                    <div class="grid grid-cols-2 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>Guard</Label>
                            <Input
                                v-model="form.guard_name"
                                placeholder="web"
                                :aria-invalid="!!form.errors.guard_name"
                            />
                            <InputError :message="form.errors.guard_name" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Level</Label>
                            <Input
                                v-model.number="form.level"
                                type="number"
                                min="0"
                                placeholder="0"
                                :aria-invalid="!!form.errors.level"
                            />
                            <InputError :message="form.errors.level" />
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="grid gap-1.5">
                        <Label>Deskripsi</Label>
                        <Textarea
                            v-model="form.description"
                            placeholder="Deskripsi singkat tentang role ini..."
                            rows="3"
                            :aria-invalid="!!form.errors.description"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <!-- Is System -->
                    <div class="flex items-center justify-between rounded-lg border px-4 py-3">
                        <div class="grid gap-0.5">
                            <Label class="cursor-pointer" for="is_system">Role Sistem</Label>
                            <p class="text-xs text-muted-foreground">Role sistem tidak dapat dihapus.</p>
                        </div>
                        <Switch id="is_system" v-model:checked="form.is_system" />
                    </div>
                </div>

                <SheetFooter class="mt-auto border-t px-6 py-4">
                    <Button type="button" variant="outline" :disabled="form.processing" @click="close">Batal</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan' : 'Tambah Role') }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
