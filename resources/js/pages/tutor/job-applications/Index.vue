<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { CircleCheck, CircleX, Send, Star, UserRound } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
import { Badge } from '@/components/ui/badge';
import TutorLayout from '@/layouts/TutorLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusCounts: { type: Object, default: () => ({}) },
});

const breadcrumbs = [
    { title: 'My Applications', href: '/tutor/job-applications' },
];

const statusMenus = [
    {
        key: 'all',
        label: 'All',
        href: '/tutor/job-applications',
        icon: Send,
    },
    {
        key: 'shortlisted',
        label: 'Shortlisted',
        href: '/tutor/job-applications/shortlisted',
        icon: Star,
    },
    {
        key: 'appointed',
        label: 'Appointed',
        href: '/tutor/job-applications/appointed',
        icon: UserRound,
    },
    {
        key: 'confirmed',
        label: 'Confirmed',
        href: '/tutor/job-applications/confirmed',
        icon: CircleCheck,
    },
    {
        key: 'cancelled',
        label: 'Canceled',
        href: '/tutor/job-applications/cancelled',
        icon: CircleX,
    },
];

const presetStatus = computed(() => props.filters.preset_status || '');
const appliedCount = computed(() => {
    const value = props.statusCounts?.applied;

    if (typeof value === 'number') {
        return value;
    }

    return 0;
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

const confirmOpen = ref(false);
const pendingRow = ref<any>(null);

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

function formatCount(count: number): string {
    return String(count).padStart(2, '0');
}

function menuCount(key: string): number {
    const value = props.statusCounts?.[key];

    if (typeof value === 'number') {
        return value;
    }

    return 0;
}
</script>

<template>
    <Head title="My Applications" />

    <TutorLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight sm:text-3xl">
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
                class="rounded-2xl border border-slate-200/80 bg-slate-50/60 px-4 shadow-sm"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-blue-200/80"
                >
                    <div
                        class="w-full [scrollbar-width:none] overflow-x-auto [-ms-overflow-style:none] md:w-auto"
                    >
                        <div
                            class="flex min-w-max items-center gap-6 pr-2 [&::-webkit-scrollbar]:hidden"
                        >
                            <Link
                                v-for="menu in statusMenus"
                                :key="menu.key"
                                :href="menu.href"
                                class="inline-flex items-center gap-1.5 border-b-2 py-3 text-sm font-medium transition"
                                :class="
                                    (menu.key === 'all' &&
                                        presetStatus === '') ||
                                    presetStatus === menu.key
                                        ? 'border-blue-500 text-blue-500'
                                        : 'border-transparent text-slate-500 hover:text-slate-700'
                                "
                            >
                                <component :is="menu.icon" class="h-4 w-4" />
                                <span>{{ menu.label }}</span>
                                <span>{{
                                    formatCount(menuCount(menu.key))
                                }}</span>
                            </Link>
                        </div>
                    </div>

                    <p
                        class="hidden pb-3 text-sm font-medium text-slate-600 md:block"
                    >
                        Applied {{ formatCount(appliedCount) }}
                    </p>
                </div>
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
