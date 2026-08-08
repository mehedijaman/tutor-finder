<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    items: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const breadcrumbs = [{ title: 'Activity Logs', href: '/admin/activity-logs' }];
const baseUrl = '/admin/activity-logs';

const columns = [
    { key: 'id', label: 'ID', sortable: true, cellClass: 'font-mono text-xs' },
    { key: 'log_name', label: 'Log', sortable: true },
    { key: 'description', label: 'Description', sortable: true },
    { key: 'event', label: 'Event', sortable: true },
    { key: 'causer', label: 'Causer' },
    { key: 'subject', label: 'Subject' },
    { key: 'properties', label: 'Properties' },
    { key: 'created_at', label: 'Created', sortable: true },
];

const search = ref(props.filters.search ?? '');
let searchDebounceTimer = null;

watch(
    () => props.filters.search,
    (value) => {
        const normalized = value ?? '';

        if (normalized !== search.value) {
            search.value = normalized;
        }
    },
);

watch(search, (value) => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        applyFilters({ search: value, page: 1 });
    }, 350);
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
});

function applyFilters(overrides = {}) {
    router.get(
        baseUrl,
        {
            search: search.value,
            sort: props.filters.sort ?? 'created_at',
            direction: props.filters.direction ?? 'desc',
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function handleSort(columnKey) {
    const nextDirection =
        props.filters.sort === columnKey && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';

    applyFilters({ sort: columnKey, direction: nextDirection, page: 1 });
}

function formatProperties(value) {
    if (!value || Object.keys(value).length === 0) {
        return '—';
    }

    return JSON.stringify(value);
}
</script>

<template>
    <Head title="Activity Logs" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm sm:p-6"
            >
                <h1
                    class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl"
                >
                    Activity Logs
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Review platform activity events and audit trail entries.
                </p>
            </div>

            <div
                class="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by description, event, log name, or model type"
                    class="max-w-md dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                />
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                :sort-by="filters.sort"
                :sort-direction="filters.direction"
                empty-text="No activity logs found."
                @sort="handleSort"
            >
                <template #cell-id="{ value }">
                    <span class="text-slate-700 dark:text-slate-300 font-mono text-xs">{{ value }}</span>
                </template>

                <template #cell-log_name="{ value }">
                    <span class="text-slate-800 dark:text-slate-200 font-medium">{{ value || 'default' }}</span>
                </template>

                <template #cell-description="{ value }">
                    <span class="text-slate-900 dark:text-slate-100 font-medium">{{ value }}</span>
                </template>

                <template #cell-event="{ value }">
                    <span class="text-slate-700 dark:text-slate-300 capitalize">{{ value || '—' }}</span>
                </template>

                <template #cell-causer="{ row }">
                    <span class="text-slate-700 dark:text-slate-300">{{ row.causer?.name || 'System' }}</span>
                </template>

                <template #cell-subject="{ row }">
                    <span class="text-slate-700 dark:text-slate-300">{{ row.subject?.type || '—' }}</span>
                </template>

                <template #cell-properties="{ value }">
                    <span
                        class="inline-block max-w-xs truncate font-mono text-xs text-muted-foreground"
                    >
                        {{ formatProperties(value) }}
                    </span>
                </template>

                <template #cell-created_at="{ value }">
                    <span class="text-slate-700 dark:text-slate-300">
                        {{ value ? new Date(value).toLocaleString() : '—' }}
                    </span>
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
