import { ref } from 'vue';

export interface ConfirmDialogOptions {
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}

// Shared state (module-level)
const isOpen = ref(false);
const dialogTitle = ref('Are you sure?');
const dialogDescription = ref('This action cannot be undone.');
const confirmText = ref('Continue');
const cancelText = ref('Cancel');
const variant = ref<'default' | 'destructive'>('default');
let resolveCallback: ((value: boolean) => void) | null = null;

export function useConfirmDialog() {
    function openDialog(options: ConfirmDialogOptions = {}): Promise<boolean> {
        dialogTitle.value = options.title ?? 'Are you sure?';
        dialogDescription.value = options.description ?? 'This action cannot be undone.';
        confirmText.value = options.confirmText ?? 'Continue';
        cancelText.value = options.cancelText ?? 'Cancel';
        variant.value = options.variant ?? 'default';
        isOpen.value = true;

        return new Promise((resolve) => {
            resolveCallback = resolve;
        });
    }

    function confirm() {
        isOpen.value = false;
        resolveCallback?.(true);
        resolveCallback = null;
    }

    function cancel() {
        isOpen.value = false;
        resolveCallback?.(false);
        resolveCallback = null;
    }

    return {
        isOpen,
        dialogTitle,
        dialogDescription,
        confirmText,
        cancelText,
        variant,
        openDialog,
        confirm,
        cancel,
    };
}
