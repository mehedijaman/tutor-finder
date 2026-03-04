<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
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
});

const emit = defineEmits(['sort']);

const rows = computed(() => props.items?.data ?? []);
const links = computed(() => props.items?.links ?? []);

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
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
    >
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50/80">
                <tr>
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        class="px-4 py-3.5 text-xs font-semibold tracking-wide text-slate-600 uppercase"
                        :class="column.headerClass"
                    >
                        <Button
                            v-if="column.sortable"
                            variant="ghost"
                            size="sm"
                            class="-ml-3 h-auto p-0 text-xs font-semibold tracking-wide text-slate-600 uppercase hover:bg-transparent hover:text-slate-900"
                            @click="toggleSort(column)"
                        >
                            {{ column.label }}
                            <component
                                :is="sortIcon(column)"
                                class="ml-2 h-3.5 w-3.5"
                            />
                        </Button>
                        <span v-else>{{ column.label }}</span>
                    </th>
                </tr>
            </thead>

            <tbody>
                <template v-if="loading">
                    <tr class="border-t">
                        <td
                            :colspan="columns.length"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            Loading...
                        </td>
                    </tr>
                </template>

                <template v-else-if="rows.length">
                    <tr
                        v-for="row in rows"
                        :key="row[rowKey] ?? row.id"
                        class="border-t border-slate-200/70 transition-colors hover:bg-slate-50/60"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="px-4 py-3.5 align-middle"
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
                    <tr class="border-t">
                        <td
                            :colspan="columns.length"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            {{ emptyText }}
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>

        <div
            v-if="links.length > 3"
            class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-200/80 px-4 py-3"
        >
            <template
                v-for="(link, index) in links"
                :key="`${index}-${link.label}`"
            >
                <Button v-if="!link.url" variant="outline" size="sm" disabled>
                    {{ formatPaginationLabel(link.label) }}
                </Button>

                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    class="inline-flex h-8 items-center justify-center rounded-lg border px-3 text-xs font-medium"
                    :class="
                        link.active
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                    "
                >
                    {{ formatPaginationLabel(link.label) }}
                </Link>
            </template>
        </div>
    </div>
</template>
