import { computed } from 'vue';

export function usePublicUrl() {
    const publicUrl = computed<string>(
        () => (import.meta.env.VITE_PUBLIC_URL as string | undefined) ?? 'http://localhost:3000',
    );

    function openPublicSite(): void {
        window.open(publicUrl.value, '_blank', 'noopener,noreferrer');
    }

    return { publicUrl, openPublicSite };
}
