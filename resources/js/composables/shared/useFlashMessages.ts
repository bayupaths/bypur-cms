import { router, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from './useToast';

interface Flash {
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
}

/**
 * Composable untuk menampilkan flash messages dari Laravel sebagai toast.
 * Panggil sekali di root layout (AppSidebarLayout).
 */
export function useFlashMessages() {
    const page = usePage<{ flash: Flash }>();

    const showFlash = (flash: Flash | undefined | null) => {
        if (!flash) return;
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
        if (flash.warning) toast.warning(flash.warning);
        if (flash.info) toast.info(flash.info);
    };

    // Reactive watch — fires on every Inertia visit including initial load
    watch(
        () => page.props.flash as Flash,
        (flash) => showFlash(flash),
        { immediate: true, deep: true },
    );
}

