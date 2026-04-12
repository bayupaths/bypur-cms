import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableRowActions from '@/components/data-table/DataTableRowActions.vue';
import { Badge } from '@/components/ui/badge';

export interface Menu {
    id: number;
    group_id: number;
    parent_id: number | null;
    title: string;
    slug: string | null;
    url: string | null;
    is_route: boolean;
    icon: string | null;
    badge: string | null;
    badge_variant: string | null;
    target: '_self' | '_blank' | null;
    order: number;
    is_active: boolean;
    is_divider: boolean;
    group: { id: number; name: string } | null;
    parent: { id: number; title: string } | null;
    roles?: number[];
    permissions?: number[];
    created_at: string;
    updated_at: string;
}

export const createColumns = (handlers: {
    onEdit: (row: Menu) => void;
    onDelete: (row: Menu) => void;
}): ColumnDef<Menu>[] => [
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
        accessorKey: 'title',
        meta: { label: 'Title' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Title' }),
        cell: ({ row }) =>
            h('div', [
                h('span', { class: 'font-medium' }, row.getValue('title')),
                row.original.badge
                    ? h(
                          Badge,
                          {
                              variant: (row.original.badge_variant as any) ?? 'secondary',
                              class: 'ml-2 text-xs',
                          },
                          () => row.original.badge,
                      )
                    : null,
            ]),
    },

    {
        accessorKey: 'group',
        meta: { label: 'Group' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Group' }),
        enableSorting: false,
        cell: ({ row }) => row.original.group?.name ?? '-',
    },

    {
        accessorKey: 'parent',
        meta: { label: 'Parent' },
        header: 'Parent',
        enableSorting: false,
        cell: ({ row }) => row.original.parent?.title ?? '-',
    },

    {
        accessorKey: 'url',
        meta: { label: 'URL / Route' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'URL / Route' }),
        cell: ({ row }) => {
            const url = row.getValue<string | null>('url');
            if (!url) return '-';
            return h(
                'code',
                { class: 'text-xs bg-muted rounded px-1 py-0.5' },
                url.length > 40 ? url.slice(0, 40) + '…' : url,
            );
        },
    },

    {
        accessorKey: 'order',
        meta: { label: 'Order' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Order' }),
        cell: ({ row }) => h('span', { class: 'tabular-nums' }, row.getValue('order')),
    },

    {
        accessorKey: 'is_divider',
        meta: { label: 'Divider' },
        header: 'Divider',
        enableSorting: false,
        cell: ({ row }) =>
            row.getValue('is_divider')
                ? h(Badge, { variant: 'outline' }, () => 'Divider')
                : null,
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
