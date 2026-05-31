<script setup lang="ts">
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { AlignLeft, AtSign, Globe, Loader2, Mail, Pencil, User } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import Heading from '@/components/common/Heading.vue';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import ProfileSidebar from './ProfileSidebar.vue';
import IdentityTab from './tabs/IdentityTab.vue';
import ContactTab from './tabs/ContactTab.vue';
import BioTab from './tabs/BioTab.vue';
import SocialTab from './tabs/SocialTab.vue';
import AccountTab from './tabs/AccountTab.vue';
import type { BreadcrumbItem, Profile, User as UserType } from '@/types';
import { updateProfileSchema } from '@/schemas/profileSchema';

const props = defineProps<{
    profile: Profile | null;
    authUser: UserType;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Profil Saya', href: route('profile.show') },
];

const activeTab = ref('identity');
const avatarPreview = ref<string | null>(props.profile?.avatar ?? null);
const avatarFile = ref<File | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm<ReturnType<typeof updateProfileSchema.parse>>({
    name: props.profile?.name || props.authUser.name || '',
    nickname: props.profile?.nickname ?? '',
    tagline: props.profile?.tagline ?? '',
    taglines: props.profile?.taglines ?? [],
    phone: props.profile?.phone ?? '',
    email: props.profile?.email ?? '',
    location: props.profile?.location ?? '',
    bio: props.profile?.bio ?? '',
    resume_url: props.profile?.resume_url ?? '',
    resume_file: null as File | null,
    is_available: props.profile?.is_available ?? false,
    avatar: null as File | null,
    gender: props.profile?.gender ?? '',
    birth_date: props.profile?.birth_date ?? '',
    address: props.profile?.address ?? '',
    city: props.profile?.city ?? '',
    country: props.profile?.country ?? '',
    postal_code: props.profile?.postal_code ?? '',
    website_url: props.profile?.website_url ?? '',
    socials: props.profile?.socials ?? [],
});

const loginAttemptsPercent = computed(() =>
    Math.min(((props.authUser.login_attempts ?? 0) / 5) * 100, 100)
);

const cityCountryLine = computed(() =>
    [props.profile?.city, props.profile?.country, props.profile?.postal_code].filter(Boolean).join(', ')
);

const completionFields = computed(() => [
    { label: 'Nama', filled: !!form.name, weight: 15 },
    { label: 'Tagline', filled: !!form.tagline, weight: 10 },
    { label: 'Avatar', filled: !!avatarPreview.value, weight: 15 },
    { label: 'Bio', filled: !!form.bio, weight: 20 },
    { label: 'Telepon', filled: !!form.phone, weight: 10 },
    { label: 'Email Publik', filled: !!form.email, weight: 10 },
    { label: 'Lokasi', filled: !!form.location, weight: 10 },
    { label: 'Resume', filled: !!form.resume_url, weight: 10 },
]);

const completionPercent = computed(() =>
    completionFields.value.reduce((acc, f) => acc + (f.filled ? f.weight : 0), 0)
);

const completionLabel = computed(() => {
    if (completionPercent.value >= 90) return 'Lengkap';
    if (completionPercent.value >= 60) return 'Hampir lengkap';
    if (completionPercent.value >= 30) return 'Sedang diisi';
    return 'Baru dimulai';
});

const completionColor = computed(() => {
    if (completionPercent.value >= 90) return 'text-emerald-600 dark:text-emerald-400';
    if (completionPercent.value >= 60) return 'text-blue-600 dark:text-blue-400';
    if (completionPercent.value >= 30) return 'text-amber-600 dark:text-amber-400';
    return 'text-rose-600 dark:text-rose-400';
});

function formatDate(value?: string | null): string {
    if (!value) return '–';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

function formatDateTime(value?: string | null): string {
    if (!value) return '–';
    return new Date(value).toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
    });
}

function triggerAvatarUpload() { fileInputRef.value?.click(); }

function onAvatarChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    avatarFile.value = file;
    form.avatar = file;
    const reader = new FileReader();
    reader.onload = (e) => { avatarPreview.value = e.target?.result as string; };
    reader.readAsDataURL(file);
}

function removeAvatar() {
    avatarPreview.value = null;
    avatarFile.value = null;
    form.avatar = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
}

const TAB_FIELDS: Record<string, string[]> = {
    identity: ['name', 'nickname', 'tagline', 'taglines', 'gender', 'birth_date', 'is_available'],
    contact:  ['phone', 'email', 'location', 'address', 'city', 'country', 'postal_code'],
    bio:      ['bio', 'resume_url', 'resume_file', 'avatar'],
    social:   ['website_url', 'socials'],
};

