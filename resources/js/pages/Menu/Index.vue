<script setup lang="ts">
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Plus } from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";
import DataTable from "@/components/data-table/DataTable.vue";
import { Button } from "@/components/ui/button";
import Heading from "@/components/common/Heading.vue";
import type { BreadcrumbItem, DataTableResponse } from "@/types";
import { createColumns, type Menu } from "./columns";
import MenuDialog from "./partials/MenuDialog.vue";
import ConfirmDialog from "@/components/dialogs/ConfirmDialog.vue";
import { useConfirmDialog } from "@/composables/shared/useConfirmDialog";

interface MenuGroup {
    id: number;
    name: string;
    display_name: string | null;
}

interface ParentMenu {
    id: number;
    title: string;
    group_id: number;
}

interface RoleItem {
    id: number;
    name: string;
    display_name: string | null;
}

interface PermissionItem {
    id: number;
    name: string;
    display_name: string | null;
}

const props = defineProps<{
    menus: DataTableResponse<Menu>;
    groups: MenuGroup[];
    parent_menus: ParentMenu[];
    roles: RoleItem[];
    permissions: PermissionItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Dashboard", href: route("dashboard") },
    { title: "Menu", href: route("menu.index") },
];

// ── Dialog state ──────────────────────────────────────────────────────────────
const dialogOpen = ref(false);
const selected = ref<Menu | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    dialogOpen.value = true;
}

function openEdit(row: Menu) {
    selected.value = row;
    dialogOpen.value = true;
}

async function handleDelete(row: Menu) {
    const confirmed = await openDialog({
        title: "Hapus Menu",
        description: `Menu "${row.title}" akan dihapus secara permanen. Lanjutkan?`,
        confirmText: "Hapus",
        cancelText: "Batal",
        variant: "destructive",
    });
    if (!confirmed) return;
    router.delete(route("menu.destroy", row.id), { preserveScroll: true });
}

// ── Columns ───────────────────────────────────────────────────────────────────
const columns = createColumns({ onEdit: openEdit, onDelete: handleDelete });

function handleServerRequest(params: any) {
    router.get(route("menu.index"), params, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl">
            <!-- Header -->
            <Heading title="Menu Items" description="Kelola item menu navigasi">
                <Button size="sm" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Add Menu
                </Button>
            </Heading>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="menus.data"
                :meta="menus.meta"
                search-placeholder="Cari menu..."
                @server-request="handleServerRequest"
            />
        </div>

        <!-- Dialog -->
        <MenuDialog
            v-model:open="dialogOpen"
            :menu="selected"
            :groups="groups"
            :parent-menus="parent_menus"
            :roles="roles"
            :permissions="permissions"
        />
        <ConfirmDialog />
    </AppLayout>
</template>
