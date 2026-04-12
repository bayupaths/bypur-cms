export interface User {
    id: number;
    name: string;
    username?: string;
    email: string;
    email_verified_at?: string;

    // Profile
    avatar?: string;
    phone?: string;
    gender?: 'male' | 'female';
    birth_date?: string;
    bio?: string;

    // Address
    address?: string;
    city?: string;
    country?: string;
    postal_code?: string;

    // Social
    website?: string;
    github?: string;
    linkedin?: string;
    twitter?: string;
    instagram?: string;

    // Status
    is_active: boolean;
    is_superadmin: boolean;

    // Security
    last_login_at?: string;
    last_login_ip?: string;
    login_attempts: number;
    locked_until?: string;

    // Misc
    meta?: Record<string, unknown>;

    created_at: string;
    updated_at: string;
    deleted_at?: string;
}

export interface Auth {
    user: User;
}

export interface Role {
    id: number;
    name: string;
    display_name: string | null;
    guard_name: string;
    level: number | null | undefined;
    is_system: boolean;
    description: string | null;
    permissions: string[];
    permission_ids: number[];
    users_count: number;
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    display_name: string | null;
    group: string | null;
    guard_name: string;
    description: string | null;
    created_at: string;
    updated_at: string;
}
