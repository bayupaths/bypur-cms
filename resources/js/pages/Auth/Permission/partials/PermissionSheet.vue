<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
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
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';
import InputError from '@/components/common/InputError.vue';
import type { Permission } from '@/types';

const props = defineProps<{
    open: boolean;
    permission?: Permission | null;
    groups: string[];
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const isEdit = computed(() => !!props.permission?.id);

// ── Mode switcher (create only) ───────────────────────────────────────────────
type Mode = 'module_crud' | 'custom';
const mode = ref<Mode>('module_crud');

const BASIC_ACTIONS = [
    { value: 'view',   label: 'View',   description: 'Melihat data' },
    { value: 'create', label: 'Create', description: 'Membuat data baru' },
    { value: 'edit',   label: 'Edit',   description: 'Mengubah data' },
    { value: 'delete', label: 'Delete', description: 'Menghapus data' },
] as const;

// ── module_crud form ──────────────────────────────────────────────────────────
const crudForm = useForm({
    mode: 'module_crud' as const,
    group: '',
    guard_name: 'web',
    selected_actions: ['view', 'create', 'edit', 'delete'] as string[],
});

// ── custom / edit form ────────────────────────────────────────────────────────
const customForm = useForm({
    mode: 'custom' as const,
    group: '',
    action: '',
    display_name: '',
    guard_name: 'web',
    description: '',
});

const normalize = (value: string): string =>
    value
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_-]/g, '')
        .replace(/_+/g, '_')
        .replace(/^[_-]+|[_-]+$/g, '');

const crudPreviews = computed(() => {
    const g = normalize(crudForm.group);
    if (!g) return [];
    return crudForm.selected_actions.map((a) => `${g}.${a}`);
});

const customPreview = computed(() => {
    const g = normalize(customForm.group);
    const a = normalize(customForm.action);
    if (!g && !a) return '';
    if (!a) return g;
    if (!g) return a;
    return `${g}.${a}`;
});

function toggleAction(action: string) {
    const idx = crudForm.selected_actions.indexOf(action);
    if (idx >= 0) crudForm.selected_actions.splice(idx, 1);
    else crudForm.selected_actions.push(action);
}

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.permission) {
            // Edit mode — populate customForm
            const parts = (props.permission.name ?? '').split('.', 2);
            customForm.group        = props.permission.group ?? parts[0] ?? '';
            customForm.action       = parts[1] ?? '';
            customForm.display_name = props.permission.display_name ?? '';
            customForm.guard_name   = props.permission.guard_name ?? 'web';
            customForm.description  = props.permission.description ?? '';
        } else {
            // Create mode — reset both
            crudForm.reset();
            crudForm.guard_name        = 'web';
            crudForm.selected_actions  = ['view', 'create', 'edit', 'delete'];
            customForm.reset();
            customForm.guard_name = 'web';
            mode.value = 'module_crud';
        }
    },
);

function close() {
    emit('update:open', false);
}

