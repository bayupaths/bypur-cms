<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Loader2, X } from 'lucide-vue-next';
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
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { experienceSchema } from '@/schemas/experienceSchema';
import type { Experience } from '../Index.vue';

const props = defineProps<{
    open: boolean;
    experience?: Experience | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const JOB_TYPES = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship', 'Remote'];

const form = useForm({
    company:     '',
    position:    '',
    location:    '',
    type:        '',
    started_at:  '',
    ended_at:    '',
    is_current:  false,
    description: '',
    tech_stack:  [] as string[],
});

const tagInput = ref('');
const clientErrors = ref<Partial<Record<string, string>>>({});

function onTagKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        const val = tagInput.value.replace(/,$/, '').trim();
        if (val && !form.tech_stack.includes(val)) {
            form.tech_stack = [...form.tech_stack, val];
        }
        tagInput.value = '';
    } else if (e.key === 'Backspace' && tagInput.value === '' && form.tech_stack.length) {
        form.tech_stack = form.tech_stack.slice(0, -1);
    }
}

function removeTag(i: number) {
    form.tech_stack = form.tech_stack.filter((_, idx) => idx !== i);
}

function toggleCurrent(v: boolean) {
    form.is_current = v;
    if (v) {
        form.ended_at = '';
        const { ended_at: _, ...rest } = clientErrors.value;
        clientErrors.value = rest;
    }
}

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.experience) {
            form.company     = props.experience.company;
            form.position    = props.experience.position;
            form.location    = props.experience.location ?? '';
            form.type        = props.experience.type ?? '';
            form.started_at  = props.experience.started_at ?? '';
            form.ended_at    = props.experience.ended_at ?? '';
            form.is_current  = props.experience.is_current;
            form.description = props.experience.description ?? '';
            form.tech_stack  = props.experience.tech_stack ? [...props.experience.tech_stack] : [];
        } else {
            form.reset();
            tagInput.value = '';
        }
    },
);

function close() {
    clientErrors.value = {};
    emit('update:open', false);
}

