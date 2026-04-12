<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { menuSchema, type MenuForm } from '@/schemas/menuSchema';
import IconPicker from '@/components/IconPicker.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Switch } from '@/components/ui/switch';
import type { Menu } from '../columns';

interface MenuGroup {
    id: number;
    name: string;
    display_name: string | null;
}

interface ParentMenu {
    id: number;
    title: string;
    group_id: number;
}

interface RoleItem {
    id: number;
    name: string;
    display_name: string | null;
}

interface PermissionItem {
    id: number;
    name: string;
    display_name: string | null;
}

const props = defineProps<{
    open: boolean;
    menu?: Menu | null;
    groups: MenuGroup[];
    parentMenus: ParentMenu[];
    roles: RoleItem[];
    permissions: PermissionItem[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    group_id: 0,
    parent_id: null as number | null,
    title: '',
    slug: '',
    url: '',
    is_route: false,
    icon: '',
    badge: '',
    badge_variant: 'secondary',
    target: '_self',
    order: 0,
    is_active: true,
    is_divider: false,
    roles: [] as number[],
    permissions: [] as number[],
});

// Only show parent options for selected group
const filteredParents = computed(() =>
    props.parentMenus.filter(
        (m) => !form.group_id || m.group_id === form.group_id,
    ),
);

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.menu) {
            form.group_id = props.menu.group_id;
            form.parent_id = props.menu.parent_id ?? null;
            form.title = props.menu.title;
            form.slug = props.menu.slug ?? '';
            form.url = props.menu.url ?? '';
            form.is_route = props.menu.is_route;
            form.icon = props.menu.icon ?? '';
            form.badge = props.menu.badge ?? '';
            form.badge_variant = props.menu.badge_variant ?? 'secondary';
            form.target = props.menu.target ?? '_self';
            form.order = props.menu.order;
            form.is_active = props.menu.is_active;
            form.is_divider = props.menu.is_divider;
            form.roles = props.menu.roles ? [...props.menu.roles] : [];
            form.permissions = props.menu.permissions ? [...props.menu.permissions] : [];
        } else {
            form.reset();
            form.badge_variant = 'secondary';
            form.target = '_self';
            form.is_active = true;
        }
    },
);

// Reset parent when group changes
watch(
    () => form.group_id,
    () => {
        if (form.parent_id && !filteredParents.value.some((m) => m.id === form.parent_id)) {
            form.parent_id = null;
        }
    },
);

// Auto-generate slug from title (only when creating)
watch(
    () => form.title,
    (val) => {
        if (props.menu) return;
        form.slug = val
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    },
);

function close() {
    emit('update:open', false);
}

function toggle(arr: number[], id: number) {
    const idx = arr.indexOf(id);
    if (idx === -1) arr.push(id);
    else arr.splice(idx, 1);
}

