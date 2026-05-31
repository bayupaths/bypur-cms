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
import { certificateSchema } from '@/schemas/certificateSchema';
import type { Certificate } from '../Index.vue';

const props = defineProps<{
    open: boolean;
    certificate?: Certificate | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    title:          '',
    issuer:         '',
    issued_at:      '',
    expired_at:     '',
    credential_url: '',
    image:          '',
});

const clientErrors = ref<Partial<Record<string, string>>>({});
const imageError   = ref(false);

watch(
    () => props.open,
    (val) => {
        if (!val) return;
        if (props.certificate) {
            form.title          = props.certificate.title;
            form.issuer         = props.certificate.issuer;
            form.issued_at      = props.certificate.issued_at ?? '';
            form.expired_at     = props.certificate.expired_at ?? '';
            form.credential_url = props.certificate.credential_url ?? '';
            form.image          = props.certificate.image ?? '';
        } else {
            form.reset();
        }
        imageError.value = false;
    },
);

watch(() => form.image, () => { imageError.value = false; });

function close() {
    clientErrors.value = {};
    emit('update:open', false);
}

function submit() {
    clientErrors.value = {};

    const result = certificateSchema.safeParse(form.data());
    if (!result.success) {
        const flat = result.error.flatten().fieldErrors;
        clientErrors.value = Object.fromEntries(
            Object.entries(flat).map(([k, v]) => [k, v?.[0] ?? '']),
        );
        return;
    }

    if (props.certificate) {
        form.put(route('profile.certificates.update', props.certificate.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    } else {
        form.post(route('profile.certificates.store'), {
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
                <SheetTitle>{{ certificate ? 'Edit Sertifikat' : 'Tambah Sertifikat' }}</SheetTitle>
                <SheetDescription>
                    {{ certificate ? 'Ubah detail sertifikat' : 'Isi detail sertifikat baru' }}
                </SheetDescription>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-y-auto" @submit.prevent="submit">
                <div class="space-y-5 px-6 py-5">

                    <!-- Judul & Penerbit -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="title">Judul <span class="text-destructive">*</span></Label>
                            <Input
                                id="title"
                                v-model="form.title"
                                placeholder="AWS Solutions Architect"
                                :class="{ 'border-destructive': form.errors.title || clientErrors.title }"
                                autofocus
                            />
                            <InputError :message="form.errors.title ?? clientErrors.title" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="issuer">Penerbit <span class="text-destructive">*</span></Label>
                            <Input
                                id="issuer"
                                v-model="form.issuer"
                                placeholder="Amazon Web Services"
                                :class="{ 'border-destructive': form.errors.issuer || clientErrors.issuer }"
                            />
                            <InputError :message="form.errors.issuer ?? clientErrors.issuer" />
                        </div>
                    </div>

                    <!-- Tanggal Terbit & Kadaluarsa -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="grid gap-1.5">
                            <Label for="issued_at">Tanggal Terbit <span class="text-destructive">*</span></Label>
                            <Input
                                id="issued_at"
                                v-model="form.issued_at"
                                type="date"
                                :class="{ 'border-destructive': form.errors.issued_at || clientErrors.issued_at }"
                            />
                            <InputError :message="form.errors.issued_at ?? clientErrors.issued_at" />
                        </div>
                        <div class="grid gap-1.5">
                            <Label for="expired_at">
                                Kadaluarsa
                                <span class="font-normal text-muted-foreground">(opsional)</span>
                            </Label>
                            <Input
                                id="expired_at"
                                v-model="form.expired_at"
                                type="date"
                                :class="{ 'border-destructive': form.errors.expired_at || clientErrors.expired_at }"
                            />
                            <p class="text-[11px] text-muted-foreground">Kosongkan jika tidak kadaluarsa</p>
                            <InputError :message="form.errors.expired_at ?? clientErrors.expired_at" />
                        </div>
                    </div>

                    <!-- URL Kredensial -->
                    <div class="grid gap-1.5">
                        <Label for="credential_url">URL Kredensial</Label>
                        <Input
                            id="credential_url"
                            v-model="form.credential_url"
                            type="url"
                            placeholder="https://www.credly.com/badges/..."
                            :class="{ 'border-destructive': form.errors.credential_url || clientErrors.credential_url }"
                        />
                        <InputError :message="form.errors.credential_url ?? clientErrors.credential_url" />
                    </div>

                    <!-- URL Gambar Badge -->
                    <div class="grid gap-1.5">
                        <Label for="image">URL Gambar Badge</Label>
                        <div class="flex items-center gap-2">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-md border bg-muted">
                                <img
                                    v-if="form.image && !imageError"
                                    :src="form.image"
                                    alt="Badge preview"
                                    class="h-8 w-8 object-contain"
                                    @error="imageError = true"
                                />
                                <span v-else class="text-xs text-muted-foreground">—</span>
                            </span>
                            <Input
                                id="image"
                                v-model="form.image"
                                placeholder="https://images.credly.com/..."
                                class="flex-1"
                                :class="{ 'border-destructive': form.errors.image || clientErrors.image }"
                            />
                        </div>
                        <InputError :message="form.errors.image ?? clientErrors.image" />
                    </div>

                </div>

                <SheetFooter class="border-t px-6 py-4">
                    <Button type="button" variant="outline" :disabled="form.processing" @click="close">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-1.5 h-4 w-4 animate-spin" />
                        {{ certificate ? 'Simpan Perubahan' : 'Tambahkan' }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
