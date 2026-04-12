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
import { Textarea } from '@/components/ui/textarea';
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
    // Profile
    avatar: '',
    phone: '',
    gender: '' as 'male' | 'female' | '',
    birth_date: '',
    bio: '',
    // Address
    address: '',
    city: '',
    country: 'Indonesia',
    postal_code: '',
    // Social
    website: '',
    github: '',
    linkedin: '',
    twitter: '',
    instagram: '',
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
            form.avatar = u.avatar ?? '';
            form.phone = u.phone ?? '';
            form.gender = u.gender ?? '';
            form.birth_date = u.birth_date ?? '';
            form.bio = u.bio ?? '';
            form.address = u.address ?? '';
            form.city = u.city ?? '';
            form.country = u.country ?? 'Indonesia';
            form.postal_code = u.postal_code ?? '';
            form.website = u.website ?? '';
            form.github = u.github ?? '';
            form.linkedin = u.linkedin ?? '';
            form.twitter = u.twitter ?? '';
            form.instagram = u.instagram ?? '';
            form.is_active = u.is_active;
            form.is_superadmin = u.is_superadmin;
            form.roles = (u as any).roles_list?.map((r: any) => r.id) ?? [];
        } else {
            form.reset();
            form.country = 'Indonesia';
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
                form.country = 'Indonesia';
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

                <!-- ── Profil ─────────────────────────────────────────────────── -->
                <div class="space-y-3 border-b px-6 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Profil</p>

                    <div class="grid grid-cols-2 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>Telepon</Label>
                            <Input v-model="form.phone" placeholder="+62..." :aria-invalid="!!form.errors.phone" />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Jenis Kelamin</Label>
                            <Select v-model="form.gender">
                                <SelectTrigger :aria-invalid="!!form.errors.gender">
                                    <SelectValue placeholder="Pilih..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="male">Laki-laki</SelectItem>
                                    <SelectItem value="female">Perempuan</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.gender" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>Tanggal Lahir</Label>
                            <Input v-model="form.birth_date" type="date" :aria-invalid="!!form.errors.birth_date" />
                            <InputError :message="form.errors.birth_date" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Avatar URL</Label>
                            <Input v-model="form.avatar" placeholder="https://..." :aria-invalid="!!form.errors.avatar" />
                            <InputError :message="form.errors.avatar" />
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label>Bio</Label>
                        <Textarea v-model="form.bio" placeholder="Deskripsi singkat..." rows="2" :aria-invalid="!!form.errors.bio" />
                        <InputError :message="form.errors.bio" />
                    </div>
                </div>

                <!-- ── Alamat ─────────────────────────────────────────────────── -->
                <div class="space-y-3 border-b px-6 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Alamat</p>

                    <div class="grid gap-1.5">
                        <Label>Alamat</Label>
                        <Input v-model="form.address" placeholder="Jalan, nomor, dll." :aria-invalid="!!form.errors.address" />
                        <InputError :message="form.errors.address" />
                    </div>

                    <div class="grid grid-cols-2 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>Kota</Label>
                            <Input v-model="form.city" placeholder="Jakarta" :aria-invalid="!!form.errors.city" />
                            <InputError :message="form.errors.city" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Kode Pos</Label>
                            <Input v-model="form.postal_code" placeholder="12345" :aria-invalid="!!form.errors.postal_code" />
                            <InputError :message="form.errors.postal_code" />
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <Label>Negara</Label>
                        <Input v-model="form.country" placeholder="Indonesia" :aria-invalid="!!form.errors.country" />
                        <InputError :message="form.errors.country" />
                    </div>
                </div>

                <!-- ── Social ─────────────────────────────────────────────────── -->
                <div class="space-y-3 border-b px-6 py-3">
                    <p class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Sosial Media</p>

                    <div class="grid grid-cols-2 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>Website</Label>
                            <Input v-model="form.website" placeholder="https://..." :aria-invalid="!!form.errors.website" />
                            <InputError :message="form.errors.website" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>GitHub</Label>
                            <Input v-model="form.github" placeholder="username" :aria-invalid="!!form.errors.github" />
                            <InputError :message="form.errors.github" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 items-start gap-3">
                        <div class="grid gap-1.5">
                            <Label>LinkedIn</Label>
                            <Input v-model="form.linkedin" placeholder="username" :aria-invalid="!!form.errors.linkedin" />
                            <InputError :message="form.errors.linkedin" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Twitter/X</Label>
                            <Input v-model="form.twitter" placeholder="username" :aria-invalid="!!form.errors.twitter" />
                            <InputError :message="form.errors.twitter" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Instagram</Label>
                            <Input v-model="form.instagram" placeholder="username" :aria-invalid="!!form.errors.instagram" />
                            <InputError :message="form.errors.instagram" />
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
