<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
import GuardianLayout from '@/layouts/GuardianLayout.vue';

const props = defineProps({
    job: { type: Object, required: true },
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
});

type StatusOption = {
    value: string;
    label: string;
};

type ApplicationRow = {
    id: number;
    status: string;
    is_selected?: boolean;
    expected_salary_amount?: string | number | null;
    salary_currency?: string | null;
    tutor: {
        name?: string | null;
        email?: string | null;
        phone?: string | null;
    };
};

type GuardianJob = {
    id: number;
    title: string;
    status?: string;
    has_assignment?: boolean;
    selected_tutor_name?: string | null;
    is_expired?: boolean;
    assignment_confirmed_at?: string | null;
    can_manage_applications?: boolean;
};

const breadcrumbs = [
    { title: 'My Jobs', href: '/guardian/jobs' },
    {
        title: 'Applications',
        href: `/guardian/jobs/${props.job.id}/applications`,
    },
];

const baseUrl = `/guardian/jobs/${props.job.id}/applications`;
const page = usePage();
const flashStatus = computed<string | null>(
    () => (page.props.flash as { status?: string } | undefined)?.status ?? null,
);
const statusOptionsList = computed<StatusOption[]>(
    () => (props.statusOptions as StatusOption[] | undefined) ?? [],
);
const currentJob = computed(() => props.job as GuardianJob);

