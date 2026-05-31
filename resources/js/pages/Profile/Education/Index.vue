<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    BookOpen, Building2, CalendarDays, GraduationCap, Pencil,
    Plus, Trash2,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/common/Heading.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import type { BreadcrumbItem } from '@/types';
import EducationDialog from './partials/EducationDialog.vue';

export interface Education {
    id: number;
    profile_id: number;
    institution: string;
    degree: string;
    field: string | null;
    started_at: string | null;
    ended_at: string | null;
    is_current: boolean;
    description: string | null;
    order: number;
}

const props = defineProps<{
    educations: Education[];
    hasProfile: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pendidikan', href: route('profile.educations.index') },
];

const dialogOpen = ref(false);
const selected   = ref<Education | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    dialogOpen.value = true;
}

function openEdit(item: Education) {
    selected.value = item;
    dialogOpen.value = true;
}

async function handleDelete(item: Education) {
    const ok = await openDialog({
        title: 'Hapus Pendidikan',
        description: `Data pendidikan "${item.degree} – ${item.institution}" akan dihapus permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(route('profile.educations.destroy', item.id), { preserveScroll: true });
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
}

function formatPeriod(edu: Education): string {
    const start = formatDate(edu.started_at);
    if (edu.is_current) return `${start} – Sekarang`;
    const end = formatDate(edu.ended_at);
    return end ? `${start} – ${end}` : start;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4">

            <!-- Header -->
            <Heading title="Pendidikan" description="Riwayat pendidikan Anda">
                <Button size="sm" :disabled="!hasProfile" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah
                </Button>
            </Heading>

            <!-- No profile warning -->
            <div v-if="!hasProfile" class="rounded-md border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-300">
                Lengkapi <a :href="route('profile.show')" class="font-medium underline">profil Anda</a> terlebih dahulu sebelum menambahkan pendidikan.
            </div>

            <!-- Empty state -->
            <div
                v-else-if="educations.length === 0"
                class="flex flex-1 flex-col items-center justify-center gap-3 rounded-md border border-dashed py-16 text-center"
            >
                <GraduationCap class="h-10 w-10 text-muted-foreground/40" />
                <p class="text-sm text-muted-foreground">Belum ada data pendidikan ditambahkan.</p>
                <Button size="sm" variant="outline" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Pendidikan
                </Button>
            </div>

            <!-- Timeline list -->
            <div v-else class="flex flex-col">
                <div
                    v-for="(edu, idx) in educations"
                    :key="edu.id"
                    class="group relative flex gap-4"
                >
                    <!-- Timeline dot + line -->
                    <div class="relative flex flex-col items-center">
                        <div
                            class="relative z-10 mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 bg-background shadow-sm transition-all duration-200"
                            :class="edu.is_current ? 'border-primary shadow-primary/20' : 'border-border'"
                        >
                            <GraduationCap
                                class="h-4 w-4 transition-colors duration-200"
                                :class="edu.is_current ? 'text-primary' : 'text-muted-foreground'"
                            />
                        </div>
                        <div
                            v-if="idx < educations.length - 1"
                            class="w-px flex-1 bg-linear-to-b from-border to-border/30"
                            style="min-height: 1.5rem;"
                        />
                    </div>

                    <!-- Card -->
                    <div
                        class="mb-4 flex-1 overflow-hidden rounded-md border bg-card text-card-foreground shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                        :class="edu.is_current ? 'border-primary/20' : ''"
                    >
                        <!-- Active accent bar -->
                        <div
                            v-if="edu.is_current"
                            class="h-0.5 w-full bg-linear-to-r from-primary/60 to-primary/20"
                        />

                        <div class="p-4">
                            <!-- Top row -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <p class="font-semibold leading-tight tracking-tight">
                                        {{ edu.degree }}<span v-if="edu.field" class="font-normal text-muted-foreground"> — {{ edu.field }}</span>
                                    </p>
                                    <div class="flex flex-wrap items-center gap-1.5 text-sm text-muted-foreground">
                                        <span class="flex items-center gap-1">
                                            <Building2 class="h-3.5 w-3.5 shrink-0" />
                                            {{ edu.institution }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Actions -->
                                <div class="flex shrink-0 gap-1 opacity-0 transition-all duration-150 group-hover:opacity-100">
                                    <Button size="icon" variant="ghost" class="h-7 w-7 rounded-lg" @click="openEdit(edu)">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="h-7 w-7 rounded-lg text-destructive hover:bg-destructive/10 hover:text-destructive" @click="handleDelete(edu)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Badges -->
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                <Badge class="gap-1 text-xs" :variant="edu.is_current ? 'default' : 'secondary'">
                                    <CalendarDays class="h-3 w-3" />
                                    {{ formatPeriod(edu) }}
                                </Badge>
                                <Badge v-if="edu.is_current" class="gap-1 bg-emerald-500/10 text-xs text-emerald-600 dark:text-emerald-400" variant="outline">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500" />
                                    Aktif
                                </Badge>
                            </div>

                            <!-- Description -->
                            <template v-if="edu.description">
                                <Separator class="my-3" />
                                <p class="flex items-start gap-1.5 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">
                                    <BookOpen class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                    {{ edu.description }}
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <EducationDialog
            v-model:open="dialogOpen"
            :education="selected"
        />
        <ConfirmDialog />
    </AppLayout>
</template>
