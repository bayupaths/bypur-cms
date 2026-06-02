import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableRowActions from '@/components/data-table/DataTableRowActions.vue';
import type { Permission } from '@/types';

export type { Permission };

export const createColumns = (handlers: {
    onEdit: (row: Permission) => void;
    onDelete: (row: Permission) => void;
}): ColumnDef<Permission>[] => [
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
        meta: { label: 'Permission' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Permission' }),
        cell: ({ row }) => {
            const name = row.getValue('name') as string;
            const displayName = row.original.display_name;
            return h('div', { class: 'grid leading-tight' }, [
                h('span', { class: 'font-medium text-sm' }, displayName ?? name),
                displayName
                    ? h('span', { class: 'text-xs text-muted-foreground' }, name)
                    : null,
            ]);
        },
    },

    {
        accessorKey: 'group',
        meta: { label: 'Group' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Group' }),
        cell: ({ row }) => {
            const group = row.original.group;
            return group
                ? h(Badge, { variant: 'secondary', class: 'text-xs' }, () => group)
                : h('span', { class: 'text-muted-foreground text-xs' }, '—');
        },
    },

    {
        accessorKey: 'guard_name',
        meta: { label: 'Guard' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Guard' }),
        cell: ({ row }) =>
            h(Badge, { variant: 'outline', class: 'text-xs' }, () => row.getValue('guard_name')),
    },

    {
        accessorKey: 'description',
        meta: { label: 'Deskripsi' },
        header: 'Deskripsi',
        enableSorting: false,
        cell: ({ row }) =>
            row.original.description
                ? h('span', { class: 'text-sm text-muted-foreground line-clamp-1 max-w-xs' }, row.original.description)
                : h('span', { class: 'text-muted-foreground text-xs' }, '—'),
    },

    {
        accessorKey: 'created_at',
        meta: { label: 'Dibuat' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Dibuat' }),
        cell: ({ row }) =>
            new Date(row.getValue('created_at')).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric',
            }),
    },

    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) =>
            h(DataTableRowActions, {
                row: row.original,
                onEdit: () => handlers.onEdit(row.original),
                onDelete: () => handlers.onDelete(row.original),
            }),
    },
];
