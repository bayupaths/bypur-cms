import { z } from 'zod';

export const certificateSchema = z
    .object({
        title: z
            .string()
            .min(1, 'Judul sertifikat wajib diisi')
            .max(255, 'Judul maksimal 255 karakter'),

        issuer: z
            .string()
            .min(1, 'Penerbit wajib diisi')
            .max(255, 'Penerbit maksimal 255 karakter'),

        issued_at: z
            .string()
            .min(1, 'Tanggal terbit wajib diisi')
            .regex(/^\d{4}-\d{2}-\d{2}$/, 'Format tanggal tidak valid'),

        expired_at: z
            .string()
            .regex(/^\d{4}-\d{2}-\d{2}$/, 'Format tanggal tidak valid')
            .optional()
            .or(z.literal('')),

        credential_url: z
            .string()
            .url('URL tidak valid')
            .max(500, 'URL maksimal 500 karakter')
            .optional()
            .or(z.literal('')),

        image: z
            .string()
            .max(500, 'URL gambar maksimal 500 karakter')
            .optional()
            .or(z.literal('')),
    })
    .superRefine((data, ctx) => {
        if (data.expired_at && data.issued_at && data.expired_at < data.issued_at) {
            ctx.addIssue({
                code: z.ZodIssueCode.custom,
                path: ['expired_at'],
                message: 'Tanggal kadaluarsa harus setelah tanggal terbit',
            });
        }
    });

export type CertificateForm = z.infer<typeof certificateSchema>;
