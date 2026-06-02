<script setup lang="ts">
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Switch } from '@/components/ui/switch';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { Permission, Role } from '@/types';

type PermissionsGrouped = Record<string, Permission[]>;

const props = defineProps<{
    open: boolean;
    role: Role | null;
    permissionsGrouped: PermissionsGrouped;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const CRUD_ACTIONS = ['view', 'create', 'edit', 'delete'] as const;

const form = useForm({
    permissions: [] as number[],
});

watch(
    () => props.open,
    (val) => {
        if (val && props.role) {
            form.permissions = [...(props.role.permission_ids ?? [])];
        }
    },
);

function close() {
    emit('update:open', false);
}

// ── Helpers ───────────────────────────────────────────────────────────────────

const getAction = (name: string): string => {
    const dot = name.indexOf('.');
    return dot >= 0 ? name.slice(dot + 1) : name;
};

const findCrudPermission = (perms: Permission[], action: string): Permission | undefined =>
    perms.find((p) => getAction(p.name) === action);

const getCustomPermissions = (perms: Permission[]): Permission[] =>
    perms.filter((p) => !(CRUD_ACTIONS as readonly string[]).includes(getAction(p.name)));

const hasAnyCustom = computed(() =>
    Object.values(props.permissionsGrouped).some((perms) => getCustomPermissions(perms).length > 0),
);

const allPermissionIds = computed(() =>
    Object.values(props.permissionsGrouped).flatMap((perms) => perms.map((p) => p.id)),
);

// ── Toggle helpers ────────────────────────────────────────────────────────────

const isChecked = (id: number) => form.permissions.includes(id);

const togglePermission = (id: number) => {
    const idx = form.permissions.indexOf(id);
    if (idx === -1) form.permissions.push(id);
    else form.permissions.splice(idx, 1);
};

const isGroupChecked = (group: string) => {
    const perms = props.permissionsGrouped[group] ?? [];
    return perms.length > 0 && perms.every((p) => form.permissions.includes(p.id));
};

const isGroupIndeterminate = (group: string) => {
    const perms = props.permissionsGrouped[group] ?? [];
    return perms.some((p) => form.permissions.includes(p.id)) && !isGroupChecked(group);
};

const toggleGroup = (group: string) => {
    const perms = props.permissionsGrouped[group] ?? [];
    if (isGroupChecked(group)) {
        perms.forEach((p) => {
            const idx = form.permissions.indexOf(p.id);
            if (idx !== -1) form.permissions.splice(idx, 1);
        });
    } else {
        perms.forEach((p) => {
            if (!form.permissions.includes(p.id)) form.permissions.push(p.id);
        });
    }
};

const isAllChecked = computed(
    () =>
        allPermissionIds.value.length > 0 &&
        allPermissionIds.value.every((id) => form.permissions.includes(id)),
);

const toggleAll = () => {
    if (isAllChecked.value) {
        form.permissions = [];
    } else {
        form.permissions = [...allPermissionIds.value];
    }
};

const totalSelected = computed(() => form.permissions.length);
const totalAvailable = computed(() => allPermissionIds.value.length);

const formatGroupName = (group: string): string =>
    group.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());

function submit() {
    if (!props.role) return;
    form.put(route('auth.roles.update', props.role.id), {
        preserveScroll: true,
        onSuccess: close,
    });
}
</script>

