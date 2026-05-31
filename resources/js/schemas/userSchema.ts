import { z } from 'zod';

export const storeUserSchema = z
    .object({
        name: z.string().min(1, 'Nama wajib diisi').max(255, 'Nama maksimal 255 karakter'),
        username: z
            .string()
            .max(100, 'Username maksimal 100 karakter')
            .regex(/^[a-z0-9_.]*$/, 'Hanya huruf kecil, angka, titik, underscore')
            .optional()
            .or(z.literal('')),
        email: z.string().email('Email tidak valid').max(255, 'Email maksimal 255 karakter'),
        password: z.string().min(8, 'Password minimal 8 karakter'),
        password_confirmation: z.string(),
        // Status
        is_active: z.boolean(),
        is_superadmin: z.boolean(),
        // Roles
        roles: z.array(z.number()),
    })
    .refine((d) => d.password === d.password_confirmation, {
        message: 'Konfirmasi password tidak cocok',
        path: ['password_confirmation'],
    });

export const updateUserSchema = z
    .object({
        name: z.string().min(1, 'Nama wajib diisi').max(255).optional(),
        username: z
            .string()
            .max(100, 'Username maksimal 100 karakter')
            .regex(/^[a-z0-9_.]*$/, 'Hanya huruf kecil, angka, titik, underscore')
            .optional()
            .or(z.literal('')),
        email: z.string().email('Email tidak valid').max(255).optional(),
        password: z.string().min(8, 'Password minimal 8 karakter').optional().or(z.literal('')),
        password_confirmation: z.string().optional().or(z.literal('')),
        // Status
        is_active: z.boolean(),
        is_superadmin: z.boolean(),
        // Roles
        roles: z.array(z.number()),
    })
    .refine((d) => !d.password || d.password === d.password_confirmation, {
        message: 'Konfirmasi password tidak cocok',
        path: ['password_confirmation'],
    });

export type StoreUserForm = z.infer<typeof storeUserSchema>;
export type UpdateUserForm = z.infer<typeof updateUserSchema>;
