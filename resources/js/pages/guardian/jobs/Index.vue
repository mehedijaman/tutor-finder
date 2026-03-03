<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [{ title: 'My Jobs', href: '/guardian/jobs' }];
const baseUrl = '/guardian/jobs';

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status' },
    { key: 'category_name', label: 'Category' },
    { key: 'class_name', label: 'Class' },
    { key: 'city_name', label: 'City' },
    { key: 'applications_count', label: 'Applications' },
    { key: 'published_at', label: 'Published At' },
    { key: 'expires_at', label: 'Expires At' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
let searchDebounceTimer = null;

watch(
    () => props.filters.q,
    (value) => {
        const normalized = value ?? '';

        if (search.value !== normalized) {
            search.value = normalized;
        }
    },
);

watch(
    () => props.filters.status,
    (value) => {
        const normalized = value || 'all';

        if (statusFilter.value !== normalized) {
            statusFilter.value = normalized;
        }
    },
);

watch(search, (value) => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    searchDebounceTimer = setTimeout(() => {
        applyFilters({ q: value, page: 1 });
    }, 350);
});

watch(statusFilter, (value) => {
    applyFilters({ status: value === 'all' ? '' : value, page: 1 });
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
            q: search.value,
            status: statusFilter.value === 'all' ? '' : statusFilter.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}
</script>

<template>
    <Head title="My Jobs" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold">My Jobs</h1>
                    <p class="text-sm text-muted-foreground">
                        Track and manage your job postings.
                    </p>
                </div>

                <Button as-child>
                    <Link href="/guardian/jobs/create">Post New Job</Link>
                </Button>
            </div>

            <div
                class="grid gap-3 rounded-xl border bg-white p-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by title or slug"
                    class="sm:col-span-2"
                />

                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All statuses</SelectItem>
                        <SelectItem
                            v-for="status in statusOptions"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No jobs found."
            >
                <template #cell-status="{ row }">
                    <Badge
                        :variant="
                            row.status === 'live' ? 'default' : 'secondary'
                        "
                        >{{ row.status }}</Badge
                    >
                </template>

                <template #cell-applications_count="{ row }">
                    <Link
                        :href="`/guardian/jobs/${row.id}/applications`"
                        class="text-sm font-medium text-blue-600 hover:underline"
                    >
                        {{ row.applications_count ?? 0 }}
                    </Link>
                </template>

                <template #cell-published_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
                }}</template>
                <template #cell-expires_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
                }}</template>
            </DataTable>
        </div>
    </GuardianLayout>
</template>
