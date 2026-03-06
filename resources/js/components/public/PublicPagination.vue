<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = withDefaults(
    defineProps<{
        links: PaginationLink[];
        currentPage?: number;
        lastPage?: number;
        from?: number | null;
        to?: number | null;
        total?: number | null;
        preserveScroll?: boolean;
    }>(),
    {
        preserveScroll: true,
    },
);

const hasPagination = computed(() => (props.links?.length ?? 0) > 3);
const previousLink = computed(() => props.links?.[0] ?? null);
const nextLink = computed(() =>
    props.links?.length ? props.links[props.links.length - 1] : null,
);
const hasRecordRange = computed(
    () =>
        props.from !== null &&
        props.from !== undefined &&
        props.to !== null &&
        props.to !== undefined &&
        props.total !== null &&
        props.total !== undefined,
);

function formatPaginationLabel(label: string): string {
    return String(label ?? '')
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]*>/g, '')
        .trim();
}

function parsePageNumber(label: string): number | null {
    const normalizedLabel = formatPaginationLabel(label);

    if (!/^\d+$/.test(normalizedLabel)) {
        return null;
    }

    return Number(normalizedLabel);
}

const derivedCurrentPage = computed(() => {
    if (Number.isFinite(props.currentPage)) {
        return Number(props.currentPage);
    }

    const activeLink = props.links.find((link) => link.active);

    if (!activeLink) {
        return null;
    }

    return parsePageNumber(activeLink.label);
});

const derivedLastPage = computed(() => {
    if (Number.isFinite(props.lastPage)) {
        return Number(props.lastPage);
    }

    let highestPage: number | null = null;

    for (const link of props.links) {
        const pageNumber = parsePageNumber(link.label);

        if (pageNumber === null) {
            continue;
        }

        if (highestPage === null || pageNumber > highestPage) {
            highestPage = pageNumber;
        }
    }

    return highestPage;
});

const mobilePaginationLabel = computed(() => {
    if (!derivedCurrentPage.value || !derivedLastPage.value) {
        return '';
    }

    return `Page ${derivedCurrentPage.value} of ${derivedLastPage.value}`;
});
</script>

<template>
    <div
        v-if="hasPagination"
        class="border-t border-slate-200/80 pt-5 dark:border-slate-700/80"
        role="navigation"
        aria-label="Pagination"
    >
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <span
                v-if="hasRecordRange"
                class="text-center text-xs text-slate-500 sm:text-left dark:text-slate-400"
            >
                Showing {{ from }}–{{ to }} of {{ total }}
            </span>

            <div class="grid grid-cols-2 gap-2 sm:hidden">
                <Button
                    v-if="!previousLink?.url"
                    variant="outline"
                    size="sm"
                    disabled
                    class="h-9 w-full"
                >
                    Previous
                </Button>

                <Link
                    v-else
                    :href="previousLink.url"
                    :preserve-scroll="preserveScroll"
                    class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                >
                    Previous
                </Link>

                <Button
                    v-if="!nextLink?.url"
                    variant="outline"
                    size="sm"
                    disabled
                    class="h-9 w-full"
                >
                    Next
                </Button>

                <Link
                    v-else
                    :href="nextLink.url"
                    :preserve-scroll="preserveScroll"
                    class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                >
                    Next
                </Link>
            </div>

            <span
                v-if="mobilePaginationLabel"
                class="text-center text-xs text-slate-500 sm:hidden dark:text-slate-400"
            >
                {{ mobilePaginationLabel }}
            </span>

            <div class="hidden flex-wrap items-center gap-1.5 sm:flex">
                <template
                    v-for="(link, index) in links"
                    :key="`${index}-${link.label}`"
                >
                    <Button
                        v-if="!link.url"
                        variant="outline"
                        size="sm"
                        disabled
                        class="h-8 min-w-8 px-2.5 text-xs"
                    >
                        {{ formatPaginationLabel(link.label) }}
                    </Button>

                    <Link
                        v-else
                        :href="link.url"
                        :preserve-scroll="preserveScroll"
                        class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border px-2.5 text-xs font-medium transition-colors"
                        :class="
                            link.active
                                ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700'
                        "
                        :aria-current="link.active ? 'page' : undefined"
                    >
                        {{ formatPaginationLabel(link.label) }}
                    </Link>
                </template>
            </div>
        </div>
    </div>
</template>
