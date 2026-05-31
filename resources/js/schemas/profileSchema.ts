import { z } from 'zod';

// ── Reusable primitives ────────────────────────────────────────────────────

const optionalStr = (max: number, msg?: string) =>
    z.string().max(max, msg).optional().or(z.literal(''));

const optionalUrl = (max = 255) =>
    z
        .string()
        .url('URL tidak valid')
        .max(max)
        .optional()
        .or(z.literal(''));

// ── Schema ─────────────────────────────────────────────────────────────────

export const updateProfileSchema = z.object({
    // ── Identity ─────────────────────────────────────────────────────────
    name:         z.string().min(1, 'Nama wajib diisi').max(255, 'Nama maksimal 255 karakter'),
    nickname:     optionalStr(100, 'Nickname maksimal 100 karakter'),
    tagline:      optionalStr(255, 'Tagline maksimal 255 karakter'),
    taglines:     z
        .array(z.string().min(1, 'Tagline tidak boleh kosong').max(100, 'Tagline maksimal 100 karakter'))
        .max(5, 'Maksimal 5 tagline')
        .optional(),
    is_available: z.boolean(),

    // ── Contact ───────────────────────────────────────────────────────────
    phone:        optionalStr(50, 'Nomor telepon maksimal 50 karakter'),
    email:        z.string().email('Email tidak valid').max(255).optional().or(z.literal('')),
    location:     optionalStr(255, 'Lokasi maksimal 255 karakter'),

    // ── Address ───────────────────────────────────────────────────────────
    address:      optionalStr(255),
    city:         optionalStr(100),
    country:      optionalStr(100),
    postal_code:  optionalStr(20, 'Kode pos maksimal 20 karakter'),

    // ── Bio & Resume ──────────────────────────────────────────────────────
    bio:          optionalStr(1000, 'Bio maksimal 1.000 karakter'),
    resume_url:   optionalUrl(500),
    resume_file:  z
        .instanceof(File)
        .refine((f) => f.size <= 10 * 1024 * 1024, 'Ukuran file maksimal 10 MB')
        .refine(
            (f) => ['application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(f.type),
            'Format file harus PDF, DOC, atau DOCX',
        )
        .optional()
        .nullable(),

    // ── Avatar ────────────────────────────────────────────────────────────
    avatar:       z
        .instanceof(File)
        .refine((f) => f.size <= 2 * 1024 * 1024, 'Ukuran avatar maksimal 2 MB')
        .refine(
            (f) => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(f.type),
            'Format gambar harus JPG, PNG, WebP, atau GIF',
        )
        .optional()
        .nullable(),

    // ── Personal ─────────────────────────────────────────────────────────
    gender:       z.enum(['male', 'female']).optional().or(z.literal('')),
    birth_date:   z
        .string()
        .refine((v) => !v || !isNaN(Date.parse(v)), 'Tanggal lahir tidak valid')
        .optional()
        .or(z.literal('')),

    // ── Social ────────────────────────────────────────────────────────────
    website_url:  optionalUrl(500),
    socials:      z
        .array(
            z.object({
                key: z.string().min(1).max(50),
                url: z.string().url('URL tidak valid').max(500),
            }),
        )
        .optional(),
});

export type UpdateProfileForm = z.infer<typeof updateProfileSchema>;
