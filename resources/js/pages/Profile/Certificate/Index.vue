<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Award, CalendarDays, ExternalLink, Pencil, Plus, Trash2,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/common/Heading.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import type { BreadcrumbItem } from '@/types';
import CertificateDialog from './partials/CertificateDialog.vue';

export interface Certificate {
    id: number;
    profile_id: number;
    title: string;
    issuer: string;
    issued_at: string | null;
    expired_at: string | null;
    credential_url: string | null;
    image: string | null;
    order: number;
}

const props = defineProps<{
    certificates: Certificate[];
    hasProfile: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Sertifikat', href: route('profile.certificates.index') },
];

const dialogOpen = ref(false);
const selected   = ref<Certificate | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    dialogOpen.value = true;
}

function openEdit(item: Certificate) {
    selected.value = item;
    dialogOpen.value = true;
}

async function handleDelete(item: Certificate) {
    const ok = await openDialog({
        title: 'Hapus Sertifikat',
        description: `Sertifikat "${item.title}" akan dihapus permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(route('profile.certificates.destroy', item.id), { preserveScroll: true });
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
}

function isExpired(cert: Certificate): boolean {
    if (!cert.expired_at) return false;
    return new Date(cert.expired_at) < new Date();
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4">

            <!-- Header -->
            <Heading title="Sertifikat" description="Daftar sertifikasi profesional Anda">
                <Button size="sm" :disabled="!hasProfile" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah
                </Button>
            </Heading>

            <!-- No profile warning -->
            <div v-if="!hasProfile" class="rounded-md border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-300">
                Lengkapi <a :href="route('profile.show')" class="font-medium underline">profil Anda</a> terlebih dahulu sebelum menambahkan sertifikat.
            </div>

            <!-- Empty state -->
            <div
                v-else-if="certificates.length === 0"
                class="flex flex-1 flex-col items-center justify-center gap-3 rounded-md border border-dashed py-16 text-center"
            >
                <Award class="h-10 w-10 text-muted-foreground/40" />
                <p class="text-sm text-muted-foreground">Belum ada sertifikat ditambahkan.</p>
                <Button size="sm" variant="outline" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Sertifikat
                </Button>
            </div>

            <!-- Certificate grid -->
            <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="cert in certificates"
                    :key="cert.id"
                    class="group relative overflow-hidden rounded-md border bg-card text-card-foreground shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <!-- Badge image -->
                    <div
                        v-if="cert.image"
                        class="flex h-32 items-center justify-center overflow-hidden border-b bg-muted"
                    >
                        <img
                            :src="cert.image"
                            :alt="cert.title"
                            class="h-full w-full object-contain p-4 transition-transform duration-300 group-hover:scale-[1.03]"
                        />
                    </div>
                    <!-- Placeholder when no image -->
                    <div
                        v-else
                        class="flex h-24 items-center justify-center border-b bg-gradient-to-br from-muted to-muted/60"
                    >
                        <Award class="h-10 w-10 text-muted-foreground/30 transition-transform duration-300 group-hover:scale-110" />
                    </div>

                    <!-- Card content -->
                    <div class="p-4">
                        <p class="font-semibold leading-tight tracking-tight">{{ cert.title }}</p>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ cert.issuer }}</p>

                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                            <Badge variant="secondary" class="gap-1 text-xs">
                                <CalendarDays class="h-3 w-3" />
                                {{ formatDate(cert.issued_at) }}
                            </Badge>
                            <Badge v-if="cert.expired_at" :variant="isExpired(cert) ? 'destructive' : 'outline'" class="text-xs">
                                {{ isExpired(cert) ? 'Kadaluarsa' : `s/d ${formatDate(cert.expired_at)}` }}
                            </Badge>
                            <Badge v-else variant="outline" class="gap-1 text-xs">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                Tidak Kadaluarsa
                            </Badge>
                        </div>
                    </div>

                    <Separator />

                    <div class="flex items-center justify-between px-4 py-2">
                        <a
                            v-if="cert.credential_url"
                            :href="cert.credential_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-1 text-xs text-primary transition-colors hover:underline"
                        >
                            <ExternalLink class="h-3.5 w-3.5" />
                            Lihat Kredensial
                        </a>
                        <span v-else class="text-xs text-muted-foreground">Tanpa URL</span>

                        <div class="flex gap-1 opacity-0 transition-all duration-150 group-hover:opacity-100">
                            <Button size="icon" variant="ghost" class="h-7 w-7 rounded-md" @click="openEdit(cert)">
                                <Pencil class="h-3.5 w-3.5" />
                            </Button>
                            <Button size="icon" variant="ghost" class="h-7 w-7 rounded-md text-destructive hover:bg-destructive/10 hover:text-destructive" @click="handleDelete(cert)">
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <CertificateDialog
            v-model:open="dialogOpen"
            :certificate="selected"
        />
        <ConfirmDialog />
    </AppLayout>
</template>