const columns = [
    { key: 'tutor_name', label: 'Tutor' },
    { key: 'status', label: 'Status' },
    { key: 'expected_salary_amount', label: 'Expected Salary' },
    { key: 'cover_letter', label: 'Cover Letter' },
    { key: 'created_at', label: 'Applied At' },
    { key: 'cancel_reason', label: 'Cancel Reason' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const statusFilter = ref(props.filters.status || 'all');
const confirmOpen = ref(false);
const pendingAction = ref<'confirm' | 'shortlist' | 'cancel' | null>(null);
const pendingRow = ref<ApplicationRow | null>(null);
const canManageApplications = computed(
    () => currentJob.value.can_manage_applications === true,
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

function badgeVariant(
    status: string | null | undefined,
): 'default' | 'destructive' | 'secondary' | 'outline' {
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

function actionItems(row: ApplicationRow) {
    return [
        {
            key: 'confirm',
            label: 'Confirm Hire',
            show:
                canManageApplications.value &&
                row.status === 'shortlisted' &&
                !row.is_selected,
        },
        {
            key: 'shortlist',
            label: 'Shortlist',
            show: canManageApplications.value && row.status === 'applied',
        },
        {
            key: 'cancel',
            label: 'Cancel Application',
            destructive: true,
            show:
                canManageApplications.value &&
                ['applied', 'shortlisted'].includes(row.status),
        },
    ];
}

function handleAction(
    action: 'confirm' | 'shortlist' | 'cancel',
    row: ApplicationRow,
) {
    pendingAction.value = action;
    pendingRow.value = row;
    confirmOpen.value = true;
}

function confirmStatusUpdate() {
    if (!pendingAction.value || !pendingRow.value) {
        return;
    }

    const isConfirmAction = pendingAction.value === 'confirm';

    if (isConfirmAction) {
        const escrowRequired = window.confirm(
            'Require month-1 escrow for this hire? Press OK for Yes, Cancel for No.',
        );
        let escrowAmount = null;

        if (escrowRequired) {
            const entered = window.prompt(
                'Enter month-1 escrow amount',
                String(pendingRow.value.expected_salary_amount ?? ''),
            );

            if (entered === null || entered.trim() === '') {
                return;
            }

            escrowAmount = Number(entered);

            if (!Number.isFinite(escrowAmount) || escrowAmount <= 0) {
                return;
            }
        }

        router.patch(
            `${baseUrl}/${pendingRow.value.id}/confirm`,
            {
                month1_escrow_required: escrowRequired,
                month1_escrow_amount: escrowAmount,
                notes: '',
            },
            {
                preserveScroll: true,
                onFinish: resetConfirm,
            },
        );
    } else {
        router.patch(
            `${baseUrl}/${pendingRow.value.id}/status`,
            {
                status:
                    pendingAction.value === 'shortlist'
                        ? 'shortlisted'
                        : 'cancelled',
                cancel_reason: '',
            },
            {
                preserveScroll: true,
                onFinish: resetConfirm,
            },
        );
    }

    confirmOpen.value = false;
}

function resetConfirm() {
    pendingAction.value = null;
    pendingRow.value = null;
}

function confirmTitle() {
    if (pendingAction.value === 'confirm') {
        return 'Confirm Tutor Hire';
    }

    return pendingAction.value === 'shortlist'
        ? 'Shortlist tutor'
        : 'Cancel application';
}

function confirmDescription() {
    if (pendingAction.value === 'confirm') {
        return 'This will finalize the hire and cancel other open applications.';
    }

    return pendingAction.value === 'shortlist'
        ? 'This tutor will be marked as shortlisted for this job.'
        : 'This tutor application will be cancelled.';
}

function confirmLabel() {
    if (pendingAction.value === 'confirm') {
        return 'Confirm Hire';
    }

    return pendingAction.value === 'shortlist' ? 'Shortlist' : 'Cancel';
}
</script>

<template>
    <Head :title="`Applications - ${job.title}`" />

    <GuardianLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6 lg:p-8">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">
                            Applications for {{ job.title }}
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Review tutors and shortlist the best fit.
                        </p>
                        <p
                            v-if="job.has_assignment"
                            class="mt-1 text-xs font-medium text-emerald-700"
                        >
                            Hire finalized with
                            {{ job.selected_tutor_name || 'selected tutor' }}.
                        </p>
                        <p
                            v-else-if="job.is_expired"
                            class="mt-1 text-xs font-medium text-amber-700"
                        >
                            This job is expired. Application actions are locked.
                        </p>
                        <p
                            v-else-if="job.status === 'confirmed'"
                            class="mt-1 text-xs font-medium text-emerald-700"
                        >
                            This job has already been confirmed.
                        </p>
                    </div>

                    <Link
                        href="/guardian/jobs"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >Back to Jobs</Link
                    >
                </div>
            </div>

            <div
                v-if="flashStatus"
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
            >
                {{ flashStatus }}
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-3"
            >
                <Select v-model="statusFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All statuses" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All statuses</SelectItem>
                        <SelectItem
                            v-for="option in statusOptionsList"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <div
                    v-if="job.has_assignment && job.assignment_confirmed_at"
                    class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800"
                >
                    Confirmed at:
                    {{ new Date(job.assignment_confirmed_at).toLocaleString() }}
                </div>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No applications received yet."
            >
                <template #cell-tutor_name="{ row }">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-slate-900">{{ row.tutor.name }}</p>
                            <Badge v-if="row.is_selected" variant="default" class="bg-emerald-600">Selected</Badge>
                        </div>
                        <div v-if="job.subjects?.length" class="flex flex-wrap items-center gap-1 text-xs">
                            <span class="font-semibold text-slate-400">Subject:</span>
                            <span class="font-semibold text-slate-700">{{ job.subjects.join(', ') }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs">
                            <span v-if="row.tutor.phone" class="text-slate-500">{{ row.tutor.phone }}</span>
                            <a
                                v-if="row.tutor.download_cv_url"
                                :href="row.tutor.download_cv_url"
                                target="_blank"
                                class="inline-flex items-center gap-1 font-bold text-blue-600 hover:text-blue-700 hover:underline"
                            >
                                <span>CV View</span>
                                <span>↓</span>
                            </a>
                        </div>
                    </div>
                </template>

                <template #cell-status="{ value }">
                    <Badge :variant="badgeVariant(value)">{{ value }}</Badge>
                </template>

                <template #cell-expected_salary_amount="{ row }">
                    {{
                        row.expected_salary_amount
                            ? `${row.salary_currency || 'BDT'} ${Number(row.expected_salary_amount).toLocaleString('en-BD', { maximumFractionDigits: 0 })}`
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
    </GuardianLayout>

    <ConfirmDialog
        v-model:open="confirmOpen"
        :title="confirmTitle()"
        :description="confirmDescription()"
        :confirm-label="confirmLabel()"
        :destructive="pendingAction === 'cancel'"
        @confirm="confirmStatusUpdate"
        @cancel="resetConfirm"
    />
</template>
