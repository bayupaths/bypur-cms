import { z } from 'zod';

const JOB_TYPES = ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship', 'Remote'] as const;

export const experienceSchema = z
    .object({
        company: z
            .string()
            .min(1, 'Perusahaan wajib diisi')
            .max(255, 'Perusahaan maksimal 255 karakter'),

        position: z
            .string()
            .min(1, 'Posisi wajib diisi')
            .max(255, 'Posisi maksimal 255 karakter'),

        location: z
            .string()
            .max(255, 'Lokasi maksimal 255 karakter')
            .optional()
            .or(z.literal('')),

        type: z
            .enum(JOB_TYPES)
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

        is_current: z.coerce.boolean(),

        description: z
            .string()
            .max(5000, 'Deskripsi maksimal 5.000 karakter')
            .optional()
            .or(z.literal('')),

        tech_stack: z
            .array(
                z.string().min(1).max(50, 'Setiap teknologi maksimal 50 karakter'),
            )
            .max(20, 'Maksimal 20 teknologi')
            .optional(),
    })
    .superRefine((data, ctx) => {
        // ended_at wajib jika tidak sedang aktif
        if (!data.is_current && !data.ended_at) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['ended_at'],
                message: 'Tanggal selesai wajib diisi jika tidak sedang bekerja di sini',
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

export type ExperienceForm = z.infer<typeof experienceSchema>;
