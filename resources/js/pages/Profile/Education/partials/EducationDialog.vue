<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
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
import type { Education } from '../Index.vue';

const props = defineProps<{
    open: boolean;
    education?: Education | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    institution: '',
    degree:      '',
    field:       '',
    started_at:  '',
    ended_at:    '',
    is_current:  false,
    description: '',
    order:       0,
});

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.education) {
            form.institution = props.education.institution;
            form.degree      = props.education.degree;
            form.field       = props.education.field ?? '';
            form.started_at  = props.education.started_at ?? '';
            form.ended_at    = props.education.ended_at ?? '';
            form.is_current  = props.education.is_current;
            form.description = props.education.description ?? '';
            form.order       = props.education.order;
        } else {
            form.reset();
        }
    },
);

function close() {
    emit('update:open', false);
}

function submit() {
    if (props.education) {
        form.put(route('profile.educations.update', props.education.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('profile.educations.store'), {
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
                <SheetTitle>{{ education ? 'Edit Pendidikan' : 'Tambah Pendidikan' }}</SheetTitle>
                <SheetDescription>
                    {{ education ? 'Ubah detail pendidikan' : 'Isi detail pendidikan baru' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                <div class="space-y-4 px-6 py-4">

                    <!-- Institution -->
                    <div class="grid gap-1.5">
                        <Label for="institution">Institusi <span class="text-destructive">*</span></Label>
                        <Input
                            id="institution"
                            v-model="form.institution"
                            placeholder="e.g. Universitas Indonesia"
                            :class="{ 'border-destructive': form.errors.institution }"
                            autofocus
                        />
                        <p class="min-h-4 text-xs text-destructive">{{ form.errors.institution }}</p>
                    </div>

                    <!-- Degree & Field -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="degree">Gelar <span class="text-destructive">*</span></Label>
                            <Input
                                id="degree"
                                v-model="form.degree"
                                placeholder="e.g. S1 / Bachelor"
                                :class="{ 'border-destructive': form.errors.degree }"
                            />
                            <p class="min-h-4 text-xs text-destructive">{{ form.errors.degree }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="field">Bidang Studi</Label>
                            <Input
                                id="field"
                                v-model="form.field"
                                placeholder="e.g. Teknik Informatika"
                            />
                            <p class="min-h-4 text-xs text-destructive">{{ form.errors.field }}</p>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="started_at">Mulai <span class="text-destructive">*</span></Label>
                            <Input
                                id="started_at"
                                v-model="form.started_at"
                                type="date"
                                :class="{ 'border-destructive': form.errors.started_at }"
                            />
                            <p class="min-h-4 text-xs text-destructive">{{ form.errors.started_at }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="ended_at">Selesai</Label>
                            <Input
                                id="ended_at"
                                v-model="form.ended_at"
                                type="date"
                                :disabled="form.is_current"
                                :class="{ 'border-destructive': form.errors.ended_at }"
                            />
                            <p class="min-h-4 text-xs text-destructive">{{ form.errors.ended_at }}</p>
                        </div>
                    </div>

                    <!-- Is Current -->
                    <div class="flex items-center gap-3 rounded-lg border p-3">
                        <Switch
                            id="is_current"
                            :checked="form.is_current"
                            @update:checked="(v: boolean) => { form.is_current = v; if (v) form.ended_at = ''; }"
                        />
                        <Label for="is_current" class="cursor-pointer text-sm">
                            Masih berkuliah / dalam proses
                        </Label>
                    </div>

                    <!-- Description -->
                    <div class="grid gap-1.5">
                        <Label for="description">Deskripsi</Label>
                        <Textarea
                            id="description"
                            v-model="form.description"
                            placeholder="Aktivitas, pencapaian, atau catatan tambahan..."
                            rows="3"
                        />
                        <p class="min-h-4 text-xs text-destructive">{{ form.errors.description }}</p>
                    </div>

                    <!-- Order -->
                    <div class="grid gap-1.5">
                        <Label for="order">Urutan</Label>
                        <Input
                            id="order"
                            v-model.number="form.order"
                            type="number"
                            min="0"
                            placeholder="0"
                        />
                        <p class="min-h-4 text-xs text-destructive">{{ form.errors.order }}</p>
                    </div>
                </div>

                <SheetFooter class="border-t px-6 py-4">
                    <Button type="button" variant="outline" @click="close">Batal</Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : education ? 'Simpan Perubahan' : 'Tambahkan' }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
