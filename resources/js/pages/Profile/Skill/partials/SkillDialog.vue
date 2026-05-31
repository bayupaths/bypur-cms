<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Loader2 } from 'lucide-vue-next';
import InputError from '@/components/common/InputError.vue';
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
import { SKILL_CATEGORIES, skillSchema } from '@/schemas/skillSchema';
import type { Skill } from '../Index.vue';

const props = defineProps<{
    open: boolean;
    skill?: Skill | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name:     '',
    slug:     '',
    icon:     '',
    category: '' as string,
    level:    null as number | null,
});

const clientErrors = ref<Partial<Record<string, string>>>({});

// Live icon preview from Iconify CDN
const iconPreviewUrl = computed(() => {
    const raw = form.icon?.trim();
    if (!raw) return '';
    let collection: string, name: string;
    if (raw.includes(':')) {
        const parts = raw.split(':');
        collection = parts[0];
        name = parts[1];
    } else {
        const stripped = raw.startsWith('i-') ? raw.slice(2) : raw;
        const firstDash = stripped.indexOf('-');
        if (firstDash === -1) return '';
        collection = stripped.slice(0, firstDash);
        name = stripped.slice(firstDash + 1);
    }
    if (!collection || !name) return '';
    return `https://api.iconify.design/${collection}/${name}.svg`;
});
const iconLoadError = ref(false);
watch(() => form.icon, () => { iconLoadError.value = false; });

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.skill) {
            form.name     = props.skill.name;
            form.slug     = props.skill.slug;
            form.icon     = props.skill.icon ?? '';
            form.category = props.skill.category ?? '';
            form.level    = props.skill.level ?? null;
        } else {
            form.reset();
        }
    },
);

// Auto-generate slug from name (only when creating)
watch(
    () => form.name,
    (val) => {
        if (props.skill) return;
        form.slug = val
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    },
);

function close() {
    clientErrors.value = {};
    emit('update:open', false);
}

function submit() {
    clientErrors.value = {};

    const result = skillSchema.safeParse(form.data());
    if (!result.success) {
        const flat = result.error.flatten().fieldErrors;
        clientErrors.value = Object.fromEntries(
            Object.entries(flat).map(([k, v]) => [k, v?.[0] ?? '']),
        );
        return;
    }

    if (props.skill) {
        form.put(route('profile.skills.update', props.skill.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('profile.skills.store'), {
            preserveScroll: true,
            onSuccess: () => {
                close();
                form.reset();
            },
        });
    }
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex w-full flex-col overflow-hidden sm:max-w-md">
            <SheetHeader class="border-b px-6 py-4">
                <SheetTitle>{{ skill ? 'Edit Skill' : 'Tambah Skill' }}</SheetTitle>
                <SheetDescription>
                    {{ skill ? 'Ubah detail skill' : 'Isi detail skill baru' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                <div class="space-y-4 px-6 py-5">

                    <!-- Nama & Slug -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="name">Nama <span class="text-destructive">*</span></Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="e.g. Vue.js"
                                :class="{ 'border-destructive': form.errors.name || clientErrors.name }"
                                autofocus
                            />
                            <InputError :message="form.errors.name ?? clientErrors.name" />
                        </div>
                        <div class="grid gap-1.5">
                            <div class="flex items-center justify-between">
                                <Label for="slug">Slug <span class="text-destructive">*</span></Label>
                                <span v-if="!skill" class="text-[10px] text-muted-foreground">Auto-generate</span>
                            </div>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                placeholder="e.g. vue-js"
                                :class="{ 'border-destructive': form.errors.slug || clientErrors.slug }"
                            />
                            <InputError :message="form.errors.slug ?? clientErrors.slug" />
                        </div>
                    </div>

                    <!-- Icon -->
                    <div class="grid gap-1.5">
                        <Label for="icon">Icon</Label>
                        <div class="flex items-center gap-2">
                            <!-- Preview box -->
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border bg-muted">
                                <img
                                    v-if="iconPreviewUrl && !iconLoadError"
                                    :src="iconPreviewUrl"
                                    :alt="form.icon"
                                    class="h-5 w-5"
                                    @error="iconLoadError = true"
                                />
                                <span v-else class="text-xs text-muted-foreground">
                                    {{ form.icon && iconLoadError ? '?' : '—' }}
                                </span>
                            </span>
                            <Input
                                id="icon"
                                v-model="form.icon"
                                placeholder="i-logos-vuejs  atau  logos:vuejs"
                                class="flex-1"
                                :class="{ 'border-destructive': form.errors.icon || clientErrors.icon }"
                            />
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Format Iconify: <code class="rounded bg-muted px-1 font-mono text-[11px]">i-{collection}-{name}</code>
                            atau <code class="rounded bg-muted px-1 font-mono text-[11px]">{collection}:{name}</code>
                        </p>
                        <InputError :message="form.errors.icon ?? clientErrors.icon" />
                    </div>

                    <!-- Kategori & Level -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label>Kategori</Label>
                            <Select
                                :model-value="form.category || '__none__'"
                                @update:model-value="(v) => (form.category = v === '__none__' ? '' : (v as string))"
                            >
                                <SelectTrigger class="w-full" :class="{ 'border-destructive': form.errors.category || clientErrors.category }">
                                    <SelectValue placeholder="Pilih kategori..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__none__">—</SelectItem>
                                    <SelectItem v-for="cat in SKILL_CATEGORIES" :key="cat.value" :value="cat.value">
                                        {{ cat.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.category ?? clientErrors.category" />
                        </div>
                        <div class="grid gap-1.5">
                            <div class="flex items-center justify-between">
                                <Label for="level">Level</Label>
                                <span class="text-[10px] text-muted-foreground">0 – 100</span>
                            </div>
                            <Input
                                id="level"
                                :model-value="form.level !== null ? String(form.level) : ''"
                                type="number"
                                min="0"
                                max="100"
                                placeholder="e.g. 80"
                                :class="{ 'border-destructive': form.errors.level || clientErrors.level }"
                                @update:model-value="(v) => { form.level = v !== '' ? Number(v) : null; }"
                            />
                            <InputError :message="form.errors.level ?? clientErrors.level" />
                        </div>
                    </div>
                </div>

                <SheetFooter class="border-t px-6 py-4">
                    <Button type="button" variant="outline" :disabled="form.processing" @click="close">Batal</Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-1.5 h-4 w-4 animate-spin" />
                        {{ skill ? 'Simpan Perubahan' : 'Tambahkan' }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
