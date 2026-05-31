export interface Profile {
    id: number;
    user_id: number;
    name: string;
    nickname: string | null;
    tagline: string | null;
    taglines: string[] | null;
    avatar: string | null;
    phone: string | null;
    email: string | null;
    location: string | null;
    bio: string | null;
    resume_url: string | null;
    website_url: string | null;
    is_available: boolean;
    // Personal
    gender: 'male' | 'female' | null;
    birth_date: string | null;
    // Address
    address: string | null;
    city: string | null;
    country: string | null;
    postal_code: string | null;
    // Social
    socials: { key: string; url: string }[] | null;
}
