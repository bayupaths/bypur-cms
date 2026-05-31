<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Briefcase, Building2, CalendarDays, GripVertical, MapPin, Pencil,
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
import ExperienceDialog from './partials/ExperienceDialog.vue';

export interface Experience {
    id: number;
    profile_id: number;
    company: string;
    position: string;
    location: string | null;
    type: string | null;
    started_at: string | null;
    ended_at: string | null;
    is_current: boolean;
    description: string | null;
    tech_stack: string[] | null;
    order: number;
}

const props = defineProps<{
    experiences: Experience[];
    hasProfile: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Pengalaman', href: route('profile.experiences.index') },
];

const dialogOpen  = ref(false);
const selected    = ref<Experience | null>(null);
const reorderMode = ref(false);
const draggingId  = ref<number | null>(null);
const localList   = ref<Experience[]>([...props.experiences]);
const { openDialog } = useConfirmDialog();

// Keep localList in sync when Inertia refreshes props after store/update/delete
watch(
    () => props.experiences,
    (val) => {
        localList.value = [...val];
        reorderMode.value = false;
    },
);

function openCreate() {
    selected.value = null;
    dialogOpen.value = true;
}

function openEdit(item: Experience) {
    selected.value = item;
    dialogOpen.value = true;
}

async function handleDelete(item: Experience) {
    const ok = await openDialog({
        title: 'Hapus Pengalaman',
        description: `Pengalaman "${item.position} di ${item.company}" akan dihapus permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(route('profile.experiences.destroy', item.id), { preserveScroll: true });
}

// ── Drag-to-reorder ───────────────────────────────────────────────
function onDragStart(id: number) { draggingId.value = id; }
function onDragEnd() { draggingId.value = null; }

function onDragOver(e: DragEvent, targetId: number) {
    e.preventDefault();
    if (draggingId.value === null || draggingId.value === targetId) return;
    const from = localList.value.findIndex((x) => x.id === draggingId.value);
    const to   = localList.value.findIndex((x) => x.id === targetId);
    if (from === -1 || to === -1) return;
    const copy = [...localList.value];
    copy.splice(to, 0, copy.splice(from, 1)[0]);
    localList.value = copy;
}

function saveReorder() {
    router.post(
        route('profile.experiences.reorder'),
        { ids: localList.value.map((x) => x.id) },
        { preserveScroll: true, onSuccess: () => { reorderMode.value = false; } },
    );
}

// ── Formatting ────────────────────────────────────────────────────
function formatDate(dateStr: string | null): string {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
}

function formatPeriod(exp: Experience): string {
    const start = formatDate(exp.started_at);
    if (exp.is_current) return `${start} – Sekarang`;
    const end = formatDate(exp.ended_at);
    return end ? `${start} – ${end}` : start;
}

function calcDuration(exp: Experience): string {
    const start = exp.started_at ? new Date(exp.started_at) : null;
    const end   = exp.is_current ? new Date() : (exp.ended_at ? new Date(exp.ended_at) : null);
    if (!start || !end) return '';
    const totalMonths = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
    if (totalMonths <= 0) return '';
    const years  = Math.floor(totalMonths / 12);
    const months = totalMonths % 12;
    const parts  = [];
    if (years)  parts.push(`${years} thn`);
    if (months) parts.push(`${months} bln`);
    return parts.join(' ');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4">

            <!-- Header -->
            <Heading title="Pengalaman Kerja" description="Riwayat pengalaman profesional Anda">
                <div class="flex items-center gap-2">
                    <template v-if="experiences.length > 1">
                        <template v-if="reorderMode">
                            <Button size="sm" variant="outline" @click="reorderMode = false; localList = [...experiences]">
                                Batal
                            </Button>
                            <Button size="sm" @click="saveReorder">
                                Simpan Urutan
                            </Button>
                        </template>
                        <Button v-else size="sm" variant="outline" @click="reorderMode = true">
                            <GripVertical class="mr-1.5 h-4 w-4" />
                            Atur Urutan
                        </Button>
                    </template>
                    <Button size="sm" :disabled="!hasProfile" @click="openCreate">
                        <Plus class="mr-1.5 h-4 w-4" />
                        Tambah
                    </Button>
                </div>
            </Heading>

            <!-- No profile warning -->
            <div v-if="!hasProfile" class="rounded-md border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-300">
                Lengkapi <a :href="route('profile.show')" class="font-medium underline">profil Anda</a> terlebih dahulu sebelum menambahkan pengalaman.
            </div>

            <!-- Empty state -->
            <div
                v-else-if="experiences.length === 0"
                class="flex flex-1 flex-col items-center justify-center gap-3 rounded-md border border-dashed py-16 text-center"
            >
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-muted">
                    <Briefcase class="h-6 w-6 text-muted-foreground" />
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-medium">Belum ada pengalaman kerja</p>
                    <p class="text-xs text-muted-foreground">Tambahkan riwayat pengalaman profesional Anda</p>
                </div>
                <Button size="sm" variant="outline" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Pengalaman
                </Button>
            </div>

            <!-- Timeline / Reorder list -->
            <div v-else class="flex flex-col">
                <div
                    v-for="(exp, idx) in localList"
                    :key="exp.id"
                    class="group relative flex gap-4"
                    :draggable="reorderMode"
                    @dragstart="onDragStart(exp.id)"
                    @dragend="onDragEnd"
                    @dragover="onDragOver($event, exp.id)"
                >
                    <!-- Timeline dot / Drag handle -->
                    <div class="relative flex flex-col items-center">
                        <div
                            class="relative z-10 mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 bg-background shadow-sm transition-all duration-200"
                            :class="[
                                reorderMode ? 'cursor-grab border-dashed border-muted-foreground/50' : '',
                                !reorderMode && exp.is_current ? 'border-primary shadow-primary/20' : 'border-border',
                            ]"
                        >
                            <GripVertical v-if="reorderMode" class="h-4 w-4 text-muted-foreground" />
                            <Briefcase
                                v-else
                                class="h-4 w-4 transition-colors duration-200"
                                :class="exp.is_current ? 'text-primary' : 'text-muted-foreground'"
                            />
                        </div>
                        <div
                            v-if="idx < localList.length - 1"
                            class="w-px flex-1 bg-linear-to-b from-border to-border/30"
                            style="min-height: 1.5rem;"
                        />
                    </div>

                    <!-- Card -->
                    <div
                        class="mb-4 flex-1 overflow-hidden rounded-md border bg-card text-card-foreground shadow-xs transition-all duration-200"
                        :class="[
                            reorderMode ? 'opacity-90 ring-1 ring-dashed ring-muted-foreground/30' : 'hover:-translate-y-0.5 hover:shadow-md',
                            !reorderMode && exp.is_current ? 'border-primary/20' : '',
                            draggingId === exp.id ? 'opacity-50 scale-[0.98]' : '',
                        ]"
                    >
                        <!-- Active accent bar -->
                        <div
                            v-if="exp.is_current && !reorderMode"
                            class="h-0.5 w-full bg-linear-to-r from-primary/60 to-primary/20"
                        />

                        <div class="p-4">
                            <!-- Top row -->
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <p class="font-semibold leading-tight tracking-tight">{{ exp.position }}</p>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-muted-foreground">
                                        <span class="flex items-center gap-1">
                                            <Building2 class="h-3.5 w-3.5 shrink-0" />
                                            {{ exp.company }}
                                        </span>
                                        <span v-if="exp.location" class="flex items-center gap-1">
                                            <MapPin class="h-3 w-3 shrink-0" />
                                            {{ exp.location }}
                                        </span>
                                    </div>
                                </div>
                                <!-- Actions -->
                                <div v-if="!reorderMode" class="flex shrink-0 gap-1 opacity-0 transition-all duration-150 group-hover:opacity-100">
                                    <Button size="icon" variant="ghost" class="h-7 w-7 rounded-lg" @click="openEdit(exp)">
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button size="icon" variant="ghost" class="h-7 w-7 rounded-lg text-destructive hover:bg-destructive/10 hover:text-destructive" @click="handleDelete(exp)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>

                            <!-- Badges -->
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                <Badge class="gap-1 text-xs" :variant="exp.is_current ? 'default' : 'secondary'">
                                    <CalendarDays class="h-3 w-3" />
                                    {{ formatPeriod(exp) }}
                                </Badge>
                                <Badge v-if="calcDuration(exp)" variant="outline" class="text-xs text-muted-foreground">
                                    {{ calcDuration(exp) }}
                                </Badge>
                                <Badge v-if="exp.type" variant="outline" class="text-xs">
                                    {{ exp.type }}
                                </Badge>
                                <Badge v-if="exp.is_current" class="gap-1 bg-emerald-500/10 text-xs text-emerald-600 dark:text-emerald-400" variant="outline">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500" />
                                    Aktif
                                </Badge>
                            </div>

                            <!-- Tech Stack -->
                            <div v-if="exp.tech_stack?.length && !reorderMode" class="mt-2.5 flex flex-wrap gap-1">
                                <span
                                    v-for="tag in exp.tech_stack"
                                    :key="tag"
                                    class="rounded bg-secondary px-1.5 py-0.5 text-xs font-medium text-secondary-foreground"
                                >
                                    {{ tag }}
                                </span>
                            </div>

                            <!-- Description -->
                            <template v-if="exp.description && !reorderMode">
                                <Separator class="my-3" />
                                <ul class="space-y-1">
                                    <li
                                        v-for="(line, i) in exp.description.split('\n').filter(Boolean)"
                                        :key="i"
                                        class="flex items-start gap-1.5 text-sm leading-relaxed text-muted-foreground"
                                    >
                                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-muted-foreground/50" />
                                        {{ line.trim() }}
                                    </li>
                                </ul>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ExperienceDialog
            v-model:open="dialogOpen"
            :experience="selected"
        />
        <ConfirmDialog />
    </AppLayout>
</template>
