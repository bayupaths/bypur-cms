<script setup lang="ts">
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface BreadcrumbEntry {
    title: string;
    href?: string;
}

const props = defineProps<{
    items?: BreadcrumbEntry[];
}>();

const page = usePage<{
    breadcrumbs?: BreadcrumbEntry[];
}>();

const breadcrumbs = computed<BreadcrumbEntry[]>(
    () => props.items ?? page.props.breadcrumbs ?? [],
);
</script>

<template>
    <Breadcrumb v-if="breadcrumbs.length">
        <BreadcrumbList class="min-w-0 flex-nowrap">
            <template v-for="(item, index) in breadcrumbs" :key="index">
                <!-- First item: always visible -->
                <template v-if="index === 0">
                    <BreadcrumbItem class="min-w-0">
                        <BreadcrumbLink
                            v-if="item.href"
                            :href="item.href"
                            :title="item.title"
                            class="inline-block max-w-25 truncate align-bottom sm:max-w-45"
                        >
                            {{ item.title }}
                        </BreadcrumbLink>
                        <BreadcrumbPage
                            v-else
                            :title="item.title"
                            class="inline-block max-w-25 truncate align-bottom sm:max-w-45"
                        >
                            {{ item.title }}
                        </BreadcrumbPage>
                    </BreadcrumbItem>
                    <!-- Ellipsis on mobile when there are more than 2 items -->
                    <template v-if="breadcrumbs.length > 2">
                        <BreadcrumbSeparator class="md:hidden" />
                        <BreadcrumbItem class="md:hidden">
                            <span class="text-muted-foreground">…</span>
                        </BreadcrumbItem>
                    </template>
                </template>

                <!-- Middle items: desktop only -->
                <template v-else-if="index < breadcrumbs.length - 1">
                    <BreadcrumbSeparator class="hidden md:flex" />
                    <BreadcrumbItem class="hidden min-w-0 md:flex">
                        <BreadcrumbLink
                            v-if="item.href"
                            :href="item.href"
                            :title="item.title"
                            class="inline-block max-w-37.5 truncate align-bottom"
                        >
                            {{ item.title }}
                        </BreadcrumbLink>
                        <BreadcrumbPage
                            v-else
                            :title="item.title"
                            class="inline-block max-w-37.5 truncate align-bottom"
                        >
                            {{ item.title }}
                        </BreadcrumbPage>
                    </BreadcrumbItem>
                </template>

                <!-- Last item: always visible -->
                <template v-else>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem class="min-w-0">
                        <BreadcrumbLink
                            v-if="item.href"
                            :href="item.href"
                            :title="item.title"
                            class="inline-block max-w-25 truncate align-bottom sm:max-w-45"
                        >
                            {{ item.title }}
                        </BreadcrumbLink>
                        <BreadcrumbPage
                            v-else
                            :title="item.title"
                            class="inline-block max-w-25 truncate align-bottom sm:max-w-45"
                        >
                            {{ item.title }}
                        </BreadcrumbPage>
                    </BreadcrumbItem>
                </template>
            </template>
        </BreadcrumbList>
    </Breadcrumb>
</template>
