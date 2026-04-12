import { route } from 'ziggy-js';

export const routes = {
    // Dashboard
    dashboard: () => route('dashboard'),

    // Auth
    login: () => route('login'),
    logout: () => route('logout'),
    register: () => route('register'),
    forgotPassword: () => route('password.request'),
    resetPassword: (token: string) => route('password.reset', { token }),
    confirmPassword: () => route('password.confirm'),
} as const;
