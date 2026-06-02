<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/data-table/DataTable.vue';
import { Button } from '@/components/ui/button';
import Heading from '@/components/common/Heading.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import type { BreadcrumbItem, DataTableResponse } from '@/types';
import { createColumns, type Permission } from './columns';
import PermissionSheet from './partials/PermissionSheet.vue';

const props = defineProps<{
    permissions: DataTableResponse<Permission>;
    groups: string[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Permissions', href: route('auth.permissions.index') },
];

// ── Sheet state ───────────────────────────────────────────────────────────────
const sheetOpen = ref(false);
const selected = ref<Permission | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    sheetOpen.value = true;
}

function openEdit(row: Permission) {
    selected.value = row;
    sheetOpen.value = true;
}

async function handleDelete(row: Permission) {
    const confirmed = await openDialog({
        title: 'Hapus Permission',
        description: `Permission "${row.display_name ?? row.name}" akan dihapus secara permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!confirmed) return;
    router.delete(route('auth.permissions.destroy', row.id), { preserveScroll: true });
}

// ── Columns ───────────────────────────────────────────────────────────────────
const columns = createColumns({ onEdit: openEdit, onDelete: handleDelete });

function handleServerRequest(params: any) {
    router.get(route('auth.permissions.index'), params, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl">
            <!-- Header -->
            <Heading title="Permissions" description="Kelola permission untuk mengatur hak akses sistem">
                <Button size="sm" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Permission
                </Button>
            </Heading>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="permissions.data"
                :meta="permissions.meta"
                search-placeholder="Cari permission..."
                @server-request="handleServerRequest"
            />
        </div>

        <!-- Permission Sheet (Create / Edit) -->
        <PermissionSheet
            v-model:open="sheetOpen"
            :permission="selected"
            :groups="groups"
        />

        <ConfirmDialog />
    </AppLayout>
</template>
