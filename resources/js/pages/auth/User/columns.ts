import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import DataTableColumnHeader from '@/components/data-table/DataTableColumnHeader.vue';
import DataTableRowActions from '@/components/data-table/DataTableRowActions.vue';
import type { User } from '@/types';

export type { User };

export const createColumns = (handlers: {
    onView: (row: User) => void;
    onEdit: (row: User) => void;
    onDelete: (row: User) => void;
}): ColumnDef<User>[] => [
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
        id: 'user',
        accessorKey: 'name',
        meta: { label: 'User' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'User' }),
        cell: ({ row }) => {
            const user = row.original;
            const initials = user.name
                .split(' ')
                .map((w: string) => w[0])
                .join('')
                .toUpperCase()
                .slice(0, 2);

            return h('div', { class: 'flex items-center gap-3' }, [
                h(Avatar, { class: 'h-8 w-8 rounded-lg' }, () => [
                    h(AvatarImage, { src: user.avatar ?? '', alt: user.name }),
                    h(AvatarFallback, { class: 'rounded-lg text-xs' }, () => initials),
                ]),
                h('div', { class: 'grid leading-tight' }, [
                    h('span', { class: 'font-medium text-sm' }, user.name),
                    h('span', { class: 'text-xs text-muted-foreground' }, user.email),
                ]),
            ]);
        },
    },

    {
        accessorKey: 'username',
        meta: { label: 'Username' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Username' }),
        cell: ({ row }) =>
            row.original.username
                ? h('span', { class: 'text-sm font-mono' }, `@${row.original.username}`)
                : h('span', { class: 'text-muted-foreground text-xs' }, '—'),
    },

    {
        accessorKey: 'phone',
        meta: { label: 'Telepon' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Telepon' }),
        cell: ({ row }) =>
            row.original.phone
                ? h('span', { class: 'text-sm' }, row.original.phone)
                : h('span', { class: 'text-muted-foreground text-xs' }, '—'),
    },

    {
        id: 'roles',
        meta: { label: 'Roles' },
        header: 'Roles',
        enableSorting: false,
        cell: ({ row }) => {
            const roles = (row.original as any).roles_list as { id: number; name: string; display_name: string | null }[] | undefined;
            if (!roles?.length) return h('span', { class: 'text-muted-foreground text-xs' }, '—');
            return h(
                'div',
                { class: 'flex flex-wrap gap-1' },
                roles.map((r) =>
                    h(Badge, { variant: 'secondary', class: 'text-xs' }, () => r.display_name ?? r.name),
                ),
            );
        },
    },

    {
        accessorKey: 'is_active',
        meta: { label: 'Status' },
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),
        cell: ({ row }) =>
            h(
                Badge,
                { variant: row.original.is_active ? 'default' : 'outline', class: 'text-xs' },
                () => (row.original.is_active ? 'Aktif' : 'Nonaktif'),
            ),
    },

    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) =>
            h(DataTableRowActions, {
                row,
                onView: () => handlers.onView(row.original),
                onEdit: () => handlers.onEdit(row.original),
                onDelete: () => handlers.onDelete(row.original),
            }),
    },
];
