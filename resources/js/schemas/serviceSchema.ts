import { z } from 'zod';

export const serviceSchema = z.object({
    title: z
        .string()
        .min(1, 'Judul layanan wajib diisi')
        .max(255, 'Judul maksimal 255 karakter'),

    slug: z
        .string()
        .min(1, 'Slug wajib diisi')
        .max(255, 'Slug maksimal 255 karakter')
        .regex(/^[a-z0-9]+(?:-[a-z0-9]+)*$/, 'Slug hanya boleh huruf kecil, angka, dan tanda hubung'),

    description: z
        .string()
        .min(1, 'Deskripsi wajib diisi')
        .max(5000, 'Deskripsi maksimal 5.000 karakter'),

    icon: z
        .string()
        .max(255, 'Icon maksimal 255 karakter')
        .optional()
        .or(z.literal('')),

    price_from: z
        .number()
        .int('Harga harus berupa bilangan bulat')
        .min(0, 'Harga tidak boleh negatif')
        .nullable()
        .optional(),

    is_active: z.boolean(),
});

export type ServiceForm = z.infer<typeof serviceSchema>;
