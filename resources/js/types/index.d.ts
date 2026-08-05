export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    phone?: string | null;
    avatar_url?: string | null;
    gender?: string | null;
    birth_date?: string | null;
    role?: string;
    is_active?: boolean;
    skill_level?: string | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
