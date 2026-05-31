<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Briefcase, Pencil, Plus, Trash2, Zap } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/common/Heading.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import type { BreadcrumbItem } from '@/types';
import ServiceDialog from './partials/ServiceDialog.vue';

export interface Service {
    id: number;
    profile_id: number;
    title: string;
    slug: string;
    description: string;
    icon: string | null;
    price_from: number | null;
    is_active: boolean;
    order: number;
}

const props = defineProps<{
    services: Service[];
    hasProfile: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Layanan', href: route('profile.services.index') },
];

const dialogOpen = ref(false);
const selected   = ref<Service | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    dialogOpen.value = true;
}

function openEdit(item: Service) {
    selected.value = item;
    dialogOpen.value = true;
}

async function handleDelete(item: Service) {
    const ok = await openDialog({
        title: 'Hapus Layanan',
        description: `Layanan "${item.title}" akan dihapus permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(route('profile.services.destroy', item.id), { preserveScroll: true });
}

function toggleActive(item: Service) {
    router.patch(route('profile.services.toggle', item.id), {}, { preserveScroll: true });
}

function formatPrice(price: number | null): string {
    if (price === null) return '';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(price);
}

const activeCount = computed(() => props.services.filter(s => s.is_active).length);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4">

            <!-- Header -->
            <Heading title="Layanan" description="Daftar layanan profesional yang Anda tawarkan">
                <Button size="sm" :disabled="!hasProfile" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah
                </Button>
            </Heading>

            <!-- No profile warning -->
            <div
                v-if="!hasProfile"
                class="rounded-md border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950 dark:text-yellow-300"
            >
                Lengkapi <a :href="route('profile.show')" class="font-medium underline">profil Anda</a> terlebih dahulu sebelum menambahkan layanan.
            </div>

            <!-- Stats bar -->
            <div v-else-if="services.length > 0" class="flex items-center gap-4 text-sm text-muted-foreground">
                <span>{{ services.length }} layanan</span>
                <span class="h-3.5 w-px bg-border" />
                <span class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500" />
                    {{ activeCount }} aktif
                </span>
                <span v-if="services.length - activeCount > 0" class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-muted-foreground/40" />
                    {{ services.length - activeCount }} nonaktif
                </span>
            </div>

            <!-- Empty state -->
            <div
                v-if="hasProfile && services.length === 0"
                class="flex flex-1 flex-col items-center justify-center gap-3 rounded-md border border-dashed py-16 text-center"
            >
                <Briefcase class="h-10 w-10 text-muted-foreground/40" />
                <p class="text-sm text-muted-foreground">Belum ada layanan ditambahkan.</p>
                <Button size="sm" variant="outline" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Layanan
                </Button>
            </div>

            <!-- Service list -->
            <div v-else-if="services.length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="service in services"
                    :key="service.id"
                    class="group relative rounded-md border bg-card text-card-foreground shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                    :class="!service.is_active ? 'opacity-60' : ''"
                >
                    <div class="p-4">
                        <!-- Icon + Title row -->
                        <div class="mb-2.5 flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10 text-primary">
                                <Zap v-if="!service.icon" class="h-4 w-4" />
                                <img
                                    v-else
                                    :src="`https://api.iconify.design/${service.icon.startsWith('i-') ? service.icon.slice(2).replace(/-(.+)/, ':$1') : service.icon}.svg`"
                                    :alt="service.title"
                                    class="h-5 w-5"
                                    @error="($event.target as HTMLImageElement).style.display = 'none'"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-semibold leading-tight">{{ service.title }}</p>
                                <p class="mt-0.5 font-mono text-xs text-muted-foreground">{{ service.slug }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="line-clamp-2 text-sm text-muted-foreground">{{ service.description }}</p>

                        <!-- Price + active -->
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <Badge v-if="service.price_from !== null" variant="secondary" class="font-mono text-xs">
                                Mulai {{ formatPrice(service.price_from) }}
                            </Badge>
                            <span v-else class="text-xs text-muted-foreground italic">Harga tidak ditampilkan</span>

                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-muted-foreground">{{ service.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                <Switch
                                    :checked="service.is_active"
                                    class="scale-75"
                                    @update:checked="toggleActive(service)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-1 border-t px-3 py-2 opacity-0 transition-all duration-150 group-hover:opacity-100">
                        <Button size="icon" variant="ghost" class="h-7 w-7 rounded-md" @click="openEdit(service)">
                            <Pencil class="h-3.5 w-3.5" />
                        </Button>
                        <Button size="icon" variant="ghost" class="h-7 w-7 rounded-md text-destructive hover:bg-destructive/10 hover:text-destructive" @click="handleDelete(service)">
                            <Trash2 class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <ServiceDialog
            v-model:open="dialogOpen"
            :service="selected"
        />
        <ConfirmDialog />
    </AppLayout>
</template>