function submit() {
    clientErrors.value = {};

    const result = experienceSchema.safeParse({
        ...form.data(),
        is_current: Boolean(form.is_current),
        tech_stack: form.tech_stack,
    });

    if (!result.success) {
        const flat = result.error.flatten().fieldErrors;
        clientErrors.value = Object.fromEntries(
            Object.entries(flat).map(([k, v]) => [k, v?.[0] ?? '']),
        );
        return;
    }

    if (props.experience) {
        form.put(route('profile.experiences.update', props.experience.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('profile.experiences.store'), {
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
                <SheetTitle>{{ experience ? 'Edit Pengalaman' : 'Tambah Pengalaman' }}</SheetTitle>
                <SheetDescription>
                    {{ experience ? 'Ubah detail pengalaman kerja' : 'Isi detail pengalaman kerja baru' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                <div class="space-y-5 px-6 py-5">

                    <!-- Posisi & Perusahaan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="position">Posisi <span class="text-destructive">*</span></Label>
                            <Input
                                id="position"
                                v-model="form.position"
                                placeholder="Frontend Developer"
                                :class="{ 'border-destructive': form.errors.position || clientErrors.position }"
                                autofocus
                            />
                            <InputError :message="form.errors.position ?? clientErrors.position" />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="company">Perusahaan <span class="text-destructive">*</span></Label>
                            <Input
                                id="company"
                                v-model="form.company"
                                placeholder="PT. Teknologi"
                                :class="{ 'border-destructive': form.errors.company || clientErrors.company }"
                            />
                            <InputError :message="form.errors.company ?? clientErrors.company" />
                        </div>
                    </div>

                    <!-- Lokasi & Tipe -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="location">Lokasi</Label>
                            <Input
                                id="location"
                                v-model="form.location"
                                placeholder="Jakarta, Indonesia"
                                :class="{ 'border-destructive': form.errors.location || clientErrors.location }"
                            />
                            <InputError :message="form.errors.location ?? clientErrors.location" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Tipe</Label>
                            <Select
                                :model-value="form.type || '__none__'"
                                @update:model-value="(v) => (form.type = v === '__none__' ? '' : (v as string))"
                            >
                                <SelectTrigger class="w-full" :class="{ 'border-destructive': form.errors.type || clientErrors.type }">
                                    <SelectValue placeholder="Pilih tipe..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__none__">—</SelectItem>
                                    <SelectItem v-for="t in JOB_TYPES" :key="t" :value="t">{{ t }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.type ?? clientErrors.type" />
                        </div>
                    </div>

                    <!-- Periode -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label for="started_at">Mulai <span class="text-destructive">*</span></Label>
                            <Input
                                id="started_at"
                                v-model="form.started_at"
                                type="date"
                                :class="{ 'border-destructive': form.errors.started_at || clientErrors.started_at }"
                            />
                            <InputError :message="form.errors.started_at ?? clientErrors.started_at" />
                        </div>
                        <div class="space-y-1.5">
                            <Label for="ended_at">Selesai</Label>
                            <Input
                                id="ended_at"
                                v-model="form.ended_at"
                                type="date"
                                :disabled="form.is_current"
                                :class="{ 'border-destructive': form.errors.ended_at || clientErrors.ended_at }"
                            />
                            <p v-if="form.is_current" class="text-xs text-muted-foreground">
                                Dikosongkan otomatis saat masih aktif
                            </p>
                            <InputError :message="form.errors.ended_at ?? clientErrors.ended_at" />
                        </div>
                    </div>

                    <!-- Masih aktif -->
                    <div
                        class="flex items-center gap-3 rounded-lg border px-4 py-3 transition-colors"
                        :class="form.is_current ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/30' : ''"
                    >
                        <Switch
                            id="is_current"
                            :checked="form.is_current"
                            @update:checked="toggleCurrent"
                        />
                        <div>
                            <Label for="is_current" class="cursor-pointer text-sm font-medium">
                                Masih bekerja di sini
                            </Label>
                            <p v-if="form.is_current" class="text-xs text-emerald-600 dark:text-emerald-400">
                                Tanggal selesai tidak diperlukan
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                Tanggal selesai wajib diisi
                            </p>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="space-y-1.5">
                        <Label for="description">Deskripsi</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Jelaskan tanggung jawab dan pencapaian…"
                            :rows="4"
                            class="resize-y"
                            :class="{ 'border-destructive': form.errors.description || clientErrors.description }"
                        />
                        <p class="text-xs text-muted-foreground">Satu baris = satu poin deskripsi pada halaman portofolio</p>
                        <InputError :message="form.errors.description ?? clientErrors.description" />
                    </div>

                    <!-- Tech Stack -->
                    <div class="space-y-1.5">
                        <Label>Tech Stack</Label>
                        <div
                            class="flex min-h-9 flex-wrap items-center gap-1.5 rounded-md border bg-background px-2 py-1.5 focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2"
                            :class="{ 'border-destructive': (form.errors as any)['tech_stack'] || clientErrors.tech_stack }"
                        >
                            <span
                                v-for="(tag, i) in form.tech_stack"
                                :key="i"
                                class="flex items-center gap-1 rounded bg-secondary px-2 py-0.5 text-xs font-medium text-secondary-foreground"
                            >
                                {{ tag }}
                                <button type="button" class="text-muted-foreground hover:text-foreground" @click="removeTag(i)">
                                    <X class="h-3 w-3" />
                                </button>
                            </span>
                            <input
                                v-model="tagInput"
                                type="text"
                                placeholder="Ketik lalu Enter…"
                                class="min-w-20 flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                                @keydown="onTagKeydown"
                            />
                        </div>
                        <p class="text-xs text-muted-foreground">Tekan Enter atau koma untuk menambahkan. Backspace untuk menghapus terakhir.</p>
                        <InputError :message="(form.errors as any)['tech_stack'] ?? clientErrors.tech_stack" />
                    </div>

                </div>

                <SheetFooter class="border-t px-6 py-4">
                    <Button type="button" variant="outline" :disabled="form.processing" @click="close">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-1.5 h-4 w-4 animate-spin" />
                        {{ experience ? 'Simpan Perubahan' : 'Tambahkan' }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
