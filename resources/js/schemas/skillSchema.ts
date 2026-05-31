import { z } from 'zod';

export const SKILL_CATEGORIES = [
    { value: 'frontend', label: 'Frontend' },
    { value: 'backend',  label: 'Backend' },
    { value: 'tools',    label: 'Tooling' },
    { value: 'ai',       label: 'AI Toolbelt' },
    { value: 'other',    label: 'Practices / Other' },
] as const;

export type SkillCategory = typeof SKILL_CATEGORIES[number]['value'];

export const skillSchema = z.object({
    name: z
        .string()
        .min(1, 'Nama skill wajib diisi')
        .max(255, 'Nama skill maksimal 255 karakter'),

    slug: z
        .string()
        .min(1, 'Slug wajib diisi')
        .max(255, 'Slug maksimal 255 karakter')
        .regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/, 'Slug hanya boleh huruf kecil, angka, dan tanda hubung'),

    icon: z
        .string()
        .max(255, 'Icon maksimal 255 karakter')
        .optional()
        .or(z.literal('')),

    category: z
        .enum(['frontend', 'backend', 'tools', 'ai', 'other'] as const)
        .optional()
        .or(z.literal('')),

    level: z
        .number()
        .int('Level harus berupa bilangan bulat')
        .min(0, 'Level minimal 0')
        .max(100, 'Level maksimal 100')
        .nullable()
        .optional(),
});

export type SkillForm = z.infer<typeof skillSchema>;