function submit() {
    const result = menuSchema.safeParse(form.data());
    if (!result.success) {
        result.error.issues.forEach((e) => {
            form.setError(e.path[0] as keyof MenuForm, e.message);
        });
        return;
    }

    if (props.menu) {
        form.put(route('menu.update', props.menu.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('menu.store'), {
            preserveScroll: true,
            onSuccess: () => {
                close();
                form.reset();
                form.badge_variant = 'secondary';
                form.target = '_self';
                form.is_active = true;
            },
        });
    }
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex flex-col w-full overflow-hidden sm:max-w-lg">
            <SheetHeader class="px-6 py-4 border-b">
                <SheetTitle>{{ menu ? 'Edit Menu Item' : 'Tambah Menu Item' }}</SheetTitle>
                <SheetDescription>
                    {{ menu ? 'Ubah detail menu item di bawah ini' : 'Isi detail menu item baru di bawah ini' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-col flex-1 overflow-y-auto" @submit.prevent="submit">

                <!-- SECTION: Struktur -->
                <div class="px-6 py-4 space-y-4 border-b">
                    <p class="text-xs font-medium tracking-wider uppercase text-muted-foreground">Struktur</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label>Group <span class="text-destructive">*</span></Label>
                            <Select
                                :model-value="form.group_id ? String(form.group_id) : ''"
                                @update:model-value="(v) => (form.group_id = Number(v))"
                            >
                                <SelectTrigger class="w-full" :class="{ 'border-destructive': form.errors.group_id }">
                                    <SelectValue placeholder="Pilih group..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="g in groups" :key="g.id" :value="String(g.id)">
                                        {{ g.display_name ?? g.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p class="text-xs min-h-4 text-destructive">{{ form.errors.group_id }}</p>
                        </div>

                        <div class="grid gap-1.5">
                            <Label>Parent menu</Label>
                            <Select
                                :model-value="form.parent_id ? String(form.parent_id) : '__none__'"
                                @update:model-value="(v) => (form.parent_id = v === '__none__' ? null : Number(v))"
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Tidak ada (root)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__none__">Tidak ada (root)</SelectItem>
                                    <SelectItem v-for="m in filteredParents" :key="m.id" :value="String(m.id)">
                                        {{ m.title }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p class="text-xs min-h-4 text-destructive">{{ form.errors.parent_id }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="title">Title <span class="text-destructive">*</span></Label>
                            <Input id="title" v-model="form.title" placeholder="e.g. Dashboard"
                                :class="{ 'border-destructive': form.errors.title }" autofocus />
                            <p class="text-xs min-h-4 text-destructive">{{ form.errors.title }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" placeholder="e.g. dashboard"
                                :class="{ 'border-destructive': form.errors.slug }" />
                            <p class="text-xs min-h-4 text-destructive">{{ form.errors.slug }}</p>
                        </div>
                    </div>

                    <div class="grid gap-1.5">
                        <div class="flex items-center justify-between">
                            <Label for="url">{{ form.is_route ? 'Route name' : 'URL' }}</Label>
                            <div class="flex items-center gap-1.5">
                                <Switch id="is_route" v-model:checked="form.is_route" class="scale-90" />
                                <Label for="is_route" class="text-xs cursor-pointer text-muted-foreground">
                                    Gunakan route name
                                </Label>
                            </div>
                        </div>
                        <Input id="url" v-model="form.url"
                            :placeholder="form.is_route ? 'e.g. dashboard' : 'e.g. /dashboard'"
                            :class="{ 'border-destructive': form.errors.url }" />
                        <p class="text-xs min-h-4 text-destructive">{{ form.errors.url }}</p>
                    </div>
                </div>

                <!-- SECTION: Tampilan -->
                <div class="px-6 py-5 space-y-4 border-b">
                    <p class="text-xs font-medium tracking-wider uppercase text-muted-foreground">Tampilan</p>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label>Icon</Label>
                            <IconPicker v-model="form.icon" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="order">Order</Label>
                            <Input id="order" v-model.number="form.order" type="number" min="0"
                                :class="{ 'border-destructive': form.errors.order }" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="badge">Badge text</Label>
                            <Input id="badge" v-model="form.badge" placeholder="e.g. New" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Badge variant</Label>
                            <Select v-model="form.badge_variant">
                                <SelectTrigger class="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="default">Default</SelectItem>
                                    <SelectItem value="secondary">Secondary</SelectItem>
                                    <SelectItem value="destructive">Destructive</SelectItem>
                                    <SelectItem value="outline">Outline</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                   <div class="grid gap-1.5">
                        <Label>Target</Label>
                        <Select v-model="form.target">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih target" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="_self">Tab yang sama</SelectItem>
                                <SelectItem value="_blank">Tab baru</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <!-- SECTION: Opsi -->
                <div class="px-6 py-5 space-y-1 border-b">
                    <p class="mb-3 text-xs font-medium tracking-wider uppercase text-muted-foreground">Opsi</p>

                    <div class="flex items-center justify-between py-2.5">
                        <div>
                            <p class="text-sm font-medium">Active</p>
                            <p class="text-xs text-muted-foreground">Menu item ditampilkan ke user</p>
                        </div>
                        <Switch id="is_active" v-model:checked="form.is_active" />
                    </div>
                    <Separator />
                    <div class="flex items-center justify-between py-2.5">
                        <div>
                            <p class="text-sm font-medium">Divider</p>
                            <p class="text-xs text-muted-foreground">Tampilkan sebagai garis pemisah</p>
                        </div>
                        <Switch id="is_divider" v-model:checked="form.is_divider" />
                    </div>
                </div>

                <!-- SECTION: Akses -->
                <div class="px-6 py-5 space-y-4">
                    <p class="text-xs font-medium tracking-wider uppercase text-muted-foreground">Akses</p>

                    <template v-if="roles.length">
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label class="text-sm font-medium">Roles</Label>
                                <span class="text-muted-foreground rounded-full border px-2 py-0.5 text-xs">
                                    {{ form.roles.length }}/{{ roles.length }} dipilih
                                </span>
                            </div>
                            <div class="overflow-y-auto border divide-y rounded-md max-h-48">
                                <label
                                    v-for="role in roles" :key="role.id"
                                    class="hover:bg-muted flex cursor-pointer items-center gap-2.5 px-3 py-2 text-sm"
                                >
                                    <input type="checkbox" :value="role.id"
                                        :checked="form.roles.includes(role.id)"
                                        class="accent-primary" @change="toggle(form.roles, role.id)" />
                                    {{ role.display_name ?? role.name }}
                                </label>
                            </div>
                        </div>
                    </template>

                    <template v-if="permissions.length">
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label class="text-sm font-medium">Permissions</Label>
                                <span class="text-muted-foreground rounded-full border px-2 py-0.5 text-xs">
                                    {{ form.permissions.length }}/{{ permissions.length }} dipilih
                                </span>
                            </div>
                            <div class="overflow-y-auto border divide-y rounded-md max-h-48">
                                <label
                                    v-for="perm in permissions" :key="perm.id"
                                    class="hover:bg-muted flex cursor-pointer items-center gap-2.5 px-3 py-2 text-sm"
                                >
                                    <input type="checkbox" :value="perm.id"
                                        :checked="form.permissions.includes(perm.id)"
                                        class="accent-primary" @change="toggle(form.permissions, perm.id)" />
                                    {{ perm.display_name ?? perm.name }}
                                </label>
                            </div>
                        </div>
                    </template>
                </div>

                <SheetFooter class="px-6 py-4 border-t">
                    <Button type="button" variant="outline" size="sm" @click="close">Cancel</Button>
                    <Button type="submit" size="sm" :disabled="form.processing">
                        {{ form.processing ? 'Saving...' : 'Save menu item' }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
