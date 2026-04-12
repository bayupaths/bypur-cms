<script setup lang="ts">
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import { Plus } from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";
import DataTable from "@/components/data-table/DataTable.vue";
import { Button } from "@/components/ui/button";
import Heading from "@/components/common/Heading.vue";
import type { BreadcrumbItem, DataTableResponse } from "@/types";
import { createColumns, type MenuGroup } from "./columns";
import MenuGroupDialog from "./partials/MenuGroupDialog.vue";
import ConfirmDialog from "@/components/dialogs/ConfirmDialog.vue";
import { useConfirmDialog } from "@/composables/shared/useConfirmDialog";

const props = defineProps<{
    groups: DataTableResponse<MenuGroup>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: "Dashboard", href: route("dashboard") },
    { title: "Menu Groups", href: route("menu.groups.index") },
];

// ── Dialog state ──────────────────────────────────────────────────────────────
const dialogOpen = ref(false);
const selected = ref<MenuGroup | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    dialogOpen.value = true;
}

function openEdit(row: MenuGroup) {
    selected.value = row;
    dialogOpen.value = true;
}

async function handleDelete(row: MenuGroup) {
    const confirmed = await openDialog({
        title: 'Hapus Group',
        description: `Group "${row.name}" akan dihapus secara permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!confirmed) return;
    router.delete(route("menu.groups.destroy", row.id), {
        preserveScroll: true,
    });
}

// ── Columns ───────────────────────────────────────────────────────────────────
const columns = createColumns({ onEdit: openEdit, onDelete: handleDelete });

function handleServerRequest(params: any) {
    router.get(route("menu.groups.index"), params, {
        preserveState: true,
        replace: true,
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-2 rounded-xl">
            <!-- Header -->
            <Heading title="Menu Groups" description="Kelola group menu navigasi">
                <Button size="sm" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Add Group
                </Button>
            </Heading>

            <!-- DataTable -->
            <DataTable
                :columns="columns"
                :data="groups.data"
                :meta="groups.meta"
                search-placeholder="Cari group..."
                @server-request="handleServerRequest"
            />
        </div>

        <!-- Dialog -->
        <MenuGroupDialog v-model:open="dialogOpen" :group="selected" />
        <ConfirmDialog />
    </AppLayout>
</template>
