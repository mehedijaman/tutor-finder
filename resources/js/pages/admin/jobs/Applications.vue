<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    job: { type: Object, required: true },
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'Jobs', href: '/admin/jobs' },
    {
        title: 'Applications',
        href: `/admin/jobs/${props.job.id}/applications`,
    },
];

const baseUrl = `/admin/jobs/${props.job.id}/applications`;

const columns = [
    { key: 'tutor_name', label: 'Tutor' },
    { key: 'status', label: 'Status' },
    { key: 'expected_salary_amount', label: 'Expected Salary' },
    { key: 'cover_letter', label: 'Cover Letter' },
    { key: 'cancel_reason', label: 'Cancel Reason' },
    { key: 'created_at', label: 'Applied At' },
];

const statusFilter = ref(props.filters.status || 'all');

watch(
    () => props.filters.status,
    (value) => {
        const normalized = value || 'all';

        if (statusFilter.value !== normalized) {
            statusFilter.value = normalized;
        }
    },
);

watch(statusFilter, (value) => {
    router.get(
        baseUrl,
        { status: value === 'all' ? '' : value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
});

function badgeVariant(status) {
    if (status === 'confirmed') {
        return 'default';
    }

    if (status === 'shortlisted' || status === 'appointed') {
        return 'secondary';
    }

    if (status === 'cancelled') {
        return 'destructive';
    }

    return 'outline';
}
</script>

<template>
    <Head :title="`Applications - ${job.title}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            Applications for {{ job.title }}
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Guardian: {{ job.guardian_name || '—' }}
                        </p>
                    </div>

                    <Link
                        href="/admin/jobs"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Jobs
                    </Link>
                </div>
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p class="text-xs text-muted-foreground">Job Status</p>
                    <Badge
                        :variant="
                            job.status === 'live' && job.is_expired
                                ? 'destructive'
                                : 'secondary'
                        "
                    >
                        {{
                            job.status === 'live' && job.is_expired
                                ? 'expired'
                                : job.status
                        }}
                    </Badge>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground">Hiring Outcome</p>
                    <p class="text-sm font-medium">
                        {{
                            job.has_assignment
                                ? job.selected_tutor_name || 'Selected tutor'
                                : 'Not finalized'
                        }}
                    </p>
                    <p
                        v-if="job.assignment_confirmed_at"
                        class="text-xs text-muted-foreground"
                    >
                        Confirmed:
                        {{
                            new Date(
                                job.assignment_confirmed_at,
                            ).toLocaleString()
                        }}
                    </p>
                </div>

                <div>
                    <p class="mb-1 text-xs text-muted-foreground">
                        Filter by Status
                    </p>
                    <Select v-model="statusFilter">
                        <SelectTrigger>
                            <SelectValue placeholder="All statuses" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All statuses</SelectItem>
                            <SelectItem
                                v-for="option in statusOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No applications found for this job."
            >
                <template #cell-tutor_name="{ row }">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <p class="font-medium">{{ row.tutor.name }}</p>
                            <Badge v-if="row.is_selected" variant="default"
                                >Selected</Badge
                            >
                        </div>
                        <p
                            v-if="row.tutor.email"
                            class="text-xs text-muted-foreground"
                        >
                            {{ row.tutor.email }}
                        </p>
                        <p
                            v-if="row.tutor.phone"
                            class="text-xs text-muted-foreground"
                        >
                            {{ row.tutor.phone }}
                        </p>
                    </div>
                </template>

                <template #cell-status="{ value }">
                    <Badge :variant="badgeVariant(value)">{{ value }}</Badge>
                </template>

                <template #cell-expected_salary_amount="{ row }">
                    {{
                        row.expected_salary_amount
                            ? `${row.salary_currency || 'BDT'} ${row.expected_salary_amount}`
                            : '—'
                    }}
                </template>

                <template #cell-cover_letter="{ value }">
                    <p
                        class="line-clamp-2 max-w-xs text-sm text-muted-foreground"
                    >
                        {{ value || '—' }}
                    </p>
                </template>

                <template #cell-cancel_reason="{ value }">{{
                    value || '—'
                }}</template>

                <template #cell-created_at="{ value }">
                    {{ value ? new Date(value).toLocaleString() : '—' }}
                </template>
            </DataTable>
        </div>
    </AdminLayout>
</template>
