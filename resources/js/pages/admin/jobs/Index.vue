<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import ConfirmDialog from '@/components/admin/dialogs/ConfirmDialog.vue';
import DataTable from '@/components/admin/table/DataTable.vue';
import RowActionsDropdown from '@/components/admin/table/RowActionsDropdown.vue';
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
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    items: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    counts: { type: Object, default: () => ({}) },
    statusOptions: { type: Array, default: () => [] },
    guardianOptions: { type: Array, default: () => [] },
    pageTitle: { type: String, default: 'All Jobs' },
});

const breadcrumbs = [{ title: 'Jobs', href: '/admin/jobs' }];

const presetStatus = computed(() => props.filters.preset_status || '');
const baseUrl = computed(() => {
    if (presetStatus.value === 'pending') {
        return '/admin/jobs/pending';
    }

    if (presetStatus.value === 'live') {
        return '/admin/jobs/live';
    }

    if (presetStatus.value === 'expired') {
        return '/admin/jobs/expired';
    }

    if (presetStatus.value === 'confirmed') {
        return '/admin/jobs/confirmed';
    }

    if (presetStatus.value === 'cancelled') {
        return '/admin/jobs/cancelled';
    }

    return '/admin/jobs';
});

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'guardian_name', label: 'Guardian' },
    { key: 'category_name', label: 'Category' },
    { key: 'class_name', label: 'Class' },
    { key: 'city_name', label: 'City' },
    { key: 'applications_count', label: 'Applications' },
    { key: 'hiring_outcome', label: 'Hiring Outcome' },
    { key: 'status', label: 'Status' },
    { key: 'published_at', label: 'Published At' },
    { key: 'expires_at', label: 'Expires At' },
    { key: 'actions', label: 'Actions', cellClass: 'w-[1%] whitespace-nowrap' },
];

const search = ref(props.filters.q ?? '');
const statusFilter = ref(props.filters.status || 'all');
const guardianFilter = ref(
    props.filters.guardian_id ? String(props.filters.guardian_id) : 'all',
);
const sortBy = ref(props.filters.sort || 'updated_at');
const direction = ref(props.filters.direction || 'desc');

const confirmOpen = ref(false);
const confirmTitle = ref('Confirm Action');
const confirmDescription = ref('');
const confirmLabel = ref('Confirm');
const confirmDestructive = ref(false);
const pendingAction = ref(null);
let searchDebounceTimer = null;

const transitionMap = {
    pending: ['live', 'cancelled'],
    live: ['confirmed', 'cancelled', 'closed'],
    confirmed: ['closed'],
    cancelled: [],
    closed: [],
};

const statusLabelMap = {
    pending: 'Pending',
    live: 'Live',
    confirmed: 'Confirmed',
    cancelled: 'Cancelled',
    closed: 'Closed',
};

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

watch(
    () => props.filters.guardian_id,
    (value) => {
        const normalized = value ? String(value) : 'all';

        if (guardianFilter.value !== normalized) {
            guardianFilter.value = normalized;
        }
    },
);

watch(
    () => props.filters.sort,
    (value) => {
        const normalized = value || 'updated_at';

        if (sortBy.value !== normalized) {
            sortBy.value = normalized;
        }
    },
);

