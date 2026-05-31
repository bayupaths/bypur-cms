<script setup lang="ts">
import { Info, Mail, MapPin, Phone } from 'lucide-vue-next';
import InputError from '@/components/common/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';


const form = defineModel<any>('form', { required: true });

</script>

<template>
    <div class="space-y-4">

        <!-- ── Kontak & Lokasi ─────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">

            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <Mail class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Kontak & Lokasi</span>
            </div>

            <div class="space-y-5 p-5">

                <!-- Row 1: Telepon + Email Publik -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <!-- Telepon -->
                    <div class="space-y-1.5">
                        <Label for="phone" class="text-xs font-medium">Nomor Telepon</Label>
                        <div class="relative">
                            <Phone class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="phone"
                                v-model="form.phone"
                                placeholder="+62 812 3456 7890"
                                :disabled="form.processing"
                                class="pl-9"
                            />
                        </div>
                        <InputError :message="form.errors.phone" />
                    </div>

                    <!-- Email Publik -->
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-1.5">
                            <Label for="email" class="text-xs font-medium">Email Publik</Label>
                            <TooltipProvider>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Info class="h-3.5 w-3.5 cursor-help text-muted-foreground" />
                                    </TooltipTrigger>
                                    <TooltipContent side="top" class="max-w-56 text-xs">
                                        Email ini tampil di portofolio publik, bukan email login akun Anda
                                    </TooltipContent>
                                </Tooltip>
                            </TooltipProvider>
                        </div>
                        <div class="relative">
                            <Mail class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="hello@example.com"
                                :disabled="form.processing"
                                class="pl-9"
                            />
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>
                </div>

                <Separator />

                <!-- Row 2: Lokasi -->
                <div class="space-y-1.5">
                    <Label for="location" class="text-xs font-medium">Lokasi Publik</Label>
                    <div class="relative">
                        <MapPin class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="location"
                            v-model="form.location"
                            placeholder="Jakarta, Indonesia"
                            :disabled="form.processing"
                            class="pl-9"
                        />
                    </div>
                    <p class="text-xs text-muted-foreground">Tampil di halaman portofolio, bisa berbeda dengan alamat terdaftar</p>
                    <InputError :message="form.errors.location" />
                </div>
            </div>
        </div>

        <!-- ── Alamat Terdaftar ────────────────────────────────────────── -->
        <div class="rounded-md border bg-card text-card-foreground">

            <div class="flex items-center gap-2 border-b px-5 py-3.5">
                <MapPin class="h-3.5 w-3.5 text-muted-foreground" />
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Alamat Terdaftar</span>
            </div>

            <div class="space-y-5 p-5">

                <!-- Jalan / Alamat lengkap -->
                <div class="space-y-1.5">
                    <Label for="address" class="text-xs font-medium">Alamat</Label>
                    <div class="relative">
                        <MapPin class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="address"
                            v-model="form.address"
                            placeholder="Jl. Sudirman No. 1"
                            :disabled="form.processing"
                            class="pl-9"
                        />
                    </div>
                    <InputError :message="form.errors.address" />
                </div>

                <Separator />

                <!-- Kota + Negara + Kode Pos -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="space-y-1.5">
                        <Label for="city" class="text-xs font-medium">Kota</Label>
                        <Input
                            id="city"
                            v-model="form.city"
                            placeholder="Jakarta"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.city" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="country" class="text-xs font-medium">Negara</Label>
                        <Input
                            id="country"
                            v-model="form.country"
                            placeholder="Indonesia"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.country" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="postal_code" class="text-xs font-medium">Kode Pos</Label>
                        <Input
                            id="postal_code"
                            v-model="form.postal_code"
                            placeholder="10110"
                            :disabled="form.processing"
                        />
                        <InputError :message="form.errors.postal_code" />
                    </div>
                </div>

                <p class="text-xs text-muted-foreground">
                    Alamat ini tidak tampil di portofolio publik, hanya digunakan untuk referensi internal.
                </p>
            </div>
        </div>
    </div>
</template>
