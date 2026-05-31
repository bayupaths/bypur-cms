import { z } from 'zod';

export const educationSchema = z
    .object({
        institution: z
            .string()
            .min(1, 'Institusi wajib diisi')
            .max(255, 'Institusi maksimal 255 karakter'),

        degree: z
            .string()
            .min(1, 'Gelar wajib diisi')
            .max(255, 'Gelar maksimal 255 karakter'),

        field: z
            .string()
            .max(255, 'Bidang studi maksimal 255 karakter')
            .optional()
            .or(z.literal('')),

        started_at: z
            .string()
            .min(1, 'Tanggal mulai wajib diisi')
            .regex(/^\d{4}-\d{2}-\d{2}$/, 'Format tanggal tidak valid'),

        ended_at: z
            .string()
            .regex(/^\d{4}-\d{2}-\d{2}$/, 'Format tanggal tidak valid')
            .optional()
            .or(z.literal('')),

        is_current: z.boolean(),

        description: z
            .string()
            .max(5000, 'Deskripsi maksimal 5.000 karakter')
            .optional()
            .or(z.literal('')),
    })
    .superRefine((data, ctx) => {
        // ended_at wajib jika tidak sedang aktif
        if (!data.is_current && !data.ended_at) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['ended_at'],
                message: 'Tanggal selesai wajib diisi jika tidak sedang berkuliah di sini',
            });
        }

        // ended_at harus setelah started_at
        if (data.ended_at && data.started_at && data.ended_at < data.started_at) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['ended_at'],
                message: 'Tanggal selesai harus setelah tanggal mulai',
            });
        }
    });

export type EducationForm = z.infer<typeof educationSchema>;
