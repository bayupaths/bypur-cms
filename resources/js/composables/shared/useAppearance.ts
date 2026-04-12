import { computed, onMounted, ref } from "vue";

export type Appearance = "light" | "dark" | "system";
export type ResolvedAppearance = "light" | "dark";

const STORAGE_KEY = "appearance";
const COOKIE_NAME = "appearance";

const appearance = ref<Appearance>("system");

// --- Helpers ---

const prefersDark = (): boolean => {
    if (typeof window === "undefined") return false;
    return window.matchMedia("(prefers-color-scheme: dark)").matches;
};

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === "undefined") return;
    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getCookie = (name: string): string | null => {
    if (typeof document === "undefined") return null;
    const cookies = document.cookie.split(";");
    for (const cookie of cookies) {
        const [cookieName, cookieValue] = cookie.trim().split("=");
        if (cookieName === name) return cookieValue;
    }
    return null;
};

const getStoredAppearance = (): Appearance | null => {
    if (typeof window === "undefined") return null;

    let stored = localStorage.getItem(STORAGE_KEY) ?? getCookie(COOKIE_NAME);

    if (stored === "light" || stored === "dark" || stored === "system") {
        // Sync localStorage and cookie if out of sync
        if (localStorage.getItem(STORAGE_KEY) !== stored) {
            localStorage.setItem(STORAGE_KEY, stored);
        }
        if (getCookie(COOKIE_NAME) !== stored) {
            setCookie(COOKIE_NAME, stored);
        }
        return stored;
    }

    return null;
};

// --- Public API ---

export function updateTheme(value: Appearance) {
    if (typeof window === "undefined") return;

    if (value === "system") {
        const isDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        document.documentElement.classList.toggle("dark", isDark);
    } else {
        document.documentElement.classList.toggle("dark", value === "dark");
    }
}

export function initializeTheme() {
    if (typeof window === "undefined") return;

    const saved = getStoredAppearance();
    const theme = saved ?? "system";

    updateTheme(theme);

    window
        .matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", () => {
            if (appearance.value === "system") updateTheme("system");
        });
}

export function useAppearance() {
    onMounted(() => {
        const saved = getStoredAppearance();
        if (saved) appearance.value = saved;
        updateTheme(appearance.value);
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() => {
        if (appearance.value === "system") {
            return prefersDark() ? "dark" : "light";
        }
        return appearance.value;
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem(STORAGE_KEY, value);
        setCookie(COOKIE_NAME, value);
        updateTheme(value);
    }

    return { appearance, resolvedAppearance, updateAppearance };
}

