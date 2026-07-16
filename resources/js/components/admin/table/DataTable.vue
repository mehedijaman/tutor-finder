<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown, Columns3, Inbox } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

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
        default: false,
    },
    maxHeight: {
        type: String,
        default: '500px',
    },
    /** Column key to use as the primary/heading in mobile card view */
    mobilePrimary: {
        type: String,
        default: '',
    },
    /** Column keys to hide on tablet (visible only on lg+) */
    hideOnTablet: {
        type: Array as () => string[],
        default: () => [],
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

// Column visibility state
const hiddenColumns = ref<Set<string>>(new Set());
const visibleColumns = computed(() =>
    props.columns.filter((col) => !hiddenColumns.value.has(col.key)),
);
const tabletColumns = computed(() =>
    visibleColumns.value.filter((col) => !props.hideOnTablet.includes(col.key)),
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

function toggleColumnVisibility(key: string) {
    if (hiddenColumns.value.has(key)) {
        hiddenColumns.value.delete(key);
    } else {
        hiddenColumns.value.add(key);
    }
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

function mobilePrimaryValue(row: any): string {
    if (props.mobilePrimary && row[props.mobilePrimary]) {
        return row[props.mobilePrimary];
    }

    const firstCol = props.columns[0];

    return firstCol ? (row[firstCol.key] ?? '') : '';
}
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
    >
        <div class="hidden sm:block">
            <div
                class="overflow-x-auto"
                :class="{ 'overflow-y-auto': maxHeight }"
                :style="maxHeight ? { maxHeight } : undefined"
            >
                <table
                    class="w-full text-left text-sm"
                    role="grid"
                    aria-label="Data table"
                >
                    <thead
                        class="sticky top-0 z-10 border-b border-border bg-muted/50"
                    >
                        <tr>
                            <th
                                v-if="showSerial"
                                class="px-4 py-3.5 text-center text-xs font-semibold tracking-wider whitespace-nowrap text-muted-foreground uppercase"
                                style="width: 56px"
                            >
                                SL
                            </th>
                            <th
                                v-for="column in visibleColumns"
                                :key="column.key"
                                class="px-4 py-3.5 text-xs font-semibold tracking-wider whitespace-nowrap text-muted-foreground uppercase"
                                :class="[
                                    column.headerClass,
                                    hideOnTablet.includes(column.key)
                                        ? 'hidden lg:table-cell'
                                        : '',
                                ]"
                            >
                                <Button
                                    v-if="column.sortable"
                                    variant="ghost"
                                    size="sm"
                                    class="-ml-3 h-auto p-0 text-xs font-semibold tracking-wide text-muted-foreground uppercase hover:bg-transparent hover:text-foreground"
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

                    <tbody class="divide-y divide-border/50">
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
                                            class="h-5 w-5 animate-spin rounded-full border-2 border-border border-t-primary"
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
                                class="transition-colors hover:bg-muted/30"
                            >
                                <td
                                    v-if="showSerial"
                                    class="px-4 py-3.5 text-center text-xs font-medium whitespace-nowrap text-muted-foreground tabular-nums"
                                >
                                    {{ serialNumber(index) }}
                                </td>
                                <td
                                    v-for="column in visibleColumns"
                                    :key="column.key"
                                    class="px-4 py-3.5 align-middle text-sm text-card-foreground"
                                    :class="[
                                        column.cellClass,
                                        hideOnTablet.includes(column.key)
                                            ? 'hidden lg:table-cell'
                                            : '',
                                    ]"
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
                                            class="flex h-12 w-12 items-center justify-center rounded-full bg-muted"
                                        >
                                            <Inbox
                                                class="h-6 w-6 text-muted-foreground"
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
        </div>

        <!-- Mobile card view -->
        <div
            class="sm:hidden"
            :class="{ 'overflow-y-auto': maxHeight }"
            :style="maxHeight ? { maxHeight } : undefined"
        >
            <template v-if="loading">
                <div class="flex flex-col items-center gap-2 px-4 py-12 text-muted-foreground">
                    <div
                        class="h-5 w-5 animate-spin rounded-full border-2 border-border border-t-primary"
                    />
                    <span class="text-sm">Loading...</span>
                </div>
            </template>

            <template v-else-if="rows.length">
                <div class="divide-y divide-border/50">
                    <div
                        v-for="(row, index) in rows"
                        :key="row[rowKey] ?? row.id"
                        class="space-y-3 px-4 py-4"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-semibold text-card-foreground"
                                >
                                    <slot
                                        :name="`cell-${mobilePrimaryValue(row) ? columns[0]?.key : ''}`"
                                        :row="row"
                                        :value="mobilePrimaryValue(row)"
                                    >
                                        {{ mobilePrimaryValue(row) || '—' }}
                                    </slot>
                                </p>
                                <p
                                    v-if="showSerial"
                                    class="mt-0.5 text-xs text-muted-foreground tabular-nums"
                                >
                                    #{{ serialNumber(index) }}
                                </p>
                            </div>
                            <slot
                                name="cell-actions"
                                :row="row"
                            >
                                <slot
                                    :name="`cell-${columns[columns.length - 1]?.key}`"
                                    :row="row"
                                    :value="row[columns[columns.length - 1]?.key]"
                                />
                            </slot>
                        </div>

                        <div class="space-y-2">
                            <div
                                v-for="(column, colIndex) in visibleColumns.slice(1)"
                                :key="column.key"
                                v-show="colIndex < 4"
                                class="flex items-start justify-between gap-3"
                            >
                                <span class="shrink-0 text-xs font-medium text-muted-foreground">
                                    {{ column.label }}
                                </span>
                                <div
                                    class="min-w-0 text-right text-sm text-card-foreground"
                                    :class="column.cellClass"
                                >
                                    <slot
                                        :name="`cell-${column.key}`"
                                        :row="row"
                                        :value="row[column.key]"
                                    >
                                        <span class="truncate block max-w-[200px]">
                                            {{ row[column.key] ?? '—' }}
                                        </span>
                                    </slot>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="flex flex-col items-center gap-3 px-4 py-16 text-muted-foreground">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-muted"
                    >
                        <Inbox class="h-6 w-6 text-muted-foreground" />
                    </div>
                    <p class="text-sm font-medium">{{ emptyText }}</p>
                </div>
            </template>
        </div>

        <!-- Pagination -->
        <div
            v-if="hasPagination"
            class="border-t border-border px-4 py-3"
            role="navigation"
            aria-label="Pagination"
        >
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <span
                        v-if="items.from && items.to && items.total"
                        class="text-center text-xs text-muted-foreground sm:text-left"
                    >
                        Showing {{ items.from }}–{{ items.to }} of {{ items.total }}
                    </span>

                    <!-- Column visibility toggle -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                size="sm"
                                class="h-8 gap-1.5 px-2.5 text-xs"
                            >
                                <Columns3 class="h-3.5 w-3.5" />
                                <span class="hidden sm:inline">Columns</span>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-44">
                            <DropdownMenuLabel class="text-xs">
                                Toggle columns
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuCheckboxItem
                                v-for="column in columns"
                                :key="column.key"
                                :checked="!hiddenColumns.has(column.key)"
                                class="text-xs"
                                @update:checked="toggleColumnVisibility(column.key)"
                            >
                                {{ column.label }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

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
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-border bg-card px-3 text-xs font-medium text-card-foreground transition-colors hover:bg-muted"
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
                        class="inline-flex h-9 w-full items-center justify-center rounded-lg border border-border bg-card px-3 text-xs font-medium text-card-foreground transition-colors hover:bg-muted"
                    >
                        Next
                    </Link>
                </div>

                <span
                    class="text-center text-xs text-muted-foreground sm:hidden"
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
                                    : 'border-border bg-card text-card-foreground hover:bg-muted'
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
