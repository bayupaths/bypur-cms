<script setup lang="ts">
import { computed } from 'vue';
import {
    BarChart3, CalendarDays, Camera, CheckCircle2, ChevronRight,
    ExternalLink, FileText, Lock, Mail, MapPin, Monitor,
    Phone, ShieldAlert, ShieldCheck, XCircle,
} from 'lucide-vue-next';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import type { User as UserType } from '@/types';

const props = defineProps<{
    authUser: UserType;
    avatarPreview: string | null;
    avatarFile: File | null;
    completionFields: { label: string; filled: boolean; weight: number }[];
    completionPercent: number;
    completionLabel: string;
    completionColor: string;
    loginAttemptsPercent: number;
    form: any;
    formatDateTime: (value?: string | null) => string;
}>();

const emit = defineEmits<{
    triggerAvatarUpload: [];
    removeAvatar: [];
}>();

const initials = (name: string) =>
    name.split(' ').map((w) => w[0]).join('').toUpperCase().slice(0, 2);

const userInitials = computed(() =>
    props.authUser.name
        .split(' ')
        .map((w: string) => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
);
</script>

<template>
    <div class="flex flex-col gap-4">

        <!-- Profile Card with Avatar Upload -->
        <div class="rounded-md border bg-card text-card-foreground overflow-hidden">
            <div class="h-20 bg-linear-to-br from-primary/20 via-primary/10 to-transparent relative">
                <div class="absolute inset-0 be written as `bg-[radial-gradient(ellipse_at_top_right,var(--tw-gradient-stops))] from-primary/20 to-transparent" />
            </div>

            <div class="px-5 pb-5 -mt-10">
                <!-- Avatar with upload overlay -->
                <div class="group relative mb-3 inline-block">
                    <Avatar class="h-20 w-20 rounded-md text-xl ring-4 ring-card shrink-0">
                        <AvatarImage :src="avatarPreview ?? ''" :alt="form.name" />
                        <AvatarFallback class="rounded-md text-xl font-semibold bg-primary/10 text-primary">
                            {{ form.name ? initials(form.name) : userInitials }}
                        </AvatarFallback>
                    </Avatar>
                    <button
                        type="button"
                        class="absolute inset-0 flex flex-col items-center justify-center gap-0.5 rounded-md bg-black/60 opacity-0 transition-all duration-200 group-hover:opacity-100"
                        aria-label="Ganti avatar"
                        @click="emit('triggerAvatarUpload')"
                    >
                        <Camera class="h-4 w-4 text-white" />
                        <span class="text-[9px] text-white font-medium leading-none">Ganti</span>
                    </button>
                </div>

                <!-- If new avatar queued, show indicator -->
                <div v-if="avatarFile" class="mb-3 flex items-center justify-between rounded-lg bg-primary/5 border border-primary/20 px-3 py-1.5">
                    <span class="text-xs text-primary font-medium truncate max-w-40">
                        {{ avatarFile.name }}
                    </span>
                    <button
                        type="button"
                        class="ml-2 shrink-0 text-muted-foreground hover:text-destructive transition-colors"
                        aria-label="Hapus avatar"
                        @click="emit('removeAvatar')"
                    >
                        <XCircle class="h-3.5 w-3.5" />
                    </button>
                </div>

                <!-- Name & meta -->
                <div class="space-y-1 mb-4">
                    <h2 class="text-base font-semibold leading-tight">{{ form.name || authUser.name }}</h2>
                    <p v-if="authUser.username" class="font-mono text-xs text-muted-foreground">@{{ authUser.username }}</p>
                    <p class="text-xs text-muted-foreground truncate">{{ authUser.email }}</p>
                    <p v-if="form.tagline" class="text-xs text-muted-foreground italic">{{ form.tagline }}</p>
                </div>

                <!-- Status badges -->
                <div class="flex flex-wrap gap-1.5 mb-5">
                    <Badge :variant="form.is_available ? 'default' : 'secondary'" class="gap-1.5 rounded-full text-xs">
                        <span class="h-1.5 w-1.5 rounded-full" :class="form.is_available ? 'animate-pulse bg-green-400' : 'bg-muted-foreground'" />
                        {{ form.is_available ? 'Open to Work' : 'Tidak Tersedia' }}
                    </Badge>
                    <Badge :variant="authUser.is_active ? 'default' : 'outline'" class="gap-1 rounded-full text-xs">
                        <CheckCircle2 v-if="authUser.is_active" class="h-3 w-3" />
                        <XCircle v-else class="h-3 w-3" />
                        {{ authUser.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>

                <Separator class="mb-4" />

                <!-- Contact info preview -->
                <div class="space-y-3">
                    <template v-if="form.phone || form.email || form.location || form.resume_url">
                        <div v-if="form.phone" class="flex items-center gap-2.5">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-muted">
                                <Phone class="h-3 w-3 text-muted-foreground" />
                            </div>
                            <span class="truncate text-sm">{{ form.phone }}</span>
                        </div>
                        <div v-if="form.email" class="flex items-center gap-2.5">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-muted">
                                <Mail class="h-3 w-3 text-muted-foreground" />
                            </div>
                            <span class="truncate text-sm">{{ form.email }}</span>
                        </div>
                        <div v-if="form.location" class="flex items-center gap-2.5">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-muted">
                                <MapPin class="h-3 w-3 text-muted-foreground" />
                            </div>
                            <span class="truncate text-sm">{{ form.location }}</span>
                        </div>
                        <div v-if="form.resume_url" class="flex items-center gap-2.5">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-muted">
                                <FileText class="h-3 w-3 text-muted-foreground" />
                            </div>
                            <a
                                :href="form.resume_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="truncate text-sm text-primary underline-offset-4 hover:underline flex items-center gap-1"
                            >
                                Lihat Resume
                                <ExternalLink class="h-3 w-3" />
                            </a>
                        </div>
                    </template>
                    <p v-else class="text-xs italic text-muted-foreground/60">
                        Belum ada info kontak — isi di tab Kontak
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="rounded-md border bg-card p-5 text-card-foreground">
            <div class="mb-3 flex items-center justify-between">
                <p class="flex items-center gap-1.5 text-xs font-medium uppercase tracking-wider text-muted-foreground">
                    <BarChart3 class="h-3.5 w-3.5" />
                    Kelengkapan Profil
                </p>
                <span class="text-sm font-semibold" :class="completionColor">{{ completionPercent }}%</span>
            </div>
            <Progress :model-value="completionPercent" class="h-2 mb-2" />
            <p class="text-xs text-muted-foreground mb-3">
                {{ completionLabel }} · {{ completionFields.filter(f => f.filled).length }} dari {{ completionFields.length }} bagian terisi
            </p>
            <div class="space-y-1.5">
                <div
                    v-for="field in completionFields.filter(f => !f.filled)"
                    :key="field.label"
                    class="flex items-center gap-2 rounded-lg bg-muted/50 px-3 py-1.5 text-xs text-muted-foreground"
                >
                    <div class="h-1.5 w-1.5 rounded-full bg-amber-400 shrink-0" />
                    {{ field.label }} belum diisi
                    <ChevronRight class="h-3 w-3 ml-auto" />
                </div>
            </div>
        </div>
    </div>
</template>
