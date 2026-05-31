<script setup lang="ts">
import { computed, ref } from 'vue';
import { AlignLeft, FileText, Link as LinkIcon, Upload, X } from 'lucide-vue-next';
import InputError from '@/components/common/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

const form = defineModel<any>('form', { required: true });

const bioMax = 1000;
const bioLength = computed(() => (form.value.bio ?? '').length);
const bioPercent = computed(() => Math.min((bioLength.value / bioMax) * 100, 100));
const bioBarColor = computed(() => {
    if (bioLength.value > bioMax * 0.9) return 'bg-destructive';
    if (bioLength.value > bioMax * 0.7) return 'bg-amber-400';
    return 'bg-primary';
});

// Resume mode: 'url' or 'file'
const resumeMode = ref<'url' | 'file'>(form.value.resume_url ? 'url' : 'file');

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);

const resumeFileInfo = computed(() => {
    const f = form.value.resume_file as File | null;
    if (!f) return null;
    const kb = f.size / 1024;
    const size = kb >= 1024 ? `${(kb / 1024).toFixed(1)} MB` : `${kb.toFixed(0)} KB`;
    return { name: f.name, size };
});

function switchMode(mode: 'url' | 'file') {
    resumeMode.value = mode;
    if (mode === 'url') {
        form.value.resume_file = null;
        if (fileInputRef.value) fileInputRef.value.value = '';
    } else {
        form.value.resume_url = '';
    }
}

function onFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) form.value.resume_file = file;
}

function onDrop(e: DragEvent) {
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) form.value.resume_file = file;
}

function clearFile() {
    form.value.resume_file = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
}
</script>

<template>
    <div class="space-y-4">

        <!-- ── Bio Portofolio ──────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">

            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <AlignLeft class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Bio Portofolio</span>
            </div>

            <div class="p-5">
                <div class="space-y-3">
                    <p class="text-xs text-muted-foreground">
                        Ceritakan dirimu — keahlian, pengalaman, dan passion yang ingin ditampilkan ke publik.
                    </p>

                    <Textarea
                        id="bio"
                        v-model="form.bio"
                        :rows="10"
                        :maxlength="bioMax"
                        placeholder="Saya adalah seorang Full-Stack Developer dengan pengalaman 5 tahun dalam membangun aplikasi web modern…"
                        :disabled="form.processing"
                        class="resize-y leading-relaxed"
                    />

                    <!-- Progress bar + counter -->
                    <div class="flex items-center justify-between gap-4">
                        <InputError :message="form.errors.bio" class="flex-1" />
                        <div class="ml-auto flex shrink-0 items-center gap-2">
                            <div class="h-1.5 w-24 overflow-hidden rounded-full bg-muted" aria-hidden="true">
                                <div
                                    class="h-full rounded-full transition-all duration-300"
                                    :class="bioBarColor"
                                    :style="{ width: `${bioPercent}%` }"
                                />
                            </div>
                            <span
                                class="text-xs tabular-nums"
                                :class="bioLength > bioMax * 0.9 ? 'text-destructive font-medium' : 'text-muted-foreground'"
                            >
                                {{ bioLength }}<span class="text-muted-foreground/60"> / {{ bioMax }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Resume / CV ─────────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">

            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <FileText class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Resume / CV</span>
            </div>

            <div class="space-y-4 p-5">

                <!-- Mode toggle -->
                <div class="inline-flex rounded-lg border bg-muted p-1">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all"
                        :class="resumeMode === 'url'
                            ? 'bg-background text-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground'"
                        @click="switchMode('url')"
                    >
                        <LinkIcon class="h-3.5 w-3.5" />
                        Link / URL
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-all"
                        :class="resumeMode === 'file'
                            ? 'bg-background text-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground'"
                        @click="switchMode('file')"
                    >
                        <Upload class="h-3.5 w-3.5" />
                        Upload File
                    </button>
                </div>

                <!-- Mode: URL -->
                <div v-if="resumeMode === 'url'" class="space-y-1.5">
                    <Label for="resume_url" class="text-xs font-medium">URL Resume / CV</Label>
                    <div class="relative">
                        <LinkIcon class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="resume_url"
                            v-model="form.resume_url"
                            type="url"
                            placeholder="https://drive.google.com/..."
                            :disabled="form.processing"
                            class="pl-9"
                        />
                    </div>
                    <InputError :message="form.errors.resume_url" />
                    <p class="text-xs text-muted-foreground">
                        Tempel link dari Google Drive, Dropbox, Notion, atau platform hosting dokumen lainnya
                    </p>

                    <!-- URL preview card -->
                    <div
                        v-if="form.resume_url"
                        class="flex items-center gap-3 rounded-md border bg-muted/30 px-4 py-3"
                    >
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10">
                            <FileText class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium">Resume / CV</p>
                            <p class="truncate text-xs text-muted-foreground">{{ form.resume_url }}</p>
                        </div>
                        <a
                            :href="form.resume_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="shrink-0 rounded-md border px-3 py-1.5 text-xs text-primary transition-colors hover:bg-muted"
                        >
                            Buka
                        </a>
                    </div>
                </div>

                <!-- Mode: Upload -->
                <div v-else class="space-y-3">
                    <input
                        ref="fileInputRef"
                        type="file"
                        accept=".pdf,.doc,.docx"
                        class="hidden"
                        @change="onFileChange"
                    />

                    <!-- File selected -->
                    <div
                        v-if="resumeFileInfo"
                        class="flex items-center gap-3 rounded-md border bg-muted/30 px-4 py-3"
                    >
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary/10">
                            <FileText class="h-4 w-4 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ resumeFileInfo.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ resumeFileInfo.size }}</p>
                        </div>
                        <button
                            type="button"
                            :disabled="form.processing"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-destructive disabled:pointer-events-none"
                            aria-label="Hapus file"
                            @click="clearFile"
                        >
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </div>

                    <!-- Drop zone -->
                    <button
                        v-else
                        type="button"
                        :disabled="form.processing"
                        class="flex w-full flex-col items-center justify-center gap-3 rounded-md border-2 border-dashed px-6 py-10 transition-colors disabled:pointer-events-none disabled:opacity-50"
                        :class="isDragging
                            ? 'border-primary bg-primary/5'
                            : 'border-muted-foreground/25 hover:border-primary/50 hover:bg-muted/30'"
                        @click="fileInputRef?.click()"
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onDrop"
                    >
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                            <Upload class="h-5 w-5 text-muted-foreground" />
                        </div>
                        <div class="space-y-1 text-center">
                            <p class="text-sm font-medium">Klik atau seret file ke sini</p>
                            <p class="text-xs text-muted-foreground">PDF, DOC, DOCX · Maks. 10 MB</p>
                        </div>
                    </button>

                    <InputError :message="form.errors.resume_file" />
                    <p class="text-xs text-muted-foreground">
                        File akan disimpan ke cloud storage. Format yang didukung: PDF, DOC, DOCX.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
