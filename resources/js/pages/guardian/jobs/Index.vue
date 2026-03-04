<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
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

type JobStatusOption = {
    value: string;
    label: string;
};

type GuardianJobRow = {
    id: number;
    status: string;
    is_expired?: boolean;
    open_applications_count?: number;
    applications_count?: number;
    has_assignment?: boolean;
    selected_tutor_name?: string | null;
    hiring_confirmed_at?: string | null;
};

const breadcrumbs = [{ title: 'My Jobs', href: '/guardian/jobs' }];
const presetStatus = computed(() => props.filters.preset_status || '');
const baseUrl = computed(() => {
    if (presetStatus.value === 'pending') {
        return '/guardian/jobs/pending';
    }

    if (presetStatus.value === 'live') {
        return '/guardian/jobs/live';
    }

    if (presetStatus.value === 'confirmed') {
        return '/guardian/jobs/confirmed';
    }

    if (presetStatus.value === 'cancelled') {
        return '/guardian/jobs/cancelled';
    }

    if (presetStatus.value === 'closed') {
        return '/guardian/jobs/closed';
    }

    return '/guardian/jobs';
});

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status' },
    { key: 'category_name', label: 'Category' },
    { key: 'class_name', label: 'Class' },
    { key: 'city_name', label: 'City' },
    { key: 'applications_count', label: 'Applications' },
    { key: 'hiring_status', label: 'Hiring Outcome' },
    { key: 'published_at', label: 'Published At' },
    { key: 'expires_at', label: 'Expires At' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;
const statusOptionsList = computed<JobStatusOption[]>(
    () => (props.statusOptions as JobStatusOption[] | undefined) ?? [],
);

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
    if (presetStatus.value) {
        return;
    }

    applyFilters({ status: value === 'all' ? '' : value, page: 1 });
});

onBeforeUnmount(() => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
});

function applyFilters(overrides = {}) {
    router.get(
        baseUrl.value,
        {
            q: search.value,
            status: presetStatus.value
                ? ''
                : statusFilter.value === 'all'
                  ? ''
                  : statusFilter.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function statusBadge(row: GuardianJobRow): {
    label: string;
    variant: 'default' | 'destructive' | 'secondary' | 'outline';
} {
    if (row.status === 'live' && row.is_expired) {
        return {
            label: 'expired',
            variant: 'destructive',
        };
    }

    if (row.status === 'live') {
        return {
            label: row.status,
            variant: 'default',
        };
    }

    return {
        label: row.status,
        variant: 'secondary',
    };
}
</script>

<template>
    <Head title="My Jobs" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            My Jobs
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Track job performance, application volume, and
                            hiring status.
                        </p>
                    </div>

                    <Button as-child>
                        <Link href="/guardian/jobs/create">Post New Job</Link>
                    </Button>
                </div>
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-3"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search by title or slug"
                    class="sm:col-span-2"
                />

                <Select v-if="!presetStatus" v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All statuses</SelectItem>
                        <SelectItem
                            v-for="status in statusOptionsList"
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
                    <Badge :variant="statusBadge(row).variant">
                        {{ statusBadge(row).label }}
                    </Badge>
                </template>

                <template #cell-applications_count="{ row }">
                    <Link
                        :href="`/guardian/jobs/${row.id}/applications`"
                        class="text-sm font-medium text-blue-600 hover:underline"
                    >
                        {{ row.open_applications_count ?? 0 }} open /
                        {{ row.applications_count ?? 0 }} total
                    </Link>
                </template>

                <template #cell-hiring_status="{ row }">
                    <div v-if="row.has_assignment" class="space-y-0.5">
                        <p class="text-sm font-medium">
                            {{ row.selected_tutor_name || 'Selected tutor' }}
                        </p>
                        <p
                            v-if="row.hiring_confirmed_at"
                            class="text-xs text-muted-foreground"
                        >
                            Confirmed:
                            {{
                                new Date(
                                    row.hiring_confirmed_at,
                                ).toLocaleString()
                            }}
                        </p>
                    </div>
                    <span v-else class="text-muted-foreground"
                        >Not finalized</span
                    >
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
