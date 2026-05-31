<script setup lang="ts">
import { computed } from 'vue';
import { ExternalLink, Globe, Link, Plus, Share2, Trash2 } from 'lucide-vue-next';
import InputError from '@/components/common/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = defineModel<any>('form', { required: true });

const PLATFORM_SUGGESTIONS = [
    { key: 'github',    label: 'GitHub',      placeholder: 'https://github.com/username' },
    { key: 'linkedin',  label: 'LinkedIn',     placeholder: 'https://linkedin.com/in/username' },
    { key: 'twitter',   label: 'Twitter / X',  placeholder: 'https://x.com/username' },
    { key: 'instagram', label: 'Instagram',    placeholder: 'https://instagram.com/username' },
    { key: 'youtube',   label: 'YouTube',      placeholder: 'https://youtube.com/@channel' },
    { key: 'dribbble',  label: 'Dribbble',     placeholder: 'https://dribbble.com/username' },
];

function addSocial() {
    form.value.socials = [...(form.value.socials ?? []), { key: '', url: '' }];
}

function removeSocial(index: number) {
    form.value.socials = form.value.socials.filter((_: any, i: number) => i !== index);
}

const activeLinks = computed(() => {
    const links: { label: string; href: string }[] = [];
    if (form.value.website_url)
        links.push({ label: 'Website', href: form.value.website_url });
    for (const s of (form.value.socials ?? [])) {
        if (s.url) links.push({ label: s.key || s.url, href: s.url });
    }
    return links;
});
</script>

<template>
    <div class="space-y-4">

        <!-- ── Website Portfolio ───────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">
            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <Globe class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Website Portfolio</span>
            </div>
            <div class="p-5">
                <div class="space-y-1.5">
                    <Label for="website_url" class="text-xs font-medium">URL Portfolio Publik</Label>
                    <div class="relative">
                        <Globe class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="website_url"
                            v-model="form.website_url"
                            type="url"
                            placeholder="https://yourportfolio.dev"
                            :disabled="form.processing"
                            class="pl-9"
                        />
                    </div>
                    <InputError :message="form.errors.website_url" />
                    <p class="text-xs text-muted-foreground">URL situs portofolio yang dapat diakses publik</p>
                </div>
            </div>
        </div>

        <!-- ── Profil Sosial ───────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">
            <div class="flex items-center justify-between border-b px-5 py-3.5">
                <div class="flex items-center gap-2">
                    <Share2 class="h-3.5 w-3.5 text-muted-foreground" />
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Profil Sosial</span>
                    <span class="rounded-full bg-muted px-2 py-0.5 text-xs tabular-nums text-muted-foreground">
                        {{ (form.socials ?? []).length }}
                    </span>
                </div>
                <Button type="button" variant="outline" size="sm" class="h-7 gap-1.5 text-xs" :disabled="form.processing" @click="addSocial">
                    <Plus class="h-3 w-3" /> Tambah
                </Button>
            </div>
            <div class="p-5">
                <!-- Empty state -->
                <div v-if="!(form.socials ?? []).length" class="flex flex-col items-center gap-2 py-6 text-center">
                    <Link class="h-8 w-8 text-muted-foreground/40" />
                    <p class="text-sm text-muted-foreground">Belum ada tautan sosial</p>
                    <p class="text-xs text-muted-foreground/70">Klik "Tambah" untuk menambahkan platform</p>
                </div>

                <!-- Social rows -->
                <div v-else class="space-y-3">
                    <div
                        v-for="(s, i) in form.socials"
                        :key="i"
                        class="grid grid-cols-[140px_1fr_auto] items-start gap-2"
                    >
                        <!-- Platform key (select + custom) -->
                        <div class="space-y-1">
                            <Label :for="`social-key-${i}`" class="text-xs font-medium">Platform</Label>
                            <select
                                :id="`social-key-${i}`"
                                v-model="s.key"
                                :disabled="form.processing"
                                class="h-9 w-full rounded-md border bg-background px-2.5 text-sm text-foreground outline-none ring-offset-background focus:ring-2 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Kustom…</option>
                                <option v-for="p in PLATFORM_SUGGESTIONS" :key="p.key" :value="p.key">
                                    {{ p.label }}
                                </option>
                            </select>
                        </div>

                        <!-- URL -->
                        <div class="space-y-1">
                            <Label :for="`social-url-${i}`" class="text-xs font-medium">URL</Label>
                            <Input
                                :id="`social-url-${i}`"
                                v-model="s.url"
                                type="url"
                                :placeholder="PLATFORM_SUGGESTIONS.find(p => p.key === s.key)?.placeholder ?? 'https://...'"
                                :disabled="form.processing"
                                :class="{ 'border-destructive': form.errors[`socials.${i}.url`] }"
                            />
                            <InputError :message="form.errors[`socials.${i}.url`]" />
                        </div>

                        <!-- Remove -->
                        <div class="pt-5">
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                class="h-9 w-9 text-muted-foreground hover:text-destructive"
                                :disabled="form.processing"
                                @click="removeSocial(Number(i))"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Pratinjau Tautan ────────────────────────────────────────── -->
        <div v-if="activeLinks.length" class="rounded-md border bg-card text-card-foreground">
            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <ExternalLink class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Pratinjau Tautan</span>
                <span class="ml-auto rounded-full bg-muted px-2 py-0.5 text-xs tabular-nums text-muted-foreground">
                    {{ activeLinks.length }}
                </span>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <a
                        v-for="link in activeLinks"
                        :key="link.label"
                        :href="link.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group flex items-center gap-2.5 rounded-md border px-3 py-2.5 text-xs text-foreground transition-colors hover:bg-muted"
                    >
                        <Link class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                        <span class="min-w-0 flex-1 truncate font-medium">{{ link.label }}</span>
                        <span class="min-w-0 flex-1 truncate text-primary">{{ link.href }}</span>
                        <ExternalLink class="h-3 w-3 shrink-0 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
                    </a>
                </div>
            </div>
        </div>

    </div>
</template>
