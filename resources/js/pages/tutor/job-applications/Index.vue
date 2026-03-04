<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import TutorLayout from '@/layouts/TutorLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { title: 'My Applications', href: '/tutor/job-applications' },
];
const presetStatus = computed(() => props.filters.preset_status || '');
const baseUrl = computed(() => {
    if (presetStatus.value === 'applied') {
        return '/tutor/job-applications/applied';
    }

    if (presetStatus.value === 'shortlisted') {
        return '/tutor/job-applications/shortlisted';
    }

    if (presetStatus.value === 'appointed') {
        return '/tutor/job-applications/appointed';
    }

    if (presetStatus.value === 'confirmed') {
        return '/tutor/job-applications/confirmed';
    }

    if (presetStatus.value === 'cancelled') {
        return '/tutor/job-applications/cancelled';
    }

    return '/tutor/job-applications';
});

const columns = [
    { key: 'job_title', label: 'Job' },
    { key: 'job_status', label: 'Job Status' },
    { key: 'status', label: 'Application Status' },
    { key: 'expected_salary_amount', label: 'Expected Salary' },
    { key: 'created_at', label: 'Applied At' },
    { key: 'cancel_reason', label: 'Cancel Reason' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const statusFilter = ref(props.filters.status || 'all');
const confirmOpen = ref(false);
const pendingRow = ref<any>(null);

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
    if (presetStatus.value) {
        return;
    }

    router.get(
        baseUrl.value,
        {
            status: presetStatus.value ? '' : value === 'all' ? '' : value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
});

function badgeVariant(status: string): string {
    if (status === 'confirmed') {
        return 'default';
    }

    if (status === 'shortlisted' || status === 'appointed') {
        return 'secondary';
    }

    if (status === 'applied') {
        return 'outline';
    }

    if (status === 'cancelled') {
        return 'destructive';
    }

    return 'outline';
}

function actionItems(row: any): Array<Record<string, unknown>> {
    return [
        { key: 'view-job', label: 'View Job', show: !!row.job.slug },
        {
            key: 'withdraw',
            label: 'Cancel Application',
            destructive: true,
            show: ['applied', 'shortlisted'].includes(row.status),
        },
    ];
}

function handleAction(action: string, row: any): void {
    if (action === 'view-job') {
        router.visit(`/jobs/${row.job.slug}`);
        return;
    }

    if (action === 'withdraw') {
        pendingRow.value = row;
        confirmOpen.value = true;
    }
}

function confirmWithdraw(): void {
    if (!pendingRow.value) {
        return;
    }

    router.patch(
        `/tutor/job-applications/${pendingRow.value.id}/withdraw`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                pendingRow.value = null;
            },
        },
    );

    confirmOpen.value = false;
}

function closeConfirm(): void {
    confirmOpen.value = false;
    pendingRow.value = null;
}
</script>

<template>
    <Head title="My Applications" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight">
                            My Applications
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Track job applications and guardian responses from
                            one timeline.
                        </p>
                    </div>

                    <Link
                        href="/jobs"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >Browse Jobs</Link
                    >
                </div>
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-3"
            >
                <Select v-if="!presetStatus" v-model="statusFilter">
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

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No job applications found."
            >
                <template #cell-job_title="{ row }">
                    <Link
                        v-if="row.job.slug"
                        :href="`/jobs/${row.job.slug}`"
                        class="font-medium text-blue-600 hover:underline"
                    >
                        {{ row.job.title }}
                    </Link>
                    <p v-else class="font-medium">{{ row.job.title }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ row.job.city_name || 'Unknown city' }}
                    </p>
                </template>

                <template #cell-job_status="{ row }">
                    <Badge variant="outline">{{ row.job.status }}</Badge>
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

                <template #cell-created_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
                }}</template>
                <template #cell-cancel_reason="{ value }">{{
                    value || '—'
                }}</template>

                <template #cell-actions="{ row }">
                    <RowActionsDropdown
                        :actions="actionItems(row)"
                        @select="(action) => handleAction(action, row)"
                    />
                </template>
            </DataTable>
        </div>
    </TutorLayout>

    <ConfirmDialog
        v-model:open="confirmOpen"
        title="Cancel Application"
        description="This will cancel your application for this job."
        confirm-label="Cancel Application"
        :destructive="true"
        @confirm="confirmWithdraw"
        @cancel="closeConfirm"
    />
</template>