watch(
    () => props.filters.direction,
    (value) => {
        const normalized = value || 'desc';

        if (direction.value !== normalized) {
            direction.value = normalized;
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

watch(guardianFilter, (value) => {
    applyFilters({ guardian_id: value === 'all' ? '' : value, page: 1 });
});

watch(sortBy, (value) => {
    applyFilters({ sort: value, page: 1 });
});

watch(direction, (value) => {
    applyFilters({ direction: value, page: 1 });
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
            trash: props.filters.trash ? 1 : 0,
            q: search.value,
            status: presetStatus.value
                ? ''
                : statusFilter.value === 'all'
                  ? ''
                  : statusFilter.value,
            guardian_id:
                guardianFilter.value === 'all' ? '' : guardianFilter.value,
            sort: sortBy.value,
            direction: direction.value,
            ...overrides,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function statusActionLabel(nextStatus) {
    return `Mark as ${statusLabelMap[nextStatus] ?? nextStatus}`;
}

function actionItemsForRow(row) {
    if (props.filters.trash) {
        return [
            { key: 'restore', label: 'Restore' },
            {
                key: 'force-delete',
                label: 'Permanently Delete',
                destructive: true,
            },
        ];
    }

    const actions = [
        { key: 'applications', label: 'Applications' },
        { key: 'edit', label: 'Edit' },
        { key: 'delete', label: 'Delete', destructive: true },
    ];

    if (row.status === 'pending') {
        actions.unshift({ key: 'approve', label: 'Approve (Go Live)' });
    }

    const transitions = transitionMap[row.status] ?? [];

    transitions.forEach((nextStatus) => {
        if (row.status === 'pending' && nextStatus === 'live') {
            return;
        }

        actions.unshift({
            key: `status-${nextStatus}`,
            label: statusActionLabel(nextStatus),
            destructive: nextStatus === 'cancelled',
        });
    });

    return actions;
}

function handleRowAction(actionKey, row) {
    if (actionKey === 'applications') {
        router.visit(`/admin/jobs/${row.id}/applications`);

        return;
    }

    if (actionKey === 'edit') {
        router.visit(`/admin/jobs/${row.id}/edit`);

        return;
    }

    openConfirm(actionKey, row);
}

function openConfirm(action, row = null) {
    pendingAction.value = { action, row };
    confirmTitle.value = 'Confirm Action';
    confirmDescription.value = '';
    confirmLabel.value = 'Confirm';
    confirmDestructive.value = false;

    if (action === 'delete') {
        confirmTitle.value = 'Delete Job';
        confirmDescription.value = 'This will move the job to recycle bin.';
        confirmLabel.value = 'Delete';
        confirmDestructive.value = true;
    }

    if (action === 'approve') {
        confirmTitle.value = 'Approve Job';
        confirmDescription.value =
            'This will move the job from pending to live.';
        confirmLabel.value = 'Approve';
    }

    if (action === 'restore') {
        confirmTitle.value = 'Restore Job';
        confirmDescription.value =
            'This will restore the job from recycle bin.';
        confirmLabel.value = 'Restore';
    }

    if (action === 'force-delete') {
        confirmTitle.value = 'Permanently Delete Job';
        confirmDescription.value = 'This action cannot be undone.';
        confirmLabel.value = 'Permanently Delete';
        confirmDestructive.value = true;
    }

    if (action === 'empty-recycle-bin') {
        confirmTitle.value = 'Empty Recycle Bin';
        confirmDescription.value =
            'This will permanently delete all trashed jobs.';
        confirmLabel.value = 'Empty Recycle Bin';
        confirmDestructive.value = true;
    }

    if (action.startsWith('status-')) {
        const nextStatus = action.replace('status-', '');
        const nextStatusLabel = statusLabelMap[nextStatus] ?? nextStatus;

        confirmTitle.value = `Change Status to ${nextStatusLabel}`;
        confirmDescription.value = `This will change the job status to ${nextStatusLabel}.`;
        confirmLabel.value = `Set ${nextStatusLabel}`;
        confirmDestructive.value = nextStatus === 'cancelled';
    }

    confirmOpen.value = true;
}

function resetConfirmState() {
    pendingAction.value = null;
}

function runConfirmedAction() {
    if (!pendingAction.value) {
        return;
    }

    const { action, row } = pendingAction.value;

    if (action === 'approve' && row) {
        router.patch(`/admin/jobs/${row.id}/approve`);
    }

    if (action.startsWith('status-') && row) {
        const nextStatus = action.replace('status-', '');

        router.patch(`/admin/jobs/${row.id}/status`, {
            status: nextStatus,
            reason:
                nextStatus === 'cancelled'
                    ? 'Cancelled by admin from listing.'
                    : '',
        });
    }

    if (action === 'delete' && row) {
        router.delete(`/admin/jobs/${row.id}`);
    }

    if (action === 'restore' && row) {
        router.patch(`/admin/jobs/${row.id}/restore`);
    }

    if (action === 'force-delete' && row) {
        router.delete(`/admin/jobs/${row.id}/force`);
    }

    if (action === 'empty-recycle-bin') {
        router.delete('/admin/jobs/recycle-bin/empty');
    }

    confirmOpen.value = false;
    resetConfirmState();
}

function statusBadge(row) {
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
    <Head title="Jobs" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4 sm:p-6">
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="space-y-1">
                        <h1 class="text-2xl font-semibold tracking-tight">
                            {{ pageTitle }}
                        </h1>
                        <p class="text-sm text-muted-foreground">
                            Total: {{ counts.total_count ?? 0 }} | Pending:
                            {{ counts.pending_count ?? 0 }} | Live:
                            {{ counts.live_count ?? 0 }} | Confirmed:
                            {{ counts.confirmed_count ?? 0 }} | Cancelled:
                            {{ counts.cancelled_count ?? 0 }} | Trash:
                            {{ counts.trash_count ?? 0 }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="
                                filters.trash ? baseUrl : `${baseUrl}?trash=1`
                            "
                            class="inline-flex items-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                        >
                            {{
                                filters.trash ? 'Back to Active' : 'Recycle Bin'
                            }}
                        </Link>

                        <Button
                            v-if="filters.trash"
                            type="button"
                            variant="destructive"
                            @click="openConfirm('empty-recycle-bin')"
                        >
                            Empty Recycle Bin
                        </Button>

                        <Link
                            v-if="!filters.trash"
                            href="/admin/jobs/create"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                        >
                            Create Job
                        </Link>
                    </div>
                </div>
            </div>

            <div
                class="grid gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5"
            >
                <Input
                    v-model="search"
                    type="text"
                    placeholder="Search jobs"
                    class="sm:col-span-2"
                />

                <Select v-if="!presetStatus" v-model="statusFilter">
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

                <Select v-model="guardianFilter">
                    <SelectTrigger>
                        <SelectValue placeholder="All guardians" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All guardians</SelectItem>
                        <SelectItem
                            v-for="guardian in guardianOptions"
                            :key="guardian.id"
                            :value="String(guardian.id)"
                        >
                            {{ guardian.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="sortBy">
                    <SelectTrigger>
                        <SelectValue placeholder="Sort by" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="updated_at">Last Updated</SelectItem>
                        <SelectItem value="created_at">Created At</SelectItem>
                        <SelectItem value="title">Title</SelectItem>
                        <SelectItem value="status">Status</SelectItem>
                        <SelectItem value="published_at"
                            >Published At</SelectItem
                        >
                        <SelectItem value="expires_at">Expires At</SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="direction">
                    <SelectTrigger>
                        <SelectValue placeholder="Direction" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="desc">Descending</SelectItem>
                        <SelectItem value="asc">Ascending</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DataTable
                :items="items"
                :columns="columns"
                empty-text="No jobs found."
            >
                <template #cell-applications_count="{ row }">
                    <Link
                        :href="`/admin/jobs/${row.id}/applications`"
                        class="text-sm font-medium text-blue-600 hover:underline"
                    >
                        {{ row.open_applications_count ?? 0 }} open /
                        {{ row.applications_count ?? 0 }} total
                    </Link>
                </template>

                <template #cell-hiring_outcome="{ row }">
                    <div v-if="row.has_assignment" class="space-y-0.5">
                        <p class="text-sm font-medium">
                            {{ row.selected_tutor_name || 'Selected tutor' }}
                        </p>
                        <p
                            v-if="row.assignment_confirmed_at"
                            class="text-xs text-muted-foreground"
                        >
                            Confirmed:
                            {{
                                new Date(
                                    row.assignment_confirmed_at,
                                ).toLocaleString()
                            }}
                        </p>
                        <p
                            v-else-if="row.assignment_appointed_at"
                            class="text-xs text-muted-foreground"
                        >
                            Appointed:
                            {{
                                new Date(
                                    row.assignment_appointed_at,
                                ).toLocaleString()
                            }}
                        </p>
                    </div>
                    <span v-else class="text-muted-foreground"
                        >Not finalized</span
                    >
                </template>

                <template #cell-status="{ row }">
                    <Badge :variant="statusBadge(row).variant">
                        {{ statusBadge(row).label }}
                    </Badge>
                </template>

                <template #cell-published_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
                }}</template>
                <template #cell-expires_at="{ value }">{{
                    value ? new Date(value).toLocaleString() : '—'
                }}</template>

                <template #cell-actions="{ row }">
                    <RowActionsDropdown
                        :actions="actionItemsForRow(row)"
                        @select="(action) => handleRowAction(action, row)"
                    />
                </template>
            </DataTable>
        </div>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-label="confirmLabel"
            :destructive="confirmDestructive"
            @confirm="runConfirmedAction"
            @cancel="resetConfirmState"
        />
    </AdminLayout>
</template>
