import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableRowActions from '@/components/data-table/DataTableRowActions.vue';
import { Badge } from '@/components/ui/badge';

export interface MenuGroup {
    id: number;
    name: string;
    display_name: string | null;
    description: string | null;
    is_active: boolean;
    menus_count: number;
    created_at: string;
    updated_at: string;
}

export const createColumns = (handlers: {
    onEdit: (row: MenuGroup) => void;
    onDelete: (row: MenuGroup) => void;
}): ColumnDef<MenuGroup>[] => [
    {
        id: 'no',
        header: 'No',
        enableSorting: false,
        enableHiding: false,
        cell: ({ row, table }) => {
            const { pageIndex, pageSize } = table.getState().pagination;
            return pageIndex * pageSize + row.index + 1;
        },
    },

    {
        accessorKey: 'name',
        meta: { label: 'Name' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Name' }),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.getValue('name')),
    },

    {
        accessorKey: 'display_name',
        meta: { label: 'Display Name' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Display Name' }),
        cell: ({ row }) => row.getValue('display_name') ?? '-',
    },

    {
        accessorKey: 'description',
        meta: { label: 'Description' },
        header: 'Description',
        enableSorting: false,
        cell: ({ row }) => {
            const val = row.getValue<string | null>('description');
            if (!val) return '-';
            return val.length > 60 ? val.slice(0, 60) + '…' : val;
        },
    },

    {
        accessorKey: 'menus_count',
        meta: { label: 'Total Menu' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Total Menu' }),
        enableSorting: false,
        cell: ({ row }) => h('span', { class: 'tabular-nums' }, row.getValue('menus_count') ?? 0),
    },

    {
        accessorKey: 'is_active',
        meta: { label: 'Status' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),
        cell: ({ row }) =>
            h(
                Badge,
                { variant: row.getValue('is_active') ? 'default' : 'secondary' },
                () => (row.getValue('is_active') ? 'Active' : 'Inactive'),
            ),
    },

    {
        accessorKey: 'created_at',
        meta: { label: 'Created' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Created' }),
        cell: ({ row }) =>
            new Date(row.getValue('created_at')).toLocaleDateString('id-ID'),
    },

    {
        id: 'actions',
        meta: { label: 'Actions' },
        header: 'Action',
        enableSorting: false,
        enableHiding: false,
        cell: ({ row }) =>
            h(DataTableRowActions, {
                row: row.original,
                onEdit: () => handlers.onEdit(row.original),
                onDelete: () => handlers.onDelete(row.original),
            }),
    },
];
