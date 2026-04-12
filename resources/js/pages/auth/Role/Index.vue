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
import type { BreadcrumbItem, DataTableResponse, Permission } from '@/types';
import { createColumns, type Role } from './columns';
import RoleSheet from './partials/RoleSheet.vue';
import PermissionSyncSheet from './partials/PermissionSyncSheet.vue';

type PermissionsGrouped = Record<string, Permission[]>;

const props = defineProps<{
    roles: DataTableResponse<Role>;
    permissions_grouped: PermissionsGrouped;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Roles', href: route('auth.roles.index') },
];

// ── Sheet state ───────────────────────────────────────────────────────────────
const roleSheetOpen = ref(false);
const syncSheetOpen = ref(false);
const selected = ref<Role | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    roleSheetOpen.value = true;
}

function openEdit(row: Role) {
    selected.value = row;
    roleSheetOpen.value = true;
}

function openSyncPermissions(row: Role) {
    selected.value = row;
    syncSheetOpen.value = true;
}

async function handleDelete(row: Role) {
    const confirmed = await openDialog({
        title: 'Hapus Role',
        description: `Role "${row.display_name ?? row.name}" akan dihapus secara permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!confirmed) return;
    router.delete(route('auth.roles.destroy', row.id), { preserveScroll: true });
}

// ── Columns ───────────────────────────────────────────────────────────────────
const columns = createColumns({
    onEdit: openEdit,
    onSyncPermissions: openSyncPermissions,
    onDelete: handleDelete,
});

function handleServerRequest(params: any) {
    router.get(route('auth.roles.index'), params, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl">
            <!-- Header -->
            <Heading title="Roles" description="Kelola role dan hak akses pengguna">
                <Button size="sm" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Role
                </Button>
            </Heading>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="roles.data"
                :meta="roles.meta"
                search-placeholder="Cari role..."
                @server-request="handleServerRequest"
            />
        </div>

        <!-- Role Sheet (Create / Edit) -->
        <RoleSheet v-model:open="roleSheetOpen" :role="selected" />

        <!-- Permission Sync Sheet -->
        <PermissionSyncSheet
            v-model:open="syncSheetOpen"
            :role="selected"
            :permissions-grouped="permissions_grouped"
        />

        <ConfirmDialog />
    </AppLayout>
</template>
