<script setup lang="ts">
import { computed } from 'vue';
import { AlertTriangle, AtSign, Calendar, CheckCircle2, Clock, ShieldAlert, ShieldCheck } from 'lucide-vue-next';
import type { User as UserType } from '@/types';

const props = defineProps<{
    authUser: UserType;
    formatDateTime: (value?: string | null) => string;
}>();

const loginAttemptsPercent = computed(() =>
    Math.min(((props.authUser.login_attempts ?? 0) / 5) * 100, 100)
);

const isLocked = computed(() =>
    !!(props.authUser.locked_until && new Date(props.authUser.locked_until) > new Date())
);
</script>

<template>
    <div class="space-y-4">

        <!-- ── Informasi Akun ──────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">
            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <AtSign class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Informasi Akun</span>
            </div>
            <div class="divide-y p-0">

                <!-- Username -->
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-xs text-muted-foreground">Username</span>
                    <span class="font-mono text-xs font-medium">
                        {{ authUser.username ? '@' + authUser.username : '–' }}
                    </span>
                </div>

                <!-- Email -->
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-xs text-muted-foreground">Email login</span>
                    <span class="text-xs">{{ authUser.email }}</span>
                </div>

                <!-- Status email -->
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-xs text-muted-foreground">Status email</span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="authUser.email_verified_at
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                    >
                        <CheckCircle2 v-if="authUser.email_verified_at" class="h-3 w-3" />
                        <AlertTriangle v-else class="h-3 w-3" />
                        {{ authUser.email_verified_at ? 'Terverifikasi' : 'Belum verifikasi' }}
                    </span>
                </div>

                <!-- Status akun -->
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-xs text-muted-foreground">Status akun</span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="authUser.is_active
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full"
                            :class="authUser.is_active ? 'bg-emerald-500' : 'bg-red-500'"
                        />
                        {{ authUser.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

            </div>
        </div>

        <!-- ── Keamanan ────────────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">
            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <component
                    :is="isLocked || (authUser.login_attempts ?? 0) > 0 ? ShieldAlert : ShieldCheck"
                    class="h-3.5 w-3.5"
                    :class="isLocked || (authUser.login_attempts ?? 0) > 0
                        ? 'text-destructive'
                        : 'text-emerald-500'"
                />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Keamanan</span>
            </div>
            <div class="divide-y p-0">

                <!-- Login terakhir -->
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-xs text-muted-foreground">Login terakhir</span>
                    <span class="text-xs">{{ authUser.last_login_at ? formatDateTime(authUser.last_login_at) : '–' }}</span>
                </div>

                <!-- IP terakhir -->
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-xs text-muted-foreground">IP terakhir</span>
                    <span class="font-mono text-xs">{{ authUser.last_login_ip ?? '–' }}</span>
                </div>

                <!-- Percobaan login -->
                <div class="space-y-2 px-5 py-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-muted-foreground">Percobaan login gagal</span>
                        <span
                            class="tabular-nums text-xs font-medium"
                            :class="(authUser.login_attempts ?? 0) > 0 ? 'text-destructive' : 'text-muted-foreground'"
                        >
                            {{ authUser.login_attempts ?? 0 }} / 5
                        </span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :class="(authUser.login_attempts ?? 0) >= 3 ? 'bg-destructive' : (authUser.login_attempts ?? 0) > 0 ? 'bg-amber-400' : 'bg-emerald-500'"
                            :style="{ width: `${loginAttemptsPercent}%` }"
                        />
                    </div>
                </div>

                <!-- Locked until -->
                <div v-if="isLocked" class="flex items-center justify-between px-5 py-3.5">
                    <span class="text-xs text-muted-foreground">Akun terkunci hingga</span>
                    <span class="text-xs font-medium text-destructive">{{ formatDateTime(authUser.locked_until) }}</span>
                </div>

            </div>
        </div>

        <!-- ── Aktivitas ───────────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">
            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Aktivitas</span>
            </div>
            <div class="divide-y p-0">
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Calendar class="h-3 w-3" />
                        Akun dibuat
                    </span>
                    <span class="text-xs">{{ formatDateTime(authUser.created_at) }}</span>
                </div>
                <div class="flex items-center justify-between px-5 py-3.5">
                    <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Clock class="h-3 w-3" />
                        Terakhir diperbarui
                    </span>
                    <span class="text-xs">{{ formatDateTime(authUser.updated_at) }}</span>
                </div>
            </div>
        </div>

    </div>
</template>