function submit() {
    if (isEdit.value && props.permission) {
        // Edit: send single permission via update endpoint
        customForm
            .transform((d) => ({
                name:         customPreview.value || d.group,
                display_name: d.display_name,
                group:        d.group,
                guard_name:   d.guard_name,
                description:  d.description,
            }))
            .put(route('auth.permissions.update', props.permission.id), {
                preserveScroll: true,
                onSuccess: close,
            });
        return;
    }

    if (mode.value === 'module_crud') {
        crudForm.post(route('auth.permissions.store'), {
            preserveScroll: true,
            onSuccess: () => {
                close();
                crudForm.reset();
                crudForm.guard_name       = 'web';
                crudForm.selected_actions = ['view', 'create', 'edit', 'delete'];
            },
        });
    } else {
        customForm
            .transform((d) => ({
                mode:         'custom',
                group:        d.group,
                action:       d.action,
                display_name: d.display_name,
                guard_name:   d.guard_name,
                description:  d.description,
            }))
            .post(route('auth.permissions.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    close();
                    customForm.reset();
                    customForm.guard_name = 'web';
                },
            });
    }
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex w-full flex-col overflow-hidden sm:max-w-lg">
            <SheetHeader class="border-b px-6 py-4">
                <SheetTitle>{{ isEdit ? 'Edit Permission' : 'Tambah Permission' }}</SheetTitle>
                <SheetDescription>
                    {{ isEdit ? 'Ubah informasi permission.' : 'Buat permission baru untuk mengatur hak akses.' }}
                </SheetDescription>
            </SheetHeader>

            <!-- ── Edit Mode ──────────────────────────────────────────────── -->
            <form v-if="isEdit" class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                <div class="space-y-3 px-6 py-4">
                    <!-- Group -->
                    <div class="grid gap-1.5">
                        <Label>Group <span class="text-destructive">*</span></Label>
                        <Select v-if="groups.length" v-model="customForm.group">
                            <SelectTrigger class="w-full" :aria-invalid="!!customForm.errors.group">
                                <SelectValue placeholder="Pilih group..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="g in groups" :key="g" :value="g">{{ g }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Input v-else v-model="customForm.group" placeholder="contoh: users, posts" :aria-invalid="!!customForm.errors.group" />
                        <InputError :message="customForm.errors.group" />
                    </div>

                    <!-- Action -->
                    <div class="grid gap-1.5">
                        <Label>Action <span class="text-destructive">*</span></Label>
                        <Input v-model="customForm.action" placeholder="contoh: view, create, export" :aria-invalid="!!customForm.errors.action" />
                        <InputError :message="customForm.errors.action" />
                    </div>

                    <!-- Preview -->
                    <div v-if="customPreview" class="rounded-md border bg-muted/40 px-3 py-2 text-sm">
                        <span class="text-xs text-muted-foreground">Preview: </span>
                        <code class="font-mono">{{ customPreview }}</code>
                    </div>

                    <!-- Display Name -->
                    <div class="grid gap-1.5">
                        <Label>Display Name</Label>
                        <Input v-model="customForm.display_name" placeholder="contoh: Lihat Data Users" :aria-invalid="!!customForm.errors.display_name" />
                        <InputError :message="customForm.errors.display_name" />
                    </div>

                    <!-- Guard -->
                    <div class="grid gap-1.5">
                        <Label>Guard</Label>
                        <Input v-model="customForm.guard_name" placeholder="web" :aria-invalid="!!customForm.errors.guard_name" />
                        <InputError :message="customForm.errors.guard_name" />
                    </div>

                    <!-- Description -->
                    <div class="grid gap-1.5">
                        <Label>Deskripsi</Label>
                        <Textarea v-model="customForm.description" placeholder="Deskripsi singkat..." rows="3" :aria-invalid="!!customForm.errors.description" />
                        <InputError :message="customForm.errors.description" />
                    </div>
                </div>

                <SheetFooter class="mt-auto border-t px-6 py-4">
                    <Button type="button" variant="outline" :disabled="customForm.processing" @click="close">Batal</Button>
                    <Button type="submit" :disabled="customForm.processing">
                        {{ customForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </SheetFooter>
            </form>

            <!-- ── Create Mode ────────────────────────────────────────────── -->
            <template v-else>
                <!-- Mode Switcher -->
                <div class="px-6 pt-4">
                    <div class="flex gap-2 rounded-lg border p-1">
                        <button
                            type="button"
                            class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                            :class="mode === 'module_crud' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                            @click="mode = 'module_crud'"
                        >
                            Module CRUD
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                            :class="mode === 'custom' ? 'bg-primary text-primary-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                            @click="mode = 'custom'"
                        >
                            Custom
                        </button>
                    </div>
                </div>

                <!-- Module CRUD Form -->
                <form v-if="mode === 'module_crud'" class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                    <div class="space-y-3 px-6 py-4">
                        <!-- Group -->
                        <div class="grid gap-1.5">
                            <Label>Group <span class="text-destructive">*</span></Label>
                            <Select v-if="groups.length" v-model="crudForm.group">
                                <SelectTrigger :aria-invalid="!!crudForm.errors.group">
                                    <SelectValue placeholder="Pilih group..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="g in groups" :key="g" :value="g">{{ g }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <Input v-else v-model="crudForm.group" placeholder="contoh: users, posts" :aria-invalid="!!crudForm.errors.group" />
                            <InputError :message="crudForm.errors.group" />
                        </div>

                        <!-- Actions -->
                        <div class="grid gap-1.5">
                            <Label>Permission Actions <span class="text-destructive">*</span></Label>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="action in BASIC_ACTIONS"
                                    :key="action.value"
                                    type="button"
                                    class="flex items-start gap-3 rounded-lg border p-3 text-left transition-colors"
                                    :class="
                                        crudForm.selected_actions.includes(action.value)
                                            ? 'border-primary bg-primary/5'
                                            : 'hover:bg-muted/50'
                                    "
                                    @click="toggleAction(action.value)"
                                >
                                    <div
                                        class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border"
                                        :class="
                                            crudForm.selected_actions.includes(action.value)
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-input'
                                        "
                                    >
                                        <svg
                                            v-if="crudForm.selected_actions.includes(action.value)"
                                            class="h-3 w-3"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="3"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="space-y-0.5">
                                        <div class="text-sm font-medium">{{ action.label }}</div>
                                        <div class="text-xs text-muted-foreground">{{ action.description }}</div>
                                    </div>
                                </button>
                            </div>
                            <InputError :message="(crudForm.errors as any).selected_actions" />
                        </div>

                        <!-- Preview -->
                        <div v-if="crudPreviews.length" class="grid gap-1.5">
                            <Label>Preview Permission</Label>
                            <div class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="perm in crudPreviews"
                                    :key="perm"
                                    class="inline-flex items-center rounded-md bg-muted px-2 py-1 font-mono text-xs"
                                >
                                    {{ perm }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <SheetFooter class="mt-auto border-t px-6 py-4">
                        <Button type="button" variant="outline" :disabled="crudForm.processing" @click="close">Batal</Button>
                        <Button type="submit" :disabled="crudForm.processing || crudForm.selected_actions.length === 0">
                            {{ crudForm.processing ? 'Menyimpan...' : 'Tambah Permission' }}
                        </Button>
                    </SheetFooter>
                </form>

                <!-- Custom Form -->
                <form v-else class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                    <div class="space-y-3 px-6 py-4">
                        <!-- Group -->
                        <div class="grid gap-1.5">
                            <Label>Group <span class="text-destructive">*</span></Label>
                            <Select v-if="groups.length" v-model="customForm.group">
                                <SelectTrigger :aria-invalid="!!customForm.errors.group">
                                    <SelectValue placeholder="Pilih group..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="g in groups" :key="g" :value="g">{{ g }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <Input v-else v-model="customForm.group" placeholder="contoh: users, posts" :aria-invalid="!!customForm.errors.group" />
                            <InputError :message="customForm.errors.group" />
                        </div>

                        <!-- Action -->
                        <div class="grid gap-1.5">
                            <Label>Action <span class="text-destructive">*</span></Label>
                            <Input v-model="customForm.action" placeholder="contoh: export, publish" :aria-invalid="!!customForm.errors.action" />
                            <p class="text-xs text-muted-foreground">Nama aksi yang diizinkan. Gunakan kata kerja deskriptif.</p>
                            <InputError :message="customForm.errors.action" />
                        </div>

                        <!-- Preview -->
                        <div v-if="customPreview" class="rounded-md border bg-muted/40 px-3 py-2 text-sm">
                            <span class="text-xs text-muted-foreground">Hasil: </span>
                            <code class="font-mono">{{ customPreview }}</code>
                        </div>

                        <!-- Display Name -->
                        <div class="grid gap-1.5">
                            <Label>Display Name</Label>
                            <Input v-model="customForm.display_name" placeholder="contoh: Export Reports" :aria-invalid="!!customForm.errors.display_name" />
                            <p class="text-xs text-muted-foreground">Opsional. Kosongkan untuk auto-generate.</p>
                            <InputError :message="customForm.errors.display_name" />
                        </div>

                        <!-- Description -->
                        <div class="grid gap-1.5">
                            <Label>Deskripsi</Label>
                            <Textarea v-model="customForm.description" placeholder="Deskripsi singkat tentang permission ini..." rows="3" :aria-invalid="!!customForm.errors.description" />
                            <InputError :message="customForm.errors.description" />
                        </div>
                    </div>

                    <SheetFooter class="mt-auto border-t px-6 py-4">
                        <Button type="button" variant="outline" :disabled="customForm.processing" @click="close">Batal</Button>
                        <Button type="submit" :disabled="customForm.processing">
                            {{ customForm.processing ? 'Menyimpan...' : 'Tambah Permission' }}
                        </Button>
                    </SheetFooter>
                </form>
            </template>
        </SheetContent>
    </Sheet>
</template>
