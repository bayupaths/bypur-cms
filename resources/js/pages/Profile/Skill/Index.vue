<script setup lang="ts">
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Pencil, Plus, Sparkles, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/common/Heading.vue';
import ConfirmDialog from '@/components/dialogs/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { useConfirmDialog } from '@/composables/shared/useConfirmDialog';
import { SKILL_CATEGORIES } from '@/schemas/skillSchema';
import type { BreadcrumbItem } from '@/types';
import SkillDialog from './partials/SkillDialog.vue';
import SkillIcon from './partials/SkillIcon.vue';

const CATEGORY_LABELS = Object.fromEntries(SKILL_CATEGORIES.map(c => [c.value, c.label])) as Record<string, string>;

function categoryLabel(cat: string | null): string {
    return cat ? (CATEGORY_LABELS[cat] ?? cat) : 'Lainnya';
}

export interface Skill {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    category: string | null;
    level: number | null;
    order: number;
}

const props = defineProps<{
    skills: {
        data: Skill[];
        meta: any;
        links: any;
    };
    filters: {
        search?: string;
        category?: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Skills', href: route('profile.skills.index') },
];

const dialogOpen = ref(false);
const selected   = ref<Skill | null>(null);
const { openDialog } = useConfirmDialog();

function openCreate() {
    selected.value = null;
    dialogOpen.value = true;
}

function openEdit(item: Skill) {
    selected.value = item;
    dialogOpen.value = true;
}

async function handleDelete(item: Skill) {
    const ok = await openDialog({
        title: 'Hapus Skill',
        description: `Skill "${item.name}" akan dihapus permanen. Lanjutkan?`,
        confirmText: 'Hapus',
        cancelText: 'Batal',
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(route('profile.skills.destroy', item.id), { preserveScroll: true });
}

// Group skills by category
const grouped = computed<Record<string, Skill[]>>(() => {
    const result: Record<string, Skill[]> = {};
    for (const skill of props.skills.data) {
        const cat = categoryLabel(skill.category);
        if (!result[cat]) result[cat] = [];
        result[cat].push(skill);
    }
    return result;
});

function levelLabel(level: number | null): string {
    if (level === null) return '';
    if (level >= 90) return 'Expert';
    if (level >= 70) return 'Advanced';
    if (level >= 50) return 'Intermediate';
    if (level >= 30) return 'Beginner';
    return 'Learning';
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4">

            <!-- Header -->
            <Heading title="Skills" description="Daftar kemampuan teknis Anda">
                <Button size="sm" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah
                </Button>
            </Heading>

            <!-- Empty state -->
            <div
                v-if="skills.data.length === 0"
                class="flex flex-1 flex-col items-center justify-center gap-3 rounded-md border border-dashed py-16 text-center"
            >
                <Sparkles class="h-10 w-10 text-muted-foreground/40" />
                <p class="text-sm text-muted-foreground">Belum ada skill ditambahkan.</p>
                <Button size="sm" variant="outline" @click="openCreate">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Skill
                </Button>
            </div>

            <!-- Grouped skill cards -->
            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="(items, category) in grouped"
                    :key="category"
                    class="rounded-md border bg-card p-4 text-card-foreground shadow-xs transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <!-- Category header -->
                    <div class="mb-3 flex items-center gap-2 border-b pb-2.5">
                        <div class="h-3.5 w-1 rounded-full bg-primary/60" />
                        <span class="text-sm font-semibold tracking-tight">{{ category }}</span>
                        <span class="ml-auto text-xs text-muted-foreground">{{ items.length }} skill</span>
                    </div>

                    <!-- Skills list -->
                    <div class="space-y-1.5">
                        <div
                            v-for="skill in items"
                            :key="skill.id"
                            class="group flex items-center gap-3 rounded-md px-1 py-1.5 transition-colors duration-150 hover:bg-muted/50"
                        >
                            <!-- Icon -->
                            <SkillIcon :icon="skill.icon" :name="skill.name" />

                            <!-- Name + level -->
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="truncate text-sm font-medium">{{ skill.name }}</span>
                                    <span v-if="skill.level !== null" class="shrink-0 text-xs tabular-nums text-muted-foreground">
                                        {{ skill.level }}%
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Progress
                                        v-if="skill.level !== null"
                                        :model-value="skill.level"
                                        class="h-1.5 flex-1"
                                    />
                                    <span v-if="skill.level !== null" class="shrink-0 text-[10px] text-muted-foreground">
                                        {{ levelLabel(skill.level) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex shrink-0 gap-0.5 opacity-0 transition-all duration-150 group-hover:opacity-100">
                                <Button size="icon" variant="ghost" class="h-6 w-6 rounded-md" @click="openEdit(skill)">
                                    <Pencil class="h-3 w-3" />
                                </Button>
                                <Button size="icon" variant="ghost" class="h-6 w-6 rounded-md text-destructive hover:bg-destructive/10 hover:text-destructive" @click="handleDelete(skill)">
                                    <Trash2 class="h-3 w-3" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <SkillDialog
            v-model:open="dialogOpen"
            :skill="selected"
        />
        <ConfirmDialog />
    </AppLayout>
</template>
