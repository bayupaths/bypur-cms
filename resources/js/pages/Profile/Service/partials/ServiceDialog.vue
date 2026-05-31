<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Loader2 } from 'lucide-vue-next';
import InputError from '@/components/common/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import { serviceSchema } from '@/schemas/serviceSchema';
import type { Service } from '../Index.vue';

const props = defineProps<{
    open: boolean;
    service?: Service | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    title:       '',
    slug:        '',
    description: '',
    icon:        '',
    price_from:  null as number | null,
    is_active:   true,
});

const clientErrors = ref<Partial<Record<string, string>>>({});

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.service) {
            form.title       = props.service.title;
            form.slug        = props.service.slug;
            form.description = props.service.description;
            form.icon        = props.service.icon ?? '';
            form.price_from  = props.service.price_from ?? null;
            form.is_active   = props.service.is_active;
        } else {
            form.reset();
            form.is_active = true;
        }
    },
);

// Auto-generate slug from title (only when creating)
watch(
    () => form.title,
    (val) => {
        if (props.service) return;
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

    const result = serviceSchema.safeParse({
        ...form.data(),
        is_active: Boolean(form.is_active),
    });
    if (!result.success) {
        const flat = result.error.flatten().fieldErrors;
        clientErrors.value = Object.fromEntries(
            Object.entries(flat).map(([k, v]) => [k, v?.[0] ?? '']),
        );
        return;
    }

    if (props.service) {
        form.put(route('profile.services.update', props.service.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('profile.services.store'), {
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
        <SheetContent class="flex w-full flex-col overflow-hidden sm:max-w-lg">
            <SheetHeader class="border-b px-6 py-4">
                <SheetTitle>{{ service ? 'Edit Layanan' : 'Tambah Layanan' }}</SheetTitle>
                <SheetDescription>
                    {{ service ? 'Ubah detail layanan' : 'Isi detail layanan baru' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                <div class="space-y-5 px-6 py-5">

                    <!-- Judul & Slug -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="title">Judul <span class="text-destructive">*</span></Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="Web Development"
                                :class="{ 'border-destructive': form.errors.title || clientErrors.title }"
                                autofocus
                            />
                            <InputError :message="form.errors.title ?? clientErrors.title" />
                        </div>
                        <div class="grid gap-1.5">
                            <div class="flex items-center justify-between">
                                <Label for="slug">Slug <span class="text-destructive">*</span></Label>
                                <span v-if="!service" class="text-[10px] text-muted-foreground">Auto-generate</span>
                            </div>
                            <Input
                                id="slug"
                                v-model="form.slug"
                                placeholder="web-development"
                                :class="{ 'border-destructive': form.errors.slug || clientErrors.slug }"
                            />
                            <InputError :message="form.errors.slug ?? clientErrors.slug" />
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="grid gap-1.5">
                        <Label for="description">Deskripsi <span class="text-destructive">*</span></Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Jelaskan layanan yang Anda tawarkan…"
                            :rows="4"
                            class="resize-y"
                            :class="{ 'border-destructive': form.errors.description || clientErrors.description }"
                        />
                        <InputError :message="form.errors.description ?? clientErrors.description" />
                    </div>

                    <!-- Icon -->
                    <div class="grid gap-1.5">
                        <Label for="icon">Icon</Label>
                        <Input
                            id="icon"
                            v-model="form.icon"
                            placeholder="i-logos-vuejs  atau  logos:vuejs"
                            :class="{ 'border-destructive': form.errors.icon || clientErrors.icon }"
                        />
                        <p class="text-xs text-muted-foreground">
                            Format Iconify: <code class="rounded bg-muted px-1 font-mono text-[11px]">i-{collection}-{name}</code>
                            atau <code class="rounded bg-muted px-1 font-mono text-[11px]">{collection}:{name}</code>
                        </p>
                        <InputError :message="form.errors.icon ?? clientErrors.icon" />
                    </div>

                    <!-- Harga & Status -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <div class="flex items-center justify-between">
                                <Label for="price_from">Harga Mulai</Label>
                                <span class="text-[10px] text-muted-foreground">Opsional</span>
                            </div>
                            <Input
                                id="price_from"
                                :model-value="form.price_from !== null ? String(form.price_from) : ''"
                                type="number"
                                min="0"
                                placeholder="e.g. 500000"
                                :class="{ 'border-destructive': form.errors.price_from || clientErrors.price_from }"
                                @update:model-value="(v) => { form.price_from = v !== '' ? Number(v) : null; }"
                            />
                            <InputError :message="form.errors.price_from ?? clientErrors.price_from" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label>Status</Label>
                            <div
                                class="flex h-9 items-center gap-2.5 rounded-md border px-3 transition-colors"
                                :class="form.is_active ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/30' : ''"
                            >
                                <Switch
                                    id="is_active"
                                    :checked="form.is_active"
                                    @update:checked="(v: boolean) => (form.is_active = v)"
                                />
                                <Label for="is_active" class="cursor-pointer text-sm">
                                    {{ form.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Label>
                            </div>
                        </div>
                    </div>

                </div>

                <SheetFooter class="border-t px-6 py-4">
                    <Button type="button" variant="outline" :disabled="form.processing" @click="close">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-1.5 h-4 w-4 animate-spin" />
                        {{ service ? 'Simpan Perubahan' : 'Tambahkan' }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
