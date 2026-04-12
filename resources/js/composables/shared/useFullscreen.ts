import { ref } from 'vue';

export function useFullscreen(targetRef?: { value: HTMLElement | null }) {
    const isFullscreen = ref(false);

    async function enter() {
        const el = targetRef?.value ?? document.documentElement;
        await el.requestFullscreen?.();
        isFullscreen.value = true;
    }

    async function exit() {
        await document.exitFullscreen?.();
        isFullscreen.value = false;
    }

    async function toggle() {
        isFullscreen.value ? await exit() : await enter();
    }

    return { isFullscreen, enter, exit, toggle };
}
