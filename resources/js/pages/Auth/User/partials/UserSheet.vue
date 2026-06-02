<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { storeUserSchema, updateUserSchema } from '@/schemas/userSchema';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Switch } from '@/components/ui/switch';
import InputError from '@/components/common/InputError.vue';
import PasswordField from '@/components/common/PasswordField.vue';
import type { User } from '@/types';

interface RoleItem {
    id: number;
    name: string;
    display_name: string | null;
}

const props = defineProps<{
    open: boolean;
    user?: User | null;
    roles: RoleItem[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isEdit = computed(() => !!props.user);

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    // Status
    is_active: true,
    is_superadmin: false,
    // Roles
    roles: [] as number[],
});

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.user) {
            const u = props.user;
            form.name = u.name;
            form.username = u.username ?? '';
            form.email = u.email;
            form.password = '';
            form.password_confirmation = '';
            form.is_active = u.is_active;
            form.is_superadmin = u.is_superadmin;
            form.roles = (u as any).roles_list?.map((r: any) => r.id) ?? [];
        } else {
            form.reset();
            form.is_active = true;
            form.is_superadmin = false;
        }
    },
);

function close() {
    emit('update:open', false);
}

function toggleRole(id: number) {
    const idx = form.roles.indexOf(id);
    if (idx === -1) form.roles.push(id);
    else form.roles.splice(idx, 1);
}

function submit() {
    const schema = isEdit.value ? updateUserSchema : storeUserSchema;
    const result = schema.safeParse(form.data());

    if (!result.success) {
        result.error.issues.forEach((e) => {
            form.setError(e.path[0] as any, e.message);
        });
        return;
    }

    if (isEdit.value && props.user) {
        form.put(route('auth.users.update', props.user.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('auth.users.store'), {
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
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex w-full flex-col overflow-hidden sm:max-w-lg">
            <SheetHeader class="border-b px-6 py-4">
                <SheetTitle>{{ isEdit ? 'Edit User' : 'Tambah User' }}</SheetTitle>
                <SheetDescription>
                    {{ isEdit ? 'Ubah detail user di bawah ini.' : 'Isi detail user baru di bawah ini.' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">

                <!-- ── Akun ──────────────────────────────────────────────────── -->
                <div class="space-y-3 border-b px-6 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Akun</p>

                    <div class="grid gap-1.5">
                        <Label>Nama <span class="text-destructive">*</span></Label>
                        <Input v-model="form.name" placeholder="Nama lengkap" :aria-invalid="!!form.errors.name" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid grid-cols-2 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>Username</Label>
                            <Input v-model="form.username" placeholder="username" :aria-invalid="!!form.errors.username" />
                            <InputError :message="form.errors.username" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Email <span class="text-destructive">*</span></Label>
                            <Input v-model="form.email" type="email" placeholder="email@example.com" :aria-invalid="!!form.errors.email" />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>{{ isEdit ? 'Password Baru' : 'Password' }} {{ !isEdit ? '*' : '' }}</Label>
                            <PasswordField v-model="form.password" placeholder="Min. 8 karakter" :aria-invalid="!!form.errors.password" />
                            <InputError :message="form.errors.password" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Konfirmasi Password {{ !isEdit ? '*' : '' }}</Label>
                            <PasswordField v-model="form.password_confirmation" placeholder="Ulangi password" :aria-invalid="!!form.errors.password_confirmation" />
                            <InputError :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                </div>

                <!-- ── Roles ──────────────────────────────────────────────────── -->
                <div v-if="roles.length" class="space-y-3 border-b px-6 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Roles</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="role in roles"
                            :key="role.id"
                            type="button"
                            :class="[
                                'rounded-md border px-3 py-1 text-xs transition-colors',
                                form.roles.includes(role.id)
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-input bg-background text-foreground hover:bg-accent',
                            ]"
                            @click="toggleRole(role.id)"
                        >
                            {{ role.display_name ?? role.name }}
                        </button>
                    </div>
                    <InputError :message="form.errors.roles" />
                </div>

                <!-- ── Status ─────────────────────────────────────────────────── -->
                <div class="space-y-3 px-6 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Status</p>

                    <div class="flex items-center justify-between">
                        <div>
                            <Label>Aktif</Label>
                            <p class="text-xs text-muted-foreground">User dapat masuk ke sistem</p>
                        </div>
                        <Switch v-model:checked="form.is_active" />
                    </div>

                    <Separator />

                    <div class="flex items-center justify-between">
                        <div>
                            <Label>Super Admin</Label>
                            <p class="text-xs text-muted-foreground">Akses penuh ke semua fitur</p>
                        </div>
                        <Switch v-model:checked="form.is_superadmin" />
                    </div>
                </div>

                <!-- ── Footer ─────────────────────────────────────────────────── -->
                <SheetFooter class="border-t px-6 py-3">
                    <Button type="button" variant="outline" @click="close">Batal</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Buat User') }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
