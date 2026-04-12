import { z } from 'zod';

// ── Menu Group ────────────────────────────────────────────────────────────────

export const menuGroupSchema = z.object({
    name: z
        .string()
        .min(1, 'Nama wajib diisi')
        .max(100, 'Nama maksimal 100 karakter')
        .regex(/^[a-z0-9_-]+$/, 'Nama hanya boleh huruf kecil, angka, underscore, dan strip'),
    display_name: z.string().max(100, 'Display name maksimal 100 karakter').optional().or(z.literal('')),
    description: z.string().max(500, 'Deskripsi maksimal 500 karakter').optional().or(z.literal('')),
    is_active: z.boolean(),
});

export type MenuGroupForm = z.infer<typeof menuGroupSchema>;

// ── Menu Item ─────────────────────────────────────────────────────────────────

export const menuSchema = z.object({
    group_id: z
        .number({ error: 'Group wajib dipilih' })
        .positive('Group wajib dipilih'),
    parent_id: z.number().nullable(),
    title: z
        .string()
        .min(1, 'Title wajib diisi')
        .max(100, 'Title maksimal 100 karakter'),
    slug: z
        .string()
        .max(100, 'Slug maksimal 100 karakter')
        .regex(/^[a-z0-9-]*$/, 'Slug hanya boleh huruf kecil, angka, dan strip')
        .optional()
        .or(z.literal('')),
    url: z.string().max(255, 'URL maksimal 255 karakter').optional().or(z.literal('')),
    is_route: z.boolean(),
    icon: z.string().max(100, 'Icon maksimal 100 karakter').optional().or(z.literal('')),
    badge: z.string().max(50, 'Badge maksimal 50 karakter').optional().or(z.literal('')),
    badge_variant: z.enum(['default', 'secondary', 'destructive', 'outline']),
    target: z.enum(['_self', '_blank']),
    order: z.number().int().min(0, 'Order tidak boleh negatif'),
    is_active: z.boolean(),
    is_divider: z.boolean(),
    roles: z.array(z.number()),
    permissions: z.array(z.number()),
});

export type MenuForm = z.infer<typeof menuSchema>;