<template>
    <Sheet :open="open" @update:open="emit('update:open', $event)">
        <SheetContent class="flex w-full flex-col overflow-hidden p-0 sm:max-w-4xl">
            <SheetHeader class="border-b bg-muted/30 px-6 pb-4 pt-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <SheetTitle>
                            {{ role ? `Sync Permissions: ${role.display_name ?? role.name}` : 'Sync Permissions' }}
                        </SheetTitle>
                        <SheetDescription class="mt-1">
                            Pilih permissions yang akan diberikan ke role ini.
                        </SheetDescription>
                    </div>
                    <Badge variant="secondary" class="mt-0.5 shrink-0 tabular-nums">
                        {{ totalSelected }} / {{ totalAvailable }}
                    </Badge>
                </div>
            </SheetHeader>

            <form class="flex flex-1 flex-col overflow-hidden" @submit.prevent="submit">
                <div class="flex-1 overflow-y-auto">
                    <div
                        v-if="Object.keys(permissionsGrouped).length === 0"
                        class="flex h-40 items-center justify-center text-sm text-muted-foreground"
                    >
                        Tidak ada permission tersedia.
                    </div>

                    <TooltipProvider v-else :delay-duration="200">
                        <table class="w-full">
                            <!-- Header -->
                            <thead class="sticky top-0 z-10 bg-muted/90 backdrop-blur-sm">
                                <tr class="border-b">
                                    <th class="w-48 min-w-48 px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                        Group
                                    </th>
                                    <th class="w-16 px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                        <div class="flex flex-col items-center gap-1">
                                            <span>All</span>
                                            <Switch :model-value="isAllChecked" @update:model-value="toggleAll" />
                                        </div>
                                    </th>
                                    <th
                                        v-for="action in CRUD_ACTIONS"
                                        :key="action"
                                        class="w-20 px-2 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                                    >
                                        {{ action }}
                                    </th>
                                    <th
                                        v-if="hasAnyCustom"
                                        class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                                    >
                                        Lainnya
                                    </th>
                                </tr>
                            </thead>

                            <!-- Body -->
                            <tbody>
                                <tr
                                    v-for="(groupPermissions, group) in permissionsGrouped"
                                    :key="group"
                                    class="border-b last:border-b-0 transition-colors hover:bg-muted/30"
                                >
                                    <!-- Group name -->
                                    <td class="w-48 min-w-48 px-5 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div
                                                class="size-2 shrink-0 rounded-full"
                                                :class="
                                                    isGroupChecked(group as string)
                                                        ? 'bg-primary'
                                                        : isGroupIndeterminate(group as string)
                                                          ? 'bg-primary/50'
                                                          : 'bg-muted-foreground/25'
                                                "
                                            />
                                            <span class="text-sm font-semibold">{{ formatGroupName(group as string) }}</span>
                                        </div>
                                    </td>

                                    <!-- Toggle all in group -->
                                    <td class="px-2 py-3 text-center">
                                        <Switch
                                            :model-value="isGroupChecked(group as string)"
                                            class="mx-auto"
                                            @update:model-value="toggleGroup(group as string)"
                                        />
                                    </td>

                                    <!-- CRUD columns -->
                                    <td
                                        v-for="action in CRUD_ACTIONS"
                                        :key="action"
                                        class="px-2 py-3 text-center"
                                    >
                                        <template v-if="findCrudPermission(groupPermissions, action)">
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <span class="inline-flex">
                                                        <Switch
                                                            :model-value="isChecked(findCrudPermission(groupPermissions, action)!.id)"
                                                            @update:model-value="togglePermission(findCrudPermission(groupPermissions, action)!.id)"
                                                        />
                                                    </span>
                                                </TooltipTrigger>
                                                <TooltipContent side="top" class="text-xs">
                                                    {{ findCrudPermission(groupPermissions, action)!.name }}
                                                </TooltipContent>
                                            </Tooltip>
                                        </template>
                                        <span v-else class="text-muted-foreground/30">—</span>
                                    </td>

                                    <!-- Custom permissions -->
                                    <td v-if="hasAnyCustom" class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1.5">
                                            <Tooltip
                                                v-for="perm in getCustomPermissions(groupPermissions)"
                                                :key="perm.id"
                                            >
                                                <TooltipTrigger as-child>
                                                    <button
                                                        type="button"
                                                        class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium shadow-xs transition-all"
                                                        :class="
                                                            isChecked(perm.id)
                                                                ? 'border-primary/40 bg-primary/10 text-primary ring-1 ring-primary/20'
                                                                : 'border-border bg-card text-muted-foreground hover:bg-muted/60 hover:text-foreground'
                                                        "
                                                        @click="togglePermission(perm.id)"
                                                    >
                                                        <span
                                                            class="inline-block size-1.5 shrink-0 rounded-full transition-colors"
                                                            :class="isChecked(perm.id) ? 'bg-primary' : 'bg-muted-foreground/30'"
                                                        />
                                                        {{ perm.display_name ?? getAction(perm.name) }}
                                                    </button>
                                                </TooltipTrigger>
                                                <TooltipContent side="top" class="text-xs">
                                                    {{ perm.name }}
                                                </TooltipContent>
                                            </Tooltip>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </TooltipProvider>
                </div>

                <SheetFooter class="border-t bg-muted/20 px-6 py-4">
                    <Button type="button" variant="outline" :disabled="form.processing" @click="close">
                        Batal
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Permissions' }}
                    </Button>
                </SheetFooter>
            </form>
        </SheetContent>
    </Sheet>
</template>
