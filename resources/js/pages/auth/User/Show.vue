<script setup lang="ts">
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    AtSign,
    CalendarDays,
    CheckCircle2,
    Github,
    Globe,
    Instagram,
    Linkedin,
    Lock,
    MapPin,
    Monitor,
    Pencil,
    ShieldAlert,
    ShieldCheck,
    Trash2,
    Twitter,
    XCircle,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/common/Heading.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import UserSheet from './partials/UserSheet.vue';
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Progress } from '@/components/ui/progress';
import type { BreadcrumbItem, User } from '@/types';

interface RoleItem {
    id: number;
    name: string;
    display_name: string | null;
}

const props = defineProps<{
    user: User & {
        roles_list?: RoleItem[];
    };
    roles: RoleItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Users', href: route('auth.users.index') },
    { title: props.user.name, href: route('auth.users.show', props.user.id) },
];

const initials = computed(() =>
    props.user.name
        .split(' ')
        .map((w: string) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
);

const loginAttemptsPercent = computed(() =>
    Math.min(((props.user.login_attempts ?? 0) / 5) * 100, 100)
);

const hasSocialLinks = computed(() =>
    !!(props.user.website || props.user.github || props.user.linkedin || props.user.twitter || props.user.instagram)
);

const cityCountryLine = computed(() =>
    [props.user.city, props.user.country, props.user.postal_code].filter(Boolean).join(', ')
);

const page = usePage();
const isSelf = computed(() => (page.props.auth as any).user?.id === props.user.id);

// ── Sheet ─────────────────────────────────────────────────────────────────────
const sheetOpen = ref(false);

// ── Delete ────────────────────────────────────────────────────────────────────
const { openDialog } = useConfirmDialog();

async function handleDelete() {
    const confirmed = await openDialog({
        title: 'Hapus User',
        description: `User "${props.user.name}" akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.`,
        confirmText: 'Hapus Permanen',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!confirmed) return;
    router.delete(route('auth.users.destroy', props.user.id), {
        onSuccess: () => router.visit(route('auth.users.index')),
    });
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatDate(value?: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

function formatDateTime(value?: string | null): string {
    if (!value) return '—';
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4">

            <!-- ── Page Header ─────────────────────────────────────────────── -->
            <Heading :title="user.name" description="Detail informasi pengguna">
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="router.visit(route('auth.users.index'))">
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Kembali
                    </Button>
                    <Button size="sm" @click="sheetOpen = true">
                        <Pencil class="mr-1.5 h-4 w-4" />
                        Edit
                    </Button>
                </div>
            </Heading>

            <!-- ── Main Grid ───────────────────────────────────────────────── -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

                <!-- ── Left Column ─────────────────────────────────────────── -->
                <div class="flex flex-col gap-4">

                    <!-- Hero Card -->
                    <div class="rounded-xl border bg-card p-5 text-card-foreground">
                        <div class="flex flex-col items-center gap-4 text-center">
                            <Avatar class="h-20 w-20 shrink-0 rounded-xl text-xl">
                                <AvatarImage :src="user.avatar ?? ''" :alt="user.name" />
                                <AvatarFallback class="rounded-xl text-xl font-semibold">
                                    {{ initials }}
                                </AvatarFallback>
                            </Avatar>

                            <div class="space-y-1.5">
                                <h2 class="text-base font-semibold leading-none">{{ user.name }}</h2>
                                <p v-if="user.username" class="font-mono text-xs text-muted-foreground">@{{ user.username }}</p>
                                <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                <p v-if="user.phone" class="text-xs text-muted-foreground">{{ user.phone }}</p>
                            </div>

                            <div class="flex flex-wrap justify-center gap-1.5">
                                <Badge
                                    :variant="user.is_active ? 'default' : 'outline'"
                                    class="gap-1 rounded-full text-xs"
                                >
                                    <CheckCircle2 v-if="user.is_active" class="h-3 w-3" />
                                    <XCircle v-else class="h-3 w-3" />
                                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                                <Badge v-if="user.is_superadmin" variant="destructive" class="gap-1 rounded-full text-xs">
                                    <ShieldCheck class="h-3 w-3" />
                                    Superadmin
                                </Badge>
                            </div>

                            <div v-if="user.roles_list?.length" class="flex flex-wrap justify-center gap-1.5">
                                <Badge
                                    v-for="role in user.roles_list"
                                    :key="role.id"
                                    variant="secondary"
                                    class="rounded-full text-xs"
                                >
                                    {{ role.display_name ?? role.name }}
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <!-- Keamanan -->
                    <div class="rounded-xl border bg-card p-5 text-card-foreground">
                        <p class="mb-4 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            <ShieldCheck class="h-3.5 w-3.5" />
                            Keamanan
                        </p>
                        <div class="space-y-4">

                            <!-- Login terakhir -->
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted">
                                    <Monitor class="h-3.5 w-3.5 text-muted-foreground" />
                                </div>
                                <div class="grid gap-0.5">
                                    <span class="text-xs text-muted-foreground">Login terakhir</span>
                                    <span class="text-sm">{{ formatDateTime(user.last_login_at) }}</span>
                                    <span v-if="user.last_login_ip" class="font-mono text-xs text-muted-foreground">
                                        {{ user.last_login_ip }}
                                    </span>
                                </div>
                            </div>

                            <!-- Percobaan gagal + progress -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                                    :class="(user.login_attempts ?? 0) > 0 ? 'bg-destructive/10' : 'bg-muted'"
                                >
                                    <ShieldAlert
                                        class="h-3.5 w-3.5"
                                        :class="(user.login_attempts ?? 0) > 0 ? 'text-destructive' : 'text-muted-foreground'"
                                    />
                                </div>
                                <div class="w-full space-y-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-muted-foreground">Percobaan gagal</span>
                                        <span
                                            class="text-xs font-medium"
                                            :class="(user.login_attempts ?? 0) > 0 ? 'text-destructive' : 'text-muted-foreground'"
                                        >
                                            {{ user.login_attempts ?? 0 }} / 5
                                        </span>
                                    </div>
                                    <Progress :model-value="loginAttemptsPercent" class="h-1.5" />
                                </div>
                            </div>

                            <!-- Terkunci -->
                            <template v-if="user.locked_until">
                                <Separator />
                                <div class="flex items-start gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-destructive/10">
                                        <Lock class="h-3.5 w-3.5 text-destructive" />
                                    </div>
                                    <div class="grid gap-0.5">
                                        <span class="text-xs text-muted-foreground">Terkunci hingga</span>
                                        <span class="text-sm font-medium text-destructive">
                                            {{ formatDateTime(user.locked_until) }}
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Informasi Akun -->
                    <div class="rounded-xl border bg-card p-5 text-card-foreground">
                        <p class="mb-4 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            <CalendarDays class="h-3.5 w-3.5" />
                            Informasi akun
                        </p>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-muted-foreground">Status email</span>
                                <span
                                    class="text-xs font-medium"
                                    :class="user.email_verified_at
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-amber-600 dark:text-amber-400'"
                                >
                                    {{ user.email_verified_at ? '✓ Terverifikasi' : '⚠ Belum verifikasi' }}
                                </span>
                            </div>
                            <Separator />
                            <div class="grid gap-0.5">
                                <span class="text-xs text-muted-foreground">Dibuat</span>
                                <span class="text-sm">{{ formatDateTime(user.created_at) }}</span>
                            </div>
                            <div class="grid gap-0.5">
                                <span class="text-xs text-muted-foreground">Terakhir diperbarui</span>
                                <span class="text-sm">{{ formatDateTime(user.updated_at) }}</span>
                            </div>
                            <div v-if="user.email_verified_at" class="grid gap-0.5">
                                <span class="text-xs text-muted-foreground">Tanggal verifikasi</span>
                                <span class="text-sm">{{ formatDateTime(user.email_verified_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Right Column ─────────────────────────────────────────── -->
                <div class="flex flex-col gap-4 lg:col-span-2">

                    <!-- Profil -->
                    <div class="rounded-xl border bg-card p-5 text-card-foreground">
                        <p class="mb-4 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            <AtSign class="h-3.5 w-3.5" />
                            Profil
                        </p>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted">
                                    <AtSign class="h-3.5 w-3.5 text-muted-foreground" />
                                </div>
                                <div class="grid gap-0.5">
                                    <span class="text-xs text-muted-foreground">Jenis kelamin</span>
                                    <span class="text-sm">
                                        {{ user.gender === 'male' ? 'Laki-laki' : user.gender === 'female' ? 'Perempuan' : '—' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted">
                                    <CalendarDays class="h-3.5 w-3.5 text-muted-foreground" />
                                </div>
                                <div class="grid gap-0.5">
                                    <span class="text-xs text-muted-foreground">Tanggal lahir</span>
                                    <span class="text-sm">{{ formatDate(user.birth_date) }}</span>
                                </div>
                            </div>
                        </div>

                        <template v-if="user.bio">
                            <Separator class="my-4" />
                            <div class="grid gap-1">
                                <span class="text-xs text-muted-foreground">Bio</span>
                                <p class="text-sm leading-relaxed text-foreground/80">{{ user.bio }}</p>
                            </div>
                        </template>

                        <div v-if="!user.gender && !user.birth_date && !user.bio" class="mt-1">
                            <p class="text-sm text-muted-foreground">Belum ada informasi profil.</p>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="rounded-xl border bg-card p-5 text-card-foreground">
                        <p class="mb-4 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            <MapPin class="h-3.5 w-3.5" />
                            Alamat
                        </p>
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-muted">
                                <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                            </div>
                            <div class="grid gap-0.5">
                                <span class="text-sm">{{ user.address ?? '—' }}</span>
                                <span v-if="cityCountryLine" class="text-xs text-muted-foreground">
                                    {{ cityCountryLine }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Media Sosial -->
                    <div class="rounded-xl border bg-card p-5 text-card-foreground">
                        <p class="mb-4 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                            <Globe class="h-3.5 w-3.5" />
                            Media sosial
                        </p>
                        <div v-if="hasSocialLinks" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <a
                                v-if="user.website"
                                :href="user.website"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors hover:bg-muted"
                            >
                                <Globe class="h-4 w-4 shrink-0 text-muted-foreground" />
                                <span class="truncate text-muted-foreground group-hover:text-foreground">
                                    {{ user.website }}
                                </span>
                            </a>
                            <a
                                v-if="user.github"
                                :href="`https://github.com/${user.github}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors hover:bg-muted"
                            >
                                <Github class="h-4 w-4 shrink-0 text-muted-foreground" />
                                <span class="truncate text-muted-foreground group-hover:text-foreground">
                                    {{ user.github }}
                                </span>
                            </a>
                            <a
                                v-if="user.linkedin"
                                :href="`https://linkedin.com/in/${user.linkedin}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors hover:bg-muted"
                            >
                                <Linkedin class="h-4 w-4 shrink-0 text-muted-foreground" />
                                <span class="truncate text-muted-foreground group-hover:text-foreground">
                                    {{ user.linkedin }}
                                </span>
                            </a>
                            <a
                                v-if="user.twitter"
                                :href="`https://twitter.com/${user.twitter}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors hover:bg-muted"
                            >
                                <Twitter class="h-4 w-4 shrink-0 text-muted-foreground" />
                                <span class="truncate text-muted-foreground group-hover:text-foreground">
                                    {{ user.twitter }}
                                </span>
                            </a>
                            <a
                                v-if="user.instagram"
                                :href="`https://instagram.com/${user.instagram}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group flex items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors hover:bg-muted"
                            >
                                <Instagram class="h-4 w-4 shrink-0 text-muted-foreground" />
                                <span class="truncate text-muted-foreground group-hover:text-foreground">
                                    {{ user.instagram }}
                                </span>
                            </a>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">Tidak ada tautan media sosial.</p>
                    </div>

                    <!-- ── Danger Zone ───────────────────────────────────── -->
                    <div v-if="!isSelf" class="rounded-xl border border-destructive/30 bg-card p-5 text-card-foreground">
                        <p class="mb-1 flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-destructive">
                            <Trash2 class="h-3.5 w-3.5" />
                            Danger zone
                        </p>
                        <p class="mb-4 text-sm text-muted-foreground">
                            Tindakan berikut bersifat permanen dan tidak dapat dibatalkan.
                        </p>
                        <div class="flex items-center justify-between gap-4 rounded-lg border border-destructive/20 bg-destructive/5 p-3">
                            <div>
                                <p class="text-sm font-medium">Hapus akun user</p>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    Menghapus
                                    <span class="font-medium text-foreground">{{ user.name }}</span>
                                    secara permanen beserta seluruh datanya.
                                </p>
                            </div>
                            <Button
                                variant="destructive"
                                size="sm"
                                class="shrink-0"
                                @click="handleDelete"
                            >
                                <Trash2 class="mr-1.5 h-4 w-4" />
                                Hapus user
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- ── Edit Sheet ──────────────────────────────────────────────────────── -->
    <UserSheet
        v-model:open="sheetOpen"
        :user="(user as any)"
        :roles="roles"
    />

    <ConfirmDialog />
</template>
