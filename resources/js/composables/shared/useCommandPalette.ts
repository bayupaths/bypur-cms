import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

export interface CommandItem {
    label: string;
    icon: string;
    route: string;
}

export function useCommandPalette(items: CommandItem[]) {
    const isOpen = ref(false);
    const query = ref('');
    const selectedIndex = ref(0);

    const filtered = computed<CommandItem[]>(() => {
        const q = query.value.toLowerCase().trim();
        return q ? items.filter(item => item.label.toLowerCase().includes(q)) : items;
    });

    watch(query, () => {
        selectedIndex.value = 0;
    });

    function open(): void {
        isOpen.value = true;
        query.value = '';
        selectedIndex.value = 0;
    }

    function close(): void {
        isOpen.value = false;
    }

    function selectPrev(): void {
        if (!filtered.value.length) return;
        selectedIndex.value = (selectedIndex.value - 1 + filtered.value.length) % filtered.value.length;
    }

    function selectNext(): void {
        if (!filtered.value.length) return;
        selectedIndex.value = (selectedIndex.value + 1) % filtered.value.length;
    }

    function confirmSelection(): void {
        const item = filtered.value[selectedIndex.value];
        if (item) {
            router.visit(item.route);
            close();
        }
    }

    function selectItem(item: CommandItem): void {
        router.visit(item.route);
        close();
    }

    function onKeydown(event: KeyboardEvent): void {
        if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
            event.preventDefault();
            isOpen.value ? close() : open();
        }
    }

    onMounted(() => window.addEventListener('keydown', onKeydown));
    onUnmounted(() => window.removeEventListener('keydown', onKeydown));

    return { isOpen, query, filtered, selectedIndex, open, close, selectPrev, selectNext, confirmSelection, selectItem };
}
