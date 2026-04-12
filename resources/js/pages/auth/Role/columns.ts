import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Badge } from '@/components/ui/badge';
import {
    DropdownMenuItem,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableRowActions from '@/components/data-table/DataTableRowActions.vue';
import type { Role } from '@/types';

export type { Role };

export const createColumns = (handlers: {
    onEdit: (row: Role) => void;
    onSyncPermissions: (row: Role) => void;
    onDelete: (row: Role) => void;
}): ColumnDef<Role>[] => [
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
        meta: { label: 'Role' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Role' }),
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
        accessorKey: 'guard_name',
        meta: { label: 'Guard' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Guard' }),
        cell: ({ row }) =>
            h(Badge, { variant: 'secondary', class: 'text-xs' }, () => row.getValue('guard_name')),
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
        accessorKey: 'users_count',
        meta: { label: 'Users' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Users' }),
        cell: ({ row }) =>
            h(Badge, { variant: 'outline', class: 'text-xs' }, () => String(row.original.users_count ?? 0)),
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
            h(
                DataTableRowActions,
                { row: row.original },
                {
                    actions: () => [
                        h(DropdownMenuItem, { onClick: () => handlers.onEdit(row.original) }, () => 'Edit'),
                        h(DropdownMenuItem, { onClick: () => handlers.onSyncPermissions(row.original) }, () => 'Sync Permissions'),
                        h(DropdownMenuSeparator),
                        h(
                            DropdownMenuItem,
                            { class: 'text-destructive focus:text-destructive', onClick: () => handlers.onDelete(row.original) },
                            () => 'Hapus',
                        ),
                    ],
                },
            ),
    },
];
