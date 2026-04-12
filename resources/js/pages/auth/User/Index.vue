<script setup lang="ts">
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable from '@/components/data-table/DataTable.vue';
import { Button } from '@/components/ui/button';
import Heading from '@/components/common/Heading.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import type { BreadcrumbItem, DataTableResponse } from '@/types';
import { createColumns, type User } from './columns';
import UserSheet from './partials/UserSheet.vue';

interface RoleItem {
    id: number;
    name: string;
    display_name: string | null;
}

const props = defineProps<{
    users: DataTableResponse<User>;
    roles: RoleItem[];
    filters: { search?: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Users', href: route('auth.users.index') },
];

// ── Sheet state ───────────────────────────────────────────────────────────────
const page = usePage();
const sheetOpen = ref(false);
const selected = ref<User | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    sheetOpen.value = true;
}

function openEdit(row: User) {
    selected.value = row;
    sheetOpen.value = true;
}

function openView(row: User) {
    router.visit(route('auth.users.show', row.id));
}

async function handleDelete(row: User) {
    if ((page.props.auth as any).user?.id === row.id) return;
    const confirmed = await openDialog({
        title: 'Hapus User',
        description: `User "${row.name}" akan dihapus secara permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!confirmed) return;
    router.delete(route('auth.users.destroy', row.id), { preserveScroll: true });
}

// ── Columns ───────────────────────────────────────────────────────────────────
const columns = createColumns({ onView: openView, onEdit: openEdit, onDelete: handleDelete });

function handleServerRequest(params: any) {
    router.get(route('auth.users.index'), params, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl">
            <!-- Header -->
            <Heading title="Users" description="Kelola akun pengguna sistem">
                <Button size="sm" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah User
                </Button>
            </Heading>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="users.data"
                :meta="users.meta"
                search-placeholder="Cari nama atau email..."
                @server-request="handleServerRequest"
            />
        </div>

        <!-- Sheet Form -->
        <UserSheet
            v-model:open="sheetOpen"
            :user="selected"
            :roles="roles"
        />

        <ConfirmDialog />
    </AppLayout>
</template>