function switchToErrorTab(errors: Record<string, string | undefined>) {
    for (const [tab, fields] of Object.entries(TAB_FIELDS)) {
        if (fields.some((f) => errors[f])) {
            activeTab.value = tab;
            return;
        }
    }
}

function submit() {
    // Defensive: ensure name is never empty before validation
    if (!form.name && props.authUser.name) {
        form.name = props.authUser.name;
    }

    const result = updateProfileSchema.safeParse({
        ...form.data(),
        resume_file: form.resume_file ?? undefined,
        avatar: form.avatar ?? undefined,
    });

    if (!result.success) {
        const fieldErrors = result.error.flatten().fieldErrors;
        form.clearErrors();
        const mapped: Record<string, string> = {};
        for (const [field, messages] of Object.entries(fieldErrors)) {
            if (messages?.[0]) {
                form.setError(field as keyof ReturnType<typeof updateProfileSchema.parse>, messages[0]);
                mapped[field] = messages[0];
            }
        }
        switchToErrorTab(mapped);
        return;
    }

    form.transform((data) => ({ ...data, _method: 'put' })).post(route('profile.update'), {
        forceFormData: true,
        onError: (errors) => switchToErrorTab(errors),
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6">

            <!-- Page Header -->
            <Heading title="Profil Saya" description="Kelola informasi akun dan tampilan publik portofolio Anda">
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" :as="'a'" :href="route('profile.show')" target="_blank">
                        Lihat Portofolio
                    </Button>
                    <Button size="sm" :disabled="form.processing" @click="submit">
                        <Loader2 v-if="form.processing" class="mr-1.5 h-4 w-4 animate-spin" />
                        <Pencil v-else class="mr-1.5 h-4 w-4" />
                        Simpan Perubahan
                    </Button>
                </div>
            </Heading>

            <!-- Hidden file input for avatar -->
            <input ref="fileInputRef" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[300px_1fr]">

                <!-- Sidebar -->
                <ProfileSidebar
                    :auth-user="authUser"
                    :avatar-preview="avatarPreview"
                    :avatar-file="avatarFile"
                    :completion-fields="completionFields"
                    :completion-percent="completionPercent"
                    :completion-label="completionLabel"
                    :completion-color="completionColor"
                    :login-attempts-percent="loginAttemptsPercent"
                    :form="form"
                    :format-date-time="formatDateTime"
                    @trigger-avatar-upload="triggerAvatarUpload"
                    @remove-avatar="removeAvatar"
                />

                <!-- Tabs -->
                <div class="flex flex-col gap-4">
                    <Tabs v-model="activeTab" class="w-full">
                        <TabsList class="w-full justify-start rounded-md h-auto p-1 gap-0.5">
                            <TabsTrigger value="identity" class="gap-1.5 rounded-lg text-xs sm:text-sm">
                                <User class="h-3.5 w-3.5" /> Identitas
                            </TabsTrigger>
                            <TabsTrigger value="contact" class="gap-1.5 rounded-lg text-xs sm:text-sm">
                                <Mail class="h-3.5 w-3.5" /> Kontak
                            </TabsTrigger>
                            <TabsTrigger value="bio" class="gap-1.5 rounded-lg text-xs sm:text-sm">
                                <AlignLeft class="h-3.5 w-3.5" /> Bio & CV
                            </TabsTrigger>
                            <TabsTrigger value="social" class="gap-1.5 rounded-lg text-xs sm:text-sm">
                                <Globe class="h-3.5 w-3.5" /> Sosial
                            </TabsTrigger>
                            <TabsTrigger value="account" class="gap-1.5 rounded-lg text-xs sm:text-sm">
                                <AtSign class="h-3.5 w-3.5" /> Akun
                            </TabsTrigger>
                        </TabsList>

                        <TabsContent value="identity" class="mt-4">
                            <IdentityTab v-model:form="form" />
                        </TabsContent>

                        <TabsContent value="contact" class="mt-4">
                            <ContactTab v-model:form="form" />
                        </TabsContent>

                        <TabsContent value="bio" class="mt-4">
                            <BioTab v-model:form="form" />
                        </TabsContent>

                        <TabsContent value="social" class="mt-4">
                            <SocialTab v-model:form="form" />
                        </TabsContent>

                        <TabsContent value="account" class="mt-4">
                            <AccountTab :auth-user="authUser" :format-date-time="formatDateTime" />
                        </TabsContent>
                    </Tabs>

                    <!-- Submit (mobile) -->
                    <div class="flex justify-end lg:hidden">
                        <Button type="button" :disabled="form.processing" class="w-full sm:w-auto" @click="submit">
                            <Loader2 v-if="form.processing" class="mr-1.5 h-4 w-4 animate-spin" />
                            <Pencil v-else class="mr-1.5 h-4 w-4" />
                            Simpan Perubahan
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
