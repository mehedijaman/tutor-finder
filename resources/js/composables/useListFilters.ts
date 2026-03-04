import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import type { BaseFilters } from '@/types';

export type UseListFiltersOptions<F extends BaseFilters> = {
    /**
     * Base URL for the list page.
     */
    baseUrl: string;

    /**
     * Initial filter values from the server.
     */
    initialFilters: F;

    /**
     * Debounce delay for search input in milliseconds.
     * @default 350
     */
    searchDebounce?: number;

    /**
     * Preserve scroll position during navigation.
     * @default true
     */
    preserveScroll?: boolean;

    /**
     * Preserve component state during navigation.
     * @default true
     */
    preserveState?: boolean;
};

export type UseListFiltersReturn<F extends BaseFilters> = {
    /**
     * Reactive search query string.
     */
    search: ReturnType<typeof ref<string>>;

    /**
     * Reactive sort field.
     */
    sortBy: ReturnType<typeof ref<string>>;

    /**
     * Reactive sort direction.
     */
    direction: ReturnType<typeof ref<'asc' | 'desc'>>;

    /**
     * Apply filter overrides and navigate.
     */
    applyFilters: (overrides?: Partial<F>) => void;

    /**
     * Handle sort toggle from table header click.
     */
    handleSort: (columnKey: string) => void;

    /**
     * Reset filters to initial state.
     */
    resetFilters: () => void;

    /**
     * Current filters merged with overrides.
     */
    getFilters: (overrides?: Partial<F>) => Record<string, unknown>;
};

export function useListFilters<F extends BaseFilters>(
    options: UseListFiltersOptions<F>,
): UseListFiltersReturn<F> {
    const {
        baseUrl,
        initialFilters,
        searchDebounce = 350,
        preserveScroll = true,
        preserveState = true,
    } = options;

    const search = ref(initialFilters.q ?? initialFilters.search ?? '');
    const sortBy = ref(initialFilters.sort ?? '');
    const direction = ref<'asc' | 'desc'>(initialFilters.direction ?? 'desc');

    let searchTimer: ReturnType<typeof setTimeout> | null = null;

    function getFilters(overrides: Partial<F> = {}): Record<string, unknown> {
        const filters: Record<string, unknown> = {
            q: search.value || undefined,
            sort: sortBy.value || undefined,
            direction: direction.value,
            ...overrides,
        };

        // Remove empty/undefined values
        Object.keys(filters).forEach((key) => {
            if (
                filters[key] === undefined ||
                filters[key] === '' ||
                filters[key] === 'all'
            ) {
                delete filters[key];
            }
        });

        return filters;
    }

    function applyFilters(overrides: Partial<F> = {}): void {
        router.get(baseUrl, getFilters(overrides), {
            preserveScroll,
            preserveState,
        });
    }

    function handleSort(columnKey: string): void {
        if (sortBy.value === columnKey) {
            direction.value = direction.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortBy.value = columnKey;
            direction.value = 'desc';
        }
        applyFilters({ page: 1 } as Partial<F>);
    }

    function resetFilters(): void {
        search.value = '';
        sortBy.value = '';
        direction.value = 'desc';
        applyFilters({ page: 1 } as Partial<F>);
    }

    // Watch search with debounce
    watch(search, (value) => {
        if (searchTimer) {
            clearTimeout(searchTimer);
        }

        searchTimer = setTimeout(() => {
            applyFilters({ q: value, page: 1 } as Partial<F>);
        }, searchDebounce);
    });

    // Cleanup timer on unmount
    onBeforeUnmount(() => {
        if (searchTimer) {
            clearTimeout(searchTimer);
        }
    });

    return {
        search,
        sortBy,
        direction,
        applyFilters,
        handleSort,
        resetFilters,
        getFilters,
    };
}
