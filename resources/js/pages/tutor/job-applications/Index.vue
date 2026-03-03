<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
const baseUrl = '/tutor/job-applications';

const columns = [
    { key: 'job_title', label: 'Job' },
    { key: 'job_status', label: 'Job Status' },
    { key: 'status', label: 'Application Status' },
    { key: 'expected_salary', label: 'Expected Salary' },
    { key: 'created_at', label: 'Applied At' },
    { key: 'reviewed_at', label: 'Reviewed At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const statusFilter = ref(props.filters.status || 'all');
const confirmOpen = ref(false);
const pendingRow = ref(null);

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
    if (status === 'shortlisted') {
        return 'default';
    }

    if (status === 'pending') {
        return 'secondary';
    }

    return 'outline';
}

function actionItems(row) {
    return [
        { key: 'view-job', label: 'View Job', show: !!row.job.slug },
        {
            key: 'withdraw',
            label: 'Withdraw Application',
            destructive: true,
            show: ['pending', 'shortlisted'].includes(row.status),
        },
    ];
}

function handleAction(action, row) {
    if (action === 'view-job') {
        router.visit(`/jobs/${row.job.slug}`);
        return;
    }

    if (action === 'withdraw') {
        pendingRow.value = row;
        confirmOpen.value = true;
    }
}

function confirmWithdraw() {
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

function closeConfirm() {
    confirmOpen.value = false;
    pendingRow.value = null;
}
</script>

<template>
    <Head title="My Applications" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold">My Applications</h1>
                    <p class="text-sm text-muted-foreground">
                        Track job applications and latest guardian feedback.
                    </p>
                </div>

                <Link
                    href="/jobs"
                    class="text-sm text-muted-foreground underline"
                    >Browse Jobs</Link
                >
            </div>

            <div
                v-if="$page.props.flash?.status"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ $page.props.flash.status }}
            </div>

            <div
                class="grid gap-3 rounded-xl border bg-white p-4 sm:grid-cols-2 lg:grid-cols-3"
            >
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

                <template #cell-expected_salary="{ value }">
                    {{ value ? `BDT ${value}` : '—' }}
                </template>

                <template #cell-created_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
                }}</template>
                <template #cell-reviewed_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
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
        title="Withdraw application"
        description="This will withdraw your application for this job."
        confirm-label="Withdraw"
        :destructive="true"
        @confirm="confirmWithdraw"
        @cancel="closeConfirm"
    />
</template>
