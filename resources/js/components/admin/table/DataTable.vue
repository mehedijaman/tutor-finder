<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown, Inbox } from 'lucide-vue-next';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';

const props = defineProps({
    items: {
        type: Object,
        required: true,
    },
    columns: {
        type: Array,
        default: () => [],
    },
    sortBy: {
        type: String,
        default: '',
    },
    sortDirection: {
        type: String,
        default: 'asc',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyText: {
        type: String,
        default: 'No records found.',
    },
    rowKey: {
        type: String,
        default: 'id',
    },
    showSerial: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['sort']);

const rows = computed(() => props.items?.data ?? []);
const links = computed(() => props.items?.links ?? []);
const currentPage = computed(() => props.items?.current_page ?? 1);
const lastPage = computed(() => props.items?.last_page ?? 1);
const perPage = computed(() => props.items?.per_page ?? rows.value.length);
const totalColumns = computed(
    () => props.columns.length + (props.showSerial ? 1 : 0),
);
const hasPagination = computed(() => links.value.length > 3);
const previousLink = computed(() => links.value[0] ?? null);
const nextLink = computed(() =>
    links.value.length > 0 ? links.value[links.value.length - 1] : null,
);

function serialNumber(index: number): number {
    return (currentPage.value - 1) * perPage.value + index + 1;
}

function toggleSort(column) {
    if (!column.sortable) {
        return;
    }

    emit('sort', column.key);
}

function sortIcon(column) {
    if (!column.sortable) {
        return null;
    }

    if (props.sortBy !== column.key) {
        return ArrowUpDown;
    }

    return props.sortDirection === 'desc' ? ArrowDown : ArrowUp;
}

function formatPaginationLabel(label) {
    return String(label ?? '')
        .replaceAll('&laquo;', '«')
        .replaceAll('&raquo;', '»')
        .replace(/<[^>]*>/g, '')
        .trim();
}

function mobilePaginationLabel(): string {
    if (
        !Number.isFinite(currentPage.value) ||
        !Number.isFinite(lastPage.value)
    ) {
        return '';
    }

    return `Page ${currentPage.value} of ${lastPage.value}`;
}
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 dark:border-slate-700/80 dark:bg-slate-900 dark:ring-slate-50/5"
    >
        <div class="overflow-x-auto">
            <table
                class="w-full text-left text-sm"
                role="grid"
                aria-label="Data table"
            >
                <thead
                    class="border-b border-slate-200/80 bg-slate-50/80 dark:border-slate-700/60 dark:bg-slate-800/50"
                >
                    <tr>
                        <th
                            v-if="showSerial"
                            class="px-4 py-3.5 text-center text-xs font-semibold tracking-wider whitespace-nowrap text-slate-500 uppercase dark:text-slate-400"
                            style="width: 56px"
                        >
                            SL
                        </th>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="px-4 py-3.5 text-xs font-semibold tracking-wider whitespace-nowrap text-slate-500 uppercase dark:text-slate-400"
                            :class="column.headerClass"
                        >
                            <Button
                                v-if="column.sortable"
                                variant="ghost"
                                size="sm"
                                class="-ml-3 h-auto p-0 text-xs font-semibold tracking-wide text-slate-600 uppercase hover:bg-transparent hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                                :aria-label="`Sort by ${column.label}`"
                                @click="toggleSort(column)"
                            >
                                {{ column.label }}
                                <component
                                    :is="sortIcon(column)"
                                    class="ml-1.5 h-3.5 w-3.5"
                                />
                            </Button>
                            <span v-else>{{ column.label }}</span>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <template v-if="loading">
                        <tr>
                            <td
                                :colspan="totalColumns"
                                class="px-4 py-12 text-center"
                            >
                                <div
                                    class="flex flex-col items-center gap-2 text-muted-foreground"
                                >
                                    <div
                                        class="h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-blue-500"
                                    />
                                    <span class="text-sm">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </template>

                    <template v-else-if="rows.length">
                        <tr
                            v-for="(row, index) in rows"
                            :key="row[rowKey] ?? row.id"
                            class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                        >
                            <td
                                v-if="showSerial"
                                class="px-4 py-3.5 text-center text-xs font-medium whitespace-nowrap text-slate-400 tabular-nums dark:text-slate-500"
                            >
                                {{ serialNumber(index) }}
                            </td>
                            <td
                                v-for="column in columns"
                                :key="column.key"
                                class="px-4 py-3.5 align-middle text-sm text-slate-700 dark:text-slate-200"
                                :class="column.cellClass"
                            >
                                <slot
                                    :name="`cell-${column.key}`"
                                    :row="row"
                                    :value="row[column.key]"
                                >
                                    {{ row[column.key] ?? '—' }}
                                </slot>
                            </td>
                        </tr>
                    </template>

                    <template v-else>
                        <tr>
                            <td
                                :colspan="totalColumns"
                                class="px-4 py-16 text-center"
                            >
                                <div
                                    class="flex flex-col items-center gap-3 text-muted-foreground"
                                >
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <Inbox
                                            class="h-6 w-6 text-slate-400 dark:text-slate-500"
                                        />
                                    </div>
                                    <p class="text-sm font-medium">
                                        {{ emptyText }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div
            v-if="hasPagination"
            class="border-t border-slate-200/80 px-4 py-3 dark:border-slate-700/80"
            role="navigation"
            aria-label="Pagination"
        >
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <span
                    v-if="items.from && items.to && items.total"
                    class="text-center text-xs text-slate-500 sm:text-left dark:text-slate-400"
                >
                    Showing {{ items.from }}–{{ items.to }} of {{ items.total }}
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
                        preserve-scroll
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
                        preserve-scroll
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                    >
                        Next
                    </Link>
                </div>

                <span
                    class="text-center text-xs text-slate-500 sm:hidden dark:text-slate-400"
                >
                    {{ mobilePaginationLabel() }}
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
                            preserve-scroll
                            class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border px-2.5 text-xs font-medium transition-colors"
                            :class="
                                link.active
                                    ? 'border-primary bg-primary text-primary-foreground shadow-sm'
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
    </div>
</template>
