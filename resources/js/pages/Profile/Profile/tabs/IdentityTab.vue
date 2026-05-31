<script setup lang="ts">
import {
    Briefcase, CalendarDays, ExternalLink, Mars,
    Plus, Sparkles, Tag, User, UserCheck, Venus, X,
} from 'lucide-vue-next';
import InputError from '@/components/common/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';

const form = defineModel<any>('form', { required: true });

const MAX_TAGLINES = 5;

function addTagline() {
    if (!form.value.taglines) form.value.taglines = [];
    if (form.value.taglines.length < MAX_TAGLINES) form.value.taglines.push('');
}

function removeTagline(index: number) {
    form.value.taglines.splice(index, 1);
}
</script>

<template>
    <div class="space-y-4">

        <!-- ── Identitas Publik ─────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">

            <!-- Section header -->
            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <User class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Identitas Publik</span>
                <a
                    v-if="form.website_url"
                    :href="form.website_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="ml-auto flex shrink-0 items-center gap-1 text-xs text-primary underline-offset-4 hover:underline"
                >
                    <ExternalLink class="h-3 w-3" />
                    Lihat portofolio
                </a>
            </div>

            <div class="space-y-5 p-5">

                <!-- Row 1: Nama Lengkap + Nama Panggilan -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label for="name" class="text-xs font-medium">
                            Nama Lengkap <span class="text-destructive">*</span>
                        </Label>
                        <div class="relative">
                            <User class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Nama lengkap Anda"
                                :disabled="form.processing"
                                class="pl-9"
                            />
                        </div>
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="nickname" class="text-xs font-medium">Nama Panggilan</Label>
                        <div class="relative">
                            <Tag class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="nickname"
                                v-model="form.nickname"
                                placeholder="Contoh: Bayu, Andi, Dika"
                                :disabled="form.processing"
                                class="pl-9"
                            />
                        </div>
                        <InputError :message="form.errors.nickname" />
                        <p class="text-xs text-muted-foreground">Nama yang biasa dipakai sehari-hari</p>
                    </div>
                </div>

                <Separator />

                <!-- Row 2: Taglines -->
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-0.5">
                            <Label class="text-xs font-medium">Tagline</Label>
                            <p class="text-xs text-muted-foreground">
                                Deskripsi singkat yang tampil di bawah nama pada halaman portofolio
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full bg-muted px-2 py-0.5 text-xs tabular-nums text-muted-foreground">
                            {{ (form.taglines ?? []).length }}/{{ MAX_TAGLINES }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="(_, index) in (form.taglines as string[])"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <div class="relative flex-1">
                                <Sparkles class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="(form.taglines as string[])[index]"
                                    :placeholder="`Tagline ${(index as number) + 1} — misal: Full-Stack Developer`"
                                    :disabled="form.processing"
                                    class="pl-9"
                                />
                            </div>
                            <button
                                type="button"
                                :disabled="form.processing"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border bg-background text-muted-foreground transition-colors hover:border-destructive hover:text-destructive disabled:pointer-events-none disabled:opacity-50"
                                aria-label="Hapus tagline"
                                @click="removeTagline(index)"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="!form.taglines || form.taglines.length === 0"
                        class="flex items-center gap-2 rounded-md border border-dashed bg-muted/30 px-4 py-3 text-xs text-muted-foreground"
                    >
                        <Sparkles class="h-3.5 w-3.5 shrink-0" />
                        Belum ada tagline — klik tombol di bawah untuk menambahkan
                    </div>

                    <button
                        v-if="(form.taglines ?? []).length < MAX_TAGLINES"
                        type="button"
                        :disabled="form.processing"
                        class="flex w-full items-center justify-center gap-1.5 rounded-md border border-dashed py-2 text-xs text-muted-foreground transition-colors hover:border-primary hover:text-primary disabled:pointer-events-none disabled:opacity-50"
                        @click="addTagline"
                    >
                        <Plus class="h-3.5 w-3.5" />
                        Tambah tagline
                    </button>

                    <InputError :message="form.errors.taglines" />
                </div>
            </div>
        </div>

        <!-- ── Data Pribadi ─────────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">

            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <CalendarDays class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Data Pribadi</span>
            </div>

            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-2">
                <!-- Jenis Kelamin -->
                <div class="space-y-1.5">
                    <Label for="gender" class="text-xs font-medium">Jenis Kelamin</Label>
                    <Select v-model="form.gender" :disabled="form.processing">
                        <SelectTrigger id="gender" class="h-9 w-full text-sm">
                            <SelectValue placeholder="Pilih jenis kelamin..." />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="male">
                                <span class="flex items-center gap-2 py-0.5">
                                    <Mars class="h-4 w-4 text-blue-500" /> Laki-laki
                                </span>
                            </SelectItem>
                            <SelectItem value="female">
                                <span class="flex items-center gap-2 py-0.5">
                                    <Venus class="h-4 w-4 text-pink-500" /> Perempuan
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.gender" />
                </div>

                <!-- Tanggal Lahir -->
                <div class="space-y-1.5">
                    <Label for="birth_date" class="text-xs font-medium">Tanggal Lahir</Label>
                    <div class="relative">
                        <CalendarDays class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="birth_date"
                            v-model="form.birth_date"
                            type="date"
                            :disabled="form.processing"
                            class="pl-9"
                        />
                    </div>
                    <InputError :message="form.errors.birth_date" />
                </div>
            </div>
        </div>

        <!-- ── Status Ketersediaan ──────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">

            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <Briefcase class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Status Ketersediaan</span>
            </div>

            <div class="p-5">
                <div
                    class="flex items-center justify-between gap-4 rounded-md px-4 py-3.5 transition-colors"
                    :class="form.is_available
                        ? 'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800'
                        : 'bg-muted/50 border border-transparent'"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md transition-colors"
                            :class="form.is_available ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-muted'"
                        >
                            <UserCheck
                                class="h-4 w-4 transition-colors"
                                :class="form.is_available ? 'text-emerald-600 dark:text-emerald-400' : 'text-muted-foreground'"
                            />
                        </div>
                        <div>
                            <p class="text-sm font-medium">
                                {{ form.is_available ? 'Tersedia untuk Project' : 'Tidak Tersedia' }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                Tampilkan badge "Open to Work" di halaman portofolio publik
                            </p>
                        </div>
                    </div>
                    <Switch v-model:checked="form.is_available" class="shrink-0" />
                </div>
            </div>
        </div>
    </div>
</template>
