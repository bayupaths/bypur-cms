import type { InertiaLinkProps } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, readonly } from 'vue';

type Href = NonNullable<InertiaLinkProps['href']>;

function resolveHref(href: Href): string | null {
    if (typeof href === 'string') return href;
    if (typeof href === 'object' && 'url' in href) return href.url;
    return null;
}

function toPathname(href: Href): string | null {
    const resolved = resolveHref(href);
    if (!resolved) return null;

    try {
        return new URL(resolved, window?.location.origin).pathname;
    } catch {
        return null;
    }
}

export function useActiveUrl() {
    const page = usePage();

    const currentUrl = computed(
        () => new URL(page.url, window?.location.origin).pathname,
    );

    function isActive(href: Href, exact = false): boolean {
        const current = currentUrl.value;
        const pathname = toPathname(href);

        if (!pathname) return false;
        if (pathname === '/') return current === '/';
        if (exact) return current === pathname;

        return current === pathname || current.startsWith(pathname + '/');
    }

    function isExactActive(href: Href): boolean {
        return isActive(href, true);
    }

    function activeClass(href: Href, styles: string, exact = false): string {
        return isActive(href, exact) ? styles : '';
    }

    return {
        currentUrl: readonly(currentUrl),
        isActive,
        isExactActive,
        activeClass,
    };
}
